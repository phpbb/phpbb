<?php

// eXtreme Styles mod cache. Generated on Sat, 26 May 2018 21:49:23 +0000 (time=1527371363)

?><?php if ($this->vars['S_IN_SEARCH_POPUP']) {  ?>
	<?php  $this->set_filename('xs_include_a4fac28272becb7707154647c02ac8a9', 'simple_header.html', true);  $this->pparse('xs_include_a4fac28272becb7707154647c02ac8a9');  ?>
	<?php  $this->set_filename('xs_include_ec3dda0976975b6de80a413b141e8bb8', 'memberlist_search.html', true);  $this->pparse('xs_include_ec3dda0976975b6de80a413b141e8bb8');  ?>
	<form method="post" id="results" action="<?php echo isset($this->vars['S_MODE_ACTION']) ? $this->vars['S_MODE_ACTION'] : $this->lang('S_MODE_ACTION'); ?>" onsubmit="insert_marked_users('#results', this.user); return false;" data-form-name="<?php echo isset($this->vars['S_FORM_NAME']) ? $this->vars['S_FORM_NAME'] : $this->lang('S_FORM_NAME'); ?>" data-field-name="<?php echo isset($this->vars['S_FIELD_NAME']) ? $this->vars['S_FIELD_NAME'] : $this->lang('S_FIELD_NAME'); ?>">

<?php } else { ?>
	<!-- INCLUDEX overall_header.html -->
	<div class="panel" id="memberlist_search"<?php if (! $this->vars['S_SEARCH_USER']) {  ?> style="display: none;"<?php } ?>>
	<?php  $this->set_filename('xs_include_1db4b18bbf96155cc6a738b71efd827c', 'memberlist_search.html', true);  $this->pparse('xs_include_1db4b18bbf96155cc6a738b71efd827c');  ?>
	</div>
	<form method="post" action="<?php echo isset($this->vars['S_MODE_ACTION']) ? $this->vars['S_MODE_ACTION'] : $this->lang('S_MODE_ACTION'); ?>">

<?php } ?>

	<?php if ($this->vars['S_SHOW_GROUP']) {  ?>
		<h2 class="group-title"<?php if ($this->vars['GROUP_COLOR']) {  ?> style="color:#<?php echo isset($this->vars['GROUP_COLOR']) ? $this->vars['GROUP_COLOR'] : $this->lang('GROUP_COLOR'); ?>;"<?php } ?>><?php echo isset($this->vars['GROUP_NAME']) ? $this->vars['GROUP_NAME'] : $this->lang('GROUP_NAME'); ?></h2>
		<?php if ($this->vars['U_MANAGE']) {  ?>
			<p class="right responsive-center manage rightside"><a href="<?php echo isset($this->vars['U_MANAGE']) ? $this->vars['U_MANAGE'] : $this->lang('U_MANAGE'); ?>"><?php echo isset($this->vars['L_MANAGE_GROUP']) ? $this->vars['L_MANAGE_GROUP'] : $this->lang('L_MANAGE_GROUP'); ?></a></p>
		<?php } ?>
		<p><?php echo isset($this->vars['GROUP_DESC']) ? $this->vars['GROUP_DESC'] : $this->lang('GROUP_DESC'); ?> <?php echo isset($this->vars['GROUP_TYPE']) ? $this->vars['GROUP_TYPE'] : $this->lang('GROUP_TYPE'); ?></p>

		<p>
			<?php if ($this->vars['AVATAR_IMG']) {  ?><?php echo isset($this->vars['AVATAR_IMG']) ? $this->vars['AVATAR_IMG'] : $this->lang('AVATAR_IMG'); ?><?php } ?>
			<?php if ($this->vars['RANK_IMG']) {  ?><?php echo isset($this->vars['RANK_IMG']) ? $this->vars['RANK_IMG'] : $this->lang('RANK_IMG'); ?><?php } ?>
			<?php if ($this->vars['GROUP_RANK']) {  ?><?php echo isset($this->vars['GROUP_RANK']) ? $this->vars['GROUP_RANK'] : $this->lang('GROUP_RANK'); ?><?php } ?>
		</p>
	<?php } else { ?>
		<h2 class="solo"><?php echo isset($this->vars['PAGE_TITLE']) ? $this->vars['PAGE_TITLE'] : $this->lang('PAGE_TITLE'); ?><?php if ($this->vars['SEARCH_WORDS']) {  ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?> <a href="<?php echo isset($this->vars['U_SEARCH_WORDS']) ? $this->vars['U_SEARCH_WORDS'] : $this->lang('U_SEARCH_WORDS'); ?>"><?php echo isset($this->vars['SEARCH_WORDS']) ? $this->vars['SEARCH_WORDS'] : $this->lang('SEARCH_WORDS'); ?></a><?php } ?></h2>

		<div class="action-bar top">
			<div class="member-search panel">
				<?php if ($this->vars['U_FIND_MEMBER'] && ! $this->vars['S_SEARCH_USER']) {  ?><a href="<?php echo isset($this->vars['U_FIND_MEMBER']) ? $this->vars['U_FIND_MEMBER'] : $this->lang('U_FIND_MEMBER'); ?>" id="member_search" data-alt-text="<?php echo isset($this->vars['LA_HIDE_MEMBER_SEARCH']) ? $this->vars['LA_HIDE_MEMBER_SEARCH'] : $this->lang('LA_HIDE_MEMBER_SEARCH'); ?>"><?php echo isset($this->vars['L_FIND_USERNAME']) ? $this->vars['L_FIND_USERNAME'] : $this->lang('L_FIND_USERNAME'); ?></a> &bull; <?php } elseif ($this->vars['S_SEARCH_USER'] && $this->vars['U_HIDE_FIND_MEMBER'] && ! $this->vars['S_IN_SEARCH_POPUP']) {  ?><a href="<?php echo isset($this->vars['U_HIDE_FIND_MEMBER']) ? $this->vars['U_HIDE_FIND_MEMBER'] : $this->lang('U_HIDE_FIND_MEMBER'); ?>" id="member_search" data-alt-text="<?php echo isset($this->vars['LA_FIND_USERNAME']) ? $this->vars['LA_FIND_USERNAME'] : $this->lang('LA_FIND_USERNAME'); ?>"><?php echo isset($this->vars['L_HIDE_MEMBER_SEARCH']) ? $this->vars['L_HIDE_MEMBER_SEARCH'] : $this->lang('L_HIDE_MEMBER_SEARCH'); ?></a> &bull; <?php } ?>
				<strong>
				<?php

$first_char_count = ( isset($this->_tpldata['first_char.']) ) ?  sizeof($this->_tpldata['first_char.']) : 0;
for ($first_char_i = 0; $first_char_i < $first_char_count; $first_char_i++)
{
 $first_char_item = &$this->_tpldata['first_char.'][$first_char_i];
 $first_char_item['S_ROW_COUNT'] = $first_char_i;
 $first_char_item['S_NUM_ROWS'] = $first_char_count;

?>
					<a href="<?php echo isset($first_char_item['U_SORT']) ? $first_char_item['U_SORT'] : ''; ?>"><?php echo isset($first_char_item['DESC']) ? $first_char_item['DESC'] : ''; ?></a>&nbsp;
				<?php

} // END first_char

if(isset($first_char_item)) { unset($first_char_item); } 

?>
				</strong>
			</div>
		
			<div class="pagination">
				<?php echo isset($this->vars['TOTAL_USERS']) ? $this->vars['TOTAL_USERS'] : $this->lang('TOTAL_USERS'); ?>
				<?php if ($this->vars['PAGINATION']) {  ?> 
					<?php  $this->set_filename('xs_include_8b4ec4dcd54627b2dfb66ae457e73280', 'pagination.html', true);  $this->pparse('xs_include_8b4ec4dcd54627b2dfb66ae457e73280');  ?>
				<?php } else { ?> 
					 &bull; <?php echo isset($this->vars['PAGE_NUMBER']) ? $this->vars['PAGE_NUMBER'] : $this->lang('PAGE_NUMBER'); ?>
				<?php } ?>
			</div>
		</div>
	<?php } ?>

	<?php if ($this->vars['S_LEADERS_SET'] || ! $this->vars['S_SHOW_GROUP'] || ! $memberrow) {  ?>
	<div class="forumbg forumbg-table">
		<div class="inner">

		<table class="table1" id="memberlist">
		<thead>
		<tr>
			<th class="name" data-dfn="<?php echo isset($this->vars['L_RANK']) ? $this->vars['L_RANK'] : $this->lang('L_RANK'); ?><?php echo isset($this->vars['L_COMMA_SEPARATOR']) ? $this->vars['L_COMMA_SEPARATOR'] : $this->lang('L_COMMA_SEPARATOR'); ?><?php if ($this->vars['S_SHOW_GROUP'] && $memberrow) {  ?><?php echo isset($this->vars['L_GROUP_LEADER']) ? $this->vars['L_GROUP_LEADER'] : $this->lang('L_GROUP_LEADER'); ?><?php } else { ?><?php echo isset($this->vars['L_USERNAME']) ? $this->vars['L_USERNAME'] : $this->lang('L_USERNAME'); ?><?php } ?>"><span class="rank-img"><a href="<?php echo isset($this->vars['U_SORT_RANK']) ? $this->vars['U_SORT_RANK'] : $this->lang('U_SORT_RANK'); ?>"><?php echo isset($this->vars['L_RANK']) ? $this->vars['L_RANK'] : $this->lang('L_RANK'); ?></a></span><a href="<?php echo isset($this->vars['U_SORT_USERNAME']) ? $this->vars['U_SORT_USERNAME'] : $this->lang('U_SORT_USERNAME'); ?>"><?php if ($this->vars['S_SHOW_GROUP'] && $memberrow) {  ?><?php echo isset($this->vars['L_GROUP_LEADER']) ? $this->vars['L_GROUP_LEADER'] : $this->lang('L_GROUP_LEADER'); ?><?php } else { ?><?php echo isset($this->vars['L_USERNAME']) ? $this->vars['L_USERNAME'] : $this->lang('L_USERNAME'); ?><?php } ?></a></th>
			<th class="posts"><a href="<?php echo isset($this->vars['U_SORT_POSTS']) ? $this->vars['U_SORT_POSTS'] : $this->lang('U_SORT_POSTS'); ?>#memberlist"><?php echo isset($this->vars['L_POSTS']) ? $this->vars['L_POSTS'] : $this->lang('L_POSTS'); ?></a></th>
			<th class="info"><?php

$custom_fields_count = ( isset($this->_tpldata['custom_fields.']) ) ?  sizeof($this->_tpldata['custom_fields.']) : 0;
for ($custom_fields_i = 0; $custom_fields_i < $custom_fields_count; $custom_fields_i++)
{
 $custom_fields_item = &$this->_tpldata['custom_fields.'][$custom_fields_i];
 $custom_fields_item['S_ROW_COUNT'] = $custom_fields_i;
 $custom_fields_item['S_NUM_ROWS'] = $custom_fields_count;

?><?php if (! $custom_fields_item['S_FIRST_ROW']) {  ?><?php echo isset($this->vars['L_COMMA_SEPARATOR']) ? $this->vars['L_COMMA_SEPARATOR'] : $this->lang('L_COMMA_SEPARATOR'); ?> <?php } ?><?php echo isset($custom_fields_item['PROFILE_FIELD_NAME']) ? $custom_fields_item['PROFILE_FIELD_NAME'] : ''; ?><?php

} // END custom_fields

if(isset($custom_fields_item)) { unset($custom_fields_item); } 

?></th>
			<th class="joined"><a href="<?php echo isset($this->vars['U_SORT_JOINED']) ? $this->vars['U_SORT_JOINED'] : $this->lang('U_SORT_JOINED'); ?>#memberlist"><?php echo isset($this->vars['L_JOINED']) ? $this->vars['L_JOINED'] : $this->lang('L_JOINED'); ?></a></th>
			<?php if ($this->vars['U_SORT_ACTIVE']) {  ?><th class="active"><a href="<?php echo isset($this->vars['U_SORT_ACTIVE']) ? $this->vars['U_SORT_ACTIVE'] : $this->lang('U_SORT_ACTIVE'); ?>#memberlist"><?php echo isset($this->vars['L_LAST_ACTIVE']) ? $this->vars['L_LAST_ACTIVE'] : $this->lang('L_LAST_ACTIVE'); ?></a></th><?php } ?>
		</tr>
		</thead>
		<tbody>
	<?php } ?>
		<?php

