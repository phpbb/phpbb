/*

  mssql_schema.sql for phpBB2 (c) 2001, phpBB Group 

 $Id$

*/

if exists (select * from sysobjects where id = object_id(N'[FK_phpbb_user_group_phpbb_groups]') and OBJECTPROPERTY(id, N'IsForeignKey') = 1)
ALTER TABLE [phpbb_user_group] DROP CONSTRAINT FK_phpbb_user_group_phpbb_groups
GO

if exists (select * from sysobjects where id = object_id(N'[FK_phpbb_posts_text_phpbb_posts]') and OBJECTPROPERTY(id, N'IsForeignKey') = 1)
ALTER TABLE [phpbb_posts_text] DROP CONSTRAINT FK_phpbb_posts_text_phpbb_posts
GO

if exists (select * from sysobjects where id = object_id(N'[FK_phpbb_privmsgs_text_phpbb_privmsgs]') and OBJECTPROPERTY(id, N'IsForeignKey') = 1)
ALTER TABLE [phpbb_privmsgs_text] DROP CONSTRAINT FK_phpbb_privmsgs_text_phpbb_privmsgs
GO

if exists (select * from sysobjects where id = object_id(N'[FK_phpbb_themes_name_phpbb_themes]') and OBJECTPROPERTY(id, N'IsForeignKey') = 1)
ALTER TABLE [phpbb_themes_name] DROP CONSTRAINT FK_phpbb_themes_name_phpbb_themes
GO

if exists (select * from sysobjects where id = object_id(N'[FK_phpbb_topics_watch_phpbb_topics]') and OBJECTPROPERTY(id, N'IsForeignKey') = 1)
ALTER TABLE [phpbb_topics_watch] DROP CONSTRAINT FK_phpbb_topics_watch_phpbb_topics
GO

if exists (select * from sysobjects where id = object_id(N'[FK_phpbb_topics_watch_phpbb_users]') and OBJECTPROPERTY(id, N'IsForeignKey') = 1)
ALTER TABLE [phpbb_topics_watch] DROP CONSTRAINT FK_phpbb_topics_watch_phpbb_users
GO

if exists (select * from sysobjects where id = object_id(N'[FK_phpbb_user_group_phpbb_users]') and OBJECTPROPERTY(id, N'IsForeignKey') = 1)
ALTER TABLE [phpbb_user_group] DROP CONSTRAINT FK_phpbb_user_group_phpbb_users
GO

