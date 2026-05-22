/* global phpbb */

function PhpbbWebpush() {
	'use strict';

	/** @type {string} URL to service worker */
	let serviceWorkerUrl = '';

	/** @type {string} URL to subscribe to push */
	let subscribeUrl = '';

	/** @type {string} URL to unsubscribe from push */
	let unsubscribeUrl = '';

	/** @type { {creationTime: number, formToken: string} } Form tokens */
	this.formTokens = {
		creationTime: 0,
		formToken: '',
	};

	/** @type {{endpoint: string, expirationTime: number}[]} Subscriptions */
	let subscriptions;

	/** @type {string} Title of an error message */
	let ajaxErrorTitle = '';

	/** @type {string} VAPID public key */
	let vapidPublicKey = '';

	/** @type {HTMLElement} Subscribe button */
	let subscribeButton;

	/** @type {HTMLElement} Unsubscribe button */
	let unsubscribeButton;

	/**
	 * Init function for phpBB Web Push
	 *
	 * @param {Object} options Init options
	 */
	this.init = function(options) {
		serviceWorkerUrl = options.serviceWorkerUrl;
		subscribeUrl = options.subscribeUrl;
		unsubscribeUrl = options.unsubscribeUrl;
		this.formTokens = options.formTokens;
		subscriptions = options.subscriptions;
		ajaxErrorTitle = options.ajaxErrorTitle;
		vapidPublicKey = options.vapidPublicKey;

		subscribeButton = document.querySelector('#subscribe_webpush');
		unsubscribeButton = document.querySelector('#unsubscribe_webpush');

		// Service workers are only supported in secure context
		if (window.isSecureContext !== true) {
			setDisabledState();
			return;
		}

		if ('serviceWorker' in navigator && 'PushManager' in window) {
			navigator.serviceWorker.register(serviceWorkerUrl)
				.then(async() => {
					subscribeButton.addEventListener('click', subscribeButtonHandler);
					unsubscribeButton.addEventListener('click', unsubscribeButtonHandler);

					await updateButtonState();
				})
				.catch(error => {
					console.info(error);
					// Service worker could not be registered
					setDisabledState();
				});
		} else {
			setDisabledState();
		}
	};

	/**
	 * If subscribing is disabled, hide the dropdown toggle and update the subscription button text
	 *
	 * @returns {void}
	 */
	function setDisabledState() {
		subscribeButton.disabled = true;

		const notificationList = document.getElementById('notification-menu');
		const subscribeToggle = notificationList.querySelector('.webpush-subscribe');

		if (subscribeToggle) {
			subscribeToggle.style.display = 'none';
		}

		if (subscribeButton.type === 'submit' || subscribeButton.classList.contains('button')) {
			subscribeButton.value = subscribeButton.getAttribute('data-disabled-msg');
		}
	}

	/**
	 * Update button state depending on the notification state
	 *
	 * @returns {Promise<void>}
	 */
	async function updateButtonState() {
		setSubscriptionState(false);

		if (Notification.permission !== 'granted') {
			return;
		}

		try {
			const registration = await navigator.serviceWorker.getRegistration(serviceWorkerUrl);
			if (typeof registration === 'undefined') {
				return;
			}

			const subscription = await registration.pushManager.getSubscription();
			if (!subscription) {
				return;
			}

			if (shouldRefreshSubscription(subscription)) {
				await refreshSubscription(registration, subscription);
				return;
			}

			setSubscriptionState(true);
		} catch (error) {
			console.error('Failed to update Web Push subscription state:', error);
		}
	}

	/**
	 * Check whether a subscription is valid
	 *
	 * @param {PushSubscription} subscription
	 * @returns {boolean}
	 */
	const isValidSubscription = subscription => {
		if (!subscription) {
			return false;
		}

		if (subscription.expirationTime && subscription.expirationTime <= Date.now()) {
			return false;
		}

		for (const curSubscription of subscriptions) {
			if (subscription.endpoint === curSubscription.endpoint) {
				return true;
			}
		}

		// Subscription is not in valid subscription list for user
		return false;
	};

	/**
	 * Check whether the current browser subscription uses the configured VAPID key
	 *
	 * @param {PushSubscription} subscription
	 * @returns {boolean}
	 */
	const hasCurrentVapidKey = subscription => {
		if (!subscription || !subscription.options || !subscription.options.applicationServerKey) {
			return true;
		}

		return uint8ArrayToUrlB64(new Uint8Array(subscription.options.applicationServerKey)) === vapidPublicKey;
	};

	/**
	 * Check whether a subscription should be recreated in the browser and backend
	 *
	 * @param {PushSubscription} subscription
	 * @returns {boolean}
	 */
	const shouldRefreshSubscription = subscription => !isValidSubscription(subscription) || !hasCurrentVapidKey(subscription);

	/**
	 * Remove a cached subscription entry
	 *
	 * @param {string} endpoint
	 */
	function removeStoredSubscription(endpoint) {
		if (!endpoint) {
			return;
		}

		subscriptions = subscriptions.filter(subscription => subscription.endpoint !== endpoint);
	}

	/**
	 * Update cached subscriptions with the newest server state
	 *
	 * @param {PushSubscription} subscription
	 * @param {string} previousEndpoint
	 */
	function storeSubscription(subscription, previousEndpoint = '') {
		removeStoredSubscription(previousEndpoint);
		removeStoredSubscription(subscription.endpoint);
		subscriptions.push({
			endpoint: subscription.endpoint,
			expirationTime: subscription.expirationTime || 0,
		});
	}

	/**
	 * Convert a PushSubscription to the payload expected by the backend
	 *
	 * @param {PushSubscription} subscription
	 * @param {string} previousEndpoint
	 * @returns {Object}
	 */
	function getSubscriptionPayload(subscription, previousEndpoint = '') {
		const payload = subscription.toJSON();
		if (previousEndpoint) {
			payload.previous_endpoint = previousEndpoint;
		}

		return payload;
	}

	/**
	 * Set subscription state for buttons
	 *
	 * @param {boolean} subscribed True if subscribed, false if not
	 */
	function setSubscriptionState(subscribed) {
		if (subscribed) {
			subscribeButton.classList.add('hidden');
			unsubscribeButton.classList.remove('hidden');
		} else {
			subscribeButton.classList.remove('hidden');
			unsubscribeButton.classList.add('hidden');
		}
	}

	/**
	 * Persist a browser subscription to the backend
	 *
	 * @param {PushSubscription} subscription
	 * @param {string} previousEndpoint
	 * @returns {Promise<Object>}
	 */
	async function persistSubscription(subscription, previousEndpoint = '') {
		const loadingIndicator = phpbb.loadingIndicator();

		try {
			const response = await fetch(subscribeUrl, {
				method: 'POST',
				headers: {
					'X-Requested-With': 'XMLHttpRequest',
				},
				body: getFormData(getSubscriptionPayload(subscription, previousEndpoint)),
			});
			const data = await response.json();

			if (!data.success) {
				throw new Error(data.message || subscribeButton.getAttribute('data-disabled-msg'));
			}

			handleSubscribe(data, subscription, previousEndpoint);
			return data;
		} finally {
			loadingIndicator.fadeOut(phpbb.alertTime);
		}
	}

	/**
	 * Create a fresh browser subscription and store it in the backend
	 *
	 * @param {ServiceWorkerRegistration} registration
	 * @param {PushSubscription|null} previousSubscription
	 * @returns {Promise<PushSubscription>}
	 */
	async function refreshSubscription(registration, previousSubscription = null) {
		const previousEndpoint = previousSubscription ? previousSubscription.endpoint : '';

		if (previousSubscription) {
			await previousSubscription.unsubscribe();
			removeStoredSubscription(previousEndpoint);
		}

		const newSubscription = await registration.pushManager.subscribe({
			userVisibleOnly: true,
			applicationServerKey: urlB64ToUint8Array(vapidPublicKey),
		});

		try {
			await persistSubscription(newSubscription, previousEndpoint);
			return newSubscription;
		} catch (error) {
			newSubscription.unsubscribe().catch(console.error);
			throw error;
		}
	}

	/**
	 * Handler for pushing subscribe button
	 *
	 * @param {Event} event Subscribe button push event
	 * @returns {Promise<void>}
	 */
	async function subscribeButtonHandler(event) {
		event.preventDefault();

		subscribeButton.removeEventListener('click', subscribeButtonHandler);

		try {
			// Prevent the user from clicking the subscribe button multiple times.
			const result = await Notification.requestPermission();
			if (result === 'denied') {
				phpbb.alert(subscribeButton.getAttribute('data-l-err'), subscribeButton.getAttribute('data-l-msg'));
				return;
			}

			const registration = await navigator.serviceWorker.getRegistration(serviceWorkerUrl);

			// We might already have a subscription that is unknown to this instance of phpBB.
			// Unsubscribe before trying to subscribe again.
			if (typeof registration === 'undefined') {
				throw new Error(subscribeButton.getAttribute('data-disabled-msg'));
			}

			try {
				const subscribed = await registration.pushManager.getSubscription();
				await refreshSubscription(registration, subscribed);
			} catch (error) {
				phpbb.alert(ajaxErrorTitle, error.message || subscribeButton.getAttribute('data-disabled-msg'));
			}
		} catch (error) {
			console.error('Push subscription error:', error);
			phpbb.alert(subscribeButton.getAttribute('data-l-err'), error.message || subscribeButton.getAttribute('data-disabled-msg'));
		} finally {
			subscribeButton.addEventListener('click', subscribeButtonHandler);
		}
	}

	/**
	 * Handler for pushing unsubscribe button
	 *
	 * @param {Event} event Unsubscribe button push event
	 * @returns {Promise<void>}
	 */
	async function unsubscribeButtonHandler(event) {
		event.preventDefault();

		const registration = await navigator.serviceWorker.getRegistration(serviceWorkerUrl);
		if (typeof registration === 'undefined') {
			return;
		}

		const subscription = await registration.pushManager.getSubscription();
		if (!subscription) {
			setSubscriptionState(false);
			return;
		}

		const loadingIndicator = phpbb.loadingIndicator();
		fetch(unsubscribeUrl, {
			method: 'POST',
			headers: {
				'X-Requested-With': 'XMLHttpRequest',
			},
			body: getFormData({ endpoint: subscription.endpoint }),
		})
			.then(async (response) => {
				let data = null;
				try {
					data = await response.json();
				} catch (e) {
					// Ignore JSON parsing failures and fall back below.
				}
				if (!response.ok || !data || !data.success) {
					throw new Error(data && data.message ? data.message : 'Unsubscribe failed.');
				}

				const unsubscribed = await subscription.unsubscribe();

				if (unsubscribed) {
					removeStoredSubscription(subscription.endpoint);
					setSubscriptionState(false);
				}
			})
			.catch(error => {
				phpbb.alert(ajaxErrorTitle, error.message || error);
			})
			.finally(() => {
				loadingIndicator.fadeOut(phpbb.alertTime);
			});
	}

	/**
	 * Handle subscribe response
	 *
	 * @param {{success: boolean, form_tokens?: Object}} response Response from subscription endpoint
	 * @param {PushSubscription} subscription Browser subscription
	 * @param {string} previousEndpoint Previous endpoint for refreshed subscriptions
	 */
	function handleSubscribe(response, subscription, previousEndpoint = '') {
		if (response.success) {
			storeSubscription(subscription, previousEndpoint);
			setSubscriptionState(true);
			if ('form_tokens' in response) {
				updateFormTokens(response.form_tokens);
			}
		}
	}

	/**
	 * Get form data object including form tokens
	 *
	 * @param {Object} data Data to create form data from
	 * @returns {FormData} Form data
	 */
	function getFormData(data) {
		const formData = new FormData();
		formData.append('form_token', phpbb.webpush.formTokens.formToken);
		formData.append('creation_time', phpbb.webpush.formTokens.creationTime.toString());
		formData.append('data', JSON.stringify(data));

		return formData;
	}

	/**
	 * Update form tokens with supplied ones
	 *
	 * @param {{creation_time: number, form_token: string}} formTokens
	 */
	function updateFormTokens(formTokens) {
		phpbb.webpush.formTokens.creationTime = formTokens.creation_time;
		phpbb.webpush.formTokens.formToken = formTokens.form_token;
	}

	/**
	 * Convert a base64 string to Uint8Array
	 *
	 * @param {string} base64String
	 * @returns {Uint8Array}
	 */
	function urlB64ToUint8Array(base64String) {
		const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
		const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
		const rawData = window.atob(base64);
		const outputArray = new Uint8Array(rawData.length);
		for (let i = 0; i < rawData.length; ++i) {
			outputArray[i] = rawData.charCodeAt(i);
		}

		return outputArray;
	}

	/**
	 * Convert a Uint8Array to a URL-safe base64 string
	 *
	 * @param {Uint8Array} value
	 * @returns {string}
	 */
	function uint8ArrayToUrlB64(value) {
		let stringValue = '';
		for (let i = 0; i < value.length; i++) {
			stringValue += String.fromCharCode(value[i]);
		}

		return window.btoa(stringValue)
			.replace(/\+/g, '-')
			.replace(/\//g, '_')
			.replace(/=+$/u, '');
	}
}

function domReady(callBack) {
	'use strict';

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', callBack);
	} else {
		callBack();
	}
}

phpbb.webpush = new PhpbbWebpush();

domReady(() => {
	'use strict';

	/* global phpbbWebpushOptions */
	phpbb.webpush.init(phpbbWebpushOptions);
});
