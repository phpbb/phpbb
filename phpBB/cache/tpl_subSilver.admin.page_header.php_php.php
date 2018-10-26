<?php

// eXtreme Styles mod cache. Generated on Sun, 21 Oct 2018 17:14:29 +0000 (time=1540142069)

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html dir="<?php echo isset($this->vars['S_CONTENT_DIRECTION']) ? $this->vars['S_CONTENT_DIRECTION'] : $this->lang('S_CONTENT_DIRECTION'); ?>">
<head>
<?php echo isset($this->vars['META']) ? $this->vars['META'] : $this->lang('META'); ?>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo isset($this->vars['S_CONTENT_ENCODING']) ? $this->vars['S_CONTENT_ENCODING'] : $this->lang('S_CONTENT_ENCODING'); ?>"  />
<!-- link rel="stylesheet" href="../templates/subSilver/<?php echo isset($this->vars['T_HEAD_STYLESHEET']) ? $this->vars['T_HEAD_STYLESHEET'] : $this->lang('T_HEAD_STYLESHEET'); ?>" type="text/css" -->
<style type="text/css">
<!--

/*
  The original subSilver Theme for phpBB version 2+
  Created by subBlue design
  http://www.subBlue.com

  NOTE: These CSS definitions are stored within the main page body so that you can use the phpBB2
  theme administration centre. When you have finalised your style you could cut the final CSS code
  and place it in an external file, deleting this section to save bandwidth.
*/
 
 
 /* General page style. The scroll bar colours only visible in IE5.5+ */
body { 
	background-color: <?php echo isset($this->vars['T_BODY_BGCOLOR']) ? $this->vars['T_BODY_BGCOLOR'] : $this->lang('T_BODY_BGCOLOR'); ?>;
	scrollbar-face-color: <?php echo isset($this->vars['T_TR_COLOR2']) ? $this->vars['T_TR_COLOR2'] : $this->lang('T_TR_COLOR2'); ?>;
	scrollbar-highlight-color: <?php echo isset($this->vars['T_TD_COLOR2']) ? $this->vars['T_TD_COLOR2'] : $this->lang('T_TD_COLOR2'); ?>;
	scrollbar-shadow-color: <?php echo isset($this->vars['T_TR_COLOR2']) ? $this->vars['T_TR_COLOR2'] : $this->lang('T_TR_COLOR2'); ?>;
	scrollbar-3dlight-color: <?php echo isset($this->vars['T_TR_COLOR3']) ? $this->vars['T_TR_COLOR3'] : $this->lang('T_TR_COLOR3'); ?>;
	scrollbar-arrow-color:  <?php echo isset($this->vars['T_BODY_LINK']) ? $this->vars['T_BODY_LINK'] : $this->lang('T_BODY_LINK'); ?>;
	scrollbar-track-color: <?php echo isset($this->vars['T_TR_COLOR1']) ? $this->vars['T_TR_COLOR1'] : $this->lang('T_TR_COLOR1'); ?>;
	scrollbar-darkshadow-color: <?php echo isset($this->vars['T_TH_COLOR1']) ? $this->vars['T_TH_COLOR1'] : $this->lang('T_TH_COLOR1'); ?>;
}

