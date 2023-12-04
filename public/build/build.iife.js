(function() {
  "use strict";
  function getDefaultExportFromCjs(x) {
    return x && x.__esModule && Object.prototype.hasOwnProperty.call(x, "default") ? x["default"] : x;
  }
  var toString = Object.prototype.toString;
  var kindOf = function kindOf2(val) {
    if (val === void 0)
      return "undefined";
    if (val === null)
      return "null";
    var type = typeof val;
    if (type === "boolean")
      return "boolean";
    if (type === "string")
      return "string";
    if (type === "number")
      return "number";
    if (type === "symbol")
      return "symbol";
    if (type === "function") {
      return isGeneratorFn(val) ? "generatorfunction" : "function";
    }
    if (isArray(val))
      return "array";
    if (isBuffer(val))
      return "buffer";
    if (isArguments(val))
      return "arguments";
    if (isDate(val))
      return "date";
    if (isError(val))
      return "error";
    if (isRegexp(val))
      return "regexp";
    switch (ctorName(val)) {
      case "Symbol":
        return "symbol";
      case "Promise":
        return "promise";
      case "WeakMap":
        return "weakmap";
      case "WeakSet":
        return "weakset";
      case "Map":
        return "map";
      case "Set":
        return "set";
      case "Int8Array":
        return "int8array";
      case "Uint8Array":
        return "uint8array";
      case "Uint8ClampedArray":
        return "uint8clampedarray";
      case "Int16Array":
        return "int16array";
      case "Uint16Array":
        return "uint16array";
      case "Int32Array":
        return "int32array";
      case "Uint32Array":
        return "uint32array";
      case "Float32Array":
        return "float32array";
      case "Float64Array":
        return "float64array";
    }
    if (isGeneratorObj(val)) {
      return "generator";
    }
    type = toString.call(val);
    switch (type) {
      case "[object Object]":
        return "object";
      case "[object Map Iterator]":
        return "mapiterator";
      case "[object Set Iterator]":
        return "setiterator";
      case "[object String Iterator]":
        return "stringiterator";
      case "[object Array Iterator]":
        return "arrayiterator";
    }
    return type.slice(8, -1).toLowerCase().replace(/\s/g, "");
  };
  function ctorName(val) {
    return typeof val.constructor === "function" ? val.constructor.name : null;
  }
  function isArray(val) {
    if (Array.isArray)
      return Array.isArray(val);
    return val instanceof Array;
  }
  function isError(val) {
    return val instanceof Error || typeof val.message === "string" && val.constructor && typeof val.constructor.stackTraceLimit === "number";
  }
  function isDate(val) {
    if (val instanceof Date)
      return true;
    return typeof val.toDateString === "function" && typeof val.getDate === "function" && typeof val.setDate === "function";
  }
  function isRegexp(val) {
    if (val instanceof RegExp)
      return true;
    return typeof val.flags === "string" && typeof val.ignoreCase === "boolean" && typeof val.multiline === "boolean" && typeof val.global === "boolean";
  }
  function isGeneratorFn(name, val) {
    return ctorName(name) === "GeneratorFunction";
  }
  function isGeneratorObj(val) {
    return typeof val.throw === "function" && typeof val.return === "function" && typeof val.next === "function";
  }
  function isArguments(val) {
    try {
      if (typeof val.length === "number" && typeof val.callee === "function") {
        return true;
      }
    } catch (err) {
      if (err.message.indexOf("callee") !== -1) {
        return true;
      }
    }
    return false;
  }
  function isBuffer(val) {
    if (val.constructor && typeof val.constructor.isBuffer === "function") {
      return val.constructor.isBuffer(val);
    }
    return false;
  }
  /*!
   * shallow-clone <https://github.com/jonschlinkert/shallow-clone>
   *
   * Copyright (c) 2015-present, Jon Schlinkert.
   * Released under the MIT License.
   */
  const valueOf = Symbol.prototype.valueOf;
  const typeOf$1 = kindOf;
  function clone$1(val, deep) {
    switch (typeOf$1(val)) {
      case "array":
        return val.slice();
      case "object":
        return Object.assign({}, val);
      case "date":
        return new val.constructor(Number(val));
      case "map":
        return new Map(val);
      case "set":
        return new Set(val);
      case "buffer":
        return cloneBuffer(val);
      case "symbol":
        return cloneSymbol(val);
      case "arraybuffer":
        return cloneArrayBuffer(val);
      case "float32array":
      case "float64array":
      case "int16array":
      case "int32array":
      case "int8array":
      case "uint16array":
      case "uint32array":
      case "uint8clampedarray":
      case "uint8array":
        return cloneTypedArray(val);
      case "regexp":
        return cloneRegExp(val);
      case "error":
        return Object.create(val);
      default: {
        return val;
      }
    }
  }
  function cloneRegExp(val) {
    const flags = val.flags !== void 0 ? val.flags : /\w+$/.exec(val) || void 0;
    const re = new val.constructor(val.source, flags);
    re.lastIndex = val.lastIndex;
    return re;
  }
  function cloneArrayBuffer(val) {
    const res = new val.constructor(val.byteLength);
    new Uint8Array(res).set(new Uint8Array(val));
    return res;
  }
  function cloneTypedArray(val, deep) {
    return new val.constructor(val.buffer, val.byteOffset, val.length);
  }
  function cloneBuffer(val) {
    const len = val.length;
    const buf = Buffer.allocUnsafe ? Buffer.allocUnsafe(len) : Buffer.from(len);
    val.copy(buf);
    return buf;
  }
  function cloneSymbol(val) {
    return valueOf ? Object(valueOf.call(val)) : {};
  }
  var shallowClone = clone$1;
  /*!
   * isobject <https://github.com/jonschlinkert/isobject>
   *
   * Copyright (c) 2014-2017, Jon Schlinkert.
   * Released under the MIT License.
   */
  var isobject = function isObject2(val) {
    return val != null && typeof val === "object" && Array.isArray(val) === false;
  };
  /*!
   * is-plain-object <https://github.com/jonschlinkert/is-plain-object>
   *
   * Copyright (c) 2014-2017, Jon Schlinkert.
   * Released under the MIT License.
   */
  var isObject = isobject;
  function isObjectObject(o) {
    return isObject(o) === true && Object.prototype.toString.call(o) === "[object Object]";
  }
  var isPlainObject$1 = function isPlainObject2(o) {
    var ctor, prot;
    if (isObjectObject(o) === false)
      return false;
    ctor = o.constructor;
    if (typeof ctor !== "function")
      return false;
    prot = ctor.prototype;
    if (isObjectObject(prot) === false)
      return false;
    if (prot.hasOwnProperty("isPrototypeOf") === false) {
      return false;
    }
    return true;
  };
  const clone = shallowClone;
  const typeOf = kindOf;
  const isPlainObject = isPlainObject$1;
  function cloneDeep(val, instanceClone) {
    switch (typeOf(val)) {
      case "object":
        return cloneObjectDeep(val, instanceClone);
      case "array":
        return cloneArrayDeep(val, instanceClone);
      default: {
        return clone(val);
      }
    }
  }
  function cloneObjectDeep(val, instanceClone) {
    if (typeof instanceClone === "function") {
      return instanceClone(val);
    }
    if (instanceClone || isPlainObject(val)) {
      const res = new val.constructor();
      for (let key in val) {
        res[key] = cloneDeep(val[key], instanceClone);
      }
      return res;
    }
    return val;
  }
  function cloneArrayDeep(val, instanceClone) {
    const res = new val.constructor(val.length);
    for (let i = 0; i < val.length; i++) {
      res[i] = cloneDeep(val[i], instanceClone);
    }
    return res;
  }
  var cloneDeep_1 = cloneDeep;
  const cloneDeep$1 = /* @__PURE__ */ getDefaultExportFromCjs(cloneDeep_1);
  const SliderHomeListPanel_vue_vue_type_style_index_0_scoped_d4bfe2fe_lang = "";
  function normalizeComponent(scriptExports, render, staticRenderFns, functionalTemplate, injectStyles, scopeId, moduleIdentifier, shadowMode) {
    var options = typeof scriptExports === "function" ? scriptExports.options : scriptExports;
    if (render) {
      options.render = render;
      options.staticRenderFns = staticRenderFns;
      options._compiled = true;
    }
    if (functionalTemplate) {
      options.functional = true;
    }
    if (scopeId) {
      options._scopeId = "data-v-" + scopeId;
    }
    var hook;
    if (moduleIdentifier) {
      hook = function(context) {
        context = context || // cached call
        this.$vnode && this.$vnode.ssrContext || // stateful
        this.parent && this.parent.$vnode && this.parent.$vnode.ssrContext;
        if (!context && typeof __VUE_SSR_CONTEXT__ !== "undefined") {
          context = __VUE_SSR_CONTEXT__;
        }
        if (injectStyles) {
          injectStyles.call(this, context);
        }
        if (context && context._registeredComponents) {
          context._registeredComponents.add(moduleIdentifier);
        }
      };
      options._ssrRegister = hook;
    } else if (injectStyles) {
      hook = shadowMode ? function() {
        injectStyles.call(
          this,
          (options.functional ? this.parent : this).$root.$options.shadowRoot
        );
      } : injectStyles;
    }
    if (hook) {
      if (options.functional) {
        options._injectStyles = hook;
        var originalRender = options.render;
        options.render = function renderWithStyleInjection(h, context) {
          hook.call(context);
          return originalRender(h, context);
        };
      } else {
        var existing = options.beforeCreate;
        options.beforeCreate = existing ? [].concat(existing, hook) : [hook];
      }
    }
    return {
      exports: scriptExports,
      options
    };
  }
  const _sfc_main = {
    name: "sliderHomeListPanelComponent",
    props: {
      items: {
        type: Array,
        default() {
          return [];
        }
      },
      title: {
        type: String,
        required: true
      },
      form: {
        type: Object,
        required: true
      },
      addSliderLabel: {
        type: String,
        required: true
      },
      confirmDeleteMessage: {
        type: String,
        required: true
      },
      apiUrl: {
        type: String,
        required: true
      }
    },
    data() {
      return {
        activeForm: null,
        activeFormTitle: "",
        isLoading: false,
        isOrdering: false,
        resetFocusTo: null
      };
    },
    created() {
    },
    methods: {
      /**
       * Clear the active form when the modal is closed
       *
       * @param {Object} event
       */
      formModalClosed(event) {
        this.activeForm = null;
        this.activeFormTitle = "";
        if (this.resetFocusTo) {
          this.resetFocusTo.focus();
        }
      },
      /**
       * The add/edit form has been successfully
       * submitted.
       *
       * @param {Object} item
       */
      formSuccess(item) {
        if (this.activeForm.method === "POST") {
          this.offset = 0;
          this.get();
          pkp.eventBus.$emit("add:sliderContent", item);
        } else {
          this.setItems(
            this.items.map((i) => i.id === item.id ? item : i),
            this.itemsMax
          );
          pkp.eventBus.$emit("update:sliderContent", item);
        }
        this.$modal.hide("form");
      },
      /**
       * Open the modal to add an item
       */
      toggleVisibility(id) {
        const item = this.getItem(id);
        this.resetFocusTo = document.activeElement;
        if (!item)
          return;
        item.show_content = !item.show_content;
        $.ajax({
          url: this.apiUrl + "/toggleShow/" + id,
          type: "POST",
          headers: {
            "X-Csrf-Token": pkp.currentUser.csrfToken,
            "X-Http-Method-Override": "PUT"
          },
          error: this.ajaxErrorCallback,
          success: (r) => {
            this.setItems(
              this.items.filter((i) => i.id !== id),
              this.itemsMax
            );
            this.$modal.hide("delete");
            this.setFocusIn(this.resetFocusTo);
          }
        });
      },
      getItem(id) {
        const item = this.items.find((a) => a.id === id);
        if (typeof item === "undefined") {
          this.ajaxErrorCallback({});
          return false;
        }
        return item;
      },
      /**
       * Open the modal to add an item
       */
      openAddModal() {
        this.resetFocusTo = document.activeElement;
        let activeForm = cloneDeep$1(this.form);
        activeForm.action = this.apiUrl;
        activeForm.method = "POST";
        this.activeForm = activeForm;
        this.activeFormTitle = this.addSliderLabel;
        this.$modal.show("form");
      },
      /**
       * Open delete modal
       *
       * @param {Number} id
       */
      openDeleteModal(id) {
        const item = this.getItem(id);
        if (!item)
          return;
        this.openDialog({
          name: "delete",
          title: this.__("common.delete"),
          message: this.replaceLocaleParams(this.confirmDeleteMessage, {
            title: item.name
          }),
          actions: [
            {
              label: this.__("common.yes"),
              isPrimary: true,
              callback: () => {
                $.ajax({
                  url: this.apiUrl + "/" + id,
                  type: "POST",
                  headers: {
                    "X-Csrf-Token": pkp.currentUser.csrfToken,
                    "X-Http-Method-Override": "DELETE"
                  },
                  error: this.ajaxErrorCallback,
                  success: (r) => {
                    this.setItems(
                      this.items.filter((i) => i.id !== id),
                      this.itemsMax
                    );
                    this.$modal.hide("delete");
                    this.setFocusIn(this.$el);
                  }
                });
              }
            },
            {
              label: this.__("common.no"),
              isWarnable: true,
              callback: () => this.$modal.hide("delete")
            }
          ]
        });
      },
      /**
       * Open the modal to edit an item
       *
       * @param {Number} id
       */
      openEditModal(id) {
        this.resetFocusTo = document.activeElement;
        const highlight = this.items.find((highlight2) => highlight2.id === id);
        if (!highlight) {
          this.ajaxErrorCallback({});
          return;
        }
        let activeForm = cloneDeep$1(this.form);
        activeForm.action = this.apiUrl + "/" + id;
        activeForm.method = "PUT";
        activeForm.fields = activeForm.fields.map((field) => {
          if (Object.keys(highlight).includes(field.name)) {
            field.value = highlight[field.name];
          }
          return field;
        });
        this.activeForm = activeForm;
        this.activeFormTitle = this.i18nEdit;
        this.$modal.show("form");
      },
      /**
       * Move an item down in the list
       *
       * @param {Object} item The item to move
       */
      orderDown(item) {
        var index = this.items.findIndex((obj) => {
          return item.id == obj.id;
        });
        if (index === this.items.length - 1) {
          return;
        }
        let newItems = [...this.items];
        newItems.splice(index + 1, 0, newItems.splice(index, 1)[0]);
        this.setItems(newItems, newItems.length);
      },
      /**
       * Move an item up in the list
       *
       * @param {Object} item The item to move
       */
      orderUp(item) {
        var index = this.items.findIndex((obj) => {
          return item.id == obj.id;
        });
        if (index === 0) {
          return;
        }
        let newItems = [...this.items];
        newItems.splice(index - 1, 0, newItems.splice(index, 1)[0]);
        this.setItems(newItems, newItems.length);
      },
      /**
       * Save the order of items
       */
      saveOrder() {
        this.isLoading = true;
        let sequence = 0;
        for (const item of this.items) {
          item.sequence = sequence;
          sequence++;
        }
        $.ajax({
          url: this.apiUrl + "/order",
          type: "POST",
          context: this,
          data: {
            sequence: this.items.map(({ id, sequence: sequence2 }) => ({ id, sequence: sequence2 }))
          },
          headers: {
            "X-Csrf-Token": pkp.currentUser.csrfToken,
            "X-Http-Method-Override": "PUT"
          },
          error: this.ajaxErrorCallback,
          success: (r) => {
            this.setItems(r.items, r.itemsMax);
          },
          complete: () => {
            this.isLoading = false;
            this.isOrdering = false;
          }
        });
      },
      /**
       * Set the list of items
       *
       * @see @/mixins/fetch.js
       * @param {Array} items
       * @param {Number} itemsMax
       */
      setItems(items, itemsMax) {
        this.$emit("set", this.id, {
          items,
          itemsMax
        });
      },
      /**
       * Update form values when they change
       *
       * @param {String} formId
       * @param {Object} data
       */
      updateForm(formId, data) {
        let activeForm = { ...this.activeForm };
        Object.keys(data).forEach(function(key) {
          activeForm[key] = data[key];
        });
        this.activeForm = activeForm;
      }
    }
  };
  var _sfc_render = function render() {
    var _vm = this, _c = _vm._self._c;
    return _c("div", [_c("pkp-list-panel", { attrs: { "items": _vm.items, "title": "title" }, scopedSlots: _vm._u([{ key: "item-title", fn: function({ item }) {
      return [item.show_content ? _c("pkp-badge", { attrs: { "isSuccess": true } }, [_c("pkp-icon", { attrs: { "icon": "fa-light fa-eye", "inline": true } }), _vm._v(" " + _vm._s(item.name) + " ")], 1) : _vm._e(), !item.show_content ? _c("pkp-badge", [_vm._v(" " + _vm._s(item.name) + " ")]) : _vm._e()];
    } }, { key: "item-actions", fn: function({ item }) {
      return [_c("pkp-button", { on: { "click": function($event) {
        return _vm.toggleVisibility(item.id);
      } } }, [_vm._v(" Enable/Disable ")]), _c("pkp-button", { on: { "click": function($event) {
        return _vm.openEditModal(item.id);
      } } }, [_vm._v(" " + _vm._s(_vm.__("common.edit")) + " ")]), _c("pkp-button", { attrs: { "isWarnable": true }, on: { "click": function($event) {
        return _vm.openDeleteModal(item.id);
      } } }, [_vm._v(" " + _vm._s(_vm.__("common.delete")) + " ")])];
    } }]) }, [_c("pkp-header", { attrs: { "slot": "header" }, slot: "header" }, [_c("h2", [_vm._v(_vm._s(_vm.title))]), _vm.isLoading ? _c("spinner") : _vm._e(), _c("pkp-button", { ref: "addSliderButton", staticStyle: { "float": "right" }, on: { "click": _vm.openAddModal } }, [_vm._v(" " + _vm._s(_vm.addSliderLabel) + " ")])], 1), _vm._v(_vm._s(_vm.items.length) + " ")], 1), _c("pkp-modal", { attrs: { "closeLabel": _vm.__("common.close"), "name": "form", "title": _vm.activeFormTitle }, on: { "closed": _vm.formModalClosed } }, [_c("pkp-form", _vm._b({ on: { "set": _vm.updateForm, "success": _vm.formSuccess } }, "pkp-form", _vm.activeForm, false))], 1)], 1);
  };
  var _sfc_staticRenderFns = [];
  var __component__ = /* @__PURE__ */ normalizeComponent(
    _sfc_main,
    _sfc_render,
    _sfc_staticRenderFns,
    false,
    null,
    "d4bfe2fe",
    null,
    null
  );
  const SliderHomeListPanel = __component__.exports;
  pkp.Vue.component("SliderHomeListPanel", SliderHomeListPanel);
})();
