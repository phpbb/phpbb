<?php

/* posting_attach_body.html */
class __TwigTemplate_41da6ba8e714e2b9f82fcfefe9d5527d376e96f91599c3597ef2b5a781e980aa extends Twig_Template
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
        echo "<div class=\"panel bg3 panel-container\" id=\"attach-panel\">
\t<div class=\"inner\">

\t<p>";
        // line 4
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ADD_ATTACHMENT_EXPLAIN");
        echo " <span class=\"hidden\" id=\"drag-n-drop-message\">";
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("PLUPLOAD_DRAG_TEXTAREA");
        echo "</span></p>

\t<fieldset class=\"fields2\" id=\"attach-panel-basic\">
\t<dl>
\t\t<dt><label for=\"fileupload\">";
        // line 8
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("FILENAME");
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
        echo "</label></dt>
\t\t<dd>
\t\t\t<input type=\"file\" name=\"fileupload\" id=\"fileupload\" class=\"inputbox autowidth\" />
\t\t\t<input type=\"submit\" name=\"add_file\" value=\"";
        // line 11
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ADD_FILE");
        echo "\" class=\"button2\" onclick=\"upload = true;\" />
\t\t</dd>
\t</dl>
\t<dl>
\t\t<dt><label for=\"filecomment\">";
        // line 15
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("FILE_COMMENT");
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
        echo "</label></dt>
\t\t<dd><textarea name=\"filecomment\" id=\"filecomment\" rows=\"1\" cols=\"40\" class=\"inputbox autowidth\">";
        // line 16
        echo ($context["FILE_COMMENT"] ?? null);
        echo "</textarea></dd>
\t</dl>
\t</fieldset>

\t<div id=\"attach-panel-multi\" class=\"attach-panel-multi\">
\t\t<input type=\"button\" class=\"button2\" value=\"";
        // line 21
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("PLUPLOAD_ADD_FILES");
        echo "\" id=\"add_files\" />
\t</div>

\t<div class=\"panel";
        // line 24
        if ( !twig_length_filter($this->env, $this->getAttribute(($context["loops"] ?? null), "attach_row", array()))) {
            echo " hidden";
        }
        echo " file-list-container\" id=\"file-list-container\">
\t\t<div class=\"inner\">
\t\t\t<table class=\"table1 zebra-list fixed-width-table\">
\t\t\t\t<thead>
\t\t\t\t\t<tr>
\t\t\t\t\t\t<th class=\"attach-name\">";
        // line 29
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("PLUPLOAD_FILENAME");
        echo "</th>
\t\t\t\t\t\t<th class=\"attach-comment\">";
        // line 30
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("FILE_COMMENT");
        echo "</th>
\t\t\t\t\t\t<th class=\"attach-filesize\">";
        // line 31
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("PLUPLOAD_SIZE");
        echo "</th>
\t\t\t\t\t\t<th class=\"attach-status\">";
        // line 32
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("PLUPLOAD_STATUS");
        echo "</th>
\t\t\t\t\t</tr>
\t\t\t\t</thead>
\t\t\t\t<tbody class=\"responsive-skip-empty file-list\" id=\"file-list\">
\t\t\t\t\t<tr class=\"attach-row\" id=\"attach-row-tpl\">
\t\t\t\t\t\t\t<td class=\"attach-name\">
\t\t\t\t\t\t\t\t<span class=\"file-name ellipsis-text\"></span>
\t\t\t\t\t\t\t\t<span class=\"attach-controls\">
\t\t\t\t\t\t\t\t\t<input type=\"button\" value=\"";
        // line 40
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("PLACE_INLINE");
        echo "\" class=\"button2 hidden file-inline-bbcode\" />&nbsp;
\t\t\t\t\t\t\t\t\t<input type=\"button\" value=\"";
        // line 41
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("DELETE_FILE");
        echo "\" class=\"button2 file-delete\" />
