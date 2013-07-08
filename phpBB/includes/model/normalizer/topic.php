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
        $normalized_topics = array(
            'topic_id'					=> $topic->get('topic_id'),
            'forum_id'					=> $topic->get('forum_id'),
            'icon_id'					=> $topic->get('icon_id'),
            'topic_title'					=> $topic->get('topic_title'),
            'topic_poster'					=> $topic->get('topic_poster'),
            'topic_time'					=> $topic->get('topic_time'),
            'topic_views'					=> $topic->get('topic_views'),
            'topic_replies'					=> $topic->get('topic_replies'),
            'topic_replies_real'					=> $topic->get('topic_replies_real'),
            'topic_type'					=> $topic->get('topic_type'),
            'topic_first_post_id'					=> $topic->get('topic_first_post_id'),
            'topic_first_poster_name'					=> $topic->get('topic_first_poster_name'),
            'topic_first_poster_colour'					=> $topic->get('topic_first_poster_colour'),
            'topic_last_post_id'					=> $topic->get('topic_last_post_id'),
            'topic_last_poster_name'					=> $topic->get('topic_last_poster_name'),
            'topic_last_poster_colour'					=> $topic->get('topic_last_poster_colour'),
            'topic_last_post_subject'					=> $topic->get('topic_last_post_subject'),
            'topic_last_post_time'					=> $topic->get('topic_last_post_time'),
        );

        return $normalized_topics;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof phpbb_model_entity_topic;
    }
}
