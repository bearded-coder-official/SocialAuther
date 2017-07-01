<?php

/**
 * SocialAuther (http://socialauther.stanislasgroup.com/)
 *
 * @author: Stanislav Protasevich
 * @author: sunsingerus (https://github.com/sunsingerus)
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace SocialAuther\Adapter;

class Facebook extends AdapterBase
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
    public function getAvatarUrl()
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
                'code'          => $_GET['code'],
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
