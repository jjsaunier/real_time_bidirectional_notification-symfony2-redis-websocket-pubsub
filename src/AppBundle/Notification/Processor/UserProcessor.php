<?php

namespace  AppBundle\Notification\Processor;

use Gos\Bundle\NotificationBundle\Model\NotificationInterface;
use Gos\Bundle\NotificationBundle\Processor\ProcessorInterface;
use Gos\Bundle\PubSubRouterBundle\Request\PubSubRequest;

class UserProcessor implements ProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process($wildcard, $pusherName, NotificationInterface $notification, PubSubRequest $request)
    {
        if (true === $wildcard) {
            return $this->doProcessWildCard($pusherName, $notification, $request);
        } else {
            return $this->doProcessUnique($pusherName, $notification, $request);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function doProcessUnique($pusherName, NotificationInterface $notification, PubSubRequest $request)
    {
        return $request->getAttributes()->get('username', false);
    }

    /**
     * {@inheritdoc}
     */
    public function doProcessWildcard($pusherName, NotificationInterface $notification, PubSubRequest $request)
    {
        //our users are stored in memory (look at app/security.yml), inject a userRepository and call findAll (or custom) if you use db
        return [
            'user1',
            'user2',
            'user3',
            'user4',
            'user5',
        ];
    }
}
