/*
 phpBB2 PostgreSQL DB schema - phpBB group 2001


 $Id$
*/

CREATE SEQUENCE phpbb_banlist_id_seq start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;
CREATE SEQUENCE phpbb_categories_id_seq start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;
CREATE SEQUENCE phpbb_config_id_seq start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;
CREATE SEQUENCE phpbb_disallow_id_seq start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;
CREATE SEQUENCE phpbb_forums_id_seq start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;
CREATE SEQUENCE phpbb_posts_id_seq start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;
CREATE SEQUENCE phpbb_privmsgs_id_seq start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;
CREATE SEQUENCE phpbb_ranks_id_seq start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;
CREATE SEQUENCE phpbb_smilies_id_seq start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;
CREATE SEQUENCE phpbb_themes_id_seq start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;
CREATE SEQUENCE phpbb_topics_id_seq start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;
CREATE SEQUENCE phpbb_users_id_seq start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;
CREATE SEQUENCE phpbb_words_id_seq start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;
CREATE SEQUENCE phpbb_groups_id_seq start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

/* --------------------------------------------------------
  Table structure for table phpbb_auth_forums
-------------------------------------------------------- */
CREATE TABLE phpbb_auth_forums (
   forum_id int4 DEFAULT '0' NOT NULL,
   auth_view int2 DEFAULT '0' NOT NULL,
   auth_read int2 DEFAULT '0' NOT NULL,
   auth_post int2 DEFAULT '0' NOT NULL,
   auth_reply int2 DEFAULT '0' NOT NULL,
   auth_edit int2 DEFAULT '0' NOT NULL,
   auth_delete int2 DEFAULT '0' NOT NULL,
   auth_announce int2 DEFAULT '0' NOT NULL,
   auth_sticky int2 DEFAULT '0' NOT NULL,
   auth_votecreate int2 DEFAULT '0' NOT NULL,
   auth_vote int2 DEFAULT '0' NOT NULL, 
   auth_attachments int2 DEFAULT '0' NOT NULL
);


/* --------------------------------------------------------
  Table structure for table phpbb_auth_access
-------------------------------------------------------- */
CREATE TABLE phpbb_auth_access (
   group_id int DEFAULT '0' NOT NULL,
   forum_id int2 DEFAULT '0' NOT NULL,
   auth_view int2 DEFAULT '0' NOT NULL,
   auth_read int2 DEFAULT '0' NOT NULL,
   auth_post int2 DEFAULT '0' NOT NULL,
   auth_reply int2 DEFAULT '0' NOT NULL,
   auth_edit int2 DEFAULT '0' NOT NULL,
   auth_delete int2 DEFAULT '0' NOT NULL,
   auth_announce int2 DEFAULT '0' NOT NULL,
   auth_sticky int2 DEFAULT '0' NOT NULL,
   auth_votecreate int2 DEFAULT '0' NOT NULL, 
   auth_attachments int2 DEFAULT '0' NOT NULL, 
   auth_vote int2 DEFAULT '0' NOT NULL,
   auth_mod int2 DEFAULT '0' NOT NULL,
   auth_admin int2 DEFAULT '0' NOT NULL
);

/* --------------------------------------------------------
  Table structure for table phpbb_user_group
-------------------------------------------------------- */
CREATE TABLE phpbb_user_group (
   group_id int DEFAULT '0' NOT NULL,
   user_id int DEFAULT '0' NOT NULL
);


/* --------------------------------------------------------
  Table structure for table phpbb_groups
-------------------------------------------------------- */
CREATE TABLE phpbb_groups (
   group_id int DEFAULT nextval('phpbb_groups_id_seq'::text) NOT NULL,
   group_name varchar(100) NOT NULL,
   group_description varchar(255) NOT NULL,
   group_moderator int4 DEFAULT '0' NOT NULL,
   group_single_user int2 DEFAULT '0' NOT NULL,
   CONSTRAINT phpbb_groups_pkey PRIMARY KEY (group_id)
);


