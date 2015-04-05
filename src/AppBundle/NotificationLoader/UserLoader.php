<?php

namespace  AppBundle\Controller\Loader;

use Gos\Bundle\NotificationBundle\Loader\LoaderInterface;

class UserLoader implements LoaderInterface
{
    const TYPE = 'user';

    /**
     * @param array $options
     *
     * @return string[]
     */
    public function load(Array $options = array())
    {
        return [
            'user1',
            'user2',
            'user3',
            'user4',
            'user5',
        ];
    }

    /**
     * @param mixed $type
     *
     * @return bool
     */
    public function supports($type)
    {
        return $type === static::TYPE;
    }
}
