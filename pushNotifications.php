<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once './vendor/autoload.php';

require_once __DIR__ . '/vendor/adriengibrat/SimpleDatabasePHPClass/Db.php';

use Sly\NotificationPusher\PushManager,
    Sly\NotificationPusher\Adapter\Gcm as GcmAdapter,
    Sly\NotificationPusher\Adapter\Apns as ApnsAdapter,
    Sly\NotificationPusher\Collection\DeviceCollection,
    Sly\NotificationPusher\Model\Device,
    Sly\NotificationPusher\Model\Message,
    Sly\NotificationPusher\Model\Push;

class AnaisPushNotifications
{
    /** @var Sly\NotificationPusher\PushManager $pushManager */
    protected $pushManager = null;
    protected $gcmAdapter  = null;
    protected $apnsAdapter = null;

    const PLATFORM_ANDROID = 'android';
    const PLATFORM_IOS = 'ios';

    public function init()
    {
        // First, instantiate the manager.
        $this->pushManager = new PushManager(PushManager::ENVIRONMENT_PROD);
//        $this->pushManager = new PushManager(PushManager::ENVIRONMENT_DEV);

        // Then declare an adapter.
        // for Android
        $this->gcmAdapter = new GcmAdapter(array(
            'apiKey' => 'AIzaSyAK8xELdfPiXWLyL0LYGSua9aVrgYVPRXM',
        ));
        // for IOS
        $this->apnsAdapter = new ApnsAdapter(array(
            'certificate' => __DIR__ . '/apple_push_notification_production.pem',
            'passPhrase'  => 'passp01+'
        ));
    }

    public function processNotifications()
    {
        // grant all PRIVILEGES on mobile_poc_notifications.* to notifs_user@localhost IDENTIFIED by 'notifs_pass';
        $db = new \Db('mysql', 'localhost', "mobile_poc_notifications", 'notifs_user', 'notifs_pass');

        // get the device(s) instance ids tokens to push the notification to. (for ANDROID)
        $rsAndroidTokens = $db->query(
            '
                  SELECT * 
                  FROM app_instance_tokens
                  WHERE platform = "'.static::PLATFORM_ANDROID.'"
                  ORDER BY id DESC
                  LIMIT 1000
                ',
            array()
        );
        $this->sendNotifications($rsAndroidTokens, static::PLATFORM_ANDROID);

//        // get the device(s) instance ids tokens to push the notification to. (for IOS)
//        $rsAndroidTokens = $db->query(
//            '
//                  SELECT *
//                  FROM app_instance_tokens
//                  WHERE platform = "'.static::PLATFORM_IOS.'"
//                  ORDER BY id DESC
//                  LIMIT 1000
//                ',
//            array()
//        );
//        $this->sendNotifications($rsAndroidTokens, static::PLATFORM_IOS);
    }

