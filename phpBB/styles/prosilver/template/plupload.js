plupload.addI18n(plupload.phpBB.i18n);

$(function () {
	$('#attach-panel .inner').pluploadQueue(plupload.phpBB.config);
	var uploader = $('#attach-panel .inner').pluploadQueue();
	uploader.bind('ChunkUploaded', function (up, file, response) {
		var json = $.parseJSON(response.response);
		if (json.error) {
			file.status = plupload.FAILED;
			alert(json.error.message);
			uploader.trigger('FileUploaded', up, file);
		}
	});
});
