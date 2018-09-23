<!-- INCLUDE overall_header.tpl -->

<form action="{S_LOGIN_ACTION}" method="post">

{IMG_THL}{IMG_THC}<span class="forumlink">{L_ENTER_PASSWORD}</span>{IMG_THR}<table class="forumlinenb">
<!-- IF SOCIAL_CONNECT_LINK -->
<tr>
	<td class="row1" colspan="2">
		<img style="float: left; margin-right: 10px;" src="{U_PROFILE_PHOTO}" alt="{USER_REAL_NAME}" />
		<span class="genmed">
			{L_SOCIAL_CONNECT_LINK_ACCOUNT}<br />
			<img src="{U_SOCIAL_NETWORK_ICON}" alt="{SOCIAL_NETWORK_NAME}" title="{SOCIAL_NETWORK_NAME}" />&nbsp;<b>{USER_REAL_NAME}</b><br/>
			<a href="{U_PROFILE_LINK}" target="_blank">{U_PROFILE_LINK}</a>
		</span>
	</td>
</tr>
<tr><th class="tvalignm" colspan="2">{L_REGISTRATION_INFO}</th></tr>
<!-- ENDIF -->
<tr>
	<td class="row1g row-center tw150px" style="padding: 30px; width: 150px;"><img src="images/icy_phoenix_small.png" alt="" /></td>
	<td class="row1g" style="padding: 30px;">
		<table>
		<tr>
			<td class="tw120px tdnw" style="width: 120px; padding-bottom: 10px;"><span class="gen">{L_USERNAME}:</span></td>
			<td align="left" style="padding-bottom: 10px;"><input type="text" name="username" class="post" size="32" maxlength="40" value="{USERNAME}" /></td>
		</tr>
		<tr>
			<td class="tdnw"><span class="gen">{L_PASSWORD}:</span></td>
			<td><input type="password" name="password" class="post" size="32" maxlength="32" /></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td class="tdnw" style="padding-bottom: 20px;">
				<span class="gensmall">
					<a href="{U_REGISTER}" class="gensmall">{L_REGISTER}</a>&nbsp;&#8226;&nbsp;<a href="{U_SEND_PASSWORD}" class="gensmall">{L_SEND_PASSWORD}</a><!-- IF S_SWITCH_RESEND_ACTIVATION_EMAIL -->&nbsp;&#8226;&nbsp;<a href="{U_RESEND_ACTIVATION_EMAIL}" class="gensmall">{L_RESEND_ACTIVATION_EMAIL}</a><!-- ENDIF -->
				</span>
			</td>
		</tr>
		<!-- BEGIN switch_login_type -->
		<tr>
			<td>&nbsp;</td>
			<td class="tdnw"><span class="genmed">{L_STATUS}:&nbsp;&nbsp;<input type="radio" name="online_status" value="default" checked="checked" />&nbsp;{L_DEFAULT}&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="online_status" value="hidden" />&nbsp;{L_HIDDEN}&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="online_status" value="visible" />&nbsp;{L_VISIBLE}&nbsp;&nbsp;</span><br /><br /></td>
		</tr>
		<!-- END switch_login_type -->
		<!-- BEGIN switch_allow_autologin -->
		<tr>
			<td>&nbsp;</td>
			<td class="tdnw" style="padding-bottom: 10px;"><span class="genmed">&nbsp;<input type="checkbox" name="autologin" checked="checked" />&nbsp;{L_AUTOLOGIN}</span></td>
		</tr>
		<!-- END switch_allow_autologin -->
		<tr>
			<td>&nbsp;</td>
			<td style="padding-bottom: 10px;">{S_HIDDEN_FIELDS}<input type="submit" name="login" class="mainoption" value="{L_LOGIN}" /></td>
		</tr>
		</table>
	</td>
	<!-- IF SOCIAL_CONNECT -->
	<td class="row1g row-center tw150px" style="padding: 30px; width: 150px;">
		{L_SOCIAL_CONNECT}
		<!-- BEGIN social_connect_button -->
		<a href="{social_connect_button.U_SOCIAL_CONNECT}" title="{social_connect_button.L_SOCIAL_CONNECT}">{social_connect_button.IMG_SOCIAL_CONNECT}</a>
		<!-- END social_connect_button -->
	</td>
	<!-- ENDIF -->
</tr>
</table>{IMG_TFL}{IMG_TFC}{IMG_TFR}

</form>

<!-- INCLUDE overall_footer.tpl -->