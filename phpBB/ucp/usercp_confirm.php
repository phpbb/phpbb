<?php
/***************************************************************************
 *                            usercp_confirm.php
 *                            -------------------
 *   begin                : Saturday, Jan 15, 2003
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

// Note to potential users of this code ...
//
// Remember this is released under the _GPL_ and is subject
// to that licence. Do not incorporate this within software 
// released or distributed in any way under a licence other
// than the GPL. We will be watching ... ;)

define('IN_PHPBB', true);
$phpbb_root_path = './../';
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);

// Start session management
$user->start();
$user->setup();
$auth->acl($user->data);
// End session management

// Do we have an id? No, then just exit
if (empty($_GET['id']))
{
	exit;
}

$confirm_id = $_GET['id'];

// Define available charset
$chars = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J',  'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T',  'U', 'V', 'W', 'X', 'Y', 'Z', '1', '2', '3', '4', '5', '6', '7', '8', '9');

// Try and grab code for this id and session
$sql = "SELECT code  
	FROM " . CONFIRM_TABLE . " 
	WHERE session_id = '" . $user->data['session_id'] . "' 
		AND confirm_id = '$confirm_id'";
$result = $db->sql_query($sql);

// If we have a row then grab data else create a new id
if ($row = $db->sql_fetchrow($result))
{
	$db->sql_freeresult($result);
	$code = $row['code'];
}
else
{
	exit;
}

// If we can we will generate a single filtered png else we will have to simply
// output six seperate original pngs ... first way is preferable!
if (@extension_loaded('zlib'))
{
	$_png = define_filtered_pngs();

	$total_width = 250;
	$total_height = 40;
	$img_height = 25;
	$img_width = 0;
	$l = 0;

	list($usec, $sec) = explode(' ', microtime()); 
	mt_srand($sec * $usec); 

	$char_widths = array();
	for ($i = 0; $i < strlen($code); $i++)
	{
		$char = $code{$i};

		$width = mt_rand(0, 4);
		$char_widths[] = $width;
		$img_width += $_png[$char]['width'] - $width;
	}

	$offset_x = mt_rand(0, $total_width - $img_width);
	$offset_y = mt_rand(0, $total_height - $img_height);

	$image = '';
	$hold_chars = array();
	for ($i = 0; $i < $total_height; $i++)
	{
		$image .= chr(0);

		if ($i > $offset_y && $i < $offset_y + $img_height)
		{
			$j = 0;

			for ($k = 0; $k < $offset_x; $k++)
			{
				$image .= chr(mt_rand(180, 255));
			}

			for ($k = 0; $k < strlen($code); $k++)
			{
				$char = $code{$k};

				if (empty($hold_chars[$char]))
				{
					$hold_chars[$char] = explode("\n", chunk_split(base64_decode($_png[$char]['data']), $_png[$char]['width'] + 1, "\n"));
				}
				$image .= randomise(substr($hold_chars[$char][$l], 1), $char_widths[$j]);
				$j++;
			}

			for ($k = $offset_x + $img_width; $k < $total_width; $k++)
			{
				$image .= chr(mt_rand(180, 255));
			}

			$l++;
		}
		else
		{
			for ($k = 0; $k < $total_width; $k++)
			{
				$image .= chr(mt_rand(180, 255));
			}
		}

	}
	unset($hold);

	$image = create_png(gzcompress($image), $total_width, $total_height);

	// Output image
	header('Content-Type: image/png');
	header('Cache-control: no-cache, no-store');
	echo $image;

	unset($image);
	unset($_png);
	exit;

}
else
{
	if (!empty($_GET['c']))
	{
		$_png = define_raw_pngs();

		$char = substr($code, intval($HTTP_GET_VARS['c']) - 1, 1);
		header('Content-Type: image/png');
		header('Cache-control: no-cache, no-store');
		echo base64_decode($_png[$char]);

		unset($_png);
		exit;
	}
}

exit;

// ---------
// FUNCTIONS
//

// This is designed to randomise the pixels of the image data within
// certain limits so as to keep it readable. It also varies the image
// width a little
function randomise($scanline, $width)
{
	$new_line = '';
	$start = floor($width/2);
	$end = strlen($scanline) - ceil($width/2);

	for ($i = $start; $i < $end; $i++)
	{
		$pixel = ord($scanline{$i});

		if ($pixel < 190)
		{
			$new_line .= chr(mt_rand(0, 170));
		}
		else if ($pixel > 190)
		{
			$new_line .= chr(mt_rand(180, 255));
		}
		else
		{
			$new_line .= $scanline{$i};
		}
	}

	return $new_line;
}

// This creates a chunk of the given type, with the given data
// of the given length adding the relevant crc
function png_chunk($length, $type, $data)
{
	$raw = $type;
	$raw .= $data;
	$crc = crc32($raw);
	$raw .= pack('C4', $crc >> 24, $crc >> 16, $crc >> 8, $crc);

	return pack('C4', $length >> 24, $length >> 16, $length >> 8, $length) . $raw;
}

// Creates greyscale 8bit png - The PNG spec can be found at
// http://www.libpng.org/pub/png/spec/PNG-Contents.html we use
// png because it's a fully recognised open standard and supported
// by practically all modern browsers and OSs
function create_png($gzimage, $width, $height)
{
	// SIG
	$image = pack('C8', 137, 80, 78, 71, 13, 10, 26, 10);
	// IHDR
	$raw = pack('C4', $width >> 24, $width >> 16, $width >> 8, $width);
	$raw .= pack('C4', $height >> 24, $height >> 16, $height >> 8, $height);
	$raw .= pack('C5', 8, 0, 0, 0, 0);
	$image .= png_chunk(13, 'IHDR', $raw);
	// IDAT
	$image .= png_chunk(strlen($gzimage), 'IDAT', $gzimage);
	// IEND
	$image .= png_chunk(0, 'IEND', '');

	return $image;
}

// Each 'data' element is base64_encoded uncompressed IDAT
// png image data
function define_filtered_pngs()
{
	$_png = array(
		'1' => array(
			'data' => 'AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A////////////////UAAA/////////////wD//////////////6QAAAD/////////////AP////////////+YBAAAAP////////////8A///////////MSAAAAAAA/////////////wD//////////wAAACgAAAD/////////////AP//////////AABE6AAAAP////////////8A//////////80rP//AAAA/////////////wD///////////////8AAAD/////////////AP///////////////wAAAP////////////8A////////////////AAAA/////////////wD///////////////8AAAD/////////////AP///////////////wAAAP////////////8A////////////////AAAA/////////////wD///////////////8AAAD/////////////AP///////////////wAAAP////////////8A////////////////AAAA/////////////wD///////////////8AAAD/////////////AP////////////////////////////////8=', 
			'width' => 25
		), 
		'2' => array(
			'data' => 'AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A////////////0FwUAAw8oP///////////wD//////////5wEAAAAAAAAVPz/////////AP/////////cBAAAAAAAAAAAkP////////8A/////////3AAACTI/+BIAAAo/////////wD/////////LAAAsP///+QAAAD/////////AP////////9QAOQo////+AAADP////////8A//////////////////+wAABA/////////wD/////////////////6CAAAKT/////////AP///////////////+goAAA4/P////////8A///////////////oKAAAGOD//////////wD/////////////3CgAACTY////////////AP///////////+QYAAA46P////////////8A///////////wKAAAVPT//////////////wD//////////0wAADD4////////////////AP/////////AAAAAAAAAAAAAAP////////8A/////////1QAAAAAAAAAAAAA/////////wD/////////EAAAAAAAAAAAAAD/////////AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8=', 
			'width' => 25
		), 
		'3' => array(
			'data' => 'AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A/////////////////////////////////wD///////////+gOAgALID0////////////AP//////////VAAAAAAAACzw//////////8A/////////6QAAAAAAAAAAGT//////////wD/////////RAAATOT4jAAADP//////////AP////////9oOBTk///4AAAI//////////8A////////////////6GgAAFD//////////wD//////////////0gAAAAc4P//////////AP//////////////MAAACKD///////////8A//////////////8UAAAAAEj8/////////wD//////////////9z/1DQAAID/////////AP//////////////////2AAAIP////////8A/////////2g4FPD/////AAAA/////////wD/////////LAAAnP///8wAABT/////////AP////////94AAAUuP/MKAAAXP////////8A/////////+gQAAAAAAAAAAjU/////////wD//////////8AMAAAAAAAIrP//////////AP///////////9hcFAAUXNj///////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8=', 
			'width' => 25
		), 
		'4' => array(
			'data' => 'AP////////////////////////////////8A/////////////////////////////////wD////////////////wHAAA////////////AP///////////////2AAAAD///////////8A//////////////+0AAAAAP///////////wD/////////////8BwAAAAA////////////AP////////////9gAAAAAAD///////////8A////////////tAAAQAAAAP///////////wD///////////AcAAzgAAAA////////////AP//////////YAAAmP8AAAD///////////8A/////////7AAAEz//wAAAP///////////wD////////sFAAQ5P//AAAA////////////AP///////1QAAKD///8AAAD///////////8A////////AAAAAAAAAAAAAAAA/////////wD///////8AAAAAAAAAAAAAAAD/////////AP///////wAAAAAAAAAAAAAAAP////////8A/////////////////wAAAP///////////wD/////////////////AAAA////////////AP////////////////8AAAD///////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8=', 
			'width' => 25
		), 
		'5' => array(
			'data' => 'AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP/////////kAAAAAAAAAAD///////////8A/////////6gAAAAAAAAAAP///////////wD/////////cAAAAAAAAAAA////////////AP////////8wAAC0//////////////////8A////////9AQAAOz//////////////////wD///////+8AAAMSAgIPLz/////////////AP///////4AAAAAAAAAAAJT///////////8A////////SAAAAAAAAAAABND//////////wD///////9MJAx85P/EKAAAXP//////////AP////////////////+8AAAc//////////8A//////////////////wAAAD//////////wD///////9cNBDw////+AAACP//////////AP///////zAAAJj///+4AAA0//////////8A////////fAAADLT/xBgAAIj//////////wD////////oEAAAAAAAAAAc8P//////////AP////////+0CAAAAAAAEMj///////////8A///////////UVBAAHGTg/////////////wD/////////////////////////////////AP////////////////////////////////8=', 
			'width' => 25
		), 
		'6' => array(
			'data' => 'AP////////////////////////////////8A/////////////////////////////////wD////////////8oDgIACyU/P//////////AP//////////9EQAAAAAAABU//////////8A//////////9gAAAAAAAAAACo/////////wD/////////1AAADKz46EwAAED/////////AP////////98AACc////5BAsVP////////8A/////////0AAAPT//////////////////wD/////////IAAgwDwECES4////////////AP////////8AABQEAAAAAACQ//////////8A/////////wAAAAAAAAAAAAC8/////////wD/////////AAAAPNT/1DgAAEz/////////AP////////8YAADc////1AAAEP////////8A/////////zQAAP//////AAAA/////////wD/////////aAAAwP///+AAABT/////////AP////////+8AAAcvP/gRAAAUP////////8A//////////88AAAAAAAAAADE/////////wD//////////+goAAAAAAAAkP//////////AP////////////B4JAAMRLj///////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8=', 
			'width' => 25
		), 
		'7' => array(
			'data' => 'AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A/////////wAAAAAAAAAAAAAA/////////wD/////////AAAAAAAAAAAAAAD/////////AP////////8AAAAAAAAAAAAAAP////////8A/////////////////7QAAACI/////////wD////////////////sFAAAYP//////////AP///////////////2QAACj0//////////8A///////////////QBAAAxP///////////wD//////////////1QAAFz/////////////AP/////////////kBAAE3P////////////8A/////////////4AAAFT//////////////wD/////////////JAAAuP//////////////AP///////////9gAABT8//////////////8A////////////mAAAWP///////////////wD///////////9gAACU////////////////AP///////////zQAAMj///////////////8A////////////GAAA6P///////////////wD///////////8AAAD/////////////////AP////////////////////////////////8=', 
			'width' => 25
		), 
		'8' => array(
			'data' => 'AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A///////////8oDwMAAxAoPz//////////wD//////////EwAAAAAAAAAVPz/////////AP////////+EAAAAAAAAAAAAhP////////8A/////////yQAAEjc/+BMAAAk/////////wD/////////AAAA7P///+wAAAD/////////AP////////8YAADs////7AAAGP////////8A/////////3AAAFDg/9xMAAB0/////////wD/////////9EAAAAAAAAAARPT/////////AP//////////7CgAAAAAACjw//////////8A/////////+ggAAAAAAAAACDo/////////wD/////////ZAAASOD/3EwAAFz/////////AP////////8QAADs////7AAADP////////8A/////////wAAAOj////sAAAA/////////wD/////////KAAAPNT/3EwAACz/////////AP////////+QAAAAAAAAAAAAlP////////8A//////////xcAAAAAAAAAFT8/////////wD///////////+wSBAADECg////////////AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8=', 
			'width' => 25
		), 
		'9' => array(
			'data' => 'AP////////////////////////////////8A////////////vEQMACR48P///////////wD//////////5AAAAAAAAAo6P//////////AP/////////EAAAAAAAAAAA8//////////8A/////////1AAAEjg/7wcAAC8/////////wD/////////FAAA4P///8AAAGz/////////AP////////8AAAD//////wAANP////////8A/////////xAAANT////cAAAY/////////wD/////////TAAAOND/1DwAAAD/////////AP////////+8AAAAAAAAAAAAAP////////8A//////////+QAAAAAAAEFAAA/////////wD///////////+4PAgEPMgcACD/////////AP//////////////////8AAARP////////8A/////////1gwEOT///+cAAB8/////////wD/////////QAAATOj4rAwAANT/////////AP////////+gAAAAAAAAAABk//////////8A//////////xEAAAAAAAASPT//////////wD///////////yAJAAIPKD8////////////AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8=', 
			'width' => 25
		), 
		'A' => array(
			'data' => 'AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A/////////////1gAAABY/////////////wD////////////sCAAAAAjs////////////AP///////////5QAAAAAAJT///////////8A////////////MAAAZAAAMP///////////wD//////////8wAABj4GAAAzP//////////AP//////////bAAAdP94AABs//////////8A//////////gQAADY/9wAABD4/////////wD/////////qAAAOP///0AAAKj/////////AP////////9EAACc////pAAARP////////8A////////4AAADPT////4EAAA4P///////wD///////+AAAAAAAAAAAAAAACA////////AP///////xwAAAAAAAAAAAAAABz///////8A//////+4AAAAAAAAAAAAAAAAALj//////wD//////1gAAIj/////////gAAAWP//////AP/////sCAAE5P/////////gAAAI7P////8A/////5QAAET///////////9EAACU/////wD/////MAAApP///////////6QAADD/////AP////////////////////////////////8=', 
			'width' => 25
		), 
		'B' => array(
			'data' => 'AP////////////////////////////////8A////////AAAAAAAAAAAMLID8/////////wD///////8AAAAAAAAAAAAAAFT/////////AP///////wAAAAAAAAAAAAAAAJT///////8A////////AAAA///////cUAAAIP///////wD///////8AAAD////////wAAAA////////AP///////wAAAP///////+wAABj///////8A////////AAAA///////QTAAAZP///////wD///////8AAAAAAAAAAAAAABjg////////AP///////wAAAAAAAAAAAAAASOD///////8A////////AAAAAAAAAAAAAAAAGOD//////wD///////8AAAD//////+zEOAAAVP//////AP///////wAAAP/////////oAAAQ//////8A////////AAAA/////////+wAAAD//////wD///////8AAAD///////zMTAAANP//////AP///////wAAAAAAAAAAAAAAAACU//////8A////////AAAAAAAAAAAAAAAAVPz//////wD///////8AAAAAAAAAAAAcQJj8////////AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8=', 
			'width' => 25
		), 
		'C' => array(
			'data' => 'AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A////////////tFggAAAURJDw/////////wD/////////6EAAAAAAAAAAABS0////////AP///////+gkAAAAAAAAAAAAAATI//////8A////////RAAADIDU//zUfAgAADT//////wD//////8QAAAzI////////uCR80P//////AP//////aAAAfP////////////////////8A//////8sAADQ/////////////////////wD//////wwAAPj/////////////////////AP//////AAAA//////////////////////8A//////8EAAD0/////////////////////wD//////ygAAND/////////////////////AP//////XAAAfP////////////////////8A//////+0AAAM2P///////6wkfND//////wD///////84AAAMiNz/+NB0BAAAQP//////AP///////+AYAAAAAAAAAAAAAAzQ//////8A/////////9w0AAAAAAAAAAAkzP///////wD///////////yoUBgAACRYqPz/////////AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8=', 
			'width' => 25
		), 
		'D' => array(
			'data' => 'AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP///////wAAAAAAAAAIKGTQ//////////8A////////AAAAAAAAAAAAAASQ/////////wD///////8AAAAAAAAAAAAAAACk////////AP///////wAAAP/////gpCQAABT0//////8A////////AAAA////////6CAAAJz//////wD///////8AAAD/////////mAAAUP//////AP///////wAAAP/////////YAAAk//////8A////////AAAA//////////wAAAT//////wD///////8AAAD//////////wAAAP//////AP///////wAAAP/////////8AAAA//////8A////////AAAA/////////9gAACD//////wD///////8AAAD/////////pAAATP//////AP///////wAAAP////////wsAACc//////8A////////AAAA//////C8QAAAFPT//////wD///////8AAAAAAAAAAAAAAACk////////AP///////wAAAAAAAAAAAAAEkP////////8A////////AAAAAAAAAAgscNz//////////wD/////////////////////////////////AP////////////////////////////////8=', 
			'width' => 25
		), 
		'E' => array(
			'data' => 'AP////////////////////////////////8A////////AAAAAAAAAAAAAAAAAP///////wD///////8AAAAAAAAAAAAAAAAA////////AP///////wAAAAAAAAAAAAAAAAD///////8A////////AAAA/////////////////////wD///////8AAAD/////////////////////AP///////wAAAP////////////////////8A////////AAAA/////////////////////wD///////8AAAAAAAAAAAAAAAD/////////AP///////wAAAAAAAAAAAAAAAP////////8A////////AAAAAAAAAAAAAAAA/////////wD///////8AAAD/////////////////////AP///////wAAAP////////////////////8A////////AAAA/////////////////////wD///////8AAAD/////////////////////AP///////wAAAAAAAAAAAAAAAAD///////8A////////AAAAAAAAAAAAAAAAAP///////wD///////8AAAAAAAAAAAAAAAAA////////AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8=', 
			'width' => 25
		), 
		'F' => array(
			'data' => 'AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A/////////wAAAAAAAAAAAAAAAP///////wD/////////AAAAAAAAAAAAAAAA////////AP////////8AAAAAAAAAAAAAAAD///////8A/////////wAAAP///////////////////wD/////////AAAA////////////////////AP////////8AAAD///////////////////8A/////////wAAAP///////////////////wD/////////AAAAAAAAAAAAAAD/////////AP////////8AAAAAAAAAAAAAAP////////8A/////////wAAAAAAAAAAAAAA/////////wD/////////AAAA////////////////////AP////////8AAAD///////////////////8A/////////wAAAP///////////////////wD/////////AAAA////////////////////AP////////8AAAD///////////////////8A/////////wAAAP///////////////////wD/////////AAAA////////////////////AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8=', 
			'width' => 25
		), 
		'G' => array(
			'data' => 'AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A///////////QaCwIAAAcRIzs/////////wD////////0YAAAAAAAAAAAABCw////////AP//////9DwAAAAAAAAAAAAAAAS4//////8A//////9gAAAAULjw//jIcAQAACz//////wD/////0AAAAKT/////////qCR80P//////AP////9wAABg//////////////////////8A/////zAAAMT//////////////////////wD/////CAAA+P//////////////////////AP////8AAAD///////8AAAAAAAAA//////8A/////wwAAOz//////wAAAAAAAAD//////wD/////NAAAuP//////AAAAAAAAAP//////AP////98AABM////////////AAAA//////8A/////9gAAACM//////////gAAAD//////wD//////2QAAABMuPD/8MB4GAAAAP//////AP//////9DwAAAAAAAAAAAAAAABE//////8A////////9GQAAAAAAAAAAAAYmPz//////wD//////////9R0NBAABChgsPz/////////AP////////////////////////////////8=', 
			'width' => 25
		), 
		'H' => array(
			'data' => 'AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP///////wAAAP////////8AAAD///////8A////////AAAA/////////wAAAP///////wD///////8AAAD/////////AAAA////////AP///////wAAAP////////8AAAD///////8A////////AAAA/////////wAAAP///////wD///////8AAAD/////////AAAA////////AP///////wAAAP////////8AAAD///////8A////////AAAAAAAAAAAAAAAAAP///////wD///////8AAAAAAAAAAAAAAAAA////////AP///////wAAAAAAAAAAAAAAAAD///////8A////////AAAA/////////wAAAP///////wD///////8AAAD/////////AAAA////////AP///////wAAAP////////8AAAD///////8A////////AAAA/////////wAAAP///////wD///////8AAAD/////////AAAA////////AP///////wAAAP////////8AAAD///////8A////////AAAA/////////wAAAP///////wD/////////////////////////////////AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8=', 
			'width' => 25
		), 
		'I' => array(
			'data' => 'AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A//////////8AAAAAAAAAAAD//////////wD//////////wAAAAAAAAAAAP//////////AP//////////AAAAAAAAAAAA//////////8A//////////////8AAAD//////////////wD//////////////wAAAP//////////////AP//////////////AAAA//////////////8A//////////////8AAAD//////////////wD//////////////wAAAP//////////////AP//////////////AAAA//////////////8A//////////////8AAAD//////////////wD//////////////wAAAP//////////////AP//////////////AAAA//////////////8A//////////////8AAAD//////////////wD//////////////wAAAP//////////////AP//////////AAAAAAAAAAAA//////////8A//////////8AAAAAAAAAAAD//////////wD//////////wAAAAAAAAAAAP//////////AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8=', 
			'width' => 25
		), 
		'J' => array(
			'data' => 'AP////////////////////////////////8A////////////////////AAAA/////////wD///////////////////8AAAD/////////AP///////////////////wAAAP////////8A////////////////////AAAA/////////wD///////////////////8AAAD/////////AP///////////////////wAAAP////////8A////////////////////AAAA/////////wD///////////////////8AAAD/////////AP///////////////////wAAAP////////8A////////////////////AAAA/////////wD///////////////////8AAAD/////////AP////////8AAAD4/////AAAAP////////8A/////////xwAAMT////UAAAc/////////wD/////////VAAAKND/4EwAAEz/////////AP////////+8AAAAAAAAAAAAtP////////8A//////////9wAAAAAAAAAHT//////////wD///////////+kPAwAFEy0////////////AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8=', 
			'width' => 25
		), 
		'K' => array(
			'data' => 'AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A////////AAAA////////9DwAABjY/////wD///////8AAAD///////Q8AAAY2P//////AP///////wAAAP/////4PAAAGNj///////8A////////AAAA/////FQAABjY/////////wD///////8AAAD///xUAAAY2P//////////AP///////wAAAP/8VAAADMT///////////8A////////AAAA/FQAAABw/////////////wD///////8AAABkAAAAAAzo////////////AP///////wAAAAAADDQAAGD///////////8A////////AAAAAAzE2AQAAMz//////////wD///////8AAAAEsP//bAAAPP//////////AP///////wAAAKz////wEAAAqP////////8A////////AAAA//////+YAAAg+P///////wD///////8AAAD///////wsAACA////////AP///////wAAAP///////7wAAAjk//////8A////////AAAA/////////0wAAFz//////wD///////8AAAD/////////3AgAAMj/////AP////////////////////////////////8=', 
			'width' => 25
		), 
		'L' => array(
			'data' => 'AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A/////////////////////////////////wD/////////AAAA////////////////////AP////////8AAAD///////////////////8A/////////wAAAP///////////////////wD/////////AAAA////////////////////AP////////8AAAD///////////////////8A/////////wAAAP///////////////////wD/////////AAAA////////////////////AP////////8AAAD///////////////////8A/////////wAAAP///////////////////wD/////////AAAA////////////////////AP////////8AAAD///////////////////8A/////////wAAAP///////////////////wD/////////AAAA////////////////////AP////////8AAAD///////////////////8A/////////wAAAAAAAAAAAAAAAP///////wD/////////AAAAAAAAAAAAAAAA////////AP////////8AAAAAAAAAAAAAAAD///////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8=', 
			'width' => 25
		), 
		'M' => array(
			'data' => 'AP////////////////////////////////8A/////////////////////////////////wD/////AAAAAADU///////UAAAAAAD/////AP////8AAAAAAIT//////4QAAAAAAP////8A/////wAAAAAAPP//////PAAAAAAA/////wD/////AAAABAAA7P///+wAAAQAAAD/////AP////8AAAA4AACg////oAAAOAAAAP////8A/////wAAAJAIAFT///9UAAiQAAAA/////wD/////AAAAoEAADPj/+AwAQKAAAAD/////AP////8AAACghAAAvP+8AACEoAAAAP////8A/////wAAAKzIAABs/2wAAMisAAAA/////wD/////AAAAwPwMACD/IAAM/MAAAAD/////AP////8AAADA/1AAAKgAAFD/wAAAAP////8A/////wAAAMD/kAAAGAAAkP/AAAAA/////wD/////AAAA4P/YAAAAAADY/+AAAAD/////AP////8AAADg//8YAAAAGP//4AAAAP////8A/////wAAAOD//1wAAABc///gAAAA/////wD/////AAAA9P//oAAAAKD///QAAAD/////AP////8AAAD////gAAAA4P///wAAAP////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8=', 
			'width' => 25
		), 
		'N' => array(
			'data' => 'AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A////////AAAAjP///////wAAAP///////wD///////8AAAAQ7P//////AAAA////////AP///////wAAAABo//////8AAAD///////8A////////AAAAAATU/////wAAAP///////wD///////8AAAAAAED/////AAAA////////AP///////wAAACgAAKz///8AAAD///////8A////////AAAAuAAAJPj//wAAAP///////wD///////8AAAD/SAAAiP//AAAA////////AP///////wAAAP/YBAAQ6P8AAAD///////8A////////AAAA//90AABk/wAAAP///////wD///////8AAAD///AUAADMAAAA////////AP///////wAAAP///5gAADwAAAD///////8A////////AAAA/////CwAAAAAAP///////wD///////8AAAD/////vAAAAAAA////////AP///////wAAAP//////UAAAAAD///////8A////////AAAA///////cCAAAAP///////wD///////8AAAD///////94AAAA////////AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8=', 
			'width' => 25
		), 
		'O' => array(
			'data' => 'AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP///////////KRMGAAAHFSs/P////////8A/////////9gsAAAAAAAAAAA03P///////wD////////gGAAAAAAAAAAAAAAY4P//////AP///////zgAAARw0Pz/1HAEAAA8//////8A//////+0AAAErP///////8QEAAC4/////wD//////2AAAGj//////////3AAAGD/////AP//////KAAAyP//////////yAAALP////8A//////8IAAD0///////////0AAAI/////wD//////wAAAP////////////8AAAD/////AP//////BAAA9P//////////8AAABP////8A//////8oAADA///////////AAAAo/////wD//////1wAAGD//////////2AAAGD/////AP//////tAAAAKz///////+sAAAAtP////8A////////OAAAAGTM/PzMbAQAADj//////wD////////cGAAAAAAAAAAAAAAY3P//////AP/////////YMAAAAAAAAAAAMNj///////8A///////////8qFAcAAAYUKj8/////////wD/////////////////////////////////AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8=', 
			'width' => 25
		), 
		'P' => array(
			'data' => 'AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////8AAAAAAAAAACBApP////////8A/////////wAAAAAAAAAAAAAAcP///////wD/////////AAAAAAAAAAAAAAAAsP//////AP////////8AAAD/////6KwYAABA//////8A/////////wAAAP///////8gAAAz//////wD/////////AAAA/////////wAAAP//////AP////////8AAAD///////+8AAAQ//////8A/////////wAAAP/////goBgAAFD//////wD/////////AAAAAAAAAAAAAAAEyP//////AP////////8AAAAAAAAAAAAABJT///////8A/////////wAAAAAAAAAQLGTU/////////wD/////////AAAA////////////////////AP////////8AAAD///////////////////8A/////////wAAAP///////////////////wD/////////AAAA////////////////////AP////////8AAAD///////////////////8A/////////wAAAP///////////////////wD/////////////////////////////////AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8=', 
			'width' => 25
		), 
		'Q' => array(
			'data' => 'AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP/////////8qFAcAAAYSJj4//////////8A////////2DAAAAAAAAAAABzE/////////wD//////9wYAAAAAAAAAAAAAATA////////AP//////NAAABGzM/PzQcAQAABz4//////8A/////7QAAASs////////tAQAAJT//////wD/////YAAAaP//////////aAAASP//////AP////8oAADI///////////IAAAY//////8A/////wQAAPT///////////QAAAD//////wD/////AAAA/////////////wAAAP//////AP////8EAAD0///////////wAAAY//////8A/////ygAAMj//////////7QAAET//////wD/////YAAAaP///8AwkPj/PAAAkP//////AP////+0AAAEwP//QAAAIFgAABDw//////8A//////84AAAEeNjcdAQAAAAAkP///////wD//////9wYAAAAAAAAAAAAAAAIbNT/////AP///////9gwAAAAAAAAAAAAAAAAOP////8A//////////yoVCQAABhInFgAAACo/////wD//////////////////////8hQJPz/////AP////////////////////////////////8=', 
			'width' => 25
		), 
		'R' => array(
			'data' => 'AP////////////////////////////////8A/////////////////////////////////wD///////8AAAAAAAAAABQ0hPT/////////AP///////wAAAAAAAAAAAAAALPT///////8A////////AAAAAAAAAAAAAAAAgP///////wD///////8AAAD//////+RQAAAo////////AP///////wAAAP////////AAAAD///////8A////////AAAA////////7AAACP///////wD///////8AAAD/////+NRQAAA8////////AP///////wAAAAAAAAAAAAAAALT///////8A////////AAAAAAAAAAAAAASU/////////wD///////8AAAAAAAAAAABw5P//////////AP///////wAAAP/8zDgAABjY//////////8A////////AAAA////9DgAADD8/////////wD///////8AAAD/////6BgAAJT/////////AP///////wAAAP//////rAAAFPD///////8A////////AAAA////////QAAAeP///////wD///////8AAAD////////QBAAE3P//////AP///////wAAAP////////9kAABI//////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8=', 
			'width' => 25
		), 
		'S' => array(
			'data' => 'AP////////////////////////////////8A/////////////////////////////////wD//////////7BIGAAAIFzA////////////AP////////xUAAAAAAAAAAB0//////////8A////////iAAAAAAAAAAAAAC0/////////wD///////8kAABg1P/8xDAAADz/////////AP///////wAAAPT/////2AAACP////////8A////////IAAARLz8/////////////////wD///////+AAAAAABBQiND/////////////AP////////xEAAAAAAAAACSU/P////////8A//////////ycKAAAAAAAAABU/P///////wD/////////////1JBQEAAAAACI////////AP//////////////////xEQAACz///////8A//////8QAADc////////8AAAAP///////wD//////0gAAFz////////MAAAM////////AP//////qAAAAFTE+P/onBgAAEz///////8A////////QAAAAAAAAAAAAAAEyP///////wD////////oPAAAAAAAAAAADKz/////////AP/////////8qFAcAAAQOHzo//////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8=', 
			'width' => 25
		), 
		'T' => array(
			'data' => 'AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A////////AAAAAAAAAAAAAAAAAP///////wD///////8AAAAAAAAAAAAAAAAA////////AP///////wAAAAAAAAAAAAAAAAD///////8A//////////////8AAAD//////////////wD//////////////wAAAP//////////////AP//////////////AAAA//////////////8A//////////////8AAAD//////////////wD//////////////wAAAP//////////////AP//////////////AAAA//////////////8A//////////////8AAAD//////////////wD//////////////wAAAP//////////////AP//////////////AAAA//////////////8A//////////////8AAAD//////////////wD//////////////wAAAP//////////////AP//////////////AAAA//////////////8A//////////////8AAAD//////////////wD//////////////wAAAP//////////////AP////////////////////////////////8=', 
			'width' => 25
		), 
		'U' => array(
			'data' => 'AP////////////////////////////////8A/////////////////////////////////wD///////8AAAD/////////AAAA////////AP///////wAAAP////////8AAAD///////8A////////AAAA/////////wAAAP///////wD///////8AAAD/////////AAAA////////AP///////wAAAP////////8AAAD///////8A////////AAAA/////////wAAAP///////wD///////8AAAD/////////AAAA////////AP///////wAAAP////////8AAAD///////8A////////AAAA/////////wAAAP///////wD///////8AAAD/////////AAAA////////AP///////wAAAP////////8AAAD///////8A////////HAAA5P//////6AAAGP///////wD///////80AACU//////+oAAAs////////AP///////3AAAAyc7P/woAwAAGT///////8A////////3AwAAAAAAAAAAAAE0P///////wD/////////rAwAAAAAAAAABJz/////////AP//////////4HAsCAAAKGTU//////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8=', 
			'width' => 25
		), 
		'V' => array(
			'data' => 'AP////////////////////////////////8A/////////////////////////////////wD/////NAAApP///////////5gAADT/////AP////+cAAA4////////////NAAAnP////8A//////gQAADU/////////8wAABD4/////wD//////3AAAGj/////////YAAAbP//////AP//////2AAADPT///////AIAADY//////8A////////RAAAlP//////kAAAQP///////wD///////+sAAAs//////8oAACo////////AP////////wYAADE////wAAAGPz///////8A/////////3wAAFj///9YAAB4/////////wD/////////5AQACOj/6AQABOD/////////AP//////////UAAAiP+IAABM//////////8A//////////+4AAAg+CAAALT//////////wD///////////8kAABoAAAg/P//////////AP///////////4wAAAAAAIj///////////8A////////////7AgAAAAE6P///////////wD/////////////YAAAAFj/////////////AP/////////////IAAAAwP////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8=', 
			'width' => 25
		), 
		'W' => array(
			'data' => 'AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A/////////////////////////////////wD/JAAAwP//////4AAAAOT//////8QAACT/AP9wAAB0//////+UAAAAnP//////fAAAcP8A/7wAACj//////0QAAABM//////80AAC8/wD/+AwAAOD////0CAAAAAj4////6AAADPj/AP//UAAAmP///6wAACAAALT///+cAABU//8A//+cAABI////XAAArAAAZP///1QAAJz//wD//+QAAAj4//wUABj/HAAc///8DAAA6P//AP///zAAALj/xAAAYP9kAADM/8AAADT///8A////fAAAbP94AACk/6gAAIT/eAAAgP///wD////EAAAg/ygAAOj/8AAAOP8sAADI////AP////wQAAC0AAAs////OAAAzAAAGP////8A/////1wAACAAAHT///+AAAA4AABg/////wD/////pAAAAAAAuP///8QAAAAAAKz/////AP/////sAAAAAAj4/////BAAAAAE9P////8A//////88AAAAQP//////VAAAAET//////wD//////4QAAACI//////+YAAAAjP//////AP//////zAAAAMz//////+AAAADc//////8=', 
			'width' => 25
		), 
		'X' => array(
			'data' => 'AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A/////////////////////////////////wD////////MBAAAyP/////IAAAEzP//////AP////////9wAAAs/P///CwAAHD///////8A//////////QcAACI//+IAAAc9P///////wD//////////7AAAAjg4AgAALD/////////AP///////////0wAAExMAABM//////////8A////////////4AwAAAAADOD//////////wD/////////////jAAAAACM////////////AP/////////////8IAAAIPz///////////8A/////////////8gEAAAEyP///////////wD////////////8MAAAAAAw/P//////////AP///////////4wAABgYAACM//////////8A///////////gDAAAqKgAAAzg/////////wD//////////1AAAEj//0gAAFD/////////AP////////+wAAAI4P//4AgAALD///////8A////////9BwAAIj/////iAAAHPT//////wD///////9wAAAs/P/////8LAAAcP//////AP//////zAQAAMj////////IAAAEzP////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8=', 
			'width' => 25
		), 
		'Y' => array(
			'data' => 'AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP/////QBAAAxP/////////EAAAE0P////8A//////+AAAAs/P///////CwAAID//////wD///////wwAACI//////+IAAAo/P//////AP///////8gEAAzg////4AgABMj///////8A/////////3QAAFD///9MAAB0/////////wD/////////+CgAALD/rAAAKPj/////////AP//////////yAQAIOwcAATI//////////8A////////////dAAAHAAAcP///////////wD////////////4KAAAACD0////////////AP/////////////IAAAAwP////////////8A//////////////8AAAD//////////////wD//////////////wAAAP//////////////AP//////////////AAAA//////////////8A//////////////8AAAD//////////////wD//////////////wAAAP//////////////AP//////////////AAAA//////////////8A//////////////8AAAD//////////////wD/////////////////////////////////AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8=', 
			'width' => 25
		), 
		'Z' => array(
			'data' => 'AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A/////////////////////////////////wD/////////////////////////////////AP////////////////////////////////8A/////////wAAAAAAAAAAAAAAAAD//////wD/////////AAAAAAAAAAAAAAAAAP//////AP////////8AAAAAAAAAAAAAAAAA//////8A////////////////////2BgAAHD//////wD//////////////////9gYAABw////////AP/////////////////oIAAAVP////////8A////////////////6CgAAFT8/////////wD///////////////QwAAA8/P//////////AP/////////////0PAAAPPT///////////8A/////////////EAAACz0/////////////wD///////////xUAAAo6P//////////////AP//////////WAAAHOj///////////////8A/////////3AAABjY/////////////////wD///////9wAAAQ2P//////////////////AP///////wAAAAAAAAAAAAAAAAAA//////8A////////AAAAAAAAAAAAAAAAAAD//////wD///////8AAAAAAAAAAAAAAAAAAP//////AP////////////////////////////////8=', 
			'width' => 25
		), 
	);

	return $_png;
}

// These define base64_encoded raw png image data used
// when we cannot generate our own single png image
function define_raw_pngs()
{
	$_png = array(
		'1' => 'iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAAAAADhgtq/AAAAFXRFWHRDcmVhdGlvbiBUaW1lAAfTARARLTT8fD5jAAAAB3RJTUUH0wEQES4Duu0r3wAAAAlwSFlzAAALEgAACxIB0t1+/AAAAEpJREFUeNpj/M+AAzAxDBGZQEZGJN5/BFiCwkVizmDBLnPGA80IOBMoqoFLxuUFdhmTNWB9CBlG5LAGuvk/dv8MfIjSUIYRZ3oDAFCne4mhLZbsAAAAAElFTkSuQmCC', 
		'2' => 'iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAAAAADhgtq/AAAAFXRFWHRDcmVhdGlvbiBUaW1lAAfTARARLgTxiF0MAAAAB3RJTUUH0wEQES4NXVUG2AAAAAlwSFlzAAALEgAACxIB0t1+/AAAAKRJREFUeNpj/M+AAzAx0EbmYqwoI6/tQoTUfyiYwwLhh/yBCsBk7rDAlE5AkylgYFA58f+BBwODBpqMDgPDBiD1BGE+zBAVhicuQEqEgYEH3QUQsIGBwQHNNAh4ocDAsASbzAsNBgaLP1hkQBISD/5jytwBSqjc+I8p80QCaNSL/5gyH4A6Qr78xyLjw8Bg8OM/FpkDGGEMo0NwyghgyDDSKYUAAIikH4Lr7MUuAAAAAElFTkSuQmCC', 
		'3' => 'iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAAAAADhgtq/AAAAFXRFWHRDcmVhdGlvbiBUaW1lAAfTARARLg4RXbQSAAAAB3RJTUUH0wEQES4a3oaDHwAAAAlwSFlzAAALEgAACxIB0t1+/AAAALJJREFUeNpj/M+AAzAx0FtmoSUno27jV4TUfygIgXB1PsAEYDJLYCpT0GVcGBh8nvzoYWDgQZfJsBB58v//DwYGDnQZMHiRwcAQgE3GA2iNzANsMgZAsxb8xyYjAnKbxx8sMnf+XzFhYGjA5oL//28wMChguhrse0SowGgdBoY5QOoMA4MImkwFUGjH/zMaDAwxaDIvBKDhxnEF3QUHeCASazD9cyNGhEEk5gacz0jVtAMA3vM11y3nNGMAAAAASUVORK5CYII=', 
		'4' => 'iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAAAAADhgtq/AAAAFXRFWHRDcmVhdGlvbiBUaW1lAAfTARARLht8gFD5AAAAB3RJTUUH0wEQES4q+F+zswAAAAlwSFlzAAALEgAACxIB0t1+/AAAAG5JREFUeNpj/M+AAzAxECXzUZaREcH7jwQSkPnIMlsYcMh8kMElk8CAQwZolgNWGaBZPA+wygDNmvEfm8wGBgaf/9hk3ogwCDzBKhPCwLDgP6oMIyREkQKF4T9JIYoCGFHihxFuFjVMIyO2aSgDANQ6ybaxis6hAAAAAElFTkSuQmCC', 
		'5' => 'iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAAAAADhgtq/AAAAFXRFWHRDcmVhdGlvbiBUaW1lAAfTARARLitaWWBVAAAAB3RJTUUH0wEQES8bsJqCyAAAAAlwSFlzAAALEgAACxIB0t1+/AAAALFJREFUeNpj/M+AAzAxDBaZp4xQgCFzBKeeE5jG/YcAAwaGLf9RAFTmCwsDwxtUGahpJ/4w8MRwctruxTCtAcadgm6aB0yG5QKaaSwqPDVP/h/RYPjTg+42CNjDwCADYzOixM9foBNhAlDTYk0FPwKpPwwMHGimAT06A0jtYGAwQXNbDQMDz5b/RyQYGDrQZF4IQI2Q+YAm838LxHyBE+g+/f//SogAg0zKA4QPGMlIVQC8RM0SoAcH5wAAAABJRU5ErkJggg==', 
		'6' => 'iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAAAAADhgtq/AAAAFXRFWHRDcmVhdGlvbiBUaW1lAAfTARARLx2M+MSNAAAAB3RJTUUH0wEQES8nn/X+TwAAAAlwSFlzAAALEgAACxIB0t1+/AAAALpJREFUeNpj/M+AAzAxEJb5u9CSk1F36l+4wH8o+OIC4YfABOAyCTClK9BkrjAw8Kz58cKHgcEBTaaGgWEOkHoioBOCJuPAwPDlPwqAue0Bg8IZW1ZO150M6G5jYBBhAfMnoLsNoXQPpozNlf9XLBgYfNBkJBgY7kAcL4AmYwK1EWEzzG1AmYNA6iHQJWhu28PAILPn/wNg4AWgh5sNTOkRdJkXGgzY/fP//4cKFQYelx1wPiNFsU1bGQDEywU+yR12dwAAAABJRU5ErkJggg==', 
		'7' => 'iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAAAAADhgtq/AAAAFXRFWHRDcmVhdGlvbiBUaW1lAAfTARARLyjaSwCuAAAAB3RJTUUH0wEQES8zhS8qMgAAAAlwSFlzAAALEgAACxIB0t1+/AAAAI1JREFUeNpj/M+AAzAxDF4ZFijNiBD6T7k9/8FgC5DVwYAiBAFvRBgYEmAcFJkUBgaNL9hkLgAdeuQ/NpkQBoaY/9hknrAwsNxBcJFcPecPQ4AykrMRilQYGHb8x2baDQYGkT//sZl2gIHBhRlr6JxgYLDBHm5XGBhMsMs8YWBQQJZhhKcqRnjMoMugAwCrgqmfFNotugAAAABJRU5ErkJggg==', 
		'8' => 'iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAAAAADhgtq/AAAAFXRFWHRDcmVhdGlvbiBUaW1lAAfTARARLzTOSlzhAAAAB3RJTUUH0wEQETADbqwUAAAAAAlwSFlzAAALEgAACxIB0t1+/AAAALZJREFUeNpj/M+AAzAx0ETm70JbXkZex4V/4VL/IeCPD5Qf8gcqApNpgSttQZNRYWDwuPP/AVCnCpoMUPEbIPUGYT7MBRJw0yTQ3BbBwJDykOFuAgNDDJrbvjhA+S5f0Oz5/0YDLKHx4T+azAsFqB6FF2gyKUBXP/h/B+jqGDQZAbiredBc/YGB4Q+E9QXNbUD7ba6ATdNBM20C3KdT0GT+wDyIEdb//2/wEGDgcVgA5zPSKYUAAFSL7HPrRuxSAAAAAElFTkSuQmCC', 
		'9' => 'iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAAAAADhgtq/AAAAFXRFWHRDcmVhdGlvbiBUaW1lAAfTARARMAQlyWLTAAAAB3RJTUUH0wEQETARnRVlSAAAAAlwSFlzAAALEgAACxIB0t1+/AAAAL1JREFUeNpj/M+AAzDBWXtdeRlVKz8ipP5DwQQIV+MFTAAmcwSm0gZdJoCBwePB/z0yDAx70GREGBgeAKkDDAw5aDIwGxkYTKAiMLcJMDBcBVJ3GRieoLnNh4HB4sL/KzYIERi9B8MfaP5hEcGU+b/DhoPF5oQMgwKGDBh8YGBwQXNbpKHgUyC1BugQNPscGBh8XvxYw8PAcAXNtAUwlSno4fbHBSLh8QVd5v+fBhUGDpsFf+COYSQiTgefDACF9AMfUn6JTgAAAABJRU5ErkJggg==', 
		'A' => 'iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAAAAADhgtq/AAAAFXRFWHRDcmVhdGlvbiBUaW1lAAfTARALJAlkZ++dAAAAB3RJTUUH0wEQDQYRATWuKQAAAAlwSFlzAAALEgAACxIB0t1+/AAAAMRJREFUeNpj/M+AAzAxDAmZSEZGxkgkqf8w8IYDyON4A+cj9Kz4ASR+rMCix4CBIYWBwQDOh8ucYWCQ+CHBwHAGw7QFDAwx7AlgGtW0HwIMDDf+32FgEPiBZhrQZgsg5QB0CZqMCwPDHCC1hIHBBVXmAQMDzxeooQ9QXAC09wsPMAw4PiDcAFEggxQqMsim7UAJyx1IMhEMDB1QixsYGCIQMsDAZHnyH+4WSLCCZaYg3Ap2/xS4DDAwl8BllkCDlRFnegMAwY7xbBxxcUwAAAAASUVORK5CYII=', 
		'B' => 'iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAAAAADhgtq/AAAAFXRFWHRDcmVhdGlvbiBUaW1lAAfTARALKQc2cbzXAAAAB3RJTUUH0wEQCyoJL+UhYwAAAAlwSFlzAAALEgAACxIB0t1+/AAAAI5JREFUeNpj/M+AAzBBKEYo4NVt/AuT+g8GSGpD/kOFMGQYpqDLgOk7AQwMClhl/n+As5jQXPSHgUECxW0wcDGBgcEHl9skHmA3jYHBAKd/YJrQ3PbmiAXMr+iu/v+CgUEAuz0sDAwf0OwB03/OAN1sAmaywBUzwlkJuNwW8gdrWMs4zIBI/GckFKeDUgYAoqOxJeAC4XsAAAAASUVORK5CYII=', 
		'C' => 'iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAAAAADhgtq/AAAAFXRFWHRDcmVhdGlvbiBUaW1lAAfTARAPDC0CMA06AAAAB3RJTUUH0wEQDw0LHCda9gAAAAlwSFlzAAALEgAACxIB0t1+/AAAAL1JREFUeNpj/M+AAzAx0EZma6QiI6Oo68SPMIH/EPDCASYgsgUiApV5oYIwhuUEsowLAwNPw5X/f67UcDAwmCDJHAFKQFT+36FScwFJJoOBoeY/GoDI6DAwXMAuw8PA8ANdhhEcboxA12P3KQsDw1fsMkDf3MEuY8LAsAYj4MC2bQH65wbE4jWo/vlvAZTquPP/x4USoJUOyDIPJBCm8KDo+X/HBCahcgYlrP///7MiAKhPJWLFHxSf0j6FAAChr925OULRHAAAAABJRU5ErkJggg==', 
		'D' => 'iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAAAAADhgtq/AAAAFXRFWHRDcmVhdGlvbiBUaW1lAAfTARAPEwCKtV/RAAAAB3RJTUUH0wEQDxMWq2AJ8AAAAAlwSFlzAAALEgAACxIB0t1+/AAAAI9JREFUeNpj/M+AAzAxDBIZRgjg1Ey9CJf6DwYIpSwT/kOF0GUYGJagy4CoB0tUGBhEvmCR+f//hQIDwxysMv9nMDAEYJe5wcCgAmYwQixnBMpA7P/LwsDyGyyELgNnYoTBX5yhc4eBQQG7zBkGBh200IEw/uhg98+HPQ5YwgB3uOEPaw6dgjtQCVgYkBTbAM6Zv7Fcumw9AAAAAElFTkSuQmCC', 
		'E' => 'iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAAAAADhgtq/AAAAFXRFWHRDcmVhdGlvbiBUaW1lAAfTARAPKwnk9luOAAAAB3RJTUUH0wEQDysinUtBvgAAAAlwSFlzAAALEgAACxIB0t1+/AAAADRJREFUeNpj/M+AA7BAKEZkMYhiJlxa8MiwoJlBjB7K7IE7/D81TKO7qykIa8b/pOsZeBkA99MHN4IuLhUAAAAASUVORK5CYII=', 
		'F' => 'iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAAAAADhgtq/AAAAFXRFWHRDcmVhdGlvbiBUaW1lAAfTARAPLRo2Er3WAAAAB3RJTUUH0wEQDy0qxcpuCgAAAAlwSFlzAAALEgAACxIB0t1+/AAAAC5JREFUeNpj/M+AAzAxUFOGBUozIon9p9g0uCE0djWSw/9TxTSaupq6Mox0SiEAfGsGNUcKjuQAAAAASUVORK5CYII=', 
		'G' => 'iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAAAAADhgtq/AAAAFXRFWHRDcmVhdGlvbiBUaW1lAAfTARAPLSz5qChPAAAAB3RJTUUH0wEQDy4C21KVMwAAAAlwSFlzAAALEgAACxIB0t1+/AAAAMZJREFUeNpj/M+AAzAxDAGZi5m6nIyMsq69b2Ei/8HgSwJcrcAGiBBE5osNkjEsO5BkQDoCdnz4/+NEAQsDgw5C5gJQYgnEjP8rVGouIGQKGBgS/qMDsIwBA8MRDBlGUFhz/mD4wY7uH7AMI9DxEB7UJ3Cf8jAwvEXXApHRYGA4g13Gg4FhCjxA4FIgzg0g3QN10g9YiEHIFCDXBxgGHw5USKDKoIQbgwuSzP8vKXBxiRl/kGX+/79SYiLAwKKRsOEPchhgBQB9F8jGMXh9NAAAAABJRU5ErkJggg==', 
		'H' => 'iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAAAAADhgtq/AAAAFXRFWHRDcmVhdGlvbiBUaW1lAAfTARAQBRyVVlIEAAAAB3RJTUUH0wEQEAUtEYmxTgAAAAlwSFlzAAALEgAACxIB0t1+/AAAADJJREFUeNpj/M+AAzAxkC7DAqEYGRj+o7HIMY1eMixwFiNVTUOEwWDxKW4ZRqqmA3JkANxfBTYyJCNIAAAAAElFTkSuQmCC', 
		'I' => 'iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAAAAADhgtq/AAAAFXRFWHRDcmVhdGlvbiBUaW1lAAfTARAQBS5dgQOEAAAAB3RJTUUH0wEQEAgLdipK/gAAAAlwSFlzAAALEgAACxIB0t1+/AAAAC5JREFUeNpj/M+AAzAxUFOGBcZghDH+U2AaI5KrGRFGUd3VQ1MGHjpUD2tauhoAL4kHLSByJyAAAAAASUVORK5CYII=', 
		'J' => 'iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAAAAADhgtq/AAAAFXRFWHRDcmVhdGlvbiBUaW1lAAfTARAQBiJ/GhxsAAAAB3RJTUUH0wEQEAYv1KqDoQAAAAlwSFlzAAALEgAACxIB0t1+/AAAAF9JREFUeNpj/M+AA7Cg8RkZGKBqmXBpGYEyjIyMP4HUX0wZGQaGM0DqBpgFAf8hIISBQePC/wc+DAw+UBGYzB64KVvQZP4XQCVK/qPL/F9iw8Mg4rMFzmf8T8jVg1EGAPXwTNe70jweAAAAAElFTkSuQmCC', 
		'K' => 'iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAAAAADhgtq/AAAAFXRFWHRDcmVhdGlvbiBUaW1lAAfTARAQBjH7pF2yAAAAB3RJTUUH0wEQEAcD/2neAwAAAAlwSFlzAAALEgAACxIB0t1+/AAAAKJJREFUeNpj/M+AAzAxDHoZRkZGCOOrLSOj5E0w8z8YwBhfbBgYJG5AhFBkfiAkUGX+hCAkUGRQJJBlQBI8R/5jyoAkGAr+Y5FJAbmU5wUWGaCwCQNDAjYZniM3WBgYzmDKsGz4/z+HgcEGU2YNkPogwMCwAot/gGAGA4PCD6wyf3QYGBqwyvzfw8DA8QSrzH8fBoYY7DJ3OBgYToAYjDjTGwAi9CFdklYLWAAAAABJRU5ErkJggg==', 
		'L' => 'iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAAAAADhgtq/AAAAFXRFWHRDcmVhdGlvbiBUaW1lAAfTARAQDRPNMMWdAAAAB3RJTUUH0wEQEA0h0OZ3bQAAAAlwSFlzAAALEgAACxIB0t1+/AAAACtJREFUeNpj/M+AAzAx0EeGBUozMjD8p9y0URliwpoBHNwQ8J9s0xipmnYAn38EM7Wx8TEAAAAASUVORK5CYII=', 
		'M' => 'iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAAAAADhgtq/AAAAFXRFWHRDcmVhdGlvbiBUaW1lAAfTARAQDSKc7sWnAAAAB3RJTUUH0wEQEA0yVFg2swAAAAlwSFlzAAALEgAACxIB0t1+/AAAAMBJREFUeNq9USESgzAQXEoFIgKBQPIIBAJRkUfwjH6rrqICieALzCDzACSGGWaO5Jqm9DqtqOjOJLd3O7lschHhAw74qkQOA+cD89cz193OIAsmtWNUP4teOQKTZRMzLj66lcANvErhrUm8kjRCSSu0C5YWVSrfozH36GcbhaK0c2w9ayVcd6tCQQXU2glviDXMxUDH7/92As68i26GRk5HMuIPDFFuQ05B2U1B+yXuyYKSiXvo3saEjKJfp/0HZQMM+Go7G71AGgAAAABJRU5ErkJggg==', 
		'N' => 'iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAAAAADhgtq/AAAAFXRFWHRDcmVhdGlvbiBUaW1lAAfTARAQDTRoOnD2AAAAB3RJTUUH0wEQEA4GXsGRxQAAAAlwSFlzAAALEgAACxIB0t1+/AAAAH9JREFUeNpj/M+AAzAx0ECGkZGxF86CSv0HAyBD4A2MBRWCyzBk4JRhuYJLhsEBh4wGA8Ma7DI7GBhUfmCV+e/BwNCBXeYGC4PAC6wy/0sYGFKwy3wQYWA4g1Xm/wwGBhvsMn90kAMMWeb/Hpwy/wNwytzhwCXzvwLOYqRTCgEAzeMLiiDoTYkAAAAASUVORK5CYII=', 
		'O' => 'iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAAAAADhgtq/AAAAFXRFWHRDcmVhdGlvbiBUaW1lAAfTARAQKhH5+xbUAAAAB3RJTUUH0wEQECogfST1ngAAAAlwSFlzAAALEgAACxIB0t1+/AAAAN5JREFUeNpj/M+AAzAxUCDzd6mvJCOjbOjavzCR/xBwQwcmYHIHIgKVeSCBMEbiAbKMBQMDS8GFP/+vFLAwMNggyWwBSqyBqDkClNqBkElgYMiAWvi/gIEhASGjwcBwAiZzgoFBByHDwcDwBSbzhYGBA0QzgkOHEeh6uNugHIhPgbZ+hUl8BHNhMioMDGdgMhfAXJiMCQPDApgMkGGBCB2gfxig/lkDZG5BDQOGlDN//pzJAdphgRw6d5DD7Q6yzP8bBjAJgxsoYf3//58VATJA9QEr/kAFGGmTDkiQAQAL0dUS43e+3gAAAABJRU5ErkJggg==', 
		'P' => 'iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAAAAADhgtq/AAAAFXRFWHRDcmVhdGlvbiBUaW1lAAfTARAQKiJGK3fCAAAAB3RJTUUH0wEQECo0Z/4h4wAAAAlwSFlzAAALEgAACxIB0t1+/AAAAHJJREFUeNpj/M+AAzAxkC/DCAWKjkthUv8hAElxAVQEU4ZhA7oMiHqxRoKBwQGbzP//JxgYeMAMRqg5jEAZVBaGq/cyMAiguw1EPVgAtCcAl9tYTuCQYZmC1T8COilXoCIsCMVooU5JWFNHhpEm6YAUGQDGfYIo3V/ScAAAAABJRU5ErkJggg==', 
		'Q' => 'iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAAAAADhgtq/AAAAFXRFWHRDcmVhdGlvbiBUaW1lAAfTARAQKjXF+PIFAAAAB3RJTUUH0wEQECsMVueoPAAAAAlwSFlzAAALEgAACxIB0t1+/AAAAOlJREFUeNpj/M+AAzAxDBaZvysDZRkZJT1n/oRL/QeDGwYwvswRiMh/iMwdCYQpLAeQZUyAAjln/vy5UMAC1PUDIbMFKLEGonILUGoKQiaBgSEDavr/DAYGD4SMBgPDCZjMCQYGCYQM0IQvMJkvMPcygsKaEciBuw3GAfsUqOcrTOIj0DREGKgwMFyByRxhYNBByFgwMCwAUgcNJ/5k6GBg8EGEzhaIzx0YGBQiGBgEPiCFAVATS8WNOyUsILUTcIUbA0fOFYQMUliD7UWS+f9nRQjQhRIec4AWMaxAlkGAEwEqf+BhgBUAALev3zKS4lWgAAAAAElFTkSuQmCC', 
		'R' => 'iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAAAAADhgtq/AAAAFXRFWHRDcmVhdGlvbiBUaW1lAAfTARAQKw304XvaAAAAB3RJTUUH0wEQECsZOzpM1wAAAAlwSFlzAAALEgAACxIB0t1+/AAAAJVJREFUeNpj/M+AAzAxEJBhhAJR09avMKn/YICkVucLVAhDhqEBXQZMPwlgYNDAKvP/A5yF7rY/DAwc2Fz982oKA4MJLrcxbMHhNpYp/7HLFDz5jyHz/88ZCwYGiRtYZP7//wKUMviDTeb/CwkGhilYZf6vYWAQ+YA9DBwYGCqwy1xgYWC5g1XmPzAQPMAMRvJjm+YyAEO38KG0H/A5AAAAAElFTkSuQmCC', 
		'S' => 'iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAAAAADhgtq/AAAAFXRFWHRDcmVhdGlvbiBUaW1lAAfTARAQLAjLyhmSAAAAB3RJTUUH0wEQECwTlK4zDgAAAAlwSFlzAAALEgAACxIB0t1+/AAAAMhJREFUeNpj/M+AAzAxEJTZ6CnJyKgYexAh9R8M/oTA+CX/oQAq04FQugVVRoWBIeHK/z9HDBgYbFBlgGq/gOgbDAwcqDIKDAwue/78RwZQmQaQDQIBHRcwZP64QO1XmfIHVeb/nzkaULmQP6gyQHBlQoAASKoDQwYEjgBN1UGWAaq9A+F/gAcLJNwsGBgaILbcYWDgQQ63FSCbj/z4/2KOBAODD4o9DohwYzmBIvPCBibBswbdPysCZIChYFHzAuZMRkpim8YyAOsdCISZ+ev1AAAAAElFTkSuQmCC', 
		'T' => 'iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAAAAADhgtq/AAAAFXRFWHRDcmVhdGlvbiBUaW1lAAfTARAQLBTfy0XdAAAAB3RJTUUH0wEQECwfnRh/JQAAAAlwSFlzAAALEgAACxIB0t1+/AAAACpJREFUeNpj/M+AAzAxDF4ZFgjFiCz2n2zTGJHCgBFm0CDx6XCTYcSZ3gCeXgUv6KfdPQAAAABJRU5ErkJggg==', 
		'U' => 'iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAAAAADhgtq/AAAAFXRFWHRDcmVhdGlvbiBUaW1lAAfTARAQLCD+f7FoAAAAB3RJTUUH0wEQECwrvKyLkAAAAAlwSFlzAAALEgAACxIB0t1+/AAAAG1JREFUeNpj/M+AAzDhkmBggVCMDAz/0Vi49Yw4GRkGhqdgxksGBgkUGSBvA5hxgIFBBKrpPxgUMDDwzHnz/8MCHgaGFIgQVOYOD9x8lgsoMv/XwKRY5vxHlfn/oECHg4FBI+UKTICRjNimlwwARYdIjOH9U8UAAAAASUVORK5CYII=', 
		'V' => 'iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAAAAADhgtq/AAAAFXRFWHRDcmVhdGlvbiBUaW1lAAfTARAQLCz3yf1DAAAAB3RJTUUH0wEQEC0RY7tjYwAAAAlwSFlzAAALEgAACxIB0t1+/AAAAO9JREFUeNpj/M+AAzAx4JUxZWRcCheZychoCmb8B4I5DAwW/2HAhIFhDogGy/wQYGC4ApU4w8Ag8APEAJvGnsDAMAVqGJCOYYeb9v8GAwPPFzDrAwcDw43/cNP+/3cBKgYzJjAwOPxHllnDwKADZmgwMKxAkfkjwcBwBEgfYGCQ+AMRgvqUOQXihhkMDAnMULdAHfuEhYHjxf8XLAwsD/6j6GGQ9mH4sYBhwR8GD3kGVD3/dzAwKPxQYGDYAhOAy/xXYWDIAMr+wZTpARvR8R9T5g3Q9wwsL7DI/AcGHkPEf2wyJ4AyBxBcRnJjmw4yABm3A/7Dos83AAAAAElFTkSuQmCC', 
		'W' => 'iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAAAAADhgtq/AAAAFXRFWHRDcmVhdGlvbiBUaW1lAAfTARAQLRNYtOE/AAAAB3RJTUUH0wEQEDkxdnuU/gAAAAlwSFlzAAALEgAACxIB0t1+/AAAARRJREFUeNrVkaFOA0EQhv8tJNeEiiYgEBU1TSoqEBUnKipOnOhDIJA8Co+BrKioqDgBCSQIHgHRJm1SgUBUrLjk55/ZO1A8AJPc3L87e9/8sxeIP6KD/1kZhfDschdCOLh6DWGkygJY+9ryxpWydlkBY1oU2lu4mgIVwdgDtlqeuqp0o9QR6EV2kBUJsokYIlYJW2bmTcgV/HlISudKvci9Q+orXHOAAVkLf6T6kDc6xhfglnfAO5+AqXZtUvcteAFvmTwbzY4POTbEF5BzArwx0eq++5lI5sJBDRvamT6+T37KH+U0PqY7SWDFkg2Nn2jHNzDOTy0NlzOlPDOwXd784vf/NC1gzlvlNMqPRrTYSn24+gaIIJkw9zhm8QAAAABJRU5ErkJggg==', 
		'X' => 'iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAAAAADhgtq/AAAAFXRFWHRDcmVhdGlvbiBUaW1lAAfTARAQOTNNdBaiAAAAB3RJTUUH0wEQEDoJdVR/owAAAAlwSFlzAAALEgAACxIB0t1+/AAAALlJREFUeNq90r0NgzAQBeDnCCQKBqCgYAAKxqCgYBTGoGAQSsYACXbwEJSWXnw4IeYnXRRLlix/ss/3ZEV8GQ/8VZZQqUkWk1Lh4vboRgPkhjQ50Lidt6wp0JItkK5H4QBEWkfAwJOwAiqZvIiOpWysr8JOpOONmAzIzJ2MARCMN2IKua0wV7FlksQrtIs8re+9x+1SAyVZAvVJtgjsyU8IOMTmB4dD1H7YTmbbyquT0TY1y0L99O88AROVMIqClFRpAAAAAElFTkSuQmCC', 
		'Y' => 'iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAAAAADhgtq/AAAAFXRFWHRDcmVhdGlvbiBUaW1lAAfTARAQOgo5XM1pAAAAB3RJTUUH0wEQEDoXj1tCwAAAAAlwSFlzAAALEgAACxIB0t1+/AAAAJ1JREFUeNpj/M+AAzAxkClzkZWR8ShU4CgjI+tFMOs/CDQwMOj8AbP+6DAwNIBZEJk/BgwMHWBWBwODxh8kmf8nWBh4HgDpBxwMLCf+I8v8L2FgCABSPgwMJf9RZX5oMDBs+L8GaNYPNBmQeQpvZOBmIcmAzJNhYCj4jykDMo9B4QsWmf8ngDIHEFxGpBBlBPqbCiFKBxlG6qcDEmUAcCSyeDODHbsAAAAASUVORK5CYII=', 
		'Z' => 'iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAAAAADhgtq/AAAAFXRFWHRDcmVhdGlvbiBUaW1lAAfTARAQOhjK5bwhAAAAB3RJTUUH0wEQEDonqYJybAAAAAlwSFlzAAALEgAACxIB0t1+/AAAAH5JREFUeNpj/M+AAzAxDF4ZFijNiCz4n1J7/kPBDQkGhgJUISQJCBNV5oUCA0PIfywyLzSAEn+wyHwxYGCwgUkgy3yxAUp8+Y8p88eBgUEHIYGQ+RPCwKDx4j8WmQgGBhlkCbgM0HcSN/5jkQFKCKBK/Gf8jxnU5Ic1I870BgC8VeNYE4D9rwAAAABJRU5ErkJggg==', 
	);

	return $_png;
}
//
// FUNCTIONS
// ---------

?>