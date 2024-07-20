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

<h4>Load WAV Files with the C Standard Library</h4>

This tutorial is also available as a <a href="https://www.youtube.com/watch?v=Q4XdG92abWA" target="_blank">video.</a>

In this tutorial I’ll show you how to load and play single channel, 44100Hz, 16-bit .wav files with the C standard library. This code can easily be adapted to load and play multi-channel .wav files with other frequencies and bit depths.

For convenience, here's a single channel, 44100Hz, 16-bit .wav file you can use to test the code: <a href="cheers.wav" download>cheers.wav</a>

Note: I'm doing this in a Win32 program, linking to WinMM and using that to play the wav sound samples, but no extra linking is required for the .wav loading code itself. I've dimmed the code not directly relevant to the tutorial.
Note: I’m compiling this code with GCC.
Note: Click any of the <a>hyperlinked words</a> to visit the documentation page for them.

<a href="main.c" download>main.c</a>
<code><span class="fadecode">#include &ltstdlib.h&gt
#include &ltstdint.h&gt
#include &ltstdbool.h&gt
<span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/stdio_h.htm" target="_blank">&lt;stdio.h&gt;</a></span></span>
#define WIN32_LEAN_AND_MEAN
#include &ltwindows.h&gt
#include &ltmmsystem.h&gt

#define PRINT_ERROR(a, args...) printf("ERROR %s() %s Line %d: " a "\n", __FUNCTION__, __FILE__, __LINE__, ##args);

#if RAND_MAX == 32767
#define rand32() ((rand()%lt%lt16) + (rand()%lt%lt1) + (rand()&1))
#else
#define rand32() rand()
#endif</span>

<span style="color:rgb(136, 174, 206); font-weight:400;">typedef</span> <span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">struct</span> {</span>
	<span style="color:rgb(136, 174, 206); font-weight:400;">uint32_t</span> samples;
	<span style="color:rgb(136, 174, 206); font-weight:400;">int16_t</span> *data;
} <span style="color:rgb(136, 174, 206); font-weight:400;">sound_t</span>;

<span style="color:rgb(136, 174, 206); font-weight:400;">sound_t</span> hello;

<span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">bool</span> <span style="color:rgb(240, 141, 73); font-weight:400;">LoadWav</span><span style="color:rgb(255, 255, 255); font-weight:400;">(<span style="color:rgb(136, 174, 206); font-weight:400;">const</span> <span style="color:rgb(136, 174, 206); font-weight:400;">char</span> *filename, <span style="color:rgb(136, 174, 206); font-weight:400;">sound_t</span> *sound)</span></span>;

<span class="fadecode">#define SAMPLING_RATE 44100
#define CHUNK_SIZE 2000
HWAVEOUT wave_out;
WAVEHDR header[2] = {0};
int16_t chunks[2][CHUNK_SIZE] = {0};
bool chunk_swap = false;
int16_t *to;
bool quit = false;

void CALLBACK WaveOutProc(HWAVEOUT wave_out_handle, UINT message, DWORD_PTR instance, DWORD_PTR param1, DWORD_PTR param2);

