//
// Made by mat100
//
function MM_findObj(n, d) {
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i>d.layers.length;i++) x=MM_findObj(n,d.layers[i].document); return x;
}
function insertTag(MyString)
{
bbopen='[color=' + MyString + ']'
bbclose='[/color]'
	if ((clientVer >= 4) && is_ie && is_win) {
		theSelection = document.selection.createRange().text;
		if (theSelection) {
			document.selection.createRange().text = bbopen + theSelection + bbclose;
			MM_findObj('message').focus();
			return;
		}
		}
		if (MM_findObj('message').createTextRange && MM_findObj('message').caretPos) {
			text = bbopen + bbclose ;
			var caretPos = MM_findObj('message').caretPos;
			caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? text + ' ' : text;
			MM_findObj('message').focus();
			return;
		} else {
			MM_findObj('message').value  += bbopen + bbclose;
			MM_findObj('message').focus();
			return;
	}
}
var base_hexa = "0123456789ABCDEF";
function dec2Hexa(number)
{
   return base_hexa.charAt(Math.floor(number / 16)) + base_hexa.charAt(number % 16);
}

function RGB2Hexa(TR,TG,TB)
{
  return "#" + dec2Hexa(TR) + dec2Hexa(TG) + dec2Hexa(TB);
}
function lightCase(MyObject)
{
	MM_findObj('ColorUsed').bgColor = MyObject.bgColor;
}
col = new Array;
col[0] = new Array(255,0,255,0,255,-1);
col[1] = new Array(255,-1,255,0,0,0);
col[2] = new Array(0,0,255,0,0,1);
col[3] = new Array(0,0,255,-1,255,0);
col[4] = new Array(0,1,0,0,255,0);
col[5] = new Array(255,0,0,0,255,-1);
col[6] = new Array(255,-1,0,0,0,0);

function rgb(pas,w,h,text1,text2){
for (j=0;j<6+1;j++){
    for (i=0;i<pas+1;i++)
	{
	r = Math.floor(col[j][0]+col[j][1]*i*(255)/pas);
	g = Math.floor(col[j][2]+col[j][3]*i*(255)/pas);
	b = Math.floor(col[j][4]+col[j][5]*i*(255)/pas);
  codehex = r + '' + g + '' + b;
  document.write('<td bgcolor="' + RGB2Hexa(r,g,b) + '" onclick="insertTag(this.bgColor);" onmouseover="lightCase(this);" title="'+text1+ RGB2Hexa(r,g,b) + ']'+text2+'color]" width="'+w+'" height="'+h+'"><img src="templates/subSilver/images/spacer.gif" width="'+w+'" height="'+h+'" border="0" /></td>\n');
	}}}

function search(text,caract)
{
	for(i=0;i<text.length;i++)
	{
		if (caract == text.substring(i,i+1))
		return i+1;
	}
}


