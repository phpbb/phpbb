<?php

/* acp_update.html */
class __TwigTemplate_30189ae53a6d533df9529d12a7cd7cc2be62b84aedb6d2f164220d74b01b1639 extends Twig_Template
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
        $this->loadTemplate("overall_header.html", "acp_update.html", 1)->display($context);
        if ($namespace) {
            $this->env->setNamespaceLookUpOrder($previous_look_up_order);
        }
        // line 2
        echo "
<a id=\"maincontent\"></a>

<h1>";
        // line 5
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("VERSION_CHECK");
        echo "</h1>

<p>";
        // line 7
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("VERSION_CHECK_EXPLAIN");
        echo "</p>

";
        // line 9
        if (($context["S_UPDATE_INCOMPLETE"] ?? null)) {
            // line 10
            echo "\t<div class=\"errorbox\">
\t\t<p>";
            // line 11
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("UPDATE_INCOMPLETE");
            echo " ";
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("UPDATE_INCOMPLETE_MORE");
            echo "</p>
\t</div>
";
        }
        // line 14
        if (($context["S_UP_TO_DATE"] ?? null)) {
            // line 15
            echo "\t<div class=\"successbox\">
\t\t<p>";
            // line 16
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("VERSION_UP_TO_DATE_ACP");
            echo " - <a href=\"";
            echo ($context["U_VERSIONCHECK_FORCE"] ?? null);
            echo "\">";
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("VERSIONCHECK_FORCE_UPDATE");
            echo "</a></p>
\t</div>
";
        } elseif ( !        // line 18
($context["S_UPDATE_INCOMPLETE"] ?? null)) {
            // line 19
            echo "\t<div class=\"errorbox\">
\t\t<p>";
            // line 20
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("VERSION_NOT_UP_TO_DATE_ACP");
            echo " - <a href=\"";
            echo ($context["U_VERSIONCHECK_FORCE"] ?? null);
            echo "\">";
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("VERSIONCHECK_FORCE_UPDATE");
            echo "</a></p>
\t</div>
";
        }
        // line 23
        if (($context["S_VERSION_UPGRADEABLE"] ?? null)) {
            // line 24
            echo "\t<div class=\"errorbox notice\">
\t\t<p>";
            // line 25
            echo ($context["UPGRADE_INSTRUCTIONS"] ?? null);
            echo "</p>
\t</div>
";
        }
        // line 28
        echo "
<fieldset>
\t<legend></legend>
\t";
        // line 31
        if ( !($context["S_UPDATE_INCOMPLETE"] ?? null)) {
            // line 32
            echo "\t<dl>
\t\t<dt><label>";
            // line 33
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("CURRENT_VERSION");
            echo "</label></dt>
\t\t<dd><strong>";
            // line 34
            echo ($context["CURRENT_VERSION"] ?? null);
            echo "</strong></dd>
\t</dl>
\t";
        } else {
            // line 37
            echo "\t<dl>
\t\t<dt><label>";
            // line 38
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("FILES_VERSION");
            echo "</label></dt>
\t\t<dd><strong>";
            // line 39
            echo ($context["FILES_VERSION"] ?? null);
            echo "</strong></dd>
\t</dl>
\t<dl>
\t\t<dt><label>";
            // line 42
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("DATABASE_VERSION");
            echo "</label></dt>
\t\t<dd><strong>";
            // line 43
            echo ($context["CURRENT_VERSION"] ?? null);
            echo "</strong></dd>
\t</dl>
\t";
        }
        // line 46
        echo "</fieldset>

";
        // line 48
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute(($context["loops"] ?? null), "updates_available", array()));
        foreach ($context['_seq'] as $context["_key"] => $context["updates_available"]) {
            // line 49
            echo "\t<fieldset>
\t\t<legend></legend>
\t\t<dl>
\t\t\t<dt><label>";
            // line 52
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("LATEST_VERSION");
            echo "</label></dt>
\t\t\t<dd><strong>";
            // line 53
            echo $this->getAttribute($context["updates_available"], "current", array());
            echo "</strong></dd>
\t\t</dl>
\t\t<dl>
\t\t\t<dt><label>";
            // line 56
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("RELEASE_ANNOUNCEMENT");
            echo "</label></dt>
\t\t\t<dd><strong><a href=\"";
            // line 57
            echo $this->getAttribute($context["updates_available"], "announcement", array());
            echo "\">";
            echo $this->getAttribute($context["updates_available"], "announcement", array());
            echo "</a></strong></dd>
\t\t</dl>
\t</fieldset>
";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['updates_available'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 61
        echo "
";
        // line 62
        if (($context["S_UPDATE_INCOMPLETE"] ?? null)) {
            // line 63
            echo "\t";
            echo ($context["INCOMPLETE_INSTRUCTIONS"] ?? null);
            echo "
\t<br>
";
        }
        // line 66
        echo "
";
        // line 67
        if ( !($context["S_UP_TO_DATE"] ?? null)) {
            // line 68
            echo "\t";
            echo ($context["UPDATE_INSTRUCTIONS"] ?? null);
            echo "
\t<br /><br />
";
        }
        // line 71
        echo "
";
        // line 72
        $location = "overall_footer.html";
        $namespace = false;
        if (strpos($location, '@') === 0) {
            $namespace = substr($location, 1, strpos($location, '/') - 1);
            $previous_look_up_order = $this->env->getNamespaceLookUpOrder();
            $this->env->setNamespaceLookUpOrder(array($namespace, '__main__'));
        }
        $this->loadTemplate("overall_footer.html", "acp_update.html", 72)->display($context);
        if ($namespace) {
            $this->env->setNamespaceLookUpOrder($previous_look_up_order);
        }
    }

    public function getTemplateName()
    {
        return "acp_update.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  208 => 72,  205 => 71,  198 => 68,  196 => 67,  193 => 66,  186 => 63,  184 => 62,  181 => 61,  169 => 57,  165 => 56,  159 => 53,  155 => 52,  150 => 49,  146 => 48,  142 => 46,  136 => 43,  132 => 42,  126 => 39,  122 => 38,  119 => 37,  113 => 34,  109 => 33,  106 => 32,  104 => 31,  99 => 28,  93 => 25,  90 => 24,  88 => 23,  78 => 20,  75 => 19,  73 => 18,  64 => 16,  61 => 15,  59 => 14,  51 => 11,  48 => 10,  46 => 9,  41 => 7,  36 => 5,  31 => 2,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "acp_update.html", "");
    }
}
