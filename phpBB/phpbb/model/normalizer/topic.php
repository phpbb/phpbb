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
 * Topic enitity normalizer
 * @package phpBB3
 */
class phpbb_model_normalizer_topic implements NormalizerInterface
{
	public function normalize($topic, $format = null)
	{
		$whitelist = array(
			'topic_id',
			'forum_id',
			'icon_id',
			'topic_title',
			'topic_poster',
			'topic_time',
			'topic_views',
			'topic_posts_approved',
			'topic_type',
			'topic_first_post_id',
			'topic_first_poster_name',
			'topic_first_poster_colour',
			'topic_last_post_id',
			'topic_last_poster_name',
			'topic_last_poster_colour',
			'topic_last_post_subject',
			'topic_last_post_time',
		);

		$normalized_topic = array();
		foreach($whitelist as $field)
		{

			$normalized_topic[$field] = $topic->get($field);

		}

		return $normalized_topic;
	}

	public function supportsNormalization($data, $format = null)
	{
		return $data instanceof phpbb_model_entity_topic;
	}
}
