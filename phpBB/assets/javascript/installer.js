/**
 * Installer's AJAX frontend handler
 */

(function($) { // Avoid conflicts with other libraries
    // Global variables
    var pollTimer = null;
    var nextReadPosition = 0;

    // Template related variables
    var $contentWrapper = $('.install-body').find('.main');

    // Intercept form submits
    intercept_form_submit($('#install_install'));

    function poll_content(xhReq) {
        var messages = xhReq.responseText;

        do {
            var unprocessed = messages.substring(nextReadPosition);
            var messageEndIndex = unprocessed.indexOf('}\n\n');

            if (messageEndIndex !== -1) {
                var endOfMessageIndex = messageEndIndex + 3; // 3 is the length of "}\n\n"
                var message = unprocessed.substring(0, endOfMessageIndex);
                parse_message(message);
                nextReadPosition += endOfMessageIndex;
            }
        } while (messageEndIndex !== -1);

        if (xhReq.readyState === 4) {
            $('#loading_indicator').css('display', 'none');
            reset_polling();
        }
    }

    function parse_message(messageJSON) {
        $('#loading_indicator').css('display', 'none');

        messageJSON = messageJSON.trim();
        var responseObject = JSON.parse(messageJSON);

        // Parse object
        if (responseObject.hasOwnProperty('errors')) {
            add_message('error', responseObject.errors)
        }

        if (responseObject.hasOwnProperty('warnings')) {
            add_message('warning', responseObject.warnings)
        }

        if (responseObject.hasOwnProperty('logs')) {
            add_message('log', responseObject.logs);
        }

        if (responseObject.hasOwnProperty('form')) {
            add_form(responseObject.form);
        }
    }

    function add_message(type, messages) {
        // Get message containers
        var errorContainer = $('#error-container');
        var warningContainer = $('#warning-container');
        var logContainer = $('#log-container');

        var title, description, msgElement, arraySize = messages.length;
        for (var i = 0; i < arraySize; i++) {
            msgElement = $('<div />');
            title = $(document.createElement('strong'));
            title.text(messages[i].title);
            msgElement.append(title);

            if (messages[i].hasOwnProperty('description')) {
                description = $(document.createElement('p'));
                description.text(messages[i].description);
                msgElement.append(description);
            }

            switch (type) {
                case 'error':
                    msgElement.addClass('errorbox');
                    errorContainer.append(msgElement);
                    break;
                case 'warning':
                    msgElement.addClass('warningbox');
                    warningContainer.append(msgElement);
                    break;
                case 'log':
                    msgElement.addClass('log');
                    logContainer.append(msgElement);
                    break;
            }
        }
    }

    function add_form(formHtml) {
        var formContainer = $('#content-container');
        formContainer.html(formHtml);
        var form = $('#install_install');
        intercept_form_submit(form);
    }

    function start_polling(xhReq) {
        reset_polling();
        pollTimer = setInterval(function () {
            poll_content(xhReq);
        }, 500);
    }

    function reset_polling() {
        clearInterval(pollTimer);
        nextReadPosition = 0;
    }

    function submit_form(form, submitBtn) {
        form.css('display', 'none');

        var xhReq = create_xhr_object();
        xhReq.open('POST', form.attr('action'), true);
        xhReq.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhReq.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhReq.send(get_form_fields(form, submitBtn));

        // Clear content
        setup_ajax_layout();
        $('#loading_indicator').css('display', 'block');

        start_polling(xhReq);
    }

    // Workaround for submit buttons
    function get_form_fields(form, submitBtn) {
        var formData = form.serialize();
        //var submitBtn = form.find(':submit');
        formData += ((formData.length) ? '&' : '') + encodeURIComponent(submitBtn.attr('name')) + '=';
        formData += encodeURIComponent(submitBtn.attr('value'));

        return formData;
    }

    function intercept_form_submit(form) {
        if (!form.length) {
            return;
        }

        form.find(':submit').bind('click', function (event) {
            event.preventDefault();
            submit_form(form, $(this));
        });

    }

    /**
     * jQuery cannot be used as the response is streamed, and
     * as of now, jQuery does not provide access to the response until
     * the connection is not closed.
     */
    function create_xhr_object() {
        var xhReq;

        if (window.XMLHttpRequest) {
            xhReq = new XMLHttpRequest();
        }
        else if (window.ActiveXObject) {
            xhReq = new ActiveXObject("Msxml2.XMLHTTP");
        }

        return xhReq;
    }

    function setup_ajax_layout() {
        // Clear content
        $contentWrapper.html('');

        var $header = $('<div />');
        $header.attr('id', 'header-container');
        $contentWrapper.append($header);

        var $description = $('<div />');
        $description.attr('id', 'description-container');
        $contentWrapper.append($description);

        var $errorContainer = $('<div />');
        $errorContainer.attr('id', 'error-container');
        $contentWrapper.append($errorContainer);

        var $warningContainer = $('<div />');
        $warningContainer.attr('id', 'warning-container');
        $contentWrapper.append($warningContainer);

        var $installerContentWrapper = $('<div />');
        $installerContentWrapper.attr('id', 'content-container');
        $contentWrapper.append($installerContentWrapper);

        var $logContainer = $('<div />');
        $logContainer.attr('id', 'log-container');
        $contentWrapper.append($logContainer);

        var $spinner = $('<div />');
        $spinner.attr('id', 'loading_indicator');
        $spinner.html('&nbsp;');
        $contentWrapper.append($spinner);
    }
})(jQuery); // Avoid conflicts with other libraries
