#
# phpbb - Firebird schema
#
# $Id$
#

# Table: phpbb_attachments
CREATE TABLE phpbb_attachments (
  attach_id INTEGER DEFAULT 0 NOT NULL,
  post_id INTEGER DEFAULT 0 NOT NULL,
  privmsgs_id INTEGER DEFAULT 0 NOT NULL,
  user_id_from INTEGER NOT NULL,
  user_id_to INTEGER NOT NULL
); 

CREATE INDEX phpbb_attachments_attach_id ON phpbb_attachments (attach_id);
CREATE INDEX phpbb_attachments_privmsgs_id ON phpbb_attachments (privmsgs_id);

# Table: phpbb_attach_desc
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
  thumbnail SMALLINT DEFAULT 0 NOT NULL,
  PRIMARY KEY (attach_id)
);

CREATE GENERATOR phpbb_attach_desc_gen;
SET GENERATOR phpbb_attach_desc_gen TO 0;
CREATE INDEX phpbb_attach_desc_filetime ON phpbb_attach_desc (filetime);
CREATE INDEX phpbb_attach_desc_filesize ON phpbb_attach_desc (filesize);

CREATE TRIGGER phpbb_attach_desc_trig 
	FOR phpbb_attach_desc BEFORE INSERT
	AS BEGIN 
		IF (NEW.attach_id IS NULL) THEN 
			NEW.attach_id = GEN_ID(phpbb_attach_desc_gen, 1)|
	END;

# Table: phpbb_auth_groups
CREATE TABLE phpbb_auth_groups (
  group_id INTEGER DEFAULT 0 NOT NULL,
  forum_id INTEGER DEFAULT 0 NOT NULL,
  auth_option_id SMALLINT DEFAULT 0 NOT NULL,
  auth_setting SMALLINT DEFAULT 0 NOT NULL
);

CREATE INDEX phpbb_auth_groups_group_id ON phpbb_auth_groups (group_id);
CREATE INDEX phpbb_auth_groups_option_id ON phpbb_auth_groups (auth_option_id);

# Table: phpbb_auth_options
CREATE TABLE phpbb_auth_options (
  auth_option_id SMALLINT NOT NULL,
  auth_option CHAR(20) NOT NULL,
  is_global SMALLINT DEFAULT 0 NOT NULL,
  is_local SMALLINT DEFAULT 0 NOT NULL,
  founder_only SMALLINT DEFAULT 0 NOT NULL, 
  PRIMARY KEY (auth_option_id) 
);

CREATE GENERATOR phpbb_auth_options_gen;
SET GENERATOR phpbb_auth_options_gen TO 0;
CREATE INDEX phpbb_auth_options_auth_option ON phpbb_auth_options (auth_option);

CREATE TRIGGER phpbb_auth_options_trig 
	FOR phpbb_auth_options BEFORE INSERT
	AS BEGIN 
		IF (NEW.auth_option_id IS NULL) THEN 
			NEW.auth_option_id = GEN_ID(phpbb_auth_options_gen, 1)|
	END;

# Table: phpbb_auth_presets
CREATE TABLE phpbb_auth_presets (
  preset_id SMALLINT NOT NULL, 
  preset_name VARCHAR(50) NOT NULL, 
  preset_user_id INTEGER NOT NULL, 
  preset_type VARCHAR(2) NOT NULL, 
  preset_data BLOB SUB_TYPE 1,
  PRIMARY KEY (preset_id) 
);

CREATE GENERATOR phpbb_auth_presets_gen;
SET GENERATOR phpbb_auth_presets_gen TO 0;
CREATE INDEX phpbb_auth_presets_type ON phpbb_auth_presets (preset_type);

CREATE TRIGGER phpbb_auth_presets_trig 
	FOR phpbb_auth_presets BEFORE INSERT
	AS BEGIN 
		IF (NEW.preset_id IS NULL) THEN 
			NEW.preset_id = GEN_ID(phpbb_auth_presets_gen, 1)|
	END;

# Table: phpbb_auth_users
CREATE TABLE phpbb_auth_users (
  user_id INTEGER DEFAULT 0 NOT NULL,
  forum_id INTEGER DEFAULT 0 NOT NULL,
  auth_option_id SMALLINT DEFAULT 0 NOT NULL,
  auth_setting SMALLINT DEFAULT 0 NOT NULL
);

CREATE INDEX phpbb_auth_users_user_id ON phpbb_auth_users (user_id);
CREATE INDEX phpbb_auth_users_option_id ON phpbb_auth_users (auth_option_id);

# Table: 'phpbb_banlist'
CREATE TABLE phpbb_banlist (
   ban_id INTEGER NOT NULL,
   ban_userid INTEGER DEFAULT 0 NOT NULL,
   ban_ip VARCHAR(40) DEFAULT '' NOT NULL,
   ban_email VARCHAR(50) DEFAULT '' NOT NULL,
   ban_start INTEGER DEFAULT 0 NOT NULL,
   ban_end INTEGER DEFAULT 0 NOT NULL,
   ban_exclude SMALLINT DEFAULT 0 NOT NULL, 
   ban_reason VARCHAR(255),
   ban_give_reason VARCHAR(255) DEFAULT '' NOT NULL, 
   PRIMARY KEY (ban_id) 
);

CREATE GENERATOR phpbb_banlist_gen;
SET GENERATOR phpbb_banlist_gen TO 0;

CREATE TRIGGER phpbb_banlist_trig 
	FOR phpbb_banlist BEFORE INSERT
	AS BEGIN 
		IF (NEW.ban_id IS NULL) THEN 
			NEW.ban_id = GEN_ID(phpbb_banlist_gen, 1)|
	END;

