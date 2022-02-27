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

<h4>Sound on Windows with WinMM in C</h4>

This tutorial is also available as a <a href="https://www.youtube.com/watch?v=z-zneNKF_u4" target="_blank">video.</a>

In this tutorial I’ll show you how to play realtime audio with Windows' native Multimedia library in C.

Note: I’m compiling this code with GCC and not Microsoft’s C compiler. To compile with cl you’ll need to either add Window opening code to open a window and handle input that way, or connect the program to a console window which GCC does automatically. You'll also need to remove the PRINT_ERROR macro.
Note: Click any of the <a>hyperlinked words</a> to visit the MSDN documentation page for them.

<a href="main.c" download>main.c</a>
<code><span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">define</span> WIN32_LEAN_AND_MEAN</span>
<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;">&lt;windows.h&gt;</span></span>
<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;">&lt;mmsystem.h&gt;</span></span>

<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;">&lt;conio.h&gt;</span></span>
<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;">&lt;stdint.h&gt;</span></span>
<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;">&lt;stdbool.h&gt;</span></span>
<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;">&lt;stdio.h&gt;</span></span>
<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;">&lt;math.h&gt;</span></span>
<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">define</span> TWOPI (M_PI + M_PI)</span>

<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">define</span> PRINT_ERROR(a, args...) printf(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;ERROR %s() %s Line %d: &quot;</span> a, __FUNCTION__, __FILE__, __LINE__, ##args);</span>

HWAVEOUT wave_out;
<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">define</span> SAMPLING_RATE 44100</span>
<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">define</span> CHUNK_SIZE 2000</span>
<a href="https://docs.microsoft.com/en-us/windows/win32/api/mmeapi/ns-mmeapi-wavehdr" target="_blank">WAVEHDR</a> header[<span style="color:rgb(240, 141, 73); font-weight:400;">2</span>] = {<span style="color:rgb(240, 141, 73); font-weight:400;">0</span>};
<span style="color:rgb(136, 174, 206); font-weight:400;">int16_t</span> chunks[<span style="color:rgb(240, 141, 73); font-weight:400;">2</span>][CHUNK_SIZE];
<span style="color:rgb(136, 174, 206); font-weight:400;">bool</span> chunk_swap = <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
<span style="color:rgb(136, 174, 206); font-weight:400;">float</span> frequency = <span style="color:rgb(240, 141, 73); font-weight:400;">400</span>;
<span style="color:rgb(136, 174, 206); font-weight:400;">float</span> wave_position = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>;
<span style="color:rgb(136, 174, 206); font-weight:400;">float</span> wave_step;

<span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">void</span> CALLBACK <a href="https://docs.microsoft.com/en-us/previous-versions/dd743869(v=vs.85)" target="_blank">WaveOutProc</a><span style="color:rgb(255, 255, 255); font-weight:400;">(HWAVEOUT, UINT, DWORD_PTR, DWORD_PTR, DWORD_PTR)</span></span>;

