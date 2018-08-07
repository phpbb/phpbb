<?php

// eXtreme Styles mod cache. Generated on Fri, 27 Jul 2018 16:44:47 +0000 (time=1532709887)

?>	<?php if ($this->_tpldata['DEFINE']['.']['S_MICRODATA']) {  ?><?php $this->_tpldata['DEFINE']['.']['MICRODATA'] = ' itemtype=\"http://data-vocabulary.org/Breadcrumb\" itemscope=\"\"'; ?><?php } else { ?><!-- DEFINE $MICRODATA = '' --><?php } ?>
	<table class="tablebg" width="100--" cellspacing="1" cellpadding="0" style="margin-top: 5px;">
	<tr>
		<td class="row1">
			<!-- EVENT overall_header_breadcrumbs_before -->
			<p class="breadcrumbs"><?php if ($this->vars['U_SITE_HOME']) {  ?><span<?php echo isset($this->_tpldata['DEFINE']['.']['MICRODATA']) ? $this->_tpldata['DEFINE']['.']['MICRODATA'] : ''; ?>><a href="<?php echo isset($this->vars['U_SITE_HOME']) ? $this->vars['U_SITE_HOME'] : $this->lang('U_SITE_HOME'); ?>" data-navbar-reference="home" itemprop="url"><span itemprop="title"><?php echo isset($this->vars['L_SITE_HOME']) ? $this->vars['L_SITE_HOME'] : $this->lang('L_SITE_HOME'); ?></span></a></span> <strong>&#187;</strong> <?php } ?><?php if ($this->_tpldata['DEFINE']['.']['OVERALL_HEADER_BREADCRUMBS']) {  ?><!-- EVENT overall_header_breadcrumb_prepend --><?php } else { ?><!-- EVENT overall_footer_breadcrumb_prepend --><?php } ?><span<?php echo isset($this->_tpldata['DEFINE']['.']['MICRODATA']) ? $this->_tpldata['DEFINE']['.']['MICRODATA'] : ''; ?>><a href="<?php echo isset($this->vars['U_INDEX']) ? $this->vars['U_INDEX'] : $this->lang('U_INDEX'); ?>" data-navbar-reference="index" itemprop="url"><span itemprop="title"><?php echo isset($this->vars['L_INDEX']) ? $this->vars['L_INDEX'] : $this->lang('L_INDEX'); ?></span></a></span><?php

$navlinks_count = ( isset($this->_tpldata['navlinks.']) ) ?  sizeof($this->_tpldata['navlinks.']) : 0;
for ($navlinks_i = 0; $navlinks_i < $navlinks_count; $navlinks_i++)
{
 $navlinks_item = &$this->_tpldata['navlinks.'][$navlinks_i];
 $navlinks_item['S_ROW_COUNT'] = $navlinks_i;
 $navlinks_item['S_NUM_ROWS'] = $navlinks_count;

?><!-- EVENT overall_header_navlink_prepend --> &#187; <span<?php echo isset($this->_tpldata['DEFINE']['.']['MICRODATA']) ? $this->_tpldata['DEFINE']['.']['MICRODATA'] : ''; ?><?php if ($navlinks_item['MICRODATA']) {  ?> <?php echo isset($navlinks_item['MICRODATA']) ? $navlinks_item['MICRODATA'] : ''; ?><?php } ?>><a href="<?php echo isset($navlinks_item['U_VIEW_FORUM']) ? $navlinks_item['U_VIEW_FORUM'] : ''; ?>" itemprop="url"><span itemprop="title"><?php echo isset($navlinks_item['FORUM_NAME']) ? $navlinks_item['FORUM_NAME'] : ''; ?></span></a></span><!-- EVENT overall_header_navlink_append --><?php

} // END navlinks

if(isset($navlinks_item)) { unset($navlinks_item); } 

?>
			<?php if ($this->_tpldata['DEFINE']['.']['OVERALL_HEADER_BREADCRUMBS']) {  ?><!-- EVENT overall_header_breadcrumb_append --><?php } else { ?><!-- EVENT overall_footer_breadcrumb_append --><?php } ?></p>
			<!-- EVENT overall_header_breadcrumbs_after -->
			<!-- EVENT overall_footer_timezone_before -->
			<p class="datetime"><?php echo isset($this->vars['S_TIMEZONE']) ? $this->vars['S_TIMEZONE'] : $this->lang('S_TIMEZONE'); ?></p>
			<!-- EVENT overall_footer_timezone_after -->
		</td>
	</tr>
	</table>
