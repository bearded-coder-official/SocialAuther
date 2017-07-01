<?php

/**
 * SocialAuther (http://socialauther.stanislasgroup.com/)
 *
 * @author: Stanislav Protasevich
 * @author: sunsingerus (https://github.com/sunsingerus)
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

require_once __DIR__.'/../vendor/autoload.php';

use SocialAuther\Adapter\AdapterBase;
use SocialAuther\Adapter\AdapterFactory;
use SocialAuther\SocialAuther;

// Social Auth settings
$config = array(
    AdapterBase::PROVIDER_VKONTAKTE => array(
        'client_id'     => '6097024',
        'client_secret' => 'Fzo6IqM8fMczAXycXpFl',
        'redirect_uri'  => 'http://localhost/examples/auth.php?provider=' . AdapterBase::PROVIDER_VKONTAKTE,
    ),
);

// build array of providers
$providers = array();
foreach ($config as $provider => $settings) {
    $providers[$provider] = AdapterFactory::create($provider, $settings);
}

if (isset($_GET['code'])) {

    $provider = isset($_GET['provider']) ? $_GET['provider'] : null;
    if (!array_key_exists($provider, $providers)) {
        return;
    }

    $auther = new SocialAuther($providers[$provider]);

    if ($auther->authenticate()) {
        if (!is_null($auther->getId()))
            echo "User ID: " . $auther->getId() . '<br />';

        if (!is_null($auther->getName()))
            echo "Name: " . $auther->getName() . '<br />';

        if (!is_null($auther->getEmail()))
            echo "Email: " . $auther->getEmail() . '<br />';

        if (!is_null($auther->getPageUrl()))
            echo "Page URL: " . $auther->getPageUrl() . '<br />';

        if (!is_null($auther->getSex()))
            echo "Gender: " . $auther->getSex() . '<br />';

        if (!is_null($auther->getBirthday()))
            echo "Birthday: " . $auther->getBirthday() . '<br />';

        // аватар пользователя
        if (!is_null($auther->getAvatarUrl()))
            echo '<img src="' . $auther->getAvatarUrl() . '" />'; echo "<br />";
    }
} else {
    foreach ($providers as $provider) {
        echo '<p><a href="' . $provider->getAuthenticationUrl() . '">Auth via ' . $provider->getProvider() . '</a></p>';
    }
}
