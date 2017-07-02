<?php

/**
 * SocialAuther
 *
 * @author: sunsingerus (https://github.com/sunsingerus)
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace SocialAuther\Provider;


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

    public static function create($provider, $config)
    {
        if (isset(static::$map[$provider])) {
            $class = static::$map[$provider];
            return new $class($config);
        }

        return null;
    }
}
