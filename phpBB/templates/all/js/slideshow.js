function runSlideShow()
{
	rnd_nmb = (Math.round((Math.random() + 0.5) * 4) - 1);
	//rnd_nmb = 0;
	switch(rnd_nmb)
	{
		case 1:
			crossfade(document.getElementById('SlideShowPic'), ssfx.cache[j].src, FXDuration, Tit[j], 'lr');
			break;

		case 2:
			swapfade(document.getElementById('SlideShowPic'), ssfx.cache[j].src, FXDuration, Tit[j], 'lr');
			break;

		case 3:
			crosswipe(document.getElementById('SlideShowPic'), ssfx.cache[j].src, FXDuration, Tit[j], 'lr');
			break;

		default:
			if (document.all)
			{
				document.getElementById('SlideShowPic').style.filter="blendTrans(duration=FXDuration)";
				document.getElementById('SlideShowPic').filters.blendTrans.Apply();
			}
			document.getElementById('SlideShowPic').src = ssfx.cache[j].src;
			if (document.all)
			{
				document.getElementById('SlideShowPic').filters.blendTrans.Play();
			}
	}

	document.getElementById('PicHeader').innerHTML = Tit[j];
	document.getElementById('PicTitle').innerHTML = Tit[j];
	document.getElementById('PicDes').innerHTML = Des[j];

	j = j + 1;
	if (j > (p - 1))
	{
		j = 0;
	}

	t = setTimeout('runSlideShow()', (slideShowSpeed * 1000));
}

//swapfade setup function
function swapfade()
{
	//if the timer is not already going
	if(ssfx.clock == null)
	{
		//copy the image object
		ssfx.obj = arguments[0];

		//copy the image src argument
		ssfx.src = arguments[1];

		//store the supported form of opacity
		if(typeof ssfx.obj.style.opacity != 'undefined')
		{
			ssfx.type = 'w3c';
		}
		else if(typeof ssfx.obj.style.MozOpacity != 'undefined')
		{
			ssfx.type = 'moz';
		}
		else if(typeof ssfx.obj.style.KhtmlOpacity != 'undefined')
		{
			ssfx.type = 'khtml';
		}
		else if(typeof ssfx.obj.filters == 'object')
		{
			//weed out win/ie5.0 by testing the length of the filters collection (where filters is an object with no data)
			//then weed out mac/ie5 by testing first the existence of the alpha object (to prevent errors in win/ie5.0)
			//then the returned value type, which should be a number, but in mac/ie5 is an empty string
			ssfx.type = (ssfx.obj.filters.length > 0 && typeof ssfx.obj.filters.alpha == 'object' && typeof ssfx.obj.filters.alpha.opacity == 'number') ? 'ie' : 'none';
		}
		else
		{
			ssfx.type = 'none';
		}

		//change the image alt text if defined
		if(typeof arguments[3] != 'undefined' && arguments[3] != '')
		{
			ssfx.obj.alt = arguments[3];
		}

		//if any kind of opacity is supported
		if(ssfx.type != 'none')
		{
			//copy and convert fade duration argument
			//the duration specifies the whole transition
			//but the swapfade is two distinct transitions
			ssfx.length = parseInt(arguments[2], 10) * 500;

			//create fade resolution argument as 20 steps per transition
			//again, split for the two distrinct transitions
			ssfx.resolution = parseInt(arguments[2], 10) * 10;

			//start the timer
			ssfx.clock = setInterval('ssfx.swapfade()', ssfx.length/ssfx.resolution);
		}

		//otherwise if opacity is not supported
		else
		{
			//just do the image swap
			ssfx.obj.src = ssfx.src;
		}

	}
};

//swapfade timer function
ssfx.swapfade = function()
{
	//increase or reduce the counter on an exponential scale
	ssfx.count = (ssfx.fade) ? ssfx.count * 0.9 : (ssfx.count * (1/0.9));

	//if the counter has reached the bottom
	if(ssfx.count < (1 / ssfx.resolution))
	{
		//clear the timer
		clearInterval(ssfx.clock);
		ssfx.clock = null;

		//do the image swap
		ssfx.obj.src = ssfx.src;

		//reverse the fade direction flag
		ssfx.fade = false;

		//restart the timer
		ssfx.clock = setInterval('ssfx.swapfade()', ssfx.length/ssfx.resolution);

	}

	//if the counter has reached the top
	if(ssfx.count > (1 - (1 / ssfx.resolution)))
	{
		//clear the timer
		clearInterval(ssfx.clock);
		ssfx.clock = null;

		//reset the fade direction flag
		ssfx.fade = true;

		//reset the counter
		ssfx.count = 1;
	}

	//set new opacity value on element
	//using whatever method is supported
	switch(ssfx.type)
	{
		case 'ie' :
			ssfx.obj.filters.alpha.opacity = ssfx.count * 100;
			break;

		case 'khtml' :
			ssfx.obj.style.KhtmlOpacity = ssfx.count;
			break;

		case 'moz' :
			//restrict max opacity to prevent a visual popping effect in firefox
			ssfx.obj.style.MozOpacity = (ssfx.count == 1 ? 0.9999999 : ssfx.count);
			break;

		default :
			//restrict max opacity to prevent a visual popping effect in firefox
			ssfx.obj.style.opacity = (ssfx.count == 1 ? 0.9999999 : ssfx.count);
	}
};

