PNCTL Event Loop Emitter
========================

Brings PNCTL event to event loop.

Install
-------

```cmd
composer require gos/pnctl-event-loop-emitter
```

Usage
-----

```php
use React\EventLoop\Factory;

$loop = Factory::create();

$pcntl->on(SIGTERM, function () use ($loop) {
	//do something
	
	$loop->stop();
});

$pcntl->on(SIGINT, function () use ($loop) {
	//do something
	
	$loop->stop();
});

$loop->run();
```

Example
-------

Handle double CTRL+C

```php
use React\EventLoop\Factory;

$loop = Factory::create();

$pnctlEmitter->on(SIGINT, function () use ($loop) {
	$this->logger->notice('Press CTLR+C again to stop the server');

    if (SIGINT === pcntl_sigtimedwait([SIGINT], $siginfo, 5)) {
        $this->logger->notice('Stopping server ...');

        //Do your stuff to stop the server

        $loop->stop();

        $this->logger->notice('Server stopped !');
    } else {
    	$this->logger->notice('CTLR+C not pressed, continue to run normally');
    }
});

$loop->run();
```

