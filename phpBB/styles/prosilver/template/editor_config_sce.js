{%- macro parse_case(EDITOR_JS_GLOBAL_OBJ, bbcodeName, parent, childData, forceValueKeep) -%}
	{%- import _self as exec -%}

	{%- for var, one in childData.caseVars %}
		xslt.setParameter('{{ var }}', attributes['{{ var }}']);
	{% endfor %}
	var conditionResult = xslt.transformToFragment(
		'<{{ bbcodeName }} d="{{ childData.num }}"></{{ bbcodeName }}>',
		document
	).firstChild.nodeValue;

	switch(conditionResult[0]){
	{% for caseVal, caseData in childData.case %}
		case '{{ caseVal }}':
			{{ exec.parse_node(EDITOR_JS_GLOBAL_OBJ, bbcodeName, parent, attribute(childData.case, caseVal).children, forceValueKeep) }}
		break;
	{% endfor %}
	}
{%- endmacro -%}

{%- macro parse_node(EDITOR_JS_GLOBAL_OBJ, bbcodeName, append_to, children, forceValueKeep) -%}
	{%- import _self as exec -%}

	{%- for child in children -%}
		{% if child.js.type == 'NODE_DEFINITION' %}

			var {{ child.js.nodeName }} = document.createElement("{{ child.tagName }}")
			{{ append_to }}.appendChild({{ child.js.nodeName }});
			{% for attrName, attrValue in child.js.attributes %}
				{{ child.js.nodeName }}.setAttribute("{{ attrName }}", {{ attrValue }});
				{% if attribute(child.js.bbcodeAttributes, attrName) is defined %}
					addBBCodeDataToElement(
						{{ child.js.nodeName }},
						"{{ attribute(child.js.bbcodeAttributes, attrName) }}",
						attributes["{{ attribute(child.js.bbcodeAttributes, attrName) }}"]
					);
				{% endif %}
				{% if child.js.parentEditable %}
					addBBCodeDataToElement(
						{{ child.js.nodeName }},
						"{{ child.js.varName }}",
						{% if forceValueKeep -%}
							{{ child.js.nodeName }}.textContent
						{%- else -%}
							editorConstants.VALUE_IN_CONTENT
						{%- endif %}
					);
					{{ child.js.nodeName }}.contentEditable = "true";

				{% endif -%}
			{% endfor %}

			{{ exec.parse_node(EDITOR_JS_GLOBAL_OBJ, bbcodeName, child.js.nodeName, child.children, forceValueKeep) }}
		{% elseif child.js.type == 'ATTRIBUTE_TEXT_NODE_DEFINITION' %}
			{% if child.vars[0].isAttribute %}
				var {{ child.js.nodeName }} = document.createTextNode(attributes["{{child.vars[0].name}}"]);
			{% else %}
				var {{ child.js.nodeName }} = document.createTextNode({{ EDITOR_JS_GLOBAL_OBJ }}.{{ attribute(child.vars[0], 'prefixedName') }});
			{% endif %}

			{{ append_to }}.appendChild({{ child.js.nodeName }});

			{% if child.js.parentEditable %}
				addBBCodeDataToElement(
					{{ append_to }},
					"{{ child.js.varName }}",
					{% if forceValueKeep -%}
						{{ child.js.nodeName }}.textContent
					{%- else -%}
						editorConstants.VALUE_IN_CONTENT
					{%- endif %}
				);
				{{ append_to }}.contentEditable = 'true';

			{% endif %}
			{{ exec.parse_node(EDITOR_JS_GLOBAL_OBJ, bbcodeName, child.js.nodeName, child.children, forceValueKeep) }}
		{% elseif child.js.type == 'ATTRIBUTES_FOR_ELEMENT_DEFINITION' %}
			var {{ child.js.nodeName }} = document.createElement('div');
			{{ exec.parse_node(EDITOR_JS_GLOBAL_OBJ, bbcodeName, child.js.nodeName, child.children, true) }}
			for (var i = 0; i < {{ child.js.nodeName }}.attributes.length; i++){
				{{ append_to }}.setAttribute(
						{{ child.js.nodeName }}.attributes[i].name,
						{{ child.js.nodeName }}.attributes[i].value
					);
			}
			{{ append_to }}.setAttribute("{{ child.js.attributeName }}", {{ child.js.nodeName }}.textContent);
			console.error({{ child.js.nodeName }}.textContent);
		{% elseif child.js.type == 'CONSTANT_TEXT_NODE_DEFINITION' %}
			var {{ child.js.nodeName }} = document.createTextNode("{{ child.js.nodeText }}");
			{{ append_to }}.appendChild({{ child.js.nodeName }});
		{% elseif child.js.type == 'PARSED_CHILDREN_SET' %}
			previousType = {{ append_to }}.getAttribute('data-bbcode-type');
						{{ append_to }}.setAttribute('data-bbcode-type',
							(previousType && previousType + '|content')|| 'content');
						{{ append_to }}.contentEditable = 'true';
						{{ append_to }}.innerHTML += content;

			{{ exec.parse_node(EDITOR_JS_GLOBAL_OBJ, bbcodeName, child.js.nodeName, child.children, forceValueKeep) }}
		{% elseif child.js.type == 'SWITCH_DEFINITION' %}
			{{ exec.parse_case(EDITOR_JS_GLOBAL_OBJ, bbcodeName, append_to, child, forceValueKeep) }}
		{% else %}
			ERROR: Got into else with type "{{ child.js.type }}".
		{% endif %}
	{%- endfor -%}
{%- endmacro -%}

