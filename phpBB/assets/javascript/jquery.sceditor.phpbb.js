/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId])
/******/ 			return installedModules[moduleId].exports;
/******/
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			exports: {},
/******/ 			id: moduleId,
/******/ 			loaded: false
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.loaded = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(0);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ function(module, exports, __webpack_require__) {

	var __WEBPACK_AMD_DEFINE_RESULT__;/**
	 * SCEditor
	 * http://www.sceditor.com/
	 *
	 * Copyright (C) 2014, Sam Clarke (samclarke.com)
	 *
	 * SCEditor is licensed under the MIT license:
	 *	http://www.opensource.org/licenses/mit-license.php
	 *
	 * @fileoverview SCEditor - A lightweight WYSIWYG BBCode and HTML editor
	 * @author Sam Clarke
	 * @requires jQuery
	 */
	!(__WEBPACK_AMD_DEFINE_RESULT__ = function (require) {
		'use strict';

		var $             = __webpack_require__(1);
		var SCEditor      = __webpack_require__(2);
		var PluginManager = __webpack_require__(3);
		var browser       = __webpack_require__(4);
		var escape        = __webpack_require__(5);


		// For backwards compatibility
		$.sceditor = SCEditor;

		SCEditor.commands       = __webpack_require__(6);
		SCEditor.defaultOptions = __webpack_require__(7);
		SCEditor.RangeHelper    = __webpack_require__(8);
		SCEditor.dom            = __webpack_require__(9);

		SCEditor.ie                 = browser.ie;
		SCEditor.ios                = browser.ios;
		SCEditor.isWysiwygSupported = browser.isWysiwygSupported;

		SCEditor.regexEscape     = escape.regex;
		SCEditor.escapeEntities  = escape.entities;
		SCEditor.escapeUriScheme = escape.uriScheme;

		SCEditor.PluginManager = PluginManager;
		SCEditor.plugins       = PluginManager.plugins;


		/**
		 * Creates an instance of sceditor on all textareas
		 * matched by the jQuery selector.
		 *
		 * If options is set to "state" it will return bool value
		 * indicating if the editor has been initilised on the
		 * matched textarea(s). If there is only one textarea
		 * it will return the bool value for that textarea.
		 * If more than one textarea is matched it will
		 * return an array of bool values for each textarea.
		 *
		 * If options is set to "instance" it will return the
		 * current editor instance for the textarea(s). Like the
		 * state option, if only one textarea is matched this will
		 * return just the instance for that textarea. If more than
		 * one textarea is matched it will return an array of
		 * instances each textarea.
		 *
		 * @param  {Object|String} options Should either be an Object of options or
		 *                                 the strings "state" or "instance"
		 * @return {this|Array|jQuery.sceditor|Bool}
		 */
		$.fn.sceditor = function (options) {
			var	$this, instance,
				ret = [];

			options = options || {};

			if (!options.runWithoutWysiwygSupport && !browser.isWysiwygSupported) {
				return;
			}

			this.each(function () {
				$this = this.jquery ? this : $(this);
				instance = $this.data('sceditor');

				// Don't allow the editor to be initilised on it's own source editor
				if ($this.parents('.sceditor-container').length > 0) {
					return;
				}

				// Add state of instance to ret if that is what options is set to
				if (options === 'state') {
					ret.push(!!instance);
				} else if (options === 'instance') {
					ret.push(instance);
				} else if (!instance) {
					/*jshint -W031*/
					(new SCEditor(this, options));
				}
			});

			// If nothing in the ret array then must be init so return this
			if (!ret.length) {
				return this;
			}

			return ret.length === 1 ? ret[0] : $(ret);
		};
	}.call(exports, __webpack_require__, exports, module), __WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ },
/* 1 */
/***/ function(module, exports, __webpack_require__) {

	module.exports = jQuery;

/***/ },
/* 2 */
/***/ function(module, exports, __webpack_require__) {

	var __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_RESULT__ = function (require) {
		'use strict';

		var $             = __webpack_require__(1);
		var PluginManager = __webpack_require__(3);
		var RangeHelper   = __webpack_require__(8);
		var dom           = __webpack_require__(9);
		var escape        = __webpack_require__(5);
		var browser       = __webpack_require__(4);
		var _tmpl         = __webpack_require__(10);

		var globalWin  = window;
		var globalDoc  = document;
		var $globalWin = $(globalWin);
		var $globalDoc = $(globalDoc);

		var IE_VER = browser.ie;

		// In IE < 11 a BR at the end of a block level element
		// causes a line break. In all other browsers it's collapsed.
		var IE_BR_FIX = IE_VER && IE_VER < 11;


		/**
		 * SCEditor - A lightweight WYSIWYG editor
		 *
		 * @param {Element} el The textarea to be converted
		 * @return {Object} options
		 * @class sceditor
		 * @name jQuery.sceditor
		 */
		var SCEditor = function (el, options) {
			/**
			 * Alias of this
			 *
			 * @private
			 */
			var base = this;

			/**
			 * The textarea element being replaced
			 *
			 * @private
			 */
			var original  = el.get ? el.get(0) : el;
			var $original = $(original);

			/**
			 * The div which contains the editor and toolbar
			 *
			 * @private
			 */
			var $editorContainer;

			/**
			 * The editors toolbar
			 *
			 * @private
			 */
			var $toolbar;

			/**
			 * The editors iframe which should be in design mode
			 *
			 * @private
			 */
			var $wysiwygEditor;
			var wysiwygEditor;

			/**
			 * The WYSIWYG editors body element
			 *
			 * @private
			 */
			var $wysiwygBody;

			/**
			 * The WYSIWYG editors document
			 *
			 * @private
			 */
			var $wysiwygDoc;

			/**
			 * The editors textarea for viewing source
			 *
			 * @private
			 */
			var $sourceEditor;
			var sourceEditor;

			/**
			 * The current dropdown
			 *
			 * @private
			 */
			var $dropdown;

			/**
			 * Store the last cursor position. Needed for IE because it forgets
			 *
			 * @private
			 */
			var lastRange;

			/**
			 * The editors locale
			 *
			 * @private
			 */
			var locale;

			/**
			 * Stores a cache of preloaded images
			 *
			 * @private
			 * @type {Array}
			 */
			var preLoadCache = [];

			/**
			 * The editors rangeHelper instance
			 *
			 * @type {jQuery.sceditor.rangeHelper}
			 * @private
			 */
			var rangeHelper;

			/**
			 * Tags which require the new line fix
			 *
			 * @type {Array}
			 * @private
			 */
			var requireNewLineFix = [];

			/**
			 * An array of button state handlers
			 *
			 * @type {Array}
			 * @private
			 */
			var btnStateHandlers = [];

			/**
			 * Plugin manager instance
			 *
			 * @type {jQuery.sceditor.PluginManager}
			 * @private
			 */
			var pluginManager;

			/**
			 * The current node containing the selection/caret
			 *
			 * @type {Node}
			 * @private
			 */
			var currentNode;

			/**
			 * The first block level parent of the current node
			 *
			 * @type {node}
			 * @private
			 */
			var currentBlockNode;

			/**
			 * The current node selection/caret
			 *
			 * @type {Object}
			 * @private
			 */
			var currentSelection;

			/**
			 * Used to make sure only 1 selection changed
			 * check is called every 100ms.
			 *
			 * Helps improve performance as it is checked a lot.
			 *
			 * @type {Boolean}
			 * @private
			 */
			var isSelectionCheckPending;

			/**
			 * If content is required (equivalent to the HTML5 required attribute)
			 *
			 * @type {Boolean}
			 * @private
			 */
			var isRequired;

			/**
			 * The inline CSS style element. Will be undefined
			 * until css() is called for the first time.
			 *
			 * @type {HTMLElement}
			 * @private
			 */
			var inlineCss;

			/**
			 * Object containing a list of shortcut handlers
			 *
			 * @type {Object}
			 * @private
			 */
			var shortcutHandlers = {};

			/**
			 * An array of all the current emoticons.
			 *
			 * Only used or populated when emoticonsCompat is enabled.
			 *
			 * @type {Array}
			 * @private
			 */
			var currentEmoticons = [];

			/**
			 * Cache of the current toolbar buttons
			 *
			 * @type {Object}
			 * @private
			 */
			var toolbarButtons = {};

			/**
			 * If the current autoUpdate action is canceled.
			 *
			 * @type {Boolean}
			 * @private
			 */
			var autoUpdateCanceled;

			/**
			 * Private functions
			 * @private
			 */
			var	init,
				replaceEmoticons,
				handleCommand,
				saveRange,
				initEditor,
				initPlugins,
				initLocale,
				initToolBar,
				initOptions,
				initEvents,
				initCommands,
				initResize,
				initEmoticons,
				getWysiwygDoc,
				handlePasteEvt,
				handlePasteData,
				handleKeyDown,
				handleBackSpace,
				handleKeyPress,
				handleFormReset,
				handleMouseDown,
				handleEvent,
				handleDocumentClick,
				handleWindowResize,
				updateToolBar,
				updateActiveButtons,
				sourceEditorSelectedText,
				appendNewLine,
				checkSelectionChanged,
				checkNodeChanged,
				autofocus,
				emoticonsKeyPress,
				emoticonsCheckWhitespace,
				currentStyledBlockNode,
				triggerValueChanged,
				valueChangedBlur,
				valueChangedKeyUp,
				autoUpdate;

			/**
			 * All the commands supported by the editor
			 * @name commands
			 * @memberOf jQuery.sceditor.prototype
			 */
			base.commands = $.extend(
				true,
				{},
				(options.commands || SCEditor.commands)
			);

			/**
			 * Options for this editor instance
			 * @name opts
			 * @memberOf jQuery.sceditor.prototype
			 */
			base.opts = options = $.extend({}, SCEditor.defaultOptions, options);


			/**
			 * Creates the editor iframe and textarea
			 * @private
			 */
			init = function () {
				$original.data('sceditor', base);

				// Clone any objects in options
				$.each(options, function (key, val) {
					if ($.isPlainObject(val)) {
						options[key] = $.extend(true, {}, val);
					}
				});

				// Load locale
				if (options.locale && options.locale !== 'en') {
					initLocale();
				}

				$editorContainer = $('<div class="sceditor-container" />')
					.insertAfter($original)
					.css('z-index', options.zIndex);

				// Add IE version to the container to allow IE specific CSS
				// fixes without using CSS hacks or conditional comments
				if (IE_VER) {
					$editorContainer.addClass('ie ie' + IE_VER);
				}

				isRequired = !!$original.attr('required');
				$original.removeAttr('required');

				// create the editor
				initPlugins();
				initEmoticons();
				initToolBar();
				initEditor(!!options.startInSourceMode);
				initCommands();
				initOptions();
				initEvents();

				// force into source mode if is a browser that can't handle
				// full editing
				if (!browser.isWysiwygSupported) {
					base.toggleSourceMode();
				}

				updateActiveButtons();

				var loaded = function () {
					$globalWin.unbind('load', loaded);

					if (options.autofocus) {
						autofocus();
					}

					if (options.autoExpand) {
						base.expandToContent();
					}

					// Page width might have changed after CSS is loaded so
					// call handleWindowResize to update any % based dimensions
					handleWindowResize();

					pluginManager.call('ready');
				};
				$globalWin.load(loaded);
				if (globalDoc.readyState && globalDoc.readyState === 'complete') {
					loaded();
				}
			};

			initPlugins = function () {
				var plugins   = options.plugins;

				plugins       = plugins ? plugins.toString().split(',') : [];
				pluginManager = new PluginManager(base);

				$.each(plugins, function (idx, plugin) {
					pluginManager.register($.trim(plugin));
				});
			};

			/**
			 * Init the locale variable with the specified locale if possible
			 * @private
			 * @return void
			 */
			initLocale = function () {
				var lang;

				locale = SCEditor.locale[options.locale];

				if (!locale) {
					lang   = options.locale.split('-');
					locale = SCEditor.locale[lang[0]];
				}

				// Locale DateTime format overrides any specified in the options
				if (locale && locale.dateFormat) {
					options.dateFormat = locale.dateFormat;
				}
			};

			/**
			 * Creates the editor iframe and textarea
			 * @param {boolean} startInSourceMode Force loading the editor in this
			 *																	mode
			 * @private
			 */
			initEditor = function (startInSourceMode) {
				var doc, tabIndex;

				$sourceEditor  = $('<textarea></textarea>');
				$wysiwygEditor = $(
					'<iframe frameborder="0" allowfullscreen="true"></iframe>'
				);

				/* This needs to be done right after they are created because,
				 * for any reason, the user may not want the value to be tinkered
				 * by any filters.
				 */
				if (startInSourceMode) {
					$editorContainer.addClass('sourceMode');
					$wysiwygEditor.hide();
				} else {
					$editorContainer.addClass('wysiwygMode');
					$sourceEditor.hide();
				}

				if (!options.spellcheck) {
					$sourceEditor.attr('spellcheck', 'false');
				}

				/*jshint scripturl: true*/
				if (globalWin.location.protocol === 'https:') {
					$wysiwygEditor.attr('src', 'javascript:false');
				}

				// Add the editor to the container
				$editorContainer.append($wysiwygEditor).append($sourceEditor);
				wysiwygEditor = $wysiwygEditor[0];
				sourceEditor  = $sourceEditor[0];

				base.dimensions(
					options.width || $original.width(),
					options.height || $original.height()
				);

				doc = getWysiwygDoc();
				doc.open();
				doc.write(_tmpl('html', {
					// Add IE version class to the HTML element so can apply
					// conditional styling without CSS hacks
					attrs: IE_VER ? ' class="ie ie"' + IE_VER : '',
					spellcheck: options.spellcheck ? '' : 'spellcheck="false"',
					charset: options.charset,
					style: options.style
				}));
				doc.close();

				$wysiwygDoc  = $(doc);
				$wysiwygBody = $(doc.body);

				base.readOnly(!!options.readOnly);

				// iframe overflow fix for iOS, also fixes an IE issue with the
				// editor not getting focus when clicking inside
				if (browser.ios || IE_VER) {
					$wysiwygBody.height('100%');

					if (!IE_VER) {
						$wysiwygBody.bind('touchend', base.focus);
					}
				}

				tabIndex = $original.attr('tabindex');
				$sourceEditor.attr('tabindex', tabIndex);
				$wysiwygEditor.attr('tabindex', tabIndex);

				rangeHelper = new RangeHelper(wysiwygEditor.contentWindow);

				// load any textarea value into the editor
				base.val($original.hide().val());
			};

			/**
			 * Initialises options
			 * @private
			 */
			initOptions = function () {
				// auto-update original textbox on blur if option set to true
				if (options.autoUpdate) {
					$wysiwygBody.bind('blur', autoUpdate);
					$sourceEditor.bind('blur', autoUpdate);
				}

				if (options.rtl === null) {
					options.rtl = $sourceEditor.css('direction') === 'rtl';
				}

				base.rtl(!!options.rtl);

				if (options.autoExpand) {
					$wysiwygDoc.bind('keyup', base.expandToContent);
				}

				if (options.resizeEnabled) {
					initResize();
				}

				$editorContainer.attr('id', options.id);
				base.emoticons(options.emoticonsEnabled);
			};

			/**
			 * Initialises events
			 * @private
			 */
			initEvents = function () {
				var CHECK_SELECTION_EVENTS = IE_VER ?
					'selectionchange' :
					'keyup focus blur contextmenu mouseup touchend click';

				var EVENTS_TO_FORWARD = 'keydown keyup keypress ' +
					'focus blur contextmenu';

				$globalDoc.click(handleDocumentClick);

				$(original.form)
					.bind('reset', handleFormReset)
					.submit(base.updateOriginal);

				$globalWin.bind('resize orientationChanged', handleWindowResize);

				$wysiwygBody
					.keypress(handleKeyPress)
					.keydown(handleKeyDown)
					.keydown(handleBackSpace)
					.keyup(appendNewLine)
					.blur(valueChangedBlur)
					.keyup(valueChangedKeyUp)
					.bind('paste', handlePasteEvt)
					.bind(CHECK_SELECTION_EVENTS, checkSelectionChanged)
					.bind(EVENTS_TO_FORWARD, handleEvent);

				if (options.emoticonsCompat && globalWin.getSelection) {
					$wysiwygBody.keyup(emoticonsCheckWhitespace);
				}

				$sourceEditor
					.blur(valueChangedBlur)
					.keyup(valueChangedKeyUp)
					.keydown(handleKeyDown)
					.bind(EVENTS_TO_FORWARD, handleEvent);

				$wysiwygDoc
					.mousedown(handleMouseDown)
					.blur(valueChangedBlur)
					.bind(CHECK_SELECTION_EVENTS, checkSelectionChanged)
					.bind('beforedeactivate keyup mouseup', saveRange)
					.keyup(appendNewLine)
					.focus(function () {
						lastRange = null;
					});

				$editorContainer
					.bind('selectionchanged', checkNodeChanged)
					.bind('selectionchanged', updateActiveButtons)
					.bind('selectionchanged valuechanged nodechanged', handleEvent);
			};

			/**
			 * Creates the toolbar and appends it to the container
			 * @private
			 */
			initToolBar = function () {
				var	$group,
					commands = base.commands,
					exclude  = (options.toolbarExclude || '').split(','),
					groups   = options.toolbar.split('|');

				$toolbar = $('<div class="sceditor-toolbar" unselectable="on" />');

				$.each(groups, function (idx, group) {
					$group  = $('<div class="sceditor-group" />');

					$.each(group.split(','), function (idx, commandName) {
						var	$button, shortcut,
							command  = commands[commandName];

						// The commandName must be a valid command and not excluded
						if (!command || $.inArray(commandName, exclude) > -1) {
							return;
						}

						shortcut = command.shortcut;
						$button  = _tmpl('toolbarButton', {
							name: commandName,
							dispName: base._(command.name ||
									command.tooltip || commandName)
						}, true);

						$button
							.data('sceditor-txtmode', !!command.txtExec)
							.data('sceditor-wysiwygmode', !!command.exec)
							.toggleClass('disabled', !command.exec)
							.mousedown(function () {
								// IE < 8 supports unselectable attribute
								// so don't need this
								if (!IE_VER || IE_VER < 9) {
									autoUpdateCanceled = true;
								}
							})
							.click(function () {
								var $this = $(this);

								if (!$this.hasClass('disabled')) {
									handleCommand($this, command);
								}

								updateActiveButtons();
								return false;
							});

						if (command.tooltip) {
							$button.attr(
								'title',
								base._(command.tooltip) +
									(shortcut ? ' (' + shortcut + ')' : '')
							);
						}

						if (shortcut) {
							base.addShortcut(shortcut, commandName);
						}

						if (command.state) {
							btnStateHandlers.push({
								name: commandName,
								state: command.state
							});
						// exec string commands can be passed to queryCommandState
						} else if (typeof command.exec === 'string') {
							btnStateHandlers.push({
								name: commandName,
								state: command.exec
							});
						}

						$group.append($button);
						toolbarButtons[commandName] = $button;
					});

					// Exclude empty groups
					if ($group[0].firstChild) {
						$toolbar.append($group);
					}
				});

				// Append the toolbar to the toolbarContainer option if given
				$(options.toolbarContainer || $editorContainer).append($toolbar);
			};

			/**
			 * Creates an array of all the key press functions
			 * like emoticons, ect.
			 * @private
			 */
			initCommands = function () {

				var bodyCodeInputHandles = [];
				var sourceCodeInputHandles = [];

				$.each(base.commands, function (name, cmd) {
					if (cmd.forceNewLineAfter && $.isArray(cmd.forceNewLineAfter)) {
						requireNewLineFix = $.merge(
							requireNewLineFix,
							cmd.forceNewLineAfter
						);
					}
					if (cmd.codeInputModeInside) {
						bodyCodeInputHandles.push(cmd.codeInputModeInside);
					}
					if (cmd.txtCodeInputModeInside) {
						sourceCodeInputHandles.push(cmd.txtCodeInputModeInside);
					}
				});
				if (bodyCodeInputHandles.length > 0) {
					addBodyCodeInputHandle(bodyCodeInputHandles);
				}
				if (sourceCodeInputHandles.length > 0) {
					addSourceCodeInputHandle(sourceCodeInputHandles);
				}

				appendNewLine();
			};

			/**
			 * Creates the resizer.
			 * @private
			 */
			initResize = function () {
				var	minHeight, maxHeight, minWidth, maxWidth,
					mouseMoveFunc, mouseUpFunc,
					$grip       = $('<div class="sceditor-grip" />'),
					// Cover is used to cover the editor iframe so document
					// still gets mouse move events
					$cover      = $('<div class="sceditor-resize-cover" />'),
					moveEvents  = 'touchmove mousemove',
					endEvents   = 'touchcancel touchend mouseup',
					startX      = 0,
					startY      = 0,
					newX        = 0,
					newY        = 0,
					startWidth  = 0,
					startHeight = 0,
					origWidth   = $editorContainer.width(),
					origHeight  = $editorContainer.height(),
					isDragging  = false,
					rtl         = base.rtl();

				minHeight = options.resizeMinHeight || origHeight / 1.5;
				maxHeight = options.resizeMaxHeight || origHeight * 2.5;
				minWidth  = options.resizeMinWidth  || origWidth  / 1.25;
				maxWidth  = options.resizeMaxWidth  || origWidth  * 1.25;

				mouseMoveFunc = function (e) {
					// iOS uses window.event
					if (e.type === 'touchmove') {
						e    = globalWin.event;
						newX = e.changedTouches[0].pageX;
						newY = e.changedTouches[0].pageY;
					} else {
						newX = e.pageX;
						newY = e.pageY;
					}

					var	newHeight = startHeight + (newY - startY),
						newWidth  = rtl ?
							startWidth - (newX - startX) :
							startWidth + (newX - startX);

					if (maxWidth > 0 && newWidth > maxWidth) {
						newWidth = maxWidth;
					}
					if (minWidth > 0 && newWidth < minWidth) {
						newWidth = minWidth;
					}
					if (!options.resizeWidth) {
						newWidth = false;
					}

					if (maxHeight > 0 && newHeight > maxHeight) {
						newHeight = maxHeight;
					}
					if (minHeight > 0 && newHeight < minHeight) {
						newHeight = minHeight;
					}
					if (!options.resizeHeight) {
						newHeight = false;
					}

					if (newWidth || newHeight) {
						base.dimensions(newWidth, newHeight);

						// The resize cover will not fill the container
						// in IE6 unless a height is specified.
						if (IE_VER < 7) {
							$editorContainer.height(newHeight);
						}
					}

					e.preventDefault();
				};

				mouseUpFunc = function (e) {
					if (!isDragging) {
						return;
					}

					isDragging = false;

					$cover.hide();
					$editorContainer.removeClass('resizing').height('auto');
					$globalDoc.unbind(moveEvents, mouseMoveFunc);
					$globalDoc.unbind(endEvents, mouseUpFunc);

					e.preventDefault();
				};

				$editorContainer.append($grip);
				$editorContainer.append($cover.hide());

				$grip.bind('touchstart mousedown', function (e) {
					// iOS uses window.event
					if (e.type === 'touchstart') {
						e      = globalWin.event;
						startX = e.touches[0].pageX;
						startY = e.touches[0].pageY;
					} else {
						startX = e.pageX;
						startY = e.pageY;
					}

					startWidth  = $editorContainer.width();
					startHeight = $editorContainer.height();
					isDragging  = true;

					$editorContainer.addClass('resizing');
					$cover.show();
					$globalDoc.bind(moveEvents, mouseMoveFunc);
					$globalDoc.bind(endEvents, mouseUpFunc);

					// The resize cover will not fill the container in
					// IE6 unless a height is specified.
					if (IE_VER < 7) {
						$editorContainer.height(startHeight);
					}

					e.preventDefault();
				});
			};

			/**
			 * Prefixes and preloads the emoticon images
			 * @private
			 */
			initEmoticons = function () {
				var	emoticon,
					emoticons = options.emoticons,
					root      = options.emoticonsRoot;

				if (!$.isPlainObject(emoticons) || !options.emoticonsEnabled) {
					return;
				}

				$.each(emoticons, function (idx, val) {
					$.each(val, function (key, url) {
						// Prefix emoticon root to emoticon urls
						if (root) {
							url = {
								url: root + (url.url || url),
								tooltip: url.tooltip || key
							};

							emoticons[idx][key] = url;
						}

						// Preload the emoticon
						emoticon     = globalDoc.createElement('img');
						emoticon.src = url.url || url;
						preLoadCache.push(emoticon);
					});
				});
			};

			/**
			 * Autofocus the editor
			 * @private
			 */
			autofocus = function () {
				var	range, txtPos,
					doc      = $wysiwygDoc[0],
					body     = $wysiwygBody[0],
					node     = body.firstChild,
					focusEnd = !!options.autofocusEnd;

				// Can't focus invisible elements
				if (!$editorContainer.is(':visible')) {
					return;
				}

				if (base.sourceMode()) {
					txtPos = focusEnd ? sourceEditor.value.length : 0;

					if (sourceEditor.setSelectionRange) {
						sourceEditor.setSelectionRange(txtPos, txtPos);
					} else {
						range = sourceEditor.createTextRange();
						range.moveEnd('character', txtPos);
						range.collapse(false);
						range.select();
					}

					return;
				}

				dom.removeWhiteSpace(body);

				if (focusEnd) {
					if (!(node = body.lastChild)) {
						node = doc.createElement('p');
						$wysiwygBody.append(node);
					}

					while (node.lastChild) {
						node = node.lastChild;

						// IE < 11 should place the cursor after the <br> as
						// it will show it as a newline. IE >= 11 and all
						// other browsers should place the cursor before.
						if (!IE_BR_FIX && $(node).is('br') &&
							node.previousSibling) {
							node = node.previousSibling;
						}
					}
				}

				if (doc.createRange) {
					range = doc.createRange();

					if (!dom.canHaveChildren(node)) {
						range.setStartBefore(node);

						if (focusEnd) {
							range.setStartAfter(node);
						}
					} else {
						range.selectNodeContents(node);
					}
				} else {
					range = body.createTextRange();
					range.moveToElementText(node.nodeType !== 3 ?
						node : node.parentNode);
				}

				range.collapse(!focusEnd);
				rangeHelper.selectRange(range);
				currentSelection = range;

				if (focusEnd) {
					$wysiwygDoc.scrollTop(body.scrollHeight);
					$wysiwygBody.scrollTop(body.scrollHeight);
				}

				base.focus();
			};

			/**
			 * Gets if the editor is read only
			 *
			 * @since 1.3.5
			 * @function
			 * @memberOf jQuery.sceditor.prototype
			 * @name readOnly
			 * @return {Boolean}
			 */
			/**
			 * Sets if the editor is read only
			 *
			 * @param {boolean} readOnly
			 * @since 1.3.5
			 * @function
			 * @memberOf jQuery.sceditor.prototype
			 * @name readOnly^2
			 * @return {this}
			 */
			base.readOnly = function (readOnly) {
				if (typeof readOnly !== 'boolean') {
					return $sourceEditor.attr('readonly') === 'readonly';
				}

				$wysiwygBody[0].contentEditable = !readOnly;

				if (!readOnly) {
					$sourceEditor.removeAttr('readonly');
				} else {
					$sourceEditor.attr('readonly', 'readonly');
				}

				updateToolBar(readOnly);

				return base;
			};

			/**
			 * Gets if the editor is in RTL mode
			 *
			 * @since 1.4.1
			 * @function
			 * @memberOf jQuery.sceditor.prototype
			 * @name rtl
			 * @return {Boolean}
			 */
			/**
			 * Sets if the editor is in RTL mode
			 *
			 * @param {boolean} rtl
			 * @since 1.4.1
			 * @function
			 * @memberOf jQuery.sceditor.prototype
			 * @name rtl^2
			 * @return {this}
			 */
			base.rtl = function (rtl) {
				var dir = rtl ? 'rtl' : 'ltr';

				if (typeof rtl !== 'boolean') {
					return $sourceEditor.attr('dir') === 'rtl';
				}

				$wysiwygBody.attr('dir', dir);
				$sourceEditor.attr('dir', dir);

				$editorContainer
					.removeClass('rtl')
					.removeClass('ltr')
					.addClass(dir);

				return base;
			};

			/**
			 * Updates the toolbar to disable/enable the appropriate buttons
			 * @private
			 */
			updateToolBar = function (disable) {
				var mode = base.inSourceMode() ? 'txtmode' : 'wysiwygmode';

				$.each(toolbarButtons, function (idx, $button) {
					if (disable === true || !$button.data('sceditor-' + mode)) {
						$button.addClass('disabled');
					} else {
						$button.removeClass('disabled');
					}
				});
			};

			/**
			 * Gets the width of the editor in pixels
			 *
			 * @since 1.3.5
			 * @function
			 * @memberOf jQuery.sceditor.prototype
			 * @name width
			 * @return {int}
			 */
			/**
			 * Sets the width of the editor
			 *
			 * @param {int} width Width in pixels
			 * @since 1.3.5
			 * @function
			 * @memberOf jQuery.sceditor.prototype
			 * @name width^2
			 * @return {this}
			 */
			/**
			 * Sets the width of the editor
			 *
			 * The saveWidth specifies if to save the width. The stored width can be
			 * used for things like restoring from maximized state.
			 *
			 * @param {int}     width            Width in pixels
			 * @param {boolean}	[saveWidth=true] If to store the width
			 * @since 1.4.1
			 * @function
			 * @memberOf jQuery.sceditor.prototype
			 * @name width^3
			 * @return {this}
			 */
			base.width = function (width, saveWidth) {
				if (!width && width !== 0) {
					return $editorContainer.width();
				}

				base.dimensions(width, null, saveWidth);

				return base;
			};

			/**
			 * Returns an object with the properties width and height
			 * which are the width and height of the editor in px.
			 *
			 * @since 1.4.1
			 * @function
			 * @memberOf jQuery.sceditor.prototype
			 * @name dimensions
			 * @return {object}
			 */
			/**
			 * <p>Sets the width and/or height of the editor.</p>
			 *
			 * <p>If width or height is not numeric it is ignored.</p>
			 *
			 * @param {int}	width	Width in px
			 * @param {int}	height	Height in px
			 * @since 1.4.1
			 * @function
			 * @memberOf jQuery.sceditor.prototype
			 * @name dimensions^2
			 * @return {this}
			 */
			/**
			 * <p>Sets the width and/or height of the editor.</p>
			 *
			 * <p>If width or height is not numeric it is ignored.</p>
			 *
			 * <p>The save argument specifies if to save the new sizes.
			 * The saved sizes can be used for things like restoring from
			 * maximized state. This should normally be left as true.</p>
			 *
			 * @param {int}		width		Width in px
			 * @param {int}		height		Height in px
			 * @param {boolean}	[save=true]	If to store the new sizes
			 * @since 1.4.1
			 * @function
			 * @memberOf jQuery.sceditor.prototype
			 * @name dimensions^3
			 * @return {this}
			 */
			base.dimensions = function (width, height, save) {
				// IE6 & IE7 add 2 pixels to the source mode textarea
				// height which must be ignored.
				// Doesn't seem to be any way to fix it with only CSS
				var ieBorder = IE_VER < 8 || globalDoc.documentMode < 8 ? 2 : 0;
				var undef;

				// set undefined width/height to boolean false
				width  = (!width && width !== 0) ? false : width;
				height = (!height && height !== 0) ? false : height;

				if (width === false && height === false) {
					return { width: base.width(), height: base.height() };
				}

				if ($wysiwygEditor.data('outerWidthOffset') === undef) {
					base.updateStyleCache();
				}

				if (width !== false) {
					if (save !== false) {
						options.width = width;
					}
	// This is the problem
					if (height === false) {
						height = $editorContainer.height();
						save   = false;
					}

					$editorContainer.width(width);
					if (width && width.toString().indexOf('%') > -1) {
						width = $editorContainer.width();
					}

					$wysiwygEditor.width(
						width - $wysiwygEditor.data('outerWidthOffset')
					);

					$sourceEditor.width(
						width - $sourceEditor.data('outerWidthOffset')
					);

					// Fix overflow issue with iOS not
					// breaking words unless a width is set
					if (browser.ios && $wysiwygBody) {
						$wysiwygBody.width(
							width - $wysiwygEditor.data('outerWidthOffset') -
							($wysiwygBody.outerWidth(true) - $wysiwygBody.width())
						);
					}
				}

				if (height !== false) {
					if (save !== false) {
						options.height = height;
					}

					// Convert % based heights to px
					if (height && height.toString().indexOf('%') > -1) {
						height = $editorContainer.height(height).height();
						$editorContainer.height('auto');
					}

					height -= !options.toolbarContainer ?
						$toolbar.outerHeight(true) : 0;

					$wysiwygEditor.height(
						height - $wysiwygEditor.data('outerHeightOffset')
					);

					$sourceEditor.height(
						height - ieBorder - $sourceEditor.data('outerHeightOffset')
					);
				}

				return base;
			};

			/**
			 * Updates the CSS styles cache.
			 *
			 * This shouldn't be needed unless changing the editors theme.
			 *F
			 * @since 1.4.1
			 * @function
			 * @memberOf jQuery.sceditor.prototype
			 * @name updateStyleCache
			 * @return {int}
			 */
			base.updateStyleCache = function () {
				// caching these improves FF resize performance
				$wysiwygEditor.data(
					'outerWidthOffset',
					$wysiwygEditor.outerWidth(true) - $wysiwygEditor.width()
				);
				$sourceEditor.data(
					'outerWidthOffset',
					$sourceEditor.outerWidth(true) - $sourceEditor.width()
				);

				$wysiwygEditor.data(
					'outerHeightOffset',
					$wysiwygEditor.outerHeight(true) - $wysiwygEditor.height()
				);
				$sourceEditor.data(
					'outerHeightOffset',
					$sourceEditor.outerHeight(true) - $sourceEditor.height()
				);
			};

			/**
			 * Gets the height of the editor in px
			 *
			 * @since 1.3.5
			 * @function
			 * @memberOf jQuery.sceditor.prototype
			 * @name height
			 * @return {int}
			 */
			/**
			 * Sets the height of the editor
			 *
			 * @param {int} height Height in px
			 * @since 1.3.5
			 * @function
			 * @memberOf jQuery.sceditor.prototype
			 * @name height^2
			 * @return {this}
			 */
			/**
			 * Sets the height of the editor
			 *
			 * The saveHeight specifies if to save the height.
			 *
			 * The stored height can be used for things like
			 * restoring from maximized state.
			 *
			 * @param {int} height Height in px
			 * @param {boolean} [saveHeight=true] If to store the height
			 * @since 1.4.1
			 * @function
			 * @memberOf jQuery.sceditor.prototype
			 * @name height^3
			 * @return {this}
			 */
			base.height = function (height, saveHeight) {
				if (!height && height !== 0) {
					return $editorContainer.height();
				}

				base.dimensions(null, height, saveHeight);

				return base;
			};

			/**
			 * Gets if the editor is maximised or not
			 *
			 * @since 1.4.1
			 * @function
			 * @memberOf jQuery.sceditor.prototype
			 * @name maximize
			 * @return {boolean}
			 */
			/**
			 * Sets if the editor is maximised or not
			 *
			 * @param {boolean} maximize If to maximise the editor
			 * @since 1.4.1
			 * @function
			 * @memberOf jQuery.sceditor.prototype
			 * @name maximize^2
			 * @return {this}
			 */
			base.maximize = function (maximize) {
				if (typeof maximize === 'undefined') {
					return $editorContainer.is('.sceditor-maximize');
				}

				maximize = !!maximize;

				// IE 6 fix
				if (IE_VER < 7) {
					$('html, body').toggleClass('sceditor-maximize', maximize);
				}

				$editorContainer.toggleClass('sceditor-maximize', maximize);
				base.width(maximize ? '100%' : options.width, false);
				base.height(maximize ? '100%' : options.height, false);

				return base;
			};

			/**
			 * Expands the editors height to the height of it's content
			 *
			 * Unless ignoreMaxHeight is set to true it will not expand
			 * higher than the maxHeight option.
			 *
			 * @since 1.3.5
			 * @param {Boolean} [ignoreMaxHeight=false]
			 * @function
			 * @name expandToContent
			 * @memberOf jQuery.sceditor.prototype
			 * @see #resizeToContent
			 */
			base.expandToContent = function (ignoreMaxHeight) {
				var	currentHeight = $editorContainer.height(),
					padding       = (currentHeight - $wysiwygEditor.height()),
					height        = $wysiwygBody[0].scrollHeight ||
						$wysiwygDoc[0].documentElement.scrollHeight,
					maxHeight     = options.resizeMaxHeight ||
						((options.height || $original.height()) * 2);

				height += padding;

				if ((ignoreMaxHeight === true || height <= maxHeight) &&
					height > currentHeight) {
					base.height(height);
				}
			};

			/**
			 * Destroys the editor, removing all elements and
			 * event handlers.
			 *
			 * Leaves only the original textarea.
			 *
			 * @function
			 * @name destroy
			 * @memberOf jQuery.sceditor.prototype
			 */
			base.destroy = function () {
				// Don't destroy if the editor has already been destroyed
				if (!pluginManager) {
					return;
				}

				pluginManager.destroy();

				rangeHelper   = null;
				lastRange     = null;
				pluginManager = null;

				if ($dropdown) {
					$dropdown.unbind().remove();
				}

				$globalDoc.unbind('click', handleDocumentClick);
				$globalWin.unbind('resize orientationChanged', handleWindowResize);

				$(original.form)
					.unbind('reset', handleFormReset)
					.unbind('submit', base.updateOriginal);

				$wysiwygBody.unbind();
				$wysiwygDoc.unbind().find('*').remove();

				$sourceEditor.unbind().remove();
				$toolbar.remove();
				$editorContainer.unbind().find('*').unbind().remove();
				$editorContainer.remove();

				$original
					.removeData('sceditor')
					.removeData('sceditorbbcode')
					.show();

				if (isRequired) {
					$original.attr('required', 'required');
				}
			};


			/**
			 * Creates a menu item drop down
			 *
			 * @param  {HTMLElement} menuItem The button to align the dropdown with
			 * @param  {string} name          Used for styling the dropown, will be
			 *                                a class sceditor-name
			 * @param  {HTMLElement} content  The HTML content of the dropdown
			 * @param  {bool} ieFix           If to add the unselectable attribute
			 *                                to all the contents elements. Stops
			 *                                IE from deselecting the text in the
			 *                                editor
			 * @function
			 * @name createDropDown
			 * @memberOf jQuery.sceditor.prototype
			 */
			base.createDropDown = function (menuItem, name, content, ieFix) {
				// first click for create second click for close
				var	dropDownCss,
					cssClass = 'sceditor-' + name,
					onlyclose = $dropdown && $dropdown.is('.' + cssClass);

				// Will re-focus the editor. This is needed for IE
				// as it has special logic to save/restore the selection
				base.closeDropDown(true);

				if (onlyclose) {
					return;
				}

				// IE needs unselectable attr to stop it from
				// unselecting the text in the editor.
				// SCEditor can cope if IE does unselect the
				// text it's just not nice.
				if (ieFix !== false) {
					$(content)
						.find(':not(input,textarea)')
						.filter(function () {
							return this.nodeType === 1;
						})
						.attr('unselectable', 'on');
				}

				dropDownCss = {
					top: menuItem.offset().top,
					left: menuItem.offset().left,
					marginTop: menuItem.outerHeight()
				};
				$.extend(dropDownCss, options.dropDownCss);

				$dropdown = $('<div class="sceditor-dropdown ' + cssClass + '" />')
					.css(dropDownCss)
					.append(content)
					.appendTo($('body'))
					.on('click focusin', function (e) {
						// stop clicks within the dropdown from being handled
						e.stopPropagation();
					});

				// If try to focus the first input immediately IE will
				// place the cursor at the start of the editor instead
				// of focusing on the input.
				setTimeout(function () {
					$dropdown.find('input,textarea').first().focus();
				});
			};


			/**
			 * Adds a handle used to remap keys to make them more useful for code
			 * input
			 *
			 * @private
			 */
			var addBodyCodeInputHandle = function (searchSelectors) {
				$wysiwygBody.on('keydown', null,
				{
					'searchSelector': searchSelectors.join(',')
				}, codeInputHandle);

			};

			/**
			 * Adds a handle used to remap keys to make them more useful for
			 * code input
			 *
			 * @private
			 */
			var codeInputHandle = function (e) {

				if (e.ctrlKey || e.altKey || e.metaKey) {
					return;
				}

				// For now, only Enter and Tab have defined executions
				if (e.which !== 13 &&	// Enter
					e.which !== 9		// tab
				) {
					return;
				}

				if (e.shiftKey && e.which !== 9) {
					return;
				}

				var currentRange = rangeHelper.selectedRange();

				if (!rangeHelper.getFirstBlockParent().
					matches(e.data.searchSelector)) {
					return;
				}

				rangeHelper.mergeTextNodesAtCaret();

				var rangeContainer = currentRange.startContainer;
				var theTextNode = rangeContainer.nodeValue;
				var cursorPosition = currentRange.startOffset - 1;
				var searchPos = theTextNode.lastIndexOf('\n', cursorPosition) + 1;

				if (e.which === 13) {	// Enter
					searchPos = theTextNode.lastIndexOf('\n', searchPos - 1) + 1;
					var newTabs = 0;
					var newTabsStr = '';
					while (theTextNode.charAt(searchPos + newTabs) === '\t') {
						newTabs++;
						newTabsStr += '\t';
					}

					rangeHelper.insertHTML('\n' + newTabsStr);
					triggerValueChanged();
					e.preventDefault();
					return;
				}

				if (e.shiftKey && e.which === 9) {	// unindent the Tab
					if (theTextNode[searchPos] === '\t') {
						rangeContainer.nodeValue =
							theTextNode.slice(0, searchPos) +
							theTextNode.slice(searchPos + 1);
						// tempfix: for some reason, the caret is being placed in
						// the previous line if it was near the \n...
						// After some hours, I can't find why...
						if (theTextNode[cursorPosition] === '\n') {
							cursorPosition++;
						}
						rangeHelper.placeCaretAt(rangeContainer, cursorPosition);

						setTimeout(function () {
							// At least in firefox, the text node is split after
							// editing where the cursor is placed.
							// If the cursor is placed just after the \n, the
							// caret appears in the line before although it is
							// ready to type in the line after.
							// This fixes that by recalculating after the split
							if (rangeContainer.nodeValue.length ===
								cursorPosition &&
								rangeContainer.nextSibling) {
									rangeHelper.placeCaretAt(
										rangeContainer.nextSibling, 0
									);
								}
						}, 10);

						triggerValueChanged();
					}

				} else if (e.which === 9) {	// Tab
					rangeHelper.insertHTML('\t');
					triggerValueChanged();
					e.preventDefault();
					return;
				}

				e.preventDefault();
				return;

			};

			var addSourceCodeInputHandle = function (searchSubstrings) {

				var startStrings = [];
				var endStrings =  [];
				for (var i = 0; i < searchSubstrings.length; i++) {
					startStrings.push(searchSubstrings[i][0]);
					endStrings.push(searchSubstrings[i][1]);
				}

				$sourceEditor.on('keydown', null,
				{
					'startStrings': startStrings,
					'endStrings': endStrings
				}, textCodeInputHandle);

			};

			var textCodeInputHandle = function (e) {
				if (e.ctrlKey || e.altKey || e.metaKey) {
					return;
				}

				// For now, only Enter and Tab have defined executions
				if (e.which !== 13 &&	// Enter
					e.which !== 9		// tab
				) {
					return;
				}

				if (e.shiftKey && e.which !== 9) {
					return;
				}

				if (!base.inText(sourceEditor, e.data.startStrings,
						e.data.endStrings)) {
					return;
				}

				var cursorPosition = sourceEditor.selectionStart;
				var sourceText = sourceEditor.value;
				var searchPos = sourceText.lastIndexOf('\n',
					cursorPosition - 1);

				if (e.which === 13) {	// Enter
					searchPos++;
					var newTabs = 0;
					var newTabsStr = '';
					while (sourceText.charAt(searchPos + newTabs) === '\t') {
						newTabs++;
						newTabsStr += '\t';
					}

					base.sourceEditorInsertText('\n' + newTabsStr);
					triggerValueChanged();

					e.preventDefault();
					return;
				}

				if (e.shiftKey && e.which === 9) {	// unindent the Tab
					// Incrementing helps with the math
					searchPos++;
					if (sourceText[searchPos] === '\t') {
						sourceEditor.value =	sourceText.slice(0, searchPos) +
												sourceText.slice(searchPos + 1);

						if (sourceText[cursorPosition - 1] !== '\n') {
							cursorPosition--;
						}
						sourceEditor.setSelectionRange(cursorPosition,
							cursorPosition);

						triggerValueChanged();
					}
				} else if (e.which === 9) {	// Tab
					base.sourceEditorInsertText('\t');
					triggerValueChanged();
				}

				e.preventDefault();
				return;
			};

			/**
			 * Handles any document click and closes the dropdown if open
			 * @private
			 */
			handleDocumentClick = function (e) {
				// ignore right clicks
				if (e.which !== 3 && $dropdown) {
					autoUpdate();

					base.closeDropDown();
				}
			};

			/**
			 * Handles the WYSIWYG editors paste event
			 * @private
			 */
			handlePasteEvt = function (e) {
				var	html, handlePaste, scrollTop,
					elm             = $wysiwygBody[0],
					doc             = $wysiwygDoc[0],
					checkCount      = 0,
					pastearea       = globalDoc.createElement('div'),
					prePasteContent = doc.createDocumentFragment(),
					clipboardData   = e ? e.clipboardData : false;

				if (options.disablePasting) {
					return false;
				}

				if (!options.enablePasteFiltering) {
					return;
				}

				rangeHelper.saveRange();
				globalDoc.body.appendChild(pastearea);

				if (clipboardData && clipboardData.getData) {
					if ((html = clipboardData.getData('text/html')) ||
						(html = clipboardData.getData('text/plain'))) {
						pastearea.innerHTML = html;
						handlePasteData(elm, pastearea);

						return false;
					}
				}

				// Save the scroll position so can be restored
				// when contents is restored
				scrollTop = $wysiwygBody.scrollTop() || $wysiwygDoc.scrollTop();

				while (elm.firstChild) {
					prePasteContent.appendChild(elm.firstChild);
				}

	// try make pastearea contenteditable and redirect to that? Might work.
	// Check the tests if still exist, if not re-0create
				handlePaste = function (elm, pastearea) {
					if (elm.childNodes.length > 0 || checkCount > 25) {
						while (elm.firstChild) {
							pastearea.appendChild(elm.firstChild);
						}

						while (prePasteContent.firstChild) {
							elm.appendChild(prePasteContent.firstChild);
						}

						$wysiwygBody.scrollTop(scrollTop);
						$wysiwygDoc.scrollTop(scrollTop);

						if (pastearea.childNodes.length > 0) {
							handlePasteData(elm, pastearea);
						} else {
							rangeHelper.restoreRange();
						}
					} else {
						// Allow max 25 checks before giving up.
						// Needed in case an empty string is pasted or
						// something goes wrong.
						checkCount++;
						setTimeout(function () {
							handlePaste(elm, pastearea);
						}, 20);
					}
				};
				handlePaste(elm, pastearea);

				base.focus();
				return true;
			};

			/**
			 * Gets the pasted data, filters it and then inserts it.
			 * @param {Element} elm
			 * @param {Element} pastearea
			 * @private
			 */
			handlePasteData = function (elm, pastearea) {
				// fix any invalid nesting
				dom.fixNesting(pastearea);
	// TODO: Trigger custom paste event to allow filtering
	// (pre and post converstion?)
				var pasteddata = pastearea.innerHTML;

				if (pluginManager.hasHandler('toSource')) {
					pasteddata = pluginManager.callOnlyFirst(
						'toSource', pasteddata, $(pastearea)
					);
				}

				pastearea.parentNode.removeChild(pastearea);

				if (pluginManager.hasHandler('toWysiwyg')) {
					pasteddata = pluginManager.callOnlyFirst(
						'toWysiwyg', pasteddata, true
					);
				}

				rangeHelper.restoreRange();
				base.wysiwygEditorInsertHtml(pasteddata, null, true);
			};

			/**
			 * Closes any currently open drop down
			 *
			 * @param {bool} [focus=false] If to focus the editor
			 *                             after closing the drop down
			 * @function
			 * @name closeDropDown
			 * @memberOf jQuery.sceditor.prototype
			 */
			base.closeDropDown = function (focus) {
				if ($dropdown) {
					$dropdown.unbind().remove();
					$dropdown = null;
				}

				if (focus === true) {
					base.focus();
				}
			};

			/**
			 * Gets the WYSIWYG editors document
			 * @private
			 */
			getWysiwygDoc = function () {
				if (wysiwygEditor.contentDocument) {
					return wysiwygEditor.contentDocument;
				}

				if (wysiwygEditor.contentWindow &&
					wysiwygEditor.contentWindow.document) {
					return wysiwygEditor.contentWindow.document;
				}

				return wysiwygEditor.document;
			};


			/**
			 * <p>Inserts HTML into WYSIWYG editor.</p>
			 *
			 * <p>If endHtml is specified, any selected text will be placed
			 * between html and endHtml. If there is no selected text html
			 * and endHtml will just be concated together.</p>
			 *
			 * @param {string} html
			 * @param {string} [endHtml=null]
			 * @param {boolean} [overrideCodeBlocking=false] If to insert the html
			 *                                               into code tags, by
			 *                                               default code tags only
			 *                                               support text.
			 * @function
			 * @name wysiwygEditorInsertHtml
			 * @memberOf jQuery.sceditor.prototype
			 */
			base.wysiwygEditorInsertHtml = function (
				html, endHtml, overrideCodeBlocking
			) {
				var	$marker, scrollTop, scrollTo,
					editorHeight = $wysiwygEditor.height();

				base.focus();

	// TODO: This code tag should be configurable and
	// should maybe convert the HTML into text instead
				// Don't apply to code elements
				if (!overrideCodeBlocking && ($(currentBlockNode).is('code') ||
					$(currentBlockNode).parents('code').length !== 0)) {
					return;
				}

				// Insert the HTML and save the range so the editor can be scrolled
				// to the end of the selection. Also allows emoticons to be replaced
				// without affecting the cusrsor position
				rangeHelper.insertHTML(html, endHtml);
				rangeHelper.saveRange();
				replaceEmoticons($wysiwygBody[0]);

				// Scroll the editor after the end of the selection
				$marker   = $wysiwygBody.find('#sceditor-end-marker').show();
				scrollTop = $wysiwygBody.scrollTop() || $wysiwygDoc.scrollTop();
				scrollTo  = (dom.getOffset($marker[0]).top +
					($marker.outerHeight(true) * 1.5)) - editorHeight;
				$marker.hide();

				// Only scroll if marker isn't already visible
				if (scrollTo > scrollTop || scrollTo + editorHeight < scrollTop) {
					$wysiwygBody.scrollTop(scrollTo);
					$wysiwygDoc.scrollTop(scrollTo);
				}

				triggerValueChanged(false);
				rangeHelper.restoreRange();

				// Add a new line after the last block element
				// so can always add text after it
				appendNewLine();
			};

			/**
			 * Like wysiwygEditorInsertHtml except it will convert any HTML
			 * into text before inserting it.
			 *
			 * @param {String} text
			 * @param {String} [endText=null]
			 * @function
			 * @name wysiwygEditorInsertText
			 * @memberOf jQuery.sceditor.prototype
			 */
			base.wysiwygEditorInsertText = function (text, endText) {
				base.wysiwygEditorInsertHtml(
					escape.entities(text), escape.entities(endText)
				);
			};

			/**
			 * <p>Inserts text into the WYSIWYG or source editor depending on which
			 * mode the editor is in.</p>
			 *
			 * <p>If endText is specified any selected text will be placed between
			 * text and endText. If no text is selected text and endText will
			 * just be concated together.</p>
			 *
			 * @param {String} text
			 * @param {String} [endText=null]
			 * @since 1.3.5
			 * @function
			 * @name insertText
			 * @memberOf jQuery.sceditor.prototype
			 */
			base.insertText = function (text, endText) {
				if (base.inSourceMode()) {
					base.sourceEditorInsertText(text, endText);
				} else {
					base.wysiwygEditorInsertText(text, endText);
				}

				return base;
			};

			/**
			 * <p>Like wysiwygEditorInsertHtml but inserts text into the
			 * source mode editor instead.</p>
			 *
			 * <p>If endText is specified any selected text will be placed between
			 * text and endText. If no text is selected text and endText will
			 * just be concated together.</p>
			 *
			 * <p>The cursor will be placed after the text param. If endText is
			 * specified the cursor will be placed before endText, so passing:<br />
			 *
			 * '[b]', '[/b]'</p>
			 *
			 * <p>Would cause the cursor to be placed:<br />
			 *
			 * [b]Selected text|[/b]</p>
			 *
			 * @param {String} text
			 * @param {String} [endText=null]
			 * @since 1.4.0
			 * @function
			 * @name sourceEditorInsertText
			 * @memberOf jQuery.sceditor.prototype
			 */
			base.sourceEditorInsertText = function (text, endText) {
				var range, scrollTop, currentValue,
					startPos = sourceEditor.selectionStart,
					endPos   = sourceEditor.selectionEnd;

				scrollTop = sourceEditor.scrollTop;
				sourceEditor.focus();

				// All browsers except IE < 9
				if (typeof startPos !== 'undefined') {
					currentValue = sourceEditor.value;

					if (endText) {
						text += currentValue.substring(startPos, endPos) + endText;
					}

					sourceEditor.value = currentValue.substring(0, startPos) +
						text +
						currentValue.substring(endPos, currentValue.length);

					sourceEditor.selectionStart = (startPos + text.length) -
						(endText ? endText.length : 0);
					sourceEditor.selectionEnd = sourceEditor.selectionStart;
				// IE < 9
				} else {
					range = globalDoc.selection.createRange();

					if (endText) {
						text += range.text + endText;
					}

					range.text = text;

					if (endText) {
						range.moveEnd('character', 0 - endText.length);
					}

					range.moveStart('character', range.End - range.Start);
					range.select();
				}

				sourceEditor.scrollTop = scrollTop;
				sourceEditor.focus();

				triggerValueChanged();
			};

			/**
			 * Gets the current instance of the rangeHelper class
			 * for the editor.
			 *
			 * @return jQuery.sceditor.rangeHelper
			 * @function
			 * @name getRangeHelper
			 * @memberOf jQuery.sceditor.prototype
			 */
			base.getRangeHelper = function () {
				return rangeHelper;
			};

			/**
			 * Gets the source editors textarea.
			 *
			 * This shouldn't be used to insert text
			 *
			 * @return {jQuery}
			 * @function
			 * @since 1.4.5
			 * @name sourceEditorCaret
			 * @memberOf jQuery.sceditor.prototype
			 */
			base.sourceEditorCaret = function (position) {
				var range,
					ret = {};

				sourceEditor.focus();

				// All browsers except IE <= 8
				if (typeof sourceEditor.selectionStart !== 'undefined') {
					if (position) {
						sourceEditor.selectionStart = position.start;
						sourceEditor.selectionEnd   = position.end;
					} else {
						ret.start = sourceEditor.selectionStart;
						ret.end   = sourceEditor.selectionEnd;
					}

				// IE8 and below
				} else {
					range = globalDoc.selection.createRange();

					if (position) {
						range.moveEnd('character', position.end);
						range.moveStart('character', position.start);
						range.select();
					} else {
						ret.start = range.Start;
						ret.end   = range.End;
					}
				}

				return position ? this : ret;
			};

			/**
			 * <p>Gets the value of the editor.</p>
			 *
			 * <p>If the editor is in WYSIWYG mode it will return the filtered
			 * HTML from it (converted to BBCode if using the BBCode plugin).
			 * It it's in Source Mode it will return the unfiltered contents
			 * of the source editor (if using the BBCode plugin this will be
			 * BBCode again).</p>
			 *
			 * @since 1.3.5
			 * @return {string}
			 * @function
			 * @name val
			 * @memberOf jQuery.sceditor.prototype
			 */
			/**
			 * <p>Sets the value of the editor.</p>
			 *
			 * <p>If filter set true the val will be passed through the filter
			 * function. If using the BBCode plugin it will pass the val to
			 * the BBCode filter to convert any BBCode into HTML.</p>
			 *
			 * @param {String} val
			 * @param {Boolean} [filter=true]
			 * @return {this}
			 * @since 1.3.5
			 * @function
			 * @name val^2
			 * @memberOf jQuery.sceditor.prototype
			 */
			base.val = function (val, filter) {
				if (typeof val !== 'string') {
					return base.inSourceMode() ?
						base.getSourceEditorValue(false) :
						base.getWysiwygEditorValue(filter);
				}

				if (!base.inSourceMode()) {
					if (filter !== false &&
						pluginManager.hasHandler('toWysiwyg')) {
						val = pluginManager.callOnlyFirst('toWysiwyg', val);
					}

					base.setWysiwygEditorValue(val);
				} else {
					base.setSourceEditorValue(val);
				}

				return base;
			};

			/**
			 * Deletes all elements that match elementSelector
			 *
			 *
			 * Example (examples show result of the .outerHTML parameter applied
			 * to "<strong>"):
			 * If the commonParent is "<strong>" and cutElement is
			 * "<!--break-->" in:
			 * <strong>hi there, how <em>are <span>y<!--break-->ou</span>
			 * doing</em> today</strong>
			 * After executing:
			 * <strong>hi there, how <em>are <span>y</span></em><!--break-->
			 * <em><span>ou</span> doing</em> today?</strong>
			 *
			 *
			 * @param {String} elementSelector
			 * @param {String | Node | jQuery | Boolean} howDeep
			 * @return {this}
			 * @function
			 * @name splitRemoveInSelection
			 * @memberOf RangeHelper.prototype
			 * @author brunoais
			 */
			base.splitRemoveInSelection = function (elementSelector, howDeep, isSplittingBlock) {
				if(typeof howDeep === 'string'){
					howDeep = $(howDeep);
				}
				if(howDeep instanceof Node){
					rangeHelper.splitAtSelection(elementSelector, howDeep);
				} else if(howDeep instanceof jQuery){
					if(howDeep.length > 0){
						rangeHelper.splitAtSelection(elementSelector, howDeep[0]);
					} else {
						rangeHelper.splitAtSelection(elementSelector, $wysiwygBody[0]);
					}
				} else if(howDeep){
					rangeHelper.splitAtSelection(elementSelector, $wysiwygBody[0]);
				} else {
					rangeHelper.splitAtSelection(elementSelector, false);
				}

				if (isSplittingBlock) {
					insertEditableLine(elementSelector);
				}
				triggerValueChanged();
				return base;
			};


			/**
			 * <p>Inserts HTML/BBCode into the editor</p>
			 *
			 * <p>If end is supplied any selected text will be placed between
			 * start and end. If there is no selected text start and end
			 * will be concated together.</p>
			 *
			 * <p>If the filter param is set to true, the HTML/BBCode will be
			 * passed through any plugin filters. If using the BBCode plugin
			 * this will convert any BBCode into HTML.</p>
			 *
			 * @param {String} start
			 * @param {String} [end=null]
			 * @param {Boolean} [filter=true]
			 * @param {Boolean} [convertEmoticons=true] If to convert emoticons
			 * @return {this}
			 * @since 1.3.5
			 * @function
			 * @name insert
			 * @memberOf jQuery.sceditor.prototype
			 */
			/**
			 * <p>Inserts HTML/BBCode into the editor</p>
			 *
			 * <p>If end is supplied any selected text will be placed between
			 * start and end. If there is no selected text start and end
			 * will be concated together.</p>
			 *
			 * <p>If the filter param is set to true, the HTML/BBCode will be
			 * passed through any plugin filters. If using the BBCode plugin
			 * this will convert any BBCode into HTML.</p>
			 *
			 * <p>If the allowMixed param is set to true, HTML any will not be
			 * escaped</p>
			 *
			 * @param {String} start
			 * @param {String} [end=null]
			 * @param {Boolean} [filter=true]
			 * @param {Boolean} [convertEmoticons=true] If to convert emoticons
			 * @param {Boolean} [allowMixed=false]
			 * @return {this}
			 * @since 1.4.3
			 * @function
			 * @name insert^2
			 * @memberOf jQuery.sceditor.prototype
			 */
			base.insert = function (
				/*jshint maxparams: false */
				start, end, filter, convertEmoticons, allowMixed
			) {
				if (base.inSourceMode()) {
					base.sourceEditorInsertText(start, end);
					return base;
				}

				// Add the selection between start and end
				if (end) {
					var	html = rangeHelper.selectedHtml(),
						$div = $('<div>').appendTo($('body')).hide().html(html);

					if (filter !== false && pluginManager.hasHandler('toSource')) {
						html = pluginManager.callOnlyFirst('toSource', html, $div);
					}

					$div.remove();

					start += html + end;
				}
	// TODO: This filter should allow empty tags as it's inserting.
				if (filter !== false && pluginManager.hasHandler('toWysiwyg')) {
					start = pluginManager.callOnlyFirst('toWysiwyg', start, true);
				}

				// Convert any escaped HTML back into HTML if mixed is allowed
				if (filter !== false && allowMixed === true) {
					start = start.replace(/&lt;/g, '<')
						.replace(/&gt;/g, '>')
						.replace(/&amp;/g, '&');
				}

				base.wysiwygEditorInsertHtml(start);

				return base;
			};

			/**
			 * Gets the WYSIWYG editors HTML value.
			 *
			 * If using a plugin that filters the Ht Ml like the BBCode plugin
			 * it will return the result of the filtering (BBCode) unless the
			 * filter param is set to false.
			 *
			 * @param {bool} [filter=true]
			 * @return {string}
			 * @function
			 * @name getWysiwygEditorValue
			 * @memberOf jQuery.sceditor.prototype
			 */
			base.getWysiwygEditorValue = function (filter) {
				var	html;
				// Create a tmp node to store contents so it can be modified
				// without affecting anything else.
				var $tmp = $('<div>')
					.appendTo(document.body)
					.append($($wysiwygBody[0].childNodes).clone());

				dom.fixNesting($tmp[0]);

				html = $tmp.html();

				// filter the HTML and DOM through any plugins
				if (filter !== false && pluginManager.hasHandler('toSource')) {
					html = pluginManager.callOnlyFirst('toSource', html, $tmp);
				}

				$tmp.remove();

				return html;
			};

			/**
			 * Gets the WYSIWYG editor's iFrame Body.
			 *
			 * @return {jQuery}
			 * @function
			 * @since 1.4.3
			 * @name getBody
			 * @memberOf jQuery.sceditor.prototype
			 */
			base.getBody = function () {
				return $wysiwygBody;
			};

			/**
			 * Gets the WYSIWYG editors container area (whole iFrame).
			 *
			 * @return {Node}
			 * @function
			 * @since 1.4.3
			 * @name getContentAreaContainer
			 * @memberOf jQuery.sceditor.prototype
			 */
			base.getContentAreaContainer = function () {
				return $wysiwygEditor;
			};

			/**
			 * Gets the text editor value
			 *
			 * If using a plugin that filters the text like the BBCode plugin
			 * it will return the result of the filtering which is BBCode to
			 * HTML so it will return HTML. If filter is set to false it will
			 * just return the contents of the source editor (BBCode).
			 *
			 * @param {bool} [filter=true]
			 * @return {string}
			 * @function
			 * @since 1.4.0
			 * @name getSourceEditorValue
			 * @memberOf jQuery.sceditor.prototype
			 */
			base.getSourceEditorValue = function (filter) {
				var val = $sourceEditor.val();

				if (filter !== false && pluginManager.hasHandler('toWysiwyg')) {
					val = pluginManager.callOnlyFirst('toWysiwyg', val);
				}

				return val;
			};

			/**
			 * Sets the WYSIWYG HTML editor value. Should only be the HTML
			 * contained within the body tags
			 *
			 * @param {string} value
			 * @function
			 * @name setWysiwygEditorValue
			 * @memberOf jQuery.sceditor.prototype
			 */
			base.setWysiwygEditorValue = function (value) {
				if (!value) {
					value = '<p>' + (IE_VER ? '' : '<br />') + '</p>';
				}

				$wysiwygBody[0].innerHTML = value;
				replaceEmoticons($wysiwygBody[0]);

				appendNewLine();
				triggerValueChanged();
			};

			/**
			 * Sets the text editor value
			 *
			 * @param {string} value
			 * @function
			 * @name setSourceEditorValue
			 * @memberOf jQuery.sceditor.prototype
			 */
			base.setSourceEditorValue = function (value) {
				$sourceEditor.val(value);

				triggerValueChanged();
			};

			/**
			 * Updates the textarea that the editor is replacing
			 * with the value currently inside the editor.
			 *
			 * @function
			 * @name updateOriginal
			 * @since 1.4.0
			 * @memberOf jQuery.sceditor.prototype
			 */
			base.updateOriginal = function () {
				$original.val(base.val());
			};

			/**
			 * Replaces any emoticon codes in the passed HTML
			 * with their emoticon images
			 * @private
			 */
			replaceEmoticons = function (node) {
	// TODO: Make this tag configurable.
				if (!options.emoticonsEnabled || $(node).parents('code').length) {
					return;
				}

				var	doc           = node.ownerDocument,
					whitespace    = '\\s|\xA0|\u2002|\u2003|\u2009|&nbsp;',
					emoticonCodes = [],
					emoticonRegex = [],
					emoticons     = $.extend(
						{},
						options.emoticons.more,
						options.emoticons.dropdown,
						options.emoticons.hidden
					);
	// TODO: cache the emoticonCodes and emoticonCodes objects and share them with
	// the AYT converstion
				$.each(emoticons, function (key) {
					if (options.emoticonsCompat) {
						emoticonRegex[key] = new RegExp(
							'(>|^|' + whitespace + ')' +
							escape.regex(key) +
							'($|<|' + whitespace + ')'
						);
					}

					emoticonCodes.push(key);
				});
	// TODO: tidy below
				var convertEmoticons = function (node) {
					node = node.firstChild;

					while (node) {
						var	parts, key, emoticon, parsedHtml,
							emoticonIdx, nextSibling, matchPos,
							nodeParent  = node.parentNode,
							nodeValue   = node.nodeValue;

						// All none textnodes
						if (node.nodeType !== 3) {
	// TODO: Make this tag configurable.
							if (!$(node).is('code')) {
								convertEmoticons(node);
							}
						} else if (nodeValue) {
							emoticonIdx = emoticonCodes.length;
							while (emoticonIdx--) {
								key      = emoticonCodes[emoticonIdx];
								matchPos = options.emoticonsCompat ?
									nodeValue.search(emoticonRegex[key]) :
									nodeValue.indexOf(key);

								if (matchPos > -1) {
									nextSibling    = node.nextSibling;
									emoticon       = emoticons[key];
									parts          = nodeValue
										.substr(matchPos).split(key);
									nodeValue      = nodeValue
										.substr(0, matchPos) + parts.shift();
									node.nodeValue = nodeValue;

									parsedHtml = dom.parseHTML(_tmpl('emoticon', {
										key: key,
										url: emoticon.url || emoticon,
										tooltip: emoticon.tooltip || key
									}), doc);

									nodeParent.insertBefore(
										parsedHtml[0],
										nextSibling
									);

									nodeParent.insertBefore(
										doc.createTextNode(parts.join(key)),
										nextSibling
									);
								}
							}
						}

						node = node.nextSibling;
					}
				};

				convertEmoticons(node);

				if (options.emoticonsCompat) {
					currentEmoticons = $wysiwygBody
						.find('img[data-sceditor-emoticon]');
				}
			};

			/**
			 * If the editor is in source code mode
			 *
			 * @return {bool}
			 * @function
			 * @name inSourceMode
			 * @memberOf jQuery.sceditor.prototype
			 */
			base.inSourceMode = function () {
				return $editorContainer.hasClass('sourceMode');
			};

			/**
			 * Gets if the editor is in sourceMode
			 *
			 * @return boolean
			 * @function
			 * @name sourceMode
			 * @memberOf jQuery.sceditor.prototype
			 */
			/**
			 * Sets if the editor is in sourceMode
			 *
			 * @param {bool} enable
			 * @return {this}
			 * @function
			 * @name sourceMode^2
			 * @memberOf jQuery.sceditor.prototype
			 */
			base.sourceMode = function (enable) {
				var inSourceMode = base.inSourceMode();

				if (typeof enable !== 'boolean') {
					return inSourceMode;
				}

				if ((inSourceMode && !enable) || (!inSourceMode && enable)) {
					base.toggleSourceMode();
				}

				return base;
			};

			/**
			 * Switches between the WYSIWYG and source modes
			 *
			 * @function
			 * @name toggleSourceMode
			 * @since 1.4.0
			 * @memberOf jQuery.sceditor.prototype
			 */
			base.toggleSourceMode = function () {
				var sourceMode = base.inSourceMode();

				// don't allow switching to WYSIWYG if doesn't support it
				if (!browser.isWysiwygSupported && sourceMode) {
					return;
				}

				if (!sourceMode) {
					rangeHelper.saveRange();
					rangeHelper.clear();
				}

				base.blur();

				if (sourceMode) {
					base.setWysiwygEditorValue(base.getSourceEditorValue());
				} else {
					base.setSourceEditorValue(base.getWysiwygEditorValue());
				}

				lastRange = null;
				$sourceEditor.toggle();
				$wysiwygEditor.toggle();
				$editorContainer
					.toggleClass('wysiwygMode', sourceMode)
					.toggleClass('sourceMode', !sourceMode);

				updateToolBar();
				updateActiveButtons();
			};

			/**
			 * Gets the selected text of the source editor
			 * @return {String}
			 * @private
			 */
			sourceEditorSelectedText = function () {
				sourceEditor.focus();

				if (typeof sourceEditor.selectionStart !== 'undefined') {
					return sourceEditor.value.substring(
						sourceEditor.selectionStart,
						sourceEditor.selectionEnd
					);
				} else {
					return globalDoc.selection.createRange().text;
				}
			};

			/**
			* Quick check if cursor in textarea is currently between any of the
			* start and any of the end substrings
			*
			* @param {object} textarea Textarea DOM object
			* @param {Array} startTags List of start tags to look for
			*		For example, Array('[code]', '[code=')
			* @param {Array} endTags List of end tags to look for
			*		For example, Array('[/code]')
			* @source Based on phpbb.inBBCodeTag from phpBB 3.1
			*
			* @return {boolean} True if cursor is in between the substrings
			*/
			base.inText = function (textarea, startStrings, endString) {
				var start = textarea.selectionStart,
					lastEnd = -1,
					lastStart = -1,
					i, index, value;

				if (typeof start !== 'number') {
					return false;
				}

				value = textarea.value.toLowerCase();

				for (i = 0; i < startStrings.length; i++) {
					var stringLength = startStrings[i].length;
					if (start >= stringLength) {
						index = value.lastIndexOf(startStrings[i],
							start - stringLength);
						lastStart = Math.max(lastStart, index);
					}
				}
				if (lastStart === -1) {
					return false;
				}

				if (start > 0) {
					for (i = 0; i < endString.length; i++) {
						index = value.lastIndexOf(endString[i], start - 1);
						lastEnd = Math.max(lastEnd, index);
					}
				}

				return (lastEnd < lastStart);
			};


			/**
			 * Handles the passed command
			 * @private
			 */
			handleCommand = function (caller, cmd) {
				// check if in text mode and handle text commands
				if (base.inSourceMode()) {
					if (cmd.txtExec) {
						if ($.isArray(cmd.txtExec)) {
							base.sourceEditorInsertText.apply(base, cmd.txtExec);
						} else {
							cmd.txtExec.call(
								base,
								caller,
								sourceEditorSelectedText()
							);
						}
					}
				} else if (cmd.exec) {
					if ($.isFunction(cmd.exec)) {
						cmd.exec.call(base, caller);
					} else {
						base.execCommand(
							cmd.exec,
							cmd.hasOwnProperty('execParam') ?
								cmd.execParam : null
						);
					}
				}

			};

			/**
			 * Saves the current range. Needed for IE because it forgets
			 * where the cursor was and what was selected
			 * @private
			 */
			saveRange = function () {
				/* this is only needed for IE */
				if (IE_VER) {
					lastRange = rangeHelper.selectedRange();
				}
			};

			/**
			 * Executes a command on the WYSIWYG editor
			 *
			 * @param {String} command
			 * @param {String|Boolean} [param]
			 * @function
			 * @name execCommand
			 * @memberOf jQuery.sceditor.prototype
			 */
			base.execCommand = function (command, param) {
				var	executed    = false,
					commandObj  = base.commands[command],
					$parentNode = $(rangeHelper.parentNode());

				base.focus();

	// TODO: make configurable
				// don't apply any commands to code elements
				if ($parentNode.is('code') ||
					$parentNode.parents('code').length !== 0) {
					return;
				}

				try {
					executed = $wysiwygDoc[0].execCommand(command, false, param);
				} catch (ex) {}

				// show error if execution failed and an error message exists
				if (!executed && commandObj && commandObj.errorMessage) {
					/*global alert:false*/
					alert(base._(commandObj.errorMessage));
				}

				updateActiveButtons();
			};

			/**
			 * Checks if the current selection has changed and triggers
			 * the selectionchanged event if it has.
			 *
			 * In browsers other than IE, it will check at most once every 100ms.
			 * This is because only IE has a selection changed event.
			 * @private
			 */
			checkSelectionChanged = function () {
				function check () {
					// rangeHelper could be null if editor was destroyed
					// before the timeout had finished
					if (rangeHelper && !rangeHelper.compare(currentSelection)) {
						currentSelection = rangeHelper.cloneSelected();
						$editorContainer.trigger($.Event('selectionchanged'));
					}

					isSelectionCheckPending = false;
				}

				if (isSelectionCheckPending) {
					return;
				}

				isSelectionCheckPending = true;

				// In IE, this is only called on the selectionchange event so no
				// need to limit checking as it should always be valid to do.
				if (IE_VER) {
					check();
				} else {
					setTimeout(check, 100);
				}
			};

			/**
			 * Checks if the current node has changed and triggers
			 * the nodechanged event if it has
			 * @private
			 */
			checkNodeChanged = function () {
				// check if node has changed
				var	oldNode,
					node = rangeHelper.parentNode();

				if (currentNode !== node) {
					oldNode          = currentNode;
					currentNode      = node;
					currentBlockNode = rangeHelper.getFirstBlockParent(node);

					$editorContainer.trigger($.Event('nodechanged', {
						oldNode: oldNode,
						newNode: currentNode
					}));
				}
			};

			/**
			 * <p>Gets the current node that contains the selection/caret in
			 * WYSIWYG mode.</p>
			 *
			 * <p>Will be null in sourceMode or if there is no selection.</p>
			 * @return {Node}
			 * @function
			 * @name currentNode
			 * @memberOf jQuery.sceditor.prototype
			 */
			base.currentNode = function () {
				return currentNode;
			};

			/**
			 * <p>Gets the first block level node that contains the
			 * selection/caret in WYSIWYG mode.</p>
			 *
			 * <p>Will be null in sourceMode or if there is no selection.</p>
			 * @return {Node}
			 * @function
			 * @name currentBlockNode
			 * @memberOf jQuery.sceditor.prototype
			 * @since 1.4.4
			 */
			base.currentBlockNode = function () {
				return currentBlockNode;
			};

			/**
			 * Updates if buttons are active or not
			 * @private
			 */
			updateActiveButtons = function (e) {
				var firstBlock, parent;
				var activeClass = 'active';
				var doc         = $wysiwygDoc[0];
				var isSource    = base.sourceMode();

				if (base.readOnly()) {
					$toolbar.find(activeClass).removeClass(activeClass);
					return;
				}

				if (!isSource) {
					parent     = e && e.newNode ? e.newNode : rangeHelper.parentNode();
					firstBlock = rangeHelper.getFirstBlockParent(parent);
				}

				for (var i = 0; i < btnStateHandlers.length; i++) {
					var state      = 0;
					var $btn       = toolbarButtons[btnStateHandlers[i].name];
					var stateFn    = btnStateHandlers[i].state;
					var isDisabled = (isSource && !$btn.data('sceditor-txtmode')) ||
								(!isSource && !$btn.data('sceditor-wysiwygmode'));

					if (typeof stateFn === 'string') {
						if (!isSource) {
							try {
								state = doc.queryCommandEnabled(stateFn) ? 0 : -1;

								/*jshint maxdepth: false*/
								if (state > -1) {
									state = doc.queryCommandState(stateFn) ? 1 : 0;
								}
							} catch (ex) {}
						}
					} else if (!isDisabled) {
						state = stateFn.call(base, parent, firstBlock);
					}

					$btn
						.toggleClass('disabled', isDisabled || state < 0)
						.toggleClass(activeClass, state > 0);
				}
			};

			/**
			 * Handles any key press in the WYSIWYG editor
			 *
			 * @private
			 */
			handleKeyPress = function (e) {
				var	$closestTag, br, brParent, lastChild;

	// TODO: improve this so isn't set list, probably should just use
	// dom.hasStyling to all block parents and if one does insert a br
				var DUPLICATED_TAGS = 'code,blockquote,pre';
				var LIST_TAGS = 'li,ul,ol';

				// FF bug: https://bugzilla.mozilla.org/show_bug.cgi?id=501496
				if (e.originalEvent.defaultPrevented) {
					return;
				}

				base.closeDropDown();

				$closestTag = $(currentBlockNode)
					.closest(DUPLICATED_TAGS + ',' + LIST_TAGS)
					.first();

				// "Fix" (OK it's a cludge) for blocklevel elements being
				// duplicated in some browsers when enter is pressed instead
				// of inserting a newline
				if (e.which === 13 && $closestTag.length &&
						!$closestTag.is(LIST_TAGS)) {
					lastRange = null;

					br = $wysiwygDoc[0].createElement('br');
					rangeHelper.insertNode(br);

					// Last <br> of a block will be collapsed unless it is
					// IE < 11 so need to make sure the <br> that was inserted
					// isn't the last node of a block.
					if (!IE_BR_FIX) {
						brParent    = br.parentNode;
						lastChild = brParent.lastChild;

						// Sometimes an empty next node is created after the <br>
						if (lastChild && lastChild.nodeType === 3 &&
							lastChild.nodeValue === '') {
							brParent.removeChild(lastChild);
							lastChild = brParent.lastChild;
						}

						// If this is the last BR of a block and the previous
						// sibling is inline then will need an extra BR. This
						// is needed because the last BR of a block will be
						// collapsed. Fixes issue #248
						if (!dom.isInline(brParent, true) && lastChild === br &&
							dom.isInline(br.previousSibling)) {
							rangeHelper.insertHTML('<br>');
						}
					}

					return false;
				}
			};

			/**
			 * Makes sure that if there is a code or quote tag at the
			 * end of the editor, that there is a new line after it.
			 *
			 * If there wasn't a new line at the end you wouldn't be able
			 * to enter any text after a code/quote tag
			 * @return {void}
			 * @private
			 */
			appendNewLine = function () {
				var name, requiresNewLine, paragraph,
					body = $wysiwygBody[0];

				dom.rTraverse(body, function (node) {
					name = node.nodeName.toLowerCase();
	// TODO: Replace requireNewLineFix with just a block level fix for any
	// block that has styling and any block that isn't a plain <p> or <div>
					if ($.inArray(name, requireNewLineFix) > -1) {
						requiresNewLine = true;
					}
	// TODO: tidy this up
					// find the last non-empty text node or line break.
					if ((node.nodeType === 3 && !/^\s*$/.test(node.nodeValue)) ||
						name === 'br' || (IE_BR_FIX && !node.firstChild &&
						!dom.isInline(node, false))) {

						// this is the last text or br node, if its in a code or
						// quote tag then add a newline to the end of the editor
						if (requiresNewLine) {
							paragraph = $wysiwygDoc[0].createElement('p');
							paragraph.className = 'sceditor-nlf';
							paragraph.innerHTML = !IE_BR_FIX ? '<br />' : '';
							body.appendChild(paragraph);
						}

						return false;
					}
				});
			};

			/**
			 * Handles form reset event
			 * @private
			 */
			handleFormReset = function () {
				base.val($original.val());
			};

			/**
			 * Handles any mousedown press in the WYSIWYG editor
			 * @private
			 */
			handleMouseDown = function () {
				base.closeDropDown();
				lastRange = null;
			};

			/**
			 * Handles the window resize event. Needed to resize then editor
			 * when the window size changes in fluid designs.
			 * @ignore
			 */
			handleWindowResize = function () {
				var	height = options.height,
					width  = options.width;

				if (!base.maximize()) {
					if ((height && height.toString().indexOf('%') > -1) ||
						(width && width.toString().indexOf('%') > -1)) {
						base.dimensions(width, height);
					}
				} else {
					base.dimensions('100%', '100%', false);
				}
			};

			/**
			 * Translates the string into the locale language.
			 *
			 * Replaces any {0}, {1}, {2}, ect. with the params provided.
			 *
			 * @param {string} str
			 * @param {...String} args
			 * @return {string}
			 * @function
			 * @name _
			 * @memberOf jQuery.sceditor.prototype
			 */
			base._ = function () {
				var	undef,
					args = arguments;

				if (locale && locale[args[0]]) {
					args[0] = locale[args[0]];
				}

				return args[0].replace(/\{(\d+)\}/g, function (str, p1) {
					return args[p1 - 0 + 1] !== undef ?
						args[p1 - 0 + 1] :
						'{' + p1 + '}';
				});
			};

			/**
			 * Passes events on to any handlers
			 * @private
			 * @return void
			 */
			handleEvent = function (e) {
				// Send event to all plugins
				pluginManager.call(e.type + 'Event', e, base);

				// convert the event into a custom event to send
				var prefix       = e.target === sourceEditor ? 'scesrc' : 'scewys';
				var customEvent  = $.Event(e);
				customEvent.type = prefix + e.type;

				$editorContainer.trigger(customEvent, base);
			};

			/**
			 * <p>Binds a handler to the specified events</p>
			 *
			 * <p>This function only binds to a limited list of
			 * supported events.<br />
			 * The supported events are:
			 * <ul>
			 *   <li>keyup</li>
			 *   <li>keydown</li>
			 *   <li>Keypress</li>
			 *   <li>blur</li>
			 *   <li>focus</li>
			 *   <li>nodechanged<br />
			 *       When the current node containing the selection changes
			 *       in WYSIWYG mode</li>
			 *   <li>contextmenu</li>
			 *   <li>selectionchanged</li>
			 *   <li>valuechanged</li>
			 * </ul>
			 * </p>
			 *
			 * <p>The events param should be a string containing the event(s)
			 * to bind this handler to. If multiple, they should be separated
			 * by spaces.</p>
			 *
			 * @param  {String} events
			 * @param  {Function} handler
			 * @param  {Boolean} excludeWysiwyg If to exclude adding this handler
			 *                                  to the WYSIWYG editor
			 * @param  {Boolean} excludeSource  if to exclude adding this handler
			 *                                  to the source editor
			 * @return {this}
			 * @function
			 * @name bind
			 * @memberOf jQuery.sceditor.prototype
			 * @since 1.4.1
			 */
			base.bind = function (events, handler, excludeWysiwyg, excludeSource) {
				events = events.split(' ');

				var i  = events.length;
				while (i--) {
					if ($.isFunction(handler)) {
						// Use custom events to allow passing the instance as the
						// 2nd argument.
						// Also allows unbinding without unbinding the editors own
						// event handlers.
						if (!excludeWysiwyg) {
							$editorContainer.bind('scewys' + events[i], handler);
						}

						if (!excludeSource) {
							$editorContainer.bind('scesrc' + events[i], handler);
						}

						// Start sending value changed events
						if (events[i] === 'valuechanged') {
							triggerValueChanged.hasHandler = true;
						}
					}
				}

				return base;
			};

			/**
			 * Unbinds an event that was bound using bind().
			 *
			 * @param  {String} events
			 * @param  {Function} handler
			 * @param  {Boolean} excludeWysiwyg If to exclude unbinding this
			 *                                  handler from the WYSIWYG editor
			 * @param  {Boolean} excludeSource  if to exclude unbinding this
			 *                                  handler from the source editor
			 * @return {this}
			 * @function
			 * @name unbind
			 * @memberOf jQuery.sceditor.prototype
			 * @since 1.4.1
			 * @see bind
			 */
			base.unbind = function (
				events, handler, excludeWysiwyg, excludeSource
			) {
				events = events.split(' ');

				var i  = events.length;
				while (i--) {
					if ($.isFunction(handler)) {
						if (!excludeWysiwyg) {
							$editorContainer.unbind('scewys' + events[i], handler);
						}

						if (!excludeSource) {
							$editorContainer.unbind('scesrc' + events[i], handler);
						}
					}
				}

				return base;
			};

			/**
			 * Blurs the editors input area
			 *
			 * @return {this}
			 * @function
			 * @name blur
			 * @memberOf jQuery.sceditor.prototype
			 * @since 1.3.6
			 */
			/**
			 * Adds a handler to the editors blur event
			 *
			 * @param  {Function} handler
			 * @param  {Boolean} excludeWysiwyg If to exclude adding this handler
			 *                                  to the WYSIWYG editor
			 * @param  {Boolean} excludeSource  if to exclude adding this handler
			 *                                  to the source editor
			 * @return {this}
			 * @function
			 * @name blur^2
			 * @memberOf jQuery.sceditor.prototype
			 * @since 1.4.1
			 */
			base.blur = function (handler, excludeWysiwyg, excludeSource) {
				if ($.isFunction(handler)) {
					base.bind('blur', handler, excludeWysiwyg, excludeSource);
				} else if (!base.sourceMode()) {
					$wysiwygBody.blur();
				} else {
					$sourceEditor.blur();
				}

				return base;
			};

			/**
			 * Fucuses the editors input area
			 *
			 * @return {this}
			 * @function
			 * @name focus
			 * @memberOf jQuery.sceditor.prototype
			 */
			/**
			 * Adds an event handler to the focus event
			 *
			 * @param  {Function} handler
			 * @param  {Boolean} excludeWysiwyg If to exclude adding this handler
			 *                                  to the WYSIWYG editor
			 * @param  {Boolean} excludeSource  if to exclude adding this handler
			 *                                  to the source editor
			 * @return {this}
			 * @function
			 * @name focus^2
			 * @memberOf jQuery.sceditor.prototype
			 * @since 1.4.1
			 */
			base.focus = function (handler, excludeWysiwyg, excludeSource) {
				if ($.isFunction(handler)) {
					base.bind('focus', handler, excludeWysiwyg, excludeSource);
				} else if (!base.inSourceMode()) {
					var container,
						rng = rangeHelper.selectedRange();

					// Fix FF bug where it shows the cursor in the wrong place
					// if the editor hasn't had focus before. See issue #393
					if (!currentSelection && !rangeHelper.hasSelection()) {
						autofocus();
					}

					// Check if cursor is set after a BR when the BR is the only
					// child of the parent. In Firefox this causes a line break
					// to occur when something is typed. See issue #321
					if (!IE_BR_FIX && rng && rng.endOffset === 1 && rng.collapsed) {
						container = rng.endContainer;

						if (container && container.childNodes.length === 1 &&
							$(container.firstChild).is('br')) {
							rng.setStartBefore(container.firstChild);
							rng.collapse(true);
							rangeHelper.selectRange(rng);
						}
					}

					wysiwygEditor.contentWindow.focus();
					$wysiwygBody[0].focus();

					// Needed for IE < 9
					if (lastRange) {
						rangeHelper.selectRange(lastRange);

						// remove the stored range after being set.
						// If the editor loses focus it should be
						// saved again.
						lastRange = null;
					}
				} else {
					sourceEditor.focus();
				}

				updateActiveButtons();

				return base;
			};

			/**
			 * Adds a handler to the key down event
			 *
			 * @param  {Function} handler
			 * @param  {Boolean} excludeWysiwyg If to exclude adding this handler
			 *                                  to the WYSIWYG editor
			 * @param  {Boolean} excludeSource  If to exclude adding this handler
			 *                                  to the source editor
			 * @return {this}
			 * @function
			 * @name keyDown
			 * @memberOf jQuery.sceditor.prototype
			 * @since 1.4.1
			 */
			base.keyDown = function (handler, excludeWysiwyg, excludeSource) {
				return base.bind('keydown', handler, excludeWysiwyg, excludeSource);
			};

			/**
			 * Adds a handler to the key press event
			 *
			 * @param  {Function} handler
			 * @param  {Boolean} excludeWysiwyg If to exclude adding this handler
			 *                                  to the WYSIWYG editor
			 * @param  {Boolean} excludeSource  If to exclude adding this handler
			 *                                  to the source editor
			 * @return {this}
			 * @function
			 * @name keyPress
			 * @memberOf jQuery.sceditor.prototype
			 * @since 1.4.1
			 */
			base.keyPress = function (handler, excludeWysiwyg, excludeSource) {
				return base
					.bind('keypress', handler, excludeWysiwyg, excludeSource);
			};

			/**
			 * Adds a handler to the key up event
			 *
			 * @param  {Function} handler
			 * @param  {Boolean} excludeWysiwyg If to exclude adding this handler
			 *                                  to the WYSIWYG editor
			 * @param  {Boolean} excludeSource  If to exclude adding this handler
			 *                                  to the source editor
			 * @return {this}
			 * @function
			 * @name keyUp
			 * @memberOf jQuery.sceditor.prototype
			 * @since 1.4.1
			 */
			base.keyUp = function (handler, excludeWysiwyg, excludeSource) {
				return base.bind('keyup', handler, excludeWysiwyg, excludeSource);
			};

			/**
			 * <p>Adds a handler to the node changed event.</p>
			 *
			 * <p>Happends whenever the node containing the selection/caret
			 * changes in WYSIWYG mode.</p>
			 *
			 * @param  {Function} handler
			 * @return {this}
			 * @function
			 * @name nodeChanged
			 * @memberOf jQuery.sceditor.prototype
			 * @since 1.4.1
			 */
			base.nodeChanged = function (handler) {
				return base.bind('nodechanged', handler, false, true);
			};

			/**
			 * <p>Adds a handler to the selection changed event</p>
			 *
			 * <p>Happens whenever the selection changes in WYSIWYG mode.</p>
			 *
			 * @param  {Function} handler
			 * @return {this}
			 * @function
			 * @name selectionChanged
			 * @memberOf jQuery.sceditor.prototype
			 * @since 1.4.1
			 */
			base.selectionChanged = function (handler) {
				return base.bind('selectionchanged', handler, false, true);
			};

			/**
			 * <p>Adds a handler to the value changed event</p>
			 *
			 * <p>Happens whenever the current editor value changes.</p>
			 *
			 * <p>Whenever anything is inserted, the value changed or
			 * 1.5 secs after text is typed. If a space is typed it will
			 * cause the event to be triggered immediately instead of
			 * after 1.5 seconds</p>
			 *
			 * @param  {Function} handler
			 * @param  {Boolean} excludeWysiwyg If to exclude adding this handler
			 *                                  to the WYSIWYG editor
			 * @param  {Boolean} excludeSource  If to exclude adding this handler
			 *                                  to the source editor
			 * @return {this}
			 * @function
			 * @name valueChanged
			 * @memberOf jQuery.sceditor.prototype
			 * @since 1.4.5
			 */
			base.valueChanged = function (handler, excludeWysiwyg, excludeSource) {
				return base
					.bind('valuechanged', handler, excludeWysiwyg, excludeSource);
			};

			/**
			 * Emoticons keypress handler
			 * @private
			 */
			emoticonsKeyPress = function (e) {
				var	replacedEmoticon,
					cachePos       = 0,
					emoticonsCache = base.emoticonsCache,
					curChar        = String.fromCharCode(e.which);
	// TODO: Make configurable
				if ($(currentBlockNode).is('code') ||
					$(currentBlockNode).parents('code').length) {
					return;
				}

				if (!emoticonsCache) {
					emoticonsCache = [];

					$.each($.extend(
						{},
						options.emoticons.more,
						options.emoticons.dropdown,
						options.emoticons.hidden
					), function (key, url) {
						emoticonsCache[cachePos++] = [
							key,
							_tmpl('emoticon', {
								key: key,
								url: url.url || url,
								tooltip: url.tooltip || key
							})
						];
					});

					emoticonsCache.sort(function (a, b) {
						return a[0].length - b[0].length;
					});

					base.emoticonsCache = emoticonsCache;
					base.longestEmoticonCode =
						emoticonsCache[emoticonsCache.length - 1][0].length;
				}

				replacedEmoticon = rangeHelper.raplaceKeyword(
					base.emoticonsCache,
					true,
					true,
					base.longestEmoticonCode,
					options.emoticonsCompat,
					curChar
				);

				if (replacedEmoticon && options.emoticonsCompat) {
					currentEmoticons = $wysiwygBody
						.find('img[data-sceditor-emoticon]');

					return /^\s$/.test(curChar);
				}

				return !replacedEmoticon;
			};

			/**
			 * Makes sure emoticons are surrounded by whitespace
			 * @private
			 */
			emoticonsCheckWhitespace = function () {
				if (!currentEmoticons.length) {
					return;
				}

				var	prev, next, parent, range, previousText, rangeStartContainer,
					currentBlock = base.currentBlockNode(),
					rangeStart   = false,
					noneWsRegex  = /[^\s\xA0\u2002\u2003\u2009\u00a0]+/;

				currentEmoticons = $.map(currentEmoticons, function (emoticon) {
					// Ignore emotiocons that have been removed from DOM
					if (!emoticon || !emoticon.parentNode) {
						return null;
					}

					if (!$.contains(currentBlock, emoticon)) {
						return emoticon;
					}

					prev         = emoticon.previousSibling;
					next         = emoticon.nextSibling;
					previousText = prev.nodeValue;

					// For IE's HTMLPhraseElement
					if (previousText === null) {
						previousText = prev.innerText || '';
					}

					if ((!prev || !noneWsRegex.test(prev.nodeValue.slice(-1))) &&
						(!next || !noneWsRegex.test((next.nodeValue || '')[0]))) {
						return emoticon;
					}

					parent              = emoticon.parentNode;
					range               = rangeHelper.cloneSelected();
					rangeStartContainer = range.startContainer;
					previousText        = previousText +
						$(emoticon).data('sceditor-emoticon');

					// Store current caret position
					if (rangeStartContainer === next) {
						rangeStart = previousText.length + range.startOffset;
					} else if (rangeStartContainer === currentBlock &&
						currentBlock.childNodes[range.startOffset] === next) {
						rangeStart = previousText.length;
					} else if (rangeStartContainer === prev) {
						rangeStart = range.startOffset;
					}

					if (!next || next.nodeType !== 3) {
						next = parent.insertBefore(
							parent.ownerDocument.createTextNode(''), next
						);
					}

					next.insertData(0, previousText);
					parent.removeChild(prev);
					parent.removeChild(emoticon);

					// Need to update the range starting
					// position if it has been modified
					if (rangeStart !== false) {
						range.setStart(next, rangeStart);
						range.collapse(true);
						rangeHelper.selectRange(range);
					}

					return null;
				});
			};

			/**
			 * Gets if emoticons are currently enabled
			 * @return {boolean}
			 * @function
			 * @name emoticons
			 * @memberOf jQuery.sceditor.prototype
			 * @since 1.4.2
			 */
			/**
			 * Enables/disables emoticons
			 *
			 * @param {boolean} enable
			 * @return {this}
			 * @function
			 * @name emoticons^2
			 * @memberOf jQuery.sceditor.prototype
			 * @since 1.4.2
			 */
			base.emoticons = function (enable) {
				if (!enable && enable !== false) {
					return options.emoticonsEnabled;
				}

				options.emoticonsEnabled = enable;

				if (enable) {
					$wysiwygBody.keypress(emoticonsKeyPress);

					if (!base.sourceMode()) {
						rangeHelper.saveRange();

						replaceEmoticons($wysiwygBody[0]);
						currentEmoticons = $wysiwygBody
							.find('img[data-sceditor-emoticon]');
						triggerValueChanged(false);

						rangeHelper.restoreRange();
					}
				} else {
					$wysiwygBody
						.find('img[data-sceditor-emoticon]')
						.replaceWith(function () {
							return $(this).data('sceditor-emoticon');
						});

					currentEmoticons = [];
					$wysiwygBody.unbind('keypress', emoticonsKeyPress);

					triggerValueChanged();
				}

				return base;
			};

			/**
			 * Gets the current WYSIWYG editors inline CSS
			 *
			 * @return {string}
			 * @function
			 * @name css
			 * @memberOf jQuery.sceditor.prototype
			 * @since 1.4.3
			 */
			/**
			 * Sets inline CSS for the WYSIWYG editor
			 *
			 * @param {string} css
			 * @return {this}
			 * @function
			 * @name css^2
			 * @memberOf jQuery.sceditor.prototype
			 * @since 1.4.3
			 */
			base.css = function (css) {
				if (!inlineCss) {
					inlineCss = $('<style id="#inline" />', $wysiwygDoc[0])
						.appendTo($wysiwygDoc.find('head'))[0];
				}

				if (typeof css !== 'string') {
					return inlineCss.styleSheet ?
						inlineCss.styleSheet.cssText : inlineCss.innerHTML;
				}

				if (inlineCss.styleSheet) {
					inlineCss.styleSheet.cssText = css;
				} else {
					inlineCss.innerHTML = css;
				}

				return base;
			};

			/**
			 * Handles the keydown event, used for shortcuts
			 * @private
			 */
			handleKeyDown = function (e) {
				var	shortcut   = [],
					SHIFT_KEYS = {
						'`': '~',
						'1': '!',
						'2': '@',
						'3': '#',
						'4': '$',
						'5': '%',
						'6': '^',
						'7': '&',
						'8': '*',
						'9': '(',
						'0': ')',
						'-': '_',
						'=': '+',
						';': ': ',
						'\'': '"',
						',': '<',
						'.': '>',
						'/': '?',
						'\\': '|',
						'[': '{',
						']': '}'
					},
					SPECIAL_KEYS = {
						8: 'backspace',
						9: 'tab',
						13: 'enter',
						19: 'pause',
						20: 'capslock',
						27: 'esc',
						32: 'space',
						33: 'pageup',
						34: 'pagedown',
						35: 'end',
						36: 'home',
						37: 'left',
						38: 'up',
						39: 'right',
						40: 'down',
						45: 'insert',
						46: 'del',
						91:  'win',
						92:  'win',
						93: 'select',
						96: '0',
						97: '1',
						98: '2',
						99: '3',
						100: '4',
						101: '5',
						102: '6',
						103: '7',
						104: '8',
						105: '9',
						106: '*',
						107: '+',
						109: '-',
						110: '.',
						111: '/',
						112: 'f1',
						113: 'f2',
						114: 'f3',
						115: 'f4',
						116: 'f5',
						117: 'f6',
						118: 'f7',
						119: 'f8',
						120: 'f9',
						121: 'f10',
						122: 'f11',
						123: 'f12',
						144: 'numlock',
						145: 'scrolllock',
						186: ';',
						187: '=',
						188: ',',
						189: '-',
						190: '.',
						191: '/',
						192: '`',
						219: '[',
						220: '\\',
						221: ']',
						222: '\''
					},
					NUMPAD_SHIFT_KEYS = {
						109: '-',
						110: 'del',
						111: '/',
						96: '0',
						97: '1',
						98: '2',
						99: '3',
						100: '4',
						101: '5',
						102: '6',
						103: '7',
						104: '8',
						105: '9'
					},
					which     = e.which,
					character = SPECIAL_KEYS[which] ||
						String.fromCharCode(which).toLowerCase();

				if (e.ctrlKey) {
					shortcut.push('ctrl');
				}

				if (e.altKey) {
					shortcut.push('alt');
				}

				if (e.shiftKey) {
					shortcut.push('shift');

					if (NUMPAD_SHIFT_KEYS[which]) {
						character = NUMPAD_SHIFT_KEYS[which];
					} else if (SHIFT_KEYS[character]) {
						character = SHIFT_KEYS[character];
					}
				}

				// Shift is 16, ctrl is 17 and alt is 18
				if (character && (which < 16 || which > 18)) {
					shortcut.push(character);
				}

				shortcut = shortcut.join('+');
				if (shortcutHandlers[shortcut]) {
					return shortcutHandlers[shortcut].call(base);
				}
			};

			/**
			 * Adds a shortcut handler to the editor
			 * @param  {String}          shortcut
			 * @param  {String|Function} cmd
			 * @return {jQuery.sceditor}
			 */
			base.addShortcut = function (shortcut, cmd) {
				shortcut = shortcut.toLowerCase();

				if (typeof cmd === 'string') {
					shortcutHandlers[shortcut] = function () {
						handleCommand(
							toolbarButtons[cmd],
							base.commands[cmd]
						);

						return false;
					};
				} else {
					shortcutHandlers[shortcut] = cmd;
				}

				return base;
			};

			/**
			 * Removes a shortcut handler
			 * @param  {String} shortcut
			 * @return {jQuery.sceditor}
			 */
			base.removeShortcut = function (shortcut) {
				delete shortcutHandlers[shortcut.toLowerCase()];

				return base;
			};

			/**
			 * Handles the backspace key press
			 *
			 * Will remove block styling like quotes/code ect if at the start.
			 * @private
			 */
			handleBackSpace = function (e) {
				var	node, offset, tmpRange, range, parent;

				// 8 is the backspace key
				if (options.disableBlockRemove || e.which !== 8 ||
					!(range = rangeHelper.selectedRange())) {
					return;
				}

				if (!globalWin.getSelection) {
					node     = range.parentElement();
					tmpRange = $wysiwygDoc[0].selection.createRange();

					// Select te entire parent and set the end
					// as start of the current range
					tmpRange.moveToElementText(node);
					tmpRange.setEndPoint('EndToStart', range);

					// Number of characters selected is the start offset
					// relative to the parent node
					offset = tmpRange.text.length;
				} else {
					node   = range.startContainer;
					offset = range.startOffset;
				}

				if (offset !== 0 || !(parent = currentStyledBlockNode())) {
					return;
				}

				while (node !== parent) {
					while (node.previousSibling) {
						node = node.previousSibling;

						// Everything but empty text nodes before the cursor
						// should prevent the style from being removed
						if (node.nodeType !== 3 || node.nodeValue) {
							return;
						}
					}

					if (!(node = node.parentNode)) {
						return;
					}
				}

				if (!parent || $(parent).is('body')) {
					return;
				}

				// The backspace was pressed at the start of
				// the container so clear the style
				base.clearBlockFormatting(parent);
				return false;
			};

			/**
			 * Gets the first styled block node that contains the cursor
			 * @return {HTMLElement}
			 */
			currentStyledBlockNode = function () {
				var block = currentBlockNode;

				while (!dom.hasStyling(block) || dom.isInline(block, true)) {
					if (!(block = block.parentNode) || $(block).is('body')) {
						return;
					}
				}

				return block;
			};

			/**
			 * Clears the formatting of the passed block element.
			 *
			 * If block is false, if will clear the styling of the first
			 * block level element that contains the cursor.
			 * @param  {HTMLElement} block
			 * @since 1.4.4
			 */
			base.clearBlockFormatting = function (block) {
				block = block || currentStyledBlockNode();

				if (!block || $(block).is('body')) {
					return base;
				}

				rangeHelper.saveRange();

				block.className = '';
				lastRange       = null;

				$(block).attr('style', '');

				if (!$(block).is('p,div,td')) {
					dom.convertElement(block, 'p');
				}

				rangeHelper.restoreRange();
				return base;
			};

			/**
			 * Triggers the valueChnaged signal if there is
			 * a plugin that handles it.
			 *
			 * If rangeHelper.saveRange() has already been
			 * called, then saveRange should be set to false
			 * to prevent the range being saved twice.
			 *
			 * @since 1.4.5
			 * @param {Boolean} saveRange If to call rangeHelper.saveRange().
			 * @private
			 */
			triggerValueChanged = function (saveRange) {
				if (!pluginManager ||
					(!pluginManager.hasHandler('valuechangedEvent') &&
						!triggerValueChanged.hasHandler)) {
					return;
				}

				var	currentHtml,
					sourceMode   = base.sourceMode(),
					hasSelection = !sourceMode && rangeHelper.hasSelection();

				// Don't need to save the range if sceditor-start-marker
				// is present as the range is already saved
				saveRange = saveRange !== false &&
					!$wysiwygDoc[0].getElementById('sceditor-start-marker');

				// Clear any current timeout as it's now been triggered
				if (valueChangedKeyUp.timer) {
					clearTimeout(valueChangedKeyUp.timer);
					valueChangedKeyUp.timer = false;
				}

				if (hasSelection && saveRange) {
					rangeHelper.saveRange();
				}

				currentHtml = sourceMode ?
					$sourceEditor.val() :
					$wysiwygBody.html();

				// Only trigger if something has actually changed.
				if (currentHtml !== triggerValueChanged.lastHtmlValue) {
					triggerValueChanged.lastHtmlValue = currentHtml;

					$editorContainer.trigger($.Event('valuechanged', {
						rawValue: sourceMode ? base.val() : currentHtml
					}));
				}

				if (hasSelection && saveRange) {
					rangeHelper.removeMarkers();
				}
			};

			/**
			 * Should be called whenever there is a blur event
			 * @private
			 */
			valueChangedBlur = function () {
				if (valueChangedKeyUp.timer) {
					triggerValueChanged();
				}
			};

			/**
			 * Should be called whenever there is a keypress event
			 * @param  {Event} e The keypress event
			 * @private
			 */
			valueChangedKeyUp = function (e) {
				var which         = e.which,
					lastChar      = valueChangedKeyUp.lastChar,
					lastWasSpace  = (lastChar === 13 || lastChar === 32),
					lastWasDelete = (lastChar === 8 || lastChar === 46);

				valueChangedKeyUp.lastChar = which;

				// 13 = return & 32 = space
				if (which === 13 || which === 32) {
					if (!lastWasSpace) {
						triggerValueChanged();
					} else {
						valueChangedKeyUp.triggerNextChar = true;
					}
				// 8 = backspace & 46 = del
				} else if (which === 8 || which === 46) {
					if (!lastWasDelete) {
						triggerValueChanged();
					} else {
						valueChangedKeyUp.triggerNextChar = true;
					}
				} else if (valueChangedKeyUp.triggerNextChar) {
					triggerValueChanged();
					valueChangedKeyUp.triggerNextChar = false;
				}

				// Clear the previous timeout and set a new one.
				if (valueChangedKeyUp.timer) {
					clearTimeout(valueChangedKeyUp.timer);
				}

				// Trigger the event 1.5s after the last keypress if space
				// isn't pressed. This might need to be lowered, will need
				// to look into what the slowest average Chars Per Min is.
				valueChangedKeyUp.timer = setTimeout(function () {
					triggerValueChanged();
				}, 1500);
			};

			autoUpdate = function () {
				if (!autoUpdateCanceled) {
					base.updateOriginal();
				}

				autoUpdateCanceled = false;
			};

			// run the initializer
			init();
		};


		/**
		 * Map containing the loaded SCEditor locales
		 * @type {Object}
		 * @name locale
		 * @memberOf jQuery.sceditor
		 */
		SCEditor.locale = {};


		/**
		 * Static command helper class
		 * @class command
		 * @name jQuery.sceditor.command
		 */
		SCEditor.command =
		/** @lends jQuery.sceditor.command */
		{
			/**
			 * Gets a command
			 *
			 * @param {String} name
			 * @return {Object|null}
			 * @since v1.3.5
			 */
			get: function (name) {
				return SCEditor.commands[name] || null;
			},

			/**
			 * <p>Adds a command to the editor or updates an existing
			 * command if a command with the specified name already exists.</p>
			 *
			 * <p>Once a command is add it can be included in the toolbar by
			 * adding it's name to the toolbar option in the constructor. It
			 * can also be executed manually by calling
			 * {@link jQuery.sceditor.execCommand}</p>
			 *
			 * @example
			 * SCEditor.command.set("hello",
			 * {
			 *     exec: function () {
			 *         alert("Hello World!");
			 *     }
			 * });
			 *
			 * @param {String} name
			 * @param {Object} cmd
			 * @return {this|false} Returns false if name or cmd is false
			 * @since v1.3.5
			 */
			set: function (name, cmd) {
				if (!name || !cmd) {
					return false;
				}

				// merge any existing command properties
				cmd = $.extend(SCEditor.commands[name] || {}, cmd);

				cmd.remove = function () {
					SCEditor.command.remove(name);
				};

				SCEditor.commands[name] = cmd;
				return this;
			},

			/**
			 * Removes a command
			 *
			 * @param {String} name
			 * @return {this}
			 * @since v1.3.5
			 */
			remove: function (name) {
				if (SCEditor.commands[name]) {
					delete SCEditor.commands[name];
				}

				return this;
			}
		};

		return SCEditor;
	}.call(exports, __webpack_require__, exports, module), __WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ },
/* 3 */
/***/ function(module, exports, __webpack_require__) {

	var __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_RESULT__ = function () {
		'use strict';

		var plugins = {};

		/**
		 * Plugin Manager class
		 * @class PluginManager
		 * @name PluginManager
		 */
		var PluginManager = function (thisObj) {
			/**
			 * Alias of this
			 *
			 * @private
			 * @type {Object}
			 */
			var base = this;

			/**
			 * Array of all currently registered plugins
			 *
			 * @type {Array}
			 * @private
			 */
			var registeredPlugins = [];


			/**
			 * Changes a signals name from "name" into "signalName".
			 *
			 * @param  {String} signal
			 * @return {String}
			 * @private
			 */
			var formatSignalName = function (signal) {
				return 'signal' + signal.charAt(0).toUpperCase() + signal.slice(1);
			};

			/**
			 * Calls handlers for a signal
			 *
			 * @see call()
			 * @see callOnlyFirst()
			 * @param  {Array}   args
			 * @param  {Boolean} returnAtFirst
			 * @return {Mixed}
			 * @private
			 */
			var callHandlers = function (args, returnAtFirst) {
				args = [].slice.call(args);

				var	idx, ret,
					signal = formatSignalName(args.shift());

				for (idx = 0; idx < registeredPlugins.length; idx++) {
					if (signal in registeredPlugins[idx]) {
						ret = registeredPlugins[idx][signal].apply(thisObj, args);

						if (returnAtFirst) {
							return ret;
						}
					}
				}
			};

			/**
			 * Calls all handlers for the passed signal
			 *
			 * @param  {String}    signal
			 * @param  {...String} args
			 * @return {Void}
			 * @function
			 * @name call
			 * @memberOf PluginManager.prototype
			 */
			base.call = function () {
				callHandlers(arguments, false);
			};

			/**
			 * Calls the first handler for a signal, and returns the
			 *
			 * @param  {String}    signal
			 * @param  {...String} args
			 * @return {Mixed} The result of calling the handler
			 * @function
			 * @name callOnlyFirst
			 * @memberOf PluginManager.prototype
			 */
			base.callOnlyFirst = function () {
				return callHandlers(arguments, true);
			};

			/**
			 * Checks if a signal has a handler
			 *
			 * @param  {String} signal
			 * @return {Boolean}
			 * @function
			 * @name hasHandler
			 * @memberOf PluginManager.prototype
			 */
			base.hasHandler = function (signal) {
				var i  = registeredPlugins.length;
				signal = formatSignalName(signal);

				while (i--) {
					if (signal in registeredPlugins[i]) {
						return true;
					}
				}

				return false;
			};

			/**
			 * Checks if the plugin exists in plugins
			 *
			 * @param  {String} plugin
			 * @return {Boolean}
			 * @function
			 * @name exists
			 * @memberOf PluginManager.prototype
			 */
			base.exists = function (plugin) {
				if (plugin in plugins) {
					plugin = plugins[plugin];

					return typeof plugin === 'function' &&
						typeof plugin.prototype === 'object';
				}

				return false;
			};

			/**
			 * Checks if the passed plugin is currently registered.
			 *
			 * @param  {String} plugin
			 * @return {Boolean}
			 * @function
			 * @name isRegistered
			 * @memberOf PluginManager.prototype
			 */
			base.isRegistered = function (plugin) {
				if (base.exists(plugin)) {
					var idx = registeredPlugins.length;

					while (idx--) {
						if (registeredPlugins[idx] instanceof plugins[plugin]) {
							return true;
						}
					}
				}

				return false;
			};

			/**
			 * Registers a plugin to receive signals
			 *
			 * @param  {String} plugin
			 * @return {Boolean}
			 * @function
			 * @name register
			 * @memberOf PluginManager.prototype
			 */
			base.register = function (plugin) {
				if (!base.exists(plugin) || base.isRegistered(plugin)) {
					return false;
				}

				plugin = new plugins[plugin]();
				registeredPlugins.push(plugin);

				if ('init' in plugin) {
					plugin.init.call(thisObj);
				}

				return true;
			};

			/**
			 * Deregisters a plugin.
			 *
			 * @param  {String} plugin
			 * @return {Boolean}
			 * @function
			 * @name deregister
			 * @memberOf PluginManager.prototype
			 */
			base.deregister = function (plugin) {
				var	removedPlugin,
					pluginIdx = registeredPlugins.length,
					removed   = false;

				if (!base.isRegistered(plugin)) {
					return removed;
				}

				while (pluginIdx--) {
					if (registeredPlugins[pluginIdx] instanceof plugins[plugin]) {
						removedPlugin = registeredPlugins.splice(pluginIdx, 1)[0];
						removed       = true;

						if ('destroy' in removedPlugin) {
							removedPlugin.destroy.call(thisObj);
						}
					}
				}

				return removed;
			};

			/**
			 * Clears all plugins and removes the owner reference.
			 *
			 * Calling any functions on this object after calling
			 * destroy will cause a JS error.
			 *
			 * @name destroy
			 * @memberOf PluginManager.prototype
			 */
			base.destroy = function () {
				var i = registeredPlugins.length;

				while (i--) {
					if ('destroy' in registeredPlugins[i]) {
						registeredPlugins[i].destroy.call(thisObj);
					}
				}

				registeredPlugins = [];
				thisObj    = null;
			};
		};

		PluginManager.plugins = plugins;

		return PluginManager;
	}.call(exports, __webpack_require__, exports, module), __WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ },
/* 4 */
/***/ function(module, exports, __webpack_require__) {

	var __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_RESULT__ = function (require, exports) {
		'use strict';

		var $ = __webpack_require__(1);

		var USER_AGENT = navigator.userAgent;

		/**
		 * Detects the version of IE is being used if any.
		 *
		 * Will be the IE version number or undefined if the
		 * browser is not IE.
		 *
		 * Source: https://gist.github.com/527683 with extra code
		 * for IE 10 & 11 detection.
		 *
		 * @function
		 * @name ie
		 * @type {int}
		 */
		exports.ie = (function () {
			var	undef,
				v   = 3,
				doc = document,
				div = doc.createElement('div'),
				all = div.getElementsByTagName('i');

			do {
				div.innerHTML = '<!--[if gt IE ' + (++v) + ']><i></i><![endif]-->';
			} while (all[0]);

			// Detect IE 10 as it doesn't support conditional comments.
			if ((doc.documentMode && doc.all && window.atob)) {
				v = 10;
			}

			// Detect IE 11
			if (v === 4 && doc.documentMode) {
				v = 11;
			}

			return v > 4 ? v : undef;
		}());

		/**
		 * <p>Detects if the browser is iOS</p>
		 *
		 * <p>Needed to fix iOS specific bugs/</p>
		 *
		 * @function
		 * @name ios
		 * @memberOf jQuery.sceditor
		 * @type {Boolean}
		 */
		exports.ios = /iPhone|iPod|iPad| wosbrowser\//i.test(USER_AGENT);

		/**
		 * If the browser supports WYSIWYG editing (e.g. older mobile browsers).
		 *
		 * @function
		 * @name isWysiwygSupported
		 * @return {Boolean}
		 */
		exports.isWysiwygSupported = (function () {
			var	match, isUnsupported, undef,
				editableAttr = $('<p contenteditable="true">')[0].contentEditable;

			// Check if the contenteditable attribute is supported
			if (editableAttr === undef && editableAttr === 'inherit') {
				return false;
			}

			// I think blackberry supports contentEditable or will at least
			// give a valid value for the contentEditable detection above
			// so it isn't included in the below tests.

			// I hate having to do UA sniffing but some mobile browsers say they
			// support contentediable when it isn't usable, i.e. you can't enter
			// text.
			// This is the only way I can think of to detect them which is also how
			// every other editor I've seen deals with this issue.

			// Exclude Opera mobile and mini
			isUnsupported = /Opera Mobi|Opera Mini/i.test(USER_AGENT);

			if (/Android/i.test(USER_AGENT)) {
				isUnsupported = true;

				if (/Safari/.test(USER_AGENT)) {
					// Android browser 534+ supports content editable
					// This also matches Chrome which supports content editable too
					match = /Safari\/(\d+)/.exec(USER_AGENT);
					isUnsupported = (!match || !match[1] ? true : match[1] < 534);
				}
			}

			// The current version of Amazon Silk supports it, older versions didn't
			// As it uses webkit like Android, assume it's the same and started
			// working at versions >= 534
			if (/ Silk\//i.test(USER_AGENT)) {
				match = /AppleWebKit\/(\d+)/.exec(USER_AGENT);
				isUnsupported = (!match || !match[1] ? true : match[1] < 534);
			}

			// iOS 5+ supports content editable
			if (exports.ios) {
				// Block any version <= 4_x(_x)
				isUnsupported = /OS [0-4](_\d)+ like Mac/i.test(USER_AGENT);
			}

			// FireFox does support WYSIWYG on mobiles so override
			// any previous value if using FF
			if (/Firefox/i.test(USER_AGENT)) {
				isUnsupported = false;
			}

			if (/OneBrowser/i.test(USER_AGENT)) {
				isUnsupported = false;
			}

			// UCBrowser works but doesn't give a unique user agent
			if (navigator.vendor === 'UCWEB') {
				isUnsupported = false;
			}

			return !isUnsupported;
		}());
	}.call(exports, __webpack_require__, exports, module), __WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ },
/* 5 */
/***/ function(module, exports, __webpack_require__) {

	var __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_RESULT__ = function (require, exports) {
		'use strict';

		var VALID_SCHEME_REGEX =
			/^(?:https?|s?ftp|mailto|spotify|skype|ssh|teamspeak|tel):|(?:\/\/)/i;

		/**
		 * Escapes a string so it's safe to use in regex
		 *
		 * @param {String} str
		 * @return {String}
		 * @name regex
		 */
		exports.regex = function (str) {
			return str.replace(/([\-.*+?^=!:${}()|\[\]\/\\])/g, '\\$1');
		};

		/**
		 * Escapes all HTML entities in a string
		 *
		 * If noQuotes is set to false, all single and double
		 * quotes will also be escaped
		 *
		 * @param {String} str
		 * @param {Boolean} [noQuotes=false]
		 * @return {String}
		 * @name entities
		 * @since 1.4.1
		 */
		exports.entities = function (str, noQuotes) {
			if (!str) {
				return str;
			}

			var replacements = {
				'&': '&amp;',
				'<': '&lt;',
				'>': '&gt;',
				'  ': ' &nbsp;',
				'\r\n': '\n',
				'\r': '\n',
				'\n': '<br />'
			};

			if (noQuotes !== false) {
				replacements['"']  = '&#34;';
				replacements['\''] = '&#39;';
				replacements['`']  = '&#96;';
			}

			str = str.replace(/ {2}|\r\n|[&<>\r\n'"`]/g, function (match) {
				return replacements[match] || match;
			});

			return str;
		};

		/**
		 * Escape URI scheme.
		 *
		 * Appends the current URL to a url if it has a scheme that is not:
		 *
		 * http
		 * https
		 * sftp
		 * ftp
		 * mailto
		 * spotify
		 * skype
		 * ssh
		 * teamspeak
		 * tel
		 * //
		 *
		 * **IMPORTANT**: This does not escape any HTML in a url, for
		 * that use the escape.entities() method.
		 *
		 * @param  {String} url
		 * @return {String}
		 * @name escapeUriScheme
		 * @memberOf jQuery.sceditor
		 * @since 1.4.5
		 */
		exports.uriScheme = function (url) {
			/*jshint maxlen:false*/
			var	path,
				// If there is a : before a / then it has a scheme
				hasScheme = /^[^\/]*:/i,
				location = window.location;

			// Has no scheme or a valid scheme
			if ((!url || !hasScheme.test(url)) || VALID_SCHEME_REGEX.test(url)) {
				return url;
			}

			path = location.pathname.split('/');
			path.pop();

			return location.protocol + '//' +
				location.host +
				path.join('/') + '/' +
				url;
		};
	}.call(exports, __webpack_require__, exports, module), __WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ },
/* 6 */
/***/ function(module, exports, __webpack_require__) {

	var __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_RESULT__ = function (require) {
		'use strict';

		var $      = __webpack_require__(1);
		var IE_VER = __webpack_require__(4).ie;
		var _tmpl  = __webpack_require__(10);

		// In IE < 11 a BR at the end of a block level element
		// causes a line break. In all other browsers it's collapsed.
		var IE_BR_FIX = IE_VER && IE_VER < 11;


		/**
		 * Map of all the commands for SCEditor
		 * @type {Object}
		 * @name commands
		 * @memberOf jQuery.sceditor
		 */
		var defaultCommnds = {
			// START_COMMAND: Bold
			bold: {
				exec: 'bold',
				tooltip: 'Bold',
				shortcut: 'Ctrl+B'
			},
			// END_COMMAND
			// START_COMMAND: Italic
			italic: {
				exec: 'italic',
				tooltip: 'Italic',
				shortcut: 'Ctrl+I'
			},
			// END_COMMAND
			// START_COMMAND: Underline
			underline: {
				exec: 'underline',
				tooltip: 'Underline',
				shortcut: 'Ctrl+U'
			},
			// END_COMMAND
			// START_COMMAND: Strikethrough
			strike: {
				exec: 'strikethrough',
				tooltip: 'Strikethrough'
			},
			// END_COMMAND
			// START_COMMAND: Subscript
			subscript: {
				exec: 'subscript',
				tooltip: 'Subscript'
			},
			// END_COMMAND
			// START_COMMAND: Superscript
			superscript: {
				exec: 'superscript',
				tooltip: 'Superscript'
			},
			// END_COMMAND

			// START_COMMAND: Left
			left: {
				exec: 'justifyleft',
				tooltip: 'Align left'
			},
			// END_COMMAND
			// START_COMMAND: Centre
			center: {
				exec: 'justifycenter',
				tooltip: 'Center'
			},
			// END_COMMAND
			// START_COMMAND: Right
			right: {
				exec: 'justifyright',
				tooltip: 'Align right'
			},
			// END_COMMAND
			// START_COMMAND: Justify
			justify: {
				exec: 'justifyfull',
				tooltip: 'Justify'
			},
			// END_COMMAND

			// START_COMMAND: Font
			font: {
				_dropDown: function (editor, caller, callback) {
					var	fontIdx = 0,
						fonts   = editor.opts.fonts.split(','),
						content = $('<div />'),
						/** @private */
						clickFunc = function () {
							callback($(this).data('font'));
							editor.closeDropDown(true);
							return false;
						};

					for (; fontIdx < fonts.length; fontIdx++) {
						content.append(
							_tmpl('fontOpt', {
								font: fonts[fontIdx]
							}, true).click(clickFunc)
						);
					}

					editor.createDropDown(caller, 'font-picker', content);
				},
				exec: function (caller) {
					var editor = this;

					defaultCommnds.font._dropDown(
						editor,
						caller,
						function (fontName) {
							editor.execCommand('fontname', fontName);
						}
					);
				},
				tooltip: 'Font Name'
			},
			// END_COMMAND
			// START_COMMAND: Size
			size: {
				_dropDown: function (editor, caller, callback) {
					var	content   = $('<div />'),
						/** @private */
						clickFunc = function (e) {
							callback($(this).data('size'));
							editor.closeDropDown(true);
							e.preventDefault();
						};

					for (var i = 1; i <= 7; i++) {
						content.append(_tmpl('sizeOpt', {
							size: i
						}, true).click(clickFunc));
					}

					editor.createDropDown(caller, 'fontsize-picker', content);
				},
				exec: function (caller) {
					var editor = this;

					defaultCommnds.size._dropDown(
						editor,
						caller,
						function (fontSize) {
							editor.execCommand('fontsize', fontSize);
						}
					);
				},
				tooltip: 'Font Size'
			},
			// END_COMMAND
			// START_COMMAND: Colour
			color: {
				_dropDown: function (editor, caller, callback) {
					var	i, x, color, colors,
						genColor     = {r: 255, g: 255, b: 255},
						content      = $('<div />'),
						colorColumns = editor.opts.colors ?
							editor.opts.colors.split('|') : new Array(21),
						// IE is slow at string concation so use an array
						html         = [],
						cmd          = defaultCommnds.color;

					if (!cmd._htmlCache) {
						for (i = 0; i < colorColumns.length; ++i) {
							colors = colorColumns[i] ?
								colorColumns[i].split(',') : new Array(21);

							html.push('<div class="sceditor-color-column">');

							for (x = 0; x < colors.length; ++x) {
								// use pre defined colour if can otherwise use the
								// generated color
								color = colors[x] || '#' +
									genColor.r.toString(16) +
									genColor.g.toString(16) +
									genColor.b.toString(16);

								html.push(
									'<a href="#" class="sceditor-color-option"' +
									' style="background-color: ' + color + '"' +
									' data-color="' + color + '"></a>'
								);

								if (x % 5 === 0) {
									genColor.g -= 51;
									genColor.b = 255;
								} else {
									genColor.b -= 51;
								}
							}

							html.push('</div>');

							if (i % 5 === 0) {
								genColor.r -= 51;
								genColor.g = 255;
								genColor.b = 255;
							} else {
								genColor.g = 255;
								genColor.b = 255;
							}
						}

						cmd._htmlCache = html.join('');
					}

					content.append(cmd._htmlCache)
						.find('a')
						.click(function (e) {
							callback($(this).attr('data-color'));
							editor.closeDropDown(true);
							e.preventDefault();
						});

					editor.createDropDown(caller, 'color-picker', content);
				},
				exec: function (caller) {
					var editor = this;

					defaultCommnds.color._dropDown(
						editor,
						caller,
						function (color) {
							editor.execCommand('forecolor', color);
						}
					);
				},
				tooltip: 'Font Color'
			},
			// END_COMMAND
			// START_COMMAND: Remove Format
			removeformat: {
				exec: 'removeformat',
				tooltip: 'Remove Formatting'
			},
			// END_COMMAND

			// START_COMMAND: Cut
			cut: {
				exec: 'cut',
				tooltip: 'Cut',
				errorMessage: 'Your browser does not allow the cut command. ' +
					'Please use the keyboard shortcut Ctrl/Cmd-X'
			},
			// END_COMMAND
			// START_COMMAND: Copy
			copy: {
				exec: 'copy',
				tooltip: 'Copy',
				errorMessage: 'Your browser does not allow the copy command. ' +
					'Please use the keyboard shortcut Ctrl/Cmd-C'
			},
			// END_COMMAND
			// START_COMMAND: Paste
			paste: {
				exec: 'paste',
				tooltip: 'Paste',
				errorMessage: 'Your browser does not allow the paste command. ' +
					'Please use the keyboard shortcut Ctrl/Cmd-V'
			},
			// END_COMMAND
			// START_COMMAND: Paste Text
			pastetext: {
				exec: function (caller) {
					var	val, content,
						editor  = this;

					content = _tmpl('pastetext', {
						label: editor._(
							'Paste your text inside the following box:'
						),
						insert: editor._('Insert')
					}, true);

					content.find('.button').click(function (e) {
						val = content.find('#txt').val();

						if (val) {
							editor.wysiwygEditorInsertText(val);
						}

						editor.closeDropDown(true);
						e.preventDefault();
					});

					editor.createDropDown(caller, 'pastetext', content);
				},
				tooltip: 'Paste Text'
			},
			// END_COMMAND
			// START_COMMAND: Bullet List
			bulletlist: {
				exec: 'insertunorderedlist',
				tooltip: 'Bullet list'
			},
			// END_COMMAND
			// START_COMMAND: Ordered List
			orderedlist: {
				exec: 'insertorderedlist',
				tooltip: 'Numbered list'
			},
			// END_COMMAND
			// START_COMMAND: Indent
			indent: {
				state: function (parents, firstBlock) {
					// Only works with lists, for now
					// This is a nested list, so it will always work
					var	range, startParent, endParent,
						$firstBlock = $(firstBlock),
						parentLists = $firstBlock.parents('ul,ol,menu'),
						parentList  = parentLists.first();

					// in case it's a list with only a single <li>
					if (parentLists.length > 1 ||
						parentList.children().length > 1) {
						return 0;
					}

					if ($firstBlock.is('ul,ol,menu')) {
						// if the whole list is selected, then this must be
						// invalidated because the browser will place a
						// <blockquote> there
						range = this.getRangeHelper().selectedRange();

						if (window.Range && range instanceof Range) {
							startParent = range.startContainer.parentNode;
							endParent   = range.endContainer.parentNode;

	// TODO: could use nodeType for this?
	// Maybe just check the firstBlock contins both the start and end conatiners
							// Select the tag, not the textNode
							// (that's why the parentNode)
							if (startParent !==
								startParent.parentNode.firstElementChild ||
								// work around a bug in FF
								($(endParent).is('li') && endParent !==
									endParent.parentNode.lastElementChild)) {
								return 0;
							}
						// it's IE... As it is impossible to know well when to
						// accept, better safe than sorry
						} else {
							return $firstBlock.is('li,ul,ol,menu') ? 0 : -1;
						}
					}

					return -1;
				},
				exec: function () {
					var editor = this,
						$elm   = $(editor.getRangeHelper().getFirstBlockParent());

					editor.focus();

					// An indent system is quite complicated as there are loads
					// of complications and issues around how to indent text
					// As default, let's just stay with indenting the lists,
					// at least, for now.
					if ($elm.parents('ul,ol,menu')) {
						editor.execCommand('indent');
					}
				},
				tooltip: 'Add indent'
			},
			// END_COMMAND
			// START_COMMAND: Outdent
			outdent: {
				state: function (parents, firstBlock) {
					return $(firstBlock).is('ul,ol,menu') ||
						$(firstBlock).parents('ul,ol,menu').length > 0 ? 0 : -1;
				},
				exec: function () {
					var	editor = this,
						$elm   = $(editor.getRangeHelper().getFirstBlockParent());

					if ($elm.parents('ul,ol,menu')) {
						editor.execCommand('outdent');
					}
				},
				tooltip: 'Remove one indent'
			},
			// END_COMMAND

			// START_COMMAND: Table
			table: {
				forceNewLineAfter: ['table'],
				exec: function (caller) {
					var	editor  = this,
						content = _tmpl('table', {
							rows: editor._('Rows:'),
							cols: editor._('Cols:'),
							insert: editor._('Insert')
						}, true);

					content.find('.button').click(function (e) {
						var	row, col,
							rows = content.find('#rows').val() - 0,
							cols = content.find('#cols').val() - 0,
							html = '<table>';

						if (rows < 1 || cols < 1) {
							return;
						}

						for (row = 0; row < rows; row++) {
							html += '<tr>';

							for (col = 0; col < cols; col++) {
								html += '<td>' +
										(IE_BR_FIX ? '' : '<br />') +
									'</td>';
							}

							html += '</tr>';
						}

						html += '</table>';

						editor.wysiwygEditorInsertHtml(html);
						editor.closeDropDown(true);
						e.preventDefault();
					});

					editor.createDropDown(caller, 'inserttable', content);
				},
				tooltip: 'Insert a table'
			},
			// END_COMMAND

			// START_COMMAND: Horizontal Rule
			horizontalrule: {
				exec: 'inserthorizontalrule',
				tooltip: 'Insert a horizontal rule'
			},
			// END_COMMAND

			// START_COMMAND: Code
			code: {
				codeInputModeInside: 'code',
				forceNewLineAfter: ['code'],
				exec: function () {
					this.wysiwygEditorInsertHtml(
						'<code>', '</code>'
					);
				},
				tooltip: 'Code'
			},
			// END_COMMAND

			// START_COMMAND: Image
			image: {
				exec: function (caller) {
					var	editor  = this,
						content = _tmpl('image', {
							url: editor._('URL:'),
							width: editor._('Width (optional):'),
							height: editor._('Height (optional):'),
							insert: editor._('Insert')
						}, true);

					content.find('.button').click(function (e) {
						var	val    = content.find('#image').val(),
							width  = content.find('#width').val(),
							height = content.find('#height').val(),
							attrs  = '';

						if (width) {
							attrs += ' width="' + width + '"';
						}

						if (height) {
							attrs += ' height="' + height + '"';
						}

						if (val) {
							editor.wysiwygEditorInsertHtml(
								'<img' + attrs + ' src="' + val + '" />'
							);
						}

						editor.closeDropDown(true);
						e.preventDefault();
					});

					editor.createDropDown(caller, 'insertimage', content);
				},
				tooltip: 'Insert an image'
			},
			// END_COMMAND

			// START_COMMAND: E-mail
			email: {
				exec: function (caller) {
					var	editor  = this,
						content = _tmpl('email', {
							label: editor._('E-mail:'),
							desc: editor._('Description (optional):'),
							insert: editor._('Insert')
						}, true);

					content.find('.button').click(function (e) {
						var val         = content.find('#email').val(),
							description = content.find('#des').val();

						if (val) {
							// needed for IE to reset the last range
							editor.focus();

							if (!editor.getRangeHelper().selectedHtml() ||
								description) {
								description = description || val;

								editor.wysiwygEditorInsertHtml(
									'<a href="' + 'mailto:' + val + '">' +
										description +
									'</a>'
								);
							} else {
								editor.execCommand('createlink', 'mailto:' + val);
							}
						}

						editor.closeDropDown(true);
						e.preventDefault();
					});

					editor.createDropDown(caller, 'insertemail', content);
				},
				tooltip: 'Insert an email'
			},
			// END_COMMAND

			// START_COMMAND: Link
			link: {
				exec: function (caller) {
					var	editor  = this,
						content = _tmpl('link', {
							url: editor._('URL:'),
							desc: editor._('Description (optional):'),
							ins: editor._('Insert')
						}, true);

					content.find('.button').click(function (e) {
						var	val         = content.find('#link').val(),
							description = content.find('#des').val();

						if (val) {
							// needed for IE to restore the last range
							editor.focus();

							// If there is no selected text then must set the URL as
							// the text. Most browsers do this automatically, sadly
							// IE doesn't.
							if (!editor.getRangeHelper().selectedHtml() ||
								description) {
								description = description || val;

								editor.wysiwygEditorInsertHtml(
									'<a href="' + val + '">' + description + '</a>'
								);
							} else {
								editor.execCommand('createlink', val);
							}
						}

						editor.closeDropDown(true);
						e.preventDefault();
					});

					editor.createDropDown(caller, 'insertlink', content);
				},
				tooltip: 'Insert a link'
			},
			// END_COMMAND

			// START_COMMAND: Unlink
			unlink: {
				state: function () {
					var $current = $(this.currentNode());
					return $current.is('a') ||
						$current.parents('a').length > 0 ? 0 : -1;
				},
				exec: function () {
					var	$current = $(this.currentNode()),
						$anchor  = $current.is('a') ? $current :
							$current.parents('a').first();

					if ($anchor.length) {
						$anchor.replaceWith($anchor.contents());
					}
				},
				tooltip: 'Unlink'
			},
			// END_COMMAND


			// START_COMMAND: Quote
			quote: {
				forceNewLineAfter: ['blockquote'],
				exec: function (caller, html, author) {
					var	before = '<blockquote>',
						end    = '</blockquote>';

					// if there is HTML passed set end to null so any selected
					// text is replaced
					if (html) {
						author = (author ? '<cite>' + author + '</cite>' : '');
						before = before + author + html + end;
						end    = null;
					// if not add a newline to the end of the inserted quote
					} else if (this.getRangeHelper().selectedHtml() === '') {
						end = (IE_BR_FIX ? '' : '<br />') + end;
					}

					this.wysiwygEditorInsertHtml(before, end);
				},
				tooltip: 'Insert a Quote'
			},
			// END_COMMAND

			// START_COMMAND: Emoticons
			emoticon: {
				exec: function (caller) {
					var editor = this;

					var createContent = function (includeMore) {
						var	$moreLink,
							emoticonsCompat = editor.opts.emoticonsCompat,
							rangeHelper     = editor.getRangeHelper(),
							startSpace      = emoticonsCompat &&
								rangeHelper.getOuterText(true, 1) !== ' ' ?
								' ' : '',
							endSpace        = emoticonsCompat &&
								rangeHelper.getOuterText(false, 1) !== ' ' ?
								' ' : '',
							$content        = $('<div />'),
							$line           = $('<div />').appendTo($content),
							perLine         = 0,
							emoticons       = $.extend(
								{},
								editor.opts.emoticons.dropdown,
								includeMore ? editor.opts.emoticons.more : {}
							);

						$.each(emoticons, function () {
							perLine++;
						});
						perLine = Math.sqrt(perLine);

						$.each(emoticons, function (code, emoticon) {
							$line.append(
								$('<img />').attr({
									src: emoticon.url || emoticon,
									alt: code,
									title: emoticon.tooltip || code
								}).click(function () {
									editor.insert(startSpace + $(this).attr('alt') +
										endSpace, null, false).closeDropDown(true);

									return false;
								})
							);

							if ($line.children().length >= perLine) {
								$line = $('<div />').appendTo($content);
							}
						});

						if (!includeMore) {
							$moreLink = $(
								'<a class="sceditor-more">' +
									editor._('More') + '</a>'
							).click(function () {
								editor.createDropDown(
									caller,
									'more-emoticons',
									createContent(true)
								);

								return false;
							});

							$content.append($moreLink);
						}

						return $content;
					};

					editor.createDropDown(
						caller,
						'emoticons',
						createContent(false)
					);
				},
				txtExec: function (caller) {
					defaultCommnds.emoticon.exec.call(this, caller);
				},
				tooltip: 'Insert an emoticon'
			},
			// END_COMMAND

			// START_COMMAND: YouTube
			youtube: {
				_dropDown: function (editor, caller, handleIdFunc) {
					var	matches,
						content = _tmpl('youtubeMenu', {
							label: editor._('Video URL:'),
							insert: editor._('Insert')
						}, true);

					content.find('.button').click(function (e) {
						var val = content
							.find('#link')
							.val();

						if (val) {
							matches = val.match(
								/(?:v=|v\/|embed\/|youtu.be\/)(.{11})/
							);

							if (matches) {
								val = matches[1];
							}

							if (/^[a-zA-Z0-9_\-]{11}$/.test(val)) {
								handleIdFunc(val);
							} else {
								/*global alert:false*/
								alert('Invalid YouTube video');
							}
						}

						editor.closeDropDown(true);
						e.preventDefault();
					});

					editor.createDropDown(caller, 'insertlink', content);
				},
				exec: function (caller) {
					var editor = this;

					defaultCommnds.youtube._dropDown(
						editor,
						caller,
						function (id) {
							editor.wysiwygEditorInsertHtml(_tmpl('youtube', {
								id: id
							}));
						}
					);
				},
				tooltip: 'Insert a YouTube video'
			},
			// END_COMMAND

			// START_COMMAND: Date
			date: {
				_date: function (editor) {
					var	now   = new Date(),
						year  = now.getYear(),
						month = now.getMonth() + 1,
						day   = now.getDate();

					if (year < 2000) {
						year = 1900 + year;
					}

					if (month < 10) {
						month = '0' + month;
					}

					if (day < 10) {
						day = '0' + day;
					}

					return editor.opts.dateFormat
						.replace(/year/i, year)
						.replace(/month/i, month)
						.replace(/day/i, day);
				},
				exec: function () {
					this.insertText(defaultCommnds.date._date(this));
				},
				txtExec: function () {
					this.insertText(defaultCommnds.date._date(this));
				},
				tooltip: 'Insert current date'
			},
			// END_COMMAND

			// START_COMMAND: Time
			time: {
				_time: function () {
					var	now   = new Date(),
						hours = now.getHours(),
						mins  = now.getMinutes(),
						secs  = now.getSeconds();

					if (hours < 10) {
						hours = '0' + hours;
					}

					if (mins < 10) {
						mins = '0' + mins;
					}

					if (secs < 10) {
						secs = '0' + secs;
					}

					return hours + ':' + mins + ':' + secs;
				},
				exec: function () {
					this.insertText(defaultCommnds.time._time());
				},
				txtExec: function () {
					this.insertText(defaultCommnds.time._time());
				},
				tooltip: 'Insert current time'
			},
			// END_COMMAND


			// START_COMMAND: Ltr
			ltr: {
				state: function (parents, firstBlock) {
					return firstBlock && firstBlock.style.direction === 'ltr';
				},
				exec: function () {
					var	editor = this,
						elm    = editor.getRangeHelper().getFirstBlockParent(),
						$elm   = $(elm);

					editor.focus();

					if (!elm || $elm.is('body')) {
						editor.execCommand('formatBlock', 'p');

						elm  = editor.getRangeHelper().getFirstBlockParent();
						$elm = $(elm);

						if (!elm || $elm.is('body')) {
							return;
						}
					}

					if ($elm.css('direction') === 'ltr') {
						$elm.css('direction', '');
					} else {
						$elm.css('direction', 'ltr');
					}
				},
				tooltip: 'Left-to-Right'
			},
			// END_COMMAND

			// START_COMMAND: Rtl
			rtl: {
				state: function (parents, firstBlock) {
					return firstBlock && firstBlock.style.direction === 'rtl';
				},
				exec: function () {
					var	editor = this,
						elm    = editor.getRangeHelper().getFirstBlockParent(),
						$elm   = $(elm);

					editor.focus();

					if (!elm || $elm.is('body')) {
						editor.execCommand('formatBlock', 'p');

						elm  = editor.getRangeHelper().getFirstBlockParent();
						$elm = $(elm);

						if (!elm || $elm.is('body')) {
							return;
						}
					}

					if ($elm.css('direction') === 'rtl') {
						$elm.css('direction', '');
					} else {
						$elm.css('direction', 'rtl');
					}
				},
				tooltip: 'Right-to-Left'
			},
			// END_COMMAND


			// START_COMMAND: Print
			print: {
				exec: 'print',
				tooltip: 'Print'
			},
			// END_COMMAND

			// START_COMMAND: Maximize
			maximize: {
				state: function () {
					return this.maximize();
				},
				exec: function () {
					this.maximize(!this.maximize());
				},
				txtExec: function () {
					this.maximize(!this.maximize());
				},
				tooltip: 'Maximize',
				shortcut: 'Ctrl+Shift+M'
			},
			// END_COMMAND

			// START_COMMAND: Source
			source: {
				state: function () {
					return this.sourceMode();
				},
				exec: function () {
					this.toggleSourceMode();
				},
				txtExec: function () {
					this.toggleSourceMode();
				},
				tooltip: 'View source',
				shortcut: 'Ctrl+Shift+S'
			},
			// END_COMMAND

			// this is here so that commands above can be removed
			// without having to remove the , after the last one.
			// Needed for IE.
			ignore: {}
		};

		return defaultCommnds;
	}.call(exports, __webpack_require__, exports, module), __WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ },
/* 7 */
/***/ function(module, exports, __webpack_require__) {

	var __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_RESULT__ = function (require) {
		'use strict';

		var $ = __webpack_require__(1);


		/**
		 * Default options for SCEditor
		 * @type {Object}
		 */
		return {
			/** @lends jQuery.sceditor.defaultOptions */
			/**
			 * Toolbar buttons order and groups. Should be comma separated and
			 * have a bar | to separate groups
			 *
			 * @type {String}
			 */
			toolbar: 'bold,italic,underline,strike,subscript,superscript|' +
				'left,center,right,justify|font,size,color,removeformat|' +
				'cut,copy,paste,pastetext|bulletlist,orderedlist,indent,outdent|' +
				'table|code,quote|horizontalrule,image,email,link,unlink|' +
				'emoticon,youtube,date,time|ltr,rtl|print,maximize,source',

			/**
			 * Comma separated list of commands to excludes from the toolbar
			 *
			 * @type {String}
			 */
			toolbarExclude: null,

			/**
			 * Stylesheet to include in the WYSIWYG editor. This is what will style
			 * the WYSIWYG elements
			 *
			 * @type {String}
			 */
			style: 'jquery.sceditor.default.css',

			/**
			 * Comma separated list of fonts for the font selector
			 *
			 * @type {String}
			 */
			fonts: 'Arial,Arial Black,Comic Sans MS,Courier New,Georgia,Impact,' +
				'Sans-serif,Serif,Times New Roman,Trebuchet MS,Verdana',

			/**
			 * Colors should be comma separated and have a bar | to signal a new
			 * column.
			 *
			 * If null the colors will be auto generated.
			 *
			 * @type {string}
			 */
			colors: null,

			/**
			 * The locale to use.
			 * @type {String}
			 */
			locale: $('html').attr('lang') || 'en',

			/**
			 * The Charset to use
			 * @type {String}
			 */
			charset: 'utf-8',

			/**
			 * Compatibility mode for emoticons.
			 *
			 * Helps if you have emoticons such as :/ which would put an emoticon
			 * inside http://
			 *
			 * This mode requires emoticons to be surrounded by whitespace or end of
			 * line chars. This mode has limited As You Type emoticon conversion
			 * support. It will not replace AYT for end of line chars, only
			 * emoticons surrounded by whitespace. They will still be replaced
			 * correctly when loaded just not AYT.
			 *
			 * @type {Boolean}
			 */
			emoticonsCompat: false,

			/**
			 * If to enable emoticons. Can be changes at runtime using the
			 * emoticons() method.
			 *
			 * @type {Boolean}
			 * @since 1.4.2
			 */
			emoticonsEnabled: true,

			/**
			 * Emoticon root URL
			 *
			 * @type {String}
			 */
			emoticonsRoot: '',
			emoticons: {
				dropdown: {
					':)': 'emoticons/smile.png',
					':angel:': 'emoticons/angel.png',
					':angry:': 'emoticons/angry.png',
					'8-)': 'emoticons/cool.png',
					':\'(': 'emoticons/cwy.png',
					':ermm:': 'emoticons/ermm.png',
					':D': 'emoticons/grin.png',
					'<3': 'emoticons/heart.png',
					':(': 'emoticons/sad.png',
					':O': 'emoticons/shocked.png',
					':P': 'emoticons/tongue.png',
					';)': 'emoticons/wink.png'
				},
				more: {
					':alien:': 'emoticons/alien.png',
					':blink:': 'emoticons/blink.png',
					':blush:': 'emoticons/blush.png',
					':cheerful:': 'emoticons/cheerful.png',
					':devil:': 'emoticons/devil.png',
					':dizzy:': 'emoticons/dizzy.png',
					':getlost:': 'emoticons/getlost.png',
					':happy:': 'emoticons/happy.png',
					':kissing:': 'emoticons/kissing.png',
					':ninja:': 'emoticons/ninja.png',
					':pinch:': 'emoticons/pinch.png',
					':pouty:': 'emoticons/pouty.png',
					':sick:': 'emoticons/sick.png',
					':sideways:': 'emoticons/sideways.png',
					':silly:': 'emoticons/silly.png',
					':sleeping:': 'emoticons/sleeping.png',
					':unsure:': 'emoticons/unsure.png',
					':woot:': 'emoticons/w00t.png',
					':wassat:': 'emoticons/wassat.png'
				},
				hidden: {
					':whistling:': 'emoticons/whistling.png',
					':love:': 'emoticons/wub.png'
				}
			},

			/**
			 * Width of the editor. Set to null for automatic with
			 *
			 * @type {int}
			 */
			width: null,

			/**
			 * Height of the editor including toolbar. Set to null for automatic
			 * height
			 *
			 * @type {int}
			 */
			height: null,

			/**
			 * If to allow the editor to be resized
			 *
			 * @type {Boolean}
			 */
			resizeEnabled: true,

			/**
			 * Min resize to width, set to null for half textarea width or -1 for
			 * unlimited
			 *
			 * @type {int}
			 */
			resizeMinWidth: null,
			/**
			 * Min resize to height, set to null for half textarea height or -1 for
			 * unlimited
			 *
			 * @type {int}
			 */
			resizeMinHeight: null,
			/**
			 * Max resize to height, set to null for double textarea height or -1
			 * for unlimited
			 *
			 * @type {int}
			 */
			resizeMaxHeight: null,
			/**
			 * Max resize to width, set to null for double textarea width or -1 for
			 * unlimited
			 *
			 * @type {int}
			 */
			resizeMaxWidth: null,
			/**
			 * If resizing by height is enabled
			 *
			 * @type {Boolean}
			 */
			resizeHeight: true,
			/**
			 * If resizing by width is enabled
			 *
			 * @type {Boolean}
			 */
			resizeWidth: true,

			/**
			 * Date format, will be overridden if locale specifies one.
			 *
			 * The words year, month and day will be replaced with the users current
			 * year, month and day.
			 *
			 * @type {String}
			 */
			dateFormat: 'year-month-day',

			/**
			 * Element to inset the toolbar into.
			 *
			 * @type {HTMLElement}
			 */
			toolbarContainer: null,

			/**
			 * If to enable paste filtering. This is currently experimental, please
			 * report any issues.
			 *
			 * @type {Boolean}
			 */
			enablePasteFiltering: false,

			/**
			 * If to completely disable pasting into the editor
			 *
			 * @type {Boolean}
			 */
			disablePasting: false,

			/**
			 * If the editor is read only.
			 *
			 * @type {Boolean}
			 */
			readOnly: false,

			/**
			 * If to set the editor to right-to-left mode.
			 *
			 * If set to null the direction will be automatically detected.
			 *
			 * @type {Boolean}
			 */
			rtl: false,

			/**
			 * If to auto focus the editor on page load
			 *
			 * @type {Boolean}
			 */
			autofocus: false,

			/**
			 * If to auto focus the editor to the end of the content
			 *
			 * @type {Boolean}
			 */
			autofocusEnd: true,

			/**
			 * If to auto expand the editor to fix the content
			 *
			 * @type {Boolean}
			 */
			autoExpand: false,

			/**
			 * If to auto update original textbox on blur
			 *
			 * @type {Boolean}
			 */
			autoUpdate: false,

			/**
			 * If to enable the browsers built in spell checker
			 *
			 * @type {Boolean}
			 */
			spellcheck: true,

			/**
			 * If to run the source editor when there is no WYSIWYG support. Only
			 * really applies to mobile OS's.
			 *
			 * @type {Boolean}
			 */
			runWithoutWysiwygSupport: false,

			/**
			 * If to load the editor in source mode and still allow switching
			 * between WYSIWYG and source mode
			 *
			 * @type {Boolean}
			 */
			startInSourceMode: false,

			/**
			 * Optional ID to give the editor.
			 *
			 * @type {String}
			 */
			id: null,

			/**
			 * Comma separated list of plugins
			 *
			 * @type {String}
			 */
			plugins: '',

			/**
			 * z-index to set the editor container to. Needed for jQuery UI dialog.
			 *
			 * @type {Int}
			 */
			zIndex: null,

			/**
			 * If to trim the BBCode. Removes any spaces at the start and end of the
			 * BBCode string.
			 *
			 * @type {Boolean}
			 */
			bbcodeTrim: false,

			/**
			 * If to disable removing block level elements by pressing backspace at
			 * the start of them
			 *
			 * @type {Boolean}
			 */
			disableBlockRemove: false,

			/**
			 * BBCode parser options, only applies if using the editor in BBCode
			 * mode.
			 *
			 * See SCEditor.BBCodeParser.defaults for list of valid options
			 *
			 * @type {Object}
			 */
			parserOptions: { },

			/**
			 * CSS that will be added to the to dropdown menu (eg. z-index)
			 *
			 * @type {Object}
			 */
			dropDownCss: { }
		};
	}.call(exports, __webpack_require__, exports, module), __WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ },
/* 8 */
/***/ function(module, exports, __webpack_require__) {

	var __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_RESULT__ = function (require) {
		'use strict';

		var $       = __webpack_require__(1);
		var dom     = __webpack_require__(9);
		var escape  = __webpack_require__(5);
		var browser = __webpack_require__(4);

		var IE_VER = browser.ie;

		// In IE < 11 a BR at the end of a block level element
		// causes a line break. In all other browsers it's collapsed.
		var IE_BR_FIX = IE_VER && IE_VER < 11;


		var _nodeToHtml = function (node) {
			return $('<p>', node.ownerDocument).append(node).html();
		};

		/**
		 * Gets the text, start/end node and offset for
		 * length chars left or right of the passed node
		 * at the specified offset.
		 *
		 * @param  {Node}  node
		 * @param  {Number}  offset
		 * @param  {Boolean} isLeft
		 * @param  {Number}  length
		 * @return {Object}
		 * @private
		 */
		var outerText = function (range, isLeft, length) {
			var nodeValue, remaining, start, end, node,
				text = '',
				next = range.startContainer,
				offset = range.startOffset;

			// Handle cases where node is a paragraph and offset
			// refers to the index of a text node.
			// 3 = text node
			if (next && next.nodeType !== 3) {
				next = next.childNodes[offset];
				offset = 0;
			}

			start = end = offset;

			while (length > text.length && next && next.nodeType === 3) {
				nodeValue = next.nodeValue;
				remaining = length - text.length;

				// If not the first node, start and end should be at their
				// max values as will be updated when getting the text
				if (node) {
					end = nodeValue.length;
					start = 0;
				}

				node = next;

				if (isLeft) {
					start = Math.max(end - remaining, 0);
					offset = start;

					text = nodeValue.substr(start, end - start) + text;
					next = node.previousSibling;
				} else {
					end = Math.min(remaining, nodeValue.length);
					offset = start + end;

					text += nodeValue.substr(start, end);
					next = node.nextSibling;
				}
			}

			return {
				node: node || next,
				offset: offset,
				text: text
			};
		};

		var RangeHelper = function (w, d) {
			var	_createMarker, _isOwner, _prepareInput,
				doc          = d || w.contentDocument || w.document,
				win          = w,
				isW3C        = !!w.getSelection,
				startMarker  = 'sceditor-start-marker',
				endMarker    = 'sceditor-end-marker',
				CHARACTER    = 'character', // Used to improve minification
				base         = this;

			/**
			 * <p>Inserts HTML into the current range replacing any selected
			 * text.</p>
			 *
			 * <p>If endHTML is specified the selected contents will be put between
			 * html and endHTML. If there is nothing selected html and endHTML are
			 * just concated together.</p>
			 *
			 * @param {string} html
			 * @param {string} endHTML
			 * @return False on fail
			 * @function
			 * @name insertHTML
			 * @memberOf RangeHelper.prototype
			 */
			base.insertHTML = function (html, endHTML) {
				var	node, div,
					range = base.selectedRange();

				if (!range) {
					return false;
				}

				if (isW3C) {
					if (endHTML) {
						html += base.selectedHtml() + endHTML;
					}

					div           = doc.createElement('p');
					node          = doc.createDocumentFragment();
					div.innerHTML = html;

					while (div.firstChild) {
						node.appendChild(div.firstChild);
					}

					base.insertNode(node);
				} else {
					range.pasteHTML(_prepareInput(html, endHTML, true));
					base.restoreRange();
				}
			};

			/**
			 * Prepares HTML to be inserted by adding a zero width space
			 * if the last child is empty and adding the range start/end
			 * markers to the last child.
			 *
			 * @param  {Node|string} node
			 * @param  {Node|string} endNode
			 * @param  {boolean} returnHtml
			 * @return {Node|string}
			 * @private
			 */
			_prepareInput = function (node, endNode, returnHtml) {
				var lastChild, $lastChild,
					div  = doc.createElement('div'),
					$div = $(div);

				if (typeof node === 'string') {
					if (endNode) {
						node += base.selectedHtml() + endNode;
					}

					$div.html(node);
				} else {
					$div.append(node);

					if (endNode) {
						$div
							.append(base.selectedRange().extractContents())
							.append(endNode);
					}
				}

				if (!(lastChild = div.lastChild)) {
					return;
				}

				while (!dom.isInline(lastChild.lastChild, true)) {
					lastChild = lastChild.lastChild;
				}

				if (dom.canHaveChildren(lastChild)) {
					$lastChild = $(lastChild);

					// IE <= 8 and Webkit won't allow the cursor to be placed
					// inside an empty tag, so add a zero width space to it.
					if (!lastChild.lastChild) {
						$lastChild.append('\u200B');
					}
				}

				// Needed so IE <= 8 can place the cursor after emoticons and images
				if (IE_VER && IE_VER < 9 && $(lastChild).is('img')) {
					$div.append('\u200B');
				}

				base.removeMarkers();

				// Append marks to last child so when restored cursor will be in
				// the right place
				($lastChild || $div)
					.append(_createMarker(startMarker))
					.append(_createMarker(endMarker));

				if (returnHtml) {
					return $div.html();
				}

				return $(doc.createDocumentFragment()).append($div.contents())[0];
			};

			/**
			 * <p>The same as insertHTML except with DOM nodes instead</p>
			 *
			 * <p><strong>Warning:</strong> the nodes must belong to the
			 * document they are being inserted into. Some browsers
			 * will throw exceptions if they don't.</p>
			 *
			 * @param {Node} node
			 * @param {Node} endNode
			 * @return False on fail
			 * @function
			 * @name insertNode
			 * @memberOf RangeHelper.prototype
			 */
			base.insertNode = function (node, endNode) {
				if (isW3C) {
					var	input  = _prepareInput(node, endNode),
						range  = base.selectedRange(),
						parent = range.commonAncestorContainer;

					if (!input) {
						return false;
					}

					range.deleteContents();

					// FF allows <br /> to be selected but inserting a node
					// into <br /> will cause it not to be displayed so must
					// insert before the <br /> in FF.
					// 3 = TextNode
					if (parent && parent.nodeType !== 3 &&
						!dom.canHaveChildren(parent)) {
						parent.parentNode.insertBefore(input, parent);
					} else {
						range.insertNode(input);
					}

					base.restoreRange();
				} else {
					base.insertHTML(
						_nodeToHtml(node),
						endNode ? _nodeToHtml(endNode) : null
					);
				}
			};

			/**
			 * <p>Clones the selected Range</p>
			 *
			 * <p>IE <= 8 will return a TextRange, all other browsers
			 * will return a Range object.</p>
			 *
			 * @return {Range|TextRange}
			 * @function
			 * @name cloneSelected
			 * @memberOf RangeHelper.prototype
			 */
			base.cloneSelected = function () {
				var range = base.selectedRange();

				if (range) {
					return isW3C ? range.cloneRange() : range.duplicate();
				}
			};

			base.userSelectionFix = function () {
				var range = base.selectedRange();
				if(!range){
					return range;
				}

				var rangeInfo = {
					'collapsed': range.collapsed,
					'startContainer': range.startContainer,
					'startOffset': range.startOffset,
					'endContainer': range.endContainer,
					'endOffset': range.endOffset,
					'commonAncestorContainer': range.commonAncestorContainer
				}
				if(!range.collapsed){
					if(range.startOffset > 0 && range.startContainer.nodeValue.length === range.startOffset){
						var newStart = range.startContainer;

						do {
							if (newStart.nextSibling){
								newStart = newStart.nextSibling;
							}else{
								newStart = newStart.parentNode;
								newStart = newStart.nextSibling;
							}
						} while (
							newStart.nodeType === Node.TEXT_NODE &&
							newStart.nodeValue.length === 0
						);

						while(newStart.firstChild){
							newStart = newStart.firstChild;
						}

						rangeInfo.startContainer = newStart;
						rangeInfo.startOffset = 0;
					}

					if(range.endOffset == 0){
						var newEnd = range.endContainer;

						do {
							if (newEnd.previousSibling){
								newEnd = newEnd.previousSibling;
							} else {
								newEnd = newEnd.parentNode;
								newEnd = newEnd.previousSibling;
							}
						} while (
							newEnd.nodeType === Node.TEXT_NODE &&
							newEnd.nodeValue.length === 0
						);

						while (newEnd.firstChild){
							newEnd = newEnd.firstChild;
						}

						if	(newEnd.nodeType === Node.TEXT_NODE){
							rangeInfo.endContainer = newEnd;
							rangeInfo.endOffset = newEnd.nodeValue.length;
						} else {
							rangeInfo.endContainer = newEnd;
							rangeInfo.endOffset = 0;
						}
					}

					if ((range.startOffset > 0 &&
							range.startContainer.nodeValue.length ===
								range.startOffset) ||
						range.endOffset == 0){

						rangeInfo.commonAncestorContainer = dom.findCommonAncestor(
							rangeInfo.startContainer,
							rangeInfo.endContainer
						);
					}
				}
				return Object.freeze(rangeInfo);
			}

			/**
			 * <p>Gets the selected Range</p>
			 *
			 * <p>IE <= 8 will return a TextRange, all other browsers
			 * will return a Range object.</p>
			 *
			 * @return {Range|TextRange}
			 * @function
			 * @name selectedRange
			 * @memberOf RangeHelper.prototype
			 */
			base.selectedRange = function () {
				var	range, firstChild,
					sel = isW3C ? win.getSelection() : doc.selection;

				if (!sel) {
					return;
				}

				// When creating a new range, set the start to the first child
				// element of the body element to avoid errors in FF.
				if (sel.getRangeAt && sel.rangeCount <= 0) {
					firstChild = doc.body;
					while (firstChild.firstChild) {
						firstChild = firstChild.firstChild;
					}

					range = doc.createRange();
					// Must be setStartBefore otherwise it can cause infinite
					// loops with lists in WebKit. See issue 442
					range.setStartBefore(firstChild);

					sel.addRange(range);
				}

				if (isW3C) {
					range = sel.getRangeAt(0);
				}

				if (!isW3C && sel.type !== 'Control') {
					range = sel.createRange();
				}

				// IE fix to make sure only return selections that
				// are part of the WYSIWYG iframe
				return _isOwner(range) ? range : null;
			};

			/**
			 * Checks if an IE TextRange range belongs to
			 * this document or not.
			 *
			 * Returns true if the range isn't an IE range or
			 * if the range is null.
			 *
			 * @private
			 */
			_isOwner = function (range) {
				var parent;

				if (range && !isW3C) {
					parent = range.parentElement();
				}

				// IE fix to make sure only return selections
				// that are part of the WYSIWYG iframe
				return parent ?
					parent.ownerDocument === doc :
					true;
			};

			/**
			 * Gets if there is currently a selection
			 *
			 * @return {boolean}
			 * @function
			 * @name hasSelection
			 * @since 1.4.4
			 * @memberOf RangeHelper.prototype
			 */
			base.hasSelection = function () {
				var	sel = isW3C ? win.getSelection() : doc.selection;

				if (isW3C || !sel) {
					return sel && sel.rangeCount > 0;
				}

				return sel.type !== 'None' && _isOwner(sel.createRange());
			};

			/**
			 * Gets the currently selected HTML
			 *
			 * @return {string}
			 * @function
			 * @name selectedHtml
			 * @memberOf RangeHelper.prototype
			 */
			base.selectedHtml = function () {
				var	div,
					range = base.selectedRange();

				if (range) {

					// IE9+ and all other browsers
					if (isW3C) {
						div = doc.createElement('p');
						div.appendChild(range.cloneContents());

						return div.innerHTML;
					// IE < 9
					} else if (range.text !== '' && range.htmlText) {
						return range.htmlText;
					}
				}

				return '';
			};


			/**
			 * Replace elements with provided selector inside user selection with
			 * their children
			 *
			 * This method provides an easier way to have the same final aspect
			 * for HTML as powerful as the browser's native execCommand() method
			 * when removing HTML tag formatting.
			 *
			 * This method is mostly meant to be used with <b>inline</b> HTML
			 * Elements. Having it working with block level elements is not
			 * guaranteed.
			 *
			 *
			 * @param {String} removeElementSelector Selector match what to delete
			 * @return undefined
			 * @function
			 * @name splitAtSelection
			 * @memberOf RangeHelper.prototype
			 * @author brunoais
			 */
			base.splitAtSelection = function (removeElementSelector, howDeep) {

				var commonParent;
				if (howDeep){
					commonParent = howDeep;
				} else {
					commonParent = base.getFirstBlockParent();
				}

				base.insertMarkers();

				var startMarkerElement = base.getMarker(startMarker);
				var endMarkerElement = base.getMarker(endMarker);
				startMarkerElement.parentNode.normalize();

				dom.splitElement(commonParent, startMarkerElement);
				dom.splitElement(commonParent, endMarkerElement);

				if (!removeElementSelector) {
					base.removeMarkers();
					return;
				}

				// if the start is before the end marker, swap them
				if ($(endMarkerElement).nextAll('#' + startMarkerElement.id).length > 0){
					var swap = endMarkerElement;
					endMarkerElement = startMarkerElement;
					startMarkerElement = swap;
				}

				var parentOfRemoval = $(startMarkerElement).next()[0];

				while (parentOfRemoval && parentOfRemoval.id !== endMarker) {

					var removalElement = ($(parentOfRemoval).
						is(removeElementSelector) && parentOfRemoval) || null;

					var removalElements = $("[data-sce-onParentRemove]", parentOfRemoval);

					removalElements.each(function(i, elem){
						console.log(elem);
						var action = elem.getAttribute('data-sce-onParentRemove');
						switch(action){
							case 'unwrap':
								if(elem.firstChild){
									$(elem.firstChild).unwrap();
									return;
								}
								/* NO BREAK */
							case 'remove':
								$(elem).remove();
								return;
							case 'ignore':
							/* NOOP */
								return;
						}
					});
					// This has to be after querySelectorAll to allow specifying a
					// selector for multiple HTML tags forming the removal
					if (removalElement) {
						if(removalElement.firstChild){
							$(removalElement.firstChild).unwrap();
						} else {
							$(removalElement).remove();
						}
					}

					parentOfRemoval = $(parentOfRemoval).next()[0];

				}

				base.removeMarkers();
			};

			/**
			 * Gets the parent node of the selected contents in the range
			 *
			 * @return {HTMLElement}
			 * @function
			 * @name parentNode
			 * @memberOf RangeHelper.prototype
			 */
			base.parentNode = function () {
				var range = base.selectedRange();

				if (range) {
					return range.parentElement ?
						range.parentElement() :
						range.commonAncestorContainer;
				}
			};

			/**
			 * Gets the first block level parent of the selected
			 * contents of the range.
			 *
			 * @return {HTMLElement}
			 * @function
			 * @name getFirstBlockParent
			 * @memberOf RangeHelper.prototype
			 */
			/**
			 * Gets the first block level parent of the selected
			 * contents of the range.
			 *
			 * @param {Node} n The element to get the first block level parent from
			 * @return {HTMLElement}
			 * @function
			 * @name getFirstBlockParent^2
			 * @since 1.4.1
			 * @memberOf RangeHelper.prototype
			 */
			base.getFirstBlockParent = function (n) {
				var func = function (node) {
					if (!dom.isInline(node, true)) {
						return node;
					}

					node = node ? node.parentNode : null;

					return node ? func(node) : node;
				};

				return func(n || base.parentNode());
			};

			/**
			 * Inserts a node at either the start or end of the current selection
			 *
			 * @param {Bool} start
			 * @param {Node} node
			 * @function
			 * @name insertNodeAt
			 * @memberOf RangeHelper.prototype
			 */
			base.insertNodeAt = function (start, node) {
				var	currentRange = base.selectedRange(),
					range        = base.cloneSelected();

				if (!range) {
					return false;
				}

				range.collapse(start);

				if (isW3C) {
					range.insertNode(node);
				} else {
					range.pasteHTML(_nodeToHtml(node));
				}

				// Reselect the current range.
				// Fixes issue with Chrome losing the selection. Issue#82
				base.selectRange(currentRange);
			};

			/**
			 * Creates a marker node
			 *
			 * @param {string} id
			 * @return {Node}
			 * @private
			 */
			_createMarker = function (id) {
				base.removeMarker(id);

				var marker              = doc.createElement('span');
				marker.id               = id;
				marker.style.lineHeight = '0';
				marker.style.display    = 'none';
				marker.className        = 'sceditor-selection sceditor-ignore';
				marker.innerHTML        = ' ';

				return marker;
			};

			/**
			 * Inserts start/end markers for the current selection
			 * which can be used by restoreRange to re-select the
			 * range.
			 *
			 * @memberOf RangeHelper.prototype
			 * @function
			 * @name insertMarkers
			 */
			base.insertMarkers = function () {
				base.insertNodeAt(true, _createMarker(startMarker));
				base.insertNodeAt(false, _createMarker(endMarker));
			};

			/**
			 * Gets the marker with the specified ID
			 *
			 * @param {string} id
			 * @return {Node}
			 * @function
			 * @name getMarker
			 * @memberOf RangeHelper.prototype
			 */
			base.getMarker = function (id) {
				return doc.getElementById(id);
			};

			/**
			 * Removes the marker with the specified ID
			 *
			 * @param {string} id
			 * @function
			 * @name removeMarker
			 * @memberOf RangeHelper.prototype
			 */
			base.removeMarker = function (id) {
				var marker = base.getMarker(id);

				if (marker) {
					marker.parentNode.removeChild(marker);
				}
			};

			/**
			 * Removes the start/end markers
			 *
			 * @function
			 * @name removeMarkers
			 * @memberOf RangeHelper.prototype
			 */
			base.removeMarkers = function () {
				base.removeMarker(startMarker);
				base.removeMarker(endMarker);
			};

			/**
			 * Saves the current range location. Alias of insertMarkers()
			 *
			 * @function
			 * @name saveRage
			 * @memberOf RangeHelper.prototype
			 */
			base.saveRange = function () {
				base.insertMarkers();
			};

			/**
			 * Select the specified range
			 *
			 * @param {Range|TextRange} range
			 * @function
			 * @name selectRange
			 * @memberOf RangeHelper.prototype
			 */
			base.selectRange = function (range) {
				if (isW3C) {
					var lastChild;
					var sel = win.getSelection();
					var container = range.endContainer;

					// Check if cursor is set after a BR when the BR is the only
					// child of the parent. In Firefox this causes a line break
					// to occur when something is typed. See issue #321
					if (!IE_BR_FIX && range.collapsed && container &&
						!dom.isInline(container, true)) {

						lastChild = container.lastChild;
						while (lastChild && $(lastChild).is('.sceditor-ignore')) {
							lastChild = lastChild.previousSibling;
						}

						if ($(lastChild).is('br')) {
							var rng = doc.createRange();
							rng.setEndAfter(lastChild);
							rng.collapse(false);

							if (base.compare(range, rng)) {
								range.setStartBefore(lastChild);
								range.collapse(true);
							}
						}
					}

					if (sel) {
						base.clear();
						sel.addRange(range);
					}
				} else {
					range.select();
				}
			};

			/**
			 * Merges all text nodes in the specified tag and restores the
			 * caret into its original position.
			 * Best used with SCE's codeInputModeInside option
			 * Caution: This method removes ALL HTML inside the caret's
			 * parent tag.
			 *
			 * @function
			 * @name mergeTextNodesAtCaret
			 * @memberOf RangeHelper.prototype
			 */
			base.mergeTextNodesAtCaret = function () {

				var currentRange = base.selectedRange();
				var currentTextNode = currentRange.startContainer;

				var fullOffset = 0;
				var mrParent = currentTextNode.parentNode;
				var currentElem = currentTextNode.previousSibling;

				while (currentElem) {
					fullOffset += currentElem.length;
					currentElem = currentElem.previousSibling;
				}

				fullOffset += currentRange.startOffset;

				var text = currentTextNode.wholeText;
				currentTextNode.parentNode.textContent = text;

				base.placeCaretAt(mrParent.firstChild, fullOffset);
			};

			/**
			 * Places the caret in a specified position in the document
			 * without any selections
			 *
			 * @function
			 * @name placeCaretAt
			 * @memberOf RangeHelper.prototype
			 */
			base.placeCaretAt = function (container, offset) {
				var range = doc.createRange();
				range.setStart(container, offset);
				range.collapse(true);

				var selection = win.getSelection();
				selection.removeAllRanges();
				selection.addRange(range);
			};

			/**
			 * Restores the last range saved by saveRange() or insertMarkers()
			 *
			 * @function
			 * @name restoreRange
			 * @memberOf RangeHelper.prototype
			 */
			base.restoreRange = function () {
				var	marker, isCollapsed, previousSibling,
					range = base.selectedRange(),
					start = base.getMarker(startMarker),
					end   = base.getMarker(endMarker);

				if (!start || !end || !range) {
					return false;
				}

				isCollapsed = start.nextSibling === end;

				if (!isW3C) {
					range  = doc.body.createTextRange();
					marker = doc.body.createTextRange();

					// IE < 9 cannot set focus after a BR so need to insert
					// a dummy char after it to allow the cursor to be placed
					previousSibling = start.previousSibling;
					if (start.nextSibling === end && (!previousSibling ||
						!dom.isInline(previousSibling, true) ||
						$(previousSibling).is('br'))) {
						$(start).before('\u200B');
					}

					marker.moveToElementText(start);
					range.setEndPoint('StartToStart', marker);
					range.moveStart(CHARACTER, 0);

					marker.moveToElementText(end);
					range.setEndPoint('EndToStart', marker);
					range.moveEnd(CHARACTER, 0);
				} else {
					range = doc.createRange();
					range.setStartBefore(start);
					range.setEndAfter(end);
				}

				if (isCollapsed) {
					range.collapse(true);
				}

				base.selectRange(range);
				base.removeMarkers();
			};

			/**
			 * Selects the text left and right of the current selection
			 *
			 * @param {int} left
			 * @param {int} right
			 * @since 1.4.3
			 * @function
			 * @name selectOuterText
			 * @memberOf RangeHelper.prototype
			 */
			base.selectOuterText = function (left, right) {
				var start, end,
					range = base.cloneSelected();

				if (!range) {
					return false;
				}

				range.collapse(false);

				if (!isW3C) {
					range.moveStart(CHARACTER, 0 - left);
					range.moveEnd(CHARACTER, right);
				} else {
					start = outerText(range, true, left);
					end = outerText(range, false, right);

					range.setStart(start.node, start.offset);
					range.setEnd(end.node, end.offset);
				}

				base.selectRange(range);
			};

			/**
			 * Gets the text left or right of the current selection
			 *
			 * @param {boolean} before
			 * @param {number} length
			 * @since 1.4.3
			 * @function
			 * @name selectOuterText
			 * @memberOf RangeHelper.prototype
			 */
			base.getOuterText = function (before, length) {
				var	range = base.cloneSelected();

				if (!range) {
					return '';
				}

				range.collapse(!before);

				if (!isW3C) {
					if (range.startContainer.nodeType === Node.TEXT_NODE) {
						// In this case, I have to find the parent and count
						// all characters from previous nodes until this node

						var startPos,
							textContent;
						var currentContainer = range.startContainer;
						while (currentContainer.previousSibling) {
							startPos += currentContainer.previousSibling.
								nodeValue.length;
							currentContainer = currentContainer.previousSibling;
						}
						textContent = range.startContainer.wholeText;
					}

					if (before) {
						range.moveStart(CHARACTER, 0 - length);
					} else {
						range.moveEnd(CHARACTER, length);
					}

					return range.text;
				}

				return outerText(range, before, length).text;
			};

			/**
			 * Replaces keywords with values based on the current caret position
			 *
			 * @param {Array}   keywords
			 * @param {boolean} includeAfter      If to include the text after the
			 *                                    current caret position or just
			 *                                    text before
			 * @param {boolean} keywordsSorted    If the keywords array is pre
			 *                                    sorted shortest to longest
			 * @param {number}  longestKeyword    Length of the longest keyword
			 * @param {boolean} requireWhitespace If the key must be surrounded
			 *                                    by whitespace
			 * @param {string}  keypressChar      If this is being called from
			 *                                    a keypress event, this should be
			 *                                    set to the pressed character
			 * @return {boolean}
			 * @function
			 * @name raplaceKeyword
			 * @memberOf RangeHelper.prototype
			 */
			/*jshint maxparams: false*/
			base.raplaceKeyword = function (
				keywords,
				includeAfter,
				keywordsSorted,
				longestKeyword,
				requireWhitespace,
				keypressChar
			) {
				if (!keywordsSorted) {
					keywords.sort(function (a, b) {
						return a[0].length - b[0].length;
					});
				}

				var before, beforeAndAfter, matchPos, startPos, beforeLen,
					charsLeft, keyword, keywordLen,
					wsRegex    = '[\\s\xA0\u2002\u2003\u2009]',
					keywordIdx = keywords.length,
					maxKeyLen  = longestKeyword ||
						keywords[keywordIdx - 1][0].length;

				if (requireWhitespace) {
					// requireWhitespace doesn't work with textRanges as they
					// select text on the other side of elements causing
					// space-img-key to match when it shouldn't.
					if (!isW3C) {
						return false;
					}

					maxKeyLen++;
				}

				keypressChar   = keypressChar || '';
				before         = base.getOuterText(true, maxKeyLen);
				beforeLen      = before.length;
				beforeAndAfter = before + keypressChar;

				if (includeAfter) {
					beforeAndAfter += base.getOuterText(false, maxKeyLen);
				}

				while (keywordIdx--) {
					keyword      = keywords[keywordIdx][0];
					keywordLen   = keyword.length;
					startPos     = beforeLen - 1 - keywordLen;

					if (requireWhitespace) {
						matchPos = beforeAndAfter
							// Start position needs to be 1 char before to include
							// any previous whitespace
							.substr(Math.max(0, startPos - 1))
							.search(new RegExp(
								'(?:' + wsRegex + ')' +
								escape.regex(keyword) +
								'(?=' + wsRegex + ')'
							));
					} else {
						matchPos = beforeAndAfter.indexOf(keyword, startPos);
					}

					if (matchPos > -1) {
						if (requireWhitespace) {
							matchPos += startPos + 1;
						}

						// Make sure the substr is between before and
						// after not entirely in one or the other
						if (matchPos > beforeLen ||
							matchPos + keywordLen + (requireWhitespace ? 1 : 0) <
							beforeLen) {
							continue;
						}

						charsLeft = beforeLen - matchPos;

						base.selectOuterText(
							charsLeft,
							keywordLen - charsLeft -
								(/^\S/.test(keypressChar) ? 1 : 0)
						);

						base.insertHTML(keywords[keywordIdx][1]);
						return true;
					}
				}

				return false;
			};

			/**
			 * Compares two ranges.
			 *
			 * If rangeB is undefined it will be set to
			 * the current selected range
			 *
			 * @param  {Range|TextRange} rangeA
			 * @param  {Range|TextRange} rangeB
			 * @return {boolean}
			 */
			base.compare = function (rangeA, rangeB) {
				var	END_TO_END     = isW3C ? Range.END_TO_END : 'EndToEnd',
					START_TO_START = isW3C ? Range.START_TO_START : 'StartToStart',
					comparePoints  = isW3C ?
						'compareBoundaryPoints' :
						'compareEndPoints';

				if (!rangeB) {
					rangeB = base.selectedRange();
				}

				if (!rangeA || !rangeB) {
					return !rangeA && !rangeB;
				}

				return _isOwner(rangeA) && _isOwner(rangeB) &&
					rangeA[comparePoints](END_TO_END, rangeB) === 0 &&
					rangeA[comparePoints](START_TO_START, rangeB) === 0;
			};

			/**
			 * Removes any current selection
			 *
			 * @since 1.4.6
			 */
			base.clear = function () {
				var sel = isW3C ? win.getSelection() : doc.selection;

				if (sel) {
					if (sel.removeAllRanges) {
						sel.removeAllRanges();
					} else if (sel.empty) {
						sel.empty();
					}
				}
			};
		};

		return RangeHelper;
	}.call(exports, __webpack_require__, exports, module), __WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ },
/* 9 */
/***/ function(module, exports, __webpack_require__) {

	var __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_RESULT__ = function (require) {
		'use strict';

		var $       = __webpack_require__(1);
		var browser = __webpack_require__(4);

		var _propertyNameCache = {};

		var dom = {
			/**
			 * Loop all child nodes of the passed node
			 *
			 * The function should accept 1 parameter being the node.
			 * If the function returns false the loop will be exited.
			 *
			 * @param  {HTMLElement} node
			 * @param  {Function} func       Callback which is called with every
			 *                               child node as the first argument.
			 * @param  {bool} innermostFirst If the innermost node should be passed
			 *                               to the function before it's parents.
			 * @param  {bool} siblingsOnly   If to only traverse the nodes siblings
			 * @param  {bool} reverse        If to traverse the nodes in reverse
			 */
			/*jshint maxparams: false*/
			traverse: function (node, func, innermostFirst, siblingsOnly, reverse) {
				if (node) {
					node = reverse ? node.lastChild : node.firstChild;

					while (node) {
						var next = reverse ?
							node.previousSibling :
							node.nextSibling;

						if (
							(!innermostFirst && func(node) === false) ||
							(!siblingsOnly && dom.traverse(
								node, func, innermostFirst, siblingsOnly, reverse
							) === false) ||
							(innermostFirst && func(node) === false)
						) {
							return false;
						}

						// move to next child
						node = next;
					}
				}
			},

			/**
			 * Like traverse but loops in reverse
			 * @see traverse
			 */
			rTraverse: function (node, func, innermostFirst, siblingsOnly) {
				this.traverse(node, func, innermostFirst, siblingsOnly, true);
			},

			/**
			 * Parses HTML
			 *
			 * @param {String} html
			 * @param {Document} context
			 * @since 1.4.4
			 * @return {Array}
			 */
			parseHTML: function (html, context) {
				var	ret = [],
					tmp = (context || document).createElement('div');

				tmp.innerHTML = html;

				$.merge(ret, tmp.childNodes);

				return ret;
			},

			/**
			 * Checks if an element has any styling.
			 *
			 * It has styiling if it is not a plain <div> or <p> or
			 * if it has a class, style attribute or data.
			 *
			 * @param  {HTMLElement} elm
			 * @return {Boolean}
			 * @since 1.4.4
			 */
			hasStyling: function (elm) {
				var $elm = $(elm);

				return elm && (!$elm.is('p,div') || elm.className ||
					$elm.attr('style') || !$.isEmptyObject($elm.data()));
			},

			/**
			 * Converts an element from one type to another.
			 *
			 * For example it can convert the element <b> to <strong>
			 *
			 * @param  {HTMLElement} oldElm
			 * @param  {String}      toTagName
			 * @return {HTMLElement}
			 * @since 1.4.4
			 */
			convertElement: function (oldElm, toTagName) {
				var	child, attr,
					oldAttrs = oldElm.attributes,
					attrsIdx = oldAttrs.length,
					newElm   = oldElm.ownerDocument.createElement(toTagName);

				while (attrsIdx--) {
					attr = oldAttrs[attrsIdx];

					// IE < 8 returns all possible attribtues instead of just
					// the specified ones so have to check it is specified.
					if (!browser.ie || attr.specified) {
						// IE < 8 doesn't return the CSS for the style attribute
						// so must copy it manually
						if (browser.ie < 8 && /style/i.test(attr.name)) {
							dom.copyCSS(oldElm, newElm);
						} else {
							newElm.setAttribute(attr.name, attr.value);
						}
					}
				}

				while ((child = oldElm.firstChild)) {
					newElm.appendChild(child);
				}

				oldElm.parentNode.replaceChild(newElm, oldElm);

				return newElm;
			},

			/**
			 * List of block level elements separated by bars (|)
			 * @type {string}
			 */
			blockLevelList: '|body|hr|p|div|h1|h2|h3|h4|h5|h6|address|pre|form|' +
				'table|tbody|thead|tfoot|th|tr|td|li|ol|ul|blockquote|center|',

			/**
			 * List of elements that do not allow children separated by bars (|)
			 *
			 * @param {Node} node
			 * @return {bool}
			 * @since  1.4.5
			 */
			canHaveChildren: function (node) {
				// 1  = Element
				// 9  = Docuemnt
				// 11 = Document Fragment
				if (!/11?|9/.test(node.nodeType)) {
					return false;
				}

				// List of empty HTML tags seperated by bar (|) character.
				// Source: http://www.w3.org/TR/html4/index/elements.html
				// Source: http://www.w3.org/TR/html5/syntax.html#void-elements
				return ('|iframe|area|base|basefont|br|col|frame|hr|img|input|' +
					'isindex|link|meta|param|command|embed|keygen|source|track|' +
					'wbr|').indexOf('|' + node.nodeName.toLowerCase() + '|') < 0;
			},

			/**
			 * Checks if an element is inline
			 *
			 * @return {bool}
			 */
			isInline: function (elm, includeCodeAsBlock) {
				var tagName,
					nodeType = (elm || {}).nodeType || 3;

				if (nodeType !== 1) {
					return nodeType === 3;
				}

				tagName = elm.tagName.toLowerCase();

				if (tagName === 'code') {
					return !includeCodeAsBlock;
				}

				return dom.blockLevelList.indexOf('|' + tagName + '|') < 0;
			},

			/**
			 * Splits the HTML tree until commonParent closing all tags and
			 * reopening them after the split point.
			 *
			 * After calling this function, cutElement will be child of
			 * commonParent, nextSibling and previousSibling of the element
			 * which is the topmost parent of cutElement while still being
			 * descendant of commonParent.
			 *
			 * Example (examples show result of the .outerHTML parameter applied
			 * to "<strong>"):
			 * If the commonParent is "<strong>" and cutElement is
			 * "<!--break-->" in:
			 * <strong>hi there, how <em>are <span>y<!--break-->ou</span>
			 * doing</em> today</strong>
			 * After executing:
			 * <strong>hi there, how <em>are <span>y</span></em><!--break-->
			 * <em><span>ou</span> doing</em> today?</strong>
			 *
			 *
			 * @param {DOMElement} commonParent The common ancestor at clipping
			 * @param {DOMNode} cutElement Where the closing and re-opening happens
			 * @return undefined
			 * @function
			 * @name splitElement
			 * @author http://stackoverflow.com/a/27498178/551625
			 * @author brunoais
			 */
			splitElement: function (commonParent, cutElement) {
				var grandparent;
				for (var parent = cutElement.parentNode; commonParent !==
						parent; parent = grandparent) {
					var right = parent.cloneNode(false);

					while (cutElement.nextSibling) {
						right.appendChild(cutElement.nextSibling);
					}

					grandparent = parent.parentNode;
					grandparent.insertBefore(right, parent.nextSibling);
					grandparent.insertBefore(cutElement, right);
				}
			},

			/**
			 * <p>Copys the CSS from 1 node to another.</p>
			 *
			 * <p>Only copies CSS defined on the element e.g. style attr.</p>
			 *
			 * @param {HTMLElement} from
			 * @param {HTMLElement} to
			 */
			copyCSS: function (from, to) {
				to.style.cssText = from.style.cssText + to.style.cssText;
			},

			/**
			 * Fixes block level elements inside in inline elements.
			 *
			 * @param {HTMLElement} node
			 */
			fixNesting: function (node) {
				var	getLastInlineParent = function (node) {
					while (dom.isInline(node.parentNode, true)) {
						node = node.parentNode;
					}

					return node;
				};

				dom.traverse(node, function (node) {
					// Any blocklevel element inside an inline element needs fixing.
					if (node.nodeType === 1 && !dom.isInline(node, true) &&
						dom.isInline(node.parentNode, true)) {
						var	parent  = getLastInlineParent(node),
							rParent = parent.parentNode,
							before  = dom.extractContents(parent, node),
							middle  = node;

						// copy current styling so when moved out of the parent
						// it still has the same styling
						dom.copyCSS(parent, middle);

						rParent.insertBefore(before, parent);
						rParent.insertBefore(middle, parent);
					}
				});
			},

			/**
			 * Finds the common parent of two nodes
			 *
			 * @param {HTMLElement} node1
			 * @param {HTMLElement} node2
			 * @return {HTMLElement}
			 */
			findCommonAncestor: function (node1, node2) {
				// Not as fast as making two arrays of parents and comparing
				// but is a lot smaller and as it's currently only used with
				// fixing invalid nesting it doesn't need to be very fast
				return $(node1).parents().has($(node2)).first();
			},

			getSibling: function (node, previous) {
				if (!node) {
					return null;
				}

				return (previous ? node.previousSibling : node.nextSibling) ||
					dom.getSibling(node.parentNode, previous);
			},

			/**
			 * Removes unused whitespace from the root and all it's children
			 *
			 * @name removeWhiteSpace^1
			 * @param {HTMLElement} root
			 */
			/**
			 * Removes unused whitespace from the root and all it's children.
			 *
			 * If preserveNewLines is true, new line characters will not be removed
			 *
			 * @name removeWhiteSpace^2
			 * @param {HTMLElement} root
			 * @param {boolean}     preserveNewLines
			 * @since 1.4.3
			 */
			removeWhiteSpace: function (root, preserveNewLines) {
				var	nodeValue, nodeType, next, previous, previousSibling,
					cssWhiteSpace, nextNode, trimStart,
					getSibling        = dom.getSibling,
					isInline          = dom.isInline,
					node              = root.firstChild;

				while (node) {
					nextNode  = node.nextSibling;
					nodeValue = node.nodeValue;
					nodeType  = node.nodeType;

					// 1 = element
					if (nodeType === 1 && node.firstChild) {
						cssWhiteSpace = $(node).css('whiteSpace');

						// Skip all pre & pre-wrap with any vendor prefix
						if (!/pre(\-wrap)?$/i.test(cssWhiteSpace)) {
							dom.removeWhiteSpace(
								node,
								/line$/i.test(cssWhiteSpace)
							);
						}
					}

					// 3 = textnode
					if (nodeType === 3 && nodeValue) {
						next            = getSibling(node);
						previous        = getSibling(node, true);
						trimStart       = false;

						while ($(previous).hasClass('sceditor-ignore')) {
							previous = getSibling(previous, true);
						}
						// If previous sibling isn't inline or is a textnode that
						// ends in whitespace, time the start whitespace
						if (isInline(node) && previous) {
							previousSibling = previous;

							while (previousSibling.lastChild) {
								previousSibling = previousSibling.lastChild;
							}

							trimStart = previousSibling.nodeType === 3 ?
								/[\t\n\r ]$/.test(previousSibling.nodeValue) :
								!isInline(previousSibling);
						}

						// Clear zero width spaces
						nodeValue = nodeValue.replace(/\u200B/g, '');

						// Strip leading whitespace
						if (!previous || !isInline(previous) || trimStart) {
							nodeValue = nodeValue.replace(
								preserveNewLines ? /^[\t ]+/ : /^[\t\n\r ]+/,
								''
							);
						}

						// Strip trailing whitespace
						if (!next || !isInline(next)) {
							nodeValue = nodeValue.replace(
								preserveNewLines ? /[\t ]+$/ : /[\t\n\r ]+$/,
								''
							);
						}

						// Remove empty text nodes
						if (!nodeValue.length) {
							root.removeChild(node);
						} else {
							node.nodeValue = nodeValue.replace(
								preserveNewLines ? /[\t ]+/g : /[\t\n\r ]+/g,
								' '
							);
						}
					}

					node = nextNode;
				}
			},

			/**
			 * Extracts all the nodes between the start and end nodes
			 *
			 * @param {HTMLElement} startNode	The node to start extracting at
			 * @param {HTMLElement} endNode		The node to stop extracting at
			 * @return {DocumentFragment}
			 */
			extractContents: function (startNode, endNode) {
				var	extract,
					commonAncestor = dom
						.findCommonAncestor(startNode, endNode)
						.get(0),
					startReached   = false,
					endReached     = false;

				extract = function (root) {
					var clone,
						docFrag = startNode.ownerDocument.createDocumentFragment();

					dom.traverse(root, function (node) {
						// if end has been reached exit loop
						if (endReached || node === endNode) {
							endReached = true;

							return false;
						}

						if (node === startNode) {
							startReached = true;
						}

						// if the start has been reached and this elm contains
						// the end node then clone it
						// if this node contains the start node then add it
						if ($.contains(node, startNode) ||
							(startReached && $.contains(node, endNode))) {
							clone = node.cloneNode(false);

							clone.appendChild(extract(node));
							docFrag.appendChild(clone);

						// otherwise move it if its parent isn't already part of it
						} else if (startReached && !$.contains(docFrag, node)) {
							docFrag.appendChild(node);
						}
					}, false);

					return docFrag;
				};

				return extract(commonAncestor);
			},

			/**
			 * Gets the offset position of an element
			 *
			 * @param  {HTMLElement} obj
			 * @return {Object} An object with left and top properties
			 */
			getOffset: function (obj) {
				var	pLeft = 0,
					pTop = 0;

				while (obj) {
					pLeft += obj.offsetLeft;
					pTop  += obj.offsetTop;
					obj   = obj.offsetParent;
				}

				return {
					left: pLeft,
					top: pTop
				};
			},

			/**
			 * Gets the value of a CSS property from the elements style attribute
			 *
			 * @param  {HTMLElement} elm
			 * @param  {String} property
			 * @return {String}
			 */
			getStyle: function (elm, property) {
				var	$elm, direction, styleValue,
					elmStyle = elm.style;

				if (!elmStyle) {
					return '';
				}

				if (!_propertyNameCache[property]) {
					_propertyNameCache[property] = $.camelCase(property);
				}

				property   = _propertyNameCache[property];
				styleValue = elmStyle[property];

				// Add an exception for text-align
				if ('textAlign' === property) {
					$elm       = $(elm);
					direction  = elmStyle.direction;
					styleValue = styleValue || $elm.css(property);

					if ($elm.parent().css(property) === styleValue ||
						$elm.css('display') !== 'block' ||
						$elm.is('hr') || $elm.is('th')) {
						return '';
					}

					// IE changes text-align to the same as the current direction
					// so skip unless its not the same
					if (direction && styleValue &&
						((/right/i.test(styleValue) && direction === 'rtl') ||
						(/left/i.test(styleValue) && direction === 'ltr'))) {
						return '';
					}
				}

				return styleValue;
			},

			/**
			 * Tests if an element has a style.
			 *
			 * If values are specified it will check that the styles value
			 * matches one of the values
			 *
			 * @param  {HTMLElement} elm
			 * @param  {String} property
			 * @param  {String|Array} values
			 * @return {Boolean}
			 */
			hasStyle: function (elm, property, values) {
				var styleValue = dom.getStyle(elm, property);

				if (!styleValue) {
					return false;
				}

				return !values || styleValue === values ||
					($.isArray(values) && $.inArray(styleValue, values) > -1);
			}
		};

		return dom;
	}.call(exports, __webpack_require__, exports, module), __WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ },
/* 10 */
/***/ function(module, exports, __webpack_require__) {

	var __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_RESULT__ = function () {
		'use strict';

		/**
		 * HTML templates used by the editor and default commands
		 * @type {Object}
		 * @private
		 */
		var _templates = {
			html:
				'<!DOCTYPE html>' +
				'<html{attrs}>' +
					'<head>' +
	// TODO: move these styles into the CSS file
						'<style>.ie * {min-height: auto !important} ' +
							'.ie table td {height:15px}</style>' +
						'<meta http-equiv="Content-Type" ' +
							'content="text/html;charset={charset}" />' +
						'<link rel="stylesheet" type="text/css" href="{style}" />' +
					'</head>' +
					'<body contenteditable="true" {spellcheck}><p></p></body>' +
				'</html>',

			toolbarButton: '<a class="sceditor-button sceditor-button-{name}" ' +
				'data-sceditor-command="{name}" unselectable="on">' +
				'<div unselectable="on">{dispName}</div></a>',

			emoticon: '<img src="{url}" data-sceditor-emoticon="{key}" ' +
				'alt="{key}" title="{tooltip}" />',

			fontOpt: '<a class="sceditor-font-option" href="#" ' +
				'data-font="{font}"><font face="{font}">{font}</font></a>',

			sizeOpt: '<a class="sceditor-fontsize-option" data-size="{size}" ' +
				'href="#"><font size="{size}">{size}</font></a>',

			pastetext:
				'<div><label for="txt">{label}</label> ' +
					'<textarea cols="20" rows="7" id="txt"></textarea></div>' +
					'<div><input type="button" class="button" value="{insert}" />' +
				'</div>',

			table:
				'<div><label for="rows">{rows}</label><input type="text" ' +
					'id="rows" value="2" /></div>' +
				'<div><label for="cols">{cols}</label><input type="text" ' +
					'id="cols" value="2" /></div>' +
				'<div><input type="button" class="button" value="{insert}"' +
					' /></div>',

			image:
				'<div><label for="link">{url}</label> ' +
					'<input type="text" id="image" placeholder="http://" /></div>' +
				'<div><label for="width">{width}</label> ' +
					'<input type="text" id="width" size="2" /></div>' +
				'<div><label for="height">{height}</label> ' +
					'<input type="text" id="height" size="2" /></div>' +
				'<div><input type="button" class="button" value="{insert}" />' +
					'</div>',

			email:
				'<div><label for="email">{label}</label> ' +
					'<input type="text" id="email" /></div>' +
				'<div><label for="des">{desc}</label> ' +
					'<input type="text" id="des" /></div>' +
				'<div><input type="button" class="button" value="{insert}" />' +
					'</div>',

			link:
				'<div><label for="link">{url}</label> ' +
					'<input type="text" id="link" placeholder="http://" /></div>' +
				'<div><label for="des">{desc}</label> ' +
					'<input type="text" id="des" /></div>' +
				'<div><input type="button" class="button" value="{ins}" /></div>',

			youtubeMenu:
				'<div><label for="link">{label}</label> ' +
					'<input type="text" id="link" placeholder="http://" /></div>' +
				'<div><input type="button" class="button" value="{insert}" />' +
					'</div>',

			youtube:
				'<iframe width="560" height="315" ' +
				'src="http://www.youtube.com/embed/{id}?wmode=opaque" ' +
				'data-youtube-id="{id}" frameborder="0" allowfullscreen></iframe>'
		};

		/**
		 * <p>Replaces any params in a template with the passed params.</p>
		 *
		 * <p>If createHtml is passed it will use jQuery to create the HTML. The
		 * same as doing: $(editor.tmpl("html", {params...}));</p>
		 *
		 * @param {string} name
		 * @param {Object} params
		 * @param {Boolean} createHtml
		 * @private
		 */
		return function (name, params, createHtml) {
			var template = _templates[name];

			$.each(params, function (name, val) {
				template = template.replace(
					new RegExp('\\{' + name + '\\}', 'g'), val
				);
			});

			if (createHtml) {
				template = $(template);
			}

			return template;
		};
	}.call(exports, __webpack_require__, exports, module), __WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }
/******/ ]);/**
 * SCEditor BBCode Plugin
 * http://www.sceditor.com/
 *
 * Copyright (C) 2011-2014, Sam Clarke (samclarke.com)
 *
 * SCEditor is licensed under the MIT license:
 *	http://www.opensource.org/licenses/mit-license.php
 *
 * @fileoverview SCEditor BBCode Plugin
 * @author Sam Clarke
 * @requires jQuery
 */
