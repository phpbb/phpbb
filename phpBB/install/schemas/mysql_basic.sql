#
# Basic DB data for phpBB2 devel
#
# $Id$
#

# -- Config
INSERT INTO phpbb_config (config_name, config_value) VALUES ('config_id','1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('board_disable','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('board_disable_msg','');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('sitename','yourdomain.com');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('site_desc','A _little_ text to describe your forum');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('cookie_name','phpbb22');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('cookie_path','/');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('cookie_domain','');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('cookie_secure','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('session_length','3600');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_html','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_html_tags','b,i,u,pre');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_bbcode','1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_smilies','1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_sig','1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_namechange','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_topic_notify','1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_forum_notify','1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_avatar_local','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_avatar_remote','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_avatar_upload','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('default_style','1'); 
INSERT INTO phpbb_config (config_name, config_value) VALUES ('default_dateformat','D M d, Y g:i a'); 
INSERT INTO phpbb_config (config_name, config_value) VALUES ('board_timezone','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('override_user_style','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('posts_per_page','10');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('topics_per_page','30');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('hot_threshold','25');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('max_post_chars', '0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('max_sig_chars','255');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('max_poll_options','10');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('max_box_privmsgs','50');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('board_email_sig','Thanks, The Management');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('board_email','youraddress@yourdomain.com');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('smtp_delivery','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('smtp_host','');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('smtp_username','');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('smtp_password','');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('ldap_enable','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('require_activation','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('flood_interval','15');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('search_interval','10');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('board_email_form','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('avatar_filesize','6144');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('avatar_max_width','90');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('avatar_max_height','90');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('avatar_path','images/avatars/upload');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('avatar_gallery_path','images/avatars/gallery');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('smilies_path','images/smiles');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('icons_path','images/icons');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('prune_enable','1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('prune_logs_enable','1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('privmsg_disable','0'); 
INSERT INTO phpbb_config (config_name, config_value) VALUES ('gzip_compress','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('coppa_enable','1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('coppa_fax', '');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('coppa_mail', '');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('record_online_users', '0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('record_online_date', '0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('server_name', '');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('server_port', '');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('script_path', '');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('newest_user_id', '1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('newest_username', '');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('num_users', '1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('num_posts', '1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('num_topics', '1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('limit_load', '1.5');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('active_sessions', '0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('session_gc', '3600');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('session_last_gc', '0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('version', '2.1.0 [20020430]');


# -- auth options
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('forum', 'list');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('forum', 'read');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('forum', 'post');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('forum', 'reply');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('forum', 'edit');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('forum', 'delete');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('forum', 'poll');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('forum', 'vote');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('forum', 'announce');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('forum', 'sticky');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('forum', 'attach');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('forum', 'download');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('forum', 'html');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('forum', 'bbcode');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('forum', 'smilies');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('forum', 'img');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('forum', 'flash');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('forum', 'sigs');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('forum', 'search');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('forum', 'email');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('forum', 'rate');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('forum', 'print');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('forum', 'ignoreflood');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('forum', 'ignorequeue');

INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('mod', 'edit');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('mod', 'delete');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('mod', 'move');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('mod', 'lock');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('mod', 'split');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('mod', 'merge');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('mod', 'approve');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('mod', 'unrate');
INSERT INTO phpbb_auth_options auth_type, (auth_option) VALUES ('mod', 'auth');

INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('admin', 'general');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('admin', 'user');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('admin', 'group');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('admin', 'forum');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('admin', 'post');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('admin', 'ban');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('admin', 'auth');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('admin', 'email');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('admin', 'styles');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('admin', 'backup');
INSERT INTO phpbb_auth_options (auth_type, auth_option) VALUES ('admin', 'clearlogs');


# -- phpbb_styles
INSERT INTO phpbb_styles (style_id, template_id, theme_id, imageset_id, style_name) VALUES (1, 1, 1, 1, 'subSilver');

# -- phpbb_styles_imageset
INSERT INTO phpbb_styles_imageset (imageset_id, imageset_name, imageset_path, post_new, post_locked, post_pm, reply_new, reply_pm, reply_locked, icon_quote, icon_edit, icon_search, icon_profile, icon_pm, icon_email, icon_www, icon_icq, icon_aim, icon_yim, icon_msnm, icon_no_email, icon_no_www, icon_no_icq, icon_no_aim, icon_no_yim, icon_no_msnm, icon_delete, icon_ip, goto_post, goto_post_new, goto_post_latest, goto_post_newest, forum, forum_new, forum_locked, folder, folder_new, folder_hot, folder_hot_new, folder_locked, folder_locked_new, folder_sticky, folder_sticky_new, folder_announce, folder_announce_new, topic_watch, topic_unwatch, poll_left, poll_center, poll_right, rating) VALUES (1, 'subSilver &copy; phpBB Group', 'subSilver', '"imagesets/subSilver/{LANG}/post.gif" width="82" height="25" border="0"', '"imagesets/subSilver/{LANG}/reply-locked.gif" width="82" height="25" border="0"', '"imagesets/subSilver/{LANG}/post.gif" width="82" height="25" border="0"', '"imagesets/subSilver/{LANG}/reply.gif" width="88" height="27" border="0"', '"imagesets/subSilver/{LANG}/reply.gif" width="88" height="27" border="0"', '"imagesets/subSilver/{LANG}/reply-locked.gif" width="82" height="25" border="0"', '"imagesets/subSilver/{LANG}/icon_quote.gif" width="59" height="18" border="0"', '"imagesets/subSilver/{LANG}/icon_edit.gif" width="59" height="18" border="0"', '"imagesets/subSilver/{LANG}/icon_search.gif" width="59" height="18" border="0"', '"imagesets/subSilver/{LANG}/icon_profile.gif" width="59" height="18" border="0"', '"imagesets/subSilver/{LANG}/icon_pm.gif" width="59" height="18" border="0"', '"imagesets/subSilver/{LANG}/icon_email.gif" width="59" height="18" border="0"', '"imagesets/subSilver/{LANG}/icon_www.gif" width="59" height="18" border="0"', '"imagesets/subSilver/{LANG}/icon_icq_add.gif" width="59" height="18" border="0"', '"imagesets/subSilver/{LANG}/icon_aim.gif" width="59" height="18" border="0"', '"imagesets/subSilver/{LANG}/icon_yim.gif" width="59" height="18" border="0"', '"imagesets/subSilver/{LANG}/icon_msnm.gif" width="59" height="18" border="0"', '', '', '', '', '', '', '"imagesets/subSilver/icon_delete.gif" width="16" height="18" border="0"', '"imagesets/subSilver/{LANG}/icon_ip.gif" width="16" height="18" border="0"', '"imagesets/subSilver/icon_minipost.gif" width="12" height="9" border="0"', '"imagesets/subSilver/icon_minipost_new.gif" width="12" height="9" border="0"', '"imagesets/subSilver/icon_latest_reply.gif" width="18" height="9" border="0"', '"imagesets/subSilver/icon_newest_reply.gif" width="18" height="9" border="0"', '"imagesets/subSilver/folder_big.gif" width="46" height="25" border="0"', '"imagesets/subSilver/folder_new_big.gif" width="46" height="25" border="0"', '"imagesets/subSilver/folder_locked_big.gif" width="46" height="25" border="0"', '"imagesets/subSilver/folder.gif" width="19" height="18" border="0"', '"imagesets/subSilver/folder_new.gif" width="19" height="18" border="0"', '"imagesets/subSilver/folder_hot.gif" width="19" height="18" border="0"', '"imagesets/subSilver/folder_new_hot.gif" width="19" height="18" border="0"', '"imagesets/subSilver/folder_lock.gif" width="19" height="18" border="0"', '"imagesets/subSilver/folder_lock_new.gif" width="19" height="18" border="0"', '"imagesets/subSilver/folder_sticky.gif" width="19" height="18" border="0"', '"imagesets/subSilver/folder_sticky_new.gif" width="19" height="18" border="0"', '"imagesets/subSilver/folder_announce.gif" width="19" height="18" border="0"', '"imagesets/subSilver/folder_announce_new.gif" width="19" height="18" border="0"', '', '', '"imagesets/subSilver/voting_lcap.gif" width="4" height="12" border="0"', '"imagesets/subSilver/voting_rcap.gif" height="12" border="0"', '"imagesets/subSilver/voting_bar.gif" width="4" height="12" border="0"', '"imagesets/subSilver/ratings/{RATE}.gif" width="45" height="17" border="0"');

# -- phpbb_styles_template
INSERT INTO phpbb_styles_template (template_id, template_name, template_path, poll_length, pm_box_length, compile_crc) VALUES (1, 'subSilver &copy; phpBB Group', 'subSilver', 205, 175, '');

# -- phpbb_styles_theme
INSERT INTO phpbb_styles_theme (theme_id, css_data, css_external) VALUES (1, 'th		{ background-image: url(templates/subSilver/images/cellpic3.gif) }\r\ntd.cat		{ background-image: url(templates/subSilver/images/cellpic1.gif) }\r\ntd.rowpic	{ background-image: url(templates/subSilver/images/cellpic2.jpg); background-repeat: repeat-y }\r\ntd.icqback	{ background-image: url(templates/subSilver/images/icon_icq_add.gif); background-repeat: no-repeat }\r\ntd.catHead,td.catSides,td.catLeft,td.catRight,td.catBottom { background-image: url(templates/subSilver/images/cellpic1.gif) }\r\nth.thTop	{ background-image: url(templates/subSilver/images/cellpic3.gif) }', 'subSilver/subSilver.css');


# -- Categories
INSERT INTO phpbb_categories (cat_id, cat_title, cat_order) VALUES (1, 'Test category 1', 10);


# -- Forums
INSERT INTO phpbb_forums (forum_id, forum_name, forum_desc, cat_id, forum_order, forum_posts, forum_topics, forum_last_post_id) VALUES (1, 'Test Forum 1', 'This is just a test forum.', 1, 10, 1, 1, 1);


# -- Users
INSERT INTO phpbb_users (user_id, username, user_regdate, user_password, user_email, user_icq, user_website, user_occ, user_from, user_interests, user_sig, user_viewemail, user_style, user_aim, user_yim, user_msnm, user_posts, user_attachsig, user_allowsmile, user_allowhtml, user_allowbbcode, user_allow_pm, user_notify_pm, user_allow_viewonline, user_rank, user_avatar, user_lang, user_timezone, user_dateformat, user_actkey, user_newpasswd, user_notify, user_active) VALUES ( -1, 'Anonymous', 0, '', '', '', '', '', '', '', '', 0, NULL, '', '', '', 0, 0, 1, 0, 1, 0, 1, 1, NULL, '', '', '', '', '', '', 0, 0);

# -- username: admin    password: admin (change this or remove it once everything is working!)
INSERT INTO phpbb_users (user_id, username, user_regdate, user_password, user_email, user_icq, user_website, user_occ, user_from, user_interests, user_sig, user_viewemail, user_style, user_aim, user_yim, user_msnm, user_posts, user_attachsig, user_allowsmile, user_allowhtml, user_allowbbcode, user_allow_pm, user_notify_pm, user_popup_pm, user_allow_viewonline, user_rank, user_avatar, user_lang, user_timezone, user_dateformat, user_actkey, user_newpasswd, user_notify, user_active) VALUES ( 2, 'Admin', 0, '21232f297a57a5a743894a0e4a801fc3', 'admin@yourdomain.com', '', '', '', '', '', '', 1, 1, '', '', '', 1, 0, 1, 0, 1, 1, 1, 1, 1, 1, '', 'english', 0, 'd M Y h:i a', '', '', 0, 1);


# -- Ranks
INSERT INTO phpbb_ranks (rank_id, rank_title, rank_min, rank_special, rank_image) VALUES ( 1, 'Site Admin', -1, 1, NULL);


# -- Groups
INSERT INTO phpbb_groups (group_id, group_name, group_type) VALUES (1, 'REGISTERED', 0);
INSERT INTO phpbb_groups (group_id, group_name, group_type) VALUES (2, 'ADMINISTRATOR', 0);


# -- User -> Group
INSERT INTO phpbb_user_group (group_id, user_id, user_pending) VALUES (1, 2, 0);
INSERT INTO phpbb_user_group (group_id, user_id, user_pending) VALUES (2, 2, 0);


# -- User auth
INSERT INTO phpbb_auth_users (user_id, forum_id, auth_option_id, auth_allow_deny) SELECT -1, 1, auth_option_id, 1 FROM phpbb_auth_options WHERE auth_type LIKE 'forum' AND auth_option IN ('list', 'read', 'post', 'reply');

# -- Group auth
INSERT INTO phpbb_auth_groups (group_id, forum_id, auth_option_id, auth_allow_deny) SELECT 2, 0, auth_option_id, 1 FROM phpbb_auth_options WHERE auth_type IN ('admin');
INSERT INTO phpbb_auth_groups (group_id, forum_id, auth_option_id, auth_allow_deny) SELECT 1, 1, auth_option_id, 1 FROM phpbb_auth_options WHERE auth_type IN ('forum', 'mod');

# -- Prefetch auth
INSERT INTO phpbb_auth_prefetch (user_id, forum_id, auth_option_id, auth_allow_deny) SELECT -1, 1, auth_option_id, 1 FROM phpbb_auth_options WHERE auth_type LIKE 'forum' AND auth_option IN ('list', 'read', 'post', 'reply');
INSERT INTO phpbb_auth_prefetch (user_id, forum_id, auth_option_id, auth_allow_deny) SELECT 2, 0, auth_option_id, 1 FROM phpbb_auth_options WHERE auth_type IN ('admin');
INSERT INTO phpbb_auth_prefetch (user_id, forum_id, auth_option_id, auth_allow_deny) SELECT 2, 1, auth_option_id, 1 FROM phpbb_auth_options WHERE auth_type IN ('forum', 'mod');


# -- Demo Topic
INSERT INTO phpbb_topics (topic_id, topic_title, topic_poster, topic_time, topic_views, topic_replies, forum_id, topic_status, topic_type, topic_first_post_id, topic_last_post_id) VALUES (1, 'Welcome to phpBB 2', 2, '972086460', 0, 0, 1, 0, 0, 1, 1);


# -- Demo Post
INSERT INTO phpbb_posts (post_id, topic_id, forum_id, poster_id, post_time, post_username, poster_ip) VALUES (1, 1, 1, 2, 972086460, NULL, '127.0.0.1');
INSERT INTO phpbb_posts_text (post_id, post_subject, post_text) VALUES (1, NULL, 'This is an example post in your phpBB 2.2 installation. You may delete this post, this topic and even this forum if you like since everything seems to be working!');


# -- Topic icons
INSERT INTO phpbb_icons (icons_id, icons_url, icons_width, icons_height) VALUES (1, '', 0, 0);


# -- Smilies
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':D', 'icon_biggrin.gif', 'Very Happy');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':-D', 'icon_biggrin.gif', 'Very Happy');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':grin:', 'icon_biggrin.gif', 'Very Happy');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':)', 'icon_smile.gif', 'Smile');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':-)', 'icon_smile.gif', 'Smile');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':smile:', 'icon_smile.gif', 'Smile');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':(', 'icon_sad.gif', 'Sad');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':-(', 'icon_sad.gif', 'Sad');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':sad:', 'icon_sad.gif', 'Sad');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':o', 'icon_surprised.gif', 'Surprised');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':-o', 'icon_surprised.gif', 'Surprised');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':eek:', 'icon_surprised.gif', 'Surprised');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( '8O', 'icon_eek.gif', 'Shocked');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( '8-O', 'icon_eek.gif', 'Shocked');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':shock:', 'icon_eek.gif', 'Shocked');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':?', 'icon_confused.gif', 'Confused');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':-?', 'icon_confused.gif', 'Confused');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':???:', 'icon_confused.gif', 'Confused');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( '8)', 'icon_cool.gif', 'Cool');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( '8-)', 'icon_cool.gif', 'Cool');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':cool:', 'icon_cool.gif', 'Cool');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':lol:', 'icon_lol.gif', 'Laughing');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':x', 'icon_mad.gif', 'Mad');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':-x', 'icon_mad.gif', 'Mad');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':mad:', 'icon_mad.gif', 'Mad');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':P', 'icon_razz.gif', 'Razz');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':-P', 'icon_razz.gif', 'Razz');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':razz:', 'icon_razz.gif', 'Razz');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':oops:', 'icon_redface.gif', 'Embarassed');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':cry:', 'icon_cry.gif', 'Crying or Very sad');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':evil:', 'icon_evil.gif', 'Evil or Very Mad');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':twisted:', 'icon_twisted.gif', 'Twisted Evil');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':roll:', 'icon_rolleyes.gif', 'Rolling Eyes');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':wink:', 'icon_wink.gif', 'Wink');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ';)', 'icon_wink.gif', 'Wink');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ';-)', 'icon_wink.gif', 'Wink');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':!:', 'icon_exclaim.gif', 'Exclamation');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':?:', 'icon_question.gif', 'Question');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':idea:', 'icon_idea.gif', 'Idea');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':arrow:', 'icon_arrow.gif', 'Arrow');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':|', 'icon_neutral.gif', 'Neutral');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':-|', 'icon_neutral.gif', 'Neutral');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':neutral:', 'icon_neutral.gif', 'Neutral');
INSERT INTO phpbb_smilies (code, smile_url, emoticon) VALUES ( ':mrgreen:', 'icon_mrgreen.gif', 'Mr. Green');


