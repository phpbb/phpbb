#
# Basic DB data for phpBB2 devel
#
# $Id$
#

# -- Config
INSERT INTO phpbb_config (config_name, config_value) VALUES ('active_sessions', '0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_attachments','1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_bbcode','1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_html','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_html_tags','b,i,u,pre');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_smilies','1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_sig','1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_namechange','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_topic_notify','1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_forum_notify','1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_avatar_local','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_avatar_remote','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_avatar_upload','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_nocensors','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_emailreuse','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_name_chars','.*?');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('board_disable','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('board_disable_msg','');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('board_dst','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('board_email_form','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('board_timezone','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('cookie_name','phpbb22');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('cookie_path','/');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('cookie_domain','');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('cookie_secure','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('default_style','1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('default_dateformat','D M d, Y g:i a');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('min_name_chars','3');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('max_name_chars','30');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('min_pass_chars','6');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('max_pass_chars','30');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('min_ratings','10');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('session_length','3600');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('sitename','yourdomain.com');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('site_desc','A _little_ text to describe your forum');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('override_user_style','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('posts_per_page','10');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('topics_per_page','25');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('hot_threshold','25');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('avatar_path','images/avatars/upload');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('avatar_gallery_path','images/avatars/gallery');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('smilies_path','images/smiles');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('icons_path','images/icons');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('ranks_path','images/ranks');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('email_enable','1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('privmsg_disable','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('gzip_compress','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('server_name', '');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('server_port', '');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('script_path', '');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('limit_load', '0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('load_online_time', '5');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('load_online', '1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('load_birthdays', '1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('load_moderators', '1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('load_search', '1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('load_search_upd', '1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('load_search_phr', '1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('load_db_lastread', '0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('load_db_track', '1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('load_onlinetrack', '0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('load_tplcompile', '0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('session_gc', '3600');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('search_gc', '7200');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('queue_interval', '600');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('ip_check', '4');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('browser_check', '1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('version', '2.1.2');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('max_post_chars', '0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('max_post_smilies', '0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('max_quote_depth', '3');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('max_sig_chars','255');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('max_poll_options','10');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('min_search_chars','3');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('max_search_chars','10');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('pm_max_boxes','4');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('pm_max_msgs','50');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('edit_time','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('display_last_edited', '1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('board_email_sig','Thanks, The Management');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('board_email','address@yourdomain.tld');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('board_contact','contact@yourdomain.tld');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('email_package_size','50');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('smtp_delivery','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('smtp_host','');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('smtp_port','25');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('smtp_username','');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('smtp_password','');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('jab_enable','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('jab_host','');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('jab_port','5222');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('jab_username','');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('jab_password','');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('jab_resource','');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('jab_aim_enable','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('jab_aim_user','');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('jab_aim_pass','');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('jab_icq_enable','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('jab_icq_user','');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('jab_icq_pass','');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('jab_msn_enable','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('jab_msn_user','');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('jab_msn_pass','');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('jab_yim_enable','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('jab_yim_user','');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('jab_yim_pass','');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('require_activation','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('flood_interval','15');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('search_interval','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('avatar_filesize','6144');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('avatar_min_width','20');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('avatar_min_height','20');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('avatar_max_width','90');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('avatar_max_height','90');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('coppa_enable','1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('coppa_fax', '');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('coppa_mail', '');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('enable_confirm', '0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('auth_method','db');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('ldap_server', '');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('ldap_base_dn', '');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('ldap_uid', '');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('lastread', '432000');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('display_order', '0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('max_filesize', '262144');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('max_filesize_pm','262144');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('attachment_quota', '52428800');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('max_attachments', '3');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('max_attachments_pm', '1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_pm_attach', '0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('upload_dir', 'files');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('img_display_inlined','1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('img_max_width','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('img_max_height','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('img_link_width','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('img_link_height','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('img_create_thumbnail','0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('img_min_thumb_filesize','12000');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('img_imagick', '');
INSERT INTO phpbb_config (config_name, config_value, is_dynamic) VALUES ('record_online_users', '0', 1);
INSERT INTO phpbb_config (config_name, config_value, is_dynamic) VALUES ('record_online_date', '0', 1);
INSERT INTO phpbb_config (config_name, config_value, is_dynamic) VALUES ('newest_user_id', '2', 1);
INSERT INTO phpbb_config (config_name, config_value, is_dynamic) VALUES ('newest_username', '', 1);
INSERT INTO phpbb_config (config_name, config_value, is_dynamic) VALUES ('num_users', '1', 1);
INSERT INTO phpbb_config (config_name, config_value, is_dynamic) VALUES ('num_posts', '1', 1);
INSERT INTO phpbb_config (config_name, config_value, is_dynamic) VALUES ('num_topics', '1', 1);
INSERT INTO phpbb_config (config_name, config_value, is_dynamic) VALUES ('session_last_gc', '0', 1);
INSERT INTO phpbb_config (config_name, config_value, is_dynamic) VALUES ('search_last_gc', '0', 1);
INSERT INTO phpbb_config (config_name, config_value, is_dynamic) VALUES ('last_queue_run', '0', 1);

# -- auth options
INSERT INTO phpbb_auth_options (auth_option, is_local) VALUES ('f_', 1);
INSERT INTO phpbb_auth_options (auth_option, is_local) VALUES ('f_list', 1);
INSERT INTO phpbb_auth_options (auth_option, is_local) VALUES ('f_read', 1);
INSERT INTO phpbb_auth_options (auth_option, is_local) VALUES ('f_post', 1);
INSERT INTO phpbb_auth_options (auth_option, is_local) VALUES ('f_reply', 1);
INSERT INTO phpbb_auth_options (auth_option, is_local) VALUES ('f_quote', 1);
INSERT INTO phpbb_auth_options (auth_option, is_local) VALUES ('f_edit', 1);
INSERT INTO phpbb_auth_options (auth_option, is_local) VALUES ('f_delete', 1);
INSERT INTO phpbb_auth_options (auth_option, is_local) VALUES ('f_poll', 1);
INSERT INTO phpbb_auth_options (auth_option, is_local) VALUES ('f_vote', 1);
INSERT INTO phpbb_auth_options (auth_option, is_local) VALUES ('f_votechg', 1);
INSERT INTO phpbb_auth_options (auth_option, is_local) VALUES ('f_announce', 1);
INSERT INTO phpbb_auth_options (auth_option, is_local) VALUES ('f_sticky', 1);
INSERT INTO phpbb_auth_options (auth_option, is_local) VALUES ('f_attach', 1);
INSERT INTO phpbb_auth_options (auth_option, is_local) VALUES ('f_download', 1);
INSERT INTO phpbb_auth_options (auth_option, is_local) VALUES ('f_html', 1);
INSERT INTO phpbb_auth_options (auth_option, is_local) VALUES ('f_bbcode', 1);
INSERT INTO phpbb_auth_options (auth_option, is_local) VALUES ('f_smilies', 1);
INSERT INTO phpbb_auth_options (auth_option, is_local) VALUES ('f_img', 1);
INSERT INTO phpbb_auth_options (auth_option, is_local) VALUES ('f_flash', 1);
INSERT INTO phpbb_auth_options (auth_option, is_local) VALUES ('f_sigs', 1);
INSERT INTO phpbb_auth_options (auth_option, is_local) VALUES ('f_search', 1);
INSERT INTO phpbb_auth_options (auth_option, is_local) VALUES ('f_email', 1);
INSERT INTO phpbb_auth_options (auth_option, is_local) VALUES ('f_rate', 1);
INSERT INTO phpbb_auth_options (auth_option, is_local) VALUES ('f_print', 1);
INSERT INTO phpbb_auth_options (auth_option, is_local) VALUES ('f_ignoreflood', 1);
INSERT INTO phpbb_auth_options (auth_option, is_local) VALUES ('f_postcount', 1);
INSERT INTO phpbb_auth_options (auth_option, is_local) VALUES ('f_moderate', 1);
INSERT INTO phpbb_auth_options (auth_option, is_local) VALUES ('f_report', 1);

INSERT INTO phpbb_auth_options (auth_option, is_local, is_global) VALUES ('m_', 1, 1);
INSERT INTO phpbb_auth_options (auth_option, is_local, is_global) VALUES ('m_edit', 1, 1);
INSERT INTO phpbb_auth_options (auth_option, is_local, is_global) VALUES ('m_delete', 1, 1);
INSERT INTO phpbb_auth_options (auth_option, is_local, is_global) VALUES ('m_move', 1, 1);
INSERT INTO phpbb_auth_options (auth_option, is_local, is_global) VALUES ('m_lock', 1, 1);
INSERT INTO phpbb_auth_options (auth_option, is_local, is_global) VALUES ('m_split', 1, 1);
INSERT INTO phpbb_auth_options (auth_option, is_local, is_global) VALUES ('m_merge', 1, 1);
INSERT INTO phpbb_auth_options (auth_option, is_local, is_global) VALUES ('m_approve', 1, 1);
INSERT INTO phpbb_auth_options (auth_option, is_local, is_global) VALUES ('m_unrate', 1, 1);
INSERT INTO phpbb_auth_options (auth_option, is_local, is_global) VALUES ('m_auth', 1, 1);
INSERT INTO phpbb_auth_options (auth_option, is_local, is_global) VALUES ('m_ip', 1, 1);
INSERT INTO phpbb_auth_options (auth_option, is_local, is_global) VALUES ('m_info', 1, 1);

INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('a_', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('a_server', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('a_defaults', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('a_board', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('a_cookies', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('a_clearlogs', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('a_words', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('a_icons', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('a_bbcode', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('a_attach', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('a_email', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('a_styles', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('a_user', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('a_useradd', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('a_userdel', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('a_ranks', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('a_ban', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('a_names', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('a_group', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('a_groupadd', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('a_groupdel', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('a_forum', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('a_forumadd', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('a_forumdel', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('a_prune', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('a_auth', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('a_authmods', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('a_authadmins', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('a_authusers', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('a_authgroups', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('a_authdeps', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('a_backup', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('a_restore', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('a_search', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('a_events', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('a_cron', 1);

INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('u_', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('u_sendemail', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('u_readpm', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('u_sendpm', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('u_sendim', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('u_hideonline', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('u_viewonline', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('u_viewprofile', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('u_chgavatar', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('u_chggrp', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('u_chgemail', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('u_chgname', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('u_chgpasswd', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('u_chgcensors', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('u_search', 1);
INSERT INTO phpbb_auth_options (auth_option, is_global) VALUES ('u_savedrafts', 1);


# MSSQL IDENTITY phpbb_styles ON #

# -- phpbb_styles
INSERT INTO phpbb_styles (style_id, style_name, style_copyright, template_id, theme_id, imageset_id) VALUES (1, 'subSilver', '&copy; phpBB Group', 1, 1, 1);

# MSSQL IDENTITY phpbb_styles OFF #


# MSSQL IDENTITY phpbb_styles_imageset ON #

# -- phpbb_styles_imageset
INSERT INTO phpbb_styles_imageset (imageset_id, imageset_name, imageset_copyright, imageset_path, btn_post, btn_post_pm, btn_reply, btn_reply_pm, btn_locked, btn_profile, btn_pm, btn_delete, btn_ip, btn_quote, btn_search, btn_edit, btn_report, btn_email, btn_www, btn_icq, btn_aim, btn_yim, btn_msnm, btn_online, btn_offline, icon_unapproved, icon_reported, icon_attach, icon_post, icon_post_new, icon_post_latest, icon_post_newest, forum, forum_new, forum_locked, forum_link, sub_forum, sub_forum_new, folder, folder_posted, folder_new, folder_new_posted, folder_hot, folder_hot_posted, folder_hot_new, folder_hot_new_posted, folder_locked, folder_locked_posted, folder_locked_new, folder_locked_new_posted, folder_sticky, folder_sticky_posted, folder_sticky_new, folder_sticky_new_posted, folder_announce, folder_announce_posted, folder_announce_new, folder_announce_new_posted, poll_left, poll_center, poll_right) VALUES (1, 'subSilver', '&copy; phpBB Group', 'subSilver', '"styles/subSilver/imageset/{LANG}/btn_post.gif" width="82" height="25" border="0"', '"styles/subSilver/imageset/{LANG}/btn_post_pm.gif" width="82" height="25" border="0"', '"styles/subSilver/imageset/{LANG}/btn_reply.gif" width="82" height="25" border="0"', '"styles/subSilver/imageset/{LANG}/reply.gif" width="88" height="25" border="0"', '"styles/subSilver/imageset/{LANG}/btn_locked.gif" width="82" height="25" border="0"', '"styles/subSilver/imageset/{LANG}/btn_profile.gif" width="59" height="18" border="0"', '"styles/subSilver/imageset/{LANG}/btn_pm.gif" width="59" height="18" border="0"', '"styles/subSilver/imageset/{LANG}/btn_delete.gif" width="16" height="18" border="0"', '"styles/subSilver/imageset/{LANG}/btn_ip.gif" width="16" height="18" border="0"', '"styles/subSilver/imageset/{LANG}/btn_quote.gif" width="59" height="18" border="0"', '"styles/subSilver/imageset/{LANG}/btn_search.gif" width="59" height="18" border="0"', '"styles/subSilver/imageset/{LANG}/btn_edit.gif" width="59" height="18" border="0"', '"styles/subSilver/imageset/{LANG}/btn_report.gif" width="59" height="18" border="0"', '"styles/subSilver/imageset/{LANG}/btn_email.gif" width="59" height="18" border="0"', '"styles/subSilver/imageset/{LANG}/btn_www.gif" width="59" height="18" border="0"', '"styles/subSilver/imageset/{LANG}/btn_icq.gif" width="59" height="18" border="0"', '"styles/subSilver/imageset/{LANG}/btn_aim.gif" width="59" height="18" border="0"', '"styles/subSilver/imageset/{LANG}/btn_yim.gif" width="59" height="18" border="0"', '"styles/subSilver/imageset/{LANG}/btn_msnm.gif" width="59" height="18" border="0"', '"styles/subSilver/imageset/{LANG}/btn_online.gif" width="59" height="18" border="0"', '"styles/subSilver/imageset/{LANG}/btn_offline.gif" width="59" height="18" border="0"', '"styles/subSilver/imageset/icon_unapproved.gif" width="19" height="18" border="0"', '"styles/subSilver/imageset/icon_reported.gif" width="19" height="18" border="0"', '"styles/subSilver/imageset/icon_attach.gif" width="14" height="18" border="0"', '"styles/subSilver/imageset/icon_minipost.gif" width="12" height="9" border="0"', '"styles/subSilver/imageset/icon_minipost_new.gif" width="12" height="9" border="0"', '"styles/subSilver/imageset/icon_latest_reply.gif" width="18" height="9" border="0"', '"styles/subSilver/imageset/icon_newest_reply.gif" width="18" height="9" border="0"', '"styles/subSilver/imageset/folder_big.gif" width="46" height="25" border="0"', '"styles/subSilver/imageset/folder_new_big.gif" width="46" height="25" border="0"', '"styles/subSilver/imageset/folder_locked_big.gif" width="46" height="25" border="0"', '"styles/subSilver/imageset/folder_link_big.gif" width="46" height="25" border="0"', '"styles/subSilver/imageset/subfolder_big.gif" width="46" height="25" border="0"', '"styles/subSilver/imageset/subfolder_new_big.gif" width="46" height="25" border="0"', '"styles/subSilver/imageset/folder.gif" width="19" height="18" border="0"', '"styles/subSilver/imageset/folder_posted.gif" width="19" height="18" border="0"', '"styles/subSilver/imageset/folder_new.gif" width="19" height="18" border="0"', '"styles/subSilver/imageset/folder_new_posted.gif" width="19" height="18" border="0"', '"styles/subSilver/imageset/folder_hot.gif" width="19" height="18" border="0"', '"styles/subSilver/imageset/folder_hot_posted.gif" width="19" height="18" border="0"', '"styles/subSilver/imageset/folder_new_hot.gif" width="19" height="18" border="0"', '"styles/subSilver/imageset/folder_new_hot_posted.gif" width="19" height="18" border="0"', '"styles/subSilver/imageset/folder_lock.gif" width="19" height="18" border="0"', '"styles/subSilver/imageset/folder_lock_posted.gif" width="19" height="18" border="0"', '"styles/subSilver/imageset/folder_lock_new.gif" width="19" height="18" border="0"', '"styles/subSilver/imageset/folder_lock_new_posted.gif" width="19" height="18" border="0"', '"styles/subSilver/imageset/folder_sticky.gif" width="19" height="18" border="0"', '"styles/subSilver/imageset/folder_sticky_posted.gif" width="19" height="18" border="0"', '"styles/subSilver/imageset/folder_sticky_new.gif" width="19" height="18" border="0"', '"styles/subSilver/imageset/folder_sticky_new_posted.gif" width="19" height="18" border="0"', '"styles/subSilver/imageset/folder_announce.gif" width="19" height="18" border="0"', '"styles/subSilver/imageset/folder_announce_posted.gif" width="19" height="18" border="0"', '"styles/subSilver/imageset/folder_announce_new.gif" width="19" height="18" border="0"', '"styles/subSilver/imageset/folder_announce_new_posted.gif" width="19" height="18" border="0"', '"styles/subSilver/imageset/vote_lcap.gif" width="4" height="12" border="0"', '"styles/subSilver/imageset/voting_bar.gif" height="12" border="0"', '"styles/subSilver/imageset/vote_rcap.gif" width="4" height="12" border="0"');

# MSSQL IDENTITY phpbb_styles_imageset OFF #


# MSSQL IDENTITY phpbb_styles_template ON #

# -- phpbb_styles_template
INSERT INTO phpbb_styles_template (template_id, template_name, template_copyright, template_path, bbcode_bitfield) VALUES (1, 'subSilver', '&copy; phpBB Group', 'subSilver', 2817);

# MSSQL IDENTITY phpbb_styles_template OFF #


# MSSQL IDENTITY phpbb_styles_theme ON #

# -- phpbb_styles_theme
INSERT INTO phpbb_styles_theme (theme_id, theme_name, theme_copyright, theme_path, theme_data) VALUES (1, 'subSilver', '&copy; phpBB Group', 'subSilver', '');

# MSSQL IDENTITY phpbb_styles_theme OFF #


# MSSQL IDENTITY phpbb_lang ON #

# -- Language
INSERT INTO phpbb_lang (lang_id, lang_iso, lang_dir, lang_english_name, lang_local_name, lang_author) VALUES (1, 'en', 'en', 'English [ UK ]', 'English [ UK ]', 'phpBB Group');

# MSSQL IDENTITY phpbb_lang OFF #


# MSSQL IDENTITY phpbb_forums ON #

# -- Forums
INSERT INTO phpbb_forums (forum_id, forum_name, forum_desc, left_id, right_id, parent_id, forum_type, forum_posts, forum_topics, forum_topics_real, forum_last_post_id, forum_last_poster_id, forum_last_poster_name, forum_last_post_time) VALUES (1, 'My first Category', '', 1, 4, 0, 0, 1, 1, 1, 1, 2, 'Admin', 972086460);

INSERT INTO phpbb_forums (forum_id, forum_name, forum_desc, left_id, right_id, parent_id, forum_type, forum_posts, forum_topics, forum_topics_real, forum_last_post_id, forum_last_poster_id, forum_last_poster_name, forum_last_post_time) VALUES (2, 'Test Forum 1', 'This is just a test forum.', 2, 3, 1, 1, 1, 1, 1, 1, 2, 'Admin', 972086460);

# MSSQL IDENTITY phpbb_forums OFF #


# MSSQL IDENTITY phpbb_users ON #

# -- Users
INSERT INTO phpbb_users (user_id, user_founder, group_id, username, user_regdate, user_password, user_email, user_lang, user_style) VALUES (1, 0, 1, 'Anonymous', 0, '', '', 'en', 1);

# -- username: Admin    password: admin (change this or remove it ON #ce everything is working!)
INSERT INTO phpbb_users (user_id, user_founder, group_id, username, user_regdate, user_password, user_email, user_lang, user_style, user_rank, user_colour) VALUES (2, 1, 7, 'Admin', 0, '21232f297a57a5a743894a0e4a801fc3', 'admin@yourdomain.com', 'en', 1, 1, 'AA0000');

# MSSQL IDENTITY phpbb_users OFF #


# MSSQL IDENTITY phpbb_ranks ON #

# -- Ranks
INSERT INTO phpbb_ranks (rank_id, rank_title, rank_min, rank_special, rank_image) VALUES (1, 'Site Admin', -1, 1, NULL);

# MSSQL IDENTITY phpbb_ranks OFF #


# MSSQL IDENTITY phpbb_groups ON #

# -- Groups
INSERT INTO phpbb_groups (group_id, group_name, group_type) VALUES (1, 'GUESTS', 3);
INSERT INTO phpbb_groups (group_id, group_name, group_type) VALUES (2, 'INACTIVE', 3);
INSERT INTO phpbb_groups (group_id, group_name, group_type) VALUES (3, 'INACTIVE_COPPA', 3);
INSERT INTO phpbb_groups (group_id, group_name, group_type) VALUES (4, 'REGISTERED', 3);
INSERT INTO phpbb_groups (group_id, group_name, group_type) VALUES (5, 'REGISTERED_COPPA', 3);
INSERT INTO phpbb_groups (group_id, group_name, group_type, group_colour) VALUES (6, 'SUPER_MODERATORS', 3, '00AA00');
INSERT INTO phpbb_groups (group_id, group_name, group_type, group_colour) VALUES (7, 'ADMINISTRATORS', 3, 'AA0000');
INSERT INTO phpbb_groups (group_id, group_name, group_type) VALUES (8, 'BANNED', 3);

# MSSQL IDENTITY phpbb_groups OFF #


# -- User -> Group
INSERT INTO phpbb_user_group (group_id, user_id, user_pending, group_leader) VALUES (1, 1, 0, 0);
INSERT INTO phpbb_user_group (group_id, user_id, user_pending, group_leader) VALUES (4, 2, 0, 0);
INSERT INTO phpbb_user_group (group_id, user_id, user_pending, group_leader) VALUES (7, 2, 0, 1);


# -- Modules

# MSSQL IDENTITY phpbb_modules OFF #

INSERT INTO phpbb_modules (module_type, module_title, module_filename, module_order, module_enabled, module_subs, module_acl) VALUES ('mcp', 'MAIN', 'main', 1, 1, '', '');
INSERT INTO phpbb_modules (module_type, module_title, module_filename, module_order, module_enabled, module_subs, module_acl) VALUES ('ucp', 'MAIN', 'main', 1, 1, 'front\r\nsubscribed\r\ndrafts', '');
INSERT INTO phpbb_modules (module_type, module_title, module_filename, module_order, module_enabled, module_subs, module_acl) VALUES ('ucp', 'PROFILE', 'profile', 2, 1, 'profile_info\r\nreg_details\r\nsignature\r\navatar', '');
INSERT INTO phpbb_modules (module_type, module_title, module_filename, module_order, module_enabled, module_subs, module_acl) VALUES ('ucp', 'PREFS', 'prefs', 3, 1, 'personal\r\nview\r\npost', '');
INSERT INTO phpbb_modules (module_type, module_title, module_filename, module_order, module_enabled, module_subs, module_acl) VALUES ('ucp', 'ZEBRA', 'zebra', 4, 1, 'friends\r\nfoes', '');

# MSSQL IDENTITY phpbb_modules OFF #


# Permissions

# Default user - admin rights
INSERT INTO phpbb_auth_users (user_id, forum_id, auth_option_id, auth_setting) SELECT 2, 0, auth_option_id, 1 FROM phpbb_auth_options WHERE auth_option LIKE 'u_%';
INSERT INTO phpbb_auth_users (user_id, forum_id, auth_option_id, auth_setting) SELECT 2, 0, auth_option_id, 1 FROM phpbb_auth_options WHERE auth_option LIKE 'a_%';
INSERT INTO phpbb_auth_users (user_id, forum_id, auth_option_id, auth_setting) SELECT 2, 1, auth_option_id, 1 FROM phpbb_auth_options WHERE auth_option IN ('f_poll', 'f_announce', 'f_sticky', 'f_attach', 'f_html');
INSERT INTO phpbb_auth_users (user_id, forum_id, auth_option_id, auth_setting) SELECT 2, 2, auth_option_id, 1 FROM phpbb_auth_options WHERE auth_option IN ('f_poll', 'f_announce', 'f_sticky', 'f_attach', 'f_html');

# Default user - moderation rights
INSERT INTO phpbb_auth_users (user_id, forum_id, auth_option_id, auth_setting) SELECT 2, 1, auth_option_id, 1 FROM phpbb_auth_options WHERE auth_option LIKE 'm_%';
INSERT INTO phpbb_auth_users (user_id, forum_id, auth_option_id, auth_setting) SELECT 2, 2, auth_option_id, 1 FROM phpbb_auth_options WHERE auth_option LIKE 'm_%';

# ADMINISTRATOR group - admin and + forum rights
INSERT INTO phpbb_auth_groups (group_id, forum_id, auth_option_id, auth_setting) SELECT 7, 0, auth_option_id, 1 FROM phpbb_auth_options WHERE auth_option LIKE 'u_%';
INSERT INTO phpbb_auth_groups (group_id, forum_id, auth_option_id, auth_setting) SELECT 7, 0, auth_option_id, 1 FROM phpbb_auth_options WHERE auth_option LIKE 'a_%';
INSERT INTO phpbb_auth_groups (group_id, forum_id, auth_option_id, auth_setting) SELECT 7, 1, auth_option_id, 1 FROM phpbb_auth_options WHERE auth_option IN ('f_poll', 'f_announce', 'f_sticky', 'f_attach', 'f_html');
INSERT INTO phpbb_auth_groups (group_id, forum_id, auth_option_id, auth_setting) SELECT 7, 2, auth_option_id, 1 FROM phpbb_auth_options WHERE auth_option IN ('f_poll', 'f_announce', 'f_sticky', 'f_attach', 'f_html');

# SUPER MODERATOR gorup - moderator rights
INSERT INTO phpbb_auth_groups (group_id, forum_id, auth_option_id, auth_setting) SELECT 6, 0, auth_option_id, 1 FROM phpbb_auth_options WHERE auth_option LIKE 'u_%' AND auth_option NOT IN ('u_chggrp', 'u_chgname');
INSERT INTO phpbb_auth_groups (group_id, forum_id, auth_option_id, auth_setting) SELECT 6, 0, auth_option_id, 1 FROM phpbb_auth_options WHERE auth_option LIKE 'm_%';

# REGISTERED/REGISTERED COPPA groups - common forum rights
INSERT INTO phpbb_auth_groups (group_id, forum_id, auth_option_id, auth_setting) SELECT 4, 0, auth_option_id, 1 FROM phpbb_auth_options WHERE auth_option LIKE 'u_%' AND auth_option NOT IN ('u_chggrp', 'u_viewonline', 'u_chgname');
INSERT INTO phpbb_auth_groups (group_id, forum_id, auth_option_id, auth_setting) SELECT 4, 1, auth_option_id, 1 FROM phpbb_auth_options WHERE auth_option IN ('f_', 'f_list', 'f_read', 'f_post', 'f_reply', 'f_quote', 'f_edit', 'f_delete', 'f_vote', 'f_download', 'f_bbcode', 'f_smilies', 'f_img', 'f_flash', 'f_sigs', 'f_search', 'f_email', 'f_print', 'f_postcount');
INSERT INTO phpbb_auth_groups (group_id, forum_id, auth_option_id, auth_setting) SELECT 4, 2, auth_option_id, 1 FROM phpbb_auth_options WHERE auth_option IN ('f_', 'f_list', 'f_read', 'f_post', 'f_reply', 'f_quote', 'f_edit', 'f_delete', 'f_vote', 'f_votechg', 'f_download', 'f_bbcode', 'f_smilies', 'f_img', 'f_flash', 'f_sigs', 'f_search', 'f_email', 'f_print', 'f_postcount', 'f_report');
INSERT INTO phpbb_auth_groups (group_id, forum_id, auth_option_id, auth_setting) SELECT 5, 0, auth_option_id, 1 FROM phpbb_auth_options WHERE auth_option LIKE 'u_%' AND auth_option NOT IN ('u_chgcensors', 'u_chggrp', 'u_viewonline', 'u_chgname');
INSERT INTO phpbb_auth_groups (group_id, forum_id, auth_option_id, auth_setting) SELECT 5, 1, auth_option_id, 1 FROM phpbb_auth_options WHERE auth_option IN ('f_', 'f_list', 'f_read', 'f_post', 'f_reply', 'f_quote', 'f_edit', 'f_delete', 'f_vote', 'f_download', 'f_bbcode', 'f_smilies', 'f_img', 'f_flash', 'f_sigs', 'f_search', 'f_email', 'f_print', 'f_postcount');
INSERT INTO phpbb_auth_groups (group_id, forum_id, auth_option_id, auth_setting) SELECT 5, 2, auth_option_id, 1 FROM phpbb_auth_options WHERE auth_option IN ('f_', 'f_list', 'f_read', 'f_post', 'f_reply', 'f_quote', 'f_edit', 'f_delete', 'f_vote', 'f_votechg', 'f_download', 'f_bbcode', 'f_smilies', 'f_img', 'f_flash', 'f_sigs', 'f_search', 'f_email', 'f_print', 'f_postcount', 'f_report');

# GUESTS, INACTIVE, INACTIVE_COPPA group - basic rights
INSERT INTO phpbb_auth_groups (group_id, forum_id, auth_option_id, auth_setting) SELECT 1, 1, auth_option_id, 1 FROM phpbb_auth_options WHERE auth_option IN ('f_list', 'f_read', 'f_post', 'f_reply', 'f_quote', 'f_bbcode', 'f_search', 'f_print');
INSERT INTO phpbb_auth_groups (group_id, forum_id, auth_option_id, auth_setting) SELECT 1, 2, auth_option_id, 1 FROM phpbb_auth_options WHERE auth_option IN ('f_list', 'f_read', 'f_post', 'f_reply', 'f_quote', 'f_bbcode', 'f_search', 'f_print');
INSERT INTO phpbb_auth_groups (group_id, forum_id, auth_option_id, auth_setting) SELECT 2, 1, auth_option_id, 1 FROM phpbb_auth_options WHERE auth_option IN ('f_list', 'f_read', 'f_post', 'f_reply', 'f_quote', 'f_bbcode', 'f_search', 'f_print');
INSERT INTO phpbb_auth_groups (group_id, forum_id, auth_option_id, auth_setting) SELECT 2, 2, auth_option_id, 1 FROM phpbb_auth_options WHERE auth_option IN ('f_list', 'f_read', 'f_post', 'f_reply', 'f_quote', 'f_bbcode', 'f_search', 'f_print');
INSERT INTO phpbb_auth_groups (group_id, forum_id, auth_option_id, auth_setting) SELECT 3, 1, auth_option_id, 1 FROM phpbb_auth_options WHERE auth_option IN ('f_list', 'f_read', 'f_post', 'f_reply', 'f_quote', 'f_bbcode', 'f_search', 'f_print');
INSERT INTO phpbb_auth_groups (group_id, forum_id, auth_option_id, auth_setting) SELECT 3, 2, auth_option_id, 1 FROM phpbb_auth_options WHERE auth_option IN ('f_list', 'f_read', 'f_post', 'f_reply', 'f_quote', 'f_bbcode', 'f_search', 'f_print');


# -- Moderator cache
INSERT INTO phpbb_moderator_cache (user_id, forum_id, username) VALUES (2, 2, 'Admin');


# MSSQL IDENTITY phpbb_topics ON #

# -- Demo Topic
INSERT INTO phpbb_topics (topic_id, topic_title, topic_poster, topic_time, topic_views, topic_replies, topic_replies_real, forum_id, topic_status, topic_type, topic_first_post_id, topic_first_poster_name, topic_last_post_id, topic_last_poster_id, topic_last_poster_name, topic_last_post_time, topic_last_view_time) VALUES (1, 'Welcome to phpBB 2', 2, 972086460, 0, 0, 0, 2, 0, 0, 1, 'Admin', 1, 2, 'Admin', 972086460, 972086460);

# MSSQL IDENTITY phpbb_topics OFF #


# MSSQL IDENTITY phpbb_posts ON #

# -- Demo Post
INSERT INTO phpbb_posts (post_id, topic_id, forum_id, poster_id, post_time, post_username, poster_ip, post_subject, post_text) VALUES (1, 1, 2, 2, 972086460, NULL, '127.0.0.1', 'Welcome to phpBB 2', 'This is an example post in your phpBB 2.2 installation. You may delete this post, this topic and even this forum if you like since everything seems to be working!');

# MSSQL IDENTITY phpbb_posts OFF #


# -- Smilies
INSERT INTO phpbb_smilies (code, smile_url, emoticon, smile_width, smile_height) VALUES (':D', 'icon_biggrin.gif', 'Very Happy', 15, 15);
INSERT INTO phpbb_smilies (code, smile_url, emoticon, smile_width, smile_height) VALUES (':)', 'icon_smile.gif', 'Smile', 15, 15);
INSERT INTO phpbb_smilies (code, smile_url, emoticon, smile_width, smile_height) VALUES (':(', 'icon_sad.gif', 'Sad', 15, 15);
INSERT INTO phpbb_smilies (code, smile_url, emoticon, smile_width, smile_height) VALUES (':o', 'icon_surprised.gif', 'Surprised', 15, 15);
INSERT INTO phpbb_smilies (code, smile_url, emoticon, smile_width, smile_height) VALUES (':eek:', 'icon_surprised.gif', 'Surprised', 15, 15);
INSERT INTO phpbb_smilies (code, smile_url, emoticon, smile_width, smile_height) VALUES ('8O', 'icon_eek.gif', 'Shocked', 15, 15);
INSERT INTO phpbb_smilies (code, smile_url, emoticon, smile_width, smile_height) VALUES (':?', 'icon_confused.gif', 'Confused', 15, 15);
INSERT INTO phpbb_smilies (code, smile_url, emoticon, smile_width, smile_height) VALUES ('8)', 'icon_cool.gif', 'Cool', 15, 15);
INSERT INTO phpbb_smilies (code, smile_url, emoticon, smile_width, smile_height) VALUES (':lol:', 'icon_lol.gif', 'Laughing', 15, 15);
INSERT INTO phpbb_smilies (code, smile_url, emoticon, smile_width, smile_height) VALUES (':x', 'icon_mad.gif', 'Mad', 15, 15);
INSERT INTO phpbb_smilies (code, smile_url, emoticon, smile_width, smile_height) VALUES (':P', 'icon_razz.gif', 'Razz', 15, 15);
INSERT INTO phpbb_smilies (code, smile_url, emoticon, smile_width, smile_height) VALUES (':oops:', 'icon_redface.gif', 'Embarassed', 15, 15);
INSERT INTO phpbb_smilies (code, smile_url, emoticon, smile_width, smile_height) VALUES (':cry:', 'icon_cry.gif', 'Crying or Very sad', 15, 15);
INSERT INTO phpbb_smilies (code, smile_url, emoticon, smile_width, smile_height) VALUES (':evil:', 'icon_evil.gif', 'Evil or Very Mad', 15, 15);
INSERT INTO phpbb_smilies (code, smile_url, emoticon, smile_width, smile_height) VALUES (':twisted:', 'icon_twisted.gif', 'Twisted Evil', 15, 15);
INSERT INTO phpbb_smilies (code, smile_url, emoticon, smile_width, smile_height) VALUES (':roll:', 'icon_rolleyes.gif', 'Rolling Eyes', 15, 15);
INSERT INTO phpbb_smilies (code, smile_url, emoticon, smile_width, smile_height) VALUES (';)', 'icon_wink.gif', 'Wink', 15, 15);
INSERT INTO phpbb_smilies (code, smile_url, emoticon, smile_width, smile_height) VALUES (':!:', 'icon_exclaim.gif', 'Exclamation', 15, 15);
INSERT INTO phpbb_smilies (code, smile_url, emoticon, smile_width, smile_height) VALUES (':?:', 'icon_question.gif', 'Question', 15, 15);
INSERT INTO phpbb_smilies (code, smile_url, emoticon, smile_width, smile_height) VALUES (':idea:', 'icon_idea.gif', 'Idea', 15, 15);
INSERT INTO phpbb_smilies (code, smile_url, emoticon, smile_width, smile_height) VALUES (':arrow:', 'icon_arrow.gif', 'Arrow', 15, 15);
INSERT INTO phpbb_smilies (code, smile_url, emoticon, smile_width, smile_height) VALUES (':|', 'icon_neutral.gif', 'Neutral', 15, 15);
INSERT INTO phpbb_smilies (code, smile_url, emoticon, smile_width, smile_height) VALUES (':mrgreen:', 'icon_mrgreen.gif', 'Mr. Green', 15, 15);


# -- icons ... these are just some of those in CVS
INSERT INTO phpbb_icons (icons_url, icons_width, icons_height, icons_order, display_on_posting) VALUES ('misc/arrow_bold_rgt.gif', 19, 19, 1, 1);
INSERT INTO phpbb_icons (icons_url, icons_width, icons_height, icons_order, display_on_posting) VALUES ('smile/redface_anim.gif', 19, 19, 9, 1);
INSERT INTO phpbb_icons (icons_url, icons_width, icons_height, icons_order, display_on_posting) VALUES ('smile/mr_green.gif', 19, 19, 10, 1);
INSERT INTO phpbb_icons (icons_url, icons_width, icons_height, icons_order, display_on_posting) VALUES ('misc/musical.gif', 19, 19, 4, 1);
INSERT INTO phpbb_icons (icons_url, icons_width, icons_height, icons_order, display_on_posting) VALUES ('misc/asterix.gif', 19, 19, 2, 1);
INSERT INTO phpbb_icons (icons_url, icons_width, icons_height, icons_order, display_on_posting) VALUES ('misc/square.gif', 19, 19, 3, 1);
INSERT INTO phpbb_icons (icons_url, icons_width, icons_height, icons_order, display_on_posting) VALUES ('smile/alien_grn.gif', 19, 19, 5, 1);
INSERT INTO phpbb_icons (icons_url, icons_width, icons_height, icons_order, display_on_posting) VALUES ('smile/idea.gif', 19, 19, 8, 1);
INSERT INTO phpbb_icons (icons_url, icons_width, icons_height, icons_order, display_on_posting) VALUES ('smile/question.gif', 19, 19, 6, 1);
INSERT INTO phpbb_icons (icons_url, icons_width, icons_height, icons_order, display_on_posting) VALUES ('smile/exclaim.gif', 19, 19, 7, 1);

    
# -- ucp modules
INSERT INTO phpbb_ucp_modules (module_id, module_title, module_filename, module_order) VALUES (1, 'MAIN', 'main', 1);
INSERT INTO phpbb_ucp_modules (module_id, module_title, module_filename, module_order) VALUES (2, 'PROFILE', 'profile', 2);
INSERT INTO phpbb_ucp_modules (module_id, module_title, module_filename, module_order) VALUES (3, 'PREFERENCES', 'prefs', 3);
INSERT INTO phpbb_ucp_modules (module_id, module_title, module_filename, module_order) VALUES (4, 'MESSAGING', 'pm', 4);
INSERT INTO phpbb_ucp_modules (module_id, module_title, module_filename, module_order) VALUES (5, 'LISTS', 'zebra', 5);


# MSSQL IDENTITY phpbb_search_wordlist ON #

# -- wordlist
INSERT INTO phpbb_search_wordlist (word_id, word_text, word_common) VALUES (1, 'example', 0);
INSERT INTO phpbb_search_wordlist (word_id, word_text, word_common) VALUES (2, 'post', 0);
INSERT INTO phpbb_search_wordlist (word_id, word_text, word_common) VALUES (3, 'phpbb', 0);
INSERT INTO phpbb_search_wordlist (word_id, word_text, word_common) VALUES (4, 'installation', 0);
INSERT INTO phpbb_search_wordlist (word_id, word_text, word_common) VALUES (5, 'delete', 0);
INSERT INTO phpbb_search_wordlist (word_id, word_text, word_common) VALUES (6, 'topic', 0);
INSERT INTO phpbb_search_wordlist (word_id, word_text, word_common) VALUES (7, 'forum', 0);
INSERT INTO phpbb_search_wordlist (word_id, word_text, word_common) VALUES (8, 'since', 0);
INSERT INTO phpbb_search_wordlist (word_id, word_text, word_common) VALUES (9, 'everything', 0);
INSERT INTO phpbb_search_wordlist (word_id, word_text, word_common) VALUES (10, 'seems', 0);
INSERT INTO phpbb_search_wordlist (word_id, word_text, word_common) VALUES (11, 'working', 0);
INSERT INTO phpbb_search_wordlist (word_id, word_text, word_common) VALUES (12, 'welcome', 0);

# MSSQL IDENTITY phpbb_search_wordlist OFF #


# MSSQL IDENTITY phpbb_search_wordmatch ON #

# -- wordmatch
INSERT INTO phpbb_search_wordmatch (word_id, post_id, title_match) VALUES (1, 1, 0);
INSERT INTO phpbb_search_wordmatch (word_id, post_id, title_match) VALUES (2, 1, 0);
INSERT INTO phpbb_search_wordmatch (word_id, post_id, title_match) VALUES (3, 1, 0);
INSERT INTO phpbb_search_wordmatch (word_id, post_id, title_match) VALUES (4, 1, 0);
INSERT INTO phpbb_search_wordmatch (word_id, post_id, title_match) VALUES (5, 1, 0);
INSERT INTO phpbb_search_wordmatch (word_id, post_id, title_match) VALUES (6, 1, 0);
INSERT INTO phpbb_search_wordmatch (word_id, post_id, title_match) VALUES (7, 1, 0);
INSERT INTO phpbb_search_wordmatch (word_id, post_id, title_match) VALUES (8, 1, 0);
INSERT INTO phpbb_search_wordmatch (word_id, post_id, title_match) VALUES (9, 1, 0);
INSERT INTO phpbb_search_wordmatch (word_id, post_id, title_match) VALUES (10, 1, 0);
INSERT INTO phpbb_search_wordmatch (word_id, post_id, title_match) VALUES (11, 1, 0);
INSERT INTO phpbb_search_wordmatch (word_id, post_id, title_match) VALUES (12, 1, 1);
INSERT INTO phpbb_search_wordmatch (word_id, post_id, title_match) VALUES (3, 1, 1);

# MSSQL IDENTITY phpbb_search_wordmatch OFF #


# MSSQL IDENTITY phpbb_reports_reasons ON #

# -- reasons
INSERT INTO phpbb_reports_reasons (reason_id, reason_priority, reason_name, reason_description) VALUES (1, 3, 'warez', 'The reported post contains links to pirated or illegal software');
INSERT INTO phpbb_reports_reasons (reason_id, reason_priority, reason_name, reason_description) VALUES (2, 2, 'spam', 'The reported post has for only purpose to advertise for a website or another product');
INSERT INTO phpbb_reports_reasons (reason_id, reason_priority, reason_name, reason_description) VALUES (3, 1, 'off_topic', 'The reported post is off topic');
INSERT INTO phpbb_reports_reasons (reason_id, reason_priority, reason_name, reason_description) VALUES (4, 0, 'other', 'The reported post does not fit into any other category, please use the description field');

# MSSQL IDENTITY phpbb_reports_reasons OFF #

# MSSQL IDENTITY phpbb_extension_groups ON #

# -- extension_groups
INSERT INTO phpbb_extension_groups (group_id, group_name, cat_id, allow_group, download_mode, upload_icon, max_filesize) VALUES (1, 'Images', 1, 1, 1, '', 0);
INSERT INTO phpbb_extension_groups (group_id, group_name, cat_id, allow_group, download_mode, upload_icon, max_filesize) VALUES (2, 'Archives', 0, 1, 1, '', 0);
INSERT INTO phpbb_extension_groups (group_id, group_name, cat_id, allow_group, download_mode, upload_icon, max_filesize) VALUES (3, 'Plain Text', 0, 0, 1, '', 0);
INSERT INTO phpbb_extension_groups (group_id, group_name, cat_id, allow_group, download_mode, upload_icon, max_filesize) VALUES (4, 'Documents', 0, 0, 1, '', 0);
INSERT INTO phpbb_extension_groups (group_id, group_name, cat_id, allow_group, download_mode, upload_icon, max_filesize) VALUES (5, 'Real Media', 3, 0, 2, '', 0);
INSERT INTO phpbb_extension_groups (group_id, group_name, cat_id, allow_group, download_mode, upload_icon, max_filesize) VALUES (6, 'Windows Media', 2, 0, 1, '', 0);

# MSSQL IDENTITY phpbb_extension_groups OFF #


# MSSQL IDENTITY phpbb_extensions ON #

# -- extensions
INSERT INTO phpbb_extensions (extension_id, group_id, extension, comment) VALUES (1, 1, 'gif', '');
INSERT INTO phpbb_extensions (extension_id, group_id, extension, comment) VALUES (2, 1, 'png', '');
INSERT INTO phpbb_extensions (extension_id, group_id, extension, comment) VALUES (3, 1, 'jpeg', '');
INSERT INTO phpbb_extensions (extension_id, group_id, extension, comment) VALUES (4, 1, 'jpg', '');
INSERT INTO phpbb_extensions (extension_id, group_id, extension, comment) VALUES (5, 1, 'tif', '');
INSERT INTO phpbb_extensions (extension_id, group_id, extension, comment) VALUES (6, 1, 'tga', '');
INSERT INTO phpbb_extensions (extension_id, group_id, extension, comment) VALUES (7, 2, 'gtar', '');
INSERT INTO phpbb_extensions (extension_id, group_id, extension, comment) VALUES (8, 2, 'gz', '');
INSERT INTO phpbb_extensions (extension_id, group_id, extension, comment) VALUES (9, 2, 'tar', '');
INSERT INTO phpbb_extensions (extension_id, group_id, extension, comment) VALUES (10, 2, 'zip', '');
INSERT INTO phpbb_extensions (extension_id, group_id, extension, comment) VALUES (11, 2, 'rar', '');
INSERT INTO phpbb_extensions (extension_id, group_id, extension, comment) VALUES (12, 2, 'ace', '');
INSERT INTO phpbb_extensions (extension_id, group_id, extension, comment) VALUES (13, 3, 'txt', '');
INSERT INTO phpbb_extensions (extension_id, group_id, extension, comment) VALUES (14, 3, 'c', '');
INSERT INTO phpbb_extensions (extension_id, group_id, extension, comment) VALUES (15, 3, 'h', '');
INSERT INTO phpbb_extensions (extension_id, group_id, extension, comment) VALUES (16, 3, 'cpp', '');
INSERT INTO phpbb_extensions (extension_id, group_id, extension, comment) VALUES (17, 3, 'hpp', '');
INSERT INTO phpbb_extensions (extension_id, group_id, extension, comment) VALUES (18, 3, 'diz', '');
INSERT INTO phpbb_extensions (extension_id, group_id, extension, comment) VALUES (19, 4, 'xls', '');
INSERT INTO phpbb_extensions (extension_id, group_id, extension, comment) VALUES (20, 4, 'doc', '');
INSERT INTO phpbb_extensions (extension_id, group_id, extension, comment) VALUES (21, 4, 'dot', '');
INSERT INTO phpbb_extensions (extension_id, group_id, extension, comment) VALUES (22, 4, 'pdf', '');
INSERT INTO phpbb_extensions (extension_id, group_id, extension, comment) VALUES (23, 4, 'ai', '');
INSERT INTO phpbb_extensions (extension_id, group_id, extension, comment) VALUES (24, 4, 'ps', '');
INSERT INTO phpbb_extensions (extension_id, group_id, extension, comment) VALUES (25, 4, 'ppt', '');
INSERT INTO phpbb_extensions (extension_id, group_id, extension, comment) VALUES (26, 5, 'rm', '');
INSERT INTO phpbb_extensions (extension_id, group_id, extension, comment) VALUES (27, 6, 'wma', '');

# MSSQL IDENTITY phpbb_extensions OFF #