int WINAPI WinMain(HINSTANCE hInstance, HINSTANCE hPrevInstance, LPSTR lpCmdLine, int nShowCmd) {
	{
		WAVEFORMATEX format = {
			.wFormatTag = WAVE_FORMAT_PCM,
			.nChannels = 1,
			.nSamplesPerSec = SAMPLING_RATE,
			.wBitsPerSample = 16,
			.cbSize = 0,
		};
		format.nBlockAlign = format.nChannels * format.wBitsPerSample / 8;
		format.nAvgBytesPerSec = format.nSamplesPerSec * format.nBlockAlign;
		if(waveOutOpen(&wave_out, WAVE_MAPPER, &format, (DWORD_PTR)WaveOutProc, (DWORD_PTR)NULL, CALLBACK_FUNCTION) != MMSYSERR_NOERROR) {
			PRINT_ERROR("waveOutOpen failed");
			return -1;
		}
	}
	
	if(waveOutSetVolume(wave_out, 0xFFFFFFFF) != MMSYSERR_NOERROR) {
		PRINT_ERROR("waveOutSetVolume failed");
		return -1;
	}
	
	for(int i = 0; i &lt 2; ++i) {
		header[i].lpData = (CHAR*)chunks[i];
		header[i].dwBufferLength = CHUNK_SIZE * 2;
		if(waveOutPrepareHeader(wave_out, &header[i], sizeof(header[i])) != MMSYSERR_NOERROR) {
			PRINT_ERROR("waveOutPrepareHeader failed");
			return -1;
		}
		if(waveOutWrite(wave_out, &header[i], sizeof(header[i])) != MMSYSERR_NOERROR) {
			PRINT_ERROR("waveOutWrite[%d] failed", i);
			return -1;
		}
	}</span>

	<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(!<span style="color:rgb(240, 141, 73); font-weight:400;">LoadWav</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;cheers.wav&quot;</span>, &amp;hello)) {
		<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;Failed to load cheers.wav&quot;</span>);
		<span style="color:rgb(136, 174, 206); font-weight:400;">return</span> <span style="color:rgb(240, 141, 73); font-weight:400;">-1</span>;
	}

	<span style="color:rgb(136, 174, 206); font-weight:400;">while</span>(!quit);

	<span style="color:rgb(136, 174, 206); font-weight:400;">return</span> <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>;
}

<span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">void</span> CALLBACK <span style="color:rgb(240, 141, 73); font-weight:400;">WaveOutProc</span><span style="color:rgb(255, 255, 255); font-weight:400;">(HWAVEOUT wave_out_handle, UINT message, DWORD_PTR instance, DWORD_PTR param1, DWORD_PTR param2)</span> </span>{
	<span style="color:rgb(136, 174, 206); font-weight:400;">static</span> <span style="color:rgb(136, 174, 206); font-weight:400;">uint32_t</span> sound_position = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>;

	<span style="color:rgb(240, 141, 73); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">switch</span></span>(message) {
		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> WOM_CLOSE: { <span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;WOM_CLOSE\n&quot;</span>); } <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> WOM_OPEN:  { <span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;WOM_OPEN\n&quot;</span>); } <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> WOM_DONE:  {
			<span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;WOM_DONE\n&quot;</span>);
			to = chunks[chunk_swap];
			<span style="color:rgb(136, 174, 206); font-weight:400;">for</span>(<span style="color:rgb(136, 174, 206); font-weight:400;">int</span> j = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>; j &lt; CHUNK_SIZE; ++j) {
				<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(sound_position &lt; hello.samples) {
					*(to++) = hello.data[sound_position++];
				}
				<span style="color:rgb(136, 174, 206); font-weight:400;">else</span> {
					quit = <span style="color:rgb(240, 141, 73); font-weight:400;">true</span>;
					*(to++) = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>;
				}
			}
			<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(<span style="color:rgb(240, 141, 73); font-weight:400;">waveOutWrite</span>(wave_out, &amp;header[chunk_swap], <span style="color:rgb(240, 141, 73); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">sizeof</span></span>(header[chunk_swap])) != MMSYSERR_NOERROR) {
				<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;waveOutWrite failed\n&quot;</span>);
			}
			chunk_swap = !chunk_swap;
		} <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;
	}
}

