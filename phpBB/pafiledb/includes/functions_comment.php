<?php
/**
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: functions_comment.php,v 1.2 2008/10/26 08:36:06 orynider Exp $
* @copyright (c) 2002-2006 [Mohd Basri, PHP Arena, pafileDB, Jon Ohlsson] MX-Publisher Project Team
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License v2
*
*/

if ( !defined( 'IN_PORTAL' ) )
{
	die( "Hacking attempt" );
}

/**
 * Enter description here...
 *
 */
class pafiledb_comments extends mx_comments
{
	/**
	 * Init Comment vars.
	 *
	 * @param unknown_type $item_data
	 * @param unknown_type $comments_type
	 */
	function init( $item_data, $comments_type = 'internal' )
	{
		global $pafiledb, $pafiledb_config, $db, $images;

		if ( !is_object($pafiledb) || empty($pafiledb_config) )
		{
			mx_message_die(GENERAL_ERROR, 'Bad global arguments');
		}

		if (!is_array($item_data) && !empty($item_data))
		{
			$sql = 'SELECT *
				FROM ' . PA_FILES_TABLE . "
				WHERE file_id = $item_data";

			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Couldn\'t get file info', '', __LINE__, __FILE__, $sql );
			}

			$item_data = $db->sql_fetchrow( $result );
		}

		$this->comments_type = $comments_type == 'internal' ? 'internal' : 'phpbb';
		$this->cat_id = $item_data['file_catid'];
		$this->item_id = $item_data['file_id'];

		$this->topic_id = $item_data['topic_id'];

		$this->item_table = PA_FILES_TABLE;
		$this->comments_table = PA_COMMENTS_TABLE;
		$this->table_field_id = 'file_id';

		//
		// Auth
		//
		$this->forum_id = $pafiledb->modules[$pafiledb->module_name]->comments[$this->cat_id]['comments_forum_id'];

		$this->auth['auth_view'] = $pafiledb->modules[$pafiledb->module_name]->auth_user[$this->cat_id]['auth_view_comment'];
		$this->auth['auth_post'] = $pafiledb->modules[$pafiledb->module_name]->auth_user[$this->cat_id]['auth_post_comment'];
		$this->auth['auth_edit'] = $pafiledb->modules[$pafiledb->module_name]->auth_user[$this->cat_id]['auth_edit_comment'];
		$this->auth['auth_delete'] = $pafiledb->modules[$pafiledb->module_name]->auth_user[$this->cat_id]['auth_delete_comment'];
		$this->auth['auth_mod'] = $pafiledb->modules[$pafiledb->module_name]->auth_user[$this->cat_id]['auth_mod'];

		//
		// Pagination
		//
		$this->pagination_action = 'action=file';
		$this->pagination_target = 'file_id=';

		$this->pagination_num = empty($show_num_comments) ? $this->pagination_num : $show_num_comments;
		$this->u_pagination = $pafiledb->this_mxurl( $this->pagination_action . "&" . $this->pagination_target . $this->item_id  );

		//
		// Configs
		//
		$this->allow_wysiwyg = $pafiledb_config['allow_wysiwyg'];

		$this->allow_comment_wysiwyg = $pafiledb_config['allow_comment_wysiwyg'];
		$this->allow_comment_bbcode = $pafiledb_config['allow_comment_bbcode'];
		$this->allow_comment_html = $pafiledb_config['allow_comment_html'];
	 	$this->allow_comment_smilies = $pafiledb_config['allow_comment_smilies'];
	 	$this->allow_comment_links = $pafiledb_config['allow_comment_links'];
	 	$this->allow_comment_images = $pafiledb_config['allow_comment_images'];

	 	$this->no_comment_image_message = $pafiledb_config['no_comment_image_message'];
	 	$this->no_comment_link_message = $pafiledb_config['no_comment_link_message'];

		$this->max_comment_subject_chars = $pafiledb_config['max_comment_subject_chars'];
		$this->max_comment_chars = $pafiledb_config['max_comment_chars'];

		$this->formatting_comment_truncate_links = $pafiledb_config['formatting_comment_truncate_links'];
		$this->formatting_comment_image_resize = $pafiledb_config['formatting_comment_image_resize'];
		$this->formatting_comment_wordwrap = $pafiledb_config['formatting_comment_wordwrap'];

		//
		// Define comments images
		//
		$this->images = array(
			'icon_minipost' => $images['pa_icon_minipost'],
			//'comment_post' => $images['pa_comment_post'],
			'comment_post' => 'pa_comment_post', // Button
			//'icon_edit' => $images['pa_comment_edit'],
			'icon_edit' => 'pa_comment_edit', // Button
			//'icon_delpost' => $images['pa_comment_delete'],
			'icon_delpost' => 'pa_comment_delete' // Button
		);

		$this->u_post = $pafiledb->this_mxurl( 'action=post_comment&item_id=' . $this->item_id . '&cat_id=' . $this->cat_id);
		$this->u_edit = $pafiledb->this_mxurl( 'action=post_comment&item_id=' . $this->item_id . '&cat_id=' . $this->cat_id );
		$this->u_delete = $pafiledb->this_mxurl( "action=post_comment&delete=do&item_id=".$this->item_id . '&cat_id=' . $this->cat_id );

	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $ranks
	 */
	function obtain_ranks( &$ranks )
	{
		global $db, $pafiledb_cache;

		if (PORTAL_BACKEND != 'internal')
		{
			if ( $pafiledb_cache->exists( 'ranks' ) )
			{
				$ranks = $pafiledb_cache->get( 'ranks' );
			}
			else
			{
				$sql = "SELECT *
					FROM " . RANKS_TABLE . "
					ORDER BY rank_special, rank_min";

				if ( !( $result = $db->sql_query( $sql ) ) )
				{
					mx_message_die( GENERAL_ERROR, "Could not obtain ranks information.", '', __LINE__, __FILE__, $sql );
				}

				$ranks = array();
				while ( $row = $db->sql_fetchrow( $result ) )
				{
					$ranks[] = $row;
				}

				$db->sql_freeresult( $result );
				$pafiledb_cache->put( 'ranks', $ranks );
			}
		}
	}
}
?>