/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./src/frontend/catalog.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/frontend/catalog.js":
/*!*********************************!*\
  !*** ./src/frontend/catalog.js ***!
  \*********************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("var DEV = !!(\"development\" === 'development'),\n    appNodes = document.querySelectorAll('.shop-catalog-app') || [],\n    catalogEl = document.querySelector('.shop-catalog-items'),\n    dataEl = document.querySelector('.shop-catalog-data'),\n    filtersDataEl = document.querySelector('[data-shop-filters]'),\n    params = new URLSearchParams(location.search.slice(1));\n\nif (appNodes.length) {\n  var filtersData = {};\n\n  if (filtersDataEl) {\n    filtersData = filtersDataEl.dataset.shopFilters;\n    filtersData = JSON.parse(filtersData);\n  }\n\n  var filters = filtersData.filters || {},\n      filtersList = filtersData.list || [],\n      filterForm = {},\n      filterQuery = {};\n\n  for (var f in filtersList) {\n    filterForm[filtersList[f]] = [];\n  }\n\n  DEV && console.log('Фильтры', filtersData);\n  params.forEach(function (val, key) {\n    if (key.includes('filter_')) {\n      val = val.split(',');\n      filterQuery[key] = val;\n      filterForm[key.replace('filter_', '')] = val;\n    }\n\n    if (key.includes('min_')) {\n      filterQuery[key] = val;\n      key = key.replace('min_', '');\n      if (filterForm[key]) filterForm[key][0] = val;\n    }\n\n    if (key.includes('max_')) {\n      filterQuery[key] = val;\n      key = key.replace('max_', '');\n      if (filterForm[key]) filterForm[key][1] = val;\n    }\n  });\n  var total, limit;\n\n  if (catalogEl) {\n    total = dataEl.dataset.total || 0;\n    limit = dataEl.dataset.limit || 1;\n  }\n\n  var data = {\n    total: total,\n    limit: limit,\n    filters: filters,\n    //Значения фильтров\n    filtersList: filtersList,\n    //Список фильтров\n    filterForm: Object.assign({}, filterForm),\n    //Поля для v-model\n    filterQuery: filterQuery,\n    //Поля для запросов\n    isFiltered: !!Object.entries(filterQuery).length,\n    filterSection: '',\n    filterVisible: false,\n    page: +params.get('page') || 1,\n    view: Cookies.get('shop_view') || 'grid',\n    sort: Cookies.get('shop_sort') || ''\n  };\n  var methods = {\n    request: function request(resetPage) {\n      var _this = this;\n\n      var showMore = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;\n      this.isFiltered = !!Object.entries(this.filterQuery).length;\n      catalogEl.classList.add('is-loading');\n      if (resetPage) this.page = 1;\n      var data = {\n        filter: Object.assign({}, this.filterQuery)\n      };\n      if (this.page > 1) data.filter.page = this.page;\n\n      if (params.get('search')) {\n        if (params.get('search')) data.filter.search = params.get('search');\n      } else {\n        data.filter.sort = this.sort;\n      }\n\n      var queryString = new URLSearchParams(data.filter);\n      history.pushState(null, null, window.location.pathname + '?' + queryString);\n      this.$shop.http('catalogSnippet', 'renderCatalog', data).then(function (resp) {\n        DEV && console.log(resp);\n        var newDiv = document.createElement(\"div\");\n        newDiv.innerHTML = resp.html;\n        var itemsNode = newDiv.querySelector('.shop-catalog-items');\n        if (!showMore) catalogEl.innerHTML = \"\";\n        catalogEl.insertAdjacentHTML('beforeend', itemsNode.innerHTML);\n        _this.total = resp.total;\n        catalogEl.classList.remove('is-loading');\n        var event = new CustomEvent('shop-catalog-update', {\n          detail: {\n            el: catalogEl\n          }\n        });\n        document.dispatchEvent(event);\n      });\n    },\n    filter: function filter(name) {\n      var type = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'checkboxes';\n\n      if (type == 'range') {\n        this.filterQuery['min_' + name] = this.filterForm[name][0];\n        this.filterQuery['max_' + name] = this.filterForm[name][1];\n      } else {\n        if (this.filterForm[name].length) {\n          this.filterQuery['filter_' + name] = this.filterForm[name];\n        } else {\n          delete this.filterQuery['filter_' + name];\n        }\n      }\n\n      this.request(true);\n    },\n    sorting: function sorting() {\n      this.request(true);\n      Cookies.set('shop_sort', this.sort);\n    },\n    pagination: function pagination(page) {\n      this.request();\n      window.scrollTo({\n        top: 0,\n        behavior: 'smooth'\n      });\n      this.filterVisible = false;\n    },\n    showMore: function showMore() {\n      if (this.page >= this.pages) return;\n      this.page++;\n      this.request(false, true);\n    },\n    reset: function reset() {\n      //this.isFiltered = false;\n      for (var _f in this.filterForm) {\n        this.filterForm[_f] = [];\n      }\n\n      this.filterQuery = {};\n      this.request(true);\n      this.filterVisible = false;\n    }\n  };\n  var computed = {\n    pages: function pages() {\n      return Math.ceil(this.total / this.limit);\n    }\n  };\n\n  if (appNodes.length) {\n    var ShopCatalogApp = new Vue({\n      data: data,\n      methods: methods,\n      watch: {\n        view: function view(v) {\n          var cl = catalogEl.classList;\n          var classes = catalogEl.className.split(' ');\n          classes.forEach(function (i) {\n            return i.includes('has-view-') && cl.remove(i);\n          });\n          cl.add(\"has-view-\".concat(v));\n          Cookies.set('shop_view', v);\n        }\n      }\n    });\n  }\n\n  appNodes.forEach(function (el) {\n    new Vue({\n      el: el,\n      data: data,\n      methods: methods,\n      computed: computed,\n      delimiters: ['(#', '#)']\n    });\n  });\n}\n\n//# sourceURL=webpack:///./src/frontend/catalog.js?");

/***/ })

/******/ });