<?php
/**
*
* @package phpBB3
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

use Symfony\Component\Routing\Matcher\Dumper\PhpMatcherDumper;
use Symfony\Component\Routing\RequestContext;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Create and/or return the cached phpbb_url_matcher class
*
* If the class already exists, it instantiates it
*
* @param phpbb_extension_finder $finder Extension finder
* @param RequestContext $context Symfony RequestContext object
* @param string $root_path Root path
* @param string $php_ext PHP extension
* @return phpbb_url_matcher
*/
function phpbb_create_url_matcher(phpbb_extension_finder $finder, RequestContext $context, $root_path, $php_ext)
{
	$matcher = phpbb_load_url_matcher($finder, $context, $root_path, $php_ext);
	if ($matcher === false)
	{
		$provider = new phpbb_controller_provider();
		$dumper = new PhpMatcherDumper($provider->get_paths($finder)->find());
		$cached_url_matcher_dump = $dumper->dump(array(
			'class'			=> 'phpbb_url_matcher',
		));

		file_put_contents($root_path . 'cache/url_matcher' . $php_ext, $cached_url_matcher_dump);
		return phpbb_load_url_matcher($finder, $context, $root_path, $php_ext);
	}

	return $matcher;
}

/**
* Load the cached phpbb_url_matcher class
*
* @param phpbb_extension_finder $finder Extension finder
* @param RequestContext $context Symfony RequestContext object
* @param string $root_path Root path
* @param string $php_ext PHP extension
* @return phpbb_url_matcher|bool False if the file doesn't exist
*/
function phpbb_load_url_matcher(phpbb_extension_finder $finder, RequestContext $context, $root_path, $php_ext)
{
	if (file_exists($root_path . 'cache/url_matcher' . $php_ext))
	{
		include($root_path . 'cache/url_matcher' . $php_ext);
		return new phpbb_url_matcher($context);
	}

	return false;
}
