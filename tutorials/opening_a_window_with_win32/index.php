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

<h4>Opening a window with Win32 in C</h4>

Note: To follow this tutorial you should have a basic understanding of C.

This tutorial is split into a <a href="#Code Walkthrough">code walkthrough</a> with the detail required for a good understanding and a <a href="#Deep Dive">deep dive</a> which covers a great deal of extra detail. The code walkthrough is also available as a <a href="https://www.youtube.com/watch?v=TMFMpCabbDU" target="_blank">video.</a>

In this tutorial I'll show you how to open a window with the Windows native library in C and respond to "close" events.

Firstly, here's the code <a href="main.c" target="_blank">(or download here)</a>:
NOTE: Click any of the <a>hyperlinked words</a> to visit the MSDN documentation page for them.

<code><span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">#<span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">define</span> UNICODE</span>
<span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">#<span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">define</span> _UNICODE</span>
<span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">#<span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&lt;windows.h&gt;</span></span>
<span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">#<span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&lt;stdbool.h&gt;</span></span>

<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">bool</span> quit = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">false</span>;

LRESULT <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">CALLBACK</span> WindowProcessMessage(HWND, <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">UINT</span>, WPARAM, LPARAM);

<span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> WINAPI WinMain(HINSTANCE hInstance, HINSTANCE hPrevInstance, PSTR pCmdLine, <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">int</span> nCmdShow) {
    WNDCLASS window_class = { <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">0</span> };
    <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">const</span> <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">wchar_t</span> window_class_name[] = L<span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&quot;My Window Class&quot;</span>;
    window_class.lpszClassName = window_class_name;
    window_class.lpfnWndProc = WindowProcessMessage;
    window_class.hInstance = hInstance;
	window_class.hCursor = LoadCursor (NULL, IDC_ARROW);
    
    RegisterClass(&amp;window_class);
    
    HWND window_handle = CreateWindow(window_class_name, L<span style="color:rgb(181, 189, 104); font-weight:400;background:rgba(0, 0, 0, 0);">&quot;Learn to Program Windows&quot;</span>, WS_OVERLAPPEDWINDOW, CW_USEDEFAULT, CW_USEDEFAULT, CW_USEDEFAULT, CW_USEDEFAULT, <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">NULL</span>, <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">NULL</span>, hInstance, <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">NULL</span>);
    <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">if</span>(window_handle == <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">NULL</span>) { <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">return</span> <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">-1</span>; }
    
    ShowWindow(window_handle, nCmdShow);
    
    <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">while</span>(!quit) {
        MSG message;
        <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">while</span>(PeekMessage(&amp;message, <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">NULL</span>, <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">0</span>, <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">0</span>, PM_REMOVE)) {
            TranslateMessage(&amp;message);
            DispatchMessage(&amp;message);
        }
        
        <span style="color:rgb(153, 153, 153); font-weight:400;background:rgba(0, 0, 0, 0);">// Do game stuff here</span>
    }
    
    <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">return</span> <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">0</span>;
}

LRESULT <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">CALLBACK</span> WindowProcessMessage(HWND window_handle, <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">UINT</span> message, WPARAM wParam, LPARAM lParam) {
    <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">switch</span>(message) {
        <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">case</span> WM_QUIT:
        <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">case</span> WM_DESTROY: {
            quit = <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">true</span>;
        } <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">break</span>;
        
        <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">default</span>: { <span style="color:rgb(153, 153, 153); font-weight:400;background:rgba(0, 0, 0, 0);">// Message not handled; pass on to default message handling function</span>
            <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">return</span> DefWindowProc(window_handle, message, wParam, lParam);
        } <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">break</span>;
    }
    <span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">return</span> <span style="color:rgb(240, 141, 73); font-weight:400;background:rgba(0, 0, 0, 0);">0</span>;
}</code>

You can build with GCC:

<code>gcc main.c</code>

