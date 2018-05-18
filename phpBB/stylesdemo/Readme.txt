#################################################################
## MOD Title: Styles Demo and Download
## Version: 1.0.3
## MOD Author: CRLin - http://web.dhjh.tcc.edu.tw/~gzqbyr/
## MOD Description:
##	Allow user to change their preferred style.
##
## Usage and Demo:
##	 http://web.dhjh.tcc.edu.tw/~gzqbyr/phpBB/styles/stylesdemo/index.php
##	 http://web.dhjh.tcc.edu.tw/~gzqbyr/phpBB/styles/demo.php?mystyle=2
##	 http://web.dhjh.tcc.edu.tw/~gzqbyr/phpBB/styles/demo.php?mystyle=progray
##	 http://web.dhjh.tcc.edu.tw/~gzqbyr/phpBB/styles/index.php?mystyle=1
##	 http://web.dhjh.tcc.edu.tw/~gzqbyr/phpBB/styles/index.php?mystyle=K_Kitty
##
## Installation Level: Easy
## Installation Time: 	3 Minutes
##
## Files To Edit:
##	includes/session.php
##	stylesdemo/fileinfo.js
##
#################################################################
##
## MOD History:
##
## 2007-10-23  - Version 1.0.3
##	- add download mod
## 2007-10-23  - Version 1.0.2
##	- Fixed for RC7
## 2007-09-05  - Version 1.0.1
##
## 2007-07-23  - Version 1.0.0
##	- Initial Release
##
#################################################################
#
#-----[ COPY ]------------------------------------------
#
copy directory stylesdemo to your forum root directory
#
#-----[ OPEN ]---------------------
#
includes/session.php

# 
#-----[ FIND ]----------------------
#
			$style = ($style) ? $style : ((!$config['override_user_style']) ? $this->data['user_style'] : $config['default_style']);
		}

# 
#-----[ AFTER, ADD ]----------------
#

		// BEGIN Styles_Demo MOD
		$style_value = '';
		if (isset($_GET['mystyle']))
		{
			$style_value = $_GET['mystyle'];
			if (intval($style_value) == 0)
			{
				$sql = 'SELECT style_id, style_name
						FROM ' . STYLES_TABLE . "
					WHERE style_active = 1 AND style_name = '$style_value'";
				if(($result = $db->sql_query($sql)) && ($row = $db->sql_fetchrow($result)))
				{
					$style_value = $row['style_id'];
				}
				else
				{
					die('Could not find style name '. $style_value . '!');
				}
			}
			else
			{
				$sql = 'SELECT style_id
						FROM ' . STYLES_TABLE . "
						WHERE style_active = 1 AND style_id = $style_value";
				if(!(($result = $db->sql_query($sql)) && ($row = $db->sql_fetchrow($result))))
				{
					die ('style_id ' . $style_value . ' not found');
				}
			}
			$this->set_cookie('change_style', $style_value, time() + 31536000);
		}
		elseif (isset($_COOKIE[$config['cookie_name'] . '_change_style']))
		{
			$style_value = $_COOKIE[$config['cookie_name'] . '_change_style'];
		}
		if (!Empty($style_value))
		{
			$style = $style_value;
		}
		// END Styles_Demo MOD

# 
#-----[ SAVE/CLOSE ALL FILES ]------ 
#
# EoM
#
# REM
# You have to edit fileinfo.js to fit your