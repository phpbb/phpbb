/*

  Basic DB data for phpBB2 devel (MSSQL)

 $Id$

*/

BEGIN TRANSACTION;

/*
  -- Config
*/
INSERT INTO phpbb_config (config_id, board_disable, board_startdate, sitename, cookie_name, cookie_path, cookie_domain, cookie_secure, session_length, allow_html, allow_html_tags, allow_bbcode, allow_smilies, allow_sig, allow_namechange, allow_theme_create, allow_avatar_local, allow_avatar_remote, allow_avatar_upload, override_themes, posts_per_page, topics_per_page, hot_threshold, email_sig, email_from, smtp_delivery, smtp_host, require_activation, flood_interval, avatar_filesize, avatar_max_width, avatar_max_height, avatar_path, smilies_path, default_theme, default_lang, default_dateformat, system_timezone, sys_template, prune_enable, gzip_compress) VALUES (1, '', '', 'yourdomain.com', 'phpbb2mssql', '', '', '', 600, '', 'b, u, i, pre, font color', 1, 1, 1, '', '', '', '', '', '', 10, 25, 10, '', '', '', '', '', 10, 6144, 70, 70, 'images/avatars', 'images/smiles', 2, 'english', 'd M Y H:i', '', 'PSO', 1, '');

/*
  -- Categories
*/
SET IDENTITY_INSERT phpbb_categories ON;

INSERT INTO phpbb_categories (cat_id, cat_title, cat_order) VALUES (1, 'Test category 1', 1);

SET IDENTITY_INSERT phpbb_categories OFF;

/*
  -- Forums
*/
SET IDENTITY_INSERT phpbb_forums ON;

INSERT INTO phpbb_forums (forum_id, cat_id, forum_name, forum_desc, forum_status, forum_order, forum_posts, forum_topics, forum_last_post_id, prune_next, prune_enable, auth_view, auth_read, auth_post, auth_reply, auth_edit, auth_delete, auth_announce, auth_sticky, auth_votecreate, auth_vote, auth_attachments) VALUES (1, 1, 'Test Forum 1', 'This is just a test forum, nothing special here.', '', 1, 1, 1, 1, '', 1, '', '', '', '', 1, 1, 3, 3, 3, 3, 3);

SET IDENTITY_INSERT phpbb_forums OFF;

/*
  -- Users
  username: admin    password: admin (change this or remove it once everything is working!)
*/
INSERT INTO phpbb_users (user_id, user_active, username, user_password, user_autologin_key, user_level, user_posts, user_timezone, user_dateformat, user_template, user_theme, user_lang, user_viewemail, user_attachsig, user_allowhtml, user_allowbbcode, user_allowsmile, user_allowavatar, user_allow_pm, user_allow_viewonline, user_notify, user_notify_pm, user_regdate, user_rank, user_avatar, user_email, user_icq, user_website, user_from, user_sig, user_aim, user_yim, user_msnm, user_occ, user_interests, user_actkey, user_newpasswd) VALUES (-1, '', 'Anonymous', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 1, '', '', '', 1, 972086460, '', '', '', '', '', '', '', '', '', '', '', '', '', '');
INSERT INTO phpbb_users (user_id, user_active, username, user_password, user_autologin_key, user_level, user_posts, user_timezone, user_dateformat, user_template, user_theme, user_lang, user_viewemail, user_attachsig, user_allowhtml, user_allowbbcode, user_allowsmile, user_allowavatar, user_allow_pm, user_allow_viewonline, user_notify, user_notify_pm, user_regdate, user_rank, user_avatar, user_email, user_icq, user_website, user_from, user_sig, user_aim, user_yim, user_msnm, user_occ, user_interests, user_actkey, user_newpasswd) VALUES (2, 1, 'Admin', '21232f297a57a5a743894a0e4a801fc3', '', 1, '', -8, 'd M Y h:i a', 'PSO', 2, 'english', 1, '', '', 1, 1, 1, 1, 1, '', 1, 972086460, 1, '', 'admin@yourdomain.com', '', '', '', 'A Signature', '', '', '', '', '', '', '');