Or here's a build script using vc <a href="build.bat" target="_blank">(download here)</a>:

<code>call "C:\Program Files\Microsoft Visual Studio\2022\Community\VC\Auxiliary\Build\vcvarsall.bat" x86_amd64
cl main.c user32.lib
pause</code>

You may need to edit the directory in the first line, or run "Developer Powershell for VS 2022" from your start menu and skip the first line.
<hr>
<h4><a id="Code Walkthrough" style="color:rgb(255,255,255);">Code Walkthrough</a></h4>

<code>><span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">#<span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">define</span> UNICODE</span>
<span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">#<span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">define</span> _UNICODE</span>
<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;">&lt;windows.h&gt;</span></span>
<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;">&lt;stdbool.h&gt;</span></span></code>
Everything we need is in “windows.h”. I’ve also included “stdbool” for the “true” and “false” macros. Regarding those definitions of UNICODE and _UNICODE, more explanation is in the deep dive. For now, the Win32 API doesn't use regular old char* strings, but wchar_t* strings instead. Each character is 2 bytes instead of one and supports many languages and symbols. Strings with a leading L before the quote, like L"Hello world" are wide character strings.

<code><span style="color:rgb(136, 174, 206); font-weight:400;">int</span> WINAPI WinMain(HINSTANCE hInstance, HINSTANCE hPrevInstance, PSTR pCmdLine, <span style="color:rgb(136, 174, 206); font-weight:400;">int</span> nCmdShow) {</code>
Win32 defines its own main() function entry-point and handles some behind-the-scenes Windows setup, then calls “WinMain” which we can treat as the new entry-point for our program.

<code>    WNDCLASS window_class = { <span style="color:rgb(240, 141, 73); font-weight:400;">0</span> };
    <span style="color:rgb(136, 174, 206); font-weight:400;">const</span> <span style="color:rgb(136, 174, 206); font-weight:400;">wchar_t</span> window_class_name[] = L<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;My Window Class&quot;</span>;
    window_class.lpszClassName = window_class_name;
    window_class.lpfnWndProc = WindowProcessMessage;
    window_class.hInstance = hInstance;
	window_class.hCursor = LoadCursor (NULL, IDC_ARROW);

    RegisterClass(&amp;window_class);
    
    HWND window_handle = CreateWindow(window_class_name, L<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;Learn to Program Windows&quot;</span>, WS_OVERLAPPEDWINDOW, CW_USEDEFAULT, CW_USEDEFAULT, CW_USEDEFAULT, CW_USEDEFAULT, <span style="color:rgb(240, 141, 73); font-weight:400;">NULL</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">NULL</span>, hInstance, <span style="color:rgb(240, 141, 73); font-weight:400;">NULL</span>);
    <span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(window_handle == <span style="color:rgb(240, 141, 73); font-weight:400;">NULL</span>) { <span style="color:rgb(136, 174, 206); font-weight:400;">return</span> <span style="color:rgb(240, 141, 73); font-weight:400;">-1</span>; }</code>
We create a window class to hold information about the window. The name is used to reference our class later. “lpfnWndProc” is a pointer to a function Windows will call in order to handle events, or as Windows calls them, messages. The hCursor line sets our window's cursor to the normal windows arrow - without this, the cursor will stay as whatever it was when it entered the window. We “Register” the class with Windows, then create a window based on it.
The name identifies our window class. We give our window a title, and tell Windows what kind of window to create with a “window style”. WS_OVERLAPPEDWINDOW combines the usual border, title bar and so on. The next 4 arguments are the x and y coordinates of the top-left of our window, then the width and height. With an “overlapped” window you can let Windows decide these values with CW_USEDEFAULT. We check the function was successful then continue.

<code>    ShowWindow(window_handle, nCmdShow);</code>
ShowWindow makes our window finally appear. nCmdShow tells Windows whether our program was launched with some specific settings such as “maximized” or “minimized” which could be set through a program shortcut.

<code>    <span style="color:rgb(136, 174, 206); font-weight:400;">while</span>(!quit) {
        MSG message;
        <span style="color:rgb(136, 174, 206); font-weight:400;">while</span>(PeekMessage(&amp;message, <span style="color:rgb(240, 141, 73); font-weight:400;">NULL</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>, PM_REMOVE)) {
            TranslateMessage(&amp;message);
            DispatchMessage(&amp;message);
        }
        
        <span style="color:rgb(153, 153, 153); font-weight:400;">// Do game stuff here</span>
    }</code>
We have a global boolean variable we can use to exit our main program loop, then some message processing code. PeekMessage() checks for the next message and removes it from the queue with the PM_REMOVE flag. TranslateMessage() takes virtual key stroke messages and adds applicable “character” messages to the message queue. You can skip that function if you’re only going to handle virtual keys instead of characters. DispatchMessage() passes the message over to our window class function pointer lpfnWndProc, and we find ourselves inside the WindowProcessMessage function.

<code>LRESULT <span style="color:rgb(240, 141, 73); font-weight:400;">CALLBACK</span> WindowProcessMessage(HWND window_handle, <span style="color:rgb(240, 141, 73); font-weight:400;">UINT</span> message, WPARAM wParam, LPARAM lParam) {
    <span style="color:rgb(136, 174, 206); font-weight:400;">switch</span>(message) {
        <span style="color:rgb(136, 174, 206); font-weight:400;">case</span> WM_QUIT:
        <span style="color:rgb(136, 174, 206); font-weight:400;">case</span> WM_DESTROY: {
            quit = <span style="color:rgb(240, 141, 73); font-weight:400;">true</span>;
        } <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;
        
        <span style="color:rgb(136, 174, 206); font-weight:400;">default</span>: { <span style="color:rgb(153, 153, 153); font-weight:400;">// Message not handled; pass on to default message handling function</span>
            <span style="color:rgb(136, 174, 206); font-weight:400;">return</span> DefWindowProc(window_handle, message, wParam, lParam);
        } <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;
    }
    <span style="color:rgb(136, 174, 206); font-weight:400;">return</span> <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>;
}</code>
We switch on the message; if it’s a QUIT or DESTROY message we set our quit variable to true so that our program will exit, otherwise we pass the message on to Windows’ “default window procedure”.

<code>gcc main.c</code>
Compiling with GCC is as simple as it gets.

<code>cl main.c user32.lib</code>
To compile with vc we need to link to user32.lib which contains implementations for the Win32 functions in the code.

With this code our program window will stay open and responsive until we hit the X button, alt+F4 or end task from task manager, at which point it will gracefully close.
<hr>
<h4><a id="Deep Dive" style="color:rgb(255,255,255);">Deep Dive</a></h4>

<code><span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">#<span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">define</span> UNICODE</span>
<span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">#<span style="color:rgb(136, 174, 206); font-weight:400;background:rgba(0, 0, 0, 0);">define</span> _UNICODE</span></code>
Originally the Win32 library used standard ANSI C strings - one byte per character. In Windows NT, in order to support more languages (I'm talking about Chinese and such, not programming languages) Microsoft switched to "wide character" strings, where each character is 2 bytes. If you ignore this and use ANSI encoding, some functions will be unavailable, and every function that uses a string will convert it to unicode under the hood. Therefore it's better to embrace the unicode strings, which you let Win32 know by #defining UNICODE and _UNICODE before #including windows.h. You could alternatively define them in your build script. The end result of all this is that our strings need a big L before the first " mark, and our char* variables become wchar_t* instead.
Thanks to starcow for getting me to look into this issue properly.

<code><span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;">&lt;windows.h&gt;</span></span></code>
In the first line it may seem heavy-handed to #include <b>all</b> of "windows.h" rather than just the few files with the functions we need, but you'll find by looking through the Win32 headers that they often #include "windows.h" themselves so we leave it to the compiler to trim the fat.

<code>LRESULT <span style="color:rgb(240, 141, 73); font-weight:400;">CALLBACK</span> WindowProcessMessage(HWND, <span style="color:rgb(240, 141, 73); font-weight:400;">UINT</span>, WPARAM, LPARAM);</code>
Function declarations don't require the variable names. By leaving them out at the declaration if you want to change them at the definition you won't have to also edit the declaration.

<code><span style="color:rgb(136, 174, 206); font-weight:400;">int</span> WINAPI <span style="color:rgb(240, 141, 73); font-weight:400;">WinMain</span><span style="color:rgb(255, 255, 255); font-weight:400;">(HINSTANCE hInstance, HINSTANCE hPrevInstance, PSTR pCmdLine, <span style="color:rgb(136, 174, 206); font-weight:400;">int</span> nCmdShow)</span> </span>{</code>

hInstance is a handle to this instance of your application, and is used to identify it to Windows.
hPrevInstance is a handle to the previous instance of this application, and is always NULL. This is a historical artifact from when multiple instances of the same program would share various things like the Window Class. Nowadays Windows gives everything its own arena for those things and so hPrevInstance is not used.
pCmdLine is the command line used to launch the application, excluding the application.exe itself. If you run your program from a command line and want to attach extra arguments this is where you'll retrieve them.
nCmdShow holds information about whether to minimize or maximize the window and is usually passed to ShowWindow(). These can be set in a program shortcut.
wWinMain also exists with the same function signature. The only difference is for pCmdLine. In WinMain it's passed as an ANSI string and wWinMain uses Unicode. If you use WinMain you can still retrieve the ANSI string using the GetCommandLine function.
WinMain must have these specific variable types, but the names can be whatever you want. You may want to change the names to fit your variable naming conventions, or rename hPrevInstance to NULLInstance since it's always NULL.

<code>    WNDCLASS window_class = { <span style="color:rgb(240, 141, 73); font-weight:400;">0</span> };
    <span style="color:rgb(136, 174, 206); font-weight:400;">const</span> <span style="color:rgb(136, 174, 206); font-weight:400;">wchar_t</span> window_class_name[] = L<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;My Window Class&quot;</span>;
    window_class.lpszClassName = (PCSTR)window_class_name;
    window_class.lpfnWndProc = WindowProcessMessage;
    window_class.hInstance = hInstance;</code>
In C by default a character is one byte, but Win32 uses wchar_t "wide characters" which are 2 bytes. The L before the "My Window Class" string indicates to the compiler to consider these "wide characters".
hInstance is a handle to the current program instance, but for some reason substituting NULL here and in CreateWindow works fine. We're supposed to give hInstance over to Windows so that it knows which program or dll registered the class and created the window but for now it doesn't seem to have any effect. I expect to discover some functions in the future which require a matching hInstance between multiple objects, like a window class, window itself and some other stuff.

<code>    RegisterClass(&amp;window_class);</code>
RegisterClass is one of several functions which has been superseded by an extended version, RegisterClassEx. If I don't need the extended functionality of those functions though, I find it more pleasant to use the base versions and avoid having to pass an extra "NULL" or fill out a few extra struct variables.

<code>    HWND window_handle = CreateWindow((PCSTR)window_class_name, <span style="color:rgb(181, 189, 104); font-weight:400;">&quot;Learn to Program Windows&quot;</span>, WS_OVERLAPPEDWINDOW, CW_USEDEFAULT, CW_USEDEFAULT, CW_USEDEFAULT, CW_USEDEFAULT, <span style="color:rgb(240, 141, 73); font-weight:400;">NULL</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">NULL</span>, hInstance, <span style="color:rgb(240, 141, 73); font-weight:400;">NULL</span>);</code>
CreateWindow, along with several other Win32 functions, aliases to either CreateWindowA or CreateWindowW, depending on whether your compiler is set to Ansi or Unicode. Most of the time this doesn't matter but if you want to specify you can use those directly instead.
The third argument is the "window style". This defines things like whether your window has a border, title bar, scroll bars and other elements. They can be bitwise ORed together. WS_OVERLAPPEDWINDOW includes the normal window styles and is usually the one you want. WS_POPUP can be used instead to create a borderless window. Here's some example code for making a borderless fullscreen window:
<code>    HWND window_handle;
    {
    RECT desktop_rect;
    HWND desktop_handle = <span style="color:rgb(240, 141, 73); font-weight:400;">GetDesktopWindow</span>();
    <span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(desktop_handle) <span style="color:rgb(240, 141, 73); font-weight:400;">GetWindowRect</span>(desktop_handle, &amp;desktop_rect);
        <span style="color:rgb(136, 174, 206); font-weight:400;">else</span> { desktop_rect.left = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>; desktop_rect.top = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>; desktop_rect.right = <span style="color:rgb(240, 141, 73); font-weight:400;">800</span>; desktop_rect.bottom = <span style="color:rgb(240, 141, 73); font-weight:400;">600</span>; }
        window_handle = <span style="color:rgb(240, 141, 73); font-weight:400;">CreateWindow</span>((PCSTR)window_class_name, <span style="color:rgb(181, 189, 104); font-weight:400;">&quot;Learn to Program Windows&quot;</span>, WS_POPUP, desktop_rect.left,desktop_rect.top, desktop_rect.right - desktop_rect.left,desktop_rect.bottom-desktop_rect.top, <span style="color:rgb(240, 141, 73); font-weight:400;">NULL</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">NULL</span>, hInstance, <span style="color:rgb(240, 141, 73); font-weight:400;">NULL</span>);
    }</code>
I've retrieved the size of the screen with GetWindowRect. If you just substitute this code your window will be invisible until you "paint" something to it, which I've added to the event handling in WindowProcessMessage:
<code>        <span style="color:rgb(136, 174, 206); font-weight:400;">case</span> WM_PAINT: {
            HDC hdc;
            PAINTSTRUCT ps;
            hdc = <span style="color:rgb(240, 141, 73); font-weight:400;">BeginPaint</span>(window_handle, &amp;ps);
            <span style="color:rgb(240, 141, 73); font-weight:400;">FillRect</span>(hdc, &amp;ps.rcPaint, (HBRUSH) (COLOR_WINDOW+<span style="color:rgb(240, 141, 73); font-weight:400;">1</span>));
            <span style="color:rgb(240, 141, 73); font-weight:400;">EndPaint</span>(window_handle, &amp;ps);
        } <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;</code>
I'll explain this type of code more in my pixel drawing tutorial. The WM_PAINT event is where you can draw things to your window. We just fill the entire window with white here.
Instead of calling ShowWindow later, you can OR in the WS_VISIBLE window style as I did in the borderless window code, although you'll ignore nCmdShow.
The 4th through 7th arguments are the x, y, width and height of your window. CW_USEDEFAULT is whatever Windows decides and is usually okay to get you a working window whatever your screen size. CW_USEDEFAULT can't be used with the WS_POPUP window style for borderless windows as it will result in a 0 width/height window at 0,0 coordinates. Another note, if you use CW_USEDEFAULT for the x ordinate and width, the y ordinate and height will be ignored and also use default values even if you fill them in.
Next is the handle to the parent window. This should only be relevant if you're making popup boxes or other child windows.
Then the handle to the menu for the window. NULL lets Windows derive this from the window class, but for games you usually don't want a menu at all.
The handle to the program instance is next. Strangely enough I've found that you can pass NULL here too and things work fine, but I imagine I'll run into some situation in the future that requires this to be set correctly so I just do as the almighty docs say and pass in hInstance.
The extended version, CreateWindowEx supports further window styles.
If CreateWindow fails it returns NULL.

<code>    ShowWindow(window_handle, nCmdShow);</code>
nCmdShow includes information from Windows on how to show your window. For example, if your program is run from a shortcut you can set Normal, Minimized or Maximized here. You can also pass one of these values but they don't have a great deal of use for game development.

<code>    <span style="color:rgb(136, 174, 206); font-weight:400;">while</span>(!quit) {
        MSG message;
        <span style="color:rgb(136, 174, 206); font-weight:400;">while</span>(PeekMessage(&amp;message, <span style="color:rgb(240, 141, 73); font-weight:400;">NULL</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>, PM_REMOVE)) {
            TranslateMessage(&amp;message);
            DispatchMessage(&amp;message);
        }</code>
The MSG structure includes the time that the message was placed in the message queue. You could imagine using that to make player button presses happen at the specific sub-frame time for greater responsiveness. The precision is only to the millisecond, but it could be worth it if you're only updating 30 or 60 times per second. A button press happening at the end of a 16 or 33 millisecond frame is a significant delay compared to at the first or second millisecond when a button may have been pressed. I find it more useful to just update more often, say a few hundred times per second, then the difference between button press time and frame calculation time is negligible.
As your program runs, Windows puts all sorts of messages on the message queue. Button presses, devices being plugged in and removed, your window gaining and losing focus and tonnes more. In order to get the stuff we want we have to pop each message off the queue one at a time and check what it is.
Other than PeekMessage, there is also GetMessage which blocks until a message comes in. If you handle messages in a separate thread, GetMessage could be useful. Otherwise PeekMessage will allow your program to continue running when there are no new messages.
The second argument is a handle to the window for which messages should be retrieved. If your program has multiple windows you might want to check each window's messages individually but NULL will check for all windows on this thread, or in this case the only window.
The next two arguments are message filter minimum and maximum. I haven't had a use for these but I think the filtered messages get left on the queue for later so it's usually best to just process all the messages instead of a subset.
Last is whether you want to remove the retrieved message from the queue.
TranslateMessage takes keydown/keyup messages and, if they're for keyboard characters, adds a character message to the queue. These may or may not be necessary for your program - you may want to just handle virtual key messages instead of character messages but I included it so that people don't try to process character messages and wonder why they're not working. The message you pass in to TranslateMessage is unchanged so you can still process the initial key message even if it adds a character message to the queue.
DispatchMessage basically just calls whatever function you pointed to with lpfnWndProc earlier in the window class: WindowProcessMessage.

<code>    <span style="color:rgb(136, 174, 206); font-weight:400;">switch</span>(message) {
        <span style="color:rgb(136, 174, 206); font-weight:400;">case</span> WM_QUIT:
        <span style="color:rgb(136, 174, 206); font-weight:400;">case</span> WM_DESTROY: {
            quit = <span style="color:rgb(240, 141, 73); font-weight:400;">true</span>;
        } <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;</code>
The DESTROY message is just a message to destroy your window, not necessarily to close your program. The usual behaviour is to add a QUIT message to the queue, then close your program when that message is processed but I just skipped the extra steps and handled both messages as a quit. You might want to handle window destruction as a recoverable error by remaking the window, as your window might get destroyed by a graphics driver crash or something.
<hr>
There's always more you can say about code, but this is where I'll end it for now. If you've got questions about any of the code feel free to e-mail me or comment on the <a href="https://www.youtube.com/watch?v=TMFMpCabbDU" target="_blank">youtube video</a>. I'll try to answer them, or someone else might come along and help you out. If you've got any extra tips about how this code can be better or just more useful info about the code, let me know. And lastly, what do you think of this two-part format with a walkthrough in video+text and a text-only deep-dive?

Thanks to Froggie717 for criticisms and correcting errors in this tutorial, and to my followers on social media for giving me feedback throughout the creation of this tutorial.

Cheers.

</article>

</body>

<?php include "/blog/blogbottom.html" ?>

<?php include "footer_common.html" ?>

</html>
