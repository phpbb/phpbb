<?php
/***************************************************************************
 *                         lang_bbcode.php [Turkish]
 *                            -------------------
 *   begin                : Wednesday Oct 3, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: lang_bbcode.php,v 1.3 2001/12/18 01:53:26 psotfx Exp $
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

//
// Translation by:
//
// Onur Turgay (postexitus)  :: onurturgay@isnet.net.tr :: http://welcome.to/aaal2000
// Erdem Corapcioglu (erdem) :: erdem@smtg.org         :: http://www.smtg.org
//
// For questions and comments use: onurturgay@isnet.net.tr
//


$faq[] = array("--","Giriþ");
$faq[] = array("BBCode nedir?", "BBCode HTML'in özel bir uygulamasýdýr. Forum'a yazdýðýnýz mesajlarda BBCode kullanabilme imkanýný pano yöneticisi saptar. Ayrýca mesaj gönderme formundaki seçenekler sayesinde dilediðiniz mesajlarda BBCode'ý iptal etmeniz mümkündür. BBCode, HTML'e benzer tarzdadýr fakat tag'ler &lt; ve &gt; yerine köþeli parantez içine alýnýr. Ayrýca nelerin nasýl görüntülendiði daha iyi kontrol edilebilir. Mesajlarýnýza BBCode eklemek için mesaj gövdesi üzerinde bulunan araç çubuðunu kullanmanýz iþi çok daha kolaylaþtýrýr (araç çubuðu görünümü kullandýðýnýz tema'ya baðlýdýr). Ayrýca alttaki rehberi faydalý bulabilirsiniz.");

$faq[] = array("--","Metin Biçimini Deðiþtirme");
$faq[] = array("Kalýn, italik veya altýçizili yazýlar nasýl yazýlýr?", "BBCode, metnin temel biçimlemesini kolayca deðiþtirmenizi saðlayan tag'lere sahiptir. Bunu gerçekleþtirmek için þu yöntemler kullanýlýr: <ul><li>Metnin belirli bir kýsmýný kalýn harflerle görüntülemek için <b>[b][/b]</b> tag'leri içine alýn, örn. <br /><br /><b>[b]</b>Hello<b>[/b]</b><br /><br />yazýlýnca <b>Hello</b> olarak görüntülenir.</li><li>Altýçizili yazýlar için <b>[u][/u]</b> kullanýn, örn.: <br /><br /><b>[u]</b>Good Morning<b>[/u]</b><br /><br />yazýlýnca <u>Good Morning</u> olarak görüntülenir.</li><li>Metni italik yazmak için <b>[i][/i]</b> kullanýn, örn. <br /><br />This is <b>[i]</b>Great!<b>[/i]</b><br /><br />yazýlýnca sonuç This is <i>Great!</i> olur.</li></ul>");
$faq[] = array("Yazýlarýn rengi veya boyutu nasýl deðiþtirilir?", "Yazýlarýn renk veya boyutunu deðiþtirmek için alttaki tag'ler kullanýlabilir. Elde edilen sonuç, kullanýlan browser ve bilgisayar sistemine göre deðiþebilir, aklýnýzda bulunsun: <ul><li>Yazýlarýn rengi, metni <b>[color=][/color]</b> tag'leri içine alarak deðiþtirilir. Belirli ingilizce renk isimlerini (örn. red, blue, yellow vs.) veya alternatif olarak 16 tabanlý sayý sisteminde kodlanmýþ üç rakamlý renk kodunu yazabilirsiniz (örn. #FFFFFF, #000000). Metni örneðin kýrmýzý harflerle yazmak için:<br /><br /><b>[color=red]</b>Hello!<b>[/color]</b><br /><br />veya<br /><br /><b>[color=#FF0000]</b>Hello!<b>[/color]</b><br /><br />ayný þekilde görüntülenir: <span style=\"color:red\">Hello!</span></li><li>Karakterlerin boyutunu benzer þekilde <b>[size=][/size]</b> kullanarak deðiþtirebilirsiniz. Bu tag kullandýðýnýz tema'ya baðlýdýr. Karakterlerin pixel olarak boyutunu yazmanýz önerilir. Bu rakam 1 ile baþlayýp (gözle görülmeyecek kadar küçük), en fazla 29 (çok büyük) olabilir. Örnek:<br /><br /><b>[size=9]</b>KÜÇÜK<b>[/size]</b><br /><br />genelde þu sonucu verir: <span style=\"font-size:9px\">KÜÇÜK</span><br /><br />öte yandan:<br /><br /><b>[size=24]</b>BÜYÜK!<b>[/size]</b><br /><br /><span style=\"font-size:24px\">BÜYÜK!</span> sonucunu verir.</li></ul>");
$faq[] = array("Biçimlendirme tag'lerini karýþtýrabilir miyim?", "Evet, mesela dikkati çekmek için þöyle yazabilirsiniz:<br /><br /><b>[size=18][color=red][b]</b>DÝKKAT!<b>[/b][/color][/size]</b><br /><br />Bu yazý þu þekilde görüntülenir: <span style=\"color:red;font-size:18px\"><b>DÝKKAT!</b></span><br /><br />Uzun metinleri bu þekilde yazmamanýzý öneririz! Unutmayýn ki, tag'lerin düzgün bir þekilde kapatýlmasýný temin etmek, metni gönderen kiþi olarak sizin görevinizdir. Örneðin bu þekilde yazmak yanlýþtýr: <br /><br /><b>[b][u]</b>Tag'ler hatalý kapatýlmýþ<b>[/b][/u]</b>");

$faq[] = array("--","Alýntý ile Cevap ve Eþaralýklý Yazýtipi");
$faq[] = array("Alýntý ile cevap yazma", "Bir metinden alýntý yapmanýn iki ayrý yöntemi vardýr: kaynak vererek veya vermeyerek.<ul><li>Bir mesaja cevap vermek için Alýnýtý ile Cevap komutunu kullanýrsanýz, orijinal mesajýn kendi mesajýnýza <b>[quote=\"\"][/quote]</b> tag'leri arasýnda eklendiðini göreceksiniz. Bu yöntem, bir þahsý veya seçeceðiniz herhangi baþka bir yeri kaynak vererek yanýt yazmanýzý saðlar. Örneðin Ali isminde bir þahsýn yazdýklarýný iktibas etmek için þu þekilde yazmanýz gerek: <br /><br /><b>[quote=\"Ali\"]</b>Ali'nin yazdýðý yazýlar...<b>[/quote]</b><br /><br />Sonuçta iktibas edilen kýsmýn önüne otomatik olarak Ali wrote: yazýlýr. Alýntý yaptýðýnýz þahsýn ismini týrnak iþaretleri \"\" içine almayý unutmayýn, týrnak iþaretleri kullanmanýz <b>þart</b>.</li><li>Ýkinci yöntem, kaynak vermeden alýntý yapmanýzý saðlar. Ýlgili bölümü <b>[quote][/quote]</b> tag'leri içine almanýz yeterli. Bu bölümün önünde Quote: yazýsýný göreceksiniz.</li></ul>");
$faq[] = array("Kaynak yazýlým veya eþaralýklý yazýtipiyle görüntüleme", "Bir programlama dilinde yazýlmýþ kaynak yazýlým veya eþaralýklý yazýtipi (örn. Courier) gerektiren herhangi bir metni görüntülemek için, ilgili kýsmý <b>[code][/code]</b> tag'leri içine almalýsýnýz. Örn.: <br /><br /><b>[code]</b>echo \"This is some code\";<b>[/code]</b><br /><br /><b>[code][/code]</b> tag'leri arasýna yazýlan tüm biçimleme tag'leri (örn. [i], [b] gibi) iptal edilir.");

$faq[] = array("--","Liste Yaratma");
$faq[] = array("Madde imiyle liste", "BBCode rakamsýz (madde imiyle) ve rakamlý olmak üzere iki türlü liste destekler. Bu listeler aslýnda HTML listelerine eþittir. Rakamsýz liste, her maddeyi bir madde imiyle beraber satýr baþýný biraz girintilenmiþ olarak görüntüler. Rakamsýz bir liste hazýrlamak için <b>[list][/list]</b> tag'lerini kullanýn ve her satýrýn baþýna <b>[*]</b> yazýn. Örn. sevdiðiniz renklerin bir listesini þu þekilde hazýrlayabilirsiniz:<br /><br /><b>[list]</b><br /><b>[*]</b>Kýrmýzý<br /><b>[*]</b>Mavi<br /><b>[*]</b>Sarý<br /><b>[/list]</b><br /><br />Sonuç olarak þu listeyi göreceksiniz:<ul><li>Kýrmýzý</li><li>Mavi</li><li>Sarý</li></ul>");
$faq[] = array("Rakamlý liste", "Ýkinci liste türü olan rakamlý listeyle, her satýr baþýnda görülen rakamý kontrol edebilirsiniz. Rakamlara göre sýralanmýþ bir liste için <b>[list=1][/list]</b> kullanmanýz gerek. Alternatif olarak alfabe'ye göre sýralanmýþ bir liste için <b>[list=a][/list]</b> tag'lerini kullanabilirsiniz. Rakamsýz listelerde olduðu gibi, her maddeyi <b>[*]</b> ile iþaretlemeniz gerek. Örneðin:<br /><br /><b>[list=1]</b><br /><b>[*]</b>Maðazaya git<br /><b>[*]</b>Yeni bilgisayar al<br /><b>[*]</b>Eve götür<br /><b>[/list]</b><br /><br />þu þekilde görüntülenir:<ol type=\"1\"><li>Maðazaya git</li><li>Yeni bilgisayar al</li><li>Eve götür</li></ol>Öte yandan alfabeye göre sýralanmýþ bir listeyi þu þekilde yazmanýz gerekir:<br /><br /><b>[list=a]</b><br /><b>[*]</b>Birinci seçenek<br /><b>[*]</b>Ýkinci seçenek<br /><b>[*]</b>Üçüncü seçenek<br /><b>[/list]</b><br /><br />Sonuç:<ol type=\"a\"><li>Birinci seçenek</li><li>Ýkinci seçenek</li><li>Üçüncü seçenek</li></ol>");

$faq[] = array("--", "Link (kýsayol) Yaratma");
$faq[] = array("Ayrý bir siteye link verme", "BBCode link (URL) yaratmak için deðiþik yöntemleri destekler.<ul><li>Birinci yöntem <b>[url=][/url]</b> tag'iyledir. = iþaretinin arkasýna yazýlanlar link olarak çalýþýr. Örneðin phpBB.com'a link vermek için þu þekilde yazýn:<br /><br /><b>[url=http://www.phpbb.com/]</b>phpBB'yi ziyaret edin!<b>[/url]</b><br /><br />Sonuçta þu linki göreceksiniz: <a href=\"http://www.phpbb.com/\" target=\"_blank\">phpBB'yi ziyaret edin!</a> Bu linki týklayýnca ayrý bir pencere açýlýr. Böylece kullanýcý forum'u gezmeye devam edebilir.</li><li>Link adresinin gösterilmesini istiyorsanýz, þu þekildede yazabilirsiniz:<br /><br /><b>[url]</b>http://www.phpbb.com/<b>[/url]</b><br /><br />Sonuçta þu linki göreceksiniz: <a href=\"http://www.phpbb.com/\" target=\"_blank\">http://www.phpbb.com/</a></li><li>phpBB ayrýca <i>Sihirli Linkler</i> denen bir iþleme sahip. Bunun sayesinde, kurallara uygun bir þekilde yazýlan her link adresi otomatik olarak link'e çevrilir, herhangi bir tag, hatta http:// yazmanýza gerek kalmaz. Örn. www.phpbb.com yazýnca, izlenim sayfasýnda otomatik olarak <a href=\"http://www.phpbb.com/\" target=\"_blank\">www.phpbb.com</a> olarak görüntülenir.</li><li>Ayný iþlem email adresleri için uygulanýr. Dilerseniz özel olarak bir adres belirleyebilirsiniz, örn.:<br /><br /><b>[email]</b>no.one@domain.adr<b>[/email]</b><br /><br />yazýlýnca þu þekilde görüntülenir: <a href=\"emailto:no.one@domain.adr\">no.one@domain.adr</a> Veya basitçe no.one@domain.adr yazabilirsiniz ve mesajýnýz görüntülendiðinde bu kýsým otomatik olarak link'e çevrilir.</li></ul>Bütün BBCode tag'leri gibi, link adreslerinide diðer tag'lerin içine alabilirsiniz, örn. <b>[img][/img]</b> (bir sonraki madde bakýn), <b>[b][/b]</b>, vs. Biçimleme tag'lerinde olduðu gibi, tag'lerin düzgün bir þekilde sýrasýyla kapatýlmasýný kendiniz saðlamalýsýnýz, örn.:<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/url][/img]</b><br /><br />doðru <u>deðildir</u> ve hatta mesajýnýzýn


 silinmesine yol açabilir, bu konuda dikkatli olmanýz gerek.");

$faq[] = array("--", "Mesajlarda Resim Görüntüleme");
$faq[] = array("Bir mesaja resim ekleme", "BBCode mesajlarýnýza resim eklemek için bir tag'e sahiptir. Bu tag'i kullanýrken iki önemli noktayý dikkate almanýz gerek: birçok kullanýcý mesajlarda çok sayýda resmin görüntülenmesini hoþ karþýlamýyor. Ayrýca kullanmak istediðiniz resme internet üzerinden ulaþýlabilmeli (örn. bu resmin kendi bilgisayarýnýzda bulunmasý yeterli deðildir). Þu anda phpBB üzerinden resim kaydetme imkaný yoktur (bu konular muhtemelen phpBB'nin bir sonraki sürümünde ele alýnacak). Bir resmi görüntülemek için, resmin adresini <b>[img][/img]</b> tag'leri içine almalýsýnýz. Örn.:<br /><br /><b>[img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img]</b><br /><br />Bir önceki maddede belirtildiði gibi, resmi dilerseniz <b>[url][/url]</b> tag'leri içine alabilirsiniz. Örn.:<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img][/url]</b><br /><br />yazýnca þu sonucu verir:<br /><br /><a href=\"http://www.phpbb.com/\" target=\"_blank\"><img src=\"templates/subSilver/images/logo_phpBB_med.gif\" border=\"0\" alt=\"\" /></a><br />");

$faq[] = array("--", "Diðer Konular");
$faq[] = array("Kendi tag'lerimi ekleyebilir miyim?", "Hayýr, maalesef phpBB 2.0 sürümünde böyle bir imkan yok. Bir sonraki sürümde özelleþtirilmiþ BBCode tag'leri sunmayý planlýyoruz.");

//
// This ends the BBCode guide entries
//

?>