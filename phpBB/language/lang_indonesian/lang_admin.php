<?php

/***************************************************************************
 *                            lang_admin.php [English]
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
// Format is same as lang_main
//

//
// Modules, this replaces the keys used
// in the modules[][] arrays in each module file
//
$lang['General'] = 'Admin Umum';
$lang['Users'] = 'Admin User';
$lang['Groups'] = 'Admin Group';
$lang['Forums'] = 'Admin Forum';
$lang['Styles'] = 'Admin Styles';

$lang['Configuration'] = 'Konfigurasi';
$lang['Permissions'] = 'Hak Akses';
$lang['Manage'] = 'Manajemen';
$lang['Disallow'] = 'Batasan Nama';
$lang['Prune'] = 'Pembersihan';
$lang['Mass_Email'] = 'Email Massal';
$lang['Ranks'] = 'Peringkat';
$lang['Smilies'] = 'Smilies';
$lang['Ban_Management'] = 'Kontrol Ban';
$lang['Word_Censor'] = 'Sensor Kata';
$lang['Export'] = 'Ekspor';
$lang['Create_new'] = 'Buat Baru';
$lang['Add_new'] = 'Tambah';
$lang['Backup_DB'] = 'Backup Database';
$lang['Restore_DB'] = 'Pembaikan Database';


//
// Index
//
$lang['Admin'] = 'Administrasi';
$lang['Not_admin'] = 'Anda tak memiliki akses sebagai Administrator';
$lang['Welcome_phpBB'] = 'Selamat datang di phpBB';
$lang['Admin_intro'] = 'Terima kasih telah memilih phpBB sebagai solusi forum Anda. Layar ini menampilkan ringkasan berbagai statistik forum Anda. Anda dapat kembali ke halaman ini dengan mengklik link <u>Admin Index</u> di sebelah kiri. Untuk kembali ke halaman utama forum Anda, klik logo phpBB logo di sebelah kiri atas. Menu lain di sebelah kiri akan membawa Anda keberbagai wilayah administrator forum. Setiap halaman akan mengandung penjelasan ringkas mengenai cara penggunaaanya.';
$lang['Main_index'] = 'Halaman Forum';
$lang['Forum_stats'] = 'Statistik Forum';
$lang['Admin_Index'] = 'Halaman Admin';
$lang['Preview_forum'] = 'Preview Forum';

$lang['Click_return_admin_index'] = '%sKembali ke Indeks Admin%s';

$lang['Statistik'] = 'Statistik';
$lang['Value'] = 'Value';
$lang['Number_posts'] = 'Jumlah posting';
$lang['Posts_per_day'] = 'Posting per hari';
$lang['Number_topics'] = 'Jumlah topik';
$lang['Topics_per_day'] = 'Topik per hari';
$lang['Number_users'] = 'Jumlah user';
$lang['Users_per_day'] = 'User per hari';
$lang['Board_started'] = 'Mulai Board';
$lang['Avatar_dir_size'] = 'Besar Direktori Avatar';
$lang['Database_size'] = 'Besar Database';
$lang['Gzip_compression'] ='Kompresi Gzip';
$lang['Not_available'] = 'Tak tersedia';

$lang['ON'] = 'ON'; // This is for GZip compression
$lang['OFF'] = 'OFF'; 


//
// DB Utils
//
$lang['Database_Utilities'] = 'Utiliti Database';

$lang['Restore'] = 'Restore';
$lang['Backup'] = 'Backup';
$lang['Restore_explain'] = 'Restore akan melakukan pembaikan total semua tabel phpBB dari file. Jika server Anda mendukung, Anda dapat melakukan upload terhadap file gzip dari file teks dan secara otomatis mendekompresi file tersebut. <strong>PERINGATAN</strong> Proses ini akan menghapus semua data. Proses pembaikan mungkin berlangsung cukup lama. Jangan meninggalkan halaman ini sebelum proses selesai.';
$lang['Backup_explain'] = 'Disini Anda dapat melakukan backup untuk semua data phpBB Anda. Jika Anda memiliki tabel tambahan lain pada database yang sama dengan phpBB yang ingin Anda backup juga, harap masukkan nama tabel-tabel tersebut, dipisahkan dengan koma, pada entri Tabel Tambahan. Jika server Anda menyediakan fasilitas Gzip, Anda dapat juga mengompresi file untuk mengurangi ukurannya sebelum di download.';

$lang['Backup_options'] = 'Pilihan Backup';
$lang['Start_backup'] = 'Mulai Backup';
$lang['Full_backup'] = 'Backup Total';
$lang['Structure_backup'] = 'Backup Struktur';
$lang['Data_backup'] = 'Backup Data';
$lang['Additional_tables'] = 'Tabel Tambahan';
$lang['Gzip_compress'] = 'File Gzip';
$lang['Select_file'] = 'Pilih file';
$lang['Start_Restore'] = 'Mulai pembaikan';

$lang['Restore_success'] = 'Database telah berhasil di-restore.<br /><br />Forum Anda akan kembali ke kondisi terakhir sebelum di backup.';
$lang['Backup_download'] = 'Download akan segera dijalankan. Harap tunggu sampai proses selesai.';
$lang['Backups_not_supported'] = 'Maaf, jenis database Anda tidak memiliki fasilitas backup';

$lang['Restore_Error_uploading'] = 'Error sewaktu mengupload file backup';
$lang['Restore_Error_filename'] = 'Masalah nama file. Harap upload file lain';
$lang['Restore_Error_decompress'] = 'Tidak dapat membuka file gzip. Tolong upload versi file teks biasa.';
$lang['Restore_Error_no_file'] = 'Tidak ada file yang dikirimkan';


//
// Auth pages
//
$lang['Select_a_User'] = 'Pilih User';
$lang['Select_a_Group'] = 'Pilih Group';
$lang['Select_a_Forum'] = 'Pilih Forum';
$lang['Auth_Control_User'] = 'Hak Akses User'; 
$lang['Auth_Control_Group'] = 'Hak Akses Group'; 
$lang['Auth_Control_Forum'] = 'Hak Akses Forum'; 
$lang['Look_up_User'] = 'Detil User'; 
$lang['Look_up_Group'] = 'Detil Group'; 
$lang['Look_up_Forum'] = 'Detil Forum'; 

$lang['Group_auth_explain'] = 'Anda dapat mengubah hak akses dan status moderator untuk tiap group. Jangan lupa bahwa hak akses individu akan tetap mengijinkan user untuk masuk ke forum, dll. Akan dimunculkan peringatan jika hal ini terjadi.';
$lang['User_auth_explain'] = 'Anda dapat mengubah hak akses dan status moderator untuk tiap individu. Jangan lupa bahwa hak akses group akan tetap mengijinkan user untuk masuk ke forum, dll. Akan dimunculkan peringatan jika hal ini terjadi.';
$lang['Forum_auth_explain'] = 'Anda dapat mengubah level otorisasi setiap forum. Tersedia cara sederhana dan lanjut untuk melakukan hal ini. Cara lanjut memungkinkan kontrol lebih besar untuk setiap operasi forum. Ingat bahwa perubahan level hak akses pada forum akan berpengaruh terhadap fasilitas user untuk forum tersebut.';

$lang['Simple_mode'] = 'Mode Sederhana';
$lang['Advanced_mode'] = 'Mode Lanjut';
$lang['Moderator_status'] = 'Status Moderator';

$lang['Allowed_Access'] = 'Akses Diijinkan';
$lang['Disallowed_Access'] = 'Akses Ditolak';
$lang['Is_Moderator'] = 'Moderator';
$lang['Not_Moderator'] = 'Bukan Moderator';

$lang['Conflict_warning'] = 'Peringatan Konflik Otorisasi';
$lang['Conflict_access_userauth'] = 'User ini masih memiliki akses ke forum melalui keanggotaan group. Anda mungkin harus mengubah akses group atau menghapus keanggotaan group user ini untuk mencegah mereka memiliki akses. Group pemberi akses (dan forum yang terlibat) tertera di bawah ini.';
$lang['Conflict_mod_userauth'] = 'User ini memiliki hak moderator untuk forum ini melalui keanggotaan group. Anda mungkin harus mengubah akses group atau menghapus keanggotaan group untuk mencegah mereka memiliki akses. Group pemberi akses (dan forum yang terlibat) tertera di bawah ini.';

$lang['Public'] = 'Publik';
$lang['Private'] = 'Pribadi';
$lang['Registered'] = 'Terdaftar';
$lang['Administrators'] = 'Administrator';
$lang['Hidden'] = 'Tersembunyi';

// These are displayed in the drop down boxes for advanced
// mode forum auth, try and keep them short!
$lang['Forum_ALL'] = 'SEMUA';
$lang['Forum_REG'] = 'REG';
$lang['Forum_PRIVATE'] = 'PRIBADI';
$lang['Forum_MOD'] = 'MOD';
$lang['Forum_ADMIN'] = 'ADMIN';

$lang['View'] = 'Lihat';
$lang['Read'] = 'Baca';
$lang['Post'] = 'Kirim';
$lang['Reply'] = 'Balas';
$lang['Edit'] = 'Ubah';
$lang['Delete'] = 'Hapus';
$lang['Sticky'] = 'Tetap';
$lang['Announce'] = 'Pengumuman'; 
$lang['Vote'] = 'Vote';
$lang['Pollcreate'] = 'Buat Polling';

$lang['Permissions'] = 'Hak Akses';
$lang['Simple_Permission'] = 'Hak Akses Sederhana';

$lang['User_Level'] = 'Level User'; 
$lang['Auth_User'] = 'User';
$lang['Auth_Admin'] = 'Administrator';
$lang['Group_memberships'] = 'Anggota Usergroup';
$lang['Usergroup_members'] = 'Group ini memiliki anggota berikut';

$lang['Forum_auth_updated'] = 'Hak akses forum berhasil diubah';
$lang['User_auth_updated'] = 'Hak akses user berhasil diubah';
$lang['Group_auth_updated'] = 'Hak akses group berhasil diubah';

$lang['Auth_updated'] = 'Hak akses berhasil diubah';
$lang['Click_return_userauth'] = '%sKembali ke hak akses user%s';
$lang['Click_return_groupauth'] = '%sKembali ke hak akses group%s';
$lang['Click_return_forumauth'] = '%sKembali ke hak akses forum%s';


//
// Banning
//
$lang['Ban_control'] = 'Kontrol Ban';
$lang['Ban_explain'] = 'Disini Anda dapat mengatur ban user. Anda dapat melakukan hal ini baik terhadap user atau individu tertentu atau range alamat IP atau hostname. Metode ini akan menghambat user untuk mengakses bahkan halaman utama forum Anda. Untuk menghambat user mendaftar atas nama lain, Anda dapat juga membanned alamat email. Harap diperhatikan bahwa membanned alamat email saja tidak akan menghambat user tersebut untuk dapat login atau memposting ke forum Anda. Anda harus menggunakan kedua metode pertama tadi untuk melakukan itu.';
$lang['Ban_explain_warn'] = 'Harap perhatikan bahwa memasukkan range alamat IP akan mengakibatkan semua alamat antara awal dan akhir akan ditambahkan pada daftar ban. Kami akan mengupayakan untuk meminimalikan jumlah alamat yang ditambahkan pada database dengan menggunakan <em>wildcards</em> secara otomatis jika sesuai. Jika Anda harus memasukkan suatu range, cobalah untuk sesedikit mungkin, atau bahkan upayakan alamat spesifik.';

$lang['Select_username'] = 'Pilih Username';
$lang['Select_ip'] = 'Pilih IP';
$lang['Select_email'] = 'Pilih Alamat Email';

$lang['Ban_username'] = 'Ban satu atau lebih user spesifik';
$lang['Ban_username_explain'] = 'Anda dapat membanned beberapa user sekaligus dengan menggunakan kombinasi mouse dan keyboard dari komputer dan browser Anda';

$lang['Ban_IP'] = 'Ban satu atau lebih alamat IP atau hostname';
$lang['IP_hostname'] = 'Alamat IP atau hostname';
$lang['Ban_IP_explain'] = 'Untuk memasukkan beberapa IP atau hostname, pisahkan dengan koma. Untuk memasukkan range IP, pisahkan awal dan akhir dengan tanda hubung (-). Untuk menggunakan wildcards, gunakan *';

$lang['Ban_email'] = 'Ban satu atau lebih alamat email';
$lang['Ban_email_explain'] = 'Untuk memasukkan lebih dari satu alamat email, pisahkan dengan koma. To specify more than one email address separate them with commas. Untuk menggunakan wildcards, gunakan *, contohnya *@hotmail.com';

$lang['Unban_username'] = 'Un-ban satu atau lebih user';
$lang['Unban_username_explain'] = 'Anda dapat meng-un-ban sekaligus user dengan menggunakan kombinasi mouse dan keyboard Anda';

$lang['Unban_IP'] = 'Un-ban satu atau lebih alamat IP';
$lang['Unban_IP_explain'] = 'Anda dapat meng-un-ban beberapa alamat IP sekaligus dengan menggunakan kombinasi mouse dan keyboard Anda';

$lang['Unban_email'] = 'Un-ban satu atau lebih email addresses';
$lang['Unban_email_explain'] = 'Anda dapat meng-un-ban beberapa alamat email sekaligus dengan menggunakan kombinasi mouse dan keyboard Anda';

$lang['No_banned_users'] = 'Tidak ada user yang diban';
$lang['No_banned_ip'] = 'Tidak ada alamat IP yang di ban';
$lang['No_banned_email'] = 'Tidak ada alamat email yang di ban';

$lang['Ban_update_sucessful'] = 'Ban telah berhasil diubah';
$lang['Click_return_banadmin'] = '%sKembali ke Kontrol Ban%s';


//
// Configuration
//
$lang['General_Config'] = 'Konfigurasi Umum';
$lang['Config_explain'] = 'Form di bawah ini mengijinkan Anda untuk mengkustomasi setting umum forum. Untuk konfigurasi User dan Forum, gunakan link lain pada menu disebelah kiri.';

$lang['Click_return_config'] = '%sKembali ke Konfigurasi Umum%s';

$lang['General_settings'] = 'Setting Umum';
$lang['Server_name'] = 'Nama Domain';
$lang['Server_name_explain'] = 'Nama domain forum ini';
$lang['Script_path'] = 'Path Script';
$lang['Script_path_explain'] = 'Path lokasi phpBB2 relatif terhadap nama domain';
$lang['Server_port'] = 'Port Server';
$lang['Server_port_explain'] = 'Port server yang digunakan, biasanya 80. Hanya ubah jika berbeda';
$lang['Site_name'] = 'Nama Situs';
$lang['Site_desc'] = 'Deskripsi Site';
$lang['Board_disable'] = 'Deaktivasi Forum';
$lang['Board_disable_explain'] = 'Pilihan ini akan menyebabkan board tak dapat diakses user. Jangan logout jika sewaktu Anda mendeaktivasi forum, Anda tidak akan bisa log in kembali!';
$lang['Acct_activation'] = 'Aktivasi account';
$lang['Acc_None'] = 'Inaktif'; // These three entries are the type of activation
$lang['Acc_User'] = 'User';
$lang['Acc_Admin'] = 'Admin';

$lang['Abilities_settings'] = 'Setting Standar User dan Forum';
$lang['Max_poll_options'] = 'Jumlah maksimum pilihan polling';
$lang['Flood_Interval'] = 'Interval flood';
$lang['Flood_Interval_explain'] = 'Satuan detik waktu antar posting user'; 
$lang['Board_email_form'] = 'Fasilitas pengiriman email';
$lang['Board_email_form_explain'] = 'Form untuk mengirim email antar user';
$lang['Topics_per_page'] = 'Topik per halaman';
$lang['Posts_per_page'] = 'Pesan per halaman';
$lang['Hot_threshold'] = 'Batas jumlah untuk status populer';
$lang['Default_style'] = 'Style standar';
$lang['Override_style'] = 'Override style user';
$lang['Override_style_explain'] = 'Ganti style user dengan standar';
$lang['Default_language'] = 'Bahasa standar';
$lang['Date_format'] = 'Format tanggal';
$lang['System_timezone'] = 'Zona waktu sistem';
$lang['Enable_gzip'] = 'Fitur kompresi Gzip';
$lang['Enable_prune'] = 'Fitur pruning forum';
$lang['Allow_HTML'] = 'HTML diijinkan';
$lang['Allow_BBCode'] = 'BBCode diijinkan';
$lang['Allowed_tags'] = 'HTML tags yang diijinkan';
$lang['Allowed_tags_explain'] = 'Pisahkan tag dengan koma';
$lang['Allow_smilies'] = 'Smilies diijinkan';
$lang['Smilies_path'] = 'Path lokasi smilies';
$lang['Smilies_path_explain'] = 'Path dibawah lokasi phpBB, misalnya. images/smiles';
$lang['Allow_sig'] = 'Signatures diijinkan';
$lang['Max_sig_length'] = 'Panjang maks. signatur';
$lang['Max_sig_length_explain'] = 'Jumlah maksimum karakter pada signatur';
$lang['Allow_name_change'] = 'Perubahan username diijinkan';

$lang['Avatar_settings'] = 'Setting Avatar';
$lang['Allow_local'] = 'Galeri avatar tersedia';
$lang['Allow_remote'] = 'Avatar eksternal diperbolehkan';
$lang['Allow_remote_explain'] = 'Avatar yang terhubung ke situs luar';
$lang['Allow_upload'] = 'Upload avatar diperbolehkan';
$lang['Max_filesize'] = 'Ukuran maks. file Avatar';
$lang['Max_filesize_explain'] = 'Untuk file Avatar upload';
$lang['Max_avatar_size'] = 'Ukuran maks. dimensi Avatar';
$lang['Max_avatar_size_explain'] = '(Tinggi x Lebar satuan pixel)';
$lang['Avatar_storage_path'] = 'Lokasi Penyimpanan Avatar';
$lang['Avatar_storage_path_explain'] = 'Lokasi file Avatar dalam direktori phpBB, misalnya images/avatars';
$lang['Avatar_gallery_path'] = 'Lokasi Galeri Avatar';
$lang['Avatar_gallery_path_explain'] = 'Lokasi koleksi file Avatar dalam direktori phpBB, misalnya images/avatars/gallery';

$lang['COPPA_settings'] = 'Setting COPPA';
$lang['COPPA_fax'] = 'Nomor Fax COPPA';
$lang['COPPA_mail'] = 'Alamat Surat COPPA';
$lang['COPPA_mail_explain'] = 'Alamat surat menyurat untuk orang tua yang akan mengirimkan form registrasi COPPA';

$lang['Email_settings'] = 'Setting Email';
$lang['Admin_email'] = 'Alamat Email Admin';
$lang['Email_sig'] = 'Signatur Email';
$lang['Email_sig_explain'] = 'Teks ini akan disertakan pada semua email yang dikirim forum';
$lang['Use_SMTP'] = 'Gunakan SMTP';
$lang['Use_SMTP_explain'] = 'Pilih ya jika Anda ingin mengirimkan email melalui server lain selain server lokal';
$lang['SMTP_server'] = 'Server SMTP';
$lang['SMTP_username'] = 'Username SMTP';
$lang['SMTP_username_explain'] = 'Hanya masukkan username jika diharuskan';
$lang['SMTP_password'] = 'Password SMTP';
$lang['SMTP_password_explain'] = 'Hanya masukkan password jika diharuskan';

$lang['Disable_privmsg'] = 'Pesan Pribadi';
$lang['Inbox_limits'] = 'Maksimum posting pada Inbox';
$lang['Sentbox_limits'] = 'Maksimum posting pada Sentbox';
$lang['Savebox_limits'] = 'Maksimum posting pada Savebox';

$lang['Cookie_settings'] = 'Setting Cookie'; 
$lang['Cookie_settings_explain'] = 'Detil berikut menentukan bagaimana penggunaan cookie pada browser. Umumnya nilai standar sudah cukup. Jika Anda membutuhkan pengaturan lain, lakukan dengan hati-hati. Kesalahan setting dapat menghalangi login user';
$lang['Cookie_domain'] = 'Domain Cookie';
$lang['Cookie_name'] = 'Nama Cookie';
$lang['Cookie_path'] = 'Path Cookie';
$lang['Cookie_secure'] = 'Secure Cookie';
$lang['Cookie_secure_explain'] = 'Jika server menggunakan SSL, gunakan pilihan ini, jika tidak biarkan pilihan Tidak';
$lang['Session_length'] = 'Lama session [ detik ]';


//
// Forum Management
//
$lang['Forum_admin'] = 'Administrasi Forum';
$lang['Forum_admin_explain'] = 'Dari panel ini Anda dapat menambah, menghapus, mengubah, menyusun ulang, mensinkronisasi kategori dan forum.';
$lang['Edit_forum'] = 'Ubah Forum';
$lang['Create_forum'] = 'Buat Forum';
$lang['Create_category'] = 'Buat Kategori';
$lang['Remove'] = 'Hapus';
$lang['Action'] = 'Action';
$lang['Update_order'] = 'Ubah Urutan';
$lang['Config_updated'] = 'Konfigurasi forum berhasil diubah';
$lang['Edit'] = 'Ubah';
$lang['Delete'] = 'Hapus';
$lang['Move_up'] = 'Naik';
$lang['Move_down'] = 'Turun';
$lang['Resync'] = 'Resync';
$lang['No_mode'] = 'Mode belum diset';
$lang['Forum_edit_delete_explain'] = 'Form berikut mengijinkan Anda untuk mengkustomisasi semua pilihan umum. Unutk konfigurasi user dan forum, gunakan link di samping kiri';

$lang['Move_contents'] = 'Pindahkan semua pesan';
$lang['Forum_delete'] = 'Hapus Forum';
$lang['Forum_delete_explain'] = 'Form berikut digunakan untuk menghapus forum (atau kategori) sekaligus menentukan dimana akan meletakkan isi topik yang ada.';

$lang['Status_locked'] = 'Terkunci';
$lang['Status_unlocked'] = 'Terbuka';
$lang['Forum_settings'] = 'Setting Umum Forum';
$lang['Forum_name'] = 'Nama Forum';
$lang['Forum_desc'] = 'Deskripsi';
$lang['Forum_status'] = 'Status Forum';
$lang['Forum_pruning'] = 'Auto-pruning';

$lang['prune_freq'] = 'Cek umur topik setiap';
$lang['prune_days'] = 'Hapus topik yang tak menerima pesan selama';
$lang['Set_prune_data'] = 'Anda telah menghidupkan fasilitas auto-prune untuk forum ini, tapi belum memasukkan frekuensi jumlah hari untuk proses ini. Harap kembali dan lakukan hal tersebut.';

$lang['Move_and_Delete'] = 'Pindah dan Hapus';

$lang['Delete_all_posts'] = 'Hapus Semua Posting';
$lang['Nowhere_to_move'] = 'Tak ada tempat untuk memindahkan';

$lang['Edit_Category'] = 'Ubah Kategori';
$lang['Edit_Category_explain'] = 'Gunakan form untuk mengubah nama kategori.';

$lang['Forums_updated'] = 'Info Forum dan Kategori berhasil diubah.';

$lang['Must_delete_forums'] = 'Anda harus menghapus semua forum sebelum dapat menghapus kategori ini';

$lang['Click_return_forumadmin'] = '%sKembali ke Admin Forum%s';


//
// Smiley Management
//
$lang['smiley_title'] = 'Pengubahan Smiley';
$lang['smile_desc'] = 'Di halaman ini Anda dapat menambah, menghapus, dan mengubah emoticon atau smiley yang digunakan di posting dan pesan pribadi user.';

$lang['smiley_config'] = 'Konfigurasi Smiley';
$lang['smiley_code'] = 'Kode Smiley';
$lang['smiley_url'] = 'File Gambar Smiley';
$lang['smiley_emot'] = 'Emotion Smiley';
$lang['smile_add'] = 'Smiley Baru';
$lang['Smile'] = 'Smile';
$lang['Emotion'] = 'Emotion';

$lang['Select_pak'] = 'Pilih File Pack (.pak)';
$lang['replace_existing'] = 'Ganti Smiley Standar';
$lang['keep_existing'] = 'Pertahankan Smiley Standar';
$lang['smiley_import_inst'] = 'Anda harus meng-unzip paket smiley dan mengupload semua file ke direktori Smiley pada instalasi Anda. Selanjutnya pilih informasi yang diperlukan pada form ini untuk mengimport paket smiley.';
$lang['smiley_import'] = 'Impor Paket Smiley';
$lang['choose_smile_pak'] = 'Pilih File Paket (.pak) Smiley';
$lang['import'] = 'Impor Smiley';
$lang['smile_conflicts'] = 'Yang harus dilakukan jika terjadi konflik';
$lang['del_existing_smileys'] = 'Hapus smiley standar sebelum import';
$lang['import_smile_pack'] = 'Impor Paket Smiley';
$lang['export_smile_pack'] = 'Buat Paket Smiley';
$lang['export_smiles'] = 'Untuk membuat paket smiley dari smiley yang terinstall saat ini, klik %sdi sini%s untuk mendownload file smiles.pak. Ubah nama file ini dilengkapi dengan ekstensi .pak. Selanjutknay buat zip file yang mengandung semua gambar smiley Anda ditambah file konfigurasi .pak.';

$lang['smiley_add_success'] = 'Smiley berhasil ditambahkan';
$lang['smiley_edit_success'] = 'Smiley berhasil diubah';
$lang['smiley_import_success'] = 'Paket Smiley berhasil diimpor!';
$lang['smiley_del_success'] = 'Smiley berhasil dihapus';
$lang['Click_return_smileadmin'] = '%sKembali ke Admin Smiley%s';


//
// User Management
//
$lang['User_admin'] = 'Admin User';
$lang['User_admin_explain'] = 'Di sini Anda dapat mengganti info user dan berbagai pilihan lain. Untuk mengubah hak akses user, silakan gunakan sistem hak akses user dan group.';

$lang['Look_up_user'] = 'Detil User';

$lang['Admin_user_fail'] = 'Pengubahan data user gagal.';
$lang['Admin_user_updated'] = 'Profil user berhasil diubah.';
$lang['Click_return_useradmin'] = '%sKembali ke Admin User%s';

$lang['User_delete'] = 'Hapus User';
$lang['User_delete_explain'] = 'Klik disini untuk menghapus user. Operasi ini tidak dapat dibatalkan.';
$lang['User_deleted'] = 'User berhasil dihapus.';

$lang['User_status'] = 'User Aktif';
$lang['User_allowpm'] = 'Dapat Menggunakan Pesan Pribadi';
$lang['User_allowavatar'] = 'Dapat Menampilkan Avatar';

$lang['Admin_avatar_explain'] = 'Disini Anda dapat melihat dan menghapus Avatar user.';

$lang['User_special'] = 'Field khusus admin';
$lang['User_special_explain'] = 'Field-field ini tidak dapat diubah oleh user. Disini Anda dapat mengatur status dan pilihan lain yang tidak dapat diubah oleh user.';


//
// Group Management
//
$lang['Group_administration'] = 'Admin Group';
$lang['Group_admin_explain'] = 'Dari panel ini Anda dapat mengelola semua usergroup. Anda dapat: menghapus, membuat, dan mengubah group yang ada. Anda dapat memilih moderator, mengubah mode terbuka/tertutup dari group, serta menentukan nama dan deskripsi group.';
$lang['Error_updating_groups'] = 'Pengubahan group gagal.';
$lang['Updated_group'] = 'The group was successfully updated';
$lang['Added_new_group'] = 'Group baru berhasil dibuat.';
$lang['Deleted_group'] = 'Group berhasil dihapus.';
$lang['New_group'] = 'Buat group baru';
$lang['Edit_group'] = 'Ubah group';
$lang['group_name'] = 'Nama group';
$lang['group_description'] = 'Deskripsi group';
$lang['group_moderator'] = 'Moderator group';
$lang['group_status'] = 'Status group';
$lang['group_open'] = 'Group Terbuka';
$lang['group_closed'] = 'Group Tertutup';
$lang['group_hidden'] = 'Group Tersembunyi';
$lang['group_delete'] = 'Hapus Group';
$lang['group_delete_check'] = 'Hapus Group';
$lang['submit_group_changes'] = 'Kirim Perubahan';
$lang['reset_group_changes'] = 'Reset Perubahan';
$lang['No_group_name'] = 'Nama group harus dimasukkan';
$lang['No_group_moderator'] = 'Moderator harus dimasukkan';
$lang['No_group_mode'] = 'Jenis group - terbuka/tertutup - harus ditentukan';
$lang['No_group_action'] = 'Tidak ada perintah diberikan';
$lang['delete_group_moderator'] = 'Hapus moderator group yang lama?';
$lang['delete_moderator_explain'] = 'Jika Anda mengubah moderator group, cek box ini untuk menghapus moderator yang lama. Jika tidak, user tersebut akan menjadi anggota regular.';
$lang['Click_return_groupsadmin'] = '%sKembali ke Admin Group%s.';
$lang['Select_group'] = 'Pilih group';
$lang['Look_up_group'] = 'Detil group';


//
// Prune Administration
//
$lang['Forum_Prune'] = 'Pembersihan Forum';
$lang['Forum_Prune_explain'] = 'Halaman ini akan menghapus semua topik yang belum menerima pesan selama waktu yang telah Anda definisikan. Jika Anda tidak memasukkan angka, makan semua topik akan dihapus. Perintah ini tidak akan menghapus topik yang memiliki polling yang masih berjalan ataupun topik yang bersifat pengumuman. Anda harus menghapus topik-topik ini secara manual.';
$lang['Do_Prune'] = 'Laksanakan';
$lang['All_Forums'] = 'Semua Forum';
$lang['Prune_topics_not_posted'] = 'Hapus topik tanpa balasan yang berumur sekian hari';
$lang['Topics_pruned'] = 'Topik dihapus';
$lang['Posts_pruned'] = 'Pesan dihapus';
$lang['Prune_success'] = 'Pembersihan forum berhasil';


//
// Word censor
//
$lang['Words_title'] = 'Sensor Kata';
$lang['Words_explain'] = 'Dari panel ini Anda dapat menambah, mengedit, dan menghapus kata yang otomatis akan disensor pada forum Anda. Sebagai tambahan, registrasi dengan nama user yang mengandung kata ini juga akan ditolak. Wildcards (*) diterima sebagai masukan kata. Misalnya *test* akan cocok dengan detestable, test* akan cocok dengan testing, *test akan cocok dengan detest.';
$lang['Word'] = 'Kata';
$lang['Edit_word_censor'] = 'Ubah sensor kata';
$lang['Replacement'] = 'Pengganti';
$lang['Add_new_word'] = 'Tambah kata baru';
$lang['Update_word'] = 'Ubah sensor kata';

$lang['Must_enter_word'] = 'Anda harus memasukkan suatu kata dan penggantinya';
$lang['No_word_selected'] = 'Tidak ada kata yang dipilih';

$lang['Word_updated'] = 'Sensor kata terpilih berhasil dilaksanakan';
$lang['Word_added'] = 'Penambahan sensor kata berhasil dilaksanakan';
$lang['Word_removed'] = 'Penghapusan sensor kata berhasil';

$lang['Click_return_wordadmin'] = '%sKembali ke Admin Sensor Kata%s';


//
// Mass Email
//
$lang['Mass_email_explain'] = 'Disini Anda dapat mengirimkan email baik ke semua user, atau user pada suatu group. Untuk melakukan ini, satu email akan dikirim ke email admin dengan BCC diset untuk semua penerima. Jika Anda mengirimkan email ke kelompok besar user, harap menunggu setelah mengirim dan jangan menghentikan loading halaman. Hal yang biasa bagi pengiriman massal untuk memakan waktu lama. Anda akan menerima notifikasi jika proses selesai';
$lang['Compose'] = 'Susun Email'; 

$lang['Recipients'] = 'Penerima'; 
$lang['All_users'] = 'Semua User';

$lang['Email_successfull'] = 'Pesan telah dikirim';
$lang['Click_return_massemail'] = '%sKembali ke form Email Massal%s';


//
// Ranks admin
//
$lang['Ranks_title'] = 'Admin Peringkat';
$lang['Ranks_explain'] = 'Form berikut dapat digunakan untuk menambah, mengubah, melihat, atau menghapus peringkat. Anda juga dapat menciptakan peringkat buatan yang dapat diterapkan ke user melalui fasilitas manajemen user';

$lang['Add_new_rank'] = 'Tambah peringkat baru';

$lang['Rank_title'] = 'Nama Peringkat';
$lang['Rank_special'] = 'Buat Jadi Peringkat Khusus';
$lang['Rank_minimum'] = 'Posting Minimum';
$lang['Rank_maximum'] = 'Posting Maksimum';
$lang['Rank_image'] = 'Gambar Peringkat(relatif ke direktori phpBB)';
$lang['Rank_image_explain'] = 'Gunakan ini untuk menentukan gambar kecil yang dihubungkan dengan peringkat';

$lang['Must_select_rank'] = 'Anda harus memilih salah satu peringkat';
$lang['No_assigned_rank'] = 'Tidak ada peringkat khusus';

$lang['Rank_updated'] = 'Peringkat berhasil diubah';
$lang['Rank_added'] = 'Peringkat berhasil ditambahkan';
$lang['Rank_removed'] = 'Peringkat berhasil dihapus';
$lang['No_update_ranks'] = 'Peringkat berhasil dihapus, tapi account user yang menggunakan peringkat ini tidak berhasil diubah. Anda harus seara manual mereset account-account ini';

$lang['Click_return_rankadmin'] = '%sKembali ke Admin Peringkat%s';


//
// Disallow Username Admin
//
$lang['Disallow_control'] = 'Kontrol Batasan Username';
$lang['Disallow_explain'] = 'Disini Anda dapat mengatur username yang tidak dapat dipilih oleh user. Batasan username dapat mengandung karakter wildcard *. Harap diperhatikan bahwa Anda tidak diijinkan untuk membuat batasan untuk nama yang telah dipilih user. Anda harus terlebih dulu menghapus nama tersebut sebelum melakukan pembatasan.';

$lang['Delete_disallow'] = 'Hapus';
$lang['Delete_disallow_title'] = 'Hapus Batasan Username';
$lang['Delete_disallow_explain'] = 'Anda dapat menghapus batasan username dengan memilih username dari daftar dan mengklik Kirim.';

$lang['Add_disallow'] = 'Tambah';
$lang['Add_disallow_title'] = 'Tambah batasan username';
$lang['Add_disallow_explain'] = 'Anda dapat memberi batasan username dengan menggunakan wildcard * untuk menyesuaikan dengan semua karakter';

$lang['No_disallowed'] = 'Batasan username tidak tersedia';

$lang['Disallowed_deleted'] = 'batasan username berhasil dihapus';
$lang['Disallow_successful'] = 'batasan username berhasil ditambah';
$lang['Disallowed_already'] = 'nama yang Anda masukkan tidak dapat dimasukkan menjadi batasan. Kata ini mungkin telah ada dalam daftar, terdapat dalam daftar kata yang disensor, atau sesuai dengan username yang sudah terdaftar';

$lang['Click_return_disallowadmin'] = '%sKembali ke Admin Batasan Username%s';


//
// Styles Admin
//
$lang['Styles_admin'] = 'Admin Style';
$lang['Styles_explain'] = 'Menggunaan fasilitas ini, Anda dapat menambah, menghapus, dan memanaje style (template dan theme) yang tersedia untuk user Anda';
$lang['Styles_addnew_explain'] = 'daftar berikut berisi semua theme yang tersedia bagi template yang telah Anda miliki. Item pada daftar belum terinstall ke database phpDB. Untuk menginstall theme, klik link install di samping entri';

$lang['Select_template'] = 'Pilih Template';

$lang['Style'] = 'Style';
$lang['Template'] = 'Template';
$lang['Install'] = 'Install';
$lang['Download'] = 'Download';

$lang['Edit_theme'] = 'Ubah Theme';
$lang['Edit_theme_explain'] = 'Pada form dibawah, Anda dapat mengubah setting untuk thema yang dipilih';

$lang['Create_theme'] = 'Buat Theme';
$lang['Create_theme_explain'] = 'Gunakan form dibawah ini untuk membuat theme baru untuk template yang dipilih. Sewaktu memasukkan warna (dimana Anda harus menggunakan notasi heksadesimal), Anda tidak boleh menuliskan awalan #, sehingga CCCCCC adalah valid dan #CCCCCC tidak valid.';

$lang['Export_themes'] = 'Ekspor Theme';
$lang['Export_explain'] = 'Pada panel ini, Anda dapat mengekspor data theme untuk template yang dipilih. Pilih template dari daftar dibawah dan script akan membuat file konfigurasi theme dan mencoba untuk menyimpannya pada direktori template yang dipilih. Jika tidak dapat disimpan, proses akan memberi pilihan untuk mendownload. Untuk dapat membuat file, Anda harus memberi akses write pada webserver untuk direkori template yang dipilih. Untuk informasi selengkapnya, baca User Guide phpBB 2.';

$lang['Theme_installed'] = 'theme telah berhasil diinstall';
$lang['Style_removed'] = 'style telah dihapus dari database. Untuk menghapus style dari sistem, Anda harus menghapus style yang bersangkutan dari direktori template.';
$lang['Theme_info_saved'] = 'info theme telah disimpan. Anda kini harus mengatur hak akses file theme_info.cfg (dan kalau perlu direktori tempate) menjadi read-onlye';
$lang['Theme_updated'] = 'theme berhasil diubah. Anda sekarang harus mengekspor setting theme baru';
$lang['Theme_created'] = 'Theme dibuat. Anda dapat mengeksport theme ke file konfigurasi theme untuk arsip atau untuk digunakan di tempat lain';

$lang['Confirm_delete_style'] = 'Yakin hapus style ini?';

$lang['Download_theme_cfg'] = 'exporter tidak dapat menulis file info theme. Klik tombol di bawah untuk mendownload ile ini dengan browser Anda. Setelah itu, Anda dapat mentransfer file ke direktori yang menyimpan file template. Anda kemudian dapat membuat paket file untuk distribusi atau penggunaan di tempat lain jika diinginkan';
$lang['No_themes'] = 'template yang dipilih tidak memiliki theme yang terkait. Untuk membuat theme baru, klik link Buat Baru pada panel di sebelah kiri';
$lang['No_template_dir'] = 'Tak dapat membuka direktori template. Mungkin tak bisa dibaca oleh webserver, mungkin juga tidak ada';
$lang['Cannot_remove_style'] = 'Anda tak dapat menghapus style karena saat ini merupakan standar forum. Harap ubah style standar dan coba lagi.';
$lang['Style_exists'] = 'nama style telah ada. Harap kembali dan pilih nama lain.';

$lang['Click_return_styleadmin'] = '%sKembali ke Admin Style%s';

$lang['Theme_settings'] = 'Setting Theme';
$lang['Theme_element'] = 'Elemen Theme';
$lang['Simple_name'] = 'Nama ringkas';
$lang['Value'] = 'Nilai';
$lang['Save_Settings'] = 'Simpan Setting';

$lang['Stylesheet'] = 'CSS Stylesheet';
$lang['Background_image'] = 'Background Image';
$lang['Background_color'] = 'Background Colour';
$lang['Theme_name'] = 'Theme Name';
$lang['Link_color'] = 'Link Colour';
$lang['Text_color'] = 'Text Colour';
$lang['VLink_color'] = 'Visited Link Colour';
$lang['ALink_color'] = 'Active Link Colour';
$lang['HLink_color'] = 'Hover Link Colour';
$lang['Tr_color1'] = 'Table Row Colour 1';
$lang['Tr_color2'] = 'Table Row Colour 2';
$lang['Tr_color3'] = 'Table Row Colour 3';
$lang['Tr_class1'] = 'Table Row Class 1';
$lang['Tr_class2'] = 'Table Row Class 2';
$lang['Tr_class3'] = 'Table Row Class 3';
$lang['Th_color1'] = 'Table Header Colour 1';
$lang['Th_color2'] = 'Table Header Colour 2';
$lang['Th_color3'] = 'Table Header Colour 3';
$lang['Th_class1'] = 'Table Header Class 1';
$lang['Th_class2'] = 'Table Header Class 2';
$lang['Th_class3'] = 'Table Header Class 3';
$lang['Td_color1'] = 'Table Cell Colour 1';
$lang['Td_color2'] = 'Table Cell Colour 2';
$lang['Td_color3'] = 'Table Cell Colour 3';
$lang['Td_class1'] = 'Table Cell Class 1';
$lang['Td_class2'] = 'Table Cell Class 2';
$lang['Td_class3'] = 'Table Cell Class 3';
$lang['fontface1'] = 'Font Face 1';
$lang['fontface2'] = 'Font Face 2';
$lang['fontface3'] = 'Font Face 3';
$lang['fontsize1'] = 'Font Size 1';
$lang['fontsize2'] = 'Font Size 2';
$lang['fontsize3'] = 'Font Size 3';
$lang['fontcolor1'] = 'Font Colour 1';
$lang['fontcolor2'] = 'Font Colour 2';
$lang['fontcolor3'] = 'Font Colour 3';
$lang['span_class1'] = 'Span Class 1';
$lang['span_class2'] = 'Span Class 2';
$lang['span_class3'] = 'Span Class 3';
$lang['img_poll_size'] = 'Ukuran gambar polling [px]';
$lang['img_pm_size'] = 'Ukuran status pesan pribadi [px]';


//
// Install Process
//
$lang['Welcome_install'] = 'Selamat Datang di Halaman Instalasi phpBB 2';
$lang['Initial_config'] = 'Konfigurasi Dasar';
$lang['DB_config'] = 'Konfigurasi Database';
$lang['Admin_config'] = 'Konfigurasi Admin';
$lang['continue_upgrade'] = 'Sewaktu Anda telah mendownload file konfigurasi ke mesin Anda, Anda dapat menekan tombol Teruskan Upgrade di bawah untuk melanjutkan proses upgrade. Harap tunggu proses upload file konfigurasi sampai prose upgrade selesai.';
$lang['upgrade_submit'] = 'Teruskan Upgrade';

$lang['Installer_Error'] = 'Terjadi error sewaktu instalasi';
$lang['Previous_Install'] = 'Instalasi sebelumnya terlacak';
$lang['Install_db_error'] = 'Terjadi error sewaktu mengubah database';

$lang['Re_install'] = 'Instalasi sebelumnya masih aktif. <br /><br />Jika Anda ingin menginstall ulang phpBB 2, Anda harus menekan tombol Ya di bawah. Harap diperhatikan bahwa hal tersebut akan menghapus semua data, tidak akan dibuat backup! Username dan password admin yang Anda pergunakan untuk login akan dibuat kembali setelah instalasi ulang. Selain itu, tidak ada setting lain yang tetap dipertahankan. <br /><br />Pikir dulu sebelum menekan Ya!';

$lang['Inst_Step_0'] = 'Terima kasih telah memilih phpBB 2. Untuk menyelesaikan proses instalasi, harap isi detil yang diminta di bawah. Harap dicatat bahwa database yang akan diinstall harus terlebih dulu ada. Jika Anda menginstall database yang menggunakan ODBC, misalnya MS Access, Anda harus terlebih dulu membuat DSN sebelum melanjutkan.';

$lang['Start_Install'] = 'Mulai Instalasi';
$lang['Finish_Install'] = 'Selesai Installation';

$lang['Default_lang'] = 'Bahasa standar';
$lang['DB_Host'] = 'Hostname/DSN Server/Database';
$lang['DB_Name'] = 'Nama database';
$lang['DB_Username'] = 'Username database';
$lang['DB_Password'] = 'Password database';
$lang['Database'] = 'Database Anda';
$lang['Install_lang'] = 'Pilih bahasa untuk Instalasi';
$lang['dbms'] = 'Tipe database';
$lang['Table_Prefix'] = 'Prefix untuk tabel pada database';
$lang['Admin_Username'] = 'Username Administrator';
$lang['Admin_Password'] = 'Password Administrator';
$lang['Admin_Password_confirm'] = 'Password Administrator [ Konfirmasi ]';

$lang['Inst_Step_2'] = 'Username admin telah dibuat. Pada saat ini instalasi dasar telah selesai. Anda kini akan diarahkan pada halaman yang menyediakan adminstrasi forum baru Anda. Harap yakinkan untuk mengecek Konfigurasi Umum dan lakukan perubahan jika diperlukan. Terima kasih untuk pilihan phpBB 2.';

$lang['Unwriteable_config'] = 'File config Anda tidak dapat ditulis pada saat ini. Salinan file konfigurasi akan didownload ke Anda setelah Anda menekan tombol dibawah. Anda harus mengupload file ini ke direktori yang sama dengan phpBB 2. Setelah selesai, Anda harus login dengan menggunakan username dan password yang Anda masukkan pada form sebelum ini, dan masuk ke Panel Admin. Terima kasih telah memilih phpBB 2.';
$lang['Download_config'] = 'Download Konfigurasi';

$lang['ftp_choose'] = 'Pilih Metode Download';
$lang['ftp_option'] = '<br />Karena extension FTP didukung pada versi PHP ini, Anda juga diberikan kemungkinan untuk pertama kali mencoba untuk melakiukan FTP file konfigurasi ke tempatnya.';
$lang['ftp_instructs'] = 'Anda telah memilih untuk melakukan FTP terhadap file secara otomatis. Harap masukkan informasi berikut untuk memfasilitasi proses. Catat bahwa path FTP harus path yang tepat seperti halnya kalau Anda melakukan proses yang sama dengan menggunakan klien FTP normal.';
$lang['ftp_info'] = 'Masukkan Info FTP Anda';
$lang['Attempt_ftp'] = 'Percobaan FTP File Konfigurasi';
$lang['Send_file'] = 'Kirim file pada saya dan saya akan melakukan FTP secara manual';
$lang['ftp_path'] = 'Path FTP terhadap phpBB 2';
$lang['ftp_username'] = 'Username FTP Anda';
$lang['ftp_password'] = 'Password FTP Anda';
$lang['Transfer_config'] = 'Mulai Transfer';
$lang['NoFTP_config'] = 'FTP file konfigurasi ke tempatnya tidak berhasil. Silakan download file dan FTP ketempatnya secara manual.';

$lang['Install'] = 'Install';
$lang['Upgrade'] = 'Upgrade';


$lang['Install_Method'] = 'Pilih metode instalasi';

$lang['Install_No_Ext'] = 'Konfigurasi php di server Anda tidak mendukung tipe database yang Anda pilih';

$lang['Install_No_PCRE'] = 'phpBB2 membutuhkan modul Perl-Compatible Regular Expressions (PCRE) untuk php yang mana tampaknya tidak didukung oleh konfigurasi php Anda!';

//
// That's all Folks!
// -------------------------------------------------

?>
