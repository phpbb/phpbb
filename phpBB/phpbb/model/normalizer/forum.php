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
			'forum_id',
			'parent_id',
			'forum_name',
			'forum_desc',
			'forum_link',
			'forum_image',
			'forum_rules',
			'forum_rules_link',
			'forum_type',
			'forum_posts_approved',
			'forum_topics_approved',
			'forum_last_post_id',
			'forum_last_poster_id',
			'forum_last_post_subject',
			'forum_last_post_time',
			'forum_last_poster_name',
			'forum_last_poster_colour',
			'subforums',
		);

		$normalized_forum = array();
		foreach($whitelist as $field)
		{
			if($field == "subforums")
			{
				$normalized_forum[$field] = ($forum->get('subforums') != null) ?
					$this->normalize_subforum($forum->get('subforums')) : null;
			}
			else
			{
				$normalized_forum[$field] = $forum->get($field);
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