<span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">int</span> WINAPI <span style="color:rgb(240, 141, 73); font-weight:400;">WinMain</span><span style="color:rgb(255, 255, 255); font-weight:400;">(HINSTANCE hInstance, HINSTANCE hPrevInstance, LPSTR lpCmdLine, <span style="color:rgb(136, 174, 206); font-weight:400;">int</span> nShowCmd)</span> </span>{
	{
		<a href="https://docs.microsoft.com/en-us/windows/win32/api/mmeapi/ns-mmeapi-waveformatex" target="_blank">WAVEFORMATEX</a> format = {
			.wFormatTag = WAVE_FORMAT_PCM,
			.nChannels = <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>,
			.nSamplesPerSec = SAMPLING_RATE,
			.wBitsPerSample = <span style="color:rgb(240, 141, 73); font-weight:400;">16</span>,
			.cbSize = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>,
		};
		format.nBlockAlign = format.nChannels * format.wBitsPerSample / <span style="color:rgb(240, 141, 73); font-weight:400;">8</span>;
		format.nAvgBytesPerSec = format.nSamplesPerSec * format.nBlockAlign;
		<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(<a href="https://docs.microsoft.com/en-us/windows/win32/api/mmeapi/nf-mmeapi-waveoutopen" target="_blank">waveOutOpen</a>(&amp;wave_out, WAVE_MAPPER, &amp;format, (DWORD_PTR)WaveOutProc, (DWORD_PTR)<span style="color:rgb(240, 141, 73); font-weight:400;">NULL</span>, CALLBACK_FUNCTION) != MMSYSERR_NOERROR) {
			<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;waveOutOpen failed\n&quot;</span>);
			<span style="color:rgb(136, 174, 206); font-weight:400;">return</span> <span style="color:rgb(240, 141, 73); font-weight:400;">-1</span>;
		}
	}

	<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(<a href="https://docs.microsoft.com/en-us/windows/win32/api/mmeapi/nf-mmeapi-waveoutsetvolume" target="_blank">waveOutSetVolume</a>(wave_out, <span style="color:rgb(240, 141, 73); font-weight:400;">0xFFFF</span>) != MMSYSERR_NOERROR) {
		<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;waveOutGetVolume failed\n&quot;</span>);
		<span style="color:rgb(136, 174, 206); font-weight:400;">return</span> <span style="color:rgb(240, 141, 73); font-weight:400;">-1</span>;
	}

	wave_step = TWOPI / ((<span style="color:rgb(136, 174, 206); font-weight:400;">float</span>)SAMPLING_RATE / frequency);

	<span style="color:rgb(136, 174, 206); font-weight:400;">for</span>(<span style="color:rgb(136, 174, 206); font-weight:400;">int</span> i = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>; i &lt; <span style="color:rgb(240, 141, 73); font-weight:400;">2</span>; ++i) {
		<span style="color:rgb(136, 174, 206); font-weight:400;">for</span>(<span style="color:rgb(136, 174, 206); font-weight:400;">int</span> j = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>; j &lt; CHUNK_SIZE; ++j) {
			chunks[i][j] = <span style="color:rgb(240, 141, 73); font-weight:400;">sin</span>(wave_position) * <span style="color:rgb(240, 141, 73); font-weight:400;">32767</span>;
			wave_position += wave_step;
		}
		header[i].lpData = (CHAR*)chunks[i];
		header[i].dwBufferLength = CHUNK_SIZE * <span style="color:rgb(240, 141, 73); font-weight:400;">2</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(<a href="https://docs.microsoft.com/en-us/windows/win32/api/mmeapi/nf-mmeapi-waveoutprepareheader" target="_blank">waveOutPrepareHeader</a>(wave_out, &amp;header[i], <span style="color:rgb(240, 141, 73); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">sizeof</span></span>(header[i])) != MMSYSERR_NOERROR) {
			<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;waveOutPrepareHeader[%d] failed\n&quot;</span>, i);
			<span style="color:rgb(136, 174, 206); font-weight:400;">return</span> <span style="color:rgb(240, 141, 73); font-weight:400;">-1</span>;
		}
		<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(<a href="https://docs.microsoft.com/en-us/windows/win32/api/mmeapi/nf-mmeapi-waveoutwrite" target="_blank">waveOutWrite</a>(wave_out, &amp;header[i], <span style="color:rgb(240, 141, 73); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">sizeof</span></span>(header[i])) != MMSYSERR_NOERROR) {
			<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;waveOutWrite[%d] failed\n&quot;</span>, i);
			<span style="color:rgb(136, 174, 206); font-weight:400;">return</span> <span style="color:rgb(240, 141, 73); font-weight:400;">-1</span>;
		}
	}

	<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">bool</span> quit = <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
	<span style="color:rgb(136, 174, 206); font-weight:400;">while</span>(!quit) {
		<span style="color:rgb(240, 141, 73); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">switch</span></span>(_getche()) {
			<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> <span style="color:rgb(240, 141, 73); font-weight:400;">72</span>: {
				frequency += <span style="color:rgb(240, 141, 73); font-weight:400;">50</span>;
				wave_step = TWOPI / ((<span style="color:rgb(136, 174, 206); font-weight:400;">float</span>)SAMPLING_RATE / frequency);
				<span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;Frequency: %f\n&quot;</span>, frequency);
			} <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;
			<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> <span style="color:rgb(240, 141, 73); font-weight:400;">80</span>: {
				frequency -= <span style="color:rgb(240, 141, 73); font-weight:400;">50</span>;
				wave_step = TWOPI / ((<span style="color:rgb(136, 174, 206); font-weight:400;">float</span>)SAMPLING_RATE / frequency);
				<span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;Frequency: %f\n&quot;</span>, frequency);
			} <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;
			<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> <span style="color:rgb(240, 141, 73); font-weight:400;">27</span>: {
				quit = <span style="color:rgb(240, 141, 73); font-weight:400;">true</span>;
			} <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;
		}
	}

	<span style="color:rgb(136, 174, 206); font-weight:400;">return</span> <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>;
}

