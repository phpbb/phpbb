<!-- INCLUDE ../common/lofi/lofi_header.tpl -->

<a href="{U_INDEX}">{L_INDEX}</a>{NAV_CAT_DESC}<br />
<span class="desc">{CURRENT_TIME}</span><br />
<div class="pagination">{PAGINATION}</div>
<div class="index">
	<a href="{U_POST_NEW_TOPIC}" class="nav">{L_POST_NEW_TOPIC}</a><br /><br />
	<!-- BEGIN topicrow -->
	<div style="padding-left: 5px; padding-right: 5px;">
		{topicrow.TOPIC_TYPE}<a href="{topicrow.U_VIEW_TOPIC}">{topicrow.TOPIC_TITLE}</a>
		<!-- IF topicrow.GOTO_PAGE --><span class="gotopage">{topicrow.GOTO_PAGE}</span><!-- ENDIF -->
		<span class="desc">({L_REPLIES}: {topicrow.REPLIES}
		{L_VIEWS}: {topicrow.VIEWS})</span>
	</div>
	<!-- END topicrow -->
	<br /><a href="{U_POST_NEW_TOPIC}" class="nav">{L_POST_NEW_TOPIC}</a>
	<br />
	<br />
	{JUMPBOX}
</div>
<br />

<!-- INCLUDE ../common/lofi/lofi_footer.tpl -->