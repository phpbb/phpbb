<?php 
/*************************************************************************** 
*                               dbinformer.php 
*                            ------------------- 
*   begin                : Saturday, May 05, 2002 
*   copyright            : (C) 2002 The phpBB Group 
*   email                : n/a 
* 
*   $Id: dbinformer.php,v 1.1 2010/10/10 15:05:22 orynider Exp $ 
* 
*   Coded by AL, Techie-Micheal, Blade, and Black Fluffy Lion. 
*   http://www.phpbb.com/phpBB/groupcp.php?g=7330 
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

/* magic quotes, borrowed from install.php */ 
set_magic_quotes_runtime(0); 
if (!get_magic_quotes_gpc()) 
{ 
    if (is_array($_POST)) 
    { 
        while (list($k, $v) = each($_POST)) 
        { 
            if (is_array($_POST[$k])) 
            { 
                while (list($k2, $v2) = each($_POST[$k])) 
                { 
                    $_POST[$k][$k2] = addslashes($v2); 
                } 
                @reset($_POST[$k]); 
            } 
            else 
            { 
                $_POST[$k] = addslashes($v); 
            } 
        } 
        @reset($_POST); 
    } 
} 

$all_connected = false; 
$error = false; 
$error_msg = ''; 
$select = false; 
$connect = false; 

function make_config($dbms, $dbhost, $dbname, $dbuser, $dbpasswd, $table_prefix) 
{ 
    $config_file = '&lt;?php<br />' . "\n"; 
    $config_file .= '<br />' . "\n"; 
    $config_file .= '//<br />' . "\n"; 
    $config_file .= '// phpBB 2.x auto-generated config file<br />' . "\n"; 
    $config_file .= '// Do not change anything in this file!<br />' . "\n"; 
    $config_file .= '//<br />' . "\n"; 
    $config_file .= '<br />' . "\n"; 
    $config_file .= '$dbms = \'' . $dbms . '\';<br /><br />' . "\n\n"; 
    $config_file .= '$dbhost = \'' . $dbhost . '\';<br />' . "\n"; 
    $config_file .= '$dbname = \'' . $dbname . '\';<br />' . "\n"; 
    $config_file .= '$dbuser = \'' . $dbuser . '\';<br />' . "\n"; 
    $config_file .= '$dbpasswd = \'' . $dbpasswd . '\';<br /><br />' . "\n\n"; 
    $config_file .= '$table_prefix = \'' . $table_prefix . '\';<br /><br />' . "\n\n"; 
    $config_file .= 'define(\'PHPBB_INSTALLED\', true);<br /><br />' . "\n\n"; 
    $config_file .= '?>'; 

    return $config_file; 
} 

function make_download($dbms, $dbhost, $dbname, $dbuser, $dbpasswd, $table_prefix)  
{  
    $config_file = '<?php' . "\n\n";  
    $config_file .= '//' . "\n"; 
    $config_file .= '// phpBB 2.x auto-generated config file' . "\n";  
    $config_file .= '// Do not change anything in this file!' . "\n";  
    $config_file .= '//' . "\n\n";  
    $config_file .= '$dbms = \'' . $dbms . '\';' . "\n\n";  
    $config_file .= '$dbhost = \'' . $dbhost . '\';' . "\n";  
    $config_file .= '$dbname = \'' . $dbname . '\';' . "\n";  
    $config_file .= '$dbuser = \'' . $dbuser . '\';' . "\n";  
    $config_file .= '$dbpasswd = \'' . $dbpasswd . '\';' . "\n\n";  
    $config_file .= '$table_prefix = \'' . $table_prefix . '\';' . "\n\n"; 
    $config_file .= 'define(\'PHPBB_INSTALLED\', true);' . "\n\n"; 
    $config_file .= '?>'; 
     
    return $config_file; 
} 

/* make all the vars safe to display in form inputs and on the user's screen. Borrowed from usercp_register.php */ 
$check_var_list = array('dbms' => 'dbms', 'dbhost' => 'dbhost', 'dbname' => 'dbname', 'dbuser' => 'dbuser', 'dbpasswd' => 'dbpasswd', 'table_prefix' => 'table_prefix'); 

while (list($var, $param) = each($check_var_list)) 
{ 
    if (!empty($_POST[$param])) 
    { 
        $$var = stripslashes(htmlspecialchars(strip_tags($_POST[$param]))); 
    } 
} 

$available_dbms = array( 
    'mysql' => 'MySQL 3.x', 
    'mysql4' => 'MySQL 4.x', 
    'postgres' => 'PostgreSQL 7.x', 
    'mssql' => 'MS SQL Server 7/2000', 
    'msaccess' => 'MS Access [ ODBC ]', 
    'mssql-odbc' => 'MS SQL Server [ OBDC ]', 
); 

