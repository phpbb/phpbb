/*
Copyright (c) 2007 John Dyer (http://johndyer.name)
MIT style license
*/

if (!window.Refresh) Refresh = {};
if (!Refresh.Web) Refresh.Web = {};

Refresh.Web.ColorValuePicker = Class.create();
Refresh.Web.ColorValuePicker.prototype = {
	initialize: function(id) {

		this.id = id;

		this.onValuesChanged = null;

		this._hueInput = $(this.id + '_Hue');
		this._valueInput = $(this.id + '_Brightness');
		this._saturationInput = $(this.id + '_Saturation');

		this._redInput = $(this.id + '_Red');
		this._greenInput = $(this.id + '_Green');
		this._blueInput = $(this.id + '_Blue');

		this._hexInput = $(this.id + '_Hex');

		// assign events

		// events
		this._event_onHsvKeyUp = this._onHsvKeyUp.bindAsEventListener(this);
		this._event_onHsvBlur = this._onHsvBlur.bindAsEventListener(this);
		this._event_onRgbKeyUp = this._onRgbKeyUp.bindAsEventListener(this);
		this._event_onRgbBlur = this._onRgbBlur.bindAsEventListener(this);
		this._event_onHexKeyUp = this._onHexKeyUp.bindAsEventListener(this);

		// HSB
		Event.observe( this._hueInput,'keyup', this._event_onHsvKeyUp);
		Event.observe( this._valueInput,'keyup',this._event_onHsvKeyUp);
		Event.observe( this._saturationInput,'keyup',this._event_onHsvKeyUp);
		Event.observe( this._hueInput,'blur', this._event_onHsvBlur);
		Event.observe( this._valueInput,'blur',this._event_onHsvBlur);
		Event.observe( this._saturationInput,'blur',this._event_onHsvBlur);

		// RGB
		Event.observe( this._redInput,'keyup', this._event_onRgbKeyUp);
		Event.observe( this._greenInput,'keyup', this._event_onRgbKeyUp);
		Event.observe( this._blueInput,'keyup', this._event_onRgbKeyUp);
		Event.observe( this._redInput,'blur', this._event_onRgbBlur);
		Event.observe( this._greenInput,'blur', this._event_onRgbBlur);
		Event.observe( this._blueInput,'blur', this._event_onRgbBlur);

		// HEX
		Event.observe( this._hexInput,'keyup', this._event_onHexKeyUp);

		this.color = new Refresh.Web.Color();

		// get an initial value
		if (this._hexInput.value != '')
			this.color.setHex(this._hexInput.value);


		// set the others based on initial value
		this._hexInput.value = this.color.hex;

		this._redInput.value = this.color.r;
		this._greenInput.value = this.color.g;
		this._blueInput.value = this.color.b;

		this._hueInput.value = this.color.h;
		this._saturationInput.value = this.color.s;
		this._valueInput.value = this.color.v;

	},
	_onHsvKeyUp: function(e) {
		if (e.target.value == '') return;
		this.validateHsv(e);
		this.setValuesFromHsv();
		if (this.onValuesChanged) this.onValuesChanged(this);
	},
	_onRgbKeyUp: function(e) {
		if (e.target.value == '') return;
		this.validateRgb(e);
		this.setValuesFromRgb();
		if (this.onValuesChanged) this.onValuesChanged(this);
	},
	_onHexKeyUp: function(e) {
		if (e.target.value == '') return;
		this.validateHex(e);
		this.setValuesFromHex();
		if (this.onValuesChanged) this.onValuesChanged(this);
	},
	_onHsvBlur: function(e) {
		if (e.target.value == '')
			this.setValuesFromRgb();
	},
	_onRgbBlur: function(e) {
		if (e.target.value == '')
			this.setValuesFromHsv();
	},
	HexBlur: function(e) {
		if (e.target.value == '')
			this.setValuesFromHsv();
	},
	validateRgb: function(e) {
		if (!this._keyNeedsValidation(e)) return e;
		this._redInput.value = this._setValueInRange(this._redInput.value,0,255);
		this._greenInput.value = this._setValueInRange(this._greenInput.value,0,255);
		this._blueInput.value = this._setValueInRange(this._blueInput.value,0,255);
	},
	validateHsv: function(e) {
		if (!this._keyNeedsValidation(e)) return e;
		this._hueInput.value = this._setValueInRange(this._hueInput.value,0,359);
		this._saturationInput.value = this._setValueInRange(this._saturationInput.value,0,100);
		this._valueInput.value = this._setValueInRange(this._valueInput.value,0,100);
	},
	validateHex: function(e) {
		if (!this._keyNeedsValidation(e)) return e;
		var hex = new String(this._hexInput.value).toUpperCase();
		hex = hex.replace(/[^A-F0-9]/g, '0');
		if (hex.length > 6) hex = hex.substring(0, 6);
		this._hexInput.value = hex;
	},
	_keyNeedsValidation: function(e) {

		if (e.keyCode == 9  || // TAB
			e.keyCode == 16  || // Shift
			e.keyCode == 38 || // Up arrow
			e.keyCode == 29 || // Right arrow
			e.keyCode == 40 || // Down arrow
			e.keyCode == 37    // Left arrow
		) return false;

		return true;
	},
	_setValueInRange: function(value,min,max) {
		if (value == '' || isNaN(value))
			return min;

		value = parseInt(value);
		if (value > max)
			return max;
		if (value < min)
			return min;

		return value;
	},
	setValuesFromRgb: function() {
		this.color.setRgb(this._redInput.value, this._greenInput.value, this._blueInput.value);
		this._hexInput.value = this.color.hex;
		this._hueInput.value = this.color.h;
		this._saturationInput.value = this.color.s;
		this._valueInput.value = this.color.v;
	},
	setValuesFromHsv: function() {
		this.color.setHsv(this._hueInput.value, this._saturationInput.value, this._valueInput.value);

		this._hexInput.value = this.color.hex;
		this._redInput.value = this.color.r;
		this._greenInput.value = this.color.g;
		this._blueInput.value = this.color.b;
	},
	setValuesFromHex: function() {
		this.color.setHex(this._hexInput.value);

		this._redInput.value = this.color.r;
		this._greenInput.value = this.color.g;
		this._blueInput.value = this.color.b;

		this._hueInput.value = this.color.h;
		this._saturationInput.value = this.color.s;
		this._valueInput.value = this.color.v;
	}
};
