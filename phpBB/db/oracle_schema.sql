/*
 phpBB2 Oracle 8i DB schema - (c) 2001 The phpBB Group

 $Id$
*/

/*
  This first section is optional, however its probably the best method
  of running phpBB on Oracle. If you already have a tablespace and user created
  for phpBB you can leave this section commented out!

  The first set of statements create a phpBB tablespace and a phpBB user,
  make sure you change the password of the phpBB user befor you run this script!!
*/

/*
CREATE TABLESPACE phpbb
	DATAFILE 'E:/web/Oracle8i/ORADATA/phpbb01.dbf'
	SIZE 10M
	AUTOEXTEND ON NEXT 10M
	MAXSIZE 100M;

CREATE USER phpbb
	IDENTIFIED BY phpbb_password
	DEFAULT TABLESPACE phpbb
	TEMPORARY TABLESPACE temp;

GRANT CREATE SESSION TO phpbb;
GRANT CREATE TABLE TO phpbb;
GRANT CREATE SEQUENCE TO phpbb;
GRANT CREATE TRIGGER TO phpbb;

ALTER USER phpbb QUOTA unlimited ON phpbb;

COMMIT;
DISCONNECT;

CONNECT phpbb/phpbb_password;
*/



CREATE SEQUENCE phpbb_banlist_id_seq increment by 1 start with 2 minvalue 0;
CREATE SEQUENCE phpbb_categories_id_seq increment by 1 start with 2 minvalue 0;
CREATE SEQUENCE phpbb_config_id_seq increment by 1 start with 2 minvalue 0;
CREATE SEQUENCE phpbb_disallow_id_seq  increment by 1 start with 2 minvalue 0;
CREATE SEQUENCE phpbb_forums_id_seq increment by 1 start with 2 minvalue 0;
CREATE SEQUENCE phpbb_posts_id_seq increment by 1 start with 2 minvalue 0;
CREATE SEQUENCE phpbb_privmsgs_id_seq increment by 1 start with 2 minvalue 0;
CREATE SEQUENCE phpbb_ranks_id_seq increment by 1 start with 2 minvalue 0;
CREATE SEQUENCE phpbb_smilies_id_seq increment by 1 start with 2 minvalue 0;
CREATE SEQUENCE phpbb_themes_id_seq increment by 1 start with 2 minvalue 0;
CREATE SEQUENCE phpbb_topics_id_seq increment by 1 start with 2 minvalue 0;
CREATE SEQUENCE phpbb_users_id_seq increment by 1 start with 2 minvalue 0;
CREATE SEQUENCE phpbb_words_id_seq increment by 1 start with 2 minvalue 0;
CREATE SEQUENCE phpbb_groups_id_seq increment by 1 start with 2 minvalue 0;
CREATE SEQUENCE phpbb_forum_prune_id_seq increment by 1 start with 2 minvalue 0;
CREATE SEQUENCE phpbb_vote_desc_id_seq increment by 1 start with 2 minvalue 0;


/* --------------------------------------------------------
  Table structure for table phpbb_auth_access
-------------------------------------------------------- */
CREATE TABLE phpbb_auth_access (
   group_id number(4) DEFAULT '0' NOT NULL,
   forum_id number(4) DEFAULT '0' NOT NULL,
   auth_view number(4) DEFAULT '0' NOT NULL,
   auth_read number(4) DEFAULT '0' NOT NULL,
   auth_post number(4) DEFAULT '0' NOT NULL,
   auth_reply number(4) DEFAULT '0' NOT NULL,
   auth_edit number(4) DEFAULT '0' NOT NULL,
   auth_delete number(4) DEFAULT '0' NOT NULL,
   auth_announce number(4) DEFAULT '0' NOT NULL,
   auth_sticky number(4) DEFAULT '0' NOT NULL,
   auth_pollcreate number(4) DEFAULT '0' NOT NULL,
   auth_attachments number(4) DEFAULT '0' NOT NULL,
   auth_vote number(4) DEFAULT '0' NOT NULL,
   auth_mod number(4) DEFAULT '0' NOT NULL,
   CONSTRAINT phpbb_auth_access_pkey PRIMARY KEY (group_id)
);
CREATE  INDEX group_phpbb_auth_access_index ON phpbb_auth_access (forum_id);


