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
* Main gd based captcha class
*
* Thanks to Robert Hetzler (Xore)
*
* @package VC
*/
class captcha
{
	/**
	* Create the image containing $code
	*/
	function execute($code, $policy)
	{
		$this->$policy(str_split($code));
	}

	/**
	* Send image and destroy
	*/
	function send_image(&$image)
	{
		header('Content-Type: image/png');
		header('Cache-control: no-cache, no-store');
		imagepng($image);
		imagedestroy($image);
	}

	/**
	*
	*/
	function wave_height($x, $y, $factor = 1, $tweak = 1)
	{
		return ((sin($x / (3 * $factor)) + sin($y / (3 * $factor))) * 10 * $tweak);
	}

	/**
	*
	*/
	function grid_height($x, $y, $factor = 1, $x_grid, $y_grid)
	{
		return ( (!($x % ($x_grid * $factor)) || !($y % ($y_grid * $factor))) ? 3 : 0);
	}

	/**
	*
	*/
	function draw_shape($type, $img, $x_min, $y_min, $x_max, $y_max, $color)
	{
		switch ($type)
		{
			case 'Square':
				imagefilledpolygon($img, array($x_min, $y_max, $x_min, $y_min, $x_max, $y_min, $x_max, $y_max), 4, $color);
			break;
			
			case 'TriangleUp':
				imagefilledpolygon($img, array($x_min, $y_max, ($x_min + $x_max) / 2, $y_min, $x_max, $y_max), 3, $color);
			break;

			case 'TriangleDown':
				imagefilledpolygon($img, array($x_min, $y_min, ($x_min + $x_max) / 2, $y_max, $x_max, $y_min), 3, $color);
			break;

			case 'Circle':			
				imagefilledellipse($img, ($x_min + $x_max) / 2, ($y_min + $y_max) / 2, $x_max - $x_min, $y_max - $y_min, $color);
			break;
		}
	}

	/**
	*
	*/
	function draw_pattern($seed, $img, $x_min, $y_min, $x_max, $y_max, $colors, $thickness = 1)
	{
		$x_size = ($x_max - $x_min) / 4;
		$y_size = ($y_max - $y_min) / 4;
		$bitmap = substr($seed, 16, 4);
		$numcolors = sizeof($colors) - 1;
		for ($y = 0; $y < 4; ++$y)
		{
			$map = hexdec(substr($bitmap, $y, 1));
			for ($x = 0; $x < 4; ++$x)
			{
				if ($map & (1 << $x))
				{
					$char = hexdec(substr($seed, ($y * 4) + $x, 1));
					if (!($char >> 2))
					{
						switch ($char & 3)
						{
							case 0:
								$shape = 'Circle';
							break;

							case 1:
								$shape = 'Square';
							break;

							case 2:
								$shape = 'TriangleUp';
							break;

							case 3:
								$shape = 'TriangleDown';
							break;
						}
						$this->draw_shape($shape, $img, $x_min + ($x * $x_size), $y_min + ($y * $y_size), $x_min + (($x + 1) * $x_size), $y_min + (($y + 1) * $y_size), $colors[array_rand($colors)]);
					}
				}
			}
		}

		$cells = array();
		for ($i = 0; $i < 6; ++$i)
		{
			$cells = hexdec(substr($seed, 20 + ($i * 2), 2));
			$x1 = $cells & 3;
			$cells = $cells >> 2;
			$y1 = $cells & 3;
			$cells = $cells >> 2;
			$x2 = $cells & 3;
			$cells = $cells >> 2;
			$y2 = $cells & 3;
			$x1_real = $x_min + (($x1 + 0.5) * $x_size);
			$y1_real = $y_min + (($y1 + 0.5) * $y_size);
			$x2_real = $x_min + (($x2 + 0.5) * $x_size);
			$y2_real = $y_min + (($y2 + 0.5) * $y_size);
			if ($thickness > 1)
			{
				imagesetthickness($img, $thickness);
			}
			imageline($img, $x1_real, $y1_real, $x2_real, $y2_real, $colors[array_rand($colors)]);
			if ($thickness > 1)
			{
				imagesetthickness($img, 1);
			}
		}
	}

	/**
	*
	*/
	function get_char_string()
	{
		static $chars = false;
		static $charcount = 0;
		if (!$chars)
		{
			$chars = array_merge(range('A', 'Z'), range('1', '9'));
		}
		$word   = '';
		for ($i = mt_rand(6, 8); $i > 0; --$i)
		{
			$word .= $chars[array_rand($chars)];
		}
		return $word;
	}

