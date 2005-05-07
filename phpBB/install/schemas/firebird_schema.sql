#
# Firebird Schema for phpBB 3.x - (c) phpBB Group, 2005
#
# $Id$
#

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
  comment VARCHAR(255),
  extension VARCHAR(100),
  mimetype VARCHAR(100),
  filesize INTEGER DEFAULT 0  NOT NULL,
  filetime INTEGER DEFAULT 0  NOT NULL,
  thumbnail INTEGER DEFAULT 0  NOT NULL
);;

# phpbb_auth_groups
CREATE TABLE phpbb_auth_groups (
  group_id INTEGER DEFAULT 0  NOT NULL,
  forum_id INTEGER DEFAULT 0  NOT NULL,
  auth_option_id INTEGER DEFAULT 0  NOT NULL,
  auth_setting INTEGER DEFAULT 0  NOT NULL
);;

# phpbb_auth_options
CREATE TABLE phpbb_auth_options (
  auth_option_id INTEGER NOT NULL,
  auth_option VARCHAR(20) NOT NULL,
  is_global INTEGER DEFAULT 0  NOT NULL,
  is_local INTEGER DEFAULT 0  NOT NULL,
  founder_only INTEGER DEFAULT 0  NOT NULL
);;

# phpbb_auth_presets
CREATE TABLE phpbb_auth_presets (
  preset_id INTEGER NOT NULL,
  preset_name VARCHAR(50) NOT NULL,
  preset_user_id INTEGER DEFAULT 0  NOT NULL,
  preset_type VARCHAR(2) NOT NULL,
  preset_data BLOB SUB_TYPE TEXT NOT NULL
);;

# phpbb_auth_users
CREATE TABLE phpbb_auth_users (
  user_id INTEGER DEFAULT 0  NOT NULL,
  forum_id INTEGER DEFAULT 0  NOT NULL,
  auth_option_id INTEGER DEFAULT 0  NOT NULL,
  auth_setting INTEGER DEFAULT 0  NOT NULL
);;

# phpbb_banlist
CREATE TABLE phpbb_banlist (
  ban_id INTEGER NOT NULL,
  ban_userid INTEGER DEFAULT 0  NOT NULL,
  ban_ip VARCHAR(40) NOT NULL,
  ban_email VARCHAR(50) NOT NULL,
  ban_start INTEGER DEFAULT 0  NOT NULL,
  ban_end INTEGER DEFAULT 0  NOT NULL,
  ban_exclude INTEGER DEFAULT 0  NOT NULL,
  ban_reason VARCHAR(255) NOT NULL,
  ban_give_reason VARCHAR(255) NOT NULL
);;

# phpbb_bbcodes
CREATE TABLE phpbb_bbcodes (
  bbcode_id INTEGER DEFAULT 0  NOT NULL,
  bbcode_tag VARCHAR(16) NOT NULL,
  bbcode_match VARCHAR(255) NOT NULL,
  bbcode_tpl BLOB SUB_TYPE TEXT NOT NULL,
  first_pass_match VARCHAR(255) NOT NULL,
  first_pass_replace VARCHAR(255) NOT NULL,
  second_pass_match VARCHAR(255) NOT NULL,
  second_pass_replace BLOB SUB_TYPE TEXT NOT NULL
);;

# phpbb_bookmarks
CREATE TABLE phpbb_bookmarks (
  topic_id INTEGER DEFAULT 0  NOT NULL,
  user_id INTEGER DEFAULT 0  NOT NULL,
  order_id INTEGER DEFAULT 0  NOT NULL
);;

# phpbb_bots
CREATE TABLE phpbb_bots (
  bot_id INTEGER NOT NULL,
  bot_active INTEGER DEFAULT 1  NOT NULL,
  bot_name VARCHAR(255) NOT NULL,
  user_id INTEGER DEFAULT 0  NOT NULL,
  bot_agent VARCHAR(255) NOT NULL,
  bot_ip VARCHAR(255) NOT NULL
);;

# phpbb_cache
CREATE TABLE phpbb_cache (
  var_name VARCHAR(200) NOT NULL,
  var_expires INTEGER DEFAULT 0  NOT NULL,
  var_data BLOB SUB_TYPE TEXT NOT NULL
);;

# phpbb_config
CREATE TABLE phpbb_config (
  config_name VARCHAR(200) NOT NULL,
  config_value VARCHAR(255) NOT NULL,
  is_dynamic INTEGER DEFAULT 0  NOT NULL
);;

# phpbb_confirm
CREATE TABLE phpbb_confirm (
  confirm_id VARCHAR(32) NOT NULL,
  session_id VARCHAR(32) NOT NULL,
  code VARCHAR(6) NOT NULL
);;

# phpbb_disallow
CREATE TABLE phpbb_disallow (
  disallow_id INTEGER NOT NULL,
  disallow_username VARCHAR(30) NOT NULL
);;

# phpbb_drafts
CREATE TABLE phpbb_drafts (
  draft_id INTEGER NOT NULL,
  user_id INTEGER DEFAULT 0  NOT NULL,
  topic_id INTEGER DEFAULT 0  NOT NULL,
  forum_id INTEGER DEFAULT 0  NOT NULL,
  save_time INTEGER DEFAULT 0  NOT NULL,
  draft_subject VARCHAR(60),
  draft_message BLOB SUB_TYPE TEXT NOT NULL
);;

# phpbb_extension_groups
CREATE TABLE phpbb_extension_groups (
  group_id INTEGER NOT NULL,
  group_name VARCHAR(20) NOT NULL,
  cat_id INTEGER DEFAULT 0  NOT NULL,
  allow_group INTEGER DEFAULT 0  NOT NULL,
  download_mode INTEGER DEFAULT 1  NOT NULL,
  upload_icon VARCHAR(100) NOT NULL,
  max_filesize INTEGER DEFAULT 0  NOT NULL,
  allowed_forums BLOB SUB_TYPE TEXT NOT NULL,
  allow_in_pm INTEGER DEFAULT 0  NOT NULL
);;

# phpbb_extensions
CREATE TABLE phpbb_extensions (
  extension_id INTEGER NOT NULL,
  group_id INTEGER DEFAULT 0  NOT NULL,
  extension VARCHAR(100) NOT NULL
);;

# phpbb_forum_access
CREATE TABLE phpbb_forum_access (
  forum_id INTEGER DEFAULT 0  NOT NULL,
  user_id INTEGER DEFAULT 0  NOT NULL,
  session_id VARCHAR(32) NOT NULL
);;

# phpbb_forums
CREATE TABLE phpbb_forums (
  forum_id INTEGER NOT NULL,
  parent_id INTEGER DEFAULT 0  NOT NULL,
  left_id INTEGER DEFAULT 0  NOT NULL,
  right_id INTEGER DEFAULT 0  NOT NULL,
  forum_parents BLOB SUB_TYPE TEXT,
  forum_name VARCHAR(150) NOT NULL,
  forum_desc BLOB SUB_TYPE TEXT,
  forum_link VARCHAR(200) NOT NULL,
  forum_password VARCHAR(32) NOT NULL,
  forum_style INTEGER,
  forum_image VARCHAR(50) NOT NULL,
  forum_rules BLOB SUB_TYPE TEXT NOT NULL,
  forum_rules_link VARCHAR(200) NOT NULL,
  forum_rules_flags INTEGER DEFAULT 0  NOT NULL,
  forum_rules_bbcode_bitfield INTEGER DEFAULT 0  NOT NULL,
  forum_rules_bbcode_uid VARCHAR(5) NOT NULL,
  forum_topics_per_page INTEGER DEFAULT 0  NOT NULL,
  forum_type INTEGER DEFAULT 0  NOT NULL,
  forum_status INTEGER DEFAULT 0  NOT NULL,
  forum_posts INTEGER DEFAULT 0  NOT NULL,
  forum_topics INTEGER DEFAULT 0  NOT NULL,
  forum_topics_real INTEGER DEFAULT 0  NOT NULL,
  forum_last_post_id INTEGER DEFAULT 0  NOT NULL,
  forum_last_poster_id INTEGER DEFAULT 0  NOT NULL,
  forum_last_post_time INTEGER DEFAULT 0  NOT NULL,
  forum_last_poster_name VARCHAR(30),
  forum_flags INTEGER DEFAULT 0  NOT NULL,
  display_on_index INTEGER DEFAULT 1  NOT NULL,
  enable_indexing INTEGER DEFAULT 1  NOT NULL,
  enable_icons INTEGER DEFAULT 1  NOT NULL,
  enable_prune INTEGER DEFAULT 0  NOT NULL,
  prune_next INTEGER,
  prune_days INTEGER DEFAULT 0  NOT NULL,
  prune_viewed INTEGER DEFAULT 0  NOT NULL,
  prune_freq INTEGER DEFAULT 0  NOT NULL
);;

