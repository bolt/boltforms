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
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
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
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 1);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/* WEBPACK VAR INJECTION */(function(module) {var __WEBPACK_AMD_DEFINE_FACTORY__, __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

var _typeof2 = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

/*!
 * clipboard.js v2.0.0
 * https://zenorocha.github.io/clipboard.js
 * 
 * Licensed MIT © Zeno Rocha
 */
(function webpackUniversalModuleDefinition(root, factory) {
    if (( false ? 'undefined' : _typeof2(exports)) === 'object' && ( false ? 'undefined' : _typeof2(module)) === 'object') module.exports = factory();else if (true) !(__WEBPACK_AMD_DEFINE_ARRAY__ = [], __WEBPACK_AMD_DEFINE_FACTORY__ = (factory),
				__WEBPACK_AMD_DEFINE_RESULT__ = (typeof __WEBPACK_AMD_DEFINE_FACTORY__ === 'function' ?
				(__WEBPACK_AMD_DEFINE_FACTORY__.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__)) : __WEBPACK_AMD_DEFINE_FACTORY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));else if ((typeof exports === 'undefined' ? 'undefined' : _typeof2(exports)) === 'object') exports["ClipboardJS"] = factory();else root["ClipboardJS"] = factory();
})(undefined, function () {
    return (/******/function (modules) {
            // webpackBootstrap
            /******/ // The module cache
            /******/var installedModules = {};
            /******/
            /******/ // The require function
            /******/function __webpack_require__(moduleId) {
                /******/
                /******/ // Check if module is in cache
                /******/if (installedModules[moduleId]) {
                    /******/return installedModules[moduleId].exports;
                    /******/
                }
                /******/ // Create a new module (and put it into the cache)
                /******/var module = installedModules[moduleId] = {
                    /******/i: moduleId,
                    /******/l: false,
                    /******/exports: {}
                    /******/ };
                /******/
                /******/ // Execute the module function
                /******/modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
                /******/
                /******/ // Flag the module as loaded
                /******/module.l = true;
                /******/
                /******/ // Return the exports of the module
                /******/return module.exports;
                /******/
            }
            /******/
            /******/
            /******/ // expose the modules object (__webpack_modules__)
            /******/__webpack_require__.m = modules;
            /******/
            /******/ // expose the module cache
            /******/__webpack_require__.c = installedModules;
            /******/
            /******/ // identity function for calling harmony imports with the correct context
            /******/__webpack_require__.i = function (value) {
                return value;
            };
            /******/
            /******/ // define getter function for harmony exports
            /******/__webpack_require__.d = function (exports, name, getter) {
                /******/if (!__webpack_require__.o(exports, name)) {
                    /******/Object.defineProperty(exports, name, {
                        /******/configurable: false,
                        /******/enumerable: true,
                        /******/get: getter
                        /******/ });
                    /******/
                }
                /******/
            };
            /******/
            /******/ // getDefaultExport function for compatibility with non-harmony modules
            /******/__webpack_require__.n = function (module) {
                /******/var getter = module && module.__esModule ?
                /******/function getDefault() {
                    return module['default'];
                } :
                /******/function getModuleExports() {
                    return module;
                };
                /******/__webpack_require__.d(getter, 'a', getter);
                /******/return getter;
                /******/
            };
            /******/
            /******/ // Object.prototype.hasOwnProperty.call
            /******/__webpack_require__.o = function (object, property) {
                return Object.prototype.hasOwnProperty.call(object, property);
            };
            /******/
            /******/ // __webpack_public_path__
            /******/__webpack_require__.p = "";
            /******/
            /******/ // Load entry module and return exports
            /******/return __webpack_require__(__webpack_require__.s = 3);
            /******/
        }(
        /************************************************************************/
        /******/[
        /* 0 */
        /***/function (module, exports, __webpack_require__) {

            var __WEBPACK_AMD_DEFINE_FACTORY__, __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;(function (global, factory) {
                if (true) {
                    !(__WEBPACK_AMD_DEFINE_ARRAY__ = [module, __webpack_require__(7)], __WEBPACK_AMD_DEFINE_FACTORY__ = factory, __WEBPACK_AMD_DEFINE_RESULT__ = typeof __WEBPACK_AMD_DEFINE_FACTORY__ === 'function' ? __WEBPACK_AMD_DEFINE_FACTORY__.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__) : __WEBPACK_AMD_DEFINE_FACTORY__, __WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));
                } else if (typeof exports !== "undefined") {
                    factory(module, require('select'));
                } else {
                    var mod = {
                        exports: {}
                    };
                    factory(mod, global.select);
                    global.clipboardAction = mod.exports;
                }
            })(this, function (module, _select) {
                'use strict';

                var _select2 = _interopRequireDefault(_select);

                function _interopRequireDefault(obj) {
                    return obj && obj.__esModule ? obj : {
                        default: obj
                    };
                }

                var _typeof = typeof Symbol === "function" && _typeof2(Symbol.iterator) === "symbol" ? function (obj) {
                    return typeof obj === 'undefined' ? 'undefined' : _typeof2(obj);
                } : function (obj) {
                    return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj === 'undefined' ? 'undefined' : _typeof2(obj);
                };

                function _classCallCheck(instance, Constructor) {
                    if (!(instance instanceof Constructor)) {
                        throw new TypeError("Cannot call a class as a function");
                    }
                }

                var _createClass = function () {
                    function defineProperties(target, props) {
                        for (var i = 0; i < props.length; i++) {
                            var descriptor = props[i];
                            descriptor.enumerable = descriptor.enumerable || false;
                            descriptor.configurable = true;
                            if ("value" in descriptor) descriptor.writable = true;
                            Object.defineProperty(target, descriptor.key, descriptor);
                        }
                    }

                    return function (Constructor, protoProps, staticProps) {
                        if (protoProps) defineProperties(Constructor.prototype, protoProps);
                        if (staticProps) defineProperties(Constructor, staticProps);
                        return Constructor;
                    };
                }();

                var ClipboardAction = function () {
                    /**
                     * @param {Object} options
                     */
                    function ClipboardAction(options) {
                        _classCallCheck(this, ClipboardAction);

                        this.resolveOptions(options);
                        this.initSelection();
                    }

                    /**
                     * Defines base properties passed from constructor.
                     * @param {Object} options
                     */

                    _createClass(ClipboardAction, [{
                        key: 'resolveOptions',
                        value: function resolveOptions() {
                            var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

                            this.action = options.action;
                            this.container = options.container;
                            this.emitter = options.emitter;
                            this.target = options.target;
                            this.text = options.text;
                            this.trigger = options.trigger;

                            this.selectedText = '';
                        }
                    }, {
                        key: 'initSelection',
                        value: function initSelection() {
                            if (this.text) {
                                this.selectFake();
                            } else if (this.target) {
                                this.selectTarget();
                            }
                        }
                    }, {
                        key: 'selectFake',
                        value: function selectFake() {
                            var _this = this;

                            var isRTL = document.documentElement.getAttribute('dir') == 'rtl';

                            this.removeFake();

                            this.fakeHandlerCallback = function () {
                                return _this.removeFake();
                            };
                            this.fakeHandler = this.container.addEventListener('click', this.fakeHandlerCallback) || true;

                            this.fakeElem = document.createElement('textarea');
                            // Prevent zooming on iOS
                            this.fakeElem.style.fontSize = '12pt';
                            // Reset box model
                            this.fakeElem.style.border = '0';
                            this.fakeElem.style.padding = '0';
                            this.fakeElem.style.margin = '0';
                            // Move element out of screen horizontally
                            this.fakeElem.style.position = 'absolute';
                            this.fakeElem.style[isRTL ? 'right' : 'left'] = '-9999px';
                            // Move element to the same position vertically
                            var yPosition = window.pageYOffset || document.documentElement.scrollTop;
                            this.fakeElem.style.top = yPosition + 'px';

                            this.fakeElem.setAttribute('readonly', '');
                            this.fakeElem.value = this.text;

                            this.container.appendChild(this.fakeElem);

                            this.selectedText = (0, _select2.default)(this.fakeElem);
                            this.copyText();
                        }
                    }, {
                        key: 'removeFake',
                        value: function removeFake() {
                            if (this.fakeHandler) {
                                this.container.removeEventListener('click', this.fakeHandlerCallback);
                                this.fakeHandler = null;
                                this.fakeHandlerCallback = null;
                            }

                            if (this.fakeElem) {
                                this.container.removeChild(this.fakeElem);
                                this.fakeElem = null;
                            }
                        }
                    }, {
                        key: 'selectTarget',
                        value: function selectTarget() {
                            this.selectedText = (0, _select2.default)(this.target);
                            this.copyText();
                        }
                    }, {
                        key: 'copyText',
                        value: function copyText() {
                            var succeeded = void 0;

                            try {
                                succeeded = document.execCommand(this.action);
                            } catch (err) {
                                succeeded = false;
                            }

                            this.handleResult(succeeded);
                        }
                    }, {
                        key: 'handleResult',
                        value: function handleResult(succeeded) {
                            this.emitter.emit(succeeded ? 'success' : 'error', {
                                action: this.action,
                                text: this.selectedText,
                                trigger: this.trigger,
                                clearSelection: this.clearSelection.bind(this)
                            });
                        }
                    }, {
                        key: 'clearSelection',
                        value: function clearSelection() {
                            if (this.trigger) {
                                this.trigger.focus();
                            }

                            window.getSelection().removeAllRanges();
                        }
                    }, {
                        key: 'destroy',
                        value: function destroy() {
                            this.removeFake();
                        }
                    }, {
                        key: 'action',
                        set: function set() {
                            var action = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'copy';

                            this._action = action;

                            if (this._action !== 'copy' && this._action !== 'cut') {
                                throw new Error('Invalid "action" value, use either "copy" or "cut"');
                            }
                        },
                        get: function get() {
                            return this._action;
                        }
                    }, {
                        key: 'target',
                        set: function set(target) {
                            if (target !== undefined) {
                                if (target && (typeof target === 'undefined' ? 'undefined' : _typeof(target)) === 'object' && target.nodeType === 1) {
                                    if (this.action === 'copy' && target.hasAttribute('disabled')) {
                                        throw new Error('Invalid "target" attribute. Please use "readonly" instead of "disabled" attribute');
                                    }

                                    if (this.action === 'cut' && (target.hasAttribute('readonly') || target.hasAttribute('disabled'))) {
                                        throw new Error('Invalid "target" attribute. You can\'t cut text from elements with "readonly" or "disabled" attributes');
                                    }

                                    this._target = target;
                                } else {
                                    throw new Error('Invalid "target" value, use a valid Element');
                                }
                            }
                        },
                        get: function get() {
                            return this._target;
                        }
                    }]);

                    return ClipboardAction;
                }();

                module.exports = ClipboardAction;
            });

            /***/
        },
        /* 1 */
        /***/function (module, exports, __webpack_require__) {

            var is = __webpack_require__(6);
            var delegate = __webpack_require__(5);

            /**
             * Validates all params and calls the right
             * listener function based on its target type.
             *
             * @param {String|HTMLElement|HTMLCollection|NodeList} target
             * @param {String} type
             * @param {Function} callback
             * @return {Object}
             */
            function listen(target, type, callback) {
                if (!target && !type && !callback) {
                    throw new Error('Missing required arguments');
                }

                if (!is.string(type)) {
                    throw new TypeError('Second argument must be a String');
                }

                if (!is.fn(callback)) {
                    throw new TypeError('Third argument must be a Function');
                }

                if (is.node(target)) {
                    return listenNode(target, type, callback);
                } else if (is.nodeList(target)) {
                    return listenNodeList(target, type, callback);
                } else if (is.string(target)) {
                    return listenSelector(target, type, callback);
                } else {
                    throw new TypeError('First argument must be a String, HTMLElement, HTMLCollection, or NodeList');
                }
            }

            /**
             * Adds an event listener to a HTML element
             * and returns a remove listener function.
             *
             * @param {HTMLElement} node
             * @param {String} type
             * @param {Function} callback
             * @return {Object}
             */
            function listenNode(node, type, callback) {
                node.addEventListener(type, callback);

                return {
                    destroy: function destroy() {
                        node.removeEventListener(type, callback);
                    }
                };
            }

            /**
             * Add an event listener to a list of HTML elements
             * and returns a remove listener function.
             *
             * @param {NodeList|HTMLCollection} nodeList
             * @param {String} type
             * @param {Function} callback
             * @return {Object}
             */
            function listenNodeList(nodeList, type, callback) {
                Array.prototype.forEach.call(nodeList, function (node) {
                    node.addEventListener(type, callback);
                });

                return {
                    destroy: function destroy() {
                        Array.prototype.forEach.call(nodeList, function (node) {
                            node.removeEventListener(type, callback);
                        });
                    }
                };
            }

            /**
             * Add an event listener to a selector
             * and returns a remove listener function.
             *
             * @param {String} selector
             * @param {String} type
             * @param {Function} callback
             * @return {Object}
             */
            function listenSelector(selector, type, callback) {
                return delegate(document.body, selector, type, callback);
            }

            module.exports = listen;

            /***/
        },
        /* 2 */
        /***/function (module, exports) {

            function E() {
                // Keep this empty so it's easier to inherit from
                // (via https://github.com/lipsmack from https://github.com/scottcorgan/tiny-emitter/issues/3)
            }

            E.prototype = {
                on: function on(name, callback, ctx) {
                    var e = this.e || (this.e = {});

                    (e[name] || (e[name] = [])).push({
                        fn: callback,
                        ctx: ctx
                    });

                    return this;
                },

                once: function once(name, callback, ctx) {
                    var self = this;
                    function listener() {
                        self.off(name, listener);
                        callback.apply(ctx, arguments);
                    };

                    listener._ = callback;
                    return this.on(name, listener, ctx);
                },

                emit: function emit(name) {
                    var data = [].slice.call(arguments, 1);
                    var evtArr = ((this.e || (this.e = {}))[name] || []).slice();
                    var i = 0;
                    var len = evtArr.length;

                    for (i; i < len; i++) {
                        evtArr[i].fn.apply(evtArr[i].ctx, data);
                    }

                    return this;
                },

                off: function off(name, callback) {
                    var e = this.e || (this.e = {});
                    var evts = e[name];
                    var liveEvents = [];

                    if (evts && callback) {
                        for (var i = 0, len = evts.length; i < len; i++) {
                            if (evts[i].fn !== callback && evts[i].fn._ !== callback) liveEvents.push(evts[i]);
                        }
                    }

                    // Remove event from queue to prevent memory leak
                    // Suggested by https://github.com/lazd
                    // Ref: https://github.com/scottcorgan/tiny-emitter/commit/c6ebfaa9bc973b33d110a84a307742b7cf94c953#commitcomment-5024910

                    liveEvents.length ? e[name] = liveEvents : delete e[name];

                    return this;
                }
            };

            module.exports = E;

            /***/
        },
        /* 3 */
        /***/function (module, exports, __webpack_require__) {

            var __WEBPACK_AMD_DEFINE_FACTORY__, __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;(function (global, factory) {
                if (true) {
                    !(__WEBPACK_AMD_DEFINE_ARRAY__ = [module, __webpack_require__(0), __webpack_require__(2), __webpack_require__(1)], __WEBPACK_AMD_DEFINE_FACTORY__ = factory, __WEBPACK_AMD_DEFINE_RESULT__ = typeof __WEBPACK_AMD_DEFINE_FACTORY__ === 'function' ? __WEBPACK_AMD_DEFINE_FACTORY__.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__) : __WEBPACK_AMD_DEFINE_FACTORY__, __WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));
                } else if (typeof exports !== "undefined") {
                    factory(module, require('./clipboard-action'), require('tiny-emitter'), require('good-listener'));
                } else {
                    var mod = {
                        exports: {}
                    };
                    factory(mod, global.clipboardAction, global.tinyEmitter, global.goodListener);
                    global.clipboard = mod.exports;
                }
            })(this, function (module, _clipboardAction, _tinyEmitter, _goodListener) {
                'use strict';

                var _clipboardAction2 = _interopRequireDefault(_clipboardAction);

                var _tinyEmitter2 = _interopRequireDefault(_tinyEmitter);

                var _goodListener2 = _interopRequireDefault(_goodListener);

                function _interopRequireDefault(obj) {
                    return obj && obj.__esModule ? obj : {
                        default: obj
                    };
                }

                var _typeof = typeof Symbol === "function" && _typeof2(Symbol.iterator) === "symbol" ? function (obj) {
                    return typeof obj === 'undefined' ? 'undefined' : _typeof2(obj);
                } : function (obj) {
                    return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj === 'undefined' ? 'undefined' : _typeof2(obj);
                };

                function _classCallCheck(instance, Constructor) {
                    if (!(instance instanceof Constructor)) {
                        throw new TypeError("Cannot call a class as a function");
                    }
                }

                var _createClass = function () {
                    function defineProperties(target, props) {
                        for (var i = 0; i < props.length; i++) {
                            var descriptor = props[i];
                            descriptor.enumerable = descriptor.enumerable || false;
                            descriptor.configurable = true;
                            if ("value" in descriptor) descriptor.writable = true;
                            Object.defineProperty(target, descriptor.key, descriptor);
                        }
                    }

                    return function (Constructor, protoProps, staticProps) {
                        if (protoProps) defineProperties(Constructor.prototype, protoProps);
                        if (staticProps) defineProperties(Constructor, staticProps);
                        return Constructor;
                    };
                }();

                function _possibleConstructorReturn(self, call) {
                    if (!self) {
                        throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
                    }

                    return call && ((typeof call === 'undefined' ? 'undefined' : _typeof2(call)) === "object" || typeof call === "function") ? call : self;
                }

                function _inherits(subClass, superClass) {
                    if (typeof superClass !== "function" && superClass !== null) {
                        throw new TypeError("Super expression must either be null or a function, not " + (typeof superClass === 'undefined' ? 'undefined' : _typeof2(superClass)));
                    }

                    subClass.prototype = Object.create(superClass && superClass.prototype, {
                        constructor: {
                            value: subClass,
                            enumerable: false,
                            writable: true,
                            configurable: true
                        }
                    });
                    if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass;
                }

                var Clipboard = function (_Emitter) {
                    _inherits(Clipboard, _Emitter);

                    /**
                     * @param {String|HTMLElement|HTMLCollection|NodeList} trigger
                     * @param {Object} options
                     */
                    function Clipboard(trigger, options) {
                        _classCallCheck(this, Clipboard);

                        var _this = _possibleConstructorReturn(this, (Clipboard.__proto__ || Object.getPrototypeOf(Clipboard)).call(this));

                        _this.resolveOptions(options);
                        _this.listenClick(trigger);
                        return _this;
                    }

                    /**
                     * Defines if attributes would be resolved using internal setter functions
                     * or custom functions that were passed in the constructor.
                     * @param {Object} options
                     */

                    _createClass(Clipboard, [{
                        key: 'resolveOptions',
                        value: function resolveOptions() {
                            var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

                            this.action = typeof options.action === 'function' ? options.action : this.defaultAction;
                            this.target = typeof options.target === 'function' ? options.target : this.defaultTarget;
                            this.text = typeof options.text === 'function' ? options.text : this.defaultText;
                            this.container = _typeof(options.container) === 'object' ? options.container : document.body;
                        }
                    }, {
                        key: 'listenClick',
                        value: function listenClick(trigger) {
                            var _this2 = this;

                            this.listener = (0, _goodListener2.default)(trigger, 'click', function (e) {
                                return _this2.onClick(e);
                            });
                        }
                    }, {
                        key: 'onClick',
                        value: function onClick(e) {
                            var trigger = e.delegateTarget || e.currentTarget;

                            if (this.clipboardAction) {
                                this.clipboardAction = null;
                            }

                            this.clipboardAction = new _clipboardAction2.default({
                                action: this.action(trigger),
                                target: this.target(trigger),
                                text: this.text(trigger),
                                container: this.container,
                                trigger: trigger,
                                emitter: this
                            });
                        }
                    }, {
                        key: 'defaultAction',
                        value: function defaultAction(trigger) {
                            return getAttributeValue('action', trigger);
                        }
                    }, {
                        key: 'defaultTarget',
                        value: function defaultTarget(trigger) {
                            var selector = getAttributeValue('target', trigger);

                            if (selector) {
                                return document.querySelector(selector);
                            }
                        }
                    }, {
                        key: 'defaultText',
                        value: function defaultText(trigger) {
                            return getAttributeValue('text', trigger);
                        }
                    }, {
                        key: 'destroy',
                        value: function destroy() {
                            this.listener.destroy();

                            if (this.clipboardAction) {
                                this.clipboardAction.destroy();
                                this.clipboardAction = null;
                            }
                        }
                    }], [{
                        key: 'isSupported',
                        value: function isSupported() {
                            var action = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : ['copy', 'cut'];

                            var actions = typeof action === 'string' ? [action] : action;
                            var support = !!document.queryCommandSupported;

                            actions.forEach(function (action) {
                                support = support && !!document.queryCommandSupported(action);
                            });

                            return support;
                        }
                    }]);

                    return Clipboard;
                }(_tinyEmitter2.default);

                /**
                 * Helper function to retrieve attribute value.
                 * @param {String} suffix
                 * @param {Element} element
                 */
                function getAttributeValue(suffix, element) {
                    var attribute = 'data-clipboard-' + suffix;

                    if (!element.hasAttribute(attribute)) {
                        return;
                    }

                    return element.getAttribute(attribute);
                }

                module.exports = Clipboard;
            });

            /***/
        },
        /* 4 */
        /***/function (module, exports) {

            var DOCUMENT_NODE_TYPE = 9;

            /**
             * A polyfill for Element.matches()
             */
            if (typeof Element !== 'undefined' && !Element.prototype.matches) {
                var proto = Element.prototype;

                proto.matches = proto.matchesSelector || proto.mozMatchesSelector || proto.msMatchesSelector || proto.oMatchesSelector || proto.webkitMatchesSelector;
            }

            /**
             * Finds the closest parent that matches a selector.
             *
             * @param {Element} element
             * @param {String} selector
             * @return {Function}
             */
            function closest(element, selector) {
                while (element && element.nodeType !== DOCUMENT_NODE_TYPE) {
                    if (typeof element.matches === 'function' && element.matches(selector)) {
                        return element;
                    }
                    element = element.parentNode;
                }
            }

            module.exports = closest;

            /***/
        },
        /* 5 */
        /***/function (module, exports, __webpack_require__) {

            var closest = __webpack_require__(4);

            /**
             * Delegates event to a selector.
             *
             * @param {Element} element
             * @param {String} selector
             * @param {String} type
             * @param {Function} callback
             * @param {Boolean} useCapture
             * @return {Object}
             */
            function _delegate(element, selector, type, callback, useCapture) {
                var listenerFn = listener.apply(this, arguments);

                element.addEventListener(type, listenerFn, useCapture);

                return {
                    destroy: function destroy() {
                        element.removeEventListener(type, listenerFn, useCapture);
                    }
                };
            }

            /**
             * Delegates event to a selector.
             *
             * @param {Element|String|Array} [elements]
             * @param {String} selector
             * @param {String} type
             * @param {Function} callback
             * @param {Boolean} useCapture
             * @return {Object}
             */
            function delegate(elements, selector, type, callback, useCapture) {
                // Handle the regular Element usage
                if (typeof elements.addEventListener === 'function') {
                    return _delegate.apply(null, arguments);
                }

                // Handle Element-less usage, it defaults to global delegation
                if (typeof type === 'function') {
                    // Use `document` as the first parameter, then apply arguments
                    // This is a short way to .unshift `arguments` without running into deoptimizations
                    return _delegate.bind(null, document).apply(null, arguments);
                }

                // Handle Selector-based usage
                if (typeof elements === 'string') {
                    elements = document.querySelectorAll(elements);
                }

                // Handle Array-like based usage
                return Array.prototype.map.call(elements, function (element) {
                    return _delegate(element, selector, type, callback, useCapture);
                });
            }

            /**
             * Finds closest match and invokes callback.
             *
             * @param {Element} element
             * @param {String} selector
             * @param {String} type
             * @param {Function} callback
             * @return {Function}
             */
            function listener(element, selector, type, callback) {
                return function (e) {
                    e.delegateTarget = closest(e.target, selector);

                    if (e.delegateTarget) {
                        callback.call(element, e);
                    }
                };
            }

            module.exports = delegate;

            /***/
        },
        /* 6 */
        /***/function (module, exports) {

            /**
             * Check if argument is a HTML element.
             *
             * @param {Object} value
             * @return {Boolean}
             */
            exports.node = function (value) {
                return value !== undefined && value instanceof HTMLElement && value.nodeType === 1;
            };

            /**
             * Check if argument is a list of HTML elements.
             *
             * @param {Object} value
             * @return {Boolean}
             */
            exports.nodeList = function (value) {
                var type = Object.prototype.toString.call(value);

                return value !== undefined && (type === '[object NodeList]' || type === '[object HTMLCollection]') && 'length' in value && (value.length === 0 || exports.node(value[0]));
            };

            /**
             * Check if argument is a string.
             *
             * @param {Object} value
             * @return {Boolean}
             */
            exports.string = function (value) {
                return typeof value === 'string' || value instanceof String;
            };

            /**
             * Check if argument is a function.
             *
             * @param {Object} value
             * @return {Boolean}
             */
            exports.fn = function (value) {
                var type = Object.prototype.toString.call(value);

                return type === '[object Function]';
            };

            /***/
        },
        /* 7 */
        /***/function (module, exports) {

            function select(element) {
                var selectedText;

                if (element.nodeName === 'SELECT') {
                    element.focus();

                    selectedText = element.value;
                } else if (element.nodeName === 'INPUT' || element.nodeName === 'TEXTAREA') {
                    var isReadOnly = element.hasAttribute('readonly');

                    if (!isReadOnly) {
                        element.setAttribute('readonly', '');
                    }

                    element.select();
                    element.setSelectionRange(0, element.value.length);

                    if (!isReadOnly) {
                        element.removeAttribute('readonly');
                    }

                    selectedText = element.value;
                } else {
                    if (element.hasAttribute('contenteditable')) {
                        element.focus();
                    }

                    var selection = window.getSelection();
                    var range = document.createRange();

                    range.selectNodeContents(element);
                    selection.removeAllRanges();
                    selection.addRange(range);

                    selectedText = selection.toString();
                }

                return selectedText;
            }

            module.exports = select;

            /***/
        }]
        /******/)
    );
});
/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(4)(module)))

