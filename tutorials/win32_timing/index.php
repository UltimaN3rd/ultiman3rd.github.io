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

<h4>High Precision Timing with Win32 in C</h4>

This tutorial is also available as a <a href="https://www.youtube.com/watch?v=LFkLLSOXQIc" target="_blank">video.</a>

In this tutorial I’ll show you how to perform at least microsecond-precision timing on Windows in C.  I’ll also show the two main applications for such timing which are measuring code performance and rate limiting – like limiting your game’s render loop to 60Hz.

Note: I’m compiling this code with GCC.
Note: Click any of the <a>hyperlinked words</a> to visit the MSDN documentation page for them.

<a href="just_timing.c" download>just_timing.c</a>
<code><span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">define</span> WIN32_LEAN_AND_MEAN</span>
<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;">&lt;windows.h&gt;</span></span>
<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;">&lt;stdio.h&gt;</span></span>

LARGE_INTEGER frequency, a, b;
<span style="color:rgb(136, 174, 206); font-weight:400;">float</span> elapsed_seconds;

<span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">int</span> WINAPI <span style="color:rgb(240, 141, 73); font-weight:400;">WinMain</span> <span style="color:rgb(255, 255, 255); font-weight:400;">(HINSTANCE hInstance, HINSTANCE hPrevInstance, LPSTR lpCmdLine, <span style="color:rgb(136, 174, 206); font-weight:400;">int</span> nShowCmd)</span> </span>{
	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://docs.microsoft.com/en-us/windows/win32/api/profileapi/nf-profileapi-queryperformancefrequency" target="_blank">QueryPerformanceFrequency</a></span>(&amp;frequency);
	<span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;Frequency: %lld ticks per second.\n&quot;</span>, frequency.QuadPart);

	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://docs.microsoft.com/en-us/windows/win32/api/profileapi/nf-profileapi-queryperformancecounter" target="_blank">QueryPerformanceCounter</a></span>(&amp;a);
	<span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;A: %lld\n&quot;</span>, a.QuadPart);

	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://docs.microsoft.com/en-us/windows/win32/api/profileapi/nf-profileapi-queryperformancecounter" target="_blank">QueryPerformanceCounter</a></span>(&amp;b);
	<span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;B: %lld\n&quot;</span>, b.QuadPart);

	elapsed_seconds = (<span style="color:rgb(136, 174, 206); font-weight:400;">float</span>)(b.QuadPart - a.QuadPart) / frequency.QuadPart;
	<span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;Elaped time between A and B: %fs\n&quot;</span>, elapsed_seconds);

	<span style="color:rgb(136, 174, 206); font-weight:400;">return</span> <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>;
}</code>

<a href="zen_timer.h" download>zen_timer.h</a>
<code><span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">pragma</span> once</span>

<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;">&lt;windows.h&gt;</span></span>
<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;">&lt;stdint.h&gt;</span></span>

<span style="color:rgb(136, 174, 206); font-weight:400;">typedef</span> <span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">struct</span> {</span>
	LARGE_INTEGER start, end;
} <span style="color:rgb(136, 174, 206); font-weight:400;">zen_timer_t</span>;

<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> LARGE_INTEGER zen_ticks_per_second = {.QuadPart = <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>};
<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">int64_t</span> zen_ticks_per_microsecond = <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>;

<span style="color:rgb(153, 153, 153); font-weight:400;">// You MUST call ZenTimer_Init() to use ZenTimer, otherwise the tick rate will be set at 1 and you&#x27;ll get garbage.</span>
<span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">inline</span> <span style="color:rgb(136, 174, 206); font-weight:400;">void</span> <span style="color:rgb(240, 141, 73); font-weight:400;">ZenTimer_Init</span><span style="color:rgb(255, 255, 255); font-weight:400;">()</span> </span>{
	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://docs.microsoft.com/en-us/windows/win32/api/profileapi/nf-profileapi-queryperformancefrequency" target="_blank">QueryPerformanceFrequency</a></span>(&amp;zen_ticks_per_second);
	zen_ticks_per_microsecond = zen_ticks_per_second.QuadPart / <span style="color:rgb(240, 141, 73); font-weight:400;">1000000</span>;
}

<span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">inline</span> <span style="color:rgb(136, 174, 206); font-weight:400;">zen_timer_t</span> <span style="color:rgb(240, 141, 73); font-weight:400;">ZenTimer_Start</span><span style="color:rgb(255, 255, 255); font-weight:400;">()</span> </span>{
	<span style="color:rgb(136, 174, 206); font-weight:400;">zen_timer_t</span> timer;
	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://docs.microsoft.com/en-us/windows/win32/api/profileapi/nf-profileapi-queryperformancecounter" target="_blank">QueryPerformanceCounter</a></span>(&amp;timer.start);
	<span style="color:rgb(136, 174, 206); font-weight:400;">return</span> timer;
}

<span style="color:rgb(153, 153, 153); font-weight:400;">// Returns time in microseconds</span>
<span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">inline</span> <span style="color:rgb(136, 174, 206); font-weight:400;">int64_t</span> <span style="color:rgb(240, 141, 73); font-weight:400;">ZenTimer_End</span><span style="color:rgb(255, 255, 255); font-weight:400;">(<span style="color:rgb(136, 174, 206); font-weight:400;">zen_timer_t</span> *timer)</span> </span>{
	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://docs.microsoft.com/en-us/windows/win32/api/profileapi/nf-profileapi-queryperformancecounter" target="_blank">QueryPerformanceCounter</a></span>(&amp;timer-&gt;end);
	<span style="color:rgb(136, 174, 206); font-weight:400;">return</span> (timer-&gt;end.QuadPart - timer-&gt;start.QuadPart) / zen_ticks_per_microsecond;
}</code>

