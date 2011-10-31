CKEDITOR.plugins.add( 'phpbbattachment',
{
	init : function( editor )
	{
		var lang = editor.lang.div;
		
		editor.addCss(
			'.cke_attachment' +
			'{' +
				'background-image: url(' + CKEDITOR.getUrl( this.path + 'images/placeholder.png' ) + ');' +
				'background-position: center center;' +
				'background-repeat: no-repeat;' +
				'border: 1px solid #a9a9a9;' +
				'width: auto;' +
				'height: 80px;' +
				'line-height: 80px;' +
				'padding: 5px;' +
			'}'
		);
		
		editor.addCommand( 'removeattachment',
		{
			exec : function( editor )
			{
				var selection = editor.getSelection(),
					ranges = selection && selection.getRanges(),
					range,
					bookmarks = selection.createBookmarks(),
					walker,
					toRemove = [];

				function findDiv( node )
				{
					var path = new CKEDITOR.dom.elementPath( node ),
						blockLimit = path.blockLimit,
						div = blockLimit.is( 'div' ) && blockLimit;

					if ( div && div.data( 'cke-saved-attachment') && !div.data( 'cke-div-added' ) )
					{
						toRemove.push( div );
						div.data( 'cke-div-added' );
					}
				}

				for ( var i = 0 ; i < ranges.length ; i++ )
				{
					range = ranges[ i ];
					if ( range.collapsed )
						findDiv( selection.getStartElement() );
					else
					{
						walker = new CKEDITOR.dom.walker( range );
						walker.evaluator = findDiv;
						walker.lastForward();
					}
				}

				for ( i = 0 ; i < toRemove.length ; i++ )
					toRemove[ i ].remove( false );

				selection.selectBookmarks( bookmarks );
			}
		});
		
		if ( editor.addMenuItems )
		{
			editor.addMenuItems(
			{
				removeattachment:
				{
					label : 'Remove Attachment',
					command : 'removeattachment',
					group : 'div',
					order : 5
				}
			} );
		
			// If the "contextmenu" plugin is loaded, register the listeners.
			if ( editor.contextMenu )
			{
				editor.contextMenu.addListener( function( element, selection )
				{
					var parent = element.getParent();
					if ( !element || !element.is( 'div' ) || !parent || !parent.is( 'div' ) || !parent.data( 'cke-saved-attachment' ) )
					{				
						return null;
					}
					
					return { removeattachment : CKEDITOR.TRISTATE_OFF };
				});
			}
		}
	}
} );