#
# phpBB2 PostgreSQL DB schema - phpBB team 2001
#
#
# $Id$
#

# --------------------------------------------------------
#
# Table structure for table 'phpbb_banlist'
#

CREATE TABLE phpbb_banlist (
   ban_id SERIAL PRIMARY KEY,
   ban_userid int,
   ban_ip int,
   ban_start int,
   ban_end int,
   ban_time_type int
);
CREATE INDEX banlist_ban_id ON phpbb_banlist (ban_id);

# --------------------------------------------------------
#
# Table structure for table 'phpbb_categories'
#

CREATE TABLE phpbb_categories (
   cat_id SERIAL PRIMARY KEY,
   cat_title varchar(100),
   cat_order varchar(10)
);
CREATE INDEX categories_cat_id ON phpbb_categories (cat_id);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_config'
#

CREATE TABLE phpbb_config (
   config_id SERIAL PRIMARY KEY,
   sitename varchar(100),
   allow_html int2,
   allow_bbcode int2,
   allow_sig int2,
   allow_namechange int2,
   require_activation int2,
   selected int2 DEFAULT 0 NOT NULL UNIQUE,
   posts_per_page int,
   hot_threshold int,
   topics_per_page int,
   allow_theme_create int,
   override_themes int2,
   email_sig varchar(255),
   email_from varchar(100),
   system_timezone varchar(4),
   default_lang varchar(255)
);
CREATE INDEX config_config_id ON phpbb_config (config_id);

# --------------------------------------------------------
#
# Table structure for table 'phpbb_disallow'
#

