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
    jQuery("#search").tagsInput(tagsInputConfig);
    jQuery("#searchAny").tagsInput(tagsInputConfig);
    jQuery("#searchExclude").tagsInput(tagsInputConfig);
    jQuery("#toggleAdvanced").click(function() {
      var advancedSearch = jQuery("#advancedSearch");
      var searchAny = jQuery("#searchAny");
      var searchExclude = jQuery("#searchExclude");
      if (advancedSearch.is(":visible")) {
        advancedSearch.hide();
        searchAny.attr("name", "");
        searchExclude.attr("name", "");
        jQuery(this).val("Show advanced search");
      } else {
        advancedSearch.show();
        searchAny.attr("name", "searchAny");
        searchExclude.attr("name", "searchExclude");
        jQuery(this).val("Hide advanced search");
      }
    });

    wallpaperList.initialiseMessageDialog();
    wallpaperList.initialiseEditDialog();
    wallpaperList.initialiseEditDialogAutoCompleteFields();
    wallpaperList.windowScroll();
    jQuery(window).scroll(function() {
      wallpaperList.windowScroll();
    });
  };

  wallpaperList.pageTriggers = function() {
    jQuery("img.lazyload").one("load", function() {
      jQuery(this).removeClass("lazyload");
    }).each(function() {
      if(this.complete) {
        jQuery(this).removeClass("lazyload");
      }
    });

    var favButtons = jQuery("a.fav_active");
    favButtons.click(function(e) {
      var wallpaperId = jQuery(this).data('wallpaperid');
      jQuery.ajax({
        url: wallpaperList.settings.basePathUrl + "ajax/wallpaper-fav?wallpaperId=" + encodeURIComponent(wallpaperId),
        cache: false,
        success: function (data) {
          jQuery("#fav_count_" + wallpaperId).text(data.favCount);
          jQuery("#fav_a_" + wallpaperId).text(data.favButtonText);
        }
      });
      e.preventDefault();
    });
    favButtons.removeClass("fav_active");

    jQuery("a.wallinfo").click(function() {
      wallpaperList.toggleInfo(jQuery(this).data('id'));
    });
    jQuery("a.editwall").click(function() {
      wallpaperList.editWall(jQuery(this).data('id'));
    });
  };

  wallpaperList.initialiseMessageDialog = function() {
    jQuery("#dialog-message").dialog({
      autoOpen: false,
      buttons: [
        {
          text: "Ok",
          click: function() {
            jQuery(this).dialog("close");
          }
        }
      ],
      width: 400,
      resizable: false,
      modal: true
    });
  };

  wallpaperList.initialiseEditDialog = function() {
    jQuery("#wallpaper_edit").dialog({
      autoOpen: false,
      buttons: [
        {
          text: "Ok",
          click: function() {
            jQuery.ajax({
              url: wallpaperList.settings.basePathUrl + "ajax/wallpaper_edit",
              type: "POST",
              data: jQuery("#wallpaper_edit_form").serialize(),
              success: function(data) {
                var dialogMessage = jQuery("#dialog-message");

                if (typeof(data.success) !== "undefined") {
                  if (data.success == '0') {
                    dialogMessage.dialog("option", "title", "Error");
                    if (typeof(data.error) !== "undefined") {
                      dialogMessage.find("p").text(data.error);
                    } else {
                      dialogMessage.find("p").text("Unknown error.");
                    }
                    dialogMessage.dialog("open");
                  } else {
                    dialogMessage.dialog("option", "title", "New information submitted");
                    if (typeof(data.novalidate) !== "undefined") {
                      jQuery.ajax({
                        url: wallpaperList.settings.basePathUrl + "ajax/wallpaper_updatetags?id=" + encodeURIComponent(jQuery("#wallid").val()),
                        cache: false,
                        success: function(newtags) {
                          jQuery("#image_info_id" + jQuery("#wallid").val()).html(newtags);
                        }
                      });
                    } else {
                      dialogMessage.find("p").text("New wallpaper information submitted to moderation. It might take a few days for it to be approved.");
                      dialogMessage.dialog("open");
                    }
                    jQuery("#wallpaper_edit").dialog("close");
                  }
                } else {
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
          click: function() { jQuery(this).dialog("close"); }
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
    jQuery("#tags").bind("keydown", function(event) {
      if (event.keyCode === jQuery.ui.keyCode.TAB && jQuery(this).data("autocomplete").menu.active) {
        event.preventDefault();
      }
    }).autocomplete({
      source: function(request, response) {
        jQuery.getJSON(wallpaperList.settings.basePathUrl + "ajax/btag_search", {
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
        return jQuery("<li>")
          .data("item.autocomplete", item)
          .append("<a style=\"line-height:1.1;\">" + item.label + "<br><span class=\"autocomplete_extra\">" + item.desc + "</span></a>")
          .appendTo(ul);
      } else {
        return jQuery("<li>")
          .data("item.autocomplete", item)
          .append("<a>" + item.label + "</a>")
          .appendTo(ul);
      }
    };
    jQuery("#author").bind("keydown", function(event) {
      if (event.keyCode === jQuery.ui.keyCode.TAB && jQuery(this).data("autocomplete").menu.active) {
        event.preventDefault();
      }
    }).autocomplete({
      source: function(request, response) {
        jQuery.getJSON(wallpaperList.settings.basePathUrl + "ajax/author_search", {
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
        return jQuery("<li>")
          .data("item.autocomplete", item)
          .append("<a style=\"line-height:1.1;\">" + item.label + "<br><span class=\"autocomplete_extra\">" + item.desc + "</span></a>")
          .appendTo(ul);
      } else {
        return jQuery("<li>")
          .data("item.autocomplete", item)
          .append("<a>" + item.label + "</a>")
          .appendTo(ul);
      }
    };
    jQuery("#platform").bind("keydown", function(event) {
      if (event.keyCode === jQuery.ui.keyCode.TAB && jQuery(this).data("autocomplete").menu.active) {
        event.preventDefault();
      }
    }).autocomplete({
      source: function(request, response) {
        jQuery.getJSON(wallpaperList.settings.basePathUrl + "ajax/platform_search", {
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
    var wallpaperInfoContainer = jQuery("#image_container_" + id + ">div");
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
    jQuery(el).css("z-index", "499");
    jQuery(el).children("div.image_extra_info").css("z-index", "499");
    if (jQuery(el).children("div.image_basicinfo").offset().left + jQuery(el).children("div.image_basicinfo").width() + grow_amount + 15 > jQuery(window).width()) {
      animation_hide = {
        left: "+=" + grow_amount,
        width: "-=" + grow_amount
      };
    } else {
      animation_hide = {
        width: "-=" + grow_amount
      };
    }
    jQuery(el).animate(animation_hide, 200, function() {
      jQuery(el).children("div.image_extra_info").css("height", "");
      jQuery(el).css("z-index", "");
      jQuery(el).children("div.image_extra_info").css("z-index", "");
      if (hide_all) {
        wallpaperList.infoOpen = "";
      }
      if (jQuery(el).children("div.image_basicinfo").offset().left + jQuery(el).children("div.image_basicinfo").width() + grow_amount + 15 > jQuery(window).width()) {
        jQuery(el).children("div.image_basicinfo").css("float", "");
      }
    });
  };

  wallpaperList.showExtraInfo = function(el) {
    var grow_amount = wallpaperList.getGrowAmount(el);
    var animation_show = {};

    jQuery(el).parent().height(jQuery(el).height());
    jQuery(el).css("z-index", "500");
    jQuery(el).children("div.image_extra_info").css("z-index", "500");
    if (jQuery(el).children("div.image_basicinfo").offset().left + jQuery(el).children("div.image_basicinfo").width() + grow_amount + 15 > jQuery(window).width()) {
      jQuery(el).children("div.image_basicinfo").css("float", "right");
      jQuery(el).children("div.image_extra_info").css("right", (wallpaperList.settings.largeWallpaperThumbs ? 478 : 221));
      jQuery(el).children("div.image_extra_info").css("left", "auto");
      animation_show = {
        left: "-=" + grow_amount,
        width: "+=" + grow_amount
      };
    } else {
      jQuery(el).children("div.image_extra_info").css("right", "auto");
      jQuery(el).children("div.image_extra_info").css("left", (wallpaperList.settings.largeWallpaperThumbs ? 478 : 221));
      animation_show = {
        width: "+=" + grow_amount
      };
    }
    if (jQuery(el).children("div.image_extra_info").outerHeight(true) > jQuery(el).outerHeight(true)) {
      var padding = jQuery(el).children("div.image_extra_info").outerHeight(true) - jQuery(el).children("div.image_extra_info").height();
      jQuery(el).children("div.image_extra_info").css({"height": jQuery(el).outerHeight(true) - padding, "overflow": "auto", "width": 370});
      jQuery(el).children("div.image_extra_info").children().css("margin-right", 5);
      if (jQuery(el).children("div.image_basicinfo").offset().left + jQuery(el).children("div.image_basicinfo").width() + grow_amount + 15 > jQuery(window).width()) {
        jQuery(el).children("div.image_extra_info").css("right", (wallpaperList.settings.largeWallpaperThumbs ? 478 : 221) - 5);
      }
      jQuery(el).children("div.image_extra_info").scrollTop(0);
    }
    jQuery(el).animate(animation_show, 200, function() {
      // Do nothing
    });
  };

  wallpaperList.getGrowAmount = function(el) {
    if (jQuery(el).children("div.image_extra_info").width() > 215 || jQuery(el).children("div.image_extra_info").outerHeight(true) > jQuery(el).outerHeight(true)) {
      if (jQuery(el).children("div.image_extra_info").width() == 215) {
        jQuery(el).children("div.image_extra_info").css("width", 365);
      }
      return 375;
    } else {
      //var padding = jQuery(el).children("div.image_extra_info").outerHeight(true) - jQuery(el).children("div.image_extra_info").height();
      return 225;
    }
  };

  wallpaperList.editWall = function(id) {
    jQuery.ajax({
      url: wallpaperList.settings.basePathUrl + "ajax/wallpaper_info?id=" + encodeURIComponent(id),
      cache: false,
      success: function(data) {
        if (typeof(data.name) !== "undefined") {
          jQuery("#wallid").val(data.id);
          jQuery("#name").val(data.name);
          jQuery("#author").val(data.author);
          jQuery("#tags").val(data.tags);
          jQuery("#platform").val(data.platform);
          jQuery("#url").val(data.url);
          jQuery("#wallpaper_edit").dialog("open");
        }
      }
    });
    return false;
  };

  wallpaperList.windowScroll = function() {
    if (jQuery("body").height() <= (jQuery(window).height() + jQuery(window).scrollTop() + 400)) {
      if (!wallpaperList.alreadyloading && !wallpaperList.noMore) {
        wallpaperList.alreadyloading = true;
        jQuery.ajax({
          url: wallpaperList.settings.basePathUrl + 'ajax/' + wallpaperList.settings.ajaxLoadMorePage +
            wallpaperList.settings.ajaxRedirect + (wallpaperList.settings.ajaxRedirect !== '' ? '&' : '?') +
            'page=' + wallpaperList.nextPage,
          cache: false,
          success: function(data) {
            if (data === "") {
              wallpaperList.noMore = true;
            } else {
              wallpaperList.nextPage ++;
              jQuery("#cleardiv").before(data);
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