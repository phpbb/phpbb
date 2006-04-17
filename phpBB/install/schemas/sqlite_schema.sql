#
# SQLite Schema for phpBB 3.x - (c) phpBB Group, 2005
#
# $Id$
#

BEGIN TRANSACTION;

# Table: phpbb_attachments
CREATE TABLE phpbb_attachments (
  attach_id INTEGER PRIMARY KEY NOT NULL,
  post_msg_id mediumint(8) NOT NULL DEFAULT '0',
  topic_id mediumint(8) NOT NULL DEFAULT '0',
  in_message tinyint(1) NOT NULL DEFAULT '0',
  poster_id mediumint(8) NOT NULL DEFAULT '0',
  physical_filename varchar(255) NOT NULL,
  real_filename varchar(255) NOT NULL,
  download_count mediumint(8) NOT NULL DEFAULT '0',
  comment text(65535),
  extension varchar(100),
  mimetype varchar(100),
  filesize int(20) NOT NULL,
  filetime int(11) NOT NULL DEFAULT '0',
  thumbnail tinyint(1) NOT NULL DEFAULT '0'
);

CREATE INDEX phpbb_attachments_filetime on phpbb_attachments (filetime);
CREATE INDEX phpbb_attachments_post_msg_id on phpbb_attachments (post_msg_id);
CREATE INDEX phpbb_attachments_topic_id on phpbb_attachments (topic_id);
CREATE INDEX phpbb_attachments_poster_id on phpbb_attachments (poster_id);
CREATE INDEX phpbb_attachments_physical_filename on phpbb_attachments (physical_filename);
CREATE INDEX phpbb_attachments_filesize on phpbb_attachments (filesize);


# Table: phpbb_auth_groups
CREATE TABLE phpbb_auth_groups (
  group_id mediumint(8) NOT NULL DEFAULT '0',
  forum_id mediumint(8) NOT NULL DEFAULT '0',
  auth_option_id mediumint(8) NOT NULL DEFAULT '0',
  auth_role_id mediumint(8) NOT NULL DEFAULT '0',
  auth_setting tinyint(4) NOT NULL DEFAULT '0'
);

CREATE INDEX phpbb_auth_groups_group_id on phpbb_auth_groups (group_id);
CREATE INDEX phpbb_auth_groups_auth_option_id on phpbb_auth_groups (auth_option_id);


# Table: phpbb_auth_options
CREATE TABLE phpbb_auth_options (
  auth_option_id INTEGER PRIMARY KEY NOT NULL,
  auth_option varchar(20) NOT NULL,
  is_global tinyint(1) NOT NULL DEFAULT '0',
  is_local tinyint(1) NOT NULL DEFAULT '0',
  founder_only tinyint(1) NOT NULL DEFAULT '0'
);

CREATE INDEX phpbb_auth_options_auth_option on phpbb_auth_options (auth_option);


# Table: phpbb_auth_roles
CREATE TABLE phpbb_auth_roles (
  role_id INTEGER PRIMARY KEY NOT NULL,
  role_name varchar(50) NOT NULL DEFAULT '',
  role_description text(65535),
  role_type varchar(10) NOT NULL DEFAULT '',
  role_order mediumint(8) NOT NULL DEFAULT '0',
  role_group_ids varchar(255) NOT NULL DEFAULT ''
);

CREATE INDEX phpbb_auth_roles_role_type on phpbb_auth_roles (role_type);
CREATE INDEX phpbb_auth_roles_role_order on phpbb_auth_roles (role_order);


# Table: phpbb_auth_roles_data
CREATE TABLE phpbb_auth_roles_data (
  role_id mediumint(8) NOT NULL DEFAULT '0',
  auth_option_id mediumint(8) NOT NULL DEFAULT '0',
  auth_setting tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY  (role_id, auth_option_id)
);


# Table: phpbb_auth_users
CREATE TABLE phpbb_auth_users (
  user_id mediumint(8) NOT NULL DEFAULT '0',
  forum_id mediumint(8) NOT NULL DEFAULT '0',
  auth_option_id mediumint(8) NOT NULL DEFAULT '0',
  auth_role_id mediumint(8) NOT NULL DEFAULT '0',
  auth_setting tinyint(4) NOT NULL DEFAULT '0'
);

CREATE INDEX phpbb_auth_users_user_id on phpbb_auth_users (user_id);
CREATE INDEX phpbb_auth_users_auth_option_id on phpbb_auth_users (auth_option_id);


# Table: phpbb_banlist
CREATE TABLE phpbb_banlist (
  ban_id INTEGER PRIMARY KEY NOT NULL,
  ban_userid mediumint(8) NOT NULL DEFAULT '0',
  ban_ip varchar(40) NOT NULL DEFAULT '',
  ban_email varchar(100) NOT NULL DEFAULT '',
  ban_start int(11) NOT NULL DEFAULT '0',
  ban_end int(11) NOT NULL DEFAULT '0',
  ban_exclude tinyint(1) NOT NULL DEFAULT '0',
  ban_reason text(65535),
  ban_give_reason text(65535)
);


# Table: phpbb_bbcodes
CREATE TABLE phpbb_bbcodes (
  bbcode_id INTEGER PRIMARY KEY NOT NULL DEFAULT '0',
  bbcode_tag varchar(16) NOT NULL DEFAULT '',
  display_on_posting tinyint(1) NOT NULL DEFAULT '0',
  bbcode_match varchar(255) NOT NULL DEFAULT '',
  bbcode_tpl text(65535),
  first_pass_match varchar(255) NOT NULL DEFAULT '',
  first_pass_replace varchar(255) NOT NULL DEFAULT '',
  second_pass_match varchar(255) NOT NULL DEFAULT '',
  second_pass_replace text(65535)
);

CREATE INDEX phpbb_bbcodes_display_on_posting on phpbb_bbcodes (display_on_posting);


# Table: phpbb_bookmarks
CREATE TABLE phpbb_bookmarks (
  topic_id mediumint(8) NOT NULL DEFAULT '0',
  user_id mediumint(8) NOT NULL DEFAULT '0',
  order_id mediumint(8) NOT NULL DEFAULT '0'
);

CREATE INDEX phpbb_bookmarks_order_id on phpbb_bookmarks (order_id);
CREATE INDEX phpbb_bookmarks_topic_user_id on phpbb_bookmarks (topic_id, user_id);