# phpbb_forums_marking
CREATE TABLE phpbb_forums_marking (
  user_id INTEGER DEFAULT 0  NOT NULL,
  forum_id INTEGER DEFAULT 0  NOT NULL,
  mark_time INTEGER DEFAULT 0  NOT NULL
);;

# phpbb_forums_watch
CREATE TABLE phpbb_forums_watch (
  forum_id INTEGER DEFAULT 0  NOT NULL,
  user_id INTEGER DEFAULT 0  NOT NULL,
  notify_status INTEGER DEFAULT 0  NOT NULL
);;

# phpbb_groups
CREATE TABLE phpbb_groups (
  group_id INTEGER NOT NULL,
  group_type INTEGER DEFAULT 1  NOT NULL,
  group_name VARCHAR(40) NOT NULL,
  group_display INTEGER DEFAULT 0  NOT NULL,
  group_avatar VARCHAR(100) NOT NULL,
  group_avatar_type INTEGER DEFAULT 0  NOT NULL,
  group_avatar_width INTEGER DEFAULT 0  NOT NULL,
  group_avatar_height INTEGER DEFAULT 0  NOT NULL,
  group_rank INTEGER DEFAULT -1  NOT NULL,
  group_colour VARCHAR(6) NOT NULL,
  group_sig_chars INTEGER DEFAULT 0  NOT NULL,
  group_receive_pm INTEGER DEFAULT 0  NOT NULL,
  group_message_limit INTEGER DEFAULT 0  NOT NULL,
  group_chgpass INTEGER DEFAULT 0  NOT NULL,
  group_description VARCHAR(255) NOT NULL,
  group_legend INTEGER DEFAULT 1  NOT NULL
);;

# phpbb_icons
CREATE TABLE phpbb_icons (
  icons_id INTEGER NOT NULL,
  icons_url VARCHAR(50),
  icons_width INTEGER DEFAULT 0  NOT NULL,
  icons_height INTEGER DEFAULT 0  NOT NULL,
  icons_order INTEGER DEFAULT 0  NOT NULL,
  display_on_posting INTEGER DEFAULT 1  NOT NULL
);;

# phpbb_lang
CREATE TABLE phpbb_lang (
  lang_id INTEGER NOT NULL,
  lang_iso VARCHAR(5) NOT NULL,
  lang_dir VARCHAR(30) NOT NULL,
  lang_english_name VARCHAR(30),
  lang_local_name VARCHAR(100),
  lang_author VARCHAR(100)
);;

# phpbb_log
CREATE TABLE phpbb_log (
  log_id INTEGER NOT NULL,
  log_type INTEGER DEFAULT 0  NOT NULL,
  user_id INTEGER DEFAULT 0  NOT NULL,
  forum_id INTEGER DEFAULT 0  NOT NULL,
  topic_id INTEGER DEFAULT 0  NOT NULL,
  reportee_id INTEGER DEFAULT 0  NOT NULL,
  log_ip VARCHAR(40) NOT NULL,
  log_time INTEGER DEFAULT 0  NOT NULL,
  log_operation BLOB SUB_TYPE TEXT,
  log_data BLOB SUB_TYPE TEXT
);;

# phpbb_moderator_cache
CREATE TABLE phpbb_moderator_cache (
  forum_id INTEGER DEFAULT 0  NOT NULL,
  user_id INTEGER DEFAULT 0  NOT NULL,
  username VARCHAR(30) NOT NULL,
  group_id INTEGER DEFAULT 0  NOT NULL,
  groupname VARCHAR(30) NOT NULL,
  display_on_index INTEGER DEFAULT 1  NOT NULL
);;

# phpbb_modules
CREATE TABLE phpbb_modules (
  module_id INTEGER NOT NULL,
  module_type VARCHAR(3) NOT NULL,
  module_title VARCHAR(50) NOT NULL,
  module_filename VARCHAR(50) NOT NULL,
  module_order INTEGER DEFAULT 0  NOT NULL,
  module_enabled INTEGER DEFAULT 1  NOT NULL,
  module_subs BLOB SUB_TYPE TEXT NOT NULL,
  module_acl VARCHAR(255) NOT NULL
);;

# phpbb_poll_results
CREATE TABLE phpbb_poll_results (
  poll_option_id INTEGER DEFAULT 0  NOT NULL,
  topic_id INTEGER DEFAULT 0  NOT NULL,
  poll_option_text VARCHAR(255) NOT NULL,
  poll_option_total INTEGER DEFAULT 0  NOT NULL
);;

# phpbb_poll_voters
CREATE TABLE phpbb_poll_voters (
  topic_id INTEGER DEFAULT 0  NOT NULL,
  poll_option_id INTEGER DEFAULT 0  NOT NULL,
  vote_user_id INTEGER DEFAULT 0  NOT NULL,
  vote_user_ip VARCHAR(40) NOT NULL
);;

# phpbb_posts
CREATE TABLE phpbb_posts (
  post_id INTEGER NOT NULL,
  topic_id INTEGER DEFAULT 0  NOT NULL,
  forum_id INTEGER DEFAULT 0  NOT NULL,
  poster_id INTEGER DEFAULT 0  NOT NULL,
  icon_id INTEGER DEFAULT 1  NOT NULL,
  poster_ip VARCHAR(40) NOT NULL,
  post_time INTEGER DEFAULT 0  NOT NULL,
  post_approved INTEGER DEFAULT 1  NOT NULL,
  post_reported INTEGER DEFAULT 0  NOT NULL,
  enable_bbcode INTEGER DEFAULT 1  NOT NULL,
  enable_html INTEGER DEFAULT 0  NOT NULL,
  enable_smilies INTEGER DEFAULT 1  NOT NULL,
  enable_magic_url INTEGER DEFAULT 1  NOT NULL,
  enable_sig INTEGER DEFAULT 1  NOT NULL,
  post_username VARCHAR(30),
  post_subject VARCHAR(60),
  post_text BLOB SUB_TYPE TEXT,
  post_checksum VARCHAR(32) NOT NULL,
  post_encoding VARCHAR(11) DEFAULT 'iso-8859-1'  NOT NULL,
  post_attachment INTEGER DEFAULT 0  NOT NULL,
  bbcode_bitfield INTEGER DEFAULT 0  NOT NULL,
  bbcode_uid VARCHAR(5) NOT NULL,
  post_edit_time INTEGER DEFAULT 0  NOT NULL,
  post_edit_reason VARCHAR(100),
  post_edit_user INTEGER DEFAULT 0  NOT NULL,
  post_edit_count INTEGER DEFAULT 0  NOT NULL,
  post_edit_locked INTEGER DEFAULT 0  NOT NULL
);;

