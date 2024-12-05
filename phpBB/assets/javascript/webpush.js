/* global phpbb */

'use strict';

function PhpbbWebpush() {
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

	/** @type {{endpoint: string, expiration: string}[]} Subscriptions */
	let subscriptions;

	/** @type {string} Title of error message */
	let ajaxErrorTitle = '';

	/** @type {string} VAPID public key */
	let vapidPublicKey = '';

	/** @type {HTMLElement} Subscribe button */
	let subscribeButton;

	/** @type {HTMLElement} Unsubscribe button */
	let unsubscribeButton;

	/**
	 * Init function for phpBB Web Push
	 * @type {array} options
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
				.then(() => {
					subscribeButton.addEventListener('click', subscribeButtonHandler);
					unsubscribeButton.addEventListener('click', unsubscribeButtonHandler);

					updateButtonState();
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
	 * Disable subscribing buttons, update subscribe button text and hide dropdown toggle
	 *
	 * @return void
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
	 * Update button state depending on notifications state
	 *
	 * @return void
	 */
	function updateButtonState() {
		if (Notification.permission === 'granted') {
			navigator.serviceWorker.getRegistration(serviceWorkerUrl)
				.then(registration => {
					if (typeof registration === 'undefined') {
						return;
					}

					registration.pushManager.getSubscription()
						.then(subscribed => {
							if (isValidSubscription(subscribed)) {
								setSubscriptionState(true);
							}
						});
				});
		}
	}

	/**
	 * Check whether subscription is valid
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
	 * Handler for pushing subscribe button
	 *
	 * @param {Object} event Subscribe button push event
	 * @returns {Promise<void>}
	 */
	async function subscribeButtonHandler(event) {
		event.preventDefault();

		subscribeButton.addEventListener('click', subscribeButtonHandler);

		// Prevent the user from clicking the subscribe button multiple times.
		const result = await Notification.requestPermission();
		if (result === 'denied') {
			phpbb.alert(subscribeButton.getAttribute('data-l-err'), subscribeButton.getAttribute('data-l-msg'));
			return;
		}

		const registration = await navigator.serviceWorker.getRegistration(serviceWorkerUrl);

		// We might already have a subscription that is unknown to this instance of phpBB.
		// Unsubscribe before trying to subscribe again.
		if (typeof registration !== 'undefined') {
			const subscribed = await registration.pushManager.getSubscription();
			if (subscribed) {
				await subscribed.unsubscribe();
			}
		}

		const newSubscription = await registration.pushManager.subscribe({
			userVisibleOnly: true,
			applicationServerKey: urlB64ToUint8Array(vapidPublicKey),
		});

		const loadingIndicator = phpbb.loadingIndicator();
		fetch(subscribeUrl, {
			method: 'POST',
			headers: {
				'X-Requested-With': 'XMLHttpRequest',
			},
			body: getFormData(newSubscription),
		})
			.then(response => {
				loadingIndicator.fadeOut(phpbb.alertTime);
				return response.json();
			})
			.then(handleSubscribe)
			.catch(error => {
				loadingIndicator.fadeOut(phpbb.alertTime);
				phpbb.alert(ajaxErrorTitle, error);
			});
	}

	/**
	 * Handler for pushing unsubscribe button
	 *
	 * @param {Object} event Unsubscribe button push event
	 * @returns {Promise<void>}
	 */
	async function unsubscribeButtonHandler(event) {
		event.preventDefault();

		const registration = await navigator.serviceWorker.getRegistration(serviceWorkerUrl);
		if (typeof registration === 'undefined') {
			return;
		}

		const subscription = await registration.pushManager.getSubscription();
		const loadingIndicator = phpbb.loadingIndicator();
		fetch(unsubscribeUrl, {
			method: 'POST',
			headers: {
				'X-Requested-With': 'XMLHttpRequest',
			},
			body: getFormData({ endpoint: subscription.endpoint }),
		})
			.then(() => {
				loadingIndicator.fadeOut(phpbb.alertTime);
				return subscription.unsubscribe();
			})
			.then(unsubscribed => {
				if (unsubscribed) {
					setSubscriptionState(false);
				}
			})
			.catch(error => {
				loadingIndicator.fadeOut(phpbb.alertTime);
				phpbb.alert(ajaxErrorTitle, error);
			});
	}

	/**
	 * Handle subscribe response
	 *
	 * @param {Object} response Response from subscription endpoint
	 */
	function handleSubscribe(response) {
		if (response.success) {
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
	 * @param {Object} formTokens
	 */
	function updateFormTokens(formTokens) {
		phpbb.webpush.formTokens.creationTime = formTokens.creation_time;
		phpbb.webpush.formTokens.formToken = formTokens.form_token;
	}

	/**
	 * Convert a base64 string to Uint8Array
	 *
	 * @param base64String
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
}

function domReady(callBack) {
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', callBack);
	} else {
		callBack();
	}
}

phpbb.webpush = new PhpbbWebpush();

domReady(() => {
	/* global phpbbWebpushOptions */
	phpbb.webpush.init(phpbbWebpushOptions);
});
