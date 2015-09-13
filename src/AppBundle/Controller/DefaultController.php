<?php

namespace AppBundle\Controller;

use Gos\Bundle\NotificationBundle\Model\Notification;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/app/example", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $notifications = array();

        if ($this->container->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            $token = $this->container->get('security.token_storage')->getToken();
            $user = $token->getUser();

            $notification = new Notification();
            $notification->setTitle('Test');
            $notification->setContent('Nouveau utilisateur connecté');
            $notification->setIcon('https://cdn3.iconfinder.com/data/icons/line-icons-medium-version/64/bell-512.png');
            $notification->setType(Notification::TYPE_ERROR);

            $notificationCenter = $this->container->get('gos_notification.notification_center');

            $redisRouter = $this->container->get('gos_pubsub_router.redis');

            $qpusher = $this->container->get('gos_web_socket.amqp.pusher');
            $notification->setContent('AMQP pusher');
            $qpusher->push(json_encode($notification), 'user_notification', ['username' => 'user1']);
            $qpusher->push(json_encode($notification), 'user_notification', ['username' => 'user1']);
            $qpusher->push(json_encode($notification), 'user_notification', ['username' => 'user1']);
            $qpusher->push(json_encode($notification), 'user_notification', ['username' => 'user1']);

            $zpusher = $this->container->get('gos_web_socket.zmq.pusher');
            $notification->setContent('ZMQ pusher');
            $zpusher->push(json_encode($notification), 'user_notification', ['username' => 'user2']);

            $wspusher = $this->container->get('gos_web_socket.wamp.pusher');
            $notification->setContent('Wesocket pusher');
            $wspusher->push(json_encode($notification), 'user_notification', ['username' => 'user1']);


            $wspusher->push(json_encode($notification), 'user_notification', ['username' => 'user2']);
            $zpusher->push(json_encode($notification), 'user_notification', ['username' => 'user1']);
            $zpusher->push(json_encode($notification), 'user_notification', ['username' => 'user1']);
            $zpusher->push(json_encode($notification), 'user_notification', ['username' => 'user1']);
            $zpusher->push(json_encode($notification), 'user_notification', ['username' => 'user1']);
            $zpusher->push(json_encode($notification), 'user_notification', ['username' => 'user1']);
            $zpusher->push(json_encode($notification), 'user_notification', ['username' => 'user1']);
            $zpusher->push(json_encode($notification), 'user_notification', ['username' => 'user1']);
            $zpusher->push(json_encode($notification), 'user_notification', ['username' => 'user1']);
            $zpusher->push(json_encode($notification), 'user_notification', ['username' => 'user1']);
            $zpusher->push(json_encode($notification), 'user_notification', ['username' => 'user1']);
            $zpusher->push(json_encode($notification), 'user_notification', ['username' => 'user1']);
            $zpusher->push(json_encode($notification), 'user_notification', ['username' => 'user1']);
            $zpusher->push(json_encode($notification), 'user_notification', ['username' => 'user1']);
            $zpusher->push(json_encode($notification), 'user_notification', ['username' => 'user1']);
//            $qpusher->push(json_encode($notification), 'user_notification', ['username' => 'user1']);

            $notification->setContent('LOL');

            $notificationCenter->publish(
                $redisRouter->generate('user_notification', ['username' => 'user2']),
                $notification
            );

            $notification->setContent('BOL');
            $notificationCenter->publish(
                $redisRouter->generate('user_application_notification', [ 'username' => '*', 'application' => '*']),
                $notification
            );

            $notification->setContent('COL');

            $notificationCenter->publish(
                $redisRouter->generate('user_notification', [ 'username' => 'all']),
                $notification
            );

//            $notificationCenter->count($redisRouter->generate('user_notification', [ 'username' => 'user2']));

//            $notification = $notificationCenter->getNotification($redisRouter->generate('user_notification', [ 'username' => 'user2']), '3d226551-b67e-4bc0-9885-6925498fe658');
//            $notificationCenter->markAsViewed($redisRouter->generate('user_notification', [ 'username' => 'user2']), '3d226551-b67e-4bc0-9885-6925498fe658');
        }

        return $this->render('AppBundle:App:index.html.twig', [
            'user' => isset($user) ? $user : null,
            'notifications' => $notifications,
        ]);
    }
}