# phpbb_privmsgs
CREATE TABLE phpbb_privmsgs (
  msg_id INTEGER NOT NULL,
  root_level INTEGER DEFAULT 0  NOT NULL,
  author_id INTEGER DEFAULT 0  NOT NULL,
  icon_id INTEGER DEFAULT 1  NOT NULL,
  author_ip VARCHAR(40) NOT NULL,
  message_time INTEGER DEFAULT 0  NOT NULL,
  message_reported INTEGER DEFAULT 0  NOT NULL,
  enable_bbcode INTEGER DEFAULT 1  NOT NULL,
  enable_html INTEGER DEFAULT 0  NOT NULL,
  enable_smilies INTEGER DEFAULT 1  NOT NULL,
  enable_magic_url INTEGER DEFAULT 1  NOT NULL,
  enable_sig INTEGER DEFAULT 1  NOT NULL,
  message_subject VARCHAR(60),
  message_text BLOB SUB_TYPE TEXT,
  message_edit_reason VARCHAR(100),
  message_edit_user INTEGER DEFAULT 0  NOT NULL,
  message_checksum VARCHAR(32) NOT NULL,
  message_encoding VARCHAR(11) DEFAULT 'iso-8859-1'  NOT NULL,
  message_attachment INTEGER DEFAULT 0  NOT NULL,
  bbcode_bitfield INTEGER DEFAULT 0  NOT NULL,
  bbcode_uid VARCHAR(5) NOT NULL,
  message_edit_time INTEGER DEFAULT 0  NOT NULL,
  message_edit_count INTEGER DEFAULT 0  NOT NULL,
  to_address BLOB SUB_TYPE TEXT,
  bcc_address BLOB SUB_TYPE TEXT
);;

# phpbb_privmsgs_folder
CREATE TABLE phpbb_privmsgs_folder (
  folder_id INTEGER NOT NULL,
  user_id INTEGER DEFAULT 0  NOT NULL,
  folder_name VARCHAR(40) NOT NULL,
  pm_count INTEGER DEFAULT 0  NOT NULL
);;

# phpbb_privmsgs_rules
CREATE TABLE phpbb_privmsgs_rules (
  rule_id INTEGER NOT NULL,
  user_id INTEGER DEFAULT 0  NOT NULL,
  rule_check INTEGER DEFAULT 0  NOT NULL,
  rule_connection INTEGER DEFAULT 0  NOT NULL,
  rule_string VARCHAR(255) NOT NULL,
  rule_user_id INTEGER DEFAULT 0  NOT NULL,
  rule_group_id INTEGER DEFAULT 0  NOT NULL,
  rule_action INTEGER DEFAULT 0  NOT NULL,
  rule_folder_id INTEGER DEFAULT 0  NOT NULL
);;

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

# phpbb_profile_fields
CREATE TABLE phpbb_profile_fields (
  field_id INTEGER NOT NULL,
  field_name VARCHAR(50) NOT NULL,
  field_desc VARCHAR(255) NOT NULL,
  field_type INTEGER DEFAULT 0  NOT NULL,
  field_ident VARCHAR(20) NOT NULL,
  field_length VARCHAR(20) NOT NULL,
  field_minlen VARCHAR(255) NOT NULL,
  field_maxlen VARCHAR(255) NOT NULL,
  field_novalue VARCHAR(255) NOT NULL,
  field_default_value VARCHAR(255) DEFAULT '0'  NOT NULL,
  field_validation VARCHAR(20) NOT NULL,
  field_required INTEGER DEFAULT 0  NOT NULL,
  field_show_on_reg INTEGER DEFAULT 0  NOT NULL,
  field_hide INTEGER DEFAULT 0  NOT NULL,
  field_no_view INTEGER DEFAULT 0  NOT NULL,
  field_active INTEGER DEFAULT 0  NOT NULL,
  field_order INTEGER DEFAULT 0  NOT NULL
);;

# phpbb_profile_fields_data
CREATE TABLE phpbb_profile_fields_data (
  user_id INTEGER DEFAULT 0  NOT NULL
);;

# phpbb_profile_fields_lang
CREATE TABLE phpbb_profile_fields_lang (
  field_id INTEGER DEFAULT 0  NOT NULL,
  lang_id INTEGER DEFAULT 0  NOT NULL,
  option_id INTEGER DEFAULT 0  NOT NULL,
  field_type INTEGER DEFAULT 0  NOT NULL,
  valueCol VARCHAR(255) NOT NULL
);;

# phpbb_profile_lang
CREATE TABLE phpbb_profile_lang (
  field_id INTEGER DEFAULT 0  NOT NULL,
  lang_id INTEGER DEFAULT 0  NOT NULL,
  lang_name VARCHAR(255) NOT NULL,
  lang_explain BLOB SUB_TYPE TEXT NOT NULL,
  lang_default_value VARCHAR(255) NOT NULL
);;

# phpbb_ranks
CREATE TABLE phpbb_ranks (
  rank_id INTEGER NOT NULL,
  rank_title VARCHAR(50) NOT NULL,
  rank_min INTEGER DEFAULT 0  NOT NULL,
  rank_special INTEGER DEFAULT 0 ,
  rank_image VARCHAR(100)
);;

# phpbb_reports
CREATE TABLE phpbb_reports (
  report_id INTEGER NOT NULL,
  reason_id INTEGER DEFAULT 0  NOT NULL,
  post_id INTEGER DEFAULT 0  NOT NULL,
  msg_id INTEGER DEFAULT 0  NOT NULL,
  user_id INTEGER DEFAULT 0  NOT NULL,
  user_notify INTEGER DEFAULT 0  NOT NULL,
  report_time INTEGER DEFAULT 0  NOT NULL,
  report_text BLOB SUB_TYPE TEXT NOT NULL
);;

# phpbb_reports_reasons
CREATE TABLE phpbb_reports_reasons (
  reason_id INTEGER NOT NULL,
  reason_priority INTEGER DEFAULT 0  NOT NULL,
  reason_name VARCHAR(255) NOT NULL,
  reason_description BLOB SUB_TYPE TEXT NOT NULL
);;

# phpbb_search_results
CREATE TABLE phpbb_search_results (
  search_id INTEGER DEFAULT 0  NOT NULL,
  session_id VARCHAR(32) NOT NULL,
  search_time INTEGER DEFAULT 0  NOT NULL,
  search_array BLOB SUB_TYPE TEXT NOT NULL
);;

# phpbb_search_wordlist
CREATE TABLE phpbb_search_wordlist (
  word_text VARCHAR(50) NOT NULL,
  word_id INTEGER NOT NULL,
  word_common INTEGER DEFAULT 0  NOT NULL
);;

# phpbb_search_wordmatch
CREATE TABLE phpbb_search_wordmatch (
  post_id INTEGER DEFAULT 0  NOT NULL,
  word_id INTEGER DEFAULT 0  NOT NULL,
  title_match INTEGER DEFAULT 0  NOT NULL
);;

# phpbb_sessions
CREATE TABLE phpbb_sessions (
  session_id VARCHAR(32) NOT NULL,
  session_user_id INTEGER DEFAULT 0  NOT NULL,
  session_last_visit INTEGER DEFAULT 0  NOT NULL,
  session_start INTEGER DEFAULT 0  NOT NULL,
  session_time INTEGER DEFAULT 0  NOT NULL,
  session_ip VARCHAR(40) DEFAULT '0'  NOT NULL,
  session_browser VARCHAR(100),
  session_page VARCHAR(100) NOT NULL,
  session_viewonline INTEGER DEFAULT 1  NOT NULL,
  session_admin INTEGER DEFAULT 0  NOT NULL
);;

# phpbb_sitelist
CREATE TABLE phpbb_sitelist (
  site_id INTEGER NOT NULL,
  site_ip VARCHAR(40) NOT NULL,
  site_hostname VARCHAR(255) NOT NULL,
  ip_exclude INTEGER DEFAULT 0  NOT NULL
);;

# phpbb_smilies
CREATE TABLE phpbb_smilies (
  smiley_id INTEGER NOT NULL,
  code VARCHAR(10),
  emotion VARCHAR(50),
  smiley_url VARCHAR(50),
  smiley_width INTEGER DEFAULT 0  NOT NULL,
  smiley_height INTEGER DEFAULT 0  NOT NULL,
  smiley_order INTEGER DEFAULT 0  NOT NULL,
  display_on_posting INTEGER DEFAULT 1  NOT NULL
);;

