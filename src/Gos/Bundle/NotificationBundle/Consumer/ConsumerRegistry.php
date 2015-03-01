<?php

namespace Gos\Bundle\NotificationBundle\Consumer;
use Gos\Bundle\NotificationBundle\Model\Message\MessageInterface;
use Gos\Bundle\NotificationBundle\Model\Message\PatternMessage;
use Gos\Bundle\NotificationBundle\Model\Message\Message;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
class ConsumerRegistry
{
    /**
     * @var array
     */
    protected $consumers;

    public function __construct()
    {
        $this->consumers = array();
    }

    /**
     * @param NotificationConsumerInterface $consumer
     */
    public function addConsumer(NotificationConsumerInterface $consumer)
    {
        $this->consumers[] = $consumer;
    }

    /**
     * @param MessageInterface $message
     *
     * @return NotificationConsumerInterface[]
     */
    public function getConsumers(MessageInterface $message)
    {
        $consumers = array();

        /** @var NotificationConsumerInterface $consumer */
        foreach($this->consumers as $consumer){
            $subscription = $consumer->getSubscription();

            //resolve parttern channel based
            if($message instanceof PatternMessage){
                if(isset($subscription['pchannel']) && !empty($subscription['pchannel'])){
                    $patterns = $subscription['pchannel'];

                    if(in_array($message->getPattern(), $patterns)){
                        $consumers[] = $consumer;
                    }
                }
            }

            //resolve channel based
            if($message instanceof Message) {
                if(isset($subscription['channel']) && !empty($subscription['channel'])){
                    $channels = $subscription['channel'];

                    if(in_array($message->getChannel(), $channels)){
                        $consumers[] = $consumer;
                    }
                }
            }

        }

        return $consumers;
    }
}