# Table: phpbb_bots
CREATE TABLE phpbb_bots (
  bot_id INTEGER PRIMARY KEY NOT NULL,
  bot_active tinyint(1) NOT NULL DEFAULT '1',
  bot_name text(65535),
  user_id mediumint(8) NOT NULL DEFAULT '0',
  bot_agent varchar(255) NOT NULL DEFAULT '',
  bot_ip varchar(255) NOT NULL DEFAULT ''
);

CREATE INDEX phpbb_bots_bot_active on phpbb_bots (bot_active);


# Table: phpbb_cache
CREATE TABLE phpbb_cache (
  var_name varchar(255) NOT NULL DEFAULT '',
  var_expires int(10) NOT NULL DEFAULT '0',
  var_data mediumtext(16777215),
  PRIMARY KEY (var_name)
);


# Table: phpbb_config
CREATE TABLE phpbb_config (
  config_name varchar(255) NOT NULL,
  config_value varchar(255) NOT NULL,
  is_dynamic tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (config_name)
);

CREATE INDEX phpbb_config_is_dynamic on phpbb_config (is_dynamic);


# Table: phpbb_confirm
CREATE TABLE phpbb_confirm (
  confirm_id char(32) NOT NULL DEFAULT '',
  session_id char(32) NOT NULL DEFAULT '',
  confirm_type INTEGER NOT NULL DEFAULT '0',
  code varchar(8) NOT NULL DEFAULT '',
  PRIMARY KEY (session_id, confirm_id)
);


# Table: phpbb_disallow
CREATE TABLE phpbb_disallow (
  disallow_id INTEGER PRIMARY KEY NOT NULL,
  disallow_username varchar(255) NOT NULL DEFAULT ''
);


# Table: phpbb_drafts
CREATE TABLE phpbb_drafts (
  draft_id INTEGER PRIMARY KEY NOT NULL,
  user_id mediumint(8) NOT NULL DEFAULT '0',
  topic_id mediumint(8) NOT NULL DEFAULT '0',
  forum_id mediumint(8) NOT NULL DEFAULT '0',
  save_time int(11) NOT NULL DEFAULT '0',
  draft_subject text(65535),
  draft_message mediumtext(16777215)
);

CREATE INDEX phpbb_drafts_save_time on phpbb_drafts (save_time);


# Table: phpbb_extensions
CREATE TABLE phpbb_extensions (
  extension_id INTEGER PRIMARY KEY NOT NULL,
  group_id mediumint(8) NOT NULL DEFAULT '0',
  extension varchar(100) NOT NULL DEFAULT ''
);


# Table: phpbb_extension_groups
CREATE TABLE phpbb_extension_groups (
  group_id INTEGER PRIMARY KEY NOT NULL,
  group_name varchar(255) NOT NULL,
  cat_id tinyint(2) NOT NULL DEFAULT '0',
  allow_group tinyint(1) NOT NULL DEFAULT '0',
  download_mode tinyint(1) NOT NULL DEFAULT '1',
  upload_icon varchar(255) NOT NULL DEFAULT '',
  max_filesize int(20) NOT NULL DEFAULT '0',
  allowed_forums text(65535),
  allow_in_pm tinyint(1) NOT NULL DEFAULT '0'
);


# Table: phpbb_forums
CREATE TABLE phpbb_forums (
  forum_id INTEGER PRIMARY KEY NOT NULL,
  parent_id smallint(5) NOT NULL,
  left_id smallint(5) NOT NULL,
  right_id smallint(5) NOT NULL,
  forum_parents text(65535),
  forum_name text(65535),
  forum_desc text(65535),
  forum_desc_bitfield int(11) NOT NULL DEFAULT '0',
  forum_desc_uid varchar(5) NOT NULL DEFAULT '',
  forum_link varchar(255) NOT NULL DEFAULT '',
  forum_password varchar(40) NOT NULL DEFAULT '',
  forum_style tinyint(4),
  forum_image varchar(255) NOT NULL DEFAULT '',
  forum_rules text(65535),
  forum_rules_link varchar(255) NOT NULL DEFAULT '',
  forum_rules_bitfield int(11) NOT NULL DEFAULT '0',
  forum_rules_uid varchar(5) NOT NULL DEFAULT '',
  forum_topics_per_page tinyint(4) NOT NULL DEFAULT '0',
  forum_type tinyint(4) NOT NULL DEFAULT '0',
  forum_status tinyint(4) NOT NULL DEFAULT '0',
  forum_posts mediumint(8) NOT NULL DEFAULT '0',
  forum_topics mediumint(8) NOT NULL DEFAULT '0',
  forum_topics_real mediumint(8) NOT NULL DEFAULT '0',
  forum_last_post_id mediumint(8) NOT NULL DEFAULT '0',
  forum_last_poster_id mediumint(8) NOT NULL DEFAULT '0',
  forum_last_post_time int(11) NOT NULL DEFAULT '0',
  forum_last_poster_name varchar(255),
  forum_flags tinyint(4) NOT NULL DEFAULT '0',
  display_on_index tinyint(1) NOT NULL DEFAULT '1',
  enable_indexing tinyint(1) NOT NULL DEFAULT '1',
  enable_icons tinyint(1) NOT NULL DEFAULT '1',
  enable_prune tinyint(1) NOT NULL DEFAULT '0',
  prune_next int(11),
  prune_days tinyint(4) NOT NULL,
  prune_viewed tinyint(4) NOT NULL,
  prune_freq tinyint(4) NOT NULL DEFAULT '0'
);

CREATE INDEX phpbb_forums_left_right_id on phpbb_forums (left_id, right_id);
CREATE INDEX phpbb_forums_forum_last_post_id on phpbb_forums (forum_last_post_id);


# Table: phpbb_forum_access
CREATE TABLE phpbb_forum_access (
  forum_id mediumint(8) NOT NULL DEFAULT '0',
  user_id mediumint(8) NOT NULL DEFAULT '0',
  session_id varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (forum_id, user_id, session_id)
);


# Table: phpbb_forums_marking
CREATE TABLE phpbb_forums_marking (
  user_id mediumint(9) NOT NULL DEFAULT '0',
  forum_id mediumint(9) NOT NULL DEFAULT '0',
  mark_time int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (user_id, forum_id)
);


# Table: phpbb_forums_watch
CREATE TABLE phpbb_forums_watch (
  forum_id smallint(5) NOT NULL DEFAULT '0',
  user_id mediumint(8) NOT NULL DEFAULT '0',
  notify_status tinyint(1) NOT NULL DEFAULT '0'
);