# Table: 'phpbb_config'
CREATE TABLE phpbb_config (
    config_name VARCHAR(50) NOT NULL,
    config_value VARCHAR(255) NOT NULL,
    is_dynamic SMALLINT DEFAULT 0 NOT NULL, 
	PRIMARY KEY (config_name) 
);

CREATE INDEX phpbb_config_is_dynamic ON phpbb_config (is_dynamic);

# Table: 'phpbb_confirm'
CREATE TABLE phpbb_confirm (
  confirm_id CHAR(32) DEFAULT '' NOT NULL,
  session_id CHAR(32) DEFAULT '' NOT NULL,
  code CHAR(6) DEFAULT '' NOT NULL, 
  PRIMARY KEY (session_id, confirm_id) 
);


# Table: 'phpbb_disallow'
CREATE TABLE phpbb_disallow (
   disallow_id INTEGER NOT NULL,
   disallow_username VARCHAR(30), 
   PRIMARY KEY (disallow_id) 
);

CREATE GENERATOR phpbb_disallow_gen;
SET GENERATOR phpbb_disallow_gen TO 0;

CREATE TRIGGER phpbb_disallow_trig 
	FOR phpbb_disallow BEFORE INSERT
	AS BEGIN 
		IF (NEW.disallow_id IS NULL) THEN 
			NEW.disallow_id = GEN_ID(phpbb_disallow_gen, 1)|
	END;

# Table: 'phpbb_extensions'
CREATE TABLE phpbb_extensions (
  extension_id INTEGER NOT NULL,
  group_id INTEGER DEFAULT 0 NOT NULL,
  extension VARCHAR(100) DEFAULT '' NOT NULL,
  comment VARCHAR(100) DEFAULT '' NOT NULL,
  PRIMARY KEY (extension_id)
);

CREATE GENERATOR phpbb_extensions_gen;
SET GENERATOR phpbb_extensions_gen TO 0;

CREATE TRIGGER phpbb_extensions_trig 
	FOR phpbb_extensions BEFORE INSERT
	AS BEGIN 
		IF (NEW.extension_id IS NULL) THEN 
			NEW.extension_id = GEN_ID(phpbb_extensions_gen, 1)|
	END;

# Table: 'phpbb_extension_groups'
CREATE TABLE phpbb_extension_groups (
  group_id INTEGER NOT NULL,
  group_name VARCHAR(20) DEFAULT '' NOT NULL,
  cat_id SMALLINT DEFAULT 0 NOT NULL, 
  allow_group SMALLINT DEFAULT 0 NOT NULL,
  download_mode SMALLINT DEFAULT 1 NOT NULL,
  upload_icon VARCHAR(100) DEFAULT '' NOT NULL,
  max_filesize INTEGER DEFAULT 0 NOT NULL,
  PRIMARY KEY (group_id)
);

CREATE GENERATOR phpbb_extension_groups_gen;
SET GENERATOR phpbb_extension_groups_gen TO 0;

CREATE TRIGGER phpbb_extension_groups_trig 
	FOR phpbb_extension_groups BEFORE INSERT
	AS BEGIN 
		IF (NEW.group_id IS NULL) THEN 
			NEW.group_id = GEN_ID(phpbb_extension_groups_gen, 1)|
	END;

# Table: 'phpbb_forbidden_extensions'
CREATE TABLE phpbb_forbidden_extensions (
  extension_id INTEGER NOT NULL, 
  extension VARCHAR(100) NOT NULL, 
  PRIMARY KEY (extension_id)
);

CREATE GENERATOR phpbb_forbidden_extensions_gen;
SET GENERATOR phpbb_forbidden_extensions_gen TO 0;

CREATE TRIGGER phpbb_forbidden_extensions_trig 
	FOR phpbb_forbidden_extensions BEFORE INSERT
	AS BEGIN 
		IF (NEW.extension_id IS NULL) THEN 
			NEW.extension_id = GEN_ID(phpbb_forbidden_extensions_gen, 1)|
	END;

# Table: 'phpbb_forums'
CREATE TABLE phpbb_forums (
   forum_id SMALLINT NOT NULL,
   parent_id SMALLINT NOT NULL,
   left_id SMALLINT NOT NULL,
   right_id SMALLINT NOT NULL,
   forum_parents BLOB SUB_TYPE 1,
   forum_name VARCHAR(150) NOT NULL,
   forum_desc BLOB SUB_TYPE 1,
   forum_link VARCHAR(200) DEFAULT '' NOT NULL,
   forum_password VARCHAR(32) DEFAULT '' NOT NULL, 
   forum_style SMALLINT DEFAULT 0 NOT NULL,
   forum_image VARCHAR(50) DEFAULT '' NOT NULL,
   forum_topics_per_page SMALLINT DEFAULT 0 NOT NULL,
   forum_type SMALLINT DEFAULT 0 NOT NULL,
   forum_status SMALLINT DEFAULT 0 NOT NULL,
   forum_posts INTEGER DEFAULT 0 NOT NULL,
   forum_topics INTEGER DEFAULT 0 NOT NULL,
   forum_topics_real INTEGER DEFAULT 0 NOT NULL,
   forum_last_post_id INTEGER DEFAULT 0 NOT NULL,
   forum_last_poster_id INTEGER DEFAULT 0 NOT NULL,
   forum_last_post_time INTEGER DEFAULT 0 NOT NULL,
   forum_last_poster_name VARCHAR(30) DEFAULT '' NOT NULL,
   forum_flags SMALLINT DEFAULT 0 NOT NULL,
   display_on_index SMALLINT DEFAULT 1 NOT NULL,
   enable_icons SMALLINT DEFAULT 1 NOT NULL, 
   enable_prune SMALLINT DEFAULT 0 NOT NULL, 
   prune_next INTEGER DEFAULT 0 NOT NULL,
   prune_days SMALLINT DEFAULT 0 NOT NULL,
   prune_freq SMALLINT DEFAULT 0 NOT NULL, 
   PRIMARY KEY (forum_id)
);