<span style="color:rgb(153, 153, 153); font-weight:400;">// Loads ONLY 16-bit 1-channel PCM .WAV files. Allocates sound-&gtdata and fills with the pcm data. Fills sound-&gt;samples with the number of ELEMENTS in sound-&gt;data. EG for 2-bytes per sample single channel, sound-&gt;samples = HALF of the number of bytes in sound-&gt;data.</span>
<span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">bool</span> <span style="color:rgb(240, 141, 73); font-weight:400;">LoadWav</span><span style="color:rgb(255, 255, 255); font-weight:400;">(<span style="color:rgb(136, 174, 206); font-weight:400;">const</span> <span style="color:rgb(136, 174, 206); font-weight:400;">char</span> *filename, <span style="color:rgb(136, 174, 206); font-weight:400;">sound_t</span> *sound)</span> </span>{
	<span style="color:rgb(136, 174, 206); font-weight:400;">bool</span> return_value = <span style="color:rgb(240, 141, 73); font-weight:400;">true</span>;
	FILE *file;
	<span style="color:rgb(136, 174, 206); font-weight:400;">char</span> magic[<span style="color:rgb(240, 141, 73); font-weight:400;">4</span>];
	<span style="color:rgb(136, 174, 206); font-weight:400;">int32_t</span> filesize;
	<span style="color:rgb(136, 174, 206); font-weight:400;">int32_t</span> format_length;		<span style="color:rgb(153, 153, 153); font-weight:400;">// 16</span>
	<span style="color:rgb(136, 174, 206); font-weight:400;">int16_t</span> format_type;		<span style="color:rgb(153, 153, 153); font-weight:400;">// 1 = PCM</span>
	<span style="color:rgb(136, 174, 206); font-weight:400;">int16_t</span> num_channels;		<span style="color:rgb(153, 153, 153); font-weight:400;">// 1</span>
	<span style="color:rgb(136, 174, 206); font-weight:400;">int32_t</span> sample_rate;		<span style="color:rgb(153, 153, 153); font-weight:400;">// 44100</span>
	<span style="color:rgb(136, 174, 206); font-weight:400;">int32_t</span> bytes_per_second;	<span style="color:rgb(153, 153, 153); font-weight:400;">// sample_rate * num_channels * bits_per_sample / 8</span>
	<span style="color:rgb(136, 174, 206); font-weight:400;">int16_t</span> block_align;		<span style="color:rgb(153, 153, 153); font-weight:400;">// num_channels * bits_per_sample / 8</span>
	<span style="color:rgb(136, 174, 206); font-weight:400;">int16_t</span> bits_per_sample;	<span style="color:rgb(153, 153, 153); font-weight:400;">// 16</span>
	<span style="color:rgb(136, 174, 206); font-weight:400;">int32_t</span> data_size;

	file = <span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fopen.htm" target="_blank">fopen</a></span>(filename, <span style="color:rgb(181, 189, 104); font-weight:400;">&quot;rb&quot;</span>);
	<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(file == <span style="color:rgb(240, 141, 73); font-weight:400;">NULL</span>) {
		<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;%s: Failed to open file&quot;</span>, filename);
		<span style="color:rgb(136, 174, 206); font-weight:400;">return</span> <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
	}

	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(magic, <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">4</span>, file);
	<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(magic[<span style="color:rgb(240, 141, 73); font-weight:400;">0</span>] != <span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;R&#x27;</span> || magic[<span style="color:rgb(240, 141, 73); font-weight:400;">1</span>] != <span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;I&#x27;</span> || magic[<span style="color:rgb(240, 141, 73); font-weight:400;">2</span>] != <span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;F&#x27;</span> || magic[<span style="color:rgb(240, 141, 73); font-weight:400;">3</span>] != <span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;F&#x27;</span>) {
		<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;%s First 4 bytes should be \&quot;RIFF\&quot;, are \&quot;%4s\&quot;&quot;</span>, filename, magic);
		return_value = <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">goto</span> CLOSE_FILE;
	}

	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(&amp;filesize, <span style="color:rgb(240, 141, 73); font-weight:400;">4</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>, file);

	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(magic, <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">4</span>, file);
	<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(magic[<span style="color:rgb(240, 141, 73); font-weight:400;">0</span>] != <span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;W&#x27;</span> || magic[<span style="color:rgb(240, 141, 73); font-weight:400;">1</span>] != <span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;A&#x27;</span> || magic[<span style="color:rgb(240, 141, 73); font-weight:400;">2</span>] != <span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;V&#x27;</span> || magic[<span style="color:rgb(240, 141, 73); font-weight:400;">3</span>] != <span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;E&#x27;</span>) {
		<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;%s 4 bytes should be \&quot;WAVE\&quot;, are \&quot;%4s\&quot;&quot;</span>, filename, magic);
		return_value = <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">goto</span> CLOSE_FILE;
	}

	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(magic, <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">4</span>, file);
	<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(magic[<span style="color:rgb(240, 141, 73); font-weight:400;">0</span>] != <span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;f&#x27;</span> || magic[<span style="color:rgb(240, 141, 73); font-weight:400;">1</span>] != <span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;m&#x27;</span> || magic[<span style="color:rgb(240, 141, 73); font-weight:400;">2</span>] != <span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;t&#x27;</span> || magic[<span style="color:rgb(240, 141, 73); font-weight:400;">3</span>] != <span style="color:rgb(181, 189, 104); font-weight:400;">&#x27; &#x27;</span>) {
		<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;%s 4 bytes should be \&quot;fmt/0\&quot;, are \&quot;%4s\&quot;&quot;</span>, filename, magic);
		return_value = <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">goto</span> CLOSE_FILE;
	}

	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(&amp;format_length, <span style="color:rgb(240, 141, 73); font-weight:400;">4</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>, file);
	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(&amp;format_type, <span style="color:rgb(240, 141, 73); font-weight:400;">2</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>, file);
	<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(format_type != <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>) {
		<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;%s format type should be 1, is %d&quot;</span>, filename, format_type);
		return_value = <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">goto</span> CLOSE_FILE;
	}

	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(&amp;num_channels, <span style="color:rgb(240, 141, 73); font-weight:400;">2</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>, file);
	<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(num_channels != <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>) {
		<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;%s Number of channels should be 1, is %d&quot;</span>, filename, num_channels);
		return_value = <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">goto</span> CLOSE_FILE;
	}

	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(&amp;sample_rate, <span style="color:rgb(240, 141, 73); font-weight:400;">4</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>, file);
	<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(sample_rate != <span style="color:rgb(240, 141, 73); font-weight:400;">44100</span>) {
		<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;%s Sample rate should be 44100, is %d&quot;</span>, filename, sample_rate);
		return_value = <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">goto</span> CLOSE_FILE;
	}

	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(&amp;bytes_per_second, <span style="color:rgb(240, 141, 73); font-weight:400;">4</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>, file);
	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(&amp;block_align, <span style="color:rgb(240, 141, 73); font-weight:400;">2</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>, file);
	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(&amp;bits_per_sample, <span style="color:rgb(240, 141, 73); font-weight:400;">2</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>, file);
	<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(bits_per_sample != <span style="color:rgb(240, 141, 73); font-weight:400;">16</span>) {
		<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;%s bits per sample should be 16, is %d&quot;</span>, filename, bits_per_sample);
		return_value = <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">goto</span> CLOSE_FILE;
	}

	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(magic, <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">4</span>, file);
	<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(magic[<span style="color:rgb(240, 141, 73); font-weight:400;">0</span>] != <span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;d&#x27;</span> || magic[<span style="color:rgb(240, 141, 73); font-weight:400;">1</span>] != <span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;a&#x27;</span> || magic[<span style="color:rgb(240, 141, 73); font-weight:400;">2</span>] != <span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;t&#x27;</span> || magic[<span style="color:rgb(240, 141, 73); font-weight:400;">3</span>] != <span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;a&#x27;</span>) {
		<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;%s 4 bytes should be \&quot;data\&quot;, are \&quot;%4s\&quot;&quot;</span>, filename, magic);
		return_value = <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">goto</span> CLOSE_FILE;
	}

	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(&amp;data_size, <span style="color:rgb(240, 141, 73); font-weight:400;">4</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>, file);

	sound-&gt;data = <span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_malloc.htm" target="_blank">malloc</a></span>(data_size);
	<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(sound-&gt;data == <span style="color:rgb(240, 141, 73); font-weight:400;">NULL</span>) {
		<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;%s Failed to allocate %d bytes for data&quot;</span>, filename, data_size);
		return_value = <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">goto</span> CLOSE_FILE;
	}

	<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(sound-&gt;data, <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>, data_size, file) != data_size) {
		<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;%s Failed to read data bytes&quot;</span>, filename);
		return_value = <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
		<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_free.htm" target="_blank">free</a></span>(sound-&gt;data);
		<span style="color:rgb(136, 174, 206); font-weight:400;">goto</span> CLOSE_FILE;
	}

	sound-&gt;samples = data_size / <span style="color:rgb(240, 141, 73); font-weight:400;">2</span>;

	CLOSE_FILE:
	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fclose.htm" target="_blank">fclose</a></span>(file);

	<span style="color:rgb(136, 174, 206); font-weight:400;">return</span> return_value;
}</code>

