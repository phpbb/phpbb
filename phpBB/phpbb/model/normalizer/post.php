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
class phpbb_model_normalizer_post implements NormalizerInterface
{
	public function normalize($topic, $format = null)
	{
		$whitelist = array(
			'post_id',
			'topic_id',
			'forum_id',
			'poster_id',
			'icon_id',
			'post_time',
			'post_username',
			'post_subject',
			'post_text',
		);

		$normalized_post = array();
		foreach($whitelist as $field)
		{

			$normalized_post[$field] = $topic->get($field);

		}

		return $normalized_post;
	}

	public function supportsNormalization($data, $format = null)
	{
		return $data instanceof phpbb_model_entity_post;
	}
}
