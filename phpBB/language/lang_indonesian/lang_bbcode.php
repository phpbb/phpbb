<?php
/***************************************************************************
 *                         lang_bbcode.php [english]
 *                            -------------------
 *   begin                : Wednesday Oct 3, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
 *
 *
 ***************************************************************************/

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
// To add an entry to your BBCode guide simply add a line to this file in this format:
// $faq[] = array("question", "answer");
// If you want to separate a section enter $faq[] = array("--","Block heading goes here if wanted");
// Links will be created automatically
//
// DO NOT forget the ; at the end of the line.
// Do NOT put double quotes (") in your BBCode guide entries, if you absolutely must then escape them ie. \"something\"
//
// The BBCode guide items will appear on the BBCode guide page in the same order they are listed in this file
//
// If just translating this file please do not alter the actual HTML unless absolutely necessary, thanks :)
//
// In addition please do not translate the colours referenced in relation to BBCode any section, if you do
// users browsing in your language may be confused to find they're BBCode doesn't work :D You can change
// references which are 'in-line' within the text though.
//
  
$faq[] = array("--","Pendahuluan");
$faq[] = array("Apa itu BBCode?", "BBCode adalah implementasi khusus dari HTML. Boleh tidaknya penggunaan BBCode dalam posting Anda di forum ditentukan oleh Administrator. Selain itu, Anda juga dapat mematikan BBCode untuk setiap posting Anda. BBCode mirip dengan HTML, tag diapit oleh kurung siku [ dan ], bukan &lt; dan &gt; serta memberikan kontrol yang lebih besar untuk apa dan bagaimana tampilan yang diinginkan. Tergantung pada template yang Anda gunakan, penambahan BBCode ke isi pesan Anda dibuat jauh lebih mudah dengan tampilan yang dapat diklik diatas bagian isi pesan pada form pesan. Walaupun demikian, Anda mungkin akan lebih mengerti dengan petunjuk ini.");

$faq[] = array("--","Format Teks");
$faq[] = array("Bagaimana membuat teks tebal, huruf miring, dan garis bawah", "BBCode menyediakan tag-tag yang membuat Anda dengan cepat mengganti model teks Anda. Hal ini dicapai dengan cara berikut: <ul><li>Untuk membuat suatu bagian teks menjadi tebal, apit dengan <b>[b][/b]</b> contohnya <br /><br /><b>[b]</b>Halo<b>[/b]</b><br /><br />akan menjadi <b>Halo</b>.</li><li>Untuk memberikan garis bawah, gunakan <b>[u][/u]</b>, sebagai contoh:<br /><br /><b>[u]</b>Selamat Pagi<b>[/u]</b><br /><br />akan menjadi <u>Selamat Pagi</u></li><li>Untuk membuat huruf miring (italic), gunakan <b>[i][/i]</b>, contohnya<br /><br />Ini <b>[i]</b>Luar Biasa!<b>[/i]</b><br /><br />akan menghasilkan Ini <i>Luar Biasa!</i></li></ul>");
$faq[] = array("Bagaimana mengganti warna atau besar teks", "Untuk mengganti warna atau besar teks, tag berikut dapat digunakan. Harap diingat bahwa tampilan akan tergantung dengan browser dan sistem yang Anda gunakan: <ul><li>Mengganti warna teks dilakukan dengan mengapit teks dengan <b>[color=][/color]</b>. Anda dapat menggunakan nama warna - dalam bahasa Inggris (misalnya red, blue, yellow, dll) atau alternatif triplet nilai warna heksadesimal, misalnya #FFFFFF, #000000. Sebagai contoh, untuk menghasilkan warna teks merah, Anda dapat menulis:<br /><br /><b>[color=red]</b>Halo!<b>[/color]</b><br /><br />atau<br /><br /><b>[color=#FF0000]</b>Halo!<b>[/color]</b><br /><br /> yang keduanya akan menghasilkan <span style=\"color:red\">Halo!</span></li><li>Mengubah besar teks dapat dilakukan dengan cara yang serupa dengan menggunakan <b>[size=][/size]</b>. Tag ini tergantung pada template yang Anda gunakan, tapi dianjurkan untuk menggunakan nilai numerik yang menunjukkan ukuran teks dalam pixel, dimulai dari 1 (sangat kecil hingga tidak akan bisa dilihat) hingga 29 (sangat besar). Contohnya:<br /><br /><b>[size=9]</b>KECIL<b>[/size]</b><br /><br />menghasilkan <span style=\"font-size:9px\">KECIL</span><br /><br />sedangkan:<br /><br /><b>[size=24]</b>BESAR!<b>[/size]</b><br /><br />akan menghasilkan <span style=\"font-size:24px\">BESAR!</span></li></ul>");
$faq[] = array("Dapatkah saya menggabungkan tag format?", "Tentu saja, contohnya untuk menari perhatian, Anda dapat menulis:<br /><br /><b>[size=18][color=red][b]</b>LIHAT!<b>[/b][/color][/size]</b><br /><br />yang akan menghasilkan <span style=\"color:red;font-size:18px\"><b>LIHAT!</b></span><br /><br />Kami tidak mengajurkan Anda menulis banyak teks yang seperti ini! Ingat bahwa tanggung jawab Anda, pengirim pesan, untuk meyakinka bahwa tag ditutup dengan benar. Contoh yang salah:<br /><br /><b>[b][u]</b>Ini salah<b>[/b][/u]</b>");

