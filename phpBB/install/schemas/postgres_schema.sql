/*
 PostgreSQL Schema for phpBB 3.x - (c) phpBB Group, 2005

 $Id$
*/

BEGIN;

/* Domain definition */
CREATE DOMAIN varchar_ci AS varchar(255) NOT NULL DEFAULT ''::character varying;
CREATE CAST (varchar_ci AS varchar) WITHOUT FUNCTION AS IMPLICIT;
CREATE CAST (varchar AS varchar_ci) WITHOUT FUNCTION AS IMPLICIT;
CREATE CAST (varchar_ci AS text) WITHOUT FUNCTION AS IMPLICIT;
CREATE CAST (text AS varchar_ci) WITHOUT FUNCTION AS IMPLICIT;

/* Operation Functions */
CREATE FUNCTION _varchar_ci_equal(varchar_ci, varchar_ci) RETURNS boolean AS 'SELECT LOWER($1) = LOWER($2)' LANGUAGE SQL STRICT;
CREATE FUNCTION _varchar_ci_not_equal(varchar_ci, varchar_ci) RETURNS boolean AS 'SELECT LOWER($1) != LOWER($2)' LANGUAGE SQL STRICT;
CREATE FUNCTION _varchar_ci_less_than(varchar_ci, varchar_ci) RETURNS boolean AS 'SELECT LOWER($1) < LOWER($2)' LANGUAGE SQL STRICT;
CREATE FUNCTION _varchar_ci_less_equal(varchar_ci, varchar_ci) RETURNS boolean AS 'SELECT LOWER($1) <= LOWER($2)' LANGUAGE SQL STRICT;
CREATE FUNCTION _varchar_ci_greater_than(varchar_ci, varchar_ci) RETURNS boolean AS 'SELECT LOWER($1) > LOWER($2)' LANGUAGE SQL STRICT;
CREATE FUNCTION _varchar_ci_greater_equals(varchar_ci, varchar_ci) RETURNS boolean AS 'SELECT LOWER($1) >= LOWER($2)' LANGUAGE SQL STRICT;

/* Operators */
CREATE OPERATOR <(
  PROCEDURE = _varchar_ci_less_than,
  LEFTARG = varchar_ci,
  RIGHTARG = varchar_ci,
  COMMUTATOR = >,
  NEGATOR = >=,
  RESTRICT = scalarltsel,
  JOIN = scalarltjoinsel);

CREATE OPERATOR <=(
  PROCEDURE = _varchar_ci_less_equal,
  LEFTARG = varchar_ci,
  RIGHTARG = varchar_ci,
  COMMUTATOR = >=,
  NEGATOR = >,
  RESTRICT = scalarltsel,
  JOIN = scalarltjoinsel);

CREATE OPERATOR >(
  PROCEDURE = _varchar_ci_greater_than,
  LEFTARG = varchar_ci,
  RIGHTARG = varchar_ci,
  COMMUTATOR = <,
  NEGATOR = <=,
  RESTRICT = scalargtsel,
  JOIN = scalargtjoinsel);

CREATE OPERATOR >=(
  PROCEDURE = _varchar_ci_greater_equals,
  LEFTARG = varchar_ci,
  RIGHTARG = varchar_ci,
  COMMUTATOR = <=,
  NEGATOR = <,
  RESTRICT = scalargtsel,
  JOIN = scalargtjoinsel);

CREATE OPERATOR <>(
  PROCEDURE = _varchar_ci_not_equal,
  LEFTARG = varchar_ci,
  RIGHTARG = varchar_ci,
  COMMUTATOR = <>,
  NEGATOR = =,
  RESTRICT = neqsel,
  JOIN = neqjoinsel);

CREATE OPERATOR =(
  PROCEDURE = _varchar_ci_equal,
  LEFTARG = varchar_ci,
  RIGHTARG = varchar_ci,
  COMMUTATOR = =,
  NEGATOR = <>,
  RESTRICT = eqsel,
  JOIN = eqjoinsel,
  HASHES,
  MERGES,
  SORT1= <);

/* Table: phpbb_attachments */

CREATE SEQUENCE phpbb_attachments_seq;

CREATE TABLE phpbb_attachments (
  attach_id INT4 DEFAULT nextval('phpbb_attachments_seq'),
  post_msg_id INT4  DEFAULT '0' NOT NULL,
  topic_id INT4  DEFAULT '0' NOT NULL,
  in_message INT2  DEFAULT '0' NOT NULL,
  poster_id INT4  DEFAULT '0' NOT NULL,
  physical_filename varchar(255) NOT NULL,
  real_filename varchar(255) NOT NULL,
  download_count INT4  DEFAULT '0' NOT NULL,
  comment varchar(8000),
  extension varchar(100),
  mimetype varchar(100),
  filesize INT4  NOT NULL,
  filetime INT4  DEFAULT '0' NOT NULL,
  thumbnail INT2 DEFAULT '0' NOT NULL,
  PRIMARY KEY (attach_id),
  CHECK (post_msg_id>=0),
  CHECK (topic_id>=0),
  CHECK (in_message>=0),
  CHECK (poster_id>=0),
  CHECK (download_count>=0),
  CHECK (filesize>=0),
  CHECK (filetime>=0)
);

CREATE INDEX phpbb_attachments_filetime ON phpbb_attachments (filetime);
CREATE INDEX phpbb_attachments_post_msg_id ON phpbb_attachments (post_msg_id);
CREATE INDEX phpbb_attachments_topic_id ON phpbb_attachments (topic_id);
CREATE INDEX phpbb_attachments_poster_id ON phpbb_attachments (poster_id);
CREATE INDEX phpbb_attachments_physical_filename ON phpbb_attachments (physical_filename);
CREATE INDEX phpbb_attachments_filesize ON phpbb_attachments (filesize);




/* Table: phpbb_auth_groups */
CREATE TABLE phpbb_auth_groups (
  group_id INT4  DEFAULT '0' NOT NULL,
  forum_id INT4  DEFAULT '0' NOT NULL,
  auth_option_id INT4  DEFAULT '0' NOT NULL,
  auth_role_id INT4  DEFAULT '0' NOT NULL,
  auth_setting INT2 DEFAULT '0' NOT NULL
);

CREATE INDEX phpbb_auth_groups_group_id ON phpbb_auth_groups (group_id);
CREATE INDEX phpbb_auth_groups_auth_option_id ON phpbb_auth_groups (auth_option_id);


/* Table: phpbb_auth_options */
CREATE SEQUENCE phpbb_auth_options_seq;

CREATE TABLE phpbb_auth_options (
  auth_option_id INT4 DEFAULT nextval('phpbb_auth_options_seq'),
  auth_option varchar(20) NOT NULL,
  is_global INT2 DEFAULT '0' NOT NULL,
  is_local INT2 DEFAULT '0' NOT NULL,
  founder_only INT2 DEFAULT '0' NOT NULL,
  PRIMARY KEY (auth_option_id)
);

CREATE INDEX phpbb_auth_options_auth_option ON phpbb_auth_options (auth_option);




/* Table: phpbb_auth_roles */
CREATE SEQUENCE phpbb_auth_roles_seq;

CREATE TABLE phpbb_auth_roles (
  role_id INT4 DEFAULT nextval('phpbb_auth_roles_seq'),
  role_name varchar(255) DEFAULT '' NOT NULL,
  role_description varchar(8000),
  role_type varchar(10) DEFAULT '' NOT NULL,
  role_order INT2  DEFAULT '0' NOT NULL,
  PRIMARY KEY (role_id)
);

CREATE INDEX phpbb_auth_roles_role_type ON phpbb_auth_roles (role_type);
CREATE INDEX phpbb_auth_roles_role_order ON phpbb_auth_roles (role_order);




/* Table: phpbb_auth_roles_data */
CREATE TABLE phpbb_auth_roles_data (
  role_id INT4  DEFAULT '0' NOT NULL,
  auth_option_id INT4  DEFAULT '0' NOT NULL,
  auth_setting INT2  DEFAULT '0' NOT NULL,
  PRIMARY KEY  (role_id, auth_option_id)
);


