<?php
/***************************************************************************
 *                         lang_bbcode.php [Galician]
 *                            -------------------
 *   begin                : Wednesday Oct 3, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: lang_bbcode.php,v 0.9 2002/03/05 01:53:26 Pato[100%Q]
 *
 ***************************************************************************/

  /****************************************************************************
 * Translation by:
 * Sergio Ares Chao :: sergio@ciberagendas.com
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
// Para engadir unha entrada de BBcode simplemente engada unha liña a este arquivo es este formato:
// $faq[] = array("pregunta", "resposta");
// Se queres separar unha sección, pon $faq[] = array("--","Encabezado del bloque opcional");
// Los enlaces se crearan automaticamente
//
// NON ESQUEZA o ; o fin da liña.
// NON POÑA COMIÑAS DOBRES (") no BBCode que introduza, se e imprescindible utilice barras invertidas e dicir:  \"lo que sea\";
//
// Os items da guia BBCode apareceran na paxina da guia BBCode na mesma orde en que se listan neste arquivo
//
// Se soamente esta a traducir este arquivo, por favor non alteres o HTML a menos que sexa absolutemente innecesario
//
// Tampouco traduzca as cores referidas a BBCode en ningunha seccion, se o fai
// os usuarios que naveguen co seu idioma poderian confundirse ó ver que o BBCode non funciona :D Si pode cambiar as
// referencias 'en liña" no texto.
//
  
$faq[] = array("--","Introducción");
$faq[] = array("¿Que é o código BBCode?", "BBCode é unha implementación especial do HTML, a forma na que BBCode se usa é determinada polo administrador, é moi similar ó HTML, as etiquetas van entre corchetes [ e ]");

$faq[] = array("--","Formateo de texto");
$faq[] = array("¿Como crear texto en negriñas, cursiva o subliñado?", "BBCode inclue etiquetas para isto: [b][/b] para negriñas, [u][/u] para subliñar y [i][/i] para cursivas, estas pódense combinar entre si, é xenial! :)");
$faq[] = array("¿Como cambia-la cor ou tamaño de texto?", "Para cambia-la cor: [color=][/color], pode escribi-lo nome da cor en inglés ou o código hexadecimal pertencente a el, ej. #FFFFFF, #000000.  para crear vermello [color=red]Hola![/color]. Cambia-lo tamaño é similar: [size=][/size], utilizando números d0 1 ó29 (moi grande!)");
$faq[] = array("¿Podo combinar as etiquetas de formato?", "Si :)");

$faq[] = array("--","Citar de texto ou código");
$faq[] = array("Citar texto nas respostas", "Hai dúas formas de facelo: cunha referencia ou sen ela, para facelo con referencia utiliza a opción CITAR do foro ó dar unha resposta, a mensaxe a citar é anexado á súa automáticamente como: [quote=\"\"][/quote] O outro método (sen referencia) é poñer unha etiqueta parecida, pero agregando o autor do texto citado, é dicir: [quote=\"Anita\"]</b>O que diga Anita debe ir aquí, lembre incluir \"\" arredor do nome a citar, se non quere inclui-lo nome, só peche o texto entre as etiquetas [quote][/quote]");
$faq[] = array("Escribindo código ou texto de outro tamaño", "Ó escribir código será posto nunha fonte tipo Typewriter, como Courier, só peche ó texto entre as etiquetas [code][/code] desta forma: [code]echo \"Esto suponse é código\";[/code].");

$faq[] = array("--","Creando Listas");
$faq[] = array("Creando unha lista desordenada", "BBCode soporta dous tipos de listas, desordenadas e ordenadas, é exactamente coma en HTML, só que coas seguientes etiquetas: Para unha desordenada [list][/list], definiendo cada parte da lista con [*]. Por exemplo, para enlistar os seus animales favoritos use [list][*]Vaca[*]Can[*]Coello[/list], esto xerará algo como isto:<ul><li>Vaca</li><li>Can</li><li>coello</li></ul>");
$faq[] = array("Creando unha lista ordenada", "O segundo tipo de lista é a ordenada, para creala use [list=1][/list] para crear una lista con numeración ou [list=a][/list] para unha con orden alfabética, cada parte da lista especiícase tamén con [*] Por exemplo: [list=1][*]Vaca[*]Can[*]Coello[/list] xerará algo coma: <ol><li>Vaca</li><li>Can</li><li>coello</li></ol>");

$faq[] = array("--", "Creando Enlaces");
$faq[] = array("Creando un enlace a outro sitio", "phpBB BBCode soporta varias formas de facer un enlace, a primeira é con [url=][/url], por exemplo, para facer un enlace a phpBB.com pode usar:[url=http://www.phpbb.com/]Visite phpBB![/url], os enlaces abriranse nunha nova fiestra, outra forma é [url]http://www.phpbb.com/[/url]. Este foro ten tamén ENLACES MÁXICOS, por exemplo, se teclea www.phpbb.com na súa mensaxe aparecerá automaticamente como enlace. Para facer un enlace a un correo electrónico deberá poñer: [email]alguen@seuenderezo.com[/email] ou simplemente teclea-lo enderezo e convertirase nun enlace. Pode combina-lo coa etiqueta [img][/img] para que o enlace sexa unha imaxe, así: [url=http://www.phpbb.com/][img]http://www.phpbb.com/images/phplogo.gif[/url][/img].");

$faq[] = array("--", "Publicando imaxes nas mensaxes");
$faq[] = array("Engadindo unha imaxe á mensaxe", "Para poñer unha imaxe simplemente escriba [img]URL[/img] donde URL é o enderezo donde está a súa imaxe, por exemplo [img]http://www.phpbb.com/images/phplogo.gif[/img], tamén pode xerar enlaces do seguinte xeito: [url=][/url] así [url=http://www.phpbb.com/][img]http://www.phpbb.com/images/phplogo.gif[/img][/url]");

$faq[] = array("--", "Outros");
$faq[] = array("¿Podo engadi-las miñas propias etiquetas?", "Non, non nesta version de phpBB (2), seguramente será posible en versiones posteriores a esta");

//
// Outro arquivo fora :)
//

?>