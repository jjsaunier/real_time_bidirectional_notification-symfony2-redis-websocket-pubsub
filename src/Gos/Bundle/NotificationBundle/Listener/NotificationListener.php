<?php

namespace Gos\Bundle\NotificationBundle\Listener;

use Gos\Bundle\NotificationBundle\Consumer\NotificationConsumerInterface;
use Gos\Bundle\NotificationBundle\Event\NotificationEvent;
use Gos\Bundle\NotificationBundle\Consumer\ConsumerRegistry;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
class NotificationListener
{
    /**
     * @var ConsumerRegistry
     */
    protected $consumerRegistry;

    /**
     * @param ConsumerRegistry $consumerRegistry
     */
    public function __construct(ConsumerRegistry $consumerRegistry)
    {
        $this->consumerRegistry = $consumerRegistry;
    }

    /**
     * @param NotificationEvent $event
     */
    public function onNotification(NotificationEvent $event)
    {
        $message = $event->getMessage();

        $consumers = $this->consumerRegistry->getConsumers($message);

        /** @var NotificationConsumerInterface $consumer */
        foreach($consumers as $consumer){
            $consumer->process($event->getNotification(), $message, $event->getContext());
        }
    }
}