/* Table: phpbb_auth_users */
CREATE TABLE phpbb_auth_users (
  user_id INT4  DEFAULT '0' NOT NULL,
  forum_id INT4  DEFAULT '0' NOT NULL,
  auth_option_id INT4  DEFAULT '0' NOT NULL,
  auth_role_id INT4  DEFAULT '0' NOT NULL,
  auth_setting INT2 DEFAULT '0' NOT NULL
);

CREATE INDEX phpbb_auth_users_user_id ON phpbb_auth_users (user_id);
CREATE INDEX phpbb_auth_users_auth_option_id ON phpbb_auth_users (auth_option_id);


/* Table: phpbb_banlist */
CREATE SEQUENCE phpbb_banlist_seq;

CREATE TABLE phpbb_banlist (
  ban_id INT4 DEFAULT nextval('phpbb_banlist_seq'),
  ban_userid INT4  DEFAULT '0' NOT NULL,
  ban_ip varchar(40) DEFAULT '' NOT NULL,
  ban_email varchar(100) DEFAULT '' NOT NULL,
  ban_start INT4 DEFAULT '0' NOT NULL,
  ban_end INT4 DEFAULT '0' NOT NULL,
  ban_exclude INT2 DEFAULT '0' NOT NULL,
  ban_reason varchar(3000),
  ban_give_reason varchar(3000),
  PRIMARY KEY (ban_id),
  CHECK (ban_userid>=0)
);




/* Table: phpbb_bbcodes */
CREATE TABLE phpbb_bbcodes (
  bbcode_id INT2  DEFAULT '0' NOT NULL,
  bbcode_tag varchar(16) DEFAULT '' NOT NULL,
  display_on_posting INT2  DEFAULT '0' NOT NULL,
  bbcode_match varchar(255) DEFAULT '' NOT NULL,
  bbcode_tpl TEXT,
  first_pass_match varchar(255) DEFAULT '' NOT NULL,
  first_pass_replace varchar(255) DEFAULT '' NOT NULL,
  second_pass_match varchar(255) DEFAULT '' NOT NULL,
  second_pass_replace TEXT,
  PRIMARY KEY (bbcode_id),
  CHECK (bbcode_id>=0)
);

CREATE INDEX phpbb_bbcodes_display_on_posting ON phpbb_bbcodes (display_on_posting);


/* Table: phpbb_bookmarks */
CREATE TABLE phpbb_bookmarks (
  topic_id INT4  DEFAULT '0' NOT NULL,
  user_id INT4  DEFAULT '0' NOT NULL,
  order_id INT4  DEFAULT '0' NOT NULL
);

CREATE INDEX phpbb_bookmarks_order_id ON phpbb_bookmarks (order_id);
CREATE INDEX phpbb_bookmarks_topic_user_id ON phpbb_bookmarks (topic_id, user_id);


/* Table: phpbb_bots */
CREATE SEQUENCE phpbb_bots_seq;

CREATE TABLE phpbb_bots (
  bot_id INT2 DEFAULT nextval('phpbb_bots_seq'),
  bot_active INT2 DEFAULT '1' NOT NULL,
  bot_name varchar(3000),
  user_id INT4  DEFAULT '0' NOT NULL,
  bot_agent varchar(255)  DEFAULT '' NOT NULL,
  bot_ip varchar(255) DEFAULT '' NOT NULL,
  PRIMARY KEY (bot_id),
  CHECK (user_id>=0)
);

CREATE INDEX phpbb_bots_bot_active ON phpbb_bots (bot_active);




/* Table: phpbb_cache */
CREATE TABLE phpbb_cache (
  var_name varchar(255) DEFAULT '' NOT NULL,
  var_expires INT4  DEFAULT '0' NOT NULL,
  var_data TEXT,
  PRIMARY KEY (var_name),
  CHECK (var_expires>=0)
);


/* Table: phpbb_config */
CREATE TABLE phpbb_config (
  config_name varchar(255) NOT NULL,
  config_value varchar(255) NOT NULL,
  is_dynamic INT2 DEFAULT '0' NOT NULL,
  PRIMARY KEY (config_name)
);

CREATE INDEX phpbb_config_is_dynamic ON phpbb_config (is_dynamic);


/* Table: phpbb_confirm */
CREATE TABLE phpbb_confirm (
  confirm_id char(32) DEFAULT '' NOT NULL,
  session_id char(32) DEFAULT '' NOT NULL,
  confirm_type INT2 DEFAULT '0' NOT NULL,
  code varchar(8) DEFAULT '' NOT NULL,
  PRIMARY KEY (session_id,confirm_id)
);


/* Table: phpbb_disallow */
CREATE SEQUENCE phpbb_disallow_seq;

CREATE TABLE phpbb_disallow (
  disallow_id INT4 DEFAULT nextval('phpbb_disallow_seq'),
  disallow_username varchar(255) DEFAULT '' NOT NULL,
  PRIMARY KEY (disallow_id)
);




/* Table: phpbb_drafts */
CREATE SEQUENCE phpbb_drafts_seq;

CREATE TABLE phpbb_drafts (
  draft_id INT4 DEFAULT nextval('phpbb_drafts_seq'),
  user_id INT4  DEFAULT '0' NOT NULL,
  topic_id INT4  DEFAULT '0' NOT NULL,
  forum_id INT4  DEFAULT '0' NOT NULL,
  save_time INT4  DEFAULT '0' NOT NULL,
  draft_subject varchar(1000),
  draft_message TEXT,
  PRIMARY KEY (draft_id),
  CHECK (user_id>=0),
  CHECK (topic_id>=0),
  CHECK (forum_id>=0),
  CHECK (save_time>=0)
);

CREATE INDEX phpbb_drafts_save_time ON phpbb_drafts (save_time);




/* Table: phpbb_extensions */
CREATE SEQUENCE phpbb_extensions_seq;

CREATE TABLE phpbb_extensions (
  extension_id INT4 DEFAULT nextval('phpbb_extensions_seq'),
  group_id INT4  DEFAULT '0' NOT NULL,
  extension varchar(100) DEFAULT '' NOT NULL,
  PRIMARY KEY (extension_id),
  CHECK (group_id>=0)
);




/* Table: phpbb_extension_groups */
CREATE SEQUENCE phpbb_extension_groups_seq;

CREATE TABLE phpbb_extension_groups (
  group_id INT4 DEFAULT nextval('phpbb_extension_groups_seq'),
  group_name varchar(255) NOT NULL,
  cat_id INT2 DEFAULT '0' NOT NULL,
  allow_group INT2 DEFAULT '0' NOT NULL,
  download_mode INT2  DEFAULT '1' NOT NULL,
  upload_icon varchar(255) DEFAULT '' NOT NULL,
  max_filesize INT4 DEFAULT '0' NOT NULL,
  allowed_forums TEXT,
  allow_in_pm INT2 DEFAULT '0' NOT NULL,
  PRIMARY KEY (group_id),
  CHECK (download_mode>=0)
);




/* Table: phpbb_forums */
CREATE SEQUENCE phpbb_forums_seq;