/* --------------------------------------------------------
  Table structure for table phpbb_auth_hosts
-------------------------------------------------------- */
CREATE TABLE phpbb_auth_hosts (
   host_id int2 DEFAULT '0' NOT NULL,
   host_ip char(8) DEFAULT '' NOT NULL,
   forum_id int2,
   ip_ban int2
);


/* --------------------------------------------------------
  Table structure for table phpbb_banlist
-------------------------------------------------------- */
CREATE TABLE phpbb_banlist (
   ban_id int4 DEFAULT nextval('phpbb_banlist_id_seq'::text) NOT NULL,
   ban_userid int4,
   ban_ip char(8),
   ban_email varchar(255),
   CONSTRAINT phpbb_banlist_pkey PRIMARY KEY (ban_id)
);
CREATE  INDEX ban_userid_phpbb_banlist_index ON phpbb_banlist (ban_userid);


/* --------------------------------------------------------
  Table structure for table phpbb_categories
-------------------------------------------------------- */
CREATE TABLE phpbb_categories (
   cat_id int4 DEFAULT nextval('phpbb_categories_id_seq'::text) NOT NULL,
   cat_title varchar(100),
   cat_order int4,
   CONSTRAINT phpbb_categories_pkey PRIMARY KEY (cat_id)
);


/* --------------------------------------------------------
  Table structure for table phpbb_config
-------------------------------------------------------- */
CREATE TABLE phpbb_config (
   config_id int2 NOT NULL,
   sitename varchar(100) NOT NULL,
   allow_html int2 NOT NULL,
   allow_bbcode int2 NOT NULL,
   allow_smilies int2 NOT NULL,
   allow_sig int2 NOT NULL,
   allow_namechange int2 NOT NULL,
   allow_theme_create int2 NOT NULL,
   allow_avatar_upload int2 DEFAULT '0' NOT NULL,
   posts_per_page int2 NOT NULL,
   topics_per_page int2 NOT NULL,
   hot_threshold int2 NOT NULL,
   email_sig varchar(255) NOT NULL,
   email_from varchar(100) NOT NULL,
   default_theme int4 NOT NULL,
   default_dateformat varchar(20) NOT NULL,
   default_lang varchar(50) NOT NULL,
   system_timezone int4 NOT NULL,
   sys_template varchar(50) NOT NULL,
   avatar_filesize int4 DEFAULT '6144' NOT NULL,
   avatar_path varchar(255) DEFAULT 'images/avatars' NOT NULL, 
   override_themes int2 NOT NULL,
   flood_interval int NOT NULL,
   selected int2 NOT NULL,
   CONSTRAINT phpbb_config_pkey PRIMARY KEY (config_id)
);


/* --------------------------------------------------------
  Table structure for table phpbb_disallow
-------------------------------------------------------- */
CREATE TABLE phpbb_disallow (
   disallow_id int4 DEFAULT nextval('phpbb_disallow_id_s'::text) NOT NULL,
   disallow_username varchar(50),
   CONSTRAINT phpbb_disallow_pkey PRIMARY KEY (disallow_id)
);


/* --------------------------------------------------------
  Table structure for table phpbb_forum_access
-------------------------------------------------------- */
CREATE TABLE phpbb_forum_access (
   forum_id int4 DEFAULT '0' NOT NULL,
   user_id int4 DEFAULT '0' NOT NULL,
   can_post int2 DEFAULT '0' NOT NULL
);
CREATE  INDEX _phpbb_forum_access_index ON phpbb_forum_access (forum_id, user_id);


/* --------------------------------------------------------
  Table structure for table phpbb_forum_mods
-------------------------------------------------------- */
CREATE TABLE phpbb_forum_mods (
   forum_id int4 DEFAULT '0' NOT NULL,
   user_id int4 DEFAULT '0' NOT NULL,
   mod_notify int2
);
CREATE  INDEX _phpbb_forum_mods_index ON phpbb_forum_mods (forum_id, user_id);


