<!-- BEGIN xs_file_version -->
/**
*
* @package Icy Phoenix eXtreme Style 2.4.1
* @file $Id config.tpl
* @author Vjacheslav Trushkin
* @copyright (C) 2003 - 2007
* @support http://www.stsoftware.biz/forum
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
**/
<!-- END xs_file_version -->

<h1>{L_XS_CONFIG_MAINTITLE}</h1>

<p>{L_XS_CONFIG_SUBTITLE}</p>

<!-- BEGIN left_refresh -->
<script type="text/javascript">
<!--
top.nav.location = top.nav.location; // '{left_refresh.ACTION}';
//-->
</script>
<!-- END left_refresh -->
<!-- BEGIN switch_updated -->
<table class="forumline">
	<tr><th class="th25px">{L_XS_CONFIG_UPDATED}</th></tr>
	<tr>
		<td class="row1">
			<table class="p2px">
				<tr><td>&nbsp;</td></tr>
				<tr><td class="tdalignc"><span class="gen">{L_XS_CONFIG_UPDATED_EXPLAIN}</span></td></tr>
				<tr><td>&nbsp;</td></tr>
			</table>
		</td>
	</tr>
</table>
<br />
<!-- END switch_updated -->

<!-- BEGIN switch_xs_warning -->
<table class="forumline">
	<tr><th class="th25px">{L_XS_CONFIG_WARNING}</th></tr>
	<tr>
		<td class="row1">
			<table class="p2px">
				<tr><td>&nbsp;</td></tr>
				<tr><td class="tdalignc"><span class="gen">{L_XS_CONFIG_WARNING_EXPLAIN}</span></td></tr>
				<tr><td>&nbsp;</td></tr>
			</table>
		</td>
	</tr>
</table>
<br />
<!-- END switch_xs_warning -->

<!-- BEGIN noftp -->
<table class="forumline">
	<tr><th class="th25px">{L_Error}</th></tr>
	<tr>
		<td class="row1">
			<table class="p2px">
				<tr><td>&nbsp;</td></tr>
				<tr><td class="tdalignc"><span class="gen">{L_XS_FTP_COMMENT3}</span></td></tr>
				<tr><td>&nbsp;</td></tr>
			</table>
		</td>
	</tr>
</table>
<br />
<!-- END noftp -->

<!-- BEGIN ftperror -->
<table class="forumline">
	<tr><th class="th25px">{L_Error}</th></tr>
	<tr>
		<td class="row1"><table class="p2px">
			<tr><td>&nbsp;</td></tr>
			<tr><td class="tdalignc"><span class="gen">{ftperror.ERROR}</span></td></tr>
			<tr><td>&nbsp;</td></tr>
		</table></td>
	</tr>
</table>
<br />
<!-- END ftperror -->

<form name="config" action="{FORM_ACTION}" method="post" style="display: inline;">
<table class="forumline">
<tr><th colspan="2">{L_XS_CONFIG_TITLE}</th></tr>
<tr>
	<td class="row1">{L_XS_CONFIG_NAVBAR}<br /><span class="gensmall">{L_XS_CONFIG_NAVBAR_EXPLAIN}</span></td>
	<td class="row2 tdnw">
		<!-- BEGIN shownav -->
		<label><input type="checkbox" name="shownav_{shownav.NUM}" {shownav.CHECKED} /> {shownav.LABEL}</label><br />
		<!-- END shownav -->
	</td>
</tr>
<tr>
	<td class="row1">{L_XS_CONFIG_DEF_TEMPLATE}<br /><span class="gensmall">{L_XS_CONFIG_DEF_TEMPLATE_EXPLAIN}</span></td>
	<td class="row2"><input class="post" type="text" name="xs_def_template" value="{XS_DEF_TEMPLATE}" /></td>
</tr>
<tr>
	<td class="row1">{L_XS_CONFIG_CHECK_SWITCHES}<br /><span class="gensmall">{L_XS_CONFIG_CHECK_SWITCHES_EXPLAIN}</span></td>
	<td class="row2">
		<label><input type="radio" name="xs_check_switches" value="0" {XS_CHECK_SWITCHES_0} /> {L_XS_CONFIG_CHECK_SWITCHES_0}</label><br />
		<br />
		<label><input type="radio" name="xs_check_switches" value="2" {XS_CHECK_SWITCHES_2} /> {L_XS_CONFIG_CHECK_SWITCHES_2}</label><br />
		<br />
		<label><input type="radio" name="xs_check_switches" value="1" {XS_CHECK_SWITCHES_1} /> {L_XS_CONFIG_CHECK_SWITCHES_1}</label>
	</td>
