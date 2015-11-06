<?php

namespace Gos\Bundle\NotificationBundle\Publisher;

use Gos\Bundle\NotificationBundle\Context\NotificationContextInterface;
use Gos\Bundle\NotificationBundle\Model\NotificationInterface;
use Predis\Client as PredisClient;
use Snc\RedisBundle\Client\Phpredis\Client as PhpClient;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class RedisPublisher implements PublisherInterface
{
    /**
     * @var PredisClient|PhpClient
     */
    protected $redis;

    /**
     * @var LoggerInterface|null
     */
    protected $logger;

    /**
     * RedisPublisher constructor.
     *
     * @param PredisClient|PhpClient $redis
     * @param LoggerInterface|null   $logger
     */
    public function __construct($redis, LoggerInterface $logger = null)
    {
        if(!$redis instanceof PhpClient && !$redis instanceof PredisClient){
            throw new \Exception('Bad client ' . get_class($redis));
        }

        $this->redis = $redis;
        $this->logger = null === $logger ? new NullLogger() : $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function publish($channel, NotificationInterface $notification, NotificationContextInterface $context = null)
    {
        $this->logger->info(sprintf(
            'push %s into %s',
            $notification->getTitle(),
            $channel
        ), $notification->toArray());

        $data = [];
        $data['notification'] = $notification;

        if (null !== $context) {
            $data['context'] = $context;
        }

        $message = json_encode($data);

        $this->redis->publish($channel, $message);
    }
}
