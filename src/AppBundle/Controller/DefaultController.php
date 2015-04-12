<?php

namespace AppBundle\Controller;

use Gos\Bundle\NotificationBundle\Model\Notification;
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

        if ($this->container->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            $token = $this->container->get('security.token_storage')->getToken();
            $user = $token->getUser();

            $notification = new Notification();
            $notification->setTitle('Test');
            $notification->setContent('Nouveau utilisateur connecté');
            $notification->setType(Notification::TYPE_INFO);

            $notificationCenter = $this->container->get('gos_notification.notification_center');

            $notificationCenter->publish(
                'user_notification',
                ['username' => 'user2'],
                $notification
            );

            $notificationCenter->publish(
                'user_application_notification',
                ['username' => '*', 'application' => '*'],
                $notification
            );

            $notificationCenter->publish(
                'user_notification',
                ['username' => 'all'],
                $notification
            );

            $notificationCenter->count('user_notification', ['username' => 'user2']);

            $notification = $notificationCenter->getNotification('user_notification', ['username' => 'user2'], '3d226551-b67e-4bc0-9885-6925498fe658');
            $notificationCenter->markAsViewed('user_notification', ['username' => 'user2'], $notification);
        }

        return $this->render('AppBundle:App:index.html.twig', [
            'user' => isset($user) ? $user : null,
            'notifications' => $notifications,
        ]);
    }
}
