/*
 Oracle Schema for phpBB 3.x - (c) phpBB Group, 2005

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
CREATE TABLESPACE "PHPBB"
	LOGGING 
	DATAFILE 'E:\ORACLE\ORADATA\LOCAL\PHPBB.ora' 
	SIZE 10M
	AUTOEXTEND ON NEXT 10M
	MAXSIZE 100M;

CREATE USER "PHPBB" 
	PROFILE "DEFAULT" 
	IDENTIFIED BY "phpbb_password" 
	DEFAULT TABLESPACE "PHPBB" 
	QUOTA UNLIMITED ON "PHPBB" 
	ACCOUNT UNLOCK;

GRANT ANALYZE ANY TO "PHPBB";
GRANT CREATE SEQUENCE TO "PHPBB";
GRANT CREATE SESSION TO "PHPBB";
GRANT CREATE TABLE TO "PHPBB";
GRANT CREATE TRIGGER TO "PHPBB";
GRANT CREATE VIEW TO "PHPBB";
GRANT "CONNECT" TO "PHPBB";

COMMIT;
DISCONNECT;

CONNECT phpbb/phpbb_password;
*/

CREATE TABLE phpbb_attachments (
  attach_id number(8) NOT NULL,
  post_msg_id number(8) DEFAULT '0' NOT NULL,
  topic_id number(8) DEFAULT '0' NOT NULL,
  in_message number(1) DEFAULT '0' NOT NULL,
  poster_id number(8) DEFAULT '0' NOT NULL,
  physical_filename varchar2(255),
  real_filename varchar2(255),
  download_count number(8) DEFAULT '0' NOT NULL,
  comment_ varchar2(255),
  extension varchar2(100),
  mimetype varchar2(100),
  filesize number(20) NOT NULL,
  filetime number(11) DEFAULT '0' NOT NULL,
  thumbnail number(1) DEFAULT '0' NOT NULL,
  CONSTRAINT pk_phpbb_attachments PRIMARY KEY (attach_id)
)
/

CREATE SEQUENCE sq_phpbb_attachments_attach_id
/

CREATE OR REPLACE TRIGGER ai_phpbb_attachments_attach_id
BEFORE INSERT ON phpbb_attachments
FOR EACH ROW WHEN (
 new.attach_id IS NULL OR new.attach_id = 0
)
BEGIN
 SELECT sq_phpbb_attachments_attach_id.nextval
 INTO :new.attach_id
 FROM dual;
END;
/

CREATE INDEX filetime on phpbb_attachments (filetime)
/
CREATE INDEX post_msg_id on phpbb_attachments (post_msg_id)
/
CREATE INDEX topic_id on phpbb_attachments (topic_id)
/
CREATE INDEX poster_id on phpbb_attachments (poster_id)
/
CREATE INDEX physical_filename on phpbb_attachments (physical_filename)
/
CREATE INDEX filesize on phpbb_attachments (filesize)
/

/*
 Table: phpbb_auth_groups
*/
CREATE TABLE phpbb_auth_groups (
  group_id number(8) DEFAULT '0' NOT NULL,
  forum_id number(8) DEFAULT '0' NOT NULL,
  auth_option_id number(8) DEFAULT '0' NOT NULL,
  auth_role_id number(8) DEFAULT '0' NOT NULL,
  auth_setting number(4) DEFAULT '0' NOT NULL
)
/

CREATE INDEX group_id on phpbb_auth_groups (group_id)
/
CREATE INDEX auth_option_id on phpbb_auth_groups (auth_option_id)
/

/*
 Table: phpbb_auth_options
*/
CREATE TABLE phpbb_auth_options (
  auth_option_id number(8) NOT NULL,
  auth_option varchar2(20),
  is_global number(1) DEFAULT '0' NOT NULL,
  is_local number(1) DEFAULT '0' NOT NULL,
  founder_only number(1) DEFAULT '0' NOT NULL,
  CONSTRAINT pk_phpbb_auth_options PRIMARY KEY (auth_option_id)
)
/

CREATE SEQUENCE sq_phpbb_auth_options_auth_opt
/

CREATE OR REPLACE TRIGGER ai_phpbb_auth_options_auth_opt
BEFORE INSERT ON phpbb_auth_options
FOR EACH ROW WHEN (
 new.auth_option_id IS NULL OR new.auth_option_id = 0
)
BEGIN
 SELECT sq_phpbb_auth_options_auth_opt.nextval
 INTO :new.auth_option_id
 FROM dual;
END;
/

CREATE INDEX auth_option on phpbb_auth_options (auth_option)
/

/*
 Table: phpbb_auth_roles
*/
CREATE TABLE phpbb_auth_roles (
  role_id number(8) NOT NULL,
  role_name varchar2(50) DEFAULT '',
  role_type varchar2(10) DEFAULT '',
  role_group_ids varchar2(255) DEFAULT '' NOT NULL,
  CONSTRAINT pk_phpbb_auth_roles PRIMARY KEY (role_id)
)
/

CREATE SEQUENCE sq_phpbb_auth_roles_role_i
/

CREATE OR REPLACE TRIGGER ai_phpbb_auth_roles_role_i
BEFORE INSERT ON phpbb_auth_roles
FOR EACH ROW WHEN (
 new.role_id IS NULL OR new.role_id = 0
)
BEGIN
 SELECT sq_phpbb_auth_roles_role_i.nextval
 INTO :new.role_id
 FROM dual;
END;
/

CREATE INDEX role_type on phpbb_auth_roles (role_type)
/

/*
 Table: phpbb_auth_roles_data
*/
CREATE TABLE phpbb_auth_roles_data (
  role_id number(8) DEFAULT '0' NOT NULL,
  auth_option_id number(8) DEFAULT '0' NOT NULL,
  auth_setting number(4) DEFAULT '0' NOT NULL,
  CONSTRAINT pk_phpbb_confirm PRIMARY KEY (role_id, auth_option_id)
)
/

/*
 Table: phpbb_auth_users
*/
CREATE TABLE phpbb_auth_users (
  user_id number(8) DEFAULT '0' NOT NULL,
  forum_id number(8) DEFAULT '0' NOT NULL,
  auth_option_id number(8) DEFAULT '0' NOT NULL,
  auth_role_id number(8) DEFAULT '0' NOT NULL,
  auth_setting number(4) DEFAULT '0' NOT NULL
)
/

CREATE INDEX user_id on phpbb_auth_users (user_id)
/
CREATE INDEX auth_option_id02 on phpbb_auth_users (auth_option_id)
/

/*
 Table: phpbb_banlist
*/
CREATE TABLE phpbb_banlist (
  ban_id number(8) NOT NULL,
  ban_userid number(8) DEFAULT '0' NOT NULL,
  ban_ip varchar2(40) DEFAULT '',
  ban_email varchar2(50) DEFAULT '',
  ban_start number(11) DEFAULT '0' NOT NULL,
  ban_end number(11) DEFAULT '0' NOT NULL,
  ban_exclude number(1) DEFAULT '0' NOT NULL,
  ban_reason varchar2(255) DEFAULT '',
  ban_give_reason varchar2(255) DEFAULT '',
  CONSTRAINT pk_phpbb_banlist PRIMARY KEY (ban_id)
)
/

CREATE SEQUENCE sq_phpbb_banlist_ban_id
/

CREATE OR REPLACE TRIGGER ai_phpbb_banlist_ban_id
BEFORE INSERT ON phpbb_banlist
FOR EACH ROW WHEN (
 new.ban_id IS NULL OR new.ban_id = 0
)
BEGIN
 SELECT sq_phpbb_banlist_ban_id.nextval
 INTO :new.ban_id
 FROM dual;
END;
/

/*
 Table: phpbb_bbcodes
*/
CREATE TABLE phpbb_bbcodes (
  bbcode_id number(3) DEFAULT '0' NOT NULL,
  bbcode_tag varchar2(16) DEFAULT '',
  display_on_posting number(1) DEFAULT '0' NOT NULL,
  bbcode_match varchar2(255) DEFAULT '',
  bbcode_tpl clob DEFAULT '',
  first_pass_match varchar2(255) DEFAULT '',
  first_pass_replace varchar2(255) DEFAULT '',
  second_pass_match varchar2(255) DEFAULT '',
  second_pass_replace clob DEFAULT '',
  CONSTRAINT pk_phpbb_bbcodes PRIMARY KEY (bbcode_id)
)
/

