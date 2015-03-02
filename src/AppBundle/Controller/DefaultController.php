<?php

namespace AppBundle\Controller;

use Gos\Bundle\NotificationBundle\Context\NotificationContext;
use Gos\Bundle\NotificationBundle\Model\Notification;
use Gos\Bundle\NotificationBundle\Pusher\RedisPusher;
use Gos\Bundle\NotificationBundle\Pusher\WebsocketPusher;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * @Route("/app/example", name="homepage")
     */
    public function indexAction()
    {
        $notifications = array();

        if($this->container->get('security.authorization_checker')->isGranted('ROLE_USER')){
            $token = $this->container->get('security.token_storage')->getToken();
            $user = $token->getUser();

            $notification = new Notification();
            $notification->setTitle('Test');
            $notification->setContent('Nouveau utilisateur connecté');
            $notification->setType(Notification::TYPE_INFO);

            $notificationCenter = $this->container->get('gos_notification.notification_center');
            $notificationContext = new NotificationContext();
            $notificationContext->setPushers([RedisPusher::ALIAS, WebsocketPusher::ALIAS]);

            $notificationCenter->push(
                'notification:user:user2',
                $notification,
                $notificationContext
            );

//            $notificationCenter->push(
//                'notification:user:all',
//                $notification,
//                PusherIdentity::fromAccount($user)
//            );

//            $notifications = $notificationCenter->fetch('notification:user:user2', 0, 20);
        }

        return $this->render('AppBundle:App:index.html.twig', [
            'user' => isset($user) ? $user : null,
            'notifications' => $notifications
        ]);
    }
}
