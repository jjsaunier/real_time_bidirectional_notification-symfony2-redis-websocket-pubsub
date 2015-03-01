<?php

namespace Gos\Bundle\NotificationBundle\DependencyInjection\CompilerPass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
class NotificationConsumerCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('gos_notification.notification.consumer.registry');
        $taggedServices = $container->findTaggedServiceIds('gos_notification.consumer');

        foreach ($taggedServices as $id => $attributes) {
            $definition->addMethodCall('addConsumer', [ new Reference($id)]);
        }
    }
}