# phpbb_styles
CREATE TABLE phpbb_styles (
  style_id INTEGER NOT NULL,
  style_name VARCHAR(30) NOT NULL,
  style_copyright VARCHAR(50) NOT NULL,
  style_active INTEGER DEFAULT 1  NOT NULL,
  template_id INTEGER DEFAULT 0  NOT NULL,
  theme_id INTEGER DEFAULT 0  NOT NULL,
  imageset_id INTEGER DEFAULT 0  NOT NULL
);;

# phpbb_styles_imageset
CREATE TABLE phpbb_styles_imageset (
  imageset_id INTEGER NOT NULL,
  imageset_name VARCHAR(30) NOT NULL,
  imageset_copyright VARCHAR(50) NOT NULL,
  imageset_path VARCHAR(30) NOT NULL,
  site_logo VARCHAR(200) NOT NULL,
  btn_post VARCHAR(200) NOT NULL,
  btn_post_pm VARCHAR(200) NOT NULL,
  btn_reply VARCHAR(200) NOT NULL,
  btn_reply_pm VARCHAR(200) NOT NULL,
  btn_locked VARCHAR(200) NOT NULL,
  btn_profile VARCHAR(200) NOT NULL,
  btn_pm VARCHAR(200) NOT NULL,
  btn_delete VARCHAR(200) NOT NULL,
  btn_info VARCHAR(200) NOT NULL,
  btn_quote VARCHAR(200) NOT NULL,
  btn_search VARCHAR(200) NOT NULL,
  btn_edit VARCHAR(200) NOT NULL,
  btn_report VARCHAR(200) NOT NULL,
  btn_email VARCHAR(200) NOT NULL,
  btn_www VARCHAR(200) NOT NULL,
  btn_icq VARCHAR(200) NOT NULL,
  btn_aim VARCHAR(200) NOT NULL,
  btn_yim VARCHAR(200) NOT NULL,
  btn_msnm VARCHAR(200) NOT NULL,
  btn_jabber VARCHAR(200) NOT NULL,
  btn_online VARCHAR(200) NOT NULL,
  btn_offline VARCHAR(200) NOT NULL,
  btn_friend VARCHAR(200) NOT NULL,
  btn_foe VARCHAR(200) NOT NULL,
  icon_unapproved VARCHAR(200) NOT NULL,
  icon_reported VARCHAR(200) NOT NULL,
  icon_attach VARCHAR(200) NOT NULL,
  icon_post VARCHAR(200) NOT NULL,
  icon_post_new VARCHAR(200) NOT NULL,
  icon_post_latest VARCHAR(200) NOT NULL,
  icon_post_newest VARCHAR(200) NOT NULL,
  forum VARCHAR(200) NOT NULL,
  forum_new VARCHAR(200) NOT NULL,
  forum_locked VARCHAR(200) NOT NULL,
  forum_link VARCHAR(200) NOT NULL,
  sub_forum VARCHAR(200) NOT NULL,
  sub_forum_new VARCHAR(200) NOT NULL,
  folder VARCHAR(200) NOT NULL,
  folder_moved VARCHAR(200) NOT NULL,
  folder_posted VARCHAR(200) NOT NULL,
  folder_new VARCHAR(200) NOT NULL,
  folder_new_posted VARCHAR(200) NOT NULL,
  folder_hot VARCHAR(200) NOT NULL,
  folder_hot_posted VARCHAR(200) NOT NULL,
  folder_hot_new VARCHAR(200) NOT NULL,
  folder_hot_new_posted VARCHAR(200) NOT NULL,
  folder_locked VARCHAR(200) NOT NULL,
  folder_locked_posted VARCHAR(200) NOT NULL,
  folder_locked_new VARCHAR(200) NOT NULL,
  folder_locked_new_posted VARCHAR(200) NOT NULL,
  folder_sticky VARCHAR(200) NOT NULL,
  folder_sticky_posted VARCHAR(200) NOT NULL,
  folder_sticky_new VARCHAR(200) NOT NULL,
  folder_sticky_new_posted VARCHAR(200) NOT NULL,
  folder_announce VARCHAR(200) NOT NULL,
  folder_announce_posted VARCHAR(200) NOT NULL,
  folder_announce_new VARCHAR(200) NOT NULL,
  folder_announce_new_posted VARCHAR(200) NOT NULL,
  folder_global VARCHAR(200) NOT NULL,
  folder_global_posted VARCHAR(200) NOT NULL,
  folder_global_new VARCHAR(200) NOT NULL,
  folder_global_new_posted VARCHAR(200) NOT NULL,
  poll_left VARCHAR(200) NOT NULL,
  poll_center VARCHAR(200) NOT NULL,
  poll_right VARCHAR(200) NOT NULL,
  attach_progress_bar VARCHAR(200) NOT NULL,
  user_icon1 VARCHAR(200) NOT NULL,
  user_icon2 VARCHAR(200) NOT NULL,
  user_icon3 VARCHAR(200) NOT NULL,
  user_icon4 VARCHAR(200) NOT NULL,
  user_icon5 VARCHAR(200) NOT NULL,
  user_icon6 VARCHAR(200) NOT NULL,
  user_icon7 VARCHAR(200) NOT NULL,
  user_icon8 VARCHAR(200) NOT NULL,
  user_icon9 VARCHAR(200) NOT NULL,
  user_icon10 VARCHAR(200) NOT NULL
);;

# phpbb_styles_template
CREATE TABLE phpbb_styles_template (
  template_id INTEGER NOT NULL,
  template_name VARCHAR(30) NOT NULL,
  template_copyright VARCHAR(50) NOT NULL,
  template_path VARCHAR(30) NOT NULL,
  bbcode_bitfield INTEGER DEFAULT 0  NOT NULL,
  template_storedb INTEGER DEFAULT 0  NOT NULL
);;

# phpbb_styles_template_data
CREATE TABLE phpbb_styles_template_data (
  template_id INTEGER DEFAULT 0  NOT NULL,
  template_filename VARCHAR(50) NOT NULL,
  template_included BLOB SUB_TYPE TEXT NOT NULL,
  template_mtime INTEGER DEFAULT 0  NOT NULL,
  template_data BLOB SUB_TYPE TEXT
);;

# phpbb_styles_theme
CREATE TABLE phpbb_styles_theme (
  theme_id INTEGER NOT NULL,
  theme_name VARCHAR(30) NOT NULL,
  theme_copyright VARCHAR(50) NOT NULL,
  theme_path VARCHAR(30) NOT NULL,
  theme_storedb INTEGER DEFAULT 0  NOT NULL,
  theme_mtime INTEGER DEFAULT 0  NOT NULL,
  theme_data BLOB SUB_TYPE TEXT NOT NULL
);;

# phpbb_topics
CREATE TABLE phpbb_topics (
  topic_id INTEGER NOT NULL,
  forum_id INTEGER DEFAULT 0  NOT NULL,
  icon_id INTEGER DEFAULT 1  NOT NULL,
  topic_attachment INTEGER DEFAULT 0  NOT NULL,
  topic_approved INTEGER DEFAULT 1  NOT NULL,
  topic_reported INTEGER DEFAULT 0  NOT NULL,
  topic_title VARCHAR(60) NOT NULL,
  topic_poster INTEGER DEFAULT 0  NOT NULL,
  topic_time INTEGER DEFAULT 0  NOT NULL,
  topic_time_limit INTEGER DEFAULT 0  NOT NULL,
  topic_views INTEGER DEFAULT 0  NOT NULL,
  topic_replies INTEGER DEFAULT 0  NOT NULL,
  topic_replies_real INTEGER DEFAULT 0  NOT NULL,
  topic_status INTEGER DEFAULT 0  NOT NULL,
  topic_type INTEGER DEFAULT 0  NOT NULL,
  topic_first_post_id INTEGER DEFAULT 0  NOT NULL,
  topic_first_poster_name VARCHAR(30),
  topic_last_post_id INTEGER DEFAULT 0  NOT NULL,
  topic_last_poster_id INTEGER DEFAULT 0  NOT NULL,
  topic_last_poster_name VARCHAR(30),
  topic_last_post_time INTEGER DEFAULT 0  NOT NULL,
  topic_last_view_time INTEGER DEFAULT 0  NOT NULL,
  topic_moved_id INTEGER DEFAULT 0  NOT NULL,
  topic_bumped INTEGER DEFAULT 0  NOT NULL,
  topic_bumper INTEGER DEFAULT 0  NOT NULL,
  poll_title VARCHAR(255) NOT NULL,
  poll_start INTEGER DEFAULT 0  NOT NULL,
  poll_length INTEGER DEFAULT 0  NOT NULL,
  poll_max_options INTEGER DEFAULT 1  NOT NULL,
  poll_last_vote INTEGER DEFAULT 0 ,
  poll_vote_change INTEGER DEFAULT 0  NOT NULL
);;

