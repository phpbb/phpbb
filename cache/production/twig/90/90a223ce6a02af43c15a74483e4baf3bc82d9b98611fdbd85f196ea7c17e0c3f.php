<?php

/* acp_ext_list.html */
class __TwigTemplate_4e54e3954231cf69bce79f8bd0958c438278fe452254101a7dc759b6ea22677d extends Twig_Template
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
        $this->loadTemplate("overall_header.html", "acp_ext_list.html", 1)->display($context);
        if ($namespace) {
            $this->env->setNamespaceLookUpOrder($previous_look_up_order);
        }
        // line 2
        echo "
<a id=\"maincontent\"></a>

\t<h1>";
        // line 5
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("EXTENSIONS_ADMIN");
        echo "</h1>

\t<p>";
        // line 7
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("EXTENSIONS_EXPLAIN");
        echo "</p>

\t<fieldset class=\"quick\">
\t\t<span class=\"small\"><a href=\"https://www.phpbb.com/go/customise/extensions/3.1\" target=\"_blank\">";
        // line 10
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("BROWSE_EXTENSIONS_DATABASE");
        echo "</a> &bull; <a href=\"";
        echo ($context["U_VERSIONCHECK_FORCE"] ?? null);
        echo "\">";
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("VERSIONCHECK_FORCE_UPDATE_ALL");
        echo "</a> &bull; <a href=\"javascript:phpbb.toggleDisplay('version_check_settings');\">";
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("SETTINGS");
        echo "</a></span>
\t</fieldset>

\t<form id=\"version_check_settings\" method=\"post\" action=\"";
        // line 13
        echo ($context["U_ACTION"] ?? null);
        echo "\" style=\"display:none\">

\t<fieldset>
\t\t<legend>";
        // line 16
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("EXTENSIONS_VERSION_CHECK_SETTINGS");
        echo "</legend>
\t\t<dl>
\t\t\t<dt><label for=\"force_unstable\">";
        // line 18
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("FORCE_UNSTABLE");
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
        echo "</label></dt>
\t\t\t<dd>
\t\t\t\t<label><input type=\"radio\" id=\"force_unstable\" name=\"force_unstable\" class=\"radio\" value=\"1\"";
        // line 20
        if (($context["FORCE_UNSTABLE"] ?? null)) {
            echo " checked=\"checked\"";
        }
        echo " /> ";
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("YES");
        echo "</label>
\t\t\t\t<label><input type=\"radio\" name=\"force_unstable\" class=\"radio\" value=\"0\"";
        // line 21
        if ( !($context["FORCE_UNSTABLE"] ?? null)) {
            echo " checked=\"checked\"";
        }
        echo " /> ";
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("NO");
        echo "</label>
\t\t\t</dd>
\t\t</dl>

\t\t<p class=\"submit-buttons\">
\t\t\t<input class=\"button1\" type=\"submit\" name=\"update\" value=\"";
        // line 26
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("SUBMIT");
        echo "\" />&nbsp;
\t\t\t<input class=\"button2\" type=\"reset\" name=\"reset\" value=\"";
        // line 27
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("RESET");
        echo "\" />
\t\t\t<input type=\"hidden\" name=\"action\" value=\"set_config_version_check_force_unstable\" />
\t\t\t";
        // line 29
        echo ($context["S_FORM_TOKEN"] ?? null);
        echo "
\t\t</p>
\t</fieldset>
\t</form>

\t<table class=\"table1\">
\t\t<col class=\"row1\" ><col class=\"row1\" ><col class=\"row2\" ><col class=\"row2\" >
\t<thead>
\t\t<tr>
\t\t\t<th>";
        // line 38
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("EXTENSION_NAME");
        echo "</th>
\t\t\t<th style=\"text-align: center; width: 20%;\">";
        // line 39
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("CURRENT_VERSION");
        echo "</th>
\t\t\t<th style=\"text-align: center; width: 10%;\">";
        // line 40
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("EXTENSION_OPTIONS");
        echo "</th>
\t\t\t<th style=\"text-align: center; width: 25%;\">";
        // line 41
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("EXTENSION_ACTIONS");
        echo "</th>
