<?php
$this->vars['_TITLE'] = $this->vars['L_FAQ_TITLE'];
?><!-- INCLUDE overall_header.tpl -->


<table width="100%" cellspacing="2" cellpadding="2" border="0" align="center">
	<tr>
		<td align="left" class="nav"><a href="{U_INDEX}" class="nav">{L_INDEX}</a></td>
	</tr>
</table>

<table width="100%" cellspacing="0" cellpadding="2" border="0" align="center">
  <tr> 
	<td align="left" valign="bottom"><span class="gensmall">
<!-- BEGIN switch_header_table -->
<div class="block">
	<h2>{L_STAFF_MESSAGE}</h2>
	{switch_header_table.HEADER_TEXT}
</div>
<!-- END switch_header_table -->
	</td>
  </tr>
</table>


<br clear="all" />

<table class="tablebg" cellspacing="1" width="100%">
	<tr>
		<th class="thHead">{L_FAQ_TITLE}</th>
	</tr>
	<tr>
		<td class="row1">
			<!-- BEGIN faq_block_link -->
			<span class="gen"><b>{faq_block_link.BLOCK_TITLE}</b></span><br />
			<!-- BEGIN faq_row_link -->
			<span class="gen"><a href="{faq_block_link.faq_row_link.U_FAQ_LINK}" class="postlink">{faq_block_link.faq_row_link.FAQ_LINK}</a></span><br />
			<!-- END faq_row_link -->
			<br />
			<!-- END faq_block_link -->
		</td>
	</tr>
	<tr>
		<td class="catBottom" height="28">&nbsp;</td>
	</tr>
</table>

<br clear="all" />
<p>
<!-- BEGIN faq_block -->
<div class="block">

	<dl> 
		<dd class="catHead" height="28" align="center"><span class="cattitle"></span></dd>
	</dl>
	<h2>{faq_block.BLOCK_TITLE}</h2>
	<ul class="sitemap">
	<!-- BEGIN faq_row -->
	<li>
		<h2 class="sitemap">{faq_block.faq_row.FAQ_QUESTION}</h2>
		<ul class="sitemap"><li><div class="post-text">{faq_block.faq_row.FAQ_ANSWER}</div><br clear="all" /></li></ul>
	</li>
	<!-- END faq_row -->
	</ul>
	<br /><a class="postlink" href="#top">{L_BACK_TO_TOP}</a></span>
	
</div>
<!-- END faq_block -->
</p>


<table width="100%" cellspacing="2" border="0" align="center">
	<tr>
		<td align="right" valign="middle" nowrap="nowrap"><span class="gensmall">{S_TIMEZONE}</span><br /><br /></td> 
	</tr>
</table>

<div align="right">{JUMPBOX}</div>
<!-- INCLUDE overall_footer.tpl -->