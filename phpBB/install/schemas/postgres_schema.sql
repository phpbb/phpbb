/*
 phpBB2 PostgreSQL DB schema - phpBB group 2001


 $Id$
*/

BEGIN;

/* Table: phpbb_attachments */

CREATE SEQUENCE phpbb_attachments_attach_id_;

CREATE TABLE phpbb_attachments (
  attach_id INT4 DEFAULT nextval('phpbb_attachments_attach_id_'),
  post_msg_id INT4  DEFAULT '0' NOT NULL,
  topic_id INT4  DEFAULT '0' NOT NULL,
  in_message INT2  DEFAULT '0' NOT NULL,
  poster_id INT4  DEFAULT '0' NOT NULL,
  physical_filename varchar(255) NOT NULL,
  real_filename varchar(255) NOT NULL,
  download_count INT4  DEFAULT '0' NOT NULL,
  comment varchar(255),
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

CREATE INDEX filetime_phpbb_attachments_index ON phpbb_attachments (filetime);
CREATE INDEX post_msg_id_phpbb_attachments_index ON phpbb_attachments (post_msg_id);
CREATE INDEX topic_id_phpbb_attachments_index ON phpbb_attachments (topic_id);
CREATE INDEX poster_id_phpbb_attachments_index ON phpbb_attachments (poster_id);
CREATE INDEX physical_filename_phpbb_attachments_index ON phpbb_attachments (physical_filename);
CREATE INDEX filesize_phpbb_attachments_index ON phpbb_attachments (filesize);

SELECT SETVAL('phpbb_attachments_attach_id_',(select case when max(attach_id)>0 then max(attach_id)+1 else 1 end from phpbb_attachments));

/* Table: phpbb_auth_groups */
CREATE TABLE phpbb_auth_groups (
  group_id INT4  DEFAULT '0' NOT NULL,
  forum_id INT4  DEFAULT '0' NOT NULL,
  auth_option_id INT2  DEFAULT '0' NOT NULL,
  auth_setting INT2 DEFAULT '0' NOT NULL,
);

CREATE INDEX group_id_phpbb_auth_groups_index ON phpbb_auth_groups (group_id);
CREATE INDEX auth_option_id_phpbb_auth_groups_index ON phpbb_auth_groups (auth_option_id);

/* Table: phpbb_auth_options */
CREATE SEQUENCE phpbb_auth_options_auth_opti;

CREATE TABLE phpbb_auth_options (
  auth_option_id INT2 DEFAULT nextval('phpbb_auth_options_auth_opti'),
  auth_option char(20) NOT NULL,
  is_global INT2 DEFAULT '0' NOT NULL,
  is_local INT2 DEFAULT '0' NOT NULL,
  founder_only INT2 DEFAULT '0' NOT NULL,
  PRIMARY KEY (auth_option_id)
);

CREATE INDEX auth_option_phpbb_auth_options_index ON phpbb_auth_options (auth_option);

SELECT SETVAL('phpbb_auth_options_auth_opti',(select case when max(auth_option_id)>0 then max(auth_option_id)+1 else 1 end from phpbb_auth_options));

/* Table: phpbb_auth_presets */
CREATE SEQUENCE phpbb_auth_presets_preset_id;

CREATE TABLE phpbb_auth_presets (
  preset_id INT2 DEFAULT nextval('phpbb_auth_presets_preset_id'),
  preset_name varchar(50) DEFAULT '' NOT NULL,
  preset_user_id INT4  DEFAULT '0' NOT NULL,
  preset_type varchar(2) DEFAULT '' NOT NULL,
  preset_data text DEFAULT '' NOT NULL,
  PRIMARY KEY (preset_id),
  CHECK (preset_user_id>=0)
);

CREATE INDEX preset_type_phpbb_auth_presets_index ON phpbb_auth_presets (preset_type);

SELECT SETVAL('phpbb_auth_presets_preset_id',(select case when max(preset_id)>0 then max(preset_id)+1 else 1 end from phpbb_auth_presets));

/* Table: phpbb_auth_users */
CREATE TABLE phpbb_auth_users (
  user_id INT4  DEFAULT '0' NOT NULL,
  forum_id INT4  DEFAULT '0' NOT NULL,
  auth_option_id INT2  DEFAULT '0' NOT NULL,
  auth_setting INT2 DEFAULT '0' NOT NULL,
);

CREATE INDEX user_id_phpbb_auth_users_index ON phpbb_auth_users (user_id);
CREATE INDEX auth_option_id_phpbb_auth_users_index ON phpbb_auth_users (auth_option_id);

/* Table: phpbb_banlist */
CREATE SEQUENCE phpbb_banlist_ban_id_seq;

CREATE TABLE phpbb_banlist (
   ban_id INT4 DEFAULT nextval('phpbb_banlist_ban_id_seq'),
   ban_userid INT4  DEFAULT 0 NOT NULL,
   ban_ip varchar(40) DEFAULT '' NOT NULL,
   ban_email varchar(50) DEFAULT '' NOT NULL,
   ban_start INT4 DEFAULT '0' NOT NULL,
   ban_end INT4 DEFAULT '0' NOT NULL,
   ban_exclude INT2 DEFAULT '0' NOT NULL,
   ban_reason varchar(255) DEFAULT '' NOT NULL,
   ban_give_reason varchar(255) DEFAULT '' NOT NULL,
   PRIMARY KEY (ban_id),
  CHECK (ban_userid>=0)
);

SELECT SETVAL('phpbb_banlist_ban_id_seq',(select case when max(ban_id)>0 then max(ban_id)+1 else 1 end from phpbb_banlist));

/* Table: phpbb_bbcodes */
CREATE TABLE phpbb_bbcodes (
  bbcode_id INT2  DEFAULT '0' NOT NULL,
  bbcode_tag varchar(16) DEFAULT '' NOT NULL,
  bbcode_match varchar(255) DEFAULT '' NOT NULL,
  bbcode_tpl text DEFAULT '' NOT NULL,
  first_pass_match varchar(255) DEFAULT '' NOT NULL,
  first_pass_replace varchar(255) DEFAULT '' NOT NULL,
  second_pass_match varchar(255) DEFAULT '' NOT NULL,
  second_pass_replace text DEFAULT '' NOT NULL,
  PRIMARY KEY (bbcode_id),
  CHECK (bbcode_id>=0)
);

/* Table: phpbb_bookmarks */
CREATE TABLE phpbb_bookmarks (
   topic_id INT4  DEFAULT '0' NOT NULL,
   user_id INT4  DEFAULT '0' NOT NULL,
   order_id INT4  DEFAULT '0' NOT NULL,
);

CREATE INDEX order_id_phpbb_bookmarks_index ON phpbb_bookmarks (order_id);
CREATE INDEX topic_user_id_phpbb_bookmarks_index ON phpbb_bookmarks (topic_id, user_id);

/* Table: phpbb_bots */
CREATE SEQUENCE phpbb_bots_bot_id_seq;

CREATE TABLE phpbb_bots (
  bot_id INT2 DEFAULT nextval('phpbb_bots_bot_id_seq'),
  bot_active INT2 DEFAULT '1' NOT NULL,
  bot_name varchar(255) DEFAULT '' NOT NULL,
  user_id INT4  DEFAULT '0' NOT NULL,
  bot_agent varchar(255)  DEFAULT '' NOT NULL,
  bot_ip varchar(255) DEFAULT '' NOT NULL,
  PRIMARY KEY (bot_id),
  CHECK (user_id>=0)
);

CREATE INDEX bot_active_phpbb_bots_index ON phpbb_bots (bot_active);

SELECT SETVAL('phpbb_bots_bot_id_seq',(select case when max(bot_id)>0 then max(bot_id)+1 else 1 end from phpbb_bots));

/* Table: phpbb_cache */
CREATE TABLE phpbb_cache (
  var_name varchar(255) DEFAULT '' NOT NULL,
  var_expires INT4  DEFAULT '0' NOT NULL,
  var_data TEXT DEFAULT '' NOT NULL,
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

CREATE INDEX is_dynamic_phpbb_config_index ON phpbb_config (is_dynamic);

/* Table: phpbb_confirm */
CREATE TABLE phpbb_confirm (
  confirm_id char(32) DEFAULT '' NOT NULL,
  session_id char(32) DEFAULT '' NOT NULL,
  code char(6) DEFAULT '' NOT NULL,
  PRIMARY KEY (session_id,confirm_id)
);

/* Table: phpbb_disallow */
CREATE SEQUENCE phpbb_disallow_disallow_id_s;

CREATE TABLE phpbb_disallow (
   disallow_id INT4 DEFAULT nextval('phpbb_disallow_disallow_id_s'),
   disallow_username varchar(30) DEFAULT '' NOT NULL,
   PRIMARY KEY (disallow_id)
);

SELECT SETVAL('phpbb_disallow_disallow_id_s',(select case when max(disallow_id)>0 then max(disallow_id)+1 else 1 end from phpbb_disallow));

/* Table: phpbb_drafts */
CREATE SEQUENCE phpbb_drafts_draft_id_seq;

CREATE TABLE phpbb_drafts (
  draft_id INT4 DEFAULT nextval('phpbb_drafts_draft_id_seq'),
  user_id INT4  DEFAULT '0' NOT NULL,
  topic_id INT4  DEFAULT '0' NOT NULL,
  forum_id INT4  DEFAULT '0' NOT NULL,
  save_time INT4  DEFAULT '0' NOT NULL,
  draft_subject varchar(60),
  draft_message text DEFAULT '' NOT NULL,
  PRIMARY KEY (draft_id),
  CHECK (user_id>=0),
  CHECK (topic_id>=0),
  CHECK (forum_id>=0),
  CHECK (save_time>=0)
);

CREATE INDEX save_time_phpbb_drafts_index ON phpbb_drafts (save_time);

SELECT SETVAL('phpbb_drafts_draft_id_seq',(select case when max(draft_id)>0 then max(draft_id)+1 else 1 end from phpbb_drafts));

/* Table: phpbb_extensions */
CREATE SEQUENCE phpbb_extensions_extension_i;

CREATE TABLE phpbb_extensions (
  extension_id INT4 DEFAULT nextval('phpbb_extensions_extension_i'),
  group_id INT4  DEFAULT '0' NOT NULL,
  extension varchar(100) DEFAULT '' NOT NULL,
  PRIMARY KEY (extension_id),
  CHECK (group_id>=0)
);

SELECT SETVAL('phpbb_extensions_extension_i',(select case when max(extension_id)>0 then max(extension_id)+1 else 1 end from phpbb_extensions));

/* Table: phpbb_extension_groups */
CREATE SEQUENCE phpbb_extension_groups_group;

CREATE TABLE phpbb_extension_groups (
  group_id INT4 DEFAULT nextval('phpbb_extension_groups_group'),
  group_name char(20) NOT NULL,
  cat_id INT2 DEFAULT '0' NOT NULL,
  allow_group INT2 DEFAULT '0' NOT NULL,
  download_mode INT2  DEFAULT '1' NOT NULL,
  upload_icon varchar(100) DEFAULT '' NOT NULL,
  max_filesize INT4 DEFAULT '0' NOT NULL,
  allowed_forums TEXT DEFAULT '' NOT NULL,
  allow_in_pm INT2 DEFAULT '0' NOT NULL,
  PRIMARY KEY (group_id),
  CHECK (download_mode>=0)
);

SELECT SETVAL('phpbb_extension_groups_group',(select case when max(group_id)>0 then max(group_id)+1 else 1 end from phpbb_extension_groups));

/* Table: phpbb_forums */
CREATE SEQUENCE phpbb_forums_forum_id_seq;

CREATE TABLE phpbb_forums (
   forum_id INT2 DEFAULT nextval('phpbb_forums_forum_id_seq'),
   parent_id INT2  NOT NULL,
   left_id INT2  NOT NULL,
   right_id INT2  NOT NULL,
   forum_parents text,
   forum_name varchar(150) NOT NULL,
   forum_desc text,
   forum_link varchar(200) DEFAULT '' NOT NULL,
   forum_password varchar(32) DEFAULT '' NOT NULL,
   forum_style INT2 ,
   forum_image varchar(50) DEFAULT '' NOT NULL,
   forum_rules text DEFAULT '' NOT NULL,
   forum_rules_link varchar(200) DEFAULT '' NOT NULL,
   forum_rules_flags INT2  DEFAULT '0' NOT NULL,
   forum_rules_bbcode_bitfield INT4  DEFAULT '0' NOT NULL,
   forum_rules_bbcode_uid varchar(5) DEFAULT '' NOT NULL,
   forum_topics_per_page INT2  DEFAULT '0' NOT NULL,
   forum_type INT2 DEFAULT '0' NOT NULL,
   forum_status INT2 DEFAULT '0' NOT NULL,
   forum_posts INT4  DEFAULT '0' NOT NULL,
   forum_topics INT4  DEFAULT '0' NOT NULL,
   forum_topics_real INT4  DEFAULT '0' NOT NULL,
   forum_last_post_id INT4  DEFAULT '0' NOT NULL,
   forum_last_poster_id INT4 DEFAULT '0' NOT NULL,
   forum_last_post_time INT4 DEFAULT '0' NOT NULL,
   forum_last_poster_name varchar(30),
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
  CHECK (forum_rules_flags>=0),
  CHECK (forum_rules_bbcode_bitfield>=0),
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

CREATE INDEX left_right_id_phpbb_forums_index ON phpbb_forums (left_id, right_id);
CREATE INDEX forum_last_post_id_phpbb_forums_index ON phpbb_forums (forum_last_post_id);

SELECT SETVAL('phpbb_forums_forum_id_seq',(select case when max(forum_id)>0 then max(forum_id)+1 else 1 end from phpbb_forums));

/* Table: phpbb_forum_access */
CREATE TABLE phpbb_forum_access (
  forum_id INT4  DEFAULT '0' NOT NULL,
  user_id INT4  DEFAULT '0' NOT NULL,
  session_id char(32) DEFAULT '' NOT NULL,
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
  notify_status INT2 DEFAULT '0' NOT NULL,
);

CREATE INDEX forum_id_phpbb_forums_watch_index ON phpbb_forums_watch (forum_id);
CREATE INDEX user_id_phpbb_forums_watch_index ON phpbb_forums_watch (user_id);
CREATE INDEX notify_status_phpbb_forums_watch_index ON phpbb_forums_watch (notify_status);

/* Table: phpbb_groups */
CREATE SEQUENCE phpbb_groups_group_id_seq;

CREATE TABLE phpbb_groups (
   group_id INT4 DEFAULT nextval('phpbb_groups_group_id_seq'),
   group_type INT2 DEFAULT '1' NOT NULL,
   group_name varchar(40) DEFAULT '' NOT NULL,
   group_display INT2 DEFAULT '0' NOT NULL,
   group_avatar varchar(100) DEFAULT '' NOT NULL,
   group_avatar_type INT2 DEFAULT '0' NOT NULL,
   group_avatar_width INT2  DEFAULT '0' NOT NULL,
   group_avatar_height INT2  DEFAULT '0' NOT NULL,
   group_rank INT2 DEFAULT '-1' NOT NULL,
   group_colour varchar(6) DEFAULT '' NOT NULL,
   group_sig_chars INT4  DEFAULT '0' NOT NULL,
   group_receive_pm INT2 DEFAULT '0' NOT NULL,
   group_message_limit INT4  DEFAULT '0' NOT NULL,
   group_chgpass INT2 DEFAULT '0' NOT NULL,
   group_description varchar(255) DEFAULT '' NOT NULL,
   group_legend INT2 DEFAULT '1' NOT NULL,
   PRIMARY KEY (group_id),
  CHECK (group_avatar_width>=0),
  CHECK (group_avatar_height>=0),
  CHECK (group_sig_chars>=0),
  CHECK (group_message_limit>=0)
);

CREATE INDEX group_legend_phpbb_groups_index ON phpbb_groups (group_legend);

SELECT SETVAL('phpbb_groups_group_id_seq',(select case when max(group_id)>0 then max(group_id)+1 else 1 end from phpbb_groups));

/* Table: phpbb_icons */
CREATE SEQUENCE phpbb_icons_icons_id_seq;

CREATE TABLE phpbb_icons (
   icons_id INT2 DEFAULT nextval('phpbb_icons_icons_id_seq'),
   icons_url varchar(50),
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

SELECT SETVAL('phpbb_icons_icons_id_seq',(select case when max(icons_id)>0 then max(icons_id)+1 else 1 end from phpbb_icons));

/* Table: phpbb_lang */
CREATE SEQUENCE phpbb_lang_lang_id_seq;

CREATE TABLE phpbb_lang (
   lang_id INT2 DEFAULT nextval('phpbb_lang_lang_id_seq'),
   lang_iso varchar(5) NOT NULL,
   lang_dir varchar(30) NOT NULL,
   lang_english_name varchar(30),
   lang_local_name varchar(100),
   lang_author varchar(100),
   PRIMARY KEY (lang_id)
);

SELECT SETVAL('phpbb_lang_lang_id_seq',(select case when max(lang_id)>0 then max(lang_id)+1 else 1 end from phpbb_lang));

/* Table: phpbb_log */
CREATE SEQUENCE phpbb_log_log_id_seq;

CREATE TABLE phpbb_log (
  log_id INT4 DEFAULT nextval('phpbb_log_log_id_seq'),
  log_type INT2  DEFAULT '0' NOT NULL,
  user_id INT4 DEFAULT '0' NOT NULL,
  forum_id INT4  DEFAULT '0' NOT NULL,
  topic_id INT4  DEFAULT '0' NOT NULL,
  reportee_id INT4  DEFAULT '0' NOT NULL,
  log_ip varchar(40) NOT NULL,
  log_time INT4 NOT NULL,
  log_operation text,
  log_data text,
  PRIMARY KEY (log_id),
  CHECK (log_type>=0),
  CHECK (forum_id>=0),
  CHECK (topic_id>=0),
  CHECK (reportee_id>=0)
);

CREATE INDEX log_type_phpbb_log_index ON phpbb_log (log_type);
CREATE INDEX forum_id_phpbb_log_index ON phpbb_log (forum_id);
CREATE INDEX topic_id_phpbb_log_index ON phpbb_log (topic_id);
CREATE INDEX reportee_id_phpbb_log_index ON phpbb_log (reportee_id);
CREATE INDEX user_id_phpbb_log_index ON phpbb_log (user_id);

SELECT SETVAL('phpbb_log_log_id_seq',(select case when max(log_id)>0 then max(log_id)+1 else 1 end from phpbb_log));

/* Table: phpbb_moderator_cache */
CREATE TABLE phpbb_moderator_cache (
  forum_id INT4  NOT NULL,
  user_id INT4  DEFAULT '0' NOT NULL,
  username char(30) DEFAULT '' NOT NULL,
  group_id INT4  DEFAULT '0' NOT NULL,
  groupname char(30) DEFAULT '' NOT NULL,
  display_on_index INT2  DEFAULT '1' NOT NULL,
);

CREATE INDEX display_on_index_phpbb_moderator_cache_index ON phpbb_moderator_cache (display_on_index);
CREATE INDEX forum_id_phpbb_moderator_cache_index ON phpbb_moderator_cache (forum_id);

/* Table: phpbb_modules */
CREATE SEQUENCE phpbb_modules_module_id_seq;

CREATE TABLE phpbb_modules (
  module_id INT4 DEFAULT nextval('phpbb_modules_module_id_seq'),
  module_type char(3) DEFAULT '' NOT NULL,
  module_title varchar(50) DEFAULT '' NOT NULL,
  module_filename varchar(50) DEFAULT '' NOT NULL,
  module_order INT4 DEFAULT '0' NOT NULL,
  module_enabled INT2  DEFAULT '1' NOT NULL,
  module_subs TEXT DEFAULT '' NOT NULL,
  module_acl varchar(255) DEFAULT '' NOT NULL,
  PRIMARY KEY (module_id),
  CHECK (module_enabled>=0)
);

CREATE INDEX module_type_phpbb_modules_index ON phpbb_modules (module_type,module_enabled);

SELECT SETVAL('phpbb_modules_module_id_seq',(select case when max(module_id)>0 then max(module_id)+1 else 1 end from phpbb_modules));

/* Table: phpbb_poll_results */
CREATE TABLE phpbb_poll_results (
  poll_option_id INT2  DEFAULT '0' NOT NULL,
  topic_id INT4  NOT NULL,
  poll_option_text varchar(255) NOT NULL,
  poll_option_total INT4  DEFAULT '0' NOT NULL,
);

CREATE INDEX poll_option_id_phpbb_poll_results_index ON phpbb_poll_results (poll_option_id);
CREATE INDEX topic_id_phpbb_poll_results_index ON phpbb_poll_results (topic_id);

/* Table: phpbb_poll_voters */
CREATE TABLE phpbb_poll_voters (
  topic_id INT4  DEFAULT '0' NOT NULL,
  poll_option_id INT2  DEFAULT '0' NOT NULL,
  vote_user_id INT4  DEFAULT '0' NOT NULL,
  vote_user_ip varchar(40) NOT NULL,
);

CREATE INDEX topic_id_phpbb_poll_voters_index ON phpbb_poll_voters (topic_id);
CREATE INDEX vote_user_id_phpbb_poll_voters_index ON phpbb_poll_voters (vote_user_id);
CREATE INDEX vote_user_ip_phpbb_poll_voters_index ON phpbb_poll_voters (vote_user_ip);

/* Table: phpbb_posts */
CREATE SEQUENCE phpbb_posts_post_id_seq;

CREATE TABLE phpbb_posts (
   post_id INT4 DEFAULT nextval('phpbb_posts_post_id_seq'),
   topic_id INT4  DEFAULT '0' NOT NULL,
   forum_id INT2  DEFAULT '0' NOT NULL,
   poster_id INT4  DEFAULT '0' NOT NULL,
   icon_id INT2  DEFAULT '1' NOT NULL,
   poster_ip varchar(40) NOT NULL,
   post_time INT4 DEFAULT '0' NOT NULL,
   post_approved INT2 DEFAULT '1' NOT NULL,
   post_reported INT2 DEFAULT '0' NOT NULL,
   enable_bbcode INT2 DEFAULT '1' NOT NULL,
   enable_html INT2 DEFAULT '0' NOT NULL,
   enable_smilies INT2 DEFAULT '1' NOT NULL,
   enable_magic_url INT2 DEFAULT '1' NOT NULL,
   enable_sig INT2 DEFAULT '1' NOT NULL,
   post_username varchar(30),
   post_subject varchar(60),
   post_text text,
   post_checksum varchar(32) NOT NULL,
   post_encoding varchar(11) DEFAULT 'iso-8859-15' NOT NULL,
   post_attachment INT2 DEFAULT '0' NOT NULL,
   bbcode_bitfield INT4  DEFAULT '0' NOT NULL,
   bbcode_uid varchar(5) DEFAULT '' NOT NULL,
   post_edit_time INT4  DEFAULT '0' NOT NULL,
   post_edit_reason varchar(100),
   post_edit_user INT4  DEFAULT '0' NOT NULL,
   post_edit_count INT2  DEFAULT '0' NOT NULL,
   post_edit_locked INT2  DEFAULT '0' NOT NULL,
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

CREATE INDEX forum_id_phpbb_posts_index ON phpbb_posts (forum_id);
CREATE INDEX topic_id_phpbb_posts_index ON phpbb_posts (topic_id);
CREATE INDEX poster_ip_phpbb_posts_index ON phpbb_posts (poster_ip);
CREATE INDEX poster_id_phpbb_posts_index ON phpbb_posts (poster_id);
CREATE INDEX post_approved_phpbb_posts_index ON phpbb_posts (post_approved);
CREATE INDEX post_time_phpbb_posts_index ON phpbb_posts (post_time);

SELECT SETVAL('phpbb_posts_post_id_seq',(select case when max(post_id)>0 then max(post_id)+1 else 1 end from phpbb_posts));

/* Table: phpbb_privmsgs */
CREATE SEQUENCE phpbb_privmsgs_msg_id_seq;

CREATE TABLE phpbb_privmsgs (
   msg_id INT4 DEFAULT nextval('phpbb_privmsgs_msg_id_seq'),
   root_level INT4  DEFAULT '0' NOT NULL,
   author_id INT4  DEFAULT '0' NOT NULL,
   icon_id INT2  DEFAULT '1' NOT NULL,
   author_ip varchar(40) DEFAULT '' NOT NULL,
   message_time INT4 DEFAULT '0' NOT NULL,
   message_reported INT2 DEFAULT '0' NOT NULL,
   enable_bbcode INT2 DEFAULT '1' NOT NULL,
   enable_html INT2 DEFAULT '0' NOT NULL,
   enable_smilies INT2 DEFAULT '1' NOT NULL,
   enable_magic_url INT2 DEFAULT '1' NOT NULL,
   enable_sig INT2 DEFAULT '1' NOT NULL,
   message_subject varchar(60),
   message_text text,
   message_edit_reason varchar(100),
   message_edit_user INT4  DEFAULT '0' NOT NULL,
   message_checksum varchar(32) DEFAULT '' NOT NULL,
   message_encoding varchar(11) DEFAULT 'iso-8859-15' NOT NULL,
   message_attachment INT2 DEFAULT '0' NOT NULL,
   bbcode_bitfield INT4  DEFAULT '0' NOT NULL,
   bbcode_uid varchar(5) DEFAULT '' NOT NULL,
   message_edit_time INT4  DEFAULT '0' NOT NULL,
   message_edit_count INT2  DEFAULT '0' NOT NULL,
   to_address text,
   bcc_address text,
   PRIMARY KEY (msg_id),
  CHECK (root_level>=0),
  CHECK (author_id>=0),
  CHECK (icon_id>=0),
  CHECK (message_edit_user>=0),
  CHECK (bbcode_bitfield>=0),
  CHECK (message_edit_time>=0),
  CHECK (message_edit_count>=0)
);

CREATE INDEX author_ip_phpbb_privmsgs_index ON phpbb_privmsgs (author_ip);
CREATE INDEX message_time_phpbb_privmsgs_index ON phpbb_privmsgs (message_time);
CREATE INDEX author_id_phpbb_privmsgs_index ON phpbb_privmsgs (author_id);
CREATE INDEX root_level_phpbb_privmsgs_index ON phpbb_privmsgs (root_level);

SELECT SETVAL('phpbb_privmsgs_msg_id_seq',(select case when max(msg_id)>0 then max(msg_id)+1 else 1 end from phpbb_privmsgs));

/* Table: phpbb_privmsgs_folder */
CREATE SEQUENCE phpbb_privmsgs_folder_folder;

CREATE TABLE phpbb_privmsgs_folder (
   folder_id INT4 DEFAULT nextval('phpbb_privmsgs_folder_folder'),
   user_id INT4  DEFAULT '0' NOT NULL,
   folder_name varchar(40) DEFAULT '' NOT NULL,
   pm_count INT4  DEFAULT '0' NOT NULL,
   PRIMARY KEY (folder_id),
  CHECK (user_id>=0),
  CHECK (pm_count>=0)
);

CREATE INDEX user_id_phpbb_privmsgs_folder_index ON phpbb_privmsgs_folder (user_id);

SELECT SETVAL('phpbb_privmsgs_folder_folder',(select case when max(folder_id)>0 then max(folder_id)+1 else 1 end from phpbb_privmsgs_folder));

/* Table: phpbb_privmsgs_rules */
CREATE SEQUENCE phpbb_privmsgs_rules_rule_id;

CREATE TABLE phpbb_privmsgs_rules (
   rule_id INT4 DEFAULT nextval('phpbb_privmsgs_rules_rule_id'),
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

SELECT SETVAL('phpbb_privmsgs_rules_rule_id',(select case when max(rule_id)>0 then max(rule_id)+1 else 1 end from phpbb_privmsgs_rules));

/* Table: phpbb_privmsgs_to */
CREATE TABLE phpbb_privmsgs_to (
   msg_id INT4  DEFAULT '0' NOT NULL,
   user_id INT4  DEFAULT '0' NOT NULL,
   author_id INT4  DEFAULT '0' NOT NULL,
   deleted INT2  DEFAULT '0' NOT NULL,
   new INT2  DEFAULT '1' NOT NULL,
   unread INT2  DEFAULT '1' NOT NULL,
   replied INT2  DEFAULT '0' NOT NULL,
   marked INT2  DEFAULT '0' NOT NULL,
   forwarded INT2  DEFAULT '0' NOT NULL,
   folder_id INT4 DEFAULT '0' NOT NULL,
);

CREATE INDEX msg_id_phpbb_privmsgs_to_index ON phpbb_privmsgs_to (msg_id);
CREATE INDEX user_id_phpbb_privmsgs_to_index ON phpbb_privmsgs_to (user_id,folder_id);

/* Table: phpbb_profile_fields */
CREATE SEQUENCE phpbb_profile_fields_field_i;

CREATE TABLE phpbb_profile_fields (
   field_id INT4 DEFAULT nextval('phpbb_profile_fields_field_i'),
   field_name varchar(50) DEFAULT '' NOT NULL,
   field_desc varchar(255) DEFAULT '' NOT NULL,
   field_type INT4  NOT NULL,
   field_ident varchar(20) DEFAULT '' NOT NULL,
   field_length varchar(20) DEFAULT '' NOT NULL,
   field_minlen varchar(255) DEFAULT '' NOT NULL,
   field_maxlen varchar(255) DEFAULT '' NOT NULL,
   field_novalue varchar(255) DEFAULT '' NOT NULL,
   field_DEFAULT_value varchar(255) DEFAULT '0' NOT NULL,
   field_validation varchar(20) DEFAULT '' NOT NULL,
   field_required INT2  DEFAULT '0' NOT NULL,
   field_show_on_reg INT2  DEFAULT '0' NOT NULL,
   field_hide INT2  DEFAULT '0' NOT NULL,
   field_active INT2  DEFAULT '0' NOT NULL,
   field_order INT2  DEFAULT '0' NOT NULL,
   PRIMARY KEY (field_id),
  CHECK (field_type>=0),
  CHECK (field_required>=0),
  CHECK (field_show_on_reg>=0),
  CHECK (field_hide>=0),
  CHECK (field_active>=0),
  CHECK (field_order>=0)
);

CREATE INDEX field_type_phpbb_profile_fields_index ON phpbb_profile_fields (field_type);
CREATE INDEX field_order_phpbb_profile_fields_index ON phpbb_profile_fields (field_order);

SELECT SETVAL('phpbb_profile_fields_field_i',(select case when max(field_id)>0 then max(field_id)+1 else 1 end from phpbb_profile_fields));

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
   lang_explain TEXT DEFAULT '' NOT NULL,
   lang_DEFAULT_value varchar(255) DEFAULT '' NOT NULL,
   PRIMARY KEY (field_id,lang_id),
  CHECK (field_id>=0),
  CHECK (lang_id>=0)
);

/* Table: phpbb_ranks */
CREATE SEQUENCE phpbb_ranks_rank_id_seq;

CREATE TABLE phpbb_ranks (
   rank_id INT2 DEFAULT nextval('phpbb_ranks_rank_id_seq'),
   rank_title varchar(50) NOT NULL,
   rank_min INT4 DEFAULT '0' NOT NULL,
   rank_special INT2 DEFAULT '0',
   rank_image varchar(100),
   PRIMARY KEY (rank_id)
);

SELECT SETVAL('phpbb_ranks_rank_id_seq',(select case when max(rank_id)>0 then max(rank_id)+1 else 1 end from phpbb_ranks));

/* Table: phpbb_ratings */
CREATE TABLE phpbb_ratings (
  post_id INT4  DEFAULT '0' NOT NULL,
  user_id INT2  UNSIGNED DEFAULT '0' NOT NULL,
  rating INT2 DEFAULT '0' NOT NULL,
);

CREATE INDEX post_id_phpbb_ratings_index ON phpbb_ratings (post_id);
CREATE INDEX user_id_phpbb_ratings_index ON phpbb_ratings (user_id);

/* Table: phpbb_reports_reasons */
CREATE SEQUENCE phpbb_reports_reasons_reason;

CREATE TABLE phpbb_reports_reasons (
  reason_id INT2 DEFAULT nextval('phpbb_reports_reasons_reason'),
  reason_priority INT2 DEFAULT '0' NOT NULL,
  reason_name varchar(255) DEFAULT '' NOT NULL,
  reason_description TEXT DEFAULT '' NOT NULL,
  PRIMARY KEY (reason_id)
);

SELECT SETVAL('phpbb_reports_reasons_reason',(select case when max(reason_id)>0 then max(reason_id)+1 else 1 end from phpbb_reports_reasons));

/* Table: phpbb_reports */
CREATE SEQUENCE phpbb_reports_report_id_seq;

CREATE TABLE phpbb_reports (
  report_id INT2 DEFAULT nextval('phpbb_reports_report_id_seq'),
  reason_id INT2  DEFAULT '0' NOT NULL,
  post_id INT4  DEFAULT '0' NOT NULL,
  msg_id INT4  DEFAULT '0' NOT NULL,
  user_id INT4  DEFAULT '0' NOT NULL,
  user_notify INT2 DEFAULT '0' NOT NULL,
  report_time INT4  DEFAULT '0' NOT NULL,
  report_text TEXT DEFAULT '' NOT NULL,
  PRIMARY KEY (report_id),
  CHECK (reason_id>=0),
  CHECK (post_id>=0),
  CHECK (msg_id>=0),
  CHECK (user_id>=0),
  CHECK (report_time>=0)
);

SELECT SETVAL('phpbb_reports_report_id_seq',(select case when max(report_id)>0 then max(report_id)+1 else 1 end from phpbb_reports));

/* Table: phpbb_search_results */
CREATE TABLE phpbb_search_results (
  search_id INT4  DEFAULT '0' NOT NULL,
  session_id varchar(32) DEFAULT '' NOT NULL,
  search_time INT4 DEFAULT '0' NOT NULL,
  search_array TEXT DEFAULT '' NOT NULL,
  PRIMARY KEY (search_id),
  CHECK (search_id>=0)
);

CREATE INDEX session_id_phpbb_search_results_index ON phpbb_search_results (session_id);

/* Table: phpbb_search_wordlist */
CREATE SEQUENCE phpbb_search_wordlist_word_i;

CREATE TABLE phpbb_search_wordlist (
  word_text varchar(50) BINARY DEFAULT '' NOT NULL,
  word_id INT4 DEFAULT nextval('phpbb_search_wordlist_word_i'),
  word_common INT2  DEFAULT '0' NOT NULL,
  PRIMARY KEY (word_text),
  CHECK (word_common>=0)
);

CREATE INDEX word_id_phpbb_search_wordlist_index ON phpbb_search_wordlist (word_id);

SELECT SETVAL('phpbb_search_wordlist_word_i',(select case when max(word_id)>0 then max(word_id)+1 else 1 end from phpbb_search_wordlist));

/* Table: phpbb_search_wordmatch */
CREATE TABLE phpbb_search_wordmatch (
  post_id INT4  DEFAULT '0' NOT NULL,
  word_id INT4  DEFAULT '0' NOT NULL,
  title_match INT2 DEFAULT '0' NOT NULL,
);

CREATE INDEX word_id_phpbb_search_wordmatch_index ON phpbb_search_wordmatch (word_id);

/* Table: phpbb_sessions */
CREATE TABLE phpbb_sessions (
   session_id varchar(32) DEFAULT '' NOT NULL,
   session_user_id INT4  DEFAULT '0' NOT NULL,
   session_last_visit INT4 DEFAULT '0' NOT NULL,
   session_start INT4 DEFAULT '0' NOT NULL,
   session_time INT4 DEFAULT '0' NOT NULL,
   session_ip varchar(40) DEFAULT '0' NOT NULL,
   session_browser varchar(100) DEFAULT '' NULL,
   session_page varchar(100) DEFAULT '' NOT NULL,
   session_viewonline INT2 DEFAULT '1' NOT NULL,
   session_admin INT2 DEFAULT '0' NOT NULL,
   PRIMARY KEY (session_id),
  CHECK (session_user_id>=0)
);

CREATE INDEX session_time_phpbb_sessions_index ON phpbb_sessions (session_time);
CREATE INDEX session_user_id_phpbb_sessions_index ON phpbb_sessions (session_user_id);

/* Table: phpbb_sitelist */
CREATE SEQUENCE phpbb_sitelist_site_id_seq;

CREATE TABLE phpbb_sitelist (
   site_id INT4 DEFAULT nextval('phpbb_sitelist_site_id_seq'),
   site_ip varchar(40) DEFAULT '' NOT NULL,
   site_hostname varchar(255) DEFAULT '' NOT NULL,
   ip_exclude INT2 DEFAULT '0' NOT NULL,
   PRIMARY KEY (site_id)
);

SELECT SETVAL('phpbb_sitelist_site_id_seq',(select case when max(site_id)>0 then max(site_id)+1 else 1 end from phpbb_sitelist));

/* Table: phpbb_smilies */
CREATE SEQUENCE phpbb_smilies_smiley_id_seq;

CREATE TABLE phpbb_smilies (
   smiley_id INT2 DEFAULT nextval('phpbb_smilies_smiley_id_seq'),
   code char(10),
   smiley char(50),
   smiley_url char(50),
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

SELECT SETVAL('phpbb_smilies_smiley_id_seq',(select case when max(smiley_id)>0 then max(smiley_id)+1 else 1 end from phpbb_smilies));

/* Table: phpbb_styles */
CREATE SEQUENCE phpbb_styles_style_id_seq;

CREATE TABLE phpbb_styles (
   style_id INT2 DEFAULT nextval('phpbb_styles_style_id_seq'),
   style_name varchar(30) DEFAULT '' NOT NULL,
   style_copyright varchar(50) DEFAULT '' NOT NULL,
   style_active INT2 DEFAULT '1' NOT NULL,
   template_id INT2  NOT NULL,
   theme_id INT2  NOT NULL,
   imageset_id INT2  NOT NULL,
   PRIMARY KEY (style_id),
  CHECK (template_id>=0),
  CHECK (theme_id>=0),
  CHECK (imageset_id>=0)

   KEY (template_id),
   KEY (theme_id),
   KEY (imageset_id)
);

CREATE UNIQUE INDEX style_name_phpbb_styles_index ON phpbb_styles (style_name);

SELECT SETVAL('phpbb_styles_style_id_seq',(select case when max(style_id)>0 then max(style_id)+1 else 1 end from phpbb_styles));

/* Table: phpbb_styles_template */
CREATE SEQUENCE phpbb_styles_template_templa;

CREATE TABLE phpbb_styles_template (
   template_id INT2 DEFAULT nextval('phpbb_styles_template_templa'),
   template_name varchar(30) NOT NULL,
   template_copyright varchar(50) NOT NULL,
   template_path varchar(30) NOT NULL,
   bbcode_bitfield INT4  DEFAULT '0' NOT NULL,
   template_storedb INT2 DEFAULT '0' NOT NULL,
   PRIMARY KEY (template_id),
  CHECK (bbcode_bitfield>=0)
);

CREATE UNIQUE INDEX template_name_phpbb_styles_template_index ON phpbb_styles_template (template_name);

SELECT SETVAL('phpbb_styles_template_templa',(select case when max(template_id)>0 then max(template_id)+1 else 1 end from phpbb_styles_template));

/* Table: phpbb_styles_template_data */
CREATE TABLE phpbb_styles_template_data (
   template_id INT2  NOT NULL,
   template_filename varchar(50) DEFAULT '' NOT NULL,
   template_included TEXT DEFAULT '' NOT NULL,
   template_mtime INT4 DEFAULT '0' NOT NULL,
   template_data text,
   KEY (template_id),
   KEY (template_filename)
);

/* Table: phpbb_styles_theme */
CREATE SEQUENCE phpbb_styles_theme_theme_id_;

CREATE TABLE phpbb_styles_theme (
   theme_id INT2 DEFAULT nextval('phpbb_styles_theme_theme_id_'),
   theme_name varchar(30) DEFAULT '' NOT NULL,
   theme_copyright varchar(50) DEFAULT '' NOT NULL,
   theme_path varchar(30) DEFAULT '' NOT NULL,
   theme_storedb INT2 DEFAULT '0' NOT NULL,
   theme_mtime INT4 DEFAULT '0' NOT NULL,
   theme_data text DEFAULT '' NOT NULL,
   PRIMARY KEY (theme_id)
);

CREATE UNIQUE INDEX theme_name_phpbb_styles_theme_index ON phpbb_styles_theme (theme_name);

SELECT SETVAL('phpbb_styles_theme_theme_id_',(select case when max(theme_id)>0 then max(theme_id)+1 else 1 end from phpbb_styles_theme));

/* Table: phpbb_styles_imageset */
CREATE SEQUENCE phpbb_styles_imageset_images;

CREATE TABLE phpbb_styles_imageset (
  imageset_id INT2 DEFAULT nextval('phpbb_styles_imageset_images'),
  imageset_name varchar(30) DEFAULT '' NOT NULL,
  imageset_copyright varchar(50) DEFAULT '' NOT NULL,
  imageset_path varchar(30) DEFAULT '' NOT NULL,
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

CREATE UNIQUE INDEX imageset_name_phpbb_styles_imageset_index ON phpbb_styles_imageset (imageset_name);

SELECT SETVAL('phpbb_styles_imageset_images',(select case when max(imageset_id)>0 then max(imageset_id)+1 else 1 end from phpbb_styles_imageset));

/* Table: phpbb_topics */
CREATE SEQUENCE phpbb_topics_topic_id_seq;

CREATE TABLE phpbb_topics (
   topic_id INT4 DEFAULT nextval('phpbb_topics_topic_id_seq'),
   forum_id INT2  DEFAULT '0' NOT NULL,
   icon_id INT2  DEFAULT '1' NOT NULL,
   topic_attachment INT2 DEFAULT '0' NOT NULL,
   topic_approved INT2  DEFAULT '1' NOT NULL,
   topic_reported INT2  DEFAULT '0' NOT NULL,
   topic_title varchar(60) NOT NULL,
   topic_poster INT4  DEFAULT '0' NOT NULL,
   topic_time INT4 DEFAULT '0' NOT NULL,
   topic_time_limit INT4 DEFAULT '0' NOT NULL,
   topic_views INT4  DEFAULT '0' NOT NULL,
   topic_replies INT4  DEFAULT '0' NOT NULL,
   topic_replies_real INT4  DEFAULT '0' NOT NULL,
   topic_status INT2 DEFAULT '0' NOT NULL,
   topic_type INT2 DEFAULT '0' NOT NULL,
   topic_first_post_id INT4  DEFAULT '0' NOT NULL,
   topic_first_poster_name varchar(30),
   topic_last_post_id INT4  DEFAULT '0' NOT NULL,
   topic_last_poster_id INT4  DEFAULT '0' NOT NULL,
   topic_last_poster_name varchar(30),
   topic_last_post_time INT4  DEFAULT '0' NOT NULL,
   topic_last_view_time INT4  DEFAULT '0' NOT NULL,
   topic_moved_id INT4  DEFAULT '0' NOT NULL,
   topic_bumped INT2  DEFAULT '0' NOT NULL,
   topic_bumper INT4  DEFAULT '0' NOT NULL,
   poll_title varchar(255) NOT NULL,
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

CREATE INDEX forum_id_phpbb_topics_index ON phpbb_topics (forum_id);
CREATE INDEX forum_id_type_phpbb_topics_index ON phpbb_topics (forum_id, topic_type);
CREATE INDEX topic_last_post_time_phpbb_topics_index ON phpbb_topics (topic_last_post_time);

SELECT SETVAL('phpbb_topics_topic_id_seq',(select case when max(topic_id)>0 then max(topic_id)+1 else 1 end from phpbb_topics));

/* Table: phpbb_topic_marking */
CREATE TABLE phpbb_topics_marking (
   user_id INT4  DEFAULT '0' NOT NULL,
   topic_id INT4  DEFAULT '0' NOT NULL,
   mark_type INT2 DEFAULT '0' NOT NULL,
   mark_time INT4 DEFAULT '0' NOT NULL,
   PRIMARY KEY (user_id,topic_id),
  CHECK (user_id>=0),
  CHECK (topic_id>=0)
);

/* Table: phpbb_topics_watch */
CREATE TABLE phpbb_topics_watch (
  topic_id INT4  DEFAULT '0' NOT NULL,
  user_id INT4  DEFAULT '0' NOT NULL,
  notify_status INT2 DEFAULT '0' NOT NULL,
);

CREATE INDEX topic_id_phpbb_topics_watch_index ON phpbb_topics_watch (topic_id);
CREATE INDEX user_id_phpbb_topics_watch_index ON phpbb_topics_watch (user_id);
CREATE INDEX notify_status_phpbb_topics_watch_index ON phpbb_topics_watch (notify_status);

/* Table: phpbb_user_group */
CREATE TABLE phpbb_user_group (
   group_id INT4 DEFAULT '0' NOT NULL,
   user_id INT4  DEFAULT '0' NOT NULL,
   group_leader INT2 DEFAULT '0' NOT NULL,
   user_pending INT2,
);

CREATE INDEX group_id_phpbb_user_group_index ON phpbb_user_group (group_id);
CREATE INDEX user_id_phpbb_user_group_index ON phpbb_user_group (user_id);
CREATE INDEX group_leader_phpbb_user_group_index ON phpbb_user_group (group_leader);

/* Table: phpbb_users */
CREATE SEQUENCE phpbb_users_user_id_seq;

CREATE TABLE phpbb_users (
   user_id INT4 DEFAULT nextval('phpbb_users_user_id_seq'),
   user_type INT2 DEFAULT '0' NOT NULL,
   group_id INT4 DEFAULT '3' NOT NULL,
   user_permissions text DEFAULT '' NOT NULL,
   user_ip varchar(40) DEFAULT '' NOT NULL,
   user_regdate INT4 DEFAULT '0' NOT NULL,
   username varchar(30) DEFAULT '' NOT NULL,
   user_password varchar(32) DEFAULT '' NOT NULL,
   user_passchg INT4 DEFAULT '0' NOT NULL,
   user_email varchar(60) DEFAULT '' NOT NULL,
   user_email_hash INT8 DEFAULT '0' NOT NULL,
   user_birthday varchar(10) DEFAULT '' NOT NULL,
   user_lastvisit INT4 DEFAULT '0' NOT NULL,
   user_lastpost_time INT4 DEFAULT '0' NOT NULL,
   user_lastpage varchar(100) DEFAULT '' NOT NULL,
   user_last_confirm_key varchar(10) DEFAULT '' NOT NULL,
   user_warnings INT2 DEFAULT '0' NOT NULL,
   user_posts INT4  DEFAULT '0' NOT NULL,
   user_lang varchar(30) DEFAULT '' NOT NULL,
   user_timezone decimal(5,2) DEFAULT '0.0' NOT NULL,
   user_dst INT2 DEFAULT '0' NOT NULL,
   user_dateformat varchar(15) DEFAULT 'd M Y H:i' NOT NULL,
   user_style INT2 DEFAULT '0' NOT NULL,
   user_rank INT4 DEFAULT '0',
   user_colour varchar(6) DEFAULT '' NOT NULL,
   user_new_privmsg INT2  DEFAULT '0' NOT NULL,
   user_unread_privmsg INT2  DEFAULT '0' NOT NULL,
   user_last_privmsg INT4 DEFAULT '0' NOT NULL,
   user_message_rules INT2  DEFAULT '0' NOT NULL,
   user_full_folder INT4 DEFAULT '-3' NOT NULL,
   user_emailtime INT4 DEFAULT '0' NOT NULL,
   user_sortby_type varchar(1) DEFAULT '' NOT NULL,
   user_sortby_dir varchar(1) DEFAULT '' NOT NULL,
   user_topic_show_days INT4 DEFAULT '0' NOT NULL,
   user_topic_sortby_type varchar(1) DEFAULT '' NOT NULL,
   user_topic_sortby_dir varchar(1) DEFAULT '' NOT NULL,
   user_post_show_days INT4 DEFAULT '0' NOT NULL,
   user_post_sortby_type varchar(1) DEFAULT '' NOT NULL,
   user_post_sortby_dir varchar(1) DEFAULT '' NOT NULL,
   user_notify INT2 DEFAULT '0' NOT NULL,
   user_notify_pm INT2 DEFAULT '1' NOT NULL,
   user_notify_type INT2 DEFAULT '0' NOT NULL,
   user_allow_pm INT2 DEFAULT '1' NOT NULL,
   user_allow_email INT2 DEFAULT '1' NOT NULL,
   user_allow_viewonline INT2 DEFAULT '1' NOT NULL,
   user_allow_viewemail INT2 DEFAULT '1' NOT NULL,
   user_allow_massemail INT2 DEFAULT '1' NOT NULL,
   user_options INT4 DEFAULT '893' NOT NULL,
   user_avatar varchar(100) DEFAULT '' NOT NULL,
   user_avatar_type INT2 DEFAULT '0' NOT NULL,
   user_avatar_width INT2  DEFAULT '0' NOT NULL,
   user_avatar_height INT2  DEFAULT '0' NOT NULL,
   user_sig text DEFAULT '' NOT NULL,
   user_sig_bbcode_uid varchar(5) DEFAULT '' NOT NULL,
   user_sig_bbcode_bitfield INT4 DEFAULT '0' NOT NULL,
   user_from varchar(100) DEFAULT '' NOT NULL,
   user_icq varchar(15) DEFAULT '' NOT NULL,
   user_aim varchar(255) DEFAULT '' NOT NULL,
   user_yim varchar(255) DEFAULT '' NOT NULL,
   user_msnm varchar(255) DEFAULT '' NOT NULL,
   user_jabber varchar(255) DEFAULT '' NOT NULL,
   user_website varchar(100) DEFAULT '' NOT NULL,
   user_occ varchar(255) DEFAULT '' NOT NULL,
   user_interests varchar(255) DEFAULT '' NOT NULL,
   user_actkey varchar(32) DEFAULT '' NOT NULL,
   user_newpasswd varchar(32) DEFAULT '' NOT NULL,
   PRIMARY KEY (user_id),
  CHECK (user_posts>=0),
  CHECK (user_new_privmsg>=0),
  CHECK (user_unread_privmsg>=0),
  CHECK (user_message_rules>=0),
  CHECK (user_avatar_width>=0),
  CHECK (user_avatar_height>=0)
);

CREATE INDEX user_birthday_phpbb_users_index ON phpbb_users (user_birthday);
CREATE INDEX user_email_hash_phpbb_users_index ON phpbb_users (user_email_hash);
CREATE INDEX username_phpbb_users_index ON phpbb_users (username);

SELECT SETVAL('phpbb_users_user_id_seq',(select case when max(user_id)>0 then max(user_id)+1 else 1 end from phpbb_users));

/* Table: phpbb_words */
CREATE SEQUENCE phpbb_words_word_id_seq;

CREATE TABLE phpbb_words (
   word_id INT4 DEFAULT nextval('phpbb_words_word_id_seq'),
   word char(100) NOT NULL,
   replacement char(100) NOT NULL,
   PRIMARY KEY (word_id)
);

SELECT SETVAL('phpbb_words_word_id_seq',(select case when max(word_id)>0 then max(word_id)+1 else 1 end from phpbb_words));

/* Table: phpbb_zebra */
CREATE TABLE phpbb_zebra (
  user_id INT4  DEFAULT '0' NOT NULL,
  zebra_id INT4  DEFAULT '0' NOT NULL,
  friend INT2 DEFAULT '0' NOT NULL,
  foe INT2 DEFAULT '0' NOT NULL,
);

CREATE INDEX user_id_phpbb_zebra_index ON phpbb_zebra (user_id);
CREATE INDEX zebra_id_phpbb_zebra_index ON phpbb_zebra (zebra_id);

COMMIT;
