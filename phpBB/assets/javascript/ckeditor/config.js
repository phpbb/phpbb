/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	
	config.extraPlugins = 'bbcode,buttonpre';
	config.toolbar = [
		{
			name: 'basicstyles',
			items: ['Bold', 'Italic', 'Underline', 'RemoveFormat']
		},
		{
			name: 'paragraph',
			items: ['NumberedList', 'BulletedList', 'Blockquote', 'buttonpre']
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
	];
	config.fontSize_defaultLabel = 'Normal';
	config.fontSize_sizes = 'Tiny/50%;Small/85%;Normal/100%;Large/150%;Huge/200%';
};
