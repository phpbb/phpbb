--
-- phpbb - Firebird schema
--
-- $Id$
--

-- Table: phpbb_attachments
CREATE TABLE phpbb_attachments (
  attach_id INTEGER DEFAULT 0 NOT NULL,
  post_id INTEGER DEFAULT 0 NOT NULL,
  privmsgs_id INTEGER DEFAULT 0 NOT NULL,
  user_id_from INTEGER NOT NULL,
  user_id_to INTEGER NOT NULL
); 

-- Table: phpbb_attach_desc
CREATE TABLE phpbb_attach_desc (
  attach_id INTEGER NOT NULL,
  physical_filename VARCHAR(255) NOT NULL,
  real_filename VARCHAR(255) NOT NULL,
  download_count INTEGER DEFAULT 0 NOT NULL,
  comment VARCHAR(255) DEFAULT '',
  extension VARCHAR(100),
  mimetype VARCHAR(100),
  filesize INTEGER NOT NULL,
  filetime INTEGER DEFAULT 0 NOT NULL,
  thumbnail INTEGER DEFAULT 0 NOT NULL,
  PRIMARY KEY (attach_id)
);

-- Table: phpbb_auth_groups
CREATE TABLE phpbb_auth_groups (
  group_id INTEGER DEFAULT 0 NOT NULL,
  forum_id INTEGER DEFAULT 0 NOT NULL,
  auth_option_id INTEGER DEFAULT 0 NOT NULL,
  auth_setting INTEGER DEFAULT 0 NOT NULL
);

-- Table: phpbb_auth_options
CREATE TABLE phpbb_auth_options (
  auth_option_id INTEGER NOT NULL,
  auth_option CHAR(20) NOT NULL,
  is_global INTEGER DEFAULT 0 NOT NULL,
  is_local INTEGER DEFAULT 0 NOT NULL,
  founder_only INTEGER DEFAULT 0 NOT NULL, 
  PRIMARY KEY (auth_option_id) 
);

-- Table: phpbb_auth_presets
CREATE TABLE phpbb_auth_presets (
  preset_id INTEGER NOT NULL, 
  preset_name VARCHAR(50) NOT NULL, 
  preset_user_id INTEGER NOT NULL, 
  preset_type VARCHAR(2) NOT NULL, 
  preset_data TEXT,
  PRIMARY KEY (preset_id) 
);

-- Table: phpbb_auth_users
CREATE TABLE phpbb_auth_users (
  user_id INTEGER DEFAULT 0 NOT NULL,
  forum_id INTEGER DEFAULT 0 NOT NULL,
  auth_option_id INTEGER DEFAULT 0 NOT NULL,
  auth_setting INTEGER DEFAULT 0 NOT NULL
);

-- Table: 'phpbb_banlist'
CREATE TABLE phpbb_banlist (
   ban_id INTEGER NOT NULL,
   ban_userid INTEGER DEFAULT 0 NOT NULL,
   ban_ip VARCHAR(40) DEFAULT '' NOT NULL,
   ban_email VARCHAR(50) DEFAULT '' NOT NULL,
   ban_start INTEGER DEFAULT 0 NOT NULL,
   ban_end INTEGER DEFAULT 0 NOT NULL,
   ban_exclude INTEGER DEFAULT 0 NOT NULL, 
   ban_reason VARCHAR(255),
   ban_give_reason VARCHAR(255) DEFAULT '' NOT NULL, 
   PRIMARY KEY (ban_id) 
);

-- Table: 'phpbb_cache'
CREATE TABLE phpbb_cache (
    var_name VARCHAR(255) NOT NULL,
    var_ts INTEGER DEFAULT 0 NOT NULL, 
    var_data TEXT DEFAULT '' NOT NULL,
	PRIMARY KEY (var_name) 
);

-- Table: 'phpbb_config'
CREATE TABLE phpbb_config (
    config_name VARCHAR(50) NOT NULL,
    config_value VARCHAR(255) NOT NULL,
    is_dynamic INTEGER DEFAULT 0 NOT NULL, 
	PRIMARY KEY (config_name) 
);

-- Table: 'phpbb_confirm'
CREATE TABLE phpbb_confirm (
  confirm_id CHAR(32) DEFAULT '' NOT NULL,
  session_id CHAR(32) DEFAULT '' NOT NULL,
  code CHAR(6) DEFAULT '' NOT NULL, 
  PRIMARY KEY (session_id, confirm_id) 
);

