#
# Basic DB data for phpBB2 devel
#
# $id: mysql_basic.sql,v 1.10 2001/04/19 17:26:24 psotfx Exp $

# -- Config
INSERT INTO phpbb_config VALUES (1,'Test forum',1,1,1,1,1,25,10,50,0,0,'With regards, the Admin','admin@yoursite.com','d M Y h:i:s a','english',0,'Default');

# -- Categories
INSERT INTO phpbb_categories VALUES (1,'Test category 1','1');

# -- Forums
INSERT INTO phpbb_forums VALUES (1,'Test Forum 1','This is just a test forum, nothing special here.',1,1,1,1,0,0,0);

# -- Forum Mods
INSERT INTO phpbb_forum_mods VALUES (1,1,0);

# -- Users
INSERT INTO phpbb_users VALUES (1,'admin',NOW(),'21232f297a57a5a743894a0e4a801fc3','admin@yourdomain.com','','','','','','','This is just a stupid sig',1,1,'','','',0,1,0,0,0,0,'',4,'','','',0,'-8',1, '');
INSERT INTO phpbb_users VALUES (-1,'Anonymous',NOW(),'','','','','','','','','',0,0,'','','',0,0,0,0,0,0,'',0,'','','',0,'-8',1,'Default');

# -- Themes
INSERT INTO phpbb_themes (themes_id, themes_name, head_stylesheet, body_background, body_bgcolor, body_text, body_link, body_vlink, body_alink, body_hlink, tr_color1, tr_color2, tr_color3, th_color1, th_color2, th_color3, td_color1, td_color2, td_color3, fontface1, fontface2, fontface3, fontsize1, fontsize2, fontsize3, fontcolor1, fontcolor2, fontcolor3, img1, img2, img3, img4) VALUES ( '1', 'PSO-Wheat', '', '', 'FFFFFF', '000000', '002266', '004411', '', '', '', '', '', '001100', 'E5CCA5', 'D4A294', 'EBE4D9', 'DAD1C4', '', 'verdana,serif', 'arial,helvetica', 'courier', '1', '2', '3', '000000', '', '', '', '', '', '');
INSERT INTO phpbb_themes_name (themes_id, tr_color1_name, tr_color2_name, tr_color3_name, th_color1_name, th_color2_name, th_color3_name, td_color1_name, td_color2_name, td_color3_name, fontface1_name, fontface2_name, fontface3_name, fontsize1_name, fontsize2_name, fontsize3_name, fontcolor1_name, fontcolor2_name, fontcolor3_name, img1_name, img2_name, img3_name, img4_name) VALUES ( '1', '', '', '', 'Table Background', 'Title Header', 'Category Header', 'Table background', 'Row Color 1', 'Row Color 2', 'Serif', 'Sans-serif', 'Courier', 'Smallest', 'Typical', 'Largest', 'All text', '', '', '', '', '', '');
