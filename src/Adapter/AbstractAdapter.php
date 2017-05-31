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
    protected $fieldsInConfig = array(
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
        if ($this->verifyConfig($config)) {
            foreach ($config as $param) {
                $property = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $param))));
                $this->$property = $config[$param];
            }
        }
    }

    /**
     * Check whether configuration is valid
     *
     * @param array $config
     * @return bool
     */
    public function verifyConfig(array $config)
    {
        // Check for mandatory params presence
        foreach ($this->fieldsInConfig as $param) {
            if (!array_key_exists($param, $config)) {
                // Mandatory param is absent
                throw new InvalidArgumentException("Expects an array with key: '$param'");
            }
        }

        // Check for extra params provided
        foreach (array_keys($config) as $param) {
            if (!array_key_exists($param, $this->fieldsInConfig)) {
                // Extra params
                throw new InvalidArgumentException("Unrecognized key: '$param'");
            }
        }

        // All OK
        return true;
    }

    /**
     * Get user social id or null if it is not set
     *
     * @return string|null
     */
    public function getSocialId()
    {
        $result = null;

        if (isset($this->userInfo[$this->fieldsMap['socialId']])) {
            $result = $this->userInfo[$this->fieldsMap['socialId']];
        }

        return $result;
    }

    /**
     * Get user email or null if it is not set
     *
     * @return string|null
     */
    public function getEmail()
    {
        $result = null;

        if (isset($this->userInfo[$this->fieldsMap['email']])) {
            $result = $this->userInfo[$this->fieldsMap['email']];
        }

        return $result;
    }

    /**
     * Get user name or null if it is not set
     *
     * @return string|null
     */
    public function getName()
    {
        $result = null;

        if (isset($this->userInfo[$this->fieldsMap['name']])) {
            $result = $this->userInfo[$this->fieldsMap['name']];
        }

        return $result;
    }

    /**
     * Get user social page url or null if it is not set
     * @return string|null
     */
    public function getSocialPage()
    {
        $result = null;

        if (isset($this->userInfo[$this->fieldsMap['socialPage']])) {
            $result = $this->userInfo[$this->fieldsMap['socialPage']];
        }

        return $result;
    }

    /**
     * Get url of user's avatar or null if it is not set
     *
     * @return string|null
     */
    public function getAvatar()
    {
        $result = null;

        if (isset($this->userInfo[$this->fieldsMap['avatar']])) {
            $result = $this->userInfo[$this->fieldsMap['avatar']];
        }

        return $result;
    }

    /**
     * Get user sex or null if it is not set
     *
     * @return string|null
     */
    public function getSex()
    {
        $result = null;

        if (isset($this->userInfo[$this->fieldsMap['sex']])) {
            $result = $this->userInfo[$this->fieldsMap['sex']];
        }

        return $result;
    }

    /**
     * Get user birthday in format dd.mm.YYYY or null if it is not set
     *
     * @return string|null
     */
    public function getBirthday()
    {
        $result = null;

        if (isset($this->userInfo[$this->fieldsMap['birthday']])) {
            $result = date('d.m.Y', strtotime($this->userInfo[$this->fieldsMap['birthday']]));
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
