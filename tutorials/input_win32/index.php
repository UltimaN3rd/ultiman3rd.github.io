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

<h4>Handling Keyboard and Mouse Input with Win32 in C</h4>

This tutorial is also available as a <a href="https://www.youtube.com/watch?v=ZjfdkDIIw2A" target="_blank">video.</a>

In this tutorial I’ll show you how to handle keyboard and mouse input events with the Win32 native library, including storing the keyboard state and tracking mouse movement and buttons.

Here's a clip of the final program running:
<video src="program_running.mp4" autoplay muted loop></video>

Note: I’m compiling this code with GCC and not Microsoft’s C compiler. To compile with cl you’ll need to remove the PRINT_ERROR macro and also link to user32.
Note: Click any of the <a>hyperlinked words</a> to visit the MSDN documentation page for them.
Note: I've dimmed all of the code not specific to the subject of the tutorial.

<a href="main.c" download>main.c</a>
<code><span class="fadecode">#define UNICODE
#define _UNICODE
#include &ltwindows.h&gt
#include &ltstdbool.h&gt
#include &ltstdint.h&gt
#include &ltstdio.h&gt

#define PRINT_ERROR(a, args...) printf("ERROR %s() %s Line %d: " a, __FUNCTION__, __FILE__, __LINE__, ##args);

#if RAND_MAX == 32767
#define rand32() ((rand() << 15) + (rand() << 1) + (rand() & 1))
#else
#define rand32() rand()
#endif

bool quit = false;
HWND window_handle;
BITMAPINFO bitmap_info;
HBITMAP bitmap;
HDC bitmap_device_context;

struct {
	union { int w, width; };
	union { int h, height; };
	uint32_t *pixels;
} frame = {0};</span>

<span style="color:rgb(136, 174, 206); font-weight:400;">bool</span> keyboard[<span style="color:rgb(240, 141, 73); font-weight:400;">256</span>] = {<span style="color:rgb(240, 141, 73); font-weight:400;">0</span>};
<span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">struct</span> {</span>
	<span style="color:rgb(136, 174, 206); font-weight:400;">int</span> x, y;
	<span style="color:rgb(136, 174, 206); font-weight:400;">uint8_t</span> buttons;
} mouse;
<span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">enum</span> {</span> MOUSE_LEFT = <span style="color:rgb(240, 141, 73); font-weight:400;">0b1</span>, MOUSE_MIDDLE = <span style="color:rgb(240, 141, 73); font-weight:400;">0b10</span>, MOUSE_RIGHT = <span style="color:rgb(240, 141, 73); font-weight:400;">0b100</span>, MOUSE_X1 = <span style="color:rgb(240, 141, 73); font-weight:400;">0b1000</span>, MOUSE_X2 = <span style="color:rgb(240, 141, 73); font-weight:400;">0b10000</span> };

<span style="color:rgb(255, 255, 255); font-weight:400;">LRESULT CALLBACK <span style="color:rgb(240, 141, 73); font-weight:400;">WindowProcessMessage</span><span style="color:rgb(255, 255, 255); font-weight:400;">(HWND window_handle, UINT message, WPARAM wParam, LPARAM lParam)</span></span>;

