#
# phpBB2 - MySQL schema
#
# $Id$
#

# --------------------------------------------------------
#
# Table structure for table `phpbb_auth_groups`
#
DROP TABLE IF EXISTS phpbb_auth_groups;
CREATE TABLE phpbb_auth_groups (
  group_id mediumint(8) unsigned NOT NULL default '0',
  forum_id mediumint(8) unsigned NOT NULL default '0',
  auth_option_id smallint(5) unsigned NOT NULL default '0',
  auth_allow_deny tinyint(4) NOT NULL default '1'
);


#
# Table structure for table `phpbb_auth_options`
#
CREATE TABLE phpbb_auth_options (
  auth_option_id tinyint(4) NOT NULL auto_increment,
  auth_option char(20) NOT NULL default '',
  PRIMARY KEY  (auth_option_id,auth_option)
);


# --------------------------------------------------------
#
# Table structure for table `phpbb_auth_prefetch`
#
CREATE TABLE phpbb_auth_prefetch (
  user_id mediumint(8) unsigned NOT NULL default '0',
  forum_id mediumint(8) unsigned NOT NULL default '0',
  auth_option_id smallint(5) unsigned NOT NULL default '0',
  auth_allow_deny tinyint(4) NOT NULL default '1')
);


# --------------------------------------------------------
#
# Table structure for table `phpbb_auth_users`
#
CREATE TABLE phpbb_auth_users (
  user_id mediumint(8) unsigned NOT NULL default '0',
  forum_id mediumint(8) unsigned NOT NULL default '0',
  auth_option_id smallint(5) unsigned NOT NULL default '0',
  auth_allow_deny tinyint(4) NOT NULL default '1'
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_banlist'
#
CREATE TABLE phpbb_banlist (
   ban_id mediumint(8) UNSIGNED NOT NULL auto_increment, 
   ban_userid mediumint(8) NOT NULL, 
   ban_ip char(40) NOT NULL, 
   ban_email char(50), 
   PRIMARY KEY (ban_id), 
   KEY ban_ip_user_id (ban_ip, ban_userid)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_categories' <- DUMP THIS?
#
CREATE TABLE phpbb_categories (
   cat_id mediumint(8) UNSIGNED NOT NULL auto_increment,
   cat_title char(60),
   cat_order mediumint(8) UNSIGNED NOT NULL,
   PRIMARY KEY (cat_id), 
   KEY cat_order (cat_order)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_config'
#
CREATE TABLE phpbb_config ( 
    config_name varchar(255) NOT NULL, 
    config_value varchar(255) NOT NULL, 
    PRIMARY KEY (config_name)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_disallow' <- combine with banlist
#
CREATE TABLE phpbb_disallow (
   disallow_id mediumint(8) UNSIGNED NOT NULL auto_increment,
   disallow_username char(30),
   PRIMARY KEY (disallow_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_forums'
#
CREATE TABLE phpbb_forums (
   forum_id smallint(5) UNSIGNED NOT NULL,
   parent_id smallint(5) UNSIGNED NOT NULL, 
   forum_order smallint(5) UNSIGNED DEFAULT '1' NOT NULL, 

   left_id smallint(5) UNSIGNED NOT NULL, 
   right_id smallint(5) UNSIGNED NOT NULL, 

   forum_name varchar(150) NOT NULL, 
   forum_desc text, 
   forum_image varchar(50), 
   forum_status tinyint(4) DEFAULT '0' NOT NULL, 
   forum_posts mediumint(8) UNSIGNED DEFAULT '0' NOT NULL, 
   forum_topics mediumint(8) UNSIGNED DEFAULT '0' NOT NULL, 
   forum_last_post_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL, 
   post_count_inc tinyint(1) DEFAULT '1' NOT NULL, 
   prune_next int(11) UNSIGNED, 
   prune_days tinyint(4) UNSIGNED NOT NULL, 
   prune_freq tinyint(4) UNSIGNED DEFAULT '0' NOT NULL, 
   default_style tinyint(4) UNSIGNED, 
   default_group mediumint(8) UNSIGNED, 
   default_user mediumint(8) UNSIGNED, 
   PRIMARY KEY (forum_id), 
   KEY forums_order (forum_order), 
   KEY forum_last_post_id (forum_last_post_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_forums_watch'
#
CREATE TABLE phpbb_forums_watch (
  forum_id smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  user_id mediumint(8) NOT NULL DEFAULT '0',
  notify_status tinyint(1) NOT NULL default '0',
  KEY forum_id (forum_id),
  KEY user_id (user_id), 
  KEY notify_status (notify_status)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_groups'
#
CREATE TABLE phpbb_groups (
   group_id mediumint(8) NOT NULL auto_increment,
   group_type tinyint(4) DEFAULT '1' NOT NULL, 
   group_name varchar(40) NOT NULL,
   group_avatar varchar(100), 
   group_avatar_type tinyint(4), 
   group_colour varchar(6) DEFAULT '' NOT NULL, 
   group_description varchar(255) NOT NULL,
   PRIMARY KEY (group_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_groups_moderator'
#
CREATE TABLE phpbb_groups_moderator (
   group_id mediumint(8) NOT NULL, 
   user_id mediumint(8) NOT NULL 
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_icons'
#
CREATE TABLE phpbb_icons (
   icons_id tinyint(4) UNSIGNED NOT NULL auto_increment,
   icons_url char(50), 
   icons_width tinyint(4) UNSIGNED NOT NULL, 
   icons_height tinyint(4) UNSIGNED NOT NULL, 
   PRIMARY KEY (icons_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_log_moderator'
#
CREATE TABLE phpbb_log_moderator (
  log_id mediumint(5) UNSIGNED NOT NULL DEFAULT '0',
  user_id mediumint(8) NOT NULL DEFAULT '0', 
  forum_id mediumint(8) UNSIGNED NOT NULL DEFAULT '0', 
  log_ip varchar(40) NOT NULL,
  log_time int(11) NOT NULL, 
  log_operation varchar(255), 
  PRIMARY KEY (log_id), 
  KEY forum_id (forum_id),
  KEY user_id (user_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_vote_results'
#
CREATE TABLE phpbb_poll_results (
  poll_option_id tinyint(4) UNSIGNED NOT NULL DEFAULT '0', 
  topic_id mediumint(8) UNSIGNED NOT NULL, 
  poll_option_text varchar(255) NOT NULL, 
  KEY poll_option_id (poll_option_id), 
  KEY topic_id (topic_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_vote_voters'
#
CREATE TABLE phpbb_poll_voters (
  vote_id mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  poll_option_id tinyint(4) UNSIGNED NOT NULL DEFAULT '0', 
  vote_user_id mediumint(8) NOT NULL DEFAULT '0',
  vote_user_ip char(40) NOT NULL,
  KEY vote_id (vote_id),
  KEY vote_user_id (vote_user_id),
  KEY vote_user_ip (vote_user_ip)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_posts'
#
CREATE TABLE phpbb_posts (
   post_id mediumint(8) UNSIGNED NOT NULL auto_increment,
   topic_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
   forum_id smallint(5) UNSIGNED DEFAULT '0' NOT NULL,
   poster_id mediumint(8) DEFAULT '0' NOT NULL,
   poster_ip char(40) NOT NULL, 
   post_time int(11) DEFAULT '0' NOT NULL,
   post_approved tinyint(1) DEFAULT '1' NOT NULL, 
   post_username char(30), 
   enable_bbcode tinyint(1) DEFAULT '1' NOT NULL,
   enable_html tinyint(1) DEFAULT '0' NOT NULL,
   enable_smilies tinyint(1) DEFAULT '1' NOT NULL,
   enable_magic_url tinyint(1) DEFAULT '1' NOT NULL, 
   enable_sig tinyint(1) DEFAULT '1' NOT NULL, 
   post_edit_time int(11), 
   post_edit_count smallint(5) UNSIGNED DEFAULT '0' NOT NULL, 
   PRIMARY KEY (post_id),
   KEY forum_id (forum_id),
   KEY topic_id (topic_id),
   KEY poster_id (poster_id), 
   KEY post_time (post_time)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_posts_text'
#
CREATE TABLE phpbb_posts_text (
   post_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
   bbcode_uid char(10) NOT NULL,
   post_subject char(60),
   post_text text,
   PRIMARY KEY (post_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_privmsgs'
#
CREATE TABLE phpbb_privmsgs (
   privmsgs_id mediumint(8) UNSIGNED NOT NULL auto_increment,
   privmsgs_type tinyint(4) DEFAULT '0' NOT NULL,
   privmsgs_subject char(60) DEFAULT '0' NOT NULL,
   privmsgs_from_userid mediumint(8) DEFAULT '0' NOT NULL,
   privmsgs_to_userid mediumint(8) DEFAULT '0' NOT NULL,
   privmsgs_date int(11) DEFAULT '0' NOT NULL,
   privmsgs_ip char(40) NOT NULL,
   privmsgs_enable_bbcode tinyint(1) DEFAULT '1' NOT NULL,
   privmsgs_enable_html tinyint(1) DEFAULT '0' NOT NULL,
   privmsgs_enable_smilies tinyint(1) DEFAULT '1' NOT NULL, 
   privmsgs_attach_sig tinyint(1) DEFAULT '1' NOT NULL, 
   PRIMARY KEY (privmsgs_id),
   KEY privmsgs_from_userid (privmsgs_from_userid),
   KEY privmsgs_to_userid (privmsgs_to_userid)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_privmsgs_text'
#
CREATE TABLE phpbb_privmsgs_text (
   privmsgs_text_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
   privmsgs_bbcode_uid char(10) DEFAULT '0' NOT NULL, 
   privmsgs_text text,
   PRIMARY KEY (privmsgs_text_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_ranks'
#
CREATE TABLE phpbb_ranks (
   rank_id smallint(5) UNSIGNED NOT NULL auto_increment,
   rank_title varchar(50) NOT NULL,
   rank_min mediumint(8) DEFAULT '0' NOT NULL,
   rank_special tinyint(1) DEFAULT '0',
   rank_image varchar(100), 
   rank_colour varchar(6), 
   PRIMARY KEY (rank_id) 
);


# --------------------------------------------------------
#
# Table structure for table `phpbb_search_results`
#
CREATE TABLE phpbb_search_results (
  search_id int(11) UNSIGNED NOT NULL default '0',
  session_id varchar(32) NOT NULL default '',
  search_array text NOT NULL,
  PRIMARY KEY  (search_id),
  KEY session_id (session_id)
);


# --------------------------------------------------------
#
# Table structure for table `phpbb_search_wordlist`
#
CREATE TABLE phpbb_search_wordlist (
  word_text varchar(25) binary NOT NULL default '',
  word_id mediumint(8) UNSIGNED NOT NULL auto_increment,
  word_common tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY (word_text), 
  KEY word_id (word_id)
);


# --------------------------------------------------------
#
# Table structure for table `phpbb_search_wordmatch`
#
CREATE TABLE phpbb_search_wordmatch (
  post_id mediumint(8) UNSIGNED NOT NULL default '0',
  word_id mediumint(8) UNSIGNED NOT NULL default '0',
  title_match tinyint(1) NOT NULL default '0',
  KEY word_id (word_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_sessions'
#
CREATE TABLE phpbb_sessions (
   session_id varchar(32) DEFAULT '' NOT NULL, 
   session_user_id mediumint(8) DEFAULT '0' NOT NULL, 
   session_start int(11) DEFAULT '0' NOT NULL, 
   session_time int(11) DEFAULT '0' NOT NULL, 
   session_ip varchar(40) DEFAULT '0' NOT NULL, 
   session_browser varchar(100) DEFAULT '' NULL, 
   session_page varchar(50) DEFAULT '0' NOT NULL, 
   PRIMARY KEY (session_id), 
   KEY session_user_id (session_user_id), 
   KEY session_id_user_id (session_id, session_user_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_smilies'
#
CREATE TABLE phpbb_smilies (
   smilies_id tinyint(4) UNSIGNED NOT NULL auto_increment,
   code char(10), 
   smile_url char(50), 
   smile_width tinyint(4) UNSIGNED NOT NULL, 
   smile_height tinyint(4) UNSIGNED NOT NULL, 
   emoticon char(50), 
   PRIMARY KEY (smilies_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_styles'
#
CREATE TABLE phpbb_styles (
   style_id tinyint(4) UNSIGNED NOT NULL auto_increment, 
   theme_id tinyint(4) UNSIGNED NOT NULL, 
   template_name char(50) NOT NULL, 
   style_name char(30) NOT NULL, 
   style_default tinyint(1) DEFAULT '1' NOT NULL, 
   PRIMARY KEY (style_id), 
   KEY (theme_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_styles_css'
#
CREATE TABLE phpbb_styles_css (
	theme_id  mediumint(8) UNSIGNED NOT NULL auto_increment, 
	css_data text NOT NULL, 
	css_extra_data text NOT NULL, 
	KEY (theme_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_topics'
#
CREATE TABLE phpbb_topics (
   topic_id mediumint(8) UNSIGNED NOT NULL auto_increment,
   forum_id smallint(8) UNSIGNED DEFAULT '0' NOT NULL,
   topic_title varchar(60) NOT NULL,
   topic_poster mediumint(8) DEFAULT '0' NOT NULL,
   topic_time int(11) DEFAULT '0' NOT NULL,
   topic_icon tinyint(4) UNSIGNED DEFAULT '0' NOT NULL, 
   topic_views mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
   topic_replies mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
   topic_status tinyint(3) DEFAULT '0' NOT NULL, 
   topic_type tinyint(3) DEFAULT '0' NOT NULL, 
   topic_first_post_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL, 
   topic_last_post_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL, 
   topic_moved_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL, 
   post_count_inc tinyint(1) DEFAULT '1' NOT NULL, 
   poll_title varchar(255) NOT NULL, 
   poll_start int(11) NOT NULL DEFAULT '0', 
   poll_length int(11) NOT NULL DEFAULT '0', 
   poll_last_vote int(11), 
   PRIMARY KEY (topic_id), 
   KEY forum_id (forum_id), 
   KEY topic_moved_id (topic_moved_id), 
   KEY topic_status (topic_status), 
   KEY topic_type (topic_type) 
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_topics_watch'
#
CREATE TABLE phpbb_topics_watch (
  topic_id mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  user_id mediumint(8) NOT NULL DEFAULT '0',
  notify_status tinyint(1) NOT NULL default '0',
  KEY topic_id (topic_id),
  KEY user_id (user_id), 
  KEY notify_status (notify_status)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_user_group'
#
CREATE TABLE phpbb_user_group (
   group_id mediumint(8) DEFAULT '0' NOT NULL,
   user_id mediumint(8) DEFAULT '0' NOT NULL,
   user_pending tinyint(1), 
   KEY group_id (group_id),
   KEY user_id (user_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_users'
#
CREATE TABLE phpbb_users (
   user_id mediumint(8) NOT NULL auto_increment,
   user_active tinyint(1) DEFAULT '1',
   username varchar(30) NOT NULL,
   user_password varchar(32) NOT NULL,
   user_email varchar(60), 
   user_session_time int(11) DEFAULT '0' NOT NULL, 
   user_session_page smallint(5) DEFAULT '0' NOT NULL, 
   user_lastvisit int(11) DEFAULT '0' NOT NULL, 
   user_regdate int(11) DEFAULT '0' NOT NULL, 
   user_level tinyint(4) DEFAULT '0', 
   user_colourise varchar(6) DEFAULT '' NOT NULL, 
   user_posts mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
   user_lang varchar(50), 
   user_timezone decimal(4,2) DEFAULT '0' NOT NULL, 
   user_dateformat varchar(15) DEFAULT 'd M Y H:i' NOT NULL,
   user_style tinyint(4), 
   user_new_privmsg smallint(5) UNSIGNED DEFAULT '0' NOT NULL, 
   user_unread_privmsg smallint(5) UNSIGNED DEFAULT '0' NOT NULL, 
   user_last_privmsg int(11) DEFAULT '0' NOT NULL, 
   user_emailtime int(11), 
   user_viewemail tinyint(1) DEFAULT '1' NOT NULL, 
   user_viewsigs tinyint(1) DEFAULT '1' NOT NULL, 
   user_viewavatars tinyint(1) DEFAULT '1' NOT NULL, 
   user_viewimg tinyint(1) DEFAULT '1' NOT NULL, 
   user_attachsig tinyint(1), 
   user_allowhtml tinyint(1) DEFAULT '1', 
   user_allowbbcode tinyint(1) DEFAULT '1', 
   user_allowsmile tinyint(1) DEFAULT '1', 
   user_allowavatar tinyint(1) DEFAULT '1' NOT NULL, 
   user_allow_pm tinyint(1) DEFAULT '1' NOT NULL, 
   user_allow_viewonline tinyint(1) DEFAULT '1' NOT NULL, 
   user_allow_email tinyint(1) DEFAULT '1' NOT NULL, 
   user_notify tinyint(1) DEFAULT '1' NOT NULL,
   user_notify_pm tinyint(1) DEFAULT '1' NOT NULL, 
   user_popup_pm tinyint(1) DEFAULT '0' NOT NULL, 
   user_rank int(11) DEFAULT '0',
   user_avatar char(100),
   user_avatar_type tinyint(4) DEFAULT '0' NOT NULL, 
   user_avatar_width tinyint(4) UNSIGNED, 
   user_avatar_height tinyint(4) UNSIGNED, 
   user_sig text,
   user_sig_bbcode_uid varchar(10),
   user_from varchar(100), 
   user_icq varchar(15), 
   user_aim varchar(255), 
   user_yim varchar(255), 
   user_msnm varchar(255), 
   user_website varchar(100), 
   user_actkey varchar(32), 
   user_newpasswd varchar(32), 

   user_occ varchar(100),
   user_interests varchar(255), 

   PRIMARY KEY (user_id), 
   KEY user_session_time (user_session_time)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_words'
#
CREATE TABLE phpbb_words (
   word_id mediumint(8) UNSIGNED NOT NULL auto_increment,
   word char(100) NOT NULL,
   replacement char(100) NOT NULL,
   PRIMARY KEY (word_id)
);
