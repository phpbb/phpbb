<?php

namespace OAuth\OAuth2\Service;

//-----------------------------------------------------------------------------
use OAuth\OAuth2\Token\StdOAuth2Token;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Consumer\CredentialsInterface;
use OAuth\Common\Http\Client\ClientInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Http\Uri\UriInterface;

//-----------------------------------------------------------------------------
class BattleNet extends AbstractService { 
    
    /** -----------------------------------------------------------------------
     * Defined scopes.
     *
     * @link https://dev.battle.net/docs
     */
    const SCOPE_WOW_PROFILE = "wow.profile";
    const SCOPE_SC2_PROFILE = "sc2.profile";
    
    /** -----------------------------------------------------------------------
     * Defined API URIs.
     *
     * @link https://dev.battle.net/docs
     */
    const API_URI_US  = 'https://us.api.battle.net/';
    const API_URI_EU  = 'https://eu.api.battle.net/';
    const API_URI_KR  = 'https://kr.api.battle.net/';
    const API_URI_TW  = 'https://tw.api.battle.net/';
    const API_URI_CN  = 'https://api.battlenet.com.cn/';
    const API_URI_SEA = 'https://sea.api.battle.net/';
    
    public function __construct( CredentialsInterface $credentials,
                                 ClientInterface $httpClient,
                                 TokenStorageInterface $storage,
                                 $scopes = array(),
                                 UriInterface $baseApiUri = null ) {
                                 
        parent::__construct( $credentials, $httpClient, $storage, 
                             $scopes, $baseApiUri );
        
        if( $baseApiUri === null ) {
            $this->baseApiUri = new Uri( self::API_URI_US );
        }
    }
    
    /** -----------------------------------------------------------------------
     * Translates the current base API URI into an OAuth base URI. 
     *
     * @returns string Base URI of oauth services.
     */
    private function GetOAuthBaseUri() {
    
        // i love china
        switch( $this->baseApiUri ) {
            case self::API_URI_US:  return 'https://us.battle.net/oauth/';
            case self::API_URI_EU:  return 'https://eu.battle.net/oauth/';
            case self::API_URI_KR:  return 'https://kr.battle.net/oauth/';
            case self::API_URI_TW:  return 'https://tw.battle.net/oauth/';
            case self::API_URI_CN:  return 'https://www.battlenet.com.cn/oauth/';
            case self::API_URI_SEA: return 'https://sea.battle.net/oauth/'; 
        }
        
    }
    
    /** -----------------------------------------------------------------------
     * {@inheritdoc}
     */
    public function getAuthorizationEndpoint() {
        return new Uri( $this->GetOAuthBaseUri() . 'authorize' );
    }
    
    /** -----------------------------------------------------------------------
     * {@inheritdoc}
     */
    public function getAccessTokenEndpoint() {
        return new Uri( $this->GetOAuthBaseUri() . 'token' );
    }
    
    /** -----------------------------------------------------------------------
     * {@inheritdoc}
     */
    protected function getAuthorizationMethod()
    {
        return static::AUTHORIZATION_METHOD_QUERY_STRING;
    }
    
    /** -----------------------------------------------------------------------
     * {@inheritdoc}
     */
    protected function parseAccessTokenResponse( $responseBody )
    {
        $data = json_decode($responseBody, true);
        if( $data === null || !is_array($data) ) {
            throw new TokenResponseException( 'Unable to parse response.' );
        } elseif( isset($data['error']) ) {
            $err = $data['error'];
            throw new TokenResponseException( 
                                "Error in retrieving token: \"$err\"" );
        }
        
        $token = new StdOAuth2Token( $data['access_token'], null, 
                                     $data['expires_in'] );
        
        unset( $data['access_token'] );
        unset( $data['expires_in'] );
        
        $token->setExtraParams( $data );
        
        return $token;
    }
}
