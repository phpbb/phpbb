<?php

/* faq_body.html */
class __TwigTemplate_fd4604de117da943f7ed663c257711c60431059c6cdf27c164b0c61d36c6af2b extends Twig_Template
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
        $this->loadTemplate("overall_header.html", "faq_body.html", 1)->display($context);
        if ($namespace) {
            $this->env->setNamespaceLookUpOrder($previous_look_up_order);
        }
        // line 2
        echo "
<h2 class=\"faq-title\">";
        // line 3
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("FAQ_TITLE");
        echo "</h2>


<div class=\"panel bg1\" id=\"faqlinks\">
\t<div class=\"inner\">
\t\t<div class=\"column1\">
\t\t";
        // line 9
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute(($context["loops"] ?? null), "faq_block", array()));
        foreach ($context['_seq'] as $context["_key"] => $context["faq_block"]) {
            // line 10
            echo "\t\t\t";
            if (($this->getAttribute($context["faq_block"], "SWITCH_COLUMN", array()) || (($context["SWITCH_COLUMN_MANUALLY"] ?? null) && ($this->getAttribute($context["faq_block"], "S_ROW_COUNT", array()) == 4)))) {
                // line 11
                echo "\t\t\t\t</div>

\t\t\t\t<div class=\"column2\">
\t\t\t";
            }
            // line 15
            echo "
\t\t\t<dl class=\"faq\">
\t\t\t\t<dt><strong>";
            // line 17
            echo $this->getAttribute($context["faq_block"], "BLOCK_TITLE", array());
            echo "</strong></dt>
\t\t\t\t";
            // line 18
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute($context["faq_block"], "faq_row", array()));
            foreach ($context['_seq'] as $context["_key"] => $context["faq_row"]) {
                // line 19
                echo "\t\t\t\t\t<dd><a href=\"#f";
                echo $this->getAttribute($context["faq_block"], "S_ROW_COUNT", array());
                echo "r";
                echo $this->getAttribute($context["faq_row"], "S_ROW_COUNT", array());
                echo "\">";
                echo $this->getAttribute($context["faq_row"], "FAQ_QUESTION", array());
                echo "</a></dd>
\t\t\t\t";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['faq_row'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 21
            echo "\t\t\t</dl>
\t\t";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['faq_block'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 23
        echo "\t\t</div>
\t</div>
</div>

";
        // line 27
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute(($context["loops"] ?? null), "faq_block", array()));
        foreach ($context['_seq'] as $context["_key"] => $context["faq_block"]) {
            // line 28
            echo "\t<div class=\"panel ";
            if (($this->getAttribute($context["faq_block"], "S_ROW_COUNT", array()) % 2 == 1)) {
                echo "bg1";
            } else {
                echo "bg2";
            }
            echo "\">
\t\t<div class=\"inner\">

\t\t<div class=\"content\">
\t\t\t<h2 class=\"faq-title\">";
            // line 32
            echo $this->getAttribute($context["faq_block"], "BLOCK_TITLE", array());
            echo "</h2>
\t\t\t";
            // line 33
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute($context["faq_block"], "faq_row", array()));
            foreach ($context['_seq'] as $context["_key"] => $context["faq_row"]) {
                // line 34
                echo "\t\t\t\t<dl class=\"faq\">
\t\t\t\t\t<dt id=\"f";
                // line 35
                echo $this->getAttribute($context["faq_block"], "S_ROW_COUNT", array());
                echo "r";
                echo $this->getAttribute($context["faq_row"], "S_ROW_COUNT", array());
                echo "\"><strong>";
                echo $this->getAttribute($context["faq_row"], "FAQ_QUESTION", array());
                echo "</strong></dt>
\t\t\t\t\t<dd>";
                // line 36
                echo $this->getAttribute($context["faq_row"], "FAQ_ANSWER", array());
                echo "</dd>
\t\t\t\t</dl>
\t\t\t\t<a href=\"#faqlinks\" class=\"top\">
\t\t\t\t\t<i class=\"icon fa-chevron-circle-up fa-fw icon-gray\" aria-hidden=\"true\"></i><span>";
                // line 39
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("BACK_TO_TOP");
                echo "</span>
\t\t\t\t</a>
\t\t\t\t";
                // line 41
                if ( !$this->getAttribute($context["faq_row"], "S_LAST_ROW", array())) {
                    echo "<hr class=\"dashed\" />";
                }
                // line 42
                echo "\t\t\t";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['faq_row'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 43
            echo "\t\t</div>

\t\t</div>
\t</div>
";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['faq_block'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 48
        echo "
";
        // line 49
        $location = "jumpbox.html";
        $namespace = false;
        if (strpos($location, '@') === 0) {
            $namespace = substr($location, 1, strpos($location, '/') - 1);
            $previous_look_up_order = $this->env->getNamespaceLookUpOrder();
            $this->env->setNamespaceLookUpOrder(array($namespace, '__main__'));
        }
        $this->loadTemplate("jumpbox.html", "faq_body.html", 49)->display($context);
        if ($namespace) {
            $this->env->setNamespaceLookUpOrder($previous_look_up_order);
        }
        // line 50
        $location = "overall_footer.html";
        $namespace = false;
        if (strpos($location, '@') === 0) {
            $namespace = substr($location, 1, strpos($location, '/') - 1);
            $previous_look_up_order = $this->env->getNamespaceLookUpOrder();
            $this->env->setNamespaceLookUpOrder(array($namespace, '__main__'));
        }
        $this->loadTemplate("overall_footer.html", "faq_body.html", 50)->display($context);
        if ($namespace) {
            $this->env->setNamespaceLookUpOrder($previous_look_up_order);
        }
    }

    public function getTemplateName()
    {
        return "faq_body.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  175 => 50,  163 => 49,  160 => 48,  150 => 43,  144 => 42,  140 => 41,  135 => 39,  129 => 36,  121 => 35,  118 => 34,  114 => 33,  110 => 32,  98 => 28,  94 => 27,  88 => 23,  81 => 21,  68 => 19,  64 => 18,  60 => 17,  56 => 15,  50 => 11,  47 => 10,  43 => 9,  34 => 3,  31 => 2,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "faq_body.html", "");
    }
}
