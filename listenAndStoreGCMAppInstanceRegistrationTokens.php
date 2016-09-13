<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once './vendor/adriengibrat/SimpleDatabasePHPClass/Db.php';

$tokenData = [];
if (isset($_GET) && is_array($_GET)) {
    $tokenData = array_merge($tokenData, $_GET);
}
if (isset($_POST) && is_array($_GET)) {
    $tokenData = array_merge($tokenData, $_POST);
}

$inputStreamParams = file_get_contents('php://input');
if ($inputStreamParams)
$inputStreamParams = json_decode($inputStreamParams,true);
if ($inputStreamParams && is_array($inputStreamParams)) {
    $tokenData = array_merge($tokenData, $inputStreamParams);
}
?>

<br>$tokenData:
<pre>
<?php
    var_dump($tokenData);
?>
</pre>

<?php
if (!is_array($tokenData) || !isset($tokenData['token'])) {
    echo 'ERROR: token data could not be parsed';
    exit;
}

// grant all PRIVILEGES on mobile_poc_notifications.* to notifs_user@localhost IDENTIFIED by 'notifs_pass';
$db = new \Db( 'mysql', 'localhost', 'mobile_poc_notifications', 'notifs_user', 'notifs_pass');

// check that token does not already exist
$existingToken = $db->query('SELECT * FROM app_instance_tokens WHERE token = :token LIMIT 1', array( 'token' => $tokenData['token'] ) )->all();
if (is_array($existingToken) && count($existingToken) > 0) {
    echo '<br>Token already exists in DB table';
    $existingToken = $existingToken[0]->token;
} else {
    $existingToken = null;
}

if (!$existingToken) {
    // insert token
    $db->create(
        'app_instance_tokens',
        array(
            'token' => $tokenData['token'],
            'crdate' => date('Y-m-d h:i:s'),
            'tstamp' => date('Y-m-d h:i:s')
        )
    );
    echo '<br>Token inserted';
}