-- Table: 'phpbb_disallow'
CREATE TABLE phpbb_disallow (
   disallow_id INTEGER NOT NULL,
   disallow_username VARCHAR(30), 
   PRIMARY KEY (disallow_id) 
);

-- Table: 'phpbb_extensions'
CREATE TABLE phpbb_extensions (
  extension_id INTEGER NOT NULL,
  group_id INTEGER DEFAULT 0 NOT NULL,
  extension VARCHAR(100) DEFAULT '' NOT NULL,
  comment VARCHAR(100) DEFAULT '' NOT NULL,
  PRIMARY KEY (extension_id)
);

-- Table: 'phpbb_extension_groups'
CREATE TABLE phpbb_extension_groups (
  group_id INTEGER NOT NULL,
  group_name VARCHAR(20) DEFAULT '' NOT NULL,
  cat_id INTEGER DEFAULT 0 NOT NULL, 
  allow_group INTEGER DEFAULT 0 NOT NULL,
  download_mode INTEGER DEFAULT 1 NOT NULL,
  upload_icon VARCHAR(100) DEFAULT '' NOT NULL,
  max_filesize INTEGER DEFAULT 0 NOT NULL,
  PRIMARY KEY (group_id)
);

-- Table: 'phpbb_forbidden_extensions'
CREATE TABLE phpbb_forbidden_extensions (
  extension_id INTEGER NOT NULL, 
  extension VARCHAR(100) NOT NULL, 
  PRIMARY KEY (extension_id)
);

-- Table: 'phpbb_forums'
CREATE TABLE phpbb_forums (
   forum_id INTEGER NOT NULL,
   parent_id INTEGER NOT NULL,
   left_id INTEGER NOT NULL,
   right_id INTEGER NOT NULL,
   forum_parents TEXT,
   forum_name VARCHAR(150) NOT NULL,
   forum_desc TEXT,
   forum_link VARCHAR(200) DEFAULT '' NOT NULL,
   forum_password VARCHAR(32) DEFAULT '' NOT NULL, 
   forum_style INTEGER DEFAULT 0 NOT NULL,
   forum_image VARCHAR(50) DEFAULT '' NOT NULL,
   forum_topics_per_page INTEGER DEFAULT 0 NOT NULL,
   forum_type INTEGER DEFAULT 0 NOT NULL,
   forum_status INTEGER DEFAULT 0 NOT NULL,
   forum_posts INTEGER DEFAULT 0 NOT NULL,
   forum_topics INTEGER DEFAULT 0 NOT NULL,
   forum_topics_real INTEGER DEFAULT 0 NOT NULL,
   forum_last_post_id INTEGER DEFAULT 0 NOT NULL,
   forum_last_poster_id INTEGER DEFAULT 0 NOT NULL,
   forum_last_post_time INTEGER DEFAULT 0 NOT NULL,
   forum_last_poster_name VARCHAR(30) DEFAULT '' NOT NULL,
   forum_flags INTEGER DEFAULT 0 NOT NULL,
   display_on_index INTEGER DEFAULT 1 NOT NULL,
   enable_icons INTEGER DEFAULT 1 NOT NULL, 
   enable_prune INTEGER DEFAULT 0 NOT NULL, 
   prune_next INTEGER DEFAULT 0 NOT NULL,
   prune_days INTEGER DEFAULT 0 NOT NULL,
   prune_freq INTEGER DEFAULT 0 NOT NULL, 
   PRIMARY KEY (forum_id)
);

-- Table: phpbb_forum_access
CREATE TABLE phpbb_forum_access (
  forum_id INTEGER DEFAULT 0 NOT NULL,
  user_id INTEGER DEFAULT 0 NOT NULL,
  session_id CHAR(32) DEFAULT '' NOT NULL,
  PRIMARY KEY  (forum_id,user_id,session_id)
);

-- Table: 'phpbb_forums_marking'
CREATE TABLE phpbb_forums_marking (
   user_id INTEGER DEFAULT 0 NOT NULL,
   forum_id INTEGER DEFAULT 0 NOT NULL,
   mark_time INTEGER DEFAULT 0 NOT NULL,
   PRIMARY KEY (user_id, forum_id)
);

