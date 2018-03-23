<?php

/* overall_header.html */
class __TwigTemplate_9ad44103aaaceaf2f9054a000eac6115dc9ca116b8c4cd136fa3c19dd5f591c5 extends Twig_Template
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
        echo "<!DOCTYPE html>
<html dir=\"";
        // line 2
        echo ($context["S_CONTENT_DIRECTION"] ?? null);
        echo "\" lang=\"";
        echo ($context["S_USER_LANG"] ?? null);
        echo "\">
<head>
<meta charset=\"utf-8\">
<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\" />
";
        // line 7
        if (($context["META"] ?? null)) {
            echo ($context["META"] ?? null);
        }
        // line 8
        echo "<title>";
        echo ($context["PAGE_TITLE"] ?? null);
        echo "</title>

<link href=\"";
        // line 10
        echo ($context["T_FONT_AWESOME_LINK"] ?? null);
        echo "\" rel=\"stylesheet\">
<link href=\"style/admin.css?assets_version=";
        // line 11
        echo ($context["T_ASSETS_VERSION"] ?? null);
        echo "\" rel=\"stylesheet\" type=\"text/css\" media=\"screen\" />

<script type=\"text/javascript\">
// <![CDATA[
var jump_page = '";
        // line 15
        echo twig_escape_filter($this->env, $this->env->getExtension('phpbb\template\twig\extension')->lang("JUMP_PAGE"), "js");
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
        echo "';
var on_page = '";
        // line 16
        echo ($context["CURRENT_PAGE"] ?? null);
        echo "';
var per_page = '";
        // line 17
        echo ($context["PER_PAGE"] ?? null);
        echo "';
var base_url = '";
        // line 18
        echo twig_escape_filter($this->env, ($context["BASE_URL"] ?? null), "js");
        echo "';

/**
* Jump to page
*/
function jumpto()
{
\tvar page = prompt(jump_page, on_page);

\tif (page !== null && !isNaN(page) && page == Math.floor(page) && page > 0)
\t{
\t\tif (base_url.indexOf('?') == -1)
\t\t{
\t\t\tdocument.location.href = base_url + '?start=' + ((page - 1) * per_page);
\t\t}
\t\telse
\t\t{
\t\t\tdocument.location.href = base_url.replace(/&amp;/g, '&') + '&start=' + ((page - 1) * per_page);
\t\t}
\t}
}

/**
* Mark/unmark checkboxes
* id = ID of parent container, name = name prefix, state = state [true/false]
*/
function marklist(id, name, state)
{
\tvar parent = document.getElementById(id) || document[id];

\tif (!parent)
\t{
\t\treturn;
\t}

\tvar rb = parent.getElementsByTagName('input');

\tfor (var r = 0; r < rb.length; r++)
\t{
\t\tif (rb[r].name.substr(0, name.length) == name && rb[r].disabled !== true)
\t\t{
\t\t\trb[r].checked = state;
\t\t}
\t}
}

/**
* Find a member
*/
function find_username(url)
{
\tpopup(url, 760, 570, '_usersearch');
\treturn false;
}

/**
* Window popup
*/
function popup(url, width, height, name)
{
\tif (!name)
\t{
\t\tname = '_popup';
\t}

\twindow.open(url.replace(/&amp;/g, '&'), name, 'height=' + height + ',resizable=yes,scrollbars=yes, width=' + width);
\treturn false;
}

// ]]>
</script>

";
        // line 90
        // line 91
        echo "
";
        // line 92
        echo $this->getAttribute(($context["definition"] ?? null), "STYLESHEETS", array());
        echo "

";
        // line 94
        // line 95
        echo "
</head>

<body class=\"";
        // line 98
        echo ($context["S_CONTENT_DIRECTION"] ?? null);
        echo " ";
        echo ($context["BODY_CLASS"] ?? null);
        echo " nojs\">

";
        // line 100
        // line 101
        echo "
<div id=\"wrap\">
\t<div id=\"page-header\">
\t\t<h1>";
        // line 104
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ADMIN_PANEL");
        echo "</h1>
\t\t<p><a href=\"";
        // line 105
        echo ($context["U_ADM_INDEX"] ?? null);
        echo "\">";
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ADMIN_INDEX");
        echo "</a> &bull; <a href=\"";
        echo ($context["U_INDEX"] ?? null);
        echo "\">";
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("FORUM_INDEX");
        echo "</a></p>
\t\t<p id=\"skip\"><a href=\"#acp\">";
        // line 106
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("SKIP");
        echo "</a></p>
\t</div>