CREATE INDEX display_on_posting on phpbb_bbcodes (display_on_posting)
/

/*
 Table: phpbb_bookmarks
*/
CREATE TABLE phpbb_bookmarks (
  topic_id number(8) DEFAULT '0' NOT NULL,
  user_id number(8) DEFAULT '0' NOT NULL,
  order_id number(8) DEFAULT '0' NOT NULL
)
/

CREATE INDEX order_id on phpbb_bookmarks (order_id)
/
CREATE INDEX topic_user_id on phpbb_bookmarks (topic_id, user_id)
/

/*
 Table: phpbb_bots
*/
CREATE TABLE phpbb_bots (
  bot_id number(3) NOT NULL,
  bot_active number(1) DEFAULT '1' NOT NULL,
  bot_name varchar2(255) DEFAULT '',
  user_id number(8) DEFAULT '0' NOT NULL,
  bot_agent varchar2(255) DEFAULT '',
  bot_ip varchar2(255) DEFAULT '',
  CONSTRAINT pk_phpbb_bots PRIMARY KEY (bot_id)
)
/

CREATE SEQUENCE sq_phpbb_bots_bot_id
/

CREATE OR REPLACE TRIGGER ai_phpbb_bots_bot_id
BEFORE INSERT ON phpbb_bots
FOR EACH ROW WHEN (
 new.bot_id IS NULL OR new.bot_id = 0
)
BEGIN
 SELECT sq_phpbb_bots_bot_id.nextval
 INTO :new.bot_id
 FROM dual;
END;
/

CREATE INDEX bot_active on phpbb_bots (bot_active)
/

/*
 Table: phpbb_cache
*/
CREATE TABLE phpbb_cache (
  var_name varchar2(255) DEFAULT '',
  var_expires number(10) DEFAULT '0' NOT NULL,
  var_data clob,
  CONSTRAINT pk_phpbb_cache PRIMARY KEY (var_name)
)
/

/*
 Table: phpbb_config
*/
CREATE TABLE phpbb_config (
  config_name varchar2(255),
  config_value varchar2(255),
  is_dynamic number(1) DEFAULT '0' NOT NULL,
  CONSTRAINT pk_phpbb_config PRIMARY KEY (config_name)
)
/

CREATE INDEX is_dynamic on phpbb_config (is_dynamic)
/

/*
 Table: phpbb_confirm
*/
CREATE TABLE phpbb_confirm (
  confirm_id varchar2(32) DEFAULT '',
  session_id varchar2(32) DEFAULT '',
  confirm_type number(3) DEFAULT '0' NOT NULL,
  code varchar2(8) DEFAULT '',
  CONSTRAINT pk_phpbb_confirm PRIMARY KEY (session_id, confirm_id)
)
/

/*
 Table: phpbb_disallow
*/
CREATE TABLE phpbb_disallow (
  disallow_id number(8) NOT NULL,
  disallow_username varchar2(30) DEFAULT '',
  CONSTRAINT pk_phpbb_disallow PRIMARY KEY (disallow_id)
)
/

CREATE SEQUENCE sq_phpbb_disallow_disallow_id
/

CREATE OR REPLACE TRIGGER ai_phpbb_disallow_disallow_id
BEFORE INSERT ON phpbb_disallow
FOR EACH ROW WHEN (
 new.disallow_id IS NULL OR new.disallow_id = 0
)
BEGIN
 SELECT sq_phpbb_disallow_disallow_id.nextval
 INTO :new.disallow_id
 FROM dual;
END;
/

/*
 Table: phpbb_drafts
*/
CREATE TABLE phpbb_drafts (
  draft_id number(8) NOT NULL,
  user_id number(8) DEFAULT '0' NOT NULL,
  topic_id number(8) DEFAULT '0' NOT NULL,
  forum_id number(8) DEFAULT '0' NOT NULL,
  save_time number(11) DEFAULT '0' NOT NULL,
  draft_subject varchar2(60),
  draft_message clob DEFAULT '',
  CONSTRAINT pk_phpbb_drafts PRIMARY KEY (draft_id)
)
/

CREATE SEQUENCE sq_phpbb_drafts_draft_id
/

CREATE OR REPLACE TRIGGER ai_phpbb_drafts_draft_id
BEFORE INSERT ON phpbb_drafts
FOR EACH ROW WHEN (
 new.draft_id IS NULL OR new.draft_id = 0
)
BEGIN
 SELECT sq_phpbb_drafts_draft_id.nextval
 INTO :new.draft_id
 FROM dual;
END;
/

CREATE INDEX save_time on phpbb_drafts (save_time)
/

/*
 Table: phpbb_extensions
*/
CREATE TABLE phpbb_extensions (
  extension_id number(8) NOT NULL,
  group_id number(8) DEFAULT '0' NOT NULL,
  extension varchar2(100) DEFAULT '',
  CONSTRAINT pk_phpbb_extensions PRIMARY KEY (extension_id)
)
/

CREATE SEQUENCE sq_phpbb_extensions_extension_
/

CREATE OR REPLACE TRIGGER ai_phpbb_extensions_extension_
BEFORE INSERT ON phpbb_extensions
FOR EACH ROW WHEN (
 new.extension_id IS NULL OR new.extension_id = 0
)
BEGIN
 SELECT sq_phpbb_extensions_extension_.nextval
 INTO :new.extension_id
 FROM dual;
END;
/

/*
 Table: phpbb_extension_groups
*/
CREATE TABLE phpbb_extension_groups (
  group_id number(8) NOT NULL,
  group_name varchar2(20),
  cat_id number(2) DEFAULT '0' NOT NULL,
  allow_group number(1) DEFAULT '0' NOT NULL,
  download_mode number(1) DEFAULT '1' NOT NULL,
  upload_icon varchar2(100) DEFAULT '',
  max_filesize number(20) DEFAULT '0' NOT NULL,
  allowed_forums clob,
  allow_in_pm number(1) DEFAULT '0' NOT NULL,
  CONSTRAINT pk_phpbb_extension_groups PRIMARY KEY (group_id)
)
/

CREATE SEQUENCE sq_phpbb_extension_groups_grou
/

CREATE OR REPLACE TRIGGER ai_phpbb_extension_groups_grou
BEFORE INSERT ON phpbb_extension_groups
FOR EACH ROW WHEN (
 new.group_id IS NULL OR new.group_id = 0
)
BEGIN
 SELECT sq_phpbb_extension_groups_grou.nextval
 INTO :new.group_id
 FROM dual;
END;
/

/*
 Table: phpbb_forums
*/
CREATE TABLE phpbb_forums (
  forum_id number(5) NOT NULL,
  parent_id number(5) NOT NULL,
  left_id number(5) NOT NULL,
  right_id number(5) NOT NULL,
  forum_parents clob,
  forum_name varchar2(150),
  forum_desc clob,
  forum_link varchar2(200) DEFAULT '',
  forum_password varchar2(32) DEFAULT '',
  forum_style number(4),
  forum_image varchar2(50) DEFAULT '',
  forum_rules clob DEFAULT '',
  forum_rules_link varchar2(200) DEFAULT '',
  forum_rules_flags number(4) DEFAULT '0' NOT NULL,
  forum_rules_bbcode_bitfield number(11) DEFAULT '0' NOT NULL,
  forum_rules_bbcode_uid varchar2(5) DEFAULT '',
  forum_topics_per_page number(4) DEFAULT '0' NOT NULL,
  forum_type number(4) DEFAULT '0' NOT NULL,
  forum_status number(4) DEFAULT '0' NOT NULL,
  forum_posts number(8) DEFAULT '0' NOT NULL,
  forum_topics number(8) DEFAULT '0' NOT NULL,
  forum_topics_real number(8) DEFAULT '0' NOT NULL,
  forum_last_post_id number(8) DEFAULT '0' NOT NULL,
  forum_last_poster_id number(8) DEFAULT '0' NOT NULL,
  forum_last_post_time number(11) DEFAULT '0' NOT NULL,
  forum_last_poster_name varchar2(30),
  forum_flags number(4) DEFAULT '0' NOT NULL,
  display_on_index number(1) DEFAULT '1' NOT NULL,
  enable_indexing number(1) DEFAULT '1' NOT NULL,
  enable_icons number(1) DEFAULT '1' NOT NULL,
  enable_prune number(1) DEFAULT '0' NOT NULL,
  prune_next number(11),
  prune_days number(4) NOT NULL,
  prune_viewed number(4) NOT NULL,
  prune_freq number(4) DEFAULT '0' NOT NULL,
  CONSTRAINT pk_phpbb_forums PRIMARY KEY (forum_id)
)
/

