#
# Basic DB data for phpBB2 devel
#
# $Id$

# -- Config
INSERT INTO phpbb_config (config_name, config_value) VALUES ('config_id','1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('board_disable','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('sitename','yourdomain.com');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('site_desc','A _little_ text to describe your forum');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('cookie_name','phpbb2mysql');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('cookie_path','/');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('cookie_domain','');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('cookie_secure','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('session_length','900');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_html','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_html_tags','b,i,u,pre');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_bbcode','1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_smilies','1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_sig','1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_namechange','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_theme_create','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_avatar_local','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_avatar_remote','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_avatar_upload','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('override_user_style','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('posts_per_page','15');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('topics_per_page','50');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('hot_threshold','25');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('max_poll_options','10');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('max_sig_chars','255');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('max_inbox_privmsgs','50');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('max_sentbox_privmsgs','25');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('max_savebox_privmsgs','50');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('board_email_sig','Thanks, The Management');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('board_email','youraddress@yourdomain.com');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('smtp_delivery','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('smtp_host','');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('require_activation','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('flood_interval','15');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('board_email_form','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('avatar_filesize','6144');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('avatar_max_width','80');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('avatar_max_height','80');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('avatar_path','images/avatars');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('avatar_gallery_path','images/avatars/gallery');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('smilies_path','images/smiles');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('default_lang','english');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('default_style','1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('default_dateformat','D M d, Y g:i a');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('board_timezone','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('prune_enable','1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('privmsg_disable','0'); 
INSERT INTO phpbb_config (config_name, config_value) VALUES ('gzip_compress','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('coppa_fax', '');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('coppa_mail', '');


# -- Categories
INSERT INTO phpbb_categories (cat_id, cat_title, cat_order) VALUES (1, 'Test category 1', 1);


# -- Forums
INSERT INTO phpbb_forums (forum_id, forum_name, forum_desc, cat_id, forum_order, forum_posts, forum_topics, forum_last_post_id, auth_view, auth_read, auth_post, auth_reply, auth_edit, auth_delete, auth_announce, auth_sticky, auth_pollcreate, auth_vote, auth_attachments) VALUES (1, 'Test Forum 1', 'This is just a test forum.', 1, 1, 1, 1, 1, 0, 0, 0, 0, 1, 1, 3, 1, 1, 1, 3);


# -- Users
INSERT INTO phpbb_users (user_id, username, user_level, user_regdate, user_password, user_autologin_key, user_email, user_icq, user_website, user_occ, user_from, user_interests, user_sig, user_viewemail, user_style, user_aim, user_yim, user_msnm, user_posts, user_attachsig, user_allowsmile, user_allowhtml, user_allowbbcode, user_allow_pm, user_notify_pm, user_allow_viewonline, user_rank, user_avatar, user_lang, user_timezone, user_dateformat, user_actkey, user_newpasswd, user_notify, user_active) VALUES ( -1, 'Anonymous', 0, 0, '', '', '', '', '', '', '', '', '', 0, NULL, '', '', '', 0, 0, 1, 0, 1, 0, 1, 1, NULL, '', '', '', '', '', '', 0, 0);

# -- username: admin    password: admin (change this or remove it once everything is working!)
INSERT INTO phpbb_users (user_id, username, user_level, user_regdate, user_password, user_autologin_key, user_email, user_icq, user_website, user_occ, user_from, user_interests, user_sig, user_viewemail, user_style, user_aim, user_yim, user_msnm, user_posts, user_attachsig, user_allowsmile, user_allowhtml, user_allowbbcode, user_allow_pm, user_notify_pm, user_popup_pm, user_allow_viewonline, user_rank, user_avatar, user_lang, user_timezone, user_dateformat, user_actkey, user_newpasswd, user_notify, user_active) VALUES ( 2, 'Admin', 1, 0, '21232f297a57a5a743894a0e4a801fc3', '', 'admin@yourdomain.com', '', '', '', '', '', '', 1, 1, '', '', '', 1, 0, 1, 0, 1, 1, 1, 1, 1, 1, '', 'english', 0, 'd M Y h:i a', '', '', 0, 1);


# -- Ranks
INSERT INTO phpbb_ranks (rank_id, rank_title, rank_min, rank_max, rank_special, rank_image) VALUES ( 1, 'Site Admin', -1, -1, 1, NULL);


# -- Groups
INSERT INTO phpbb_groups (group_id, group_name, group_description, group_single_user) VALUES (1, 'Anonymous', 'Personal User', 1);
INSERT INTO phpbb_groups (group_id, group_name, group_description, group_single_user) VALUES (2, 'Admin', 'Personal User', 1);


# -- User -> Group
INSERT INTO phpbb_user_group (group_id, user_id, user_pending) VALUES (1, -1, 0);
INSERT INTO phpbb_user_group (group_id, user_id, user_pending) VALUES (2, 2, 0);


# -- Demo Topic
INSERT INTO phpbb_topics (topic_id, topic_title, topic_poster, topic_time, topic_views, topic_replies, forum_id, topic_status, topic_type, topic_vote, topic_last_post_id) VALUES (1, 'Welcome to phpBB 2', 2, '972086460', 0, 0, 1, 0, 0, 0, 1);


# -- Demo Post
INSERT INTO phpbb_posts (post_id, topic_id, forum_id, poster_id, post_time, post_username, poster_ip) VALUES (1, 1, 1, 2, 972086460, NULL, '7F000001');
INSERT INTO phpbb_posts_text (post_id, post_subject, post_text) VALUES (1, NULL, 'This is an example post in your phpBB 2 installation. You may delete this post, this topic and even this forum if you like since everything seems to be working!');


# -- Themes
INSERT INTO phpbb_themes (themes_id, template_name, style_name, head_stylesheet, body_background, body_bgcolor, body_text, body_link, body_vlink, body_alink, body_hlink, tr_color1, tr_color2, tr_color3, tr_class1, tr_class2, tr_class3, th_color1, th_color2, th_color3, th_class1, th_class2, th_class3, td_color1, td_color2, td_color3, td_class1, td_class2, td_class3, fontface1, fontface2, fontface3, fontsize1, fontsize2, fontsize3, fontcolor1, fontcolor2, fontcolor3, span_class1, span_class2, span_class3) VALUES (1, 'subSilver', 'subSilver', NULL, NULL, 'E5E5E5', '000000', '006699', '5584AA', 'FF9933', 'EDF2F2', 'EFEFEF', 'DEE3E7', 'c2cdd6', NULL, NULL, NULL, 'CBD3D9', 'BCBCBC', '1B7CAD', NULL, NULL, NULL, 'AEBDC4', '006699', 'FFFFFF', 'row1', 'row2', NULL, 'Verdana, Arial, Helvetica, sans-serif', 'Verdana, Arial, Helvetica, sans-serif', 'courier', NULL, NULL, NULL, '004c75', '004c75', '004c75', NULL, NULL, NULL);
INSERT INTO phpbb_themes (themes_id, style_name, template_name, head_stylesheet, body_background, body_bgcolor, body_text, body_link, body_vlink, body_alink, body_hlink, tr_color1, tr_color2, tr_color3, tr_class1, tr_class2, tr_class3, th_color1, th_color2, th_color3, th_class1, th_class2, th_class3, td_color1, td_color2, td_color3, td_class1, td_class2, td_class3, fontface1, fontface2, fontface3, fontsize1, fontsize2, fontsize3, fontcolor1, fontcolor2, fontcolor3, span_class1, span_class2, span_class3) VALUES (2, 'PSO [ Wheat ] ', 'PSO', NULL, NULL, 'FFFFFF', '000000', '002266', '004411', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '001100', 'E5CCA5', 'D4A294', NULL, NULL, NULL, 'EBE4D9', 'DAD1C4', NULL, 'row1', 'row2', NULL, 'verdana, serif', 'arial, helvetica', 'courier', 1, 2, 3, '000000', NULL, NULL, NULL, NULL, NULL);
INSERT INTO phpbb_themes (themes_id, style_name, template_name, head_stylesheet, body_background, body_bgcolor, body_text, body_link, body_vlink, body_alink, body_hlink, tr_color1, tr_color2, tr_color3, tr_class1, tr_class2, tr_class3, th_color1, th_color2, th_color3, th_class1, th_class2, th_class3, td_color1, td_color2, td_color3, td_class1, td_class2, td_class3, fontface1, fontface2, fontface3, fontsize1, fontsize2, fontsize3, fontcolor1, fontcolor2, fontcolor3, span_class1, span_class2, span_class3) VALUES (3, 'PSO [ Ocean ]', 'PSO', NULL, NULL, 'DFF5FF', '000000', '011001', '2100cc', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '000000', 'A7C1CB', '7897A8', NULL, NULL, NULL, '83D7CC', 'A0CCE0', NULL, 'row1', 'row2', NULL, 'verdana, serif', 'arial, helvetica', 'courier', 1, 2, 3, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO phpbb_themes (themes_id, style_name, template_name, head_stylesheet, body_background, body_bgcolor, body_text, body_link, body_vlink, body_alink, body_hlink, tr_color1, tr_color2, tr_color3, tr_class1, tr_class2, tr_class3, th_color1, th_color2, th_color3, th_class1, th_class2, th_class3, td_color1, td_color2, td_color3, td_class1, td_class2, td_class3, fontface1, fontface2, fontface3, fontsize1, fontsize2, fontsize3, fontcolor1, fontcolor2, fontcolor3, span_class1, span_class2, span_class3) VALUES (4, 'PSO [ Blue ]', 'PSO', NULL, NULL, 'FFFFFF', '000000', '417FB9', '4E6172', '0000AA', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '000000', '90BAE2', '5195D4', NULL, NULL, NULL, 'cde3f2', 'daedFd', NULL, 'row1', 'row2', NULL, 'verdana, serif', 'arial, helvetica', 'courier', 1, 2, 3, '000000', NULL, NULL, NULL, NULL, NULL);
INSERT INTO phpbb_themes (themes_id, style_name, template_name, head_stylesheet, body_background, body_bgcolor, body_text, body_link, body_vlink, body_alink, body_hlink, tr_color1, tr_color2, tr_color3, tr_class1, tr_class2, tr_class3, th_color1, th_color2, th_color3, th_class1, th_class2, th_class3, td_color1, td_color2, td_color3, td_class1, td_class2, td_class3, fontface1, fontface2, fontface3, fontsize1, fontsize2, fontsize3, fontcolor1, fontcolor2, fontcolor3, span_class1, span_class2, span_class3) VALUES (5, 'PSO [ Cool Midnight ]', 'PSO', NULL, NULL, '444444', 'ECECEC', 'EDF2F2', 'DDEDED', 'FFFFFF', 'EDF2F2', NULL, NULL, NULL, NULL, NULL, NULL, '000000', '80707F', '66555F', NULL, NULL, NULL, '60707D', '667A80', NULL, 'row1', 'row2', NULL, 'Verdana, serif', 'Arial, Helvetica, sans-serif', 'courier', 1, 2, 3, 'ECECEC', 'ECECEC', 'ECECEC', NULL, NULL, NULL);
INSERT INTO phpbb_themes (themes_id, style_name, template_name, head_stylesheet, body_background, body_bgcolor, body_text, body_link, body_vlink, body_alink, body_hlink, tr_color1, tr_color2, tr_color3, tr_class1, tr_class2, tr_class3, th_color1, th_color2, th_color3, th_class1, th_class2, th_class3, td_color1, td_color2, td_color3, td_class1, td_class2, td_class3, fontface1, fontface2, fontface3, fontsize1, fontsize2, fontsize3, fontcolor1, fontcolor2, fontcolor3, span_class1, span_class2, span_class3) VALUES (6, 'PSO [ Pastel Purple ]', 'PSO', NULL, NULL, 'FFFFFF', '000000', '445588', '337744', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'CCCCDD', 'CCCCDD', 'DDDDEE', NULL, NULL, NULL, 'EFEFEF', 'FEFEFE', NULL, 'row1', 'row2', NULL, 'Verdana, serif', 'Arial, Helvetica, sans-serif', 'courier', 1, 2, 3, NULL, NULL, '0000EE', NULL, NULL, NULL);


# -- Smilies
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 1, ':D', 'icon_biggrin.gif', 'Very Happy');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 2, ':-D', 'icon_biggrin.gif', 'Very Happy');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 3, ':grin:', 'icon_biggrin.gif', 'Very Happy');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 4, ':)', 'icon_smile.gif', 'Smile');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 5, ':-)', 'icon_smile.gif', 'Smile');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 6, ':smile:', 'icon_smile.gif', 'Smile');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 7, ':(', 'icon_sad.gif', 'Sad');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 8, ':-(', 'icon_sad.gif', 'Sad');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 9, ':sad:', 'icon_sad.gif', 'Sad');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 10, ':o', 'icon_eek.gif', 'Surprised');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 11, ':-o', 'icon_eek.gif', 'Surprised');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 12, ':eek:', 'icon_eek.gif', 'Surprised');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 13, ':?', 'icon_confused.gif', 'Confused');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 14, ':-?', 'icon_confused.gif', 'Confused');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 15, ':???:', 'icon_confused.gif', 'Confused');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 16, '8)', 'icon_cool.gif', 'Cool');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 17, '8-)', 'icon_cool.gif', 'Cool');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 18, ':cool:', 'icon_cool.gif', 'Cool');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 19, ':lol:', 'icon_lol.gif', 'Laughing');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 20, ':x', 'icon_mad.gif', 'Mad');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 21, ':-x', 'icon_mad.gif', 'Mad');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 22, ':mad:', 'icon_mad.gif', 'Mad');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 23, ':P', 'icon_razz.gif', 'Razz');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 24, ':-P', 'icon_razz.gif', 'Razz');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 25, ':razz:', 'icon_razz.gif', 'Razz');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 26, ':oops:', 'icon_redface.gif', 'Embarassed');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 27, ':cry:', 'icon_cry.gif', 'Crying or Very sad');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 28, ':evil:', 'icon_evil.gif', 'Evil or Very Mad');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 29, ':twisted:', 'icon_twisted.gif', 'Twisted Evil');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 30, ':roll:', 'icon_rolleyes.gif', 'Rolling Eyes');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 31, ':wink:', 'icon_wink.gif', 'Wink');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 32, ';)', 'icon_wink.gif', 'Wink');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 33, ';-)', 'icon_wink.gif', 'Wink');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 34, ':!:', 'icon_exclaim.gif', 'Exclamation');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 35, ':?:', 'icon_question.gif', 'Question');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 36, ':idea:', 'icon_idea.gif', 'Idea');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 37, ':arrow:', 'icon_arrow.gif', 'Arrow');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 38, ':|', 'icon_neutral.gif', 'Neutral');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 39, ':-|', 'icon_neutral.gif', 'Neutral');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 40, ':neutral:', 'icon_neutral.gif', 'Neutral');
INSERT INTO phpbb_smilies (smilies_id, code, smile_url, emoticon) VALUES ( 41, ':mrgreen:', 'icon_mrgreen.gif', 'Mr. Green');