if exists (select * from sysobjects where id = object_id(N'[phpbb_auth_access]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [phpbb_auth_access]
GO

if exists (select * from sysobjects where id = object_id(N'[phpbb_banlist]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [phpbb_banlist]
GO

if exists (select * from sysobjects where id = object_id(N'[phpbb_categories]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [phpbb_categories]
GO

if exists (select * from sysobjects where id = object_id(N'[phpbb_config]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [phpbb_config]
GO

if exists (select * from sysobjects where id = object_id(N'[phpbb_disallow]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [phpbb_disallow]
GO

if exists (select * from sysobjects where id = object_id(N'[phpbb_forum_prune]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [phpbb_forum_prune]
GO

if exists (select * from sysobjects where id = object_id(N'[phpbb_forums]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [phpbb_forums]
GO

if exists (select * from sysobjects where id = object_id(N'[phpbb_groups]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [phpbb_groups]
GO

if exists (select * from sysobjects where id = object_id(N'[phpbb_posts]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [phpbb_posts]
GO

if exists (select * from sysobjects where id = object_id(N'[phpbb_posts_text]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [phpbb_posts_text]
GO

if exists (select * from sysobjects where id = object_id(N'[phpbb_privmsgs]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [phpbb_privmsgs]
GO

if exists (select * from sysobjects where id = object_id(N'[phpbb_privmsgs_text]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [phpbb_privmsgs_text]
GO

if exists (select * from sysobjects where id = object_id(N'[phpbb_ranks]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [phpbb_ranks]
GO

if exists (select * from sysobjects where id = object_id(N'[phpbb_session]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [phpbb_session]
GO

if exists (select * from sysobjects where id = object_id(N'[phpbb_smilies]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [phpbb_smilies]
GO

if exists (select * from sysobjects where id = object_id(N'[phpbb_themes]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [phpbb_themes]
GO

if exists (select * from sysobjects where id = object_id(N'[phpbb_themes_name]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [phpbb_themes_name]
GO

if exists (select * from sysobjects where id = object_id(N'[phpbb_topics]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [phpbb_topics]
GO

if exists (select * from sysobjects where id = object_id(N'[phpbb_topics_watch]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [phpbb_topics_watch]
GO

if exists (select * from sysobjects where id = object_id(N'[phpbb_user_group]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [phpbb_user_group]
GO

if exists (select * from sysobjects where id = object_id(N'[phpbb_users]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [phpbb_users]
GO

if exists (select * from sysobjects where id = object_id(N'[phpbb_vote_desc]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [phpbb_vote_desc]
GO

if exists (select * from sysobjects where id = object_id(N'[phpbb_vote_results]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [phpbb_vote_results]
GO

if exists (select * from sysobjects where id = object_id(N'[phpbb_vote_voters]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [phpbb_vote_voters]
GO

if exists (select * from sysobjects where id = object_id(N'[phpbb_words]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [phpbb_words]
GO

CREATE TABLE [phpbb_auth_access] (
	[group_id] [int] NULL ,
	[forum_id] [int] NULL ,
	[auth_view] [smallint] NOT NULL ,
	[auth_read] [smallint] NOT NULL ,
	[auth_post] [smallint] NOT NULL ,
	[auth_reply] [smallint] NOT NULL ,
	[auth_edit] [smallint] NOT NULL ,
	[auth_delete] [smallint] NOT NULL ,
	[auth_sticky] [smallint] NOT NULL ,
	[auth_announce] [smallint] NOT NULL ,
	[auth_vote] [smallint] NOT NULL ,
	[auth_pollcreate] [smallint] NOT NULL ,
	[auth_attachments] [smallint] NOT NULL ,
	[auth_mod] [smallint] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_banlist] (
	[ban_id] [int] IDENTITY (1, 1) NOT NULL ,
	[ban_userid] [int] NULL ,
	[ban_ip] [char] (8) NULL ,
	[ban_email] [varchar] (50) NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_categories] (
	[cat_id] [int] IDENTITY (1, 1) NOT NULL ,
	[cat_title] [varchar] (50) NOT NULL ,
	[cat_order] [int] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_config] (
	[config_id] [int] NULL ,
	[board_disable] [smallint] NOT NULL ,
	[board_startdate] [int] NULL ,
	[sitename] [varchar] (100) NOT NULL ,
	[cookie_name] [varchar] (25) NULL ,
	[cookie_path] [varchar] (50) NULL ,
	[cookie_domain] [varchar] (100) NULL ,
	[cookie_secure] [smallint] NULL ,
	[session_length] [int] NULL ,
	[allow_html] [smallint] NULL ,
	[allow_html_tags] [varchar] (255) NULL ,
	[allow_bbcode] [smallint] NULL ,
	[allow_smilies] [smallint] NULL ,
	[allow_sig] [smallint] NULL ,
	[allow_namechange] [smallint] NULL ,
	[allow_theme_create] [smallint] NULL ,
	[allow_avatar_local] [smallint] NULL ,
	[allow_avatar_remote] [smallint] NULL ,
	[allow_avatar_upload] [smallint] NULL ,
	[override_themes] [smallint] NULL ,
	[posts_per_page] [int] NULL ,
	[topics_per_page] [int] NULL ,
	[hot_threshold] [int] NULL ,
	[email_sig] [varchar] (255) NULL ,
	[email_from] [varchar] (255) NULL ,
	[smtp_delivery] [smallint] NULL ,
	[smtp_host] [varchar] (100) NULL ,
	[require_activation] [smallint] NULL ,
	[flood_interval] [int] NULL ,
	[max_poll_options] [int] NULL ,
	[avatar_filesize] [int] NULL ,
	[avatar_max_width] [smallint] NULL ,
	[avatar_max_height] [smallint] NULL ,
	[avatar_path] [varchar] (50) NULL ,
	[smilies_path] [varchar] (50) NULL ,
	[default_theme] [int] NULL ,
	[default_lang] [varchar] (50) NULL ,
	[default_dateformat] [varchar] (15) NULL ,
	[system_timezone] [int] NULL ,
	[sys_template] [varchar] (50) NULL ,
	[prune_enable] [smallint] NULL ,
	[gzip_compress] [smallint] NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_disallow] (
	[disallow_id] [int] IDENTITY (1, 1) NOT NULL ,
	[disallow_username] [varchar] (100) NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_forum_prune] (
	[prune_id] [int] IDENTITY (1, 1) NOT NULL ,
	[forum_id] [int] NOT NULL ,
	[prune_days] [int] NOT NULL ,
	[prune_freq] [int] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_forums] (
	[forum_id] [int] IDENTITY (1, 1) NOT NULL ,
	[cat_id] [int] NOT NULL ,
	[forum_name] [varchar] (100) NOT NULL ,
	[forum_desc] [varchar] (255) NULL ,
	[forum_status] [smallint] NOT NULL ,
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
	[auth_sticky] [smallint] NOT NULL ,
	[auth_announce] [smallint] NOT NULL ,
	[auth_vote] [smallint] NOT NULL ,
	[auth_pollcreate] [smallint] NOT NULL ,
	[auth_attachments] [smallint] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_groups] (
	[group_id] [int] NOT NULL ,
	[group_type] [smallint] NULL ,
	[group_name] [varchar] (50) NOT NULL ,
	[group_description] [varchar] (255) NOT NULL ,
	[group_moderator] [int] NULL ,
	[group_single_user] [smallint] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_posts] (
	[post_id] [int] IDENTITY (1, 1) NOT NULL ,
	[topic_id] [int] NOT NULL ,
	[forum_id] [int] NOT NULL ,
	[poster_id] [int] NOT NULL ,
	[post_time] [int] NOT NULL ,
	[poster_ip] [char] (8) NULL ,
	[post_username] [varchar] (50) NULL ,
	[enable_bbcode] [smallint] NULL ,
	[enable_html] [smallint] NULL ,
	[enable_smilies] [smallint] NULL ,
	[enable_sig] [smallint] NULL ,
	[bbcode_uid] [char] (10) NULL ,
	[post_edit_time] [int] NULL ,
	[post_edit_count] [smallint] NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_posts_text] (
	[post_id] [int] NOT NULL ,
	[post_subject] [varchar] (100) NULL ,
	[post_text] [text] NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [phpbb_privmsgs] (
	[privmsgs_id] [int] IDENTITY (1, 1) NOT NULL ,
	[privmsgs_type] [smallint] NOT NULL ,
	[privmsgs_subject] [varchar] (100) NOT NULL ,
	[privmsgs_from_userid] [int] NOT NULL ,
	[privmsgs_to_userid] [int] NOT NULL ,
	[privmsgs_date] [int] NOT NULL ,
	[privmsgs_ip] [char] (8) NOT NULL ,
	[privmsgs_enable_bbcode] [smallint] NULL ,
	[privmsgs_enable_html] [smallint] NULL ,
	[privmsgs_enable_smilies] [smallint] NULL ,
	[privmsgs_enable_sig] [smallint] NULL ,
	[privmsgs_bbcode_uid] [char] (10) NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_privmsgs_text] (
	[privmsgs_text_id] [int] NOT NULL ,
	[privmsgs_text] [text] NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [phpbb_ranks] (
	[rank_id] [int] IDENTITY (1, 1) NOT NULL ,
	[rank_title] [varchar] (50) NOT NULL ,
	[rank_min] [int] NULL ,
	[rank_max] [int] NULL ,
	[rank_special] [smallint] NULL ,
	[rank_image] [varchar] (50) NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_session] (
	[session_id] [char] (32) NOT NULL ,
	[session_user_id] [int] NOT NULL ,
	[session_start] [int] NULL ,
	[session_time] [int] NULL ,
	[session_last_visit] [int] NULL ,
	[session_ip] [char] (8) NOT NULL ,
	[session_page] [int] NULL ,
	[session_logged_in] [smallint] NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_smilies] (
	[smilies_id] [int] IDENTITY (1, 1) NOT NULL ,
	[code] [varchar] (10) NOT NULL ,
	[smile_url] [varchar] (50) NOT NULL ,
	[emoticon] [varchar] (50) NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_themes] (
	[themes_id] [int] IDENTITY (1, 1) NOT NULL ,
	[themes_name] [varchar] (50) NOT NULL ,
	[head_stylesheet] [varchar] (50) NULL ,
	[body_background] [varchar] (50) NULL ,
	[body_bgcolor] [char] (6) NULL ,
	[body_text] [char] (6) NULL ,
	[body_link] [char] (6) NULL ,
	[body_vlink] [char] (6) NULL ,
	[body_alink] [char] (6) NULL ,
	[body_hlink] [char] (6) NULL ,
	[tr_color1] [char] (6) NULL ,
	[tr_color2] [char] (6) NULL ,
	[tr_color3] [char] (6) NULL ,
	[tr_class1] [varchar] (25) NULL ,
	[tr_class2] [varchar] (25) NULL ,
	[tr_class3] [varchar] (25) NULL ,
	[th_color1] [char] (6) NULL ,
	[th_color2] [char] (6) NULL ,
	[th_color3] [char] (6) NULL ,
	[th_class1] [varchar] (25) NULL ,
	[th_class2] [varchar] (25) NULL ,
	[th_class3] [varchar] (25) NULL ,
	[td_color1] [char] (6) NULL ,
	[td_color2] [char] (6) NULL ,
	[td_color3] [char] (6) NULL ,
	[td_class1] [varchar] (25) NULL ,
	[td_class2] [varchar] (25) NULL ,
	[td_class3] [varchar] (25) NULL ,
	[fontface1] [varchar] (50) NULL ,
	[fontface2] [varchar] (50) NULL ,
	[fontface3] [varchar] (50) NULL ,
	[fontsize1] [smallint] NULL ,
	[fontsize2] [smallint] NULL ,
	[fontsize3] [smallint] NULL ,
	[fontcolor1] [char] (6) NULL ,
	[fontcolor2] [char] (6) NULL ,
	[fontcolor3] [char] (6) NULL ,
	[span_class1] [varchar] (25) NULL ,
	[span_class2] [varchar] (25) NULL ,
	[span_class3] [varchar] (25) NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_themes_name] (
	[themes_id] [int] NOT NULL ,
	[tr_color1_name] [varchar] (25) NULL ,
	[tr_color2_name] [varchar] (25) NULL ,
	[tr_color3_name] [varchar] (25) NULL ,
	[tr_class1_name] [varchar] (25) NULL ,
	[tr_class2_name] [varchar] (25) NULL ,
	[tr_class3_name] [varchar] (25) NULL ,
	[th_color1_name] [varchar] (25) NULL ,
	[th_color2_name] [varchar] (25) NULL ,
	[th_color3_name] [varchar] (25) NULL ,
	[th_class1_name] [varchar] (25) NULL ,
	[th_class2_name] [varchar] (25) NULL ,
	[th_class3_name] [varchar] (25) NULL ,
	[td_color1_name] [varchar] (25) NULL ,
	[td_color2_name] [varchar] (25) NULL ,
	[td_color3_name] [varchar] (25) NULL ,
	[td_class1_name] [varchar] (25) NULL ,
	[td_class2_name] [varchar] (25) NULL ,
	[td_class3_name] [varchar] (25) NULL ,
	[fontface1_name] [varchar] (25) NULL ,
	[fontface2_name] [varchar] (25) NULL ,
	[fontface3_name] [varchar] (25) NULL ,
	[fontsize1_name] [varchar] (25) NULL ,
	[fontsize2_name] [varchar] (25) NULL ,
	[fontsize3_name] [varchar] (25) NULL ,
	[fontcolor1_name] [varchar] (25) NULL ,
	[fontcolor2_name] [varchar] (25) NULL ,
	[fontcolor3_name] [varchar] (25) NULL ,
	[span_class1_name] [varchar] (25) NULL ,
	[span_class2_name] [varchar] (25) NULL ,
	[span_class3_name] [varchar] (25) NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_topics] (
	[topic_id] [int] IDENTITY (1, 1) NOT NULL ,
	[forum_id] [int] NOT NULL ,
	[topic_title] [varchar] (100) NOT NULL ,
	[topic_poster] [int] NOT NULL ,
	[topic_time] [int] NOT NULL ,
	[topic_views] [int] NOT NULL ,
	[topic_replies] [int] NOT NULL ,
	[topic_status] [smallint] NOT NULL ,
	[topic_type] [smallint] NOT NULL ,
	[topic_vote] [smallint] NOT NULL ,
	[topic_last_post_id] [int] NULL ,
	[topic_moved_id] [int] NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_topics_watch] (
	[topic_id] [int] NOT NULL ,
	[user_id] [int] NOT NULL ,
	[notify_status] [smallint] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_user_group] (
	[group_id] [int] NOT NULL ,
	[user_id] [int] NOT NULL ,
	[user_pending] [smallint] NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_users] (
	[user_id] [int] NOT NULL ,
	[user_active] [smallint] NULL ,
	[username] [varchar] (40) NOT NULL ,
	[user_password] [char] (32) NULL ,
	[user_autologin_key] [char] (32) NULL ,
	[user_level] [smallint] NULL ,
	[user_posts] [int] NOT NULL ,
	[user_timezone] [int] NOT NULL ,
	[user_dateformat] [varchar] (15) NOT NULL ,
	[user_template] [varchar] (50) NULL ,
	[user_theme] [int] NULL ,
	[user_lang] [varchar] (50) NULL ,
	[user_viewemail] [smallint] NULL ,
	[user_attachsig] [smallint] NULL ,
	[user_allowhtml] [smallint] NULL ,
	[user_allowbbcode] [smallint] NULL ,
	[user_allowsmile] [smallint] NULL ,
	[user_allowavatar] [smallint] NOT NULL ,
	[user_allow_pm] [smallint] NOT NULL ,
	[user_allow_viewonline] [smallint] NOT NULL ,
	[user_notify] [smallint] NOT NULL ,
	[user_notify_pm] [smallint] NOT NULL ,
	[user_regdate] [int] NOT NULL ,
	[user_rank] [int] NULL ,
	[user_avatar] [varchar] (100) NULL ,
	[user_email] [varchar] (25) NULL ,
	[user_icq] [varchar] (15) NULL ,
	[user_website] [varchar] (50) NULL ,
	[user_from] [varchar] (200) NULL ,
	[user_sig] [varchar] (255) NULL ,
	[user_aim] [varchar] (50) NULL ,
	[user_yim] [varchar] (50) NULL ,
	[user_msnm] [varchar] (50) NULL ,
	[user_occ] [varchar] (255) NULL ,
	[user_interests] [varchar] (255) NULL ,
	[user_actkey] [char] (32) NULL ,
	[user_newpasswd] [char] (32) NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_vote_desc] (
	[vote_id] [int] IDENTITY (1, 1) NOT NULL ,
	[topic_id] [int] NOT NULL ,
	[vote_text] [varchar] (255) NOT NULL ,
	[vote_start] [int] NOT NULL ,
	[vote_length] [int] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_vote_results] (
	[vote_id] [int] NOT NULL ,
	[vote_option_id] [int] NOT NULL ,
	[vote_option_text] [varchar] (255) NOT NULL ,
	[vote_result] [int] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_vote_voters] (
	[vote_id] [int] NOT NULL ,
	[vote_user_id] [int] NOT NULL ,
	[vote_user_ip] [char] (8) NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [phpbb_words] (
	[word_id] [int] IDENTITY (1, 1) NOT NULL ,
	[word] [varchar] (255) NOT NULL ,
	[replacement] [varchar] (255) NOT NULL 
) ON [PRIMARY]
GO

ALTER TABLE [phpbb_banlist] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_banlist] PRIMARY KEY  CLUSTERED 
	(
		[ban_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_categories] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_categories] PRIMARY KEY  CLUSTERED 
	(
		[cat_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_disallow] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_disallow] PRIMARY KEY  CLUSTERED 
	(
		[disallow_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_forum_prune] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_forum_prune] PRIMARY KEY  CLUSTERED 
	(
		[prune_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_forums] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_forums] PRIMARY KEY  CLUSTERED 
	(
		[forum_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_groups] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_groups] PRIMARY KEY  CLUSTERED 
	(
		[group_id]
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
		[privmsgs_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_privmsgs_text] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_privmsgs_text] PRIMARY KEY  CLUSTERED 
	(
		[privmsgs_text_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_ranks] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_ranks] PRIMARY KEY  CLUSTERED 
	(
		[rank_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_smilies] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_smilies] PRIMARY KEY  CLUSTERED 
	(
		[smilies_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_themes] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_themes] PRIMARY KEY  CLUSTERED 
	(
		[themes_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_themes_name] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_themes_name] PRIMARY KEY  CLUSTERED 
	(
		[themes_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_topics] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_topics] PRIMARY KEY  CLUSTERED 
	(
		[topic_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_users] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_users] PRIMARY KEY  CLUSTERED 
	(
		[user_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_vote_desc] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_vote_desc] PRIMARY KEY  CLUSTERED 
	(
		[vote_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_words] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_words] PRIMARY KEY  CLUSTERED 
	(
		[word_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [phpbb_auth_access] WITH NOCHECK ADD 
	CONSTRAINT [DF_phpbb_auth_access_auth_view] DEFAULT (0) FOR [auth_view],
	CONSTRAINT [DF_phpbb_auth_access_auth_read] DEFAULT (0) FOR [auth_read],
	CONSTRAINT [DF_phpbb_auth_access_auth_post] DEFAULT (0) FOR [auth_post],
	CONSTRAINT [DF_phpbb_auth_access_auth_reply] DEFAULT (0) FOR [auth_reply],
	CONSTRAINT [DF_phpbb_auth_access_auth_edit] DEFAULT (0) FOR [auth_edit],
	CONSTRAINT [DF_phpbb_auth_access_auth_delete] DEFAULT (0) FOR [auth_delete],
	CONSTRAINT [DF_phpbb_auth_access_auth_sticky] DEFAULT (0) FOR [auth_sticky],
	CONSTRAINT [DF_phpbb_auth_access_auth_announce] DEFAULT (0) FOR [auth_announce],
	CONSTRAINT [DF_phpbb_auth_access_auth_vote] DEFAULT (0) FOR [auth_vote],
	CONSTRAINT [DF_phpbb_auth_access_auth_pollcreate] DEFAULT (0) FOR [auth_pollcreate],
	CONSTRAINT [DF_phpbb_auth_access_auth_attachments] DEFAULT (0) FOR [auth_attachments],
	CONSTRAINT [DF_phpbb_auth_access_auth_mod] DEFAULT (0) FOR [auth_mod]
GO

ALTER TABLE [phpbb_forums] WITH NOCHECK ADD 
	CONSTRAINT [DF_phpbb_forums_forum_posts] DEFAULT (0) FOR [forum_posts],
	CONSTRAINT [DF_phpbb_forums_forum_topics] DEFAULT (0) FOR [forum_topics],
	CONSTRAINT [DF_phpbb_forums_forum_last_post_id] DEFAULT (0) FOR [forum_last_post_id],
	CONSTRAINT [DF_phpbb_forums_prune_enable] DEFAULT (0) FOR [prune_enable],
	CONSTRAINT [DF_phpbb_forums_auth_view] DEFAULT (0) FOR [auth_view],
	CONSTRAINT [DF_phpbb_forums_auth_read] DEFAULT (0) FOR [auth_read],
	CONSTRAINT [DF_phpbb_forums_auth_post] DEFAULT (0) FOR [auth_post],
	CONSTRAINT [DF_phpbb_forums_auth_reply] DEFAULT (0) FOR [auth_reply],
	CONSTRAINT [DF_phpbb_forums_auth_edit] DEFAULT (0) FOR [auth_edit],
	CONSTRAINT [DF_phpbb_forums_auth_delete] DEFAULT (0) FOR [auth_delete],
	CONSTRAINT [DF_phpbb_forums_auth_sticky] DEFAULT (0) FOR [auth_sticky],
	CONSTRAINT [DF_phpbb_forums_auth_announce] DEFAULT (0) FOR [auth_announce],
	CONSTRAINT [DF_phpbb_forums_auth_vote] DEFAULT (0) FOR [auth_vote],
	CONSTRAINT [DF_phpbb_forums_auth_pollcreate] DEFAULT (0) FOR [auth_pollcreate],
	CONSTRAINT [DF_phpbb_forums_auth_attachments] DEFAULT (0) FOR [auth_attachments]
GO

ALTER TABLE [phpbb_topics] WITH NOCHECK ADD 
	CONSTRAINT [DF_phpbb_topics_topic_views] DEFAULT (0) FOR [topic_views],
	CONSTRAINT [DF_phpbb_topics_topic_replies] DEFAULT (0) FOR [topic_replies],
	CONSTRAINT [DF_phpbb_topics_topic_status] DEFAULT (0) FOR [topic_status],
	CONSTRAINT [DF_phpbb_topics_topic_type] DEFAULT (0) FOR [topic_type],
	CONSTRAINT [DF_phpbb_topics_topic_vote] DEFAULT (0) FOR [topic_vote]
GO

ALTER TABLE [phpbb_users] WITH NOCHECK ADD 
	CONSTRAINT [DF_phpbb_users_user_posts] DEFAULT (0) FOR [user_posts],
	CONSTRAINT [DF_phpbb_users_user_viewemail] DEFAULT (1) FOR [user_viewemail],
	CONSTRAINT [DF_phpbb_users_user_attachsig] DEFAULT (1) FOR [user_attachsig],
	CONSTRAINT [DF_phpbb_users_user_allowhtml] DEFAULT (0) FOR [user_allowhtml],
	CONSTRAINT [DF_phpbb_users_user_allowbbcode] DEFAULT (1) FOR [user_allowbbcode],
	CONSTRAINT [DF_phpbb_users_user_allowsmile] DEFAULT (1) FOR [user_allowsmile],
	CONSTRAINT [DF_phpbb_users_user_allowavatar] DEFAULT (1) FOR [user_allowavatar],
	CONSTRAINT [DF_phpbb_users_user_allow_pm] DEFAULT (1) FOR [user_allow_pm],
	CONSTRAINT [DF_phpbb_users_user_allow_viewonline] DEFAULT (1) FOR [user_allow_viewonline],
	CONSTRAINT [DF_phpbb_users_user_notify] DEFAULT (1) FOR [user_notify],
	CONSTRAINT [DF_phpbb_users_user_notify_pm] DEFAULT (1) FOR [user_notify_pm]
GO

 CREATE  INDEX [IX_phpbb_auth_access] ON [phpbb_auth_access]([group_id], [forum_id]) ON [PRIMARY]
GO

 CREATE  INDEX [IX_phpbb_banlist] ON [phpbb_banlist]([ban_userid], [ban_ip]) ON [PRIMARY]
GO

 CREATE  INDEX [IX_phpbb_categories] ON [phpbb_categories]([cat_order]) ON [PRIMARY]
GO

 CREATE  INDEX [IX_phpbb_forum_prune] ON [phpbb_forum_prune]([forum_id]) ON [PRIMARY]
GO

 CREATE  INDEX [IX_phpbb_forums] ON [phpbb_forums]([cat_id], [forum_order], [forum_last_post_id]) ON [PRIMARY]
GO

 CREATE  INDEX [IX_phpbb_groups] ON [phpbb_groups]([group_single_user]) ON [PRIMARY]
GO

 CREATE  INDEX [IX_phpbb_posts] ON [phpbb_posts]([topic_id], [forum_id], [poster_id]) ON [PRIMARY]
GO

 CREATE  INDEX [IX_phpbb_posts_text] ON [phpbb_posts_text]([post_id]) ON [PRIMARY]
GO

 CREATE  INDEX [IX_phpbb_privmsgs] ON [phpbb_privmsgs]([privmsgs_from_userid], [privmsgs_to_userid]) ON [PRIMARY]
GO

 CREATE  INDEX [IX_phpbb_ranks] ON [phpbb_ranks]([rank_min], [rank_max], [rank_special]) ON [PRIMARY]
GO

 CREATE  INDEX [IX_phpbb_session] ON [phpbb_session]([session_id], [session_user_id], [session_ip], [session_logged_in]) ON [PRIMARY]
GO

 CREATE  INDEX [IX_phpbb_topics] ON [phpbb_topics]([forum_id], [topic_type], [topic_last_post_id]) ON [PRIMARY]
GO

 CREATE  INDEX [IX_phpbb_topics_watch] ON [phpbb_topics_watch]([topic_id], [user_id]) ON [PRIMARY]
GO

 CREATE  INDEX [IX_phpbb_user_group] ON [phpbb_user_group]([group_id], [user_id]) ON [PRIMARY]
GO

 CREATE  INDEX [IX_phpbb_users] ON [phpbb_users]([user_level]) ON [PRIMARY]
GO

 CREATE  INDEX [IX_phpbb_vote_desc] ON [phpbb_vote_desc]([topic_id]) ON [PRIMARY]
GO

 CREATE  INDEX [IX_phpbb_vote_results] ON [phpbb_vote_results]([vote_id]) ON [PRIMARY]
GO

 CREATE  INDEX [IX_phpbb_vote_results_1] ON [phpbb_vote_results]([vote_option_id]) ON [PRIMARY]
GO

 CREATE  INDEX [IX_phpbb_vote_voters] ON [phpbb_vote_voters]([vote_id]) ON [PRIMARY]
GO

 CREATE  INDEX [IX_phpbb_vote_voters_1] ON [phpbb_vote_voters]([vote_user_id]) ON [PRIMARY]
GO

ALTER TABLE [phpbb_posts_text] ADD 
	CONSTRAINT [FK_phpbb_posts_text_phpbb_posts] FOREIGN KEY 
	(
		[post_id]
	) REFERENCES [phpbb_posts] (
		[post_id]
	)
GO

ALTER TABLE [phpbb_privmsgs_text] ADD 
	CONSTRAINT [FK_phpbb_privmsgs_text_phpbb_privmsgs] FOREIGN KEY 
	(
		[privmsgs_text_id]
	) REFERENCES [phpbb_privmsgs] (
		[privmsgs_id]
	)
GO

ALTER TABLE [phpbb_themes_name] ADD 
	CONSTRAINT [FK_phpbb_themes_name_phpbb_themes] FOREIGN KEY 
	(
		[themes_id]
	) REFERENCES [phpbb_themes] (
		[themes_id]
	)
GO

ALTER TABLE [phpbb_topics_watch] ADD 
	CONSTRAINT [FK_phpbb_topics_watch_phpbb_topics] FOREIGN KEY 
	(
		[topic_id]
	) REFERENCES [phpbb_topics] (
		[topic_id]
	),
	CONSTRAINT [FK_phpbb_topics_watch_phpbb_users] FOREIGN KEY 
	(
		[user_id]
	) REFERENCES [phpbb_users] (
		[user_id]
	)
GO

ALTER TABLE [phpbb_user_group] ADD 
	CONSTRAINT [FK_phpbb_user_group_phpbb_groups] FOREIGN KEY 
	(
		[group_id]
	) REFERENCES [phpbb_groups] (
		[group_id]
	),
	CONSTRAINT [FK_phpbb_user_group_phpbb_users] FOREIGN KEY 
	(
		[user_id]
	) REFERENCES [phpbb_users] (
		[user_id]
	)
GO