# phpbb_topics_marking
CREATE TABLE phpbb_topics_marking (
  user_id INTEGER DEFAULT 0  NOT NULL,
  topic_id INTEGER DEFAULT 0  NOT NULL,
  forum_id INTEGER DEFAULT 0  NOT NULL,
  mark_type INTEGER DEFAULT 0  NOT NULL,
  mark_time INTEGER DEFAULT 0  NOT NULL
);;

# phpbb_topics_watch
CREATE TABLE phpbb_topics_watch (
  topic_id INTEGER DEFAULT 0  NOT NULL,
  user_id INTEGER DEFAULT 0  NOT NULL,
  notify_status INTEGER DEFAULT 0  NOT NULL
);;

# phpbb_user_group
CREATE TABLE phpbb_user_group (
  group_id INTEGER DEFAULT 0  NOT NULL,
  user_id INTEGER DEFAULT 0  NOT NULL,
  group_leader INTEGER DEFAULT 0  NOT NULL,
  user_pending INTEGER
);;

# phpbb_users
CREATE TABLE phpbb_users (
  user_id INTEGER NOT NULL,
  user_type INTEGER DEFAULT 0  NOT NULL,
  group_id INTEGER DEFAULT 3  NOT NULL,
  user_permissions BLOB SUB_TYPE TEXT NOT NULL,
  user_ip VARCHAR(40) NOT NULL,
  user_regdate INTEGER DEFAULT 0  NOT NULL,
  username VARCHAR(30) NOT NULL,
  user_password VARCHAR(32) NOT NULL,
  user_passchg INTEGER DEFAULT 0  NOT NULL,
  user_email VARCHAR(60) NOT NULL,
  user_email_hash DOUBLE PRECISION DEFAULT 0  NOT NULL,
  user_birthday VARCHAR(10) NOT NULL,
  user_lastvisit INTEGER DEFAULT 0  NOT NULL,
  user_lastpost_time INTEGER DEFAULT 0  NOT NULL,
  user_lastpage VARCHAR(100) NOT NULL,
  user_last_confirm_key VARCHAR(10) NOT NULL,
  user_warnings INTEGER DEFAULT 0  NOT NULL,
  user_posts INTEGER DEFAULT 0  NOT NULL,
  user_lang VARCHAR(30) NOT NULL,
  user_timezone DOUBLE PRECISION DEFAULT 0  NOT NULL,
  user_dst INTEGER DEFAULT 0  NOT NULL,
  user_dateformat VARCHAR(15) DEFAULT 'd M Y H:i'  NOT NULL,
  user_style INTEGER DEFAULT 0  NOT NULL,
  user_rank INTEGER DEFAULT 0 ,
  user_colour VARCHAR(6) NOT NULL,
  user_new_privmsg INTEGER DEFAULT 0  NOT NULL,
  user_unread_privmsg INTEGER DEFAULT 0  NOT NULL,
  user_last_privmsg INTEGER DEFAULT 0  NOT NULL,
  user_message_rules INTEGER DEFAULT 0  NOT NULL,
  user_full_folder INTEGER DEFAULT -3  NOT NULL,
  user_emailtime INTEGER DEFAULT 0  NOT NULL,
  user_sortby_type VARCHAR(2) NOT NULL,
  user_sortby_dir VARCHAR(2) NOT NULL,
  user_topic_show_days INTEGER DEFAULT 0  NOT NULL,
  user_topic_sortby_type VARCHAR(1) NOT NULL,
  user_topic_sortby_dir VARCHAR(1) NOT NULL,
  user_post_show_days INTEGER DEFAULT 0  NOT NULL,
  user_post_sortby_type VARCHAR(2) NOT NULL,
  user_post_sortby_dir VARCHAR(2) NOT NULL,
  user_notify INTEGER DEFAULT 0  NOT NULL,
  user_notify_pm INTEGER DEFAULT 1  NOT NULL,
  user_notify_type INTEGER DEFAULT 0  NOT NULL,
  user_allow_pm INTEGER DEFAULT 1  NOT NULL,
  user_allow_email INTEGER DEFAULT 1  NOT NULL,
  user_allow_viewonline INTEGER DEFAULT 1  NOT NULL,
  user_allow_viewemail INTEGER DEFAULT 1  NOT NULL,
  user_allow_massemail INTEGER DEFAULT 1  NOT NULL,
  user_options INTEGER DEFAULT 893  NOT NULL,
  user_avatar VARCHAR(100) NOT NULL,
  user_avatar_type INTEGER DEFAULT 0  NOT NULL,
  user_avatar_width INTEGER DEFAULT 0  NOT NULL,
  user_avatar_height INTEGER DEFAULT 0  NOT NULL,
  user_sig BLOB SUB_TYPE TEXT NOT NULL,
  user_sig_bbcode_uid VARCHAR(5) NOT NULL,
  user_sig_bbcode_bitfield INTEGER DEFAULT 0  NOT NULL,
  user_from VARCHAR(100) NOT NULL,
  user_icq VARCHAR(15) NOT NULL,
  user_aim VARCHAR(255) NOT NULL,
  user_yim VARCHAR(255) NOT NULL,
  user_msnm VARCHAR(255) NOT NULL,
  user_jabber VARCHAR(255) NOT NULL,
  user_website VARCHAR(100) NOT NULL,
  user_occ VARCHAR(255) NOT NULL,
  user_interests VARCHAR(255) NOT NULL,
  user_actkey VARCHAR(32) NOT NULL,
  user_newpasswd VARCHAR(32) NOT NULL
);;

# phpbb_words
CREATE TABLE phpbb_words (
  word_id INTEGER NOT NULL,
  word VARCHAR(100) NOT NULL,
  replacement VARCHAR(100) NOT NULL
);;

# phpbb_zebra
CREATE TABLE phpbb_zebra (
  user_id INTEGER DEFAULT 0  NOT NULL,
  zebra_id INTEGER DEFAULT 0  NOT NULL,
  friend INTEGER DEFAULT 0  NOT NULL,
  foe INTEGER DEFAULT 0  NOT NULL
);;

ALTER TABLE phpbb_attachments
ADD PRIMARY KEY (
  attach_id
);;

CREATE INDEX filesize1
ON phpbb_attachments(
  filesize
);;

CREATE INDEX filetime2
ON phpbb_attachments(
  filetime
);;

CREATE INDEX post_msg_id4
ON phpbb_attachments(
  post_msg_id
);;

CREATE INDEX poster_id5
ON phpbb_attachments(
  poster_id
);;

CREATE INDEX topic_id6
ON phpbb_attachments(
  topic_id
);;

CREATE INDEX auth_option_id7
ON phpbb_auth_groups(
  auth_option_id
);;

CREATE INDEX group_id8
ON phpbb_auth_groups(
  group_id
);;

ALTER TABLE phpbb_auth_options
ADD PRIMARY KEY (
  auth_option_id
);;

CREATE INDEX auth_option9
ON phpbb_auth_options(
  auth_option
);;

ALTER TABLE phpbb_auth_presets
ADD PRIMARY KEY (
  preset_id
);;

CREATE INDEX preset_type10
ON phpbb_auth_presets(
  preset_type
);;

