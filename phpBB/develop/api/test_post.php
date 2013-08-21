<?php

$request = array(
	'auth_key' => '0d2206b9114e99d0',
	'serial' => 67,

	'username' => '',
	'topic_type' => 0,

	'forum_id' => 2,
	'icon_id' => 0,

	'enable_bbcode' => true,
	'enable_smilies' =>false,
	'enable_urls' => true,
	'enable_sig' => true,

	'message' => '',

	'topic_title' => '',

	'notify' => false,
);

$imploded_request = implode('/', $request);

$hash = hash_hmac('sha256', $imploded_request, 'b758239365974841');

$request['hash'] = $hash;

$options = array(
	'http' => array(
		'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
		'method'  => 'POST',
		'content' => http_build_query($request),
		'ignore_errors' => true
	),
);
$context  = stream_context_create($options);
$result = file_get_contents('http://localhost/phpBB/phpBB/app.php?controller=api/topic/new', false, $context);

echo $result;
