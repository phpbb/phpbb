<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Parser\AttributeFilters;
use s9e\TextFormatter\Parser\Logger;
class UrlFilter
{
	public static function filter($attrValue, array $urlConfig, Logger $logger = \null)
	{
		$p = self::parseUrl(\trim($attrValue));
		$error = self::validateUrl($urlConfig, $p);
		if (!empty($error))
		{
			if (isset($logger))
			{
				$p['attrValue'] = $attrValue;
				$logger->err($error, $p);
			}
			return \false;
		}
		return self::rebuildUrl($p);
	}
	protected static function parseUrl($url)
	{
		$regexp = '(^(?:([a-z][-+.\\w]*):)?(?://(?:([^:/?#]*)(?::([^/?#]*)?)?@)?(?:(\\[[a-f\\d:]+\\]|[^:/?#]+)(?::(\\d*))?)?(?![^/?#]))?([^?#]*)(\\?[^#]*)?(#.*)?$)Di';
		\preg_match($regexp, $url, $m);
		$parts  = [];
		$tokens = ['scheme', 'user', 'pass', 'host', 'port', 'path', 'query', 'fragment'];
		foreach ($tokens as $i => $name)
			$parts[$name] = (isset($m[$i + 1])) ? $m[$i + 1] : '';
		$parts['scheme'] = \strtolower($parts['scheme']);
		$parts['host'] = \rtrim(\preg_replace("/\xE3\x80\x82|\xEF(?:\xBC\x8E|\xBD\xA1)/s", '.', $parts['host']), '.');
		if (\preg_match('#[^[:ascii:]]#', $parts['host']) && \function_exists('idn_to_ascii'))
		{
			$variant = (\defined('INTL_IDNA_VARIANT_UTS46')) ? \INTL_IDNA_VARIANT_UTS46 : 0;
			$parts['host'] = \idn_to_ascii($parts['host'], 0, $variant);
		}
		return $parts;
	}
	protected static function rebuildUrl(array $p)
	{
		$url = '';
		if ($p['scheme'] !== '')
			$url .= $p['scheme'] . ':';
		if ($p['host'] === '')
		{
			if ($p['scheme'] === 'file')
				$url .= '//';
		}
		else
		{
			$url .= '//';
			if ($p['user'] !== '')
			{
				$url .= \rawurlencode(\urldecode($p['user']));
				if ($p['pass'] !== '')
					$url .= ':' . \rawurlencode(\urldecode($p['pass']));
				$url .= '@';
			}
			$url .= $p['host'];
			if ($p['port'] !== '')
				$url .= ':' . $p['port'];
		}
		$path = $p['path'] . $p['query'] . $p['fragment'];
		$path = \preg_replace_callback(
			'/%.?[a-f]/',
			function ($m)
			{
				return \strtoupper($m[0]);
			},
			$path
		);
		$url .= self::sanitizeUrl($path);
		if (!$p['scheme'])
			$url = \preg_replace('#^([^/]*):#', '$1%3A', $url);
		return $url;
	}
	public static function sanitizeUrl($url)
	{
		return \preg_replace_callback(
			'/%(?![0-9A-Fa-f]{2})|[^!#-&*-;=?-Z_a-z]/S',
			function ($m)
			{
				return \rawurlencode($m[0]);
			},
			$url
		);
	}
	protected static function validateUrl(array $urlConfig, array $p)
	{
		if ($p['scheme'] !== '' && !\preg_match($urlConfig['allowedSchemes'], $p['scheme']))
			return 'URL scheme is not allowed';
		if ($p['host'] === '')
		{
			if ($p['scheme'] !== 'file' && $p['scheme'] !== '')
				return 'Missing host';
		}
		else
		{
			$regexp = '/^(?!-)[-a-z0-9]{0,62}[a-z0-9](?:\\.(?!-)[-a-z0-9]{0,62}[a-z0-9])*$/i';
			if (!\preg_match($regexp, $p['host']))
				if (!NetworkFilter::filterIpv4($p['host'])
				 && !NetworkFilter::filterIpv6(\preg_replace('/^\\[(.*)\\]$/', '$1', $p['host'])))
					return 'URL host is invalid';
			if ((isset($urlConfig['disallowedHosts']) && \preg_match($urlConfig['disallowedHosts'], $p['host']))
			 || (isset($urlConfig['restrictedHosts']) && !\preg_match($urlConfig['restrictedHosts'], $p['host'])))
				return 'URL host is not allowed';
		}
	}
}