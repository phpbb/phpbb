<?php
/***************************************************************************
 *                         lang_bbcode.php [spanish]
 *                            -------------------
 *   begin                : Wednesday Oct 3, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   
 *   traducción a español : Daniel González Cuellar (webmaster@ba-k.com)
 *   			    Mariano Martene (correo@webfactory.com)
 *                          Patricio Marín (pmarin@hotmail.com)
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
  
$faq[] = array("--","Introducción");
$faq[] = array("¿Qué es el código BBCode?", "BBCode es una implementación especial del HTML, la forma en la que BBCode se usa es determinada por el administrador, es muy similar al HTML, las etiquetas van entre corchetes [ y ]");

$faq[] = array("--","Formateo de texto");
$faq[] = array("¿Cómo crear texto en negritas, cursiva o subrayado?", "BBCode incluye etiquetas para esto: [b][/b] para negritas, [u][/u] para subrayar y [i][/i] para cursivas, estas se pueden combinar entre si, es genial! :)");
$faq[] = array("¿Cómo cambiar el color o tamaño de texto?", "Para cambiar el color: [color=][/color], puede escribir el nombre del color en inglés o el código hexadecimal perteneciente a él, ej. #FFFFFF, #000000.  para crear rojo [color=red]Hola![/color]. Cambiar el tamaño es similar: [size=][/size], utilizando números del 1 al 29 (muy grande!)");
$faq[] = array("¿Puedo combinar las etiquetas de formato?", "Si :)");

$faq[] = array("--","Haciendo citas de texto o código");
$faq[] = array("Citar texto en las respuestas", "Hay dos formas de hacerlo: con una referencia o sin ella, para hacerlo con referencia utiliza la opcion CITAR del foro al dar una respuesta, el mensaje a citar es anexado al suyo automáticamente como: [quote=\"\"][/quote] El otro método (sin referencia) es poner una etiqueta parecida, pero agregando el autor del texto citado, es decir: [quote=\"Anita\"]</b>Lo qe diga Anita debe ir aquí, recuerde incluir \"\" alrededor del nombre a citar, si no quiere incluir el nombre, solo encierre el texto entre las etiquetas [quote][/quote]");
$faq[] = array("Escribiendo código o texto de otro tamaño", "Al escribir código será puesto en una fuente tipo Typewriter, como Courier, solo encierre el texto entre las etiquetas [code][/code] de esta forma: [code]echo \"Esto se supone es código\";[/code].");

$faq[] = array("--","Creando Listas");
$faq[] = array("Creando una lista desordenada", "BBCode soporta dos tipos de listas, desordenadas y ordenadas, es exactamente como en HTML, solo que con las siguientes etiquetas: Para una desordenada [list][/list], definiendo cada parte de la lista con [*]. Por ejemplo, para enlistar sus animales favoritos use [list][*]Vaca[*]Cuyo[*]Conejo[/list], esto generará algo como esto:<ul><li>Vaca</li><li>Cuyo</li><li>conejo</li></ul>");
$faq[] = array("Creando una lista ordenada", "El segundo tipo de lista es la ordenada, para crearla use [list=1][/list] para crear una lista con numeración o [list=a][/list] para una con orden alfabético, cada parte de la lista se especifica tambien con [*] Por ejemploe: [list=1][*]Vaca[*]Cuyo[*]Conejo[/list] generará algo como: <ol><li>Vaca</li><li>Cuyo</li><li>conejo</li></ol>");

$faq[] = array("--", "Creando Enlaces");
$faq[] = array("Creando un enlace a otro sitio", "phpBB BBCode soporta varias formas de hacer un enlace, la primera es con [url=][/url], por ejemplo, para hacer un enlace a phpBB.com puede usar:[url=http://www.phpbb.com/]Visite phpBB![/url], los enlaces se abrirán en una nueva ventana nueva, otra forma es [url]http://www.phpbb.com/[/url]. Este foro tiene tambien ENLACES MÁGICOS, por ejemplo, si teclea www.phpbb.com en su mensaje aparecerá automaticamente como enlace. Para hacer un enlace a un correo electrónico deberá poner: [email]alguien@sudireccion.com[/email] o simplemente teclear la direccion y se convertirá en un enlace. Puede combinarlo con la etiqueta [img][/img] para que el enlace sea una imagen, así: [url=http://www.phpbb.com/][img]http://www.phpbb.com/images/phplogo.gif[/url][/img].");

$faq[] = array("--", "Publicando imágenes en los mensajes");
$faq[] = array("Agregando una imagen al mensaje", "Para poner una imagen simplemente escriba [img]URL[/img] donde URL es la dirección en donde está su imagen, por ejemplo [img]http://www.phpbb.com/images/phplogo.gif[/img], tambien puede generar enlaces de la siguiente forma: [url=][/url] así [url=http://www.phpbb.com/][img]http://www.phpbb.com/images/phplogo.gif[/img][/url]");

$faq[] = array("--", "Otros");
$faq[] = array("¿Puedo agregar mis propias etiquetas?", "No, no en esta version de phpBB (2), seguramente será en versiones posteriores a esta");

//
// Todo por hoy :)
//

?>