/***/ }),
/* 1 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


__webpack_require__(2);

__webpack_require__(5);

//((===  JS Entry Point  ===))//


//=== Function Imports

$('.button-bars').click(function () {
    $('.docs__sidebar--menu').slideToggle();
});

$('.button-search').click(function () {
    $('.docs__sidebar--search').slideToggle();
});

$('h2').each(function (i, el) {
    var $el, icon, id;
    $el = $(el);
    id = $el.attr('id');
    icon = '¶';
    if (id) {
        return $el.append($("<a />").addClass("anchor").attr("href", "#" + id).html(icon));
    }
});

/***/ }),
/* 2 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/* WEBPACK VAR INJECTION */(function(global) {

//((=== Syntax Highlight  ===))//

/* https://cdnjs.cloudflare.com/ajax/libs/prism/1.14.0/prism.min.js */
var _self = "undefined" != typeof window ? window : "undefined" != typeof WorkerGlobalScope && self instanceof WorkerGlobalScope ? self : {},
    Prism = function () {
    var o = /\blang(?:uage)?-([\w-]+)\b/i,
        t = 0,
        O = _self.Prism = { manual: _self.Prism && _self.Prism.manual, disableWorkerMessageHandler: _self.Prism && _self.Prism.disableWorkerMessageHandler, util: { encode: function encode(e) {
                return e instanceof s ? new s(e.type, O.util.encode(e.content), e.alias) : "Array" === O.util.type(e) ? e.map(O.util.encode) : e.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/\u00a0/g, " ");
            }, type: function type(e) {
                return Object.prototype.toString.call(e).match(/\[object (\w+)\]/)[1];
            }, objId: function objId(e) {
                return e.__id || Object.defineProperty(e, "__id", { value: ++t }), e.__id;
            }, clone: function clone(e, a) {
                var t = O.util.type(e);switch (a = a || {}, t) {case "Object":
                        if (a[O.util.objId(e)]) return a[O.util.objId(e)];var n = {};for (var r in a[O.util.objId(e)] = n, e) {
                            e.hasOwnProperty(r) && (n[r] = O.util.clone(e[r], a));
                        }return n;case "Array":
                        if (a[O.util.objId(e)]) return a[O.util.objId(e)];n = [];return a[O.util.objId(e)] = n, e.forEach(function (e, t) {
                            n[t] = O.util.clone(e, a);
                        }), n;}return e;
            } }, languages: { extend: function extend(e, t) {
                var a = O.util.clone(O.languages[e]);for (var n in t) {
                    a[n] = t[n];
                }return a;
            }, insertBefore: function insertBefore(a, e, t, n) {
                var r = (n = n || O.languages)[a];if (2 == arguments.length) {
                    for (var i in t = e) {
                        t.hasOwnProperty(i) && (r[i] = t[i]);
                    }return r;
                }var s = {};for (var l in r) {
                    if (r.hasOwnProperty(l)) {
                        if (l == e) for (var i in t) {
                            t.hasOwnProperty(i) && (s[i] = t[i]);
                        }s[l] = r[l];
                    }
                }return O.languages.DFS(O.languages, function (e, t) {
                    t === n[a] && e != a && (this[e] = s);
                }), n[a] = s;
            }, DFS: function DFS(e, t, a, n) {
                for (var r in n = n || {}, e) {
                    e.hasOwnProperty(r) && (t.call(e, r, e[r], a || r), "Object" !== O.util.type(e[r]) || n[O.util.objId(e[r])] ? "Array" !== O.util.type(e[r]) || n[O.util.objId(e[r])] || (n[O.util.objId(e[r])] = !0, O.languages.DFS(e[r], t, r, n)) : (n[O.util.objId(e[r])] = !0, O.languages.DFS(e[r], t, null, n)));
                }
            } }, plugins: {}, highlightAll: function highlightAll(e, t) {
            O.highlightAllUnder(document, e, t);
        }, highlightAllUnder: function highlightAllUnder(e, t, a) {
            var n = { callback: a, selector: 'code[class*="language-"], [class*="language-"] code, code[class*="lang-"], [class*="lang-"] code' };O.hooks.run("before-highlightall", n);for (var r, i = n.elements || e.querySelectorAll(n.selector), s = 0; r = i[s++];) {
                O.highlightElement(r, !0 === t, n.callback);
            }
        }, highlightElement: function highlightElement(e, t, a) {
            for (var n, r, i = e; i && !o.test(i.className);) {
                i = i.parentNode;
            }i && (n = (i.className.match(o) || [, ""])[1].toLowerCase(), r = O.languages[n]), e.className = e.className.replace(o, "").replace(/\s+/g, " ") + " language-" + n, e.parentNode && (i = e.parentNode, /pre/i.test(i.nodeName) && (i.className = i.className.replace(o, "").replace(/\s+/g, " ") + " language-" + n));var s = { element: e, language: n, grammar: r, code: e.textContent };if (O.hooks.run("before-sanity-check", s), !s.code || !s.grammar) return s.code && (O.hooks.run("before-highlight", s), s.element.textContent = s.code, O.hooks.run("after-highlight", s)), void O.hooks.run("complete", s);if (O.hooks.run("before-highlight", s), t && _self.Worker) {
                var l = new Worker(O.filename);l.onmessage = function (e) {
                    s.highlightedCode = e.data, O.hooks.run("before-insert", s), s.element.innerHTML = s.highlightedCode, a && a.call(s.element), O.hooks.run("after-highlight", s), O.hooks.run("complete", s);
                }, l.postMessage(JSON.stringify({ language: s.language, code: s.code, immediateClose: !0 }));
            } else s.highlightedCode = O.highlight(s.code, s.grammar, s.language), O.hooks.run("before-insert", s), s.element.innerHTML = s.highlightedCode, a && a.call(e), O.hooks.run("after-highlight", s), O.hooks.run("complete", s);
        }, highlight: function highlight(e, t, a) {
            var n = { code: e, grammar: t, language: a };return O.hooks.run("before-tokenize", n), n.tokens = O.tokenize(n.code, n.grammar), O.hooks.run("after-tokenize", n), s.stringify(O.util.encode(n.tokens), n.language);
        }, matchGrammar: function matchGrammar(e, t, a, n, r, i, s) {
            var l = O.Token;for (var o in a) {
                if (a.hasOwnProperty(o) && a[o]) {
                    if (o == s) return;var u = a[o];u = "Array" === O.util.type(u) ? u : [u];for (var g = 0; g < u.length; ++g) {
                        var c = u[g],
                            d = c.inside,
                            p = !!c.lookbehind,
                            m = !!c.greedy,
                            h = 0,
                            f = c.alias;if (m && !c.pattern.global) {
                            var y = c.pattern.toString().match(/[imuy]*$/)[0];c.pattern = RegExp(c.pattern.source, y + "g");
                        }c = c.pattern || c;for (var b = n, k = r; b < t.length; k += t[b].length, ++b) {
                            var v = t[b];if (t.length > e.length) return;if (!(v instanceof l)) {
                                if (m && b != t.length - 1) {
                                    if (c.lastIndex = k, !(S = c.exec(e))) break;for (var P = S.index + (p ? S[1].length : 0), w = S.index + S[0].length, F = b, x = k, A = t.length; F < A && (x < w || !t[F].type && !t[F - 1].greedy); ++F) {
                                        (x += t[F].length) <= P && (++b, k = x);
                                    }if (t[b] instanceof l) continue;j = F - b, v = e.slice(k, x), S.index -= k;
                                } else {
                                    c.lastIndex = 0;var S = c.exec(v),
                                        j = 1;
                                }if (S) {
                                    p && (h = S[1] ? S[1].length : 0);w = (P = S.index + h) + (S = S[0].slice(h)).length;var _ = v.slice(0, P),
                                        C = v.slice(w),
                                        N = [b, j];_ && (++b, k += _.length, N.push(_));var E = new l(o, d ? O.tokenize(S, d) : S, f, S, m);if (N.push(E), C && N.push(C), Array.prototype.splice.apply(t, N), 1 != j && O.matchGrammar(e, t, a, b, k, !0, o), i) break;
                                } else if (i) break;
                            }
                        }
                    }
                }
            }
        }, tokenize: function tokenize(e, t, a) {
            var n = [e],
                r = t.rest;if (r) {
                for (var i in r) {
                    t[i] = r[i];
                }delete t.rest;
            }return O.matchGrammar(e, n, t, 0, 0, !1), n;
        }, hooks: { all: {}, add: function add(e, t) {
                var a = O.hooks.all;a[e] = a[e] || [], a[e].push(t);
            }, run: function run(e, t) {
                var a = O.hooks.all[e];if (a && a.length) for (var n, r = 0; n = a[r++];) {
                    n(t);
                }
            } } },
        s = O.Token = function (e, t, a, n, r) {
        this.type = e, this.content = t, this.alias = a, this.length = 0 | (n || "").length, this.greedy = !!r;
    };if (s.stringify = function (t, a, e) {
        if ("string" == typeof t) return t;if ("Array" === O.util.type(t)) return t.map(function (e) {
            return s.stringify(e, a, t);
        }).join("");var n = { type: t.type, content: s.stringify(t.content, a, e), tag: "span", classes: ["token", t.type], attributes: {}, language: a, parent: e };if (t.alias) {
            var r = "Array" === O.util.type(t.alias) ? t.alias : [t.alias];Array.prototype.push.apply(n.classes, r);
        }O.hooks.run("wrap", n);var i = Object.keys(n.attributes).map(function (e) {
            return e + '="' + (n.attributes[e] || "").replace(/"/g, "&quot;") + '"';
        }).join(" ");return "<" + n.tag + ' class="' + n.classes.join(" ") + '"' + (i ? " " + i : "") + ">" + n.content + "</" + n.tag + ">";
    }, !_self.document) return _self.addEventListener && (O.disableWorkerMessageHandler || _self.addEventListener("message", function (e) {
        var t = JSON.parse(e.data),
            a = t.language,
            n = t.code,
            r = t.immediateClose;_self.postMessage(O.highlight(n, O.languages[a], a)), r && _self.close();
    }, !1)), _self.Prism;var e = document.currentScript || [].slice.call(document.getElementsByTagName("script")).pop();return e && (O.filename = e.src, O.manual || e.hasAttribute("data-manual") || ("loading" !== document.readyState ? window.requestAnimationFrame ? window.requestAnimationFrame(O.highlightAll) : window.setTimeout(O.highlightAll, 16) : document.addEventListener("DOMContentLoaded", O.highlightAll))), _self.Prism;
}();"undefined" != typeof module && module.exports && (module.exports = Prism), "undefined" != typeof global && (global.Prism = Prism), Prism.languages.markup = { comment: /<!--[\s\S]*?-->/, prolog: /<\?[\s\S]+?\?>/, doctype: /<!DOCTYPE[\s\S]+?>/i, cdata: /<!\[CDATA\[[\s\S]*?]]>/i, tag: { pattern: /<\/?(?!\d)[^\s>\/=$<%]+(?:\s+[^\s>\/=]+(?:=(?:("|')(?:\\[\s\S]|(?!\1)[^\\])*\1|[^\s'">=]+))?)*\s*\/?>/i, greedy: !0, inside: { tag: { pattern: /^<\/?[^\s>\/]+/i, inside: { punctuation: /^<\/?/, namespace: /^[^\s>\/:]+:/ } }, "attr-value": { pattern: /=(?:("|')(?:\\[\s\S]|(?!\1)[^\\])*\1|[^\s'">=]+)/i, inside: { punctuation: [/^=/, { pattern: /(^|[^\\])["']/, lookbehind: !0 }] } }, punctuation: /\/?>/, "attr-name": { pattern: /[^\s>\/]+/, inside: { namespace: /^[^\s>\/:]+:/ } } } }, entity: /&#?[\da-z]{1,8};/i }, Prism.languages.markup.tag.inside["attr-value"].inside.entity = Prism.languages.markup.entity, Prism.hooks.add("wrap", function (e) {
    "entity" === e.type && (e.attributes.title = e.content.replace(/&amp;/, "&"));
}), Prism.languages.xml = Prism.languages.markup, Prism.languages.html = Prism.languages.markup, Prism.languages.mathml = Prism.languages.markup, Prism.languages.svg = Prism.languages.markup, Prism.languages.css = { comment: /\/\*[\s\S]*?\*\//, atrule: { pattern: /@[\w-]+?.*?(?:;|(?=\s*\{))/i, inside: { rule: /@[\w-]+/ } }, url: /url\((?:(["'])(?:\\(?:\r\n|[\s\S])|(?!\1)[^\\\r\n])*\1|.*?)\)/i, selector: /[^{}\s][^{};]*?(?=\s*\{)/, string: { pattern: /("|')(?:\\(?:\r\n|[\s\S])|(?!\1)[^\\\r\n])*\1/, greedy: !0 }, property: /[-_a-z\xA0-\uFFFF][-\w\xA0-\uFFFF]*(?=\s*:)/i, important: /\B!important\b/i, function: /[-a-z0-9]+(?=\()/i, punctuation: /[(){};:]/ }, Prism.languages.css.atrule.inside.rest = Prism.languages.css, Prism.languages.markup && (Prism.languages.insertBefore("markup", "tag", { style: { pattern: /(<style[\s\S]*?>)[\s\S]*?(?=<\/style>)/i, lookbehind: !0, inside: Prism.languages.css, alias: "language-css", greedy: !0 } }), Prism.languages.insertBefore("inside", "attr-value", { "style-attr": { pattern: /\s*style=("|')(?:\\[\s\S]|(?!\1)[^\\])*\1/i, inside: { "attr-name": { pattern: /^\s*style/i, inside: Prism.languages.markup.tag.inside }, punctuation: /^\s*=\s*['"]|['"]\s*$/, "attr-value": { pattern: /.+/i, inside: Prism.languages.css } }, alias: "language-css" } }, Prism.languages.markup.tag)), Prism.languages.clike = { comment: [{ pattern: /(^|[^\\])\/\*[\s\S]*?(?:\*\/|$)/, lookbehind: !0 }, { pattern: /(^|[^\\:])\/\/.*/, lookbehind: !0, greedy: !0 }], string: { pattern: /(["'])(?:\\(?:\r\n|[\s\S])|(?!\1)[^\\\r\n])*\1/, greedy: !0 }, "class-name": { pattern: /((?:\b(?:class|interface|extends|implements|trait|instanceof|new)\s+)|(?:catch\s+\())[\w.\\]+/i, lookbehind: !0, inside: { punctuation: /[.\\]/ } }, keyword: /\b(?:if|else|while|do|for|return|in|instanceof|function|new|try|throw|catch|finally|null|break|continue)\b/, boolean: /\b(?:true|false)\b/, function: /[a-z0-9_]+(?=\()/i, number: /\b0x[\da-f]+\b|(?:\b\d+\.?\d*|\B\.\d+)(?:e[+-]?\d+)?/i, operator: /--?|\+\+?|!=?=?|<=?|>=?|==?=?|&&?|\|\|?|\?|\*|\/|~|\^|%/, punctuation: /[{}[\];(),.:]/ }, Prism.languages.javascript = Prism.languages.extend("clike", { keyword: /\b(?:as|async|await|break|case|catch|class|const|continue|debugger|default|delete|do|else|enum|export|extends|finally|for|from|function|get|if|implements|import|in|instanceof|interface|let|new|null|of|package|private|protected|public|return|set|static|super|switch|this|throw|try|typeof|var|void|while|with|yield)\b/, number: /\b(?:0[xX][\dA-Fa-f]+|0[bB][01]+|0[oO][0-7]+|NaN|Infinity)\b|(?:\b\d+\.?\d*|\B\.\d+)(?:[Ee][+-]?\d+)?/, function: /[_$a-z\xA0-\uFFFF][$\w\xA0-\uFFFF]*(?=\s*\()/i, operator: /-[-=]?|\+[+=]?|!=?=?|<<?=?|>>?>?=?|=(?:==?|>)?|&[&=]?|\|[|=]?|\*\*?=?|\/=?|~|\^=?|%=?|\?|\.{3}/ }), Prism.languages.insertBefore("javascript", "keyword", { regex: { pattern: /((?:^|[^$\w\xA0-\uFFFF."'\])\s])\s*)\/(\[[^\]\r\n]+]|\\.|[^/\\\[\r\n])+\/[gimyu]{0,5}(?=\s*($|[\r\n,.;})]))/, lookbehind: !0, greedy: !0 }, "function-variable": { pattern: /[_$a-z\xA0-\uFFFF][$\w\xA0-\uFFFF]*(?=\s*=\s*(?:function\b|(?:\([^()]*\)|[_$a-z\xA0-\uFFFF][$\w\xA0-\uFFFF]*)\s*=>))/i, alias: "function" }, constant: /\b[A-Z][A-Z\d_]*\b/ }), Prism.languages.insertBefore("javascript", "string", { "template-string": { pattern: /`(?:\\[\s\S]|[^\\`])*`/, greedy: !0, inside: { interpolation: { pattern: /\$\{[^}]+\}/, inside: { "interpolation-punctuation": { pattern: /^\$\{|\}$/, alias: "punctuation" }, rest: Prism.languages.javascript } }, string: /[\s\S]+/ } } }), Prism.languages.markup && Prism.languages.insertBefore("markup", "tag", { script: { pattern: /(<script[\s\S]*?>)[\s\S]*?(?=<\/script>)/i, lookbehind: !0, inside: Prism.languages.javascript, alias: "language-javascript", greedy: !0 } }), Prism.languages.js = Prism.languages.javascript, "undefined" != typeof self && self.Prism && self.document && document.querySelector && (self.Prism.fileHighlight = function () {
    var o = { js: "javascript", py: "python", rb: "ruby", ps1: "powershell", psm1: "powershell", sh: "bash", bat: "batch", h: "c", tex: "latex" };Array.prototype.slice.call(document.querySelectorAll("pre[data-src]")).forEach(function (t) {
        for (var e, a = t.getAttribute("data-src"), n = t, r = /\blang(?:uage)?-(?!\*)([\w-]+)\b/i; n && !r.test(n.className);) {
            n = n.parentNode;
        }if (n && (e = (t.className.match(r) || [, ""])[1]), !e) {
            var i = (a.match(/\.(\w+)$/) || [, ""])[1];e = o[i] || i;
        }var s = document.createElement("code");s.className = "language-" + e, t.textContent = "", s.textContent = "Loading…", t.appendChild(s);var l = new XMLHttpRequest();l.open("GET", a, !0), l.onreadystatechange = function () {
            4 == l.readyState && (l.status < 400 && l.responseText ? (s.textContent = l.responseText, Prism.highlightElement(s)) : 400 <= l.status ? s.textContent = "✖ Error " + l.status + " while fetching file: " + l.statusText : s.textContent = "✖ Error: File does not exist or is empty");
        }, t.hasAttribute("data-download-link") && Prism.plugins.toolbar && Prism.plugins.toolbar.registerButton("download-file", function () {
            var e = document.createElement("a");return e.textContent = t.getAttribute("data-download-link-label") || "Download", e.setAttribute("download", ""), e.href = a, e;
        }), l.send(null);
    });
}, document.addEventListener("DOMContentLoaded", self.Prism.fileHighlight));

/* https://cdnjs.cloudflare.com/ajax/libs/prism/1.14.0/components/prism-markup-templating.min.js */
Prism.languages["markup-templating"] = {}, Object.defineProperties(Prism.languages["markup-templating"], { buildPlaceholders: { value: function value(e, t, n, a) {
            e.language === t && (e.tokenStack = [], e.code = e.code.replace(n, function (n) {
                if ("function" == typeof a && !a(n)) return n;for (var r = e.tokenStack.length; -1 !== e.code.indexOf("___" + t.toUpperCase() + r + "___");) {
                    ++r;
                }return e.tokenStack[r] = n, "___" + t.toUpperCase() + r + "___";
            }), e.grammar = Prism.languages.markup);
        } }, tokenizePlaceholders: { value: function value(e, t) {
            if (e.language === t && e.tokenStack) {
                e.grammar = Prism.languages[t];var n = 0,
                    a = Object.keys(e.tokenStack),
                    r = function r(o) {
                    if (!(n >= a.length)) for (var i = 0; i < o.length; i++) {
                        var g = o[i];if ("string" == typeof g || g.content && "string" == typeof g.content) {
                            var c = a[n],
                                s = e.tokenStack[c],
                                l = "string" == typeof g ? g : g.content,
                                p = l.indexOf("___" + t.toUpperCase() + c + "___");if (p > -1) {
                                ++n;var f,
                                    u = l.substring(0, p),
                                    _ = new Prism.Token(t, Prism.tokenize(s, e.grammar, t), "language-" + t, s),
                                    k = l.substring(p + ("___" + t.toUpperCase() + c + "___").length);if (u || k ? (f = [u, _, k].filter(function (e) {
                                    return !!e;
                                }), r(f)) : f = _, "string" == typeof g ? Array.prototype.splice.apply(o, [i, 1].concat(f)) : g.content = f, n >= a.length) break;
                            }
                        } else g.content && "string" != typeof g.content && r(g.content);
                    }
                };r(e.tokens);
            }
        } } });

/* https://cdnjs.cloudflare.com/ajax/libs/prism/1.14.0/components/prism-php.min.js */
!function (e) {
    e.languages.php = e.languages.extend("clike", { keyword: /\b(?:and|or|xor|array|as|break|case|cfunction|class|const|continue|declare|default|die|do|else|elseif|enddeclare|endfor|endforeach|endif|endswitch|endwhile|extends|for|foreach|function|include|include_once|global|if|new|return|static|switch|use|require|require_once|var|while|abstract|interface|public|implements|private|protected|parent|throw|null|echo|print|trait|namespace|final|yield|goto|instanceof|finally|try|catch)\b/i, constant: /\b[A-Z0-9_]{2,}\b/, comment: { pattern: /(^|[^\\])(?:\/\*[\s\S]*?\*\/|\/\/.*)/, lookbehind: !0 } }), e.languages.insertBefore("php", "string", { "shell-comment": { pattern: /(^|[^\\])#.*/, lookbehind: !0, alias: "comment" } }), e.languages.insertBefore("php", "keyword", { delimiter: { pattern: /\?>|<\?(?:php|=)?/i, alias: "important" }, variable: /\$+(?:\w+\b|(?={))/i, "package": { pattern: /(\\|namespace\s+|use\s+)[\w\\]+/, lookbehind: !0, inside: { punctuation: /\\/ } } }), e.languages.insertBefore("php", "operator", { property: { pattern: /(->)[\w]+/, lookbehind: !0 } }), e.languages.insertBefore("php", "string", { "nowdoc-string": { pattern: /<<<'([^']+)'(?:\r\n?|\n)(?:.*(?:\r\n?|\n))*?\1;/, greedy: !0, alias: "string", inside: { delimiter: { pattern: /^<<<'[^']+'|[a-z_]\w*;$/i, alias: "symbol", inside: { punctuation: /^<<<'?|[';]$/ } } } }, "heredoc-string": { pattern: /<<<(?:"([^"]+)"(?:\r\n?|\n)(?:.*(?:\r\n?|\n))*?\1;|([a-z_]\w*)(?:\r\n?|\n)(?:.*(?:\r\n?|\n))*?\2;)/i, greedy: !0, alias: "string", inside: { delimiter: { pattern: /^<<<(?:"[^"]+"|[a-z_]\w*)|[a-z_]\w*;$/i, alias: "symbol", inside: { punctuation: /^<<<"?|[";]$/ } }, interpolation: null } }, "single-quoted-string": { pattern: /'(?:\\[\s\S]|[^\\'])*'/, greedy: !0, alias: "string" }, "double-quoted-string": { pattern: /"(?:\\[\s\S]|[^\\"])*"/, greedy: !0, alias: "string", inside: { interpolation: null } } }), delete e.languages.php.string;var n = { pattern: /{\$(?:{(?:{[^{}]+}|[^{}]+)}|[^{}])+}|(^|[^\\{])\$+(?:\w+(?:\[.+?]|->\w+)*)/, lookbehind: !0, inside: { rest: e.languages.php } };e.languages.php["heredoc-string"].inside.interpolation = n, e.languages.php["double-quoted-string"].inside.interpolation = n, e.hooks.add("before-tokenize", function (n) {
        if (/(?:<\?php|<\?)/gi.test(n.code)) {
            var i = /(?:<\?php|<\?)[\s\S]*?(?:\?>|$)/gi;e.languages["markup-templating"].buildPlaceholders(n, "php", i);
        }
    }), e.hooks.add("after-tokenize", function (n) {
        e.languages["markup-templating"].tokenizePlaceholders(n, "php");
    });
}(Prism);

/* https://cdnjs.cloudflare.com/ajax/libs/prism/1.14.0/components/prism-json.min.js */
Prism.languages.json = { property: /"(?:\\.|[^\\"\r\n])*"(?=\s*:)/i, string: { pattern: /"(?:\\.|[^\\"\r\n])*"(?!\s*:)/, greedy: !0 }, number: /\b0x[\dA-Fa-f]+\b|(?:\b\d+\.?\d*|\B\.\d+)(?:[Ee][+-]?\d+)?/, punctuation: /[{}[\]);,]/, operator: /:/g, "boolean": /\b(?:true|false)\b/i, "null": /\bnull\b/i }, Prism.languages.jsonp = Prism.languages.json;

/* https://cdnjs.cloudflare.com/ajax/libs/prism/1.14.0/components/prism-yaml.min.js */
Prism.languages.yaml = { scalar: { pattern: /([\-:]\s*(?:![^\s]+)?[ \t]*[|>])[ \t]*(?:((?:\r?\n|\r)[ \t]+)[^\r\n]+(?:\2[^\r\n]+)*)/, lookbehind: !0, alias: "string" }, comment: /#.*/, key: { pattern: /(\s*(?:^|[:\-,[{\r\n?])[ \t]*(?:![^\s]+)?[ \t]*)[^\r\n{[\]},#\s]+?(?=\s*:\s)/, lookbehind: !0, alias: "atrule" }, directive: { pattern: /(^[ \t]*)%.+/m, lookbehind: !0, alias: "important" }, datetime: { pattern: /([:\-,[{]\s*(?:![^\s]+)?[ \t]*)(?:\d{4}-\d\d?-\d\d?(?:[tT]|[ \t]+)\d\d?:\d{2}:\d{2}(?:\.\d*)?[ \t]*(?:Z|[-+]\d\d?(?::\d{2})?)?|\d{4}-\d{2}-\d{2}|\d\d?:\d{2}(?::\d{2}(?:\.\d*)?)?)(?=[ \t]*(?:$|,|]|}))/m, lookbehind: !0, alias: "number" }, "boolean": { pattern: /([:\-,[{]\s*(?:![^\s]+)?[ \t]*)(?:true|false)[ \t]*(?=$|,|]|})/im, lookbehind: !0, alias: "important" }, "null": { pattern: /([:\-,[{]\s*(?:![^\s]+)?[ \t]*)(?:null|~)[ \t]*(?=$|,|]|})/im, lookbehind: !0, alias: "important" }, string: { pattern: /([:\-,[{]\s*(?:![^\s]+)?[ \t]*)("|')(?:(?!\2)[^\\\r\n]|\\.)*\2(?=[ \t]*(?:$|,|]|}))/m, lookbehind: !0, greedy: !0 }, number: { pattern: /([:\-,[{]\s*(?:![^\s]+)?[ \t]*)[+-]?(?:0x[\da-f]+|0o[0-7]+|(?:\d+\.?\d*|\.?\d+)(?:e[+-]?\d+)?|\.inf|\.nan)[ \t]*(?=$|,|]|})/im, lookbehind: !0 }, tag: /![^\s]+/, important: /[&*][\w]+/, punctuation: /---|[:[\]{}\-,|>?]|\.\.\./ };

/* https://cdnjs.cloudflare.com/ajax/libs/prism/1.14.0/components/prism-bash.min.js */
!function (e) {
    var t = { variable: [{ pattern: /\$?\(\([\s\S]+?\)\)/, inside: { variable: [{ pattern: /(^\$\(\([\s\S]+)\)\)/, lookbehind: !0 }, /^\$\(\(/], number: /\b0x[\dA-Fa-f]+\b|(?:\b\d+\.?\d*|\B\.\d+)(?:[Ee]-?\d+)?/, operator: /--?|-=|\+\+?|\+=|!=?|~|\*\*?|\*=|\/=?|%=?|<<=?|>>=?|<=?|>=?|==?|&&?|&=|\^=?|\|\|?|\|=|\?|:/, punctuation: /\(\(?|\)\)?|,|;/ } }, { pattern: /\$\([^)]+\)|`[^`]+`/, greedy: !0, inside: { variable: /^\$\(|^`|\)$|`$/ } }, /\$(?:[\w#?*!@]+|\{[^}]+\})/i] };e.languages.bash = { shebang: { pattern: /^#!\s*\/bin\/bash|^#!\s*\/bin\/sh/, alias: "important" }, comment: { pattern: /(^|[^"{\\])#.*/, lookbehind: !0 }, string: [{ pattern: /((?:^|[^<])<<\s*)["']?(\w+?)["']?\s*\r?\n(?:[\s\S])*?\r?\n\2/, lookbehind: !0, greedy: !0, inside: t }, { pattern: /(["'])(?:\\[\s\S]|\$\([^)]+\)|`[^`]+`|(?!\1)[^\\])*\1/, greedy: !0, inside: t }], variable: t.variable, "function": { pattern: /(^|[\s;|&])(?:alias|apropos|apt-get|aptitude|aspell|awk|basename|bash|bc|bg|builtin|bzip2|cal|cat|cd|cfdisk|chgrp|chmod|chown|chroot|chkconfig|cksum|clear|cmp|comm|command|cp|cron|crontab|csplit|curl|cut|date|dc|dd|ddrescue|df|diff|diff3|dig|dir|dircolors|dirname|dirs|dmesg|du|egrep|eject|enable|env|ethtool|eval|exec|expand|expect|export|expr|fdformat|fdisk|fg|fgrep|file|find|fmt|fold|format|free|fsck|ftp|fuser|gawk|getopts|git|grep|groupadd|groupdel|groupmod|groups|gzip|hash|head|help|hg|history|hostname|htop|iconv|id|ifconfig|ifdown|ifup|import|install|jobs|join|kill|killall|less|link|ln|locate|logname|logout|look|lpc|lpr|lprint|lprintd|lprintq|lprm|ls|lsof|make|man|mkdir|mkfifo|mkisofs|mknod|more|most|mount|mtools|mtr|mv|mmv|nano|netstat|nice|nl|nohup|notify-send|npm|nslookup|open|op|passwd|paste|pathchk|ping|pkill|popd|pr|printcap|printenv|printf|ps|pushd|pv|pwd|quota|quotacheck|quotactl|ram|rar|rcp|read|readarray|readonly|reboot|rename|renice|remsync|rev|rm|rmdir|rsync|screen|scp|sdiff|sed|seq|service|sftp|shift|shopt|shutdown|sleep|slocate|sort|source|split|ssh|stat|strace|su|sudo|sum|suspend|sync|tail|tar|tee|test|time|timeout|times|touch|top|traceroute|trap|tr|tsort|tty|type|ulimit|umask|umount|unalias|uname|unexpand|uniq|units|unrar|unshar|uptime|useradd|userdel|usermod|users|uuencode|uudecode|v|vdir|vi|vmstat|wait|watch|wc|wget|whereis|which|who|whoami|write|xargs|xdg-open|yes|zip)(?=$|[\s;|&])/, lookbehind: !0 }, keyword: { pattern: /(^|[\s;|&])(?:let|:|\.|if|then|else|elif|fi|for|break|continue|while|in|case|function|select|do|done|until|echo|exit|return|set|declare)(?=$|[\s;|&])/, lookbehind: !0 }, "boolean": { pattern: /(^|[\s;|&])(?:true|false)(?=$|[\s;|&])/, lookbehind: !0 }, operator: /&&?|\|\|?|==?|!=?|<<<?|>>|<=?|>=?|=~/, punctuation: /\$?\(\(?|\)\)?|\.\.|[{}[\];]/ };var a = t.variable[1].inside;a.string = e.languages.bash.string, a["function"] = e.languages.bash["function"], a.keyword = e.languages.bash.keyword, a.boolean = e.languages.bash.boolean, a.operator = e.languages.bash.operator, a.punctuation = e.languages.bash.punctuation, e.languages.shell = e.languages.bash;
}(Prism);

/* https://cdnjs.cloudflare.com/ajax/libs/prism/1.14.0/plugins/line-numbers/prism-line-numbers.min.js */
!function () {
    if ("undefined" != typeof self && self.Prism && self.document) {
        var e = "line-numbers",
            t = /\n(?!$)/g,
            n = function n(e) {
            var n = r(e),
                s = n["white-space"];if ("pre-wrap" === s || "pre-line" === s) {
                var l = e.querySelector("code"),
                    i = e.querySelector(".line-numbers-rows"),
                    a = e.querySelector(".line-numbers-sizer"),
                    o = l.textContent.split(t);a || (a = document.createElement("span"), a.className = "line-numbers-sizer", l.appendChild(a)), a.style.display = "block", o.forEach(function (e, t) {
                    a.textContent = e || "\n";var n = a.getBoundingClientRect().height;i.children[t].style.height = n + "px";
                }), a.textContent = "", a.style.display = "none";
            }
        },
            r = function r(e) {
            return e ? window.getComputedStyle ? getComputedStyle(e) : e.currentStyle || null : null;
        };window.addEventListener("resize", function () {
            Array.prototype.forEach.call(document.querySelectorAll("pre." + e), n);
        }), Prism.hooks.add("complete", function (e) {
            if (e.code) {
                var r = e.element.parentNode,
                    s = /\s*\bline-numbers\b\s*/;if (r && /pre/i.test(r.nodeName) && (s.test(r.className) || s.test(e.element.className)) && !e.element.querySelector(".line-numbers-rows")) {
                    s.test(e.element.className) && (e.element.className = e.element.className.replace(s, " ")), s.test(r.className) || (r.className += " line-numbers");var l,
                        i = e.code.match(t),
                        a = i ? i.length + 1 : 1,
                        o = new Array(a + 1);o = o.join("<span></span>"), l = document.createElement("span"), l.setAttribute("aria-hidden", "true"), l.className = "line-numbers-rows", l.innerHTML = o, r.hasAttribute("data-start") && (r.style.counterReset = "linenumber " + (parseInt(r.getAttribute("data-start"), 10) - 1)), e.element.appendChild(l), n(r), Prism.hooks.run("line-numbers", e);
                }
            }
        }), Prism.hooks.add("line-numbers", function (e) {
            e.plugins = e.plugins || {}, e.plugins.lineNumbers = !0;
        }), Prism.plugins.lineNumbers = { getLine: function getLine(t, n) {
                if ("PRE" === t.tagName && t.classList.contains(e)) {
                    var r = t.querySelector(".line-numbers-rows"),
                        s = parseInt(t.getAttribute("data-start"), 10) || 1,
                        l = s + (r.children.length - 1);s > n && (n = s), n > l && (n = l);var i = n - s;return r.children[i];
                }
            } };
    }
}();

/* https://cdnjs.cloudflare.com/ajax/libs/prism/1.14.0/plugins/line-highlight/prism-line-highlight.min.js */
!function () {
    function e(e, t) {
        return Array.prototype.slice.call((t || document).querySelectorAll(e));
    }function t(e, t) {
        return t = " " + t + " ", (" " + e.className + " ").replace(/[\n\t]/g, " ").indexOf(t) > -1;
    }function n(e, n, i) {
        n = "string" == typeof n ? n : e.getAttribute("data-line");for (var o, l = n.replace(/\s+/g, "").split(","), a = +e.getAttribute("data-line-offset") || 0, s = r() ? parseInt : parseFloat, d = s(getComputedStyle(e).lineHeight), u = t(e, "line-numbers"), c = 0; o = l[c++];) {
            var p = o.split("-"),
                m = +p[0],
                f = +p[1] || m,
                h = e.querySelector('.line-highlight[data-range="' + o + '"]') || document.createElement("div");if (h.setAttribute("aria-hidden", "true"), h.setAttribute("data-range", o), h.className = (i || "") + " line-highlight", u && Prism.plugins.lineNumbers) {
                var g = Prism.plugins.lineNumbers.getLine(e, m),
                    y = Prism.plugins.lineNumbers.getLine(e, f);g && (h.style.top = g.offsetTop + "px"), y && (h.style.height = y.offsetTop - g.offsetTop + y.offsetHeight + "px");
            } else h.setAttribute("data-start", m), f > m && h.setAttribute("data-end", f), h.style.top = (m - a - 1) * d + "px", h.textContent = new Array(f - m + 2).join(" \n");u ? e.appendChild(h) : (e.querySelector("code") || e).appendChild(h);
        }
    }function i() {
        var t = location.hash.slice(1);e(".temporary.line-highlight").forEach(function (e) {
            e.parentNode.removeChild(e);
        });var i = (t.match(/\.([\d,-]+)$/) || [, ""])[1];if (i && !document.getElementById(t)) {
            var r = t.slice(0, t.lastIndexOf(".")),
                o = document.getElementById(r);o && (o.hasAttribute("data-line") || o.setAttribute("data-line", ""), n(o, i, "temporary "), document.querySelector(".temporary.line-highlight").scrollIntoView());
        }
    }if ("undefined" != typeof self && self.Prism && self.document && document.querySelector) {
        var r = function () {
            var e;return function () {
                if ("undefined" == typeof e) {
                    var t = document.createElement("div");t.style.fontSize = "13px", t.style.lineHeight = "1.5", t.style.padding = 0, t.style.border = 0, t.innerHTML = "&nbsp;<br />&nbsp;", document.body.appendChild(t), e = 38 === t.offsetHeight, document.body.removeChild(t);
                }return e;
            };
        }(),
            o = 0;Prism.hooks.add("before-sanity-check", function (t) {
            var n = t.element.parentNode,
                i = n && n.getAttribute("data-line");if (n && i && /pre/i.test(n.nodeName)) {
                var r = 0;e(".line-highlight", n).forEach(function (e) {
                    r += e.textContent.length, e.parentNode.removeChild(e);
                }), r && /^( \n)+$/.test(t.code.slice(-r)) && (t.code = t.code.slice(0, -r));
            }
        }), Prism.hooks.add("complete", function l(e) {
            var r = e.element.parentNode,
                a = r && r.getAttribute("data-line");if (r && a && /pre/i.test(r.nodeName)) {
                clearTimeout(o);var s = Prism.plugins.lineNumbers,
                    d = e.plugins && e.plugins.lineNumbers;t(r, "line-numbers") && s && !d ? Prism.hooks.add("line-numbers", l) : (n(r, a), o = setTimeout(i, 1));
            }
        }), window.addEventListener("hashchange", i), window.addEventListener("resize", function () {
            var e = document.querySelectorAll("pre[data-line]");Array.prototype.forEach.call(e, function (e) {
                n(e);
            });
        });
    }
}();

/* https://cdnjs.cloudflare.com/ajax/libs/prism/1.14.0/plugins/toolbar/prism-toolbar.min.js */
!function () {
    if ("undefined" != typeof self && self.Prism && self.document) {
        var t = [],
            e = {},
            n = function n() {};Prism.plugins.toolbar = {};var a = Prism.plugins.toolbar.registerButton = function (n, a) {
            var o;o = "function" == typeof a ? a : function (t) {
                var e;return "function" == typeof a.onClick ? (e = document.createElement("button"), e.type = "button", e.addEventListener("click", function () {
                    a.onClick.call(this, t);
                })) : "string" == typeof a.url ? (e = document.createElement("a"), e.href = a.url) : e = document.createElement("span"), e.textContent = a.text, e;
            }, t.push(e[n] = o);
        },
            o = Prism.plugins.toolbar.hook = function (a) {
            var o = a.element.parentNode;if (o && /pre/i.test(o.nodeName) && !o.parentNode.classList.contains("code-toolbar")) {
                var r = document.createElement("div");r.classList.add("code-toolbar"), o.parentNode.insertBefore(r, o), r.appendChild(o);var i = document.createElement("div");i.classList.add("toolbar"), document.body.hasAttribute("data-toolbar-order") && (t = document.body.getAttribute("data-toolbar-order").split(",").map(function (t) {
                    return e[t] || n;
                })), t.forEach(function (t) {
                    var e = t(a);if (e) {
                        var n = document.createElement("div");n.classList.add("toolbar-item"), n.appendChild(e), i.appendChild(n);
                    }
                }), r.appendChild(i);
            }
        };a("label", function (t) {
            var e = t.element.parentNode;if (e && /pre/i.test(e.nodeName) && e.hasAttribute("data-label")) {
                var n,
                    a,
                    o = e.getAttribute("data-label");try {
                    a = document.querySelector("template#" + o);
                } catch (r) {}return a ? n = a.content : (e.hasAttribute("data-url") ? (n = document.createElement("a"), n.href = e.getAttribute("data-url")) : n = document.createElement("span"), n.textContent = o), n;
            }
        }), Prism.hooks.add("complete", o);
    }
}();

(function () {
    if (typeof self === 'undefined' || !self.Prism || !self.document) {
        return;
    }

    if (!Prism.plugins.toolbar) {
        console.warn('Copy to Clipboard plugin loaded before Toolbar plugin.');

        return;
    }

    var Clipboard = window.Clipboard || undefined;

    if (Clipboard && /(native code)/.test(Clipboard.toString())) {
        Clipboard = undefined;
    }

    if (!Clipboard && "function" === 'function') {
        Clipboard = __webpack_require__(0);
    }

    var callbacks = [];

    if (!Clipboard) {
        var script = document.createElement('script');
        var head = document.querySelector('head');

        script.onload = function () {
            Clipboard = window.Clipboard;

            if (Clipboard) {
                while (callbacks.length) {
                    callbacks.pop()();
                }
            }
        };

        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.7.1/clipboard.min.js';
        head.appendChild(script);
    }

    Prism.plugins.toolbar.registerButton('copy-to-clipboard', function (env) {
        var linkCopy = document.createElement('a');
        linkCopy.className = 'copy-code';
        linkCopy.setAttribute('aria-label', 'Copy Code');
        linkCopy.innerHTML = '<i class="icon-copy"></i>';

        if (!Clipboard) {
            callbacks.push(registerClipboard);
        } else {
            registerClipboard();
        }

        return linkCopy;

        function registerClipboard() {
            var clip = new Clipboard(linkCopy, {
                'text': function text() {
                    return env.code;
                }
            });

            clip.on('success', function () {
                linkCopy.setAttribute('aria-label', 'Copied!');

                resetText();
            });
            clip.on('error', function () {
                linkCopy.textContent = 'Press Ctrl+C to copy';

                resetText();
            });
        }

        function resetText() {
            setTimeout(function () {
                linkCopy.setAttribute('aria-label', 'Copy Code');
            }, 5000);
        }
    });
})();
/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(3)))

/***/ }),
/* 3 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var g;

// This works in non-strict mode
g = function () {
	return this;
}();

try {
	// This works if eval is allowed (see CSP)
	g = g || Function("return this")() || (1, eval)("this");
} catch (e) {
	// This works if the window reference is available
	if ((typeof window === "undefined" ? "undefined" : _typeof(window)) === "object") g = window;
}

// g can still be undefined, but nothing to do about it...
// We return undefined, instead of nothing here, so it's
// easier to handle this case. if(!global) { ...}

module.exports = g;

/***/ }),
/* 4 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


module.exports = function (module) {
	if (!module.webpackPolyfill) {
		module.deprecate = function () {};
		module.paths = [];
		// module.parent = undefined by default
		if (!module.children) module.children = [];
		Object.defineProperty(module, "loaded", {
			enumerable: true,
			get: function get() {
				return module.l;
			}
		});
		Object.defineProperty(module, "id", {
			enumerable: true,
			get: function get() {
				return module.i;
			}
		});
		module.webpackPolyfill = 1;
	}
	return module;
};

/***/ }),
/* 5 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


//((=== Copy Code To Clipboard  ===))//

var Clipboard = __webpack_require__(0);

/***/ })
/******/ ]);