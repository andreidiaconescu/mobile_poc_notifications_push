<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once './vendor/autoload.php';

require_once __DIR__.'/vendor/adriengibrat/SimpleDatabasePHPClass/Db.php';

use Sly\NotificationPusher\PushManager,
    Sly\NotificationPusher\Adapter\Gcm as GcmAdapter,
    Sly\NotificationPusher\Collection\DeviceCollection,
    Sly\NotificationPusher\Model\Device,
    Sly\NotificationPusher\Model\Message,
    Sly\NotificationPusher\Model\Push
;

// First, instantiate the manager.
//
// Example for production environment:
// $pushManager = new PushManager(PushManager::ENVIRONMENT_PROD);
//
// Development one by default (without argument).
$pushManager = new PushManager(PushManager::ENVIRONMENT_DEV);

// Then declare an adapter.
$gcmAdapter = new GcmAdapter(array(
    'apiKey' => 'AIzaSyAK8xELdfPiXWLyL0LYGSua9aVrgYVPRXM',
));


// grant all PRIVILEGES on mobile_poc_notifications.* to notifs_user@localhost IDENTIFIED by 'notifs_pass';
$db = new \Db( 'mysql', 'localhost', 'mobile_poc_notifications', 'notifs_user', 'notifs_pass');

// get the device(s) instance ids tokens to push the notification to.
$tokens = $db->query(
        '
          SELECT * 
          FROM app_instance_tokens 
          ORDER BY id DESC
          LIMIT 1000
        ',
        array()
    )
    ->all();
$devicesTokensArr = [];
foreach ($tokens as $token) {
    $devicesTokensArr[] = new Device($token->token);
}


// Set the device(s) to push the notification to.
$devices = new DeviceCollection(
//    array(
//        new Device('dfu7LL9Py2Y:APA91bFdtG5rrCPqg8ytPF2jMMgm4CXFcJH_XbtHVkv8MEYRSyoQxJcxLngvwfs8Q-h1IZRFYDkuq8B6pgUpoKTl07VRj9UjGHCmLnm1Lj2UdB1UmF4c-e-ilitoDt5cJi3DpbvSiC4Y')
//    )
    $devicesTokensArr
);

// Then, create the push skel.
$message = new Message(
    'This is an example.',
    [
        'title' => 'title example',
        'body'  => 'body example',
    ]
);

// Finally, create and add the push to the manager, and push it!
$push = new Push($gcmAdapter, $devices, $message);
$pushManager->add($push);
$pushManager->push(); // Returns a collection of notified devices