CREATE GENERATOR phpbb_forums_gen;
SET GENERATOR phpbb_forums_gen TO 0;
CREATE INDEX phpbb_forums_left_id ON phpbb_forums (left_id);
CREATE INDEX phpbb_forums_last_post_id ON phpbb_forums (forum_last_post_id);

CREATE TRIGGER phpbb_forums_trig 
	FOR phpbb_forums BEFORE INSERT
	AS BEGIN 
		IF (NEW.forum_id IS NULL) THEN 
			NEW.forum_id = GEN_ID(phpbb_forums_gen, 1)|
	END;

# Table: phpbb_forum_access
CREATE TABLE phpbb_forum_access (
  forum_id INTEGER DEFAULT 0 NOT NULL,
  user_id INTEGER DEFAULT 0 NOT NULL,
  session_id CHAR(32) DEFAULT '' NOT NULL,
  PRIMARY KEY  (forum_id,user_id,session_id)
);

# Table: 'phpbb_forums_marking'
CREATE TABLE phpbb_forums_marking (
   user_id INTEGER DEFAULT 0 NOT NULL,
   forum_id INTEGER DEFAULT 0 NOT NULL,
   mark_time INTEGER DEFAULT 0 NOT NULL,
   PRIMARY KEY (user_id, forum_id)
);

# Table: 'phpbb_forums_watch'
CREATE TABLE phpbb_forums_watch (
  forum_id SMALLINT DEFAULT 0 NOT NULL,
  user_id INTEGER DEFAULT 0 NOT NULL,
  notify_status SMALLINT DEFAULT 0 NOT NULL 
);

CREATE INDEX phpbb_forums_watch_forum_id ON phpbb_forums_watch (forum_id);
CREATE INDEX phpbb_forums_watch_user_id ON phpbb_forums_watch (user_id);
CREATE INDEX phpbb_forums_watch_status ON phpbb_forums_watch (notify_status);

# Table: 'phpbb_groups'
CREATE TABLE phpbb_groups (
   group_id INTEGER NOT NULL,
   group_type SMALLINT DEFAULT 1 NOT NULL,
   group_name VARCHAR(40) DEFAULT '' NOT NULL,
   group_display SMALLINT DEFAULT 0 NOT NULL, 
   group_avatar VARCHAR(100) DEFAULT '' NOT NULL,
   group_avatar_type SMALLINT DEFAULT 0 NOT NULL,
   group_rank INTEGER DEFAULT 0 NOT NULL,
   group_colour VARCHAR(6) DEFAULT '' NOT NULL,
   group_description VARCHAR(255) DEFAULT '' NOT NULL, 
   PRIMARY KEY (group_id) 
);

CREATE GENERATOR phpbb_groups_gen;
SET GENERATOR phpbb_groups_gen TO 0;

CREATE TRIGGER phpbb_groups_trig 
	FOR phpbb_groups BEFORE INSERT
	AS BEGIN 
		IF (NEW.group_id IS NULL) THEN 
			NEW.group_id = GEN_ID(phpbb_groups_gen, 1)|
	END;

# Table: 'phpbb_groups_moderator'
CREATE TABLE phpbb_groups_moderator (
   group_id INTEGER NOT NULL,
   user_id INTEGER NOT NULL
);

# Table: 'phpbb_icons'
CREATE TABLE phpbb_icons (
   icons_id SMALLINT NOT NULL,
   icons_url VARCHAR(50),
   icons_width SMALLINT NOT NULL,
   icons_height SMALLINT NOT NULL,
   icons_order SMALLINT NOT NULL,
   display_on_posting SMALLINT DEFAULT 1 NOT NULL, 
   PRIMARY KEY (icons_id) 
);

CREATE GENERATOR phpbb_icons_gen;
SET GENERATOR phpbb_icons_gen TO 0;

CREATE TRIGGER phpbb_icons_trig 
	FOR phpbb_icons BEFORE INSERT
	AS BEGIN 
		IF (NEW.icons_id IS NULL) THEN 
			NEW.icons_id = GEN_ID(phpbb_icons_gen, 1)|
	END;

# Table: 'phpbb_lang'
CREATE TABLE phpbb_lang (
   lang_id SMALLINT NOT NULL,
   lang_iso VARCHAR(5) NOT NULL, 
   lang_dir VARCHAR(30) NOT NULL, 
   lang_english_name VARCHAR(30), 
   lang_local_name VARCHAR(100), 
   lang_author VARCHAR(100), 
   PRIMARY KEY (lang_id)
);

CREATE GENERATOR phpbb_lang_gen;
SET GENERATOR phpbb_lang_gen TO 0;

CREATE TRIGGER phpbb_lang_trig 
	FOR phpbb_lang BEFORE INSERT
	AS BEGIN 
		IF (NEW.lang_id IS NULL) THEN 
			NEW.lang_id = GEN_ID(phpbb_lang_gen, 1)|
	END;

# Table: 'phpbb_log_moderator'
CREATE TABLE phpbb_log_moderator (
  log_id INTEGER DEFAULT 0 NOT NULL,
  user_id INTEGER DEFAULT 0 NOT NULL,
  forum_id INTEGER DEFAULT 0 NOT NULL,
  topic_id INTEGER DEFAULT 0 NOT NULL,
  log_ip VARCHAR(40) NOT NULL,
  log_time INTEGER NOT NULL,
  log_operation BLOB SUB_TYPE 1,
  log_data BLOB SUB_TYPE 1,
  PRIMARY KEY (log_id) 
);

