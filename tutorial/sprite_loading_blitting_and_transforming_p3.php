<?php include "../bloghead.html" ?>
<h4>Sprite loading, blitting and transforming</h4>

<code><xmp></xmp></code>

Right, let's spin these sprites around!

Since we've made a few sprite blitting functions already I'd like to write this page in the style I actually develop an API. First thing is writing the function call I'd like to be able to make.

<code><xmp>SpriteBlitRotated(&kero_sprite, &framebuffer, x, y, rot, 32, 32);</xmp></code>

The arguments are: Sprite to blit, target sprite, x/y position, rotation (in radians) on xy plane, originx/y (point about which rotation happens). Let's implement the function. We're going to construct the function in the same sort of way as the scaling function - loop through each pixel of the source image and place it in its correct position based on the given rotation/origin variables. First let's look at some trigonometry to calculate the destination point.

----------------------------------------------------------INSERT IMAGE SHOWING TRIG TO CALC POSITION OF SOURCE PIXEL ON TARGET

--------------
|............|
|............|
|....S.......|
|.......A....|    P
|............|
|........R...|
|............|
|............|
--------------

S is the source pixel. In this case 4,2
R is the origin. In this case 8,5

To find the rotated point P with the angle A we can use good ol' trig functions.

Sx - originx*cos(angle) + originy*sin(angle) + originx
Sy - originx*sin(angle) - originy*cos(angle) + originy

<code><xmp>// Top of file
#include <math.h>

// Before main()
void SpriteBlitRotated(Sprite* sprite, Sprite* target, float x, float y, float angle, float originx, float originy){
	float s = sinf(angle);
	float c = cosf(angle);
    
    for(int sourcey = 0; sourcey < sprite->h; ++sourcey){
        for(int sourcex = 0; sourcex < sprite->w; ++sourcex){
            float centerx = sourcex - originx;
            float centery = sourcey - originy;
            SpriteBlendPixel(target, x + centerx*c - centery*s + originx, y + centerx*s + centery*c + originy, SpriteGetPixel(sprite, sourcex, sourcey));
        }
    }
}

	// Before main loop
    float rot = 0.f;

		// Before blitting inside main loop
        rot += 0.01f;</xmp></code>



Thanks to Froggie717 for criticisms and correcting errors in this tutorial.
<?php include "../blogbottom.html" ?>
