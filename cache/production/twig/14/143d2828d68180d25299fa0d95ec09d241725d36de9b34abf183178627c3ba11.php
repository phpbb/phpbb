<?php

/* posting_layout.html */
class __TwigTemplate_2ed8599ecac63c129101630ff47a14286bdf655be5400c65a60c493cb88908b6 extends Twig_Template
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
        $this->loadTemplate("overall_header.html", "posting_layout.html", 1)->display($context);
        if ($namespace) {
            $this->env->setNamespaceLookUpOrder($previous_look_up_order);
        }
        // line 2
        echo "
";
        // line 3
        if (($context["TOPIC_TITLE"] ?? null)) {
            // line 4
            echo "\t<h2 class=\"posting-title\">";
            echo "<a href=\"";
            echo ($context["U_VIEW_TOPIC"] ?? null);
            echo "\">";
            echo ($context["TOPIC_TITLE"] ?? null);
            echo "</a>";
            echo "</h2>
";
        } else {
            // line 6
            echo "\t<h2 class=\"posting-title\"><a href=\"";
            echo ($context["U_VIEW_FORUM"] ?? null);
            echo "\">";
            echo ($context["FORUM_NAME"] ?? null);
            echo "</a></h2>
 ";
        }
        // line 8
        echo "
";
        // line 9
        if (($context["S_FORUM_RULES"] ?? null)) {
            // line 10
            echo "\t<div class=\"rules";
            if (($context["U_FORUM_RULES"] ?? null)) {
                echo " rules-link";
            }
            echo "\">
\t\t<div class=\"inner\">

\t\t";
            // line 13
            if (($context["U_FORUM_RULES"] ?? null)) {
                // line 14
                echo "\t\t\t<a href=\"";
                echo ($context["U_FORUM_RULES"] ?? null);
                echo "\">";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("FORUM_RULES");
                echo "</a>
\t\t";
            } else {
                // line 16
                echo "\t\t\t<strong>";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("FORUM_RULES");
                echo "</strong><br />
\t\t\t";
                // line 17
                echo ($context["FORUM_RULES"] ?? null);
                echo "
\t\t";
            }
            // line 19
            echo "
\t\t</div>
\t</div>
";
        }
        // line 23
        echo "
<form id=\"postform\" method=\"post\" action=\"";
        // line 24
        echo ($context["S_POST_ACTION"] ?? null);
        echo "\"";
        echo ($context["S_FORM_ENCTYPE"] ?? null);
        echo ">

";
        // line 26
        if (($context["S_DRAFT_LOADED"] ?? null)) {
            // line 27
            echo "\t<div class=\"panel\">
\t\t<div class=\"inner\">

\t\t<h3>";
            // line 30
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("INFORMATION");
            echo "</h3>
\t\t<p>";
            // line 31
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("DRAFT_LOADED");
            echo "</p>

\t\t</div>
\t</div>
";
        }
        // line 36
        echo "
";
        // line 37
        if (($context["S_SHOW_DRAFTS"] ?? null)) {
            $location = "drafts.html";
            $namespace = false;
            if (strpos($location, '@') === 0) {
                $namespace = substr($location, 1, strpos($location, '/') - 1);
                $previous_look_up_order = $this->env->getNamespaceLookUpOrder();
                $this->env->setNamespaceLookUpOrder(array($namespace, '__main__'));
            }
            $this->loadTemplate("drafts.html", "posting_layout.html", 37)->display($context);
            if ($namespace) {
                $this->env->setNamespaceLookUpOrder($previous_look_up_order);
            }
        }
        // line 38
        echo "
";
        // line 39
        if (($context["S_POST_REVIEW"] ?? null)) {
            $location = "posting_review.html";
            $namespace = false;
            if (strpos($location, '@') === 0) {
                $namespace = substr($location, 1, strpos($location, '/') - 1);
                $previous_look_up_order = $this->env->getNamespaceLookUpOrder();
                $this->env->setNamespaceLookUpOrder(array($namespace, '__main__'));
            }
            $this->loadTemplate("posting_review.html", "posting_layout.html", 39)->display($context);
            if ($namespace) {
                $this->env->setNamespaceLookUpOrder($previous_look_up_order);
            }
        }
        // line 40
        echo "
";
        // line 41
        if (($context["S_UNGLOBALISE"] ?? null)) {
            // line 42
            echo "\t<div class=\"panel bg3\">
\t\t<div class=\"inner\">
\t\t<fieldset class=\"fields1\">
\t\t\t<h2>";
            // line 45
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("SELECT_DESTINATION_FORUM");
            echo "</h2>
\t\t\t<p>";
            // line 46
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("UNGLOBALISE_EXPLAIN");
            echo "</p>
\t\t\t<dl>
\t\t\t\t<dt><label for=\"to_forum_id\">";
            // line 48
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("MOVE");
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
            echo "</label></dt>
\t\t\t\t<dd><select id=\"to_forum_id\" name=\"to_forum_id\">";
            // line 49
            echo ($context["S_FORUM_SELECT"] ?? null);
            echo "</select></dd>
\t\t\t</dl>

\t\t\t<dl>
\t\t\t\t<dt>&nbsp;</dt>
\t\t\t\t<dd><input class=\"button1\" type=\"submit\" name=\"post\" value=\"";
            // line 54
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("CONFIRM");
            echo "\" /> <input class=\"button2\" type=\"submit\" name=\"cancel_unglobalise\" value=\"";
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("CANCEL");
            echo "\" /></dd>
\t\t\t</dl>
\t\t</fieldset>

\t\t</div>
\t</div>
";
        }
        // line 61
        echo "
