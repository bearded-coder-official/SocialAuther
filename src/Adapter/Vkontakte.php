<?php

namespace SocialAuther\Adapter;

class Vkontakte extends AbstractAdapter
{
    /**
     * {@inheritDoc}
     */
    protected $provider = 'vkontakte';

    /**
     * {@inheritDoc}
     */
    protected $fieldsMap = array(
        // local property name => external property name
        self::ATTRIBUTE_ID         => 'uid',
        self::ATTRIBUTE_EMAIL      => 'email',
        self::ATTRIBUTE_AVATAR_URL => 'photo_big',
        self::ATTRIBUTE_BIRTHDAY   => 'bdate',
    );

    /**
     * Get user name or null if it is not set
     *
     * @return string|null
     */
    public function getName()
    {
        if (isset($this->userInfo['first_name']) && isset($this->userInfo['last_name'])) {
            // both first and last name
            return $this->userInfo['first_name'] . ' ' . $this->userInfo['last_name'];
        }

        if (isset($this->userInfo['first_name']) && !isset($this->userInfo['last_name'])) {
            // first name only
            return $this->userInfo['first_name'];
        }

        if (!isset($this->userInfo['first_name']) && isset($this->userInfo['last_name'])) {
            // last name only
            return $this->userInfo['last_name'];
        }

        // nothing available
        return null;
    }

    /**
     * Get user social id or null if it is not set
     *
     * @return string|null
     */
    public function getPageUrl()
    {
        if (isset($this->userInfo['screen_name'])) {
            // screen name is available - can build URL
            return "http://vk.com/{$this->userInfo['screen_name']}";
        }

        return null;
    }

    /**
     * Get user sex or null if it is not set
     *
     * @return string|null
     */
    public function getSex()
    {
        if (isset($this->userInfo['sex'])) {
            // gender is specified
            return $this->userInfo['sex'] == 1 ? 'female' : 'male';
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate()
    {
        $result = false;

        if (isset($_GET['code'])) {
            $params = array(
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code'          => $_GET['code'],
                'redirect_uri'  => $this->redirectUri
            );

            // Perform auth
            $authInfo = $this->get('https://oauth.vk.com/access_token', $params);
            if (isset($authInfo['access_token'])) {
                // Auth OK, can fetch additional info
                $params = array(
                    'uids'         => $authInfo['user_id'],
                    'fields'       => 'uid,first_name,last_name,screen_name,sex,bdate,photo_big',
                    'access_token' => $authInfo['access_token']
                );

                // Fetch additional info
                $userInfo = $this->get('https://api.vk.com/method/users.get', $params);
                if (isset($authInfo['email'])) {
                        $userInfo['response'][0]['email'] = $authInfo['email'];
                }
                if (isset($userInfo['response'][0]['uid'])) {
                    $this->userInfo = $userInfo['response'][0];
                    $result = true;
                }
            }
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthUrlComponents()
    {
        return array(
            'auth_url'    => 'http://oauth.vk.com/authorize',
            'auth_params' => array(
                'client_id'     => $this->clientId,
                'scope'         => 'notify,email',
                'redirect_uri'  => $this->redirectUri,
                'response_type' => 'code'
            )
        );
    }
}
