#
# phpBB2 - MySQL schema
#
# $id:  Exp $
#


# --------------------------------------------------------
#
# Table structure for table 'phpbb_banlist'
#

CREATE TABLE phpbb_banlist (
   ban_id int(11) NOT NULL auto_increment,
   ban_userid int(11),
   ban_ip char(8),
   ban_start char(8),
   ban_end char(8),
   ban_time_type tinyint(4),
   PRIMARY KEY (ban_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_categories'
#

CREATE TABLE phpbb_categories (
   cat_id int(11) NOT NULL auto_increment,
   cat_title varchar(100),
   cat_order varchar(10),
   PRIMARY KEY (cat_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_config'
#

CREATE TABLE phpbb_config (
   config_id int(11) NOT NULL auto_increment,
   sitename varchar(100),
   allow_html tinyint(3),
   allow_bbcode tinyint(3),
   allow_sig tinyint(3),
   allow_namechange tinyint(3),
   selected int(2) DEFAULT '0' NOT NULL,
   posts_per_page int(11),
   hot_threshold int(11),
   topics_per_page int(11),
   allow_theme_create int(11),
   override_themes tinyint(3),
   email_sig varchar(255),
   email_from varchar(100),
   default_theme int(11) DEFAULT '1' NOT NULL,
   default_lang varchar(255),
   default_dateformat varchar(14) DEFAULT 'd M Y H:m' NOT NULL,
   system_timezone int(11) DEFAULT '0' NOT NULL,
   sys_template varchar(100) DEFAULT 'Default' NOT NULL,
   PRIMARY KEY (config_id),
   UNIQUE selected (selected)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_disallow'
#

CREATE TABLE phpbb_disallow (
   disallow_id int(11) NOT NULL auto_increment,
   disallow_username varchar(50),
   PRIMARY KEY (disallow_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_forum_access'
#

CREATE TABLE phpbb_forum_access (
   forum_id int(11) NOT NULL,
   user_id int(11) NOT NULL,
   can_post tinyint(1) NOT NULL,
   PRIMARY KEY (forum_id, user_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_forum_mods'
#

CREATE TABLE phpbb_forum_mods (
   forum_id int(11) NOT NULL,
   user_id int(11) NOT NULL,
   mod_notify tinyint(3)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_forums'
#

CREATE TABLE phpbb_forums (
   forum_id int(11) NOT NULL auto_increment,
   forum_name varchar(150),
   forum_desc text,
   forum_access tinyint(3),
   cat_id int(11),
   forum_order int(11) DEFAULT '1' NOT NULL,
   forum_type tinyint(4),
   forum_posts int(11) NOT NULL,
   forum_topics tinyint(4) NOT NULL,
   forum_last_post_id int(11) NOT NULL,
   PRIMARY KEY (forum_id),
   KEY forum_id (forum_id),
   KEY forums_order (forum_order),
   KEY cat_id (cat_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_posts'
#

CREATE TABLE phpbb_posts (
   post_id int(11) NOT NULL auto_increment,
   topic_id int(11) NOT NULL,
   forum_id int(11) NOT NULL,
   poster_id int(11) NOT NULL,
   post_time int(11) NOT NULL,
   poster_ip char(8) NOT NULL,
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

CREATE TABLE phpbb_posts_text (
   post_id int(11) NOT NULL,
   post_text text,
   PRIMARY KEY (post_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_privmsgs'
#

CREATE TABLE phpbb_privmsgs (
   msg_id int(11) NOT NULL auto_increment,
   from_userid int(11) NOT NULL,
   to_userid int(11) NOT NULL,
   msg_time int(11) NOT NULL,
   poster_ip char(8) NOT NULL,
   msg_status int(11) NOT NULL,
   msg_text text NOT NULL,
   newmsg tinyint(4) NOT NULL,
   PRIMARY KEY (msg_id),
   KEY to_userid (to_userid)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_ranks'
#

CREATE TABLE phpbb_ranks (
   rank_id int(11) NOT NULL auto_increment,
   rank_title varchar(50) NOT NULL,
   rank_min int(11) NOT NULL,
   rank_max int(11) NOT NULL,
   rank_special tinyint(1),
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

CREATE TABLE phpbb_session (
   session_id int(11) NOT NULL,
   session_user_id int(11) NOT NULL,
   session_start int(11) NOT NULL,
   session_time int(11) NOT NULL,
   session_ip char(8) NOT NULL,
   session_page int(11),
   session_logged_in tinyint(1) NOT NULL,
   PRIMARY KEY (session_id),
   KEY session_ip (session_ip),
   KEY session_user_id (session_user_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_smilies'
#

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

CREATE TABLE phpbb_themes (
   themes_id int(11) NOT NULL auto_increment,
   themes_name varchar(30),
   head_stylesheet varchar(100),
   body_background varchar(100),
   body_bgcolor varchar(6),
   body_text varchar(6),
   body_link varchar(6),
   body_vlink varchar(6),
   body_alink varchar(6),
   body_hlink varchar(6),
   tr_color1 varchar(6),
   tr_color2 varchar(6),
   tr_color3 varchar(6),
   th_color1 varchar(6),
   th_color2 varchar(6),
   th_color3 varchar(6),
   td_color1 varchar(6),
   td_color2 varchar(6),
   td_color3 varchar(6),
   fontface1 varchar(15),
   fontface2 varchar(15),
   fontface3 varchar(15),
   fontsize1 tinyint(4),
   fontsize2 tinyint(4),
   fontsize3 tinyint(4),
   fontcolor1 varchar(6),
   fontcolor2 varchar(6),
   fontcolor3 varchar(6),
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

CREATE TABLE phpbb_themes_name (
   themes_id int(11) NOT NULL,
   tr_color1_name varchar(25),
   tr_color2_name varchar(25),
   tr_color3_name varchar(25),
   th_color1_name varchar(25),
   th_color2_name varchar(25),
   th_color3_name varchar(25),
   td_color1_name varchar(25),
   td_color2_name varchar(25),
   td_color3_name varchar(25),
   fontface1_name varchar(25),
   fontface2_name varchar(25),
   fontface3_name varchar(25),
   fontsize1_name varchar(25),
   fontsize2_name varchar(25),
   fontsize3_name varchar(25),
   fontcolor1_name varchar(25),
   fontcolor2_name varchar(25),
   fontcolor3_name varchar(25),
   img1_name varchar(25),
   img2_name varchar(25),
   img3_name varchar(25),
   img4_name varchar(25),
   PRIMARY KEY (themes_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_topics'
#

CREATE TABLE phpbb_topics (
   topic_id int(11) NOT NULL auto_increment,
   topic_title varchar(100) NOT NULL,
   topic_poster int(11) NOT NULL,
   topic_time int(11) NOT NULL,
   topic_views int(11) NOT NULL,
   topic_replies int(11) NOT NULL,
   forum_id int(11) NOT NULL,
   topic_status tinyint(3) NOT NULL,
   topic_notify tinyint(3),
   topic_last_post_id int(11) NOT NULL,
   PRIMARY KEY (topic_id),
   KEY forum_id (forum_id),
   KEY topic_id (topic_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_users'
#

CREATE TABLE phpbb_users (
   user_id int(11) NOT NULL auto_increment,
   username varchar(40) NOT NULL,
   user_regdate varchar(20) NOT NULL,
   user_password varchar(32) NOT NULL,
   user_autologin_key varchar(32),
   user_hint varchar(25) NOT NULL,
   user_email varchar(255),
   user_icq varchar(15),
   user_website varchar(100),
   user_occ varchar(100),
   user_from varchar(100),
   user_interests varchar(255),
   user_sig varchar(255),
   user_viewemail tinyint(3),
   user_theme int(11),
   user_aim varchar(255),
   user_yim varchar(255),
   user_msnm varchar(255),
   user_posts int(11) DEFAULT '0',
   user_attachsig tinyint(3),
   user_desmile tinyint(3),
   user_html tinyint(3),
   user_bbcode tinyint(3),
   user_rank int(11) DEFAULT '0',
   user_avatar varchar(100),
   user_level int(11) DEFAULT '1',
   user_lang varchar(255),
   user_timezone int(11) DEFAULT '0' NOT NULL,
   user_dateformat varchar(14) DEFAULT 'd M Y H:m' NOT NULL,
   user_actkey varchar(32),
   user_newpasswd varchar(32),
   user_notify tinyint(3),
   user_active tinyint(4),
   user_template varchar(50),
   PRIMARY KEY (user_id),
   KEY user_id (user_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_words'
#

CREATE TABLE phpbb_words (
   word_id int(11) NOT NULL auto_increment,
   word varchar(100) NOT NULL,
   replacement varchar(100) NOT NULL,
   PRIMARY KEY (word_id)
);