-- Table: 'phpbb_forums_watch'
CREATE TABLE phpbb_forums_watch (
  forum_id INTEGER DEFAULT 0 NOT NULL,
  user_id INTEGER DEFAULT 0 NOT NULL,
  notify_status INTEGER DEFAULT 0 NOT NULL 
);

-- Table: 'phpbb_groups'
CREATE TABLE phpbb_groups (
   group_id INTEGER NOT NULL,
   group_type INTEGER DEFAULT 1 NOT NULL,
   group_name VARCHAR(40) DEFAULT '' NOT NULL,
   group_display INTEGER DEFAULT 0 NOT NULL, 
   group_avatar VARCHAR(100) DEFAULT '' NOT NULL,
   group_avatar_type INTEGER DEFAULT 0 NOT NULL,
   group_rank INTEGER DEFAULT 0 NOT NULL,
   group_colour VARCHAR(6) DEFAULT '' NOT NULL,
   group_description VARCHAR(255) DEFAULT '' NOT NULL, 
   PRIMARY KEY (group_id) 
);

-- Table: 'phpbb_groups_moderator'
CREATE TABLE phpbb_groups_moderator (
   group_id INTEGER NOT NULL,
   user_id INTEGER NOT NULL
);

-- Table: 'phpbb_icons'
CREATE TABLE phpbb_icons (
   icons_id INTEGER NOT NULL,
   icons_url VARCHAR(50),
   icons_width INTEGER NOT NULL,
   icons_height INTEGER NOT NULL,
   icons_order INTEGER NOT NULL,
   display_on_posting INTEGER DEFAULT 1 NOT NULL, 
   PRIMARY KEY (icons_id) 
);

-- Table: 'phpbb_lang'
CREATE TABLE phpbb_lang (
   lang_id INTEGER NOT NULL,
   lang_iso VARCHAR(5) NOT NULL, 
   lang_dir VARCHAR(30) NOT NULL, 
   lang_english_name VARCHAR(30), 
   lang_local_name VARCHAR(100), 
   lang_author VARCHAR(100), 
   PRIMARY KEY (lang_id)
);

-- Table: 'phpbb_log_moderator'
CREATE TABLE phpbb_log_moderator (
  log_id INTEGER DEFAULT 0 NOT NULL,
  user_id INTEGER DEFAULT 0 NOT NULL,
  forum_id INTEGER DEFAULT 0 NOT NULL,
  topic_id INTEGER DEFAULT 0 NOT NULL,
  log_ip VARCHAR(40) NOT NULL,
  log_time INTEGER NOT NULL,
  log_operation TEXT,
  log_data TEXT,
  PRIMARY KEY (log_id) 
);

-- Table: 'phpbb_log_admin'
CREATE TABLE phpbb_log_admin (
  log_id INTEGER NOT NULL,
  user_id INTEGER DEFAULT 0 NOT NULL,
  log_ip VARCHAR(40) NOT NULL,
  log_time INTEGER NOT NULL,
  log_operation TEXT,
  log_data TEXT,
  PRIMARY KEY (log_id)
);

-- Table: 'phpbb_moderator_cache'
CREATE TABLE phpbb_moderator_cache (
  forum_id INTEGER NOT NULL,
  user_id INTEGER,
  username CHAR(30),
  group_id INTEGER,
  groupname CHAR(30),
  display_on_index INTEGER DEFAULT 1 NOT NULL 
);

-- Table: 'phpbb_vote_results'
CREATE TABLE phpbb_poll_results (
  poll_option_id INTEGER DEFAULT 0 NOT NULL,
  topic_id INTEGER DEFAULT 0 NOT NULL,
  poll_option_text VARCHAR(255) DEFAULT '' NOT NULL,
  poll_option_total INTEGER DEFAULT 0 NOT NULL 
);

-- Table: 'phpbb_vote_voters'
CREATE TABLE phpbb_poll_voters (
  topic_id INTEGER DEFAULT 0 NOT NULL,
  poll_option_id INTEGER DEFAULT 0 NOT NULL,
  vote_user_id INTEGER DEFAULT 0 NOT NULL,
  vote_user_ip VARCHAR(40) DEFAULT '' NOT NULL 
);

