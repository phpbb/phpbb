function parse()
{
	parseAbstractScript('SUP', '^', /\^(?!\()[^\x17\s^()]+\^?/g, /\^\([^\x17()]+\)/g);
}