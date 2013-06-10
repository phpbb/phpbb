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
		$compiler
			->write("if (!isset(\$loops)) {\n")
			->indent()
			->write("\$loops = array();")
			->write("\$nestingLevel = 0;")
			->outdent()
			->write("}\n")
			->write("\$loops[\$nestingLevel] = array();\n")
		;

        if (null !== $this->getNode('else')) {
            $compiler->write("\$loops[\$nestingLevel]['iterated'] = false;\n");
        }

        $compiler
			->write("if (isset(\$context['loop']['" . $this->getAttribute('beginName') . "'])) {")
			->write("foreach (\$context['loop']['". $this->getAttribute('beginName'). "'] as \$" . $this->getAttribute('beginName') . ") {")
			->write("\$context['". $this->getAttribute('beginName'). "'] = \$" . $this->getAttribute('beginName') . ";")
			->indent()
        ;

        $compiler->subcompile($this->getNode('body'));

        if (null !== $this->getNode('else')) {
            $compiler->write("\$loops[\$nestingLevel]['iterated'] = true;\n");
        }

        $compiler
            ->outdent()
            ->write("}}\n")
        ;

        if (null !== $this->getNode('else')) {
            $compiler
                ->write("if (!\$loops[\$nestingLevel]['iterated']) {\n")
                ->indent()
                ->subcompile($this->getNode('else'))
                ->outdent()
                ->write("}\n")
            ;
        }

		$compiler->write("\$nestingLevel--;\n");
    }
}