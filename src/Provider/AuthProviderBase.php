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

use SocialAuther\Exception\InvalidArgumentException;

abstract class AuthProviderBase implements AuthProviderInterface
{
    const PROVIDER_FACEBOOK      = 'fb';
    const PROVIDER_GOOGLE        = 'google';
    const PROVIDER_MAILRU        = 'mailru';
    const PROVIDER_ODNOKLASSNIKI = 'ok';
    const PROVIDER_TWITTER       = 'twitter';
    const PROVIDER_VKONTAKTE     = 'vk';
    const PROVIDER_YANDEX        = 'yandex';

    protected static $providers = array(
        self::PROVIDER_FACEBOOK      => 'facebook.com',
        self::PROVIDER_GOOGLE        => 'google.com',
        self::PROVIDER_MAILRU        => 'mail.ru',
        self::PROVIDER_ODNOKLASSNIKI => 'ok.ru',
        self::PROVIDER_TWITTER       => 'twitter.com',
        self::PROVIDER_VKONTAKTE     => 'vk.com',
        self::PROVIDER_YANDEX        => 'yandex.ru',
    );

    /**
     * Social Client ID
     *
     * @var string null
     */
    protected $clientId = null;

    /**
     * Social Client Secret
     *
     * @var string null
     */
    protected $clientSecret = null;

    /**
     * Social Redirect Uri
     *
     * @var string null
     */
    protected $redirectUri = null;

    /**
     * Name of auth provider
     *
     * @var string null
     */
    protected $provider = null;

    const ATTRIBUTE_ID         = 'socialId';
    const ATTRIBUTE_EMAIL      = 'email';
    const ATTRIBUTE_NAME       = 'name';
    const ATTRIBUTE_PAGE_URL   = 'socialPage';
    const ATTRIBUTE_AVATAR_URL = 'avatar';
    const ATTRIBUTE_SEX        = 'sex';
    const ATTRIBUTE_BIRTHDAY   = 'birthday';


    /**
     * Social Fields Map for universal keys
     *
     * @var array
     */
    protected $fieldsMap = array(
        self::ATTRIBUTE_ID         => null,
        self::ATTRIBUTE_EMAIL      => null,
        self::ATTRIBUTE_NAME       => null,
        self::ATTRIBUTE_PAGE_URL   => null,
        self::ATTRIBUTE_AVATAR_URL => null,
        self::ATTRIBUTE_SEX        => null,
        self::ATTRIBUTE_BIRTHDAY   => null,
    );

    /**
     * Storage for user info
     *
     * @var array
     */
    protected $userInfo = null;

    /**
     * Name of parameter which service returnes
     *
     * @var string
     */
    protected $responseType = 'code';

    /**
     * Accepted and required fields in initial config
     *
     * @var array
     */
    protected $knownConfigParams = array(
        'client_id',
        'client_secret',
        'redirect_uri',
    );

