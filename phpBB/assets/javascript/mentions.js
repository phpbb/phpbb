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
				return $('[data-id="mention-default-avatar-group"]').html();
			}

			return $('[data-id="mention-default-avatar"]').html();
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

			if (data.html === '') {
				const $avatarImg = $($('[data-id="mention-media-avatar-img"]').html());
				$avatarImg.attr({
					src: data.src,
					width: data.width,
					height: data.height,
					alt: data.title,
				});
				return $avatarImg.get(0).outerHTML;
			}

			const $avatarImg = $(data.html);
			$avatarImg.addClass('mention-media-avatar');
			return $avatarImg.get(0).outerHTML;
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
				const startString = query.slice(0, i);
				if (cachedNames[startString]) {
					return startString;
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
			let itemsLength;
			const highestPriorities = { u: 1, g: 1 };
			const _unsorted = { u: {}, g: {} };
			const _exactMatch = [];
			let _results = [];

			// Reduce the items array to the relevant ones
			items = getMatchedNames(query, items, 'name');

			// Group names by their types and calculate priorities
			for (i = 0, itemsLength = items.length; i < itemsLength; i++) {
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
				_unsorted[item.type][item.id].priority += Number.parseFloat(item.priority.toString());

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
			_results = _results.sort((a, b) => b.priority - a.priority);

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
			const cachedNamesForQuery = cachedKeyword === null ? null : cachedNames[cachedKeyword];

			/*
			* Use cached values when we can:
			* 1) There are some names in the cache relevant for the query
			*    (cache for the query with the same first characters contains some data)
			* 2) We have enough names to display OR
			*    all relevant names have been fetched from the server
			*/
			if (cachedNamesForQuery
				&& (getMatchedNames(query, cachedNamesForQuery, cachedSearchKey).length >= mentionNamesLimit
					|| cachedAll[cachedKeyword])) {
				callback(cachedNamesForQuery);
				return;
			}

			queryInProgress = query;

			// eslint-disable-next-line camelcase
			const parameters = { keyword: query, topic_id: mentionTopicId, _referer: location.href };
			$.getJSON(mentionURL, parameters, data => {
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
			const rank = (itemData.rank) ? $($('[data-id="mention-rank-span"]').html()).text(itemData.rank).get(0).outerHTML : '';
			const $mentionContainer = $('.' + tribute.current.collection.containerClass);

			if (typeof $mentionContainer !== 'undefined' && $mentionContainer.children('ul').hasClass('mention-list') === false) {
				$mentionContainer.children('ul').addClass('mention-list');
			}

			const $avatarSpan = $($('[data-id="mention-media-span"]').html());
			$avatarSpan.html(avatar);

			const $nameSpan = $($('[data-id="mention-name-span"]').html());
			$nameSpan.html(itemData.name + rank);

			return $avatarSpan.get(0).outerHTML + $nameSpan.get(0).outerHTML;
		}

		this.isEnabled = function() {
			return $mentionDataContainer.length;
		};

		/* global Tribute */
		this.handle = function(textarea) {
			tribute = new Tribute({
				trigger: '@',
				allowSpaces: true,
				containerClass: 'mention-container',
				selectClass: 'is-active',
				itemClass: 'mention-item',
				menuItemTemplate,
				selectTemplate(item) {
					return '[mention ' + (item.type === 'g' ? 'group_id=' : 'user_id=') + item.id + ']' + item.name + '[/mention]';
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
		/* global form_name, text_name */
		const textarea = phpbb.getEditorTextArea(form_name, text_name);

		if (typeof textarea === 'undefined') {
			return;
		}

		if (phpbb.mentions.isEnabled()) {
			phpbb.mentions.handle(textarea);
		}
	});
})(jQuery);