/*
  -- Ranks
*/
SET IDENTITY_INSERT phpbb_ranks ON;

INSERT INTO phpbb_ranks (rank_id, rank_title, rank_min, rank_max, rank_special, rank_image) VALUES (1, 'Site Admin', -1, -1, 1, '');
INSERT INTO phpbb_ranks (rank_id, rank_title, rank_min, rank_max, rank_special, rank_image) VALUES (2, 'Newbie', '', 9, '', '');
INSERT INTO phpbb_ranks (rank_id, rank_title, rank_min, rank_max, rank_special, rank_image) VALUES (5, 'Here Often', 10, 49, '', '');
INSERT INTO phpbb_ranks (rank_id, rank_title, rank_min, rank_max, rank_special, rank_image) VALUES (6, 'Should Get Out More', 50, 199, '', '');
INSERT INTO phpbb_ranks (rank_id, rank_title, rank_min, rank_max, rank_special, rank_image) VALUES (7, 'Has No Life', 200, 99999, '', '');

SET IDENTITY_INSERT phpbb_ranks OFF;

/*
  -- Groups
*/
INSERT INTO phpbb_groups (group_id, group_type, group_name, group_description, group_moderator, group_single_user) VALUES (1, 1, 'Anonymous', 'Personal User', '', 1);
INSERT INTO phpbb_groups (group_id, group_type, group_name, group_description, group_moderator, group_single_user) VALUES (2, 1, 'Admin', 'Personal User', '', 1);
/*
  -- User -> Group
*/
INSERT INTO phpbb_user_group (group_id, user_id, user_pending) VALUES (1, -1, '');
INSERT INTO phpbb_user_group (group_id, user_id, user_pending) VALUES (2, 2, '');

/*
  -- User Access
*/
INSERT INTO phpbb_auth_access (group_id, forum_id, auth_view, auth_read, auth_post, auth_reply, auth_edit, auth_delete, auth_announce, auth_sticky, auth_votecreate, auth_attachments, auth_vote, auth_mod) VALUES (2, 1, '', '', '', '', '', '', '', '', '', '', '', 1);

/*
  -- Demo Topic
*/
SET IDENTITY_INSERT phpbb_topics ON;

INSERT INTO phpbb_topics (topic_id, forum_id, topic_title, topic_poster, topic_time, topic_views, topic_replies, topic_status, topic_type, topic_last_post_id, topic_moved_id) VALUES (1, 1, 'Demo Topic', 2, 972086460, '', '', '', '', 1, '');

SET IDENTITY_INSERT phpbb_topics OFF;

/*
  -- Demo Post
*/
SET IDENTITY_INSERT phpbb_posts ON;

INSERT INTO phpbb_posts (post_id, topic_id, forum_id, poster_id, post_time, poster_ip, post_username, enable_bbcode, enable_html, enable_smilies, bbcode_uid, post_edit_time, post_edit_count) VALUES (1, 1, 1, 2, 972086460, '7F000001', '', 1, '', 1, '', '', '');

INSERT INTO phpbb_posts_text (post_id, post_subject, post_text) VALUES (1, 'This is the subject', 'This is a demo post in the demo topic, what do you think of it?');

SET IDENTITY_INSERT phpbb_posts OFF;

/*
  -- Themes
*/
SET IDENTITY_INSERT phpbb_themes ON;

