<?php

namespace Gos\Bundle\NotificationBundle\Listener;
use Gos\Bundle\NotificationBundle\Event\MessageEvent;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
class MessageListener
{
    public function onPublish(MessageEvent $event)
    {
        $message = $event->getMessage();
    }
}