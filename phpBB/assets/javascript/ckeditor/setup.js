(function($) {
	$(function() {
		var editor = null,
			format_buttons = $('#format-buttons'),
			colour_palette = $('#colour_palette');
		
		function createEditor() {
			editor = editor || (CKEDITOR.instances.message || CKEDITOR.instances.signature);
			
			if (editor) {
				return;
			}
			
			$('textarea#message,textarea#signature').ckeditor();
		}
		
		function removeEditor() {
			editor = editor || (CKEDITOR.instances.message || CKEDITOR.instances.signature);
			
			if (!editor) {
				return;
			}
			
			editor.destroy();
			editor = null;
		}
		
		if (format_buttons.is(':hidden')) {
			createEditor();
		}
		
		$('#mode-wysiwyg').click(function() {
			format_buttons.hide();
			colour_palette.hide();
			
			createEditor();
			
			return false;
		});
		$('#mode-bbcode').click(function() {
			format_buttons.show();
			removeEditor();
			
			return false;
		});
	});
})(jQuery);