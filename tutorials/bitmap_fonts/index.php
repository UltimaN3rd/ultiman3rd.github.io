<!DOCTYPE html>
<html lang="en-US">

<!-- Code syntax highlighting generated using codebeautify.org/code-highlighter with the "Stackoverflow Dark" style. -->

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

<h4>Draw Text with Bitmap Fonts</h4>

This tutorial is also available as a <a href="https://www.youtube.com/watch?v=3RtO01mcjYI" target="_blank">video.</a>

<a href="tutorial bitmap fonts.zip" download>Download the code here.</a> It's bundled as a complete project which opens a graphical window and draws text to the screen, compilable for Windows and Linux.

In this tutorial I’ll show you how to draw text to a graphical window using simple bitmap fonts, where each character is pre-drawn in a bitmap. Compared to TrueType fonts, bitmap fonts are simpler to program, and make it easier to create unique text graphics, like Rayman 1.

<img src="videos/complex.gif">
Forgive my lack of artistry!

Note: I’m compiling this code with GCC.
Note: The bitmap_font.h header #includes "sprite.h" which is my own simple sprite library, including loading .bmp and .tga files and drawing them to the screen. You can use that library, or substitute your own image loading and drawing code.

<hr>
<h4><a id="Code Walkthrough" style="color:rgb(255,255,255);">Code Walkthrough</a></h4>

The code provided above loads a bitmap containing the font characters, trims the blank pixels, and loads a properties.txt file containing information about the font. Before we get to that, though, I'll start with a simpler version of bitmap fonts. We'll just load in the font bitmap and draw all the characters with the same width and height, like so:

<img src="videos/simple.gif">

Before we get to the code, a bit of background knowledge. In C, strings are a series of bytes each representing 1 ASCII character, and ending with a 0 byte, or the '\0' or NULL character. Here's an ASCII character table:

<img src="images/ascii table.png">

As you can see, only the characters from 33 through 126 are visible, so we need 94 character sprites. We could store all 94 sprites as individual images to be loaded, but I prefer to store them all in a single image, which will be a 10x10 grid of equally sized character cells, with 6 left unused.

<code><span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">#<span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">pragma</span> once</span>

<span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">#<span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&lt;stdint.h&gt;</span></span>
<span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">#<span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&lt;stdbool.h&gt;</span></span>
<span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">#<span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&quot;sprite.h&quot;</span></span>

<span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">#<span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">define</span> BITMAP_FONT_FIRST_VISIBLE_CHAR 33</span>
<span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">#<span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">define</span> BITMAP_FONT_LAST_VISIBLE_CHAR 126</span>
<span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">#<span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">define</span> BITMAP_FONT_NUM_VISIBLE_CHARS (BITMAP_FONT_LAST_VISIBLE_CHAR - BITMAP_FONT_FIRST_VISIBLE_CHAR + 1)</span>

<span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">typedef</span> <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">struct</span> {
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> character_width, character_height;
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">pixel_t</span> *pixels;
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">sprite_t</span> bitmaps[BITMAP_FONT_NUM_VISIBLE_CHARS];
} <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">font_t</span>;

<span style="color:rgb(153, 153, 153); font-weight:400;background:rgba(0, 0, 0, 0);">// Pass in the font image file, either a targa or 32-bit bitmap with alpha channel</span>
<span style="color:rgb(153, 153, 153); font-weight:400;background:rgba(0, 0, 0, 0);">// Returns false on failure and true on success</span>
<span style="color:rgb(255, 255, 255); font-weight:400;background:rgba(0, 0, 0, 0);"><span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">bool</span> <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">font_Load</span> <span style="color:rgb(255, 255, 255); font-weight:400;background:rgba(0, 0, 0, 0);">(<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">font_t</span> *font, <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">char</span> *bitmap_or_targa)</span> </span>{
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">bool</span> return_value = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">false</span>;

    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">sprite_t</span> sprite;
    <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">if</span> (!<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">SpriteLoad</span> (&amp;sprite, bitmap_or_targa)) {
        <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">PRINT_ERROR</span> (<span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&quot;Failed to load font: %s&quot;</span>, bitmap_or_targa);
        <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">goto</span> LoadReturn;
    }

    <span style="color:rgb(153, 153, 153); font-weight:400;background:rgba(0, 0, 0, 0);">// Convert bitmap to individual character bitmaps</span>
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">sprite_t</span> *bmp;
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> character_width, character_height;
    character_width = sprite.width / <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">10</span>;
    character_height = sprite.height / <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">10</span>;
    font-&gt;character_width = character_width;
    font-&gt;character_height = character_height;
    font-&gt;pixels = (<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">pixel_t</span>*)<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">malloc</span> (character_width * character_height * BITMAP_FONT_NUM_VISIBLE_CHARS * <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">sizeof</span>(<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">pixel_t</span>));

    <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">if</span> (!font-&gt;pixels) {
        <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">PRINT_ERROR</span> (<span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&quot;Failed to allocate %dx%d font pixels&quot;</span>, sprite.w, sprite.h);
        <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">goto</span> LoadFreeSprite;
    }
    
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> x, y;
    <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">for</span> (<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> i = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">0</span>; i &lt; BITMAP_FONT_NUM_VISIBLE_CHARS; ++i) {
        y = i / <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">10</span>;
        x = i % <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">10</span>;

        bmp = &amp;font-&gt;bitmaps[i];
        bmp-&gt;w = character_width;
        bmp-&gt;h = character_height;
        bmp-&gt;p = &amp;font-&gt;pixels[i * character_width * character_height];
        
        <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">for</span> (<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> sy = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">0</span>; sy &lt; character_height; ++sy) {
            <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">memcpy</span> (&amp;bmp-&gt;p[sy * character_width], &amp;sprite.pixels[x * character_width + (y * character_height + sy) * sprite.width], character_width * <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">sizeof</span>(<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">pixel_t</span>));
        }
    }

    return_value = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">true</span>;

