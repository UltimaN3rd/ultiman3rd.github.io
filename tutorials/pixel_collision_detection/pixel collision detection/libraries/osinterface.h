#pragma once

// For a frame buffer allocated by this library, automatically resized to the window size, you needn't define anything.
// For a frame buffer allocated by you, scaled to fit the window, you must:
// #define OSINTERFACE_FRAME_BUFFER_SCALED

#ifdef OSINTERFACE_LINUX_USE_OPENGL
	#ifndef __linux__
		#undef OSINTERFACE_LINUX_USE_OPENGL
	#endif
#endif

// Everthing publicly accessible in this library is prepended with this NAMESPACE macro. You can change the definition here to whatever you like.
#pragma push_macro ("NAMESPACE")
#define NAMESPACE os_

#pragma push_macro ("C")
#pragma push_macro ("C_")
#pragma push_macro ("N")
#pragma push_macro ("Min")
#pragma push_macro ("Max")

#define C_(a,b) a##b
#define C(a,b) C_(a,b)
#define N(a) C(NAMESPACE, a)

#define Min(a, b) ((a) < (b) ? (a) : (b))
#define Max(a, b) ((a) > (b) ? (a) : (b))

#include <stdint.h>
#include <stdbool.h>
#include <stdlib.h>
#include <string.h>
#include "PRINT_ERROR.h"

typedef enum {
	N(EVENT_NULL), N(EVENT_INTERNAL), N(EVENT_QUIT), N(EVENT_WINDOW_RESIZE), N(EVENT_KEY_PRESS), N(EVENT_KEY_RELEASE), N(EVENT_MOUSE_BUTTON_PRESS), N(EVENT_MOUSE_BUTTON_RELEASE), N(EVENT_MOUSE_MOVE), N(EVENT_MOUSE_SCROLL),
} N(event_e);

typedef enum { N(MOUSE_LEFT) = 0b1, N(MOUSE_MIDDLE) = 0b10, N(MOUSE_RIGHT) = 0b100, N(MOUSE_X1) = 0b1000, N(MOUSE_X2) = 0b10000 } N(mouse_button_e);

#ifdef WIN32
#define UNICODE
#define _UNICODE
#include <windows.h>
typedef enum {
	N(KEY_A) = 'A', N(KEY_B) = 'B', N(KEY_C) = 'C', N(KEY_D) = 'D', N(KEY_E) = 'E', N(KEY_F) = 'F', N(KEY_G) = 'G', N(KEY_H) = 'H', N(KEY_I) = 'I', N(KEY_J) = 'J', N(KEY_K) = 'K', N(KEY_L) = 'L', N(KEY_M) = 'M', N(KEY_N) = 'N', N(KEY_O) = 'O', N(KEY_P) = 'P',	N(KEY_Q) = 'Q',	N(KEY_R) = 'R',	N(KEY_S) = 'S',	N(KEY_T) = 'T',	N(KEY_U) = 'U',	N(KEY_V) = 'V',	N(KEY_W) = 'W',	N(KEY_X) = 'X',	N(KEY_Y) = 'Y',	N(KEY_Z) = 'Z',
	N(KEY_0) = '0', N(KEY_1) = '1', N(KEY_2) = '2', N(KEY_3) = '3', N(KEY_4) = '4', N(KEY_5) = '5', N(KEY_6) = '6', N(KEY_7) = '7', N(KEY_8) = '8', N(KEY_9) = '9',
	N(KEY_ENTER) = VK_RETURN, N(KEY_SPACE) = VK_SPACE, N(KEY_ESCAPE) = VK_ESCAPE, N(KEY_LEFT) = VK_LEFT, N(KEY_RIGHT) = VK_RIGHT, N(KEY_UP) = VK_UP, N(KEY_DOWN) = VK_DOWN, N(KEY_PLUS) = VK_OEM_PLUS, N(KEY_MINUS) = VK_OEM_MINUS, N(KEY_LALT) = VK_LMENU, N(KEY_RALT) = VK_RMENU,
	N(KEY_F1) = VK_F1, N(KEY_F2) = VK_F2, N(KEY_F3) = VK_F3, N(KEY_F4) = VK_F4, N(KEY_F5) = VK_F5, N(KEY_F6) = VK_F6, N(KEY_F7) = VK_F7, N(KEY_F8) = VK_F8, N(KEY_F9) = VK_F9, N(KEY_F10) = VK_F10, N(KEY_F11) = VK_F11, N(KEY_F12) = VK_F12,
} N(key_e);
#elif defined __linux__
#define XK_LATIN1
#define XK_MISCELLANY
#include <X11/keysymdef.h>
typedef enum {
	N(KEY_A) = 'A', N(KEY_B) = 'B', N(KEY_C) = 'C', N(KEY_D) = 'D', N(KEY_E) = 'E', N(KEY_F) = 'F', N(KEY_G) = 'G', N(KEY_H) = 'H', N(KEY_I) = 'I', N(KEY_J) = 'J', N(KEY_K) = 'K', N(KEY_L) = 'L', N(KEY_M) = 'M', N(KEY_N) = 'N', N(KEY_O) = 'O', N(KEY_P) = 'P',	N(KEY_Q) = 'Q',	N(KEY_R) = 'R',	N(KEY_S) = 'S',	N(KEY_T) = 'T',	N(KEY_U) = 'U',	N(KEY_V) = 'V',	N(KEY_W) = 'W',	N(KEY_X) = 'X',	N(KEY_Y) = 'Y',	N(KEY_Z) = 'Z',
	N(KEY_0) = '0', N(KEY_1) = '1', N(KEY_2) = '2', N(KEY_3) = '3', N(KEY_4) = '4', N(KEY_5) = '5', N(KEY_6) = '6', N(KEY_7) = '7', N(KEY_8) = '8', N(KEY_9) = '9',
	N(KEY_ENTER) = (uint8_t)XK_Return, N(KEY_SPACE) = (uint8_t)XK_space, N(KEY_ESCAPE) = (uint8_t)XK_Escape, N(KEY_LEFT) = (uint8_t)XK_Left, N(KEY_RIGHT) = (uint8_t)XK_Right, N(KEY_UP) = (uint8_t)XK_Up, N(KEY_DOWN) = (uint8_t)XK_Down, N(KEY_PLUS) = (uint8_t)XK_plus, N(KEY_MINUS) = (uint8_t)XK_minus, N(KEY_LALT) = (uint8_t)XK_Alt_L, N(KEY_RALT) = (uint8_t)XK_Alt_R,
	N(KEY_F1) = (uint8_t)XK_F1, N(KEY_F2) = (uint8_t)XK_F2, N(KEY_F3) = (uint8_t)XK_F3, N(KEY_F4) = (uint8_t)XK_F4, N(KEY_F5) = (uint8_t)XK_F5, N(KEY_F6) = (uint8_t)XK_F6, N(KEY_F7) = (uint8_t)XK_F7, N(KEY_F8) = (uint8_t)XK_F8, N(KEY_F9) = (uint8_t)XK_F9, N(KEY_F10) = (uint8_t)XK_F10, N(KEY_F11) = (uint8_t)XK_F11, N(KEY_F12) = (uint8_t)XK_F12,
} N(key_e);
#endif