/* --------------------------------------------------------
  Table structure for table phpbb_groups
-------------------------------------------------------- */
CREATE TABLE phpbb_groups (
   group_id number(4) NOT NULL,
   group_name varchar(40) NOT NULL,
   group_type number(2) DEFAULT '1' NOT NULL,
   group_description varchar(255) NOT NULL,
   group_moderator number(4) DEFAULT '0' NOT NULL,
   group_single_user number(4) DEFAULT '0' NOT NULL,
   CONSTRAINT phpbb_groups_pkey PRIMARY KEY (group_id)
);

/* --------------------------------------------------------
  Table structure for table phpbb_banlist
-------------------------------------------------------- */
CREATE TABLE phpbb_banlist (
   ban_id number(4) NOT NULL,
   ban_userid number(4),
   ban_ip char(8),
   ban_email varchar(255),
   CONSTRAINT phpbb_banlist_pkey PRIMARY KEY (ban_id)
);
CREATE  INDEX ban_userid_phpbb_banlist_index ON phpbb_banlist (ban_userid);


/* --------------------------------------------------------
  Table structure for table phpbb_categories
-------------------------------------------------------- */
CREATE TABLE phpbb_categories (
   cat_id number(4) NOT NULL,
   cat_title varchar(100),
   cat_order number(4),
   CONSTRAINT phpbb_categories_pkey PRIMARY KEY (cat_id)
);


/* --------------------------------------------------------
  Table structure for table phpbb_config
-------------------------------------------------------- */
CREATE TABLE phpbb_config (
   config_name varchar(255) NOT NULL,
   config_value varchar(255),
   CONSTRAINT phpbb_config_pkey PRIMARY KEY (config_name)
);


/* --------------------------------------------------------
  Table structure for table phpbb_disallow
-------------------------------------------------------- */
CREATE TABLE phpbb_disallow (
   disallow_id number(4) NOT NULL,
   disallow_username varchar(25),
   CONSTRAINT phpbb_disallow_pkey PRIMARY KEY (disallow_id)
);


/* --------------------------------------------------------
  Table structure for table phpbb_forums
-------------------------------------------------------- */
CREATE TABLE phpbb_forums (
   forum_id number(4) NOT NULL,
   cat_id number(4),
   forum_name varchar(150),
   forum_desc varchar(2000),
   forum_status number(4) DEFAULT '0' NOT NULL,
   forum_order number(4) DEFAULT '1' NOT NULL,
   forum_posts number(4) DEFAULT '0' NOT NULL,
   forum_topics number(4) DEFAULT '0' NOT NULL,
   forum_last_post_id number(4) DEFAULT '0' NOT NULL,
   prune_enable number(4) DEFAULT '1' NOT NULL,
   prune_next number(4),
   auth_view number(4) DEFAULT '0' NOT NULL,
   auth_read number(4) DEFAULT '0' NOT NULL,
   auth_post number(4) DEFAULT '0' NOT NULL,
   auth_reply number(4) DEFAULT '0' NOT NULL,
   auth_edit number(4) DEFAULT '0' NOT NULL,
   auth_delete number(4) DEFAULT '0' NOT NULL,
   auth_announce number(4) DEFAULT '0' NOT NULL,
   auth_sticky number(4) DEFAULT '0' NOT NULL,
   auth_pollcreate number(4) DEFAULT '0' NOT NULL,
   auth_vote number(4) DEFAULT '0' NOT NULL,
   auth_attachments number(4) DEFAULT '0' NOT NULL,
   CONSTRAINT phpbb_forums_pkey PRIMARY KEY (forum_id)
);
CREATE  INDEX cat_id_phpbb_forums_index ON phpbb_forums (cat_id);
CREATE  INDEX forums_order_phpbb_forums ON phpbb_forums (forum_order);


/* --------------------------------------------------------
  Table structure for table phpbb_forum_prune
-------------------------------------------------------- */
CREATE TABLE phpbb_forum_prune (
   prune_id number(4) NOT NULL,
   forum_id number(4) NOT NULL,
   prune_days number(4) NOT NULL,
   prune_freq number(4) NOT NULL,
   CONSTRAINT phpbb_forum_prune_pkey PRIMARY KEY (prune_id)
);
CREATE  INDEX forum_id_phpbb_forum_prune ON phpbb_forum_prune (forum_id);


