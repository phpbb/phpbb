#
# Basic DB data for phpBB2 devel
#
# $id: $

# -- Config
INSERT INTO phpbb_config (config_id, sitename, allow_html, allow_bbcode, allow_smilies, allow_sig, allow_namechange, selected, posts_per_page, hot_threshold, topics_per_page, flood_interval, allow_theme_create, override_themes, email_sig, email_from, default_theme, default_lang, default_dateformat, system_timezone, sys_template, avatar_filesize, avatar_path, allow_avatar_upload) VALUES ( '1', 'phpbb.com', '0', '1', '1', '1', '0', '1', '10', '10', '25', '10', '0', '0', '', '', '5', 'english', 'd M Y H:i', '0', 'Default', '6144', 'images/avatars', '0');

# -- Categories
INSERT INTO phpbb_categories VALUES (1, 'Test category 1', '1');

# -- Forums
INSERT INTO phpbb_forums VALUES (1, 1, 'Test Forum 1', 'This is just a test forum, nothing special here.', 1, 1, 1, 1, 1, 1);

# -- Users (admin is set as that, an admin ... password is null, change it once online!)
INSERT INTO phpbb_users (user_id, username, user_level, user_regdate, user_password, user_autologin_key, user_email, user_icq, user_website, user_occ, user_from, user_interests, user_sig, user_viewemail, user_theme, user_aim, user_yim, user_msnm, user_posts, user_attachsig, user_allowsmile, user_allowhtml, user_allowbbcode, user_rank, user_avatar, user_lang, user_timezone, user_dateformat, user_actkey, user_newpasswd, user_notify, user_active, user_template) VALUES ( '-1', 'Anonymous', '0', '972086460', '', '', '', '', '', '', '', '', '', '0', '0', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '0', '0', '');
INSERT INTO phpbb_users (user_id, username, user_level, user_regdate, user_password, user_autologin_key, user_email, user_icq, user_website, user_occ, user_from, user_interests, user_sig, user_viewemail, user_theme, user_aim, user_yim, user_msnm, user_posts, user_attachsig, user_allowsmile, user_allowhtml, user_allowbbcode, user_rank, user_avatar, user_lang, user_timezone, user_dateformat, user_actkey, user_newpasswd, user_notify, user_active, user_template) VALUES ( '2', 'Admin', '1', NOW(), '', '', 'admin@yourdomain.com', '', '', '', '', '', 'A Signature', '1', '1', '', '', '', '0', '0', '1', '0', '1', '1', '', 'english', '-8', 'd M Y h:i a', '', '', '0', '1', 'Default');

# -- Ranks
INSERT INTO phpbb_ranks (rank_id, rank_title, rank_min, rank_max, rank_special, rank_image) VALUES ( '1', 'Site Admin', '-1', '-1', '1', '');
INSERT INTO phpbb_ranks (rank_id, rank_title, rank_min, rank_max, rank_special, rank_image) VALUES ( '2', 'Newbie', '0', '9', '0', '');
INSERT INTO phpbb_ranks (rank_id, rank_title, rank_min, rank_max, rank_special, rank_image) VALUES ( '5', 'Here Often', '10', '49', '0', '');
INSERT INTO phpbb_ranks (rank_id, rank_title, rank_min, rank_max, rank_special, rank_image) VALUES ( '6', 'Should Get Out More', '50', '199', '0', '');
INSERT INTO phpbb_ranks (rank_id, rank_title, rank_min, rank_max, rank_special, rank_image) VALUES ( '7', 'Has No Life', '200', '99999', '0', '');

# -- Groups
INSERT INTO phpbb_groups (group_id, group_name, group_note, single_user) VALUES (1, 'Anonymous', 'Personal User', 1);
INSERT INTO phpbb_groups (group_id, group_name, group_note, single_user) VALUES (2, 'Admin', 'Personal User', 1);

# -- User -> Group
INSERT INTO phpbb_user_group (group_id, user_id) VALUES (1, 1);
INSERT INTO phpbb_user_group (group_id, user_id) VALUES (2, 2);

