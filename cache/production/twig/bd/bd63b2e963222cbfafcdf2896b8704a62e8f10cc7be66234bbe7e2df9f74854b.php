<?php

/* acp_help_phpbb.html */
class __TwigTemplate_4e5877f2f47827b6b020d93fc6c2c757fb78a7315fbde2e18008452831f89ddf extends Twig_Template
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
        $this->loadTemplate("overall_header.html", "acp_help_phpbb.html", 1)->display($context);
        if ($namespace) {
            $this->env->setNamespaceLookUpOrder($previous_look_up_order);
        }
        // line 2
        echo "
<a id=\"maincontent\"></a>

<h1>";
        // line 5
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ACP_HELP_PHPBB");
        echo "</h1>

<form id=\"acp_help_phpbb\" method=\"post\" action=\"";
        // line 7
        echo ($context["U_ACTION"] ?? null);
        echo "\" data-ajax-action=\"";
        echo ($context["U_COLLECT_STATS"] ?? null);
        echo "\">
<div class=\"send-stats-row\">
\t";
        // line 9
        $previous_look_up_order = $this->env->getNamespaceLookUpOrder();
        $this->env->setNamespaceLookUpOrder(array('phpbb_viglink', '__main__'));
        $this->env->loadTemplate('@phpbb_viglink/event/acp_help_phpbb_stats_before.html')->display($context);
        $this->env->setNamespaceLookUpOrder($previous_look_up_order);
        // line 10
        echo "\t<div class=\"send-stats-tile\">
\t\t<h2><i class=\"icon fa-bar-chart\"></i>";
        // line 11
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("SEND_STATISTICS");
        echo "</h2>
\t\t<p>";
        // line 12
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("EXPLAIN_SEND_STATISTICS");
        echo "</p>
\t\t<div class=\"send-stats-row\">
\t\t\t<div class=\"send-stats-data-row send-stats-data-only-row\">
\t\t\t\t<a id=\"trigger-configlist\" data-ajax=\"toggle_link\" data-overlay=\"false\" data-toggle-text=\"";
        // line 15
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("HIDE_STATISTICS");
        echo "\"><span>";
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("SHOW_STATISTICS");
        echo "</span><i class=\"icon fa-angle-down\"></i></a>
\t\t\t</div>
\t\t\t<div class=\"send-stats-data-row\">
\t\t\t\t<div class=\"configlist\" id=\"configlist\">
\t\t\t\t\t";
        // line 19
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute(($context["loops"] ?? null), "providers", array()));
        foreach ($context['_seq'] as $context["_key"] => $context["providers"]) {
            // line 20
            echo "\t\t\t\t\t<fieldset>
\t\t\t\t\t\t<legend>";
            // line 21
            echo $this->getAttribute($context["providers"], "NAME", array());
            echo "</legend>
\t\t\t\t\t\t";
            // line 22
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute($context["providers"], "values", array()));
            foreach ($context['_seq'] as $context["_key"] => $context["values"]) {
                // line 23
                echo "\t\t\t\t\t\t<dl>
\t\t\t\t\t\t\t<dt>";
                // line 24
                echo $this->getAttribute($context["values"], "KEY", array());
                echo "</dt>
\t\t\t\t\t\t\t<dd>";
                // line 25
                echo $this->getAttribute($context["values"], "VALUE", array());
                echo "</dd>
\t\t\t\t\t\t</dl>
\t\t\t\t\t\t";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['values'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 28
            echo "\t\t\t\t\t</fieldset>
\t\t\t\t\t";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['providers'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 30
        echo "\t\t\t\t</div>
\t\t\t</div>
\t\t</div>
\t\t<dl class=\"send-stats-settings\">
\t\t\t<dt>
\t\t\t\t<input name=\"help_send_statistics\" id=\"help_send_statistics\" type=\"checkbox\"";
        // line 35
        if (($context["S_COLLECT_STATS"] ?? null)) {
            echo " checked=\"checked\"";
        }
        echo " />
\t\t\t\t<label for=\"help_send_statistics\"></label>
\t\t\t</dt>
\t\t\t<dd>";
        // line 38
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("SEND_STATISTICS_LONG");
        echo "</dd>
\t\t</dl>
\t</div>
\t";
        // line 41
        $previous_look_up_order = $this->env->getNamespaceLookUpOrder();
        $this->env->setNamespaceLookUpOrder(array('phpbb_viglink', '__main__'));
        $this->env->loadTemplate('@phpbb_viglink/event/acp_help_phpbb_stats_after.html')->display($context);
        $this->env->setNamespaceLookUpOrder($previous_look_up_order);
        // line 42
        echo "\t<fieldset>
\t\t<p class=\"submit-buttons\">
\t\t\t<input type=\"hidden\" name=\"systemdata\" value=\"";
        // line 44
        echo ($context["RAW_DATA"] ?? null);
        echo "\" />
\t\t\t<input type=\"hidden\" name=\"help_send_statistics_time\" value=\"";
        // line 45
        echo ($context["COLLECT_STATS_TIME"] ?? null);
        echo "\" />
\t\t\t<input class=\"button1\" type=\"submit\" id=\"submit\" name=\"submit\" value=\"";
        // line 46
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("SUBMIT");
        echo "\" />
\t\t</p>
\t\t";
        // line 48
        echo ($context["S_FORM_TOKEN"] ?? null);
        echo "
\t</fieldset>
</div>
</form>
<form action=\"";
        // line 52
        echo ($context["U_COLLECT_STATS"] ?? null);
        echo "\" method=\"post\" target=\"questionaire_result\" id=\"questionnaire-form\">
\t<fieldset>
\t\t<p class=\"submit-buttons\">
\t\t\t<input type=\"hidden\" name=\"systemdata\" value=\"";
        // line 55
        echo ($context["RAW_DATA"] ?? null);
        echo "\" />
\t\t\t<input class=\"button1\" type=\"submit\" id=\"submit_stats\" name=\"submit\" value=\"";
        // line 56
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("SEND_STATISTICS");
        echo "\" />
\t\t</p>
\t</fieldset>
</form>

";
        // line 61
        $location = "overall_footer.html";
        $namespace = false;
        if (strpos($location, '@') === 0) {
            $namespace = substr($location, 1, strpos($location, '/') - 1);
            $previous_look_up_order = $this->env->getNamespaceLookUpOrder();
            $this->env->setNamespaceLookUpOrder(array($namespace, '__main__'));
        }
        $this->loadTemplate("overall_footer.html", "acp_help_phpbb.html", 61)->display($context);
        if ($namespace) {
            $this->env->setNamespaceLookUpOrder($previous_look_up_order);
        }
    }

    public function getTemplateName()
    {
        return "acp_help_phpbb.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  181 => 61,  173 => 56,  169 => 55,  163 => 52,  156 => 48,  151 => 46,  147 => 45,  143 => 44,  139 => 42,  134 => 41,  128 => 38,  120 => 35,  113 => 30,  106 => 28,  97 => 25,  93 => 24,  90 => 23,  86 => 22,  82 => 21,  79 => 20,  75 => 19,  66 => 15,  60 => 12,  56 => 11,  53 => 10,  48 => 9,  41 => 7,  36 => 5,  31 => 2,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "acp_help_phpbb.html", "");
    }
}
