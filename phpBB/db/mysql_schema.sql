# ***************************************************************************  
# *                              mysql_schema.sql 
# *                            -------------------                         
# *   begin                : Thursday, Apr 19, 2001 
# *   copyright            : (C) 2001 The phpBB Group        
# *   email                : support@phpbb.com                           
# *                                                          
# *   $Id$
# *                                                            
# * 
# *************************************************************************** 
#
#
# ***************************************************************************  
# *                                                     
# *   This program is free software; you can redistribute it and/or modify    
# *   it under the terms of the GNU General Public License as published by   
# *   the Free Software Foundation; either version 2 of the License, or  
# *   (at your option) any later version.                      
# *                                                          
# * 
# *************************************************************************** 
#
#
# Table structure for table 'phpbb_banlist'
#

CREATE TABLE phpbb_banlist (
  ban_id int(10) NOT NULL auto_increment,
  ban_userid int(10) default NULL,
  ban_ip char(8) default NULL,
  ban_start int(10) default NULL,
  ban_end int(10) default NULL,
  ban_time_type int(10) default NULL,
  PRIMARY KEY  (ban_id)
) TYPE=MyISAM;

#
# Table structure for table 'phpbb_categories'
#

CREATE TABLE phpbb_categories (
  cat_id int(10) NOT NULL auto_increment,
  cat_title varchar(100) default NULL,
  cat_order varchar(10) default NULL,
  PRIMARY KEY  (cat_id)
) TYPE=MyISAM;

#
# Table structure for table 'phpbb_config'
#

CREATE TABLE phpbb_config (
  config_id int(10) NOT NULL auto_increment,
  sitename varchar(100) default NULL,
  allow_html tinyint(3) default NULL,
  allow_bbcode tinyint(3) default NULL,
  allow_sig tinyint(3) default NULL,
  allow_namechange tinyint(3) default NULL,
  selected int(2) NOT NULL default '0',
  posts_per_page int(10) default NULL,
  hot_threshold int(10) default NULL,
  topics_per_page int(10) default NULL,
  allow_theme_create int(10) default NULL,
  override_themes tinyint(3) default NULL,
  email_sig varchar(255) default NULL,
  email_from varchar(100) default NULL,
  default_dateformat varchar(20) default NULL,
  default_lang varchar(255) default NULL,
  system_timezone smallint(6) default NULL,
  PRIMARY KEY  (config_id),
  UNIQUE KEY selected (selected)
) TYPE=MyISAM;

#
# Table structure for table 'phpbb_disallow'
#

CREATE TABLE phpbb_disallow (
  disallow_id int(10) NOT NULL auto_increment,
  disallow_username varchar(50) default NULL,
  PRIMARY KEY  (disallow_id)
) TYPE=MyISAM;

#
# Table structure for table 'phpbb_forum_access'
#

CREATE TABLE phpbb_forum_access (
  forum_id int(10) NOT NULL default '0',
  user_id int(10) NOT NULL default '0',
  can_post tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (forum_id,user_id)
) TYPE=MyISAM;

#
# Table structure for table 'phpbb_forum_mods'
#

CREATE TABLE phpbb_forum_mods (
  forum_id int(10) NOT NULL default '0',
  user_id int(10) NOT NULL default '0',
  mod_notify tinyint(3) default NULL
) TYPE=MyISAM;

#
# Table structure for table 'phpbb_forums'
#

CREATE TABLE phpbb_forums (
  forum_id int(10) NOT NULL auto_increment,
  forum_name varchar(150) default NULL,
  forum_desc text,
  forum_access tinyint(3) default NULL,
  cat_id int(10) default NULL,
  forum_order int(11) NOT NULL default '1',
  forum_type tinyint(3) default NULL,
  forum_posts int(11) NOT NULL default '0',
  forum_topics tinyint(4) NOT NULL default '0',
  forum_last_post_id int(11) NOT NULL default '0',
  PRIMARY KEY  (forum_id),
  KEY forum_id (forum_id),
  KEY forums_order (forum_order),
  KEY cat_id (cat_id)
) TYPE=MyISAM;

#
# Table structure for table 'phpbb_headermetafooter'
#

CREATE TABLE phpbb_headermetafooter (
  header text,
  meta text,
  footer text
) TYPE=MyISAM;

#
# Table structure for table 'phpbb_posts'
#

CREATE TABLE phpbb_posts (
  post_id int(10) NOT NULL auto_increment,
  topic_id int(10) NOT NULL default '0',
  forum_id int(10) NOT NULL default '0',
  poster_id int(10) NOT NULL default '0',
  post_time int(10) NOT NULL default '0',
  poster_ip char(8) NOT NULL default '0',
  bbcode_uid varchar(10) NOT NULL default '',
  PRIMARY KEY  (post_id),
  KEY forum_id (forum_id),
  KEY topic_id (topic_id),
  KEY poster_id (poster_id)
) TYPE=MyISAM;

#
# Table structure for table 'phpbb_posts_text'
#

CREATE TABLE phpbb_posts_text (
  post_id int(10) NOT NULL default '0',
  post_text text,
  PRIMARY KEY  (post_id)
) TYPE=MyISAM;

#
# Table structure for table 'phpbb_priv_msgs'
#

