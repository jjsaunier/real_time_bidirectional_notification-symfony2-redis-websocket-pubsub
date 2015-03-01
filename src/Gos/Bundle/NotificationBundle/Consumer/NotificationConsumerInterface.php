<?php

namespace Gos\Bundle\NotificationBundle\Consumer;

use Gos\Bundle\NotificationBundle\Context\TransportContext;
use Gos\Bundle\NotificationBundle\Model\Message\MessageInterface;
use Gos\Bundle\NotificationBundle\Model\Notification;

interface NotificationConsumerInterface
{
    /**
     * @return array
     */
    public function getSubscription();

    /**
     * @param Notification     $notification
     * @param MessageInterface $message
     * @param TransportContext|null $context
     *
     * @return mixed
     */
    public function process(Notification $notification, MessageInterface $message, TransportContext $context = null);
}