";
        // line 62
        if (($context["S_DISPLAY_PREVIEW"] ?? null)) {
            $location = "posting_preview.html";
            $namespace = false;
            if (strpos($location, '@') === 0) {
                $namespace = substr($location, 1, strpos($location, '/') - 1);
                $previous_look_up_order = $this->env->getNamespaceLookUpOrder();
                $this->env->setNamespaceLookUpOrder(array($namespace, '__main__'));
            }
            $this->loadTemplate("posting_preview.html", "posting_layout.html", 62)->display($context);
            if ($namespace) {
                $this->env->setNamespaceLookUpOrder($previous_look_up_order);
            }
        }
        // line 63
        echo "
<div class=\"panel\" id=\"postingbox\">
\t<div class=\"inner\">

\t<h3>";
        // line 67
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("POST_A");
        echo "</h3>

\t";
        // line 69
        $value = 1;
        $context['definition']->set('EXTRA_POSTING_OPTIONS', $value);
        // line 70
        echo "\t";
        $location = "posting_editor.html";
        $namespace = false;
        if (strpos($location, '@') === 0) {
            $namespace = substr($location, 1, strpos($location, '/') - 1);
            $previous_look_up_order = $this->env->getNamespaceLookUpOrder();
            $this->env->setNamespaceLookUpOrder(array($namespace, '__main__'));
        }
        $this->loadTemplate("posting_editor.html", "posting_layout.html", 70)->display($context);
        if ($namespace) {
            $this->env->setNamespaceLookUpOrder($previous_look_up_order);
        }
        // line 71
        echo "\t<input type=\"hidden\" name=\"show_panel\" value=\"options-panel\" />
\t";
        // line 72
        echo ($context["S_FORM_TOKEN"] ?? null);
        echo "
\t</div>
</div>

";
        // line 76
        if (($context["S_SHOW_ATTACH_BOX"] ?? null)) {
            $location = "posting_attach_body.html";
            $namespace = false;
            if (strpos($location, '@') === 0) {
                $namespace = substr($location, 1, strpos($location, '/') - 1);
                $previous_look_up_order = $this->env->getNamespaceLookUpOrder();
                $this->env->setNamespaceLookUpOrder(array($namespace, '__main__'));
            }
            $this->loadTemplate("posting_attach_body.html", "posting_layout.html", 76)->display($context);
            if ($namespace) {
                $this->env->setNamespaceLookUpOrder($previous_look_up_order);
            }
        }
        // line 77
        echo "
";
        // line 78
        if ((($context["S_SHOW_POLL_BOX"] ?? null) || ($context["S_POLL_DELETE"] ?? null))) {
            $location = "posting_poll_body.html";
            $namespace = false;
            if (strpos($location, '@') === 0) {
                $namespace = substr($location, 1, strpos($location, '/') - 1);
                $previous_look_up_order = $this->env->getNamespaceLookUpOrder();
                $this->env->setNamespaceLookUpOrder(array($namespace, '__main__'));
            }
            $this->loadTemplate("posting_poll_body.html", "posting_layout.html", 78)->display($context);
            if ($namespace) {
                $this->env->setNamespaceLookUpOrder($previous_look_up_order);
            }
        }
        // line 79
        echo "
";
        // line 80
        // line 81
        echo "
";
        // line 82
        if (($context["S_DISPLAY_REVIEW"] ?? null)) {
            $location = "posting_topic_review.html";
            $namespace = false;
            if (strpos($location, '@') === 0) {
                $namespace = substr($location, 1, strpos($location, '/') - 1);
                $previous_look_up_order = $this->env->getNamespaceLookUpOrder();
                $this->env->setNamespaceLookUpOrder(array($namespace, '__main__'));
            }
            $this->loadTemplate("posting_topic_review.html", "posting_layout.html", 82)->display($context);
            if ($namespace) {
                $this->env->setNamespaceLookUpOrder($previous_look_up_order);
            }
        }
        // line 83
        echo "
</form>

";
        // line 86
        $location = "overall_footer.html";
        $namespace = false;
        if (strpos($location, '@') === 0) {
            $namespace = substr($location, 1, strpos($location, '/') - 1);
            $previous_look_up_order = $this->env->getNamespaceLookUpOrder();
            $this->env->setNamespaceLookUpOrder(array($namespace, '__main__'));
        }
        $this->loadTemplate("overall_footer.html", "posting_layout.html", 86)->display($context);
        if ($namespace) {
            $this->env->setNamespaceLookUpOrder($previous_look_up_order);
        }
    }

    public function getTemplateName()
    {
        return "posting_layout.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  312 => 86,  307 => 83,  293 => 82,  290 => 81,  289 => 80,  286 => 79,  272 => 78,  269 => 77,  255 => 76,  248 => 72,  245 => 71,  232 => 70,  229 => 69,  224 => 67,  218 => 63,  204 => 62,  201 => 61,  189 => 54,  181 => 49,  176 => 48,  171 => 46,  167 => 45,  162 => 42,  160 => 41,  157 => 40,  143 => 39,  140 => 38,  126 => 37,  123 => 36,  115 => 31,  111 => 30,  106 => 27,  104 => 26,  97 => 24,  94 => 23,  88 => 19,  83 => 17,  78 => 16,  70 => 14,  68 => 13,  59 => 10,  57 => 9,  54 => 8,  46 => 6,  36 => 4,  34 => 3,  31 => 2,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "posting_layout.html", "");
    }
}
