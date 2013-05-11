<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : compile_template.php
// STARTED   : Sun Apr 24, 2011
// COPYRIGHT : © 2011 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

define('IN_PHPBB', 1);
define('ANONYMOUS', 1);
$phpEx = substr(strrchr(__FILE__, '.'), 1);
$phpbb_root_path = './../';

include($phpbb_root_path . 'includes/template_compile.'.$phpEx);

$file = $argv[1];

$compile = new phpbb_template_compile(false);
echo $compile->compile_file($file);