<a href="build.bat" download>build.bat</a>
<code>gcc main.c -lwinmm</code>

<hr>
<h4><a id="Code Walkthrough" style="color:rgb(255,255,255);">Code Walkthrough</a></h4>

<code><span style="color:rgb(136, 174, 206); font-weight:400;">#<span style="color:rgb(136, 174, 206); font-weight:400;">include</span> <span style="color:rgb(181, 189, 104); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/stdio_h.htm" target="_blank">&lt;stdio.h&gt;</a></span></span></code>
We need to include stdio to read files.

<code><span style="color:rgb(136, 174, 206); font-weight:400;">typedef</span> <span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">struct</span> {</span>
	<span style="color:rgb(136, 174, 206); font-weight:400;">uint32_t</span> samples;
	<span style="color:rgb(136, 174, 206); font-weight:400;">int16_t</span> *data;
} <span style="color:rgb(136, 174, 206); font-weight:400;">sound_t</span>;

<span style="color:rgb(136, 174, 206); font-weight:400;">sound_t</span> hello;</code>
I’ve created this sound structure to hold the sound samples and sample count.

<code>	<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(!<span style="color:rgb(240, 141, 73); font-weight:400;">LoadWav</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;cheers.wav&quot;</span>, &amp;hello)) {
		<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;Failed to load cheers.wav&quot;</span>);
		<span style="color:rgb(136, 174, 206); font-weight:400;">return</span> <span style="color:rgb(240, 141, 73); font-weight:400;">-1</span>;
	}</code>
