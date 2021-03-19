(function($) {  // Avoid conflicts with other libraries

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
	$buttons: $('#avatar-cropper-buttons'),

	/** @type {jQuery} */
	$box: $('#avatar-box'),

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
	init: function() {
		// If the cropper library is not available
		if (!$.isFunction($.fn.cropper)) {
			return;
		}

		// Correctly position the cropper buttons
		this.$buttons.appendTo(this.$box);

		this.image = this.$box.children('img');

		this.bindInput();
		this.bindSelect();
	},

	/**
	 * Destroy (undo) any initialisation.
	 */
	destroy: function() {
		this.$buttons.find('[data-cropper-action]').off('click.phpbb.avatars');
		this.image.off('crop.phpbb.avatars');

		this.$data.val('');
		this.$buttons.hide();

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
	bindSelect: function() {
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
	bindInput: function() {
		this.$input.on('change', function() {
			const fileReader = new FileReader();

			if (this.files[0].type.match('image.*')) {
				fileReader.readAsDataURL(this.files[0]);

				fileReader.onload = function() {
					phpbb.avatars.image.cropper('destroy').attr('src', this.result).addClass('avatar');
					phpbb.avatars.initCropper();
					phpbb.avatars.initButtons();
				};
			} else {
				phpbb.avatars.destroy();
			}
		});
	},

	/**
	 * Bind a function to all the cropper <button> elements.
	 *
	 * Only buttons with a data-cropper-action attribute are recognized.
	 * The value for this data attribute should be a function available in the cropper.
	 * It also takes two optional parameters, imploded by a comma.
	 * For example: data-cropper-action="move,0,10" which results in $().cropper('move', 0, 10)
	 */
	initButtons: function() {
		this.$buttons.show().find('[data-cropper-action]').off('click.phpbb.avatars').on('click.phpbb.avatars', function() {
			const option = $(this).data('cropper-action').split(',');
			const action = option[0];

			if (typeof phpbb.avatars.cropper[action] === 'function') {
				// Special case: flip, set it to the opposite value (-1 and 1).
				if (action === 'scaleX' || action === 'scaleY') {
					phpbb.avatars.image.cropper(action, - phpbb.avatars.cropper.getData(true)[action]);
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
	initCropper: function() {
		this.cropper = this.image.cropper({
			aspectRatio: 1,
			autoCropArea: 1
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
	onCrop: function(event) {
		const data = phpbb.avatars.$data.data();
		const width = event.detail.width;
		const height = event.detail.height;

		if (width < data.minWidth || width > data.maxWidth ||
			height < data.minHeight || height > data.maxHeight
		) {
			phpbb.avatars.cropper.setData({
				width: Math.max(data.minWidth, Math.min(data.maxWidth, width)),
				height: Math.max(data.minHeight, Math.min(data.maxHeight, height)),
			});
		}

		phpbb.avatars.$data.val(JSON.stringify(phpbb.avatars.cropper.getData(true)));
	},
};

$(function() {
	phpbb.avatars.init();
});

})(jQuery); // Avoid conflicts with other libraries
