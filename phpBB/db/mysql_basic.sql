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
INSERT INTO phpbb_themes VALUES ( '1', 'Default', '', '', 'FFFFFF', '000000', '', '', '', '', '', '', '', '495FA8', '', '', '000000', 'CCCCCC', 'DDDDDD', 'sans-serif', '', '', '2', '0', '0', 'FFFFFF', '000000', '', '', '', '', '');
INSERT INTO phpbb_themes_name VALUES ( '1', '', '', '', '', 'Table Header', '', '', 'Table background', 'Row Color 1', 'Row Color 2', '', '', '', 'Titles', '', '', 'Titles', 'General Text', '', '', '', '', '');
