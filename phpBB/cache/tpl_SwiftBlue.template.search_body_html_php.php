<?php

// eXtreme Styles mod cache. Generated on Fri, 03 Aug 2018 09:15:47 +0000 (time=1533287747)

?><!-- INCLUDEX overall_header.html -->

<div id="pagecontent">

	<form method="get" action="<?php echo isset($this->vars['S_SEARCH_ACTION']) ? $this->vars['S_SEARCH_ACTION'] : $this->lang('S_SEARCH_ACTION'); ?>">
	
	<table class="tablebg" width="100--" cellspacing="1">
	<tr>
		<th colspan="4"><?php echo isset($this->vars['L_SEARCH_QUERY']) ? $this->vars['L_SEARCH_QUERY'] : $this->lang('L_SEARCH_QUERY'); ?></th>
	</tr>
	<tr>
		<td class="row1" colspan="2" width="50--"><b class="genmed"><?php echo isset($this->vars['L_SEARCH_KEYWORDS']) ? $this->vars['L_SEARCH_KEYWORDS'] : $this->lang('L_SEARCH_KEYWORDS'); ?>: </b><br /><span class="gensmall"><?php echo isset($this->vars['L_SEARCH_KEYWORDS_EXPLAIN']) ? $this->vars['L_SEARCH_KEYWORDS_EXPLAIN'] : $this->lang('L_SEARCH_KEYWORDS_EXPLAIN'); ?></span></td>
		<td class="row2" colspan="2" valign="top"><input type="text" style="width: 300px" class="post" name="keywords" size="30" /><br /><input type="radio" class="radio" name="terms" value="all" checked="checked" /> <span class="genmed"><?php echo isset($this->vars['L_SEARCH_ALL_TERMS']) ? $this->vars['L_SEARCH_ALL_TERMS'] : $this->lang('L_SEARCH_ALL_TERMS'); ?></span><br /><input type="radio" class="radio" name="terms" value="any" /> <span class="genmed"><?php echo isset($this->vars['L_SEARCH_ANY_TERMS']) ? $this->vars['L_SEARCH_ANY_TERMS'] : $this->lang('L_SEARCH_ANY_TERMS'); ?></span></td>
	</tr>
	<tr>
		<td class="row1" colspan="2"><b class="genmed"><?php echo isset($this->vars['L_SEARCH_AUTHOR']) ? $this->vars['L_SEARCH_AUTHOR'] : $this->lang('L_SEARCH_AUTHOR'); ?>:</b><br /><span class="gensmall"><?php echo isset($this->vars['L_SEARCH_AUTHOR_EXPLAIN']) ? $this->vars['L_SEARCH_AUTHOR_EXPLAIN'] : $this->lang('L_SEARCH_AUTHOR_EXPLAIN'); ?></span></td>
		<td class="row2" colspan="2" valign="middle"><input type="text" style="width: 300px" class="post" name="author" size="30" /></td>
	</tr>
	<tr>
		<td class="row1" colspan="2"><b class="genmed"><?php echo isset($this->vars['L_SEARCH_FORUMS']) ? $this->vars['L_SEARCH_FORUMS'] : $this->lang('L_SEARCH_FORUMS'); ?>: </b><br /><span class="gensmall"><?php echo isset($this->vars['L_SEARCH_FORUMS_EXPLAIN']) ? $this->vars['L_SEARCH_FORUMS_EXPLAIN'] : $this->lang('L_SEARCH_FORUMS_EXPLAIN'); ?></span></td>
		<td class="row2" colspan="2"><select name="fid[]" multiple="multiple" size="5"><?php echo isset($this->vars['S_FORUM_OPTIONS']) ? $this->vars['S_FORUM_OPTIONS'] : $this->lang('S_FORUM_OPTIONS'); ?></select></td>
	</tr>
	<tr>
		<th colspan="4"><?php echo isset($this->vars['L_SEARCH_OPTIONS']) ? $this->vars['L_SEARCH_OPTIONS'] : $this->lang('L_SEARCH_OPTIONS'); ?></th>
	</tr>
	<tr>
		<td class="row1" width="25--" nowrap="nowrap"><b class="genmed"><?php echo isset($this->vars['L_SEARCH_SUBFORUMS']) ? $this->vars['L_SEARCH_SUBFORUMS'] : $this->lang('L_SEARCH_SUBFORUMS'); ?>: </b></td>
		<td class="row2" width="25--" nowrap="nowrap"><input type="radio" class="radio" name="sc" value="1" checked="checked" /> <span class="genmed"><?php echo isset($this->vars['L_YES']) ? $this->vars['L_YES'] : $this->lang('L_YES'); ?></span>&nbsp;&nbsp;<input type="radio" class="radio" name="sc" value="0" /> <span class="genmed"><?php echo isset($this->vars['L_NO']) ? $this->vars['L_NO'] : $this->lang('L_NO'); ?></span></td>
		<td class="row1" width="25--" nowrap="nowrap"><b class="genmed"><?php echo isset($this->vars['L_SEARCH_WITHIN']) ? $this->vars['L_SEARCH_WITHIN'] : $this->lang('L_SEARCH_WITHIN'); ?>: </b></td>
		<td class="row2" width="25--" nowrap="nowrap"><input type="radio" class="radio" name="sf" value="all" checked="checked" /> <span class="genmed"><?php echo isset($this->vars['L_SEARCH_TITLE_MSG']) ? $this->vars['L_SEARCH_TITLE_MSG'] : $this->lang('L_SEARCH_TITLE_MSG'); ?></span><br /><input type="radio" class="radio" name="sf" value="msgonly" /> <span class="genmed"><?php echo isset($this->vars['L_SEARCH_MSG_ONLY']) ? $this->vars['L_SEARCH_MSG_ONLY'] : $this->lang('L_SEARCH_MSG_ONLY'); ?></span> <br /><input type="radio" class="radio" name="sf" value="titleonly" /> <span class="genmed"><?php echo isset($this->vars['L_SEARCH_TITLE_ONLY']) ? $this->vars['L_SEARCH_TITLE_ONLY'] : $this->lang('L_SEARCH_TITLE_ONLY'); ?></span> <br /><input type="radio" class="radio" name="sf" value="firstpost" /> <span class="genmed"><?php echo isset($this->vars['L_SEARCH_FIRST_POST']) ? $this->vars['L_SEARCH_FIRST_POST'] : $this->lang('L_SEARCH_FIRST_POST'); ?></span></td>
	</tr>
	<tr>
		<td class="row1"><b class="genmed"><?php echo isset($this->vars['L_RESULT_SORT']) ? $this->vars['L_RESULT_SORT'] : $this->lang('L_RESULT_SORT'); ?>: </b></td>
		<td class="row2" nowrap="nowrap"><?php echo isset($this->vars['S_SELECT_SORT_KEY']) ? $this->vars['S_SELECT_SORT_KEY'] : $this->lang('S_SELECT_SORT_KEY'); ?><br /><input type="radio" class="radio" name="sd" value="a" /> <span class="genmed"><?php echo isset($this->vars['L_SORT_ASCENDING']) ? $this->vars['L_SORT_ASCENDING'] : $this->lang('L_SORT_ASCENDING'); ?></span><br /><input type="radio" class="radio" name="sd" value="d" checked="checked" /> <span class="genmed"><?php echo isset($this->vars['L_SORT_DESCENDING']) ? $this->vars['L_SORT_DESCENDING'] : $this->lang('L_SORT_DESCENDING'); ?></span></td>
		<td class="row1" nowrap="nowrap"><b class="genmed"><?php echo isset($this->vars['L_DISPLAY_RESULTS']) ? $this->vars['L_DISPLAY_RESULTS'] : $this->lang('L_DISPLAY_RESULTS'); ?>: </b></td>
		<td class="row2" nowrap="nowrap"><input type="radio" class="radio" name="sr" value="posts" checked="checked" /> <span class="genmed"><?php echo isset($this->vars['L_POSTS']) ? $this->vars['L_POSTS'] : $this->lang('L_POSTS'); ?></span>&nbsp;&nbsp;<input type="radio" class="radio" name="sr" value="topics" /> <span class="genmed"><?php echo isset($this->vars['L_TOPICS']) ? $this->vars['L_TOPICS'] : $this->lang('L_TOPICS'); ?></span></td>
	</tr>
	<tr>
		<td class="row1" width="25--"><b class="genmed"><?php echo isset($this->vars['L_RESULT_DAYS']) ? $this->vars['L_RESULT_DAYS'] : $this->lang('L_RESULT_DAYS'); ?>: </b></td>
		<td class="row2" width="25--" nowrap="nowrap"><?php echo isset($this->vars['S_SELECT_SORT_DAYS']) ? $this->vars['S_SELECT_SORT_DAYS'] : $this->lang('S_SELECT_SORT_DAYS'); ?></td>
		<td class="row1" nowrap="nowrap"><b class="genmed"><?php echo isset($this->vars['L_RETURN_FIRST']) ? $this->vars['L_RETURN_FIRST'] : $this->lang('L_RETURN_FIRST'); ?>: </b></td>
		<td class="row2" nowrap="nowrap"><select name="ch"><?php echo isset($this->vars['S_CHARACTER_OPTIONS']) ? $this->vars['S_CHARACTER_OPTIONS'] : $this->lang('S_CHARACTER_OPTIONS'); ?></select> <span class="genmed"><?php echo isset($this->vars['L_POST_CHARACTERS']) ? $this->vars['L_POST_CHARACTERS'] : $this->lang('L_POST_CHARACTERS'); ?></span></td>
	</tr>
	<tr>
		<td class="cat" colspan="4" align="center"><?php echo isset($this->vars['S_HIDDEN_FIELDS']) ? $this->vars['S_HIDDEN_FIELDS'] : $this->lang('S_HIDDEN_FIELDS'); ?><input class="btnmain" name="submit" type="submit" value="<?php echo isset($this->vars['L_SEARCH']) ? $this->vars['L_SEARCH'] : $this->lang('L_SEARCH'); ?>" />&nbsp;&nbsp;<input class="btnlite" type="reset" value="<?php echo isset($this->vars['L_RESET']) ? $this->vars['L_RESET'] : $this->lang('L_RESET'); ?>" /></td>
	</tr>
	</table>
	
	</form>

	<br clear="all" />

	<?php if ($recentsearch) {  ?>
	<table class="tablebg" width="100--" cellspacing="1">
	<tr>
		<th colspan="2"><?php echo isset($this->vars['L_RECENT_SEARCHES']) ? $this->vars['L_RECENT_SEARCHES'] : $this->lang('L_RECENT_SEARCHES'); ?></th>
	</tr>
	<?php

$recentsearch_count = ( isset($this->_tpldata['recentsearch.']) ) ?  sizeof($this->_tpldata['recentsearch.']) : 0;
for ($recentsearch_i = 0; $recentsearch_i < $recentsearch_count; $recentsearch_i++)
{
 $recentsearch_item = &$this->_tpldata['recentsearch.'][$recentsearch_i];
 $recentsearch_item['S_ROW_COUNT'] = $recentsearch_i;
 $recentsearch_item['S_NUM_ROWS'] = $recentsearch_count;

?>
		<?php if (!($recentsearch_item['S_ROW_COUNT'] % 2)) {  ?><tr class="row2"><?php } else { ?><tr class="row1"><?php } ?>

			<td class="genmed" style="padding: 4px;" width="70--"><a href="<?php echo isset($recentsearch_item['U_KEYWORDS']) ? $recentsearch_item['U_KEYWORDS'] : ''; ?>"><?php echo isset($recentsearch_item['KEYWORDS']) ? $recentsearch_item['KEYWORDS'] : ''; ?></a></td>
			<td class="genmed" style="padding: 4px;" width="30--" align="center"><?php echo isset($recentsearch_item['TIME']) ? $recentsearch_item['TIME'] : ''; ?></td>
		</tr>
	<?php

} // END recentsearch

if(isset($recentsearch_item)) { unset($recentsearch_item); } 

?>
	</table>

	<br clear="all" />
	<?php } ?>

	</div>

	<?php  $this->set_filename('xs_include_ca158a11c448d937424ab11d87b11ff6', 'breadcrumbs.html', true);  $this->pparse('xs_include_ca158a11c448d937424ab11d87b11ff6');  ?>

	<br clear="all" />

	<div align="<?php echo isset($this->vars['S_CONTENT_FLOW_END']) ? $this->vars['S_CONTENT_FLOW_END'] : $this->lang('S_CONTENT_FLOW_END'); ?>"><?php  $this->set_filename('xs_include_559f05bc4d28f435aaf5ab7c20438dc6', 'jumpbox.html', true);  $this->pparse('xs_include_559f05bc4d28f435aaf5ab7c20438dc6');  ?></div>

<!-- INCLUDEX overall_footer.html -->