<?php

namespace SocialAuther\Adapter;

class Twitter extends AbstractAdapter
{
    public function __construct($config)
    {
        parent::__construct($config);

        $this->socialFieldsMap = array(
            'socialId' => 'id',
            'name'     => 'name',
            'email'    => 'email',
            'sex'      => 'sex',
            'birthday' => 'bdate'

        );

        $this->provider = 'twitter';
        $this->responseType = 'oauth_token';
    }

    /**
     * Get user social id or null if it is not set
     *
     * @return string|null
     */
    public function getSocialPage()
    {
        $result = null;

        if (isset($this->userInfo['screen_name'])) {
            $result = 'http://twitter.com/' . $this->userInfo['screen_name'];
        }

        return $result;
    }

    /**
     * Get url of user's avatar or null if it is not set
     *
     * @return string|null
     */
    public function getAvatar()
    {
        $result = null;

        if (isset($this->userInfo['profile_image_url'])) {
            $result = implode('', explode('_normal', $this->userInfo['profile_image_url']));
        }

        return $result;
    }

    /**
     * Authenticate and return bool result of authentication
     *
     * @return bool
     */
    public function authenticate()
    {
        $result = false;

        if (isset($_GET['oauth_token']) && isset($_GET['oauth_verifier'])) {
            $params = array(
                'oauth_token'    => $_GET['oauth_token'],
                'oauth_verifier' => $_GET['oauth_verifier'],
            );

            $accessTokenUrl = 'https://api.twitter.com/oauth/access_token';
            $params = $this->prepareUrlParams($accessTokenUrl, $params);

            $accessTokens = $this->get($accessTokenUrl, $params, false);
            parse_str($accessTokens, $accessTokens);

            if (isset($accessTokens['oauth_token'])) {
                $getDataUrl = 'https://api.twitter.com/1.1/users/show.json';
                $params = array(
                    'oauth_token' => $accessTokens['oauth_token'],
                    'screen_name' => $accessTokens['screen_name'],
                    'include_entities' => 'false',
                );
                $params = $this->prepareUrlParams($getDataUrl, $params, $accessTokens['oauth_token_secret']);

                $userInfo = $this->get($getDataUrl, $params);

                if (isset($userInfo['id'])) {
                    $this->userInfo = $userInfo;
                    $result = true;
                }
            }
        }

        return $result;
    }

    /**
     * Prepare params for authentication url
     *
     * @return array
     */
    public function prepareAuthParams()
    {
        $requestTokenUrl = 'https://api.twitter.com/oauth/request_token';
        $params = $this->prepareUrlParams($requestTokenUrl, array('oauth_callback' => $this->redirectUri));
        $requestTokens = $this->get($requestTokenUrl, $params, false);
        parse_str($requestTokens, $requestTokens);

        return array(
            'auth_url'    => 'https://api.twitter.com/oauth/authorize',
            'auth_params' => array('oauth_token' => isset($requestTokens['oauth_token'])?$requestTokens['oauth_token']:NULL),
        );
    }


    /**
     * Prepare url-params with signature
     *
     * @return array
     */
    private function prepareUrlParams($url, $params = array(), $oauth_token = '', $type = 'GET') 
    {
        $params += array(
            'oauth_consumer_key'     => $this->clientId,
            'oauth_nonce'            => md5(uniqid(rand(), true)),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp'        => time(),
            'oauth_token'            => $oauth_token,
            'oauth_version'          => '1.0',
        );
        ksort($params);
        $sigBaseStr = $type . '&' . urlencode($url) . '&' . urlencode(http_build_query($params));
        $key = $this->clientSecret . '&' . $oauth_token;
        $params['oauth_signature'] = base64_encode(hash_hmac("sha1", $sigBaseStr, $key, true));
        $params = array_map('urlencode', $params);
        return $params;
    }
}