# -- wordlist
INSERT INTO phpbb_search_wordlist (word_id, word_text, word_common) VALUES ( 1, 'example', 0 );
INSERT INTO phpbb_search_wordlist (word_id, word_text, word_common) VALUES ( 2, 'post', 0 );
INSERT INTO phpbb_search_wordlist (word_id, word_text, word_common) VALUES ( 3, 'phpbb', 0 );
INSERT INTO phpbb_search_wordlist (word_id, word_text, word_common) VALUES ( 4, 'installation', 0 );
INSERT INTO phpbb_search_wordlist (word_id, word_text, word_common) VALUES ( 5, 'delete', 0 );
INSERT INTO phpbb_search_wordlist (word_id, word_text, word_common) VALUES ( 6, 'topic', 0 );
INSERT INTO phpbb_search_wordlist (word_id, word_text, word_common) VALUES ( 7, 'forum', 0 );
INSERT INTO phpbb_search_wordlist (word_id, word_text, word_common) VALUES ( 8, 'since', 0 );
INSERT INTO phpbb_search_wordlist (word_id, word_text, word_common) VALUES ( 9, 'everything', 0 );
INSERT INTO phpbb_search_wordlist (word_id, word_text, word_common) VALUES ( 10, 'seems', 0 );
INSERT INTO phpbb_search_wordlist (word_id, word_text, word_common) VALUES ( 11, 'working', 0 );
INSERT INTO phpbb_search_wordlist (word_id, word_text, word_common) VALUES ( 12, 'welcome', 0 );


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
INSERT INTO phpbb_search_wordmatch (word_id, post_id, title_match) VALUES ( 12, 1, 1 );
INSERT INTO phpbb_search_wordmatch (word_id, post_id, title_match) VALUES ( 3, 1, 1 );
