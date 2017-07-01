<?php
/**
 * SocialAuther (http://socialauther.stanislasgroup.com/)
 *
 * @author: Stanislav Protasevich
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace SocialAuther\Adapter;

use SocialAuther\Exception\InvalidArgumentException;

abstract class AbstractAdapter implements AdapterInterface
{
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

    const ATTRIBUTE_ID        = 'socialId';
    const ATTRIBUTE_EMAIL     = 'email';
    const ATTRIBUTE_NAME      = 'name';
    const ATTRIBUTE_PAGE      = 'socialPage';
    const ATTRIBUTE_AVATAR    = 'avatar';
    const ATTRIBUTE_SEX       = 'sex';
    const AATTRIBUTE_BIRTHDAY = 'birthday';


    /**
     * Social Fields Map for universal keys
     *
     * @var array
     */
    protected $fieldsMap = array();

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
        foreach ($config as $param) {
            // build property name
            $property = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $param))));
            // assign property with config's value
            $this->$property = $config[$param];
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
                // no mandatory mandatory param provided
                throw new InvalidArgumentException("Expects an array with key: '$param'");
            }
        }

        // check for unknown params
        foreach (array_keys($config) as $param) {
            if (!array_key_exists($param, $this->knownConfigParams)) {
                // unknown param provided
                throw new InvalidArgumentException("Unrecognized key: '$param'");
            }
        }

        // all OK
        return true;
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
    public function getSocialId()
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
    public function getSocialPage()
    {
        return $this->getUserAttribute(static::ATTRIBUTE_PAGE);
    }

    /**
     * Get url of user's avatar or null if it is not set
     *
     * @return string|null
     */
    public function getAvatar()
    {
        return $this->getUserAttribute(static::ATTRIBUTE_AVATAR);
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
        $result = $this->getUserAttribute(static::ATTRIBUTE_EMAIL);

        if (!empty($result)) {
            return date('d.m.Y', strtotime($result));
        }

        return $result;
    }

    /**
     * Return name of auth provider
     *
     * @return string
     */
    public function getProvider()
    {
        return $this->provider;
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
     * Get authentication url
     *
     * @return string
     */
    public function getAuthUrl()
    {
        $config = $this->getAuthUrlComponents();

        return $config['auth_url'] . '?' . urldecode(http_build_query($config['auth_params']));
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