CREATE SEQUENCE sq_phpbb_forums_forum_id
/

CREATE OR REPLACE TRIGGER ai_phpbb_forums_forum_id
BEFORE INSERT ON phpbb_forums
FOR EACH ROW WHEN (
 new.forum_id IS NULL OR new.forum_id = 0
)
BEGIN
 SELECT sq_phpbb_forums_forum_id.nextval
 INTO :new.forum_id
 FROM dual;
END;
/

CREATE INDEX left_right_id on phpbb_forums (left_id, right_id)
/
CREATE INDEX forum_last_post_id on phpbb_forums (forum_last_post_id)
/

/*
 Table: phpbb_forum_access
*/
CREATE TABLE phpbb_forum_access (
  forum_id number(8) DEFAULT '0' NOT NULL,
  user_id number(8) DEFAULT '0' NOT NULL,
  session_id varchar2(32) DEFAULT '',
  CONSTRAINT pk_phpbb_forum_access PRIMARY KEY (forum_id, user_id, session_id)
)
/

/*
 Table: phpbb_forums_marking
*/
CREATE TABLE phpbb_forums_marking (
  user_id number(9) DEFAULT '0' NOT NULL,
  forum_id number(9) DEFAULT '0' NOT NULL,
  mark_time number(11) DEFAULT '0' NOT NULL,
  CONSTRAINT pk_phpbb_forums_marking PRIMARY KEY (user_id, forum_id)
)
/

/*
 Table: phpbb_forums_watch
*/
CREATE TABLE phpbb_forums_watch (
  forum_id number(5) DEFAULT '0' NOT NULL,
  user_id number(8) DEFAULT '0' NOT NULL,
  notify_status number(1) DEFAULT '0' NOT NULL
)
/

CREATE INDEX forum_id on phpbb_forums_watch (forum_id)
/
CREATE INDEX user_id02 on phpbb_forums_watch (user_id)
/
CREATE INDEX notify_status on phpbb_forums_watch (notify_status)
/

/*
 Table: phpbb_groups
*/
CREATE TABLE phpbb_groups (
  group_id number(8) NOT NULL,
  group_type number(4) DEFAULT '1' NOT NULL,
  group_name varchar2(40) DEFAULT '',
  group_display number(1) DEFAULT '0' NOT NULL,
  group_avatar varchar2(100) DEFAULT '',
  group_avatar_type number(4) DEFAULT '0' NOT NULL,
  group_avatar_width number(4) DEFAULT '0' NOT NULL,
  group_avatar_height number(4) DEFAULT '0' NOT NULL,
  group_rank number(5) DEFAULT '1' NOT NULL,
  group_colour varchar2(6) DEFAULT '',
  group_sig_chars number(8) DEFAULT '0' NOT NULL,
  group_receive_pm number(1) DEFAULT '0' NOT NULL,
  group_message_limit number(8) DEFAULT '0' NOT NULL,
  group_chgpass number(6) DEFAULT '0' NOT NULL,
  group_description varchar2(255) DEFAULT '',
  group_legend number(1) DEFAULT '1' NOT NULL,
  CONSTRAINT pk_phpbb_groups PRIMARY KEY (group_id)
)
/

CREATE SEQUENCE sq_phpbb_groups_group_id
/

CREATE OR REPLACE TRIGGER ai_phpbb_groups_group_id
BEFORE INSERT ON phpbb_groups
FOR EACH ROW WHEN (
 new.group_id IS NULL OR new.group_id = 0
)
BEGIN
 SELECT sq_phpbb_groups_group_id.nextval
 INTO :new.group_id
 FROM dual;
END;
/

CREATE INDEX group_legend on phpbb_groups (group_legend)
/

/*
 Table: phpbb_icons
*/
CREATE TABLE phpbb_icons (
  icons_id number(4) NOT NULL,
  icons_url varchar2(50),
  icons_width number(4) NOT NULL,
  icons_height number(4) NOT NULL,
  icons_order number(4) NOT NULL,
  display_on_posting number(1) DEFAULT '1' NOT NULL,
  CONSTRAINT pk_phpbb_icons PRIMARY KEY (icons_id)
)
/

CREATE SEQUENCE sq_phpbb_icons_icons_id
/

CREATE OR REPLACE TRIGGER ai_phpbb_icons_icons_id
BEFORE INSERT ON phpbb_icons
FOR EACH ROW WHEN (
 new.icons_id IS NULL OR new.icons_id = 0
)
BEGIN
 SELECT sq_phpbb_icons_icons_id.nextval
 INTO :new.icons_id
 FROM dual;
END;
/

/*
 Table: phpbb_lang
*/
CREATE TABLE phpbb_lang (
  lang_id number(4) NOT NULL,
  lang_iso varchar2(5),
  lang_dir varchar2(30),
  lang_english_name varchar2(30),
  lang_local_name varchar2(100),
  lang_author varchar2(100),
  CONSTRAINT pk_phpbb_lang PRIMARY KEY (lang_id)
)
/

CREATE SEQUENCE sq_phpbb_lang_lang_id
/

CREATE OR REPLACE TRIGGER ai_phpbb_lang_lang_id
BEFORE INSERT ON phpbb_lang
FOR EACH ROW WHEN (
 new.lang_id IS NULL OR new.lang_id = 0
)
BEGIN
 SELECT sq_phpbb_lang_lang_id.nextval
 INTO :new.lang_id
 FROM dual;
END;
/

/*
 Table: phpbb_log
*/
CREATE TABLE phpbb_log (
  log_id number(8) NOT NULL,
  log_type number(4) DEFAULT '0' NOT NULL,
  user_id number(8) DEFAULT '0' NOT NULL,
  forum_id number(8) DEFAULT '0' NOT NULL,
  topic_id number(8) DEFAULT '0' NOT NULL,
  reportee_id number(8) DEFAULT '0' NOT NULL,
  log_ip varchar2(40),
  log_time number(11) NOT NULL,
  log_operation clob,
  log_data clob,
  CONSTRAINT pk_phpbb_log PRIMARY KEY (log_id)
)
/

CREATE SEQUENCE sq_phpbb_log_log_id
/

CREATE OR REPLACE TRIGGER ai_phpbb_log_log_id
BEFORE INSERT ON phpbb_log
FOR EACH ROW WHEN (
 new.log_id IS NULL OR new.log_id = 0
)
BEGIN
 SELECT sq_phpbb_log_log_id.nextval
 INTO :new.log_id
 FROM dual;
END;
/

CREATE INDEX log_type on phpbb_log (log_type)
/
CREATE INDEX forum_id02 on phpbb_log (forum_id)
/
CREATE INDEX topic_id02 on phpbb_log (topic_id)
/
CREATE INDEX reportee_id on phpbb_log (reportee_id)
/
CREATE INDEX user_id03 on phpbb_log (user_id)
/

/*
 Table: phpbb_moderator_cache
*/
CREATE TABLE phpbb_moderator_cache (
  forum_id number(8) NOT NULL,
  user_id number(8) DEFAULT '0' NOT NULL,
  username varchar2(30) DEFAULT '',
  group_id number(8) DEFAULT '0' NOT NULL,
  groupname varchar2(30) DEFAULT '',
  display_on_index number(1) DEFAULT '1' NOT NULL
)
/

CREATE INDEX display_on_index on phpbb_moderator_cache (display_on_index)
/
CREATE INDEX forum_id03 on phpbb_moderator_cache (forum_id)
/

/*
 Table: phpbb_modules
*/
CREATE TABLE phpbb_modules (
  module_id number(8) NOT NULL,
  module_enabled number(1) DEFAULT '1' NOT NULL,
  module_display number(1) DEFAULT '1' NOT NULL,
  module_name varchar2(20) DEFAULT '' NOT NULL,
  module_class varchar2(4) DEFAULT '' NOT NULL,
  parent_id number(5) DEFAULT '0' NOT NULL,
  left_id number(5) DEFAULT '0' NOT NULL,
  right_id number(5) DEFAULT '0' NOT NULL,
  module_langname varchar2(50) DEFAULT '' NOT NULL,
  module_mode varchar2(255) DEFAULT '' NOT NULL,
  module_auth varchar2(255) DEFAULT '' NOT NULL,
  CONSTRAINT pk_phpbb_modules PRIMARY KEY (module_id)
)
/

