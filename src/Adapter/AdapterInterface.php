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
     * Get authentication url for presenting to the user
     *
     * @return string
     */
    public function getAuthenticationUrl();

    /**
     * Extract params for authentication from provided array
     * In most cases this provided params array would be $_GET
     *
     * @param $params array of authentication params (Ex.: $_GET)
     * @return mixed
     */
    public function getAuthenticationParams($params);

    /**
     * Authenticate and return bool result of authentication
     *
     * @param $params array of authentication params (Ex.: $_GET)
     * @return bool
     */
    public function authenticate($params);
}
