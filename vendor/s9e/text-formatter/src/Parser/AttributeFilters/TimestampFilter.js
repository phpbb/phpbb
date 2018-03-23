/** @const */
var TimestampFilter =
{
	/**
	* @param  {*} attrValue
	* @return {*}
	*/
	filter: function(attrValue)
	{
		var m = /^(?=\d)(?:(\d+)h)?(?:(\d+)m)?(?:(\d+)s)?$/.exec(attrValue);
		if (m)
		{
			return 3600 * (m[1] || 0) + 60 * (m[2] || 0) + (+m[3] || 0);
		}

		return NumericFilter.filterUint(attrValue);
	}
};