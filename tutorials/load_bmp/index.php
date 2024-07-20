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

<h4>Load BMP Files with the C Standard Library</h4>

This tutorial is also available as a <a href="https://www.youtube.com/watch?v=hNi_MEZ8X10" target="_blank">video.</a>

In this tutorial I’ll show you how to load and display 32-bit bitmap image files with transparency with the C standard library.

Note: I'm doing this inside a basic Win32 program, linking to gdi32, but no extra linking is required for the bitmap code itself.
Note: I’m compiling this code with GCC.
Note: Click any of the <a>hyperlinked words</a> to visit the documentation page for them.
Note: I've dimmed all the code not relevant to the tutorial.

<a href="main.c" download>main.c</a>
<code><span class="fadecode">#define UNICODE
#define _UNICODE
#include &lt;windows.h&gt;
#include &lt;stdbool.h&gt;
#include &lt;stdint.h&gt;</span>
<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;">&lt;<a href="https://www.tutorialspoint.com/c_standard_library/stdio_h.htm" target="_blank">stdio.h</a>&gt;</span></span>

<span class="fadecode">#define PRINT_ERROR(a, args...) printf(&quot;ERROR %s() %s Line %d: &quot; a &quot;\n&quot;, __FUNCTION__, __FILE__, __LINE__, ##args);

bool quit = false;
HWND window_handle;
HDC device_context;
HBITMAP bitmap;
BITMAPINFO bitmap_info;</span>

<span style="color:rgb(136, 174, 206); font-weight:400;">typedef</span> <span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">struct</span> {</span>
	<span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">union</span> {</span> <span style="color:rgb(136, 174, 206); font-weight:400;">int</span> width,  w; };
	<span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">union</span> {</span> <span style="color:rgb(136, 174, 206); font-weight:400;">int</span> height, h; };
	<span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">union</span> {</span> <span style="color:rgb(136, 174, 206); font-weight:400;">uint32_t</span> *pixels, *p; };
} <span style="color:rgb(136, 174, 206); font-weight:400;">sprite_t</span>;

<span style="color:rgb(136, 174, 206); font-weight:400;">sprite_t</span> frame;

<span class="fadecode">LRESULT CALLBACK WindowProcessMessage(HWND window_handle, UINT message, WPARAM wParam, LPARAM lParam);</span>
<span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">bool</span> <span style="color:rgb(240, 141, 73); font-weight:400;">LoadSprite</span><span style="color:rgb(255, 255, 255); font-weight:400;">(<span style="color:rgb(136, 174, 206); font-weight:400;">sprite_t</span> *sprite, <span style="color:rgb(136, 174, 206); font-weight:400;">const</span> <span style="color:rgb(136, 174, 206); font-weight:400;">char</span> *filename)</span></span>;