LoadFreeSprite:     <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">free</span> (sprite.p);
LoadReturn:         <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">return</span> return_value;
}

<span style="color:rgb(255, 255, 255); font-weight:400;background:rgba(0, 0, 0, 0);"><span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">inline</span> <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">void</span> <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">font_Free</span> <span style="color:rgb(255, 255, 255); font-weight:400;background:rgba(0, 0, 0, 0);">(<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">font_t</span> *font)</span> </span>{
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">free</span> (font-&gt;pixels);
}

<span style="color:rgb(255, 255, 255); font-weight:400;background:rgba(0, 0, 0, 0);"><span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">void</span> <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">font_Write</span> <span style="color:rgb(255, 255, 255); font-weight:400;background:rgba(0, 0, 0, 0);">(<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">font_t</span> *font, <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">sprite_t</span> *destination, <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> left, <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> top, <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">char</span> *text)</span> </span>{
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">char</span> c = *(text++);
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> i;
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> x = left, y = top - font-&gt;character_height; <span style="color:rgb(153, 153, 153); font-weight:400;background:rgba(0, 0, 0, 0);">// Current x and y, updated as we draw each character</span>
    <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">while</span> (c != <span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&#x27;\0&#x27;</span>) {
        <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">switch</span> (c) {
            <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">case</span> <span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&#x27; &#x27;</span>: x += font-&gt;character_width; <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">break</span>;

            <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">case</span> <span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&#x27;\n&#x27;</span>: {
                x = left;
                y -= font-&gt;character_height;
            } <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">break</span>;

            <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">default</span>: {
                i = c - BITMAP_FONT_FIRST_VISIBLE_CHAR;
                <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">if</span> (i &gt;= <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">0</span> &amp;&amp; i &lt; BITMAP_FONT_NUM_VISIBLE_CHARS) {
                    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">BlitAlpha10</span> (&amp;font-&gt;bitmaps[i], destination, x, y);
                    x += font-&gt;character_width;
                } <span style="color:rgb(153, 153, 153); font-weight:400;background:rgba(0, 0, 0, 0);">// If character wasn&#x27;t in the range, then we ignore it.</span>
            } <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">break</span>;
        }
        c = *(text++);
    }
}</code>

Let's get into the code.

<code><span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">typedef</span> <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">struct</span> {
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> character_width, character_height;
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">pixel_t</span> *pixels;
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">sprite_t</span> bitmaps[BITMAP_FONT_NUM_VISIBLE_CHARS];
} <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">font_t</span>;</code>

The font_t type contains:
The width and height of the characters, which will be the same for all characters.
A pointer to the memory which will be allocated to hold the characters' pixel data.
An array of sprite_t structures, one for each character, which can be used for drawing them to the window. Their pixel pointers will point to a section of the font.pixels memory.

<code><span style="color:rgb(153, 153, 153); font-weight:400;background:rgba(0, 0, 0, 0);">// Pass in the font image file, either a targa or 32-bit bitmap with alpha channel</span>
<span style="color:rgb(153, 153, 153); font-weight:400;background:rgba(0, 0, 0, 0);">// Returns false on failure and true on success</span>
<span style="color:rgb(255, 255, 255); font-weight:400;background:rgba(0, 0, 0, 0);"><span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">bool</span> <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">font_Load</span> <span style="color:rgb(255, 255, 255); font-weight:400;background:rgba(0, 0, 0, 0);">(<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">font_t</span> *font, <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">char</span> *bitmap_or_targa)</span> </span>{
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">bool</span> return_value = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">false</span>;

    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">sprite_t</span> sprite;
    <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">if</span> (!<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">SpriteLoad</span> (&amp;sprite, bitmap_or_targa)) {
        <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">PRINT_ERROR</span> (<span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&quot;Failed to load font: %s&quot;</span>, bitmap_or_targa);
        <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">goto</span> LoadReturn;
    }</code>
