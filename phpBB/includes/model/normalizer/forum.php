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
		$normalized_forums = array(
			'forum_id'					=> $forum->get('forum_id'),
			'parent_id'					=> $forum->get('parent_id'),
			'forum_name'				=> $forum->get('forum_name'),
			'forum_desc'				=> $forum->get('forum_desc'),
			'forum_link'				=> $forum->get('forum_link'),
			'forum_image'				=> $forum->get('forum_image'),
			'forum_rules'				=> $forum->get('forum_rules'),
			'forum_rules_link'			=> $forum->get('forum_rules_link'),
			'forum_type'				=> $forum->get('forum_type'),
			'forum_posts'				=> $forum->get('forum_posts'),
			'forum_topics'				=> $forum->get('forum_topics'),
			'forum_topics_real'			=> $forum->get('forum_topics_real'),
			'forum_last_post_id'		=> $forum->get('forum_last_post_id'),
			'forum_last_poster_id'		=> $forum->get('forum_last_poster_id'),
			'forum_last_post_subject'	=> $forum->get('forum_last_post_subject'),
			'forum_last_post_time'		=> $forum->get('forum_last_post_time'),
			'forum_last_poster_name'	=> $forum->get('forum_last_poster_name'),
			'forum_last_poster_colour'	=> $forum->get('forum_last_poster_colour'),
			'subforums'				=> ($forum->get('subforums') != null) ?
				$this->normalize_subforum($forum->get('subforums')) : null,
		);

		return $normalized_forums;
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