/* --------------------------------------------------------
  Table structure for table phpbb_forums
-------------------------------------------------------- */
CREATE TABLE phpbb_forums (
   forum_id int4 DEFAULT nextval('phpbb_forums_id_seq'::text) NOT NULL,
   cat_id int4,
   forum_name varchar(150),
   forum_desc text,
   forum_access int2,
   forum_order int4 DEFAULT '1' NOT NULL,
   forum_type int2,
   forum_posts int4 DEFAULT '0' NOT NULL,
   forum_topics int4 DEFAULT '0' NOT NULL,
   forum_last_post_id int4 DEFAULT '0' NOT NULL,
   CONSTRAINT phpbb_forums_pkey PRIMARY KEY (forum_id)
);
CREATE  INDEX cat_id_phpbb_forums_index ON phpbb_forums (cat_id);
CREATE  INDEX forum_id_phpbb_forums_index ON phpbb_forums (forum_id);
CREATE  INDEX forum_type_phpbb_forums_index ON phpbb_forums (forum_type);
CREATE  INDEX forums_order_phpbb_forums_index ON phpbb_forums (forum_order);


/* --------------------------------------------------------
  Table structure for table phpbb_groups
-------------------------------------------------------- */
CREATE TABLE phpbb_groups (
   group_id int4 DEFAULT '0' NOT NULL,
   group_name varchar(100) DEFAULT '' NOT NULL,
   group_note varchar(255) DEFAULT '' NOT NULL,
   group_level int2 DEFAULT '0' NOT NULL
);


/* --------------------------------------------------------
  Table structure for table phpbb_posts
-------------------------------------------------------- */
CREATE TABLE phpbb_posts (
   post_id int4 DEFAULT nextval('phpbb_posts_id_seq'::text) NOT NULL,
   topic_id int4 DEFAULT '0' NOT NULL,
   forum_id int4 DEFAULT '0' NOT NULL,
   poster_id int4 DEFAULT '0' NOT NULL,
   post_time int4 DEFAULT '0' NOT NULL,
   poster_ip varchar(8) DEFAULT '' NOT NULL,
   bbcode_uid varchar(10) DEFAULT '' NOT NULL,
   CONSTRAINT phpbb_posts_pkey PRIMARY KEY (post_id)
);
CREATE  INDEX forum_id_phpbb_posts_index ON phpbb_posts (forum_id);
CREATE  INDEX post_time_phpbb_posts_index ON phpbb_posts (post_time);
CREATE  INDEX poster_id_phpbb_posts_index ON phpbb_posts (poster_id);
CREATE  INDEX topic_id_phpbb_posts_index ON phpbb_posts (topic_id);


/* --------------------------------------------------------
  Table structure for table phpbb_posts_text
-------------------------------------------------------- */
CREATE TABLE phpbb_posts_text (
   post_id int4 DEFAULT '0' NOT NULL,
   post_subject varchar(255),
   post_text text,
   CONSTRAINT phpbb_posts_text_pkey PRIMARY KEY (post_id)
);
CREATE  INDEX post_id_phpbb_posts_text_index ON phpbb_posts_text (post_id);


/* --------------------------------------------------------
  Table structure for table phpbb_privmsgs
-------------------------------------------------------- */
CREATE TABLE phpbb_privmsgs (
   msg_id int4 DEFAULT nextval('phpbb_privmsgs_id_seq'::text) NOT NULL,
   from_userid int4 DEFAULT '0' NOT NULL,
   to_userid int4 DEFAULT '0' NOT NULL,
   msg_time int4 DEFAULT '0' NOT NULL,
   poster_ip varchar(8),
   msg_status int4 DEFAULT '0' NOT NULL,
   msg_text text NOT NULL,
   newmsg int2 DEFAULT '0' NOT NULL,
   CONSTRAINT phpbb_privmsgs_pkey PRIMARY KEY (msg_id)
);
CREATE  INDEX to_userid_phpbb_privmsgs_index ON phpbb_privmsgs (to_userid);


