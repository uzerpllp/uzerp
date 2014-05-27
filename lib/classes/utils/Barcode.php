<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class Barcode
{
	
	protected $version='$Revision: 1.4 $';

	function get_path($path_type, $args, &$errors = array())
	{

		$response = Barcode::create($args, $errors);
		
		if ($response !== FALSE)
		{
			return $response[$path_type . '_path'];
		}
		
		return $response;
		
	}
	
	function create($args, &$errors = array())
	{
		
		// if args is not an array we can assume it's the code
		if (!is_array($args))
		{
			$args = array('code' => $args);
		}
	
		// defaults
		$defaults = array(
			'height'		=> 25,
			'char_width'	=> 2,
			'type'			=> 'code39',
			'code'			=> ''
		);
		
		// merge defaults with passed args
		$args = array_merge($defaults, $args);
				
		foreach ($args as $key => $value)
		{
			
			// set the option to it's own variable
			$$key = $value;
			
			if (empty($value))
			{
				$errors[] = 'No ' . $key . ' set';
				return FALSE;
			}
		
		}
		
		$output_dir = DATA_ROOT . 'barcodes/';
		
		// if the directory doesn't exist...
		if (!file_exists($output_dir))
		{
			
			// ...create it recursively
			if (!mkdir($output_dir, 0777, TRUE))
			{
				$errors[] = 'Cannot create barcode directory';
				return FALSE;
			}
			
		}
		
		// build the filepath
		$filename = $type . '_' . $height . '_' . $char_width . '_' . $code . '.gif';
		$filepath = $output_dir . $filename;
		
		// check if it already exists, return it if so
		if (file_exists($filepath))
		{
			return array(
				'http_path' => SERVER_ROOT . '/data/barcodes/' . $filename,
				'file_path' => $filepath
			);
		}
		
		// detect barcode width and set x position
		$width = ((strlen($code) + 2) * (12 * $char_width)) + (((strlen($code) + 1) * $char_width));
		$x = $width / 2;
		
		// set y position
		$y = $height / 2;
		
		// initiate image var
		$im	= imagecreatetruecolor($width, $height);
		
		// set colours
		$black = ImageColorAllocate($im, 0x00, 0x00, 0x00);
		$white = ImageColorAllocate($im, 0xff, 0xff, 0xff);
		
		// fill shape with white
		imagefilledrectangle($im, 0, 0, $width, $height, $white);
		
		// generate the barcode
		$data = PHPBarcode::gd($im, $black, $x, $y, 0, $type, array('code'=>$code), $char_width, $height);
		
		if (empty($data))
		{
			$errors[] = 'Error generating barcode';
			return FALSE;
			// error generating barcode		
		}
		
		
		if (!is_writable($output_dir))
		{
			$errors[] = 'Filepath not writable';
			return FALSE;
		}
		
		imagegif($im, $filepath);
		
		if (file_exists($filepath))
		{
					
			return array(
				'http_path' => SERVER_ROOT . '/data/barcodes/' . $filename,
				'file_path' => $filepath
			);
		
		}
		
	}

}

// end of barcode.php