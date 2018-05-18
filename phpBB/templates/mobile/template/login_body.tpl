<!-- INCLUDE overall_header.tpl -->

<form action="{S_LOGIN_ACTION}" method="post">
<div class="block">
<span class="gen">{L_USERNAME}:</span>&nbsp;<input type="text" name="username" class="post" size="32" maxlength="40" value="{USERNAME}" /><br />
<span class="gen">{L_PASSWORD}:</span>&nbsp;<input type="password" name="password" class="post" size="32" maxlength="32" /><br /><br />
<span class="gensmall"><a href="{U_REGISTER}" class="gensmall">{L_REGISTER}</a>&nbsp;&#8226;&nbsp;<a href="{U_SEND_PASSWORD}" class="gensmall">{L_SEND_PASSWORD}</a><!-- IF S_SWITCH_RESEND_ACTIVATION_EMAIL -->&nbsp;&#8226;&nbsp;<a href="{U_RESEND_ACTIVATION_EMAIL}" class="gensmall">{L_RESEND_ACTIVATION_EMAIL}</a><!-- ENDIF --></span><br /><br />
<!-- BEGIN switch_login_type -->
<span class="genmed">{L_STATUS}:<br />
<input type="radio" name="online_status" value="default" checked="checked" />&nbsp;{L_DEFAULT}&nbsp;&nbsp;<input type="radio" name="online_status" value="hidden" />&nbsp;{L_HIDDEN}&nbsp;&nbsp;<input type="radio" name="online_status" value="visible" />&nbsp;{L_VISIBLE}</span><br /><br />
<!-- END switch_login_type -->
<!-- BEGIN switch_allow_autologin -->
<span class="genmed"><input type="checkbox" name="autologin" checked="checked" />&nbsp;{L_AUTOLOGIN}</span><br /><br />
<!-- END switch_allow_autologin -->
{S_HIDDEN_FIELDS}
<input type="submit" name="login" class="mainoption" value="{L_LOGIN}" /><br />
</div>
</form>

<!-- INCLUDE overall_footer.tpl -->