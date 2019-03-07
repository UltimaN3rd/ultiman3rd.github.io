<!DOCTYPE html>
<html lang="en-US">

<head>
  <?php set_include_path($_SERVER['DOCUMENT_ROOT']); ?>
  <!--[if lt IE 9]>  <script src="html5shiv.min.js"></script>  <![endif]-->
  <?php include "head_common.html" ?>
  <link rel="stylesheet" href="/blog/blog.css" />
  <link rel="stylesheet" href="/tutorials/tutorial.css" />
  <?php include "/blog/bloghead.html" ?>
</head>

<?php include "header_common.html" ?>

<body>

<article>

<h4>Sprite loading, blitting and transforming</h4>

Right, let's spin these sprites around!

Since we've made a few sprite blitting functions already I'd like to write this page in the style I actually develop an API. First thing is writing the function call I'd like to be able to make.

<code><xmp>SpriteBlitRotated(&kero_sprite, &framebuffer, x, y, rot, 32, 32);</xmp></code>

The arguments are: Sprite to blit, target sprite, x/y position, rotation (in radians) on xy plane, originx/y (point about which rotation happens). Let's implement the function. We're going to construct the function in the same sort of way as the scaling function - loop through each pixel of the source image and place it in its correct position based on the given rotation/origin variables.



Sx - originx*cos(angle) + originy*sin(angle) + originx
Sy - originx*sin(angle) - originy*cos(angle) + originy

If you want a detailed explanation and proof of the above equations check this page: https://matthew-brett.github.io/teaching/rotation_2d.html
I think about it this way: 

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

</article>

</body>

<?php include "/blog/blogbottom.html" ?>

<?php include "footer_common.html" ?>

</html>
