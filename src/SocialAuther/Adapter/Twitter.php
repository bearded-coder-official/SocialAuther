<?php

namespace SocialAuther\Adapter;

use SocialAuther\Bridge\TwitterAuthSA;

class Twitter extends AbstractAdapter
{
    /**
     * @var TwitterAuthSA
     */
    protected $twAuth;

    public function prepareAuthParams()
    {
        throw new \LogicException('not used method');
    }

    public function __construct($config)
    {
        if (isset($config['redirect_uri'])) {
            if (strpos($config['redirect_uri'], 'code=') === false) {
                $config['redirect_uri'] = $config['redirect_uri'] . '&code=twitter';
            }
        }
        parent::__construct($config);

        $this->socialFieldsMap = array(
            'socialId'   => 'id_str',
            'avatar'     => 'profile_image_ur',
        );

        $this->provider = 'twitter';
    }

    public function getTwAuth()
    {
        if (is_null($this->twAuth)) {
            $this->twAuth = new TwitterAuthSA($this->clientId, $this->clientSecret, $this->redirectUri);
        }
        return $this->twAuth;
    }

    /**
     * Get user name or null if it is not set
     *
     * @return string|null
     */
    public function getName()
    {
       return (isset($this->userInfo['name'])) ? $this->userInfo['name'] : null;
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
     * Get user sex or null if it is not set
     *
     * @return string|null
     */
    public function getSex()
    {
        $result = null;
        if (isset($this->userInfo['sex'])) {
            $result = $this->userInfo['sex'] == 1 ? 'female' : 'male';
        }

        return $result;
    }

    //////////////////////////////////////////////

    public function getAuthUrl()
    {
        return $this->getTwAuth()->getAuthUrl();
    }

    /**
     * Authenticate and return bool result of authentication
     *
     * @return bool
     */
    public function authenticate()
    {
        if (!$this->getTwAuth()->checkAuthToken()) {
            return false;
        }

        $userInfo = $this->getTwAuth()->authenticate();
        if (!$userInfo) {
            return false;
        }

        $this->userInfo = $userInfo;
        return true;
    }

}