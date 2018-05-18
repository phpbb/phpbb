<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html dir="{S_CONTENT_DIRECTION}">
<head>
<meta http-equiv="Content-Type" content="text/html; charset={S_CONTENT_ENCODING}">
<meta http-equiv="Content-Style-Type" content="text/css">
{META}
{NAV_LINKS}
<title>{SITENAME} :: {PAGE_TITLE}</title>
<link rel="stylesheet" href="templates/Vision2/{T_HEAD_STYLESHEET}" type="text/css">
<!-- BEGIN switch_enable_pm_popup -->
<script language="Javascript" type="text/javascript">
<!--
	if ( {PRIVATE_MESSAGE_NEW_FLAG} )
	{
		window.open('{U_PRIVATEMSGS_POPUP}', '_phpbbprivmsg', 'HEIGHT=225,resizable=yes,WIDTH=400');;
	}
//-->
</script>
<!-- END switch_enable_pm_popup -->
</head>
<body>
<table cellpadding="0" cellspacing="0" border="0" width="100%" height="100%" background="templates/Vision2/images/interface_top_bg.jpg" style="background-repeat: repeat-x;">
    <tr>
        <td width="50%" height="100%">
            <table cellpadding="0" cellspacing="0" border="0" width="100%" height="100%">
                <tr>
                    <td width="100%" height="352" valign="top" background="templates/Vision2/images/interface_top2.jpg" style="background-repeat: repeat-y;background-position: 100% 0%"></td>
                </tr>
                <tr>
                    <td width="100%" height="100%" valign="top" background="templates/Vision2/images/interface_shadow2.gif" style="background-repeat: repeat-y;background-position: 100% 0%"></td>
                </tr>
            </table>
	    </td>
        <td width="780" height="100%" valign="top">
            <table cellpadding="0" cellspacing="0" border="0" width="768" height="100%">
                <tr>
                    <td width="780" height="140" colspan="2" bgcolor="#00477D">
                        <table width="780" cellspacing="0" border="0" cellpadding="0" background="templates/Vision2/images/header_logo_bg.jpg">
                            <tr>
                                <td width="268" height="140"><a href="{U_INDEX}"><img src="templates/Vision2/images/header_logo.gif" border="0" height="140" alt="{L_INDEX}"></a></td>
                                <td width="512" height="140">
                                    <table width="512" cellspacing="0" border="0" cellpadding="0">
                                        <tr>
                                            <td></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td width="780" height="25" colspan="2" bgcolor="#FFFFFF" style="padding-left:10px"><span class="gen"><b>{SITENAME}</b> : {SITE_DESCRIPTION}</span></td>
                </tr>
                 <tr>
                    <td height="5" background="templates/Vision2/images/line_left_shadow.gif"></td>
                    <td height="5" background="templates/Vision2/images/line_shadow.gif"></td>
                </tr>
                <tr>
                    <td width="166" background="templates/Vision2/images/left_left_bg.gif" valign="top">
                        <table cellpadding="0" cellspacing="0" border="0" width="166">
                            <tr>
                                <td width="166"><img src="templates/Vision2/images/left_menu_members.gif" border="0" width="166" height="50"></td>
                            </tr>
                            <tr>
                                <td width="166" align="center">
				                    <table cellpadding="0" cellspacing="0" border="0" width="166">
				                        <tr>
					                        <td width="166" height="9"><img src="templates/Vision2/images/left_member_1.gif" width="166" height="9" border="0"></td>
					                    </tr>
					                    <tr>
					                        <td width="166" height="25" background="templates/Vision2/images/left_member_2.gif" align="center">
					                            <!-- BEGIN switch_user_logged_out -->
						                        <b>{L_LOGIN}</b>
						                        <!-- END switch_user_logged_out -->
						                        <!-- BEGIN switch_user_logged_in -->
                                                <a href="{U_LOGIN_LOGOUT}" class="mainmenu"><img src="templates/Vision2/images/icon_mini_login.gif" width="12" height="13" border="0" alt="{L_LOGIN_LOGOUT}" hspace="3" align="absmiddle">{L_LOGIN_LOGOUT}</a>
                                                <!-- END switch_user_logged_in -->
                                            </td>
										</tr>
										<!-- BEGIN switch_user_logged_out -->
										<form method="post" action="{S_LOGIN_ACTION}">
										<tr>
					    					<td width="166" height="80" background="templates/Vision2/images/left_member_3.gif" valign="top">
                                                <table cellpadding="4" cellspacing="0" border="0" width="166">
                                                    <tr>
                                                        <td width="60" align="right" style="padding-right:2px;font-size:11px;">{L_USERNAME}</td>
                                                        <td><input  type="text" name="username" style="width:80px" class="normal"></td>
                                                    </tr>
						    						<tr>
                                                        <td width="60" align="right" style="padding-right:2px;font-size:11px;">{L_PASSWORD}</td>
														<td><input type="password" name="password" style="width:80px" class="normal" maxlength="32"></td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2" align="right" style="padding-right:10px"><span style="font-size:80%">{L_AUTO_LOGIN}</span> <input class="text" type="checkbox" name="autologin"></td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2" align="center"><input type="submit" name="login" value="{L_LOGIN}" class="liteoption" value="{L_LOGIN}"></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
										</form>
										<!-- END switch_user_logged_out -->
										<!-- BEGIN switch_user_logged_in -->
                                        <tr>
					    					<td width="166" height="80" background="templates/Vision2/images/left_member_3.gif" valign="top" align="center">
                                                <table cellpadding="4" cellspacing="0" border="0" width="166" align="center">
                                                    <tr>
						        						<td colspan="2">
							    							<b>{L_PROFILE}</b><br>
                                                            <span class="gensmall">
							    							<a href="{U_PROFILE}" class="mainmenu">{L_PROFILE}</a><br>
                                                            <a href="{U_PRIVATEMSGS}" class="mainmenu">{PRIVATE_MESSAGE_INFO}</a>
                                                            </span>
														</td>
						    						</tr>
						    						<tr>
						       							 <td colspan="2">
							   							     <b>{L_SEARCH}</b><br>
                                                             <span class="gensmall">
							    							 <a href="{U_SEARCH_NEW}" class="mainmenu">{L_SEARCH_NEW}</a><br>
							    							 <a href="{U_SEARCH_SELF}" class="mainmenu">{L_SEARCH_SELF}</a><br>
						            						 <a href="{U_SEARCH_UNANSWERED}" class="mainmenu">{L_SEARCH_UNANSWERED}</a>
                                                             </span>
														 </td>
                                                    </tr>
						   						    <tr>
						        						<td colspan="2">
							    							<span class="gensmall">{LAST_VISIT_DATE}</span>
														</td>
                                                    </tr>
                                                </table>
                                            </td>
										</tr>
										<!-- END switch_user_logged_in -->
										<tr>
					    					<td width="166" height="11"><img src="templates/Vision2/images/left_member_4.gif" width="166" height="11" border="0"></td>
										</tr>
                                    </table>
								</td>
                            </tr>
                            <tr>
                                <td width="166"><img src="templates/Vision2/images/left_menu_products.gif" border="0" width="166" height="50"></td>
                            </tr>
                            <tr>
                                <td width="166" align="center" style="padding-top: 5px;">
				    				<table cellpadding="0" cellspacing="0" border="0" width="166">
				        				<tr>
					    					<td width="166" height="9"><img src="templates/Vision2/images/left_member_1.gif" width="166" height="9" border="0"></td>
										</tr>
										<tr>
					    					<td width="166" height="25" background="templates/Vision2/images/left_member_2.gif" align="center"></td>
										</tr>
				        				<tr>
					    					<td background="templates/Vision2/images/left_member_3.gif" valign="top" align="center">
                                                <table cellpadding="4" cellspacing="0" border="0" width="140">
                                                    <!-- BEGIN switch_user_logged_out -->
													<tr>
						        						<td><a href="{U_REGISTER}" class="mainmenu"><img src="templates/Vision2/images/icon_mini_register.gif" width="12" height="13" border="0" alt="{L_REGISTER}" hspace="3" align="absmiddle">{L_REGISTER}</a></td>
						    						</tr>
						    						<!-- END switch_user_logged_out -->
						    						<tr>
						        						<td><a href="{U_FAQ}" class="mainmenu"><img src="templates/Vision2/images/icon_mini_faq.gif" width="12" height="13" border="0" alt="{L_FAQ}" hspace="3" align="absmiddle">{L_FAQ}</a></td>
						    						</tr>
						    						<tr>
						        						<td><a href="{U_MEMBERLIST}" class="mainmenu"><img src="templates/Vision2/images/icon_mini_members.gif" width="12" height="13" border="0" alt="{L_MEMBERLIST}" hspace="3" align="absmiddle">{L_MEMBERLIST}</a></td>
					            					</tr>
						  						    <tr>
						        						<td><a href="{U_GROUP_CP}" class="mainmenu"><img src="templates/Vision2/images/icon_mini_groups.gif" width="12" height="13" border="0" alt="{L_USERGROUPS}" hspace="3" align="absmiddle">{L_USERGROUPS}</a></td>
						    						</tr>
                            						<tr>
						        						<td><a href="{U_SEARCH}" class="mainmenu"><img src="templates/Vision2/images/icon_mini_search.gif" width="12" height="13" border="0" alt="{L_SEARCH}" hspace="3" align="absmiddle">{L_SEARCH}</a></td>
						   						    </tr>
						    						<tr>
						        						<td><hr></td>
						    						</tr>
                                                </table>
                                            </td>
										</tr>
										<tr>
					    					<td width="166" height="11"><img src="templates/Vision2/images/left_member_4.gif" width="166" height="11" border="0"></td>
										</tr>
				    				</table>
								</td>
                            </tr>


                            <tr>
                                <td align="center">

                                </td>
                            </tr>
                            
                        </table>
                    </td>
                    <td width="624" valign="top">
                        <a name="top"></a>
                        <table width="100%" cellspacing="0" cellpadding="10" border="0" align="center" class="bodyline"> 
                            <tr> 
                                <td>