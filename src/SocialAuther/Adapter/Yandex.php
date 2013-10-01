<?php

namespace SocialAuther\Adapter;

class Yandex extends AbstractAdapter
{
    public function __construct($config)
    {
        parent::__construct($config);

        $this->fieldsMap = array(
            'id'         => 'id',
            'email'      => 'default_email',
            'name'       => 'real_name',
            'page'       => 'link',
            'image'      => 'picture'
        );

        $this->provider = 'yandex';
    }

    /**
     * Get user sex or null if it is not set
     *
     * @return string|null
     */
    public function getSex()
    {
        if (isset($this->response['sex']) && in_array($this->response['sex'], array('male', 'female'))) {
            return $this->response['sex'];
        }

        return null;
    }

    /**
     * Call to provider server, get access token, authenticate,
     * parse user profile data and return result of all this.
     *
     * @return boolean
     */
    protected function readUserProfile()
    {
        if (isset($_GET['code'])) {
            $params = array(
                'grant_type' => 'authorization_code',
                'code' => $_GET['code'],
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret
            );

            $tokenInfo = $this->post('https://oauth.yandex.ru/token', $params);

            if (isset($tokenInfo['access_token'])) {
                $params = array(
                    'format' => 'json',
                    'oauth_token' => $tokenInfo['access_token']
                );

                $userInfo = $this->get('https://login.yandex.ru/info', $params);

                if (isset($userInfo['id'])) {
                    $this->parseUserData($userInfo);

                    if (isset($this->response['birthday'])) {
                        $birthDate = explode('.', $this->response['birthday']);
                        $this->userInfo['birthDay']   = isset($birthDate[0]) ? $birthDate[0] : null;
                        $this->userInfo['birthMonth'] = isset($birthDate[1]) ? $birthDate[1] : null;
                        $this->userInfo['birthYear']  = isset($birthDate[2]) ? $birthDate[2] : null;
                    }
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Prepare params for authentication url
     *
     * @return array
     */
    protected function prepareAuthParams()
    {
        return array(
            'auth_url'    => 'https://oauth.yandex.ru/authorize',
            'auth_params' => array(
                'response_type' => 'code',
                'client_id'     => $this->clientId,
                'display'       => 'popup'
            )
        );
    }
}