</tr>
<tr>
	<td class="row1">{L_XS_CONFIG_SHOW_ERRORS}<br /><span class="gensmall">{L_XS_CONFIG_SHOW_ERROR_EXPLAIN}</span></td>
	<td class="row2"><label><input type="radio" name="xs_warn_includes" value="1" {XS_WARN_INCLUDES_1} /> {L_YES}</label>&nbsp;&nbsp;<label><input type="radio" name="xs_warn_includes" value="0" {XS_WARN_INCLUDES_0} /> {L_NO}</label></td>
</tr>
<tr>
	<td class="row1">{L_XS_CONFIG_TPL_COMMENTS}<br /><span class="gensmall">{L_XS_CONFIG_TPL_COMMENTS_EXPLAIN}</span></td>
	<td class="row2"><label><input type="radio" name="xs_add_comments" value="1" {XS_ADD_COMMENTS_1} /> {L_YES}</label>&nbsp;&nbsp;<label><input type="radio" name="xs_add_comments" value="0" {XS_ADD_COMMENTS_0} /> {L_NO}</label></td>
</tr>
<tr><th colspan="2">{L_XS_CONFIG_CACHE}</th></tr>
<tr>
	<td class="row1">{L_XS_CONFIG_USE_CACHE}<br /><span class="gensmall">{L_XS_CONFIG_USE_CACHE_EXPLAIN}</span></td>
	<td class="row2"><label><input type="radio" name="xs_use_cache" value="1" {XS_USE_CACHE_1} /> {L_YES}</label>&nbsp;&nbsp;<label><input type="radio" name="xs_use_cache" value="0" {XS_USE_CACHE_0} /> {L_NO}</label></td>
</tr>
<tr>
	<td class="row1">{L_XS_CONFIG_AUTO_COMPILE}<br /><span class="gensmall">{L_XS_CONFIG_AUTO_COMPILE_EXPLAIN}</span></td>
	<td class="row2"><label><input type="radio" name="xs_auto_compile" value="1" {XS_AUTO_COMPILE_1} /> {L_YES}</label>&nbsp;&nbsp;<label><input type="radio" name="xs_auto_compile" value="0" {XS_AUTO_COMPILE_0} /> {L_NO}</label></td>
</tr>
<tr>
	<td class="row1">{L_XS_CONFIG_AUTO_RECOMPILE}<br /><span class="gensmall">{L_XS_CONFIG_AUTO_RECOMPILE_EXPLAIN}</span></td>
	<td class="row2"><label><input type="radio" name="xs_auto_recompile" value="1" {XS_AUTO_RECOMPILE_1} /> {L_YES}</label>&nbsp;&nbsp;<label><input type="radio" name="xs_auto_recompile" value="0" {XS_AUTO_RECOMPILE_0} /> {L_NO}</label></td>
</tr>
<tr>
	<td class="row1">{L_XS_CONFIG_PHP}<br /><span class="gensmall">{L_XS_CONFIG_PHP_EXPLAIN}</span></td>
	<td class="row2"><input class="post" type="text" name="xs_php" value="{XS_PHP}" /></td>
</tr>
<tr><th colspan="2">{L_XS_FTP_CONFIG}</th></tr>
<tr><td class="explain" colspan="2" align="left">{L_XS_FTP_EXPLAIN}</td></tr>
<tr>
	<td class="row1">{L_XS_FTP_HOST}{HOST_GUESS}:</td>
	<td class="row2"><input class="post" type="text" name="xs_ftp_host" value="{XS_FTP_HOST}" /></td>
</tr>
<tr>
	<td class="row1">{L_XS_FTP_LOGIN}{LOGIN_GUESS}:</td>
	<td class="row2"><input class="post" type="text" name="xs_ftp_login" value="{XS_FTP_LOGIN}" /></td>
</tr>
<tr>
	<td class="row1">{L_XS_FTP_PATH}{PATH_GUESS}:</td>
	<td class="row2"><input class="post" type="text" name="xs_ftp_path" value="{XS_FTP_PATH}" /></td>
</tr>
<tr>
	<td class="catBottom" colspan="2" align="center">{S_HIDDEN_FIELDS}<input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption" /></td>
</tr>
</table>
</form>

<br class="clear" />


<table class="forumline">
<tr><th colspan="2">{L_XS_DEBUG_HEADER}</th></tr>
<tr><td colspan="2" class="explain" align="left">{L_XS_DEBUG_EXPLAIN}</td></tr>
<tr><th colspan="2">{XS_DEBUG_HDR1}</th></tr>
<tr>
	<td class="row1"><span class="gen">{L_XS_DEBUG_TPL_NAME}</span></td>
	<td class="row2"><span class="gen">{XS_DEBUG_FILENAME1}</span></td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_XS_DEBUG_CACHE_FILENAME}</span></td>
	<td class="row2"><span class="gen">{XS_DEBUG_FILENAME2}</span></td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_XS_DEBUG_DATA}</span></td>
	<td class="row2"><span class="gensmall">{XS_DEBUG_DATA}</span></td>
</tr>
</table>