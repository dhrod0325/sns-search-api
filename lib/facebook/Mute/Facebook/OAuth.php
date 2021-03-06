<?php

namespace Mute\Facebook;

class OAuth
{
    /**
     * @var App
     */
    protected $app;

    function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * Builds an OAuth URL, where users will be prompted to log in and for any desired permissions.
     *
     * When the users log in, you receive a callback with their
     * See http://developers.facebook.com/docs/authentication/.
     *
     * The server-side authentication and dialog methods should only be used
     * if your application can't use the Facebook Javascript SDK,
     * which provides a much better user experience.
     * See http://developers.facebook.com/docs/reference/javascript/.
     *
     * @return string an OAuth URL you can send your users to
     */
    public function getCodeURL($redirect_uri, $permissions = null, $state = null, $auth_type = null, $auth_nonce = null)
    {
        # for permissions, see http://developers.facebook.com/docs/authentication/permissions
        $options = array(
            'client_id' => $this->app->getId(),
            'redirect_uri' => $redirect_uri,
        );
        if ($permissions) {
            $options['scope'] = implode(',', $permissions);
        }
        if ($state) {
            $options['state'] = $state;
        }
        if ($auth_type) {
            $options['auth_type'] = implode(',', (array) $auth_type);
        }
        if ($auth_nonce) {
            $options['auth_nonce'] = $auth_nonce;
        }

        # Creates the URL for oauth authorization for a given callback and optional set of permissions
        return 'https://www.facebook.com/dialog/oauth?' . http_build_query($options, '', '&');
    }

    /**
     * Once you receive an OAuth code, you need to redeem it from Facebook using an appropriate URL.
     * (This is done by your server behind the scenes.)
     * See http://developers.facebook.com/docs/authentication/.
     *
     * @param string $redirect_uri
     * @param string $code
     * @return string an URL your server can query for the user's access token
     */
    public function getAccessTokenURL($redirect_uri, $code)
    {
        # Creates the URL for the token corresponding to a given code generated by Facebook

        $options = array(
            'client_id' => $this->app->getId(),
            'client_secret' => $this->app->getSecret(),
            'code' => $code,
            'redirect_uri' => $redirect_uri,
        );

        return 'https://graph.facebook.com/oauth/access_token?' . http_build_query($options, '', '&');
    }

    /**
     * Fetches an access token, token expiration, and other info from Facebook.
     * Useful when you've received an OAuth code using the server-side authentication process.
     *
     * @param string $code
     * @param string $redirect_uri
     * @return array of the access token info returned by Facebook (token, expiration, etc.)
     */
    public function getAccessToken($code, $redirect_uri = '')
    {
        # convenience method to get a parsed token from Facebook for a given code
        # should this require an OAuth callback URL?
        $params = array(
            'client_id' => $this->app->getId(),
            'client_secret' => $this->app->getSecret(),
            'code' => $code,
            'redirect_uri' => $redirect_uri,
        );
        $response = $this->app->request('oauth/access_token', $params);
        parse_str($response, $components);

        // return array "access_token", ...;
        return $components;
    }

    public function getAppAccessToken()
    {
        $params = array(
            'client_id' => $this->app->getId(),
            'client_secret' => $this->app->getSecret(),
            'grant_type' => 'client_credentials',
        );

        $response = $this->app->request('oauth/access_token', $params);
        parse_str($response, $components);

        // return array "access_token", ...;
        return $components;
    }

    /**
     * Fetches an access_token with extended expiration time, along with any other information provided by Facebook.
     *
     * @see https://developers.facebook.com/docs/offline-access-deprecation/#extend_token (search for fb_exchange_token).
     * @param string $access_token the access token to exchange
     * @return array the access token with extended expiration time and other
     *                          information (expiration, etc.)
     */
    public function exchangeAccessToken($access_token)
    {
        $params = array(
            'grant_type' => 'fb_exchange_token',
            'fb_exchange_token' => $access_token,
            'client_id' => $this->app->getId(),
            'client_secret' => $this->app->getSecret(),
        );
        $response = $this->app->request('oauth/access_token?', $params);
        parse_str($response, $components);

        // return array "access_token", ...;
        return $components;
    }
}