CREATE INDEX phpbb_forums_watch_forum_id on phpbb_forums_watch (forum_id);
CREATE INDEX phpbb_forums_watch_user_id on phpbb_forums_watch (user_id);
CREATE INDEX phpbb_forums_watch_notify_status on phpbb_forums_watch (notify_status);


# Table: phpbb_groups
CREATE TABLE phpbb_groups (
  group_id INTEGER PRIMARY KEY NOT NULL,
  group_type tinyint(4) NOT NULL DEFAULT '1',
  group_name varchar(255) NOT NULL,
  group_desc text(65535),
  group_desc_bitfield int(11) NOT NULL DEFAULT '0',
  group_desc_uid varchar(5) NOT NULL DEFAULT '',
  group_display tinyint(1) NOT NULL DEFAULT '0',
  group_avatar varchar(255) NOT NULL DEFAULT '',
  group_avatar_type tinyint(4) NOT NULL DEFAULT '0',
  group_avatar_width tinyint(4) NOT NULL DEFAULT '0',
  group_avatar_height tinyint(4) NOT NULL DEFAULT '0',
  group_rank smallint(5) NOT NULL DEFAULT '-1',
  group_colour varchar(6) NOT NULL DEFAULT '',
  group_sig_chars mediumint(8) NOT NULL DEFAULT '0',
  group_receive_pm tinyint(1) NOT NULL DEFAULT '0',
  group_message_limit mediumint(8) NOT NULL DEFAULT '0',
  group_chgpass smallint(6) NOT NULL DEFAULT '0',
  group_legend tinyint(1) NOT NULL DEFAULT '1'
);

CREATE INDEX phpbb_groups_group_legend on phpbb_groups (group_legend);


# Table: phpbb_icons
CREATE TABLE phpbb_icons (
  icons_id INTEGER PRIMARY KEY NOT NULL,
  icons_url varchar(255),
  icons_width tinyint(4) NOT NULL,
  icons_height tinyint(4) NOT NULL,
  icons_order tinyint(4) NOT NULL,
  display_on_posting tinyint(1) NOT NULL DEFAULT '1'
);


# Table: phpbb_lang
CREATE TABLE phpbb_lang (
  lang_id INTEGER PRIMARY KEY NOT NULL,
  lang_iso varchar(5) NOT NULL,
  lang_dir varchar(30) NOT NULL,
  lang_english_name varchar(100),
  lang_local_name varchar(255),
  lang_author varchar(255)
);


# Table: phpbb_log
CREATE TABLE phpbb_log (
  log_id INTEGER PRIMARY KEY NOT NULL,
  log_type tinyint(4) NOT NULL DEFAULT '0',
  user_id mediumint(8) NOT NULL DEFAULT '0',
  forum_id mediumint(8) NOT NULL DEFAULT '0',
  topic_id mediumint(8) NOT NULL DEFAULT '0',
  reportee_id mediumint(8) NOT NULL DEFAULT '0',
  log_ip varchar(40) NOT NULL,
  log_time int(11) NOT NULL,
  log_operation text(65535),
  log_data text(65535)
);

CREATE INDEX phpbb_log_log_type on phpbb_log (log_type);
CREATE INDEX phpbb_log_forum_id on phpbb_log (forum_id);
CREATE INDEX phpbb_log_topic_id on phpbb_log (topic_id);
CREATE INDEX phpbb_log_reportee_id on phpbb_log (reportee_id);
CREATE INDEX phpbb_log_user_id on phpbb_log (user_id);


# Table: phpbb_moderator_cache
CREATE TABLE phpbb_moderator_cache (
  forum_id mediumint(8) NOT NULL,
  user_id mediumint(8) NOT NULL DEFAULT '0',
  username varchar(255) NOT NULL DEFAULT '',
  group_id mediumint(8) NOT NULL DEFAULT '0',
  group_name varchar(255) NOT NULL DEFAULT '',
  display_on_index tinyint(1) NOT NULL DEFAULT '1'
);

CREATE INDEX phpbb_moderator_cache_display_on_index on phpbb_moderator_cache (display_on_index);
CREATE INDEX phpbb_moderator_cache_forum_id on phpbb_moderator_cache (forum_id);


# Table: phpbb_modules
CREATE TABLE phpbb_modules (
  module_id INTEGER PRIMARY KEY NOT NULL,
  module_enabled tinyint(1) NOT NULL DEFAULT '1',
  module_display tinyint(1) NOT NULL DEFAULT '1',
  module_name varchar(255) NOT NULL DEFAULT '',
  module_class varchar(10) NOT NULL DEFAULT '',
  parent_id mediumint(8) NOT NULL DEFAULT '0',
  left_id mediumint(8) NOT NULL DEFAULT '0',
  right_id mediumint(8) NOT NULL DEFAULT '0',
  module_langname varchar(255) NOT NULL DEFAULT '',
  module_mode varchar(255) NOT NULL DEFAULT '',
  module_auth varchar(255) NOT NULL DEFAULT ''
);

CREATE INDEX phpbb_modules_module_enabled on phpbb_modules (module_enabled);
CREATE INDEX phpbb_modules_left_right_id on phpbb_modules (left_id, right_id);


# Table: phpbb_poll_results
CREATE TABLE phpbb_poll_results (
  poll_option_id tinyint(4) NOT NULL DEFAULT '0',
  topic_id mediumint(8) NOT NULL,
  poll_option_text text(65535),
  poll_option_total mediumint(8) NOT NULL DEFAULT '0'
);

CREATE INDEX phpbb_poll_results_poll_option_id on phpbb_poll_results (poll_option_id);
CREATE INDEX phpbb_poll_results_topic_id on phpbb_poll_results (topic_id);


# Table: phpbb_poll_voters
CREATE TABLE phpbb_poll_voters (
  topic_id mediumint(8) NOT NULL DEFAULT '0',
  poll_option_id tinyint(4) NOT NULL DEFAULT '0',
  vote_user_id mediumint(8) NOT NULL DEFAULT '0',
  vote_user_ip varchar(40) NOT NULL
);

CREATE INDEX phpbb_poll_voters_topic_id on phpbb_poll_voters (topic_id);
CREATE INDEX phpbb_poll_voters_vote_user_id on phpbb_poll_voters (vote_user_id);
CREATE INDEX phpbb_poll_voters_vote_user_ip on phpbb_poll_voters (vote_user_ip);


