#include "zen_timer.h"
#include <stdio.h>

int WINAPI WinMain(HINSTANCE hInstance, HINSTANCE hPrevInstance, LPSTR lpCmdLine, int nShowCmd) {
	ZenTimer_Init();

	zen_timer_t timer = ZenTimer_Start();

	for(int i = 0; i < 1000; ++i) {
		rand();
	}

	int64_t time = ZenTimer_End(&timer);

	printf("1000 rand()s took: %lldus\n", time);

	return 0;
}