-- Table: 'phpbb_posts'
CREATE TABLE phpbb_posts (
   post_id INTEGER NOT NULL,
   topic_id INTEGER DEFAULT 0 NOT NULL,
   forum_id INTEGER DEFAULT 0 NOT NULL,
   poster_id INTEGER DEFAULT 0 NOT NULL,
   icon_id INTEGER DEFAULT 1 NOT NULL,
   poster_ip VARCHAR(40) DEFAULT '' NOT NULL,
   post_time INTEGER DEFAULT 0 NOT NULL,
   post_approved INTEGER DEFAULT 1 NOT NULL,
   post_reported INTEGER DEFAULT 0 NOT NULL,
   enable_bbcode INTEGER DEFAULT 1 NOT NULL,
   enable_html INTEGER DEFAULT 0 NOT NULL,
   enable_smilies INTEGER DEFAULT 1 NOT NULL,
   enable_magic_url INTEGER DEFAULT 1 NOT NULL,
   enable_sig INTEGER DEFAULT 1 NOT NULL,
   post_username VARCHAR(30) DEFAULT '',
   post_subject VARCHAR(60) DEFAULT '',
   post_text TEXT DEFAULT '' NOT NULL,
   post_checksum VARCHAR(32) DEFAULT '' NOT NULL,
   post_encoding VARCHAR(11) DEFAULT 'iso-8859-15' NOT NULL, 
   post_attachment INTEGER DEFAULT 0 NOT NULL,
   bbcode_bitfield INTEGER DEFAULT 0 NOT NULL,
   bbcode_uid VARCHAR(10) DEFAULT '' NOT NULL,
   post_edit_time INTEGER DEFAULT 0 NOT NULL,
   post_edit_count INTEGER DEFAULT 0 NOT NULL,
   post_edit_locked INTEGER DEFAULT 0 NOT NULL,
   PRIMARY KEY (post_id) 
);

-- Table: 'phpbb_privmsgs'
CREATE TABLE phpbb_privmsgs (
   privmsgs_id INTEGER NOT NULL,
   privmsgs_attachment INTEGER DEFAULT 0 NOT NULL,
   privmsgs_type INTEGER DEFAULT 0 NOT NULL,
   privmsgs_subject VARCHAR(60) DEFAULT 0 NOT NULL,
   privmsgs_from_userid INTEGER DEFAULT 0 NOT NULL,
   privmsgs_to_userid INTEGER DEFAULT 0 NOT NULL,
   privmsgs_date INTEGER DEFAULT 0 NOT NULL,
   privmsgs_ip VARCHAR(40) NOT NULL,
   privmsgs_enable_bbcode INTEGER DEFAULT 1 NOT NULL,
   privmsgs_enable_html INTEGER DEFAULT 0 NOT NULL,
   privmsgs_enable_smilies INTEGER DEFAULT 1 NOT NULL,
   privmsgs_attach_sig INTEGER DEFAULT 1 NOT NULL,
   privmsgs_text TEXT,
   privmsgs_bbcode_uid VARCHAR(10) DEFAULT 0 NOT NULL, 
   PRIMARY KEY (privmsgs_id) 
);

-- Table: 'phpbb_ranks'
CREATE TABLE phpbb_ranks (
   rank_id INTEGER NOT NULL,
   rank_title VARCHAR(50) NOT NULL,
   rank_min INTEGER DEFAULT 0 NOT NULL,
   rank_special INTEGER DEFAULT 0,
   rank_image VARCHAR(100),
   PRIMARY KEY (rank_id) 
);

-- Table: 'phpbb_ratings'
CREATE TABLE phpbb_ratings (
  post_id INTEGER DEFAULT 0 NOT NULL,
  user_id INTEGER DEFAULT 0 NOT NULL,
  rating INTEGER DEFAULT 0 NOT NULL
);

-- Table: 'phpbb_reports_reasons'
CREATE TABLE phpbb_reports_reasons (
  reason_id INTEGER NOT NULL,
  reason_priority INTEGER DEFAULT 0 NOT NULL,
  reason_name VARCHAR(255) DEFAULT '' NOT NULL,
  reason_description TEXT NOT NULL, 
  PRIMARY KEY (reason_id)
);

-- Table: 'phpbb_reports'
CREATE TABLE phpbb_reports (
  report_id INTEGER NOT NULL,
  reason_id INTEGER DEFAULT 0 NOT NULL,
  post_id INTEGER DEFAULT 0 NOT NULL,
  user_id INTEGER DEFAULT 0 NOT NULL,
  user_notify INTEGER DEFAULT 0 NOT NULL,
  report_time INTEGER DEFAULT 0 NOT NULL,
  report_text TEXT NOT NULL, 
  PRIMARY KEY (report_id)
);