CREATE SEQUENCE sq_phpbb_modules_module_id
/

CREATE OR REPLACE TRIGGER ai_phpbb_modules_module_id
BEFORE INSERT ON phpbb_modules
FOR EACH ROW WHEN (
 new.module_id IS NULL OR new.module_id = 0
)
BEGIN
 SELECT sq_phpbb_modules_module_id.nextval
 INTO :new.module_id
 FROM dual;
END;
/

CREATE INDEX module_enabled on phpbb_modules (module_enabled)
/
CREATE INDEX module_left_id on phpbb_modules (left_id)
/

/*
 Table: phpbb_poll_results
*/
CREATE TABLE phpbb_poll_results (
  poll_option_id number(4) DEFAULT '0' NOT NULL,
  topic_id number(8) NOT NULL,
  poll_option_text varchar2(255),
  poll_option_total number(8) DEFAULT '0' NOT NULL
)
/

CREATE INDEX poll_option_id on phpbb_poll_results (poll_option_id)
/
CREATE INDEX topic_id03 on phpbb_poll_results (topic_id)
/

/*
 Table: phpbb_poll_voters
*/
CREATE TABLE phpbb_poll_voters (
  topic_id number(8) DEFAULT '0' NOT NULL,
  poll_option_id number(4) DEFAULT '0' NOT NULL,
  vote_user_id number(8) DEFAULT '0' NOT NULL,
  vote_user_ip varchar2(40)
)
/

CREATE INDEX topic_id04 on phpbb_poll_voters (topic_id)
/
CREATE INDEX vote_user_id on phpbb_poll_voters (vote_user_id)
/
CREATE INDEX vote_user_ip on phpbb_poll_voters (vote_user_ip)
/

/*
 Table: phpbb_posts
*/
CREATE TABLE phpbb_posts (
  post_id number(8) NOT NULL,
  topic_id number(8) DEFAULT '0' NOT NULL,
  forum_id number(5) DEFAULT '0' NOT NULL,
  poster_id number(8) DEFAULT '0' NOT NULL,
  icon_id number(4) DEFAULT '1' NOT NULL,
  poster_ip varchar2(40),
  post_time number(11) DEFAULT '0' NOT NULL,
  post_approved number(1) DEFAULT '1' NOT NULL,
  post_reported number(1) DEFAULT '0' NOT NULL,
  enable_bbcode number(1) DEFAULT '1' NOT NULL,
  enable_smilies number(1) DEFAULT '1' NOT NULL,
  enable_magic_url number(1) DEFAULT '1' NOT NULL,
  enable_sig number(1) DEFAULT '1' NOT NULL,
  post_username varchar2(30),
  post_subject varchar2(60),
  post_text clob,
  post_checksum varchar2(32),
  post_encoding varchar2(11) DEFAULT 'iso-8859-1',
  post_attachment number(1) DEFAULT '0' NOT NULL,
  bbcode_bitfield number(11) DEFAULT '0' NOT NULL,
  bbcode_uid varchar2(5) DEFAULT '',
  post_edit_time number(11) DEFAULT '0' NOT NULL,
  post_edit_reason varchar2(100),
  post_edit_user number(8) DEFAULT '0' NOT NULL,
  post_edit_count number(5) DEFAULT '0' NOT NULL,
  post_edit_locked number(1) DEFAULT '0' NOT NULL,
  CONSTRAINT pk_phpbb_posts PRIMARY KEY (post_id)
)
/

CREATE SEQUENCE sq_phpbb_posts_post_id
/

CREATE OR REPLACE TRIGGER ai_phpbb_posts_post_id
BEFORE INSERT ON phpbb_posts
FOR EACH ROW WHEN (
 new.post_id IS NULL OR new.post_id = 0
)
BEGIN
 SELECT sq_phpbb_posts_post_id.nextval
 INTO :new.post_id
 FROM dual;
END;
/

CREATE INDEX forum_id04 on phpbb_posts (forum_id)
/
CREATE INDEX topic_id05 on phpbb_posts (topic_id)
/
CREATE INDEX poster_ip on phpbb_posts (poster_ip)
/
CREATE INDEX poster_id02 on phpbb_posts (poster_id)
/
CREATE INDEX post_approved on phpbb_posts (post_approved)
/
CREATE INDEX post_time on phpbb_posts (post_time)
/

/*
 Table: phpbb_privmsgs
*/
CREATE TABLE phpbb_privmsgs (
  msg_id number(8) NOT NULL,
  root_level number(8) DEFAULT '0' NOT NULL,
  author_id number(8) DEFAULT '0' NOT NULL,
  icon_id number(4) DEFAULT '1' NOT NULL,
  author_ip varchar2(40) DEFAULT '',
  message_time number(11) DEFAULT '0' NOT NULL,
  enable_bbcode number(1) DEFAULT '1' NOT NULL,
  enable_smilies number(1) DEFAULT '1' NOT NULL,
  enable_magic_url number(1) DEFAULT '1' NOT NULL,
  enable_sig number(1) DEFAULT '1' NOT NULL,
  message_subject varchar2(60),
  message_text clob,
  message_edit_reason varchar2(100),
  message_edit_user number(8) DEFAULT '0' NOT NULL,
  message_checksum varchar2(32) DEFAULT '',
  message_encoding varchar2(11) DEFAULT 'iso-8859-1',
  message_attachment number(1) DEFAULT '0' NOT NULL,
  bbcode_bitfield number(11) DEFAULT '0' NOT NULL,
  bbcode_uid varchar2(5) DEFAULT '',
  message_edit_time number(11) DEFAULT '0' NOT NULL,
  message_edit_count number(5) DEFAULT '0' NOT NULL,
  to_address clob,
  bcc_address clob,
  CONSTRAINT pk_phpbb_privmsgs PRIMARY KEY (msg_id)
)
/

CREATE SEQUENCE sq_phpbb_privmsgs_msg_id
/

CREATE OR REPLACE TRIGGER ai_phpbb_privmsgs_msg_id
BEFORE INSERT ON phpbb_privmsgs
FOR EACH ROW WHEN (
 new.msg_id IS NULL OR new.msg_id = 0
)
BEGIN
 SELECT sq_phpbb_privmsgs_msg_id.nextval
 INTO :new.msg_id
 FROM dual;
END;
/

CREATE INDEX author_ip on phpbb_privmsgs (author_ip)
/
CREATE INDEX message_time on phpbb_privmsgs (message_time)
/
CREATE INDEX author_id on phpbb_privmsgs (author_id)
/
CREATE INDEX root_level on phpbb_privmsgs (root_level)
/

/*
 Table: phpbb_privmsgs_folder
*/
CREATE TABLE phpbb_privmsgs_folder (
  folder_id number(8) NOT NULL,
  user_id number(8) DEFAULT '0' NOT NULL,
  folder_name varchar2(40) DEFAULT '',
  pm_count number(8) DEFAULT '0' NOT NULL,
  CONSTRAINT pk_phpbb_privmsgs_folder PRIMARY KEY (folder_id)
)
/

CREATE SEQUENCE sq_phpbb_privmsgs_folder_folde
/

CREATE OR REPLACE TRIGGER ai_phpbb_privmsgs_folder_folde
BEFORE INSERT ON phpbb_privmsgs_folder
FOR EACH ROW WHEN (
 new.folder_id IS NULL OR new.folder_id = 0
)
BEGIN
 SELECT sq_phpbb_privmsgs_folder_folde.nextval
 INTO :new.folder_id
 FROM dual;
END;
/

CREATE INDEX user_id04 on phpbb_privmsgs_folder (user_id)
/

/*
 Table: phpbb_privmsgs_rules
*/
CREATE TABLE phpbb_privmsgs_rules (
  rule_id number(8) NOT NULL,
  user_id number(8) DEFAULT '0' NOT NULL,
  rule_check number(4) DEFAULT '0' NOT NULL,
  rule_connection number(4) DEFAULT '0' NOT NULL,
  rule_string varchar2(255) DEFAULT '',
  rule_user_id number(8) DEFAULT '0' NOT NULL,
  rule_group_id number(8) DEFAULT '0' NOT NULL,
  rule_action number(4) DEFAULT '0' NOT NULL,
  rule_folder_id number(8) DEFAULT '0' NOT NULL,
  CONSTRAINT pk_phpbb_privmsgs_rules PRIMARY KEY (rule_id)
)
/

