/* global phpbb */
/* import Tribute from './tribute.min'; */

(function($) {
	'use strict';

	/**
	 * Mentions data returned from ajax requests
	 * @typedef {Object} MentionsData
	 * @property {string} name User/group name
	 * @property {string} id User/group ID
	 * @property {{img: string, group: string}} avatar Avatar data
	 * @property {string} rank User rank or empty string for groups
	 * @property {number} priority Priority of data entry
	 */

	/**
	 * Mentions class
	 * @constructor
	 */
	function Mentions() {
		const $mentionDataContainer = $('[data-mention-url]:first');
		const mentionURL = $mentionDataContainer.data('mentionUrl');
		const mentionNamesLimit = $mentionDataContainer.data('mentionNamesLimit');
		const mentionTopicId = $mentionDataContainer.data('topicId');
		const mentionUserId = $mentionDataContainer.data('userId');
		const $mentionAvatarTemplate = $('[data-id="mentions-avatar-span"]');
		let queryInProgress = null;
		const cachedNames = [];
		const cachedAll = [];
		const cachedSearchKey = 'name';
		let tribute = null;

		/**
		 * Get default avatar
		 * @param {string} type Type of avatar; either 'g' for group or user on any other value
		 * @returns {string} Default avatar svg code
		 */
		function defaultAvatar(type) {
			if (type === 'g') {
				return '<svg class="mention-media-avatar" xmlns="http://www.w3.org/2000/svg" viewbox="0 0 24 24"><path fill-rule="evenodd" d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>';
			}

			return '<svg class="mention-media-avatar" xmlns="http://www.w3.org/2000/svg" viewbox="0 0 24 24"><path fill-rule="evenodd" d="M12,19.2C9.5,19.2 7.29,17.92 6,16C6.03,14 10,12.9 12,12.9C14,12.9 17.97,14 18,16C16.71,17.92 14.5,19.2 12,19.2M12,5A3,3 0 0,1 15,8A3,3 0 0,1 12,11A3,3 0 0,1 9,8A3,3 0 0,1 12,5M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12C22,6.47 17.5,2 12,2Z"/></svg>';
		}

		/**
		 * Get avatar HTML for data and type of avatar
		 *
		 * @param {object} data
		 * @param {string} type
		 * @return {string} Avatar HTML
		 */
		function getAvatar(data, type) {
			if (data.html === '' && data.src === '') {
				return defaultAvatar(type);
			}

			const $avatarSpan = $($mentionAvatarTemplate.html());

			if (data.html === '') {
				const $avatarImg = $avatarSpan.find('img');
				$avatarImg.attr({
					src: data.src,
					width: data.width,
					height: data.height,
					alt: data.title,
				});
			} else {
				$avatarSpan.html(data.html);
			}

			return $avatarSpan.get(0).outerHTML;
		}

		/**
		 * Get cached keyword for query string
		 * @param {string} query Query string
		 * @returns {?string} Cached keyword if one fits query, else empty string if cached keywords exist, null if cached keywords do not exist
		 */
		function getCachedKeyword(query) {
			if (!cachedNames) {
				return null;
			}

			let i;

			for (i = query.length; i > 0; i--) {
				const startStr = query.substr(0, i);
				if (cachedNames[startStr]) {
					return startStr;
				}
			}

			return '';
		}

		/**
		 * Get names matching query
		 * @param {string} query Query string
		 * @param {Object.<number, MentionsData>} items List of {@link MentionsData} items
		 * @param {string} searchKey Key to use for matching items
		 * @returns {Object.<number, MentionsData>} List of {@link MentionsData} items filtered with query and by searchKey
		 */
		function getMatchedNames(query, items, searchKey) {
			let i;
			let itemsLength;
			const matchedNames = [];
			for (i = 0, itemsLength = items.length; i < itemsLength; i++) {
				const item = items[i];
				if (isItemMatched(query, item, searchKey)) {
					matchedNames.push(item);
				}
			}

			return matchedNames;
		}

		/**
		 * Return whether item is matched by query
		 *
		 * @param {string} query Search query string
		 * @param {MentionsData} item Mentions data item
		 * @param {string }searchKey Key to use for matching items
		 * @return {boolean} True if items is matched, false otherwise
		 */
		function isItemMatched(query, item, searchKey) {
			return String(item[searchKey]).toLowerCase().indexOf(query.toLowerCase()) === 0;
		}

		/**
		 * Filter items by search query
		 *
		 * @param {string} query Search query string
		 * @param {Object.<number, MentionsData>} items List of {@link MentionsData} items
		 * @return {Object.<number, MentionsData>} List of {@link MentionsData} items filtered with query and by searchKey
		 */
		function itemFilter(query, items) {
			let i;
			let len;
			const highestPriorities = { u: 1, g: 1 };
			const _unsorted = { u: {}, g: {} };
			const _exactMatch = [];
			let _results = [];

			// Reduce the items array to the relevant ones
			items = getMatchedNames(query, items, 'name');

			// Group names by their types and calculate priorities
			for (i = 0, len = items.length; i < len; i++) {
				const item = items[i];

				// Check for unsupported type - in general, this should never happen
				if (!_unsorted[item.type]) {
					continue;
				}

				// Current user doesn't want to mention themselves with "@" in most cases -
				// do not waste list space with their own name
				if (item.type === 'u' && item.id === String(mentionUserId)) {
					continue;
				}

				// Exact matches should not be prioritised - they always come first
				if (item.name === query) {
					_exactMatch.push(items[i]);
					continue;
				}

				// If the item hasn't been added yet - add it
				if (!_unsorted[item.type][item.id]) {
					_unsorted[item.type][item.id] = item;
					continue;
				}

				// Priority is calculated as the sum of priorities from different sources
				_unsorted[item.type][item.id].priority += parseFloat(item.priority.toString());

				// Calculate the highest priority - we'll give it to group names
				highestPriorities[item.type] = Math.max(highestPriorities[item.type], _unsorted[item.type][item.id].priority);
			}

			// All types of names should come at the same level of importance,
			// otherwise they will be unlikely to be shown
			// That's why we normalize priorities and push names to a single results array
			$.each([ 'u', 'g' ], (key, type) => {
				if (_unsorted[type]) {
					$.each(_unsorted[type], (name, value) => {
						// Normalize priority
						value.priority /= highestPriorities[type];

						// Add item to all results
						_results.push(value);
					});
				}
			});

			// Sort names by priorities - higher values come first
			_results = _results.sort((a, b) => {
				return b.priority - a.priority;
			});

			// Exact match is the most important - should come above anything else
			$.each(_exactMatch, (name, value) => {
				_results.unshift(value);
			});

			return _results;
		}

		/**
		 * remoteFilter callback filter function
		 * @param {string} query Query string
		 * @param {function} callback Callback function for filtered items
		 */
		function remoteFilter(query, callback) {
			/*
			* Do not make a new request until the previous one for the same query is returned
			* This fixes duplicate server queries e.g. when arrow keys are pressed
			*/
			if (queryInProgress === query) {
				setTimeout(() => {
					remoteFilter(query, callback);
				}, 1000);
				return;
			}

			const cachedKeyword = getCachedKeyword(query);
			const cachedNamesForQuery = (cachedKeyword !== null) ? cachedNames[cachedKeyword] : null;

			/*
			* Use cached values when we can:
			* 1) There are some names in the cache relevant for the query
			*    (cache for the query with the same first characters contains some data)
			* 2) We have enough names to display OR
			*    all relevant names have been fetched from the server
			*/
			if (cachedNamesForQuery &&
				(getMatchedNames(query, cachedNamesForQuery, cachedSearchKey).length >= mentionNamesLimit ||
					cachedAll[cachedKeyword])) {
				callback(cachedNamesForQuery);
				return;
			}

			queryInProgress = query;

			const params = { keyword: query, topic_id: mentionTopicId, _referer: location.href };
			$.getJSON(mentionURL, params, data => {
				cachedNames[query] = data.names;
				cachedAll[query] = data.all;
				callback(data.names);
			}).always(() => {
				queryInProgress = null;
			});
		}

		/**
		 * Generate menu item HTML representation. Also ensures that mention-list
		 * class is set for unordered list in mention container
		 *
		 * @param {object} data Item data
		 * @returns {string} HTML representation of menu item
		 */
		function menuItemTemplate(data) {
			const itemData = data;
			const avatar = getAvatar(itemData.avatar, itemData.type);
			const rank = (itemData.rank) ? '<span class=\'mention-rank\'>' + itemData.rank + '</span>' : '';
			const $mentionContainer = $('.' + tribute.current.collection.containerClass);

			if (typeof $mentionContainer !== 'undefined' && $mentionContainer.children('ul').hasClass('mention-list') === false) {
				$mentionContainer.children('ul').addClass('mention-list');
			}

			return '<span class=\'mention-media\'>' + avatar + '</span><span class=\'mention-name\'>' + itemData.name + rank + '</span>';
		}

		this.isEnabled = function() {
			return $mentionDataContainer.length;
		};

		this.handle = function(textarea) {
			tribute = new Tribute({
				trigger: '@',
				allowSpaces: true,
				containerClass: 'mention-container',
				selectClass: 'is-active',
				itemClass: 'mention-item',
				menuItemTemplate,
				selectTemplate(item) {
					return '[mention=' + item.type + ':' + item.id + ']' + item.name + '[/mention]';
				},
				menuItemLimit: mentionNamesLimit,
				values(text, cb) {
					remoteFilter(text, users => cb(users));
				},
				lookup(element) {
					return Object.prototype.hasOwnProperty.call(element, 'name') ? element.name : '';
				},
			});

			tribute.search.filter = itemFilter;

			tribute.attach($(textarea));
		};
	}

	phpbb.mentions = new Mentions();

	$(document).ready(() => {
		const textarea = getEditorTextArea();

		if (typeof textarea === 'undefined') {
			return;
		}

		if (phpbb.mentions.isEnabled()) {
			phpbb.mentions.handle(textarea);
		}
	});
})(jQuery);
