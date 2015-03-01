<?php

namespace Gos\Bundle\NotificationBundle\Event;

use Gos\Bundle\NotificationBundle\Model\Message\MessageInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
class MessageEvent extends Event
{
    /**
     * @var MessageInterface
     */
    protected $message;

    /**
     * @var bool
     */
    protected $rejected;

    /**
     * @param MessageInterface $message
     */
    public function __construct(MessageInterface $message)
    {
        $this->message = $message;
        $this->rejected = false;
    }

    /**
     * @return MessageInterface
     */
    public function getMessage()
    {
        return $this->message;
    }

    public function reject()
    {
        $this->rejected = false;
    }

    /**
     * @return bool
     */
    public function isRejected()
    {
        return $this->rejected;
    }
}