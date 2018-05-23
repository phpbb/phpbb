<?php
$expired = (time() > 1558554604) ? true : false;
if ($expired) { return; }

$data = unserialize('a:1:{s:7:"special";a:1:{i:1;a:2:{s:10:"rank_title";s:10:"Site Admin";s:10:"rank_image";s:0:"";}}}');
?>