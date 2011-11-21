CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	
	
	config.skin = 'prosilver,' + phpbb.board_url + phpbb.theme_path + '/ckeditor/';
	config.resize_dir = 'vertical';
	//config.contentsCss = phpbb.board_url + phpbb.theme_path + '/stylesheet.css';
};