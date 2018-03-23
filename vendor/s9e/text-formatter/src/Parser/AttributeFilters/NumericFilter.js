/** @const */
var NumericFilter =
{
	/**
	* @param  {*} attrValue
	* @return {*}
	*/
	filterFloat: function(attrValue)
	{
		return /^(?:0|-?[1-9]\d*)(?:\.\d+)?(?:e[1-9]\d*)?$/i.test(attrValue) ? attrValue : false;
	},

	/**
	* @param  {*} attrValue
	* @return {*}
	*/
	filterInt: function(attrValue)
	{
		return /^(?:0|-?[1-9]\d*)$/.test(attrValue) ? attrValue : false;
	},

	/**
	* @param  {*}       attrValue
	* @param  {!number} min
	* @param  {!number} max
	* @param  {Logger}  logger
	* @return {!number|boolean}
	*/
	filterRange: function(attrValue, min, max, logger)
	{
		if (!/^(?:0|-?[1-9]\d*)$/.test(attrValue))
		{
			return false;
		}

		attrValue = parseInt(attrValue, 10);

		if (attrValue < min)
		{
			if (logger)
			{
				logger.warn(
					'Value outside of range, adjusted up to min value',
					{
						'attrValue' : attrValue,
						'min'       : min,
						'max'       : max
					}
				);
			}

			return min;
		}

		if (attrValue > max)
		{
			if (logger)
			{
				logger.warn(
					'Value outside of range, adjusted down to max value',
					{
						'attrValue' : attrValue,
						'min'       : min,
						'max'       : max
					}
				);
			}

			return max;
		}

		return attrValue;
	},

	/**
	* @param  {*} attrValue
	* @return {*}
	*/
	filterUint: function(attrValue)
	{
		return /^(?:0|[1-9]\d*)$/.test(attrValue) ? attrValue : false;
	}
};