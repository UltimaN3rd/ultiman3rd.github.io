#include <Windows.h>
#include "zen_timer.h"
#include <stdio.h>

int WINAPI WinMain(HINSTANCE hInstance, HINSTANCE hPrevInstance, LPSTR lpCmdLine, int nShowCmd) {
	if(!AttachConsole(ATTACH_PARENT_PROCESS)) {
		return -1;
	}
	freopen("CONOUT$", "w", stdout);
	printf("\n");

	static zen_timer_t timer;
	static long long slept, num_overslept;
	static unsigned long long avg = 0, min = -1, max = 0;
	static unsigned long time;

	ZenTimer_Init();

	HANDLE waitable_timer = CreateWaitableTimerEx(NULL, NULL, CREATE_WAITABLE_TIMER_HIGH_RESOLUTION, TIMER_ALL_ACCESS);
	LARGE_INTEGER wait_time;
	wait_time.QuadPart = -5000;

#define NUM_LOOPS 10000
	for(int i = 0; i < NUM_LOOPS; ++i) {
		timer = ZenTimer_Start();
		SetWaitableTimer(waitable_timer, &wait_time, 0, 0, 0, 0);
		WaitForSingleObject(waitable_timer, INFINITE);
		slept = ZenTimer_End(&timer);
		avg += slept;
		if(slept < min) min = slept;
		if(slept > max) max = slept;
	}

	printf("Avg: %llu\nMin: %llu\nMax: %llu\n", avg / NUM_LOOPS, min, max);

	return 0;
}
