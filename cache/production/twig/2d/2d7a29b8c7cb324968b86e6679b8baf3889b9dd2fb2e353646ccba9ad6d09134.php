<?php

/* simple_header.html */
class __TwigTemplate_f9d956fe3affba716eda31758b72097df341b0eb3861e26d2733f9ed04347f5d extends Twig_Template
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

<link href=\"style/admin.css?assets_version=";
        // line 10
        echo ($context["T_ASSETS_VERSION"] ?? null);
        echo "\" rel=\"stylesheet\" type=\"text/css\" media=\"screen\" />

<script type=\"text/javascript\">
// <![CDATA[
var jump_page = '";
        // line 14
        echo twig_escape_filter($this->env, $this->env->getExtension('phpbb\template\twig\extension')->lang("JUMP_PAGE"), "js");
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
        echo "';
var on_page = '";
        // line 15
        echo ($context["CURRENT_PAGE"] ?? null);
        echo "';
var per_page = '";
        // line 16
        echo ($context["PER_PAGE"] ?? null);
        echo "';
var base_url = '";
        // line 17
        echo twig_escape_filter($this->env, ($context["BASE_URL"] ?? null), "js");
        echo "';

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
\tvar parent = document.getElementById(id);
\tif (!parent)
\t{
\t\treturn;
\t}

\tvar rb = parent.getElementsByTagName('input');
\t
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

// ]]>
</script>
";
        // line 87
        // line 88
        echo $this->getAttribute(($context["definition"] ?? null), "STYLESHEETS", array());
        echo "
";
        // line 89
        // line 90
        echo "</head>

<body class=\"";
        // line 92
        echo ($context["S_CONTENT_DIRECTION"] ?? null);
        echo " ";
        echo ($context["BODY_CLASS"] ?? null);
        echo "\">

";
        // line 94
        // line 95
        echo "
<div id=\"page-body\" class=\"simple-page-body\">
";
    }

    public function getTemplateName()
    {
        return "simple_header.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  153 => 95,  152 => 94,  145 => 92,  141 => 90,  140 => 89,  136 => 88,  135 => 87,  62 => 17,  58 => 16,  54 => 15,  49 => 14,  42 => 10,  36 => 8,  32 => 7,  22 => 2,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "simple_header.html", "");
    }
}
