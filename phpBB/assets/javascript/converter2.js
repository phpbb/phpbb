/**
 * Created by bala on 19/6/17.
 */
/**
 * Created by bala on 19/6/17.
 */
(function($){
	var ajaxPoll;
	var progressBarTriggered = false;
	var progressTimer = null;
	var currentProgress = 0;


	/**
	 * Animates the progress bar
	 *
	 * @param $progressText
	 * @param $progressFiller
	 * @param $progressFillerText
	 * @param progressLimit
	 */
	function incrementFiller($progressText, $progressFiller, $progressFillerText, progressLimit) {
		if (currentProgress >= progressLimit || currentProgress >= 100) {
			clearInterval(progressTimer);
			return;
		}

		var $progressBar = $('#progress-bar');

		currentProgress++;
		$progressFillerText.css('width', $progressBar.width());
		$progressFillerText.text(currentProgress + '%');
		$progressText.text(currentProgress + '%');
		$progressFiller.css('width', currentProgress + '%');
	}

	/**
	 * Wrapper function for progress bar rendering and animating
	 *
	 * @param progressLimit
	 */
	function incrementProgressBar(progressLimit) {
		var $progressFiller = $('#progress-bar-filler');
		var $progressFillerText = $('#progress-bar-filler-text');
		var $progressText = $('#progress-bar-text');
		var progressStart = $progressFiller.width() / $progressFiller.offsetParent().width() * 100;
		currentProgress = Math.floor(progressStart);

		clearInterval(progressTimer);
		progressTimer = setInterval(function() {
			incrementFiller($progressText, $progressFiller, $progressFillerText, progressLimit);
		}, 10);
	}

	/**
	 * Renders progress bar
	 *
	 * @param progressObject
	 */
	function setProgress(progressObject) {
		var $statusText, $progressBar, $progressText, $progressFiller, $progressFillerText;

		if (progressObject.task_name.length) {
			if (!progressBarTriggered) {
				// Create progress bar
				var $progressBarWrapper = $('#progress-bar-container');

				// Create progress bar elements
				$progressBar = $('<div />');
				$progressBar.attr('id', 'progress-bar');
				$progressText = $('<p />');
				$progressText.attr('id', 'progress-bar-text');
				$progressFiller = $('<div />');
				$progressFiller.attr('id', 'progress-bar-filler');
				$progressFillerText = $('<p />');
				$progressFillerText.attr('id', 'progress-bar-filler-text');

				$statusText = $('<p />');
				$statusText.attr('id', 'progress-status-text');

				$progressFiller.append($progressFillerText);
				$progressBar.append($progressText);
				$progressBar.append($progressFiller);

				$progressBarWrapper.append($statusText);
				$progressBarWrapper.append($progressBar);

				$progressFillerText.css('width', $progressBar.width());

				progressBarTriggered = true;
			} else if (progressObject.hasOwnProperty('restart')) {
				clearInterval(progressTimer);

				$progressFiller = $('#progress-bar-filler');
				$progressFillerText = $('#progress-bar-filler-text');
				$progressText = $('#progress-bar-text');
				$statusText = $('#progress-status-text');

				$progressText.text('0%');
				$progressFillerText.text('0%');
				$progressFiller.css('width', '0%');

				currentProgress = 0;
			} else {
				$statusText = $('#progress-status-text');
			}

			// Update progress bar
			$statusText.text(progressObject.task_name + '…');
			incrementProgressBar(Math.round(progressObject.task_num / progressObject.task_count * 100));
		}
	}


	function setAjaxPoll() {

		ajaxPoll = setInterval(function(){
			poll();
		},1000);
	}

	function poll() {
		$.ajax({
			url: "/install/app.php/converter/start/status",
			dataType: "json"
		}).done(function(result){
			console.log(result.file);
			var html="Converting "+result.file+".....";
			$(".status").empty();
			$(".status").html(html);

			var pobj = {
				'task_name':result.file,
				'task_num': result.index+1,
				'task_count':result.total
			}
			setProgress(pobj);

		});
	}

	function clearAjaxPoll() {
		clearInterval(ajaxPoll);
		$(".status").empty();


	}

	function startConversion() {
		setAjaxPoll();
			$.ajax({
			url: "/install/app.php/converter/start/ajax",
			cache: false,
		}).done(function(result) {

			if (result == "reload") {
				clearAjaxPoll();
				startConversion();
				var pobj={"restart" : true};
				progressBarTriggered=false;
				setProgress(pobj);

			}
			else if(result == "end"){
				clearAjaxPoll();
				$(".status").empty();
				$(".status").html("Conversion completed successfully");
			}
		});
	}

	function setupAjaxLayout() {
		progressBarTriggered = false;
		var $progressContainer = $('<div />');
		$progressContainer.attr('id', 'progress-bar-container');
		$('.progress').append($progressContainer);

	}



	$(document).ready(function(){

		$("#btn").click(function(e) {
			e.preventDefault();
			$("#btn").hide();
			setupAjaxLayout();
			startConversion();


		});
	});

})(jQuery);