<span class="fadecode">int WINAPI WinMain(HINSTANCE hInstance, HINSTANCE hPrevInstance, LPSTR lpCmdLine, int nShowCmd) {
	const wchar_t window_class_name[] = L"Window Class";
	static WNDCLASS window_class = { 0 };
	window_class.lpfnWndProc = WindowProcessMessage;
	window_class.hInstance = hInstance;
	window_class.lpszClassName = window_class_name;
	RegisterClass(&window_class);

	bitmap_info.bmiHeader.biSize = sizeof(bitmap_info.bmiHeader);
	bitmap_info.bmiHeader.biPlanes = 1;
	bitmap_info.bmiHeader.biBitCount = 32;
	bitmap_info.bmiHeader.biCompression = BI_RGB;
	device_context = CreateCompatibleDC(0);

	window_handle = CreateWindow(window_class_name, L"Learn to Program Windows", WS_OVERLAPPEDWINDOW | WS_VISIBLE, CW_USEDEFAULT, CW_USEDEFAULT, CW_USEDEFAULT, CW_USEDEFAULT, NULL, NULL, hInstance, NULL);
	if(window_handle == NULL) {
		PRINT_ERROR("CreateWindow failed");
		return -1;
	}</span>

	<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">sprite_t</span> sprite;
	<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(!<span style="color:rgb(240, 141, 73); font-weight:400;">LoadSprite</span>(&amp;sprite, <span style="color:rgb(181, 189, 104); font-weight:400;">&quot;colorwheel.bmp&quot;</span>)) {
		<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;Failed to load sprite: \&quot;colorwheel.bmp\&quot;&quot;</span>);
		<span style="color:rgb(136, 174, 206); font-weight:400;">return</span> <span style="color:rgb(240, 141, 73); font-weight:400;">-1</span>;
	}

	<span class="fadecode">while(!quit) {
		static MSG message = { 0 };
		while(PeekMessage(&message, NULL, 0, 0, PM_REMOVE)) {
			DispatchMessage(&message);
		}</span>

		<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">uint32_t</span> *p;
		p = frame.pixels;
		<span style="color:rgb(136, 174, 206); font-weight:400;">for</span>(<span style="color:rgb(136, 174, 206); font-weight:400;">int</span> n = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>; n &lt; frame.w * frame.h; ++n) {
			*(p++) = (<span style="color:rgb(136, 174, 206); font-weight:400;">float</span>)n / (<span style="color:rgb(136, 174, 206); font-weight:400;">float</span>)(frame.w * frame.h) * (<span style="color:rgb(136, 174, 206); font-weight:400;">uint32_t</span>)(<span style="color:rgb(240, 141, 73); font-weight:400;">-1</span>);
		}

		<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">if</span> 1</span>
		<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">uint32_t</span> *frame_pointer, *sprite_pointer;
		<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">int</span> sprite_byte_width;
		frame_pointer = frame.pixels;
		sprite_pointer = sprite.pixels;
		sprite_byte_width = sprite.w * <span style="color:rgb(240, 141, 73); font-weight:400;">4</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">for</span>(<span style="color:rgb(136, 174, 206); font-weight:400;">int</span> y = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>; y &lt; sprite.h; ++y) {
			<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_memcpy.htm" target="_blank">memcpy</a></span>(frame_pointer, sprite_pointer, sprite_byte_width);
			frame_pointer += frame.w; sprite_pointer += sprite.w;
		}

		<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">else</span></span>
		<span style="color:rgb(136, 174, 206); font-weight:400;">for</span>(<span style="color:rgb(136, 174, 206); font-weight:400;">int</span> y = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>; y &lt; sprite.h; ++y) {
			<span style="color:rgb(136, 174, 206); font-weight:400;">for</span>(<span style="color:rgb(136, 174, 206); font-weight:400;">int</span> x = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>; x &lt; sprite.w; ++x) {
				<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">uint32_t</span> source_index, target_index;
				<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">float</span> alpha, anti_alpha;
				<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">uint32_t</span> sr, sg, sb; <span style="color:rgb(153, 153, 153); font-weight:400;">// Source</span>
				<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">uint32_t</span> tr, tg, tb; <span style="color:rgb(153, 153, 153); font-weight:400;">// Target</span>
				source_index = x + y*sprite.w;
				target_index = x + y*frame.w;
				alpha = (<span style="color:rgb(136, 174, 206); font-weight:400;">float</span>)    ((sprite.p[source_index] &amp; <span style="color:rgb(240, 141, 73); font-weight:400;">0xff000000</span>) &gt;&gt; <span style="color:rgb(240, 141, 73); font-weight:400;">24</span>) / <span style="color:rgb(240, 141, 73); font-weight:400;">255.f</span>;
				anti_alpha = <span style="color:rgb(240, 141, 73); font-weight:400;">1.f</span> - alpha;
				sr    = (<span style="color:rgb(136, 174, 206); font-weight:400;">uint32_t</span>)(((sprite.p[source_index] &amp; <span style="color:rgb(240, 141, 73); font-weight:400;">0x00ff0000</span>) &gt;&gt; <span style="color:rgb(240, 141, 73); font-weight:400;">16</span>) * alpha)      &lt;&lt; <span style="color:rgb(240, 141, 73); font-weight:400;">16</span>;
				sg    = (<span style="color:rgb(136, 174, 206); font-weight:400;">uint32_t</span>)(((sprite.p[source_index] &amp; <span style="color:rgb(240, 141, 73); font-weight:400;">0x0000ff00</span>) &gt;&gt;  <span style="color:rgb(240, 141, 73); font-weight:400;">8</span>) * alpha)      &lt;&lt;  <span style="color:rgb(240, 141, 73); font-weight:400;">8</span>;
				sb    =             (sprite.p[source_index] &amp; <span style="color:rgb(240, 141, 73); font-weight:400;">0x000000ff</span>       ) * alpha;
				tr    = (<span style="color:rgb(136, 174, 206); font-weight:400;">uint32_t</span>)((( frame.p[target_index] &amp; <span style="color:rgb(240, 141, 73); font-weight:400;">0x00ff0000</span>) &gt;&gt; <span style="color:rgb(240, 141, 73); font-weight:400;">16</span>) * anti_alpha) &lt;&lt; <span style="color:rgb(240, 141, 73); font-weight:400;">16</span>;
				tg    = (<span style="color:rgb(136, 174, 206); font-weight:400;">uint32_t</span>)((( frame.p[target_index] &amp; <span style="color:rgb(240, 141, 73); font-weight:400;">0x0000ff00</span>) &gt;&gt;  <span style="color:rgb(240, 141, 73); font-weight:400;">8</span>) * anti_alpha) &lt;&lt;  <span style="color:rgb(240, 141, 73); font-weight:400;">8</span>;
				tb    =             ( frame.p[target_index] &amp; <span style="color:rgb(240, 141, 73); font-weight:400;">0x000000ff</span>       ) * anti_alpha;
				frame.pixels[target_index] = sb + tb + sg + tg + sr + tr;
			}
		}
		<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">endif</span></span>

		<span class="fadecode">InvalidateRect(window_handle, NULL, FALSE);
		UpdateWindow(window_handle);
	}

	return 0;
}

