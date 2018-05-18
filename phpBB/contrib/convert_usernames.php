<html>
<body>
<?php

//
// Security message:
//
// This script is potentially dangerous.
// Remove or comment the next line (die(".... ) to enable this script.
// Do NOT FORGET to either remove this script or disable it after you have used it.
//
die("Please read the first lines of this script for instructions on how to enable it");

//
// Do not change anything below this line.
//


//
// Convert 2.0.x Usernames to the new 2.0.5 Username format.
//

chdir("../");

define('IN_PHPBB', true);
include('extension.inc');
include('config.'.$phpEx);
include('includes/constants.'.$phpEx);
include('includes/db.'.$phpEx);

$sql = "SELECT user_id, username 
	FROM " . USERS_TABLE;
$result = $db->sql_query($sql);

if(!$result)
{
	die("Unable to get users");
}

while ($row = $db->sql_fetchrow($result))
{
	if (!preg_match('#(&gt;)|(&lt;)|(&quot)|(&amp;)#', $row['username']))
	{
		if ($row['username'] != htmlspecialchars($row['username']))
		{
			flush();
			$sql = "UPDATE " . USERS_TABLE . "
				SET username = '" . str_replace("'", "''", htmlspecialchars($row['username'])) . "'
				WHERE user_id = " . $row['user_id'];
			if (!$db->sql_query($sql))
			{
				echo "ERROR: Unable to rename user " . htmlspecialchars($row['username']) . " with ID " . $row['user_id'] . "<br>";
				echo "<pre>" . print_r($db->sql_error()) . "</pre><br />$sql";
			}
			else
			{
				echo "Renamed User " . htmlspecialchars($row['username']) . " with ID " . $row['user_id'] . "<br>";
			}
		}
	}
}

echo "<br>That's All Folks!";

?>
</body>
</html>