In our main program we call the “LoadWav” function to load the sound file, passing it the filename and sound structure to use.

<code><span style="color:rgb(255, 255, 255); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">bool</span> <span style="color:rgb(240, 141, 73); font-weight:400;">LoadWav</span><span style="color:rgb(255, 255, 255); font-weight:400;">(<span style="color:rgb(136, 174, 206); font-weight:400;">const</span> <span style="color:rgb(136, 174, 206); font-weight:400;">char</span> *filename, <span style="color:rgb(136, 174, 206); font-weight:400;">sound_t</span> *sound)</span> </span>{
	<span style="color:rgb(136, 174, 206); font-weight:400;">bool</span> return_value = <span style="color:rgb(240, 141, 73); font-weight:400;">true</span>;
	FILE *file;
	<span style="color:rgb(136, 174, 206); font-weight:400;">char</span> magic[<span style="color:rgb(240, 141, 73); font-weight:400;">4</span>];
	<span style="color:rgb(136, 174, 206); font-weight:400;">int32_t</span> filesize;
	<span style="color:rgb(136, 174, 206); font-weight:400;">int32_t</span> format_length;		<span style="color:rgb(153, 153, 153); font-weight:400;">// 16</span>
	<span style="color:rgb(136, 174, 206); font-weight:400;">int16_t</span> format_type;		<span style="color:rgb(153, 153, 153); font-weight:400;">// 1 = PCM</span>
	<span style="color:rgb(136, 174, 206); font-weight:400;">int16_t</span> num_channels;		<span style="color:rgb(153, 153, 153); font-weight:400;">// 1</span>
	<span style="color:rgb(136, 174, 206); font-weight:400;">int32_t</span> sample_rate;		<span style="color:rgb(153, 153, 153); font-weight:400;">// 44100</span>
	<span style="color:rgb(136, 174, 206); font-weight:400;">int32_t</span> bytes_per_second;	<span style="color:rgb(153, 153, 153); font-weight:400;">// sample_rate * num_channels * bits_per_sample / 8</span>
	<span style="color:rgb(136, 174, 206); font-weight:400;">int16_t</span> block_align;		<span style="color:rgb(153, 153, 153); font-weight:400;">// num_channels * bits_per_sample / 8</span>
	<span style="color:rgb(136, 174, 206); font-weight:400;">int16_t</span> bits_per_sample;	<span style="color:rgb(153, 153, 153); font-weight:400;">// 16</span>
	<span style="color:rgb(136, 174, 206); font-weight:400;">int32_t</span> data_size;</code>
First we declare the return value variable, file pointer, and all the data we’re going to load from the file. Several of these are unnecessary but I included them so you can analyze the wave files you’re loading.

<code>	file = <span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fopen.htm" target="_blank">fopen</a></span>(filename, <span style="color:rgb(181, 189, 104); font-weight:400;">&quot;rb&quot;</span>);
	<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(file == <span style="color:rgb(240, 141, 73); font-weight:400;">NULL</span>) {
		<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;%s: Failed to open file&quot;</span>, filename);
		<span style="color:rgb(136, 174, 206); font-weight:400;">return</span> <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
	}</code>
