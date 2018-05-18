<!-- INCLUDE ../common/lofi/lofi_header.tpl -->

<div class="nav"><a href="{U_INDEX}">{L_INDEX}</a>{NAV_CAT_DESC}</div>
<span class="pagination">{CURRENT_TIME}</span><br />
<div class="pagination">
	<!-- IF S_LOGGED_IN --><a href="{U_SEARCH_NEW}">{L_SEARCH_NEW}</a><br /><!-- ENDIF -->
	<a href="{U_SEARCH_UNANSWERED}">{L_SEARCH_UNANSWERED}</a>
</div>
<div class="index">
<!-- BEGIN catrow -->
<!-- BEGIN cathead -->
<br />
<b><a href="{catrow.cathead.U_VIEWCAT}" class="cattitle" title="{catrow.cathead.CAT_DESC}">{catrow.cathead.CAT_TITLE}</a><br /><span class="desc">{catrow.cathead.CAT_DESC}</span></b>
<!-- END cathead -->
<!-- BEGIN forumrow -->
<div style="padding-left: 15px; padding-right: 15px;">
	<a href="{catrow.forumrow.U_VIEWFORUM}">{catrow.forumrow.FORUM_NAME}</a><br />
	<b><span class="desc">({catrow.forumrow.FORUM_DESC}){catrow.forumrow.L_LINKS}{catrow.forumrow.LINKS}</span><br /></b>
	<span class="desc">{L_TOPICS}/{L_POSTS}: {catrow.forumrow.TOPICS}/{catrow.forumrow.POSTS}</span><br />
</div>
<!-- END forumrow -->
<!-- END catrow -->
</div>
<br />
<!-- IF S_VIEWONLINE -->
<div class="whosonline">
	{L_WHO_IS_ONLINE}<br />
	{TOTAL_POSTS}<br />{TOTAL_USERS}<br />{NEWEST_USER}<br />
	{TOTAL_USERS_ONLINE}<br />{RECORD_USERS}<br />{LOGGED_IN_USER_LIST}<br />{L_ONLINE_EXPLAIN}
</div>
<br />
<!-- ENDIF -->

<!-- INCLUDE ../common/lofi/lofi_footer.tpl -->