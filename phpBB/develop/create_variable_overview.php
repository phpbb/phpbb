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

/**
* This script generates an index of some template vars and their use within the templates.
* It writes down all language variables used by various templates.
*/

//
// Security message:
//
// This script is potentially dangerous.
// Remove or comment the next line (die(".... ) to enable this script.
// Do NOT FORGET to either remove this script or disable it after you have used it.
//
die("Please read the first lines of this script for instructions on how to enable it");

$directory = '../styles/subSilver/template/';
$ext = 'html';
$store_dir = '../store/';

$phpfiles_directories = array('../', '../includes/', '../includes/acm/', '../includes/auth/', '../includes/mcp/', '../includes/ucp/');
// Template Files beginning with this names are merged together
$merge = array('gcp', 'login', 'mcp', 'memberlist', 'posting', 'ucp');

if (!is_writable($store_dir))
{
	die("Directory $store_dir is not writable!");
}

$contents = implode('', file('../adm/subSilver.css', filesize('../adm/subSilver.css')));
$fp = fopen($store_dir . 'subSilver.css', 'w');
fwrite($fp, $contents);
fclose($fp);

$html_skeleton = '
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="subSilver.css" type="text/css">
<style type="text/css">
<!--
th		{ background-image: url(\'cellpic3.gif\') }
td.cat	{ background-image: url(\'cellpic1.gif\') }
//-->
</style>
<title>{FILENAME}</title>
</head>
<body>

<table width="100%" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td><img src="header_left.jpg" width="200" height="60" alt="phpBB Logo" title="phpBB Logo" border="0"/></td>
		<td width="100%" background="header_bg.jpg" height="60" align="right" nowrap="nowrap"><span class="maintitle">File {FILENAME}</span> &nbsp; &nbsp; &nbsp;</td>
	</tr>
</table>

<table width="95%" cellspacing="0" cellpadding="0" border="0" align="center">
	<tr>
		<td><br clear="all" />

';
$html_skeleton .= '<br><a href="./index.html" class="gen">Back to Contents</a><br><br>';
$html_skeleton .= '<br><a href="#lang" class="gen">Language Variables</a> :: <a href="#includes" class="gen">Includes</a> :: <a href="#cond" class="gen">Conditionals</a><br><a href="#remain" class="gen">Remaining Vars</a> :: <a href="#usedby" class="gen">phpBB File Usage</a> :: <a href="#ref" class="gen">References</a>';
$html_skeleton .= '<br><br><a name="lang"></a><b>Language Variables</b><br><br>{LANGUAGE_VARIABLES}';
$html_skeleton .= '<br><br><a name="includes"></a><b>Included Files</b><br><br>{INCLUDES}';
$html_skeleton .= '<br><br><a name="cond"></a><b>Used Conditionals</b><br><br>{CONDITIONALS}';
$html_skeleton .= '<br><br><a name="remain"></a><b>Remaining Vars used</b><br><br>{REMAINING_VARS}';
$html_skeleton .= '<br><br><a name="usedby"></a><b>This Template File is used by the following phpBB Files</b><br><br>{USED_BY}';
$html_skeleton .= '<br><br><a name="ref"></a><b>References: </b>{SEE_FILES}';

//$html_skeleton .= "</body>\n</html>\n";

$html_skeleton .= '
<br><br>
<div class="copyright" align="center">Powered by <a href="http://www.phpbb.com/">phpBB</a>&reg; Forum Software &copy; phpBB Limited</div>

		<br clear="all" /></td>
	</tr>
</table>

</body>
</html>
';

// Open Language File
include('../language/en/lang_main.php');
include('../language/en/lang_admin.php');

$files_to_parse = $php_files = array();

$dhandler = opendir($directory);
if (!$dhandler)
{
	die("Unable to open $directory");
}

$num = 0;
while ($file = readdir($dhandler))
{
	if (is_file($directory . $file) && preg_match('#\.' . $ext . '$#i', $file))
	{
		$files_to_parse[$num]['filename'] = $directory . $file;
		$files_to_parse[$num]['single_filename'] = $file;
		$files_to_parse[$num]['destfile'] = str_replace(".{$ext}", '', $file) . '_' . $num . '.html';
		$file_to_destfile[$file] = $files_to_parse[$num]['destfile'];
		$num++;
	}
}
closedir($dhandler);

$num = 0;
foreach ($phpfiles_directories as $directory)
{
	$dhandler = opendir($directory);
	if (!$dhandler)
	{
		die("Unable to open $directory");
	}

	while ($file = readdir($dhandler))
	{
		if (is_file($directory . $file) && preg_match('#\.php$#i', $file))
		{
			$php_files[$num]['filename'] = $directory . $file;
			$php_files[$num]['single_filename'] = $file;
			$num++;
		}
	}
	closedir($dhandler);
}

$php_files_includes = $lang_references = array();

//$php_files_includes['viewtopic_attach_body.html'][0] = filename

echo '<br>Parsing PHP Files';

// Parse PHP Files and get our filenames
foreach ($php_files as $file_num => $data)
{
	echo '.';
	flush();
	$contents = implode('', file($data['filename'], filesize($data['filename'])));

	$html_files = array();
	preg_match_all('#([a-zA-Z0-9\-_]*?)\.' . $ext . '#s', $contents, $html_files);
	$html_files = array_unique($html_files[1]);

	foreach ($html_files as $html_file)
	{
		$html_file = trim($html_file);
		if ($html_file != '')
		{
			$php_files_includes[$html_file . '.' . $ext][] = $data['filename'];
		}
	}
}

echo '<br>Parsing HTML Files';
foreach ($files_to_parse as $file_num => $data)
{
	echo '.';
	flush();
	$contents = implode('', file($data['filename'], filesize($data['filename'])));

	// Language Variables -> [0]:tpl [1]:lang
	$lang_vars = array();
	preg_match_all('#{L_([a-z0-9\-_]*?)\}#is', $contents, $lang_vars);
	$contents = preg_replace('#{L_([a-z0-9\-_]*?)\}#is', '', $contents);
	$lang_vars[0] = array_unique($lang_vars[0]);
	$lang_vars[1] = array_unique($lang_vars[1]);

	// Includes
	$includes = array();
	preg_match_all('#<!-- INCLUDE ([a-zA-Z0-9\_\-\+\.]+?) -->#s', $contents, $includes);
	$contents = preg_replace('#<!-- INCLUDE ([a-zA-Z0-9\_\-\+\.]+?) -->#', '', $contents);
	$includes = $includes[1];
	$includes = array_unique($includes);

	// IF Conditions
	$switches = array();
	preg_match_all('#<!-- [IF]|[ELSEIF] ([a-zA-Z0-9\-_\.]+?) (.*?)?[ ]?-->#', $contents, $switches);
	$contents = preg_replace('#<!-- [IF]|[ELSEIF] ([a-zA-Z0-9\-_]) (.*?)?[ ]?-->#s', '', $contents);
	$switches[0] = array_unique($switches[1]); // No resorting please
	$switches[1] = $switches[2];
	unset($switches[2]);

	// Remaining Vars
	$remaining_vars = array();
	preg_match_all('#{([a-z0-9\-_\.]*?)\}#is', $contents, $remaining_vars);
	$contents = preg_replace('#{([a-z0-9\-_]*?)\}#is', '', $contents);
	$remaining_vars = array_unique($remaining_vars[1]);
	sort($remaining_vars, SORT_STRING);

	// Now build the filename specific site
	$fp = fopen($store_dir . $data['destfile'], 'w');
	$html_data = $html_skeleton;

	$html_data = str_replace('{FILENAME}', $data['single_filename'], $html_data);

	// Write up the Language Variables
	if (count($lang_vars[0]))
	{
		$lang_data = '<ul>';
		for ($num = 0; $num <= count($lang_vars[0]); $num++)
		{
			$var = $lang_vars[0][$num];
			if ($var != '')
			{
				$_var = str_replace(array('{', '}'), array('', ''), $var);
				$lang_references[$_var][] = $data['single_filename'];
				$lang_data .= '<li>' . $var . '<br>' . "\n" . ((isset($lang[$_var])) ? htmlspecialchars(str_replace("\\'", "'", $lang[$_var])) : '<span style="color:red">No Language Variable available</span>') . '<br></li><br>' . "\n";
			}
		}
		$lang_data .= '</ul>';
	}
	else
	{
		$lang_data = '<b>NONE</b><br>' . "\n";
	}

	$html_data = str_replace('{LANGUAGE_VARIABLES}', $lang_data, $html_data);

	// Write up the Includes
	echo '.';
	flush();
	if (count($includes))
	{
		$includes_data = '<ul>';
		$see_files = '';
		for ($num = 0; $num <= count($includes); $num++)
		{
			$var = $includes[$num];
			if ($var != '')
			{
				$includes_data .= '<li><a href="./' . $file_to_destfile[$var] . '" class="gen">' . $var . '</a></li><br>' . "\n";
				$see_files .= ($see_files != '') ? ' :: ' : '';
				$see_files .= '<a href="./' . $file_to_destfile[$var] . '" class="gen">' . $var . '</a>';
			}
		}
		$includes_data .= '</ul>';
	}
	else
	{
		$includes_data = '<b>NONE</b><br>' . "\n";
		$see_files = '<b>NONE</b>';
	}

	$html_data = str_replace('{INCLUDES}', $includes_data, $html_data);
	$html_data = str_replace('{SEE_FILES}', $see_files, $html_data);

	// Write up Conditionals
	echo '.';
	flush();
	if (count($switches[0]))
	{
		$conditionals = '<ul>';
		for ($num = 0; $num <= count($switches[0]); $num++)
		{
			$var = trim($switches[0][$num]);
			if ($var != '')
			{
				if ($var == 'not')
				{
					$conditionals .= '<li>' . trim($switches[1][$num]) . '<br><b>Negation</b><br>' . "\n";
					$block_var = explode('.', trim($switches[1][$num]));
					unset($block_var[0]);
				}
				else
				{
					$conditionals .= '<li>' . $var . ((trim($switches[1][$num]) != '') ? '<br>' . "\n" . '<i>Compared with</i> -&gt; <b>' . trim($switches[1][$num]) . '</b>' : '') . '<br>' . "\n";
					$block_var = explode('.', $var);
					unset($block_var[count($block_var)-1]);
				}

				if (count($block_var))
				{
					for ($_num = count($block_var)-1; $_num >= 0; $_num--)
					{
						$conditionals .= ($_num == count($block_var)-1) ? '<i>Element of Block</i> -&gt; <b>' . $block_var[$_num] . '</b><br>' . "\n" : '<i>...which is an element of</i> -&gt; <b>' . $block_var[$_num] . '</b><br>' . "\n";
					}
				}
				$conditionals .= '<br></li>' . "\n";
			}
		}
		$conditionals .= '</ul>';
	}
	else
	{
		$conditionals = '<b>NONE</b><br>' . "\n";
	}

	$html_data = str_replace('{CONDITIONALS}', $conditionals, $html_data);

	// Write up Remaining Vars
	echo '.';
	flush();
	if (count($remaining_vars))
	{
		$remaining = '<ul>';
		for ($num = 0; $num <= count($remaining_vars); $num++)
		{
			$var = trim($remaining_vars[$num]);
			if ($var != '')
			{
				$remaining .= '<li>' . $var . '<br>' . "\n";
				$block_var = explode('.', $var);
				unset($block_var[count($block_var)-1]);

				if (count($block_var))
				{
					for ($_num = count($block_var)-1; $_num >= 0; $_num--)
					{
						$remaining .= ($_num == count($block_var)-1) ? '<i>Element of Block</i> -&gt; <b>' . $block_var[$_num] . '</b><br>' . "\n" : '<i>...which is an element of</i> -&gt; <b>' . $block_var[$_num] . '</b><br>' . "\n";
					}
				}
				$remaining .= '<br></li>' . "\n";
			}
		}
		$remaining .= '</ul>';
	}
	else
	{
		$remaining = '<b>NONE</b><br>' . "\n";
	}

	$html_data = str_replace('{REMAINING_VARS}', $remaining, $html_data);

	if (isset($php_files_includes[$data['single_filename']]) && count($php_files_includes[$data['single_filename']]))
	{
		$usedby = '<ul>';
		foreach ($php_files_includes[$data['single_filename']] as $php_filename)
		{
			$usedby .= '<li>' . str_replace('../', '', $php_filename) . '</li>';
		}
		$usedby .= '</ul>';
	}
	else
	{
		$usedby = '<b>NONE</b><br>' . "\n";
	}

	$html_data = str_replace('{USED_BY}', $usedby, $html_data);

	fwrite($fp, $html_data);
	fclose($fp);
}

echo '<br>Store Files';

$fp = fopen($store_dir . 'index.html', 'w');

$html_data = '
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="subSilver.css" type="text/css">
<style type="text/css">
<!--
th		{ background-image: url(\'cellpic3.gif\') }
td.cat	{ background-image: url(\'cellpic1.gif\') }
//-->
</style>
<title>Contents</title>
</head>
<body>

<table width="100%" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td><img src="header_left.jpg" width="200" height="60" alt="phpBB Logo" title="phpBB Logo" border="0"/></td>
		<td width="100%" background="header_bg.jpg" height="60" align="right" nowrap="nowrap"><span class="maintitle">Available Template Files</span> &nbsp; &nbsp; &nbsp;</td>
	</tr>
</table>

<table width="95%" cellspacing="0" cellpadding="0" border="0" align="center">
	<tr>
		<td><br clear="all" />
<br>This Style Document is 100% auto-generated... no human interaction included. :D<br>
<h2>phpBB 2.2 Template</h2>
<br>
<ol>
';

sort($files_to_parse);
foreach ($files_to_parse as $file_num => $data)
{
	echo '.';
	flush();
	$var = $data['single_filename'];
	$html_data .= '<li><a href="./' . $file_to_destfile[$var] . '" class="gen">' . $var . '</a></li><br>' . "\n";
}

$html_data .= '<br><li><a href="./lang_index.html" class="gen">Appendix A: Language Variable Index</a></li><br>';

$html_data .= '
</ol><br><br>
<div class="copyright" align="center">Powered by <a href="http://www.phpbb.com/">phpBB</a>&reg; Forum Software &copy; phpBB Limited</div>

		<br clear="all" /></td>
	</tr>
</table>

</body>
</html>
';

fwrite($fp, $html_data);
fclose($fp);

// Not only write down all language files, place them into a specific array, named by the template file
// All Language vars assigned to more than one template will be placed into a common file
$entry = array();
$common_fp = fopen($store_dir . 'lang_common.php', 'w');
fwrite($common_fp, "<?php\n\n \$lang = array(\n");

$fp = fopen($store_dir . 'lang_index.html', 'w');

$html_data = '
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="subSilver.css" type="text/css">
<style type="text/css">
<!--
th		{ background-image: url(\'cellpic3.gif\') }
td.cat	{ background-image: url(\'cellpic1.gif\') }
//-->
</style>
<title>Appendix A :: Language Variable Index</title>
</head>
<body>

<table width="100%" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td><img src="header_left.jpg" width="200" height="60" alt="phpBB Logo" title="phpBB Logo" border="0"/></td>
		<td width="100%" background="header_bg.jpg" height="60" align="right" nowrap="nowrap"><span class="maintitle">Language Variable Index</span> &nbsp; &nbsp; &nbsp;</td>
	</tr>
</table>

<table width="95%" cellspacing="0" cellpadding="0" border="0" align="center">
	<tr>
		<td><br clear="all" />
<br><a href="./index.html" class="gen">Back to Contents</a><br><br>
<br>
';

echo '<br>Write Language Files';

asort($lang_references);
ksort($lang_references);
$_index = '';
$old_char = '';
foreach ($lang_references as $lang_var => $filenames)
{
	$var = preg_replace('#^L_(.*?)#', '\1', $lang_var);
	$char = $var{0};
	if ($old_char != $char)
	{
		$old_char = $char;
		$_index .= ($_index != '') ? ' :: ' : '';
		$_index .= '<a href="#' . $char . '" class="gen"><b>' . $char . '</b></a>';
	}
}

$html_data .= $_index . '<br><br><br>';
$old_char = '';
foreach ($lang_references as $lang_var => $filenames)
{
	echo '.';
	flush();
	$var = preg_replace('#^L_(.*?)#', '\1', $lang_var);
	$char = $var{0};
	if ($old_char != $char)
	{
		$old_char = $char;
		$html_data .= '<br><hr><br><a name="' . $char . '"></a><h2>Letter ' . $char . '</h2><br><br>';
	}

	$html_data .= '<b>' . $lang_var . '</b><ul>';

	if (sizeof($filenames) != 1)
	{
		fwrite($common_fp, (($entry['common']) ? ",\n" : '') . "\t'$var' => '" . $lang[$var] . "'");
		$entry['common'] = true;
	}
	else if (sizeof($filenames) == 1)
	{
		// Merge logical - hardcoded
		$fname = (preg_match('#^(' . implode('|', $merge) . ')#', $filenames[0], $match)) ? $match[0] . '.php' : str_replace($ext, 'php', $filenames[0]);
		
		if (!$lang_fp[$fname])
		{
			$lang_fp[$fname] = fopen($store_dir . 'lang_' . $fname, 'w');
			fwrite($lang_fp[$fname], "<?php\n\n\$lang = array(\n");
			$entry[$fname] = false;
		}
		fwrite($lang_fp[$fname], (($entry[$fname]) ? ",\n" : '') . "\t'$var' => '" . $lang[$var] . "'");
		$entry[$fname] = true;
	}
	
	foreach ($filenames as $f_name)
	{
		$var = trim($f_name);
		$html_data .= '<li><a href="./' . $file_to_destfile[$var] . '" class="gen">' . $var . '</a></li><br>' . "\n";
	}
	$html_data .= '</ul><br><br>';
}

fwrite($common_fp, ")\n);\n?>");
fclose($common_fp);

foreach ($lang_fp as $filepointer)
{
	fwrite($filepointer, ")\n);\n?>");
	fclose($filepointer);
}

$html_data .= '
<br><br>
<div class="copyright" align="center">Powered by <a href="http://www.phpbb.com/">phpBB</a>&reg; Forum Software &copy; phpBB Limited</div>

		<br clear="all" /></td>
	</tr>
</table>

</body>
</html>
';

fwrite($fp, $html_data);
fclose($fp);

echo '<br>Finished!';
flush();
