/**
 * Sets up the Rich Text Editor for posts
 */
(function($) {	
	$(function() {
		var editor = null,
			format_buttons = $('#format-buttons'),
			colour_palette = $('#colour_palette'),
			smilies_box = $('#smiley-box'),
			smiley_map = {};

		if (phpbb.smilies) {
			for(var i = 0; i < phpbb.smilies.code.length; i++) {
				smiley_map[phpbb.smilies.desc[i]] = phpbb.smilies.code[i];
			}
		}
		
		CKEDITOR.plugins.addExternal('phpbb', phpbb.board_url + 'assets/javascript/ckeditor-plugins/phpbb/');
		CKEDITOR.plugins.addExternal('phpbbattachment', phpbb.board_url + 'assets/javascript/ckeditor-plugins/phpbbattachment/');

		/**
		 * creates/shows the editor
		 */
		function createEditor() {
			editor = editor || (CKEDITOR.instances.message || CKEDITOR.instances.signature);
			
			if (editor) {
				return;
			}
			
			$('textarea#message,textarea#signature').ckeditor({
				// config file is in the theme so that it can set a skin and change other settings
				customConfig : phpbb.board_url + phpbb.theme_path + '/ckeditor-config.js',
				
				// setup ckeditor
				extraPlugins : 'phpbb,phpbbattachment',
				toolbar : [
					{
						name: 'basicstyles',
						items: ['Bold', 'Italic', 'Underline', 'RemoveFormat']
					},
					{
						name: 'paragraph',
						items: ['NumberedList', 'BulletedList', 'Blockquote']
					},
					{
						name: 'links',
						items: ['Link', 'Unlink', 'Anchor']
					},
					{
						name: 'insert',
						items: ['Image', 'Flash', 'Smiley']
					},
					{
						name: 'font',
						items: ['FontSize', 'TextColor' ]
					}
				],
				fontSize_defaultLabel : 'Normal',
				fontSize_sizes : 'Tiny/50%;Small/85%;Normal/100%;Large/150%;Huge/200%',

				smiley_descriptions : phpbb.smilies.desc,
				smiley_images : phpbb.smilies.img,
				smiley_path : phpbb.board_url + phpbb.smiley_path,
				smiley_map : smiley_map,
				bbcode : phpbb.bbcode || null
			});
		}
		
		/**
		 * removes/hides the editor
		 */
		function removeEditor() {
			editor = editor || (CKEDITOR.instances.message || CKEDITOR.instances.signature);
			
			if (!editor) {
				return;
			}
			
			editor.destroy();
			editor = null;
		}
		
		// if the format buttons are hidden then the user has the rich text editor on by default
		if (format_buttons.is(':hidden')) {
			createEditor();
		}
		
		// switches to RTE mode
		$('#mode-rte').click(function() {
			format_buttons.hide();
			colour_palette.hide();
			smilies_box.hide();
			
			createEditor();
			
			return false;
		});
		
		// switches to bbcode mode
		$('#mode-bbcode').click(function() {
			format_buttons.show();
			smilies_box.show();
			removeEditor();
			
			return false;
		});
	});
})(jQuery);