CREATE INDEX auth_option_id11
ON phpbb_auth_users(
  auth_option_id
);;

CREATE INDEX user_id12
ON phpbb_auth_users(
  user_id
);;

ALTER TABLE phpbb_banlist
ADD PRIMARY KEY (
  ban_id
);;

ALTER TABLE phpbb_bbcodes
ADD PRIMARY KEY (
  bbcode_id
);;

CREATE INDEX order_id13
ON phpbb_bookmarks(
  order_id
);;

CREATE INDEX topic_user_id14
ON phpbb_bookmarks(
  topic_id,
  user_id
);;

ALTER TABLE phpbb_bots
ADD PRIMARY KEY (
  bot_id
);;

CREATE INDEX bot_active15
ON phpbb_bots(
  bot_active
);;

ALTER TABLE phpbb_cache
ADD PRIMARY KEY (
  var_name
);;

ALTER TABLE phpbb_config
ADD PRIMARY KEY (
  config_name
);;

CREATE INDEX is_dynamic16
ON phpbb_config(
  is_dynamic
);;

ALTER TABLE phpbb_confirm
ADD PRIMARY KEY (
  session_id,
  confirm_id
);;

ALTER TABLE phpbb_disallow
ADD PRIMARY KEY (
  disallow_id
);;

ALTER TABLE phpbb_drafts
ADD PRIMARY KEY (
  draft_id
);;

CREATE INDEX save_time17
ON phpbb_drafts(
  save_time
);;

ALTER TABLE phpbb_extension_groups
ADD PRIMARY KEY (
  group_id
);;

ALTER TABLE phpbb_extensions
ADD PRIMARY KEY (
  extension_id
);;

ALTER TABLE phpbb_forum_access
ADD PRIMARY KEY (
  forum_id,
  user_id,
  session_id
);;

ALTER TABLE phpbb_forums
ADD PRIMARY KEY (
  forum_id
);;

CREATE INDEX forum_last_post_id18
ON phpbb_forums(
  forum_last_post_id
);;

CREATE INDEX left_right_id19
ON phpbb_forums(
  left_id,
  right_id
);;

ALTER TABLE phpbb_forums_marking
ADD PRIMARY KEY (
  user_id,
  forum_id
);;

CREATE INDEX forum_id20
ON phpbb_forums_watch(
  forum_id
);;

CREATE INDEX notify_status21
ON phpbb_forums_watch(
  notify_status
);;

CREATE INDEX user_id22
ON phpbb_forums_watch(
  user_id
);;

ALTER TABLE phpbb_groups
ADD PRIMARY KEY (
  group_id
);;

CREATE INDEX group_legend23
ON phpbb_groups(
  group_legend
);;

ALTER TABLE phpbb_icons
ADD PRIMARY KEY (
  icons_id
);;

ALTER TABLE phpbb_lang
ADD PRIMARY KEY (
  lang_id
);;

ALTER TABLE phpbb_log
ADD PRIMARY KEY (
  log_id
);;

CREATE INDEX forum_id24
ON phpbb_log(
  forum_id
);;

CREATE INDEX log_type25
ON phpbb_log(
  log_type
);;

CREATE INDEX reportee_id26
ON phpbb_log(
  reportee_id
);;

CREATE INDEX topic_id27
ON phpbb_log(
  topic_id
);;

CREATE INDEX user_id28
ON phpbb_log(
  user_id
);;

CREATE INDEX display_on_index29
ON phpbb_moderator_cache(
  display_on_index
);;

CREATE INDEX forum_id30
ON phpbb_moderator_cache(
  forum_id
);;

ALTER TABLE phpbb_modules
ADD PRIMARY KEY (
  module_id
);;

CREATE INDEX module_type31
ON phpbb_modules(
  module_type,
  module_enabled
);;

CREATE INDEX poll_option_id32
ON phpbb_poll_results(
  poll_option_id
);;

CREATE INDEX topic_id33
ON phpbb_poll_results(
  topic_id
);;

CREATE INDEX vote_user_id35
ON phpbb_poll_voters(
  vote_user_id
);;

CREATE INDEX vote_user_ip36
ON phpbb_poll_voters(
  vote_user_ip
);;

ALTER TABLE phpbb_posts
ADD PRIMARY KEY (
  post_id
);;

CREATE INDEX forum_id37
ON phpbb_posts(
  forum_id
);;

CREATE INDEX post_approved38
ON phpbb_posts(
  post_approved
);;

CREATE INDEX post_time39
ON phpbb_posts(
  post_time
);;

CREATE INDEX poster_id40
ON phpbb_posts(
  poster_id
);;

CREATE INDEX poster_ip41
ON phpbb_posts(
  poster_ip
);;

CREATE INDEX topic_id42
ON phpbb_posts(
  topic_id
);;

ALTER TABLE phpbb_privmsgs
ADD PRIMARY KEY (
  msg_id
);;

CREATE INDEX author_id43
ON phpbb_privmsgs(
  author_id
);;

CREATE INDEX author_ip44
ON phpbb_privmsgs(
  author_ip
);;

CREATE INDEX message_time45
ON phpbb_privmsgs(
  message_time
);;

CREATE INDEX root_level46
ON phpbb_privmsgs(
  root_level
);;

ALTER TABLE phpbb_privmsgs_folder
ADD PRIMARY KEY (
  folder_id
);;

CREATE INDEX user_id47
ON phpbb_privmsgs_folder(
  user_id
);;

ALTER TABLE phpbb_privmsgs_rules
ADD PRIMARY KEY (
  rule_id
);;

CREATE INDEX msg_id48
ON phpbb_privmsgs_to(
  msg_id
);;

CREATE INDEX user_id49
ON phpbb_privmsgs_to(
  user_id,
  folder_id
);;

ALTER TABLE phpbb_profile_fields
ADD PRIMARY KEY (
  field_id
);;

CREATE INDEX field_order50
ON phpbb_profile_fields(
  field_order
);;

CREATE INDEX field_type51
ON phpbb_profile_fields(
  field_type
);;

ALTER TABLE phpbb_profile_fields_data
ADD PRIMARY KEY (
  user_id
);;

ALTER TABLE phpbb_profile_fields_lang
ADD PRIMARY KEY (
  field_id,
  lang_id,
  option_id
);;

ALTER TABLE phpbb_profile_lang
ADD PRIMARY KEY (
  field_id,
  lang_id
);;

ALTER TABLE phpbb_ranks
ADD PRIMARY KEY (
  rank_id
);;

ALTER TABLE phpbb_reports
ADD PRIMARY KEY (
  report_id
);;

ALTER TABLE phpbb_reports_reasons
ADD PRIMARY KEY (
  reason_id
);;

ALTER TABLE phpbb_search_results
ADD PRIMARY KEY (
  search_id
);;

CREATE INDEX session_id54
ON phpbb_search_results(
  session_id
);;

ALTER TABLE phpbb_search_wordlist
ADD PRIMARY KEY (
  word_text
);;

CREATE INDEX word_id55
ON phpbb_search_wordlist(
  word_id
);;

ALTER TABLE phpbb_sessions
ADD PRIMARY KEY (
  session_id
);;

CREATE INDEX session_time57
ON phpbb_sessions(
  session_time
);;

CREATE INDEX session_user_id58
ON phpbb_sessions(
  session_user_id
);;

ALTER TABLE phpbb_sitelist
ADD PRIMARY KEY (
  site_id
);;

ALTER TABLE phpbb_smilies
ADD PRIMARY KEY (
  smiley_id
);;

ALTER TABLE phpbb_styles
ADD PRIMARY KEY (
  style_id
);;

CREATE UNIQUE INDEX style_name59
ON phpbb_styles(
  style_name
);;

CREATE INDEX imageset_id60
ON phpbb_styles(
  imageset_id
);;

CREATE INDEX template_id61
ON phpbb_styles(
  template_id
);;

CREATE INDEX theme_id62
ON phpbb_styles(
  theme_id
);;

