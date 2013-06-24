<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_template_twig_node_event extends Twig_Node
{
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

        $compiler->indent();

    	$location = $this->getNode('expr')->getAttribute('name');

    	foreach ($this->environment->get_phpbb_extensions() as $ext_namespace => $ext_path)
    	{
    		$ext_namespace = str_replace('/', '_', $ext_namespace);

    		if ($this->environment->getLoader()->exists('@' . $ext_namespace . '/' . $location . '.html'))
    		{
    			$compiler->write("\$this->env->loadTemplate('@" . $ext_namespace . "/" . $location . ".html')->display(\$context);\n");
			}
		}
    }
}