CREATE GENERATOR phpbb_log_moderator_gen;
SET GENERATOR phpbb_log_moderator_gen TO 0;
CREATE INDEX phpbb_log_moderator_forum ON phpbb_log_moderator (forum_id);
CREATE INDEX phpbb_log_moderator_topic ON phpbb_log_moderator (topic_id);
CREATE INDEX phpbb_log_moderator_user ON phpbb_log_moderator (user_id);

CREATE TRIGGER phpbb_log_moderator_trig 
	FOR phpbb_log_moderator BEFORE INSERT
	AS BEGIN 
		IF (NEW.log_id IS NULL) THEN 
			NEW.log_id = GEN_ID(phpbb_log_moderator_gen, 1)|
	END;

# Table: 'phpbb_log_admin'
CREATE TABLE phpbb_log_admin (
  log_id INTEGER NOT NULL,
  user_id INTEGER DEFAULT 0 NOT NULL,
  log_ip VARCHAR(40) NOT NULL,
  log_time INTEGER NOT NULL,
  log_operation BLOB SUB_TYPE 1,
  log_data BLOB SUB_TYPE 1,
  PRIMARY KEY (log_id)
);

CREATE GENERATOR phpbb_log_admin_gen;
SET GENERATOR phpbb_log_admin_gen TO 0;
CREATE INDEX phpbb_log_admin_user_id ON phpbb_log_admin (user_id);

CREATE TRIGGER phpbb_log_admin_trig 
	FOR phpbb_log_admin BEFORE INSERT
	AS BEGIN 
		IF (NEW.log_id IS NULL) THEN 
			NEW.log_id = GEN_ID(phpbb_log_admin_gen, 1)|
	END;

# Table: 'phpbb_moderator_cache'
CREATE TABLE phpbb_moderator_cache (
  forum_id INTEGER NOT NULL,
  user_id INTEGER,
  username CHAR(30),
  group_id INTEGER,
  groupname CHAR(30),
  display_on_index SMALLINT DEFAULT 1 NOT NULL 
);

CREATE INDEX phpbb_mod_cache_disp ON phpbb_moderator_cache (display_on_index);
CREATE INDEX phpbb_mod_cache_forum ON phpbb_moderator_cache (forum_id);

# Table: 'phpbb_vote_results'
CREATE TABLE phpbb_poll_results (
  poll_option_id SMALLINT DEFAULT 0 NOT NULL,
  topic_id INTEGER DEFAULT 0 NOT NULL,
  poll_option_text VARCHAR(255) DEFAULT '' NOT NULL,
  poll_option_total INTEGER DEFAULT 0 NOT NULL 
);

CREATE INDEX phpbb_poll_results_id ON phpbb_poll_results (poll_option_id);
CREATE INDEX phpbb_poll_results_topic_id ON phpbb_poll_results (topic_id);

# Table: 'phpbb_vote_voters'
CREATE TABLE phpbb_poll_voters (
  topic_id INTEGER DEFAULT 0 NOT NULL,
  poll_option_id SMALLINT DEFAULT 0 NOT NULL,
  vote_user_id INTEGER DEFAULT 0 NOT NULL,
  vote_user_ip VARCHAR(40) DEFAULT '' NOT NULL 
);

CREATE INDEX phpbb_poll_voters_topic ON phpbb_poll_voters (topic_id);
CREATE INDEX phpbb_poll_voters_vote_user ON phpbb_poll_voters (vote_user_id);
CREATE INDEX phpbb_poll_voters_vote_ip ON phpbb_poll_voters (vote_user_ip);

# Table: 'phpbb_posts'
CREATE TABLE phpbb_posts (
   post_id INTEGER NOT NULL,
   topic_id INTEGER DEFAULT 0 NOT NULL,
   forum_id SMALLINT DEFAULT 0 NOT NULL,
   poster_id INTEGER DEFAULT 0 NOT NULL,
   icon_id SMALLINT DEFAULT 1 NOT NULL,
   poster_ip VARCHAR(40) DEFAULT '' NOT NULL,
   post_time INTEGER DEFAULT 0 NOT NULL,
   post_approved SMALLINT DEFAULT 1 NOT NULL,
   post_reported SMALLINT DEFAULT 0 NOT NULL,
   enable_bbcode SMALLINT DEFAULT 1 NOT NULL,
   enable_html SMALLINT DEFAULT 0 NOT NULL,
   enable_smilies SMALLINT DEFAULT 1 NOT NULL,
   enable_magic_url SMALLINT DEFAULT 1 NOT NULL,
   enable_sig SMALLINT DEFAULT 1 NOT NULL,
   post_username VARCHAR(30) DEFAULT '',
   post_subject VARCHAR(60) DEFAULT '',
   post_text BLOB SUB_TYPE 1 DEFAULT '' NOT NULL,
   post_checksum VARCHAR(32) DEFAULT '' NOT NULL,
   post_encoding VARCHAR(11) DEFAULT 'iso-8859-15' NOT NULL, 
   post_attachment SMALLINT DEFAULT 0 NOT NULL,
   bbcode_bitfield INTEGER DEFAULT 0 NOT NULL,
   bbcode_uid VARCHAR(10) DEFAULT '' NOT NULL,
   post_edit_time INTEGER DEFAULT 0 NOT NULL,
   post_edit_count SMALLINT DEFAULT 0 NOT NULL,
   post_edit_locked SMALLINT DEFAULT 0 NOT NULL,
   PRIMARY KEY (post_id) 
);

