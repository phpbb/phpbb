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
 * Post enitity normalizer
 * @package phpBB3
 */
class phpbb_model_normalizer_post implements NormalizerInterface
{
	public function normalize($post, $format = null)
	{
		$whitelist = array(
			array('int', 'post_id'),
			array('int', 'topic_id'),
			array('int', 'forum_id'),
			array('int', 'poster_id'),
			array('int', 'icon_id'),
			array('int', 'post_time'),
			array('string', 'post_username'),
			array('string', 'post_subject'),
			array('string', 'post_text'),
		);

		$normalized_post = array();
		foreach($whitelist as $field)
		{
			$value = $post->get($field[1]);
			settype($value, $field[0]);
			$normalized_post[$field[1]] = $value;

		}

		return $normalized_post;
	}

	public function supportsNormalization($data, $format = null)
	{
		return $data instanceof phpbb_model_entity_post;
	}
}