$memberrow_count = ( isset($this->_tpldata['memberrow.']) ) ?  sizeof($this->_tpldata['memberrow.']) : 0;
for ($memberrow_i = 0; $memberrow_i < $memberrow_count; $memberrow_i++)
{
 $memberrow_item = &$this->_tpldata['memberrow.'][$memberrow_i];
 $memberrow_item['S_ROW_COUNT'] = $memberrow_i;
 $memberrow_item['S_NUM_ROWS'] = $memberrow_count;

?>
			<?php if ($this->vars['S_SHOW_GROUP']) {  ?>
				<?php if (! $memberrow_item['S_GROUP_LEADER'] && ! $this->_tpldata['DEFINE']['.']['S_MEMBER_HEADER']) {  ?>
				<?php if ($this->vars['S_LEADERS_SET'] && $memberrow_item['S_FIRST_ROW']) {  ?>
				<tr class="bg1">
					<td colspan="<?php if ($this->vars['U_SORT_ACTIVE']) {  ?>5<?php } else { ?>4<?php } ?>">&nbsp;</td>
				</tr>
				<?php } ?>
<?php if ($this->vars['S_LEADERS_SET']) {  ?>
		</tbody>
		</table>

	</div>
</div>
<?php } ?>
<div class="forumbg forumbg-table">
	<div class="inner">

	<table class="table1">
	<thead>
	<tr>
	<?php if (! $this->vars['S_LEADERS_SET']) {  ?>
		<th class="name" data-dfn="<?php echo isset($this->vars['L_RANK']) ? $this->vars['L_RANK'] : $this->lang('L_RANK'); ?><?php echo isset($this->vars['L_COMMA_SEPARATOR']) ? $this->vars['L_COMMA_SEPARATOR'] : $this->lang('L_COMMA_SEPARATOR'); ?><?php echo isset($this->vars['L_USERNAME']) ? $this->vars['L_USERNAME'] : $this->lang('L_USERNAME'); ?>"><span class="rank-img"><a href="<?php echo isset($this->vars['U_SORT_RANK']) ? $this->vars['U_SORT_RANK'] : $this->lang('U_SORT_RANK'); ?>"><?php echo isset($this->vars['L_RANK']) ? $this->vars['L_RANK'] : $this->lang('L_RANK'); ?></a></span><a href="<?php echo isset($this->vars['U_SORT_USERNAME']) ? $this->vars['U_SORT_USERNAME'] : $this->lang('U_SORT_USERNAME'); ?>"><?php if ($this->vars['S_SHOW_GROUP']) {  ?><?php echo isset($this->vars['L_GROUP_MEMBERS']) ? $this->vars['L_GROUP_MEMBERS'] : $this->lang('L_GROUP_MEMBERS'); ?><?php } else { ?><?php echo isset($this->vars['L_USERNAME']) ? $this->vars['L_USERNAME'] : $this->lang('L_USERNAME'); ?><?php } ?></a></th>
			<th class="posts"><a href="<?php echo isset($this->vars['U_SORT_POSTS']) ? $this->vars['U_SORT_POSTS'] : $this->lang('U_SORT_POSTS'); ?>#memberlist"><?php echo isset($this->vars['L_POSTS']) ? $this->vars['L_POSTS'] : $this->lang('L_POSTS'); ?></a></th>
			<th class="info"><?php

$custom_fields_count = ( isset($memberrow_item['custom_fields.']) ) ? sizeof($memberrow_item['custom_fields.']) : 0;
for ($custom_fields_i = 0; $custom_fields_i < $custom_fields_count; $custom_fields_i++)
{
 $custom_fields_item = &$memberrow_item['custom_fields.'][$custom_fields_i];
 $custom_fields_item['S_ROW_COUNT'] = $custom_fields_i;
 $custom_fields_item['S_NUM_ROWS'] = $custom_fields_count;

?><?php if (! $custom_fields_item['S_FIRST_ROW']) {  ?><?php echo isset($this->vars['L_COMMA_SEPARATOR']) ? $this->vars['L_COMMA_SEPARATOR'] : $this->lang('L_COMMA_SEPARATOR'); ?> <?php } ?><?php echo isset($custom_fields_item['PROFILE_FIELD_NAME']) ? $custom_fields_item['PROFILE_FIELD_NAME'] : ''; ?><?php

} // END custom_fields

if(isset($custom_fields_item)) { unset($custom_fields_item); } 

?></th>
			<th class="joined"><a href="<?php echo isset($this->vars['U_SORT_JOINED']) ? $this->vars['U_SORT_JOINED'] : $this->lang('U_SORT_JOINED'); ?>#memberlist"><?php echo isset($this->vars['L_JOINED']) ? $this->vars['L_JOINED'] : $this->lang('L_JOINED'); ?></a></th>
			<?php if ($this->vars['U_SORT_ACTIVE']) {  ?><th class="active"><a href="<?php echo isset($this->vars['U_SORT_ACTIVE']) ? $this->vars['U_SORT_ACTIVE'] : $this->lang('U_SORT_ACTIVE'); ?>#memberlist"><?php echo isset($this->vars['L_LAST_ACTIVE']) ? $this->vars['L_LAST_ACTIVE'] : $this->lang('L_LAST_ACTIVE'); ?></a></th><?php } ?>
	<?php } elseif ($this->vars['S_SHOW_GROUP']) {  ?>
		<th class="name"><?php echo isset($this->vars['L_GROUP_MEMBERS']) ? $this->vars['L_GROUP_MEMBERS'] : $this->lang('L_GROUP_MEMBERS'); ?></th>
		<th class="posts"><?php echo isset($this->vars['L_POSTS']) ? $this->vars['L_POSTS'] : $this->lang('L_POSTS'); ?></th>
		<th class="info"><?php

$custom_fields_count = ( isset($memberrow_item['custom_fields.']) ) ? sizeof($memberrow_item['custom_fields.']) : 0;
for ($custom_fields_i = 0; $custom_fields_i < $custom_fields_count; $custom_fields_i++)
{
 $custom_fields_item = &$memberrow_item['custom_fields.'][$custom_fields_i];
 $custom_fields_item['S_ROW_COUNT'] = $custom_fields_i;
 $custom_fields_item['S_NUM_ROWS'] = $custom_fields_count;

?><?php if (! $custom_fields_item['S_FIRST_ROW']) {  ?><?php echo isset($this->vars['L_COMMA_SEPARATOR']) ? $this->vars['L_COMMA_SEPARATOR'] : $this->lang('L_COMMA_SEPARATOR'); ?> <?php } ?><?php echo isset($custom_fields_item['PROFILE_FIELD_NAME']) ? $custom_fields_item['PROFILE_FIELD_NAME'] : ''; ?><?php

} // END custom_fields

if(isset($custom_fields_item)) { unset($custom_fields_item); } 

?></th>
		<th class="joined"><?php echo isset($this->vars['L_JOINED']) ? $this->vars['L_JOINED'] : $this->lang('L_JOINED'); ?></th>
		<?php if ($this->vars['U_SORT_ACTIVE']) {  ?><th class="active"><?php echo isset($this->vars['L_LAST_ACTIVE']) ? $this->vars['L_LAST_ACTIVE'] : $this->lang('L_LAST_ACTIVE'); ?></th><?php } ?>
	<?php } ?>
	</tr>
	</thead>
	<tbody>
					<?php $this->_tpldata['DEFINE']['.']['S_MEMBER_HEADER'] = 1; ?>
				<?php } ?>
			<?php } ?>

	<tr class="<?php if (!($memberrow_item['S_ROW_COUNT'] % 2)) {  ?>bg1<?php } else { ?>bg2<?php } ?><?php if ($memberrow_item['S_INACTIVE']) {  ?> inactive<?php } ?>">
		<td><span class="rank-img"><!-- EVENT memberlist_body_rank_prepend --><?php if ($memberrow_item['RANK_IMG']) {  ?><?php echo isset($memberrow_item['RANK_IMG']) ? $memberrow_item['RANK_IMG'] : ''; ?><?php } else { ?><?php echo isset($memberrow_item['RANK_TITLE']) ? $memberrow_item['RANK_TITLE'] : ''; ?><?php } ?><!-- EVENT memberlist_body_rank_append --></span><?php if ($this->vars['S_IN_SEARCH_POPUP'] && ! $this->vars['S_SELECT_SINGLE']) {  ?><input type="checkbox" name="user" value="<?php echo isset($memberrow_item['USERNAME']) ? $memberrow_item['USERNAME'] : ''; ?>" /> <?php } ?><!-- EVENT memberlist_body_username_prepend --><?php echo isset($memberrow_item['USERNAME_FULL']) ? $memberrow_item['USERNAME_FULL'] : ''; ?><?php if ($memberrow_item['S_INACTIVE']) {  ?> (<?php echo isset($this->vars['L_INACTIVE']) ? $this->vars['L_INACTIVE'] : $this->lang('L_INACTIVE'); ?>)<?php } ?><!-- EVENT memberlist_body_username_append --><?php if ($this->vars['S_IN_SEARCH_POPUP']) {  ?><br />[&nbsp;<a href="#" onclick="insert_single_user('#results', '<?php echo isset($memberrow_item['A_USERNAME']) ? $memberrow_item['A_USERNAME'] : ''; ?>'); return false;"><?php echo isset($this->vars['L_SELECT']) ? $this->vars['L_SELECT'] : $this->lang('L_SELECT'); ?></a>&nbsp;]<?php } ?></td>
		<td class="posts"><?php if ($memberrow_item['POSTS'] && $this->vars['S_DISPLAY_SEARCH']) {  ?><a href="<?php echo isset($memberrow_item['U_SEARCH_USER']) ? $memberrow_item['U_SEARCH_USER'] : ''; ?>" title="<?php echo isset($this->vars['L_SEARCH_USER_POSTS']) ? $this->vars['L_SEARCH_USER_POSTS'] : $this->lang('L_SEARCH_USER_POSTS'); ?>"><?php echo isset($memberrow_item['POSTS']) ? $memberrow_item['POSTS'] : ''; ?></a><?php } else { ?><?php echo isset($memberrow_item['POSTS']) ? $memberrow_item['POSTS'] : ''; ?><?php } ?></td>
		<td class="info"><?php

$custom_fields_count = ( isset($memberrow_item['custom_fields.']) ) ? sizeof($memberrow_item['custom_fields.']) : 0;
for ($custom_fields_i = 0; $custom_fields_i < $custom_fields_count; $custom_fields_i++)
{
 $custom_fields_item = &$memberrow_item['custom_fields.'][$custom_fields_i];
 $custom_fields_item['S_ROW_COUNT'] = $custom_fields_i;
 $custom_fields_item['S_NUM_ROWS'] = $custom_fields_count;

?><div><?php echo isset($custom_fields_item['PROFILE_FIELD_VALUE']) ? $custom_fields_item['PROFILE_FIELD_VALUE'] : ''; ?></div><?php } if(!$custom_fields_count) { ?>&nbsp;<?php

} // END custom_fields

if(isset($custom_fields_item)) { unset($custom_fields_item); } 

?></td>
		<td><?php echo isset($memberrow_item['JOINED']) ? $memberrow_item['JOINED'] : ''; ?></td>
		<?php if ($this->vars['S_VIEWONLINE']) {  ?><td><?php echo isset($memberrow_item['LAST_ACTIVE']) ? $memberrow_item['LAST_ACTIVE'] : ''; ?>&nbsp;</td><?php } ?>
	</tr>
		<?php } if(!$memberrow_count) { ?>
	<tr class="bg1">
		<td colspan="<?php if ($this->vars['S_VIEWONLINE']) {  ?>5<?php } else { ?>4<?php } ?>"><?php echo isset($this->vars['L_NO_MEMBERS']) ? $this->vars['L_NO_MEMBERS'] : $this->lang('L_NO_MEMBERS'); ?></td>
	</tr>
		<?php

} // END memberrow

