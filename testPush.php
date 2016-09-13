<?php

require_once './vendor/autoload.php';

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

// Set the device(s) to push the notification to.
$devices = new DeviceCollection(
    array(
        new Device('edGtl6RwIDc:APA91bG0nZGuv6wB0D_5mXB9JEyGvQCZtW4zG5sZIBzvyHsNRxMYRyUYu7txLexVzkb_0quLlvwX5_itGofjPgh-VDh03fHhz1MGcXDZxt0KxS3ueuJu6LpwuE59yHTGuI_Nc7o1rZWK')
    )
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
