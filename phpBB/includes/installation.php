<?php
/***************************************************************************
 *                                 install.php
 *                            -------------------
 *   begin                : Tuesday, Sept 11, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : supportphpbb.com
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
$phpbb_root_path = '';
//
// Initialize a couple of needed variables.  It really doesn't matter what we
// put in userdata... It just has to be set so that message_die will work.
// Basically all of the following variables are to setup the template engine.
//
$userdata = "some false data"; 
$theme = array(
	'themes_id' => '2',
	'themes_name' => 'Default',
	'template_name' => 'Default',
	'td_color1' => 'CCCCCC', 
	'td_color2' => 'DDDDDD'
);
$default_language = 'english';
$default_template = 'Default';
					
$available_dbms[] = array(
	"LABEL" => "MySQL",
	"VALUE" => "mysql"
);
$available_dbms[] = array(
	"LABEL" => "MS SQL",
	"VALUE" => "mssql"
);
$available_dbms[] = array(
	"LABEL" => "Postgres",
	"VALUE" => "postgres"
);
$available_dbms[] = array(
	"LABEL" => "ODBC - MSAccess",
	"VALUE" => "odbc:access"
);
$available_dbms[] = array(
	"LABEL" => "ODBC - DB2",
	"VALUE" => "odbc:db2"
);
$available_lang[] = 'english';
?>
