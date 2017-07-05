<?php

/**
 * SocialAuther (http://socialauther.stanislasgroup.com/)
 *
 * @author: Stanislav Protasevich
 * @author: sunsingerus (https://github.com/sunsingerus)
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace SocialAuther\Provider;

class Google extends AuthProviderBase
{
    /**
     * {@inheritDoc}
     */
    protected $provider = self::PROVIDER_GOOGLE;

    /**
     * {@inheritDoc}
     */
    protected $fieldsMap = array(
        // local property name => external property name
        self::ATTRIBUTE_ID         => 'id',
        self::ATTRIBUTE_EMAIL      => 'email',
        self::ATTRIBUTE_NAME       => 'name',
        self::ATTRIBUTE_PAGE_URL   => 'link',
        self::ATTRIBUTE_SEX        => 'gender',
        self::ATTRIBUTE_AVATAR_URL => 'picture',
    );

    /**
     * Get user birthday or null if it is not set
     *
     * @return string|null
     */
    public function getUserBirthday()
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
    public function authenticate($params)
    {
        $params = $this->getAuthenticationParams($params);
        if (empty($params)) {
            // no required params provided
            return false;
        }

        $params = array(
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUri,
            'code'          => $params['code'],
            'grant_type'    => 'authorization_code',
        );

        // Perform auth
        $authInfo = $this->post('https://accounts.google.com/o/oauth2/token', $params);
        if (!isset($authInfo['access_token'])) {
            // something went wrong
            return false;
        }

        // Auth OK, can fetch additional info
        $params['access_token'] = $authInfo['access_token'];

        // Fetch user info
        $userInfo = $this->get('https://www.googleapis.com/oauth2/v1/userinfo', $params);
        if (!isset($userInfo[$this->fieldsMap[static::ATTRIBUTE_ID]])) {
            // something went wrong
            return false;
        }

        // user info received
        $this->userInfo = $userInfo;

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthUrlComponents()
    {
        return array(
            'auth_url'    => 'https://accounts.google.com/o/oauth2/auth',
            'auth_params' => array(
                'client_id'     => $this->clientId,
                'redirect_uri'  => $this->redirectUri,
                'response_type' => 'code',
                'scope'         => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile'
            )
        );
    }
}