/* --------------------------------------------------------
  Table structure for table phpbb_posts
-------------------------------------------------------- */
CREATE TABLE phpbb_posts (
   post_id number(4) NOT NULL,
   topic_id number(4) DEFAULT '0' NOT NULL,
   forum_id number(4) DEFAULT '0' NOT NULL,
   poster_id number(4) DEFAULT '0' NOT NULL,
   post_time number(11) DEFAULT '0' NOT NULL,
   post_username varchar(30),
   poster_ip char(8) DEFAULT '' NOT NULL,
   enable_bbcode number(4) DEFAULT '1' NOT NULL,
   enable_html number(4) DEFAULT '0' NOT NULL,
   enable_smilies number(4) DEFAULT '1' NOT NULL,
   enable_sig number(4) DEFAULT '1' NOT NULL,
   bbcode_uid varchar(10) DEFAULT '',
   post_edit_time number(11),
   post_edit_count number(4) DEFAULT '0' NOT NULL,
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
   post_id number(4) DEFAULT '0' NOT NULL,
   post_subject varchar(255),
   post_text varchar(2000),
   CONSTRAINT phpbb_posts_text_pkey PRIMARY KEY (post_id)
);


/* --------------------------------------------------------
  Table structure for table phpbb_privmsgs
-------------------------------------------------------- */
CREATE TABLE phpbb_privmsgs (
   privmsgs_id number(4) NOT NULL,
   privmsgs_type number(4) DEFAULT '0' NOT NULL,
   privmsgs_subject varchar(255) DEFAULT '0' NOT NULL,
   privmsgs_from_userid number(4) DEFAULT '0' NOT NULL,
   privmsgs_to_userid number(4) DEFAULT '0' NOT NULL,
   privmsgs_date number(4) DEFAULT '0' NOT NULL,
   privmsgs_ip char(8) NOT NULL,
   privmsgs_bbcode_uid varchar(10) DEFAULT '0' NOT NULL,
   CONSTRAINT phpbb_privmsgs_pkey PRIMARY KEY (privmsgs_id)
);
CREATE  INDEX privmsgs_from_userid_index ON phpbb_privmsgs (privmsgs_from_userid);
CREATE  INDEX privmsgs_to_userid_index ON phpbb_privmsgs (privmsgs_to_userid);


/* --------------------------------------------------------
  Table structure for table phpbb_privmsgs_text
-------------------------------------------------------- */
CREATE TABLE phpbb_privmsgs_text (
   privmsgs_text_id number(4) DEFAULT '0' NOT NULL,
   privmsgs_text varchar(2000),
   CONSTRAINT phpbb_privmsgs_text_pkey PRIMARY KEY (privmsgs_text_id)
);


/* --------------------------------------------------------
  Table structure for table phpbb_ranks
-------------------------------------------------------- */
CREATE TABLE phpbb_ranks (
   rank_id number(4) NOT NULL,
   rank_title varchar(50) DEFAULT '' NOT NULL,
   rank_min number(4) DEFAULT '0' NOT NULL,
   rank_max number(4) DEFAULT '0' NOT NULL,
   rank_special number(4) DEFAULT '0',
   rank_image varchar(255),
   CONSTRAINT phpbb_ranks_pkey PRIMARY KEY (rank_id)
);
CREATE  INDEX rank_max_phpbb_ranks_index ON phpbb_ranks (rank_max);
CREATE  INDEX rank_min_phpbb_ranks_index ON phpbb_ranks (rank_min);


/* --------------------------------------------------------
  Table structure for table phpbb_session
-------------------------------------------------------- */
CREATE TABLE phpbb_sessions (
   session_id char(32) DEFAULT '0' NOT NULL,
   session_user_id number(11) DEFAULT '0' NOT NULL,
   session_start number(11) DEFAULT '0' NOT NULL,
   session_time number(11) DEFAULT '0' NOT NULL,
   session_last_visit number(11) DEFAULT '0' NOT NULL,
   session_ip char(8) DEFAULT '0' NOT NULL,
   session_page number(11) DEFAULT '0' NOT NULL,
   session_logged_in number(11) DEFAULT '0' NOT NULL,
   CONSTRAINT phpbb_sessions_pkey PRIMARY KEY (session_id)
);
CREATE INDEX session_id_ip_user_id ON phpbb_sessions (session_id, session_ip, session_user_id);