typedef struct {
	int x, y;
} N(vec2i);

typedef struct {
	float x, y;
} N(vec2f);

typedef struct {
    N(event_e) type;
    union {
        struct { // WINDOW_RESIZE
            int width, height;
        };
		N(key_e) key; // KEY_PRESS + RELEASE
		struct {
			N(mouse_button_e) button; // MOUSE_BUTTON_PRESS + _RELEASE
			union { N(vec2f) position, p; };
		} button;
		struct { // MOUSE_MOVE
			N(vec2i) previous_position, new_position;
		};
		bool scrolled_up; // MOUSE_SCROLL
    };
} N(event_t);

struct {
	bool keyboard[256];
	struct {
		union { N(vec2i) p, position; };
		uint8_t buttons;
	} mouse;
	struct {
		union { unsigned int width, w; };
		union { unsigned int height, h; };
		bool is_fullscreen;
	} window;
} N(public);

#ifdef OSINTERFACE_FRAME_BUFFER_SCALED
void N(WindowFrameBufferCalculateScale) ();
#endif

#ifdef WIN32

#include <uxtheme.h>
#include <mmsystem.h>
#include <process.h>
#include "NtSetTimerResolution.h"

struct {
	struct {
		HWND window_handle;
		BITMAPINFO bitmap_info;
		HBITMAP bitmap;
		HDC bitmap_device_context;
		uint32_t *bitmap_pixels;
		HBRUSH background_brush;
		int64_t ticks_per_microsecond;
    	N(event_t) event;
	} win32;
	struct {
		int width, height;
		uint32_t *pixels;
		int scale;
		int left, bottom;
		bool has_been_set;
	} frame_buffer;
} N(private);

LRESULT CALLBACK N(Internal_WindowProcessMessage)(HWND window_handle, UINT message, WPARAM wParam, LPARAM lParam);

bool N(Init) (const char *window_title) {
	{ ULONG time; NtSetTimerResolution(1000, TRUE, &time); }

	{
		LARGE_INTEGER t;
		QueryPerformanceFrequency(&t);
		N(private).win32.ticks_per_microsecond = t.QuadPart / 1000000;
	}

	{
		const wchar_t window_class_name[] = L"Window Class";
		WNDCLASS window_class = { 0 };
		window_class.lpfnWndProc = N(Internal_WindowProcessMessage);
		window_class.lpszClassName = window_class_name;
		RegisterClass(&window_class);

		N(private).win32.bitmap_info.bmiHeader.biSize = sizeof (N(private).win32.bitmap_info.bmiHeader);
		N(private).win32.bitmap_info.bmiHeader.biPlanes = 1;
		N(private).win32.bitmap_info.bmiHeader.biBitCount = 32;
		N(private).win32.bitmap_info.bmiHeader.biCompression = BI_RGB;
		N(private).win32.bitmap_device_context = CreateCompatibleDC (0);

		wchar_t title[1024];
		mbstowcs (title, window_title, 1023);

		N(private).win32.window_handle = CreateWindow (window_class_name, title, WS_OVERLAPPEDWINDOW | WS_VISIBLE | WS_MAXIMIZE, CW_USEDEFAULT, CW_USEDEFAULT, CW_USEDEFAULT, CW_USEDEFAULT, NULL, NULL, NULL, NULL);
		N(public).window.is_fullscreen = false;
		if(N(private).win32.window_handle == NULL) {
			PRINT_ERROR("CreateWindow() failed. Returned NULL.\n");
			return false;
		}
	}

	BufferedPaintInit();
	N(private).win32.background_brush = (HBRUSH)GetStockObject(GRAY_BRUSH);
	SetCursor (LoadCursor (NULL, IDC_ARROW));

	return true;
}

void N(Fullscreen) (bool fullscreen) {
	if (fullscreen) {
		N(public).window.is_fullscreen = true;
		
		RECT desktop_rect;
		HWND desktop_handle = GetDesktopWindow();
		if (desktop_handle) GetWindowRect(desktop_handle, &desktop_rect);
		else { desktop_rect.left = 0; desktop_rect.top = 0; desktop_rect.right = 800; desktop_rect.bottom = 600; }
		SetWindowLongPtr (N(private).win32.window_handle, GWL_STYLE, (WS_POPUP | WS_VISIBLE) & ~WS_OVERLAPPEDWINDOW);
		SetWindowPos (N(private).win32.window_handle, NULL, desktop_rect.left, desktop_rect.top, desktop_rect.right - desktop_rect.left, desktop_rect.bottom - desktop_rect.top, SWP_NOOWNERZORDER);
	} else {
		N(public).window.is_fullscreen = false;

		SetWindowLongPtr (N(private).win32.window_handle, GWL_STYLE, ~WS_POPUP & (WS_OVERLAPPEDWINDOW | WS_VISIBLE | WS_MAXIMIZE));
		ShowWindow (N(private).win32.window_handle, SW_SHOW);
	}
}

void N(Maximize) (bool maximize) {
	N(public).window.is_fullscreen = false;

	long args = ~WS_POPUP & (WS_OVERLAPPEDWINDOW);
	if (maximize) args |= WS_MAXIMIZE;

	SetWindowLongPtr (N(private).win32.window_handle, GWL_STYLE, args);
	ShowWindow (N(private).win32.window_handle, SW_SHOW);
}

