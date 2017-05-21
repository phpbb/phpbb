<html>
	<head>
		<title>Duplicate Language Keys</title>
	</head>
	<body>
<?php
//
// Security message:
//
// This script is potentially dangerous.
// Remove or comment the next line (die(".... ) to enable this script.
// Do NOT FORGET to either remove this script or disable it after you have used it.
//
die("Please read the first lines of this script for instructions on how to enable it");

// -------------------------------------------------------------
//
// @copyright (c) phpBB Limited <https://www.phpbb.com>
// @license GNU General Public License, version 2 (GPL-2.0)
//
// For full copyright and license information, please see
// the docs/CREDITS.txt file.
// 
// -------------------------------------------------------------
// Thanks to arod-1

define('IN_PHPBB', 1);
$phpEx = substr(strrchr(__FILE__, '.'), 1);
$phpbb_root_path='./../';
include($phpbb_root_path . 'common.'.$phpEx);

$mode = $request->variable('mode', '');

$modules = find_modules($phpbb_root_path . 'language/en');

$kkeys = $keys = array();
$langdir = dirname(__FILE__);

if (isset($lang))
{
	unset($lang);
}

foreach($modules as $module)
{
	require_once("$langdir$module");
	if (isset($lang))
	{
		$kkeys[$module] = $lang;
		$keys[] = $module;
		unset($lang);
	}
}

$equal = $case = $diff = 0;
$output = array();

while ($module = array_shift($keys))
{
	$keys_1 = array_keys($kkeys[$module]);

	foreach ($keys as $other_module)
	{
		$keys_2 = array_keys($kkeys[$other_module]);

		foreach(array_intersect($keys_1, $keys_2) as $dup)
		{
			if ($kkeys[$module][$dup] == $kkeys[$other_module][$dup])
			{
				$compare = "Equal";
				$equal++;
			}
			else if (strcasecmp($kkeys[$module][$dup], $kkeys[$other_module][$dup]) == 0)
			{
				$compare = "Differ in case";
				$case++;
			}
			else
			{
				$compare = "'{$kkeys[$module][$dup]}' - '{$kkeys[$other_module][$dup]}'";
				$diff++;
			}

			$color = '';
			if ((basename($module) == "common.$phpEx") || (basename($other_module) == "common.$phpEx"))
			{
				$color = ' style="color:#B00000;"';
			}

			switch ($mode)
			{
				case 'module':
					$output[$module][] = "<tr$color><td>" . ((isset($output[$module])) ? '&nbsp;' : "<b>$module</b>" ) . "</td><td>$dup</td><td>$other_module</td><td>$compare</td></tr>";
				break;

				default:
					$output[$dup][] = "<tr$color><td><b>$dup</b></td><td>$module</td><td>$other_module</td><td>$compare</td></tr>";
				break;
			}
		}
	}
}//var_dump($output);

echo "<p><a href=\"lang_duplicates.php\">By Key</a> <a href=\"lang_duplicates.php?mode=module\">By Module</a></p><p>Equal: <b>$equal</b>, Differ in case only: $case, differ in content: $diff</p>";
switch ($mode)
{
	case 'module':
		echo "<table cellpadding=\"4\"><tr><th>Key</th><th>First File</th><th>Second File</th><th>Difference</th></tr>";
		foreach ($output as $module => $html)
		{
			echo implode('', $html);
		}
	break;

	default:
		ksort($output);
		echo "<table cellpadding=\"4\"><tr><th>File</th><th>Key</th><th>Conflicting File</th><th>Difference</th></tr>";
		foreach ($output as $dup)
		{
			echo implode('', $dup);
		}
	break;
}

echo "</table>";


function find_modules($dirname)
{
	$list = glob("$dirname/*.php");

	foreach(glob("$dirname/*", GLOB_ONLYDIR) as $name)
	{
		$list =  array_merge($list, find_modules($name));
	}
	return $list;
}

?>
	</body>
</html>
