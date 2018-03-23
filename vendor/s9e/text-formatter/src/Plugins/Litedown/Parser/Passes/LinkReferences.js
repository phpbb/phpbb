function parse()
{
	if (text.indexOf(']:') < 0)
	{
		return;
	}

	var m, regexp = /^\x1A* {0,3}\[([^\x17\]]+)\]: *([^\s\x17]+ *(?:"[^\x17]*?"|'[^\x17]*?'|\([^\x17)]*\))?)[^\x17\n]*\n?/gm;
	while (m = regexp.exec(text))
	{
		addIgnoreTag(m['index'], m[0].length, -2);

		// Only add the reference if it does not already exist
		var id = m[1].toLowerCase();
		if (!linkReferences[id])
		{
			hasReferences      = true;
			linkReferences[id] = m[2];
		}
	}
}