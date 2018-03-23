<?php

/* acp_inactive.html */
class __TwigTemplate_3239677e4c8fba6c1a0bdf9dcfab1314b907caa9c254ef7223fbc1af10821b38 extends Twig_Template
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
        $this->loadTemplate("overall_header.html", "acp_inactive.html", 1)->display($context);
        if ($namespace) {
            $this->env->setNamespaceLookUpOrder($previous_look_up_order);
        }
        // line 2
        echo "
<a id=\"maincontent\"></a>

<h1>";
        // line 5
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("INACTIVE_USERS");
        echo "</h1>

<p>";
        // line 7
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("INACTIVE_USERS_EXPLAIN");
        echo "</p>

<form id=\"inactive\" method=\"post\" action=\"";
        // line 9
        echo ($context["U_ACTION"] ?? null);
        echo "\">

";
        // line 11
        if (twig_length_filter($this->env, $this->getAttribute(($context["loops"] ?? null), "pagination", array()))) {
            // line 12
            echo "<div class=\"pagination\">
\t";
            // line 13
            $location = "pagination.html";
            $namespace = false;
            if (strpos($location, '@') === 0) {
                $namespace = substr($location, 1, strpos($location, '/') - 1);
                $previous_look_up_order = $this->env->getNamespaceLookUpOrder();
                $this->env->setNamespaceLookUpOrder(array($namespace, '__main__'));
            }
            $this->loadTemplate("pagination.html", "acp_inactive.html", 13)->display($context);
            if ($namespace) {
                $this->env->setNamespaceLookUpOrder($previous_look_up_order);
            }
            // line 14
            echo "</div>
";
        }
        // line 16
        echo "
<table class=\"table1 zebra-table\">
<thead>
<tr>
\t<th>";
        // line 20
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("USERNAME");
        echo "</th>
\t<th>";
        // line 21
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("EMAIL");
        echo "</th>
\t<th>";
        // line 22
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("JOINED");
        echo "</th>
\t<th>";
        // line 23
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("INACTIVE_DATE");
        echo "</th>
\t<th>";
        // line 24
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("LAST_VISIT");
        echo "</th>
\t<th>";
        // line 25
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("INACTIVE_REASON");
        echo "</th>
\t<th>";
        // line 26
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("MARK");
        echo "</th>
