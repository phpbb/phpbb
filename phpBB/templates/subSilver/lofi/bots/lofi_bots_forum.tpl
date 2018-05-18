<!-- INCLUDE ../common/lofi/bots/lofi_bots_header.tpl -->

<b>{FORUM_NAME}</b>
<div class="nav"><a href="{U_INDEX}">{L_INDEX}</a>{NAV_CAT_DESC}<br />{CURRENT_TIME}</div>
<div class="pagination">{PAGINATION}</div>
<div class="index">
	<ul class="forumrow">
	<!-- BEGIN topicrow -->
	<li >
		<a href="{topicrow.U_VIEW_TOPIC}">{topicrow.TOPIC_TITLE}</a>
		<span class="gotopage">{topicrow.GOTO_PAGE}</span>
		<span class="desc">({L_REPLIES}: {topicrow.REPLIES} {L_VIEWS}: {topicrow.VIEWS})</span>
	</li>
	<!-- END topicrow -->
	</ul>
	<br />
	{JUMPBOX}
</div>
<br />

<!-- INCLUDE ../common/lofi/bots/lofi_bots_footer.tpl -->