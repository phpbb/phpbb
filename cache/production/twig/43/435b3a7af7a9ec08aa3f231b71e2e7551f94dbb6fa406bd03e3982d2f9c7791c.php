<?php

/* permission_mask.html */
class __TwigTemplate_bbb3ee09fc1e56310772cdb9e016ce6105c4f6ac679e2667dcd9d2e7b501da0f extends Twig_Template
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
\tvar active_pmask = '0';
\tvar active_fmask = '0';
\tvar active_cat = '0';

\tvar id = '000';

\tvar role_options = new Array();

\t";
        // line 12
        if (($context["S_ROLE_JS_ARRAY"] ?? null)) {
            // line 13
            echo "\t\t";
            echo ($context["S_ROLE_JS_ARRAY"] ?? null);
            echo "
\t";
        }
        // line 15
        echo "// ]]>
</script>
<script type=\"text/javascript\" src=\"style/permissions.js\"></script>

";
        // line 19
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute(($context["loops"] ?? null), "p_mask", array()));
        foreach ($context['_seq'] as $context["_key"] => $context["p_mask"]) {
            // line 20
            echo "<div class=\"clearfix\"></div>
<h3>";
            // line 21
            echo $this->getAttribute($context["p_mask"], "NAME", array());
            if ($this->getAttribute($context["p_mask"], "S_LOCAL", array())) {
                echo " <span class=\"small\"> [";
                echo $this->getAttribute($context["p_mask"], "L_ACL_TYPE", array());
                echo "]</span>";
            }
            echo "</h3>

";
            // line 23
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute($context["p_mask"], "f_mask", array()));
            foreach ($context['_seq'] as $context["_key"] => $context["f_mask"]) {
                // line 24
                echo "<div class=\"clearfix\"></div>
<fieldset class=\"permissions\" id=\"perm";
                // line 25
                echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                echo "\">
\t<legend id=\"legend";
                // line 26
                echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                echo "\">
\t\t";
                // line 27
                if ( !$this->getAttribute($context["p_mask"], "S_VIEW", array())) {
                    // line 28
                    echo "\t\t\t<input type=\"checkbox\" style=\"display: none;\" class=\"permissions-checkbox\" name=\"inherit[";
                    echo $this->getAttribute($context["f_mask"], "UG_ID", array());
                    echo "][";
                    echo $this->getAttribute($context["f_mask"], "FORUM_ID", array());
                    echo "]\" id=\"checkbox";
                    echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                    echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                    echo "\" value=\"1\" onclick=\"toggle_opacity('";
                    echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                    echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                    echo "')\" /> 
\t\t";
                } else {
                    // line 30
                    echo "\t\t";
                }
                // line 31
                echo "\t\t";
                if ($this->getAttribute($context["f_mask"], "PADDING", array())) {
                    echo "<span class=\"padding\">";
                    echo $this->getAttribute($context["f_mask"], "PADDING", array());
                    echo $this->getAttribute($context["f_mask"], "PADDING", array());
                    echo "</span>";
                }
                echo $this->getAttribute($context["f_mask"], "NAME", array());
                echo "
\t</legend>
\t";
                // line 33
                if ( !$this->getAttribute($context["p_mask"], "S_VIEW", array())) {
                    // line 34
                    echo "\t\t<div class=\"permissions-switch\">
\t\t\t<div class=\"permissions-reset\">
\t\t\t\t<a href=\"#\" onclick=\"mark_options('perm";
                    // line 36
                    echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                    echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                    echo "', 'y'); reset_role('role";
                    echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                    echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                    echo "'); init_colours('";
                    echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                    echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                    echo "'); return false;\">";
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ALL_YES");
                    echo "</a> &middot; <a href=\"#\" onclick=\"mark_options('perm";
                    echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                    echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                    echo "', 'u'); reset_role('role";
                    echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                    echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                    echo "'); init_colours('";
                    echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                    echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                    echo "'); return false;\">";
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ALL_NO");
                    echo "</a> &middot; <a href=\"#\" onclick=\"mark_options('perm";
                    echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                    echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                    echo "', 'n'); reset_role('role";
                    echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                    echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                    echo "'); init_colours('";
                    echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                    echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                    echo "'); return false;\">";
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ALL_NEVER");
                    echo "</a>
\t\t\t</div>
\t\t\t<a href=\"#\" onclick=\"swap_options('";
                    // line 38
                    echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                    echo "', '";
                    echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                    echo "', '0', true); return false;\">";
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ADVANCED_PERMISSIONS");
                    echo "</a>";
                    if (( !$this->getAttribute($context["p_mask"], "S_VIEW", array()) && $this->getAttribute($context["f_mask"], "S_CUSTOM", array()))) {
                        echo " *";
                    }
                    // line 39
                    echo "\t\t</div>
\t\t<dl class=\"permissions-simple\">
\t\t\t<dt style=\"width: 20%\"><label for=\"role";
                    // line 41
                    echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                    echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                    echo "\">";
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ROLE");
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
                    echo "</label></dt>
\t\t\t";
                    // line 42
                    if ($this->getAttribute($context["f_mask"], "role_options", array())) {
                        // line 43
                        echo "\t\t\t\t<dd style=\"margin-";
                        echo ($context["S_CONTENT_FLOW_BEGIN"] ?? null);
                        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
                        echo " 20%\">
\t\t\t\t\t<div class=\"dropdown-container dropdown-right dropdown-button-control roles-options\" data-alt-text=\"";
                        // line 44
                        echo twig_escape_filter($this->env, $this->env->getExtension('phpbb\template\twig\extension')->lang("ROLE_DESCRIPTION"), "js");
                        echo "\">
\t\t\t\t\t\t<select id=\"role";
                        // line 45
                        echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                        echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                        echo "\" name=\"role[";
                        echo $this->getAttribute($context["f_mask"], "UG_ID", array());
                        echo "][";
                        echo $this->getAttribute($context["f_mask"], "FORUM_ID", array());
                        echo "]\">";
                        echo $this->getAttribute($context["f_mask"], "S_ROLE_OPTIONS", array());
                        echo "</select>
\t\t\t\t\t\t<span title=\"Roles\" class=\"button icon-button tools-icon dropdown-trigger dropdown-select\">";
                        // line 46
                        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("NO_ROLE_ASSIGNED");
                        echo "</span>
\t\t\t\t\t\t<div class=\"dropdown hidden\">
\t\t\t\t\t\t\t<ul class=\"dropdown-contents\" id=\"role";
                        // line 48
                        echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                        echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                        echo "\" >
\t\t\t\t\t\t\t\t";
                        // line 49
                        $context['_parent'] = $context;
                        $context['_seq'] = twig_ensure_traversable($this->getAttribute($context["f_mask"], "role_options", array()));
                        foreach ($context['_seq'] as $context["_key"] => $context["role"]) {
                            // line 50
                            echo "\t\t\t\t\t\t\t\t\t<li data-id=\"";
                            echo $this->getAttribute($context["role"], "ID", array());
                            echo "\" data-target-id=\"advanced";
                            echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                            echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                            echo "\" data-title=\"";
                            echo $this->getAttribute($context["role"], "TITLE", array());
                            echo "\"";
                            if (($this->getAttribute($context["role"], "SELECTED", array()) == true)) {
                                echo " data-selected=\"";
                                echo $this->getAttribute($context["role"], "SELECTED", array());
                                echo "\"";
                            }
                            echo ">";
                            echo $this->getAttribute($context["role"], "ROLE_NAME", array());
                            echo "</li>
\t\t\t\t\t\t\t\t";
                        }
                        $_parent = $context['_parent'];
                        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['role'], $context['_parent'], $context['loop']);
                        $context = array_intersect_key($context, $_parent) + $_parent;
                        // line 52
                        echo "\t\t\t\t\t\t\t</ul>
\t\t\t\t\t\t</div>
\t\t\t\t\t\t<input type=\"hidden\" data-name=\"role[";
                        // line 54
                        echo $this->getAttribute($context["f_mask"], "UG_ID", array());
                        echo "][";
                        echo $this->getAttribute($context["f_mask"], "FORUM_ID", array());
                        echo "]\"";
                        if ($this->getAttribute($context["f_mask"], "S_ROLE_ID", array())) {
                            echo "value=\"";
                            echo $this->getAttribute($context["f_mask"], "S_ROLE_ID", array());
                            echo "\"";
                        }
                        echo " />
\t\t\t\t\t</div>
\t\t\t\t</dd>
\t\t\t";
                    } else {
                        // line 58
                        echo "\t\t\t\t<dd>";
                        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("NO_ROLE_AVAILABLE");
                        echo "</dd>
\t\t\t";
                    }
                    // line 60
                    echo "\t\t</dl>
\t";
                }
                // line 62
                echo "
