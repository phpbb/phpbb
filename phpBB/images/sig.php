<?
//JN (GPL)
Header("Content-Type: image/png");
Header("Expires: Mon, 1, 1999 05:00:00 GMT");
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
Header("Cache-Control: no-store, no-cache, must-revalidate");
Header("Cache-Control: post-check=0, pre-check=0", false);
Header("Pragma: no-cache");

$im = ImageCreateFromPNG("sig_mx.png");
$blue = ImageColorAllocate($im, 6, 108, 159);
srand ((float) microtime() * 10000000);
$quote = rand(1,6);

$current_release = "2.7.6";

switch($quote)
{

case "1":
$rand_quote = "mxBB Team, www.mx-publisher.com";
break;

case "2":
$rand_quote = "in between milestones edition ;)";
break;

case "3":
$rand_quote = "mxBB, fully modular portal for phpbb";
break;

case "4":
$rand_quote = "portal & cms site creation tool";
break;

case "5":
$rand_quote = "...pafileDB, Smartor, KB...modules";
break;

case "6":
$rand_quote = "...Calendar, Links & News...modules";
break;

}
ImageString($im, 2, 125, 2, $current_release, $blue);
ImageString($im, 2, 20, 17, $rand_quote, $blue);
ImagePNG($im);
?>