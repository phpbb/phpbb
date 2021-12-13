#!/bin/bash
#
# This file is part of the phpBB Forum Software package.
#
# @copyright (c) phpBB Limited <https://www.phpbb.com>
# @license GNU General Public License, version 2 (GPL-2.0)
#
# For full copyright and license information, please see
# the docs/CREDITS.txt file.
#
set -e
set -x

sudo apt-get update
sudo apt-get install -q -y sphinxsearch

DIR=$(dirname "$0")

SPHINX_DAEMON_HOST="localhost"
SPHINX_DAEMON_PORT="9312"
SPHINX_CONF="$DIR/sphinx.conf"
SPHINX_DATA_DIR="/var/run/sphinxsearch"
SPHINX_LOG="$SPHINX_DATA_DIR/log/searchd.log"
SPHINX_QUERY_LOG="$SPHINX_DATA_DIR/log/sphinx-query.log"
ID="saw9zf2fdhp1goue" # Randomly generated via phpBB unique_id()

PHPBB_TEST_DBHOST="0.0.0.0"
PHPBB_TEST_DBNAME="phpbb_tests"
PHPBB_TEST_DBUSER="root"
PHPBB_TEST_DBPASSWD=""

sudo service sphinxsearch stop
sudo mkdir -p "$SPHINX_DATA_DIR/log"
sudo chown "sphinxsearch" "$SPHINX_DATA_DIR/log"