CREATE TABLE phpbb_forums (
  forum_id INT2 DEFAULT nextval('phpbb_forums_seq'),
  parent_id INT2  NOT NULL,
  left_id INT2  NOT NULL,
  right_id INT2  NOT NULL,
  forum_parents TEXT,
  forum_name TEXT,
  forum_desc TEXT,
  forum_desc_bitfield INT4  DEFAULT '0' NOT NULL,
  forum_desc_uid varchar(5) DEFAULT '' NOT NULL,
  forum_link varchar(255) DEFAULT '' NOT NULL,
  forum_password varchar(40) DEFAULT '' NOT NULL,
  forum_style INT2 ,
  forum_image varchar(255) DEFAULT '' NOT NULL,
  forum_rules TEXT,
  forum_rules_link varchar(255) DEFAULT '' NOT NULL,
  forum_rules_bitfield INT4  DEFAULT '0' NOT NULL,
  forum_rules_uid varchar(5) DEFAULT '' NOT NULL,
  forum_topics_per_page INT2  DEFAULT '0' NOT NULL,
  forum_type INT2 DEFAULT '0' NOT NULL,
  forum_status INT2 DEFAULT '0' NOT NULL,
  forum_posts INT4  DEFAULT '0' NOT NULL,
  forum_topics INT4  DEFAULT '0' NOT NULL,
  forum_topics_real INT4  DEFAULT '0' NOT NULL,
  forum_last_post_id INT4  DEFAULT '0' NOT NULL,
  forum_last_poster_id INT4 DEFAULT '0' NOT NULL,
  forum_last_post_time INT4 DEFAULT '0' NOT NULL,
  forum_last_poster_name varchar(255),
  forum_flags INT2 DEFAULT '0' NOT NULL,
  display_on_index INT2 DEFAULT '1' NOT NULL,
  enable_indexing INT2 DEFAULT '1' NOT NULL,
  enable_icons INT2 DEFAULT '1' NOT NULL,
  enable_prune INT2 DEFAULT '0' NOT NULL,
  prune_next INT4 ,
  prune_days INT2  NOT NULL,
  prune_viewed INT2  NOT NULL,
  prune_freq INT2  DEFAULT '0' NOT NULL,
  PRIMARY KEY (forum_id),
  CHECK (parent_id>=0),
  CHECK (left_id>=0),
  CHECK (right_id>=0),
  CHECK (forum_style>=0),
  CHECK (forum_desc_bitfield>=0),
  CHECK (forum_rules_bitfield>=0),
  CHECK (forum_topics_per_page>=0),
  CHECK (forum_posts>=0),
  CHECK (forum_topics>=0),
  CHECK (forum_topics_real>=0),
  CHECK (forum_last_post_id>=0),
  CHECK (prune_next>=0),
  CHECK (prune_days>=0),
  CHECK (prune_viewed>=0),
  CHECK (prune_freq>=0)
);

CREATE INDEX phpbb_forums_left_right_id ON phpbb_forums (left_id, right_id);
CREATE INDEX phpbb_forums_forum_last_post_id ON phpbb_forums (forum_last_post_id);




/* Table: phpbb_forum_access */
CREATE TABLE phpbb_forum_access (
  forum_id INT4  DEFAULT '0' NOT NULL,
  user_id INT4  DEFAULT '0' NOT NULL,
  session_id varchar(32) DEFAULT '' NOT NULL,
  PRIMARY KEY (forum_id,user_id,session_id),
  CHECK (forum_id>=0),
  CHECK (user_id>=0)
);


/* Table: phpbb_forums_marking */
CREATE TABLE phpbb_forums_marking (
  user_id INT4  DEFAULT '0' NOT NULL,
  forum_id INT4  DEFAULT '0' NOT NULL,
  mark_time INT4 DEFAULT '0' NOT NULL,
  PRIMARY KEY (user_id,forum_id),
  CHECK (user_id>=0),
  CHECK (forum_id>=0)
);


/* Table: phpbb_forums_watch */
CREATE TABLE phpbb_forums_watch (
  forum_id INT2  DEFAULT '0' NOT NULL,
  user_id INT4 DEFAULT '0' NOT NULL,
  notify_status INT2 DEFAULT '0' NOT NULL
);

CREATE INDEX phpbb_forums_watch_forum_id ON phpbb_forums_watch (forum_id);
CREATE INDEX phpbb_forums_watch_user_id ON phpbb_forums_watch (user_id);
CREATE INDEX phpbb_forums_watch_notify_status ON phpbb_forums_watch (notify_status);


/* Table: phpbb_groups */
CREATE SEQUENCE phpbb_groups_seq;

CREATE TABLE phpbb_groups (
  group_id INT4 DEFAULT nextval('phpbb_groups_seq'),
  group_type INT2 DEFAULT '1' NOT NULL,
  group_name varchar_ci,
  group_desc TEXT,
  group_desc_bitfield INT4  DEFAULT '0' NOT NULL,
  group_desc_uid varchar(5) DEFAULT '' NOT NULL,
  group_display INT2 DEFAULT '0' NOT NULL,
  group_avatar varchar(255) DEFAULT '' NOT NULL,
  group_avatar_type INT2 DEFAULT '0' NOT NULL,
  group_avatar_width INT2  DEFAULT '0' NOT NULL,
  group_avatar_height INT2  DEFAULT '0' NOT NULL,
  group_rank INT2 DEFAULT '-1' NOT NULL,
  group_colour varchar(6) DEFAULT '' NOT NULL,
  group_sig_chars INT4  DEFAULT '0' NOT NULL,
  group_receive_pm INT2 DEFAULT '0' NOT NULL,
  group_message_limit INT4  DEFAULT '0' NOT NULL,
  group_chgpass INT2 DEFAULT '0' NOT NULL,
  group_legend INT2 DEFAULT '1' NOT NULL,
  PRIMARY KEY (group_id),
  CHECK (group_avatar_width>=0),
  CHECK (group_avatar_height>=0),
  CHECK (group_desc_bitfield>=0),
  CHECK (group_sig_chars>=0),
  CHECK (group_message_limit>=0)
);

CREATE INDEX phpbb_groups_group_legend ON phpbb_groups (group_legend);




/* Table: phpbb_icons */
CREATE SEQUENCE phpbb_icons_seq;

CREATE TABLE phpbb_icons (
  icons_id INT2 DEFAULT nextval('phpbb_icons_seq'),
  icons_url varchar(255),
  icons_width INT2  NOT NULL,
  icons_height INT2  NOT NULL,
  icons_order INT2  NOT NULL,
  display_on_posting INT2  DEFAULT '1' NOT NULL,
  PRIMARY KEY (icons_id),
  CHECK (icons_width>=0),
  CHECK (icons_height>=0),
  CHECK (icons_order>=0),
  CHECK (display_on_posting>=0)
);




/* Table: phpbb_lang */
CREATE SEQUENCE phpbb_lang_seq;

CREATE TABLE phpbb_lang (
  lang_id INT2 DEFAULT nextval('phpbb_lang_seq'),
  lang_iso varchar(5) NOT NULL,
  lang_dir varchar(30) NOT NULL,
  lang_english_name varchar(100),
  lang_local_name varchar(255),
  lang_author varchar(255),
  PRIMARY KEY (lang_id)
);




/* Table: phpbb_log */
CREATE SEQUENCE phpbb_log_seq;

CREATE TABLE phpbb_log (
  log_id INT4 DEFAULT nextval('phpbb_log_seq'),
  log_type INT2  DEFAULT '0' NOT NULL,
  user_id INT4 DEFAULT '0' NOT NULL,
  forum_id INT4  DEFAULT '0' NOT NULL,
  topic_id INT4  DEFAULT '0' NOT NULL,
  reportee_id INT4  DEFAULT '0' NOT NULL,
  log_ip varchar(40) NOT NULL,
  log_time INT4 NOT NULL,
  log_operation varchar(8000),
  log_data TEXT,
  PRIMARY KEY (log_id),
  CHECK (log_type>=0),
  CHECK (forum_id>=0),
  CHECK (topic_id>=0),
  CHECK (reportee_id>=0)
);

CREATE INDEX phpbb_log_log_type ON phpbb_log (log_type);
CREATE INDEX phpbb_log_forum_id ON phpbb_log (forum_id);
CREATE INDEX phpbb_log_topic_id ON phpbb_log (topic_id);
CREATE INDEX phpbb_log_reportee_id ON phpbb_log (reportee_id);
CREATE INDEX phpbb_log_user_id ON phpbb_log (user_id);




/* Table: phpbb_moderator_cache */
CREATE TABLE phpbb_moderator_cache (
  forum_id INT4  NOT NULL,
  user_id INT4  DEFAULT '0' NOT NULL,
  username varchar(255) DEFAULT '' NOT NULL,
  group_id INT4  DEFAULT '0' NOT NULL,
  group_name varchar(255) DEFAULT '' NOT NULL,
  display_on_index INT2  DEFAULT '1' NOT NULL
);

