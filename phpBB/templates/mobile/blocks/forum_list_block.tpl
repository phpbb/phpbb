<h2>{FL_TITLE}</h2>
<ul class="links">
<!-- BEGIN cat_row -->
	<li>
		<a href="javascript:void(0);" class="gradient">{cat_row.CAT_ITEM}</a>
		<ul class="menu">
			<!-- BEGIN forum_row -->
			<li><a href="{cat_row.forum_row.FORUM_LINK}">{cat_row.forum_row.FORUM_ITEM}</a></li>
			<!-- END forum_row -->
		</ul>
	</li>
<!-- END cat_row -->
</ul>
<!-- BEGIN no_forum -->
<p>{no_forum.NO_FORUM}</p>
<!-- END no_forum -->

<div class="clear"></div>