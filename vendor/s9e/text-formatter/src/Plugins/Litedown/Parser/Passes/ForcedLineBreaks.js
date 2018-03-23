function parse()
{
	var pos = text.indexOf("  \n");
	while (pos > 0)
	{
		addBrTag(pos + 2);
		pos = text.indexOf("  \n", pos + 3);
	}
}