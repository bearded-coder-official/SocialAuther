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

class Yandex extends AdapterBase
{
    /**
     * {@inheritDoc}
     */
    protected $provider = self::PROVIDER_YANDEX;

    /**
     * {@inheritDoc}
     */
    protected $fieldsMap = array(
        self::ATTRIBUTE_ID         => 'id',
        self::ATTRIBUTE_EMAIL      => 'default_email',
        self::ATTRIBUTE_NAME       => 'real_name',
        self::ATTRIBUTE_PAGE_URL   => 'link',
        self::ATTRIBUTE_AVATAR_URL => 'default_avatar_id',
        self::ATTRIBUTE_SEX        => 'sex',
        self::ATTRIBUTE_BIRTHDAY   => 'birthday',
    );

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
            'code'          => $params['code'],
            'grant_type'    => 'authorization_code',
        );

        // Perform auth
        $authInfo = $this->post('https://oauth.yandex.ru/token', $params);
        if (!isset($authInfo['access_token'])) {
            // something went wrong
            return false;
        }

        // Auth OK, can fetch additional info
        $params = array(
            'format'      => 'json',
            'oauth_token' => $authInfo['access_token']
        );

        // Fetch user info
        $userInfo = $this->get('https://login.yandex.ru/info', $params);
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
            'auth_url'    => 'https://oauth.yandex.ru/authorize',
            'auth_params' => array(
                'response_type' => 'code',
                'client_id'     => $this->clientId,
                'display'       => 'popup'
            )
        );
    }
}