# -- wordlist
INSERT INTO phpbb_search_wordlist (word_id, word_text, word_common) VALUES ( 1, 'example', 0 );
INSERT INTO phpbb_search_wordlist (word_id, word_text, word_common) VALUES ( 2, 'post', 0 );
INSERT INTO phpbb_search_wordlist (word_id, word_text, word_common) VALUES ( 3, 'phpbb', 0 );
INSERT INTO phpbb_search_wordlist (word_id, word_text, word_common) VALUES ( 4, 'installation', 0 );
INSERT INTO phpbb_search_wordlist (word_id, word_text, word_common) VALUES ( 5, 'may', 0 );
INSERT INTO phpbb_search_wordlist (word_id, word_text, word_common) VALUES ( 6, 'delete', 0 );
INSERT INTO phpbb_search_wordlist (word_id, word_text, word_common) VALUES ( 7, 'topic', 0 );
INSERT INTO phpbb_search_wordlist (word_id, word_text, word_common) VALUES ( 8, 'forum', 0 );
INSERT INTO phpbb_search_wordlist (word_id, word_text, word_common) VALUES ( 9, 'since', 0 );
INSERT INTO phpbb_search_wordlist (word_id, word_text, word_common) VALUES ( 10, 'everything', 0 );
INSERT INTO phpbb_search_wordlist (word_id, word_text, word_common) VALUES ( 11, 'seems', 0 );
INSERT INTO phpbb_search_wordlist (word_id, word_text, word_common) VALUES ( 12, 'working', 0 );


