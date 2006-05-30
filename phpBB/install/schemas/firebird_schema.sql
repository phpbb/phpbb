#
# Firebird Schema for phpBB 3.x - (c) phpBB Group, 2005
#
# $Id$

# phpbb_attachments
CREATE TABLE phpbb_attachments (
  attach_id INTEGER NOT NULL,
  post_msg_id INTEGER DEFAULT 0  NOT NULL,
  topic_id INTEGER DEFAULT 0  NOT NULL,
  in_message INTEGER DEFAULT 0  NOT NULL,
  poster_id INTEGER DEFAULT 0  NOT NULL,
  physical_filename VARCHAR(255) NOT NULL,
  real_filename VARCHAR(255) NOT NULL,
  download_count INTEGER DEFAULT 0  NOT NULL,
  comment BLOB SUB_TYPE TEXT,
  extension VARCHAR(100),
  mimetype VARCHAR(100),
  filesize INTEGER NOT NULL,
  filetime INTEGER DEFAULT 0  NOT NULL,
  thumbnail INTEGER DEFAULT 0  NOT NULL
);;

ALTER TABLE phpbb_attachments ADD PRIMARY KEY (attach_id);;

CREATE INDEX phpbb_attachments_filesize ON phpbb_attachments(filesize);;
CREATE INDEX phpbb_attachments_filetime ON phpbb_attachments(filetime);;
CREATE INDEX phpbb_attachments_post_msg_id ON phpbb_attachments(post_msg_id);;
CREATE INDEX phpbb_attachments_poster_id ON phpbb_attachments(poster_id);;
CREATE INDEX phpbb_attachments_topic_id ON phpbb_attachments(topic_id);;

CREATE GENERATOR phpbb_attachments_gen;;
SET GENERATOR phpbb_attachments_gen TO 0;;

CREATE TRIGGER t_phpbb_attachments_gen FOR phpbb_attachments
BEFORE INSERT
AS
BEGIN
  NEW.attach_id = GEN_ID(phpbb_attachments_gen, 1);
END;;


# phpbb_auth_groups
CREATE TABLE phpbb_auth_groups (
  group_id INTEGER DEFAULT 0  NOT NULL,
  forum_id INTEGER DEFAULT 0  NOT NULL,
  auth_option_id INTEGER DEFAULT 0  NOT NULL,
  auth_role_id INTEGER DEFAULT 0  NOT NULL,
  auth_setting INTEGER DEFAULT 0  NOT NULL
);;

CREATE INDEX phpbb_auth_groups_auth_opt_id ON phpbb_auth_groups(auth_option_id);;
CREATE INDEX phpbb_auth_groups_group_id ON phpbb_auth_groups(group_id);;


# phpbb_auth_options
CREATE TABLE phpbb_auth_options (
  auth_option_id INTEGER NOT NULL,
  auth_option VARCHAR(20) NOT NULL,
  is_global INTEGER DEFAULT 0  NOT NULL,
  is_local INTEGER DEFAULT 0  NOT NULL,
  founder_only INTEGER DEFAULT 0  NOT NULL
);;

ALTER TABLE phpbb_auth_options ADD PRIMARY KEY (auth_option_id);;

CREATE INDEX phpbb_auth_options_auth_option ON phpbb_auth_options(auth_option);;

CREATE GENERATOR phpbb_auth_options_gen;;
SET GENERATOR phpbb_auth_options_gen TO 0;;

CREATE TRIGGER t_phpbb_auth_options_gen FOR phpbb_auth_options
BEFORE INSERT
AS
BEGIN
  NEW.auth_option_id = GEN_ID(phpbb_auth_options_gen, 1);
END;;


# phpbb_auth_roles
CREATE TABLE phpbb_auth_roles (
  role_id INTEGER NOT NULL,
  role_name VARCHAR(255) DEFAULT '' NOT NULL,
  role_description BLOB SUB_TYPE TEXT,
  role_type VARCHAR(10) DEFAULT '' NOT NULL,
  role_order INTEGER DEFAULT 0  NOT NULL
);;

ALTER TABLE phpbb_auth_roles ADD PRIMARY KEY (role_id);;

CREATE INDEX phpbb_auth_roles_role_type ON phpbb_auth_roles(role_type);;
CREATE INDEX phpbb_auth_roles_role_order ON phpbb_auth_roles(role_order);;

CREATE GENERATOR phpbb_auth_roles_gen;;
SET GENERATOR phpbb_auth_roles_gen TO 0;;

CREATE TRIGGER t_phpbb_auth_roles_gen FOR phpbb_auth_roles
BEFORE INSERT
AS
BEGIN
  NEW.role_id = GEN_ID(phpbb_auth_roles_gen, 1);
END;;


# phpbb_auth_roles_data
CREATE TABLE phpbb_auth_roles_data (
  role_id INTEGER DEFAULT 0  NOT NULL,
  auth_option_id INTEGER DEFAULT 0  NOT NULL,
  auth_setting INTEGER DEFAULT 0  NOT NULL
);;

ALTER TABLE phpbb_auth_roles_data ADD PRIMARY KEY (role_id, auth_option_id);;


# phpbb_auth_users
CREATE TABLE phpbb_auth_users (
  user_id INTEGER DEFAULT 0  NOT NULL,
  forum_id INTEGER DEFAULT 0  NOT NULL,
  auth_option_id INTEGER DEFAULT 0  NOT NULL,
  auth_role_id INTEGER DEFAULT 0  NOT NULL,
  auth_setting INTEGER DEFAULT 0  NOT NULL
);;

CREATE INDEX phpbb_auth_users_auth_opt_id ON phpbb_auth_users(auth_option_id);;
CREATE INDEX phpbb_auth_users_user_id ON phpbb_auth_users(user_id);;


# phpbb_banlist
CREATE TABLE phpbb_banlist (
  ban_id INTEGER NOT NULL,
  ban_userid INTEGER DEFAULT 0  NOT NULL,
  ban_ip VARCHAR(40) DEFAULT '' NOT NULL,
  ban_email VARCHAR(100) DEFAULT '' NOT NULL,
  ban_start INTEGER DEFAULT 0  NOT NULL,
  ban_end INTEGER DEFAULT 0  NOT NULL,
  ban_exclude INTEGER DEFAULT 0  NOT NULL,
  ban_reason BLOB SUB_TYPE TEXT,
  ban_give_reason BLOB SUB_TYPE TEXT
);;

ALTER TABLE phpbb_banlist ADD PRIMARY KEY (ban_id);;

CREATE GENERATOR phpbb_banlist_gen;;
SET GENERATOR phpbb_banlist_gen TO 0;;

CREATE TRIGGER t_phpbb_banlist_gen FOR phpbb_banlist
BEFORE INSERT
AS
BEGIN
  NEW.ban_id = GEN_ID(phpbb_banlist_gen, 1);
END;;

# phpbb_bbcodes
CREATE TABLE phpbb_bbcodes (
  bbcode_id INTEGER DEFAULT 0  NOT NULL,
  bbcode_tag VARCHAR(16) DEFAULT '' NOT NULL,
  display_on_posting INTEGER DEFAULT 0  NOT NULL,
  bbcode_match VARCHAR(255) DEFAULT '' NOT NULL,
  bbcode_tpl BLOB SUB_TYPE TEXT,
  first_pass_match VARCHAR(255) DEFAULT '' NOT NULL,
  first_pass_replace VARCHAR(255) DEFAULT '' NOT NULL,
  second_pass_match VARCHAR(255) DEFAULT '' NOT NULL,
  second_pass_replace BLOB SUB_TYPE TEXT
);;

ALTER TABLE phpbb_bbcodes ADD PRIMARY KEY (bbcode_id);;

CREATE INDEX phpbb_bbcodes_display_on_post ON phpbb_bbcodes(display_on_posting);;


# phpbb_bookmarks
CREATE TABLE phpbb_bookmarks (
  topic_id INTEGER DEFAULT 0  NOT NULL,
  user_id INTEGER DEFAULT 0  NOT NULL,
  order_id INTEGER DEFAULT 0  NOT NULL
);;

CREATE INDEX phpbb_bookmarks_order_id ON phpbb_bookmarks(order_id);;
CREATE INDEX phpbb_bookmarks_topic_user_id ON phpbb_bookmarks(topic_id, user_id);;


# phpbb_bots
CREATE TABLE phpbb_bots (
  bot_id INTEGER NOT NULL,
  bot_active INTEGER DEFAULT 1  NOT NULL,
  bot_name BLOB SUB_TYPE TEXT,
  user_id INTEGER DEFAULT 0  NOT NULL,
  bot_agent VARCHAR(255) DEFAULT '' NOT NULL,
  bot_ip VARCHAR(255) DEFAULT '' NOT NULL
);;

