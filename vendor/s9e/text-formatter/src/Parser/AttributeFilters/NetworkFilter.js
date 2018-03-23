/** @const */
var NetworkFilter =
{
	/**
	* @param  {*} attrValue
	* @return {*}
	*/
	filterIp: function(attrValue)
	{
		if (/^[\d.]+$/.test(attrValue))
		{
			return NetworkFilter.filterIpv4(attrValue);
		}

		if (/^[\da-f:]+$/i.test(attrValue))
		{
			return NetworkFilter.filterIpv6(attrValue);
		}

		return false;
	},

	/**
	* @param  {*} attrValue
	* @return {*}
	*/
	filterIpport: function(attrValue)
	{
		var m, ip;

		if (m = /^\[([\da-f:]+)(\]:[1-9]\d*)$/i.exec(attrValue))
		{
			ip = NetworkFilter.filterIpv6(m[1]);

			if (ip === false)
			{
				return false;
			}

			return '[' + ip + m[2];
		}

		if (m = /^([\d.]+)(:[1-9]\d*)$/.exec(attrValue))
		{
			ip = NetworkFilter.filterIpv4(m[1]);

			if (ip === false)
			{
				return false;
			}

			return ip + m[2];
		}

		return false;
	},

	/**
	* @param  {*} attrValue
	* @return {*}
	*/
	filterIpv4: function(attrValue)
	{
		if (!/^\d+\.\d+\.\d+\.\d+$/.test(attrValue))
		{
			return false;
		}

		var i = 4, p = attrValue.split('.');
		while (--i >= 0)
		{
			// NOTE: ext/filter doesn't support octal notation
			if (p[i][0] === '0' || p[i] > 255)
			{
				return false;
			}
		}

		return attrValue;
	},

	/**
	* @param  {*} attrValue
	* @return {*}
	*/
	filterIpv6: function(attrValue)
	{
		return /^([\da-f]{0,4}:){2,7}(?:[\da-f]{0,4}|\d+\.\d+\.\d+\.\d+)$/.test(attrValue) ? attrValue : false;
	}
};