<?php

use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;

/**
 * This class has been auto-generated
 * by the Symfony Routing Component.
 */
class phpbb_url_matcher extends Symfony\Component\Routing\Matcher\UrlMatcher
{
    public function __construct(RequestContext $context)
    {
        $this->context = $context;
    }

    public function match($pathinfo)
    {
        $allow = array();
        $pathinfo = rawurldecode($pathinfo);
        $context = $this->context;
        $request = $this->request;

        if (0 === strpos($pathinfo, '/feed')) {
            // phpbb_feed_forums
            if ('/feed/forums' === $pathinfo) {
                return array (  '_controller' => 'phpbb.feed.controller:forums',  '_route' => 'phpbb_feed_forums',);
            }

            // phpbb_feed_news
            if ('/feed/news' === $pathinfo) {
                return array (  '_controller' => 'phpbb.feed.controller:news',  '_route' => 'phpbb_feed_news',);
            }

            if (0 === strpos($pathinfo, '/feed/topics')) {
                // phpbb_feed_topics
                if ('/feed/topics' === $pathinfo) {
                    return array (  '_controller' => 'phpbb.feed.controller:topics',  '_route' => 'phpbb_feed_topics',);
                }

                if (0 === strpos($pathinfo, '/feed/topics_')) {
                    // phpbb_feed_topics_active
                    if ('/feed/topics_active' === $pathinfo) {
                        return array (  '_controller' => 'phpbb.feed.controller:topics_active',  '_route' => 'phpbb_feed_topics_active',);
                    }

                    // phpbb_feed_topics_new
                    if ('/feed/topics_new' === $pathinfo) {
                        return array (  '_controller' => 'phpbb.feed.controller:topics_new',  '_route' => 'phpbb_feed_topics_new',);
                    }

                }

            }

            // phpbb_feed_forum
            if (0 === strpos($pathinfo, '/feed/forum') && preg_match('#^/feed/forum/(?P<forum_id>\\d+)$#s', $pathinfo, $matches)) {
                return $this->mergeDefaults(array_replace($matches, array('_route' => 'phpbb_feed_forum')), array (  '_controller' => 'phpbb.feed.controller:forum',));
            }

            // phpbb_feed_topic
            if (0 === strpos($pathinfo, '/feed/topic') && preg_match('#^/feed/topic/(?P<topic_id>\\d+)$#s', $pathinfo, $matches)) {
                return $this->mergeDefaults(array_replace($matches, array('_route' => 'phpbb_feed_topic')), array (  '_controller' => 'phpbb.feed.controller:topic',));
            }

            // phpbb_feed_overall
            if (preg_match('#^/feed/(?P<mode>[^/]++)$#s', $pathinfo, $matches)) {
                return $this->mergeDefaults(array_replace($matches, array('_route' => 'phpbb_feed_overall')), array (  '_controller' => 'phpbb.feed.controller:overall',));
            }

            // phpbb_feed_index
            if ('/feed' === $pathinfo) {
                return array (  '_controller' => 'phpbb.feed.controller:overall',  '_route' => 'phpbb_feed_index',);
            }

        }

        if (0 === strpos($pathinfo, '/help')) {
            // phpbb_help_bbcode_controller
            if ('/help/bbcode' === $pathinfo) {
                return array (  '_controller' => 'phpbb.help.controller.bbcode:handle',  '_route' => 'phpbb_help_bbcode_controller',);
            }

            // phpbb_help_faq_controller
            if ('/help/faq' === $pathinfo) {
                return array (  '_controller' => 'phpbb.help.controller.faq:handle',  '_route' => 'phpbb_help_faq_controller',);
            }

        }

        if (0 === strpos($pathinfo, '/p')) {
            // phpbb_report_pm_controller
            if (0 === strpos($pathinfo, '/pm') && preg_match('#^/pm/(?P<id>\\d+)/report$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                    goto not_phpbb_report_pm_controller;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'phpbb_report_pm_controller')), array (  '_controller' => 'phpbb.report.controller:handle',  'mode' => 'pm',));
            }
            not_phpbb_report_pm_controller:

            // phpbb_report_post_controller
            if (0 === strpos($pathinfo, '/post') && preg_match('#^/post/(?P<id>\\d+)/report$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                    goto not_phpbb_report_post_controller;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'phpbb_report_post_controller')), array (  '_controller' => 'phpbb.report.controller:handle',  'mode' => 'post',));
            }
            not_phpbb_report_post_controller:

        }

        throw 0 < count($allow) ? new MethodNotAllowedException(array_unique($allow)) : new ResourceNotFoundException();
    }
}
