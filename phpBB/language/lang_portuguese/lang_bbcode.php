<?php
/***************************************************************************
 *                        lang_bbcode.php [portuguese]
 *                            -------------------
 *   begin                : Wednesday Oct 3, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
 *
 *
 ***************************************************************************/

 /****************************************************************************
 * Translation by:
 * LuizCB (Pincel) LuizCB@pincel.net || http://pincel.net
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
  
$faq[] = array("--","Introduction");
$faq[] = array("O que é BBCode?", "BBCode é uma implementação especial de HTML. A possibilidade em poder usar BBCode nas suas mensagens é determinada pelo Administrador dos fóruns. Em adição, você pode poderá desligar os BBCode em cada mensagem via 'Desactivar BBCode nesta mensagem' abixo do corpo principal de cada mensagem. BBCode por si mesmo é similar em estilo a HTML, as etiquetas são englobadas por parêntesis rectos [ and ] em vez de &lt; e &gt; TML, as etiquetas sao introduzidas entre perêntesis rectos [ and ] em vez de &lt; e &gt; e proporciona um maior controle do que e como algo é apresentado. Dependendo do modelo de phpBB em uso verá que adicionar BBCode ás suas mensagens é tornado mais fácil através de um painel acima do corpo principal de mensagem onde pode carregar nos vários itens consoante o código que pretenda aplicar. Apesar disso você talvez irá encarar este guia como útil.");

$faq[] = array("--","Formatos de Texto");
$faq[] = array("Como criar texto sobrecarregado, itálico e sublinhado", "O BBCode inclúi etiquetas que lhe permitem mudar rapidamente o estilo básico do seu texto. Isto é possível das seguintes formas: <ul><li>Para tornar uma parte de texto sobrecarregada inluí[la entre <b>[b][/b]</b>, ou seja, <br /><br /><b>[b]</b>Olá<b>[/b]</b><br /><br />passará a ser <b>Olá</b></li><li>Para sublinhar use <b>[u][/u]</b>, por exemplo:<br /><br /><b>[u]</b>Bom Dia<b>[/u]</b><br /><br />passa a ser <u>Bom Dia</u></li><li>Para tornar texo itálico use <b>[i][/i]</b>, ou seja,<br /><br />Isto é <b>[i]</b>Óptimo!<b>[/i]</b><br /><br />resultará nisto <i>Óptimo!</i></li></ul>");
$faq[] = array("Como mudar a cor ou o tamanho do texto", "Para alterar a cor ou o tamanho do texto devem ser usadas as seguintes etiquetas. Ter em mente que a forma como aparecerá no monitor de cada visitante está dependente do seu 'browser' ou sistema: <ul><li>A mudança da cor do texto é atingida englobando-o em <b>[color=][/color]</b>. Tanto pode especificar o nome de uma cor conhecida (terá que ser em ingês) (por exempllo, red, blue, yellow, etc.) ou na sua forma hexadecimal, ou seja, #FFFFFF, #000000. Por exemplo, para criar texto em vermelho poderá usar:<br /><br /><b>[color=red]</b>Olá!<b>[/color]</b><br /><br />ou<br /><br /><b>[color=#FF0000]</b>Olá!<b>[/color]</b><br /><br />ambos aparecerão como <span style=\"color:red\">Olá!</span></li><li>A mudança do tamanho do texto pode ser feita de uma forma similar, usando <b>[size=][/size]</b>. Esta etiqueta está dependente do Modelo de phpBB que você se encontra a usar mas o formato recomendado é um valor numérico representando o tamanho de texto em pixels, começando em 1 (tão pequeno que práticamente não se vê) ate 29 (muito grande). Por exemplo:<br /><br /><b>[size=9]</b>PEQUENO<b>[/size]</b><br /><br />normalmente aparecerá como <span style=\"font-size:9px\">PEQUENO</span><br /><br />enquanto que:<br /><br /><b>[size=24]</b>ENORME!<b>[/size]</b><br /><br />será <span style=\"font-size:24px\">ENORME!</span></li></ul>");
$faq[] = array("Posso combinar etiquetas de formato?", "Sim, claro que pode. Por exemplo, para cativar a atenção de alguém poderá escrever:<br /><br /><b>[size=18][color=red][b]</b>OLHE PARA MIM!<b>[/b][/color][/size]</b><br /><br />isto surgirá como <span style=\"color:red;font-size:18px\"><b>OLHE PARA MIM!</b></span><br /><br />No entanto, não recomendamos o uso de muito texto como acabamos de descrever! Lembre-se que depende de si, o autor da mensagem, de assegurar que os códigos são colocados correctamente. Por exemplo, isto está incorrecto:<br /><br /><b>[b][u]</b>Isto é errado<b>[/b][/u]</b>");

$faq[] = array("--","Citar e produzir texto de largura fixa");
$faq[] = array("Citar texto em respostas", "Há duas formas de reproduzir texto previamente feito (normalmente uma réplica de uma mensagem anterior), com ou sem referência.<ul><li>Quando você utiliza a função Citar para responder a uma mensagem no fórum note que o texto dessa mensagem é adicionado á sua janela de mensagem englobado num bloco <b>[quote=\"\"][/quote]</b>. Este método permite-lhe citar com uma referência a uma pessoa ou o que quer que seja você decida colocar! Por exemplo, para citar uma peça de texto que Mr. Blobby escreveu, você escreverá:<br /><br /><b>[quote=\"Mr. Blobby\"]</b>O texto que Mr. Blobby escreveu irá aqui<b>[/quote]</b><br /><br />O resultado adicionará automaticamente, Mr. Blobby wrote: antes do texto actual. De lembrar que você <b>deve</b> incluir os parêntesis \"\" á volta do nome que está a citar, não sendo opcional.</li><li>O segundo método permite-lhe citar algo ás cegas. Para utilizar isto englobe o texto em etiquetas <b>[quote][/quote]</b>. Quando verificar a mensagem simplesmente mostrará, Citação: antes do texto.</li></ul>");
$faq[] = array("Produzir código ou texto com uma largura fixa", "Se pretende reproduzir uma porção de código ou ude facto algo que requeira uma largura fixa, ou seja, typo de fonte Courier, englobe o texto em etiquetas <b>[code][/code]</b>, ou seja<br /><br /><b>[code]</b>echo \"Isto é algum código\";<b>[/code]</b><br /><br />O formato usado entre as etiquetas <b>[code][/code]</b> é preservado quando posteriormente o verificar.");

$faq[] = array("--","Gerando listas");
$faq[] = array("Criando uma lista sem ordem", "O BBCode aceita dois tipos de listas, sem ordem e ordenada. Elas são basicamente o mesmo que em HTML. Uma lista sem ordem produz cada item na sua lista de uma forma sequencial, uma a seguir á outra,  precedendo cada uma com um caracter constante. Para a criar escrever <b>[list][/list]</b> e definir cada item entre essas etiquetas usando <b>[*]</b>. Por exemplo, para listar as suas cores favoritaspoderá escrever:<br /><br /><b>[list]</b><br /><b>[*]</b>Vermelho<br /><b>[*]</b>Azul<br /><b>[*]</b>Amarelo<br /><b>[/list]</b><br /><br />Isto irá resultar em :<ul><li>Vermelho</li><li>Azul</li><li>Amarelo</li></ul>");
$faq[] = array("Criar uma lista ordenada", "O segundo tipo de listas, uma ordenada, dá-lhe controle do que aparecerá antes de cada item. Para criar uma lista ordenada você usará <b>[list=1][/list]</b> de forma a criar uma lista numérica ou, alternativamente, <b>[list=a][/list]</b> para uma lista alfabética. Como para o tipo de lista sem ordem os itens são especificados usando <b>[*]</b>. Por exemplo:<br /><br /><b>[list=1]</b><br /><b>[*]</b>Ir ás compras<br /><b>[*]</b>Comprar um computador novo<br /><b>[*]</b>Insultar o computador quando bloqueia<br /><b>[/list]</b><br /><br />produzirá o seguinte:<ol type=\"1\"><li>Ir ás compras</li><li>Comprar um computador novo</li><li>Insultar o computador quando bloqueia</li></ol>Enquanto que para uma lista alfabética você usará:<br /><br /><b>[list=a]</b><br /><b>[*]</b>A primaira resposta<br /><b>[*]</b>A segunda resposta<br /><b>[*]</b>A terceira resposta<br /><b>[/list]</b><br /><br />resultando em <ol type=\"a\"><li>A primeira resposta</li><li>A segunda resposta</li><li>A terceira resposta</li></ol>");

$faq[] = array("--", "Criar atalhos");
$faq[] = array("Criar um atalho para outra página/site", "O phpBB BBCode aceita um número variável de formas para criar URIs, Uniform Resource Indicators, melhor conhecidos como URLs.<ul><li>A primeira dessas formas usa a etiqueta <b>[url=][/url]</b>, o que quer que seja que escreva depois do sinal = fazem com que o conteúdo dessa etiqueta aja como um URL. Por exemplo, para fazer uma ligação-atalho a phpBB.com você escreverá:<br /><br /><b>[url=http://www.phpbb.com/]</b>Visite phpBB!<b>[/url]</b><br /><br />Isto irá resultar no atalho seguinte, <a href=\"http://www.phpbb.com/\" target=\"_blank\">Visite phpBB!</a> Veja que a página irá abrir numa janela nova de forma a que o utilizador possa continuar nos fóruns caso queira.</li><li>Se pretende que o URL esteja á vista simplesmente faça isto:<br /><br /><b>[url]</b>http://www.phpbb.com/<b>[/url]</b><br /><br />Isso produzirá o seguinte atalho, <a href=\"http://www.phpbb.com/\" target=\"_blank\">http://www.phpbb.com/</a></li><li>Adicionalmente o phpBB possui algo chamado <i>Atalhos Mágicos</i>, que automaticamente transforma qualquer URL escrito com um sintaxe correcto num atalho sem ser necessário especificar quaisquer etiquetas ou mesmo o prefixo http://. Por exemplo, escrevendo typing, www.phpbb.com na sua mensagem, automaticamente produzirá <a href=\"http://www.phpbb.com/\" target=\"_blank\">www.phpbb.com</a> quando verificar essa mensagem.</li><li>A mesma coisa se aplica aos endereços de email, pode especificar explicitamente o endereço, por exemplo:<br /><br /><b>[email]</b>ninguem@domain.adr<b>[/email]</b><br /><br />que resultará em <a href=\"emailto:ninguem@domain.adr\">ninguem@domain.adr</a> ou pode apenas escrever ninguem@domain.adr na sua mensagem que será automaticamente convertido quando a vir.</li></ul>Como em todas as etiquetas de BBCode pode misturar URLs com quaisquer outras etiquetas como por exemplo <b>[img][/img]</b> (ver a próxima), <b>[b][/b]</b>, etc. Em relação ao formato das etiquetas está totalmente dependente de si assegurar a ordem correcta de início e fecho, por exemplo:<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/url][/img]</b><br /><br />isto <u>não é</u> correcto, o que pode conduzir á sua mensagem ser removida, como tal tenha cuidado.");

$faq[] = array("--", "Mostrar imagens em mensagens");
$faq[] = array("Adicionar uma imagem a uma mensagem", "O phpBB BBCode incorpora uma etiqueta para incluir imagens nas suas mensagens. Duas coisas muito importantes a lembrar quandoo se usa estas etiquetas são; muitos utilizadores não gostam de ver muitas imagens em mensagens e, segundo, a imagem que você pretende mostrar deve exixtir na internet (não pode existir apenas no seu computador, por exemplo, a menos que tenha um servidor de páginas na web e seja publicamente acessível!). Não há presentemente qualquer forma de armazenar imagens localmente com o phpBB (contamos debruçar-nos nesses assuntos na próxima publicação do phpBB). Para mostrar uma imagem você terá que rodear o URL apontando para a imagem com as etiquetas <b>[img][/img]</b>. Por exemplo:<br /><br /><b>[img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img]</b><br /><br />Como deve ter notado na secção do URL acima, você pode englobar uma imagem numa etiqueta <b>[url][/url]</b> se assim o desejar, ou seja, <br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img][/url]</b><br /><br />irá produzir:<br /><br /><a href=\"http://www.phpbb.com/\" target=\"_blank\"><img src=\"http://www.phpbb.com/images/phplogo.gif\" border=\"0\" alt=\"\" /></a><br />");

$faq[] = array("--", "Outros assuntos");
$faq[] = array("Posso adicionar as minhas próprias etiquetas?", "Não, receio que não o possa fazer directamente no phpBB 2.0. Pensamos oferecer para a próxima versão etiquetas configuráveis de BBCode");

//
// This ends the BBCode guide entries
//

?>