void N(WindowSize) (int width, int height) {
	N(Maximize) (false);
	RECT rect;
	GetWindowRect (N(private).win32.window_handle, &rect);
	rect.right = rect.left + width;
	rect.bottom = rect.top + height;
	AdjustWindowRect (&rect, WS_OVERLAPPEDWINDOW, FALSE);
	if (rect.left < 0) {
		rect.right -= rect.left;
		rect.left = 0;
	}
	if (rect.top < 0) {
		rect.bottom -= rect.top;
		rect.top = 0;
	}
	SetWindowPos (N(private).win32.window_handle, HWND_TOP, rect.left, rect.top, rect.right - rect.left, rect.bottom - rect.top, SWP_SHOWWINDOW);
}

void N(ShowCursor) () {
	SetCursor (LoadCursor (NULL, IDC_ARROW));
	ShowCursor (true);
}

void N(HideCursor) () {
	ShowCursor (false);
}

N(event_t) N(NextEvent) () {
    MSG message = {0};
    N(private).win32.event.type = N(EVENT_NULL);
	if (PeekMessage (&message, NULL, 0, 0, PM_REMOVE)) {
        DispatchMessage (&message); // Fills out event structure
    }
    return N(private).win32.event;
}

// void N(WindowPositionToFrameBufferPosition) (int windowx, int windowy, int *resultx, int *resulty) {
// 	*resultx = (windowx - N(private).frame_buffer.left)   / N(private).frame_buffer.scale;
// 	*resulty = (windowy - N(private).frame_buffer.bottom) / N(private).frame_buffer.scale;
// }

LRESULT CALLBACK N(Internal_WindowProcessMessage)(HWND window_handle, UINT message, WPARAM wParam, LPARAM lParam) {
    static bool has_focus = true;

    switch (message) {
		case WM_QUIT:
		case WM_DESTROY: {
			N(private).win32.event.type = N(EVENT_QUIT);
		} break;
        
		case WM_PAINT: {
            N(private).win32.event.type = N(EVENT_INTERNAL);

			PAINTSTRUCT paint;
			HDC device_context;
			device_context = BeginPaint (window_handle, &paint);

			HPAINTBUFFER paint_buffer;
			HDC buffered_device_context;
			paint_buffer = BeginBufferedPaint (device_context, &paint.rcPaint, BPBF_DIB, NULL, &buffered_device_context);

#ifdef OSINTERFACE_FRAME_BUFFER_SCALED
				if (N(private).frame_buffer.pixels) {
					FillRect(buffered_device_context, &paint.rcPaint, N(private).win32.background_brush);
					StretchDIBits(buffered_device_context, N(private).frame_buffer.left, N(private).frame_buffer.bottom, N(private).frame_buffer.width * N(private).frame_buffer.scale, N(private).frame_buffer.height * N(private).frame_buffer.scale, 0, 0, N(private).frame_buffer.width, N(private).frame_buffer.height, N(private).frame_buffer.pixels, &N(private).win32.bitmap_info, DIB_RGB_COLORS, SRCCOPY);
				}
#else
				BitBlt (buffered_device_context, paint.rcPaint.left, paint.rcPaint.top, paint.rcPaint.right - paint.rcPaint.left, paint.rcPaint.bottom - paint.rcPaint.top, N(private).win32.bitmap_device_context, paint.rcPaint.left, paint.rcPaint.top, SRCCOPY);
#endif

			EndBufferedPaint (paint_buffer, TRUE);

			EndPaint (window_handle, &paint);
		} break;
        
		case WM_SIZE: {
            N(private).win32.event.type = N(EVENT_WINDOW_RESIZE);

			N(private).win32.event.width  = N(public).window.w = LOWORD(lParam);
			N(private).win32.event.height = N(public).window.h = HIWORD(lParam);

#ifdef OSINTERFACE_FRAME_BUFFER_SCALED
			N(private).win32.bitmap_info.bmiHeader.biWidth  = N(private).frame_buffer.width;
			N(private).win32.bitmap_info.bmiHeader.biHeight = N(private).frame_buffer.height;
			N(WindowFrameBufferCalculateScale) ();
#else
			N(private).win32.bitmap_info.bmiHeader.biWidth  = LOWORD(lParam);
			N(private).win32.bitmap_info.bmiHeader.biHeight = HIWORD(lParam);
			if (N(private).win32.bitmap) DeleteObject(N(private).win32.bitmap);
			N(private).win32.bitmap = CreateDIBSection (NULL, &N(private).win32.bitmap_info, DIB_RGB_COLORS, (void**)&N(private).win32.bitmap_pixels, 0, 0);
			SelectObject(N(private).win32.bitmap_device_context, N(private).win32.bitmap);
#endif
		} break;

		case WM_KILLFOCUS: {
			has_focus = false;
			memset(N(public).keyboard, 0, 256 * sizeof(N(public).keyboard[0]));
			N(public).mouse.buttons = 0;
		} break;

		case WM_SETFOCUS: has_focus = true; break;

		case WM_SYSKEYDOWN:
		case WM_SYSKEYUP:
		case WM_KEYDOWN:
		case WM_KEYUP: {
			if(has_focus) {
				bool key_is_down, key_was_down;
				key_is_down  = ((lParam & (1 << 31)) == 0);
				key_was_down = ((lParam & (1 << 30)) != 0);
				if(key_is_down != key_was_down) {
					if (wParam == VK_MENU) { // The ALT key is handled differently. It's a syskey, and both L and R trigger the same message so they're differentiated with lParam bit 24
						if (lParam & (1 << 24)) {
							wParam = VK_RMENU;
						}
						else {
							wParam = VK_LMENU;
						}
					}
					N(public).keyboard[(uint8_t)wParam] = key_is_down;
					N(private).win32.event.key = (uint8_t)wParam;
					if(key_is_down) {
						N(private).win32.event.type = N(EVENT_KEY_PRESS);
					}
					else {
						N(private).win32.event.type = N(EVENT_KEY_RELEASE);
					}
				}
			}
		} break;

		case WM_MOUSEMOVE: {
			N(private).win32.event.type = N(EVENT_MOUSE_MOVE);
			N(private).win32.event.previous_position.x = N(public).mouse.p.x;
			N(private).win32.event.previous_position.y = N(public).mouse.p.y;
			N(public).mouse.p.x = N(private).win32.event.new_position.x = LOWORD(lParam);
			N(public).mouse.p.y = N(private).win32.event.new_position.y = N(public).window.h - 1 - HIWORD(lParam);
		} break;

		case WM_LBUTTONDOWN: {
			N(public).mouse.buttons |=  N(MOUSE_LEFT);
			N(private).win32.event.type = N(EVENT_MOUSE_BUTTON_PRESS);
			N(private).win32.event.button.button = N(MOUSE_LEFT);
			N(private).win32.event.button.p.x = LOWORD(lParam);
			N(private).win32.event.button.p.y = N(public).window.h - 1 - HIWORD(lParam);
		} break;
		case WM_LBUTTONUP: {
			N(public).mouse.buttons &= ~N(MOUSE_LEFT);
			N(private).win32.event.type = N(EVENT_MOUSE_BUTTON_RELEASE);
			N(private).win32.event.button.button = N(MOUSE_LEFT);
			N(private).win32.event.button.p.x = LOWORD(lParam);
			N(private).win32.event.button.p.y = N(public).window.h - 1 - HIWORD(lParam);
		} break;

		case WM_RBUTTONDOWN: {
			N(public).mouse.buttons |=  N(MOUSE_RIGHT);
			N(private).win32.event.type = N(EVENT_MOUSE_BUTTON_PRESS);
			N(private).win32.event.button.button = N(MOUSE_RIGHT);
			N(private).win32.event.button.p.x = LOWORD(lParam);
			N(private).win32.event.button.p.y = N(public).window.h - 1 - HIWORD(lParam);
		} break;
		case WM_RBUTTONUP: {
			N(public).mouse.buttons &= ~N(MOUSE_RIGHT);
			N(private).win32.event.type = N(EVENT_MOUSE_BUTTON_RELEASE);
			N(private).win32.event.button.button = N(MOUSE_RIGHT);
			N(private).win32.event.button.p.x = LOWORD(lParam);
			N(private).win32.event.button.p.y = N(public).window.h - 1 - HIWORD(lParam);
		} break;

		case WM_MBUTTONDOWN: {
			N(public).mouse.buttons |=  N(MOUSE_MIDDLE);
			N(private).win32.event.type = N(EVENT_MOUSE_BUTTON_PRESS);
			N(private).win32.event.button.button = N(MOUSE_MIDDLE);
			N(private).win32.event.button.p.x = LOWORD(lParam);
			N(private).win32.event.button.p.y = N(public).window.h - 1 - HIWORD(lParam);
		} break;
		case WM_MBUTTONUP: {
			N(public).mouse.buttons &= ~N(MOUSE_MIDDLE);
			N(private).win32.event.type = N(EVENT_MOUSE_BUTTON_RELEASE);
			N(private).win32.event.button.button = N(MOUSE_MIDDLE);
			N(private).win32.event.button.p.x = LOWORD(lParam);
			N(private).win32.event.button.p.y = N(public).window.h - 1 - HIWORD(lParam);
		} break;

		case WM_XBUTTONDOWN: {
			if(GET_XBUTTON_WPARAM(wParam) == XBUTTON1) {
					 N(public).mouse.buttons |= N(MOUSE_X1);
			} else { N(public).mouse.buttons |= N(MOUSE_X2); }
			N(private).win32.event.type = N(EVENT_MOUSE_BUTTON_PRESS);
			N(private).win32.event.button.button = N(MOUSE_X1);
			N(private).win32.event.button.p.x = LOWORD(lParam);
			N(private).win32.event.button.p.y = N(public).window.h - 1 - HIWORD(lParam);
		} break;
		case WM_XBUTTONUP: {
			if(GET_XBUTTON_WPARAM(wParam) == XBUTTON1) {
					 N(public).mouse.buttons &= ~N(MOUSE_X1);
			} else { N(public).mouse.buttons &= ~N(MOUSE_X2); }
			N(private).win32.event.type = N(EVENT_MOUSE_BUTTON_RELEASE);
			N(private).win32.event.button.button = N(MOUSE_X1);
			N(private).win32.event.button.p.x = LOWORD(lParam);
			N(private).win32.event.button.p.y = N(public).window.h - 1 - HIWORD(lParam);
		} break;

		case WM_MOUSEWHEEL: {
			N(private).win32.event.type = N(EVENT_MOUSE_SCROLL);
			N(private).win32.event.scrolled_up = !(wParam & 0b10000000000000000000000000000000);
		} break;

		default: return DefWindowProc(window_handle, message, wParam, lParam);
    }

	return 0;
}