CREATE GENERATOR phpbb_posts_gen;
SET GENERATOR phpbb_posts_gen TO 0;
CREATE INDEX phpbb_posts_forum_id ON phpbb_posts (forum_id);
CREATE INDEX phpbb_posts_topic_id ON phpbb_posts (topic_id);
CREATE INDEX phpbb_posts_poster_ip ON phpbb_posts (poster_ip);
CREATE INDEX phpbb_posts_poster_id ON phpbb_posts (poster_id);
CREATE INDEX phpbb_posts_post_apv ON phpbb_posts (post_approved);

CREATE TRIGGER phpbb_posts_trig 
	FOR phpbb_posts BEFORE INSERT
	AS BEGIN 
		IF (NEW.post_id IS NULL) THEN 
			NEW.post_id = GEN_ID(phpbb_posts_gen, 1)|
	END;

# Table: 'phpbb_privmsgs'
CREATE TABLE phpbb_privmsgs (
   privmsgs_id INTEGER NOT NULL,
   privmsgs_attachment SMALLINT DEFAULT 0 NOT NULL,
   privmsgs_type SMALLINT DEFAULT 0 NOT NULL,
   privmsgs_subject VARCHAR(60) DEFAULT 0 NOT NULL,
   privmsgs_from_userid INTEGER DEFAULT 0 NOT NULL,
   privmsgs_to_userid INTEGER DEFAULT 0 NOT NULL,
   privmsgs_date INTEGER DEFAULT 0 NOT NULL,
   privmsgs_ip VARCHAR(40) NOT NULL,
   privmsgs_enable_bbcode SMALLINT DEFAULT 1 NOT NULL,
   privmsgs_enable_html SMALLINT DEFAULT 0 NOT NULL,
   privmsgs_enable_smilies SMALLINT DEFAULT 1 NOT NULL,
   privmsgs_attach_sig SMALLINT DEFAULT 1 NOT NULL,
   privmsgs_text BLOB SUB_TYPE 1,
   privmsgs_bbcode_uid VARCHAR(10) DEFAULT 0 NOT NULL, 
   PRIMARY KEY (privmsgs_id) 
);

CREATE GENERATOR phpbb_privmsgs_gen;
SET GENERATOR phpbb_privmsgs_gen TO 0;
CREATE INDEX phpbb_privmsgs_from_userid ON phpbb_privmsgs (privmsgs_from_userid);
CREATE INDEX phpbb_privmsgs_to_userid ON phpbb_privmsgs (privmsgs_to_userid);

CREATE TRIGGER phpbb_privmsgs_trig 
	FOR phpbb_privmsgs BEFORE INSERT
	AS BEGIN 
		IF (NEW.privmsgs_id IS NULL) THEN 
			NEW.privmsgs_id = GEN_ID(phpbb_privmsgs_gen, 1)|
	END;

# Table: 'phpbb_ranks'
CREATE TABLE phpbb_ranks (
   rank_id SMALLINT NOT NULL,
   rank_title VARCHAR(50) NOT NULL,
   rank_min INTEGER DEFAULT 0 NOT NULL,
   rank_special SMALLINT DEFAULT 0,
   rank_image VARCHAR(100),
   PRIMARY KEY (rank_id) 
);

CREATE GENERATOR phpbb_ranks_gen;
SET GENERATOR phpbb_ranks_gen TO 0;

CREATE TRIGGER phpbb_ranks_trig 
	FOR phpbb_ranks BEFORE INSERT
	AS BEGIN 
		IF (NEW.rank_id IS NULL) THEN 
			NEW.rank_id = GEN_ID(phpbb_ranks_gen, 1)|
	END;

# Table: 'phpbb_ratings'
CREATE TABLE phpbb_ratings (
  post_id INTEGER DEFAULT 0 NOT NULL,
  user_id SMALLINT DEFAULT 0 NOT NULL,
  rating SMALLINT DEFAULT 0 NOT NULL
);

CREATE INDEX phpbb_ratings_post_id ON phpbb_ratings (post_id);
CREATE INDEX phpbb_ratings_user_id ON phpbb_ratings (user_id);

# Table: 'phpbb_reports_reasons'
CREATE TABLE phpbb_reports_reasons (
  reason_id INTEGER NOT NULL,
  reason_priority SMALLINT DEFAULT 0 NOT NULL,
  reason_name VARCHAR(255) DEFAULT '' NOT NULL,
  reason_description BLOB SUB_TYPE 1 NOT NULL, 
  PRIMARY KEY (reason_id)
);

CREATE GENERATOR phpbb_reports_reasons_gen;
SET GENERATOR phpbb_reports_reasons_gen TO 0;

CREATE TRIGGER phpbb_reports_reasons_trig 
	FOR phpbb_reports_reasons BEFORE INSERT
	AS BEGIN 
		IF (NEW.reason_id IS NULL) THEN 
			NEW.reason_id = GEN_ID(phpbb_reports_reasons_gen, 1)|
	END;

# Table: 'phpbb_reports'
CREATE TABLE phpbb_reports (
  report_id SMALLINT NOT NULL,
  reason_id SMALLINT DEFAULT 0 NOT NULL,
  post_id INTEGER DEFAULT 0 NOT NULL,
  user_id INTEGER DEFAULT 0 NOT NULL,
  user_notify SMALLINT DEFAULT 0 NOT NULL,
  report_time INTEGER DEFAULT 0 NOT NULL,
  report_text BLOB SUB_TYPE 1 NOT NULL, 
  PRIMARY KEY (report_id)
);