/*global prompt: true*/
/*jshint maxdepth: false*/
// TODO: Tidy this code up and consider seperating the BBCode parser into a
// standalone module that can be used with other JS/NodeJS
(function ($, window, document) {
	'use strict';

	var SCEditor        = $.sceditor;
	var sceditorPlugins = SCEditor.plugins;
	var escapeEntities  = SCEditor.escapeEntities;
	var escapeUriScheme = SCEditor.escapeUriScheme;

	var IE_VER = SCEditor.ie;

	// In IE < 11 a BR at the end of a block level element
	// causes a double line break.
	var IE_BR_FIX = IE_VER && IE_VER < 11;



	var getEditorCommand = SCEditor.command.get;

	var defaultCommandsOverrides = {
		bold: {
			txtExec: ['[b]', '[/b]']
		},
		italic: {
			txtExec: ['[i]', '[/i]']
		},
		underline: {
			txtExec: ['[u]', '[/u]']
		},
		strike: {
			txtExec: ['[s]', '[/s]']
		},
		subscript: {
			txtExec: ['[sub]', '[/sub]']
		},
		superscript: {
			txtExec: ['[sup]', '[/sup]']
		},
		left: {
			txtExec: ['[left]', '[/left]']
		},
		center: {
			txtExec: ['[center]', '[/center]']
		},
		right: {
			txtExec: ['[right]', '[/right]']
		},
		justify: {
			txtExec: ['[justify]', '[/justify]']
		},
		font: {
			txtExec: function (caller) {
				var editor = this;

				getEditorCommand('font')._dropDown(
					editor,
					caller,
					function (fontName) {
						editor.insertText(
							'[font=' + fontName + ']',
							'[/font]'
						);
					}
				);
			}
		},
		size: {
			txtExec: function (caller) {
				var editor = this;

				getEditorCommand('size')._dropDown(
					editor,
					caller,
					function (fontSize) {
						editor.insertText(
							'[size=' + fontSize + ']',
							'[/size]'
						);
					}
				);
			}
		},
		color: {
			txtExec: function (caller) {
				var editor = this;

				getEditorCommand('color')._dropDown(
					editor,
					caller,
					function (color) {
						editor.insertText(
							'[color=' + color + ']',
							'[/color]'
						);
					}
				);
			}
		},
		bulletlist: {
			txtExec: function (caller, selected) {
				var content = '';

				$.each(selected.split(/\r?\n/), function () {
					content += (content ? '\n' : '') +
						'[li]' + this + '[/li]';
				});

				this.insertText('[ul]\n' + content + '\n[/ul]');
			}
		},
		orderedlist: {
			txtExec: function (caller, selected) {
				var content = '';

				$.each(selected.split(/\r?\n/), function () {
					content += (content ? '\n' : '') +
						'[li]' + this + '[/li]';
				});

				sceditorPlugins.bbcode.bbcode.get('');

				this.insertText('[ol]\n' + content + '\n[/ol]');
			}
		},
		table: {
			txtExec: ['[table][tr][td]', '[/td][/tr][/table]']
		},
		horizontalrule: {
			txtExec: ['[hr]']
		},
		code: {
			txtExec: ['[code]', '[/code]'],
			txtCodeInputModeInside: ['[code', '[/code]']
		},
		image: {
			txtExec: function (caller, selected) {
				var	editor = this,
					url    = prompt(editor._('Enter the image URL:'), selected);

				if (url) {
					editor.insertText('[img]' + url + '[/img]');
				}
			}
		},
		email: {
			txtExec: function (caller, selected) {
				var	editor  = this,
					display = selected && selected.indexOf('@') > -1 ?
						null : selected,
					email	= prompt(editor._('Enter the e-mail address:'),
						(display ? '' : selected)),
					text	= prompt(editor._('Enter the displayed text:'),
						display || email) || email;

				if (email) {
					editor.insertText('[email=' + email + ']' +
						text + '[/email]');
				}
			}
		},
		link: {
			txtExec: function (caller, selected) {
				var	editor  = this,
					display = /^[a-z]+:\/\//i.test($.trim(selected)) ?
						null : selected,
					url     = prompt(editor._('Enter URL:'),
						(display ? 'http://' : $.trim(selected))),
					text    = prompt(editor._('Enter the displayed text:'),
						display || url) || url;

				if (url) {
					editor.insertText('[url=' + url + ']' + text + '[/url]');
				}
			}
		},
		quote: {
			txtExec: ['[quote]', '[/quote]']
		},
		youtube: {
			txtExec: function (caller) {
				var editor = this;

				getEditorCommand('youtube')._dropDown(
					editor,
					caller,
					function (id) {
						editor.insertText('[youtube]' + id + '[/youtube]');
					}
				);
			}
		},
		rtl: {
			txtExec: ['[rtl]', '[/rtl]']
		},
		ltr: {
			txtExec: ['[ltr]', '[/ltr]']
		}
	};

	/**
	 * Removes any leading or trailing quotes ('")
	 *
	 * @return string
	 * @since v1.4.0
	 */
	var _stripQuotes = function (str) {
		return str ?
			str.replace(/\\(.)/g, '$1').replace(/^(["'])(.*?)\1$/, '$2') : str;
	};

	/**
	 * Formats a string replacing {0}, {1}, {2}, ect. with
	 * the params provided
	 *
	 * @param {String} str The string to format
	 * @param {string} args... The strings to replace
	 * @return {String}
	 * @since v1.4.0
	 */
	var _formatString = function () {
		var	undef;
		var args = arguments;

		return args[0].replace(/\{(\d+)\}/g, function (str, p1) {
			return args[p1 - 0 + 1] !== undef ?
				args[p1 - 0 + 1] :
				'{' + p1 + '}';
		});
	};

	/**
	 * Enum of valid token types
	 * @type {Object}
	 * @private
	 */
	var TokenType = {
		OPEN:    'open',
		CONTENT: 'content',
		NEWLINE: 'newline',
		CLOSE:   'close'
	};


	/**
	 * Tokenize token object
	 *
	 * @param  {String} type The type of token this is,
	 *                       should be one of tokenType
	 * @param  {String} name The name of this token
	 * @param  {String} val The originally matched string
	 * @param  {Array} attrs Any attributes. Only set on
	 *                       TokenType.OPEN tokens
	 * @param  {Array} children Any children of this token
	 * @param  {TokenizeToken} closing This tokens closing tag.
	 *                                 Only set on TokenType.OPEN tokens
	 * @class TokenizeToken
	 * @name TokenizeToken
	 * @memberOf jQuery.sceditor.BBCodeParser.prototype
	 */
	var TokenizeToken = function (
		/*jshint maxparams: false*/
		type, name, val, attrs, children, closing
	) {
		var base      = this;

		base.type     = type;
		base.name     = name;
		base.val      = val;
		base.attrs    = attrs || {};
		base.children = children || [];
		base.closing  = closing || null;
	};

	TokenizeToken.prototype = {
		/** @lends jQuery.sceditor.BBCodeParser.prototype.TokenizeToken */
		/**
		 * Clones this token
		 *
		 * @param  {Bool} includeChildren If to include the children in
		 *                                the clone. Defaults to false.
		 * @return {TokenizeToken}
		 */
		clone: function (includeChildren) {
			var base = this;

			return new TokenizeToken(
				base.type,
				base.name,
				base.val,
				base.attrs,
				includeChildren ? base.children : [],
				base.closing ? base.closing.clone() : null
			);
		},
		/**
		 * Splits this token at the specified child
		 *
		 * @param  {TokenizeToken|Int} splitAt The child to split at or the
		 *                                     index of the child
		 * @return {TokenizeToken} The right half of the split token or
		 *                         null if failed
		 */
		splitAt: function (splitAt) {
			var	clone;
			var base          = this;
			var splitAtLength = 0;
			var childrenLen   = base.children.length;

			if (typeof splitAt !== 'number') {
				splitAt = $.inArray(splitAt, base.children);
			}

			if (splitAt < 0 || splitAt > childrenLen) {
				return null;
			}

			// Work out how many items are on the right side of the split
			// to pass to splice()
			while (childrenLen--) {
				if (childrenLen >= splitAt) {
					splitAtLength++;
				} else {
					childrenLen = 0;
				}
			}

			clone          = base.clone();
			clone.children = base.children.splice(splitAt, splitAtLength);
			return clone;
		}
	};


	/**
	 * SCEditor BBCode parser class
	 *
	 * @param {Object} options
	 * @class BBCodeParser
	 * @name jQuery.sceditor.BBCodeParser
	 * @since v1.4.0
	 */
	var BBCodeParser = function (options) {
		// make sure this is not being called as a function
		if (!(this instanceof BBCodeParser)) {
			return new BBCodeParser(options);
		}

		var base = this;

		// Private methods
		var	init,
			tokenizeTag,
			tokenizeAttrs,
			parseTokens,
			normaliseNewLines,
			fixNesting,
			isChildAllowed,
			removeEmpty,
			fixChildren,
			convertToHTML,
			convertToBBCode,
			hasTag,
			quote,
			lower,
			last;


		init = function () {
			base.bbcodes = sceditorPlugins.bbcode.bbcodes;
			base.opts    = $.extend(
				{},
				BBCodeParser.defaults,
				options
			);
		};

		/**
		 * Takes a BBCode string and splits it into open,
		 * content and close tags.
		 *
		 * It does no checking to verify a tag has a matching open
		 * or closing tag or if the tag is valid child of any tag
		 * before it. For that the tokens should be passed to the
		 * parse function.
		 *
		 * @param {String} str
		 * @return {Array}
		 * @memberOf jQuery.sceditor.BBCodeParser.prototype
		 */
		base.tokenize = function (str) {
			var	matches, type, i;
			var toks   = [];
			var tokens = [
				// Close must come before open as they are
				// the same except close has a / at the start.
				{
					type: TokenType.CLOSE,
					regex: /^\[\/[^\[\]]+\]/
				},
				{
					type: TokenType.OPEN,
					regex: /^\[[^\[\]]+\]/
				},
				{
					type: TokenType.NEWLINE,
					regex: /^(\r\n|\r|\n)/
				},
				{
					type: TokenType.CONTENT,
					regex: /^([^\[\r\n]+|\[)/
				}
			];

			tokens.reverse();

			strloop:
			while (str.length) {
				i = tokens.length;
				while (i--) {
					type = tokens[i].type;

					// Check if the string matches any of the tokens
					if (!(matches = str.match(tokens[i].regex)) ||
						!matches[0]) {
						continue;
					}

					// Add the match to the tokens list
					toks.push(tokenizeTag(type, matches[0]));

					// Remove the match from the string
					str = str.substr(matches[0].length);

					// The token has been added so start again
					continue strloop;
				}

				// If there is anything left in the string which doesn't match
				// any of the tokens then just assume it's content and add it.
				if (str.length) {
					toks.push(tokenizeTag(TokenType.CONTENT, str));
				}

				str = '';
			}

			return toks;
		};

		/**
		 * Extracts the name an params from a tag
		 *
		 * @param {tokenType} type
		 * @param {string} val
		 * @return {Object}
		 * @private
		 */
		tokenizeTag = function (type, val) {
			var matches, attrs, name,
				openRegex  = /\[([^\]\s=]+)(?:([^\]]+))?\]/,
				closeRegex = /\[\/([^\[\]]+)\]/;

			// Extract the name and attributes from opening tags and
			// just the name from closing tags.
			if (type === TokenType.OPEN && (matches = val.match(openRegex))) {
				name = lower(matches[1]);

				if (matches[2] && (matches[2] = $.trim(matches[2]))) {
					attrs = tokenizeAttrs(matches[2]);
				}
			}

			if (type === TokenType.CLOSE && (matches = val.match(closeRegex))) {
				name = lower(matches[1]);
			}

			if (type === TokenType.NEWLINE) {
				name = '#newline';
			}

			// Treat all tokens without a name and
			// all unknown BBCodes as content
			if (!name ||
				((type === TokenType.OPEN || type === TokenType.CLOSE) &&
					!sceditorPlugins.bbcode.bbcodes[name])) {
				type = TokenType.CONTENT;
				name = '#';
			}

			return new TokenizeToken(type, name, val, attrs);
		};

		/**
		 * Extracts the individual attributes from a string containing
		 * all the attributes.
		 *
		 * @param {String} attrs
		 * @return {Array} Assoc array of attributes
		 * @private
		 */
		tokenizeAttrs = function (attrs) {
			var	matches,
				/*
				([^\s=]+)				Anything that's not a space or equals
				=						Equals sign =
				(?:
					(?:
						(["'])					The opening quote
						(
							(?:\\\2|[^\2])*?	Anything that isn't the
												unescaped opening quote
						)
						\2						The opening quote again which
												will close the string
					)
						|				If not a quoted string then match
					(
						(?:.(?!\s\S+=))*.?		Anything that isn't part of
												[space][non-space][=] which
												would be a new attribute
					)
				)
				*/
				attrRegex =
			/([^\s=]+)=(?:(?:(["'])((?:\\\2|[^\2])*?)\2)|((?:.(?!\s\S+=))*.))/g,
				ret       = {};

			// if only one attribute then remove the = from the start and
			// strip any quotes
			if (attrs.charAt(0) === '=' && attrs.indexOf('=', 1) < 0) {
				ret.defaultattr = _stripQuotes(attrs.substr(1));
			} else {
				if (attrs.charAt(0) === '=') {
					attrs = 'defaultattr' + attrs;
				}

				// No need to strip quotes here, the regex will do that.
				while ((matches = attrRegex.exec(attrs))) {
					ret[lower(matches[1])] =
						_stripQuotes(matches[3]) || matches[4];
				}
			}

			return ret;
		};

		/**
		 * Parses a string into an array of BBCodes
		 *
		 * @param  {string}  str
		 * @param  {boolean} preserveNewLines If to preserve all new lines, not
		 *                                    strip any based on the passed
		 *                                    formatting options
		 * @return {Array}                    Array of BBCode objects
		 * @memberOf jQuery.sceditor.BBCodeParser.prototype
		 */
		base.parse = function (str, preserveNewLines) {
			var ret  = parseTokens(base.tokenize(str));
			var opts = base.opts;

			if (opts.fixInvalidChildren) {
				fixChildren(ret);
			}

			if (opts.removeEmptyTags) {
				removeEmpty(ret);
			}

			if (opts.fixInvalidNesting) {
				fixNesting(ret);
			}

			normaliseNewLines(ret, null, preserveNewLines);

			if (opts.removeEmptyTags) {
				removeEmpty(ret);
			}

			return ret;
		};

		/**
		 * Checks if an array of TokenizeToken's contains the
		 * specified token.
		 *
		 * Checks the tokens name and type match another tokens
		 * name and type in the array.
		 *
		 * @param  {string}    name
		 * @param  {tokenType} type
		 * @param  {Array}     arr
		 * @return {Boolean}
		 * @private
		 */
		hasTag = function (name, type, arr) {
			var i = arr.length;

			while (i--) {
				if (arr[i].type === type && arr[i].name === name) {
					return true;
				}
			}

			return false;
		};

		/**
		 * Checks if the child tag is allowed as one
		 * of the parent tags children.
		 *
		 * @param  {TokenizeToken}  parent
		 * @param  {TokenizeToken}  child
		 * @return {Boolean}
		 * @private
		 */
		isChildAllowed = function (parent, child) {
			var	parentBBCode    = parent ? base.bbcodes[parent.name] : {},
				allowedChildren = parentBBCode.allowedChildren;

			if (base.opts.fixInvalidChildren && allowedChildren) {
				return $.inArray(child.name || '#', allowedChildren) > -1;
			}

			return true;
		};

// TODO: Tidy this parseTokens() function up a bit.
		/**
		 * Parses an array of tokens created by tokenize()
		 *
		 * @param  {Array} toks
		 * @return {Array} Parsed tokens
		 * @see tokenize()
		 * @private
		 */
		parseTokens = function (toks) {
			var	token, bbcode, curTok, clone, i, previous, next,
				cloned     = [],
				output     = [],
				openTags   = [],
				/**
				 * Returns the currently open tag or undefined
				 * @return {TokenizeToken}
				 */
				currentOpenTag = function () {
					return last(openTags);
				},
				/**
				 * Adds a tag to either the current tags children
				 * or to the output array.
				 * @param {TokenizeToken} token
				 * @private
				 */
				addTag = function (token) {
					if (currentOpenTag()) {
						currentOpenTag().children.push(token);
					} else {
						output.push(token);
					}
				},
				/**
				 * Checks if this tag closes the current tag
				 * @param  {String} name
				 * @return {Void}
				 */
				closesCurrentTag = function (name) {
					return currentOpenTag() &&
						(bbcode = base.bbcodes[currentOpenTag().name]) &&
						bbcode.closedBy &&
						$.inArray(name, bbcode.closedBy) > -1;
				};

			while ((token = toks.shift())) {
				next = toks[0];

				/* jshint indent:false */
				switch (token.type) {
					case TokenType.OPEN:
						// Check it this closes a parent,
						// e.g. for lists [*]one [*]two
						if (closesCurrentTag(token.name)) {
							openTags.pop();
						}

						addTag(token);
						bbcode = base.bbcodes[token.name];

						// If this tag is not self closing and it has a closing
						// tag then it is open and has children so add it to the
						// list of open tags. If has the closedBy property then
						// it is closed by other tags so include everything as
						// it's children until one of those tags is reached.
						if ((!bbcode || !bbcode.isSelfClosing) &&
							(bbcode.closedBy ||
								hasTag(token.name, TokenType.CLOSE, toks))) {
							openTags.push(token);
						} else if (!bbcode || !bbcode.isSelfClosing) {
							token.type = TokenType.CONTENT;
						}
						break;

					case TokenType.CLOSE:
						// check if this closes the current tag,
						// e.g. [/list] would close an open [*]
						if (currentOpenTag() &&
							token.name !== currentOpenTag().name &&
							closesCurrentTag('/' + token.name)) {
							openTags.pop();
						}

						// If this is closing the currently open tag just pop
						// the close tag off the open tags array
						if (currentOpenTag() &&
							token.name === currentOpenTag().name) {
							currentOpenTag().closing = token;
							openTags.pop();

						// If this is closing an open tag that is the parent of
						// the current tag then clone all the tags including the
						// current one until reaching the parent that is being
						// closed. Close the parent and then add the clones back
						// in.
						} else if (hasTag(token.name, TokenType.OPEN,
							openTags)) {

							// Remove the tag from the open tags
							while ((curTok = openTags.pop())) {

								// If it's the tag that is being closed then
								// discard it and break the loop.
								if (curTok.name === token.name) {
									curTok.closing = token;
									break;
								}

								// Otherwise clone this tag and then add any
								// previously cloned tags as it's children
								clone = curTok.clone();

								if (cloned.length > 1) {
									clone.children.push(last(cloned));
								}

								cloned.push(clone);
							}

							// Add the last cloned child to the now current tag
							// (the parent of the tag which was being closed)
							addTag(last(cloned));

							// Add all the cloned tags to the open tags list
							i = cloned.length;
							while (i--) {
								openTags.push(cloned[i]);
							}

							cloned.length = 0;

						// This tag is closing nothing so treat it as content
						} else {
							token.type = TokenType.CONTENT;
							addTag(token);
						}
						break;

					case TokenType.NEWLINE:
						// handle things like
						//     [*]list\nitem\n[*]list1
						// where it should come out as
						//     [*]list\nitem[/*]\n[*]list1[/*]
						// instead of
						//     [*]list\nitem\n[/*][*]list1[/*]
						if (currentOpenTag() && next &&
							closesCurrentTag(
								(next.type === TokenType.CLOSE ? '/' : '') +
								next.name
							)) {
							// skip if the next tag is the closing tag for
							// the option tag, i.e. [/*]
							if (!(next.type === TokenType.CLOSE &&
								next.name === currentOpenTag().name)) {
								bbcode = base.bbcodes[currentOpenTag().name];

								if (bbcode && bbcode.breakAfter) {
									openTags.pop();
								} else if (bbcode &&
									bbcode.isInline === false &&
									base.opts.breakAfterBlock &&
									bbcode.breakAfter !== false) {
									openTags.pop();
								}
							}
						}

						addTag(token);
						break;

					default: // content
						addTag(token);
						break;
				}

				previous = token;
			}

			return output;
		};

		/**
		 * Normalise all new lines
		 *
		 * Removes any formatting new lines from the BBCode
		 * leaving only content ones. I.e. for a list:
		 *
		 * [list]
		 * [*] list item one
		 * with a line break
		 * [*] list item two
		 * [/list]
		 *
		 * would become
		 *
		 * [list] [*] list item one
		 * with a line break [*] list item two [/list]
		 *
		 * Which makes it easier to convert to HTML or add
		 * the formatting new lines back in when converting
		 * back to BBCode
		 *
		 * @param  {Array} children
		 * @param  {TokenizeToken} parent
		 * @param  {Bool} onlyRemoveBreakAfter
		 * @return {void}
		 */
		normaliseNewLines = function (children, parent, onlyRemoveBreakAfter) {
			var	token, left, right, parentBBCode, bbcode,
				removedBreakEnd, removedBreakBefore, remove;
			var childrenLength = children.length;
// TODO: this function really needs tidying up
			if (parent) {
				parentBBCode = base.bbcodes[parent.name];
			}

			var i = childrenLength;
			while (i--) {
				if (!(token = children[i])) {
					continue;
				}

				if (token.type === TokenType.NEWLINE) {
					left   = i > 0 ? children[i - 1] : null;
					right  = i < childrenLength - 1 ? children[i + 1] : null;
					remove = false;

					// Handle the start and end new lines
					// e.g. [tag]\n and \n[/tag]
					if (!onlyRemoveBreakAfter && parentBBCode &&
						parentBBCode.isSelfClosing !== true) {
						// First child of parent so must be opening line break
						// (breakStartBlock, breakStart) e.g. [tag]\n
						if (!left) {
							if (parentBBCode.isInline === false &&
								base.opts.breakStartBlock &&
								parentBBCode.breakStart !== false) {
								remove = true;
							}

							if (parentBBCode.breakStart) {
								remove = true;
							}
						// Last child of parent so must be end line break
						// (breakEndBlock, breakEnd)
						// e.g. \n[/tag]
						// remove last line break (breakEndBlock, breakEnd)
						} else if (!removedBreakEnd && !right) {
							if (parentBBCode.isInline === false &&
								base.opts.breakEndBlock &&
								parentBBCode.breakEnd !== false) {
								remove = true;
							}

							if (parentBBCode.breakEnd) {
								remove = true;
							}

							removedBreakEnd = remove;
						}
					}

					if (left && left.type === TokenType.OPEN) {
						if ((bbcode = base.bbcodes[left.name])) {
							if (!onlyRemoveBreakAfter) {
								if (bbcode.isInline === false &&
									base.opts.breakAfterBlock &&
									bbcode.breakAfter !== false) {
									remove = true;
								}

								if (bbcode.breakAfter) {
									remove = true;
								}
							} else if (bbcode.isInline === false) {
								remove = true;
							}
						}
					}

					if (!onlyRemoveBreakAfter && !removedBreakBefore &&
						right && right.type === TokenType.OPEN) {

						if ((bbcode = base.bbcodes[right.name])) {
							if (bbcode.isInline === false &&
								base.opts.breakBeforeBlock &&
								bbcode.breakBefore !== false) {
								remove = true;
							}

							if (bbcode.breakBefore) {
								remove = true;
							}

							removedBreakBefore = remove;

							if (remove) {
								children.splice(i, 1);
								continue;
							}
						}
					}

					if (remove) {
						children.splice(i, 1);
					}

					// reset double removedBreakBefore removal protection.
					// This is needed for cases like \n\n[\tag] where
					// only 1 \n should be removed but without this they both
					// would be.
					removedBreakBefore = false;
				} else if (token.type === TokenType.OPEN) {
					normaliseNewLines(token.children, token,
						onlyRemoveBreakAfter);
				}
			}
		};

		/**
		 * Fixes any invalid nesting.
		 *
		 * If it is a block level element inside 1 or more inline elements
		 * then those inline elements will be split at the point where the
		 * block level is and the block level element placed between the split
		 * parts. i.e.
		 *     [inline]A[blocklevel]B[/blocklevel]C[/inline]
		 * Will become:
		 *     [inline]A[/inline][blocklevel]B[/blocklevel][inline]C[/inline]
		 *
		 * @param {Array} children
		 * @param {Array} [parents] Null if there is no parents
		 * @param {Array} [insideInline] Boolean, if inside an inline element
		 * @param {Array} [rootArr] Root array if there is one
		 * @return {Array}
		 * @private
		 */
		fixNesting = function (children, parents, insideInline, rootArr) {
			var	token, i, parent, parentIndex, parentParentChildren, right;

			var isInline = function (token) {
				var bbcode = base.bbcodes[token.name];

				return !bbcode || bbcode.isInline !== false;
			};

			parents = parents || [];
			rootArr = rootArr || children;

			// This must check the length each time as it can change when
			// tokens are moved to fix the nesting.
			for (i = 0; i < children.length; i++) {
				if (!(token = children[i]) || token.type !== TokenType.OPEN) {
					continue;
				}

				if (!isInline(token) && insideInline) {
					// if this is a blocklevel element inside an inline one then
					// split the parent at the block level element
					parent = last(parents);
					right  = parent.splitAt(token);

					parentParentChildren = parents.length > 1 ?
						parents[parents.length - 2].children : rootArr;

					parentIndex = $.inArray(parent, parentParentChildren);
					if (parentIndex > -1) {
						// remove the block level token from the right side of
						// the split inline element
						right.children.splice(
							$.inArray(token, right.children), 1);

						// insert the block level token and the right side after
						// the left side of the inline token
						parentParentChildren.splice(
							parentIndex + 1, 0, token, right
						);

						// return to parents loop as the
						// children have now increased
						return;
					}

				}

				parents.push(token);

				fixNesting(
					token.children,
					parents,
					insideInline || isInline(token),
					rootArr
				);

				parents.pop(token);
			}
		};

		/**
		 * Fixes any invalid children.
		 *
		 * If it is an element which isn't allowed as a child of it's parent
		 * then it will be converted to content of the parent element. i.e.
		 *     [code]Code [b]only[/b] allows text.[/code]
		 * Will become:
		 *     <code>Code [b]only[/b] allows text.</code>
		 * Instead of:
		 *     <code>Code <b>only</b> allows text.</code>
		 *
		 * @param {Array} children
		 * @param {Array} [parent] Null if there is no parents
		 * @private
		 */
		fixChildren = function (children, parent) {
			var	token, args;

			var i = children.length;
			while (i--) {
				if (!(token = children[i])) {
					continue;
				}

				if (!isChildAllowed(parent, token)) {
					// if it is not then convert it to text and see if it
					// is allowed
					token.name = null;
					token.type = TokenType.CONTENT;

					if (isChildAllowed(parent, token)) {
						args = [i + 1, 0].concat(token.children);

						if (token.closing) {
							token.closing.name = null;
							token.closing.type = TokenType.CONTENT;
							args.push(token.closing);
						}

						i += args.length - 1;
						Array.prototype.splice.apply(children, args);
					} else {
						parent.children.splice(i, 1);
					}
				}

				if (token.type === TokenType.OPEN) {
					fixChildren(token.children, token);
				}
			}
		};

		/**
		 * Removes any empty BBCodes which are not allowed to be empty.
		 *
		 * @param {Array} tokens
		 * @private
		 */
		removeEmpty = function (tokens) {
			var	token, bbcode;

			/**
			 * Checks if all children are whitespace or not
			 * @private
			 */
			var isTokenWhiteSpace = function (children) {
				var j = children.length;

				while (j--) {
					var type = children[j].type;

					if (type === TokenType.OPEN || type === TokenType.CLOSE) {
						return false;
					}

					if (type === TokenType.CONTENT &&
						/\S|\u00A0/.test(children[j].val)) {
						return false;
					}
				}

				return true;
			};

			var i = tokens.length;
			while (i--) {
				// So skip anything that isn't a tag since only tags can be
				// empty, content can't
				if (!(token = tokens[i]) || token.type !== TokenType.OPEN) {
					continue;
				}

				bbcode = base.bbcodes[token.name];

				// Remove any empty children of this tag first so that if they
				// are all removed this one doesn't think it's not empty.
				removeEmpty(token.children);

				if (isTokenWhiteSpace(token.children) && bbcode &&
					!bbcode.isSelfClosing && !bbcode.allowsEmpty) {
					tokens.splice.apply(
						tokens,
						$.merge([i, 1], token.children)
					);
				}
			}
		};

		/**
		 * Converts a BBCode string to HTML
		 *
		 * @param {String} str
		 * @param {Bool}   preserveNewLines If to preserve all new lines, not
		 *                                  strip any based on the passed
		 *                                  formatting options
		 * @return {String}
		 * @memberOf jQuery.sceditor.BBCodeParser.prototype
		 */
		base.toHTML = function (str, preserveNewLines) {
			return convertToHTML(base.parse(str, preserveNewLines), true);
		};

		/**
		 * @private
		 */
		convertToHTML = function (tokens, isRoot, myself) {
			var	undef, token, bbcode, content, html, needsBlockWrap,
				blockWrapOpen, isInline, lastChild,
				ret = [];

			isInline = function (bbcode) {
				return (!bbcode || (bbcode.isHtmlInline !== undef ?
					bbcode.isHtmlInline : bbcode.isInline)) !== false;
			};

			while (tokens.length > 0) {
				if (!(token = tokens.shift())) {
					continue;
				}

				if (token.type === TokenType.OPEN) {
					lastChild      =
						token.children[token.children.length - 1] || {};
					bbcode         = base.bbcodes[token.name];
					needsBlockWrap = isRoot && isInline(bbcode);
					content        =
						convertToHTML(token.children, false, bbcode);

					if (bbcode && bbcode.html) {
						// Only add a line break to the end if this is
						// blocklevel and the last child wasn't block-level
						if (!isInline(bbcode) &&
							isInline(base.bbcodes[lastChild.name]) &&
							!bbcode.isPreFormatted &&
							!bbcode.skipLastLineBreak) {
							// Add placeholder br to end of block level elements
							// in all browsers apart from IE < 9 which handle
							// new lines differently and doesn't need one.
							if (!IE_BR_FIX) {
								content += '<br />';
							}
						}

						if (!$.isFunction(bbcode.html)) {
							token.attrs['0'] = content;
							html = sceditorPlugins.bbcode.formatBBCodeString(
								bbcode.html,
								token.attrs
							);
						} else {
							html = bbcode.html.call(
								base,
								token,
								token.attrs,
								content
							);
						}
					} else {
						html = token.val + content +
							(token.closing ? token.closing.val : '');
					}
				} else if (token.type === TokenType.NEWLINE) {
					if (!isRoot) {
						if (myself && myself.isPreFormatted) {
							ret.push('\n');
						} else {
							ret.push('<br />');
						}
						continue;
					}

					// If not already in a block wrap then start a new block
					if (!blockWrapOpen) {
						ret.push('<div>');

						// If it's an empty DIV and compatibility mode is below
						// IE8 then we must add a non-breaking space to the div
						// otherwise the div will be collapsed. Adding a BR
						// works but when you press enter to make a newline it
						// suddenly goes back to the normal IE div behavior and
						// creates two lines, one for the newline and one for
						// the BR. I'm sure there must be a better fix but I've
						// yet to find one.
						// Cannot do zoom: 1; or set a height on the div to fix
						// it as that causes resize handles to be added to the
						// div when it's clicked on.
						if (IE_VER < 8 || (document.documentMode &&
							document.documentMode < 8)) {
							ret.push('\u00a0');
						}
					}

					// Putting BR in a div in IE causes it
					// to do a double line break.
					if (!IE_BR_FIX) {
						ret.push('<br />');
					}

					// Normally the div acts as a line-break with by moving
					// whatever comes after onto a new line.
					// If this is the last token, add an extra line-break so it
					// shows as there will be nothing after it.
					if (!tokens.length) {
						ret.push('<br />');
					}

					ret.push('</div>\n');
					blockWrapOpen = false;
					continue;
				// content
				} else {
					needsBlockWrap = isRoot;
					html           = escapeEntities(token.val, true);
				}

				if (needsBlockWrap && !blockWrapOpen) {
					ret.push('<div>');
					blockWrapOpen = true;
				} else if (!needsBlockWrap && blockWrapOpen) {
					ret.push('</div>\n');
					blockWrapOpen = false;
				}

				ret.push(html);
			}

			if (blockWrapOpen) {
				ret.push('</div>\n');
			}

			return ret.join('');
		};

		/**
		 * Takes a BBCode string, parses it then converts it back to BBCode.
		 *
		 * This will auto fix the BBCode and format it with the specified
		 * options.
		 *
		 * @param {String} str
		 * @param {Bool} preserveNewLines If to preserve all new lines, not
		 *                                strip any based on the passed
		 *                                formatting options
		 * @return {String}
		 * @memberOf jQuery.sceditor.BBCodeParser.prototype
		 */
		base.toBBCode = function (str, preserveNewLines) {
			return convertToBBCode(base.parse(str, preserveNewLines));
		};

		/**
		 * Converts parsed tokens back into BBCode with the
		 * formatting specified in the options and with any
		 * fixes specified.
		 *
		 * @param  {Array} toks Array of parsed tokens from base.parse()
		 * @return {String}
		 * @private
		 */
		convertToBBCode = function (toks) {
			var	token, attr, bbcode, isBlock, isSelfClosing, quoteType,
				breakBefore, breakStart, breakEnd, breakAfter,
				// Create an array of strings which are joined together
				// before being returned as this is faster in slow browsers.
				// (Old versions of IE).
				ret = [];

			while (toks.length > 0) {
				if (!(token = toks.shift())) {
					continue;
				}
// TODO: tidy this
				bbcode        = base.bbcodes[token.name];
				isBlock       = !(!bbcode || bbcode.isInline !== false);
				isSelfClosing = bbcode && bbcode.isSelfClosing;

				breakBefore = (isBlock && base.opts.breakBeforeBlock &&
						bbcode.breakBefore !== false) ||
					(bbcode && bbcode.breakBefore);

				breakStart = (isBlock && !isSelfClosing &&
						base.opts.breakStartBlock &&
						bbcode.breakStart !== false) ||
					(bbcode && bbcode.breakStart);

				breakEnd = (isBlock && base.opts.breakEndBlock &&
						bbcode.breakEnd !== false) ||
					(bbcode && bbcode.breakEnd);

				breakAfter = (isBlock && base.opts.breakAfterBlock &&
						bbcode.breakAfter !== false) ||
					(bbcode && bbcode.breakAfter);

				quoteType = (bbcode ? bbcode.quoteType : null) ||
					base.opts.quoteType || BBCodeParser.QuoteType.auto;

				if (!bbcode && token.type === TokenType.OPEN) {
					ret.push(token.val);

					if (token.children) {
						ret.push(convertToBBCode(token.children));
					}

					if (token.closing) {
						ret.push(token.closing.val);
					}
				} else if (token.type === TokenType.OPEN) {
					if (breakBefore) {
						ret.push('\n');
					}

					// Convert the tag and it's attributes to BBCode
					ret.push('[' + token.name);
					if (token.attrs) {
						if (token.attrs.defaultattr) {
							ret.push('=', quote(
								token.attrs.defaultattr,
								quoteType,
								'defaultattr'
							));

							delete token.attrs.defaultattr;
						}

						for (attr in token.attrs) {
							if (token.attrs.hasOwnProperty(attr)) {
								ret.push(' ', attr, '=',
									quote(token.attrs[attr], quoteType, attr));
							}
						}
					}
					ret.push(']');

					if (breakStart) {
						ret.push('\n');
					}

					// Convert the tags children to BBCode
					if (token.children) {
						ret.push(convertToBBCode(token.children));
					}

					// add closing tag if not self closing
					if (!isSelfClosing && !bbcode.excludeClosing) {
						if (breakEnd) {
							ret.push('\n');
						}

						ret.push('[/' + token.name + ']');
					}

					if (breakAfter) {
						ret.push('\n');
					}

					// preserve whatever was recognized as the
					// closing tag if it is a self closing tag
					if (token.closing && isSelfClosing) {
						ret.push(token.closing.val);
					}
				} else {
					ret.push(token.val);
				}
			}

			return ret.join('');
		};

		/**
		 * Quotes an attribute
		 *
		 * @param {String} str
		 * @param {BBCodeParser.QuoteType} quoteType
		 * @param {String} name
		 * @return {String}
		 * @private
		 */
		quote = function (str, quoteType, name) {
			var	QuoteTypes  = BBCodeParser.QuoteType,
				needsQuotes = /\s|=/.test(str);

			if ($.isFunction(quoteType)) {
				return quoteType(str, name);
			}

			if (quoteType === QuoteTypes.never ||
				(quoteType === QuoteTypes.auto && !needsQuotes)) {
				return str;
			}

			return '"' + str.replace('\\', '\\\\').replace('"', '\\"') + '"';
		};

		/**
		 * Returns the last element of an array or null
		 *
		 * @param {Array} arr
		 * @return {Object} Last element
		 * @private
		 */
		last = function (arr) {
			if (arr.length) {
				return arr[arr.length - 1];
			}

			return null;
		};

		/**
		 * Converts a string to lowercase.
		 *
		 * @param {String} str
		 * @return {String} Lowercase version of str
		 * @private
		 */
		lower = function (str) {
			return str.toLowerCase();
		};

		init();
	};

	/**
	 * Quote type
	 * @type {Object}
	 * @class QuoteType
	 * @name jQuery.sceditor.BBCodeParser.QuoteType
	 * @since v1.4.0
	 */
	BBCodeParser.QuoteType = {
		/** @lends jQuery.sceditor.BBCodeParser.QuoteType */
		/**
		 * Always quote the attribute value
		 * @type {Number}
		 */
		always: 1,

		/**
		 * Never quote the attributes value
		 * @type {Number}
		 */
		never: 2,

		/**
		 * Only quote the attributes value when it contains spaces to equals
		 * @type {Number}
		 */
		auto: 3
	};

	/**
	 * Default BBCode parser options
	 * @type {Object}
	 */
	BBCodeParser.defaults = {
		/**
		 * If to add a new line before block level elements
		 *
		 * @type {Boolean}
		 */
		breakBeforeBlock: false,

		/**
		 * If to add a new line after the start of block level elements
		 *
		 * @type {Boolean}
		 */
		breakStartBlock: false,

		/**
		 * If to add a new line before the end of block level elements
		 *
		 * @type {Boolean}
		 */
		breakEndBlock: false,

		/**
		 * If to add a new line after block level elements
		 *
		 * @type {Boolean}
		 */
		breakAfterBlock: true,

		/**
		 * If to remove empty tags
		 *
		 * @type {Boolean}
		 */
		removeEmptyTags: true,

		/**
		 * If to fix invalid nesting,
		 * i.e. block level elements inside inline elements.
		 *
		 * @type {Boolean}
		 */
		fixInvalidNesting: true,

		/**
		 * If to fix invalid children.
		 * i.e. A tag which is inside a parent that doesn't
		 * allow that type of tag.
		 *
		 * @type {Boolean}
		 */
		fixInvalidChildren: true,

		/**
		 * Attribute quote type
		 *
		 * @type {BBCodeParser.QuoteType}
		 * @since 1.4.1
		 */
		quoteType: BBCodeParser.QuoteType.auto
	};

	/**
	 * Deprecated, use sceditorPlugins.bbcode
	 *
	 * @class sceditorBBCodePlugin
	 * @name jQuery.sceditor.sceditorBBCodePlugin
	 * @deprecated
	 */
	$.sceditorBBCodePlugin =
	/**
	 * BBCode plugin for SCEditor
	 *
	 * @class bbcode
	 * @name jQuery.sceditor.plugins.bbcode
	 * @since 1.4.1
	 */
	sceditorPlugins.bbcode = function () {
		var base = this;

		/**
		 * Private methods
		 * @private
		 */
		var	buildBbcodeCache,
			handleStyles,
			handleTags,
			removeFirstLastDiv;

		base.bbcodes     = sceditorPlugins.bbcode.bbcodes;
		base.stripQuotes = _stripQuotes;

		/**
		 * cache of all the tags pointing to their bbcodes to enable
		 * faster lookup of which bbcode a tag should have
		 * @private
		 */
		var tagsToBBCodes = {};

		/**
		 * Same as tagsToBBCodes but instead of HTML tags it's styles
		 * @private
		 */
		var stylesToBBCodes = {};

		/**
		 * Allowed children of specific HTML tags. Empty array if no
		 * children other than text nodes are allowed
		 * @private
		 */
		var validChildren = {
			ul: ['li', 'ol', 'ul'],
			ol: ['li', 'ol', 'ul'],
			table: ['tr'],
			tr: ['td', 'th'],
			code: []
		};

		/**
		 * Initializer
		 * @private
		 */
		base.init = function () {
			base.opts = this.opts;

			// build the BBCode cache
			buildBbcodeCache();

			this.commands = $.extend(
				true, {}, defaultCommandsOverrides, this.commands
			);

			// Add BBCode helper methods
			this.toBBCode   = base.signalToSource;
			this.fromBBCode = base.signalToWysiwyg;
		};

		/**
		 * Populates tagsToBBCodes and stylesToBBCodes to enable faster lookups
		 *
		 * @private
		 */
		buildBbcodeCache = function () {
			$.each(base.bbcodes, function (bbcode) {
				var	isBlock,
					tags   = base.bbcodes[bbcode].tags,
					styles = base.bbcodes[bbcode].styles;

				if (tags) {
					$.each(tags, function (tag, values) {
						isBlock = base.bbcodes[bbcode].isInline === false;

						tagsToBBCodes[tag] = tagsToBBCodes[tag] || {};

						tagsToBBCodes[tag][isBlock] =
							tagsToBBCodes[tag][isBlock] || {};

						tagsToBBCodes[tag][isBlock][bbcode] = values;
					});
				}

				if (styles) {
					$.each(styles, function (style, values) {
						isBlock = base.bbcodes[bbcode].isInline === false;

						stylesToBBCodes[isBlock] =
							stylesToBBCodes[isBlock] || {};

						stylesToBBCodes[isBlock][style] =
							stylesToBBCodes[isBlock][style] || {};

						stylesToBBCodes[isBlock][style][bbcode] = values;
					});
				}
			});
		};

		/**
		 * Checks if any bbcode styles match the elements styles
		 *
		 * @return string Content with any matching
		 *                bbcode tags wrapped around it.
		 * @private
		 */
		handleStyles = function ($element, content, blockLevel) {
			var	styleValue, format,
				getStyle = SCEditor.dom.getStyle;

			// convert blockLevel to boolean
			blockLevel = !!blockLevel;

			if (!stylesToBBCodes[blockLevel]) {
				return content;
			}

			$.each(stylesToBBCodes[blockLevel], function (property, bbcodes) {
				styleValue = getStyle($element[0], property);

				// if the parent has the same style use that instead of this one
				// so you don't end up with [i]parent[i]child[/i][/i]
				if (!styleValue ||
					getStyle($element.parent()[0], property) === styleValue) {
					return;
				}

				$.each(bbcodes, function (bbcode, values) {
					if (!values ||
						$.inArray(styleValue.toString(), values) > -1) {
						format = base.bbcodes[bbcode].format;

						if ($.isFunction(format)) {
							content = format.call(base, $element, content);
						} else {
							content = _formatString(format, content);
						}
					}
				});
			});

			return content;
		};

		/**
		 * Handles a HTML tag and finds any matching bbcodes
		 *
		 * @param {jQuery} $element The element to convert
		 * @param {String} content  The Tags text content
		 * @param {Bool} blockLevel If to convert block level tags
		 * @return {String} Content with any matching bbcode tags
		 *                  wrapped around it.
		 * @private
		 */
		handleTags = function ($element, content, blockLevel) {
			var	convertBBCode, format,
				element = $element[0],
				tag     = element.nodeName.toLowerCase();

			// convert blockLevel to boolean
			blockLevel = !!blockLevel;

			if (tagsToBBCodes[tag] && tagsToBBCodes[tag][blockLevel]) {
				// loop all bbcodes for this tag
				$.each(tagsToBBCodes[tag][blockLevel], function (
					bbcode, bbcodeAttribs) {
					// if the bbcode requires any attributes then check this has
					// all needed
					if (bbcodeAttribs) {
						convertBBCode = false;

						// loop all the bbcode attribs
						$.each(bbcodeAttribs, function (attrib, values) {
							// Skip if the element doesn't have the attibue or
							// the attribute doesn't match one of the require
							// values
							if (!$element.attr(attrib) || (values &&
								$.inArray($element.attr(attrib), values) < 0)) {
								return;
							}

							// break this loop as we have matched this bbcode
							convertBBCode = true;
							return false;
						});

						if (!convertBBCode) {
							return;
						}
					}

					format = base.bbcodes[bbcode].format;

					if ($.isFunction(format)) {
						content = format.call(base, $element, content);
					} else {
						content = _formatString(format, content);
					}
				});
			}

			var isInline = SCEditor.dom.isInline;
			if (blockLevel && (!isInline(element, true) || tag === 'br')) {
				var	isLastBlockChild, parent, parentLastChild,
					previousSibling = element.previousSibling;

				// Skips selection makers and ignored elements
				// Skip empty inline elements
				while (previousSibling &&
						previousSibling.nodeType === 1 &&
						!$(previousSibling).is('br') &&
						isInline(previousSibling, true) &&
						!previousSibling.firstChild) {
					previousSibling = previousSibling.previousSibling;
				}

				// If it's the last block of an inline that is the last
				// child of a block then it shouldn't cause a line break
				// except in IE < 11
				// <block><inline><br></inline></block>
				do {
					parent          = element.parentNode;
					parentLastChild = parent.lastChild;

					isLastBlockChild = parentLastChild === element;
					element = parent;
				} while (parent && isLastBlockChild && isInline(parent, true));

				// If this block is:
				//	* Not the last child of a block level element
				//	* Is a <li> tag (lists are blocks)
				//	* Is IE < 11 and the tag is BR. IE < 11 never collapses BR
				//	  tags.
				if (!isLastBlockChild || tag === 'li' ||
					(tag === 'br' && IE_BR_FIX)) {
					content += '\n';
				}

				// Check for:
				// <block>text<block>text</block></block>
				//
				// The second opening <block> opening tag should cause a
				// line break because the previous sibing is inline.
				if (tag !== 'br' && previousSibling &&
					!$(previousSibling).is('br') &&
					isInline(previousSibling, true)) {
					content = '\n' + content;
				}
			}

			return content;
		};

		/**
		 * Converts HTML to BBCode
		 *
		 * @param {String}	html   Html string, this function ignores this,
		 *                         it works off domBody
		 * @param {jQuery}	$body  Editors dom body object to convert
		 * @return {String} BBCode which has been converted from HTML
		 * @memberOf jQuery.plugins.bbcode.prototype
		 */
		base.signalToSource = function (html, $body) {
			var	$tmpContainer, bbcode,
				parser = new BBCodeParser(base.opts.parserOptions);

			if (!$body) {
				if (typeof html === 'string') {
					$tmpContainer = $('<div />')
						.css('visibility', 'hidden')
						.appendTo(document.body)
						.html(html);

					$body = $tmpContainer;
				} else {
					$body = $(html);
				}
			}

			if (!$body || !$body.jquery) {
				return '';
			}

			SCEditor.dom.removeWhiteSpace($body[0]);

			// Remove all the stuff that is meant to be ignored
			$('.sceditor-ignore', $body).remove();

			bbcode = base.elementToBbcode($body);

			if ($tmpContainer) {
				$tmpContainer.remove();
			}

			bbcode = parser.toBBCode(bbcode, true);

			if (base.opts.bbcodeTrim) {
				bbcode = $.trim(bbcode);
			}

			return bbcode;
		};

		/**
		 * Converts a HTML dom element to BBCode starting from
		 * the innermost element and working backwards
		 *
		 * @private
		 * @param {jQuery}	$element The element to convert to BBCode
		 * @return {string} BBCode
		 * @memberOf jQuery.plugins.bbcode.prototype
		 */
		base.elementToBbcode = function ($element) {
			var toBBCode = function (node, vChildren) {
				var ret = '';
// TODO: Move to BBCode class?
				SCEditor.dom.traverse(node, function (node) {
					var	$node        = $(node),
						curTag       = '',
						nodeType     = node.nodeType,
						tag          = node.nodeName.toLowerCase(),
						vChild       = validChildren[tag],
						firstChild   = node.firstChild,
						isValidChild = true;

					if (typeof vChildren === 'object') {
						isValidChild = $.inArray(tag, vChildren) > -1;

						// Emoticons should always be converted
						if ($node.is('img') &&
							$node.data('sceditor-emoticon')) {
							isValidChild = true;
						}

						// if this tag is one of the parents allowed children
						// then set this tags allowed children to whatever it
						// allows, otherwise set to what the parent allows
						if (!isValidChild) {
							vChild = vChildren;
						}
					}

					// 3 = text and 1 = element
					if (nodeType !== 3 && nodeType !== 1) {
						return;
					}

					if (nodeType === 1) {
						// skip empty nlf elements (new lines automatically
						// added after block level elements like quotes)
						if ($node.hasClass('sceditor-nlf')) {
							if (!firstChild || (!IE_BR_FIX &&
								node.childNodes.length === 1 &&
								/br/i.test(firstChild.nodeName))) {
								return;
							}
						}

						// don't loop inside iframes
						if (tag !== 'iframe') {
							curTag = toBBCode(node, vChild);
						}

// TODO: isValidChild is no longer needed. Should use valid children bbcodes
// instead by creating BBCode tokens like the parser.
						if (isValidChild) {
							// code tags should skip most styles
							if (tag !== 'code') {
								// handle inline bbcodes
								curTag = handleStyles($node, curTag);
								curTag = handleTags($node, curTag);

								// handle blocklevel bbcodes
								curTag = handleStyles($node, curTag, true);
							}

							ret += handleTags($node, curTag, true);
						} else {
							ret += curTag;
						}
					} else {
						ret += node.nodeValue;
					}
				}, false, true);

				return ret;
			};

			return toBBCode($element[0]);
		};

		/**
		 * Converts BBCode to HTML
		 *
		 * @param {String} text
		 * @param {Bool} asFragment
		 * @return {String} HTML
		 * @memberOf jQuery.plugins.bbcode.prototype
		 */
		base.signalToWysiwyg = function (text, asFragment) {
			var	parser = new BBCodeParser(base.opts.parserOptions),
				html   = parser.toHTML(base.opts.bbcodeTrim ?
					$.trim(text) : text);

			return asFragment ? removeFirstLastDiv(html) : html;
		};

		/**
		 * Removes the first and last divs from the HTML.
		 *
		 * This is needed for pasting
		 * @param  {String} html
		 * @return {String}
		 * @private
		 */
		removeFirstLastDiv = function (html) {
			var	node, next, removeDiv,
				$output = $('<div />').hide().appendTo(document.body),
				output  = $output[0];

			removeDiv = function (node, isFirst) {
				// Don't remove divs that have styling
				if (SCEditor.dom.hasStyling(node)) {
					return;
				}

				if (IE_BR_FIX || (node.childNodes.length !== 1 ||
					!$(node.firstChild).is('br'))) {
					while ((next = node.firstChild)) {
						output.insertBefore(next, node);
					}
				}

				if (isFirst) {
					var lastChild = output.lastChild;

					if (node !== lastChild && $(lastChild).is('div') &&
						node.nextSibling === lastChild) {
						output.insertBefore(document.createElement('br'), node);
					}
				}

				output.removeChild(node);
			};

			output.innerHTML = html.replace(/<\/div>\n/g, '</div>');

			if ((node = output.firstChild) && $(node).is('div')) {
				removeDiv(node, true);
			}

			if ((node = output.lastChild) && $(node).is('div')) {
				removeDiv(node);
			}

			output = output.innerHTML;
			$output.remove();

			return output;
		};
	};



	/**
	 * Formats a string replacing {name} with the values of
	 * obj.name properties.
	 *
	 * If there is no property for the specified {name} then
	 * it will be left intact.
	 *
	 * @param  {String} str
	 * @param  {Object} obj
	 * @return {String}
	 * @since 1.4.5
	 */
	sceditorPlugins.bbcode.formatBBCodeString = function (str, obj) {
		return str.replace(/\{([^}]+)\}/g, function (match, group) {
			var	undef,
				escape = true;

			if (group.charAt(0) === '!') {
				escape = false;
				group = group.substring(1);
			}

			if (group === '0') {
				escape = false;
			}

			if (obj[group] === undef) {
				return match;
			}

			return escape ?
				escapeEntities(obj[group], true) :
				obj[group];
		});
	};

	/**
	 * Converts a number 0-255 to hex.
	 *
	 * Will return 00 if number is not a valid number.
	 *
	 * @param  {Number} number
	 * @return {String}
	 * @private
	 */
	var toHex = function (number) {
		number = parseInt(number, 10);

		if (isNaN(number)) {
			return '00';
		}

		number = Math.max(0, Math.min(number, 255)).toString(16);

		return number.length < 2 ? '0' + number : number;
	};

	var _normaliseColour = function (colorStr) {
		var match;

		colorStr = colorStr || '#000';

		// rgb(n,n,n);
		if ((match =
			colorStr.match(/rgb\((\d{1,3}),\s*?(\d{1,3}),\s*?(\d{1,3})\)/i))) {
			return '#' +
				toHex(match[1]) +
				toHex(match[2] - 0) +
				toHex(match[3] - 0);
		}

		// expand shorthand
		if ((match = colorStr.match(/#([0-f])([0-f])([0-f])\s*?$/i))) {
			return '#' +
				match[1] + match[1] +
				match[2] + match[2] +
				match[3] + match[3];
		}

		return colorStr;
	};

	var bbcodes = {
		// START_COMMAND: Bold
		b: {
			tags: {
				b: null,
				strong: null
			},
			styles: {
				// 401 is for FF 3.5
				'font-weight': ['bold', 'bolder', '401', '700', '800', '900']
			},
			format: '[b]{0}[/b]',
			html: '<strong>{0}</strong>'
		},
		// END_COMMAND

		// START_COMMAND: Italic
		i: {
			tags: {
				i: null,
				em: null
			},
			styles: {
				'font-style': ['italic', 'oblique']
			},
			format: '[i]{0}[/i]',
			html: '<em>{0}</em>'
		},
		// END_COMMAND

		// START_COMMAND: Underline
		u: {
			tags: {
				u: null
			},
			styles: {
				'text-decoration': ['underline']
			},
			format: '[u]{0}[/u]',
			html: '<u>{0}</u>'
		},
		// END_COMMAND

		// START_COMMAND: Strikethrough
		s: {
			tags: {
				s: null,
				strike: null
			},
			styles: {
				'text-decoration': ['line-through']
			},
			format: '[s]{0}[/s]',
			html: '<s>{0}</s>'
		},
		// END_COMMAND

		// START_COMMAND: Subscript
		sub: {
			tags: {
				sub: null
			},
			format: '[sub]{0}[/sub]',
			html: '<sub>{0}</sub>'
		},
		// END_COMMAND

		// START_COMMAND: Superscript
		sup: {
			tags: {
				sup: null
			},
			format: '[sup]{0}[/sup]',
			html: '<sup>{0}</sup>'
		},
		// END_COMMAND

		// START_COMMAND: Font
		font: {
			tags: {
				font: {
					face: null
				}
			},
			styles: {
				'font-family': null
			},
			quoteType: BBCodeParser.QuoteType.never,
			format: function ($element, content) {
				var font;

				if (!$element.is('font') || !(font = $element.attr('face'))) {
					font = $element.css('font-family');
				}

				return '[font=' + _stripQuotes(font) + ']' +
					content + '[/font]';
			},
			html: '<font face="{defaultattr}">{0}</font>'
		},
		// END_COMMAND

		// START_COMMAND: Size
		size: {
			tags: {
				font: {
					size: null
				}
			},
			styles: {
				'font-size': null
			},
			format: function (element, content) {
				var	fontSize = element.attr('size'),
					size     = 2;

				if (!fontSize) {
					fontSize = element.css('fontSize');
				}

				// Most browsers return px value but IE returns 1-7
				if (fontSize.indexOf('px') > -1) {
					// convert size to an int
					fontSize = fontSize.replace('px', '') - 0;

					if (fontSize < 12) {
						size = 1;
					}
					if (fontSize > 15) {
						size = 3;
					}
					if (fontSize > 17) {
						size = 4;
					}
					if (fontSize > 23) {
						size = 5;
					}
					if (fontSize > 31) {
						size = 6;
					}
					if (fontSize > 47) {
						size = 7;
					}
				} else {
					size = fontSize;
				}

				return '[size=' + size + ']' + content + '[/size]';
			},
			html: '<font size="{defaultattr}">{!0}</font>'
		},
		// END_COMMAND

		// START_COMMAND: Color
		color: {
			tags: {
				font: {
					color: null
				}
			},
			styles: {
				color: null
			},
			quoteType: BBCodeParser.QuoteType.never,
			format: function ($element, content) {
				var	color;

				if (!$element.is('font') || !(color = $element.attr('color'))) {
					color = $element[0].style.color || $element.css('color');
				}

				return '[color=' + _normaliseColour(color) + ']' +
					content + '[/color]';
			},
			html: function (token, attrs, content) {
				return '<font color="' +
					escapeEntities(_normaliseColour(attrs.defaultattr), true) +
					'">' + content + '</font>';
			}
		},
		// END_COMMAND

		// START_COMMAND: Lists
		ul: {
			tags: {
				ul: null
			},
			breakStart: true,
			isInline: false,
			skipLastLineBreak: true,
			format: '[ul]{0}[/ul]',
			html: '<ul>{0}</ul>'
		},
		list: {
			breakStart: true,
			isInline: false,
			skipLastLineBreak: true,
			html: '<ul>{0}</ul>'
		},
		ol: {
			tags: {
				ol: null
			},
			breakStart: true,
			isInline: false,
			skipLastLineBreak: true,
			format: '[ol]{0}[/ol]',
			html: '<ol>{0}</ol>'
		},
		li: {
			tags: {
				li: null
			},
			isInline: false,
			closedBy: ['/ul', '/ol', '/list', '*', 'li'],
			format: '[li]{0}[/li]',
			html: '<li>{0}</li>'
		},
		'*': {
			isInline: false,
			closedBy: ['/ul', '/ol', '/list', '*', 'li'],
			html: '<li>{0}</li>'
		},
		// END_COMMAND

		// START_COMMAND: Table
		table: {
			tags: {
				table: null
			},
			isInline: false,
			isHtmlInline: true,
			skipLastLineBreak: true,
			format: '[table]{0}[/table]',
			html: '<table>{0}</table>'
		},
		tr: {
			tags: {
				tr: null
			},
			isInline: false,
			skipLastLineBreak: true,
			format: '[tr]{0}[/tr]',
			html: '<tr>{0}</tr>'
		},
		th: {
			tags: {
				th: null
			},
			allowsEmpty: true,
			isInline: false,
			format: '[th]{0}[/th]',
			html: '<th>{0}</th>'
		},
		td: {
			tags: {
				td: null
			},
			allowsEmpty: true,
			isInline: false,
			format: '[td]{0}[/td]',
			html: '<td>{0}</td>'
		},
		// END_COMMAND

		// START_COMMAND: Emoticons
		emoticon: {
			allowsEmpty: true,
			tags: {
				img: {
					src: null,
					'data-sceditor-emoticon': null
				}
			},
			format: function ($elm, content) {
				return $elm.data('sceditor-emoticon') + content;
			},
			html: '{0}'
		},
		// END_COMMAND

		// START_COMMAND: Horizontal Rule
		hr: {
			tags: {
				hr: null
			},
			allowsEmpty: true,
			isSelfClosing: true,
			isInline: false,
			format: '[hr]{0}',
			html: '<hr />'
		},
		// END_COMMAND

		// START_COMMAND: Image
		img: {
			allowsEmpty: true,
			tags: {
				img: {
					src: null
				}
			},
			allowedChildren: ['#'],
			quoteType: BBCodeParser.QuoteType.never,
			format: function ($element, content) {
				var	width, height,
					attribs   = '',
					element   = $element[0],
					style     = function (name) {
						return element.style ? element.style[name] : null;
					};

				// check if this is an emoticon image
				if ($element.attr('data-sceditor-emoticon')) {
					return content;
				}

				width = $element.attr('width') || style('width');
				height = $element.attr('height') || style('height');

				// only add width and height if one is specified
				if ((element.complete && (width || height)) ||
					(width && height)) {
					attribs = '=' + $element.width() + 'x' + $element.height();
				}

				return '[img' + attribs + ']' + $element.attr('src') + '[/img]';
			},
			html: function (token, attrs, content) {
				var	undef, width, height, match,
					attribs = '';

				// handle [img width=340 height=240]url[/img]
				width  = attrs.width;
				height = attrs.height;

				// handle [img=340x240]url[/img]
				if (attrs.defaultattr) {
					match = attrs.defaultattr.split(/x/i);

					width  = match[0];
					height = (match.length === 2 ? match[1] : match[0]);
				}

				if (width !== undef) {
					attribs += ' width="' + escapeEntities(width, true) + '"';
				}

				if (height !== undef) {
					attribs += ' height="' + escapeEntities(height, true) + '"';
				}

				return '<img' + attribs +
					' src="' + escapeUriScheme(content) + '" />';
			}
		},
		// END_COMMAND

		// START_COMMAND: URL
		url: {
			allowsEmpty: true,
			tags: {
				a: {
					href: null
				}
			},
			quoteType: BBCodeParser.QuoteType.never,
			format: function (element, content) {
				var url = element.attr('href');

				// make sure this link is not an e-mail,
				// if it is return e-mail BBCode
				if (url.substr(0, 7) === 'mailto:') {
					return '[email="' + url.substr(7) + '"]' +
						content + '[/email]';
				}

				return '[url=' + url + ']' + content + '[/url]';
			},
			html: function (token, attrs, content) {
				attrs.defaultattr =
					escapeEntities(attrs.defaultattr, true) || content;

				return '<a href="' + escapeUriScheme(attrs.defaultattr) +
					'">' + content + '</a>';
			}
		},
		// END_COMMAND

		// START_COMMAND: E-mail
		email: {
			quoteType: BBCodeParser.QuoteType.never,
			html: function (token, attrs, content) {
				return '<a href="mailto:' +
					(escapeEntities(attrs.defaultattr, true) || content) +
					'">' + content + '</a>';
			}
		},
		// END_COMMAND

		// START_COMMAND: Quote
		quote: {
			tags: {
				blockquote: null
			},
			isInline: false,
			quoteType: BBCodeParser.QuoteType.never,
			format: function (element, content) {
				var	author = '';
				var $elm  = $(element);
				var $cite = $elm.children('cite').first();

				if ($cite.length === 1 || $elm.data('author')) {
					author = $cite.text() || $elm.data('author');

					$elm.data('author', author);
					$cite.remove();

					content	= this.elementToBbcode($(element));
					author  = '=' + author.replace(/(^\s+|\s+$)/g, '');

					$elm.prepend($cite);
				}

				return '[quote' + author + ']' + content + '[/quote]';
			},
			html: function (token, attrs, content) {
				if (attrs.defaultattr) {
					content = '<cite>' + escapeEntities(attrs.defaultattr) +
						'</cite>' + content;
				}

				return '<blockquote>' + content + '</blockquote>';
			}
		},
		// END_COMMAND

		// START_COMMAND: Code
		code: {
			tags: {
				code: null
			},
			isInline: false,
			isPreFormatted: true,
			codeInputModeInside: true,
			allowedChildren: ['#', '#newline'],
			format: '[code]{0}[/code]',
			html: '<code>{0}</code>'
		},
		// END_COMMAND


		// START_COMMAND: Left
		left: {
			styles: {
				'text-align': [
					'left',
					'-webkit-left',
					'-moz-left',
					'-khtml-left'
				]
			},
			isInline: false,
			format: '[left]{0}[/left]',
			html: '<div align="left">{0}</div>'
		},
		// END_COMMAND

		// START_COMMAND: Centre
		center: {
			styles: {
				'text-align': [
					'center',
					'-webkit-center',
					'-moz-center',
					'-khtml-center'
				]
			},
			isInline: false,
			format: '[center]{0}[/center]',
			html: '<div align="center">{0}</div>'
		},
		// END_COMMAND

		// START_COMMAND: Right
		right: {
			styles: {
				'text-align': [
					'right',
					'-webkit-right',
					'-moz-right',
					'-khtml-right'
				]
			},
			isInline: false,
			format: '[right]{0}[/right]',
			html: '<div align="right">{0}</div>'
		},
		// END_COMMAND

		// START_COMMAND: Justify
		justify: {
			styles: {
				'text-align': [
					'justify',
					'-webkit-justify',
					'-moz-justify',
					'-khtml-justify'
				]
			},
			isInline: false,
			format: '[justify]{0}[/justify]',
			html: '<div align="justify">{0}</div>'
		},
		// END_COMMAND

		// START_COMMAND: YouTube
		youtube: {
			allowsEmpty: true,
			tags: {
				iframe: {
					'data-youtube-id': null
				}
			},
			format: function (element, content) {
				element = element.attr('data-youtube-id');

				return element ? '[youtube]' + element + '[/youtube]' : content;
			},
			html: '<iframe width="560" height="315" frameborder="0" ' +
				'src="http://www.youtube.com/embed/{0}?wmode=opaque" ' +
				'data-youtube-id="{0}" allowfullscreen></iframe>'
		},
		// END_COMMAND


		// START_COMMAND: Rtl
		rtl: {
			styles: {
				direction: ['rtl']
			},
			format: '[rtl]{0}[/rtl]',
			html: '<div style="direction: rtl">{0}</div>'
		},
		// END_COMMAND

		// START_COMMAND: Ltr
		ltr: {
			styles: {
				direction: ['ltr']
			},
			format: '[ltr]{0}[/ltr]',
			html: '<div style="direction: ltr">{0}</div>'
		},
		// END_COMMAND

		// this is here so that commands above can be removed
		// without having to remove the , after the last one.
		// Needed for IE.
		ignore: {}
	};

	/**
	 * Static BBCode helper class
	 * @class command
	 * @name jQuery.plugins.bbcode.bbcode
	 */
	sceditorPlugins.bbcode.bbcode =
	/** @lends jQuery.plugins.bbcode.bbcode */
	{
		/**
		 * Gets a BBCode
		 *
		 * @param {String} name
		 * @return {Object|null}
		 * @since v1.3.5
		 */
		get: function (name) {
			return bbcodes[name] || null;
		},

		/**
		 * <p>Adds a BBCode to the parser or updates an existing
		 * BBCode if a BBCode with the specified name already exists.</p>
		 *
		 * @param {String} name
		 * @param {Object} bbcode
		 * @return {this|false} Returns false if name or bbcode is false
		 * @since v1.3.5
		 */
		set: function (name, bbcode) {
			if (!name || !bbcode) {
				return false;
			}

			// merge any existing command properties
			bbcode = $.extend(bbcodes[name] || {}, bbcode);

			bbcode.remove = function () {
				delete bbcodes[name];
			};

			bbcodes[name] = bbcode;

			return this;
		},

		/**
		 * Renames a BBCode
		 *
		 * This does not change the format or HTML handling, those must be
		 * changed manually.
		 *
		 * @param  {String} name    [description]
		 * @param  {String} newName [description]
		 * @return {this|false}
		 * @since v1.4.0
		 */
		rename: function (name, newName) {
			if (name in bbcodes) {
				bbcodes[newName] = bbcodes[name];

				delete bbcodes[name];
			} else {
				return false;
			}

			return this;
		},

		/**
		 * Removes a BBCode
		 *
		 * @param {String} name
		 * @return {this}
		 * @since v1.3.5
		 */
		remove: function (name) {
			if (name in bbcodes) {
				delete bbcodes[name];
			}

			return this;
		}
	};

	/**
	 * Deprecated, use plugins: option instead. I.e.:
	 *
	 * $('textarea').sceditor({
	 *      plugins: 'bbcode'
	 * });
	 *
	 * @deprecated
	 */
	$.fn.sceditorBBCodePlugin = function (options) {
		options = options || {};

		if ($.isPlainObject(options)) {
			options.plugins = (options.plugins || '') + 'bbcode';
		}

		return this.sceditor(options);
	};

	/**
	 * Converts CSS RGB and hex shorthand into hex
	 *
	 * @since v1.4.0
	 * @param {String} colorStr
	 * @return {String}
	 * @deprecated
	 */
	sceditorPlugins.bbcode.normaliseColour = _normaliseColour;
	sceditorPlugins.bbcode.formatString    = _formatString;
	sceditorPlugins.bbcode.stripQuotes     = _stripQuotes;
	sceditorPlugins.bbcode.bbcodes         = bbcodes;
	SCEditor.BBCodeParser                  = BBCodeParser;
})(jQuery, window, document);
;(function ($) {
	'use strict';

	$.sceditor.plugins.undo = function () {
		var base = this;
		var editor;
		var charChangedCount = 0;
		var previousValue;

		var undoLimit  = 50;
		var redoStates = [];
		var undoStates = [];
		var ignoreNextValueChanged = false;

		/**
		 * Sets the editor to the specified state.
		 *
		 * @param  {Object} state
		 * @private
		 */
		var applyState = function (state) {
			ignoreNextValueChanged = true;

			previousValue = state.value;

			editor.sourceMode(state.sourceMode);
			editor.val(state.value, false);
			editor.focus();

			if (state.sourceMode) {
				editor.sourceEditorCaret(state.caret);
			} else {
				editor.getRangeHelper().restoreRange();
			}

			ignoreNextValueChanged = false;
		};


		/**
		 * Caluclates the number of characters that have changed
		 * between two strings.
		 *
		 * @param {String} strA
		 * @param {String} strB
		 * @return {String}
		 * @private
		 */
		var simpleDiff = function (strA, strB) {
			var start, end, aLenDiff, bLenDiff,
				aLength = strA.length,
				bLength = strB.length,
				length  = Math.max(aLength, bLength);

			// Calculate the start
			for (start = 0; start < length; start++) {
				if (strA.charAt(start) !== strB.charAt(start)) {
					break;
				}
			}

			// Calculate the end
			aLenDiff = aLength < bLength ? bLength - aLength : 0;
			bLenDiff = bLength < aLength ? aLength - bLength : 0;

			for (end = length - 1; end >= 0; end--) {
				if (strA.charAt(end - aLenDiff) !==
						strB.charAt(end - bLenDiff)) {
					break;
				}
			}

			return (end - start) + 1;
		};

		base.init = function () {
			// The this variable will be set to the instance of the editor
			// calling it, hence why the plugins "this" is saved to the base
			// variable.
			editor = this;

			undoLimit = editor.undoLimit || undoLimit;

			// addShortcut is the easiest way to add handlers to specific
			// shortcuts
			editor.addShortcut('ctrl+z', base.undo);
			editor.addShortcut('ctrl+shift+z', base.redo);
			editor.addShortcut('ctrl+y', base.redo);
		};

		base.undo = function () {
			var state = undoStates.pop();
			var rawEditorValue = editor.val(null, false);

			if (state && !redoStates.length && rawEditorValue === state.value) {
				state = undoStates.pop();
			}

			if (state) {
				if (!redoStates.length) {
					redoStates.push({
						'caret': editor.sourceEditorCaret(),
						'sourceMode': editor.sourceMode(),
						'value': rawEditorValue
					});
				}

				redoStates.push(state);
				applyState(state);
			}

			return false;
		};

		base.redo = function () {
			var state = redoStates.pop();

			if (!undoStates.length) {
				undoStates.push(state);
				state = redoStates.pop();
			}

			if (state) {
				undoStates.push(state);
				applyState(state);
			}

			return false;
		};

		base.signalReady = function () {
			var rawValue = editor.val(null, false);

			// Store the initial value as the last value
			previousValue = rawValue;

			undoStates.push({
				'caret': this.sourceEditorCaret(),
				'sourceMode': this.sourceMode(),
				'value': rawValue
			});
		};

		/**
		 * Handle the valueChanged signal.
		 *
		 * e.rawValue will either be the raw HTML from the WYSIWYG editor with
		 * the rangeHelper range markers inserted, or it will be the raw value
		 * of the source editor (BBCode or HTML depening on plugins).
		 * @return {void}
		 */
		base.signalValuechangedEvent = function (e) {
			var rawValue = e.rawValue;

			if (undoLimit > 0 && undoStates.length > undoLimit) {
				undoStates.shift();
			}

			// If the editor hasn't fully loaded yet,
			// then the previous value won't be set.
			if (ignoreNextValueChanged || !previousValue ||
					previousValue === rawValue) {
				return;
			}

			// Value has changed so remove all redo states
			redoStates.length = 0;
			charChangedCount += simpleDiff(previousValue, rawValue);

			if (charChangedCount < 20) {
				return;
			// ??
			} else if (charChangedCount < 50 && !/\s$/g.test(e.rawValue)) {
				return;
			}

			undoStates.push({
				'caret': editor.sourceEditorCaret(),
				'sourceMode': editor.sourceMode(),
				'value': rawValue
			});

			charChangedCount = 0;
			previousValue = rawValue;
		};
	};
}(jQuery));