void N(WaitForScreenRefresh) () { UpdateWindow (N(private).win32.window_handle); } // Ensure that last frame has been presented

void N(DrawScreen) () { InvalidateRect (N(private).win32.window_handle, NULL, FALSE); }

int64_t N(uTime) () {
	LARGE_INTEGER ticks;
	QueryPerformanceCounter(&ticks);
	return ticks.QuadPart / N(private).win32.ticks_per_microsecond;
}

void N(uSleepEfficient) (int64_t microseconds) {
	if (microseconds > 0) Sleep (microseconds / 1000);
}

void N(uSleepPrecise) (int64_t microseconds) {
	if (microseconds < 0) return;
	int64_t end, milliseconds;
	end = N(uTime) () + microseconds;

	// Sleep an amount of milliseconds - inaccurate so we sleep 2 milliseconds less than we actually want
	milliseconds = microseconds / 1000 - 2;
	if (milliseconds < 0) goto tinysleep;
	Sleep (milliseconds);
tinysleep:
	while (N(uTime) () < end) Sleep (0);
}

#ifdef OSINTERFACE_FRAME_BUFFER_SCALED
void N(WindowPositionToScaledFrameBufferPosition) (int windowx, int windowy, int *outx, int *outy) {
	*outx = (windowx - N(private).frame_buffer.left) / N(private).frame_buffer.scale;
	*outy = (windowy - N(private).frame_buffer.bottom) / N(private).frame_buffer.scale;
}

void N(ScaledFrameBufferPositionToWindowPosition) (int framex, int framey, int *outx, int *outy) {
	*outx = framex * N(private).frame_buffer.scale + N(private).frame_buffer.left + N(private).frame_buffer.scale/2;
	*outy = framey * N(private).frame_buffer.scale + N(private).frame_buffer.bottom + N(private).frame_buffer.scale/2;
}

void N(SetWindowFrameBuffer) (uint32_t *pixels, int width, int height) {
	N(private).frame_buffer.has_been_set = true;
	N(private).frame_buffer.pixels = pixels;
	N(private).frame_buffer.width  = width;
	N(private).frame_buffer.height = height;
	N(WindowFrameBufferCalculateScale) ();
}
#endif

