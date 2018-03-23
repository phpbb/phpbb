<?php

/* acp_styles.html */
class __TwigTemplate_4e8a5f7f0f5507855d67c166747af2cb14ad24c34ddc279538e422f8ad7f8080 extends Twig_Template
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
        $this->loadTemplate("overall_header.html", "acp_styles.html", 1)->display($context);
        if ($namespace) {
            $this->env->setNamespaceLookUpOrder($previous_look_up_order);
        }
        // line 2
        echo "
<a id=\"maincontent\"></a>

";
        // line 5
        if (($context["S_STYLE_DETAILS"] ?? null)) {
            // line 6
            echo "\t<a href=\"";
            echo ($context["U_ACTION"] ?? null);
            echo "\" style=\"float: ";
            echo ($context["S_CONTENT_FLOW_END"] ?? null);
            echo ";\">&laquo; ";
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("BACK");
            echo "</a>
";
        }
        // line 8
        echo "
";
        // line 9
        if (($context["S_CONFIRM_ACTION"] ?? null)) {
            // line 10
            echo "<form id=\"confirm\" method=\"post\" action=\"";
            echo ($context["S_CONFIRM_ACTION"] ?? null);
            echo "\">

<fieldset>
\t<h1>";
            // line 13
            echo ($context["MESSAGE_TITLE"] ?? null);
            echo "</h1>
\t<p>";
            // line 14
            echo ($context["MESSAGE_TEXT"] ?? null);
            echo "</p>
\t";
            // line 15
            if (($context["S_CONFIRM_DELETE"] ?? null)) {
                // line 16
                echo "\t<label><input type=\"checkbox\" class=\"checkbox\" name=\"confirm_delete_files\" /> ";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("DELETE_FROM_FS");
                echo "</label>
\t";
            }
            // line 18
            echo "
\t";
            // line 19
            echo ($context["S_HIDDEN_FIELDS"] ?? null);
            echo "

\t<div style=\"text-align: center;\">
\t\t<input type=\"submit\" name=\"confirm\" value=\"";
            // line 22
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("YES");
            echo "\" class=\"button2\" />&nbsp;
\t\t<input type=\"submit\" name=\"cancel\" value=\"";
            // line 23
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("NO");
            echo "\" class=\"button2\" />
\t</div>

</fieldset>

</form>
";
        } else {
            // line 30
            echo "
";
            // line 31
            if (($context["L_TITLE"] ?? null)) {
                echo "<h1>";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("TITLE");
                echo "</h1>";
            }
            // line 32
            echo "
";
            // line 33
            if (($context["L_EXPLAIN"] ?? null)) {
                echo "<p>";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("EXPLAIN");
                echo "</p>";
            }
            // line 34
            echo "
<fieldset class=\"quick\">
\t<span class=\"small\"><a href=\"https://www.phpbb.com/go/customise/styles/3.1\" target=\"_blank\">";
            // line 36
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("BROWSE_STYLES_DATABASE");
            echo "</a></span>
</fieldset>

<form id=\"acp_styles\" method=\"post\" action=\"";
            // line 39
            echo ($context["U_ACTION"] ?? null);
            echo "\">
";
            // line 40
            echo ($context["S_HIDDEN_FIELDS"] ?? null);
            echo "
";
            // line 41
            echo ($context["S_FORM_TOKEN"] ?? null);
            echo "

";
            // line 43
            if (($context["S_STYLE_DETAILS"] ?? null)) {
                // line 44
                echo "\t<input type=\"hidden\" name=\"id\" value=\"";
                echo ($context["STYLE_ID"] ?? null);
                echo "\" />
\t<fieldset>
\t<dl>
\t\t<dt><label for=\"name\">";
                // line 47
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("STYLE_NAME");
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
                echo "</label></dt>
\t\t<dd><input type=\"text\" id=\"name\" name=\"style_name\" value=\"";
                // line 48
                echo ($context["STYLE_NAME"] ?? null);
                echo "\" /></dd>
\t</dl>
\t<dl>
\t\t<dt><label>";
                // line 51
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("STYLE_PATH");
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
                echo "</label></dt>
\t\t<dd><strong>";
                // line 52
                echo ($context["STYLE_PATH"] ?? null);
                echo "</strong></dd>
\t</dl>
\t<dl>
\t\t<dt><label>";
                // line 55
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("STYLE_VERSION");
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
                echo "</label></dt>
\t\t<dd><strong>";
                // line 56
                echo ($context["STYLE_VERSION"] ?? null);
                echo "</strong></dd>
\t</dl>
\t<dl>
\t\t<dt><label for=\"name\">";
                // line 59
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COPYRIGHT");
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
                echo "</label></dt>
\t\t<dd><strong>";
                // line 60
                echo ($context["STYLE_COPYRIGHT"] ?? null);
                echo "</strong></dd>
\t</dl>
\t<dl>
\t\t<dt><label for=\"style_parent\">";
                // line 63
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("INHERITING_FROM");
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
                echo "</label></dt>
\t\t<dd><select id=\"style_parent\" name=\"style_parent\">
\t\t\t<option value=\"\"";
                // line 65
                if ((($context["STYLE_PARENT"] ?? null) == 0)) {
                    echo " selected=\"selected\"";
                }
                echo "> - </option>
\t\t\t";
                // line 66
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable($this->getAttribute(($context["loops"] ?? null), "parent_styles", array()));
                foreach ($context['_seq'] as $context["_key"] => $context["parent_styles"]) {
                    // line 67
                    echo "\t\t\t\t<option value=\"";
                    echo $this->getAttribute($context["parent_styles"], "STYLE_ID", array());
                    echo "\"";
                    if (($this->getAttribute($context["parent_styles"], "STYLE_ID", array()) == ($context["STYLE_PARENT"] ?? null))) {
                        echo " selected=\"selected\"";
                    }
                    echo ">";
                    echo $this->getAttribute($context["parent_styles"], "SPACER", array());
                    echo $this->getAttribute($context["parent_styles"], "STYLE_NAME", array());
                    echo "</option>
\t\t\t";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['parent_styles'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 69
                echo "\t\t</select></dd>
\t</dl>
\t<dl>
\t\t<dt><label for=\"style_active\">";
                // line 72
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("STYLE_ACTIVE");
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
                echo "</label></dt>
\t\t<dd><label><input type=\"radio\" class=\"radio\" name=\"style_active\" value=\"1\"";
                // line 73
                if (($context["S_STYLE_ACTIVE"] ?? null)) {
                    echo " id=\"style_active\" checked=\"checked\"";
                }
                echo " /> ";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("YES");
                echo "</label>
\t\t\t<label><input type=\"radio\" class=\"radio\" name=\"style_active\" value=\"0\"";
                // line 74
                if ( !($context["S_STYLE_ACTIVE"] ?? null)) {
                    echo " id=\"style_active\" checked=\"checked\"";
                }
                echo " /> ";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("NO");
                echo "</label></dd>
\t</dl>
\t";
                // line 76
                if ( !($context["S_STYLE_DEFAULT"] ?? null)) {
                    // line 77
                    echo "\t\t<dl>
\t\t\t<dt><label for=\"style_default\">";
                    // line 78
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("STYLE_DEFAULT");
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
                    echo "</label></dt>
\t\t\t<dd><label><input type=\"radio\" class=\"radio\" name=\"style_default\" value=\"1\" /> ";
                    // line 79
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("YES");
                    echo "</label>
\t\t\t\t<label><input type=\"radio\" class=\"radio\" id=\"style_default\" name=\"style_default\" value=\"0\" checked=\"checked\" /> ";
                    // line 80
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("NO");
                    echo "</label></dd>
\t\t</dl>
\t";
                }
                // line 83
                echo "\t</fieldset>

\t<fieldset class=\"submit-buttons\">
\t\t<legend>";
                // line 86
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("SUBMIT");
                echo "</legend>
\t\t<input class=\"button1\" type=\"submit\" name=\"update\" value=\"";
                // line 87
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("SUBMIT");
                echo "\" />&nbsp;
\t\t<input class=\"button2\" type=\"reset\" id=\"reset\" name=\"reset\" value=\"";
                // line 88
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("RESET");
                echo "\" />
\t\t";
                // line 89
                echo ($context["S_FORM_TOKEN"] ?? null);
                echo "
\t</fieldset>
";
            }
            // line 92
            echo "
";
            // line 93
            if (twig_length_filter($this->env, $this->getAttribute(($context["loops"] ?? null), "styles_list", array()))) {
                // line 94
                echo "\t";
                // line 95
                echo "\t<table class=\"table1 styles\">
\t<thead>
\t<tr>
\t\t<th>";
                // line 98
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("STYLE_NAME");
                echo "</th>
\t\t";
                // line 99
                if ( !($context["STYLES_LIST_HIDE_COUNT"] ?? null)) {
                    echo "<th width=\"10%\" style=\"white-space: nowrap; text-align: center;\">";
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("STYLE_USED_BY");
                    echo "</th>";
                }
                // line 100
                echo "\t\t<th width=\"25%\" style=\"white-space: nowrap; text-align: center;\">";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ACTIONS");
                echo "</th>
\t\t";
                // line 101
                echo ($context["STYLES_LIST_EXTRA"] ?? null);
                echo "
\t\t<th>&nbsp;</th>
\t</tr>
\t</thead>
\t";
                // line 105
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable($this->getAttribute(($context["loops"] ?? null), "styles_list", array()));
                foreach ($context['_seq'] as $context["_key"] => $context["styles_list"]) {
                    // line 106
                    echo "\t<tbody id=\"styles-list-";
                    echo $this->getAttribute($context["styles_list"], "S_ROW_COUNT", array());
                    echo "\">
\t<tr class=\"row-highlight";
                    // line 107
                    if (($this->getAttribute($context["styles_list"], "STYLE_ID", array()) &&  !$this->getAttribute($context["styles_list"], "STYLE_ACTIVE", array()))) {
                        echo " row-inactive";
                    }
                    echo "\">
\t\t";
                    // line 108
                    if (($this->getAttribute($context["styles_list"], "LEVEL", array()) % 2 == 1)) {
                        // line 109
                        echo "\t\t\t";
                        if (($this->getAttribute(($context["definition"] ?? null), "ROW_CLASS", array()) == "row1a")) {
                            $value = "row1b";
                            $context['definition']->set('ROW_CLASS', $value);
                        } else {
                            $value = "row1a";
                            $context['definition']->set('ROW_CLASS', $value);
                        }
                        // line 110
                        echo "\t\t";
                    } else {
                        // line 111
                        echo "\t\t\t";
                        if (($this->getAttribute(($context["definition"] ?? null), "ROW_CLASS", array()) == "row2a")) {
                            $value = "row2b";
                            $context['definition']->set('ROW_CLASS', $value);
                        } else {
                            $value = "row2a";
                            $context['definition']->set('ROW_CLASS', $value);
                        }
                        // line 112
                        echo "\t\t";
                    }
                    // line 113
                    echo "\t\t<td class=\"";
                    echo $this->getAttribute(($context["definition"] ?? null), "ROW_CLASS", array());
                    echo "\" style=\"padding-";
                    echo ($context["S_CONTENT_FLOW_BEGIN"] ?? null);
                    echo ": ";
                    echo $this->getAttribute($context["styles_list"], "PADDING", array());
                    echo "px;\">
\t\t\t";
                    // line 114
                    if ((($this->getAttribute($context["styles_list"], "STYLE_ID", array()) && ($this->getAttribute($context["styles_list"], "COMMENT", array()) == "")) && $this->getAttribute($context["styles_list"], "STYLE_ACTIVE", array()))) {
                        // line 115
                        echo "\t\t\t\t<div class=\"default-style\" style=\"display: none; float: ";
                        echo ($context["S_CONTENT_FLOW_END"] ?? null);
                        echo ";\">
\t\t\t\t\t<input class=\"radio\" type=\"radio\" name=\"default\" value=\"";
                        // line 116
                        echo $this->getAttribute($context["styles_list"], "STYLE_ID", array());
                        echo "\"";
                        if ($this->getAttribute($context["styles_list"], "DEFAULT", array())) {
                            echo " checked=\"checked\"";
                        } else {
                            $value = 1;
                            $context['definition']->set('S_DEFAULT', $value);
                        }
                        echo " title=\"";
                        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("STYLE_DEFAULT");
                        echo "\" />
\t\t\t\t</div>
\t\t\t";
                    }
                    // line 119
                    echo "\t\t\t";
                    if (($this->getAttribute($context["styles_list"], "DEFAULT", array()) || $this->getAttribute($context["styles_list"], "SHOW_COPYRIGHT", array()))) {
                        // line 120
                        echo "\t\t\t\t<strong>";
                        echo $this->getAttribute($context["styles_list"], "STYLE_NAME", array());
                        echo "</strong>
\t\t\t\t";
                        // line 121
                        if (($this->getAttribute($context["styles_list"], "SHOW_COPYRIGHT", array()) && ($this->getAttribute($context["styles_list"], "COMMENT", array()) == ""))) {
                            echo "<span><br />";
                            echo $this->getAttribute($context["styles_list"], "STYLE_COPYRIGHT", array());
                            echo "</span>";
                        }
                        // line 122
                        echo "\t\t\t";
                    } else {
                        // line 123
                        echo "\t\t\t\t<span>";
                        echo $this->getAttribute($context["styles_list"], "STYLE_NAME", array());
                        echo "</span>
\t\t\t";
                    }
                    // line 125
                    echo "\t\t\t";
                    if (($this->getAttribute($context["styles_list"], "COMMENT", array()) != "")) {
                        // line 126
                        echo "\t\t\t\t<span class=\"error\"><br />";
                        echo $this->getAttribute($context["styles_list"], "COMMENT", array());
                        echo "</span>
\t\t\t";
                    }
                    // line 128
                    echo "\t\t\t";
                    if (( !$this->getAttribute($context["styles_list"], "STYLE_ID", array()) && ($this->getAttribute($context["styles_list"], "COMMENT", array()) == ""))) {
                        // line 129
                        echo "\t\t\t\t<span class=\"style-path\"><br />";
                        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("STYLE_PATH");
                        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
                        echo " ";
                        echo $this->getAttribute($context["styles_list"], "STYLE_PATH_FULL", array());
                        echo "</span>
\t\t\t";
                    }
                    // line 131
                    echo "\t\t</td>
\t\t";
                    // line 132
                    if ( !($context["STYLES_LIST_HIDE_COUNT"] ?? null)) {
                        // line 133
                        echo "\t\t\t<td class=\"";
                        echo $this->getAttribute(($context["definition"] ?? null), "ROW_CLASS", array());
                        echo " users\">";
                        echo $this->getAttribute($context["styles_list"], "USERS", array());
                        echo "</td>
\t\t";
                    }
                    // line 135
                    echo "\t\t<td class=\"";
                    echo $this->getAttribute(($context["definition"] ?? null), "ROW_CLASS", array());
                    echo " actions\">
\t\t\t";
                    // line 136
                    $context['_parent'] = $context;
                    $context['_seq'] = twig_ensure_traversable($this->getAttribute($context["styles_list"], "actions", array()));
                    foreach ($context['_seq'] as $context["_key"] => $context["actions"]) {
                        // line 137
                        echo "\t\t\t\t";
                        if (($this->getAttribute($context["actions"], "S_ROW_COUNT", array()) > 0)) {
                            echo " | ";
                        }
                        // line 138
                        echo "\t\t\t\t";
                        if ($this->getAttribute($context["actions"], "U_ACTION", array())) {
                            // line 139
                            echo "\t\t\t\t\t<a href=\"";
                            echo $this->getAttribute($context["actions"], "U_ACTION", array());
                            echo "\"";
                            echo $this->getAttribute($context["actions"], "U_ACTION_ATTR", array());
                            echo ">";
                            echo $this->getAttribute($context["actions"], "L_ACTION", array());
                            echo "</a>
\t\t\t\t";
                        }
                        // line 141
                        echo "\t\t\t\t";
                        echo $this->getAttribute($context["actions"], "HTML", array());
                        echo "
\t\t\t";
                    }
                    $_parent = $context['_parent'];
                    unset($context['_seq'], $context['_iterated'], $context['_key'], $context['actions'], $context['_parent'], $context['loop']);
                    $context = array_intersect_key($context, $_parent) + $_parent;
                    // line 143
                    echo "\t\t</td>
\t\t";
                    // line 144
                    echo $this->getAttribute($context["styles_list"], "EXTRA", array());
                    echo "
\t\t<td class=\"";
                    // line 145
                    echo $this->getAttribute(($context["definition"] ?? null), "ROW_CLASS", array());
                    echo " mark\" width=\"20\">
\t\t\t";
                    // line 146
                    if ($this->getAttribute($context["styles_list"], "STYLE_ID", array())) {
                        // line 147
                        echo "\t\t\t\t<input class=\"checkbox\" type=\"checkbox\" name=\"ids[]\" value=\"";
                        echo $this->getAttribute($context["styles_list"], "STYLE_ID", array());
                        echo "\" />
\t\t\t";
                    } else {
                        // line 149
                        echo "\t\t\t\t";
                        if (($this->getAttribute($context["styles_list"], "COMMENT", array()) != "")) {
                            // line 150
                            echo "\t\t\t\t\t&nbsp;
\t\t\t\t";
                        } else {
                            // line 152
                            echo "\t\t\t\t\t<input class=\"checkbox\" type=\"checkbox\" name=\"dirs[]\" value=\"";
                            echo $this->getAttribute($context["styles_list"], "STYLE_PATH", array());
                            echo "\" />
\t\t\t\t";
                        }
                        // line 154
                        echo "\t\t\t";
                    }
                    // line 155
                    echo "\t\t</td>
\t</tr>
\t</tbody>
\t";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['styles_list'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 159
                echo "\t</table>
";
            }
            // line 161
            echo "
";
            // line 162
            if (twig_length_filter($this->env, $this->getAttribute(($context["loops"] ?? null), "extra_actions", array()))) {
                // line 163
                echo "\t<fieldset class=\"quick\">
\t\t";
                // line 164
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable($this->getAttribute(($context["loops"] ?? null), "extra_actions", array()));
                foreach ($context['_seq'] as $context["_key"] => $context["extra_actions"]) {
                    // line 165
                    echo "\t\t\t<input type=\"submit\" name=\"";
                    echo $this->getAttribute($context["extra_actions"], "ACTION_NAME", array());
                    echo "\" class=\"button2\" value=\"";
                    echo $this->getAttribute($context["extra_actions"], "L_ACTION", array());
                    echo "\" />
\t\t";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['extra_actions'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 167
                echo "\t</fieldset>
";
            }
            // line 169
            echo "
</form>

";
        }
        // line 173
        echo "
";
        // line 174
        $location = "overall_footer.html";
        $namespace = false;
        if (strpos($location, '@') === 0) {
            $namespace = substr($location, 1, strpos($location, '/') - 1);
            $previous_look_up_order = $this->env->getNamespaceLookUpOrder();
            $this->env->setNamespaceLookUpOrder(array($namespace, '__main__'));
        }
        $this->loadTemplate("overall_footer.html", "acp_styles.html", 174)->display($context);
        if ($namespace) {
            $this->env->setNamespaceLookUpOrder($previous_look_up_order);
        }
    }

    public function getTemplateName()
    {
        return "acp_styles.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  578 => 174,  575 => 173,  569 => 169,  565 => 167,  554 => 165,  550 => 164,  547 => 163,  545 => 162,  542 => 161,  538 => 159,  529 => 155,  526 => 154,  520 => 152,  516 => 150,  513 => 149,  507 => 147,  505 => 146,  501 => 145,  497 => 144,  494 => 143,  485 => 141,  475 => 139,  472 => 138,  467 => 137,  463 => 136,  458 => 135,  450 => 133,  448 => 132,  445 => 131,  436 => 129,  433 => 128,  427 => 126,  424 => 125,  418 => 123,  415 => 122,  409 => 121,  404 => 120,  401 => 119,  386 => 116,  381 => 115,  379 => 114,  370 => 113,  367 => 112,  358 => 111,  355 => 110,  346 => 109,  344 => 108,  338 => 107,  333 => 106,  329 => 105,  322 => 101,  317 => 100,  311 => 99,  307 => 98,  302 => 95,  300 => 94,  298 => 93,  295 => 92,  289 => 89,  285 => 88,  281 => 87,  277 => 86,  272 => 83,  266 => 80,  262 => 79,  257 => 78,  254 => 77,  252 => 76,  243 => 74,  235 => 73,  230 => 72,  225 => 69,  209 => 67,  205 => 66,  199 => 65,  193 => 63,  187 => 60,  182 => 59,  176 => 56,  171 => 55,  165 => 52,  160 => 51,  154 => 48,  149 => 47,  142 => 44,  140 => 43,  135 => 41,  131 => 40,  127 => 39,  121 => 36,  117 => 34,  111 => 33,  108 => 32,  102 => 31,  99 => 30,  89 => 23,  85 => 22,  79 => 19,  76 => 18,  70 => 16,  68 => 15,  64 => 14,  60 => 13,  53 => 10,  51 => 9,  48 => 8,  38 => 6,  36 => 5,  31 => 2,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "acp_styles.html", "");
    }
}
