<html>
<body>
<?php

$phpbb_root_path = "./";
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);
   
$subsilver_themes_sql = "INSERT INTO phpbb_themes (themes_id, template_name, style_name, head_stylesheet, body_background, body_bgcolor, body_text, body_link, body_vlink, body_alink, body_hlink, tr_color1, tr_color2, tr_color3, tr_class1, tr_class2, tr_class3, th_color1, th_color2, th_color3, th_class1, th_class2, th_class3, td_color1, td_color2, td_color3, td_class1, td_class2, td_class3, fontface1, fontface2, fontface3, fontsize1, fontsize2, fontsize3, fontcolor1, fontcolor2, fontcolor3, span_class1, span_class2, span_class3, img_size_poll, img_size_privmsg) VALUES (1, 'subSilver', 'subSilver', '', '', 'E5E5E5', '000000', '006699', '5493B4', '', 'DD6900', 'EFEFEF', 'DEE3E7', 'D1D7DC', '', '', '', '98AAB1', '006699', 'FFFFFF', 'cellpic1.gif', 'cellpic3.gif', 'cellpic2.jpg', 'FAFAFA', 'FFFFFF', '', 'row1', 'row2', '', 'Verdana, Arial, Helvetica, sans-serif', 'Trebuchet MS', 'Courier, \'Courier New\', sans-serif', 10, 11, 12, '444444', '006600', 'FFA34F', '', '', '', '','')";
$subsilver_themes_names_sql = "INSERT INTO phpbb_themes_name (themes_id, tr_color1_name, tr_color2_name, tr_color3_name, tr_class1_name, tr_class2_name, tr_class3_name, th_color1_name, th_color2_name, th_color3_name, th_class1_name, th_class2_name, th_class3_name, td_color1_name, td_color2_name, td_color3_name, td_class1_name, td_class2_name, td_class3_name, fontface1_name, fontface2_name, fontface3_name, fontsize1_name, fontsize2_name, fontsize3_name, fontcolor1_name, fontcolor2_name, fontcolor3_name, span_class1_name, span_class2_name, span_class3_name) VALUES (1, 'The lightest row colour', 'The medium row color', 'The darkest row colour', '', '', '', 'Border round the whole page', 'Outer table border', 'Inner table border', 'Silver gradient picture', 'Blue gradient picture', 'Fade-out gradient on index', 'Background for quote boxes', 'All white areas', '', 'Background for topic posts', '2nd background for topic posts', '', 'Main fonts', 'Additional topic title font', 'Form fonts', 'Smallest font size', 'Medium font size', 'Normal font size (post body etc)', 'Quote & copyright text', 'Code text colour', 'Main table header text colour', '', '', '')";

$sql = array();

switch ( SQL_LAYER )
{
	case 'mysql':
	case 'mysql4':
		$sql[] = "ALTER TABLE " . TOPICS_TABLE . "  
			ADD COLUMN topic_first_post_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL, 
			ADD INDEX (topic_first_post_id)";
		break;

	case 'postgresql':
		$sql[] = "ALTER TABLE " . TOPICS_TABLE . " 
			ADD COLUMN topic_first_post_id int4";
		$sql[] = "CREATE INDEX topic_first_post_id_" . $table_prefix . "topics_index 
			ON " . TOPICS_TABLE . " (topic_first_post_id)";
		break;

	case 'mssql-odbc':
	case 'mssql':
		$sql[] = "ALTER TABLE " . TOPICS_TABLE . " ADD 
			topic_first_post_id int NOT NULL, 
			CONSTRAINT [DF_" . $table_prefix . "topics_topic_first_post_id] DEFAULT (0) FOR [topic_first_post_id]";
		$sql[] = "CREATE INDEX [IX_" . $table_prefix . "topics] 
			ON [" . TOPICS_TABLE . "]([topic_first_post_id]) ON [PRIMARY]";
		break;

	case 'msaccess':
		$sql[] = "ALTER TABLE " . TOPICS_TABLE . " ADD 
			topic_first_post_id int NOT NULL";
		$sql[] = "CREATE INDEX topic_first_post_id 
			ON " . TOPICS_TABLE . " (topic_first_post_id)";
		break;

	default:
		die("No DB LAYER found!");
		break;
}

	$errored = false;
	for($i = 0; $i < count($sql); $i++)
	{
		echo "Running >>> " . $sql[$i];

		$result = $db->sql_query($sql[$i]);

		if( !$result )
		{
			$errored = true;
			$error = $db->sql_error();
			echo " :: <b>FAILED</b> <u>( " . $error['message'] . " )</u><br /><br />\n\n";
		}
		else
		{
			echo " :: <b>COMPLETED</b><br /><br />\n\n";
		}
	}

	if( $errored )
	{
		echo "\n<br /><br />Errors occured! Please check and correct issues as required<br />\n";
	}
	else
	{

		$sql = "SELECT post_id, topic_id  
			FROM " . POSTS_TABLE . " 
			GROUP BY topic_id ASC
			ORDER BY post_id ASC";
		if( !($result = $db->sql_query($sql)) )
		{
			die("Couldn't obtain first post id list");
		}

		if( $row = $db->sql_fetchrow($result) )
		{
			do
			{
				$post_id = $row['post_id'];
				$topic_id = $row['topic_id'];

				$sql = "UPDATE " . TOPICS_TABLE . " 
					SET topic_first_post_id = $post_id 
					WHERE topic_id = $topic_id";
				if( !$db->sql_query($sql) )
				{
					die("Couldn't update topic first post id in topic :: $topic_id");
				}
			}
			while ( $row = $db->sql_fetchrow($result) );
		}
	
		echo "\n<br /><br />\nCOMPLETE! Please delete this file before continuing!<br />\n";
	}


?>
</body>
</html>