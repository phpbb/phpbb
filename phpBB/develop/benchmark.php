<?
include('extension.inc');
include('config.'.$phpEx);
include('includes/constants.'.$phpEx);
include('functions/functions.'.$phpEx);
include('includes/db.'.$phpEx);

srand ((double) microtime() * 1000000);
set_time_limit(20*60);

// The script expects the ID's in the tables to sequential (1,2,3,4,5), 
// so no holes please (1,4,5,8)...
$nr_of_users  = nrof(USERS_TABLE);
$nr_of_cats   = nrof(CATEGORIES_TABLE);
$nr_of_forums = nrof(FORUMS_TABLE);
$nr_of_posts  = nrof(POSTS_TABLE);
$nr_of_topics = nrof(TOPICS_TABLE);  // create_topic() will keep this up to date

$u = $users;

$starttime = microtime();

while($users > 0){

  $name = "testuser_" . ($nr_of_users+10);
  createuser($name);
  $users--;
}
if ($forums > 0){
    create_forums($forums);
}
if ($posts > 0){
    filldb($posts);
}

$endtime = microtime();

if ($submit="" || !isset($submit)){
    ?>
Hello, welcome to this little phpBB Benchmarking script :)<p>

At the moment there are:<br>
<table>
<tr><td align="right"><?=$nr_of_users?></td><td>Users</td></tr>
<tr><td align="right"><?=$nr_of_topics?></td><td>Topics</td></tr>
<tr><td align="right"><?=$nr_of_forums?></td><td>Forums</td></tr>
<tr><td align="right"><?=$nr_of_posts?></td><td>Posts</td></tr>
</table>
<p>
What do you want to create?<p>

<form method="get" action="<?=$PHP_SELF?>">
<input type="text" name="users" size="3"> Users<br>
<input type="text" name="forums" size="3"> Forums/categories<br>
<input type="text" name="posts" size="3"> Posts/topics (optional: post size in <input type="text" name="size" size="3"> bytes)<br>
<input type="submit" name="submit">
</form>

    <?
} else {

  
	list ($starttime_msec,$starttime_sec) = explode(" ",$starttime);
	list ($endtime_msec,$endtime_sec) = explode(" ",$endtime);
	$timetaken_sec = ($endtime_sec+$endtime_msec) - ($starttime_sec+$starttime_msec);
	print "<B>TIME TAKEN : ".$timetaken_sec."s</B><BR>\n"; 

	$starttime = microtime();

	$result = $db->sql_query("SELECT * FROM users LIMIT 5,200");
	$rowresult = $db->sql_fetchrowset($result);

	$endtime = microtime();

	for($i=0;$i<count($rowresult);$i++){
		print $rowresult[$i]['user_id']." : ".$rowresult[$i]['username']."<BR>";
	}

//	while($row = $db->sql_fetchrow()){
//		print $row['user_id']."<BR>";
//	}

	list ($starttime_msec,$starttime_sec) = explode(" ",$starttime);
	list ($endtime_msec,$endtime_sec) = explode(" ",$endtime);
	$timetaken_sec = ($endtime_sec+$endtime_msec) - ($starttime_sec+$starttime_msec);
	print "<B>TIME TAKEN : ".$timetaken_sec."s</B><BR>\n"; 

	print "<p>\n<a href=\"$PHP_SELF\">Back to the overview page</a>\n";
}

function filldb($newposts){
  global $nr_of_topics;
  global $nr_of_forums;
  global $nr_of_users;
  for($i=0 ; $i<=$newposts; $i++){
    $userid   = rand(1,$nr_of_users);
    $forum    = rand(1,$nr_of_forums);
    if (rand(0,20) < 1 || $nr_of_topics == 0){
      // create a new topic 1 in 20 times (or when there are none);
      $topic    = create_topic($userid, "This is test topic nr. $i", $forum);
    } else {
      // Otherwise create a reply(posting) somewhere.
      $topic = rand(1,$nr_of_topics);
    }
    create_posting($userid, $topic, $forum);
  }
}


function create_topic($userid, $subject, $forum){
  global $db;
  global $nr_of_topics;
  $userdata = get_userdata($username, $db);
  $time = time();
  $sql = "INSERT INTO ".TOPICS_TABLE." (topic_title, topic_poster, forum_id, topic_time, topic_notify) VALUES ('$subject', '$userid', '$forum', '$time', 0)";
   if (!$result = $db->sql_query($sql)) {
     print "Couldn't create Topic $subject in DB!<br>\n";
     print "This is the sql statement:<br>\n$sql\n<br>\n";
     die;
   } else {
     print "<br>Topic '$subject' created!<br>\n";
     $nr_of_topics++;
     flush();
     return mysql_insert_id($db->db_connect_id);
   }
}

function create_posting($userid, $topic_id, $forum){
  global $db;
  $message = generatepost(650);
  $time = time();
  $poster_ip = "234234232";

  $sql = "INSERT INTO ".POSTS_TABLE." (forum_id, topic_id, poster_id, post_time, poster_ip) VALUES ('$forum', '$topic_id', '$userid', '$time', '$poster_ip')";
   if (!$result = $db->sql_query($sql)) {
     print "Couldn't create post in $forum!<br>\n";
     print "This is the sql statement:<br>\n$sql\n<br>\n";
     die;
   } else {

	$post_id = mysql_insert_id($db->db_connect_id);
	$sql = "INSERT INTO ".POSTS_TEXT_TABLE." (post_id, post_text) VALUES ('$post_id', '$message')";
	if (!$result = $db->sql_query($sql)) {
		print "Couldn't create post text in $forum!<br>\n";
		print "This is the sql statement:<br>\n$sql\n<br>\n";
		die;
	}
	 print "$forum ";
     return 0;
   }
}

