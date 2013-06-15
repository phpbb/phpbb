<?php

/*
 * This file is part of Twig.
 *
 * (c) 2009 Fabien Potencier
 * (c) 2009 Armin Ronacher
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Represents a for node.
 *
 * @package    twig
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class phpbb_template_twig_node_begin extends Twig_Node
{
    public function __construct($beginName, Twig_NodeInterface $body, Twig_NodeInterface $else = null, $lineno, $tag = null)
    {
        parent::__construct(array('body' => $body, 'else' => $else), array('beginName' => $beginName), $lineno, $tag);
    }

    /**
     * Compiles the node to PHP.
     *
     * @param Twig_Compiler A Twig_Compiler instance
     */
    public function compile(Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this);

		$compiler
			// name -> loop name
			// local context -> parent template variable context
			// global context -> global template variable context
			// variable chain -> full chain of variables to output template vars properly in subloops
			//		e.g. [foo][bar][foobar]
			// current chain location -> current location in subloop
			//		e.g. [foobar] of [foo][bar]
			->write("\$iterator = function (\$name, \$local_context, \$global_context, &\$variable_chain, &\$current_chain_location) {\n")
			->indent()
				//->write("var_dump(\$name, \$local_context);\n")
				// Try to find the loop in the
				// local context (child of local context passed, in case of a child loop)
				// global context (root template var)
				->write("if (isset(\$local_context[\$name])) {\n")
				->indent()
					->write("\$local_context = \$local_context[\$name];\n")
				->outdent()
				->write("}\n")
				->write("else if (isset(\$global_context[\$name])) {\n")
				->indent()
					->write("\$local_context = \$global_context[\$name];\n")
				->outdent()
				->write("} else { return; }\n")

				->write("if (!is_array(\$local_context) || empty(\$local_context)) { return; }\n")

				->write("foreach (\$local_context as \$for_context) {\n")
				->indent()
					// Some hackish stuff for Twig to properly subcompile
					->write("\$current_chain_location[\$name] = \$for_context;\n")
					->write("\$context = array_merge(\$global_context, \$variable_chain);\n")

					// Children
					->subcompile($this->getNode('body'))
				->outdent()
				->write("}\n")
			->outdent()
			->write("};\n")
			->write("if (isset(\$global_context)) {\n")
			->indent()
				// We are already inside an anonymous function
				->write("\$iterator('" . $this->getAttribute('beginName') . "', \$for_context, \$global_context, \$variable_chain, \$current_chain_location[\$name]);\n")
			->outdent()
			->write("} else {\n")
			->indent()
				// We are not inside the anonymous function (first level)
				->write("\$variable_chain = array();\n")
				->write("\$current_chain_location = array();\n")
				->write("\$iterator('" . $this->getAttribute('beginName') . "', array(), \$context, \$variable_chain, \$variable_chain);\n")
			->outdent()
			->write("}\n");
    }
}
