<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<title>phpBB 2.2 Color Swatch</title>
<style type="text/css">

td {
	border: solid 1px #333333; 
}

.over { 
	border-color: white; 
}

.out {
	border-color: #333333; 
}

</style>
<body bgcolor="#404040" text="#FFFFFF">
<script language="javascript" type="text/javascript">
<!--
	var r = 0, g = 0, b = 0;
	var numberList = new Array(6);
	numberList[0] = "00";
	numberList[1] = "33";
	numberList[2] = "66";
	numberList[3] = "99";
	numberList[4] = "CC";
	numberList[5] = "FF";
	document.writeln('<table cellspacing="0" cellpadding="0" border="0">');
	for(r = 0; r < 6; r++)
	{
		document.writeln('<tr>');
		for(g = 0; g < 6; g++)
		{
			for(b = 0; b < 6; b++)
			{
				color = String(numberList[r]) + String(numberList[g]) + String(numberList[b]);
				document.write('<td bgcolor="#' + color + '" onmouseover="this.className=\'over\'" onmouseout="this.className=\'out\'">');
				document.write('<a href="javascript:cell(\'' + color + '\');"><img src="../images/spacer.gif" width="15" height="12" border="0" alt="#' + color + '" title="#' + color + '" /></a>');
				document.writeln('</td>');
			}
		}
		document.writeln('</tr>');
	}
	document.writeln('</table>');

function cell(color)
{
	opener.document.forms['<?php echo $_GET['form']; ?>'].<?php echo $_GET['name']; ?>.value=color;
}
//-->
</script>

</body>
</html>