// Returns the refresh rate of the display on which the program window is.
// Returns 0 if the refresh rate cannot be retrieved.
int N(GetScreenRefreshRate) () {
	uint16_t screen_refresh;
	screen_refresh = GetDeviceCaps(CreateCompatibleDC(NULL), VREFRESH);
	if (screen_refresh == 1) return 0; // 0 or 1 indicate the "default" refresh rate.
	return screen_refresh;
}

void N(Cleanup) () {
	{ ULONG time; NtSetTimerResolution(0, FALSE, &time); }
}

#elif defined __linux__

#include <X11/Xlib.h>
#include <X11/Xutil.h>
#include <X11/Xatom.h>
#include <X11/XKBlib.h>
#ifdef OSINTERFACE_LINUX_USE_XRENDER
#include <X11/extensions/Xrender.h>
#elif defined OSINTERFACE_LINUX_USE_OPENGL
#include <GL/gl.h>
#include <GL/glx.h>
#include <GL/glu.h>
#endif
#include <time.h>
typedef struct timespec timespec_t;
#include <unistd.h> // usleep
#include <X11/extensions/Xfixes.h> // XFixesHideCursor

struct {
	struct {
		Display* display;
		int root_window;
		int screen;
		Window window;
		Atom WM_DELETE_WINDOW;
#ifdef OSINTERFACE_LINUX_USE_XRENDER
		XVisualInfo visual_info;
		XImage window_image;
		GC graphics_context;
		Pixmap pixmap;
#elif defined OSINTERFACE_LINUX_USE_OPENGL
		XVisualInfo *gl_visual_info;
		GLXContext gl_context;
#endif
	} x11;
	uint32_t *bitmap_pixels;
	struct {
		unsigned int width, height;
		uint32_t *pixels;
		int scale;
		int left, bottom;
		bool has_been_set;
	} frame_buffer;
} N(private);