CREATE TABLE phpbb_priv_msgs (
  msg_id int(10) NOT NULL auto_increment,
  from_userid int(10) NOT NULL default '0',
  to_userid int(10) NOT NULL default '0',
  msg_time int(10) NOT NULL default '0',
  poster_ip char(8) NOT NULL default '0',
  msg_status int(10) NOT NULL default '0',
  msg_text text NOT NULL,
  PRIMARY KEY  (msg_id),
  KEY to_userid (to_userid)
) TYPE=MyISAM;

#
# Table structure for table 'phpbb_ranks'
#

CREATE TABLE phpbb_ranks (
  rank_id int(11) NOT NULL auto_increment,
  rank_title varchar(50) NOT NULL default '',
  rank_min int(11) NOT NULL default '0',
  rank_max int(11) NOT NULL default '0',
  rank_special tinyint(4) default '0',
  rank_image varchar(255) default NULL,
  PRIMARY KEY  (rank_id),
  KEY rank_min (rank_min),
  KEY rank_max (rank_max),
  KEY rank_id (rank_id)
) TYPE=MyISAM;

#
# Table structure for table 'phpbb_session'
#

CREATE TABLE phpbb_session (
  session_id int(10) unsigned NOT NULL default '0',
  session_user_id int(10) NOT NULL default '0',
  session_time int(10) unsigned NOT NULL default '0',
  session_ip char(8) default NULL,
  session_page int(10) NOT NULL default '0',
  session_logged_in tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (session_id)
) TYPE=MyISAM;

#
# Table structure for table 'phpbb_themes'
#

CREATE TABLE phpbb_themes (
  theme_id int(10) NOT NULL auto_increment,
  theme_name varchar(35) default NULL,
  bgcolor varchar(10) default NULL,
  textcolor varchar(10) default NULL,
  color1 varchar(10) default NULL,
  color2 varchar(10) default NULL,
  table_bgcolor varchar(10) default NULL,
  header_image varchar(50) default NULL,
  newtopic_image varchar(50) default NULL,
  reply_image varchar(50) default NULL,
  linkcolor varchar(15) default NULL,
  vlinkcolor varchar(15) default NULL,
  theme_default int(2) default '0',
  fontface varchar(100) default NULL,
  fontsize1 varchar(5) default NULL,
  fontsize2 varchar(5) default NULL,
  fontsize3 varchar(5) default NULL,
  fontsize4 varchar(5) default NULL,
  tablewidth varchar(10) default NULL,
  replylocked_image varchar(255) default NULL,
  PRIMARY KEY  (theme_id)
) TYPE=MyISAM;

#
# Table structure for table 'phpbb_topics'
#

CREATE TABLE phpbb_topics (
  topic_id int(10) NOT NULL auto_increment,
  topic_title varchar(100) NOT NULL default '',
  topic_poster int(10) NOT NULL default '0',
  topic_time int(10) NOT NULL default '0',
  topic_views int(10) NOT NULL default '0',
  topic_replies int(11) NOT NULL default '0',
  forum_id int(10) NOT NULL default '0',
  topic_status tinyint(3) NOT NULL default '0',
  topic_notify tinyint(3) default '0',
  topic_last_post_id int(11) NOT NULL default '0',
  PRIMARY KEY  (topic_id),
  KEY forum_id (forum_id),
  KEY topic_id (topic_id)
) TYPE=MyISAM;

#
# Table structure for table 'phpbb_users'
#

CREATE TABLE phpbb_users (
  user_id int(11) NOT NULL auto_increment,
  username varchar(40) NOT NULL default '',
  user_regdate varchar(20) NOT NULL default '',
  user_password varchar(32) NOT NULL default '',
  user_email varchar(255) default NULL,
  user_icq varchar(15) default NULL,
  user_website varchar(100) default NULL,
  user_occ varchar(100) default NULL,
  user_from varchar(100) default NULL,
  user_intrest varchar(150) default NULL,
  user_sig varchar(255) default NULL,
  user_viewemail tinyint(3) default NULL,
  user_theme int(11) default NULL,
  user_aim varchar(255) default NULL,
  user_yim varchar(255) default NULL,
  user_msnm varchar(255) default NULL,
  user_posts int(11) default '0',
  user_attachsig tinyint(3) default NULL,
  user_desmile tinyint(3) default NULL,
  user_html tinyint(3) default NULL,
  user_bbcode tinyint(3) default NULL,
  user_rank int(11) default '0',
  user_avatar varchar(100) default NULL,
  user_level int(11) default '1',
  user_lang varchar(255) default NULL,
  user_actkey varchar(32) default NULL,
  user_newpasswd varchar(32) default NULL,
  user_notify tinyint(3) default NULL,
  user_timezone int(4) default NULL,
  user_active int(2) default NULL,
  PRIMARY KEY  (user_id),
  KEY user_id (user_id)
) TYPE=MyISAM;

#
# Table structure for table 'phpbb_whosonline'
#

CREATE TABLE phpbb_whosonline (
  id int(3) NOT NULL auto_increment,
  ip varchar(255) default NULL,
  name varchar(255) default NULL,
  count varchar(255) default NULL,
  date varchar(255) default NULL,
  username varchar(40) default NULL,
  forum int(10) default NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

#
# Table structure for table 'phpbb_words'
#

CREATE TABLE phpbb_words (
  word_id int(10) NOT NULL auto_increment,
  word varchar(100) NOT NULL default '',
  replacement varchar(100) NOT NULL default '',
  PRIMARY KEY  (word_id)
) TYPE=MyISAM;

