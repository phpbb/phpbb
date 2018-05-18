<!-- INCLUDE overall_inc_footer.tpl -->

<!-- IF GF_BLOCK --><!-- BEGIN gfooter_blocks_row -->{gfooter_blocks_row.CMS_BLOCK}<!-- END gfooter_blocks_row --><!-- ENDIF -->

{BOTTOM_HTML_BLOCK}

<!-- IF not S_BOT -->{RUN_CRON_TASK}<!-- ENDIF -->

</div>
<div id="page-footer" class="gradient">
<p class="left">{COPYRIGHT_LINK}</p>
<p class="right"><!-- IF S_MOBILE -->{MOBILE_STYLE}<!-- ENDIF -->{TEMPLATE_COPYRIGHT_LINK}</p>
</div>
{GOOGLE_ANALYTICS}
</body>
</html>