    protected function sendNotifications($rsTokens, $platform)
    {
        $tokens = $rsTokens->all();
        $devicesTokensArr = [];
        foreach ($tokens as $token) {
            $devicesTokensArr[] = new Device($token->token);
        }
//        $devicesTokensArr = [new Device('dWpna0_yWI8:APA91bHaT-OTAcwzzP_adejEwWPdjE7gt7W9WByPZNvun9qvt52KT_9rxCvmtIQ5XLVBNBu-tb4ziIO444qwcE2wLNV2HqtCz9cQ-t_PdJi2pU3seZdO3p61NGFfww0th1_fJAtU2TVY')];

        if (!count($devicesTokensArr)) {
            echo "\n".'No Tokens found for platform: '.$platform;
            return;
        }

        // Set the device(s) to push the notification to.
        $devices = new DeviceCollection(
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


        if ($platform == static::PLATFORM_ANDROID) {
            $adapter = $this->gcmAdapter;
        } elseif ($platform == static::PLATFORM_IOS) {
            $adapter = $this->apnsAdapter;
        }

        // Finally, create and add the push to the manager, and push it!
        $push = new Push($adapter, $devices, $message);
        $this->pushManager->add($push);
        $pushResult = $this->pushManager->push(); // Returns a collection of notified devices

//        echo '$pushResult: '; var_dump($pushResult); exit;

        echo "\n".'$pushResult: ';
        /** @var \Sly\NotificationPusher\Model\Push $notifiedDevice */
        foreach ($pushResult as $notifiedDevice) {
            /** @var \ZendService\Apple\Apns\Response\Message $responseMessage */
            $responseMessage = $notifiedDevice->getAdapter()->getResponse();

            if ($platform == static::PLATFORM_IOS) {
                echo "\n".'-- status: '.$notifiedDevice->getStatus()
                    .'; isPushed: '.$notifiedDevice->isPushed()
                    .'; message: '.($notifiedDevice->getMessage() ? $notifiedDevice->getMessage()->getText() : '')
                    .'; response: '.$responseMessage->getCode().' - '.$responseMessage->getId();
            } elseif ($platform == static::PLATFORM_ANDROID) {

                echo "\n".'-- status: '.$notifiedDevice->getStatus()
                    .'; isPushed: '.$notifiedDevice->isPushed()
                    .'; message: '.($notifiedDevice->getMessage() ? $notifiedDevice->getMessage()->getText() : '')
                    .'; results: '.json_encode($responseMessage->getResults());
            }
        }

    }
}

$sendNotificationsTool = new AnaisPushNotifications();
$sendNotificationsTool->init();
$sendNotificationsTool->processNotifications();
echo "\n";


//// First, instantiate the manager.
////
//// Example for production environment:
//$pushManager = new PushManager(PushManager::ENVIRONMENT_PROD);
////
//// Development one by default (without argument).
////$pushManager = new PushManager(PushManager::ENVIRONMENT_DEV);
//
//// Then declare an adapter.
//// for Android
//$gcmAdapter = new GcmAdapter(array(
//    'apiKey' => 'AIzaSyAK8xELdfPiXWLyL0LYGSua9aVrgYVPRXM',
//));
//// for IOS
//$apnsAdapter = new ApnsAdapter(array(
//    'certificate' => __DIR__ . '/apple_push_notification_production.pem',
//    'passPhrase'  => 'passp01+'
//));
//
//
//// grant all PRIVILEGES on mobile_poc_notifications.* to notifs_user@localhost IDENTIFIED by 'notifs_pass';
//$db = new Db('mysql', 'localhost', 'mobile_poc_notifications', 'notifs_user', 'notifs_pass');
//
//// get the device(s) instance ids tokens to push the notification to. (for ANDROID)
//$tokens = $db->query(
//    '
//          SELECT *
//          FROM app_instance_tokens
//          WHERE platform = "android"
//          ORDER BY id DESC
//          LIMIT 1000
//        ',
//    array()
//)
//    ->all();
//$devicesTokensArr = [];
//foreach ($tokens as $token) {
//    $devicesTokensArr[] = new Device($token->token);
//}
//$devicesTokensArr = [new Device('dWpna0_yWI8:APA91bHaT-OTAcwzzP_adejEwWPdjE7gt7W9WByPZNvun9qvt52KT_9rxCvmtIQ5XLVBNBu-tb4ziIO444qwcE2wLNV2HqtCz9cQ-t_PdJi2pU3seZdO3p61NGFfww0th1_fJAtU2TVY')];
//
//// Set the device(s) to push the notification to.
//$devices = new DeviceCollection(
////    array(
////        new Device('dfu7LL9Py2Y:APA91bFdtG5rrCPqg8ytPF2jMMgm4CXFcJH_XbtHVkv8MEYRSyoQxJcxLngvwfs8Q-h1IZRFYDkuq8B6pgUpoKTl07VRj9UjGHCmLnm1Lj2UdB1UmF4c-e-ilitoDt5cJi3DpbvSiC4Y')
////    )
//    $devicesTokensArr
//);
//
//// Then, create the push skel.
//$message = new Message(
//    'This is an example.',
//    [
//        'title' => 'title example',
//        'body'  => 'body example',
//    ]
//);
//
//
//// Finally, create and add the push to the manager, and push it!
//$push = new Push($gcmAdapter, $devices, $message);
//$pushManager->add($push);
//$pushManager->push(); // Returns a collection of notified devices
//