LRESULT CALLBACK WindowProcessMessage(HWND window_handle, UINT message, WPARAM wParam, LPARAM lParam) {
	switch(message) {
		case WM_DESTROY: {
			PostQuitMessage(0);
			quit = true;
		} break;

		case WM_PAINT: {
			static PAINTSTRUCT paint;
			static HDC paint_device_context;
			paint_device_context = BeginPaint(window_handle, &paint);
			BitBlt(paint_device_context, paint.rcPaint.left, paint.rcPaint.top, paint.rcPaint.right - paint.rcPaint.left, paint.rcPaint.bottom - paint.rcPaint.top, device_context, paint.rcPaint.left, paint.rcPaint.top, SRCCOPY);
			EndPaint(window_handle, &paint);
		} break;

		case WM_SIZE: {
			frame.width  = bitmap_info.bmiHeader.biWidth  = LOWORD(lParam);
			frame.height = bitmap_info.bmiHeader.biHeight = HIWORD(lParam);
			if(bitmap) DeleteObject(bitmap);
			bitmap = CreateDIBSection(NULL, &bitmap_info, DIB_RGB_COLORS, (void**)&frame.pixels, 0, 0);
			SelectObject(device_context, bitmap);
		} break;

		case WM_KEYDOWN: {
			if(wParam == VK_ESCAPE) {
				PostQuitMessage(0);
				quit = true;
			}
		} break;

		default: return DefWindowProc(window_handle, message, wParam, lParam);
	}
	return 0;
}</span>

<span style="color:rgb(153, 153, 153); font-weight:400;">/* Bitmap file format
 *
 * SECTION
 * Address:Bytes	Name
 *
 * HEADER:
 *	  0:	2		&quot;BM&quot; magic number
 *	  2:	4		file size
 *	  6:	4		junk
 *	 10:	4		Starting address of image data
 * BITMAP HEADER:
 *	 14:	4		header size
 *	 18:	4		width  (signed)
 *	 22:	4		height (signed)
 *	 26:	2		Number of color planes
 *	 28:	2		Bits per pixel
 *	[...]
 * [OPTIONAL COLOR PALETTE, NOT PRESENT IN 32 BIT BITMAPS]
 * BITMAP DATA:
 *	DATA:	X	Pixels
 */</span>