/* General font families for common tags */
font,th,td,p { font-family: <?php echo isset($this->vars['T_FONTFACE1']) ? $this->vars['T_FONTFACE1'] : $this->lang('T_FONTFACE1'); ?> }
p, td		{ font-size : <?php echo isset($this->vars['T_FONTSIZE2']) ? $this->vars['T_FONTSIZE2'] : $this->lang('T_FONTSIZE2'); ?>; color : <?php echo isset($this->vars['T_BODY_TEXT']) ? $this->vars['T_BODY_TEXT'] : $this->lang('T_BODY_TEXT'); ?>; }
a:link,a:active,a:visited { color : <?php echo isset($this->vars['T_BODY_LINK']) ? $this->vars['T_BODY_LINK'] : $this->lang('T_BODY_LINK'); ?>; }
a:hover		{ text-decoration: underline; color : <?php echo isset($this->vars['T_BODY_HLINK']) ? $this->vars['T_BODY_HLINK'] : $this->lang('T_BODY_HLINK'); ?>; }
hr	{ height: 0px; border: solid <?php echo isset($this->vars['T_TR_COLOR3']) ? $this->vars['T_TR_COLOR3'] : $this->lang('T_TR_COLOR3'); ?> 0px; border-top-width: 1px;}
h1,h2		{ font-family: "Trebuchet MS", Verdana, Arial, Helvetica, sans-serif; font-size : 22px; font-weight : bold; text-decoration : none; line-height : 120%; color : #000000;}


/* This is the border line & background colour round the entire page */
$bodyline { background-color: <?php echo isset($this->vars['T_TD_COLOR2']) ? $this->vars['T_TD_COLOR2'] : $this->lang('T_TD_COLOR2'); ?>; border: 1px <?php echo isset($this->vars['T_TH_COLOR1']) ? $this->vars['T_TH_COLOR1'] : $this->lang('T_TH_COLOR1'); ?> solid; }

/* This is the outline round the main forum tables */
$forumline { background-color: <?php echo isset($this->vars['T_TD_COLOR2']) ? $this->vars['T_TD_COLOR2'] : $this->lang('T_TD_COLOR2'); ?>; border: 2px <?php echo isset($this->vars['T_TH_COLOR2']) ? $this->vars['T_TH_COLOR2'] : $this->lang('T_TH_COLOR2'); ?> solid; }


/* Main table cell colours and backgrounds */
td.row1	{ background-color: <?php echo isset($this->vars['T_TR_COLOR1']) ? $this->vars['T_TR_COLOR1'] : $this->lang('T_TR_COLOR1'); ?>; }
td.row2	{ background-color: <?php echo isset($this->vars['T_TR_COLOR2']) ? $this->vars['T_TR_COLOR2'] : $this->lang('T_TR_COLOR2'); ?>; }
td.row3	{ background-color: <?php echo isset($this->vars['T_TR_COLOR3']) ? $this->vars['T_TR_COLOR3'] : $this->lang('T_TR_COLOR3'); ?>; }


/*
  This is for the table cell above the Topics, Post & Last posts on the index.php page
  By default this is the fading out gradiated silver background.
  However, you could replace this with a bitmap specific for each forum
*/
td.rowpic {
		background-color: <?php echo isset($this->vars['T_TD_COLOR2']) ? $this->vars['T_TD_COLOR2'] : $this->lang('T_TD_COLOR2'); ?>;
		background-image: url(../templates/subSilver/images/<?php echo isset($this->vars['T_TH_CLASS3']) ? $this->vars['T_TH_CLASS3'] : $this->lang('T_TH_CLASS3'); ?>);
		background-repeat: repeat-y;
}

/* Header cells - the blue and silver gradient backgrounds */
th	{
	color: <?php echo isset($this->vars['T_FONTCOLOR3']) ? $this->vars['T_FONTCOLOR3'] : $this->lang('T_FONTCOLOR3'); ?>; font-size: <?php echo isset($this->vars['T_FONTSIZE2']) ? $this->vars['T_FONTSIZE2'] : $this->lang('T_FONTSIZE2'); ?>px; font-weight : bold; 
	background-color: <?php echo isset($this->vars['T_BODY_LINK']) ? $this->vars['T_BODY_LINK'] : $this->lang('T_BODY_LINK'); ?>; height: 25px;
	background-image: url(../templates/subSilver/images/<?php echo isset($this->vars['T_TH_CLASS2']) ? $this->vars['T_TH_CLASS2'] : $this->lang('T_TH_CLASS2'); ?>);
}

td.cat,td.catHead,td.catSides,td.catLeft,td.catRight,td.catBottom {
			background-image: url(../templates/subSilver/images/<?php echo isset($this->vars['T_TH_CLASS1']) ? $this->vars['T_TH_CLASS1'] : $this->lang('T_TH_CLASS1'); ?>);
			background-color:<?php echo isset($this->vars['T_TR_COLOR3']) ? $this->vars['T_TR_COLOR3'] : $this->lang('T_TR_COLOR3'); ?>; border: <?php echo isset($this->vars['T_TH_COLOR3']) ? $this->vars['T_TH_COLOR3'] : $this->lang('T_TH_COLOR3'); ?>; border-style: solid; height: 28px;
}


/*
  Setting additional nice inner borders for the main table cells.
  The names indicate which sides the border will be on.
  Don't worry if you don't understand this, just ignore it :-)
*/
td.cat,td.catHead,td.catBottom {
	height: 29px;
	border-width: 0px 0px 0px 0px;
}
th.thHead,th.thSides,th.thTop,th.thLeft,th.thRight,th.thBottom,th.thCornerL,th.thCornerR {
	font-weight: bold; border: <?php echo isset($this->vars['T_TD_COLOR2']) ? $this->vars['T_TD_COLOR2'] : $this->lang('T_TD_COLOR2'); ?>; border-style: solid; height: 28px; }
td.row3Right,td.spaceRow {
	background-color: <?php echo isset($this->vars['T_TR_COLOR3']) ? $this->vars['T_TR_COLOR3'] : $this->lang('T_TR_COLOR3'); ?>; border: <?php echo isset($this->vars['T_TH_COLOR3']) ? $this->vars['T_TH_COLOR3'] : $this->lang('T_TH_COLOR3'); ?>; border-style: solid; }

th.thHead,td.catHead { font-size: <?php echo isset($this->vars['T_FONTSIZE3']) ? $this->vars['T_FONTSIZE3'] : $this->lang('T_FONTSIZE3'); ?>px; border-width: 1px 1px 0px 1px; }
th.thSides,td.catSides,td.spaceRow	 { border-width: 0px 1px 0px 1px; }
th.thRight,td.catRight,td.row3Right	 { border-width: 0px 1px 0px 0px; }
th.thLeft,td.catLeft	  { border-width: 0px 0px 0px 1px; }
th.thBottom,td.catBottom  { border-width: 0px 1px 1px 1px; }
th.thTop	 { border-width: 1px 0px 0px 0px; }
th.thCornerL { border-width: 1px 0px 0px 1px; }
th.thCornerR { border-width: 1px 1px 0px 0px; }


/* The largest text used in the index page title and toptic title etc. */
$maintitle {
			font-weight: bold; font-size: 22px; font-family: "<?php echo isset($this->vars['T_FONTFACE2']) ? $this->vars['T_FONTFACE2'] : $this->lang('T_FONTFACE2'); ?>",<?php echo isset($this->vars['T_FONTFACE1']) ? $this->vars['T_FONTFACE1'] : $this->lang('T_FONTFACE1'); ?>;
			text-decoration: none; line-height : 120%; color : <?php echo isset($this->vars['T_BODY_TEXT']) ? $this->vars['T_BODY_TEXT'] : $this->lang('T_BODY_TEXT'); ?>;
}


/* General text */
$gen { font-size : <?php echo isset($this->vars['T_FONTSIZE3']) ? $this->vars['T_FONTSIZE3'] : $this->lang('T_FONTSIZE3'); ?>px; }
$genmed { font-size : <?php echo isset($this->vars['T_FONTSIZE2']) ? $this->vars['T_FONTSIZE2'] : $this->lang('T_FONTSIZE2'); ?>px; }
$gensmall { font-size : <?php echo isset($this->vars['T_FONTSIZE1']) ? $this->vars['T_FONTSIZE1'] : $this->lang('T_FONTSIZE1'); ?>px; }
$gen $genmed gensmall|length { color : <?php echo isset($this->vars['T_BODY_TEXT']) ? $this->vars['T_BODY_TEXT'] : $this->lang('T_BODY_TEXT'); ?>; }
a.gen,a.genmed,a.gensmall { color: <?php echo isset($this->vars['T_BODY_LINK']) ? $this->vars['T_BODY_LINK'] : $this->lang('T_BODY_LINK'); ?>; text-decoration: none; }
a.gen:hover,a.genmed:hover,a.gensmall:hover	{ color: <?php echo isset($this->vars['T_BODY_HLINK']) ? $this->vars['T_BODY_HLINK'] : $this->lang('T_BODY_HLINK'); ?>; text-decoration: underline; }


/* The register, login, search etc links at the top of the page */
$mainmenu 	{ font-size : <?php echo isset($this->vars['T_FONTSIZE2']) ? $this->vars['T_FONTSIZE2'] : $this->lang('T_FONTSIZE2'); ?>px; color : <?php echo isset($this->vars['T_BODY_TEXT']) ? $this->vars['T_BODY_TEXT'] : $this->lang('T_BODY_TEXT'); ?> }
a.mainmenu		{ text-decoration: none; color : <?php echo isset($this->vars['T_BODY_LINK']) ? $this->vars['T_BODY_LINK'] : $this->lang('T_BODY_LINK'); ?>;  }
a.mainmenu:hover{ text-decoration: underline; color : <?php echo isset($this->vars['T_BODY_HLINK']) ? $this->vars['T_BODY_HLINK'] : $this->lang('T_BODY_HLINK'); ?>; }


/* Forum category titles */
$cattitle 	{ font-weight: bold; font-size: <?php echo isset($this->vars['T_FONTSIZE3']) ? $this->vars['T_FONTSIZE3'] : $this->lang('T_FONTSIZE3'); ?>px ; letter-spacing: 1px; color : <?php echo isset($this->vars['T_BODY_LINK']) ? $this->vars['T_BODY_LINK'] : $this->lang('T_BODY_LINK'); ?>}
a.cattitle		{ text-decoration: none; color : <?php echo isset($this->vars['T_BODY_LINK']) ? $this->vars['T_BODY_LINK'] : $this->lang('T_BODY_LINK'); ?>; }
a.cattitle:hover{ text-decoration: underline; }


/* Forum title: Text and link to the forums used in: index.php */
$forumlink 	{ font-weight: bold; font-size: <?php echo isset($this->vars['T_FONTSIZE3']) ? $this->vars['T_FONTSIZE3'] : $this->lang('T_FONTSIZE3'); ?>px; color : <?php echo isset($this->vars['T_BODY_LINK']) ? $this->vars['T_BODY_LINK'] : $this->lang('T_BODY_LINK'); ?>; }
a.forumlink 	{ text-decoration: none; color : <?php echo isset($this->vars['T_BODY_LINK']) ? $this->vars['T_BODY_LINK'] : $this->lang('T_BODY_LINK'); ?>; }
a.forumlink:hover{ text-decoration: underline; color : <?php echo isset($this->vars['T_BODY_HLINK']) ? $this->vars['T_BODY_HLINK'] : $this->lang('T_BODY_HLINK'); ?>; }


/* Used for the navigation text, (Page 1,2,3 etc) and the navigation bar when in a forum */
$nav 		{ font-weight: bold; font-size: <?php echo isset($this->vars['T_FONTSIZE2']) ? $this->vars['T_FONTSIZE2'] : $this->lang('T_FONTSIZE2'); ?>px; color : <?php echo isset($this->vars['T_BODY_TEXT']) ? $this->vars['T_BODY_TEXT'] : $this->lang('T_BODY_TEXT'); ?>;}
a.nav			{ text-decoration: none; color : <?php echo isset($this->vars['T_BODY_LINK']) ? $this->vars['T_BODY_LINK'] : $this->lang('T_BODY_LINK'); ?>; }
a.nav:hover		{ text-decoration: underline; }



/* Name of poster in viewmsg.php and viewtopic.php and other places */
$name 		{ font-size : <?php echo isset($this->vars['T_FONTSIZE2']) ? $this->vars['T_FONTSIZE2'] : $this->lang('T_FONTSIZE2'); ?>px; color : <?php echo isset($this->vars['T_BODY_TEXT']) ? $this->vars['T_BODY_TEXT'] : $this->lang('T_BODY_TEXT'); ?>;}

/* Location, number of posts, post date etc */
$postdetails 	{ font-size : <?php echo isset($this->vars['T_FONTSIZE1']) ? $this->vars['T_FONTSIZE1'] : $this->lang('T_FONTSIZE1'); ?>px; color : <?php echo isset($this->vars['T_BODY_TEXT']) ? $this->vars['T_BODY_TEXT'] : $this->lang('T_BODY_TEXT'); ?>; }


/* The content of the posts (body of text) */
$postbody { font-size : <?php echo isset($this->vars['T_FONTSIZE3']) ? $this->vars['T_FONTSIZE3'] : $this->lang('T_FONTSIZE3'); ?>px; line-height: 18px}
a.postlink:link	{ text-decoration: none; color : <?php echo isset($this->vars['T_BODY_LINK']) ? $this->vars['T_BODY_LINK'] : $this->lang('T_BODY_LINK'); ?> }
a.postlink:visited { text-decoration: none; color : <?php echo isset($this->vars['T_BODY_VLINK']) ? $this->vars['T_BODY_VLINK'] : $this->lang('T_BODY_VLINK'); ?>; }
a.postlink:hover { text-decoration: underline; color : <?php echo isset($this->vars['T_BODY_HLINK']) ? $this->vars['T_BODY_HLINK'] : $this->lang('T_BODY_HLINK'); ?>}


/* Quote & Code blocks */
$code { 
	font-family: <?php echo isset($this->vars['T_FONTFACE3']) ? $this->vars['T_FONTFACE3'] : $this->lang('T_FONTFACE3'); ?>; font-size: <?php echo isset($this->vars['T_FONTSIZE2']) ? $this->vars['T_FONTSIZE2'] : $this->lang('T_FONTSIZE2'); ?>px; color: <?php echo isset($this->vars['T_FONTCOLOR2']) ? $this->vars['T_FONTCOLOR2'] : $this->lang('T_FONTCOLOR2'); ?>;
	background-color: <?php echo isset($this->vars['T_TD_COLOR1']) ? $this->vars['T_TD_COLOR1'] : $this->lang('T_TD_COLOR1'); ?>; border: <?php echo isset($this->vars['T_TR_COLOR3']) ? $this->vars['T_TR_COLOR3'] : $this->lang('T_TR_COLOR3'); ?>; border-style: solid;
	border-left-width: 1px; border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px
}

$quote {
	font-family: <?php echo isset($this->vars['T_FONTFACE1']) ? $this->vars['T_FONTFACE1'] : $this->lang('T_FONTFACE1'); ?>; font-size: <?php echo isset($this->vars['T_FONTSIZE2']) ? $this->vars['T_FONTSIZE2'] : $this->lang('T_FONTSIZE2'); ?>px; color: <?php echo isset($this->vars['T_FONTCOLOR1']) ? $this->vars['T_FONTCOLOR1'] : $this->lang('T_FONTCOLOR1'); ?>; line-height: 125%;
	background-color: <?php echo isset($this->vars['T_TD_COLOR1']) ? $this->vars['T_TD_COLOR1'] : $this->lang('T_TD_COLOR1'); ?>; border: <?php echo isset($this->vars['T_TR_COLOR3']) ? $this->vars['T_TR_COLOR3'] : $this->lang('T_TR_COLOR3'); ?>; border-style: solid;
	border-left-width: 1px; border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px
}


/* Copyright and bottom info */
$copyright 	{ font-size: <?php echo isset($this->vars['T_FONTSIZE1']) ? $this->vars['T_FONTSIZE1'] : $this->lang('T_FONTSIZE1'); ?>px; font-family: <?php echo isset($this->vars['T_FONTFACE1']) ? $this->vars['T_FONTFACE1'] : $this->lang('T_FONTFACE1'); ?>; color: <?php echo isset($this->vars['T_FONTCOLOR1']) ? $this->vars['T_FONTCOLOR1'] : $this->lang('T_FONTCOLOR1'); ?>; letter-spacing: -1px;}
a.copyright		{ color: <?php echo isset($this->vars['T_FONTCOLOR1']) ? $this->vars['T_FONTCOLOR1'] : $this->lang('T_FONTCOLOR1'); ?>; text-decoration: none;}
a.copyright:hover { color: <?php echo isset($this->vars['T_BODY_TEXT']) ? $this->vars['T_BODY_TEXT'] : $this->lang('T_BODY_TEXT'); ?>; text-decoration: underline;}


/* Form elements */
input,textarea, select {
	color : <?php echo isset($this->vars['T_BODY_TEXT']) ? $this->vars['T_BODY_TEXT'] : $this->lang('T_BODY_TEXT'); ?>;
	font: normal <?php echo isset($this->vars['T_FONTSIZE2']) ? $this->vars['T_FONTSIZE2'] : $this->lang('T_FONTSIZE2'); ?>px <?php echo isset($this->vars['T_FONTFACE1']) ? $this->vars['T_FONTFACE1'] : $this->lang('T_FONTFACE1'); ?>;
	border-color : <?php echo isset($this->vars['T_BODY_TEXT']) ? $this->vars['T_BODY_TEXT'] : $this->lang('T_BODY_TEXT'); ?>;
}

/* The text input fields background colour */
input.post, textarea.post, select {
	background-color : <?php echo isset($this->vars['T_TD_COLOR2']) ? $this->vars['T_TD_COLOR2'] : $this->lang('T_TD_COLOR2'); ?>;
}

input { text-indent : 2px; }

/* The buttons used for bbCode styling in message post */
input.button {
	background-color : <?php echo isset($this->vars['T_TR_COLOR1']) ? $this->vars['T_TR_COLOR1'] : $this->lang('T_TR_COLOR1'); ?>;
	color : <?php echo isset($this->vars['T_BODY_TEXT']) ? $this->vars['T_BODY_TEXT'] : $this->lang('T_BODY_TEXT'); ?>;
	font-size: <?php echo isset($this->vars['T_FONTSIZE2']) ? $this->vars['T_FONTSIZE2'] : $this->lang('T_FONTSIZE2'); ?>px; font-family: <?php echo isset($this->vars['T_FONTFACE1']) ? $this->vars['T_FONTFACE1'] : $this->lang('T_FONTFACE1'); ?>;
}

/* The main submit button option */
input.mainoption {
	background-color : <?php echo isset($this->vars['T_TD_COLOR1']) ? $this->vars['T_TD_COLOR1'] : $this->lang('T_TD_COLOR1'); ?>;
	font-weight : bold;
}

/* None-bold submit button */
input.liteoption {
	background-color : <?php echo isset($this->vars['T_TD_COLOR1']) ? $this->vars['T_TD_COLOR1'] : $this->lang('T_TD_COLOR1'); ?>;
	font-weight : normal;
}

/* This is the line in the posting page which shows the rollover
  help line. This is actually a text box, but if set to be the same
  colour as the background no one will know ;)
*/
$helpline { background-color: <?php echo isset($this->vars['T_TR_COLOR2']) ? $this->vars['T_TR_COLOR2'] : $this->lang('T_TR_COLOR2'); ?>; border-style: none; }


/* Import the fancy styles for IE only (NS4.x doesn't use the @import function) */
@import url("../templates/subSilver/formIE.css"); 
-->
</style>
<title><?php echo isset($this->vars['SITENAME']) ? $this->vars['SITENAME'] : $this->lang('SITENAME'); ?> - <?php echo isset($this->vars['L_PHPBB_ADMIN']) ? $this->vars['L_PHPBB_ADMIN'] : $this->lang('L_PHPBB_ADMIN'); ?></title>
</head>
<body bgcolor="<?php echo isset($this->vars['T_BODY_BGCOLOR']) ? $this->vars['T_BODY_BGCOLOR'] : $this->lang('T_BODY_BGCOLOR'); ?>" text="<?php echo isset($this->vars['T_BODY_TEXT']) ? $this->vars['T_BODY_TEXT'] : $this->lang('T_BODY_TEXT'); ?>" link="<?php echo isset($this->vars['T_BODY_LINK']) ? $this->vars['T_BODY_LINK'] : $this->lang('T_BODY_LINK'); ?>" vlink="<?php echo isset($this->vars['T_BODY_VLINK']) ? $this->vars['T_BODY_VLINK'] : $this->lang('T_BODY_VLINK'); ?>">

<a name="top"></a>
