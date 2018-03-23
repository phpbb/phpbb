<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bridge\Twig\NodeVisitor;

/**
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 */
class Scope
{
    private $parent;
    private $data = array();
    private $left = false;

    public function __construct(Scope $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * Opens a new child scope.
     *
     * @return self
     */
    public function enter()
    {
        return new self($this);
    }

    /**
     * Closes current scope and returns parent one.
     *
     * @return self|null
     */
    public function leave()
    {
        $this->left = true;

        return $this->parent;
    }

    /**
     * Stores data into current scope.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     *
     * @throws \LogicException
     */
    public function set($key, $value)
    {
        if ($this->left) {
            throw new \LogicException('Left scope is not mutable.');
        }

        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Tests if a data is visible from current scope.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        if (array_key_exists($key, $this->data)) {
            return true;
        }

        if (null === $this->parent) {
            return false;
        }

        return $this->parent->has($key);
    }

    /**
     * Returns data visible from current scope.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        if (null === $this->parent) {
            return $default;
        }

        return $this->parent->get($key, $default);
    }
}
