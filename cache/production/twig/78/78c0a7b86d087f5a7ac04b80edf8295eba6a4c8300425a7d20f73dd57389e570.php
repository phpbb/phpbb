<?php

/* overall_footer.html */
class __TwigTemplate_fb99cf33739fa167d07360ae8a36d13c49f6ae6c6653a646933f9193d7ba381c extends Twig_Template
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
        echo "\t\t\t\t\t\t</div>
\t\t\t\t\t</div><!-- /#main -->
\t\t\t\t</div>
\t\t</div><!-- /#acp -->
\t</div>

\t<div id=\"page-footer\">
\t\t<div class=\"copyright\">
\t\t\t";
        // line 9
        if (($context["S_COPYRIGHT_HTML"] ?? null)) {
            // line 10
            echo "\t\t\t\t";
            echo ($context["CREDIT_LINE"] ?? null);
            echo "
\t\t\t\t";
            // line 11
            if (($context["TRANSLATION_INFO"] ?? null)) {
                echo "<br />";
                echo ($context["TRANSLATION_INFO"] ?? null);
            }
            // line 12
            echo "\t\t\t";
        }
        // line 13
        echo "
\t\t\t";
        // line 14
        if (($context["DEBUG_OUTPUT"] ?? null)) {
            // line 15
            echo "\t\t\t\t";
            if (($context["S_COPYRIGHT_HTML"] ?? null)) {
                echo "<br />";
            }
            // line 16
            echo "\t\t\t\t";
            echo ($context["DEBUG_OUTPUT"] ?? null);
            echo "
\t\t\t";
        }
        // line 18
        echo "\t\t</div>

\t\t<div id=\"darkenwrapper\" data-ajax-error-title=\"";
        // line 20
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("AJAX_ERROR_TITLE");
        echo "\" data-ajax-error-text=\"";
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("AJAX_ERROR_TEXT");
        echo "\" data-ajax-error-text-abort=\"";
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("AJAX_ERROR_TEXT_ABORT");
        echo "\" data-ajax-error-text-timeout=\"";
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("AJAX_ERROR_TEXT_TIMEOUT");
        echo "\" data-ajax-error-text-parsererror=\"";
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("AJAX_ERROR_TEXT_PARSERERROR");
        echo "\">
\t\t\t<div id=\"darken\">&nbsp;</div>
\t\t</div>
\t\t<div id=\"loading_indicator\"></div>

\t\t<div id=\"phpbb_alert\" class=\"phpbb_alert\" data-l-err=\"";
        // line 25
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ERROR");
        echo "\" data-l-timeout-processing-req=\"";
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("TIMEOUT_PROCESSING_REQ");
        echo "\">
\t\t\t<a href=\"#\" class=\"alert_close\"></a>
\t\t\t<h3 class=\"alert_title\"></h3><p class=\"alert_text\"></p>
\t\t</div>
\t\t<div id=\"phpbb_confirm\" class=\"phpbb_alert\">
\t\t\t<a href=\"#\" class=\"alert_close\"></a>
\t\t\t<div class=\"alert_text\"></div>
\t\t</div>
\t</div>
</div>

<script type=\"text/javascript\" src=\"";
        // line 36
        echo ($context["T_JQUERY_LINK"] ?? null);
        echo "\"></script>
";
        // line 37
        if (($context["S_ALLOW_CDN"] ?? null)) {
            echo "<script type=\"text/javascript\">window.jQuery || document.write('\\x3Cscript src=\"";
            echo ($context["T_ASSETS_PATH"] ?? null);
            echo "/javascript/jquery.min.js?assets_version=";
            echo ($context["T_ASSETS_VERSION"] ?? null);
            echo "\">\\x3C/script>');</script>";
        }
        // line 38
        echo "<script type=\"text/javascript\" src=\"";
        echo ($context["T_ASSETS_PATH"] ?? null);
        echo "/javascript/core.js?assets_version=";
        echo ($context["T_ASSETS_VERSION"] ?? null);
        echo "\"></script>
";
        // line 39
        $asset_file = "ajax.js";
        $asset = new \phpbb\template\asset($asset_file, $this->getEnvironment()->get_path_helper(), $this->getEnvironment()->get_filesystem());
        if (substr($asset_file, 0, 2) !== './' && $asset->is_relative()) {
            $asset_path = $asset->get_path();            $local_file = $this->getEnvironment()->get_phpbb_root_path() . $asset_path;
            if (!file_exists($local_file)) {
                $local_file = $this->getEnvironment()->findTemplate($asset_path);
                $asset->set_path($local_file, true);
            }
            $asset->add_assets_version('2');
        }
        $this->getEnvironment()->get_assets_bag()->add_script($asset);        // line 40
        $asset_file = "admin.js";
        $asset = new \phpbb\template\asset($asset_file, $this->getEnvironment()->get_path_helper(), $this->getEnvironment()->get_filesystem());
        if (substr($asset_file, 0, 2) !== './' && $asset->is_relative()) {
            $asset_path = $asset->get_path();            $local_file = $this->getEnvironment()->get_phpbb_root_path() . $asset_path;
            if (!file_exists($local_file)) {
                $local_file = $this->getEnvironment()->findTemplate($asset_path);
                $asset->set_path($local_file, true);
            }
            $asset->add_assets_version('2');
        }
        $this->getEnvironment()->get_assets_bag()->add_script($asset);        // line 41
        echo "
";
        // line 42
        // line 43
        echo $this->getAttribute(($context["definition"] ?? null), "SCRIPTS", array());
        echo "

</body>
</html>
";
    }

    public function getTemplateName()
    {
        return "overall_footer.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  141 => 43,  140 => 42,  137 => 41,  126 => 40,  115 => 39,  108 => 38,  100 => 37,  96 => 36,  80 => 25,  64 => 20,  60 => 18,  54 => 16,  49 => 15,  47 => 14,  44 => 13,  41 => 12,  36 => 11,  31 => 10,  29 => 9,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "overall_footer.html", "");
    }
}