<span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">bool</span> <span style="color:rgb(240, 141, 73); font-weight:400;">LoadSprite</span><span style="color:rgb(255, 255, 255); font-weight:400;">(<span style="color:rgb(136, 174, 206); font-weight:400;">sprite_t</span> *sprite, <span style="color:rgb(136, 174, 206); font-weight:400;">const</span> <span style="color:rgb(136, 174, 206); font-weight:400;">char</span> *filename)</span> </span>{
	<span style="color:rgb(136, 174, 206); font-weight:400;">bool</span> return_value = <span style="color:rgb(240, 141, 73); font-weight:400;">true</span>;

	<span style="color:rgb(136, 174, 206); font-weight:400;">uint32_t</span> image_data_address;
	<span style="color:rgb(136, 174, 206); font-weight:400;">int32_t</span> width;
	<span style="color:rgb(136, 174, 206); font-weight:400;">int32_t</span> height;
	<span style="color:rgb(136, 174, 206); font-weight:400;">uint32_t</span> pixel_count;
	<span style="color:rgb(136, 174, 206); font-weight:400;">uint16_t</span> bit_depth;
	<span style="color:rgb(136, 174, 206); font-weight:400;">uint8_t</span> byte_depth;
	<span style="color:rgb(136, 174, 206); font-weight:400;">uint32_t</span> *pixels;

	<span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;Loading bitmap file: %s\n&quot;</span>, filename);

	FILE *file;
	file = <span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fopen.htm" target="_blank">fopen</a></span>(filename, <span style="color:rgb(181, 189, 104); font-weight:400;">&quot;rb&quot;</span>);
	<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(file) {
		<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fgetc.htm" target="_blank">fgetc</a></span>(file) == <span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;B&#x27;</span> &amp;&amp; <span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fgetc.htm" target="_blank">fgetc</a></span>(file) == <span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;M&#x27;</span>) {
			<span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;BM read; bitmap file confirmed.\n&quot;</span>);
			<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fseek.htm" target="_blank">fseek</a></span>(file, <span style="color:rgb(240, 141, 73); font-weight:400;">8</span>, SEEK_CUR);
			<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(&amp;image_data_address, <span style="color:rgb(240, 141, 73); font-weight:400;">4</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>, file);
			<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fseek.htm" target="_blank">fseek</a></span>(file, <span style="color:rgb(240, 141, 73); font-weight:400;">4</span>, SEEK_CUR);
			<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(&amp;width, <span style="color:rgb(240, 141, 73); font-weight:400;">4</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>, file);
			<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(&amp;height, <span style="color:rgb(240, 141, 73); font-weight:400;">4</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>, file);
			<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fseek.htm" target="_blank">fseek</a></span>(file, <span style="color:rgb(240, 141, 73); font-weight:400;">2</span>, SEEK_CUR);
			<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(&amp;bit_depth, <span style="color:rgb(240, 141, 73); font-weight:400;">2</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>, file);
			<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(bit_depth != <span style="color:rgb(240, 141, 73); font-weight:400;">32</span>) {
				<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;(%s) Bit depth expected %d is %d&quot;</span>, filename, <span style="color:rgb(240, 141, 73); font-weight:400;">32</span>, bit_depth);
				return_value = <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
			}
			<span style="color:rgb(136, 174, 206); font-weight:400;">else</span> { <span style="color:rgb(153, 153, 153); font-weight:400;">// Image metadata correct</span>
				<span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;image data address:\t%d\nwidth:\t\t\t%d pix\nheight:\t\t\t%d pix\nbit depth:\t\t%d bpp\n&quot;</span>, image_data_address, width, height, bit_depth);
				pixel_count = width * height;
				byte_depth = bit_depth / <span style="color:rgb(240, 141, 73); font-weight:400;">8</span>;
				pixels = <span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_malloc.htm" target="_blank">malloc</a></span>(pixel_count * byte_depth);
				<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(pixels) {
					<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fseek.htm" target="_blank">fseek</a></span>(file, image_data_address, SEEK_SET);
					<span style="color:rgb(136, 174, 206); font-weight:400;">int</span> pixels_read = <span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(pixels, byte_depth, pixel_count, file);
					<span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;Read %d pixels\n&quot;</span>, pixels_read);
					<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(pixels_read == pixel_count) {
						sprite-&gt;w = width;
						sprite-&gt;h = height;
						sprite-&gt;p = pixels;
					}
					<span style="color:rgb(136, 174, 206); font-weight:400;">else</span> {
						<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;(%s) Read pixel count incorrect. Is %d expected %d&quot;</span>, filename, pixels_read, pixel_count);
						<span style="color:rgb(240, 141, 73); font-weight:400;">free</span>(pixels);
						return_value = <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
					}
				}
				<span style="color:rgb(136, 174, 206); font-weight:400;">else</span> {
					<span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;(%s) Failed to allocate %d pixels.\n&quot;</span>, filename, pixel_count);
					return_value = <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
				}
			} <span style="color:rgb(153, 153, 153); font-weight:400;">// Done loading sprite</span>
		}
		<span style="color:rgb(136, 174, 206); font-weight:400;">else</span> {
			<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;(%s) First two bytes of file are not \&quot;BM\&quot;&quot;</span>, filename);
			return_value = <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
		}

		<span style="color:rgb(240, 141, 73); font-weight:400;">fclose</span>(file);
	}
	<span style="color:rgb(136, 174, 206); font-weight:400;">else</span> {
		<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;(%s) Failed to open file&quot;</span>, filename);
		return_value = <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
	}
	<span style="color:rgb(136, 174, 206); font-weight:400;">return</span> return_value;
}</code>

