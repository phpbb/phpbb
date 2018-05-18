/*
Copyright (c) 2007 John Dyer (http://johndyer.name)
MIT style license
*/

if (!window.Refresh) Refresh = {};
if (!Refresh.Web) Refresh.Web = {};

Refresh.Web.SlidersList = [];

Refresh.Web.DefaultSliderSettings = {
	xMinValue: 0,
	xMaxValue: 100,
	yMinValue: 0,
	yMaxValue: 100,
	arrowImage: 'images/colorpicker/rangearrows.gif'
}


Refresh.Web.Slider = Class.create();
Refresh.Web.Slider.prototype = {
	_bar: null,
	_arrow: null,

	initialize: function(id, obj_id, settings) {

		this.id = id;
		this.settings = Object.extend(Object.extend({},Refresh.Web.DefaultSliderSettings), settings || {});

		this.xValue = 0;
		this.yValue = 0;

		// hook up controls
		this._bar = $(this.id);

		// build controls
		this._arrow = document.createElement('img');
		this._arrow.id = obj_id;
		this._arrow.border = 0;
		this._arrow.src = this.settings.arrowImage;
		this._arrow.margin = 0;
		this._arrow.padding = 0;
		this._arrow.style.position = 'absolute';
		this._arrow.style.top = '0px';
		this._arrow.style.left = '0px';
		document.body.appendChild(this._arrow);

		// attach 'this' to html objects
		var slider = this;

		this.setPositioningVariables();

		this._event_docMouseMove = this._docMouseMove.bindAsEventListener(this);
		this._event_docMouseUp = this._docMouseUp.bindAsEventListener(this);

		Event.observe( this._bar, 'mousedown', this._bar_mouseDown.bindAsEventListener(this));
		Event.observe( this._arrow, 'mousedown', this._arrow_mouseDown.bindAsEventListener(this));

		// set initial position
		this.setArrowPositionFromValues();

		// fire events
		if(this.onValuesChanged)
			this.onValuesChanged(this);

		// final setup
		Refresh.Web.SlidersList.push(this);
	},


	setPositioningVariables: function() {
		// calculate sizes and ranges
		// BAR

		this._barWidth = this._bar.getWidth();
		this._barHeight = this._bar.getHeight();

		var pos = this._bar.cumulativeOffset();
		this._barTop = pos.top;
		this._barLeft = pos.left;

		this._barBottom = this._barTop + this._barHeight;
		this._barRight = this._barLeft + this._barWidth;

		// ARROW
		this._arrow = $(this._arrow);
		this._arrowWidth = this._arrow.getWidth();
		this._arrowHeight = this._arrow.getHeight();

		// MIN & MAX
		this.MinX = this._barLeft;
		this.MinY = this._barTop;

		this.MaxX = this._barRight;
		this.MinY = this._barBottom;
	},

	setArrowPositionFromValues: function(e) {
		this.setPositioningVariables();

		// sets the arrow position from XValue and YValue properties

		var arrowOffsetX = 0;
		var arrowOffsetY = 0;

		// X Value/Position
		if (this.settings.xMinValue != this.settings.xMaxValue) {

			if (this.xValue == this.settings.xMinValue) {
				arrowOffsetX = 0;
			} else if (this.xValue == this.settings.xMaxValue) {
				arrowOffsetX = this._barWidth-1;
			} else {

				var xMax = this.settings.xMaxValue;
				if (this.settings.xMinValue < 1)  {
					xMax = xMax + Math.abs(this.settings.xMinValue) + 1;
				}
				var xValue = this.xValue;

				if (this.xValue < 1) xValue = xValue + 1;

				arrowOffsetX = xValue / xMax * this._barWidth;

				if (parseInt(arrowOffsetX) == (xMax-1))
					arrowOffsetX=xMax;
				else
					arrowOffsetX=parseInt(arrowOffsetX);

				// shift back to normal values
				if (this.settings.xMinValue < 1)  {
					arrowOffsetX = arrowOffsetX - Math.abs(this.settings.xMinValue) - 1;
				}
			}
		}

		// X Value/Position
		if (this.settings.yMinValue != this.settings.yMaxValue) {

			if (this.yValue == this.settings.yMinValue) {
				arrowOffsetY = 0;
			} else if (this.yValue == this.settings.yMaxValue) {
				arrowOffsetY = this._barHeight-1;
			} else {

				var yMax = this.settings.yMaxValue;
				if (this.settings.yMinValue < 1)  {
					yMax = yMax + Math.abs(this.settings.yMinValue) + 1;
				}

				var yValue = this.yValue;

				if (this.yValue < 1) yValue = yValue + 1;

				var arrowOffsetY = yValue / yMax * this._barHeight;

				if (parseInt(arrowOffsetY) == (yMax-1))
					arrowOffsetY=yMax;
				else
					arrowOffsetY=parseInt(arrowOffsetY);

				if (this.settings.yMinValue < 1)  {
					arrowOffsetY = arrowOffsetY - Math.abs(this.settings.yMinValue) - 1;
				}
			}
		}

		this._setArrowPosition(arrowOffsetX, arrowOffsetY);

	},
	_setArrowPosition: function(offsetX, offsetY) {


		// validate
		if (offsetX < 0) offsetX = 0
		if (offsetX > this._barWidth) offsetX = this._barWidth;
		if (offsetY < 0) offsetY = 0
		if (offsetY > this._barHeight) offsetY = this._barHeight;

		var posX = this._barLeft + offsetX;
		var posY = this._barTop + offsetY;

		// check if the arrow is bigger than the bar area
		if (this._arrowWidth > this._barWidth) {
			posX = posX - (this._arrowWidth/2 - this._barWidth/2);
		} else {
			posX = posX - parseInt(this._arrowWidth/2);
		}
		if (this._arrowHeight > this._barHeight) {
			posY = posY - (this._arrowHeight/2 - this._barHeight/2);
		} else {
			posY = posY - parseInt(this._arrowHeight/2);
		}
		this._arrow.style.left = posX + 'px';
		this._arrow.style.top = posY + 'px';
	},
	_bar_mouseDown: function(e) {
		this._mouseDown(e);
	},

	_arrow_mouseDown: function(e) {
		this._mouseDown(e);
	},

	_mouseDown: function(e) {
		Refresh.Web.ActiveSlider = this;

		this.setValuesFromMousePosition(e);

		Event.observe(document, 'mousemove', this._event_docMouseMove);
		Event.observe(document, 'mouseup', this._event_docMouseUp);

		Event.stop(e);
	},

	_docMouseMove: function(e) {

		this.setValuesFromMousePosition(e);

		Event.stop(e);
	},

	_docMouseUp: function(e) {
		Event.stopObserving( document, 'mouseup', this._event_docMouseUp);
		Event.stopObserving( document, 'mousemove', this._event_docMouseMove);
		Event.stop(e);
	},

	setValuesFromMousePosition: function(e) {
		//this.setPositioningVariables();


		var mouse = Event.pointer(e);

		var relativeX = 0;
		var relativeY = 0;

		// mouse relative to object's top left
		if (mouse.x < this._barLeft)
			relativeX = 0;
		else if (mouse.x > this._barRight)
			relativeX = this._barWidth;
		else
			relativeX = mouse.x - this._barLeft + 1;

		if (mouse.y < this._barTop)
			relativeY = 0;
		else if (mouse.y > this._barBottom)
			relativeY = this._barHeight;
		else
			relativeY = mouse.y - this._barTop + 1;


		var newXValue = parseInt(relativeX / this._barWidth * this.settings.xMaxValue);
		var newYValue = parseInt(relativeY / this._barHeight * this.settings.yMaxValue);

		// set values
		this.xValue = newXValue;
		this.yValue = newYValue;

		// position arrow
		if (this.settings.xMaxValue == this.settings.xMinValue)
			relativeX = 0;
		if (this.settings.yMaxValue == this.settings.yMinValue)
			relativeY = 0;
		this._setArrowPosition(relativeX, relativeY);

		// fire events
		if(this.onValuesChanged)
			this.onValuesChanged(this);
	}

}
