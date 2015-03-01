<?php

namespace Gos\Bundle\NotificationBundle\Context;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
class TransportContext implements \JsonSerializable
{
    /**
     * @var PusherIdentity|null
     */
    protected $pusherIdentity;

    /**
     * @param PusherIdentity|null $pusherIdentity
     */
    public function __construct(PusherIdentity $pusherIdentity = null)
    {
        $this->pusherIdentity = $pusherIdentity;
    }

    /**
     * @return bool
     */
    public function hasPusherIdentity()
    {
        return null !== $this->pusherIdentity;
    }

    /**
     * @return PusherIdentity|null
     */
    public function getPusherIdentity()
    {
        return $this->pusherIdentity;
    }

    /**
     * @return array
     */
    function jsonSerialize()
    {
        return array(
            'pusher_identity' => $this->pusherIdentity
        );
    }
}