We open the file with fopen, passing in the filename and “read binary” mode. If fopen failed, file will be NULL in which case we print the error and return false.

<code>	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(magic, <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">4</span>, file);
	<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(magic[<span style="color:rgb(240, 141, 73); font-weight:400;">0</span>] != <span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;R&#x27;</span> || magic[<span style="color:rgb(240, 141, 73); font-weight:400;">1</span>] != <span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;I&#x27;</span> || magic[<span style="color:rgb(240, 141, 73); font-weight:400;">2</span>] != <span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;F&#x27;</span> || magic[<span style="color:rgb(240, 141, 73); font-weight:400;">3</span>] != <span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;F&#x27;</span>) {
		<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;%s First 4 bytes should be \&quot;RIFF\&quot;, are \&quot;%4s\&quot;&quot;</span>, filename, magic);
		return_value = <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">goto</span> CLOSE_FILE;
	}</code>
Next we use fread, giving it the address to put what it reads, the size of each element, the number of elements to read and the file to read from. The first four bytes of a wav file are the letters “RIFF”. If they’re not, we print the error, set the return value to false and goto CLOSE_FILE, which is at the end of the function and closes the file and returns. Most of the error conditions will mimic this behaviour so I’ll skip over it from now on.

<code>	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(&amp;filesize, <span style="color:rgb(240, 141, 73); font-weight:400;">4</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>, file);

	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(magic, <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">4</span>, file);
	<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(magic[<span style="color:rgb(240, 141, 73); font-weight:400;">0</span>] != <span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;W&#x27;</span> || magic[<span style="color:rgb(240, 141, 73); font-weight:400;">1</span>] != <span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;A&#x27;</span> || magic[<span style="color:rgb(240, 141, 73); font-weight:400;">2</span>] != <span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;V&#x27;</span> || magic[<span style="color:rgb(240, 141, 73); font-weight:400;">3</span>] != <span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;E&#x27;</span>) {
		<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;%s 4 bytes should be \&quot;WAVE\&quot;, are \&quot;%4s\&quot;&quot;</span>, filename, magic);
		return_value = <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">goto</span> CLOSE_FILE;
	}

	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(magic, <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">4</span>, file);
	<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(magic[<span style="color:rgb(240, 141, 73); font-weight:400;">0</span>] != <span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;f&#x27;</span> || magic[<span style="color:rgb(240, 141, 73); font-weight:400;">1</span>] != <span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;m&#x27;</span> || magic[<span style="color:rgb(240, 141, 73); font-weight:400;">2</span>] != <span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;t&#x27;</span> || magic[<span style="color:rgb(240, 141, 73); font-weight:400;">3</span>] != <span style="color:rgb(181, 189, 104); font-weight:400;">&#x27; &#x27;</span>) {
		<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;%s 4 bytes should be \&quot;fmt/0\&quot;, are \&quot;%4s\&quot;&quot;</span>, filename, magic);
		return_value = <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">goto</span> CLOSE_FILE;
	}</code>
