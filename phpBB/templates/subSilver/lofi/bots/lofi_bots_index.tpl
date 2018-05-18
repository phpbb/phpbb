<!-- INCLUDE ../common/lofi/bots/lofi_bots_header.tpl -->

<div class="nav"><a href="{U_INDEX}">{L_INDEX}</a>{NAV_CAT_DESC}</div>
<span class="pagination">{CURRENT_TIME}</span><br />
<div class="pagination"></div>
<div class="index">
<!-- BEGIN catrow -->

<!-- BEGIN cathead -->
<p></p>
<b><a href="{catrow.cathead.U_VIEWCAT}" class="cattitle" title="{catrow.cathead.CAT_DESC}">{catrow.cathead.CAT_TITLE}</a><br /><span class="desc">{catrow.cathead.CAT_DESC}</span></b>
<!-- END cathead -->
<!-- BEGIN forumrow -->
<div style="padding-left: 15pt; padding-right: 15pt;">
	<a href="{catrow.forumrow.U_VIEWFORUM}">{catrow.forumrow.FORUM_NAME}</a><br />
	<b><span class="desc">({catrow.forumrow.FORUM_DESC}){catrow.forumrow.L_LINKS}{catrow.forumrow.LINKS}<br /></span></b>
	<span class="desc">{L_TOPICS}/{L_POSTS}: {catrow.forumrow.TOPICS}/{catrow.forumrow.POSTS}</span> <br />
</div>
<!-- END forumrow -->
<!-- END catrow -->
</div>

<!-- INCLUDE ../common/lofi/bots/lofi_bots_footer.tpl -->