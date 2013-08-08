<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}


class phpbb_template_twig_node_event extends Twig_Node
{
	/** 
	 * The subdirectory in which all template event files must be placed
	 */
	const TEMPLATE_EVENTS_SUBDIRECTORY = 'events/';

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

			if (defined('DEBUG'))
			{
				// If debug mode is enabled, lets check for new/removed EVENT
				//  templates on page load rather than at compile. This is
				//  slower, but makes developing extensions easier (no need to
				//  purge the cache when a new event template file is added)
		        $compiler
		            ->write("if (\$this->env->getLoader()->exists('@{$ext_namespace}/" . self::TEMPLATE_EVENTS_SUBDIRECTORY . "{$location}.html')) {\n")
		            ->indent()
		        ;
			}

			if (defined('DEBUG') || $this->environment->getLoader()->exists('@' . $ext_namespace . '/' . self::TEMPLATE_EVENTS_SUBDIRECTORY . $location . '.html'))
			{
				$compiler
					->write("\$previous_look_up_order = \$this->env->getNamespaceLookUpOrder();\n")

					// We set the namespace lookup order to be this extension first, then the main path
					->write("\$this->env->setNamespaceLookUpOrder(array('{$ext_namespace}', '__main__'));\n")
					->write("\$this->env->loadTemplate('@{$ext_namespace}/" . self::TEMPLATE_EVENTS_SUBDIRECTORY . "{$location}.html')->display(\$context);\n")
					->write("\$this->env->setNamespaceLookUpOrder(\$previous_look_up_order);\n")
				;
			}

			if (defined('DEBUG'))
			{
				$compiler
					->outdent()
		            ->write("}\n\n")
				;
			}
		}
	}
}