CREATE SEQUENCE sq_phpbb_privmsgs_rules_rule_i
/

CREATE OR REPLACE TRIGGER ai_phpbb_privmsgs_rules_rule_i
BEFORE INSERT ON phpbb_privmsgs_rules
FOR EACH ROW WHEN (
 new.rule_id IS NULL OR new.rule_id = 0
)
BEGIN
 SELECT sq_phpbb_privmsgs_rules_rule_i.nextval
 INTO :new.rule_id
 FROM dual;
END;
/

/*
 Table: phpbb_privmsgs_to
*/
CREATE TABLE phpbb_privmsgs_to (
  msg_id number(8) DEFAULT '0' NOT NULL,
  user_id number(8) DEFAULT '0' NOT NULL,
  author_id number(8) DEFAULT '0' NOT NULL,
  deleted number(1) DEFAULT '0' NOT NULL,
  new number(1) DEFAULT '1' NOT NULL,
  unread number(1) DEFAULT '1' NOT NULL,
  replied number(1) DEFAULT '0' NOT NULL,
  marked number(1) DEFAULT '0' NOT NULL,
  forwarded number(1) DEFAULT '0' NOT NULL,
  folder_id number(10) DEFAULT '0' NOT NULL
)
/

CREATE INDEX msg_id on phpbb_privmsgs_to (msg_id)
/
CREATE INDEX user_id05 on phpbb_privmsgs_to (user_id, folder_id)
/

/*
 Table: phpbb_profile_fields
*/
CREATE TABLE phpbb_profile_fields (
  field_id number(8) NOT NULL,
  field_name varchar2(50) DEFAULT '',
  field_desc varchar2(255) DEFAULT '',
  field_type number(8) NOT NULL,
  field_ident varchar2(20) DEFAULT '',
  field_length varchar2(20) DEFAULT '',
  field_minlen varchar2(255) DEFAULT '',
  field_maxlen varchar2(255) DEFAULT '',
  field_novalue varchar2(255) DEFAULT '',
  field_default_value varchar2(255) DEFAULT '0',
  field_validation varchar2(20) DEFAULT '',
  field_required number(1) DEFAULT '0' NOT NULL,
  field_show_on_reg number(1) DEFAULT '0' NOT NULL,
  field_hide number(1) DEFAULT '0' NOT NULL,
  field_no_view number(1) DEFAULT '0' NOT NULL,
  field_active number(1) DEFAULT '0' NOT NULL,
  field_order number(4) DEFAULT '0' NOT NULL,
  CONSTRAINT pk_phpbb_profile_fields PRIMARY KEY (field_id)
)
/

CREATE SEQUENCE sq_phpbb_profile_fields_field_
/

CREATE OR REPLACE TRIGGER ai_phpbb_profile_fields_field_
BEFORE INSERT ON phpbb_profile_fields
FOR EACH ROW WHEN (
 new.field_id IS NULL OR new.field_id = 0
)
BEGIN
 SELECT sq_phpbb_profile_fields_field_.nextval
 INTO :new.field_id
 FROM dual;
END;
/

CREATE INDEX field_type on phpbb_profile_fields (field_type)
/
CREATE INDEX field_order on phpbb_profile_fields (field_order)
/

/*
 Table: phpbb_profile_fields_data
*/
CREATE TABLE phpbb_profile_fields_data (
  user_id number(8) DEFAULT '0' NOT NULL,
  CONSTRAINT pk_phpbb_profile_fields_data PRIMARY KEY (user_id)
)
/

/*
 Table: phpbb_profile_fields_lang
*/
CREATE TABLE phpbb_profile_fields_lang (
  field_id number(8) DEFAULT '0' NOT NULL,
  lang_id number(8) DEFAULT '0' NOT NULL,
  option_id number(8) DEFAULT '0' NOT NULL,
  field_type number(4) DEFAULT '0' NOT NULL,
  value varchar2(255) DEFAULT '',
  CONSTRAINT pk_phpbb_profile_fields_lang PRIMARY KEY (field_id, lang_id, option_id)
)
/

/*
 Table: phpbb_profile_lang
*/
CREATE TABLE phpbb_profile_lang (
  field_id number(8) DEFAULT '0' NOT NULL,
  lang_id number(4) DEFAULT '0' NOT NULL,
  lang_name varchar2(255) DEFAULT '',
  lang_explain clob,
  lang_default_value varchar2(255) DEFAULT '',
  CONSTRAINT pk_phpbb_profile_lang PRIMARY KEY (field_id, lang_id)
)
/

/*
 Table: phpbb_ranks
*/
CREATE TABLE phpbb_ranks (
  rank_id number(5) NOT NULL,
  rank_title varchar2(50),
  rank_min number(8) DEFAULT '0' NOT NULL,
  rank_special number(1) DEFAULT '0',
  rank_image varchar2(100),
  CONSTRAINT pk_phpbb_ranks PRIMARY KEY (rank_id)
)
/

CREATE SEQUENCE sq_phpbb_ranks_rank_id
/

CREATE OR REPLACE TRIGGER ai_phpbb_ranks_rank_id
BEFORE INSERT ON phpbb_ranks
FOR EACH ROW WHEN (
 new.rank_id IS NULL OR new.rank_id = 0
)
BEGIN
 SELECT sq_phpbb_ranks_rank_id.nextval
 INTO :new.rank_id
 FROM dual;
END;
/

/*
 Table: phpbb_reports_reasons
*/
CREATE TABLE phpbb_reports_reasons (
  reason_id number(6) NOT NULL,
  reason_title varchar2(255) DEFAULT '',
  reason_description clob,
  reason_order number(4) DEFAULT '0' NOT NULL,
  CONSTRAINT pk_phpbb_reports_reasons PRIMARY KEY (reason_id)
)
/

CREATE SEQUENCE sq_phpbb_reports_reasons_reaso
/

CREATE OR REPLACE TRIGGER ai_phpbb_reports_reasons_reaso
BEFORE INSERT ON phpbb_reports_reasons
FOR EACH ROW WHEN (
 new.reason_id IS NULL OR new.reason_id = 0
)
BEGIN
 SELECT sq_phpbb_reports_reasons_reaso.nextval
 INTO :new.reason_id
 FROM dual;
END;
/

/*
 Table: phpbb_reports
*/
CREATE TABLE phpbb_reports (
  report_id number(5) NOT NULL,
  reason_id number(5) DEFAULT '0' NOT NULL,
  post_id number(8) DEFAULT '0' NOT NULL,
  user_id number(8) DEFAULT '0' NOT NULL,
  user_notify number(1) DEFAULT '0' NOT NULL,
  report_closed number(1) DEFAULT '0' NOT NULL,
  report_time number(10) DEFAULT '0' NOT NULL,
  report_text clob,
  CONSTRAINT pk_phpbb_reports PRIMARY KEY (report_id)
)
/

CREATE SEQUENCE sq_phpbb_reports_report_id
/

CREATE OR REPLACE TRIGGER ai_phpbb_reports_report_id
BEFORE INSERT ON phpbb_reports
FOR EACH ROW WHEN (
 new.report_id IS NULL OR new.report_id = 0
)
BEGIN
 SELECT sq_phpbb_reports_report_id.nextval
 INTO :new.report_id
 FROM dual;
END;
/

/*
 Table: phpbb_search_results
*/
CREATE TABLE phpbb_search_results (
  session_key varchar2(32) DEFAULT '',
  search_time number(11) DEFAULT '0' NOT NULL,
  search_keywords clob,
  search_authors clob,
  CONSTRAINT pk_phpbb_search_results PRIMARY KEY (search_key)
)
/

/*
 Table: phpbb_search_wordlist
*/
CREATE TABLE phpbb_search_wordlist (
  word_text varchar2(50) DEFAULT '',
  word_id number(8) NOT NULL,
  word_common number(1) DEFAULT '0' NOT NULL,
  CONSTRAINT pk_phpbb_search_wordlist PRIMARY KEY (word_text)
)
/

CREATE SEQUENCE sq_phpbb_search_wordlist_word_
/

CREATE OR REPLACE TRIGGER ai_phpbb_search_wordlist_word_
BEFORE INSERT ON phpbb_search_wordlist
FOR EACH ROW WHEN (
 new.word_id IS NULL OR new.word_id = 0
)
BEGIN
 SELECT sq_phpbb_search_wordlist_word_.nextval
 INTO :new.word_id
 FROM dual;
