<?php include "../bloghead.html" ?>
<h4>Sprite loading, blitting and transforming
Page 1 - Loading and basic blitting</h4>

Prerequisites:
- A basic knowledge of C
- A C program with a window and pixel buffer. You can follow my <a href="blog.php#tutorial/opening_a_window_with_xlib" target="_blank">Xlib</a> or <a href="blog.php#tutorial/opening_a_window_with_sdl" target="_blank">SDL</a> tutorials for this.
- <a href="https://github.com/nothings/stb/blob/master/stb_image.h" target="_blank">stb_image.h</a>

Alright, first I'll show you what you'll end up with at the end of this tutorial:

<img src="tutorial/data_sprite_loading_blitting_and_transforming/end_result.gif">

To summarize: We load a sprite with transparency, blit it to the pixel buffer, and blit using rotation, scale and rotation+scale, all with origin points. The PNG loading is done with the stb_image library for this tutorial. In the future I'd like to cover decompressing PNGs manually but for now Sean T Barrett comes to the rescue!

Here's the full source for this tutorial: <a href="https://gitlab.com/UltimaN3rd/croaking-kero-programming-tutorials/tree/master/sprite_loading_blitting_and_transforming" target="_blank">https://gitlab.com/UltimaN3rd/croaking-kero-programming-tutorial/tree/master/sprite_loading_blitting_and_transforming</a>

I suggest you try compiling+running that before following the tutorial to make sure you've got your libraries installed correctly.

Linux:
<code><xmp>gcc sprite_loading_blitting_and_transforming.c -o bin -lX11 -lm</xmp></code>

Windows (developer console):
<code><xmp>cl sprite_loading_blitting_and_transforming.c -link SDL_LIBRARY_DIRECTORY/SDL2.lib SDL_LIBRARY_DIRECTORY/SDL2main.lib -SUBSYSTEM:CONSOLE sprite_loading_blitting_and_transforming.exe</xmp></code>

For my C program with a window and pixel buffer I'm using my <a href="https://gitlab.com/UltimaN3rd/croaking-kero-c-libraries" target="_blank">Kero Window library</a>, made for these tutorials. You can take the following as psuedocode and implement your own window code if you like.

<code><xmp>#include "kero_window.h"

int main(int argc, char* argv[]){
    KWInit(1280, 720, (char*)"Nick's Sprite Blitting Window");
    
    // Main loop
    bool game_running = true;
    while(game_running){
        while(KWEventsQueued()){
            kwEvent* e = KWNextEvent();
            switch(e->type){
                case KWEVENT_QUIT:{
                    game_running = false;
                }break;
            }
            KWFreeEvent(e);
        }
        
	// Clear frame buffer
	memset(kw_framebuffer.pixels, 0xff, kw_framebuffer.w * kw_framebuffer.h * 4);

        KWFlipWindow();
    }
}</xmp></code>

If you want to go into detail on how that stuff works, or the few other KW* functions and variables you'll see later you can take a look at the <a href="https://gitlab.com/UltimaN3rd/croaking-kero-c-libraries/blob/master/include/kero_window.h" target="_blank">kero_window.h</a> source file and/or follow my "opening a window" tutorials.

First thing, let's load a sprite. I'm using this croaking kero sprite: <img src="tutorial/data_sprite_loading_blitting_and_transforming/croakingkero.png">

<code><xmp>// Top of file
#define STB_IMAGE_IMPLEMENTATION
#define STBI_ONLY_PNG
#include <stb/stb_image.h>
#include <stdint.h>



    // Before main loop
    uint8_t* kero_pixels;
    int kero_width, kero_height;
    kero_pixels = stbi_load((char*)"croakingkero.png", &kero_width, &kero_height, 0, 0);
    
    // Inside main loop before FlipWindow
    for(int sourcey = 0; sourcey < kero_height; sourcey++){
        for(int sourcex = 0; sourcex < kero_width; sourcex++){
            ((uint32_t*)kw_framebuffer.pixels)[sourcey*kw_framebuffer.w + sourcex] = ((uint32_t*)kero_pixels)[sourcey*kero_width + sourcex];
        }
    }</xmp></code>

