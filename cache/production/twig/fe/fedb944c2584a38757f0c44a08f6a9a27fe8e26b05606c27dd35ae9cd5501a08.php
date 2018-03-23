<?php

/* login_body.html */
class __TwigTemplate_1aa3e63314d57ca676b6cadec0aa6ef752d36917e532120b4731681b111298e9 extends Twig_Template
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
        $this->loadTemplate("overall_header.html", "login_body.html", 1)->display($context);
        if ($namespace) {
            $this->env->setNamespaceLookUpOrder($previous_look_up_order);
        }
        // line 2
        echo "
<form action=\"";
        // line 3
        echo ($context["S_LOGIN_ACTION"] ?? null);
        echo "\" method=\"post\" id=\"login\" data-focus=\"";
        if (($context["S_ADMIN_AUTH"] ?? null)) {
            echo ($context["PASSWORD_CREDENTIAL"] ?? null);
        } else {
            echo ($context["USERNAME_CREDENTIAL"] ?? null);
        }
        echo "\">
<div class=\"panel\">
\t<div class=\"inner\">

\t<div class=\"content\">
\t\t<h2 class=\"login-title\">";
        // line 8
        if (($context["LOGIN_EXPLAIN"] ?? null)) {
            echo ($context["LOGIN_EXPLAIN"] ?? null);
        } else {
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("LOGIN");
        }
        echo "</h2>

\t\t<fieldset ";
        // line 10
        if ( !($context["S_CONFIRM_CODE"] ?? null)) {
            echo "class=\"fields1\"";
        } else {
            echo "class=\"fields2\"";
        }
        echo ">
\t\t";
        // line 11
        if (($context["LOGIN_ERROR"] ?? null)) {
            echo "<div class=\"error\">";
            echo ($context["LOGIN_ERROR"] ?? null);
            echo "</div>";
        }
        // line 12
        echo "\t\t<dl>
\t\t\t<dt><label for=\"";
        // line 13
        echo ($context["USERNAME_CREDENTIAL"] ?? null);
        echo "\">";
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("USERNAME");
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
        echo "</label></dt>
\t\t\t<dd><input type=\"text\" tabindex=\"1\" name=\"";
        // line 14
        echo ($context["USERNAME_CREDENTIAL"] ?? null);
        echo "\" id=\"";
        echo ($context["USERNAME_CREDENTIAL"] ?? null);
        echo "\" size=\"25\" value=\"";
        echo ($context["USERNAME"] ?? null);
        echo "\" class=\"inputbox autowidth\" /></dd>
\t\t</dl>
\t\t<dl>
\t\t\t<dt><label for=\"";
        // line 17
        echo ($context["PASSWORD_CREDENTIAL"] ?? null);
        echo "\">";
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("PASSWORD");
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
        echo "</label></dt>
\t\t\t<dd><input type=\"password\" tabindex=\"2\" id=\"";
        // line 18
        echo ($context["PASSWORD_CREDENTIAL"] ?? null);
        echo "\" name=\"";
        echo ($context["PASSWORD_CREDENTIAL"] ?? null);
        echo "\" size=\"25\" class=\"inputbox autowidth\" autocomplete=\"off\" /></dd>
\t\t\t";
        // line 19
        if ((($context["S_DISPLAY_FULL_LOGIN"] ?? null) && (($context["U_SEND_PASSWORD"] ?? null) || ($context["U_RESEND_ACTIVATION"] ?? null)))) {
            // line 20
            echo "\t\t\t\t";
            if (($context["U_SEND_PASSWORD"] ?? null)) {
                echo "<dd><a href=\"";
                echo ($context["U_SEND_PASSWORD"] ?? null);
                echo "\">";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("FORGOT_PASS");
                echo "</a></dd>";
            }
            // line 21
            echo "\t\t\t\t";
            if (($context["U_RESEND_ACTIVATION"] ?? null)) {
                echo "<dd><a href=\"";
                echo ($context["U_RESEND_ACTIVATION"] ?? null);
                echo "\">";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("RESEND_ACTIVATION");
                echo "</a></dd>";
            }
            // line 22
            echo "\t\t\t";
        }
        // line 23
        echo "\t\t</dl>