\t";
                // line 63
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable($this->getAttribute($context["f_mask"], "category", array()));
                foreach ($context['_seq'] as $context["_key"] => $context["category"]) {
                    // line 64
                    echo "\t\t";
                    if ($this->getAttribute($context["category"], "S_FIRST_ROW", array())) {
                        // line 65
                        echo "\t\t\t";
                        if ( !$this->getAttribute($context["p_mask"], "S_VIEW", array())) {
                            // line 66
                            echo "\t\t\t\t<div class=\"permissions-advanced\" id=\"advanced";
                            echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                            echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                            echo "\" style=\"display: none;\">
\t\t\t";
                        } else {
                            // line 68
                            echo "\t\t\t\t<div class=\"permissions-advanced\" id=\"advanced";
                            echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                            echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                            echo "\">
\t\t\t";
                        }
                        // line 70
                        echo "
\t\t\t<div class=\"permissions-category\">
\t\t\t\t<ul>
\t\t";
                    }
                    // line 74
                    echo " \t\t
\t\t";
                    // line 75
                    if ($this->getAttribute($context["category"], "S_YES", array())) {
                        // line 76
                        echo "\t\t\t<li class=\"permissions-preset-yes";
                        if ((($this->getAttribute($context["p_mask"], "S_FIRST_ROW", array()) && $this->getAttribute($context["f_mask"], "S_FIRST_ROW", array())) && $this->getAttribute($context["category"], "S_FIRST_ROW", array()))) {
                            echo " activetab";
                        }
                        echo "\" id=\"tab";
                        echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                        echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                        echo $this->getAttribute($context["category"], "S_ROW_COUNT", array());
                        echo "\">
\t\t";
                    } elseif ($this->getAttribute(                    // line 77
$context["category"], "S_NEVER", array())) {
                        // line 78
                        echo "\t\t\t<li class=\"permissions-preset-never";
                        if ((($this->getAttribute($context["p_mask"], "S_FIRST_ROW", array()) && $this->getAttribute($context["f_mask"], "S_FIRST_ROW", array())) && $this->getAttribute($context["category"], "S_FIRST_ROW", array()))) {
                            echo " activetab";
                        }
                        echo "\" id=\"tab";
                        echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                        echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                        echo $this->getAttribute($context["category"], "S_ROW_COUNT", array());
                        echo "\">
\t\t";
                    } elseif ($this->getAttribute(                    // line 79
$context["category"], "S_NO", array())) {
                        // line 80
                        echo "\t\t\t<li class=\"permissions-preset-no";
                        if ((($this->getAttribute($context["p_mask"], "S_FIRST_ROW", array()) && $this->getAttribute($context["f_mask"], "S_FIRST_ROW", array())) && $this->getAttribute($context["category"], "S_FIRST_ROW", array()))) {
                            echo " activetab";
                        }
                        echo "\" id=\"tab";
                        echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                        echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                        echo $this->getAttribute($context["category"], "S_ROW_COUNT", array());
                        echo "\">
\t\t";
                    } else {
                        // line 82
                        echo "\t\t\t<li class=\"permissions-preset-custom";
                        if ((($this->getAttribute($context["p_mask"], "S_FIRST_ROW", array()) && $this->getAttribute($context["f_mask"], "S_FIRST_ROW", array())) && $this->getAttribute($context["category"], "S_FIRST_ROW", array()))) {
                            echo " activetab";
                        }
                        echo "\" id=\"tab";
                        echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                        echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                        echo $this->getAttribute($context["category"], "S_ROW_COUNT", array());
                        echo "\">
\t\t";
                    }
                    // line 84
                    echo "\t\t<a href=\"#\" onclick=\"swap_options('";
                    echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                    echo "', '";
                    echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                    echo "', '";
                    echo $this->getAttribute($context["category"], "S_ROW_COUNT", array());
                    echo "', false";
                    if ($this->getAttribute($context["p_mask"], "S_VIEW", array())) {
                        echo ", true";
                    }
                    echo "); return false;\"><span class=\"tabbg\"><span class=\"colour\"></span>";
                    echo $this->getAttribute($context["category"], "CAT_NAME", array());
                    echo "</span></a></li>
\t";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['category'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 86
                echo "\t\t\t\t</ul>
\t\t\t</div>

\t";
                // line 89
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable($this->getAttribute($context["f_mask"], "category", array()));
                foreach ($context['_seq'] as $context["_key"] => $context["category"]) {
                    // line 90
                    echo "\t\t<div class=\"permissions-panel\" id=\"options";
                    echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                    echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                    echo $this->getAttribute($context["category"], "S_ROW_COUNT", array());
                    echo "\" ";
                    if ((($this->getAttribute($context["p_mask"], "S_FIRST_ROW", array()) && $this->getAttribute($context["f_mask"], "S_FIRST_ROW", array())) && $this->getAttribute($context["category"], "S_FIRST_ROW", array()))) {
                    } else {
                        echo " style=\"display: none;\"";
                    }
                    echo ">
\t\t\t<div class=\"tablewrap\">
\t\t\t\t<table id=\"table";
                    // line 92
                    echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                    echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                    echo $this->getAttribute($context["category"], "S_ROW_COUNT", array());
                    echo "\" class=\"table1 not-responsive\">
\t\t\t\t<colgroup>
\t\t\t\t\t<col class=\"permissions-name\" />
\t\t\t\t\t<col class=\"permissions-yes\" />
\t\t\t\t\t<col class=\"permissions-no\" />
\t\t\t\t\t";
                    // line 97
                    if ( !$this->getAttribute($context["p_mask"], "S_VIEW", array())) {
                        // line 98
                        echo "\t\t\t\t\t\t<col class=\"permissions-never\" />
\t\t\t\t\t";
                    }
                    // line 100
                    echo "\t\t\t\t</colgroup>
\t\t\t\t<thead>
\t\t\t\t<tr>
\t\t\t\t\t<th class=\"name\" scope=\"col\"><strong>";
                    // line 103
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ACL_SETTING");
                    echo "</strong></th>
\t\t\t\t";
                    // line 104
                    if ($this->getAttribute($context["p_mask"], "S_VIEW", array())) {
                        // line 105
                        echo "\t\t\t\t\t<th class=\"value\" scope=\"col\">";
                        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ACL_YES");
                        echo "</th>
\t\t\t\t\t<th class=\"value\" scope=\"col\">";
                        // line 106
                        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ACL_NEVER");
                        echo "</th>
\t\t\t\t";
                    } else {
                        // line 108
                        echo "\t\t\t\t\t<th class=\"value permissions-yes\" scope=\"col\"><a href=\"#\" onclick=\"mark_options('options";
                        echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                        echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                        echo $this->getAttribute($context["category"], "S_ROW_COUNT", array());
                        echo "', 'y'); reset_role('role";
                        echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                        echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                        echo "'); set_colours('";
                        echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                        echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                        echo $this->getAttribute($context["category"], "S_ROW_COUNT", array());
                        echo "', false, 'yes'); return false;\">";
                        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ACL_YES");
                        echo "</a></th>
\t\t\t\t\t<th class=\"value permissions-no\" scope=\"col\"><a href=\"#\" onclick=\"mark_options('options";
                        // line 109
                        echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                        echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                        echo $this->getAttribute($context["category"], "S_ROW_COUNT", array());
                        echo "', 'u'); reset_role('role";
                        echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                        echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                        echo "'); set_colours('";
                        echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                        echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                        echo $this->getAttribute($context["category"], "S_ROW_COUNT", array());
                        echo "', false, 'no'); return false;\">";
                        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ACL_NO");
                        echo "</a></th>
\t\t\t\t\t<th class=\"value permissions-never\" scope=\"col\"><a href=\"#\" onclick=\"mark_options('options";
                        // line 110
                        echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                        echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                        echo $this->getAttribute($context["category"], "S_ROW_COUNT", array());
                        echo "', 'n'); reset_role('role";
                        echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                        echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                        echo "'); set_colours('";
                        echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                        echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                        echo $this->getAttribute($context["category"], "S_ROW_COUNT", array());
                        echo "', false, 'never'); return false;\">";
                        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ACL_NEVER");
                        echo "</a></th>
\t\t\t\t";
                    }
                    // line 112
                    echo "\t\t\t\t</tr>
\t\t\t\t</thead>
\t\t\t\t<tbody>
\t\t\t\t";
                    // line 115
                    $context['_parent'] = $context;
                    $context['_seq'] = twig_ensure_traversable($this->getAttribute($context["category"], "mask", array()));
                    foreach ($context['_seq'] as $context["_key"] => $context["mask"]) {
                        // line 116
                        echo "\t\t\t\t\t";
                        if (($this->getAttribute($context["mask"], "S_ROW_COUNT", array()) % 2 == 0)) {
                            echo "<tr class=\"row4\">";
                        } else {
                            echo "<tr class=\"row3\">";
                        }
                        // line 117
                        echo "\t\t\t\t\t<th class=\"permissions-name";
                        if (($this->getAttribute($context["mask"], "S_ROW_COUNT", array()) % 2 == 0)) {
                            echo " row4";
                        } else {
                            echo " row3";
                        }
                        echo "\">";
                        if ($this->getAttribute($context["mask"], "U_TRACE", array())) {
                            echo "<a href=\"";
                            echo $this->getAttribute($context["mask"], "U_TRACE", array());
                            echo "\" class=\"trace\" onclick=\"popup(this.href, 750, 515, '_trace'); return false;\" title=\"";
                            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("TRACE_SETTING");
                            echo "\"><img src=\"images/icon_trace.gif\" alt=\"";
                            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("TRACE_SETTING");
                            echo "\" /></a> ";
                        }
                        echo $this->getAttribute($context["mask"], "PERMISSION", array());
                        echo "</th>
\t\t\t\t\t";
                        // line 118
                        if ($this->getAttribute($context["p_mask"], "S_VIEW", array())) {
                            // line 119
                            echo "\t\t\t\t\t\t<td";
                            if ($this->getAttribute($context["mask"], "S_YES", array())) {
                                echo " class=\"yes\"";
                            }
                            echo ">&nbsp;</td>
\t\t\t\t\t\t<td";
                            // line 120
                            if ($this->getAttribute($context["mask"], "S_NEVER", array())) {
                                echo " class=\"never\"";
                            }
                            echo "></td>
\t\t\t\t\t";
                        } else {
                            // line 122
                            echo "\t\t\t\t\t\t<td class=\"permissions-yes\"><label for=\"";
                            echo $this->getAttribute($context["mask"], "S_FIELD_NAME", array());
                            echo "_y\"><input onclick=\"reset_role('role";
                            echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                            echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                            echo "'); set_colours('";
                            echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                            echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                            echo $this->getAttribute($context["category"], "S_ROW_COUNT", array());
                            echo "', false)\" id=\"";
                            echo $this->getAttribute($context["mask"], "S_FIELD_NAME", array());
                            echo "_y\" name=\"";
                            echo $this->getAttribute($context["mask"], "S_FIELD_NAME", array());
                            echo "\" class=\"radio\" type=\"radio\"";
                            if ($this->getAttribute($context["mask"], "S_YES", array())) {
                                echo " checked=\"checked\"";
                            }
                            echo " value=\"1\" /></label></td>
\t\t\t\t\t\t<td class=\"permissions-no\"><label for=\"";
                            // line 123
                            echo $this->getAttribute($context["mask"], "S_FIELD_NAME", array());
                            echo "_u\"><input onclick=\"reset_role('role";
                            echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                            echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                            echo "'); set_colours('";
                            echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                            echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                            echo $this->getAttribute($context["category"], "S_ROW_COUNT", array());
                            echo "', false)\" id=\"";
                            echo $this->getAttribute($context["mask"], "S_FIELD_NAME", array());
                            echo "_u\" name=\"";
                            echo $this->getAttribute($context["mask"], "S_FIELD_NAME", array());
                            echo "\" class=\"radio\" type=\"radio\"";
                            if ($this->getAttribute($context["mask"], "S_NO", array())) {
                                echo " checked=\"checked\"";
                            }
                            echo " value=\"-1\" /></label></td>
\t\t\t\t\t\t<td class=\"permissions-never\"><label for=\"";
                            // line 124
                            echo $this->getAttribute($context["mask"], "S_FIELD_NAME", array());
                            echo "_n\"><input onclick=\"reset_role('role";
                            echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                            echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                            echo "'); set_colours('";
                            echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                            echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                            echo $this->getAttribute($context["category"], "S_ROW_COUNT", array());
                            echo "', false)\" id=\"";
                            echo $this->getAttribute($context["mask"], "S_FIELD_NAME", array());
                            echo "_n\" name=\"";
                            echo $this->getAttribute($context["mask"], "S_FIELD_NAME", array());
                            echo "\" class=\"radio\" type=\"radio\"";
                            if ($this->getAttribute($context["mask"], "S_NEVER", array())) {
                                echo " checked=\"checked\"";
                            }
                            echo " value=\"0\" /></label></td>
\t\t\t\t\t";
                        }
                        // line 126
                        echo "\t\t\t\t</tr>
\t\t\t\t";
                    }
                    $_parent = $context['_parent'];
                    unset($context['_seq'], $context['_iterated'], $context['_key'], $context['mask'], $context['_parent'], $context['loop']);
                    $context = array_intersect_key($context, $_parent) + $_parent;
                    // line 128
                    echo "\t\t\t\t</tbody>
\t\t\t\t</table>
\t\t\t</div>
\t\t\t
\t\t\t";
                    // line 132
                    if ( !$this->getAttribute($context["p_mask"], "S_VIEW", array())) {
                        // line 133
                        echo "\t\t\t<fieldset class=\"quick\" style=\"margin-";
                        echo ($context["S_CONTENT_FLOW_END"] ?? null);
                        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
                        echo " 11px;\">
\t\t\t\t<p class=\"small\">";
                        // line 134
                        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("APPLY_PERMISSIONS_EXPLAIN");
                        echo "</p>
\t\t\t\t<input class=\"button1\" type=\"submit\" name=\"psubmit[";
                        // line 135
                        echo $this->getAttribute($context["f_mask"], "UG_ID", array());
                        echo "][";
                        echo $this->getAttribute($context["f_mask"], "FORUM_ID", array());
                        echo "]\" value=\"";
                        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("APPLY_PERMISSIONS");
                        echo "\" />
\t\t\t\t";
                        // line 136
                        if (((twig_length_filter($this->env, $this->getAttribute($context["p_mask"], "f_mask", array())) > 1) || (twig_length_filter($this->env, $this->getAttribute(($context["loops"] ?? null), "p_mask", array())) > 1))) {
                            // line 137
                            echo "\t\t\t\t\t<p class=\"small\"><a href=\"#\" onclick=\"reset_opacity(0, '";
                            echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                            echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                            echo "'); return false;\">";
                            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("MARK_ALL");
                            echo "</a> &bull; <a href=\"#\" onclick=\"reset_opacity(1, '";
                            echo $this->getAttribute($context["p_mask"], "S_ROW_COUNT", array());
                            echo $this->getAttribute($context["f_mask"], "S_ROW_COUNT", array());
                            echo "'); return false;\">";
                            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("UNMARK_ALL");
                            echo "</a></p>
\t\t\t\t";
                        }
                        // line 139
                        echo "\t\t\t</fieldset>
\t\t
\t\t\t";
                    }
                    // line 142
                    echo "
\t\t</div>
\t";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['category'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 145
                echo "\t\t\t<div class=\"clearfix\"></div>
\t</div>
</fieldset>
";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['f_mask'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 149
            echo "
";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['p_mask'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
    }

    public function getTemplateName()
    {
        return "permission_mask.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  658 => 149,  649 => 145,  641 => 142,  636 => 139,  622 => 137,  620 => 136,  612 => 135,  608 => 134,  602 => 133,  600 => 132,  594 => 128,  587 => 126,  567 => 124,  548 => 123,  528 => 122,  521 => 120,  514 => 119,  512 => 118,  492 => 117,  485 => 116,  481 => 115,  476 => 112,  460 => 110,  445 => 109,  429 => 108,  424 => 106,  419 => 105,  417 => 104,  413 => 103,  408 => 100,  404 => 98,  402 => 97,  392 => 92,  379 => 90,  375 => 89,  370 => 86,  351 => 84,  339 => 82,  327 => 80,  325 => 79,  314 => 78,  312 => 77,  301 => 76,  299 => 75,  296 => 74,  290 => 70,  283 => 68,  276 => 66,  273 => 65,  270 => 64,  266 => 63,  263 => 62,  259 => 60,  253 => 58,  238 => 54,  234 => 52,  212 => 50,  208 => 49,  203 => 48,  198 => 46,  187 => 45,  183 => 44,  177 => 43,  175 => 42,  167 => 41,  163 => 39,  153 => 38,  117 => 36,  113 => 34,  111 => 33,  99 => 31,  96 => 30,  82 => 28,  80 => 27,  75 => 26,  70 => 25,  67 => 24,  63 => 23,  53 => 21,  50 => 20,  46 => 19,  40 => 15,  34 => 13,  32 => 12,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "permission_mask.html", "");
    }
}
