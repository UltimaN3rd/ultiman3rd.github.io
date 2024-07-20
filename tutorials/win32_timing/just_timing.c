#define WIN32_LEAN_AND_MEAN
#include <windows.h>
#include <stdio.h>

LARGE_INTEGER frequency, a, b;
float elapsed_seconds;

int WINAPI WinMain (HINSTANCE hInstance, HINSTANCE hPrevInstance, LPSTR lpCmdLine, int nShowCmd) {
	QueryPerformanceFrequency(&frequency);
	printf("Frequency: %lld ticks per second.\n", frequency.QuadPart);

	QueryPerformanceCounter(&a);
	printf("A: %lld\n", a.QuadPart);

	QueryPerformanceCounter(&b);
	printf("B: %lld\n", b.QuadPart);

	elapsed_seconds = (float)(b.QuadPart - a.QuadPart) / frequency.QuadPart;
	printf("Elaped time between A and B: %fs\n", elapsed_seconds);

	return 0;
}