ALTER TABLE phpbb_bots ADD PRIMARY KEY (bot_id);;

CREATE INDEX phpbb_bots_bot_active ON phpbb_bots(bot_active);;

CREATE GENERATOR phpbb_bots_gen;;
SET GENERATOR phpbb_bots_gen TO 0;;

CREATE TRIGGER t_phpbb_bots_gen FOR phpbb_bots
BEFORE INSERT
AS
BEGIN
  NEW.bot_id = GEN_ID(phpbb_bots_gen, 1);
END;;


# phpbb_cache
CREATE TABLE phpbb_cache (
  var_name VARCHAR(252) NOT NULL,
  var_expires INTEGER DEFAULT 0  NOT NULL,
  var_data BLOB SUB_TYPE TEXT
);;

ALTER TABLE phpbb_cache ADD PRIMARY KEY (var_name);;


# phpbb_config
CREATE TABLE phpbb_config (
  config_name VARCHAR(252) NOT NULL,
  config_value VARCHAR(255) NOT NULL,
  is_dynamic INTEGER DEFAULT 0  NOT NULL
);;

ALTER TABLE phpbb_config ADD PRIMARY KEY (config_name);;

CREATE INDEX phpbb_config_is_dynamic ON phpbb_config(is_dynamic);;


# phpbb_confirm
CREATE TABLE phpbb_confirm (
  confirm_id CHAR(32) DEFAULT '' NOT NULL,
  session_id CHAR(32) DEFAULT '' NOT NULL,
  confirm_type INTEGER DEFAULT 0  NOT NULL,
  code VARCHAR(8) DEFAULT '' NOT NULL
);;

ALTER TABLE phpbb_confirm ADD PRIMARY KEY (session_id, confirm_id);;


# phpbb_disallow
CREATE TABLE phpbb_disallow (
  disallow_id INTEGER NOT NULL,
  disallow_username VARCHAR(255) DEFAULT '' NOT NULL
);;

ALTER TABLE phpbb_disallow ADD PRIMARY KEY (disallow_id);;

CREATE GENERATOR phpbb_disallow_gen;;
SET GENERATOR phpbb_disallow_gen TO 0;;

CREATE TRIGGER t_phpbb_disallow_gen FOR phpbb_disallow
BEFORE INSERT
AS
BEGIN
  NEW.disallow_id = GEN_ID(phpbb_disallow_gen, 1);
END;;


# phpbb_drafts
CREATE TABLE phpbb_drafts (
  draft_id INTEGER NOT NULL,
  user_id INTEGER DEFAULT 0  NOT NULL,
  topic_id INTEGER DEFAULT 0  NOT NULL,
  forum_id INTEGER DEFAULT 0  NOT NULL,
  save_time INTEGER DEFAULT 0  NOT NULL,
  draft_subject BLOB SUB_TYPE TEXT,
  draft_message BLOB SUB_TYPE TEXT
);;

ALTER TABLE phpbb_drafts ADD PRIMARY KEY (draft_id);;

CREATE INDEX phpbb_drafts_save_time ON phpbb_drafts(save_time);;

CREATE GENERATOR phpbb_drafts_gen;;
SET GENERATOR phpbb_drafts_gen TO 0;;

CREATE TRIGGER t_phpbb_drafts_gen FOR phpbb_drafts
BEFORE INSERT
AS
BEGIN
  NEW.draft_id = GEN_ID(phpbb_drafts_gen, 1);
END;;


# phpbb_extensions
CREATE TABLE phpbb_extensions (
  extension_id INTEGER NOT NULL,
  group_id INTEGER DEFAULT 0  NOT NULL,
  extension VARCHAR(100) DEFAULT '' NOT NULL
);;

ALTER TABLE phpbb_extensions ADD PRIMARY KEY (extension_id);;

CREATE GENERATOR phpbb_extensions_gen;;
SET GENERATOR phpbb_extensions_gen TO 0;;

CREATE TRIGGER t_phpbb_extensions_gen FOR phpbb_extensions
BEFORE INSERT
AS
BEGIN
  NEW.extension_id = GEN_ID(phpbb_extensions_gen, 1);
END;;


# phpbb_extension_groups
CREATE TABLE phpbb_extension_groups (
  group_id INTEGER NOT NULL,
  group_name VARCHAR(255) NOT NULL,
  cat_id INTEGER DEFAULT 0  NOT NULL,
  allow_group INTEGER DEFAULT 0  NOT NULL,
  download_mode INTEGER DEFAULT 1  NOT NULL,
  upload_icon VARCHAR(255) DEFAULT '' NOT NULL,
  max_filesize INTEGER DEFAULT 0  NOT NULL,
  allowed_forums BLOB SUB_TYPE TEXT,
  allow_in_pm INTEGER DEFAULT 0  NOT NULL
);;

ALTER TABLE phpbb_extension_groups ADD PRIMARY KEY (group_id);;

CREATE GENERATOR phpbb_extension_groups_gen;;
SET GENERATOR phpbb_extension_groups_gen TO 0;;

CREATE TRIGGER t_phpbb_extension_groups_gen FOR phpbb_extension_groups
BEFORE INSERT
AS
BEGIN
  NEW.group_id = GEN_ID(phpbb_extension_groups_gen, 1);
END;;


# phpbb_forums
CREATE TABLE phpbb_forums (
  forum_id INTEGER NOT NULL,
  parent_id INTEGER NOT NULL,
  left_id INTEGER NOT NULL,
  right_id INTEGER NOT NULL,
  forum_parents BLOB SUB_TYPE TEXT,
  forum_name BLOB SUB_TYPE TEXT,
  forum_desc BLOB SUB_TYPE TEXT,
  forum_desc_bitfield INTEGER DEFAULT 0  NOT NULL,
  forum_desc_uid VARCHAR(5) DEFAULT '' NOT NULL,
  forum_link VARCHAR(255) DEFAULT '' NOT NULL,
  forum_password VARCHAR(40) DEFAULT '' NOT NULL,
  forum_style INTEGER,
  forum_image VARCHAR(255) DEFAULT '' NOT NULL,
  forum_rules BLOB SUB_TYPE TEXT,
  forum_rules_link VARCHAR(255) DEFAULT '' NOT NULL,
  forum_rules_bitfield INTEGER DEFAULT 0  NOT NULL,
  forum_rules_uid VARCHAR(5) DEFAULT '' NOT NULL,
  forum_topics_per_page INTEGER DEFAULT 0  NOT NULL,
  forum_type INTEGER DEFAULT 0  NOT NULL,
  forum_status INTEGER DEFAULT 0  NOT NULL,
  forum_posts INTEGER DEFAULT 0  NOT NULL,
  forum_topics INTEGER DEFAULT 0  NOT NULL,
  forum_topics_real INTEGER DEFAULT 0  NOT NULL,
  forum_last_post_id INTEGER DEFAULT 0  NOT NULL,
  forum_last_poster_id INTEGER DEFAULT 0  NOT NULL,
  forum_last_post_time INTEGER DEFAULT 0  NOT NULL,
  forum_last_poster_name VARCHAR(255),
  forum_flags INTEGER DEFAULT 0  NOT NULL,
  display_on_index INTEGER DEFAULT 1  NOT NULL,
  enable_indexing INTEGER DEFAULT 1  NOT NULL,
  enable_icons INTEGER DEFAULT 1  NOT NULL,
  enable_prune INTEGER DEFAULT 0  NOT NULL,
  prune_next INTEGER,
  prune_days INTEGER NOT NULL,
  prune_viewed INTEGER NOT NULL,
  prune_freq INTEGER DEFAULT 0  NOT NULL
);;

ALTER TABLE phpbb_forums ADD PRIMARY KEY (forum_id);;

CREATE INDEX phpbb_forums_forum_lst_post_id ON phpbb_forums(forum_last_post_id);;
CREATE INDEX phpbb_forums_left_right_id ON phpbb_forums(left_id, right_id);;

CREATE GENERATOR phpbb_forums_gen;;
SET GENERATOR phpbb_forums_gen TO 0;;

CREATE TRIGGER t_phpbb_forums_gen FOR phpbb_forums
BEFORE INSERT
AS
BEGIN
  NEW.forum_id = GEN_ID(phpbb_forums_gen, 1);
END;;


# phpbb_forum_access
CREATE TABLE phpbb_forum_access (
  forum_id INTEGER DEFAULT 0  NOT NULL,
  user_id INTEGER DEFAULT 0  NOT NULL,
  session_id VARCHAR(32) DEFAULT '' NOT NULL
);;

ALTER TABLE phpbb_forum_access ADD PRIMARY KEY (forum_id, user_id, session_id);;