<a href="using_zen_timer.c" download>using_zen_timer.c</a>
<code><span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;">&quot;zen_timer.h&quot;</span></span>
<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;">&lt;stdio.h&gt;</span></span>

<span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">int</span> WINAPI <span style="color:rgb(240, 141, 73); font-weight:400;">WinMain</span><span style="color:rgb(255, 255, 255); font-weight:400;">(HINSTANCE hInstance, HINSTANCE hPrevInstance, LPSTR lpCmdLine, <span style="color:rgb(136, 174, 206); font-weight:400;">int</span> nShowCmd)</span> </span>{
	<span style="color:rgb(240, 141, 73); font-weight:400;">ZenTimer_Init</span>();

	<span style="color:rgb(136, 174, 206); font-weight:400;">zen_timer_t</span> timer = <span style="color:rgb(240, 141, 73); font-weight:400;">ZenTimer_Start</span>();

	<span style="color:rgb(136, 174, 206); font-weight:400;">for</span>(<span style="color:rgb(136, 174, 206); font-weight:400;">int</span> i = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>; i &lt; <span style="color:rgb(240, 141, 73); font-weight:400;">1000</span>; ++i) {
		<span style="color:rgb(240, 141, 73); font-weight:400;">rand</span>();
	}

	<span style="color:rgb(136, 174, 206); font-weight:400;">int64_t</span> time = <span style="color:rgb(240, 141, 73); font-weight:400;">ZenTimer_End</span>(&amp;timer);

	<span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;1000 rand()s took: %lldus\n&quot;</span>, time);

	<span style="color:rgb(136, 174, 206); font-weight:400;">return</span> <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>;
}</code>

<a href="rate_limiting.c" download>rate_limiting.c</a>
<code><span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">define</span> WIN32_LEAN_AND_MEAN</span>
<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;">&lt;windows.h&gt;</span></span>
<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;">&lt;mmsystem.h&gt;</span></span>
<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;">&lt;stdio.h&gt;</span></span>
<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;">&lt;stdint.h&gt;</span></span>
<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;">&lt;conio.h&gt;</span></span>

<span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">int</span> WINAPI <span style="color:rgb(240, 141, 73); font-weight:400;">WinMain</span><span style="color:rgb(255, 255, 255); font-weight:400;">(HINSTANCE hInstance, HINSTANCE hPrevInstance, LPSTR lpCmdLine, <span style="color:rgb(136, 174, 206); font-weight:400;">int</span> nShowCmd)</span> </span>{
	LARGE_INTEGER ticks_per_second, start, current;
	<span style="color:rgb(136, 174, 206); font-weight:400;">int64_t</span> ticks_per_loop, ticks_per_millisecond;
	<span style="color:rgb(136, 174, 206); font-weight:400;">unsigned</span> <span style="color:rgb(136, 174, 206); font-weight:400;">int</span> loop_count = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>;

	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://docs.microsoft.com/en-us/windows/win32/api/timeapi/nf-timeapi-timebeginperiod" target="_blank">timeBeginPeriod</a></span>(<span style="color:rgb(240, 141, 73); font-weight:400;">1</span>);

	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://docs.microsoft.com/en-us/windows/win32/api/profileapi/nf-profileapi-queryperformancefrequency" target="_blank">QueryPerformanceFrequency</a></span>(&amp;ticks_per_second);
	ticks_per_millisecond = ticks_per_second.QuadPart / <span style="color:rgb(240, 141, 73); font-weight:400;">1000</span>;

	ticks_per_loop = ticks_per_second.QuadPart / <span style="color:rgb(240, 141, 73); font-weight:400;">5</span>;

	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://docs.microsoft.com/en-us/windows/win32/api/profileapi/nf-profileapi-queryperformancecounter" target="_blank">QueryPerformanceCounter</a></span>(&amp;start);
	<span style="color:rgb(136, 174, 206); font-weight:400;">while</span>(!<span style="color:rgb(240, 141, 73); font-weight:400;">kbhit</span>()) {
		<span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;%u &quot;</span>, ++loop_count);

		<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://docs.microsoft.com/en-us/windows/win32/api/profileapi/nf-profileapi-queryperformancecounter" target="_blank">QueryPerformanceCounter</a></span>(&amp;current);

		<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">int64_t</span> sleep_time;
		sleep_time = (start.QuadPart + ticks_per_loop - current.QuadPart) / ticks_per_millisecond - <span style="color:rgb(240, 141, 73); font-weight:400;">2</span>;

		<span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;Sleeping: %lldms\n&quot;</span>, sleep_time);

		<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://docs.microsoft.com/en-us/windows/win32/api/synchapi/nf-synchapi-sleep" target="_blank">Sleep</a></span>(sleep_time);

		<span style="color:rgb(136, 174, 206); font-weight:400;">do</span> {
			<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://docs.microsoft.com/en-us/windows/win32/api/synchapi/nf-synchapi-sleep" target="_blank">Sleep</a></span>(<span style="color:rgb(240, 141, 73); font-weight:400;">0</span>);
			<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://docs.microsoft.com/en-us/windows/win32/api/profileapi/nf-profileapi-queryperformancecounter" target="_blank">QueryPerformanceCounter</a></span>(&amp;current);
		} <span style="color:rgb(136, 174, 206); font-weight:400;">while</span>(current.QuadPart &lt; start.QuadPart + ticks_per_loop);

		start.QuadPart += ticks_per_loop;
		<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(current.QuadPart - start.QuadPart &gt; ticks_per_loop)
		start = current;
	}

	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://docs.microsoft.com/en-us/windows/win32/api/timeapi/nf-timeapi-timeendperiod" target="_blank">timeEndPeriod</a></span>(<span style="color:rgb(240, 141, 73); font-weight:400;">1</span>);

	<span style="color:rgb(136, 174, 206); font-weight:400;">return</span> <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>;
}</code>

