/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./assets/src/js/admin.js":
/*!********************************!*\
  !*** ./assets/src/js/admin.js ***!
  \********************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

var _require = __webpack_require__(/*! ./admin/releases */ "./assets/src/js/admin/releases.js"),
    renderProductReleases = _require.renderProductReleases;

var _require2 = __webpack_require__(/*! ./admin/media-upload */ "./assets/src/js/admin/media-upload.js"),
    mediaButtonEvent = _require2.mediaButtonEvent;

document.addEventListener('DOMContentLoaded', function () {
  renderProductReleases();
  mediaButtonEvent();
});

/***/ }),

/***/ "./assets/src/js/admin/media-upload.js":
/*!*********************************************!*\
  !*** ./assets/src/js/admin/media-upload.js ***!
  \*********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "mediaButtonEvent": () => (/* binding */ mediaButtonEvent)
/* harmony export */ });
/* global eddSlReleases */
function mediaButtonEvent() {
  var mediaButtons = document.querySelectorAll('.edd-sl-releases--upload');

  if (!mediaButtons) {
    return;
  }

  mediaButtons.forEach(function (button) {
    button.addEventListener('click', function (e) {
      e.preventDefault();
      var fileIdEl = document.getElementById(button.getAttribute('data-id-el'));

      if (!fileIdEl) {
        console.log('Missing file ID element.');
        return;
      }

      var mediaFrame = wp.media({
        title: eddSlReleases.uploadReleaseFile,
        button: {
          text: eddSlReleases.selectFile
        },
        multiple: false
      });
      mediaFrame.open();
      mediaFrame.on('select', function () {
        var selection = mediaFrame.state().get('selection');
        selection.map(function (attachment) {
          attachment.toJSON();
          console.log('attachment', attachment);

          if (attachment.id) {
            fileIdEl.value = attachment.id;
          }

          if (attachment.attributes && attachment.attributes.filename) {
            var fileNameEl = document.getElementById('edd-sl-releases-file-name');

            if (fileNameEl && !fileNameEl.value) {
              fileNameEl.value = attachment.attributes.filename;
            }
          }
        });
      });
    });
  });
}

/***/ }),

/***/ "./assets/src/js/admin/releases.js":
/*!*****************************************!*\
  !*** ./assets/src/js/admin/releases.js ***!
  \*****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "renderProductReleases": () => (/* binding */ renderProductReleases)
/* harmony export */ });
/* harmony import */ var _utils_api__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../utils/api */ "./assets/src/js/utils/api.js");
/* harmony import */ var _utils_errors__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../utils/errors */ "./assets/src/js/utils/errors.js");
/* global eddSlReleases */


function renderProductReleases() {
  var wrapper = document.getElementById('edd-sl-releases');
  var loading = document.getElementById('edd-sl-releases-loading');
  var noReleases = document.getElementById('edd-sl-releases-none');
  var listReleases = document.getElementById('edd-sl-releases-list');

  if (!wrapper || !listReleases) {
    return;
  }

  var productId = wrapper.getAttribute('data-product');

  if (!productId) {
    return;
  }

  (0,_utils_api__WEBPACK_IMPORTED_MODULE_0__["default"])('products/' + productId + '/releases').then(function (response) {
    if (!response.releases || response.releases.length === 0) {
      if (noReleases) {
        noReleases.classList.remove('hidden');
      }
    } else {
      listReleases.innerHTML = response.releases.map(buildReleaseMarkup).join('');
      listReleases.classList.remove('hidden');
    }
  })["catch"](function (error) {
    console.log('Error fetching releases', error);
    error.json().then(function (response) {
      var errorWrap = document.getElementById('edd-sl-releases-errors');

      if (errorWrap) {
        errorWrap.innerText = (0,_utils_errors__WEBPACK_IMPORTED_MODULE_1__.parseErrorMessage)(response);
        errorWrap.classList.remove('hidden');
      }
    });
  })["finally"](function () {
    if (loading) {
      loading.classList.add('hidden');
    }
  });
}

function buildReleaseMarkup(release) {
  var releaseType = '';

  if (release.pre_release) {
    releaseType = "<span class=\"edd-sl-releases--release--pre-release\">".concat(eddSlReleases.preRelease, "</span>");
  } else {
    releaseType = "<span class=\"edd-sl-releases--release--stable\">".concat(eddSlReleases.stableRelease, "</span>");
  }

  return "\n<div class=\"edd-sl-releases--release\" data-id=\"".concat(release.id, "\">\n    <div class=\"edd-sl-releases--release--header\">\n        <h4>\n            <span class=\"edd-sl-releases--release--version\">").concat(release.version, "</span>\n            ").concat(releaseType, "\n            <span class=\"edd-sl-releases--release--date\">\n                &ndash;\n                ").concat(release.released_at_display, "\n            </span>\n        </h4>\n        <div class=\"edd-sl-releases--release--actions\">\n            <a href=\"").concat(release.edit_url, "\" class=\"button button-secondary\">\n                ").concat(eddSlReleases.edit, "\n            </a>\n        </div>    \n    </div>\n</div>\n    ");
}

/***/ }),

/***/ "./assets/src/js/utils/api.js":
/*!************************************!*\
  !*** ./assets/src/js/utils/api.js ***!
  \************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ apiRequest)
/* harmony export */ });
/* global eddSlReleases */
function apiRequest(endpoint) {
  var method = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'GET';
  var body = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
  var args = {
    method: method,
    credentials: 'same-origin',
    headers: {
      'Content-Type': 'application/json',
      'X-WP-Nonce': eddSlReleases.restNonce
    }
  };

  if (Object.keys(body).length) {
    args.body = JSON.stringify(body);
  }

  return fetch(eddSlReleases.restBase + endpoint, args).then(function (response) {
    if (!response.ok) {
      return Promise.reject(response);
    }

    return response.json();
  });
}

/***/ }),

/***/ "./assets/src/js/utils/errors.js":
/*!***************************************!*\
  !*** ./assets/src/js/utils/errors.js ***!
  \***************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "parseErrorMessage": () => (/* binding */ parseErrorMessage)
/* harmony export */ });
/* global eddSlReleases */
function parseErrorMessage(error) {
  var errorMessage = eddSlReleases.defaultError;

  if (error.message) {
    errorMessage = error.message;
  } else if (error.error) {
    errorMessage = error.error;
  } else if (error.status && error.statusText) {
    errorMessage = error.status + ": " + error.statusText;
  }

  return errorMessage;
}

/***/ }),

/***/ "./assets/src/sass/frontend.scss":
/*!***************************************!*\
  !*** ./assets/src/sass/frontend.scss ***!
  \***************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./assets/src/sass/admin.scss":
/*!************************************!*\
  !*** ./assets/src/sass/admin.scss ***!
  \************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	(() => {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var [chunkIds, fn, priority] = deferred[i];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"/assets/build/js/admin": 0,
/******/ 			"assets/build/css/admin": 0,
/******/ 			"assets/build/css/frontend": 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			var [chunkIds, moreModules, runtime] = data;
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = self["webpackChunkedd_sl_releases"] = self["webpackChunkedd_sl_releases"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	__webpack_require__.O(undefined, ["assets/build/css/admin","assets/build/css/frontend"], () => (__webpack_require__("./assets/src/js/admin.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/build/css/admin","assets/build/css/frontend"], () => (__webpack_require__("./assets/src/sass/frontend.scss")))
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["assets/build/css/admin","assets/build/css/frontend"], () => (__webpack_require__("./assets/src/sass/admin.scss")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;