# phpbb_forums_marking
CREATE TABLE phpbb_forums_marking (
  user_id INTEGER DEFAULT 0  NOT NULL,
  forum_id INTEGER DEFAULT 0  NOT NULL,
  mark_time INTEGER DEFAULT 0  NOT NULL
);;

ALTER TABLE phpbb_forums_marking ADD PRIMARY KEY (user_id, forum_id);;


# phpbb_forums_watch
CREATE TABLE phpbb_forums_watch (
  forum_id INTEGER DEFAULT 0  NOT NULL,
  user_id INTEGER DEFAULT 0  NOT NULL,
  notify_status INTEGER DEFAULT 0  NOT NULL
);;

CREATE INDEX phpbb_forums_watch_forum_id ON phpbb_forums_watch(forum_id);;
CREATE INDEX phpbb_forums_watch_notify_stat ON phpbb_forums_watch(notify_status);;
CREATE INDEX phpbb_forums_watch_user_id ON phpbb_forums_watch(user_id);;


# phpbb_groups
CREATE TABLE phpbb_groups (
  group_id INTEGER NOT NULL,
  group_type INTEGER DEFAULT 1  NOT NULL,
  group_name VARCHAR(255) DEFAULT '' NOT NULL,
  group_desc BLOB SUB_TYPE TEXT,
  group_desc_bitfield INTEGER DEFAULT 0  NOT NULL,
  group_desc_uid VARCHAR(5) DEFAULT '' NOT NULL,
  group_display INTEGER DEFAULT 0  NOT NULL,
  group_avatar VARCHAR(255) DEFAULT '' NOT NULL,
  group_avatar_type INTEGER DEFAULT 0  NOT NULL,
  group_avatar_width INTEGER DEFAULT 0  NOT NULL,
  group_avatar_height INTEGER DEFAULT 0  NOT NULL,
  group_rank INTEGER DEFAULT -1  NOT NULL,
  group_colour VARCHAR(6) DEFAULT '' NOT NULL,
  group_sig_chars INTEGER DEFAULT 0  NOT NULL,
  group_receive_pm INTEGER DEFAULT 0  NOT NULL,
  group_message_limit INTEGER DEFAULT 0  NOT NULL,
  group_chgpass INTEGER DEFAULT 0  NOT NULL,
  group_legend INTEGER DEFAULT 1  NOT NULL
);;

ALTER TABLE phpbb_groups ADD PRIMARY KEY (group_id);;

CREATE INDEX phpbb_groups_group_legend ON phpbb_groups(group_legend);;

CREATE GENERATOR phpbb_groups_gen;;
SET GENERATOR phpbb_groups_gen TO 0;;

CREATE TRIGGER t_phpbb_groups_gen FOR phpbb_groups
BEFORE INSERT
AS
BEGIN
  NEW.group_id = GEN_ID(phpbb_groups_gen, 1);
END;;


# phpbb_icons
CREATE TABLE phpbb_icons (
  icons_id INTEGER NOT NULL,
  icons_url VARCHAR(255),
  icons_width INTEGER  NOT NULL,
  icons_height INTEGER  NOT NULL,
  icons_order INTEGER  NOT NULL,
  display_on_posting INTEGER DEFAULT 1  NOT NULL
);;

ALTER TABLE phpbb_icons ADD PRIMARY KEY (icons_id);;

CREATE GENERATOR phpbb_icons_gen;;
SET GENERATOR phpbb_icons_gen TO 0;;

CREATE TRIGGER t_phpbb_icons_gen FOR phpbb_icons
BEFORE INSERT
AS
BEGIN
  NEW.icons_id = GEN_ID(phpbb_icons_gen, 1);
END;;


# phpbb_lang
CREATE TABLE phpbb_lang (
  lang_id INTEGER NOT NULL,
  lang_iso VARCHAR(5) NOT NULL,
  lang_dir VARCHAR(30) NOT NULL,
  lang_english_name VARCHAR(100),
  lang_local_name VARCHAR(255),
  lang_author VARCHAR(255)
);;

ALTER TABLE phpbb_lang ADD PRIMARY KEY (lang_id);;

CREATE GENERATOR phpbb_lang_gen;;
SET GENERATOR phpbb_lang_gen TO 0;;

CREATE TRIGGER t_phpbb_lang_gen FOR phpbb_lang
BEFORE INSERT
AS
BEGIN
  NEW.lang_id = GEN_ID(phpbb_lang_gen, 1);
END;;


# phpbb_log
CREATE TABLE phpbb_log (
  log_id INTEGER NOT NULL,
  log_type INTEGER DEFAULT 0  NOT NULL,
  user_id INTEGER DEFAULT 0  NOT NULL,
  forum_id INTEGER DEFAULT 0  NOT NULL,
  topic_id INTEGER DEFAULT 0  NOT NULL,
  reportee_id INTEGER DEFAULT 0  NOT NULL,
  log_ip VARCHAR(40) NOT NULL,
  log_time INTEGER  NOT NULL,
  log_operation BLOB SUB_TYPE TEXT,
  log_data BLOB SUB_TYPE TEXT
);;

ALTER TABLE phpbb_log ADD PRIMARY KEY (log_id);;

CREATE INDEX phpbb_log_forum_id ON phpbb_log(forum_id);;
CREATE INDEX phpbb_log_log_type ON phpbb_log(log_type);;
CREATE INDEX phpbb_log_reportee_id ON phpbb_log(reportee_id);;
CREATE INDEX phpbb_log_topic_id ON phpbb_log(topic_id);;
CREATE INDEX phpbb_log_user_id ON phpbb_log(user_id);;

CREATE GENERATOR phpbb_log_gen;;
SET GENERATOR phpbb_log_gen TO 0;;

CREATE TRIGGER t_phpbb_log_gen FOR phpbb_log
BEFORE INSERT
AS
BEGIN
  NEW.log_id = GEN_ID(phpbb_log_gen, 1);
END;;


# phpbb_moderator_cache
CREATE TABLE phpbb_moderator_cache (
  forum_id INTEGER  NOT NULL,
  user_id INTEGER DEFAULT 0  NOT NULL,
  username VARCHAR(255) DEFAULT '' NOT NULL,
  group_id INTEGER DEFAULT 0  NOT NULL,
  group_name VARCHAR(255) DEFAULT '' NOT NULL,
  display_on_index INTEGER DEFAULT 1  NOT NULL
);;

CREATE INDEX phpbb_moderator_cche_dis_on_idx ON phpbb_moderator_cache(display_on_index);;
CREATE INDEX phpbb_moderator_cache_forum_id ON phpbb_moderator_cache(forum_id);;


# phpbb_modules
CREATE TABLE phpbb_modules (
  module_id INTEGER NOT NULL,
  module_enabled INTEGER DEFAULT 1  NOT NULL,
  module_display INTEGER DEFAULT 1  NOT NULL,
  "module_name" VARCHAR(255) DEFAULT '' NOT NULL,
  module_class VARCHAR(10) DEFAULT '' NOT NULL,
  parent_id INTEGER DEFAULT 0 NOT NULL,
  left_id INTEGER DEFAULT 0 NOT NULL,
  right_id INTEGER DEFAULT 0 NOT NULL,
  module_langname VARCHAR(255) DEFAULT '' NOT NULL,
  module_mode VARCHAR(255) DEFAULT '' NOT NULL,
  module_auth VARCHAR(255) DEFAULT '' NOT NULL
);;

ALTER TABLE phpbb_modules ADD PRIMARY KEY (module_id);;

CREATE INDEX phpbb_modules_module_enabled ON phpbb_modules(module_enabled);;
CREATE INDEX phpbb_modules_left_right_id ON phpbb_modules(left_id, right_id);;

CREATE GENERATOR phpbb_modules_gen;;
SET GENERATOR phpbb_modules_gen TO 0;;

CREATE TRIGGER t_phpbb_modules_gen FOR phpbb_modules
BEFORE INSERT
AS
BEGIN
  NEW.module_id = GEN_ID(phpbb_modules_gen, 1);
END;;


# phpbb_poll_results
CREATE TABLE phpbb_poll_results (
  poll_option_id INTEGER DEFAULT 0  NOT NULL,
  topic_id INTEGER NOT NULL,
  poll_option_text BLOB SUB_TYPE TEXT,
  poll_option_total INTEGER DEFAULT 0  NOT NULL
);;

CREATE INDEX phpbb_poll_results_poll_opt_id ON phpbb_poll_results(poll_option_id);;
CREATE INDEX phpbb_poll_results_topic_id ON phpbb_poll_results(topic_id);;


# phpbb_poll_voters
CREATE TABLE phpbb_poll_voters (
  topic_id INTEGER DEFAULT 0  NOT NULL,
  poll_option_id INTEGER DEFAULT 0  NOT NULL,
  vote_user_id INTEGER DEFAULT 0  NOT NULL,
  vote_user_ip VARCHAR(40) NOT NULL
);;

