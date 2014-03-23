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
            'page'       => 'link',
            'image'      => 'picture',
            'sex'        => 'sex',
            'name'       => 'display_name',
        );

        $this->provider = 'yandex';
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
                        $birthDate = explode('-', $this->response['birthday']);
                        $this->userInfo['birthDay']   = isset($birthDate[2]) ? $birthDate[2] : null;
                        $this->userInfo['birthMonth'] = isset($birthDate[1]) ? $birthDate[1] : null;
                        $this->userInfo['birthYear']  = isset($birthDate[0]) ? $birthDate[0] : null;
                    }

                    if (isset($this->response['real_name'])) {
                        $name = explode(' ', $this->response['real_name']);
                        $this->userInfo['secondName'] = (isset($name[0]) && !empty($name[0])) ? $name[0] : null;
                        $this->userInfo['firstName'] = (isset($name[1]) && !empty($name[1])) ? $name[1] : null;
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