This is the beginning of the font_Load function. You pass in the font structure to be filled out and the bitmap directory/filename. Here I'm using my sprite.h library to load the image. You can use it too, or substitute your own.

Here is the font bitmap I'm using in this tutorial:
<img src="images/font.bmp">

<code>    <span style="color:rgb(153, 153, 153); font-weight:400;background:rgba(0, 0, 0, 0);">// Convert bitmap to individual character bitmaps</span>
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">sprite_t</span> *bmp;
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> character_width, character_height;
    character_width = sprite.width / <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">10</span>;
    character_height = sprite.height / <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">10</span>;
    font-&gt;character_width = character_width;
    font-&gt;character_height = character_height;
    font-&gt;pixels = (<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">pixel_t</span>*)<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">malloc</span> (character_width * character_height * BITMAP_FONT_NUM_VISIBLE_CHARS * <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">sizeof</span>(<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">pixel_t</span>));

    <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">if</span> (!font-&gt;pixels) {
        <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">PRINT_ERROR</span> (<span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&quot;Failed to allocate %dx%d font pixels&quot;</span>, sprite.w, sprite.h);
        <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">goto</span> LoadFreeSprite;
    }</code>
We set the font's character width/height to 1/10th of the font image's width/height, since it's a 10x10 grid of characters. Then we allocate the amount of memory needed to hold all 94 characters.

<code>    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> x, y;
    <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">for</span> (<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> i = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">0</span>; i &lt; BITMAP_FONT_NUM_VISIBLE_CHARS; ++i) {
        y = i / <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">10</span>;
        x = i % <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">10</span>;

        bmp = &amp;font-&gt;bitmaps[i];
        bmp-&gt;w = character_width;
        bmp-&gt;h = character_height;
        bmp-&gt;p = &amp;font-&gt;pixels[i * character_width * character_height];
        
        <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">for</span> (<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> sy = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">0</span>; sy &lt; character_height; ++sy) {
            <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">memcpy</span> (&amp;bmp-&gt;p[sy * character_width], &amp;sprite.pixels[x * character_width + (y * character_height + sy) * sprite.width], character_width * <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">sizeof</span>(<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">pixel_t</span>));
        }
    }

    return_value = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">true</span>;

LoadFreeSprite:     <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">free</span> (sprite.p);
LoadReturn:         <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">return</span> return_value;
}</code>
We loop through the characters, setting up each character sprite's width and height, and pointing to the next section of the font.pixels memory. Then we copy the character from the loaded image, row-by-row, into the font.pixels memory.

Lastly we exit the function. Notice the error handling style, where the initial return value was set to false to signify failure, then only set to true at the end when everything is completed. We also have two goto labels which are used to skip to the end when errors happen.

With that, our font is loaded and just needs to be drawn to the screen.

<code><span style="color:rgb(255, 255, 255); font-weight:400;background:rgba(0, 0, 0, 0);"><span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">void</span> <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">font_Write</span> <span style="color:rgb(255, 255, 255); font-weight:400;background:rgba(0, 0, 0, 0);">(<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">font_t</span> *font, <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">sprite_t</span> *destination, <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> left, <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> top, <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">char</span> *text)</span> </span>{
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">char</span> c = *(text++);
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> i;
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> x = left, y = top - font-&gt;character_height; <span style="color:rgb(153, 153, 153); font-weight:400;background:rgba(0, 0, 0, 0);">// Current x and y, updated as we draw each character</span></code>
So we pass in the font, the destination sprite, the top-left corner of our drawing area and a C string. The variable, c, will represent the current character, so we dereference the text pointer and post-increment it so that next time we check it we'll get the next character. We also start x at the left, and y at the bottom of the top line. My sprites draw from bottom to top - if yours draw the other way around you might keep y at top.

<code>	while</span> (c != <span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&#x27;\0&#x27;</span>) {
        <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">switch</span> (c) {
            <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">case</span> <span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&#x27; &#x27;</span>: x += font-&gt;character_width; <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">break</span>;

            <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">case</span> <span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&#x27;\n&#x27;</span>: {
                x = left;
                y -= font-&gt;character_height;
            } <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">break</span>;

            <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">default</span>: {
                i = c - BITMAP_FONT_FIRST_VISIBLE_CHAR;
                <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">if</span> (i &gt;= <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">0</span> &amp;&amp; i &lt; BITMAP_FONT_NUM_VISIBLE_CHARS) {
                    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">BlitAlpha10</span> (&amp;font-&gt;bitmaps[i], destination, x, y);
                    x += font-&gt;character_width;
                } <span style="color:rgb(153, 153, 153); font-weight:400;background:rgba(0, 0, 0, 0);">// If character wasn&#x27;t in the range, then we ignore it.</span>
            } <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">break</span>;
        }
        c = *(text++);
    }
}</code>
We loop until we reach the NULL character, represented by '\0'. For every character we switch, checking for the special characters, space and newline. On space we just add a character's width to x, and on newline we reset x to left and subtract the character height from y.
For other characters, we subtract the first visible character from it and see if it lands in the range of 0 to 93. If so, we draw the corresponding character sprite and add the width to x.
At the end of the loop we get the character currently pointed to by text and increment the pointer.
Once we hit the NULL character, the loop will terminate and we will have drawn out all the text.

