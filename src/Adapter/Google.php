<?php

namespace SocialAuther\Adapter;

class Google extends AbstractAdapter
{
    /**
     * {@inheritDoc}
     */
    protected $provider = 'google';

    /**
     * {@inheritDoc}
     */
    protected $fieldsMap = array(
        // local property name => external property name
        'socialId'   => 'id',
        'email'      => 'email',
        'name'       => 'name',
        'socialPage' => 'link',
        'sex'        => 'gender',
        'avatar'     => 'picture',
    );

    /**
     * Get user birthday or null if it is not set
     *
     * @return string|null
     */
    public function getBirthday()
    {
        if (isset($this->userInfo['birthday'])) {
            $this->userInfo['birthday'] = str_replace('0000', date('Y'), $this->userInfo['birthday']);
            return date('d.m.Y', strtotime($this->userInfo['birthday']));
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
                'redirect_uri'  => $this->redirectUri,
                'grant_type'    => 'authorization_code',
                'code'          => $_GET['code']
            );

            // Perform auth
            $authInfo = $this->post('https://accounts.google.com/o/oauth2/token', $params);
            if (isset($authInfo['access_token'])) {
                // Auth OK, can fetch additional info
                $params['access_token'] = $authInfo['access_token'];

                // Fetch additional info
                $userInfo = $this->get('https://www.googleapis.com/oauth2/v1/userinfo', $params);
                if (isset($userInfo[$this->fieldsMap['socialId']])) {
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
            'auth_url'    => 'https://accounts.google.com/o/oauth2/auth',
            'auth_params' => array(
                'redirect_uri'  => $this->redirectUri,
                'response_type' => 'code',
                'client_id'     => $this->clientId,
                'scope'         => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile'
            )
        );
    }
}
