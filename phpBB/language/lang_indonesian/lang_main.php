<?php
/***************************************************************************
 *                            lang_main.php [Indonesian]
 *                              -------------------
 *     begin                : Sat Dec 16 2000
 *     copyright            : (C) 2001 The phpBB Group
 *     email                : support@phpbb.com
 *
 *     $Id$
 *
 ****************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

// Indonesian Translation: Ivan R. Lanin <ivan@konsep.net>
// Last Update: 2002-11-27 01:26

//
// The format of this file is ---> $lang['message'] = 'text';
//
// You should also try to set a locale and a character encoding (plus direction). The encoding and direction
// will be sent to the template. The locale may or may not work, it's dependent on OS support and the syntax
// varies ... give it your best guess!
//

$lang['ENCODING'] = 'iso-8859-1';
$lang['DIRECTION'] = 'ltr';
$lang['LEFT'] = 'left';
$lang['RIGHT'] = 'right';
$lang['DATE_FORMAT'] =  'd M Y'; // This should be changed to the default date format for your language, php date() format

// This is optional, if you would like a _SHORT_ message output
// along with our copyright message indicating you are the translator
// please add it here.
// $lang['TRANSLATION'] = '';

//
// Common, these terms are used
// extensively on several pages
//
$lang['Forum'] = 'Forum';
$lang['Category'] = 'Kategori';
$lang['Topic'] = 'Topik';
$lang['Topics'] = 'Topik';
$lang['Replies'] = 'Balasan';
$lang['Views'] = 'Dilihat';
$lang['Post'] = 'Post';
$lang['Posts'] = 'Post';
$lang['Posted'] = 'Dikirim';
$lang['Username'] = 'Username';
$lang['Password'] = 'Password';
$lang['Email'] = 'Email';
$lang['Poster'] = 'Pengirim';
$lang['Author'] = 'Pengirim';
$lang['Time'] = 'Waktu';
$lang['Hours'] = 'Jam';
$lang['Message'] = 'Message';

$lang['1_Day'] = '1 Hari';
$lang['7_Days'] = '7 Hari';
$lang['2_Weeks'] = '2 Minggu';
$lang['1_Month'] = '1 Bulan';
$lang['3_Months'] = '3 Bulan';
$lang['6_Months'] = '6 Bulan';
$lang['1_Year'] = '1 Tahun';

$lang['Go'] = 'Cari';
$lang['Jump_to'] = 'Navigasi';
$lang['Submit'] = 'Kirim';
$lang['Reset'] = 'Reset';
$lang['Cancel'] = 'Batal';
$lang['Preview'] = 'Preview';
$lang['Confirm'] = 'Konfirmasi';
$lang['Spellcheck'] = 'Cek ejaan';
$lang['Yes'] = 'Ya';
$lang['No'] = 'Tidak';
$lang['Enabled'] = 'Bisa';
$lang['Disabled'] = 'Tidak Bisa';
$lang['Error'] = 'Error';

$lang['Next'] = 'Selanjutnya';
$lang['Previous'] = 'Sebelumnya';
$lang['Goto_page'] = 'Pilih halaman';
$lang['Joined'] = 'Sejak';
$lang['IP_Address'] = 'Alamat IP';

$lang['Select_forum'] = 'Pilih forum';
$lang['View_latest_post'] = 'Lihat posting terakhir';
$lang['View_newest_post'] = 'Lihat posting terbaru';
$lang['Page_of'] = 'Halaman <b>%d</b> dari <b>%d</b>'; // Replaces with: Page 1 of 2 for example

$lang['ICQ'] = 'ICQ Number';
$lang['AIM'] = 'AIM Address';
$lang['MSNM'] = 'MSN Messenger';
$lang['YIM'] = 'Yahoo ID';

$lang['Forum_Index'] = 'Indeks Forum %s';  // eg. sitename Forum Index, %s can be removed if you prefer

$lang['Post_new_topic'] = 'Kirim topik baru';
$lang['Reply_to_topic'] = 'Kirim balasan';
$lang['Reply_with_quote'] = 'Balas dengan kutipan';

$lang['Click_return_topic'] = '%sKembali ke topik%s'; // %s's here are for uris, do not remove!
$lang['Click_return_login'] = '%sSilakan ulangi%s';
$lang['Click_return_forum'] = '%sKembali ke forum%s';
$lang['Click_view_message'] = '%sLihat pesan Anda%s';
$lang['Click_return_modcp'] = '%sKembali ke panel kontrol moderator%s';
$lang['Click_return_group'] = '%sKembali ke informasi grup%s';

$lang['Admin_panel'] = 'Panel Admin';

$lang['Board_disable'] = 'Maaf, forum ini tidak dapat diakses. Silakan kembali lagi.';


//
// Global Header strings
//
$lang['Registered_users'] = 'User terdaftar:';
$lang['Browsing_forum'] = 'User yang sedang berada di forum ini:';
$lang['Online_users_zero_total'] = 'Total <b>0</b> user online :: ';
$lang['Online_users_total'] = 'Total <b>%d</b> uses online :: ';
$lang['Online_user_total'] = 'Total <b>%d</b> user online :: ';
$lang['Reg_users_zero_total'] = '0 Terdaftar, ';
$lang['Reg_users_total'] = '%d Terdaftar, ';
$lang['Reg_user_total'] = '%d Terdaftar, ';
$lang['Hidden_users_zero_total'] = '0 Tersembunyi dan ';
$lang['Hidden_user_total'] = '%d Tersembunyi dan ';
$lang['Hidden_users_total'] = '%d Tersembunyi dan ';
$lang['Guest_users_zero_total'] = '0 Tamu';
$lang['Guest_users_total'] = '%d Tamu';
$lang['Guest_user_total'] = '%d Tamu';
$lang['Record_online_users'] = 'User online terbanyak adalah <b>%s</b> pada %s'; // first %s = number of users, second %s is the date.

$lang['Admin_online_color'] = '%sAdministrator%s';
$lang['Mod_online_color'] = '%sModerator%s';

$lang['You_last_visit'] = 'Kunjungan terakhir %s'; // %s replaced by date/time
$lang['Current_time'] = 'Waktu sekarang %s'; // %s replaced by time

$lang['Search_new'] = 'Daftar Pesan Terbaru';
$lang['Search_your_posts'] = 'Daftar Pesan Kiriman Anda';
$lang['Search_unanswered'] = 'Daftar Pesan Belum Dibalas';

$lang['Register'] = 'Pendaftaran';
$lang['Profile'] = 'Profil';
$lang['Edit_profile'] = 'Ubah profil';
$lang['Search'] = 'Pencarian';
$lang['Memberlist'] = 'Anggota';
$lang['FAQ'] = 'FAQ';
$lang['BBCode_guide'] = 'BBCode Guide';
$lang['Usergroups'] = 'Group';
$lang['Last_Post'] = 'Pesan Terakhir';
$lang['Moderator'] = 'Moderator';
$lang['Moderators'] = 'Moderators';


//
// Stats block text
//
$lang['Posted_articles_zero_total'] = 'Total <b>0</b> kiriman artikel dari user'; // Number of posts
$lang['Posted_articles_total'] = 'Total <b>%d</b> kiriman artikel dari user'; // Number of posts
$lang['Posted_article_total'] = 'Total <b>%d</b> kiriman artikel dari user'; // Number of posts
$lang['Registered_users_zero_total'] = 'Total <b>0</b> user terdaftar'; // # registered users
$lang['Registered_users_total'] = 'Total <b>%d</b> user terdaftar'; // # registered users
$lang['Registered_user_total'] = 'Total <b>%d</b> user terdaftar'; // # registered users
$lang['Newest_user'] = 'User terdaftar terakhir adalah <b>%s%s%s</b>'; // a href, username, /a 

$lang['No_new_posts_last_visit'] = 'Tak ada pesan baru sejak login terakhir.';
$lang['No_new_posts'] = 'Tak ada pesan baru';
$lang['New_posts'] = 'Pesan baru';
$lang['New_post'] = 'Pesan baru';
$lang['No_new_posts_hot'] = 'Tak ada pesan baru [ Popular ]';
$lang['New_posts_hot'] = 'Pesan baru[ Popular ]';
$lang['No_new_posts_locked'] = 'Tak ada pesan baru [ Dikunci ]';
$lang['New_posts_locked'] = 'Pesan baru [ Dikunci ]';
$lang['Forum_is_locked'] = 'Forum dikunci';


//
// Login
//
$lang['Enter_password'] = 'Masukkan username dan password Anda untuk login.';
$lang['Login'] = 'Login';
$lang['Logout'] = 'Logout';

$lang['Forgotten_password'] = 'Lupa password?';

$lang['Log_me_in'] = 'Login otomatis';

$lang['Error_login'] = 'Username atau password salah atau tidak aktif';


//
// Index page
//
$lang['Index'] = 'Indeks';
$lang['No_Posts'] = 'Tak ada pesan';
$lang['No_forums'] = 'Tak ada forum';

$lang['Private_Message'] = 'Pesan Pribadi';
$lang['Private_Messages'] = 'Pesan Pribadi';
$lang['Who_is_Online'] = 'User Yang Sedang Online';

$lang['Mark_all_forums'] = 'Tandai Telah Dibaca untuk Semua Forum';
$lang['Forums_marked_read'] = 'Semua forum telah ditandai';


//
// Viewforum
//
$lang['View_forum'] = 'Lihat Forum';

$lang['Forum_not_exist'] = 'Forum yang Anda pilih tidak tersedia';
$lang['Reached_on_error'] = 'Halaman ini ditampilkan karena terjadi error';

$lang['Display_topics'] = 'Tampilkan topik sebelumnya';
$lang['All_Topics'] = 'Semua Topik';

$lang['Topic_Announcement'] = '<b>Pengumuman:</b>';
$lang['Topic_Sticky'] = '<b>Pesan Tetap:</b>';
$lang['Topic_Moved'] = '<b>Pindah:</b>';
$lang['Topic_Poll'] = '<b>[ Poll ]</b>';

$lang['Mark_all_topics'] = 'Tandai Telah Dibaca untuk Semua Topik';
$lang['Topics_marked_read'] = 'Topik untuk forum ini telah ditandai sebagai telah dibaca';

$lang['Rules_post_can'] = 'Anda <b>dapat</b> mengirim topik pada forum ini';
$lang['Rules_post_cannot'] = 'Anda <b>tidak dapat</b> mengirim topik pada forum ini';
$lang['Rules_reply_can'] = 'Anda <b>dapat</b> menjawab topik pada forum ini';
$lang['Rules_reply_cannot'] = 'Anda <b>tidak dapat</b> menjawab topik pada forum ini';
$lang['Rules_edit_can'] = 'Anda <b>dapat</b> mengubah pesan Anda pada forum ini';
$lang['Rules_edit_cannot'] = 'Anda <b>tidak dapat</b> mengubah pesan Anda pada forum ini';
$lang['Rules_delete_can'] = 'Anda <b>dapat</b> menghapus pesan Anda pada forum ini';
$lang['Rules_delete_cannot'] = 'Anda <b>tidak dapat</b> menghapus pesan Anda pada forum ini';
$lang['Rules_vote_can'] = 'Anda <b>dapat</b> mengikuti polling pada forum ini';
$lang['Rules_vote_cannot'] = 'Anda <b>tidak dapat</b> mengikuti polling pada forum ini';
$lang['Rules_moderate'] = 'Anda <b>dapat</b> %smemoderasi forum ini%s'; // %s replaced by a href links, do not remove! 

$lang['No_topics_post_one'] = 'Tidak ada pesan pada forum ini<br />Tekan link <b>New Topic</b> untuk mengirimkan topik baru';


//
// Viewtopic
//
$lang['View_topic'] = 'Lihat topik';

$lang['Guest'] = 'Tamu';
$lang['Post_subject'] = 'Judul';
$lang['View_next_topic'] = 'Topik selanjutnya';
$lang['View_previous_topic'] = 'Topik sebelumnya';
$lang['Submit_vote'] = 'Kirim Pilihan';
$lang['View_results'] = 'Lihat Hasil';

$lang['No_newer_topics'] = 'Tak ada topik yang lebih baru pada forum ini';
$lang['No_older_topics'] = 'Tak ada topik yang lebih lama pada forum ini';
$lang['Topic_post_not_exist'] = 'Topik atau pesan yang Anda cari tidak ditemukan';
$lang['No_posts_topic'] = 'Tak ada pesan pada topik ini';

$lang['Display_posts'] = 'Tampilan pesan sebelumnya';
$lang['All_Posts'] = 'Semua Pesan';
$lang['Newest_First'] = 'Terbaru Dulu';
$lang['Oldest_First'] = 'Terlama Dulu';

$lang['Back_to_top'] = 'Kembali Ke Atas';

$lang['Read_profile'] = 'Lihat profil user'; 
$lang['Send_email'] = 'Kirim email untuk user';
$lang['Visit_website'] = 'Kunjungi situs pengirim';
$lang['ICQ_status'] = 'Status ICQ';
$lang['Edit_delete_post'] = 'Ubah/Hapus pesan';
$lang['View_IP'] = 'Lihat IP pengirim';
$lang['Delete_post'] = 'Hapus pesan';

$lang['wrote'] = 'wrote'; // proceeds the username and is followed by the quoted text
$lang['Quote'] = 'Quote'; // comes before bbcode quote output.
$lang['Code'] = 'Code'; // comes before bbcode code output.

$lang['Edited_time_total'] = 'Terakhir diubah oleh %s tanggal %s, total %d kali diubah'; // Last edited by me on 12 Oct 2001, edited 1 time in total
$lang['Edited_times_total'] = 'Terakhir diubah oleh %s tanggal %s, total %d kali diubah'; // Last edited by me on 12 Oct 2001, edited 2 times in total

$lang['Lock_topic'] = 'Kunci topik';
$lang['Unlock_topic'] = 'Buka topik';
$lang['Move_topic'] = 'Pindahkan topik';
$lang['Delete_topic'] = 'Hapus topik';
$lang['Split_topic'] = 'Pecah topik';

$lang['Stop_watching_topic'] = 'Hentikan pengawasan topik';
$lang['Start_watching_topic'] = 'Awasi topik untuk balasan';
$lang['No_longer_watching'] = 'Anda tidak lagi mengawasi topik ini';
$lang['You_are_watching'] = 'Anda mulai pengawasan topik';

$lang['Total_votes'] = 'Total Suara';

//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = 'Isi pesan';
$lang['Topic_review'] = 'Review topik';

$lang['No_post_mode'] = 'Post mode tidak diset'; // If posting.php is called without a mode (newtopic/reply/delete/etc, shouldn't be shown normaly)

$lang['Post_a_new_topic'] = 'Kirim topik baru';
$lang['Post_a_reply'] = 'Kirim balasan';
$lang['Post_topic_as'] = 'Kirim topik sebagai';
$lang['Edit_Post'] = 'Ubah topik';
$lang['Options'] = 'Pilihan';

$lang['Post_Announcement'] = 'Pengumuman';
$lang['Post_Sticky'] = 'Topik Tetap';
$lang['Post_Normal'] = 'Normal';

$lang['Confirm_delete'] = 'Hapus pesan ini?';
$lang['Confirm_delete_poll'] = 'Hapus polling ini?';

$lang['Flood_Error'] = 'Anda tak dapat langsung mengirim pesan lain dalam waktu yg berdekatan. Harap tunggu sejenak sebelum mengirim pesan lain';
$lang['Empty_subject'] = 'Subyek harus diisi untuk pesan baru';
$lang['Empty_message'] = 'Isi pesan harus diisi';
$lang['Forum_locked'] = 'Forum ini dikunci. Anda tak dapat mengirim, membalas, atau mengubah topik';
$lang['Topic_locked'] = 'Topik ini dikunci. Anda tak mengubah atau membalas pesan';
$lang['No_post_id'] = 'Anda harus memilih pesan yang ingin diubah';
$lang['No_topic_id'] = 'Anda harus memilih pesan yang akan dibalas';
$lang['No_valid_mode'] = 'Anda hanya dapat mengirim, membalas, atau mengutip pesan. Silakan kembali dan ulangi lagi';
$lang['No_such_post'] = 'Tak ada pesan yang Anda cari. Silakan kembali dan ulangi lagi';
$lang['Edit_own_posts'] = 'Maaf, tapi Anda hanya dapat mengedit pesan Anda sendiri';
$lang['Delete_own_posts'] = 'Maaf, tapi Anda hanya dapat menghapus pesan Anda sendiri';
$lang['Cannot_delete_replied'] = 'Maaf, tapi Anda tidak dapat menghapus pesan yang sudah dibalas';
$lang['Cannot_delete_poll'] = 'Maaf tapi Anda tidak dapat menghapus polling aktif';
$lang['Empty_poll_title'] = 'Judul polling harus dimasukkan';
$lang['To_few_poll_options'] = 'Paling tidak dua pilihan harus dimasukkan untuk polling';
$lang['To_many_poll_options'] = 'Pilihan polling yang Anda masukkan terlalu banyak';
$lang['Post_has_no_poll'] = 'Pesan ini tidak memiliki polling';
$lang['Already_voted'] = 'Anda sudah pernah mengirimkan pilihan untuk polling ini';
$lang['No_vote_option'] = 'Anda harus memasukkan pilihan';

$lang['Add_poll'] = 'Buat polling baru';
$lang['Add_poll_explain'] = 'Jika Anda tidak ingin menambahkan polling pada topik Anda, kosongkan isian yang tersedia';
$lang['Poll_question'] = 'Pertanyaan polling';
$lang['Poll_option'] = 'Pilihan polling';
$lang['Add_option'] = 'Tambah Pilihan';
$lang['Update'] = 'Ubah Data';
$lang['Delete'] = 'Hapus';
$lang['Poll_for'] = 'Polling untuk';
$lang['Days'] = 'hari'; // This is used for the Run poll for ... Days + in admin_forums for pruning
$lang['Poll_for_explain'] = '[ Masukkan 0 atau kosongkan untuk polling tanpa batas waktu ]';
$lang['Delete_poll'] = 'Hapus polling';

$lang['Disable_HTML_post'] = 'HTML non aktif untuk pesan ini';
$lang['Disable_BBCode_post'] = 'BBCode non aktif untuk pesan ini';
$lang['Disable_Smilies_post'] = 'Smilies non aktif untuk pesan ini';

$lang['HTML_is_ON'] = 'Setting HTML <u>ON</u>';
$lang['HTML_is_OFF'] = 'Setting HTML <u>OFF</u>';
$lang['BBCode_is_ON'] = 'Setting %sBBCode%s <u>ON</u>'; // %s are replaced with URI pointing to FAQ
$lang['BBCode_is_OFF'] = 'Setting %sBBCode%s <u>OFF</u>';
$lang['Smilies_are_ON'] = 'Setting Smilies <u>ON</u>';
$lang['Smilies_are_OFF'] = 'Setting Smilies <u>OFF</u>';

$lang['Attach_signature'] = 'Lampirkan signatur (Signatur bisa diubah di Profil)';
$lang['Notify'] = 'Notifikasi balasan pesan';
$lang['Delete_post'] = 'Hapus post ini';

$lang['Stored'] = 'Pesan terkirim dengan sukses';
$lang['Deleted'] = 'Penghapusan pesan berhasil';
$lang['Poll_delete'] = 'Penghapusan polling berhasil';
$lang['Vote_cast'] = 'Pilihan Anda berhasil terkirim';

$lang['Topic_reply_notification'] = 'Notifikasi Balasan Topik';

$lang['bbcode_b_help'] = 'Tebal: [b]teks[/b]  (alt+b)';
$lang['bbcode_i_help'] = 'Miring: [i]teks[/i]  (alt+i)';
$lang['bbcode_u_help'] = 'Garis bawah: [u]teks[/u]  (alt+u)';
$lang['bbcode_q_help'] = 'Kutipan: [quote]teks[/quote]  (alt+q)';
$lang['bbcode_c_help'] = 'Kode program: [code]code[/code]  (alt+c)';
$lang['bbcode_l_help'] = 'Daftar: [list]teks[/list] (alt+l)';
$lang['bbcode_o_help'] = 'Daftar bernomor: [list=]teks[/list]  (alt+o)';
$lang['bbcode_p_help'] = 'Sisipan gambar: [img]http://image_url[/img]  (alt+p)';
$lang['bbcode_w_help'] = 'Sisipan URL: [url]http://url[/url] atau [url=http://url]teks URL[/url]  (alt+w)';
$lang['bbcode_a_help'] = 'Tutup semua tag bbCode';
$lang['bbcode_s_help'] = 'Warna huruf: [color=red]teks[/color]  Anda dapat juga menggunakan color=#FF0000';
$lang['bbcode_f_help'] = 'Besar huruf: [size=x-small]teks kecil[/size]';

$lang['Emoticons'] = 'Emoticons';
$lang['More_emoticons'] = 'Emoticons lain';

$lang['Font_color'] = 'Warna huruf';
$lang['color_default'] = 'Default';
$lang['color_dark_red'] = 'Merah Tua';
$lang['color_red'] = 'Merah';
$lang['color_orange'] = 'Oranye';
$lang['color_brown'] = 'Coklat';
$lang['color_yellow'] = 'Kuning';
$lang['color_green'] = 'Hijau';
$lang['color_olive'] = 'Zaitun';
$lang['color_cyan'] = 'Sian';
$lang['color_blue'] = 'Biru';
$lang['color_dark_blue'] = 'Biru Tua';
$lang['color_indigo'] = 'Indigo';
$lang['color_violet'] = 'Ungu';
$lang['color_white'] = 'Putih';
$lang['color_black'] = 'Hitam';

$lang['Font_size'] = 'Ukuran huruf';
$lang['font_tiny'] = 'Kecil sekali';
$lang['font_small'] = 'Kecil';
$lang['font_normal'] = 'Normal';
$lang['font_large'] = 'Besar';
$lang['font_huge'] = 'Besar sekali';

$lang['Close_Tags'] = 'Tutup tag';
$lang['Styles_tip'] = 'Tip: Style dapat langsung diterapkan pada teks yang dipilih';


//
// Private Messaging
//
$lang['Private_Messaging'] = 'Pesan Pribadi';

$lang['Login_check_pm'] = 'Login ke Pesan Pribadi Anda';
$lang['New_pms'] = 'Pesan Baru: %d'; // You have 2 new messages
$lang['New_pm'] = 'Pesan Baru: %d'; // You have 1 new message
$lang['No_new_pm'] = 'Tak Ada Pesan Baru';
$lang['Unread_pms'] = 'Pesan Belum Dibaca: %d';
$lang['Unread_pm'] = 'Pesan Belum Dibaca: %d';
$lang['No_unread_pm'] = 'Tak ada pesan yang belum dibaca';
$lang['You_new_pm'] = 'Ada pesan pribadi baru di Inbox Anda';
$lang['You_new_pms'] = 'Ada pesan pribadi baru di Inbox Anda';
$lang['You_no_new_pm'] = 'Tak ada pesan pribadi baru untuk Anda';

$lang['Unread_message'] = 'Pesan belum dibaca';
$lang['Read_message'] = 'Baca pesan';

$lang['Read_pm'] = 'Baca pesan';
$lang['Post_new_pm'] = 'Kirim pesan';
$lang['Post_reply_pm'] = 'Balas Pesan';
$lang['Post_quote_pm'] = 'Kutip Pesan';
$lang['Edit_pm'] = 'Ubah Pesan';

$lang['Inbox'] = 'Inbox';
$lang['Outbox'] = 'Outbox';
$lang['Savebox'] = 'Savebox';
$lang['Sentbox'] = 'Sentbox';
$lang['Flag'] = 'Flag';
$lang['Subject'] = 'Subyek';
$lang['From'] = 'Dari';
$lang['To'] = 'Untuk';
$lang['Date'] = 'Tanggal';
$lang['Mark'] = 'Tandai';
$lang['Sent'] = 'Kirim';
$lang['Saved'] = 'Simpan';
$lang['Delete_marked'] = 'Hapus Marked';
$lang['Delete_all'] = 'Hapus Semua';
$lang['Save_marked'] = 'Simpan Tanda'; 
$lang['Save_message'] = 'Simpan Pesan';
$lang['Delete_message'] = 'Hapus Pesan';

$lang['Display_messages'] = 'Umur pesan'; // Followed by number of days/weeks/months
$lang['All_Messages'] = 'Semua pesan';

$lang['No_messages_folder'] = 'Tidak ada message pada folder ini';

$lang['PM_disabled'] = 'Pesan Pribadi tidak diijinkan pada forum ini';
$lang['Cannot_send_privmsg'] = 'Maaf, administrator melarang Anda mengirim Pesan Pribadi';
$lang['No_to_user'] = 'Anda harus memilih user yang akan dikirim pesan';
$lang['No_such_user'] = 'Maaf, user tersebut tidak tersedia';

$lang['Disable_HTML_pm'] = 'HTML tidak diijikan';
$lang['Disable_BBCode_pm'] = 'BBCode tidak diijikan';
$lang['Disable_Smilies_pm'] = 'Smilies tidak diijikan';

$lang['Message_sent'] = 'Pesan Anda berhasil dikirimkan';

$lang['Click_return_inbox'] = 'Kembali %ske Inbox Anda%s';
$lang['Click_return_index'] = 'Kembali %ske Indeks Forum%s';

$lang['Send_a_new_message'] = 'Kirim pesan pribadi baru';
$lang['Send_a_reply'] = 'Jawab pesan';
$lang['Edit_message'] = 'Ubah pesan';

$lang['Notification_subject'] = 'Pesan Pribadi baru telah diterima';

$lang['Find_username'] = 'Cari User';
$lang['Find'] = 'Cari';
$lang['No_match'] = 'Tidak ditemukan data yang sesuai';

$lang['No_post_id'] = 'Post ID tidak didefinisikan';
$lang['No_such_folder'] = 'Tidak ditemukan folder yang sesuai';
$lang['No_folder'] = 'Folder tidak terdefinisi';

$lang['Mark_all'] = 'Tandai Semua';
$lang['Unmark_all'] = 'Batal Tandai Semua';

$lang['Confirm_delete_pm'] = 'Anda yakin untuk menghapus semua pesan ini?';
$lang['Confirm_delete_pms'] = 'Anda yakin untuk menghapus semua pesan ini?';

$lang['Inbox_size'] = 'Inbox Anda %d%% terisi'; // eg. Your Inbox is 50% full
$lang['Sentbox_size'] = 'Sentbox Anda %d%% terisi'; 
$lang['Savebox_size'] = 'Savebox Anda %d%% terisi'; 

$lang['Click_view_privmsg'] = '%sBuka Inbox Anda%s';


//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = 'Profil :: %s'; // %s is username 
$lang['About_user'] = 'Mengenai %s'; // %s is username

$lang['Preferences'] = 'Preferensi';
$lang['Items_required'] = 'Item yang bertanda * harus diisi, kecuali jika ada catatan lain';
$lang['Registration_info'] = 'Informasi Pendaftaran';
$lang['Profile_info'] = 'Informasi Profil';
$lang['Profile_info_warn'] = 'Informasi ini akan dapat dibaca pengunjung lain';
$lang['Avatar_panel'] = 'Panel Avatar';
$lang['Avatar_gallery'] = 'Galeri Avatar';

$lang['Website'] = 'Website';
$lang['Location'] = 'Lokasi';
$lang['Contact'] = 'Kontak';
$lang['Email_address'] = 'Alamat Email';
$lang['Email'] = 'Email';
$lang['Send_private_message'] = 'Kirim pesan pribadi';
$lang['Hidden_email'] = '[ Tersembunyi ]';
$lang['Search_user_posts'] = 'Cari pesan user ini';
$lang['Interests'] = 'Minat/Hobi';
$lang['Occupation'] = 'Pekerjaan'; 
$lang['Poster_rank'] = 'Pangkat Pengirim';

$lang['Total_posts'] = 'Total pesan';
$lang['User_post_pct_stats'] = '%.2f%% dari total'; // 1.25% of total
$lang['User_post_day_stats'] = '%.2f%% dari total perhari'; // 1.5 posts per day
$lang['Search_user_posts'] = 'Daftar posting dari %s'; // Find all posts by username

$lang['No_user_id_specified'] = 'Maaf, user tersebut tidak ditemukan';
$lang['Wrong_Profile'] = 'Anda tak dapat mengubah profil orang lain.';

$lang['Only_one_avatar'] = 'Hanya satu jenis Avatar dapat ditentukan';
$lang['File_no_data'] = 'File pada URL yang Anda berikan tidak valid';
$lang['No_connection_URL'] = 'Tidak dapat menghubungi URL yang Anda masukkan';
$lang['Incomplete_URL'] = 'URL yang Anda masukkan tidak lengkap';
$lang['Wrong_remote_avatar_format'] = 'Format Avatar tidak valid';
$lang['No_send_account_inactive'] = 'Maaf, password Anda tidak dapat diambil karena Anda sedang tak aktif. Silakan kontak Administrator untuk informasi lanjut';

$lang['Always_smile'] = 'Selalu ijinkan Smilies';
$lang['Always_html'] = 'Selalu ijinkan HTML';
$lang['Always_bbcode'] = 'Selalu ijinkan BBCode';
$lang['Always_add_sig'] = 'Selalu lampirkan signature';
$lang['Always_notify'] = 'Selalu kirim notifikasi balasan';
$lang['Always_notify_explain'] = 'Kirim email jika topik Anda dibalas orang lain. Pilihan ini dapat diganti setiap Anda mengirim pesan';

$lang['Board_style'] = 'Style';
$lang['Board_lang'] = 'Bahasa';
$lang['No_themes'] = 'Theme tak tersedia';
$lang['Timezone'] = 'Zona Waktu';
$lang['Date_format'] = 'Format tanggal';
$lang['Date_format_explain'] = 'Sintaks yang digunakan identik dengan fungsi <a href=\'http://www.php.net/date\' target=\'_other\'>date()</a> pada PHP';
$lang['Signature'] = 'Signatur';
$lang['Signature_explain'] = 'Blok teks yang akan ditambahkan pada setiap pesan. Jumlahnya dibatasi %d karakter';
$lang['Public_view_email'] = 'Selalu tampilkan alamat email saya';

$lang['Current_password'] = 'Password Kini';
$lang['New_password'] = 'Password baru';
$lang['Confirm_password'] = 'Konfirmasi password';
$lang['Confirm_password_explain'] = 'Password saat kini harus dikonfirmasi jika Anda ingin mengubah password atau mengubah alamat email';
$lang['password_if_changed'] = 'Password hanya perlu dimasukkan jika ingin diubah';
$lang['password_confirm_if_changed'] = 'Anda hanya perlu mengkonfirmasi password jika memang ingin diubah';

$lang['Avatar'] = 'Avatar';
$lang['Avatar_explain'] = 'Avatar adalah gambar/icon kecil dibawah detil Anda pada setiap pesan. Hanya satu gambar yang dapat ditampilkan dengan lebar maksimum %d pixels, tinggi maksimum %d pixels, dan besar file tidak lebih besar dari %dkB.';
$lang['Upload_Avatar_file'] = 'Upload Avatar dari komputer Anda';
$lang['Upload_Avatar_URL'] = 'Upload Avatar dari URL';
$lang['Upload_Avatar_URL_explain'] = 'Masukkan URL lokasi gambar yang Anda inginkan. File tersebut akan di-copy ke server ini.';
$lang['Pick_local_Avatar'] = 'Pilih Avatar dari galeri';
$lang['Link_remote_Avatar'] = 'Link ke Avatar dari situs lain';
$lang['Link_remote_Avatar_explain'] = 'Masukkan URL lokasi dimana Avatar yang Anda inginkan berada.';
$lang['Avatar_URL'] = 'URL Avatar';
$lang['Select_from_gallery'] = 'Pilih Avatar dari galeri';
$lang['View_avatar_gallery'] = 'Galeri Avatar';

$lang['Select_avatar'] = 'Pilih avatar';
$lang['Return_profile'] = 'Batal';
$lang['Select_category'] = 'Pilih kategori';

$lang['Delete_Image'] = 'Hapus gambar';
$lang['Current_Image'] = 'Pilihan saat ini';

$lang['Notify_on_privmsg'] = 'Notifikasi pesan pribadi baru';
$lang['Popup_on_privmsg'] = 'Window pop up waktu pesan pribadi masuk'; 
$lang['Popup_on_privmsg_explain'] = 'Beberapa template akan membuka window baru untuk pemberitahuan pesan pribadi baru'; 
$lang['Hide_user'] = 'Sembunyikan status online';

$lang['Profile_updated'] = 'Profil Anda telah diupdate';
$lang['Profile_updated_inactive'] = 'Profil Anda telah diupdate, tapi beberapa detil penting telah Anda rubah sehingga account Anda menjadi non aktif. Cek email Anda dan baca petunjuk untuk reaktivasi account Anda, atau jika dibutuhkan aktivasi oleh Admin, harap tunggu proses oleh administrator';

$lang['Password_mismatch'] = 'Password tak cocok';
$lang['Current_password_mismatch'] = 'Password yang Anda masukkan tidak sesuai dengan data yang kami miliki';
$lang['Password_long'] = 'Password tak boleh lebih dari 32 karakter';
$lang['Username_taken'] = 'Maaf, username yang Anda pilih telah diambil orang lain';
$lang['Username_invalid'] = 'Maaf, username yang Anda masukkan mengandung karakter ilegal seperti \'';
$lang['Username_disallowed'] = 'Maaf, Anda tidak diijinkan menggunakan username tersebut';
$lang['Email_taken'] = 'Maaf, alamat email tersebut telah terdaftar';
$lang['Email_banned'] = 'Maaf, alamat email ini di-banned';
$lang['Email_invalid'] = 'Maaf, alamat email ini tidak valid';
$lang['Signature_too_long'] = 'Signatur Anda terlalu panjang';
$lang['Fields_empty'] = 'Anda harus mengisi entri yang diharuskan';
$lang['Avatar_filetype'] = 'Tipe file avatar haruslah berupa .jpg, .gif atau .png';
$lang['Avatar_filesize'] = 'Besar file avatar tidak boleh lebih dari %d kB'; // The avatar image file size must be less than 6 kB
$lang['Avatar_imagesize'] = 'Ukuran gambar avatar tidak boleh lebih dari lebar %d pixels dan tinggi %d pixels'; 

$lang['Welcome_subject'] = 'Selamat datang di Forum %s'; // Welcome to my.com forums
$lang['New_account_subject'] = 'Account Baru';
$lang['Account_activated_subject'] = 'Account Diaktivasi';

$lang['Account_added'] = 'Terima kasih. Pendaftaran Anda telah diterima dan account Anda telah dibuat. Anda dapat login dengan menggunakan username dan password Anda sekarang';
$lang['Account_inactive'] = 'Account Anda telah dibuat. Forum ini mengharuskan aktivasi account. Kunci aktivasi telah dikirimkan ke alamat yang Anda berikan. Silakan cek email Anda untuk keterangan lebih lanjut';
$lang['Account_inactive_admin'] = 'Account Anda telah dibuat. Administrator harus mengaktivasi account Anda. Email telah dikirimkan ke administrator, dan Anda akan diinformasikan jika account Anda telah aktif';
$lang['Account_active'] = 'Account Anda telah aktif. Selamat bergabung';
$lang['Account_active_admin'] = 'Account ini telah diaktifkan';
$lang['Reactivate'] = 'Aktivasi ulang account Anda!';
$lang['Already_activated'] = 'Account Anda telah diaktifkan';
$lang['COPPA'] = 'Account Anda telah dibuat, tapi harus disetujui terlebih dulu. Silakan cek email Anda untuk detil lebih lanjut.';

$lang['Registration'] = 'Ketentuan Registrasi';
$lang['Reg_agreement'] = 'Walaupun Administrator dan moderator forum ini akan berusaha untuk menghapus atau mengubah semua pesan yang tidak pantas secepat mungkin, adalah tidak mungkin untuk melakukan pengecekan terhadap setiap pesan. Dengan demikian, Anda mengakui bahwa semua kiriman pada forum ini adalah pandangan dan pendapat dari masing-masing penulis dan bukan pendapat dari para administrator, moderator, atau webmaster (kecuali yang dikirimkan oleh orang-orang tersebut) dan karenanya bukan merupakan tanggung jawab mereka.<br /><br />Anda setuju untuk tidak mengirimkan pesan abusive, obscene, vulgar, slanderous, hateful, threatening, sexually-orientated atau apapun yang akan melanggar hukum. Jika Anda melakukan hal tersebut, Anda akan langsung dan secara permanen di banned (dan ISP Anda akan diberi tahu). Alamat IP semua pesan disimpan untuk membantu pelaksanaan aturan ini. Anda setuju bahwa webmaster, administrator, dan moderator forum ini memiliki hak untuk menghapus, mengubah, memindahkan, atau menutup topik manapun pada waktu kapanpun jika dinilai pantas dilakukan. Sebagai pengguna Anda setuju bahwa semua informasi yang Anda masukkan disini akan disimpan pada database. Walaupun informasi ini tidak akan dibuka pada pihak ketiga manapun tanpa persetujuan Anda, para webmaster, administrator,dan moderator tidak bertanggung jawab terhadap semua jenis upaya hacking yang dapat membuka data tersebut<br /><br />Sistem forum ini menggunakan cookies untuk menyimpan informasi pada komputer lokal Anda. Cookies ini tidak mengandung informasi yang telah Anda masukkan di atas, melainkan hanya berfungsi untuk memudahkan Anda mengakses forum. Alamat email hanya digunakan untuk mengkonfirmasi registrasi dan password Anda (dan untuk mengirimkan password baru jika Anda lupa password Anda).<br /><br />Dengan menekan link Registrasi dibawah, Anda menyetujui semua ketentuan di atas.';

$lang['Agree_under_13'] = 'Saya setuju dengan ketentuan ini dan saya berusia <b>di bawah</b> 13 tahun';
$lang['Agree_over_13'] = 'Saya setuju dengan ketentuan ini dan saya berusia <b>di atas</b> atau <b>tepat</b> 13 tahun';
$lang['Agree_not'] = 'Saya tidak menyetujui ketentuan ini';

$lang['Wrong_activation'] = 'Kunci aktivasi yang Anda masukkan tidak sesuai data pada database kami';
$lang['Send_password'] = 'Kirim password baru'; 
$lang['Password_updated'] = 'Password baru telah dibuat, silakan cek email Anda untuk petunjuk lengkap cara aktivasi';
$lang['No_email_match'] = 'Alamat Email yang Anda masukkan tidak sesuai dengan yang ada dalam daftar kami';
$lang['New_password_activation'] = 'Aktivasi password baru';
$lang['Password_activated'] = 'Account Anda telah diaktivasi ulang. Untuk logon, gunakan password yang dikirimkan ke alamat email Anda';

$lang['Send_email_msg'] = 'Kirim email';
$lang['No_user_specified'] = 'Tidak ada user Anda pilih';
$lang['User_prevent_email'] = 'User ini tidak mau mengerima email. Cobalah mengirimkan pesan pribadi';
$lang['User_not_exist'] = 'User tersebut tidak ada';
$lang['CC_email'] = 'Kirim salinan pesan ke email sendiri';
$lang['Email_message_desc'] = 'Pesan ini akan dikirim dalam bentuk teks, karenanya jangan menggunakan HTML atau BBCode. Alamat balasan pesan ini akan diset menjadi alamat email Anda.';
$lang['Flood_email_limit'] = 'Anda tidak dapat mengirimkan email lain saat ini. Harap tunggu.';
$lang['Recipient'] = 'Penerima';
$lang['Email_sent'] = 'Email telah dikirimkan';
$lang['Send_email'] = 'Kirim email';
$lang['Empty_subject_email'] = 'Judul pesan email harus dimasukkan';
$lang['Empty_message_email'] = 'Isi pesan harus dimasukkan';


//
// Memberslist
//
$lang['Select_sort_method'] = 'Pilih urutan tampilan';
$lang['Sort'] = 'Urutkan!';
$lang['Sort_Top_Ten'] = '10 Teraktif';
$lang['Sort_Joined'] = 'Tgl Daftar';
$lang['Sort_Username'] = 'Username';
$lang['Sort_Location'] = 'Lokasi';
$lang['Sort_Posts'] = 'Jumlah Pesan';
$lang['Sort_Email'] = 'Email';
$lang['Sort_Website'] = 'Website';
$lang['Sort_Ascending'] = 'Terkecil';
$lang['Sort_Descending'] = 'Terbesar';
$lang['Order'] = 'Urutan';


//
// Group control panel
//
$lang['Group_Control_Panel'] = 'Control Panel Grup';
$lang['Group_member_details'] = 'Detil Keanggotaan Grup';
$lang['Group_member_join'] = 'Bergabung dengan Grup';

$lang['Group_Information'] = 'Informasi Grup';
$lang['Group_name'] = 'Nama grup';
$lang['Group_description'] = 'Deskripsi grup';
$lang['Group_membership'] = 'Keanggotaan grup';
$lang['Group_Members'] = 'Anggota grup';
$lang['Group_Moderator'] = 'Moderator grup';
$lang['Pending_members'] = 'Anggota tunda';

$lang['Group_type'] = 'Tipe grup';
$lang['Group_open'] = 'Grup terbuka';
$lang['Group_closed'] = 'Grup tertutup';
$lang['Group_hidden'] = 'Grup tersembunyi';

$lang['Current_memberships'] = 'Keanggotaan kini';
$lang['Non_member_groups'] = 'Grup Non-member';
$lang['Memberships_pending'] = 'Keanggotaan tunda';

$lang['No_groups_exist'] = 'Tidak ada grup yang tersedia';
$lang['Group_not_exist'] = 'Grup user tersebut tidak tersedia';

$lang['Join_group'] = 'Bergabung dengan grup';
$lang['No_group_members'] = 'Grup ini tidak memiliki anggota';
$lang['Group_hidden_members'] = 'Grup ini tersebunyi, Anda tidak dapat melihat anggotanya';
$lang['No_pending_group_members'] = 'Grup ini tidak memiliki anggota tunda';
$lang['Group_joined'] = 'Anda telah berhasil mengirimkan pendaftaran grup<br />Jika pendaftaran Anda disetujui oleh moderator grup, Anda akan diberitahu';
$lang['Group_request'] = 'Permintaan untuk bergabung dengan grup telah dikirim';
$lang['Group_approved'] = 'Permintaan Anda telah disetujui';
$lang['Group_added'] = 'Anda telah diikutkan pada grup ini'; 
$lang['Already_member_group'] = 'Anda telah menjadi anggota grup ini';
$lang['User_is_member_group'] = 'User telah menjadi anggota grup';
$lang['Group_type_updated'] = 'Tipe grup berhasil diubah';

$lang['Could_not_add_user'] = 'User yang Anda pilih tidak ditemukan';
$lang['Could_not_anon_user'] = 'Anonymous tidak bisa dikelompokkan dalam grup';

$lang['Confirm_unsub'] = 'Anda yakin akan mengundurkan diri dari grup ini?';
$lang['Confirm_unsub_pending'] = 'Pendaftaran Anda belum lagi disetujui, Anda yakin akan mengundurkan diri dari grup ini?';

$lang['Unsub_success'] = 'Keanggotaan Anda di grup ini telah dibatalkan.';

$lang['Approve_selected'] = 'Setujui Pilihan';
$lang['Deny_selected'] = 'Tolak Pilihan';
$lang['Not_logged_in'] = 'Anda harus login untuk bergabung dengan suatu grup.';
$lang['Remove_selected'] = 'Hapus Pilihan';
$lang['Add_member'] = 'Tambah Anggota';
$lang['Not_group_moderator'] = 'Anda bukan moderator group ini sehingga Anda tidak dapat melakukan operasi tersebut.';

$lang['Login_to_join'] = 'Login untuk bergabung atau mengatur grup';
$lang['This_open_group'] = 'Grup terbuka, klik untuk mengajukan keanggotaan';
$lang['This_closed_group'] = 'Grup tertutup, tidak mengijinkan penambahan anggota';
$lang['This_hidden_group'] = 'Grup tersembunyi, penambahan user otomatis tidak diperbolehkan';
$lang['Member_this_group'] = 'Anda anggota grup ini';
$lang['Pending_this_group'] = 'Keanggotaan Anda sedang diproses';
$lang['Are_group_moderator'] = 'Anda adalah moderator grup';
$lang['None'] = 'Tidak ada';

$lang['Subscribe'] = 'Daftar';
$lang['Unsubscribe'] = 'Keluar';
$lang['View_Information'] = 'Lihat Info';


//
// Search
//
$lang['Search_query'] = 'Parameter Pencarian';
$lang['Search_options'] = 'Pilihan Pencarian';

$lang['Search_keywords'] = 'Cari Keyword';
$lang['Search_keywords_explain'] = 'Anda dapat menggunakan <u>AND</u> untuk mendefinisikan kata yang harus ada di hasil pencarian, <u>OR</u> untuk kata yang mungkin ada di hasil pencarian, dan <u>NOT</u> untuk kata yang tidak boleh ada. Gunakan * untuk wildcard.';
$lang['Search_author'] = 'Cari pengirim';
$lang['Search_author_explain'] = 'Gunakan * untuk wildcard';

$lang['Search_for_any'] = 'Cari salah satu kata atau sesuai urutan penulisan';
$lang['Search_for_all'] = 'Cari semua kata';
$lang['Search_title_msg'] = 'Cari di judul topik dan isi pesan';
$lang['Search_msg_only'] = 'Cari hanya di isi pesan';

$lang['Return_first'] = 'Tampilan'; // followed by xxx characters in a select box
$lang['characters_posts'] = 'karakter pertama pesan';

$lang['Search_previous'] = 'Cari sebelumnya'; // followed by days, weeks, months, year, all in a select box

$lang['Sort_by'] = 'Urutan';
$lang['Sort_Time'] = 'Waktu Kirim';
$lang['Sort_Post_Subject'] = 'Judul pesan';
$lang['Sort_Topic_Title'] = 'Judul topik';
$lang['Sort_Author'] = 'Pengirim';
$lang['Sort_Forum'] = 'Forum';

$lang['Display_results'] = 'Tampilan hasil sesuai';
$lang['All_available'] = 'Semua tersedia';
$lang['No_searchable_forums'] = 'Anda tidak memiliki hak akses untuk melakukan pencarian pada forum';

$lang['No_search_match'] = 'Tak ada topik atau pesan yang sesuai dengan yang Anda cari';
$lang['Found_search_match'] = 'Ditemukan %d data yang cocok'; // eg. Search found 1 match
$lang['Found_search_matches'] = 'Ditemukan %d data yang cocok'; // eg. Search found 24 matches

$lang['Close_window'] = 'Tutup Window';


//
// Auth related entries
//
// Note the %s will be replaced with one of the following 'user' arrays
$lang['Sorry_auth_announce'] = 'Maaf, hanya %s diijinkan mengirim Pengumuman pada forum ini';
$lang['Sorry_auth_sticky'] = 'Maaf, hanya %s diijinkan mengirim Pesan Tetap pada forum ini'; 
$lang['Sorry_auth_read'] = 'Maaf, hanya %s diijinkan membaca topik pada forum ini'; 
$lang['Sorry_auth_post'] = 'Maaf, hanya %s diijinkan mengirim topik pada forum ini'; 
$lang['Sorry_auth_reply'] = 'Maaf, hanya %s diijinkan mengirim balasan pada forum ini'; 
$lang['Sorry_auth_edit'] = 'Maaf, hanya %s  diijinkan mengubah pesan pada forum ini'; 
$lang['Sorry_auth_delete'] = 'Maaf, hanya %s  diijinkan menghapus pesan pada forum ini'; 
$lang['Sorry_auth_vote'] = 'Maaf, hanya %s dapat ikut memasukkan suara pada forum ini'; 

// These replace the %s in the above strings
$lang['Auth_Anonymous_Users'] = '<b>user anonim</b>';
$lang['Auth_Registered_Users'] = '<b>user terdaftar</b>';
$lang['Auth_Users_granted_access'] = '<b>user dengan hak khusus</b>';
$lang['Auth_Moderators'] = '<b>moderator</b>';
$lang['Auth_Administrators'] = '<b>administrator</b>';

$lang['Not_Moderator'] = 'Anda bukan moderator forum ini';
$lang['Not_Authorised'] = 'Akses ditolak';

$lang['You_been_banned'] = 'Anda telah di-banned dari forum ini<br />Silakan kontak webmaster atau administrator forum untuk informasi lebih lanjut';


//
// Viewonline
//
$lang['Reg_users_zero_online'] = 'Ada 0 user terdaftar dan '; // There ae 5 Registered and
$lang['Reg_users_online'] = 'Ada %d user terdaftar dan  '; // There ae 5 Registered and
$lang['Reg_user_online'] = 'Ada %d user terdaftar dan  '; // There ae 5 Registered and
$lang['Hidden_users_zero_online'] = '0 user tersembunyi'; // 6 Hidden users online
$lang['Hidden_users_online'] = '%d user tersembunyi'; // 6 Hidden users online
$lang['Hidden_user_online'] = '%d user tersembunyi'; // 6 Hidden users online
$lang['Guest_users_online'] = 'Ada %d Tamu online'; // There are 10 Guest users online
$lang['Guest_users_zero_online'] = 'Ada 0 Tamu online'; // There are 10 Guest users online
$lang['Guest_user_online'] = 'Ada %d Tamu online'; // There is 1 Guest user online
$lang['No_users_browsing'] = 'Tak ada user yang sedang melihat forum ini';

$lang['Online_explain'] = 'Data ini didasarkan pada data user aktif lima menit terakhir';

$lang['Forum_Location'] = 'Lokasi Forum';
$lang['Last_updated'] = 'Perubahan Akhir';

$lang['Forum_index'] = 'Indeks Forum';
$lang['Logging_on'] = 'Logging on';
$lang['Posting_message'] = 'Menulis pesan';
$lang['Searching_forums'] = 'Melakukan pencarian';
$lang['Viewing_profile'] = 'Melihat profil';
$lang['Viewing_online'] = 'Melihat siapa yg sedang online';
$lang['Viewing_member_list'] = 'Melihat daftar anggota';
$lang['Viewing_priv_msgs'] = 'Melihat pesan pribadi';
$lang['Viewing_FAQ'] = 'Melihat FAQ';


//
// Moderator Control Panel
//
$lang['Mod_CP'] = 'Control Panel Moderator';
$lang['Mod_CP_explain'] = 'Dengan menggunakan form di bawah ini, Anda dapat melakukan kegiatan moderasi untuk forum ini secara massal. Anda dapat mengunci, membuka, memindahkan, atau menghapus beberapa topik sekaligus.';

$lang['Select'] = 'Pilih';
$lang['Delete'] = 'Hapus';
$lang['Move'] = 'Pindah';
$lang['Lock'] = 'Kunci';
$lang['Unlock'] = 'Buka';

$lang['Topics_Removed'] = 'Topik-topik pilihan berhasil dihapus dari database';
$lang['Topics_Locked'] = 'Topik-topik pilihan berhasil dikunci';
$lang['Topics_Moved'] = 'Topik-topik pilihan berhasil dipindahkan';
$lang['Topics_Unlocked'] = 'Topik-topik pilihan berhasil dibuak';
$lang['No_Topics_Moved'] = 'Tak ada topik yang dipindahkan';

$lang['Confirm_delete_topic'] = 'Anda yakin untuk menghapus topik pilihan tersebut?';
$lang['Confirm_lock_topic'] = 'Anda yakin untuk mengunci topik pilihan tersebut?';
$lang['Confirm_unlock_topic'] = 'Anda yakin untuk membuka topik pilihan tersebut?';
$lang['Confirm_move_topic'] = 'Anda yakin untuk memindahkan topik pilihan tersebut?';

$lang['Move_to_forum'] = 'Pindahkan ke forum';
$lang['Leave_shadow_topic'] = 'Biarkan topik bayangan pada forum asal.';

$lang['Split_Topic'] = 'Control Panel Pemecahan Topik';
$lang['Split_Topic_explain'] = 'Anda dapat memecah suatu topik menjadi dua baik dengan memilih pesan secara individu, ataupun sekaligus';
$lang['Split_title'] = 'Judul topik baru';
$lang['Split_forum'] = 'Forum untuk topik baru';
$lang['Split_posts'] = 'Pecah pesan';
$lang['Split_after'] = 'Pecah pesan';
$lang['Topic_split'] = 'Topik telah dipisah menjadi pecahan';

$lang['Too_many_error'] = 'Anda telah memilih terlalu banyak pesan. Hanya satu pesan yang dapat dipilih untuk dipecah!';

$lang['None_selected'] = 'Anda belum memilih topik apapun. Silakan kembali dan pilih salah satu topik.';
$lang['New_forum'] = 'Forum baru';

$lang['This_posts_IP'] = 'IP untuk pesan ini';
$lang['Other_IP_this_user'] = 'Alamat IP lain yang digunakan user';
$lang['Users_this_IP'] = 'User yang mengirim dari IP ini';
$lang['IP_info'] = 'Info IP';
$lang['Lookup_IP'] = 'Lihat IP';


//
// Timezones ... for display on each page
//
$lang['All_times'] = 'Zona waktu menurut %s'; // eg. All times are GMT -12 Hours (times from next block)

$lang['-12'] = 'GMT -12 Jam';
$lang['-11'] = 'GMT -11 Jam';
$lang['-10'] = 'GMT -10 Jam';
$lang['-9'] = 'GMT -9 Jam';
$lang['-8'] = 'GMT -8 Jam';
$lang['-7'] = 'GMT -7 Jam';
$lang['-6'] = 'GMT -6 Jam';
$lang['-5'] = 'GMT -5 Jam';
$lang['-4'] = 'GMT -4 Jam';
$lang['-3.5'] = 'GMT -3.5 Jam';
$lang['-3'] = 'GMT -3 Jam';
$lang['-2'] = 'GMT -2 Jam';
$lang['-1'] = 'GMT -1 Jam';
$lang['0'] = 'GMT';
$lang['1'] = 'GMT +1 Hour';
$lang['2'] = 'GMT +2 Jam';
$lang['3'] = 'GMT +3 Jam';
$lang['3.5'] = 'GMT +3.5 Jam';
$lang['4'] = 'GMT +4 Jam';
$lang['4.5'] = 'GMT +4.5 Jam';
$lang['5'] = 'GMT +5 Jam';
$lang['5.5'] = 'GMT +5.5 Jam';
$lang['6'] = 'GMT +6 Jam';
$lang['6.5'] = 'GMT +6.5 Jam';
$lang['7'] = 'GMT +7 Jam';
$lang['8'] = 'GMT +8 Jam';
$lang['9'] = 'GMT +9 Jam';
$lang['9.5'] = 'GMT +9.5 Jam';
$lang['10'] = 'GMT +10 Jam';
$lang['11'] = 'GMT +11 Jam';
$lang['12'] = 'GMT +12 Jam';

// These are displayed in the timezone select box
$lang['tz']['-12'] = 'GMT -12 Jam';
$lang['tz']['-11'] = 'GMT -11 Jam';
$lang['tz']['-10'] = 'GMT -10 Jam';
$lang['tz']['-9'] = 'GMT -9 Jam';
$lang['tz']['-8'] = 'GMT -8 Jam';
$lang['tz']['-7'] = 'GMT -7 Jam';
$lang['tz']['-6'] = 'GMT -6 Jam';
$lang['tz']['-5'] = 'GMT -5 Jam';
$lang['tz']['-4'] = 'GMT -4 Jam';
$lang['tz']['-3.5'] = 'GMT -3.5 Jam';
$lang['tz']['-3'] = 'GMT -3 Jam';
$lang['tz']['-2'] = 'GMT -2 Jam';
$lang['tz']['-1'] = 'GMT -1 Jam';
$lang['tz']['0'] = 'GMT';
$lang['tz']['1'] = 'GMT +1 Hour';
$lang['tz']['2'] = 'GMT +2 Jam';
$lang['tz']['3'] = 'GMT +3 Jam';
$lang['tz']['3.5'] = 'GMT +3.5 Jam';
$lang['tz']['4'] = 'GMT +4 Jam';
$lang['tz']['4.5'] = 'GMT +4.5 Jam';
$lang['tz']['5'] = 'GMT +5 Jam';
$lang['tz']['5.5'] = 'GMT +5.5 Jam';
$lang['tz']['6'] = 'GMT +6 Jam';
$lang['tz']['6.5'] = 'GMT +6.5 Jam';
$lang['tz']['7'] = 'GMT +7 Jam';
$lang['tz']['8'] = 'GMT +8 Jam';
$lang['tz']['9'] = 'GMT +9 Jam';
$lang['tz']['9.5'] = 'GMT +9.5 Jam';
$lang['tz']['10'] = 'GMT +10 Jam';
$lang['tz']['11'] = 'GMT +11 Jam';
$lang['tz']['12'] = 'GMT +12 Jam';

$lang['datetime']['Sunday'] = 'Minggu';
$lang['datetime']['Monday'] = 'Senin';
$lang['datetime']['Tuesday'] = 'Selasa';
$lang['datetime']['Wednesday'] = 'Rabu';
$lang['datetime']['Thursday'] = 'Kamis';
$lang['datetime']['Friday'] = 'Jumat';
$lang['datetime']['Saturday'] = 'Sabtu';
$lang['datetime']['Sun'] = 'Min';
$lang['datetime']['Mon'] = 'Sen';
$lang['datetime']['Tue'] = 'Sel';
$lang['datetime']['Wed'] = 'Rab';
$lang['datetime']['Thu'] = 'Kam';
$lang['datetime']['Fri'] = 'Jum';
$lang['datetime']['Sat'] = 'Sab';
$lang['datetime']['January'] = 'Januari';
$lang['datetime']['February'] = 'Februari';
$lang['datetime']['March'] = 'Maret';
$lang['datetime']['April'] = 'April';
$lang['datetime']['May'] = 'Mei';
$lang['datetime']['June'] = 'Juni';
$lang['datetime']['July'] = 'Juli';
$lang['datetime']['August'] = 'Agustus';
$lang['datetime']['September'] = 'September';
$lang['datetime']['October'] = 'Oktober';
$lang['datetime']['November'] = 'November';
$lang['datetime']['December'] = 'Desember';
$lang['datetime']['Jan'] = 'Jan';
$lang['datetime']['Feb'] = 'Feb';
$lang['datetime']['Mar'] = 'Mar';
$lang['datetime']['Apr'] = 'Apr';
$lang['datetime']['May'] = 'Mei';
$lang['datetime']['Jun'] = 'Jun';
$lang['datetime']['Jul'] = 'Jul';
$lang['datetime']['Aug'] = 'Ags';
$lang['datetime']['Sep'] = 'Sep';
$lang['datetime']['Oct'] = 'Okt';
$lang['datetime']['Nov'] = 'Nov';
$lang['datetime']['Dec'] = 'Desc';

//
// Errors (not related to a
// specific failure on a page)
//
$lang['Information'] = 'Informasi';
$lang['Critical_Information'] = 'Critical Information';

$lang['General_Error'] = 'General Error';
$lang['Critical_Error'] = 'Critical Error';
$lang['An_error_occured'] = 'Terjadi Error';
$lang['A_critical_error'] = 'Terjadi Critical Error';

//
// That's all Folks!
// -------------------------------------------------

?>
