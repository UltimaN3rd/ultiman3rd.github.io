#pragma once

#include "PRINT_ERROR.h"
#include <stdio.h>
#include <stdint.h>
#include <stdlib.h>

bool LoadBMP(int *width, int *height, uint32_t **pixels, const char *filename) {
	bool return_value = false;

	uint32_t file_size;
	uint32_t image_data_address;
	uint32_t bitmap_header_size;
	int32_t w;
	int32_t h;
	uint32_t pixel_count;
	uint16_t bit_depth;
	uint8_t byte_depth;
	uint32_t *p;

	FILE *file;
	file = fopen(filename, "rb");
	if (!file) {
		PRINT_ERROR("(%s) Failed to open file\n", filename);
		return false;
	}

	uint8_t byte;
	if (!fread(&byte, 1, 1, file) || byte != 'B') {
		PRINT_ERROR("(%s) First byte of file is not \"B\"\n", filename);
		goto LoadBMPClose;
	}

	if (!fread(&byte, 1, 1, file) || byte != 'M') {
		PRINT_ERROR("(%s) Second byte of file is not \"M\"\n", filename);
		goto LoadBMPClose;
	}

	fread(&file_size, 4, 1, file);
	fseek(file, 10, SEEK_SET);
	fread(&image_data_address, 4, 1, file);
	fread(&bitmap_header_size, 4, 1, file);
	fread(&w, 4, 1, file);
	fread(&h, 4, 1, file);
	pixel_count = w * h;
	fseek(file, 28, SEEK_SET);
	fread(&bit_depth, 2, 1, file);

	if (bit_depth != 32) {
		PRINT_ERROR("(%s) Bit depth expected %d is %d\n", filename, 32, bit_depth);
		goto LoadBMPClose;
	}

	byte_depth = bit_depth / 8;
	//printf("file size: %d\nimage data address: %d\nbitmap header size: %d\nw: %d\nh: %d\nbit depth: %d\n", file_size, image_data_address, bitmap_header_size, w, h, bit_depth);
	p = (uint32_t*)malloc(pixel_count * byte_depth);
	if(!p) {
		PRINT_ERROR("(%s) Failed to allocate %d pixels.\n", filename, pixel_count);
		goto LoadBMPClose;
	}

	fseek(file, image_data_address, SEEK_SET);
	uint32_t pixels_read = fread(p, byte_depth, pixel_count, file);
	//printf("Read %d pixels\n", pixels_read);
	if(pixels_read != pixel_count) {
		PRINT_ERROR("(%s) Read pixel count incorrect. Is %d expected %d\n", filename, pixels_read, pixel_count);
		free (pixels);
		goto LoadBMPClose;
	}

	*width = w;
	*height = h;
	*pixels = p;
	return_value = true;

LoadBMPClose:
	fclose(file);
	
	return return_value;
}