That's a simple way to draw text in a graphical window. It doesn't look pretty but it gets the job done.

<hr>
<h4>Adding features</h4>

Now let's spruce things up by giving the characters their own individual sizes and descent and customizing the font's space width and line height. For reference, we're now exploring the code of bitmap_font.h inside the zip file downloadable at the top of the page.

<code><span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">typedef</span> <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">struct</span> {
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> line_height;
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> space_width;
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">pixel_t</span> *pixels;
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">sprite_t</span> bitmaps[BITMAP_FONT_NUM_VISIBLE_CHARS];
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int8_t</span> descent[BITMAP_FONT_NUM_VISIBLE_CHARS];
} <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">font_t</span>;</code>
To the font type, I've added line_height and space_width, and an array of descent values, one for each character.

This new font format contains two files inside a folder, so to load the font you now call font_Load() passing the name of the folder, and inside the function we'll figure out the filenames of the font files inside.

<code><span style="color:rgb(153, 153, 153); font-weight:400;background:rgba(0, 0, 0, 0);">// Pass in the folder which contains the font files: font.bmp/tga and properties.txt</span>
<span style="color:rgb(153, 153, 153); font-weight:400;background:rgba(0, 0, 0, 0);">// Returns false on failure and true on success</span>
<span style="color:rgb(255, 255, 255); font-weight:400;background:rgba(0, 0, 0, 0);"><span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">bool</span> <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">font_Load</span> <span style="color:rgb(255, 255, 255); font-weight:400;background:rgba(0, 0, 0, 0);">(<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">font_t</span> *font, <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">char</span> *directory)</span> </span>{
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">bool</span> return_value = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">false</span>;

    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> directory_length = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">strlen</span> (directory);
    <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">if</span> (directory_length &gt; <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">512</span>) {
        <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">PRINT_ERROR</span> (<span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&quot;Directory exceeds 512 characters: %s&quot;</span>, directory);
        <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">goto</span> LoadFontReturn;
    }

    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">char</span> folder_name[<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">512</span>];
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">char</span> last_char = directory[directory_length<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">-1</span>]; <span style="color:rgb(153, 153, 153); font-weight:400;background:rgba(0, 0, 0, 0);">// Go back from the NULL</span>
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">bool</span> ending_slash = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">false</span>;
    <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">if</span> (last_char == <span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&#x27;\&#x27;&#x27;</span> || last_char == <span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&#x27;/&#x27;</span>) {
        ending_slash = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">true</span>;
    }
	<span style="color:rgb(153, 153, 153); font-weight:400;background:rgba(0, 0, 0, 0);">// Copy the directory, adding a slash at the end if it&#x27;s missing.</span>
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">char</span> directory_fixed[<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">513</span>];
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">sprintf</span> (directory_fixed, <span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&quot;%s%c&quot;</span>, directory, ending_slash ? <span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&#x27;\0&#x27;</span> : <span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&#x27;/&#x27;</span>);</code>
So we start at the end of the passed directory string, moving back 1 from the NULL character to the last real character. We check if it's a slash, then copy the directory to a new string buffer, directory_fixed, adding the slash if it was missing. Doing things like this allows you to be less strict about the format of a string passed into the function and makes the code easier to use, so as long as it's not performance-critical I think it's usually worth doing.

<code>    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">char</span> filename[<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">600</span>];
    
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">sprintf</span> (filename, <span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&quot;%sfont.bmp&quot;</span>, directory_fixed);
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">sprite_t</span> bitmap;
    <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">if</span> (!<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">SpriteLoad</span> (&amp;bitmap, filename)) {
        <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">sprintf</span> (filename, <span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&quot;%sfont.tga&quot;</span>, directory_fixed);
        <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">if</span> (!<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">SpriteLoad</span> (&amp;bitmap, filename)) {
            <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">PRINT_ERROR</span> (<span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&quot;Failed to load font bitmap: %s. Attempted both .bmp and .tga.&quot;</span>, filename);
            <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">goto</span> LoadFontReturn;
        }
    }</code>
We create a new string buffer a bit longer than the previous ones to make room for the entire directory, plus an extra filename at the end. sprintf allows us to write to a string buffer with the same style and formatting as printf or fprintf. So we use the directory_fixed string from before and add "font.bmp". Then we load font.bmp with a standard image loading function. I decided to also handle targa files, so if we fail to load font.bmp, we try again with font.tga.

