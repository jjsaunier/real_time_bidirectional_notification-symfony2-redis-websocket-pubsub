<?php

namespace Gos\Bundle\NotificationBundle\Consumer;

use Gos\Bundle\NotificationBundle\Context\TransportContext;
use Gos\Bundle\NotificationBundle\Model\Notification;
use Gos\Bundle\NotificationBundle\Model\Message\MessageInterface;
use Gos\Bundle\WebSocketBundle\Client\ClientStorage;
use Gos\Component\WebSocketClient\Wamp\Client;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
class UserNotificationConsumer extends NotificationConsumer
{
    /**
     * @var ClientStorage
     */
    protected $clientStorage;

    /**
     * @var \Predis\Client
     */
    protected $redisClientStorage;

    /**
     * @param ClientStorage  $clientStorage
     * @param \Predis\Client $redisClientStorage
     */
    public function __construct(ClientStorage $clientStorage, \Predis\Client $redisClientStorage)
    {
        $this->clientStorage = $clientStorage;
        $this->redisClientStorage = $redisClientStorage;
    }

    /**
     * @return array
     */
    protected function subscribe()
    {
        return array(
            'pchannel' => array('notification:user:*'),
            'channel' => array()
        );
    }

    /**
     * @param Notification     $notification
     * @param MessageInterface $message
     * @param TransportContext|null $context
     *
     * @throws \Gos\Component\WebSocketClient\Exception\BadResponseException
     */
    public function process(Notification $notification, MessageInterface $message, TransportContext $context = null)
    {
        //@TODO: Auth application with pushed identity from context
        //@TODO: Websocket Client auth feature
        //@TODO: Inject server parameters through DI
        $socket = new Client('notification.dev', 1337);
        $sessionId = $socket->connect('/');

        $socket->publish(str_replace(':', '/', $message->getChannel()), json_encode($notification));
        $socket->disconnect();
    }
}