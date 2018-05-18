<!-- INCLUDE ../common/lofi/lofi_header.tpl -->

<a href="{U_INDEX}">{L_INDEX}</a>{NAV_CAT_DESC}<br />
<span class="desc">{CURRENT_TIME}</span><br />
<br />
<div style="width: 100%; margin: 0 auto; text-align: center;">
	<form name="form" method="get" action="{FORM_ACTION}">
		{L_SELECT_MODE}
		[ <a href="{FORM_ACTION}?mode=today">{L_TODAY}</a> ]
		[ <a href="{FORM_ACTION}?mode=yesterday">{L_YESTERDAY}</a> ]
		[ <a href="{FORM_ACTION}?mode=last24">{L_LAST24}</a> ]
		[ <a href="{FORM_ACTION}?mode=lastweek">{L_LASTWEEK}</a> ]
		[ <a href="#">{L_LAST}</a> <input type="hidden" name="mode" value="lastXdays" />
		<input type="text" name="amount_days" size="2" value="{AMOUNT_DAYS}" maxlength="3" class="post" />
		<a href="javascript:document.form.submit();">{L_DAYS}</a> ]
	</form>
</div>
<br />
<div class="pagination">{PAGINATION}</div>
<div class="index">
	<!-- BEGIN recent -->
	<div style="padding-left: 5px; padding-right: 5px;">
		{recent.TOPIC_TYPE}<a href="{recent.U_VIEW_TOPIC}">{recent.TOPIC_TITLE}</a>
		<!-- IF recent.GOTO_PAGE --><span class="gotopage">{recent.GOTO_PAGE}</span><!-- ENDIF -->
		<span class="desc">({L_REPLIES}: {recent.REPLIES} {L_VIEWS}: {recent.VIEWS}) {recent.LAST_URL}</span>
	</div>
	<!-- END recent -->
	<br />
	<br />
	{JUMPBOX}
</div>
<br />

<!-- INCLUDE ../common/lofi/lofi_footer.tpl -->