<?php
/**
 * Deezer service.
 *
 * @author  Pedro Amorim <contact@pamorim.fr>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @link    http://developers.deezer.com/api/
 */

namespace OAuth\OAuth2\Service;

use OAuth\OAuth2\Token\StdOAuth2Token;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Consumer\CredentialsInterface;
use OAuth\Common\Http\Client\ClientInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Http\Uri\UriInterface;

/**
 * Deezer service.
 *
 * @author  Pedro Amorim <contact@pamorim.fr>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @link    http://developers.deezer.com/api/
 */
class Deezer extends AbstractService
{
    /**
     * Defined scopes
     * http://developers.deezer.com/api/permissions
     */
    const SCOPE_BASIC_ACCESS      = 'basic_access';       // Access users basic information
    const SCOPE_EMAIL             = 'email';              // Get the user's email
    const SCOPE_OFFLINE_ACCESS    = 'offline_access';     // Access user data any time
    const SCOPE_MANAGE_LIBRARY    = 'manage_library';     // Manage users' library
    const SCOPE_MANAGE_COMMUNITY  = 'manage_community';   // Manage users' friends
    const SCOPE_DELETE_LIBRARY    = 'delete_library';     // Delete library items
    const SCOPE_LISTENING_HISTORY = 'listening_history';  // Access the user's listening history

    public function __construct(
        CredentialsInterface $credentials,
        ClientInterface $httpClient,
        TokenStorageInterface $storage,
        $scopes = array(),
        UriInterface $baseApiUri = null
    ) {
        parent::__construct(
            $credentials,
            $httpClient,
            $storage,
            $scopes,
            $baseApiUri,
            true
        );

        if (null === $baseApiUri) {
            $this->baseApiUri = new Uri('https://api.deezer.com/');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationEndpoint()
    {
        return new Uri('https://connect.deezer.com/oauth/auth.php');
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessTokenEndpoint()
    {
        return new Uri('https://connect.deezer.com/oauth/access_token.php');
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthorizationMethod()
    {
        return static::AUTHORIZATION_METHOD_QUERY_STRING;
    }

    /**
     * {@inheritdoc}
     */
    protected function parseAccessTokenResponse($responseBody)
    {
        parse_str($responseBody, $data);
        if (null === $data || !is_array($data) || empty($data)) {
            throw new TokenResponseException('Unable to parse response.');
        } elseif (isset($data['error'])) {
            throw new TokenResponseException(
                'Error in retrieving token: "' . $data['error'] . '"'
            );
        } elseif (isset($data['error_reason'])) {
            throw new TokenResponseException(
                'Error in retrieving token: "' . $data['error_reason'] . '"'
            );
        }

        $token = new StdOAuth2Token();
        $token->setAccessToken($data['access_token']);
        $token->setLifeTime($data['expires']);

        // I hope one day Deezer add a refresh token :)
        if (isset($data['refresh_token'])) {
            $token->setRefreshToken($data['refresh_token']);
            unset($data['refresh_token']);
        }

        unset($data['access_token']);
        unset($data['expires']);

        $token->setExtraParams($data);

        return $token;
    }
}
