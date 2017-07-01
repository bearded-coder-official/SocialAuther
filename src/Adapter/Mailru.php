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

class Mailru extends AdapterBase
{
    /**
     * {@inheritDoc}
     */
    protected $provider = self::PROVIDER_MAILRU;

    /**
     * {@inheritDoc}
     */
    protected $fieldsMap = array(
        // local property name => external property name
        self::ATTRIBUTE_ID         => 'uid',
        self::ATTRIBUTE_EMAIL      => 'email',
        self::ATTRIBUTE_NAME       => 'nick',
        self::ATTRIBUTE_PAGE_URL   => 'link',
        self::ATTRIBUTE_AVATAR_URL => 'pic_big',
        self::ATTRIBUTE_BIRTHDAY   => 'birthday',
    );

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
                'grant_type'    => 'authorization_code',
                'code'          => $_GET['code'],
                'redirect_uri'  => $this->redirectUri
            );

            // Perform auth
            $authInfo = $this->post('https://connect.mail.ru/oauth/token', $params);
            if (isset($authInfo['access_token'])) {
                // Auth OK, can fetch additional info
                $params = array(
                    'method'       => 'users.getInfo',
                    'secure'       => '1',
                    'app_id'       => $this->clientId,
                    'session_key'  => $authInfo['access_token'],
                    'sig'          => md5("app_id={$this->clientId}method=users.getInfosecure=1session_key={$authInfo['access_token']}{$this->clientSecret}"),
                );

                // Fetch additional info
                $userInfo = $this->get('http://www.appsmail.ru/platform/api', $params);
                if (isset($userInfo[0]['uid'])) {
                    $this->userInfo = array_shift($userInfo);
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
            'auth_url'    => 'https://connect.mail.ru/oauth/authorize',
            'auth_params' => array(
                'client_id'     => $this->clientId,
                'response_type' => 'code',
                'redirect_uri'  => $this->redirectUri
            )
        );
    }
}