//crossfade setup function
function crossfade()
{
	//if the timer is not already going
	if(ssfx.clock == null)
	{
		//copy the image object
		ssfx.obj = arguments[0];

		//copy the image src argument
		ssfx.src = arguments[1];

		//store the supported form of opacity
		if(typeof ssfx.obj.style.opacity != 'undefined')
		{
			ssfx.type = 'w3c';
		}
		else if(typeof ssfx.obj.style.MozOpacity != 'undefined')
		{
			ssfx.type = 'moz';
		}
		else if(typeof ssfx.obj.style.KhtmlOpacity != 'undefined')
		{
			ssfx.type = 'khtml';
		}
		else if(typeof ssfx.obj.filters == 'object')
		{
			//weed out win/ie5.0 by testing the length of the filters collection (where filters is an object with no data)
			//then weed out mac/ie5 by testing first the existence of the alpha object (to prevent errors in win/ie5.0)
			//then the returned value type, which should be a number, but in mac/ie5 is an empty string
			ssfx.type = (ssfx.obj.filters.length > 0 && typeof ssfx.obj.filters.alpha == 'object' && typeof ssfx.obj.filters.alpha.opacity == 'number') ? 'ie' : 'none';
		}
		else
		{
			ssfx.type = 'none';
		}

		//change the image alt text if defined
		if(typeof arguments[3] != 'undefined' && arguments[3] != '')
		{
			ssfx.obj.alt = arguments[3];
		}

		//if any kind of opacity is supported
		if(ssfx.type != 'none')
		{
			//create a new image object and append it to body
			//detecting support for namespaced element creation, in case we're in the XML DOM
			ssfx.newimg = document.getElementsByTagName('body')[0].appendChild((typeof document.createElementNS != 'undefined') ? document.createElementNS('http://www.w3.org/1999/xhtml', 'img') : document.createElement('img'));

			//set positioning classname
			ssfx.newimg.className = 'idupe';

			//set src to new image src
			ssfx.newimg.src = ssfx.src

			//move it to superimpose original image
			ssfx.newimg.style.left = ssfx.getRealPosition(ssfx.obj, 'x') + 'px';
			ssfx.newimg.style.top = ssfx.getRealPosition(ssfx.obj, 'y') + 'px';

			//copy and convert fade duration argument
			ssfx.length = parseInt(arguments[2], 10) * 1000;

			//create fade resolution argument as 20 steps per transition
			ssfx.resolution = parseInt(arguments[2], 10) * 20;

			//start the timer
			ssfx.clock = setInterval('ssfx.crossfade()', ssfx.length/ssfx.resolution);
		}

		//otherwise if opacity is not supported
		else
		{
			//just do the image swap
			ssfx.obj.src = ssfx.src;
		}

	}
};

//crossfade timer function
ssfx.crossfade = function()
{
	//decrease the counter on a linear scale
	ssfx.count -= (1 / ssfx.resolution);

	//if the counter has reached the bottom
	if(ssfx.count < (1 / ssfx.resolution))
	{
		//clear the timer
		clearInterval(ssfx.clock);
		ssfx.clock = null;

		//reset the counter
		ssfx.count = 1;

		//set the original image to the src of the new image
		ssfx.obj.src = ssfx.src;
	}

	//set new opacity value on both elements
	//using whatever method is supported
	switch(ssfx.type)
	{
		case 'ie' :
			ssfx.obj.filters.alpha.opacity = ssfx.count * 100;
			ssfx.newimg.filters.alpha.opacity = (1 - ssfx.count) * 100;
			break;

		case 'khtml' :
			ssfx.obj.style.KhtmlOpacity = ssfx.count;
			ssfx.newimg.style.KhtmlOpacity = (1 - ssfx.count);
			break;

		case 'moz' :
			//restrict max opacity to prevent a visual popping effect in firefox
			ssfx.obj.style.MozOpacity = (ssfx.count == 1 ? 0.9999999 : ssfx.count);
			ssfx.newimg.style.MozOpacity = (1 - ssfx.count);
			break;

		default :
			//restrict max opacity to prevent a visual popping effect in firefox
			ssfx.obj.style.opacity = (ssfx.count == 1 ? 0.9999999 : ssfx.count);
			ssfx.newimg.style.opacity = (1 - ssfx.count);
	}

	//now that we've gone through one fade iteration
	//we can show the image that's fading in
	ssfx.newimg.style.visibility = 'visible';

	//keep new image in position with original image
	//in case text size changes mid transition or something
	ssfx.newimg.style.left = ssfx.getRealPosition(ssfx.obj, 'x') + 'px';
	ssfx.newimg.style.top = ssfx.getRealPosition(ssfx.obj, 'y') + 'px';

	//if the counter is at the top, which is just after the timer has finished
	if(ssfx.count == 1)
	{
		//remove the duplicate image
		ssfx.newimg.parentNode.removeChild(ssfx.newimg);
	}
};