CREATE INDEX phpbb_moderator_cache_display_on_index ON phpbb_moderator_cache (display_on_index);
CREATE INDEX phpbb_moderator_cache_forum_id ON phpbb_moderator_cache (forum_id);


/* Table: phpbb_modules */
CREATE SEQUENCE phpbb_modules_seq;

CREATE TABLE phpbb_modules (
  module_id INT4 DEFAULT nextval('phpbb_modules_seq'),
  module_enabled INT2 DEFAULT '1' NOT NULL,
  module_display INT2 DEFAULT '1' NOT NULL,
  module_name varchar(255) DEFAULT '' NOT NULL,
  module_class varchar(10) DEFAULT '' NOT NULL,
  parent_id INT4 DEFAULT '0' NOT NULL,
  left_id INT4 DEFAULT '0' NOT NULL,
  right_id INT4 DEFAULT '0' NOT NULL,
  module_langname varchar(255) DEFAULT '' NOT NULL,
  module_mode varchar(255) DEFAULT '' NOT NULL,
  module_auth varchar(255) DEFAULT '' NOT NULL,
  PRIMARY KEY (module_id),
  CHECK (module_enabled>=0)
);

CREATE INDEX phpbb_modules_module_enabled ON phpbb_modules (module_enabled);
CREATE INDEX phpbb_modules_left_right_id ON phpbb_modules (left_id, right_id);




/* Table: phpbb_poll_results */
CREATE TABLE phpbb_poll_results (
  poll_option_id INT2  DEFAULT '0' NOT NULL,
  topic_id INT4  NOT NULL,
  poll_option_text varchar(3000),
  poll_option_total INT4  DEFAULT '0' NOT NULL
);

CREATE INDEX phpbb_poll_results_poll_option_id ON phpbb_poll_results (poll_option_id);
CREATE INDEX phpbb_poll_results_topic_id ON phpbb_poll_results (topic_id);


/* Table: phpbb_poll_voters */
CREATE TABLE phpbb_poll_voters (
  topic_id INT4  DEFAULT '0' NOT NULL,
  poll_option_id INT2  DEFAULT '0' NOT NULL,
  vote_user_id INT4  DEFAULT '0' NOT NULL,
  vote_user_ip varchar(40) NOT NULL
);

CREATE INDEX phpbb_poll_voters_topic_id ON phpbb_poll_voters (topic_id);
CREATE INDEX phpbb_poll_voters_vote_user_id ON phpbb_poll_voters (vote_user_id);
CREATE INDEX phpbb_poll_voters_vote_user_ip ON phpbb_poll_voters (vote_user_ip);


/* Table: phpbb_posts */
CREATE SEQUENCE phpbb_posts_seq;

CREATE TABLE phpbb_posts (
  post_id INT4 DEFAULT nextval('phpbb_posts_seq'),
  topic_id INT4  DEFAULT '0' NOT NULL,
  forum_id INT2  DEFAULT '0' NOT NULL,
  poster_id INT4  DEFAULT '0' NOT NULL,
  icon_id INT2  DEFAULT '0' NOT NULL,
  poster_ip varchar(40) NOT NULL,
  post_time INT4 DEFAULT '0' NOT NULL,
  post_approved INT2 DEFAULT '1' NOT NULL,
  post_reported INT2 DEFAULT '0' NOT NULL,
  enable_bbcode INT2 DEFAULT '1' NOT NULL,
  enable_smilies INT2 DEFAULT '1' NOT NULL,
  enable_magic_url INT2 DEFAULT '1' NOT NULL,
  enable_sig INT2 DEFAULT '1' NOT NULL,
  post_username varchar(255) NULL,
  post_subject varchar(1000) NOT NULL,
  post_text TEXT NOT NULL,
  post_checksum varchar(32) NOT NULL,
  post_encoding varchar(20) DEFAULT 'iso-8859-1' NOT NULL,
  post_attachment INT2 DEFAULT '0' NOT NULL,
  bbcode_bitfield INT4  DEFAULT '0' NOT NULL,
  bbcode_uid varchar(5) DEFAULT '' NOT NULL,
  post_edit_time INT4  DEFAULT '0' NULL,
  post_edit_reason varchar(3000) NULL,
  post_edit_user INT4  DEFAULT '0' NULL,
  post_edit_count INT2  DEFAULT '0' NULL,
  post_edit_locked INT2  DEFAULT '0' NULL,
  PRIMARY KEY (post_id),
  CHECK (topic_id>=0),
  CHECK (forum_id>=0),
  CHECK (poster_id>=0),
  CHECK (icon_id>=0),
  CHECK (bbcode_bitfield>=0),
  CHECK (post_edit_time>=0),
  CHECK (post_edit_user>=0),
  CHECK (post_edit_count>=0),
  CHECK (post_edit_locked>=0)
);

CREATE INDEX phpbb_posts_forum_id ON phpbb_posts (forum_id);
CREATE INDEX phpbb_posts_topic_id ON phpbb_posts (topic_id);
CREATE INDEX phpbb_posts_poster_ip ON phpbb_posts (poster_ip);
CREATE INDEX phpbb_posts_poster_id ON phpbb_posts (poster_id);
CREATE INDEX phpbb_posts_post_approved ON phpbb_posts (post_approved);
CREATE INDEX phpbb_posts_post_time ON phpbb_posts (post_time);




/* Table: phpbb_privmsgs */
CREATE SEQUENCE phpbb_privmsgs_seq;

CREATE TABLE phpbb_privmsgs (
  msg_id INT4 DEFAULT nextval('phpbb_privmsgs_seq'),
  root_level INT4  DEFAULT '0' NOT NULL,
  author_id INT4  DEFAULT '0' NOT NULL,
  icon_id INT2  DEFAULT '0' NOT NULL,
  author_ip varchar(40) DEFAULT '' NOT NULL,
  message_time INT4 DEFAULT '0' NOT NULL,
  enable_bbcode INT2 DEFAULT '1' NOT NULL,
  enable_smilies INT2 DEFAULT '1' NOT NULL,
  enable_magic_url INT2 DEFAULT '1' NOT NULL,
  enable_sig INT2 DEFAULT '1' NOT NULL,
  message_subject varchar(1000) NOT NULL,
  message_text TEXT NOT NULL,
  message_edit_reason varchar(3000) NULL,
  message_edit_user INT4  DEFAULT '0' NULL,
  message_encoding varchar(20) DEFAULT 'iso-8859-1' NOT NULL,
  message_attachment INT2 DEFAULT '0' NOT NULL,
  bbcode_bitfield INT4  DEFAULT '0' NOT NULL,
  bbcode_uid varchar(5) DEFAULT '' NOT NULL,
  message_edit_time INT4  DEFAULT '0' NULL,
  message_edit_count INT2  DEFAULT '0' NULL,
  to_address TEXT NOT NULL,
  bcc_address TEXT NOT NULL,
  PRIMARY KEY (msg_id),
  CHECK (root_level>=0),
  CHECK (author_id>=0),
  CHECK (icon_id>=0),
  CHECK (message_edit_user>=0),
  CHECK (bbcode_bitfield>=0),
  CHECK (message_edit_time>=0),
  CHECK (message_edit_count>=0)
);

CREATE INDEX phpbb_privmsgs_author_ip ON phpbb_privmsgs (author_ip);
CREATE INDEX phpbb_privmsgs_message_time ON phpbb_privmsgs (message_time);
CREATE INDEX phpbb_privmsgs_author_id ON phpbb_privmsgs (author_id);
CREATE INDEX phpbb_privmsgs_root_level ON phpbb_privmsgs (root_level);




/* Table: phpbb_privmsgs_folder */
CREATE SEQUENCE phpbb_privmsgs_folder_seq;

CREATE TABLE phpbb_privmsgs_folder (
  folder_id INT4 DEFAULT nextval('phpbb_privmsgs_folder_seq'),
  user_id INT4  DEFAULT '0' NOT NULL,
  folder_name varchar(255) DEFAULT '' NOT NULL,
  pm_count INT4  DEFAULT '0' NOT NULL,
  PRIMARY KEY (folder_id),
  CHECK (user_id>=0),
  CHECK (pm_count>=0)
);