CREATE INDEX phpbb_poll_voters_vote_user_id ON phpbb_poll_voters(vote_user_id);;
CREATE INDEX phpbb_poll_voters_vote_user_ip ON phpbb_poll_voters(vote_user_ip);;


# phpbb_posts
CREATE TABLE phpbb_posts (
  post_id INTEGER NOT NULL,
  topic_id INTEGER DEFAULT 0  NOT NULL,
  forum_id INTEGER DEFAULT 0  NOT NULL,
  poster_id INTEGER DEFAULT 0  NOT NULL,
  icon_id INTEGER DEFAULT 0  NOT NULL,
  poster_ip VARCHAR(40) NOT NULL,
  post_time INTEGER DEFAULT 0  NOT NULL,
  post_approved INTEGER DEFAULT 1  NOT NULL,
  post_reported INTEGER DEFAULT 0  NOT NULL,
  enable_bbcode INTEGER DEFAULT 1  NOT NULL,
  enable_smilies INTEGER DEFAULT 1  NOT NULL,
  enable_magic_url INTEGER DEFAULT 1  NOT NULL,
  enable_sig INTEGER DEFAULT 1  NOT NULL,
  post_username VARCHAR(255),
  post_subject BLOB SUB_TYPE TEXT NOT NULL,
  post_text BLOB SUB_TYPE TEXT NOT NULL,
  post_checksum VARCHAR(32) NOT NULL,
  post_encoding VARCHAR(20) DEFAULT 'iso-8859-1'  NOT NULL,
  post_attachment INTEGER DEFAULT 0  NOT NULL,
  bbcode_bitfield INTEGER DEFAULT 0  NOT NULL,
  bbcode_uid VARCHAR(5) DEFAULT '' NOT NULL,
  post_edit_time INTEGER DEFAULT 0,
  post_edit_reason BLOB SUB_TYPE TEXT,
  post_edit_user INTEGER DEFAULT 0,
  post_edit_count INTEGER DEFAULT 0,
  post_edit_locked INTEGER DEFAULT 0
);;

ALTER TABLE phpbb_posts ADD PRIMARY KEY (post_id);;

CREATE INDEX phpbb_posts_forum_id ON phpbb_posts(forum_id);;
CREATE INDEX phpbb_posts_post_approved ON phpbb_posts(post_approved);;
CREATE INDEX phpbb_posts_post_time ON phpbb_posts(post_time);;
CREATE INDEX phpbb_posts_poster_id ON phpbb_posts(poster_id);;
CREATE INDEX phpbb_posts_poster_ip ON phpbb_posts(poster_ip);;
CREATE INDEX phpbb_posts_topic_id ON phpbb_posts(topic_id);;

CREATE GENERATOR phpbb_posts_gen;;
SET GENERATOR phpbb_posts_gen TO 0;;

CREATE TRIGGER t_phpbb_posts_gen FOR phpbb_posts
BEFORE INSERT
AS
BEGIN
  NEW.post_id = GEN_ID(phpbb_posts_gen, 1);
END;;


# phpbb_privmsgs
CREATE TABLE phpbb_privmsgs (
  msg_id INTEGER NOT NULL,
  root_level INTEGER DEFAULT 0  NOT NULL,
  author_id INTEGER DEFAULT 0  NOT NULL,
  icon_id INTEGER DEFAULT 0  NOT NULL,
  author_ip VARCHAR(40) DEFAULT '' NOT NULL,
  message_time INTEGER DEFAULT 0  NOT NULL,
  enable_bbcode INTEGER DEFAULT 1  NOT NULL,
  enable_smilies INTEGER DEFAULT 1  NOT NULL,
  enable_magic_url INTEGER DEFAULT 1  NOT NULL,
  enable_sig INTEGER DEFAULT 1  NOT NULL,
  message_subject BLOB SUB_TYPE TEXT  NOT NULL,
  message_text BLOB SUB_TYPE TEXT  NOT NULL,
  message_edit_reason BLOB SUB_TYPE TEXT,
  message_edit_user INTEGER DEFAULT 0,
  message_encoding VARCHAR(20) DEFAULT 'iso-8859-1'  NOT NULL,
  message_attachment INTEGER DEFAULT 0  NOT NULL,
  bbcode_bitfield INTEGER DEFAULT 0  NOT NULL,
  bbcode_uid VARCHAR(5) DEFAULT '' NOT NULL,
  message_edit_time INTEGER DEFAULT 0,
  message_edit_count INTEGER DEFAULT 0,
  to_address BLOB SUB_TYPE TEXT  NOT NULL,
  bcc_address BLOB SUB_TYPE TEXT  NOT NULL
);;

ALTER TABLE phpbb_privmsgs ADD PRIMARY KEY (msg_id);;

CREATE INDEX phpbb_privmsgs_author_id ON phpbb_privmsgs(author_id);;
CREATE INDEX phpbb_privmsgs_author_ip ON phpbb_privmsgs(author_ip);;
CREATE INDEX phpbb_privmsgs_message_time ON phpbb_privmsgs(message_time);;
CREATE INDEX phpbb_privmsgs_root_level ON phpbb_privmsgs(root_level);;

CREATE GENERATOR phpbb_privmsgs_gen;;
SET GENERATOR phpbb_privmsgs_gen TO 0;;

CREATE TRIGGER t_phpbb_privmsgs_gen FOR phpbb_privmsgs
BEFORE INSERT
AS
BEGIN
  NEW.msg_id = GEN_ID(phpbb_privmsgs_gen, 1);
END;;


# phpbb_privmsgs_folder
CREATE TABLE phpbb_privmsgs_folder (
  folder_id INTEGER NOT NULL,
  user_id INTEGER DEFAULT 0  NOT NULL,
  folder_name VARCHAR(255) DEFAULT '' NOT NULL,
  pm_count INTEGER DEFAULT 0  NOT NULL
);;

ALTER TABLE phpbb_privmsgs_folder ADD PRIMARY KEY (folder_id);;

CREATE INDEX phpbb_privmsgs_folder_user_id ON phpbb_privmsgs_folder(user_id);;

CREATE GENERATOR phpbb_privmsgs_folder_gen;;
SET GENERATOR phpbb_privmsgs_folder_gen TO 0;;

CREATE TRIGGER t_phpbb_privmsgs_folder_gen FOR phpbb_privmsgs_folder
BEFORE INSERT
AS
BEGIN
  NEW.folder_id = GEN_ID(phpbb_privmsgs_folder_gen, 1);
END;;


# phpbb_privmsgs_rules
CREATE TABLE phpbb_privmsgs_rules (
  rule_id INTEGER NOT NULL,
  user_id INTEGER DEFAULT 0  NOT NULL,
  rule_check INTEGER DEFAULT 0  NOT NULL,
  rule_connection INTEGER DEFAULT 0  NOT NULL,
  rule_string VARCHAR(255) DEFAULT '' NOT NULL,
  rule_user_id INTEGER DEFAULT 0  NOT NULL,
  rule_group_id INTEGER DEFAULT 0  NOT NULL,
  rule_action INTEGER DEFAULT 0  NOT NULL,
  rule_folder_id INTEGER DEFAULT 0  NOT NULL
);;

ALTER TABLE phpbb_privmsgs_rules ADD PRIMARY KEY (rule_id);;

CREATE GENERATOR phpbb_privmsgs_rules_gen;;
SET GENERATOR phpbb_privmsgs_rules_gen TO 0;;

CREATE TRIGGER t_phpbb_privmsgs_rules_gen FOR phpbb_privmsgs_rules
BEFORE INSERT
AS
BEGIN
  NEW.rule_id = GEN_ID(phpbb_privmsgs_rules_gen, 1);
END;;


# phpbb_privmsgs_to
CREATE TABLE phpbb_privmsgs_to (
  msg_id INTEGER DEFAULT 0  NOT NULL,
  user_id INTEGER DEFAULT 0  NOT NULL,
  author_id INTEGER DEFAULT 0  NOT NULL,
  deleted INTEGER DEFAULT 0  NOT NULL,
  new INTEGER DEFAULT 1  NOT NULL,
  unread INTEGER DEFAULT 1  NOT NULL,
  replied INTEGER DEFAULT 0  NOT NULL,
  marked INTEGER DEFAULT 0  NOT NULL,
  forwarded INTEGER DEFAULT 0  NOT NULL,
  folder_id INTEGER DEFAULT 0  NOT NULL
);;

CREATE INDEX phpbb_privmsgs_to_msg_id ON phpbb_privmsgs_to(msg_id);;
CREATE INDEX phpbb_privmsgs_to_user_id ON phpbb_privmsgs_to(user_id, folder_id);;