<span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">void</span> CALLBACK <a href="https://docs.microsoft.com/en-us/previous-versions/dd743869(v=vs.85)" target="_blank">WaveOutProc</a><span style="color:rgb(255, 255, 255); font-weight:400;">(HWAVEOUT wave_out_handle, UINT message, DWORD_PTR instance, DWORD_PTR param1, DWORD_PTR param2)</span> </span>{
	<span style="color:rgb(240, 141, 73); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">switch</span></span>(message) {
		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> <a href="https://docs.microsoft.com/en-us/windows/win32/multimedia/wom-close" target="_blank">WOM_CLOSE</a>: <span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;WOM_CLOSE\n&quot;</span>); <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> <a href="https://docs.microsoft.com/en-us/windows/win32/multimedia/wom-open" target="_blank">WOM_OPEN</a>:  <span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;WOM_OPEN\n&quot;</span>);  <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> <a href="https://docs.microsoft.com/en-us/windows/win32/multimedia/wom-done" target="_blank">WOM_DONE</a>:{ <span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;WOM_DONE\n&quot;</span>);
			<span style="color:rgb(136, 174, 206); font-weight:400;">for</span>(<span style="color:rgb(136, 174, 206); font-weight:400;">int</span> i = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>; i &lt; CHUNK_SIZE; ++i) {
				chunks[chunk_swap][i] = <span style="color:rgb(240, 141, 73); font-weight:400;">sin</span>(wave_position) * <span style="color:rgb(240, 141, 73); font-weight:400;">32767</span>;
				wave_position += wave_step;
			}
			<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(<a href="https://docs.microsoft.com/en-us/windows/win32/api/mmeapi/nf-mmeapi-waveoutwrite" target="_blank">waveOutWrite</a>(wave_out, &amp;header[chunk_swap], <span style="color:rgb(240, 141, 73); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">sizeof</span></span>(header[chunk_swap])) != MMSYSERR_NOERROR) {
				<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;waveOutWrite failed\n&quot;</span>);
			}
			chunk_swap = !chunk_swap;
		} <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;
	}
}</code>

<a href="build.bat" download>build.bat</a>
<code>gcc main.c -lwinmm</code>

<hr>
<h4><a id="Code Walkthrough" style="color:rgb(255,255,255);">Code Walkthrough</a></h4>

<code><span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;">&lt;mmsystem.h&gt;</span></span></code>

<code>HWAVEOUT wave_out;
<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">define</span> SAMPLING_RATE 44100</span>
<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">define</span> CHUNK_SIZE 2000</span>
<a href="https://docs.microsoft.com/en-us/windows/win32/api/mmeapi/ns-mmeapi-wavehdr" target="_blank">WAVEHDR</a> header[<span style="color:rgb(240, 141, 73); font-weight:400;">2</span>] = {<span style="color:rgb(240, 141, 73); font-weight:400;">0</span>};
<span style="color:rgb(136, 174, 206); font-weight:400;">int16_t</span> chunks[<span style="color:rgb(240, 141, 73); font-weight:400;">2</span>][CHUNK_SIZE];
<span style="color:rgb(136, 174, 206); font-weight:400;">bool</span> chunk_swap = <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
<span style="color:rgb(136, 174, 206); font-weight:400;">float</span> frequency = <span style="color:rgb(240, 141, 73); font-weight:400;">400</span>;
<span style="color:rgb(136, 174, 206); font-weight:400;">float</span> wave_position = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>;
<span style="color:rgb(136, 174, 206); font-weight:400;">float</span> wave_step;</code>
Here are all the variables we need to use WinMM. I've #defined a couple of things for easy modification. frequency, wave_position and wave_step are all used to generate samples of a sine wave that is continuous across the two sound chunks.

