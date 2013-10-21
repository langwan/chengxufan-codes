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
	var settings = null;
	var $input = null;
	var index = 0;
	var callbacks = [];

	var progressList = [];

	var onProgress = function(ev) {
		console.debug("progress");
		if (ev.lengthComputable) {
			var id = ev.target.upfile_id;
			var percent = Math.round(ev.loaded * 100 / ev.total);
			console.debug("percent=" + percent);
			console.debug("progress");
			if (settings.progress != null)
				settings.progress(id, percent);
		}
	}

	var onLoad = function(ev) {
		var id = ev.target.upload.upfile_id;
		console.debug(ev.target.responseText);
		json = eval("(" + ev.target.responseText + ")");
		console.debug("success");
		if (settings.success != null)
			settings.success(id, json);
	}

	var fetch = function(upfile) {
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

		this.each(function() {

			var $el = $(this);
			$(this).bind('change', function() {
				var fileList = $el.get(0).files;
				for (var i = 0; i < fileList.length; i++) {
					fetch(fileList[i]);
				}
			});

		});

		return this;
	}

})(jQuery);