<?php
// Just a handy script to completely wipe out the contents of a 
// database.. Use with caution :)


if(!isset($submit))
{
	?>
	<FORM ACTION="<?php echo $PHP_SELF?>" METHOD="post" >
	<table>
	<tr>
		<td>DB host:</td>
		<td><INPUT TYPE="text" name="dbhost" value="localhost"></td>
	</tr><tr>
		<td>DB name:</td>
		<td><INPUT TYPE="text" name="dbname" value="phpBB"></td>
	</tr><tr>
		<td>DB username:</td>
		<td><INPUT TYPE="text" name="dbuser" value="root"></td>
	</tr><tr>
		<td>DB password:</td>
		<td><INPUT TYPE="password" name="dbpass"></td>
	</tr></table>
	<INPUT TYPE="submit" name="submit" value="Submit">
	</FORM>
	<?php
}
else
{
	$dbuser = 'bartvb';
	$dbpass = 'bvbdb=';
	$dbname = 'welsh_forum';
	$dbhost = 'localhost';

	mysql_connect($dbhost, $dbuser, $dbpass);
	mysql_select_db($dbname);

	$result = mysql_query("SHOW TABLES");
	while($row = mysql_fetch_row($result)){
		$table = $row[0];
		print "Going to drop $table...";
		mysql_query("DROP TABLE $table") || die();
		print "Done.<br>\n";
		flush();
	}
}
?>