$faq[] = array("--","Mengutip dan menghasilkan teks fixed-width");
$faq[] = array("Mengutip teks pada tanggapan", "Ada dua cara untuk memasukkan teks kutipan, dengan referensi, atau tanpa referensi.<ul><li>Jika Anda menggunakan fungsi Kutip untuk membalas suatu pesan, Anda akan menyaksikan bahwa teks pesan ditambahkan pada kotak pesan diapit dengan <b>[quote=\"\"][/quote]</b>. Metode ini mengijinkan Anda untuk mengutip dengan referensi orang atau apapun yang ingin Anda masukkan! Contohnya untuk mengutip teks yang dikirim oleh Mr.Blobby, Anda akan memasukkan:<br /><br /><b>[quote=\"Mr. Blobby\"]</b>Teks Mr.Blobby akan ditulis disini<b>[/quote]</b><br /><br />Hasilnya akan otomatis menambahkan Mr.Blobby wrote: sebelum teks asli. Ingat bahwa Anda <b>harus</b> menambahkan \"\" mengapit nama yang Anda kutip. Ini tidak opsional.</li><li>Metode kedua mengijinkan Anda untuk langsung mengutip sesuatu. Untuk menggunakan metode ini, apit teks dengan tag <b>[quote][/quote]</b>. Sewaktu Anda melihat pesan tersebut, tampilannya akan berbentuk sederhana, Quote: sebelum teks asli.</li></ul>");
$faq[] = array("Menampilkan kode atau data fixed-width", "Jika ANda ingin menampilkan bagian program atau apapun yang memerlukan tampilan fixed width, misalnya font jenis Courier, Anda harus mengapit teks dengan tag <b>[code][/code]</b>, misalnya <br /><br /><b>[code]</b>echo \"Ini suatu coding\";<b>[/code]</b><br /><br />Semua format dalam tag <b>[code][/code]</b> akan dipertahankan.");

$faq[] = array("--","Menampilkan daftar");
$faq[] = array("Menampilkan daftar tanpa nomor", "BBCode menyediakan dua jenis daftar, tanpa nomor (unordered) dan bernomor (ordered). Prinsipnya sama dengan ekivalen HTMLnya. Daftar unordered menampilkan setiap item pada daftar secara berurut dengan indentasi berupa karakter bullet. Untuk membuat daftar unordered, gunakan <b>[list][/list]</b> dan definisikan tiap item dengan menggunakan <b>[*]</b>. Contohnya, untuk membuat daftar warna favorit, Anda menggunakan:<br /><br /><b>[list]</b><br /><b>[*]</b>Merah<br /><b>[*]</b>Biru<br /><b>[*]</b>Kuning<br /><b>[/list]</b><br /><br />yang akan menghasilkan daftar:<ul><li>Merah</li><li>Biru</li><li>Kuning</li></ul>");
$faq[] = array("Menampilkan daftar dengan nomor", "Tipe kedua daftar, daftar dengan nomor memberikan Anda kontrol apa yang ditampilkan sebelum setiap item. Untuk membuat daftar ordered, gunakan <b>[list=1][/list]</b> untuk nomor berbentuk angka atau <b>[list=a][/list]</b> untuk nomor berbentuk alfabet. Seperti halnya unordered list, item dinyatakan dengan <b>[*]</b>. Contoh:<br /><br /><b>[list=1]</b><br /><b>[*]</b>Pergi ke toko<br /><b>[*]</b>Beli komputer baru<br /><b>[*]</b>Mengomel sewaktu komputer rusak<br /><b>[/list]</b><br /><br />akan ditampilkan:<ol type=\"1\"><li>Pergi ke toko</li><li>Beli komputer baru</li><li>Mengomel sewaktu komputer rusak</li></ol>Untuk penomoron alfabetis, Anda menggunakan:<br /><br /><b>[list=a]</b><br /><b>[*]</b>Kemungkinan jawaban pertama<br /><b>[*]</b>Kemungkinan jawaban kedua<br /><b>[*]</b>Kemungkinan jawaban ketiga<br /><b>[/list]</b><br /><br />yang menghasilkan<ol type=\"a\"><li>Kemungkinan jawaban pertama</li><li>Kemungkinan jawaban kedua</li><li>Kemungkinan jawaban ketiga</li></ol>");

