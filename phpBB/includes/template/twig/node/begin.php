<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
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
		$compiler
			->write("if (!isset(\$phpbb_blocks)) {\n")
			->indent()
				->write("\$phpbb_blocks = array();\n")
				->write("\$parent = \$context['_phpbb_blocks'];\n")
			->outdent()
			->write("}\n")
			->write("\$phpbb_blocks[] = '" . $this->getAttribute('beginName') . "';\n")
		;

        $compiler
        	->write("if (!empty(\$parent['" . $this->getAttribute('beginName') . "'])) {\n")
        	->indent()
				->write("foreach (\$parent['" . $this->getAttribute('beginName') . "'] as \$" . $this->getAttribute('beginName') . ") {\n")
				->indent()
					// Set up $context correctly so that Twig can get the correct data with $this->getAttribute
					->write("\$this->getEnvironment()->context_recursive_loop_builder(\$" . $this->getAttribute('beginName') . ", \$phpbb_blocks, \$context);\n")

					// We store the parent so that we can do this recursively
					->write("\$parent = \$" . $this->getAttribute('beginName') . ";\n")
        ;

        $compiler->subcompile($this->getNode('body'));

		$compiler
				->outdent()
				->write("}\n")
		;

		if (null !== $this->getNode('else')) {
			$compiler
				->write("} else {\n")
				->indent()
					->subcompile($this->getNode('else'))
				->outdent()
			;
		}

		$compiler
			->outdent()
			->write("}\n")

            // Remove the last item from the blocks storage as we've completed iterating over them all
            ->write("array_pop(\$phpbb_blocks);\n")

            // If we've gone through all of the blocks, we're back at the main level and have completed, so unset the var
            ->write("if (empty(\$phpbb_blocks)) { unset(\$phpbb_blocks); }\n")
        ;
    }

    /**
     * Compiles the node to PHP.
     *
     * Uses anonymous functions to compile the loops, which seems nicer to me, but requires PHP 5.4 (since subcompile uses $this, which is not available in 5.3)
     *
     * @param Twig_Compiler A Twig_Compiler instance
     *
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
    */
}
