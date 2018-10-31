<?
/*
 * getDownNow - Directory Browser
 * Copyright (C) 2001 Ray Lopez (http://www.TheDreaming.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

/*
*********************** CONFIGURE BELOW THIS LINE ***********************
*/
error_reporting  (E_ERROR | E_WARNING | E_PARSE);
/*
**	CSS (Style Sheet) Definition Begginning
*/

$scriptLocation = ""; // Point this to a refernce to a style sheet (Embeeded CSS will be ingnorned)

$scriptCSS   = "
<style>
BODY {
  margin: 1em;
  font-family: Arial;
  line-height: 1.1;
  background: #3f3f3f;
  color: #999999;
}

A:link { color: #cccccc;}          /* unvisited link */
A:visited { color: #cccccc;}        /* visited links */
A:active { color: #555555; background: #999999;}        /* active links */
A:hover { color: #cccccc; background: #555555;}

TD { background: #555555 }

INPUT.SUBMIT  { background: #999999; }
</style>
";
/*
**      CSS (Style Sheet) Definition Finished
*/


/*
**	Display Disk Space Usagae and Free
*/
$scriptStats = "1"; // 1 for Yes - 0 for No

define('IN_PHPBB', true);
define('IN_PORTAL', true);

$mx_root_path = './../../../../';
 $phpEx = substr(strrchr(__FILE__, '.'), 1);
 include($mx_root_path . 'common.'.$phpEx);

//
// Start session management
//
/* - orig
$userdata = session_pagestart($user_ip, PAGE_REGISTER);
init_userprefs($userdata);
*/
//MX
  $mx_user->init($user_ip, PAGE_INDEX);


if ((!$userdata['session_logged_in']) && ($userdata['user_level'] != ADMIN))
{
	mx_redirect(mx_append_sid("index.$phpEx", true));
}

/*
*********************** DO NOT MODIFY BELOW THIS LINE ***********************
*/
function dirHeader() {
        $content  = "<table width=100% nowrap>";
        return $content;
}

function dirTable() {
        $content = "<tr><td><b><font size=-1>Type</font></b></td><td width=50%><b><font size=-1>Name</font></b></td><td><b><font size=-1>Size</font></b></td><td><b><font size=-1>Modified</font></b></td></tr>";
        return $content;
}


function dirFooter() {
        $content  = "</table>";
        return $content;
}

function fType($file) {
	$varFileType = filetype($file);
	if($varFileType != "dir") {
		$curdir = getcwd();
		$pInfo = pathinfo("$curdir/$file");
		$varFileType = $pInfo["extension"];
	}
	return $varFileType;
}


function fileView($file) {
	$varType = strtolower(fType($file));
	$varJSSettings = "width=300,height=300,resizable=1,scrollbars=1,menubar=0,status=0,titlebar=0,toolbar=0,hotkeys=0,locationbar=0";
	$txtArray[] = "txt";
	$txtArray[] = "nfo";
	$txtArray[] = "diz";
	$txtArray[] = "now";
	$txtArray[] = "bmp";
	$txtArray[] = "jpg";
	$txtArray[] = "gif";
	$txtArray[] = "doc";
	$txtArray[] = "1st";
	$txtArray[] = "now";
	$txtArray[] = "me";
	if(in_array($varType, $txtArray)) {
		$content = " - (<a href=\"#\" onClick=\"window.open('$file', 'viewer','$varJSSettings');\">view</a>)";
	}
	return $content;
}
function display_size($file_size){
    if($file_size >= 1073741824) {
        $file_size = round($file_size / 1073741824 * 100) / 100 . "g";
    } elseif($file_size >= 1048576) {
        $file_size = round($file_size / 1048576 * 100) / 100 . "m";
    } elseif($file_size >= 1024) {
        $file_size = round($file_size / 1024 * 100) / 100 . "k";
    } else {
        $file_size = $file_size . "b";
    }
    return $file_size;
}

function dirGather() {
        $handle=opendir(".");
	$content = "";
        //while (false!=($file = readdir($handle))) {
	 while ($file = readdir($handle)) {
                if(($file != "index.txt") && ($file != "index.php") && ($file != "index.html") && ($file != "index.htm") && ($file != ".htaccess")) {
			$filetype = fType($file);
			if($filetype == "dir") {
				$dirtext[] = "$file";
			} else {
				$context[] = "$file";
			}
                }
        }
if($dirtext) {
        sort($dirtext);
        for($i=0; $i<count($dirtext); $i++) {
                $file = $dirtext[$i];
                        $lastchanged = filectime($file);
                        $changeddate = date("d-m-Y H:i:s", $lastchanged);
                        $filesize = display_size(filesize($file));
                        $filetype = fType($file);
                        $viewfile = fileView($file);
                        $content .= "<tr><td><font size=-1>$filetype</font></td>";
                        $content .= "<td><font size=-1><a href=\"$file\">$file</a> $viewfile</font></td>";
                        $content .= "<td><font size=-1>$filesize</font></td>";
                        $content .= "<td><font size=-1>$changeddate</font></td></tr>";
        }
}
if($context) {
	sort($context);
	for($i=0; $i<count($context); $i++) {
		$file = $context[$i];
                        $lastchanged = filectime($file);
                        $changeddate = date("d-m-Y H:i:s", $lastchanged);
                        $filesize = display_size(filesize($file));
                        $filetype = fType($file);
                        $viewfile = fileView($file);
                        $content .= "<tr><td><font size=-1>$filetype</font></td>";
                        $content .= "<td><font size=-1><a href=\"$file\">$file</a> $viewfile</font></td>";
                        $content .= "<td><font size=-1>$filesize</font></td>";
                        $content .= "<td><font size=-1>$changeddate</font></td></tr>";
	}
}
	return $content;
}

function diskStats($scriptStats) {
	if($scriptStats) {
//		$diskTotal = display_size(disk_total_space("/"));
        	$diskFree  = display_size(diskfreespace("/"));
		$content  = "<table width=100%>";
		$content .= "<tr><td width=150><b><font size=-1>Free Disk Space:</font></b></td><td><font size=-1>$diskFree</font></td></tr>";
//		$content .= "<tr><td width=150><b><font size=-1>Total Disk Space:</font></b></td><td><font size=-1>$diskFree</font></td></tr>";
		$content .= "</table>";
		print($content);
	}
}
?>
<html>
	<head>

	<? if($scriptLocation == "") {
		print($scriptCSS);
	} else {
		print("<LINK REL=stylesheet HREF=\"$scriptLocation\" TYPE=\"text/css\">");
	} ?>
	</head>
<body>
<?

	diskStats($scriptStats);
	print(dirHeader());
	print(dirTable());
	print(dirGather());
	print(dirTable());
	print(dirFooter());
	diskStats($scriptStats);
	@include("index.txt");
	print("<table width=100%><tr><td><fonr size=-1>Directory listing generated by: <a href=\"http://www.TheDreaming.com\">getDownNow</a> ver. 0.9.5</font></td></tr></table>");
?>
</body>
</html>
