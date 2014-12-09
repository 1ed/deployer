<?php

\Deployer\Deployer::get()->servers->set('main', new \Deployer\Server\Local());
\Deployer\Deployer::get()->servers->set('second', new \Deployer\Server\Local());

task('test', function () {
});

task('do', function () {
    \Deployer\Deployer::get()->getOutput()->writeln('Just do it!');
})->onlyOn(['second']);

task('after', function () {
});

after('do', 'after');

task('2', ['test', 'do']);

task('before', function () {
})->once();

before('2', 'before');

