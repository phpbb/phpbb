<?php

/* posting_preview.html */
class __TwigTemplate_c462a88239a9074ab5a487ba940e70261a9d54ca4366e88022dba1911efa121f extends Twig_Template
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
        echo "<div class=\"post ";
        if (($context["S_PRIVMSGS"] ?? null)) {
            echo "pm";
        } else {
            echo "bg2";
        }
        echo "\" id=\"preview\">
\t<div class=\"inner\">

";
        // line 4
        if (($context["S_HAS_POLL_OPTIONS"] ?? null)) {
            // line 5
            echo "\t<div class=\"content\">
\t\t<h2>";
            // line 6
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("PREVIEW");
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
            echo " ";
            echo ($context["POLL_QUESTION"] ?? null);
            echo "</h2>
\t\t<p class=\"author\">";
            // line 7
            if (($context["L_POLL_LENGTH"] ?? null)) {
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("POLL_LENGTH");
                echo "<br />";
            }
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("MAX_VOTES");
            echo "</p>

\t\t<fieldset class=\"polls\">
\t\t";
            // line 10
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute(($context["loops"] ?? null), "poll_option", array()));
            foreach ($context['_seq'] as $context["_key"] => $context["poll_option"]) {
                // line 11
                echo "\t\t\t<dl>
\t\t\t\t<dt><label for=\"vote_";
                // line 12
                echo $this->getAttribute($context["poll_option"], "POLL_OPTION_ID", array());
                echo "\">";
                echo $this->getAttribute($context["poll_option"], "POLL_OPTION_CAPTION", array());
                echo "</label></dt>
\t\t\t\t<dd style=\"width: auto;\">";
                // line 13
                if (($context["S_IS_MULTI_CHOICE"] ?? null)) {
                    echo "<input type=\"checkbox\" name=\"vote_id[]\" id=\"vote_";
                    echo $this->getAttribute($context["poll_option"], "POLL_OPTION_ID", array());
                    echo "\" value=\"";
                    echo $this->getAttribute($context["poll_option"], "POLL_OPTION_ID", array());
                    echo "\"";
                    if ($this->getAttribute($context["poll_option"], "POLL_OPTION_VOTED", array())) {
                        echo " checked=\"checked\"";
                    }
                    echo " />";
                } else {
                    echo "<input type=\"radio\" name=\"vote_id[]\" id=\"vote_";
                    echo $this->getAttribute($context["poll_option"], "POLL_OPTION_ID", array());
                    echo "\" value=\"";
                    echo $this->getAttribute($context["poll_option"], "POLL_OPTION_ID", array());
                    echo "\"";
                    if ($this->getAttribute($context["poll_option"], "POLL_OPTION_VOTED", array())) {
                        echo " checked=\"checked\"";
                    }
                    echo " />";
                }
                echo "</dd>
\t\t\t</dl>
\t\t";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['poll_option'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 16
            echo "\t\t</fieldset>
\t</div>

\t</div>
</div>

<div class=\"post bg2\">
\t<div class=\"inner\">

";
        }
        // line 26
        echo "
";
        // line 27
        // line 28
        echo "
\t<div class=\"postbody\">
\t\t<h3>";
        // line 30
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("PREVIEW");
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
        echo " ";
        echo ($context["PREVIEW_SUBJECT"] ?? null);
        echo "</h3>

\t\t<div class=\"content\">";
        // line 32
        echo ($context["PREVIEW_MESSAGE"] ?? null);
        echo "</div>

\t\t";
        // line 34
        if (twig_length_filter($this->env, $this->getAttribute(($context["loops"] ?? null), "attachment", array()))) {
            // line 35
            echo "\t\t<dl class=\"attachbox\">
\t\t\t<dt>";
            // line 36
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ATTACHMENTS");
            echo "</dt>
\t\t\t";
            // line 37
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute(($context["loops"] ?? null), "attachment", array()));
            foreach ($context['_seq'] as $context["_key"] => $context["attachment"]) {
                // line 38
                echo "\t\t\t<dd>";
                echo $this->getAttribute($context["attachment"], "DISPLAY_ATTACHMENT", array());
                echo "</dd>
\t\t\t";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['attachment'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 40
            echo "\t\t</dl>
\t\t";
        }
        // line 42
        echo "
\t\t";
        // line 43
        if (($context["PREVIEW_SIGNATURE"] ?? null)) {
            echo "<div class=\"signature\">";
            echo ($context["PREVIEW_SIGNATURE"] ?? null);
            echo "</div>";
        }
        // line 44
        echo "\t</div>

\t</div>
</div>

<hr />
";
    }

    public function getTemplateName()
    {
        return "posting_preview.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  162 => 44,  156 => 43,  153 => 42,  149 => 40,  140 => 38,  136 => 37,  132 => 36,  129 => 35,  127 => 34,  122 => 32,  114 => 30,  110 => 28,  109 => 27,  106 => 26,  94 => 16,  65 => 13,  59 => 12,  56 => 11,  52 => 10,  42 => 7,  35 => 6,  32 => 5,  30 => 4,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "posting_preview.html", "");
    }
}
