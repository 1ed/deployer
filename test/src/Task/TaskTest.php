<?php
/* (c) Anton Medvedev <anton@medv.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Deployer\Task;

use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function testTask()
    {
        $mock = $this->getMockBuilder('stdClass')->setMethods(['callback'])->getMock();
        $mock->expects($this->exactly(1))
            ->method('callback');

        $task = new Task('task_name', function () use ($mock) {
            $mock->callback();
        });

        $context = $this->getMockBuilder(Context::class)->disableOriginalConstructor()->getMock();

        $task->run($context);

        $this->assertEquals('task_name', $task->getName());

        $task->desc('Task description.');
        $this->assertEquals('Task description.', $task->getDescription());

        $task->once();
        $this->assertTrue($task->isOnce());

        $task->onlyOn(['server']);
        $this->assertEquals(['server' => 0], $task->getOnlyOn());
        $this->assertTrue($task->isOnServer('server'));

        $task->onlyOn([]);
        $this->assertTrue($task->isOnServer('server'));

        $task->onlyOn('server');
        $this->assertEquals(['server' => 0], $task->getOnlyOn());
        $this->assertTrue($task->isOnServer('server'));

        $task->onlyOn();
        $this->assertTrue($task->isOnServer('server'));

        $task->setPrivate();
        $this->assertTrue($task->isPrivate());

        $task->onlyForStage(['staging', 'production']);
        $this->assertEquals(['staging' => 0, 'production' => 1], $task->getOnlyForStage());
        $this->assertTrue($task->isForStages(['staging']));
        $this->assertTrue($task->isForStages(['production']));
        $this->assertTrue($task->isForStages(['staging', 'production']));

        $task->onlyForStage('staging', 'production');
        $this->assertEquals(['staging' => 0, 'production' => 1], $task->getOnlyForStage());
        $this->assertTrue($task->isForStages(['staging']));
        $this->assertTrue($task->isForStages(['production']));
        $this->assertTrue($task->isForStages(['staging', 'production']));

        $task->onlyForStage();
        $this->assertTrue($task->isForStages('anything'));
    }

    public function testInit()
    {
        $context = $this->getMockBuilder(Context::class)->disableOriginalConstructor()->getMock();

        // Test create task with [$object, 'method']
        $mock1 = $this->getMockBuilder('stdClass')->setMethods(['callback'])->getMock();
        $mock1->expects($this->once())->method('callback');
        $task1 = new Task('task1', [$mock1, 'callback']);
        $task1->run($context);

        // Test create task with anonymous functions
        $mock2 = $this->getMockBuilder('stdClass')->setMethods(['callback'])->getMock();
        $mock2->expects($this->once())->method('callback');
        $task2 = new Task('task2', function () use ($mock2) {
            $mock2->callback();
        });
        $task2->run($context);

        $this->assertEquals(0, StubTask::$runned);
        $task3 = new Task('task3', new StubTask());
        $task3->run($context);
        $this->assertEquals(1, StubTask::$runned);
    }
}

/**
 * Stub class for task callable by __invoke()
 */
class StubTask
{
    public static $runned = 0;

    public function __invoke()
    {
        self::$runned++;
    }
}
