<?php

/* jumpbox.html */
class __TwigTemplate_dbed853ce439447d9a0e05b8b8dcb51315736aabdcbc0f1e72605ab2cf629c4e extends Twig_Template
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
        echo "
<div class=\"action-bar actions-jump\">
\t";
        // line 3
        if (($context["S_VIEWTOPIC"] ?? null)) {
            // line 4
            echo "\t<p class=\"jumpbox-return\">
\t\t<a href=\"";
            // line 5
            echo ($context["U_VIEW_FORUM"] ?? null);
            echo "\" class=\"left-box arrow-";
            echo ($context["S_CONTENT_FLOW_BEGIN"] ?? null);
            echo "\" accesskey=\"r\">
\t\t\t<i class=\"icon fa-angle-";
            // line 6
            echo ($context["S_CONTENT_FLOW_BEGIN"] ?? null);
            echo " fa-fw icon-black\" aria-hidden=\"true\"></i><span>";
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("RETURN_TO_FORUM");
            echo "</span>
\t\t</a>
\t</p>
\t";
        } elseif (        // line 9
($context["S_VIEWFORUM"] ?? null)) {
            // line 10
            echo "\t<p class=\"jumpbox-return\">
\t\t<a href=\"";
            // line 11
            echo ($context["U_INDEX"] ?? null);
            echo "\" class=\"left-box arrow-";
            echo ($context["S_CONTENT_FLOW_BEGIN"] ?? null);
            echo "\" accesskey=\"r\">
\t\t\t<i class=\"icon fa-angle-";
            // line 12
            echo ($context["S_CONTENT_FLOW_BEGIN"] ?? null);
            echo " fa-fw icon-black\" aria-hidden=\"true\"></i><span>";
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("RETURN_TO_INDEX");
            echo "</span>
\t\t</a>
\t</p>
\t";
        } elseif (        // line 15
($context["SEARCH_TOPIC"] ?? null)) {
            // line 16
            echo "\t<p class=\"jumpbox-return\">
\t\t<a class=\"left-box arrow-";
            // line 17
            echo ($context["S_CONTENT_FLOW_BEGIN"] ?? null);
            echo "\" href=\"";
            echo ($context["U_SEARCH_TOPIC"] ?? null);
            echo "\" accesskey=\"r\">
\t\t\t<i class=\"icon fa-angle-";
            // line 18
            echo ($context["S_CONTENT_FLOW_BEGIN"] ?? null);
            echo " fa-fw icon-black\" aria-hidden=\"true\"></i><span>";
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("RETURN_TO_TOPIC");
            echo "</span>
\t\t</a>
\t</p>
\t";
        } elseif (        // line 21
($context["S_SEARCH_ACTION"] ?? null)) {
            // line 22
            echo "\t<p class=\"jumpbox-return\">
\t\t<a class=\"left-box arrow-";
            // line 23
            echo ($context["S_CONTENT_FLOW_BEGIN"] ?? null);
            echo "\" href=\"";
            echo ($context["U_SEARCH"] ?? null);
            echo "\" title=\"";
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("SEARCH_ADV");
            echo "\" accesskey=\"r\">
\t\t\t<i class=\"icon fa-angle-";
            // line 24
            echo ($context["S_CONTENT_FLOW_BEGIN"] ?? null);
            echo " fa-fw icon-black\" aria-hidden=\"true\"></i><span>";
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("GO_TO_SEARCH_ADV");
            echo "</span>
\t\t</a>
\t</p>
\t";
        }
        // line 28
        echo "
