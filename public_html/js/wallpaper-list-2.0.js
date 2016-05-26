/**
 * @param settings
 * @constructor
 */
function WallpaperList(settings) {
	var wallpaperList = this;
	var defaultSettings = {
		"ajaxLoadMorePage": "",
		"ajaxRedirect": "",
		"basePathUrl": "",
		"largeWallpaperThumbs": false,
		"nextPage": 0,
		"userIsAnonymous": true
	};

	// Internal variables
	wallpaperList.alreadyLoading = false;
	wallpaperList.nextPage = 0;
	wallpaperList.noMore = false;
	wallpaperList.infoOpen = "";
	wallpaperList.settings = defaultSettings;

	// Get the settings
	for (var prop in settings) {
		if (settings.hasOwnProperty(prop)) {
			wallpaperList.settings[prop] = settings[prop];
		}
	}
	wallpaperList.nextPage = wallpaperList.settings.nextPage;


	wallpaperList.initialise = function() {
		wallpaperList.pageTriggers();
		var tagsInputConfig = {
			autocomplete_url: wallpaperList.settings.basePathUrl + "ajax/tag_search",
			autocomplete: {
				"focus": function(event)  {
					event.preventDefault();
				}
			},
			height: "62px",
			width: "450px"
		};
		$("#search").tagsInput(tagsInputConfig);
		$("#searchAny").tagsInput(tagsInputConfig);
		$("#searchExclude").tagsInput(tagsInputConfig);
		$("#toggleAdvanced").click(function() {
			var advancedSearch = $("#advancedSearch");
			var searchAny = $("#searchAny");
			var searchExclude = $("#searchExclude");
			if (advancedSearch.is(":visible")) {
				advancedSearch.hide();
				searchAny.attr("name", "");
				searchExclude.attr("name", "");
				$(this).val("Show advanced search");
			} else {
				advancedSearch.show();
				searchAny.attr("name", "searchAny");
				searchExclude.attr("name", "searchExclude");
				$(this).val("Hide advanced search");
			}
		});

		wallpaperList.initialiseMessageDialog();
		wallpaperList.initialiseEditDialog();
		wallpaperList.initialiseEditDialogAutoCompleteFields();
		wallpaperList.windowScroll();
		$(window).scroll(function() {
			wallpaperList.windowScroll();
		});
	};

	wallpaperList.pageTriggers = function() {
		var imgLazy = $("img.lazy");
		imgLazy.lazyload({effect:"fadeIn", threshold: 200});
		imgLazy.removeClass("lazy");

		var favButtons = $("a.fav_active");
		favButtons.click(function(e) {
			var wallpaperId = $(this).data('wallpaperid');
			$.ajax({
				url: wallpaperList.settings.basePathUrl + "ajax/wallpaper-fav?wallpaperId=" + encodeURIComponent(wallpaperId),
				cache: false,
				success: function (data) {
					$("#fav_count_" + wallpaperId).text(data.favCount);
					$("#fav_a_" + wallpaperId).text(data.favButtonText);
				}
			});
			e.preventDefault();
		});
		favButtons.removeClass("fav_active");

		$("a.wallinfo").click(function() {
			wallpaperList.toggleInfo($(this).data('id'));
		});
		$("a.editwall").click(function() {
			wallpaperList.editWall($(this).data('id'));
		});
	};

	wallpaperList.initialiseMessageDialog = function() {
		$("#dialog-message").dialog({
			autoOpen: false,
			buttons: [
				{
					text: "Ok",
					click: function() {
						$(this).dialog("close");
					}
				}
			],
			width: 400,
			resizable: false,
			modal: true
		});
	};

	wallpaperList.initialiseEditDialog = function() {
		$("#wallpaper_edit").dialog({
			autoOpen: false,
			buttons: [
				{
					text: "Ok",
					click: function() {
						$.ajax({
							url: wallpaperList.settings.basePathUrl + "ajax/wallpaper_edit",
							type: "POST",
							data: $("#wallpaper_edit_form").serialize(),
							success: function(data) {
								var dialogMessage = $("#dialog-message");

								if (typeof(data.success) != "undefined") {
									if (data.success == '0') {
										if (wallpaperList.settings.userIsAnonymous) {
											Recaptcha.reload();
										}
										dialogMessage.dialog("option", "title", "Error");
										if (typeof(data.error) != "undefined") {
											dialogMessage.find("p").text(data.error);
										} else {
											dialogMessage.find("p").text("Unknown error.");
										}
										dialogMessage.dialog("open");
									} else {
										dialogMessage.dialog("option", "title", "New information submitted");
										if (typeof(data.novalidate) != "undefined") {
											$.ajax({
												url: wallpaperList.settings.basePathUrl + "ajax/wallpaper_updatetags?id=" + encodeURIComponent($("#wallid").val()),
												cache: false,
												success: function(newtags) {
													$("#image_info_id" + $("#wallid").val()).html(newtags);
												}
											});
										} else {
											dialogMessage.find("p").text("New wallpaper information submitted to moderation. It might take a few days for it to be approved.");
											dialogMessage.dialog("open");
										}
										$("#wallpaper_edit").dialog("close");
									}
								} else {
									if (wallpaperList.settings.userIsAnonymous) {
										Recaptcha.reload();
									}
									dialogMessage.dialog("option", "title", "Error");
									dialogMessage.find("p").text("Unknown error.");
									dialogMessage.dialog("open");
								}
							}
						});
					}
				},
				{
					text: "Cancel",
					click: function() { $(this).dialog("close"); }
				}
			],
			close: function(event, ui) {
			},
			width: 510,
			resizable: false,
			modal: true
		});
	};

	wallpaperList.initialiseEditDialogAutoCompleteFields = function() {
		$("#tags").bind("keydown", function(event) {
			if (event.keyCode === $.ui.keyCode.TAB && $(this).data("autocomplete").menu.active) {
				event.preventDefault();
			}
		}).autocomplete({
			source: function(request, response) {
				$.getJSON(wallpaperList.settings.basePathUrl + "ajax/btag_search", {
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
		}).autocomplete("instance")._renderItem = function(ul, item) {
			if (item.desc !== "") {
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
				$.getJSON(wallpaperList.settings.basePathUrl + "ajax/author_search", {
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
		}).autocomplete("instance")._renderItem = function(ul, item) {
			if (item.desc !== "") {
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
				$.getJSON(wallpaperList.settings.basePathUrl + "ajax/platform_search", {
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
	};

	wallpaperList.toggleInfo = function(id) {
		var wallpaperInfoContainer = $("#image_container_" + id + ">div");
		if (wallpaperInfoContainer.width() > (wallpaperList.settings.largeWallpaperThumbs ? 478 : 221) + 150) {
			if (!wallpaperInfoContainer.is(":animated")) {
				wallpaperList.hideExtraInfo("#image_container_" + id + ">div", true);
			}
		} else {
			if (wallpaperList.infoOpen !== "" && wallpaperList.infoOpen != id) {
				wallpaperList.hideExtraInfo("#image_container_" + wallpaperList.infoOpen + ">div", false);
			}
			if (!wallpaperInfoContainer.is(":animated")) {
				wallpaperList.infoOpen = id;
				wallpaperList.showExtraInfo("#image_container_" + id + ">div");
			} else {
				wallpaperList.infoOpen = "";
			}
		}
		return false;
	};

	wallpaperList.hideExtraInfo = function(el, hide_all) {
		var grow_amount = wallpaperList.getGrowAmount(el);
		var animation_hide = {};
		$(el).css("z-index", "499");
		$(el).children("div.image_extra_info").css("z-index", "499");
		if ($(el).children("div.image_basicinfo").offset().left + $(el).children("div.image_basicinfo").width() + grow_amount + 15 > $(window).width()) {
			animation_hide = {
				left: "+=" + grow_amount,
				width: "-=" + grow_amount
			};
		} else {
			animation_hide = {
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
				wallpaperList.infoOpen = "";
			}
			if ($(el).children("div.image_basicinfo").offset().left + $(el).children("div.image_basicinfo").width() + grow_amount + 15 > $(window).width()) {
				$(el).children("div.image_basicinfo").css("float", "");
			}
		});
	};

	wallpaperList.showExtraInfo = function(el) {
		var grow_amount = wallpaperList.getGrowAmount(el);
		var animation_show = {};

		$(el).parent().height($(el).height());
		$(el).css("z-index", "500");
		$(el).children("div.image_extra_info").css("z-index", "500");
		if ($(el).children("div.image_basicinfo").offset().left + $(el).children("div.image_basicinfo").width() + grow_amount + 15 > $(window).width()) {
			$(el).children("div.image_basicinfo").css("float", "right");
			$(el).children("div.image_extra_info").css("right", (wallpaperList.settings.largeWallpaperThumbs ? 478 : 221));
			$(el).children("div.image_extra_info").css("left", "auto");
			animation_show = {
				left: "-=" + grow_amount,
				width: "+=" + grow_amount
			};
		} else {
			$(el).children("div.image_extra_info").css("right", "auto");
			$(el).children("div.image_extra_info").css("left", (wallpaperList.settings.largeWallpaperThumbs ? 478 : 221));
			animation_show = {
				width: "+=" + grow_amount
			};
		}
		if ($(el).children("div.image_extra_info").outerHeight(true) > $(el).outerHeight(true)) {
			var padding = $(el).children("div.image_extra_info").outerHeight(true) - $(el).children("div.image_extra_info").height();
			$(el).children("div.image_extra_info").css("height", $(el).outerHeight(true) - padding);
			$(el).children("div.image_extra_info").children().css("margin-right", 18);
			$(el).children("div.image_extra_info").css("width", 370);
			if ($(el).children("div.image_basicinfo").offset().left + $(el).children("div.image_basicinfo").width() + grow_amount + 15 > $(window).width()) {
				$(el).children("div.image_extra_info").css("right", (wallpaperList.settings.largeWallpaperThumbs ? 478 : 221) - 5);
			}
			$(el).children("div.image_extra_info").scrollTop(0);
			$(el).children("div.image_extra_info").perfectScrollbar({wheelSpeed: 10});
		}
		$(el).animate(animation_show, 200, function() {
			// Do nothing
		});
	};

	wallpaperList.getGrowAmount = function(el) {
		if ($(el).children("div.image_extra_info").width() > 215 || $(el).children("div.image_extra_info").outerHeight(true) > $(el).outerHeight(true)) {
			if ($(el).children("div.image_extra_info").width() == 215) {
				$(el).children("div.image_extra_info").css("width", 365);
			}
			return 375;
		} else {
			//var padding = $(el).children("div.image_extra_info").outerHeight(true) - $(el).children("div.image_extra_info").height();
			return 225;
		}
	};

	wallpaperList.editWall = function(id) {
		if (wallpaperList.settings.userIsAnonymous) {
			Recaptcha.reload();
		}
		$.ajax({
			url: wallpaperList.settings.basePathUrl + "ajax/wallpaper_info?id=" + encodeURIComponent(id),
			cache: false,
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
	};

	wallpaperList.windowScroll = function() {
		if ($("body").height() <= ($(window).height() + $(window).scrollTop() + 400)) {
			if (!wallpaperList.alreadyloading && !wallpaperList.noMore) {
				wallpaperList.alreadyloading = true;
				$.ajax({
					url: wallpaperList.settings.basePathUrl + 'ajax/' + wallpaperList.settings.ajaxLoadMorePage +
						wallpaperList.settings.ajaxRedirect + (wallpaperList.settings.ajaxRedirect != '' ? '&' : '?') +
						'page=' + wallpaperList.nextPage,
					cache: false,
					success: function(data) {
						if (data == "") {
							wallpaperList.noMore = true;
						} else {
							wallpaperList.nextPage ++;
							$("#cleardiv").before(data);
							wallpaperList.pageTriggers();
						}
						wallpaperList.alreadyloading = false;
					}
				});
			}
		}
	};

	// Time to initialise this bad puppy
	wallpaperList.initialise();
}