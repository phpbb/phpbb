#
# phpBB2 - MySQL schema
#
# $Id$
#

#
# Table structure for table 'phpbb_auth_access'
#
CREATE TABLE phpbb_auth_access (
   group_id mediumint(8) DEFAULT '0' NOT NULL,
   forum_id smallint(5) UNSIGNED DEFAULT '0' NOT NULL,
   auth_view tinyint(1) DEFAULT '0' NOT NULL,
   auth_read tinyint(1) DEFAULT '0' NOT NULL,
   auth_post tinyint(1) DEFAULT '0' NOT NULL,
   auth_reply tinyint(1) DEFAULT '0' NOT NULL,
   auth_edit tinyint(1) DEFAULT '0' NOT NULL,
   auth_delete tinyint(1) DEFAULT '0' NOT NULL,
   auth_sticky tinyint(1) DEFAULT '0' NOT NULL,
   auth_announce tinyint(1) DEFAULT '0' NOT NULL,
   auth_vote tinyint(1) DEFAULT '0' NOT NULL,
   auth_pollcreate tinyint(1) DEFAULT '0' NOT NULL,
   auth_attachments tinyint(1) DEFAULT '0' NOT NULL,
   auth_mod tinyint(1) DEFAULT '0' NOT NULL, 
   KEY group_id (group_id),
   KEY forum_id (forum_id)
);


#
# Table structure for table 'phpbb_user_group'
#
CREATE TABLE phpbb_user_group (
   group_id mediumint(8) DEFAULT '0' NOT NULL,
   user_id mediumint(8) DEFAULT '0' NOT NULL,
   user_pending tinyint(1), 
   KEY group_id (group_id),
   KEY user_id (user_id)
);