CREATE INDEX phpbb_privmsgs_folder_user_id ON phpbb_privmsgs_folder (user_id);




/* Table: phpbb_privmsgs_rules */
CREATE SEQUENCE phpbb_privmsgs_rules_seq;

CREATE TABLE phpbb_privmsgs_rules (
  rule_id INT4 DEFAULT nextval('phpbb_privmsgs_rules_seq'),
  user_id INT4  DEFAULT '0' NOT NULL,
  rule_check INT4  DEFAULT '0' NOT NULL,
  rule_connection INT4  DEFAULT '0' NOT NULL,
  rule_string varchar(255) DEFAULT '' NOT NULL,
  rule_user_id INT4  DEFAULT '0' NOT NULL,
  rule_group_id INT4  DEFAULT '0' NOT NULL,
  rule_action INT4  DEFAULT '0' NOT NULL,
  rule_folder_id INT4  DEFAULT '0' NOT NULL,
  PRIMARY KEY (rule_id),
  CHECK (user_id>=0),
  CHECK (rule_check>=0),
  CHECK (rule_connection>=0),
  CHECK (rule_user_id>=0),
  CHECK (rule_group_id>=0),
  CHECK (rule_action>=0),
  CHECK (rule_folder_id>=0)
);




/* Table: phpbb_privmsgs_to */
CREATE TABLE phpbb_privmsgs_to (
  msg_id INT4  DEFAULT '0' NOT NULL,
  user_id INT4  DEFAULT '0' NOT NULL,
  author_id INT4  DEFAULT '0' NOT NULL,
  deleted INT2  DEFAULT '0' NOT NULL,
  "new" INT2  DEFAULT '1' NOT NULL,
  unread INT2  DEFAULT '1' NOT NULL,
  replied INT2  DEFAULT '0' NOT NULL,
  marked INT2  DEFAULT '0' NOT NULL,
  forwarded INT2  DEFAULT '0' NOT NULL,
  folder_id INT4 DEFAULT '0' NOT NULL
);

CREATE INDEX phpbb_privmsgs_to_msg_id ON phpbb_privmsgs_to (msg_id);
CREATE INDEX phpbb_privmsgs_to_user_id ON phpbb_privmsgs_to (user_id,folder_id);


/* Table: phpbb_profile_fields */
CREATE SEQUENCE phpbb_profile_fields_seq;

CREATE TABLE phpbb_profile_fields (
  field_id INT4 DEFAULT nextval('phpbb_profile_fields_seq'),
  field_name varchar(255) DEFAULT '' NOT NULL,
  field_type INT4  NOT NULL,
  field_ident varchar(20) DEFAULT '' NOT NULL,
  field_length varchar(20) DEFAULT '' NOT NULL,
  field_minlen varchar(255) DEFAULT '' NOT NULL,
  field_maxlen varchar(255) DEFAULT '' NOT NULL,
  field_novalue varchar(255) DEFAULT '' NOT NULL,
  field_default_value varchar(255) DEFAULT '0' NOT NULL,
  field_validation varchar(20) DEFAULT '' NOT NULL,
  field_required INT2  DEFAULT '0' NOT NULL,
  field_show_on_reg INT2  DEFAULT '0' NOT NULL,
  field_hide INT2  DEFAULT '0' NOT NULL,
  field_no_view INT2  DEFAULT '0' NOT NULL,
  field_active INT2  DEFAULT '0' NOT NULL,
  field_order INT2  DEFAULT '0' NOT NULL,
  PRIMARY KEY (field_id),
  CHECK (field_type>=0),
  CHECK (field_required>=0),
  CHECK (field_show_on_reg>=0),
  CHECK (field_hide>=0),
  CHECK (field_no_view>=0),
  CHECK (field_active>=0),
  CHECK (field_order>=0)
);

CREATE INDEX phpbb_profile_fields_field_type ON phpbb_profile_fields (field_type);
CREATE INDEX phpbb_profile_fields_field_order ON phpbb_profile_fields (field_order);




/* Table: phpbb_profile_fields_data */
CREATE TABLE phpbb_profile_fields_data (
  user_id INT4  DEFAULT '0' NOT NULL,
  PRIMARY KEY (user_id),
  CHECK (user_id>=0)
);


/* Table: phpbb_profile_fields_lang */
CREATE TABLE phpbb_profile_fields_lang (
  field_id INT4  DEFAULT '0' NOT NULL,
  lang_id INT4  DEFAULT '0' NOT NULL,
  option_id INT4  DEFAULT '0' NOT NULL,
  field_type INT2 DEFAULT '0' NOT NULL,
  value varchar(255) DEFAULT '' NOT NULL,
  PRIMARY KEY (field_id,lang_id,option_id),
  CHECK (field_id>=0),
  CHECK (lang_id>=0),
  CHECK (option_id>=0)
);


/* Table: phpbb_profile_lang */
CREATE TABLE phpbb_profile_lang (
  field_id INT4  DEFAULT '0' NOT NULL,
  lang_id INT2  DEFAULT '0' NOT NULL,
  lang_name varchar(255) DEFAULT '' NOT NULL,
  lang_explain varchar(8000),
  lang_default_value varchar(255) DEFAULT '' NOT NULL,
  PRIMARY KEY (field_id,lang_id),
  CHECK (field_id>=0),
  CHECK (lang_id>=0)
);


/* Table: phpbb_ranks */
CREATE SEQUENCE phpbb_ranks_seq;

CREATE TABLE phpbb_ranks (
  rank_id INT2 DEFAULT nextval('phpbb_ranks_seq'),
  rank_title varchar(255) NOT NULL,
  rank_min INT4 DEFAULT '0' NOT NULL,
  rank_special INT2 DEFAULT '0',
  rank_image varchar(255),
  PRIMARY KEY (rank_id)
);







/* Table: phpbb_reports */
CREATE SEQUENCE phpbb_reports_seq;

CREATE TABLE phpbb_reports (
  report_id INT2 DEFAULT nextval('phpbb_reports_seq'),
  reason_id INT2  DEFAULT '0' NOT NULL,
  post_id INT4  DEFAULT '0' NOT NULL,
  user_id INT4  DEFAULT '0' NOT NULL,
  user_notify INT2 DEFAULT '0' NOT NULL,
  report_closed INT2 DEFAULT '0' NOT NULL,
  report_time INT4  DEFAULT '0' NOT NULL,
  report_text TEXT,
  PRIMARY KEY (report_id),
  CHECK (reason_id>=0),
  CHECK (post_id>=0),
  CHECK (user_id>=0),
  CHECK (report_time>=0)
);



/* Table: phpbb_reports_reasons */
CREATE SEQUENCE phpbb_reports_reasons_seq;

CREATE TABLE phpbb_reports_reasons (
  reason_id INT2 DEFAULT nextval('phpbb_reports_reasons_seq'),
  reason_title varchar(255) DEFAULT '' NOT NULL,
  reason_description varchar(8000),
  reason_order INT2 DEFAULT '0' NOT NULL,
  PRIMARY KEY (reason_id)
);



/* Table: phpbb_search_results */
CREATE TABLE phpbb_search_results (
  search_key varchar(32) DEFAULT '' NOT NULL,
  search_time INT4 DEFAULT '0' NOT NULL,
  search_keywords TEXT,
  search_authors TEXT,
  PRIMARY KEY (search_key)
);


/* Table: phpbb_search_wordlist */
CREATE SEQUENCE phpbb_search_wordlist_seq;

CREATE TABLE phpbb_search_wordlist (
  word_id INT4 DEFAULT nextval('phpbb_search_wordlist_seq'),
  word_text varchar(50) DEFAULT '' NOT NULL,
  word_common INT2  DEFAULT '0' NOT NULL,
  PRIMARY KEY (word_text),
  CHECK (word_common>=0)
);

CREATE INDEX phpbb_search_wordlist_word_id ON phpbb_search_wordlist (word_id);




