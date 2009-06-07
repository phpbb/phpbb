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
* Wave3D CAPTCHA
*
* @author Robert Hetzler
* @package VC
*/
class captcha
{
	var $width		= 360;
	var $height		= 96;

	function execute($code, $seed)
	{
		global $starttime;

		// seed the random generator
		mt_srand($seed);

		// set height and width
		$img_x = $this->width;
		$img_y = $this->height;

		// Generate image
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
		$plane_x	= 100;
		$plane_y	= 30;

		$subdivision_factor = 3;

		// $box is the 4 points in img_space that correspond to the corners of the plane in 3-space
		$box = array(
			'upper_left'	=> array(
				'x' => mt_rand(5, 15),
				'y' => mt_rand(10, 15)
			),
			'upper_right'	=> array(
				'x' => mt_rand($img_x - 35, $img_x - 19),
				'y' => mt_rand(10, 17)
			),
			'lower_left'	=> array(
				'x' => mt_rand($img_x - 5, $img_x - 45),
				'y' => mt_rand($img_y - 0, $img_y - 15)
			),
		);

		$box['lower_right'] = array(
			'x' => $box['lower_left']['x'] + $box['upper_left']['x'] - $box['upper_right']['x'],
			'y' => $box['lower_left']['y'] + $box['upper_left']['y'] - $box['upper_right']['y'],
		);

		// TODO
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

 		$colors = array();

		$minr = mt_rand(20, 30);
		$ming = mt_rand(20, 30);
		$minb = mt_rand(20, 30);

		$maxr = mt_rand(150, 230);
		$maxg = mt_rand(150, 230);
		$maxb = mt_rand(150, 230);

		for ($i = -30; $i <= 30; ++$i)
		{
			$coeff1 = ($i + 12) / 45;
			$coeff2 = 1 - $coeff1;
			$colors[$i] = imagecolorallocate($img, ($coeff2 * $maxr) + ($coeff1 * $minr), ($coeff2 * $maxg) + ($coeff1 * $ming), ($coeff2 * $maxb) + ($coeff1 * $minb));
		}

		// $img_buffer is the last row of 3-space positions (converted to img-space), cached
		// (using this means we don't need to recalculate all 4 positions for each new polygon,
		// merely the newest point that we're adding, which is then cached.
		$img_buffer = array(array(), array());

		// In image-space, the x- and y-offset necessary to move one unit in the x-direction in planar-space
		$dxx = ($box['upper_right']['x'] - $box['upper_left']['x']) / ($subdivision_factor * $plane_x);
		$dxy = ($box['upper_right']['y'] - $box['upper_left']['y']) / ($subdivision_factor * $plane_x);

		// In image-space, the x- and y-offset necessary to move one unit in the y-direction in planar-space
		$dyx = ($box['lower_right']['x'] - $box['upper_left']['x']) / ($subdivision_factor * $plane_y);
		$dyy = ($box['lower_right']['y'] - $box['upper_left']['y']) / ($subdivision_factor * $plane_y);

		// Initial captcha-letter offset in planar-space
		$plane_offset_x = mt_rand(3, 8);
		$plane_offset_y = mt_rand( 12, 15);

		// character map
		$map = $this->captcha_bitmaps();

		// matrix
		$plane = array();

		// for each character, we'll silkscreen it into our boolean pixel plane
		for ($c = 0, $code_num = strlen($code); $c < $code_num; ++$c)
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
		$img_pos_prev	= $img_buffer[0][0] = array($box['upper_left']['x'], $box['upper_left']['y']);
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
			$img_pos_cur	= array($img_buffer[$buffer_prev][0][0] + $dyx, min($img_buffer[$buffer_prev][0][1] + $dyy + $offset, $img_y - 1));

			// make sure we don't try to write off the page
			$img_pos_prev	= $img_pos_cur;

			$img_buffer[$buffer_cur][0]	= $img_pos_cur;

			for ($x = 1; $x <= $full_x; ++$x)
			{
				$cur_height		= $this->wave_height($x, $y, $subdivision_factor) + $this->grid_height($x, $y, 1, $x_grid, $y_grid);

				// height is a z-factor, not a y-factor
				$offset			= $cur_height - $prev_height;
				$img_pos_cur	= array($img_pos_prev[0] + $dxx, $img_pos_prev[1] + $dxy + $offset);

				// height is float, index it to an int, get closest color
				$color			= $colors[intval($cur_height)];
				$img_pos_prev	= $img_pos_cur;
				$prev_height	= $cur_height;

				$y_index_old = intval(($y - 1) / $subdivision_factor);
				$y_index_new = intval($y / $subdivision_factor);
				$x_index_old = intval(($x - 1) / $subdivision_factor);
				$x_index_new = intval($x / $subdivision_factor);

				if (!empty($plane[$y_index_new][$x_index_new]))
				{
					$img_pos_cur[1]	+= $this->wave_height($x, $y, $subdivision_factor, 1) - 30 - $cur_height;
					$color			= $colors[20];
				}
				$img_pos_cur[1] = min($img_pos_cur[1], $img_y - 1);
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

		// Output image
		header('Content-Type: image/png');
		header('Cache-control: no-cache, no-store');
		//$mtime = explode(' ', microtime());
		//$totaltime = $mtime[0] + $mtime[1] - $starttime;

		//echo $totaltime . "<br />\n";
		//echo memory_get_usage() - $tmp;
		imagepng($img);
		imagedestroy($img);
	}

	function wave_height($x, $y, $factor = 1, $tweak = 0.7)
	{
		// stretch the wave. TODO: pretty it up
		$x = $x/5 + 180;
		$y = $y/4;
		return ((sin($x / (3 * $factor)) + sin($y / (3 * $factor))) * 10 * $tweak);
	}

	function grid_height($x, $y, $factor = 1, $x_grid, $y_grid)
	{
		return ((!($x % ($x_grid * $factor)) || !($y % ($y_grid * $factor))) ? 3 : 0);
	}

	function captcha_bitmaps()
	{
		return array(
			'width'		=> 9,
			'height'	=> 13,
			'data'		=> array(
				'A' => array(
					array(0,0,1,1,1,1,0,0,0),
					array(0,1,0,0,0,0,1,0,0),
					array(1,0,0,0,0,0,0,1,0),
					array(1,0,0,0,0,0,0,1,0),
					array(1,0,0,0,0,0,0,1,0),
					array(1,0,0,0,0,0,0,1,0),
					array(1,0,0,0,0,0,0,1,0),
					array(1,1,1,1,1,1,1,1,0),
					array(1,0,0,0,0,0,0,1,0),
					array(1,0,0,0,0,0,0,1,0),
					array(1,0,0,0,0,0,0,1,0),
					array(1,0,0,0,0,0,0,1,0),
					array(1,0,0,0,0,0,0,1,0),
					array(0,0,0,0,0,0,0,0,0),
				),
				'B' => array(
					array(1,1,1,1,1,1,0,0,0),
					array(1,0,0,0,0,0,1,0,0),
					array(1,0,0,0,0,0,0,1,0),
					array(1,0,0,0,0,0,0,1,0),
					array(1,0,0,0,0,0,0,1,0),
					array(1,0,0,0,0,0,1,0,0),
					array(1,1,1,1,1,1,0,0,0),
					array(1,0,0,0,0,0,1,0,0),
					array(1,0,0,0,0,0,0,1,0),
					array(1,0,0,0,0,0,0,1,0),
					array(1,0,0,0,0,0,0,1,0),
					array(1,0,0,0,0,0,1,0,0),
					array(1,1,1,1,1,1,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
				),
				'C' => array(
					array(0,0,1,1,1,1,1,0,0),
					array(0,1,0,0,0,0,0,1,0),
					array(1,0,0,0,0,0,0,0,1),
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,1),
					array(0,1,0,0,0,0,0,1,0),
					array(0,0,1,1,1,1,1,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
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
					array(1,0,0,0,0,0,0,1,0),
					array(1,1,1,1,1,1,1,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
				),
				'E' => array(
					array(0,0,1,1,1,1,1,1,1),
					array(0,1,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(1,1,1,1,1,1,1,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(0,1,0,0,0,0,0,0,0),
					array(0,0,1,1,1,1,1,1,1),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
				),
				'F' => array(
					array(0,0,1,1,1,1,1,1,0),
					array(0,1,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(1,1,1,1,1,1,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
				),
				'G' => array(
					array(0,0,1,1,1,1,1,0,0),
					array(0,1,0,0,0,0,0,1,0),
					array(1,0,0,0,0,0,0,0,1),
					array(1,0,0,0,0,0,0,0,1),
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,1,1),
					array(1,0,0,0,0,0,0,0,1),
					array(1,0,0,0,0,0,0,0,1),
					array(0,1,0,0,0,0,0,1,0),
					array(0,0,1,1,1,1,1,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
				),
				'H' => array(
					array(1,0,0,0,0,0,1,0,0),
					array(1,0,0,0,0,0,1,0,0),
					array(1,0,0,0,0,0,1,0,0),
					array(1,0,0,0,0,0,1,0,0),
					array(1,0,0,0,0,0,1,0,0),
					array(1,1,1,1,1,1,1,0,0),
					array(1,0,0,0,0,0,1,0,0),
					array(1,0,0,0,0,0,1,0,0),
					array(1,0,0,0,0,0,1,0,0),
					array(1,0,0,0,0,0,1,0,0),
					array(1,0,0,0,0,0,1,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
				),
				'I' => array(
					array(0,1,1,1,1,1,1,1,0),
					array(0,0,0,0,1,0,0,0,0),
					array(0,0,0,0,1,0,0,0,0),
					array(0,0,0,0,1,0,0,0,0),
					array(0,0,0,0,1,0,0,0,0),
					array(0,0,0,0,1,0,0,0,0),
					array(0,0,0,0,1,0,0,0,0),
					array(0,0,0,0,1,0,0,0,0),
					array(0,0,0,0,1,0,0,0,0),
					array(0,1,1,1,1,1,1,1,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
				),
				'J' => array(
					array(0,0,0,0,0,0,1,1,1),
					array(0,0,0,0,0,0,0,0,1),
					array(0,0,0,0,0,0,0,0,1),
					array(0,0,0,0,0,0,0,0,1),
					array(0,0,0,0,0,0,0,0,1),
					array(0,0,0,0,0,0,0,0,1),
					array(0,0,0,0,0,0,0,0,1),
					array(0,1,0,0,0,0,0,0,1),
					array(0,1,0,0,0,0,0,0,1),
					array(0,0,1,0,0,0,0,1,0),
					array(0,0,0,1,1,1,1,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
				),
				'K' => array(
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
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
				),
				'L' => array(
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(0,1,0,0,0,0,0,0,0),
					array(0,0,1,1,1,1,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
				),
				'M' => array(
					array(0,1,0,0,0,0,0,1,0),
					array(0,1,1,0,0,0,1,1,0),
					array(0,1,0,1,0,1,0,1,0),
					array(0,1,0,0,1,0,0,1,0),
					array(0,1,0,0,0,0,0,1,0),
					array(0,1,0,0,0,0,0,1,0),
					array(0,1,0,0,0,0,0,1,0),
					array(0,1,0,0,0,0,0,1,0),
					array(0,1,0,0,0,0,0,1,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
				),
				'N' => array(
					array(1,0,0,0,0,0,0,0,1),
					array(1,1,0,0,0,0,0,0,1),
					array(1,0,1,0,0,0,0,0,1),
					array(1,0,0,1,0,0,0,0,1),
					array(1,0,0,0,1,0,0,0,1),
					array(1,0,0,0,0,1,0,0,1),
					array(1,0,0,0,0,0,1,0,1),
					array(1,0,0,0,0,0,0,1,1),
					array(1,0,0,0,0,0,0,0,1),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
				),
				'O' => array(
					array(0,0,0,1,1,1,0,0,0),
					array(0,0,1,0,0,0,1,0,0),
					array(0,1,0,0,0,0,0,1,0),
					array(1,0,0,0,0,0,0,0,1),
					array(1,0,0,0,0,0,0,0,1),
					array(1,0,0,0,0,0,0,0,1),
					array(1,0,0,0,0,0,0,0,1),
					array(1,0,0,0,0,0,0,0,1),
					array(0,1,0,0,0,0,0,1,0),
					array(0,0,1,0,0,0,1,0,0),
					array(0,0,0,1,1,1,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
				),
				'P' => array(
					array(1,1,1,1,1,1,0,0,0),
					array(1,0,0,0,0,0,1,0,0),
					array(1,0,0,0,0,0,0,1,0),
					array(1,0,0,0,0,0,0,1,0),
					array(1,0,0,0,0,0,0,1,0),
					array(1,0,0,0,0,0,0,1,0),
					array(1,0,0,0,0,0,1,0,0),
					array(1,1,1,1,1,1,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
				),
				'Q' => array(
					array(0,0,1,1,1,1,0,0,0),
					array(0,1,0,0,0,0,1,0,0),
					array(1,0,0,0,0,0,0,1,0),
					array(1,0,0,0,0,0,0,1,0),
					array(1,0,0,0,0,0,0,1,0),
					array(1,0,0,0,0,0,0,1,0),
					array(1,0,0,0,1,0,0,1,0),
					array(1,0,0,0,0,1,0,1,0),
					array(0,1,0,0,0,0,1,0,0),
					array(0,0,1,1,1,1,0,1,0),
					array(0,0,0,0,0,0,0,0,1),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
				),
				'R' => array(
					array(1,1,1,1,1,1,0,0,0),
					array(1,0,0,0,0,0,1,0,0),
					array(1,0,0,0,0,0,0,1,0),
					array(1,0,0,0,0,0,0,1,0),
					array(1,0,0,0,0,0,0,1,0),
					array(1,0,0,0,0,0,1,0,0),
					array(1,1,1,1,1,1,0,0,0),
					array(1,0,1,0,0,0,0,0,0),
					array(1,0,0,1,0,0,0,0,0),
					array(1,0,0,0,1,0,0,0,0),
					array(1,0,0,0,0,1,0,0,0),
					array(1,0,0,0,0,0,1,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
				),
				'S' => array(
					array(0,0,1,1,1,1,1,1,1),
					array(0,1,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(0,1,0,0,0,0,0,0,0),
					array(0,0,1,1,1,1,1,0,0),
					array(0,0,0,0,0,0,0,1,0),
					array(0,0,0,0,0,0,0,0,1),
					array(0,0,0,0,0,0,0,0,1),
					array(0,0,0,0,0,0,0,1,0),
					array(1,1,1,1,1,1,1,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
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
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
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
					array(0,1,0,0,0,0,0,1,0),
					array(0,0,1,1,1,1,1,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
				),
				'V' => array(
					array(1,0,0,0,0,0,0,0,1),
					array(1,0,0,0,0,0,0,0,1),
					array(1,0,0,0,0,0,0,0,1),
					array(1,0,0,0,0,0,0,0,1),
					array(1,0,0,0,0,0,0,0,1),
					array(1,0,0,0,0,0,0,0,1),
					array(1,0,0,0,0,0,0,0,1),
					array(0,1,0,0,0,0,0,1,0),
					array(0,0,1,0,0,0,1,0,0),
					array(0,0,0,1,0,1,0,0,0),
					array(0,0,0,0,1,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
				),
				'W' => array(
					array(1,0,0,0,0,0,0,0,1),
					array(1,0,0,0,0,0,0,0,1),
					array(1,0,0,0,0,0,0,0,1),
					array(1,0,0,0,0,0,0,0,1),
					array(1,0,0,0,1,0,0,0,1),
					array(1,0,0,1,0,1,0,0,1),
					array(1,0,1,0,0,0,1,0,1),
					array(1,1,0,0,0,0,0,1,1),
					array(1,0,0,0,0,0,0,0,1),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
				),
				'X' => array(
					array(1,0,0,0,0,0,0,0,1),
					array(1,0,0,0,0,0,0,0,1),
					array(0,1,0,0,0,0,0,1,0),
					array(0,0,1,0,0,0,1,0,0),
					array(0,0,0,1,0,1,0,0,0),
					array(0,0,0,0,1,0,0,0,0),
					array(0,0,0,1,0,1,0,0,0),
					array(0,0,1,0,0,0,1,0,0),
					array(0,1,0,0,0,0,0,1,0),
					array(1,0,0,0,0,0,0,0,1),
					array(1,0,0,0,0,0,0,0,1),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
				),
				'Y' => array(
					array(1,0,0,0,0,0,0,0,1),
					array(1,0,0,0,0,0,0,0,1),
					array(1,0,0,0,0,0,0,0,1),
					array(0,1,0,0,0,0,0,1,0),
					array(0,0,1,0,0,0,1,0,0),
					array(0,0,0,1,0,1,0,0,0),
					array(0,0,0,0,1,0,0,0,0),
					array(0,0,0,0,1,0,0,0,0),
					array(0,0,0,0,1,0,0,0,0),
					array(0,0,0,0,1,0,0,0,0),
					array(0,0,0,0,1,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
				),
				'Z' => array(
					array(1,1,1,1,1,1,1,1,1),
					array(1,0,0,0,0,0,0,0,1),
					array(0,0,0,0,0,0,0,1,0),
					array(0,0,0,0,0,0,1,0,0),
					array(0,0,0,0,0,1,0,0,0),
					array(0,0,0,0,1,0,0,0,0),
					array(0,0,0,1,0,0,0,0,0),
					array(0,0,1,0,0,0,0,0,0),
					array(0,1,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,1),
					array(1,1,1,1,1,1,1,1,1),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
				),
				'1' => array(
					array(0,0,0,0,1,0,0,0,0),
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
					array(0,1,1,1,1,1,1,1,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
				),
				'2' => array(
					array(0,0,0,1,1,1,0,0,0),
					array(0,0,1,0,0,0,1,0,0),
					array(0,1,0,0,0,0,0,1,0),
					array(0,0,0,0,0,0,0,0,1),
					array(0,0,0,0,0,0,0,0,1),
					array(0,0,0,0,0,0,0,0,1),
					array(0,0,0,0,0,0,0,1,0),
					array(0,0,0,0,0,0,1,0,0),
					array(0,0,0,0,0,1,0,0,0),
					array(0,0,0,0,1,0,0,0,0),
					array(0,0,0,1,0,0,0,0,0),
					array(0,0,1,0,0,0,0,0,0),
					array(0,1,1,1,1,1,1,1,1),
					array(0,0,0,0,0,0,0,0,0),
				),
				'3' => array(
					array(0,0,0,1,1,1,1,0,0),
					array(0,0,1,0,0,0,0,1,0),
					array(0,1,0,0,0,0,0,0,1),
					array(0,0,0,0,0,0,0,0,1),
					array(0,0,0,0,0,0,0,0,1),
					array(0,0,0,0,0,0,0,1,0),
					array(0,0,0,0,0,1,1,0,0),
					array(0,0,0,0,0,0,0,1,0),
					array(0,0,0,0,0,0,0,0,1),
					array(0,0,0,0,0,0,0,0,1),
					array(0,1,0,0,0,0,0,0,1),
					array(0,0,1,0,0,0,0,1,0),
					array(0,0,0,1,1,1,1,0,0),
					array(0,0,0,0,0,0,0,0,0),
				),
				'4' => array(
					array(0,0,0,0,0,0,0,1,0),
					array(0,0,0,0,0,0,1,1,0),
					array(0,0,0,0,0,1,0,1,0),
					array(0,0,0,0,1,0,0,1,0),
					array(0,0,0,1,0,0,0,1,0),
					array(0,0,1,0,0,0,0,1,0),
					array(0,1,1,1,1,1,1,1,1),
					array(0,0,0,0,0,0,0,1,0),
					array(0,0,0,0,0,0,0,1,0),
					array(0,0,0,0,0,0,0,1,0),
					array(0,0,0,0,0,0,0,1,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
				),
				'5' => array(
					array(1,1,1,1,1,1,1,1,1),
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(0,1,0,0,0,0,0,0,0),
					array(0,0,1,1,1,1,1,0,0),
					array(0,0,0,0,0,0,0,1,0),
					array(0,0,0,0,0,0,0,0,1),
					array(1,0,0,0,0,0,0,0,1),
					array(0,1,0,0,0,0,0,1,0),
					array(0,0,1,1,1,1,1,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
				),
				'6' => array(
					array(0,0,1,1,1,1,1,0,0),
					array(0,1,0,0,0,0,0,1,0),
					array(1,0,0,0,0,0,0,0,1),
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,0,0,0,0,0,0),
					array(1,0,0,1,1,1,1,0,0),
					array(1,0,1,0,0,0,0,1,0),
					array(1,1,0,0,0,0,0,0,1),
					array(1,0,0,0,0,0,0,0,1),
					array(1,0,0,0,0,0,0,0,1),
					array(0,1,0,0,0,0,0,1,0),
					array(0,0,1,1,1,1,1,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
				),
				'7' => array(
					array(1,1,1,1,1,1,1,1,1),
					array(1,0,0,0,0,0,0,0,1),
					array(0,0,0,0,0,0,0,1,0),
					array(0,0,0,0,0,0,1,0,0),
					array(0,0,0,0,0,1,0,0,0),
					array(0,0,0,0,1,0,0,0,0),
					array(0,0,0,0,1,0,0,0,0),
					array(0,0,0,0,1,0,0,0,0),
					array(0,0,0,0,1,0,0,0,0),
					array(0,0,0,0,1,0,0,0,0),
					array(0,0,0,0,1,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
				),
				'8' => array(
					array(0,0,1,1,1,1,1,0,0),
					array(0,1,0,0,0,0,0,1,0),
					array(1,0,0,0,0,0,0,0,1),
					array(1,0,0,0,0,0,0,0,1),
					array(1,0,0,0,0,0,0,0,1),
					array(0,1,0,0,0,0,0,1,0),
					array(0,0,1,1,1,1,1,0,0),
					array(0,1,0,0,0,0,0,1,0),
					array(1,0,0,0,0,0,0,0,1),
					array(1,0,0,0,0,0,0,0,1),
					array(1,0,0,0,0,0,0,0,1),
					array(0,1,0,0,0,0,0,1,0),
					array(0,0,1,1,1,1,1,0,0),
					array(0,0,0,0,0,0,0,0,0),
				),
				'9' => array(
					array(0,0,0,1,1,1,1,0,0),
					array(0,0,1,0,0,0,0,1,0),
					array(0,1,0,0,0,0,0,0,1),
					array(0,1,0,0,0,0,0,0,1),
					array(0,1,0,0,0,0,0,0,1),
					array(0,1,0,0,0,0,0,1,1),
					array(0,0,1,1,1,1,1,0,1),
					array(0,0,0,0,0,0,0,0,1),
					array(0,0,0,0,0,0,0,0,1),
					array(0,1,0,0,0,0,0,0,1),
					array(0,0,1,0,0,0,0,1,0),
					array(0,0,0,1,1,1,1,0,0),
					array(0,0,0,0,0,0,0,0,0),
					array(0,0,0,0,0,0,0,0,0),
				),
			)
		);
	}
}

?>