# phpbb_profile_fields
CREATE TABLE phpbb_profile_fields (
  field_id INTEGER NOT NULL,
  field_name VARCHAR(255) NOT NULL,
  field_type INTEGER NOT NULL,
  field_ident VARCHAR(20) DEFAULT '' NOT NULL,
  field_length VARCHAR(20) DEFAULT '' NOT NULL,
  field_minlen VARCHAR(255) DEFAULT '' NOT NULL,
  field_maxlen VARCHAR(255) DEFAULT '' NOT NULL,
  field_novalue VARCHAR(255) DEFAULT '' NOT NULL,
  field_default_value VARCHAR(255) DEFAULT '0'  NOT NULL,
  field_validation VARCHAR(20) DEFAULT '' NOT NULL,
  field_required INTEGER DEFAULT 0  NOT NULL,
  field_show_on_reg INTEGER DEFAULT 0  NOT NULL,
  field_hide INTEGER DEFAULT 0  NOT NULL,
  field_no_view INTEGER DEFAULT 0  NOT NULL,
  field_active INTEGER DEFAULT 0  NOT NULL,
  field_order INTEGER DEFAULT 0  NOT NULL
);;

ALTER TABLE phpbb_profile_fields ADD PRIMARY KEY (field_id);;

CREATE INDEX phpbb_profile_fields_field_ord ON phpbb_profile_fields(field_order);;
CREATE INDEX phpbb_profile_fields_field_type ON phpbb_profile_fields(field_type);;

CREATE GENERATOR phpbb_profile_fields_gen;;
SET GENERATOR phpbb_profile_fields_gen TO 0;;

CREATE TRIGGER t_phpbb_profile_fields_gen FOR phpbb_profile_fields
BEFORE INSERT
AS
BEGIN
  NEW.field_id = GEN_ID(phpbb_profile_fields_gen, 1);
END;;


# phpbb_profile_fields_data
CREATE TABLE phpbb_profile_fields_data (
  user_id INTEGER DEFAULT 0  NOT NULL
);;

ALTER TABLE phpbb_profile_fields_data ADD PRIMARY KEY (user_id);;


# phpbb_profile_fields_lang
CREATE TABLE phpbb_profile_fields_lang (
  field_id INTEGER DEFAULT 0  NOT NULL,
  lang_id INTEGER DEFAULT 0  NOT NULL,
  option_id INTEGER DEFAULT 0  NOT NULL,
  field_type INTEGER DEFAULT 0  NOT NULL,
  "value" VARCHAR(255) DEFAULT '' NOT NULL
);;

ALTER TABLE phpbb_profile_fields_lang ADD PRIMARY KEY (field_id, lang_id, option_id);;


# phpbb_profile_lang
CREATE TABLE phpbb_profile_lang (
  field_id INTEGER DEFAULT 0  NOT NULL,
  lang_id INTEGER DEFAULT 0  NOT NULL,
  lang_name VARCHAR(255) DEFAULT '' NOT NULL,
  lang_explain BLOB SUB_TYPE TEXT,
  lang_default_value VARCHAR(255) DEFAULT '' NOT NULL
);;

ALTER TABLE phpbb_profile_lang ADD PRIMARY KEY (field_id, lang_id);;


# phpbb_ranks
CREATE TABLE phpbb_ranks (
  rank_id INTEGER NOT NULL,
  rank_title VARCHAR(255) NOT NULL,
  rank_min INTEGER DEFAULT 0  NOT NULL,
  rank_special INTEGER DEFAULT 0 ,
  rank_image VARCHAR(255)
);;

ALTER TABLE phpbb_ranks ADD PRIMARY KEY (rank_id);;

CREATE GENERATOR phpbb_ranks_gen;;
SET GENERATOR phpbb_ranks_gen TO 0;;

CREATE TRIGGER t_phpbb_ranks_gen FOR phpbb_ranks
BEFORE INSERT
AS
BEGIN
  NEW.rank_id = GEN_ID(phpbb_ranks_gen, 1);
END;;


# phpbb_reports
CREATE TABLE phpbb_reports (
  report_id INTEGER NOT NULL,
  reason_id INTEGER DEFAULT 0  NOT NULL,
  post_id INTEGER DEFAULT 0  NOT NULL,
  user_id INTEGER DEFAULT 0  NOT NULL,
  user_notify INTEGER DEFAULT 0  NOT NULL,
  report_closed INTEGER DEFAULT 0  NOT NULL,
  report_time INTEGER DEFAULT 0  NOT NULL,
  report_text BLOB SUB_TYPE TEXT
);;

ALTER TABLE phpbb_reports ADD PRIMARY KEY (report_id);;

CREATE GENERATOR phpbb_reports_gen;;
SET GENERATOR phpbb_reports_gen TO 0;;

CREATE TRIGGER t_phpbb_reports_gen FOR phpbb_reports
BEFORE INSERT
AS
BEGIN
  NEW.report_id = GEN_ID(phpbb_reports_gen, 1);
END;;


# phpbb_reports_reasons
CREATE TABLE phpbb_reports_reasons (
  reason_id INTEGER NOT NULL,
  reason_title VARCHAR(255) DEFAULT '' NOT NULL,
  reason_description BLOB SUB_TYPE TEXT,
  reason_order INTEGER DEFAULT 0  NOT NULL
);;

ALTER TABLE phpbb_reports_reasons ADD PRIMARY KEY (reason_id);;

CREATE GENERATOR phpbb_reports_reasons_gen;;
SET GENERATOR phpbb_reports_reasons_gen TO 0;;

CREATE TRIGGER t_phpbb_reports_reasons_gen FOR phpbb_reports_reasons
BEFORE INSERT
AS
BEGIN
  NEW.reason_id = GEN_ID(phpbb_reports_reasons_gen, 1);
END;;


# phpbb_search_results
CREATE TABLE phpbb_search_results (
  search_key VARCHAR(32) DEFAULT '' NOT NULL,
  search_time INTEGER DEFAULT 0  NOT NULL,
  search_keywords BLOB SUB_TYPE TEXT,
  search_authors BLOB SUB_TYPE TEXT
);;

ALTER TABLE phpbb_search_results ADD PRIMARY KEY (search_key);;


# phpbb_search_wordlist
CREATE TABLE phpbb_search_wordlist (
  word_text VARCHAR(252) DEFAULT '' NOT NULL,
  word_id INTEGER NOT NULL,
  word_common INTEGER DEFAULT 0  NOT NULL
);;

ALTER TABLE phpbb_search_wordlist ADD PRIMARY KEY (word_text);;

CREATE INDEX phpbb_search_wordlist_word_id ON phpbb_search_wordlist(word_id);;

CREATE GENERATOR phpbb_search_wordlist_gen;;
SET GENERATOR phpbb_search_wordlist_gen TO 0;;

CREATE TRIGGER t_phpbb_search_wordlist_gen FOR phpbb_search_wordlist
BEFORE INSERT
AS
BEGIN
  NEW.word_id = GEN_ID(phpbb_search_wordlist_gen, 1);
END;;


# phpbb_search_wordmatch
CREATE TABLE phpbb_search_wordmatch (
  post_id INTEGER DEFAULT 0  NOT NULL,
  word_id INTEGER DEFAULT 0  NOT NULL,
  title_match INTEGER DEFAULT 0  NOT NULL
);;

CREATE INDEX phpbb_search_wordmatch_word_id ON phpbb_search_wordmatch(word_id);;

# phpbb_sessions
CREATE TABLE phpbb_sessions (
  session_id VARCHAR(32) DEFAULT '' NOT NULL,
  session_user_id INTEGER DEFAULT 0  NOT NULL,
  session_last_visit INTEGER DEFAULT 0  NOT NULL,
  session_start INTEGER DEFAULT 0  NOT NULL,
  session_time INTEGER DEFAULT 0  NOT NULL,
  session_ip VARCHAR(40) DEFAULT '0'  NOT NULL,
  session_browser VARCHAR(150) DEFAULT '' NOT NULL,
  session_page VARCHAR(200) DEFAULT '' NOT NULL,
  session_viewonline INTEGER DEFAULT 1  NOT NULL,
  session_autologin INTEGER DEFAULT 0  NOT NULL,
  session_admin INTEGER DEFAULT 0  NOT NULL
);;

ALTER TABLE phpbb_sessions ADD PRIMARY KEY (session_id);;

CREATE INDEX phpbb_sessions_session_time ON phpbb_sessions(session_time);;
CREATE INDEX phpbb_sessions_session_user_id ON phpbb_sessions(session_user_id);;


# phpbb_sessions_keys
CREATE TABLE phpbb_sessions_keys (
  key_id VARCHAR(32) DEFAULT '' NOT NULL,
  user_id INTEGER DEFAULT 0  NOT NULL,
  last_ip VARCHAR(40) DEFAULT '0'  NOT NULL,
  last_login INTEGER DEFAULT 0  NOT NULL
);;

