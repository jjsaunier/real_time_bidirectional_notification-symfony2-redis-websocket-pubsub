<?php

namespace Gos\Bundle\NotificationBundle\Pusher;

use Gos\Bundle\NotificationBundle\Context\NotificationContextInterface;
use Gos\Bundle\NotificationBundle\Model\Message\MessageInterface;
use Gos\Bundle\NotificationBundle\Model\NotificationInterface;
use Gos\Bundle\PubSubRouterBundle\Request\PubSubRequest;
use Gos\Bundle\PubSubRouterBundle\Router\RouteInterface;
use Gos\Bundle\PubSubRouterBundle\Router\RouterInterface;
use Predis\Client as PredisClient;
use Snc\RedisBundle\Client\Phpredis\Client as PhpClient;

/**
 * Class RedisPusher.
 */
class RedisPusher extends AbstractPusher
{
    const ALIAS = 'gos_redis';

    /**
     * @var PredisClient|PhpClient
     */
    protected $client;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @param PredisClient|PhpClient          $client
     * @param RouterInterface $router
     */
    public function __construct($client, RouterInterface $router)
    {
        if(!$client instanceof PhpClient && !$client instanceof PredisClient){
            throw new \Exception('Bad client ' . get_class($client));
        }

        $this->client = $client;
        $this->router = $router;
    }

    /**
     * @param RouteInterface $route
     * @param array          $matrix
     *
     * @return array
     */
    protected function generateRoutes(RouteInterface $route, array $matrix)
    {
        $channels = [];
        foreach ($this->generateMatrixPermutations($matrix) as $parameters) {
            $channels[] = $this->router->generate((string) $route, $parameters);
        }

        return $channels;
    }

    /**
     * {@inheritdoc}
     */
    protected function doPush(
        MessageInterface $message,
        NotificationInterface $notification,
        PubSubRequest $request,
        Array $matrix,
        NotificationContextInterface $context = null
    ) {
        foreach ($this->generateRoutes($request->getRoute(), $matrix) as $channel) {
            $notification->setChannel($channel);

            if($this->client instanceof PredisClient){
                $pipe = $this->client->pipeline();

                $pipe->lpush($channel, json_encode($notification->toArray()));
                $pipe->incr($channel . '-counter');

                $pipe->execute();
            }else{
                $this->client->multi()
                    ->lpush($channel, json_encode($notification->toArray()))
                    ->incr($channel . '-counter')
                    ->exec()
                ;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return static::ALIAS;
    }
}
