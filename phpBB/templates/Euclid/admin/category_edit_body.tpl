<br clear="all" />

<h1>Edit Category</h1>

<p>Use this form to modify a category.</p>

<form action="{S_FORUM_ACTION}" method="POST">

<table width="99%" cellpadding="1" cellspacing="0" border="0" align="center">
	<tr>
		<td class="tablebg" width="100%"><table width="100%" cellpadding="4" cellspacing="1" border="0">
			<tr>
				<td class="cat" colspan="2"><span class="cattitle">Modify Category</span></td>
			</tr>
			<tr>
				<td class="row1">Category Name:</td>
				<td class="row2"><input type="text" size="25" name="cat_title" value="{CAT_TITLE}"></td>
			</tr>
			<tr>
				<td class="row2" colspan="2" align="center">
					<input type="hidden" name="mode" value="{S_NEWMODE}">
					<input type="hidden" name="cat_id" value="{S_CATID}">
					<input type="submit" name="submit" value="{BUTTONVALUE}">
				</td>
			</tr>
		</table></td>
	</tr>
</table>

</form>
		
<br clear="all">
