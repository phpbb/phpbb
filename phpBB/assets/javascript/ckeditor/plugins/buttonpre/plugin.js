
CKEDITOR.plugins.add( 'buttonpre',
{
	requires : [ 'format' ],

	init : function( editor )
	{
		var addButtonCommand = function( buttonName, buttonLabel, commandName, styleDefiniton )
		{
			var style = new CKEDITOR.style( styleDefiniton );
	
			editor.attachStyleStateChange( style, function( state )
			{
				!editor.readOnly && editor.getCommand( commandName ).setState( state );
			});
	
			editor.addCommand( commandName, new CKEDITOR.styleCommand( style ) );
	
			editor.ui.addButton( buttonName,
			{
				label : buttonLabel,
				command : commandName
			});
		};
		
		var config = editor.config,
			lang = editor.lang
		
		addButtonCommand( 'buttonpre', 'Code', 'pre', config.format_pre );
	}
});