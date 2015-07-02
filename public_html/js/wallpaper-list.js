var userIsAnonymous = true;
var alreadyloading = false;
var nomore = false;
var nextpage = 0;
var info_open = "";
var basePathUrl = "";
var largeWallpaperThumbs = false;

function page_triggers() {
	var imgLazy = $("img.lazy");
	imgLazy.lazyload({effect:"fadeIn", threshold: 200});
	imgLazy.removeClass("lazy");

	var favButtons = $("a.fav_active");
	favButtons.click(function(e) {
		var wallpaperId = $(this).data('wallpaperid');
		$.ajax({
			url: basePathUrl + "ajax/wallpaper-fav?wallpaperId=" + encodeURIComponent(wallpaperId),
			success: function (data) {
				$("#fav_count_" + wallpaperId).text(data.favCount);
				$("#fav_a_" + wallpaperId).text(data.favButtonText);
			}
		});
		e.preventDefault();
	});
	favButtons.removeClass("fav_active");
}

function toggle_info(id) {
	if ($("#image_container_" + id + ">div").width() > (largeWallpaperThumbs ? 478 : 221) + 150) {
		if (!$("#image_container_" + id + ">div").is(":animated")) {
			hide_extra_info("#image_container_" + id + ">div", true);
		}
	} else {
		if (info_open != "" && info_open != id) {
			hide_extra_info("#image_container_" + info_open + ">div", false);
		}
		if (!$("#image_container_" + id + ">div").is(":animated")) {
			info_open = id;
			show_extra_info("#image_container_" + id + ">div");
		} else {
			info_open = "";
		}
	}
	return false;
}

function hide_extra_info(el, hide_all) {
	var grow_amount = get_grow_amount(el);
	$(el).css("z-index", "499");
	$(el).children("div.image_extra_info").css("z-index", "499");
	if ($(el).children("div.image_basicinfo").offset().left + $(el).children("div.image_basicinfo").width() + grow_amount + 15 > $(window).width()) {
		var animation_hide = {
			left: "+=" + grow_amount,
			width: "-=" + grow_amount
		};
	} else {
		var animation_hide = {
			width: "-=" + grow_amount
		};
	}
	$(el).animate(animation_hide, 200, function() {
		$(el).children("div.image_extra_info").css("height", "");
		if ($(el).children("div.image_extra_info").outerHeight(true) > $(el).outerHeight(true)) {
			$(el).children("div.image_extra_info").perfectScrollbar("destroy");
		}
		$(el).css("z-index", "");
		$(el).children("div.image_extra_info").css("z-index", "");
		if (hide_all) {
			info_open = "";
		}
		if ($(el).children("div.image_basicinfo").offset().left + $(el).children("div.image_basicinfo").width() + grow_amount + 15 > $(window).width()) {
			$(el).children("div.image_basicinfo").css("float", "");
		}
	});
}

function show_extra_info(el) {
	var grow_amount = get_grow_amount(el);
	$(el).parent().height($(el).height());
	$(el).css("z-index", "500");
	$(el).children("div.image_extra_info").css("z-index", "500");
	if ($(el).children("div.image_basicinfo").offset().left + $(el).children("div.image_basicinfo").width() + grow_amount + 15 > $(window).width()) {
		$(el).children("div.image_basicinfo").css("float", "right");
		$(el).children("div.image_extra_info").css("right", (largeWallpaperThumbs ? 478 : 221));
		$(el).children("div.image_extra_info").css("left", "auto");
		var animation_show = {
			left: "-=" + grow_amount,
			width: "+=" + grow_amount
		};
	} else {
		$(el).children("div.image_extra_info").css("right", "auto");
		$(el).children("div.image_extra_info").css("left", (largeWallpaperThumbs ? 478 : 221));
		var animation_show = {
			width: "+=" + grow_amount
		};
	}
	if ($(el).children("div.image_extra_info").outerHeight(true) > $(el).outerHeight(true)) {
		var padding = $(el).children("div.image_extra_info").outerHeight(true) - $(el).children("div.image_extra_info").height();
		$(el).children("div.image_extra_info").css("height", $(el).outerHeight(true) - padding);
		$(el).children("div.image_extra_info").children().css("margin-right", 18);
		$(el).children("div.image_extra_info").css("width", 370);
		if ($(el).children("div.image_basicinfo").offset().left + $(el).children("div.image_basicinfo").width() + grow_amount + 15 > $(window).width()) {
			$(el).children("div.image_extra_info").css("right", (largeWallpaperThumbs ? 478 : 221) - 5);
		}
		$(el).children("div.image_extra_info").scrollTop(0);
		$(el).children("div.image_extra_info").perfectScrollbar({wheelSpeed: 20});
	}
	$(el).animate(animation_show, 200, function() {
		// Do nothing
	});
}