# Table: phpbb_posts
CREATE TABLE phpbb_posts (
  post_id INTEGER PRIMARY KEY NOT NULL,
  topic_id mediumint(8) NOT NULL DEFAULT '0',
  forum_id smallint(5) NOT NULL DEFAULT '0',
  poster_id mediumint(8) NOT NULL DEFAULT '0',
  icon_id tinyint(4) NOT NULL DEFAULT '1',
  poster_ip varchar(40) NOT NULL,
  post_time int(11) NOT NULL DEFAULT '0',
  post_approved tinyint(1) NOT NULL DEFAULT '1',
  post_reported tinyint(1) NOT NULL DEFAULT '0',
  enable_bbcode tinyint(1) NOT NULL DEFAULT '1',
  enable_smilies tinyint(1) NOT NULL DEFAULT '1',
  enable_magic_url tinyint(1) NOT NULL DEFAULT '1',
  enable_sig tinyint(1) NOT NULL DEFAULT '1',
  post_username varchar(255),
  post_subject text(65535),
  post_text mediumtext(16777215),
  post_checksum varchar(32) NOT NULL,
  post_encoding varchar(20) NOT NULL DEFAULT 'iso-8859-1',
  post_attachment tinyint(1) NOT NULL DEFAULT '0',
  bbcode_bitfield int(11) NOT NULL DEFAULT '0',
  bbcode_uid varchar(5) NOT NULL DEFAULT '',
  post_edit_time int(11) NOT NULL DEFAULT '0',
  post_edit_reason text(65535),
  post_edit_user mediumint(8) NOT NULL DEFAULT '0',
  post_edit_count smallint(5) NOT NULL DEFAULT '0',
  post_edit_locked tinyint(1) NOT NULL DEFAULT '0'
);

CREATE INDEX phpbb_posts_forum_id on phpbb_posts (forum_id);
CREATE INDEX phpbb_posts_topic_id on phpbb_posts (topic_id);
CREATE INDEX phpbb_posts_poster_ip on phpbb_posts (poster_ip);
CREATE INDEX phpbb_posts_poster_id on phpbb_posts (poster_id);
CREATE INDEX phpbb_posts_post_approved on phpbb_posts (post_approved);
CREATE INDEX phpbb_posts_post_time on phpbb_posts (post_time);


# Table: phpbb_privmsgs
CREATE TABLE phpbb_privmsgs (
  msg_id INTEGER PRIMARY KEY NOT NULL,
  root_level mediumint(8) NOT NULL DEFAULT '0',
  author_id mediumint(8) NOT NULL DEFAULT '0',
  icon_id tinyint(4) NOT NULL DEFAULT '1',
  author_ip varchar(40) NOT NULL DEFAULT '',
  message_time int(11) NOT NULL DEFAULT '0',
  enable_bbcode tinyint(1) NOT NULL DEFAULT '1',
  enable_smilies tinyint(1) NOT NULL DEFAULT '1',
  enable_magic_url tinyint(1) NOT NULL DEFAULT '1',
  enable_sig tinyint(1) NOT NULL DEFAULT '1',
  message_subject text(65535),
  message_text mediumtext(16777215),
  message_edit_reason text(65535),
  message_edit_user mediumint(8) NOT NULL DEFAULT '0',
  message_encoding varchar(20) NOT NULL DEFAULT 'iso-8859-1',
  message_attachment tinyint(1) NOT NULL DEFAULT '0',
  bbcode_bitfield int(11) NOT NULL DEFAULT '0',
  bbcode_uid varchar(5) NOT NULL DEFAULT '',
  message_edit_time int(11) NOT NULL DEFAULT '0',
  message_edit_count smallint(5) NOT NULL DEFAULT '0',
  to_address text(65535),
  bcc_address text(65535)
);

CREATE INDEX phpbb_privmsgs_author_ip on phpbb_privmsgs (author_ip);
CREATE INDEX phpbb_privmsgs_message_time on phpbb_privmsgs (message_time);
CREATE INDEX phpbb_privmsgs_author_id on phpbb_privmsgs (author_id);
CREATE INDEX phpbb_privmsgs_root_level on phpbb_privmsgs (root_level);


# Table: phpbb_privmsgs_folder
CREATE TABLE phpbb_privmsgs_folder (
  folder_id INTEGER PRIMARY KEY NOT NULL,
  user_id mediumint(8) NOT NULL DEFAULT '0',
  folder_name varchar(255) NOT NULL DEFAULT '',
  pm_count mediumint(8) NOT NULL DEFAULT '0'
);

CREATE INDEX phpbb_privmsgs_folder_user_id on phpbb_privmsgs_folder (user_id);


# Table: phpbb_privmsgs_rules
CREATE TABLE phpbb_privmsgs_rules (
  rule_id INTEGER PRIMARY KEY NOT NULL,
  user_id mediumint(8) NOT NULL DEFAULT '0',
  rule_check mediumint(4) NOT NULL DEFAULT '0',
  rule_connection mediumint(4) NOT NULL DEFAULT '0',
  rule_string varchar(255) NOT NULL DEFAULT '',
  rule_user_id mediumint(8) NOT NULL DEFAULT '0',
  rule_group_id mediumint(8) NOT NULL DEFAULT '0',
  rule_action mediumint(4) NOT NULL DEFAULT '0',
  rule_folder_id mediumint(8) NOT NULL DEFAULT '0'
);


# Table: phpbb_privmsgs_to
CREATE TABLE phpbb_privmsgs_to (
  msg_id mediumint(8) NOT NULL DEFAULT '0',
  user_id mediumint(8) NOT NULL DEFAULT '0',
  author_id mediumint(8) NOT NULL DEFAULT '0',
  deleted tinyint(1) NOT NULL DEFAULT '0',
  new tinyint(1) NOT NULL DEFAULT '1',
  unread tinyint(1) NOT NULL DEFAULT '1',
  replied tinyint(1) NOT NULL DEFAULT '0',
  marked tinyint(1) NOT NULL DEFAULT '0',
  forwarded tinyint(1) NOT NULL DEFAULT '0',
  folder_id int(10) NOT NULL DEFAULT '0'
);

CREATE INDEX phpbb_privmsgs_to_msg_id on phpbb_privmsgs_to (msg_id);
CREATE INDEX phpbb_privmsgs_to_user_id on phpbb_privmsgs_to (user_id, folder_id);