<code>    <span style="color:rgb(153, 153, 153); font-weight:400;background:rgba(0, 0, 0, 0);">// Convert bitmap to individual character bitmaps</span>
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">sprite_t</span> *bmp;
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> width, height;
    width = bitmap.width / <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">10</span>;
    height = bitmap.height / <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">10</span>;
    font-&gt;space_width = width/<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">2</span>; font-&gt;line_height = height + <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">4</span>; <span style="color:rgb(153, 153, 153); font-weight:400;background:rgba(0, 0, 0, 0);">// Just defaults in case they aren&#x27;t set inside properties.txt</span>
    font-&gt;pixels = (<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">pixel_t</span>*) <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">malloc</span> (width * height * BITMAP_FONT_NUM_VISIBLE_CHARS * <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">sizeof</span> (<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">pixel_t</span>));
    <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">if</span> (!font-&gt;pixels) {
        <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">PRINT_ERROR</span> (<span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&quot;Failed to allocate font pixels: %s&quot;</span>, directory);
        <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">free</span> (bitmap.p);
        <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">goto</span> LoadFontReturn;
    }</code>
Here we get set up for extracting the individual character sprites from the font image. Each character is inside a cell 1/10th the width and height of the image. We allocate the maximum amount of memory the font might need, which is 94 times that 1/10th width and height.

<code>    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> x, y;
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> cumulative_pixels = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">0</span>;
    <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">for</span> (<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> i = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">0</span>; i &lt; BITMAP_FONT_NUM_VISIBLE_CHARS; ++i) {
        y = i / <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">10</span>;
        x = i % <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">10</span>;
        <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">struct</span> {
            <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> left, right, bottom, top, width, height;
        } source;

        <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">for</span> (<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> xx = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">0</span>; xx &lt; width; ++xx) {
            <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">for</span> (<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> yy = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">0</span>; yy &lt; height; ++yy) {
                <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">if</span> (bitmap.pixels[(yy + y * height) * bitmap.width + x * width + xx].a != <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">0</span>) {
                    source.left = xx;
                    yy = height;
                    xx = width;
                }
            }
        }
        <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">for</span> (<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> xx = width - <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">1</span>; xx &gt;= <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">0</span>; --xx) {
            <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">for</span> (<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> yy = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">0</span>; yy &lt; height; ++yy) {
                <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">if</span> (bitmap.pixels[(yy + y * height) * bitmap.width + x * width + xx].a != <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">0</span>) {
                    source.right = xx;
                    yy = height;
                    xx = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">-1</span>;
                }
            }
        }
        <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">for</span> (<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> yy = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">0</span>; yy &lt; height; ++yy) {
            <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">for</span> (<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> xx = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">0</span>; xx &lt; width; ++xx) {
                <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">if</span> (bitmap.pixels[(yy + y * height) * bitmap.width + x * width + xx].a != <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">0</span>) {
                    source.bottom = yy;
                    xx = width;
                    yy = height;
                }
            }
        }
        <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">for</span> (<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> yy = height<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">-1</span>; yy &gt;= <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">0</span>; --yy) {
            <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">for</span> (<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> xx = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">0</span>; xx &lt; width; ++xx) {
                <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">if</span> (bitmap.pixels[(yy + y * height) * bitmap.width + x * width + xx].a != <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">0</span>) {
                    source.top = yy;
                    xx = width;
                    yy = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">-1</span>;
                }
            }
        }</code>
We loop through all 94 characters, finding the smallest rectangle which contains each character sprite. These four for loops each check a different side: left, right, bottom and top. Taking the first one as an example, we go column by column, starting at the leftmost column of this character's cell, checking each pixel. If we encounter an opaque pixel, we've found the leftmost column and can set source.left to the current column, then set xx and yy such that both for loops will break. The same is done for the other sides.

<code>        source.width = source.right - source.left + <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">1</span>;
        source.height = source.top - source.bottom + <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">1</span>;
        bmp = &amp;font-&gt;bitmaps[i];
        bmp-&gt;w = source.width;
        bmp-&gt;h = source.height;
        font-&gt;descent[i] = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">0</span>;
		
        bmp-&gt;p = font-&gt;pixels + cumulative_pixels;
        <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">for</span> (<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> sy = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">0</span>; sy &lt; source.height; ++sy) {
            <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">memcpy</span> (&amp;bmp-&gt;p[sy * bmp-&gt;w], &amp;bitmap.pixels[(sy + source.bottom + y * height) * bitmap.width + x * width + source.left], bmp-&gt;w * <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">sizeof</span>(<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">pixel_t</span>));
        }
        cumulative_pixels += bmp-&gt;width * bmp-&gt;height;
    }</code>
We calculate the width and height of this character's sprite by subtracting left from right, and bottom from top, and adding one. Then we set the bmp sprite pointer to point to this character's sprite in the font data structure, and assign its width and height. We also set this character's descent to 0 by default.
The character's sprite will point to the next section of font pixel memory, which will initially be the very beginning of said memory. Then we loop through a number of pixel rows equal to the character's height, copying each row from the font bitmap to this character's section of the font pixels. Finally we increase the cumulative_pixels value by the width x height of this character sprite, to index the next blank section of font pixel memory for the next character.

