<?php
/**
 *
 * @package entity
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}


/**
 * Forum enitity
 * @package phpBB3
 */
class phpbb_model_entity_forum extends phpbb_model_entity
{
	public function __construct($forum)
    {
        $this->data['subforums'] = null;
        parent::__construct($forum);
    }

}
