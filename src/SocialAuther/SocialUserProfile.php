<?php
/**
 * SocialUserProfile
 *
 * @author Andrey Izman <cyborgcms@gmail.com>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace SocialAuther;

use SocialAuther\Adapter\AdapterInterface;

class SocialUserProfile implements \Iterator
{
    /**
     * Adapter manager
     *
     * @var AdapterInterface
     */
    protected $adapter = null;

    /**
     * Cached data
     *
     * @var array
     */
    protected $cache = array();


    /**
     * Allowed user data fields
     *
     * @var array
     */
    protected $fields = array(
        'id',
        'name',
        'firstName',
        'secondName',
        'sex',
        'email',
        'page',
        'image',
        'phone',
        'location',
        'country',
        'city',
        'birthDate',
        'birthDay',
        'birthMonth',
        'birthYear'
    );


    /**
     * Constructor.
     *
     * @param AdapterInterface $adapter
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($adapter)
    {
        if ($adapter instanceof AdapterInterface) {
            $this->adapter = $adapter;
        } else {
            throw new Exception\InvalidArgumentException(
                    'SocialUserProfile only expects instance of the ' .
                    'SocialAuther\Adapter\AdapterInterface type.'
            );
        }
    }

    /**
     * Magic method to getting user data fields as properties
     *
     * @param string $name Variable name
     * @throws \LogicException
     * @return mixed
     */
    public function __get($name)
    {
        if (in_array($name, $this->fields))
        {
            if (array_key_exists($name, $this->cache)) {
                return $this->cache[$name];
            }

            return $this->cache[$name] = call_user_func(array($this->adapter, 'get'.ucfirst($name)));
        }
        elseif ($name === 'provider') {
            return $this->adapter->getProvider();
        }

        throw new \LogicException("Property $name is not defined in " . __CLASS__);
    }

    /**
     * Rewind user data
     * @see Iterator::rewind()
     */
    public function rewind()
    {
        reset($this->fields);
    }

    /**
     * Get current value
     * @see Iterator::current()
     */
    public function current()
    {
        $field = current($this->fields);

        if (array_key_exists($field, $this->cache)) {
            return $this->cache[$field];
        }

        return $this->cache[$field] = call_user_func(array($this->adapter, 'get'.ucfirst($field)));
    }

    /**
     * Get current key
     * @see Iterator::key()
     */
    public function key()
    {
        return current($this->fields);
    }

    /**
     * Get next value
     * @see Iterator::next()
     */
    public function next()
    {
        $field = next($this->fields);

        if (!$field)
            return false;

        if (array_key_exists($field, $this->cache)) {
            return $this->cache[$field];
        }

        return $this->cache[$field] = call_user_func(array($this->adapter, 'get'.ucfirst($field)));
    }

    /**
     * Validation
     * @see Iterator::valid()
     */
    public function valid()
    {
        $key = current($this->fields);
        return is_string($key);
    }

}
