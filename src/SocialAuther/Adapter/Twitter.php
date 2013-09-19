<?php

namespace SocialAuther\Adapter;

use OrbisTools\Session;
use SocialAuther\Bridge\TwitterAuthSA;

class Twitter extends AbstractAdapter
{
    /**
     * @var TwitterAuthSA
     */
    protected $twAuth;

    /**
     * @var Session
     */
    protected $session;

    protected $sessionClosures = null;

    public function prepareAuthParams()
    {
        throw new \LogicException('not used method');
    }

    public function __construct($config, Session $session = null)
    {
        if (isset($config['redirect_uri'])) {
            if (strpos($config['redirect_uri'], 'code=') === false) {
                $config['redirect_uri'] = $config['redirect_uri'] . '&code=twitter';
            }
        }

        if (!is_null($session)) {
            $this->setSession($session);
        }

        parent::__construct($config);

        $this->socialFieldsMap = array(
            'socialId'   => 'id_str',
            'avatar'     => 'profile_image_url',
        );

        $this->provider = 'twitter';
    }

    public function getTwAuth()
    {
        if (is_null($this->twAuth)) {
            $this->twAuth = new TwitterAuthSA($this->clientId, $this->clientSecret, $this->redirectUri, $this->getSession());
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

    public function getFirstName()
    {
        $name = $this->getName();
        $name = explode(' ', $name);
        if (count($name)<1) {
            return null;
        }
        return reset($name);
    }

    public function getSecondName()
    {
        $name = $this->getName();
        $name = explode(' ', $name);
        if (count($name)<2) {
            return null;
        }
        return end($name);
    }

    public function getAvatar()
    {
        $avatarUrl =  parent::getAvatar();
        $avatarUrl = str_replace('_normal.', '.', $avatarUrl);
        return $avatarUrl;
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

    /**
     * @return mixed
     */
    public function getSessionClosures()
    {
        return $this->sessionClosures;
    }

    /**
     * @param mixed $sessionClosures
     */
    public function setSessionClosures(array $sessionClosures)
    {
        $this->sessionClosures = $sessionClosures;
    }

    public function setSession(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @return \OrbisTools\Session
     */
    public function getSession()
    {
        if (is_null($this->session)) {
            $this->session = new Session($this->getSessionClosures());
        }
        return $this->session;
    }



}