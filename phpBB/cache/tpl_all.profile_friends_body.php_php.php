<?php

// eXtreme Styles mod cache. Generated on Wed, 26 Sep 2018 03:16:45 +0000 (time=1537931805)

?><?php echo isset($this->vars['IMG_THL']) ? $this->vars['IMG_THL'] : $this->lang('IMG_THL'); ?><?php echo isset($this->vars['IMG_THC']) ? $this->vars['IMG_THC'] : $this->lang('IMG_THC'); ?><span class="forumlink"><?php echo isset($this->vars['L_FRIENDS']) ? $this->vars['L_FRIENDS'] : $this->lang('L_FRIENDS'); ?></span><?php echo isset($this->vars['IMG_THR']) ? $this->vars['IMG_THR'] : $this->lang('IMG_THR'); ?><table class="forumlinenb">
<tr><th><span class="text_green"><?php echo isset($this->vars['L_FRIENDS_ONLINE']) ? $this->vars['L_FRIENDS_ONLINE'] : $this->lang('L_FRIENDS_ONLINE'); ?></span></th></tr>
<tr>
	<td class="row1 tw100pct">
		<ul style="margin:0;padding:0;list-style-type:none;line-height:175--;">
		<?php

$friends_online_count = ( isset($this->_tpldata['friends_online.']) ) ?  sizeof($this->_tpldata['friends_online.']) : 0;
for ($friends_online_i = 0; $friends_online_i < $friends_online_count; $friends_online_i++)
{
 $friends_online_item = &$this->_tpldata['friends_online.'][$friends_online_i];
 $friends_online_item['S_ROW_COUNT'] = $friends_online_i;
 $friends_online_item['S_NUM_ROWS'] = $friends_online_count;

?>
			<li><?php echo isset($friends_online_item['USERNAME_FULL']) ? $friends_online_item['USERNAME_FULL'] : ''; ?></li>
		<?php

} // END friends_online

if(isset($friends_online_item)) { unset($friends_online_item); } 

?>
		<?php

$no_friends_online_count = ( isset($this->_tpldata['no_friends_online.']) ) ?  sizeof($this->_tpldata['no_friends_online.']) : 0;
for ($no_friends_online_i = 0; $no_friends_online_i < $no_friends_online_count; $no_friends_online_i++)
{
 $no_friends_online_item = &$this->_tpldata['no_friends_online.'][$no_friends_online_i];
 $no_friends_online_item['S_ROW_COUNT'] = $no_friends_online_i;
 $no_friends_online_item['S_NUM_ROWS'] = $no_friends_online_count;

?>
			<li><?php echo isset($this->vars['L_NO_FRIENDS_ONLINE']) ? $this->vars['L_NO_FRIENDS_ONLINE'] : $this->lang('L_NO_FRIENDS_ONLINE'); ?></li>
		<?php

} // END no_friends_online

if(isset($no_friends_online_item)) { unset($no_friends_online_item); } 

?>
		</ul>
	</td>
</tr>
<tr><th><span class="text_red"><?php echo isset($this->vars['L_FRIENDS_OFFLINE']) ? $this->vars['L_FRIENDS_OFFLINE'] : $this->lang('L_FRIENDS_OFFLINE'); ?></span><!--  / <?php echo isset($this->vars['L_FRIENDS_HIDDEN']) ? $this->vars['L_FRIENDS_HIDDEN'] : $this->lang('L_FRIENDS_HIDDEN'); ?> --></th></tr>
<tr>
	<td class="row1 tw100pct">
		<ul style="margin:0;padding:0;list-style-type:none;line-height:175--;">
		<?php

$friends_offline_count = ( isset($this->_tpldata['friends_offline.']) ) ?  sizeof($this->_tpldata['friends_offline.']) : 0;
for ($friends_offline_i = 0; $friends_offline_i < $friends_offline_count; $friends_offline_i++)
{
 $friends_offline_item = &$this->_tpldata['friends_offline.'][$friends_offline_i];
 $friends_offline_item['S_ROW_COUNT'] = $friends_offline_i;
 $friends_offline_item['S_NUM_ROWS'] = $friends_offline_count;

?>
			<li><?php echo isset($friends_offline_item['USERNAME_FULL']) ? $friends_offline_item['USERNAME_FULL'] : ''; ?></li>
		<?php

} // END friends_offline

if(isset($friends_offline_item)) { unset($friends_offline_item); } 

?>
		<?php

$no_friends_offline_count = ( isset($this->_tpldata['no_friends_offline.']) ) ?  sizeof($this->_tpldata['no_friends_offline.']) : 0;
for ($no_friends_offline_i = 0; $no_friends_offline_i < $no_friends_offline_count; $no_friends_offline_i++)
{
 $no_friends_offline_item = &$this->_tpldata['no_friends_offline.'][$no_friends_offline_i];
 $no_friends_offline_item['S_ROW_COUNT'] = $no_friends_offline_i;
 $no_friends_offline_item['S_NUM_ROWS'] = $no_friends_offline_count;

?>
			<li><?php echo isset($this->vars['L_NO_FRIENDS_OFFLINE']) ? $this->vars['L_NO_FRIENDS_OFFLINE'] : $this->lang('L_NO_FRIENDS_OFFLINE'); ?></li>
		<?php

} // END no_friends_offline

if(isset($no_friends_offline_item)) { unset($no_friends_offline_item); } 

?>
		</ul>
	</td>
</tr>
<!-- <tr><td class="cat">&nbsp;</td></tr> -->
</table><?php echo isset($this->vars['IMG_TFL']) ? $this->vars['IMG_TFL'] : $this->lang('IMG_TFL'); ?><?php echo isset($this->vars['IMG_TFC']) ? $this->vars['IMG_TFC'] : $this->lang('IMG_TFC'); ?><?php echo isset($this->vars['IMG_TFR']) ? $this->vars['IMG_TFR'] : $this->lang('IMG_TFR'); ?>