/* Table: phpbb_search_wordmatch */
CREATE TABLE phpbb_search_wordmatch (
  post_id INT4  DEFAULT '0' NOT NULL,
  word_id INT4  DEFAULT '0' NOT NULL,
  title_match INT2 DEFAULT '0' NOT NULL
);

CREATE INDEX phpbb_search_wordmatch_word_id ON phpbb_search_wordmatch (word_id);


/* Table: phpbb_sessions */
CREATE TABLE phpbb_sessions (
  session_id varchar(32) DEFAULT '' NOT NULL,
  session_user_id INT4  DEFAULT '0' NOT NULL,
  session_last_visit INT4 DEFAULT '0' NOT NULL,
  session_start INT4 DEFAULT '0' NOT NULL,
  session_time INT4 DEFAULT '0' NOT NULL,
  session_ip varchar(40) DEFAULT '0' NOT NULL,
  session_browser varchar(150) DEFAULT '' NOT NULL,
  session_page varchar(200) DEFAULT '' NOT NULL,
  session_viewonline INT2 DEFAULT '1' NOT NULL,
  session_autologin INT2 DEFAULT '0' NOT NULL,
  session_admin INT2 DEFAULT '0' NOT NULL,
  PRIMARY KEY (session_id),
  CHECK (session_user_id>=0)
);

CREATE INDEX phpbb_sessions_session_time ON phpbb_sessions (session_time);
CREATE INDEX phpbb_sessions_session_user_id ON phpbb_sessions (session_user_id);


/* Table: phpbb_sessions_keys */
CREATE TABLE phpbb_sessions_keys (
  key_id varchar(32) DEFAULT '' NOT NULL,
  user_id INT4 DEFAULT '0' NOT NULL,
  last_ip varchar(40) DEFAULT '' NOT NULL,
  last_login INT4 DEFAULT '0' NOT NULL,
  PRIMARY KEY  (key_id,user_id)
);

CREATE INDEX phpbb_sessions_keys_last_login ON phpbb_sessions_keys (last_login);


/* Table: phpbb_sitelist */
CREATE SEQUENCE phpbb_sitelist_seq;

CREATE TABLE phpbb_sitelist (
  site_id INT4 DEFAULT nextval('phpbb_sitelist_seq'),
  site_ip varchar(40) DEFAULT '' NOT NULL,
  site_hostname varchar(255) DEFAULT '' NOT NULL,
  ip_exclude INT2 DEFAULT '0' NOT NULL,
  PRIMARY KEY (site_id)
);




/* Table: phpbb_smilies */
CREATE SEQUENCE phpbb_smilies_seq;

CREATE TABLE phpbb_smilies (
  smiley_id INT2 DEFAULT nextval('phpbb_smilies_seq'),
  code varchar(50),
  emotion varchar(50),
  smiley_url varchar(50),
  smiley_width INT2  NOT NULL,
  smiley_height INT2  NOT NULL,
  smiley_order INT2  NOT NULL,
  display_on_posting INT2  DEFAULT '1' NOT NULL,
  PRIMARY KEY (smiley_id),
  CHECK (smiley_width>=0),
  CHECK (smiley_height>=0),
  CHECK (smiley_order>=0),
  CHECK (display_on_posting>=0)
);




/* Table: phpbb_styles */
CREATE SEQUENCE phpbb_styles_seq;

CREATE TABLE phpbb_styles (
  style_id INT2 DEFAULT nextval('phpbb_styles_seq'),
  style_name varchar(255) DEFAULT '' NOT NULL,
  style_copyright varchar(255) DEFAULT '' NOT NULL,
  style_active INT2 DEFAULT '1' NOT NULL,
  template_id INT2  NOT NULL,
  theme_id INT2  NOT NULL,
  imageset_id INT2  NOT NULL,
  PRIMARY KEY (style_id),
  CHECK (template_id>=0),
  CHECK (theme_id>=0),
  CHECK (imageset_id>=0)
);

CREATE UNIQUE INDEX phpbb_styles_style_name ON phpbb_styles (style_name);




/* Table: phpbb_styles_template */
CREATE SEQUENCE phpbb_styles_template_seq;

CREATE TABLE phpbb_styles_template (
  template_id INT2 DEFAULT nextval('phpbb_styles_template_seq'),
  template_name varchar(255) NOT NULL,
  template_copyright varchar(255) NOT NULL,
  template_path varchar(100) NOT NULL,
  bbcode_bitfield INT4  DEFAULT '6921' NOT NULL,
  template_storedb INT2 DEFAULT '0' NOT NULL,
  PRIMARY KEY (template_id),
  CHECK (bbcode_bitfield>=0)
);

CREATE UNIQUE INDEX phpbb_styles_template_template_name ON phpbb_styles_template (template_name);




/* Table: phpbb_styles_template_data */
CREATE TABLE phpbb_styles_template_data (
  template_id INT2  NOT NULL,
  template_filename varchar(100) DEFAULT '' NOT NULL,
  template_included TEXT,
  template_mtime INT4 DEFAULT '0' NOT NULL,
  template_data TEXT
);


/* Table: phpbb_styles_theme */
CREATE SEQUENCE phpbb_styles_theme_seq;

CREATE TABLE phpbb_styles_theme (
  theme_id INT2 DEFAULT nextval('phpbb_styles_theme_seq'),
  theme_name varchar(255) DEFAULT '' NOT NULL,
  theme_copyright varchar(255) DEFAULT '' NOT NULL,
  theme_path varchar(100) DEFAULT '' NOT NULL,
  theme_storedb INT2 DEFAULT '0' NOT NULL,
  theme_mtime INT4 DEFAULT '0' NOT NULL,
  theme_data TEXT,
  PRIMARY KEY (theme_id)
);

CREATE UNIQUE INDEX phpbb_styles_theme_theme_name ON phpbb_styles_theme (theme_name);




/* Table: phpbb_styles_imageset */
CREATE SEQUENCE phpbb_styles_imageset_seq;

