<?php

namespace Gos\Bundle\PubSubRouterBundle\Loader;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;

class RoutingLoader extends Loader
{
    /**
     * @var string[]
     */
    protected $resources;

    /**
     * @param string $resource
     * @param null  $type
     *
     * @return RouteCollection
     */
    public function load($resource, $type = null)
    {
        $collection = new RouteCollection();

        $importedRoutes = $this->import($resource, $type);

        $collection->addCollection($importedRoutes);

        return $collection;
    }

    /**
     * @param mixed $resource
     * @param null  $type
     *
     * @return bool
     */
    public function supports($resource, $type = null)
    {
        return true;
    }
}