if(isset($memberrow_item)) { unset($memberrow_item); } 

?>
	</tbody>
	</table>

	</div>
</div>

<?php if ($this->vars['S_IN_SEARCH_POPUP'] && ! $this->vars['S_SELECT_SINGLE']) {  ?>
<fieldset class="display-actions">
	<input type="submit" name="submit" value="<?php echo isset($this->vars['L_SELECT_MARKED']) ? $this->vars['L_SELECT_MARKED'] : $this->lang('L_SELECT_MARKED'); ?>" class="button2" />
	<div><a href="#" onclick="marklist('results', 'user', true); return false;"><?php echo isset($this->vars['L_MARK_ALL']) ? $this->vars['L_MARK_ALL'] : $this->lang('L_MARK_ALL'); ?></a> &bull; <a href="#" onclick="marklist('results', 'user', false); return false;"><?php echo isset($this->vars['L_UNMARK_ALL']) ? $this->vars['L_UNMARK_ALL'] : $this->lang('L_UNMARK_ALL'); ?></a></div>
</fieldset>
<?php } ?>

<?php if ($this->vars['S_IN_SEARCH_POPUP']) {  ?>
</form>
<form method="post" id="sort-results" action="<?php echo isset($this->vars['S_MODE_ACTION']) ? $this->vars['S_MODE_ACTION'] : $this->lang('S_MODE_ACTION'); ?>">
<?php } ?>