/* --------------------------------------------------------
  Table structure for table phpbb_smilies
-------------------------------------------------------- */
CREATE TABLE phpbb_smilies (
   smilies_id number(4) NOT NULL,
   code varchar(50),
   smile_url varchar(100),
   emoticon varchar(75),
   CONSTRAINT phpbb_smilies_pkey PRIMARY KEY (smilies_id)
);


/* --------------------------------------------------------
  Table structure for table phpbb_themes
-------------------------------------------------------- */
CREATE TABLE phpbb_themes (
   themes_id number(4) NOT NULL,
   style_name varchar(30),
   template_name varchar(30) DEFAULT '' NOT NULL,
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
   tr_class1 varchar(25),
   tr_class2 varchar(25),
   tr_class3 varchar(25),
   th_color1 char(6),
   th_color2 char(6),
   th_color3 char(6),
   th_class1 varchar(25),
   th_class2 varchar(25),
   th_class3 varchar(25),
   td_color1 char(6),
   td_color2 char(6),
   td_color3 char(6),
   td_class1 varchar(25),
   td_class2 varchar(25),
   td_class3 varchar(25),
   fontface1 varchar(25),
   fontface2 varchar(25),
   fontface3 varchar(25),
   fontsize1 number(4),
   fontsize2 number(4),
   fontsize3 number(4),
   fontcolor1 char(6),
   fontcolor2 char(6),
   fontcolor3 char(6),
   span_class1 varchar(25),
   span_class2 varchar(25),
   span_class3 varchar(25),
   CONSTRAINT phpbb_themes_pkey PRIMARY KEY (themes_id)
);


/* --------------------------------------------------------
  Table structure for table phpbb_themes_name
-------------------------------------------------------- */
CREATE TABLE phpbb_themes_name (
   themes_id number(4) DEFAULT '0' NOT NULL,
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
   CONSTRAINT phpbb_themes_name_pkey PRIMARY KEY (themes_id)
);


/* --------------------------------------------------------
  Table structure for table phpbb_topics
-------------------------------------------------------- */
CREATE TABLE phpbb_topics (
   topic_id number(4) NOT NULL,
   topic_title varchar(100) DEFAULT '' NOT NULL,
   topic_poster number(4) DEFAULT '0' NOT NULL,
   topic_time number(11) DEFAULT '0' NOT NULL,
   topic_views number(4) DEFAULT '0' NOT NULL,
   topic_replies number(4) DEFAULT '0' NOT NULL,
   forum_id number(4) DEFAULT '0' NOT NULL,
   topic_status number(4) DEFAULT '0' NOT NULL,
	topic_vote number(4) DEFAULT '0' NOT NULL,
   topic_type number(4) DEFAULT '0' NOT NULL,
	topic_moved_id number(4),
   topic_last_post_id number(4) DEFAULT '0' NOT NULL,
   CONSTRAINT phpbb_topics_pkey PRIMARY KEY (topic_id)
);
CREATE  INDEX phpbb_topics_index ON phpbb_topics (forum_id, topic_id);
CREATE  INDEX forum_id_phpbb_topics_index ON phpbb_topics (forum_id);

/* --------------------------------------------------------
  Table structure for table phpbb_topics_watch
-------------------------------------------------------- */
CREATE TABLE phpbb_topics_watch (
  topic_id number(4),
  user_id number(4),
  notify_status number(4) DEFAULT '0' NOT NULL
);
CREATE  INDEX phpbb_topics_watch_index ON phpbb_topics_watch (topic_id, user_id);


/* --------------------------------------------------------
  Table structure for table phpbb_user_group
-------------------------------------------------------- */
CREATE TABLE phpbb_user_group (
   group_id number(4) DEFAULT '0' NOT NULL,
   user_id number(4) DEFAULT '0' NOT NULL,
   user_pending number(4)
);
CREATE  INDEX group_id_phpbb_user_group ON phpbb_user_group (group_id);
CREATE  INDEX user_id_phpbb_user_group_index ON phpbb_user_group (user_id);


