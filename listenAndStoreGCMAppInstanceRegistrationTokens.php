<br>$_GET:
<pre>
<?php
    var_dump($_GET);
?>
</pre>

<br>$_POST:
<pre>
<?php
    var_dump($_POST);
?>
</pre>

<br>$_REQUEST:
<pre>
<?php
    var_dump($_REQUEST);
?>
</pre>

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