<a href="build.bat" download>build.bat</a>
<code>gcc -o just_timing.exe just_timing.c
gcc -o using_zen_timer.exe using_zen_timer.c
gcc -o rate_limiting.exe rate_limiting.c -lwinmm</code>

<hr>
<h4><a id="Code Walkthrough" style="color:rgb(255,255,255);">Code Walkthrough</a></h4>

Modern CPUs have an internal counter which increments at a fixed rate, so all we need to do is find out how many times that counter increments per second, then we can get the value of that counter at any time and divide by the tick rate to get time measurements.

The two functions we’ll use are <a href="https://docs.microsoft.com/en-us/windows/win32/api/profileapi/nf-profileapi-queryperformancefrequency" target="_blank">QueryPerformanceFrequency</a> and <a href="https://docs.microsoft.com/en-us/windows/win32/api/profileapi/nf-profileapi-queryperformancecounter" target="_blank">QueryPerformanceCounter</a>, from windows.h. Both functions use Windows’ LARGE_INTEGER type, which is a union used here as a 64-bit signed integer. To extract the int64 from the union we access the QuadPart member.

<a href="just_timing.c" download>just_timing.c</a>
<code><span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">define</span> WIN32_LEAN_AND_MEAN</span>
<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;">&lt;windows.h&gt;</span></span>
<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;">&lt;stdio.h&gt;</span></span>

LARGE_INTEGER frequency, a, b;
<span style="color:rgb(136, 174, 206); font-weight:400;">float</span> elapsed_seconds;

<span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">int</span> WINAPI <span style="color:rgb(240, 141, 73); font-weight:400;">WinMain</span> <span style="color:rgb(255, 255, 255); font-weight:400;">(HINSTANCE hInstance, HINSTANCE hPrevInstance, LPSTR lpCmdLine, <span style="color:rgb(136, 174, 206); font-weight:400;">int</span> nShowCmd)</span> </span>{
	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://docs.microsoft.com/en-us/windows/win32/api/profileapi/nf-profileapi-queryperformancefrequency" target="_blank">QueryPerformanceFrequency</a></span>(&amp;frequency);
	<span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;Frequency: %lld ticks per second.\n&quot;</span>, frequency.QuadPart);

	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://docs.microsoft.com/en-us/windows/win32/api/profileapi/nf-profileapi-queryperformancecounter" target="_blank">QueryPerformanceCounter</a></span>(&amp;a);
	<span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;A: %lld\n&quot;</span>, a.QuadPart);

	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://docs.microsoft.com/en-us/windows/win32/api/profileapi/nf-profileapi-queryperformancecounter" target="_blank">QueryPerformanceCounter</a></span>(&amp;b);
	<span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;B: %lld\n&quot;</span>, b.QuadPart);

	elapsed_seconds = (<span style="color:rgb(136, 174, 206); font-weight:400;">float</span>)(b.QuadPart - a.QuadPart) / frequency.QuadPart;
	<span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;Elaped time between A and B: %fs\n&quot;</span>, elapsed_seconds);

	<span style="color:rgb(136, 174, 206); font-weight:400;">return</span> <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>;
}</code>
We call <a href="https://docs.microsoft.com/en-us/windows/win32/api/profileapi/nf-profileapi-queryperformancefrequency" target="_blank">QueryPerformanceFrequency</a>, passing a pointer to our frequency variable to be filled, and print the result. Next we call <a href="https://docs.microsoft.com/en-us/windows/win32/api/profileapi/nf-profileapi-queryperformancecounter" target="_blank">QueryPerformanceCounter</a> a couple of times and print out the values. To demonstrate calculating an elapsed time between two QPC calls, here I’m subtracting a from b and dividing by frequency to calculate the elapsed seconds.

That’s all you need for timing, and it doesn’t take much to turn this into a code performance measuring tool. Here’s what I’ve come up with: Zen Timer; get the reference?

<a href="zen_timer.h" download>zen_timer.h</a>
<code><span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">pragma</span> once</span>

<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;">&lt;windows.h&gt;</span></span>
<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;">&lt;stdint.h&gt;</span></span>

<span style="color:rgb(136, 174, 206); font-weight:400;">typedef</span> <span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">struct</span> {</span>
	LARGE_INTEGER start, end;
} <span style="color:rgb(136, 174, 206); font-weight:400;">zen_timer_t</span>;