<a href="build.bat" download>build.bat</a>
<code>gcc main.c -lgdi32</code>

And here's the bitmap image loaded in the demo code: <a href="colorwheel.bmp" download>colorwheel.bmp
<img src="colorwheel.bmp"></a>
This image was generated using GIMP. If you have trouble creating a similar transparent bmp image, be sure to add transparency to the layer in GIMP and in the bmp export options, select "Advanced Options->A8 R8 G8 B8".

<hr>
<h4><a id="Code Walkthrough" style="color:rgb(255,255,255);">Code Walkthrough</a></h4>

<code><span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;">&lt;<a href="https://www.tutorialspoint.com/c_standard_library/stdio_h.htm" target="_blank">stdio.h</a>&gt;</span></span></code>
stdio.h contains all the file operation functions we need to load any file. We could use the operating system's native libraries to load files but unless you have specific needs to meet, the portable C standard library is an easy way to get the job done.

<code><span style="color:rgb(136, 174, 206); font-weight:400;">typedef</span> <span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">struct</span> {</span>
	<span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">union</span> {</span> <span style="color:rgb(136, 174, 206); font-weight:400;">int</span> width,  w; };
	<span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">union</span> {</span> <span style="color:rgb(136, 174, 206); font-weight:400;">int</span> height, h; };
	<span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">union</span> {</span> <span style="color:rgb(136, 174, 206); font-weight:400;">uint32_t</span> *pixels, *p; };
} <span style="color:rgb(136, 174, 206); font-weight:400;">sprite_t</span>;</code>
This “sprite” structure holds the width, height and pixels of our bitmap image. We use uint32s for the pixels since we’re only going to be loading 32-bit bitmaps with alpha, red, green and blue 8-bit sub-pixels.

<code>	<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">sprite_t</span> sprite;
	<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(!<span style="color:rgb(240, 141, 73); font-weight:400;">LoadSprite</span>(&amp;sprite, <span style="color:rgb(181, 189, 104); font-weight:400;">&quot;colorwheel.bmp&quot;</span>)) {
		<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;Failed to load sprite: \&quot;colorwheel.bmp\&quot;&quot;</span>);
		<span style="color:rgb(136, 174, 206); font-weight:400;">return</span> <span style="color:rgb(240, 141, 73); font-weight:400;">-1</span>;
	}</code>
In the main program we create the sprite variable and load it with this “LoadSprite” function, sending a pointer to the sprite to be filled with data and the filename to load. Within LoadSprite is the bulk of the program.

<code><span style="color:rgb(153, 153, 153); font-weight:400;">/* Bitmap file format
 *
 * SECTION
 * Address:Bytes	Name
 *
 * HEADER:
 *	  0:	2		&quot;BM&quot; magic number
 *	  2:	4		file size
 *	  6:	4		junk
 *	 10:	4		Starting address of image data
 * BITMAP HEADER:
 *	 14:	4		header size
 *	 18:	4		width  (signed)
 *	 22:	4		height (signed)
 *	 26:	2		Number of color planes
 *	 28:	2		Bits per pixel
 *	[...]
 * [OPTIONAL COLOR PALETTE, NOT PRESENT IN 32 BIT BITMAPS]
 * BITMAP DATA:
 *	DATA:	X	Pixels
 */</span></code>
 I found the bitmap file format information online and wrote it here in a convenient reference format. There are actually 4 versions of the bitmap format but I’ve written this code to only work with version 4 which has been standard since Windows 95. There are also many variations of bitmap formats such as 8-bit, using a colour palette and so on, but I’ve written code only to load 32-bit bitmaps with transparency.

Bitmap files contain a file header identifying it, a bitmap header with information about the image and the bitmap pixel data.