ALTER TABLE phpbb_sessions_keys ADD PRIMARY KEY (key_id, user_id);;

CREATE INDEX phpbb_sessions_keys_last_login ON phpbb_sessions_keys(last_login);;


# phpbb_sitelist
CREATE TABLE phpbb_sitelist (
  site_id INTEGER NOT NULL,
  site_ip VARCHAR(40) DEFAULT '' NOT NULL,
  site_hostname VARCHAR(255) DEFAULT '' NOT NULL,
  ip_exclude INTEGER DEFAULT 0  NOT NULL
);;

ALTER TABLE phpbb_sitelist ADD PRIMARY KEY (site_id);;

CREATE GENERATOR phpbb_sitelist_gen;;
SET GENERATOR phpbb_sitelist_gen TO 0;;

CREATE TRIGGER t_phpbb_sitelist_gen FOR phpbb_sitelist
BEFORE INSERT
AS
BEGIN
  NEW.site_id = GEN_ID(phpbb_sitelist_gen, 1);
END;;


# phpbb_smilies
CREATE TABLE phpbb_smilies (
  smiley_id INTEGER NOT NULL,
  code VARCHAR(50),
  emotion VARCHAR(50),
  smiley_url VARCHAR(50),
  smiley_width INTEGER NOT NULL,
  smiley_height INTEGER NOT NULL,
  smiley_order INTEGER NOT NULL,
  display_on_posting INTEGER DEFAULT 1  NOT NULL
);;

ALTER TABLE phpbb_smilies ADD PRIMARY KEY (smiley_id);;

CREATE GENERATOR phpbb_smilies_gen;;
SET GENERATOR phpbb_smilies_gen TO 0;;

CREATE TRIGGER t_phpbb_smilies_gen FOR phpbb_smilies
BEFORE INSERT
AS
BEGIN
  NEW.smiley_id = GEN_ID(phpbb_smilies_gen, 1);
END;;


# phpbb_styles
CREATE TABLE phpbb_styles (
  style_id INTEGER NOT NULL,
  style_name VARCHAR(252) DEFAULT '' NOT NULL,
  style_copyright VARCHAR(255) DEFAULT '' NOT NULL,
  style_active INTEGER DEFAULT 1  NOT NULL,
  template_id INTEGER NOT NULL,
  theme_id INTEGER NOT NULL,
  imageset_id INTEGER NOT NULL
);;

# phpbb_styles_template
CREATE TABLE phpbb_styles_template (
  template_id INTEGER NOT NULL,
  template_name VARCHAR(252) NOT NULL,
  template_copyright VARCHAR(255) NOT NULL,
  template_path VARCHAR(100) NOT NULL,
  bbcode_bitfield INTEGER DEFAULT 6921  NOT NULL,
  template_storedb INTEGER DEFAULT 0  NOT NULL
);;

ALTER TABLE phpbb_styles_template ADD PRIMARY KEY (template_id);;

CREATE UNIQUE INDEX phpbb_styles_template_tmplte_nm ON phpbb_styles_template(template_name);;


# phpbb_styles_template_data
CREATE TABLE phpbb_styles_template_data (
  template_id INTEGER NOT NULL,
  template_filename VARCHAR(100) DEFAULT '' NOT NULL,
  template_included BLOB SUB_TYPE TEXT,
  template_mtime INTEGER DEFAULT 0  NOT NULL,
  template_data BLOB SUB_TYPE TEXT
);;

CREATE INDEX phpbb_styles_tmplte_d_tmpl_flnm ON phpbb_styles_template_data(template_filename);;
CREATE INDEX phpbb_styles_tmplte_dt_tmplt_id ON phpbb_styles_template_data(template_id);;

CREATE GENERATOR phpbb_styles_templte_data_gen;;
SET GENERATOR phpbb_styles_templte_data_gen TO 0;;

CREATE TRIGGER t_phpbb_styles_templte_data_gen FOR phpbb_styles_template
BEFORE INSERT
AS
BEGIN
  NEW.template_id = GEN_ID(phpbb_styles_templte_data_gen, 1);
END;;


# phpbb_styles_theme
CREATE TABLE phpbb_styles_theme (
  theme_id INTEGER NOT NULL,
  theme_name VARCHAR(252) DEFAULT '' NOT NULL,
  theme_copyright VARCHAR(255) DEFAULT '' NOT NULL,
  theme_path VARCHAR(100) DEFAULT '' NOT NULL,
  theme_storedb INTEGER DEFAULT 0  NOT NULL,
  theme_mtime INTEGER DEFAULT 0  NOT NULL,
  theme_data BLOB SUB_TYPE TEXT
);;

ALTER TABLE phpbb_styles_theme ADD PRIMARY KEY (theme_id);;

CREATE UNIQUE INDEX phpbb_styles_theme_theme_name ON phpbb_styles_theme(theme_name);;

CREATE GENERATOR phpbb_styles_theme_gen;;
SET GENERATOR phpbb_styles_theme_gen TO 0;;

CREATE TRIGGER t_phpbb_styles_theme_gen FOR phpbb_styles_theme
BEFORE INSERT
AS
BEGIN
  NEW.theme_id = GEN_ID(phpbb_styles_theme_gen, 1);
END;;

ALTER TABLE phpbb_styles ADD PRIMARY KEY (style_id);;

CREATE UNIQUE INDEX phpbb_styles_style_name ON phpbb_styles(style_name);;
CREATE INDEX phpbb_styles_imageset_id ON phpbb_styles(imageset_id);;
CREATE INDEX phpbb_styles_template_id ON phpbb_styles(template_id);;
CREATE INDEX phpbb_styles_theme_id ON phpbb_styles(theme_id);;

CREATE GENERATOR phpbb_styles_gen;;
SET GENERATOR phpbb_styles_gen TO 0;;

CREATE TRIGGER t_phpbb_styles_gen FOR phpbb_styles
BEFORE INSERT
AS
BEGIN
  NEW.style_id = GEN_ID(phpbb_styles_gen, 1);
END;;