#ifdef OSINTERFACE_LINUX_USE_XRENDER
bool N(Init) (const char *window_title, N(window_present_style_e) window_present_style) {
	N(private).window_present_style = window_present_style;

	// Create window
    {
        N(private).x11.display = XOpenDisplay(0);
        N(private).x11.root_window = DefaultRootWindow(N(private).x11.display);
        N(private).x11.screen = DefaultScreen(N(private).x11.display);

        XMatchVisualInfo(N(private).x11.display, N(private).x11.screen, 24, TrueColor, &N(private).x11.visual_info);

        XSetWindowAttributes window_attributes;
        window_attributes.background_pixel = 0xff808080;
        window_attributes.colormap = XCreateColormap(N(private).x11.display, N(private).x11.root_window, N(private).x11.visual_info.visual, AllocNone);
		window_attributes.event_mask = StructureNotifyMask | KeyPressMask | KeyReleaseMask | FocusChangeMask | PointerMotionMask | ButtonPressMask | ButtonReleaseMask;

        N(private).x11.window = XCreateWindow (N(private).x11.display, N(private).x11.root_window, 0, 0, 1280, 720, 0, N(private).x11.visual_info.depth, 0, N(private).x11.visual_info.visual, CWBackPixel | CWColormap | CWEventMask, &window_attributes);
        XMapWindow(N(private).x11.display, N(private).x11.window);

		// { // Maximize
		// 	XEvent e = {0};
		// 	e.type = ClientMessage;
		// 	e.xclient.window = N(private).x11.window;
		// 	e.xclient.message_type = XInternAtom(N(private).x11.display, "_NET_WM_STATE", False);
		// 	e.xclient.format = 32;
		// 	e.xclient.data.l[0] = _NET_WM_STATE_ADD;
		// 	e.xclient.data.l[1] = XInternAtom(N(private).x11.display, "_NET_WM_STATE_MAXIMIZED_HORZ", False);
		// 	e.xclient.data.l[2] = XInternAtom(N(private).x11.display, "_NET_WM_STATE_MAXIMIZED_VERT", False);

		// 	XSendEvent(N(private).x11.display, N(private).x11.root_window, False, SubstructureNotifyMask, &e);
		// }

		N(private).x11.WM_DELETE_WINDOW = XInternAtom(N(private).x11.display, "WM_DELETE_WINDOW", False);
        XSetWMProtocols(N(private).x11.display, N(private).x11.window, &N(private).x11.WM_DELETE_WINDOW, 1);

		// Set up drawing
		{
			struct {
				Window root_window;
				int x, y;
				unsigned int border_width, depth;
			} dummy;
			XGetGeometry (N(private).x11.display, N(private).x11.window, &dummy.root_window, &dummy.x, &dummy.y, &N(public).window.width, &N(public).window.height, &dummy.border_width, &dummy.depth);
		}
		switch (N(private).window_present_style) {
			case N(WINDOW_PRESENT_DIRECT): {
				N(private).frame_buffer.width = N(public).window.width;
				N(private).frame_buffer.height = N(public).window.height;
				N(private).frame_buffer.pixels = malloc (N(private).frame_buffer.width * N(private).frame_buffer.height * 4);
			} break;

			case N(WINDOW_PRESENT_SCALED): {
				N(WindowFrameBufferCalculateScale) ();
			}
		}
		N(private).x11.window_image = *XCreateImage (N(private).x11.display, N(private).x11.visual_info.visual, N(private).x11.visual_info.depth, ZPixmap, 0, (char*)N(private).frame_buffer.pixels, N(private).frame_buffer.width, N(private).frame_buffer.height, 32, 0);
		N(private).x11.graphics_context = DefaultGC (N(private).x11.display, N(private).x11.screen);

		XkbSetDetectableAutoRepeat(N(private).x11.display, True, 0); // No key repeat when holding key down

		{
			struct {
				Window root_window, child_window;
				int x, y;
				unsigned int mask;
			} dummy;
			XQueryPointer(N(private).x11.display, N(private).x11.window, &dummy.root_window, &dummy.child_window, &dummy.x, &dummy.y, &N(public).mouse.p.x, &N(public).mouse.p.y, &dummy.mask);
			N(public).mouse.p.y = N(public).window.h - 1 - N(public).mouse.p.y;
		}

		N(private).x11.pixmap = XCreatePixmap (N(private).x11.display, N(private).x11.root_window, 320, 240, N(private).x11.visual_info.depth);

        XFlush(N(private).x11.display);
    }

	return true;
}
#elif defined OSINTERFACE_LINUX_USE_OPENGL
bool N(Init) (const char *window_title) {
	N(public).window.is_fullscreen = false;

	// Create window
    {
        N(private).x11.display = XOpenDisplay (0);
        N(private).x11.root_window = DefaultRootWindow (N(private).x11.display);
        N(private).x11.screen = DefaultScreen (N(private).x11.display);

        // XMatchVisualInfo(N(private).x11.display, N(private).x11.screen, 24, TrueColor, &N(private).x11.visual_info);
		int attributes [] = { GLX_RGBA, GLX_DOUBLEBUFFER,
			GLX_RED_SIZE, 8, GLX_BLUE_SIZE, 8, GLX_GREEN_SIZE, 8, GLX_DEPTH_SIZE, 24,
			GLX_CONTEXT_MAJOR_VERSION_ARB, 1,
			GLX_CONTEXT_MINOR_VERSION_ARB, 0,
			0 };
		N(private).x11.gl_visual_info = glXChooseVisual (N(private).x11.display, N(private).x11.screen, attributes);
		EXIT_IF (!N(private).x11.gl_visual_info, 2);

		N(private).x11.gl_context = glXCreateContext (N(private).x11.display, N(private).x11.gl_visual_info, 0, true);

        XSetWindowAttributes window_attributes;
        window_attributes.background_pixel = 0xff808080;
        window_attributes.colormap = XCreateColormap(N(private).x11.display, N(private).x11.root_window, N(private).x11.gl_visual_info->visual, AllocNone);
		window_attributes.event_mask = StructureNotifyMask | KeyPressMask | KeyReleaseMask | FocusChangeMask | PointerMotionMask | ButtonPressMask | ButtonReleaseMask;

        N(private).x11.window = XCreateWindow (N(private).x11.display, N(private).x11.root_window, 0, 0, 1280, 720, 0, N(private).x11.gl_visual_info->depth, InputOutput, N(private).x11.gl_visual_info->visual, CWColormap | CWBackPixel | CWEventMask, &window_attributes);
		N(public).window.is_fullscreen = false;
        XMapWindow (N(private).x11.display, N(private).x11.window);
		glXMakeCurrent (N(private).x11.display, N(private).x11.window, N(private).x11.gl_context);

		glViewport (0, 0, N(public).window.w, N(public).window.h);
		glMatrixMode (GL_PROJECTION);
		glLoadIdentity ();
		glOrtho (0, N(public).window.w-1, 0, N(public).window.h-1, -1, 1);

		XChangeProperty (N(private).x11.display, N(private).x11.window, XA_WM_NAME, XA_STRING, 8, 0, (const char unsigned*)window_title, strlen (window_title));

		// { // Maximize
		// 	XEvent e = {0};
		// 	e.type = ClientMessage;
		// 	e.xclient.window = N(private).x11.window;
		// 	e.xclient.message_type = XInternAtom(N(private).x11.display, "_NET_WM_STATE", False);
		// 	e.xclient.format = 32;
		// 	e.xclient.data.l[0] = _NET_WM_STATE_ADD;
		// 	e.xclient.data.l[1] = XInternAtom(N(private).x11.display, "_NET_WM_STATE_MAXIMIZED_HORZ", False);
		// 	e.xclient.data.l[2] = XInternAtom(N(private).x11.display, "_NET_WM_STATE_MAXIMIZED_VERT", False);

		// 	XSendEvent(N(private).x11.display, N(private).x11.root_window, False, SubstructureNotifyMask, &e);
		// }

		N(private).x11.WM_DELETE_WINDOW = XInternAtom(N(private).x11.display, "WM_DELETE_WINDOW", False);
        XSetWMProtocols(N(private).x11.display, N(private).x11.window, &N(private).x11.WM_DELETE_WINDOW, 1);

		// Set up drawing
		{
			struct {
				Window root_window;
				int x, y;
				unsigned int border_width, depth;
			} dummy;
			XGetGeometry (N(private).x11.display, N(private).x11.window, &dummy.root_window, &dummy.x, &dummy.y, &N(public).window.width, &N(public).window.height, &dummy.border_width, &dummy.depth);
		}
#ifdef OSINTERFACE_FRAME_BUFFER_SCALED
		N(WindowFrameBufferCalculateScale) ();
#else
		N(private).frame_buffer.width = N(public).window.width;
		N(private).frame_buffer.height = N(public).window.height;
		N(private).frame_buffer.pixels = malloc (N(private).frame_buffer.width * N(private).frame_buffer.height * 4);
#endif

		XkbSetDetectableAutoRepeat(N(private).x11.display, True, 0); // No key repeat when holding key down

		{
			struct {
				Window root_window, child_window;
				int x, y;
				unsigned int mask;
			} dummy;
			XQueryPointer(N(private).x11.display, N(private).x11.window, &dummy.root_window, &dummy.child_window, &dummy.x, &dummy.y, &N(public).mouse.p.x, &N(public).mouse.p.y, &dummy.mask);
			N(public).mouse.p.y = N(public).window.h - 1 - N(public).mouse.p.y;
		}

        XFlush(N(private).x11.display);
    }

	return true;
}
#endif

#ifdef OSINTERFACE_FRAME_BUFFER_SCALED
void N(SetWindowFrameBuffer) (uint32_t *pixels, int width, int height) {
	N(private).frame_buffer.has_been_set = true;
	N(private).frame_buffer.pixels = pixels;
	N(private).frame_buffer.width  = width;
	N(private).frame_buffer.height = height;
	N(WindowFrameBufferCalculateScale) ();
}

void N(WindowPositionToScaledFrameBufferPosition) (int windowx, int windowy, int *outx, int *outy) {
	*outx = (windowx - N(private).frame_buffer.left) / N(private).frame_buffer.scale;
	*outy = (windowy - N(private).frame_buffer.bottom) / N(private).frame_buffer.scale;
}

void N(ScaledFrameBufferPositionToWindowPosition) (int framex, int framey, int *outx, int *outy) {
	*outx = framex * N(private).frame_buffer.scale + N(private).frame_buffer.left + N(private).frame_buffer.scale/2;
	*outy = framey * N(private).frame_buffer.scale + N(private).frame_buffer.bottom + N(private).frame_buffer.scale/2;
}
#endif