<code><span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">bool</span> <span style="color:rgb(240, 141, 73); font-weight:400;">LoadSprite</span><span style="color:rgb(255, 255, 255); font-weight:400;">(<span style="color:rgb(136, 174, 206); font-weight:400;">sprite_t</span> *sprite, <span style="color:rgb(136, 174, 206); font-weight:400;">const</span> <span style="color:rgb(136, 174, 206); font-weight:400;">char</span> *filename)</span> </span>{
	<span style="color:rgb(136, 174, 206); font-weight:400;">bool</span> return_value = <span style="color:rgb(240, 141, 73); font-weight:400;">true</span>;

	<span style="color:rgb(136, 174, 206); font-weight:400;">uint32_t</span> image_data_address;
	<span style="color:rgb(136, 174, 206); font-weight:400;">int32_t</span> width;
	<span style="color:rgb(136, 174, 206); font-weight:400;">int32_t</span> height;
	<span style="color:rgb(136, 174, 206); font-weight:400;">uint32_t</span> pixel_count;
	<span style="color:rgb(136, 174, 206); font-weight:400;">uint16_t</span> bit_depth;
	<span style="color:rgb(136, 174, 206); font-weight:400;">uint8_t</span> byte_depth;
	<span style="color:rgb(136, 174, 206); font-weight:400;">uint32_t</span> *pixels;

	<span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;Loading bitmap file: %s\n&quot;</span>, filename);</code>
Here I’ve declared all the variables we care to load from the file. Some of the data we don’t care about so we’ll just skip it. I’ve interspersed several printfs to print out the data as we read it just to make debugging easier.

<code>	FILE *file;
	file = <span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fopen.htm" target="_blank">fopen</a></span>(filename, <span style="color:rgb(181, 189, 104); font-weight:400;">&quot;rb&quot;</span>);
	<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(file) {</code>
We create a FILE pointer and open the file in “read binary” mode with fopen. If the file was opened successfully fopen returns the pointer to the file, otherwise it returns NULL so we can check it was successful with a simple if statement.

<code>		<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fgetc.htm" target="_blank">fgetc</a></span>(file) == <span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;B&#x27;</span> &amp;&amp; <span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fgetc.htm" target="_blank">fgetc</a></span>(file) == <span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;M&#x27;</span>) {
			<span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;BM read; bitmap file confirmed.\n&quot;</span>);</code>
Using fgetc we read the first two bytes, checking that they’re the letters “BM” which identify a bitmap file. Note that these if blocks each have an else block to handle errors further down the code.

<code>			<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fseek.htm" target="_blank">fseek</a></span>(file, <span style="color:rgb(240, 141, 73); font-weight:400;">8</span>, SEEK_CUR);
			<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(&amp;image_data_address, <span style="color:rgb(240, 141, 73); font-weight:400;">4</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>, file);
			<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fseek.htm" target="_blank">fseek</a></span>(file, <span style="color:rgb(240, 141, 73); font-weight:400;">4</span>, SEEK_CUR);
			<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(&amp;width, <span style="color:rgb(240, 141, 73); font-weight:400;">4</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>, file);
			<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(&amp;height, <span style="color:rgb(240, 141, 73); font-weight:400;">4</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>, file);
			<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fseek.htm" target="_blank">fseek</a></span>(file, <span style="color:rgb(240, 141, 73); font-weight:400;">2</span>, SEEK_CUR);
			<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(&amp;bit_depth, <span style="color:rgb(240, 141, 73); font-weight:400;">2</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>, file);
			<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(bit_depth != <span style="color:rgb(240, 141, 73); font-weight:400;">32</span>) {
				<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;(%s) Bit depth expected %d is %d&quot;</span>, filename, <span style="color:rgb(240, 141, 73); font-weight:400;">32</span>, bit_depth);
				return_value = <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
			}</code>
Next we read the variables we care about with fread, which takes a pointer to be filled, the number of bytes per element, the number of elements to be read and the file pointer. We read: image data address; width; height; and bit depth, with a couple of file seek operations to skip unneeded data. fseek takes the file pointer, amount of bytes by which to offset the pointer and position from which to offset, which can be SEEK_SET (beginning of file), SEEK_CUR (current position) or SEEK_END (end of file). We verify the bit depth is correct before continuing.

<code>			<span style="color:rgb(136, 174, 206); font-weight:400;">else</span> { <span style="color:rgb(153, 153, 153); font-weight:400;">// Image metadata correct</span>
				<span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;image data address:\t%d\nwidth:\t\t\t%d pix\nheight:\t\t\t%d pix\nbit depth:\t\t%d bpp\n&quot;</span>, image_data_address, width, height, bit_depth);
				pixel_count = width * height;
				byte_depth = bit_depth / <span style="color:rgb(240, 141, 73); font-weight:400;">8</span>;
				pixels = <span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_malloc.htm" target="_blank">malloc</a></span>(pixel_count * byte_depth);
				<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(pixels) {
					<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fseek.htm" target="_blank">fseek</a></span>(file, image_data_address, SEEK_SET);
					<span style="color:rgb(136, 174, 206); font-weight:400;">int</span> pixels_read = <span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(pixels, byte_depth, pixel_count, file);
					<span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;Read %d pixels\n&quot;</span>, pixels_read);
					<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(pixels_read == pixel_count) {
						sprite-&gt;w = width;
						sprite-&gt;h = height;
						sprite-&gt;p = pixels;
					}</code>
