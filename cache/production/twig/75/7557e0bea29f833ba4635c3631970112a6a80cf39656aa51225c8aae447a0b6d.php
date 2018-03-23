<?php

/* posting_buttons.html */
class __TwigTemplate_1578aae54013918ffa3984f9443cfcbaa4ff5b1c1d120423ea8f1f0feadc01ad extends Twig_Template
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
<script type=\"text/javascript\">
// <![CDATA[
\tvar form_name = 'postform';
\tvar text_name = ";
        // line 5
        if ($this->getAttribute(($context["definition"] ?? null), "SIG_EDIT", array())) {
            echo "'signature'";
        } else {
            echo "'message'";
        }
        echo ";
\tvar load_draft = false;
\tvar upload = false;

\t// Define the bbCode tags
\tvar bbcode = new Array();
\tvar bbtags = new Array('[b]','[/b]','[i]','[/i]','[u]','[/u]','[quote]','[/quote]','[code]','[/code]','[list]','[/list]','[list=]','[/list]','[img]','[/img]','[url]','[/url]','[flash=]', '[/flash]','[size=]','[/size]'";
        // line 11
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute(($context["loops"] ?? null), "custom_tags", array()));
        foreach ($context['_seq'] as $context["_key"] => $context["custom_tags"]) {
            echo ", ";
            echo $this->getAttribute($context["custom_tags"], "BBCODE_NAME", array());
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['custom_tags'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        echo ");
\tvar imageTag = false;

\t// Helpline messages
\tvar help_line = {
\t\tb: '";
        // line 16
        echo twig_escape_filter($this->env, $this->env->getExtension('phpbb\template\twig\extension')->lang("BBCODE_B_HELP"), "js");
        echo "',
\t\ti: '";
        // line 17
        echo twig_escape_filter($this->env, $this->env->getExtension('phpbb\template\twig\extension')->lang("BBCODE_I_HELP"), "js");
        echo "',
\t\tu: '";
        // line 18
        echo twig_escape_filter($this->env, $this->env->getExtension('phpbb\template\twig\extension')->lang("BBCODE_U_HELP"), "js");
        echo "',
\t\tq: '";
        // line 19
        echo twig_escape_filter($this->env, $this->env->getExtension('phpbb\template\twig\extension')->lang("BBCODE_Q_HELP"), "js");
        echo "',
\t\tc: '";
        // line 20
        echo twig_escape_filter($this->env, $this->env->getExtension('phpbb\template\twig\extension')->lang("BBCODE_C_HELP"), "js");
        echo "',
\t\tl: '";
        // line 21
        echo twig_escape_filter($this->env, $this->env->getExtension('phpbb\template\twig\extension')->lang("BBCODE_L_HELP"), "js");
        echo "',
\t\to: '";
        // line 22
        echo twig_escape_filter($this->env, $this->env->getExtension('phpbb\template\twig\extension')->lang("BBCODE_O_HELP"), "js");
        echo "',
\t\tp: '";
        // line 23
        echo twig_escape_filter($this->env, $this->env->getExtension('phpbb\template\twig\extension')->lang("BBCODE_P_HELP"), "js");
        echo "',
\t\tw: '";
        // line 24
        echo twig_escape_filter($this->env, $this->env->getExtension('phpbb\template\twig\extension')->lang("BBCODE_W_HELP"), "js");
        echo "',
\t\ta: '";
        // line 25
        echo twig_escape_filter($this->env, $this->env->getExtension('phpbb\template\twig\extension')->lang("BBCODE_A_HELP"), "js");
        echo "',
\t\ts: '";
        // line 26
        echo twig_escape_filter($this->env, $this->env->getExtension('phpbb\template\twig\extension')->lang("BBCODE_S_HELP"), "js");
        echo "',
\t\tf: '";
        // line 27
        echo twig_escape_filter($this->env, $this->env->getExtension('phpbb\template\twig\extension')->lang("BBCODE_F_HELP"), "js");
        echo "',
\t\ty: '";
        // line 28
        echo twig_escape_filter($this->env, $this->env->getExtension('phpbb\template\twig\extension')->lang("BBCODE_Y_HELP"), "js");
        echo "',
\t\td: '";
        // line 29
        echo twig_escape_filter($this->env, $this->env->getExtension('phpbb\template\twig\extension')->lang("BBCODE_D_HELP"), "js");
        echo "'
\t\t";
        // line 30
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute(($context["loops"] ?? null), "custom_tags", array()));
        foreach ($context['_seq'] as $context["_key"] => $context["custom_tags"]) {
            // line 31
            echo "\t\t\t,cb_";
            echo $this->getAttribute($context["custom_tags"], "BBCODE_ID", array());
            echo ": '";
            echo $this->getAttribute($context["custom_tags"], "A_BBCODE_HELPLINE", array());
            echo "'
\t\t";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['custom_tags'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 33
        echo "\t}

\tfunction change_palette()
\t{
\t\tphpbb.toggleDisplay('colour_palette');
\t\te = document.getElementById('colour_palette');

\t\tif (e.style.display == 'block')
\t\t{
\t\t\tdocument.getElementById('bbpalette').value = '";
        // line 42
        echo twig_escape_filter($this->env, $this->env->getExtension('phpbb\template\twig\extension')->lang("FONT_COLOR_HIDE"), "js");
        echo "';
\t\t}
\t\telse
\t\t{
\t\t\tdocument.getElementById('bbpalette').value = '";
        // line 46
        echo twig_escape_filter($this->env, $this->env->getExtension('phpbb\template\twig\extension')->lang("FONT_COLOR"), "js");
        echo "';
\t\t}
\t}

// ]]>
</script>
";
        // line 52
        $asset_file = (("" . ($context["T_ASSETS_PATH"] ?? null)) . "/javascript/editor.js");
        $asset = new \phpbb\template\asset($asset_file, $this->getEnvironment()->get_path_helper(), $this->getEnvironment()->get_filesystem());
        if (substr($asset_file, 0, 2) !== './' && $asset->is_relative()) {
            $asset_path = $asset->get_path();            $local_file = $this->getEnvironment()->get_phpbb_root_path() . $asset_path;
            if (!file_exists($local_file)) {
                $local_file = $this->getEnvironment()->findTemplate($asset_path);
                $asset->set_path($local_file, true);
            }
            $asset->add_assets_version('2');
        }
        $this->getEnvironment()->get_assets_bag()->add_script($asset);        // line 53
        echo "
";
        // line 54
        if (($context["S_BBCODE_ALLOWED"] ?? null)) {
            // line 55
            echo "<div id=\"colour_palette\" style=\"display: none;\">
\t<dl style=\"clear: left;\">
\t\t<dt><label>";
			// hiding place inline button for attachements when bbcode is disabled
			echo "<input type=\"button\" value=\"Place inline\" class=\"button2 hidden file-inline-bbcode\" style=\"display: none;\">";
            // line 57
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("FONT_COLOR");
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
			
            echo "</label></dt>
\t\t<dd id=\"color_palette_placeholder\" class=\"color_palette_placeholder\" data-orientation=\"h\" data-height=\"12\" data-width=\"15\" data-bbcode=\"true\"></dd>
\t</dl>
</div>

";
            // line 62
            // line 63
            echo "<div id=\"format-buttons\" class=\"format-buttons\">
\t<button type=\"button\" class=\"button button-icon-only bbcode-b\" accesskey=\"b\" name=\"addbbcode0\" value=\" B \" onclick=\"bbstyle(0)\" title=\"";
            // line 64
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("BBCODE_B_HELP");
            echo "\">
\t\t<i class=\"icon fa-bold fa-fw\" aria-hidden=\"true\"></i>
\t</button>
\t<button type=\"button\" class=\"button button-icon-only bbcode-i\" accesskey=\"i\" name=\"addbbcode2\" value=\" i \" onclick=\"bbstyle(2)\" title=\"";
            // line 67
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("BBCODE_I_HELP");
            echo "\">
\t\t<i class=\"icon fa-italic fa-fw\" aria-hidden=\"true\"></i>
\t</button>
\t<button type=\"button\" class=\"button button-icon-only bbcode-u\" accesskey=\"u\" name=\"addbbcode4\" value=\" u \" onclick=\"bbstyle(4)\" title=\"";
            // line 70
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("BBCODE_U_HELP");
            echo "\">
\t\t<i class=\"icon fa-underline fa-fw\" aria-hidden=\"true\"></i>
\t</button>
\t";
            // line 73
            if (($context["S_BBCODE_QUOTE"] ?? null)) {
                // line 74
                echo "\t<button type=\"button\" class=\"button button-icon-only bbcode-quote\" accesskey=\"q\" name=\"addbbcode6\" value=\"Quote\" onclick=\"bbstyle(6)\" title=\"";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("BBCODE_Q_HELP");
                echo "\">
\t\t<i class=\"icon fa-quote-left fa-fw\" aria-hidden=\"true\"></i>
\t</button>
\t";
            }
            // line 78
            echo "\t<button type=\"button\" class=\"button button-icon-only bbcode-code\" accesskey=\"c\" name=\"addbbcode8\" value=\"Code\" onclick=\"bbstyle(8)\" title=\"";
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("BBCODE_C_HELP");
            echo "\">
\t\t<i class=\"icon fa-code fa-fw\" aria-hidden=\"true\"></i>
\t</button>
\t<button type=\"button\" class=\"button button-icon-only bbcode-list\" accesskey=\"l\" name=\"addbbcode10\" value=\"List\" onclick=\"bbstyle(10)\" title=\"";
            // line 81
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("BBCODE_L_HELP");
            echo "\">
\t\t<i class=\"icon fa-list fa-fw\" aria-hidden=\"true\"></i>
\t</button>
\t<button type=\"button\" class=\"button button-icon-only bbcode-list-\" accesskey=\"o\" name=\"addbbcode12\" value=\"List=\" onclick=\"bbstyle(12)\" title=\"";
            // line 84
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("BBCODE_O_HELP");
            echo "\">
\t\t<i class=\"icon fa-list-ol fa-fw\" aria-hidden=\"true\"></i>
\t</button>
\t<button type=\"button\" class=\"button button-icon-only bbcode-asterisk\" accesskey=\"y\" name=\"addlistitem\" value=\"[*]\" onclick=\"bbstyle(-1)\" title=\"";
            // line 87
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("BBCODE_LISTITEM_HELP");
            echo "\">
\t\t<i class=\"icon fa-asterisk fa-fw\" aria-hidden=\"true\"></i>
\t</button>
\t";
            // line 90
            if (($context["S_BBCODE_IMG"] ?? null)) {
                // line 91
                echo "\t<button type=\"button\" class=\"button button-icon-only bbcode-img\" accesskey=\"p\" name=\"addbbcode14\" value=\"Img\" onclick=\"bbstyle(14)\" title=\"";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("BBCODE_P_HELP");
                echo "\">
\t\t<i class=\"icon fa-image fa-fw\" aria-hidden=\"true\"></i>
\t</button>
\t";
            }
            // line 95
            echo "\t";
            if (($context["S_LINKS_ALLOWED"] ?? null)) {
                // line 96
                echo "\t<button type=\"button\" class=\"button button-icon-only bbcode-url\" accesskey=\"w\" name=\"addbbcode16\" value=\"URL\" onclick=\"bbstyle(16)\" title=\"";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("BBCODE_W_HELP");
                echo "\">
\t\t<i class=\"icon fa-link fa-fw\" aria-hidden=\"true\"></i>
\t</button>
\t";
            }
            // line 100
            echo "\t";
            if (($context["S_BBCODE_FLASH"] ?? null)) {
                // line 101
                echo "\t<button type=\"button\" class=\"button button-icon-only bbcode-flash\" accesskey=\"d\" name=\"addbbcode18\" value=\"Flash\" onclick=\"bbstyle(18)\" title=\"";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("BBCODE_D_HELP");
                echo "\">
\t\t<i class=\"icon fa-flash fa-fw\" aria-hidden=\"true\"></i>
\t</button>
\t";
            }
            // line 105
            echo "\t<button type=\"button\" class=\"button button-icon-only bbcode-color\" name=\"bbpalette\" id=\"bbpalette\" value=\"";
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("FONT_COLOR");
            echo "\" onclick=\"change_palette();\" title=\"";
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("BBCODE_S_HELP");
            echo "\">
\t\t<i class=\"icon fa-tint fa-fw\" aria-hidden=\"true\"></i>
\t</button>
\t<select name=\"addbbcode20\" class=\"bbcode-size\" onchange=\"bbfontstyle('[size=' + this.form.addbbcode20.options[this.form.addbbcode20.selectedIndex].value + ']', '[/size]');this.form.addbbcode20.selectedIndex = 2;\" title=\"";
            // line 108
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("BBCODE_F_HELP");
            echo "\">
\t\t<option value=\"50\">";
            // line 109
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("FONT_TINY");
            echo "</option>
\t\t<option value=\"85\">";
            // line 110
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("FONT_SMALL");
            echo "</option>
\t\t<option value=\"100\" selected=\"selected\">";
            // line 111
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("FONT_NORMAL");
            echo "</option>
\t\t";
            // line 112
            if (( !($context["MAX_FONT_SIZE"] ?? null) || (($context["MAX_FONT_SIZE"] ?? null) >= 150))) {
                // line 113
                echo "\t\t\t<option value=\"150\">";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("FONT_LARGE");
                echo "</option>
\t\t\t";
                // line 114
                if (( !($context["MAX_FONT_SIZE"] ?? null) || (($context["MAX_FONT_SIZE"] ?? null) >= 200))) {
                    // line 115
                    echo "\t\t\t\t<option value=\"200\">";
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("FONT_HUGE");
                    echo "</option>
\t\t\t";
                }
                // line 117
                echo "\t\t";
            }
            // line 118
            echo "\t</select>

\t";
            // line 120
            // line 121
            echo "
\t";
            // line 122
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute(($context["loops"] ?? null), "custom_tags", array()));
            foreach ($context['_seq'] as $context["_key"] => $context["custom_tags"]) {
                // line 123
                echo "\t<button type=\"button\" class=\"button button-secondary bbcode-";
                echo $this->getAttribute($context["custom_tags"], "BBCODE_TAG_CLEAN", array());
                echo "\" name=\"addbbcode";
                echo $this->getAttribute($context["custom_tags"], "BBCODE_ID", array());
                echo "\" value=\"";
                echo $this->getAttribute($context["custom_tags"], "BBCODE_TAG", array());
                echo "\" onclick=\"bbstyle(";
                echo $this->getAttribute($context["custom_tags"], "BBCODE_ID", array());
                echo ")\" title=\"";
                echo $this->getAttribute($context["custom_tags"], "BBCODE_HELPLINE", array());
                echo "\">
\t\t";
                // line 124
                echo $this->getAttribute($context["custom_tags"], "BBCODE_TAG", array());
                echo "
\t</button>
\t";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['custom_tags'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 127
            echo "</div>
";
            // line 128
        }
    }

    public function getTemplateName()
    {
        return "posting_buttons.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  350 => 128,  347 => 127,  338 => 124,  325 => 123,  321 => 122,  318 => 121,  317 => 120,  313 => 118,  310 => 117,  304 => 115,  302 => 114,  297 => 113,  295 => 112,  291 => 111,  287 => 110,  283 => 109,  279 => 108,  270 => 105,  262 => 101,  259 => 100,  251 => 96,  248 => 95,  240 => 91,  238 => 90,  232 => 87,  226 => 84,  220 => 81,  213 => 78,  205 => 74,  203 => 73,  197 => 70,  191 => 67,  185 => 64,  182 => 63,  181 => 62,  172 => 57,  168 => 55,  166 => 54,  163 => 53,  152 => 52,  143 => 46,  136 => 42,  125 => 33,  114 => 31,  110 => 30,  106 => 29,  102 => 28,  98 => 27,  94 => 26,  90 => 25,  86 => 24,  82 => 23,  78 => 22,  74 => 21,  70 => 20,  66 => 19,  62 => 18,  58 => 17,  54 => 16,  38 => 11,  25 => 5,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "posting_buttons.html", "");
    }
}
