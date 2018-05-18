<?php

/***************************************************************************
 *                               admin_xs.php
 *                               ------------
 *   copyright            : (C) 2003 - 2007 Vjacheslav Trushkin
 *   support              : http://www.stsoftware.biz/forum
 *
 *   version              : 2.4.1
 *
 *   file revision        : 80
 *   project revision     : 83
 *   last modified        : 12 Mar 2007  10:28:52
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

if (!defined('IN_PHPBB'))
{
    die('Hacking attempt');
}

if(empty($setmodules))
{
	return;
}

define('IN_XS', true);
define('XS_ADMIN_OVERRIDE', true);
include_once('xs_include.' . $phpEx);
return;

?>