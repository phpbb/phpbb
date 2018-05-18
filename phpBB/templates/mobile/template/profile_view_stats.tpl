<!-- IF S_POSTS_SECTION -->
{BB_USAGE_STATS_TEMPLATE}
<!-- ENDIF -->

<!-- BEGIN switch_display_ips -->
{IMG_THL}{IMG_THC}<span class="forumlink">{L_OTHER_REGISTERED_IPS}</span>{IMG_THR}<table class="forumlinenb">
<tr>
	<th>{L_USERNAME}</th>
	<th>{L_REGISTERED_HOSTNAME}</th>
	<th>{L_TIME}</th>
</tr>
<!-- BEGIN switch_other_user_ips -->
<!-- BEGIN OTHER_REGISTERED_IPS -->
<tr>
	<td class="row1"><span class="genmed"><a href="{switch_display_ips.switch_other_user_ips.OTHER_REGISTERED_IPS.U_PROFILE}">{switch_display_ips.switch_other_user_ips.OTHER_REGISTERED_IPS.USER_NAME}</a></span></td>
	<td class="row1"><span class="genmed">{switch_display_ips.switch_other_user_ips.OTHER_REGISTERED_IPS.USER_HOSTNAME}</span></td>
	<td class="row1"><span class="genmed">{switch_display_ips.switch_other_user_ips.OTHER_REGISTERED_IPS.TIME}</span></td>
</tr>
<!-- END OTHER_REGISTERED_IPS -->
<!-- END switch_other_user_ips -->
<!-- BEGIN switch_no_other_registered_ips -->
<tr><td class="row1 row-center" colspan="3"><span class="genmed">{L_NO_OTHER_REGISTERED_IPS}</span></td></tr>
<!-- END switch_no_other_registered_ips -->
</table>{IMG_TFL}{IMG_TFC}{IMG_TFR}

<table>
<tr>
	<td><span class="gen">{USERS_PAGE_NUMBER}&nbsp;</span></td>
	<td class="tdalignr tdnw"><span class="pagination">&nbsp;{USERS_PAGINATION}</span><br /></td>
</tr>
</table>

<br />

{IMG_THL}{IMG_THC}<span class="forumlink">{L_OTHER_IPS}</span>{IMG_THR}<table class="forumlinenb">
<!-- BEGIN switch_other_posted_ips -->
<!-- BEGIN ALL_IPS_POSTED_FROM -->
<tr>
	<td class="row1"><span class="genmed"><a href="{switch_display_ips.switch_other_posted_ips.ALL_IPS_POSTED_FROM.U_POSTER_IP}" target="_blank">{switch_display_ips.switch_other_posted_ips.ALL_IPS_POSTED_FROM.POSTER_IP}</a> [ <a href="{switch_display_ips.switch_other_posted_ips.ALL_IPS_POSTED_FROM.U_POSTS_LINK}">{switch_display_ips.switch_other_posted_ips.ALL_IPS_POSTED_FROM.POSTS}</a> ] </span></td>
</tr>
<!-- END ALL_IPS_POSTED_FROM -->
<!-- END switch_other_posted_ips -->
<!-- BEGIN switch_no_other_posted_ips -->
<tr>
	<td class="row1 row-center"><span class="genmed">{L_NO_OTHER_POSTED_IPS}</span></td>
</tr>
<!-- END switch_no_other_posted_ips -->
</table>{IMG_TFL}{IMG_TFC}{IMG_TFR}

<table>
<tr>
	<td><span class="gen">{IPS_PAGE_NUMBER}&nbsp;</span></td>
	<td class="tdalignr tdnw"><span class="pagination">&nbsp;{IPS_PAGINATION}</span></td>
</tr>
</table>

<br />

<!-- IF S_LOGINS_HISTORY -->
{IMG_THL}{IMG_THC}<span class="forumlink">{L_LOGINS}</span>{IMG_THR}<table class="forumlinenb">
<tr>
	<th>{L_IP}</th>
	<th>{L_BROWSER}</th>
	<th>{L_TIME}</th>
</tr>
<!-- BEGIN USER_LOGINS -->
<tr>
	<td class="row1"><span class="genmed"><a href="{switch_display_ips.USER_LOGINS.U_IP}" target="_blank">{switch_display_ips.USER_LOGINS.IP}</a></span></td>
	<td class="row1"><span class="genmed">{switch_display_ips.USER_LOGINS.USER_AGENT}</span></td>
	<td class="row1"><span class="genmed">{switch_display_ips.USER_LOGINS.LOGIN_TIME}</span></td>
</tr>
<!-- END USER_LOGINS -->
<!-- BEGIN switch_no_logins -->
<tr><td class="row1 row-center" colspan="3"><span class="genmed">{L_NO_LOGINS}</span></td></tr>
<!-- END switch_no_logins -->
</table>{IMG_TFL}{IMG_TFC}{IMG_TFR}

<table>
<tr>
	<td><span class="gen">{LOGINS_PAGE_NUMBER}&nbsp;</span></td>
	<td class="tdalignr tdnw"><span class="pagination">&nbsp;{LOGINS_PAGINATION}</span><br /><span class="gensmall">{S_TIMEZONE}</span></td>
</tr>
</table>
<!-- ENDIF -->
<!-- END switch_display_ips -->