<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> LARGE_INTEGER zen_ticks_per_second = {.QuadPart = <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>};
<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">int64_t</span> zen_ticks_per_microsecond = <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>;

<span style="color:rgb(153, 153, 153); font-weight:400;">// You MUST call ZenTimer_Init() to use ZenTimer, otherwise the tick rate will be set at 1 and you&#x27;ll get garbage.</span>
<span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">inline</span> <span style="color:rgb(136, 174, 206); font-weight:400;">void</span> <span style="color:rgb(240, 141, 73); font-weight:400;">ZenTimer_Init</span><span style="color:rgb(255, 255, 255); font-weight:400;">()</span> </span>{
	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://docs.microsoft.com/en-us/windows/win32/api/profileapi/nf-profileapi-queryperformancefrequency" target="_blank">QueryPerformanceFrequency</a></span>(&amp;zen_ticks_per_second);
	zen_ticks_per_microsecond = zen_ticks_per_second.QuadPart / <span style="color:rgb(240, 141, 73); font-weight:400;">1000000</span>;
}

<span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">inline</span> <span style="color:rgb(136, 174, 206); font-weight:400;">zen_timer_t</span> <span style="color:rgb(240, 141, 73); font-weight:400;">ZenTimer_Start</span><span style="color:rgb(255, 255, 255); font-weight:400;">()</span> </span>{
	<span style="color:rgb(136, 174, 206); font-weight:400;">zen_timer_t</span> timer;
	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://docs.microsoft.com/en-us/windows/win32/api/profileapi/nf-profileapi-queryperformancecounter" target="_blank">QueryPerformanceCounter</a></span>(&amp;timer.start);
	<span style="color:rgb(136, 174, 206); font-weight:400;">return</span> timer;
}

<span style="color:rgb(153, 153, 153); font-weight:400;">// Returns time in microseconds</span>
<span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">inline</span> <span style="color:rgb(136, 174, 206); font-weight:400;">int64_t</span> <span style="color:rgb(240, 141, 73); font-weight:400;">ZenTimer_End</span><span style="color:rgb(255, 255, 255); font-weight:400;">(<span style="color:rgb(136, 174, 206); font-weight:400;">zen_timer_t</span> *timer)</span> </span>{
	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://docs.microsoft.com/en-us/windows/win32/api/profileapi/nf-profileapi-queryperformancecounter" target="_blank">QueryPerformanceCounter</a></span>(&amp;timer-&gt;end);
	<span style="color:rgb(136, 174, 206); font-weight:400;">return</span> (timer-&gt;end.QuadPart - timer-&gt;start.QuadPart) / zen_ticks_per_microsecond;
}</code>
The Init function gets the tick frequency and Start creates a new timer structure and gets the starting tick count. End takes an existing timer, gets the ending tick count and returns the real time in microseconds between beginning and end.

<a href="using_zen_timer.c" download>using_zen_timer.c</a>
<code><span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;">&quot;zen_timer.h&quot;</span></span>
<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;">&lt;stdio.h&gt;</span></span>

<span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">int</span> WINAPI <span style="color:rgb(240, 141, 73); font-weight:400;">WinMain</span><span style="color:rgb(255, 255, 255); font-weight:400;">(HINSTANCE hInstance, HINSTANCE hPrevInstance, LPSTR lpCmdLine, <span style="color:rgb(136, 174, 206); font-weight:400;">int</span> nShowCmd)</span> </span>{
	<span style="color:rgb(240, 141, 73); font-weight:400;">ZenTimer_Init</span>();

	<span style="color:rgb(136, 174, 206); font-weight:400;">zen_timer_t</span> timer = <span style="color:rgb(240, 141, 73); font-weight:400;">ZenTimer_Start</span>();

	<span style="color:rgb(136, 174, 206); font-weight:400;">for</span>(<span style="color:rgb(136, 174, 206); font-weight:400;">int</span> i = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>; i &lt; <span style="color:rgb(240, 141, 73); font-weight:400;">1000</span>; ++i) {
		<span style="color:rgb(240, 141, 73); font-weight:400;">rand</span>();
	}

	<span style="color:rgb(136, 174, 206); font-weight:400;">int64_t</span> time = <span style="color:rgb(240, 141, 73); font-weight:400;">ZenTimer_End</span>(&amp;timer);

	<span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;1000 rand()s took: %lldus\n&quot;</span>, time);

	<span style="color:rgb(136, 174, 206); font-weight:400;">return</span> <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>;
}</code>
Here’s an example usage of Zen Timer. We include zen_timer.h, Initialize ZenTimer, Start a new timer and store it locally, do something we want to time, then End the timer, recording the returned time locally. Lastly we print out the time. Zen Timer can easily be inserted into whatever existing Windows code you have to measure code performance.

Now on to rate limiting. What we want to do is set a target amount of time for each update, render or whatever. Then time how long the iteration takes and wait until we’ve reached the target amount of time. Here’s a simple program demonstrating that.

