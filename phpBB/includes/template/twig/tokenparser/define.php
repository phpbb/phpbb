<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group, sections (c) 2009 Fabien Potencier
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_template_twig_tokenparser_define extends Twig_TokenParser_Set
{
    public function decideBlockEnd(Twig_Token $token)
    {
        return $token->test('ENDDEFINE');
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'DEFINE';
    }
}
