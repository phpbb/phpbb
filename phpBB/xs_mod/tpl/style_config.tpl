<!-- BEGIN xs_file_version -->
/***************************************************************************
 *                             style_config.tpl
 *                             ----------------
 *   copyright            : (C) 2003 - 2005 Vjacheslav Trushkin
 *   support              : http://www.stsoftware.biz/forum
 *
 *   version              : 2.4.0
 *
 *   file revision        : 79
 *   project revision     : 83
 *   last modified        : 12 Mar 2007  10:28:52
 *
 ***************************************************************************/
<!-- END xs_file_version -->

<h1>{L_XS_STYLE_CONFIGURATION}: {TPL}</h1>

<form action="{U_ACTION}" method="post"><input type="hidden" name="tpl" value="{TPL}" />
<table class="forumline" width="100%" cellspacing="1" cellpadding="4" border="0">
<!-- BEGIN item -->
<!-- BEGIN cat -->
<tr>
	<th colspan="2">{item.cat.TEXT}</th>
</tr>
<!-- END cat -->
<tr>
	<td class="row1">{item.TEXT}<!-- IF item.EXPLAIN --><br /><span class="gensmall">{item.EXPLAIN}</span><!-- ENDIF --></td>
	<td class="row2">
	<!-- IF item.TYPE === "bool" -->
		<label><input type="radio" name="{item.VAR}" value="1" <!-- IF item.VALUE == 1 -->checked="checked"<!-- ENDIF --> /> {L_Yes}</label>
		&nbsp; &nbsp;
		<label><input type="radio" name="{item.VAR}" value="0" <!-- IF item.VALUE == 0 -->checked="checked"<!-- ENDIF --> /> {L_No}</label>
	<!-- ELSEIF item.TYPE === "text" -->
		<input type="text" class="post" name="{item.VAR}" value="{item.VALUE}" size="30" />
	<!-- ELSEIF item.TYPE === "select" -->
		<!-- BEGIN select -->
		<label><input type="radio" name="{item.VAR}" value="{item.select.VALUE}"<!-- IF item.select.SELECTED --> checked="checked"<!-- ENDIF --> /> {item.select.TEXT}</label><br />
		<!-- END select -->
	<!-- ELSEIF item.TYPE === "list" -->
		<!-- BEGIN list -->
			<label><input type="checkbox" name="{item.VAR}[{item.list.VALUE}]" <!-- IF item.list.SELECTED -->checked="checked"<!-- ENDIF --> /> {item.list.TEXT}</label><br />
		<!-- END list -->
	<!-- ELSE -->
	<input type="hidden" name="{item.VAR}" value="{item.VALUE}" />
	<!-- ENDIF -->
	</td>
</tr>
<!-- END item -->
<tr>
	<td class="catBottom" colspan="2" align="center">{S_HIDDEN_FIELDS}<input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption" /></td>
</tr>
</table>
</form>
<br />

<br />