If the data so far is correct, we calculate our number of pixels and bytes per pixel, and allocate the pixel buffer. We seek to the image data address and read the pixels. fread returns the number of elements read so we check that matches our calculated number of pixels before finally setting our sprite’s width, height and pointer to the pixels.

<code>					<span style="color:rgb(136, 174, 206); font-weight:400;">else</span> {
						<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;(%s) Read pixel count incorrect. Is %d expected %d&quot;</span>, filename, pixels_read, pixel_count);
						<span style="color:rgb(240, 141, 73); font-weight:400;">free</span>(pixels);
						return_value = <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
					}
				}
				<span style="color:rgb(136, 174, 206); font-weight:400;">else</span> {
					<span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;(%s) Failed to allocate %d pixels.\n&quot;</span>, filename, pixel_count);
					return_value = <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
				}
			} <span style="color:rgb(153, 153, 153); font-weight:400;">// Done loading sprite</span>
		}
		<span style="color:rgb(136, 174, 206); font-weight:400;">else</span> {
			<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;(%s) First two bytes of file are not \&quot;BM\&quot;&quot;</span>, filename);
			return_value = <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
		}

		<span style="color:rgb(240, 141, 73); font-weight:400;">fclose</span>(file);
	}
	<span style="color:rgb(136, 174, 206); font-weight:400;">else</span> {
		<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;(%s) Failed to open file&quot;</span>, filename);
		return_value = <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
	}
	<span style="color:rgb(136, 174, 206); font-weight:400;">return</span> return_value;
}</code>
After the cascading error handling code, we close the file and return.

<code>		p = frame.pixels;
		<span style="color:rgb(136, 174, 206); font-weight:400;">for</span>(<span style="color:rgb(136, 174, 206); font-weight:400;">int</span> n = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>; n &lt; frame.w * frame.h; ++n) {
			*(p++) = (<span style="color:rgb(136, 174, 206); font-weight:400;">float</span>)n / (<span style="color:rgb(136, 174, 206); font-weight:400;">float</span>)(frame.w * frame.h) * (<span style="color:rgb(136, 174, 206); font-weight:400;">uint32_t</span>)(<span style="color:rgb(240, 141, 73); font-weight:400;">-1</span>);
		}</code>
Back in our main program, we proceed to the main program loop where we fill the frame buffer with a background, then we have two different methods of copying the pixels to the screen:

<code>		<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">if</span> 1</span>
		<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">uint32_t</span> *frame_pointer, *sprite_pointer;
		<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">int</span> sprite_byte_width;
		frame_pointer = frame.pixels;
		sprite_pointer = sprite.pixels;
		sprite_byte_width = sprite.w * <span style="color:rgb(240, 141, 73); font-weight:400;">4</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">for</span>(<span style="color:rgb(136, 174, 206); font-weight:400;">int</span> y = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>; y &lt; sprite.h; ++y) {
			<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_memcpy.htm" target="_blank">memcpy</a></span>(frame_pointer, sprite_pointer, sprite_byte_width);
			frame_pointer += frame.w; sprite_pointer += sprite.w;
		}</code>
First is a relatively fast loop through each row of the sprite, using advancing pointers to copy each row at a time to the frame with memcpy. This doesn’t process any of the pixels individually and so won’t do alpha blending.

<img src="program-fast.png">

