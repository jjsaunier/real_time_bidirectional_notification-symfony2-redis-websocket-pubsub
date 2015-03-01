<?php

namespace Gos\Bundle\NotificationBundle\Server;

use Gos\Bundle\NotificationBundle\Context\TransportContext;
use Gos\Bundle\NotificationBundle\Event\MessageEvent;
use Gos\Bundle\NotificationBundle\Event\MessageEvents;
use Gos\Bundle\NotificationBundle\Event\NotificationEvent;
use Gos\Bundle\NotificationBundle\Event\NotificationEvents;
use Gos\Bundle\NotificationBundle\Model\Message\Message;
use Gos\Bundle\NotificationBundle\Model\Message\PatternMessage;
use Gos\Bundle\NotificationBundle\Model\TransportNotification;
use Gos\Bundle\WebSocketBundle\Event\Events;
use Gos\Bundle\WebSocketBundle\Event\ServerEvent;
use Gos\Bundle\WebSocketBundle\Server\Type\ServerInterface;
use Predis\Async\Client;
use Predis\Async\PubSub\PubSubContext;
use Psr\Log\LoggerInterface;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
class RedisServer implements ServerInterface
{
    /** @var  LoopInterface */
    protected $loop;

    /** @var  Client */
    protected $client;

    /** @var LoggerInterface */
    protected $logger;

    /** @var  EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param LoggerInterface|null         $logger
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Launches the server loop
     *
     * @return void
     */
    public function launch()
    {
        $this->loop = Factory::create();
        $this->client = new Client('tcp://'.$this->getAddress(), $this->loop);

        $this->client->connect(function(){
            $notifier = new Client('tcp://'.$this->getAddress(), $this->loop);

            //redis-cli PUBLISH notification:user:whatever_you_want "{"uuid":"7fd83972-db9e-4817-bccc-cf71296be762","type":"info","icon":null,"viewed_at":null,"created_at":"2015-02-28T14:24:24+01:00","content":"Hello world"}"
            $subscription = array(
                'subscribe' => array(),
                'psubscribe' => array('notification:user:*', 'notification:application:*')
            );

            if (null !== $this->logger) {
                $this->logger->info('Starting redis pubsub');

                if(!empty($subscription['subscribe'])){
                    $this->logger->info(sprintf(
                        'Listening topics %s',
                        implode(', ', $subscription['subscribe'])
                    ));
                }

                if(!empty($subscription['psubscribe'])){
                    $this->logger->info(sprintf(
                        'Listening pattern %s',
                        implode(', ', $subscription['psubscribe'])
                    ));
                }
            }

            $this->client->pubSub($subscription, function($event) use ($notifier, $subscription){

                $notifier->lpush((string) $event->channel, $event->payload, function () use ($event) {

                    if(!in_array($event->kind, array(PubSubContext::MESSAGE, PubSubContext::PMESSAGE))){
                        return;
                    }

                    if($event->kind === PubSubContext::MESSAGE ){
                        $message = new Message(
                            $event->kind,
                            $event->channel,
                            $event->payload
                        );
                    }

                    if($event->kind === PubSubContext::PMESSAGE){
                        $message = new PatternMessage(
                            $event->kind,
                            $event->pattern,
                            $event->channel,
                            $event->payload
                        );
                    }

                    $messageEvent = new MessageEvent($message);
                    $this->eventDispatcher->dispatch(MessageEvents::MESSAGE_PUBLISHED, $messageEvent);

                    if($messageEvent->isRejected()){
                        return;
                    }

                    //transform message into notification
                    $wrappedNotification = TransportNotification::toObject(json_decode($message->getPayload(), true));
                    $notification = $wrappedNotification->getNotification();

                    $context = new TransportContext($wrappedNotification->getPusherIdentity());

                    $notificationEvent = new NotificationEvent($notification, $message, $context);

                    $this->eventDispatcher->dispatch(NotificationEvents::NEW_NOTIFICATION, $notificationEvent);

                    if (null !== $this->logger) {
                        $this->logger->info(sprintf(
                            'Stored message %s from %s type %s',
                            $message->getPayload(),
                            $message->getChannel(),
                            $message->getKind()
                        ));
                    }
                });
            });
        });

        /* Server Event Loop to add other services in the same loop. */
        $event = new ServerEvent($this->loop, $this->getAddress(), $this->getName());
        $this->eventDispatcher->dispatch(Events::SERVER_LAUNCHED, $event);

        $this->loop->run();
    }

    /**
     * Returns a string of the host:port for debugging / display purposes
     *
     * @return string
     */
    public function getAddress()
    {
        //@TODO: Inject server parameters through DI
        return '127.0.0.1:6379';
    }

    /**
     * Returns a string of the name of the server/service for debugging / display purposes
     *
     * @return string
     */
    public function getName()
    {
        return 'Redis';
    }
}