#include <Windows.h>
#include <stdio.h>
#include <mmsystem.h>
#include "zen_timer.h"

extern NTSYSAPI NTSTATUS NTAPI NtSetTimerResolution(ULONG DesiredResolution, BOOLEAN SetResolution, PULONG CurrentResolution);

int WINAPI WinMain(HINSTANCE hInstance, HINSTANCE hPrevInstance, LPSTR lpCmdLine, int nShowCmd) {
	static zen_timer_t timer;
	static long long slept;
	static unsigned long long avg = 0, min = -1, max = 0;

#if 0
	timeBeginPeriod(1);
#else
	static unsigned long time;
	NtSetTimerResolution(1000, TRUE, &time);
	printf("Timer resolution: %ldns\n", time * 100);
#endif

	ZenTimer_Init();

	#define NUM_LOOPS 10000
	for(int i = 0; i < NUM_LOOPS; ++i) {
		timer = ZenTimer_Start();
		Sleep(1);
		slept = ZenTimer_End(&timer);
		avg += slept;
		if(slept < min) min = slept;
		if(slept > max) max = slept;
	}

	printf("Avg: %llu\nMin: %llu\nMax: %llu\n", avg / NUM_LOOPS, min, max);

	NtSetTimerResolution(0, FALSE, &time);

	return 0;
}
