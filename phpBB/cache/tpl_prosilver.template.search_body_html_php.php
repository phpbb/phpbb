<?php

// eXtreme Styles mod cache. Generated on Fri, 18 May 2018 17:12:50 +0000 (time=1526663570)

?><!-- INCLUDEX overall_header.html -->

<h2 class="solo"><?php echo isset($this->vars['L_SEARCH']) ? $this->vars['L_SEARCH'] : $this->lang('L_SEARCH'); ?></h2>

<!-- EVENT search_body_form_before -->
<form method="get" action="<?php echo isset($this->vars['S_SEARCH_ACTION']) ? $this->vars['S_SEARCH_ACTION'] : $this->lang('S_SEARCH_ACTION'); ?>" data-focus="keywords">

<div class="panel">
	<div class="inner">
	<h3><?php echo isset($this->vars['L_SEARCH_QUERY']) ? $this->vars['L_SEARCH_QUERY'] : $this->lang('L_SEARCH_QUERY'); ?></h3>

	<!-- EVENT search_body_search_query_before -->
	<fieldset>
	<!-- EVENT search_body_search_query_prepend -->
	<dl>
		<dt><label for="keywords"><?php echo isset($this->vars['L_SEARCH_KEYWORDS']) ? $this->vars['L_SEARCH_KEYWORDS'] : $this->lang('L_SEARCH_KEYWORDS'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></label><br /><span><?php echo isset($this->vars['L_SEARCH_KEYWORDS_EXPLAIN']) ? $this->vars['L_SEARCH_KEYWORDS_EXPLAIN'] : $this->lang('L_SEARCH_KEYWORDS_EXPLAIN'); ?></span></dt>
		<dd><input type="search" class="inputbox" name="keywords" id="keywords" size="40" title="<?php echo isset($this->vars['L_SEARCH_KEYWORDS']) ? $this->vars['L_SEARCH_KEYWORDS'] : $this->lang('L_SEARCH_KEYWORDS'); ?>" /></dd>
		<dd><label for="terms1"><input type="radio" name="terms" id="terms1" value="all" checked="checked" /> <?php echo isset($this->vars['L_SEARCH_ALL_TERMS']) ? $this->vars['L_SEARCH_ALL_TERMS'] : $this->lang('L_SEARCH_ALL_TERMS'); ?></label></dd>
		<dd><label for="terms2"><input type="radio" name="terms" id="terms2" value="any" /> <?php echo isset($this->vars['L_SEARCH_ANY_TERMS']) ? $this->vars['L_SEARCH_ANY_TERMS'] : $this->lang('L_SEARCH_ANY_TERMS'); ?></label></dd>
	</dl>
	<dl>
		<dt><label for="author"><?php echo isset($this->vars['L_SEARCH_AUTHOR']) ? $this->vars['L_SEARCH_AUTHOR'] : $this->lang('L_SEARCH_AUTHOR'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></label><br /><span><?php echo isset($this->vars['L_SEARCH_AUTHOR_EXPLAIN']) ? $this->vars['L_SEARCH_AUTHOR_EXPLAIN'] : $this->lang('L_SEARCH_AUTHOR_EXPLAIN'); ?></span></dt>
		<dd><input type="search" class="inputbox" name="author" id="author" size="40" title="<?php echo isset($this->vars['L_SEARCH_AUTHOR']) ? $this->vars['L_SEARCH_AUTHOR'] : $this->lang('L_SEARCH_AUTHOR'); ?>" /></dd>
	</dl>
	<!-- EVENT search_body_search_query_append -->
	</fieldset>
	<!-- EVENT search_body_search_query_after -->

	</div>
</div>

<div class="panel bg2">
	<div class="inner">

	<h3><?php echo isset($this->vars['L_SEARCH_OPTIONS']) ? $this->vars['L_SEARCH_OPTIONS'] : $this->lang('L_SEARCH_OPTIONS'); ?></h3>

	<!-- EVENT search_body_search_options_before -->
	<fieldset>
	<!-- EVENT search_body_search_options_prepend -->
	<dl>
		<dt><label for="search_forum"><?php echo isset($this->vars['L_SEARCH_FORUMS']) ? $this->vars['L_SEARCH_FORUMS'] : $this->lang('L_SEARCH_FORUMS'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></label><br /><span><?php echo isset($this->vars['L_SEARCH_FORUMS_EXPLAIN']) ? $this->vars['L_SEARCH_FORUMS_EXPLAIN'] : $this->lang('L_SEARCH_FORUMS_EXPLAIN'); ?></span></dt>
		<dd><select name="fid[]" id="search_forum" multiple="multiple" size="8" title="<?php echo isset($this->vars['L_SEARCH_FORUMS']) ? $this->vars['L_SEARCH_FORUMS'] : $this->lang('L_SEARCH_FORUMS'); ?>"><?php echo isset($this->vars['S_FORUM_OPTIONS']) ? $this->vars['S_FORUM_OPTIONS'] : $this->lang('S_FORUM_OPTIONS'); ?></select></dd>
	</dl>
	<dl>
		<dt><label for="search_child1"><?php echo isset($this->vars['L_SEARCH_SUBFORUMS']) ? $this->vars['L_SEARCH_SUBFORUMS'] : $this->lang('L_SEARCH_SUBFORUMS'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></label></dt>
		<dd>
			<label for="search_child1"><input type="radio" name="sc" id="search_child1" value="1" checked="checked" /> <?php echo isset($this->vars['L_YES']) ? $this->vars['L_YES'] : $this->lang('L_YES'); ?></label>
			<label for="search_child2"><input type="radio" name="sc" id="search_child2" value="0" /> <?php echo isset($this->vars['L_NO']) ? $this->vars['L_NO'] : $this->lang('L_NO'); ?></label>
		</dd>
	</dl>
	<dl>
		<dt><label for="sf1"><?php echo isset($this->vars['L_SEARCH_WITHIN']) ? $this->vars['L_SEARCH_WITHIN'] : $this->lang('L_SEARCH_WITHIN'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></label></dt>
		<dd><label for="sf1"><input type="radio" name="sf" id="sf1" value="all" checked="checked" /> <?php echo isset($this->vars['L_SEARCH_TITLE_MSG']) ? $this->vars['L_SEARCH_TITLE_MSG'] : $this->lang('L_SEARCH_TITLE_MSG'); ?></label></dd>
		<dd><label for="sf2"><input type="radio" name="sf" id="sf2" value="msgonly" /> <?php echo isset($this->vars['L_SEARCH_MSG_ONLY']) ? $this->vars['L_SEARCH_MSG_ONLY'] : $this->lang('L_SEARCH_MSG_ONLY'); ?></label></dd>
		<dd><label for="sf3"><input type="radio" name="sf" id="sf3" value="titleonly" /> <?php echo isset($this->vars['L_SEARCH_TITLE_ONLY']) ? $this->vars['L_SEARCH_TITLE_ONLY'] : $this->lang('L_SEARCH_TITLE_ONLY'); ?></label></dd>
		<dd><label for="sf4"><input type="radio" name="sf" id="sf4" value="firstpost" /> <?php echo isset($this->vars['L_SEARCH_FIRST_POST']) ? $this->vars['L_SEARCH_FIRST_POST'] : $this->lang('L_SEARCH_FIRST_POST'); ?></label></dd>
	</dl>
	<!-- EVENT search_body_search_options_append -->

	<hr class="dashed" />

	<!-- EVENT search_body_search_display_options_prepend -->
	<dl>
		<dt><label for="show_results1"><?php echo isset($this->vars['L_DISPLAY_RESULTS']) ? $this->vars['L_DISPLAY_RESULTS'] : $this->lang('L_DISPLAY_RESULTS'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></label></dt>
		<dd>
			<label for="show_results1"><input type="radio" name="sr" id="show_results1" value="posts" checked="checked" /> <?php echo isset($this->vars['L_POSTS']) ? $this->vars['L_POSTS'] : $this->lang('L_POSTS'); ?></label>
			<label for="show_results2"><input type="radio" name="sr" id="show_results2" value="topics" /> <?php echo isset($this->vars['L_TOPICS']) ? $this->vars['L_TOPICS'] : $this->lang('L_TOPICS'); ?></label>
		</dd>
	</dl>
	<dl>
		<dt><label for="sd"><?php echo isset($this->vars['L_RESULT_SORT']) ? $this->vars['L_RESULT_SORT'] : $this->lang('L_RESULT_SORT'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></label></dt>
		<dd><?php echo isset($this->vars['S_SELECT_SORT_KEY']) ? $this->vars['S_SELECT_SORT_KEY'] : $this->lang('S_SELECT_SORT_KEY'); ?>&nbsp;
			<label for="sa"><input type="radio" name="sd" id="sa" value="a" /> <?php echo isset($this->vars['L_SORT_ASCENDING']) ? $this->vars['L_SORT_ASCENDING'] : $this->lang('L_SORT_ASCENDING'); ?></label>
			<label for="sd"><input type="radio" name="sd" id="sd" value="d" checked="checked" /> <?php echo isset($this->vars['L_SORT_DESCENDING']) ? $this->vars['L_SORT_DESCENDING'] : $this->lang('L_SORT_DESCENDING'); ?></label>
		</dd>
	</dl>
	<dl>
		<dt><label><?php echo isset($this->vars['L_RESULT_DAYS']) ? $this->vars['L_RESULT_DAYS'] : $this->lang('L_RESULT_DAYS'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></label></dt>
		<dd><?php echo isset($this->vars['S_SELECT_SORT_DAYS']) ? $this->vars['S_SELECT_SORT_DAYS'] : $this->lang('S_SELECT_SORT_DAYS'); ?></dd>
	</dl>
	<dl>
		<dt><label><?php echo isset($this->vars['L_RETURN_FIRST']) ? $this->vars['L_RETURN_FIRST'] : $this->lang('L_RETURN_FIRST'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></label></dt>
		<dd><select name="ch" title="<?php echo isset($this->vars['L_RETURN_FIRST']) ? $this->vars['L_RETURN_FIRST'] : $this->lang('L_RETURN_FIRST'); ?>"><?php echo isset($this->vars['S_CHARACTER_OPTIONS']) ? $this->vars['S_CHARACTER_OPTIONS'] : $this->lang('S_CHARACTER_OPTIONS'); ?></select> <?php echo isset($this->vars['L_POST_CHARACTERS']) ? $this->vars['L_POST_CHARACTERS'] : $this->lang('L_POST_CHARACTERS'); ?></dd>
	</dl>
	<!-- EVENT search_body_search_display_options_append -->
	</fieldset>
	<!-- EVENT search_body_search_options_after -->

	</div>
</div>

<div class="panel bg3">
	<div class="inner">

	<fieldset class="submit-buttons">
		<?php echo isset($this->vars['S_HIDDEN_FIELDS']) ? $this->vars['S_HIDDEN_FIELDS'] : $this->lang('S_HIDDEN_FIELDS'); ?><input type="reset" value="<?php echo isset($this->vars['L_RESET']) ? $this->vars['L_RESET'] : $this->lang('L_RESET'); ?>" name="reset" class="button2" />&nbsp;
		<input type="submit" name="submit" value="<?php echo isset($this->vars['L_SEARCH']) ? $this->vars['L_SEARCH'] : $this->lang('L_SEARCH'); ?>" class="button1" />
	</fieldset>

	</div>
</div>

</form>
<!-- EVENT search_body_form_after -->

<!-- EVENT search_body_recent_search_before -->
<?php if ($recentsearch) {  ?>
<div class="forumbg forumbg-table">
	<div class="inner">

	<table class="table1">
	<thead>
	<tr>
		<th colspan="2" class="name"><?php echo isset($this->vars['L_RECENT_SEARCHES']) ? $this->vars['L_RECENT_SEARCHES'] : $this->lang('L_RECENT_SEARCHES'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php

$recentsearch_count = ( isset($this->_tpldata['recentsearch.']) ) ?  sizeof($this->_tpldata['recentsearch.']) : 0;
for ($recentsearch_i = 0; $recentsearch_i < $recentsearch_count; $recentsearch_i++)
{
 $recentsearch_item = &$this->_tpldata['recentsearch.'][$recentsearch_i];
 $recentsearch_item['S_ROW_COUNT'] = $recentsearch_i;
 $recentsearch_item['S_NUM_ROWS'] = $recentsearch_count;

?>
		<tr class="<?php if (!($recentsearch_item['S_ROW_COUNT'] % 2)) {  ?>bg1<?php } else { ?>bg2<?php } ?>">
			<td><a href="<?php echo isset($recentsearch_item['U_KEYWORDS']) ? $recentsearch_item['U_KEYWORDS'] : ''; ?>"><?php echo isset($recentsearch_item['KEYWORDS']) ? $recentsearch_item['KEYWORDS'] : ''; ?></a></td>
			<td class="active"><?php echo isset($recentsearch_item['TIME']) ? $recentsearch_item['TIME'] : ''; ?></td>
		</tr>
	<?php } if(!$recentsearch_count) { ?>
		<tr class="bg1">
			<td colspan="2"><?php echo isset($this->vars['L_NO_RECENT_SEARCHES']) ? $this->vars['L_NO_RECENT_SEARCHES'] : $this->lang('L_NO_RECENT_SEARCHES'); ?></td>
		</tr>
	<?php

} // END recentsearch

if(isset($recentsearch_item)) { unset($recentsearch_item); } 

?>
	</tbody>
	</table>

	</div>
</div>
<?php } ?>
<!-- EVENT search_body_recent_search_after -->

<!-- INCLUDEX overall_footer.html -->
