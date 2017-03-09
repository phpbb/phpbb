<?php

/*
	Converts imageset to CSS code
	
	Change style name and path below, open in browser.
*/

$phpbb_root_path = '../';
$style = 'prosilver';

$imageset_path = $phpbb_root_path . 'styles/' . $style . '/imageset';
$theme_path = $phpbb_root_path . 'styles/' . $style . '/theme';

// Start output buffering
ob_start();

// Get global and English images
$images_global = get_imageset($imageset_path);
if ($images_global === false)
{
	echo 'imageset.cfg was not found.';
	echo ob_get_clean();
	return;
}
$images_en = get_imageset($imageset_path, 'en');
if ($images_en === false)
{
	echo 'English imageset.cfg was not found.';
	echo ob_get_clean();
	return;
}

// Remove duplicate images
foreach ($images_en as $key => $row)
{
	unset($images_global[$key]);
}

// CSS replacements
$not_compatible = array(
	'{T_TEMPLATE_PATH}',
	'{T_IMAGESET_PATH}',
	'{T_IMAGESET_LANG_PATH}',
	'{T_STYLESHEET_NAME}',
	'{S_USER_LANG}'
);
$replace = array(
	'{T_THEME_PATH}'	=> '.',
);
// Enable/disable one of lines below to enable/disable replacement of English buttons
// $replace = array_merge($replace, get_replacements($images_global));
$replace = array_merge($replace, get_replacements($images_global), get_replacements($images_en));

// BIDI code
$bidi_code = css($images_global, './images/', true);

// Get all CSS files, parse them
$files = list_files($theme_path, 'css');
if ($files === false || !count($files))
{
	echo 'No CSS files found in theme directory.<br />';
}
else for ($i=0; $i<count($files); $i++)
{
	$file = $theme_path . '/' . $files[$i];
	$data = file_get_contents($file);
	$hash = md5($data);
	$data = strtr($data, $replace);
	$errors = false;
	for($j=0; $j<count($not_compatible); $j++)
	{
		if (strpos($data, $not_compatible[$j]) !== false)
		{
			echo 'Error: ', $file, ' contains ', $not_compatible[$j], '. That variable cannot be converted.<br />';
			continue;
		}
	}
	if (basename($file) == 'bidi.css' && strpos($data, '/* Former imageset */') === false && strlen($bidi_code))
	{
		// Add bidi data
		$data .= "\n/* Former imageset */\n" . $bidi_code;
		$bidi_code = '';
		echo 'Note: RTL imageset entries were added at the end of file below:<br />';
	}
	if (md5($data) == $hash)
	{
		echo 'Nothing to replace in ', $file, '<br />';
	}
	else
	{
		echo 'Updated ', $file, ':', dump_code($data, $files[$i]);
	}
}

// Check if there are invalid images in imageset
$list = array_merge($images_global, $images_en);
foreach ($list as $key => $row)
{
	if ($row['skip'])
	{
		echo 'Unable to generate code to add to CSS files because some images are missing or invalid. See errors above.';
		echo ob_get_clean();
		return;
	}
}

// Code to add to CSS files
$code = '
/* Former imageset */
span.imageset {
	display: inline-block;
	background: transparent none 0 0 no-repeat;
	margin: 0;
	padding: 0;
	width: 0;
	height: 0;
	overflow: hidden;
}

/* Global imageset items */
' . css($images_global, './images/') . '

/* English images for fallback */
' . css($images_en, './en/');
if (strlen($bidi_code))
{
	$code .= "\n/* RTL imageset entries */\n" . $bidi_code;
}
echo 'Code to add to CSS file:', dump_code($code, 'imageset.css');


$list = list_languages($imageset_path);
for ($i=0; $i<count($list); $i++)
{
	$lang = $list[$i];
	$images = get_imageset($imageset_path . '/' . $lang);
	if (!count($images))
	{
		continue;
	}
	$code = '/* ' . strtoupper($lang) . ' Language Pack */
' . css($images, './');
	echo 'New CSS file: ', $theme_path, '/', $lang, '/stylesheet.css', dump_code($code, 'stylesheet_' . $lang . '.css');
}

echo ob_get_clean();
return;