CREATE UNIQUE INDEX imageset_name63
ON phpbb_styles_imageset(
  imageset_name
);;

ALTER TABLE phpbb_styles_imageset
ADD PRIMARY KEY (
  imageset_id
);;

ALTER TABLE phpbb_styles_template
ADD PRIMARY KEY (
  template_id
);;

CREATE UNIQUE INDEX template_name64
ON phpbb_styles_template(
  template_name
);;

CREATE INDEX template_filename65
ON phpbb_styles_template_data(
  template_filename
);;

CREATE INDEX template_id66
ON phpbb_styles_template_data(
  template_id
);;

ALTER TABLE phpbb_styles_theme
ADD PRIMARY KEY (
  theme_id
);;

CREATE UNIQUE INDEX theme_name67
ON phpbb_styles_theme(
  theme_name
);;

ALTER TABLE phpbb_topics
ADD PRIMARY KEY (
  topic_id
);;

CREATE INDEX forum_id68
ON phpbb_topics(
  forum_id
);;

CREATE INDEX forum_id_type69
ON phpbb_topics(
  forum_id,
  topic_type
);;

CREATE INDEX topic_last_post_time70
ON phpbb_topics(
  topic_last_post_time
);;

ALTER TABLE phpbb_topics_marking
ADD PRIMARY KEY (
  user_id,
  topic_id
);;

CREATE INDEX notify_status71
ON phpbb_topics_watch(
  notify_status
);;

CREATE INDEX topic_id72
ON phpbb_topics_watch(
  topic_id
);;

CREATE INDEX user_id73
ON phpbb_topics_watch(
  user_id
);;

CREATE INDEX group_id74
ON phpbb_user_group(
  group_id
);;

CREATE INDEX group_leader75
ON phpbb_user_group(
  group_leader
);;

CREATE INDEX user_id76
ON phpbb_user_group(
  user_id
);;

CREATE INDEX user_birthday77
ON phpbb_users(
  user_birthday
);;

CREATE INDEX user_email_hash78
ON phpbb_users(
  user_email_hash
);;

CREATE INDEX username79
ON phpbb_users(
  username
);;

ALTER TABLE phpbb_words
ADD PRIMARY KEY (
  word_id
);;

CREATE INDEX user_id80
ON phpbb_zebra(
  user_id
);;

CREATE INDEX zebra_id81
ON phpbb_zebra(
  zebra_id
);;

CREATE GENERATOR G_phpbb_attachmentsattach_idGen;;

SET GENERATOR G_phpbb_attachmentsattach_idGen TO 0;;

CREATE GENERATOR b_auth_optionsauth_option_idGen;;

SET GENERATOR b_auth_optionsauth_option_idGen TO 0;;

CREATE GENERATOR G_auth_presetspreset_idGen;;

SET GENERATOR G_auth_presetspreset_idGen TO 0;;

CREATE GENERATOR G_phpbb_banlistban_idGen3;;

SET GENERATOR G_phpbb_banlistban_idGen3 TO 0;;

CREATE GENERATOR G_phpbb_botsbot_idGen4;;

SET GENERATOR G_phpbb_botsbot_idGen4 TO 0;;

CREATE GENERATOR G_phpbb_disallowdisallow_idGen5;;

SET GENERATOR G_phpbb_disallowdisallow_idGen5 TO 0;;

CREATE GENERATOR G_phpbb_draftsdraft_idGen6;;

SET GENERATOR G_phpbb_draftsdraft_idGen6 TO 0;;

CREATE GENERATOR pbb_extension_groupsgroup_idGen;;

SET GENERATOR pbb_extension_groupsgroup_idGen TO 0;;

CREATE GENERATOR phpbb_extensionsextension_idGen;;

SET GENERATOR phpbb_extensionsextension_idGen TO 0;;

CREATE GENERATOR G_phpbb_forumsforum_idGen9;;

SET GENERATOR G_phpbb_forumsforum_idGen9 TO 0;;

CREATE GENERATOR G_phpbb_groupsgroup_idGen10;;

SET GENERATOR G_phpbb_groupsgroup_idGen10 TO 0;;

CREATE GENERATOR G_phpbb_iconsicons_idGen11;;

SET GENERATOR G_phpbb_iconsicons_idGen11 TO 0;;

CREATE GENERATOR G_phpbb_langlang_idGen12;;

SET GENERATOR G_phpbb_langlang_idGen12 TO 0;;

CREATE GENERATOR G_phpbb_loglog_idGen13;;

SET GENERATOR G_phpbb_loglog_idGen13 TO 0;;

CREATE GENERATOR G_phpbb_modulesmodule_idGen14;;

SET GENERATOR G_phpbb_modulesmodule_idGen14 TO 0;;

CREATE GENERATOR G_phpbb_postspost_idGen15;;

SET GENERATOR G_phpbb_postspost_idGen15 TO 0;;

CREATE GENERATOR G_phpbb_privmsgsmsg_idGen16;;

SET GENERATOR G_phpbb_privmsgsmsg_idGen16 TO 0;;

CREATE GENERATOR bb_privmsgs_folderfolder_idGen1;;

SET GENERATOR bb_privmsgs_folderfolder_idGen1 TO 0;;

CREATE GENERATOR phpbb_privmsgs_rulesrule_idGen1;;

SET GENERATOR phpbb_privmsgs_rulesrule_idGen1 TO 0;;

CREATE GENERATOR hpbb_profile_fieldsfield_idGen1;;

SET GENERATOR hpbb_profile_fieldsfield_idGen1 TO 0;;

CREATE GENERATOR G_phpbb_ranksrank_idGen20;;

SET GENERATOR G_phpbb_ranksrank_idGen20 TO 0;;

CREATE GENERATOR G_phpbb_reportsreport_idGen21;;

SET GENERATOR G_phpbb_reportsreport_idGen21 TO 0;;

CREATE GENERATOR bb_reports_reasonsreason_idGen2;;

SET GENERATOR bb_reports_reasonsreason_idGen2 TO 0;;

CREATE GENERATOR hpbb_search_wordlistword_idGen2;;

SET GENERATOR hpbb_search_wordlistword_idGen2 TO 0;;

CREATE GENERATOR G_phpbb_sitelistsite_idGen24;;

SET GENERATOR G_phpbb_sitelistsite_idGen24 TO 0;;

CREATE GENERATOR G_phpbb_smiliessmiley_idGen25;;

SET GENERATOR G_phpbb_smiliessmiley_idGen25 TO 0;;

CREATE GENERATOR G_phpbb_stylesstyle_idGen26;;

SET GENERATOR G_phpbb_stylesstyle_idGen26 TO 0;;

CREATE GENERATOR G_styles_imagesetimageset_idGen;;

SET GENERATOR G_styles_imagesetimageset_idGen TO 0;;

CREATE GENERATOR G_styles_templatetemplate_idGen;;

SET GENERATOR G_styles_templatetemplate_idGen TO 0;;

CREATE GENERATOR G_phpbb_styles_themetheme_idGen;;

SET GENERATOR G_phpbb_styles_themetheme_idGen TO 0;;

CREATE GENERATOR G_phpbb_topicstopic_idGen30;;

SET GENERATOR G_phpbb_topicstopic_idGen30 TO 0;;

CREATE GENERATOR G_phpbb_usersuser_idGen31;;

SET GENERATOR G_phpbb_usersuser_idGen31 TO 0;;

CREATE GENERATOR G_phpbb_wordsword_idGen32;;

SET GENERATOR G_phpbb_wordsword_idGen32 TO 0;;

CREATE TRIGGER tG_phpbb_attachmentsattach_idGe FOR phpbb_attachments
BEFORE INSERT
AS
BEGIN
  NEW.attach_id = GEN_ID(G_phpbb_attachmentsattach_idGen, 1);
END;;

CREATE TRIGGER tb_auth_optionsauth_option_idGe FOR phpbb_auth_options
BEFORE INSERT
AS
BEGIN
  NEW.auth_option_id = GEN_ID(b_auth_optionsauth_option_idGen, 1);
END;;

