<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once './vendor/autoload.php';


use Sly\NotificationPusher\PushManager,
    Sly\NotificationPusher\Adapter\Apns as ApnsAdapter,
    Sly\NotificationPusher\Collection\DeviceCollection,
    Sly\NotificationPusher\Model\Device,
    Sly\NotificationPusher\Model\Message,
    Sly\NotificationPusher\Model\Push;

// First, instantiate the manager.
//
// Example for production environment:
 $pushManager = new PushManager(PushManager::ENVIRONMENT_PROD);
//
// Development one by default (without argument).
//$pushManager = new PushManager(PushManager::ENVIRONMENT_DEV);

// Then declare an adapter.
$apnsAdapter = new ApnsAdapter(array(
    'certificate' => __DIR__.'/apple_push_notification_production.pem',
    'passPhrase'  => 'passp01+'
));

// Set the device(s) to push the notification to.
$devices = new DeviceCollection(array(
    new Device('f4c13321c34816430fccbb94b370b7c8bcab6c4272fe91dffd5c40335e3b725a', array('badge' => 5)),
//    new Device('Token2', array('badge' => 1)),
//    new Device('Token3'),
));

// Then, create the push skel.
$message = new Message('This is an example.', array(
    'badge' => 1,
//    'sound' => 'example.aiff',
//
//    'actionLocKey' => 'Action button title!',
//    'locKey'       => 'localized key',
//    'locArgs'      => array(
//        'localized args',
//        'localized args',
//        'localized args'
//    ),
//    'launchImage'  => 'image.jpg',
//
//    'custom' => array(
//        'custom data' => array(
//            'we' => 'want',
//            'send to app'
//        )
//    )
));

// Finally, create and add the push to the manager, and push it!
$push = new Push($apnsAdapter, $devices, $message);
$pushManager->add($push);
$pushResult = $pushManager->push(); // Returns a collection of notified devices
echo "\n".'$pushResult: ';
/** @var \Sly\NotificationPusher\Model\Push $notifiedDevice */
foreach ($pushResult as $notifiedDevice) {
    /** @var \ZendService\Apple\Apns\Response\Message $responseMessage */
    $responseMessage = $notifiedDevice->getAdapter()->getResponse();
    echo "\n".'-- status: '.$notifiedDevice->getStatus()
        .'; isPushed: '.$notifiedDevice->isPushed()
        .'; message: '.($notifiedDevice->getMessage() ? $notifiedDevice->getMessage()->getText() : '')
        .'; response: '.$responseMessage->getCode().' - '.$responseMessage->getId();
}
echo "\n";



