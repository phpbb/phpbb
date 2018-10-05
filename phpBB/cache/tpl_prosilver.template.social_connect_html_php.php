<?php

// eXtreme Styles mod cache. Generated on Mon, 24 Sep 2018 03:28:06 +0000 (time=1537759686)

?>	<?php if ($this->vars['SOCIAL_CONNECT']) {  ?>
	<dt class="row1g row-center tw150px" style="padding: 30px; width: 150px;">
		<?php echo isset($this->vars['L_SOCIAL_CONNECT']) ? $this->vars['L_SOCIAL_CONNECT'] : $this->lang('L_SOCIAL_CONNECT'); ?>
		<?php

$social_connect_button_count = ( isset($this->_tpldata['social_connect_button.']) ) ?  sizeof($this->_tpldata['social_connect_button.']) : 0;
for ($social_connect_button_i = 0; $social_connect_button_i < $social_connect_button_count; $social_connect_button_i++)
{
 $social_connect_button_item = &$this->_tpldata['social_connect_button.'][$social_connect_button_i];
 $social_connect_button_item['S_ROW_COUNT'] = $social_connect_button_i;
 $social_connect_button_item['S_NUM_ROWS'] = $social_connect_button_count;

?>
		<a href="<?php echo isset($social_connect_button_item['U_SOCIAL_CONNECT']) ? $social_connect_button_item['U_SOCIAL_CONNECT'] : ''; ?>" title="<?php echo isset($social_connect_button_item['L_SOCIAL_CONNECT']) ? $social_connect_button_item['L_SOCIAL_CONNECT'] : ''; ?>"><?php echo isset($social_connect_button_item['IMG_SOCIAL_CONNECT']) ? $social_connect_button_item['IMG_SOCIAL_CONNECT'] : ''; ?></a>
		<?php

} // END social_connect_button

if(isset($social_connect_button_item)) { unset($social_connect_button_item); } 

?>
	</dt>
	<?php } ?>