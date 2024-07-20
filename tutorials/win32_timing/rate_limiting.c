  #define WIN32_LEAN_AND_MEAN
 #include <windows.h>
 #include <mmsystem.h>
 #include <stdio.h>
 #include <stdint.h>
 #include <conio.h>

 int WINAPI WinMain(HINSTANCE hInstance, HINSTANCE hPrevInstance, LPSTR lpCmdLine, int nShowCmd) {
	 LARGE_INTEGER ticks_per_second, start, current;
	 int64_t ticks_per_loop, ticks_per_millisecond;
	 unsigned int loop_count = 0;

	 timeBeginPeriod(1);

	 QueryPerformanceFrequency(&ticks_per_second);
	 ticks_per_millisecond = ticks_per_second.QuadPart / 1000;

	 ticks_per_loop = ticks_per_second.QuadPart / 5;

	 QueryPerformanceCounter(&start);
	 while(!kbhit()) {
		 printf("%u ", ++loop_count);

		 QueryPerformanceCounter(&current);

		 static int64_t sleep_time;
		 sleep_time = (start.QuadPart + ticks_per_loop - current.QuadPart) / ticks_per_millisecond - 2;

		 printf("Sleeping: %lldms\n", sleep_time);

		 Sleep(sleep_time);

		 do {
			 Sleep(0);
			 QueryPerformanceCounter(&current);
		 } while(current.QuadPart < start.QuadPart + ticks_per_loop);

		 start.QuadPart += ticks_per_loop;
		 if(current.QuadPart - start.QuadPart > ticks_per_loop)
			 start = current;
	 }

	 timeEndPeriod(1);

	 return 0;
 }
