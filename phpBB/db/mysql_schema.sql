#
# phpBB2 - MySQL schema
#
# $Id$
#

#
# Table structure for table 'phpbb_auth_access'
#
DROP TABLE IF EXISTS phpbb_auth_access;
CREATE TABLE phpbb_auth_access (
   group_id int(11) DEFAULT '0' NOT NULL,
   forum_id int(11) DEFAULT '0' NOT NULL,
   auth_view tinyint(1) DEFAULT '0' NOT NULL,
   auth_read tinyint(1) DEFAULT '0' NOT NULL,
   auth_post tinyint(1) DEFAULT '0' NOT NULL,
   auth_reply tinyint(1) DEFAULT '0' NOT NULL,
   auth_edit tinyint(1) DEFAULT '0' NOT NULL,
   auth_delete tinyint(1) DEFAULT '0' NOT NULL,
   auth_sticky tinyint(1) DEFAULT '0' NOT NULL,
   auth_announce tinyint(1) DEFAULT '0' NOT NULL,
   auth_vote tinyint(1) DEFAULT '0' NOT NULL,
   auth_pollcreate tinyint(1) DEFAULT '0' NOT NULL,
   auth_attachments tinyint(1) DEFAULT '0' NOT NULL,
   auth_mod tinyint(1) DEFAULT '0' NOT NULL, 
   KEY group_id (group_id),
   KEY forum_id (forum_id)
);


#
# Table structure for table 'phpbb_user_group'
#
DROP TABLE IF EXISTS phpbb_user_group;
CREATE TABLE phpbb_user_group (
   group_id int(11) DEFAULT '0' NOT NULL,
   user_id int(11) DEFAULT '0' NOT NULL,
   user_pending tinyint(1), 
   KEY group_id (group_id),
   KEY user_id (user_id)
);