</tr>
</thead>
<tbody>
";
        // line 30
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute(($context["loops"] ?? null), "inactive", array()));
        $context['_iterated'] = false;
        foreach ($context['_seq'] as $context["_key"] => $context["inactive"]) {
            // line 31
            echo "\t<tr>
\t\t<td style=\"vertical-align: top;\">
\t\t\t";
            // line 33
            echo $this->getAttribute($context["inactive"], "USERNAME_FULL", array());
            echo "
\t\t\t";
            // line 34
            if ($this->getAttribute($context["inactive"], "POSTS", array())) {
                echo "<br />";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("POSTS");
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
                echo " <strong>";
                echo $this->getAttribute($context["inactive"], "POSTS", array());
                echo "</strong> [<a href=\"";
                echo $this->getAttribute($context["inactive"], "U_SEARCH_USER", array());
                echo "\">";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("SEARCH_USER_POSTS");
                echo "</a>]";
            }
            // line 35
            echo "\t\t</td>
\t\t<td style=\"vertical-align: top;\">";
            // line 36
            echo $this->getAttribute($context["inactive"], "USER_EMAIL", array());
            echo "</td>
\t\t<td style=\"vertical-align: top;\">";
            // line 37
            echo $this->getAttribute($context["inactive"], "JOINED", array());
            echo "</td>
\t\t<td style=\"vertical-align: top;\">";
            // line 38
            echo $this->getAttribute($context["inactive"], "INACTIVE_DATE", array());
            echo "</td>
\t\t<td style=\"vertical-align: top;\">";
            // line 39
            echo $this->getAttribute($context["inactive"], "LAST_VISIT", array());
            echo "</td>
\t\t<td style=\"vertical-align: top;\">
\t\t\t";
            // line 41
            echo $this->getAttribute($context["inactive"], "REASON", array());
            echo "
\t\t\t";
            // line 42
            if ($this->getAttribute($context["inactive"], "REMINDED", array())) {
                echo "<br />";
                echo $this->getAttribute($context["inactive"], "REMINDED_EXPLAIN", array());
            }
            // line 43
            echo "\t\t</td>
\t\t<td>&nbsp;<input type=\"checkbox\" class=\"radio\" name=\"mark[]\" value=\"";
            // line 44
            echo $this->getAttribute($context["inactive"], "USER_ID", array());
            echo "\" />&nbsp;</td>
\t</tr>
";
            $context['_iterated'] = true;
        }
        if (!$context['_iterated']) {
            // line 47
            echo "\t<tr>
\t\t<td colspan=\"6\" style=\"text-align: center;\">";
            // line 48
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("NO_INACTIVE_USERS");
            echo "</td>
\t</tr>
";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['inactive'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 51
        echo "</tbody>
</table>

<fieldset class=\"display-options\">
\t";
        // line 55
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("DISPLAY_LOG");
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
        echo " &nbsp;";
        echo ($context["S_LIMIT_DAYS"] ?? null);
        echo "&nbsp;";
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("SORT_BY");
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
        echo " ";
        echo ($context["S_SORT_KEY"] ?? null);
        echo " ";
        echo ($context["S_SORT_DIR"] ?? null);
        if (twig_length_filter($this->env, $this->getAttribute(($context["loops"] ?? null), "pagination", array()))) {
            echo "&nbsp;";
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("USERS_PER_PAGE");
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
            echo " <input class=\"inputbox autowidth\" type=\"number\" name=\"users_per_page\" id=\"users_per_page\" min=\"0\" max=\"999\" value=\"";
            echo ($context["USERS_PER_PAGE"] ?? null);
            echo "\" />";
        }
        // line 56
        echo "\t<input class=\"button2\" type=\"submit\" value=\"";
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("GO");
        echo "\" name=\"sort\" />
</fieldset>

<hr />

";
        // line 61
        if (twig_length_filter($this->env, $this->getAttribute(($context["loops"] ?? null), "pagination", array()))) {
            // line 62
            echo "\t<div class=\"pagination\">
\t\t";
            // line 63
            $location = "pagination.html";
            $namespace = false;
            if (strpos($location, '@') === 0) {
                $namespace = substr($location, 1, strpos($location, '/') - 1);
                $previous_look_up_order = $this->env->getNamespaceLookUpOrder();
                $this->env->setNamespaceLookUpOrder(array($namespace, '__main__'));
            }
            $this->loadTemplate("pagination.html", "acp_inactive.html", 63)->display($context);
            if ($namespace) {
                $this->env->setNamespaceLookUpOrder($previous_look_up_order);
            }
            // line 64
            echo "\t</div>
";
        }
        // line 66
        echo "
<fieldset class=\"quick\">
\t<select name=\"action\">";
        // line 68
        echo ($context["S_INACTIVE_OPTIONS"] ?? null);
        echo "</select>
\t<input class=\"button2\" type=\"submit\" name=\"submit\" value=\"";
        // line 69
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("SUBMIT");
        echo "\" />
\t<p class=\"small\"><a href=\"#\" onclick=\"marklist('inactive', 'mark', true); return false;\">";
        // line 70
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("MARK_ALL");
        echo "</a> &bull; <a href=\"#\" onclick=\"marklist('inactive', 'mark', false); return false;\">";
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("UNMARK_ALL");
        echo "</a></p>
\t";
        // line 71
        echo ($context["S_FORM_TOKEN"] ?? null);
        echo "
</fieldset>

</form>

";
        // line 76
        $location = "overall_footer.html";
        $namespace = false;
        if (strpos($location, '@') === 0) {
            $namespace = substr($location, 1, strpos($location, '/') - 1);
            $previous_look_up_order = $this->env->getNamespaceLookUpOrder();
            $this->env->setNamespaceLookUpOrder(array($namespace, '__main__'));
        }
        $this->loadTemplate("overall_footer.html", "acp_inactive.html", 76)->display($context);
        if ($namespace) {
            $this->env->setNamespaceLookUpOrder($previous_look_up_order);
        }
    }

    public function getTemplateName()
    {
        return "acp_inactive.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  269 => 76,  261 => 71,  255 => 70,  251 => 69,  247 => 68,  243 => 66,  239 => 64,  227 => 63,  224 => 62,  222 => 61,  213 => 56,  193 => 55,  187 => 51,  178 => 48,  175 => 47,  167 => 44,  164 => 43,  159 => 42,  155 => 41,  150 => 39,  146 => 38,  142 => 37,  138 => 36,  135 => 35,  122 => 34,  118 => 33,  114 => 31,  109 => 30,  102 => 26,  98 => 25,  94 => 24,  90 => 23,  86 => 22,  82 => 21,  78 => 20,  72 => 16,  68 => 14,  56 => 13,  53 => 12,  51 => 11,  46 => 9,  41 => 7,  36 => 5,  31 => 2,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "acp_inactive.html", "");
    }
}