-- Table: phpbb_search_results
CREATE TABLE phpbb_search_results (
  search_id INTEGER DEFAULT 0 NOT NULL,
  session_id VARCHAR(32) DEFAULT '' NOT NULL,
  search_array TEXT NOT NULL, 
  PRIMARY KEY (search_id)
);

-- Table: phpbb_search_wordlist
CREATE TABLE phpbb_search_wordlist (
  word_id INTEGER NOT NULL,
  word_text VARCHAR(50) DEFAULT '' NOT NULL,
  word_common INTEGER DEFAULT 0 NOT NULL, 
  PRIMARY KEY (word_id)
);

-- Table: phpbb_search_wordmatch
CREATE TABLE phpbb_search_wordmatch (
  post_id INTEGER DEFAULT 0 NOT NULL,
  word_id INTEGER DEFAULT 0 NOT NULL,
  title_match INTEGER DEFAULT 0 NOT NULL 
);

-- Table: 'phpbb_sessions'
CREATE TABLE phpbb_sessions (
   session_id VARCHAR(32) DEFAULT '' NOT NULL,
   session_user_id INTEGER DEFAULT 0 NOT NULL,
   session_last_visit INTEGER DEFAULT 0 NOT NULL,
   session_start INTEGER DEFAULT 0 NOT NULL,
   session_time INTEGER DEFAULT 0 NOT NULL,
   session_ip VARCHAR(40) DEFAULT 0 NOT NULL,
   session_browser VARCHAR(100) DEFAULT '' NOT NULL,
   session_page VARCHAR(100) DEFAULT 0 NOT NULL,
   session_allow_viewonline INTEGER DEFAULT 1 NOT NULL, 
   PRIMARY KEY (session_id)
);

-- Table: 'phpbb_smilies'
CREATE TABLE phpbb_smilies (
   smile_id INTEGER NOT NULL,
   code CHAR(10) DEFAULT '' NOT NULL,
   emoticon CHAR(50) DEFAULT '' NOT NULL,
   smile_url CHAR(50) DEFAULT '' NOT NULL,
   smile_width INTEGER DEFAULT 0 NOT NULL,
   smile_height INTEGER DEFAULT 0 NOT NULL,
   smile_order INTEGER DEFAULT 1 NOT NULL,
   display_on_posting INTEGER DEFAULT 1 NOT NULL, 
   PRIMARY KEY (smile_id)
);

-- Table: 'phpbb_styles'
CREATE TABLE phpbb_styles (
   style_id INTEGER NOT NULL,
   template_id CHAR(50) DEFAULT '' NOT NULL,
   theme_id INTEGER DEFAULT 0 NOT NULL,
   imageset_id INTEGER DEFAULT 0 NOT NULL,
   style_name CHAR(30) DEFAULT '' NOT NULL, 
   PRIMARY KEY (style_id) 
);

-- Table: 'phpbb_styles_template'
CREATE TABLE phpbb_styles_template (
   template_id INTEGER NOT NULL,
   template_name CHAR(30) DEFAULT '' NOT NULL,
   template_path CHAR(50) DEFAULT '' NOT NULL,
   poll_length INTEGER DEFAULT 0 NOT NULL,
   pm_box_length INTEGER DEFAULT 0 NOT NULL, 
   bbcode_bitfield INT DEFAULT 0 NOT NULL,
   PRIMARY KEY (template_id)
);

-- Table: 'phpbb_styles_theme'
CREATE TABLE phpbb_styles_theme (
   theme_id INTEGER NOT NULL,
   theme_name CHAR(60) DEFAULT '' NOT NULL,
   css_external CHAR(100) DEFAULT '' NOT NULL,
   css_data TEXT, 
   PRIMARY KEY (theme_id)
);

