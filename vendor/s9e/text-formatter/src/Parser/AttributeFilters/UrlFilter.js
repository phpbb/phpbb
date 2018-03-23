/** @const */
var UrlFilter =
{
	/**
	* @param  {*} attrValue
	* @param  {!Object} urlConfig
	* @param  {Logger} logger
	* @return {*}
	*/
	filter: function(attrValue, urlConfig, logger)
	{
		/**
		* Trim the URL to conform with HTML5 then parse it
		* @link http://dev.w3.org/html5/spec/links.html#attr-hyperlink-href
		*/
		var p = UrlFilter.parseUrl(attrValue.replace(/^\s+/, '').replace(/\s+$/, ''));

		var error = UrlFilter.validateUrl(urlConfig, p);
		if (error)
		{
			if (logger)
			{
				p['attrValue'] = attrValue;
				logger.err(error, p);
			}

			return false;
		}

		return UrlFilter.rebuildUrl(urlConfig, p);
	},

	/**
	* Parse a URL and return its components
	*
	* Similar to PHP's own parse_url() except that all parts are always returned
	*
	* @param  {!string} url Original URL
	* @return {!Object}
	*/
	parseUrl: function(url)
	{
		var regexp = /^(?:([a-z][-+.\w]*):)?(?:\/\/(?:([^:\/?#]*)(?::([^\/?#]*)?)?@)?(?:(\[[a-f\d:]+\]|[^:\/?#]+)(?::(\d*))?)?(?![^\/?#]))?([^?#]*)(\?[^#]*)?(#.*)?$/i;

		// NOTE: this regexp always matches because of the last three captures
		var m = regexp['exec'](url),
			parts = {},
			tokens = ['scheme', 'user', 'pass', 'host', 'port', 'path', 'query', 'fragment'];
		tokens.forEach(
			function(name, i)
			{
				parts[name] = (m[i + 1] > '') ? m[i + 1] : '';
			}
		);

		/**
		* @link http://tools.ietf.org/html/rfc3986#section-3.1
		*
		* 'An implementation should accept uppercase letters as equivalent to lowercase in
		* scheme names (e.g., allow "HTTP" as well as "http") for the sake of robustness but
		* should only produce lowercase scheme names for consistency.'
		*/
		parts['scheme'] = parts['scheme'].toLowerCase();

		/**
		* Normalize the domain label separators and remove trailing dots
		* @link http://url.spec.whatwg.org/#domain-label-separators
		*/
		parts['host'] = parts['host'].replace(/[\u3002\uff0e\uff61]/g, '.').replace(/\.+$/g, '');

		// Test whether host has non-ASCII characters and punycode it if possible
		if (/[^\x00-\x7F]/.test(parts['host']) && typeof punycode !== 'undefined')
		{
			parts['host'] = punycode.toASCII(parts['host']);
		}

		return parts;
	},

	/**
	* Rebuild a parsed URL
	*
	* @param  {!Object} urlConfig
	* @param  {!Object} p
	* @return {!string}
	*/
	rebuildUrl: function(urlConfig, p)
	{
		var url = '';
		if (p['scheme'] !== '')
		{
			url += p['scheme'] + ':';
		}
		if (p['host'] === '')
		{
			// Allow the file: scheme to not have a host and ensure it starts with slashes
			if (p['scheme'] === 'file')
			{
				url += '//';
			}
		}
		else
		{
			url += '//';

			// Add the credentials if applicable
			if (p['user'] !== '')
			{
				// Reencode the credentials in case there are invalid chars in them, or suspicious
				// characters such as : or @ that could confuse a browser into connecting to the
				// wrong host (or at least, to a host that is different than the one we thought)
				url += rawurlencode(decodeURIComponent(p['user']));

				if (p['pass'] !== '')
				{
					url += ':' + rawurlencode(decodeURIComponent(p['pass']));
				}

				url += '@';
			}

			url += p['host'];

			// Append the port number (note that as per the regexp it can only contain digits)
			if (p['port'] !== '')
			{
				url += ':' + p['port'];
			}
		}

		// Build the path, including the query and fragment parts
		var path = p['path'] + p['query'] + p['fragment'];

		/**
		* "For consistency, URI producers and normalizers should use uppercase hexadecimal digits
		* for all percent- encodings."
		*
		* @link http://tools.ietf.org/html/rfc3986#section-2.1
		*/
		path = path.replace(
			/%.?[a-f]/g,
			function (str)
			{
				return str.toUpperCase();
			},
			path
		);

		// Append the sanitized path to the URL
		url += UrlFilter.sanitizeUrl(path);

		// Replace the first colon if there's no scheme and it could potentially be interpreted as
		// the scheme separator
		if (!p['scheme'])
		{
			url = url.replace(/^([^\/]*):/, '$1%3A', url);
		}

		return url;
	},

	/**
	* Sanitize a URL for safe use regardless of context
	*
	* This method URL-encodes some sensitive characters in case someone would want to use the URL in
	* some JavaScript thingy, or in CSS. We also encode characters that are not allowed in the path
	* of a URL as defined in RFC 3986 appendix A, including percent signs that are not immediately
	* followed by two hex digits.
	*
	* " and ' to prevent breaking out of quotes (JavaScript or otherwise)
	* ( and ) to prevent the use of functions in JavaScript (eval()) or CSS (expression())
	* < and > to prevent breaking out of <script>
	* \r and \n because they're illegal in JavaScript
	* [ and ] because the W3 validator rejects them and they "should" be escaped as per RFC 3986
	* Non-ASCII characters as per RFC 3986
	* Control codes and spaces, as per RFC 3986
	*
	* @link http://sla.ckers.org/forum/read.php?2,51478
	* @link http://timelessrepo.com/json-isnt-a-javascript-subset
	* @link http://www.ietf.org/rfc/rfc3986.txt
	* @link http://stackoverflow.com/a/1547922
	* @link http://tools.ietf.org/html/rfc3986#appendix-A
	*
	* @param  {!string} url Original URL
	* @return {!string}     Sanitized URL
	*/
	sanitizeUrl: function(url)
	{
		return url.replace(/[^\u0020-\u007E]+/g, encodeURIComponent).replace(/%(?![0-9A-Fa-f]{2})|[^!#-&*-;=?-Z_a-z~]/g, escape);
	},

	/**
	* Validate a parsed URL
	*
	* @param  {!Object} urlConfig
	* @param  {!Object} p
	* @return {string|undefined}
	*/
	validateUrl: function(urlConfig, p)
	{
		if (p['scheme'] !== '' && !urlConfig.allowedSchemes.test(p['scheme']))
		{
			return 'URL scheme is not allowed';
		}

		if (p['host'] === '')
		{
			// Reject malformed URLs such as http:///example.org but allow schemeless paths
			if (p['scheme'] !== 'file' && p['scheme'] !== '')
			{
				return 'Missing host';
			}
		}
		else
		{
			/**
			* Test whether the host is valid
			* @link http://tools.ietf.org/html/rfc1035#section-2.3.1
			* @link http://tools.ietf.org/html/rfc1123#section-2
			*/
			var regexp = /^(?!-)[-a-z0-9]{0,62}[a-z0-9](?:\.(?!-)[-a-z0-9]{0,62}[a-z0-9])*$/i;
			if (!regexp.test(p['host']))
			{
				// If the host invalid, retest as an IPv4 and IPv6 address (IPv6 in brackets)
				if (!NetworkFilter.filterIpv4(p['host'])
				 && !NetworkFilter.filterIpv6(p['host'].replace(/^\[(.*)\]$/, '$1', p['host'])))
				{
					return 'URL host is invalid';
				}
			}

			if ((urlConfig.disallowedHosts && urlConfig.disallowedHosts.test(p['host']))
			 || (urlConfig.restrictedHosts && !urlConfig.restrictedHosts.test(p['host'])))
			{
				return 'URL host is not allowed';
			}
		}
	}
};