\t";
        // line 29
        if (($context["S_DISPLAY_JUMPBOX"] ?? null)) {
            // line 30
            echo "\t<div class=\"jumpbox dropdown-container dropdown-container-right";
            if ( !($context["S_IN_MCP"] ?? null)) {
                echo " dropdown-up";
            }
            echo " dropdown-";
            echo ($context["S_CONTENT_FLOW_BEGIN"] ?? null);
            echo " dropdown-button-control\" id=\"jumpbox\">
\t\t\t<span title=\"";
            // line 31
            if ((($context["S_IN_MCP"] ?? null) && ($context["S_MERGE_SELECT"] ?? null))) {
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("SELECT_TOPICS_FROM");
            } elseif (($context["S_IN_MCP"] ?? null)) {
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("MODERATE_FORUM");
            } else {
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("JUMP_TO");
            }
            echo "\" class=\"button button-secondary dropdown-trigger dropdown-select\">
\t\t\t\t<span>";
            // line 32
            if ((($context["S_IN_MCP"] ?? null) && ($context["S_MERGE_SELECT"] ?? null))) {
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("SELECT_TOPICS_FROM");
            } elseif (($context["S_IN_MCP"] ?? null)) {
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("MODERATE_FORUM");
            } else {
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("JUMP_TO");
            }
            echo "</span>
\t\t\t\t<span class=\"caret\"><i class=\"icon fa-sort-down fa-fw\" aria-hidden=\"true\"></i></span>
\t\t\t</span>
\t\t<div class=\"dropdown\">
\t\t\t<div class=\"pointer\"><div class=\"pointer-inner\"></div></div>
\t\t\t<ul class=\"dropdown-contents\">
\t\t\t\t";
            // line 38
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute(($context["loops"] ?? null), "jumpbox_forums", array()));
            foreach ($context['_seq'] as $context["_key"] => $context["jumpbox_forums"]) {
                // line 39
                echo "\t\t\t\t";
                if (($this->getAttribute($context["jumpbox_forums"], "FORUM_ID", array()) !=  -1)) {
                    // line 40
                    echo "\t\t\t\t<li><a href=\"";
                    echo $this->getAttribute($context["jumpbox_forums"], "LINK", array());
                    echo "\" class=\"";
                    if ($this->getAttribute($context["jumpbox_forums"], "level", array())) {
                        echo "jumpbox-sub-link";
                    } elseif ($this->getAttribute($context["jumpbox_forums"], "S_IS_CAT", array())) {
                        echo "jumpbox-cat-link";
                    } else {
                        echo "jumpbox-forum-link";
                    }
                    echo "\">";
                    $context['_parent'] = $context;
                    $context['_seq'] = twig_ensure_traversable($this->getAttribute($context["jumpbox_forums"], "level", array()));
                    foreach ($context['_seq'] as $context["_key"] => $context["level"]) {
                        echo "<span class=\"spacer\"></span>";
                    }
                    $_parent = $context['_parent'];
                    unset($context['_seq'], $context['_iterated'], $context['_key'], $context['level'], $context['_parent'], $context['loop']);
                    $context = array_intersect_key($context, $_parent) + $_parent;
                    echo " <span>";
                    if ($this->getAttribute($context["jumpbox_forums"], "level", array())) {
                        if ((($context["S_CONTENT_DIRECTION"] ?? null) == "rtl")) {
                            echo "&#8626;";
                        } else {
                            echo "&#8627;";
                        }
                        echo " &nbsp;";
                    }
                    echo " ";
                    echo $this->getAttribute($context["jumpbox_forums"], "FORUM_NAME", array());
                    echo "</span></a></li>
\t\t\t\t";
                }
                // line 42
                echo "\t\t\t\t";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['jumpbox_forums'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 43
            echo "\t\t\t</ul>
\t\t</div>
\t</div>

\t";
        } else {
            // line 48
            echo "\t<br /><br />
\t";
        }
        // line 50
        echo "</div>
";
    }

    public function getTemplateName()
    {
        return "jumpbox.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  199 => 50,  195 => 48,  188 => 43,  182 => 42,  148 => 40,  145 => 39,  141 => 38,  126 => 32,  116 => 31,  107 => 30,  105 => 29,  102 => 28,  93 => 24,  85 => 23,  82 => 22,  80 => 21,  72 => 18,  66 => 17,  63 => 16,  61 => 15,  53 => 12,  47 => 11,  44 => 10,  42 => 9,  34 => 6,  28 => 5,  25 => 4,  23 => 3,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "jumpbox.html", "");
    }
}
