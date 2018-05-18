<!-- BEGIN xs_file_version -->
/***************************************************************************
 *                                import.tpl
 *                                ----------
 *   copyright            : (C) 2003 - 2005 Vjacheslav Trushkin
 *   support              : http://www.stsoftware.biz/forum
 *
 *   version              : 2.4.0
 *
 *   file revision        : 79
 *   project revision     : 83
 *   last modified        : 12 Mar 2007  10:28:53
 *
 ***************************************************************************/
<!-- END xs_file_version -->

<h1>{L_XS_IMPORT_STYLES}</h1>

<p>{L_XS_IMPORT_EXPLAIN}</p>

<table width="100%" cellpadding="4" cellspacing="1" border="0" class="forumline">
	<tr>
		<th class="thCornerL" align="center" nowrap="nowrap">{L_XS_FILE}</th>
		<th class="thTop" align="center" nowrap="nowrap">{L_XS_TEMPLATE}</th>
		<th class="thTop" align="center" nowrap="nowrap">{L_XS_STYLES}</th>
		<th class="thTop" align="center" nowrap="nowrap">{L_XS_UPLOAD_TIME}</th>
		<th class="thTop" align="center" nowrap="nowrap">{L_XS_COMMENT}</th>
		<th class="thCornerR" align="center" nowrap="nowrap">{L_XS_OPTIONS}</th>
	</tr>
	<!-- BEGIN styles -->
	<tr>
		<td class="{styles.ROW_CLASS}" align="left"><span class="gensmall">{styles.FILE2}</span></td>
		<td class="{styles.ROW_CLASS}" align="left"><span class="gen"><!-- BEGIN valid -->{styles.TEMPLATE}<!-- END valid --><!-- BEGIN error -->-<!-- END error --></span></td>
		<td class="{styles.ROW_CLASS}" align="left"><span class="gen"><!-- BEGIN list -->{list.STYLE}<br /><!-- END list --></span></td>
		<td class="{styles.ROW_CLASS}" align="center" nowrap="nowrap"><span class="genmed"><!-- BEGIN valid -->{styles.DATE}<!-- END valid --><!-- BEGIN error -->-<!-- END error --></span></td>
		<td class="{styles.ROW_CLASS}" align="left"><span class="gensmall"><!-- BEGIN valid -->{styles.COMMENT}<!-- END valid --><!-- BEGIN error -->{styles.error.ERROR}<!-- END error --></span></td>
		<td class="{styles.ROW_CLASS}" align="center">
		<!-- BEGIN valid -->
			[<a href="{styles.U_IMPORT}">{L_XS_IMPORT_LC}</a>]
			[<a href="{styles.U_LIST}">{L_XS_LIST_FILES_LC}</a>]
		<!-- END valid -->
			[<a href="{styles.U_DELETE}">{L_XS_DELETE_FILE_LC}</a>]
		</td>
	</tr>
	<!-- END styles -->
	<!-- BEGIN nostyles -->
	<tr>
		<td colspan="6" align="center" class="row1"><span class="gen">{L_XS_IMPORT_NO_CACHED}</span></td>
	</tr>
	<!-- END nostyles -->
</table>

<br />

<table width="100%">

<table width="100%" cellpadding="4" cellspacing="1" border="0" align="center" class="forumline">
	<tr>
	  <th class="thHead" colspan="2">{L_XS_ADD_STYLES}</th>
	</tr>
	<tr>
		<td class="row1">{L_XS_ADD_STYLES_WEB}:</td>
		<td class="row2" nowrap="nowrap">
			<form action="{U_SCRIPT}" method="post" style="display: inline;"><input type="hidden" name="action" value="web" />{S_HIDDEN_FIELDS}
			<input type="text" name="source" size="40" value="http://" />
			<input type="submit" value="{L_XS_ADD_STYLES_WEB_GET}" class="mainoption" />
			</form>
		</tr>
	</tr>
	<tr>
		<td class="row1">{L_XS_ADD_STYLES_COPY}:</td>
		<td class="row2" nowrap="nowrap">
			<form action="{U_SCRIPT}" method="post" style="display: inline;"><input type="hidden" name="action" value="copy" />{S_HIDDEN_FIELDS}
			<input type="text" name="source" size="40" value="" />
			<input type="submit" value="{L_XS_ADD_STYLES_COPY_GET}" class="mainoption" />
			</form>
		</tr>
	</tr>
	<tr>
		<td class="row1">{L_XS_ADD_STYLES_UPLOAD}:</td>
		<td class="row2" nowrap="nowrap">
			<form action="{U_SCRIPT}" method="post" enctype="multipart/form-data" style="display: inline;"><input type="hidden" name="action" value="upload" />{S_HIDDEN_FIELDS}
			<input type="file" name="source" size="30" />
			<input type="submit" value="{L_XS_ADD_STYLES_UPLOAD_GET}" class="mainoption" />
			</form>
		</tr>
	</tr>
</table>

<br />