#
# Table structure for table 'phpbb_groups'
#
CREATE TABLE phpbb_groups (
   group_id mediumint(8) NOT NULL auto_increment,
   group_type tinyint(4) DEFAULT '1' NOT NULL, 
   group_name varchar(40) NOT NULL,
   group_description varchar(255) NOT NULL,
   group_moderator mediumint(8) DEFAULT '0' NOT NULL, 
   group_single_user tinyint(1) DEFAULT '1' NOT NULL, 
   PRIMARY KEY (group_id), 
   KEY group_single_user (group_single_user)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_banlist'
#
CREATE TABLE phpbb_banlist (
   ban_id mediumint(8) UNSIGNED NOT NULL auto_increment,
   ban_userid mediumint(8) NOT NULL,
   ban_ip char(8) NOT NULL,
   ban_email varchar(255),
   PRIMARY KEY (ban_id), 
   KEY ban_ip_user_id (ban_ip, ban_userid)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_categories'
#
CREATE TABLE phpbb_categories (
   cat_id mediumint(8) UNSIGNED NOT NULL auto_increment,
   cat_title varchar(100),
   cat_order mediumint(8) UNSIGNED NOT NULL,
   PRIMARY KEY (cat_id), 
   KEY cat_order (cat_order)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_config'
#
CREATE TABLE phpbb_config ( 
    config_name varchar(255) NOT NULL, 
    config_value varchar(255) NOT NULL, 
    PRIMARY KEY (config_name)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_disallow'
#
CREATE TABLE phpbb_disallow (
   disallow_id mediumint(8) UNSIGNED NOT NULL auto_increment,
   disallow_username varchar(25),
   PRIMARY KEY (disallow_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_forum_prune'
#
CREATE TABLE phpbb_forum_prune (
   prune_id mediumint(8) UNSIGNED NOT NULL auto_increment,
   forum_id smallint(5) UNSIGNED NOT NULL,
   prune_days tinyint(4) UNSIGNED NOT NULL,
   prune_freq tinyint(4) UNSIGNED NOT NULL,
   PRIMARY KEY(prune_id),
   KEY forum_id (forum_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_forums'
#
CREATE TABLE phpbb_forums (
   forum_id smallint(5) UNSIGNED NOT NULL auto_increment,
   cat_id mediumint(8) UNSIGNED NOT NULL,
   forum_name varchar(150),
   forum_desc text,
   forum_status tinyint(4) DEFAULT '0' NOT NULL, 
   forum_order mediumint(8) UNSIGNED DEFAULT '1' NOT NULL,
   forum_posts mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
   forum_topics mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
   forum_last_post_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
   prune_next int(11),
   prune_enable tinyint(1) DEFAULT '1' NOT NULL,
   auth_view tinyint(2) DEFAULT '0' NOT NULL,
   auth_read tinyint(2) DEFAULT '0' NOT NULL,
   auth_post tinyint(2) DEFAULT '0' NOT NULL,
   auth_reply tinyint(2) DEFAULT '0' NOT NULL,
   auth_edit tinyint(2) DEFAULT '0' NOT NULL,
   auth_delete tinyint(2) DEFAULT '0' NOT NULL,
   auth_sticky tinyint(2) DEFAULT '0' NOT NULL,
   auth_announce tinyint(2) DEFAULT '0' NOT NULL,
   auth_vote tinyint(2) DEFAULT '0' NOT NULL,
   auth_pollcreate tinyint(2) DEFAULT '0' NOT NULL,
   auth_attachments tinyint(2) DEFAULT '0' NOT NULL,
   PRIMARY KEY (forum_id),
   KEY forums_order (forum_order),
   KEY cat_id (cat_id), 
   KEY forum_last_post_id (forum_last_post_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_posts'
#
CREATE TABLE phpbb_posts (
   post_id mediumint(8) UNSIGNED NOT NULL auto_increment,
   topic_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
   forum_id smallint(5) UNSIGNED DEFAULT '0' NOT NULL,
   poster_id mediumint(8) DEFAULT '0' NOT NULL,
   post_time int(11) DEFAULT '0' NOT NULL,
   poster_ip char(8) NOT NULL, 
   post_username varchar(25), 
   enable_bbcode tinyint(1) DEFAULT '1' NOT NULL,
   enable_html tinyint(1) DEFAULT '0' NOT NULL,
   enable_smilies tinyint(1) DEFAULT '1' NOT NULL,
   enable_sig tinyint(1) DEFAULT '1' NOT NULL, 
   post_edit_time int(11),
   post_edit_count smallint(5) UNSIGNED DEFAULT '0' NOT NULL,
   PRIMARY KEY (post_id),
   KEY forum_id (forum_id),
   KEY topic_id (topic_id),
   KEY poster_id (poster_id), 
   KEY post_time (post_time)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_posts_text'
#
CREATE TABLE phpbb_posts_text (
   post_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
   bbcode_uid char(10) NOT NULL,
   post_subject char(60),
   post_text text,
   PRIMARY KEY (post_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_privmsgs'
#
CREATE TABLE phpbb_privmsgs (
   privmsgs_id mediumint(8) UNSIGNED NOT NULL auto_increment,
   privmsgs_type tinyint(4) DEFAULT '0' NOT NULL,
   privmsgs_subject varchar(255) DEFAULT '0' NOT NULL,
   privmsgs_from_userid mediumint(8) DEFAULT '0' NOT NULL,
   privmsgs_to_userid mediumint(8) DEFAULT '0' NOT NULL,
   privmsgs_date int(11) DEFAULT '0' NOT NULL,
   privmsgs_ip char(8) NOT NULL,
   privmsgs_enable_bbcode tinyint(1) DEFAULT '1' NOT NULL,
   privmsgs_enable_html tinyint(1) DEFAULT '0' NOT NULL,
   privmsgs_enable_smilies tinyint(1) DEFAULT '1' NOT NULL, 
   privmsgs_attach_sig tinyint(1) DEFAULT '1' NOT NULL, 
   PRIMARY KEY (privmsgs_id),
   KEY privmsgs_from_userid (privmsgs_from_userid),
   KEY privmsgs_to_userid (privmsgs_to_userid)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_privmsgs_text'
#
CREATE TABLE phpbb_privmsgs_text (
   privmsgs_text_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
   privmsgs_bbcode_uid char(10) DEFAULT '0' NOT NULL, 
   privmsgs_text text,
   PRIMARY KEY (privmsgs_text_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_ranks'
#
CREATE TABLE phpbb_ranks (
   rank_id smallint(5) UNSIGNED NOT NULL auto_increment,
   rank_title varchar(50) NOT NULL,
   rank_min mediumint(8) DEFAULT '0' NOT NULL,
   rank_max mediumint(8) DEFAULT '0' NOT NULL,
   rank_special tinyint(1) DEFAULT '0',
   rank_image varchar(255),
   PRIMARY KEY (rank_id) 
);


# --------------------------------------------------------
#
# Table structure for table `phpbb_search_results`
#
CREATE TABLE phpbb_search_results (
  search_id int(11) UNSIGNED NOT NULL default '0',
  session_id char(32) NOT NULL default '',
  search_array text NOT NULL,
  PRIMARY KEY  (search_id),
  KEY session_id (session_id)
);


# --------------------------------------------------------
#
# Table structure for table `phpbb_search_wordlist`
#
CREATE TABLE phpbb_search_wordlist (
  word_text varchar(50) binary NOT NULL default '',
  word_id mediumint(8) UNSIGNED NOT NULL auto_increment,
  word_common tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY (word_text), 
  KEY word_id (word_id)
);

# --------------------------------------------------------
#
# Table structure for table `phpbb_search_wordmatch`
#
CREATE TABLE phpbb_search_wordmatch (
  post_id mediumint(8) UNSIGNED NOT NULL default '0',
  word_id mediumint(8) UNSIGNED NOT NULL default '0',
  title_match tinyint(1) NOT NULL default '0',
  KEY word_id (word_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_sessions'
#
# Note that if you're running 3.23.x you may want to make
# this table a type HEAP. This type of table is stored
# within system memory and therefore for big busy boards
# is likely to be noticeably faster than continually
# writing to disk ... 
#
# I must admit I read about this type on vB's board.
# Hey, I never said you cannot get basic ideas from
# competing boards, just that I find it's best not to
# look at any code ... !
#
CREATE TABLE phpbb_sessions (
   session_id char(32) DEFAULT '' NOT NULL,
   session_user_id mediumint(8) DEFAULT '0' NOT NULL,
   session_start int(11) DEFAULT '0' NOT NULL,
   session_time int(11) DEFAULT '0' NOT NULL,
   session_ip char(8) DEFAULT '0' NOT NULL,
   session_page int(11) DEFAULT '0' NOT NULL,
   session_logged_in tinyint(1) DEFAULT '0' NOT NULL,
   PRIMARY KEY (session_id),
   KEY session_user_id (session_user_id),
   KEY session_id_ip_user_id (session_id, session_ip, session_user_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_smilies'
#
CREATE TABLE phpbb_smilies (
   smilies_id smallint(5) UNSIGNED NOT NULL auto_increment,
   code varchar(50),
   smile_url varchar(100),
   emoticon varchar(75),
   PRIMARY KEY (smilies_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_themes'
#
CREATE TABLE phpbb_themes (
   themes_id mediumint(8) UNSIGNED NOT NULL auto_increment, 
   template_name varchar(30) NOT NULL default '', 
   style_name varchar(30) NOT NULL default '',
   head_stylesheet varchar(100) default NULL,
   body_background varchar(100) default NULL,
   body_bgcolor varchar(6) default NULL,
   body_text varchar(6) default NULL,
   body_link varchar(6) default NULL,
   body_vlink varchar(6) default NULL,
   body_alink varchar(6) default NULL,
   body_hlink varchar(6) default NULL,
   tr_color1 varchar(6) default NULL,
   tr_color2 varchar(6) default NULL,
   tr_color3 varchar(6) default NULL,
   tr_class1 varchar(25) default NULL,
   tr_class2 varchar(25) default NULL,
   tr_class3 varchar(25) default NULL,
   th_color1 varchar(6) default NULL,
   th_color2 varchar(6) default NULL,
   th_color3 varchar(6) default NULL,
   th_class1 varchar(25) default NULL,
   th_class2 varchar(25) default NULL,
   th_class3 varchar(25) default NULL,
   td_color1 varchar(6) default NULL,
   td_color2 varchar(6) default NULL,
   td_color3 varchar(6) default NULL,
   td_class1 varchar(25) default NULL,
   td_class2 varchar(25) default NULL,
   td_class3 varchar(25) default NULL,
   fontface1 varchar(50) default NULL,
   fontface2 varchar(50) default NULL,
   fontface3 varchar(50) default NULL,
   fontsize1 tinyint(4) default NULL,
   fontsize2 tinyint(4) default NULL,
   fontsize3 tinyint(4) default NULL,
   fontcolor1 varchar(6) default NULL,
   fontcolor2 varchar(6) default NULL,
   fontcolor3 varchar(6) default NULL,
   span_class1 varchar(25) default NULL,
   span_class2 varchar(25) default NULL,
   span_class3 varchar(25) default NULL, 
   img_size_poll smallint(5) UNSIGNED, 
   img_size_privmsg smallint(5) UNSIGNED, 
   PRIMARY KEY  (themes_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_themes_name'
#
CREATE TABLE phpbb_themes_name (
   themes_id smallint(5) UNSIGNED DEFAULT '0' NOT NULL,
   tr_color1_name char(50),
   tr_color2_name char(50),
   tr_color3_name char(50),
   tr_class1_name char(50),
   tr_class2_name char(50),
   tr_class3_name char(50),
   th_color1_name char(50),
   th_color2_name char(50),
   th_color3_name char(50),
   th_class1_name char(50),
   th_class2_name char(50),
   th_class3_name char(50),
   td_color1_name char(50),
   td_color2_name char(50),
   td_color3_name char(50),
   td_class1_name char(50),
   td_class2_name char(50),
   td_class3_name char(50),
   fontface1_name char(50),
   fontface2_name char(50),
   fontface3_name char(50),
   fontsize1_name char(50),
   fontsize2_name char(50),
   fontsize3_name char(50),
   fontcolor1_name char(50),
   fontcolor2_name char(50),
   fontcolor3_name char(50),
   span_class1_name char(50),
   span_class2_name char(50),
   span_class3_name char(50),
   PRIMARY KEY (themes_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_topics'
#
CREATE TABLE phpbb_topics (
   topic_id mediumint(8) UNSIGNED NOT NULL auto_increment,
   forum_id smallint(8) UNSIGNED DEFAULT '0' NOT NULL,
   topic_title char(60) NOT NULL,
   topic_poster mediumint(8) DEFAULT '0' NOT NULL,
   topic_time int(11) DEFAULT '0' NOT NULL,
   topic_views mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
   topic_replies mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
   topic_status tinyint(3) DEFAULT '0' NOT NULL,
   topic_vote tinyint(1) DEFAULT '0' NOT NULL,
   topic_type tinyint(3) DEFAULT '0' NOT NULL,
   topic_last_post_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
   topic_moved_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
   PRIMARY KEY (topic_id),
   KEY forum_id (forum_id),
   KEY topic_moved_id (topic_moved_id),
   KEY topic_status (topic_status) 
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_topics_watch'
#
CREATE TABLE phpbb_topics_watch (
  topic_id mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  user_id mediumint(8) NOT NULL DEFAULT '0',
  notify_status tinyint(1) NOT NULL default '0',
  KEY topic_id (topic_id),
  KEY user_id (user_id), 
  KEY notify_status (notify_status)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_users'
#
CREATE TABLE phpbb_users (
   user_id mediumint(8) NOT NULL auto_increment,
   user_active tinyint(1) DEFAULT '1',
   username varchar(25) NOT NULL,
   user_password varchar(32) NOT NULL,
   user_session_time int(11) DEFAULT '0' NOT NULL, 
   user_session_page smallint(5) DEFAULT '0' NOT NULL, 
   user_lastvisit int(11) DEFAULT '0' NOT NULL, 
   user_regdate int(11) DEFAULT '0' NOT NULL, 
   user_level tinyint(4) DEFAULT '0',
   user_posts mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
   user_timezone tinyint(4) DEFAULT '0' NOT NULL,
   user_style tinyint(4),
   user_lang varchar(255),
   user_dateformat varchar(14) DEFAULT 'd M Y H:i' NOT NULL,
   user_new_privmsg smallint(5) UNSIGNED DEFAULT '0' NOT NULL, 
   user_unread_privmsg smallint(5) UNSIGNED DEFAULT '0' NOT NULL, 
   user_last_privmsg int(11) DEFAULT '0' NOT NULL, 
   user_emailtime int(11), 
   user_viewemail tinyint(1), 
   user_attachsig tinyint(1), 
   user_allowhtml tinyint(1) DEFAULT '1', 
   user_allowbbcode tinyint(1) DEFAULT '1', 
   user_allowsmile tinyint(1) DEFAULT '1', 
   user_allowavatar tinyint(1) DEFAULT '1' NOT NULL, 
   user_allow_pm tinyint(1) DEFAULT '1' NOT NULL, 
   user_allow_viewonline tinyint(1) DEFAULT '1' NOT NULL, 
   user_notify tinyint(1) DEFAULT '1' NOT NULL,
   user_notify_pm tinyint(1) DEFAULT '1' NOT NULL, 
   user_popup_pm tinyint(1) DEFAULT '0' NOT NULL, 
   user_rank int(11) DEFAULT '0',
   user_avatar varchar(100),
   user_avatar_type tinyint(4) DEFAULT '0' NOT NULL, 
   user_email varchar(255),
   user_icq varchar(15),
   user_website varchar(100),
   user_from varchar(100),
   user_sig text,
   user_sig_bbcode_uid char(10),
   user_aim varchar(255),
   user_yim varchar(255),
   user_msnm varchar(255),
   user_occ varchar(100),
   user_interests varchar(255),
   user_actkey varchar(32),
   user_newpasswd varchar(32),
   PRIMARY KEY (user_id), 
   KEY user_session_time (user_session_time)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_vote_desc'
#
CREATE TABLE phpbb_vote_desc (
  vote_id mediumint(8) UNSIGNED NOT NULL auto_increment,
  topic_id mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  vote_text text NOT NULL,
  vote_start int(11) NOT NULL DEFAULT '0',
  vote_length int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY  (vote_id),
  KEY topic_id (topic_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_vote_results'
#
CREATE TABLE phpbb_vote_results (
  vote_id mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  vote_option_id tinyint(4) UNSIGNED NOT NULL DEFAULT '0',
  vote_option_text varchar(255) NOT NULL,
  vote_result int(11) NOT NULL DEFAULT '0',
  KEY vote_option_id (vote_option_id),
  KEY vote_id (vote_id)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_vote_voters'
#
CREATE TABLE phpbb_vote_voters (
  vote_id mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  vote_user_id mediumint(8) NOT NULL DEFAULT '0',
  vote_user_ip char(8) NOT NULL,
  KEY vote_id (vote_id),
  KEY vote_user_id (vote_user_id),
  KEY vote_user_ip (vote_user_ip)
);


# --------------------------------------------------------
#
# Table structure for table 'phpbb_words'
#
CREATE TABLE phpbb_words (
   word_id mediumint(8) UNSIGNED NOT NULL auto_increment,
   word char(100) NOT NULL,
   replacement char(100) NOT NULL,
   PRIMARY KEY (word_id)
);