# Table: phpbb_profile_fields
CREATE TABLE phpbb_profile_fields (
  field_id INTEGER PRIMARY KEY NOT NULL,
  field_name varchar(255) NOT NULL DEFAULT '',
  field_desc text(65535),
  field_type mediumint(8) NOT NULL,
  field_ident varchar(20) NOT NULL DEFAULT '',
  field_length varchar(20) NOT NULL DEFAULT '',
  field_minlen varchar(255) NOT NULL DEFAULT '',
  field_maxlen varchar(255) NOT NULL DEFAULT '',
  field_novalue varchar(255) NOT NULL DEFAULT '',
  field_default_value varchar(255) NOT NULL DEFAULT '0',
  field_validation varchar(20) NOT NULL DEFAULT '',
  field_required tinyint(1) NOT NULL DEFAULT '0',
  field_show_on_reg tinyint(1) NOT NULL DEFAULT '0',
  field_hide tinyint(1) NOT NULL DEFAULT '0',
  field_no_view tinyint(1) NOT NULL DEFAULT '0',
  field_active tinyint(1) NOT NULL DEFAULT '0',
  field_order tinyint(4) NOT NULL DEFAULT '0'
);

CREATE INDEX phpbb_profile_fields_field_type on phpbb_profile_fields (field_type);
CREATE INDEX phpbb_profile_fields_field_order on phpbb_profile_fields (field_order);


# Table: phpbb_profile_fields_data
CREATE TABLE phpbb_profile_fields_data (
  user_id INTEGER PRIMARY KEY NOT NULL DEFAULT '0'
);


# Table: phpbb_profile_fields_lang
CREATE TABLE phpbb_profile_fields_lang (
  field_id mediumint(8) NOT NULL DEFAULT '0',
  lang_id mediumint(8) NOT NULL DEFAULT '0',
  option_id mediumint(8) NOT NULL DEFAULT '0',
  field_type tinyint(4) NOT NULL DEFAULT '0',
  value varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (field_id, lang_id, option_id)
);


# Table: phpbb_profile_lang
CREATE TABLE phpbb_profile_lang (
  field_id mediumint(8) NOT NULL DEFAULT '0',
  lang_id tinyint(4) NOT NULL DEFAULT '0',
  lang_name varchar(255) NOT NULL DEFAULT '',
  lang_explain text(65535),
  lang_default_value varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (field_id, lang_id)
);


# Table: phpbb_ranks
CREATE TABLE phpbb_ranks (
  rank_id INTEGER PRIMARY KEY NOT NULL,
  rank_title varchar(255) NOT NULL,
  rank_min mediumint(8) NOT NULL DEFAULT '0',
  rank_special tinyint(1) DEFAULT '0',
  rank_image varchar(255)
);


# Table: phpbb_reports_reasons
CREATE TABLE phpbb_reports_reasons (
  reason_id INTEGER PRIMARY KEY NOT NULL,
  reason_title varchar(255) NOT NULL DEFAULT '',
  reason_description text(65535),
  reason_order tinyint(4) NOT NULL DEFAULT '0'
);


# Table: phpbb_reports
CREATE TABLE phpbb_reports (
  report_id INTEGER PRIMARY KEY NOT NULL,
  reason_id smallint(5) NOT NULL DEFAULT '0',
  post_id mediumint(8) NOT NULL DEFAULT '0',
  user_id mediumint(8) NOT NULL DEFAULT '0',
  user_notify tinyint(1) NOT NULL DEFAULT '0',
  report_closed tinyint(1) NOT NULL DEFAULT '0',
  report_time int(10) NOT NULL DEFAULT '0',
  report_text mediumtext(16777215)
);


# Table: phpbb_search_results
CREATE TABLE phpbb_search_results (
  search_key varchar(32) NOT NULL DEFAULT '',
  search_time int(11) NOT NULL DEFAULT '0',
  search_keywords mediumtext(16777215),
  search_authors mediumtext(16777215),
  PRIMARY KEY (search_key)
);


# Table: phpbb_search_wordlist
CREATE TABLE phpbb_search_wordlist (
  word_text varchar(50) NOT NULL DEFAULT '',
  word_id mediumint NOT NULL,
  word_common tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (word_text)
);

CREATE INDEX phpbb_search_wordlist_word_id on phpbb_search_wordlist (word_id);


# Table: phpbb_search_wordmatch
CREATE TABLE phpbb_search_wordmatch (
  post_id mediumint(8) NOT NULL DEFAULT '0',
  word_id mediumint(8) NOT NULL DEFAULT '0',
  title_match tinyint(1) NOT NULL DEFAULT '0'
);

CREATE INDEX phpbb_search_wordmatch_word_id on phpbb_search_wordmatch (word_id);


# Table: phpbb_sessions
CREATE TABLE phpbb_sessions (
  session_id varchar(32) NOT NULL DEFAULT '',
  session_user_id mediumint(8) NOT NULL DEFAULT '0',
  session_last_visit int(11) NOT NULL DEFAULT '0',
  session_start int(11) NOT NULL DEFAULT '0',
  session_time int(11) NOT NULL DEFAULT '0',
  session_ip varchar(40) NOT NULL DEFAULT '0',
  session_browser varchar(150) NOT NULL DEFAULT '',
  session_page varchar(200) NOT NULL DEFAULT '',
  session_viewonline tinyint(1) NOT NULL DEFAULT '1',
  session_autologin tinyint(1) NOT NULL DEFAULT '0',
  session_admin tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (session_id)
);

CREATE INDEX phpbb_sessions_session_time on phpbb_sessions (session_time);
CREATE INDEX phpbb_sessions_session_user_id on phpbb_sessions (session_user_id);


# Table: phpbb_sessions_keys
CREATE TABLE phpbb_sessions_keys (
  key_id varchar(32) NOT NULL DEFAULT '',
  user_id mediumint(8) NOT NULL DEFAULT '0',
  last_ip varchar(40) NOT NULL DEFAULT '',
  last_login int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY  (key_id,user_id)
);

CREATE INDEX phpbb_sessions_keys_last_login on phpbb_sessions_keys (last_login);


# Table: phpbb_sitelist
CREATE TABLE phpbb_sitelist (
  site_id INTEGER PRIMARY KEY NOT NULL,
  site_ip varchar(40) NOT NULL DEFAULT '',
  site_hostname varchar(255) NOT NULL DEFAULT '',
  ip_exclude tinyint(1) NOT NULL DEFAULT '0'
);


