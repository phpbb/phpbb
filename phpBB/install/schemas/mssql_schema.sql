/*

  mssql_schema.sql for phpBB2 (c) 2001, phpBB Group 

 $Id$

*/

BEGIN TRANSACTION
GO


CREATE TABLE [phpbb_attachments] (
	[attach_id] [int] IDENTITY (1, 1) NOT NULL ,
	[post_msg_id] [int] NOT NULL ,
	[topic_id] [int] NOT NULL ,
	[in_message] [int] NOT NULL ,
	[poster_id] [int] NOT NULL ,
	[physical_filename] [varchar] (255) NOT NULL ,
	[real_filename] [varchar] (255) NOT NULL ,
	[download_count] [int] NOT NULL ,
	[comment] [varchar] (255) NULL ,
	[extension] [varchar] (100) NULL ,
	[mimetype] [varchar] (100) NULL ,
	[filesize] [int] NOT NULL ,
	[filetime] [int] NOT NULL ,
	[thumbnail] [int] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_auth_groups] (
	[group_id] [int] NOT NULL ,
	[forum_id] [int] NOT NULL ,
	[auth_option_id] [int] NOT NULL ,
	[auth_setting] [int] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_auth_options] (
	[auth_option_id] [int] IDENTITY (1, 1) NOT NULL ,
	[auth_option] [varchar] (20) NOT NULL ,
	[is_global] [int] NOT NULL ,
	[is_local] [int] NOT NULL ,
	[founder_only] [int] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_auth_presets] (
	[preset_id] [int] IDENTITY (1, 1) NOT NULL ,
	[preset_name] [varchar] (50) NOT NULL ,
	[preset_user_id] [int] NOT NULL ,
	[preset_type] [varchar] (2) NOT NULL ,
	[preset_data] [text] NOT NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [phpbb_auth_users] (
	[user_id] [int] NOT NULL ,
	[forum_id] [int] NOT NULL ,
	[auth_option_id] [int] NOT NULL ,
	[auth_setting] [int] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_banlist] (
	[ban_id] [int] IDENTITY (1, 1) NOT NULL ,
	[ban_userid] [int] NOT NULL ,
	[ban_ip] [varchar] (40) NOT NULL ,
	[ban_email] [varchar] (50) NOT NULL ,
	[ban_start] [int] NOT NULL ,
	[ban_end] [int] NOT NULL ,
	[ban_exclude] [int] NOT NULL ,
	[ban_reason] [varchar] (255) NOT NULL ,
	[ban_give_reason] [varchar] (255) NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_bbcodes] (
	[bbcode_id] [int] NOT NULL ,
	[bbcode_tag] [varchar] (16) NOT NULL ,
	[bbcode_match] [varchar] (255) NOT NULL ,
	[bbcode_tpl] [text] NOT NULL ,
	[first_pass_match] [varchar] (255) NOT NULL ,
	[first_pass_replace] [varchar] (255) NOT NULL ,
	[second_pass_match] [varchar] (255) NOT NULL ,
	[second_pass_replace] [text] NOT NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [phpbb_bookmarks] (
	[topic_id] [int] NOT NULL ,
	[user_id] [int] NOT NULL ,
	[order_id] [int] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_bots] (
	[bot_id] [int] IDENTITY (1, 1) NOT NULL ,
	[bot_active] [int] NOT NULL ,
	[bot_name] [varchar] (255) NOT NULL ,
	[user_id] [int] NOT NULL ,
	[bot_agent] [varchar] (255) NOT NULL ,
	[bot_ip] [varchar] (255) NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_cache] (
	[var_name] [varchar] (255) NOT NULL ,
	[var_expires] [int] NOT NULL ,
	[var_data] [text] NOT NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [phpbb_config] (
	[config_name] [varchar] (255) NOT NULL ,
	[config_value] [varchar] (255) NOT NULL ,
	[is_dynamic] [int] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_confirm] (
	[confirm_id] [varchar] (32) NOT NULL ,
	[session_id] [varchar] (32) NOT NULL ,
	[code] [varchar] (6) NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_disallow] (
	[disallow_id] [int] IDENTITY (1, 1) NOT NULL ,
	[disallow_username] [varchar] (30) NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_drafts] (
	[draft_id] [int] IDENTITY (1, 1) NOT NULL ,
	[user_id] [int] NOT NULL ,
	[topic_id] [int] NOT NULL ,
	[forum_id] [int] NOT NULL ,
	[save_time] [int] NOT NULL ,
	[draft_subject] [varchar] (60) NULL ,
	[draft_message] [text] NOT NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [phpbb_extension_groups] (
	[group_id] [int] IDENTITY (1, 1) NOT NULL ,
	[group_name] [varchar] (20) NOT NULL ,
	[cat_id] [int] NOT NULL ,
	[allow_group] [int] NOT NULL ,
	[download_mode] [int] NOT NULL ,
	[upload_icon] [varchar] (100) NOT NULL ,
	[max_filesize] [int] NOT NULL ,
	[allowed_forums] [text] NOT NULL ,
	[allow_in_pm] [int] NOT NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [phpbb_extensions] (
	[extension_id] [int] IDENTITY (1, 1) NOT NULL ,
	[group_id] [int] NOT NULL ,
	[extension] [varchar] (100) NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_forum_access] (
	[forum_id] [int] NOT NULL ,
	[user_id] [int] NOT NULL ,
	[session_id] [varchar] (32) NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_forums] (
	[forum_id] [int] IDENTITY (1, 1) NOT NULL ,
	[parent_id] [int] NOT NULL ,
	[left_id] [int] NOT NULL ,
	[right_id] [int] NOT NULL ,
	[forum_parents] [text] NULL ,
	[forum_name] [varchar] (150) NOT NULL ,
	[forum_desc] [text] NULL ,
	[forum_link] [varchar] (200) NOT NULL ,
	[forum_password] [varchar] (32) NOT NULL ,
	[forum_style] [int] NULL ,
	[forum_image] [varchar] (50) NOT NULL ,
	[forum_rules] [text] NOT NULL ,
	[forum_rules_link] [varchar] (200) NOT NULL ,
	[forum_rules_flags] [int] NOT NULL ,
	[forum_rules_bbcode_bitfield] [int] NOT NULL ,
	[forum_rules_bbcode_uid] [varchar] (5) NOT NULL ,
	[forum_topics_per_page] [int] NOT NULL ,
	[forum_type] [int] NOT NULL ,
	[forum_status] [int] NOT NULL ,
	[forum_posts] [int] NOT NULL ,
	[forum_topics] [int] NOT NULL ,
	[forum_topics_real] [int] NOT NULL ,
	[forum_last_post_id] [int] NOT NULL ,
	[forum_last_poster_id] [int] NOT NULL ,
	[forum_last_post_time] [int] NOT NULL ,
	[forum_last_poster_name] [varchar] (30) NULL ,
	[forum_flags] [int] NOT NULL ,
	[display_on_index] [int] NOT NULL ,
	[enable_indexing] [int] NOT NULL ,
	[enable_icons] [int] NOT NULL ,
	[enable_prune] [int] NOT NULL ,
	[prune_next] [int] NULL ,
	[prune_days] [int] NOT NULL ,
	[prune_viewed] [int] NOT NULL ,
	[prune_freq] [int] NOT NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [phpbb_forums_marking] (
	[user_id] [int] NOT NULL ,
	[forum_id] [int] NOT NULL ,
	[mark_time] [int] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_forums_watch] (
	[forum_id] [int] NOT NULL ,
	[user_id] [int] NOT NULL ,
	[notify_status] [int] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_groups] (
	[group_id] [int] IDENTITY (1, 1) NOT NULL ,
	[group_type] [int] NOT NULL ,
	[group_name] [varchar] (40) NOT NULL ,
	[group_display] [int] NOT NULL ,
	[group_avatar] [varchar] (100) NOT NULL ,
	[group_avatar_type] [int] NOT NULL ,
	[group_avatar_width] [int] NOT NULL ,
	[group_avatar_height] [int] NOT NULL ,
	[group_rank] [int] NOT NULL ,
	[group_colour] [varchar] (6) NOT NULL ,
	[group_sig_chars] [int] NOT NULL ,
	[group_receive_pm] [int] NOT NULL ,
	[group_message_limit] [int] NOT NULL ,
	[group_chgpass] [int] NOT NULL ,
	[group_description] [varchar] (255) NOT NULL ,
	[group_legend] [int] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_icons] (
	[icons_id] [int] IDENTITY (1, 1) NOT NULL ,
	[icons_url] [varchar] (50) NULL ,
	[icons_width] [int] NOT NULL ,
	[icons_height] [int] NOT NULL ,
	[icons_order] [int] NOT NULL ,
	[display_on_posting] [int] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_lang] (
	[lang_id] [int] IDENTITY (1, 1) NOT NULL ,
	[lang_iso] [varchar] (5) NOT NULL ,
	[lang_dir] [varchar] (30) NOT NULL ,
	[lang_english_name] [varchar] (30) NULL ,
	[lang_local_name] [varchar] (100) NULL ,
	[lang_author] [varchar] (100) NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_log] (
	[log_id] [int] IDENTITY (1, 1) NOT NULL ,
	[log_type] [int] NOT NULL ,
	[user_id] [int] NOT NULL ,
	[forum_id] [int] NOT NULL ,
	[topic_id] [int] NOT NULL ,
	[reportee_id] [int] NOT NULL ,
	[log_ip] [varchar] (40) NOT NULL ,
	[log_time] [int] NOT NULL ,
	[log_operation] [text] NULL ,
	[log_data] [text] NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [phpbb_moderator_cache] (
	[forum_id] [int] NOT NULL ,
	[user_id] [int] NOT NULL ,
	[username] [varchar] (30) NOT NULL ,
	[group_id] [int] NOT NULL ,
	[groupname] [varchar] (30) NOT NULL ,
	[display_on_index] [int] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_modules] (
	[module_id] [int] IDENTITY (1, 1) NOT NULL ,
	[module_type] [varchar] (3) NOT NULL ,
	[module_title] [varchar] (50) NOT NULL ,
	[module_filename] [varchar] (50) NOT NULL ,
	[module_order] [int] NOT NULL ,
	[module_enabled] [int] NOT NULL ,
	[module_subs] [text] NOT NULL ,
	[module_acl] [varchar] (255) NOT NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [phpbb_poll_results] (
	[poll_option_id] [int] NOT NULL ,
	[topic_id] [int] NOT NULL ,
	[poll_option_text] [varchar] (255) NOT NULL ,
	[poll_option_total] [int] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_poll_voters] (
	[topic_id] [int] NOT NULL ,
	[poll_option_id] [int] NOT NULL ,
	[vote_user_id] [int] NOT NULL ,
	[vote_user_ip] [varchar] (40) NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_posts] (
	[post_id] [int] IDENTITY (1, 1) NOT NULL ,
	[topic_id] [int] NOT NULL ,
	[forum_id] [int] NOT NULL ,
	[poster_id] [int] NOT NULL ,
	[icon_id] [int] NOT NULL ,
	[poster_ip] [varchar] (40) NOT NULL ,
	[post_time] [int] NOT NULL ,
	[post_approved] [int] NOT NULL ,
	[post_reported] [int] NOT NULL ,
	[enable_bbcode] [int] NOT NULL ,
	[enable_html] [int] NOT NULL ,
	[enable_smilies] [int] NOT NULL ,
	[enable_magic_url] [int] NOT NULL ,
	[enable_sig] [int] NOT NULL ,
	[post_username] [varchar] (30) NULL ,
	[post_subject] [varchar] (60) NULL ,
	[post_text] [text] NULL ,
	[post_checksum] [varchar] (32) NOT NULL ,
	[post_encoding] [varchar] (11) NOT NULL ,
	[post_attachment] [int] NOT NULL ,
	[bbcode_bitfield] [int] NOT NULL ,
	[bbcode_uid] [varchar] (5) NOT NULL ,
	[post_edit_time] [int] NOT NULL ,
	[post_edit_reason] [varchar] (100) NULL ,
	[post_edit_user] [int] NOT NULL ,
	[post_edit_count] [int] NOT NULL ,
	[post_edit_locked] [int] NOT NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [phpbb_privmsgs] (
	[msg_id] [int] IDENTITY (1, 1) NOT NULL ,
	[root_level] [int] NOT NULL ,
	[author_id] [int] NOT NULL ,
	[icon_id] [int] NOT NULL ,
	[author_ip] [varchar] (40) NOT NULL ,
	[message_time] [int] NOT NULL ,
	[message_reported] [int] NOT NULL ,
	[enable_bbcode] [int] NOT NULL ,
	[enable_html] [int] NOT NULL ,
	[enable_smilies] [int] NOT NULL ,
	[enable_magic_url] [int] NOT NULL ,
	[enable_sig] [int] NOT NULL ,
	[message_subject] [varchar] (60) NULL ,
	[message_text] [text] NULL ,
	[message_edit_reason] [varchar] (100) NULL ,
	[message_edit_user] [int] NOT NULL ,
	[message_checksum] [varchar] (32) NOT NULL ,
	[message_encoding] [varchar] (11) NOT NULL ,
	[message_attachment] [int] NOT NULL ,
	[bbcode_bitfield] [int] NOT NULL ,
	[bbcode_uid] [varchar] (5) NOT NULL ,
	[message_edit_time] [int] NOT NULL ,
	[message_edit_count] [int] NOT NULL ,
	[to_address] [text] NULL ,
	[bcc_address] [text] NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [phpbb_privmsgs_folder] (
	[folder_id] [int] IDENTITY (1, 1) NOT NULL ,
	[user_id] [int] NOT NULL ,
	[folder_name] [varchar] (40) NOT NULL ,
	[pm_count] [int] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_privmsgs_rules] (
	[rule_id] [int] IDENTITY (1, 1) NOT NULL ,
	[user_id] [int] NOT NULL ,
	[rule_check] [int] NOT NULL ,
	[rule_connection] [int] NOT NULL ,
	[rule_string] [varchar] (255) NOT NULL ,
	[rule_user_id] [int] NOT NULL ,
	[rule_group_id] [int] NOT NULL ,
	[rule_action] [int] NOT NULL ,
	[rule_folder_id] [int] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_privmsgs_to] (
	[msg_id] [int] NOT NULL ,
	[user_id] [int] NOT NULL ,
	[author_id] [int] NOT NULL ,
	[deleted] [int] NOT NULL ,
	[new] [int] NOT NULL ,
	[unread] [int] NOT NULL ,
	[replied] [int] NOT NULL ,
	[marked] [int] NOT NULL ,
	[forwarded] [int] NOT NULL ,
	[folder_id] [int] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_profile_fields] (
	[field_id] [int] IDENTITY (1, 1) NOT NULL ,
	[field_name] [varchar] (50) NOT NULL ,
	[field_desc] [varchar] (255) NOT NULL ,
	[field_type] [int] NOT NULL ,
	[field_ident] [varchar] (20) NOT NULL ,
	[field_length] [varchar] (20) NOT NULL ,
	[field_minlen] [varchar] (255) NOT NULL ,
	[field_maxlen] [varchar] (255) NOT NULL ,
	[field_novalue] [varchar] (255) NOT NULL ,
	[field_default_value] [varchar] (255) NOT NULL ,
	[field_validation] [varchar] (20) NOT NULL ,
	[field_required] [int] NOT NULL ,
	[field_show_on_reg] [int] NOT NULL ,
	[field_hide] [int] NOT NULL ,
	[field_active] [int] NOT NULL ,
	[field_order] [int] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_profile_fields_data] (
	[user_id] [int] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_profile_fields_lang] (
	[field_id] [int] NOT NULL ,
	[lang_id] [int] NOT NULL ,
	[option_id] [int] NOT NULL ,
	[field_type] [int] NOT NULL ,
	[value] [varchar] (255) NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_profile_lang] (
	[field_id] [int] NOT NULL ,
	[lang_id] [int] NOT NULL ,
	[lang_name] [varchar] (255) NOT NULL ,
	[lang_explain] [text] NOT NULL ,
	[lang_default_value] [varchar] (255) NOT NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [phpbb_ranks] (
	[rank_id] [int] IDENTITY (1, 1) NOT NULL ,
	[rank_title] [varchar] (50) NOT NULL ,
	[rank_min] [int] NOT NULL ,
	[rank_special] [int] NULL ,
	[rank_image] [varchar] (100) NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_ratings] (
	[post_id] [int] NOT NULL ,
	[user_id] [int] NOT NULL ,
	[rating] [int] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_reports] (
	[report_id] [int] IDENTITY (1, 1) NOT NULL ,
	[reason_id] [int] NOT NULL ,
	[post_id] [int] NOT NULL ,
	[msg_id] [int] NOT NULL ,
	[user_id] [int] NOT NULL ,
	[user_notify] [int] NOT NULL ,
	[report_time] [int] NOT NULL ,
	[report_text] [text] NOT NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [phpbb_reports_reasons] (
	[reason_id] [int] IDENTITY (1, 1) NOT NULL ,
	[reason_priority] [int] NOT NULL ,
	[reason_name] [varchar] (255) NOT NULL ,
	[reason_description] [text] NOT NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [phpbb_search_results] (
	[search_id] [int] NOT NULL ,
	[session_id] [varchar] (32) NOT NULL ,
	[search_time] [int] NOT NULL ,
	[search_array] [text] NOT NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [phpbb_search_wordlist] (
	[word_text] [varbinary] (50) NOT NULL ,
	[word_id] [int] IDENTITY (1, 1) NOT NULL ,
	[word_common] [int] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_search_wordmatch] (
	[post_id] [int] NOT NULL ,
	[word_id] [int] NOT NULL ,
	[title_match] [int] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_sessions] (
	[session_id] [varchar] (32) NOT NULL ,
	[session_user_id] [int] NOT NULL ,
	[session_last_visit] [int] NOT NULL ,
	[session_start] [int] NOT NULL ,
	[session_time] [int] NOT NULL ,
	[session_ip] [varchar] (40) NOT NULL ,
	[session_browser] [varchar] (100) NULL ,
	[session_page] [varchar] (100) NOT NULL ,
	[session_viewonline] [int] NOT NULL ,
	[session_admin] [int] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_sitelist] (
	[site_id] [int] IDENTITY (1, 1) NOT NULL ,
	[site_ip] [varchar] (40) NOT NULL ,
	[site_hostname] [varchar] (255) NOT NULL ,
	[ip_exclude] [int] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_smilies] (
	[smile_id] [int] IDENTITY (1, 1) NOT NULL ,
	[code] [varchar] (10) NULL ,
	[emoticon] [varchar] (50) NULL ,
	[smile_url] [varchar] (50) NULL ,
	[smile_width] [int] NOT NULL ,
	[smile_height] [int] NOT NULL ,
	[smile_order] [int] NOT NULL ,
	[display_on_posting] [int] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_styles] (
	[style_id] [int] IDENTITY (1, 1) NOT NULL ,
	[style_name] [varchar] (30) NOT NULL ,
	[style_copyright] [varchar] (50) NOT NULL ,
	[style_active] [int] NOT NULL ,
	[template_id] [int] NOT NULL ,
	[theme_id] [int] NOT NULL ,
	[imageset_id] [int] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_styles_imageset] (
	[imageset_id] [int] IDENTITY (1, 1) NOT NULL ,
	[imageset_name] [varchar] (30) NOT NULL ,
	[imageset_copyright] [varchar] (50) NOT NULL ,
	[imageset_path] [varchar] (30) NOT NULL ,
	[site_logo] [varchar] (200) NOT NULL ,
	[btn_post] [varchar] (200) NOT NULL ,
	[btn_post_pm] [varchar] (200) NOT NULL ,
	[btn_reply] [varchar] (200) NOT NULL ,
	[btn_reply_pm] [varchar] (200) NOT NULL ,
	[btn_locked] [varchar] (200) NOT NULL ,
	[btn_profile] [varchar] (200) NOT NULL ,
	[btn_pm] [varchar] (200) NOT NULL ,
	[btn_delete] [varchar] (200) NOT NULL ,
	[btn_info] [varchar] (200) NOT NULL ,
	[btn_quote] [varchar] (200) NOT NULL ,
	[btn_search] [varchar] (200) NOT NULL ,
	[btn_edit] [varchar] (200) NOT NULL ,
	[btn_report] [varchar] (200) NOT NULL ,
	[btn_email] [varchar] (200) NOT NULL ,
	[btn_www] [varchar] (200) NOT NULL ,
	[btn_icq] [varchar] (200) NOT NULL ,
	[btn_aim] [varchar] (200) NOT NULL ,
	[btn_yim] [varchar] (200) NOT NULL ,
	[btn_msnm] [varchar] (200) NOT NULL ,
	[btn_jabber] [varchar] (200) NOT NULL ,
	[btn_online] [varchar] (200) NOT NULL ,
	[btn_offline] [varchar] (200) NOT NULL ,
	[btn_friend] [varchar] (200) NOT NULL ,
	[btn_foe] [varchar] (200) NOT NULL ,
	[icon_unapproved] [varchar] (200) NOT NULL ,
	[icon_reported] [varchar] (200) NOT NULL ,
	[icon_attach] [varchar] (200) NOT NULL ,
	[icon_post] [varchar] (200) NOT NULL ,
	[icon_post_new] [varchar] (200) NOT NULL ,
	[icon_post_latest] [varchar] (200) NOT NULL ,
	[icon_post_newest] [varchar] (200) NOT NULL ,
	[forum] [varchar] (200) NOT NULL ,
	[forum_new] [varchar] (200) NOT NULL ,
	[forum_locked] [varchar] (200) NOT NULL ,
	[forum_link] [varchar] (200) NOT NULL ,
	[sub_forum] [varchar] (200) NOT NULL ,
	[sub_forum_new] [varchar] (200) NOT NULL ,
	[folder] [varchar] (200) NOT NULL ,
	[folder_moved] [varchar] (200) NOT NULL ,
	[folder_posted] [varchar] (200) NOT NULL ,
	[folder_new] [varchar] (200) NOT NULL ,
	[folder_new_posted] [varchar] (200) NOT NULL ,
	[folder_hot] [varchar] (200) NOT NULL ,
	[folder_hot_posted] [varchar] (200) NOT NULL ,
	[folder_hot_new] [varchar] (200) NOT NULL ,
	[folder_hot_new_posted] [varchar] (200) NOT NULL ,
	[folder_locked] [varchar] (200) NOT NULL ,
	[folder_locked_posted] [varchar] (200) NOT NULL ,
	[folder_locked_new] [varchar] (200) NOT NULL ,
	[folder_locked_new_posted] [varchar] (200) NOT NULL ,
	[folder_sticky] [varchar] (200) NOT NULL ,
	[folder_sticky_posted] [varchar] (200) NOT NULL ,
	[folder_sticky_new] [varchar] (200) NOT NULL ,
	[folder_sticky_new_posted] [varchar] (200) NOT NULL ,
	[folder_announce] [varchar] (200) NOT NULL ,
	[folder_announce_posted] [varchar] (200) NOT NULL ,
	[folder_announce_new] [varchar] (200) NOT NULL ,
	[folder_announce_new_posted] [varchar] (200) NOT NULL ,
	[folder_global] [varchar] (200) NOT NULL ,
	[folder_global_posted] [varchar] (200) NOT NULL ,
	[folder_global_new] [varchar] (200) NOT NULL ,
	[folder_global_new_posted] [varchar] (200) NOT NULL ,
	[poll_left] [varchar] (200) NOT NULL ,
	[poll_center] [varchar] (200) NOT NULL ,
	[poll_right] [varchar] (200) NOT NULL ,
	[attach_progress_bar] [varchar] (200) NOT NULL ,
	[user_icon1] [varchar] (200) NOT NULL ,
	[user_icon2] [varchar] (200) NOT NULL ,
	[user_icon3] [varchar] (200) NOT NULL ,
	[user_icon4] [varchar] (200) NOT NULL ,
	[user_icon5] [varchar] (200) NOT NULL ,
	[user_icon6] [varchar] (200) NOT NULL ,
	[user_icon7] [varchar] (200) NOT NULL ,
	[user_icon8] [varchar] (200) NOT NULL ,
	[user_icon9] [varchar] (200) NOT NULL ,
	[user_icon10] [varchar] (200) NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_styles_template] (
	[template_id] [int] IDENTITY (1, 1) NOT NULL ,
	[template_name] [varchar] (30) NOT NULL ,
	[template_copyright] [varchar] (50) NOT NULL ,
	[template_path] [varchar] (30) NOT NULL ,
	[bbcode_bitfield] [int] NOT NULL ,
	[template_storedb] [int] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_styles_template_data] (
	[template_id] [int] NOT NULL ,
	[template_filename] [varchar] (50) NOT NULL ,
	[template_included] [text] NOT NULL ,
	[template_mtime] [int] NOT NULL ,
	[template_data] [text] NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [phpbb_styles_theme] (
	[theme_id] [int] IDENTITY (1, 1) NOT NULL ,
	[theme_name] [varchar] (30) NOT NULL ,
	[theme_copyright] [varchar] (50) NOT NULL ,
	[theme_path] [varchar] (30) NOT NULL ,
	[theme_storedb] [int] NOT NULL ,
	[theme_mtime] [int] NOT NULL ,
	[theme_data] [text] NOT NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [phpbb_topics] (
	[topic_id] [int] IDENTITY (1, 1) NOT NULL ,
	[forum_id] [int] NOT NULL ,
	[icon_id] [int] NOT NULL ,
	[topic_attachment] [int] NOT NULL ,
	[topic_approved] [int] NOT NULL ,
	[topic_reported] [int] NOT NULL ,
	[topic_title] [varchar] (60) NOT NULL ,
	[topic_poster] [int] NOT NULL ,
	[topic_time] [int] NOT NULL ,
	[topic_time_limit] [int] NOT NULL ,
	[topic_views] [int] NOT NULL ,
	[topic_replies] [int] NOT NULL ,
	[topic_replies_real] [int] NOT NULL ,
	[topic_status] [int] NOT NULL ,
	[topic_type] [int] NOT NULL ,
	[topic_first_post_id] [int] NOT NULL ,
	[topic_first_poster_name] [varchar] (30) NULL ,
	[topic_last_post_id] [int] NOT NULL ,
	[topic_last_poster_id] [int] NOT NULL ,
	[topic_last_poster_name] [varchar] (30) NULL ,
	[topic_last_post_time] [int] NOT NULL ,
	[topic_last_view_time] [int] NOT NULL ,
	[topic_moved_id] [int] NOT NULL ,
	[topic_bumped] [int] NOT NULL ,
	[topic_bumper] [int] NOT NULL ,
	[poll_title] [varchar] (255) NOT NULL ,
	[poll_start] [int] NOT NULL ,
	[poll_length] [int] NOT NULL ,
	[poll_max_options] [int] NOT NULL ,
	[poll_last_vote] [int] NULL ,
	[poll_vote_change] [int] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_topics_marking] (
	[user_id] [int] NOT NULL ,
	[topic_id] [int] NOT NULL ,
	[mark_type] [int] NOT NULL ,
	[mark_time] [int] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_topics_watch] (
	[topic_id] [int] NOT NULL ,
	[user_id] [int] NOT NULL ,
	[notify_status] [int] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_user_group] (
	[group_id] [int] NOT NULL ,
	[user_id] [int] NOT NULL ,
	[group_leader] [int] NOT NULL ,
	[user_pending] [int] NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_users] (
	[user_id] [int] IDENTITY (1, 1) NOT NULL ,
	[user_type] [int] NOT NULL ,
	[group_id] [int] NOT NULL ,
	[user_permissions] [text] NOT NULL ,
	[user_ip] [varchar] (40) NOT NULL ,
	[user_regdate] [int] NOT NULL ,
	[username] [varchar] (30) NOT NULL ,
	[user_password] [varchar] (32) NOT NULL ,
	[user_passchg] [int] NOT NULL ,
	[user_email] [varchar] (60) NOT NULL ,
	[user_email_hash] [float] NOT NULL ,
	[user_birthday] [varchar] (10) NOT NULL ,
	[user_lastvisit] [int] NOT NULL ,
	[user_lastpost_time] [int] NOT NULL ,
	[user_lastpage] [varchar] (100) NOT NULL ,
	[user_last_confirm_key] [varchar] (10) NOT NULL ,
	[user_warnings] [int] NOT NULL ,
	[user_posts] [int] NOT NULL ,
	[user_lang] [varchar] (30) NOT NULL ,
	[user_timezone] [float] NOT NULL ,
	[user_dst] [int] NOT NULL ,
	[user_dateformat] [varchar] (15) NOT NULL ,
	[user_style] [int] NOT NULL ,
	[user_rank] [int] NULL ,
	[user_colour] [varchar] (6) NOT NULL ,
	[user_new_privmsg] [int] NOT NULL ,
	[user_unread_privmsg] [int] NOT NULL ,
	[user_last_privmsg] [int] NOT NULL ,
	[user_message_rules] [int] NOT NULL ,
	[user_full_folder] [int] NOT NULL ,
	[user_emailtime] [int] NOT NULL ,
	[user_sortby_type] [varchar] (1) NOT NULL ,
	[user_sortby_dir] [varchar] (1) NOT NULL ,
	[user_show_days] [int] NOT NULL ,
	[user_notify] [int] NOT NULL ,
	[user_notify_pm] [int] NOT NULL ,
	[user_notify_type] [int] NOT NULL ,
	[user_allow_pm] [int] NOT NULL ,
	[user_allow_email] [int] NOT NULL ,
	[user_allow_viewonline] [int] NOT NULL ,
	[user_allow_viewemail] [int] NOT NULL ,
	[user_allow_massemail] [int] NOT NULL ,
	[user_options] [int] NOT NULL ,
	[user_avatar] [varchar] (100) NOT NULL ,
	[user_avatar_type] [int] NOT NULL ,
	[user_avatar_width] [int] NOT NULL ,
	[user_avatar_height] [int] NOT NULL ,
	[user_sig] [text] NOT NULL ,
	[user_sig_bbcode_uid] [varchar] (5) NOT NULL ,
	[user_sig_bbcode_bitfield] [int] NOT NULL ,
	[user_from] [varchar] (100) NOT NULL ,
	[user_icq] [varchar] (15) NOT NULL ,
	[user_aim] [varchar] (255) NOT NULL ,
	[user_yim] [varchar] (255) NOT NULL ,
	[user_msnm] [varchar] (255) NOT NULL ,
	[user_jabber] [varchar] (255) NOT NULL ,
	[user_website] [varchar] (100) NOT NULL ,
	[user_occ] [varchar] (255) NOT NULL ,
	[user_interests] [varchar] (255) NOT NULL ,
	[user_actkey] [varchar] (32) NOT NULL ,
	[user_newpasswd] [varchar] (32) NOT NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [phpbb_words] (
	[word_id] [int] IDENTITY (1, 1) NOT NULL ,
	[word] [varchar] (100) NOT NULL ,
	[replacement] [varchar] (100) NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_zebra] (
	[user_id] [int] NOT NULL ,
	[zebra_id] [int] NOT NULL ,
	[friend] [int] NOT NULL ,
	[foe] [int] NOT NULL 
) ON [PRIMARY]
GO

ALTER TABLE [phpbb_attachments] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_attachments] PRIMARY KEY  CLUSTERED 
	(
		[attach_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_auth_options] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_auth_options] PRIMARY KEY  CLUSTERED 
	(
		[auth_option_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_auth_presets] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_auth_presets] PRIMARY KEY  CLUSTERED 
	(
		[preset_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_banlist] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_banlist] PRIMARY KEY  CLUSTERED 
	(
		[ban_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_bbcodes] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_bbcodes] PRIMARY KEY  CLUSTERED 
	(
		[bbcode_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_bots] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_bots] PRIMARY KEY  CLUSTERED 
	(
		[bot_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_cache] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_cache] PRIMARY KEY  CLUSTERED 
	(
		[var_name]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_config] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_config] PRIMARY KEY  CLUSTERED 
	(
		[config_name]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_confirm] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_confirm] PRIMARY KEY  CLUSTERED 
	(
		[session_id],
		[confirm_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_disallow] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_disallow] PRIMARY KEY  CLUSTERED 
	(
		[disallow_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_drafts] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_drafts] PRIMARY KEY  CLUSTERED 
	(
		[draft_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_extension_groups] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_extension_groups] PRIMARY KEY  CLUSTERED 
	(
		[group_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_extensions] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_extensions] PRIMARY KEY  CLUSTERED 
	(
		[extension_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_forum_access] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_forum_access] PRIMARY KEY  CLUSTERED 
	(
		[forum_id],
		[user_id],
		[session_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_forums] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_forums] PRIMARY KEY  CLUSTERED 
	(
		[forum_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_forums_marking] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_forums_marking] PRIMARY KEY  CLUSTERED 
	(
		[user_id],
		[forum_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_groups] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_groups] PRIMARY KEY  CLUSTERED 
	(
		[group_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_icons] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_icons] PRIMARY KEY  CLUSTERED 
	(
		[icons_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_lang] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_lang] PRIMARY KEY  CLUSTERED 
	(
		[lang_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_log] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_log] PRIMARY KEY  CLUSTERED 
	(
		[log_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_modules] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_modules] PRIMARY KEY  CLUSTERED 
	(
		[module_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_posts] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_posts] PRIMARY KEY  CLUSTERED 
	(
		[post_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_privmsgs] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_privmsgs] PRIMARY KEY  CLUSTERED 
	(
		[msg_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_privmsgs_folder] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_privmsgs_folder] PRIMARY KEY  CLUSTERED 
	(
		[folder_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_privmsgs_rules] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_privmsgs_rules] PRIMARY KEY  CLUSTERED 
	(
		[rule_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_profile_fields] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_profile_fields] PRIMARY KEY  CLUSTERED 
	(
		[field_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_profile_fields_data] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_profile_fields_data] PRIMARY KEY  CLUSTERED 
	(
		[user_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_profile_fields_lang] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_profile_fields_lang] PRIMARY KEY  CLUSTERED 
	(
		[field_id],
		[lang_id],
		[option_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_profile_lang] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_profile_lang] PRIMARY KEY  CLUSTERED 
	(
		[field_id],
		[lang_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_ranks] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_ranks] PRIMARY KEY  CLUSTERED 
	(
		[rank_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_reports] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_reports] PRIMARY KEY  CLUSTERED 
	(
		[report_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_reports_reasons] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_reports_reasons] PRIMARY KEY  CLUSTERED 
	(
		[reason_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_search_results] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_search_results] PRIMARY KEY  CLUSTERED 
	(
		[search_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_search_wordlist] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_search_wordlist] PRIMARY KEY  CLUSTERED 
	(
		[word_text]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_sessions] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_sessions] PRIMARY KEY  CLUSTERED 
	(
		[session_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_sitelist] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_sitelist] PRIMARY KEY  CLUSTERED 
	(
		[site_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_smilies] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_smilies] PRIMARY KEY  CLUSTERED 
	(
		[smile_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_styles] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_styles] PRIMARY KEY  CLUSTERED 
	(
		[style_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_styles_imageset] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_styles_imageset] PRIMARY KEY  CLUSTERED 
	(
		[imageset_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_styles_template] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_styles_template] PRIMARY KEY  CLUSTERED 
	(
		[template_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_styles_theme] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_styles_theme] PRIMARY KEY  CLUSTERED 
	(
		[theme_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_topics] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_topics] PRIMARY KEY  CLUSTERED 
	(
		[topic_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_topics_marking] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_topics_marking] PRIMARY KEY  CLUSTERED 
	(
		[user_id],
		[topic_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_users] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_users] PRIMARY KEY  CLUSTERED 
	(
		[user_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_words] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_words] PRIMARY KEY  CLUSTERED 
	(
		[word_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_attachments] WITH NOCHECK ADD 
	CONSTRAINT [DF_attach_post_msg_id] DEFAULT (0) FOR [post_msg_id],
	CONSTRAINT [DF_attach_topic_id] DEFAULT (0) FOR [topic_id],
	CONSTRAINT [DF_attach_in_message] DEFAULT (0) FOR [in_message],
	CONSTRAINT [DF_attach_poster_id] DEFAULT (0) FOR [poster_id],
	CONSTRAINT [DF_attach_download_count] DEFAULT (0) FOR [download_count],
	CONSTRAINT [DF_attach_filesize] DEFAULT (0) FOR [filesize],
	CONSTRAINT [DF_attach_filetime] DEFAULT (0) FOR [filetime],
	CONSTRAINT [DF_attach_thumbnail] DEFAULT (0) FOR [thumbnail]
GO

ALTER TABLE [phpbb_auth_groups] WITH NOCHECK ADD 
	CONSTRAINT [DF_auth_g_group_id] DEFAULT (0) FOR [group_id],
	CONSTRAINT [DF_auth_g_forum_id] DEFAULT (0) FOR [forum_id],
	CONSTRAINT [DF_auth_g_auth_option_id] DEFAULT (0) FOR [auth_option_id],
	CONSTRAINT [DF_auth_g_auth_setting] DEFAULT (0) FOR [auth_setting]
GO

ALTER TABLE [phpbb_auth_options] WITH NOCHECK ADD 
	CONSTRAINT [DF_auth_o_is_global] DEFAULT (0) FOR [is_global],
	CONSTRAINT [DF_auth_o_is_local] DEFAULT (0) FOR [is_local],
	CONSTRAINT [DF_auth_o_founder_only] DEFAULT (0) FOR [founder_only]
GO

ALTER TABLE [phpbb_auth_presets] WITH NOCHECK ADD 
	CONSTRAINT [DF_auth_p_preset_user_id] DEFAULT (0) FOR [preset_user_id]
GO

ALTER TABLE [phpbb_auth_users] WITH NOCHECK ADD 
	CONSTRAINT [DF_auth_u_user_id] DEFAULT (0) FOR [user_id],
	CONSTRAINT [DF_auth_u_forum_id] DEFAULT (0) FOR [forum_id],
	CONSTRAINT [DF_auth_u_auth_option_id] DEFAULT (0) FOR [auth_option_id],
	CONSTRAINT [DF_auth_u_auth_setting] DEFAULT (0) FOR [auth_setting]
GO

ALTER TABLE [phpbb_banlist] WITH NOCHECK ADD 
	CONSTRAINT [DF_banlis_ban_userid] DEFAULT (0) FOR [ban_userid],
	CONSTRAINT [DF_banlis_ban_start] DEFAULT (0) FOR [ban_start],
	CONSTRAINT [DF_banlis_ban_end] DEFAULT (0) FOR [ban_end],
	CONSTRAINT [DF_banlis_ban_exclude] DEFAULT (0) FOR [ban_exclude]
GO

ALTER TABLE [phpbb_bbcodes] WITH NOCHECK ADD 
	CONSTRAINT [DF_bbcode_bbcode_id] DEFAULT (0) FOR [bbcode_id]
GO

ALTER TABLE [phpbb_bookmarks] WITH NOCHECK ADD 
	CONSTRAINT [DF_bookma_topic_id] DEFAULT (0) FOR [topic_id],
	CONSTRAINT [DF_bookma_user_id] DEFAULT (0) FOR [user_id],
	CONSTRAINT [DF_bookma_order_id] DEFAULT (0) FOR [order_id]
GO

ALTER TABLE [phpbb_bots] WITH NOCHECK ADD 
	CONSTRAINT [DF_bots___bot_active] DEFAULT (1) FOR [bot_active],
	CONSTRAINT [DF_bots___user_id] DEFAULT (0) FOR [user_id]
GO

ALTER TABLE [phpbb_cache] WITH NOCHECK ADD 
	CONSTRAINT [DF_cache__var_expires] DEFAULT (0) FOR [var_expires]
GO

ALTER TABLE [phpbb_config] WITH NOCHECK ADD 
	CONSTRAINT [DF_config_is_dynamic] DEFAULT (0) FOR [is_dynamic]
GO

ALTER TABLE [phpbb_drafts] WITH NOCHECK ADD 
	CONSTRAINT [DF_drafts_user_id] DEFAULT (0) FOR [user_id],
	CONSTRAINT [DF_drafts_topic_id] DEFAULT (0) FOR [topic_id],
	CONSTRAINT [DF_drafts_forum_id] DEFAULT (0) FOR [forum_id],
	CONSTRAINT [DF_drafts_save_time] DEFAULT (0) FOR [save_time]
GO

ALTER TABLE [phpbb_extension_groups] WITH NOCHECK ADD 
	CONSTRAINT [DF_extens_cat_id] DEFAULT (0) FOR [cat_id],
	CONSTRAINT [DF_extens_allow_group] DEFAULT (0) FOR [allow_group],
	CONSTRAINT [DF_extens_download_mode] DEFAULT (1) FOR [download_mode],
	CONSTRAINT [DF_extens_max_filesize] DEFAULT (0) FOR [max_filesize],
	CONSTRAINT [DF_extens_allow_in_pm] DEFAULT (0) FOR [allow_in_pm]
GO

ALTER TABLE [phpbb_extensions] WITH NOCHECK ADD 
	CONSTRAINT [DF_extens_group_id] DEFAULT (0) FOR [group_id]
GO

ALTER TABLE [phpbb_forum_access] WITH NOCHECK ADD 
	CONSTRAINT [DF_forum__forum_id] DEFAULT (0) FOR [forum_id],
	CONSTRAINT [DF_forum__user_id] DEFAULT (0) FOR [user_id]
GO

ALTER TABLE [phpbb_forums] WITH NOCHECK ADD 
	CONSTRAINT [DF_forums_parent_id] DEFAULT (0) FOR [parent_id],
	CONSTRAINT [DF_forums_left_id] DEFAULT (0) FOR [left_id],
	CONSTRAINT [DF_forums_right_id] DEFAULT (0) FOR [right_id],
	CONSTRAINT [DF_forums_rules_flags] DEFAULT (0) FOR [forum_rules_flags],
	CONSTRAINT [DF_forums_rules_bbcode_bitfiel] DEFAULT (0) FOR [forum_rules_bbcode_bitfield],
	CONSTRAINT [DF_forums_topics_per_page] DEFAULT (0) FOR [forum_topics_per_page],
	CONSTRAINT [DF_forums_forum_type] DEFAULT (0) FOR [forum_type],
	CONSTRAINT [DF_forums_forum_status] DEFAULT (0) FOR [forum_status],
	CONSTRAINT [DF_forums_forum_posts] DEFAULT (0) FOR [forum_posts],
	CONSTRAINT [DF_forums_forum_topics] DEFAULT (0) FOR [forum_topics],
	CONSTRAINT [DF_forums_forum_topics_real] DEFAULT (0) FOR [forum_topics_real],
	CONSTRAINT [DF_forums_forum_last_post_id] DEFAULT (0) FOR [forum_last_post_id],
	CONSTRAINT [DF_forums_forum_last_poster_id] DEFAULT (0) FOR [forum_last_poster_id],
	CONSTRAINT [DF_forums_forum_last_post_time] DEFAULT (0) FOR [forum_last_post_time],
	CONSTRAINT [DF_forums_forum_flags] DEFAULT (0) FOR [forum_flags],
	CONSTRAINT [DF_forums_display_on_index] DEFAULT (1) FOR [display_on_index],
	CONSTRAINT [DF_forums_enable_indexing] DEFAULT (1) FOR [enable_indexing],
	CONSTRAINT [DF_forums_enable_icons] DEFAULT (1) FOR [enable_icons],
	CONSTRAINT [DF_forums_enable_prune] DEFAULT (0) FOR [enable_prune],
	CONSTRAINT [DF_forums_prune_days] DEFAULT (0) FOR [prune_days],
	CONSTRAINT [DF_forums_prune_viewed] DEFAULT (0) FOR [prune_viewed],
	CONSTRAINT [DF_forums_prune_freq] DEFAULT (0) FOR [prune_freq]
GO

ALTER TABLE [phpbb_forums_marking] WITH NOCHECK ADD 
	CONSTRAINT [DF_forumm_user_id] DEFAULT (0) FOR [user_id],
	CONSTRAINT [DF_forumm_forum_id] DEFAULT (0) FOR [forum_id],
	CONSTRAINT [DF_forumm_mark_time] DEFAULT (0) FOR [mark_time]
GO

ALTER TABLE [phpbb_forums_watch] WITH NOCHECK ADD 
	CONSTRAINT [DF_forumw_forum_id] DEFAULT (0) FOR [forum_id],
	CONSTRAINT [DF_forumw_user_id] DEFAULT (0) FOR [user_id],
	CONSTRAINT [DF_forumw_notify_status] DEFAULT (0) FOR [notify_status]
GO

ALTER TABLE [phpbb_groups] WITH NOCHECK ADD 
	CONSTRAINT [DF_groups_group_type] DEFAULT (1) FOR [group_type],
	CONSTRAINT [DF_groups_group_display] DEFAULT (0) FOR [group_display],
	CONSTRAINT [DF_groups_group_avatar_type] DEFAULT (0) FOR [group_avatar_type],
	CONSTRAINT [DF_groups_group_avatar_width] DEFAULT (0) FOR [group_avatar_width],
	CONSTRAINT [DF_groups_group_avatar_height] DEFAULT (0) FOR [group_avatar_height],
	CONSTRAINT [DF_groups_group_rank] DEFAULT ((-1)) FOR [group_rank],
	CONSTRAINT [DF_groups_group_sig_chars] DEFAULT (0) FOR [group_sig_chars],
	CONSTRAINT [DF_groups_group_receive_pm] DEFAULT (0) FOR [group_receive_pm],
	CONSTRAINT [DF_groups_group_message_limit] DEFAULT (0) FOR [group_message_limit],
	CONSTRAINT [DF_groups_group_chgpass] DEFAULT (0) FOR [group_chgpass],
	CONSTRAINT [DF_groups_group_legend] DEFAULT (1) FOR [group_legend]
GO

ALTER TABLE [phpbb_icons] WITH NOCHECK ADD 
	CONSTRAINT [DF_icons__icons_width] DEFAULT (0) FOR [icons_width],
	CONSTRAINT [DF_icons__icons_height] DEFAULT (0) FOR [icons_height],
	CONSTRAINT [DF_icons__icons_order] DEFAULT (0) FOR [icons_order],
	CONSTRAINT [DF_icons__display_on_posting] DEFAULT (1) FOR [display_on_posting]
GO

ALTER TABLE [phpbb_log] WITH NOCHECK ADD 
	CONSTRAINT [DF_log____log_type] DEFAULT (0) FOR [log_type],
	CONSTRAINT [DF_log____user_id] DEFAULT (0) FOR [user_id],
	CONSTRAINT [DF_log____forum_id] DEFAULT (0) FOR [forum_id],
	CONSTRAINT [DF_log____topic_id] DEFAULT (0) FOR [topic_id],
	CONSTRAINT [DF_log____reportee_id] DEFAULT (0) FOR [reportee_id],
	CONSTRAINT [DF_log____log_time] DEFAULT (0) FOR [log_time]
GO

ALTER TABLE [phpbb_moderator_cache] WITH NOCHECK ADD 
	CONSTRAINT [DF_modera_forum_id] DEFAULT (0) FOR [forum_id],
	CONSTRAINT [DF_modera_user_id] DEFAULT (0) FOR [user_id],
	CONSTRAINT [DF_modera_group_id] DEFAULT (0) FOR [group_id],
	CONSTRAINT [DF_modera_display_on_index] DEFAULT (1) FOR [display_on_index]
GO

ALTER TABLE [phpbb_modules] WITH NOCHECK ADD 
	CONSTRAINT [DF_module_module_order] DEFAULT (0) FOR [module_order],
	CONSTRAINT [DF_module_module_enabled] DEFAULT (1) FOR [module_enabled]
GO

ALTER TABLE [phpbb_poll_results] WITH NOCHECK ADD 
	CONSTRAINT [DF_poll_r_poll_option_id] DEFAULT (0) FOR [poll_option_id],
	CONSTRAINT [DF_poll_r_topic_id] DEFAULT (0) FOR [topic_id],
	CONSTRAINT [DF_poll_r_poll_option_total] DEFAULT (0) FOR [poll_option_total]
GO

ALTER TABLE [phpbb_poll_voters] WITH NOCHECK ADD 
	CONSTRAINT [DF_poll_v_topic_id] DEFAULT (0) FOR [topic_id],
	CONSTRAINT [DF_poll_v_poll_option_id] DEFAULT (0) FOR [poll_option_id],
	CONSTRAINT [DF_poll_v_vote_user_id] DEFAULT (0) FOR [vote_user_id]
GO

ALTER TABLE [phpbb_posts] WITH NOCHECK ADD 
	CONSTRAINT [DF_posts__topic_id] DEFAULT (0) FOR [topic_id],
	CONSTRAINT [DF_posts__forum_id] DEFAULT (0) FOR [forum_id],
	CONSTRAINT [DF_posts__poster_id] DEFAULT (0) FOR [poster_id],
	CONSTRAINT [DF_posts__icon_id] DEFAULT (1) FOR [icon_id],
	CONSTRAINT [DF_posts__post_time] DEFAULT (0) FOR [post_time],
	CONSTRAINT [DF_posts__post_approved] DEFAULT (1) FOR [post_approved],
	CONSTRAINT [DF_posts__post_reported] DEFAULT (0) FOR [post_reported],
	CONSTRAINT [DF_posts__enable_bbcode] DEFAULT (1) FOR [enable_bbcode],
	CONSTRAINT [DF_posts__enable_html] DEFAULT (0) FOR [enable_html],
	CONSTRAINT [DF_posts__enable_smilies] DEFAULT (1) FOR [enable_smilies],
	CONSTRAINT [DF_posts__enable_magic_url] DEFAULT (1) FOR [enable_magic_url],
	CONSTRAINT [DF_posts__enable_sig] DEFAULT (1) FOR [enable_sig],
	CONSTRAINT [DF_posts__post_encoding] DEFAULT ('iso-8859-15') FOR [post_encoding],
	CONSTRAINT [DF_posts__post_attachment] DEFAULT (0) FOR [post_attachment],
	CONSTRAINT [DF_posts__bbcode_bitfield] DEFAULT (0) FOR [bbcode_bitfield],
	CONSTRAINT [DF_posts__post_edit_time] DEFAULT (0) FOR [post_edit_time],
	CONSTRAINT [DF_posts__post_edit_user] DEFAULT (0) FOR [post_edit_user],
	CONSTRAINT [DF_posts__post_edit_count] DEFAULT (0) FOR [post_edit_count],
	CONSTRAINT [DF_posts__post_edit_locked] DEFAULT (0) FOR [post_edit_locked]
GO

ALTER TABLE [phpbb_privmsgs] WITH NOCHECK ADD 
	CONSTRAINT [DF_privms_root_level] DEFAULT (0) FOR [root_level],
	CONSTRAINT [DF_privms_author_id] DEFAULT (0) FOR [author_id],
	CONSTRAINT [DF_privms_icon_id] DEFAULT (1) FOR [icon_id],
	CONSTRAINT [DF_privms_message_time] DEFAULT (0) FOR [message_time],
	CONSTRAINT [DF_privms_message_reported] DEFAULT (0) FOR [message_reported],
	CONSTRAINT [DF_privms_enable_bbcode] DEFAULT (1) FOR [enable_bbcode],
	CONSTRAINT [DF_privms_enable_html] DEFAULT (0) FOR [enable_html],
	CONSTRAINT [DF_privms_enable_smilies] DEFAULT (1) FOR [enable_smilies],
	CONSTRAINT [DF_privms_enable_magic_url] DEFAULT (1) FOR [enable_magic_url],
	CONSTRAINT [DF_privms_enable_sig] DEFAULT (1) FOR [enable_sig],
	CONSTRAINT [DF_privms_message_edit_user] DEFAULT (0) FOR [message_edit_user],
	CONSTRAINT [DF_privms_message_encoding] DEFAULT ('iso-8859-15') FOR [message_encoding],
	CONSTRAINT [DF_privms_message_attachment] DEFAULT (0) FOR [message_attachment],
	CONSTRAINT [DF_privms_bbcode_bitfield] DEFAULT (0) FOR [bbcode_bitfield],
	CONSTRAINT [DF_privms_message_edit_time] DEFAULT (0) FOR [message_edit_time],
	CONSTRAINT [DF_privms_message_edit_count] DEFAULT (0) FOR [message_edit_count]
GO

ALTER TABLE [phpbb_privmsgs_folder] WITH NOCHECK ADD 
	CONSTRAINT [DF_pmfold_user_id] DEFAULT (0) FOR [user_id],
	CONSTRAINT [DF_pmfold_pm_count] DEFAULT (0) FOR [pm_count]
GO

ALTER TABLE [phpbb_privmsgs_rules] WITH NOCHECK ADD 
	CONSTRAINT [DF_pmrule_user_id] DEFAULT (0) FOR [user_id],
	CONSTRAINT [DF_pmrule_rule_check] DEFAULT (0) FOR [rule_check],
	CONSTRAINT [DF_pmrule_rule_connection] DEFAULT (0) FOR [rule_connection],
	CONSTRAINT [DF_pmrule_rule_user_id] DEFAULT (0) FOR [rule_user_id],
	CONSTRAINT [DF_pmrule_rule_group_id] DEFAULT (0) FOR [rule_group_id],
	CONSTRAINT [DF_pmrule_rule_action] DEFAULT (0) FOR [rule_action],
	CONSTRAINT [DF_pmrule_rule_folder_id] DEFAULT (0) FOR [rule_folder_id]
GO

ALTER TABLE [phpbb_privmsgs_to] WITH NOCHECK ADD 
	CONSTRAINT [DF_pmto___msg_id] DEFAULT (0) FOR [msg_id],
	CONSTRAINT [DF_pmto___user_id] DEFAULT (0) FOR [user_id],
	CONSTRAINT [DF_pmto___author_id] DEFAULT (0) FOR [author_id],
	CONSTRAINT [DF_pmto___deleted] DEFAULT (0) FOR [deleted],
	CONSTRAINT [DF_pmto___new] DEFAULT (1) FOR [new],
	CONSTRAINT [DF_pmto___unread] DEFAULT (1) FOR [unread],
	CONSTRAINT [DF_pmto___replied] DEFAULT (0) FOR [replied],
	CONSTRAINT [DF_pmto___marked] DEFAULT (0) FOR [marked],
	CONSTRAINT [DF_pmto___forwarded] DEFAULT (0) FOR [forwarded],
	CONSTRAINT [DF_pmto___folder_id] DEFAULT (0) FOR [folder_id]
GO

ALTER TABLE [phpbb_profile_fields] WITH NOCHECK ADD 
	CONSTRAINT [DF_pffiel_field_type] DEFAULT (0) FOR [field_type],
	CONSTRAINT [DF_pffiel_field_default_value] DEFAULT ('0') FOR [field_default_value],
	CONSTRAINT [DF_pffiel_field_required] DEFAULT (0) FOR [field_required],
	CONSTRAINT [DF_pffiel_field_show_on_reg] DEFAULT (0) FOR [field_show_on_reg],
	CONSTRAINT [DF_pffiel_field_hide] DEFAULT (0) FOR [field_hide],
	CONSTRAINT [DF_pffiel_field_active] DEFAULT (0) FOR [field_active],
	CONSTRAINT [DF_pffiel_field_order] DEFAULT (0) FOR [field_order]
GO

ALTER TABLE [phpbb_profile_fields_data] WITH NOCHECK ADD 
	CONSTRAINT [DF_pfdata_user_id] DEFAULT (0) FOR [user_id]
GO

ALTER TABLE [phpbb_profile_fields_lang] WITH NOCHECK ADD 
	CONSTRAINT [DF_pfflan_field_id] DEFAULT (0) FOR [field_id],
	CONSTRAINT [DF_pfflan_lang_id] DEFAULT (0) FOR [lang_id],
	CONSTRAINT [DF_pfflan_option_id] DEFAULT (0) FOR [option_id],
	CONSTRAINT [DF_pfflan_field_type] DEFAULT (0) FOR [field_type]
GO

ALTER TABLE [phpbb_profile_lang] WITH NOCHECK ADD 
	CONSTRAINT [DF_pflang_field_id] DEFAULT (0) FOR [field_id],
	CONSTRAINT [DF_pflang_lang_id] DEFAULT (0) FOR [lang_id]
GO

ALTER TABLE [phpbb_ranks] WITH NOCHECK ADD 
	CONSTRAINT [DF_ranks__rank_min] DEFAULT (0) FOR [rank_min],
	CONSTRAINT [DF_ranks__rank_special] DEFAULT (0) FOR [rank_special]
GO

ALTER TABLE [phpbb_ratings] WITH NOCHECK ADD 
	CONSTRAINT [DF_rating_post_id] DEFAULT (0) FOR [post_id],
	CONSTRAINT [DF_rating_user_id] DEFAULT (0) FOR [user_id],
	CONSTRAINT [DF_rating_rating] DEFAULT (0) FOR [rating]
GO

ALTER TABLE [phpbb_reports] WITH NOCHECK ADD 
	CONSTRAINT [DF_report_reason_id] DEFAULT (0) FOR [reason_id],
	CONSTRAINT [DF_report_post_id] DEFAULT (0) FOR [post_id],
	CONSTRAINT [DF_report_msg_id] DEFAULT (0) FOR [msg_id],
	CONSTRAINT [DF_report_user_id] DEFAULT (0) FOR [user_id],
	CONSTRAINT [DF_report_user_notify] DEFAULT (0) FOR [user_notify],
	CONSTRAINT [DF_report_report_time] DEFAULT (0) FOR [report_time]
GO

ALTER TABLE [phpbb_reports_reasons] WITH NOCHECK ADD 
	CONSTRAINT [DF_reporr_reason_priority] DEFAULT (0) FOR [reason_priority]
GO

ALTER TABLE [phpbb_search_results] WITH NOCHECK ADD 
	CONSTRAINT [DF_search_search_id] DEFAULT (0) FOR [search_id],
	CONSTRAINT [DF_search_search_time] DEFAULT (0) FOR [search_time]
GO

ALTER TABLE [phpbb_search_wordlist] WITH NOCHECK ADD 
	CONSTRAINT [DF_swlist_word_common] DEFAULT (0) FOR [word_common]
GO

ALTER TABLE [phpbb_search_wordmatch] WITH NOCHECK ADD 
	CONSTRAINT [DF_swmatc_post_id] DEFAULT (0) FOR [post_id],
	CONSTRAINT [DF_swmatc_word_id] DEFAULT (0) FOR [word_id],
	CONSTRAINT [DF_swmatc_title_match] DEFAULT (0) FOR [title_match]
GO

ALTER TABLE [phpbb_sessions] WITH NOCHECK ADD 
	CONSTRAINT [DF_sessio_session_user_id] DEFAULT (0) FOR [session_user_id],
	CONSTRAINT [DF_sessio_session_last_visit] DEFAULT (0) FOR [session_last_visit],
	CONSTRAINT [DF_sessio_session_start] DEFAULT (0) FOR [session_start],
	CONSTRAINT [DF_sessio_session_time] DEFAULT (0) FOR [session_time],
	CONSTRAINT [DF_sessio_session_ip] DEFAULT ('0') FOR [session_ip],
	CONSTRAINT [DF_sessio_session_viewonline] DEFAULT (1) FOR [session_viewonline],
	CONSTRAINT [DF_sessio_session_admin] DEFAULT (0) FOR [session_admin]
GO

ALTER TABLE [phpbb_sitelist] WITH NOCHECK ADD 
	CONSTRAINT [DF_siteli_ip_exclude] DEFAULT (0) FOR [ip_exclude]
GO

ALTER TABLE [phpbb_smilies] WITH NOCHECK ADD 
	CONSTRAINT [DF_smilie_smile_width] DEFAULT (0) FOR [smile_width],
	CONSTRAINT [DF_smilie_smile_height] DEFAULT (0) FOR [smile_height],
	CONSTRAINT [DF_smilie_smile_order] DEFAULT (0) FOR [smile_order],
	CONSTRAINT [DF_smilie_display_on_posting] DEFAULT (1) FOR [display_on_posting]
GO

ALTER TABLE [phpbb_styles] WITH NOCHECK ADD 
	CONSTRAINT [DF_styles_style_active] DEFAULT (1) FOR [style_active],
	CONSTRAINT [DF_styles_template_id] DEFAULT (0) FOR [template_id],
	CONSTRAINT [DF_styles_theme_id] DEFAULT (0) FOR [theme_id],
	CONSTRAINT [DF_styles_imageset_id] DEFAULT (0) FOR [imageset_id]
GO

ALTER TABLE [phpbb_styles_template] WITH NOCHECK ADD 
	CONSTRAINT [DF_templa_bbcode_bitfield] DEFAULT (0) FOR [bbcode_bitfield],
	CONSTRAINT [DF_templa_template_storedb] DEFAULT (0) FOR [template_storedb]
GO

ALTER TABLE [phpbb_styles_template_data] WITH NOCHECK ADD 
	CONSTRAINT [DF_tpldat_template_id] DEFAULT (0) FOR [template_id],
	CONSTRAINT [DF_tpldat_template_mtime] DEFAULT (0) FOR [template_mtime]
GO

ALTER TABLE [phpbb_styles_theme] WITH NOCHECK ADD 
	CONSTRAINT [DF_theme__theme_storedb] DEFAULT (0) FOR [theme_storedb],
	CONSTRAINT [DF_theme__theme_mtime] DEFAULT (0) FOR [theme_mtime]
GO

ALTER TABLE [phpbb_topics] WITH NOCHECK ADD 
	CONSTRAINT [DF_topics_forum_id] DEFAULT (0) FOR [forum_id],
	CONSTRAINT [DF_topics_icon_id] DEFAULT (1) FOR [icon_id],
	CONSTRAINT [DF_topics_topic_attachment] DEFAULT (0) FOR [topic_attachment],
	CONSTRAINT [DF_topics_topic_approved] DEFAULT (1) FOR [topic_approved],
	CONSTRAINT [DF_topics_topic_reported] DEFAULT (0) FOR [topic_reported],
	CONSTRAINT [DF_topics_topic_poster] DEFAULT (0) FOR [topic_poster],
	CONSTRAINT [DF_topics_topic_time] DEFAULT (0) FOR [topic_time],
	CONSTRAINT [DF_topics_topic_time_limit] DEFAULT (0) FOR [topic_time_limit],
	CONSTRAINT [DF_topics_topic_views] DEFAULT (0) FOR [topic_views],
	CONSTRAINT [DF_topics_topic_replies] DEFAULT (0) FOR [topic_replies],
	CONSTRAINT [DF_topics_topic_replies_real] DEFAULT (0) FOR [topic_replies_real],
	CONSTRAINT [DF_topics_topic_status] DEFAULT (0) FOR [topic_status],
	CONSTRAINT [DF_topics_topic_type] DEFAULT (0) FOR [topic_type],
	CONSTRAINT [DF_topics_topic_first_post_id] DEFAULT (0) FOR [topic_first_post_id],
	CONSTRAINT [DF_topics_topic_last_post_id] DEFAULT (0) FOR [topic_last_post_id],
	CONSTRAINT [DF_topics_topic_last_poster_id] DEFAULT (0) FOR [topic_last_poster_id],
	CONSTRAINT [DF_topics_topic_last_post_time] DEFAULT (0) FOR [topic_last_post_time],
	CONSTRAINT [DF_topics_topic_last_view_time] DEFAULT (0) FOR [topic_last_view_time],
	CONSTRAINT [DF_topics_topic_moved_id] DEFAULT (0) FOR [topic_moved_id],
	CONSTRAINT [DF_topics_topic_bumped] DEFAULT (0) FOR [topic_bumped],
	CONSTRAINT [DF_topics_topic_bumper] DEFAULT (0) FOR [topic_bumper],
	CONSTRAINT [DF_topics_poll_start] DEFAULT (0) FOR [poll_start],
	CONSTRAINT [DF_topics_poll_length] DEFAULT (0) FOR [poll_length],
	CONSTRAINT [DF_topics_poll_max_options] DEFAULT (1) FOR [poll_max_options],
	CONSTRAINT [DF_topics_poll_last_vote] DEFAULT (0) FOR [poll_last_vote],
	CONSTRAINT [DF_topics_poll_vote_change] DEFAULT (0) FOR [poll_vote_change]
GO

ALTER TABLE [phpbb_topics_marking] WITH NOCHECK ADD 
	CONSTRAINT [DF_tmarki_user_id] DEFAULT (0) FOR [user_id],
	CONSTRAINT [DF_tmarki_topic_id] DEFAULT (0) FOR [topic_id],
	CONSTRAINT [DF_tmarki_mark_type] DEFAULT (0) FOR [mark_type],
	CONSTRAINT [DF_tmarki_mark_time] DEFAULT (0) FOR [mark_time]
GO

ALTER TABLE [phpbb_topics_watch] WITH NOCHECK ADD 
	CONSTRAINT [DF_twatch_topic_id] DEFAULT (0) FOR [topic_id],
	CONSTRAINT [DF_twatch_user_id] DEFAULT (0) FOR [user_id],
	CONSTRAINT [DF_twatch_notify_status] DEFAULT (0) FOR [notify_status]
GO

ALTER TABLE [phpbb_user_group] WITH NOCHECK ADD 
	CONSTRAINT [DF_usersg_group_id] DEFAULT (0) FOR [group_id],
	CONSTRAINT [DF_usersg_user_id] DEFAULT (0) FOR [user_id],
	CONSTRAINT [DF_usersg_group_leader] DEFAULT (0) FOR [group_leader]
GO

ALTER TABLE [phpbb_users] WITH NOCHECK ADD 
	CONSTRAINT [DF_users__user_type] DEFAULT (0) FOR [user_type],
	CONSTRAINT [DF_users__group_id] DEFAULT (3) FOR [group_id],
	CONSTRAINT [DF_users__user_regdate] DEFAULT (0) FOR [user_regdate],
	CONSTRAINT [DF_users__user_passchg] DEFAULT (0) FOR [user_passchg],
	CONSTRAINT [DF_users__user_email_hash] DEFAULT (0) FOR [user_email_hash],
	CONSTRAINT [DF_users__user_lastvisit] DEFAULT (0) FOR [user_lastvisit],
	CONSTRAINT [DF_users__user_lastpost_time] DEFAULT (0) FOR [user_lastpost_time],
	CONSTRAINT [DF_users__user_warnings] DEFAULT (0) FOR [user_warnings],
	CONSTRAINT [DF_users__user_posts] DEFAULT (0) FOR [user_posts],
	CONSTRAINT [DF_users__user_timezone] DEFAULT (0) FOR [user_timezone],
	CONSTRAINT [DF_users__user_dst] DEFAULT (0) FOR [user_dst],
	CONSTRAINT [DF_users__user_dateformat] DEFAULT ('d M Y H:i') FOR [user_dateformat],
	CONSTRAINT [DF_users__user_style] DEFAULT (0) FOR [user_style],
	CONSTRAINT [DF_users__user_rank] DEFAULT (0) FOR [user_rank],
	CONSTRAINT [DF_users__user_new_privmsg] DEFAULT (0) FOR [user_new_privmsg],
	CONSTRAINT [DF_users__user_unread_privmsg] DEFAULT (0) FOR [user_unread_privmsg],
	CONSTRAINT [DF_users__user_last_privmsg] DEFAULT (0) FOR [user_last_privmsg],
	CONSTRAINT [DF_users__user_message_rules] DEFAULT (0) FOR [user_message_rules],
	CONSTRAINT [DF_users__user_full_folder] DEFAULT ((-3)) FOR [user_full_folder],
	CONSTRAINT [DF_users__user_emailtime] DEFAULT (0) FOR [user_emailtime],
	CONSTRAINT [DF_users__user_show_days] DEFAULT (0) FOR [user_show_days],
	CONSTRAINT [DF_users__user_notify] DEFAULT (0) FOR [user_notify],
	CONSTRAINT [DF_users__user_notify_pm] DEFAULT (1) FOR [user_notify_pm],
	CONSTRAINT [DF_users__user_notify_type] DEFAULT (0) FOR [user_notify_type],
	CONSTRAINT [DF_users__user_allow_pm] DEFAULT (1) FOR [user_allow_pm],
	CONSTRAINT [DF_users__user_allow_email] DEFAULT (1) FOR [user_allow_email],
	CONSTRAINT [DF_users__user_allow_viewonlin] DEFAULT (1) FOR [user_allow_viewonline],
	CONSTRAINT [DF_users__user_allow_viewemail] DEFAULT (1) FOR [user_allow_viewemail],
	CONSTRAINT [DF_users__user_allow_massemail] DEFAULT (1) FOR [user_allow_massemail],
	CONSTRAINT [DF_users__user_options] DEFAULT (893) FOR [user_options],
	CONSTRAINT [DF_users__user_avatar_type] DEFAULT (0) FOR [user_avatar_type],
	CONSTRAINT [DF_users__user_avatar_width] DEFAULT (0) FOR [user_avatar_width],
	CONSTRAINT [DF_users__user_avatar_height] DEFAULT (0) FOR [user_avatar_height],
	CONSTRAINT [DF_users__user_sig_bbcode_bitf] DEFAULT (0) FOR [user_sig_bbcode_bitfield]
GO

ALTER TABLE [phpbb_zebra] WITH NOCHECK ADD 
	CONSTRAINT [DF_zebra__user_id] DEFAULT (0) FOR [user_id],
	CONSTRAINT [DF_zebra__zebra_id] DEFAULT (0) FOR [zebra_id],
	CONSTRAINT [DF_zebra__friend] DEFAULT (0) FOR [friend],
	CONSTRAINT [DF_zebra__foe] DEFAULT (0) FOR [foe]
GO

CREATE  INDEX [filetime] ON [phpbb_attachments]([filetime]) ON [PRIMARY]
GO

CREATE  INDEX [post_msg_id] ON [phpbb_attachments]([post_msg_id]) ON [PRIMARY]
GO

CREATE  INDEX [topic_id] ON [phpbb_attachments]([topic_id]) ON [PRIMARY]
GO

CREATE  INDEX [poster_id] ON [phpbb_attachments]([poster_id]) ON [PRIMARY]
GO

CREATE  INDEX [physical_filename] ON [phpbb_attachments]([physical_filename]) ON [PRIMARY]
GO

CREATE  INDEX [filesize] ON [phpbb_attachments]([filesize]) ON [PRIMARY]
GO

CREATE  INDEX [group_id] ON [phpbb_auth_groups]([group_id]) ON [PRIMARY]
GO

CREATE  INDEX [auth_option_id] ON [phpbb_auth_groups]([auth_option_id]) ON [PRIMARY]
GO

CREATE  INDEX [auth_option] ON [phpbb_auth_options]([auth_option]) ON [PRIMARY]
GO

CREATE  INDEX [preset_type] ON [phpbb_auth_presets]([preset_type]) ON [PRIMARY]
GO

CREATE  INDEX [user_id] ON [phpbb_auth_users]([user_id]) ON [PRIMARY]
GO

CREATE  INDEX [auth_option_id] ON [phpbb_auth_users]([auth_option_id]) ON [PRIMARY]
GO

CREATE  INDEX [order_id] ON [phpbb_bookmarks]([order_id]) ON [PRIMARY]
GO

CREATE  INDEX [topic_user_id] ON [phpbb_bookmarks]([topic_id], [user_id]) ON [PRIMARY]
GO

CREATE  INDEX [bot_active] ON [phpbb_bots]([bot_active]) ON [PRIMARY]
GO

CREATE  INDEX [is_dynamic] ON [phpbb_config]([is_dynamic]) ON [PRIMARY]
GO

CREATE  INDEX [save_time] ON [phpbb_drafts]([save_time]) ON [PRIMARY]
GO

CREATE  INDEX [left_right_id] ON [phpbb_forums]([left_id], [right_id]) ON [PRIMARY]
GO

CREATE  INDEX [forum_last_post_id] ON [phpbb_forums]([forum_last_post_id]) ON [PRIMARY]
GO

CREATE  INDEX [forum_id] ON [phpbb_forums_watch]([forum_id]) ON [PRIMARY]
GO

CREATE  INDEX [user_id] ON [phpbb_forums_watch]([user_id]) ON [PRIMARY]
GO

CREATE  INDEX [notify_status] ON [phpbb_forums_watch]([notify_status]) ON [PRIMARY]
GO

CREATE  INDEX [group_legend] ON [phpbb_groups]([group_legend]) ON [PRIMARY]
GO

CREATE  INDEX [log_type] ON [phpbb_log]([log_type]) ON [PRIMARY]
GO

CREATE  INDEX [forum_id] ON [phpbb_log]([forum_id]) ON [PRIMARY]
GO

CREATE  INDEX [topic_id] ON [phpbb_log]([topic_id]) ON [PRIMARY]
GO

CREATE  INDEX [reportee_id] ON [phpbb_log]([reportee_id]) ON [PRIMARY]
GO

CREATE  INDEX [user_id] ON [phpbb_log]([user_id]) ON [PRIMARY]
GO

CREATE  INDEX [display_on_index] ON [phpbb_moderator_cache]([display_on_index]) ON [PRIMARY]
GO

CREATE  INDEX [forum_id] ON [phpbb_moderator_cache]([forum_id]) ON [PRIMARY]
GO

CREATE  INDEX [module_type] ON [phpbb_modules]([module_type], [module_enabled]) ON [PRIMARY]
GO

CREATE  INDEX [poll_option_id] ON [phpbb_poll_results]([poll_option_id]) ON [PRIMARY]
GO

CREATE  INDEX [topic_id] ON [phpbb_poll_results]([topic_id]) ON [PRIMARY]
GO

CREATE  INDEX [topic_id] ON [phpbb_poll_voters]([topic_id]) ON [PRIMARY]
GO

CREATE  INDEX [vote_user_id] ON [phpbb_poll_voters]([vote_user_id]) ON [PRIMARY]
GO

CREATE  INDEX [vote_user_ip] ON [phpbb_poll_voters]([vote_user_ip]) ON [PRIMARY]
GO

CREATE  INDEX [forum_id] ON [phpbb_posts]([forum_id]) ON [PRIMARY]
GO

CREATE  INDEX [topic_id] ON [phpbb_posts]([topic_id]) ON [PRIMARY]
GO

CREATE  INDEX [poster_ip] ON [phpbb_posts]([poster_ip]) ON [PRIMARY]
GO

CREATE  INDEX [poster_id] ON [phpbb_posts]([poster_id]) ON [PRIMARY]
GO

CREATE  INDEX [post_approved] ON [phpbb_posts]([post_approved]) ON [PRIMARY]
GO

CREATE  INDEX [post_time] ON [phpbb_posts]([post_time]) ON [PRIMARY]
GO

CREATE  INDEX [author_ip] ON [phpbb_privmsgs]([author_ip]) ON [PRIMARY]
GO

CREATE  INDEX [message_time] ON [phpbb_privmsgs]([message_time]) ON [PRIMARY]
GO

CREATE  INDEX [author_id] ON [phpbb_privmsgs]([author_id]) ON [PRIMARY]
GO

CREATE  INDEX [root_level] ON [phpbb_privmsgs]([root_level]) ON [PRIMARY]
GO

CREATE  INDEX [user_id] ON [phpbb_privmsgs_folder]([user_id]) ON [PRIMARY]
GO

CREATE  INDEX [msg_id] ON [phpbb_privmsgs_to]([msg_id]) ON [PRIMARY]
GO

CREATE  INDEX [user_id] ON [phpbb_privmsgs_to]([user_id], [folder_id]) ON [PRIMARY]
GO

CREATE  INDEX [field_type] ON [phpbb_profile_fields]([field_type]) ON [PRIMARY]
GO

CREATE  INDEX [field_order] ON [phpbb_profile_fields]([field_order]) ON [PRIMARY]
GO

CREATE  INDEX [post_id] ON [phpbb_ratings]([post_id]) ON [PRIMARY]
GO

CREATE  INDEX [user_id] ON [phpbb_ratings]([user_id]) ON [PRIMARY]
GO

CREATE  INDEX [session_id] ON [phpbb_search_results]([session_id]) ON [PRIMARY]
GO

CREATE  INDEX [word_id] ON [phpbb_search_wordlist]([word_id]) ON [PRIMARY]
GO

CREATE  INDEX [word_id] ON [phpbb_search_wordmatch]([word_id]) ON [PRIMARY]
GO

CREATE  INDEX [session_time] ON [phpbb_sessions]([session_time]) ON [PRIMARY]
GO

CREATE  INDEX [session_user_id] ON [phpbb_sessions]([session_user_id]) ON [PRIMARY]
GO

CREATE  UNIQUE  INDEX [style_name] ON [phpbb_styles]([style_name]) ON [PRIMARY]
GO

CREATE  INDEX [template_id] ON [phpbb_styles]([template_id]) ON [PRIMARY]
GO

CREATE  INDEX [theme_id] ON [phpbb_styles]([theme_id]) ON [PRIMARY]
GO

CREATE  INDEX [imageset_id] ON [phpbb_styles]([imageset_id]) ON [PRIMARY]
GO

CREATE  UNIQUE  INDEX [imageset_name] ON [phpbb_styles_imageset]([imageset_name]) ON [PRIMARY]
GO

CREATE  UNIQUE  INDEX [template_name] ON [phpbb_styles_template]([template_name]) ON [PRIMARY]
GO

CREATE  INDEX [template_id] ON [phpbb_styles_template_data]([template_id]) ON [PRIMARY]
GO

CREATE  INDEX [template_filename] ON [phpbb_styles_template_data]([template_filename]) ON [PRIMARY]
GO

CREATE  UNIQUE  INDEX [theme_name] ON [phpbb_styles_theme]([theme_name]) ON [PRIMARY]
GO

CREATE  INDEX [forum_id] ON [phpbb_topics]([forum_id]) ON [PRIMARY]
GO

CREATE  INDEX [forum_id_type] ON [phpbb_topics]([forum_id], [topic_type]) ON [PRIMARY]
GO

CREATE  INDEX [topic_last_post_time] ON [phpbb_topics]([topic_last_post_time]) ON [PRIMARY]
GO

CREATE  INDEX [topic_id] ON [phpbb_topics_watch]([topic_id]) ON [PRIMARY]
GO

CREATE  INDEX [user_id] ON [phpbb_topics_watch]([user_id]) ON [PRIMARY]
GO

CREATE  INDEX [notify_status] ON [phpbb_topics_watch]([notify_status]) ON [PRIMARY]
GO

CREATE  INDEX [group_id] ON [phpbb_user_group]([group_id]) ON [PRIMARY]
GO

CREATE  INDEX [user_id] ON [phpbb_user_group]([user_id]) ON [PRIMARY]
GO

CREATE  INDEX [group_leader] ON [phpbb_user_group]([group_leader]) ON [PRIMARY]
GO

CREATE  INDEX [user_birthday] ON [phpbb_users]([user_birthday]) ON [PRIMARY]
GO

CREATE  INDEX [user_email_hash] ON [phpbb_users]([user_email_hash]) ON [PRIMARY]
GO

CREATE  INDEX [username] ON [phpbb_users]([username]) ON [PRIMARY]
GO

CREATE  INDEX [user_id] ON [phpbb_zebra]([user_id]) ON [PRIMARY]
GO

CREATE  INDEX [zebra_id] ON [phpbb_zebra]([zebra_id]) ON [PRIMARY]
GO

COMMIT
GO