# phpbb_styles_imageset
CREATE TABLE phpbb_styles_imageset (
  imageset_id INTEGER NOT NULL,
  imageset_name VARCHAR(252) DEFAULT '' NOT NULL,
  imageset_copyright VARCHAR(255) DEFAULT '' NOT NULL,
  imageset_path VARCHAR(100) DEFAULT '' NOT NULL,
  site_logo VARCHAR(200) DEFAULT '' NOT NULL,
  btn_post VARCHAR(200) DEFAULT '' NOT NULL,
  btn_post_pm VARCHAR(200) DEFAULT '' NOT NULL,
  btn_reply VARCHAR(200) DEFAULT '' NOT NULL,
  btn_reply_pm VARCHAR(200) DEFAULT '' NOT NULL,
  btn_locked VARCHAR(200) DEFAULT '' NOT NULL,
  btn_profile VARCHAR(200) DEFAULT '' NOT NULL,
  btn_pm VARCHAR(200) DEFAULT '' NOT NULL,
  btn_delete VARCHAR(200) DEFAULT '' NOT NULL,
  btn_info VARCHAR(200) DEFAULT '' NOT NULL,
  btn_quote VARCHAR(200) DEFAULT '' NOT NULL,
  btn_search VARCHAR(200) DEFAULT '' NOT NULL,
  btn_edit VARCHAR(200) DEFAULT '' NOT NULL,
  btn_report VARCHAR(200) DEFAULT '' NOT NULL,
  btn_email VARCHAR(200) DEFAULT '' NOT NULL,
  btn_www VARCHAR(200) DEFAULT '' NOT NULL,
  btn_icq VARCHAR(200) DEFAULT '' NOT NULL,
  btn_aim VARCHAR(200) DEFAULT '' NOT NULL,
  btn_yim VARCHAR(200) DEFAULT '' NOT NULL,
  btn_msnm VARCHAR(200) DEFAULT '' NOT NULL,
  btn_jabber VARCHAR(200) DEFAULT '' NOT NULL,
  btn_online VARCHAR(200) DEFAULT '' NOT NULL,
  btn_offline VARCHAR(200) DEFAULT '' NOT NULL,
  btn_friend VARCHAR(200) DEFAULT '' NOT NULL,
  btn_foe VARCHAR(200) DEFAULT '' NOT NULL,
  icon_unapproved VARCHAR(200) DEFAULT '' NOT NULL,
  icon_reported VARCHAR(200) DEFAULT '' NOT NULL,
  icon_attach VARCHAR(200) DEFAULT '' NOT NULL,
  icon_post VARCHAR(200) DEFAULT '' NOT NULL,
  icon_post_new VARCHAR(200) DEFAULT '' NOT NULL,
  icon_post_latest VARCHAR(200) DEFAULT '' NOT NULL,
  icon_post_newest VARCHAR(200) DEFAULT '' NOT NULL,
  forum VARCHAR(200) DEFAULT '' NOT NULL,
  forum_new VARCHAR(200) DEFAULT '' NOT NULL,
  forum_locked VARCHAR(200) DEFAULT '' NOT NULL,
  forum_link VARCHAR(200) DEFAULT '' NOT NULL,
  sub_forum VARCHAR(200) DEFAULT '' NOT NULL,
  sub_forum_new VARCHAR(200) DEFAULT '' NOT NULL,
  folder VARCHAR(200) DEFAULT '' NOT NULL,
  folder_moved VARCHAR(200) DEFAULT '' NOT NULL,
  folder_posted VARCHAR(200) DEFAULT '' NOT NULL,
  folder_new VARCHAR(200) DEFAULT '' NOT NULL,
  folder_new_posted VARCHAR(200) DEFAULT '' NOT NULL,
  folder_hot VARCHAR(200) DEFAULT '' NOT NULL,
  folder_hot_posted VARCHAR(200) DEFAULT '' NOT NULL,
  folder_hot_new VARCHAR(200) DEFAULT '' NOT NULL,
  folder_hot_new_posted VARCHAR(200) DEFAULT '' NOT NULL,
  folder_locked VARCHAR(200) DEFAULT '' NOT NULL,
  folder_locked_posted VARCHAR(200) DEFAULT '' NOT NULL,
  folder_locked_new VARCHAR(200) DEFAULT '' NOT NULL,
  folder_locked_new_posted VARCHAR(200) DEFAULT '' NOT NULL,
  folder_sticky VARCHAR(200) DEFAULT '' NOT NULL,
  folder_sticky_posted VARCHAR(200) DEFAULT '' NOT NULL,
  folder_sticky_new VARCHAR(200) DEFAULT '' NOT NULL,
  folder_sticky_new_posted VARCHAR(200) DEFAULT '' NOT NULL,
  folder_announce VARCHAR(200) DEFAULT '' NOT NULL,
  folder_announce_posted VARCHAR(200) DEFAULT '' NOT NULL,
  folder_announce_new VARCHAR(200) DEFAULT '' NOT NULL,
  folder_announce_new_posted VARCHAR(200) DEFAULT '' NOT NULL,
  folder_global VARCHAR(200) DEFAULT '' NOT NULL,
  folder_global_posted VARCHAR(200) DEFAULT '' NOT NULL,
  folder_global_new VARCHAR(200) DEFAULT '' NOT NULL,
  folder_global_new_posted VARCHAR(200) DEFAULT '' NOT NULL,
  poll_left VARCHAR(200) DEFAULT '' NOT NULL,
  poll_center VARCHAR(200) DEFAULT '' NOT NULL,
  poll_right VARCHAR(200) DEFAULT '' NOT NULL,
  attach_progress_bar VARCHAR(200) DEFAULT '' NOT NULL,
  user_icon1 VARCHAR(200) DEFAULT '' NOT NULL,
  user_icon2 VARCHAR(200) DEFAULT '' NOT NULL,
  user_icon3 VARCHAR(200) DEFAULT '' NOT NULL,
  user_icon4 VARCHAR(200) DEFAULT '' NOT NULL,
  user_icon5 VARCHAR(200) DEFAULT '' NOT NULL,
  user_icon6 VARCHAR(200) DEFAULT '' NOT NULL,
  user_icon7 VARCHAR(200) DEFAULT '' NOT NULL,
  user_icon8 VARCHAR(200) DEFAULT '' NOT NULL,
  user_icon9 VARCHAR(200) DEFAULT '' NOT NULL,
  user_icon10 VARCHAR(200) DEFAULT '' NOT NULL
);;

ALTER TABLE phpbb_styles_imageset ADD PRIMARY KEY (imageset_id);;

CREATE UNIQUE INDEX phpbb_styles_imageset_imgset_nm ON phpbb_styles_imageset(imageset_name);;

CREATE GENERATOR phpbb_styles_imageset_gen;;
SET GENERATOR phpbb_styles_imageset_gen TO 0;;

CREATE TRIGGER t_phpbb_styles_imageset_gen FOR phpbb_styles_imageset
BEFORE INSERT
AS
BEGIN
  NEW.imageset_id = GEN_ID(phpbb_styles_imageset_gen, 1);
END;;

# phpbb_topics
CREATE TABLE phpbb_topics (
  topic_id INTEGER NOT NULL,
  forum_id INTEGER DEFAULT 0  NOT NULL,
  icon_id INTEGER DEFAULT 1  NOT NULL,
  topic_attachment INTEGER DEFAULT 0  NOT NULL,
  topic_approved INTEGER DEFAULT 1  NOT NULL,
  topic_reported INTEGER DEFAULT 0  NOT NULL,
  topic_title BLOB SUB_TYPE TEXT,
  topic_poster INTEGER DEFAULT 0  NOT NULL,
  topic_time INTEGER DEFAULT 0  NOT NULL,
  topic_time_limit INTEGER DEFAULT 0  NOT NULL,
  topic_views INTEGER DEFAULT 0  NOT NULL,
  topic_replies INTEGER DEFAULT 0  NOT NULL,
  topic_replies_real INTEGER DEFAULT 0  NOT NULL,
  topic_status INTEGER DEFAULT 0  NOT NULL,
  topic_type INTEGER DEFAULT 0  NOT NULL,
  topic_first_post_id INTEGER DEFAULT 0  NOT NULL,
  topic_first_poster_name VARCHAR(255),
  topic_last_post_id INTEGER DEFAULT 0  NOT NULL,
  topic_last_poster_id INTEGER DEFAULT 0  NOT NULL,
  topic_last_poster_name VARCHAR(255),
  topic_last_post_time INTEGER DEFAULT 0  NOT NULL,
  topic_last_view_time INTEGER DEFAULT 0  NOT NULL,
  topic_moved_id INTEGER DEFAULT 0  NOT NULL,
  topic_bumped INTEGER DEFAULT 0  NOT NULL,
  topic_bumper INTEGER DEFAULT 0  NOT NULL,
  poll_title BLOB SUB_TYPE TEXT,
  poll_start INTEGER DEFAULT 0  NOT NULL,
  poll_length INTEGER DEFAULT 0  NOT NULL,
  poll_max_options INTEGER DEFAULT 1  NOT NULL,
  poll_last_vote INTEGER DEFAULT 0 ,
  poll_vote_change INTEGER DEFAULT 0  NOT NULL
);;

ALTER TABLE phpbb_topics ADD PRIMARY KEY (topic_id);;

CREATE INDEX phpbb_topics_forum_id ON phpbb_topics(forum_id);;
CREATE INDEX phpbb_topics_forum_id_type ON phpbb_topics(forum_id, topic_type);;
CREATE INDEX phpbb_topics_topic_last_pst_tme ON phpbb_topics(topic_last_post_time);;

CREATE GENERATOR phpbb_topics_gen;;
SET GENERATOR phpbb_topics_gen TO 0;;

CREATE TRIGGER t_phpbb_topics_gen FOR phpbb_topics
BEFORE INSERT
AS
BEGIN
  NEW.topic_id = GEN_ID(phpbb_topics_gen, 1);
END;;


# phpbb_topics_marking
CREATE TABLE phpbb_topics_marking (
  user_id INTEGER DEFAULT 0  NOT NULL,
  topic_id INTEGER DEFAULT 0  NOT NULL,
  forum_id INTEGER DEFAULT 0  NOT NULL,
  mark_time INTEGER DEFAULT 0  NOT NULL
);;

ALTER TABLE phpbb_topics_marking ADD PRIMARY KEY (user_id, topic_id);;

CREATE INDEX phpbb_topics_marking_forum_id ON phpbb_topics_marking(forum_id);;


# phpbb_topics_posted
CREATE TABLE phpbb_topics_posted (
  user_id INTEGER DEFAULT 0  NOT NULL,
  topic_id INTEGER DEFAULT 0  NOT NULL,
  topic_posted INTEGER DEFAULT 0  NOT NULL
);;

ALTER TABLE phpbb_topics_posted ADD PRIMARY KEY (user_id, topic_id);;


# phpbb_topics_watch
CREATE TABLE phpbb_topics_watch (
  topic_id INTEGER DEFAULT 0  NOT NULL,
  user_id INTEGER DEFAULT 0  NOT NULL,
  notify_status INTEGER DEFAULT 0  NOT NULL
);;

