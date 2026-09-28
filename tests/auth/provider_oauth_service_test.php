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

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use phpbb\auth\provider\oauth\service\bitly;
use phpbb\auth\provider\oauth\service\facebook;
use phpbb\auth\provider\oauth\service\google;

class phpbb_auth_provider_oauth_service_test extends phpbb_test_case
{
	/** @var \phpbb\config\config */
	protected $config;

	protected function setUp(): void
	{
		parent::setUp();

		$this->config = new \phpbb\config\config([
			'auth_oauth_bitly_key' => 'bitly-client',
			'auth_oauth_bitly_secret' => 'bitly-secret',
			'auth_oauth_facebook_key' => 'facebook-client',
			'auth_oauth_facebook_secret' => 'facebook-secret',
			'auth_oauth_google_key' => 'google-client',
			'auth_oauth_google_secret' => 'google-secret',
		]);
	}

	public function test_google_provider_uses_openid_connect_endpoints()
	{
		$provider = (new google($this->config))->get_provider('https://forum.example/callback');
		$query = $this->get_authorization_query($provider);

		$this->assertSame('https://accounts.google.com/o/oauth2/v2/auth', $this->get_authorization_url($provider));
		$this->assertSame('google-client', $query['client_id']);
		$this->assertSame('code', $query['response_type']);
		$this->assertSame(['openid', 'email', 'profile'], explode(' ', $query['scope']));
		$this->assertNotEmpty($query['state']);
	}

	public function test_facebook_provider_uses_the_declared_graph_version()
	{
		$provider = (new facebook($this->config))->get_provider('https://forum.example/callback');

		$this->assertSame(
			'https://www.facebook.com/' . facebook::GRAPH_VERSION . '/dialog/oauth',
			$this->get_authorization_url($provider)
		);
		$this->assertSame(
			'https://graph.facebook.com/' . facebook::GRAPH_VERSION . '/me?fields=id',
			$provider->getResourceOwnerDetailsUrl(new AccessToken(['access_token' => 'token']))
		);
	}

	public function test_bitly_provider_requests_a_json_token_response()
	{
		$provider = (new bitly($this->config))->get_provider('https://forum.example/callback');
		$request = $this->get_access_token_request($provider, [
			'code' => 'authorization-code',
			'grant_type' => 'authorization_code',
			'redirect_uri' => 'https://forum.example/callback',
		]);

		$this->assertSame('https://bitly.com/oauth/authorize', $this->get_authorization_url($provider));
		$this->assertSame('application/json', $request->getHeaderLine('Accept'));
	}

	protected function get_authorization_url(AbstractProvider $provider): string
	{
		return strtok($provider->getAuthorizationUrl(), '?');
	}

	protected function get_authorization_query(AbstractProvider $provider): array
	{
		parse_str((string) parse_url($provider->getAuthorizationUrl(), PHP_URL_QUERY), $query);

		return $query;
	}

	protected function get_access_token_request(AbstractProvider $provider, array $params)
	{
		$method = new \ReflectionMethod($provider, 'getAccessTokenRequest');

		return $method->invoke($provider, $params);
	}
}