CREATE TABLE phpbb_styles_imageset (
  imageset_id INT2 DEFAULT nextval('phpbb_styles_imageset_seq'),
  imageset_name varchar(255) DEFAULT '' NOT NULL,
  imageset_copyright varchar(255) DEFAULT '' NOT NULL,
  imageset_path varchar(100) DEFAULT '' NOT NULL,
  site_logo varchar(200) DEFAULT '' NOT NULL,
  btn_post varchar(200) DEFAULT '' NOT NULL,
  btn_post_pm varchar(200) DEFAULT '' NOT NULL,
  btn_reply varchar(200) DEFAULT '' NOT NULL,
  btn_reply_pm varchar(200) DEFAULT '' NOT NULL,
  btn_locked varchar(200) DEFAULT '' NOT NULL,
  btn_profile varchar(200) DEFAULT '' NOT NULL,
  btn_pm varchar(200) DEFAULT '' NOT NULL,
  btn_delete varchar(200) DEFAULT '' NOT NULL,
  btn_info varchar(200) DEFAULT '' NOT NULL,
  btn_quote varchar(200) DEFAULT '' NOT NULL,
  btn_search varchar(200) DEFAULT '' NOT NULL,
  btn_edit varchar(200) DEFAULT '' NOT NULL,
  btn_report varchar(200) DEFAULT '' NOT NULL,
  btn_email varchar(200) DEFAULT '' NOT NULL,
  btn_www varchar(200) DEFAULT '' NOT NULL,
  btn_icq varchar(200) DEFAULT '' NOT NULL,
  btn_aim varchar(200) DEFAULT '' NOT NULL,
  btn_yim varchar(200) DEFAULT '' NOT NULL,
  btn_msnm varchar(200) DEFAULT '' NOT NULL,
  btn_jabber varchar(200) DEFAULT '' NOT NULL,
  btn_online varchar(200) DEFAULT '' NOT NULL,
  btn_offline varchar(200) DEFAULT '' NOT NULL,
  btn_friend varchar(200) DEFAULT '' NOT NULL,
  btn_foe varchar(200) DEFAULT '' NOT NULL,
  icon_unapproved varchar(200) DEFAULT '' NOT NULL,
  icon_reported varchar(200) DEFAULT '' NOT NULL,
  icon_attach varchar(200) DEFAULT '' NOT NULL,
  icon_post varchar(200) DEFAULT '' NOT NULL,
  icon_post_new varchar(200) DEFAULT '' NOT NULL,
  icon_post_latest varchar(200) DEFAULT '' NOT NULL,
  icon_post_newest varchar(200) DEFAULT '' NOT NULL,
  forum varchar(200) DEFAULT '' NOT NULL,
  forum_new varchar(200) DEFAULT '' NOT NULL,
  forum_locked varchar(200) DEFAULT '' NOT NULL,
  forum_link varchar(200) DEFAULT '' NOT NULL,
  sub_forum varchar(200) DEFAULT '' NOT NULL,
  sub_forum_new varchar(200) DEFAULT '' NOT NULL,
  folder varchar(200) DEFAULT '' NOT NULL,
  folder_moved varchar(200) DEFAULT '' NOT NULL,
  folder_posted varchar(200) DEFAULT '' NOT NULL,
  folder_new varchar(200) DEFAULT '' NOT NULL,
  folder_new_posted varchar(200) DEFAULT '' NOT NULL,
  folder_hot varchar(200) DEFAULT '' NOT NULL,
  folder_hot_posted varchar(200) DEFAULT '' NOT NULL,
  folder_hot_new varchar(200) DEFAULT '' NOT NULL,
  folder_hot_new_posted varchar(200) DEFAULT '' NOT NULL,
  folder_locked varchar(200) DEFAULT '' NOT NULL,
  folder_locked_posted varchar(200) DEFAULT '' NOT NULL,
  folder_locked_new varchar(200) DEFAULT '' NOT NULL,
  folder_locked_new_posted varchar(200) DEFAULT '' NOT NULL,
  folder_sticky varchar(200) DEFAULT '' NOT NULL,
  folder_sticky_posted varchar(200) DEFAULT '' NOT NULL,
  folder_sticky_new varchar(200) DEFAULT '' NOT NULL,
  folder_sticky_new_posted varchar(200) DEFAULT '' NOT NULL,
  folder_announce varchar(200) DEFAULT '' NOT NULL,
  folder_announce_posted  varchar(200) DEFAULT '' NOT NULL,
  folder_announce_new varchar(200) DEFAULT '' NOT NULL,
  folder_announce_new_posted varchar(200) DEFAULT '' NOT NULL,
  folder_global  varchar(200) DEFAULT '' NOT NULL,
  folder_global_posted  varchar(200) DEFAULT '' NOT NULL,
  folder_global_new varchar(200) DEFAULT '' NOT NULL,
  folder_global_new_posted varchar(200) DEFAULT '' NOT NULL,
  poll_left varchar(200) DEFAULT '' NOT NULL,
  poll_center varchar(200) DEFAULT '' NOT NULL,
  poll_right varchar(200) DEFAULT '' NOT NULL,
  attach_progress_bar varchar(200) DEFAULT '' NOT NULL,
  user_icon1 varchar(200) DEFAULT '' NOT NULL,
  user_icon2 varchar(200) DEFAULT '' NOT NULL,
  user_icon3 varchar(200) DEFAULT '' NOT NULL,
  user_icon4 varchar(200) DEFAULT '' NOT NULL,
  user_icon5 varchar(200) DEFAULT '' NOT NULL,
  user_icon6 varchar(200) DEFAULT '' NOT NULL,
  user_icon7 varchar(200) DEFAULT '' NOT NULL,
  user_icon8 varchar(200) DEFAULT '' NOT NULL,
  user_icon9 varchar(200) DEFAULT '' NOT NULL,
  user_icon10 varchar(200) DEFAULT '' NOT NULL,
  PRIMARY KEY (imageset_id)
);

CREATE UNIQUE INDEX phpbb_styles_imageset_imageset_name ON phpbb_styles_imageset (imageset_name);




/* Table: phpbb_topics */
CREATE SEQUENCE phpbb_topics_seq;

CREATE TABLE phpbb_topics (
  topic_id INT4 DEFAULT nextval('phpbb_topics_seq'),
  forum_id INT2  DEFAULT '0' NOT NULL,
  icon_id INT2  DEFAULT '1' NOT NULL,
  topic_attachment INT2 DEFAULT '0' NOT NULL,
  topic_approved INT2  DEFAULT '1' NOT NULL,
  topic_reported INT2  DEFAULT '0' NOT NULL,
  topic_title varchar(1000),
  topic_poster INT4  DEFAULT '0' NOT NULL,
  topic_time INT4 DEFAULT '0' NOT NULL,
  topic_time_limit INT4 DEFAULT '0' NOT NULL,
  topic_views INT4  DEFAULT '0' NOT NULL,
  topic_replies INT4  DEFAULT '0' NOT NULL,
  topic_replies_real INT4  DEFAULT '0' NOT NULL,
  topic_status INT2 DEFAULT '0' NOT NULL,
  topic_type INT2 DEFAULT '0' NOT NULL,
  topic_first_post_id INT4  DEFAULT '0' NOT NULL,
  topic_first_poster_name varchar(255),
  topic_last_post_id INT4  DEFAULT '0' NOT NULL,
  topic_last_poster_id INT4  DEFAULT '0' NOT NULL,
  topic_last_poster_name varchar(255),
  topic_last_post_time INT4  DEFAULT '0' NOT NULL,
  topic_last_view_time INT4  DEFAULT '0' NOT NULL,
  topic_moved_id INT4  DEFAULT '0' NOT NULL,
  topic_bumped INT2  DEFAULT '0' NOT NULL,
  topic_bumper INT4  DEFAULT '0' NOT NULL,
  poll_title varchar(3000),
  poll_start INT4 DEFAULT '0' NOT NULL,
  poll_length INT4 DEFAULT '0' NOT NULL,
  poll_max_options INT2  DEFAULT '1' NOT NULL,
  poll_last_vote INT4  DEFAULT '0',
  poll_vote_change INT2  DEFAULT '0' NOT NULL,
  PRIMARY KEY (topic_id),
  CHECK (forum_id>=0),
  CHECK (icon_id>=0),
  CHECK (topic_approved>=0),
  CHECK (topic_reported>=0),
  CHECK (topic_poster>=0),
  CHECK (topic_views>=0),
  CHECK (topic_replies>=0),
  CHECK (topic_replies_real>=0),
  CHECK (topic_first_post_id>=0),
  CHECK (topic_last_post_id>=0),
  CHECK (topic_last_poster_id>=0),
  CHECK (topic_last_post_time>=0),
  CHECK (topic_last_view_time>=0),
  CHECK (topic_moved_id>=0),
  CHECK (topic_bumped>=0),
  CHECK (topic_bumper>=0),
  CHECK (poll_max_options>=0),
  CHECK (poll_last_vote>=0),
  CHECK (poll_vote_change>=0)
);

CREATE INDEX phpbb_topics_forum_id ON phpbb_topics (forum_id);
CREATE INDEX phpbb_topics_forum_id_type ON phpbb_topics (forum_id, topic_type);
CREATE INDEX phpbb_topics_topic_last_post_time ON phpbb_topics (topic_last_post_time);




/* Table: phpbb_topics_marking */
CREATE TABLE phpbb_topics_marking (
  user_id INT4  DEFAULT '0' NOT NULL,
  topic_id INT4  DEFAULT '0' NOT NULL,
  forum_id INT4  DEFAULT '0' NOT NULL,
  mark_time INT4 DEFAULT '0' NOT NULL,
  PRIMARY KEY (user_id,topic_id),
  CHECK (user_id>=0),
  CHECK (topic_id>=0),
  CHECK (forum_id>=0)
);

CREATE INDEX phpbb_topics_marking_forum_id ON phpbb_topics_marking (forum_id);


/* Table: phpbb_topics_posted */
CREATE TABLE phpbb_topics_posted (
  user_id INT4  DEFAULT '0' NOT NULL,
  topic_id INT4  DEFAULT '0' NOT NULL,
  topic_posted INT2  DEFAULT '0' NOT NULL,
  PRIMARY KEY (user_id,topic_id),
  CHECK (user_id>=0),
  CHECK (topic_id>=0)
);


