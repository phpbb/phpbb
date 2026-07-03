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

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

class phpbb_http_client_decorator implements HttpClientInterface
{
	private HttpClientInterface $client;

	public function __construct(HttpClientInterface $client)
	{
		$this->client = $client;
	}

	public function request(string $method, string $url, array $options = []): ResponseInterface
	{
		if (isset($options['body']) && is_iterable($options['body']) && !is_string($options['body']))
		{
			$body = '';
			foreach ($options['body'] as $chunk)
			{
				$body .= $chunk;
			}
			$options['body'] = $body;
		}

		return $this->client->request($method, $url, $options);
	}

	public function stream(ResponseInterface|iterable $responses, ?float $timeout = null): ResponseStreamInterface
	{
		return $this->client->stream($responses, $timeout);
	}

	public function withOptions(array $options): static
	{
		$clone = clone $this;
		$clone->client = $this->client->withOptions($options);
		return $clone;
	}
}
