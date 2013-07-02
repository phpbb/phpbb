<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_template_twig_node_includejs extends Twig_Node
{
	/** @var Twig_Environment */
	protected $environment;

    public function __construct(Twig_Node_Expression $expr, phpbb_template_twig_environment $environment, $lineno, $tag = null)
    {
    	$this->environment = $environment;

        parent::__construct(array('expr' => $expr), array(), $lineno, $tag);
    }

    /**
     * Compiles the node to PHP.
     *
     * @param Twig_Compiler A Twig_Compiler instance
     */
    public function compile(Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this);

		$config = $this->environment->get_phpbb_config();

        $compiler
			->write("\$context['definition']->append('SCRIPTS', '<script type=\"text/javascript\" src=\"' . ")
        	->subcompile($this->getNode('expr'))
        	->raw(" . '?assets_version=" . $config['assets_version'] . "\"></script>');\n")
        ;
    }
}