END;
/

CREATE INDEX word_id on phpbb_search_wordlist (word_id)
/

/*
 Table: phpbb_search_wordmatch
*/
CREATE TABLE phpbb_search_wordmatch (
  post_id number(8) DEFAULT '0' NOT NULL,
  word_id number(8) DEFAULT '0' NOT NULL,
  title_match number(1) DEFAULT '0' NOT NULL
)
/

CREATE INDEX word_id02 on phpbb_search_wordmatch (word_id)
/

/*
 Table: phpbb_sessions
*/
CREATE TABLE phpbb_sessions (
  session_id varchar2(32) DEFAULT '',
  session_user_id number(8) DEFAULT '0' NOT NULL,
  session_last_visit number(11) DEFAULT '0' NOT NULL,
  session_start number(11) DEFAULT '0' NOT NULL,
  session_time number(11) DEFAULT '0' NOT NULL,
  session_ip varchar2(40) DEFAULT '0',
  session_browser varchar2(150) DEFAULT '',
  session_page varchar2(100) DEFAULT '',
  session_viewonline number(1) DEFAULT '1' NOT NULL,
  session_admin number(1) DEFAULT '0' NOT NULL,
  CONSTRAINT pk_phpbb_sessions PRIMARY KEY (session_id)
)
/

CREATE INDEX session_time on phpbb_sessions (session_time)
/
CREATE INDEX session_user_id on phpbb_sessions (session_user_id)
/

/*
 Table: phpbb_sessions_keys
*/
CREATE TABLE phpbb_sessions_keys (
  key_id varchar2(32) DEFAULT '',
  user_id number(8) DEFAULT '0' NOT NULL,
  last_ip varchar2(40) DEFAULT '0',
  last_login number(11) DEFAULT '0' NOT NULL,
  CONSTRAINT pk_phpbb_sessions_keys PRIMARY KEY (key_id,user_id)
)
/

CREATE INDEX last_login on phpbb_sessions_keys (last_login)
/

/*
 Table: phpbb_sitelist
*/
CREATE TABLE phpbb_sitelist (
  site_id number(8) NOT NULL,
  site_ip varchar2(40) DEFAULT '',
  site_hostname varchar2(255) DEFAULT '',
  ip_exclude number(1) DEFAULT '0' NOT NULL,
  CONSTRAINT pk_phpbb_sitelist PRIMARY KEY (site_id)
)
/

CREATE SEQUENCE sq_phpbb_sitelist_site_id
/

CREATE OR REPLACE TRIGGER ai_phpbb_sitelist_site_id
BEFORE INSERT ON phpbb_sitelist
FOR EACH ROW WHEN (
 new.site_id IS NULL OR new.site_id = 0
)
BEGIN
 SELECT sq_phpbb_sitelist_site_id.nextval
 INTO :new.site_id
 FROM dual;
END;
/

/*
 Table: phpbb_smilies
*/
CREATE TABLE phpbb_smilies (
  smiley_id number(4) NOT NULL,
  code varchar2(10),
  emotion varchar2(50),
  smiley_url varchar2(50),
  smiley_width number(4) NOT NULL,
  smiley_height number(4) NOT NULL,
  smiley_order number(4) NOT NULL,
  display_on_posting number(1) DEFAULT '1' NOT NULL,
  CONSTRAINT pk_phpbb_smilies PRIMARY KEY (smiley_id)
)
/

CREATE SEQUENCE sq_phpbb_smilies_smiley_id
/

CREATE OR REPLACE TRIGGER ai_phpbb_smilies_smiley_id
BEFORE INSERT ON phpbb_smilies
FOR EACH ROW WHEN (
 new.smiley_id IS NULL OR new.smiley_id = 0
)
BEGIN
 SELECT sq_phpbb_smilies_smiley_id.nextval
 INTO :new.smiley_id
 FROM dual;
END;
/

/*
 Table: phpbb_styles
*/
CREATE TABLE phpbb_styles (
  style_id number(4) NOT NULL,
  style_name varchar2(30) DEFAULT '',
  style_copyright varchar2(50) DEFAULT '',
  style_active number(1) DEFAULT '1' NOT NULL,
  template_id number(4) NOT NULL,
  theme_id number(4) NOT NULL,
  imageset_id number(4) NOT NULL,
  CONSTRAINT pk_phpbb_styles PRIMARY KEY (style_id),
  CONSTRAINT u_style_name UNIQUE (style_name)
)
/

CREATE SEQUENCE sq_phpbb_styles_style_id
/

CREATE OR REPLACE TRIGGER ai_phpbb_styles_style_id
BEFORE INSERT ON phpbb_styles
FOR EACH ROW WHEN (
 new.style_id IS NULL OR new.style_id = 0
)
BEGIN
 SELECT sq_phpbb_styles_style_id.nextval
 INTO :new.style_id
 FROM dual;
END;
/

CREATE INDEX i_phpbb_styles on phpbb_styles (template_id)
/
CREATE INDEX i_phpbb_styles02 on phpbb_styles (theme_id)
/
CREATE INDEX i_phpbb_styles03 on phpbb_styles (imageset_id)
/

/*
 Table: phpbb_styles_template
*/
CREATE TABLE phpbb_styles_template (
  template_id number(4) NOT NULL,
  template_name varchar2(30),
  template_copyright varchar2(50),
  template_path varchar2(30),
  bbcode_bitfield number(11) DEFAULT '0' NOT NULL,
  template_storedb number(1) DEFAULT '0' NOT NULL,
  CONSTRAINT pk_phpbb_styles_template PRIMARY KEY (template_id),
  CONSTRAINT u_template_name UNIQUE (template_name)
)
/

CREATE SEQUENCE sq_phpbb_styles_template_templ
/

CREATE OR REPLACE TRIGGER ai_phpbb_styles_template_templ
BEFORE INSERT ON phpbb_styles_template
FOR EACH ROW WHEN (
 new.template_id IS NULL OR new.template_id = 0
)
BEGIN
 SELECT sq_phpbb_styles_template_templ.nextval
 INTO :new.template_id
 FROM dual;
END;
/

/*
 Table: phpbb_styles_template_data
*/
CREATE TABLE phpbb_styles_template_data (
  template_id number(4) NOT NULL,
  template_filename varchar2(50) DEFAULT '',
  template_included clob,
  template_mtime number(11) DEFAULT '0' NOT NULL,
  template_data clob
)
/

CREATE INDEX i_phpbb_styles_template_data on phpbb_styles_template_data (template_id)
/
CREATE INDEX i_phpbb_styles_template_data02 on phpbb_styles_template_data (template_filename)
/

/*
 Table: phpbb_styles_theme
*/
CREATE TABLE phpbb_styles_theme (
  theme_id number(4) NOT NULL,
  theme_name varchar2(30) DEFAULT '',
  theme_copyright varchar2(50) DEFAULT '',
  theme_path varchar2(30) DEFAULT '',
  theme_storedb number(1) DEFAULT '0' NOT NULL,
  theme_mtime number(11) DEFAULT '0' NOT NULL,
  theme_data clob DEFAULT '',
  CONSTRAINT pk_phpbb_styles_theme PRIMARY KEY (theme_id),
  CONSTRAINT u_theme_name UNIQUE (theme_name)
)
/

CREATE SEQUENCE sq_phpbb_styles_theme_theme_id
/

CREATE OR REPLACE TRIGGER ai_phpbb_styles_theme_theme_id
BEFORE INSERT ON phpbb_styles_theme
FOR EACH ROW WHEN (
 new.theme_id IS NULL OR new.theme_id = 0
)
BEGIN
 SELECT sq_phpbb_styles_theme_theme_id.nextval
 INTO :new.theme_id
 FROM dual;
END;
/

