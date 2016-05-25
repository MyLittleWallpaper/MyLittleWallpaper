/* <![CDATA[ */
$(document).ready(function(){

});

function change_category(el, pageURI) {
	if ($(el).val() != '0') {
		window.location.href = '/c/' + $(el).val() + '/' + pageURI;
	} else {
		window.location.href = '/c/all/' + pageURI;
	}
}

/**
 * @param {string} el
 * @param id
 * @param image
 * @param download
 * @returns {boolean}
 */
function image_preview(el, id, image, download) {
	$('body').append('<div id="' + id + '" class="image_loading_animation" style="left:' + $(el).children('img').offset().left + 'px;top:' + $(el).children('img').offset().top + 'px;width:' + $(el).children('img').width() + 'px;height:' + $(el).children('img').height() + 'px;"></div>');
	$.ajax({
		url: image,
		processData: false,
		success: function() {
			$('#' + id).remove();
			var title = $(el).children('img').attr('title');
			vex.dialog.open({
				message: '<div style="text-align:center;"><img src="' + image + '" alt="' + title + '" /><div class="image_preview_title">' + title + '</div></div>',
				className: 'vex-theme-flat-attack',
				buttons: [
					{
						text: 'Close',
						type: 'link',
						href: '#close',
						className: 'vex-dialog-button-secondary',
						click: function($vexContent, event) {
							$vexContent.data().vex.value = false;
							return vex.close($vexContent.data().vex.id);
						}
					},
					{
						text: 'Download',
						type: 'link',
						href: download,
						className: 'vex-dialog-button-secondary'
					}
				]
			});
		}
	});
	return false;
}

function open_taglist(url) {
	$.ajax({
		url: url,
		success: function(data) {
			vex.dialog.open({
				message: '<div class="taglist-container">' + data + '</div>',
				className: 'vex-theme-flat-attack taglist-dialog',
				buttons: [
					$.extend({}, vex.dialog.buttons.NO, {
						text: 'Close'
					})
				]
			});
		}
	});
	return false;
}

function download_image_enlarge(el, width) {
	if ($(el).css('width') == width + 'px') {
		$(el).css('width', '');
		$(el).css('max-width', '');
		$('html').css('min-width', '');
		if ($(el).hasClass('resizable')) {
			$(el).removeClass('resizableZoomOut');
			$(el).addClass('resizable resizableZoomIn');
		}
	} else if ($(el).width() < width) {
		$(el).css('width', width + 'px');
		$(el).css('max-width', 'none');
		$('html').css('min-width', (width + 270) + 'px');
		if ($(el).hasClass('resizable')) {
			$(el).removeClass('resizableZoomIn');
			$(el).addClass('resizable resizableZoomOut');
		}
	}
}

function split( val ) {
	return val.split( /,\s*/ );
}
function extractLast( term ) {
	return split( term ).pop();
}
/* ]]> */