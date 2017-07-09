<?php

/**
 * SocialAuther (http://socialauther.stanislasgroup.com/)
 *
 * @author: Stanislav Protasevich
 * @author: sunsingerus (https://github.com/sunsingerus)
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace SocialAuth\Provider;

class Facebook extends AuthProviderBase
{
    /**
     * {@inheritDoc}
     */
    protected $provider = self::PROVIDER_FACEBOOK;

    /**
     * {@inheritDoc}
     */
    protected $fieldsMap = array(
        // local property name => external property name
        self::ATTRIBUTE_ID       => 'id',
        self::ATTRIBUTE_EMAIL    => 'email',
        self::ATTRIBUTE_NAME     => 'name',
        self::ATTRIBUTE_PAGE_URL => 'link',
        self::ATTRIBUTE_SEX      => 'gender',
        self::ATTRIBUTE_BIRTHDAY => 'birthday',
    );

    /**
     * Get url of user's avatar or null if it is not set
     *
     * @return string|null
     */
    public function getUserAvatarUrl()
    {
        if (isset($this->userInfo['username'])) {
            return "http://graph.facebook.com/{$this->userInfo['username']}/picture?type=large";
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
        );

        // Perform auth
        $authInfo = $this->post('https://graph.facebook.com/oauth/access_token', $params);
        if (!isset($authInfo['access_token'])) {
            // something went wrong
            return false;
        }

        // Auth OK, can fetch additional info
        $params = array(
            'access_token' => $authInfo['access_token']
        );

        // Fetch user info
        $userInfo = $this->get('https://graph.facebook.com/me', $params);
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
