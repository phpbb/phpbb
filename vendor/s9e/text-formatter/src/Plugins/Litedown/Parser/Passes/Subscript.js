function parse()
{
	parseAbstractScript('SUB', '~', /~(?!\()[^\x17\s~()]+~?/g, /~\([^\x17()]+\)/g);
}