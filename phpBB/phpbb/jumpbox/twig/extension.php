<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\jumpbox\twig;

use phpbb\auth\auth;
use phpbb\db\driver\driver_interface;
use phpbb\event\dispatcher_interface;
use phpbb\path_helper;

class extension extends \Twig_Extension
{
	/**
	 * Constructor
	 *
	 * @param auth $auth Auth object
	 * @param driver_interface Database object
	 * @param dispatcher_interface	$phpbb_dispatcher	Event dispatcher object
	 * @param path_helper	$path_helper	Path helper
	 */
	public function __construct(auth $auth, driver_interface $db, dispatcher_interface $dispatcher, path_helper $path_helper)
	{
		$this->auth = $auth;
		$this->db = $db;
		$this->dispatcher = $dispatcher;
		$this->path_helper = $path_helper;
	}

	/**
	* Get the name of this extension
	*
	* @return string
	*/
	public function getName()
	{
		return 'jumpbox';
	}

	/**
	* Returns a list of global functions to add to the existing list.
	*
	* @return array An array of global functions
	*/
	public function getFunctions()
	{
		return array(
			new \Twig_SimpleFunction('jumpbox', [$this, 'jumpbox'], array('needs_environment' => true)),
		);
	}

	/**
	* Generate Jumpbox
	*/
	function jumpbox(\Twig_Environment $env)
	{
		$rowset = $this->get_forums();

		if (empty($rowset))
		{
			return;
		}

		$right = $padding = 0;
		$padding_store = array('0' => 0);

		/**
		* Modify the jumpbox forum list data
		*
		* @event core.make_jumpbox_modify_forum_list
		* @var	array	rowset	Array with the forums list data
		* @since 3.1.10-RC1
		*/
		$vars = array('rowset');
		extract($this->dispatcher->trigger_event('core.make_jumpbox_modify_forum_list', compact($vars)));

		// Sometimes it could happen that forums will be displayed here not be displayed within the index page
		// This is the result of forums not displayed at index, having list permissions and a parent of a forum with no permissions.
		// If this happens, the padding could be "broken"

		$forums = [];

		foreach ($rowset as $row)
		{
			if ($row['left_id'] < $right)
			{
				$padding++;
				$padding_store[$row['parent_id']] = $padding;
			}
			else if ($row['left_id'] > $right + 1)
			{
				// Ok, if the $padding_store for this parent is empty there is something wrong. For now we will skip over it.
				// @todo digging deep to find out "how" this can happen.
				$padding = (isset($padding_store[$row['parent_id']])) ? $padding_store[$row['parent_id']] : $padding;
			}

			$right = $row['right_id'];

			if ($row['forum_type'] == FORUM_CAT && ($row['left_id'] + 1 == $row['right_id']))
			{
				// Non-postable forum with no subforums, don't display
				continue;
			}

			if (!$this->auth->acl_get('f_list', $row['forum_id']))
			{
				// if the user does not have permissions to list this forum skip
				continue;
			}

			$tpl_ary = array(
				'FORUM_ID'		=> $row['forum_id'],
				'FORUM_NAME'	=> $row['forum_name'],
				'S_IS_CAT'		=> ($row['forum_type'] == FORUM_CAT) ? true : false,
				'LINK'			=> $this->path_helper->append_url_params("viewforum.php", array('f' => $row['forum_id'])),
			);

			/**
			 * Modify the jumpbox before it is assigned to the template
			 *
			 * @event core.make_jumpbox_modify_tpl_ary
			 * @var	array	row				The data of the forum
			 * @var	array	tpl_ary			Template data of the forum
			 * @since 3.1.10-RC1
			 */
			$vars = array(
				'row',
				'tpl_ary',
			);
			extract($this->dispatcher->trigger_event('core.make_jumpbox_modify_tpl_ary', compact($vars)));

			for ($i = 0; $i < $padding; $i++)
			{
				$tpl_ary['level'][] = [];
			}

			$forums[] = $tpl_ary;
		}

		// Display jumpbox only if there are forums
		if (!empty($forums))
		{
			return $env->render('jumpbox.html', [
				'jumpbox_forums'	=> $forums,
			]);
		}
	}

	protected function get_forums()
	{
		$sql = 'SELECT forum_id, forum_name, parent_id, forum_type, left_id, right_id
			FROM ' . FORUMS_TABLE . '
			ORDER BY left_id ASC';
		$result = $this->db->sql_query($sql, 600);

		$rowset = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$rowset[(int) $row['forum_id']] = $row;
		}
		$this->db->sql_freeresult($result);

		return $rowset;
	}
}