CREATE GENERATOR phpbb_reports_gen;
SET GENERATOR phpbb_reports_gen TO 0;

CREATE TRIGGER phpbb_reports_trig 
	FOR phpbb_reports BEFORE INSERT
	AS BEGIN 
		IF (NEW.report_id IS NULL) THEN 
			NEW.report_id = GEN_ID(phpbb_reports_gen, 1)|
	END;

# Table: phpbb_search_results
CREATE TABLE phpbb_search_results (
  search_id INTEGER DEFAULT 0 NOT NULL,
  session_id VARCHAR(32) DEFAULT '' NOT NULL,
  search_array BLOB SUB_TYPE 1 NOT NULL, 
  PRIMARY KEY (search_id)
);

CREATE INDEX phpbb_search_results_session_id ON phpbb_search_results (session_id);

# Table: phpbb_search_wordlist
CREATE TABLE phpbb_search_wordlist (
  word_id INTEGER NOT NULL,
  word_text VARCHAR(50) DEFAULT '' NOT NULL,
  word_common SMALLINT DEFAULT 0 NOT NULL, 
  PRIMARY KEY (word_id)
);

CREATE INDEX phpbb_search_wordlist_text ON phpbb_search_wordlist (word_text);
CREATE INDEX phpbb_search_wordlist_common ON phpbb_search_wordlist (word_common);

# Table: phpbb_search_wordmatch
CREATE TABLE phpbb_search_wordmatch (
  post_id INTEGER DEFAULT 0 NOT NULL,
  word_id INTEGER DEFAULT 0 NOT NULL,
  title_match SMALLINT DEFAULT 0 NOT NULL 
);

CREATE INDEX phpbb_search_wordmatch_post ON phpbb_search_wordmatch (post_id);
CREATE INDEX phpbb_search_wordmatch_word ON phpbb_search_wordmatch (word_id);

# Table: 'phpbb_sessions'
CREATE TABLE phpbb_sessions (
   session_id VARCHAR(32) DEFAULT '' NOT NULL,
   session_user_id INTEGER DEFAULT 0 NOT NULL,
   session_last_visit INTEGER DEFAULT 0 NOT NULL,
   session_start INTEGER DEFAULT 0 NOT NULL,
   session_time INTEGER DEFAULT 0 NOT NULL,
   session_ip VARCHAR(40) DEFAULT 0 NOT NULL,
   session_browser VARCHAR(100) DEFAULT '' NOT NULL,
   session_page VARCHAR(100) DEFAULT 0 NOT NULL,
   session_allow_viewonline SMALLINT DEFAULT 1 NOT NULL, 
   PRIMARY KEY (session_id)
);

CREATE INDEX phpbb_sessions_session_time ON phpbb_sessions (session_time);

# Table: 'phpbb_smilies'
CREATE TABLE phpbb_smilies (
   smile_id SMALLINT NOT NULL,
   code CHAR(10) DEFAULT '' NOT NULL,
   emoticon CHAR(50) DEFAULT '' NOT NULL,
   smile_url CHAR(50) DEFAULT '' NOT NULL,
   smile_width SMALLINT DEFAULT 0 NOT NULL,
   smile_height SMALLINT DEFAULT 0 NOT NULL,
   smile_order SMALLINT DEFAULT 1 NOT NULL,
   display_on_posting SMALLINT DEFAULT 1 NOT NULL, 
   PRIMARY KEY (smile_id)
);

CREATE GENERATOR phpbb_smilies_gen;
SET GENERATOR phpbb_smilies_gen TO 0;

CREATE TRIGGER phpbb_smilies_trig 
	FOR phpbb_smilies BEFORE INSERT
	AS BEGIN 
		IF (NEW.smile_id IS NULL) THEN 
			NEW.smile_id = GEN_ID(phpbb_smilies_gen, 1)|
	END;

# Table: 'phpbb_styles'
CREATE TABLE phpbb_styles (
   style_id SMALLINT NOT NULL,
   template_id CHAR(50) DEFAULT '' NOT NULL,
   theme_id SMALLINT DEFAULT 0 NOT NULL,
   imageset_id SMALLINT DEFAULT 0 NOT NULL,
   style_name CHAR(30) DEFAULT '' NOT NULL, 
   PRIMARY KEY (style_id) 
);

CREATE GENERATOR phpbb_styles_gen;
SET GENERATOR phpbb_styles_gen TO 0;
CREATE INDEX phpbb_styles_template_id ON phpbb_styles (template_id);
CREATE INDEX phpbb_styles_theme_id ON phpbb_styles (theme_id);
CREATE INDEX phpbb_styles_imageset_id ON phpbb_styles (imageset_id);

CREATE TRIGGER phpbb_styles_trig 
	FOR phpbb_styles BEFORE INSERT
	AS BEGIN 
		IF (NEW.style_id IS NULL) THEN 
			NEW.style_id = GEN_ID(phpbb_styles_gen, 1)|
	END;

# Table: 'phpbb_styles_template'
CREATE TABLE phpbb_styles_template (
   template_id SMALLINT NOT NULL,
   template_name CHAR(30) DEFAULT '' NOT NULL,
   template_path CHAR(50) DEFAULT '' NOT NULL,
   poll_length SMALLINT DEFAULT 0 NOT NULL,
   pm_box_length SMALLINT DEFAULT 0 NOT NULL, 
   bbcode_bitfield INT DEFAULT 0 NOT NULL,
   PRIMARY KEY (template_id)
);

CREATE GENERATOR phpbb_styles_template_gen;
SET GENERATOR phpbb_styles_template_gen TO 0;

