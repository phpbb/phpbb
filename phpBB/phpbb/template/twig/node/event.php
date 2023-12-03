<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\template\twig\node;

class event extends \Twig\Node\Node
{
	/**
	* The subdirectory in which all template listener files must be placed
	* @var string
	*/
	protected $listener_directory = 'event/';

	/** @var \phpbb\template\twig\environment */
	protected $environment;

	/** @var array */
	protected $template_event_priority_array;

	public function __construct(\Twig\Node\Expression\AbstractExpression $expr, \phpbb\template\twig\environment $environment, $lineno, $tag = null, $template_event_priority_array = [])
	{
		$this->environment = $environment;
		$this->template_event_priority_array = $template_event_priority_array;

		parent::__construct(array('expr' => $expr), array(), $lineno, $tag);
	}

	/**
	* Compiles the node to PHP.
	*
	* @param \Twig\Compiler A Twig\Compiler instance
	*/
	public function compile(\Twig\Compiler $compiler)
	{
		$compiler->addDebugInfo($this);

		$location = $this->listener_directory . $this->getNode('expr')->getAttribute('name');

		$template_events = [];

		// Group and sort extension template events in according to their priority (0 by default if not set)
		foreach ($this->environment->get_phpbb_extensions() as $ext_namespace => $ext_path)
		{
			$ext_namespace = str_replace('/', '_', $ext_namespace);
			if ($this->environment->isDebug() || $this->environment->getLoader()->exists('@' . $ext_namespace . '/' . $location . '.html'))
			{
				$priority_key = $this->template_event_priority_array[$ext_namespace][$location] ?? 0;
				$template_events[$priority_key][] = $ext_namespace;
			}
		}
		krsort($template_events);

		foreach ($template_events as $events)
		{
			foreach ($events as $ext_namespace)
			{
				if ($this->environment->isDebug())
				{
					// If debug mode is enabled, lets check for new/removed EVENT
					//  templates on page load rather than at compile. This is
					//  slower, but makes developing extensions easier (no need to
					//  purge the cache when a new event template file is added)
					$compiler
						->write("if (\$this->env->getLoader()->exists('@{$ext_namespace}/{$location}.html')) {\n")
						->indent();
				}

				$compiler
					->write("\$previous_look_up_order = \$this->env->getNamespaceLookUpOrder();\n")

					// We set the namespace lookup order to be this extension first, then the main path
					->write("\$this->env->setNamespaceLookUpOrder(array('{$ext_namespace}', '__main__'));\n")
					->write("\$this->env->loadTemplate(\$this->env->getTemplateClass('@{$ext_namespace}/{$location}.html'), '@{$ext_namespace}/{$location}.html')->display(\$context);\n")
					->write("\$this->env->setNamespaceLookUpOrder(\$previous_look_up_order);\n");

				if ($this->environment->isDebug())
				{
					$compiler
						->outdent()
						->write("}\n\n");
				}
			}
		}
	}
}
