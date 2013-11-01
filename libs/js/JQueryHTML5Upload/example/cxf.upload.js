/**
 * cxf.upload.js
 * author: langwan<langwan@chengxufan.com>
 * use the HTML5 upload image, Support single or multiple files uplaod.
 *
 $('input[type=file]').upload({
	url: 'http://upload.chengxufan.com/upload',
	add: function(id) {
		$('#box').append('<li id="'+id+'"><img src="/default.png" /></li>')
	},
	success: function(id, json) {
		$('#box').find("li[id='+id+']").html('<img src="'+json.data+'" />');
	},
	progress: function(id, percent) {
		$('#box').find("li[id='+id+']").html('progress is %' + percent);
	}
 });
 *
 */
(function($) {
	var context = new Array();
	var gUploadId = 0;
	var settings = null;
	var index = 0;

	var onProgress = function(ev) {
		console.debug("progress");
		if (ev.lengthComputable) {
			var id = ev.target.upfile_id;
			var uploadId = ev.target.upload_id;
			settings = context[uploadId]['settings'];			
			var percent = Math.round(ev.loaded * 100 / ev.total);
			console.debug("percent=" + percent);
			console.debug("progress");
			if (settings.progress != null)
				settings.progress(id, percent);
		}
	}

	var onLoad = function(ev) {
		var id = ev.target.upload.upfile_id;
		var uploadId = ev.target.upload.upload_id;
		settings = context[uploadId]['settings'];
		console.debug(ev.target.responseText);
		json = eval("(" + ev.target.responseText + ")");
		console.debug("success");
		if (settings.success != null)
			settings.success(id, json);
	}

	var fetch = function(uploadId, upfile) {
		settings = context[uploadId]['settings'];
		console.debug("fetch.settings=" + settings.url);
		console.debug("fetch");
		console.debug("add(" + index + ")");
		if (settings.add != null)
			settings.add(index);
		var fd = new FormData();
		fd.append(settings.fieldName, upfile);
		var xhr = new XMLHttpRequest();
		xhr.addEventListener("load", onLoad, false);
		xhr.upload.addEventListener("progress", onProgress, false);
		xhr.upload.upfile_id = index;
		xhr.upload.upload_id = uploadId;
		xhr.open("POST", settings.url, true);
		xhr.send(fd);
		index++;
	}

	$.fn.upload = function(options) {
		
		settings = jQuery.extend({
			url: null,
			add: null,
			success: null,
			fieldName: 'upload_file',
			progress: null,
		}, options);
		context[gUploadId] = new Array();
		context[gUploadId]['settings'] = settings;
		$(this).attr('g-upload-id', gUploadId);
		gUploadId++;
		this.each(function() {
			var $el = $(this);
			$(this).bind('change', function() {
				var fileList = $el.get(0).files;
				for (var i = 0; i < fileList.length; i++) {
					var uploadId = $(this).attr('g-upload-id');
					fetch(uploadId, fileList[i]);
				}
			});

		});

		return this;
	}

})(jQuery);