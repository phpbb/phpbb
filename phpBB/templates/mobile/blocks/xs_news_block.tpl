<div id="xs_news_h" style="display: none;">
{IMG_THL}{IMG_THC}<img class="max-min-right" style="{SHOW_HIDE_PADDING}" src="{IMG_MAXIMISE}" onclick="ShowHide('xs_news','xs_news_h','xs_news');" alt="{L_SHOW}" /><span class="forumlink">{NEWS_TITLE}</span>{IMG_THR_ALT}<table class="forumlinenb">
<tr><td>&nbsp;</td></tr>
</table>{IMG_TFL}{IMG_TFC}{IMG_TFR}
</div>
<div id="xs_news">
<script type="text/javascript">
<!--
tmp = 'xs_news';
if(GetCookie(tmp) == '2')
{
	ShowHide('xs_news', 'xs_news_h', 'xs_news');
}
//-->
</script>
{IMG_THL}{IMG_THC}<img class="max-min-right" style="{SHOW_HIDE_PADDING}" src="{IMG_MINIMISE}" onclick="ShowHide('xs_news','xs_news_h','xs_news');" alt="{L_HIDE}" /><span class="forumlink">{NEWS_TITLE}</span>{IMG_THR}<table class="forumlinenb">
<tr>
	<td>

	<!-- BEGIN switch_news_ticker -->
		<!-- BEGIN switch_ticker_subtitle -->
		<p class="subtitle" style="height:18px;">{XS_NEWS_TICKERS_TITLE}</p>
		<!-- END switch_ticker_subtitle -->
		<!-- BEGIN news_ticker_row -->
			<!-- BEGIN switch_show_feed -->
			<p class="forum-buttons2" style="height:17px;valign:middle;"><span>{news_ticker_row.XS_NEWS_TICKER_FROM}</span></p>
			<!-- END switch_show_feed -->
			<p class="row1 row-news-tickers">
				<span class="nav-div" {news_ticker_row.XS_NEWS_TICKER_FONTSIZE}>
					<marquee name="{news_ticker_row.XS_NEWS_TICKER_ID}" id="{news_ticker_row.XS_NEWS_TICKER_ID}" behavior="scroll" direction="{news_ticker_row.XS_NEWS_TICKER_SCROLL_DIR}" scrollamount="{news_ticker_row.XS_NEWS_TICKER_SPEED}" loop="true" onmouseover="this.stop()" onmouseout="this.start()">{news_ticker_row.XS_NEWS_TICKER_CONTENTS}</marquee>
				</span>
			</p>
		<!-- END news_ticker_row -->
	<!-- END switch_news_ticker -->

	<!-- BEGIN switch_news_subtitle -->
	<p class="forum-buttons2" style="height:17px;valign:middle;text-align:left;">{XS_NEWS_ITEMS_TITLE}</p>
	<!-- END switch_news_subtitle -->
	<p class="row1 row-news-tickers">
		<!-- BEGIN newsitem -->
		<b>{newsitem.NEWS_ITEM_DATE}:</b> {newsitem.NEWS_ITEM}<br />
		<!-- END newsitem -->
	</p>

	</td>
</tr>
</table>{IMG_TFL}{IMG_TFC}{IMG_TFR}
</div>