# -- Forum Access (Open access to ALL)
INSERT INTO phpbb_auth_forums (forum_id, auth_view, auth_read, auth_post, auth_reply, auth_edit, auth_delete, auth_announce, auth_sticky, auth_votecreate, auth_vote) VALUES (1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

# -- User Access (admin is set as a moderator of the created forum)
INSERT INTO phpbb_auth_access (group_id, forum_id, auth_view, auth_read, auth_post, auth_reply, auth_edit, auth_delete, auth_announce, auth_sticky, auth_votecreate, auth_vote, auth_mod) VALUES (2, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1);

# -- Demo Topic
INSERT INTO phpbb_topics VALUES(1, 1, 'Demo Topic', 1, NOW(), 0, 0, 0, 0, 0, 1);

# -- Demo Post
INSERT INTO phpbb_posts VALUES(1, 1, 1, 1, NOW(), '127.0.0.1' , LEFT(MD5('42'), 10));
INSERT INTO phpbb_posts_text VALUES(1,'This is the subject', 'This is a demo post in the demo topic');

# -- Themes
INSERT INTO phpbb_themes (themes_id, themes_name, head_stylesheet, body_background, body_bgcolor, body_text, body_link, body_vlink, body_alink, body_hlink, tr_color1, tr_color2, tr_color3, th_color1, th_color2, th_color3, td_color1, td_color2, td_color3, fontface1, fontface2, fontface3, fontsize1, fontsize2, fontsize3, fontcolor1, fontcolor2, fontcolor3, img1, img2, img3, img4) VALUES ( '1', 'Default-Default', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'CCCCCC', 'DDDDDD', '', '', '', '', '0', '0', '0', '', '', '', '', '', '', '');
INSERT INTO phpbb_themes_name (themes_id, tr_color1_name, tr_color2_name, tr_color3_name, th_color1_name, th_color2_name, th_color3_name, td_color1_name, td_color2_name, td_color3_name, fontface1_name, fontface2_name, fontface3_name, fontsize1_name, fontsize2_name, fontsize3_name, fontcolor1_name, fontcolor2_name, fontcolor3_name, img1_name, img2_name, img3_name, img4_name) VALUES ( '1', '', '', '', '', '', '', '', 'Row Color 1', 'Row Color 2', '', '', '', '', '', '', '', '', '', '', '', '', '');

INSERT INTO phpbb_themes (themes_id, themes_name, head_stylesheet, body_background, body_bgcolor, body_text, body_link, body_vlink, body_alink, body_hlink, tr_color1, tr_color2, tr_color3, th_color1, th_color2, th_color3, td_color1, td_color2, td_color3, fontface1, fontface2, fontface3, fontsize1, fontsize2, fontsize3, fontcolor1, fontcolor2, fontcolor3, img1, img2, img3, img4) VALUES ( '2', 'PSO-Default', 'site_style_default.css', '', 'FFFFFF', '000000', '002266', '004411', '', '', '', '', '', '000000', 'D2D2D2', 'BCBCBC', 'EDEDED', 'DEDEDE', '', 'verdana,serif', 'arial,helvetica', 'courier', '1', '2', '3', '', '', '', '', '', '', '');
INSERT INTO phpbb_themes_name (themes_id, tr_color1_name, tr_color2_name, tr_color3_name, th_color1_name, th_color2_name, th_color3_name, td_color1_name, td_color2_name, td_color3_name, fontface1_name, fontface2_name, fontface3_name, fontsize1_name, fontsize2_name, fontsize3_name, fontcolor1_name, fontcolor2_name, fontcolor3_name, img1_name, img2_name, img3_name, img4_name) VALUES ( '2', '', '', '', 'Table Background', 'Title Header', 'Category Header', 'Table background', 'Row Color 1', 'Row Color 2', 'Serif', 'Sans-serif', 'Courier', 'Smallest', 'Typical', 'Largest', 'All text', '', '', '', '', '', '');

INSERT INTO phpbb_themes (themes_id, themes_name, head_stylesheet, body_background, body_bgcolor, body_text, body_link, body_vlink, body_alink, body_hlink, tr_color1, tr_color2, tr_color3, th_color1, th_color2, th_color3, td_color1, td_color2, td_color3, fontface1, fontface2, fontface3, fontsize1, fontsize2, fontsize3, fontcolor1, fontcolor2, fontcolor3, img1, img2, img3, img4) VALUES ( '3', 'PSO-Wheat', '', '', 'FFFFFF', '000000', '002266', '004411', '', '', '', '', '', '001100', 'E5CCA5', 'D4A294', 'EBE4D9', 'DAD1C4', '', 'verdana,serif', 'arial,helvetica', 'courier', '1', '2', '3', '000000', '', '', '', '', '', '');
INSERT INTO phpbb_themes_name (themes_id, tr_color1_name, tr_color2_name, tr_color3_name, th_color1_name, th_color2_name, th_color3_name, td_color1_name, td_color2_name, td_color3_name, fontface1_name, fontface2_name, fontface3_name, fontsize1_name, fontsize2_name, fontsize3_name, fontcolor1_name, fontcolor2_name, fontcolor3_name, img1_name, img2_name, img3_name, img4_name) VALUES ( '3', '', '', '', 'Table Background', 'Title Header', 'Category Header', 'Table background', 'Row Color 1', 'Row Color 2', 'Serif', 'Sans-serif', 'Courier', 'Smallest', 'Typical', 'Largest', 'All text', '', '', '', '', '', '');

INSERT INTO phpbb_themes (themes_id, themes_name, head_stylesheet, body_background, body_bgcolor, body_text, body_link, body_vlink, body_alink, body_hlink, tr_color1, tr_color2, tr_color3, th_color1, th_color2, th_color3, td_color1, td_color2, td_color3, fontface1, fontface2, fontface3, fontsize1, fontsize2, fontsize3, fontcolor1, fontcolor2, fontcolor3, img1, img2, img3, img4) VALUES ( '4', 'PSO-Ocean', '', '', 'DFF5FF', '000000', '011001', '2100cc', '', '', '', '', '', '000000', 'A7C1CB', '7897A8', '83D7CC', 'A0CCE0', '', 'verdana,serif', 'arial,helvetica', 'courier', '1', '2', '3', '', '', '', '', '', '', '');
INSERT INTO phpbb_themes_name (themes_id, tr_color1_name, tr_color2_name, tr_color3_name, th_color1_name, th_color2_name, th_color3_name, td_color1_name, td_color2_name, td_color3_name, fontface1_name, fontface2_name, fontface3_name, fontsize1_name, fontsize2_name, fontsize3_name, fontcolor1_name, fontcolor2_name, fontcolor3_name, img1_name, img2_name, img3_name, img4_name) VALUES ( '4', '', '', '', 'Table Background', 'Title Header', 'Category Header', 'Table background', 'Row Color 1', 'Row Color 2', 'Serif', 'Sans-serif', 'Courier', 'Smallest', 'Typical', 'Largest', 'All text', '', '', '', '', '', '');

INSERT INTO phpbb_themes (themes_id, themes_name, head_stylesheet, body_background, body_bgcolor, body_text, body_link, body_vlink, body_alink, body_hlink, tr_color1, tr_color2, tr_color3, th_color1, th_color2, th_color3, td_color1, td_color2, td_color3, fontface1, fontface2, fontface3, fontsize1, fontsize2, fontsize3, fontcolor1, fontcolor2, fontcolor3, img1, img2, img3, img4) VALUES ( '5', 'PSO-Oranges and Lemons', '', '', 'F6FABC', '000000', '854F37', '488655', '', '', '', '', '', '000000', 'EAC33C', 'E8E660', 'F5ED91', 'FFEB8B', '', 'verdana,serif', 'arial,helvetica', 'courier', '1', '2', '3', '000000', '000000', '000000', '', '', '', '');
INSERT INTO phpbb_themes_name (themes_id, tr_color1_name, tr_color2_name, tr_color3_name, th_color1_name, th_color2_name, th_color3_name, td_color1_name, td_color2_name, td_color3_name, fontface1_name, fontface2_name, fontface3_name, fontsize1_name, fontsize2_name, fontsize3_name, fontcolor1_name, fontcolor2_name, fontcolor3_name, img1_name, img2_name, img3_name, img4_name) VALUES ( '5', '', '', '', 'Table Background', 'Title Header', 'Category Header', 'Table background', 'Row Color 1', 'Row Color 2', 'Serif', 'Sans-serif', 'Courier', 'Smallest', 'Typical', 'Largest', 'All text', '', '', '', '', '', '');