<code><a href="https://docs.microsoft.com/en-us/windows/win32/api/mmeapi/ns-mmeapi-waveformatex" target="_blank">WAVEFORMATEX</a> format = {
			.wFormatTag = WAVE_FORMAT_PCM,
			.nChannels = <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>,
			.nSamplesPerSec = SAMPLING_RATE,
			.wBitsPerSample = <span style="color:rgb(240, 141, 73); font-weight:400;">16</span>,
			.cbSize = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>,
		};
		format.nBlockAlign = format.nChannels * format.wBitsPerSample / <span style="color:rgb(240, 141, 73); font-weight:400;">8</span>;
		format.nAvgBytesPerSec = format.nSamplesPerSec * format.nBlockAlign;</code>
First we need to define our sound wave format with the WAVEFORMATEX structure. PCM stands for Pulse Code Modulation and means that each sample represents how far the speaker should be extended at each step. It’s the simplest, most direct and most common sound format. For now I’m using one sound channel. More channels would generally represent more speakers, so to play different sound data from two different speakers you’d use two channels. That would be standard stereo sound.
The sample rate is how often we’ll update the speaker position per second; 44100Hz is the standard sampling rate for CD audio. We’re using 16 bits per sample which is also standard. cbSize lets us tell the structure how much extra space we’re using for extended format information; we’re using the standard PCM format so we don’t need any.
We calculate nBlockAlign, which is how many bytes each sample requires, and the average bytes per second has a self-explanatory name.

<code>		<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(<a href="https://docs.microsoft.com/en-us/windows/win32/api/mmeapi/nf-mmeapi-waveoutopen" target="_blank">waveOutOpen</a>(&amp;wave_out, WAVE_MAPPER, &amp;format, (DWORD_PTR)WaveOutProc, (DWORD_PTR)<span style="color:rgb(240, 141, 73); font-weight:400;">NULL</span>, CALLBACK_FUNCTION) != MMSYSERR_NOERROR) {
			<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;waveOutOpen failed\n&quot;</span>);
			<span style="color:rgb(136, 174, 206); font-weight:400;">return</span> <span style="color:rgb(240, 141, 73); font-weight:400;">-1</span>;
		}</code>
We pass waveOutOpen a pointer to our waveout handle to fill. The second argument is the ID of the sound device we want to use; WAVE_MAPPER selects the default device. We pass our wave format, a pointer to our waveout callback function, and a flag to tell it that we’re using a callback function. Using a callback function means that whenever something happens on the waveout device, our program will be interrupted by a call to the specified function to handle whatever happened, such as finishing playing a sound data chunk. There are 3 other options but a callback function is simple and effective. In a performance-intensive program, a separate thread can be used to handle audio without interrupting the program.

<code>	<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(<a href="https://docs.microsoft.com/en-us/windows/win32/api/mmeapi/nf-mmeapi-waveoutsetvolume" target="_blank">waveOutSetVolume</a>(wave_out, <span style="color:rgb(240, 141, 73); font-weight:400;">0xFFFF</span>) != MMSYSERR_NOERROR) {
		<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;waveOutGetVolume failed\n&quot;</span>);
		<span style="color:rgb(136, 174, 206); font-weight:400;">return</span> <span style="color:rgb(240, 141, 73); font-weight:400;">-1</span>;
	}</code>
Setting the volume here sets the internal wave device volume. We want our audio to be sent along to the speaker driver without decreasing, then the user can adjust their speaker volume either through Windows audio controls or with their physical speaker controls.

