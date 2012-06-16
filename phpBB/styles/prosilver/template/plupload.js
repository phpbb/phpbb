plupload.addI18n(plupload.phpBB.i18n);

$(function () {
	$('#attach-panel .inner').pluploadQueue(plupload.phpBB.config);
	var uploader = $('#attach-panel .inner').pluploadQueue();

	uploader.bind('ChunkUploaded', function (up, file, response) {
		if (response.chunk >= response.chunks - 1) {
			return;
		}

		var json = {};
		try {
			json = $.parseJSON(response.response);
		} catch (e) {}
		
		if (json.error) {
			file.status = plupload.FAILED;
			alert(json.error.message);
			uploader.trigger('FileUploaded', up, file);
		}
	});

	uploader.bind('FileUploaded', function (up, file, response) {
		var json = {};
		try {
			json = $.parseJSON(response.response);
		} catch (e) {}
		
		if (json.error) {
			file.status = plupload.FAILED;
			alert(json.error.message);
		}
	});
});
