<?php

/* acp_main.html */
class __TwigTemplate_cadb97091e34b0276544bb1d2ed26951f3caa00eac18b12a2c6acefa925bc45c extends Twig_Template
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
        $this->loadTemplate("overall_header.html", "acp_main.html", 1)->display($context);
        if ($namespace) {
            $this->env->setNamespaceLookUpOrder($previous_look_up_order);
        }
        // line 2
        echo "
<a id=\"maincontent\"></a>

";
        // line 5
        if (($context["S_RESTORE_PERMISSIONS"] ?? null)) {
            // line 6
            echo "
\t<h1>";
            // line 7
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("PERMISSIONS_TRANSFERRED");
            echo "</h1>

\t<p>";
            // line 9
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("PERMISSIONS_TRANSFERRED_EXPLAIN");
            echo "</p>

";
        } else {
            // line 12
            echo "
\t<h1>";
            // line 13
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("WELCOME_PHPBB");
            echo "</h1>

\t<p>";
            // line 15
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ADMIN_INTRO");
            echo "</p>

\t";
            // line 17
            if (($context["S_UPDATE_INCOMPLETE"] ?? null)) {
                // line 18
                echo "\t\t<div class=\"errorbox\">
\t\t\t<p>";
                // line 19
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("UPDATE_INCOMPLETE");
                echo " <a href=\"";
                echo ($context["U_VERSIONCHECK"] ?? null);
                echo "\">";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("MORE_INFORMATION");
                echo "</a></p>
\t\t</div>
\t";
            } elseif (            // line 21
($context["S_VERSIONCHECK_FAIL"] ?? null)) {
                // line 22
                echo "\t\t<div class=\"errorbox notice\">
\t\t\t<p>";
                // line 23
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("VERSIONCHECK_FAIL");
                echo "</p>
\t\t\t<p>";
                // line 24
                echo ($context["VERSIONCHECK_FAIL_REASON"] ?? null);
                echo "</p>
\t\t\t<p><a href=\"";
                // line 25
                echo ($context["U_VERSIONCHECK_FORCE"] ?? null);
                echo "\">";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("VERSIONCHECK_FORCE_UPDATE");
                echo "</a> &middot; <a href=\"";
                echo ($context["U_VERSIONCHECK"] ?? null);
                echo "\">";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("MORE_INFORMATION");
                echo "</a></p>
\t\t</div>
\t";
            } elseif ( !            // line 27
($context["S_VERSION_UP_TO_DATE"] ?? null)) {
                // line 28
                echo "\t\t<div class=\"errorbox\">
\t\t\t<p>";
                // line 29
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("VERSION_NOT_UP_TO_DATE_TITLE");
                echo "</p>
\t\t\t<p><a href=\"";
                // line 30
                echo ($context["U_VERSIONCHECK_FORCE"] ?? null);
                echo "\">";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("VERSIONCHECK_FORCE_UPDATE");
                echo "</a> &middot; <a href=\"";
                echo ($context["U_VERSIONCHECK"] ?? null);
                echo "\">";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("MORE_INFORMATION");
                echo "</a></p>
\t\t</div>
\t";
            }
            // line 33
            echo "\t";
            if (($context["S_VERSION_UPGRADEABLE"] ?? null)) {
                // line 34
                echo "\t\t<div class=\"errorbox notice\">
\t\t\t<p>";
                // line 35
                echo ($context["UPGRADE_INSTRUCTIONS"] ?? null);
                echo "</p>
\t\t</div>
\t";
            }
            // line 38
            echo "
\t";
            // line 39
            if (($context["S_SEARCH_INDEX_MISSING"] ?? null)) {
                // line 40
                echo "\t\t<div class=\"errorbox\">
\t\t\t<h3>";
                // line 41
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("WARNING");
                echo "</h3>
\t\t\t<p>";
                // line 42
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("NO_SEARCH_INDEX");
                echo "</p>
\t\t</div>
\t";
            }
            // line 45
            echo "
\t";
            // line 46
            if (($context["S_REMOVE_INSTALL"] ?? null)) {
                // line 47
                echo "\t\t<div class=\"errorbox\">
\t\t\t<h3>";
                // line 48
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("WARNING");
                echo "</h3>
\t\t\t<p>";
                // line 49
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("REMOVE_INSTALL");
                echo "</p>
\t\t</div>
\t";
            }
            // line 52
            echo "
\t";
            // line 53
            if (($context["S_MBSTRING_LOADED"] ?? null)) {
                // line 54
                echo "\t\t";
                if (($context["S_MBSTRING_FUNC_OVERLOAD_FAIL"] ?? null)) {
                    // line 55
                    echo "\t\t\t<div class=\"errorbox\">
\t\t\t\t<h3>";
                    // line 56
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ERROR_MBSTRING_FUNC_OVERLOAD");
                    echo "</h3>
\t\t\t\t<p>";
                    // line 57
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ERROR_MBSTRING_FUNC_OVERLOAD_EXPLAIN");
                    echo "</p>
\t\t\t</div>
\t\t";
                }
                // line 60
                echo "
\t\t";
                // line 61
                if (($context["S_MBSTRING_ENCODING_TRANSLATION_FAIL"] ?? null)) {
                    // line 62
                    echo "\t\t\t<div class=\"errorbox\">
\t\t\t\t<h3>";
                    // line 63
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ERROR_MBSTRING_ENCODING_TRANSLATION");
                    echo "</h3>
\t\t\t\t<p>";
                    // line 64
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ERROR_MBSTRING_ENCODING_TRANSLATION_EXPLAIN");
                    echo "</p>
\t\t\t</div>
\t\t";
                }
                // line 67
                echo "
\t\t";
                // line 68
                if (($context["S_MBSTRING_HTTP_INPUT_FAIL"] ?? null)) {
                    // line 69
                    echo "\t\t\t<div class=\"errorbox\">
\t\t\t\t<h3>";
                    // line 70
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ERROR_MBSTRING_HTTP_INPUT");
                    echo "</h3>
\t\t\t\t<p>";
                    // line 71
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ERROR_MBSTRING_HTTP_INPUT_EXPLAIN");
                    echo "</p>
\t\t\t</div>
\t\t";
                }
                // line 74
                echo "
\t\t";
                // line 75
                if (($context["S_MBSTRING_HTTP_OUTPUT_FAIL"] ?? null)) {
                    // line 76
                    echo "\t\t\t<div class=\"errorbox\">
\t\t\t\t<h3>";
                    // line 77
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ERROR_MBSTRING_HTTP_OUTPUT");
                    echo "</h3>
\t\t\t\t<p>";
                    // line 78
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ERROR_MBSTRING_HTTP_OUTPUT_EXPLAIN");
                    echo "</p>
\t\t\t</div>
\t\t";
                }
                // line 81
                echo "\t";
            }
            // line 82
            echo "
\t";
            // line 83
            if (($context["S_WRITABLE_CONFIG"] ?? null)) {
                // line 84
                echo "\t\t<div class=\"errorbox notice\">
\t\t\t<p>";
                // line 85
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("WRITABLE_CONFIG");
                echo "</p>
\t\t</div>
\t";
            }
            // line 88
            echo "
\t";
            // line 89
            if (($context["S_PHP_VERSION_OLD"] ?? null)) {
                // line 90
                echo "\t\t<div class=\"errorbox notice\">
\t\t\t<p>";
                // line 91
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("PHP_VERSION_OLD");
                echo "</p>
\t\t</div>
\t";
            }
            // line 94
            echo "
\t";
            // line 95
            // line 96
            echo "
\t<table class=\"table1 two-columns no-header\" data-no-responsive-header=\"true\">
\t\t<caption>";
            // line 98
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("FORUM_STATS");
            echo "</caption>
\t\t<col class=\"col1\" /><col class=\"col2\" /><col class=\"col1\" /><col class=\"col2\" />
\t<thead>
\t<tr>
\t\t<th>";
            // line 102
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("STATISTIC");
            echo "</th>
\t\t<th>";
            // line 103
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("VALUE");
            echo "</th>
\t\t<th>";
            // line 104
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("STATISTIC");
            echo "</th>
\t\t<th>";
            // line 105
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("VALUE");
            echo "</th>
\t</tr>
\t</thead>
\t<tbody>
\t<tr>
\t\t<td>";
            // line 110
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("NUMBER_POSTS");
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
            echo " </td>
\t\t<td><strong>";
            // line 111
            echo ($context["TOTAL_POSTS"] ?? null);
            echo "</strong></td>
\t\t<td>";
            // line 112
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("POSTS_PER_DAY");
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
            echo " </td>
\t\t<td><strong>";
            // line 113
            echo ($context["POSTS_PER_DAY"] ?? null);
            echo "</strong></td>
\t</tr>
\t<tr>
\t\t<td>";
            // line 116
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("NUMBER_TOPICS");
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
            echo " </td>
\t\t<td><strong>";
            // line 117
            echo ($context["TOTAL_TOPICS"] ?? null);
            echo "</strong></td>
\t\t<td>";
            // line 118
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("TOPICS_PER_DAY");
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
            echo " </td>
\t\t<td><strong>";
            // line 119
            echo ($context["TOPICS_PER_DAY"] ?? null);
            echo "</strong></td>
\t</tr>
\t<tr>
\t\t<td>";
            // line 122
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("NUMBER_USERS");
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
            echo " </td>
\t\t<td><strong>";
            // line 123
            echo ($context["TOTAL_USERS"] ?? null);
            echo "</strong></td>
\t\t<td>";
            // line 124
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("USERS_PER_DAY");
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
            echo " </td>
\t\t<td><strong>";
            // line 125
            echo ($context["USERS_PER_DAY"] ?? null);
            echo "</strong></td>
\t</tr>
\t<tr>
\t\t<td>";
            // line 128
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("NUMBER_FILES");
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
            echo " </td>
\t\t<td><strong>";
            // line 129
            echo ($context["TOTAL_FILES"] ?? null);
            echo "</strong></td>
\t\t<td>";
            // line 130
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("FILES_PER_DAY");
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
            echo " </td>
\t\t<td><strong>";
            // line 131
            echo ($context["FILES_PER_DAY"] ?? null);
            echo "</strong></td>
\t</tr>
\t<tr>
\t\t<td>";
            // line 134
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("BOARD_STARTED");
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
            echo " </td>
\t\t<td><strong>";
            // line 135
            echo ($context["START_DATE"] ?? null);
            echo "</strong></td>
\t\t<td>";
            // line 136
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("AVATAR_DIR_SIZE");
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
            echo " </td>
\t\t<td><strong>";
            // line 137
            echo ($context["AVATAR_DIR_SIZE"] ?? null);
            echo "</strong></td>
\t</tr>
\t<tr>
\t\t<td>";
            // line 140
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("DATABASE_SIZE");
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
            echo " </td>
\t\t<td><strong>";
            // line 141
            echo ($context["DBSIZE"] ?? null);
            echo "</strong></td>
\t\t<td>";
            // line 142
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("UPLOAD_DIR_SIZE");
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
            echo " </td>
\t\t<td><strong>";
            // line 143
            echo ($context["UPLOAD_DIR_SIZE"] ?? null);
            echo "</strong></td>
\t</tr>
\t<tr>
\t\t<td>";
            // line 146
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("DATABASE_SERVER_INFO");
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
            echo " </td>
\t\t<td><strong>";
            // line 147
            echo ($context["DATABASE_INFO"] ?? null);
            echo "</strong></td>
\t\t<td>";
            // line 148
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("GZIP_COMPRESSION");
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
            echo " </td>
\t\t<td><strong>";
            // line 149
            echo ($context["GZIP_COMPRESSION"] ?? null);
            echo "</strong></td>
\t</tr>
\t<tr>
\t\t<td>";
            // line 152
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("PHP_VERSION");
            echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
            echo " </td>
\t\t<td><strong>";
            // line 153
            echo ($context["PHP_VERSION_INFO"] ?? null);
            echo "</strong></td>
\t";
            // line 154
            if (($context["S_TOTAL_ORPHAN"] ?? null)) {
                // line 155
                echo "\t\t<td>";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("NUMBER_ORPHAN");
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
                echo " </td>
\t\t<td><strong>";
                // line 156
                echo ($context["TOTAL_ORPHAN"] ?? null);
                echo "</strong></td>
\t";
            } else {
                // line 158
                echo "\t\t<td>&nbsp;</td>
\t\t<td>&nbsp;</td>
\t";
            }
            // line 161
            echo "\t</tr>
\t";
            // line 162
            if (($context["S_VERSIONCHECK"] ?? null)) {
                // line 163
                echo "\t<tr>
\t\t<td>";
                // line 164
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("BOARD_VERSION");
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
                echo " </td>
\t\t<td>
\t\t\t<strong><a href=\"";
                // line 166
                echo ($context["U_VERSIONCHECK"] ?? null);
                echo "\" ";
                if (($context["S_VERSION_UP_TO_DATE"] ?? null)) {
                    echo "style=\"color: #228822;\" ";
                } elseif ( !($context["S_VERSIONCHECK_FAIL"] ?? null)) {
                    echo "style=\"color: #BC2A4D;\" ";
                }
                echo "title=\"";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("MORE_INFORMATION");
                echo "\">";
                echo ($context["BOARD_VERSION"] ?? null);
                echo "</a></strong> [&nbsp;<a href=\"";
                echo ($context["U_VERSIONCHECK_FORCE"] ?? null);
                echo "\">";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("VERSIONCHECK_FORCE_UPDATE");
                echo "</a>&nbsp;]
\t\t</td>
\t\t<td>&nbsp;</td>
\t\t<td>&nbsp;</td>
\t</tr>
\t";
            }
            // line 172
            echo "\t</tbody>
\t</table>

\t";
            // line 175
            if (($context["S_ACTION_OPTIONS"] ?? null)) {
                // line 176
                echo "\t\t<fieldset>
\t\t\t<legend>";
                // line 177
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("STATISTIC_RESYNC_OPTIONS");
                echo "</legend>

\t\t\t<form id=\"action_online_form\" method=\"post\" action=\"";
                // line 179
                echo ($context["U_ACTION"] ?? null);
                echo "\" data-ajax=\"true\">
\t\t\t\t<dl>
\t\t\t\t\t<dt><label for=\"action_online\">";
                // line 181
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("RESET_ONLINE");
                echo "</label><br /><span>&nbsp;</span></dt>
\t\t\t\t\t<dd><input type=\"hidden\" name=\"action\" value=\"online\" /><input class=\"button2\" type=\"submit\" id=\"action_online\" name=\"action_online\" value=\"";
                // line 182
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("RUN");
                echo "\" /></dd>
\t\t\t\t</dl>
\t\t\t</form>

\t\t\t<form id=\"action_date_form\" method=\"post\" action=\"";
                // line 186
                echo ($context["U_ACTION"] ?? null);
                echo "\" data-ajax=\"true\">
\t\t\t\t<dl>
\t\t\t\t\t<dt><label for=\"action_date\">";
                // line 188
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("RESET_DATE");
                echo "</label><br /><span>&nbsp;</span></dt>
\t\t\t\t\t<dd><input type=\"hidden\" name=\"action\" value=\"date\" /><input class=\"button2\" type=\"submit\" id=\"action_date\" name=\"action_date\" value=\"";
                // line 189
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("RUN");
                echo "\" /></dd>
\t\t\t\t</dl>
\t\t\t</form>

\t\t\t<form id=\"action_stats_form\" method=\"post\" action=\"";
                // line 193
                echo ($context["U_ACTION"] ?? null);
                echo "\">
\t\t\t\t<dl>
\t\t\t\t\t<dt><label for=\"action_stats\">";
                // line 195
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("RESYNC_STATS");
                echo "</label><br /><span>";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("RESYNC_STATS_EXPLAIN");
                echo "</span></dt>
\t\t\t\t\t<dd><input type=\"hidden\" name=\"action\" value=\"stats\" /><input class=\"button2\" type=\"submit\" id=\"action_stats\" name=\"action_stats\" value=\"";
                // line 196
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("RUN");
                echo "\" /></dd>
\t\t\t\t</dl>
\t\t\t</form>

\t\t\t<form id=\"action_user_form\" method=\"post\" action=\"";
                // line 200
                echo ($context["U_ACTION"] ?? null);
                echo "\">
\t\t\t\t<dl>
\t\t\t\t\t<dt><label for=\"action_user\">";
                // line 202
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("RESYNC_POSTCOUNTS");
                echo "</label><br /><span>";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("RESYNC_POSTCOUNTS_EXPLAIN");
                echo "</span></dt>
\t\t\t\t\t<dd><input type=\"hidden\" name=\"action\" value=\"user\" /><input class=\"button2\" type=\"submit\" id=\"action_user\" name=\"action_user\" value=\"";
                // line 203
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("RUN");
                echo "\" /></dd>
\t\t\t\t</dl>
\t\t\t</form>

\t\t\t<form id=\"action_db_track_form\" method=\"post\" action=\"";
                // line 207
                echo ($context["U_ACTION"] ?? null);
                echo "\">
\t\t\t\t<dl>
\t\t\t\t\t<dt><label for=\"action_db_track\">";
                // line 209
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("RESYNC_POST_MARKING");
                echo "</label><br /><span>";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("RESYNC_POST_MARKING_EXPLAIN");
                echo "</span></dt>
\t\t\t\t\t<dd><input type=\"hidden\" name=\"action\" value=\"db_track\" /><input class=\"button2\" type=\"submit\" id=\"action_db_track\" name=\"action_db_track\" value=\"";
                // line 210
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("RUN");
                echo "\" /></dd>
\t\t\t\t</dl>
\t\t\t</form>

\t\t\t";
                // line 214
                if (($context["S_FOUNDER"] ?? null)) {
                    // line 215
                    echo "\t\t\t<form id=\"action_purge_sessions_form\" method=\"post\" action=\"";
                    echo ($context["U_ACTION"] ?? null);
                    echo "\" data-ajax=\"true\">
\t\t\t\t<dl>
\t\t\t\t\t<dt><label for=\"action_purge_sessions\">";
                    // line 217
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("PURGE_SESSIONS");
                    echo "</label><br /><span>";
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("PURGE_SESSIONS_EXPLAIN");
                    echo "</span></dt>
\t\t\t\t\t<dd><input type=\"hidden\" name=\"action\" value=\"purge_sessions\" /><input class=\"button2\" type=\"submit\" id=\"action_purge_sessions\" name=\"action_purge_sessions\" value=\"";
                    // line 218
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("RUN");
                    echo "\" /></dd>
\t\t\t\t</dl>
\t\t\t</form>
\t\t\t";
                }
                // line 222
                echo "
\t\t\t<form id=\"action_purge_cache_form\" method=\"post\" action=\"";
                // line 223
                echo ($context["U_ACTION"] ?? null);
                echo "\" data-ajax=\"true\">
\t\t\t\t<dl>
\t\t\t\t\t<dt><label for=\"action_purge_cache\">";
                // line 225
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("PURGE_CACHE");
                echo "</label><br /><span>";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("PURGE_CACHE_EXPLAIN");
                echo "</span></dt>
\t\t\t\t\t<dd><input type=\"hidden\" name=\"action\" value=\"purge_cache\" /><input class=\"button2\" type=\"submit\" id=\"action_purge_cache\" name=\"action_purge_cache\" value=\"";
                // line 226
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("RUN");
                echo "\" /></dd>
\t\t\t\t</dl>
\t\t\t</form>

\t\t\t";
                // line 230
                // line 231
                echo "  \t\t</fieldset>
\t";
            }
            // line 233
            echo "
\t";
            // line 234
            if (twig_length_filter($this->env, $this->getAttribute(($context["loops"] ?? null), "log", array()))) {
                // line 235
                echo "\t\t<h2>";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ADMIN_LOG");
                echo "</h2>

\t\t<p>";
                // line 237
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ADMIN_LOG_INDEX_EXPLAIN");
                echo "</p>

\t\t<div style=\"text-align: right;\"><a href=\"";
                // line 239
                echo ($context["U_ADMIN_LOG"] ?? null);
                echo "\">&raquo; ";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("VIEW_ADMIN_LOG");
                echo "</a></div>

\t\t<table class=\"table1 zebra-table\">
\t\t<thead>
\t\t<tr>
\t\t\t<th>";
                // line 244
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("USERNAME");
                echo "</th>
\t\t\t<th>";
                // line 245
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("IP");
                echo "</th>
\t\t\t<th>";
                // line 246
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("TIME");
                echo "</th>
\t\t\t<th>";
                // line 247
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ACTION");
                echo "</th>
\t\t</tr>
\t\t</thead>
\t\t<tbody>
\t\t";
                // line 251
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable($this->getAttribute(($context["loops"] ?? null), "log", array()));
                foreach ($context['_seq'] as $context["_key"] => $context["log"]) {
                    // line 252
                    echo "\t\t\t<tr>
\t\t\t\t<td>";
                    // line 253
                    echo $this->getAttribute($context["log"], "USERNAME", array());
                    echo "</td>
\t\t\t\t<td style=\"text-align: center;\">";
                    // line 254
                    echo $this->getAttribute($context["log"], "IP", array());
                    echo "</td>
\t\t\t\t<td style=\"text-align: center;\">";
                    // line 255
                    echo $this->getAttribute($context["log"], "DATE", array());
                    echo "</td>
\t\t\t\t<td>";
                    // line 256
                    echo $this->getAttribute($context["log"], "ACTION", array());
                    echo "</td>
\t\t\t</tr>
\t\t";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['log'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 259
                echo "\t\t</tbody>
\t\t</table>
\t";
            }
            // line 262
            echo "
\t";
            // line 263
            if (($context["S_INACTIVE_USERS"] ?? null)) {
                // line 264
                echo "\t\t<h2>";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("INACTIVE_USERS");
                echo "</h2>

\t\t<p>";
                // line 266
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("INACTIVE_USERS_EXPLAIN_INDEX");
                echo "</p>

\t\t<div style=\"text-align: right;\"><a href=\"";
                // line 268
                echo ($context["U_INACTIVE_USERS"] ?? null);
                echo "\">&raquo; ";
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("VIEW_INACTIVE_USERS");
                echo "</a></div>

\t\t<table class=\"table1 zebra-table\">
\t\t<thead>
\t\t<tr>
\t\t\t<th>";
                // line 273
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("USERNAME");
                echo "</th>
\t\t\t<th>";
                // line 274
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("JOINED");
                echo "</th>
\t\t\t<th>";
                // line 275
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("INACTIVE_DATE");
                echo "</th>
\t\t\t<th>";
                // line 276
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("LAST_VISIT");
                echo "</th>
\t\t\t<th>";
                // line 277
                echo $this->env->getExtension('phpbb\template\twig\extension')->lang("INACTIVE_REASON");
                echo "</th>
\t\t</tr>
\t\t</thead>
\t\t<tbody>
\t\t";
                // line 281
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable($this->getAttribute(($context["loops"] ?? null), "inactive", array()));
                $context['_iterated'] = false;
                foreach ($context['_seq'] as $context["_key"] => $context["inactive"]) {
                    // line 282
                    echo "\t\t\t<tr>
\t\t\t\t<td style=\"vertical-align: top;\">
\t\t\t\t\t";
                    // line 284
                    echo $this->getAttribute($context["inactive"], "USERNAME_FULL", array());
                    echo "
\t\t\t\t\t";
                    // line 285
                    if ($this->getAttribute($context["inactive"], "POSTS", array())) {
                        echo "<br />";
                        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("POSTS");
                        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("COLON");
                        echo " <strong>";
                        echo $this->getAttribute($context["inactive"], "POSTS", array());
                        echo "</strong> [<a href=\"";
                        echo $this->getAttribute($context["inactive"], "U_SEARCH_USER", array());
                        echo "\">";
                        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("SEARCH_USER_POSTS");
                        echo "</a>]";
                    }
                    // line 286
                    echo "\t\t\t\t</td>
\t\t\t\t<td style=\"vertical-align: top;\">";
                    // line 287
                    echo $this->getAttribute($context["inactive"], "JOINED", array());
                    echo "</td>
\t\t\t\t<td style=\"vertical-align: top;\">";
                    // line 288
                    echo $this->getAttribute($context["inactive"], "INACTIVE_DATE", array());
                    echo "</td>
\t\t\t\t<td style=\"vertical-align: top;\">";
                    // line 289
                    echo $this->getAttribute($context["inactive"], "LAST_VISIT", array());
                    echo "</td>
\t\t\t\t<td style=\"vertical-align: top;\">
\t\t\t\t\t";
                    // line 291
                    echo $this->getAttribute($context["inactive"], "REASON", array());
                    echo "
\t\t\t\t\t";
                    // line 292
                    if ($this->getAttribute($context["inactive"], "REMINDED", array())) {
                        echo "<br />";
                        echo $this->getAttribute($context["inactive"], "REMINDED_EXPLAIN", array());
                    }
                    // line 293
                    echo "\t\t\t\t</td>
\t\t\t</tr>
\t\t";
                    $context['_iterated'] = true;
                }
                if (!$context['_iterated']) {
                    // line 296
                    echo "\t\t\t<tr>
\t\t\t\t<td colspan=\"5\" style=\"text-align: center;\">";
                    // line 297
                    echo $this->env->getExtension('phpbb\template\twig\extension')->lang("NO_INACTIVE_USERS");
                    echo "</td>
\t\t\t</tr>
\t\t";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['inactive'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 300
                echo "\t\t</tbody>
\t\t</table>
\t";
            }
            // line 303
            echo "
";
        }
        // line 305
        echo "
";
        // line 306
        $location = "overall_footer.html";
        $namespace = false;
        if (strpos($location, '@') === 0) {
            $namespace = substr($location, 1, strpos($location, '/') - 1);
            $previous_look_up_order = $this->env->getNamespaceLookUpOrder();
            $this->env->setNamespaceLookUpOrder(array($namespace, '__main__'));
        }
        $this->loadTemplate("overall_footer.html", "acp_main.html", 306)->display($context);
        if ($namespace) {
            $this->env->setNamespaceLookUpOrder($previous_look_up_order);
        }
    }

    public function getTemplateName()
    {
        return "acp_main.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  874 => 306,  871 => 305,  867 => 303,  862 => 300,  853 => 297,  850 => 296,  843 => 293,  838 => 292,  834 => 291,  829 => 289,  825 => 288,  821 => 287,  818 => 286,  805 => 285,  801 => 284,  797 => 282,  792 => 281,  785 => 277,  781 => 276,  777 => 275,  773 => 274,  769 => 273,  759 => 268,  754 => 266,  748 => 264,  746 => 263,  743 => 262,  738 => 259,  729 => 256,  725 => 255,  721 => 254,  717 => 253,  714 => 252,  710 => 251,  703 => 247,  699 => 246,  695 => 245,  691 => 244,  681 => 239,  676 => 237,  670 => 235,  668 => 234,  665 => 233,  661 => 231,  660 => 230,  653 => 226,  647 => 225,  642 => 223,  639 => 222,  632 => 218,  626 => 217,  620 => 215,  618 => 214,  611 => 210,  605 => 209,  600 => 207,  593 => 203,  587 => 202,  582 => 200,  575 => 196,  569 => 195,  564 => 193,  557 => 189,  553 => 188,  548 => 186,  541 => 182,  537 => 181,  532 => 179,  527 => 177,  524 => 176,  522 => 175,  517 => 172,  494 => 166,  488 => 164,  485 => 163,  483 => 162,  480 => 161,  475 => 158,  470 => 156,  464 => 155,  462 => 154,  458 => 153,  453 => 152,  447 => 149,  442 => 148,  438 => 147,  433 => 146,  427 => 143,  422 => 142,  418 => 141,  413 => 140,  407 => 137,  402 => 136,  398 => 135,  393 => 134,  387 => 131,  382 => 130,  378 => 129,  373 => 128,  367 => 125,  362 => 124,  358 => 123,  353 => 122,  347 => 119,  342 => 118,  338 => 117,  333 => 116,  327 => 113,  322 => 112,  318 => 111,  313 => 110,  305 => 105,  301 => 104,  297 => 103,  293 => 102,  286 => 98,  282 => 96,  281 => 95,  278 => 94,  272 => 91,  269 => 90,  267 => 89,  264 => 88,  258 => 85,  255 => 84,  253 => 83,  250 => 82,  247 => 81,  241 => 78,  237 => 77,  234 => 76,  232 => 75,  229 => 74,  223 => 71,  219 => 70,  216 => 69,  214 => 68,  211 => 67,  205 => 64,  201 => 63,  198 => 62,  196 => 61,  193 => 60,  187 => 57,  183 => 56,  180 => 55,  177 => 54,  175 => 53,  172 => 52,  166 => 49,  162 => 48,  159 => 47,  157 => 46,  154 => 45,  148 => 42,  144 => 41,  141 => 40,  139 => 39,  136 => 38,  130 => 35,  127 => 34,  124 => 33,  112 => 30,  108 => 29,  105 => 28,  103 => 27,  92 => 25,  88 => 24,  84 => 23,  81 => 22,  79 => 21,  70 => 19,  67 => 18,  65 => 17,  60 => 15,  55 => 13,  52 => 12,  46 => 9,  41 => 7,  38 => 6,  36 => 5,  31 => 2,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "acp_main.html", "");
    }
}
