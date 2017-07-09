<?php
/**
 * SocialAuther (http://socialauther.stanislasgroup.com/)
 *
 * @author: sunsingerus (https://github.com/sunsingerus)
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace SocialAuth\Provider;


interface AuthProviderEnum
{
    const PROVIDER_FACEBOOK      = 'fb';
    const PROVIDER_GOOGLE        = 'google';
    const PROVIDER_MAILRU        = 'mailru';
    const PROVIDER_ODNOKLASSNIKI = 'ok';
    const PROVIDER_TWITTER       = 'twitter';
    const PROVIDER_VKONTAKTE     = 'vk';
    const PROVIDER_YANDEX        = 'yandex';


    const ATTRIBUTE_ID         = 'id';
    const ATTRIBUTE_EMAIL      = 'email';
    const ATTRIBUTE_NAME       = 'name';
    const ATTRIBUTE_PAGE_URL   = 'page_url';
    const ATTRIBUTE_AVATAR_URL = 'avatar';
    const ATTRIBUTE_SEX        = 'sex';
    const ATTRIBUTE_BIRTHDAY   = 'birthday';
}