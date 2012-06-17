Function.prototype.curry = function () {
	if (arguments.length < 1) {
		return this;
	}
	
	var __method = this;
	var args = Array.prototype.slice.call(arguments);
	return function () {
		return __method.apply(this, args.concat(Array.prototype.slice.call(arguments)));
	}
}

plupload.addI18n(plupload.phpBB.i18n);
plupload.attachment_data = [];

function plupload_find_attachment_idx (id) {
	var data = plupload.attachment_data;
	for (var i = 0; i < data.length; i++) {
		if (data[i].id === id) {
			return i;
		}
	}

	return false;
}

function plupload_attachment_data_serialize () {
	var obj = {};
	for (var i = 0; i < plupload.attachment_data.length; i++) {
		var datum = plupload.attachment_data[i];
		for (var key in datum) {
			if (!datum.hasOwnProperty(key)) {
				continue;
			}

			obj['attachment_data[' + i + '][' + key + ']'] = datum[key];
		}
	}

	return obj;
}

function plupload_clear_params (obj) {
	for (var key in obj) {
		if (!obj.hasOwnProperty(key) || key.indexOf('attachment_data[') !== 0) {
			continue;
		}

		delete obj[key];
	}
}

$(function () {
	$(plupload.phpBB.config.element_hook).pluploadQueue(plupload.phpBB.config);
	var uploader = $(plupload.phpBB.config.element_hook).pluploadQueue();

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
		} else {
			plupload.attachment_data = json;
			file.attachment_data = json[0];
			up.settings.multipart_params = $.extend(
				up.settings.multipart_params,
				plupload_attachment_data_serialize()
			);
		}
	});

	uploader.bind('UploadComplete', function (up, files) {
		$('.plupload_upload_status').css('display', 'none');
		$('.plupload_buttons').css('display', 'block');

		files.forEach(function (file) {
			$('#' + file.id).mouseenter(function (evt) {
				$(evt.target).attr('class', 'plupload_delete');
				$(evt.target).css('cursor', 'pointer');
			}).mouseleave(function (evt) {
				$(evt.target).attr('class', 'plupload_done');
			}).click(function (file, evt) {
				var throbber = "url('" + plupload.phpBB.config.img_path + "/throbber.gif')";
				$(evt.target).find('a').css('background', throbber);
				
				var idx = plupload_find_attachment_idx(file.attachment_data.id);
				var fields = {};
				fields['delete_file[' + idx + ']'] = 1;
				
				$.ajax(plupload.phpBB.config.url, {
					type: 'POST',
					data: $.extend(fields, plupload_attachment_data_serialize()),
					headers: {'X-phpBB-Using-Plupload': '1'}
				}).always(function () {
					$(evt.target).find('a').css('background', '');
				}).done(function (response) {
					up.removeFile(file);
					plupload.attachment_data = response;
					plupload_clear_params(up.settings.multipart_params);
					up.settings.multipart_params = $.extend(
						up.settings.multipart_params,
						plupload_attachment_data_serialize()
					);
				});
			}.curry(file));
		});
	});
});
