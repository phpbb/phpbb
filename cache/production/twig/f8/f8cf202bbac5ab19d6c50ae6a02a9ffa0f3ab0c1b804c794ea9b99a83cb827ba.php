<?php

/* @phpbb_viglink/event/acp_help_phpbb_stats_after.html */
class __TwigTemplate_4592c6814c799f27c866fc34c37b01c22775d638dc757a2fc4f05630400e5c22 extends Twig_Template
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
        echo "<div class=\"send-stats-tile\">
    <h2 class=\"viglink-header-h2\"><span class=\"viglink-header\"></span></h2>
    <p>";
        // line 3
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ACP_VIGLINK_SETTINGS_EXPLAIN");
        echo "<br /><br />";
        echo ($context["ACP_VIGLINK_SETTINGS_CHANGE"] ?? null);
        echo "</p>
    <dl class=\"send-stats-settings\">
        <dt>
            <input name=\"enable-viglink\" id=\"enable-viglink\" type=\"checkbox\" ";
        // line 6
        if ((($context["S_ENABLE_VIGLINK"] ?? null) == 1)) {
            echo "checked=\"checked\"";
        }
        echo "/>
            <label for=\"enable-viglink\"></label>
        </dt>
        <dd>";
        // line 9
        echo $this->env->getExtension('phpbb\template\twig\extension')->lang("ENABLE");
        echo "</dd>
    </dl>
</div>

";
        // line 13
        $asset_file = "@phpbb_viglink/viglink.css";
        $asset = new \phpbb\template\asset($asset_file, $this->getEnvironment()->get_path_helper(), $this->getEnvironment()->get_filesystem());
        if (substr($asset_file, 0, 2) !== './' && $asset->is_relative()) {
            $asset_path = $asset->get_path();            $local_file = $this->getEnvironment()->get_phpbb_root_path() . $asset_path;
            if (!file_exists($local_file)) {
                $local_file = $this->getEnvironment()->findTemplate($asset_path);
                $asset->set_path($local_file, true);
            }
            $asset->add_assets_version('2');
        }
        $this->getEnvironment()->get_assets_bag()->add_stylesheet($asset);    }

    public function getTemplateName()
    {
        return "@phpbb_viglink/event/acp_help_phpbb_stats_after.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  46 => 13,  39 => 9,  31 => 6,  23 => 3,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "@phpbb_viglink/event/acp_help_phpbb_stats_after.html", "");
    }
}