<code>    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">free</span> (bitmap.p);

    font-&gt;pixels = (<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">pixel_t</span>*)<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">realloc</span> (font-&gt;pixels, cumulative_pixels * <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">sizeof</span> (<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">pixel_t</span>));
    
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> pixel_offset = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">0</span>;
    <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">for</span> (<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> i = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">0</span>; i &lt; BITMAP_FONT_NUM_VISIBLE_CHARS; ++i) {
        font-&gt;bitmaps[i].pixels = font-&gt;pixels + pixel_offset;
        pixel_offset += font-&gt;bitmaps[i].w * font-&gt;bitmaps[i].h;
    }</code>
We've copied out the character sprites so we no longer need the font bitmap. Now we reallocate the font pixel memory to be only large enough to fit the cumulative number of pixels of all the character sprites combined. Even when guaranteed to reallocate a smaller - or the same - amount of memory, realloc may still return a pointer to a completely different section of memory, so we loop through all of the character sprites, setting their pixel pointers to point to the correct part of the new font pixel memory.

At this point we've got all of our character sprites trimmed of excess transparent pixels. Now let's load the font properties. These properties will be stored in a text file, "properties.txt" inside the font folder. It will be laid out line-by-line, where most lines begin with a visible character whose properties will then be listed. So for the character j with a descent of 9 pixels, the line will be:
j d9
There will also be one line beginning with a space, followed by the font properties of space width and line height, like so:
 w12 h24
You may not be able to see the space there, but you get the point aye? Alright, back to the code.

<code>    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">sprintf</span> (filename, <span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&quot;%sproperties.txt&quot;</span>, directory_fixed);

    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">char</span> *contents;
    contents = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">ReadEntireFile</span> (filename, <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">NULL</span>);
    <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">if</span> (contents == <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">NULL</span>) {
        <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">PRINT_ERROR</span> (<span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&quot;Failed to read file: %s&quot;</span>, filename);
        <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">goto</span> LoadFontReturn;
    }</code>
Just like with font.bmp, we sprintf the directory and add "properties.txt". This file will contain the font's space width and line height, and the descent of any characters that need it to be other than 0.

<code>    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">char</span> *c = contents;
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> i;
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">char</span> property;
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> value;
    <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">while</span> (*c != <span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&#x27;\0&#x27;</span>) {</code>
We start at the beginning of the file contents, and loop until we hit the null character.

<code>        i = *c;
        <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">if</span> (i &gt;= BITMAP_FONT_FIRST_VISIBLE_CHAR &amp;&amp; i &lt;= BITMAP_FONT_LAST_VISIBLE_CHAR) {
            i -= BITMAP_FONT_FIRST_VISIBLE_CHAR;
            ++c; <span style="color:rgb(153, 153, 153); font-weight:400;background:rgba(0, 0, 0, 0);">// Skip the letter to get to the space before first property</span>
            <span style="color:rgb(153, 153, 153); font-weight:400;background:rgba(0, 0, 0, 0);">// Now we&#x27;re at the list of properties for this letter [i]</span>
            <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">do</span> {
                ++c;
                property = *c;
                value = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">atoi</span> (c+<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">1</span>);
                <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">switch</span> (property) {
                    <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">case</span> <span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&#x27;d&#x27;</span>: font-&gt;descent[i] = value; <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">break</span>;
                    <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">default</span>: {
                        <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">PRINT_ERROR</span> (<span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&quot;Reading font \&#x27;%s\&#x27; encountered invalid property \&#x27;%c\&#x27; in line of character \&#x27;%c\&#x27;&quot;</span>, directory, property, (<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">char</span>)(i + BITMAP_FONT_FIRST_VISIBLE_CHAR));
                        <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">goto</span> LoadFontFreeContents;
                    }
                }
                c = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">StringSkipNonWhiteSpace</span> (c); <span style="color:rgb(153, 153, 153); font-weight:400;background:rgba(0, 0, 0, 0);">// Skip the property value</span>
            } <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">while</span> (*c == <span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&#x27; &#x27;</span>); <span style="color:rgb(153, 153, 153); font-weight:400;background:rgba(0, 0, 0, 0);">// If it&#x27;s a space, there&#x27;s another property</span>
            c = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">StringSkipWhiteSpace</span> (c); <span style="color:rgb(153, 153, 153); font-weight:400;background:rgba(0, 0, 0, 0);">// Otherwise we need to get to the next line and the next letter</span>
        }</code>
If the present character is one of our visible characters, then this line contains its properties. Here's an example line:
j d9
So we increment c to get past the first character, then loop, reading any listed properties. I've only created one property but I wrote the code in a way that makes it easy to add more. Each iteration we increment past the space, read the character which represents a property, and the next character(s) we convert to a number with atoi. Then we switch on the character. Currently we only have descent, represented by 'd', then the number of pixels to descend.
After reading a property, the c character pointer is still pointing to the property letter (d). So we use this StringSkipNonWhiteSpace function to skip all non-white-space characters. Now c will either be at the end of the line, pointing to a '\n', or there'll be a space followed by another property, so the while checks if we should repeat for another property. If we're done with the line, we skip the whitespace (\n). At this point we proceed to the end of our if else chain and go back to the top of the while (*c != '\0') loop.