INSERT INTO phpbb_themes (themes_id, themes_name, head_stylesheet, body_background, body_bgcolor, body_text, body_link, body_vlink, body_alink, body_hlink, tr_color1, tr_color2, tr_color3, tr_class1, tr_class2, tr_class3, th_color1, th_color2, th_color3, th_class1, th_class2, th_class3, td_color1, td_color2, td_color3, td_class1, td_class2, td_class3, fontface1, fontface2, fontface3, fontsize1, fontsize2, fontsize3, fontcolor1, fontcolor2, fontcolor3, span_class1, span_class2, span_class3) VALUES (1, 'Default-Default', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'CCCCCC', 'DDDDDD', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
INSERT INTO phpbb_themes (themes_id, themes_name, head_stylesheet, body_background, body_bgcolor, body_text, body_link, body_vlink, body_alink, body_hlink, tr_color1, tr_color2, tr_color3, tr_class1, tr_class2, tr_class3, th_color1, th_color2, th_color3, th_class1, th_class2, th_class3, td_color1, td_color2, td_color3, td_class1, td_class2, td_class3, fontface1, fontface2, fontface3, fontsize1, fontsize2, fontsize3, fontcolor1, fontcolor2, fontcolor3, span_class1, span_class2, span_class3) VALUES (2, 'PSO-Default', '', '', 'FFFFFF', '000000', '002266', '004411', '', '', '', '', '', '', '', '', '000000', 'D2D2D2', 'BCBCBC', '', '', '', 'EDEDED', 'DEDEDE', '', 'row1', 'row2', '', 'verdana, serif', 'arial, helvetica', 'courier', 1, 2, 3, '', '', '', '', '', '');
INSERT INTO phpbb_themes (themes_id, themes_name, head_stylesheet, body_background, body_bgcolor, body_text, body_link, body_vlink, body_alink, body_hlink, tr_color1, tr_color2, tr_color3, tr_class1, tr_class2, tr_class3, th_color1, th_color2, th_color3, th_class1, th_class2, th_class3, td_color1, td_color2, td_color3, td_class1, td_class2, td_class3, fontface1, fontface2, fontface3, fontsize1, fontsize2, fontsize3, fontcolor1, fontcolor2, fontcolor3, span_class1, span_class2, span_class3) VALUES (3, 'PSO-Wheat', '', '', 'FFFFFF', '000000', '002266', '004411', '', '', '', '', '', '', '', '', '001100', 'E5CCA5', 'D4A294', '', '', '', 'EBE4D9', 'DAD1C4', '', 'row1', 'row2', '', 'verdana, serif', 'arial, helvetica', 'courier', 1, 2, 3, '000000', '', '', '', '', '');
INSERT INTO phpbb_themes (themes_id, themes_name, head_stylesheet, body_background, body_bgcolor, body_text, body_link, body_vlink, body_alink, body_hlink, tr_color1, tr_color2, tr_color3, tr_class1, tr_class2, tr_class3, th_color1, th_color2, th_color3, th_class1, th_class2, th_class3, td_color1, td_color2, td_color3, td_class1, td_class2, td_class3, fontface1, fontface2, fontface3, fontsize1, fontsize2, fontsize3, fontcolor1, fontcolor2, fontcolor3, span_class1, span_class2, span_class3) VALUES (4, 'PSO-Ocean', '', '', 'DFF5FF', '000000', '011001', '2100cc', '', '', '', '', '', '', '', '', '000000', 'A7C1CB', '7897A8', '', '', '', '83D7CC', 'A0CCE0', '', 'row1', 'row2', '', 'verdana, serif', 'arial, helvetica', 'courier', 1, 2, 3, '', '', '', '', '', '');
INSERT INTO phpbb_themes (themes_id, themes_name, head_stylesheet, body_background, body_bgcolor, body_text, body_link, body_vlink, body_alink, body_hlink, tr_color1, tr_color2, tr_color3, tr_class1, tr_class2, tr_class3, th_color1, th_color2, th_color3, th_class1, th_class2, th_class3, td_color1, td_color2, td_color3, td_class1, td_class2, td_class3, fontface1, fontface2, fontface3, fontsize1, fontsize2, fontsize3, fontcolor1, fontcolor2, fontcolor3, span_class1, span_class2, span_class3) VALUES (6, 'PSO-Blue', '', '', 'FFFFFF', '000000', '417FB9', '4E6172', '0000AA', '', '', '', '', '', '', '', '000000', '90BAE2', '5195D4', '', '', '', 'cde3f2', 'daedFd', '', 'row1', 'row2', '', 'verdana, serif', 'arial, helvetica', 'courier', 1, 2, 3, '000000', '', '', '', '', '');
INSERT INTO phpbb_themes (themes_id, themes_name, head_stylesheet, body_background, body_bgcolor, body_text, body_link, body_vlink, body_alink, body_hlink, tr_color1, tr_color2, tr_color3, tr_class1, tr_class2, tr_class3, th_color1, th_color2, th_color3, th_class1, th_class2, th_class3, td_color1, td_color2, td_color3, td_class1, td_class2, td_class3, fontface1, fontface2, fontface3, fontsize1, fontsize2, fontsize3, fontcolor1, fontcolor2, fontcolor3, span_class1, span_class2, span_class3) VALUES (7, 'PSO-Cool Midnight', '', '', '444444', 'ECECEC', 'EDF2F2', 'DDEDED', 'FFFFFF', 'EDF2F2', '', '', '', '', '', '', '000000', '80707F', '66555F', '', '', '', '60707D', '667A80', '', 'row1', 'row2', '', 'Verdana, serif', 'Arial, Helvetica, sans-serif', 'courier', '', '', '', 'ECECEC', 'ECECEC', 'ECECEC', '', '', '');

SET IDENTITY_INSERT phpbb_themes OFF;

/*
  -- Smilies
*/
SET IDENTITY_INSERT phpbb_smilies ON;

INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (1, ':D', 'icon_biggrin.gif', 'Very Happy');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (2, ':-D', 'icon_biggrin.gif', 'Very Happy');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (3, ':grin:', 'icon_biggrin.gif', 'Very Happy');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (4, ':)', 'icon_smile.gif', 'Smile');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (5, ':-)', 'icon_smile.gif', 'Smile');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (6, ':smile:', 'icon_smile.gif', 'Smile');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (7, ':(', 'icon_sad.gif', 'Sad');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (8, ':-(', 'icon_sad.gif', 'Sad');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (9, ':sad:', 'icon_sad.gif', 'Sad');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (10, ':o', 'icon_eek.gif', 'Surprised');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (11, ':-o', 'icon_eek.gif', 'Surprised');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (12, ':eek:', 'icon_eek.gif', 'Surprised');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (13, ':?', 'icon_confused.gif', 'Confused');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (14, ':-?', 'icon_confused.gif', 'Confused');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (15, ':???:', 'icon_confused.gif', 'Confused');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (16, '8)', 'icon_cool.gif', 'Cool');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (17, '8-)', 'icon_cool.gif', 'Cool');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (18, ':cool:', 'icon_cool.gif', 'Cool');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (19, ':lol:', 'icon_lol.gif', 'Laughing');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (20, ':x', 'icon_mad.gif', 'Mad');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (21, ':-x', 'icon_mad.gif', 'Mad');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (22, ':mad:', 'icon_mad.gif', 'Mad');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (23, ':P', 'icon_razz.gif', 'Razz');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (24, ':-P', 'icon_razz.gif', 'Razz');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (25, ':razz:', 'icon_razz.gif', 'Razz');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (26, ':oops:', 'icon_redface.gif', 'Embarassed');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (27, ':cry:', 'icon_cry.gif', 'Crying or Very sad');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (28, ':evil:', 'icon_evil.gif', 'Evil or Very Mad');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (29, ':roll:', 'icon_rolleyes.gif', 'Rolling Eyes');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (30, ':wink:', 'icon_wink.gif', 'Wink');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (31, ';)', 'icon_wink.gif', 'Wink');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (32, ';-)', 'icon_wink.gif', 'Wink');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (33, ':!:', 'icon_exclaim.gif', 'Exclamation');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (34, ':?:', 'icon_question.gif', 'Question');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (35, ':idea:', 'icon_idea.gif', 'Idea');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (36, ':arrow:', 'icon_arrow.gif', 'Arrow');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (37, ':|', 'icon_neutral.gif', 'Neutral');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (38, ':-|', 'icon_neutral.gif', 'Neutral');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (39, ':neutral:', 'icon_neutral.gif', 'Neutral');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES (40, ':mrgreen:', 'icon_mrgreen.gif', 'Mr. Green');

SET IDENTITY_INSERT phpbb_smilies OFF;

/*
  -- Words
*/
SET IDENTITY_INSERT phpbb_words ON;

INSERT INTO phpbb_words (word_id, word, replacement) VALUES (1, 'bollocks', '*beep*');
INSERT INTO phpbb_words (word_id, word, replacement) VALUES (2, '*fuck*', '*beep*');
INSERT INTO phpbb_words (word_id, word, replacement) VALUES (3, 'cunt*', '*beep*');
INSERT INTO phpbb_words (word_id, word, replacement) VALUES (4, 'dick*', '*beep*');
INSERT INTO phpbb_words (word_id, word, replacement) VALUES (5, '*shit*', '*beep*');
INSERT INTO phpbb_words (word_id, word, replacement) VALUES (6, 'bitch', '*beep*');
INSERT INTO phpbb_words (word_id, word, replacement) VALUES (7, 'cock*', '*beep*');
INSERT INTO phpbb_words (word_id, word, replacement) VALUES (8, 'slut', '*beep*');
INSERT INTO phpbb_words (word_id, word, replacement) VALUES (9, 'pussy', '*beep*');
INSERT INTO phpbb_words (word_id, word, replacement) VALUES (10, 'prick', '*beep*');
INSERT INTO phpbb_words (word_id, word, replacement) VALUES (11, 'tits', '*beep*');
INSERT INTO phpbb_words (word_id, word, replacement) VALUES (12, 'piss*', '*beep*');
INSERT INTO phpbb_words (word_id, word, replacement) VALUES (13, 'asshole', '*beep*');
INSERT INTO phpbb_words (word_id, word, replacement) VALUES (14, 'whore', '*beep*');
INSERT INTO phpbb_words (word_id, word, replacement) VALUES (15, 'cum', '*beep*');
INSERT INTO phpbb_words (word_id, word, replacement) VALUES (16, 'nutsack', '*beep*');
INSERT INTO phpbb_words (word_id, word, replacement) VALUES (17, 'clitsaq', '*beep*');
INSERT INTO phpbb_words (word_id, word, replacement) VALUES (18, '*wank*', '*beep*');
INSERT INTO phpbb_words (word_id, word, replacement) VALUES (19, 'twat', '*beep*');
INSERT INTO phpbb_words (word_id, word, replacement) VALUES (20, 'c0ck', '*beep*');
INSERT INTO phpbb_words (word_id, word, replacement) VALUES (21, 'fuk*', '*beep*');
INSERT INTO phpbb_words (word_id, word, replacement) VALUES (22, 'penis', '*beep*');
INSERT INTO phpbb_words (word_id, word, replacement) VALUES (23, 'fu(k', '*beep*');
INSERT INTO phpbb_words (word_id, word, replacement) VALUES (24, 'bastard', '*beep*');
INSERT INTO phpbb_words (word_id, word, replacement) VALUES (25, 'assram*', '*beep*');
INSERT INTO phpbb_words (word_id, word, replacement) VALUES (26, 'dickweed', '*beep*');
INSERT INTO phpbb_words (word_id, word, replacement) VALUES (27, 'dickwad', '*beep*');
INSERT INTO phpbb_words (word_id, word, replacement) VALUES (28, 'frig*', '*beep*');
INSERT INTO phpbb_words (word_id, word, replacement) VALUES (29, 'hore', '*beep*');
INSERT INTO phpbb_words (word_id, word, replacement) VALUES (30, 'fag*', '*beep*');
INSERT INTO phpbb_words (word_id, word, replacement) VALUES (31, 'asswipe', '*beep*');
INSERT INTO phpbb_words (word_id, word, replacement) VALUES (32, 'asstool*', '*beep*');
INSERT INTO phpbb_words (word_id, word, replacement) VALUES (33, '*cock*', '*beep*');
INSERT INTO phpbb_words (word_id, word, replacement) VALUES (34, 'b!tch', '*beep*');
INSERT INTO phpbb_words (word_id, word, replacement) VALUES (35, 'masturbation', '*beep*');
INSERT INTO phpbb_words (word_id, word, replacement) VALUES (36, 'dickhead', '*beep*');
INSERT INTO phpbb_words (word_id, word, replacement) VALUES (37, 'spunk', '*beep*');
INSERT INTO phpbb_words (word_id, word, replacement) VALUES (38, '*crap*', '*beep*');

SET IDENTITY_INSERT phpbb_words OFF;

COMMIT;
