<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

use Symfony\Component\Routing\Matcher\Dumper\PhpMatcherDumper;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Create a new UrlMatcher class and dump it into the cache file
*
* @param \phpbb\extension\manager $manager Extension manager
* @param RequestContext $context Symfony RequestContext object
* @param string $root_path Root path
* @param string $php_ext PHP file extension
* @return null
*/
function phpbb_get_url_matcher(\phpbb\extension\manager $manager, RequestContext $context, $root_path, $php_ext)
{
	if (defined('DEBUG'))
	{
		return phpbb_create_url_matcher($manager, $context, $root_path);
	}

	if (!phpbb_url_matcher_dumped($root_path, $php_ext))
	{
		phpbb_create_dumped_url_matcher($manager, $root_path, $php_ext);
	}

	return phpbb_load_url_matcher($context, $root_path, $php_ext);
}

/**
* Create a new UrlMatcher class and dump it into the cache file
*
* @param \phpbb\extension\manager $manager Extension manager
* @param string $root_path Root path
* @param string $php_ext PHP file extension
* @return null
*/
function phpbb_create_dumped_url_matcher(\phpbb\extension\manager $manager, $root_path, $php_ext)
{
	$provider = new \phpbb\controller\provider();
	$provider->find_routing_files($manager->get_finder());
	$routes = $provider->find($root_path)->get_routes();
	$dumper = new PhpMatcherDumper($routes);
	$cached_url_matcher_dump = $dumper->dump(array(
		'class'			=> 'phpbb_url_matcher',
	));

	file_put_contents($root_path . 'cache/url_matcher.' . $php_ext, $cached_url_matcher_dump);
}

/**
* Create a non-cached UrlMatcher
*
* @param \phpbb\extension\manager $manager Extension manager
* @param RequestContext $context Symfony RequestContext object
* @return UrlMatcher
*/
function phpbb_create_url_matcher(\phpbb\extension\manager $manager, RequestContext $context, $root_path)
{
	$provider = new \phpbb\controller\provider();
	$provider->find_routing_files($manager->get_finder());
	$routes = $provider->find($root_path)->get_routes();
	return new UrlMatcher($routes, $context);
}

/**
* Load the cached phpbb_url_matcher class
*
* @param RequestContext $context Symfony RequestContext object
* @param string $root_path Root path
* @param string $php_ext PHP file extension
* @return phpbb_url_matcher
*/
function phpbb_load_url_matcher(RequestContext $context, $root_path, $php_ext)
{
	require($root_path . 'cache/url_matcher.' . $php_ext);
	return new phpbb_url_matcher($context);
}

/**
* Determine whether we have our dumped URL matcher
*
* The class is automatically dumped to the cache directory
*
* @param string $root_path Root path
* @param string $php_ext PHP file extension
* @return bool True if it exists, false if not
*/
function phpbb_url_matcher_dumped($root_path, $php_ext)
{
	return file_exists($root_path . 'cache/url_matcher.' . $php_ext);
}