If the character was not a visible character, then...

<code>        <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">else</span> <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">if</span> (*c == <span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&#x27; &#x27;</span>) { <span style="color:rgb(153, 153, 153); font-weight:400;background:rgba(0, 0, 0, 0);">// Font properties</span>
            <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">do</span> {
                ++c;
                property = *c;
                value = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">atoi</span> (c+<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">1</span>);
                <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">switch</span> (property) {
                    <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">case</span> <span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&#x27;w&#x27;</span>: font-&gt;space_width = value; <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">break</span>;
                    <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">case</span> <span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&#x27;h&#x27;</span>: font-&gt;line_height = value; <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">break</span>;
                    <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">default</span>: {
                        <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">PRINT_ERROR</span> (<span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&quot;Reading font \&#x27;%s\&#x27; encountered invalid property \&#x27;%c\&#x27; in line of font properties (line which starts with space).&quot;</span>, directory, property);
                        <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">goto</span> LoadFontFreeContents;
                    }
                }
                c = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">StringSkipNonWhiteSpace</span> (c); <span style="color:rgb(153, 153, 153); font-weight:400;background:rgba(0, 0, 0, 0);">// Skip the property value</span>
            } <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">while</span> (*c == <span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&#x27; &#x27;</span>); <span style="color:rgb(153, 153, 153); font-weight:400;background:rgba(0, 0, 0, 0);">// If it&#x27;s a space, there&#x27;s another property</span>
            c = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">StringSkipWhiteSpace</span> (c); <span style="color:rgb(153, 153, 153); font-weight:400;background:rgba(0, 0, 0, 0);">// Otherwise we need to get to the next line and the next letter</span>
        }</code>
If the first character on the line was a space, then this line has our font properties. Very similar to our character property code, we get a property and value, handling 'w' for space width and 'h' for line height.

<code>        <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">else</span> {
            <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">PRINT_ERROR</span> (<span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&quot;Invalid font file. Format for each line should always be: \&quot;&lt;ascii character&gt; p&lt;property number&gt;\&quot;. Error encountered here: %s&quot;</span>, c);
            <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">goto</span> LoadFontFreeContents;
        }
    }</code>
Finally, a line starting with any other character is invalid.

<code>    return_value = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">true</span>;

LoadFontFreeContents:   <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">free</span> (contents);
LoadFontReturn:         <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">return</span> return_value;
}</code>
If we made it all the way through everything, then we can set return_value to true. Happy days! Otherwise, we have these goto labels for early exits when errors are encountered. If you notice that we don't have one for freeing the font image, that's because we can only error in one place between loading and freeing that image, so I just added the free() code in that spot before gotoing. Anyway, we free the properties file contents and return.

So the font bitmap and properties files have been loaded from a folder! Loading was the hard part, and now we just need a couple of updates to the drawing code.

<code><span style="color:rgb(255, 255, 255); font-weight:400;background:rgba(0, 0, 0, 0);"><span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">void</span> <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">font_Write</span> <span style="color:rgb(255, 255, 255); font-weight:400;background:rgba(0, 0, 0, 0);">(<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">font_t</span> *font, <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">sprite_t</span> *destination, <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> left, <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> top, <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">char</span> *text)</span> </span>{
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">char</span> c = *(text++);
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> i;
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> x = left, y = top - font-&gt;line_height; <span style="color:rgb(153, 153, 153); font-weight:400;background:rgba(0, 0, 0, 0);">// Current x and y, updated as we draw each character</span>
    <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">while</span> (c != <span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&#x27;\0&#x27;</span>) {
        <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">switch</span> (c) {
            <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">case</span> <span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&#x27; &#x27;</span>: x += font-&gt;space_width; <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">break</span>;

            <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">case</span> <span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&#x27;\n&#x27;</span>: {
                x = left;
                y -= font-&gt;line_height;
            } <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">break</span>;

            <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">default</span>: {
                i = c - BITMAP_FONT_FIRST_VISIBLE_CHAR;
                <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">if</span> (i &gt;= <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">0</span> &amp;&amp; i &lt; BITMAP_FONT_NUM_VISIBLE_CHARS) {
                    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">BlitAlpha10</span> (&amp;font-&gt;bitmaps[i], destination, x, y - font-&gt;descent[i]);
                    x += font-&gt;bitmaps[i].width + <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">1</span>;
                } <span style="color:rgb(153, 153, 153); font-weight:400;background:rgba(0, 0, 0, 0);">// If character wasn&#x27;t in the range, then we ignore it.</span>
            } <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">break</span>;
        }
        c = *(text++);
    }
}</code>
When calling this function we pass in the top-left corner where the text will be drawn, so x is set to left, but y is top - line_height. In my sprite drawing library, sprites are drawn bottom-up with Cartesian coordinates. If yours are drawn top-down you can just set y to top.
We start going through the text string character-by-character. We handle space and newline with our new font properties, and when drawing a visibile character we increment x by its width + 1.
See? It was all in the loading. The drawing barely needed an update after all that! At this point we've got some pretty natural-looking text drawing to the screen:

