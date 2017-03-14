<?php
/* (c) Anton Medvedev <anton@medv.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Deployer\Collection;

class Collection implements CollectionInterface, \Countable
{
    /**
     * @var array
     */
    private $collection = [];

    /**
     * Collection constructor.
     * @param array $collection
     */
    public function __construct(array $collection = [])
    {
        $this->collection = $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        if ($this->has($name)) {
            return $this->collection[$name];
        } else {
            return $this->throwNotFound($name);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return array_key_exists($name, $this->collection);
    }

    /**
     * {@inheritdoc}
     */
    public function set($name, $object)
    {
        $this->collection[$name] = $object;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->collection);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->collection[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->collection);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return iterator_to_array($this);
    }

    /**
     * @param string $name
     */
    protected function throwNotFound($name)
    {
        throw new \InvalidArgumentException("`$name` not found in collection.");
    }
}
