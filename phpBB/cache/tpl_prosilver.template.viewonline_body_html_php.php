<?php

// eXtreme Styles mod cache. Generated on Thu, 17 May 2018 10:04:44 +0000 (time=1526551484)

?><!-- INCLUDEX overall_header.html -->

<h2 class="viewonline-title"><?php echo isset($this->vars['TOTAL_REGISTERED_USERS_ONLINE']) ? $this->vars['TOTAL_REGISTERED_USERS_ONLINE'] : $this->lang('TOTAL_REGISTERED_USERS_ONLINE'); ?></h2>
<p><?php echo isset($this->vars['TOTAL_GUEST_USERS_ONLINE']) ? $this->vars['TOTAL_GUEST_USERS_ONLINE'] : $this->lang('TOTAL_GUEST_USERS_ONLINE'); ?><?php if ($this->vars['S_SWITCH_GUEST_DISPLAY']) {  ?> &bull; <a href="<?php echo isset($this->vars['U_SWITCH_GUEST_DISPLAY']) ? $this->vars['U_SWITCH_GUEST_DISPLAY'] : $this->lang('U_SWITCH_GUEST_DISPLAY'); ?>"><?php echo isset($this->vars['L_SWITCH_GUEST_DISPLAY']) ? $this->vars['L_SWITCH_GUEST_DISPLAY'] : $this->lang('L_SWITCH_GUEST_DISPLAY'); ?></a><?php } ?></p>

<div class="action-bar top">
	<div class="pagination">
		<?php if ($this->vars['PAGINATION']) {  ?> 
			<?php  $this->set_filename('xs_include_727b4233e57a049958366a999eaf825e', 'pagination.html', true);  $this->pparse('xs_include_727b4233e57a049958366a999eaf825e');  ?>
		<?php } else { ?> 
			<?php echo isset($this->vars['PAGE_NUMBER']) ? $this->vars['PAGE_NUMBER'] : $this->lang('PAGE_NUMBER'); ?>
		<?php } ?>
	</div>
</div>

