<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php
/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>phpBB Visual Editor</title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link type="text/css" rel="stylesheet" href="sample.css" />
</head>
<body>
	<center><h1>
		Posted Data
	</h1></center>
	<table border="1" cellspacing="0" cellpadding="10" id="outputSample">
		<colgroup><col width="100" /></colgroup>
		<thead>
			<tr>
				<th>Field&nbsp;Name</th>
				<th>Value</th>
			</tr>
		</thead>
<?php

if ( isset( $_POST ) )
	$postArray = &$_POST ;			// 4.1.0 or later, use $_POST
else
	$postArray = &$HTTP_POST_VARS ;	// prior to 4.1.0, use HTTP_POST_VARS

foreach ( $postArray as $sForm => $value )
{
	if ( get_magic_quotes_gpc() )
		$postedValue = htmlspecialchars( stripslashes( $value ) ) ;
	else
		$postedValue = htmlspecialchars( $value ) ;

?>
		<tr>
			<th><?php echo htmlspecialchars($sForm); ?></th>
			<td><pre><?php echo $postedValue?></pre></td>
		</tr>
	<?php
}
?>
	</table>
</body>
</html>