CREATE TRIGGER t_phpbb_auth_presetspreset_idGe FOR phpbb_auth_presets
BEFORE INSERT
AS
BEGIN
  NEW.preset_id = GEN_ID(G_auth_presetspreset_idGen, 1);
END;;

CREATE TRIGGER GetNextG_phpbb_banlistban_idGen FOR phpbb_banlist
BEFORE INSERT
AS
BEGIN
  NEW.ban_id = GEN_ID(G_phpbb_banlistban_idGen3, 1);
END;;

CREATE TRIGGER GetNextG_phpbb_botsbot_idGen4 FOR phpbb_bots
BEFORE INSERT
AS
BEGIN
  NEW.bot_id = GEN_ID(G_phpbb_botsbot_idGen4, 1);
END;;

CREATE TRIGGER tG_phpbb_disallowdisallow_idGen FOR phpbb_disallow
BEFORE INSERT
AS
BEGIN
  NEW.disallow_id = GEN_ID(G_phpbb_disallowdisallow_idGen5, 1);
END;;

CREATE TRIGGER etNextG_phpbb_draftsdraft_idGen FOR phpbb_drafts
BEFORE INSERT
AS
BEGIN
  NEW.draft_id = GEN_ID(G_phpbb_draftsdraft_idGen6, 1);
END;;

CREATE TRIGGER tpbb_extension_groupsgroup_idGe FOR phpbb_extension_groups
BEFORE INSERT
AS
BEGIN
  NEW.group_id = GEN_ID(pbb_extension_groupsgroup_idGen, 1);
END;;

CREATE TRIGGER tphpbb_extensionsextension_idGe FOR phpbb_extensions
BEFORE INSERT
AS
BEGIN
  NEW.extension_id = GEN_ID(phpbb_extensionsextension_idGen, 1);
END;;

CREATE TRIGGER etNextG_phpbb_forumsforum_idGen FOR phpbb_forums
BEFORE INSERT
AS
BEGIN
  NEW.forum_id = GEN_ID(G_phpbb_forumsforum_idGen9, 1);
END;;

CREATE TRIGGER tNextG_phpbb_groupsgroup_idGen1 FOR phpbb_groups
BEFORE INSERT
AS
BEGIN
  NEW.group_id = GEN_ID(G_phpbb_groupsgroup_idGen10, 1);
END;;

CREATE TRIGGER etNextG_phpbb_iconsicons_idGen1 FOR phpbb_icons
BEFORE INSERT
AS
BEGIN
  NEW.icons_id = GEN_ID(G_phpbb_iconsicons_idGen11, 1);
END;;

CREATE TRIGGER GetNextG_phpbb_langlang_idGen12 FOR phpbb_lang
BEFORE INSERT
AS
BEGIN
  NEW.lang_id = GEN_ID(G_phpbb_langlang_idGen12, 1);
END;;

CREATE TRIGGER GetNextG_phpbb_loglog_idGen13 FOR phpbb_log
BEFORE INSERT
AS
BEGIN
  NEW.log_id = GEN_ID(G_phpbb_loglog_idGen13, 1);
END;;

CREATE TRIGGER extG_phpbb_modulesmodule_idGen1 FOR phpbb_modules
BEFORE INSERT
AS
BEGIN
  NEW.module_id = GEN_ID(G_phpbb_modulesmodule_idGen14, 1);
END;;

CREATE TRIGGER GetNextG_phpbb_postspost_idGen1 FOR phpbb_posts
BEFORE INSERT
AS
BEGIN
  NEW.post_id = GEN_ID(G_phpbb_postspost_idGen15, 1);
END;;

CREATE TRIGGER tNextG_phpbb_privmsgsmsg_idGen1 FOR phpbb_privmsgs
BEFORE INSERT
AS
BEGIN
  NEW.msg_id = GEN_ID(G_phpbb_privmsgsmsg_idGen16, 1);
END;;

CREATE TRIGGER tbb_privmsgs_folderfolder_idGen FOR phpbb_privmsgs_folder
BEFORE INSERT
AS
BEGIN
  NEW.folder_id = GEN_ID(bb_privmsgs_folderfolder_idGen1, 1);
END;;

CREATE TRIGGER tphpbb_privmsgs_rulesrule_idGen FOR phpbb_privmsgs_rules
BEFORE INSERT
AS
BEGIN
  NEW.rule_id = GEN_ID(phpbb_privmsgs_rulesrule_idGen1, 1);
END;;

CREATE TRIGGER thpbb_profile_fieldsfield_idGen FOR phpbb_profile_fields
BEFORE INSERT
AS
BEGIN
  NEW.field_id = GEN_ID(hpbb_profile_fieldsfield_idGen1, 1);
END;;

CREATE TRIGGER GetNextG_phpbb_ranksrank_idGen2 FOR phpbb_ranks
BEFORE INSERT
AS
BEGIN
  NEW.rank_id = GEN_ID(G_phpbb_ranksrank_idGen20, 1);
END;;

CREATE TRIGGER extG_phpbb_reportsreport_idGen2 FOR phpbb_reports
BEFORE INSERT
AS
BEGIN
  NEW.report_id = GEN_ID(G_phpbb_reportsreport_idGen21, 1);
END;;

CREATE TRIGGER tbb_reports_reasonsreason_idGen FOR phpbb_reports_reasons
BEFORE INSERT
AS
BEGIN
  NEW.reason_id = GEN_ID(bb_reports_reasonsreason_idGen2, 1);
END;;

CREATE TRIGGER thpbb_search_wordlistword_idGen FOR phpbb_search_wordlist
BEFORE INSERT
AS
BEGIN
  NEW.word_id = GEN_ID(hpbb_search_wordlistword_idGen2, 1);
END;;

CREATE TRIGGER NextG_phpbb_sitelistsite_idGen2 FOR phpbb_sitelist
BEFORE INSERT
AS
BEGIN
  NEW.site_id = GEN_ID(G_phpbb_sitelistsite_idGen24, 1);
END;;

CREATE TRIGGER NextG_phpbb_smiliessmiley_idGen2 FOR phpbb_smilies
BEFORE INSERT
AS
BEGIN
  NEW.smiley_id = GEN_ID(G_phpbb_smiliessmiley_idGen25, 1);
END;;

CREATE TRIGGER tNextG_phpbb_stylesstyle_idGen2 FOR phpbb_styles
BEFORE INSERT
AS
BEGIN
  NEW.style_id = GEN_ID(G_phpbb_stylesstyle_idGen26, 1);
END;;

CREATE TRIGGER t_styles_imagesetimageset_idGen FOR phpbb_styles_imageset
BEFORE INSERT
AS
BEGIN
  NEW.imageset_id = GEN_ID(G_styles_imagesetimageset_idGen, 1);
END;;

CREATE TRIGGER t_styles_templatetemplate_idGen FOR phpbb_styles_template
BEFORE INSERT
AS
BEGIN
  NEW.template_id = GEN_ID(G_styles_templatetemplate_idGen, 1);
END;;

CREATE TRIGGER t_phpbb_styles_themetheme_idGen FOR phpbb_styles_theme
BEFORE INSERT
AS
BEGIN
  NEW.theme_id = GEN_ID(G_phpbb_styles_themetheme_idGen, 1);
END;;

CREATE TRIGGER tNextG_phpbb_topicstopic_idGen3 FOR phpbb_topics
BEFORE INSERT
AS
BEGIN
  NEW.topic_id = GEN_ID(G_phpbb_topicstopic_idGen30, 1);
END;;

CREATE TRIGGER GetNextG_phpbb_usersuser_idGen3 FOR phpbb_users
BEFORE INSERT
AS
BEGIN
  NEW.user_id = GEN_ID(G_phpbb_usersuser_idGen31, 1);
END;;

CREATE TRIGGER GetNextG_phpbb_wordsword_idGen3 FOR phpbb_words
BEFORE INSERT
AS
BEGIN
  NEW.word_id = GEN_ID(G_phpbb_wordsword_idGen32, 1);
END;;
