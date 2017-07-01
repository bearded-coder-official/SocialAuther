<?php

/**
 * SocialAuther
 *
 * @author: sunsingerus (https://github.com/sunsingerus)
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace SocialAuther\Adapter;


class AdapterFactory
{
    /**
     * @var array mapping provider name => provider class for Factory
     */
    protected static $map = array(
        // provider name => provider class
        AdapterBase::PROVIDER_FACEBOOK      => Facebook::class,
        AdapterBase::PROVIDER_GOOGLE        => Google::class,
        AdapterBase::PROVIDER_MAILRU        => Mailru::class,
        AdapterBase::PROVIDER_ODNOKLASSNIKI => Odnoklassniki::class,
        AdapterBase::PROVIDER_TWITTER       => Twitter::class,
        AdapterBase::PROVIDER_VKONTAKTE     => Vkontakte::class,
        AdapterBase::PROVIDER_YANDEX        => Yandex::class,
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