# -- wordmatch
INSERT INTO phpbb_search_wordmatch (word_id, post_id, title_match) VALUES ( 1, 1, 0 );
INSERT INTO phpbb_search_wordmatch (word_id, post_id, title_match) VALUES ( 2, 1, 0 );
INSERT INTO phpbb_search_wordmatch (word_id, post_id, title_match) VALUES ( 3, 1, 0 );
INSERT INTO phpbb_search_wordmatch (word_id, post_id, title_match) VALUES ( 4, 1, 0 );
INSERT INTO phpbb_search_wordmatch (word_id, post_id, title_match) VALUES ( 5, 1, 0 );
INSERT INTO phpbb_search_wordmatch (word_id, post_id, title_match) VALUES ( 6, 1, 0 );
INSERT INTO phpbb_search_wordmatch (word_id, post_id, title_match) VALUES ( 7, 1, 0 );
INSERT INTO phpbb_search_wordmatch (word_id, post_id, title_match) VALUES ( 8, 1, 0 );
INSERT INTO phpbb_search_wordmatch (word_id, post_id, title_match) VALUES ( 9, 1, 0 );
INSERT INTO phpbb_search_wordmatch (word_id, post_id, title_match) VALUES ( 10, 1, 0 );
INSERT INTO phpbb_search_wordmatch (word_id, post_id, title_match) VALUES ( 11, 1, 0 );
INSERT INTO phpbb_search_wordmatch (word_id, post_id, title_match) VALUES ( 12, 1, 0 );