<span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">int</span> WINAPI <span style="color:rgb(240, 141, 73); font-weight:400;">WinMain</span><span style="color:rgb(255, 255, 255); font-weight:400;">(HINSTANCE hInstance, HINSTANCE hPrevInstance, LPSTR pCmdLine, <span style="color:rgb(136, 174, 206); font-weight:400;">int</span> nCmdShow)</span> </span>{
<span class="fadecode">	const wchar_t window_class_name[] = L"Window Class";
	static WNDCLASS window_class = { 0 };
	window_class.lpfnWndProc = WindowProcessMessage;
	window_class.hInstance = hInstance;
	window_class.lpszClassName = window_class_name;
	RegisterClass(&window_class);

	bitmap_info.bmiHeader.biSize = sizeof(bitmap_info.bmiHeader);
	bitmap_info.bmiHeader.biPlanes = 1;
	bitmap_info.bmiHeader.biBitCount = 32;
	bitmap_info.bmiHeader.biCompression = BI_RGB;
	bitmap_device_context = CreateCompatibleDC(0);

	window_handle = CreateWindow(window_class_name, L"Learn to Program Windows", WS_OVERLAPPEDWINDOW | WS_VISIBLE, CW_USEDEFAULT, CW_USEDEFAULT, CW_USEDEFAULT, CW_USEDEFAULT, NULL, NULL, hInstance, NULL);
	if(window_handle == NULL) {
		PRINT_ERROR("CreateWindow() failed. Returned NULL.\n");
		return -1;
	}</span>

	<span style="color:rgb(136, 174, 206); font-weight:400;">while</span>(!quit) {
		<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> MSG message = { <span style="color:rgb(240, 141, 73); font-weight:400;">0</span> };
		<span style="color:rgb(136, 174, 206); font-weight:400;">while</span>(<span style="color:rgb(240, 141, 73); font-weight:400;">PeekMessage</span>(&amp;message, <span style="color:rgb(240, 141, 73); font-weight:400;">NULL</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>, PM_REMOVE)) { <span style="color:rgb(240, 141, 73); font-weight:400;">DispatchMessage</span>(&amp;message); }

		<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">int</span> keyboard_x = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>, keyboard_y = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(keyboard[VK_RIGHT] || keyboard[<span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;D&#x27;</span>]) ++keyboard_x;
		<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(keyboard[VK_LEFT]  || keyboard[<span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;A&#x27;</span>]) --keyboard_x;
		<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(keyboard[VK_UP]    || keyboard[<span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;W&#x27;</span>]) ++keyboard_y;
		<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(keyboard[VK_DOWN]  || keyboard[<span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;S&#x27;</span>]) --keyboard_y;

		<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(keyboard_x &lt; <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>)			keyboard_x = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(keyboard_x &gt; frame.w<span style="color:rgb(240, 141, 73); font-weight:400;">-1</span>)	keyboard_x = frame.w<span style="color:rgb(240, 141, 73); font-weight:400;">-1</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(keyboard_y &lt; <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>)			keyboard_y = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(keyboard_y &gt; frame.h<span style="color:rgb(240, 141, 73); font-weight:400;">-1</span>)	keyboard_y = frame.h<span style="color:rgb(240, 141, 73); font-weight:400;">-1</span>;

		<span style="color:rgb(136, 174, 206); font-weight:400;">for</span>(<span style="color:rgb(136, 174, 206); font-weight:400;">int</span> i = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>; i &lt; <span style="color:rgb(240, 141, 73); font-weight:400;">1000</span>; ++i) frame.pixels[<span style="color:rgb(240, 141, 73); font-weight:400;">rand32</span>() % (frame.w * frame.h)] = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>;

		frame.pixels[keyboard_x + keyboard_y*frame.w] = <span style="color:rgb(240, 141, 73); font-weight:400;">0x00ffffff</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(mouse.buttons &amp; MOUSE_LEFT) frame.pixels[mouse.x + mouse.y*frame.w] = <span style="color:rgb(240, 141, 73); font-weight:400;">0x00ffffff</span>;

		<span style="color:rgb(240, 141, 73); font-weight:400;">InvalidateRect</span>(window_handle, <span style="color:rgb(240, 141, 73); font-weight:400;">NULL</span>, FALSE);
		<span style="color:rgb(240, 141, 73); font-weight:400;">UpdateWindow</span>(window_handle);
	}

	<span style="color:rgb(136, 174, 206); font-weight:400;">return</span> <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>;
}

<span style="color:rgb(255, 255, 255); font-weight:400;">LRESULT CALLBACK <span style="color:rgb(240, 141, 73); font-weight:400;">WindowProcessMessage</span><span style="color:rgb(255, 255, 255); font-weight:400;">(HWND window_handle, UINT message, WPARAM wParam, LPARAM lParam)</span> </span>{
	<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">bool</span> has_focus = <span style="color:rgb(240, 141, 73); font-weight:400;">true</span>;

	<span style="color:rgb(240, 141, 73); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">switch</span></span>(message) {
<span class="fadecode">		case WM_QUIT:
		case WM_DESTROY: {
			quit = true;
		} break;

		case WM_PAINT: {
			static PAINTSTRUCT paint;
			static HDC device_context;
			device_context = BeginPaint(window_handle, &paint);
			BitBlt(device_context, paint.rcPaint.left, paint.rcPaint.top, paint.rcPaint.right - paint.rcPaint.left, paint.rcPaint.bottom - paint.rcPaint.top, bitmap_device_context, paint.rcPaint.left, paint.rcPaint.top, SRCCOPY);
			EndPaint(window_handle,&paint);
		} break;

		case WM_SIZE: {
			frame.w = bitmap_info.bmiHeader.biWidth = LOWORD(lParam);
			frame.h = bitmap_info.bmiHeader.biHeight = HIWORD(lParam);
			if(bitmap) DeleteObject(bitmap);
			bitmap = CreateDIBSection(NULL, &bitmap_info, DIB_RGB_COLORS, (void**)&frame.pixels, 0, 0);
			SelectObject(bitmap_device_context, bitmap);
		} break;</span>

		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> <a href="https://docs.microsoft.com/en-us/windows/win32/inputdev/wm-killfocus" target="_blank">WM_KILLFOCUS</a>: {
			has_focus = <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
			<span style="color:rgb(240, 141, 73); font-weight:400;">memset</span>(keyboard, <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">256</span> * <span style="color:rgb(240, 141, 73); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">sizeof</span></span>(keyboard[<span style="color:rgb(240, 141, 73); font-weight:400;">0</span>]));
			mouse.buttons = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>;
		} <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;

		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> <a href="https://docs.microsoft.com/en-us/windows/win32/inputdev/wm-setfocus" target="_blank">WM_SETFOCUS</a>: has_focus = <span style="color:rgb(240, 141, 73); font-weight:400;">true</span>; <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;

		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> <a href="https://docs.microsoft.com/en-us/windows/win32/inputdev/wm-syskeydown" target="_blank">WM_SYSKEYDOWN</a>:
		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> <a href="https://docs.microsoft.com/en-us/windows/win32/inputdev/wm-syskeyup" target="_blank">WM_SYSKEYUP</a>:
		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> <a href="https://docs.microsoft.com/en-us/windows/win32/inputdev/wm-keydown" target="_blank">WM_KEYDOWN</a>:
		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> <a href="https://docs.microsoft.com/en-us/windows/win32/inputdev/wm-keyup" target="_blank">WM_KEYUP</a>: {
			<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(has_focus) {
				<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">bool</span> key_is_down, key_was_down;
				key_is_down  = ((lParam &amp; (<span style="color:rgb(240, 141, 73); font-weight:400;">1</span> &lt;&lt; <span style="color:rgb(240, 141, 73); font-weight:400;">31</span>)) == <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>);
				key_was_down = ((lParam &amp; (<span style="color:rgb(240, 141, 73); font-weight:400;">1</span> &lt;&lt; <span style="color:rgb(240, 141, 73); font-weight:400;">30</span>)) != <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>);
				<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(key_is_down != key_was_down) {
					keyboard[(<span style="color:rgb(136, 174, 206); font-weight:400;">uint8_t</span>)wParam] = key_is_down;
					<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(key_is_down) {
						<span style="color:rgb(240, 141, 73); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">switch</span></span>(wParam) {
							<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> VK_ESCAPE: quit = <span style="color:rgb(240, 141, 73); font-weight:400;">true</span>; <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;
						}
					}
				}
			}
		} <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;

		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> <a href="https://docs.microsoft.com/en-us/windows/win32/inputdev/wm-mousemove" target="_blank">WM_MOUSEMOVE</a>: {
			mouse.x = <span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://docs.microsoft.com/en-us/previous-versions/windows/desktop/legacy/ms632659(v=vs.85)" target="_blank">LOWORD</a></span>(lParam);
			mouse.y = frame.h - <span style="color:rgb(240, 141, 73); font-weight:400;">1</span> - <span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://docs.microsoft.com/en-us/previous-versions/windows/desktop/legacy/ms632657(v=vs.85)" target="_blank">HIWORD</a></span>(lParam);
		} <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;

		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> <a href="https://docs.microsoft.com/en-us/windows/win32/inputdev/wm-lbuttondown" target="_blank">WM_LBUTTONDOWN</a>: mouse.buttons |=  MOUSE_LEFT;   <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> <a href="https://docs.microsoft.com/en-us/windows/win32/inputdev/wm-lbuttonup" target="_blank">WM_LBUTTONUP</a>:   mouse.buttons &amp;= ~MOUSE_LEFT;   <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> <a href="https://docs.microsoft.com/en-us/windows/win32/inputdev/wm-mbuttondown" target="_blank">WM_MBUTTONDOWN</a>: mouse.buttons |=  MOUSE_MIDDLE; <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> <a href="https://docs.microsoft.com/en-us/windows/win32/inputdev/wm-mbuttonup" target="_blank">WM_MBUTTONUP</a>:   mouse.buttons &amp;= ~MOUSE_MIDDLE; <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> <a href="https://docs.microsoft.com/en-us/windows/win32/inputdev/wm-rbuttondown" target="_blank">WM_RBUTTONDOWN</a>: mouse.buttons |=  MOUSE_RIGHT;  <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> <a href="https://docs.microsoft.com/en-us/windows/win32/inputdev/wm-rbuttonup" target="_blank">WM_RBUTTONUP</a>:   mouse.buttons &amp;= ~MOUSE_RIGHT;  <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;

		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> <a href="https://docs.microsoft.com/en-us/windows/win32/inputdev/wm-xbuttondown" target="_blank">WM_XBUTTONDOWN</a>: {
			<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://docs.microsoft.com/en-us/windows/win32/api/winuser/nf-winuser-get_xbutton_wparam" target="_blank">GET_XBUTTON_WPARAM</a></span>(wParam) == XBUTTON1) {
					 mouse.buttons |= MOUSE_X1;
			} <span style="color:rgb(136, 174, 206); font-weight:400;">else</span> { mouse.buttons |= MOUSE_X2; }
		} <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> <a href="https://docs.microsoft.com/en-us/windows/win32/inputdev/wm-xbuttonup" target="_blank">WM_XBUTTONUP</a>: {
			<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://docs.microsoft.com/en-us/windows/win32/api/winuser/nf-winuser-get_xbutton_wparam" target="_blank">GET_XBUTTON_WPARAM</a></span>(wParam) == XBUTTON1) {
					 mouse.buttons &amp;= ~MOUSE_X1;
			} <span style="color:rgb(136, 174, 206); font-weight:400;">else</span> { mouse.buttons &amp;= ~MOUSE_X2; }
		} <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;

		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> <a href="https://docs.microsoft.com/en-us/windows/win32/inputdev/wm-mousehwheel" target="_blank">WM_MOUSEWHEEL</a>: {
			<span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;%s\n&quot;</span>, wParam &amp; <span style="color:rgb(240, 141, 73); font-weight:400;">0b10000000000000000000000000000000</span> ? <span style="color:rgb(181, 189, 104); font-weight:400;">&quot;Down&quot;</span> : <span style="color:rgb(181, 189, 104); font-weight:400;">&quot;Up&quot;</span>);
		} <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;

		<span style="color:rgb(136, 174, 206); font-weight:400;">default</span>: <span style="color:rgb(136, 174, 206); font-weight:400;">return</span> <span style="color:rgb(240, 141, 73); font-weight:400;">DefWindowProc</span>(window_handle, message, wParam, lParam);
	}
	<span style="color:rgb(136, 174, 206); font-weight:400;">return</span> <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>;
}</code>

<a href="build.bat" download>build.bat</a>
<code>gcc main.c -lgdi32</code>

<hr>
<h4><a id="Code Walkthrough" style="color:rgb(255,255,255);">Code Walkthrough</a></h4>

<code><span style="color:rgb(136, 174, 206); font-weight:400;">bool</span> keyboard[<span style="color:rgb(240, 141, 73); font-weight:400;">256</span>] = {<span style="color:rgb(240, 141, 73); font-weight:400;">0</span>};</code>
We have a 256-element boolean array to store the state of each keyboard key, with false meaning not pressed and true meaning pressed.

<code><span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">struct</span> {</span>
	<span style="color:rgb(136, 174, 206); font-weight:400;">int</span> x, y;
	<span style="color:rgb(136, 174, 206); font-weight:400;">uint8_t</span> buttons;
} mouse;
<span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">enum</span> {</span> MOUSE_LEFT = <span style="color:rgb(240, 141, 73); font-weight:400;">0b1</span>, MOUSE_MIDDLE = <span style="color:rgb(240, 141, 73); font-weight:400;">0b10</span>, MOUSE_RIGHT = <span style="color:rgb(240, 141, 73); font-weight:400;">0b100</span>, MOUSE_X1 = <span style="color:rgb(240, 141, 73); font-weight:400;">0b1000</span>, MOUSE_X2 = <span style="color:rgb(240, 141, 73); font-weight:400;">0b10000</span> };</code>
This mouse structure stores the cursor position within our window and the state of the standard mouse buttons, which Windows considers to be left, middle, right, “x1” and “x2”. I made this enumerator to assign each of these buttons to a different bit, which is how we can store all 5 buttons in a single byte in the structure.

<code>		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> WM_SYSKEYDOWN:
		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> WM_SYSKEYUP:
		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> WM_KEYDOWN:
		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> WM_KEYUP: {
			<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(has_focus) {
				<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">bool</span> key_is_down, key_was_down;
				key_is_down  = ((lParam &amp; (<span style="color:rgb(240, 141, 73); font-weight:400;">1</span> &lt;&lt; <span style="color:rgb(240, 141, 73); font-weight:400;">31</span>)) == <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>);
				key_was_down = ((lParam &amp; (<span style="color:rgb(240, 141, 73); font-weight:400;">1</span> &lt;&lt; <span style="color:rgb(240, 141, 73); font-weight:400;">30</span>)) != <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>);
				<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(key_is_down != key_was_down) {
					keyboard[(<span style="color:rgb(136, 174, 206); font-weight:400;">uint8_t</span>)wParam] = key_is_down;
					<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(key_is_down) {
						<span style="color:rgb(240, 141, 73); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">switch</span></span>(wParam) {
							<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> VK_ESCAPE: quit = <span style="color:rgb(240, 141, 73); font-weight:400;">true</span>; <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;
						}
					}
				}
			}
		} <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;</code>
We can handle all 4 keyboard events in one block of code. If our window has focus when it processes the event, we check the current and previous state of the key by extracting the 31st and 32nd bits of lParam. We do this to ignore key repeat events, where you hold down a key and after a few seconds it starts sending repeated key events. wParam stores the key index, which we use to set the corresponding element in the keyboard array to the new value. This results in an automatically updating array holding the keyboard state. I’ve also demonstrated how you could use a switch statement to immediately respond to the press of any keys here.

Latching the keyboard events like this does cause the issue that, if you press and hold a key, tab out of the window, then release the key, you won’t receive a key release event so in your application the key will still be considered pressed. That’s why in many older games you can tab out, then when you tab back in find that your character has walked straight over a cliff.

<code>		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> WM_KILLFOCUS: {
			has_focus = <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
			<span style="color:rgb(240, 141, 73); font-weight:400;">memset</span>(keyboard, <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">256</span> * <span style="color:rgb(240, 141, 73); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">sizeof</span></span>(keyboard[<span style="color:rgb(240, 141, 73); font-weight:400;">0</span>]));
			mouse.buttons = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>;
		} <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;

		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> WM_SETFOCUS: has_focus = <span style="color:rgb(240, 141, 73); font-weight:400;">true</span>; <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;</code>
To solve this we clear the keyboard array whenever we receive the “WM_KILLFOCUS” event. We can also use this “has_focus” variable just because Windows can in some circumstances send you keyboard events even when your window doesn’t have focus.

<code>		<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">int</span> keyboard_x = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>, keyboard_y = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(keyboard[VK_RIGHT] || keyboard[<span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;D&#x27;</span>]) ++keyboard_x;
		<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(keyboard[VK_LEFT]  || keyboard[<span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;A&#x27;</span>]) --keyboard_x;
		<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(keyboard[VK_UP]    || keyboard[<span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;W&#x27;</span>]) ++keyboard_y;
		<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(keyboard[VK_DOWN]  || keyboard[<span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;S&#x27;</span>]) --keyboard_y;</code>
In the main program loop we can check the state of any key using the <a href="https://docs.microsoft.com/en-us/windows/win32/inputdev/virtual-key-codes" target="_blank">“virtual key codes”, listed in Microsoft’s documentation.</a> The upper-case ASCII character values of all the letters correspond to their virtual key codes so use 'A' to reference that keyboard key. Here I’m checking the WASD and arrow keys to move a dot around the screen.

<code>		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> WM_MOUSEMOVE: {
			mouse.x = <span style="color:rgb(240, 141, 73); font-weight:400;">LOWORD</span>(lParam);
			mouse.y = frame.h - <span style="color:rgb(240, 141, 73); font-weight:400;">1</span> - <span style="color:rgb(240, 141, 73); font-weight:400;">HIWORD</span>(lParam);
		} <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;</code>
“WM_MOUSEMOVE” tells us the position of the cursor relative to our window, where the top-left is 0,0 and the bottom-right is width-1, height-1. X and Y are the low and high two bytes of lParam. Since our drawing code uses the opposite y axis, we subtract the mouse y coordinate from height-1 to invert it.

<code>		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> WM_LBUTTONDOWN: mouse.buttons |=  MOUSE_LEFT;   <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> WM_LBUTTONUP:   mouse.buttons &amp;= ~MOUSE_LEFT;   <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> WM_MBUTTONDOWN: mouse.buttons |=  MOUSE_MIDDLE; <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> WM_MBUTTONUP:   mouse.buttons &amp;= ~MOUSE_MIDDLE; <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> WM_RBUTTONDOWN: mouse.buttons |=  MOUSE_RIGHT;  <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> WM_RBUTTONUP:   mouse.buttons &amp;= ~MOUSE_RIGHT;  <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;</code>
Here we handle the left, middle and right mouse buttons. When they’re pressed we use bitwise OR to set the correct bit in the mouse.buttons byte to true, and on release we bitwise AND the inverse to set the correct bit to false and leave all the other bits as whatever value they already were.

<code>		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> WM_XBUTTONDOWN: {
			<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(<span style="color:rgb(240, 141, 73); font-weight:400;">GET_XBUTTON_WPARAM</span>(wParam) == XBUTTON1) {
					 mouse.buttons |= MOUSE_X1;
			} <span style="color:rgb(136, 174, 206); font-weight:400;">else</span> { mouse.buttons |= MOUSE_X2; }
		} <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> WM_XBUTTONUP: {
			<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(<span style="color:rgb(240, 141, 73); font-weight:400;">GET_XBUTTON_WPARAM</span>(wParam) == XBUTTON1) {
					 mouse.buttons &amp;= ~MOUSE_X1;
			} <span style="color:rgb(136, 174, 206); font-weight:400;">else</span> { mouse.buttons &amp;= ~MOUSE_X2; }
		} <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;</code>
The X1 and X2 buttons are handled from the same WM_XBUTTONDOWN and UP events and we extract whether it was X1 or X2 from wParam using the GET_XBUTTON_WPARAM macro.

<code>		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> WM_MOUSEWHEEL: {
			<span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;%s\n&quot;</span>, wParam &amp; <span style="color:rgb(240, 141, 73); font-weight:400;">0b10000000000000000000000000000000</span> ? <span style="color:rgb(181, 189, 104); font-weight:400;">&quot;Down&quot;</span> : <span style="color:rgb(181, 189, 104); font-weight:400;">&quot;Up&quot;</span>);
		} <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;</code>
In the MOUSEWHEEL event the scroll direction is stored in the 32nd bit of wParam.

<code>		<span class="fadecode">case WM_KILLFOCUS: {
			has_focus = false;
			memset(keyboard, 0, 256 * sizeof(keyboard[0]));</span>
			mouse.buttons = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>;
		<span class="fadecode">} break;</span></code>
Like the keyboard, whenever the window loses focus we set mouse.buttons to 0.

<code>		<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(mouse.buttons &amp; MOUSE_LEFT) frame.pixels[mouse.x + mouse.y*frame.w] = <span style="color:rgb(240, 141, 73); font-weight:400;">0x00ffffff</span>;</code>
In the main program loop we check if the left button is pressed, then set the pixel at the mouse position to white.

Now when we run the program we get two white dots: one that zips around as we use WASD or the arrow keys, and another which only appears when holding the left mouse button.

<video src="program_running.mp4" autoplay muted loop></video>

That’s how to handle input with the Windows native library in C. In my next tutorials I’ll show you how to play real-time audio, load and play sound files and load and display images.

<hr>
If you've got questions about any of the code feel free to e-mail me or comment on the <a href="https://www.youtube.com/watch?v=ZjfdkDIIw2A" target="_blank">youtube video</a>. I'll try to answer them, or someone else might come along and help you out. If you've got any extra tips about how this code can be better or just more useful info about the code, let me know so I can update the tutorial.

Thanks to Froggie717 for criticisms and correcting errors in this tutorial.

Cheers.

</article>

</body>

<?php include "/blog/blogbottom.html" ?>

<?php include "footer_common.html" ?>

</html>