\t<div id=\"page-body\">
\t\t<div id=\"tabs\">
\t\t\t<ul>
\t\t\t";
        // line 112
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute(($context["loops"] ?? null), "t_block1", array()));
        foreach ($context['_seq'] as $context["_key"] => $context["t_block1"]) {
            // line 113
            echo "\t\t\t\t<li class=\"tab";
            if ($this->getAttribute($context["t_block1"], "S_SELECTED", array())) {
                echo " activetab";
            }
            echo "\"><a href=\"";
            echo $this->getAttribute($context["t_block1"], "U_TITLE", array());
            echo "\">";
            echo $this->getAttribute($context["t_block1"], "L_TITLE", array());
            echo "</a></li>
\t\t\t";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['t_block1'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 115
        echo "\t\t\t</ul>
\t\t</div>

\t\t<div id=\"acp\">
\t\t\t\t<div id=\"content\">
\t\t\t\t\t<div id=\"menu\">
\t\t\t\t\t\t<p>";
        // line 121
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("LOGGED_IN_AS");
        echo "<br /><strong>";
        echo ($context["USERNAME"] ?? null);
        echo "</strong> [&nbsp;<a href=\"";
        echo ($context["U_LOGOUT"] ?? null);
        echo "\">";
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("LOGOUT");
        echo "</a>&nbsp;][&nbsp;<a href=\"";
        echo ($context["U_ADM_LOGOUT"] ?? null);
        echo "\">";
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ADM_LOGOUT");
        echo "</a>&nbsp;]&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
\t\t\t\t\t\t";
        // line 122
        $value = 0;
        $context['definition']->set('LI_USED', $value);
        // line 123
        echo "\t\t\t\t\t\t";
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute(($context["loops"] ?? null), "l_block1", array()));
        foreach ($context['_seq'] as $context["_key"] => $context["l_block1"]) {
            // line 124
            echo "\t\t\t\t\t\t\t";
            if ($this->getAttribute($context["l_block1"], "S_SELECTED", array())) {
                // line 125
                echo "
\t\t\t\t\t\t";
                // line 126
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable($this->getAttribute($context["l_block1"], "l_block2", array()));
                foreach ($context['_seq'] as $context["_key"] => $context["l_block2"]) {
                    // line 127
                    echo "\t\t\t\t\t\t\t";
                    if (twig_length_filter($this->env, $this->getAttribute($context["l_block2"], "l_block3", array()))) {
                        // line 128
                        echo "\t\t\t\t\t\t\t";
                        if ($this->getAttribute(($context["definition"] ?? null), "LI_USED", array())) {
                            echo "</ul></div>";
                        }
                        // line 129
                        echo "\t\t\t\t\t\t\t<div class=\"menu-block\">
\t\t\t\t\t\t\t\t<a class=\"header\" href=\"javascript:void(0);\">";
                        // line 130
                        echo $this->getAttribute($context["l_block2"], "L_TITLE", array());
                        echo "</a>
\t\t\t\t\t\t\t\t<ul>
\t\t\t\t\t\t\t";
                        // line 132
                        $value = 1;
                        $context['definition']->set('LI_USED', $value);
                        // line 133
                        echo "\t\t\t\t\t\t\t";
                    }
                    // line 134
                    echo "
\t\t\t\t\t\t\t";
                    // line 135
                    $context['_parent'] = $context;
                    $context['_seq'] = twig_ensure_traversable($this->getAttribute($context["l_block2"], "l_block3", array()));
                    foreach ($context['_seq'] as $context["_key"] => $context["l_block3"]) {
                        // line 136
                        echo "\t\t\t\t\t\t\t\t<li";
                        if ($this->getAttribute($context["l_block3"], "S_SELECTED", array())) {
                            echo " id=\"activemenu\"";
                        }
                        echo "><a href=\"";
                        echo $this->getAttribute($context["l_block3"], "U_TITLE", array());
                        echo "\"><span>";
                        echo $this->getAttribute($context["l_block3"], "L_TITLE", array());
                        echo "</span></a></li>
\t\t\t\t\t\t\t\t";
                        // line 137
                        $value = 1;
                        $context['definition']->set('LI_USED', $value);
                        // line 138
                        echo "\t\t\t\t\t\t\t";
                    }
                    $_parent = $context['_parent'];
                    unset($context['_seq'], $context['_iterated'], $context['_key'], $context['l_block3'], $context['_parent'], $context['loop']);
                    $context = array_intersect_key($context, $_parent) + $_parent;
                    // line 139
                    echo "\t\t\t\t\t\t";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['l_block2'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 140
                echo "
\t\t\t\t\t\t\t";
            }
            // line 142
            echo "\t\t\t\t\t\t";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['l_block1'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 143
        echo "\t\t\t\t\t\t";
        if ($this->getAttribute(($context["definition"] ?? null), "LI_USED", array())) {
            // line 144
            echo "\t\t\t\t\t\t\t\t</ul>
\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t";
        }
        // line 147
        echo "\t\t\t\t\t</div>

\t\t\t\t\t<div id=\"main\">
\t\t\t\t\t\t<div class=\"main\">
\t\t\t\t\t\t\t";
        // line 151
        if ((($context["CONTAINER_EXCEPTION"] ?? null) !== false)) {
            // line 152
            echo "\t\t\t\t\t\t\t<div class=\"errorbox\">
\t\t\t\t\t\t\t\t<p>";
            // line 153
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("CONTAINER_EXCEPTION");
            echo "</p><br />
\t\t\t\t\t\t\t\t<p>";
            // line 154
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("EXCEPTION");
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
            echo " ";
            echo $this->getAttribute(($context["CONTAINER_EXCEPTION"] ?? null), "getMessage", array(), "method");
            echo "</p>
\t\t\t\t\t\t\t\t<pre>";
            // line 155
            echo $this->getAttribute(($context["CONTAINER_EXCEPTION"] ?? null), "getTraceAsString", array(), "method");
            echo "</pre>
\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t";
        }
    }

    public function getTemplateName()
    {
        return "overall_header.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  346 => 155,  339 => 154,  335 => 153,  332 => 152,  330 => 151,  324 => 147,  319 => 144,  316 => 143,  310 => 142,  306 => 140,  300 => 139,  294 => 138,  291 => 137,  280 => 136,  276 => 135,  273 => 134,  270 => 133,  267 => 132,  262 => 130,  259 => 129,  254 => 128,  251 => 127,  247 => 126,  244 => 125,  241 => 124,  236 => 123,  233 => 122,  219 => 121,  211 => 115,  196 => 113,  192 => 112,  183 => 106,  173 => 105,  169 => 104,  164 => 101,  163 => 100,  156 => 98,  151 => 95,  150 => 94,  145 => 92,  142 => 91,  141 => 90,  66 => 18,  62 => 17,  58 => 16,  53 => 15,  46 => 11,  42 => 10,  36 => 8,  32 => 7,  22 => 2,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "overall_header.html", "");
    }
}
