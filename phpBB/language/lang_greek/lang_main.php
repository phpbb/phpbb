<?php
/***************************************************************************
 *                             lang_main.php [Greek]
 *                              -------------------
 *     begin                : Dec 6 2001
 *     copyright            : (C) 2001 The phpBB Group
 *     email                : support@phpbb.com
 *
 *     $Id: lang_main.php,v 1.58 2001/11/29 22:51:34 psotfx Exp $
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
// Translation by Alexandros Topalidis (arttor)
// Email : arttor@mailbox.gr
//


//setlocale(LC_ALL, "el_GR.ISO-8859-7");
$lang['ENCODING'] = 'iso-8859-7';
$lang['DIRECTION'] = 'ltr';
$lang['LEFT'] = 'left';
$lang['RIGHT'] = 'right';
$lang['DATE_FORMAT'] = 'd M Y'; // This should be changed to the default date format for your language, php date() format

// This is optional, if you would like a _SHORT_ message output
// along with our copyright message indicating you are the translator
// please add it here.
// $lang['TRANSLATION'] = '';

//
// Common, these terms are used
// extensively on several pages
//
$lang['Forum'] = 'Δημόσια  συζήτηση';
$lang['Category'] = 'Κατηγορία';
$lang['Topic'] = 'Θεματική Ενότητα';
$lang['Topics'] = 'Θεματικές Ενότητες';
$lang['Replies'] = 'Απαντήσεις';
$lang['Views'] = 'Αναγνώσεις';
$lang['Post'] = 'Δημοσίευση';
$lang['Posts'] = 'Δημοσιεύσεις';
$lang['Posted'] = 'Δημοσιεύθηκε';
$lang['Username'] = 'Όνομα μέλους';
$lang['Password'] = 'Κωδικός';
$lang['Email'] = 'Email';
$lang['Poster'] = 'Αποστολέας';
$lang['Author'] = 'Συγγραφέας';
$lang['Time'] = 'Ώρα';
$lang['Hours'] = 'Ώρες';
$lang['Message'] = 'Μήνυμα';

$lang['1_Day'] = '1 Ημέρα';
$lang['7_Days'] = '7 Ημέρες';
$lang['2_Weeks'] = '2 Εβδομάδες';
$lang['1_Month'] = '1 Μήνα';
$lang['3_Months'] = '3 Μήνες';
$lang['6_Months'] = '6 Μήνες';
$lang['1_Year'] = '1 Έτος';

$lang['Go'] = 'Go';
$lang['Jump_to'] = 'Μετάβαση στη';
$lang['Submit'] = 'Υποβολή';
$lang['Reset'] = 'Επαναφορά';
$lang['Cancel'] = 'Άκυρο';
$lang['Preview'] = 'Προεπισκόπιση';
$lang['Confirm'] = 'Επιβεβαίωση';
$lang['Spellcheck'] = 'Έλεγχος Ορθογραφίας';
$lang['Yes'] = 'Ναί';
$lang['No'] = 'Όχι';
$lang['Enabled'] = 'Ενεργοποιημένο';
$lang['Disabled'] = 'Απενεργοποιημένο';
$lang['Error'] = 'Λάθος';

$lang['Next'] = 'Επόμενη';
$lang['Previous'] = 'Προηγούμενη';
$lang['Goto_page'] = 'Μετάβαση στη σελίδα';
$lang['Joined'] = 'Ένταξη';
$lang['IP_Address'] = 'IP Διεύθυνση';

$lang['Select_forum'] = 'Επιλέξτε μια Δημόσια Συζήτηση';
$lang['View_latest_post'] = 'Δείτε την τελευταία Δημοσιεύση';
$lang['View_newest_post'] = 'Δείτε την πιο πρόσφατη Δημοσιεύση';
$lang['Page_of'] = 'Σελίδα <b>%d</b> από <b>%d</b>'; // Replaces with: Page 1 of 2 for example

$lang['ICQ'] = 'ICQ Αριθμός';
$lang['AIM'] = 'AIM διεύθυνση';
$lang['MSNM'] = 'MSN Messenger';
$lang['YIM'] = 'Yahoo Messenger';

$lang['Forum_Index'] = '%s Αρχική σελίδα';  // eg. sitename Forum Index, %s can be removed if you prefer

$lang['Post_new_topic'] = 'Δημοσίευση νέας  Θ.Ενότητας';
$lang['Reply_to_topic'] = 'Απάντηση στη Θ.Ενότητα';
$lang['Reply_with_quote'] = 'Απάντηση με παράθεση αυτού του μηνύματος';

$lang['Click_return_topic'] = 'Για την επιστροφή σας στη Θ.Ενότητα πατήστε %sΕδώ%s '; // %s's here are for uris, do not remove!
$lang['Click_return_login'] = 'Για να ξαναδοκιμάσετε πατήστε %sΕδώ%s';
$lang['Click_return_forum'] = 'Για την επιστροφή σας στη Δ.Συζήτηση πατήστε %sΕδώ%s';
$lang['Click_view_message'] = 'Πατήστε %sΕδώ%s για να δείτε το μήνυμα σας';
$lang['Click_return_modcp'] = 'Πατήστε %sΕδώ%s για να επιστρέψετε στον πίνακα ελέγχου του Συντονιστή';
$lang['Click_return_group'] = 'Πατήστε %sΕδώ%s για να επιστρέψετε στις Πληροφορίες Ομάδας';

$lang['Admin_panel'] = 'Μετάβαση στόν Πίνακα Διαχείρισης';

$lang['Board_disable'] = 'Συγνώμη αλλά αυτή η συζήτηση είναι προσωρινά μη διαθέσιμη, επιστρέψτε αργότερα';


//
// Global Header strings
//
$lang['Registered_users'] = 'Μέλη:';
$lang['Browsing_forum'] = 'Χρήστες συνδεδεμένοι στο forum:';
$lang['Online_users_zero_total'] = 'Συνολικά <b>0</b> χρήστες είναι συνδεδεμένοι :: ';
$lang['Online_users_total'] = '%d χρήστες είναι συνδεδεμένοι αυτήν την στιγμή:: ';
$lang['Online_user_total'] = '%d χρήστης είναι συνδεδεμένος αυτήν την στιγμή:: ';
$lang['Reg_users_zero_total'] = '0 μέλη, ';
$lang['Reg_users_total'] = '%d μέλη, ';
$lang['Reg_user_total'] = '%d μέλος, ';
$lang['Hidden_users_zero_total'] = '0 μη ορατοί και ';
$lang['Hidden_user_total'] = '%d μη ορατός και ';
$lang['Hidden_users_total'] = '%d μη ορατοί και ';
$lang['Guest_users_zero_total'] = '0 επισκέπτες';
$lang['Guest_users_total'] = '%d επισκέπτες';
$lang['Guest_user_total'] = '%d επισκέπτης';
$lang['Record_online_users'] = 'Περισσότεροι χρήστες υπό σύνδεση <b>%s</b>, στις %s'; // first %s = number of users, second %s is the date.

$lang['Admin_online_color'] = '%sΔιαχειριστής%s';
$lang['Mod_online_color'] = '%sΣυντονιστής%s';

$lang['You_last_visit'] = 'Η τελευταία επίσκεψή σας ήταν στις %s'; // %s replaced by date/time
$lang['Current_time'] = 'Η ώρα είναι %s'; // %s replaced by time

$lang['Search_new'] = 'Δημοσιεύσεις που έγιναν μετά την τελευταία σας επίσκεψη';
$lang['Search_your_posts'] = 'Ανασκόπηση των δημοσιεύσεων σας';
$lang['Search_unanswered'] = 'Αναπάντητες δημοσιεύσεις';

$lang['Register'] = 'Εγγραφή';
$lang['Profile'] = 'Προφίλ';
$lang['Edit_profile'] = 'Επεξεργασία του Προφίλ σας';
$lang['Search'] = 'Αναζήτηση';
$lang['Memberlist'] = 'Κατάλογος Μελών';
$lang['FAQ'] = 'Συχνές Ερωτήσεις';
$lang['BBCode_guide'] = 'BBCode Εγχειρίδιο';
$lang['Usergroups'] = 'Ομάδες Μελών';
$lang['Last_Post'] = 'Τελευταία  Δημοσίευση';
$lang['Moderator'] = 'Συντονιστής';
$lang['Moderators'] = 'Συντονιστές';


//
// Stats block text
//
$lang['Posted_articles_zero_total'] = 'Τα μέλη μας έχουν δημοσιεύσει συνολικά <b>0</b> θέματα'; // Number of posts
$lang['Posted_articles_total'] = 'Τα μέλη μας έχουν δημοσιεύσει συνολικά <b>%d</b> θέματα'; // Number of posts
$lang['Posted_article_total'] = 'Τα μέλη μας έχουν δημοσιεύσει συνολικά <b>%d</b> θέμα'; // Number of posts
$lang['Registered_users_zero_total'] = 'Έχουμε <b>0</b> μέλη'; // # registered users
$lang['Registered_users_total'] = 'Τα μέλη μας είναι συνολικά <b>%d</b> '; // # registered users
$lang['Registered_user_total'] = 'Έχουμε <b>%d</b> μέλος'; // # registered users
$lang['Newest_user'] = 'Το νέο μέλος στις συζητήσεις μας είναι ο/η <b>%s%s%s</b>'; // a href, username, /a 

$lang['No_new_posts_last_visit'] = 'Δεν υπάρχουν νέες δημοσιεύσεις, από την τελευταία σας επίσκεψη';
$lang['No_new_posts'] = 'Δεν υπάρχουν νέες δημοσιεύσεις';
$lang['New_posts'] = 'Υπάρχουν νέες δημοσιεύσεις';
$lang['New_post'] = 'Υπάρχει νέα δημοσίευση';
$lang['No_new_posts_hot'] = 'Δεν υπάρχουν νέες δημοσιεύσεις [ Popular ]';
$lang['New_posts_hot'] = 'Υπάρχουν νέες δημοσιεύσεις [ Popular ]';
$lang['No_new_posts_locked'] = 'Δεν υπάρχουν νέες δημοσιεύσεις [ Locked ]';
$lang['New_posts_locked'] = 'Υπάρχουν νέες δημοσιεύσεις [ Locked ]';
$lang['Forum_is_locked'] = 'Η Δ.Συζήτηση είναι κλειδωμένη ';


//
// Login
//
$lang['Enter_password'] = 'Παρακαλώ εισαγάγετε το όνομα μέλους και τον κωδικό σας';
$lang['Login'] = 'Σύνδεση';
$lang['Logout'] = 'Αποσύνδεση';

$lang['Forgotten_password'] = 'Έχω ξεχάσει τον κωδικό μου';

$lang['Log_me_in'] = 'Να γίνεται η σύνδεση αυτόματα σε κάθε μου επίσκεψη';

$lang['Error_login'] = 'Πιθανόν δώσατε λάθος όνομα χρήστη-κωδικό ή δεν είστε ενεργοποιημένο μέλος';


//
// Index page
//
$lang['Index'] = 'Ευρετήριο';
$lang['No_Posts'] = 'Δεν υπάρχουν δημοσιεύσεις';
$lang['No_forums'] = 'Ο χώρος των συζητήσεων είναι κενός';

$lang['Private_Message'] = 'Προσωπικό μήνυμα';
$lang['Private_Messages'] = 'Προσωπικά μηνύματα';
$lang['Who_is_Online'] = 'Παρόντες χρήστες';

$lang['Mark_all_forums'] = 'Να σημειωθούν όλες οι Δ.Συζητήσεις ως αναγνωσμένες ';
$lang['Forums_marked_read'] = 'Όλες οι Δ.Συζητήσεις σημειώθηκαν ως αναγνωσμένες';


//
// Viewforum
//
$lang['View_forum'] = 'Επισκόπηση Δ.Συζήτησης';

$lang['Forum_not_exist'] = 'Η Δ.Συζήτηση που επιλέξατε δεν υπάρχει';
$lang['Reached_on_error'] = 'You have reached this page in error';

$lang['Display_topics'] = 'Επισκόπηση όλων των Θ.Ενοτήτων που έγιναν πριν από';
$lang['All_Topics'] = 'Όλα τα Θέματα';

$lang['Topic_Announcement'] = '<b>Ανακοίνωση:</b>';
$lang['Topic_Sticky'] = '<b>Υπόμνημα:</b>';
$lang['Topic_Moved'] = '<b>Μετακινήθηκε:</b>';
$lang['Topic_Poll'] = '<b>[ Ψηφοφορία ]</b>';

$lang['Mark_all_topics'] = 'Να σημειωθούν όλες οι Θ.Ενότητες ως αναγνωσμένες ';
$lang['Topics_marked_read'] = 'Όλες οι Θ.Ενότητες αυτής της Δ.Συζήτησης χαρακτηρίσθηκαν ως αναγνωσμένες';

$lang['Rules_post_can'] = '<b>Μπορείτε</b> να δημοσιεύσετε νέο Θέμα σ\' αυτή τη Δ.Συζήτηση';
$lang['Rules_post_cannot'] = '<b>Δεν μπορείτε</b> να δημοσιεύσετε νέο Θέμα σ\' αυτή τη Δ.Συζήτηση';
$lang['Rules_reply_can'] = '<b>Μπορείτε</b> να απαντήσετε στα Θέματα αυτής της Δ.Συζήτησης';
$lang['Rules_reply_cannot'] = '<b>Δεν μπορείτε</b> να απαντήσετε στα Θέματα αυτής της Δ.Συζήτησης';
$lang['Rules_edit_can'] = '<b>Μπορείτε</b> να επεξεργασθείτε τις δημοσιεύσεις σας σ\' αυτή τη Δ.Συζήτηση';
$lang['Rules_edit_cannot'] = '<b>Δεν μπορείτε</b> να επεξεργασθείτε τις δημοσιεύσεις σας σ\' αυτή τη Δ.Συζήτηση';
$lang['Rules_delete_can'] = '<b>Μπορείτε</b> να διαγράψετε τις δημοσιεύσεις σας σ\' αυτή τη Δ.Συζήτηση';
$lang['Rules_delete_cannot'] = '<b>Δεν μπορείτε</b> να διαγράψετε τις δημοσιεύσεις σας σ\' αυτή τη Δ.Συζήτηση';
$lang['Rules_vote_can'] = '<b>Έχετε</b> δικαίωμα ψήφου στα δημοψηφίσματα αυτής της Δ.Συζήτησης';
$lang['Rules_vote_cannot'] = '<b>Δεν έχετε</b> δικαίωμα ψήφου στα δημοψηφίσματα αυτής της Δ.Συζήτησης';
$lang['Rules_moderate'] = '<b>Μπορείτε</b> να είστε %sΣυντονιστής σ\'αυτή τη Δ.Συζήτηση%s'; // %s replaced by a href links, do not remove! 

$lang['No_topics_post_one'] = 'Δεν υπάρχουν Θέματα προς συζήτηση αυτήν τη στιγμή. <br />Μπορείτε να ξεκινήσετε ένα πατώντας <b>Δημοσίευση νέας Θ.Ενότητας</b>';


//
// Viewtopic
//
$lang['View_topic'] = 'Επισκόπηση Θ.Ενότητας';

$lang['Guest'] = 'Επισκέπτης';
$lang['Post_subject'] = 'Θέμα δημοσίευσης';
$lang['View_next_topic'] = 'Επισκόπηση επόμενης Θ.Ενότητας';
$lang['View_previous_topic'] = 'Επισκόπηση προηγούμενης Θ.Ενότητας';
$lang['Submit_vote'] = 'Υποβολή ψήφου';
$lang['View_results'] = 'Επισκόπηση αποτελεσμάτων';

$lang['No_newer_topics'] = 'Δεν υπάρχουν νεότερα Θέματα σ\' αυτή την Δ.Συζήτηση';
$lang['No_older_topics'] = 'Δεν υπάρχουν παλαιότερα Θέματα σ\' αυτή την Δ.Συζήτηση';
$lang['Topic_post_not_exist'] = 'Η Θ.Ενότητα ή η δημοσίευση που ζητήσατε δεν υπάρχει ';
$lang['No_posts_topic'] = 'Δεν υπάρχουν Δημοσιεύσεις γι\' αυτή τη Θ.Ενότητα';

$lang['Display_posts'] = 'Επισκόπηση όλων των Δημοσιεύσεων που έγιναν πριν από';
$lang['All_Posts'] = 'Όλες οι Δημοσιεύσεις';
$lang['Newest_First'] = 'Πρώτα οι νεώτερες';
$lang['Oldest_First'] = 'Πρώτα οι παλαιότερες';

$lang['Back_to_top'] = 'Επιστροφή στην κορυφή';

$lang['Read_profile'] = 'Επισκόπηση του προφίλ των χρηστών'; 
$lang['Send_email'] = 'Αποστολή email';
$lang['Visit_website'] = 'Επίσκεψη στην ιστοσελίδα του Συγγραφέα';
$lang['ICQ_status'] = 'ICQ Status';
$lang['Edit_delete_post'] = 'Επεξεργασία/Διαγραφή αυτού του μηνύματος';
$lang['View_IP'] = 'Εμφάνιση της IP του συγγραφέα';
$lang['Delete_post'] = 'Διαγραφή αυτής της δημοσίευσης';

$lang['wrote'] = 'έγραψε'; // proceeds the username and is followed by the quoted text
$lang['Quote'] = 'Παράθεση'; // comes before bbcode quote output.
$lang['Code'] = 'Κώδικας'; // comes before bbcode code output.

$lang['Edited_time_total'] = 'Έχει επεξεργασθεί από τον/την %s στις %s,  %d φορά'; // Last edited by me on 12 Oct 2001; edited 1 time in total
$lang['Edited_times_total'] = 'Έχει επεξεργασθεί από τον/την %s στις %s,  %d φορές συνολικά'; // Last edited by me on 12 Oct 2001; edited 2 times in total

$lang['Lock_topic'] = 'Κλείδωσε αυτήν την Θ.Ενότητα';
$lang['Unlock_topic'] = 'Ξεκλείδωσε αυτήν την Θ.Ενότητα';
$lang['Move_topic'] = 'Μετακίνησε αυτήν την Θ.Ενότητα';
$lang['Delete_topic'] = 'Διέγραψε αυτήν την Θ.Ενότητα';
$lang['Split_topic'] = 'Διαχώρισε αυτήν την Θ.Ενότητα';

$lang['Stop_watching_topic'] = 'Παύση παρακολούθησης αυτής της Θ.Ενότητας';
$lang['Start_watching_topic'] = 'Παρακολούθηση αυτής της Θ.Ενότητας για απαντήσεις';
$lang['No_longer_watching'] = 'Αυτήν την Θ.Ενότητα δεν την παρακολουθείτε πλέον';
$lang['You_are_watching'] = 'Αυτήν την Θ.Ενότητα  την παρακολουθείτε';

$lang['Total_votes'] = 'Σύνολο Ψήφων';

//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = 'Περιεχόμενο';
$lang['Topic_review'] = 'Ανασκόπηση Θέματος';

$lang['No_post_mode'] = 'Δεν προσδιορίσθηκε η μέθοδος της Δημοσίευσης'; // If posting.php is called without a mode (newtopic/reply/delete/etc, shouldn't be shown normaly)

$lang['Post_a_new_topic'] = 'Δημοσίευση νέου Θέματος';
$lang['Post_a_reply'] = 'Δημοσίευση απάντησης';
$lang['Post_topic_as'] = 'Δημοσίευση Θέματος ως';
$lang['Edit_Post'] = 'Επεξεργασία Δημοσίευσης';
$lang['Options'] = 'Επιλογές';

$lang['Post_Announcement'] = 'Ανακοίνωση';
$lang['Post_Sticky'] = 'Σημείωση';
$lang['Post_Normal'] = 'Απλό';

$lang['Confirm_delete'] = 'Είστε σίγουρος ότι επιθυμείτε να διαγράψετε αυτό το μήνυμα  ;';
$lang['Confirm_delete_poll'] = 'Είστε σίγουρος ότι επιθυμείτε να διαγράψετε αυτό το δημοψήφισμα ;';

$lang['Flood_Error'] = 'Δεν μπορείτε να επαναδημοσιεύσετε τόσο σύντομα, δοκιμάστε σε λίγο';
$lang['Empty_subject'] = 'Δεν έχετε γράψει θέμα στη δημοσίευσή σας';
$lang['Empty_message'] = 'Δεν υπάρχει περιεχόμενο στο μήνυμά σας';
$lang['Forum_locked'] = 'Αυτή η συζήτηση έχει κλειδώσει, δεν μπορείτε να δημοσιεύσετε, να απαντήσετε ή να επεξεργασθείτε θέμα σ\' αυτή';
$lang['Topic_locked'] = 'Αυτή η Θ.Ενότητα έχει κλειδώσει, δεν μπορείτε να απαντήσετε ή να επεξεργασθείτε συζήτηση σ\' αυτή';
$lang['No_post_id'] = 'Δεν έχει ορισθεί ID Δημοσίευσης';
$lang['No_topic_id'] = 'Πρέπει να επιλέξετε μια Θ.Ενότητα για να απαντήσετε σ\' αυτή';
$lang['No_valid_mode'] = 'Μπορείτε μόνο να δημοσιεύσετε, να απαντήσετε, να επεξεργασθείτε ή να επισυνάψετε μηνύματα, παρακαλώ επιστρέψτε και ξαναδοκιμάστε';
$lang['No_such_post'] = 'Δεν υπάρχει παρόμοια δημοσίευση, παρακαλώ επιστρέψτε και ξαναδοκιμάστε';
$lang['Edit_own_posts'] = 'Συγνώμη, μπορείτε να επεξεργασθείτε μόνο τα δικά σας μηνύματα';
$lang['Delete_own_posts'] = 'Συγνώμη, μπορείτε να διαγράψετε μόνο τα δικά σας μηνύματα';
$lang['Cannot_delete_replied'] = 'Συγνώμη, εάν θέλετε μην διαγράφετε τα μηνύματα τα οποία έχουν απαντηθεί';
$lang['Cannot_delete_poll'] = 'Συγνώμη, δεν μπορείτε να διαγράψετε ένα δημοψήφισμα εφόσον είναι ενεργό';
$lang['Empty_poll_title'] = 'Πρέπει να εισαγάγετε έναν τίτλο στο δημοψήφισμα σας';
$lang['To_few_poll_options'] = 'Πρέπει να εισαγάγετε τουλάχιστον δύο επιλογές στο δημοψήφισμα σας';
$lang['To_many_poll_options'] = 'Επιχειρείτε να εισαγάγετε υπερβολικά πολλές επιλογές στο δημοψήφισμα σας ';
$lang['Post_has_no_poll'] = 'Η δημοσίευση δεν έχει δημοψήφισμα';
$lang['Already_voted'] = 'Έχετε ήδη ψηφίσει σ΄ αυτήν την ψηφοφορία';
$lang['No_vote_option'] = 'Δώστε μια επιλογή στην ψήφο σας';

$lang['Add_poll'] = 'Εισαγωγή δημοψηφίσματος';
$lang['Add_poll_explain'] = 'Εάν δεν επιθυμείτε να εισαγάγετε δημοψήφισμα στη Θ.Ενότητα, αφήστε αυτό το πεδίο κενό';
$lang['Poll_question'] = 'Θέμα δημοψηφίσματος';
$lang['Poll_option'] = 'Επιλογές δημοψηφίσματος';
$lang['Add_option'] = 'Προσθήκη επιλογής';
$lang['Update'] = 'Ενημέρωση';
$lang['Delete'] = 'Διαγραφή';
$lang['Poll_for'] = 'Διεξαγωγή δημοψηφίσματος για';
$lang['Days'] = 'Ημέρες'; // This is used for the Run poll for ... Days + in admin_forums for pruning
$lang['Poll_for_explain'] = '[ Εισαγάγετε 0 ή αφήστε το κενό, για δημοψήφισμα δίχως χρονικό όριο ]';
$lang['Delete_poll'] = 'Διαγραφή δημοψηφίσματος';

$lang['Disable_HTML_post'] = 'Απενεργοποίηση HTML σ\' αυτή τη δημοσίευση';
$lang['Disable_BBCode_post'] = 'Απενεργοποίηση BBCode σ\' αυτή τη δημοσίευση';
$lang['Disable_Smilies_post'] = 'Απενεργοποίηση Smilies σ\' αυτή τη δημοσίευση';

$lang['HTML_is_ON'] = 'HTML  <u>Ενεργό</u>';
$lang['HTML_is_OFF'] = 'HTML  <u>Ανενεργό</u>';
$lang['BBCode_is_ON'] = '%sBBCode%s <u>Ενεργό</u>'; // %s are replaced with URI pointing to FAQ
$lang['BBCode_is_OFF'] = '%sBBCode%s <u>Ανενεργό</u>';
$lang['Smilies_are_ON'] = 'Smilies <u>Ενεργά</u>';
$lang['Smilies_are_OFF'] = 'Smilies <u>Ανενεργά</u>';

$lang['Attach_signature'] = 'Προσάρτηση υπογραφής (η υπογραφή μπορεί να αλλάξει από το Προφίλ)';
$lang['Notify'] = 'Να ενημερωθώ όταν δημοσιευθεί απάντηση';
$lang['Delete_post'] = 'Διαγραφή αυτής της δημοσίευσης';

$lang['Stored'] = 'Το μήνυμα σας καταχωρήθηκε επιτυχώς';
$lang['Deleted'] = 'Το μήνυμα σας διαγράφηκε επιτυχώς';
$lang['Poll_delete'] = 'Το δημοψήφισμα σας διαγράφηκε επιτυχώς';
$lang['Vote_cast'] = 'Η ψήφος σας καταχωρήθηκε';

$lang['Topic_reply_notification'] = 'Γνωστοποίηση απάντησης σε Θ.Ενότητα';

$lang['bbcode_b_help'] = 'Έντονο κείμενο: [b]κείμενο[/b]  (alt+b)';
$lang['bbcode_i_help'] = 'Πλάγια γραφή: [i]κείμενο[/i]  (alt+i)';
$lang['bbcode_u_help'] = 'Υπογραμμισμένο κείμενο: [u]κείμενο[/u]  (alt+u)';
$lang['bbcode_q_help'] = 'Κείμενο σε παράθεση: [quote]κείμενο[/quote]  (alt+q)';
$lang['bbcode_c_help'] = 'Εμφάνιση κώδικα: [code]κώδικας[/code]  (alt+c)';
$lang['bbcode_l_help'] = 'Λίστα: [list]Κείμενο[/list] (alt+l)';
$lang['bbcode_o_help'] = 'Ταξινομημένη λίστα: [list=]κείμενο[/list]  (alt+o)';
$lang['bbcode_p_help'] = 'Εισαγωγή εικόνας: [img]http://image_url[/img]  (alt+p)';
$lang['bbcode_w_help'] = 'Εισαγωγή URL: [url]http://url[/url] ή [url=http://url]URL text[/url]  (alt+w)';
$lang['bbcode_a_help'] = 'Να κλείσουν όλα τα ανοιχτά bbCode tags';
$lang['bbcode_s_help'] = 'Χρώμα γραμματοσειράς: [color=red]κείμενο[/color]  Τεχ: επίσης μπορείτε να χρησιμοποιήσετε color=#FF0000';
$lang['bbcode_f_help'] = 'Μέγεθος γραμματοσειράς: [size=x-small]μικρή γραμματοσειρά[/size]';

$lang['Emoticons'] = 'Emoticons';
$lang['More_emoticons'] = 'Περισσότερα Emoticons';

$lang['Font_color'] = 'Χρώμα γραμματοσειράς';
$lang['color_default'] = 'Προεπιλογή';
$lang['color_dark_red'] = 'Βαθύ Κόκκινο';
$lang['color_red'] = 'Κόκκινο';
$lang['color_orange'] = 'Πορτοκαλί';
$lang['color_brown'] = 'Καφέ';
$lang['color_yellow'] = 'Κίτρινο';
$lang['color_green'] = 'Πράσινο';
$lang['color_olive'] = 'Λαδί';
$lang['color_cyan'] = 'Κυανό';
$lang['color_blue'] = 'Μπλέ';
$lang['color_dark_blue'] = 'Βαθύ Μπλέ';
$lang['color_indigo'] = 'Λουλακί';
$lang['color_violet'] = 'Βιολετί';
$lang['color_white'] = 'Λευκό';
$lang['color_black'] = 'Μαύρο';

$lang['Font_size'] = 'Μέγεθος γραμματοσειράς';
$lang['font_tiny'] = 'Μικροσκοπικό';
$lang['font_small'] = 'Μικρό';
$lang['font_normal'] = 'Κανονικό';
$lang['font_large'] = 'Μεγάλο';
$lang['font_huge'] = 'Τεράστιο';

$lang['Close_Tags'] = 'Να κλείσουν τα Tags';
$lang['Styles_tip'] = 'Τεχ: Τα στιλ μπορούν να εφαρμοστούν γρηγορότερα σε επιλεγμένο κείμενο';


//
// Private Messaging
//
$lang['Private_Messaging'] = 'Προσωπικά μηνύματα';

$lang['Login_check_pm'] = 'Συνδεθείτε, για να ελέγξετε την αλληλογραφία σας';
$lang['New_pms'] = 'Έχετε %d νέα μηνύματα'; // You have 2 new messages
$lang['New_pm'] = 'Έχετε %d νέο μήνυμα'; // You have 1 new message
$lang['No_new_pm'] = 'Δεν έχετε νέα μηνύματα';
$lang['Unread_pms'] = 'Έχετε %d μη αναγνωσμένα μηνύματα';
$lang['Unread_pm'] = 'Έχετε %d μη αναγνωσμένο μήνυμα';
$lang['No_unread_pm'] = 'Δεν έχετε μη αναγνωσμένα μηνύματα';
$lang['You_new_pm'] = 'Ένα προσωπικό μήνυμα σας περιμένει στο γραμματοκιβώτιο σας';
$lang['You_new_pms'] = 'Έχετε προσωπικά μηνύματα σε αναμονή στο γραμματοκιβώτιο σας';
$lang['You_no_new_pm'] = 'Δεν έχετε προσωπικά μηνύματα σε αναμονή';

$lang['Unread_message'] = 'Αναγνωσμένο';
$lang['Read_message'] = 'Μη αναγνωσμένο';

$lang['Read_pm'] = 'Ανάγνωση μηνύματος';
$lang['Post_new_pm'] = 'Αποστολή μηνύματος';
$lang['Post_reply_pm'] = 'Απάντηση στο μήνυμα';
$lang['Post_quote_pm'] = 'Απάντηση στο μήνυμα με παράθεση αυτού';
$lang['Edit_pm'] = 'Επεξεργασία μηνύματος';

$lang['Inbox'] = 'Εισερχόμενα';
$lang['Outbox'] = 'Εξερχόμενα';
$lang['Savebox'] = 'Αρχείο';
$lang['Sentbox'] = 'Απεσταλμένα';
$lang['Flag'] = 'Flag';
$lang['Subject'] = 'Θέμα';
$lang['From'] = 'Από';
$lang['To'] = 'Προς';
$lang['Date'] = 'Ημερομηνία';
$lang['Mark'] = 'Επιλογή';
$lang['Sent'] = 'Απεσταλμένα';
$lang['Saved'] = 'Αποθηκευμένο';
$lang['Delete_marked'] = 'Διαγραφή επιλεγμένων';
$lang['Delete_all'] = 'Διαγραφή όλων';
$lang['Save_marked'] = 'Αποθήκευση επιλεγμένων'; 
$lang['Save_message'] = 'Αποθήκευση μηνύματος';
$lang['Delete_message'] = 'Διαγραφή μηνύματος';

$lang['Display_messages'] = 'Εμφάνιση των μηνυμάτων, πριν από'; // Followed by number of days/weeks/months
$lang['All_Messages'] = 'Όλα τα μηνύματα';

$lang['No_messages_folder'] = 'Δεν έχετε μηνύματα σ\' αυτόν τον φάκελο';

$lang['PM_disabled'] = 'Στο σύστημα αυτό τα προσωπικά μηνύματα έχουν απενεργοποιηθεί';
$lang['Cannot_send_privmsg'] = 'Συγνώμη αλλά ο Διαχειριστής σας έχει αποκλείσει από την δυνατότητα αποστολής προσωπικών μηνυμάτων ';
$lang['No_to_user'] = 'Πρέπει να ορίσετε τον παραλήπτη χρήστη για να αποσταλεί αυτό το μήνυμα';
$lang['No_such_user'] = 'Συγνώμη δεν υπάρχει αυτός ο χρήστης';

$lang['Disable_HTML_pm'] = 'Απενεργοποίηση της HTML σε αυτό το μήνυμα';
$lang['Disable_BBCode_pm'] = 'Απενεργοποίηση του BBCode σε αυτό το μήνυμα';
$lang['Disable_Smilies_pm'] = 'Απενεργοποίηση των Smilies σε αυτό το μήνυμα';

$lang['Message_sent'] = 'Το μήνυμα σας έχει αποσταλεί';

$lang['Click_return_inbox'] = 'Πατήστε %sεδώ%s για να επιστρέψετε στο γραμματοκιβώτιο σας';
$lang['Click_return_index'] = 'Πατήστε %sεδώ%s για να επιστρέψετε στην Αρχική σελίδα';

$lang['Send_a_new_message'] = 'Αποστολή νέου προσωπικού μηνύματος';
$lang['Send_a_reply'] = 'Απάντηση σε προσωπικό μήνυμα';
$lang['Edit_message'] = 'Επεξεργασία προσωπικού μηνύματος';

$lang['Notification_subject'] = 'Έχετε ένα νέο προσωπικό μήνυμα';

$lang['Find_username'] = 'Εύρεση ονόματος χρήστη';
$lang['Find'] = 'Εύρεση';
$lang['No_match'] = 'Δεν βρέθηκαν εγγραφές';

$lang['No_post_id'] = 'Δεν έχει ορισθεί ID Δημοσίευσης';
$lang['No_such_folder'] = 'Δεν υπάρχει παρόμοιος φάκελος';
$lang['No_folder'] = 'Δεν ορίσατε φάκελο';

$lang['Mark_all'] = 'Επιλογή όλων';
$lang['Unmark_all'] = 'Αναίρεση όλων';

$lang['Confirm_delete_pm'] = 'Είστε σίγουρος ότι θέλετε να διαγράψετε αυτό το μήνυμα ;';
$lang['Confirm_delete_pms'] = 'Είστε σίγουρος ότι θέλετε να διαγράψετε αυτά τα μηνύματα;';

$lang['Inbox_size'] = 'Το γραμματοκιβώτιο σας είναι κατά  %d%% γεμάτο'; // eg. Your Inbox is 50% full
$lang['Sentbox_size'] = 'Ο φάκελος απεσταλμένα είναι κατά  %d%% γεμάτος'; 
$lang['Savebox_size'] = 'Ο φάκελος αρχείο είναι κατά  %d%% γεμάτος'; 

$lang['Click_view_privmsg'] = 'Πατήστε %sΕδώ%s για να εισέλθετε στο γραμματοκιβώτιο σας';


//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = 'Επισκόπηση προφίλ :: %s'; // %s is username 
$lang['About_user'] = 'Πληροφορίες για τον/την %s'; // %s is username

$lang['Preferences'] = 'Επιλογές';
$lang['Items_required'] = 'Τα πεδία σημειωμένα με * είναι υποχρεωτικά, εκτός εάν είναι ορισμένα διαφορετικά';
$lang['Registration_info'] = 'Πληροφορίες εγγραφής';
$lang['Profile_info'] = 'Πληροφορίες προφίλ';
$lang['Profile_info_warn'] = 'Αυτές οι πληροφορίες θα είναι ορατές στο κοινό';
$lang['Avatar_panel'] = 'Πίνακας ελέγχου Άβαταρ';
$lang['Avatar_gallery'] = 'Φωτοθήκη Άβαταρ';

$lang['Website'] = 'Ιστοσελίδα';
$lang['Location'] = 'Τόπος';
$lang['Contact'] = 'Επαφή';
$lang['Email_address'] = 'Email διεύθυνση';
$lang['Email'] = 'Email';
$lang['Send_private_message'] = 'Αποστολή προσωπικού μηνύματος';
$lang['Hidden_email'] = '[ Κρυφό ]';
$lang['Search_user_posts'] = 'Αναζήτηση δημοσιεύσεων από τον/την %s';
$lang['Interests'] = 'Ενδιαφέροντα';
$lang['Occupation'] = 'Επάγγελμα'; 
$lang['Poster_rank'] = 'Κατάταξη συγγραφέα';

$lang['Total_posts'] = 'Σύνολο δημοσιεύσεων';
$lang['User_post_pct_stats'] = '%.2f%% επί συνόλου'; // 1.25% of total
$lang['User_post_day_stats'] = '%.2f δημοσιεύσεις ανά ημέρα'; // 1.5 posts per day
$lang['Search_user_posts'] = 'Αναζήτηση δημοσιεύσεων από τον/την %s'; // Find all posts by username

$lang['No_user_id_specified'] = 'Συγνώμη, αυτός ο χρήστης δεν υπάρχει';
$lang['Wrong_Profile'] = 'Δεν μπορείτε να αλλάξετε προφίλ το οποίο δεν σας ανήκει.';

$lang['Only_one_avatar'] = 'Μόνο ένας τύπος Άβαταρ μπορεί να ορισθεί';
$lang['File_no_data'] = 'Το αρχείο στην Διεύθυνση που ορίσατε δεν υπάρχει';
$lang['No_connection_URL'] = 'Δεν μπορεί να πραγματοποιηθεί σύνδεση με τη Διεύθυνση που ορίσατε';
$lang['Incomplete_URL'] = 'Η Διεύθυνση που ορίσατε είναι ημιτελής';
$lang['Wrong_remote_avatar_format'] = 'Η απομακρυσμένη Διεύθυνση του Άβαταρ δεν είναι ακριβής';
$lang['No_send_account_inactive'] = 'Συγνώμη, ο κωδικός σας δεν μπορεί να ανακτηθεί διότι ο λογαριασμός σας είναι αδρανοποιημένος. Για περισσότερες πληροφορίες επικοινωνήστε με τον Διαχειριστή';

$lang['Always_smile'] = 'Πάντα ενεργά τα Smilies';
$lang['Always_html'] = 'Να επιτρέπετε πάντα  η HTML';
$lang['Always_bbcode'] = 'Να επιτρέπετε πάντα το BBCode';
$lang['Always_add_sig'] = 'Να προσαρτάται πάντα η υπογραφή μου';
$lang['Always_notify'] = 'Να ενημερώνομαι πάντα για απαντήσεις';
$lang['Always_notify_explain'] = 'Να αποστέλλεται email όταν κάποιος απαντά στην Θ.Ενότητα που δημοσιεύσαμε. Η επιλογή αυτή μπορεί να αλλάζει κατά βούληση κάθε φορά που δημοσιεύουμε μήνυμα ';

$lang['Board_style'] = 'Εμφάνιση επιφάνειας';
$lang['Board_lang'] = 'Γλώσσα επικοινωνίας';
$lang['No_themes'] = 'Δεν υπάρχουν μοτίβα εμφάνισης';
$lang['Timezone'] = 'Ωρολογιακή ζώνη';
$lang['Date_format'] = 'Μορφή ημερομηνίας';
$lang['Date_format_explain'] = 'Η σύνταξη είναι παρόμοια με τη σύνταξη της PHP <a href=\"http://www.php.net/date\" target=\"_other\">date()</a> function';
$lang['Signature'] = 'Υπογραφή';
$lang['Signature_explain'] = 'Αυτό είναι το τμήμα κειμένου το οποίο θα προσαρτάτε στα μηνύματα σας. Το όριο είναι  %d χαρακτήρες';
$lang['Public_view_email'] = 'Να είναι πάντα εμφανές το email μου';

$lang['Current_password'] = 'Υπάρχον κωδικός';
$lang['New_password'] = 'Νέος κωδικός';
$lang['Confirm_password'] = 'Επαλήθευση κωδικού';
$lang['Confirm_password_explain'] = 'Πρέπει να επαληθευτεί ο κωδικός σας εφόσον επιθυμείται να τον αλλάξετε ή να  τροποποιήσετε το email σας';
$lang['password_if_changed'] = 'Πρέπει να εισαγάγετε το κωδικό μόνο εφόσον σκοπεύετε να το αλλάξετε';
$lang['password_confirm_if_changed'] = 'Πρέπει να επαληθεύσετε το κωδικό μόνο εάν το αλλάξατε παραπάνω';

$lang['Avatar'] = 'Άβαταρ';
$lang['Avatar_explain'] = 'Εμφανίζετε μια μικρή εικόνα κάτω από τις λεπτομέρειες της δημοσίευση σας. Μόνο μία εικόνα μπορεί να εμφανίζετε κάθε φορά. Το μέγεθος της δεν μπορεί να ξεπερνά τα  %d εικονοστοιχεία σε πλάτος και τα %d εικονοστοιχεία σε ύψος. Το αρχείο δεν μπορεί να είναι μεγαλύτερο από %dkB.';
$lang['Upload_Avatar_file'] = 'Μεταφορά Άβαταρ από τον υπολογιστή σας';
$lang['Upload_Avatar_URL'] = 'Μεταφορά Άβαταρ από URL';
$lang['Upload_Avatar_URL_explain'] = 'Εισαγωγή URL η οποία περιέχει το Άβαταρ αρχείο, θα γίνει μεταφορά στον εξυπηρετητή μας.';
$lang['Pick_local_Avatar'] = 'Επιλογή ενός Άβαταρ από την δική μας φωτοθήκη';
$lang['Link_remote_Avatar'] = 'Σύνδεσμος για απομακρυσμένο Άβαταρ';
$lang['Link_remote_Avatar_explain'] = 'Εισαγωγή URL η οποία περιέχει το Άβαταρ αρχείο για το οποίο θέλετε να δημιουργήσετε σύνδεσμο.';
$lang['Avatar_URL'] = 'URL Άβαταρ αρχείου';
$lang['Select_from_gallery'] = 'Επιλογή ενός Άβαταρ από εικονοθήκη';
$lang['View_avatar_gallery'] = 'Παρουσίαση φωτοθήκης';

$lang['Select_avatar'] = 'Επιλογή Άβαταρ';
$lang['Return_profile'] = 'Ακύρωση Άβαταρ';
$lang['Select_category'] = 'Επιλογή κατηγορίας';

$lang['Delete_Image'] = 'Διαγραφή εικόνας';
$lang['Current_Image'] = 'Τρέχουσα εικόνα';

$lang['Notify_on_privmsg'] = 'Να ενημερώνομε για νέα προσωπικά μηνύματα';
$lang['Popup_on_privmsg'] = 'Αναδυόμενο μήνυμα για νέο προσωπικό μήνυμα'; 
$lang['Popup_on_privmsg_explain'] = 'Ορισμένες σελίδες μπορούν να ανοίξουν ένα νέο παράθυρο στο οποίο υπάρχει μια ειδοποίηση, για νέα προσωπική αλληλογραφία';
$lang['Hide_user'] = 'Απόκρυψη των στοιχείων μου κατά την διάρκεια της σύνδεσης';

$lang['Profile_updated'] = 'Το προφίλ σας έχει ενημερωθεί';
$lang['Profile_updated_inactive'] = 'Το προφίλ σας έχει ενημερωθεί, όμως ο λογαριασμός σας έγινε ανενεργός διότι αλλάξατε κρίσιμα στοιχεία. Ελέγξτε την αλληλογραφία σας για να μάθετε πώς να επανεργοποιήσετε τον λογαριασμό σας. Εάν χρειάζεται η επανεργοποίηση να γίνει από τον διαχειριστή περιμένετε έως ότου ο διαχειριστής ενεργοποιήσει ξανά τον λογαριασμό σας';

$lang['Password_mismatch'] = 'Οι κωδικοί που εισαγάγατε δεν ταιριάζουν';
$lang['Current_password_mismatch'] = 'Ο κωδικός που εισαγάγατε δεν ταιριάζει με αυτόν που είναι αποθηκευμένος στην βάση δεδομένων μας';
$lang['Password_long'] = 'Ο κωδικός σας δεν πρέπει να υπερβαίνει τους 32 χαρακτήρες';
$lang['Username_taken'] = 'Συγνώμη, το όνομα μέλους που δώσατε χρησιμοποιείται ήδη';
$lang['Username_invalid'] = 'Συγνώμη, το όνομα μέλους που δώσατε περιέχει μη επιτρεπτούς χαρακτήρες όπως οι \"';
$lang['Username_disallowed'] = 'Συγνώμη, το όνομα που δώσατε δεν επιτρέπετε';
$lang['Email_taken'] = 'Συγνώμη, το email αυτό ανήκει ήδη σε μέλος';
$lang['Email_banned'] = 'Συγνώμη, στο email αυτό έχει απαγορευθεί η συμμετοχή';
$lang['Email_invalid'] = 'Συγνώμη, το email αυτό είναι μη έγκυρο';
$lang['Signature_too_long'] = 'Η υπογραφή σας είναι υπερβολικά μεγάλη';
$lang['Fields_empty'] = 'Πρέπει να συμπληρωθούν όλα τα υποχρεωτικά πεδία';
$lang['Avatar_filetype'] = 'Ο τύπος του αρχείου Άβαταρ πρέπει να είναι .jpg, .gif ή .png';
$lang['Avatar_filesize'] = 'Το μέγεθος του αρχείου Άβαταρ πρέπει να είναι μικρότερο από %d kB'; // The avatar image file size must be less than 6 KB
$lang['Avatar_imagesize'] = 'Το μέγεθος του Άβαταρ δεν μπορεί να ξεπερνά τα %d εικονοστοιχεία σε πλάτος και τα %d εικονοστοιχεία σε ύψος'; 

$lang['Welcome_subject'] = 'Το %s σας καλωσορίζει στην δημόσια συζήτηση του'; // Welcome to my.com forums
$lang['New_account_subject'] = 'Λογαριασμός νέου χρήστη';
$lang['Account_activated_subject'] = 'Ο λογαριασμός Ενεργοποιήθηκε';

$lang['Account_added'] = 'Σας ευχαριστούμε για την εγγραφή σας, ο λογαριασμός σας έχει δημιουργηθεί. Μπορείτε από τώρα να συνδέεστε χρησιμοποιώντας το όνομα χρήστη και τον κωδικό σας';
$lang['Account_inactive'] = 'Ο λογαριασμός σας έχει δημιουργηθεί. Παρόλα αυτά πρέπει να τον ενεργοποιήσετε, το κλειδί της ενεργοποίησης θα σας αποσταλεί στην ηλεκτρονική διεύθυνση που μας δηλώσατε. Για πληροφορίες των περαιτέρω ενεργειών παρακαλώ ελέγξτε το email σας ';
$lang['Account_inactive_admin'] = 'Ο λογαριασμός σας έχει δημιουργηθεί. Παρόλα αυτά απαιτείτε η ενεργοποίηση του από τον διαχειριστή. Έχει αποσταλεί ένα μήνυμα σε αυτόν και θα ειδοποιηθείτε όταν ο λογαριασμός σας θα ενεργοποιηθεί';
$lang['Account_active'] = 'Ο λογαριασμός σας ενεργοποιήθηκε. Σας ευχαριστούμε για ην εγγραφή σας';
$lang['Account_active_admin'] = 'Ο λογαριασμός ενεργοποιήθηκε';
$lang['Reactivate'] = 'Επανεργοποιήστε τον λογαριασμό σας !';
$lang['Already_activated'] = 'Ο λογαριασμός σας είναι ήδη ενεργός';
$lang['COPPA'] = 'Ο λογαριασμό σας δημιουργήθηκε, πρέπει όμως να εγκριθεί. Ελέγξτε το email σας για περισσότερες πληροφορίες.';

$lang['Registration'] = 'Όροι Εγγραφής';
$lang['Reg_agreement'] = 'Οι διαχειριστές και οι συντονιστές προσπαθούν να διατηρούν το περιεχόμενο αυτής της Δ. Συζήτησης καθαρό από μεμπτό περιεχόμενο επεμβαίνοντας  και ενίοτε διαγράφοντάς το. Επειδή  όμως είναι αδύνατο να ελέγχονται όλα τα μηνύματα που αναρτώνται, γι\' αυτό αποδέχεστε πως ό,τι αναρτάται εκφράζει μόνο τον δημιουργό του μηνύματος και όχι την άποψη των διαχειριστών, των συντονιστών και του webmaster (εκτός από τα μηνύματα τα οποία αναρτήθηκαν από αυτούς) και δεν φέρουν καμία ευθύνη γι\' αυτά.<br /><br />Συμφωνείτε να μην αναρτάτε μηνύματα με υβριστικό, άσεμνο, πρόστυχο, χυδαίο, συκοφαντικό, απεχθές, απειλητικό, με πορνογραφικό προσανατολισμό ή οποιοδήποτε άλλο περιεχόμενο που υπόκειται στην νομοθετική αρχή. Μη συμμόρφωση θα οδηγήσει σε άμεση και μόνιμη διαγραφή του μέλους (με γνωστοποίηση της ενέργειάς του στο φορέα παροχής υπηρεσιών Internet μέσω του οποίου συνδεθήκατε). Η διεύθυνση IP του εκάστοτε μηνύματος καταγράφεται για να διασφαλιστεί το περιεχόμενο των αναρτήσεων όπως ήδη ορίστηκε. Συμφωνείτε ότι ο webmaster, ο διαχειριστής ή ο συντονιστής έχουν το δικαίωμα να διαγράψουν, να επεξεργαστούν ή να μετακινήσουν οποιαδήποτε ανάρτηση ή Θ. Ενότητα το περιεχόμενο της οποίας δεν πληροί τους όρους χρήσης. Ως μέλος αποδέχεστε να αποθηκευτούν οι πληροφορίες, τις οποίες θα εισαγάγετε, σε βάση δεδομένων. Οι πληροφορίες αυτές δεν πρόκειται να γνωστοποιηθούν σε τρίτο πρόσωπο ή φορέα χωρίς την άδειά σας, όμως ο webmaster, ο διαχειριστής ή ο συντονιστής δεν φέρει την ευθύνη απώλειάς τους από κακόβουλους χρήστες σε περίπτωση εισβολής τους στον διακομιστή.<br /><br />Αυτό το σύστημα της Δ. Συζήτησης βασίζεται σε cookies τα οποία αποθηκεύονται στον υπολογιστή σας. Τα cookies αυτά δεν περιέχουν καμία απολύτως πληροφορία από τα στοιχεία που θα εισαγάγετε παρακάτω, εξυπηρετούν μόνο στο να βελτιωθεί η περιήγησή σας στην Δ. συζήτηση. Η e-mail διεύθυνσή σας χρησιμοποιείται μόνο για την επιβεβαίωση των στοιχείων εγγραφής σας και του κωδικού σας, καθώς και για την αποστολή νέου κωδικού σε περίπτωση που λησμονήσετε το τρέχοντα ενεργό κωδικό σας.<br /><br />Με την εγγραφή σας δεσμεύεστε με τους ανωτέρω όρους.';

$lang['Agree_under_13'] = 'Συμφωνώ με τα παραπάνω και είμαι <b>ΚΑΤΩ</b> των 13 ετών';
$lang['Agree_over_13'] = 'Συμφωνώ με τα παραπάνω και είμαι <b>ΑΝΩ</b> των 13 ετών';
$lang['Agree_not'] = 'Δεν συμφωνώ με τους παραπάνω όρους';

$lang['Wrong_activation'] = 'Το κλειδί ενεργοποίησης που εισαγάγατε δεν ταιριάζει με κανένα  κλειδί της βάσης δεδομένων';
$lang['Send_password'] = 'Αποστολή νέου κωδικού '; 
$lang['Password_updated'] = 'Ο νέος κωδικός δημιουργήθηκε, ελέγξτε το email σας για να μάθετε πως θα το ενεργοποιήσετε ';
$lang['No_email_match'] = 'Η email διεύθυνση που εισαγάγατε δεν αντιστοιχεί σε αυτόν τον χρήστη';
$lang['New_password_activation'] = 'Ενεργοποίηση νέου κωδικού';
$lang['Password_activated'] = 'Ο λογαριασμός σας επανεργοποιήθηκε. Για να συνδεθείτε χρησιμοποιήστε τον κωδικό που λάβατε με την αλληλογραφία σας';

$lang['Send_email_msg'] = 'Αποστολή μηνύματος email';
$lang['No_user_specified'] = 'Δεν ορίσατε χρήστη';
$lang['User_prevent_email'] = 'Ο χρήστης αυτός δεν επιθυμεί την λήψη email. Δοκιμάστε να του στείλετε προσωπικό μήνυμα';
$lang['User_not_exist'] = 'Δεν υπάρχει αυτός ο χρήστης';
$lang['CC_email'] = 'Αποστολή αντιγράφου στο δικό μου email';
$lang['Email_message_desc'] = 'Το μήνυμα αυτό θα αποσταλεί με απλό κείμενο χωρίς μορφοποίηση HTML ή BBCode. Η διεύθυνση επιστροφής για αυτό το μήνυμα θα σας αποσταλεί στην email διεύθυνση σας. ';
$lang['Flood_email_limit'] = 'Δεν μπορείτε να ξαναστείλετε email αυτή τη στιγμή, δοκιμάστε αργότερα';
$lang['Recipient'] = 'Παραλήπτης';
$lang['Email_sent'] = 'Το email απεστάλη';
$lang['Send_email'] = 'Αποστολή email';
$lang['Empty_subject_email'] = 'Πρέπει να ορίσετε το θέμα του email';
$lang['Empty_message_email'] = 'Πρέπει να εισαγάγετε το μήνυμα σας για να αποσταλεί';


//
// Memberslist
//
$lang['Select_sort_method'] = 'Επέλεξε μέθοδο ταξινόμησης';
$lang['Sort'] = 'Ταξινόμησε';
$lang['Sort_Top_Ten'] = 'Οι δέκα πρώτοι συγγραφείς';
$lang['Sort_Joined'] = 'Ημερομηνία εγγραφής';
$lang['Sort_Username'] = 'Όνομα μέλους';
$lang['Sort_Location'] = 'Τοποθεσία';
$lang['Sort_Posts'] = 'Σύνολο δημοσιεύσεων';
$lang['Sort_Email'] = 'Email';
$lang['Sort_Website'] = 'Ιστοσελίδα';
$lang['Sort_Ascending'] = 'Αύξουσα';
$lang['Sort_Descending'] = 'Φθίνουσα';
$lang['Order'] = 'Σειρά';


//
// Group control panel
//
$lang['Group_Control_Panel'] = 'Πίνακας Ελέγχου Ομάδων';
$lang['Group_member_details'] = 'Λεπτομέρειες ιδιότητας μέλους ομάδων';
$lang['Group_member_join'] = 'Ένταξη σε ομάδα';

$lang['Group_Information'] = 'Πληροφορίες ομάδας';
$lang['Group_name'] = 'Όνομα ομάδας';
$lang['Group_description'] = 'Περιγραφή ομάδας';
$lang['Group_membership'] = 'Ιδιότητα μέλους ομάδας';
$lang['Group_Members'] = 'Μέλη ομάδας';
$lang['Group_Moderator'] = 'Συντονιστής Ομάδας';
$lang['Pending_members'] = 'Μέλη σε εκκρεμότητα';

$lang['Group_type'] = 'Τύπος Ομάδας';
$lang['Group_open'] = 'Ανοικτή ομάδα';
$lang['Group_closed'] = 'Κλειστή ομάδα';
$lang['Group_hidden'] = 'Κρυφή ομάδα';

$lang['Current_memberships'] = 'Τρέχουσες ιδιότητες μέλους';
$lang['Non_member_groups'] = 'Ομάδες με μέλη';
$lang['Memberships_pending'] = 'Ιδιότητες Μελών σε εκκρεμότητα';

$lang['No_groups_exist'] = 'Δεν υπάρχουν Ομάδες';
$lang['Group_not_exist'] = 'Αυτή η ομάδα χρηστών δεν υπάρχει';

$lang['Join_group'] = 'Ένωση με Ομάδα';
$lang['No_group_members'] = 'Αυτή η ομάδα δεν έχει μέλη';
$lang['Group_hidden_members'] = 'Αυτή η ομάδα είναι κρυφή δεν μπορείτε να δείτε τα μέλη της';
$lang['No_pending_group_members'] = 'Αυτή η ομάδα δεν έχει μέλη σε εκκρεμότητα';
$lang['Group_joined'] = 'Εγγραφήκατε επιτυχώς σ\' αυτή τη ομάδα<br/>Μόλις εγκριθεί η συμμετοχή σας στην ομάδα αυτή θα ειδοποιηθείτε από τον συντονιστή ομάδας';
$lang['Group_request'] = 'Έλήφθη αίτηση ένταξης στην ομάδα σας ';
$lang['Group_approved'] = 'Η αίτηση σας εγκρίθηκε';
$lang['Group_added'] = 'Έχετε προστεθεί στην λίστα χρηστών αυτής της ομάδας '; 
$lang['Already_member_group'] = 'Είστε ήδη μέλος αυτής της ομάδας';
$lang['User_is_member_group'] = 'Ο χρήστης αυτός είναι ήδη μέλος αυτής της ομάδας';
$lang['Group_type_updated'] = 'Ο χαρακτήρας της ομάδας ανανεώθηκε επιτυχώς';

$lang['Could_not_add_user'] = 'Ο χρήστης που επιλέξατε δεν υπάρχει';
$lang['Could_not_anon_user'] = 'Δεν μπορείτε να εντάξετε σε ομάδα ανώνυμο χρήστη';

$lang['Confirm_unsub'] = 'Είστε σίγουρος ότι θέλετε να ακυρώσετε την συμμετοχή σας σ\' αυτήν την ομάδα;';
$lang['Confirm_unsub_pending'] = 'Η συμμετοχή σας σ\' αυτήν την ομάδα δεν έχει εγκριθεί ακόμα. Είστε σίγουρος ότι θέλετε να ακυρώσετε την συμμετοχή σας σ\' αυτήν την ομάδα;';

$lang['Unsub_success'] = 'Έχετε αποχωρήσει από την ομάδα.';

$lang['Approve_selected'] = 'Ενέκρινε τα σημειωμένα';
$lang['Deny_selected'] = 'Απέρριψε τα σημειωμένα';
$lang['Not_logged_in'] = 'Πρέπει να είστε συνδεδεμένος για ενταχθείτε στην ομάδα.';
$lang['Remove_selected'] = 'Απομάκρυνε τα σημειωμένα';
$lang['Add_member'] = 'Προσθήκη μέλους';
$lang['Not_group_moderator'] = 'Δεν είστε συντονιστής αυτής της ομάδας, για αυτό δεν μπορείτε να ολοκληρώσετε αυτήν την ενέργεια.';

$lang['Login_to_join'] = 'Πρέπει να συνδεθείτε για να μπορέσετε, να ενωθείτε ή να διαχειρισθείτε το σύνολο μελών ομάδας';
$lang['This_open_group'] = 'Αυτή είναι μια ανοικτή ομάδα πατήστε εδώ για να ζητήσετε την ένταξη σας σε αυτή';
$lang['This_closed_group'] = 'Αυτή η ομάδα είναι κλειστή δεν δέχεται άλλα μέλη';
$lang['This_hidden_group'] = 'Αυτή η ομάδα είναι κρυφή δεν δέχεται μέλη με αυτοματοποιημένη μέθοδο';
$lang['Member_this_group'] = 'Είστε μέλος αυτής της ομάδας';
$lang['Pending_this_group'] = 'Η ένταξη σας στην ομάδα προωθείτε';
$lang['Are_group_moderator'] = 'Είστε Συντονιστής Ομάδας';
$lang['None'] = 'Κανένας';

$lang['Subscribe'] = 'Εγγραφή';
$lang['Unsubscribe'] = 'Τερματισμός εγγραφής';
$lang['View_Information'] = 'Επισκόπηση ομάδας';


//
// Search
//
$lang['Search_query'] = 'Ερώτημα αναζήτησης';
$lang['Search_options'] = 'Επιλογές αναζήτησης';

$lang['Search_keywords'] = 'Αναζήτηση λέξεων κλειδιών';
$lang['Search_keywords_explain'] = 'Μπορείτε να χρησιμοποιήσετε το <u>AND</u> Για να συμπεριλάβετε τις λέξεις που πρέπει να βρίσκονται στο αποτέλεσμα, <u>OR</u> Για να συμπεριλάβετε τις λέξεις που μπορούν να βρίσκονται στο αποτέλεσμα <u>NOT</u> Για να συμπεριλάβετε τις λέξεις που δεν πρέπει να βρίσκονται στο αποτέλεσμα. Ο χαρακτήρας * χρησιμοποιείται ως μπαλαντέρ';
$lang['Search_author'] = 'Αναζήτηση αποστολέα';
$lang['Search_author_explain'] = 'Ο χαρακτήρας * χρησιμοποιείται ως μπαλαντέρ';

$lang['Search_for_any'] = 'Αναζήτησε οποιονδήποτε όρο ή όπως εισήχθη το ερώτημα';
$lang['Search_for_all'] = 'Αναζήτησε όλους τους όρους';
$lang['Search_title_msg'] = 'Αναζήτησε σε θέμα και στο κείμενο του μηνύματος';
$lang['Search_msg_only'] = 'Αναζήτησε μόνο στο κείμενο του μηνύματος';

$lang['Return_first'] = 'Επιστροφή των πρώτων'; // followed by xxx characters in a select box
$lang['characters_posts'] = 'χαρακτήρων δημοσιεύματος';

$lang['Search_previous'] = 'Αναζήτηση πριν από'; // followed by days, weeks, months, year, all in a select box

$lang['Sort_by'] = 'Ταξινόμηση κατά';
$lang['Sort_Time'] = 'Ώρα δημοσίευσης';
$lang['Sort_Post_Subject'] = 'Θέμα δημοσίευσης';
$lang['Sort_Topic_Title'] = 'Τίτλος Θ.Ενότητας';
$lang['Sort_Author'] = 'Συγγραφέας';
$lang['Sort_Forum'] = 'Δ.Συζήτηση';

$lang['Display_results'] = 'Εμφάνισε τα αποτελέσματα κατά:';
$lang['All_available'] = 'Όλα τα διαθέσιμα';
$lang['No_searchable_forums'] = 'Δεν έχετε το δικαίωμα να αναζητήσετε σε καμία Δ.Συζήτηση στις σελίδες μας';

$lang['No_search_match'] = 'Δεν βρέθηκαν ούτε δημοσιεύσεις ούτε Θ.Ενότητες κατά το ερώτημα σας';
$lang['Found_search_match'] = 'Η αναζήτηση βρήκε %d εγγραφή'; // eg. Search found 1 match
$lang['Found_search_matches'] = 'Η αναζήτηση βρήκε %d εγγραφές'; // eg. Search found 24 matches

$lang['Close_window'] = 'Κλείσιμο παραθύρου';


//
// Auth related entries
//
// Note the %s will be replaced with one of the following 'user' arrays
$lang['Sorry_auth_announce'] = 'Συγνώμη αλά μόνο οι %s μπορούν να δημοσιεύσουν ανακοινώσεις σ\' αυτή την Δ.Συζήτηση';
$lang['Sorry_auth_sticky'] = 'Συγνώμη αλά μόνο οι %s μπορούν να επικολλήσουν μηνύματα σ\' αυτή την Δ.Συζήτηση'; 
$lang['Sorry_auth_read'] = 'Συγνώμη αλά μόνο οι %s μπορούν να διαβάσουν τα μηνύματα σ\' αυτή την Δ.Συζήτηση'; 
$lang['Sorry_auth_post'] = 'Συγνώμη αλά μόνο οι %s μπορούν να δημοσιεύσουν σ\' αυτή την Δ.Συζήτηση'; 
$lang['Sorry_auth_reply'] = 'Συγνώμη αλά μόνο οι %s μπορούν να απαντήσουν σε δημοσίευση σ\' αυτή την Δ.Συζήτηση';
$lang['Sorry_auth_edit'] = 'Συγνώμη αλά μόνο οι %s μπορούν να επεξεργαστούν μηνύματα σ\' αυτή την Δ.Συζήτηση'; 
$lang['Sorry_auth_delete'] = 'Συγνώμη αλά μόνο οι %s μπορούν διαγράψουν μήνυμα σ\' αυτή την Δ.Συζήτηση';
$lang['Sorry_auth_vote'] = 'Συγνώμη αλά μόνο οι %s μπορούν να συμμετάσχουν σε δημοψήφισμα σ\' αυτή την Δ.Συζήτηση';

// These replace the %s in the above strings
$lang['Auth_Anonymous_Users'] = '<b>ανώνυμοι χρήστες</b>';
$lang['Auth_Registered_Users'] = '<b>εγγεγραμμένοι χρήστες</b>';
$lang['Auth_Users_granted_access'] = '<b>χρήστες με δικαίωμα ειδικής πρόσβασης</b>';
$lang['Auth_Moderators'] = '<b>Συντονιστές</b>';
$lang['Auth_Administrators'] = '<b>Διαχειριστές</b>';

$lang['Not_Moderator'] = 'Δεν είστε Συντονιστής σε αυτή τη Δ.Συζήτηση';
$lang['Not_Authorised'] = 'Μη εξουσιοδοτημένος';

$lang['You_been_banned'] = 'Η συμμετοχή σ\' αυτή την Δ.Συζήτηση σας έχει απαγορευθεί<br />Επικοινωνήστε με τον διαχειριστή για περισσότερες πληροφορίες';


//
// Viewonline
//
$lang['Reg_users_zero_online'] = 'Συνδεδεμένοι είναι 0 Μέλη και '; // There are 5 Registered and
$lang['Reg_users_online'] = 'Συνδεδεμένοι είναι %d Μέλη και '; // There are 5 Registered and
$lang['Reg_user_online'] = 'Είναι συνδεδεμένο %d Μέλος και '; // There is 1 Registered and
$lang['Hidden_users_zero_online'] = '0 Κρυφοί χρήστες'; // 6 Hidden users online
$lang['Hidden_users_online'] = '%d Κρυφοί χρήστες'; // 6 Hidden users online
$lang['Hidden_user_online'] = '%d Κρυφός χρήστης'; // 6 Hidden users online
$lang['Guest_users_online'] = 'Συνδεδεμένοι είναι %d Επισκέπτες'; // There are 10 Guest users online
$lang['Guest_users_zero_online'] = 'Συνδεδεμένοι είναι 0 Επισκέπτες'; // There are 10 Guest users online
$lang['Guest_user_online'] = 'Συνδεδεμένος είναι %d Επισκέπτης'; // There is 1 Guest user online
$lang['No_users_browsing'] = 'Δεν υπάρχουν συνδεδεμένοι χρήστες αυτή την στιγμή';

$lang['Online_explain'] = 'Τα δεδομένα αυτά προέρχονται από χρήστες που συνδεθήκανε τα τελευταία πέντε λεπτά';

$lang['Forum_Location'] = 'Τοποθεσία Δ.Συζήτησης';
$lang['Last_updated'] = 'Τελευταία ανανέωση';

$lang['Forum_index'] = 'Ευρετήριο Δ.Συζήτησης';
$lang['Logging_on'] = 'Σύνδεση';
$lang['Posting_message'] = 'Δημοσίευση μηνύματος';
$lang['Searching_forums'] = 'Αναζήτηση σε Δ.Συζητήσης';
$lang['Viewing_profile'] = 'Επισκόπηση προφίλ';
$lang['Viewing_online'] = 'Επισκόπηση χρηστών υπό σύνδεση';
$lang['Viewing_member_list'] = 'Επισκόπηση καταλόγου μελών';
$lang['Viewing_priv_msgs'] = 'Επισκόπηση προσωπικών μηνυμάτων';
$lang['Viewing_FAQ'] = 'Επισκόπηση συχνών ερωτήσεων';


//
// Moderator Control Panel
//
$lang['Mod_CP'] = 'Πίνακας ελέγχου συντονιστή';
$lang['Mod_CP_explain'] = 'Χρησιμοποιώντας τον παρακάτω πίνακα μπορείτε να πραγματοποιήσετε πλήθος ενεργειών σ\' αυτή τη Δ.Συζήτηση. Μπορείτε να κλειδώσετε να ξεκλειδώσετε να μετακινήσετε ή ακόμα και να διαγράψετε οποιονδήποτε αριθμό μηνυμάτων.';

$lang['Select'] = 'Επιλογή';
$lang['Delete'] = 'Διαγραφή';
$lang['Move'] = 'Μετακίνηση';
$lang['Lock'] = 'Κλείδωμα';
$lang['Unlock'] = 'Ξεκλείδωμα';

$lang['Topics_Removed'] = 'Οι επιλεγμένες Θ. Ενότητες διαγράφηκαν με επιτυχία από την Βάση Δεδομένων.';
$lang['Topics_Locked'] = 'Οι επιλεγμένες Θ. Ενότητες έχουν κλειδώσει';
$lang['Topics_Moved'] = 'Οι επιλεγμένες Θ. Ενότητες μετακινήθηκαν';
$lang['Topics_Unlocked'] = 'Οι επιλεγμένες Θ. Ενότητες έχουν ξεκλειδώσει';
$lang['No_Topics_Moved'] = 'Οι Θ. Ενότητες δεν μετακινήθηκαν';

$lang['Confirm_delete_topic'] = 'Είστε σίγουρος ότι θέλετε να διαγράψετε την /τις επιλεγμένη /ες Θ.Ενότητα /τες;';
$lang['Confirm_lock_topic'] = 'Είστε σίγουρος ότι θέλετε να κλειδώσετε την /τις επιλεγμένη /ες Θ.Ενότητα /τες;';
$lang['Confirm_unlock_topic'] = 'Είστε σίγουρος ότι θέλετε να ξεκλειδώσετε την /τις επιλεγμένη /ες Θ.Ενότητα /τες;';
$lang['Confirm_move_topic'] = 'Είστε σίγουρος ότι θέλετε να μετακινήσετε την /τις επιλεγμένη /ες Θ.Ενότητα /τες;';

$lang['Move_to_forum'] = 'Μετακίνηση στην Δ.Συζήτηση';
$lang['Leave_shadow_topic'] = 'Να μείνει ένα είδωλο της Θ.Ενότητας στην παλαιά Δ.Συζήτηση.';

$lang['Split_Topic'] = 'Πίνακας Ελέγχου διάσπασης Θ.Ενότητας';
$lang['Split_Topic_explain'] = 'Κάνοντας χρήση του παρακάτω πίνακα ελέγχου μπορείτε να διαχωρίσετε την Θ.Ενότητα σε δύο, επιλέγοντας ξεχωριστά τις δημοσιεύσεις ή επιλέγοντας μια δημοσίευση ως ορόσημο';
$lang['Split_title'] = 'Τίτλος νέας Θ.Ενότητας';
$lang['Split_forum'] = 'Δ.Συζήτηση για την νέα Θ.Ενότητα';
$lang['Split_posts'] = 'Διάσπαση των επιλεγμένων δημοσιεύσεων';
$lang['Split_after'] = 'Διάσπαση, από την δημοσίευση';
$lang['Topic_split'] = 'Η επιλεγμένη Θ.Ενότητα  διαχωρίστηκε με επιτυχία';

$lang['Too_many_error'] = 'Επιλέξατε πάρα πολλές δημοσιεύσεις. Μπορείτε να επιλέξετε μόνο μία ως ορόσημο για τον διαχωρισμό!';

$lang['None_selected'] = 'Δεν έχετε επιλέξει καμία Θ.Ενότητα για να ολοκληρωθεί η εργασία. Παρακαλώ επιστρέψτε και επιλέξτε τουλάχιστον μία.';
$lang['New_forum'] = 'Νέα Δ.Συζήτηση';

$lang['This_posts_IP'] = 'IP για αυτή την δημοσίευση';
$lang['Other_IP_this_user'] = 'Άλες IP από τις οποίες δημοσίευσε αυτός ο χρήστης';
$lang['Users_this_IP'] = 'Χρήστες που δημοσιεύουν από αυτή την IP';
$lang['IP_info'] = 'Πληροφορίες IP';
$lang['Lookup_IP'] = 'Look up IP';


//
// Timezones ... for display on each page
//
$lang['All_times'] = 'Όλες οι Ώρες είναι %s'; // eg. All times are GMT - 12 Hours (times from next block)

$lang['-12'] = 'GMT - 12 Ώρες';
$lang['-11'] = 'GMT - 11 Ώρες';
$lang['-10'] = 'GMT - 10 Ώρες';
$lang['-9'] = 'GMT - 9 Ώρες';
$lang['-8'] = 'GMT - 8 Ώρες';
$lang['-7'] = 'GMT - 7 Ώρες';
$lang['-6'] = 'GMT - 6 Ώρες';
$lang['-5'] = 'GMT - 5 Ώρες';
$lang['-4'] = 'GMT - 4 Ώρες';
$lang['-3.5'] = 'GMT - 3.5 Hours';
$lang['-3'] = 'GMT - 3 Ώρες';
$lang['-2'] = 'GMT - 2 Ώρες';
$lang['-1'] = 'GMT - 1 Ώρες';
$lang['0'] = 'GMT';
$lang['1'] = 'GMT + 1 Ώρες';
$lang['2'] = 'GMT + 2 Ώρες';
$lang['3'] = 'GMT + 3 Ώρες';
$lang['3.5'] = 'GMT + 3.5 Hours';
$lang['4'] = 'GMT + 4 Ώρες';
$lang['4.5'] = 'GMT + 4.5 Hours';
$lang['5'] = 'GMT + 5 Ώρες';
$lang['5.5'] = 'GMT + 5.5 Hours';
$lang['6'] = 'GMT + 6 Ώρες';
$lang['6.5'] = 'GMT + 6.5 Hours';
$lang['7'] = 'GMT + 7 Ώρες';
$lang['8'] = 'GMT + 8 Ώρες';
$lang['9'] = 'GMT + 9 Ώρες';
$lang['9.5'] = 'GMT + 9.5 Hours';
$lang['10'] = 'GMT + 10 Ώρες';
$lang['11'] = 'GMT + 11 Ώρες';
$lang['12'] = 'GMT + 12 Ώρες';

// These are displayed in the timezone select box
$lang['tz']['-12'] = 'GMT - 12 Ώρες';
$lang['tz']['-11'] = 'GMT - 11 Ώρες';
$lang['tz']['-10'] = 'GMT - 10 Ώρες';
$lang['tz']['-9'] = 'GMT - 9 Ώρες';
$lang['tz']['-8'] = 'GMT - 8 Ώρες';
$lang['tz']['-7'] = 'GMT - 7 Ώρες';
$lang['tz']['-6'] = 'GMT - 6 Ώρες';
$lang['tz']['-5'] = 'GMT - 5 Ώρες';
$lang['tz']['-4'] = 'GMT - 4 Ώρες';
$lang['tz']['-3.5'] = 'GMT - 3.5 Ώρες';
$lang['tz']['-3'] = 'GMT - 3 Ώρες';
$lang['tz']['-2'] = 'GMT - 2 Ώρες';
$lang['tz']['-1'] = 'GMT - 1 Ώρες';
$lang['tz']['0'] = 'GMT';
$lang['tz']['1'] = 'GMT + 1 Ώρες';
$lang['tz']['2'] = 'GMT + 2 Ώρες';
$lang['tz']['3'] = 'GMT + 3 Ώρες';
$lang['tz']['3.5'] = 'GMT + 3.5 Ώρες';
$lang['tz']['4'] = 'GMT + 4 Ώρες';
$lang['tz']['4.5'] = 'GMT + 4.5 Ώρες';
$lang['tz']['5'] = 'GMT + 5 Ώρες';
$lang['tz']['5.5'] = 'GMT + 5.5 Ώρες';
$lang['tz']['6'] = 'GMT + 6 Ώρες';
$lang['tz']['6.5'] = 'GMT + 6.5 Ώρες';
$lang['tz']['7'] = 'GMT + 7 Ώρες';
$lang['tz']['8'] = 'GMT + 8 Ώρες';
$lang['tz']['9'] = 'GMT + 9 Ώρες';
$lang['tz']['9.5'] = 'GMT + 9.5 Ώρες';
$lang['tz']['10'] = 'GMT + 10 Ώρες';
$lang['tz']['11'] = 'GMT + 11 Ώρες';
$lang['tz']['12'] = 'GMT + 12 Ώρες';
$lang['tz']['13'] = 'GMT + 13 Ώρες';

$lang['datetime']['Sunday'] = 'Κυριακή';
$lang['datetime']['Monday'] = 'Δευτέρα';
$lang['datetime']['Tuesday'] = 'Τρίτη';
$lang['datetime']['Wednesday'] = 'Τετάρτη';
$lang['datetime']['Thursday'] = 'Πέμπτη';
$lang['datetime']['Friday'] = 'Παρασκευή';
$lang['datetime']['Saturday'] = 'Σάββατο';
$lang['datetime']['Sun'] = 'Κυρ';
$lang['datetime']['Mon'] = 'Δευ';
$lang['datetime']['Tue'] = 'Τρι';
$lang['datetime']['Wed'] = 'Τετ';
$lang['datetime']['Thu'] = 'Πεμ';
$lang['datetime']['Fri'] = 'Παρ';
$lang['datetime']['Sat'] = 'Σαβ';
$lang['datetime']['January'] = 'Ιανουάριος';
$lang['datetime']['February'] = 'Φεβρουάριος';
$lang['datetime']['March'] = 'Μάρτιος';
$lang['datetime']['April'] = 'Απρίλιος';
$lang['datetime']['May'] = 'Μάϊ';
$lang['datetime']['June'] = 'Ιούνιος';
$lang['datetime']['July'] = 'Ιούλιος';
$lang['datetime']['August'] = 'Αύγουστος';
$lang['datetime']['September'] = 'Σεπτέμβριος';
$lang['datetime']['October'] = 'Οκτώβριος';
$lang['datetime']['November'] = 'Νοέμβριος';
$lang['datetime']['December'] = 'Δεκέμβριος';
$lang['datetime']['Jan'] = 'Ιαν';
$lang['datetime']['Feb'] = 'Φεβ';
$lang['datetime']['Mar'] = 'Μάρ';
$lang['datetime']['Apr'] = 'Απρ';
$lang['datetime']['May'] = 'Μάϊ';
$lang['datetime']['Jun'] = 'Ιούν';
$lang['datetime']['Jul'] = 'Ιούλ';
$lang['datetime']['Aug'] = 'Αύγ';
$lang['datetime']['Sep'] = 'Σεπ';
$lang['datetime']['Oct'] = 'Οκτ';
$lang['datetime']['Nov'] = 'Νοέ';
$lang['datetime']['Dec'] = 'Δεκ';

//
// Errors (not related to a
// specific failure on a page)
//
$lang['Information'] = 'Πληροφορίες';
$lang['Critical_Information'] = 'Κρίσιμες πληροφορίες';

$lang['General_Error'] = 'Γενικό Λάθος';
$lang['Critical_Error'] = 'Κρίσιμο Λάθος';
$lang['An_error_occured'] = 'Παρουσιάσθηκε Λάθος';
$lang['A_critical_error'] = 'Παρουσιάσθηκε Κρίσιμο Λάθος';

//
// That's all, Folks!
// -------------------------------------------------

?>