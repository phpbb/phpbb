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
   forum_id tinyint(4) DEFAULT '0' NOT NULL,
   auth_view tinyint(1) DEFAULT '0' NOT NULL,
   auth_read tinyint(1) DEFAULT '0' NOT NULL,
   auth_post tinyint(1) DEFAULT '0' NOT NULL,
   auth_reply tinyint(1) DEFAULT '0' NOT NULL,
   auth_edit tinyint(1) DEFAULT '0' NOT NULL,
   auth_delete tinyint(1) DEFAULT '0' NOT NULL,
   auth_announce tinyint(1) DEFAULT '0' NOT NULL,
   auth_sticky tinyint(1) DEFAULT '0' NOT NULL,
   auth_votecreate tinyint(1) DEFAULT '0' NOT NULL,
   auth_attachments tinyint(1) DEFAULT '0' NOT NULL,
   auth_vote tinyint(1) DEFAULT '0' NOT NULL,
   auth_mod tinyint(1) DEFAULT '0' NOT NULL
);

#
# Table structure for table 'phpbb_user_group'
#

DROP TABLE IF EXISTS phpbb_user_group;
CREATE TABLE phpbb_user_group (
   group_id int(11) DEFAULT '0' NOT NULL,
   user_id int(11) DEFAULT '0' NOT NULL,
   user_pending tinyint(1)
);

#
# Table structure for table 'phpbb_groups'
#

DROP TABLE IF EXISTS phpbb_groups;
CREATE TABLE phpbb_groups (
   group_id int(11) NOT NULL auto_increment,
   group_name varchar(40) NOT NULL,
   group_description varchar(255) NOT NULL,
   group_moderator int(11) DEFAULT '0' NOT NULL,
   group_single_user tinyint(1) DEFAULT '0' NOT NULL,
   PRIMARY KEY (group_id)
);

# --------------------------------------------------------
#
# Table structure for table 'phpbb_banlist'
#
DROP TABLE IF EXISTS phpbb_banlist;

