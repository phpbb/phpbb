#
# Structure of phpBB2 DB as at
#
# 24 Feb 2001 22.18 GMT 
#

# --------------------------------------------------------
#
# Table structure for table 'phpbb_banlist'
#

CREATE TABLE phpbb_banlist (
   ban_id int(10) NOT NULL auto_increment,
   ban_userid int(10),
   ban_ip int(10),
   ban_start int(10),
   ban_end int(10),
   ban_time_type int(10),
   PRIMARY KEY (ban_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_categories'
#

CREATE TABLE phpbb_categories (
   cat_id int(10) NOT NULL auto_increment,
   cat_title varchar(100),
   cat_order varchar(10),
   PRIMARY KEY (cat_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_config'
#

CREATE TABLE phpbb_config (
   config_id int(10) NOT NULL auto_increment,
   sitename varchar(100),
   allow_html tinyint(3),
   allow_bbcode tinyint(3),
   allow_sig tinyint(3),
   allow_namechange tinyint(3),
   selected int(2) DEFAULT '0' NOT NULL,
   posts_per_page int(10),
   hot_threshold int(10),
   topics_per_page int(10),
   allow_theme_create int(10),
   override_themes tinyint(3),
   email_sig varchar(255),
   email_from varchar(100),
   default_lang varchar(255),
   PRIMARY KEY (config_id),
   UNIQUE selected (selected)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_disallow'
#

CREATE TABLE phpbb_disallow (
   disallow_id int(10) NOT NULL auto_increment,
   disallow_username varchar(50),
   PRIMARY KEY (disallow_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_forum_access'
#

CREATE TABLE phpbb_forum_access (
   forum_id int(10) DEFAULT '0' NOT NULL,
   user_id int(10) DEFAULT '0' NOT NULL,
   can_post tinyint(1) DEFAULT '0' NOT NULL,
   PRIMARY KEY (forum_id, user_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_forum_mods'
#

CREATE TABLE phpbb_forum_mods (
   forum_id int(10) DEFAULT '0' NOT NULL,
   user_id int(10) DEFAULT '0' NOT NULL,
   mod_notify tinyint(3)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_forums'
#

CREATE TABLE phpbb_forums (
   forum_id int(10) NOT NULL auto_increment,
   forum_name varchar(150),
   forum_desc text,
   forum_access tinyint(3),
   cat_id int(10),
   forum_type tinyint(3),
   forum_posts int(11) DEFAULT '0' NOT NULL,
   forum_topics tinyint(4) DEFAULT '0' NOT NULL,
   forum_last_post_id int(11) DEFAULT '0' NOT NULL,
   PRIMARY KEY (forum_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_headermetafooter'
#

CREATE TABLE phpbb_headermetafooter (
   header text,
   meta text,
   footer text
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_posts'
#

CREATE TABLE phpbb_posts (
   post_id int(10) NOT NULL auto_increment,
   topic_id int(10) DEFAULT '0' NOT NULL,
   forum_id int(10) DEFAULT '0' NOT NULL,
   poster_id int(10) DEFAULT '0' NOT NULL,
   post_time int(10) DEFAULT '0' NOT NULL,
   poster_ip int(10) DEFAULT '0' NOT NULL,
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
   post_id int(10) DEFAULT '0' NOT NULL,
   post_text text,
   PRIMARY KEY (post_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_priv_msgs'
#

CREATE TABLE phpbb_priv_msgs (
   msg_id int(10) NOT NULL auto_increment,
   from_userid int(10) DEFAULT '0' NOT NULL,
   to_userid int(10) DEFAULT '0' NOT NULL,
   msg_time int(10) DEFAULT '0' NOT NULL,
   poster_ip int(10) DEFAULT '0' NOT NULL,
   msg_status int(10) DEFAULT '0' NOT NULL,
   msg_text text NOT NULL,
   PRIMARY KEY (msg_id),
   KEY to_userid (to_userid)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_ranks'
#

CREATE TABLE phpbb_ranks (
   rank_id int(10) NOT NULL auto_increment,
   rank_title varchar(50) NOT NULL,
   rank_min int(10) DEFAULT '0' NOT NULL,
   rank_max int(10) DEFAULT '0' NOT NULL,
   rank_special int(2) DEFAULT '0',
   rank_image varchar(255),
   PRIMARY KEY (rank_id),
   KEY rank_min (rank_min),
   KEY rank_max (rank_max)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_sessions'
#

CREATE TABLE phpbb_sessions (
   sess_id int(10) unsigned DEFAULT '0' NOT NULL,
   user_id int(10) DEFAULT '0' NOT NULL,
   start_time int(10) unsigned DEFAULT '0' NOT NULL,
   remote_ip int(10) DEFAULT '0' NOT NULL,
   username varchar(40),
   forum int(10),
   PRIMARY KEY (sess_id),
   KEY start_time (start_time),
   KEY remote_ip (remote_ip)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_themes'
#

CREATE TABLE phpbb_themes (
   theme_id int(10) NOT NULL auto_increment,
   theme_name varchar(35),
   bgcolor varchar(10),
   textcolor varchar(10),
   color1 varchar(10),
   color2 varchar(10),
   table_bgcolor varchar(10),
   header_image varchar(50),
   newtopic_image varchar(50),
   reply_image varchar(50),
   linkcolor varchar(15),
   vlinkcolor varchar(15),
   theme_default int(2) DEFAULT '0',
   fontface varchar(100),
   fontsize1 varchar(5),
   fontsize2 varchar(5),
   fontsize3 varchar(5),
   fontsize4 varchar(5),
   tablewidth varchar(10),
   replylocked_image varchar(255),
   PRIMARY KEY (theme_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_topics'
#

CREATE TABLE phpbb_topics (
   topic_id int(10) NOT NULL auto_increment,
   topic_title varchar(100) NOT NULL,
   topic_poster int(10) DEFAULT '0' NOT NULL,
   topic_time int(10) DEFAULT '0' NOT NULL,
   topic_views int(10) DEFAULT '0' NOT NULL,
   forum_id int(10) DEFAULT '0' NOT NULL,
   topic_status tinyint(3) DEFAULT '0' NOT NULL,
   topic_notify tinyint(3) DEFAULT '0',
   topic_last_post_id int(11) DEFAULT '0' NOT NULL,
   PRIMARY KEY (topic_id),
   KEY forum_id (forum_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_users'
#

CREATE TABLE phpbb_users (
   user_id int(10) NOT NULL auto_increment,
   username varchar(40) NOT NULL,
   user_regdate varchar(20) NOT NULL,
   user_password varchar(32) NOT NULL,
   user_email varchar(255),
   user_icq varchar(15),
   user_website varchar(100),
   user_occ varchar(100),
   user_from varchar(100),
   user_intrest varchar(150),
   user_sig varchar(255),
   user_viewemail tinyint(3),
   user_theme int(10),
   user_aim varchar(255),
   user_yim varchar(255),
   user_msnm varchar(255),
   user_posts int(10) DEFAULT '0',
   user_attachsig tinyint(3),
   user_desmile tinyint(3),
   user_html tinyint(3),
   user_bbcode tinyint(3),
   user_rank int(10) DEFAULT '0',
   user_level int(10) DEFAULT '1',
   user_lang varchar(255),
   user_actkey varchar(32),
   user_newpasswd varchar(32),
   user_notify tinyint(3),
   PRIMARY KEY (user_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_whosonline'
#

CREATE TABLE phpbb_whosonline (
   id int(3) NOT NULL auto_increment,
   ip varchar(255),
   name varchar(255),
   count varchar(255),
   date varchar(255),
   username varchar(40),
   forum int(10),
   PRIMARY KEY (id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_words'
#

CREATE TABLE phpbb_words (
   word_id int(10) NOT NULL auto_increment,
   word varchar(100) NOT NULL,
   replacement varchar(100) NOT NULL,
   PRIMARY KEY (word_id)
);
