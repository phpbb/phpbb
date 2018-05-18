/*
* Icy Phoenix jQuery CMS
* Luca Libralato
* Vjacheslav Trushkin
*/

jQuery(document).ready(function()
{
	jQuery(".tiptip").tipTip();
	if(typeof(cmsEditorData) != 'undefined')
	{
		cmsEditor.init(cmsEditorData);
	}
	else if(typeof(cmsEditorSettings) != 'undefined')
	{
		cmsSettings.init(cmsEditorSettings);
	}
});

var cmsSettings = {
	data    : {},
	admin   : false,
	/*
		Initialize editor
	*/
	init: function(data)
	{
		if(!data.rows.length)
		{
			$('#add-block-top').get(0).submit();
			$('#add-block-bottom').get(0).submit();
			return;
		}
		$('#cms-editor-header').removeClass('header-notice').html('123');
		cmsSettings.data = data;
		cmsSettings.admin = typeof(data.blist) == 'boolean' ? false : true;
		var html = '<ul class="cms-blocks-list"></ul>';
		$('#cms-editor').html(html);
		// add all rows
		for(var i = 0; i < data.rows.length; i++)
		{
			cmsSettings.addItem(data.rows[i]);
		}
	},

	/*
		Add item
	*/
	addItem: function(data, odd)
	{
		var html = '<li class="cms-block-item" id="cms-block-' + data.bs_id + '">';
		// title
		// if you want to reduce the name length (use 5 chars extra just to have a more nice abbreviation): (data.name.length > 30 ? (data.name.substring(0, 25) + '...') : data.name)
		html += '<p class="block-title"><span class="block-title-value">' + data.name + '</span><span class="gensmall">' + '&nbsp;[' + data.bs_id + ']' + '</span><a href="' + cmsSettings.data.edit.replace('{ID}', data.bs_id) + '" class="edit-parent" title="' + cmsEditorLang.editBlockSettingsAlt + '"></a><a href="javascript:void(0);" class="edit-title" onclick="cmsSettings.toggleChangeTitle(' + data.bs_id + ', true); return false;" title="' + cmsEditorLang.tipTitle + '"></a><a href="' + cmsSettings.data.remove.replace('{ID}', data.bs_id) + '" class="edit-delete" title="' + cmsEditorLang.tipDelete + '"></a>';
		// rename form
		html += '<span class="block-title-edit" style="display: none;"><input id="block-title-' + data.bs_id + '" type="text" /><a href="javascript:void(0);" onclick="cmsSettings.changeTitle(' + data.bs_id + '); return false;">' + cmsEditorLang.buttonChange + '</a><a href="javascript:void(0);" onclick="cmsSettings.toggleChangeTitle(' + data.bs_id + ', false); return false;">' + cmsEditorLang.buttonCancel + '</a></span>';
		html += '</p>';
		// type
		// if you want to reduce the name length: (cmsSettings.blockType(data).length > 30 ? (cmsSettings.blockType(data).substring(0, 25) + '...') : cmsSettings.blockType(data))
		//html += '<p class="block-type"><span class="block-type-value">' + (cmsSettings.blockType(data).length > 30 ? (cmsSettings.blockType(data).substring(0, 25) + '...') : cmsSettings.blockType(data)) + '</span><a href="javascript:void(0);" onclick="cmsSettings.editSettings(' + data.bs_id + '); return false;" class="cms-button-small edit-settings">' + cmsEditorLang.editBlockSettings + '</a><a title="' + cmsEditorLang.tipChangeType + '" href="javascript:void(0);" onclick="cmsSettings.toggleChangeType(' + data.bs_id + ', true); return false;" class="edit-type"></a></p>';
		html += '<p class="block-type"><span class="block-type-value">' + cmsSettings.blockType(data) + '</span><a title="' + cmsEditorLang.editBlockSettings + '" href="javascript:void(0);" onclick="cmsSettings.editSettings(' + data.bs_id + '); return false;" class="edit-settings"></a><a title="' + cmsEditorLang.tipChangeType + '" href="javascript:void(0);" onclick="cmsSettings.toggleChangeType(' + data.bs_id + ', true); return false;" class="edit-type"></a></p>';
		// view permissions
		html += '<p class="block-view"><span class="block-view-value">' + cmsEditorLang.viewedBy + ': ';
		for(var i = 0; i < cmsSettings.data.view.length; i++)
		{
			if(data.view == i) html += cmsSettings.data.view[i];
		}
		html += '</span><a title="' + cmsEditorLang.tipChangeView + '" href="javascript:void(0);" onclick="cmsSettings.toggleChangeView(' + data.bs_id + ', true); return false;" class="edit-view"></a></p>';
		// groups
		html += '<p class="block-groups"><span>' + cmsEditorLang.userGroups + ': ';
		if(!data.groups.length)
		{
			html += cmsEditorLang.userGroupsAll;
		}
		else
		{
			var groups = data.groups.split(',');
			for(var i = 0; i < groups.length; i++)
			{
				if (i > 0)
				{
					html += ', ';
				}
				html += cmsSettings.data.groups[groups[i]];
			}
		}
		html += '</span><a title="' + cmsEditorLang.userGroupsEdit + '" href="javascript:void(0);" onclick="cmsSettings.toggleChangeGroups(' + data.bs_id + ', true); return false;" class="edit-groups"></a></p>';
		// pages where layout is used
		var used = '';
		if(typeof(cmsSettings.data.list[data.bs_id]) != 'undefined')
		{
			for(var i = 0; i < cmsSettings.data.list[data.bs_id].length; i++)
			{
				var row = cmsSettings.data.list[data.bs_id][i];
				used += '<li>';
				if (row.layout > 0)
				{
					used += cmsEditorLang.blockUsedLayout;
				}
				else if (row.special > 0)
				{
					used += cmsEditorLang.blockUsedLayoutSpecial;
				}
				else
				{
					used += cmsEditorLang.blockUsedLayoutGlobal;
				}
				used += ' <a href="' + row.url + '" title="' + cmsEditorLang.blockUsedClick + '">' + row.name + '</a>' + (i == (cmsSettings.data.list[data.bs_id].length - 1) ? '' : ', ') + '</li>';
			}
		}
		if(used.length)
		{
			html += '<ul class="used-list"><li class="note">' + cmsEditorLang.blockUsed + '</li>' + used + '</ul>';
		}
		else
		{
			html += '<ul class="used-list"><li class="note">' + cmsEditorLang.blockUsedNone + '</li></ul>';
		}
		html += '<div class="clear"></div></li>';
		$('#cms-editor ul.cms-blocks-list').append(html);
		var row = $('#cms-block-' + data.bs_id);
		row.data('cmsData', data);
		row.find('a').tipTip();
		if(data.blockfile == '')
		{
			row.find('.edit-settings').hide();
		}
		row.find('p.block-view select').data('key', data.bs_id).change(function() { cmsSettings.changedView($(this).data('key'), this); });
	},

	blockType: function(data)
	{
		if(data.blockfile == '')
		{
			return data['type'] == 1 ? 'BBCode' : 'HTML';
		}
		return typeof(cmsSettings.data.blist[data.blockfile]) == 'undefined' ? data.blockfile : cmsSettings.data.blist[data.blockfile];
	},

	/*
		Edit title
	*/
	toggleChangeTitle: function(id, show)
	{
		var row = $('#cms-block-' + id),
			data = row.data('cmsData');
		if(show)
		{
			// hide other blocks
			cmsSettings.toggleChangeType(id, false);
			cmsSettings.toggleChangeView(id, false);
			cmsSettings.toggleChangeGroups(id, false);
			// show block
			row.find('span.block-title-edit input').val(data.name);
			row.find('span.block-title-edit').show();
			row.find('span.block-title-value, a.edit-title').hide();
		}
		else
		{
			row.find('span.block-title-edit').hide();
			row.find('span.block-title-value, a.edit-title').show();
		}
	},

	/*
		Edit block type
	*/
	toggleChangeType: function(id, show)
	{
		var row = $('#cms-block-' + id),
			data = row.data('cmsData');
		if(!show)
		{
			row.find('div.block-change-type').hide();
			row.find('span.block-type-value').html(cmsSettings.blockType(data));
			row.find('a.edit-type').show();
			return;
		}
		// hide other blocks
		cmsSettings.toggleChangeTitle(id, false);
		cmsSettings.toggleChangeGroups(id, false);
		cmsSettings.toggleChangeView(id, false);
		// find block
		var block = row.find('div.block-change-type');
		if(!block.length)
		{
			var html = '<div class="block-change-type"><ul>';
			html += '<li class="row-0' + (data.blockfile == '' ? '' : ' checked') + '"><label><a href="javascript:void(0);" onclick="cmsSettings.selectType(' + id + ', 0); return false;">' + cmsEditorLang.selectTypeFile + ':</a><select>';
			for(var key in cmsSettings.data.blist)
			{
				html += '<option value="' + key + '"' + (data.blockfile == key ? ' selected="selected"' : '') + '>' + cmsSettings.data.blist[key] + '</option>';
			}
			html += '</select></li>';
			html += '<li class="edit-settings"' + (data.blockfile == '' ? ' style="display: none;"' : '') + '>' + cmsEditorLang.editBlockSettingsTip + '</li>';
			html += '<li class="row-1' + (data.blockfile == '' && data['type'] == 0 ? ' checked' : '') + '"><label><a href="javascript:void(0);" onclick="cmsSettings.selectType(' + id + ', 1); return false;">' + cmsEditorLang.selectTypeHTML + '</a></li>';
			html += '<li class="row-2' + (data.blockfile == '' && data['type'] == 1 ? ' checked' : '') + '"><label><a href="javascript:void(0);" onclick="cmsSettings.selectType(' + id + ', 2); return false;">' + cmsEditorLang.selectTypeBBCode + '</a></li>';
			html += '</ul>';
			html += '<div class="block-type-content"' + (data.blockfile == '' ? '' : ' style="display: none;"') + '><p>' + cmsEditorLang.blockContent + '</p><textarea></textarea><p class="buttons"><a href="javascript:void(0);" class="cms-button-small" onclick="cmsSettings.changeText(' + id + ', true); return false;">' + cmsEditorLang.buttonChange + '</a> <a href="javascript:void(0);" class="cms-button-small undo" onclick="cmsSettings.changeText(' + id + ', false); return false;">' + cmsEditorLang.buttonRestore + '</a></p></div>';
			html += '</div>';
			row.find('p.block-groups').after(html);
			row.find('textarea').val(data.content).bind('keyup change', function() { var block = $(this).parents('li.cms-block-item'), data = block.data('cmsData'); if(data.content != this.value) block.find('a.undo').show(); });
			row.find('select').change(function() { var block = $(this).parents('li.cms-block-item'), data = block.data('cmsData'); cmsSettings.selectTypeChanged(data.bs_id, this); });
			row.find('a.undo').hide();
		}
		else
		{
			block.show();
		}
		row.find('span.block-type-value').html(cmsEditorLang.selectType + ':');
		row.find('a.edit-type').hide();
	},

	/*
		Edit permissions
	*/
	toggleChangeView: function(id, show)
	{
		var row = $('#cms-block-' + id),
			data = row.data('cmsData');
		if(!show)
		{
			row.find('div.block-change-view').hide();
			//row.find('a.edit-view').show();
			return;
		}
		//row.find('a.edit-view').hide();
		// hide other blocks
		cmsSettings.toggleChangeTitle(id, false);
		cmsSettings.toggleChangeType(id, false);
		cmsSettings.toggleChangeGroups(id, false);
		// find block
		var block = row.find('div.block-change-view');
		if(!block.length)
		{
			var html = '<div class="block-change-view"><ul>';
			for(var i = 0; i < cmsSettings.data.view.length; i++)
			{
				html += '<li class="row-' + cmsSettings.data.view_id[i] + '"><a href="javascript:void(0);" onclick="cmsSettings.checkView(' + id + ', ' + cmsSettings.data.view_id[i] + '); return false;">' + cmsSettings.data.view[i] + '</a></li>';
			}
			html += '</ul>';
			html += '</div>';
			row.find('p.block-groups').after(html);
			block = row.find('div.block-change-view');
			block.find('li.row-' + data.view).addClass('checked');
			for(var i = 0; i < cmsSettings.data.view.length; i++)
			{
				row.find('li.row-' + cmsSettings.data.view_id[i]).data('key', cmsSettings.data.view_id[i]);
			}
		}
		else
		{
			block.show();
		}
	},

	/*
		Edit groups list
	*/
	toggleChangeGroups: function(id, show)
	{
		var row = $('#cms-block-' + id),
			data = row.data('cmsData');
		if(!show)
		{
			row.find('div.block-change-groups').hide();
			return;
		}
		// hide other blocks
		cmsSettings.toggleChangeTitle(id, false);
		cmsSettings.toggleChangeType(id, false);
		cmsSettings.toggleChangeView(id, false);
		// find block
		var block = row.find('div.block-change-groups');
		if(!block.length)
		{
			var html = '<div class="block-change-groups"><ul>';
			for(var key in cmsSettings.data.groups)
			{
				html += '<li class="row-' + key + '"><a href="javascript:void(0);" onclick="cmsSettings.checkGroup(' + id + ', ' + key + '); return false;">' + cmsSettings.data.groups[key] + '</a></li>';
			}
			html += '</ul>';
			html += '</div>';
			row.find('p.block-groups').after(html);
			block = row.find('div.block-change-groups');
			if(!data.groups.length)
			{
				block.find('li').addClass('checked');
			}
			else
			{
				var groups = data.groups.split(',');
				for(var i = 0; i < groups.length; i++)
				{
					block.find('li.row-' + groups[i]).addClass('checked');
				}
			}
			for(var key in cmsSettings.data.groups)
			{
				row.find('li.row-' + key).data('key', key);
			}
		}
		else
		{
			block.show();
		}
	},

	/*
		Change title
	*/
	changeTitle: function(id)
	{
		var row = $('#cms-block-' + id),
			data = row.data('cmsData'),
			value = row.find('p.block-title input').val();
		cmsSettings.toggleChangeTitle(id, false);
		if(data.name == value) return;
		data.name = value;
		row.find('span.block-title-value').text(value);
		cmsSettings.ajaxUpdate(id, {'name': value});
	},

	/*
		Change content type
	*/
	selectType: function(id, t)
	{
		var row = $('#cms-block-' + id),
			data = row.data('cmsData'),
			block = row.find('div.block-change-type');
		switch(t)
		{
			case 0:
				// change to file
				if(data.blockfile != '') return;
				var select = block.find('select').get(0);
				data.blockfile = select.options[select.selectedIndex].value;
				block.find('div.block-type-content').hide();
				row.find('.edit-settings').show();
				break;
			case 1:
			case 2:
				if(data.blockfile == '' && data['type'] == (t - 1)) return;
				data.blockfile = '';
				data['type'] = t - 1;
				block.find('div.block-type-content').show();
				block.find('textarea').val(data.content);
				block.find('a.undo').hide();
				row.find('.edit-settings').hide();
				break;
		}
		block.find('li').removeClass('checked');
		block.find('li.row-' + t).addClass('checked');
		cmsSettings.ajaxUpdate(id, {'blockfile': data.blockfile, 'type': data.type});
	},

	/*
		Change content
	*/
	changeText: function(id, change)
	{
		var row = $('#cms-block-' + id),
			data = row.data('cmsData'),
			block = row.find('div.block-change-type');
		if(change)
		{
			data.content = block.find('textarea').val();
		}
		else
		{
			block.find('textarea').val(data.content);
		}
		block.find('a.undo').hide();
		cmsSettings.ajaxUpdate(id, {'content': data.content});
	},

	/*
		Changed file selection
	*/
	selectTypeChanged: function(id, select)
	{
		var row = $('#cms-block-' + id),
			data = row.data('cmsData'),
			value = select.options[select.selectedIndex].value;
		if(value == data.blockfile) return;
		if(data.blockfile == '')
		{
			cmsSettings.selectType(id, 0);
		}
		else
		{
			data.blockfile = value;
			cmsSettings.ajaxUpdate(id, {'blockfile': data.blockfile});
		}
	},

	/*
		Changed groups selection
	*/
	checkGroup: function(id, key)
	{
		var row = $('#cms-block-' + id),
			data = row.data('cmsData'),
			block = row.find('div.block-change-groups'),
			checked = '',
			unchecked = '',
			text = '';
		block.find('li.row-' + key).toggleClass('checked');
		block.find('li').each(function()
		{
			var key = $(this).data('key');
			if($(this).hasClass('checked'))
			{
				checked += (checked.length ? ',' : '') + key;
				text += (text.length ? ', ' : '') + $('a', this).text();
			}
			else
			{
				unchecked += (unchecked.length ? ',' : '') + key;
			}
		});
		if(!checked.length)
		{
			// no groups: check them all
			block.find('li').addClass('checked');
			unchecked = '';
		}
		if(!unchecked.length)
		{
			data.groups = '';
			row.find('p.block-groups span').html(cmsEditorLang.userGroups + ': ' + cmsEditorLang.userGroupsAll);
		}
		else
		{
			data.groups = checked;
			row.find('p.block-groups span').html(cmsEditorLang.userGroups + ': ' + text);
		}
		cmsSettings.ajaxUpdate(id, {'groups': data.groups});
	},

	/*
		Edit block settings
	*/
	editSettings: function(id)
	{
		var row = $('#cms-block-' + id),
			data = row.data('cmsData'),
			html = '<form action="' + cmsSettings.data.edit.replace('{ID}', id) + '" method="post" id="settings-form-' + id + '">';
		html += '<input class="mainoption" type="hidden" value="Submit" name="hascontent" />';
		html += '<input type="hidden" value="block_settings" name="mode" />';
		html += '<input type="hidden" value="edit" name="action" />';
		html += '<input type="hidden" value="' + data.name + '" name="name" />';
		html += '<input type="hidden" value="' + data.blockfile + '" name="blockfile" />';
		html += '<input type="hidden" value="' + data.view + '" name="view" />';
		html += '<input type="hidden" value="' + data.type + '" name="type" />';
		var groups = data.groups.length ? data.groups.split(',') : false;
		for(var key in cmsSettings.data.groups)
		{
			var found = data.groups.length ? true : false;
			if(data.groups.length)
			{
				found = false;
				for(var i = 0; i < groups.length; i++)
				{
					if(groups[i] == key) found = true;
				}
			}
			html += '<input type="hidden" name="group' + key + '" value="' + (found ? 'checked' : '') + '" />';
		}
		html += '</form>';
		$('#cms-editor-footer').append(html);
		$('#settings-form-' + id).get(0).submit();
	},

	/*
		Changed view permissions
	*/
	checkView: function(id, value)
	{
		var row = $('#cms-block-' + id),
			data = row.data('cmsData'),
			block = row.find('div.block-change-view');
		if(data.view == value) return;
		data.view = value;
		block.find('li').removeClass('checked');
		block.find('li.row-' + value).addClass('checked');
		row.find('span.block-view-value').html(cmsEditorLang.viewedBy + ': ' + block.find('li.row-' + value + ' a').text());
		cmsSettings.ajaxUpdate(id, {'view': data.view});
	},

	/*
		Ajax stuff
	*/
	ajaxUpdate: function(id, params)
	{
		params['bs_id'] = id;
		params['json_action'] = 'update';
		params['json'] = true;
		params['temp'] = Math.random();
		for(var key in cmsSettings.data.post)
		{
			if(key != 'url') params[key] = cmsSettings.data.post[key];
		}
		// save changes
		cmsEditor.infoBox(cmsEditorLang.savingChanges, true);
		$.ajax({
			'url'       : cmsSettings.data.post.url,
			'type'      : 'POST',
			'data'      : params,
			'dataType'  : 'json',
			'timeout'   : '60000',
			'success'   : function(data) { cmsSettings.ajaxSuccess(data); },
			'error'     : function(XMLHttpRequest, textStatus, errorThrown) { cmsSettings.ajaxError(textStatus, errorThrown); }
		});
	},

	ajaxSuccess: function(data)
	{
		if(data === null)
		{
			cmsSettings.ajaxError('parsererror', '');
			return;
		}
		if(typeof(data) == 'string')
		{
			$('#cms-editor-header').text(data);
			return;
		}
		if(data.error)
		{
			cmsSettings.ajaxError('error', data.error);
			return;
		}
		if(data.reload)
		{
			// reload page
			var html = '<form id="reload-form" action="' + cmsSettings.data.post.url + '" method="post">';
			for(var key in cmsSettings.data.post)
			{
				if(key != 'url') html += '<input type="hidden" name="' + key + '" value="' + cmsSettings.data.post[key] + '" />';
			}
			html += '</form>';
			$('#cms-editor-footer').html(html);
			$('#reload-form').get(0).submit();
			return;
		}
		if(data.changed)
		{
			if($('#cms-editor-header').html() == cmsEditorLang.savingChanges)
			{
				cmsSettings.infoBox(cmsEditorLang.savingChangesDone, true);
				//setTimeout('cmsEditor.infoBox(false, false)', 2500);
				setTimeout(function() { cmsEditor.infoBox(false, false); }, 2500);
			}
			else
			{
				cmsSettings.infoBox(false, false);
			}
		}
	},

	infoBox: function(boxText, boxDisplay)
	{
		if (boxDisplay)
		{
			$('#cms-editor-header').addClass('header-notice').html(boxText).show();
			$('#cms-editor-header').html(boxText);

			if(boxText == cmsEditorLang.savingChanges)
			{
				//var info_box_begin = '<div id="result-box" class="text_red_cont"><span class="text_red">';
				//var info_box_end = '</span></div>';
				var info_box_begin = '<div id="result-box" class="rmbox rmb-red"><p class="rmb-center">';
				var info_box_end = '</p></div>';
				$('#sort-info-box').hide();
				$('#sort-info-box').html(info_box_begin + boxText + info_box_end);
				$('#sort-info-box').fadeIn(1000);
				setTimeout(function() { $('#result-box').effect('highlight', {}, 1000); }, 1000);
			}
			else
			{
				//var info_box_begin = '<div id="result-box" class="text_green_cont"><span class="text_green">';
				//var info_box_end = '</span></div>';
				var info_box_begin = '<div id="result-box" class="rmbox rmb-green"><p class="rmb-center">';
				var info_box_end = '</p></div>';
				$('#sort-info-box').html(info_box_begin + boxText + info_box_end);
			}
		}
		else
		{
			if($('#cms-editor-header').html() == cmsEditorLang.savingChangesDone)
			{
				$('#cms-editor-header').removeClass('header-notice').append(' ');
				$("#sort-info-box").fadeOut(1000);
				$('#sort-info-box').html('');
			}
		}
		if(!$('#sort-info-box').hasClass('initialized')) $('#sort-info-box').mouseover(function() { $(this).hide(); }).addClass('initialized');
	},

	ajaxError: function(textStatus, errorThrown)
	{
		var text = '';
		switch(status)
		{
			case 'timeout':
				text = cmsEditorLang.errorConnection;
				break;
			case 'parsererror':
				text = cmsEditorLang.errorParser;
				break;
			default:
				if(typeof(error) == 'string')
				{
					text = error;
					break;
				}
				text = cmsEditorLang.errorParser;
		}
		$('#cms-editor-header').addClass('header-notice').html(text);
		$('div.cms-block-extra p.status').html(cmsEditorLang.statusAddingError);
	}

};