-- Table: 'phpbb_styles_imageset'
CREATE TABLE phpbb_styles_imageset (
  imageset_id INTEGER NOT NULL,
  imageset_name CHAR(100),
  imageset_path CHAR(30),
  btn_post CHAR(200) DEFAULT '' NOT NULL,
  btn_post_pm CHAR(200) DEFAULT '' NOT NULL,
  btn_reply CHAR(200) DEFAULT '' NOT NULL,
  btn_reply_pm CHAR(200) DEFAULT '' NOT NULL,
  btn_locked CHAR(200) DEFAULT '' NOT NULL,
  btn_profile CHAR(200) DEFAULT '' NOT NULL,
  btn_pm CHAR(200) DEFAULT '' NOT NULL,
  btn_delete CHAR(200) DEFAULT '' NOT NULL,
  btn_ip CHAR(200) DEFAULT '' NOT NULL,
  btn_quote CHAR(200) DEFAULT '' NOT NULL,
  btn_search CHAR(200) DEFAULT '' NOT NULL,
  btn_edit CHAR(200) DEFAULT '' NOT NULL,
  btn_report CHAR(200) DEFAULT '' NOT NULL,
  btn_email CHAR(200) DEFAULT '' NOT NULL,
  btn_www CHAR(200) DEFAULT '' NOT NULL,
  btn_icq CHAR(200) DEFAULT '' NOT NULL,
  btn_aim CHAR(200) DEFAULT '' NOT NULL,
  btn_yim CHAR(200) DEFAULT '' NOT NULL,
  btn_msnm CHAR(200) DEFAULT '' NOT NULL,
  btn_jabber CHAR(200) DEFAULT '' NOT NULL,
  btn_online CHAR(200) DEFAULT '' NOT NULL,
  btn_offline CHAR(200) DEFAULT '' NOT NULL,
  btn_topic_watch CHAR(200) DEFAULT '' NOT NULL,
  btn_topic_unwatch CHAR(200) DEFAULT '' NOT NULL,
  icon_unapproved CHAR(200) DEFAULT '' NOT NULL,
  icon_reported CHAR(200) DEFAULT '' NOT NULL,
  icon_attach CHAR(200) DEFAULT '' NOT NULL,
  icon_post CHAR(200) DEFAULT '' NOT NULL,
  icon_post_new CHAR(200) DEFAULT '' NOT NULL,
  icon_post_latest CHAR(200) DEFAULT '' NOT NULL,
  icon_post_newest CHAR(200) DEFAULT '' NOT NULL,
  forum CHAR(200) DEFAULT '' NOT NULL,
  forum_new CHAR(200) DEFAULT '' NOT NULL,
  forum_locked CHAR(200) DEFAULT '' NOT NULL,
  forum_link CHAR(200) DEFAULT '' NOT NULL, 
  sub_forum CHAR(200) DEFAULT '' NOT NULL,
  sub_forum_new CHAR(200) DEFAULT '' NOT NULL,
  folder CHAR(200) DEFAULT '' NOT NULL,
  folder_posted CHAR(200) DEFAULT '' NOT NULL,
  folder_new CHAR(200) DEFAULT '' NOT NULL,
  folder_new_posted CHAR(200) DEFAULT '' NOT NULL,
  folder_hot CHAR(200) DEFAULT '' NOT NULL,
  folder_hot_posted CHAR(200) DEFAULT '' NOT NULL,
  folder_hot_new CHAR(200) DEFAULT '' NOT NULL,
  folder_hot_new_posted CHAR(200) DEFAULT '' NOT NULL,
  folder_locked CHAR(200) DEFAULT '' NOT NULL,
  folder_locked_posted CHAR(200) DEFAULT '' NOT NULL,
  folder_locked_new CHAR(200) DEFAULT '' NOT NULL,
  folder_locked_new_posted CHAR(200) DEFAULT '' NOT NULL,
  folder_sticky CHAR(200) DEFAULT '' NOT NULL,
  folder_sticky_posted CHAR(200) DEFAULT '' NOT NULL,
  folder_sticky_new CHAR(200) DEFAULT '' NOT NULL,
  folder_sticky_new_posted CHAR(200) DEFAULT '' NOT NULL,
  folder_announce CHAR(200) DEFAULT '' NOT NULL,
  folder_announce_posted  CHAR(200) DEFAULT '' NOT NULL,
  folder_announce_new CHAR(200) DEFAULT '' NOT NULL,
  folder_announce_new_posted CHAR(200) DEFAULT '' NOT NULL,
  poll_left CHAR(200) DEFAULT '' NOT NULL,
  poll_center CHAR(200) DEFAULT '' NOT NULL,
  poll_right CHAR(200) DEFAULT '' NOT NULL,
  PRIMARY KEY (imageset_id) 
);