\t\t\t\t\t\t\t\t</span>
\t\t\t\t\t\t\t\t<span class=\"clear\"></span>
\t\t\t\t\t\t\t</td>
\t\t\t\t\t\t\t<td class=\"attach-comment\">
\t\t\t\t\t\t\t\t<textarea rows=\"1\" cols=\"30\" class=\"inputbox\"></textarea>
\t\t\t\t\t\t\t</td>
\t\t\t\t\t\t\t<td class=\"attach-filesize\">
\t\t\t\t\t\t\t\t<span class=\"file-size\"></span>
\t\t\t\t\t\t\t</td>
\t\t\t\t\t\t\t<td class=\"attach-status\">
\t\t\t\t\t\t\t\t<span class=\"file-progress\">
\t\t\t\t\t\t\t\t\t<span class=\"file-progress-bar\"></span>
\t\t\t\t\t\t\t\t</span>
\t\t\t\t\t\t\t\t<span class=\"file-status\"></span>
\t\t\t\t\t\t\t</td>
\t\t\t\t\t</tr>
\t\t\t\t\t";
        // line 58
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute(($context["loops"] ?? null), "attach_row", array()));
        foreach ($context['_seq'] as $context["_key"] => $context["attach_row"]) {
            // line 59
            echo "\t\t\t\t\t\t<tr class=\"attach-row\" data-attach-id=\"";
            echo $this->getAttribute($context["attach_row"], "ATTACH_ID", array());
            echo "\">
\t\t\t\t\t\t\t<td class=\"attach-name\">
\t\t\t\t\t\t\t\t<span class=\"file-name ellipsis-text\"><a href=\"";
            // line 61
            echo $this->getAttribute($context["attach_row"], "U_VIEW_ATTACHMENT", array());
            echo "\">";
            echo $this->getAttribute($context["attach_row"], "FILENAME", array());
            echo "</a></span>
\t\t\t\t\t\t\t\t";
            // line 62
            // line 63
            echo "\t\t\t\t\t\t\t\t<span class=\"attach-controls\">
\t\t\t\t\t\t\t\t\t";
            // line 64
            if (($context["S_INLINE_ATTACHMENT_OPTIONS"] ?? null)) {
                echo "<input type=\"button\" value=\"";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("PLACE_INLINE");
                echo "\" class=\"button2 file-inline-bbcode\" />&nbsp; ";
            }
            // line 65
            echo "\t\t\t\t\t\t\t\t\t<input type=\"submit\" name=\"delete_file[";
            echo $this->getAttribute($context["attach_row"], "ASSOC_INDEX", array());
            echo "]\" value=\"";
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("DELETE_FILE");
            echo "\" class=\"button2 file-delete\" />
\t\t\t\t\t\t\t\t</span>
\t\t\t\t\t\t\t\t";
            // line 67
            // line 68
            echo "\t\t\t\t\t\t\t\t<span class=\"clear\"></span>
\t\t\t\t\t\t\t</td>
\t\t\t\t\t\t\t<td class=\"attach-comment\">
\t\t\t\t\t\t\t\t<textarea name=\"comment_list[";
            // line 71
            echo $this->getAttribute($context["attach_row"], "ASSOC_INDEX", array());
            echo "]\" rows=\"1\" cols=\"30\" class=\"inputbox\">";
            echo $this->getAttribute($context["attach_row"], "FILE_COMMENT", array());
            echo "</textarea>
\t\t\t\t\t\t\t\t";
            // line 72
            echo $this->getAttribute($context["attach_row"], "S_HIDDEN", array());
            echo "
\t\t\t\t\t\t\t</td>
\t\t\t\t\t\t\t<td class=\"attach-filesize\">
\t\t\t\t\t\t\t\t<span class=\"file-size\">";
            // line 75
            echo $this->getAttribute($context["attach_row"], "FILESIZE", array());
            echo "</span>
\t\t\t\t\t\t\t</td>
\t\t\t\t\t\t\t<td class=\"attach-status\">
\t\t\t\t\t\t\t\t<span class=\"file-status file-uploaded\"></span>
\t\t\t\t\t\t\t</td>
\t\t\t\t\t\t</tr>
\t\t\t\t\t";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['attach_row'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 82
        echo "\t\t\t\t</tbody>
\t\t\t</table>
\t\t</div>
\t</div>
\t</div>
</div>
";
    }

    public function getTemplateName()
    {
        return "posting_attach_body.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  188 => 82,  175 => 75,  169 => 72,  163 => 71,  158 => 68,  157 => 67,  149 => 65,  143 => 64,  140 => 63,  139 => 62,  133 => 61,  127 => 59,  123 => 58,  103 => 41,  99 => 40,  88 => 32,  84 => 31,  80 => 30,  76 => 29,  66 => 24,  60 => 21,  52 => 16,  47 => 15,  40 => 11,  33 => 8,  24 => 4,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "posting_attach_body.html", "");
    }
}
