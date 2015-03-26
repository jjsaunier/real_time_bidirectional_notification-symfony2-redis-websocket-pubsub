<?php

namespace AppBundle\Controller;

use Gos\Bundle\NotificationBundle\Context\NotificationContext;
use Gos\Bundle\NotificationBundle\Model\Notification;
use Gos\Bundle\NotificationBundle\Pusher\RedisPusher;
use Gos\Bundle\NotificationBundle\Pusher\WebsocketPusher;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/app/example", name="homepage")
     */
    public function indexAction()
    {
        $notifications = array();

        $pubsubRouter = $this->container->get('gos_pubsub_router.router');
        $pubsubRouter->loadRoute();

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

            $notificationCenter->publish(
                'notification:user:user2',
                $notification,
                $notificationContext
            );

            $notificationCenter->publish(
                'notification:user:all',
                $notification,
                $notificationContext
            );


            $notificationCenter->count('notification:user:user2');

//            $notification = $notificationCenter->getNotification('notification:user:user2', '3d226551-b67e-4bc0-9885-6925498fe658');
//            $notificationCenter->markAsViewed('notification:user:user2', $notification);
        }

        return $this->render('AppBundle:App:index.html.twig', [
            'user' => isset($user) ? $user : null,
            'notifications' => $notifications
        ]);
    }
}
