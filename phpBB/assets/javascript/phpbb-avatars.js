/* global phpbb */
(function($) { // Avoid conflicts with other libraries
	'use strict';

	/**
	 * phpBB Avatars namespace.
	 *
	 * Handles cropping for local file uploads.
	 */
	phpbb.avatars = {
		cropper: null,
		image: null,

		/** @type {jQuery} */
		$form: null,

		/** @type {jQuery} */
		$buttons: $('#avatar-cropper-buttons'),

		/** @type {jQuery} */
		$box: $('.c-avatar-box'),

		/** @type {jQuery} */
		$data: $('#avatar-cropper-data'),

		/** @type {jQuery} */
		$input: $('#avatar_upload_file'),

		/** @type {jQuery} */
		$driver: $('#avatar_driver'),

		/** @type {string} */
		driverUpload: 'avatar_driver_upload',

		/**
		 * Initialise avatar cropping.
		 */
		init() {
			// If the cropper library is not available
			if (!$.isFunction($.fn.cropper)) {
				return;
			}

			// Correctly position the cropper buttons
			this.$buttons.appendTo(this.$box);

			// Ensure we have an img for the cropping
			if (this.$box.children('img').length === 0) {
				const $avatarImg = $('<img src="" alt="">');
				$avatarImg.setAttribute('width', phpbb.avatars.$data.data().maxWidth);
				$avatarImg.setAttribute('height', phpbb.avatars.$data.data().maxHeight);
				$avatarImg.addClass('avatar');
				this.image = $avatarImg;
				this.$box.prepend($avatarImg);
			} else {
				this.image = this.$box.children('img');
			}

			this.bindInput();
			this.bindSelect();
			this.bindSubmit();
		},

		/**
		 * Destroy (undo) any initialisation.
		 */
		destroy() {
			this.$buttons.find('[data-cropper-action]').off('click.phpbb.avatars');
			this.image.off('crop.phpbb.avatars');
			this.$form.off('submit');

			this.$data.val('');
			this.$buttons.hide();
			this.$box.removeClass('c-cropper-avatar-box');

			if (this.cropper !== null) {
				this.cropper.destroy();
			}
		},

		/**
		 * Bind a function to the avatar driver <select> element.
		 *
		 * If a different driver than the "upload" driver is selected, the cropper is destroyed.
		 * Otherwise if the "upload" driver is (re-)selected, and it has a value, initialise it.
		 */
		bindSelect() {
			this.$driver.on('change', function() {
				if ($(this).val() === phpbb.avatars.driverUpload) {
					if (phpbb.avatars.$input.val() !== '') {
						phpbb.avatars.$input.trigger('change');
					}
				} else {
					phpbb.avatars.destroy();
				}
			});
		},

		/**
		 * Bind a function to the avatar file upload <input> element.
		 *
		 * If a file was chosen and it is a valid image file, the cropper is initialised.
		 * Otherwise the cropper is destroyed.
		 */
		bindInput() {
			this.$input.on('change', function() {
				const fileReader = new FileReader();

				if (this.files[0].type.match('image.*')) {
					fileReader.readAsDataURL(this.files[0]);

					fileReader.addEventListener('load', function() {
						phpbb.avatars.image.cropper('destroy').attr('src', this.result).addClass('avatar');
						phpbb.avatars.$box.addClass('c-cropper-avatar-box');
						phpbb.avatars.initCropper();
						phpbb.avatars.initButtons();
					});
				} else {
					phpbb.avatars.destroy();
				}
			});
		},

		/**
		 * Bind submit button to be handled by ajax submit
		 */
		bindSubmit() {
			const $this = this;
			$this.$form = this.$input.closest('form');
			$this.$form.on('submit', () => {
				const data = phpbb.avatars.$data.data();

				const avatarCanvas = phpbb.avatars.cropper.getCroppedCanvas({
					maxWidth: 4096, // High values for max quality cropping
					maxHeight: 4096, // High values for max quality cropping
				});

				// eslint-disable-next-line no-undef
				const hermiteResize = new Hermite_class();
				hermiteResize.resample_single(avatarCanvas, data.maxWidth, data.maxHeight, true);

				avatarCanvas.toBlob(blob => {
					const formData = new FormData($this.$form[0]);
					formData.set('avatar_upload_file', blob, $this.getUploadFileName());
					formData.set('submit', '1');

					const canvasDataUrl = avatarCanvas.toDataURL('image/png');

					$.ajax({
						url: $this.$form.attr('action'),
						type: 'POST',
						data: formData,
						processData: false,
						contentType: false,
						success(response) {
							$this.uploadDone(response, canvasDataUrl);
						},
						error() {
							console.log('Upload error');
						},
					});
				}, 'image/png');

				return false;
			});
		},

		/**
		 * Get upload filename for the blob data
		 *
		 * As the blob data is always in png format, we'll replace the file
		 * extension in the upload name with one that ends with .png
		 *
		 * @return {string} Upload file name
		 */
		getUploadFileName() {
			const originalName = this.$input[0].files[0].name;

			return originalName.replace(/\.[^/\\.]+$/, '.png');
		},

		/**
		 * Handle response from avatar submission
		 * @param {Object} response AJAX response object
		 * @param {string} canvasDataUrl Uploaded canvas element as data URL
		 */
		uploadDone(response, canvasDataUrl) {
			if (typeof response !== 'object') {
				return;
			}

			// Handle errors while deleting file
			if (typeof response.error === 'undefined') {
				const alert = phpbb.alert(response.MESSAGE_TITLE, response.MESSAGE_TEXT);

				setTimeout(() => {
					window.location = response.REFRESH_DATA.url.replace('&amp;', '&');
					alert.hide();
				}, response.REFRESH_DATA.time * 1000);

				phpbb.avatars.image.attr('src', canvasDataUrl);
				phpbb.avatars.destroy();
			} else {
				phpbb.alert(response.error.title, response.error.messages.join('<br>'));
			}
		},

		/**
		 * Bind a function to all the cropper <button> elements.
		 *
		 * Only buttons with a data-cropper-action attribute are recognized.
		 * The value for this data attribute should be a function available in the cropper.
		 * It also takes two optional parameters, imploded by a comma.
		 * For example: data-cropper-action="move,0,10" which results in $().cropper('move', 0, 10)
		 */
		initButtons() {
			this.$buttons.show().find('[data-cropper-action]').off('click.phpbb.avatars').on('click.phpbb.avatars', function() {
				const option = $(this).data('cropper-action').split(',');
				const action = option[0];

				if (typeof phpbb.avatars.cropper[action] === 'function') {
					// Special case: flip, set it to the opposite value (-1 and 1).
					if (action === 'scaleX' || action === 'scaleY') {
						phpbb.avatars.image.cropper(action, -phpbb.avatars.cropper.getData(true)[action]);
					} else {
						phpbb.avatars.image.cropper(action, option[1], option[2]);
					}
				}
			});
		},

		/**
		 * Initialise the cropper (CropperJS).
		 *
		 * @see https://github.com/fengyuanchen/cropperjs
		 *
		 * This creates a cropper instance with a 1 to 1 (square) aspect ratio,
		 * automatically creates the maximum available and allowed cropping area,
		 * and registers a callback function for the 'crop' event.
		 */
		initCropper() {
			this.cropper = this.image.cropper({
				aspectRatio: 1,
				autoCropArea: 1,
			}).data('cropper');

			this.image.off('crop.phpbb.avatars').on('crop.phpbb.avatars', phpbb.avatars.onCrop);
		},

		/**
		 * The callback function for the 'crop' event.
		 *
		 * This function ensures that the crop area is within the configured dimensions.
		 * Meaning the width and height can not exceed the limits set by an Administrator.
		 *
		 * It also JSON encodes the data array and places it into an <input> element,
		 * which will be requested server side, and crop the image accordingly.
		 * Image cropping is done server side, to ensure the best image quality
		 * and image blobs (from .toBlob()) can only be send through AJAX requests.
		 *
		 * @param {object} event
		 */
		onCrop(event) {
			const data = phpbb.avatars.$data.data();
			let { width, height } = event.detail;

			if (width < data.minWidth || height < data.minHeight) {
				width = Math.max(data.minWidth, Math.min(data.maxWidth, width));
				height = Math.max(data.minHeight, Math.min(data.maxHeight, height));
				phpbb.avatars.cropper.setData({
					width,
					height,
				});
			}
		},
	};

	$(() => {
		phpbb.avatars.init();
	});
})(jQuery); // Avoid conflicts with other libraries
