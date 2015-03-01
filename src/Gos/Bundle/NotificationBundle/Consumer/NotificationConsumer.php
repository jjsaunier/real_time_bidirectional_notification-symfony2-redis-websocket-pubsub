<?php

namespace Gos\Bundle\NotificationBundle\Consumer;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
abstract class NotificationConsumer implements NotificationConsumerInterface
{
    /**
     * @return array
     */
    abstract protected function subscribe();

    /**
     * @return array
     */
    public function getSubscription()
    {
        return $this->subscribe();
    }
}