Next we read the file size, the letters “WAVE” and “fmt ”, which marks the beginning of the format data.

<code>	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(&amp;format_length, <span style="color:rgb(240, 141, 73); font-weight:400;">4</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>, file);
	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(&amp;format_type, <span style="color:rgb(240, 141, 73); font-weight:400;">2</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>, file);
	<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(format_type != <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>) {
		<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;%s format type should be 1, is %d&quot;</span>, filename, format_type);
		return_value = <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">goto</span> CLOSE_FILE;
	}

	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(&amp;num_channels, <span style="color:rgb(240, 141, 73); font-weight:400;">2</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>, file);
	<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(num_channels != <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>) {
		<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;%s Number of channels should be 1, is %d&quot;</span>, filename, num_channels);
		return_value = <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">goto</span> CLOSE_FILE;
	}

	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(&amp;sample_rate, <span style="color:rgb(240, 141, 73); font-weight:400;">4</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>, file);
	<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(sample_rate != <span style="color:rgb(240, 141, 73); font-weight:400;">44100</span>) {
		<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;%s Sample rate should be 44100, is %d&quot;</span>, filename, sample_rate);
		return_value = <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">goto</span> CLOSE_FILE;
	}

	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(&amp;bytes_per_second, <span style="color:rgb(240, 141, 73); font-weight:400;">4</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>, file);
	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(&amp;block_align, <span style="color:rgb(240, 141, 73); font-weight:400;">2</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>, file);
	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(&amp;bits_per_sample, <span style="color:rgb(240, 141, 73); font-weight:400;">2</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>, file);
	<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(bits_per_sample != <span style="color:rgb(240, 141, 73); font-weight:400;">16</span>) {
		<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;%s bits per sample should be 16, is %d&quot;</span>, filename, bits_per_sample);
		return_value = <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">goto</span> CLOSE_FILE;
	}</code>
The format length is always 16, and format type should always be 1 for PCM. The number of channels should be 1, sample rate should be 44100, then we read bytes per second, block align and bits per sample, which should be 16.

<code>	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(magic, <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">4</span>, file);
	<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(magic[<span style="color:rgb(240, 141, 73); font-weight:400;">0</span>] != <span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;d&#x27;</span> || magic[<span style="color:rgb(240, 141, 73); font-weight:400;">1</span>] != <span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;a&#x27;</span> || magic[<span style="color:rgb(240, 141, 73); font-weight:400;">2</span>] != <span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;t&#x27;</span> || magic[<span style="color:rgb(240, 141, 73); font-weight:400;">3</span>] != <span style="color:rgb(181, 189, 104); font-weight:400;">&#x27;a&#x27;</span>) {
		<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;%s 4 bytes should be \&quot;data\&quot;, are \&quot;%4s\&quot;&quot;</span>, filename, magic);
		return_value = <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">goto</span> CLOSE_FILE;
	}

	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(&amp;data_size, <span style="color:rgb(240, 141, 73); font-weight:400;">4</span>, <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>, file);

	sound-&gt;data = <span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_malloc.htm" target="_blank">malloc</a></span>(data_size);
	<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(sound-&gt;data == <span style="color:rgb(240, 141, 73); font-weight:400;">NULL</span>) {
		<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;%s Failed to allocate %d bytes for data&quot;</span>, filename, data_size);
		return_value = <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
		<span style="color:rgb(136, 174, 206); font-weight:400;">goto</span> CLOSE_FILE;
	}

	<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fread.htm" target="_blank">fread</a></span>(sound-&gt;data, <span style="color:rgb(240, 141, 73); font-weight:400;">1</span>, data_size, file) != data_size) {
		<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;%s Failed to read data bytes&quot;</span>, filename);
		return_value = <span style="color:rgb(240, 141, 73); font-weight:400;">false</span>;
		<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_free.htm" target="_blank">free</a></span>(sound-&gt;data);
		<span style="color:rgb(136, 174, 206); font-weight:400;">goto</span> CLOSE_FILE;
	}</code>