<?php if ($this->vars['S_IN_SEARCH_POPUP'] && ! $this->vars['S_SEARCH_USER']) {  ?>
<fieldset class="display-options">
	<label for="sk"><?php echo isset($this->vars['L_SELECT_SORT_METHOD']) ? $this->vars['L_SELECT_SORT_METHOD'] : $this->lang('L_SELECT_SORT_METHOD'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?> <select name="sk" id="sk"><?php echo isset($this->vars['S_MODE_SELECT']) ? $this->vars['S_MODE_SELECT'] : $this->lang('S_MODE_SELECT'); ?></select></label>
	<label for="sd"><?php echo isset($this->vars['L_ORDER']) ? $this->vars['L_ORDER'] : $this->lang('L_ORDER'); ?> <select name="sd" id="sd"><?php echo isset($this->vars['S_ORDER_SELECT']) ? $this->vars['S_ORDER_SELECT'] : $this->lang('S_ORDER_SELECT'); ?></select></label>
	<input type="submit" name="sort" value="<?php echo isset($this->vars['L_SUBMIT']) ? $this->vars['L_SUBMIT'] : $this->lang('L_SUBMIT'); ?>" class="button2" />
</fieldset>
<?php } ?>

</form>

<div class="action-bar bottom">
	<div class="pagination">
		<?php echo isset($this->vars['TOTAL_USERS']) ? $this->vars['TOTAL_USERS'] : $this->lang('TOTAL_USERS'); ?>
		<?php if ($this->vars['PAGINATION']) {  ?> 
			<?php  $this->set_filename('xs_include_2482c3ba211d4c1efa50a9dab3879de3', 'pagination.html', true);  $this->pparse('xs_include_2482c3ba211d4c1efa50a9dab3879de3');  ?>
		<?php } else { ?> 
			 &bull; <?php echo isset($this->vars['PAGE_NUMBER']) ? $this->vars['PAGE_NUMBER'] : $this->lang('PAGE_NUMBER'); ?>
		<?php } ?>
	</div>
</div>

<?php if ($this->vars['S_IN_SEARCH_POPUP']) {  ?>
	<?php  $this->set_filename('xs_include_9587df0656450a90e8e70f7c5b36178a', 'simple_footer.html', true);  $this->pparse('xs_include_9587df0656450a90e8e70f7c5b36178a');  ?>
<?php } else { ?>
	<?php  $this->set_filename('xs_include_f15048c2b3d2faed0800e582313e34ba', 'jumpbox.html', true);  $this->pparse('xs_include_f15048c2b3d2faed0800e582313e34ba');  ?>
	<!-- INCLUDEX overall_footer.html -->
<?php } ?>