<code>		<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">else</span></span>
		<span style="color:rgb(136, 174, 206); font-weight:400;">for</span>(<span style="color:rgb(136, 174, 206); font-weight:400;">int</span> y = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>; y &lt; sprite.h; ++y) {
			<span style="color:rgb(136, 174, 206); font-weight:400;">for</span>(<span style="color:rgb(136, 174, 206); font-weight:400;">int</span> x = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>; x &lt; sprite.w; ++x) {
				<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">uint32_t</span> source_index, target_index;
				<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">float</span> alpha, anti_alpha;
				<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">uint32_t</span> sr, sg, sb; <span style="color:rgb(153, 153, 153); font-weight:400;">// Source</span>
				<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">uint32_t</span> tr, tg, tb; <span style="color:rgb(153, 153, 153); font-weight:400;">// Target</span>
				source_index = x + y*sprite.w;
				target_index = x + y*frame.w;
				alpha = (<span style="color:rgb(136, 174, 206); font-weight:400;">float</span>)    ((sprite.p[source_index] &amp; <span style="color:rgb(240, 141, 73); font-weight:400;">0xff000000</span>) &gt;&gt; <span style="color:rgb(240, 141, 73); font-weight:400;">24</span>) / <span style="color:rgb(240, 141, 73); font-weight:400;">255.f</span>;
				anti_alpha = <span style="color:rgb(240, 141, 73); font-weight:400;">1.f</span> - alpha;
				sr    = (<span style="color:rgb(136, 174, 206); font-weight:400;">uint32_t</span>)(((sprite.p[source_index] &amp; <span style="color:rgb(240, 141, 73); font-weight:400;">0x00ff0000</span>) &gt;&gt; <span style="color:rgb(240, 141, 73); font-weight:400;">16</span>) * alpha)      &lt;&lt; <span style="color:rgb(240, 141, 73); font-weight:400;">16</span>;
				sg    = (<span style="color:rgb(136, 174, 206); font-weight:400;">uint32_t</span>)(((sprite.p[source_index] &amp; <span style="color:rgb(240, 141, 73); font-weight:400;">0x0000ff00</span>) &gt;&gt;  <span style="color:rgb(240, 141, 73); font-weight:400;">8</span>) * alpha)      &lt;&lt;  <span style="color:rgb(240, 141, 73); font-weight:400;">8</span>;
				sb    =             (sprite.p[source_index] &amp; <span style="color:rgb(240, 141, 73); font-weight:400;">0x000000ff</span>       ) * alpha;
				tr    = (<span style="color:rgb(136, 174, 206); font-weight:400;">uint32_t</span>)((( frame.p[target_index] &amp; <span style="color:rgb(240, 141, 73); font-weight:400;">0x00ff0000</span>) &gt;&gt; <span style="color:rgb(240, 141, 73); font-weight:400;">16</span>) * anti_alpha) &lt;&lt; <span style="color:rgb(240, 141, 73); font-weight:400;">16</span>;
				tg    = (<span style="color:rgb(136, 174, 206); font-weight:400;">uint32_t</span>)((( frame.p[target_index] &amp; <span style="color:rgb(240, 141, 73); font-weight:400;">0x0000ff00</span>) &gt;&gt;  <span style="color:rgb(240, 141, 73); font-weight:400;">8</span>) * anti_alpha) &lt;&lt;  <span style="color:rgb(240, 141, 73); font-weight:400;">8</span>;
				tb    =             ( frame.p[target_index] &amp; <span style="color:rgb(240, 141, 73); font-weight:400;">0x000000ff</span>       ) * anti_alpha;
				frame.pixels[target_index] = sb + tb + sg + tg + sr + tr;
			}
		}
		<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">endif</span></span></code>
Alternatively we can loop through every pixel in the sprite and blend it with the background. We extract the alpha value and convert it to a floating point between 0 and 1. Then we extract the red, green and blue values, shift them into the lower 8 bits, multiply by alpha and shift back up to their correct byte. We do the same for the background, only using one minus alpha instead. Finally we add the sprite and background colour values together to get the blended pixel value. Note that this is very slow code and is written to be understandable, not fast. If you’re rendering numerous transparent sprites in a game you’ll want to create far more optimized structures and algorithms to draw them.

<img src="program-transparent.png">

That’s how to load and display bitmaps in C. In my next tutorials I’ll show you how to load sound files, do precise timing and soon I’ll be showing you how to make a complete small game in C.

<hr>
If you've got questions about any of the code feel free to e-mail me or comment on the <a href="https://www.youtube.com/watch?v=hNi_MEZ8X10" target="_blank">youtube video</a>. I'll try to answer them, or someone else might come along and help you out. If you've got any extra tips about how this code can be better or just more useful info about the code, let me know so I can update the tutorial.

Thanks to Froggie717 for criticisms and correcting errors in this tutorial.

Cheers.

</article>

</body>

<?php include "/blog/blogbottom.html" ?>

<?php include "footer_common.html" ?>

</html>
