<?php
/***************************************************************************
 *                            lang_admin.php [Slovak]
 *                            ----------------------
 *     characterset         : Windows-1250
 *     begin                : 09-08-2002
 *     copyright            : (c) 2002 The phpBB CZ Group
 *     translation          : kolenkas@stonline.sk
 *     convert2iso          : Kukymann
 *     www                  : 
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

//
// Format is same as lang_main
//

//
// Modules, this replaces the keys used
// in the modules[][] arrays in each module file
//
$lang['General'] = "Všeobecné";
$lang['Users'] = "Uívatelia";
$lang['Groups'] = "Skupiny";
$lang['Forums'] = "Fórum";
$lang['Styles'] = "Štıly";

$lang['Configuration'] = "Konfigurácia";
$lang['Permissions'] = "Povolenia";
$lang['Manage'] = "Administrácia";
$lang['Disallow'] = "Nepovolené mená";
$lang['Prune'] = "Preèistenie";
$lang['Mass_Email'] = "Hromadnı e-mail";
$lang['Ranks'] = "Hodnotenia";
$lang['Smilies'] = "Smajlíky (emotikony)";
$lang['Ban_Management'] = "Zakázanie vstupu";
$lang['Word_Censor'] = "Cenzúra slov";
$lang['Export'] = "Exportova";
$lang['Create_new'] = "Vytvori";
$lang['Add_new'] = "Prida";
$lang['Backup_DB'] = "Zálohova databázu";
$lang['Restore_DB'] = "Obnovi databázu";


//
// Index
//
$lang['Admin'] = "Administrácia";
$lang['Not_admin'] = "Nemáte oprávnenie k administrácii tohto fóra";
$lang['Welcome_phpBB'] = "Vitajte na phpBB";
$lang['Admin_intro'] = "Ïakujeme, e ste si zvolil(a) phpBB ako riešenie pre Vaše fórum. Táto stránka slúí k rıchlemu zobrazeniu rôznych štatistík Vášho fóra. Pokia¾ sa budete chcie vráti spä na túto stránku kliknite na odkaz <u>Obsah administrácie</u> v ¾avom paneli. Pre návrat na obsah Vášho fóra, kliknite na logo fóra, ktoré je umiestnené tie na ¾avom paneli. Ostatné odkazy na ¾avom paneli tejto stránky Vás dovedú k jednotlivım polokám moného nastavenia fóra pod¾a Vašich poiadaviek, kadá stránka obsahuje návod ako poui danú funkciu.";

$lang['Main_index'] = "Obsah fóra";
$lang['Forum_stats'] = "Štatistiky fóra";
$lang['Admin_Index'] = "Obsah administrácie";
$lang['Preview_forum'] = "Náh¾ad na fórum";

$lang['Click_return_admin_index'] = "Kliknite %ssem%s pre návrat na obsah administrácie";

$lang['Statistic'] = "Štatistiky";
$lang['Value'] = "Hodnota";
$lang['Number_posts'] = "Poèet príspevkov";
$lang['Posts_per_day'] = "Príspevkov za deò";
$lang['Number_topics'] = "Poèet tém";
$lang['Topics_per_day'] = "Tém za deò";
$lang['Number_users'] = "Poèet uívate¾ov";
$lang['Users_per_day'] = "Uívate¾ov za deò";
$lang['Board_started'] = "Fórum spustené";
$lang['Avatar_dir_size'] = "Ve¾kos adresára s obrázkami postavièiek";
$lang['Database_size'] = "Ve¾kos databázy";
$lang['Gzip_compression'] ="GZIP kompresia";
$lang['Not_available'] = "Nedostupné";

$lang['ON'] = "Áno"; // This is for GZip compression
$lang['OFF'] = "Nie";


//
// DB Utils
//
$lang['Database_Utilities'] = "Databázové nástroje";

$lang['Restore'] = "Obnovenie";
$lang['Backup'] = "Zálohovanie";
$lang['Restore_explain'] = "Táto funkcia je urèená k úplnému obnoveniu všetkıch databázovıch tabuliek phpBB fóra z uloenıch súborov. Ak to Váš server podporuje, môete poui GZIP komprimované textové súbory a tie potom budú automaticky dekomprimované. <b>POZOR</b> Tımto budú prepísané všetky existujúce dáta. Obnovenie potrebuje dlhší èas na spracovanie, preto prosím neodchádzajte z tejto stránky pokia¾ nebude všetko dokonèené.";
$lang['Backup_explain'] = "Táto funkcia je urèená na kompletnú zálohu dát phpBB fóra. Ak pouívate niektoré ïalšie tabu¾ky spoloène s phpBB databázou, doporuèujeme ich tie zazálohova, zadajte preto prosím názvy tabuliek a odde¾te ich odde¾ovaèom (,). Ak to Váš server podporuje, môete poui GZIP kompresiu dát pre zmenšenie ve¾kosti súborov pred ich stiahnutím do Vášho poèítaèa.";

$lang['Backup_options'] = "Nastavenie zálohy";
$lang['Start_backup'] = "Spusti zálohovanie";
$lang['Full_backup'] = "Kompletná záloha";
$lang['Structure_backup'] = "Zálohova len štruktúru";
$lang['Data_backup'] = "Zálohova len dáta";
$lang['Additional_tables'] = "Ïalšie tabu¾ky";
$lang['Gzip_compress'] = "GZIP kompresia súborov";
$lang['Select_file'] = "Zvoli súbor";
$lang['Start_Restore'] = "Spusti obnovenie";

$lang['Restore_success'] = "Databáza bola úspešne obnovená.<br><br>Vaše fórum by teraz malo by v stave pred vykonaním zálohy.";
$lang['Backup_download'] = "Prosím poèkajte na zaèiatok sahovania";
$lang['Backups_not_supported'] = "¼utujem, ale zálohovanie databázy nie je v súèasnej dobe vo vešom databázovom systéme podporované";

$lang['Restore_Error_uploading'] = "Vyskytla sa chyba pri nahrávaní súboru zálohy";
$lang['Restore_Error_filename'] = "Vyskytol sa problém s menom súboru, skúste iné";
$lang['Restore_Error_decompress'] = "Nebolo moné dekomprimova GZIP súbor, pouite textovı súbor";
$lang['Restore_Error_no_file'] = "Nebol nahratı iadny súbor";


//
// Auth pages
//
$lang['Select_a_User'] = "Zvoli uívate¾a";
$lang['Select_a_Group'] = "Zvoli skupinu";
$lang['Select_a_Forum'] = "Zvoli fórum";
$lang['Auth_Control_User'] = "Uívate¾ské oprávnenia";
$lang['Auth_Control_Group'] = "Oprávnenia skupiny";
$lang['Auth_Control_Forum'] = "Oprávnenia fóra";
$lang['Look_up_User'] = "Zvoli uívate¾a";
$lang['Look_up_Group'] = "Zvoli skupinu";
$lang['Look_up_Forum'] = "Zvoli fórum";

$lang['Group_auth_explain'] = "Tu môete meni oprávnenia a priradi moderovanie skupine uívate¾ov. Nezabudnite, aby pred zmenou oprávnenia  skupina oprávnenıch mala stále povolenı vstup uívate¾a na fórum.";
$lang['User_auth_explain'] = "Tu môete meni oprávnenia a priradi moderovánie zvolenému uívate¾ovi. Nezabudnite pred zmenou oprávnenia,,aby skupina oprávnenıch mala stále povolenı vstup uívate¾a na fórum.";
$lang['Forum_auth_explain'] = "Tu môete nastavi úroveò zabezpeèenia fóra. Môete zvoli základnı alebo rozšírenı mód pre túto èinnos. Rozšírenı mód ponúka ove¾a väèšiu škálu moností pre nastavenie fóra. Pamätajte, e pred zmenou zabezpeèenia fóra by sa na fóre nemali vykonáva iné operácie.";

$lang['Simple_mode'] = "Základnı reim";
$lang['Advanced_mode'] = "Rozšírenı reim";
$lang['Moderator_status'] = "Moderátor";

$lang['Allowed_Access'] = "Prístup povolenı";
$lang['Disallowed_Access'] = "Prístup zamietnutı";
$lang['Is_Moderator'] = "Je moderátorom";
$lang['Not_Moderator'] = "Nie je moderátorom";

$lang['Conflict_warning'] = "Varovanie, autorizaènı konflikt";
$lang['Conflict_access_userauth'] = "Tento uívate¾ má poadované prístupové práva k tomuto fóre cez èlenstvo v skupine. Môete povoli oprávnenie skupine alebo odstráni tohto uívate¾a zo skupiny pre úplné zabránenie poadovanıch prístupovıch práv.";
$lang['Conflict_mod_userauth'] = "Tento moderátor má poadované práva pre toto fórum cez èlenstvo v skupine. Môete povoli oprávnenie skupine alebo odstráni tohto uívate¾a zo skupiny pre úplné zabránenie poadovanıch prístupovıch práv.";
$lang['Conflict_access_groupauth'] = "Nasledovnı uívate¾ (uívatelia) majú poadované práva pre toto fórum cez ich nastavené oprávnenia. Môete povoli oprávnenie skupine alebo odstráni tohto uívate¾a zo skupiny pre úplné zabránenie poadovanıch prístupovıch práv.";
$lang['Conflict_mod_groupauth'] = "Následovnı uívate¾ (uívatelia) majú poadované práva pre toto fórum cez ich nastavené oprávnenia. Môete povoli oprávnenia skupine alebo odstráni tohto uívate¾a zo skupiny pre úplné zabránenie poadovanıch prístupovıch práv.";

$lang['Public'] = "Verejnı";
$lang['Private'] = "Súkromnı";
$lang['Registered'] = "Registrovanı";
$lang['Administrators'] = "Administrátor";
$lang['Hidden'] = "Skrytı";

// These are displayed in the drop down boxes for advanced
// mode forum auth, try and keep them short!
$lang['Forum_ALL'] = "Všetci";
$lang['Forum_REG'] = "Registrovaní";
$lang['Forum_PRIVATE'] = "Súkromné";
$lang['Forum_MOD'] = "Moderátor";
$lang['Forum_ADMIN'] = "Administrátor";

$lang['View'] = "Zobrazi";
$lang['Read'] = "Èíta";
$lang['Post'] = "Odosla";
$lang['Reply'] = "Odpoveda";
$lang['Edit'] = "Upravi";
$lang['Delete'] = "Odstráni";
$lang['Sticky'] = "Dôleité";
$lang['Announce'] = "Oznámenia";
$lang['Vote'] = "Hlasovania";
$lang['Pollcreate'] = "Hlas pridanı";

$lang['Permissions'] = "Oprávnenia";
$lang['Simple_Permission'] = "Základné oprávnenie";

$lang['User_Level'] = "Uívate¾ská úroveò";
$lang['Auth_User'] = "Uívate¾";
$lang['Auth_Admin'] = "Administrátor";
$lang['Group_memberships'] = "Èlenstvo uívate¾skej skupiny";
$lang['Usergroup_members'] = "Táto skupina má nasledovnıch èlenov";

$lang['Forum_auth_updated'] = "Oprávnenia fóra aktualizované";
$lang['User_auth_updated'] = "Uívate¾ské oprávnenia aktualizované";
$lang['Group_auth_updated'] = "Oprávnenia skupiny aktualizované";

$lang['Auth_updated'] = "Oprávnenie bolo aktualizované";
$lang['Click_return_userauth'] = "Kliknite %ssem%s pre návrat do uívate¾ského oprávnenia";
$lang['Click_return_groupauth'] = "Kliknite %ssem%s pre návrat do oprávnenia skupiny";
$lang['Click_return_forumauth'] = "Kliknite %ssem%s pre návrat do oprávnenia fóra";


//
// Banning
//
$lang['Ban_control'] = "Zakázanie vstupu";

$lang['Ban_explain'] = "Tu môete zakáza vstup zvolenım uívate¾om. Môete zakáza konkrétneho uívate¾a alebo rozsah IP adries alebo meno poèítaèa. Touto metódou ochránite Vaše fórum proti vstupu neiadúcich uívate¾ov na stránky fóra. Proti registrácii uívate¾a pod inım menom môete zakáza jeho e-mailovú adresu.";
$lang['Ban_explain_warn'] = "Dávajte si prosím pozor pri zadávaní rozsahu IP adries èi sú všetky adresy od zaèiatku do konca v zozname. Doporuèuje sa, aby bol zoznam uloenıch IP adries v databáze èo najmenší, preto sa pokúste radšej poui znak \"*\" pre špecifikáciu namiesto zadávania rozsahu IP adries. Pokia¾ je aj tak porebné zada rozsah IP adries, pokúste sa aby bol zoznam èo najkratší.";

$lang['Select_username'] = "Zvo¾te uívate¾a";
$lang['Select_ip'] = "Zvo¾te IP";
$lang['Select_email'] = "Zvo¾te e-mailovú adresu";

$lang['Ban_username'] = "Zakázánie vstupu zadanım uívate¾om";
$lang['Ban_username_explain'] = "Ak chcete prida do zakázanıch niektorého uívate¾a, zadajte tu jeho meno, prípadne ho vyh¾adajte v zozname registrovanıch uívate¾ov.";

$lang['Ban_IP'] = "Zakázanie vstupu pod¾a IP adresy alebo mena poèítaèa";
$lang['Ban_IP_explain'] = "Tu môete zada názov poèítaèa, èi IP adresy, ktorım chcete zakáza vstup. Jednotlivé adresy èi mená od seba odde¾te odde¾ovaèom. Ak chcete zada rozsah IP adries, odde¾te ich od seba znakom \"-\". Môete poui i znak\"*\" pre nahradenie èasti reazca.";

$lang['IP_hostname'] = "IP adresa alebo meno poèítaèa";

$lang['Ban_email'] = "Zakázanie vstupu pod¾a e-mailovıch adries";
$lang['Ban_email_explain'] = "Tu môete zada zoznam e-mailovıch adries, ktorım chcete zamedzi vstup, jednotlivé adresy od seba odde¾te odde¾ovaèom. Môete poui i znak \"*\" pre nahradenie èasti adresy, napr. *@hotmail.com";

$lang['Unban_username'] = "Vyòatie uívate¾ov zo zoznamu zakázanıch";
$lang['Unban_username_explain'] = "Ak chcete vyòa niekterıch uívate¾ov z tohto zoznamu, oznaète ich pomocou myši èi klávesnice a potvrïte odoslaním.";

$lang['Unban_IP'] = "Vyòatie IP adries zo zoznamu zakázanıch";
$lang['Unban_IP_explain'] = "Ak chcete vyòa niektoré IP adresy z tohto zoznamu, oznaète ich pomocou myši èi klávesnice a potvrïte odoslaním.";

$lang['Unban_email'] = "Vyòatie e-mailovıch adries zo zoznamu zakázanıch";
$lang['Unban_email_explain'] = "Ak chcete vyòa niektoré e-mailové adresy z tohto zoznamu, oznaète ich pomocou myši èi klávesnice a potvrïte odoslaním.";

$lang['No_banned_users'] = "iadni zakázaní uívatelia";
$lang['No_banned_ip'] = "iadne zakázané IP adresy";
$lang['No_banned_email'] = "iadne zakázané e-mail adresy";

$lang['Ban_update_sucessful'] = "Zoznam zakázanıch uívate¾ov bol úspešne aktualizovanı";
$lang['Click_return_banadmin'] = "Kliknite %ssem%s pre návrat do ovládacieho panelu zakázanie vstupu";


//
// Configuration
//
$lang['General_Config'] = "Konfigurácia";
$lang['Config_explain'] = "Nišie uvedené poloky Vám umonia nastavi fórum pod¾a Vašich poiadaviek. Pre nastavenie uívate¾ov fóra pouívajte odkazy v ¾avej èasti stránky.";

$lang['Click_return_config'] = "Kliknite %ssem%s pre návrat do konfigurácie";

$lang['General_settings'] = "Všeobecné nastavenie fóra";
$lang['Server_name'] = "Meno domény";
$lang['Server_name_explain'] = "Doménové meno tohto fóra beí na";
$lang['Script_path'] = "Cesta ku skriptom";
$lang['Script_path_explain'] = "Cesta ku skriptom phpBB, relatívne umiestnenie v doméne";
$lang['Server_port'] = "Port servera";
$lang['Server_port_explain'] = "Port, na ktorom beí Váš server, štandardne 80";
$lang['Site_name'] = "Meno fóra";
$lang['Site_desc'] = "Popis fóra";
$lang['Board_disable'] = "Zablokova fórum";
$lang['Board_disable_explain'] = "Tımto zneprístupníte fórum pre uívate¾ov. Neodhlasujte sa pokia¾ ste zneprístupnil fórum, inak sa nebudete môc nalogova spä!";

$lang['Acct_activation'] = "Spôsob aktivácie úètu";
$lang['Acc_None'] = "iadny"; // These three entries are the type of activation
$lang['Acc_User'] = "Uívate¾om";
$lang['Acc_Admin'] = "Administrátorom";

$lang['Abilities_settings'] = "Základné nastavenie pre uívate¾a a fórum";
$lang['Max_poll_options'] = "Maximálna hodnota pri hlasovaní";
$lang['Flood_Interval'] = "Ochrannı interval";
$lang['Flood_Interval_explain'] = "Poèet sekúnd, poèas ktorıch musí uívate¾ poèka medzi príspevkami";
$lang['Board_email_form'] = "E-mail uívate¾a cez toto fórum";
$lang['Board_email_form_explain'] = "Umoòuje zasielanie e-mailov inım uívate¾om cez toto fórum";
$lang['Topics_per_page'] = "Tém na stránku";
$lang['Posts_per_page'] = "Príspevkov na stránku";
$lang['Hot_threshold'] = "Príspevky do prípustnej hranice";
$lang['Default_style'] = "Vıchodzí vzh¾ad";
$lang['Override_style'] = "Nahradi uívate¾om zvolenı vzh¾ad";
$lang['Override_style_explain'] = "Pouije vıchodzí vzh¾ad namiesto zvoleného uívate¾om";
$lang['Default_language'] = "Vıchodzí jazyk";
$lang['Date_format'] = "Formát dátumu";
$lang['System_timezone'] = "Èasové pásmo fóra";
$lang['Enable_gzip'] = "Povoli GZIP kompresiu";
$lang['Enable_prune'] = "Povoli preèistenie fóra";
$lang['Allow_HTML'] = "Povoli HTML";
$lang['Allow_BBCode'] = "Povoli znaèky";
$lang['Allowed_tags'] = "Povolené HTML znaèky";
$lang['Allowed_tags_explain'] = "Odde¾te znaèky odde¾ovaèom (,)";
$lang['Allow_smilies'] = "Povoli smajlíky (emotikony)";
$lang['Smilies_path'] = "Cesta k umiestneniu smajlíkov";
$lang['Smilies_path_explain'] = "Cesta mimo Váš phpBB kmeòovı adresár, pr.: images/smilies";
$lang['Allow_sig'] = "Povoli podpisy";
$lang['Max_sig_length'] = "Maximálna dåka podpisu";
$lang['Max_sig_length_explain'] = "Maximálny poèet znakov uívate¾ovho podpisu";
$lang['Allow_name_change'] = "Povoli zmenu uívate¾ského mena";

$lang['Avatar_settings'] = "Nastavenia obrázkov postavièiek";
$lang['Allow_local'] = "Povoli galériu postavièiek";
$lang['Allow_remote'] = "Povoli vzdialené obrázky postavièiek";
$lang['Allow_remote_explain'] = "Obrázok postavièky prepojenı na inı WWW server";
$lang['Allow_upload'] = "Povoli prihrávanie obrázkov postavièiek";
$lang['Max_filesize'] = "Maximálna ve¾kos súboru s obrázkom postavièky";
$lang['Max_filesize_explain'] = "Pre prihrávanie súborov obrázkov postavièiek";
$lang['Max_avatar_size'] = "Maximálne rozmery obrázku postavièky";
$lang['Max_avatar_size_explain'] = "(vıška x šírka v bodoch)";
$lang['Avatar_storage_path'] = "Cesta na ukladanie obrázkov postavièiek";
$lang['Avatar_storage_path_explain'] = "Cesta mimo Váš phpBB kmeòovı adresár, pr.: images/avatars";
$lang['Avatar_gallery_path'] = "Cesta ku galérii obrázkov postavièiek";
$lang['Avatar_gallery_path_explain'] = "Cesta mimo Váš phpBB kmeòovı adresár pre prednastavené obrázky, pr.:images/avatars/gallery";

$lang['COPPA_settings'] = "COPPA nastavenia";
$lang['COPPA_fax'] = "COPPA faxové èíslo";
$lang['COPPA_mail'] = "COPPA e-mailové adresy";
$lang['COPPA_mail_explain'] = "Toto je zoznam adries na ktoré budú rodièia zasiela COPPA registraènı formulár";

$lang['Email_settings'] = "Nastavenia e-mailov";
$lang['Admin_email'] = "Administrátorova e-mailová adresa:";
$lang['Email_sig'] = "Podpis e-mailu";
$lang['Email_sig_explain'] = "Tento text bude pripojenı ku všetkım e-mailom odoslanım z tohto fóra";

$lang['Use_SMTP'] = "Poui SMTP Server pre e-mail";
$lang['Use_SMTP_explain'] = "Zvo¾te Áno ak chcete odosiela e-maily cez meno servra namiesto lokálnej mail funkcie.";
$lang['SMTP_server'] = "Adresa SMTP servera";
$lang['SMTP_username'] = "SMTP úèet";
$lang['SMTP_username_explain'] = "Zadajte len v prípade, e to Váš SMTP server vyaduje";
$lang['SMTP_password'] = "SMTP heslo";
$lang['SMTP_password_explain'] = "Zadajte len v prípade, e to Váš SMTP server vyaduje";

$lang['Disable_privmsg'] = "Súkromné zprávy";
$lang['Inbox_limits'] = "Max. poèet príspevkov v zloke doruèené";
$lang['Sentbox_limits'] = "Max. poèet príspevkov v zloke odoslané";
$lang['Savebox_limits'] = "Max. poèet príspevkov v zloke uloené";

$lang['Cookie_settings'] = "Nastavení Cookie";
$lang['Cookie_settings_explain'] = "Toto detailné nastavenie definuje ako budú zasielané cookies vo Vašom prehliadaèi. Doporuèujeme ponecha vıchodzie hodnoty nastavení cookie ale je moné zmeni hodnoty pod¾a Vašich poiadaviek, nastavenie sa prejeví a po novom prihlásení.";
$lang['Cookie_domain'] = "Doména Cookie";
$lang['Cookie_name'] = "Meno Cookie";
$lang['Cookie_path'] = "Cesta k Cookie";
$lang['Cookie_secure'] = "Zabezpeèenie Cookie";
$lang['Cookie_secure_explain'] = "Ak váš server beí cez SSL nastavte na povolené, ak nie tak nastavte zakázané";
$lang['Session_length'] = "Dåka platnosti Session [ sekúnd ]";


//
// Forum Management
//
$lang['Forum_admin'] = "Administrácia fóra";
$lang['Forum_admin_explain'] = "Z tohto panelu môete pridáva, odstráni, upravova, triedi a synchronizova kategórie fóra";
$lang['Edit_forum'] = "Úprava fóra";
$lang['Create_forum'] = "Vytvori nové fórum";
$lang['Create_category'] = "Vytvori novú kategóriu";
$lang['Remove'] = "Vyòa";
$lang['Action'] = "Akcia";
$lang['Update_order'] = "Aktualizova instrukcie";
$lang['Config_updated'] = "Zmena konfigurácie fóra bola úspešne dokonèená";
$lang['Edit'] = "Upravi";
$lang['Delete'] = "Odstráni";
$lang['Move_up'] = "presunú hore";
$lang['Move_down'] = "presunú dole";
$lang['Resync'] = "Synchronizova";
$lang['No_mode'] = "Mód nebol priradenı";
$lang['Forum_edit_delete_explain'] = "Nišie uvedenı formulár Vám umoní úpravy všeobecnıch nastavení fóra. Pre nastavenia uívate¾ov a fóra pouívajte odkazy v ¾avej èasti stránky.";

$lang['Move_contents'] = "Presunú celı obsah";
$lang['Forum_delete'] = "Odstráni fórum";
$lang['Forum_delete_explain'] = "Nišie uvedenı formulár Vám umoní odstráni fóra èi kategórie a rozhodnú kam chcete da všetky témy, které sú v òom obsiahnuté.";

$lang['Forum_settings'] = "Všeobecné nastavenia fóra";
$lang['Forum_name'] = "Meno fóra";
$lang['Forum_desc'] = "Popis";
$lang['Forum_status'] = "Stav fóra";
$lang['Forum_pruning'] = "Automatické preèistenie";

$lang['prune_freq'] = "Kontrolova staršie témy kadıch";
$lang['prune_days'] = "Odstráni témy ktoré sú staršie";
$lang['Set_prune_data'] = "Chcete nastavi povolenie automatického preèistenia tohto fóra, ale nemáte nastavenú poèetnos alebo poèet dní. Vráte sa prosím spä a zadajte poadované hodnoty.";

$lang['Move_and_Delete'] = "Presunú a odstráni";

$lang['Delete_all_posts'] = "Odstráni všetky príspevky";
$lang['Nowhere_to_move'] = "Sem sa to nedá presunú";

$lang['Edit_Category'] = "Úprava kategórie";
$lang['Edit_Category_explain'] = "Pouite tento formulár pre úpravu mena kategórie.";

$lang['Forums_updated'] = "Fórum a informácie o skupine boli aktualizované";

$lang['Must_delete_forums'] = "Musíte odstráni všetky fóra ešte pred odstránením tejto kategórie";

$lang['Click_return_forumadmin'] = "Kliknite %ssem%s pre návrat do administrácie fóra";


//
// Smiley Management
//
$lang['smiley_title'] = "Úprava smajlíkov (emotikon)";
$lang['smile_desc'] = "Na tejto stránke môete pridáva, odobera a upravova smajlíky (emotikony), ktoré môu Vaši uívatelia pouíva v príspevkoch a súkromnıch správach.";

$lang['smiley_config'] = "Nastavenia smajlíkov";
$lang['smiley_code'] = "Kód smajlíka";
$lang['smiley_url'] = "Grafickı súbor smajlíka";
$lang['smiley_emot'] = "Vıraz smajlíka";
$lang['smile_add'] = "Pridaj novı smajlík";
$lang['Smile'] = "Smajlík";
$lang['Emotion'] = "Vıraz";

$lang['Select_pak'] = "Vyberte (.pak) súbor";
$lang['replace_existing'] = "Nahradi doterajší smajlík";
$lang['keep_existing'] = "Ponecha existujúci smajlík";
$lang['smiley_import_inst'] = "Rozba¾te kolekciu smajlíkov a nahrajte všetky súbory do príslušného adresára smajlíkov pre inštaláciu.  Potom vyberte správnu informáciu v tomto formulári k importovaniu kolekcie smajlíkov.";
$lang['smiley_import'] = "Import kolekcie smajlíkov";
$lang['choose_smile_pak'] = "Vyberte súbor smajlíkov (.pak)";
$lang['import'] = "Importuj smajlíkov";
$lang['smile_conflicts'] = "Co urobi v prípade konfliktov ?";
$lang['del_existing_smileys'] = "Pred importovaním zmate doterajších smajlíkov";
$lang['import_smile_pack'] = "Importova kolekciu smajlíkov";
$lang['export_smile_pack'] = "Vytvori kolekciiu smajlíkov";
$lang['export_smiles'] = "Pokia¾ chcete vytvori kolekciu smajlíkov z dosia¾ pouívanıch smajlíkov, kliknite %ssem%s a stiahnite súbor smiles.pak. Pomenujte tento príslušnı súbor, ale nezabudnite zachova príponu (.pak). Potom vytvorte komprimovanı súbor všetkıch Vašich smajlíkov aj s Vašim súborom nastavení .pak";

$lang['smiley_add_success'] = "Smajlík bol úspešne pridanı !";
$lang['smiley_edit_success'] = "Smajlík bol úspešne zmenenı !";
$lang['smiley_import_success'] = "Súbor smajlíkov bol úspešne importovanı !";
$lang['smiley_del_success'] = "Smajlík bol úspešne odstránenı";
$lang['Click_return_smileadmin'] = "Kliknite %ssem%s pre návrat do administrácie smajlíkov";


//
// User Management
//
$lang['User_admin'] = "Uívate¾ská administrácia";
$lang['User_admin_explain'] = "Tu môete zmeni informáciu o uívate¾ovi a niektoré špecifické nastavenia. K úprave práv pouite uívate¾a a skupinovı povo¾ovací systém.";

$lang['Look_up_user'] = "Zvoli uívate¾a";

$lang['Admin_user_fail'] = "Nie je moné zmeni nastavenia uívate¾a.";
$lang['Admin_user_updated'] = "Zmena nastavení uívate¾a prebehla úspešne.";
$lang['Click_return_useradmin'] = "Kliknite %ssem%s pre návrat do Uívate¾skej administrácie";

$lang['User_delete'] = "Odstráni tohto uívate¾a";
$lang['User_delete_explain'] = "Tu zmaete tohto uívate¾a. Nemono vzia spä !";
$lang['User_deleted'] = "Uívate¾ úspešne odstránenı.";

$lang['User_status'] = "Uívate¾ je aktívny";
$lang['User_allowpm'] = "Môe posiela súkromné správy";
$lang['User_allowavatar'] = "Môe zobrazova postavièky";

$lang['Admin_avatar_explain'] = "Tu môete vidie a odstráni súèasnú uívate¾ovu postavièku.";

$lang['User_special'] = "Zvláštne oblasti administrátorskıch nastavení";
$lang['User_special_explain'] = "Tieto oblasti nemôu by upravované uívate¾mi. Tu môete nastavi ich zaradenie a ïalšie oblasti, ktoré nie sú uívate¾om priradené.";



//
// Group Management
//
$lang['Group_administration'] = "Skupinová administrácia";
$lang['Group_admin_explain'] = "Z tohto panelu môete spravova všetky uívate¾ské skupiny, môete odstráni, vytvori a meni súèasné skupiny, môete vybera moderátorov, zamknú otvorené/uzavreté skupiny a nastavi meno a popis skupiny";
$lang['Error_updating_groups'] = "Pri nahrávání skupín došlo k chybe";
$lang['Updated_group'] = "Skupina bola úspešne nahratá";
$lang['Added_new_group'] = "Nová skupina bola úspešne vytvorená";
$lang['Deleted_group'] = "Skupina bola úspešne odstránená";
$lang['New_group'] = "Vytvori novú skupinu";
$lang['Edit_group'] = "Zmeni skupinu";
$lang['group_name'] = "Meno skupiny";
$lang['group_description'] = "Popis skupiny";
$lang['group_moderator'] = "Moderátor skupiny";
$lang['group_status'] = "Nastavenia skupiny";
$lang['group_open'] = "Otvorená skupina";
$lang['group_closed'] = "Uzavretá skupina";
$lang['group_hidden'] = "Skrytá skupina";
$lang['group_delete'] = "odstráni skupinu";
$lang['group_delete_check'] = "odstráni túto skupinu";
$lang['submit_group_changes'] = "Odosla zmeny";
$lang['reset_group_changes'] = "Obnovi zmeny";
$lang['No_group_name'] = "Musíte zada meno pre túto skupinu";
$lang['No_group_moderator'] = "Musíte zada moderátora pre túto skupinu";
$lang['No_group_mode'] = "Musíte zada nastavenie tejto skupiny, otvorená alebo uzavretá.";
$lang['No_group_action'] = "Nebola vybratá iadna akcia";
$lang['delete_group_moderator'] = "odstráni moderátora pôvodnej skupiny ?";
$lang['delete_moderator_explain'] = "Pokia¾ meníte moderátora skupiny, zaškrtnite toto políèko k odstráneniu pôvodného moderátora z tejto skupiny.  V opaènom prípade sa tento uívate¾ stane benım èlenom tejto skupiny.";
$lang['Click_return_groupsadmin'] = "Kliknite %ssem%s pre návrat do Skupinovej administrácie.";
$lang['Select_group'] = "Vyberte skupinu";
$lang['Look_up_group'] = "Vyh¾adajte skupinu";


//
// Prune Administration
//
$lang['Forum_Prune'] = "Preèistenie fóra";
$lang['Forum_Prune_explain'] = "Táto funkcia odstráni všetky témy, ku ktorım neboli pridané príspevky za Vami zadanı poèet dní. Pokia¾ nezadáte poèet dní, potom budú odstránené všetky témy. Nebudú odstránené témy, v ktorıch beí hlasovanie a rovnako tak sa neodstránia oznámenia. Tieto témy budete musie odstráni ruène.";
$lang['Do_Prune'] = "Preèisti";
$lang['All_Forums'] = "Všetky fóra";
$lang['Prune_topics_not_posted'] = "Preèisti témy bez odpovede staršie";
$lang['Topics_pruned'] = "Témy preèistené";
$lang['Posts_pruned'] = "Príspevky preèistené";
$lang['Prune_success'] = "Preèistenie fór prebehlo úspešne.";


//
// Word censor
//
$lang['Words_title'] = "Slovná cenzúra";
$lang['Words_explain'] = "Z tohto kontrolného panelu môete prida, zmeni a odstráni slová, ktoré budú automaticky cenzurované na Vašich fórach. Rovnako tak nebude moné zaregistrova uívate¾ské mená obsahujúce tieto vırazy. Hviezdièku (*) je moné poui za èas slova, take napr. vıraz 'test' vyh¾adá vıraz 'protestova', test* potom 'testova' a *test slovo 'protest'.";
$lang['Word'] = "Slovo";
$lang['Edit_word_censor'] = "Zmeni slovnú cenzúru";
$lang['Replacement'] = "Náhrada";
$lang['Add_new_word'] = "Pridajte nové slovo";
$lang['Update_word'] = "Nahrajte slovnú cenzúru";

$lang['Must_enter_word'] = "Musíte vloi slovo a jeho náhradu";
$lang['No_word_selected'] = "K úprave nebolo vybraté iadne slovo";

$lang['Word_updated'] = "Vybraté slovo bolo úspešne nahraté do cenzúry";
$lang['Word_added'] = "Slovo bolo úspešne pridané do cenzúry";
$lang['Word_removed'] = "Cenzúrované slovo bolo úspešne odstránené";

$lang['Click_return_wordadmin'] = "Kliknite %ssem%s pre návrat do Administrácie slovnej cenzúry";


//
// Mass Email
//
$lang['Mass_email_explain'] = "Odtia¾to môete posla správu akémuko¾vek uívate¾ovi alebo všetkım z vybratej skupiny. Stane sa tak zaslatím e-mailu na zadanú administrátorskú adresu, prièom uívate¾om bude zaslatá slepá kópia. Pokia¾ posielate správu väèšej skupine, prosím, majte chví¾ku strpenia a nezastavujte akciu, keï sa vykonáva. Je celkom bené, e hromadná korešpondencia trvá dlhšiu dobu a budete upozornenı, keï sa akcia dokonèí";
$lang['Compose'] = "Napísa";

$lang['Recipients'] = "Príjemcovia";
$lang['All_users'] = "Všetci uívatelia";

$lang['Email_successfull'] = "Vaša správa bola odoslaná";
$lang['Click_return_massemail'] = "Kliknite %ssem%s pre návrat na formulár Hromadnej korešpondencie";


//
// Ranks admin
//
$lang['Ranks_title'] = "Administrácia hodnotení";
$lang['Ranks_explain'] = "Tımto formulárom pridávate, meníte, prehliadate a maete hodnotenia. Môete tie vytvori vlastné nastavenia hodnotení, ktoré môu by priradené cez funkcie nastavení uívate¾a.";

$lang['Add_new_rank'] = "Prida nové hodnotenie";

$lang['Rank_title'] = "Názov hodnotenia";
$lang['Rank_special'] = "Nastavi ako zvláštne hodnotenie";
$lang['Rank_minimum'] = "Minimálny poèet príspevkov";
$lang['Rank_maximum'] = "Maximálny poèet príspevkov";
$lang['Rank_image'] = "Obrázok hodnotenia";
$lang['Rank_image_explain'] = "Pouite túto funkciu na definovanie malého obrázku spojeného s danım hodnotením. Cesta mimo Váš phpBB kmeòovı adresár a názov súboru, pr.: images/ranks/images1.gif";

$lang['Must_select_rank'] = "Musíte vybra hodnotenie";
$lang['No_assigned_rank'] = "Nebolo zadané iadne zvláštne hodnotenie";

$lang['Rank_updated'] = "Hodnotenie bolo úspešne zmenené";
$lang['Rank_added'] = "Hodnotenie bolo úspešne pridané";
$lang['Rank_removed'] = "Hodnotenie bolo úspešne zrušené";
$lang['No_update_ranks'] = "Hodnotenie bolo úspešne odstránené, avšak uívate¾ské úèty spojené s tımto hodnotením sa nezmenili. Bude potrebné toto hodnotenie upravi ruène";

$lang['Click_return_rankadmin'] = "Kliknite %ssem%s pre návrat do Administrácia hodnocení";


//
// Disallow Username Admin
//
$lang['Disallow_control'] = "Správa nepovolenıch uívate¾skıch mien";
$lang['Disallow_explain'] = "Tu môete spravova uívate¾ské mená, ktorá nebudú povoloené k pouitiu. Nepovolené uívate¾ské mená môu obsahova \*\.  Upozoròujeme, e nebudete môc urèi u zaregistrované uívate¾ské meno. Najskôr ho musíte odstráni a následne ho nepovoli";

$lang['Delete_disallow'] = "odstráni";
$lang['Delete_disallow_title'] = "odstráni nepovolené uívate¾ské meno";
$lang['Delete_disallow_explain'] = "môete odstráni nepovolené uívate¾ské meno tak, e ho vyberiete zo zoznamu a stlaète tlaèítko odstráni";

$lang['Add_disallow'] = "Prida";
$lang['Add_disallow_title'] = "Prida nepovolené uívate¾ské meno";
$lang['Add_disallow_explain'] = "Môete zakáza niektoré nepovolené uívate¾ské mená. Tieto mená si nebude môc iadny uívate¾ zaregistrova. Môete pouí aj znak \"*\" pre nahradenie èasti mena";

$lang['No_disallowed'] = "iadne nepovolené uívate¾ské mená";

$lang['Disallowed_deleted'] = "Nepovolené uívate¾ské meno bolo úspešne odstránené";
$lang['Disallow_successful'] = "Nepovolené uívate¾ské meno bolo úspešne pridené";
$lang['Disallowed_already'] = "Meno, ktoré ste zadali, nemôe by zakázané. Buï sa u vyskytuje v tomto zozname alebo v zozname cenzúrovanıch slov, alebo existuje rovnaké uívate¾ské meno";

$lang['Click_return_disallowadmin'] = "Kliknite %ssem%s pre návrat do Administrácie nastavení nepovolenıch uívate¾skıch mien";


//
// Styles Admin
//
$lang['Styles_admin'] = "Administrácia štılov";
$lang['Styles_explain'] = "Tu môete pridáva, odobera a spravova štıly (vzory a motívy) dostupné pre Vašich uívate¾ov";
$lang['Styles_addnew_explain'] = "Tento zoznam obsahuje všetky motívy, které sú dostupné pre vzory, ktoré nynímate. Èasti na tomto zozname ešte neboli nainštalované do zodpovedajúcej databázy phpBB. Pre nainštalovanie kliknite na inštalaènı odkaz ved¾a zadania";

$lang['Select_template'] = "Vybra vzor";

$lang['Style'] = "Štıl";
$lang['Template'] = "Vzor";
$lang['Install'] = "Nainštalova";
$lang['Download'] = "Stiahnu";

$lang['Edit_theme'] = "Upravi motív";
$lang['Edit_theme_explain'] = "V spodnom formulári môete upravova nastavenia pre zvolenı vzor";

$lang['Create_theme'] = "Vytvori motív";
$lang['Create_theme_explain'] = "V spodnom formulári môete vytvori novı motív. Pri zadávání farieb (pre ktoré pouijete hexadecimálne hodnoty) neuvádzajte znak #, tzn. hodnota CCCCCC je platná, #CCCCCC nie";

$lang['Export_themes'] = "Exportova motívy";
$lang['Export_explain'] = "V tomto paneli môete exportova zadanie motívu pre zvolenı vzor. Vyberte vzor zo spodného vıberu a skript vytvorí konfiguraènı súbor pre motív a bude ho chcie uloi do vybratého adresára vzorov. Pokia¾ sa mu to nepodarí, ponúkne Vám monos súbor stiahnu na disk. Aby sa mohol súbor uloi, je potrebné, aby bol povolenı prístup na zápis pre príslušnı adresár. Pre viac informácií si pozrite uívate¾skı manuál k phpBB 2.";
$lang['Theme_installed'] = "Vybratı motív bol úspešne nainštalovanı";
$lang['Style_removed'] = "Vybratı štıl bol odstránenı z databázy. K plnému odstráneniu tohto stılu zo systému musíte odstráni príslušnı stıl z adresára vzorov.";
$lang['Theme_info_saved'] = "Informácie k zvolenému vzoru boli uloené. Teraz nastavte povolenie na theme_info.cfg (a tie vybratého adresára vzorov) na 'len na èítanie'";
$lang['Theme_updated'] = "Vybratı motív bol zmenenı. Vyexportujte teraz nastavenia nového motívu";
$lang['Theme_created'] = "Motív vytvorenı. Vyexportujte teraz novı motív do konfiguraèného súboru kvôli bezpeènému uloeniu alebo pouitiu pre iné prípady";

$lang['Confirm_delete_style'] = "Ste si istı, e chcete odstráni tento štıl ?";

$lang['Download_theme_cfg'] = "Nie je moné zapisova do konfiguraèného súboru. Kliknite na spodné tlaèítko na sttiahnutie súboru Vašim prehliadaèom. A ho stiahnete, môete ho presunú do adresára obsahujúceho súbory vzorov. Potom môete zabali súbory pre distribúciu alebo poui inde, keï chcete";
$lang['No_themes'] = "Ku vzoru, ktorı ste vybrali, sa neviau iadne motívy. Novı motív vytvoríte kliknutím na 'Vytvori nové' na ¾avej strane panelu";
$lang['No_template_dir'] = "Nie je moné otvori adresár so vzormi. Moné je, e ho sa nedá cez server èíta alebo neexistuje";
$lang['Cannot_remove_style'] = "Nie je moné odstráni vybratı štıl, ak je urèenı ako pôvodnı pre fórum. Zmeòte, prosím, pôvodnı stıl a skúste to znova.";
$lang['Style_exists'] = "Meno stılu u existuje. Prosím vráte sa spä a zvo¾te iné meno.";

$lang['Click_return_styleadmin'] = "Kliknete %ssem%s pre návrat do Administrácie štılov";

$lang['Theme_settings'] = "Nastavenia motívu";
$lang['Theme_element'] = "Súèas vzoru";
$lang['Simple_name'] = "Jednoduchı názov";
$lang['Value'] = "Hodnota";
$lang['Save_Settings'] = "Ulo nastavenia";

$lang['Stylesheet'] = "Zadanie štılu CSS";
$lang['Background_image'] = "Obrázok pozadia";
$lang['Background_color'] = "Farba pozadia";
$lang['Theme_name'] = "Meno motívu";
$lang['Link_color'] = "Farba odkazu";
$lang['Text_color'] = "Farba textu";
$lang['VLink_color'] = "Farba navštíveného odkazu";
$lang['ALink_color'] = "Farba aktívneho odkazu";
$lang['HLink_color'] = "Hover Link Colour";
$lang['Tr_color1'] = "Farba riadku tabu¾ky 1";
$lang['Tr_color2'] = "Farba riadku tabu¾ky 2";
$lang['Tr_color3'] = "Farba riadku tabu¾ky 3";
$lang['Tr_class1'] = "Trieda riadku tabu¾ky 1";
$lang['Tr_class2'] = "Trieda riadku tabu¾ky 2";
$lang['Tr_class3'] = "Trieda riadku tabu¾ky 3";
$lang['Th_color1'] = "Farba titulu tabu¾ky 1";
$lang['Th_color2'] = "Farba titulu tabu¾ky 2";
$lang['Th_color3'] = "Farba titulu tabu¾ky 3";
$lang['Th_class1'] = "Table Header Class 1";
$lang['Th_class2'] = "Table Header Class 2";
$lang['Th_class3'] = "Table Header Class 3";
$lang['Td_color1'] = "Farba bunky tabu¾ky 1";
$lang['Td_color2'] = "Farba bunky tabu¾ky 2";
$lang['Td_color3'] = "Farba bunky tabu¾ky 3";
$lang['Td_class1'] = "Table Cell Class 1";
$lang['Td_class2'] = "Table Cell Class 2";
$lang['Td_class3'] = "Table Cell Class 3";
$lang['fontface1'] = "Vzh¾ad písma 1";
$lang['fontface2'] = "Vzh¾ad písma 2";
$lang['fontface3'] = "Vzh¾ad písma 3";
$lang['fontsize1'] = "Ve¾kos písma 1";
$lang['fontsize2'] = "Ve¾kos písma 2";
$lang['fontsize3'] = "Ve¾kos písma 3";
$lang['fontcolor1'] = "Farba písma 1";
$lang['fontcolor2'] = "Farba písma 2";
$lang['fontcolor3'] = "Farba písma 3";
$lang['span_class1'] = "Rozpätie tried 1";
$lang['span_class2'] = "Rozpätie tried 2";
$lang['span_class3'] = "Rozpätie tried 3";
$lang['img_poll_size'] = "Ve¾kos obrázku pre hlasovanie [v pixeloch]";
$lang['img_pm_size'] = "Ve¾kos obrázku pre súkromnú správu [v pixeloch]";


//
// Install Process
//
$lang['Welcome_install'] = "Vitajte v inštalácii phpBB 2";
$lang['Initial_config'] = "Základné nastavenia";
$lang['DB_config'] = "Nastavenia databázy";
$lang['Admin_config'] = "Administrátorské nastavenia";
$lang['continue_upgrade'] = "Po tom, èo ste stiahli konfiguraènı súbor na Váš disk môete spodnım tlaèítkom 'Pokraèova v inštalácii novšej verzie'. Poèkajte s nahrávaním konfiguraèného súboru dokia¾ nie je ukonèená inštalácia novšej verzie.";
$lang['upgrade_submit'] = "Pokraèova v inštalácii novšej verzie";

$lang['Installer_Error'] = "Poèas inštalácie sa vyskytla chyba";
$lang['Previous_Install'] = "Bola nájdená prechádzajúca inštalácia";
$lang['Install_db_error'] = "Poèas nahrávania novšej inštalácie databázy sa vyskytla chyba";

$lang['Re_install'] = "Vaša predchádzajúca inštalácia je stále aktívna. <br /><br />Pokia¾ si prajete preinštalova phpBB 2,pokraèujte tlaèítkem 'Áno'. Uvedomte si, prosím, e v tomto prípade sa znièia všetky dáta; nedôjde k zálohovaniu. Administrátorské uívate¾ské meno a heslo, ktoré ste pouívali k nalogovaniu budú po reinštalácii prerobené, iadne ïalšie nastavenia nebudú zachované.<br /><br />Zamyslite sa pozorne pred stlaèením tlaèítka 'Áno'!";

$lang['Inst_Step_0'] = "Thank you for choosing phpBB 2. In order to complete this install please fill out the details requested below. Please note that the database you install into should already exist. If you are installing to a database that uses ODBC, e.g. MS Access you should first create a DSN for it before proceeding.";

$lang['Start_Install'] = "Zaèa inštaláciu";
$lang['Finish_Install'] = "Ukonèi inštaláciu";

$lang['Default_lang'] = "Pôvodnı jazyk boardu";
$lang['DB_Host'] = "Database Server Hostname / DSN";
$lang['DB_Name'] = "Názov Vašej databázy";
$lang['DB_Username'] = "Uívate¾ské meno databázy";
$lang['DB_Password'] = "Heslo databázy";
$lang['Database'] = "Vaša databáza";
$lang['Install_lang'] = "Vyberte jazyk pre inštaláciu";
$lang['dbms'] = "Typ databázy";
$lang['Table_Prefix'] = "Predpona pre tabu¾ky v databázi";
$lang['Admin_Username'] = "Administrátorské uiv. meno";
$lang['Admin_Password'] = "Administrátorské heslo";
$lang['Admin_Password_confirm'] = "Administrátorské heslo [ Potvrdi ]";

$lang['Inst_Step_2'] = "Vaše uívate¾ské meno bolo vytvorené. V tomto momente je základná inštalácia ukonèená. Teraz budete prepnutı do ïalšej èasti, ktorá Vám umoní ïalšiu administráciu novej inštalácie. Skontrolujte, prosím, detaily Všeobecnıch nastavení a urobte potrebné zmeny. Ïakujeme, e ste si vybrali phpBB 2.";

$lang['Unwriteable_config'] = "Do Vášho konfiguraèného súboru nie je moné zapisova. Kópia tohto súboru bude stiahnutá, keï kliknete na tlaèítko dole. Potom nahrajte tento súbor do adresára phpBB 2. Ïalej sa prihláste ako administrátor s heslom, ktoré ste zadali v predchádzajúcom formulári a navštívte administrátorské centrum (odkaz sa objaví v spodnej èasti kadej stránky potom, èo sa nalogujete) a skontrolujte všeobecnú konfiguráciu. Ïakujeme, e ste si vybrali phpBB 2.";
$lang['Download_config'] = "Stiahnu konfiguraènı súbor";

$lang['ftp_choose'] = "Vyberte si spôsob stiahnutia";
$lang['ftp_option'] = "<br />Vzh¾adom k tomu, e je v tejto verzii umonenı rozšírenı prenos php môe Vám by danı priestor najskôr prenies Váš konfiguraènı súbor automaticky.";
$lang['ftp_instructs'] = "Vybrali ste automatickú vo¾bu prenosu. Zadajte, prosím, informácie k uskutoèneniu tohto procesu. Prosím, pamätajte na to, e cesta prenosu má by presne taká, akú by ste zadávali cez akéhoko¾vek beného klienta.";
$lang['ftp_info'] = "Zadejte Vaše informácie prenosu FTP";
$lang['Attempt_ftp'] = "Pokus o prenos konfiguraèného súboru na miesto";
$lang['Send_file'] = "Pošlite mi súbor a ja ho prenesiem sám";
$lang['ftp_path'] = "Cesta FTP na phpBB";
$lang['ftp_username'] = "Vaše uívate¾ské meno FTP";
$lang['ftp_password'] = "Vaše heslo FTP";
$lang['Transfer_config'] = "Zaèa prenos";
$lang['NoFTP_config'] = "Pokus prenies súbor na miesto zlyhal. Prosím, stiahnite a potom nahrajte konfiguraènı súbor sami.";

$lang['Install'] = "Inštalova";
$lang['Upgrade'] = "Inovova verziu";

$lang['Install_Method'] = "Vyberte druh inštalácie";

$lang['Install_No_Ext'] = "Nastavenia php na Vašom serveri nepodporuje databázu, ktorú ste zvolili";

$lang['Install_No_PCRE'] = "phpBB2 poaduje the Perl-Compatible Regular Expressions Module pre php, èo Vaša konfigurácia php pravdepodobne nepodporuje!";

//
// That's all Folks!
// -------------------------------------------------

$lang['Status_locked'] = 'Zamknúté'; 
$lang['Status_unlocked'] = 'Odomknúté';
?>