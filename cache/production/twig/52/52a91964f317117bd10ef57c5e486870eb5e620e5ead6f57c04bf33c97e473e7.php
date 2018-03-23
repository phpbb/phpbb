<?php

/* captcha_default.html */
class __TwigTemplate_9647a1cf3a4b6a2056d36382df1c65bdeacbf69f396712341b04a9b27dc5e456 extends Twig_Template
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
        if ((($context["S_TYPE"] ?? null) == 1)) {
            // line 2
            echo "<div class=\"panel captcha-panel\">
\t<div class=\"inner\">

\t<h3 class=\"captcha-title\">";
            // line 5
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("CONFIRMATION");
            echo "</h3>
\t<p>";
            // line 6
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("CONFIRM_EXPLAIN");
            echo "</p>

\t<fieldset class=\"fields2\">
";
        }
        // line 10
        echo "
\t<dl>
\t\t<dt><label for=\"confirm_code\">";
        // line 12
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("CONFIRM_CODE");
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
        echo "</label></dt>
\t\t<dd class=\"captcha captcha-image\"><img src=\"";
        // line 13
        echo ($context["CONFIRM_IMAGE_LINK"] ?? null);
        echo "\" alt=\"";
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("CONFIRM_CODE");
        echo "\" /></dd>
\t\t<dd><input type=\"text\" name=\"confirm_code\" id=\"confirm_code\" size=\"8\" maxlength=\"8\" tabindex=\"";
        // line 14
        echo $this->getAttribute(($context["definition"] ?? null), "CAPTCHA_TAB_INDEX", array());
        echo "\" class=\"inputbox narrow\" title=\"";
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("CONFIRM_CODE");
        echo "\" />
\t\t";
        // line 15
        if (($context["S_CONFIRM_REFRESH"] ?? null)) {
            echo "<input type=\"submit\" name=\"refresh_vc\" id=\"refresh_vc\" class=\"button2\" value=\"";
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("VC_REFRESH");
            echo "\" />";
        }
        // line 16
        echo "\t\t<input type=\"hidden\" name=\"confirm_id\" id=\"confirm_id\" value=\"";
        echo ($context["CONFIRM_ID"] ?? null);
        echo "\" /></dd>
\t\t<dd>";
        // line 17
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("CONFIRM_CODE_EXPLAIN");
        echo "</dd>
\t</dl>

";
        // line 20
        if ((($context["S_TYPE"] ?? null) == 1)) {
            // line 21
            echo "\t</fieldset>
\t</div>
</div>
";
        }
    }

    public function getTemplateName()
    {
        return "captcha_default.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  77 => 21,  75 => 20,  69 => 17,  64 => 16,  58 => 15,  52 => 14,  46 => 13,  41 => 12,  37 => 10,  30 => 6,  26 => 5,  21 => 2,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "captcha_default.html", "");
    }
}