{%- macro generate_dropdown(EDITOR_JS_GLOBAL_OBJ, bbcodeName, parent, childData) -%}
	{%- import _self as exec -%}

	{%- for var, one in childData.caseVars %}
		xslt.setParameter('{{ var }}', attributes['{{ var }}']);
	{% endfor %}
	var conditionResult = xslt.transformToFragment(
		'<{{ bbcodeName }} d="{{ childData.num }}"></{{ bbcodeName }}>',
		document
	).firstChild.nodeValue;

	switch(conditionResult[0]){
	{% for caseVal, caseData in childData.case %}
		case '{{ caseVal }}':
			{{ exec.parse_node(EDITOR_JS_GLOBAL_OBJ, bbcodeName, parent, attribute(childData.case, caseVal).children, false) }}
		break;
	{% endfor %}
	}
{%- endmacro -%}

{%- import _self as exec -%}




(function($, window, document, undefined) {  // Avoid conflicts with other libraries

	var addBBCodeDataToElement = function (element, bbcodeParamName, bbcodeParamValue){
		previousType = element.getAttribute('data-bbcode-type');
		attrData = JSON.parse(element.getAttribute('data-bbcode-data')) || [];

		attrData.push({
			name : bbcodeParamName,
			value: bbcodeParamValue
		});

		element.setAttribute('data-bbcode-type',
			(previousType && previousType + '|attr')|| 'attr');
		element.setAttribute('data-bbcode-data', JSON.stringify(attrData));
	}

	var xslt = editor.xslt('{{ XSLT }}');


	var makeDropdown = function(editor){

		var makeSelectBox = function (options, selectMultiple, separator, required){
			var select = document.createElement('select');
			select.multiple = !!selectMultiple;
			select.required = !!required;

			for (var i = 0; i < options.length; i++){
				var option = new Option(
						options[i].text,
						options[i].value,
						!!options[i].selected,
						!!options[i].selected
					);
				select.add(option);
			}
			var returner = {
				element: select
			};
			if(selectMultiple){
				returner.getValue = function (){
					return $(select).val();
				};
			}else{
				returner.getValue = function (){
					return ($(select).val() || []).join(separator);
				};
			}
			return returner;
		};
		var makeInput = function (type, isRequired, defaultValue){
			var input = document.createElement('input');
			input.type = type || 'text';
			input.required = !!isRequired;
			input.value = defaultValue || '';

			return {
				element: input,
				getValue: function (){
					return input.value;
				}
			};
		};

		return function (button, BBCodeName, attributes, oKCallback, errorCallback){
			var elements = [];
			var container = document.createElement('form');
			container.className = 'editorDropdownContainer';
			container.onsubmit = function (e){
				var data = {};
				for (var i = 0; i < elements.length; i++){
					data[attributes[i].name] = elements[i].getValue();
				}
				editor.closeDropDown(true);
				oKCallback(data);
				e.preventDefault();
			}

			for (var i = 0; i < attributes.length; i++){
				var attributeRequestContainer = document.createElement('div');

				var text = document.createElement('span');
				text.textContent = attributes[i].name;
				attributeRequestContainer.appendChild(text);

				var dataElement;

				switch(attributes[i].type){
					case 'chooseMany':
					case 'choose1':
						dataElement = makeSelectBox(attributes[i].options,
							attributes[i].type === 'chooseMany',
							attributes[i].separator,
							attributes[i].required)
					break;
					default:
						dataElement = makeInput(attributes[i].type,
							attributes[i].required, attributes[i].value);
					break;
				}

				elements.push(dataElement);
				attributeRequestContainer.appendChild(dataElement.element);
				container.appendChild(attributeRequestContainer);

			}

			var confirm = document.createElement('button');
			confirm.type = 'submit';
			confirm.textContent = {{ EDITOR_JS_GLOBAL_OBJ }}.L_SUBMIT;
			container.appendChild(confirm);

			editor.createDropDown(button, 'dropdown-' + BBCodeName, container);
		};
	};

{% set toolbar = '' %}

{% for bbcode in BBCODES %}

{% if bbcode.data.displayButton %}
	$.sceditor.command.set('{{ bbcode.name }}',
		{
			state: function (parent, blockParent){
				parent = $(this.currentNode());
				return (parent.attr('data-tag-id') === '{{ bbcode.tagId }}' ||
					parent.closest('[data-tag-id={{ bbcode.tagId }}]', blockParent)[0]) ? 1 : 0;
			},
			exec: function(button) {
				var parent = $(this.currentNode());
				// TODO: This needs more checking than just this
				if (parent.attr('data-tag-id') === '{{ bbcode.tagId }}' ||
					parent.closest('[data-tag-id={{ bbcode.tagId }}]')[0]){
						this.splitRemoveInSelection('[data-tag-id={{ bbcode.tagId }}]');
				}else{
					{% if bbcode.data.attr is empty %}
						this.insert('[{{ bbcode.name }}]'
						{%- if bbcode.data.autoCloseOn is empty and
								(not bbcode.data.autoClose or bbcode.data.allowedChildren is not empty) %}, '[/{{ bbcode.name }}]'{% endif -%}
						);
					{% else %}
						var editor = this;
						var attributes = [
						{% for attrName, attrData in bbcode.data.attr %}
							{% if bbcode.override.data.attr[attrName] is defined -%}
								{{ bbcode.override.data.attr[attrName] }}
							{% else %}
							{
								{% if attrData.type == 'choose1' or
									attrData.type == 'chooseMany' -%}
									'type': "{{ attrData.type }}",
									'options': [
									{% for option in attrData.options %}
										{
											text: {% if option.langText is defined -%}
													{{ EDITOR_JS_GLOBAL_OBJ }}.{{ option.langText }}
												{%- else -%}
													"{{ option.text }}"
												{%- endif %},
											value: "{{ option.value }}",
											selected: {{ option.selected ? 'true' : 'false' }},
										}
										{%- if not loop.last -%}
										,
										{% endif -%}
									{% endfor %}
									],
									'separator': {% if attrData.separator -%}
										"{{ attrData.separator }}",
									{% else -%}
										'',
									{% endif %}
								{% endif %}
								{% if attrData.required %}
									"required": true,
								{% endif %}
								"name": '{{ attrName }}'
							}
							{% endif %}
							{%- if not loop.last %}
							,
							{% endif %}
						{% endfor %}
						];
					makeDropdown(button, "{{ bbcode.name }}", attributes, function (data){
						var attrStr = '';
						for (var attribute in data){
							if (data[attribute].length > 0) {
								attrStr += ' ' + attribute + '="' + data[attribute] + '"';
							} else if (data[attribute].required) {
								attrStr += ' ' + attribute + '=""';
							}
						}
						editor.insert('[{{ bbcode.name }}' + attrStr + ']'
							{%- if bbcode.data.autoCloseOn is empty and
								(not bbcode.data.autoClose or bbcode.data.allowedChildren is not empty) %}, '[/{{ bbcode.name }}]'{% endif -%}
							);
					});
					{% endif %}
				}
			},
			txtExec: function() {
				this.insert('[{{ bbcode.name }}]'
					{%- if bbcode.data.autoCloseOn is empty and
							(not bbcode.data.autoClose or bbcode.data.allowedChildren is not empty) %}, '[/{{ bbcode.name }}]'{% endif -%}
				);
			},

			name: '{{ bbcode.name }}',
			{% if bbcode.data.tooltip_lang is defined %}
			tooltip: {{ EDITOR_JS_GLOBAL_OBJ }}['{{ bbcode.data.tooltip_lang }}']
			{% elseif bbcode.data.tooltip_text is defined %}
			tooltip: '{{ bbcode.data.tooltip_text|e('js') }}'
			{% endif %}
		}
	);
{% endif %}

	$.sceditor.plugins.bbcode.bbcode.set('{{ bbcode.name }}',
			{
				tags: {
				{% for containerTag in bbcode.containerTags %}
					'{{ containerTag }}': {
						'data-tag-id': ["{{ bbcode.tagId }}"]
					},
				{% endfor %}
				},
				// TODO: This needs improvement as it might simply not be true
				isInline:
					{%- if bbcode.override.data.isInline is defined -%}
						{{- bbcode.override.data.isInline ? 'true' : 'false' -}},
					{%- else -%}
						{%- for containerTag in bbcode.containerTags %}  editor.getElementDefaultDisplay('{{ containerTag }}') !== 'block' && {% endfor %}true,
					{%- endif %}

				{% if bbcode.data.isHtmlInline is defined -%}
					isHtmlInline: {{- bbcode.override.data.isHtmlInline ? 'true' : 'false' -}},
				{%- endif %}
				{% if bbcode.data.breakBefore is defined -%}
					breakBefore: {{- bbcode.override.data.breakBefore ? 'true' : 'false' -}},
				{%- endif %}
				{% if bbcode.data.breakStart is defined -%}
					breakStart: {{- bbcode.override.data.breakStart ? 'true' : 'false' -}},
				{%- endif %}
				{% if bbcode.data.breakEnd is defined -%}
					breakEnd: {{- bbcode.override.data.breakEnd ? 'true' : 'false' -}},
				{%- endif %}
				{% if bbcode.data.breakAfter is defined -%}
					breakAfter: {{- bbcode.override.data.breakAfter ? 'true' : 'false' -}},
				{%- endif %}

				{% if bbcode.data.autoClose and bbcode.data.allowedChildren is empty and
					bbcode.data.autoCloseOn is empty and
					bbcode.data.useContent is empty %}
					isSelfClosing: true,
					excludeClosing: true,
				{% endif %}

				{% if bbcode.data.autoClose is not empty %}
					excludeClosing: true,
					{% if bbcode.data.autoCloseOn is not empty %}
						closedBy: [
							{%- for autoCloseTag in bbcode.data.autoCloseOn -%}
								'{{ autoCloseTag }}',
							{%- endfor -%}
							{%- for autoCloseTag in bbcode.data.autoCloseOn -%}
							{%- if not loop.first -%}, {% endif -%}
								'/{{ autoCloseTag }}'
							{%- endfor -%}
							],
					{% elseif bbcode.data.deniedChildren is not empty %}
						closedBy: [
							{%- for deniedChild in bbcode.data.deniedChildren -%}
								'{{ deniedChild }}',
							{%- endfor -%}
							{%- for deniedChild in bbcode.data.deniedChildren -%}
							{%- if not loop.first -%}, {% endif -%}
								'/{{ deniedChild }}'
							{%- endfor -%}
							],
					{% endif %}
				{% endif %}

				{% if bbcode.data.ignoreBBCodeInside is not empty or
					bbcode.data.allowedChildren is empty %}
				allowedChildren: ['#'],
				{% else %}
				allowedChildren: ['
					{%- if not bbcode.data.ignoreTextInside -%}
						{{- "#','"-}}
					{%- endif -%}
					{{- bbcode.data.allowedChildren|join("','") -}}
					'],
				{% endif %}
				allowsEmpty: true,

				quoteType: $.sceditor.BBCodeParser.QuoteType.always,

				html: function (token, attributes, content) {
					var originalAttributes = attributes;
					var originalContent = content;
					var previousType;
					var attrData;
					var usedContents = [];
				{% if bbcode.data.trimWhitespace %}
					content = content.trim();
				{% endif %}
				{% if bbcode.data.defaultAttribute %}
					if(!attributes["{{ bbcode.data.defaultAttribute }}"] &&
						attributes["{{ bbcode.data.defaultAttribute }}"] !== '' && attributes.defaultattr){
						attributes["{{ bbcode.data.defaultAttribute }}"] = attributes.defaultattr;
					}
				{% endif %}
				{% for useContentAttr in bbcode.data.useContent %}
					if(!attributes["{{ useContentAttr }}"] &&
						attributes["{{ useContentAttr }}"] !== '' &&
						(content || content === '')
						){
						attributes["{{ useContentAttr }}"] = content;
						usedContents.push("{{ useContentAttr }}");
					}
				{% endfor %}
				{% for name, value in bbcode.data.attrPresets %}
					if(!attributes['{{ name }}'] && attributes['{{ name }}'] !== ''){
						attributes['{{ name }}'] = "{{ value }}";
					}
				{% endfor %}
				{% if bbcode.data.preProcessors is not empty %}
					var searchResult;
					{% for preProcessor in bbcode.data.preProcessors %}
						searchResult = /{{ preProcessor.regexFixed }}/{{ preProcessor.modifiersFixed }}.exec(attributes['{{ preProcessor.sourceAttribute }}']);
					if(searchResult){
					{% for num, attr in preProcessor.matchNumVsAttr %}
						if(!attributes["{{ attr }}"] && attributes["{{ attr }}"] !== ''){
							attributes['{{ attr }}'] = searchResult[{{ num }}];
						}
					{% endfor %}
					}
					{% endfor %}
				{% endif %}
				if (typeof attributes['{{ attrName }}'] !== "undefined") {
					{% for attrName, attrData in bbcode.data.attr %}
						{% for filter in attrData.filters %}
							{% if filter.name is defined %}
						attributes['{{ attrName }}'] =
							editor.paramFilters['{{ filter.name }}'](attributes['{{ attrName }}']{{ filter.extraVars }});
							{% else %}
						attributes['{{ attrName }}'] =
							({{ filter.inlineFunc }})(attributes['{{ attrName }}']{{ filter.extraVars }});
							{% endif %}
						if(attributes['{{ attrName }}'] === false){
							console.warn("Attribute {{ attrName }} from BBCode {{ bbcode.name }} failed to validate {% if filter.name is defined %}{{ filter.name }}{% else %}{{ filter.inlineFunc|e('js') }}{% endif %}");
						}
						{% endfor %}
						{% if attrData.defaultValue %}
						if(!attributes['{{ attrName }}'] && attributes['{{ attrName }}'] !== ''){
							attributes['{{ attrName }}'] = "{{ attrData.defaultValue }}";
						}
						{% endif %}
						{% if attrData.required %}
						if(!attributes['{{ attrName }}'] && attributes['{{ attrName }}'] !== ''){
							return editor.revertBackToBBCode("{{ bbcode.name }}", originalAttributes, originalContent);
						}
						{% endif %}
					{% endfor %}
				}
				var mainContainerFragment = document.createDocumentFragment();

				{{ exec.parse_node(EDITOR_JS_GLOBAL_OBJ, bbcode.name, 'mainContainerFragment', bbcode.parsedTemplate, false) }}

				if(mainContainerFragment.firstChild.getAttribute('contentEditable') !== 'true'){
					mainContainerFragment.firstChild.contentEditable = 'false';
				}
				mainContainerFragment.firstChild.setAttribute('data-tag-id', "{{ bbcode.tagId }}");
				return mainContainerFragment.firstChild.outerHTML;
			},
			format: function (element) {
				var infos = element[0].querySelectorAll('[data-bbcode-type]');
				var params = [];
				var content = '';
				if(element.is('[data-bbcode-type]')){
					infos = Array.prototype.slice.call(infos);
					infos.push(element[0]);
				}
				for(var i = 0; i < infos.length; i++){
					var current = infos[i];
					var type = current.getAttribute('data-bbcode-type');
					var data = current.getAttribute('data-bbcode-data');
					if(!type){
						console.error("To BBCode translation error at BBCode {{ bbcode.name }}.\n"
									+ 'Unexpected empty data-bbcode-type parameter. Value and node as follows:');
						console.error(type);
						console.error(current);
						return;
					}
					var types = type.split("|");
					var data = JSON.parse(data);
					var extraOffset = 0;
					for(var j = 0; j < types.length; j++){
						if(types[j] === 'content'){
							content = this.elementToBbcode($(current));
						{% if bbcode.data.trimWhitespace %}
							content = content.trim();
						{% endif %}
							extraOffset--;
						}else if(types[j] === 'attr'){
							var name = data[j + extraOffset].name;
							var value = data[j + extraOffset].value;
							if(value === editorConstants.VALUE_IN_CONTENT){
								value = current.textContent;
							}
							params.push(
								name + '="' + value + '"'
							);
						}else{
							console.warn("To BBCode translation warning at BBCode {{ bbcode.name }}.\n" +
										 "Unexpected value for data-bbcode-type parameter." +
										 "Skipping to the next value. Value and node were as follows:");
							console.warn(types[j]);
							console.warn(types);
							console.warn(current);
							continue;
						}
					}
				}

				return '[{{ bbcode.name }}' +
					(params ? ' ' : '') +
					params.join(' ') +
					']' + content {% if bbcode.data.autoCloseOn is empty and
							(not bbcode.data.autoClose or bbcode.data.allowedChildren is not empty) -%}
						+ '[/{{ bbcode.name }}]'
							{%- endif -%};
				}
			});


		{% set toolbar = toolbar ~ bbcode.name %}
		{% if loop.index0 is divisible by(4) %}
			{% set toolbar = toolbar ~ '|'  %}
		{% else %}
			{% set toolbar = toolbar ~ ',' %}
		{% endif %}

{% endfor %}


// Loadup and start SCE
var messageTextarea = $("#signature, #message");
messageTextarea.sceditor({
	plugins: 'bbcode,undo',
	style: {{ EDITOR_JS_GLOBAL_OBJ }}.stylePath,

{% if OVERRIDES.toolbar is defined %}
	toolbar: '{{ OVERRIDES.toolbar }}|removeformat|' +
				'cut,copy,paste,pastetext|' +
				'unlink|print,maximize,source',
{% else %}
	toolbar: '{{ toolbar }}|indent,outdent,removeformat|' +
				'cut,copy,paste,pastetext|' +
				'unlink|print,maximize,source',
{% endif %}
	startInSourceMode: !!({{ EDITOR_JS_GLOBAL_OBJ }}.S_EDITOR_MODE & {{ constant('\\phpbb\\bbcode\\convert_editor\\base::DEFAULT_SOURCE_MODE') }}),
	emoticons: {
		dropdown: {},
		more: {},
		hidden: {{ EDITOR_JS_GLOBAL_OBJ }}.emoticons
	}
});

var sceInstance = messageTextarea.sceditor("instance");

makeDropdown = makeDropdown(sceInstance);

var messageBox = document.getElementById('message-box');
if (messageBox){
	// For icon mode, it should use none of these classes
	if ({{ EDITOR_JS_GLOBAL_OBJ }}.S_EDITOR_BUTTONS_MODE  &
		{{ constant('\\phpbb\\bbcode\\convert_editor\\base::HAS_BUTTON_MODE_ICON_TEXT') }}) {
		messageBox.className += ' icon-text';
	} else if ({{ EDITOR_JS_GLOBAL_OBJ }}.S_EDITOR_BUTTONS_MODE  &
		{{ constant('\\phpbb\\bbcode\\convert_editor\\base::HAS_BUTTON_MODE_TEXT') }}) {
		messageBox.className += ' text';
	}
}

editor.insertHTML = function (editor, start, end){
	editor.insert(start, end, true, true, true);
}.bind(editor, sceInstance);

editor.insertBBCode = function (editor, start, end){
	editor.insert(start, end);
}.bind(editor, sceInstance);

editor.insertUnformatted = function (editor, start, end){
	editor.insertText(start, end);
}.bind(editor, sceInstance);

editor.getValue = function (){
	return editor.val();
}.bind(editor, sceInstance);

})(jQuery, window, document); // Avoid conflicts with other libraries