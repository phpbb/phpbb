<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

namespace phpbb\viewtopic\controller;

use phpbb\exception\http_exception;
use phpbb\viewtopic\exception\no_posts_found_exception;
use phpbb\viewtopic\exception\topic_not_found_exception;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller that renders posts in a topic.
 */
class viewtopic
{
	/**
	 * @var \phpbb\viewtopic\parameter_handler
	 */
	protected $parameter_handler;

	/**
	 * @var \phpbb\viewtopic\poll_retriever
	 */
	protected $poll_retriever;

	protected $post_retriever;

	/**
	 * @var \phpbb\viewtopic\topic_retriever
	 */
	protected $topic_retriever;

	/**
	 * @var \phpbb\viewtopic\topic_visibility_manager
	 */
	protected $visibility_manager;

	/**
	 * @var \phpbb\user
	 */
	protected $user;

	/*
	 * @todo
	 */
	public function __construct(
		\phpbb\viewtopic\parameter_handler $parameter_handler,
		\phpbb\viewtopic\poll_retriever $poll_retriever,
		\phpbb\viewtopic\post_retriever $post_retriever,
		\phpbb\viewtopic\topic_retriever $topic_retriever,
		\phpbb\viewtopic\topic_visibility_manager $visibility_manager,
		\phpbb\user $user)
	{
		$this->parameter_handler = $parameter_handler;
		$this->poll_retriever = $poll_retriever;
		$this->post_retriever = $post_retriever;
		$this->topic_retriever = $topic_retriever;
		$this->visibility_manager = $visibility_manager;
		$this->user = $user;
	}

	/*
	 * @todo
	 */
	public function show_topic($topic_id, $slug)
	{
		return $this->render_helper($topic_id, $slug);
	}

	/*
	 * @todo
	 */
	public function show_post($post_id, $slug)
	{
		return $this->render_helper($post_id, $slug, false);
	}

	/*
	 * @todo
	 */
	protected function render_helper($id, $slug, $topic = true)
	{
		$parameters = $this->process_parameters($slug);
		$parameters = $this->parameter_handler->decode($parameters);

		// @todo: viewtopic_url should go into topic data.

		$should_update = false; // @todo
		$should_display_vote_results = false; // @todo
		$voted_id = []; // @todo: request param

		$topic_data = $poll_data = [];

		try
		{
			if ($topic)
			{
				$topic_data = $this->topic_retriever->get_topic_by_id($id);
			}
			else
			{
				$topic_data = $this->topic_retriever->get_topic_by_post($id);
			}

			$this->visibility_manager->check($topic_data, !$topic);
			$poll_data = $this->poll_retriever->get_poll($topic_data, $should_update, $should_display_vote_results);
			$post_data = $this->post_retriever->get_posts($topic_data, $parameters);
		}
		catch (topic_not_found_exception $e)
		{
			throw new http_exception(404, $e->getMessage());
		}
		catch (no_posts_found_exception $e)
		{
			throw new http_exception(200, $e->getMessage());
		}

		$this->topic_helper($topic_data);

		return new JsonResponse(
			['id' => $id, 'params' => $parameters, 'data' => $topic_data, 'poll_data' => $poll_data, 'post_data' => $post_data]
		);
	}

	/*
	 * @todo
	 */
	protected function topic_helper($topic_data)
	{
		// Set which forum the user is reading so it gets displayed.
		$this->user->page['forum'] = $topic_data['forum_id'];
	}

	/*
	 * @todo
	 */
	protected function process_parameters($parameters)
	{
		$param_array = (empty($parameters)) ? [] : explode('/', $parameters);

		return $this->parameter_handler->decode($param_array);
	}
}
