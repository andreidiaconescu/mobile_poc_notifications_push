<?php
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

// grant all PRIVILEGES on mobile_poc_notifications.* to mobile_poc_notifications_user@localhost IDENTIFIED by 'mobile_poc_notifications_pass';
$db = new Db( 'mysql', 'localhost', 'mobile_poc_notifications', 'notifs_user', 'notifs_pass');
$dbReadResult = $db->query('SELECT * FROM app_instance_tokens WHERE id = :id', array( 'id' => 1 ) );
?>
<br>$dbReadResult:
<pre>
<?php
    var_dump($dbReadResult);
?>
</pre>