N(event_t) N(NextEvent) () {
	XEvent e;
	N(event_t) event;
	event.type = N(EVENT_NULL);
	bool key_is_down = true;
	if (XPending(N(private).x11.display)) {
		XNextEvent(N(private).x11.display, &e);
		switch (e.type) {
			case DestroyNotify: {
				event.type = N(EVENT_QUIT);
			} break;

			case ClientMessage: {
				XClientMessageEvent* ev = (XClientMessageEvent*)&e;
				if((Atom)ev->data.l[0] == N(private).x11.WM_DELETE_WINDOW) {
					event.type = N(EVENT_QUIT);
				}
			} break;

			case FocusOut: {
				memset(N(public).keyboard, false, sizeof(N(public).keyboard));
				N(public).mouse.buttons = 0;
			} break;

			case KeyRelease:
				event.type = N(EVENT_KEY_RELEASE);
				key_is_down = false;
			case KeyPress: { // key_is_down is initialized to true before the switch statement
				int symbol = XLookupKeysym(&e.xkey, 0);
				if (N(public).keyboard[(uint8_t)symbol] != key_is_down) { // Prevent key repeats from sending events. Should only happen with key release anyway.
					N(public).keyboard[(uint8_t)symbol] = key_is_down;
					event.key = (uint8_t)symbol;
					if(key_is_down) {
						event.type = N(EVENT_KEY_PRESS);
					}
				}
			} break;

			case ConfigureNotify: {
				XConfigureEvent* ev = (XConfigureEvent*)&e;
				if (ev->width != N(public).window.w || ev->height != N(public).window.h) {
					N(public).window.w = event.width = ev->width;
					N(public).window.h = event.height = ev->height;

					struct {
						Window root_window, child_window;
						int x, y;
						unsigned int mask;
					} dummy;
					XQueryPointer(N(private).x11.display, N(private).x11.window, &dummy.root_window, &dummy.child_window, &dummy.x, &dummy.y, &N(public).mouse.p.x, &N(public).mouse.p.y, &dummy.mask);
					N(public).mouse.p.y = N(public).window.h - 1 - N(public).mouse.p.y;

#ifdef OSINTERFACE_LINUX_USE_OPENGL
					glViewport (0, 0, N(public).window.w, N(public).window.h);
					glMatrixMode (GL_PROJECTION);
					glLoadIdentity ();
					glOrtho (0, N(public).window.w-1, 0, N(public).window.h-1, -1, 1);
#endif

#ifdef OSINTERFACE_FRAME_BUFFER_SCALED
					N(WindowFrameBufferCalculateScale) ();
#else
					N(private).frame_buffer.width = N(public).window.w;
					N(private).frame_buffer.height = N(public).window.h;
					N(private).frame_buffer.pixels = (uint32_t*)realloc (N(private).frame_buffer.pixels, N(private).frame_buffer.width * N(private).frame_buffer.height * sizeof (N(private).frame_buffer.pixels[0]));
	#ifdef OSINTERFACE_LINUX_USE_XRENDER
					N(private).x11.window_image = *XCreateImage (N(private).x11.display, N(private).x11.visual_info.visual, N(private).x11.visual_info.depth, ZPixmap, 0, (char*)N(private).frame_buffer.pixels, N(public).window.w, N(public).window.h, 32, 0);
	#elif defined OSINTERFACE_LINUX_USE_OPENGL
					glMatrixMode (GL_MODELVIEW);
					glLoadIdentity ();
					glPixelZoom (1, 1);
					glRasterPos2i (0, 0);
	#endif
#endif
				}
			} break;

			case MotionNotify: {
				XMotionEvent *ev = (XMotionEvent*)&e;
				event.type = N(EVENT_MOUSE_MOVE);
				event.previous_position.x = N(public).mouse.p.x;
				event.previous_position.y = N(public).mouse.p.y;
				N(public).mouse.p.x = event.new_position.x = ev->x;
				N(public).mouse.p.y = event.new_position.y = N(public).window.h - 1 - ev->y;
			} break;

			case ButtonRelease:
				event.type = N(EVENT_MOUSE_BUTTON_RELEASE);
				key_is_down = false;
			case ButtonPress: { // key_is_down is initialized to true before the switch statement
				if (key_is_down)
					event.type = N(EVENT_MOUSE_BUTTON_PRESS);
				XButtonEvent *ev = (XButtonEvent*)&e;
				event.button.p.x = ev->x;
				event.button.p.y = N(public).window.h - 1 - ev->y;
				switch (ev->button) {
					case Button1: event.button.button = N(MOUSE_LEFT); break;
					case Button2: event.button.button = N(MOUSE_MIDDLE); break;
					case Button3: event.button.button = N(MOUSE_RIGHT); break;
					case Button4: event.button.button = N(MOUSE_X1); break;
					case Button5: event.button.button = N(MOUSE_X2); break;
				}
			} break;
		}
	}
    return event;
}

void N(WindowSize) (int width, int height) {
	XMoveResizeWindow (N(private).x11.display, N(private).x11.window, 0, 0, width, height);
}

void N(Maximize) (bool maximize) {
	N(public).window.is_fullscreen = false;
	
	if (maximize) {
		XEvent e = {0};
		e.type = ClientMessage;
		e.xclient.window = N(private).x11.window;
		e.xclient.message_type = XInternAtom(N(private).x11.display, "_NET_WM_STATE", False);
		e.xclient.format = 32;
		e.xclient.data.l[0] = 0;
		e.xclient.data.l[1] = XInternAtom(N(private).x11.display, "_NET_WM_STATE_MAXIMIZED_HORZ", False);
		e.xclient.data.l[2] = XInternAtom(N(private).x11.display, "_NET_WM_STATE_MAXIMIZED_VERT", False);

		XSendEvent(N(private).x11.display, N(private).x11.root_window, False, SubstructureNotifyMask, &e);
	}
	else {
		N(WindowSize) (N(public).window.w, N(public).window.h);
	}
}

