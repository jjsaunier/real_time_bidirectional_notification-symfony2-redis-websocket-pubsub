<?php

namespace Gos\Bundle\NotificationBundle\Model;

use Gos\Bundle\NotificationBundle\Context\PusherIdentity;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
class TransportNotification implements \JsonSerializable
{
    /**
     * @var NotificationInterface
     */
    protected $notification;

    /**
     * @var PusherIdentity
     */
    protected $pusherIdentity;

    /**
     * @param NotificationInterface $notification
     */
    public function wrap(NotificationInterface $notification)
    {
        $this->notification = $notification;
    }

    /**
     * @param PusherIdentity $pusherIdentity
     */
    public function setPusherIdentity(PusherIdentity $pusherIdentity)
    {
        $this->pusherIdentity = $pusherIdentity;
    }

    /**
     * @return string
     */
    public function getPusherIdentity()
    {
        return $this->pusherIdentity;
    }

    /**
     * @return NotificationInterface
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     *       which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        if(null === $this->notification){
            throw new \RuntimeException('No notification');
        }

        return array(
            'notification' => $this->notification,
            'pusher_identity' => $this->pusherIdentity->getIdentity()
        );
    }

    /**
     * @param Array $data
     *
     * @return TransportNotification
     */
    public static function toObject(Array $data)
    {
        $notificationTransport = new TransportNotification();
        $notification = Notification::toObject($data['notification']);
        $notificationTransport->wrap($notification);

        if(null !== $data['pusher_identity']){
            list($type, $identifier) = explode('#', $data['pusher_identity']);
            $notificationTransport->setPusherIdentity(new PusherIdentity($type, $identifier));
        }

        return $notificationTransport;
    }
}