if (isset($_POST['download_config']) && $_POST['download_config'] == true && isset($_POST['submit_download_config']) && $_POST['submit_download_config'] == 'Download') 
{ 
    /* borrowed from install.php */ 
    header('Content-Type: text/x-delimtext; name="config.php"'); 
    header('Content-disposition: attachment; filename=config.php'); 
    echo make_download($dbms, $dbhost, $dbname, $dbuser, $dbpasswd, $table_prefix); 
    return; 
} 
?> 

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"> 
<html> 
<head> 
<meta http-equiv="Content-Type" content="text/html" /> 
<meta http-equiv="Content-Style-Type" content="text/css" /> 
<title>phpBB :: dbinformer.php</title> 
<link rel="stylesheet" href="../templates/subSilver/subSilver.css" type="text/css" /> 
<style type="text/css"> 
<!-- 
p,ul,td {font-size:10pt;} 
h3 {font-size:12pt;color:blue} 
//--> 
</style> 
</head> 
<body> 
<table width="100%" border="0" cellspacing="0" cellpadding="10" align="center"> 
<tr> 
<td class="bodyline"><table width="100%" border="0" cellspacing="0" cellpadding="0"> 
<tr> 
<td> 
<table width="100%" border="0" cellspacing="0" cellpadding="0"> 
<tr> 
<td><img src="../templates/subSilver/images/logo_phpBB.gif" border="0" alt="phpBB2 : Creating Communities" vspace="1" /></a></td> 
<td align="center" width="100%" valign="middle"><span class="maintitle">dbinformer.php</span> 
</td> 
</tr> 
</table> 

<br /><b><div align="center"> 
<a href="#what">What you entered</a> | 
<a href="#connect">Connection to database</a> | 
<a href="#tables">Tables in database</a> | 
<a href="#config">Config file</a> 
</b></div> 

