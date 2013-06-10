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
 * Represents an include node.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class phpbb_template_twig_node_includejs extends Twig_Node_Include
{
    /**
     * Compiles the node to PHP.
     *
     * @param Twig_Compiler A Twig_Compiler instance
     */
    public function compile(Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this);

        $compiler
            ->write("try {\n")
            ->indent()
        ;

        $this->addGetTemplate($compiler);

        $compiler->raw('->display(');

        $this->addTemplateArguments($compiler);

        $compiler->raw(");\n");

        $compiler
            ->write("} catch (Twig_Error_Loader \$e) {\n")
            ->indent()
            ->write("// ignore missing template\n")
            ->outdent()
            ->write("}\n\n")
        ;
    }
}