    /**
     * Constructor
     *
     * @param array $config
     * @throws InvalidArgumentException
     */
    public function __construct(array $config)
    {
        // throws Exception if anything is not good
        $this->verifyConfig($config);

        // assign values from config to local properties
        foreach ($config as $name => $value) {
            // build property name
            $property = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $name))));
            // assign property with config's value
            $this->$property = $config[$name];
        }
    }

    /**
     * Check whether configuration is valid
     *
     * @param array $config
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    public function verifyConfig(array $config)
    {
        // check for mandatory params
        foreach ($this->knownConfigParams as $param) {
            if (!array_key_exists($param, $config)) {
                // no mandatory param provided
                throw new InvalidArgumentException("Expects an array with key: '$param'");
            }
        }

        // check for unknown params
        foreach (array_keys($config) as $param) {
            if (!in_array($param, $this->knownConfigParams)) {
                // unknown param provided
                throw new InvalidArgumentException("Unrecognized key: '$param'");
            }
        }

        // all OK
        return true;
    }

    /**
     * Check whether provider provider id/name is valid
     *
     * @param $provider name/id of the provider (One of AuthProviderBase::PROVIDER_*)
     * @return bool
     */
    public static function isProviderValid($provider)
    {
        return array_key_exists($provider, static::$providers);
    }

    /**
     * Get unified user attribute based on unified attribute name
     *
     * @param $attribute
     * @return mixed|null
     */
    protected function getUserAttribute($attribute)
    {
        // do we have requested attribute?
        if (isset($this->userInfo[$this->fieldsMap[$attribute]])) {
            // yes, we have requested attribute
            return $this->userInfo[$this->fieldsMap[$attribute]];
        }

        // we do not have requested attribute, just return something
        return null;
    }

    /**
     * Get user social id or null if it is not set
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->getUserAttribute(static::ATTRIBUTE_ID);
    }

    /**
     * Get user email or null if it is not set
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->getUserAttribute(static::ATTRIBUTE_EMAIL);
    }

    /**
     * Get user name or null if it is not set
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->getUserAttribute(static::ATTRIBUTE_NAME);
    }

    /**
     * Get user social page url or null if it is not set
     * @return string|null
     */
    public function getPageUrl()
    {
        return $this->getUserAttribute(static::ATTRIBUTE_PAGE_URL);
    }

    /**
     * Get url of user's avatar or null if it is not set
     *
     * @return string|null
     */
    public function getAvatarUrl()
    {
        return $this->getUserAttribute(static::ATTRIBUTE_AVATAR_URL);
    }

    /**
     * Get user sex or null if it is not set
     *
     * @return string|null
     */
    public function getSex()
    {
        return $this->getUserAttribute(static::ATTRIBUTE_SEX);
    }

    /**
     * Get user birthday in format dd.mm.YYYY or null if it is not set
     *
     * @return string|null
     */
    public function getBirthday()
    {
        $result = $this->getUserAttribute(static::ATTRIBUTE_BIRTHDAY);

        if (!empty($result)) {
            return date('d.m.Y', strtotime($result));
        }

        return $result;
    }

    /**
     * Return provider's name/id
     *
     * @return string
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Get user-friendly name of the provider
     *
     * @param null $provider provider's name/id (One of AuthProviderBase::PROVIDER_*)
     *
     * @return string|null
     */
    public function getProviderName($provider = null)
    {
        $provider = empty($provider) ? $this->provider : $provider;
        return array_key_exists($provider, static::$providers) ? static::$providers[$provider] : null;
    }

    /**
     * Return name of parameter which service returns
     *
     * @return string
     */
    public function getResponseType()
    {
        return $this->responseType;
    }

    /**
     * Get all components required to build authentication url
     *
     * @return array
     */
    abstract public function getAuthUrlComponents();

    /**
     * {@inheritDoc}
     */
    public function getAuthenticationUrl()
    {
        $config = $this->getAuthUrlComponents();

        return $config['auth_url'] . '?' . urldecode(http_build_query($config['auth_params']));
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthenticationParams($params)
    {
        if (!isset($params['code'])) {
            return false;
        }

        return array(
            'code' => $params['code'],
        );
    }

    /**
     * Make post request and return result
     *
     * @param string $url
     * @param string $params
     * @param bool $parse
     * @return array|string
     */
    protected function post($url, $params, $parse = true)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode(http_build_query($params)));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($curl);
        curl_close($curl);

        if ($parse) {
            $result = json_decode($result, true);
        }

        return $result;
    }

    /**
     * Make get request and return result
     *
     * @param $url
     * @param $params
     * @param bool $parse
     * @return mixed
     */
    protected function get($url, $params, $parse = true)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url . '?' . urldecode(http_build_query($params)));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($curl);
        curl_close($curl);

        if ($parse) {
            $result = json_decode($result, true);
        }

        return $result;
    }
}