<table width="100%" border="0" cellspacing="0" cellpadding="10" align="center"> 
<tr> 
<td align="center" width="100%" valign="middle"><span class="maintitle"></span></td> 
</tr> 
<tr> 
<td width="100%"> 
<table width="100%" cellpadding="2" cellspacing="1" border="0" class="forumline"> 
<tr> 
<th colspan="2">Database Configuration</th> 
</tr> 
<tr> 
<td class="row1" align="right"><span class="gen">Database type: </span></td> 
<td class="row2"> 
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post"> 
<select name="dbms"> 
<?php 
/* loop through the dbms, with the correct one selected (hopefully!) */ 
while (list($var, $param) = each($available_dbms)) 
{ 
    $selected = ($dbms == $var) ? ' selected="selected"' : ''; 
    echo '<option value="' . $var . '"' . $selected . '>' . $param . '</option>'; 
} 
?> 
</select></td> 
</tr> 
<tr> 
<td class="row1" align="right"><span class="gen">Database Server Hostname / DSN: </span></td> 
<td class="row2"><input type="text" name="dbhost" value="<?php echo @$dbhost; ?>" /></td> 
</tr> 
<tr> 
<td class="row1" align="right"><span class="gen">Your Database Name: </span></td> 
<td class="row2"><input type="text" name="dbname" value="<?php echo @$dbname; ?>" /></td> 
</tr> 
<tr> 
<td class="row1" align="right"><span class="gen">Database Username: </span></td> 
<td class="row2"><input type="text" name="dbuser" value="<?php echo @$dbuser; ?>" /></td> 
</tr> 
<tr> 
<td class="row1" align="right"><span class="gen">Database Password: </span></td> 
<td class="row2"><input type="password" name="dbpasswd" value="<?php echo @$dbpasswd; ?>" /></td> 
</tr> 
<tr> 
<td class="row1" align="right"><span class="gen">Chosen Prefix: </span></td> 
<td class="row2"><input type="text" name="table_prefix" value="<?php echo @$table_prefix; ?>" /></td> 
</tr> 
<tr> 
<td class="row1" align="right"><span class="gen">Generate a config file: </span></td> 
</td> 
<td class="row2"><input type="checkbox" name="generate_config" value="true" <?php $checked = (isset($_POST['generate_config']) && $_POST['generate_config'] == true) ? 'checked="checked"' : ''; echo $checked; ?> /></td> 
</tr> 
<tr> 
<td class="catbottom" align="center" colspan="2"> 
<input class="mainoption" type="submit" name="submit" value="Submit" /></td> 
</tr> 
</form></td> 
</tr> 
</table> 
<?php 
if (!isset($_POST['submit'])) 
{ 
    echo '<br />Please enter your data.<br />'; 
} 
else 
{ 
    /* dbal added by Techie-Micheal [and then obliterated by BFL]. weeeeeee! */ 
    switch ($dbms) 
    { 
        case 'mysql': 
            if (function_exists(@mysql_connect)) 
            { 
                $db = array( 
                    'choice' => 'MySQL 3.x', 
                    'connect' => @mysql_connect($dbhost, $dbuser, $dbpasswd), 
                    'select' => @mysql_select_db($dbname), 
                    'error' => @mysql_error(), 
                    'list' => @mysql_list_tables($dbname), 
                    'fetch' => @mysql_fetch_row, 
                    'close' => @mysql_close() 
                ); 
            } 
            else 
            { 
                $error = true; 
                $error_msg = 'You do not have the needed functions available for ' . $available_dbms[$dbms] . '.'; 
            } 
        break; 

        case 'mysql4': 
            if (function_exists(@mysql_connect)) 
            { 
                $db = array( 
                    'choice' => 'MySQL 4.x', 
                    'connect' => @mysql_connect($dbhost, $dbuser, $dbpasswd), 
                    'select' => @mysql_select_db($dbname), 
                    'error' => @mysql_error(), 
                    'list' => @mysql_list_tables($dbname), 
                    'fetch' => @mysql_fetch_row, 
                    'close' => @mysql_close() 
                ); 
            } 
            else 
            { 
                $error = true; 
                $error_msg = 'You do not have the needed functions available for ' . $available_dbms[$dbms] . '.'; 
            } 
        break; 
             
        case 'msaccess': 
            if (function_exists(@odbc_connect)) 
            { 
                $db = array( 
                    'choice' => 'MS Access [ ODBC ]', 
                    'connect' => @odbc_connect($dbhost, $dbuser, $dbpasswd), 
                    'select' => 'na', 
                    'error' => @odbc_errormsg(), 
                    'list' => 'na', /* odbc_tables() */ 
                    'fetch' => 'na', /* odbc_fetch_row(), odbc_result_all() */ 
                    'close' => @odbc_close() 
                ); 
            } 
            else 
            { 
                $error = true; 
                $error_msg = 'You do not have the needed functions available for ' . $available_dbms[$dbms] . '.'; 
            } 
        break; 
              
        case 'postgres': 
            if (function_exists(@pg_connect)) 
            { 
                $db = array( 
                    'choice' => 'PostgreSQL 7.x', 
                    'connect' => @pg_connect('host=' . $dbhost . ' user=' . $dbuser . ' dbname=' . $dbname . ' password=' . $dbpasswd), 
                    'select' => 'na', 
                    'error' => @pg_last_error(), 
                    'list' => @pg_exec("SELECT relname FROM pg_class WHERE relkind = 'r' AND relname NOT LIKE 'pg\_%'"), /* provided by SuGa */ 
                    'fetch' => @pg_fetch_row, 
                    'close' => @pg_close() 
                ); 
            } 
            else 
            { 
                $error = true; 
                $error_msg = 'You do not have the needed functions available for ' . $available_dbms[$dbms] . '.'; 
            } 
        break;                 
        case 'mssql': 
            if (function_exists(@mssql_connect)) 
            { 
                $db = array( 
                    'choice' => 'MS SQL Server 7/2000', 
                    'connect' => @mssql_connect($dbhost, $dbuser, $dbpasswd), 
                    'select' => @mssql_select_db($dbname), 
                    'error' => @mssql_get_last_message(), 
                    'list' => 'na', 
                    'fetch' => 'na', /* mssql_fetch_row() */ 
                    'close' => @mssql_close() 
                ); 
            } 
            else 
            { 
                $error = true; 
                $error_msg = 'You do not have the needed functions available for ' . $available_dbms[$dbms] . '.'; 
            } 
        break;  

        case 'mssql-odbc': 
            if (function_exists(@odbc_connect)) 
            { 
                $db = array( 
                    'choice' => 'MS SQL Server [ ODBC ]', 
                    'connect' => @odbc_connect($dbhost, $dbuser, $dbpasswd), 
                    'select' => 'na', 
                    'error' => @odbc_errormsg(), 
                    'list' => 'na', /* odbc_tables() */ 
                    'fetch' => 'na', /* odbc_fetch_row(), odbc_result_all() */ 
                    'close' => @odbc_close() 
                ); 
            } 
            else 
            { 
                $error = true; 
                $error_msg = 'You do not have the needed functions available for ' . $available_dbms[$dbms] . '.'; 
            } 
        break;  

        default: 
            $error = true; 
            $error_msg = 'Unrecognised DBMS.'; 
        break; 
    } 
     
    if ($error == true && $error_msg != '') 
    { 
        echo '<br /><b>ERROR:</b> ' . $error_msg . '<br />'; 
    } 
    else 
    { 
        echo '<a name="what"><h3><u>What you entered</u></h3></a>'; 
        echo 'Database Type: <b>' . $db['choice']  . '</b><br />'; 
        echo 'Database Server Hostname / DSN: <b>' . $dbhost . '</b><br />'; 
        echo 'Your Database Name: <b>' . $dbname . '</b><br />'; 
        echo 'Database Username: <b>' . $dbuser .   '</b><br />'; 
        echo 'Database Password: <b>' . $dbpasswd   . '</b><br />'; 

        echo '<a name="connect"><h3><u>Connection to database</u></h3></a>'; 
         
        if (!$db['connect']) 
        { 
            echo 'You have not established a connection to <b>' . $db['choice'] . '</b>.<br />'; 
            echo '<b>ERROR:</b> <i>' . $db['error'] . '</i><br /><br />'; 
        } 
        else 
        { 
            echo 'You have established a connection to <b>' . $db['choice'] . '</b>.<br /><br />'; 
            $connect = true; 
        } 

        if ($dbms == 'msaccess' || $dbms == 'postgres' || $dbms == 'mssql-odbc')  
        {         
            /* for dbmses which have no db select function */ 
            $select = true; 
        } 
        else 
        { 
            if (!$db['select']) 
            { 
                echo 'Your database was not found.<br />'; 
                echo '<b>ERROR:</b> <i>' . htmlspecialchars($db['error']) . '</i><br />'; 
            } 
            else 
            { 
                echo 'Your database was found.<br />'; 
                $select = true; 
            } 
        } 

        if ($connect == true && $select == true) 
        { 
            echo '<a name="tables"><h3><u>Tables in database</u></h3></a>'; 
            if ($dbms == 'mysql' || $dbms == 'mysql4' || $dbms == 'postgres') 
            { 
                echo '<i>Tables with the table prefix you specified are in bold.</i>'; 
                echo '<ul>'; 
                while ($table = $db['fetch']($db['list'])) 
                {    
                    /* Highlight tables with the table_prefix specified */ 
                    if (preg_match("/^$_POST[table_prefix]/i", $table[0])) 
                    { 
                        echo '<li><b>' . htmlspecialchars($table[0]) . '</b></li><br />'; 
                    } 
                    else 
                    { 
                        echo '<li>' . htmlspecialchars($table[0]) . '</li><br />'; 
                    } 
                } 
                echo '</ul>'; 
            } 
            else 
            { 
                echo 'Sorry, this feature isn\'t available with ' . $db['choice'] . '.'; 
            } 

            /* defined a var which is only there if successfully connected to the database and the database is found */ 
            $all_connected = true; 
        } 

        /* Create a config file if checked and if the connection went OK */ 
        if (isset($_POST['generate_config']) && $_POST['generate_config'] == true) 
        { 
            echo '<a name="config"><h3><u>Config file</u></h3></a>'; 
            if ($all_connected != true) 
            { 
                echo 'The database has not been successfully connected to so no config file has been generated.<br />'; 
            } 
            else 
            { 
                echo 'Either copy the <b>19</b> lines below and save them as <u>config.php</u> or click on the <u>Download</u> button below. Then upload the file to your phpBB2 root directory (phpBB2/ by default). Make sure that there is nothing (this includes blank spaces) after the <u>?></u>.<br /><br />'; 

                /* Create our config file */ 
                echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '" method="post"><table cellspacing="1" cellpadding="3" border="0"><tr><td class="code">'; 
                echo make_config($dbms, $dbhost, $dbname, $dbuser, $dbpasswd, $table_prefix); 
                echo '</td></tr></table>'; 
                echo '<input type="hidden" name="dbms" value="' . $dbms . '" />'; 
                echo '<input type="hidden" name="dbhost" value="' . $dbhost . '" />'; 
                echo '<input type="hidden" name="dbname" value="' . $dbname . '" />'; 
                echo '<input type="hidden" name="dbuser" value="' . $dbuser . '" />'; 
                echo '<input type="hidden" name="dbpasswd" value="' . $dbpasswd . '" />'; 
                echo '<input type="hidden" name="table_prefix" value="' . $table_prefix . '" />'; 
                echo '<input type="hidden" name="download_config" value="true" />'; 
                echo '<br /><input type="submit" name="submit_download_config" value="Download" class="mainoption" /><br />'; 
            } 
        } 

        /* close the connection */ 
        if ($all_connected == true) 
        { 
            $db['close']; 
        } 
    } 
} 

/* And they all lived happily ever after... 
The End */ 
?> 

<br /><a href="javascript:scrollTo('0','0');"><b>Return to top</b></a> 
</td> 
</tr> 
</table> 
<div align="center"><span class="copyright">&copy; Copyright 2002 The <a href="http://www.phpbb.com/about.php" target="_phpbb" class="copyright">phpBB Group</a></span></div> 
</td> 
</tr> 
</table> 
</body> 
</html> 