<code>	LARGE_INTEGER ticks_per_second, start, current;
	<span style="color:rgb(136, 174, 206); font-weight:400;">int64_t</span> ticks_per_loop;
	<span style="color:rgb(136, 174, 206); font-weight:400;">unsigned</span> <span style="color:rgb(136, 174, 206); font-weight:400;">int</span> loop_count = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>;

	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://docs.microsoft.com/en-us/windows/win32/api/profileapi/nf-profileapi-queryperformancefrequency" target="_blank">QueryPerformanceFrequency</a></span>(&amp;ticks_per_second);

	ticks_per_loop = ticks_per_second.QuadPart / <span style="color:rgb(240, 141, 73); font-weight:400;">5</span>;

	<a href="https://docs.microsoft.com/en-us/windows/win32/api/profileapi/nf-profileapi-queryperformancecounter" target="_blank">QueryPerformanceCounter</a>(<span style="color:rgb(255, 255, 255); font-weight:400;">&amp;</span><span style="color:rgb(136, 174, 206); font-weight:400;">start</span>);</code>
Since our timing functions use ticks, we calculate the amount of ticks per loop by taking the amount of ticks per second and dividing by the number of loops we want to run per second, in this case 5. We get a starting tick count, then proceed through our loop.

<code>	<span style="color:rgb(136, 174, 206); font-weight:400;">while</span>(!kbhit()) {
		<span style="color:rgb(136, 174, 206); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;%u &quot;</span>, ++loop_count);

		<span style="color:rgb(136, 174, 206); font-weight:400;">do</span> {
			<a href="https://docs.microsoft.com/en-us/windows/win32/api/profileapi/nf-profileapi-queryperformancecounter" target="_blank">QueryPerformanceCounter</a>(&amp;current);
		} <span style="color:rgb(136, 174, 206); font-weight:400;">while</span>(current.QuadPart - start.QuadPart &lt; ticks_per_loop);

		start = current;
	}</code>
In this case our loop just prints out the loop count, which, without a rate limit, could easily run thousands of times per second. So we run this while loop checking the current time until the difference between start and current has exceeded the target number of ticks per loop. Then set the new starting ticks to the current ticks.

This delay loop gets the job done but it will exceed the target amount of time by a tiny bit and constantly uses the CPU while waiting.

<code>		start.QuadPart += ticks_per_loop;</code>
We can solve the overshoot by, instead of setting start to current, adding the ticks per loop to start. This way even if one wait loop overshoots by a bit, that amount will be compensated for in the next loop.

However if our thread gets locked for, lets say, a full second, each proceeding loop won’t wait at all until we’ve added the ticks per loop amount to the start variable enough to catch up to all the time that’s passed.

<code>		<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(current.QuadPart - start.QuadPart &gt; ticks_per_loop)
start = current;</code>
To fix that we can cap the difference between start and end to 1 loop’s worth of ticks so that small overshoots in our loop and wait runtime can be compensated for and large overshoots like a temporary thread freeze will act like the program was paused for that period. You can tune these values depending on the performance and requirements of your program.

Instead of a spin-lock, we’d rather put the thread to sleep and let Windows wake it back up when the time has elapsed, so that we can let the CPU cool off between loops instead of melting the thing. The best Windows gives us is the <a href="https://docs.microsoft.com/en-us/windows/win32/api/synchapi/nf-synchapi-sleep" target="_blank">Sleep</a> function, which takes a time in milliseconds to sleep for. By default, <a href="https://docs.microsoft.com/en-us/windows/win32/api/synchapi/nf-synchapi-sleep" target="_blank">Sleep</a> is actually only accurate to around 15ms intervals, so calling <a href="https://docs.microsoft.com/en-us/windows/win32/api/synchapi/nf-synchapi-sleep" target="_blank">Sleep</a>(1) will way overshoot. We can increase precision using <a href="https://docs.microsoft.com/en-us/windows/win32/api/timeapi/nf-timeapi-timebeginperiod" target="_blank">timeBeginPeriod</a>, from mmsystem.h. We now also need to link to winmm.

