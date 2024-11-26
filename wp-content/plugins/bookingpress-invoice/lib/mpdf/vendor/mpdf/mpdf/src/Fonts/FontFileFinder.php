<?php

namespace Mpdf\Fonts;

class FontFileFinder
{

	private $directories;

	public function __construct($directories)
	{
		$this->setDirectories($directories);
	}

	public function setDirectories($directories)
	{
		if (!is_array($directories)) {
			$directories = [$directories];
		}

		$this->directories = $directories;
	}

	public function findFontFile($name)
	{
		global $bookingpress_invoice, $BookingPress;
		foreach ($this->directories as $directory) {
			
			if( $directory != BOOKINGPRESS_INVOICE_FONT_DIR ){
				$default_dir = $directory;
			}

			$filename = $directory . '/' . $name;
			if (file_exists($filename)) {
				return $filename;
			} else {
				$fonts_arr = $bookingpress_invoice->bookingpress_invoice_get_fonts_arr();
				/* $bookingpress_selected_font = $BookingPress->bookingpress_get_settings( 'bookingpress_selected_font', 'invoice_setting' );
				$font_lists = $fonts_arr[ $bookingpress_selected_font ];

				if( !function_exists('WP_Filesystem' ) ){
					require_once(ABSPATH . 'wp-admin/includes/file.php');
				}				 */
				$font_name = array();

				foreach( $fonts_arr as $k => $font_arr ){
					foreach( $font_arr as $k1 => $font_file ){
						if( is_array( $font_file ) && in_array( $name, $font_file ) ){
							$font_name = $font_file;
							break;
						}
					}
				}
			}
		}
		
		if( !empty( $font_name ) ){
			if( is_writable( BOOKINGPRESS_INVOICE_FONT_DIR ) ){
				$response = $bookingpress_invoice->bookingpress_import_invoice_fonts( $font_name );
				$response = json_decode( $response, true );
				if( 'success' == $response['variant'] ){
					return BOOKINGPRESS_INVOICE_FONT_DIR . '/' . $name;
				} else {
					if( file_exists( BOOKINGPRESS_INVOICE_FONT_DIR . '/arial.ttf' ) ){
						return BOOKINGPRESS_INVOICE_FONT_DIR . '/arial.ttf';
					} else {
						return $default_dir . '/arial.ttf';
					}
				}
			} else {
				if( file_exists( BOOKINGPRESS_INVOICE_FONT_DIR . '/arial.ttf' ) ){
					return BOOKINGPRESS_INVOICE_FONT_DIR . '/arial.ttf';
				} else if( file_exists( $default_dir . '/arial.ttf' ) ){
					return $default_dir . '/arial.ttf';
				}
			}
		}

		throw new \Mpdf\MpdfException(sprintf('Cannot find TTF TrueType font file "%s" in configured font directories.', $name));
	}
}