\t\t</tr>
\t</thead>
\t<tbody>
\t\t";
        // line 45
        if (twig_length_filter($this->env, $this->getAttribute(($context["loops"] ?? null), "enabled", array()))) {
            // line 46
            echo "\t\t<tr>
\t\t\t<td class=\"row3\" colspan=\"4\"><strong>";
            // line 47
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("EXTENSIONS_ENABLED");
            echo "</strong>";
            echo "</td>
\t\t</tr>
\t\t";
            // line 49
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute(($context["loops"] ?? null), "enabled", array()));
            foreach ($context['_seq'] as $context["_key"] => $context["enabled"]) {
                // line 50
                echo "\t\t<tr class=\"ext_enabled row-highlight\">
\t\t\t<td><strong title=\"";
                // line 51
                echo $this->getAttribute($context["enabled"], "NAME", array());
                echo "\">";
                echo $this->getAttribute($context["enabled"], "META_DISPLAY_NAME", array());
                echo "</strong>";
                echo "</td>
\t\t\t<td style=\"text-align: center;\">
\t\t\t\t";
                // line 53
                if ($this->getAttribute($context["enabled"], "S_VERSIONCHECK", array())) {
                    // line 54
                    echo "\t\t\t\t<strong class=\"";
                    if ($this->getAttribute($context["enabled"], "S_UP_TO_DATE", array())) {
                        echo "current-ext";
                    } else {
                        echo "outdated-ext";
                    }
                    echo "\">";
                    echo $this->getAttribute($context["enabled"], "META_VERSION", array());
                    echo "</strong>
\t\t\t\t";
                    // line 55
                    if ( !$this->getAttribute($context["enabled"], "S_UP_TO_DATE", array())) {
                        echo "<i class=\"fa fa-exclamation-circle outdated-ext\" aria-hidden=\"true\"></i>";
                    }
                    // line 56
                    echo "\t\t\t\t";
                } else {
                    // line 57
                    echo "\t\t\t\t";
                    echo $this->getAttribute($context["enabled"], "META_VERSION", array());
                    echo "
\t\t\t\t";
                }
                // line 59
                echo "\t\t\t</td>
\t\t\t<td style=\"text-align: center;\"><a href=\"";
                // line 60
                echo $this->getAttribute($context["enabled"], "U_DETAILS", array());
                echo "\">";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("DETAILS");
                echo "</a></td>
\t\t\t<td style=\"text-align: center;\">
\t\t\t\t";
                // line 62
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable($this->getAttribute($context["enabled"], "actions", array()));
                foreach ($context['_seq'] as $context["_key"] => $context["actions"]) {
                    // line 63
                    echo "\t\t\t\t\t<a href=\"";
                    echo $this->getAttribute($context["actions"], "U_ACTION", array());
                    echo "\"";
                    if ($this->getAttribute($context["actions"], "L_ACTION_EXPLAIN", array())) {
                        echo " title=\"";
                        echo $this->getAttribute($context["actions"], "L_ACTION_EXPLAIN", array());
                        echo "\"";
                    }
                    echo ">";
                    echo $this->getAttribute($context["actions"], "L_ACTION", array());
                    echo "</a>
\t\t\t\t\t";
                    // line 64
                    if ( !$this->getAttribute($context["actions"], "S_LAST_ROW", array())) {
                        echo "&nbsp;|&nbsp;";
                    }
                    // line 65
                    echo "\t\t\t\t";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['actions'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 66
                echo "\t\t\t</td>
\t\t</tr>
\t\t";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['enabled'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 69
            echo "\t\t";
        }
        // line 70
        echo "
\t\t";
        // line 71
        if (twig_length_filter($this->env, $this->getAttribute(($context["loops"] ?? null), "disabled", array()))) {
            // line 72
            echo "\t\t<tr>
\t\t\t<td class=\"row3\" colspan=\"4\"><strong>";
            // line 73
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("EXTENSIONS_DISABLED");
            echo "</strong>";
            echo "</td>
\t\t</tr>
\t\t";
            // line 75
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute(($context["loops"] ?? null), "disabled", array()));
            foreach ($context['_seq'] as $context["_key"] => $context["disabled"]) {
                // line 76
                echo "\t\t<tr class=\"ext_disabled row-highlight\">
\t\t\t<td><strong title=\"";
                // line 77
                echo $this->getAttribute($context["disabled"], "NAME", array());
                echo "\">";
                echo $this->getAttribute($context["disabled"], "META_DISPLAY_NAME", array());
                echo "</strong>";
                echo "</td>
\t\t\t<td style=\"text-align: center;\">
\t\t\t\t";
                // line 79
                if ($this->getAttribute($context["disabled"], "S_VERSIONCHECK", array())) {
                    // line 80
                    echo "\t\t\t\t<strong class=\"";
                    if ($this->getAttribute($context["disabled"], "S_UP_TO_DATE", array())) {
                        echo "current-ext";
                    } else {
                        echo "outdated-ext";
                    }
                    echo "\">";
                    echo $this->getAttribute($context["disabled"], "META_VERSION", array());
                    echo "</strong>
\t\t\t\t";
                    // line 81
                    if ( !$this->getAttribute($context["disabled"], "S_UP_TO_DATE", array())) {
                        echo "<i class=\"fa fa-exclamation-circle outdated-ext\" aria-hidden=\"true\"></i>";
                    }
                    // line 82
                    echo "\t\t\t\t";
                } else {
                    // line 83
                    echo "\t\t\t\t";
                    echo $this->getAttribute($context["disabled"], "META_VERSION", array());
                    echo "
\t\t\t\t";
                }
                // line 85
                echo "\t\t\t</td>
\t\t\t<td style=\"text-align: center;\">
\t\t\t\t";
                // line 87
                if ($this->getAttribute($context["disabled"], "U_DETAILS", array())) {
                    echo "<a href=\"";
                    echo $this->getAttribute($context["disabled"], "U_DETAILS", array());
                    echo "\">";
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("DETAILS");
                    echo "</a>";
                }
                // line 88
                echo "\t\t\t</td>
\t\t\t<td style=\"text-align: center;\">
\t\t\t\t";
                // line 90
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable($this->getAttribute($context["disabled"], "actions", array()));
                foreach ($context['_seq'] as $context["_key"] => $context["actions"]) {
                    // line 91
                    echo "\t\t\t\t\t<a href=\"";
                    echo $this->getAttribute($context["actions"], "U_ACTION", array());
                    echo "\"";
                    if ($this->getAttribute($context["actions"], "L_ACTION_EXPLAIN", array())) {
                        echo " title=\"";
                        echo $this->getAttribute($context["actions"], "L_ACTION_EXPLAIN", array());
                        echo "\"";
                    }
                    echo ">";
                    echo $this->getAttribute($context["actions"], "L_ACTION", array());
                    echo "</a>
\t\t\t\t\t";
                    // line 92
                    if ( !$this->getAttribute($context["actions"], "S_LAST_ROW", array())) {
                        echo "&nbsp;|&nbsp;";
                    }
                    // line 93
                    echo "\t\t\t\t";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['actions'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 94
                echo "\t\t\t</td>
\t\t</tr>
\t\t";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['disabled'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 97
            echo "\t\t";
        }
        // line 98
        echo "\t</tbody>
\t</table>

\t<table class=\"table1\">
\t<tr>
\t\t<th>";
        // line 103
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("EXTENSION_INSTALL_HEADLINE");
        echo "</th>
\t</tr>
\t<tr>
\t\t<td class=\"row3\">";
        // line 106
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("EXTENSION_INSTALL_EXPLAIN");
        echo "</td>
\t</tr>
\t<tr>
\t\t<th>";
        // line 109
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("EXTENSION_UPDATE_HEADLINE");
        echo "</th>
\t</tr>
\t<tr>
\t\t<td class=\"row3\">";
        // line 112
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("EXTENSION_UPDATE_EXPLAIN");
        echo "</td>
\t</tr>
\t<tr>
\t\t<th>";
        // line 115
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("EXTENSION_REMOVE_HEADLINE");
        echo "</th>
\t</tr>
\t<tr>
\t\t<td class=\"row3\">";
        // line 118
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("EXTENSION_REMOVE_EXPLAIN");
        echo "</td>
\t</tr>
\t</tbody>
\t</table>

";
        // line 123
        $location = "overall_footer.html";
        $namespace = false;
        if (strpos($location, '@') === 0) {
            $namespace = substr($location, 1, strpos($location, '/') - 1);
            $previous_look_up_order = $this->env->getNamespaceLookUpOrder();
            $this->env->setNamespaceLookUpOrder(array($namespace, '__main__'));
        }
        $this->loadTemplate("overall_footer.html", "acp_ext_list.html", 123)->display($context);
        if ($namespace) {
            $this->env->setNamespaceLookUpOrder($previous_look_up_order);
        }
    }

    public function getTemplateName()
    {
        return "acp_ext_list.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  390 => 123,  382 => 118,  376 => 115,  370 => 112,  364 => 109,  358 => 106,  352 => 103,  345 => 98,  342 => 97,  334 => 94,  328 => 93,  324 => 92,  311 => 91,  307 => 90,  303 => 88,  295 => 87,  291 => 85,  285 => 83,  282 => 82,  278 => 81,  267 => 80,  265 => 79,  257 => 77,  254 => 76,  250 => 75,  244 => 73,  241 => 72,  239 => 71,  236 => 70,  233 => 69,  225 => 66,  219 => 65,  215 => 64,  202 => 63,  198 => 62,  191 => 60,  188 => 59,  182 => 57,  179 => 56,  175 => 55,  164 => 54,  162 => 53,  154 => 51,  151 => 50,  147 => 49,  141 => 47,  138 => 46,  136 => 45,  129 => 41,  125 => 40,  121 => 39,  117 => 38,  105 => 29,  100 => 27,  96 => 26,  84 => 21,  76 => 20,  70 => 18,  65 => 16,  59 => 13,  47 => 10,  41 => 7,  36 => 5,  31 => 2,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "acp_ext_list.html", "");
    }
}
