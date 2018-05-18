<!-- BEGIN articles -->
<div class="block">
	<h2><a href="{articles.U_COMMENT}" class="forumlink">{articles.L_TITLE}</a></h2>
	<p class="post-time">{L_POSTED}&nbsp;{L_WORD_ON}&nbsp;{articles.POST_DATE}&nbsp;{L_BY}&nbsp;{articles.L_POSTER}</p>
	{articles.BODY}
	<p class="post-time">
		{articles.READ_MORE_LINK}
		<!-- IF not S_BOT -->
			<a href="{articles.U_POST_COMMENT}">{L_REPLY_NEWS}</a>
		<!-- ENDIF -->
	</p>
	<p class="post-time">
		{L_NEWS_SUMMARY} <!-- IF S_NEWS_VIEWS --><a href="{articles.U_VIEWS}"><!-- ENDIF -->{articles.COUNT_VIEWS} {L_NEWS_VIEWS}<!-- IF S_NEWS_VIEWS --></a><!-- ENDIF -->
		{L_NEWS_AND} <!-- IF not S_BOT --><a href="{INDEX_FILE}?{PORTAL_PAGE_ID}topic_id={articles.ID}" rel="nofollow" title="{articles.L_TITLE}"><!-- ENDIF -->{articles.COUNT_COMMENTS} {L_NEWS_COMMENTS}<!-- IF not S_BOT --></a><!-- ENDIF -->
	</p>
</div>
<!-- END articles --><!-- BEGIN comments -->
<div class="block">
	<h2>{comments.L_TITLE}</h2>
	<p class="post-time">
		{comments.POST_DATE} {L_BY} {comments.L_POSTER}
	</p>
	{comments.BODY}
</div>
<!-- END comments --><!-- BEGIN pagination --><div class="pagination">{pagination.PAGINATION}</div><!-- END pagination -->