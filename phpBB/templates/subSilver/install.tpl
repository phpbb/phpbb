<HTML>
<HEAD>
<TITLE>phpBB Version 2.0 Installation</TITLE>
</HEAD>
<body bgcolor="#E5E5E5" text="#03699C" link="#993300" vlink="#FF9900">
<FORM action='{S_FORM_ACTION}' method="post" >
  <!-- BEGIN hidden_fields -->
  <input type="hidden" name='{hidden_fields.NAME}' value='{hidden_fields.VALUE}'>
  <!-- END hidden_fields -->
  <table>
	<!-- BEGIN selects -->
	<tr> 
	  <td colspan="2"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">{L_INSTRUCT}</font></td>
	</tr>
	<tr> 
	  <td align="right"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">{selects.L_LABEL}</font></td>
	  <td> 
		<select name="{selects.NAME}">
		  <!-- BEGIN options -->
		  <option value="{selects.options.VALUE}" {selects.options.DEFAULT}>{selects.options.LABEL}</option>
		  <!-- END options -->
		</select>
	  </td>
	</tr>
	<!-- END selects -->
	<!-- BEGIN inputs -->
	<tr> 
	  <td align="right"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">{inputs.L_LABEL}</font></td>
	  <td> 
		<input type="{inputs.TYPE}" name="{inputs.NAME}" value="{inputs.VALUE}">
	  </td>
	</tr>
	<!-- END inputs -->
	<tr> 
	  <td align="center" colspan="2"> 
		<input type="submit" value="{L_SUBMIT}">
	  </td>
	</tr>
  </table>
</FORM>
</BODY>
</HTML>
