<?php
/*
 * This file is part of the Designmodo WordPress Plugin.
 *
 * (c) Designmodo Inc. <info@designmodo.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Designmodo\Qards\Utility;

/**
 * Context implements context handler for Timber.
 */
class Context implements \ArrayAccess
{

    /**
     * Context
     *
     * @var Context
     */
    private static $instance;

    /**
     * Custom part of context
     *
     * @var array
     */
    protected $container = array();

    /**
     * Constructor
     *
     * @return void
     */
    private function __construct()
    {}

    /**
     * Constructor
     *
     * @return void
     */
    private function __clone()
    {}

    /**
     * Constructor
     *
     * @return void
     */
    private function __wakeup()
    {}

    /**
     * Constructor
     *
     * @return Context
     */
    public static function getInstance()
    {
        return empty(self::$instance) ? self::$instance = new self() : self::$instance;
    }

    /**
     * Offset to set
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * Whether a offset exists
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        $context = $this->toArray();
        return isset($context[$offset]);
    }

    /**
     * Offset to unset
     *
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    /**
     * Offset to retrieve
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        $context = $this->toArray();
        return isset($context[$offset]) ? $context[$offset] : null;
    }

    /**
     * Set item
     *
     * @param mixed $name
     * @param mixed $val
     * @return void
     */
    public function set($name, $val)
    {
        $this->offsetSet($name, $val);
    }

    /**
     * Get item
     *
     * @param mixed $name
     * @return mixed
     */
    public function get($name)
    {
        return $this->offsetGet($name);
    }

    /**
     * Convert all data to array
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge(\Timber::get_context(), $this->container);
    }
}