/*
	Functions
*/
function get_imageset($path, $lang = '')
{
	$cfg = $path . ($lang ? '/' . $lang : '') . '/imageset.cfg';
	if (!@file_exists($cfg))
	{	
		return false;
	}
	$data = file($cfg);
	$result = array();
	for ($i=0; $i<count($data); $i++)
	{
		$str = trim($data[$i]);
		if (substr($str, 0, 4) != 'img_') 
		{
			continue;
		}
		$list = explode('=', $data[$i]);
		if (count($list) != 2) 
		{
			continue;
		}
		$key = trim($list[0]);
		$row = explode('*', trim($list[1]));
		$file = trim($row[0]);
		$height = isset($row[1]) && intval($row[1]) ? intval($row[1]) : false;
		$width = isset($row[2]) && intval($row[2]) ? intval($row[2]) : false;
		$skip = false;
		if (strlen($file) && (!$width || !$height))
		{
			// Try to detect width/height
			$filename = $path . ($lang ? '/' . $lang : '') . '/' . $file;
			if (!@file_exists($filename))
			{
				echo 'Error: file ', $filename, ' does not exist and its dimensions are not available in imageset.cfg<br />';
				$skip = true;
			}
			else
			{
				$size = @getimagesize($filename);
				if ($size === false)
				{
					echo 'Error: file ', $filename, ' is not a valid image<br />';
					$skip = true;
				}
				else
				{
					if(!$width) $width = intval($size[0]);
					if(!$height) $height = intval($size[1]);
				}
			}
		}
		$result[$key] = array(
			'lang'  => $lang,
			'file'  => $file,
			'height'	=> $height,
			'width' => $width,
			'skip'  => $skip
		);
	}
	return $result;
}

function get_replacements($list)
{
	$result = array();
	foreach ($list as $key => $row)
	{
		$key = '{' . strtoupper($key);
		$result[$key . '_SRC}'] = strlen($row['file']) ? ($row['lang'] ? './' . $row['lang'] : './images') . '/' . $row['file'] : '';
		$result[$key . '_WIDTH}'] = intval($row['width']);
		$result[$key . '_HEIGHT}'] = intval($row['height']);
	}
	return $result;
}

function list_files($dir, $ext)
{
	$res = @opendir($dir);
	if ($res === false)
	{
		return false;
	}
	$files = array();
	while (($file = readdir($res)) !== false)
	{
		$list = explode('.', $file);
		if(count($list) > 1 && strtolower($list[count($list) - 1]) == $ext)
		{
			$files[] = $file;
		}
	}
	closedir($res);
	return $files;
}

function list_languages($dir)
{
	$res = @opendir($dir);
	if ($res === false)
	{
		return array();
	}
	$files = array();
	while (($file = readdir($res)) !== false)
	{
		if (substr($file, 0, 1) == '.')
		{
			continue;
		}
		$filename = $dir . '/' . $file;
		if (is_dir($filename) && file_exists($filename . '/imageset.cfg'))
		{
			$files[] = $file;
		}
	}
	closedir($res);
	return $files;
}

function dump_code($code, $filename = 'file.txt')
{
	$hash = md5($code);
	if (isset($_GET['download']) && $_GET['download'] === $hash)
	{
		// Download file
		ob_end_clean();
		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Disposition: attachment; filename="' . $filename . '";');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: ' . strlen($code));
		echo $code;
		exit;
	}
	$list = explode("\n", $code);
	$height = 15 * count($list);
	echo ' [ <a href="?download=', $hash, '">download</a> <a href="javascript:void(0);" onclick="document.getElementById(\'code-', $hash, '\').style.height = \'', $height, 'px\'; this.style.display = \'none\'; return false;">expand</a> ]<br />';
	echo '<textarea id="code-', $hash, '" onfocus="this.select();" style="width: 98%; height: 200px;">', htmlspecialchars($code), '</textarea><br />';
}

function css($list, $path = './', $bidi = false)
{
	$code = '';
	// Change value to true if you want images to be grouped up by size
	$group = $bidi;
	if ($group)
	{
		// group up images by size
		$groups = array();
		foreach ($list as $key => $row)
		{
			if (!strlen($row['file']))
			{
				continue;
			}
			$groups[$row['width'] . '*' . $row['height']][] = $key;
		}
		foreach ($groups as $size => $keys)
		{
			$extra = '';
			for ($i=0; $i<count($keys); $i++)
			{
				$code .= ($i == 0 ? '' : ', ') . ($bidi ? '.rtl ' : '') . '.imageset.' . substr($keys[$i], 4);
				if (!$bidi)
				{
					$extra .= '.imageset.' . substr($keys[$i], 4) . ' { background-image: url("' . $path . $list[$keys[$i]]['file'] . "\"); }\n";
				}
			}
			$row = $list[$keys[0]];
			$code .= ' {';
			if ($bidi)
			{
				$code .= '
	padding-right: ' . $row['width'] . 'px;
	padding-left: 0;
}
';
			}
			else
			{
				$code .= '
	padding-left: ' . $row['width'] . 'px;
	padding-top: ' . $row['height'] . 'px;
}
' . $extra;
			}
		}
	}
	else
	{
		foreach ($list as $key => $row)
		{
			if (!strlen($row['file']))
			{
				continue;
			}
			$code .= ($bidi ? '.rtl ' : '') . '.imageset.' . substr($key, 4) . ' {';
			if ($bidi)
			{
				$code .= '
	padding-right: ' . $row['width'] . 'px;
	padding-left: 0;
}
';
			}
			else
			{
				$code .= '
	background-image: url("' . $path . $row['file'] . '");
	padding-left: ' . $row['width'] . 'px;
	padding-top: ' . $row['height'] . 'px;
}
';
			}
		}
	}
	return $code;
}