CREATE TABLE phpbb_banlist (
   ban_id int(10) NOT NULL auto_increment,
   ban_userid int(10),
   ban_ip char(8),
   ban_email varchar(255),
   PRIMARY KEY (ban_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_categories'
#
DROP TABLE IF EXISTS phpbb_categories;

CREATE TABLE phpbb_categories (
   cat_id int(10) NOT NULL auto_increment,
   cat_title varchar(100),
   cat_order int(11),
   PRIMARY KEY (cat_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_config'
#
DROP TABLE IF EXISTS phpbb_config;

CREATE TABLE phpbb_config (
   config_id int(10) NOT NULL auto_increment,
   board_disable tinyint(1) DEFAULT '0' NOT NULL, 
   sitename varchar(100),
   allow_html tinyint(1),
   allow_bbcode tinyint(1),
   allow_smilies tinyint(1),
   allow_sig tinyint(1),
   allow_namechange tinyint(1),
   allow_theme_create tinyint(1),
   allow_avatar_local tinyint(1) DEFAULT '0' NOT NULL, 
   allow_avatar_remote tinyint(1) DEFAULT '0' NOT NULL, 
   allow_avatar_upload tinyint(1) DEFAULT '0' NOT NULL,
   override_themes tinyint(3),
   posts_per_page int(10),
   topics_per_page int(10),
   hot_threshold int(10),
   email_sig varchar(255),
   email_from varchar(100), 
   require_activation tinyint(1) DEFAULT '0' NOT NULL, 
   flood_interval int(4) NOT NULL,
   avatar_filesize int(11) DEFAULT '6144' NOT NULL,
   avatar_max_width smallint(6) DEFAULT '70' NOT NULL, 
   avatar_max_height smallint(6) DEFAULT '70' NOT NULL, 
   avatar_path varchar(255) DEFAULT 'images/avatars' NOT NULL,
   default_theme int(11) DEFAULT '1' NOT NULL,
   default_lang varchar(255),
   default_dateformat varchar(14) DEFAULT 'd M Y H:i' NOT NULL,
   system_timezone int(11) DEFAULT '0' NOT NULL,
   sys_template varchar(100) DEFAULT 'Default' NOT NULL,
   prune_enable tinyint(1) DEFAULT '1' NOT NULL, 
   gzip_compress tinyint(1) DEFAULT '0' NOT NULL, 
   PRIMARY KEY (config_id),
   UNIQUE selected (selected)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_disallow'
#
DROP TABLE IF EXISTS phpbb_disallow;

CREATE TABLE phpbb_disallow (
   disallow_id int(10) NOT NULL auto_increment,
   disallow_username varchar(25),
   PRIMARY KEY (disallow_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_forum_prune'
#
DROP TABLE IF EXISTS phpbb_forum_prune;

CREATE TABLE phpbb_forum_prune (
   prune_id int(10) NOT NULL auto_increment,
   forum_id int(11) NOT NULL,
   prune_days int(3) NOT NULL,
   prune_freq int(3) NOT NULL,
   PRIMARY KEY(prune_id)
);

# --------------------------------------------------------
#
# Table structure for table 'phpbb_forums'
#
DROP TABLE IF EXISTS phpbb_forums;

CREATE TABLE phpbb_forums (
   forum_id int(10) NOT NULL auto_increment,
   cat_id int(10) NOT NULL,
   forum_name varchar(150),
   forum_desc text,
   forum_access tinyint(3),
   forum_order int(11) DEFAULT '1' NOT NULL,
   forum_type tinyint(4),
   forum_posts int(11) DEFAULT '0' NOT NULL,
   forum_topics tinyint(4) DEFAULT '0' NOT NULL,
   forum_last_post_id int(11) DEFAULT '0' NOT NULL,
   prune_next int(11),
   prune_enable tinyint(1) DEFAULT '1' NOT NULL,
   auth_view tinyint(4) DEFAULT '0' NOT NULL,
   auth_read tinyint(4) DEFAULT '0' NOT NULL,
   auth_post tinyint(4) DEFAULT '0' NOT NULL,
   auth_reply tinyint(4) DEFAULT '0' NOT NULL,
   auth_edit tinyint(4) DEFAULT '0' NOT NULL,
   auth_delete tinyint(4) DEFAULT '0' NOT NULL,
   auth_announce tinyint(4) DEFAULT '0' NOT NULL,
   auth_sticky tinyint(4) DEFAULT '0' NOT NULL,
   auth_votecreate tinyint(4) DEFAULT '0' NOT NULL,
   auth_vote tinyint(4) DEFAULT '0' NOT NULL,
   auth_attachments tinyint(4) DEFAULT '0' NOT NULL,
   PRIMARY KEY (forum_id),
   KEY forum_id (forum_id),
   KEY forums_order (forum_order),
   KEY cat_id (cat_id)
);



# --------------------------------------------------------
#
# Table structure for table 'phpbb_posts'
#
DROP TABLE IF EXISTS phpbb_posts;

CREATE TABLE phpbb_posts (
   post_id int(10) NOT NULL auto_increment,
   topic_id int(10) DEFAULT '0' NOT NULL,
   forum_id int(10) DEFAULT '0' NOT NULL,
   poster_id int(10) DEFAULT '0' NOT NULL,
   post_time int(10) DEFAULT '0' NOT NULL,
   poster_ip char(8) NOT NULL, 
   post_username varchar(30), 
   bbcode_uid varchar(10) NOT NULL,
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
   post_id int(10) DEFAULT '0' NOT NULL,
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
   privmsgs_bbcode_uid varchar(10) DEFAULT '0' NOT NULL,
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
   PRIMARY KEY (rank_id),
   KEY rank_min (rank_min),
   KEY rank_max (rank_max),
   KEY rank_id (rank_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_session'
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
   INDEX session_user_id (session_user_id),
   INDEX session_id_ip_user_id (session_id, session_ip, session_user_id)
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
   themes_name varchar(30) NOT NULL,
   head_stylesheet varchar(100),
   body_background varchar(100),
   body_bgcolor char(6),
   body_text char(6),
   body_link char(6),
   body_vlink char(6),
   body_alink char(6),
   body_hlink char(6),
   tr_color1 char(6),
   tr_color2 char(6),
   tr_color3 char(6),
   th_color1 char(6),
   th_color2 char(6),
   th_color3 char(6),
   td_color1 char(6),
   td_color2 char(6),
   td_color3 char(6),
   fontface1 varchar(50),
   fontface2 varchar(50),
   fontface3 varchar(50),
   fontsize1 tinyint(4),
   fontsize2 tinyint(4),
   fontsize3 tinyint(4),
   fontcolor1 char(6),
   fontcolor2 char(6),
   fontcolor3 char(6),
   img1 varchar(100),
   img2 varchar(100),
   img3 varchar(100),
   img4 varchar(100),
   PRIMARY KEY (themes_id),
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
   th_color1_name varchar(50),
   th_color2_name varchar(50),
   th_color3_name varchar(50),
   td_color1_name varchar(50),
   td_color2_name varchar(50),
   td_color3_name varchar(50),
   fontface1_name varchar(50),
   fontface2_name varchar(50),
   fontface3_name varchar(50),
   fontsize1_name varchar(50),
   fontsize2_name varchar(50),
   fontsize3_name varchar(50),
   fontcolor1_name varchar(50),
   fontcolor2_name varchar(50),
   fontcolor3_name varchar(50),
   img1_name varchar(50),
   img2_name varchar(50),
   img3_name varchar(50),
   img4_name varchar(50),
   PRIMARY KEY (themes_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_topics'
#
DROP TABLE IF EXISTS phpbb_topics;

CREATE TABLE phpbb_topics (
   topic_id int(10) NOT NULL auto_increment,
   forum_id int(10) DEFAULT '0' NOT NULL,
   topic_title varchar(100) NOT NULL,
   topic_poster int(10) DEFAULT '0' NOT NULL,
   topic_time int(10) DEFAULT '0' NOT NULL,
   topic_views int(10) DEFAULT '0' NOT NULL,
   topic_replies int(11) DEFAULT '0' NOT NULL,
   topic_status tinyint(3) DEFAULT '0' NOT NULL,
   topic_type tinyint(3) DEFAULT '0' NOT NULL,
   topic_notify tinyint(3) DEFAULT '0',
   topic_last_post_id int(11) DEFAULT '0' NOT NULL,
   PRIMARY KEY (topic_id),
   KEY forum_id (forum_id),
   KEY topic_id (topic_id)
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
   user_notify_pm tinyint(1) DEFAULT '1' NOT NULL, 
   user_regdate int(11) DEFAULT '0' NOT NULL,
   user_rank int(11) DEFAULT '0',
   user_avatar varchar(100),
   user_email varchar(255),
   user_icq varchar(15),
   user_website varchar(100),
   user_occ varchar(100),
   user_from varchar(100),
   user_interests varchar(255),
   user_sig varchar(255),
   user_aim varchar(255),
   user_yim varchar(255),
   user_msnm varchar(255),
   user_posts int(11) DEFAULT '0',
   user_actkey varchar(32),
   user_newpasswd varchar(32),
   user_notify tinyint(3),
   PRIMARY KEY (user_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_words'
#
DROP TABLE IF EXISTS phpbb_words;

CREATE TABLE phpbb_words (
   word_id int(10) NOT NULL auto_increment,
   word varchar(100) NOT NULL,
   replacement varchar(100) NOT NULL,
   PRIMARY KEY (word_id)
);
