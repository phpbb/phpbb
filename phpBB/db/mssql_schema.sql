if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[phpbb_forum_prune]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[phpbb_forum_prune]
GO

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[phpbb_auth_access]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[phpbb_auth_access]
GO

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[phpbb_banlist]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[phpbb_banlist]
GO

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[phpbb_categories]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[phpbb_categories]
GO

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[phpbb_config]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[phpbb_config]
GO

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[phpbb_disallow]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[phpbb_disallow]
GO

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[phpbb_forums]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[phpbb_forums]
GO

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[phpbb_groups]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[phpbb_groups]
GO

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[phpbb_posts]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[phpbb_posts]
GO

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[phpbb_posts_text]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[phpbb_posts_text]
GO

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[phpbb_privmsgs]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[phpbb_privmsgs]
GO

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[phpbb_privmsgs_text]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[phpbb_privmsgs_text]
GO

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[phpbb_ranks]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[phpbb_ranks]
GO

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[phpbb_session]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[phpbb_session]
GO

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[phpbb_smilies]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[phpbb_smilies]
GO

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[phpbb_themes]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[phpbb_themes]
GO

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[phpbb_themes_name]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[phpbb_themes_name]
GO

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[phpbb_topics]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[phpbb_topics]
GO

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[phpbb_topics_watch]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[phpbb_topics_watch]
GO

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[phpbb_user_group]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[phpbb_user_group]
GO

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[phpbb_users]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[phpbb_users]
GO

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[phpbb_words]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[phpbb_words]
GO

