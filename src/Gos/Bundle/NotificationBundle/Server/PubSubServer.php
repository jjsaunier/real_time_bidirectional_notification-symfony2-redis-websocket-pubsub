<?php

namespace Gos\Bundle\NotificationBundle\Server;

use Evenement\EventEmitter;
use Gos\Bundle\NotificationBundle\Exception\NotificationServerException;
use Gos\Bundle\NotificationBundle\Model\Message\Message;
use Gos\Bundle\NotificationBundle\Model\Message\PatternMessage;
use Gos\Bundle\NotificationBundle\Router\Dumper\RedisDumper;
use Gos\Bundle\WebSocketBundle\Server\Type\ServerInterface;
use Gos\Component\PnctlEventLoopEmitter\PnctlEmitter;
use Predis\Async\Client;
use Predis\Response\Error;
use Psr\Log\LoggerInterface;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Log\NullLogger;
use Predis\Async\PubSub\Consumer;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
class PubSubServer implements ServerInterface
{
    /** @var  LoopInterface */
    protected $loop;

    /** @var  Client */
    protected $client;

    /** @var LoggerInterface */
    protected $logger;

    /** @var  EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var RedisDumper */
    protected $redisDumper;

    /** @var  Consumer */
    protected $pubSub;

    /** @var ServerNotificationProcessorInterface  */
    protected $processor;

    /** @var  bool */
    protected $debug;

    /**
     * @param EventDispatcherInterface             $eventDispatcher
     * @param array                                $pubSubConfig
     * @param RedisDumper                          $redisDumper
     * @param ServerNotificationProcessorInterface $processor
     * @param bool                                 $debug
     * @param LoggerInterface                      $logger
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RedisDumper $redisDumper,
        ServerNotificationProcessorInterface $processor,
        $debug,
        LoggerInterface $logger = null
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->redisDumper = $redisDumper;
        $this->processor = $processor;
        $this->debug = $debug;
        $this->logger = null === $logger ? new NullLogger() : $logger;
    }

    /**
     * @return array
     */
    protected function getSubscriptions()
    {
        $subscription = $this->redisDumper->dump();

        if (!empty($subscription['subscribe'])) {
            $this->logger->info(sprintf(
                'Listening topics %s',
                implode(', ', $subscription['subscribe'])
            ));
        }

        if (!empty($subscription['psubscribe'])) {
            $this->logger->info(sprintf(
                'Listening pattern %s',
                implode(', ', $subscription['psubscribe'])
            ));
        }

        return $subscription;
    }

    /**
     * {@inheritdoc}
     */
    public function launch($host, $port, $profile, array $options = [])
    {
        $this->logger->info('Starting redis pubsub');

        $this->loop = Factory::create();

        if (extension_loaded('pcntl')) {
            $this->handlePnctlEvent();
        }

        $dns = sprintf('tcp://%s:%s', $host, $port);

        $this->client = new Client($dns, $this->loop);

        $dispatcher = new EventEmitter();
        $dispatcher->on('notification', $this->processor);

        if (true === $profile) {
            $this->loop->addPeriodicTimer(5, function () {
                $this->logger->info('Memory usage : ' . round((memory_get_usage() / (1024 * 1024)), 4) . 'Mo');
            });
        }

        $subscriptions = $this->getSubscriptions();

        $timeoutTimer = $this->loop->addTimer(5, function(){
            $this->logger->critical('Redis connection timed out');
            $this->loop->stop();
        });

        $this->client->connect(function ($client) use ($dispatcher, $subscriptions, $timeoutTimer) {
            $timeoutTimer->cancel();

            $this->pubSub = $client->pubSubLoop($subscriptions, function ($event, $pubsub) use ($dispatcher, $timeoutTimer) {

                if($event instanceof Error){
                    $this->logger->critical($event->getMessage());
                    return;
                }

                if ($event->payload === 'quit') {
                    $this->stop();
                }

                if (!in_array($event->kind, array(Consumer::MESSAGE, Consumer::PMESSAGE))) {
                    throw new NotificationServerException(sprintf(
                        'Unsupported message type %s given, supported [%]',
                        $event->kind,
                        [Consumer::MESSAGE, Consumer::PMESSAGE, Consumer::PSUBSCRIBE, Consumer::SUBSCRIBE, Consumer::UNSUBSCRIBE]
                    ));
                }

                if (in_array($event->kind, [Consumer::MESSAGE, Consumer::PMESSAGE])) {
                    if ($event->kind === Consumer::MESSAGE) {
                        $message = new Message(
                            $event->kind,
                            $event->channel,
                            $event->payload
                        );
                    }

                    if ($event->kind === Consumer::PMESSAGE) {
                        $message = new PatternMessage(
                            $event->kind,
                            $event->pattern,
                            $event->channel,
                            $event->payload
                        );
                    }

                    $dispatcher->emit('notification', [$message]);
                }
            });
        });

        $this->logger->info(sprintf(
            'Launching %s on %s',
            $this->getName(),
            $host . ':' . $port
        ));

        $this->loop->run();
    }

    protected function stop()
    {
        if (null !== $this->pubSub) {
            $this->pubSub->quit();
        }

        $this->client->getConnection()->disconnect();
        $this->loop->stop();
    }

    protected function handlePnctlEvent()
    {
        $pnctlEmitter = new PnctlEmitter($this->loop);

        $pnctlEmitter->on(SIGTERM, function () {
            $this->logger->notice('Stopping server ...');
            $this->stop();
            $this->logger->notice('Server stopped !');
        });

        $pnctlEmitter->on(SIGINT, function () {
            $this->logger->notice('Press CTLR+C again to stop the server');

            if (SIGINT === pcntl_sigtimedwait([SIGINT], $siginfo, 5)) {
                $this->logger->notice('Stopping server ...');
                $this->stop();
                $this->logger->notice('Server stopped !');
            } else {
                $this->logger->notice('CTLR+C not pressed, continue to run normally');
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'PubSub';
    }
}