<code><span style="color:rgb(136, 174, 206); font-weight:400;">int</span> WINAPI <span style="color:rgb(240, 141, 73); font-weight:400;">WinMain</span><span style="color:rgb(255, 255, 255); font-weight:400;">(HINSTANCE hInstance, HINSTANCE hPrevInstance, LPSTR lpCmdLine, <span style="color:rgb(136, 174, 206); font-weight:400;">int</span> nShowCmd)</span> </span>{
	LARGE_INTEGER ticks_per_second, start, current;
	<span style="color:rgb(136, 174, 206); font-weight:400;">int64_t</span> ticks_per_loop, ticks_per_millisecond;
	<span style="color:rgb(136, 174, 206); font-weight:400;">unsigned</span> <span style="color:rgb(136, 174, 206); font-weight:400;">int</span> loop_count = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>;

	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://docs.microsoft.com/en-us/windows/win32/api/timeapi/nf-timeapi-timebeginperiod" target="_blank">timeBeginPeriod</a></span>(<span style="color:rgb(240, 141, 73); font-weight:400;">1</span>);

	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://docs.microsoft.com/en-us/windows/win32/api/profileapi/nf-profileapi-queryperformancefrequency" target="_blank">QueryPerformanceFrequency</a></span>(&amp;ticks_per_second);
	ticks_per_millisecond = ticks_per_second.QuadPart / <span style="color:rgb(240, 141, 73); font-weight:400;">1000</span>;

	ticks_per_loop = ticks_per_second.QuadPart / <span style="color:rgb(240, 141, 73); font-weight:400;">5</span>;

	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://docs.microsoft.com/en-us/windows/win32/api/profileapi/nf-profileapi-queryperformancecounter" target="_blank">QueryPerformanceCounter</a></span>(&amp;start);
	<span style="color:rgb(136, 174, 206); font-weight:400;">while</span>(!<span style="color:rgb(240, 141, 73); font-weight:400;">kbhit</span>()) {
		<span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;%u &quot;</span>, ++loop_count);

		<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://docs.microsoft.com/en-us/windows/win32/api/profileapi/nf-profileapi-queryperformancecounter" target="_blank">QueryPerformanceCounter</a></span>(&amp;current);

		<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">int64_t</span> sleep_time;
		sleep_time = (start.QuadPart + ticks_per_loop - current.QuadPart) / ticks_per_millisecond;

		<span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;Sleeping: %lldms\n&quot;</span>, sleep_time);

		<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://docs.microsoft.com/en-us/windows/win32/api/synchapi/nf-synchapi-sleep" target="_blank">Sleep</a></span>(sleep_time);

		start.QuadPart += ticks_per_loop;
		<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(current.QuadPart - start.QuadPart &gt; ticks_per_loop)
		start = current;
	}

	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://docs.microsoft.com/en-us/windows/win32/api/timeapi/nf-timeapi-timeendperiod" target="_blank">timeEndPeriod</a></span>(<span style="color:rgb(240, 141, 73); font-weight:400;">1</span>);

	<span style="color:rgb(136, 174, 206); font-weight:400;">return</span> <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>;
}</code>
<a href="https://docs.microsoft.com/en-us/windows/win32/api/timeapi/nf-timeapi-timebeginperiod" target="_blank">timeBeginPeriod</a> sets the thread timing resolution to a number of milliseconds, so we set it to one since that’s lowest. Until Windows 10 version 2004, this set the system-wide timer resolution and therefore increased CPU and power usage even after closing your program, so be sure to call <a href="https://docs.microsoft.com/en-us/windows/win32/api/timeapi/nf-timeapi-timeendperiod" target="_blank">timeEndPeriod</a> with the same value at the end of the program. Since that Windows version this is now handled per-process and is no longer a problem for most users.

Now we can <a href="https://docs.microsoft.com/en-us/windows/win32/api/synchapi/nf-synchapi-sleep" target="_blank">Sleep</a> with millisecond <i>precision</i>, though it’s still <i>inaccurate</i>, often missing by 1 millisecond and occasionally more than that. If you want the most accuracy you can <a href="https://docs.microsoft.com/en-us/windows/win32/api/synchapi/nf-synchapi-sleep" target="_blank">Sleep</a> for an amount of milliseconds a few less than needed, then use a spin lock for the rest, and if you want the least CPU usage you can <a href="https://docs.microsoft.com/en-us/windows/win32/api/synchapi/nf-synchapi-sleep" target="_blank">Sleep</a> to the nearest millisecond and let the 0-3 milliseconds of inaccuracy get absorbed into the next loop/<a href="https://docs.microsoft.com/en-us/windows/win32/api/synchapi/nf-synchapi-sleep" target="_blank">Sleep</a> cycle. How you choose to tune that depends on your program requirements.

One extra optimization is that calling <a href="https://docs.microsoft.com/en-us/windows/win32/api/synchapi/nf-synchapi-sleep" target="_blank">Sleep</a>(0) is a kind of hidden feature which sleeps for the remainder of the current “time-slice”. There’s no guarantee how long that will be, but in my testing it’s around 30us. By using this in a spin-lock you could reduce your CPU usage without losing any meaningful amount of precision. You can see these extra optimizations implemented in "rate_limiting.c" above the Code Overview.

That’s how to do precise timing, performance measuring and loop frequency limiting in C on Windows, and this one really was a blast to make. Soon I’ll be starting my series of tutorials on how to make a small game in C.

<hr>
<h4><a id="Addendum" style="color:rgb(255,255,255);">Addendum</a></h4>

Following the release of this tutorial I was provided information regarding some undocumented code which can be used to either increase the accuracy of Sleep() or replace it with something more accurate. Thanks to Reddit users Sunius, skeeto and ack_error for providing the initial information and links.

TimeBeginPeriod allows us to request a minimum of 1ms for the timer Windows uses to wake our thread. The very old and undocumented function, NtSetTimerResolution allows us to request a timer interval with a precision of 100ns! We can <i>request</i> that interval, but as far as I have determined, modern systems with modern Windows are only able to set a minimum timer resolution of 496us - still half the minimum of timeBeginPeriod. We'd like it to be a little lower than that, so I'm requesting 100us but it'll round up to 496us unless a future version of Windows and/or hardware supports lower. NtSetTimerResolution is not exposed in any Windows headers so we have to declare it ourselves, and the linker will find it in ntdll. Here's some sample code of using this function:

<a href="NtSetTimerResolution.c" download>NtSetTimerResolution.c</a>
<code><span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;">&lt;Windows.h&gt;</span></span>
<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;">&lt;stdio.h&gt;</span></span>
<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;">&lt;mmsystem.h&gt;</span></span>
<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;">&quot;zen_timer.h&quot;</span></span>

<span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">extern</span> NTSYSAPI NTSTATUS NTAPI <span style="color:rgb(240, 141, 73); font-weight:400;">NtSetTimerResolution</span><span style="color:rgb(255, 255, 255); font-weight:400;">(ULONG DesiredResolution, BOOLEAN SetResolution, PULONG CurrentResolution)</span></span>;

