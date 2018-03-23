<?php

/* simple_footer.html */
class __TwigTemplate_2c3ec58bfcff354a9150105c15a5551e9929f48ecd903af91a51b88839042da5 extends Twig_Template
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
        echo "\t\t<div style=\"text-align: ";
        echo ($context["S_CONTENT_FLOW_END"] ?? null);
        echo ";\"><a href=\"#\" onclick=\"self.close(); return false;\">";
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("CLOSE_WINDOW");
        echo "</a></div>
\t<br /><br />
</div>

<div id=\"page-footer\">

\t";
        // line 7
        if (($context["S_COPYRIGHT_HTML"] ?? null)) {
            // line 8
            echo "\t\t<br />";
            echo ($context["CREDIT_LINE"] ?? null);
            echo "
\t\t";
            // line 9
            if (($context["TRANSLATION_INFO"] ?? null)) {
                echo "<br />";
                echo ($context["TRANSLATION_INFO"] ?? null);
            }
            // line 10
            echo "\t";
        }
        // line 11
        echo "
\t";
        // line 12
        if (($context["DEBUG_OUTPUT"] ?? null)) {
            // line 13
            echo "\t\t";
            if (($context["S_COPYRIGHT_HTML"] ?? null)) {
                echo "<br /><br />";
            }
            // line 14
            echo "\t\t";
            echo ($context["DEBUG_OUTPUT"] ?? null);
            echo "
\t";
        }
        // line 16
        echo "
</div>

<script type=\"text/javascript\" src=\"";
        // line 19
        echo ($context["T_JQUERY_LINK"] ?? null);
        echo "\"></script>
";
        // line 20
        if (($context["S_ALLOW_CDN"] ?? null)) {
            echo "<script type=\"text/javascript\">window.jQuery || document.write('\\x3Cscript src=\"";
            echo ($context["T_ASSETS_PATH"] ?? null);
            echo "/javascript/jquery.min.js?assets_version=";
            echo ($context["T_ASSETS_VERSION"] ?? null);
            echo "\">\\x3C/script>');</script>";
        }
        // line 21
        echo "<script type=\"text/javascript\" src=\"";
        echo ($context["T_ASSETS_PATH"] ?? null);
        echo "/javascript/core.js?assets_version=";
        echo ($context["T_ASSETS_VERSION"] ?? null);
        echo "\"></script>

";
        // line 23
        // line 24
        echo $this->getAttribute(($context["definition"] ?? null), "SCRIPTS", array());
        echo "

</body>
</html>
";
    }

    public function getTemplateName()
    {
        return "simple_footer.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  88 => 24,  87 => 23,  79 => 21,  71 => 20,  67 => 19,  62 => 16,  56 => 14,  51 => 13,  49 => 12,  46 => 11,  43 => 10,  38 => 9,  33 => 8,  31 => 7,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "simple_footer.html", "");
    }
}