/* --------------------------------------------------------
  Table structure for table phpbb_ranks
-------------------------------------------------------- */
CREATE TABLE phpbb_ranks (
   rank_id int4 DEFAULT nextval('phpbb_ranks_id_seq'::text) NOT NULL,
   rank_title varchar(50) DEFAULT '' NOT NULL,
   rank_min int4 DEFAULT '0' NOT NULL,
   rank_max int4 DEFAULT '0' NOT NULL,
   rank_special int2 DEFAULT '0',
   rank_image varchar(255),
   CONSTRAINT phpbb_ranks_pkey PRIMARY KEY (rank_id)
);
CREATE  INDEX rank_id_phpbb_ranks_index ON phpbb_ranks (rank_id);
CREATE  INDEX rank_max_phpbb_ranks_index ON phpbb_ranks (rank_max);
CREATE  INDEX rank_min_phpbb_ranks_index ON phpbb_ranks (rank_min);


/* --------------------------------------------------------
  Table structure for table phpbb_session
-------------------------------------------------------- */
CREATE TABLE phpbb_session (
   session_id char(32) DEFAULT '0' NOT NULL,
   session_user_id int4 DEFAULT '0' NOT NULL,
   session_start int4 DEFAULT '0' NOT NULL,
   session_time int4 DEFAULT '0' NOT NULL, 
   session_last_visit int4 DEFAULT '0' NOT NULL, 
   session_ip char(8) DEFAULT '0' NOT NULL,
   session_page int4 DEFAULT '0' NOT NULL,
   session_logged_in int2 DEFAULT '0' NOT NULL,
   CONSTRAINT phpbb_session_pkey PRIMARY KEY (session_id)
);
CREATE INDEX session_user_id ON phpbb_session (session_user_id);
CREATE INDEX session_id_ip_user_id ON phpbb_session (session_id, session_ip, session_user_id);

/* --------------------------------------------------------
  Table structure for table phpbb_session_keys
-------------------------------------------------------- */
CREATE TABLE phpbb_session_keys (
   key_user_id int4 DEFAULT '0' NOT NULL,
   key_ip varchar(8) DEFAULT '' NOT NULL,
   key_login varchar(32) DEFAULT '' NOT NULL,
   CONSTRAINT phpbb_session_keys_pkey PRIMARY KEY (key_user_id)
);
CREATE  INDEX key_ip_phpbb_session_keys_index ON phpbb_session_keys (key_ip);


/* --------------------------------------------------------
  Table structure for table phpbb_smilies
-------------------------------------------------------- */
CREATE TABLE phpbb_smilies (
   smilies_id int4 DEFAULT nextval('phpbb_smilies_id_seq'::text) NOT NULL,
   code varchar(50),
   smile_url varchar(100),
   emoticon varchar(75),
   CONSTRAINT phpbb_smilies_pkey PRIMARY KEY (smilies_id)
);


/* --------------------------------------------------------
  Table structure for table phpbb_themes
-------------------------------------------------------- */
CREATE TABLE phpbb_themes (
   themes_id int4 DEFAULT nextval('phpbb_themes_id_seq'::text) NOT NULL,
   themes_name varchar(30),
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
   fontface1 varchar(30),
   fontface2 varchar(30),
   fontface3 varchar(30),
   fontsize1 int2,
   fontsize2 int2,
   fontsize3 int2,
   fontcolor1 char(6),
   fontcolor2 char(6),
   fontcolor3 char(6),
   img1 varchar(100),
   img2 varchar(100),
   img3 varchar(100),
   img4 varchar(100),
   CONSTRAINT phpbb_themes_pkey PRIMARY KEY (themes_id)
);
CREATE  INDEX themes_name_phpbb_themes_index ON phpbb_themes (themes_name);


