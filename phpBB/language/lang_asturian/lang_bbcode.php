<?php
/***************************************************************************
 *                         lang_bbcode.php [asturian]
 *                            -------------------
 *   begin                : Wednesday Oct 3, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   
 *   tradución al asturianu : Mikel González (mikelglez@iespana.es)
 *
 *   $Id: lang_bbcode.php,v 0.9 2002/03/05 01:53:26 Pato[100%Q]
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
  
$faq[] = array("--","Introdución");
$faq[] = array("¿Qué ye el códigu BBCode?", "BBCode ye una implementación especial del HTML, la forma ena que BBCode usase ye determiná po'l alministrador, ye mui similar al HTML, les etiquetes van entre corchetes [ y ]");

$faq[] = array("--","Formateu de textu");
$faq[] = array("¿Cómu crear textu en negrites, cursiva o subrayao?", "BBCode incluye etiquetes pa estu: [b][/b] pa negrites, [u][/u] pa subrayar y [i][/i] pa cursives, estes puense combinar entre si, ye xenial! :)");
$faq[] = array("¿Cómu camudar el color o tamañu de textu?", "Pa camudar el color: [color=][/color], pue escribir el nombe del color n'inglés o'l códigu hexadecimal , ex. #FFFFFF, #000000.  pa crear roxu [color=red]Hola![/color]. Camudar el tamañu ye similar: [size=][/size], utilizando númberos del 1 al 29 (mui grande!)");
$faq[] = array("¿Pueo combinar les etiquetes de formatu?", "Si :)");

$faq[] = array("--","Faciendo cites de textu o código");
$faq[] = array("Citar textu en les rempuestes", "Hay dos formes de facelo: con una referencia o ensin ella, pa facerlo con referencia utiliza la opcion CITAR del foru al dar una rempuesta, el mensaxe a citar ye xuntao al suyo automáticamente como: [quote=\"\"][/quote] L'otru método (ensin referencia) ye poner una etiqueta parecia, pero añadiendo l'autor del textu citau: [quote=\"Pachín\"]</b>Lo qe diga Pachín va equí, recuerde incluir \"\" alredeor del nome a citar, si nun quier incluir el nome, encierre el textu entre les etiquetes [quote][/quote]");
$faq[] = array("Escribiendo códigu o textu d'otru tamañu", "Al escribir código será puestu n'una fuente tipu Typewriter, comu Courier, encierre namás el textu entre les etiquetes [code][/code] d'esta mena: [code]echo \"Esto suponese que ye códigu\";[/code].");

$faq[] = array("--","Creando Llistes");
$faq[] = array("Creando una llista desordenada", "BBCode soporta dos tipos de llistes, desordenaes y ordenaes, ye exactamente comu en HTML, pero coles siguientes etiquetes: Pa una desordenada [list][/list], definiendo cada parte de la llista con [*]. Por exemplu, pa llistar sus animales favoritos use [list][*]Vaca[*]Perru[*]Agüarón[/list], esto xenerará algo asi:<ul><li>Vaca</li><li>Perru</li><li>Agüarón</li></ul>");
$faq[] = array("Creando una llista ordenada", "El segundu tipu de llista ye la ordenada, pa creala use [list=1][/list] pa crear una llista con numeración o [list=a][/list] pa una con orden alfabético, cada parte de la llista especificase tamién con [*] Por exemplu: [list=1][*]Vaca[*]Perru[*]Agüarón[/list] xenerará algo asi: <ol><li>Vaca</li><li>Perru</li><li>Agüarón</li></ol>");

$faq[] = array("--", "Creando Enllaces");
$faq[] = array("Creando un enllace a otru sitiu", "phpBB BBCode soporta varies formas de facer un enllace, la primera ye con [url=][/url], por exemplu, pa facer un enllace a phpBB.com pue usar:[url=http://www.phpbb.com/]Visite phpBB![/url], los enllaces abriránse n'una ventana nueva, otra mena ye [url]http://www.phpbb.com/[/url]. Esti foru tien tamién ENLLACES MÁXICOS, por exemplu, si teclea www.phpbb.com en su mensaxe aparecerá automaticamente como enllace. Pa facer un enlace a un correu lletrónicu deberá poner: [email]alguien@dominiu.com[/email] o simplemente teclear la direción y convertiráse en un enllace. Pue combinalo cola etiqueta [img][/img] pa que'l enllace seya una imaxen, así: [url=http://www.phpbb.com/][img]http://www.phpbb.com/images/phplogo.gif[/url][/img].");

$faq[] = array("--", "Publicando imáxenes en los mensaxes");
$faq[] = array("Agregando una imaxen al mensaxe", "Pa poner una imaxen namás tien que plumiar [img]URL[/img] URL ye la direción au ta la su imaxen, por exemplu [img]http://www.phpbb.com/images/phplogo.gif[/img], tamén pue xenerar enllaces de la siguiente mena: [url=][/url] así [url=http://www.phpbb.com/][img]http://www.phpbb.com/images/phplogo.gif[/img][/url]");

$faq[] = array("--", "Otros");
$faq[] = array("¿Pueo agregar mis propies etiquetes?", "No, nun puede n'esta version de phpBB (2), seguramente podrá facelo en versiones posteriores a esta");

//
// 
//

?>