/*
 Table: phpbb_styles_imageset
*/
CREATE TABLE phpbb_styles_imageset (
  imageset_id number(4) NOT NULL,
  imageset_name varchar2(30) DEFAULT '',
  imageset_copyright varchar2(50) DEFAULT '',
  imageset_path varchar2(30) DEFAULT '',
  site_logo varchar2(200) DEFAULT '',
  btn_post varchar2(200) DEFAULT '',
  btn_post_pm varchar2(200) DEFAULT '',
  btn_reply varchar2(200) DEFAULT '',
  btn_reply_pm varchar2(200) DEFAULT '',
  btn_locked varchar2(200) DEFAULT '',
  btn_profile varchar2(200) DEFAULT '',
  btn_pm varchar2(200) DEFAULT '',
  btn_delete varchar2(200) DEFAULT '',
  btn_info varchar2(200) DEFAULT '',
  btn_quote varchar2(200) DEFAULT '',
  btn_search varchar2(200) DEFAULT '',
  btn_edit varchar2(200) DEFAULT '',
  btn_report varchar2(200) DEFAULT '',
  btn_email varchar2(200) DEFAULT '',
  btn_www varchar2(200) DEFAULT '',
  btn_icq varchar2(200) DEFAULT '',
  btn_aim varchar2(200) DEFAULT '',
  btn_yim varchar2(200) DEFAULT '',
  btn_msnm varchar2(200) DEFAULT '',
  btn_jabber varchar2(200) DEFAULT '',
  btn_online varchar2(200) DEFAULT '',
  btn_offline varchar2(200) DEFAULT '',
  btn_friend varchar2(200) DEFAULT '',
  btn_foe varchar2(200) DEFAULT '',
  icon_unapproved varchar2(200) DEFAULT '',
  icon_reported varchar2(200) DEFAULT '',
  icon_attach varchar2(200) DEFAULT '',
  icon_post varchar2(200) DEFAULT '',
  icon_post_new varchar2(200) DEFAULT '',
  icon_post_latest varchar2(200) DEFAULT '',
  icon_post_newest varchar2(200) DEFAULT '',
  forum varchar2(200) DEFAULT '',
  forum_new varchar2(200) DEFAULT '',
  forum_locked varchar2(200) DEFAULT '',
  forum_link varchar2(200) DEFAULT '',
  sub_forum varchar2(200) DEFAULT '',
  sub_forum_new varchar2(200) DEFAULT '',
  folder varchar2(200) DEFAULT '',
  folder_moved varchar2(200) DEFAULT '',
  folder_posted varchar2(200) DEFAULT '',
  folder_new varchar2(200) DEFAULT '',
  folder_new_posted varchar2(200) DEFAULT '',
  folder_hot varchar2(200) DEFAULT '',
  folder_hot_posted varchar2(200) DEFAULT '',
  folder_hot_new varchar2(200) DEFAULT '',
  folder_hot_new_posted varchar2(200) DEFAULT '',
  folder_locked varchar2(200) DEFAULT '',
  folder_locked_posted varchar2(200) DEFAULT '',
  folder_locked_new varchar2(200) DEFAULT '',
  folder_locked_new_posted varchar2(200) DEFAULT '',
  folder_sticky varchar2(200) DEFAULT '',
  folder_sticky_posted varchar2(200) DEFAULT '',
  folder_sticky_new varchar2(200) DEFAULT '',
  folder_sticky_new_posted varchar2(200) DEFAULT '',
  folder_announce varchar2(200) DEFAULT '',
  folder_announce_posted varchar2(200) DEFAULT '',
  folder_announce_new varchar2(200) DEFAULT '',
  folder_announce_new_posted varchar2(200) DEFAULT '',
  folder_global varchar2(200) DEFAULT '',
  folder_global_posted varchar2(200) DEFAULT '',
  folder_global_new varchar2(200) DEFAULT '',
  folder_global_new_posted varchar2(200) DEFAULT '',
  poll_left varchar2(200) DEFAULT '',
  poll_center varchar2(200) DEFAULT '',
  poll_right varchar2(200) DEFAULT '',
  attach_progress_bar varchar2(200) DEFAULT '',
  user_icon1 varchar2(200) DEFAULT '',
  user_icon2 varchar2(200) DEFAULT '',
  user_icon3 varchar2(200) DEFAULT '',
  user_icon4 varchar2(200) DEFAULT '',
  user_icon5 varchar2(200) DEFAULT '',
  user_icon6 varchar2(200) DEFAULT '',
  user_icon7 varchar2(200) DEFAULT '',
  user_icon8 varchar2(200) DEFAULT '',
  user_icon9 varchar2(200) DEFAULT '',
  user_icon10 varchar2(200) DEFAULT '',
  CONSTRAINT pk_phpbb_styles_imageset PRIMARY KEY (imageset_id),
  CONSTRAINT u_imageset_name UNIQUE (imageset_name)
)
/

CREATE SEQUENCE sq_phpbb_styles_imageset_image
/

CREATE OR REPLACE TRIGGER ai_phpbb_styles_imageset_image
BEFORE INSERT ON phpbb_styles_imageset
FOR EACH ROW WHEN (
 new.imageset_id IS NULL OR new.imageset_id = 0
)
BEGIN
 SELECT sq_phpbb_styles_imageset_image.nextval
 INTO :new.imageset_id
 FROM dual;
END;
/

/*
 Table: phpbb_topics
*/
CREATE TABLE phpbb_topics (
  topic_id number(8) NOT NULL,
  forum_id number(8) DEFAULT '0' NOT NULL,
  icon_id number(4) DEFAULT '1' NOT NULL,
  topic_attachment number(1) DEFAULT '0' NOT NULL,
  topic_approved number(1) DEFAULT '1' NOT NULL,
  topic_reported number(1) DEFAULT '0' NOT NULL,
  topic_title varchar2(60),
  topic_poster number(8) DEFAULT '0' NOT NULL,
  topic_time number(11) DEFAULT '0' NOT NULL,
  topic_time_limit number(11) DEFAULT '0' NOT NULL,
  topic_views number(8) DEFAULT '0' NOT NULL,
  topic_replies number(8) DEFAULT '0' NOT NULL,
  topic_replies_real number(8) DEFAULT '0' NOT NULL,
  topic_status number(3) DEFAULT '0' NOT NULL,
  topic_type number(3) DEFAULT '0' NOT NULL,
  topic_first_post_id number(8) DEFAULT '0' NOT NULL,
  topic_first_poster_name varchar2(30),
  topic_last_post_id number(8) DEFAULT '0' NOT NULL,
  topic_last_poster_id number(8) DEFAULT '0' NOT NULL,
  topic_last_poster_name varchar2(30),
  topic_last_post_time number(11) DEFAULT '0' NOT NULL,
  topic_last_view_time number(11) DEFAULT '0' NOT NULL,
  topic_moved_id number(8) DEFAULT '0' NOT NULL,
  topic_bumped number(1) DEFAULT '0' NOT NULL,
  topic_bumper number(8) DEFAULT '0' NOT NULL,
  poll_title varchar2(255),
  poll_start number(11) DEFAULT '0' NOT NULL,
  poll_length number(11) DEFAULT '0' NOT NULL,
  poll_max_options number(4) DEFAULT '1' NOT NULL,
  poll_last_vote number(11) DEFAULT '0',
  poll_vote_change number(1) DEFAULT '0' NOT NULL,
  CONSTRAINT pk_phpbb_topics PRIMARY KEY (topic_id)
)
/

CREATE SEQUENCE sq_phpbb_topics_topic_id
/

CREATE OR REPLACE TRIGGER ai_phpbb_topics_topic_id
BEFORE INSERT ON phpbb_topics
FOR EACH ROW WHEN (
 new.topic_id IS NULL OR new.topic_id = 0
)
BEGIN
 SELECT sq_phpbb_topics_topic_id.nextval
 INTO :new.topic_id
 FROM dual;
END;
/

CREATE INDEX forum_id05 on phpbb_topics (forum_id)
/
CREATE INDEX forum_id_type on phpbb_topics (forum_id, topic_type)
/
CREATE INDEX topic_last_post_time on phpbb_topics (topic_last_post_time)
/

/*
 Table: phpbb_topics_marking
*/
CREATE TABLE phpbb_topics_marking (
  user_id number(8) DEFAULT '0' NOT NULL,
  topic_id number(8) DEFAULT '0' NOT NULL,
  forum_id number(8) DEFAULT '0' NOT NULL,
  mark_time number(11) DEFAULT '0' NOT NULL,
  CONSTRAINT pk_phpbb_topics_marking PRIMARY KEY (user_id, topic_id)
)
/

CREATE INDEX forum_id06 on phpbb_topics_marking (forum_id)
/

/*
 Table: phpbb_topics_posted
*/
CREATE TABLE phpbb_topics_posted (
  user_id number(8) DEFAULT '0' NOT NULL,
  topic_id number(8) DEFAULT '0' NOT NULL,
  topic_posted number(4) DEFAULT '0' NOT NULL,
  CONSTRAINT pk_phpbb_topics_posted PRIMARY KEY (user_id, topic_id)
)
/

