<html>
<body>
<?php

chdir("../");

include('extension.inc');
include('config.'.$phpEx);
include('includes/constants.'.$phpEx);
include('includes/db.'.$phpEx);

	$months = array(
		"Jan" => 1,
		"Feb" => 2,
		"Mar" => 3,
		"Apr" => 4,
		"May" => 5,
		"Jun" => 6,
		"Jul" => 7,
		"Aug" => 8,
		"Sep" => 9,
		"Oct" => 10,
		"Nov" => 11,
		"Dec" => 12 
	);

	$sql = "SELECT user_id, user_regdate FROM ".USERS_TABLE;
	$result = $db->sql_query($sql);
	if(!$result)
	{
		die("OOpppps, that didn't work!");
	}

	$all_old_dates = $db->sql_fetchrowset($result);

	$sql = "ALTER TABLE ".USERS_TABLE."
		CHANGE user_regdate user_regdate INT (11) NOT NULL";
	$result = $db->sql_query($sql);
	if(!$result)
	{
		die("Opps, that didn't work either ... oh damn!");
	}


	for($i = 0; $i < count($all_old_dates); $i++)
	{
		if(is_string($all_old_dates[$i]['user_regdate']))
		{
			if(eregi("^([a-zA-Z]{3}) ([0-9]+), ([0-9]{4})", $all_old_dates[$i]['user_regdate'], $result))
			{
				echo $all_old_dates[$i]['user_regdate']." : ";
				echo $new_time = gmmktime(0, 1, 0, $months[$result[1]], $result[2], $result[3]);
				echo " : ".gmdate("M d, Y", $new_time)."<br>";
				$sql = "UPDATE phpbb_users 
					SET user_regdate = '$new_time' 
					WHERE user_id = '".$all_old_dates[$i]['user_id']."'";
				$result = $db->sql_query($sql);
				if(!$result)
				{
					die("Oh damn it, now that's really broken it!");
				}
			}
		}
	}
		
	echo "<br>That's All Folks!";

?>
</body>
</html>