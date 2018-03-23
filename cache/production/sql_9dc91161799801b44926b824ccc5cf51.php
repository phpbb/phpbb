<?php exit; ?>
1521830997
SELECT ban_ip, ban_userid, ban_email, ban_exclude, ban_give_reason, ban_end FROM phpbb_banlist WHERE ban_email = '' AND (ban_userid = 1 OR ban_ip <> '')
6
a:0:{}