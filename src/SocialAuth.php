<?php

/**
 * SocialAuther (http://socialauther.stanislasgroup.com/)
 *
 * @author: Stanislav Protasevich
 * @author: sunsingerus (https://github.com/sunsingerus)
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace SocialAuth;

use SocialAuth\Provider\AuthProviderEnum;
use SocialAuth\Provider\AuthProviderInterface;
use SocialAuth\Provider\AuthProviderFactory;
use SocialAuth\Exception\InvalidArgumentException;

class SocialAuth implements AuthProviderEnum
{
    /**
     * Instance of Provider
     *
     * @var AuthProviderInterface
     */
    protected $provider = null;

    /**
     * Array of providers' configurations like
     * array(
     *      AuthProviderBase::PROVIDER_VKONTAKTE => array(
     *          'client_id'     => '6097024',
     *          'client_secret' => 'Fzo6IqM8fMczAXycXpFl',
     *          'redirect_uri'  => 'http://localhost/examples/auth.php?provider=' . AuthProviderBase::PROVIDER_VKONTAKTE,
     *      ),
     * );
     *
     * @var array
     */
    protected $config = null;

    /**
     * SocialAuther constructor.
     *
     * @param AuthProviderInterface|array $mixed either instance of Provider or Config arrays
     */
    public function __construct($mixed = null)
    {
        if ($mixed instanceof AuthProviderInterface) {
            $this->provider = $mixed;
            return;
        }

        if (is_array($mixed)) {
            $this->config = $mixed;
            AuthProviderFactory::init($this->config);
            return;
        }

        throw new InvalidArgumentException("Unknown param for constructor " . __CLASS__);
    }

    /**
     * Setter method
     *
     * @param AuthProviderInterface $provider Provider instance
     */
    public function setProvider(AuthProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Getter method
     *
     * @return array|null|AuthProviderInterface
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Get list of available Providers
     *
     * @return array list of available Providers as array of AuthProviderInterface
     */
    public function getProviders()
    {
        return AuthProviderFactory::providers();
    }

    /**
     * Get info about all available providers
     *
     * @return array (
     *  provider_name_id => array(
     *      'name' => NAME,
     *      'url'  => URL,
     *  )
     * )
     */
    public function getProvidersInfo()
    {
        $info = array();
        foreach ($this->getProviders() as $provider) {
            $info[$provider->getProvider()] = array(
                'name' => $provider->getName(),
                'url'  => $provider->getAuthenticationUrl(),
            );
        }

        return $info;
    }

    /**
     * Init provider to be used by its name/id (One of AuthProviderInterface::PROVIDER_*)
     *
     * @param null $provider
     * @return bool success/failure
     */
    public function initProvider($provider = null)
    {
        $provider = AuthProviderFactory::provider($provider);

        if (empty($provider)) {
            return false;
        }

        $this->provider = $provider;
        return true;
    }

    /**
     * Assign providers' config
     *
     * @param array $config array of Providers' config
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
        AuthProviderFactory::init($this->config);
    }

    /**
     * Call either local or provider's method
     *
     * @param $method
     * @param $params
     * @return mixed
     */
    public function __call($method, $params)
    {
        if (method_exists($this->provider, $method)) {
            return call_user_func_array([$this->provider, $method], $params);
        }

        throw new InvalidArgumentException("Unknown method " . __CLASS__ . "->$method()");
    }


}
