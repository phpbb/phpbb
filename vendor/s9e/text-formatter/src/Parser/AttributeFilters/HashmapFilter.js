/** @const */
var HashmapFilter =
{
	/**
	* @param  {*}        attrValue Original value
	* @param  {!Object}  map       Hash map
	* @param  {!boolean} strict    Whether this map is strict (values with no match are invalid)
	* @return {*}                  Filtered value, or FALSE if invalid
	*/
	filter: function(attrValue, map, strict)
	{
		if (attrValue in map)
		{
			return map[attrValue];
		}

		return (strict) ? false : attrValue;
	}
};