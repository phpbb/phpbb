#
# phpbb - MySQL schema
#
# $Id$
#

# --------------------------------------------------------
#
# Table structure for table 'phpbb_attach_desc'
#
CREATE TABLE phpbb_attach_desc (
  attach_id mediumint(8) UNSIGNED NOT NULL auto_increment,
  attach_filename varchar(255) NOT NULL,
  download_count mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
  filename varchar(255) NOT NULL,
  comment varchar(60),
  mimetype varchar(60),
  filesize int(20) NOT NULL,
  filetime int(11) DEFAULT '0' NOT NULL,
  PRIMARY KEY (attach_id)
);

# --------------------------------------------------------
#
# Table structure for table `phpbb_auth_groups`
#
CREATE TABLE phpbb_auth_groups (
  group_id mediumint(8) unsigned NOT NULL default '0',
  forum_id mediumint(8) unsigned NOT NULL default '0',
  auth_option_id smallint(5) unsigned NOT NULL default '0',
  auth_allow_deny tinyint(4) NOT NULL default '1',
  KEY group_id (group_id),
  KEY auth_option_id (auth_option_id)
);


#
# Table structure for table `phpbb_auth_options`
#
CREATE TABLE phpbb_auth_options (
  auth_option_id tinyint(4) NOT NULL auto_increment,
  auth_value char(20) NOT NULL,
  is_global tinyint(1) DEFAULT '0' NOT NULL,
  is_local tinyint(1) DEFAULT '0' NOT NULL,
  founder_only tinyint(1) DEFAULT '0' NOT NULL,
  PRIMARY KEY (auth_option_id),
  KEY auth_value (auth_value)
);