<span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">int</span> WINAPI <span style="color:rgb(240, 141, 73); font-weight:400;">WinMain</span><span style="color:rgb(255, 255, 255); font-weight:400;">(HINSTANCE hInstance, HINSTANCE hPrevInstance, LPSTR lpCmdLine, <span style="color:rgb(136, 174, 206); font-weight:400;">int</span> nShowCmd)</span> </span>{
	<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">zen_timer_t</span> timer;
	<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">long</span> <span style="color:rgb(136, 174, 206); font-weight:400;">long</span> slept;
	<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">unsigned</span> <span style="color:rgb(136, 174, 206); font-weight:400;">long</span> <span style="color:rgb(136, 174, 206); font-weight:400;">long</span> avg = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>, min = <span style="color:rgb(240, 141, 73); font-weight:400;">-1</span>, max = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>;

<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">if</span> 0</span>
	<span style="color:rgb(240, 141, 73); font-weight:400;">timeBeginPeriod</span>(<span style="color:rgb(240, 141, 73); font-weight:400;">1</span>);
<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">else</span></span>
	<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">unsigned</span> <span style="color:rgb(136, 174, 206); font-weight:400;">long</span> time;
	<span style="color:rgb(240, 141, 73); font-weight:400;">NtSetTimerResolution</span>(<span style="color:rgb(240, 141, 73); font-weight:400;">1000</span>, TRUE, &amp;time);
	<span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;Timer resolution: %ldns\n&quot;</span>, time * <span style="color:rgb(240, 141, 73); font-weight:400;">100</span>);
<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">endif</span></span>

	<span style="color:rgb(240, 141, 73); font-weight:400;">ZenTimer_Init</span>();

	<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">define</span> NUM_LOOPS 10000</span>
	<span style="color:rgb(136, 174, 206); font-weight:400;">for</span>(<span style="color:rgb(136, 174, 206); font-weight:400;">int</span> i = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>; i &lt; NUM_LOOPS; ++i) {
		timer = <span style="color:rgb(240, 141, 73); font-weight:400;">ZenTimer_Start</span>();
		<span style="color:rgb(240, 141, 73); font-weight:400;">Sleep</span>(<span style="color:rgb(240, 141, 73); font-weight:400;">1</span>);
		slept = <span style="color:rgb(240, 141, 73); font-weight:400;">ZenTimer_End</span>(&amp;timer);
		avg += slept;
		<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(slept &lt; min) min = slept;
		<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(slept &gt; max) max = slept;
	}

	<span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;Avg: %llu\nMin: %llu\nMax: %llu\n&quot;</span>, avg / NUM_LOOPS, min, max);
	
	<span style="color:rgb(240, 141, 73); font-weight:400;">NtSetTimerResolution</span>(<span style="color:rgb(240, 141, 73); font-weight:400;">0</span>, FALSE, &amp;time);

	<span style="color:rgb(136, 174, 206); font-weight:400;">return</span> <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>;
}</code>

<a href="build-nt.bat" download>build-nt.bat</a>
<code>gcc -o nt.exe NtSetTimerResolution.c -lntdll -lwinmm</code>

I've hybridized the above code to measure the difference between using timeBeginPeriod and NtSetTimerResolution. This code sets the thread timer either way, then, 10,000 times, records how long a call to Sleep(1) takes. At the end we also call NtSetTimerResolution, passing FALSE as the second argument to remove our requested time, just like calling timeEndPeriod. I find that the maximum occasionally has an outlier result, but the minimum and average are consistent between runs. Here are the results from running the above code:

timeBeginPeriod:
Avg: 1887
Min: 1010
Max: 2689

NtSetTimerResolution:
Avg: 1249
Min: 1009
Max: 1929

As you can see from my results, and from running the code yourself, NtSetTimerResolution brings the average Sleep time much closer to the requested 1ms, and brings the maximum down from more than 2 and a half milliseconds to less than 2ms! These are great improvements and well worth the small cost of declaring the function ourselves and linking to ntdll, in my opinion. There are yet question marks about this function though - does it have an impact on other programs? What about overall system performance and battery life? For now it seems a safe bet to assume that it functions similarly to timeBeginPeriod within the Windows black box, but I invite anyone with reliable information about this function to share it.

There is a function which can entirely replace Sleep(): WaitForSingleObject(). This requires creating a "Waitable Timer" object with CreateWaitableTimerEx(), specifically using the undocumented option, CREATE_WAITABLE_TIMER_HIGH_RESOLUTION. The biggest caveat with this combo is that CREATE_WAITABLE_TIMER_HIGH_RESOLUTION is, as far as I have been able to discover, not available when using the GCC/MinGW toolchain. I'm not sure of the details of how MinGW bundles/implements the Windows libraries, so if anyone has details on why this feature is available from Microsoft but not MinGW I'm all ears. In any case, here's the code using Waitable Timer objects:

<a href="waitable-timer.c" download>waitable-timer.c</a>
<code><span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;">&lt;Windows.h&gt;</span></span>
<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;">&quot;zen_timer.h&quot;</span></span>
<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;">&lt;stdio.h&gt;</span></span>

