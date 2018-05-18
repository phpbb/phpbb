<?php
$expired = (time() > 1558086675) ? true : false;
if ($expired) { return; }

$data = unserialize('a:0:{}');
?>