var cmsEditor = {
	started : false,
	data    : {},
	blocks  : [],
	orderNum    : 0,
	/*
		Initialize editor
	*/
	init: function(data)
	{
		// rename duplicate parent blocks
		var parent = {}, title = '';
		for(var i = 0; i < data.all.length; i++)
		{
			title = data.all[i].name;
			data.all[i].originalName = title;
			if(typeof(parent[title]) == 'undefined')
			{
				parent[title] = 1;
			}
			else
			{
				parent[title] ++;
				// Mighty Gorgon: better avoid this and add the ID between square brackets!
				//data.all[i].name += ' (' + parent[title] + ')';
			}
		}
		// set new data
		cmsEditor.data = data;
		cmsEditor.blocks = [];
		// empty header/footer
		$('#cms-editor-header, #cms-editor-footer').html('');
		// empty blocks
		if(!cmsEditor.started)
		{
			$('#cms-editor-header').removeClass('header-notice').html('123');
			for(var key in data.pos)
			{
				var block = $('ul.cms-block-' + key);
				if(block.length)
				{
					if(block.length > 1)
					{
						// duplicates????
						block.not(':first').remove();
						block = $('ul.cms-block-' + key);
					}
					cmsEditor.blocks.push(key);
					block.html('').attr('id', 'cms-block-' + key).before('<div class="cms-block-header">' + data.postext[key] + '</div>');
					block.data('key', key).after('<div id="cms-block-' + key + '-extra" class="cms-block-extra"><p class="buttons"><a href="javascript:void(0);" onclick="cmsEditor.addForm(\'' + key + '\'); return false;" title="' + cmsEditorLang.tipAdd + '">' + cmsEditorLang.buttonAdd + '</a> <a class="move-all" href="javascript:void(0);" onclick="cmsEditor.moveAllForm(\'' + key + '\'); return false;" title="' + cmsEditorLang.tipMoveAll + '">' + cmsEditorLang.buttonMoveAll + '</a></p><p class="status" style="display: none;"></p></div>');
				}
			}
			$('ul.cms-editor-container').sortable({
				'connectWith'   : 'ul.cms-editor-container',
				'change'        : function(event, ui) { cmsEditor.sorted(); },
				'receive'       : function(event, ui) { cmsEditor.sorted(); },
				'over'          : function(event, ui) { $(this).addClass('sort-over'); },
				'out'           : function(event, ui) { $(this).removeClass('sort-over'); },
				'handle'        : 'a.edit-move' // 'p.block-title'
				});
			$('div.cms-block-extra a').tipTip();
		}
		else
		{
			for(var key in data.pos)
			{
				$('#cms-block-' + key).html('');
			}
		}
		// add rows
		var errors = [];
		for(var i = 0; i < data.rows.length; i++)
		{
			if(!cmsEditor.addBlock(data.rows[i]))
			{
				errors.push(i);
			}
		}
		if(errors.length)
		{
			// invalid block location, move it to first container
			var row = $('ul.cms-editor-container:first'),
				update = {'total': 0};
			if(row.length)
			{
				var key = row.data('key');
				for(var i = 0; i < errors.length; i++)
				{
					var item = data.rows[errors[i]];
					item.bposition = key;
					item.weight = row.find('li.cms-editor-block').length + 1;
					if(cmsEditor.addBlock(item))
					{
						update['p' + update.total + '_bid'] = item.bid;
						update['p' + update.total + '_bposition'] = key;
						update['p' + update.total + '_weight'] = item.weight;
						update.total ++;
					}
				}
				cmsEditor.ajaxRequest('update', update);
				$('#cms-editor-header').html(cmsEditorLang.errorWrongBlocksLocation);
			}
		}
		cmsEditor.cleanupAdded();
	},

	/*
		Add block
	*/
	addBlock: function(data)
	{
		var block = $('#cms-block-' + data.bposition);
		if (!block.length) return false;
		block.append(cmsEditor.blockHTML(data));
		var id = 'block-item-' + data.bid,
			row = $('#' + id);
		if(!row.length) return false;
		row.data('cmsData', $.extend(true, {}, data));
		cmsEditor.blockParentInfo(data.bid, data.bs_id);
		row.find('a:not(.edit-move)').tipTip();
		row.find('select').change(function() { cmsEditor.changedParent(this); });
		return true;
	},

	/*
		HTML code for block
	*/
	blockHTML: function(data)
	{
		var id = 'block-item-' + data.bid,
			html = '<li id="' + id + '" class="cms-editor-block">',
			keys = {
				'active': cmsEditorLang.optionEnabled,
				'border': cmsEditorLang.optionBorder,
				'titlebar': cmsEditorLang.optionTitle,
				'local': cmsEditorLang.optionLocal,
				'background': cmsEditorLang.optionBackground
			},
			keysExplain = {
				'active': cmsEditorLang.optionEnabledExplain,
				'border': cmsEditorLang.optionBorderExplain,
				'titlebar': cmsEditorLang.optionTitleExplain,
				'local': cmsEditorLang.optionLocalExplain,
				'background': cmsEditorLang.optionBackgroundExplain
			};
		html += '<p class="block-title"><span>' + data.title + '</span>';
		html += '<a class="edit-move" href="javascript:void(0);" title="' + cmsEditorLang.tipMove + '"></a>';
		html += '<a class="edit-title" href="javascript:void(0);" onclick="cmsEditor.toggleChangeTitle(' + data.bid + ', true); return false;" title="' + cmsEditorLang.tipTitle + '"></a>';
		html += '<a class="edit-delete" href="javascript:void(0);" onclick="if(confirm(cmsEditorLang.confirmDelete)) { cmsEditor.removeItem(' + data.bid + '); } return false;" title="' + cmsEditorLang.tipDelete + '"></a>';
		html += '</p>';
		html += '<p class="block-title-edit"><input type="text" id="title-' + data.bid + '" value="" /><a href="javascript:void(0);" onclick="cmsEditor.changeTitle(' + data.bid + '); return false;">' + cmsEditorLang.buttonChange + '</a><a href="javascript:void(0);" onclick="cmsEditor.toggleChangeTitle(' + data.bid + ', false); return false;">' + cmsEditorLang.buttonCancel + '</a></p>';
		html += '<p class="block-parent"><span>' + cmsEditorLang.parentBlock + '</span><select id="parent-' + data.bid + '"><option value="-1">' + cmsEditorLang.manageBlocks + '</option><option value="-1" disabled="disabled">----------</option>';
		var found = false;
		for(var i = 0; i < cmsEditor.data.all.length; i++)
		{
			html += '<option value="' + cmsEditor.data.all[i].bs_id + '"';
			if(cmsEditor.data.all[i].bs_id == data.bs_id)
			{
				found = true;
				html += ' selected="selected"';
			}
			html += '>' + (cmsEditor.data.all[i].name.length > 22 ? (cmsEditor.data.all[i].name.substring(0, 17) + '...') : cmsEditor.data.all[i].name) + ' [' + cmsEditor.data.all[i].bs_id + ']</option>';
		}
		if(!found)
		{
			html += '<option value="" selected="selected">' + cmsEditorLang.parentBlockNone + '</option>';
		}
		html += '</select></p>';
		html += '<div class="block-parent-info"></div>';
		html += '<ul class="block-settings">';
		for(var key in keys)
		{
			if(data[key] == '0') data[key] = false;
			html += '<li class="block-' + key + (data[key] ? ' checked' : '') + '"><a href="javascript:void(0);" onclick="cmsEditor.blockOption(\'' + key + '\', ' + data.bid + '); return false;" title="' + keysExplain[key] + '">' + keys[key] + '</a></li>';
		}
		html += '</ul>';
		html += '</li>';
		return html;
	},

	/*
		HTML code for parent block information
	*/
	blockParentInfo: function(bid, bs_id)
	{
		var block = $('#block-item-' + bid),
			row = block.find('div.block-parent-info'),
			html = '',
			item = false;
		// find block
		for(var i = 0; i < cmsEditor.data.blocks.length; i++)
		{
			if(cmsEditor.data.blocks[i].bs_id == bs_id)
			{
				item = cmsEditor.data.blocks[i];
				break;
			}
		}
		if(item === false)
		{
			row.html(cmsEditorLang.blockNotFound);
			return;
		}
		html += cmsEditorLang.blockType + ' ';
		if (item.blockfile.length)
		{
			html += cmsEditorLang.selectTypeFile + ': ' + item.blockfile;
		}
		else if (item['type'] == 1)
		{
			html += cmsEditorLang.selectTypeBBcode;
		}
		else
		{
			html += cmsEditorLang.selectTypeHTML;
		}
		// add button
		html += ' <a href="' + cmsEditor.data.edit.replace('{ID}', bs_id) + '" class="edit-parent" title="' + cmsEditorLang.editBlockSettingsAlt + '"></a>';
		row.html(html);
	},

	/*
		Edit title
	*/
	toggleChangeTitle: function(bid, show)
	{
		var block = $('#block-item-' + bid);
		if(show)
		{
			block.find('p.block-title-edit input').val(block.data('cmsData').title);
		}
		block.find('p.block-title-edit').css('display', show ? 'block' : 'none');
		block.find('p.block-title').css('display', show ? 'none' : 'block');
	},

	changeTitle: function(bid)
	{
		var block = $('#block-item-' + bid),
			value = block.find('p.block-title-edit input').val(),
			data = block.data('cmsData');
		if(value != data.title)
		{
			// change title
			data.title = value;
			block.find('p.block-title span').html(value);
			cmsEditor.ajaxRequest('update', {'total': 1, 'p0_bid': bid, 'p0_title': value});
		}
		cmsEditor.toggleChangeTitle(bid, false);
	},

	/*
		Change option
	*/
	blockOption: function(key, bid)
	{
		var block = $('#block-item-' + bid),
			data = block.data('cmsData'),
			checked = !data[key],
			params = {
				'total': 1,
				'p0_bid': bid
			};
		data[key] = !data[key];
		block.find('li.block-' + key).toggleClass('checked');
		params['p0_' + key] = checked ? '1' : '0';
		cmsEditor.ajaxRequest('update', params);
	},

	changedParent: function(select)
	{
		var value = select.options[select.selectedIndex].value,
			parent = $(select).parents('li.cms-editor-block:first'),
			data = parent.data('cmsData');
		if(value == -1)
		{
			document.location.href = cmsEditor.data.urls.blocks;
			return;
		}
		if(!value || data.bs_id == value) return;
		data.bs_id = value;
		cmsEditor.blockParentInfo(data.bid, data.bs_id);
		cmsEditor.ajaxRequest('update', {'total': 1, 'p0_bid': data.bid, 'p0_bs_id': value});
	},

	removeItem: function(bid)
	{
		$('#block-item-' + bid).replaceWith('<li id="remove-item-' + bid + '" class="cms-block-removed">' + cmsEditorLang.removedBlock + '</li>');
		//setTimeout('$(\'#remove-item-' + bid + '\').slideUp();', 1000);
		setTimeout(function() { $('#remove-item-' + bid).slideUp(); }, 1000);
		//setTimeout('$(\'#remove-item-' + bid + '\').remove();', 3000);
		setTimeout(function() { $('#remove-item-' + bid).remove(); }, 3000);
		cmsEditor.cleanupAdded();
		// send ajax request
		cmsEditor.ajaxRequest('delete', {'total': 1, 'p0_bid': bid});
	},

	sorted: function()
	{
		cmsEditor.orderNum ++;
		cmsEditor.cleanupAdded();
		// delay sorting by 3 seconds in case if user continues to move item to avoid flooding server
		//setTimeout('cmsEditor._sorted(' + cmsEditor.orderNum + ');', 3000);
		setTimeout(function() { cmsEditor._sorted(cmsEditor.orderNum); }, 3000);
	},

	_sorted: function(num)
	{
		if(cmsEditor.orderNum != num && num !== false) return;
		// get order of items
		var update = {},
			update_count = 0;
		for(var key in cmsEditor.data.pos)
		{
			$('#cms-block-' + key + ' li.cms-editor-block').each(function(i)
			{
				if(!this.id) return;
				var data = $(this).data('cmsData'),
					weight = i + 1;
				if((data.bposition != key) || (data.weight != weight))
				{
					// block moved
					update['p' + update_count + '_bid'] = data.bid;
					if(data.bposition != key) update['p' + update_count + '_bposition'] = key;
					if(data.weight != weight) update['p' + update_count + '_weight'] = weight;
					update_count ++;
					data.bposition = key;
					data.weight = weight;
				}
			});
		}
		update['total'] = update_count;
		if(update_count) cmsEditor.ajaxRequest('update', update);
	},

	/*
		Add block
	*/
	addForm: function(key)
	{
		var row = $('#cms-block-' + key + '-extra'),
			form = row.find('div.add-form');
		$('div.cms-block-extra div.add-form, div.cms-block-extra div.move-all-form').hide();
		$('div.cms-block-extra p.buttons').show();
		if(!form.length)
		{
			var keys = {
				'active': cmsEditorLang.optionEnabled,
				'border': cmsEditorLang.optionBorder,
				'titlebar': cmsEditorLang.optionTitle,
				'local': cmsEditorLang.optionLocal,
				'background': cmsEditorLang.optionBackground
			},
			html = '<div class="add-form">' +
			'<dl>' +
				'<dt>' + cmsEditorLang.blockTitle + ':</dt><dd><input type="text" class="post" id="add-form-' + key + '-title" /></dd>' +
				'<dt>' + cmsEditorLang.parentBlock + '</dt><dd><select id="add-form-' + key + '-parent">';
			for(var i = 0; i < cmsEditor.data.all.length; i++)
			{
				html += '<option value="' + cmsEditor.data.all[i].bs_id + '">' + (cmsEditor.data.all[i].name.length > 22 ? (cmsEditor.data.all[i].name.substring(0, 17) + '...') : cmsEditor.data.all[i].name) + ' [' + cmsEditor.data.all[i].bs_id + ']</option>';
			}
			html += '</select> <a href="' + cmsEditor.data.urls.blocks + '" class="cms-button-small">' + cmsEditorLang.manageBlocks + '</a></dd>' +
				'</dl>' +
				'<ul class="options" id="add-form-' + key + '-options">';
			for(var key2 in keys)
			{
				html += '<li class="block-' + key2 + '"><a href="javascript:void(0);" onclick="$(\'#add-form-' + key + '-options li.block-' + key2 + '\').toggleClass(\'checked\'); return false;">' + keys[key2] + '</a></li>';
			}
			html += '</ul>' +
				'<div class="buttons"><a href="javascript:void(0);" onclick="cmsEditor.addFormSubmit(\'' + key + '\'); return false;" title="' + cmsEditorLang.tipAdd + '">' + cmsEditorLang.buttonAdd + '</a> <a href="javascript:void(0);" onclick="cmsEditor.hideAddForm(\'' + key + '\'); return false;">' + cmsEditorLang.buttonCancel + '</a></div>' +
				'</div><div class="clear"></div>';
			row.append(html);
			row.find('div.buttons a').tipTip();
		}
		else
		{
			form.find('li').removeClass('checked');
			$('#add-form-' + key + '-title').val('');
			form.show();
		}
		row.find('p.buttons').hide();
		form.show();
	},

	hideAddForm: function(key)
	{
		var row = $('#cms-block-' + key + '-extra');
		row.find('div.add-form, div.move-all-form').hide();
		row.find('p.buttons').show();
	},

	addFormSubmit: function(key)
	{
		var row = $('#cms-block-' + key + '-extra'),
			parent = $('#add-form-' + key + '-parent').get(0),
			options = ['active', 'border', 'titlebar', 'local', 'background'],
			params = {
				'total'         : 1,
				'p0_bposition'  : key,
				'p0_bs_id'      : parent.options[parent.selectedIndex].value,
				'p0_title'      : $('#add-form-' + key + '-title').val(),
				'p0_weight'     : $('#cms-block-' + key + ' li.cms-editor-block').length + 1
			};
		for(var i = 0; i < options.length; i++)
		{
			params['p0_' + options[i]] = row.find('li.block-' + options[i]).hasClass('checked') ? 1 : 0;
		}
		if(!params.p0_title.length)
		{
			for(var i = 0; i < cmsEditor.data.all.length; i++)
			{
				if(cmsEditor.data.all[i].bs_id == params.p0_bs_id) params.p0_title = cmsEditor.data.all[i].originalName;
			}
		}
		// show status window
		row.find('p.status').css('display', 'block').html(cmsEditorLang.statusAdding);
		row.find('p.buttons, div.add-form, div.move-all-form').hide();
		// submit request
		cmsEditor.ajaxRequest('add', params);
	},

	cleanupAdded: function()
	{
		$('div.cms-block-extra div.add-form, div.cms-block-extra p.status, div.cms-block-extra div.move-all-form').hide();
		$('div.cms-block-extra p.buttons').show();
		for(var key in cmsEditor.data.pos)
		{
			var block = $('#cms-block-' + key);
			if(block.length) $('#cms-block-' + key + '-extra a.move-all').css('display', block.find('li.cms-editor-block').length > 0 ? '' : 'none');
		}
	},

	/*
		Move all blocks
	*/
	moveAllForm: function(key)
	{
		var row = $('#cms-block-' + key + '-extra'),
			form = $('#cms-block-' + key + '-extra-moveall');
		$('div.cms-block-extra div.add-form, div.cms-block-extra div.move-all-form').hide();
		$('div.cms-block-extra p.buttons').show();
		if(!form.length)
		{
			var html = '<div id="cms-block-' + key + '-extra-moveall" class="move-all-form">' + cmsEditorLang.moveAllSelect + ' <select>';
			for(var key2 in cmsEditor.data.pos)
			{
				if(key2 != key && $('#cms-block-' + key2).length)
				{
					html += '<option value="' + key2 + '">' + cmsEditor.data.postext[key2] + '</option>';
				}
			}
			html += '</select><div class="buttons"><a href="javascript:void(0);" onclick="cmsEditor.moveAllSubmit(\'' + key + '\'); return false;" title="' + cmsEditorLang.tipMoveAll + '">' + cmsEditorLang.buttonMoveAll + '</a> <a href="javascript:void(0);" onclick="cmsEditor.hideAddForm(\'' + key + '\'); return false;">' + cmsEditorLang.buttonCancel + '</a></div>';
			html += '</div><div class="clear"></div>';
			row.append(html);
			row.find('div.buttons a').tipTip();
		}
		else
		{
			form.show();
		}
		row.find('p.buttons').hide();
	},

	moveAllSubmit: function(key)
	{
		var select = $('#cms-block-' + key + '-extra-moveall select').get(0),
			newKey = select.options[select.selectedIndex].value;
		if(!newKey || newKey == key) return;
		// move all blocks
		$('#cms-block-' + key + ' li.cms-editor-block').appendTo($('#cms-block-' + newKey));
		// update
		cmsEditor.cleanupAdded();
		cmsEditor._sorted(cmsEditor.orderNum);
	},

	/*
		AJAX stuff
	*/
	ajaxRequest: function(action, params)
	{
		// generate request
		params['json_action'] = action;
		params['json'] = true;
		params['temp'] = Math.random();
		for(var key in cmsEditor.data.post)
		{
			if(key != 'url') params[key] = cmsEditor.data.post[key];
		}
		// save changes
		cmsEditor.infoBox(cmsEditorLang.savingChanges, true);
		$.ajax({
			'url'       : cmsEditor.data.post.url,
			'type'      : 'POST',
			'data'      : params,
			'dataType'  : 'json',
			'timeout'   : '60000',
			'success'   : function(data) { cmsEditor.ajaxSuccess(data); },
			'error'     : function(XMLHttpRequest, textStatus, errorThrown) { cmsEditor.ajaxError(textStatus, errorThrown); }
		});
	},

	ajaxSuccess: function(data)
	{
		if(data === null)
		{
			cmsEditor.ajaxError('parsererror', '');
			return;
		}
		if(typeof(data) == 'string')
		{
			$('#cms-editor-header').text(data);
			return;
		}
		if(data.error)
		{
			cmsEditor.ajaxError('error', data.error);
			return;
		}
		if(data.added)
		{
			// add new items
			for(var i = 0; i < data.items.length; i++)
			{
				cmsEditor.data.rows.push(data.items[i]);
				cmsEditor.addBlock(data.items[i]);
			}
			cmsEditor.cleanupAdded();
		}
		if(data.reload)
		{
			// reload page
			var html = '<form id="reload-form" action="' + cmsEditor.data.post.url + '" method="post">';
			for(var key in cmsEditor.data.post)
			{
				if(key != 'url') html += '<input type="hidden" name="' + key + '" value="' + cmsEditor.data.post[key] + '" />';
			}
			html += '</form>';
			$('#cms-editor-footer').html(html);
			$('#reload-form').get(0).submit();
			return;
		}
		if(data.changed || data.added)
		{
			cmsEditor.dataSaved();
		}
	},

	dataSaved: function()
	{
		if($('#cms-editor-header').html() == cmsEditorLang.savingChanges)
		{
			cmsEditor.infoBox(cmsEditorLang.savingChangesDone, true);
			//setTimeout('cmsEditor.infoBox(false, false)', 2500);
			setTimeout(function() { cmsEditor.infoBox(false, false); }, 2500);
		}
		else
		{
			cmsEditor.infoBox(false, false);
		}
	},

	infoBox: function(boxText, boxDisplay)
	{
		if (boxDisplay)
		{
			$('#cms-editor-header').addClass('header-notice').html(boxText).show();
			$('#cms-editor-header').html(boxText);

			if(boxText == cmsEditorLang.savingChanges)
			{
				//var info_box_begin = '<div id="result-box" class="text_red_cont"><span class="text_red">';
				//var info_box_end = '</span></div>';
				var info_box_begin = '<div id="result-box" class="rmbox rmb-red"><p class="rmb-center">';
				var info_box_end = '</p></div>';
				$('#sort-info-box').hide();
				$('#sort-info-box').html(info_box_begin + boxText + info_box_end);
				$('#sort-info-box').fadeIn(1000);
				setTimeout(function() { $('#result-box').effect('highlight', {}, 1000); }, 1000);
			}
			else
			{
				//var info_box_begin = '<div id="result-box" class="text_green_cont"><span class="text_green">';
				//var info_box_end = '</span></div>';
				var info_box_begin = '<div id="result-box" class="rmbox rmb-green"><p class="rmb-center">';
				var info_box_end = '</p></div>';
				$('#sort-info-box').html(info_box_begin + boxText + info_box_end);
			}
		}
		else
		{
			if($('#cms-editor-header').html() == cmsEditorLang.savingChangesDone)
			{
				$('#cms-editor-header').removeClass('header-notice').append(' ');
				$("#sort-info-box").fadeOut(1000);
				$('#sort-info-box').html('');
			}
		}
		if(!$('#sort-info-box').hasClass('initialized')) $('#sort-info-box').mouseover(function() { $(this).hide(); }).addClass('initialized');
	},

	ajaxError: function(textStatus, errorThrown)
	{
		var text = '';
		switch(status)
		{
			case 'timeout':
				text = cmsEditorLang.errorConnection;
				break;
			case 'parsererror':
				text = cmsEditorLang.errorParser;
				break;
			default:
				if(typeof(error) == 'string')
				{
					text = error;
					break;
				}
				text = cmsEditorLang.errorParser;
		}
		$('#cms-editor-header').addClass('header-notice').html(text);
		$('div.cms-block-extra p.status').html(cmsEditorLang.statusAddingError);
	}

};