<span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">int</span> WINAPI <span style="color:rgb(240, 141, 73); font-weight:400;">WinMain</span><span style="color:rgb(255, 255, 255); font-weight:400;">(HINSTANCE hInstance, HINSTANCE hPrevInstance, LPSTR lpCmdLine, <span style="color:rgb(136, 174, 206); font-weight:400;">int</span> nShowCmd)</span> </span>{
	<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(!<span style="color:rgb(240, 141, 73); font-weight:400;">AttachConsole</span>(ATTACH_PARENT_PROCESS)) {
		<span style="color:rgb(136, 174, 206); font-weight:400;">return</span> <span style="color:rgb(240, 141, 73); font-weight:400;">-1</span>;
	}
	<span style="color:rgb(240, 141, 73); font-weight:400;">freopen</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;CONOUT$&quot;</span>, <span style="color:rgb(181, 189, 104); font-weight:400;">&quot;w&quot;</span>, stdout);
	<span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;\n&quot;</span>);

	<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">zen_timer_t</span> timer;
	<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">long</span> <span style="color:rgb(136, 174, 206); font-weight:400;">long</span> slept, num_overslept;
	<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">unsigned</span> <span style="color:rgb(136, 174, 206); font-weight:400;">long</span> <span style="color:rgb(136, 174, 206); font-weight:400;">long</span> avg = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>, min = <span style="color:rgb(240, 141, 73); font-weight:400;">-1</span>, max = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>;
	<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">unsigned</span> <span style="color:rgb(136, 174, 206); font-weight:400;">long</span> time;

	<span style="color:rgb(240, 141, 73); font-weight:400;">ZenTimer_Init</span>();

	HANDLE waitable_timer = <a href="https://docs.microsoft.com/en-us/windows/win32/api/synchapi/nf-synchapi-createwaitabletimerexw" target="_blank">CreateWaitableTimerEx</a>(<span style="color:rgb(240, 141, 73); font-weight:400;">NULL</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">NULL</span>, CREATE_WAITABLE_TIMER_HIGH_RESOLUTION, TIMER_ALL_ACCESS);
	LARGE_INTEGER wait_time;
	wait_time.QuadPart = <span style="color:rgb(240, 141, 73); font-weight:400;">-5000</span>;

<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">define</span> NUM_LOOPS 10000</span>
	<span style="color:rgb(136, 174, 206); font-weight:400;">for</span>(<span style="color:rgb(136, 174, 206); font-weight:400;">int</span> i = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>; i &lt; NUM_LOOPS; ++i) {
		timer = <span style="color:rgb(240, 141, 73); font-weight:400;">ZenTimer_Start</span>();
		<a href="https://docs.microsoft.com/en-us/windows/win32/api/synchapi/nf-synchapi-setwaitabletimer" target="_blank">SetWaitableTimer</a>(waitable_timer, &amp;wait_time, <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>);
		<a href="https://docs.microsoft.com/en-us/windows/win32/api/synchapi/nf-synchapi-waitforsingleobject" target="_blank">WaitForSingleObject</a>(waitable_timer, INFINITE);
		slept = <span style="color:rgb(240, 141, 73); font-weight:400;">ZenTimer_End</span>(&amp;timer);
		avg += slept;
		<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(slept &lt; min) min = slept;
		<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(slept &gt; max) max = slept;
	}

	<span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;Avg: %llu\nMin: %llu\nMax: %llu\n&quot;</span>, avg / NUM_LOOPS, min, max);

	<span style="color:rgb(136, 174, 206); font-weight:400;">return</span> <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>;
}</code>

<a href="build-wt.bat" download>build-wt.bat</a>
<code>cl waitable-timer.c</code>

We create the waitable timer object, passing CREATE_WAITABLE_TIMER_HIGH_RESOLUTION. We store the amount of time we want to wait in hundreds of nanoseconds. A positive value is absolute time and negative is relative time, so -5000 represents 500us after calling SetWaitableTimer(). Then we call WaitForSingleObject(), specifying an infinite timeout other than the timer's timeout itself. You might want to choose something smaller than infinity when using this function, just in case of an error. That "AttachConsole [...] freopen" stuff just enables the program to output to a console again, since "Window" applications under cl don't associate with a console window by default.
Running this code, the following statistics are normal:
Avg: 701
Min: 504
Max: 1405

We were able to request a sub-millisecond wait time, and the minimum and average are actually pretty close to the requested time! The max is half a millisecond better than before as well. It also suffers from the occasional outlier, as with Sleep(). Note that requesting less than 500us, in my testing, still results in the same values as above, so that seems to be the minimum. This may be related to the minumum supported by NtSetTimerResolution. Also note that neither timeBeginPeriod nor NtSetTimerResolution need to be called when using waitable timer objects.

That about covers this undocumented functionality. If you have further information about this stuff, with reliable sources, please send me an e-mail with the link below. Cheers.

<hr>
If you've got questions about any of the code feel free to e-mail me or comment on the <a href="https://www.youtube.com/watch?v=LFkLLSOXQIc" target="_blank">youtube video</a>. I'll try to answer them, or someone else might come along and help you out. If you've got any extra tips about how this code can be better or just more useful info about the code, let me know so I can update the tutorial.

Thanks to Evgeny Borodin for e-mailing me to correct some errors in this tutorial.
Thanks to Froggie717 for criticisms and correcting errors in this tutorial.

Cheers.

</article>

</body>

<?php include "/blog/blogbottom.html" ?>

<?php include "footer_common.html" ?>

</html>