//crosswipe setup function
function crosswipe()
{
	//if the timer is not already going
	if(ssfx.clock == null)
	{
		//copy the image object
		ssfx.obj = arguments[0];

		//get its dimensions
		ssfx.size = { 'w' : ssfx.obj.width, 'h' : ssfx.obj.height };

		//copy the image src argument
		ssfx.src = arguments[1];

		//change the image alt text if defined
		if(typeof arguments[3] != 'undefined' && arguments[4] != '')
		{
			ssfx.obj.alt = arguments[3];
		}

		//if dynamic element creation is supported
		if(typeof document.createElementNS != 'undefined' || typeof document.createElement != 'undefined')
		{
			//create a new image object and append it to body
			//detecting support for namespaced element creation, in case we're in the XML DOM
			ssfx.newimg = document.getElementsByTagName('body')[0].appendChild((typeof document.createElementNS != 'undefined') ? document.createElementNS('http://www.w3.org/1999/xhtml', 'img') : document.createElement('img'));

			//set positioning classname
			ssfx.newimg.className = 'idupe';

			//set src to new image src
			ssfx.newimg.src = ssfx.src

			//move it to superimpose original image
			ssfx.newimg.style.left = ssfx.getRealPosition(ssfx.obj, 'x') + 'px';
			ssfx.newimg.style.top = ssfx.getRealPosition(ssfx.obj, 'y') + 'px';

			//set it to be completely hidden with clip
			ssfx.newimg.style.clip = 'rect(0, 0, 0, 0)';

			//show the image
			ssfx.newimg.style.visibility = 'visible';

			//copy and convert fade duration argument
			ssfx.length = parseInt(arguments[2], 10) * 1000;

			//create fade resolution argument as 20 steps per transition
			ssfx.resolution = parseInt(arguments[2], 10) * 20;

			//copy slide direction argument
			ssfx.dir = arguments[4];

			//start the timer
			ssfx.clock = setInterval('ssfx.crosswipe()', ssfx.length/ssfx.resolution);
		}

		//otherwise if dynamic element creation is not supported
		else
		{
			//just do the image swap
			ssfx.obj.src = ssfx.src;
		}

	}
};

//crosswipe timer function
ssfx.crosswipe = function()
{
	//decrease the counter on a linear scale
	ssfx.count -= (1 / ssfx.resolution);

	//if the counter has reached the bottom
	if(ssfx.count < (1 / ssfx.resolution))
	{
		//clear the timer
		clearInterval(ssfx.clock);
		ssfx.clock = null;

		//reset the counter
		ssfx.count = 1;

		//set the original image to the src of the new image
		ssfx.obj.src = ssfx.src;
	}

	//animate the clip of the new image
	//using the width and height properties we saved earlier
	ssfx.newimg.style.clip = 'rect('
		+ ( (/bt|bltr|brtl/.test(ssfx.dir)) ? (ssfx.size.h * ssfx.count) : (/che|cc/.test(ssfx.dir)) ? ((ssfx.size.h * ssfx.count) / 2) : (0) )
		+ 'px, '
		+ ( (/lr|tlbr|bltr/.test(ssfx.dir)) ? (ssfx.size.w - (ssfx.size.w * ssfx.count)) : (/cve|cc/.test(ssfx.dir)) ? (ssfx.size.w - ((ssfx.size.w * ssfx.count) / 2)) : (ssfx.size.w) )
		+ 'px, '
		+ ( (/tb|tlbr|trbl/.test(ssfx.dir)) ? (ssfx.size.h - (ssfx.size.h * ssfx.count)) : (/che|cc/.test(ssfx.dir)) ? (ssfx.size.h - ((ssfx.size.h * ssfx.count) / 2)) : (ssfx.size.h) )
		+ 'px, '
		+ ( (/lr|tlbr|bltr/.test(ssfx.dir)) ? (0) : (/tb|bt|che/.test(ssfx.dir)) ? (0) : (/cve|cc/.test(ssfx.dir)) ? ((ssfx.size.w * ssfx.count) / 2) : (ssfx.size.w * ssfx.count) )
		+ 'px)';

	//keep new image in position with original image
	//in case text size changes mid transition or something
	ssfx.newimg.style.left = ssfx.getRealPosition(ssfx.obj, 'x') + 'px';
	ssfx.newimg.style.top = ssfx.getRealPosition(ssfx.obj, 'y') + 'px';

	//if the counter is at the top, which is just after the timer has finished
	if(ssfx.count == 1)
	{
		//remove the duplicate image
		ssfx.newimg.parentNode.removeChild(ssfx.newimg);
	}
};

//get real position method
ssfx.getRealPosition = function()
{
	this.pos = (arguments[1] == 'x') ? arguments[0].offsetLeft : arguments[0].offsetTop;
	this.tmp = arguments[0].offsetParent;
	while(this.tmp != null)
	{
		this.pos += (arguments[1] == 'x') ? this.tmp.offsetLeft : this.tmp.offsetTop;
		this.tmp = this.tmp.offsetParent;
	}

	return this.pos;
};