	/**
	* shape
	*/
	function policy_shape($code)
	{
		global $config, $user;
		// Generate image
		$img_x = 800;
		$img_y = 250;
		$img = imagecreate($img_x, $img_y);

		// Generate colors
		$background = imagecolorallocate($img, mt_rand(155, 255), mt_rand(155, 255), mt_rand(155, 255));
		imagefill($img, 0, 0, $background);

		$random = array();
		$fontcolors = array();
		for ($i = 0; $i < 15; $i++)
		{
			$random[$i] = imagecolorallocate($img, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
			$fontcolors[$i] = imagecolorallocate($img, mt_rand(0, 120), mt_rand(0, 120), mt_rand(0, 120));
		}

		// Generate code characters
		$code_num = sizeof($code);

		// Add some line noise
		if ($config['policy_shape_noise_line'])
		{
			$this->noise_line($img, 0, 0, $img_x, $img_y, $background, $fontcolors, $random);
		}

		$real = mt_rand(0, 3);
		$patterns = array('', '', '', '');
		for ($i = 32; $i > 0; --$i)
		{
			$patterns[$i & 3] .= str_pad(dechex(mt_rand(0, 65535)), 4, '0', STR_PAD_LEFT);
		}

		$char_class = $this->captcha_char('char_ttf');

		for ($i = 0; $i < 4; ++$i)
		{
			if ($i)
			{
				$y = 5 + ($i * 60);
				imageline($img, 550, $y, 650, $y, $fontcolors[0]);
			}
			$this->draw_pattern($patterns[$i], $img, 525, 10 + ($i * 60), 575, ($i + 1) * 60, $fontcolors);
			if ($i == $real)
			{
				$this->draw_pattern($patterns[$i], $img, 25, 25, 225, 225, $fontcolors, 3);
				for ($j = 0; $j < $code_num; ++$j)
				{
					$character = new $char_class($code[$j]);
					$character->drawchar(25, 600 + ($j * 25), 35 + ($i * 60), $img, $background, $fontcolors);
				}
			}
			else
			{
				$word = $this->get_char_string();
				for ($j = strlen($word) - 1; $j >= 0; --$j)
				{
					$character = new $char_class(substr($word, $j, 1));
					$character->drawchar(25, 600 + ($j * 25), 35 + ($i * 60), $img, $background, $fontcolors);
				}
			}
		}
		imagestring($img, 6, 250,  50, $user->lang['CAPTCHA_LINE_1'], $fontcolors[0]);
		imagestring($img, 6, 250, 100, $user->lang['CAPTCHA_LINE_2'], $fontcolors[0]);
		imagestring($img, 6, 250, 150, $user->lang['CAPTCHA_LINE_3'], $fontcolors[0]);
		imagestring($img, 6, 250, 200, $user->lang['CAPTCHA_LINE_4'], $fontcolors[0]);


		// Add some pixel noise
		if ($config['policy_shape_noise_pixel'])
		{
			$this->noise_pixel($img, 0, 0, $img_x, $img_y, $background, $fontcolors, $random, $config['policy_shape_noise_pixel']);
		}

		// Send image
		$this->send_image($img);
	}

	function policy_composite($code)
	{
		// Generate image
		$img_x = 800;
		$img_y = 250;
		$img = imagecreate($img_x, $img_y);

		$map = captcha_vectors();
		$fonts = captcha_load_ttf_fonts();

		// Generate basic colors
		$background = imagecolorallocate($img, mt_rand(155, 255), mt_rand(155, 255), mt_rand(155, 255));
		imagefill($img, 0, 0, $background);
		$black = imagecolorallocate($img, 0, 0, 0);
		$random = array();
		$fontcolors = array();
		for ($i = 0; $i < 15; ++$i)
		{
			$random[$i] = imagecolorallocate($img, mt_rand(120, 255), mt_rand(120, 255), mt_rand(120, 255));
		}
		$fontcolors[0] = imagecolorallocate($img, mt_rand(0, 120), mt_rand(0, 120), mt_rand(0, 120));

		// Specificy image portion dimensions.
		$count = sizeof($code);
		$cellsize = $img_x / $count;
		$y_range = min($cellsize, $img_y);
		$y_max = $img_y - $y_range;
		$y_off = array(); // consecutive vertical offset of characters
		$y_off[0] = mt_rand(0, $y_max);
		for ($i = 1; $i < $count; ++$i)
		{
			// each consective character can be as much as 50% closer to the top or bottom of the image as the previous
			$diff = mt_rand(-50, 50);
			if ($diff > 0)
			{
				$y_off[$i] = $y_off[$i - 1] + ((($y_max - $y_off[$i - 1]) * $diff) / 100);
			}
			else
			{
				$y_off[$i] = $y_off[$i - 1] * ((100 + $diff) / 100);
			}
		}


		$range = 0.1;

		$chars = array_merge(range('A', 'Z'), range('0', '9'));

		// draw some characters. if they're within the vector spec of the code character, color them differently
		for ($i = 0; $i < 5000; ++$i)
		{
			$degree = mt_rand(-30, 30);
			$x = mt_rand(0, $img_x - 1);
			$y = mt_rand(0, $img_y);
			$c = $chars[array_rand($chars)];
			$char = $x / $cellsize;
			$meta_x = ((($x % $cellsize) / $cellsize) * 1.5) - 0.25;
			$meta_y = (($img_y - $y) - $y_off[$char]) / $y_range;
			$font = $fonts[array_rand($fonts)];

			// distance check
			$distance = 2;
			$show = false;
			foreach ($map[$code[$char]] AS $vector)
			{
				switch ($vector[0])
				{
					case 'line':

						$c_theta = cos($vector[5]);
						$s_theta = sin($vector[5]);
						$bx = $meta_x - $vector[1]; 
						$by = $meta_y - $vector[2]; 
						$r = ($by * $c_theta) - ($bx * $s_theta);
						if ($r < $range && $r > -$range)
						{
							if (abs($c_theta) > abs($s_theta))
							{
								$s = (($bx + ($s_theta * $r)) / $c_theta);								
							}
							else
							{
								$s = (($by + ($c_theta * $r)) / $s_theta);
							}
							if ($s > -$range)
							{
								if ($s < 0)
								{
									$distance = min($distance, sqrt(pow($s, 2) + pow($r, 2)));
								}
								else if ($s < $vector[6])
								{
									$distance = min($distance, $r);
								}
								else if ($s < $vector[6] + $range)
								{
									$distance = min($distance, sqrt(pow($s - $vector[6], 2) + pow($r, 2)));
								}
							}
						}

					break;

					case 'arc':

						$dx = $meta_x - $vector[1];
						$dy = -($meta_y - $vector[2]); // because our arcs are upside-down
						if (abs($dx) > abs($dy))
						{
							$phi = rad2deg(atan(($dy * $vector[3]) / ($dx * $vector[4])));
							$phi += ($dx < 0) ? 180 : 360;
							$phi %= 360;
						}
						else
						{
							$phi = 90 - rad2deg(atan(($dx * $vector[4]) / ($dy * $vector[3])));
							$phi += ($dy < 0) ? 180 : 360;
							$phi %= 360;
						}

						$internal = $vector[6] > $vector[5]; // external wraps over the 360 point
						$low = $phi >= $vector[5]; // phi is above our low range
						$high = $phi <= $vector[6]; // phi is below our high range.
						if ($internal ? ($low && $high) : ($low || $high)) // if it wraps, it can only be one or the other
						{
							$radphi = deg2rad($phi);
							$px = cos($radphi) * 0.5 * $vector[3];
							$py = sin($radphi) * 0.5 * $vector[4];
							$distance = min($distance, sqrt(pow($px - $dx, 2) + pow($py - $dy, 2)));
						}

					break;
				}
			}

			if ($distance <= $range)
			{
				imagettftext($img, 10, $degree, $x, $y, $black, $font, $c);
			}
			else
			{
				imagettftext($img, 10, $degree, $x, $y, $random[$char], $font, $c);
			}

		}

		// Send image
		$this->send_image($img);
	}

	function policy_stencil($code)
	{
		// Generate image
		$img_x = 800;
		$img_y = 250;
		$img = imagecreate($img_x, $img_y);
		$stencil = imagecreate($img_x, $img_y);

		$map = captcha_vectors();
		$fonts = captcha_load_ttf_fonts();

		// Generate colors
		$white1 = imagecolorallocate($img, 255, 255, 255);
		$black2 = imagecolorallocate($stencil, 0, 0, 0);
		$black1 = imagecolorallocate($img, 0, 0, 0);
		$white2 = imagecolorallocate($stencil, 255, 255, 255);
		$channel = mt_rand(0,2);
		$c1 = (!($channel % 3)) ? 255 : 0;
		$c2 = (!(($channel + 1) % 3)) ? 255 : 0;
		$c3 = (!(($channel + 2) % 3)) ? 255 : 0;
		$primary = imagecolorallocate($img, $c1, $c2, $c3);
		$second = imagecolorallocate($img, $c2, $c3, $c1);
		$third = imagecolorallocate($img, $c3, $c1, $c2);

		imagefill($stencil, 0, 0, $black2);
		imagefill($img, 0, 0, $white1);

		$chars = array_merge(range('A', 'Z'), range('1', '9'));

		for ($i = 0; $i < 1000; ++$i)
		{
			$degree = mt_rand(-30, 30);
			$x = mt_rand(0, $img_x - 1);
			$y = mt_rand(0, $img_y);
			$c = $chars[array_rand($chars)];
			$font = $fonts[array_rand($fonts)];

			imagettftext($stencil, mt_rand(20, 30), $degree, $x, $y, $white2, $font, $c);
		}

		for ($i = 0; $i < 3; ++$i)
		{
			$degree1 = mt_rand(-30, 30);
			$degree2 = mt_rand(-30, 30);
			$x1 = mt_rand(0, $img_x - 1);
			$x2 = mt_rand(0, $img_x - 1);
			$y1 = mt_rand(0, $img_y);
			$y2 = mt_rand(0, $img_y);
			$c1 = $chars[array_rand($chars)];
			$c2 = $chars[array_rand($chars)];
			$font1 = $fonts[array_rand($fonts)];
			$font2 = $fonts[array_rand($fonts)];
			
			imagettftext($img, 150, $degree1, $x1, $y1, $second, $font1, $c1);
			imagettftext($img, 150, $degree2, $x2, $y2, $third, $font2, $c2);
		}

		$text = implode('', $code);
		$font = $fonts[array_rand($fonts)];
		imagettftext($img, 150, mt_rand(-5, 5), mt_rand(0, $img_x / 4), mt_rand(150, $img_y), $primary, $font, $text);

		for ($i = 0; $i < $img_x; ++$i)
		{
			for ($j = 0; $j < $img_y; ++$j)
			{
				// if the stencil is black, set the pixel in the image black
				if (!imagecolorat($stencil, $i, $j))
				{
					imagesetpixel($img, $i, $j, $black1);
				}
			}
		}

		// Send image
		$this->send_image($img);
	}

	function policy_cells($code)
	{
		// Generate image
		$img_x = 800;
		$img_y = 250;
		$img = imagecreate($img_x, $img_y);

		$fonts = captcha_load_ttf_fonts();

		$map = captcha_vectors();

		//
		// Generate colors
		//
		$white = imagecolorallocate($img, 255, 255, 255);
		$black = imagecolorallocate($img, 0, 0, 0);
		$colors = array(
			imagecolorallocate($img, 128, 0, 0),
			imagecolorallocate($img, 0, 128, 0),
			imagecolorallocate($img, 0, 0, 128),
			imagecolorallocate($img, 90, 90, 90)
		);
						
		$red = mt_rand(0,3);

		imagefill($img, 10, 10, $white);

		$offset1 = 50;
		$offset2 = 550;

		// draw the cell grid
		for ($i = 0; $i < 4; ++$i)
		{
			imageline($img, $offset1 + 40 + ($i * 40), 25, $offset1 + 40 + ($i * 40), 225, $black);
			imageline($img, $offset1, 65 + ($i * 40), $offset1 + 200, 65 + ($i * 40), $black);
			imageline($img, $offset2 + 40 + ($i * 40), 25, $offset2 + 40 + ($i * 40), 225, $black);
			imageline($img, $offset2, 65 + ($i * 40), $offset2 + 200, 65 + ($i * 40), $black);
		}

		$code_count = sizeof($code);

		$characters = array();
		$bitmap = array_fill(0, 25, 0);
		$cellorder = array();
		$xs = array();
		$ys = array();

		$chars = array_merge(range('A', 'Z'), range('1', '9'));

		for ($i = 0, $count = sizeof($chars) - 1; $i < 25; ++$i)
		{
			// Put a character in this cell
			$characters[$i] = $chars[array_rand($chars)];
			$xs[$i] = $offset1 + 20 + (($i % 5) * 40) + mt_rand(-13, 13);
			$ys[$i] = 45 + (intval($i / 5) * 40) + mt_rand(-13, 13);
			
			// Generate a pastel color
			$color = array(255, 255, 255);
			for ($j = 0; $j < 3; ++$j)
			{
				$color[array_rand($color)] -= 25;
			}
			$bg = imagecolorallocate($img, $color[0], $color[1], $color[2]);
			
			// fill the cells with the background colors
			imagefilledrectangle($img, $offset1 + 1 + (($i % 5) * 40), 26 + (intval($i / 5) * 40), $offset1 + 39 + (($i % 5) * 40), 64 + (intval($i / 5) * 40), $bg);
			imagefilledrectangle($img, $offset2 + 1 + (($i % 5) * 40), 26 + (intval($i / 5) * 40), $offset2 + 39 + (($i % 5) * 40), 64 + (intval($i / 5) * 40), $bg);
		}

		// assign the code to some cells
		$cellorder = range(0, 24);
		shuffle($cellorder);
		array_splice($cellorder, $code_count);
		for ($i = 0; $i < $code_count; ++$i)
		{
			$bitmap[$cellorder[$i]] = $i + 1;
			$characters[$cellorder[$i]] = $code[$i];
		}

		// assign the remaning cells to three separate colors
		$spares = array(array(), array(), array());
		for ($i = 0; $i < 25; ++$i)
		{
			if (!$bitmap[$i])
			{
				$spares[array_rand($spares)][] = $i;
			}
		}
		shuffle($spares[0]);
		shuffle($spares[1]);
		shuffle($spares[2]);

		// draw circles and lines for our fake entries
		for ($k = 0; $k < 3; ++$k )
		{
			for ($i = 0, $size = sizeof($spares[$k]); $i < $size; ++$i )
			{
				imagefilledellipse($img, $xs[$spares[$k][$i]], $ys[$spares[$k][$i]], 20, 20, $colors[($red + $k + 1) & 3]);
				if ($i)
				{
					imageline($img, $xs[$spares[$k][$i - 1]], $ys[$spares[$k][$i - 1]], $xs[$spares[$k][$i]], $ys[$spares[$k][$i]], $colors[($red + $k + 1) & 3]);
				}
			}
		}

		// draw lines for our real entries
		for ($i = 1; $i < $code_count; ++$i )
		{
			imageline($img, $xs[$cellorder[$i - 1]], $ys[$cellorder[$i - 1]], $xs[$cellorder[$i]], $ys[$cellorder[$i]], $colors[$red]);
		}

		// write characters into the text cells & put white dots (change this?) on our fake entry circles
		for ($i = 0; $i < 25; ++$i)
		{
			if (!$bitmap[$i])
			{
				imagefilledellipse($img, $xs[$i], $ys[$i], 9, 9, $white);
			}
			$level = intval($i / 5);
			$pos = $i % 5;
			imagettftext($img, 12, 0, $offset2 + 15 + ($pos * 40), 50 + ($level * 40), $black, $fonts[array_rand($fonts)], $characters[$i]);
		}

		// draw our real entries in
		for ($i = 0; $i < $code_count; ++$i )
		{
			imagefilledellipse($img, $xs[$cellorder[$i]], $ys[$cellorder[$i]], 20, 20, $colors[$red]);
			imagettftext($img, 12, 0, $xs[$cellorder[$i]] - 5, $ys[$cellorder[$i]] + 5, $white, $fonts[array_rand($fonts)], ($i + 1));
		}

		// Send image
		$this->send_image($img);
	}

	/**
	* entropy
	*/
	function policy_entropy($code)
	{
		global $config;
		// Generate image
		$img_x = 800;
		$img_y = 250;
		$img = imagecreate($img_x, $img_y);

		// Generate colors
		$background = imagecolorallocate($img, mt_rand(155, 255), mt_rand(155, 255), mt_rand(155, 255));
		imagefill($img, 0, 0, $background);

		$random = $fontcolors = array();

		for ($i = 0; $i < 15; $i++)
		{
			$random[$i] = imagecolorallocate($img, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
			$fontcolors[$i] = imagecolorallocate($img, mt_rand(0, 120), mt_rand(0, 120), mt_rand(0, 120));
		}

		// Generate code characters
		$characters = $sizes = $bounding_boxes = array();
		$width_avail = $img_x;
		$code_num = sizeof($code);

		for ($i = 0; $i < $code_num; ++$i)
		{
			$char_class = $this->captcha_char();
			$characters[$i] = new $char_class($code[$i]);
			
			list($min, $max) = $characters[$i]->range();
			$sizes[$i] = mt_rand($min, $max);
			$box = $characters[$i]->dimensions($sizes[$i]);
			$width_avail -= ($box[2] - $box[0]);
			$bounding_boxes[$i] = $box;
		}

		// Redistribute leftover x-space
		$offset = array();
		for ($i = 0; $i < $code_num; ++$i)
		{
			$denom = ($code_num - $i);
			$denom = max(1.5, $denom);
			$offset[$i] = mt_rand(0, (1.5 * $width_avail) / $denom);
			$width_avail -= $offset[$i];
		}

		// Add some line noise
		if ($config['policy_entropy_noise_line'])
		{
			$this->noise_line($img, 0, 0, $img_x, $img_y, $background, $fontcolors, $random);
		}

		// Draw the text
		$xoffset = 0;
		for ($i = 0; $i < $code_num; ++$i)
		{
			$dimm = $bounding_boxes[$i];
			$xoffset += ($offset[$i] - $dimm[0]);
			$yoffset = mt_rand(-$dimm[1], $img_y - $dimm[3]);
			$characters[$i]->drawchar($sizes[$i], $xoffset, $yoffset, $img, $background, $fontcolors);
			$xoffset += $dimm[2];
		}

		// Add some pixel noise
		if ($config['policy_entropy_noise_pixel'])
		{
			$this->noise_pixel($img, 0, 0, $img_x, $img_y, $background, $fontcolors, $random, $config['policy_entropy_noise_pixel']);
		}

		// Send image
		$this->send_image($img);
	}

	/**
	* 3dbitmap
	*/
	function policy_3dbitmap($code)
	{
		// Generate image
		$img_x	= 700;
		$img_y	= 225;
		$img	= imagecreate($img_x, $img_y);
		$x_grid = mt_rand(6, 10);
		$y_grid = mt_rand(6, 10);

		// Ok, so lets cut to the chase. We could accurately represent this in 3d and
		// do all the appropriate linear transforms. my questions is... why bother?
		// The computational overhead is unnecessary when you consider the simple fact:
		// we're not here to accurately represent a model, but to just show off some random-ish
		// polygons

		// Conceive of 3 spaces.
		// 1) planar-space (discrete "pixel" grid)
		// 2) 3-space. (planar-space with z/height aspect)
		// 3) image space (pixels on the screen)

		// resolution of the planar-space we're embedding the text code in
		$plane_x	= 90;
		$plane_y	= 25;

		$subdivision_factor	= 2;

		// $box is the 4 points in img_space that correspond to the corners of the plane in 3-space
		$box = array(array(), array(), array(), array());

		// Top left
		$box[0][0] = mt_rand(20, 40);
		$box[0][1] = mt_rand(40, 60);

		// Top right
		$box[1][0] = mt_rand($img_x - 80, $img_x - 60);
		$box[1][1] = mt_rand(10, 30);

		// Bottom right
		$box[2][0] = mt_rand($img_x - 40, $img_x - 20);
		$box[2][1] = mt_rand($img_y - 50, $img_y - 30);

		// Bottom left.
		// because we want to be able to make shortcuts in the 3d->2d,
		// we'll calculate the 4th point so that it forms a proper trapezoid
		$box[3][0] = $box[2][0] + $box[0][0] - $box[1][0];
		$box[3][1] = $box[2][1] + $box[0][1] - $box[1][1];

		// Generate colors (When we get a chance, come up with *better* colors. these ones suck)
		$background = imagecolorallocate($img, mt_rand(155, 255), mt_rand(155, 255), mt_rand(155, 255));
		imagefill($img, 0, 0, $background);

		$colors = array();

		$minr = mt_rand(0, 8);
		$ming = mt_rand(0, 8);
		$minb = mt_rand(0, 8);

		$maxr = mt_rand(128, 220);
		$maxg = mt_rand(128, 220);
		$maxb = mt_rand(128, 220);

		for ($i = -30; $i <= 30; ++$i)
		{
			$coeff1 = ($i + 30) / 60;
			$coeff2 = 1 - $coeff1;
			$colors[$i] = imagecolorallocate($img, ($coeff2 * $maxr) + ($coeff1 * $minr), ($coeff2 * $maxg) + ($coeff1 * $ming), ($coeff2 * $maxb) + ($coeff1 * $minb));
		}

		// $img_buffer is the last row of 3-space positions (converted to img-space), cached
		// (using this means we don't need to recalculate all 4 positions for each new polygon,
		// merely the newest point that we're adding, which is then cached.
		$img_buffer = array(array(), array());

		// In image-space, the x- and y-offset necessary to move one unit in the x-direction in planar-space
		$dxx = ($box[1][0] - $box[0][0]) / ($subdivision_factor * $plane_x);
		$dxy = ($box[1][1] - $box[0][1]) / ($subdivision_factor * $plane_x);

		// In image-space, the x- and y-offset necessary to move one unit in the y-direction in planar-space
		$dyx = ($box[3][0] - $box[0][0]) / ($subdivision_factor * $plane_y);
		$dyy = ($box[3][1] - $box[0][1]) / ($subdivision_factor * $plane_y);

		// Initial captcha-letter offset in planar-space
		$plane_offset_x = 2;
		$plane_offset_y = 5;

		// character map
		$map = captcha_bitmaps();

		// matrix
		$plane = array();

		// for each character, we'll silkscreen it into our boolean pixel plane
		for ($c = 0, $code_num = sizeof($code); $c < $code_num; ++$c)
		{
			$letter = $code[$c];

			for ($x = $map['width'] - 1; $x >= 0; --$x)
			{
				for ($y = $map['height'] - 1; $y >= 0; --$y)
				{
					if ($map['data'][$letter][$y][$x])
					{
						$plane[$y + $plane_offset_y + (($c & 1) ? 1 : -1)][$x + $plane_offset_x] = true;
					}
				}
			}
			$plane_offset_x += 11;
		}

		// calculate our first buffer, we can't actually draw polys with these yet
		// img_pos_prev == screen x,y location to our immediate left.
		// img_pos_cur == current screen x,y location
		// we calculate screen position of our
		// current cell based on the difference from the previous cell
		// rather than recalculating from absolute coordinates
		// What we cache into the $img_buffer contains the raised text coordinates.
		$img_pos_prev	= $img_buffer[0][0] = $box[0];
		$cur_height		= $prev_height = $this->wave_height(0, 0, $subdivision_factor);
		$full_x			= $plane_x * $subdivision_factor;
		$full_y			= $plane_y * $subdivision_factor;

		for ($x = 1; $x <= $full_x; ++$x)
		{
			$cur_height		= $this->wave_height($x, 0, $subdivision_factor);
			$offset			= $cur_height - $prev_height; 
			$img_pos_cur	= array($img_pos_prev[0] + $dxx, $img_pos_prev[1] + $dxy + $offset);

			$img_buffer[0][$x]	= $img_pos_cur;
			$img_pos_prev		= $img_pos_cur;
			$prev_height		= $cur_height;
		}

		for ($y = 1; $y <= $full_y; ++$y)
		{
			// swap buffers
			$buffer_cur		= $y & 1;
			$buffer_prev	= 1 - $buffer_cur;

			$prev_height	= $this->wave_height(0, $y, $subdivision_factor);
			$offset			= $prev_height - $this->wave_height(0, $y - 1, $subdivision_factor);
			$img_pos_cur	= array($img_buffer[$buffer_prev][0][0] + $dyx, $img_buffer[$buffer_prev][0][1] + $dyy + $offset);
			$img_pos_prev	= $img_pos_cur;

			$img_buffer[$buffer_cur][0]	= $img_pos_cur;

			for ($x = 1; $x <= $full_x; ++$x)
			{
				$cur_height		= $this->wave_height($x, $y, $subdivision_factor) + $this->grid_height($x, $y, 1, $x_grid, $y_grid);

				// height is a z-factor, not a y-factor
				$offset			= $cur_height - $prev_height;
				$img_pos_cur	= array($img_pos_prev[0] + $dxx, $img_pos_prev[1] + $dxy + $offset);
				
				// (height is float, index it to an int, get closest color)
				$color			= $colors[intval($cur_height)];
				$img_pos_prev	= $img_pos_cur;
				$prev_height	= $cur_height;

				$y_index_old = intval(($y - 1) / $subdivision_factor);
				$y_index_new = intval($y / $subdivision_factor);
				$x_index_old = intval(($x - 1) / $subdivision_factor);
				$x_index_new = intval($x / $subdivision_factor);

				if (!empty($plane[$y_index_new][$x_index_new]))
				{
					$offset2		= $this->wave_height($x, $y, $subdivision_factor, 1) - 30 - $cur_height;
					$img_pos_cur[1]	+= $offset2;
					$color			= $colors[20];
				}
				$img_buffer[$buffer_cur][$x] = $img_pos_cur;

				// Smooth the edges as much as possible by having not more than one low<->high traingle per square
				// Otherwise, just
				$diag_down	= (empty($plane[$y_index_old][$x_index_old]) == empty($plane[$y_index_new][$x_index_new]));
				$diag_up	= (empty($plane[$y_index_old][$x_index_new]) == empty($plane[$y_index_new][$x_index_old]));

				// natural switching
				$mode = ($x + $y) & 1;

				// override if it requires it
				if ($diag_down != $diag_up)
				{
					$mode = $diag_up;
				}

				if ($mode)
				{
					//		+-/			  /
					// 1	|/		2	 /|
					//		/			/-+
					$poly1 = array_merge($img_buffer[$buffer_cur][$x - 1], $img_buffer[$buffer_prev][$x - 1], $img_buffer[$buffer_prev][$x]);
					$poly2 = array_merge($img_buffer[$buffer_cur][$x - 1], $img_buffer[$buffer_cur][$x], $img_buffer[$buffer_prev][$x]);
				}
				else
				{
					//		\			\-+
					// 1	|\		2	 \|
					//		+-\			  \
					$poly1 = array_merge($img_buffer[$buffer_cur][$x - 1], $img_buffer[$buffer_prev][$x - 1], $img_buffer[$buffer_cur][$x]);
					$poly2 = array_merge($img_buffer[$buffer_prev][$x - 1], $img_buffer[$buffer_prev][$x], $img_buffer[$buffer_cur][$x]);
				}

				imagefilledpolygon($img, $poly1, 3, $color);
				imagefilledpolygon($img, $poly2, 3, $color);
			}
		}

		// Send image on it's merry way
		$this->send_image($img);
	}

	/**
	* overlap
	*/
	function policy_overlap($code)
	{
		global $config;
		$char_size = 40;
		$overlap_factor = .32;

		// Generate image
		$img_x = 250;
		$img_y = 120;
		$img = imagecreate($img_x, $img_y);

		// Generate colors
		$background = imagecolorallocate($img, mt_rand(155, 255), mt_rand(155, 255), mt_rand(155, 255));
		imagefill($img, 0, 0, $background);

		$random = $fontcolors = array();

		for ($i = 0; $i < 15; $i++)
		{
			$random[$i] = imagecolorallocate($img, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
		}

		$fontcolors[0] = imagecolorallocate($img, mt_rand(0, 120), mt_rand(0, 120), mt_rand(0, 120));

		// Generate code characters
		$characters = $bounding_boxes = array();
		$width_avail = $img_x;

		// Get the character rendering scheme
		$char_class = $this->captcha_char('char_ttf');
		$code_num = sizeof($code);

		for ($i = 0; $i < $code_num; ++$i)
		{
			$characters[$i] = new $char_class($code[$i], array('angle' => 0));
			$box = $characters[$i]->dimensions($char_size);
			$width_avail -= ((1 - $overlap_factor) * ($box[2] - $box[0]));
			$bounding_boxes[$i] = $box;
		}

		// Redistribute leftover x-space
		$offset = mt_rand(0, $width_avail);

		// Add some line noise
		if ($config['policy_overlap_noise_line'])
		{
			$this->noise_line($img, 0, 0, $img_x, $img_y, $background, $fontcolors, $random);
		}

		// Draw the text
		$min = -$bounding_boxes[0][1];
		$max = $img_y - $bounding_boxes[0][3];
		$med = ($max + $min) / 2;

		$yoffset = mt_rand($med, $max);

		for ($i = 0; $i < $code_num; ++$i)
		{
			$dimm = $bounding_boxes[$i];
			$offset -= $dimm[0];
			$characters[$i]->drawchar($char_size, $offset, $yoffset, $img, $background, $fontcolors);
			$offset += $dimm[2];
			$offset -= (($dimm[2] - $dimm[0]) * $overlap_factor);
			$yoffset += ($i & 1) ? ((1 - $overlap_factor) * ($dimm[3] - $dimm[1])) : ((1 - $overlap_factor) * ($dimm[1] - $dimm[3]));
		}

		// Add some medium pixel noise
		if ($config['policy_overlap_noise_pixel'])
		{
			$this->noise_pixel($img, 0, 0, $img_x, $img_y, $background, $fontcolors, $random, $config['policy_overlap_noise_pixel']);
		}

		// Send image
		$this->send_image($img);
	}

	/**
	* Noise pixel
	*/
	function noise_pixel($img, $min_x, $min_y, $max_x, $max_y, $bg, $font, $non_font, $override = false)
	{
		$noise_modules = array('noise_pixel_light', 'noise_pixel_medium', 'noise_pixel_heavy');

		if ($override == false)
		{
			$override = array_rand($override);
		}

		// Use the module $override, else a random picked one...
		$module = $noise_modules[intval($override) - 1];

		switch ($module)
		{
			case 'noise_pixel_light':

				for ($x = $min_x; $x < $max_x; $x += mt_rand(9, 18))
				{
					for ($y = $min_y; $y < $max_y; $y += mt_rand(4, 9))
					{
						imagesetpixel($img, $x, $y, $non_font[array_rand($non_font)]);
					}
				}

				for ($y = $min_y; $y < $max_y; $y += mt_rand(9, 18))
				{
					for ($x = $min_x; $x < $max_x; $x += mt_rand(4, 9))
					{
						imagesetpixel($img, $x, $y, $non_font[array_rand($non_font)]);
					}
				}

			break;

			case 'noise_pixel_medium':

				for ($x = $min_x; $x < $max_x; $x += mt_rand(4, 9))
				{
					for ($y = $min_y; $y < $max_y; $y += mt_rand(2, 5))
					{
						imagesetpixel($img, $x, $y, $non_font[array_rand($non_font)]);
					}
				}

				for ($y = $min_y; $y < $max_y; $y += mt_rand(4, 9))
				{
					for ($x = $min_x; $x < $max_x; $x += mt_rand(2, 5))
					{
						imagesetpixel($img, $x, $y, $non_font[array_rand($non_font)]);
					}
				}

			break;

			case 'noise_pixel_heavy':

				for ($x = $min_x; $x < $max_x; $x += mt_rand(4, 9))
				{
					for ($y = $min_y; $y < $max_y; $y++)
					{
						imagesetpixel($img, $x, $y, $non_font[array_rand($non_font)]);
					}
				}

				for ($y = $min_y; $y < $max_y; $y+= mt_rand(4, 9))
				{
					for ($x = $min_x; $x < $max_x; $x++)
					{
						imagesetpixel($img, $x, $y, $non_font[array_rand($non_font)]);
					}
				}

			break;
		}
	}

	/**
	* Noise line
	*/
	function noise_line($img, $min_x, $min_y, $max_x, $max_y, $bg, $font, $non_font)
	{
		$x1 = $min_x;
		$x2 = $max_x;
		$y1 = $min_y;
		$y2 = $min_y;

		do
		{
			$line = array();

			for ($j = mt_rand(30, 60); $j > 0; --$j)
			{
				$line[] = $non_font[array_rand($non_font)];
			}

			for ($j = mt_rand(30, 60); $j > 0; --$j)
			{
				$line[] = $bg;
			}

			imagesetstyle($img, $line);
			for ($yp = -1; $yp <= 1; ++$yp)
			{
				imageline($img, $x1, $y1 + $yp, $x2, $y2 + $yp, IMG_COLOR_STYLED);
			}

			$y1 += mt_rand(12, 35);
			$y2 += mt_rand(12, 35);
		}
		while ($y1 < $max_y && $y2 < $max_y);

		$x1 = $min_x;
		$x2 = $min_x;
		$y1 = $min_y;
		$y2 = $max_y;

		do
		{
			$line = array();

			for ($j = mt_rand(30, 60); $j > 0; --$j)
			{
				$line[] = $non_font[array_rand($non_font)];
			}

			for ($j = mt_rand(30, 60); $j > 0; --$j)
			{
				$line[] = $bg;
			}

			imagesetstyle($img, $line);
			for ($xp = -1; $xp <= 1; ++$xp)
			{
				imageline($img, $x1 + $xp, $y1, $x2 + $xp, $y2, IMG_COLOR_STYLED);
			}

			$x1 += mt_rand(12, 35);
			$x2 += mt_rand(12, 35);
		}
		while ($x1 < $max_x && $x2 < $max_x);
	}

	/**
	* Randomly determine which char class to use
	* Able to define static one with override
	*/
	function captcha_char($override = false)
	{
		static $character_classes = array();

		// Some people have GD but no TTF support
		if (sizeof($character_classes) == 0)
		{
			$character_classes = array('char_vector', 'char_hatches', 'char_cube3d', 'char_dots');

			if (function_exists('imagettfbbox') && function_exists('imagettftext'))
			{
				$character_classes[] = 'char_ttf';
			}
		}

		// Use the module $override, else a random picked one...
		$class = ($override !== false && in_array($override, $character_classes)) ? $override : $character_classes[array_rand($character_classes)];

		return $class;
	}
}

/**
* @package VC
*/
class char_dots
{
	var $vectors;
	var $space;
	var $radius;
	var $letter;
	var $width_percent;

	/**
	* Constuctor
	*/
	function char_dots($letter = '', $args = false)
	{
		$width_percent = false;
		if (is_array($args))
		{
			$width_percent = (!empty($args['width_percent'])) ? $args['width_percent'] : false;
		}

		$this->vectors = captcha_vectors();
		$this->width_percent = (!empty($width_percent)) ? max(25, min(150, intval($width_percent))) : mt_rand(60, 90);

		$this->space = 10;
		$this->radius = 1;
		$this->letter = $letter;
	}
	
	/**
	* Draw a character
	*/
	function drawchar($scale, $xoff, $yoff, $img, $background, $colors)
	{
		$vectorset	= $this->vectors[$this->letter];
		$height		= $scale;
		$width		= (($scale * $this->width_percent) / 100);
		$color		= $colors[array_rand($colors)];

		if (sizeof($vectorset))
		{
			foreach ($vectorset as $veclist)
			{
				switch ($veclist[0])
				{
					case 'line':

						$dx = ($veclist[3] - $veclist[1]) * $width;
						$dy = ($veclist[4] - $veclist[2]) * -$height;

						$len = sqrt(($dx * $dx) + ($dy * $dy));

						$inv_dx = -($dy / $len);
						$inv_dy = ($dx / $len);

						for ($i = 0; $i < $len; ++$i)
						{
							$shift1 = mt_rand(-$this->radius, $this->radius);
							$shift2 = mt_rand(-$this->radius, $this->radius);

							imagesetpixel($img,
										$xoff + ($veclist[1] * $width) + (($i * $dx) / $len) + ($inv_dx * $shift1),
										$yoff + ((1 - $veclist[2]) * $height) + (($i * $dy) / $len) + ($inv_dy * $shift1),
										$color);
							
							imagesetpixel($img,
										$xoff + ($veclist[1] * $width) + (($i * $dx) / $len) + ($inv_dx * $shift2),
										$yoff + ((1 - $veclist[2]) * $height) + (($i * $dy) / $len) + ($inv_dy * $shift2),
										$color);
						}
						
					break;

					case 'arc':
					
						$arclengthdeg = $veclist[6] - $veclist[5];
						$arclengthdeg += ( $arclengthdeg < 0 ) ? 360 : 0;
						
						$arclength = ((($veclist[3] * $width) + ($veclist[4] * $height)) * M_PI) / 2;

						$arclength = ($arclength * $arclengthdeg) / 360;

						$x_c = $veclist[1] * $width;
						$y_c = (1 - $veclist[2]) * $height;
						$increment = ($arclengthdeg / $arclength);

						for ($i = 0; $i < $arclengthdeg; $i += $increment)
						{
							$theta = deg2rad(($i + $veclist[5]) % 360);
							$shift1 = mt_rand(-$this->radius, $this->radius);
							$shift2 = mt_rand(-$this->radius, $this->radius);
							$x_o1 = cos($theta) * (($veclist[3] * 0.5 * $width) + $shift1);
							$y_o1 = sin($theta) * (($veclist[4] * 0.5 * $height) + $shift1);
							$x_o2 = cos($theta) * (($veclist[3] * 0.5 * $width) + $shift2);
							$y_o2 = sin($theta) * (($veclist[4] * 0.5 * $height) + $shift2);

							imagesetpixel($img,
										$xoff + $x_c + $x_o1,
										$yoff + $y_c + $y_o1,
										$color);

							imagesetpixel($img,
										$xoff + $x_c + $x_o2,
										$yoff + $y_c + $y_o2,
										$color);
						}

					break;
					
					default:
						// Do nothing with bad input
					break;
				}
			}
		}
	}

	/*
	* return a roughly acceptable range of sizes for rendering with this texttype
	*/
	function range()
	{
		return array(60, 80);
	}

	/**
	* dimensions
	*/
	function dimensions($size)
	{
		return array(-4, -4, (($size * $this->width_percent) / 100) + 4, $size + 4);
	}
}

/**
* @package VC
*/
class char_vector
{
	var $vectors;
	var $width_percent;
	var $letter;

	/**
	* Constructor
	*/
	function char_vector($letter = '', $args = false)
	{
		$width_percent = false;
		if (is_array($args))
		{
			$width_percent = (!empty($args['width_percent'])) ? $args['width_percent'] : false;
		}

		$this->vectors = captcha_vectors();
		$this->width_percent = (!empty($width_percent)) ? max(25, min(150, intval($width_percent))) : mt_rand(60,90);
		$this->letter = $letter;
	}

	/**
	* Draw a character
	*/
	function drawchar($scale, $xoff, $yoff, $img, $background, $colors)
	{
		$vectorset	= $this->vectors[$this->letter];
		$height		= $scale;
		$width		= (($scale * $this->width_percent) / 100);
		$color		= $colors[array_rand($colors)];

		if (sizeof($vectorset))
		{
			foreach ($vectorset as $veclist)
			{
				for ($i = 0; $i < 9; ++$i)
				{
					$xp = $i % 3;
					$yp = ($i - $xp) / 3;
					$xp--;
					$yp--;

					switch ($veclist[0])
					{
						case 'line':
							imageline($img,
								$xoff + $xp + ($veclist[1] * $width),
								$yoff + $yp + ((1 - $veclist[2]) * $height),
								$xoff + $xp + ($veclist[3] * $width),
								$yoff + $yp + ((1 - $veclist[4]) * $height),
								$color
							);
						break;

						case 'arc':
							imagearc($img,
								$xoff + $xp + ($veclist[1] * $width),
								$yoff + $yp + ((1 - $veclist[2]) * $height),
								$veclist[3] * $width,
								$veclist[4] * $height,
								$veclist[5],
								$veclist[6],
								$color
							);
						break;
					}
				}
			}
		}
	}

	/*
	* return a roughly acceptable range of sizes for rendering with this texttype
	*/
	function range()
	{
		return array(50, 80);
	}

	/**
	* dimensions
	*/
	function dimensions($size)
	{
		return array(-2, -2, (($size * $this->width_percent) / 100 ) + 2, $size + 2);
	}
}

/**
* @package VC
*/
class char_ttf
{
	var $angle = 0;
	var $fontfile = '';
	var $letter = '';

	/**
	* Constructor
	*/
	function char_ttf($letter = '', $args = false)
	{
		$font = $angle = false;

		if (is_array($args))
		{
			$font = (!empty($args['font'])) ? $args['font'] : false;
			$angle = (isset($args['angle'])) ? $args['angle'] : false;
		}

		$fonts = captcha_load_ttf_fonts();

		if (empty($font) || !isset($fonts[$font]))
		{
			$font = array_rand($fonts);
		}

		$this->fontfile = $fonts[$font];
		$this->angle = ($angle !== false) ? intval($angle) : mt_rand(-40, 40);
		$this->letter = $letter;
	}

	/**
	* Draw a character
	*/
	function drawchar($scale, $xoff, $yoff, $img, $background, $colors)
	{
		$color = $colors[array_rand($colors)];
		imagettftext($img, $scale, $this->angle, $xoff, $yoff, $color, $this->fontfile, $this->letter);
	}

	/*
	* return a roughly acceptable range of sizes for rendering with this texttype
	*/
	function range()
	{
		return array(36, 150);
	}

	/**
	* Dimensions
	*/
	function dimensions($scale)
	{
		$data = imagettfbbox($scale, $this->angle, $this->fontfile, $this->letter);
		return ($this->angle > 0) ? array($data[6], $data[5], $data[2], $data[1]) : array($data[0], $data[7], $data[4], $data[3]);
	}
}

/**
* @package VC
*/
class char_hatches
{
	var $vectors;
	var $space;
	var $radius;
	var $letter;

	/**
	* Constructor
	*/
	function char_hatches($letter = '', $args = false)
	{
		$width_percent = false;
		if (is_array($args))
		{
			$width_percent = (!empty($args['width_percent'])) ? $args['width_percent'] : false;
		}

		$this->vectors = captcha_vectors();
		$this->width_percent = (!empty($width_percent)) ? max(25, min(150, intval($width_percent))) : mt_rand(60, 90);

		$this->space = 10;
		$this->radius = 3;
		$this->letter = $letter;
	}

	/**
	* Draw a character
	*/
	function drawchar($scale, $xoff, $yoff, $img, $background, $colors)
	{
		$vectorset	= $this->vectors[$this->letter];
		$height		= $scale;
		$width		= (($scale * $this->width_percent) / 100);
		$color		= $colors[array_rand($colors)];

		if (sizeof($vectorset))
		{
			foreach ($vectorset as $veclist)
			{
				switch ($veclist[0])
				{
					case 'line':
						$dx = ($veclist[3] - $veclist[1]) * $width;
						$dy = ($veclist[4] - $veclist[2]) * -$height;

						$idx = -$dy;
						$idy = $dx;

						$length = sqrt(pow($dx, 2) + pow($dy, 2));

						$hatches = $length / $this->space;

						for ($p = 0; $p <= $hatches; ++$p)
						{
							// toss some crap at the hough transform, if people even get to that stage here
							if (!mt_rand(0, 9) && ($hatches > 3) && !$p)
							{
								continue;
							}

							$xp = 1;
							$yp = -2;
							for ($i = 0; $i < 9; ++$i)
							{
								$xp += !($i % 3) ? -2 : 1;
								$yp += !($i % 3) ? 1 : 0;

								$x_o = ((($p * $veclist[1]) + (($hatches - $p) * $veclist[3]))  * $width ) / $hatches; 
								$y_o = $height - (((($p * $veclist[2]) + (($hatches - $p) * $veclist[4]))  * $height ) / $hatches);
								$x_1 = $xoff + $xp + $x_o;
								$y_1 = $yoff + $yp + $y_o;

								$x_d1 = (($dx - $idx) * $this->radius) / $length;
								$y_d1 = (($dy - $idy) * $this->radius) / $length;

								$x_d2 = (($dx - $idx) * -$this->radius) / $length;
								$y_d2 = (($dy - $idy) * -$this->radius) / $length;

								imageline($img, $x_1 + $x_d1, $y_1 + $y_d1, $x_1 + $x_d2, $y_1 + $y_d2, $color);
							}
						}
					break;

					case 'arc':
						$arclengthdeg = $veclist[6] - $veclist[5];
						$arclengthdeg += ( $arclengthdeg < 0 ) ? 360 : 0;

						$arclength = ((($veclist[3] * $width) + ($veclist[4] * $height)) * M_PI) / 2;
						$arclength = ($arclength * $arclengthdeg) / 360;

						$hatches = $arclength / $this->space;

						$hatchdeg = ($arclengthdeg * $this->space) / $arclength;
						$shiftdeg = ($arclengthdeg * $this->radius) / $arclength;

						$x_c = $veclist[1] * $width;
						$y_c = (1 - $veclist[2]) * $height;

						for ($p = 0; $p <= $arclengthdeg; $p += $hatchdeg)
						{
							if (!mt_rand(0, 9) && ($hatches > 3) && !$p)
							{
								continue;
							}

							$theta1 = deg2rad(($p + $veclist[5] - $shiftdeg) % 360);
							$theta2 = deg2rad(($p + $veclist[5] + $shiftdeg) % 360);
							$x_o1 = cos($theta1) * (($veclist[3] * 0.5 * $width) - $this->radius);
							$y_o1 = sin($theta1) * (($veclist[4] * 0.5 * $height) - $this->radius);
							$x_o2 = cos($theta2) * (($veclist[3] * 0.5 * $width) + $this->radius);
							$y_o2 = sin($theta2) * (($veclist[4] * 0.5 * $height) + $this->radius);

							$xp = 1;
							$yp = -2;
							for ($i = 0; $i < 9; ++$i)
							{
								$xp += !($i % 3) ? -2 : 1;
								$yp += !($i % 3) ? 1 : 0;
								
								imageline($img,
									$xoff + $xp + $x_c + $x_o1,
									$yoff + $yp + $y_c + $y_o1,
									$xoff + $xp + $x_c + $x_o2,
									$yoff + $yp + $y_c + $y_o2,
									$color
								);
							}
						}
					break;
				}
			}
		}
	}

	/*
	* return a roughly acceptable range of sizes for rendering with this texttype
	*/
	function range()
	{
		return array(60, 80);
	}

	/**
	* Dimensions
	*/
	function dimensions($size)
	{
		return array(-4, -4, (($size * $this->width_percent) / 100) + 4, $size + 4);
	}
}

/**
* @package VC
*/
class char_cube3d
{
	// need to abstract out the cube3d from the cubechar
	var $bitmaps;

	var $basis_matrix = array(array(1, 0, 0), array(0, 1, 0), array(0, 0, 1));
	var $abs_x = array(1, 0);
	var $abs_y = array(0, 1);
	var $x = 0;
	var $y = 1;
	var $z = 2;
	var $letter = '';

	function char_cube3d($letter)
	{
		$this->bitmaps = captcha_bitmaps();

		$this->basis_matrix[0][0] = mt_rand(-600, 600);
		$this->basis_matrix[0][1] = mt_rand(-600, 600);
		$this->basis_matrix[0][2] = (mt_rand(0, 1) * 2000) - 1000;
		$this->basis_matrix[1][0] = mt_rand(-1000, 1000);
		$this->basis_matrix[1][1] = mt_rand(-1000, 1000);
		$this->basis_matrix[1][2] = mt_rand(-1000, 1000);

		$this->normalize($this->basis_matrix[0]);
		$this->normalize($this->basis_matrix[1]);
		$this->basis_matrix[2] = $this->cross_product($this->basis_matrix[0], $this->basis_matrix[1]);
		$this->normalize($this->basis_matrix[2]);

		// $this->basis_matrix[1] might not be (probably isn't) orthogonal to $basis_matrix[0]
		$this->basis_matrix[1] = $this->cross_product($this->basis_matrix[0], $this->basis_matrix[2]);
		$this->normalize($this->basis_matrix[1]);

		// Make sure our cube is facing into the canvas (assuming +z == in)
		for ($i = 0; $i < 3; ++$i)
		{
			if ($this->basis_matrix[$i][2] < 0)
			{
				$this->basis_matrix[$i][0] *= -1;
				$this->basis_matrix[$i][1] *= -1;
				$this->basis_matrix[$i][2] *= -1;
			}
		}

		// Force our "z" basis vector to be the one with greatest absolute z value
		$this->x = 0;
		$this->y = 1;
		$this->z = 2;

		// Swap "y" with "z"
		if ($this->basis_matrix[1][2] > $this->basis_matrix[2][2])
		{
			$this->z = 1;
			$this->y = 2;
		}

		// Swap "x" with "z"
		if ($this->basis_matrix[0][2] > $this->basis_matrix[$this->z][2])
		{
			$this->x = $this->z;
			$this->z = 0;
		}

		// Still need to determine which of $x,$y are which.
		// wrong orientation if y's y-component is less than it's x-component
		// likewise if x's x-component is less than it's y-component
		// if they disagree, go with the one with the greater weight difference.
		// rotate if positive
		$weight = (abs($this->basis_matrix[$this->x][1]) - abs($this->basis_matrix[$this->x][0])) +
					(abs($this->basis_matrix[$this->y][0]) - abs($this->basis_matrix[$this->y][1]));

		// Swap "x" with "y"
		if ($weight > 0)
		{
			list($this->x, $this->y) = array($this->y, $this->x);
		}

		$this->abs_x = array($this->basis_matrix[$this->x][0], $this->basis_matrix[$this->x][1]);
		$this->abs_y = array($this->basis_matrix[$this->y][0], $this->basis_matrix[$this->y][1]);

		if ($this->abs_x[0] < 0)
		{
			$this->abs_x[0] *= -1;
			$this->abs_x[1] *= -1;
		}

		if ($this->abs_y[1] > 0)
		{
			$this->abs_y[0] *= -1;
			$this->abs_y[1] *= -1;
		}

		$this->letter = $letter;
	}

	/**
	*
	*/
	function draw($im, $scale, $xoff, $yoff, $face, $xshadow, $yshadow)
	{
		$origin = array(0, 0, 0);
		$xvec = $this->scale($this->basis_matrix[$this->x], $scale);
		$yvec = $this->scale($this->basis_matrix[$this->y], $scale);
		$face_corner = $this->sum2($xvec, $yvec);

		$zvec = $this->scale($this->basis_matrix[$this->z], $scale);
		$x_corner = $this->sum2($xvec, $zvec);
		$y_corner = $this->sum2($yvec, $zvec);

		imagefilledpolygon($im, $this->gen_poly($xoff, $yoff, $origin, $xvec, $x_corner, $zvec), 4, $yshadow);
		imagefilledpolygon($im, $this->gen_poly($xoff, $yoff, $origin, $yvec, $y_corner, $zvec), 4, $xshadow);
		imagefilledpolygon($im, $this->gen_poly($xoff, $yoff, $origin, $xvec, $face_corner, $yvec), 4, $face);
	}

	/**
	* Draw a character
	*/
	function drawchar($scale, $xoff, $yoff, $img, $background, $colors)
	{
		$width = $this->bitmaps['width'];
		$height = $this->bitmaps['height'];
		$bitmap = $this->bitmaps['data'][$this->letter];

		$color1 = $colors[array_rand($colors)];
		$color2 = $colors[array_rand($colors)];

		$swapx = ($this->basis_matrix[$this->x][0] > 0);
		$swapy = ($this->basis_matrix[$this->y][1] < 0);

		for ($y = 0; $y < $height; ++$y)
		{
			for ($x = 0; $x < $width; ++$x)
			{
				$xp = ($swapx) ? ($width - $x - 1) : $x;
				$yp = ($swapy) ? ($height - $y - 1) : $y;

				if ($bitmap[$height - $yp - 1][$xp])
				{
					$dx = $this->scale($this->abs_x, ($xp - ($swapx ? ($width / 2) : ($width / 2) - 1)) * $scale);
					$dy = $this->scale($this->abs_y, ($yp - ($swapy ? ($height / 2) : ($height / 2) - 1)) * $scale);
					$xo = $xoff + $dx[0] + $dy[0];
					$yo = $yoff + $dx[1] + $dy[1];

					$origin = array(0, 0, 0);
					$xvec = $this->scale($this->basis_matrix[$this->x], $scale);
					$yvec = $this->scale($this->basis_matrix[$this->y], $scale);
					$face_corner = $this->sum2($xvec, $yvec);

					$zvec = $this->scale($this->basis_matrix[$this->z], $scale);
					$x_corner = $this->sum2($xvec, $zvec);
					$y_corner = $this->sum2($yvec, $zvec);

					imagefilledpolygon($img, $this->gen_poly($xo, $yo, $origin, $xvec, $x_corner,$zvec), 4, $color1);
					imagefilledpolygon($img, $this->gen_poly($xo, $yo, $origin, $yvec, $y_corner,$zvec), 4, $color2);

					$face = $this->gen_poly($xo, $yo, $origin, $xvec, $face_corner, $yvec);

					imagefilledpolygon($img, $face, 4, $background);
					imagepolygon($img, $face, 4, $color1);
				}
			}
		}
	}

	/*
	* return a roughly acceptable range of sizes for rendering with this texttype
	*/
	function range()
	{
		return array(5, 10);
	}

	/**
	* Vector length
	*/
	function vectorlen($vector)
	{
		return sqrt(pow($vector[0], 2) + pow($vector[1], 2) + pow($vector[2], 2));
	}

	/**
	* Normalize
	*/
	function normalize(&$vector, $length = 1)
	{
		$length = (( $length < 1) ? 1 : $length);
		$length /= $this->vectorlen($vector);
		$vector[0] *= $length;
		$vector[1] *= $length;
		$vector[2] *= $length;
	}

	/**
	*
	*/
	function cross_product($vector1, $vector2)
	{
		$retval = array(0, 0, 0);
		$retval[0] =  (($vector1[1] * $vector2[2]) - ($vector1[2] * $vector2[1]));
		$retval[1] = -(($vector1[0] * $vector2[2]) - ($vector1[2] * $vector2[0]));
		$retval[2] =  (($vector1[0] * $vector2[1]) - ($vector1[1] * $vector2[0]));

		return $retval;
	}

	/**
	* 
	*/
	function sum($vector1, $vector2)
	{
		return array($vector1[0] + $vector2[0], $vector1[1] + $vector2[1], $vector1[2] + $vector2[2]);
	}

	/**
	* 
	*/
	function sum2($vector1, $vector2)
	{
		return array($vector1[0] + $vector2[0], $vector1[1] + $vector2[1]);
	}

	/**
	* 
	*/
	function scale($vector, $length)
	{
		if (sizeof($vector) == 2)
		{
			return array($vector[0] * $length, $vector[1] * $length);
		}

		return array($vector[0] * $length, $vector[1] * $length, $vector[2] * $length);
	}

	/**
	* 
	*/
	function gen_poly($xoff, $yoff, &$vec1, &$vec2, &$vec3, &$vec4)
	{
		$poly = array();
		$poly[0] = $xoff + $vec1[0];
		$poly[1] = $yoff + $vec1[1];
		$poly[2] = $xoff + $vec2[0];
		$poly[3] = $yoff + $vec2[1];
		$poly[4] = $xoff + $vec3[0];
		$poly[5] = $yoff + $vec3[1];
		$poly[6] = $xoff + $vec4[0];
		$poly[7] = $yoff + $vec4[1];

		return $poly;
	}

	/**
	* dimensions
	*/
	function dimensions($size)
	{
		$xn = $this->scale($this->basis_matrix[$this->x], -($this->bitmaps['width'] / 2) * $size);
		$xp = $this->scale($this->basis_matrix[$this->x], ($this->bitmaps['width'] / 2) * $size);
		$yn = $this->scale($this->basis_matrix[$this->y], -($this->bitmaps['height'] / 2) * $size);
		$yp = $this->scale($this->basis_matrix[$this->y], ($this->bitmaps['height'] / 2) * $size);

		$p = array();
		$p[0] = $this->sum2($xn, $yn);
		$p[1] = $this->sum2($xp, $yn);
		$p[2] = $this->sum2($xp, $yp);
		$p[3] = $this->sum2($xn, $yp);

		$min_x = $max_x = $p[0][0];
		$min_y = $max_y = $p[0][1];

		for ($i = 1; $i < 4; ++$i)
		{
			$min_x = ($min_x > $p[$i][0]) ? $p[$i][0] : $min_x;
			$min_y = ($min_y > $p[$i][1]) ? $p[$i][1] : $min_y;
			$max_x = ($max_x < $p[$i][0]) ? $p[$i][0] : $max_x;
			$max_y = ($max_y < $p[$i][1]) ? $p[$i][1] : $max_y;
		}

		return array($min_x, $min_y, $max_x, $max_y);
	}
}

/**
* Return bitmaps
*/
function captcha_bitmaps()
{
	return array(
		'width'		=> 9,
		'height'	=> 15,
		'data'		=> array(
		'A' => array(
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,1,0,1,0,0,0),
			array(0,0,0,1,0,1,0,0,0),
			array(0,0,0,1,0,1,0,0,0),
			array(0,0,1,0,0,0,1,0,0),
			array(0,0,1,0,0,0,1,0,0),
			array(0,0,1,0,0,0,1,0,0),
			array(0,1,0,0,0,0,0,1,0),
			array(0,1,0,0,0,0,0,1,0),
			array(0,1,1,1,1,1,1,1,0),
			array(0,1,0,0,0,0,0,1,0),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
		),
		'B' => array(
			array(1,1,1,1,1,1,1,0,0),
			array(1,0,0,0,0,0,0,1,0),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,1,0),
			array(1,1,1,1,1,1,1,0,0),
			array(1,0,0,0,0,0,0,1,0),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,1,0),
			array(1,1,1,1,1,1,1,0,0),
		),
		'C' => array(
			array(0,0,1,1,1,1,1,0,0),
			array(0,1,0,0,0,0,0,1,0),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(0,1,0,0,0,0,0,1,0),
			array(0,0,1,1,1,1,1,0,0),
		),
		'D' => array(
			array(1,1,1,1,1,1,1,0,0),
			array(1,0,0,0,0,0,0,1,0),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,1,0),
			array(1,1,1,1,1,1,1,0,0),
		),
		'E' => array(
			array(1,1,1,1,1,1,1,1,1),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,1,1,1,1,1,1,1,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,1,1,1,1,1,1,1,1),
		),
		'F' => array(
			array(1,1,1,1,1,1,1,1,1),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,1,1,1,1,1,1,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
		),
		'G' => array(
			array(0,0,1,1,1,1,1,0,0),
			array(0,1,0,0,0,0,0,1,0),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,1,1,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(0,1,0,0,0,0,0,1,0),
			array(0,0,1,1,1,1,1,0,0),
		),
		'H' => array(
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,1,1,1,1,1,1,1,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
		),
		'I' => array(
			array(1,1,1,1,1,1,1,1,1),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(1,1,1,1,1,1,1,1,1),
		),
		'J' => array(
			array(1,1,1,1,1,1,1,1,1),
			array(0,0,0,0,0,1,0,0,0),
			array(0,0,0,0,0,1,0,0,0),
			array(0,0,0,0,0,1,0,0,0),
			array(0,0,0,0,0,1,0,0,0),
			array(0,0,0,0,0,1,0,0,0),
			array(0,0,0,0,0,1,0,0,0),
			array(0,0,0,0,0,1,0,0,0),
			array(0,0,0,0,0,1,0,0,0),
			array(0,0,0,0,0,1,0,0,0),
			array(0,0,0,0,0,1,0,0,0),
			array(1,0,0,0,0,1,0,0,0),
			array(1,0,0,0,0,1,0,0,0),
			array(0,1,0,0,1,0,0,0,0),
			array(0,0,1,1,0,0,0,0,0),
		),
		'K' => array(	// New 'K', supplied by NeoThermic
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,1,0),
			array(1,0,0,0,0,0,1,0,0),
			array(1,0,0,0,0,1,0,0,0),
			array(1,0,0,0,1,0,0,0,0),
			array(1,0,0,1,0,0,0,0,0),
			array(1,0,1,0,0,0,0,0,0),
			array(1,1,0,0,0,0,0,0,0),
			array(1,0,1,0,0,0,0,0,0),
			array(1,0,0,1,0,0,0,0,0),
			array(1,0,0,0,1,0,0,0,0),
			array(1,0,0,0,0,1,0,0,0),
			array(1,0,0,0,0,0,1,0,0),
			array(1,0,0,0,0,0,0,1,0),
			array(1,0,0,0,0,0,0,0,1),
		),
		'L' => array(
			array(0,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,1,1,1,1,1,1,1,1),
		),
		'M' => array(
			array(1,1,0,0,0,0,0,1,1),
			array(1,1,0,0,0,0,0,1,1),
			array(1,0,1,0,0,0,1,0,1),
			array(1,0,1,0,0,0,1,0,1),
			array(1,0,1,0,0,0,1,0,1),
			array(1,0,0,1,0,1,0,0,1),
			array(1,0,0,1,0,1,0,0,1),
			array(1,0,0,1,0,1,0,0,1),
			array(1,0,0,0,1,0,0,0,1),
			array(1,0,0,0,1,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
		),
		'N' => array(
			array(1,1,0,0,0,0,0,0,1),
			array(1,1,0,0,0,0,0,0,1),
			array(1,0,1,0,0,0,0,0,1),
			array(1,0,1,0,0,0,0,0,1),
			array(1,0,0,1,0,0,0,0,1),
			array(1,0,0,1,0,0,0,0,1),
			array(1,0,0,0,1,0,0,0,1),
			array(1,0,0,0,1,0,0,0,1),
			array(1,0,0,0,1,0,0,0,1),
			array(1,0,0,0,0,1,0,0,1),
			array(1,0,0,0,0,1,0,0,1),
			array(1,0,0,0,0,0,1,0,1),
			array(1,0,0,0,0,0,1,0,1),
			array(1,0,0,0,0,0,0,1,1),
			array(1,0,0,0,0,0,0,1,1),
		),
		'O' => array(
			array(0,0,1,1,1,1,1,0,0),
			array(0,1,0,0,0,0,0,1,0),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(0,1,0,0,0,0,0,1,0),
			array(0,0,1,1,1,1,1,0,0),
		),
		'P' => array(
			array(1,1,1,1,1,1,1,0,0),
			array(1,0,0,0,0,0,0,1,0),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,1,0),
			array(1,1,1,1,1,1,1,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
		),
		'Q' => array(
			array(0,0,1,1,1,1,1,0,0),
			array(0,1,0,0,0,0,0,1,0),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,1,0,0,1),
			array(1,0,0,0,0,0,1,0,1),
			array(0,1,0,0,0,0,0,1,0),
			array(0,0,1,1,1,1,1,0,1),
		),
		'R' => array(
			array(1,1,1,1,1,1,1,0,0),
			array(1,0,0,0,0,0,0,1,0),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,1,0),
			array(1,1,1,1,1,1,1,0,0),
			array(1,1,1,0,0,0,0,0,0),
			array(1,0,0,1,0,0,0,0,0),
			array(1,0,0,0,1,0,0,0,0),
			array(1,0,0,0,0,1,0,0,0),
			array(1,0,0,0,0,0,1,0,0),
			array(1,0,0,0,0,0,0,1,0),
			array(1,0,0,0,0,0,0,0,1),
		),
		'S' => array(
			array(0,0,1,1,1,1,1,0,0),
			array(0,1,0,0,0,0,0,1,0),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(0,1,0,0,0,0,0,0,0),
			array(0,0,1,1,1,1,1,0,0),
			array(0,0,0,0,0,0,0,1,0),
			array(0,0,0,0,0,0,0,0,1),
			array(0,0,0,0,0,0,0,0,1),
			array(0,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(0,1,0,0,0,0,0,1,0),
			array(0,0,1,1,1,1,1,0,0),
		),
		'T' => array(
			array(1,1,1,1,1,1,1,1,1),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
		),
		'U' => array(
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(0,1,0,0,0,0,0,1,0),
			array(0,0,1,1,1,1,1,0,0),
		),
		'V' => array(
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(0,1,0,0,0,0,0,1,0),
			array(0,1,0,0,0,0,0,1,0),
			array(0,1,0,0,0,0,0,1,0),
			array(0,0,1,0,0,0,1,0,0),
			array(0,0,1,0,0,0,1,0,0),
			array(0,0,1,0,0,0,1,0,0),
			array(0,0,1,0,0,0,1,0,0),
			array(0,0,0,1,0,1,0,0,0),
			array(0,0,0,1,0,1,0,0,0),
			array(0,0,0,1,0,1,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
		),
		'W' => array(	// New 'W', supplied by MHobbit
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,1,0,0,0,1),
			array(1,0,0,0,1,0,0,0,1),
			array(1,0,0,1,0,1,0,0,1),
			array(1,0,0,1,0,1,0,0,1),
			array(1,0,0,1,0,1,0,0,1),
			array(1,0,1,0,0,0,1,0,1),
			array(1,0,1,0,0,0,1,0,1),
			array(1,0,1,0,0,0,1,0,1),
			array(1,1,0,0,0,0,0,1,1),
			array(1,1,0,0,0,0,0,1,1),
		),
		'X' => array(
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(0,1,0,0,0,0,0,1,0),
			array(0,1,0,0,0,0,0,1,0),
			array(0,0,1,0,0,0,1,0,0),
			array(0,0,0,1,0,1,0,0,0),
			array(0,0,0,1,0,1,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,1,0,1,0,0,0),
			array(0,0,0,1,0,1,0,0,0),
			array(0,0,1,0,0,0,1,0,0),
			array(0,1,0,0,0,0,1,0,0),
			array(0,1,0,0,0,0,0,1,0),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
		),
		'Y' => array(
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(0,1,0,0,0,0,0,1,0),
			array(0,1,0,0,0,0,0,1,0),
			array(0,0,1,0,0,0,1,0,0),
			array(0,0,1,0,0,0,1,0,0),
			array(0,0,0,1,0,1,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
		),
		'Z' => array(	// New 'Z' supplied by Anon
			array(1,1,1,1,1,1,1,1,1),
			array(1,0,0,0,0,0,0,0,1),
			array(0,0,0,0,0,0,0,0,1),
			array(0,0,0,0,0,0,0,1,0),
			array(0,0,0,0,0,0,1,0,0),
			array(0,0,0,0,0,1,0,0,0),
			array(0,0,0,0,0,1,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,1,0,0,0,0,0),
			array(0,0,0,1,0,0,0,0,0),
			array(0,0,1,0,0,0,0,0,0),
			array(0,1,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,1),
			array(1,1,1,1,1,1,1,1,1),
		),
		'1' => array(
			array(0,0,0,1,1,0,0,0,0),
			array(0,0,1,0,1,0,0,0,0),
			array(0,1,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,1,1,1,1,1,1,1,0),
		),
		'2' => array(	// New '2' supplied by Anon
			array(0,0,0,1,1,1,0,0,0),
			array(0,0,1,0,0,0,1,0,0),
			array(0,1,0,0,0,0,1,1,0),
			array(0,0,0,0,0,0,0,0,1),
			array(0,0,0,0,0,0,0,0,1),
			array(0,0,0,0,0,0,0,1,1),
			array(0,0,0,0,0,0,0,1,0),
			array(0,0,0,0,0,0,1,0,0),
			array(0,0,0,0,0,1,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,1,0,0,0,0,0),
			array(0,0,1,0,0,0,0,0,0),
			array(0,1,0,0,0,0,0,0,0),
			array(1,1,1,1,1,1,1,1,1),
			array(0,0,0,0,0,0,0,0,0),
		),
		'3' => array(
			array(0,0,1,1,1,1,1,0,0),
			array(0,1,0,0,0,0,0,1,0),
			array(1,0,0,0,0,0,0,0,1),
			array(0,0,0,0,0,0,0,0,1),
			array(0,0,0,0,0,0,0,0,1),
			array(0,0,0,0,0,0,0,0,1),
			array(0,0,0,0,0,0,0,1,0),
			array(0,0,0,0,0,1,1,0,0),
			array(0,0,0,0,0,0,0,1,0),
			array(0,0,0,0,0,0,0,0,1),
			array(0,0,0,0,0,0,0,0,1),
			array(0,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(0,1,0,0,0,0,0,1,0),
			array(0,0,1,1,1,1,1,0,0),
		),
		'4' => array(
			array(0,0,0,0,0,0,1,1,0),
			array(0,0,0,0,0,1,0,1,0),
			array(0,0,0,0,1,0,0,1,0),
			array(0,0,0,1,0,0,0,1,0),
			array(0,0,1,0,0,0,0,1,0),
			array(0,1,0,0,0,0,0,1,0),
			array(1,0,0,0,0,0,0,1,0),
			array(1,0,0,0,0,0,0,1,0),
			array(1,1,1,1,1,1,1,1,1),
			array(0,0,0,0,0,0,0,1,0),
			array(0,0,0,0,0,0,0,1,0),
			array(0,0,0,0,0,0,0,1,0),
			array(0,0,0,0,0,0,0,1,0),
			array(0,0,0,0,0,0,0,1,0),
			array(0,0,0,0,0,0,0,1,0),
		),
		'5' => array(
			array(1,1,1,1,1,1,1,1,1),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(0,1,0,0,0,0,0,0,0),
			array(0,0,1,1,1,1,1,0,0),
			array(0,0,0,0,0,0,0,1,0),
			array(0,0,0,0,0,0,0,0,1),
			array(0,0,0,0,0,0,0,0,1),
			array(0,0,0,0,0,0,0,0,1),
			array(0,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(0,1,0,0,0,0,0,1,0),
			array(0,0,1,1,1,1,1,0,0),
		),
		'6' => array(
			array(0,0,1,1,1,1,1,0,0),
			array(0,1,0,0,0,0,0,1,0),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,1,1,1,1,0,0),
			array(1,0,1,0,0,0,0,1,0),
			array(1,1,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(0,1,0,0,0,0,0,1,0),
			array(0,0,1,1,1,1,1,0,0),
		),
		'7' => array(
			array(1,1,1,1,1,1,1,1,1),
			array(0,0,0,0,0,0,0,0,1),
			array(0,0,0,0,0,0,0,1,0),
			array(0,0,0,0,0,0,0,1,0),
			array(0,0,0,0,0,0,1,0,0),
			array(0,0,0,0,0,1,0,0,0),
			array(0,0,0,0,0,1,0,0,0),
			array(0,0,0,0,1,0,0,0,0),
			array(0,0,0,1,0,0,0,0,0),
			array(0,0,0,1,0,0,0,0,0),
			array(0,0,1,0,0,0,0,0,0),
			array(0,1,0,0,0,0,0,0,0),
			array(0,1,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
			array(1,0,0,0,0,0,0,0,0),
		),
		'8' => array(
			array(0,0,1,1,1,1,1,0,0),
			array(0,1,0,0,0,0,0,1,0),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(0,1,0,0,0,0,0,1,0),
			array(0,0,1,1,1,1,1,0,0),
			array(0,1,0,0,0,0,0,1,0),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(0,1,0,0,0,0,0,1,0),
			array(0,0,1,1,1,1,1,0,0),
		),
		'9' => array(
			array(0,0,1,1,1,1,1,0,0),
			array(0,1,0,0,0,0,0,1,0),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,1,1),
			array(0,1,0,0,0,0,1,0,1),
			array(0,0,1,1,1,1,0,0,1),
			array(0,0,0,0,0,0,0,0,1),
			array(0,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(1,0,0,0,0,0,0,0,1),
			array(0,1,0,0,0,0,0,1,0),
			array(0,0,1,1,1,1,1,0,0),
		),
		)
	);
}


/**
* Load True Type Fonts
*/
function captcha_load_ttf_fonts()
{
	static $load_files = array();

	if (sizeof($load_files) > 0)
	{
		return $load_files;
	}

	global $phpbb_root_path;

	$dr = opendir($phpbb_root_path . 'includes/captcha/fonts');
	while (false !== ($entry = readdir($dr)))
	{
		if (strtolower(pathinfo($entry, PATHINFO_EXTENSION)) == 'ttf')
		{
			$load_files[$entry] = $phpbb_root_path . 'includes/captcha/fonts/' . $entry;
		}
	}
	closedir($dr);

	return $load_files;
}


/**
* Return vectors
*/
function captcha_vectors()
{
	return array(
		'A' => array(
			array('line',	0.00,	0.00,	0.50,	1.00,	1.10714871779,		1.11803398875),
			array('line',	1.00,	0.00,	0.50,	1.00,	2.0344439358,		1.11803398875),
			array('line',	0.25,	0.50,	0.75,	0.50,	0.00,				0.50),
		),
		'B' => array(
			array('line',	0.00,	0.00,	0.00,	1.00,	1.57079632679,		1.00),
			array('line',	0.00,	1.00,	0.70,	1.00,	0.00,				0.70),
			array('line',	0.00,	0.50,	0.70,	0.50,	0.00,				0.70),
			array('line',	0.00,	0.00,	0.70,	0.00,	0.00,				0.70),
			array('arc',	0.70,	0.75,	0.60,	0.50,	270,	90),
			array('arc',	0.70,	0.25,	0.60,	0.50,	270,	90),
		),
		'C' => array(
			array('arc',	0.50,	0.50,	1.00,	1.00,	45,		315),
		),
		'D' => array(
			array('line',	0.00,	0.00,	0.00,	1.00,	1.57079632679,		1.00),
			array('line',	0.00,	0.00,	0.50,	0.00,	0.00,				0.50),
			array('line',	0.00,	1.00,	0.50,	1.00,	0.00,				0.50),
			array('arc',	0.50,	0.50,	1.00,	1.00,	270,	90),
		),
		'E' => array(
			array('line',	0.00,	0.00,	1.00,	0.00,	0.00,				1.00),
			array('line',	0.00,	0.00,	0.00,	1.00,	1.57079632679,		1.00),
			array('line',	0.00,	1.00,	1.00,	1.00,	0.00,				1.00),
			array('line',	0.00,	0.50,	0.50,	0.50,	0.00,				0.50),
		),
		'F' => array(
			array('line',	0.00,	0.00,	0.00,	1.00,	1.57079632679,		1.00),
			array('line',	0.00,	1.00,	1.00,	1.00,	0.00,				1.00),
			array('line',	0.00,	0.50,	0.50,	0.50,	0.00,				0.50),
		),
		'G' => array(
			array('line',	0.50,	0.50,	1.00,	0.50,	0.00,				0.50),
			array('line',	1.00,	0.00,	1.00,	0.50,	1.57079632679,		0.50),
			array('arc',	0.50,	0.50,	1.00,	1.00,	0,		315),
		),
		'H' => array(
			array('line',	0.00,	0.00,	0.00,	1.00,	1.57079632679,		1.00),
			array('line',	1.00,	0.00,	1.00,	1.00,	1.57079632679,		1.00),
			array('line',	0.00,	0.50,	1.00,	0.50,	0.00,	1.00),
		),
		'I' => array(
			array('line',	0.00,	0.00,	1.00,	0.00,	0.00,				1.00),
			array('line',	0.00,	1.00,	1.00,	1.00,	0.00,				1.00),
			array('line',	0.50,	0.00,	0.50,	1.00,	1.57079632679,		1.00),
		),
		'J' => array(
			array('line',	1.00,	1.00,	1.00,	0.25,	-1.57079632679,		0.75),
			array('arc',	0.50,	0.25,	1.00,	0.50,	0,		180),
		),
		'K' => array(
			array('line',	0.00,	0.00,	0.00,	1.00,	1.57079632679,		1.00),
			array('line',	0.00,	0.50,	1.00,	1.00,	0.463647609001,		1.11803398875),
			array('line',	0.00,	0.50,	1.00,	0.00,	-0.463647609001,	1.11803398875),
		),
		'L' => array(
			array('line',	0.00,	0.00,	0.00,	1.00,	1.57079632679,		1.00),
			array('line',	0.00,	0.00,	1.00,	0.00,	0.00,				1.00),
		),
		'M' => array(
			array('line',	0.00,	0.00,	0.00,	1.00,	1.57079632679,		1.00),
			array('line',	0.50,	0.50,	0.00,	1.00,	2.35619449019,		0.707106781187),
			array('line',	0.50,	0.50,	1.00,	1.00,	0.785398163397,		0.707106781187),
			array('line',	1.00,	0.00,	1.00,	1.00,	1.57079632679,		1.00),
		),
		'N' => array(
			array('line',	0.00,	0.00,	0.00,	1.00,	1.57079632679,		1.00),
			array('line',	0.00,	1.00,	1.00,	0.00,	-0.785398163397,	1.41421356237),
			array('line',	1.00,	0.00,	1.00,	1.00,	1.57079632679,		1.00),
		),
		'O' => array(
			array('arc',	0.50,	0.50,	1.00,	1.00,	0,		360),
		),
		'P' => array(
			array('line',	0.00,	0.00,	0.00,	1.00,	1.57079632679,		1.00),
			array('line',	0.00,	1.00,	0.70,	1.00,	0.00,				0.70),
			array('line',	0.00,	0.50,	0.70,	0.50,	0.00,				0.70),
			array('arc',	0.70,	0.75,	0.60,	0.50,	270,	90),
		),
		'Q' => array(
			array('line',	0.70,	0.30,	1.00,	0.00,	-0.785398163397,	0.424264068712),
			array('arc',	0.50,	0.50,	1.00,	1.00,	0,		360),
		),
		'R' => array(
			array('line',	0.00,	0.00,	0.00,	1.00,	1.57079632679,		1.00),
			array('line',	0.00,	1.00,	0.70,	1.00,	0.00,				0.70),
			array('line',	0.00,	0.50,	0.70,	0.50,	0.00,				0.70),
			array('line',	0.50,	0.50,	1.00,	0.00,	-0.785398163397,	0.707106781187),
			array('arc',	0.70,	0.75,	0.60,	0.50,	270,	90),
		),
		'S' => array(
			array('arc',	0.50,	0.75,	1.00,	0.50,	90,		360),
			array('arc',	0.50,	0.25,	1.00,	0.50,	270,	180),
		),
		'T' => array(
			array('line',	0.00,	1.00,	1.00,	1.00,	0.00,				1.00),
			array('line',	0.50,	0.00,	0.50,	1.00,	1.57079632679,		1.00),
		),
		'U' => array(
			array('line',	0.00,	1.00,	0.00,	0.25,	-1.57079632679,		0.75),
			array('line',	1.00,	1.00,	1.00,	0.25,	-1.57079632679,		0.75),
			array('arc',	0.50,	0.25,	1.00,	0.50,	0,		180),
		),
		'V' => array(
			array('line',	0.00,	1.00,	0.50,	0.00,	-1.10714871779,		1.11803398875),
			array('line',	1.00,	1.00,	0.50,	0.00,	-2.0344439358,		1.11803398875),
		),
		'W' => array(
			array('line',	0.00,	1.00,	0.25,	0.00,	-1.32581766367,		1.0307764064),
			array('line',	0.50,	0.50,	0.25,	0.00,	-2.0344439358,		0.559016994375),
			array('line',	0.50,	0.50,	0.75,	0.00,	-1.10714871779,		0.559016994375),
			array('line',	1.00,	1.00,	0.75,	0.00,	-1.81577498992,		1.0307764064),
		),
		'X' => array(
			array('line',	0.00,	1.00,	1.00,	0.00,	-0.785398163397,	1.41421356237),
			array('line',	0.00,	0.00,	1.00,	1.00,	0.785398163397,		1.41421356237),
		),
		'Y' => array(
			array('line',	0.00,	1.00,	0.50,	0.50,	-0.785398163397,	0.707106781187),
			array('line',	1.00,	1.00,	0.50,	0.50,	-2.35619449019,		0.707106781187),
			array('line',	0.50,	0.50,	0.50,	0.00,	-1.57079632679,		0.50),
		),
		'Z' => array(
			array('line',	0.00,	1.00,	1.00,	1.00,	0.00,				1.00),
			array('line',	0.00,	0.00,	1.00,	1.00,	0.785398163397,		1.41421356237),
			array('line',	0.00,	0.00,	1.00,	0.00,	0.00,				1.00),
		),
		'1' => array(
			array('line',	0.00,	0.75,	0.50,	1.00,	0.463647609001,		0.559016994375),
			array('line',	0.50,	0.00,	0.50,	1.00,	1.57079632679,		1.00),
			array('line',	0.00,	0.00,	1.00,	0.00,	0.00,				1.00),
		),
		'2' => array(
			array('line',	0.00,	0.00,	1.00,	0.00,	0.00,				1.00),
			array('arc',	0.50,	0.70,	1.00,	0.60,	180,	360),
			array('arc',	0.50,	0.70,	1.00,	0.70,	0,		90),
			array('arc',	0.50,	0.00,	1.00,	0.70,	180,	270),
		),
		'3' => array(
			array('arc',	0.50,	0.75,	1.00,	0.50,	180,	90),
			array('arc',	0.50,	0.25,	1.00,	0.50,	270,	180),
		),
		'4' => array(
			array('line',	0.70,	0.00,	0.70,	1.00,	1.57079632679,		1.00),
			array('line',	0.00,	0.50,	0.70,	1.00,	0.620249485983,		0.860232526704),
			array('line',	0.00,	0.50,	1.00,	0.50,	0.00,				1.00),
		),
		'5' => array(
			array('line',	0.00,	1.00,	1.00,	1.00,	0.00,				1.00),
			array('line',	0.00,	1.00,	0.00,	0.60,	-1.57079632679,		0.4),
			array('line',	0.00,	0.60,	0.50,	0.60,	0.00,				0.50),
			array('arc',	0.50,	0.30,	1.00,	0.60,	270,	180),
		),
		'6' => array(
			array('arc',	0.50,	0.50,	1.00,	1.00,	90,		315),
			array('arc',	0.50,	0.30,	0.80,	0.60,	0,		360),
		),
		'7' => array(
			array('line',	0.00,	1.00,	1.00,	1.00,	0.00,				1.00),
			array('line',	0.50,	0.00,	1.00,	1.00,	1.10714871779,		1.11803398875),
		),
		'8' => array(
			array('arc',	0.50,	0.75,	1.00,	0.50,	0,		360),
			array('arc',	0.50,	0.25,	1.00,	0.50,	0,		360),
		),
		'9' => array(
			array('arc',	0.50,	0.50,	1.00,	1.00,	270,	135),
			array('arc',	0.50,	0.70,	0.80,	0.60,	0,		360),
		)
	);
}

?>