# Generate configuration file for Sphinx
echo "
source source_phpbb_${ID}_main 
{
	type = mysql # mysql or pgsql
	sql_host =  $PHPBB_TEST_DBHOST
	sql_user = $PHPBB_TEST_DBUSER
	sql_pass = $PHPBB_TEST_DBPASSWD
	sql_db = $PHPBB_TEST_DBNAME
	sql_port =
	sql_query_pre = SET NAMES 'utf8'
	sql_query_pre = UPDATE phpbb_sphinx SET max_doc_id = (SELECT MAX(post_id) FROM phpbb_posts) WHERE counter_id = 1
	sql_query_range = SELECT MIN(post_id), MAX(post_id) FROM phpbb_posts
	sql_range_step = 5000
	sql_query = SELECT \
						p.post_id AS id, \
						p.forum_id, \
						p.topic_id, \
						p.poster_id, \
						p.post_visibility, \
						CASE WHEN p.post_id = t.topic_first_post_id THEN 1 ELSE 0 END as topic_first_post, \
						p.post_time, \
						p.post_subject, \
						p.post_subject as title, \
						p.post_text as data, \
						t.topic_last_post_time, \
						0 as deleted \
					FROM phpbb_posts p, phpbb_topics t \
					WHERE \
						p.topic_id = t.topic_id \
						AND p.post_id >= \$start AND p.post_id <= \$end
	sql_query_post =
	sql_query_post_index = UPDATE phpbb_sphinx SET max_doc_id = \$maxid WHERE counter_id = 1
	sql_attr_uint = forum_id
	sql_attr_uint = topic_id
	sql_attr_uint = poster_id
	sql_attr_uint = post_visibility
	sql_attr_bool = topic_first_post
	sql_attr_bool = deleted
	sql_attr_timestamp = post_time
	sql_attr_timestamp = topic_last_post_time
	sql_attr_string = post_subject
}
source source_phpbb_${ID}_delta : source_phpbb_${ID}_main
{
	sql_query_pre = SET NAMES 'utf8'
	sql_query_range =
	sql_range_step =
	sql_query = SELECT \
						p.post_id AS id, \
						p.forum_id, \
						p.topic_id, \
						p.poster_id, \
						p.post_visibility, \
						CASE WHEN p.post_id = t.topic_first_post_id THEN 1 ELSE 0 END as topic_first_post, \
						p.post_time, \
						p.post_subject, \
						p.post_subject as title, \
						p.post_text as data, \
						t.topic_last_post_time, \
						0 as deleted \
					FROM phpbb_posts p, phpbb_topics t \
					WHERE \
						p.topic_id = t.topic_id \
						AND p.post_id >=  ( SELECT max_doc_id FROM phpbb_sphinx WHERE counter_id=1 )
	sql_query_post_index =
}
index index_phpbb_${ID}_main
{
	path = $SPHINX_DATA_DIR/index_phpbb_${ID}_main
	source = source_phpbb_${ID}_main
	docinfo = extern
	morphology = none
	stopwords =
	wordforms =
	exceptions =
	min_word_len = 2
	charset_table = U+FF10..U+FF19->0..9, 0..9, U+FF41..U+FF5A->a..z, U+FF21..U+FF3A->a..z, A..Z->a..z, a..z, U+0149, U+017F, U+0138, U+00DF, U+00FF, U+00C0..U+00D6->U+00E0..U+00F6, U+00E0..U+00F6, U+00D8..U+00DE->U+00F8..U+00FE, U+00F8..U+00FE, U+0100->U+0101, U+0101, U+0102->U+0103, U+0103, U+0104->U+0105, U+0105, U+0106->U+0107, U+0107, U+0108->U+0109, U+0109, U+010A->U+010B, U+010B, U+010C->U+010D, U+010D, U+010E->U+010F, U+010F, U+0110->U+0111, U+0111, U+0112->U+0113, U+0113, U+0114->U+0115, U+0115, U+0116->U+0117, U+0117, U+0118->U+0119, U+0119, U+011A->U+011B, U+011B, U+011C->U+011D, U+011D, U+011E->U+011F, U+011F, U+0130->U+0131, U+0131, U+0132->U+0133, U+0133, U+0134->U+0135, U+0135, U+0136->U+0137, U+0137, U+0139->U+013A, U+013A, U+013B->U+013C, U+013C, U+013D->U+013E, U+013E, U+013F->U+0140, U+0140, U+0141->U+0142, U+0142, U+0143->U+0144, U+0144, U+0145->U+0146, U+0146, U+0147->U+0148, U+0148, U+014A->U+014B, U+014B, U+014C->U+014D, U+014D, U+014E->U+014F, U+014F, U+0150->U+0151, U+0151, U+0152->U+0153, U+0153, U+0154->U+0155, U+0155, U+0156->U+0157, U+0157, U+0158->U+0159, U+0159, U+015A->U+015B, U+015B, U+015C->U+015D, U+015D, U+015E->U+015F, U+015F, U+0160->U+0161, U+0161, U+0162->U+0163, U+0163, U+0164->U+0165, U+0165, U+0166->U+0167, U+0167, U+0168->U+0169, U+0169, U+016A->U+016B, U+016B, U+016C->U+016D, U+016D, U+016E->U+016F, U+016F, U+0170->U+0171, U+0171, U+0172->U+0173, U+0173, U+0174->U+0175, U+0175, U+0176->U+0177, U+0177, U+0178->U+00FF, U+00FF, U+0179->U+017A, U+017A, U+017B->U+017C, U+017C, U+017D->U+017E, U+017E, U+0410..U+042F->U+0430..U+044F, U+0430..U+044F, U+4E00..U+9FFF
	ignore_chars = U+0027, U+002C
	min_prefix_len = 3
	min_infix_len = 0
	html_strip = 1
	index_exact_words = 0
	blend_chars = U+23, U+24, U+25, U+26, U+40
}
index index_phpbb_${ID}_delta : index_phpbb_${ID}_main
{
	path = $SPHINX_DATA_DIR/index_phpbb_${ID}_delta
	source = source_phpbb_${ID}_delta
}
indexer
{
	mem_limit = 512M
}
searchd
{
	listen = $SPHINX_DAEMON_PORT
	log = $SPHINX_LOG
	query_log = $SPHINX_QUERY_LOG
	read_timeout = 5
	max_children = 30
	pid_file = $SPHINX_DATA_DIR/searchd.pid
	binlog_path = $SPHINX_DATA_DIR/
}
" > $SPHINX_CONF

sudo mv "$SPHINX_CONF" "/etc/sphinxsearch/sphinx.conf"
sudo sed -i "s/START=no/START=yes/g" "/etc/default/sphinxsearch"
sudo chmod 777 "/var/run/sphinxsearch"
