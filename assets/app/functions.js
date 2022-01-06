/**
 * @param {jqXHR} jqXHR
 * @param {string} textStatus
 * @param {string} error
 */
function handleAjaxFailure(jqXHR, textStatus, error) {
  "use strict";
  let dialogMessage = jQuery("#dialog-message");
  dialogMessage.dialog("option", "title", "Error");
  dialogMessage.find("p").text(textStatus + ", " + error);
  dialogMessage.dialog("open");
}

/**
 * @param {!string} val
 * @returns {string[]}
 */
function split( val ) {
  "use strict";
  return val.split(/,\s*/);
}

/**
 *
 * @param {string} term
 * @returns {string}
 */
function extractLast( term ) {
  "use strict";
  return split(term).pop();
}