If you run that and notice that the colours of the sprite are messed up, we'll fix that next.

So first we include stb_image along with defining the "implementation" (required for the lib to work) and STBI_ONLY_PNG which just prevents the lib from loading all the code for other file types. If you're using a different file type you can just remove that #define, and if you like use the one for your image type.

Starting in main before the main loop, we create uninitialized variables for a pixel buffer, width and height of the sprite. Then we send the width and height into the stbi_load function along with the filename of our PNG and capture the returned pixel buffer. You can see from the function call that we're passing pointers to our variables so that the stbi_load function can determine the width and height, fill those variables for us and allocate the appropriate amount of bytes for our pixel buffer.

Then we iterate through the image pixel by pixel, copying them to the frame buffer. We travel row-by-row instead of column-by-column because that's how the data is laid out in memory. So we index each pixel by casting to a uint32_t*, adding sourcey times the width (move down sourcey rows) plus sourcex (move across sourcex pixels within that row). We index the same pixel in the source image, and dereference both to set the frame buffer pixel to the value of the sprite pixel.

<img src="tutorial/data_sprite_loading_blitting_and_transforming/pixel_indexing.png">

I've done it pixel-by-pixel here because we're going to need to do it that way for every other kind of blit, but there is an alternative for a non-transformed blit and that's row-by-row. Just remove the X for loop and instead, for every row use:
<code><xmp>memcpy((uint32_t*)(kw_framebuffer.pixels) + sourcey*kw_framebuffer.w, (uint32_t*)(kero_pixels) + sourcey*kero_width, kero_width*4);</xmp></code>

So, why are the colours messed up? stb_image loads in the pixel components in the following order: RGBA. This is probably because it's the most common pixel component order used by graphics cards and most of the time you'd use a graphics API like OpenGL. However Xorg, Win32 and I assume Mac's display server use BGRX ordering. X being an unread byte of ignored data. This means the Red and Blue channels are reversed, so we need to juggle our bytes around. Ideally we'd be loading the PNG files ourselves and we could order the bytes correctly while loading, but instead we'll loop through the pixels and fix them one at a time:

<code><xmp>    // After loading sprite
    for(int y = 0; y < kero_height; y++){
        for(int x = 0; x < kero_width; x++){
            uint8_t* b = &((uint32_t*)kero_pixels)[y*kero_width + x];
            uint8_t* r = b + 2;
            uint8_t true_red = *b;
            *b = *r;
            *r = true_red;
        }
    }</xmp></code>

Since we're indexing individual components instead of whole pixels we make uint8_t pointers to the red and blue bytes and index them by multiplying the x/y values by 4. As mentioned before, stb_image loads images in RGBA order, so the first byte holds the red component data but will be interpreted by the display server as blue. Add 2 to access the third byte in the pixel which currently holds the blue byte but will be read by our display server as red. We save the value in the first byte, copy the actual blue value to the correct blue byte then set red to the saved true_red value.

Now that the R/B bytes have been flipped the image should display properly.

During normal development I'd wait to wrap code into functions until I've had to use that code in multiple places but obviously in any real-world scenario we're going to want to draw multiple sprites so let's wrap it up:

<code><xmp>// Before main()
void SpriteBlit(uint8_t* sprite_pixels, int sprite_width, int sprite_height, uint8_t* dest_pixels, int dest_width){
    for(int sourcey = 0; sourcey < sprite_height; sourcey++){
        for(int sourcex = 0; sourcex < sprite_width; sourcex++){
            ((uint32_t*)dest_pixels)[sourcey*dest_width + sourcex] = ((uint32_t*)sprite_pixels)[sourcey*sprite_width + sourcex];
        }
    }
}

    // Replace blitting code before FlipWindow
    SpriteBlit(kero_pixels, kero_width, kero_height, kw_framebuffer.pixels, kw_framebuffer.w);</xmp></code>

