<?php

/* acp_permissions.html */
class __TwigTemplate_874b87e7519de70e5e4234c61a367d225aeda595b7077887482b5e6bdc7588e0 extends Twig_Template
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
        $this->loadTemplate("overall_header.html", "acp_permissions.html", 1)->display($context);
        if ($namespace) {
            $this->env->setNamespaceLookUpOrder($previous_look_up_order);
        }
        // line 2
        echo "
<a id=\"maincontent\"></a>

";
        // line 5
        if (($context["S_INTRO"] ?? null)) {
            // line 6
            echo "
\t<h1>";
            // line 7
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ACP_PERMISSIONS");
            echo "</h1>

\t";
            // line 9
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ACP_PERMISSIONS_EXPLAIN");
            echo "

";
        }
        // line 12
        echo "
";
        // line 13
        if (($context["S_SELECT_VICTIM"] ?? null)) {
            // line 14
            echo "
\t";
            // line 15
            if (($context["U_BACK"] ?? null)) {
                echo "<a href=\"";
                echo ($context["U_BACK"] ?? null);
                echo "\" style=\"float: ";
                echo ($context["S_CONTENT_FLOW_END"] ?? null);
                echo ";\">&laquo; ";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("BACK");
                echo "</a>";
            }
            // line 16
            echo "
\t<h1>";
            // line 17
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("TITLE");
            echo "</h1>

\t<p>";
            // line 19
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("EXPLAIN");
            echo "</p>

\t";
            // line 21
            if (($context["S_FORUM_NAMES"] ?? null)) {
                // line 22
                echo "\t\t<p><strong>";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("FORUMS");
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
                echo "</strong> ";
                echo ($context["FORUM_NAMES"] ?? null);
                echo "</p>
\t";
            }
            // line 24
            echo "
\t";
            // line 25
            if (($context["S_SELECT_FORUM"] ?? null)) {
                // line 26
                echo "
\t\t<form id=\"select_victim\" method=\"post\" action=\"";
                // line 27
                echo ($context["U_ACTION"] ?? null);
                echo "\">

\t\t<fieldset>
\t\t\t<legend>";
                // line 30
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("LOOK_UP_FORUM");
                echo "</legend>
\t\t\t";
                // line 31
                if (($context["S_FORUM_MULTIPLE"] ?? null)) {
                    echo "<p>";
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("LOOK_UP_FORUMS_EXPLAIN");
                    echo "</p>";
                }
                // line 32
                echo "\t\t<dl>
\t\t\t<dt>";
                // line 33
                echo "<label for=\"forum\">";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("LOOK_UP_FORUM");
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
                echo "</label>";
                echo "</dt>
\t\t\t<dd><select id=\"forum\" name=\"forum_id[]\"";
                // line 34
                if (($context["S_FORUM_MULTIPLE"] ?? null)) {
                    echo " multiple=\"multiple\"";
                }
                echo " size=\"10\">";
                echo ($context["S_FORUM_OPTIONS"] ?? null);
                echo "</select></dd>
\t\t\t";
                // line 35
                if (($context["S_FORUM_ALL"] ?? null)) {
                    echo "<dd><label><input type=\"checkbox\" class=\"radio\" name=\"all_forums\" value=\"1\" /> ";
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ALL_FORUMS");
                    echo "</label></dd>";
                }
                // line 36
                echo "\t\t</dl>

\t\t<p class=\"quick\">
\t\t\t";
                // line 39
                echo ($context["S_HIDDEN_FIELDS"] ?? null);
                echo "
\t\t\t";
                // line 40
                echo ($context["S_FORM_TOKEN"] ?? null);
                echo "
\t\t\t<input type=\"submit\" name=\"submit\" value=\"";
                // line 41
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("SUBMIT");
                echo "\" class=\"button1\" />
\t\t</p>

\t\t</fieldset>
\t\t</form>

\t\t";
                // line 47
                if (($context["S_FORUM_MULTIPLE"] ?? null)) {
                    // line 48
                    echo "
\t\t\t<form id=\"select_subforum\" method=\"post\" action=\"";
                    // line 49
                    echo ($context["U_ACTION"] ?? null);
                    echo "\">

\t\t\t<fieldset>
\t\t\t\t<legend>";
                    // line 52
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("LOOK_UP_FORUM");
                    echo "</legend>
\t\t\t\t<p>";
                    // line 53
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("SELECT_FORUM_SUBFORUM_EXPLAIN");
                    echo "</p>
\t\t\t<dl>
\t\t\t\t<dt>";
                    // line 55
                    echo "<label for=\"sforum\">";
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("LOOK_UP_FORUM");
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
                    echo "</label>";
                    echo "</dt>
\t\t\t\t<dd><select id=\"sforum\" name=\"subforum_id\">";
                    // line 56
                    echo ($context["S_SUBFORUM_OPTIONS"] ?? null);
                    echo "</select></dd>
\t\t\t</dl>

\t\t\t<p class=\"quick\">
\t\t\t\t";
                    // line 60
                    echo ($context["S_HIDDEN_FIELDS"] ?? null);
                    echo "
\t\t\t\t";
                    // line 61
                    echo ($context["S_FORM_TOKEN"] ?? null);
                    echo "
\t\t\t\t<input type=\"submit\" name=\"submit\" value=\"";
                    // line 62
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("SUBMIT");
                    echo "\" class=\"button1\" />
\t\t\t</p>

\t\t\t</fieldset>
\t\t\t</form>

\t\t";
                }
                // line 69
                echo "
\t";
            } elseif ((            // line 70
($context["S_SELECT_USER"] ?? null) && ($context["S_CAN_SELECT_USER"] ?? null))) {
                // line 71
                echo "
\t\t<form id=\"select_victim\" method=\"post\" action=\"";
                // line 72
                echo ($context["U_ACTION"] ?? null);
                echo "\">

\t\t<fieldset>
\t\t\t<legend>";
                // line 75
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("LOOK_UP_USER");
                echo "</legend>
\t\t<dl>
\t\t\t<dt><label for=\"username\">";
                // line 77
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("FIND_USERNAME");
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
                echo "</label></dt>
\t\t\t<dd><input class=\"text medium\" type=\"text\" id=\"username\" name=\"username[]\" /></dd>
\t\t\t<dd>[ <a href=\"";
                // line 79
                echo ($context["U_FIND_USERNAME"] ?? null);
                echo "\" onclick=\"find_username(this.href); return false;\">";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("FIND_USERNAME");
                echo "</a> ]</dd>
\t\t\t<dd class=\"full\" style=\"text-align: left;\"><label><input type=\"checkbox\" class=\"radio\" id=\"anonymous\" name=\"user_id[]\" value=\"";
                // line 80
                echo ($context["ANONYMOUS_USER_ID"] ?? null);
                echo "\" /> ";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("SELECT_ANONYMOUS");
                echo "</label></dd>
\t\t</dl>

\t\t<p class=\"quick\">
\t\t\t";
                // line 84
                echo ($context["S_HIDDEN_FIELDS"] ?? null);
                echo "
\t\t\t";
                // line 85
                echo ($context["S_FORM_TOKEN"] ?? null);
                echo "
\t\t\t<input type=\"submit\" name=\"submit\" value=\"";
                // line 86
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("SUBMIT");
                echo "\" class=\"button1\" />
\t\t</p>
\t\t</fieldset>
\t\t</form>

\t";
            } elseif ((            // line 91
($context["S_SELECT_GROUP"] ?? null) && ($context["S_CAN_SELECT_GROUP"] ?? null))) {
                // line 92
                echo "
\t\t<form id=\"select_victim\" method=\"post\" action=\"";
                // line 93
                echo ($context["U_ACTION"] ?? null);
                echo "\">

\t\t<fieldset>
\t\t\t<legend>";
                // line 96
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("LOOK_UP_GROUP");
                echo "</legend>
\t\t<dl>
\t\t\t<dt>";
                // line 98
                echo "<label for=\"group\">";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("LOOK_UP_GROUP");
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
                echo "</label>";
                echo "</dt>
\t\t\t<dd><select name=\"group_id[]\" id=\"group\">";
                // line 99
                echo ($context["S_GROUP_OPTIONS"] ?? null);
                echo "</select></dd>
\t\t</dl>

\t\t<p class=\"quick\">
\t\t\t";
                // line 103
                echo ($context["S_HIDDEN_FIELDS"] ?? null);
                echo "
\t\t\t";
                // line 104
                echo ($context["S_FORM_TOKEN"] ?? null);
                echo "
\t\t\t<input type=\"submit\" name=\"submit\" value=\"";
                // line 105
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("SUBMIT");
                echo "\" class=\"button1\" />
\t\t</p>

\t\t</fieldset>
\t\t</form>

\t\t";
            } elseif (            // line 111
($context["S_SELECT_USERGROUP"] ?? null)) {
                // line 112
                echo "
\t\t<div class=\"column1\">

\t\t";
                // line 115
                if (($context["S_CAN_SELECT_USER"] ?? null)) {
                    // line 116
                    echo "
\t\t\t<h1>";
                    // line 117
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("USERS");
                    echo "</h1>

\t\t\t<form id=\"users\" method=\"post\" action=\"";
                    // line 119
                    echo ($context["U_ACTION"] ?? null);
                    echo "\">

\t\t\t<fieldset>
\t\t\t\t<legend>";
                    // line 122
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("MANAGE_USERS");
                    echo "</legend>
\t\t\t<dl>
\t\t\t\t<dd class=\"full\"><select style=\"width: 100%;\" name=\"user_id[]\" multiple=\"multiple\" size=\"5\">";
                    // line 124
                    echo ($context["S_DEFINED_USER_OPTIONS"] ?? null);
                    echo "</select></dd>
\t\t\t\t";
                    // line 125
                    if (($context["S_ALLOW_ALL_SELECT"] ?? null)) {
                        echo "<dd class=\"full\" style=\"text-align: right;\"><label><input type=\"checkbox\" class=\"radio\" name=\"all_users\" value=\"1\" /> ";
                        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ALL_USERS");
                        echo "</label></dd>";
                    }
                    // line 126
                    echo "\t\t\t</dl>
\t\t\t</fieldset>

\t\t\t<fieldset class=\"quick\">
\t\t\t\t";
                    // line 130
                    echo ($context["S_HIDDEN_FIELDS"] ?? null);
                    echo "
\t\t\t\t";
                    // line 131
                    echo ($context["S_FORM_TOKEN"] ?? null);
                    echo "
\t\t\t\t<input type=\"submit\" class=\"button2\" name=\"action[delete]\" value=\"";
                    // line 132
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("REMOVE_PERMISSIONS");
                    echo "\" style=\"width: 46% !important;\" /> &nbsp; <input class=\"button1\" type=\"submit\" name=\"submit_edit_options\" value=\"";
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("EDIT_PERMISSIONS");
                    echo "\" style=\"width: 46% !important;\" />
\t\t\t</fieldset>
\t\t\t</form>

\t\t\t<form id=\"add_user\" method=\"post\" action=\"";
                    // line 136
                    echo ($context["U_ACTION"] ?? null);
                    echo "\">

\t\t\t<fieldset>
\t\t\t\t<legend>";
                    // line 139
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ADD_USERS");
                    echo "</legend>
\t\t\t\t<p>";
                    // line 140
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("USERNAMES_EXPLAIN");
                    echo "</p>
\t\t\t<dl>
\t\t\t\t<dd class=\"full\"><textarea id=\"username\" name=\"usernames\" rows=\"5\" cols=\"5\" style=\"width: 100%; height: 60px;\"></textarea></dd>
\t\t\t\t<dd class=\"full\" style=\"text-align: left;\">";
                    // line 143
                    echo "<div style=\"float: ";
                    echo ($context["S_CONTENT_FLOW_END"] ?? null);
                    echo ";\">[ <a href=\"";
                    echo ($context["U_FIND_USERNAME"] ?? null);
                    echo "\" onclick=\"find_username(this.href); return false;\">";
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("FIND_USERNAME");
                    echo "</a> ]</div>";
                    echo "<label><input type=\"checkbox\" class=\"radio\" id=\"anonymous\" name=\"user_id[]\" value=\"";
                    echo ($context["ANONYMOUS_USER_ID"] ?? null);
                    echo "\" /> ";
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("SELECT_ANONYMOUS");
                    echo "</label></dd>
\t\t\t</dl>
\t\t\t</fieldset>

\t\t\t<fieldset class=\"quick\">
\t\t\t\t";
                    // line 148
                    echo ($context["S_HIDDEN_FIELDS"] ?? null);
                    echo "
\t\t\t\t";
                    // line 149
                    echo ($context["S_FORM_TOKEN"] ?? null);
                    echo "
\t\t\t\t<input class=\"button1\" type=\"submit\" name=\"submit_add_options\" value=\"";
                    // line 150
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ADD_PERMISSIONS");
                    echo "\" />
\t\t\t</fieldset>
\t\t\t</form>

\t\t";
                }
                // line 155
                echo "
\t\t</div>

\t\t<div class=\"column2\">

\t\t";
                // line 160
                if (($context["S_CAN_SELECT_GROUP"] ?? null)) {
                    // line 161
                    echo "
\t\t\t<h1>";
                    // line 162
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("USERGROUPS");
                    echo "</h1>

\t\t\t<form id=\"groups\" method=\"post\" action=\"";
                    // line 164
                    echo ($context["U_ACTION"] ?? null);
                    echo "\">

\t\t\t<fieldset>
\t\t\t\t<legend>";
                    // line 167
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("MANAGE_GROUPS");
                    echo "</legend>
\t\t\t<dl>
\t\t\t\t<dd class=\"full\"><select style=\"width: 100%;\" name=\"group_id[]\" multiple=\"multiple\" size=\"5\">";
                    // line 169
                    echo ($context["S_DEFINED_GROUP_OPTIONS"] ?? null);
                    echo "</select></dd>
\t\t\t\t";
                    // line 170
                    if (($context["S_ALLOW_ALL_SELECT"] ?? null)) {
                        echo "<dd class=\"full\" style=\"text-align: right;\"><label><input type=\"checkbox\" class=\"radio\" name=\"all_groups\" value=\"1\" /> ";
                        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ALL_GROUPS");
                        echo "</label></dd>";
                    }
                    // line 171
                    echo "\t\t\t</dl>
\t\t\t</fieldset>

\t\t\t<fieldset class=\"quick\">
\t\t\t\t";
                    // line 175
                    echo ($context["S_HIDDEN_FIELDS"] ?? null);
                    echo "
\t\t\t\t";
                    // line 176
                    echo ($context["S_FORM_TOKEN"] ?? null);
                    echo "
\t\t\t\t<input class=\"button2\" type=\"submit\" name=\"action[delete]\" value=\"";
                    // line 177
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("REMOVE_PERMISSIONS");
                    echo "\" style=\"width: 46% !important;\" /> &nbsp; <input class=\"button1\" type=\"submit\" name=\"submit_edit_options\" value=\"";
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("EDIT_PERMISSIONS");
                    echo "\" style=\"width: 46% !important;\" />
\t\t\t</fieldset>
\t\t\t</form>

\t\t\t<form id=\"add_groups\" method=\"post\" action=\"";
                    // line 181
                    echo ($context["U_ACTION"] ?? null);
                    echo "\">

\t\t\t<fieldset>
\t\t\t\t<legend>";
                    // line 184
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ADD_GROUPS");
                    echo "</legend>
\t\t\t<dl>
\t\t\t\t<dd class=\"full\">";
                    // line 186
                    echo "<select name=\"group_id[]\" style=\"width: 100%; height: 107px;\" multiple=\"multiple\">";
                    echo ($context["S_ADD_GROUP_OPTIONS"] ?? null);
                    echo "</select>";
                    echo "</dd>
\t\t\t</dl>
\t\t\t</fieldset>

\t\t\t<fieldset class=\"quick\">
\t\t\t\t";
                    // line 191
                    echo ($context["S_HIDDEN_FIELDS"] ?? null);
                    echo "
\t\t\t\t";
                    // line 192
                    echo ($context["S_FORM_TOKEN"] ?? null);
                    echo "
\t\t\t\t<input type=\"submit\" class=\"button1\" name=\"submit_add_options\" value=\"";
                    // line 193
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ADD_PERMISSIONS");
                    echo "\" />
\t\t\t</fieldset>
\t\t\t</form>

\t\t";
                }
                // line 198
                echo "
\t\t</div>

\t";
            } elseif (            // line 201
($context["S_SELECT_USERGROUP_VIEW"] ?? null)) {
                // line 202
                echo "
\t\t<div class=\"column1\">

\t\t\t<h1>";
                // line 205
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("USERS");
                echo "</h1>

\t\t\t<form id=\"users\" method=\"post\" action=\"";
                // line 207
                echo ($context["U_ACTION"] ?? null);
                echo "\">

\t\t\t<fieldset>
\t\t\t\t<legend>";
                // line 210
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("MANAGE_USERS");
                echo "</legend>
\t\t\t<dl>
\t\t\t\t<dd class=\"full\"><select style=\"width: 100%;\" name=\"user_id[]\" multiple=\"multiple\" size=\"5\">";
                // line 212
                echo ($context["S_DEFINED_USER_OPTIONS"] ?? null);
                echo "</select></dd>
\t\t\t</dl>
\t\t\t</fieldset>

\t\t\t<fieldset class=\"quick\">
\t\t\t\t";
                // line 217
                echo ($context["S_HIDDEN_FIELDS"] ?? null);
                echo "
\t\t\t\t";
                // line 218
                echo ($context["S_FORM_TOKEN"] ?? null);
                echo "
\t\t\t\t<input class=\"button1\" type=\"submit\" name=\"submit\" value=\"";
                // line 219
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("VIEW_PERMISSIONS");
                echo "\" />
\t\t\t</fieldset>
\t\t\t</form>

\t\t\t<form id=\"add_user\" method=\"post\" action=\"";
                // line 223
                echo ($context["U_ACTION"] ?? null);
                echo "\">

\t\t\t<fieldset>
\t\t\t\t<legend>";
                // line 226
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("LOOK_UP_USER");
                echo "</legend>
\t\t\t<dl>
\t\t\t\t<dt><label for=\"username\">";
                // line 228
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("FIND_USERNAME");
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
                echo "</label></dt>
\t\t\t\t<dd><input type=\"text\" id=\"username\" name=\"username[]\" /></dd>
\t\t\t\t<dd>[ <a href=\"";
                // line 230
                echo ($context["U_FIND_USERNAME"] ?? null);
                echo "\" onclick=\"find_username(this.href); return false;\">";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("FIND_USERNAME");
                echo "</a> ]</dd>
\t\t\t\t<dd class=\"full\" style=\"text-align: left;\"><label><input type=\"checkbox\" class=\"radio\" id=\"anonymous\" name=\"user_id[]\" value=\"";
                // line 231
                echo ($context["ANONYMOUS_USER_ID"] ?? null);
                echo "\" /> ";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("SELECT_ANONYMOUS");
                echo "</label></dd>
\t\t\t</dl>
\t\t\t</fieldset>

\t\t\t<fieldset class=\"quick\">
\t\t\t\t";
                // line 236
                echo ($context["S_HIDDEN_FIELDS"] ?? null);
                echo "
\t\t\t\t";
                // line 237
                echo ($context["S_FORM_TOKEN"] ?? null);
                echo "
\t\t\t\t<input type=\"submit\" name=\"submit\" value=\"";
                // line 238
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("VIEW_PERMISSIONS");
                echo "\" class=\"button1\" />
\t\t\t</fieldset>
\t\t\t</form>

\t\t</div>

\t\t<div class=\"column2\">

\t\t\t<h1>";
                // line 246
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("USERGROUPS");
                echo "</h1>

\t\t\t<form id=\"groups\" method=\"post\" action=\"";
                // line 248
                echo ($context["U_ACTION"] ?? null);
                echo "\">

\t\t\t<fieldset>
\t\t\t\t<legend>";
                // line 251
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("MANAGE_GROUPS");
                echo "</legend>
\t\t\t<dl>
\t\t\t\t<dd class=\"full\"><select style=\"width: 100%;\" name=\"group_id[]\" multiple=\"multiple\" size=\"5\">";
                // line 253
                echo ($context["S_DEFINED_GROUP_OPTIONS"] ?? null);
                echo "</select></dd>
\t\t\t</dl>
\t\t\t</fieldset>

\t\t\t<fieldset class=\"quick\">
\t\t\t\t";
                // line 258
                echo ($context["S_HIDDEN_FIELDS"] ?? null);
                echo "
\t\t\t\t";
                // line 259
                echo ($context["S_FORM_TOKEN"] ?? null);
                echo "
\t\t\t\t<input class=\"button1\" type=\"submit\" name=\"submit\" value=\"";
                // line 260
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("VIEW_PERMISSIONS");
                echo "\" />
\t\t\t</fieldset>
\t\t\t</form>

\t\t\t<form id=\"group\" method=\"post\" action=\"";
                // line 264
                echo ($context["U_ACTION"] ?? null);
                echo "\">

\t\t\t<fieldset>
\t\t\t\t<legend>";
                // line 267
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("LOOK_UP_GROUP");
                echo "</legend>
\t\t\t<dl>
\t\t\t\t<dt><label for=\"group_select\">";
                // line 269
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("LOOK_UP_GROUP");
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
                echo "</label></dt>
\t\t\t\t";
                // line 270
                // line 271
                echo "\t\t\t\t<dd><select name=\"group_id[]\" id=\"group_select\">";
                echo ($context["S_ADD_GROUP_OPTIONS"] ?? null);
                echo "</select></dd>
\t\t\t\t";
                // line 272
                // line 273
                echo "\t\t\t\t<dd>&nbsp;</dd>
\t\t\t</dl>
\t\t\t</fieldset>

\t\t\t<fieldset class=\"quick\">
\t\t\t\t";
                // line 278
                echo ($context["S_HIDDEN_FIELDS"] ?? null);
                echo "
\t\t\t\t";
                // line 279
                echo ($context["S_FORM_TOKEN"] ?? null);
                echo "
\t\t\t\t<input type=\"submit\" name=\"submit\" value=\"";
                // line 280
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("VIEW_PERMISSIONS");
                echo "\" class=\"button1\" />
\t\t\t</fieldset>
\t\t\t</form>

\t\t</div>

\t";
            }
            // line 287
            echo "
";
        }
        // line 289
        echo "
";
        // line 290
        if (($context["S_VIEWING_PERMISSIONS"] ?? null)) {
            // line 291
            echo "
\t<h1>";
            // line 292
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ACL_VIEW");
            echo "</h1>

\t<p>";
            // line 294
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ACL_VIEW_EXPLAIN");
            echo "</p>

\t<fieldset class=\"quick\">
\t\t<strong>&raquo; ";
            // line 297
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("PERMISSION_TYPE");
            echo "</strong>
\t</fieldset>

\t";
            // line 300
            $location = "permission_mask.html";
            $namespace = false;
            if (strpos($location, '@') === 0) {
                $namespace = substr($location, 1, strpos($location, '/') - 1);
                $previous_look_up_order = $this->env->getNamespaceLookUpOrder();
                $this->env->setNamespaceLookUpOrder(array($namespace, '__main__'));
            }
            $this->loadTemplate("permission_mask.html", "acp_permissions.html", 300)->display($context);
            if ($namespace) {
                $this->env->setNamespaceLookUpOrder($previous_look_up_order);
            }
            // line 301
            echo "
";
        }
        // line 303
        echo "
";
        // line 304
        if (($context["S_SETTING_PERMISSIONS"] ?? null)) {
            // line 305
            echo "
\t<h1>";
            // line 306
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ACL_SET");
            echo "</h1>

\t<p>";
            // line 308
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ACL_SET_EXPLAIN");
            echo "</p>

\t<br />

\t<fieldset class=\"quick\" style=\"float: ";
            // line 312
            echo ($context["S_CONTENT_FLOW_END"] ?? null);
            echo ";\">
\t\t<strong>&raquo; ";
            // line 313
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("PERMISSION_TYPE");
            echo "</strong>
\t</fieldset>

\t";
            // line 316
            if (($context["S_PERMISSION_DROPDOWN"] ?? null)) {
                // line 317
                echo "\t\t<form id=\"pselect\" method=\"post\" action=\"";
                echo ($context["U_ACTION"] ?? null);
                echo "\">

\t\t<fieldset class=\"quick\" style=\"float: ";
                // line 319
                echo ($context["S_CONTENT_FLOW_BEGIN"] ?? null);
                echo ";\">
\t\t\t";
                // line 320
                echo ($context["S_HIDDEN_FIELDS"] ?? null);
                echo "
\t\t\t";
                // line 321
                echo ($context["S_FORM_TOKEN"] ?? null);
                echo "
\t\t\t";
                // line 322
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("SELECT_TYPE");
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
                echo " <select name=\"type\">";
                echo ($context["S_PERMISSION_DROPDOWN"] ?? null);
                echo "</select>

\t\t\t<input class=\"button2\" type=\"submit\" name=\"submit\" value=\"";
                // line 324
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("GO");
                echo "\" />
\t\t</fieldset>
\t\t</form>
\t";
            }
            // line 328
            echo "
\t<br class=\"responsive-hide\" /><br class=\"responsive-hide\" />

\t<!-- include tooltip file -->
\t";
            // line 332
            $asset_file = "tooltip.js";
            $asset = new \phpbb\template\asset($asset_file, $this->getEnvironment()->get_path_helper(), $this->getEnvironment()->get_filesystem());
            if (substr($asset_file, 0, 2) !== './' && $asset->is_relative()) {
                $asset_path = $asset->get_path();                $local_file = $this->getEnvironment()->get_phpbb_root_path() . $asset_path;
                if (!file_exists($local_file)) {
                    $local_file = $this->getEnvironment()->findTemplate($asset_path);
                    $asset->set_path($local_file, true);
                }
                $asset->add_assets_version('2');
            }
            $this->getEnvironment()->get_assets_bag()->add_script($asset);            // line 333
            echo "
\t<form id=\"set-permissions\" method=\"post\" action=\"";
            // line 334
            echo ($context["U_ACTION"] ?? null);
            echo "\" data-role-description=\"";
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ROLE_DESCRIPTION");
            echo "\">

\t";
            // line 336
            echo ($context["S_HIDDEN_FIELDS"] ?? null);
            echo "

\t";
            // line 338
            $location = "permission_mask.html";
            $namespace = false;
            if (strpos($location, '@') === 0) {
                $namespace = substr($location, 1, strpos($location, '/') - 1);
                $previous_look_up_order = $this->env->getNamespaceLookUpOrder();
                $this->env->setNamespaceLookUpOrder(array($namespace, '__main__'));
            }
            $this->loadTemplate("permission_mask.html", "acp_permissions.html", 338)->display($context);
            if ($namespace) {
                $this->env->setNamespaceLookUpOrder($previous_look_up_order);
            }
            // line 339
            echo "
\t<br class=\"responsive-hide\" /><br class=\"responsive-hide\" />

\t<fieldset class=\"quick\" style=\"float: ";
            // line 342
            echo ($context["S_CONTENT_FLOW_END"] ?? null);
            echo ";\">
\t\t<input class=\"button1\" type=\"submit\" name=\"action[apply_all_permissions]\" value=\"";
            // line 343
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("APPLY_ALL_PERMISSIONS");
            echo "\" />
\t\t<input class=\"button2\" type=\"button\" name=\"cancel\" value=\"";
            // line 344
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("RESET");
            echo "\" onclick=\"document.forms['set-permissions'].reset(); init_colours(active_pmask + active_fmask);\" />
\t\t";
            // line 345
            echo ($context["S_FORM_TOKEN"] ?? null);
            echo "
\t</fieldset>

\t<br class=\"responsive-hide\" /><br class=\"responsive-hide\" />

\t</form>

";
        }
        // line 353
        echo "
";
        // line 354
        $location = "overall_footer.html";
        $namespace = false;
        if (strpos($location, '@') === 0) {
            $namespace = substr($location, 1, strpos($location, '/') - 1);
            $previous_look_up_order = $this->env->getNamespaceLookUpOrder();
            $this->env->setNamespaceLookUpOrder(array($namespace, '__main__'));
        }
        $this->loadTemplate("overall_footer.html", "acp_permissions.html", 354)->display($context);
        if ($namespace) {
            $this->env->setNamespaceLookUpOrder($previous_look_up_order);
        }
    }

    public function getTemplateName()
    {
        return "acp_permissions.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  903 => 354,  900 => 353,  889 => 345,  885 => 344,  881 => 343,  877 => 342,  872 => 339,  860 => 338,  855 => 336,  848 => 334,  845 => 333,  834 => 332,  828 => 328,  821 => 324,  813 => 322,  809 => 321,  805 => 320,  801 => 319,  795 => 317,  793 => 316,  787 => 313,  783 => 312,  776 => 308,  771 => 306,  768 => 305,  766 => 304,  763 => 303,  759 => 301,  747 => 300,  741 => 297,  735 => 294,  730 => 292,  727 => 291,  725 => 290,  722 => 289,  718 => 287,  708 => 280,  704 => 279,  700 => 278,  693 => 273,  692 => 272,  687 => 271,  686 => 270,  681 => 269,  676 => 267,  670 => 264,  663 => 260,  659 => 259,  655 => 258,  647 => 253,  642 => 251,  636 => 248,  631 => 246,  620 => 238,  616 => 237,  612 => 236,  602 => 231,  596 => 230,  590 => 228,  585 => 226,  579 => 223,  572 => 219,  568 => 218,  564 => 217,  556 => 212,  551 => 210,  545 => 207,  540 => 205,  535 => 202,  533 => 201,  528 => 198,  520 => 193,  516 => 192,  512 => 191,  502 => 186,  497 => 184,  491 => 181,  482 => 177,  478 => 176,  474 => 175,  468 => 171,  462 => 170,  458 => 169,  453 => 167,  447 => 164,  442 => 162,  439 => 161,  437 => 160,  430 => 155,  422 => 150,  418 => 149,  414 => 148,  396 => 143,  390 => 140,  386 => 139,  380 => 136,  371 => 132,  367 => 131,  363 => 130,  357 => 126,  351 => 125,  347 => 124,  342 => 122,  336 => 119,  331 => 117,  328 => 116,  326 => 115,  321 => 112,  319 => 111,  310 => 105,  306 => 104,  302 => 103,  295 => 99,  288 => 98,  283 => 96,  277 => 93,  274 => 92,  272 => 91,  264 => 86,  260 => 85,  256 => 84,  247 => 80,  241 => 79,  235 => 77,  230 => 75,  224 => 72,  221 => 71,  219 => 70,  216 => 69,  206 => 62,  202 => 61,  198 => 60,  191 => 56,  184 => 55,  179 => 53,  175 => 52,  169 => 49,  166 => 48,  164 => 47,  155 => 41,  151 => 40,  147 => 39,  142 => 36,  136 => 35,  128 => 34,  121 => 33,  118 => 32,  112 => 31,  108 => 30,  102 => 27,  99 => 26,  97 => 25,  94 => 24,  85 => 22,  83 => 21,  78 => 19,  73 => 17,  70 => 16,  60 => 15,  57 => 14,  55 => 13,  52 => 12,  46 => 9,  41 => 7,  38 => 6,  36 => 5,  31 => 2,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "acp_permissions.html", "");
    }
}
