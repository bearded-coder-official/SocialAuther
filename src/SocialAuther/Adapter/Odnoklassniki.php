<?php

namespace SocialAuther\Adapter;

class Odnoklassniki extends AbstractAdapter
{
    /**
     * Social Public Key
     *
     * @var string|null
     */
    protected $publicKey = null;

    /**
     * Constructor.
     *
     * @param array $config
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($config)
    {
        if (!is_array($config))
            throw new Exception\InvalidArgumentException(
                __METHOD__ . ' expects an array with keys: `client_id`, `client_secret`, `redirect_uri`, `public_key`'
            );

        foreach (array('client_id', 'client_secret', 'redirect_uri', 'public_key') as $param) {
            if (!array_key_exists($param, $config)) {
                throw new Exception\InvalidArgumentException(
                    __METHOD__ . ' expects an array with key: `' . $param . '`'
                );
            } else {
                $property = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $param))));
                $this->$property = $config[$param];
            }
        }

        $this->socialFieldsMap = array(
            'socialId'   => 'uid',
            'email'      => 'email',
            'name'       => 'name',
            'avatar'     => 'pic_2',
            'sex'        => 'gender',
            'birthday'   => 'birthday'
        );

        $this->provider = 'odnoklassniki';
    }

    /**
     * Get user social id or null if it is not set
     *
     * @return string|null
     */
    public function getSocialPage()
    {
        $result = null;
        if (isset($this->userInfo['uid'])) {
            return 'http://www.odnoklassniki.ru/profile/' . $this->userInfo['uid'];
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

        if (isset($_GET['code'])) {
            $params = array(
                'code' => $_GET['code'],
                'redirect_uri' => $this->redirectUri,
                'grant_type' => 'authorization_code',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret
            );

            $tokenInfo = $this->post('http://api.odnoklassniki.ru/oauth/token.do', $params);

            if (isset($tokenInfo['access_token']) && isset($this->publicKey)) {
                $sign = md5("application_key={$this->publicKey}format=jsonmethod=users.getCurrentUser" . md5("{$tokenInfo['access_token']}{$this->clientSecret}"));

                $params = array(
                    'method'          => 'users.getCurrentUser',
                    'access_token'    => $tokenInfo['access_token'],
                    'application_key' => $this->publicKey,
                    'format'          => 'json',
                    'sig'             => $sign
                );

                $userInfo = $this->get('http://api.odnoklassniki.ru/fb.do', $params);

                if (isset($userInfo['uid'])) {
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
        return array(
            'auth_url'    => 'http://www.odnoklassniki.ru/oauth/authorize',
            'auth_params' => array(
                'client_id'     => $this->clientId,
                'response_type' => 'code',
                'redirect_uri'  => $this->redirectUri
            )
        );
    }
}