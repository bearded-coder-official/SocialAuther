<?php

namespace SocialAuther\Adapter;

class Facebook extends AbstractAdapter
{
    /**
     * {@inheritDoc}
     */
    protected $provider = 'facebook';

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
        'birthday'   => 'birthday'
    );

    /**
     * Get url of user's avatar or null if it is not set
     *
     * @return string|null
     */
    public function getAvatar()
    {
        if (isset($this->userInfo['username'])) {
            return "http://graph.facebook.com/{$this->userInfo['username']}/picture?type=large";
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
                'redirect_uri'  => $this->redirectUri,
                'client_secret' => $this->clientSecret,
                'code'          => $_GET['code']
            );

            // Perform auth
            $authInfo = $this->post('https://graph.facebook.com/oauth/access_token', $params);
            if (isset($authInfo['access_token'])) {
                // Auth OK, can fetch additional info
                $params = array(
                    'access_token' => $authInfo['access_token']
                );

                // Fetch additional info
                $userInfo = $this->get('https://graph.facebook.com/me', $params);
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
            'auth_url'    => 'https://www.facebook.com/dialog/oauth',
            'auth_params' => array(
                'client_id'     => $this->clientId,
                'redirect_uri'  => $this->redirectUri,
                'response_type' => 'code',
                'scope'         => 'email,user_birthday'
            )
        );
    }
}
