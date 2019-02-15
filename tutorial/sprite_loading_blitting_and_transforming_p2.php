<?php include "../bloghead.html" ?>
<h4>Sprite loading, blitting and transforming
Page 2 - Scaling</h4>

Right, let's get transforming! We'll start with the simplest transform, scaling:

<code><xmp>// Above main()
void SpriteBlitScaled(Sprite* source, Sprite* dest, float x, float y, float scalex, float scaley){
    for(int sourcey = 0; sourcey < source->h; sourcey++){
        for(int sourcex = 0; sourcex < source->w; sourcex++){
            uint32_t source_pixel = source->pixels[sourcey*source->w + sourcex];
            int destx = x + sourcex * scalex;
            int desty = y + sourcey * scaley;
            for(int dy = 0; dy < scaley; dy++){
                for(int dx = 0; dx < scalex; dx++){
                    SpriteBlendPixel(dest, destx+dx, desty+dy, source_pixel);
                }
            }
        }
    }
}

        // Replace SpriteBlit()
        SpriteBlitScaled(&kero_sprite, &framebuffer, x, y, 2, 2);</xmp></code>

The simplest method of scaling is to loop through the pixels of the source sprite and blit each pixel a number of times based on the scale. Notice that we have to write the same kind of code as before to retrieve and set pixels. I've used floats since we'll often want to scale by fractional amounts and that works fine with this scaling method. However if you want pixel-perfect scaling (like upscaling a low-resolution game by fixed pixel amounts) you may want to make a version of this function accepting only integer values.

A problem with our scaled blit function is that it can't scale by negative amounts. It's up to you what behaviour you want when using negatives, but I think flipping the sprite in that dimension is what I'd want if I put a negative into this function.

<code><xmp>            // Replace dy/dx for loops inside SpriteBlitScaled function
            for(int dy = Min(0, scaley); dy < Max(scaley, 0); dy++){
                for(int dx = Min(0, scalex); dx < Max(scalex, 0); dx++){</xmp></code>

Previously we were setting dy = 0 and looping while dy &lt; scaley. If scaley were negative that would mean that we would always fail that check and never blit any pixels. Now if scaley is positive then the same math occurs, but if scaley is negative we set dy to scaley and loop while scaley &lt; 0.

When you scale by negatives in each axis you'll notice that we get lines of empty pixels in the corresponding dimensions. Let's take a look at how the loops work when dealing with positive fractions and negative fractions.

<img src="tutorial/sprite_loading_blitting_and_transforming/scaling_loop_series.png">

So when our scale is negative we need to somehow give ourselves an extra pixel. Here's an easy way to do it:

<code><xmp>            // Update previous dy/dx loops inside SpriteBlitScaled function
            for(int dy = Min(0, scaley); dy < Max(scaley, 1); dy++){
                for(int dx = Min(0, scalex); dx < Max(scalex, 1); dx++){</xmp></code>

I replaced the zeroes with ones in that max check. This doesn't change the math for positive scales because 0 is always less than any fractional scale less than 1, the zero pixel will be drawn either way and the one pixel won't. However with negative scales we get our extra pixel to fill those nasty empty lines.

Now let's add another important feature: origin. An origin point is the center point of the transformation. Previously we were just scaling from the top-left corner because that's how our pixel data is laid out. In many situations you might want to use the center of the sprite, the bottom-middle or any other point - perhaps even a point not located on the sprite itself!

<code><xmp>// Add these extra arguments to SpriteBlitScaled
, float originx, float originy

            // Replace previous destx/desty code in SpriteBlitScaled function
            int destx = x + (sourcex-originx) * scalex;
            int desty = y + (sourcey-originy) * scaley;

        // Add last 2 arguments to SpriteBlitScaled call
        32, 32);</xmp></code>

So we adjust the destination of each block of scaled pixels by the origin position. Now the origin point of our sprite will be located at the x,y position we give to SpriteBlitScaled, and the sprite is transformed around that point! Try setting the origin to 32,32 (center of the croakingkero sprite) and other interesting points.

Now we've got a complete scaled blit function! At this point I highly recommend you do something like add some scale/origin variables that you can modify with key presses so that you can fully explore the range of sprite transformation you can achieve with the scaled blit.

This tutorial continues on page 3 with rotation! I'd link it here but I'm a C programmer, not web, so you'll have to click the link in the side/top-bar.



Thanks to Froggie717 for criticisms and correcting errors in this tutorial.
<?php include "../blogbottom.html" ?>
