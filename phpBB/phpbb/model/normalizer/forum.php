<?php
/**
 *
 * @package normalizer
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General public License v2
 *
 */

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
/**
 * Forum enitity normalizer
 * @package phpBB3
 */
class phpbb_model_normalizer_forum implements NormalizerInterface
{
	public function normalize($forum, $format = null)
	{
		 $whitelist = array(
			array('int', 'forum_id'),
			array('int', 'parent_id'),
			array('string', 'forum_name'),
			array('string', 'forum_desc'),
			array('string', 'forum_link'),
			array('string', 'forum_image'),
			array('string', 'forum_rules'),
			array('string', 'forum_rules_link'),
			array('int', 'forum_type'),
			array('int', 'forum_posts_approved'),
			array('int', 'forum_topics_approved'),
			array('int', 'forum_last_post_id'),
			array('int', 'forum_last_poster_id'),
			array('string', 'forum_last_post_subject'),
			array('int', 'forum_last_post_time'),
			array('string', 'forum_last_poster_name'),
			array('string', 'forum_last_poster_colour'),
			array('unset', 'subforums'),
		);

		$normalized_forum = array();
		foreach($whitelist as $field)
		{
			if($field[1] == "subforums")
			{
				$normalized_forum[$field[1]] = ($forum->get('subforums') != null) ?
					$this->normalize_subforum($forum->get('subforums')) : null;
			}
			else
			{
				$value = $forum->get($field[1]);
				settype($value, $field[0]);
				$normalized_forum[$field[1]] = $value;
			}
		}

		return $normalized_forum;
	}

	public function supportsNormalization($data, $format = null)
	{
		return $data instanceof phpbb_model_entity_forum;
	}

	private function normalize_subforum($subforum)
	{
		$normalized_subforums = array();
		foreach ($subforum as $forum)
		{
			$normalized_subforums[] = $this->normalize($forum);
		}

		return $normalized_subforums;
	}
}
