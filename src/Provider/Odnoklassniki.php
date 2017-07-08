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

class Odnoklassniki extends AuthProviderBase
{
    /**
     * Social Public Key
     *
     * @var string|null
     */
    protected $publicKey = null;

    /**
     * {@inheritDoc}
     */
    protected $provider = self::PROVIDER_ODNOKLASSNIKI;

    /**
     * {@inheritDoc}
     */
    protected $fieldsMap = array(
        // local property name => external property name
        self::ATTRIBUTE_ID         => 'uid',
        self::ATTRIBUTE_EMAIL      => 'email',
        self::ATTRIBUTE_NAME       => 'name',
        self::ATTRIBUTE_AVATAR_URL => 'pic_2',
        self::ATTRIBUTE_SEX        => 'gender',
        self::ATTRIBUTE_BIRTHDAY   => 'birthday',
    );

    /**
     * {@inheritDoc}
     */
    protected $knownConfigParams = array(
        'client_id',
        'client_secret',
        'redirect_uri',
        'public_key',
    );

    /**
     * Get user social id or null if it is not set
     *
     * @return string|null
     */
    public function getUserPageUrl()
    {
        if (isset($this->userInfo['uid'])) {
            return 'http://www.odnoklassniki.ru/profile/' . $this->userInfo['uid'];
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate($params)
    {
        if (!isset($this->publicKey)) {
            // need to have own public key specified
            return false;
        }

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
        $authInfo = $this->post('http://api.odnoklassniki.ru/oauth/token.do', $params);
        if (!isset($authInfo['access_token'])) {
            // something went wrong
            return false;
        }

        // Auth OK, can fetch additional info
        $params = array(
            'method'          => 'users.getCurrentUser',
            'access_token'    => $authInfo['access_token'],
            'application_key' => $this->publicKey,
            'format'          => 'json',
            'sig'             => md5("application_key={$this->publicKey}format=jsonmethod=users.getCurrentUser" . md5("{$authInfo['access_token']}{$this->clientSecret}")),
        );

        // Fetch user info
        $userInfo = $this->get('http://api.odnoklassniki.ru/fb.do', $params);
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
            'auth_url'    => 'http://www.odnoklassniki.ru/oauth/authorize',
            'auth_params' => array(
                'client_id'     => $this->clientId,
                'redirect_uri'  => $this->redirectUri,
                'response_type' => 'code',
            )
        );
    }
}
