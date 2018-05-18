<!-- INCLUDE ../common/lofi/bots/lofi_bots_header.tpl -->

<b>{TOPIC_TITLE}</b>
<div class="nav"><a href="{U_INDEX}">{L_INDEX}</a>{NAV_CAT_DESC}</div><br />
<div class="pagination">{PAGINATION}</div>
<div class="index">
	<!-- BEGIN postrow -->
	<div class="postwrapper">
		<div class="posttopbar">
			<div class="postname">{postrow.POSTER_NAME}</div>
			<div class="postdate">{postrow.POST_DATE}</div>
		</div>
		<span class="desc">{L_SUBJECT}: {postrow.POST_SUBJECT}</span>
		<div class="postcontent">{postrow.MESSAGE}</div><br />
		<!-- IF postrow.EDITED_MESSAGE --><span class="signature">{postrow.EDITED_MESSAGE}</span><!-- ENDIF -->
	</div>
	<!-- END postrow -->
	<div class="pagination">{PAGINATION}</div><br />
	{JUMPBOX}
</div>
<br />

<!-- INCLUDE ../common/lofi/bots/lofi_bots_footer.tpl -->