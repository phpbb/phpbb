<?php
/***************************************************************************
 *                         lang_bbcode.php [Italian]
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
 
//
// Translation by teo
//

  
$faq[] = array("--","Introduzione");
$faq[] = array("Cos'è il BBCode?", "BBCode è un ampliamento/attrezzo speciale del codice HTML. L'uso del BBCode nei tuoi messaggi nel forum è determinato dall'amministratore. Inoltre puoi disabilitare il BBCode in ogni messaggio attraverso il modulo di invio. Il BBCode stesso ha uno stile simile all'HTML, i tags sono racchiusi in parentesi quadre [ e ] piuttosto che in &lt; e &gt; e offre grande controllo su cosa e come vogliamo mostrare qualcosa. La facilità di utilizzo del BBCode nei tuoi messaggi dipende dal modello che stai utilizzando. Per ogni problema puoi far riferimento a questa guida.");

$faq[] = array("--","Formattazione del Testo");
$faq[] = array("Come creare il testo in grassetto, sottolineato o corsivo", "Il BBCode include dei tag per permetterti di cambiare velocemente lo stile di base del tuo testo. Questo avviene nel seguente modo: <ul><li>Per il testo in grassetto usa <b>[b][/b]</b>, es. <br /><br /><b>[b]</b>Ciao<b>[/b]</b><br /><br >diventerà <b>Ciao</b></li><li>Per il testo sottolineato usa<b>[u][/u]</b>, es.:<br /><br /><b>[u]</b>Buon Giorno<b>[/u]</b><br /><br />diventa <u>Buon Giorno</u></li><li>Per il testo in corsivo usa<b>[i][/i]</b>, es.<br /><br >Questo è <b>[i]</b>Grande!<b>[/i]</b><br /><br />diventa Questo è <i>Grande!</i></li></ul>");
$faq[] = array("Come cambiare il colore o la dimensione del testo", "Per modificare il colore o la dimensione del testo puoi usare i seguenti tags. Ricordati che queste impostazioni dipendono dal browser e dal sistyema di chi guarda il messaggio: <ul><li>Per cambiare il colore del testo racchiudilo tra <b>[color=][/color]</b>. Puoi anche specificare un nome di colore conosciuto (es. rosso, blu, giallo, ecc.) o alternativamente il codice esadecimale, es. #FFFFFF, #000000. Per esempio, per creare un testo rosso puoi usare:<br /><br /><b>[color=red]</b>Ciao!<b>[/color]</b><br /><br />or<br /><br /><b>[color=#FF0000]</b>Ciao!<b>[/color]</b><br /><br />entrambi danno come risultato <span style=\"color:red\">Ciao!</span></li><li>Per cambiare la dimensione del testo usa <b>[size=][/size]</b>. Questo tag dipende dal modello che stai usando ma il formato raccomandato è un valore numerico che rappresenti la dimensione del testo in pixels, iniziando da 1 (così piccolo che non si riesce a vedere) fino a 29 (molto grande). Per esempio:<br /><br /><b>[size=9]</b>PICCOLO<b>[/size]</b><br /><br />generalmente è <span style=\"font-size:9px\">PICCOLO</span><br /><br />mentre:<br /><br /><b>[size=24]</b>ENORME!<b>[/size]</b><br /><br />sarà <span style=\"font-size:24px\">ENORME!</span></li></ul>");
$faq[] = array("Posso combinare i tags di formattazione?", "Sì, certo, per esempio per richiamare l'attenzione puoi scrivere:<br /><br /><b>[size=18][color=red][b]</b>GUARDAMI!<b>[/b][/color][/size]</b><br /><br />cioè <span style=\"color:red;font-size:18px\"><b>GUARDAMI!</b></span><br /><br />Ti consigliamo di non usare troppo testo come questo, comunque! Ricorda che tu, l'autore, devi assicurarti che tutti i tags siano chiusi in modo corretto. Per esempio, quello che segue non è corretto:<br /><br /><b>[b][u]</b>Questo è sbagliato<b>[/b][/u]</b>");

$faq[] = array("--","Citazioni e testo a larghezza fissa");
$faq[] = array("Citazioni di testo nelle risposte", "Ci sono due modi per fare una citazione, con un referente o senza.<ul><li>Quando utilizzi la funzione Citazione per rispondere ad un messaggio sul forum devi notare che il testo del messaggio viene incluso nel finestra del messaggio tra <b>[quote=\"\"][/quote]</b>. Questo metodo ti permette di fare una citazione riferendoti ad una persona o qualsiasi altra cosa che hai deciso di inserire! Per esempio, per citare un pezzo di testo di Mr. Blobby devi inserire:<br /><br /><b>[quote=\"Mr. Blobby\"]</b>Il testo di Mr. Blobby andrà qui<b>[/quote]</b><br /><br />Nel messaggio verrà automaticamente aggiunto, Mr. Blobby ha scritto: prima del testo citato. Ricorda che tu <b>devi</b> includere le parentesi \"\" attorno al nome che stai citando, non sono apozionali.</li><li>Il secondo metodo ti permette di citare qualcosa alla cieca. Per utilizzare questo metodo, racchiudi il testo tra i tags <b>[quote][/quote]</b>. Quando vedrai il messaggio comparirà semplicemente, Citazione: prima del testo stesso.</li></ul>");
$faq[] = array("Mostrare il codice", "Se vuoi mostrare un pezzo di codice o qualcosa che ha bisogno di una larghezza fissa, es. Courier devi racchiudere il testo tra i tags <b>[code][/code]</b>, es.<br /><br /><b>[code]</b>echo \"Questo è un codice\";<b>[/code]</b><br /><br />Tutta la formattazione utilizzata tra i tags <b>[code][/code]</b> viene mantenuta quando viene visualizzata in seguito.");

$faq[] = array("--","Generazione di liste");
$faq[] = array("Creare una lista non ordinata", "BBCode supporta due tipi di liste, ordinate e non. Sono essenzialmente la stessa cosa del loro equivalente in HTML. Una lista non ordinata mostra ogni oggetto nella tua lista in modo sequenziale, uno dopo l'altro inserendo un punto per ogni riga. Per creare una lista non ordinata usa <b>[list][/list]</b> e definisci ogni oggetto nella lista usando <b>[*]</b>. Per esempio per fare una lista dei tuoi colori preferiti puoi usare:<br /><br /><b>[list]</b><br /><b>[*]</b>Rosso<br /><b>[*]</b>Blu<br /><b>[*]</b>Giallo<br /><b>[/list]</b><br /><br />Questo mostrerà questa lista:<ul><li>Rosso</li><li>Blu</li><li>Giallo</li></ul>");
$faq[] = array("Creare una lista Ordinata", "Una lista ordinata ti permette di controllare il modo in cui ogni oggetto della lista viene mostrato. Per creare una lista ordinata usa <b>[list=1][/list]</b> per creare una lista numerata o alternativamente <b>[list=a][/list]</b> per una lista alfabetica. Come per la lista non ordinata gli oggetti vengono specificati utilizzando <b>[*]</b>. Per esempio:<br /><br /><b>[list=1]</b><br /><b>[*]</b>Vai al negozio<br /><b>[*]</b>Compra un novo computer<br /><b>[*]</b>Impreca sul computer quando si blocca<br /><b>[/list]</b><br /><br />verrà mostrato così:<ol type=\"1\"><li>Vai al negozio</li><li>Compra un nuovo computer</li><li>Impreca sul computer quando si blocca</li></ol>mentre per una lista alfabetica devi usare:<br /><br /><b>[list=a]</b><br /><b>[*]</b>La prima risposta possibile<br /><b>[*]</b>La seconda risposta possibile<br /><b>[*]</b>La terza risposta possibile<br /><b>[/list]</b><br /><br />sarà<ol type=\"a\"><li>La prima risposta possibile</li><li>La seconda risposta possibile</li><li>La terza risposta possibile</li></ol>");

$faq[] = array("--", "Creare Links");
$faq[] = array("Linkare un altro sito", "Il BBCode di phpBB supporta diversi modi per creare URI, Uniform Resource Indicators meglio conosciuti come URL.<ul><li>Il primo di questi utilizza il tag <b>[url=][/url]</b>, qualunque cosa digiti dopo il segno = genererà il contenuto del tag che si comporterà come URL. Per esempio per linkarsi a phpBB.com devi usare:<br /><br /><b>[url=http://www.phpbb.com/]</b>Visita phpBB!<b>[/url]</b><br /><br />Questo genera il seguente link, <a href=\"http://www.phpbb.com/\" target=\"_blank\">Visita phpBB!</a> Come puoi vedere il link si apre in una nuova finestra così l'utente può continuare a navigare nei forum.</li><li>Se vuoi che l'URL stesso venga mostrato come link puoi fare questo semplicemente usando:<br /><br /><b>[url]</b>http://www.phpbb.com/<b>[/url]</b><br /><br />Questo genera il seguente link, <a href=\"http://www.phpbb.com/\" target=\"_blank\">http://www.phpbb.com/</a></li><li>Inoltre phpBB dispone di una cosa chiamata <i>Magic Links</i>, questo cambierà ogni URL sintatticamente corretta in un link senza la necessità di specificare nessun tag o http://. Per esempio digitando www.phpbb.com nel tuo messaggio automaticamente verrà cambiato in <a href=\"http://www.phpbb.com/\" target=\"_blank\">www.phpbb.com</a> e verrà mostrato nel messaggio finale.</li><li>La stessa cosa accade per gli indirizzi email, puoi specificare un indirizzo esplicitamente, per esempio:<br /><br /><b>[email]</b>no.one@domain.adr<b>[/email]</b><br /><br />che mostrerà <a href=\"emailto:no.one@domain.adr\">no.one@domain.adr</a> o puoi digitare no.one@domain.adr nel tuo messaggio e verrà automaticamente convertito.</li></ul>Come per tutti i tag del BBCode puoi includere le URL in ogni altro tag come <b>[img][/img]</b> (guarda il successivo punto), <b>[b][/b]</b>, ecc. Come per i tag di formattazione dipende da te verificare che tutti i tag siano correttamente aperti e chiusi, per esempio:<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/url][/img]</b><br /><br /> <u>non</u> è corretto e potrebbe cancellare il tuo messaggio. Quindi presta attenzione. ");

$faq[] = array("--", "Mostrare immagini nei messaggi");
$faq[] = array("Aggiungere una immagine al messaggio", "Il BBCode di phpBB incorpora un tag per l'inclusione di immagini nei tuoi messaggi. Ci sono due cose importanti da ricordare nell'usare questo tag; a molti utenti non piacciono molte immagini nei messaggi e in secondo luogo l'immagine deve essere già disponibile su internet (non può esistere solo sul tuo computer per esempio, a meno che tu non abbia un webserver!). Non c'è modo di salvare le immagini localmente con phpBB (forse nella prossima release di phpBB). Per mostrare delle immagini devi inserire l'URL che rimanda all'immagine con il tag <b>[img][/img]</b>. Per esempio:<br /><br /><b>[img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img]</b><br /><br />Come notato nella sezione URL puoi inserire un'immagine nel tag <b>[url][/url]</b> se vuoi, es.<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img][/url]</b><br /><br />genera:<br /><br /><a href=\"http://www.phpbb.com/\" target=\"_blank\"><img src=\"http://www.phpbb.com/images/phplogo.gif\" border=\"0\" alt=\"\" /></a><br />");

$faq[] = array("--", "Altre Cose");
$faq[] = array("Posso aggiungere i miei tag personali?", "No, non direttamente in phpBB 2.0. Stiamo cercndo di rendere i tag del BBCode più versatili per la prossima versione");

//
// This ends the BBCode guide entries
//

?>