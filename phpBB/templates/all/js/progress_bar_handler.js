/*****************************************************************
 *
 * jsProgressBarHandler 0.3.3 - by Bramus! - http://www.bram.us/
 *
 * v 0.3.3 - 2008.11.10 - UPD: fixed IE compatibility issue (thanks Kevin - Sep 19 2008 / 6pm)
 *                      - UPD: setPercentage now parses the targetPercentage to an Integer to avoid infinite loop (thanks Jack - Sep 07 2008 / 9pm)
 *                      - UPD: Moved from Event.Observe(window, 'load', fn) to document.observe('dom:loaded', fn) in order to force people to use an up to date Prototype release.
 *                      - UPD: setPercentage now takes an overrideQueue param. If set the current queue is cleared.
 *                      - ADD: Added onTick callback event which gets called when the percentage is updated.
 *                      - ADD: Added stable (as in "non-crashing") versions of the additions which first surfaced in the (unreleased) 0.3.2 release
 *                             Preloading support partially implemented in IE as all versions (IE6,7&8) are quite hard to tame (one time they work, the next reload they don't anymore)
 * v 0.3.2 - 2008.04.09 (*UNRELEASED*)
 *                      - ADD: implemented preloading of images to avoid slight flicker when switching images (BUGGY!)
 *                      - ADD: percentage image now has class percentImage and percentage Text now has class percentText; This allows you to style the output easily.
 * v 0.3.1 - 2008.02.20 - UPD: fixed queue bug when animate was set to false (thanks Jamie Chong)
 *                      - UPD: update Prototype to version 1.6.0.2
 * v 0.3.0 - 2008.02.01 - ADD: animation queue, prevents from the progressbar getting stuck when multiple calls are made during an animation
 *                      - UPD: multiple barImages now work properly in Safari
 * v 0.2.1 - 2007.12.20 - ADD: option : set boxImage
 *                        ADD: option : set barImage (one or more)
 *                        ADD: option : showText
 * v 0.2   - 2007.12.13 - SYS: rewrite in 2 classs including optimisations
 *                        ADD: Config options
 * v 0.1   - 2007.08.02 - initial release
 *
 * @see http://www.barenakedapp.com/the-design/displaying-percentages on how to create a progressBar Background Image!
 *
 * Licensed under the Creative Commons Attribution 2.5 License - http://creativecommons.org/licenses/by/2.5/
 *
 *****************************************************************/


 /**
  * CONFIG
  * -------------------------------------------------------------
  */

 	// Should jsProgressBarHandler hook itself to all span.progressBar elements? - default : true
		var autoHook	= true;	
 
 	// Default Options
		var defaultOptions = {
			animate		: true,										// Animate the progress? - default: true
			showText	: true,										// show text with percentage in next to the progressbar? - default : true
			width		: 120,										// Width of the progressbar - don't forget to adjust your image too!!!
			boxImage	: 'images/progress_bar/percent_image.png',			// boxImage : image around the progress bar
			barImage	: 'images/progress_bar/percent_image_back.png',	// Image to use in the progressbar. Can be an array of images too.
			height		: 12,										// Height of the progressbar - don't forget to adjust your image too!!!
			onTick		: function(pbObj) { return true }
		}

 /**
  * NO NEED TO CHANGE ANYTHING BENEATH THIS LINE
  * -------------------------------------------------------------
  */

	/**
	 * JS_BRAMUS Object
	 * -------------------------------------------------------------
	 */

		if (!JS_BRAMUS) { var JS_BRAMUS = new Object(); }


	/**
	 * ProgressBar Class
	 * -------------------------------------------------------------
	 */

		JS_BRAMUS.jsProgressBar = Class.create();

		JS_BRAMUS.jsProgressBar.prototype = {


			/**
			 * Datamembers
			 * -------------------------------------------------------------
			 */
			 
				el				: null,								// Element where to render the progressBar in
				id				: null,								// Unique ID of the progressbar
				percentage		: null,								// Percentage of the progressbar

				options			: null,								// The options

				initialPos		: null,								// Initial postion of the background in the progressbar
				initialPerc		: null,								// Initial percentage the progressbar should hold
				pxPerPercent	: null,								// Number of pixels per 1 percent

				backIndex		: null,								// index in the array of background images currently used
				numPreloaded	: null,								// number of images preloaded

				running			: null,								// is this one running (being animated) or not?

				queue			: false,							// queue of percentages to set to


			/**
			 * Constructor
			 *
			 * @param HTMLElement el
			 * @param string id
			 * @param int percentage
			 * @return void
			 * -------------------------------------------------------------
			 */
			 
				initialize		: function(el, percentage, options) {

					// get the options
					this.options			= Object.clone(defaultOptions);
					Object.extend(this.options, options || {});

					// datamembers from arguments
					this.el				= $(el);
					this.id				= $(el).id;
					this.percentage		= 0;							// Set to 0 intially, we'll change this later.
					this.backIndex		= 0;							// Set to 0 initially
					this.numPreloaded	= 0;							// Set to 0 initially
					this.running		= false;						// Set to false initially
					this.queue			= Array();						// Set to empty Array initially

					// datamembers which are calculatef
					this.imgWidth		= this.options.width * 2;		// define the width of the image (twice the width of the progressbar)
					this.initialPos		= this.options.width * (-1);	// Initial postion of the background in the progressbar (0% is the middle of our image!)
					this.pxPerPercent	= this.options.width / 100;		// Define how much pixels go into 1%
					this.initialPerc	= percentage;					// Store this, we'll need it later.

					// enfore backimage array
					if (this.options.barImage.constructor != Array) { 	// used to be (but doesn't work in Safari): if (this.options.barImage.constructor.toString().indexOf("Array") == -1) {
						this.options.barImage = Array(this.options.barImage);
					}

					// preload Images
					this.preloadImages();

				},


			/**
			 * Preloads the images needed for the progressbar
			 *
			 * @return void
			 * -------------------------------------------------------------
			 */

				preloadImages	: function() {

					// loop all barimages
					for (i = 0; i < this.options.barImage.length; i++) {

						// create new image ref
						var newImage = null;
						newImage = new Image();

						// set onload, onerror and onabort functions
						newImage.onload		= function() { this.numPreloaded++; }.bind(this);
						newImage.onerror	= function() { this.numPreloaded++; }.bind(this);
						newImage.onabort	= function() { this.numPreloaded++; }.bind(this);

						// set image source (preload it!)
						newImage.src = this.options.barImage[i];

						// image is in cache
						if (newImage.complete) {
							this.numPreloaded++;
						}
						
					}

					// if not IE, check if they're loaded
					if (!Prototype.Browser.IE) {
						this.checkPreloadedImages();

					// if IE, just init the visuals as it's quite hard to tame all IE's
					} else {
						this.initVisuals();
					}

				},


			/**
			 * Check whether all images are preloaded and loads the percentage if so
			 *
			 * @return void
			 * -------------------------------------------------------------
			 */

			 	checkPreloadedImages	: function() {

					// all images are loaded, go init the visuals
					if (parseInt(this.numPreloaded,10) >= parseInt(this.options.barImage.length,10) ) {

						// initVisuals
						this.initVisuals();

					// not all images are loaded ... wait a little and then retry
					} else {

						if ( parseInt(this.numPreloaded,10) <= parseInt(this.options.barImage.length,10) ) {
							// $(this.el).update(this.id + ' : ' + this.numPreloaded + '/' + this.options.barImage.length);
							setTimeout(function() { this.checkPreloadedImages(); }.bind(this), 100);
						}

					}

				},


			/**
			 * Intializes the visual output and sets the percentage
			 *
			 * @return void
			 * -------------------------------------------------------------
			 */				
				
				initVisuals		: function () {

					// create the visual aspect of the progressBar
					$(this.el).update(
						'<img id="' + this.id + '_percentImage" src="' + this.options.boxImage + '" alt="0%" style="width: ' + this.options.width + 'px; height: ' + this.options.height + 'px; background-position: ' + this.initialPos + 'px 50%; background-image: url(' + this.options.barImage[this.backIndex] + '); padding: 0; margin: 0;" class="percentImage" />' + 
						((this.options.showText == true)?'<span id="' + this.id + '_percentText" class="percentText">0%</span>':''));
				
					// set the percentage
					this.setPercentage(this.initialPerc);
				},
			
			
			/**
			 * Sets the percentage of the progressbar
			 *
			 * @param string targetPercentage
			 * @param boolen clearQueue
			 * @return void
			 * -------------------------------------------------------------
			 */
				setPercentage	: function(targetPercentage, clearQueue) {
					
					// if clearQueue is set, empty the queue and then set the percentage
					if (clearQueue) {
						
						this.percentage = (this.queue.length != 0) ? this.queue[0] : targetPercentage;
						this.timer		= null;
						this.queue 		= [];
						
						setTimeout(function() { this.setPercentage(targetPercentage); }.bind(this), 10);
						
					// no clearQueue defined, set the percentage
					} else {
					
						// add the percentage on the queue
						this.queue.push(targetPercentage);
						
						// process the queue (if not running already)
						if (this.running == false) {
							this.processQueue();
						}
					}
					
				},
			
			
			/**
			 * Processes the queue
			 *
			 * @return void
			 * -------------------------------------------------------------
			 */
				
				processQueue	: function() {
					
					// stuff on queue?
					if (this.queue.length > 0) {
						
						// tell the world that we're busy
						this.running = true;
						
						// process the entry
						this.processQueueEntry(this.queue[0]);
						
					// no stuff on queue
					} else {
							
						// return;
						return;
							
					}
					
				},
			
			
			/**
			 * Processes an entry from the queue (viz. animates it)
			 *
			 * @param string targetPercentage
			 * @return void
			 * -------------------------------------------------------------
			 */
				
				processQueueEntry	: function(targetPercentage) {
										
					// get the current percentage
					var curPercentage	= parseInt(this.percentage,10);
					
					// define the new percentage
					if ((targetPercentage.toString().substring(0,1) == "+") || (targetPercentage.toString().substring(0,1) == "-")) {
						targetPercentage	= curPercentage + parseInt(targetPercentage);
					}
				
					// min and max percentages
					if (targetPercentage < 0)		targetPercentage = 0;
					if (targetPercentage > 100)		targetPercentage = 100;
					
					// if we don't need to animate, just change the background position right now and return
					if (this.options.animate == false) {
						
						// remove the entry from the queue 
						this.queue.splice(0,1);	// @see: http://www.bram.us/projects/js_bramus/jsprogressbarhandler/#comment-174878
						
						// Change the background position (and update this.percentage)
						this._setBgPosition(targetPercentage);
					
						// call onTick
						if (!this.options.onTick(this)) {
							return;	
						}
						
						// we're not running anymore
						this.running = false;
						
						// continue processing the queue
						this.processQueue();
						
						// we're done!
						return;
					}
					
					// define if we need to add/subtract something to the current percentage in order to reach the target percentage
					if (targetPercentage != curPercentage) {					
						if (curPercentage < targetPercentage) {
							newPercentage = curPercentage + 1;
						} else {
							newPercentage = curPercentage - 1;	
						}						
						callTick = true;						
					} else {
						newPercentage = curPercentage;
						callTick = false;
					}											
											
					// Change the background position (and update this.percentage)
					this._setBgPosition(newPercentage);
					
					// call onTick
					if (callTick && !this.options.onTick(this)) {
						return;	
					}
					
					// Percentage not reached yet : continue processing entry
					if (curPercentage != newPercentage) {
						
						this.timer = setTimeout(function() { this.processQueueEntry(targetPercentage); }.bind(this), 10);
						
					// Percentage reached!
					} else {
														  
						// remove the entry from the queue
						this.queue.splice(0,1);
						
						// we're not running anymore
						this.running = false;	
						
						// unset timer
						this.timer = null;
						
						// process the rest of the queue
						this.processQueue();
						
						// we're done!
						return;
					}
					
				},
			
			
			/**
			 * Gets the percentage of the progressbar
			 *
			 * @return int
			 */
				getPercentage		: function(id) {
					return this.percentage;
				},
			
			
			/**
			 * Set the background position
			 *
			 * @param int percentage
			 */
				_setBgPosition		: function(percentage) {
					// adjust the background position
						$(this.id + "_percentImage").style.backgroundPosition 	= (this.initialPos + (percentage * this.pxPerPercent)) + "px 50%";
												
					// adjust the background image and backIndex
						var newBackIndex										= Math.floor((percentage-1) / (100/this.options.barImage.length));
						
						if ((newBackIndex != this.backIndex) && (this.options.barImage[newBackIndex] != undefined)) {
							$(this.id + "_percentImage").style.backgroundImage 	= "url(" + this.options.barImage[newBackIndex] + ")";
						}
						
						this.backIndex											= newBackIndex;
					
					// Adjust the alt & title of the image
						$(this.id + "_percentImage").alt 						= percentage + "%";
						$(this.id + "_percentImage").title 						= percentage + "%";
						
					// Update the text
						if (this.options.showText == true) {
							$(this.id + "_percentText").update("" + percentage + "%");
						}
						
					// adjust datamember to stock the percentage
						this.percentage	= percentage;
				}
		}


	/**
	 * ProgressHandlerBar Class - automatically create ProgressBar instances
	 * -------------------------------------------------------------
	 */
	 
		JS_BRAMUS.jsProgressBarHandler = Class.create();

	
		JS_BRAMUS.jsProgressBarHandler.prototype = {
			
			
			/**
			 * Datamembers
			 * -------------------------------------------------------------
			 */
			 
				pbArray				: new Array(),		// Array of progressBars
		
		
			/**
			 * Constructor
			 *
			 * @return void
			 * -------------------------------------------------------------
			 */
			 
				initialize			: function() {		
				
					// get all span.progressBar elements
					$$('span.progressBar').each(function(el) {
														 
						// create a progressBar for each element
						this.pbArray[el.id]	= new JS_BRAMUS.jsProgressBar(el, parseInt(el.innerHTML.replace("%",""))); 
					
					}.bind(this));
				},
		
		
			/**
			 * Set the percentage of a progressbar
			 *
			 * @param string el
			 * @param string percentage
			 * @return void
			 * -------------------------------------------------------------
			 */
				setPercentage		: function(el, percentage, clearQueue) {
					this.pbArray[el].setPercentage(percentage, clearQueue);
				},
		
		
			/**
			 * Get the percentage of a progressbar
			 *
			 * @param string el
			 * @return int percentage
			 * -------------------------------------------------------------
			 */
				getPercentage		: function(el) {
					return this.pbArray[el].getPercentage();
				}
			
		}


	/**
	 * ProgressHandlerBar Class - hook me or not?
	 * -------------------------------------------------------------
	 */
	
		if (autoHook == true) {
			function initProgressBarHandler() { myJsProgressBarHandler = new JS_BRAMUS.jsProgressBarHandler(); }
			document.observe('dom:loaded', initProgressBarHandler, false);
		}