<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\captcha;

class colour_manager
{
	var $img;
	var $mode;
	var $colours;
	var $named_colours;

	/**
	* Create the colour manager, link it to the image resource
	*/
	function __construct($img, $background = false, $mode = 'ahsv')
	{
		$this->img = $img;
		$this->mode = $mode;
		$this->colours = array();
		$this->named_colours = array();

		if ($background !== false)
		{
			$bg = $this->allocate_named('background', $background);
			imagefill($this->img, 0, 0, $bg);
		}
	}

	/**
	* Lookup a named colour resource
	*/
	function get_resource($named_colour)
	{
		if (isset($this->named_colours[$named_colour]))
		{
			return $this->named_colours[$named_colour];
		}

		if (isset($this->named_rgb[$named_colour]))
		{
			return $this->allocate_named($named_colour, $this->named_rgb[$named_colour], 'rgb');
		}

		return false;
	}

	/**
	* Assign a name to a colour resource
	*/
	function name_colour($name, $resource)
	{
		$this->named_colours[$name] = $resource;
	}

	/**
	* names and allocates a colour resource
	*/
	function allocate_named($name, $colour, $mode = false)
	{
		$resource = $this->allocate($colour, $mode);

		if ($resource !== false)
		{
			$this->name_colour($name, $resource);
		}
		return $resource;
	}

	/**
	* allocates a specified colour into the image
	*/
	function allocate($colour, $mode = false)
	{
		if ($mode === false)
		{
			$mode = $this->mode;
		}

		if (!is_array($colour))
		{
			if (isset($this->named_rgb[$colour]))
			{
				return $this->allocate_named($colour, $this->named_rgb[$colour], 'rgb');
			}

			if (!is_int($colour))
			{
				return false;
			}

			$mode = 'rgb';
			$colour = array(255 & ($colour >> 16), 255 & ($colour >>  8), 255 & $colour);
		}

		if (isset($colour['mode']))
		{
			$mode = $colour['mode'];
			unset($colour['mode']);
		}

		if (isset($colour['random']))
		{
			unset($colour['random']);
			// everything else is params
			return $this->random_colour($colour, $mode);
		}

		$rgb		= $this->model_convert($colour, $mode, 'rgb');
		$store		= ($this->mode == 'rgb') ? $rgb : $this->model_convert($colour, $mode, $this->mode);
		$resource	= imagecolorallocate($this->img, $rgb[0], $rgb[1], $rgb[2]);
		$this->colours[$resource] = $store;

		return $resource;
	}

	/**
	* randomly generates a colour, with optional params
	*/
	function random_colour($params = array(), $mode = false)
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
					'max_saturation'	=> 80,		// 0 - 100
					'min_value'			=> 30,		// 0 - 100
					'max_value'			=> 80,		// 0 - 100
				);

				$alt = ($mode == 'ahsv') ? true : false;
				$params = array_merge($default_params, $params);

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

	/**
	*/
	function colour_scheme($resource, $include_original = true)
	{
		$mode = 'hsv';

		if (($pre = $this->get_resource($resource)) !== false)
		{
			$resource = $pre;
		}

		$colour = $this->model_convert($this->colours[$resource], $this->mode, $mode);
		$results = ($include_original) ? array($resource) : array();
		$colour2 = $colour3 = $colour4 = $colour;
		$colour2[0] += 150;
		$colour3[0] += 180;
		$colour4[0] += 210;

		$results[] = $this->allocate($colour2, $mode);
		$results[] = $this->allocate($colour3, $mode);
		$results[] = $this->allocate($colour4, $mode);

		return $results;
	}

	/**
	*/
	function mono_range($resource, $count = 5, $include_original = true)
	{
		if (is_array($resource))
		{
			$results = array();
			for ($i = 0, $size = sizeof($resource); $i < $size; ++$i)
			{
				$results = array_merge($results, $this->mono_range($resource[$i], $count, $include_original));
			}
			return $results;
		}

		$mode = (in_array($this->mode, array('hsv', 'ahsv'), true) ? $this->mode : 'ahsv');
		if (($pre = $this->get_resource($resource)) !== false)
		{
			$resource = $pre;
		}

		$colour = $this->model_convert($this->colours[$resource], $this->mode, $mode);

		$results = array();
		if ($include_original)
		{
			$results[] = $resource;
			$count--;
		}

		// This is a hard problem. I chicken out and try to maintain readability at the cost of less randomness.

		while ($count > 0)
		{
			$colour[1] = ($colour[1] + mt_rand(40,60)) % 99;
			$colour[2] = ($colour[2] + mt_rand(40,60));
			$results[] = $this->allocate($colour, $mode);
			$count--;
		}
		return $results;
	}

	/**
	* Convert from one colour model to another
	*/
	function model_convert($colour, $from_model, $to_model)
	{
		if ($from_model == $to_model)
		{
			return $colour;
		}

		switch ($to_model)
		{
			case 'hsv':

				switch ($from_model)
				{
					case 'ahsv':
						return $this->ah2h($colour);
						break;

					case 'rgb':
						return $this->rgb2hsv($colour);
						break;
				}
				break;

			case 'ahsv':

				switch ($from_model)
				{
					case 'hsv':
						return $this->h2ah($colour);
						break;

					case 'rgb':
						return $this->h2ah($this->rgb2hsv($colour));
						break;
				}
				break;

			case 'rgb':
				switch ($from_model)
				{
					case 'hsv':
						return $this->hsv2rgb($colour);
						break;

					case 'ahsv':
						return $this->hsv2rgb($this->ah2h($colour));
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
		$this->normalize_hue($hsv[0]);

		$h = $hsv[0];
		$s = min(1, max(0, $hsv[1] / 100));
		$v = min(1, max(0, $hsv[2] / 100));

		// calculate hue sector
		$hi = floor($hsv[0] / 60);

		// calculate opposite colour
		$p = $v * (1 - $s);

		// calculate distance between hex vertices
		$f = ($h / 60) - $hi;

		// coming in or going out?
		if (!($hi & 1))
		{
			$f = 1 - $f;
		}

		// calculate adjacent colour
		$q = $v * (1 - ($f * $s));

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

		// if max - min is 0, we want hue to be 0 anyway.
		$h = $max - $min;

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
		$this->normalize_hue($h);

		return array($h, $s * 100, $v * 100);
	}

	/**
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
			$ahue[0] = $this->ah2h($ahue[0]);
			return $ahue;
		}
		$this->normalize_hue($ahue);

		// blue through red is already ok
		if ($ahue >= 240)
		{
			return $ahue;
		}

		// ahue green is at 180
		if ($ahue >= 180)
		{
			// return (240 - (2 * (240 - $ahue)));
			return (2 * $ahue) - 240; // equivalent
		}

		// ahue yellow is at 120   (RYB rather than RGB)
		if ($ahue >= 120)
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
			$hue[0] = $this->h2ah($hue[0]);
			return $hue;
		}
		$this->normalize_hue($hue);

		// blue through red is already ok
		if ($hue >= 240)
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
