/*
  phpBB2 Oracle 8i Triggers File - (c) 2001 The phpBB Group

  $Id$
 */

/* --------------------------------------------------------
  Trigger structure for table phpbb_groups
-------------------------------------------------------- */
CREATE OR REPLACE TRIGGER "PHPBB"."SET_PHPBB_GROUPS_ID_SEQ"
BEFORE INSERT OR UPDATE OF "GROUP_ID" ON "PHPBB"."PHPBB_GROUPS"
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW
BEGIN
SELECT PHPBB_GROUPS_ID_SEQ.NEXTVAL
INTO :NEW.group_id
FROM DUAL;
END;
/

/* --------------------------------------------------------
  Trigger structure for table phpbb_banlist
-------------------------------------------------------- */
CREATE OR REPLACE TRIGGER "PHPBB"."SET_PHPBB_BANLIST_ID_SEQ"
BEFORE INSERT OR UPDATE OF "BAN_ID" ON "PHPBB"."PHPBB_BANLIST"
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW
BEGIN
SELECT PHPBB_BANLIST_ID_SEQ.NEXTVAL
INTO :NEW.ban_id
FROM DUAL;
END;
/

/* --------------------------------------------------------
  Trigger structure for table phpbb_categories
-------------------------------------------------------- */
CREATE OR REPLACE TRIGGER "PHPBB"."SET_CATEGORIES_ID_SEQ"
BEFORE INSERT OR UPDATE OF "CAT_ID" ON "PHPBB"."PHPBB_CATEGORIES"
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW
BEGIN
SELECT PHPBB_CATEGORIES_ID_SEQ.NEXTVAL
INTO :NEW.cat_id
FROM DUAL;
END;
/

/* --------------------------------------------------------
  Trigger structure for table phpbb_disallow
-------------------------------------------------------- */
CREATE OR REPLACE TRIGGER "PHPBB"."SET_DISALLOW_ID_SEQ"
BEFORE INSERT OR UPDATE OF "DISALLOW_ID" ON "PHPBB"."PHPBB_DISALLOW"
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW
BEGIN
SELECT PHPBB_DISALLOW_ID_SEQ.NEXTVAL
INTO :NEW.disallow_id
FROM DUAL;
END;
/

/* --------------------------------------------------------
  Trigger structure for table phpbb_forums
-------------------------------------------------------- */
CREATE OR REPLACE TRIGGER "PHPBB"."SET_FORUMS_ID_SEQ"
BEFORE INSERT OR UPDATE OF "FORUM_ID" ON "PHPBB"."PHPBB_FORUMS"
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW
BEGIN
SELECT PHPBB_FORUMS_ID_SEQ.NEXTVAL
INTO :NEW.forum_id
FROM DUAL;
END;
/

/* --------------------------------------------------------
  Trigger structure for table phpbb_forum_prune
-------------------------------------------------------- */
CREATE OR REPLACE TRIGGER "PHPBB"."SET_FORUM_PRUNE_ID_SEQ"
BEFORE INSERT OR UPDATE OF "PRUNE_ID" ON "PHPBB"."PHPBB_FORUM_PRUNE"
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW
BEGIN
SELECT PHPBB_FORUM_PRUNE_ID_SEQ.NEXTVAL
INTO :NEW.prune_id
FROM DUAL;
END;
/

/* --------------------------------------------------------
  Trigger structure for table phpbb_posts
-------------------------------------------------------- */
CREATE OR REPLACE TRIGGER "PHPBB"."SET_POSTS_ID_SEQ"
BEFORE INSERT OR UPDATE OF "POST_ID" ON "PHPBB"."PHPBB_POSTS"
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW
BEGIN
SELECT PHPBB_POSTS_ID_SEQ.NEXTVAL
INTO :NEW.post_id
FROM DUAL;
END;
/