# Table: phpbb_smilies
CREATE TABLE phpbb_smilies (
  smiley_id INTEGER PRIMARY KEY NOT NULL,
  code varchar(10),
  emotion varchar(50),
  smiley_url varchar(50),
  smiley_width tinyint(4) NOT NULL,
  smiley_height tinyint(4) NOT NULL,
  smiley_order tinyint(4) NOT NULL,
  display_on_posting tinyint(1) NOT NULL DEFAULT '1'
);


# Table: phpbb_styles
CREATE TABLE phpbb_styles (
  style_id INTEGER PRIMARY KEY NOT NULL,
  style_name varchar(255) NOT NULL DEFAULT '',
  style_copyright varchar(255) NOT NULL DEFAULT '',
  style_active tinyint(1) NOT NULL DEFAULT '1',
  template_id tinyint(4) NOT NULL,
  theme_id tinyint(4) NOT NULL,
  imageset_id tinyint(4) NOT NULL
);

CREATE INDEX phpbb_styles_template_id on phpbb_styles (template_id);
CREATE INDEX phpbb_styles_theme_id on phpbb_styles (theme_id);
CREATE INDEX phpbb_styles_imageset_id on phpbb_styles (imageset_id);
CREATE UNIQUE INDEX phpbb_styles_style_name on phpbb_styles (style_name);


# Table: phpbb_styles_template
CREATE TABLE phpbb_styles_template (
  template_id INTEGER PRIMARY KEY NOT NULL,
  template_name varchar(255) NOT NULL,
  template_copyright varchar(255) NOT NULL,
  template_path varchar(100) NOT NULL,
  bbcode_bitfield int(11) NOT NULL DEFAULT '0',
  template_storedb tinyint(1) NOT NULL DEFAULT '0'
);

CREATE UNIQUE INDEX phpbb_styles_template_template_name on phpbb_styles_template (template_name);


# Table: phpbb_styles_template_data
CREATE TABLE phpbb_styles_template_data (
  template_id tinyint(4) NOT NULL,
  template_filename varchar(100) NOT NULL DEFAULT '',
  template_included text(65535),
  template_mtime int(11) NOT NULL DEFAULT '0',
  template_data mediumtext(16777215)
);

CREATE INDEX phpbb_styles_template_data_template_id on phpbb_styles_template_data (template_id);
CREATE INDEX phpbb_styles_template_data_template_filename on phpbb_styles_template_data (template_filename);


# Table: phpbb_styles_theme
CREATE TABLE phpbb_styles_theme (
  theme_id INTEGER PRIMARY KEY NOT NULL,
  theme_name varchar(255) NOT NULL DEFAULT '',
  theme_copyright varchar(255) NOT NULL DEFAULT '',
  theme_path varchar(100) NOT NULL DEFAULT '',
  theme_storedb tinyint(1) NOT NULL DEFAULT '0',
  theme_mtime int(11) NOT NULL DEFAULT '0',
  theme_data mediumtext(16777215)
);

CREATE UNIQUE INDEX phpbb_styles_theme_theme_name on phpbb_styles_theme (theme_name);


# Table: phpbb_styles_imageset
CREATE TABLE phpbb_styles_imageset (
  imageset_id INTEGER PRIMARY KEY NOT NULL,
  imageset_name varchar(255) NOT NULL DEFAULT '',
  imageset_copyright varchar(255) NOT NULL DEFAULT '',
  imageset_path varchar(100) NOT NULL DEFAULT '',
  site_logo varchar(200) NOT NULL DEFAULT '',
  btn_post varchar(200) NOT NULL DEFAULT '',
  btn_post_pm varchar(200) NOT NULL DEFAULT '',
  btn_reply varchar(200) NOT NULL DEFAULT '',
  btn_reply_pm varchar(200) NOT NULL DEFAULT '',
  btn_locked varchar(200) NOT NULL DEFAULT '',
  btn_profile varchar(200) NOT NULL DEFAULT '',
  btn_pm varchar(200) NOT NULL DEFAULT '',
  btn_delete varchar(200) NOT NULL DEFAULT '',
  btn_info varchar(200) NOT NULL DEFAULT '',
  btn_quote varchar(200) NOT NULL DEFAULT '',
  btn_search varchar(200) NOT NULL DEFAULT '',
  btn_edit varchar(200) NOT NULL DEFAULT '',
  btn_report varchar(200) NOT NULL DEFAULT '',
  btn_email varchar(200) NOT NULL DEFAULT '',
  btn_www varchar(200) NOT NULL DEFAULT '',
  btn_icq varchar(200) NOT NULL DEFAULT '',
  btn_aim varchar(200) NOT NULL DEFAULT '',
  btn_yim varchar(200) NOT NULL DEFAULT '',
  btn_msnm varchar(200) NOT NULL DEFAULT '',
  btn_jabber varchar(200) NOT NULL DEFAULT '',
  btn_online varchar(200) NOT NULL DEFAULT '',
  btn_offline varchar(200) NOT NULL DEFAULT '',
  btn_friend varchar(200) NOT NULL DEFAULT '',
  btn_foe varchar(200) NOT NULL DEFAULT '',
  icon_unapproved varchar(200) NOT NULL DEFAULT '',
  icon_reported varchar(200) NOT NULL DEFAULT '',
  icon_attach varchar(200) NOT NULL DEFAULT '',
  icon_post varchar(200) NOT NULL DEFAULT '',
  icon_post_new varchar(200) NOT NULL DEFAULT '',
  icon_post_latest varchar(200) NOT NULL DEFAULT '',
  icon_post_newest varchar(200) NOT NULL DEFAULT '',
  forum varchar(200) NOT NULL DEFAULT '',
  forum_new varchar(200) NOT NULL DEFAULT '',
  forum_locked varchar(200) NOT NULL DEFAULT '',
  forum_link varchar(200) NOT NULL DEFAULT '',
  sub_forum varchar(200) NOT NULL DEFAULT '',
  sub_forum_new varchar(200) NOT NULL DEFAULT '',
  folder varchar(200) NOT NULL DEFAULT '',
  folder_moved varchar(200) NOT NULL DEFAULT '',
  folder_posted varchar(200) NOT NULL DEFAULT '',
  folder_new varchar(200) NOT NULL DEFAULT '',
  folder_new_posted varchar(200) NOT NULL DEFAULT '',
  folder_hot varchar(200) NOT NULL DEFAULT '',
  folder_hot_posted varchar(200) NOT NULL DEFAULT '',
  folder_hot_new varchar(200) NOT NULL DEFAULT '',
  folder_hot_new_posted varchar(200) NOT NULL DEFAULT '',
  folder_locked varchar(200) NOT NULL DEFAULT '',
  folder_locked_posted varchar(200) NOT NULL DEFAULT '',
  folder_locked_new varchar(200) NOT NULL DEFAULT '',
  folder_locked_new_posted varchar(200) NOT NULL DEFAULT '',
  folder_sticky varchar(200) NOT NULL DEFAULT '',
  folder_sticky_posted varchar(200) NOT NULL DEFAULT '',
  folder_sticky_new varchar(200) NOT NULL DEFAULT '',
  folder_sticky_new_posted varchar(200) NOT NULL DEFAULT '',
  folder_announce varchar(200) NOT NULL DEFAULT '',
  folder_announce_posted varchar(200) NOT NULL DEFAULT '',
  folder_announce_new varchar(200) NOT NULL DEFAULT '',
  folder_announce_new_posted varchar(200) NOT NULL DEFAULT '',
  folder_global varchar(200) NOT NULL DEFAULT '',
  folder_global_posted varchar(200) NOT NULL DEFAULT '',
  folder_global_new varchar(200) NOT NULL DEFAULT '',
  folder_global_new_posted varchar(200) NOT NULL DEFAULT '',
  poll_left varchar(200) NOT NULL DEFAULT '',
  poll_center varchar(200) NOT NULL DEFAULT '',
  poll_right varchar(200) NOT NULL DEFAULT '',
  attach_progress_bar varchar(200) NOT NULL DEFAULT '',
  user_icon1 varchar(200) NOT NULL DEFAULT '',
  user_icon2 varchar(200) NOT NULL DEFAULT '',
  user_icon3 varchar(200) NOT NULL DEFAULT '',
  user_icon4 varchar(200) NOT NULL DEFAULT '',
  user_icon5 varchar(200) NOT NULL DEFAULT '',
  user_icon6 varchar(200) NOT NULL DEFAULT '',
  user_icon7 varchar(200) NOT NULL DEFAULT '',
  user_icon8 varchar(200) NOT NULL DEFAULT '',
  user_icon9 varchar(200) NOT NULL DEFAULT '',
  user_icon10 varchar(200) NOT NULL DEFAULT ''
);