CREATE TABLE [dbo].[phpbb_forum_prune] (
	[prune_id] [int] NOT NULL ,
	[forum_id] [int] NOT NULL ,
	[prune_days] [int] NOT NULL ,
	[prune_freq] [int] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[phpbb_auth_access] (
	[group_id] [int] NOT NULL ,
	[forum_id] [int] NOT NULL ,
	[auth_view] [smallint] NOT NULL ,
	[auth_read] [smallint] NOT NULL ,
	[auth_post] [smallint] NOT NULL ,
	[auth_reply] [smallint] NOT NULL ,
	[auth_edit] [smallint] NOT NULL ,
	[auth_delete] [smallint] NOT NULL ,
	[auth_announce] [smallint] NOT NULL ,
	[auth_sticky] [smallint] NOT NULL ,
	[auth_votecreate] [smallint] NOT NULL ,
	[auth_attachments] [smallint] NOT NULL ,
	[auth_vote] [smallint] NOT NULL ,
	[auth_mod] [smallint] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[phpbb_banlist] (
	[ban_id] [int] NOT NULL ,
	[ban_userid] [int] NULL ,
	[ban_ip] [char] (8) COLLATE Latin1_General_CI_AS NULL ,
	[ban_email] [char] (255) COLLATE Latin1_General_CI_AS NULL 
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[phpbb_categories] (
	[cat_id] [int] NOT NULL ,
	[cat_title] [char] (100) COLLATE Latin1_General_CI_AS NULL ,
	[cat_order] [int] NULL 
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[phpbb_config] (
	[config_id] [int] NULL ,
	[selected] [int] NOT NULL ,
	[board_disable] [smallint] NOT NULL ,
	[sitename] [varchar] (100) COLLATE Latin1_General_CI_AS NULL ,
	[cookie_name] [varchar] (25) COLLATE Latin1_General_CI_AS NOT NULL ,
	[cookie_path] [varchar] (25) COLLATE Latin1_General_CI_AS NOT NULL ,
	[cookie_domain] [varchar] (50) COLLATE Latin1_General_CI_AS NOT NULL ,
	[cookie_secure] [smallint] NOT NULL ,
	[session_length] [int] NOT NULL ,
	[allow_html] [smallint] NULL ,
	[allow_html_tags] [varchar] (255) COLLATE Latin1_General_CI_AS NOT NULL ,
	[allow_bbcode] [smallint] NULL ,
	[allow_smilies] [smallint] NULL ,
	[allow_sig] [smallint] NULL ,
	[allow_namechange] [smallint] NULL ,
	[allow_theme_create] [smallint] NULL ,
	[allow_avatar_local] [smallint] NOT NULL ,
	[allow_avatar_remote] [smallint] NOT NULL ,
	[allow_avatar_upload] [smallint] NOT NULL ,
	[override_themes] [smallint] NULL ,
	[posts_per_page] [int] NULL ,
	[topics_per_page] [int] NULL ,
	[hot_threshold] [int] NULL ,
	[email_sig] [varchar] (255) COLLATE Latin1_General_CI_AS NULL ,
	[email_from] [varchar] (100) COLLATE Latin1_General_CI_AS NULL ,
	[require_activation] [smallint] NOT NULL ,
	[flood_interval] [int] NOT NULL ,
	[avatar_filesize] [int] NOT NULL ,
	[avatar_max_width] [smallint] NOT NULL ,
	[avatar_max_height] [smallint] NOT NULL ,
	[avatar_path] [varchar] (255) COLLATE Latin1_General_CI_AS NOT NULL ,
	[smilies_path] [varchar] (50) COLLATE Latin1_General_CI_AS NOT NULL ,
	[default_theme] [int] NOT NULL ,
	[default_lang] [varchar] (255) COLLATE Latin1_General_CI_AS NULL ,
	[default_dateformat] [varchar] (14) COLLATE Latin1_General_CI_AS NOT NULL ,
	[system_timezone] [int] NOT NULL ,
	[sys_template] [varchar] (100) COLLATE Latin1_General_CI_AS NOT NULL ,
	[prune_enable] [smallint] NOT NULL ,
	[gzip_compress] [smallint] NOT NULL ,
	[board_startdate] [int] NOT NULL ,
	[smtp_delivery] [smallint] NOT NULL ,
	[smtp_host] [varchar] (50) COLLATE Latin1_General_CI_AS NULL 
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[phpbb_disallow] (
	[disallow_id] [int] NULL ,
	[disallow_username] [varchar] (25) COLLATE Latin1_General_CI_AS NULL 
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[phpbb_forums] (
	[forum_id] [int] NOT NULL ,
	[cat_id] [int] NOT NULL ,
	[forum_name] [varchar] (150) COLLATE Latin1_General_CI_AS NULL ,
	[forum_status] [smallint] NOT NULL ,
	[forum_desc] [text] COLLATE Latin1_General_CI_AS NULL ,
	[forum_order] [int] NOT NULL ,
	[forum_posts] [int] NOT NULL ,
	[forum_topics] [smallint] NOT NULL ,
	[forum_last_post_id] [int] NOT NULL ,
	[prune_next] [int] NULL ,
	[prune_enable] [smallint] NOT NULL ,
	[auth_view] [smallint] NOT NULL ,
	[auth_read] [smallint] NOT NULL ,
	[auth_post] [smallint] NOT NULL ,
	[auth_reply] [smallint] NOT NULL ,
	[auth_edit] [smallint] NOT NULL ,
	[auth_delete] [smallint] NOT NULL ,
	[auth_announce] [smallint] NOT NULL ,
	[auth_sticky] [smallint] NOT NULL ,
	[auth_votecreate] [smallint] NOT NULL ,
	[auth_vote] [smallint] NOT NULL ,
	[auth_attachments] [smallint] NOT NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [dbo].[phpbb_groups] (
	[group_id] [int] NOT NULL ,
	[group_type] [smallint] NOT NULL ,
	[group_name] [varchar] (40) COLLATE Latin1_General_CI_AS NOT NULL ,
	[group_description] [varchar] (255) COLLATE Latin1_General_CI_AS NOT NULL ,
	[group_moderator] [int] NOT NULL ,
	[group_single_user] [smallint] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[phpbb_posts] (
	[post_id] [int] NOT NULL ,
	[topic_id] [int] NOT NULL ,
	[forum_id] [int] NOT NULL ,
	[poster_id] [int] NOT NULL ,
	[post_time] [int] NOT NULL ,
	[poster_ip] [varchar] (8) COLLATE Latin1_General_CI_AS NOT NULL ,
	[post_username] [varchar] (30) COLLATE Latin1_General_CI_AS NULL ,
	[enable_bbcode] [smallint] NOT NULL ,
	[enable_html] [smallint] NOT NULL ,
	[enable_smilies] [smallint] NOT NULL ,
	[bbcode_uid] [varchar] (10) COLLATE Latin1_General_CI_AS NOT NULL ,
	[post_edit_time] [int] NULL ,
	[post_edit_count] [smallint] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[phpbb_posts_text] (
	[post_id] [int] NOT NULL ,
	[post_subject] [varchar] (255) COLLATE Latin1_General_CI_AS NULL ,
	[post_text] [text] COLLATE Latin1_General_CI_AS NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [dbo].[phpbb_privmsgs] (
	[privmsgs_id] [int] NOT NULL ,
	[privmsgs_type] [smallint] NOT NULL ,
	[privmsgs_subject] [varchar] (255) COLLATE Latin1_General_CI_AS NOT NULL ,
	[privmsgs_from_userid] [int] NOT NULL ,
	[privmsgs_to_userid] [int] NOT NULL ,
	[privmsgs_date] [int] NOT NULL ,
	[privmsgs_ip] [varchar] (8) COLLATE Latin1_General_CI_AS NOT NULL ,
	[privmsgs_bbcode_uid] [varchar] (10) COLLATE Latin1_General_CI_AS NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[phpbb_privmsgs_text] (
	[privmsgs_text_id] [int] NOT NULL ,
	[privmsgs_text] [text] COLLATE Latin1_General_CI_AS NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [dbo].[phpbb_ranks] (
	[rank_id] [int] NOT NULL ,
	[rank_title] [varchar] (50) COLLATE Latin1_General_CI_AS NOT NULL ,
	[rank_min] [int] NOT NULL ,
	[rank_max] [int] NOT NULL ,
	[rank_special] [smallint] NULL ,
	[rank_image] [varchar] (255) COLLATE Latin1_General_CI_AS NULL 
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[phpbb_session] (
	[session_id] [char] (32) COLLATE Latin1_General_CI_AS NOT NULL ,
	[session_user_id] [int] NOT NULL ,
	[session_start] [int] NOT NULL ,
	[session_time] [int] NOT NULL ,
	[session_last_visit] [int] NOT NULL ,
	[session_ip] [char] (8) COLLATE Latin1_General_CI_AS NOT NULL ,
	[session_page] [int] NOT NULL ,
	[session_logged_in] [smallint] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[phpbb_smilies] (
	[smilies_id] [int] NOT NULL ,
	[code] [varchar] (50) COLLATE Latin1_General_CI_AS NULL ,
	[smile_url] [varchar] (100) COLLATE Latin1_General_CI_AS NULL ,
	[emoticon] [varchar] (75) COLLATE Latin1_General_CI_AS NULL 
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[phpbb_themes] (
	[themes_id] [int] NOT NULL ,
	[themes_name] [varchar] (30) COLLATE Latin1_General_CI_AS NOT NULL ,
	[head_stylesheet] [varchar] (100) COLLATE Latin1_General_CI_AS NULL ,
	[body_background] [varchar] (100) COLLATE Latin1_General_CI_AS NULL ,
	[body_bgcolor] [varchar] (6) COLLATE Latin1_General_CI_AS NULL ,
	[body_text] [varchar] (6) COLLATE Latin1_General_CI_AS NULL ,
	[body_link] [varchar] (6) COLLATE Latin1_General_CI_AS NULL ,
	[body_vlink] [varchar] (6) COLLATE Latin1_General_CI_AS NULL ,
	[body_alink] [varchar] (6) COLLATE Latin1_General_CI_AS NULL ,
	[body_hlink] [varchar] (6) COLLATE Latin1_General_CI_AS NULL ,
	[tr_color1] [varchar] (6) COLLATE Latin1_General_CI_AS NULL ,
	[tr_color2] [varchar] (6) COLLATE Latin1_General_CI_AS NULL ,
	[tr_color3] [varchar] (6) COLLATE Latin1_General_CI_AS NULL ,
	[tr_class1] [varchar] (20) COLLATE Latin1_General_CI_AS NULL ,
	[tr_class2] [varchar] (20) COLLATE Latin1_General_CI_AS NULL ,
	[tr_class3] [varchar] (25) COLLATE Latin1_General_CI_AS NULL ,
	[th_color1] [varchar] (6) COLLATE Latin1_General_CI_AS NULL ,
	[th_color2] [varchar] (6) COLLATE Latin1_General_CI_AS NULL ,
	[th_color3] [varchar] (6) COLLATE Latin1_General_CI_AS NULL ,
	[th_class1] [varchar] (25) COLLATE Latin1_General_CI_AS NULL ,
	[th_class2] [varchar] (25) COLLATE Latin1_General_CI_AS NULL ,
	[th_class3] [varchar] (6) COLLATE Latin1_General_CI_AS NULL ,
	[td_color1] [varchar] (6) COLLATE Latin1_General_CI_AS NULL ,
	[td_color2] [varchar] (6) COLLATE Latin1_General_CI_AS NULL ,
	[td_color3] [varchar] (6) COLLATE Latin1_General_CI_AS NULL ,
	[td_class1] [varchar] (25) COLLATE Latin1_General_CI_AS NULL ,
	[td_class2] [varchar] (25) COLLATE Latin1_General_CI_AS NULL ,
	[td_class3] [varchar] (25) COLLATE Latin1_General_CI_AS NULL ,
	[fontface1] [varchar] (50) COLLATE Latin1_General_CI_AS NULL ,
	[fontface2] [varchar] (50) COLLATE Latin1_General_CI_AS NULL ,
	[fontface3] [varchar] (50) COLLATE Latin1_General_CI_AS NULL ,
	[fontsize1] [smallint] NULL ,
	[fontsize2] [smallint] NULL ,
	[fontsize3] [smallint] NULL ,
	[fontcolor1] [varchar] (6) COLLATE Latin1_General_CI_AS NULL ,
	[fontcolor2] [varchar] (6) COLLATE Latin1_General_CI_AS NULL ,
	[fontcolor3] [varchar] (6) COLLATE Latin1_General_CI_AS NULL ,
	[span_class1] [varchar] (25) COLLATE Latin1_General_CI_AS NULL ,
	[span_class2] [varchar] (25) COLLATE Latin1_General_CI_AS NULL ,
	[span_class3] [varchar] (25) COLLATE Latin1_General_CI_AS NULL 
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[phpbb_themes_name] (
	[themes_id] [int] NOT NULL ,
	[tr_color1_name] [varchar] (50) COLLATE Latin1_General_CI_AS NULL ,
	[tr_color2_name] [varchar] (50) COLLATE Latin1_General_CI_AS NULL ,
	[tr_color3_name] [varchar] (50) COLLATE Latin1_General_CI_AS NULL ,
	[th_color1_name] [varchar] (50) COLLATE Latin1_General_CI_AS NULL ,
	[th_color2_name] [varchar] (50) COLLATE Latin1_General_CI_AS NULL ,
	[th_color3_name] [varchar] (50) COLLATE Latin1_General_CI_AS NULL ,
	[td_color1_name] [varchar] (50) COLLATE Latin1_General_CI_AS NULL ,
	[td_color2_name] [varchar] (50) COLLATE Latin1_General_CI_AS NULL ,
	[td_color3_name] [varchar] (50) COLLATE Latin1_General_CI_AS NULL ,
	[fontface1_name] [varchar] (50) COLLATE Latin1_General_CI_AS NULL ,
	[fontface2_name] [varchar] (50) COLLATE Latin1_General_CI_AS NULL ,
	[fontface3_name] [varchar] (50) COLLATE Latin1_General_CI_AS NULL ,
	[fontsize1_name] [varchar] (50) COLLATE Latin1_General_CI_AS NULL ,
	[fontsize2_name] [varchar] (50) COLLATE Latin1_General_CI_AS NULL ,
	[fontsize3_name] [varchar] (50) COLLATE Latin1_General_CI_AS NULL ,
	[fontcolor1_name] [varchar] (50) COLLATE Latin1_General_CI_AS NULL ,
	[fontcolor2_name] [varchar] (50) COLLATE Latin1_General_CI_AS NULL ,
	[fontcolor3_name] [varchar] (50) COLLATE Latin1_General_CI_AS NULL ,
	[img1_name] [varchar] (50) COLLATE Latin1_General_CI_AS NULL ,
	[img2_name] [varchar] (50) COLLATE Latin1_General_CI_AS NULL ,
	[img3_name] [varchar] (50) COLLATE Latin1_General_CI_AS NULL ,
	[img4_name] [varchar] (50) COLLATE Latin1_General_CI_AS NULL 
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[phpbb_topics] (
	[topic_id] [int] NOT NULL ,
	[forum_id] [int] NOT NULL ,
	[topic_title] [varchar] (100) COLLATE Latin1_General_CI_AS NOT NULL ,
	[topic_poster] [int] NOT NULL ,
	[topic_time] [int] NOT NULL ,
	[topic_views] [int] NOT NULL ,
	[topic_replies] [int] NOT NULL ,
	[topic_status] [smallint] NOT NULL ,
	[topic_type] [smallint] NOT NULL ,
	[topic_notify] [smallint] NULL ,
	[topic_last_post_id] [int] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[phpbb_topics_watch] (
	[topic_id] [int] NOT NULL ,
	[user_id] [int] NOT NULL ,
	[notify_status] [smallint] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[phpbb_user_group] (
	[group_id] [int] NOT NULL ,
	[user_id] [int] NOT NULL ,
	[user_pending] [smallint] NULL 
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[phpbb_users] (
	[user_id] [int] NOT NULL ,
	[user_active] [smallint] NULL ,
	[username] [varchar] (25) COLLATE Latin1_General_CI_AS NOT NULL ,
	[user_password] [varchar] (32) COLLATE Latin1_General_CI_AS NOT NULL ,
	[user_autologin_key] [varchar] (32) COLLATE Latin1_General_CI_AS NULL ,
	[user_level] [smallint] NULL ,
	[user_timezone] [real] NOT NULL ,
	[user_dateformat] [varchar] (14) COLLATE Latin1_General_CI_AS NOT NULL ,
	[user_template] [varchar] (50) COLLATE Latin1_General_CI_AS NULL ,
	[user_theme] [int] NULL ,
	[user_lang] [varchar] (255) COLLATE Latin1_General_CI_AS NULL ,
	[user_viewemail] [smallint] NULL ,
	[user_attachsig] [smallint] NULL ,
	[user_allowhtml] [smallint] NULL ,
	[user_allowbbcode] [smallint] NULL ,
	[user_allowsmile] [smallint] NULL ,
	[user_allowavatar] [smallint] NOT NULL ,
	[user_allow_pm] [smallint] NOT NULL ,
	[user_allow_viewonline] [smallint] NOT NULL ,
	[user_notify_pm] [smallint] NOT NULL ,
	[user_regdate] [int] NOT NULL ,
	[user_rank] [int] NULL ,
	[user_avatar] [varchar] (100) COLLATE Latin1_General_CI_AS NULL ,
	[user_email] [varchar] (255) COLLATE Latin1_General_CI_AS NULL ,
	[user_icq] [varchar] (15) COLLATE Latin1_General_CI_AS NULL ,
	[user_website] [varchar] (100) COLLATE Latin1_General_CI_AS NULL ,
	[user_occ] [varchar] (100) COLLATE Latin1_General_CI_AS NULL ,
	[user_from] [varchar] (100) COLLATE Latin1_General_CI_AS NULL ,
	[user_interests] [varchar] (255) COLLATE Latin1_General_CI_AS NULL ,
	[user_sig] [varchar] (255) COLLATE Latin1_General_CI_AS NULL ,
	[user_aim] [varchar] (255) COLLATE Latin1_General_CI_AS NULL ,
	[user_yim] [varchar] (255) COLLATE Latin1_General_CI_AS NULL ,
	[user_msnm] [varchar] (255) COLLATE Latin1_General_CI_AS NULL ,
	[user_posts] [int] NULL ,
	[user_actkey] [varchar] (32) COLLATE Latin1_General_CI_AS NULL ,
	[user_newpasswd] [varchar] (32) COLLATE Latin1_General_CI_AS NULL ,
	[user_notify] [smallint] NULL 
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[phpbb_words] (
	[word_id] [int] NULL ,
	[word] [varchar] (100) COLLATE Latin1_General_CI_AS NOT NULL ,
	[replacement] [varchar] (100) COLLATE Latin1_General_CI_AS NOT NULL 
) ON [PRIMARY]
GO
