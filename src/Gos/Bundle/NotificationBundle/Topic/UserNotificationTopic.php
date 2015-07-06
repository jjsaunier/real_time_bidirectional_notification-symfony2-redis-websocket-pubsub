<?php

namespace Gos\Bundle\NotificationBundle\Topic;

use Gos\Bundle\WebSocketBundle\Pipeline\WampPipelineInterface;
use Gos\Bundle\WebSocketBundle\Router\WampRequest;
use Gos\Bundle\WebSocketBundle\Topic\TopicInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;

class UserNotificationTopic implements TopicInterface
{
    /**
     * @var WampPipelineInterface
     */
    protected $pipeline;

    /**
     * @param WampPipelineInterface $wampPipeline
     */
    public function __construct(WampPipelineInterface $wampPipeline)
    {
        $this->pipeline = $wampPipeline;
    }

    /**
     * @param ConnectionInterface $connection
     * @param Topic               $topic
     */
    public function onSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
    {
        //nothing
    }

    /**
     * @param ConnectionInterface $connection
     * @param Topic               $topic
     */
    public function onUnSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
    {
        //nothing
    }

    /**
     * @param ConnectionInterface $connection
     * @param Topic               $topic
     * @param string              $event
     * @param array               $exclude
     * @param array               $eligible
     */
    public function onPublish(ConnectionInterface $connection, Topic $topic, WampRequest $request, $event, array $exclude, array $eligible)
    {
        $log = '{"type":"entry","format":"monolog","file_path":"\/home\/johann\/Projects\/notification\/app\/logs\/pipeline.log","file_name":"pipeline.log","data":{"date":"2015-07-06T00:27:33+02:00","logger":"event","level":"DEBUG","message":"Notified event \"console.command\" to listener \"Symfony\\Component\\HttpKernel\\EventListener\\DebugHandlersListener::configure\"."}}';

        $pipe = $this->pipeline->pipe();

        $pipe->connection($connection)
            ->request('watcher_notify')
            ->data($log)
            ->forward(WampPipelineInterface::PUBLICATION)
            ->exclude()
            ->eligible()
        ;

        $this->pipeline->flush();

        $topic->broadcast($event, $exclude, $eligible);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'gos.notification.topic';
    }
}