-- Table: 'phpbb_topics'
CREATE TABLE phpbb_topics (
   topic_id INTEGER NOT NULL,
   forum_id INTEGER DEFAULT 0 NOT NULL,
   icon_id INTEGER DEFAULT 1 NOT NULL,
   topic_attachment INTEGER DEFAULT 0 NOT NULL,
   topic_approved INTEGER DEFAULT 1 NOT NULL,
   topic_reported INTEGER DEFAULT 0 NOT NULL,
   topic_title VARCHAR(60) NOT NULL,
   topic_poster INTEGER DEFAULT 0 NOT NULL,
   topic_time INTEGER DEFAULT 0 NOT NULL,
   topic_views INTEGER DEFAULT 0 NOT NULL,
   topic_replies INTEGER DEFAULT 0 NOT NULL,
   topic_replies_real INTEGER DEFAULT 0 NOT NULL,
   topic_status INTEGER DEFAULT 0 NOT NULL,
   topic_type INTEGER DEFAULT 0 NOT NULL,
   topic_first_post_id INTEGER DEFAULT 0 NOT NULL,
   topic_first_poster_name VARCHAR(30),
   topic_last_post_id INTEGER DEFAULT 0 NOT NULL,
   topic_last_poster_id INTEGER DEFAULT 0 NOT NULL,
   topic_last_poster_name VARCHAR(30),
   topic_last_post_time INTEGER DEFAULT 0 NOT NULL,
   topic_last_view_time INTEGER DEFAULT 0 NOT NULL,
   topic_moved_id INTEGER DEFAULT 0 NOT NULL,
   poll_title VARCHAR(255) DEFAULT '' NOT NULL,
   poll_start INTEGER DEFAULT 0 NOT NULL,
   poll_length INTEGER DEFAULT 0 NOT NULL,
   poll_max_options INTEGER DEFAULT 1 NOT NULL,
   poll_last_vote INTEGER, 
   PRIMARY KEY (topic_id) 
);

-- Table: 'phpbb_topic_marking'
CREATE TABLE phpbb_topics_marking (
   user_id INTEGER DEFAULT 0 NOT NULL,
   topic_id INTEGER DEFAULT 0 NOT NULL,
   mark_type INTEGER DEFAULT 0 NOT NULL,
   mark_time INTEGER DEFAULT 0 NOT NULL,
   PRIMARY KEY (user_id, topic_id)
);

-- Table: 'phpbb_topics_watch'
CREATE TABLE phpbb_topics_watch (
  topic_id INTEGER DEFAULT 0 NOT NULL,
  user_id INTEGER DEFAULT 0 NOT NULL,
  notify_status INTEGER DEFAULT 0 NOT NULL 
);

-- Table: 'phpbb_ucp_modules'
CREATE TABLE phpbb_ucp_modules (
	module_id INTEGER DEFAULT 0 NOT NULL,
	module_title VARCHAR(50) DEFAULT ''  NOT NULL,
	module_filename VARCHAR(50) DEFAULT '' NOT NULL,
	module_order INTEGER DEFAULT 0 NOT NULL, 
	PRIMARY KEY (module_id)
);

-- Table: 'phpbb_user_group'
CREATE TABLE phpbb_user_group (
   group_id INTEGER DEFAULT 0 NOT NULL,
   user_id INTEGER DEFAULT 0 NOT NULL,
   user_pending INTEGER 
);