Still working just fine, but that argument list is a bit cumbersome, right? It's a bit of a pain to separately pass the pixels, width and height of the source and destination, so let's make a struct to hold this data together. It's also become clear at this point that although stb_image passes us an array of bytes indexing each pixel component, we are more often than not converting to an array of uint32_t pixels so let's make that change now.

<code><xmp>// After #includes
typedef struct{
    int w, h;
    uint32_t* pixels;
} Sprite;</xmp></code>

Now that we've got a Sprite struct we can make our code more reusable, so let's bundle up the load code into a function:

<code><xmp>// Before main()
bool SpriteLoad(char* file, Sprite* sprite){
    sprite->pixels = (uint32_t*)stbi_load(file, &sprite->w, &sprite->h, 0, 0);
    if(!sprite->pixels){
        return false;
    }
    
    for(int y = 0; y < sprite->h; y++){
        for(int x = 0; x < sprite->w; x++){
            uint8_t* b = (uint8_t*)&sprite->pixels[y*sprite->w + x];
            uint8_t* r = b + 2;
            uint8_t true_red = *b;
            *b = *r;
            *r = true_red;
        }
    }
    
    return true;
}

    // In main()
    Sprite kero_sprite;
    if(!SpriteLoad((char*)"croakingkero.png", &kero_sprite)){
        printf("Failed to load sprite.\n");
        return 0;
    }</xmp></code>

We've just taken the previous loading code and B/R swapping code and put them together into a function with a check to make sure the image was loaded correctly. Now let's make our sprite blitting code into a function that works with Sprites as well.

<code><xmp>// Before main()
void SpriteBlit(Sprite* source, Sprite* dest){
    for(int sourcey = 0; sourcey < source->h; sourcey++){
        for(int sourcex = 0; sourcex < source->w; sourcex++){
            dest->pixels[sourcey*dest->w + sourcex] = source->pixels[sourcey*source->w + sourcex];
        }
    }
}</xmp></code>

Basically the same as before but now we can just pass pointers to Sprites! There's one problem - our frame buffer isn't a Sprite. That's easy enough to fix:

<code><xmp>    // Before main loop
    Sprite framebuffer;
    framebuffer.pixels = (uint32_t*)kw_framebuffer.pixels;
    framebuffer.w = kw_framebuffer.w;
    framebuffer.h = kw_framebuffer.h;
    
        // Replace previous SpriteBlit call inside main loop
        SpriteBlit(&kero_sprite, &framebuffer);</xmp></code>

That's all much nicer and is now reusable. However, since we're now storing the width/height and pixel buffer pointer of the frame buffer in our own struct we need to update that data on a window resize. You shouldn't have much trouble doing that with whatever windowing code you're using but here's what I've got for Kero Window:

<code><xmp>                // Inside switch(e->type){
                case KWEVENT_RESIZE:{
                    framebuffer.w = kw_framebuffer.w;
                    framebuffer.h = kw_framebuffer.h;
                    framebuffer.pixels = (uint32_t*)kw_framebuffer.pixels;
                }break;</xmp></code>

Now let's get that sprite moving around.
s
<code><xmp>    // Before main loop
    int x = 0, y = 0;

        // Before blit
        if(kw_keyboard[KEY_UP]){
            --y;
        }
        if(kw_keyboard[KEY_DOWN]){
            ++y;
        }
        if(kw_keyboard[KEY_LEFT]){
            --x;
        }
        if(kw_keyboard[KEY_RIGHT]){
            ++x;
        }

        // New SpriteBlit call
        SpriteBlit(&kero_sprite, &framebuffer, x, y);

// Updated SpriteBlit function
void SpriteBlit(Sprite* source, Sprite* dest, int x, int y){
    for(int sourcey = 0; sourcey < source->h; sourcey++){
        for(int sourcex = 0; sourcex < source->w; sourcex++){
            dest->pixels[(y+sourcey)*dest->w + sourcex+x] = source->pixels[sourcey*source->w + sourcex];
        }
    }
}</xmp></code>

All we need to do is take an x,y pair in our SpriteBlit call and offset each destination pixel by those values.