/* --------------------------------------------------------
  Table structure for table phpbb_themes_name
-------------------------------------------------------- */
CREATE TABLE phpbb_themes_name (
   themes_id int4 DEFAULT '0' NOT NULL,
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
   CONSTRAINT phpbb_themes_name_pkey PRIMARY KEY (themes_id)
);


/* --------------------------------------------------------
  Table structure for table phpbb_topics
-------------------------------------------------------- */
CREATE TABLE phpbb_topics (
   topic_id int4 DEFAULT nextval('phpbb_topics_id_seq'::text) NOT NULL,
   topic_title varchar(100) DEFAULT '' NOT NULL,
   topic_poster int4 DEFAULT '0' NOT NULL,
   topic_time int4 DEFAULT '0' NOT NULL,
   topic_views int4 DEFAULT '0' NOT NULL,
   topic_replies int4 DEFAULT '0' NOT NULL,
   forum_id int4 DEFAULT '0' NOT NULL,
   topic_status int2 DEFAULT '0' NOT NULL,
   topic_notify int2 DEFAULT '0',
   topic_last_post_id int4 DEFAULT '0' NOT NULL,
   CONSTRAINT phpbb_topics_pkey PRIMARY KEY (topic_id)
);
CREATE  INDEX _phpbb_topics_index ON phpbb_topics (forum_id, topic_id);
CREATE  INDEX forum_id_phpbb_topics_index ON phpbb_topics (forum_id);


/* --------------------------------------------------------
  Table structure for table phpbb_user_groups
-------------------------------------------------------- */
CREATE TABLE phpbb_user_groups (
   group_id int4 DEFAULT '0' NOT NULL,
   user_id int4 DEFAULT '0' NOT NULL
);


/* --------------------------------------------------------
  Table structure for table phpbb_users
-------------------------------------------------------- */
CREATE TABLE phpbb_users (
   user_id int4 DEFAULT nextval('phpbb_users_id_seq'::text) NOT NULL,
   username varchar(40) DEFAULT '' NOT NULL,
   user_regdate int4 DEFAULT '0' NOT NULL,
   user_password varchar(32) DEFAULT '' NOT NULL,
   user_autologin_key varchar(32),
   user_email varchar(255),
   user_icq varchar(15),
   user_website varchar(100),
   user_occ varchar(100),
   user_from varchar(100),
   user_interests varchar(255),
   user_sig varchar(255),
   user_theme int4,
   user_aim varchar(255),
   user_yim varchar(255),
   user_msnm varchar(255),
   user_posts int4 DEFAULT '0',
   user_viewemail int2,
   user_attachsig int2,
   user_allowhtml int2,
   user_allowbbcode int2,
   user_allowsmile int2,
   user_rank int4 DEFAULT '0',
   user_avatar varchar(100),
   user_level int4 DEFAULT '1',
   user_lang varchar(255),
   user_timezone int4 DEFAULT '0' NOT NULL,
   user_dateformat varchar(14) DEFAULT 'd M Y H:m' NOT NULL,
   user_actkey varchar(32),
   user_newpasswd varchar(32),
   user_notify int2,
   user_active int2,
   user_template varchar(50),
   CONSTRAINT phpbb_users_pkey PRIMARY KEY (user_id)
);


/* --------------------------------------------------------
  Table structure for table phpbb_words
-------------------------------------------------------- */
CREATE TABLE phpbb_words (
   word_id int4 DEFAULT nextval('phpbb_words_id_seq'::text) NOT NULL,
   word varchar(100) DEFAULT '' NOT NULL,
   replacement varchar(100) DEFAULT '' NOT NULL,
   CONSTRAINT phpbb_words_pkey PRIMARY KEY (word_id)
);