# --------------------------------------------------------
#
# Table structure for table `phpbb_auth_users`
#
CREATE TABLE phpbb_auth_users (
  user_id mediumint(8) UNSIGNED NOT NULL default '0',
  forum_id mediumint(8) unsigned NOT NULL default '0',
  auth_option_id smallint(5) unsigned NOT NULL default '0',
  auth_allow_deny tinyint(4) NOT NULL default '1',
  KEY user_id (user_id),
  KEY auth_option_id (auth_option_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_banlist'
#
CREATE TABLE phpbb_banlist (
   ban_id mediumint(8) UNSIGNED NOT NULL auto_increment,
   ban_userid mediumint(8) UNSIGNED NOT NULL,
   ban_ip varchar(40) NOT NULL,
   ban_email varchar(50),
   ban_start int(11),
   ban_end int(11),
   ban_reason varchar(255),
   PRIMARY KEY (ban_id),
   KEY ban_ip_user_id (ban_ip, ban_userid),
   KEY ban_email (ban_email)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_config'
#
CREATE TABLE phpbb_config (
    config_name varchar(255) NOT NULL,
    config_value varchar(255) NOT NULL,
    is_dynamic tinyint(1) DEFAULT '0' NOT NULL,
    PRIMARY KEY (config_name),
    KEY is_dynamic (is_dynamic)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_disallow' <- combine with banlist
#
CREATE TABLE phpbb_disallow (
   disallow_id mediumint(8) UNSIGNED NOT NULL auto_increment,
   disallow_username varchar(30),
   PRIMARY KEY (disallow_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_forums'
#
CREATE TABLE phpbb_forums (
   forum_id smallint(5) UNSIGNED NOT NULL,
   parent_id smallint(5) UNSIGNED NOT NULL,
   left_id smallint(5) UNSIGNED NOT NULL,
   right_id smallint(5) UNSIGNED NOT NULL,
   forum_name varchar(150) NOT NULL,
   forum_desc text,
   forum_style tinyint(4) UNSIGNED,
   forum_image varchar(50),
   forum_status tinyint(4) DEFAULT '0' NOT NULL,
   forum_posts mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
   forum_topics mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
   forum_last_post_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
   forum_last_poster_id mediumint(8) DEFAULT '0' NOT NULL,
   forum_last_post_time int(11) DEFAULT '0' NOT NULL,
   forum_last_poster_name varchar(30),
   display_on_index tinyint(1) DEFAULT '1' NOT NULL,
   post_count_inc tinyint(1) DEFAULT '1' NOT NULL,
   moderated tinyint(1) DEFAULT '0' NOT NULL,
   prune_enable tinyint(1) DEFAULT '0' NOT NULL,
   prune_next int(11) UNSIGNED,
   prune_days tinyint(4) UNSIGNED NOT NULL,
   prune_freq tinyint(4) UNSIGNED DEFAULT '0' NOT NULL,
   PRIMARY KEY (forum_id),
   KEY left_id (left_id),
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
   icons_url varchar(50),
   icons_width tinyint(4) UNSIGNED NOT NULL,
   icons_height tinyint(4) UNSIGNED NOT NULL,
   PRIMARY KEY (icons_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_log_moderator'
#
CREATE TABLE phpbb_log_moderator (
  log_id mediumint(5) UNSIGNED NOT NULL DEFAULT '0' auto_increment,
  user_id mediumint(8) NOT NULL DEFAULT '0',
  forum_id mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  log_ip varchar(40) NOT NULL,
  log_time int(11) NOT NULL,
  log_operation text,
  log_data text,
  PRIMARY KEY (log_id),
  KEY forum_id (forum_id),
  KEY user_id (user_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_log_admin'
#
CREATE TABLE phpbb_log_admin (
  log_id mediumint(5) UNSIGNED NOT NULL DEFAULT '0' auto_increment,
  user_id mediumint(8) NOT NULL DEFAULT '0',
  log_ip varchar(40) NOT NULL,
  log_time int(11) NOT NULL,
  log_operation text,
  log_data text,
  PRIMARY KEY (log_id),
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
  poll_option_total mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  KEY poll_option_id (poll_option_id),
  KEY topic_id (topic_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_vote_voters'
#
CREATE TABLE phpbb_poll_voters (
  topic_id mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  poll_option_id tinyint(4) UNSIGNED NOT NULL DEFAULT '0',
  vote_user_id mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  vote_user_ip varchar(40) NOT NULL,
  KEY topic_id (topic_id),
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
   poster_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
   attach_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
   poster_ip varchar(40) NOT NULL,
   post_time int(11) DEFAULT '0' NOT NULL,
   post_approved tinyint(1) DEFAULT '1' NOT NULL,
   post_username varchar(30),
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
   bbcode_uid varchar(10) NOT NULL,
   bbcode_bitfield int(11) UNSIGNED DEFAULT '0' NOT NULL,
   post_checksum varchar(32) NOT NULL,
   post_subject varchar(60),
   post_text text,
   PRIMARY KEY (post_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_privmsgs'
#
CREATE TABLE phpbb_privmsgs (
   privmsgs_id mediumint(8) UNSIGNED NOT NULL auto_increment,
   attach_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
   privmsgs_type tinyint(4) DEFAULT '0' NOT NULL,
   privmsgs_subject varchar(60) DEFAULT '0' NOT NULL,
   privmsgs_from_userid mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
   privmsgs_to_userid mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
   privmsgs_date int(11) DEFAULT '0' NOT NULL,
   privmsgs_ip varchar(40) NOT NULL,
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
   privmsgs_bbcode_uid varchar(10) DEFAULT '0' NOT NULL,
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
  word_text varchar(50) binary NOT NULL default '',
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
   session_user_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
   session_last_visit int(11) DEFAULT '0' NOT NULL,
   session_start int(11) DEFAULT '0' NOT NULL,
   session_time int(11) DEFAULT '0' NOT NULL,
   session_ip varchar(40) DEFAULT '0' NOT NULL,
   session_browser varchar(100) DEFAULT '' NULL,
   session_page varchar(50) DEFAULT '0' NOT NULL,
   PRIMARY KEY (session_id),
   KEY session_time (session_time)
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
   smile_order tinyint(4) UNSIGNED NOT NULL,
   emoticon char(50),
   smile_on_posting tinyint(1) UNSIGNED DEFAULT '1' NOT NULL,
   PRIMARY KEY (smilies_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_styles'
#
CREATE TABLE phpbb_styles (
   style_id tinyint(4) UNSIGNED NOT NULL auto_increment,
   template_id char(50) NOT NULL,
   theme_id tinyint(4) UNSIGNED NOT NULL,
   imageset_id tinyint(4) UNSIGNED NOT NULL,
   style_name char(30) NOT NULL,
   PRIMARY KEY (style_id),
   KEY (template_id),
   KEY (theme_id),
   KEY (imageset_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_styles_template'
#
CREATE TABLE phpbb_styles_template (
   template_id tinyint(4) UNSIGNED NOT NULL auto_increment,
   template_name varchar(30) NOT NULL,
   template_path varchar(50) NOT NULL,
   poll_length smallint(5) UNSIGNED NOT NULL,
   pm_box_length smallint(5) UNSIGNED NOT NULL,
   compile_crc text,
   PRIMARY KEY (template_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_styles_theme'
#
CREATE TABLE phpbb_styles_theme (
   theme_id tinyint(4) UNSIGNED NOT NULL auto_increment,
   theme_name varchar(60),
   css_external varchar(100),
   css_data text,
   PRIMARY KEY (theme_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_styles_imageset'
#
CREATE TABLE phpbb_styles_imageset (
   imageset_id tinyint(4) UNSIGNED NOT NULL auto_increment,
   imageset_name varchar(100),
   imageset_path varchar(30),
   post_new varchar(200),
   post_locked varchar(200),
   post_pm varchar(200),
   reply_new varchar(200),
   reply_pm varchar(200),
   reply_locked varchar(200),
   icon_profile varchar(200),
   icon_pm varchar(200),
   icon_delete varchar(200),
   icon_ip varchar(200),
   icon_quote varchar(200),
   icon_search varchar(200),
   icon_edit varchar(200),
   icon_email varchar(200),
   icon_www varchar(200),
   icon_icq varchar(200),
   icon_aim varchar(200),
   icon_yim varchar(200),
   icon_msnm varchar(200),
   icon_no_email varchar(200) DEFAULT '',
   icon_no_www varchar(200) DEFAULT '',
   icon_no_icq varchar(200) DEFAULT '',
   icon_no_aim varchar(200) DEFAULT '',
   icon_no_yim varchar(200) DEFAULT '',
   icon_no_msnm varchar(200) DEFAULT '',
   goto_post varchar(200),
   goto_post_new varchar(200),
   goto_post_latest varchar(200),
   goto_post_newest varchar(200),
   forum varchar(200),
   forum_new varchar(200),
   forum_locked varchar(200),
   sub_forum varchar(200),
   sub_forum_new varchar(200),
   folder varchar(200),
   folder_new varchar(200),
   folder_hot varchar(200),
   folder_hot_new varchar(200),
   folder_locked varchar(200),
   folder_locked_new varchar(200),
   folder_sticky varchar(200),
   folder_sticky_new varchar(200),
   folder_announce varchar(200),
   folder_announce_new varchar(200),
   topic_watch varchar(200),
   topic_unwatch varchar(200),
   poll_left varchar(200),
   poll_center varchar(200),
   poll_right varchar(200),
   rating varchar(200),
   PRIMARY KEY (imageset_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_topics'
#
CREATE TABLE phpbb_topics (
   topic_id mediumint(8) UNSIGNED NOT NULL auto_increment,
   forum_id smallint(8) UNSIGNED DEFAULT '0' NOT NULL,
   topic_approved tinyint(1) UNSIGNED DEFAULT '1' NOT NULL,
   topic_title varchar(60) NOT NULL,
   topic_poster mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
   topic_time int(11) DEFAULT '0' NOT NULL,
   topic_icon tinyint(4) UNSIGNED DEFAULT '1' NOT NULL,
   topic_rating tinyint(4) DEFAULT '0' NOT NULL,
   topic_views mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
   topic_replies mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
   topic_status tinyint(3) DEFAULT '0' NOT NULL,
   topic_type tinyint(3) DEFAULT '0' NOT NULL,
   topic_first_post_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
   topic_first_poster_name varchar(30),
   topic_last_post_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
   topic_last_poster_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
   topic_last_poster_name varchar(30),
   topic_last_post_time int(11) DEFAULT '0' NOT NULL,
   topic_moved_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
   poll_title varchar(255) NOT NULL,
   poll_start int(11) NOT NULL DEFAULT '0',
   poll_length int(11) NOT NULL DEFAULT '0',
   poll_last_vote int(11),
   PRIMARY KEY (topic_id),
   KEY forum_id (forum_id),
   KEY topic_moved_id (topic_moved_id),
   KEY topic_last_post_time (topic_last_post_time),
   KEY topic_type (topic_type)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_topics_rating'
#
CREATE TABLE phpbb_topics_rating (
  topic_id mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  user_id tinyint(4) UNSIGNED UNSIGNED NOT NULL DEFAULT '0',
  rating tinyint(4) NOT NULL,
  KEY topic_id (topic_id),
  KEY user_id (user_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_topics_watch'
#
CREATE TABLE phpbb_topics_watch (
  topic_id mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  user_id mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
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
   user_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
   user_pending tinyint(1),
   KEY group_id (group_id),
   KEY user_id (user_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_users'
#
CREATE TABLE phpbb_users (
   user_id mediumint(8) UNSIGNED NOT NULL auto_increment,
   user_active tinyint(1) DEFAULT '1',
   user_founder tinyint(1) DEFAULT '0' NOT NULL,
   user_permissions text NULL,
   user_ip varchar(40),
   user_regdate int(11) DEFAULT '0' NOT NULL,
   username varchar(30) NOT NULL,
   user_password varchar(32) NOT NULL,
   user_email varchar(60),
   user_session_time int(11) DEFAULT '0' NOT NULL,
   user_session_page smallint(5) DEFAULT '0' NOT NULL,
   user_lastvisit int(11) DEFAULT '0' NOT NULL,
   user_startpage varchar(100) DEFAULT '',
   user_colour varchar(6) DEFAULT '' NOT NULL,
   user_posts mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
   user_lang varchar(50),
   user_timezone decimal(4,2) DEFAULT '0' NOT NULL,
   user_dst tinyint(1) DEFAULT '0' NOT NULL,
   user_dateformat varchar(15) DEFAULT 'd M Y H:i' NOT NULL,
   user_style tinyint(4),
   user_rank int(11) DEFAULT '0',
   user_new_privmsg smallint(5) UNSIGNED DEFAULT '0' NOT NULL,
   user_unread_privmsg smallint(5) UNSIGNED DEFAULT '0' NOT NULL,
   user_last_privmsg int(11) DEFAULT '0' NOT NULL,
   user_emailtime int(11),
   user_sortby_type varchar(1) DEFAULT 'l' NOT NULL,
   user_sortby_dir varchar(1) DEFAULT 'd' NOT NULL,
   user_show_days tinyint(1) DEFAULT '0' NOT NULL,
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
   user_allow_email tinyint(1) DEFAULT '1' NOT NULL,
   user_allow_viewonline tinyint(1) DEFAULT '1' NOT NULL,
   user_notify tinyint(1) DEFAULT '1' NOT NULL,
   user_notify_pm tinyint(1) DEFAULT '1' NOT NULL,
   user_popup_pm tinyint(1) DEFAULT '0' NOT NULL,
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
   user_occ varchar(100),
   user_interests varchar(255),
   user_actkey varchar(32),
   user_newpasswd varchar(32),
   PRIMARY KEY (user_id)
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
