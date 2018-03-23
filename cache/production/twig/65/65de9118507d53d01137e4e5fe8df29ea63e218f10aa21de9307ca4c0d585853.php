<?php

/* attachment.html */
class __TwigTemplate_e77ea3d42c9048d0f470498a787d757d6c1488f9958d69d2a192d4b4ecd14042 extends Twig_Template
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
        // line 2
        echo "
";
        // line 3
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute(($context["loops"] ?? null), "_file", array()));
        foreach ($context['_seq'] as $context["_key"] => $context["_file"]) {
            // line 4
            echo "\t";
            if ($this->getAttribute($context["_file"], "S_DENIED", array())) {
                // line 5
                echo "\t<p>[";
                echo $this->getAttribute($context["_file"], "DENIED_MESSAGE", array());
                echo "]</p>
\t";
            } else {
                // line 7
                echo "\t\t";
                // line 8
                echo "
\t\t";
                // line 9
                if ($this->getAttribute($context["_file"], "S_THUMBNAIL", array())) {
                    // line 10
                    echo "\t\t<dl class=\"thumbnail\">
\t\t\t<dt><a href=\"";
                    // line 11
                    echo $this->getAttribute($context["_file"], "U_DOWNLOAD_LINK", array());
                    echo "\"><img src=\"";
                    echo $this->getAttribute($context["_file"], "THUMB_IMAGE", array());
                    echo "\" class=\"postimage\" alt=\"";
                    echo $this->getAttribute($context["_file"], "DOWNLOAD_NAME", array());
                    echo "\" title=\"";
                    echo $this->getAttribute($context["_file"], "DOWNLOAD_NAME", array());
                    echo " (";
                    echo $this->getAttribute($context["_file"], "FILESIZE", array());
                    echo " ";
                    echo $this->getAttribute($context["_file"], "SIZE_LANG", array());
                    echo ") ";
                    echo $this->getAttribute($context["_file"], "L_DOWNLOAD_COUNT", array());
                    echo "\" /></a></dt>
\t\t\t";
                    // line 12
                    if ($this->getAttribute($context["_file"], "COMMENT", array())) {
                        echo "<dd> ";
                        echo $this->getAttribute($context["_file"], "COMMENT", array());
                        echo "</dd>";
                    }
                    // line 13
                    echo "\t\t</dl>
\t\t";
                }
                // line 15
                echo "
\t\t";
                // line 16
                if ($this->getAttribute($context["_file"], "S_IMAGE", array())) {
                    // line 17
                    echo "\t\t<dl class=\"file\">
\t\t\t<dt class=\"attach-image\"><img src=\"";
                    // line 18
                    echo $this->getAttribute($context["_file"], "U_INLINE_LINK", array());
                    echo "\" class=\"postimage\" alt=\"";
                    echo $this->getAttribute($context["_file"], "DOWNLOAD_NAME", array());
                    echo "\" onclick=\"viewableArea(this);\" /></dt>
\t\t\t";
                    // line 19
                    if ($this->getAttribute($context["_file"], "COMMENT", array())) {
                        echo "<dd><em>";
                        echo $this->getAttribute($context["_file"], "COMMENT", array());
                        echo "</em></dd>";
                    }
                    // line 20
                    echo "\t\t\t<dd>";
                    echo $this->getAttribute($context["_file"], "DOWNLOAD_NAME", array());
                    echo " (";
                    echo $this->getAttribute($context["_file"], "FILESIZE", array());
                    echo " ";
                    echo $this->getAttribute($context["_file"], "SIZE_LANG", array());
                    echo ") ";
                    echo $this->getAttribute($context["_file"], "L_DOWNLOAD_COUNT", array());
                    echo "</dd>
\t\t</dl>
\t\t";
                }
                // line 23
                echo "
\t\t";
                // line 24
                if ($this->getAttribute($context["_file"], "S_FILE", array())) {
                    // line 25
                    echo "\t\t<dl class=\"file\">
\t\t\t<dt>";
                    // line 26
                    if ($this->getAttribute($context["_file"], "UPLOAD_ICON", array())) {
                        echo $this->getAttribute($context["_file"], "UPLOAD_ICON", array());
                        echo " ";
                    }
                    echo "<a class=\"postlink\" href=\"";
                    echo $this->getAttribute($context["_file"], "U_DOWNLOAD_LINK", array());
                    echo "\">";
                    echo $this->getAttribute($context["_file"], "DOWNLOAD_NAME", array());
                    echo "</a></dt>
\t\t\t";
                    // line 27
                    if ($this->getAttribute($context["_file"], "COMMENT", array())) {
                        echo "<dd><em>";
                        echo $this->getAttribute($context["_file"], "COMMENT", array());
                        echo "</em></dd>";
                    }
                    // line 28
                    echo "\t\t\t<dd>(";
                    echo $this->getAttribute($context["_file"], "FILESIZE", array());
                    echo " ";
                    echo $this->getAttribute($context["_file"], "SIZE_LANG", array());
                    echo ") ";
                    echo $this->getAttribute($context["_file"], "L_DOWNLOAD_COUNT", array());
                    echo "</dd>
\t\t</dl>
\t\t";
                }
                // line 31
                echo "
\t\t";
                // line 32
                if ($this->getAttribute($context["_file"], "S_FLASH_FILE", array())) {
                    // line 33
                    echo "\t\t\t<object classid=\"clsid:D27CDB6E-AE6D-11CF-96B8-444553540000\" codebase=\"http://active.macromedia.com/flash2/cabs/swflash.cab#version=5,0,0,0\" width=\"";
                    echo $this->getAttribute($context["_file"], "WIDTH", array());
                    echo "\" height=\"";
                    echo $this->getAttribute($context["_file"], "HEIGHT", array());
                    echo "\">
\t\t\t\t<param name=\"movie\" value=\"";
                    // line 34
                    echo $this->getAttribute($context["_file"], "U_VIEW_LINK", array());
                    echo "\" />
\t\t\t\t<param name=\"play\" value=\"true\" />
\t\t\t\t<param name=\"loop\" value=\"true\" />
\t\t\t\t<param name=\"quality\" value=\"high\" />
\t\t\t\t<param name=\"allowScriptAccess\" value=\"never\" />
\t\t\t\t<param name=\"allowNetworking\" value=\"internal\" />
\t\t\t\t<embed src=\"";
                    // line 40
                    echo $this->getAttribute($context["_file"], "U_VIEW_LINK", array());
                    echo "\" type=\"application/x-shockwave-flash\" pluginspage=\"http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash\" width=\"";
                    echo $this->getAttribute($context["_file"], "WIDTH", array());
                    echo "\" height=\"";
                    echo $this->getAttribute($context["_file"], "HEIGHT", array());
                    echo "\" play=\"true\" loop=\"true\" quality=\"high\" allowscriptaccess=\"never\" allownetworking=\"internal\"></embed>
\t\t\t</object>
\t\t\t<p><a href=\"";
                    // line 42
                    echo $this->getAttribute($context["_file"], "U_DOWNLOAD_LINK", array());
                    echo "\">";
                    echo $this->getAttribute($context["_file"], "DOWNLOAD_NAME", array());
                    echo "</a> [ ";
                    echo $this->getAttribute($context["_file"], "FILESIZE", array());
                    echo " ";
                    echo $this->getAttribute($context["_file"], "SIZE_LANG", array());
                    echo " | ";
                    echo $this->getAttribute($context["_file"], "L_DOWNLOAD_COUNT", array());
                    echo " ]</p>
\t\t";
                }
                // line 44
                echo "
\t\t";
                // line 45
                // line 46
                echo "\t";
            }
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['_file'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 48
    }

    public function getTemplateName()
    {
        return "attachment.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  195 => 48,  188 => 46,  187 => 45,  184 => 44,  171 => 42,  162 => 40,  153 => 34,  146 => 33,  144 => 32,  141 => 31,  130 => 28,  124 => 27,  113 => 26,  110 => 25,  108 => 24,  105 => 23,  92 => 20,  86 => 19,  80 => 18,  77 => 17,  75 => 16,  72 => 15,  68 => 13,  62 => 12,  46 => 11,  43 => 10,  41 => 9,  38 => 8,  36 => 7,  30 => 5,  27 => 4,  23 => 3,  20 => 2,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "attachment.html", "");
    }
}
