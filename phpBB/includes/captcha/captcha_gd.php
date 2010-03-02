<?php
/** 
*
* @package VC
* @version $Id$
* @copyright (c) 2006 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* Based on PHP-Class hn_captcha Version 1.3, released 11-Apr-2006
* Original Author - Horst Nogajski, horst@nogajski.de
*
* @package VC
*/
class captcha
{
	var $width = 360;
	var $height = 96;

	function execute($code)
	{
		global $config;
		$stats = gd_info();

		$bundled = (substr($stats['GD Version'], 0, 7) === 'bundled') ? true : false;

		preg_match('/[\\d.]+/', $stats['GD Version'], $version);
		$gd_version = (version_compare($version[0], '2.0.1', '>=')) ? 2 : 1;

		// create the image, stay compat with older versions of GD
		if ($gd_version === 2)
		{
			$func1 = 'imagecreatetruecolor';
			$func2 = 'imagecolorallocate';
		}
		else
		{
			$func1 = 'imagecreate';
			$func2 = 'imagecolorclosest';
		}

		$image = $func1($this->width, $this->height);

		if ($bundled)
		{
			imageantialias($image, true);
		}

		// set background color
		$back =  imagecolorallocate($image, mt_rand(224, 255), mt_rand(224, 255), mt_rand(224, 255));
		imagefilledrectangle($image, 0, 0, $this->width, $this->height, $back);

		// allocates the 216 websafe color palette to the image
		if ($gd_version === 1)
		{
			for ($r = 0; $r <= 255; $r += 51)
			{
				for ($g = 0; $g <= 255; $g += 51)
				{
					for ($b = 0; $b <= 255; $b += 51)
					{
						imagecolorallocate($image, $r, $g, $b);
					}
				}
			}
		}

		// fill with noise or grid
		if ($config['captcha_gd_noise'])
		{
			// random characters in background with random position, angle, color
			for ($i = 0 ; $i < 72; $i++)
			{
				$size	= mt_rand(8, 23);
				$angle	= mt_rand(0, 360);
				$x		= mt_rand(0, 360);
				$y		= mt_rand(0, (int)($this->height - ($size / 5)));
				$color	= $func2($image, mt_rand(160, 224), mt_rand(160, 224), mt_rand(160, 224));
				$text	= chr(mt_rand(45, 250));
				imagettftext($image, $size, $angle, $x, $y, $color, $this->get_font(), $text);
			}
		}
		else
		{
			// generate grid
			for ($i = 0; $i < $this->width; $i += 13)
			{
				$color	= $func2($image, mt_rand(160, 224), mt_rand(160, 224), mt_rand(160, 224));
				imageline($image, $i, 0, $i, $this->height, $color);
			}

			for ($i = 0; $i < $this->height; $i += 11)
			{
				$color	= $func2($image, mt_rand(160, 224), mt_rand(160, 224), mt_rand(160, 224));
				imageline($image, 0, $i, $this->width, $i, $color);
			}
		}

		$len = strlen($code);

		for ($i = 0, $x = mt_rand(20, 40); $i < $len; $i++)
		{
			$text	= strtoupper($code[$i]);
			$angle	= mt_rand(-30, 30);
			$size	= mt_rand(20, 40);
			$y		= mt_rand((int)($size * 1.5), (int)($this->height - ($size / 7)));

			$color	= $func2($image, mt_rand(0, 127), mt_rand(0, 127), mt_rand(0, 127));
			$shadow = $func2($image, mt_rand(127, 254), mt_rand(127, 254), mt_rand(127, 254));
			$font = $this->get_font();

			imagettftext($image, $size, $angle, $x + (int)($size / 15), $y, $shadow, $font, $text);
			imagettftext($image, $size, $angle, $x, $y - (int)($size / 15), $color, $font, $text);

			$x += $size + 4;
		}

		// Output image
		header('Content-Type: image/png');
		header('Cache-control: no-cache, no-store');
		imagepng($image);
		imagedestroy($image);
	}

	function get_font()
	{
		static $fonts = array();
	
		if (!sizeof($fonts))
		{
			global $phpbb_root_path;
	
			$dr = opendir($phpbb_root_path . 'includes/captcha/fonts');
			while (false !== ($entry = readdir($dr)))
			{
				if (strtolower(pathinfo($entry, PATHINFO_EXTENSION)) == 'ttf')
				{
					$fonts[] = $phpbb_root_path . 'includes/captcha/fonts/' . $entry;
				}
			}
			closedir($dr);
		}

		return $fonts[array_rand($fonts)];
	}
}

?>