#
# Table structure for table 'phpbb_groups'
#
DROP TABLE IF EXISTS phpbb_groups;
CREATE TABLE phpbb_groups (
   group_id int(11) NOT NULL auto_increment,
   group_type tinyint(4) DEFAULT '1' NOT NULL, 
   group_name varchar(40) NOT NULL,
   group_description varchar(255) NOT NULL,
   group_moderator int(11) DEFAULT '0' NOT NULL,
   group_single_user tinyint(1) DEFAULT '0' NOT NULL,
   PRIMARY KEY (group_id), 
   KEY group_single_user (group_single_user)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_banlist'
#
DROP TABLE IF EXISTS phpbb_banlist;
CREATE TABLE phpbb_banlist (
   ban_id int(11) NOT NULL auto_increment,
   ban_userid int(11) NOT NULL,
   ban_ip char(8) NOT NULL,
   ban_email varchar(255),
   PRIMARY KEY (ban_id), 
   KEY ban_ip_user_id (ban_ip, ban_userid)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_categories'
#
DROP TABLE IF EXISTS phpbb_categories;
CREATE TABLE phpbb_categories (
   cat_id int(11) NOT NULL auto_increment,
   cat_title varchar(100),
   cat_order int(11) NOT NULL,
   PRIMARY KEY (cat_id), 
   KEY cat_order (cat_order)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_config'
#
DROP TABLE IF EXISTS phpbb_config;
CREATE TABLE phpbb_config (
   config_id int(11) NOT NULL auto_increment,
   board_disable tinyint(1) DEFAULT '0' NOT NULL, 
   board_startdate int(11), 
   sitename varchar(100),
   cookie_name char(20),
   cookie_path char(25),
   cookie_domain char(50), 
   cookie_secure tinyint(1), 
   session_length int(11), 
   allow_html tinyint(1),
   allow_html_tags char(255) DEFAULT 'b,u,i,pre,font color' NOT NULL, 
   allow_bbcode tinyint(1),
   allow_smilies tinyint(1),
   allow_sig tinyint(1),
   allow_namechange tinyint(1),
   allow_theme_create tinyint(1),
   allow_avatar_local tinyint(1) DEFAULT '0' NOT NULL, 
   allow_avatar_remote tinyint(1) DEFAULT '0' NOT NULL, 
   allow_avatar_upload tinyint(1) DEFAULT '0' NOT NULL,
   override_themes tinyint(3),
   posts_per_page int(11),
   topics_per_page int(11),
   hot_threshold int(11),
   max_poll_options int(11), 
   email_sig varchar(255),
   email_from varchar(100), 
   smtp_delivery tinyint(1) DEFAULT '0' NOT NULL, 
   smtp_host varchar(50), 
   require_activation tinyint(1) DEFAULT '0' NOT NULL, 
   flood_interval int(4) NOT NULL,
   avatar_filesize int(11) DEFAULT '6144' NOT NULL,
   avatar_max_width smallint(6) DEFAULT '70' NOT NULL, 
   avatar_max_height smallint(6) DEFAULT '70' NOT NULL, 
   avatar_path varchar(255) DEFAULT 'images/avatars' NOT NULL,
   smilies_path char(100) DEFAULT 'images/smiles' NOT NULL, 
   default_theme int(11) DEFAULT '1' NOT NULL,
   default_lang varchar(255),
   default_dateformat varchar(14) DEFAULT 'd M Y H:i' NOT NULL,
   system_timezone int(11) DEFAULT '0' NOT NULL,
   sys_template varchar(100) DEFAULT 'Default' NOT NULL,
   prune_enable tinyint(1) DEFAULT '1' NOT NULL, 
   gzip_compress tinyint(1) DEFAULT '0' NOT NULL, 
   PRIMARY KEY (config_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_disallow'
#
DROP TABLE IF EXISTS phpbb_disallow;
CREATE TABLE phpbb_disallow (
   disallow_id int(11) NOT NULL auto_increment,
   disallow_username varchar(25),
   PRIMARY KEY (disallow_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_forum_prune'
#
DROP TABLE IF EXISTS phpbb_forum_prune;
CREATE TABLE phpbb_forum_prune (
   prune_id int(11) NOT NULL auto_increment,
   forum_id int(11) NOT NULL,
   prune_days int(3) NOT NULL,
   prune_freq int(3) NOT NULL,
   PRIMARY KEY(prune_id),
   KEY forum_id (forum_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_forums'
#
DROP TABLE IF EXISTS phpbb_forums;
CREATE TABLE phpbb_forums (
   forum_id int(11) NOT NULL auto_increment,
   cat_id int(11) NOT NULL,
   forum_name varchar(150),
   forum_desc text,
   forum_status tinyint(4) DEFAULT '0' NOT NULL, 
   forum_order int(11) DEFAULT '1' NOT NULL,
   forum_posts int(11) DEFAULT '0' NOT NULL,
   forum_topics int(11) DEFAULT '0' NOT NULL,
   forum_last_post_id int(11) DEFAULT '0' NOT NULL,
   prune_next int(11),
   prune_enable tinyint(1) DEFAULT '1' NOT NULL,
   auth_view tinyint(2) DEFAULT '0' NOT NULL,
   auth_read tinyint(2) DEFAULT '0' NOT NULL,
   auth_post tinyint(2) DEFAULT '0' NOT NULL,
   auth_reply tinyint(2) DEFAULT '0' NOT NULL,
   auth_edit tinyint(2) DEFAULT '0' NOT NULL,
   auth_delete tinyint(2) DEFAULT '0' NOT NULL,
   auth_sticky tinyint(2) DEFAULT '0' NOT NULL,
   auth_announce tinyint(2) DEFAULT '0' NOT NULL,
   auth_vote tinyint(2) DEFAULT '0' NOT NULL,
   auth_pollcreate tinyint(2) DEFAULT '0' NOT NULL,
   auth_attachments tinyint(2) DEFAULT '0' NOT NULL,
   PRIMARY KEY (forum_id),
   KEY forum_id (forum_id),
   KEY forums_order (forum_order),
   KEY cat_id (cat_id), 
   KEY forum_last_post_id (forum_last_post_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_posts'
#
DROP TABLE IF EXISTS phpbb_posts;
CREATE TABLE phpbb_posts (
   post_id int(11) NOT NULL auto_increment,
   topic_id int(11) DEFAULT '0' NOT NULL,
   forum_id int(11) DEFAULT '0' NOT NULL,
   poster_id int(11) DEFAULT '0' NOT NULL,
   post_time int(11) DEFAULT '0' NOT NULL,
   poster_ip char(8) NOT NULL, 
   post_username varchar(30), 
   enable_bbcode tinyint(1) DEFAULT '1' NOT NULL,
   enable_html tinyint(1) DEFAULT '0' NOT NULL,
   enable_smilies tinyint(1) DEFAULT '1' NOT NULL,
   enable_sig tinyint(1) DEFAULT '1' NOT NULL, 
   bbcode_uid char(10) NOT NULL,
   post_edit_time int(11),
   post_edit_count smallint(6) DEFAULT '0' NOT NULL,
   PRIMARY KEY (post_id),
   KEY forum_id (forum_id),
   KEY topic_id (topic_id),
   KEY poster_id (poster_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_posts_text'
#
DROP TABLE IF EXISTS phpbb_posts_text;
CREATE TABLE phpbb_posts_text (
   post_id int(11) DEFAULT '0' NOT NULL,
   post_subject varchar(255),
   post_text text,
   PRIMARY KEY (post_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_privmsgs'
#
DROP TABLE IF EXISTS phpbb_privmsgs;
CREATE TABLE phpbb_privmsgs (
   privmsgs_id int(11) NOT NULL auto_increment,
   privmsgs_type tinyint(4) DEFAULT '0' NOT NULL,
   privmsgs_subject varchar(255) DEFAULT '0' NOT NULL,
   privmsgs_from_userid int(11) DEFAULT '0' NOT NULL,
   privmsgs_to_userid int(11) DEFAULT '0' NOT NULL,
   privmsgs_date int(11) DEFAULT '0' NOT NULL,
   privmsgs_ip char(8) NOT NULL,
   privmsgs_enable_bbcode tinyint(1) DEFAULT '1' NOT NULL,
   privmsgs_enable_html tinyint(1) DEFAULT '0' NOT NULL,
   privmsgs_enable_smilies tinyint(1) DEFAULT '1' NOT NULL, 
   privmsgs_enable_sig tinyint(1) DEFAULT '1' NOT NULL, 
   privmsgs_bbcode_uid char(10) DEFAULT '0' NOT NULL, 
   PRIMARY KEY (privmsgs_id),
   KEY privmsgs_from_userid (privmsgs_from_userid),
   KEY privmsgs_to_userid (privmsgs_to_userid)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_privmsgs_text'
#
DROP TABLE IF EXISTS phpbb_privmsgs_text;
CREATE TABLE phpbb_privmsgs_text (
   privmsgs_text_id int(11) DEFAULT '0' NOT NULL,
   privmsgs_text text,
   PRIMARY KEY (privmsgs_text_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_ranks'
#
DROP TABLE IF EXISTS phpbb_ranks;
CREATE TABLE phpbb_ranks (
   rank_id int(11) NOT NULL auto_increment,
   rank_title varchar(50) NOT NULL,
   rank_min int(11) DEFAULT '0' NOT NULL,
   rank_max int(11) DEFAULT '0' NOT NULL,
   rank_special tinyint(1) DEFAULT '0',
   rank_image varchar(255),
   PRIMARY KEY (rank_id) 
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_session'
#
# Note that if you're running 3.23.x you may want to make
# this table a type HEAP. This type of table is stored
# within system memory and therefore for big busy boards
# is likely to be noticeably faster than continually
# writing to disk ... 
#
# I must admit I read about this type on vB's board.
# Hey, I never said you cannot get basic ideas from
# competing boards, just that I find it's best not to
# look at any code ... !
#
DROP TABLE IF EXISTS phpbb_session;
CREATE TABLE phpbb_session (
   session_id char(32) DEFAULT '' NOT NULL,
   session_user_id int(11) DEFAULT '0' NOT NULL,
   session_start int(11) DEFAULT '0' NOT NULL,
   session_time int(11) DEFAULT '0' NOT NULL,
   session_last_visit int(11) DEFAULT '0' NOT NULL,
   session_ip char(8) DEFAULT '0' NOT NULL,
   session_page int(11) DEFAULT '0' NOT NULL,
   session_logged_in tinyint(1) DEFAULT '0' NOT NULL,
   PRIMARY KEY (session_id),
   KEY session_user_id (session_user_id),
   KEY session_id_ip_user_id (session_id, session_ip, session_user_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_smilies'
#
DROP TABLE IF EXISTS phpbb_smilies;
CREATE TABLE phpbb_smilies (
   smilies_id int(11) NOT NULL auto_increment,
   code varchar(50),
   smile_url varchar(100),
   emoticon varchar(75),
   PRIMARY KEY (smilies_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_themes'
#
DROP TABLE IF EXISTS phpbb_themes;
CREATE TABLE phpbb_themes (
   themes_id int(11) NOT NULL auto_increment,
   themes_name varchar(30) NOT NULL default '',
   head_stylesheet varchar(100) default NULL,
   body_background varchar(100) default NULL,
   body_bgcolor varchar(6) default NULL,
   body_text varchar(6) default NULL,
   body_link varchar(6) default NULL,
   body_vlink varchar(6) default NULL,
   body_alink varchar(6) default NULL,
   body_hlink varchar(6) default NULL,
   tr_color1 varchar(6) default NULL,
   tr_color2 varchar(6) default NULL,
   tr_color3 varchar(6) default NULL,
   tr_class1 varchar(25) default NULL,
   tr_class2 varchar(25) default NULL,
   tr_class3 varchar(25) default NULL,
   th_color1 varchar(6) default NULL,
   th_color2 varchar(6) default NULL,
   th_color3 varchar(6) default NULL,
   th_class1 varchar(25) default NULL,
   th_class2 varchar(25) default NULL,
   th_class3 varchar(25) default NULL,
   td_color1 varchar(6) default NULL,
   td_color2 varchar(6) default NULL,
   td_color3 varchar(6) default NULL,
   td_class1 varchar(25) default NULL,
   td_class2 varchar(25) default NULL,
   td_class3 varchar(25) default NULL,
   fontface1 varchar(50) default NULL,
   fontface2 varchar(50) default NULL,
   fontface3 varchar(50) default NULL,
   fontsize1 tinyint(4) default NULL,
   fontsize2 tinyint(4) default NULL,
   fontsize3 tinyint(4) default NULL,
   fontcolor1 varchar(6) default NULL,
   fontcolor2 varchar(6) default NULL,
   fontcolor3 varchar(6) default NULL,
   span_class1 varchar(25) default NULL,
   span_class2 varchar(25) default NULL,
   span_class3 varchar(25) default NULL,
   PRIMARY KEY  (themes_id),
   KEY themes_name (themes_name)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_themes_name'
#
DROP TABLE IF EXISTS phpbb_themes_name;
CREATE TABLE phpbb_themes_name (
   themes_id int(11) DEFAULT '0' NOT NULL,
   tr_color1_name varchar(50),
   tr_color2_name varchar(50),
   tr_color3_name varchar(50),
   tr_class1_name varchar(50),
   tr_class2_name varchar(50),
   tr_class3_name varchar(50),
   th_color1_name varchar(50),
   th_color2_name varchar(50),
   th_color3_name varchar(50),
   th_class1_name varchar(50),
   th_class2_name varchar(50),
   th_class3_name varchar(50),
   td_color1_name varchar(50),
   td_color2_name varchar(50),
   td_color3_name varchar(50),
   td_class1_name varchar(50),
   td_class2_name varchar(50),
   td_class3_name varchar(50),
   fontface1_name varchar(50),
   fontface2_name varchar(50),
   fontface3_name varchar(50),
   fontsize1_name varchar(50),
   fontsize2_name varchar(50),
   fontsize3_name varchar(50),
   fontcolor1_name varchar(50),
   fontcolor2_name varchar(50),
   fontcolor3_name varchar(50),
   span_class1_name varchar(50),
   span_class2_name varchar(50),
   span_class3_name varchar(50),
   PRIMARY KEY (themes_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_topics'
#
DROP TABLE IF EXISTS phpbb_topics;
CREATE TABLE phpbb_topics (
   topic_id int(11) NOT NULL auto_increment,
   forum_id int(11) DEFAULT '0' NOT NULL,
   topic_title varchar(100) NOT NULL,
   topic_poster int(11) DEFAULT '0' NOT NULL,
   topic_time int(11) DEFAULT '0' NOT NULL,
   topic_views int(11) DEFAULT '0' NOT NULL,
   topic_replies int(11) DEFAULT '0' NOT NULL,
   topic_status tinyint(3) DEFAULT '0' NOT NULL,
   topic_vote tinyint(1) DEFAULT '0' NOT NULL,
   topic_type tinyint(3) DEFAULT '0' NOT NULL,
   topic_last_post_id int(11) DEFAULT '0' NOT NULL,
   topic_moved_id int(11),
   PRIMARY KEY (topic_id),
   KEY forum_id (forum_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_topics_watch'
#
DROP TABLE IF EXISTS phpbb_topics_watch;
CREATE TABLE phpbb_topics_watch (
  topic_id int(11) NOT NULL DEFAULT '0',
  user_id int(11) NOT NULL DEFAULT '0',
  notify_status tinyint(1) NOT NULL default '0',
  KEY topic_id (topic_id),
  KEY user_id (user_id), 
  KEY notify_status (notify_status)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_users'
#
DROP TABLE IF EXISTS phpbb_users;
CREATE TABLE phpbb_users (
   user_id int(11) NOT NULL auto_increment,
   user_active tinyint(4),
   username varchar(25) NOT NULL,
   user_password varchar(32) NOT NULL,
   user_autologin_key varchar(32),
   user_level tinyint(4) DEFAULT '0',
   user_posts int(11) DEFAULT '0' NOT NULL,
   user_timezone int(11) DEFAULT '0' NOT NULL,
   user_dateformat varchar(14) DEFAULT 'd M Y H:i' NOT NULL,
   user_template varchar(50),
   user_theme int(11),
   user_lang varchar(255),
   user_viewemail tinyint(1),
   user_attachsig tinyint(1),
   user_allowhtml tinyint(1),
   user_allowbbcode tinyint(1),
   user_allowsmile tinyint(1), 
   user_allowavatar tinyint(1) DEFAULT '1' NOT NULL, 
   user_allow_pm tinyint(1) DEFAULT '1' NOT NULL, 
   user_allow_viewonline tinyint(1) DEFAULT '1' NOT NULL, 
   user_notify tinyint(1) DEFAULT '1' NOT NULL,
   user_notify_pm tinyint(1) DEFAULT '1' NOT NULL, 
   user_regdate int(11) DEFAULT '0' NOT NULL,
   user_rank int(11) DEFAULT '0',
   user_avatar varchar(100),
   user_email varchar(255),
   user_icq varchar(15),
   user_website varchar(100),
   user_from varchar(100),
   user_sig varchar(255),
   user_aim varchar(255),
   user_yim varchar(255),
   user_msnm varchar(255),
   user_occ varchar(100),
   user_interests varchar(255),
   user_actkey varchar(32),
   user_newpasswd varchar(32),
   PRIMARY KEY (user_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_vote_desc'
#
DROP TABLE IF EXISTS phpbb_vote_desc;
CREATE TABLE phpbb_vote_desc (
  vote_id int(11) NOT NULL auto_increment,
  topic_id int(11) NOT NULL DEFAULT '0',
  vote_text text NOT NULL,
  vote_start int(11) NOT NULL DEFAULT '0',
  vote_length int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY  (vote_id),
  KEY topic_id (topic_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_vote_results'
#
DROP TABLE IF EXISTS phpbb_vote_results;
CREATE TABLE phpbb_vote_results (
  vote_id int(11) NOT NULL DEFAULT '0',
  vote_option_id int(11) NOT NULL DEFAULT '0',
  vote_option_text varchar(255) NOT NULL,
  vote_result int(11) NOT NULL DEFAULT '0',
  KEY vote_option_id (vote_option_id),
  KEY vote_id (vote_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_vote_voters'
#
DROP TABLE IF EXISTS phpbb_vote_voters;
CREATE TABLE phpbb_vote_voters (
  vote_id int(11) NOT NULL DEFAULT '0',
  vote_user_id int(11) NOT NULL DEFAULT '0',
  vote_user_ip char(8) NOT NULL,
  KEY vote_id (vote_id),
  KEY vote_user_id (vote_user_id),
  KEY vote_user_ip (vote_user_ip)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_words'
#
DROP TABLE IF EXISTS phpbb_words;
CREATE TABLE phpbb_words (
   word_id int(11) NOT NULL auto_increment,
   word varchar(100) NOT NULL,
   replacement varchar(100) NOT NULL,
   PRIMARY KEY (word_id)
);