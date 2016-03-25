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

class char_cube3d
{
	var $bitmap;
	var $bitmap_width;
	var $bitmap_height;

	var $basis_matrix = array(array(1, 0, 0), array(0, 1, 0), array(0, 0, 1));
	var $abs_x = array(1, 0);
	var $abs_y = array(0, 1);
	var $x = 0;
	var $y = 1;
	var $z = 2;
	var $letter = '';

	/**
	*/
	function __construct(&$bitmaps, $letter)
	{
		$this->bitmap			= $bitmaps['data'][$letter];
		$this->bitmap_width		= $bitmaps['width'];
		$this->bitmap_height	= $bitmaps['height'];

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
		$weight = (abs($this->basis_matrix[$this->x][1]) - abs($this->basis_matrix[$this->x][0])) + (abs($this->basis_matrix[$this->y][0]) - abs($this->basis_matrix[$this->y][1]));

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
	* Draw a character
	*/
	function drawchar($scale, $xoff, $yoff, $img, $background, $colours)
	{
		$width	= $this->bitmap_width;
		$height	= $this->bitmap_height;
		$bitmap	= $this->bitmap;

		$colour1 = $colours[array_rand($colours)];
		$colour2 = $colours[array_rand($colours)];

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

					imagefilledpolygon($img, $this->gen_poly($xo, $yo, $origin, $xvec, $x_corner,$zvec), 4, $colour1);
					imagefilledpolygon($img, $this->gen_poly($xo, $yo, $origin, $yvec, $y_corner,$zvec), 4, $colour2);

					$face = $this->gen_poly($xo, $yo, $origin, $xvec, $face_corner, $yvec);

					imagefilledpolygon($img, $face, 4, $background);
					imagepolygon($img, $face, 4, $colour1);
				}
			}
		}
	}

	/*
	* return a roughly acceptable range of sizes for rendering with this texttype
	*/
	function range()
	{
		return array(3, 4);
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
	*/
	function sum($vector1, $vector2)
	{
		return array($vector1[0] + $vector2[0], $vector1[1] + $vector2[1], $vector1[2] + $vector2[2]);
	}

	/**
	*/
	function sum2($vector1, $vector2)
	{
		return array($vector1[0] + $vector2[0], $vector1[1] + $vector2[1]);
	}

	/**
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
		$xn = $this->scale($this->basis_matrix[$this->x], -($this->bitmap_width / 2) * $size);
		$xp = $this->scale($this->basis_matrix[$this->x], ($this->bitmap_width / 2) * $size);
		$yn = $this->scale($this->basis_matrix[$this->y], -($this->bitmap_height / 2) * $size);
		$yp = $this->scale($this->basis_matrix[$this->y], ($this->bitmap_height / 2) * $size);

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
