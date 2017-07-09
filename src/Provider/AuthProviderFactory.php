<?php

/**
 * SocialAuther
 *
 * @author: sunsingerus (https://github.com/sunsingerus)
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace SocialAuth\Provider;


class AuthProviderFactory
{
    /**
     * @var array mapping provider name => provider class for Factory
     */
    protected static $map = array(
        // provider name => provider class
        AuthProviderBase::PROVIDER_FACEBOOK      => Facebook::class,
        AuthProviderBase::PROVIDER_GOOGLE        => Google::class,
        AuthProviderBase::PROVIDER_MAILRU        => Mailru::class,
        AuthProviderBase::PROVIDER_ODNOKLASSNIKI => Odnoklassniki::class,
        AuthProviderBase::PROVIDER_TWITTER       => Twitter::class,
        AuthProviderBase::PROVIDER_VKONTAKTE     => Vkontakte::class,
        AuthProviderBase::PROVIDER_YANDEX        => Yandex::class,
    );

    /**
     * @var array of all available providers
     */
    protected static $providers = array();

    /**
     * Instantiate all available providers into static array
     *
     * @param $config array of provider configurations
     * @return array instances of all available providers
     */
    public static function init(array $config = array())
    {
        // build array of providers
        static::$providers = array();
        foreach ($config as $provider => $settings) {
            // config can contain whatever provider names, so need to check its valid
            if (AuthProviderBase::isProviderValid($provider)) {
                static::$providers[$provider] = static::create($provider, $settings);
            }
        }

        return static::$providers;
    }

    /**
     * Get array of instances of all available providers
     *
     * @param $config array of provider configurations
     * @return array instances of all available providers
     */
    public static function providers($config = null)
    {
        if (is_null($config)) {
            // get previously built array of providers
            return static::$providers;
        }

        // init array of providers from config
        return static::init($config);
    }

    /**
     * Get provider by its name/id (One of AuthProviderInterface::PROVIDER_*)
     *
     * @param null $provider name/id of the provider (One of AuthProviderInterface::PROVIDER_*)
     * @return mixed|null provider instance, if available
     */
    public static function provider($provider = null)
    {
        if (empty($provider)) {
            return null;
        }

        if (array_key_exists($provider, static::$providers)) {
            // known provider
            return static::$providers[$provider];
        }
        
        return null;
    }

    /**
     * Create instance of provider by its name/id (One of AuthProviderInterface::PROVIDER_*)
     *
     * @param $provider provider name/id (One of AuthProviderInterface::PROVIDER_*)
     * @param array $config configuration for provider to be created
     * @return mixed|null provider, if able to instansiate
     */
    public static function create($provider, array $config = array())
    {
        if (isset(static::$map[$provider])) {
            $class = static::$map[$provider];
            return new $class($config);
        }

        return null;
    }
}
