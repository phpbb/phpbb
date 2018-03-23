<?php

/* acp_board.html */
class __TwigTemplate_417b519e76257a509d7d5115a2614173d2f5e21810a5b3efda26948e4ec50f03 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        $location = "overall_header.html";
        $namespace = false;
        if (strpos($location, '@') === 0) {
            $namespace = substr($location, 1, strpos($location, '/') - 1);
            $previous_look_up_order = $this->env->getNamespaceLookUpOrder();
            $this->env->setNamespaceLookUpOrder(array($namespace, '__main__'));
        }
        $this->loadTemplate("overall_header.html", "acp_board.html", 1)->display($context);
        if ($namespace) {
            $this->env->setNamespaceLookUpOrder($previous_look_up_order);
        }
        // line 2
        echo "
<a id=\"maincontent\"></a>

<h1>";
        // line 5
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("TITLE");
        echo "</h1>

<p>";
        // line 7
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("TITLE_EXPLAIN");
        echo "</p>

";
        // line 9
        if (($context["S_ERROR"] ?? null)) {
            // line 10
            echo "\t<div class=\"errorbox\">
\t\t<h3>";
            // line 11
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("WARNING");
            echo "</h3>
\t\t<p>";
            // line 12
            echo ($context["ERROR_MSG"] ?? null);
            echo "</p>
\t</div>
";
        }
        // line 15
        echo "
<form id=\"acp_board\" method=\"post\" action=\"";
        // line 16
        echo ($context["U_ACTION"] ?? null);
        echo "\">

";
        // line 18
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute(($context["loops"] ?? null), "options", array()));
        foreach ($context['_seq'] as $context["_key"] => $context["options"]) {
            // line 19
            echo "\t";
            if ($this->getAttribute($context["options"], "S_LEGEND", array())) {
                // line 20
                echo "\t\t";
                if ( !$this->getAttribute($context["options"], "S_FIRST_ROW", array())) {
                    // line 21
                    echo "\t\t</fieldset>
\t\t";
                }
                // line 23
                echo "
\t\t<fieldset>
\t\t<legend>";
                // line 25
                echo $this->getAttribute($context["options"], "LEGEND", array());
                echo "</legend>
\t";
            } else {
                // line 27
                echo "
\t\t<dl>
\t\t\t<dt><label for=\"";
                // line 29
                echo $this->getAttribute($context["options"], "KEY", array());
                echo "\">";
                echo $this->getAttribute($context["options"], "TITLE", array());
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
                echo "</label>";
                if ($this->getAttribute($context["options"], "S_EXPLAIN", array())) {
                    echo "<br /><span>";
                    echo $this->getAttribute($context["options"], "TITLE_EXPLAIN", array());
                    echo "</span>";
                }
                echo "</dt>
\t\t\t<dd>";
                // line 30
                echo $this->getAttribute($context["options"], "CONTENT", array());
                echo "</dd>
\t\t</dl>

\t";
            }
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['options'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 35
        echo "
";
        // line 36
        if (($context["S_AUTH"] ?? null)) {
            // line 37
            echo "\t</fieldset>
\t";
            // line 38
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute(($context["loops"] ?? null), "auth_tpl", array()));
            $context['loop'] = array(
              'parent' => $context['_parent'],
              'index0' => 0,
              'index'  => 1,
              'first'  => true,
            );
            if (is_array($context['_seq']) || (is_object($context['_seq']) && $context['_seq'] instanceof Countable)) {
                $length = count($context['_seq']);
                $context['loop']['revindex0'] = $length - 1;
                $context['loop']['revindex'] = $length;
                $context['loop']['length'] = $length;
                $context['loop']['last'] = 1 === $length;
            }
            foreach ($context['_seq'] as $context["_key"] => $context["auth_tpl"]) {
                // line 39
                echo "\t\t";
                $location = (("" . $this->getAttribute($context["auth_tpl"], "TEMPLATE_FILE", array())) . "");
                $namespace = false;
                if (strpos($location, '@') === 0) {
                    $namespace = substr($location, 1, strpos($location, '/') - 1);
                    $previous_look_up_order = $this->env->getNamespaceLookUpOrder();
                    $this->env->setNamespaceLookUpOrder(array($namespace, '__main__'));
                }
                $this->loadTemplate((("" . $this->getAttribute($context["auth_tpl"], "TEMPLATE_FILE", array())) . ""), "acp_board.html", 39)->display($context);
                if ($namespace) {
                    $this->env->setNamespaceLookUpOrder($previous_look_up_order);
                }
                // line 40
                echo "\t";
                ++$context['loop']['index0'];
                ++$context['loop']['index'];
                $context['loop']['first'] = false;
                if (isset($context['loop']['length'])) {
                    --$context['loop']['revindex0'];
                    --$context['loop']['revindex'];
                    $context['loop']['last'] = 0 === $context['loop']['revindex0'];
                }
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['auth_tpl'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 41
            echo "\t<fieldset>
\t\t<legend>";
            // line 42
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ACP_SUBMIT_CHANGES");
            echo "</legend>
";
        }
        // line 44
        echo "
\t<p class=\"submit-buttons\">
\t\t<input class=\"button1\" type=\"submit\" id=\"submit\" name=\"submit\" value=\"";
        // line 46
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("SUBMIT");
        echo "\" />&nbsp;
\t\t<input class=\"button2\" type=\"reset\" id=\"reset\" name=\"reset\" value=\"";
        // line 47
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("RESET");
        echo "\" />
\t</p>
\t";
        // line 49
        echo ($context["S_FORM_TOKEN"] ?? null);
        echo "
</fieldset>
</form>

";
        // line 53
        $location = "overall_footer.html";
        $namespace = false;
        if (strpos($location, '@') === 0) {
            $namespace = substr($location, 1, strpos($location, '/') - 1);
            $previous_look_up_order = $this->env->getNamespaceLookUpOrder();
            $this->env->setNamespaceLookUpOrder(array($namespace, '__main__'));
        }
        $this->loadTemplate("overall_footer.html", "acp_board.html", 53)->display($context);
        if ($namespace) {
            $this->env->setNamespaceLookUpOrder($previous_look_up_order);
        }
    }

    public function getTemplateName()
    {
        return "acp_board.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  200 => 53,  193 => 49,  188 => 47,  184 => 46,  180 => 44,  175 => 42,  172 => 41,  158 => 40,  145 => 39,  128 => 38,  125 => 37,  123 => 36,  120 => 35,  109 => 30,  96 => 29,  92 => 27,  87 => 25,  83 => 23,  79 => 21,  76 => 20,  73 => 19,  69 => 18,  64 => 16,  61 => 15,  55 => 12,  51 => 11,  48 => 10,  46 => 9,  41 => 7,  36 => 5,  31 => 2,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "acp_board.html", "");
    }
}