/* --------------------------------------------------------
  Table structure for table phpbb_users
-------------------------------------------------------- */
CREATE TABLE phpbb_users (
   user_id number(4) NOT NULL,
   user_active number(4),
   username varchar(25) DEFAULT '' NOT NULL,
   user_regdate number(11) DEFAULT '0' NOT NULL,
   user_password varchar(32) DEFAULT '' NOT NULL,
   user_autologin_key varchar(32),
   user_email varchar(255),
   user_icq varchar(15),
   user_website varchar(100),
   user_occ varchar(100),
   user_from varchar(100),
   user_interests varchar(255),
   user_sig varchar(2000),
   user_sig_bbcode_uid char(10),
   user_style number(4),
   user_aim varchar(255),
   user_yim varchar(255),
   user_msnm varchar(255),
   user_posts number(4) DEFAULT '0' NOT NULL,
   user_viewemail number(4),
   user_attachsig number(4),
   user_allowhtml number(4),
   user_allowbbcode number(4),
   user_allowsmile number(4),
   user_allow_pm number(4) DEFAULT '1' NOT NULL,
   user_allowavatar number(4) DEFAULT '1' NOT NULL,
   user_allow_viewonline number(4) DEFAULT '1' NOT NULL,
   user_rank number(4) DEFAULT '0',
   user_avatar varchar(100),
   user_level number(4) DEFAULT '1',
   user_lang varchar(255),
   user_timezone number(4) DEFAULT '0' NOT NULL,
   user_dateformat varchar(14) DEFAULT 'd M Y H:m' NOT NULL,
   user_notify_pm number(4) DEFAULT '1' NOT NULL,
   user_notify number(4),
   user_actkey varchar(32),
   user_newpasswd varchar(32),
   CONSTRAINT phpbb_users_pkey PRIMARY KEY (user_id)
);

/* --------------------------------------------------------
  Table structure for table phpbb_vote_desc
-------------------------------------------------------- */
CREATE TABLE phpbb_vote_desc (
  vote_id number(4) NOT NULL,
  topic_id number(4) DEFAULT '0' NOT NULL,
  vote_text varchar2(4000) NOT NULL,
  vote_start number(4) DEFAULT '0' NOT NULL,
  vote_length number(4) DEFAULT '0' NOT NULL,
  CONSTRAINT phpbb_vote_dsc_pkey PRIMARY KEY (vote_id)
);
CREATE INDEX topic_id_phpbb_vote_desc_index ON phpbb_vote_desc (topic_id);

/* --------------------------------------------------------
 Table structure for table phpbb_vote_results
-------------------------------------------------------- */
CREATE TABLE phpbb_vote_results (
  vote_id number(4) DEFAULT '0' NOT NULL,
  vote_option_id number(4) DEFAULT '0' NOT NULL,
  vote_option_text varchar(255) NOT NULL,
  vote_result number(4) DEFAULT '0' NOT NULL
);
CREATE INDEX option_id_vote_results_index ON phpbb_vote_results (vote_option_id);

/* --------------------------------------------------------
 Table structure for table phpbb_vote_voters
-------------------------------------------------------- */
CREATE TABLE phpbb_vote_voters (
  vote_id number(4) DEFAULT '0' NOT NULL,
  vote_user_id number(4) DEFAULT '0' NOT NULL,
  vote_user_ip char(8) NOT NULL
);
CREATE INDEX vote_id_vote_voters_index ON phpbb_vote_voters (vote_id);
CREATE INDEX vote_user_id_vote_voters_index ON phpbb_vote_voters (vote_user_id);
CREATE INDEX vote_user_ip_vote_voters_index ON phpbb_vote_voters (vote_user_ip);

/* --------------------------------------------------------
  Table structure for table phpbb_words
-------------------------------------------------------- */
CREATE TABLE phpbb_words (
   word_id number(4) NOT NULL,
   word varchar(100) DEFAULT '' NOT NULL,
   replacement varchar(100) DEFAULT '' NOT NULL,
   CONSTRAINT phpbb_words_pkey PRIMARY KEY (word_id)
);

COMMIT;