If you move the sprite around you should notice a few problems. First, the sprite has a transparent background but displays black where it should be see-through. And second, when the sprite moves beyond the edges of the frame we begin accessing the wrong pixels, or some other memory entirely, probably causing a crash.

We could prevent the sprite from going out of bounds by simply restricting our x/y variables, but you don't want to have to do that for every single sprite you draw in your game. Instead let's clip the drawing area in our blit function:

<code><xmp>// Top of file
#define Min(a,b) ((a)<(b) ? (a):(b))
#define Max(a,b) ((a)>(b) ? (a):(b))

// SpriteBlit body
    int left_clip = Max(0, -x);
    int right_clip = Max(0, x + source->w - dest->w);
    int top_clip = Max(0, -y);
    int bottom_clip = Max(0, y + source->h - dest->h);
    
    for(int sourcey = top_clip; sourcey < source->h - bottom_clip; sourcey++){
        for(int sourcex = left_clip; sourcex < source->w - right_clip; sourcex++){</xmp></code>

So for the left we want to clip an amount of the image equal to the amount our x value is left of 0, or clip nothing if it's right of 0. The top clip is the same but with y. To clip the right we take the difference between the right-most point we want to draw (x + source->w) and the destination's width and clip that amount. Same for the bottom in the y axis. Then we actually clip those amounts by reading pixels from our sprite starting at the left/top clips and ending at width/height minus right/bottom clips.

<img src="tutorial/data_sprite_loading_blitting_and_transforming/lclip.png"> <img src="tutorial/data_sprite_loading_blitting_and_transforming/rclip.png">

Let's solve the transparency issue now. If you were always going to have pixels that were either fully transparent or fully opaque you could check the transparency and skip the pixel. You could even have 24-bit pixels and use some specific colour (like magenta) to represent transparent pixels. However I want to be able to have semi-transparent pixels so that I can create "beautiful" effects like this horrendous blue glow: <img src="tutorial/data_sprite_loading_blitting_and_transforming/croakingkeroglow.png">

That means we'll have to blend each pixel with the target pixel depending on the source's transparency:

<code><xmp>            // Inside SpriteBlit foor loops
            uint8_t* source_pixel = (uint8_t*)&source->pixels[sourcey*source->w + sourcex];
            uint8_t* dest_pixel = (uint8_t*)&dest->pixels[(y+sourcey)*dest->w + sourcex+x];
            float alpha = (float)(source_pixel[3]) / 255.f;
            float one_minus_alpha = 1.f - alpha;
            dest_pixel[0] = source_pixel[0] * alpha + dest_pixel[0] * one_minus_alpha; // Blue
            dest_pixel[1] = source_pixel[1] * alpha + dest_pixel[1] * one_minus_alpha; // Green
            dest_pixel[2] = source_pixel[2] * alpha + dest_pixel[2] * one_minus_alpha; // Red
            dest_pixel[3] += (255 - dest_pixel[3]) * alpha; // Alpha (ignored on frame buffer)</xmp></code>

So we index the first component of the source and destination pixels, which is the Blue component. We reduce the alpha value of the source pixel to a fraction between 0 and 1 so we can multiply other values by it. Then for the first three components we do a little math to blend the pixels. This simple equation just takes the source and destination pixels and adds them together according to the ratio of the alpha value. If the source pixel has 50% opacity then we get 50% of the source pixel colour and 50% of the destination's colour. At 75% opacity we get 75% of the source pixel colour and 25% of the destination colour, and so on.

If you think about it in real-world terms, starting with the light coming from the object at the back of the screen, that light passes through a transparent object. If that object is 50% transparent, 50% of the light is lost. If you now place another transparent object in the path of that light, say a 25% transparent object, then the light will lose 25% of what remains. Therefore all we have to do is take the transparency (255 - opacity) of the destination pixel, multiply that by the source pixel's alpha and add it to the original opacity.

<img src="tutorial/data_sprite_loading_blitting_and_transforming/light.png">

So you could say by stacking a 50% opaque object with a 25% opaque object, you end up with a combined 62.5% opacity.

Since we're blitting to the frame buffer the alpha value could be left out. The alpha of the frame buffer's pixels is ignored when copying to the screen. However, for blitting from Sprite to Sprite, correct alpha combining is important. For performance in your own project you might make two separate functions, one which calculates the alpha value of the destination pixel for Sprite->Sprite blits and one that doesn't for Sprite->Frame Buffer blits. Or you may have your SpriteBlit take a pointer to a pixel blending function instead.

In the following pages of this tutorial we're going to need to copy that pixel blitting code many times, so let's wrap it up into some functions.

<code><xmp>// Above blitting functions
uint32_t SpriteGetPixel(Sprite* source, int x, int y){
    if(x < 0 || x > source->w-1 || y < 0 || y > source->h-1)return 0;
    return source->pixels[y*source->w + x];
}

// Overwrite target pixel without blending
void SpriteSetPixel(Sprite* dest, int x, int y, uint32_t pixel){
    if(x < 0 || x > dest->w-1 || y < 0 || y > dest->h-1)return;
    source->pixels[sourcey*source->w + sourcex] = pixel;
}

// Blend correctly, including calculating new alpha
void SpriteBlendPixel(Sprite* dest, int x, int y, uint32_t pixel){
    if(x < 0 || x > dest->w-1 || y < 0 || y > dest->h-1)return;
    uint8_t* dest_pixel = (uint8_t*)&dest->pixels[y*dest->w + x];
    float alpha = (float)(pixel >> 24) / 255.f;
    float one_minus_alpha = 1.f - alpha;
    dest_pixel[0] = (uint8_t)(pixel)      *alpha + dest_pixel[0]*one_minus_alpha;
    dest_pixel[1] = (uint8_t)(pixel>>8)   *alpha + dest_pixel[1]*one_minus_alpha;
    dest_pixel[2] = (uint8_t)(pixel>>16)  *alpha + dest_pixel[2]*one_minus_alpha;
    dest_pixel[3] += (255-dest_pixel[3]) * alpha;
}



            // Inside SpriteBlit, replacing previous pixel setting lines of code
            SpriteBlendPixel(dest, x+sourcex, y+sourcey, source->pixels[sourcey*source->w + sourcex]);</xmp></code>

We built in some safety checks to each of the pixel functions so that we don't accidentally access memory outside of the array bounds. As mentioned earlier you may want to make a pixel blending function that doesn't bother calculating the new pixel alpha so you can save some CPU cycles when blitting to the frame buffer.

The last thing I think it will be useful to add to the blitting function is an origin point. Origins will be vital in the transformations in later pages of this tutorial but they can also be useful for a standard blit. For instance you might consider your player's X/Y position to be located at the bottom-middle of the sprite, so simply being able to supply that position to the blit function makes it easy to keep sprites properly aligned with the physics or underlying logic.

<code><xmp>// Final SpriteBlit function
void SpriteBlit(Sprite* source, Sprite* dest, int x, int y, int originx, int originy){
    int left = x - originx;
    int top = y - originy;
    
    int left_clip = Max(0, -left);
    int right_clip = Max(0, left + source->w - dest->w);
    int top_clip = Max(0, -top);
    int bottom_clip = Max(0, top + source->h - dest->h);
    
    for(int sourcey = top_clip; sourcey < source->h - bottom_clip; sourcey++){
        for(int sourcex = left_clip; sourcex < source->w - right_clip; sourcex++){
            SpriteBlendPixel(dest, left+sourcex, top+sourcey, source->pixels[sourcey*source->w + sourcex]);
        }
    }
}

        // Replace previous SpriteBlit call
        SpriteBlit(&kero_sprite, &framebuffer, x, y, 0, 0);</xmp></code>

That's about it for simple blitting. With this you could make a full game - particularly games in the NES days usually didn't transform sprites so there's a whole lot of possibility with just this simple technique.

This tutorial continues on page 2 with scaling! I'd link it here but I'm a C programmer, not web, so you'll have to click the link in the side/top-bar.

Thanks to Froggie717 for criticisms and correcting errors in this tutorial.
<?php include "../blogbottom.html" ?>
