# $id$
# Some basic inserts to get you started.

INSERT INTO phpbb_config VALUES (1,'Test forum',1,1,1,1,1,25,10,50,0,0,'With regards, the Admin','admin@yoursite.com','d M Y h:i:s a','english',0);

INSERT INTO phpbb_categories VALUES (1,'Test category 1','1');

INSERT INTO phpbb_forums VALUES (1,'Test Forum 1','This is just a test forum, nothing special here.',1,1,1,1,0,0,0);

INSERT INTO phpbb_forum_mods VALUES (1,1,0);

INSERT INTO phpbb_users VALUES (1,'admin','Jan 09, 2001','21232f297a57a5a743894a0e4a801fc3','admin@yourdomain.com','','','','','','This is just a stupid sig',1,1,'','','',0,1,0,0,0,0,'',4,'','','',0);
INSERT INTO phpbb_users VALUES (-1,'Anonymous','May 12, 1978','','','','','','','','',0,0,'','','',0,0,0,0,0,0,'',0,'','','',0);
