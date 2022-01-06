/**
 * @param settings
 * @constructor
 */
function WallpaperList(settings) {
  "use strict";

  let wallpaperList = this;
  let defaultSettings = {
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
  for (let prop in settings) {
    if (settings.hasOwnProperty(prop)) {
      wallpaperList.settings[prop] = settings[prop];
    }
  }
  wallpaperList.nextPage = wallpaperList.settings.nextPage;


  wallpaperList.initialise = function() {
    wallpaperList.pageTriggers();
    let tagsInputConfig = {
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
    jQuery("#toggleAdvanced").on("click", function() {
      let advancedSearch = jQuery("#advancedSearch");
      let searchAny = jQuery("#searchAny");
      let searchExclude = jQuery("#searchExclude");
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
    jQuery(window).on("scroll", function() {
      wallpaperList.windowScroll();
    });
  };

  wallpaperList.pageTriggers = function() {
    jQuery("img.lazyload").on("load", function() {
      jQuery(this).removeClass("lazyload");
    });

    let favButtons = jQuery("a.fav_active");
    favButtons.on("click", function(e) {
      let wallpaperId = jQuery(this).data("wallpaperid");
      jQuery.ajax({
        url: wallpaperList.settings.basePathUrl + "ajax/wallpaper-fav?wallpaperId=" + encodeURIComponent(wallpaperId),
        cache: false
      }).then(
        /**
         * @param {!Object} data
         * @param {!string} data.favCount
         * @param {!string} data.favButtonText
         */
        function (data) {
          jQuery("#fav_count_" + wallpaperId).text(data.favCount);
          jQuery("#fav_a_" + wallpaperId).text(data.favButtonText);
        },
        handleAjaxFailure
      );
      e.preventDefault();
    });
    favButtons.removeClass("fav_active");

    jQuery("a.wallinfo").on("click", function() {
      wallpaperList.toggleInfo(jQuery(this).data("id"));
    });
    jQuery("a.editwall").on("click", function() {
      wallpaperList.editWall(jQuery(this).data("id"));
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
            }).then(
              /**
               * @param {Object} data
               * @param {?boolean} data.success
               * @param {?string} data.error
               * @param {?boolean} data.novalidate
               */
              function (data) {
                let dialogMessage = jQuery("#dialog-message");

                if (typeof(data.success) !== "undefined") {
                  if (!data.success) {
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
                        cache: false
                      }).then(
                        function(newtags) {
                          jQuery("#image_info_id" + jQuery("#wallid").val()).html(newtags);
                        },
                        handleAjaxFailure
                      );
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
              },
              handleAjaxFailure
            );
          }
        },
        {
          text: "Cancel",
          click: function() {
            jQuery(this).dialog("close");
          }
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
    jQuery("#tags").on("keydown", function(event) {
      if (event.code === "Tab" && jQuery(this).data("autocomplete").menu.active) {
        event.preventDefault();
      }
    }).autocomplete({
      source: function(request, response) {
        jQuery.getJSON(wallpaperList.settings.basePathUrl + "ajax/btag_search", {
          term: extractLast(request.term)
        }, response);
      },
      search: function() {
        let term = extractLast(this.value);
        if (term.length < 2) {
          return false;
        }
      },
      focus: function() {
        return false;
      },
      select: function(event, ui) {
        let terms = split(this.value);
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
    jQuery("#author").on("keydown", function(event) {
      if (event.code === "Tab" && jQuery(this).data("autocomplete").menu.active) {
        event.preventDefault();
      }
    }).autocomplete({
      source: function(request, response) {
        jQuery.getJSON(wallpaperList.settings.basePathUrl + "ajax/author_search", {
          term: extractLast(request.term)
        }, response);
      },
      search: function() {
        let term = extractLast(this.value);
        if (term.length < 2) {
          return false;
        }
      },
      focus: function() {
        return false;
      },
      select: function(event, ui) {
        let terms = split(this.value);
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
    jQuery("#platform").on("keydown", function(event) {
      if (event.code === "Tab" && jQuery(this).data("autocomplete").menu.active) {
        event.preventDefault();
      }
    }).autocomplete({
      source: function(request, response) {
        jQuery.getJSON(wallpaperList.settings.basePathUrl + "ajax/platform_search", {
          term: extractLast(request.term)
        }, response);
      },
      search: function() {
        let term = extractLast(this.value);
        if (term.length < 2) {
          return false;
        }
      },
      focus: function() {
        return false;
      },
      select: function(event, ui) {
        let terms = split(this.value);
        terms.pop();
        terms.push(ui.item.value);
        terms.push("");
        this.value = terms.join(", ");
        return false;
      }
    });
  };

  wallpaperList.toggleInfo = function(id) {
    let wallpaperInfoContainer = jQuery("#image_container_" + id + ">div");
    if (wallpaperInfoContainer.width() > (wallpaperList.settings.largeWallpaperThumbs ? 478 : 221) + 150) {
      if (!wallpaperInfoContainer.is(":animated")) {
        wallpaperList.hideExtraInfo("#image_container_" + id + ">div", true);
      }
    } else {
      if (wallpaperList.infoOpen !== "" && wallpaperList.infoOpen !== id) {
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
    let grow_amount = wallpaperList.getGrowAmount(el);
    let animation_hide;
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
    let grow_amount = wallpaperList.getGrowAmount(el);
    let animation_show;

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
      let padding = jQuery(el).children("div.image_extra_info").outerHeight(true) - jQuery(el).children("div.image_extra_info").height();
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
      if (jQuery(el).children("div.image_extra_info").width() === 215) {
        jQuery(el).children("div.image_extra_info").css("width", 365);
      }
      return 375;
    } else {
      return 225;
    }
  };

  wallpaperList.editWall = function(id) {
    jQuery.ajax({
      url: wallpaperList.settings.basePathUrl + "ajax/wallpaper_info?id=" + encodeURIComponent(id),
      cache: false
    }).then(
      /**
       * @param {Object} data
       * @param {!number} data.id
       * @param {!string} data.name
       * @param {!string} data.author
       * @param {!string} data.tags
       * @param {!string} data.platform
       * @param {!string} data.url
       */
      function(data) {
        if (typeof(data.name) !== "undefined") {
          jQuery("#wallid").val(data.id.toFixed());
          jQuery("#name").val(data.name);
          jQuery("#author").val(data.author);
          jQuery("#tags").val(data.tags);
          jQuery("#platform").val(data.platform);
          jQuery("#url").val(data.url);
          jQuery("#wallpaper_edit").dialog("open");
        }
      },
      handleAjaxFailure
    );
    return false;
  };

  wallpaperList.windowScroll = function() {
    if (jQuery("body").height() <= (jQuery(window).height() + jQuery(window).scrollTop() + 400)) {
      if (!wallpaperList.alreadyloading && !wallpaperList.noMore) {
        wallpaperList.alreadyloading = true;
        jQuery.ajax({
          url: wallpaperList.settings.basePathUrl + "ajax/" + wallpaperList.settings.ajaxLoadMorePage +
            wallpaperList.settings.ajaxRedirect + (wallpaperList.settings.ajaxRedirect !== "" ? "&" : "?") +
            "page=" + wallpaperList.nextPage,
          cache: false
        }).then(
          /**
           * {!string} @param data
           */
          function(data) {
            if (data === "") {
              wallpaperList.noMore = true;
            } else {
              wallpaperList.nextPage ++;
              jQuery("#cleardiv").before(data);
              wallpaperList.pageTriggers();
            }
            wallpaperList.alreadyloading = false;
          },
          handleAjaxFailure
        );
      }
    }
  };

  // Time to initialise this bad puppy
  wallpaperList.initialise();
}