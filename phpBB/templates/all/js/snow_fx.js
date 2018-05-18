/***************************************
          Falling snow script
 Written by Mark Wilton-Jones, 6/1/2001
****************************************

Please see http://www.howtocreate.co.uk/jslibs/ for details and a demo of this script
Please see http://www.howtocreate.co.uk/jslibs/termsOfUse.html for terms of use

To use this script add the following just before the </body> tag:

<script type="text/javascript">
<!--

//Edit the next few lines to suit your page. Recommended values are:
//numFlakes = 10; downSpeed = 0.01; lrFlakes = 10;

var pictureSrc = 'snowflake.gif'; //the location of the snowflakes
var pictureWidth = 20;            //the width of the snowflakes
var pictureHeight = 20;           //the height of the snowflakes
var numFlakes = 10;               //the number of snowflakes
var downSpeed = 0.01;             //the falling speed of snowflakes (portion of screen per 100 ms)
var lrFlakes = 10;                //the speed that the snowflakes should swing from side to side
                                  //relative to distance fallen (swing increases with fewer
                                  //snowflakes to fill available space)

//--></script>
<script src="PATH TO SCRIPT/snowscript.js" type="text/javascript" language="javascript1.2"></script>
________________________________________________________________________________________________

              AND NOW THE CODE:
*/


/****** Do not edit below this line unless you know what you are doing ******/

//safety checks. Browsers will hang if this is wrong. If other values are wrong there will just be errors
if( typeof( numFlakes ) != 'number' || Math.round( numFlakes ) != numFlakes || numFlakes < 1 ) { numFlakes = 10; }

//draw the snowflakes
for( var x = 0; x < numFlakes; x++ ) {
	if( document.layers ) { //releave NS4 bug
		document.write('<layer id="snFlkDiv'+x+'"><img src="'+pictureSrc+'" height="'+pictureHeight+'" width="'+pictureWidth+'" alt="*" border="0"></layer>');
	} else {
		document.write('<div style="position:absolute;" id="snFlkDiv'+x+'"><img src="'+pictureSrc+'" height="'+pictureHeight+'" width="'+pictureWidth+'" alt="*" border="0"></div>');
	}
}

//calculate initial positions (in portions of browser window size)
var xcoords = new Array(), ycoords = new Array(), snFlkTemp;
for( var x = 0; x < numFlakes; x++ ) {
	xcoords[x] = ( x + 1 ) / ( numFlakes + 1 );
	do { snFlkTemp = Math.round( ( numFlakes - 1 ) * Math.random() );
	} while( typeof( ycoords[snFlkTemp] ) == 'number' );
	ycoords[snFlkTemp] = x / numFlakes;
}

//now animate
function flakeFall() {
	if( !getRefToDivNest('snFlkDiv0') ) { return; }
	var scrWidth = 0, scrHeight = 0, scrollHeight = 0, scrollWidth = 0;
	//find screen settings for all variations. doing this every time allows for resizing and scrolling
	if( typeof( window.innerWidth ) == 'number' ) { scrWidth = window.innerWidth; scrHeight = window.innerHeight; } else {
		if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
			scrWidth = document.documentElement.clientWidth; scrHeight = document.documentElement.clientHeight; } else {
			if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
				scrWidth = document.body.clientWidth; scrHeight = document.body.clientHeight; } } }
	if( typeof( window.pageYOffset ) == 'number' ) { scrollHeight = pageYOffset; scrollWidth = pageXOffset; } else {
		if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) { scrollHeight = document.body.scrollTop; scrollWidth = document.body.scrollLeft; } else {
			if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) { scrollHeight = document.documentElement.scrollTop; scrollWidth = document.documentElement.scrollLeft; } }
	}
	//move the snowflakes to their new position
	for( var x = 0; x < numFlakes; x++ ) {
		if( ycoords[x] * scrHeight > scrHeight - pictureHeight ) { ycoords[x] = 0; }
		var divRef = getRefToDivNest('snFlkDiv'+x); if( !divRef ) { return; }
		if( divRef.style ) { divRef = divRef.style; } var oPix = document.childNodes ? 'px' : 0;
		divRef.top = ( Math.round( ycoords[x] * scrHeight ) + scrollHeight ) + oPix;
		divRef.left = ( Math.round( ( ( xcoords[x] * scrWidth ) - ( pictureWidth / 2 ) ) + ( ( scrWidth / ( ( numFlakes + 1 ) * 4 ) ) * ( Math.sin( lrFlakes * ycoords[x] ) - Math.sin( 3 * lrFlakes * ycoords[x] ) ) ) ) + scrollWidth ) + oPix;
		ycoords[x] += downSpeed;
	}
}

//DHTML handlers
function getRefToDivNest(divName) {
	if( document.layers ) { return document.layers[divName]; } //NS4
	if( document[divName] ) { return document[divName]; } //NS4 also
	if( document.getElementById ) { return document.getElementById(divName); } //DOM (IE5+, NS6+, Mozilla0.9+, Opera)
	if( document.all ) { return document.all[divName]; } //Proprietary DOM - IE4
	return false;
}

window.setInterval('flakeFall();',100);