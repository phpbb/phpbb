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
					$char = hexdec(substr($seed, ($y << 2) + $x, 1));
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
			$cells = hexdec(substr($seed, 20 + ($i << 1), 2));
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
		$img = imagecreatetruecolor($img_x, $img_y);

		// Generate colors
		$c = new color_manager($img, array(
			'random'			=> true,
			'min_saturation'	=> 70,
			'min_value'			=> 65,
		));
		
		$primaries = $c->color_scheme('background', 'tetradic', false);
		
		$noise = array_shift($primaries);
		$noise = $c->mono_range($noise, 'value', 5, false);
		$primaries = $c->mono_range($primaries, 'value', 5, false);

		// Generate code characters
		$characters = array();
		$sizes = array();
		$bounding_boxes = array();
		$width_avail = $img_x;
		$code_num = sizeof($code);
		$char_class = $this->captcha_char('char_ttf');
		for ( $i = 0; $i < $code_num; ++$i )
		{
			$characters[$i] = new $char_class($code[$i]);
			list($min, $max) = $characters[$i]->range();
			$sizes[$i] = mt_rand($min, $max / 2);
			$box = $characters[$i]->dimensions($sizes[$i]);
			$width_avail -= ($box[2] - $box[0]);
			$bounding_boxes[$i] = $box;
		}

		// Redistribute leftover x-space
		$offset = array();
		for ( $i = 0; $i < $code_num; ++$i )
		{
			$denom = ($code_num - $i);
			$denom = max(1.5, $denom);
			$offset[$i] = mt_rand(0, (1.5 * $width_avail) / $denom);
			$width_avail -= $offset[$i];
		}

		// Add some line noise
		if ($config['policy_shape_noise_line'])
		{
			$this->noise_line($img, 0, 0, $img_x, $img_y, $c->r('background'), $primaries, $noise);
		}

		$real = mt_rand(0, 3);
		$patterns = array('', '', '', '');
		for ($i = 32; $i > 0; --$i)
		{
			$patterns[$i & 3] .= str_pad(dechex(mt_rand(0, 65535)), 4, '0', STR_PAD_LEFT);
		}


		for ($i = 0; $i < 4; ++$i)
		{
			/*if ($i)
			{
				$y = 5 + ($i * 60);
				imageline($img, 550, $y, 650, $y, $fontcolors[0]);
			}*/
			$this->draw_pattern($patterns[$i], $img, 525, 10 + ($i * 60), 575, ($i + 1) * 60, $primaries);
			if ($i == $real)
			{
				$this->draw_pattern($patterns[$i], $img, 25, 25, 225, 225, $primaries, 3);
				for ($j = 0; $j < $code_num; ++$j)
				{
					$character = new $char_class($code[$j]);
					$character->drawchar(25, 600 + ($j * 25), 35 + ($i * 60), $img, $c->r('background'), $primaries);
				}
			}
			else
			{
				$word = $this->get_char_string();
				for ($j = strlen($word) - 1; $j >= 0; --$j)
				{
					$character = new $char_class(substr($word, $j, 1));
					$character->drawchar(25, 600 + ($j * 25), 35 + ($i * 60), $img, $c->r('background'), $primaries);
				}
			}
		}

		$count = sizeof($user->lang['CAPTCHA']['shape']);
		$line_height = $img_y / ($count + 1);
		for ($i = 0; $i < $count; ++$i)
		{
			$text = $user->lang['CAPTCHA']['shape'][$i];
			$line_width = strlen($text) * 4.5; //  ( / 2, * 9 )
			imagestring($img, 6, ($img_x / 2) - $line_width - 1, $line_height * ($i + 1) - 1, $text, $c->r('black'));
			imagestring($img, 6, ($img_x / 2) - $line_width - 1, $line_height * ($i + 1) + 1, $text, $c->r('black'));
			imagestring($img, 6, ($img_x / 2) - $line_width + 1, $line_height * ($i + 1) + 1, $text, $c->r('black'));
			imagestring($img, 6, ($img_x / 2) - $line_width + 1, $line_height * ($i + 1) - 1, $text, $c->r('black'));
			imagestring($img, 6, ($img_x / 2) - $line_width, $line_height * ($i + 1), $text, $c->r('white'));
		}


		// Add some pixel noise
		if ($config['policy_shape_noise_pixel'])
		{
			$this->noise_pixel($img, 0, 0, $img_x, $img_y, $c->r('background'), $primaries, $noise, $config['policy_shape_noise_pixel']);
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
		$c = new color_manager($img, 'white');
		$c->allocate_named('primary', array(
			'random'			=> true,
			'min_saturation'	=> 50,
			'min_value'			=> 75,
		));
		$bg_colors		= $c->color_scheme('primary', 'triadic', false);
		$text_colors	= $c->mono_range('primary', 'saturation', 6);
		$bg_colors		= $c->mono_range($bg_colors, 'saturation', 6);
		
		// Specificy image portion dimensions.
		$count = sizeof($code);
		$cellsize = $img_x / $count;
		$y_range = min($cellsize, $img_y);
		$y_max = $img_y - $y_range;
		$y_off = array(); // consecutive vertical offset of characters
		$color = array(); // color of characters
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
		
		$range = 0.075;

		$chars = array_merge(range('A', 'Z'), range('1', '9'));

		// draw some characters. if they're within the vector spec of the code character, color them differently
		for ($i = 0; $i < 8000; ++$i)
		{
			$degree = mt_rand(-30, 30);
			$x = mt_rand(0, $img_x - 1);
			$y = mt_rand(0, $img_y);
			$text = $chars[array_rand($chars)];
			$char = $x / $cellsize;
			$meta_x = ((($x % $cellsize) / $cellsize) * 1.5) - 0.25;
			$meta_y = (($img_y - $y) - $y_off[$char]) / $y_range;
			$font = $fonts[array_rand($fonts)];
			
			$distance = vector_distance($map[$code[$char]], $meta_x, $meta_y, $range);

			$switch = !(rand() % 100);
			
			imagettftext($img, 10, $degree, $x, $y,
				(($distance <= $range) xor $switch) ?
					$c->r_rand($text_colors) :
					$c->r_rand($bg_colors),
				$font, $text);
			
		}

		// Send image
		$this->send_image($img);
	}

	function policy_stencil($code)
	{
		// Generate image
		$img_x = 800;
		$img_y = 250;
		$img		= imagecreatetruecolor($img_x, $img_y);
		$stencil	= imagecreatetruecolor($img_x, $img_y);

		$map = captcha_vectors();
		$fonts = captcha_load_ttf_fonts();

		// Generate colors
		$c = new color_manager($img, 'black');
		$cs = new color_manager($stencil, 'gray');
		
		$c->allocate_named('primary', array(
			'random'			=> true,
			'min_saturation'	=> 75,
			'min_value'			=> 80,
		));
		
		$secondary = $c->color_scheme('primary', 'triadic', false);

		//imagefill($stencil, 0, 0, $black2);
		//imagefill($img, 0, 0, $white1);

		$chars = array_merge(range('A', 'Z'), range('1', '9'));
		$step = 20;
		$density = 4;
		for ($i = 0; $i < $img_x; $i += $step)
		{
			for ($j = 0; $j < $img_y; $j += $step)
			{
				for ($k = 0; $k < $density; ++$k)
				{
					$degree = mt_rand(-30, 30);
					$x = mt_rand($i, $i + $step);
					$y = mt_rand($j, $j + $step);
					$char = $chars[array_rand($chars)];
					$font = $fonts[array_rand($fonts)];
					imagettftext($stencil, mt_rand(20, 30), $degree, $x, $y, $cs->r('black'), $font, $char);
				}
			}
		}

		for ($i = 0; $i < 3; ++$i)
		{
			$degree1 = mt_rand(-30, 30);
			$degree2 = mt_rand(-30, 30);
			$x1 = mt_rand(0, $img_x - 1);
			$x2 = mt_rand(0, $img_x - 1);
			$y1 = mt_rand(0, $img_y);
			$y2 = mt_rand(0, $img_y);
			$char1 = $chars[array_rand($chars)];
			$char2 = $chars[array_rand($chars)];
			$font1 = $fonts[array_rand($fonts)];
			$font2 = $fonts[array_rand($fonts)];
			
			imagettftext($img, mt_rand(75, 100), $degree1, $x1, $y1, $secondary[0], $font1, $char1);
			imagettftext($img, mt_rand(75, 100), $degree2, $x2, $y2, $secondary[1], $font2, $char2);
		}

		$characters = array();
		$sizes = array();
		$bounding_boxes = array();
		$width_avail = $img_x;
		$code_num = sizeof($code);
		$char_class = $this->captcha_char('char_ttf');
		for ($i = 0; $i < $code_num; ++$i)
		{
			$characters[$i] = new $char_class($code[$i]);
			$sizes[$i] = mt_rand(75, 100);
			$box = $characters[$i]->dimensions($sizes[$i]);
			$width_avail -= ($box[2] - $box[0]);
			$bounding_boxes[$i] = $box;
		}
		
		//
		// Redistribute leftover x-space
		//
		$offset = array();
		for ($i = 0; $i < $code_num; ++$i)
		{
			$denom = ($code_num - $i);
			$denom = max(1.5, $denom);
			$offset[$i] = mt_rand(0, (1.5 * $width_avail) / $denom);
			$width_avail -= $offset[$i];
		}

		// Draw the text
		$xoffset = 0;
		for ($i = 0; $i < $code_num; ++$i)
		{
			$characters[$i] = new $char_class($code[$i]);
			$dimm = $bounding_boxes[$i];
			$xoffset += ($offset[$i] - $dimm[0]);
			$yoffset = mt_rand(-$dimm[1], $img_y - $dimm[3]);
			$characters[$i]->drawchar($sizes[$i], $xoffset, $yoffset, $img, $c->r('background'), array($c->r('primary')));
			$xoffset += $dimm[2];
		}

		for ($i = 0; $i < $img_x; ++$i)
		{
			for ($j = 0; $j < $img_y; ++$j)
			{
				// if the stencil is not black, set the pixel in the image to gray
				if (imagecolorat($stencil, $i, $j))
				{
					imagesetpixel($img, $i, $j, $c->r('gray'));
				}
			}
		}

		// Send image
		$this->send_image($img);
	}

	function policy_cells($code)
	{
		global $user;
		// Generate image
		$img_x = 800;
		$img_y = 250;
		$img = imagecreate($img_x, $img_y);

		$fonts = captcha_load_ttf_fonts();

		$map = captcha_vectors();

		//
		// Generate colors
		//
		$c = new color_manager($img, 'white');
		
		$c->allocate_named('primary', array(
			'random'			=> true,
			'min_saturation'	=> 30,
			'min_value'			=> 65,
		));
		$primaries = $c->color_scheme('primary', 'tetradic');
		$bg_colors = $c->mono_range($primaries, 'value', 4, false);
		shuffle($primaries);
		shuffle($bg_colors);
		
		// Randomize the characters on the right and the left
		$left_characters	= array(); 
		$right_characters	= array();
		$chars				= array_merge(range('A', 'Z'), range('1', '9'));
		$chars_size			= sizeof($chars) - 1;
		$alpha				= range('A', 'Z');
		$alpha_size			= sizeof($alpha) - 1;
		for ($i = 0; $i < 25; ++$i)
		{
			$left_characters[$i]	= $alpha[mt_rand(0, $alpha_size)];
			$right_characters[$i]	= $chars[mt_rand(0, $chars_size)];
		}
		
		// Pick locations for our code, shuffle the rest into 3 separate queues
		$code_count = sizeof($code);
		$code_order = range(0, 24);
		shuffle($code_order);
		$remaining = array_splice($code_order, $code_count);
		$lineups = array($code_order, array(), array(), array());
		for ($i = sizeof($remaining) - 1; $i >= 0; --$i)
		{
			$lineups[mt_rand(1, 3)][] = $remaining[$i];
		}
		
		// overwrite the randomized left and right values with our code, where applicable
		for ($i = 0; $i < $code_count; ++$i)
		{
			$left_characters[$code_order[$i]]	= $i + 1;
			$right_characters[$code_order[$i]]	= $code[$i];
		}
		
		
		$offset1 = 50;
		$offset2 = 550;

		// Draw the cells and right hand characters
		$xs = $ys = array();
		for ($i = 0; $i < 25; ++$i)
		{
			$xs[$i] = $offset1 + 20 + (($i % 5) * 40) + mt_rand(-13, 13);
			$ys[$i] = 45 + (intval($i / 5) * 40) + mt_rand(-13, 13);

			$bg = $c->r_rand($bg_colors);
			
			// fill the cells with the background colors
			imagefilledrectangle($img,
				$offset1 + 1 + (($i % 5) * 40),		26 + (intval($i / 5) * 40),
				$offset1 + 39 + (($i % 5) * 40),	64 + (intval($i / 5) * 40),
				$bg);
			imagefilledrectangle($img,
				$offset2 + 1 + (($i % 5) * 40),		26 + (intval($i / 5) * 40),
				$offset2 + 39 + (($i % 5) * 40),	64 + (intval($i / 5) * 40),
				$bg);
			
			$level = intval($i / 5);
			$pos = $i % 5;
			imagettftext($img, 12, 0,
				$offset2 + 15 + ($pos * 40),		50 + ($level * 40),
				$c->is_dark($bg) ? $c->r('white'): $c->r('black'), $fonts['genr102.ttf'], $right_characters[$i]);
		}
		
		// draw the lines that appear between nodes (visual hint)
		for ($k = 0; $k < 4; ++$k )
		{
			$lineup = $lineups[$k];
			for ($i = 1, $size = sizeof($lineup); $i < $size; ++$i )
			{
				imageline($img,
					$xs[$lineup[$i - 1]],	$ys[$lineup[$i - 1]],
					$xs[$lineup[$i]],		$ys[$lineup[$i]],
					$primaries[$k]);
			}
		}
		
		// draw the actual nodes
		$textcolor = $c->is_dark($primaries[0]) ? $c->r('white') : $c->r('black');
		for ($k = 0; $k < 4; ++$k )
		{
			for ($j = 0, $size = sizeof($lineups[$k]); $j < $size; ++$j )
			{
				$i = $lineups[$k][$j];
				imagefilledellipse($img,
					$xs[$i],			$ys[$i],
					20,					20,
					$primaries[$k]);
				imagettftext($img, 12, 0,
					$xs[$i] - 5,		$ys[$i] + 5,
					$textcolor, $fonts['genr102.ttf'], $left_characters[$i]);
			}
		}
		
		// Draw poly behind explain text
		$points = mt_rand(3, 6);
		$arc = 360 / $points;
		$vertices = array();
		$c_x = $img_x / 2;
		$c_y = $img_y / 2;
		$radius = $img_y / 2.5;
		$start = deg2rad(mt_rand(0, 360));
		for ($i = 0; $i < $points; ++$i)
		{
			$rad = $start + deg2rad(($arc * $i) + mt_rand(-10, 10));
			$vertices[] = $c_x + (cos($rad) * $radius);
			$vertices[] = $c_y + (sin($rad) * $radius);
		}
		imagefilledpolygon($img, $vertices, $points, $primaries[mt_rand(0,3)]);
		
		// draw explain text
		$count = sizeof($user->lang['CAPTCHA']['cells']);
		$line_height = $img_y / ($count + 1);
		for ($i = 0; $i < $count; ++$i)
		{
			$text = $user->lang['CAPTCHA']['cells'][$i];
			$line_width = strlen($text) * 4.5; //  ( / 2, * 9 )
			imagestring($img, 6, ($img_x / 2) - $line_width - 1, $line_height * ($i + 1) - 1, $text, $c->r('black'));
			imagestring($img, 6, ($img_x / 2) - $line_width - 1, $line_height * ($i + 1) + 1, $text, $c->r('black'));
			imagestring($img, 6, ($img_x / 2) - $line_width + 1, $line_height * ($i + 1) + 1, $text, $c->r('black'));
			imagestring($img, 6, ($img_x / 2) - $line_width + 1, $line_height * ($i + 1) - 1, $text, $c->r('black'));
			imagestring($img, 6, ($img_x / 2) - $line_width, $line_height * ($i + 1), $text, $c->r('white'));
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
		$img = imagecreatetruecolor($img_x, $img_y);

		// Generate colors
		$c = new color_manager($img, array(
			'random'			=> true,
			'min_value'			=> 60,
		), 'hsv');

		$scheme = $c->color_scheme('background', 'triadic', false);
		$scheme = $c->mono_range($scheme, 'both', 10, false);
		shuffle($scheme);
		$bg_colors = array_splice($scheme, mt_rand(6, 12));

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
			$this->noise_line($img, 0, 0, $img_x, $img_y, $c->r('background'), $scheme, $bg_colors);
		}

		// Draw the text
		$xoffset = 0;
		for ($i = 0; $i < $code_num; ++$i)
		{
			$dimm = $bounding_boxes[$i];
			$xoffset += ($offset[$i] - $dimm[0]);
			$yoffset = mt_rand(-$dimm[1], $img_y - $dimm[3]);
			$characters[$i]->drawchar($sizes[$i], $xoffset, $yoffset, $img, $c->r('background'), $scheme);
			$xoffset += $dimm[2];
		}

		// Add some pixel noise
		if ($config['policy_entropy_noise_pixel'])
		{
			$this->noise_pixel($img, 0, 0, $img_x, $img_y, $c->r('background'), $scheme, $bg_colors, $config['policy_entropy_noise_pixel']);
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
		$img	= imagecreatetruecolor($img_x, $img_y);
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
		$c = new color_manager($img, array(
			'random'			=> true,
			'min_saturation'	=> 50,
			'min_value'			=> 65,
		));
		
		$r1 = $c->random_color(array(
			'min_value'		=> 20,
			'max_value'		=> 50,
		));
		$r2 = $c->random_color(array(
			'min_value'		=> 70,
			'max_value'		=> 100,
		));
		$rdata = mt_rand(0,1) ? array(
			$c->colors[$r1],
			$c->colors[$r2],
		) : array(
			$c->colors[$r2],
			$c->colors[$r1],
		);
		
		$colors = array();
		for ($i = 0; $i < 60; ++$i)
		{
			$colors[$i - 30] = $c->allocate(array(
				$rdata[0][0],
				(($i * $rdata[0][1]) + ((60 - $i) * $rdata[1][1])) / 60,
				(($i * $rdata[0][2]) + ((60 - $i) * $rdata[1][2])) / 60,
			));
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
			$img_pos_cur	= array($img_pos_prev[0] + $dxx,
									$img_pos_prev[1] + $dxy + $offset);
									
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
			$img_pos_cur	= array($img_buffer[$buffer_prev][0][0] + $dyx,
									$img_buffer[$buffer_prev][0][1] + $dyy + $offset);
			$img_pos_prev	= $img_pos_cur;

			$img_buffer[$buffer_cur][0]	= $img_pos_cur;
			
			for ($x = 1; $x <= $full_x; ++$x)
			{
				$cur_height		= $this->wave_height($x, $y, $subdivision_factor) + $this->grid_height($x, $y, 1, $x_grid, $y_grid);

				//height is a z-factor, not a y-factor
				$offset			= $cur_height - $prev_height;
				$img_pos_cur	= array($img_pos_prev[0] + $dxx,
										$img_pos_prev[1] + $dxy + $offset);

				//(height is float, index it to an int, get closest color)
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
		$img = imagecreatetruecolor($img_x, $img_y);

		// Generate colors
		$c = new color_manager($img, array(
			'random'			=> true,
			'min_saturation'	=> 70,
			'min_value'			=> 65,
		));
		
		$primaries = $c->color_scheme('background', 'triadic', false);
		$text = mt_rand(0, 1);
		$c->name_color('text', $primaries[$text]);
		$noise = $c->mono_range($primaries[1 - $text], 'both', 6, false);

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
			$this->noise_line($img, 0, 0, $img_x, $img_y, $c->r('background'), array($c->r('text')), $noise);
		}

		// Draw the text
		$min = 10 - $bounding_boxes[0][1];
		$max = ($img_y - 10) - $bounding_boxes[0][3];
		$med = ($max + $min) / 2;
		
		$yoffset = mt_rand($med, $max);
		$char_num = sizeof($characters);
		
		imagesetthickness($img, 3);
		for ($i = 0; $i < $char_num; ++$i)
		{
			if ($i)
			{
				imageline($img, $old_x + mt_rand(-3, 3), $old_y - 70 + mt_rand(-3, 3), $offset + mt_rand(-3, 3), $yoffset - 70 + mt_rand(-3, 3), $c->r('text'));
				imageline($img, $old_x + mt_rand(-3, 3), $old_y + 30 + mt_rand(-3, 3), $offset + mt_rand(-3, 3), $yoffset + 30 + mt_rand(-3, 3), $c->r('text')); 
			}
			
			$dimm = $bounding_boxes[$i];
			$offset -= $dimm[0];
			$characters[$i]->drawchar($char_size, $offset, $yoffset, $img, $c->r('background'), array($c->r('text')));
			
			$old_x = $offset;
			$old_y = $yoffset;
			
			$offset += $dimm[2];
			$offset -= (($dimm[2] - $dimm[0]) * $overlap_factor);
			$yoffset += ($i & 1) ? ((1 - $overlap_factor) * ($dimm[3] - $dimm[1])) : ((1 - $overlap_factor) * ($dimm[1] - $dimm[3]));
		}
		
		imagesetthickness($img, 1);

		// Add some medium pixel noise
		if ($config['policy_overlap_noise_pixel'])
		{
			$this->noise_pixel($img, 0, 0, $img_x, $img_y, $c->r('background'), array($c->r('text')), $noise, $config['policy_overlap_noise_pixel']);
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
		imagesetthickness($img, 2);
		$x1 = $min_x;
		$x2 = $max_x;
		$y1 = $min_y;
		$y2 = $min_y;

		do
		{
			$line = array_merge(
				array_fill(0, mt_rand(30, 60), $non_font[array_rand($non_font)]),
				array_fill(0, mt_rand(30, 60), $bg)
			);

			imagesetstyle($img, $line);
			imageline($img, $x1, $y1, $x2, $y2, IMG_COLOR_STYLED);

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
			$line = array_merge(
				array_fill(0, mt_rand(30, 60), $non_font[array_rand($non_font)]),
				array_fill(0, mt_rand(30, 60), $bg)
			);

			imagesetstyle($img, $line);
			imageline($img, $x1, $y1, $x2, $y2, IMG_COLOR_STYLED);

			$x1 += mt_rand(12, 35);
			$x2 += mt_rand(12, 35);
		}
		while ($x1 < $max_x && $x2 < $max_x);
		imagesetthickness($img, 1);
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
		$this->radius = 3;
		$this->density = 3;
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
							for ($k = 0; $k <= $this->density; ++$k)
							{
								$shift = mt_rand(-$this->radius, $this->radius);
								imagesetpixel($img,
											$xoff + ($veclist[1] * $width) + (($i * $dx) / $len) + ($inv_dx * $shift),
											$yoff + ((1 - $veclist[2]) * $height) + (($i * $dy) / $len) + ($inv_dy * $shift),
											$color);
							}
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
							$x_o = cos($theta);
							$y_o = sin($theta);
							$pre_width = ($veclist[3] * 0.5 * $width);
							$pre_height = ($veclist[4] * 0.5 * $height);
							for ($k = 0; $k <= $this->density; ++$k)
							{
								$shift = mt_rand(-$this->radius, $this->radius);
								$x_o1 = $x_o * ($pre_width + $shift);
								$y_o1 = $y_o * ($pre_height + $shift);
								imagesetpixel($img,
											$xoff + $x_c + $x_o1,
											$yoff + $y_c + $y_o1,
											$color);
							}
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
							if (!$p && !mt_rand(0, 9) && ($hatches > 3))
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

class color_manager
{
	var $img;
	var $mode;
	var $colors;
	var $named_colors;
	var $named_rgb = array(
		'red'		=> array(0xff, 0x00, 0x00),
		'maroon'	=> array(0x80, 0x00, 0x00),
		'yellow'	=> array(0xff, 0xff, 0x00),
		'olive'		=> array(0x80, 0x80, 0x00),
		'lime'		=> array(0x00, 0xff, 0x00),
		'green'		=> array(0x00, 0x80, 0x00),
		'aqua'		=> array(0x00, 0xff, 0xff),
		'teal'		=> array(0x00, 0x80, 0x80),
		'blue'		=> array(0x00, 0x00, 0xff),
		'navy'		=> array(0x00, 0x00, 0x80),
		'fuchsia'	=> array(0xff, 0x00, 0xff),
		'purple'	=> array(0x80, 0x00, 0x80),
		'white'		=> array(0xff, 0xff, 0xff),
		'silver'	=> array(0xc0, 0xc0, 0xc0),
		'gray'		=> array(0x80, 0x80, 0x80),
		'black'		=> array(0x00, 0x00, 0x00),
	);
	
	/**
	* Create the color manager, link it to
	* the image resource
	*/
	function color_manager($img, $background = false, $mode = 'ahsv')
	{
		$this->img = $img;
		$this->mode = $mode;
		$this->colors = array();
		$this->named_colors = array();
		if ($background !== false)
		{
			$bg = $this->allocate_named('background', $background);
			imagefill($this->img, 0, 0, $bg);
		}
	}
	
	/**
	* Lookup a named color resource
	*/
	function r($named_color)
	{
		if (isset($this->named_colors[$named_color]))
		{
			return $this->named_colors[$named_color];
		}
		if (isset($this->named_rgb[$named_color]))
		{
			return $this->allocate_named($named_color, $this->named_rgb[$named_color], 'rgb');
		}
		return false;
	}
	
	/**
	* Assign a name to a color resource
	*/
	function name_color($name, $resource)
	{
		$this->named_colors[$name] = $resource;
	}
	
	/**
	* random color resource
	*/
	function r_rand($colors)
	{
		return $colors[array_rand($colors)];
	}
	
	/**
	* names and allocates a color resource
	*/
	function allocate_named($name, $color, $mode = false)
	{
		$resource = $this->allocate($color, $mode);
		if ($resource !== false)
		{
			$this->name_color($name, $resource);
		}
		return $resource;
	}
	
	/**
	* allocates a specified color into the image
	*/
	function allocate($color, $mode = false)
	{
		if ($mode === false)
		{
			$mode = $this->mode;
		}
		if (!is_array($color))
		{
			if (isset($this->named_rgb[$color]))
			{
				return $this->allocate_named($color, $this->named_rgb[$color], 'rgb');
			}
			if (!is_int($color))
			{
				return false;
			}
			$mode = 'rgb';
			$color = array(
				255 & ($color >> 16),
				255 & ($color >>  8),
				255 & $color,
			);
		}
		
		if (isset($color['mode']))
		{
			$mode = $color['mode'];
			unset($color['mode']);
		}
		if (isset($color['random']))
		{
			unset($color['random']);
			// everything else is params
			return $this->random_color($color, $mode);
		}
		
		$rgb		= color_manager::model_convert($color, $mode, 'rgb');
		$store		= ($this->mode == 'rgb') ? $rgb : color_manager::model_convert($color, $mode, $this->mode);
		$resource	= imagecolorallocate($this->img, $rgb[0], $rgb[1], $rgb[2]);
		
		$this->colors[$resource] = $store;
		
		return $resource;
	}
	
	/**
	* randomly generates a color, with optional params
	*/
	function random_color($params = array(), $mode = false)
	{
		if ($mode === false)
		{
			$mode = $this->mode;
		}
		switch ($mode)
		{
			case 'rgb':
			
				// @TODO random rgb generation. do we intend to do this, or is it just too tedious?
			
			break;
			
			case 'ahsv':
			case 'hsv':
			default:
			
				$default_params = array(
					'hue_bias'			=> false,	// degree / 'r'/'g'/'b'/'c'/'m'/'y'   /'o'
					'hue_range'			=> false,	// if hue bias, then difference range +/- from bias
					'min_saturation'	=> 30,		// 0 - 100
					'max_saturation'	=> 100,		// 0 - 100
					'min_value'			=> 30,		// 0 - 100
					'max_value'			=> 100,		// 0 - 100
				);
				
				$alt = ($mode == 'ahsv');
				
				$params			= array_merge($default_params, $params);
				
				$min_hue		= 0;
				$max_hue		= 359;
				$min_saturation	= max(0, $params['min_saturation']);
				$max_saturation	= min(100, $params['max_saturation']);
				$min_value		= max(0, $params['min_value']);
				$max_value		= min(100, $params['max_value']);
				
				if ($params['hue_bias'] !== false)
				{
					if (is_numeric($params['hue_bias']))
					{
						$h = intval($params['hue_bias']) % 360;
					}
					else
					{
						switch ($params['hue_bias'])
						{
							case 'o':
								$h = $alt ?  60 :  30;
							break;

							case 'y':
								$h = $alt ? 120 :  60;
							break;

							case 'g':
								$h = $alt ? 180 : 120;
							break;

							case 'c':
								$h = $alt ? 210 : 180;
							break;

							case 'b':
								$h = 240;
							break;

							case 'm':
								$h = 300;
							break;

							case 'r':
							default:
								$h = 0;
							break;
						}
					}

					$min_hue = $h + 360;
					$max_hue = $h + 360;

					if ($params['hue_range'])
					{
						$min_hue -= min(180, $params['hue_range']);
						$max_hue += min(180, $params['hue_range']);
					}
				}

				$h = mt_rand($min_hue, $max_hue);
				$s = mt_rand($min_saturation, $max_saturation);
				$v = mt_rand($min_value, $max_value);

				return $this->allocate(array($h, $s, $v), $mode);

			break;
		}
	}
	
	function color_scheme($resource, $scheme, $include_original = true)
	{
		$mode = (in_array($this->mode, array('hsv', 'ahsv'), true) ? $this->mode : 'hsv');
		if (($pre = $this->r($resource)) !== false)
		{
			$resource = $pre;
		}
		$color = color_manager::model_convert($this->colors[$resource], $this->mode, $mode);
		$results = $include_original ? array($resource) : array();
		
		switch ($scheme)
		{
			case 'complement':
				
				$color2 = $color;
				$color2[0] += 180;
				$results[] = $this->allocate($color2, $mode);
				
			break;
			
			case 'triadic':
				
				$color2 = $color3 = $color;
				$color2[0] += 120;
				$color3[0] += 240;
				$results[] = $this->allocate($color2, $mode);
				$results[] = $this->allocate($color3, $mode);
				
			break;
			
			case 'tetradic':
				
				$color2 = $color3 = $color4 = $color;
				$color2[0] += 30;
				$color3[0] += 180;
				$color4[0] += 210;
				$results[] = $this->allocate($color2, $mode);
				$results[] = $this->allocate($color3, $mode);
				$results[] = $this->allocate($color4, $mode);
				
			break;
			
			case 'analogous':
				
				$color2 = $color3 = $color;
				$color2[0] += 30;
				$color3[0] += 330;
				$results[] = $this->allocate($color2, $mode);
				$results[] = $this->allocate($color3, $mode);
				
			break;
		}
		return $results;
	}
	
	function mono_range($resource, $type = 'both', $count = 5, $include_original = true)
	{
		if (is_array($resource))
		{
			$results = array();
			for ($i = 0, $size = sizeof($resource); $i < $size; ++$i)
			{
				$results = array_merge($results, $this->mono_range($resource[$i], $type, $count, $include_original));
			}
			return $results;
		}
		$mode = (in_array($this->mode, array('hsv', 'ahsv'), true) ? $this->mode : 'ahsv');
		if (($pre = $this->r($resource)) !== false)
		{
			$resource = $pre;
		}
		$color = color_manager::model_convert($this->colors[$resource], $this->mode, $mode);
		
		$results = array();
		if ($include_original)
		{
			$results[] = $resource;
			$count--;
		}
		
		switch ($type)
		{
			case 'saturation':
				
				$pivot		= $color[1];
				$num_below	= intval(($pivot * $count) / 100);
				$num_above	= $count - $num_below;
				
				for ($i = $num_above; $i > 0; --$i)
				{
					$color[1] = (($i * 100) + (($num_above - $i) * $pivot)) / $num_above;
					$results[] = $this->allocate($color, $mode);
				}
				
				++$num_below;
				
				for ($i = $num_below - 1; $i > 0; --$i)
				{
					$color[1] = ($i * $pivot) / $num_below;;
					$results[] = $this->allocate($color, $mode);
				}
				
				return $results;
				
			break;
			
			case 'value':
				
				$pivot		= $color[2];
				$num_below	= intval(($pivot * $count) / 100);
				$num_above	= $count - $num_below;
				
				for ($i = $num_above; $i > 0; --$i)
				{
					$color[2] = (($i * 100) + (($num_above - $i) * $pivot)) / $num_above;
					$results[] = $this->allocate($color, $mode);
				}
				
				++$num_below;
				
				for ($i = $num_below - 1; $i > 0; --$i)
				{
					$color[2] = ($i * $pivot) / $num_below;;
					$results[] = $this->allocate($color, $mode);
				}
				
				return $results;
				
			break;
			
			case 'both':
				
				// This is a hard problem. I chicken out and do an even triangle
				// the problem is that it disregards the original saturation and value,
				//		and as such a generated result might come arbitrarily close to our original value.
				$length = ceil(sqrt($count * 2));
				for ($i = $length; $i > 0; --$i)
				{
					for ($j = $i; $j > 0; --$j)
					{
						$color[1] = ($i * 100) / $length;
						$color[2] = ($j * 100) / $i;
						$results[] = $this->allocate($color, $mode);
						--$count;
						if (!$count)
						{
							return $results;
						}
					}
				}
				
				return $results;
				
			break;
		}
		
		return false;
	}
	
	function is_dark($resource)
	{
		$color = (($pre = $this->r($resource)) !== false) ? $this->colors[$pre] : $this->colors[$resource];
		switch($this->mode)
		{
			case 'ahsv':
			case 'hsv':
				
				return ($color[2] <= 50);
				
			break;

			case 'rgb':
				
				return (max($color[0], $color[1], $color[2]) <= 128);
				
			break;
		}
		return false;
	}
	
	/**
	* Convert from one color model to another
	*
	* note: properly following coding standards here yields unweildly amounts of whitespace, rendering this less than easily readable
	* 
	*/
	function model_convert($color, $from_model, $to_model)
	{
		if ($from_model == $to_model)
		{
			return $color;
		}
		switch ($to_model)
		{
			case 'hsv':
				switch($from_model)
				{
					case 'ahsv':
						return color_manager::ah2h($color);
					break;

					case 'rgb':
						return color_manager::rgb2hsv($color);
					break;
				}
			break;
			
			case 'ahsv':
				switch($from_model)
				{
					case 'hsv':
						return color_manager::h2ah($color);
					break;

					case 'rgb':
						return color_manager::h2ah(color_manager::rgb2hsv($color));
					break;
				}
			break;
			
			case 'rgb':
				switch($from_model)
				{
					case 'hsv':
						return color_manager::hsv2rgb($color);
					break;

					case 'ahsv':
						return color_manager::hsv2rgb(color_manager::ah2h($color));
					break;
				}
			break;
		}
		return false;
	}
	
	/**
	* Slightly altered from wikipedia's algorithm
	*/
	function hsv2rgb($hsv)
	{
		color_manager::normalize_hue($hsv[0]);
		$h = $hsv[0];
		$s = min(1, max(0, $hsv[1] / 100));
		$v = min(1, max(0, $hsv[2] / 100));
		
		$hi = floor($hsv[0] / 60);		// calculate hue sector

		$p = $v * (1 - $s);				// calculate opposite color
		$f = ($h / 60) - $hi;			// calculate distance between hex vertices
		if (!($hi & 1))					// coming in or going out?
		{
			$f = 1 - $f;
		}
		$q = $v * (1 - ($f * $s));		// calculate adjacent color
		
		switch ($hi)
		{
			case 0:
				$rgb = array($v, $q, $p);
			break;

			case 1:
				$rgb = array($q, $v, $p);
			break;

			case 2:
				$rgb = array($p, $v, $q);
			break;

			case 3:
				$rgb = array($p, $q, $v);
			break;

			case 4:
				$rgb = array($q, $p, $v);
			break;

			case 5:
				$rgb = array($v, $p, $q);
			break;

			default:
				return array(0, 0, 0);
			break;
		}
		return array(255 * $rgb[0], 255 * $rgb[1], 255 * $rgb[2]);
	}
	
	/**
	* (more than) Slightly altered from wikipedia's algorithm
	*/
	function rgb2hsv($rgb)
	{
		$r = min(255, max(0, $rgb[0]));
		$g = min(255, max(0, $rgb[1]));
		$b = min(255, max(0, $rgb[2]));
		$max = max($r, $g, $b);
		$min = min($r, $g, $b);
		
		$v = $max / 255;
		$s = (!$max) ? 0 : 1 - ($min / $max);
		$h = $max - $min;	// if max - min is 0, we want hue to be 0 anyway.
		if ($h)
		{
			switch ($max)
			{
				case $g:
					$h = 120 + (60 * ($b - $r) / $h);
				break;

				case $b:
					$h = 240 + (60 * ($r - $g) / $h);
				break;

				case $r:
					$h = 360 + (60 * ($g - $b) / $h);
				break;
			}
		}
		color_manager::normalize_hue($h);
		return array($h, $s * 100, $v * 100);
	}
	
	/**
	* Bleh
	*/
	function normalize_hue(&$hue)
	{
		$hue %= 360;
		if ($hue < 0)
		{
			$hue += 360;
		}
	}
	
	/**
	* Alternate hue to hue
	*/
	function ah2h($ahue)
	{
		if (is_array($ahue))
		{
			$ahue[0] = color_manager::ah2h($ahue[0]);
			return $ahue;
		}
		color_manager::normalize_hue($ahue);
		if ($ahue >= 240) // blue through red is already ok
		{
			return $ahue;
		}
		if ($ahue >= 180) // ahue green is at 180
		{
			// return (240 - (2 * (240 - $ahue)));
			return (2 * $ahue) - 240; // equivalent
		}
		if ($ahue >= 120) // ahue yellow is at 120   (RYB rather than RGB)
		{
			return $ahue - 60;
		}
		return $ahue / 2;
	}

	/**
	* hue to Alternate hue
	*/
	function h2ah($hue)
	{
		if (is_array($hue))
		{
			$hue[0] = color_manager::h2ah($hue[0]);
			return $hue;
		}
		color_manager::normalize_hue($hue);
		if ($hue >= 240) // blue through red is already ok
		{
			return $hue;
		}
		else if ($hue <= 60)
		{
			return $hue * 2;
		}
		else if ($hue <= 120)
		{
			return $hue + 60;
		}
		else
		{
			return ($hue + 240) / 2;
		}
	}
}

function vector_distance(&$char, $x, $y, $range = 0.1)
{
	$distance = $range + 1;
	foreach ($char AS $vector)
	{
		$d = $range + 1;
		switch ($vector[0])
		{
			case 'arc':
				
				$dx = $x - $vector[1];
				$dy = -($y - $vector[2]);				//because our arcs are upside-down....
				if (abs($dx) > abs($dy))
				{
					$phi = rad2deg(atan(($dy * $vector[3])/($dx * $vector[4])));
					$phi += ($dx < 0) ? 180 : 360;
					$phi %= 360;
				}
				else
				{
					$phi = 90 - rad2deg(atan(($dx * $vector[4])/($dy * $vector[3])));
					$phi += ($dy < 0) ? 180 : 360;
					$phi %= 360;
				}
								
				$internal = $vector[6] > $vector[5];	//external wraps over the 360 point
				$low = $phi >= $vector[5]; 					//phi is above our low range
				$high = $phi <= $vector[6];					//phi is below our high range.
				if ($internal ? ($low && $high) : ($low || $high))	//if it wraps, it can only be one or the other
				{
					$radphi = deg2rad($phi);						// i'm awesome. or not.
					$px = cos($radphi) * 0.5 * $vector[3];
					$py = sin($radphi) * 0.5 * $vector[4];
					$d = sqrt(pow($px - $dx, 2) + pow($py - $dy, 2));
				}
				
			break;
							
			case 'line':
				
				$bx = $x - $vector[1];
				$by = $y - $vector[2];
				$dx = cos($vector[5]);
				$dy = sin($vector[5]);
				$r = ($by * $dx) - ($bx * $dy);
				if ($r < $range && $r > -$range)
				{
					if (abs($dx) > abs($dy))
					{
						$s = (($bx + ($dy * $r)) / $dx);								
					}
					else
					{
						$s = (($by + ($dx * $r)) / $dy);
					}
					if ($s > -$range)
					{
						if ($s < 0)
						{
							$d = sqrt(pow($s, 2) + pow($r, 2));
						}
						elseif ($s < $vector[6])
						{
							$d = $r;
						}
						elseif ($s < $vector[6] + $range)
						{
							$d = sqrt(pow($s - $vector[6], 2) + pow($r, 2));
						}
					}
				}
				
			break;
		}
		$distance = min($distance, abs($d));
	}
	return $distance;
}
?>