<code>	wave_step = TWOPI / ((<span style="color:rgb(136, 174, 206); font-weight:400;">float</span>)SAMPLING_RATE / frequency);

	<span style="color:rgb(136, 174, 206); font-weight:400;">for</span>(<span style="color:rgb(136, 174, 206); font-weight:400;">int</span> i = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>; i &lt; <span style="color:rgb(240, 141, 73); font-weight:400;">2</span>; ++i) {
		<span style="color:rgb(136, 174, 206); font-weight:400;">for</span>(<span style="color:rgb(136, 174, 206); font-weight:400;">int</span> j = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>; j &lt; CHUNK_SIZE; ++j) {
			chunks[i][j] = <span style="color:rgb(240, 141, 73); font-weight:400;">sin</span>(wave_position) * <span style="color:rgb(240, 141, 73); font-weight:400;">32767</span>;
			wave_position += wave_step;
		}</code>
We have to send audio to the wave device in chunks, so we create two chunks holding 2000 samples each. I’m using this wave_position, wave_step code to generate samples of a sine wave which will be continuous across the chunks.

<code>		header[i].lpData = (CHAR*)chunks[i];
		header[i].dwBufferLength = CHUNK_SIZE * <span style="color:rgb(240, 141, 73); font-weight:400;">2</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(<a href="https://docs.microsoft.com/en-us/windows/win32/api/mmeapi/nf-mmeapi-waveoutprepareheader" target="_blank">waveOutPrepareHeader</a>(wave_out, &amp;header[i], <span style="color:rgb(240, 141, 73); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">sizeof</span></span>(header[i])) != MMSYSERR_NOERROR) {
			<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;waveOutPrepareHeader[%d] failed\n&quot;</span>, i);
			<span style="color:rgb(136, 174, 206); font-weight:400;">return</span> <span style="color:rgb(240, 141, 73); font-weight:400;">-1</span>;
		}
		<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(<a href="https://docs.microsoft.com/en-us/windows/win32/api/mmeapi/nf-mmeapi-waveoutwrite" target="_blank">waveOutWrite</a>(wave_out, &amp;header[i], <span style="color:rgb(240, 141, 73); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">sizeof</span></span>(header[i])) != MMSYSERR_NOERROR) {
			<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;waveOutWrite[%d] failed\n&quot;</span>, i);
			<span style="color:rgb(136, 174, 206); font-weight:400;">return</span> <span style="color:rgb(240, 141, 73); font-weight:400;">-1</span>;
		}</code>
We use these wave headers to hold a pointer to the chunk data and length of the chunk, in bytes. Since each sample is two bytes we multiply the chunk size by two. We give the header to WinMM to be prepared for writing to the wave device using waveOutPrepareHeader, then pass the header to waveOutWrite. The first sound data sent is immediately played. We loop through the same code again, setting up and sending the second sound chunk.

<code><span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">void</span> CALLBACK <a href="https://docs.microsoft.com/en-us/previous-versions/dd743869(v=vs.85)" target="_blank">WaveOutProc</a><span style="color:rgb(255, 255, 255); font-weight:400;">(HWAVEOUT wave_out_handle, UINT message, DWORD_PTR instance, DWORD_PTR param1, DWORD_PTR param2)</span> </span>{
	<span style="color:rgb(240, 141, 73); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">switch</span></span>(message) {
		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> <a href="https://docs.microsoft.com/en-us/windows/win32/multimedia/wom-close" target="_blank">WOM_CLOSE</a>: <span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;WOM_CLOSE\n&quot;</span>); <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> <a href="https://docs.microsoft.com/en-us/windows/win32/multimedia/wom-open" target="_blank">WOM_OPEN</a>:  <span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;WOM_OPEN\n&quot;</span>);  <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> <a href="https://docs.microsoft.com/en-us/windows/win32/multimedia/wom-done" target="_blank">WOM_DONE</a>:{ <span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;WOM_DONE\n&quot;</span>);
			<span style="color:rgb(136, 174, 206); font-weight:400;">for</span>(<span style="color:rgb(136, 174, 206); font-weight:400;">int</span> i = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>; i &lt; CHUNK_SIZE; ++i) {
				chunks[chunk_swap][i] = <span style="color:rgb(240, 141, 73); font-weight:400;">sin</span>(wave_position) * <span style="color:rgb(240, 141, 73); font-weight:400;">32767</span>;
				wave_position += wave_step;
			}
			<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(<a href="https://docs.microsoft.com/en-us/windows/win32/api/mmeapi/nf-mmeapi-waveoutwrite" target="_blank">waveOutWrite</a>(wave_out, &amp;header[chunk_swap], <span style="color:rgb(240, 141, 73); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">sizeof</span></span>(header[chunk_swap])) != MMSYSERR_NOERROR) {
				<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;waveOutWrite failed\n&quot;</span>);
			}
			chunk_swap = !chunk_swap;
		} <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;
	}
}</code>
Whenever a sound chunk has finished playing, WinMM will call our callback function with a WOM_DONE event. In that event we fill the completed chunk with new sound data and write it back to the wave device. This will continuously add a new chunk to the wave device whenever one finishes.

