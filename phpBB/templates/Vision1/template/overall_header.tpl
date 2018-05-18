<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html dir="{S_CONTENT_DIRECTION}">
<head>
<meta http-equiv="Content-Type" content="text/html; charset={S_CONTENT_ENCODING}">
<meta http-equiv="Content-Style-Type" content="text/css">
{META}
{NAV_LINKS}
<title>{SITENAME} :: {PAGE_TITLE}</title>
<link rel="stylesheet" href="templates/Vision1/{T_HEAD_STYLESHEET}" type="text/css">
<!-- BEGIN switch_enable_pm_popup -->
<script language="Javascript" type="text/javascript">
<!--
	if ( {PRIVATE_MESSAGE_NEW_FLAG} )
	{
		window.open('{U_PRIVATEMSGS_POPUP}', '_phpbbprivmsg', 'HEIGHT=225,resizable=yes,WIDTH=400');
	}
//-->
</script>
<!-- END switch_enable_pm_popup -->
<script type="text/javascript" src="templates/Vision1/swfobject.js"></script>
</head>
<body bgcolor="{T_BODY_BGCOLOR}" text="{T_BODY_TEXT}" link="{T_BODY_LINK}" vlink="{T_BODY_VLINK}">
<a name="top"></a>
<table cellpadding="0" cellspacing="0" border="0" width="100%" height="100%" background="templates/Vision1/images/shadow_bg_top.gif" style="background-repeat: repeat-x;">
    <tr>
        <td width="50%" height="100%">
	    <table cellpadding="0" cellspacing="0" border="0" width="100%" height="100%">
	        <tr>
		    <td width="100%" height="204" valign="top" background="templates/Vision1/images/shadow_bg_top_left.jpg" style="background-repeat: no-repeat;background-position: 100% 0%"></td>
		</tr>
		<tr>
		    <td width="100%" height="100%" valign="top" background="templates/Vision1/images/shadow_bg_left.gif" style="background-repeat: repeat-y;background-position: 100% 0%"></td>
	        </tr>
	    </table>
	</td>
        <td width="780" height="100%" valign="top" bgcolor="{T_BODY_BGCOLOR}">
            <table width="780" cellspacing="0" cellpadding="0" border="0" align="center" height="100%">
                <tr>
                    <td height="160" width="780" background="templates/Vision1/images/header_background{DEMO}.jpg" valign="top">
                        <div style="position:absolute" align="center">
                        <table cellspacing="0" cellpadding="0" border="0" width="780" height="160">
                            <tr>
                                <td width="300" height="160">&nbsp;</td>
                                <td width="480" align="center" valign="middle" style="padding-right:20px;padding-left:20px">
                                    <span class="maintitle">{SITENAME}</span><p><span class="gen">{SITE_DESCRIPTION}</span> 
                                </td>
                            </tr>
                        </table>
                        </div>
                        <div id="header" style="position:absolute"></div>
                        <script type="text/javascript">
                        var header = new FlashObject("templates/Vision1/vision1.swf", "mymovie", "780", "160", "7", "#eeeeee");
                        header.addParam("quality", "high");
                        header.addParam("wmode", "transparent");
                        header.addParam("menu", "false");
                        header.write("header");
                        </script>
                    </td>
                </tr>
                <tr>
		    <td align="center" width="100%" valign="middle" height="25" colspan="2">
                        <table  width="100%" cellspacing="0" cellpadding="0" border="0" style="border-top:1px solid #D3E9F6">
			    <tr>
                                <td background="templates/Vision1/images/cellpic3.gif" height="25"><!--<img src="templates/Vision1/images/row_left.jpg" border="0" height="25" width="220">--></td>
			        <td align="center" nowrap="nowrap" background="templates/Vision1/images/cellpic3.gif">
				    <a href="{U_FAQ}" class="submenu">{L_FAQ}</a>
				    <a href="{U_SEARCH}" class="submenu">
				    {L_SEARCH}</a>
				    <a href="{U_MEMBERLIST}" class="submenu">{L_MEMBERLIST}</a>
				    <a href="{U_GROUP_CP}" class="submenu">{L_USERGROUPS}</a>
                                    <a href="{U_PROFILE}" class="submenu">{L_PROFILE}</a>
                                    <!-- BEGIN switch_user_logged_out -->
                                    <a href="{U_REGISTER}" class="submenu">{L_REGISTER}</a>
                                    <!-- END switch_user_logged_out -->
                                    <a href="{U_PRIVATEMSGS}" class="submenu">{PRIVATE_MESSAGE_INFO}</a>
                                    <a href="{U_LOGIN_LOGOUT}" class="submenu">{L_LOGIN_LOGOUT}</a>
			        </td>
			    </tr>
                            <tr><td height="5" class="shadow_bottom" colspan="2"></td></tr>
		        </table>
                    </td>
		</tr>
	        <tr> 
		    <td class="bodyline" colspan="2" style="padding:10px">