<?php

namespace Gos\Bundle\NotificationBundle;

use Gos\Bundle\NotificationBundle\Context\PusherIdentity;
use Gos\Bundle\NotificationBundle\Model\NotificationInterface;
use Gos\Bundle\NotificationBundle\Model\TransportNotification;
use Predis\Client;
use Psr\Log\LoggerInterface;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
class NotificationCenter
{
    /**
     * @var Client
     */
    protected $redis;

    /**
     * @var LoggerInterface|null
     */
    protected $logger;

    /**
     * @param Client          $redis
     * @param LoggerInterface $logger
     */
    public function __construct(Client $redis, LoggerInterface $logger = null)
    {
        $this->redis = $redis;
        $this->logger = $logger;
    }

    /**
     * @param string $channel
     * @param int $start
     * @param int $end
     *
     * @return NotificationInterface[]|array
     */
    public function fetch($channel, $start, $end)
    {
        //@TODO: Create fetch feature
        return array();
    }

    /**
     * @param string                      $channel
     * @param NotificationInterface $notification
     * @param string|null                  $pusherIdentifier.
     */
    public function push($channel, NotificationInterface $notification, PusherIdentity $pusherIdentity = null)
    {
        if(null !== $this->logger){
            $this->logger->info(sprintf(
                'push %s into %s',
                $notification->getTitle(),
                $channel
            ), $notification->toArray());
        }

        $wrappedNotification = new TransportNotification();
        $wrappedNotification->wrap($notification);
        $wrappedNotification->setPusherIdentity($pusherIdentity);

        $this->redis->publish($channel, json_encode($wrappedNotification));
    }
}