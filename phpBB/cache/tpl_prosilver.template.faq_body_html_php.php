<?php

// eXtreme Styles mod cache. Generated on Sat, 26 May 2018 20:50:34 +0000 (time=1527367834)

?><!-- INCLUDEX overall_header.html -->

<h2 class="faq-title"><?php echo isset($this->vars['L_FAQ_TITLE']) ? $this->vars['L_FAQ_TITLE'] : $this->lang('L_FAQ_TITLE'); ?></h2>


<div class="panel bg1" id="faqlinks">
	<div class="inner">
		<div class="column1">
		<?php

$faq_block_count = ( isset($this->_tpldata['faq_block.']) ) ?  sizeof($this->_tpldata['faq_block.']) : 0;
for ($faq_block_i = 0; $faq_block_i < $faq_block_count; $faq_block_i++)
{
 $faq_block_item = &$this->_tpldata['faq_block.'][$faq_block_i];
 $faq_block_item['S_ROW_COUNT'] = $faq_block_i;
 $faq_block_item['S_NUM_ROWS'] = $faq_block_count;

?>
			<?php if ($faq_block_item['SWITCH_COLUMN'] || ( $this->vars['SWITCH_COLUMN_MANUALLY'] && $faq_block_item['S_ROW_COUNT'] == 4 )) {  ?>
				</div>

				<div class="column2">
			<?php } ?>

			<dl class="faq">
				<dt><strong><?php echo isset($faq_block_item['BLOCK_TITLE']) ? $faq_block_item['BLOCK_TITLE'] : ''; ?></strong></dt>
				<?php

$faq_row_count = ( isset($faq_block_item['faq_row.']) ) ? sizeof($faq_block_item['faq_row.']) : 0;
for ($faq_row_i = 0; $faq_row_i < $faq_row_count; $faq_row_i++)
{
 $faq_row_item = &$faq_block_item['faq_row.'][$faq_row_i];
 $faq_row_item['S_ROW_COUNT'] = $faq_row_i;
 $faq_row_item['S_NUM_ROWS'] = $faq_row_count;

?>
					<dd><a href="#f<?php echo isset($faq_block_item['S_ROW_COUNT']) ? $faq_block_item['S_ROW_COUNT'] : ''; ?>r<?php echo isset($faq_row_item['S_ROW_COUNT']) ? $faq_row_item['S_ROW_COUNT'] : ''; ?>"><?php echo isset($faq_row_item['FAQ_QUESTION']) ? $faq_row_item['FAQ_QUESTION'] : ''; ?></a></dd>
				<?php

} // END faq_row

if(isset($faq_row_item)) { unset($faq_row_item); } 

?>
			</dl>
		<?php

} // END faq_block

if(isset($faq_block_item)) { unset($faq_block_item); } 

?>
		</div>
	</div>
</div>

<?php

$faq_block_count = ( isset($this->_tpldata['faq_block.']) ) ?  sizeof($this->_tpldata['faq_block.']) : 0;
for ($faq_block_i = 0; $faq_block_i < $faq_block_count; $faq_block_i++)
{
 $faq_block_item = &$this->_tpldata['faq_block.'][$faq_block_i];
 $faq_block_item['S_ROW_COUNT'] = $faq_block_i;
 $faq_block_item['S_NUM_ROWS'] = $faq_block_count;

?>
	<div class="panel <?php if (($faq_block_item['S_ROW_COUNT'] %	2)) {  ?>bg1<?php } else { ?>bg2<?php } ?>">
		<div class="inner">

		<div class="content">
			<h2 class="faq-title"><?php echo isset($faq_block_item['BLOCK_TITLE']) ? $faq_block_item['BLOCK_TITLE'] : ''; ?></h2>
			<?php

$faq_row_count = ( isset($faq_block_item['faq_row.']) ) ? sizeof($faq_block_item['faq_row.']) : 0;
for ($faq_row_i = 0; $faq_row_i < $faq_row_count; $faq_row_i++)
{
 $faq_row_item = &$faq_block_item['faq_row.'][$faq_row_i];
 $faq_row_item['S_ROW_COUNT'] = $faq_row_i;
 $faq_row_item['S_NUM_ROWS'] = $faq_row_count;

?>
				<dl class="faq">
					<dt id="f<?php echo isset($faq_block_item['S_ROW_COUNT']) ? $faq_block_item['S_ROW_COUNT'] : ''; ?>r<?php echo isset($faq_row_item['S_ROW_COUNT']) ? $faq_row_item['S_ROW_COUNT'] : ''; ?>"><strong><?php echo isset($faq_row_item['FAQ_QUESTION']) ? $faq_row_item['FAQ_QUESTION'] : ''; ?></strong></dt>
					<dd><?php echo isset($faq_row_item['FAQ_ANSWER']) ? $faq_row_item['FAQ_ANSWER'] : ''; ?></dd>
					<dd><a href="#faqlinks" class="top2"><?php echo isset($this->vars['L_BACK_TO_TOP']) ? $this->vars['L_BACK_TO_TOP'] : $this->lang('L_BACK_TO_TOP'); ?></a></dd>
				</dl>
				<?php if (! $faq_row_item['S_LAST_ROW']) {  ?><hr class="dashed" /><?php } ?>
			<?php

} // END faq_row

if(isset($faq_row_item)) { unset($faq_row_item); } 

?>
		</div>

		</div>
	</div>
<?php

} // END faq_block

if(isset($faq_block_item)) { unset($faq_block_item); } 

?>

<?php  $this->set_filename('xs_include_807920bd600c8601554adefff6bfcd27', 'jumpbox.html', true);  $this->pparse('xs_include_807920bd600c8601554adefff6bfcd27');  ?>
<!-- INCLUDEX overall_footer.html -->