<div class="forumbg forumbg-table">
	<div class="inner">
	
	<table class="table1">

	<?php if ($user_row) {  ?>
		<thead>
		<tr>
			<th class="name"><a href="<?php echo isset($this->vars['U_SORT_USERNAME']) ? $this->vars['U_SORT_USERNAME'] : $this->lang('U_SORT_USERNAME'); ?>"><?php echo isset($this->vars['L_USERNAME']) ? $this->vars['L_USERNAME'] : $this->lang('L_USERNAME'); ?></a></th>
			<th class="info"><a href="<?php echo isset($this->vars['U_SORT_LOCATION']) ? $this->vars['U_SORT_LOCATION'] : $this->lang('U_SORT_LOCATION'); ?>"><?php echo isset($this->vars['L_FORUM_LOCATION']) ? $this->vars['L_FORUM_LOCATION'] : $this->lang('L_FORUM_LOCATION'); ?></a></th>
			<th class="active"><a href="<?php echo isset($this->vars['U_SORT_UPDATED']) ? $this->vars['U_SORT_UPDATED'] : $this->lang('U_SORT_UPDATED'); ?>"><?php echo isset($this->vars['L_LAST_UPDATED']) ? $this->vars['L_LAST_UPDATED'] : $this->lang('L_LAST_UPDATED'); ?></a></th>
		</tr>
		</thead>
		<tbody>
		<?php

$user_row_count = ( isset($this->_tpldata['user_row.']) ) ?  sizeof($this->_tpldata['user_row.']) : 0;
for ($user_row_i = 0; $user_row_i < $user_row_count; $user_row_i++)
{
 $user_row_item = &$this->_tpldata['user_row.'][$user_row_i];
 $user_row_item['S_ROW_COUNT'] = $user_row_i;
 $user_row_item['S_NUM_ROWS'] = $user_row_count;

?>
		<tr class="<?php if (($user_row_item['S_ROW_COUNT'] %	2)) {  ?>bg1<?php } else { ?>bg2<?php } ?>">
			<td><?php echo isset($user_row_item['USERNAME_FULL']) ? $user_row_item['USERNAME_FULL'] : ''; ?><?php if ($user_row_item['USER_IP']) {  ?> <span style="float: <?php echo isset($this->vars['S_CONTENT_FLOW_END']) ? $this->vars['S_CONTENT_FLOW_END'] : $this->lang('S_CONTENT_FLOW_END'); ?>;"><?php echo isset($this->vars['L_IP']) ? $this->vars['L_IP'] : $this->lang('L_IP'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?> <a href="<?php echo isset($user_row_item['U_USER_IP']) ? $user_row_item['U_USER_IP'] : ''; ?>"><?php echo isset($user_row_item['USER_IP']) ? $user_row_item['USER_IP'] : ''; ?></a> &#187; <a href="<?php echo isset($user_row_item['U_WHOIS']) ? $user_row_item['U_WHOIS'] : ''; ?>" onclick="popup(this.href, 750, 500); return false;"><?php echo isset($this->vars['L_WHOIS']) ? $this->vars['L_WHOIS'] : $this->lang('L_WHOIS'); ?></a></span><?php } ?>
				<?php if ($user_row_item['USER_BROWSER']) {  ?><br /><?php echo isset($user_row_item['USER_BROWSER']) ? $user_row_item['USER_BROWSER'] : ''; ?><?php } ?></td>
			<td class="info"><a href="<?php echo isset($user_row_item['U_FORUM_LOCATION']) ? $user_row_item['U_FORUM_LOCATION'] : ''; ?>"><?php echo isset($user_row_item['FORUM_LOCATION']) ? $user_row_item['FORUM_LOCATION'] : ''; ?></a></td>
			<td class="active"><?php echo isset($user_row_item['LASTUPDATE']) ? $user_row_item['LASTUPDATE'] : ''; ?></td>
		</tr>
		<?php

} // END user_row

if(isset($user_row_item)) { unset($user_row_item); } 

?>
	<?php } else { ?>
		<tbody>
		<tr class="bg1">
			<td colspan="3"><?php echo isset($this->vars['L_NO_ONLINE_USERS']) ? $this->vars['L_NO_ONLINE_USERS'] : $this->lang('L_NO_ONLINE_USERS'); ?><?php if ($this->vars['S_SWITCH_GUEST_DISPLAY']) {  ?> &bull; <a href="<?php echo isset($this->vars['U_SWITCH_GUEST_DISPLAY']) ? $this->vars['U_SWITCH_GUEST_DISPLAY'] : $this->lang('U_SWITCH_GUEST_DISPLAY'); ?>"><?php echo isset($this->vars['L_SWITCH_GUEST_DISPLAY']) ? $this->vars['L_SWITCH_GUEST_DISPLAY'] : $this->lang('L_SWITCH_GUEST_DISPLAY'); ?></a><?php } ?></td>
		</tr>
	<?php } ?>
	</tbody>
	</table>
	
	</div>
</div>

<?php if ($this->vars['LEGEND']) {  ?><p><em><?php echo isset($this->vars['L_LEGEND']) ? $this->vars['L_LEGEND'] : $this->lang('L_LEGEND'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?> <?php echo isset($this->vars['LEGEND']) ? $this->vars['LEGEND'] : $this->lang('LEGEND'); ?></em></p><?php } ?>

<div class="action-bar bottom">
	<div class="pagination">
		<?php if ($this->vars['PAGINATION']) {  ?> 
			<?php  $this->set_filename('xs_include_dd5f400607d7db2a6af9ce8d7d6d07e3', 'pagination.html', true);  $this->pparse('xs_include_dd5f400607d7db2a6af9ce8d7d6d07e3');  ?>
		<?php } else { ?> 
			<?php echo isset($this->vars['PAGE_NUMBER']) ? $this->vars['PAGE_NUMBER'] : $this->lang('PAGE_NUMBER'); ?>
		<?php } ?>
	</div>
</div>

<?php  $this->set_filename('xs_include_a4a089886914637decafe19a02ff3ada', 'jumpbox.html', true);  $this->pparse('xs_include_a4a089886914637decafe19a02ff3ada');  ?>
<!-- INCLUDEX overall_footer.html -->