CREATE INDEX phpbb_topics_watch_notify_stat ON phpbb_topics_watch(notify_status);;
CREATE INDEX phpbb_topics_watch_topic_id ON phpbb_topics_watch(topic_id);;
CREATE INDEX phpbb_topics_watch_user_id ON phpbb_topics_watch(user_id);;


# phpbb_user_group
CREATE TABLE phpbb_user_group (
  group_id INTEGER DEFAULT 0  NOT NULL,
  user_id INTEGER DEFAULT 0  NOT NULL,
  group_leader INTEGER DEFAULT 0  NOT NULL,
  user_pending INTEGER
);;

CREATE INDEX phpbb_user_group_group_id ON phpbb_user_group(group_id);;
CREATE INDEX phpbb_user_group_group_leader ON phpbb_user_group(group_leader);;
CREATE INDEX phpbb_user_group_user_id ON phpbb_user_group(user_id);;


# phpbb_users
CREATE TABLE phpbb_users (
  user_id INTEGER NOT NULL,
  user_type INTEGER DEFAULT 0  NOT NULL,
  group_id INTEGER DEFAULT 3  NOT NULL,
  user_permissions BLOB SUB_TYPE TEXT,
  user_perm_from INTEGER DEFAULT 0 NOT NULL,
  user_ip VARCHAR(40) DEFAULT '' NOT NULL,
  user_regdate INTEGER DEFAULT 0  NOT NULL,
  username VARCHAR(252) DEFAULT '' NOT NULL,
  user_password VARCHAR(40) DEFAULT '' NOT NULL,
  user_passchg INTEGER DEFAULT 0,
  user_email VARCHAR(100) DEFAULT '' NOT NULL,
  user_email_hash DOUBLE PRECISION DEFAULT 0  NOT NULL,
  user_birthday VARCHAR(10) DEFAULT '',
  user_lastvisit INTEGER DEFAULT 0  NOT NULL,
  user_lastmark INTEGER DEFAULT 0  NOT NULL,
  user_lastpost_time INTEGER DEFAULT 0  NOT NULL,
  user_lastpage VARCHAR(200) DEFAULT '' NOT NULL,
  user_last_confirm_key VARCHAR(10) DEFAULT '',
  user_last_search INTEGER DEFAULT 0,
  user_warnings INTEGER DEFAULT 0,
  user_last_warning INTEGER DEFAULT 0,
  user_login_attempts INTEGER DEFAULT 0,
  user_posts INTEGER DEFAULT 0  NOT NULL,
  user_lang VARCHAR(30) DEFAULT '' NOT NULL,
  user_timezone DOUBLE PRECISION DEFAULT 0  NOT NULL,
  user_dst INTEGER DEFAULT 0  NOT NULL,
  user_dateformat VARCHAR(30) DEFAULT 'd M Y H:i'  NOT NULL,
  user_style INTEGER DEFAULT 0  NOT NULL,
  user_rank INTEGER DEFAULT 0 ,
  user_colour VARCHAR(6) DEFAULT '' NOT NULL,
  user_new_privmsg INTEGER DEFAULT 0  NOT NULL,
  user_unread_privmsg INTEGER DEFAULT 0  NOT NULL,
  user_last_privmsg INTEGER DEFAULT 0  NOT NULL,
  user_message_rules INTEGER DEFAULT 0  NOT NULL,
  user_full_folder INTEGER DEFAULT -3  NOT NULL,
  user_emailtime INTEGER DEFAULT 0  NOT NULL,
  user_topic_show_days INTEGER DEFAULT 0  NOT NULL,
  user_topic_sortby_type VARCHAR(1) DEFAULT 't' NOT NULL,
  user_topic_sortby_dir VARCHAR(1) DEFAULT 'd' NOT NULL,
  user_post_show_days INTEGER DEFAULT 0  NOT NULL,
  user_post_sortby_type VARCHAR(1) DEFAULT 't' NOT NULL,
  user_post_sortby_dir VARCHAR(1) DEFAULT 'a' NOT NULL,
  user_notify INTEGER DEFAULT 0  NOT NULL,
  user_notify_pm INTEGER DEFAULT 1  NOT NULL,
  user_notify_type INTEGER DEFAULT 0  NOT NULL,
  user_allow_pm INTEGER DEFAULT 1  NOT NULL,
  user_allow_email INTEGER DEFAULT 1  NOT NULL,
  user_allow_viewonline INTEGER DEFAULT 1  NOT NULL,
  user_allow_viewemail INTEGER DEFAULT 1  NOT NULL,
  user_allow_massemail INTEGER DEFAULT 1  NOT NULL,
  user_options INTEGER DEFAULT 893  NOT NULL,
  user_avatar VARCHAR(255) DEFAULT '' NOT NULL,
  user_avatar_type INTEGER DEFAULT 0  NOT NULL,
  user_avatar_width INTEGER DEFAULT 0  NOT NULL,
  user_avatar_height INTEGER DEFAULT 0  NOT NULL,
  user_sig BLOB SUB_TYPE TEXT,
  user_sig_bbcode_uid VARCHAR(5) DEFAULT '',
  user_sig_bbcode_bitfield INTEGER DEFAULT 0,
  user_from VARCHAR(100) DEFAULT '',
  user_icq VARCHAR(15) DEFAULT '',
  user_aim VARCHAR(255) DEFAULT '',
  user_yim VARCHAR(255) DEFAULT '',
  user_msnm VARCHAR(255) DEFAULT '',
  user_jabber VARCHAR(255) DEFAULT '',
  user_website VARCHAR(200) DEFAULT '',
  user_occ VARCHAR(255) DEFAULT '',
  user_interests VARCHAR(255) DEFAULT '',
  user_actkey VARCHAR(32) DEFAULT '' NOT NULL,
  user_newpasswd VARCHAR(32) DEFAULT ''
);;

ALTER TABLE phpbb_users ADD PRIMARY KEY (user_id);;

CREATE INDEX phpbb_users_user_birthday ON phpbb_users(user_birthday);;
CREATE INDEX phpbb_users_user_email_hash ON phpbb_users(user_email_hash);;
CREATE INDEX phpbb_users_username ON phpbb_users(username);;

CREATE GENERATOR phpbb_users_gen;;
SET GENERATOR phpbb_users_gen TO 0;;

CREATE TRIGGER t_phpbb_users_gen FOR phpbb_users
BEFORE INSERT
AS
BEGIN
  NEW.user_id = GEN_ID(phpbb_users_gen, 1);
END;;


# phpbb_warnings
CREATE TABLE phpbb_warnings (
  warning_id INTEGER NOT NULL,
  user_id INTEGER DEFAULT 0  NOT NULL,
  post_id INTEGER DEFAULT 0  NOT NULL,
  log_id INTEGER DEFAULT 0  NOT NULL,
  warning_time INTEGER DEFAULT 0  NOT NULL
);;

ALTER TABLE phpbb_warnings ADD PRIMARY KEY (warning_id);;

CREATE GENERATOR phpbb_warnings_gen;;
SET GENERATOR phpbb_warnings_gen TO 0;;

CREATE TRIGGER t_phpbb_warnings_gen FOR phpbb_warnings
BEFORE INSERT
AS
BEGIN
  NEW.warning_id = GEN_ID(phpbb_warnings_gen, 1);
END;;


# phpbb_words
CREATE TABLE phpbb_words (
  word_id INTEGER NOT NULL,
  word VARCHAR(255) NOT NULL,
  replacement VARCHAR(255) NOT NULL
);;

ALTER TABLE phpbb_words ADD PRIMARY KEY (word_id);;

CREATE GENERATOR phpbb_words_gen;;
SET GENERATOR phpbb_words_gen TO 0;;

CREATE TRIGGER t_phpbb_words_gen FOR phpbb_words
BEFORE INSERT
AS
BEGIN
  NEW.word_id = GEN_ID(phpbb_words_gen, 1);
END;;


# phpbb_zebra
CREATE TABLE phpbb_zebra (
  user_id INTEGER DEFAULT 0  NOT NULL,
  zebra_id INTEGER DEFAULT 0  NOT NULL,
  friend INTEGER DEFAULT 0  NOT NULL,
  foe INTEGER DEFAULT 0  NOT NULL
);;

CREATE INDEX phpbb_zebra_user_id ON phpbb_zebra(user_id);;
CREATE INDEX phpbb_zebra_zebra_id ON phpbb_zebra(zebra_id);;

DECLARE EXTERNAL FUNCTION STRLEN
    CSTRING(32767)
RETURNS INTEGER BY VALUE
ENTRY_POINT 'IB_UDF_strlen' MODULE_NAME 'ib_udf';;

DECLARE EXTERNAL FUNCTION LOWER CSTRING(80)
RETURNS CSTRING(80) FREE_IT 
ENTRY_POINT 'IB_UDF_lower' MODULE_NAME 'ib_udf';;