<img src="videos/complex.gif">

One last thing. I created this extra drawing function to automatically handle word wrapping - moving to the next line automatically whenever a word would be too wide to fit a set area.
<code><span style="color:rgb(255, 255, 255); font-weight:400;background:rgba(0, 0, 0, 0);"><span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">void</span> <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">font_WriteWrap</span> <span style="color:rgb(255, 255, 255); font-weight:400;background:rgba(0, 0, 0, 0);">(<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">font_t</span> *font, <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">sprite_t</span> *destination, <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> left, <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> top, <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> right, <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">char</span> *text)</span> </span>{
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">char</span> c = *text;
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> i;
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> x = left, y = top - font-&gt;line_height; <span style="color:rgb(153, 153, 153); font-weight:400;background:rgba(0, 0, 0, 0);">// Current x and y, updated as we draw each character</span>
    <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">bool</span> new_word = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">true</span>;
    <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">while</span> (c != <span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&#x27;\0&#x27;</span>) {
        i = c - BITMAP_FONT_FIRST_VISIBLE_CHAR;
        <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">if</span> (i &gt;= <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">0</span> &amp;&amp; i &lt; BITMAP_FONT_NUM_VISIBLE_CHARS) {
            <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">if</span> (new_word) {
                new_word = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">false</span>;
                <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> word_width = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">0</span>;
                <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">char</span> *word = text;
                <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">do</span> {
                    word_width += font-&gt;bitmaps[i].width + <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">1</span>;
                    i = *(++word) - BITMAP_FONT_FIRST_VISIBLE_CHAR;
                } <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">while</span> (i &gt;= <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">0</span> &amp;&amp; i &lt; BITMAP_FONT_NUM_VISIBLE_CHARS);
                i = c - BITMAP_FONT_FIRST_VISIBLE_CHAR;
                word_width -= <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">1</span>; <span style="color:rgb(153, 153, 153); font-weight:400;background:rgba(0, 0, 0, 0);">// Don&#x27;t need the extra pixel on the right of the word</span>
                <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">if</span> (x + word_width &gt; right) { <span style="color:rgb(153, 153, 153); font-weight:400;background:rgba(0, 0, 0, 0);">// The word is too wide so we need to start the next line</span>
                    x = left;
                    y -= font-&gt;line_height;
                }
            }
            <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">if</span> (!new_word) {
                <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">BlitAlpha10</span> (&amp;font-&gt;bitmaps[i], destination, x, y - font-&gt;descent[i]);
                x += font-&gt;bitmaps[i].width + <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">1</span>;
            }
        }
        <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">else</span> { <span style="color:rgb(153, 153, 153); font-weight:400;background:rgba(0, 0, 0, 0);">// Character is not in the visible set</span>
            new_word = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">true</span>;
            <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">switch</span> (c) {
                <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">case</span> <span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&#x27; &#x27;</span>: {
                    x += font-&gt;space_width;
                } <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">break</span>;
                <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">case</span> <span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&#x27;\n&#x27;</span>: {
                    x = left;
                    y -= font-&gt;line_height;
                } <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">break</span>;
                <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">default</span>: <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">break</span>;
            }
        }
        c = *++text;
    }
}</code>
This looks quite a bit more complicated, but it's not so bad. All we're doing is, whenever we hit a new word, we loop through characters until we hit a non-visible character, adding each character's width and finally checking if the current x position + word width exceeds the right edge passed into the function. If it does, we basically do a newline right there before continuing to draw the characters from the start of the word. new_word is initially set to true and is set to false whenever we evaluate the width of a word, then set to true again whenever we hit non-visible characters. That includes ' ' and '\n', but also any other non-visible ASCII character that we don't specifically handle.

Unlike my other tutorials, I won't say that's all there is to rendering text! But with this relatively simple code, you can draw your own very cool fonts and print good-looking text to the screen. If you make a font and use this code to render text in your program I'd love to see it, so feel free to send me an e-mail with the contact link at the bottom of the page!

<hr>
If you've got questions about any of the code feel free to e-mail me or comment on the <a href="https://www.youtube.com/watch?v=3RtO01mcjYI" target="_blank">youtube video</a>. I'll try to answer them, or someone else might come along and help you out. If you've got any extra tips about how this code can be better or just more useful info about the code, let me know so I can update the tutorial.

Cheers.

</article>

</body>

<?php include "/blog/blogbottom.html" ?>

<?php include "footer_common.html" ?>

</html>