Next are the letters “data” to mark the data chunk. We read the data size in bytes and allocate enough memory with malloc. Now we can finally read the sound data. Fread always returns the number of bytes read, so we check it’s correct and if not, we free our allocated memory and otherwise handle the error as usual.

<code>	sound-&gt;samples = data_size / <span style="color:rgb(240, 141, 73); font-weight:400;">2</span>;

	CLOSE_FILE:
	<span style="color:rgb(240, 141, 73); font-weight:400;"><a href="https://www.tutorialspoint.com/c_standard_library/c_function_fclose.htm" target="_blank">fclose</a></span>(file);

	<span style="color:rgb(136, 174, 206); font-weight:400;">return</span> return_value;
}</code>
Lastly we set our sound structure’s sample count to half the data size, since each 16-bit sample is two bytes, then close the file and return. On a successful read we wouldn’t have hit any error conditions and so return_value will still be set to true since its declaration, so whenever we call this function we can just check whether it returns true or false.

<code>		<span style="color:rgb(136, 174, 206); font-weight:400;">case</span> WOM_DONE:  {
			<span style="color:rgb(240, 141, 73); font-weight:400;">printf</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;WOM_DONE\n&quot;</span>);
			to = chunks[chunk_swap];
			<span style="color:rgb(136, 174, 206); font-weight:400;">for</span>(<span style="color:rgb(136, 174, 206); font-weight:400;">int</span> j = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>; j &lt; CHUNK_SIZE; ++j) {
				<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(sound_position &lt; hello.samples) {
					*(to++) = hello.data[sound_position++];
				}
				<span style="color:rgb(136, 174, 206); font-weight:400;">else</span> {
					quit = <span style="color:rgb(240, 141, 73); font-weight:400;">true</span>;
					*(to++) = <span style="color:rgb(240, 141, 73); font-weight:400;">0</span>;
				}
			}
			<span style="color:rgb(136, 174, 206); font-weight:400;">if</span>(<span style="color:rgb(240, 141, 73); font-weight:400;">waveOutWrite</span>(wave_out, &amp;header[chunk_swap], <span style="color:rgb(240, 141, 73); font-weight:400;"><span style="color:rgb(136, 174, 206); font-weight:400;">sizeof</span></span>(header[chunk_swap])) != MMSYSERR_NOERROR) {
				<span style="color:rgb(240, 141, 73); font-weight:400;">PRINT_ERROR</span>(<span style="color:rgb(181, 189, 104); font-weight:400;">&quot;waveOutWrite failed\n&quot;</span>);
			}
			chunk_swap = !chunk_swap;
		} <span style="color:rgb(136, 174, 206); font-weight:400;">break</span>;</code>
That’s all the loading code, now to play the sound I’m just filling the sound chunks we’re passing to WinMM with the sound samples instead of generating sine wave samples. I’m using this sound_position variable to iterate over the samples and when they’ve all been used I just tell the program to quit.

That’s how to load and play .wav files in C. In my next tutorials I’ll show you how to do precise timing in C on Windows and soon I’ll be starting a series of tutorials on how to make a complete small game in C.

<hr>
If you've got questions about any of the code feel free to e-mail me or comment on the <a href="https://www.youtube.com/watch?v=Q4XdG92abWA" target="_blank">youtube video</a>. I'll try to answer them, or someone else might come along and help you out. If you've got any extra tips about how this code can be better or just more useful info about the code, let me know so I can update the tutorial.

Thanks to Froggie717 for criticisms and correcting errors in this tutorial.

Cheers.

</article>

</body>

<?php include "/blog/blogbottom.html" ?>

<?php include "footer_common.html" ?>

</html>
