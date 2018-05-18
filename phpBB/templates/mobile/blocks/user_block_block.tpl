<!-- IF not S_LOGGED_IN -->
<form method="post" action="{S_LOGIN_ACTION}">
	<input type="hidden" name="redirect" value="{U_PORTAL_NOSID}" />
	{L_USERNAME}: <input class="post" type="text" name="username" size="15" />
	<br />
	{L_PASSWORD}: <input class="post" type="password" name="password" size="15" />
	<br />
	<!-- BEGIN switch_allow_autologin -->
	<input class="text" type="checkbox" name="autologin" /><span class="gensmall">&nbsp;{L_REMEMBER_ME}</span><br />
	<!-- END switch_allow_autologin -->
	<input type="submit" class="mainoption" name="login" value="{L_LOGIN}" /><br />
	<a href="{U_SEND_PASSWORD}" class="gensmall">{L_SEND_PASSWORD}</a><br />
	<span class="gensmall">{L_REGISTER_NEW_ACCOUNT}</span>
</form>
<!-- ENDIF -->