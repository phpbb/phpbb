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

namespace phpbb\auth\provider\oauth\provider;

use League\OAuth2\Client\Provider\GenericProvider;

/**
 * Bitly's token endpoint supports JSON when requested explicitly.
 */
class bitly extends GenericProvider
{
	/**
	 * Request an access token with a JSON response.
	 *
	 * @param array $params
	 * @return \Psr\Http\Message\RequestInterface
	 */
	protected function getAccessTokenRequest(array $params)
	{
		return parent::getAccessTokenRequest($params)->withHeader('Accept', 'application/json');
	}
}
