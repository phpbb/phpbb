<HTML>
<HEAD>
 <TITLE>phpBB Version 2.0 Installation</TITLE>
</HEAD>
<BODY BACKGROUND="WHITE">
 <FORM action='{S_FORM_ACTION}' method="post" >
 	<!-- BEGIN hidden_fields -->
	<input type="hidden" name='{hidden_fields.NAME}' value='{hidden_fields.VALUE}'>
	<!-- END hidden_fields -->
	<table>
	 	<tr>
			<td>{L_INSTRUCT}</td>
		</tr>
	</table>
	<table>
	<!-- BEGIN selects -->
	 	<tr>
		 <td align="right">{selects.L_LABEL}</td>
		 <td><select name='{selects.NAME}'>
		 	<!-- BEGIN options -->
			<option value='{selects.options.VALUE}' {selects.options.DEFAULT}>{selects.options.LABEL}</option>
			<!-- END options -->
		 </td>
	 	</tr>
	<!-- END selects -->
	<!-- BEGIN inputs -->
		<tr>	
			<td align="right">{inputs.L_LABEL}</td>
			<td><input type="{inputs.TYPE}" name="{inputs.NAME}" value="{inputs.VALUE}"></td>
		</tr>
	<!-- END inputs -->
		<tr>
			<td align="center" colspan="2"><input type="submit" value="{L_SUBMIT}"></td>
		</tr>
	</table>
 </FORM>
</BODY>
</HTML>