#ifdef OSINTERFACE_LINUX_USE_XRENDER
void N(DrawScreen) () {
	// XPutImage(N(private).x11.display, N(private).x11.window, N(private).x11.graphics_context, &N(private).x11.window_image, 0, 0, N(private).frame_buffer.left, N(private).frame_buffer.bottom, N(private).frame_buffer.width, N(private).frame_buffer.height);

	XPutImage (N(private).x11.display, N(private).x11.pixmap, N(private).x11.graphics_context, &N(private).x11.window_image, 0, 0, 0, 0, 320, 240);

	XRenderPictFormat *format = XRenderFindVisualFormat (N(private).x11.display, DefaultVisual (N(private).x11.display, 0));
	XRenderPictureAttributes attributes;
	attributes.clip_mask = None;
	Picture src_pict = XRenderCreatePicture (N(private).x11.display, N(private).x11.pixmap, format, 0, &attributes);
	Picture dst_pict = XRenderCreatePicture (N(private).x11.display, N(private).x11.window, format, 0, &attributes);

	XTransform transform_matrix = {{
	{XDoubleToFixed(1.0/N(private).frame_buffer.scale), XDoubleToFixed(0), XDoubleToFixed(0)},
	{XDoubleToFixed(0), -XDoubleToFixed(1.0/N(private).frame_buffer.scale), XDoubleToFixed(0)},
	{XDoubleToFixed(0), XDoubleToFixed(0), XDoubleToFixed(1.0)}  
	}};
	XRenderSetPictureTransform(N(private).x11.display, src_pict, &transform_matrix);

	XRenderComposite(N(private).x11.display, PictOpSrc, src_pict, 0, dst_pict, 
					0, 0, 0, 0, N(private).frame_buffer.left, N(private).frame_buffer.bottom,
					N(public).window.w, N(public).window.h);

	XRenderFreePicture (N(private).x11.display, src_pict);
	XRenderFreePicture (N(private).x11.display, dst_pict);
}
#elif defined OSINTERFACE_LINUX_USE_OPENGL
void N(DrawScreen) () {
	// XPutImage(N(private).x11.display, N(private).x11.window, N(private).x11.graphics_context, &N(private).x11.window_image, 0, 0, N(private).frame_buffer.left, N(private).frame_buffer.bottom, N(private).frame_buffer.width, N(private).frame_buffer.height);
	// XClearWindow (N(private).x11.display, N(private).x11.window);
	glClearColor (0.314, 0.314, 0.314, 1);
	glClear (GL_COLOR_BUFFER_BIT | GL_DEPTH_BUFFER_BIT);

	glDrawPixels (N(private).frame_buffer.width, N(private).frame_buffer.height, GL_BGRA, GL_UNSIGNED_BYTE, N(private).frame_buffer.pixels);

	// glBegin (GL_QUADS);
	// glVertex2i (left, bottom);
	// glVertex2i (right, bottom);
	// glVertex2i (right, top);
	// glVertex2i (left, top);
	// glEnd ();

	glXSwapBuffers (N(private).x11.display, N(private).x11.window);
}
#endif

#ifdef OSINTERFACE_LINUX_USE_XRENDER
void N(WaitForScreenRefresh) () { XFlush (N(private).x11.display); } // Ensure that last frame has been presented
#elif defined OSINTERFACE_LINUX_USE_OPENGL
void N(WaitForScreenRefresh) () {
	glFinish ();
} // Ensure that last frame has been presented
#endif

void N(Fullscreen) (bool fullscreen) {
	if (fullscreen) {
		N(public).window.is_fullscreen = true;

		Atom wm_state = XInternAtom (N(private).x11.display, "_NET_WM_STATE", False);
		Atom fullscreen = XInternAtom (N(private).x11.display, "_NET_WM_STATE_FULLSCREEN", False);

		XEvent e;
		memset(&e, 0, sizeof(e));
		e.type = ClientMessage;
		e.xclient.window = N(private).x11.window;
		e.xclient.message_type = wm_state;
		e.xclient.format = 32;
		e.xclient.data.l[0] = 1;
		e.xclient.data.l[1] = fullscreen;
		e.xclient.data.l[2] = 0;

		XSendEvent (N(private).x11.display, N(private).x11.root_window, False, SubstructureRedirectMask | SubstructureNotifyMask, &e);
	} else {
		N(public).window.is_fullscreen = false;

		Atom wm_state = XInternAtom (N(private).x11.display, "_NET_WM_STATE", False);
		Atom fullscreen = XInternAtom (N(private).x11.display, "_NET_WM_STATE_FULLSCREEN", False);

		XEvent e;
		memset(&e, 0, sizeof(e));
		e.type = ClientMessage;
		e.xclient.window = N(private).x11.window;
		e.xclient.message_type = wm_state;
		e.xclient.format = 32;
		e.xclient.data.l[0] = 0;
		e.xclient.data.l[1] = fullscreen;
		e.xclient.data.l[2] = 0;

		XSendEvent (N(private).x11.display, N(private).x11.root_window, False, SubstructureRedirectMask | SubstructureNotifyMask, &e);
	}
}

int64_t N(uTime) () {
	timespec_t t;
	clock_gettime (CLOCK_MONOTONIC, &t);
	int64_t ticks = t.tv_sec * 1000000000 + t.tv_nsec;
	return ticks / 1000;
}

void N(uSleepEfficient) (int64_t microseconds) {
	if (microseconds > 0) usleep (microseconds);
}

void N(uSleepPrecise) (int64_t microseconds) {
	N(uSleepEfficient) (microseconds);
}

void N(ShowCursor) () {
	XFixesShowCursor (N(private).x11.display, N(private).x11.window);
}

void N(HideCursor) () {
	XFixesHideCursor (N(private).x11.display, N(private).x11.window);
}

#endif

#ifdef OSINTERFACE_FRAME_BUFFER_SCALED
void N(WindowFrameBufferCalculateScale) () {
	if (!N(private).frame_buffer.has_been_set) return;
	int scalex, scaley;
	scalex = N(public).window.w / N(private).frame_buffer.width;
	scaley = N(public).window.h / N(private).frame_buffer.height;
	N(private).frame_buffer.scale  = Min (scalex, scaley);
	N(private).frame_buffer.left   = (N(public).window.w - N(private).frame_buffer.width  * N(private).frame_buffer.scale) / 2;
	N(private).frame_buffer.bottom = (N(public).window.h - N(private).frame_buffer.height * N(private).frame_buffer.scale) / 2;
	#ifdef OSINTERFACE_LINUX_USE_OPENGL
	glMatrixMode (GL_MODELVIEW);
	glLoadIdentity ();
	glPixelZoom (N(private).frame_buffer.scale, N(private).frame_buffer.scale);
	glRasterPos2i (N(private).frame_buffer.left, N(private).frame_buffer.bottom);
	#endif
}
#endif

#pragma pop_macro ("NAMESPACE")
#pragma pop_macro ("C")
#pragma pop_macro ("C_")
#pragma pop_macro ("N")
#pragma pop_macro ("Min")
#pragma pop_macro ("Max")
