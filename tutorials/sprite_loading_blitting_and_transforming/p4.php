<?php include "../bloghead.html" ?>
<h4>Sprite loading, blitting and transforming</h4>

Right now our scaled blit function can only scale by integers. That works fine for pixel-perfect scaled games, but let's look at how to scale by fractions. We're going to use a technique called "sampling". Before we'd iterate through each pixel of the source image and blit it a number of times based on the scale, but now let's iterate through the area of the target which the scaled sprite will cover and <i>sample</i> a pixel from the source.

<code>// Before main()
void SpriteBlitScaledSampled(Sprite* source, Sprite* dest, float x, float y, float scalex, float scaley){
    int left = max(x, 0);
    int right = min(x + source-&gt;w*scalex, dest->w-1);
    int top = max(y, 0);
    int bottom = min(y + source-&gt;h*scaley, dest->h-1);
    
    for(int dy = top; dy &lt; bottom; dy++){
        for(int dx = left; dx &lt; right; dx++){
            SpriteBlendPixel(dest, dx, dy, SpriteGetPixel(source, (dx - left)/scalex, (dy - top)/scaley));
        }
    }
}

        // Replace previous SpriteBlit call
        SpriteBlitScaled(&kero_sprite, &framebuffer, x, y, 2, 2);</code>

Since we're scaling by the same factors, that looks exactly the same. However let's get a side-by-side comparison of the new functionality enabled by sampling!

<code>    // Before main loop
    float scalex = 0.f, scaley = 0.f;

        // Inside main loop
        scalex += 0.01f;
        scaley += 0.03f;

        // Replace previous SpriteBlit call
        SpriteBlitScaled(&kero_sprite, &framebuffer, x + 512, y, sin(scalex)*10.f, sin(scaley)*10.f);
        SpriteBlitScaledSampled(&kero_sprite, &framebuffer, x, y, sin(scalex)*10.f, sin(scaley)*10.f);</code>

With the sampling method we can scale to any fractional amount without a problem!



Thanks to Froggie717 for criticisms and correcting errors in this tutorial.
<?php include "../blogbottom.html" ?>