function create_forums($totalforums){
  global $db;
  global $nr_of_cats;
  $j=0;
  for($i=0 ; $i<$totalforums; $i++){
    if (rand(0,5) <= 2 || $nr_of_cats == 0){
      // create a new cat at random times  or when there are no cats yet.
      $j++;
      $catid    = create_cat("Category $j");
    } else {
      // Otherwise create a forum in one of the cats.
      $catid = rand(0,$nr_of_cats);
    }
    $forum = "Test forum number $i";
    create_forum($catid, $forum);
  }
}

function create_cat($category){
  global $db;
  global $nr_of_cats;
  // At the moment cat_order is always one, oh well..
  echo $sql = "INSERT INTO ".CATEGORIES_TABLE." (cat_title, cat_order) VALUES ('$category', '1')";
  if (!$result = $db->sql_query($sql)) {
    print "Couldn't create category \"$category\"!<br>\n";
    die;
  } else {
    print "Category \"$category\" created!<br>\n";
    $nr_of_cats++;
    return mysql_insert_id($db->db_connect_id);
  }
}

function create_forum($catid, $forum){
  global $db;
  global $nr_of_forums;
 
  $sql = "INSERT INTO ".FORUMS_TABLE." (forum_name, forum_desc, forum_access, cat_id, forum_type) VALUES ('$forum', 'This is a forum created for the benchmark', '2', '$catid', '0')";
  if (!$result = $db->sql_query($sql)) {
    print "Couldn't create forum \"$forum\"!<br>\n";
    die;
  } else {
    print "Forum \"$forum\" created!<br>\n";
    $nr_of_forums++;
    $forum_id = mysql_insert_id($db->db_connect_id);
      // Sorry, no error checking. We just assume that if the forum can be
      // created the moderator can be assigned too :)
      $sql = "INSERT INTO ".FORUM_MODS_TABLE." (forum_id, user_id) VALUES ('$forum_id', '1')";
      $result = $db->sql_query($sql);
    return $forum_id;
  }
}


function generatepost($size=850){
   // Returns a string with a length between $size and $size*0.2
   $size = rand(0.2*$size, $size);
   $text = "Step 1: Untar the soure into the directory phpBB will run from. Seeing as you
are reading this file you have probably gotten that far. (Or if you are on a
different machine then your webserver FTP the resulting phpBB dir over to where
it will run from on the server. ie /www/yourdomain.com/phpBB)

Step 2: Edit config.php and change the following values:

The url fields my work for you if you're running phpBB from a domain 
like this: http://www.phpbb.com/phpBB

However if you access phpBB like this: http://www.domain.com/~you/phpBB you
will have to change these values.

Also, config.php MUST be writeable by the webserver or the install will fail.
To set this on UNIX systems you can use chmod
ie: chmod 777 config.php (after the install if finsished set it back to 755 
via chmod 755 config.php so it can't be written by anyone again.)";
   $textsize = strlen($text);
   $currentsize = 0;
   // Add whole $text multiple times
   while($currentsize < $size && $size-$currentsize <= $textsize){
      $message .= $text;
      $currentsize += $textsize;
   }
   // Add the remainder number of chars and return it.
   $message .= substr($text, 0, $size-$currentsize);

   // WARNING!! THIS IS NOT GOOD, THESE FUNCTIONS WILL ADD CHARACTERS!!!
   return (nl2br(addslashes($message)));
}
   
      
function nrof($table){
    global $db;
    $sql = "SELECT count(*) AS counted FROM $table";
    $result = $db->sql_query($sql);
    $topics = $db->sql_fetchrow($result);
    return $topics[counted];
}


function createuser($name){
   global $db;
   global $nr_of_users;
   $username    = $name;
   $regdate    = time();
   $email    = $username . "@phpbb.com";
   $icq        = "29317129";
   $passwd    = md5("test");
   $occ        = "";
   $intrest    = "";
   $from    = "";
   $website    = "http://www.phpBB.com/";
   $sig        = "To Bla, or Not to Bla";
   $aim     = "";
   $sqlviewemail = "1";
   $yim     = "";
   $msnm     = "";

   $userdata = get_userdata($username, $db);
   if($userdata["username"]) {
     print "Username '$username' has been already used...<br>\n";
     return 1;
   }
   
   $sql = "INSERT INTO ".USERS_TABLE." (username, user_regdate, user_email, user_icq, user_password, user_occ, user_intrest, user_from, user_website, user_sig, user_aim, user_viewemail, user_yim, user_msnm) VALUES ('$username', '$regdate', '$email', '$icq', '$passwd', '$occ', '$intrest', '$from', '$website', '$sig', '$aim', '$sqlviewemail', '$yim', '$msnm')";

  if (!$result = $db->sql_query($sql)) {
     print "Couldn't create user $username in DB!<br>\n";
     print "This is the sql statement:<br>\n$sql\n<br>\n";
     return 1;
   } else {
//     print "User $username created!<br>\n";
     $nr_of_users++;
 //    flush();
     return 0;
   }
}

function get_userdata($username, $db) {
	$sql = "SELECT * FROM ".USERS_TABLE." WHERE username = '$username' AND user_level != -1";
	if(!$result = $db->sql_query($sql))
		$userdata = array("error" => "1");
	if(!$myrow = $db->sql_fetchrow($result))
		$userdata = array("error" => "1");
	
	return($myrow);
}

  
?>