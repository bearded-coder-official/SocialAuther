<?php

/**
 * SocialAuther (http://socialauther.stanislasgroup.com/)
 *
 * @author: Stanislav Protasevich
 * @author: sunsingerus (https://github.com/sunsingerus)
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace SocialAuther\Adapter;

interface AdapterInterface
{
    /**
     * Authenticate and return bool result of authentication
     *
     * @return bool
     */
    public function authenticate();

    /**
     * Get authentication url
     *
     * @return string
     */
    public function getAuthenticationUrl();
}