-- Table: 'phpbb_users'
CREATE TABLE phpbb_users (
   user_id INTEGER NOT NULL,
   user_active INTEGER DEFAULT 1 NOT NULL,
   user_founder INTEGER DEFAULT 0 NOT NULL,
   group_id INTEGER DEFAULT 0 NOT NULL,
   user_permissions TEXT DEFAULT '',
   user_ip VARCHAR(40),
   user_regdate INTEGER DEFAULT 0 NOT NULL,
   username VARCHAR(30) NOT NULL,
   user_password VARCHAR(32) NOT NULL,
   user_email VARCHAR(60),
   user_birthday VARCHAR(10) DEFAULT '' NOT NULL,
   user_lastvisit INTEGER DEFAULT 0 NOT NULL,
   user_lastpage VARCHAR(100) DEFAULT '' NOT NULL,
   user_karma INTEGER DEFAULT '3' NOT NULL, 
   user_min_karma INTEGER DEFAULT '-5' NOT NULL, 
   user_startpage VARCHAR(100) DEFAULT '',
   user_colour VARCHAR(6) DEFAULT '' NOT NULL,
   user_posts INTEGER DEFAULT 0 NOT NULL,
   user_lang VARCHAR(30) DEFAULT '' NOT NULL,
   user_timezone decimal(5,2) DEFAULT 0 NOT NULL,
   user_dst INTEGER DEFAULT 0 NOT NULL,
   user_dateformat VARCHAR(15) DEFAULT 'd M Y H:i' NOT NULL,
   user_style INTEGER DEFAULT 1 NOT NULL,
   user_rank INTEGER DEFAULT 0 NOT NULL,
   user_new_privmsg INTEGER DEFAULT 0 NOT NULL,
   user_unread_privmsg INTEGER DEFAULT 0 NOT NULL,
   user_last_privmsg INTEGER DEFAULT 0 NOT NULL,
   user_emailtime INTEGER,
   user_sortby_type VARCHAR(1) DEFAULT '' NOT NULL,
   user_sortby_dir VARCHAR(1) DEFAULT '' NOT NULL,
   user_show_days INTEGER DEFAULT 0 NOT NULL,
   user_viewimg INTEGER DEFAULT 1 NOT NULL,
   user_notify INTEGER DEFAULT 0 NOT NULL,
   user_notify_pm INTEGER DEFAULT 1 NOT NULL,
   user_popup_pm INTEGER DEFAULT 0 NOT NULL,
   user_viewflash INTEGER DEFAULT 1 NOT NULL,
   user_viewsmilies INTEGER DEFAULT 1 NOT NULL,
   user_viewsigs INTEGER DEFAULT 1 NOT NULL,
   user_viewavatars INTEGER DEFAULT 1 NOT NULL,
   user_viewcensors INTEGER DEFAULT 1 NOT NULL,
   user_attachsig INTEGER DEFAULT 1 NOT NULL,
   user_allowhtml INTEGER DEFAULT 1 NOT NULL,
   user_allowbbcode INTEGER DEFAULT 1 NOT NULL,
   user_allowsmile INTEGER DEFAULT 1 NOT NULL,
   user_allowavatar INTEGER DEFAULT 1 NOT NULL,
   user_allow_pm INTEGER DEFAULT 1 NOT NULL,
   user_allow_email INTEGER DEFAULT 1 NOT NULL,
   user_allow_viewonline INTEGER DEFAULT 1 NOT NULL,
   user_allow_viewemail INTEGER DEFAULT 1 NOT NULL,
   user_allow_massemail INTEGER DEFAULT 1 NOT NULL,
   user_avatar VARCHAR(100) DEFAULT '' NOT NULL,
   user_avatar_type INTEGER DEFAULT 0 NOT NULL,
   user_avatar_width INTEGER DEFAULT 0 NOT NULL,
   user_avatar_height INTEGER DEFAULT 0 NOT NULL,
   user_sig TEXT,
   user_sig_bbcode_uid VARCHAR(5) DEFAULT '' NOT NULL,
   user_sig_bbcode_bitfield INTEGER DEFAULT 0 NOT NULL,
   user_from VARCHAR(100) DEFAULT '' NOT NULL,
   user_icq VARCHAR(15) DEFAULT '' NOT NULL,
   user_aim VARCHAR(255) DEFAULT '' NOT NULL,
   user_yim VARCHAR(255) DEFAULT '' NOT NULL,
   user_msnm VARCHAR(255) DEFAULT '' NOT NULL,
   user_jabber VARCHAR(255) DEFAULT '' NOT NULL,
   user_website VARCHAR(100) DEFAULT '' NOT NULL,
   user_actkey VARCHAR(32) DEFAULT '' NOT NULL,
   user_newpasswd VARCHAR(32) DEFAULT '' NOT NULL,
   user_occ VARCHAR(255) DEFAULT '' NOT NULL,
   user_interests VARCHAR(255) DEFAULT '' NOT NULL,
   PRIMARY KEY (user_id) 
);

-- Table: 'phpbb_words'
CREATE TABLE phpbb_words (
   word_id INTEGER NOT NULL,
   word CHAR(100) DEFAULT '' NOT NULL,
   replacement CHAR(100) DEFAULT '' NOT NULL, 
   PRIMARY KEY (word_id) 
);
