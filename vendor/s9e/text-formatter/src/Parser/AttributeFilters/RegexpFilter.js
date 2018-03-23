/** @const */
var RegexpFilter =
{
	/**
	* @param  {*} attrValue
	* @param  {!RegExp} regexp
	* @return {*}
	*/
	filter: function(attrValue, regexp)
	{
		return regexp.test(attrValue) ? attrValue : false;
	}
};