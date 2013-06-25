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

    	$location = $this->getNode('expr')->getAttribute('name');

    	foreach ($this->environment->get_phpbb_extensions() as $ext_namespace => $ext_path)
    	{
    		$ext_namespace = str_replace('/', '_', $ext_namespace);

    		if ($this->environment->getLoader()->exists('@' . $ext_namespace . '/' . $location . '.html'))
    		{
    			$compiler
    				->write("\$previous_look_up_order = \$this->env->getNamespaceLookUpOrder();\n")

    				// We set the namespace lookup order to be this extension first, then the main path
    				->write("\$this->env->setNamespaceLookUpOrder(array('" . $ext_namespace . "', '__main__'));\n")
    				->write("\$this->env->loadTemplate('@" . $ext_namespace . "/" . $location . ".html')->display(\$context);\n")
    				->write("\$this->env->setNamespaceLookUpOrder(\$previous_look_up_order);\n")
    			;
			}
		}
    }
}
