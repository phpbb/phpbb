<br clear="all" />
<b>{L_NOTICE}</b>
<br clear="all" />

<h1>{L_EMAIL_TITLE}</h1>

<p>{L_EMAIL_EXPLAIN}</p>

<table cellspacing="1" cellpadding="4" border="0" align="center">
        <tr>
                <td class="cat" align="center"><span class="cattitle"><b>{L_COMPOSE}</b></span></td>
        </tr>
        <tr>
		<form method="post" action="{S_USER_ACTION}">
                <td class="row1" align="center">{L_GROUP_SELECT}&nbsp;&nbsp;&nbsp;{S_GROUP_SELECT}</td>
	</tr>
	<tr>
		<td class="row2" align="center">{L_EMAIL_SUBJECT}&nbsp;&nbsp;&nbsp;<input type="text" name="{S_EMAIL_SUBJECT}">&nbsp;</td>
	</tr>
	<tr>
		<td class="row3" align="center">{L_EMAIL_MSG}</td>
	</tr>
	<tr>
		<td class="row4" align="center">
		<textarea name="{S_EMAIL_MSG}" ROWS = 15 COLS = 40></textarea>
	</tr>
	<tr>
		<td class="row5" align="center"><input type="submit" value="{L_EMAIL}" name="submit">&nbsp;</td>
        </tr>	
		</form>
	</tr>
</table>

<br clear="all" />
