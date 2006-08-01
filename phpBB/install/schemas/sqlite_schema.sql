#
# SQLite Schema for phpBB 3.x - (c) phpBB Group, 2005
#
# $Id$
#

BEGIN TRANSACTION;;

# Table: 'phpbb_attachments'
CREATE TABLE phpbb_attachments (
	attach_id INTEGER NOT NULL ,
	post_msg_id mediumint(8) NOT NULL DEFAULT '0',
	topic_id mediumint(8) NOT NULL DEFAULT '0',
	in_message tinyint(1) NOT NULL DEFAULT '0',
	poster_id mediumint(8) NOT NULL DEFAULT '0',
	physical_filename varchar(255) NOT NULL DEFAULT '',
	real_filename varchar(255) NOT NULL DEFAULT '',
	download_count mediumint(8) NOT NULL DEFAULT '0',
	attach_comment text(65535) NOT NULL DEFAULT '',
	extension varchar(100) NOT NULL DEFAULT '',
	mimetype varchar(100) NOT NULL DEFAULT '',
	filesize int(20) NOT NULL DEFAULT '0',
	filetime int(11) NOT NULL DEFAULT '0',
	thumbnail tinyint(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (attach_id)
);;

CREATE INDEX phpbb_attachments_filetime ON phpbb_attachments (filetime);;
CREATE INDEX phpbb_attachments_post_msg_id ON phpbb_attachments (post_msg_id);;
CREATE INDEX phpbb_attachments_topic_id ON phpbb_attachments (topic_id);;
CREATE INDEX phpbb_attachments_poster_id ON phpbb_attachments (poster_id);;
CREATE INDEX phpbb_attachments_filesize ON phpbb_attachments (filesize);;

# Table: 'phpbb_acl_groups'
CREATE TABLE phpbb_acl_groups (
	group_id mediumint(8) NOT NULL DEFAULT '0',
	forum_id mediumint(8) NOT NULL DEFAULT '0',
	auth_option_id mediumint(8) NOT NULL DEFAULT '0',
	auth_role_id mediumint(8) NOT NULL DEFAULT '0',
	auth_setting tinyint(2) NOT NULL DEFAULT '0'
);;

CREATE INDEX phpbb_acl_groups_group_id ON phpbb_acl_groups (group_id);;
CREATE INDEX phpbb_acl_groups_auth_option_id ON phpbb_acl_groups (auth_option_id);;

# Table: 'phpbb_acl_options'
CREATE TABLE phpbb_acl_options (
	auth_option_id INTEGER NOT NULL ,
	auth_option varchar(50) NOT NULL DEFAULT '',
	is_global tinyint(1) NOT NULL DEFAULT '0',
	is_local tinyint(1) NOT NULL DEFAULT '0',
	founder_only tinyint(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (auth_option_id)
);;

CREATE INDEX phpbb_acl_options_auth_option ON phpbb_acl_options (auth_option);;

# Table: 'phpbb_acl_roles'
CREATE TABLE phpbb_acl_roles (
	role_id INTEGER NOT NULL ,
	role_name varchar(255) NOT NULL DEFAULT '',
	role_description text(65535) NOT NULL DEFAULT '',
	role_type varchar(10) NOT NULL DEFAULT '',
	role_order mediumint(4) NOT NULL DEFAULT '0',
	PRIMARY KEY (role_id)
);;

CREATE INDEX phpbb_acl_roles_role_type ON phpbb_acl_roles (role_type);;
CREATE INDEX phpbb_acl_roles_role_order ON phpbb_acl_roles (role_order);;

# Table: 'phpbb_acl_roles_data'
CREATE TABLE phpbb_acl_roles_data (
	role_id mediumint(8) NOT NULL DEFAULT '0',
	auth_option_id mediumint(8) NOT NULL DEFAULT '0',
	auth_setting tinyint(2) NOT NULL DEFAULT '0',
	PRIMARY KEY (role_id, auth_option_id)
);;


# Table: 'phpbb_acl_users'
CREATE TABLE phpbb_acl_users (
	user_id mediumint(8) NOT NULL DEFAULT '0',
	forum_id mediumint(8) NOT NULL DEFAULT '0',
	auth_option_id mediumint(8) NOT NULL DEFAULT '0',
	auth_role_id mediumint(8) NOT NULL DEFAULT '0',
	auth_setting tinyint(2) NOT NULL DEFAULT '0'
);;

CREATE INDEX phpbb_acl_users_user_id ON phpbb_acl_users (user_id);;
CREATE INDEX phpbb_acl_users_auth_option_id ON phpbb_acl_users (auth_option_id);;

# Table: 'phpbb_banlist'
CREATE TABLE phpbb_banlist (
	ban_id INTEGER NOT NULL ,
	ban_userid mediumint(8) NOT NULL DEFAULT '0',
	ban_ip varchar(40) NOT NULL DEFAULT '',
	ban_email varchar(100) NOT NULL DEFAULT '',
	ban_start int(11) NOT NULL DEFAULT '0',
	ban_end int(11) NOT NULL DEFAULT '0',
	ban_exclude tinyint(1) NOT NULL DEFAULT '0',
	ban_reason text(65535) NOT NULL DEFAULT '',
	ban_give_reason text(65535) NOT NULL DEFAULT '',
	PRIMARY KEY (ban_id)
);;


# Table: 'phpbb_bbcodes'
CREATE TABLE phpbb_bbcodes (
	bbcode_id tinyint(3) NOT NULL DEFAULT '0',
	bbcode_tag varchar(16) NOT NULL DEFAULT '',
	bbcode_helpline varchar(255) DEFAULT '' NOT NULL,
	display_on_posting tinyint(1) NOT NULL DEFAULT '0',
	bbcode_match varchar(255) NOT NULL DEFAULT '',
	bbcode_tpl mediumtext(16777215) NOT NULL DEFAULT '',
	first_pass_match varchar(255) NOT NULL DEFAULT '',
	first_pass_replace varchar(255) NOT NULL DEFAULT '',
	second_pass_match varchar(255) NOT NULL DEFAULT '',
	second_pass_replace mediumtext(16777215) NOT NULL DEFAULT '',
	PRIMARY KEY (bbcode_id)
);;

CREATE INDEX phpbb_bbcodes_display_in_posting ON phpbb_bbcodes (display_on_posting);;

# Table: 'phpbb_bookmarks'
CREATE TABLE phpbb_bookmarks (
	topic_id mediumint(8) NOT NULL DEFAULT '0',
	user_id mediumint(8) NOT NULL DEFAULT '0',
	order_id mediumint(8) NOT NULL DEFAULT '0'
);;

CREATE INDEX phpbb_bookmarks_order_id ON phpbb_bookmarks (order_id);;
CREATE INDEX phpbb_bookmarks_topic_user_id ON phpbb_bookmarks (topic_id, user_id);;

# Table: 'phpbb_bots'
CREATE TABLE phpbb_bots (
	bot_id INTEGER NOT NULL ,
	bot_active tinyint(1) NOT NULL DEFAULT '1',
	bot_name text(65535) NOT NULL DEFAULT '',
	user_id mediumint(8) NOT NULL DEFAULT '0',
	bot_agent varchar(255) NOT NULL DEFAULT '',
	bot_ip varchar(255) NOT NULL DEFAULT '',
	PRIMARY KEY (bot_id)
);;

CREATE INDEX phpbb_bots_bot_active ON phpbb_bots (bot_active);;

# Table: 'phpbb_config'
CREATE TABLE phpbb_config (
	config_name varchar(255) NOT NULL DEFAULT '',
	config_value varchar(255) NOT NULL DEFAULT '',
	is_dynamic tinyint(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (config_name)
);;

CREATE INDEX phpbb_config_is_dynamic ON phpbb_config (is_dynamic);;

# Table: 'phpbb_confirm'
CREATE TABLE phpbb_confirm (
	confirm_id char(32) NOT NULL DEFAULT '',
	session_id char(32) NOT NULL DEFAULT '',
	confirm_type tinyint(3) NOT NULL DEFAULT '0',
	code varchar(8) NOT NULL DEFAULT '',
	PRIMARY KEY (session_id, confirm_id)
);;


# Table: 'phpbb_disallow'
CREATE TABLE phpbb_disallow (
	disallow_id INTEGER NOT NULL ,
	disallow_username varchar(255) NOT NULL DEFAULT '',
	PRIMARY KEY (disallow_id)
);;


# Table: 'phpbb_drafts'
CREATE TABLE phpbb_drafts (
	draft_id INTEGER NOT NULL ,
	user_id mediumint(8) NOT NULL DEFAULT '0',
	topic_id mediumint(8) NOT NULL DEFAULT '0',
	forum_id mediumint(8) NOT NULL DEFAULT '0',
	save_time int(11) NOT NULL DEFAULT '0',
	draft_subject text(65535) NOT NULL DEFAULT '',
	draft_message mediumtext(16777215) NOT NULL DEFAULT '',
	PRIMARY KEY (draft_id)
);;

CREATE INDEX phpbb_drafts_save_time ON phpbb_drafts (save_time);;

# Table: 'phpbb_extensions'
CREATE TABLE phpbb_extensions (
	extension_id INTEGER NOT NULL ,
	group_id mediumint(8) NOT NULL DEFAULT '0',
	extension varchar(100) NOT NULL DEFAULT '',
	PRIMARY KEY (extension_id)
);;


# Table: 'phpbb_extension_groups'
CREATE TABLE phpbb_extension_groups (
	group_id INTEGER NOT NULL ,
	group_name varchar(255) NOT NULL DEFAULT '',
	cat_id tinyint(2) NOT NULL DEFAULT '0',
	allow_group tinyint(1) NOT NULL DEFAULT '0',
	download_mode tinyint(1) NOT NULL DEFAULT '1',
	upload_icon varchar(255) NOT NULL DEFAULT '',
	max_filesize int(20) NOT NULL DEFAULT '0',
	allowed_forums text(65535) NOT NULL DEFAULT '',
	allow_in_pm tinyint(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (group_id)
);;


# Table: 'phpbb_forums'
CREATE TABLE phpbb_forums (
	forum_id INTEGER NOT NULL ,
	parent_id mediumint(8) NOT NULL DEFAULT '0',
	left_id mediumint(8) NOT NULL DEFAULT '0',
	right_id mediumint(8) NOT NULL DEFAULT '0',
	forum_parents mediumtext(16777215) NOT NULL DEFAULT '',
	forum_name text(65535) NOT NULL DEFAULT '',
	forum_desc text(65535) NOT NULL DEFAULT '',
	forum_desc_bitfield blob NOT NULL DEFAULT '',
	forum_desc_options int(11) NOT NULL DEFAULT '0',
	forum_desc_uid varchar(5) NOT NULL DEFAULT '',
	forum_link varchar(255) NOT NULL DEFAULT '',
	forum_password varchar(40) NOT NULL DEFAULT '',
	forum_style tinyint(4) NOT NULL DEFAULT '0',
	forum_image varchar(255) NOT NULL DEFAULT '',
	forum_rules text(65535) NOT NULL DEFAULT '',
	forum_rules_link varchar(255) NOT NULL DEFAULT '',
	forum_rules_bitfield blob NOT NULL DEFAULT '',
	forum_rules_options int(11) NOT NULL DEFAULT '0',
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
	forum_last_poster_name varchar(255) NOT NULL DEFAULT '',
	forum_flags tinyint(4) NOT NULL DEFAULT '32',
	display_on_index tinyint(1) NOT NULL DEFAULT '1',
	enable_indexing tinyint(1) NOT NULL DEFAULT '1',
	enable_icons tinyint(1) NOT NULL DEFAULT '1',
	enable_prune tinyint(1) NOT NULL DEFAULT '0',
	prune_next int(11) NOT NULL DEFAULT '0',
	prune_days tinyint(4) NOT NULL DEFAULT '0',
	prune_viewed tinyint(4) NOT NULL DEFAULT '0',
	prune_freq tinyint(4) NOT NULL DEFAULT '0',
	PRIMARY KEY (forum_id)
);;

CREATE INDEX phpbb_forums_left_right_id ON phpbb_forums (left_id, right_id);;
CREATE INDEX phpbb_forums_forum_last_post_id ON phpbb_forums (forum_last_post_id);;

# Table: 'phpbb_forums_access'
CREATE TABLE phpbb_forums_access (
	forum_id mediumint(8) NOT NULL DEFAULT '0',
	user_id mediumint(8) NOT NULL DEFAULT '0',
	session_id char(32) NOT NULL DEFAULT '',
	PRIMARY KEY (forum_id, user_id, session_id)
);;


# Table: 'phpbb_forums_track'
CREATE TABLE phpbb_forums_track (
	user_id mediumint(8) NOT NULL DEFAULT '0',
	forum_id mediumint(8) NOT NULL DEFAULT '0',
	mark_time int(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (user_id, forum_id)
);;


# Table: 'phpbb_forums_watch'
CREATE TABLE phpbb_forums_watch (
	forum_id mediumint(8) NOT NULL DEFAULT '0',
	user_id mediumint(8) NOT NULL DEFAULT '0',
	notify_status tinyint(1) NOT NULL DEFAULT '0'
);;

CREATE INDEX phpbb_forums_watch_forum_id ON phpbb_forums_watch (forum_id);;
CREATE INDEX phpbb_forums_watch_user_id ON phpbb_forums_watch (user_id);;
CREATE INDEX phpbb_forums_watch_notify_status ON phpbb_forums_watch (notify_status);;

# Table: 'phpbb_groups'
CREATE TABLE phpbb_groups (
	group_id INTEGER NOT NULL ,
	group_type tinyint(4) NOT NULL DEFAULT '1',
	group_name varchar(255) NOT NULL DEFAULT '',
	group_desc text(65535) NOT NULL DEFAULT '',
	group_desc_bitfield blob NOT NULL DEFAULT '',
	group_desc_options int(11) NOT NULL DEFAULT '0',
	group_desc_uid varchar(5) NOT NULL DEFAULT '',
	group_display tinyint(1) NOT NULL DEFAULT '0',
	group_avatar varchar(255) NOT NULL DEFAULT '',
	group_avatar_type tinyint(4) NOT NULL DEFAULT '0',
	group_avatar_width tinyint(4) NOT NULL DEFAULT '0',
	group_avatar_height tinyint(4) NOT NULL DEFAULT '0',
	group_rank mediumint(8) NOT NULL DEFAULT '0',
	group_colour varchar(6) NOT NULL DEFAULT '',
	group_sig_chars mediumint(8) NOT NULL DEFAULT '0',
	group_receive_pm tinyint(1) NOT NULL DEFAULT '0',
	group_message_limit mediumint(8) NOT NULL DEFAULT '0',
	group_legend tinyint(1) NOT NULL DEFAULT '1',
	PRIMARY KEY (group_id)
);;

CREATE INDEX phpbb_groups_group_legend ON phpbb_groups (group_legend);;

# Table: 'phpbb_icons'
CREATE TABLE phpbb_icons (
	icons_id INTEGER NOT NULL ,
	icons_url varchar(255) NOT NULL DEFAULT '',
	icons_width tinyint(4) NOT NULL DEFAULT '0',
	icons_height tinyint(4) NOT NULL DEFAULT '0',
	icons_order mediumint(8) NOT NULL DEFAULT '0',
	display_on_posting tinyint(1) NOT NULL DEFAULT '1',
	PRIMARY KEY (icons_id)
);;


# Table: 'phpbb_lang'
CREATE TABLE phpbb_lang (
	lang_id INTEGER NOT NULL ,
	lang_iso varchar(30) NOT NULL DEFAULT '',
	lang_dir varchar(30) NOT NULL DEFAULT '',
	lang_english_name varchar(100) NOT NULL DEFAULT '',
	lang_local_name varchar(255) NOT NULL DEFAULT '',
	lang_author varchar(255) NOT NULL DEFAULT '',
	PRIMARY KEY (lang_id)
);;

CREATE INDEX phpbb_lang_lang_iso ON phpbb_lang (lang_iso);;

# Table: 'phpbb_log'
CREATE TABLE phpbb_log (
	log_id INTEGER NOT NULL ,
	log_type tinyint(4) NOT NULL DEFAULT '0',
	user_id mediumint(8) NOT NULL DEFAULT '0',
	forum_id mediumint(8) NOT NULL DEFAULT '0',
	topic_id mediumint(8) NOT NULL DEFAULT '0',
	reportee_id mediumint(8) NOT NULL DEFAULT '0',
	log_ip varchar(40) NOT NULL DEFAULT '',
	log_time int(11) NOT NULL DEFAULT '0',
	log_operation text(65535) NOT NULL DEFAULT '',
	log_data mediumtext(16777215) NOT NULL DEFAULT '',
	PRIMARY KEY (log_id)
);;

CREATE INDEX phpbb_log_log_type ON phpbb_log (log_type);;
CREATE INDEX phpbb_log_forum_id ON phpbb_log (forum_id);;
CREATE INDEX phpbb_log_topic_id ON phpbb_log (topic_id);;
CREATE INDEX phpbb_log_reportee_id ON phpbb_log (reportee_id);;
CREATE INDEX phpbb_log_user_id ON phpbb_log (user_id);;

# Table: 'phpbb_moderator_cache'
CREATE TABLE phpbb_moderator_cache (
	forum_id mediumint(8) NOT NULL DEFAULT '0',
	user_id mediumint(8) NOT NULL DEFAULT '0',
	username varchar(255) NOT NULL DEFAULT '',
	group_id mediumint(8) NOT NULL DEFAULT '0',
	group_name varchar(255) NOT NULL DEFAULT '',
	display_on_index tinyint(1) NOT NULL DEFAULT '1'
);;

CREATE INDEX phpbb_moderator_cache_display_on_index ON phpbb_moderator_cache (display_on_index);;
CREATE INDEX phpbb_moderator_cache_forum_id ON phpbb_moderator_cache (forum_id);;

# Table: 'phpbb_modules'
CREATE TABLE phpbb_modules (
	module_id INTEGER NOT NULL ,
	module_enabled tinyint(1) NOT NULL DEFAULT '1',
	module_display tinyint(1) NOT NULL DEFAULT '1',
	module_basename varchar(255) NOT NULL DEFAULT '',
	module_class varchar(10) NOT NULL DEFAULT '',
	parent_id mediumint(8) NOT NULL DEFAULT '0',
	left_id mediumint(8) NOT NULL DEFAULT '0',
	right_id mediumint(8) NOT NULL DEFAULT '0',
	module_langname varchar(255) NOT NULL DEFAULT '',
	module_mode varchar(255) NOT NULL DEFAULT '',
	module_auth varchar(255) NOT NULL DEFAULT '',
	PRIMARY KEY (module_id)
);;

CREATE INDEX phpbb_modules_left_right_id ON phpbb_modules (left_id, right_id);;
CREATE INDEX phpbb_modules_module_enabled ON phpbb_modules (module_enabled);;
CREATE INDEX phpbb_modules_class_left_id ON phpbb_modules (module_class, left_id);;

# Table: 'phpbb_poll_options'
CREATE TABLE phpbb_poll_options (
	poll_option_id tinyint(4) NOT NULL DEFAULT '0',
	topic_id mediumint(8) NOT NULL DEFAULT '0',
	poll_option_text text(65535) NOT NULL DEFAULT '',
	poll_option_total mediumint(8) NOT NULL DEFAULT '0'
);;

CREATE INDEX phpbb_poll_options_poll_option_id ON phpbb_poll_options (poll_option_id);;
CREATE INDEX phpbb_poll_options_topic_id ON phpbb_poll_options (topic_id);;

# Table: 'phpbb_poll_votes'
CREATE TABLE phpbb_poll_votes (
	topic_id mediumint(8) NOT NULL DEFAULT '0',
	poll_option_id tinyint(4) NOT NULL DEFAULT '0',
	vote_user_id mediumint(8) NOT NULL DEFAULT '0',
	vote_user_ip varchar(40) NOT NULL DEFAULT ''
);;

CREATE INDEX phpbb_poll_votes_topic_id ON phpbb_poll_votes (topic_id);;
CREATE INDEX phpbb_poll_votes_vote_user_id ON phpbb_poll_votes (vote_user_id);;
CREATE INDEX phpbb_poll_votes_vote_user_ip ON phpbb_poll_votes (vote_user_ip);;

# Table: 'phpbb_posts'
CREATE TABLE phpbb_posts (
	post_id INTEGER NOT NULL ,
	topic_id mediumint(8) NOT NULL DEFAULT '0',
	forum_id mediumint(8) NOT NULL DEFAULT '0',
	poster_id mediumint(8) NOT NULL DEFAULT '0',
	icon_id mediumint(8) NOT NULL DEFAULT '0',
	poster_ip varchar(40) NOT NULL DEFAULT '',
	post_time int(11) NOT NULL DEFAULT '0',
	post_approved tinyint(1) NOT NULL DEFAULT '1',
	post_reported tinyint(1) NOT NULL DEFAULT '0',
	enable_bbcode tinyint(1) NOT NULL DEFAULT '1',
	enable_smilies tinyint(1) NOT NULL DEFAULT '1',
	enable_magic_url tinyint(1) NOT NULL DEFAULT '1',
	enable_sig tinyint(1) NOT NULL DEFAULT '1',
	post_username varchar(255) NOT NULL DEFAULT '',
	post_subject text(65535) NOT NULL DEFAULT '',
	post_text mediumtext(16777215) NOT NULL DEFAULT '',
	post_checksum varchar(32) NOT NULL DEFAULT '',
	post_encoding varchar(20) NOT NULL DEFAULT 'iso-8859-1',
	post_attachment tinyint(1) NOT NULL DEFAULT '0',
	bbcode_bitfield blob NOT NULL DEFAULT '',
	bbcode_uid varchar(5) NOT NULL DEFAULT '',
	post_postcount tinyint(1) NOT NULL DEFAULT '1',
	post_edit_time int(11) NOT NULL DEFAULT '0',
	post_edit_reason text(65535) NOT NULL DEFAULT '',
	post_edit_user mediumint(8) NOT NULL DEFAULT '0',
	post_edit_count mediumint(4) NOT NULL DEFAULT '0',
	post_edit_locked tinyint(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (post_id)
);;

CREATE INDEX phpbb_posts_forum_id ON phpbb_posts (forum_id);;
CREATE INDEX phpbb_posts_topic_id ON phpbb_posts (topic_id);;
CREATE INDEX phpbb_posts_poster_ip ON phpbb_posts (poster_ip);;
CREATE INDEX phpbb_posts_poster_id ON phpbb_posts (poster_id);;
CREATE INDEX phpbb_posts_post_approved ON phpbb_posts (post_approved);;
CREATE INDEX phpbb_posts_post_postcount ON phpbb_posts (post_postcount);;
CREATE INDEX phpbb_posts_post_time ON phpbb_posts (post_time);;

# Table: 'phpbb_privmsgs'
CREATE TABLE phpbb_privmsgs (
	msg_id INTEGER NOT NULL ,
	root_level mediumint(8) NOT NULL DEFAULT '0',
	author_id mediumint(8) NOT NULL DEFAULT '0',
	icon_id mediumint(8) NOT NULL DEFAULT '0',
	author_ip varchar(40) NOT NULL DEFAULT '',
	message_time int(11) NOT NULL DEFAULT '0',
	enable_bbcode tinyint(1) NOT NULL DEFAULT '1',
	enable_smilies tinyint(1) NOT NULL DEFAULT '1',
	enable_magic_url tinyint(1) NOT NULL DEFAULT '1',
	enable_sig tinyint(1) NOT NULL DEFAULT '1',
	message_subject text(65535) NOT NULL DEFAULT '',
	message_text mediumtext(16777215) NOT NULL DEFAULT '',
	message_edit_reason text(65535) NOT NULL DEFAULT '',
	message_edit_user mediumint(8) NOT NULL DEFAULT '0',
	message_encoding varchar(20) NOT NULL DEFAULT 'iso-8859-1',
	message_attachment tinyint(1) NOT NULL DEFAULT '0',
	bbcode_bitfield blob NOT NULL DEFAULT '',
	bbcode_uid varchar(5) NOT NULL DEFAULT '',
	message_edit_time int(11) NOT NULL DEFAULT '0',
	message_edit_count mediumint(4) NOT NULL DEFAULT '0',
	to_address text(65535) NOT NULL DEFAULT '',
	bcc_address text(65535) NOT NULL DEFAULT '',
	PRIMARY KEY (msg_id)
);;

CREATE INDEX phpbb_privmsgs_author_ip ON phpbb_privmsgs (author_ip);;
CREATE INDEX phpbb_privmsgs_message_time ON phpbb_privmsgs (message_time);;
CREATE INDEX phpbb_privmsgs_author_id ON phpbb_privmsgs (author_id);;
CREATE INDEX phpbb_privmsgs_root_level ON phpbb_privmsgs (root_level);;

# Table: 'phpbb_privmsgs_folder'
CREATE TABLE phpbb_privmsgs_folder (
	folder_id INTEGER NOT NULL ,
	user_id mediumint(8) NOT NULL DEFAULT '0',
	folder_name varchar(255) NOT NULL DEFAULT '',
	pm_count mediumint(8) NOT NULL DEFAULT '0',
	PRIMARY KEY (folder_id)
);;

CREATE INDEX phpbb_privmsgs_folder_user_id ON phpbb_privmsgs_folder (user_id);;

# Table: 'phpbb_privmsgs_rules'
CREATE TABLE phpbb_privmsgs_rules (
	rule_id INTEGER NOT NULL ,
	user_id mediumint(8) NOT NULL DEFAULT '0',
	rule_check mediumint(8) NOT NULL DEFAULT '0',
	rule_connection mediumint(8) NOT NULL DEFAULT '0',
	rule_string varchar(255) NOT NULL DEFAULT '',
	rule_user_id mediumint(8) NOT NULL DEFAULT '0',
	rule_group_id mediumint(8) NOT NULL DEFAULT '0',
	rule_action mediumint(8) NOT NULL DEFAULT '0',
	rule_folder_id int(4) NOT NULL DEFAULT '0',
	PRIMARY KEY (rule_id)
);;


# Table: 'phpbb_privmsgs_to'
CREATE TABLE phpbb_privmsgs_to (
	msg_id mediumint(8) NOT NULL DEFAULT '0',
	user_id mediumint(8) NOT NULL DEFAULT '0',
	author_id mediumint(8) NOT NULL DEFAULT '0',
	pm_deleted tinyint(1) NOT NULL DEFAULT '0',
	pm_new tinyint(1) NOT NULL DEFAULT '1',
	pm_unread tinyint(1) NOT NULL DEFAULT '1',
	pm_replied tinyint(1) NOT NULL DEFAULT '0',
	pm_marked tinyint(1) NOT NULL DEFAULT '0',
	pm_forwarded tinyint(1) NOT NULL DEFAULT '0',
	folder_id int(4) NOT NULL DEFAULT '0'
);;

CREATE INDEX phpbb_privmsgs_to_msg_id ON phpbb_privmsgs_to (msg_id);;
CREATE INDEX phpbb_privmsgs_to_user_folder_id ON phpbb_privmsgs_to (user_id, folder_id);;

# Table: 'phpbb_profile_fields'
CREATE TABLE phpbb_profile_fields (
	field_id INTEGER NOT NULL ,
	field_name varchar(255) NOT NULL DEFAULT '',
	field_type tinyint(4) NOT NULL DEFAULT '0',
	field_ident varchar(20) NOT NULL DEFAULT '',
	field_length varchar(20) NOT NULL DEFAULT '',
	field_minlen varchar(255) NOT NULL DEFAULT '',
	field_maxlen varchar(255) NOT NULL DEFAULT '',
	field_novalue varchar(255) NOT NULL DEFAULT '',
	field_default_value varchar(255) NOT NULL DEFAULT '',
	field_validation varchar(20) NOT NULL DEFAULT '',
	field_required tinyint(1) NOT NULL DEFAULT '0',
	field_show_on_reg tinyint(1) NOT NULL DEFAULT '0',
	field_hide tinyint(1) NOT NULL DEFAULT '0',
	field_no_view tinyint(1) NOT NULL DEFAULT '0',
	field_active tinyint(1) NOT NULL DEFAULT '0',
	field_order mediumint(8) NOT NULL DEFAULT '0',
	PRIMARY KEY (field_id)
);;

CREATE INDEX phpbb_profile_fields_field_type ON phpbb_profile_fields (field_type);;
CREATE INDEX phpbb_profile_fields_field_order ON phpbb_profile_fields (field_order);;

# Table: 'phpbb_profile_fields_data'
CREATE TABLE phpbb_profile_fields_data (
	user_id mediumint(8) NOT NULL DEFAULT '0',
	PRIMARY KEY (user_id)
);;


# Table: 'phpbb_profile_fields_lang'
CREATE TABLE phpbb_profile_fields_lang (
	field_id mediumint(8) NOT NULL DEFAULT '0',
	lang_id mediumint(8) NOT NULL DEFAULT '0',
	option_id mediumint(8) NOT NULL DEFAULT '0',
	field_type tinyint(4) NOT NULL DEFAULT '0',
	lang_value varchar(255) NOT NULL DEFAULT '',
	PRIMARY KEY (field_id, lang_id, option_id)
);;


# Table: 'phpbb_profile_lang'
CREATE TABLE phpbb_profile_lang (
	field_id mediumint(8) NOT NULL DEFAULT '0',
	lang_id mediumint(8) NOT NULL DEFAULT '0',
	lang_name varchar(255) NOT NULL DEFAULT '',
	lang_explain text(65535) NOT NULL DEFAULT '',
	lang_default_value varchar(255) NOT NULL DEFAULT '',
	PRIMARY KEY (field_id, lang_id)
);;


# Table: 'phpbb_ranks'
CREATE TABLE phpbb_ranks (
	rank_id INTEGER NOT NULL ,
	rank_title varchar(255) NOT NULL DEFAULT '',
	rank_min mediumint(8) NOT NULL DEFAULT '0',
	rank_special tinyint(1) NOT NULL DEFAULT '0',
	rank_image varchar(255) NOT NULL DEFAULT '',
	PRIMARY KEY (rank_id)
);;


# Table: 'phpbb_reports'
CREATE TABLE phpbb_reports (
	report_id INTEGER NOT NULL ,
	reason_id mediumint(4) NOT NULL DEFAULT '0',
	post_id mediumint(8) NOT NULL DEFAULT '0',
	user_id mediumint(8) NOT NULL DEFAULT '0',
	user_notify tinyint(1) NOT NULL DEFAULT '0',
	report_closed tinyint(1) NOT NULL DEFAULT '0',
	report_time int(11) NOT NULL DEFAULT '0',
	report_text mediumtext(16777215) NOT NULL DEFAULT '',
	PRIMARY KEY (report_id)
);;


# Table: 'phpbb_reports_reasons'
CREATE TABLE phpbb_reports_reasons (
	reason_id INTEGER NOT NULL ,
	reason_title varchar(255) NOT NULL DEFAULT '',
	reason_description mediumtext(16777215) NOT NULL DEFAULT '',
	reason_order mediumint(4) NOT NULL DEFAULT '0',
	PRIMARY KEY (reason_id)
);;


# Table: 'phpbb_search_results'
CREATE TABLE phpbb_search_results (
	search_key varchar(32) NOT NULL DEFAULT '',
	search_time int(11) NOT NULL DEFAULT '0',
	search_keywords mediumtext(16777215) NOT NULL DEFAULT '',
	search_authors mediumtext(16777215) NOT NULL DEFAULT '',
	PRIMARY KEY (search_key)
);;


# Table: 'phpbb_search_wordlist'
CREATE TABLE phpbb_search_wordlist (
	word_text varchar(252) NOT NULL DEFAULT '',
	word_id INTEGER NOT NULL ,
	word_common tinyint(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (word_id)
);;

CREATE INDEX phpbb_search_wordlist_word_text ON phpbb_search_wordlist (word_text);;

# Table: 'phpbb_search_wordmatch'
CREATE TABLE phpbb_search_wordmatch (
	post_id mediumint(8) NOT NULL DEFAULT '0',
	word_id mediumint(8) NOT NULL DEFAULT '0',
	title_match tinyint(1) NOT NULL DEFAULT '0'
);;

CREATE INDEX phpbb_search_wordmatch_word_id ON phpbb_search_wordmatch (word_id);;

# Table: 'phpbb_sessions'
CREATE TABLE phpbb_sessions (
	session_id char(32) NOT NULL DEFAULT '',
	session_user_id mediumint(8) NOT NULL DEFAULT '0',
	session_last_visit int(11) NOT NULL DEFAULT '0',
	session_start int(11) NOT NULL DEFAULT '0',
	session_time int(11) NOT NULL DEFAULT '0',
	session_ip varchar(40) NOT NULL DEFAULT '',
	session_browser varchar(150) NOT NULL DEFAULT '',
	session_page varchar(255) NOT NULL DEFAULT '',
	session_viewonline tinyint(1) NOT NULL DEFAULT '1',
	session_autologin tinyint(1) NOT NULL DEFAULT '0',
	session_admin tinyint(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (session_id)
);;

CREATE INDEX phpbb_sessions_session_time ON phpbb_sessions (session_time);;
CREATE INDEX phpbb_sessions_session_user_id ON phpbb_sessions (session_user_id);;

# Table: 'phpbb_sessions_keys'
CREATE TABLE phpbb_sessions_keys (
	key_id char(32) NOT NULL DEFAULT '',
	user_id mediumint(8) NOT NULL DEFAULT '0',
	last_ip varchar(40) NOT NULL DEFAULT '',
	last_login int(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (key_id, user_id)
);;

CREATE INDEX phpbb_sessions_keys_last_login ON phpbb_sessions_keys (last_login);;

# Table: 'phpbb_sitelist'
CREATE TABLE phpbb_sitelist (
	site_id INTEGER NOT NULL ,
	site_ip varchar(40) NOT NULL DEFAULT '',
	site_hostname varchar(255) NOT NULL DEFAULT '',
	ip_exclude tinyint(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (site_id)
);;


# Table: 'phpbb_smilies'
CREATE TABLE phpbb_smilies (
	smiley_id INTEGER NOT NULL ,
	code varchar(50) NOT NULL DEFAULT '',
	emotion varchar(50) NOT NULL DEFAULT '',
	smiley_url varchar(50) NOT NULL DEFAULT '',
	smiley_width tinyint(4) NOT NULL DEFAULT '0',
	smiley_height tinyint(4) NOT NULL DEFAULT '0',
	smiley_order mediumint(8) NOT NULL DEFAULT '0',
	display_on_posting tinyint(1) NOT NULL DEFAULT '1',
	PRIMARY KEY (smiley_id)
);;

CREATE INDEX phpbb_smilies_display_on_posting ON phpbb_smilies (display_on_posting);;

# Table: 'phpbb_styles'
CREATE TABLE phpbb_styles (
	style_id INTEGER NOT NULL ,
	style_name varchar(255) NOT NULL DEFAULT '',
	style_copyright varchar(255) NOT NULL DEFAULT '',
	style_active tinyint(1) NOT NULL DEFAULT '1',
	template_id tinyint(4) NOT NULL DEFAULT '0',
	theme_id tinyint(4) NOT NULL DEFAULT '0',
	imageset_id tinyint(4) NOT NULL DEFAULT '0',
	PRIMARY KEY (style_id)
);;

CREATE UNIQUE INDEX phpbb_styles_style_name ON phpbb_styles (style_name);;
CREATE INDEX phpbb_styles_template_id ON phpbb_styles (template_id);;
CREATE INDEX phpbb_styles_theme_id ON phpbb_styles (theme_id);;
CREATE INDEX phpbb_styles_imageset_id ON phpbb_styles (imageset_id);;

# Table: 'phpbb_styles_template'
CREATE TABLE phpbb_styles_template (
	template_id INTEGER NOT NULL ,
	template_name varchar(255) NOT NULL DEFAULT '',
	template_copyright varchar(255) NOT NULL DEFAULT '',
	template_path varchar(100) NOT NULL DEFAULT '',
	bbcode_bitfield blob NOT NULL DEFAULT '',
	template_storedb tinyint(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (template_id)
);;

CREATE UNIQUE INDEX phpbb_styles_template_template_name ON phpbb_styles_template (template_name);;

CREATE TRIGGER "t_phpbb_styles_template"
AFTER INSERT ON "phpbb_styles_template"
FOR EACH ROW WHEN NEW.bbcode_bitfield = ''
BEGIN
   UPDATE phpbb_styles_template SET bbcode_bitfield = binary_insert(1) WHERE template_id = NEW.template_id;
END;;

# Table: 'phpbb_styles_template_data'
CREATE TABLE phpbb_styles_template_data (
	template_id INTEGER NOT NULL ,
	template_filename varchar(100) NOT NULL DEFAULT '',
	template_included text(65535) NOT NULL DEFAULT '',
	template_mtime int(11) NOT NULL DEFAULT '0',
	template_data mediumtext(16777215) NOT NULL DEFAULT ''
);;

CREATE INDEX phpbb_styles_template_data_template_id ON phpbb_styles_template_data (template_id);;
CREATE INDEX phpbb_styles_template_data_template_filename ON phpbb_styles_template_data (template_filename);;

# Table: 'phpbb_styles_theme'
CREATE TABLE phpbb_styles_theme (
	theme_id INTEGER NOT NULL ,
	theme_name varchar(255) NOT NULL DEFAULT '',
	theme_copyright varchar(255) NOT NULL DEFAULT '',
	theme_path varchar(100) NOT NULL DEFAULT '',
	theme_storedb tinyint(1) NOT NULL DEFAULT '0',
	theme_mtime int(11) NOT NULL DEFAULT '0',
	theme_data mediumtext(16777215) NOT NULL DEFAULT '',
	PRIMARY KEY (theme_id)
);;

CREATE UNIQUE INDEX phpbb_styles_theme_theme_name ON phpbb_styles_theme (theme_name);;

# Table: 'phpbb_styles_imageset'
CREATE TABLE phpbb_styles_imageset (
	imageset_id INTEGER NOT NULL ,
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
	btn_warn varchar(200) NOT NULL DEFAULT '',
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
	folder_post varchar(200) NOT NULL DEFAULT '',
	folder_new varchar(200) NOT NULL DEFAULT '',
	folder_new_post varchar(200) NOT NULL DEFAULT '',
	folder_hot varchar(200) NOT NULL DEFAULT '',
	folder_hot_post varchar(200) NOT NULL DEFAULT '',
	folder_hot_new varchar(200) NOT NULL DEFAULT '',
	folder_hot_new_post varchar(200) NOT NULL DEFAULT '',
	folder_lock varchar(200) NOT NULL DEFAULT '',
	folder_lock_post varchar(200) NOT NULL DEFAULT '',
	folder_lock_new varchar(200) NOT NULL DEFAULT '',
	folder_lock_new_post varchar(200) NOT NULL DEFAULT '',
	folder_lock_announce varchar(200) NOT NULL DEFAULT '',
	folder_lock_announce_new varchar(200) NOT NULL DEFAULT '',
	folder_lock_announce_post varchar(200) NOT NULL DEFAULT '',
	folder_lock_announce_new_post varchar(200) NOT NULL DEFAULT '',
	folder_lock_global varchar(200) NOT NULL DEFAULT '',
	folder_lock_global_new varchar(200) NOT NULL DEFAULT '',
	folder_lock_global_post varchar(200) NOT NULL DEFAULT '',
	folder_lock_global_new_post varchar(200) NOT NULL DEFAULT '',
	folder_lock_sticky varchar(200) NOT NULL DEFAULT '',
	folder_lock_sticky_new varchar(200) NOT NULL DEFAULT '',
	folder_lock_sticky_post varchar(200) NOT NULL DEFAULT '',
	folder_lock_sticky_new_post varchar(200) NOT NULL DEFAULT '',
	folder_sticky varchar(200) NOT NULL DEFAULT '',
	folder_sticky_post varchar(200) NOT NULL DEFAULT '',
	folder_sticky_new varchar(200) NOT NULL DEFAULT '',
	folder_sticky_new_post varchar(200) NOT NULL DEFAULT '',
	folder_announce varchar(200) NOT NULL DEFAULT '',
	folder_announce_post varchar(200) NOT NULL DEFAULT '',
	folder_announce_new varchar(200) NOT NULL DEFAULT '',
	folder_announce_new_post varchar(200) NOT NULL DEFAULT '',
	folder_global varchar(200) NOT NULL DEFAULT '',
	folder_global_post varchar(200) NOT NULL DEFAULT '',
	folder_global_new varchar(200) NOT NULL DEFAULT '',
	folder_global_new_post varchar(200) NOT NULL DEFAULT '',
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
	user_icon10 varchar(200) NOT NULL DEFAULT '',
	PRIMARY KEY (imageset_id)
);;

CREATE UNIQUE INDEX phpbb_styles_imageset_imageset_name ON phpbb_styles_imageset (imageset_name);;

# Table: 'phpbb_topics'
CREATE TABLE phpbb_topics (
	topic_id INTEGER NOT NULL ,
	forum_id mediumint(8) NOT NULL DEFAULT '0',
	icon_id mediumint(8) NOT NULL DEFAULT '0',
	topic_attachment tinyint(1) NOT NULL DEFAULT '0',
	topic_approved tinyint(1) NOT NULL DEFAULT '1',
	topic_reported tinyint(1) NOT NULL DEFAULT '0',
	topic_title text(65535) NOT NULL DEFAULT '',
	topic_poster mediumint(8) NOT NULL DEFAULT '0',
	topic_time int(11) NOT NULL DEFAULT '0',
	topic_time_limit int(11) NOT NULL DEFAULT '0',
	topic_views mediumint(8) NOT NULL DEFAULT '0',
	topic_replies mediumint(8) NOT NULL DEFAULT '0',
	topic_replies_real mediumint(8) NOT NULL DEFAULT '0',
	topic_status tinyint(3) NOT NULL DEFAULT '0',
	topic_type tinyint(3) NOT NULL DEFAULT '0',
	topic_first_post_id mediumint(8) NOT NULL DEFAULT '0',
	topic_first_poster_name varchar(255) NOT NULL DEFAULT '',
	topic_last_post_id mediumint(8) NOT NULL DEFAULT '0',
	topic_last_poster_id mediumint(8) NOT NULL DEFAULT '0',
	topic_last_poster_name varchar(255) NOT NULL DEFAULT '',
	topic_last_post_time int(11) NOT NULL DEFAULT '0',
	topic_last_view_time int(11) NOT NULL DEFAULT '0',
	topic_moved_id mediumint(8) NOT NULL DEFAULT '0',
	topic_bumped tinyint(1) NOT NULL DEFAULT '0',
	topic_bumper mediumint(8) NOT NULL DEFAULT '0',
	poll_title text(65535) NOT NULL DEFAULT '',
	poll_start int(11) NOT NULL DEFAULT '0',
	poll_length int(11) NOT NULL DEFAULT '0',
	poll_max_options tinyint(4) NOT NULL DEFAULT '1',
	poll_last_vote int(11) NOT NULL DEFAULT '0',
	poll_vote_change tinyint(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (topic_id)
);;

CREATE INDEX phpbb_topics_forum_id ON phpbb_topics (forum_id);;
CREATE INDEX phpbb_topics_forum_id_type ON phpbb_topics (forum_id, topic_type);;
CREATE INDEX phpbb_topics_topic_last_post_time ON phpbb_topics (topic_last_post_time);;

# Table: 'phpbb_topics_track'
CREATE TABLE phpbb_topics_track (
	user_id mediumint(8) NOT NULL DEFAULT '0',
	topic_id mediumint(8) NOT NULL DEFAULT '0',
	forum_id mediumint(8) NOT NULL DEFAULT '0',
	mark_time int(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (user_id, topic_id)
);;

CREATE INDEX phpbb_topics_track_forum_id ON phpbb_topics_track (forum_id);;

# Table: 'phpbb_topics_posted'
CREATE TABLE phpbb_topics_posted (
	user_id mediumint(8) NOT NULL DEFAULT '0',
	topic_id mediumint(8) NOT NULL DEFAULT '0',
	topic_posted tinyint(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (user_id, topic_id)
);;


# Table: 'phpbb_topics_watch'
CREATE TABLE phpbb_topics_watch (
	topic_id mediumint(8) NOT NULL DEFAULT '0',
	user_id mediumint(8) NOT NULL DEFAULT '0',
	notify_status tinyint(1) NOT NULL DEFAULT '0'
);;

CREATE INDEX phpbb_topics_watch_topic_id ON phpbb_topics_watch (topic_id);;
CREATE INDEX phpbb_topics_watch_user_id ON phpbb_topics_watch (user_id);;
CREATE INDEX phpbb_topics_watch_notify_status ON phpbb_topics_watch (notify_status);;

# Table: 'phpbb_user_group'
CREATE TABLE phpbb_user_group (
	group_id mediumint(8) NOT NULL DEFAULT '0',
	user_id mediumint(8) NOT NULL DEFAULT '0',
	group_leader tinyint(1) NOT NULL DEFAULT '0',
	user_pending tinyint(1) NOT NULL DEFAULT '1'
);;

CREATE INDEX phpbb_user_group_group_id ON phpbb_user_group (group_id);;
CREATE INDEX phpbb_user_group_user_id ON phpbb_user_group (user_id);;
CREATE INDEX phpbb_user_group_group_leader ON phpbb_user_group (group_leader);;

# Table: 'phpbb_users'
CREATE TABLE phpbb_users (
	user_id INTEGER NOT NULL ,
	user_type tinyint(2) NOT NULL DEFAULT '0',
	group_id mediumint(8) NOT NULL DEFAULT '3',
	user_permissions mediumtext(16777215) NOT NULL DEFAULT '',
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
	user_last_search int(11) NOT NULL DEFAULT '0',
	user_warnings tinyint(4) NOT NULL DEFAULT '0',
	user_last_warning int(11) NOT NULL DEFAULT '0',
	user_login_attempts tinyint(4) NOT NULL DEFAULT '0',
	user_posts mediumint(8) NOT NULL DEFAULT '0',
	user_lang varchar(30) NOT NULL DEFAULT '',
	user_timezone decimal(5,2) NOT NULL DEFAULT '0',
	user_dst tinyint(1) NOT NULL DEFAULT '0',
	user_dateformat varchar(30) NOT NULL DEFAULT 'd M Y H:i',
	user_style tinyint(4) NOT NULL DEFAULT '0',
	user_rank mediumint(8) NOT NULL DEFAULT '0',
	user_colour varchar(6) NOT NULL DEFAULT '',
	user_new_privmsg tinyint(4) NOT NULL DEFAULT '0',
	user_unread_privmsg tinyint(4) NOT NULL DEFAULT '0',
	user_last_privmsg int(11) NOT NULL DEFAULT '0',
	user_message_rules tinyint(1) NOT NULL DEFAULT '0',
	user_full_folder int(11) NOT NULL DEFAULT '-3',
	user_emailtime int(11) NOT NULL DEFAULT '0',
	user_topic_show_days mediumint(4) NOT NULL DEFAULT '0',
	user_topic_sortby_type varchar(1) NOT NULL DEFAULT 't',
	user_topic_sortby_dir varchar(1) NOT NULL DEFAULT 'd',
	user_post_show_days mediumint(4) NOT NULL DEFAULT '0',
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
	user_sig mediumtext(16777215) NOT NULL DEFAULT '',
	user_sig_bbcode_uid varchar(5) NOT NULL DEFAULT '',
	user_sig_bbcode_bitfield blob NOT NULL DEFAULT '',
	user_from varchar(100) NOT NULL DEFAULT '',
	user_icq varchar(15) NOT NULL DEFAULT '',
	user_aim varchar(255) NOT NULL DEFAULT '',
	user_yim varchar(255) NOT NULL DEFAULT '',
	user_msnm varchar(255) NOT NULL DEFAULT '',
	user_jabber varchar(255) NOT NULL DEFAULT '',
	user_website varchar(200) NOT NULL DEFAULT '',
	user_occ varchar(255) NOT NULL DEFAULT '',
	user_interests text(65535) NOT NULL DEFAULT '',
	user_actkey varchar(32) NOT NULL DEFAULT '',
	user_newpasswd varchar(32) NOT NULL DEFAULT '',
	PRIMARY KEY (user_id)
);;

CREATE INDEX phpbb_users_user_birthday ON phpbb_users (user_birthday);;
CREATE INDEX phpbb_users_user_email_hash ON phpbb_users (user_email_hash);;
CREATE INDEX phpbb_users_user_type ON phpbb_users (user_type);;
CREATE INDEX phpbb_users_username ON phpbb_users (username);;

# Table: 'phpbb_warnings'
CREATE TABLE phpbb_warnings (
	warning_id INTEGER NOT NULL ,
	user_id mediumint(8) NOT NULL DEFAULT '0',
	post_id mediumint(8) NOT NULL DEFAULT '0',
	log_id mediumint(8) NOT NULL DEFAULT '0',
	warning_time int(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (warning_id)
);;


# Table: 'phpbb_words'
CREATE TABLE phpbb_words (
	word_id INTEGER NOT NULL ,
	word varchar(255) NOT NULL DEFAULT '',
	replacement varchar(255) NOT NULL DEFAULT '',
	PRIMARY KEY (word_id)
);;


# Table: 'phpbb_zebra'
CREATE TABLE phpbb_zebra (
	user_id mediumint(8) NOT NULL DEFAULT '0',
	zebra_id mediumint(8) NOT NULL DEFAULT '0',
	friend tinyint(1) NOT NULL DEFAULT '0',
	foe tinyint(1) NOT NULL DEFAULT '0'
);;

CREATE INDEX phpbb_zebra_user_id ON phpbb_zebra (user_id);;
CREATE INDEX phpbb_zebra_zebra_id ON phpbb_zebra (zebra_id);;


COMMIT;;