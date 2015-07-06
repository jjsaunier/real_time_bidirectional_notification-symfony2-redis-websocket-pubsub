<?php

namespace Prophet777\MoonWalk\src\Topic;

use Gos\Bundle\WebSocketBundle\Client\ClientManipulatorInterface;
use Gos\Bundle\WebSocketBundle\Router\WampRequest;
use Gos\Bundle\WebSocketBundle\Router\WampRouter;
use Gos\Bundle\WebSocketBundle\Topic\ConnectionPeriodicTimer;
use Gos\Bundle\WebSocketBundle\Topic\TopicInterface;
use Gos\Bundle\WebSocketBundle\Topic\TopicPeriodicTimerInterface;
use Gos\Bundle\WebSocketBundle\Topic\TopicPeriodicTimerTrait;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;

class WatcherTopic implements TopicInterface, TopicPeriodicTimerInterface
{
    use TopicPeriodicTimerTrait;

    /**
     * @var ClientManipulatorInterface
     */
    protected $clientManipulator;

    /**
     * @var WampRouter
     */
    protected $wampRouter;

    /**
     * @param ClientManipulatorInterface $clientManipulator
     * @param WampRouter                 $wampRouter
     */
    public function __construct(ClientManipulatorInterface $clientManipulator, WampRouter $wampRouter)
    {
        $this->clientManipulator = $clientManipulator;
        $this->wampRouter = $wampRouter;
    }

    /**
     * @param Topic $topic
     *
     * @return array
     */
    public function registerPeriodicTimer(Topic $topic)
    {
//        $this->periodicTimer->addPeriodicTimer($this, 'hello', 2, function() use ($topic){
//            $topic->broadcast('hello world');
//        });
    }

    /**
     * @param  ConnectionInterface $connection
     * @param  Topic               $topic
     * @param WampRequest          $request
     */
    public function onSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
    {
//        $connection->event($this->wampRouter->generate('watcher_notify'), 'message');

        /** @var ConnectionPeriodicTimer $topicTimer */
//        $topicTimer = $connection->PeriodicTimer;

        //Add periodic timer
//        $topicTimer->addPeriodicTimer('hello', 2, function() use ($topic, $connection) {
//            dump('hello2');
//            $connection->event($topic->getId(), ['msg' => 'hello world']);
//        });

//        $user = $this->clientManipulator->getClient($connection);
    }

    /**
     * @param  ConnectionInterface $connection
     * @param  Topic               $topic
     * @param WampRequest          $request
     */
    public function onUnSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
    {
    }

    /**
     * @param  ConnectionInterface $connection
     * @param  Topic               $topic
     * @param WampRequest          $request
     * @param                      $event
     * @param  array               $exclude
     * @param  array               $eligible
     */
    public function onPublish(ConnectionInterface $connection, Topic $topic, WampRequest $request, $event, array $exclude, array $eligible)
    {
        $topic->broadcast($event, $exclude, $eligible);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'prophet777.watcher.topic';
    }
}