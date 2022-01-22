/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./assets/src/js/admin.js":
/*!********************************!*\
  !*** ./assets/src/js/admin.js ***!
  \********************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

__webpack_require__(/*! ./admin/releases */ "./assets/src/js/admin/releases.js");

var _require = __webpack_require__(/*! ./admin/releases */ "./assets/src/js/admin/releases.js"),
    renderProductReleases = _require.renderProductReleases;

document.addEventListener('DOMContentLoaded', function () {
  renderProductReleases();
});

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

  if (!wrapper) {
    return;
  }

  var productId = wrapper.getAttribute('data-product');

  if (!productId) {
    return;
  }

  wrapper.innerHTML = '<p>' + eddSlReleases.loadingReleases + '</p>';
  (0,_utils_api__WEBPACK_IMPORTED_MODULE_0__["default"])('products/' + productId + '/releases').then(function (response) {
    if (!response.releases || !response.releases.length) {
      wrapper.innerHTML = '<p>' + eddSlReleases.noReleases + '</p>';
    } else {
      wrapper.innerHTML = response.releases.map(buildReleaseMarkup).join('');
    }
  })["catch"](function (error) {
    console.log('Error fetching releases', error);
    error.json().then(function (response) {
      wrapper.innerText = (0,_utils_errors__WEBPACK_IMPORTED_MODULE_1__.parseErrorMessage)(response);
    });
  });
}

function buildReleaseMarkup(release) {
  var preRelease = '';

  if (release.pre_release) {
    preRelease = "<span class=\"edd-sl-releases--pre-release\">".concat(eddSlReleases.preRelease, "</span>");
  }

  return "\n<div class=\"edd-sl-releases--release\" data-id=\"".concat(release.id, "\">\n    <div class=\"edd-sl-releases--release--header\">\n        <h4>\n            ").concat(release.version, "\n            ").concat(preRelease, "\n        </h4>\n        <span class=\"edd-sl-releases--release--date\">\n            ").concat(release.created_at_display, "\n        </span>    \n    </div>\n    <div class=\"edd-sl-releases--release--body\">\n        <div class=\"edd-form-group edd-form-group__control\">\n            <label\n                for=\"release-").concat(release.id, "-changelog\"\n                class=\"edd-form-group__label\"\n            >").concat(eddSlReleases.changelog, "</label>\n            <textarea\n                id=\"release-").concat(release.id, "-changelog\"\n                rows=\"5\"\n            >").concat(release.changelog || '', "</textarea>\n        </div>\n    </div>\n</div>\n    ");
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
/******/ 	__webpack_require__.O(undefined, ["assets/build/css/frontend"], () => (__webpack_require__("./assets/src/js/admin.js")))
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["assets/build/css/frontend"], () => (__webpack_require__("./assets/src/sass/frontend.scss")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;