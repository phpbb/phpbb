<div class="block">
	<h2>{NEWS_TITLE}</h2>
	<!-- BEGIN switch_news_ticker -->
		<!-- BEGIN switch_ticker_subtitle -->
		<p>{XS_NEWS_TICKERS_TITLE}</p>
		<!-- END switch_ticker_subtitle -->
		<!-- BEGIN news_ticker_row -->
			<!-- BEGIN switch_show_feed -->
			<p class="post-time">{news_ticker_row.XS_NEWS_TICKER_FROM}</p>
			<!-- END switch_show_feed -->
			<p>
				{news_ticker_row.XS_NEWS_TICKER_CONTENTS}
			</p>
		<!-- END news_ticker_row -->
	<!-- END switch_news_ticker -->

	<!-- BEGIN switch_news_subtitle -->
	<p>{XS_NEWS_ITEMS_TITLE}</p>
	<!-- END switch_news_subtitle -->
	<p>
		<!-- BEGIN newsitem -->
		<b>{newsitem.NEWS_ITEM_DATE}:</b> {newsitem.NEWS_ITEM}<br />
		<!-- END newsitem -->
	</p>

</div>