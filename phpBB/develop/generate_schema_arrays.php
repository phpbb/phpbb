<?php

function remove_comments_schemas($sql)
{
	$lines = explode("\n", $sql);

	// try to keep mem. use down
	$sql = "";
	$linecount = count($lines);
	$output = "";
	$in_comment = false;
	for($i = 0; $i < $linecount; $i++)
	{
		$lines[$i] = trim($lines[$i]);

		if( ereg("^\/\*", $lines[$i]) )
		{
			$in_comment = true;
		}

		if( ereg("\*\/$", $lines[$i]) )
		{
			$i++;
			$in_comment = false;
		}

		if( !$in_comment && !ereg("^#", $lines[$i]) && !ereg("^\/$", $lines[$i]) && !ereg("^--", $lines[$i]) )
		{
			 $output .= $lines[$i] . "\n";
		}
		$lines[$i] = '';
	}
	return $output;
}

@set_time_limit(1200);

$phpbb_root_path = "../";

include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'includes/constants.'.$phpEx);
include($phpbb_root_path . 'includes/sql_parse.'.$phpEx);

$path_to_db = "../db";

$dir = opendir($path_to_db);

$sql_output_file = fopen("sql_arrays.$phpEx", "w");

fwrite($sql_output_file, "<?php\n\n");

while( $file = readdir($dir) )
{
	if( ereg("\.sql$", $file) )
	{
		$sql_file = fopen($path_to_db . "/" . $file, "r");

		$array_name = substr($file, 0, strpos($file, ".sql"));

		$file_contents = trim(fread($sql_file, 100000000));

		$file_contents = remove_comments_schemas($file_contents);
		if($array_name != "mssql_schema")
		{
			$sql_pieces = split_sql_file($file_contents, ";");
		}
		else
		{
			$lines = explode("\n", $file_contents);
			$finish_grab = false;
			$line = "";
			for($i = 0; $i < count($lines); $i++)
			{
				$lines[$i] = trim($lines[$i]);

				if( ereg("^GO$", $lines[$i]) )
				{
					$finish_grab = true;
				}
				else
				{
					$line .= $lines[$i] . " ";
				}

				if( $finish_grab )
				{
					$sql_pieces[] = $line . "\n";
					$sql_pieces[] = "GO" . "\n";

					$line = "";
					$finish_grab = false;
				}
				$lines[$i] = '';
			}
		}

		$sql_count = count($sql_pieces);

		fwrite($sql_output_file, "//\n// $file\n//\n");

		for($i = 0; $i < $sql_count; $i++)
		{
			if( ereg("schema|triggers", $file) )
			{
				$sql_pieces[$i] = str_replace("\n", " ", $sql_pieces[$i]);
			}

			$sql_pieces[$i] = preg_replace("/phpbb_(.*?) /is", "{PREFIX}_\\1 ", $sql_pieces[$i]);

			fwrite($sql_output_file, "\$sql_array['$array_name'][$i] = \"" . trim(str_replace("\"", "\\\"", $sql_pieces[$i])) . "\";\n");
		}
		fwrite($sql_output_file, "\n");

		unset($file_contents);
		unset($sql_pieces);

	}
}

fwrite($sql_output_file, "?>");

closedir($dir);
fclose($sql_output_file);

?>