CREATE UNIQUE INDEX phpbb_styles_imageset_imageset_name on phpbb_styles_imageset (imageset_name);


# Table: phpbb_topics
CREATE TABLE phpbb_topics (
  topic_id INTEGER PRIMARY KEY NOT NULL,
  forum_id smallint(5) NOT NULL DEFAULT '0',
  icon_id tinyint(4) NOT NULL DEFAULT '1',
  topic_attachment tinyint(1) NOT NULL DEFAULT '0',
  topic_approved tinyint(1) NOT NULL DEFAULT '1',
  topic_reported tinyint(1) NOT NULL DEFAULT '0',
  topic_title text(65535),
  topic_poster mediumint(8) NOT NULL DEFAULT '0',
  topic_time int(11) NOT NULL DEFAULT '0',
  topic_time_limit int(11) NOT NULL DEFAULT '0',
  topic_views mediumint(8) NOT NULL DEFAULT '0',
  topic_replies mediumint(8) NOT NULL DEFAULT '0',
  topic_replies_real mediumint(8) NOT NULL DEFAULT '0',
  topic_status tinyint(3) NOT NULL DEFAULT '0',
  topic_type tinyint(3) NOT NULL DEFAULT '0',
  topic_first_post_id mediumint(8) NOT NULL DEFAULT '0',
  topic_first_poster_name varchar(255),
  topic_last_post_id mediumint(8) NOT NULL DEFAULT '0',
  topic_last_poster_id mediumint(8) NOT NULL DEFAULT '0',
  topic_last_poster_name varchar(255),
  topic_last_post_time int(11) NOT NULL DEFAULT '0',
  topic_last_view_time int(11) NOT NULL DEFAULT '0',
  topic_moved_id mediumint(8) NOT NULL DEFAULT '0',
  topic_bumped tinyint(1) NOT NULL DEFAULT '0',
  topic_bumper mediumint(8) NOT NULL DEFAULT '0',
  poll_title text(65535),
  poll_start int(11) NOT NULL DEFAULT '0',
  poll_length int(11) NOT NULL DEFAULT '0',
  poll_max_options tinyint(4) NOT NULL DEFAULT '1',
  poll_last_vote int(11) DEFAULT '0',
  poll_vote_change tinyint(1) NOT NULL DEFAULT '0'
);

CREATE INDEX phpbb_topics_forum_id on phpbb_topics (forum_id);
CREATE INDEX phpbb_topics_forum_topic_type on phpbb_topics (forum_id, topic_type);
CREATE INDEX phpbb_topics_topic_last_post_time on phpbb_topics (topic_last_post_time);


# Table: phpbb_topics_marking
CREATE TABLE phpbb_topics_marking (
  user_id mediumint(8) NOT NULL DEFAULT '0',
  topic_id mediumint(8) NOT NULL DEFAULT '0',
  forum_id mediumint(8) NOT NULL DEFAULT '0',
  mark_time int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (user_id, topic_id)
);


# Table: phpbb_topics_posted
CREATE TABLE phpbb_topics_posted (
  user_id mediumint(8) NOT NULL DEFAULT '0',
  topic_id mediumint(8) NOT NULL DEFAULT '0',
  topic_posted tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (user_id, topic_id)
);


# Table: phpbb_topics_watch
CREATE TABLE phpbb_topics_watch (
  topic_id mediumint(8) NOT NULL DEFAULT '0',
  user_id mediumint(8) NOT NULL DEFAULT '0',
  notify_status tinyint(1) NOT NULL DEFAULT '0'
);

CREATE INDEX phpbb_topics_watch_topic_id on phpbb_topics_watch (topic_id);
CREATE INDEX phpbb_topics_watch_user_id on phpbb_topics_watch (user_id);
CREATE INDEX phpbb_topics_watch_notify_status on phpbb_topics_watch (notify_status);


# Table: phpbb_user_group
CREATE TABLE phpbb_user_group (
  group_id mediumint(8) NOT NULL DEFAULT '0',
  user_id mediumint(8) NOT NULL DEFAULT '0',
  group_leader tinyint(1) NOT NULL DEFAULT '0',
  user_pending tinyint(1)
);

