<?php

namespace SocialAuther\Adapter;

class Yandex extends AbstractAdapter
{
    /**
     * {@inheritDoc}
     */
    protected $provider = 'yandex';

    /**
     * {@inheritDoc}
     */
    protected $fieldsMap = array(
        'socialId'   => 'id',
        'email'      => 'default_email',
        'name'       => 'real_name',
        'socialPage' => 'link',
        'avatar'     => 'default_avatar_id',
        'sex'        => 'sex',
        'birthday'   => 'birthday'
    );

    /**
     * {@inheritDoc}
     */
    public function authenticate()
    {
        $result = false;

        if (isset($_GET['code'])) {
            $params = array(
                'grant_type'    => 'authorization_code',
                'code'          => $_GET['code'],
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret
            );

            // Perform auth
            $authInfo = $this->post('https://oauth.yandex.ru/token', $params);
            if (isset($authInfo['access_token'])) {
                // Auth OK, can fetch additional info
                $params = array(
                    'format'      => 'json',
                    'oauth_token' => $authInfo['access_token']
                );

                // Fetch additional info
                $userInfo = $this->get('https://login.yandex.ru/info', $params);
                if (isset($userInfo['id'])) {
                    $this->userInfo = $userInfo;
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
            'auth_url'    => 'https://oauth.yandex.ru/authorize',
            'auth_params' => array(
                'response_type' => 'code',
                'client_id'     => $this->clientId,
                'display'       => 'popup'
            )
        );
    }
}