\t\t";
        // line 24
        if ((($context["CAPTCHA_TEMPLATE"] ?? null) && ($context["S_CONFIRM_CODE"] ?? null))) {
            // line 25
            echo "\t\t\t";
            $value = 3;
            $context['definition']->set('CAPTCHA_TAB_INDEX', $value);
            // line 26
            echo "\t\t\t";
            $location = (("" . ($context["CAPTCHA_TEMPLATE"] ?? null)) . "");
            $namespace = false;
            if (strpos($location, '@') === 0) {
                $namespace = substr($location, 1, strpos($location, '/') - 1);
                $previous_look_up_order = $this->env->getNamespaceLookUpOrder();
                $this->env->setNamespaceLookUpOrder(array($namespace, '__main__'));
            }
            $this->loadTemplate((("" . ($context["CAPTCHA_TEMPLATE"] ?? null)) . ""), "login_body.html", 26)->display($context);
            if ($namespace) {
                $this->env->setNamespaceLookUpOrder($previous_look_up_order);
            }
            // line 27
            echo "\t\t";
        }
        // line 28
        echo "\t\t";
        if (($context["S_DISPLAY_FULL_LOGIN"] ?? null)) {
            // line 29
            echo "\t\t<dl>
\t\t\t";
            // line 30
            if (($context["S_AUTOLOGIN_ENABLED"] ?? null)) {
                echo "<dd><label for=\"autologin\"><input type=\"checkbox\" name=\"autologin\" id=\"autologin\" tabindex=\"4\" /> ";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("LOG_ME_IN");
                echo "</label></dd>";
            }
            // line 31
            echo "\t\t\t<dd><label for=\"viewonline\"><input type=\"checkbox\" name=\"viewonline\" id=\"viewonline\" tabindex=\"5\" /> ";
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("HIDE_ME");
            echo "</label></dd>
\t\t</dl>
\t\t";
        }
        // line 34
        echo "
\t\t";
        // line 35
        echo ($context["S_LOGIN_REDIRECT"] ?? null);
        echo "
\t\t<dl>
\t\t\t<dt>&nbsp;</dt>
\t\t\t<dd>";
        // line 38
        echo ($context["S_HIDDEN_FIELDS"] ?? null);
        echo "<input type=\"submit\" name=\"login\" tabindex=\"6\" value=\"";
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("LOGIN");
        echo "\" class=\"button1\" /></dd>
\t\t</dl>
\t\t</fieldset>
\t</div>

\t";
        // line 43
        if (( !($context["S_ADMIN_AUTH"] ?? null) && ($context["PROVIDER_TEMPLATE_FILE"] ?? null))) {
            // line 44
            echo "\t\t";
            $location = (("" . ($context["PROVIDER_TEMPLATE_FILE"] ?? null)) . "");
            $namespace = false;
            if (strpos($location, '@') === 0) {
                $namespace = substr($location, 1, strpos($location, '/') - 1);
                $previous_look_up_order = $this->env->getNamespaceLookUpOrder();
                $this->env->setNamespaceLookUpOrder(array($namespace, '__main__'));
            }
            $this->loadTemplate((("" . ($context["PROVIDER_TEMPLATE_FILE"] ?? null)) . ""), "login_body.html", 44)->display($context);
            if ($namespace) {
                $this->env->setNamespaceLookUpOrder($previous_look_up_order);
            }
            // line 45
            echo "\t";
        }
        // line 46
        echo "\t</div>
</div>


";
        // line 50
        if (( !($context["S_ADMIN_AUTH"] ?? null) && ($context["S_REGISTER_ENABLED"] ?? null))) {
            // line 51
            echo "\t<div class=\"panel\">
\t\t<div class=\"inner\">

\t\t<div class=\"content\">
\t\t\t<h3>";
            // line 55
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("REGISTER");
            echo "</h3>
\t\t\t<p>";
            // line 56
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("LOGIN_INFO");
            echo "</p>
\t\t\t<p><strong><a href=\"";
            // line 57
            echo ($context["U_TERMS_USE"] ?? null);
            echo "\">";
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("TERMS_USE");
            echo "</a> | <a href=\"";
            echo ($context["U_PRIVACY"] ?? null);
            echo "\">";
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("PRIVACY");
            echo "</a></strong></p>
\t\t\t<hr class=\"dashed\" />
\t\t\t<p><a href=\"";
            // line 59
            echo ($context["U_REGISTER"] ?? null);
            echo "\" class=\"button2\">";
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("REGISTER");
            echo "</a></p>
\t\t</div>

\t\t</div>
\t</div>
";
        }
        // line 65
        echo "
</form>

";
        // line 68
        $location = "overall_footer.html";
        $namespace = false;
        if (strpos($location, '@') === 0) {
            $namespace = substr($location, 1, strpos($location, '/') - 1);
            $previous_look_up_order = $this->env->getNamespaceLookUpOrder();
            $this->env->setNamespaceLookUpOrder(array($namespace, '__main__'));
        }
        $this->loadTemplate("overall_footer.html", "login_body.html", 68)->display($context);
        if ($namespace) {
            $this->env->setNamespaceLookUpOrder($previous_look_up_order);
        }
    }

    public function getTemplateName()
    {
        return "login_body.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  257 => 68,  252 => 65,  241 => 59,  230 => 57,  226 => 56,  222 => 55,  216 => 51,  214 => 50,  208 => 46,  205 => 45,  192 => 44,  190 => 43,  180 => 38,  174 => 35,  171 => 34,  164 => 31,  158 => 30,  155 => 29,  152 => 28,  149 => 27,  136 => 26,  132 => 25,  130 => 24,  127 => 23,  124 => 22,  115 => 21,  106 => 20,  104 => 19,  98 => 18,  91 => 17,  81 => 14,  74 => 13,  71 => 12,  65 => 11,  57 => 10,  48 => 8,  34 => 3,  31 => 2,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "login_body.html", "");
    }
}
