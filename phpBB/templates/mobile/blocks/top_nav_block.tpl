<p class="nav-header"><a href="{U_PORTAL}" class="nav-current">{L_HOME}</a></p>
<p class="right">
	<!-- IF S_LOGGED_IN -->
	<a href="{U_RECENT}" class="gensmall">{L_RECENT}</a>&nbsp;|&nbsp;<a href="{U_SEARCH_NEW}">{L_SEARCH_NEW}</a><br />
	<a href="{U_SEARCH_SELF}">{L_SEARCH_SELF}</a>&nbsp;|&nbsp;<a href="{U_SEARCH_UNANSWERED}">{L_SEARCH_UNANSWERED}</a>
	<!-- ELSE -->
	<a href="{U_RECENT}" class="gensmall">{L_RECENT}</a>&nbsp;|&nbsp;<a href="{U_SEARCH_UNANSWERED}">{L_SEARCH_UNANSWERED}</a>
	<!-- ENDIF -->
</p>
<p>
	<!-- IF S_LOGGED_IN --><a href="{U_PRIVATEMSGS}">{PRIVATE_MESSAGE_INFO}</a><br /><!-- ENDIF -->
	{CURRENT_TIME} | {S_TIMEZONE}
</p>
<div class="clear"></div>