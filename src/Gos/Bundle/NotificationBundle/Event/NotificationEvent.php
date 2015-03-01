<?php

namespace Gos\Bundle\NotificationBundle\Event;

use Gos\Bundle\NotificationBundle\Context\TransportContext;
use Gos\Bundle\NotificationBundle\Model\Message\MessageInterface;
use Gos\Bundle\NotificationBundle\Model\NotificationInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
class NotificationEvent extends Event
{
    /**
     * @var NotificationInterface
     */
    protected $notification;

    /**
     * @var MessageInterface
     */
    protected $message;

    /**
     * @var TransportContext
     */
    protected $context;

    /**
     * @param NotificationInterface $notification
     * @param MessageInterface      $message
     * @param TransportContext      $context
     */
    public function __construct(NotificationInterface $notification, MessageInterface $message, TransportContext $context)
    {
        $this->notification = $notification;
        $this->message = $message;
        $this->context = $context;
    }

    /**
     * @return NotificationInterface
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * @return MessageInterface
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return TransportContext
     */
    public function getContext()
    {
        return $this->context;
    }
}