#pragma once

#include <stdio.h>

#define PRINT_ERROR(a, args...) printf("ERROR %s() %s Line %d: " a "\n", __FUNCTION__, __FILE__, __LINE__, ##args);

// Pass this the expression to evaluate and a number to return on program exit.
#define EXIT_IF(a, b) do { if(a) { PRINT_ERROR(#a); exit(b); } } while(0)