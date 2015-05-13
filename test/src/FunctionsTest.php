<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Deployer;

use Deployer\Console\Application;
use Deployer\Server\Environment;
use Deployer\Task\Context;

class FunctionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Deployer
     */
    private $deployer;

    /**
     * @var Application
     */
    private $console;

    protected function setUp()
    {
        $this->console = new Application();

        $input = $this->getMock('Symfony\Component\Console\Input\InputInterface');
        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
        $server = $this->getMockBuilder('Deployer\Server\ServerInterface')->disableOriginalConstructor()->getMock();
        $env = new Environment();

        $this->deployer = new Deployer($this->console, $input, $output);
        Context::push(new Context($server, $env, $input, $output));
    }

    protected function tearDown()
    {
        unset($this->deployer);

        $this->deployer = null;
    }

    public function testServer()
    {
        server('main', 'domain.com', 22);

        $server = $this->deployer->servers->get('main');
        $env = $this->deployer->environments->get('main');

        $this->assertInstanceOf('Deployer\Server\ServerInterface', $server);
        $this->assertInstanceOf('Deployer\Server\Environment', $env);
    }

    public function testLocalServer()
    {
        localServer('main')->env('deploy_path', __DIR__ . '/localhost');

        $server = $this->deployer->servers->get('main');
        $env = $this->deployer->environments->get('main');

        $this->assertInstanceOf('Deployer\Server\ServerInterface', $server);
        $this->assertInstanceOf('Deployer\Server\Environment', $env);
        $this->assertEquals(__DIR__ . '/localhost', $env->get('deploy_path'));
    }

    public function testServerList()
    {
        serverList(__DIR__ . '/../fixture/servers.yml');

        foreach (['production', 'beta', 'test'] as $stage) {
            $server = $this->deployer->servers->get($stage);
            $env = $this->deployer->environments->get($stage);

            $this->assertInstanceOf('Deployer\Server\ServerInterface', $server);
            $this->assertInstanceOf('Deployer\Server\Environment', $env);

            $this->assertEquals('/home', $env->get('deploy_path'));
        }
    }

    public function testTask()
    {
        task('task', function () {});

        $task = $this->deployer->tasks->get('task');
        $this->assertInstanceOf('Deployer\Task\Task', $task);

        task('group', ['task']);
        $task = $this->deployer->tasks->get('group');
        $this->assertInstanceOf('Deployer\Task\GroupTask', $task);

        $this->setExpectedException('InvalidArgumentException', 'Task should be an closure or array of other tasks.');
        task('wrong', 'thing');
    }

    public function testBefore()
    {
        task('main', function () {});
        task('before', function () {});
        before('main', 'before');

        $mainScenario = $this->deployer->scenarios->get('main');
        $this->assertInstanceOf('Deployer\Task\Scenario\Scenario', $mainScenario);
        $this->assertEquals(['before', 'main'], $mainScenario->getTasks());
    }

    public function testAfter()
    {
        task('main', function () {});
        task('after', function () {});
        after('main', 'after');

        $mainScenario = $this->deployer->scenarios->get('main');
        $this->assertInstanceOf('Deployer\Task\Scenario\Scenario', $mainScenario);
        $this->assertEquals(['main', 'after'], $mainScenario->getTasks());
    }

    public function testRunLocally() {
        $output = runLocally('echo "hello"');

        $this->assertInstanceOf('Deployer\Type\Result', $output);
        $this->assertEquals('hello', (string)$output);
    }
}
