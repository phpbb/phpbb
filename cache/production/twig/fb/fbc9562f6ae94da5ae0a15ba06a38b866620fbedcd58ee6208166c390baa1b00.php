<?php

/* posting_poll_body.html */
class __TwigTemplate_e6fdddc2219ddca579cbd9126287f03c34704d5d91f5c6623530228508d5dd47 extends Twig_Template
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
        echo "<div class=\"panel bg3\" id=\"poll-panel\">
\t<div class=\"inner\">

\t";
        // line 4
        if (($context["S_SHOW_POLL_BOX"] ?? null)) {
            // line 5
            echo "\t<p>";
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ADD_POLL_EXPLAIN");
            echo "</p>
\t";
        }
        // line 7
        echo "
\t<fieldset class=\"fields2\">
\t";
        // line 9
        if (($context["S_POLL_DELETE"] ?? null)) {
            // line 10
            echo "\t\t<dl>
\t\t\t<dt><label for=\"poll_delete\">";
            // line 11
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("POLL_DELETE");
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
            echo "</label></dt>
\t\t\t<dd><label for=\"poll_delete\"><input type=\"checkbox\" name=\"poll_delete\" id=\"poll_delete\"";
            // line 12
            if (($context["S_POLL_DELETE_CHECKED"] ?? null)) {
                echo " checked=\"checked\"";
            }
            echo " /> </label></dd>
\t\t</dl>
\t";
        }
        // line 15
        echo "
\t";
        // line 16
        if (($context["S_SHOW_POLL_BOX"] ?? null)) {
            // line 17
            echo "\t\t<dl>
\t\t\t<dt><label for=\"poll_title\">";
            // line 18
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("POLL_QUESTION");
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
            echo "</label></dt>
\t\t\t<dd><input type=\"text\" name=\"poll_title\" id=\"poll_title\" maxlength=\"255\" value=\"";
            // line 19
            echo ($context["POLL_TITLE"] ?? null);
            echo "\" class=\"inputbox\" /></dd>
\t\t</dl>
\t\t<dl>
\t\t\t<dt><label for=\"poll_option_text\">";
            // line 22
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("POLL_OPTIONS");
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
            echo "</label><br /><span>";
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("POLL_OPTIONS_EXPLAIN");
            echo "</span></dt>
\t\t\t<dd><textarea name=\"poll_option_text\" id=\"poll_option_text\" rows=\"5\" cols=\"35\" class=\"inputbox\">";
            // line 23
            echo ($context["POLL_OPTIONS"] ?? null);
            echo "</textarea></dd>
\t\t</dl>

\t\t<hr class=\"dashed\" />

\t\t<dl>
\t\t\t<dt><label for=\"poll_max_options\">";
            // line 29
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("POLL_MAX_OPTIONS");
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
            echo "</label></dt>
\t\t\t<dd><input type=\"number\" min=\"0\" max=\"999\" name=\"poll_max_options\" id=\"poll_max_options\" value=\"";
            // line 30
            echo ($context["POLL_MAX_OPTIONS"] ?? null);
            echo "\" class=\"inputbox autowidth\" /></dd>
\t\t\t<dd>";
            // line 31
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("POLL_MAX_OPTIONS_EXPLAIN");
            echo "</dd>
\t\t</dl>
\t\t<dl>
\t\t\t<dt><label for=\"poll_length\">";
            // line 34
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("POLL_FOR");
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
            echo "</label></dt>
\t\t\t<dd><label for=\"poll_length\"><input type=\"number\" min=\"0\" max=\"999\" name=\"poll_length\" id=\"poll_length\" value=\"";
            // line 35
            echo ($context["POLL_LENGTH"] ?? null);
            echo "\" class=\"inputbox autowidth\" /> ";
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("DAYS");
            echo "</label></dd>
\t\t\t<dd>";
            // line 36
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("POLL_FOR_EXPLAIN");
            echo "</dd>
\t\t</dl>

\t\t";
            // line 39
            if (($context["S_POLL_VOTE_CHANGE"] ?? null)) {
                // line 40
                echo "\t\t\t<hr class=\"dashed\" />

\t\t\t<dl>
\t\t\t\t<dt><label for=\"poll_vote_change\">";
                // line 43
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("POLL_VOTE_CHANGE");
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
                echo "</label></dt>
\t\t\t\t<dd><label for=\"poll_vote_change\"><input type=\"checkbox\" id=\"poll_vote_change\" name=\"poll_vote_change\"";
                // line 44
                echo ($context["VOTE_CHANGE_CHECKED"] ?? null);
                echo " /> ";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("POLL_VOTE_CHANGE_EXPLAIN");
                echo "</label></dd>
\t\t\t</dl>
\t\t";
            }
            // line 47
            echo "\t";
        }
        // line 48
        echo "
\t";
        // line 49
        // line 50
        echo "\t</fieldset>

\t</div>
</div>
";
    }

    public function getTemplateName()
    {
        return "posting_poll_body.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  148 => 50,  147 => 49,  144 => 48,  141 => 47,  133 => 44,  128 => 43,  123 => 40,  121 => 39,  115 => 36,  109 => 35,  104 => 34,  98 => 31,  94 => 30,  89 => 29,  80 => 23,  73 => 22,  67 => 19,  62 => 18,  59 => 17,  57 => 16,  54 => 15,  46 => 12,  41 => 11,  38 => 10,  36 => 9,  32 => 7,  26 => 5,  24 => 4,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "posting_poll_body.html", "");
    }
}
