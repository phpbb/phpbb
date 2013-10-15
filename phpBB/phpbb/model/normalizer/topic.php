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
			array('int', 'topic_id'),
			array('int', 'forum_id'),
			array('int', 'icon_id'),
			array('string', 'topic_title'),
			array('int', 'topic_poster'),
			array('int', 'topic_time'),
			array('int', 'topic_views'),
			array('int', 'topic_posts_approved'),
			array('int', 'topic_type'),
			array('int', 'topic_first_post_id'),
			array('string', 'topic_first_poster_name'),
			array('string', 'topic_first_poster_colour'),
			array('int', 'topic_last_post_id'),
			array('string', 'topic_last_poster_name'),
			array('string', 'topic_last_poster_colour'),
			array('string', 'topic_last_post_subject'),
			array('int', 'topic_last_post_time'),
		);

		$normalized_topic = array();
		foreach($whitelist as $field)
		{
			$value = $topic->get($field[1]);
			settype($value, $field[0]);
			$normalized_topic[$field[1]] = $value;
		}

		return $normalized_topic;
	}

	public function supportsNormalization($data, $format = null)
	{
		return $data instanceof phpbb_model_entity_topic;
	}
}