CREATE TABLE phpbb_disallow (
   disallow_id SERIAL,
   disallow_username varchar(50)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_forum_access'
#

CREATE TABLE phpbb_forum_access (
   forum_id SERIAL PRIMARY KEY,
   user_id int,
   can_post int2 DEFAULT 0 NOT NULL
);
CREATE INDEX forum_access_forum_id ON phpbb_forum_access (forum_id);
CREATE INDEX forum_access_user_id ON phpbb_forum_access (user_id);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_forum_mods'
#

CREATE TABLE phpbb_forum_mods (
   forum_id int NOT NULL DEFAULT 0,
   user_id int NOT NULL DEFAULT 0,
   mod_notify int2
);
CREATE INDEX forum_mods_forum_id ON phpbb_forum_mods (forum_id);
CREATE INDEX forum_mods_user_id ON phpbb_forum_mods (user_id);

# --------------------------------------------------------
#
# Table structure for table 'phpbb_forums'
#

CREATE TABLE phpbb_forums (
   forum_id SERIAL PRIMARY KEY,
   forum_name varchar(150),
   forum_desc text,
   forum_access int2,
   cat_id int,
   forum_order int DEFAULT '1' NOT NULL,
   forum_type int2,
   forum_posts int DEFAULT '0' NOT NULL,
   forum_topics int DEFAULT '0' NOT NULL,
   forum_last_post_id int DEFAULT '0' NOT NULL
);
CREATE INDEX forums_forum_id ON phpbb_forums (forum_id);
CREATE INDEX forums_forum_order ON phpbb_forums (forum_order);
CREATE INDEX forums_cat_id ON phpbb_forums (cat_id);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_headermetafooter'
#

CREATE TABLE phpbb_headermetafooter (
   header text,
   meta text,
   footer text
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_posts'
#

CREATE TABLE phpbb_posts (
   post_id SERIAL PRIMARY KEY,
   topic_id int DEFAULT '0' NOT NULL,
   forum_id int DEFAULT '0' NOT NULL,
   poster_id int DEFAULT '0' NOT NULL,
   post_time int DEFAULT '0' NOT NULL,
   poster_ip int DEFAULT '0' NOT NULL
);
CREATE INDEX posts_post_id ON phpbb_posts (post_id);
CREATE INDEX posts_forum_id ON phpbb_posts (forum_id);
CREATE INDEX posts_topic_id ON phpbb_posts (topic_id);
CREATE INDEX posts_poster_id ON phpbb_posts (poster_id);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_posts_text'
#

CREATE TABLE phpbb_posts_text (
   post_id int DEFAULT '0' NOT NULL PRIMARY KEY,
   post_text text
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_priv_msgs'
#

CREATE TABLE phpbb_priv_msgs (
   msg_id SERIAL PRIMARY KEY,
   from_userid int DEFAULT '0' NOT NULL,
   to_userid int DEFAULT '0' NOT NULL,
   msg_time int DEFAULT '0' NOT NULL,
   poster_ip int DEFAULT '0' NOT NULL,
   msg_status int DEFAULT '0' NOT NULL,
   msg_text text NOT NULL
);
CREATE INDEX priv_msgs_to_userid ON phpbb_priv_msgs (to_userid);

# --------------------------------------------------------
#
# Table structure for table 'phpbb_ranks'
#

CREATE TABLE phpbb_ranks (
   rank_id SERIAL PRIMARY KEY,
   rank_title varchar(50) NOT NULL,
   rank_min int DEFAULT '0' NOT NULL,
   rank_max int DEFAULT '0' NOT NULL,
   rank_special int2 DEFAULT '0',
   rank_image varchar(255)
);
CREATE INDEX ranks_rank_min ON phpbb_ranks (rank_min);
CREATE INDEX ranks_rank_max ON phpbb_ranks (rank_max);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_sessions'
#

CREATE TABLE phpbb_sessions (
   sess_id int4 DEFAULT '0' NOT NULL PRIMARY KEY,
   user_id int DEFAULT '0' NOT NULL,
   start_time int4 DEFAULT '0' NOT NULL,
   remote_ip int DEFAULT '0' NOT NULL,
   username varchar(40),
   forum int
);
CREATE INDEX sessions_start_time ON phpbb_sessions (start_time);
CREATE INDEX sessions_remote_ip ON phpbb_sessions (remote_ip);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_themes'
#

CREATE TABLE phpbb_themes (
   theme_id SERIAL PRIMARY KEY,
   theme_name varchar(35),
   bgcolor varchar(10),
   textcolor varchar(10),
   color1 varchar(10),
   color2 varchar(10),
   table_bgcolor varchar(10),
   header_image varchar(50),
   newtopic_image varchar(50),
   reply_image varchar(50),
   linkcolor varchar(15),
   vlinkcolor varchar(15),
   theme_default int2 DEFAULT '0',
   fontface varchar(100),
   fontsize1 varchar(5),
   fontsize2 varchar(5),
   fontsize3 varchar(5),
   fontsize4 varchar(5),
   tablewidth varchar(10),
   replylocked_image varchar(255)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_topics'
#

CREATE TABLE phpbb_topics (
   topic_id SERIAL PRIMARY KEY,
   topic_title varchar(100) NOT NULL,
   topic_poster int DEFAULT '0' NOT NULL,
   topic_time int DEFAULT '0' NOT NULL,
   topic_views int DEFAULT '0' NOT NULL,
   topic_replies int DEFAULT '0' NOT NULL,
   forum_id int DEFAULT '0' NOT NULL,
   topic_status int2 DEFAULT '0' NOT NULL,
   topic_notify int2 DEFAULT '0',
   topic_last_post_id int DEFAULT '0' NOT NULL
);
CREATE INDEX topics_topic_id ON phpbb_topics (topic_id);
CREATE INDEX topics_forum_id ON phpbb_topics (forum_id);

# --------------------------------------------------------
#
# Table structure for table 'phpbb_users'
#

CREATE TABLE phpbb_users (
   user_id SERIAL PRIMARY KEY,
   username varchar(40) NOT NULL,
   user_regdate varchar(20) NOT NULL,
   user_password varchar(32) NOT NULL,
   user_email varchar(255),
   user_icq varchar(15),
   user_website varchar(100),
   user_occ varchar(100),
   user_from varchar(100),
   user_intrest varchar(150),
   user_sig varchar(255),
   user_viewemail int2,
   user_theme int,
   user_aim varchar(255),
   user_yim varchar(255),
   user_msnm varchar(255),
   user_posts int DEFAULT '0',
   user_attachsig int2,
   user_desmile int2,
   user_html int2,
   user_bbcode int2,
   user_rank int DEFAULT '0',
   user_level int DEFAULT '1',
   user_lang varchar(255),
   user_timezone varchar(4),
   user_active int2,
   user_actkey varchar(32),
   user_newpasswd varchar(32),
   user_notify int2
);
CREATE INDEX users_user_id ON phpbb_users (user_id);

# --------------------------------------------------------
#
# Table structure for table 'phpbb_whosonline'
#

CREATE TABLE phpbb_whosonline (
   id SERIAL PRIMARY KEY,
   ip varchar(255),
   name varchar(255),
   count varchar(255),
   date varchar(255),
   username varchar(40),
   forum int
);
CREATE INDEX whosonline_id ON phpbb_whosonline (id);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_words'
#

CREATE TABLE phpbb_words (
   word_id SERIAL PRIMARY KEY,
   word varchar(100) NOT NULL,
   replacement varchar(100) NOT NULL
);
CREATE INDEX words_word_id ON phpbb_words (word_id);