$faq[] = array("--", "Membuat link");
$faq[] = array("Link ke situs lain", "BBCode phpBB menyediakan berbagai cara untuk membuat URI, Uniform Resource Indicators atau lebih dikenal dengan URL.<ul><li>Metode pertama menggunakan tag <b>[url=][/url]</b>, apapun yang ditulis setelah tanda = akan berfungsi sebagai URL. Contohnya, untuk membuat link ke phpBB.com, ketik:<br /><br /><b>[url=http://www.phpbb.com/]</b>Kunjungi phpBB!<b>[/url]</b><br /><br />yang akan menghasilkan, <a href=\"http://www.phpbb.com/\" target=\"_blank\">Kunjungi phpBB!</a> Anda akan melihat bahwa link terbuka di window lain sehingga user dapat melanjutkan browsing di forum sesuai keinginannya.</li><li>Jika Anda ingin menampilkan alamat URL sendiri sebagai teks untuk link, gunakan:<br /><br /><b>[url]</b>http://www.phpbb.com/<b>[/url]</b><br /><br />Ini akan menghasilkan link, <a href=\"http://www.phpbb.com/\" target=\"_blank\">http://www.phpbb.com/</a></li><li>Tambahan lainnya, phpBB memiliki fitur yang disebut <i>Magic Links</i>, yang akan membuat semua yang valid sebagai URL ditampilkan sebagai link tanpa harus dibuatkan tag secara khusus, atau bahkan http://. Contoh, menulis www.phpbb.com dalam pesan Anda secara otomatis akan menghasilkan <a href=\"http://www.phpbb.com/\" target=\"_blank\">www.phpbb.com</a> sewaktu pesan ditampilkan.</li><li>Hal yang sama terjadi pada alamat email. Anda dapat secara explicit menulis:<br /><br /><b>[email]</b>no.one@domain.adr<b>[/email]</b><br /><br />yang akan menghasilkan <a href=\"emailto:no.one@domain.adr\">no.one@domain.adr</a> atau dapat langsung menuliskan no.one@domain.adr pada isi pesan yang selanjutnya akan otomatis dikonversi sewaktu dilihat.</li></ul>Seperti tag BBCode lainnya, URL dapat mengapit tag lain seperti <b>[img][/img]</b> (lihat bagian selanjutnya), <b>[b][/b]</b>, dll. Seperti juga tag lain, Anda bertanggung jawab untuk membuka dan menutup tag dengan benar. Contoh:<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/url][/img]</b><br /><br /> <u>tidak tepat</u> dan dapat menyebabkan pesan Anda dapat dihapus. Jadi berhati-hatilah.");

$faq[] = array("--", "Menampilkan gambar pada pesan");
$faq[] = array("Menambahkan gambar pada isi pesan", "BBCode phpBB menggunakan suatu tag untuk menampilkan gambar pada posting Anda. Dua hal sangat penting yang harus diingat waktu menggunakan tag ini adalah; banyak user yang tidak senang terlalu banyak gambar pada pesan dan kedua, gambar yang Anda masukkan harus tersedia di internet (gambar ini tidak boleh hanya tersedia pada komputer Anda sendiri contohnya, kecuali jika Anda menjalankan webserver sendiri!). Saat ini tidak tersedia fasilitas untuk menyimpan gambar secara lokal di phpBB (semua masalah ini diharapkan akan diselesaikan pada rilis selanjutnya dari phpBB). Untuk menampilkan gambar, Anda harus mengapit URL yang mengarah ke gambar tersebut dengan tag <b>[img][/img]</b>. Contohnya <br /><br /><b>[img]</b>http://www.phpbb.com/images/mainlogo.gif<b>[/img]</b><br /><br />Seperti telah dijelaskan pada bagian URL di atas, Anda dapat mengapit gambar dengan tag <b>[url][/url]</b> jika diinginkan, contohnya <br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/mainlogo.gif<b>[/img][/url]</b><br /><br /> akan menghasilkan <br /><br /><a href=\"http://www.phpbb.com/\" target=\"_blank\"><img src=\"http://www.phpbb.com/images/mainlogo.gif\" border=\"0\" alt=\"\" /></a><br />");

$faq[] = array("--", "Hal-hal lain");
$faq[] = array("Dapatkan saya menambahkan tag sendiri?", "Tidak. Sayangnya fasilitas ini tidak tersedia pada phpBB 2.0. Kami sedang mencari cara untuk memberikan kesempatan mengkustomisasi tag BBCode pada versi mayor selanjutnya");

//
// This ends the BBCode guide entries
//

?>