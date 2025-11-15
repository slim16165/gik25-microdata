<?php
/**
 * Local Image Upscaler Service
 *
 * Provides local image upscaling using GD Library or ImageMagick
 * instead of WordLift cloud service.
 *
 * @package Wordlift
 * @since 3.40.0
 */

class Wordlift_Local_Image_Upscaler {

	/**
	 * Upscale an image using local image processing libraries.
	 *
	 * @param string $image_path Path to the image file.
	 * @param int    $scale_factor Scale factor (2 = 2x upscale, 4 = 4x upscale). Default 2.
	 * @return string|WP_Error Binary image data on success, WP_Error on failure.
	 */
	public static function upscale_image( $image_path, $scale_factor = 2 ) {
		if ( ! file_exists( $image_path ) ) {
			return new WP_Error( 'file_not_found', 'Image file not found.' );
		}

		// Try ImageMagick first (better quality), then GD
		if ( extension_loaded( 'imagick' ) && class_exists( 'Imagick' ) ) {
			return self::upscale_with_imagick( $image_path, $scale_factor );
		} elseif ( extension_loaded( 'gd' ) && function_exists( 'imagecreatefromjpeg' ) ) {
			return self::upscale_with_gd( $image_path, $scale_factor );
		} else {
			return new WP_Error( 'no_image_lib', 'No image processing library available (GD or ImageMagick required).' );
		}
	}

	/**
	 * Upscale image using ImageMagick (better quality).
	 *
	 * @param string $image_path Path to the image file.
	 * @param int    $scale_factor Scale factor.
	 * @return string|WP_Error Binary image data on success, WP_Error on failure.
	 */
	private static function upscale_with_imagick( $image_path, $scale_factor ) {
		try {
			$imagick = new Imagick( $image_path );
			
			// Get original dimensions
			$width = $imagick->getImageWidth();
			$height = $imagick->getImageHeight();
			
			// Calculate new dimensions
			$new_width = $width * $scale_factor;
			$new_height = $height * $scale_factor;
			
			// Use Lanczos filter for better quality upscaling
			$imagick->resizeImage( $new_width, $new_height, Imagick::FILTER_LANCZOS, 1, true );
			
			// Apply unsharp mask to enhance details
			$imagick->unsharpMaskImage( 0.5, 0.5, 0.8, 0.05 );
			
			// Get image format
			$format = $imagick->getImageFormat();
			if ( $format === 'JPEG' || $format === 'JPG' ) {
				$imagick->setImageFormat( 'jpeg' );
				$imagick->setImageCompressionQuality( 90 );
			}
			
			// Get binary data
			$image_data = $imagick->getImageBlob();
			$imagick->clear();
			$imagick->destroy();
			
			return $image_data;
		} catch ( Exception $e ) {
			return new WP_Error( 'imagick_error', 'ImageMagick error: ' . $e->getMessage() );
		}
	}

	/**
	 * Upscale image using GD Library (fallback).
	 *
	 * @param string $image_path Path to the image file.
	 * @param int    $scale_factor Scale factor.
	 * @return string|WP_Error Binary image data on success, WP_Error on failure.
	 */
	private static function upscale_with_gd( $image_path, $scale_factor ) {
		$image_info = getimagesize( $image_path );
		if ( ! $image_info ) {
			return new WP_Error( 'invalid_image', 'Invalid image file.' );
		}

		$mime_type = $image_info['mime'];
		$width = $image_info[0];
		$height = $image_info[1];

		// Load image based on type
		switch ( $mime_type ) {
			case 'image/jpeg':
				$source = imagecreatefromjpeg( $image_path );
				break;
			case 'image/png':
				$source = imagecreatefrompng( $image_path );
				break;
			case 'image/gif':
				$source = imagecreatefromgif( $image_path );
				break;
			default:
				return new WP_Error( 'unsupported_format', 'Unsupported image format.' );
		}

		if ( ! $source ) {
			return new WP_Error( 'gd_error', 'Failed to load image with GD.' );
		}

		// Calculate new dimensions
		$new_width = $width * $scale_factor;
		$new_height = $height * $scale_factor;

		// Create new image with better interpolation
		$destination = imagecreatetruecolor( $new_width, $new_height );
		
		// Preserve transparency for PNG/GIF
		if ( $mime_type === 'image/png' || $mime_type === 'image/gif' ) {
			imagealphablending( $destination, false );
			imagesavealpha( $destination, true );
			$transparent = imagecolorallocatealpha( $destination, 255, 255, 255, 127 );
			imagefill( $destination, 0, 0, $transparent );
		}

		// Use better interpolation (bicubic-like)
		imagecopyresampled( $destination, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height );

		// Output to buffer
		ob_start();
		switch ( $mime_type ) {
			case 'image/jpeg':
				imagejpeg( $destination, null, 90 );
				break;
			case 'image/png':
				imagepng( $destination, null, 9 );
				break;
			case 'image/gif':
				imagegif( $destination );
				break;
		}
		$image_data = ob_get_clean();

		imagedestroy( $source );
		imagedestroy( $destination );

		return $image_data;
	}
}