/*
 Table: phpbb_topics_watch
*/
CREATE TABLE phpbb_topics_watch (
  topic_id number(8) DEFAULT '0' NOT NULL,
  user_id number(8) DEFAULT '0' NOT NULL,
  notify_status number(1) DEFAULT '0' NOT NULL
)
/

CREATE INDEX topic_id06 on phpbb_topics_watch (topic_id)
/
CREATE INDEX user_id06 on phpbb_topics_watch (user_id)
/
CREATE INDEX notify_status02 on phpbb_topics_watch (notify_status)
/

/*
 Table: phpbb_user_group
*/
CREATE TABLE phpbb_user_group (
  group_id number(8) DEFAULT '0' NOT NULL,
  user_id number(8) DEFAULT '0' NOT NULL,
  group_leader number(1) DEFAULT '0' NOT NULL,
  user_pending number(1)
)
/

CREATE INDEX group_id02 on phpbb_user_group (group_id)
/
CREATE INDEX user_id07 on phpbb_user_group (user_id)
/
CREATE INDEX group_leader on phpbb_user_group (group_leader)
/

/*
 Table: phpbb_users
*/
CREATE TABLE phpbb_users (
  user_id number(8) NOT NULL,
  user_type number(1) DEFAULT '0' NOT NULL,
  group_id number(8) DEFAULT '3' NOT NULL,
  user_permissions clob DEFAULT '',
  user_ip varchar2(40) DEFAULT '',
  user_regdate number(11) DEFAULT '0' NOT NULL,
  username varchar2(30) DEFAULT '',
  user_password varchar2(32) DEFAULT '',
  user_passchg number(11) DEFAULT '0' NOT NULL,
  user_email varchar2(60) DEFAULT '',
  user_email_hash number(20) DEFAULT '0' NOT NULL,
  user_birthday varchar2(10) DEFAULT '',
  user_lastvisit number(11) DEFAULT '0' NOT NULL,
  user_lastmark number(11) DEFAULT '0' NOT NULL,
  user_lastpost_time number(11) DEFAULT '0' NOT NULL,
  user_lastpage varchar2(100) DEFAULT '',
  user_last_confirm_key varchar2(10) DEFAULT '',
  user_warnings number(4) DEFAULT '0' NOT NULL,
  user_last_warning number(11) DEFAULT '0' NOT NULL,
  user_login_attempts number(4) DEFAULT '0' NOT NULL,
  user_posts number(8) DEFAULT '0' NOT NULL,
  user_lang varchar2(30) DEFAULT '',
  user_timezone number(5, 2) DEFAULT '1' NOT NULL,
  user_dst number(1) DEFAULT '0' NOT NULL,
  user_dateformat varchar2(15) DEFAULT 'd M Y H:i',
  user_style number(4) DEFAULT '0' NOT NULL,
  user_rank number(11) DEFAULT '0',
  user_colour varchar2(6) DEFAULT '',
  user_new_privmsg number(4) DEFAULT '0' NOT NULL,
  user_unread_privmsg number(4) DEFAULT '0' NOT NULL,
  user_last_privmsg number(11) DEFAULT '0' NOT NULL,
  user_message_rules number(1) DEFAULT '0' NOT NULL,
  user_full_folder number(11) DEFAULT '1' NOT NULL,
  user_emailtime number(11) DEFAULT '0' NOT NULL,
  user_topic_show_days number(4) DEFAULT '0' NOT NULL,
  user_topic_sortby_type varchar2(1) DEFAULT 't',
  user_topic_sortby_dir varchar2(1) DEFAULT 'd',
  user_post_show_days number(4) DEFAULT '0' NOT NULL,
  user_post_sortby_type varchar2(1) DEFAULT 't',
  user_post_sortby_dir varchar2(1) DEFAULT 'a',
  user_notify number(1) DEFAULT '0' NOT NULL,
  user_notify_pm number(1) DEFAULT '1' NOT NULL,
  user_notify_type number(4) DEFAULT '0' NOT NULL,
  user_allow_pm number(1) DEFAULT '1' NOT NULL,
  user_allow_email number(1) DEFAULT '1' NOT NULL,
  user_allow_viewonline number(1) DEFAULT '1' NOT NULL,
  user_allow_viewemail number(1) DEFAULT '1' NOT NULL,
  user_allow_massemail number(1) DEFAULT '1' NOT NULL,
  user_options number(11) DEFAULT '893' NOT NULL,
  user_avatar varchar2(100) DEFAULT '',
  user_avatar_type number(2) DEFAULT '0' NOT NULL,
  user_avatar_width number(4) DEFAULT '0' NOT NULL,
  user_avatar_height number(4) DEFAULT '0' NOT NULL,
  user_sig clob DEFAULT '',
  user_sig_bbcode_uid varchar2(5) DEFAULT '',
  user_sig_bbcode_bitfield number(11) DEFAULT '0' NOT NULL,
  user_from varchar2(100) DEFAULT '',
  user_icq varchar2(15) DEFAULT '',
  user_aim varchar2(255) DEFAULT '',
  user_yim varchar2(255) DEFAULT '',
  user_msnm varchar2(255) DEFAULT '',
  user_jabber varchar2(255) DEFAULT '',
  user_website varchar2(100) DEFAULT '',
  user_occ varchar2(255) DEFAULT '',
  user_interests varchar2(255) DEFAULT '',
  user_actkey varchar2(32) DEFAULT '',
  user_newpasswd varchar2(32) DEFAULT '',
  CONSTRAINT pk_phpbb_users PRIMARY KEY (user_id)
)
/

CREATE SEQUENCE sq_phpbb_users_user_id
/

CREATE OR REPLACE TRIGGER ai_phpbb_users_user_id
BEFORE INSERT ON phpbb_users
FOR EACH ROW WHEN (
 new.user_id IS NULL OR new.user_id = 0
)
BEGIN
 SELECT sq_phpbb_users_user_id.nextval
 INTO :new.user_id
 FROM dual;
END;
/

CREATE INDEX user_birthday on phpbb_users (user_birthday)
/
CREATE INDEX user_email_hash on phpbb_users (user_email_hash)
/
CREATE INDEX username on phpbb_users (username)
/

/*
 Table: phpbb_warnings
*/
CREATE TABLE phpbb_warnings (
  warning_id number(8) NOT NULL,
  user_id number(8) DEFAULT '0' NOT NULL,
  post_id number(8) DEFAULT '0' NOT NULL,
  log_id number(8) DEFAULT '0' NOT NULL,
  warning_time number(11) DEFAULT '0' NOT NULL
  CONSTRAINT pk_phpbb_warnings PRIMARY KEY (warning_id)
)
/

CREATE SEQUENCE sq_phpbb_warnings_warning_id
/

CREATE OR REPLACE TRIGGER ai_phpbb_warnings_warning_id
BEFORE INSERT ON phpbb_warnings
FOR EACH ROW WHEN (
 new.warning_id IS NULL OR new.warning_id = 0
)
BEGIN
 SELECT sq_phpbb_warnings_warning_id.nextval
 INTO :new.warning_id
 FROM dual;
END;
/

/*
 Table: phpbb_words
*/
CREATE TABLE phpbb_words (
  word_id number(8) NOT NULL,
  word varchar2(100),
  replacement varchar2(100),
  CONSTRAINT pk_phpbb_words PRIMARY KEY (word_id)
)
/

CREATE SEQUENCE sq_phpbb_words_word_id
/

CREATE OR REPLACE TRIGGER ai_phpbb_words_word_id
BEFORE INSERT ON phpbb_words
FOR EACH ROW WHEN (
 new.word_id IS NULL OR new.word_id = 0
)
BEGIN
 SELECT sq_phpbb_words_word_id.nextval
 INTO :new.word_id
 FROM dual;
END;
/

/*
 Table: phpbb_zebra
*/
CREATE TABLE phpbb_zebra (
  user_id number(8) DEFAULT '0' NOT NULL,
  zebra_id number(8) DEFAULT '0' NOT NULL,
  friend number(1) DEFAULT '0' NOT NULL,
  foe number(1) DEFAULT '0' NOT NULL
)
/

CREATE INDEX user_id08 on phpbb_zebra (user_id)
/
CREATE INDEX zebra_id on phpbb_zebra (zebra_id)
/