CREATE TRIGGER phpbb_styles_template_trig 
	FOR phpbb_styles_template BEFORE INSERT
	AS BEGIN 
		IF (NEW.template_id IS NULL) THEN 
			NEW.template_id = GEN_ID(phpbb_styles_template_gen, 1)|
	END;

# Table: 'phpbb_styles_theme'
CREATE TABLE phpbb_styles_theme (
   theme_id SMALLINT NOT NULL,
   theme_name CHAR(60) DEFAULT '' NOT NULL,
   css_external CHAR(100) DEFAULT '' NOT NULL,
   css_data BLOB SUB_TYPE 1, 
   PRIMARY KEY (theme_id)
);

CREATE GENERATOR phpbb_styles_theme_gen;
SET GENERATOR phpbb_styles_theme_gen TO 0;

CREATE TRIGGER phpbb_styles_theme_trig 
	FOR phpbb_styles_theme BEFORE INSERT
	AS BEGIN 
		IF (NEW.theme_id IS NULL) THEN 
			NEW.theme_id = GEN_ID(phpbb_styles_theme_gen, 1)|
	END;

# Table: 'phpbb_styles_imageset'
CREATE TABLE phpbb_styles_imageset (
  imageset_id SMALLINT NOT NULL,
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

CREATE GENERATOR phpbb_styles_imageset_gen;
SET GENERATOR phpbb_styles_imageset_gen TO 0;

CREATE TRIGGER phpbb_styles_imageset_trig 
	FOR phpbb_styles_imageset BEFORE INSERT
	AS BEGIN 
		IF (NEW.imageset_id IS NULL) THEN 
			NEW.imageset_id = GEN_ID(phpbb_styles_imageset_gen, 1)|
	END;

# Table: 'phpbb_topics'
CREATE TABLE phpbb_topics (
   topic_id INTEGER NOT NULL,
   forum_id INTEGER DEFAULT 0 NOT NULL,
   icon_id SMALLINT DEFAULT 1 NOT NULL,
   topic_attachment SMALLINT DEFAULT 0 NOT NULL,
   topic_approved SMALLINT DEFAULT 1 NOT NULL,
   topic_reported SMALLINT DEFAULT 0 NOT NULL,
   topic_title VARCHAR(60) NOT NULL,
   topic_poster INTEGER DEFAULT 0 NOT NULL,
   topic_time INTEGER DEFAULT 0 NOT NULL,
   topic_views INTEGER DEFAULT 0 NOT NULL,
   topic_replies INTEGER DEFAULT 0 NOT NULL,
   topic_replies_real INTEGER DEFAULT 0 NOT NULL,
   topic_status SMALLINT DEFAULT 0 NOT NULL,
   topic_type SMALLINT DEFAULT 0 NOT NULL,
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
   poll_max_options SMALLINT DEFAULT 1 NOT NULL,
   poll_last_vote INTEGER, 
   PRIMARY KEY (topic_id) 
);

CREATE GENERATOR phpbb_topics_gen;
SET GENERATOR phpbb_topics_gen TO 0;
CREATE INDEX phpbb_topics_forum_id ON phpbb_topics (forum_id);
CREATE INDEX phpbb_topics_moved_id ON phpbb_topics (topic_moved_id);
CREATE INDEX phpbb_topics_last_post_time ON phpbb_topics (topic_last_post_time);
CREATE INDEX phpbb_topics_last_poll_vote ON phpbb_topics (poll_last_vote);
CREATE INDEX phpbb_topics_type ON phpbb_topics (topic_type);

CREATE TRIGGER phpbb_topics_trig 
	FOR phpbb_topics BEFORE INSERT
	AS BEGIN 
		IF (NEW.topic_id IS NULL) THEN 
			NEW.topic_id = GEN_ID(phpbb_topics_gen, 1)|
	END;

# Table: 'phpbb_topic_marking'
CREATE TABLE phpbb_topics_marking (
   user_id INTEGER DEFAULT 0 NOT NULL,
   topic_id INTEGER DEFAULT 0 NOT NULL,
   mark_type SMALLINT DEFAULT 0 NOT NULL,
   mark_time INTEGER DEFAULT 0 NOT NULL,
   PRIMARY KEY (user_id, topic_id)
);

# Table: 'phpbb_topics_watch'
CREATE TABLE phpbb_topics_watch (
  topic_id INTEGER DEFAULT 0 NOT NULL,
  user_id INTEGER DEFAULT 0 NOT NULL,
  notify_status SMALLINT DEFAULT 0 NOT NULL 
);

CREATE INDEX phpbb_topics_watch_topic ON phpbb_topics_watch (topic_id);
CREATE INDEX phpbb_topics_watch_user ON phpbb_topics_watch (user_id);
CREATE INDEX phpbb_topics_watch_status ON phpbb_topics_watch (notify_status);

# Table: 'phpbb_ucp_modules'
CREATE TABLE phpbb_ucp_modules (
	module_id INTEGER DEFAULT 0 NOT NULL,
	module_title VARCHAR(50) DEFAULT ''  NOT NULL,
	module_filename VARCHAR(50) DEFAULT '' NOT NULL,
	module_order INTEGER DEFAULT 0 NOT NULL, 
	PRIMARY KEY (module_id)
);

CREATE GENERATOR phpbb_ucp_modules_gen;
SET GENERATOR phpbb_ucp_modules_gen TO 0;
CREATE INDEX phpbb_ucp_modules_order ON phpbb_ucp_modules (module_order);

CREATE TRIGGER phpbb_ucp_modules_trig 
	FOR phpbb_ucp_modules BEFORE INSERT
	AS BEGIN 
		IF (NEW.module_id IS NULL) THEN 
			NEW.module_id = GEN_ID(phpbb_ucp_modules_gen, 1)|
	END;

# Table: 'phpbb_user_group'
CREATE TABLE phpbb_user_group (
   group_id INTEGER DEFAULT 0 NOT NULL,
   user_id INTEGER DEFAULT 0 NOT NULL,
   user_pending SMALLINT 
);

CREATE INDEX phpbb_user_group_user_id ON phpbb_user_group (user_id);
CREATE INDEX phpbb_user_group_group_id ON phpbb_user_group (group_id);

# Table: 'phpbb_users'
CREATE TABLE phpbb_users (
   user_id INTEGER NOT NULL,
   user_active SMALLINT DEFAULT 1,
   user_founder SMALLINT DEFAULT 0 NOT NULL,
   group_id INTEGER DEFAULT 0 NOT NULL,
   user_permissions BLOB SUB_TYPE 1 DEFAULT '',
   user_ip VARCHAR(40),
   user_regdate INTEGER DEFAULT 0 NOT NULL,
   username VARCHAR(30) NOT NULL,
   user_password VARCHAR(32) NOT NULL,
   user_email VARCHAR(60),
   user_birthday VARCHAR(10) DEFAULT '' NOT NULL,
   user_lastvisit INTEGER DEFAULT 0 NOT NULL,
   user_lastpage VARCHAR(100) DEFAULT '' NOT NULL,
   user_karma SMALLINT DEFAULT 3 NOT NULL, 
   user_min_karma SMALLINT DEFAULT -5 NOT NULL, 
   user_startpage VARCHAR(100) DEFAULT '',
   user_colour VARCHAR(6) DEFAULT '' NOT NULL,
   user_posts INTEGER DEFAULT 0 NOT NULL,
   user_lang VARCHAR(30) DEFAULT '' NOT NULL,
   user_timezone decimal(5,2) DEFAULT 0 NOT NULL,
   user_dst SMALLINT DEFAULT 0 NOT NULL,
   user_dateformat VARCHAR(15) DEFAULT 'd M Y H:i' NOT NULL,
   user_style SMALLINT DEFAULT 1 NOT NULL,
   user_rank INTEGER DEFAULT 0 NOT NULL,
   user_new_privmsg SMALLINT DEFAULT 0 NOT NULL,
   user_unread_privmsg SMALLINT DEFAULT 0 NOT NULL,
   user_last_privmsg INTEGER DEFAULT 0 NOT NULL,
   user_emailtime INTEGER,
   user_sortby_type VARCHAR(1) DEFAULT '' NOT NULL,
   user_sortby_dir VARCHAR(1) DEFAULT '' NOT NULL,
   user_show_days SMALLINT DEFAULT 0 NOT NULL,
   user_viewimg SMALLINT DEFAULT 1 NOT NULL,
   user_notify SMALLINT DEFAULT 0 NOT NULL,
   user_notify_pm SMALLINT DEFAULT 1 NOT NULL,
   user_popup_pm SMALLINT DEFAULT 0 NOT NULL,
   user_viewflash SMALLINT DEFAULT 1 NOT NULL,
   user_viewsmilies SMALLINT DEFAULT 1 NOT NULL,
   user_viewsigs SMALLINT DEFAULT 1 NOT NULL,
   user_viewavatars SMALLINT DEFAULT 1 NOT NULL,
   user_viewcensors SMALLINT DEFAULT 1 NOT NULL,
   user_attachsig SMALLINT DEFAULT 1 NOT NULL,
   user_allowhtml SMALLINT DEFAULT 1 NOT NULL,
   user_allowbbcode SMALLINT DEFAULT 1 NOT NULL,
   user_allowsmile SMALLINT DEFAULT 1 NOT NULL,
   user_allowavatar SMALLINT DEFAULT 1 NOT NULL,
   user_allow_pm SMALLINT DEFAULT 1 NOT NULL,
   user_allow_email SMALLINT DEFAULT 1 NOT NULL,
   user_allow_viewonline SMALLINT DEFAULT 1 NOT NULL,
   user_allow_viewemail SMALLINT DEFAULT 1 NOT NULL,
   user_allow_massemail SMALLINT DEFAULT 1 NOT NULL,
   user_avatar VARCHAR(100) DEFAULT '' NOT NULL,
   user_avatar_type SMALLINT DEFAULT 0 NOT NULL,
   user_avatar_width SMALLINT DEFAULT 0 NOT NULL,
   user_avatar_height SMALLINT DEFAULT 0 NOT NULL,
   user_sig BLOB SUB_TYPE 1,
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

CREATE GENERATOR phpbb_users_gen;
SET GENERATOR phpbb_users_gen TO 0;
CREATE INDEX phpbb_users_user_birthday ON phpbb_users (user_birthday);

CREATE TRIGGER phpbb_users_trig 
	FOR phpbb_users BEFORE INSERT
	AS BEGIN 
		IF (NEW.user_id IS NULL) THEN 
			NEW.user_id = GEN_ID(phpbb_users_gen, 1)|
	END;

# Table: 'phpbb_words'
CREATE TABLE phpbb_words (
   word_id INTEGER NOT NULL,
   word CHAR(100) DEFAULT '' NOT NULL,
   replacement CHAR(100) DEFAULT '' NOT NULL, 
   PRIMARY KEY (word_id) 
);

CREATE GENERATOR phpbb_words_gen;
SET GENERATOR phpbb_words_gen TO 0;

CREATE TRIGGER phpbb_words_trig 
	FOR phpbb_words BEFORE INSERT
	AS BEGIN 
		IF (NEW.word_id IS NULL) THEN 
			NEW.word_id = GEN_ID(phpbb_words_gen, 1)|
	END;
