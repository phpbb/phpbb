/** @const */
var EmailFilter =
{
	/**
	* @param  {*} attrValue
	* @return {*}
	*/
	filter: function(attrValue)
	{
		return /^[-\w.+]+@[-\w.]+$/.test(attrValue) ? attrValue : false;
	}
};