function get_grow_amount(el) {
	if ($(el).children("div.image_extra_info").width() > 215 || $(el).children("div.image_extra_info").outerHeight(true) > $(el).outerHeight(true)) {
		if ($(el).children("div.image_extra_info").width() == 215) {
			$(el).children("div.image_extra_info").css("width", 365);
		}
		return 375;
	} else {
		var padding = $(el).children("div.image_extra_info").outerHeight(true) - $(el).children("div.image_extra_info").height();
		return 225;
	}
}

$(document).ready(function(){
	page_triggers();
	var tagsInputConfig = {
		autocomplete_url: basePathUrl + "ajax/tag_search",
		autocomplete: {
			"focus": function( event, ui ) {
				event.preventDefault();
			}
		},
		height: "62px",
		width: "450px"
	};
	$("#search").tagsInput(tagsInputConfig);
	$("#searchAny").tagsInput(tagsInputConfig);
	$("#searchExclude").tagsInput(tagsInputConfig);
	$("#wallpaper_edit").dialog({
		autoOpen: false,
		buttons: [
			{
				text: "Ok",
				click: function() {
					$.ajax({
						url: basePathUrl + "ajax/wallpaper_edit",
						type: "POST",
						data: $("#wallpaper_edit_form").serialize(),
						success: function(data) {
							if (typeof(data.success) != "undefined") {
								if (data.success == 0) {
									if (userIsAnonymous) {
										Recaptcha.reload();
									}
									$("#dialog-message").dialog("option", "title", "Error");
									if (typeof(data.error) != "undefined") {
										$("#dialog-message p").text(data.error);
									} else {
										$("#dialog-message p").text("Unknown error.");
									}
									$("#dialog-message").dialog("open");
								} else {
									$("#dialog-message").dialog("option", "title", "New information submitted");
									if (typeof(data.novalidate) != "undefined") {
										$.ajax({
											url: basePathUrl + "ajax/wallpaper_updatetags?id=" + encodeURIComponent($("#wallid").val()),
											success: function(newtags) {
												$("#image_info_id" + $("#wallid").val()).html(newtags);
											}
										});
									} else {
										$("#dialog-message p").text("New wallpaper information submitted to moderation. It might take a few days for it to be approved.");
										$("#dialog-message").dialog("open");
									}
									$("#wallpaper_edit").dialog("close");
								}
							} else {
								if (userIsAnonymous) {
									Recaptcha.reload();
								}
								$("#dialog-message").dialog("option", "title", "Error");
								$("#dialog-message p").text("Unknown error.");
								$("#dialog-message").dialog("open");
							}
						}
					});
				}
			},
			{
				text: "Cancel",
				click: function() { $(this).dialog("close"); }
			},
		],
		close: function(event, ui) {
		},
		width: 510,
		resizable: false,
		modal: true
	});
	$("#dialog-message").dialog({
		autoOpen: false,
		buttons: [
			{
				text: "Ok",
				click: function() { $(this).dialog("close"); }
			}
		],
		width: 400,
		resizable: false,
		modal: true
	});
	$("#tags").bind("keydown", function(event) {
		if (event.keyCode === $.ui.keyCode.TAB && $(this).data("autocomplete").menu.active) {
			event.preventDefault();
		}
	}).autocomplete({
		source: function(request, response) {
			$.getJSON(basePathUrl + "ajax/btag_search", {
				term: extractLast(request.term)
			}, response);
		},
		search: function() {
			var term = extractLast(this.value);
			if (term.length < 2) {
				return false;
			}
		},
		focus: function() {
			return false;
		},
		select: function(event, ui) {
			var terms = split(this.value);
			terms.pop();
			terms.push(ui.item.value);
			terms.push("");
			this.value = terms.join(", ");
			return false;
		}
	}).data("autocomplete")._renderItem = function(ul, item) {
		if (item.desc != "") {
			return $("<li>")
				.data("item.autocomplete", item)
				.append("<a style=\"line-height:1.1;\">" + item.label + "<br><span class=\"autocomplete_extra\">" + item.desc + "</span></a>")
				.appendTo(ul);
		} else {
			return $("<li>")
				.data("item.autocomplete", item)
				.append("<a>" + item.label + "</a>")
				.appendTo(ul);
		}
	};
	$("#author").bind("keydown", function(event) {
		if (event.keyCode === $.ui.keyCode.TAB && $(this).data("autocomplete").menu.active) {
			event.preventDefault();
		}
	}).autocomplete({
		source: function(request, response) {
			$.getJSON(basePathUrl + "ajax/author_search", {
				term: extractLast(request.term)
			}, response);
		},
		search: function() {
			var term = extractLast(this.value);
			if (term.length < 2) {
				return false;
			}
		},
		focus: function() {
			return false;
		},
		select: function(event, ui) {
			var terms = split(this.value);
			terms.pop();
			terms.push(ui.item.value);
			terms.push("");
			this.value = terms.join(", ");
			return false;
		}
	}).data("autocomplete")._renderItem = function(ul, item) {
		if (item.desc != "") {
			return $("<li>")
				.data("item.autocomplete", item)
				.append("<a style=\"line-height:1.1;\">" + item.label + "<br><span class=\"autocomplete_extra\">" + item.desc + "</span></a>")
				.appendTo(ul);
		} else {
			return $("<li>")
				.data("item.autocomplete", item)
				.append("<a>" + item.label + "</a>")
				.appendTo(ul);
		}
	};
	$("#platform").bind("keydown", function(event) {
		if (event.keyCode === $.ui.keyCode.TAB && $(this).data("autocomplete").menu.active) {
			event.preventDefault();
		}
	}).autocomplete({
		source: function(request, response) {
			$.getJSON(basePathUrl + "ajax/platform_search", {
				term: extractLast(request.term)
			}, response);
		},
		search: function() {
			var term = extractLast(this.value);
			if (term.length < 2) {
				return false;
			}
		},
		focus: function() {
			return false;
		},
		select: function(event, ui) {
			var terms = split(this.value);
			terms.pop();
			terms.push(ui.item.value);
			terms.push("");
			this.value = terms.join(", ");
			return false;
		}
	});
});

function edit_wall(id) {
	if (userIsAnonymous) {
		Recaptcha.reload();
	}
	$.ajax({
		url: basePathUrl + "ajax/wallpaper_info?id=" + encodeURIComponent(id) + "&nocache=" + new Date().getTime(),
		success: function(data) {
			if (typeof(data.name) != "undefined") {
				$("#wallid").val(data.id);
				$("#name").val(data.name);
				$("#author").val(data.author);
				$("#tags").val(data.tags);
				$("#platform").val(data.platform);
				$("#url").val(data.url);
				$("#wallpaper_edit").dialog("open");
			}
		}
	});
	return false;
}
function toggleAdvancedSearch(el) {
	var advancedSearch = $("#advancedSearch");
	var searchAny = $("#searchAny");
	var searchExclude = $("#searchExclude");
	if (advancedSearch.is(":visible")) {
		advancedSearch.hide();
		searchAny.attr("name", "");
		searchExclude.attr("name", "");
		el.val("Show advanced search");
	} else {
		advancedSearch.show();
		searchAny.attr("name", "searchAny");
		searchExclude.attr("name", "searchExclude");
		el.val("Hide advanced search");
	}
}
var RecaptchaOptions = {
	lang : 'en',
	theme : 'clean'
};