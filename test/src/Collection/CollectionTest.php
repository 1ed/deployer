<?php
/* (c) Anton Medvedev <anton@medv.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Deployer\Collection;

use Deployer\Server\EnvironmentCollection;
use Deployer\Server\ServerCollection;
use Deployer\Task\TaskCollection;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    public static function collections()
    {
        return [
            [new Collection()],
            [new TaskCollection()],
            [new ServerCollection()],
            [new EnvironmentCollection()],
        ];
    }

    /**
     * @dataProvider collections
     */
    public function testCollection($collection)
    {
        $this->assertInstanceOf('Deployer\Collection\CollectionInterface', $collection);

        $object = new \stdClass();
        $collection->set('object', $object);

        $this->assertTrue($collection->has('object'));
        $this->assertEquals($object, $collection->get('object'));

        $this->assertInstanceOf('Traversable', $collection);

        $traversable = false;
        foreach ($collection as $i) {
            $traversable = $i === $object;
        }

        $this->assertTrue($traversable, 'Collection does not traversable.');
    }

    /**
     * @dataProvider collections
     * @depends      testCollection
     */
    public function testException($collection)
    {
        $class = explode('\\', get_class($collection));
        $class = end($class);
        $name  = 'unexpected';

        $this->setExpectedException(\RuntimeException::class, "Object `$name` does not exist in $class.");
        $collection->get('unexpected');
    }

    public function testArrayAccess()
    {
        $collection = new Collection();

        $collection['key'] = 'value';
        $this->assertEquals('value', $collection['key']);

        $this->assertTrue(isset($collection['key']));

        unset($collection['key']);
        $this->assertFalse(isset($collection['key']));
    }
}