CREATE INDEX phpbb_user_group_group_id on phpbb_user_group (group_id);
CREATE INDEX phpbb_user_group_user_id on phpbb_user_group (user_id);
CREATE INDEX phpbb_user_group_group_leader on phpbb_user_group (group_leader);


# Table: phpbb_users
CREATE TABLE phpbb_users (
  user_id INTEGER PRIMARY KEY NOT NULL,
  user_type tinyint(1) NOT NULL DEFAULT '0',
  group_id mediumint(8) NOT NULL DEFAULT '3',
  user_permissions text(65535),
  user_perm_from mediumint(8) NOT NULL DEFAULT '0',
  user_ip varchar(40) NOT NULL DEFAULT '',
  user_regdate int(11) NOT NULL DEFAULT '0',
  username varchar(255) NOT NULL DEFAULT '',
  user_password varchar(40) NOT NULL DEFAULT '',
  user_passchg int(11) NOT NULL DEFAULT '0',
  user_email varchar(100) NOT NULL DEFAULT '',
  user_email_hash bigint(20) NOT NULL DEFAULT '0',
  user_birthday varchar(10) NOT NULL DEFAULT '',
  user_lastvisit int(11) NOT NULL DEFAULT '0',
  user_lastmark int(11) NOT NULL DEFAULT '0',
  user_lastpost_time int(11) NOT NULL DEFAULT '0',
  user_lastpage varchar(200) NOT NULL DEFAULT '',
  user_last_confirm_key varchar(10) NOT NULL DEFAULT '',
  user_warnings tinyint(4) NOT NULL DEFAULT '0',
  user_last_warning int(11) NOT NULL DEFAULT '0',
  user_login_attempts smallint(4) NOT NULL DEFAULT '0',
  user_posts mediumint(8) NOT NULL DEFAULT '0',
  user_lang varchar(30) NOT NULL DEFAULT '',
  user_timezone decimal(5,2) NOT NULL DEFAULT '0.0',
  user_dst tinyint(1) NOT NULL DEFAULT '0',
  user_dateformat varchar(30) NOT NULL DEFAULT 'd M Y H:i',
  user_style tinyint(4) NOT NULL DEFAULT '0',
  user_rank int(11) DEFAULT '0',
  user_colour varchar(6) NOT NULL DEFAULT '',
  user_new_privmsg tinyint(4) NOT NULL DEFAULT '0',
  user_unread_privmsg tinyint(4) NOT NULL DEFAULT '0',
  user_last_privmsg int(11) NOT NULL DEFAULT '0',
  user_message_rules tinyint(1) NOT NULL DEFAULT '0',
  user_full_folder int(11) NOT NULL DEFAULT '-3',
  user_emailtime int(11) NOT NULL DEFAULT '0',
  user_topic_show_days smallint(4) NOT NULL DEFAULT '0',
  user_topic_sortby_type varchar(1) NOT NULL DEFAULT 't',
  user_topic_sortby_dir varchar(1) NOT NULL DEFAULT 'd',
  user_post_show_days smallint(4) NOT NULL DEFAULT '0',
  user_post_sortby_type varchar(1) NOT NULL DEFAULT 't',
  user_post_sortby_dir varchar(1) NOT NULL DEFAULT 'a',
  user_notify tinyint(1) NOT NULL DEFAULT '0',
  user_notify_pm tinyint(1) NOT NULL DEFAULT '1',
  user_notify_type tinyint(4) NOT NULL DEFAULT '0',
  user_allow_pm tinyint(1) NOT NULL DEFAULT '1',
  user_allow_email tinyint(1) NOT NULL DEFAULT '1',
  user_allow_viewonline tinyint(1) NOT NULL DEFAULT '1',
  user_allow_viewemail tinyint(1) NOT NULL DEFAULT '1',
  user_allow_massemail tinyint(1) NOT NULL DEFAULT '1',
  user_options int(11) NOT NULL DEFAULT '893',
  user_avatar varchar(255) NOT NULL DEFAULT '',
  user_avatar_type tinyint(2) NOT NULL DEFAULT '0',
  user_avatar_width tinyint(4) NOT NULL DEFAULT '0',
  user_avatar_height tinyint(4) NOT NULL DEFAULT '0',
  user_sig text(65535),
  user_sig_bbcode_uid varchar(5) NOT NULL DEFAULT '',
  user_sig_bbcode_bitfield int(11) NOT NULL DEFAULT '0',
  user_from varchar(100) NOT NULL DEFAULT '',
  user_icq varchar(15) NOT NULL DEFAULT '',
  user_aim varchar(255) NOT NULL DEFAULT '',
  user_yim varchar(255) NOT NULL DEFAULT '',
  user_msnm varchar(255) NOT NULL DEFAULT '',
  user_jabber varchar(255) NOT NULL DEFAULT '',
  user_website varchar(200) NOT NULL DEFAULT '',
  user_occ varchar(255) NOT NULL DEFAULT '',
  user_interests varchar(255) NOT NULL DEFAULT '',
  user_actkey varchar(32) NOT NULL DEFAULT '',
  user_newpasswd varchar(32) NOT NULL DEFAULT ''
);

CREATE INDEX phpbb_users_user_birthday on phpbb_users (user_birthday);
CREATE INDEX phpbb_users_user_email_hash on phpbb_users (user_email_hash);
CREATE INDEX phpbb_users_username on phpbb_users (username);


# Table: phpbb_warnings
CREATE TABLE phpbb_warnings (
  warning_id INTEGER PRIMARY KEY NOT NULL,
  user_id mediumint(8) NOT NULL DEFAULT '0',
  post_id mediumint(8) NOT NULL DEFAULT '0',
  log_id mediumint(8) NOT NULL DEFAULT '0',
  warning_time int(11) NOT NULL DEFAULT '0'
);


# Table: phpbb_words
CREATE TABLE phpbb_words (
  word_id INTEGER PRIMARY KEY NOT NULL,
  word char(100) NOT NULL,
  replacement char(100) NOT NULL
);


# Table: phpbb_zebra
CREATE TABLE phpbb_zebra (
  user_id mediumint(8) NOT NULL DEFAULT '0',
  zebra_id mediumint(8) NOT NULL DEFAULT '0',
  friend tinyint(1) NOT NULL DEFAULT '0',
  foe tinyint(1) NOT NULL DEFAULT '0'
);

CREATE INDEX phpbb_zebra_user_id on phpbb_zebra (user_id);
CREATE INDEX phpbb_zebra_zebra_id on phpbb_zebra (zebra_id);

COMMIT;