<code>	<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">bool</span> quit = <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
	<span style="color:rgb(136, 174, 206); font-weight:400;">while</span>(!quit) {
		<span style="color:rgb(240, 141, 73); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">switch</span></span>(_getche()) {
			<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> <span style="color:rgb(240, 141, 73); font-weight:400;">72</span>: {
				frequency += <span style="color:rgb(240, 141, 73); font-weight:400;">50</span>;
				wave_step = TWOPI / ((<span style="color:rgb(136, 174, 206); font-weight:400;">float</span>)SAMPLING_RATE / frequency);
				<span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;Frequency: %f\n&quot;</span>, frequency);
			} <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;
			<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> <span style="color:rgb(240, 141, 73); font-weight:400;">80</span>: {
				frequency -= <span style="color:rgb(240, 141, 73); font-weight:400;">50</span>;
				wave_step = TWOPI / ((<span style="color:rgb(136, 174, 206); font-weight:400;">float</span>)SAMPLING_RATE / frequency);
				<span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;Frequency: %f\n&quot;</span>, frequency);
			} <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;
			<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> <span style="color:rgb(240, 141, 73); font-weight:400;">27</span>: {
				quit = <span style="color:rgb(240, 141, 73); font-weight:400;">true</span>;
			} <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;
		}
	}</code>
In our main program loop we use _getche() to retrieve any button presses in the console window, and respond to the up and down arrow keys by adjusting the wave frequency. Escape causes the program to quit. This adjustable sine wave is just a simple way to demonstrate that the sound is working, and to test the latency. In my opinion the latency is low enough that it feels as if the sound changes immediately upon pressing the arrow key with these settings.

Each chunk is 2000 samples which is about 45 milliseconds of audio in this format. That means our latency between something happening in our program and that being reflected in audio playback is 45 to 90ms, depending on whether that sound is put at the beginning or near the end of the new chunk. The chunks have to be big enough that WinMM is able to process and queue the new chunk of audio before it finishes playing the last one, or else we’ll hear a “pop” between each chunk as it runs out of data. This also depends on the performance of the user’s computer. I've found that 2000 samples is a safe amount on my low-end GPD Pocket2 laptop, regardless of sample rate.
If you want lower latency you could decrease the chunk size, but you may get the aforementioned “pop” issue. You could also increase the sample frequency, for example to 96000Hz so that 2000 samples is only about 20ms. I’ve found that my current settings are a good enough baseline for good quality audio with unnoticeable latency but I recommend tuning these values depending on your application.

As an aside, this is reminiscent of a batch script I wrote in my high school computer science class, which played an adjustable frequency sine wave noise. I adjusted the frequency high enough that all my classmates could hear it but my teacher couldn’t. All the students were complaining, and as you’ve probably noticed it’s very hard to identify the direction a high frequency noise is coming from, so the teacher thought everyone was just fooling around looking for an excuse not to do their work. I never got in trouble for it either, unlike my friend who made a program that deletes all your user files, called it "DO NOT RUN THIS.exe" with a nuclear symbol and copied it to everyone's desktops over the network. Good times.

Anyway, that’s how to play realtime audio with Windows’ native Multimedia library in C. In my next tutorials I’ll show you how to load sound files, images, and do precise timing in C.

<hr>
If you've got questions about any of the code feel free to e-mail me or comment on the <a href="https://www.youtube.com/watch?v=z-zneNKF_u4" target="_blank">youtube video</a>. I'll try to answer them, or someone else might come along and help you out. If you've got any extra tips about how this code can be better or just more useful info about the code, let me know so I can update the tutorial.

Thanks to Froggie717 for criticisms and correcting errors in this tutorial.

Cheers.

</article>

</body>

<?php include "/blog/blogbottom.html" ?>

<?php include "footer_common.html" ?>

</html>