/* --------------------------------------------------------
  Trigger structure for table phpbb_privmsgs
-------------------------------------------------------- */
CREATE OR REPLACE TRIGGER "PHPBB"."SET_PRIVMSGS_ID_SEQ"
BEFORE INSERT OR UPDATE OF "PRIVMSGS_ID" ON "PHPBB"."PHPBB_PRIVMSGS"
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW
BEGIN
SELECT PHPBB_PRIVMSGS_ID_SEQ.NEXTVAL
INTO :NEW.privmsgs_id
FROM DUAL;
END;
/

/* --------------------------------------------------------
  Trigger structure for table phpbb_ranks
-------------------------------------------------------- */
CREATE OR REPLACE TRIGGER "PHPBB"."SET_RANK_ID_SEQ"
BEFORE INSERT OR UPDATE OF "RANK_ID" ON "PHPBB"."PHPBB_RANKS"
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW
BEGIN
SELECT PHPBB_RANKS_ID_SEQ.NEXTVAL
INTO :NEW.rank_id
FROM DUAL;
END;
/

/* --------------------------------------------------------
  Trigger structure for table phpbb_smilies
-------------------------------------------------------- */
CREATE OR REPLACE TRIGGER "PHPBB"."SET_SMILIES_ID_SEQ"
BEFORE INSERT OR UPDATE OF "SMILIES_ID" ON "PHPBB"."PHPBB_SMILIES"
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW
BEGIN
SELECT PHPBB_SMILIES_ID_SEQ.NEXTVAL
INTO :NEW.smilies_id
FROM DUAL;
END;
/

/* --------------------------------------------------------
  Trigger structure for table phpbb_themes
-------------------------------------------------------- */
CREATE OR REPLACE TRIGGER "PHPBB"."SET_THEMES_ID_SEQ"
BEFORE INSERT OR UPDATE OF "THEMES_ID" ON "PHPBB"."PHPBB_THEMES"
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW
BEGIN
SELECT PHPBB_THEMES_ID_SEQ.NEXTVAL
INTO :NEW.themes_id
FROM DUAL;
END;
/

/* --------------------------------------------------------
  Trigger structure for table phpbb_topics
-------------------------------------------------------- */
CREATE OR REPLACE TRIGGER "PHPBB"."SET_TOPICS_ID_SEQ"
BEFORE INSERT OR UPDATE OF "TOPIC_ID" ON "PHPBB"."PHPBB_TOPICS"
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW
BEGIN
SELECT PHPBB_TOPICS_ID_SEQ.NEXTVAL
INTO :NEW.topic_id
FROM DUAL;
END;
/

/* --------------------------------------------------------
  Trigger structure for table phpbb_users
-------------------------------------------------------- */
CREATE OR REPLACE TRIGGER "PHPBB"."SET_USERS_ID_SEQ"
BEFORE INSERT OR UPDATE OF "USER_ID" ON "PHPBB"."PHPBB_USERS"
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW
BEGIN
SELECT PHPBB_USERS_ID_SEQ.NEXTVAL
INTO :NEW.user_id
FROM DUAL;
END;
/

/* --------------------------------------------------------
  Trigger structure for table phpbb_vote_desc
-------------------------------------------------------- */
CREATE OR REPLACE TRIGGER "PHPBB"."SET_VOTE_DESC_ID_SEQ"
BEFORE INSERT OR UPDATE OF "VOTE_ID" ON "PHPBB"."PHPBB_VOTE_DESC"
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW
BEGIN
SELECT PHPBB_VOTE_DESC_ID_SEQ.NEXTVAL
INTO :NEW.vote_id
FROM DUAL;
END;
/

/* --------------------------------------------------------
  Trigger structure for table phpbb_words
-------------------------------------------------------- */
CREATE OR REPLACE TRIGGER "PHPBB"."SET_WORDS_ID_SEQ"
BEFORE INSERT OR UPDATE OF "WORD_ID" ON "PHPBB"."PHPBB_WORDS"
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW
BEGIN
SELECT PHPBB_WORDS_ID_SEQ.NEXTVAL
INTO :NEW.word_id
FROM DUAL;
END;
/