<?php

namespace AppBundle\Notification\Processor;

use Gos\Bundle\NotificationBundle\Model\NotificationInterface;
use Gos\Bundle\NotificationBundle\Processor\ProcessorInterface;
use Gos\Bundle\PubSubRouterBundle\Request\PubSubRequest;

class ApplicationProcessor implements ProcessorInterface
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
    protected function doProcessUnique($pusherName, NotificationInterface $notification, PubSubRequest $request)
    {
        return $request->getAttributes()->get('application', false);
    }

    /**
     * {@inheritdoc}
     */
    protected function doProcessWildcard($pusherName, NotificationInterface $notification, PubSubRequest $request)
    {
        return [
            'blog',
            'dashboard',
            'translator',
            'boosting',
            'coaching',
        ];
    }
}