/* Table: phpbb_topics_watch */
CREATE TABLE phpbb_topics_watch (
  topic_id INT4  DEFAULT '0' NOT NULL,
  user_id INT4  DEFAULT '0' NOT NULL,
  notify_status INT2 DEFAULT '0' NOT NULL
);

CREATE INDEX phpbb_topics_watch_topic_id ON phpbb_topics_watch (topic_id);
CREATE INDEX phpbb_topics_watch_user_id ON phpbb_topics_watch (user_id);
CREATE INDEX phpbb_topics_watch_notify_status ON phpbb_topics_watch (notify_status);


/* Table: phpbb_user_group */
CREATE TABLE phpbb_user_group (
  group_id INT4 DEFAULT '0' NOT NULL,
  user_id INT4  DEFAULT '0' NOT NULL,
  group_leader INT2 DEFAULT '0' NOT NULL,
  user_pending INT2
);

CREATE INDEX phpbb_user_group_group_id ON phpbb_user_group (group_id);
CREATE INDEX phpbb_user_group_user_id ON phpbb_user_group (user_id);
CREATE INDEX phpbb_user_group_group_leader ON phpbb_user_group (group_leader);


/* Table: phpbb_users */
CREATE SEQUENCE phpbb_users_seq;

CREATE TABLE phpbb_users (
  user_id INT4 DEFAULT nextval('phpbb_users_seq'),
  user_type INT2 DEFAULT '0' NOT NULL,
  group_id INT4 DEFAULT '3' NOT NULL,
  user_permissions TEXT NULL,
  user_perm_from INT4 DEFAULT '0' NOT NULL,
  user_ip varchar(40) DEFAULT '' NOT NULL,
  user_regdate INT4 DEFAULT '0' NOT NULL,
  username varchar_ci,
  user_password varchar(40) DEFAULT '' NOT NULL,
  user_passchg INT4 DEFAULT '0' NULL,
  user_email varchar(100) DEFAULT '' NOT NULL,
  user_email_hash INT8 DEFAULT '0' NOT NULL,
  user_birthday varchar(10) DEFAULT '' NULL,
  user_lastvisit INT4 DEFAULT '0' NOT NULL,
  user_lastmark INT4 DEFAULT '0' NOT NULL,
  user_lastpost_time INT4 DEFAULT '0' NOT NULL,
  user_lastpage varchar(200) DEFAULT '' NOT NULL,
  user_last_confirm_key varchar(10) DEFAULT '' NULL,
  user_last_search INT4 DEFAULT '0' NOT NULL,
  user_warnings INT2 DEFAULT '0' NULL,
  user_last_warning INT4 DEFAULT '0' NULL,
  user_login_attempts INT2 DEFAULT '0' NULL,
  user_posts INT4  DEFAULT '0' NOT NULL,
  user_lang varchar(30) DEFAULT '' NOT NULL,
  user_timezone decimal(5,2) DEFAULT '0.0' NOT NULL,
  user_dst INT2 DEFAULT '0' NOT NULL,
  user_dateformat varchar(30) DEFAULT 'd M Y H:i' NOT NULL,
  user_style INT2 DEFAULT '0' NOT NULL,
  user_rank INT4 DEFAULT '0' NULL,
  user_colour varchar(6) DEFAULT '' NOT NULL,
  user_new_privmsg INT2  DEFAULT '0' NOT NULL,
  user_unread_privmsg INT2  DEFAULT '0' NOT NULL,
  user_last_privmsg INT4 DEFAULT '0' NOT NULL,
  user_message_rules INT2  DEFAULT '0' NOT NULL,
  user_full_folder INT4 DEFAULT '-3' NOT NULL,
  user_emailtime INT4 DEFAULT '0' NOT NULL,
  user_topic_show_days INT2 DEFAULT '0' NOT NULL,
  user_topic_sortby_type varchar(1) DEFAULT 't' NOT NULL,
  user_topic_sortby_dir varchar(1) DEFAULT 'd' NOT NULL,
  user_post_show_days INT2 DEFAULT '0' NOT NULL,
  user_post_sortby_type varchar(1) DEFAULT 't' NOT NULL,
  user_post_sortby_dir varchar(1) DEFAULT 'a' NOT NULL,
  user_notify INT2 DEFAULT '0' NOT NULL,
  user_notify_pm INT2 DEFAULT '1' NOT NULL,
  user_notify_type INT2 DEFAULT '0' NOT NULL,
  user_allow_pm INT2 DEFAULT '1' NOT NULL,
  user_allow_email INT2 DEFAULT '1' NOT NULL,
  user_allow_viewonline INT2 DEFAULT '1' NOT NULL,
  user_allow_viewemail INT2 DEFAULT '1' NOT NULL,
  user_allow_massemail INT2 DEFAULT '1' NOT NULL,
  user_options INT4 DEFAULT '893' NOT NULL,
  user_avatar varchar(255) DEFAULT '' NOT NULL,
  user_avatar_type INT2 DEFAULT '0' NOT NULL,
  user_avatar_width INT2  DEFAULT '0' NOT NULL,
  user_avatar_height INT2  DEFAULT '0' NOT NULL,
  user_sig TEXT NULL,
  user_sig_bbcode_uid varchar(5) DEFAULT '' NULL,
  user_sig_bbcode_bitfield INT4 DEFAULT '0' NULL,
  user_from varchar(100) DEFAULT '' NULL,
  user_icq varchar(15) DEFAULT '' NULL,
  user_aim varchar(255) DEFAULT '' NULL,
  user_yim varchar(255) DEFAULT '' NULL,
  user_msnm varchar(255) DEFAULT '' NULL,
  user_jabber varchar(255) DEFAULT '' NULL,
  user_website varchar(200) DEFAULT '' NULL,
  user_occ varchar(255) DEFAULT '' NULL,
  user_interests varchar(255) DEFAULT '' NULL,
  user_actkey varchar(32) DEFAULT '' NOT NULL,
  user_newpasswd varchar(32) DEFAULT '' NULL,
  PRIMARY KEY (user_id),
  CHECK (user_posts>=0),
  CHECK (user_new_privmsg>=0),
  CHECK (user_unread_privmsg>=0),
  CHECK (user_message_rules>=0),
  CHECK (user_avatar_width>=0),
  CHECK (user_avatar_height>=0)
);

CREATE INDEX phpbb_users_user_birthday ON phpbb_users (user_birthday);
CREATE INDEX phpbb_users_user_email_hash ON phpbb_users (user_email_hash);
CREATE INDEX phpbb_users_username ON phpbb_users (username);
CREATE INDEX phpbb_users_lower_username ON phpbb_users (LOWER(username));




/* Table: phpbb_warnings */
CREATE SEQUENCE phpbb_warnings_seq;

CREATE TABLE phpbb_warnings (
  warning_id INT4 DEFAULT nextval('phpbb_warnings_seq'),
  user_id INT4 DEFAULT '0' NOT NULL,
  post_id INT4 DEFAULT '0' NOT NULL,
  log_id INT4 DEFAULT '0' NOT NULL,
  warning_time INT4 DEFAULT '0' NOT NULL,
  PRIMARY KEY (warning_id)
);




/* Table: phpbb_words */
CREATE SEQUENCE phpbb_words_seq;

CREATE TABLE phpbb_words (
  word_id INT4 DEFAULT nextval('phpbb_words_seq'),
  word varchar(255) NOT NULL,
  replacement varchar(255) NOT NULL,
  PRIMARY KEY (word_id)
);




/* Table: phpbb_zebra */
CREATE TABLE phpbb_zebra (
  user_id INT4  DEFAULT '0' NOT NULL,
  zebra_id INT4  DEFAULT '0' NOT NULL,
  friend INT2 DEFAULT '0' NOT NULL,
  foe INT2 DEFAULT '0' NOT NULL
);

CREATE INDEX phpbb_zebra_user_id ON phpbb_zebra (user_id);
CREATE INDEX phpbb_zebra_zebra_id ON phpbb_zebra (zebra_id);

COMMIT;
