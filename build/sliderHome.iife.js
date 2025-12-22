(function(vue) {
  "use strict";
  const _hoisted_1$2 = { key: 0 };
  const _hoisted_2$2 = { key: 1 };
  const _hoisted_3$1 = { key: 2 };
  const _sfc_main$2 = {
    __name: "AddSliderContentSideModal",
    props: {
      mode: {
        type: String,
        default: "add"
      },
      form: {
        type: Object,
        default: null
      },
      item: {
        type: Object,
        default: null
      },
      itemId: {
        type: [String, Number],
        default: null
      },
      onFormSuccess: { type: Function, default: null }
    },
    emits: ["set"],
    setup(__props, { emit: __emit }) {
      const { useLocalize } = pkp.modules.useLocalize;
      const { t } = useLocalize();
      const closeModal = vue.inject("closeModal");
      const props = __props;
      const ensureHiddenField = (name, value) => {
        if (!props.form || !Array.isArray(props.form.fields)) return;
        const existing = props.form.fields.find((f) => f.name === name);
        if (existing) {
          existing.value = value;
        } else {
          props.form.fields.push({ name, type: "hidden", value });
        }
      };
      ensureHiddenField("mode", props.mode);
      ensureHiddenField("itemId", props.itemId ?? "");
      vue.watch(() => props.mode, (v) => ensureHiddenField("mode", v));
      vue.watch(() => props.itemId, (v) => ensureHiddenField("itemId", v ?? ""));
      function handleSuccess(data) {
        if (props.onFormSuccess) {
          props.onFormSuccess(data);
        }
        closeModal();
      }
      return (_ctx, _cache) => {
        const _component_PkpForm = vue.resolveComponent("PkpForm");
        const _component_PkpSideModalLayoutBasic = vue.resolveComponent("PkpSideModalLayoutBasic");
        const _component_PkpSideModalBody = vue.resolveComponent("PkpSideModalBody");
        return vue.openBlock(), vue.createBlock(_component_PkpSideModalBody, null, {
          title: vue.withCtx(() => [
            __props.mode === "add" ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_1$2, vue.toDisplayString(__props.form.ButtonLabelAdd), 1)) : vue.createCommentVNode("", true),
            __props.mode === "addFromIssue" ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_2$2, vue.toDisplayString(__props.form.ButtonLabeladdFromIssue), 1)) : vue.createCommentVNode("", true),
            __props.mode === "edit" ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_3$1, vue.toDisplayString(__props.form.ButtonLabelEdit) + ": " + vue.toDisplayString(__props.item ? __props.item.name : ""), 1)) : vue.createCommentVNode("", true)
          ]),
          default: vue.withCtx(() => [
            vue.createVNode(_component_PkpSideModalLayoutBasic, null, {
              default: vue.withCtx(() => [
                vue.createVNode(_component_PkpForm, vue.mergeProps(__props.form, { onSuccess: handleSuccess }), null, 16)
              ]),
              _: 1
            })
          ]),
          _: 1
        });
      };
    }
  };
  function getDefaultExportFromCjs(x) {
    return x && x.__esModule && Object.prototype.hasOwnProperty.call(x, "default") ? x["default"] : x;
  }
  var debounce_1;
  var hasRequiredDebounce;
  function requireDebounce() {
    if (hasRequiredDebounce) return debounce_1;
    hasRequiredDebounce = 1;
    function debounce2(func, wait, immediate) {
      var timeout, args, context, timestamp, result;
      if (null == wait) wait = 100;
      function later() {
        var last = Date.now() - timestamp;
        if (last < wait && last >= 0) {
          timeout = setTimeout(later, wait - last);
        } else {
          timeout = null;
          if (!immediate) {
            result = func.apply(context, args);
            context = args = null;
          }
        }
      }
      var debounced = function() {
        context = this;
        args = arguments;
        timestamp = Date.now();
        var callNow = immediate && !timeout;
        if (!timeout) timeout = setTimeout(later, wait);
        if (callNow) {
          result = func.apply(context, args);
          context = args = null;
        }
        return result;
      };
      debounced.clear = function() {
        if (timeout) {
          clearTimeout(timeout);
          timeout = null;
        }
      };
      debounced.flush = function() {
        if (timeout) {
          result = func.apply(context, args);
          context = args = null;
          clearTimeout(timeout);
          timeout = null;
        }
      };
      return debounced;
    }
    debounce2.debounce = debounce2;
    debounce_1 = debounce2;
    return debounce_1;
  }
  var debounceExports = requireDebounce();
  const debounce = /* @__PURE__ */ getDefaultExportFromCjs(debounceExports);
  const _hoisted_1$1 = { key: 0 };
  const _hoisted_2$1 = { key: 1 };
  const _hoisted_3 = { key: 2 };
  const _hoisted_4 = { key: 0 };
  const _hoisted_5 = { class: "flex items-center" };
  const _hoisted_6 = { key: 0 };
  const _sfc_main$1 = {
    __name: "SelectIssueSideModal",
    props: {
      mode: {
        type: String,
        default: "add"
      },
      form: {
        type: Object,
        default: null
      },
      item: {
        type: Object,
        default: null
      },
      itemId: {
        type: [String, Number],
        default: null
      },
      onFormSuccess: { type: Function, default: null }
    },
    emits: ["set"],
    setup(__props, { emit: __emit }) {
      const { useLocalize } = pkp.modules.useLocalize;
      const { t } = useLocalize();
      const closeModal = vue.inject("closeModal");
      const publishedIssues = vue.ref([]);
      const props = __props;
      const { useModal } = pkp.modules.useModal;
      const { openSideModal } = useModal();
      if (props.mode === "addFromIssue") {
        $.ajax({
          url: props.form.searchApiUrl + "?status=3",
          method: "GET",
          dataType: "json"
        }).then((response) => {
          if (response) {
            issueResults.value = response.items;
            publishedIssues.value = response.items;
          }
        }).catch((error) => {
          console.error("Error fetching issue ID:", error);
        });
      }
      const searchPhrase = vue.ref("");
      const issueResults = vue.ref([]);
      const isSearching = vue.ref(false);
      let searchRequestId = 0;
      function setSearchPhrase(phrase) {
        searchPhrase.value = phrase;
        doIssueSearch(phrase);
      }
      const doIssueSearch = debounce(async (phrase) => {
        if (!phrase) {
          issueResults.value = publishedIssues.value;
          return;
        }
        isSearching.value = true;
        const reqId = ++searchRequestId;
        try {
          const url = props.form.searchApiUrl + "?status=3&searchPhrase=" + encodeURIComponent(phrase);
          const res = await fetch(url, { credentials: "same-origin" });
          if (!res.ok) throw new Error("Network response was not ok");
          const json = await res.json();
          if (reqId !== searchRequestId) return;
          issueResults.value = json.items || [];
        } catch (e) {
          console.error("Issue search error", e);
          issueResults.value = [];
        } finally {
          if (reqId === searchRequestId) isSearching.value = false;
        }
      }, 300);
      function selectIssue(issue) {
        if (!issue || !props.form) return;
        props.form.fields.forEach((field) => {
          if (field.name === "name") {
            field.value = issue.identification || "";
          } else if (field.name === "sliderImageLink") {
            field.value = issue.publishedUrl || "";
          } else if (field.name === "sliderImage") {
            for (const localeKey in props.form.visibleLocales) {
              const locale = props.form.visibleLocales[localeKey];
              if (issue.coverImage && issue.coverImage[locale]) {
                if (!field.value[locale]) field.value[locale] = {};
                field.value[locale]["temporaryFileId"] = issue.coverImage[locale] || "";
                field.value[locale]["altText"] = issue.coverImageAltText && issue.coverImageAltText[locale] ? issue.coverImageAltText[locale] : "";
              }
            }
          } else if (field.name === "content") {
            for (const localeKey in props.form.visibleLocales || {}) {
              const locale = props.form.visibleLocales[localeKey];
              if (!field.value) field.value = {};
              field.value[locale] = issue.title && issue.title[locale] || "";
            }
          }
        });
        closeModal();
        openSideModal(_sfc_main$2, {
          modalProps: {
            size: "large"
          },
          mode: "addFromIssue",
          form: props.form,
          itemId: issue.id,
          item: issue,
          onFormSuccess: (data) => {
            if (props.onFormSuccess) {
              props.onFormSuccess(data);
            }
            closeModal();
          }
        });
      }
      return (_ctx, _cache) => {
        const _component_PkpSearch = vue.resolveComponent("PkpSearch");
        const _component_PkpTableColumn = vue.resolveComponent("PkpTableColumn");
        const _component_PkpTableHeader = vue.resolveComponent("PkpTableHeader");
        const _component_PkpTableCell = vue.resolveComponent("PkpTableCell");
        const _component_PkpButton = vue.resolveComponent("PkpButton");
        const _component_PkpTableRow = vue.resolveComponent("PkpTableRow");
        const _component_PkpTableBody = vue.resolveComponent("PkpTableBody");
        const _component_PkpTable = vue.resolveComponent("PkpTable");
        const _component_PkpSideModalLayoutBasic = vue.resolveComponent("PkpSideModalLayoutBasic");
        const _component_PkpSideModalBody = vue.resolveComponent("PkpSideModalBody");
        return vue.openBlock(), vue.createBlock(_component_PkpSideModalBody, null, {
          title: vue.withCtx(() => [
            __props.mode === "add" ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_1$1, vue.toDisplayString(__props.form.ButtonLabelAdd), 1)) : vue.createCommentVNode("", true),
            __props.mode === "addFromIssue" ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_2$1, vue.toDisplayString(__props.form.ButtonLabeladdFromIssue), 1)) : vue.createCommentVNode("", true),
            __props.mode === "edit" ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_3, vue.toDisplayString(__props.form.ButtonLabelEdit) + ": " + vue.toDisplayString(__props.item ? __props.item.name : ""), 1)) : vue.createCommentVNode("", true)
          ]),
          default: vue.withCtx(() => [
            vue.createVNode(_component_PkpSideModalLayoutBasic, null, {
              default: vue.withCtx(() => [
                __props.mode === "addFromIssue" ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_4, [
                  vue.createVNode(_component_PkpTable, {
                    "aria-label": vue.unref(t)("plugins.generic.sliderHome.addFromIssue")
                  }, {
                    "top-controls": vue.withCtx(() => [
                      vue.createVNode(_component_PkpSearch, {
                        searchPhrase: searchPhrase.value,
                        onSearchPhraseChanged: setSearchPhrase
                      }, null, 8, ["searchPhrase"]),
                      vue.createElementVNode("div", _hoisted_5, [
                        isSearching.value ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_6, vue.toDisplayString(vue.unref(t)("common.loading")), 1)) : vue.createCommentVNode("", true)
                      ])
                    ]),
                    default: vue.withCtx(() => [
                      vue.createVNode(_component_PkpTableHeader, null, {
                        default: vue.withCtx(() => [
                          vue.createVNode(_component_PkpTableColumn, { id: "identification" }, {
                            default: vue.withCtx(() => [
                              vue.createTextVNode(vue.toDisplayString(vue.unref(t)("editor.issues.identification")), 1)
                            ]),
                            _: 1
                          }),
                          vue.createVNode(_component_PkpTableColumn, { id: "title" }, {
                            default: vue.withCtx(() => [
                              vue.createTextVNode(vue.toDisplayString(vue.unref(t)("common.title")), 1)
                            ]),
                            _: 1
                          }),
                          vue.createVNode(_component_PkpTableColumn, { id: "actions" }, {
                            default: vue.withCtx(() => [..._cache[0] || (_cache[0] = [
                              vue.createTextVNode(" ", -1)
                            ])]),
                            _: 1
                          })
                        ]),
                        _: 1
                      }),
                      vue.createVNode(_component_PkpTableBody, null, {
                        default: vue.withCtx(() => [
                          (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(issueResults.value, (issue) => {
                            return vue.openBlock(), vue.createBlock(_component_PkpTableRow, {
                              key: issue.id
                            }, {
                              default: vue.withCtx(() => [
                                vue.createVNode(_component_PkpTableCell, null, {
                                  default: vue.withCtx(() => [
                                    vue.createTextVNode(vue.toDisplayString(issue.identification || ""), 1)
                                  ]),
                                  _: 2
                                }, 1024),
                                vue.createVNode(_component_PkpTableCell, null, {
                                  default: vue.withCtx(() => [
                                    vue.createTextVNode(vue.toDisplayString(issue.title && issue.title[Object.keys(issue.title || {})[0]] || ""), 1)
                                  ]),
                                  _: 2
                                }, 1024),
                                vue.createVNode(_component_PkpTableCell, null, {
                                  default: vue.withCtx(() => [
                                    vue.createVNode(_component_PkpButton, {
                                      onClick: ($event) => selectIssue(issue)
                                    }, {
                                      default: vue.withCtx(() => [
                                        vue.createTextVNode(vue.toDisplayString(vue.unref(t)("common.select")), 1)
                                      ]),
                                      _: 1
                                    }, 8, ["onClick"])
                                  ]),
                                  _: 2
                                }, 1024)
                              ]),
                              _: 2
                            }, 1024);
                          }), 128))
                        ]),
                        _: 1
                      })
                    ]),
                    _: 1
                  }, 8, ["aria-label"])
                ])) : vue.createCommentVNode("", true)
              ]),
              _: 1
            })
          ]),
          _: 1
        });
      };
    }
  };
  const _hoisted_1 = { class: "flex gap-x-2" };
  const _hoisted_2 = { class: "flex gap-x-2 items-end justify-end" };
  const _sfc_main = {
    __name: "SliderHomeContentList",
    props: {
      data: { type: Object, required: true },
      slidercontentform: { type: Object, required: true }
    },
    emits: ["add", "edit", "delete", "set"],
    setup(__props, { emit: __emit }) {
      const { useOrdering } = pkp.modules.useOrdering;
      const { useModal } = pkp.modules.useModal;
      const { openDialog, openSideModal } = useModal();
      const { useLocalize } = pkp.modules.useLocalize;
      const { t } = useLocalize();
      const props = __props;
      const emit = __emit;
      function handleAdd() {
        const formClone = JSON.parse(JSON.stringify(props.slidercontentform));
        openSideModal(_sfc_main$2, {
          modalProps: {
            size: "large"
          },
          mode: "add",
          form: formClone,
          onFormSuccess: (data) => {
            const updatedItems = props.data.items.concat(data || []);
            emit("set", "sliderHomeContentListComponent", { items: updatedItems });
          }
        });
        emit("add");
      }
      function addFromIssue() {
        const formClone = JSON.parse(JSON.stringify(props.slidercontentform));
        openSideModal(_sfc_main$1, {
          modalProps: {
            size: "large"
          },
          mode: "addFromIssue",
          form: formClone,
          onFormSuccess: (data) => {
            const updatedItems = props.data.items.concat(data || []);
            emit("set", "sliderHomeContentListComponent", { items: updatedItems });
          }
        });
        emit("add");
      }
      console.log("props : ", props);
      function handleEdit(itemId) {
        const item = props.data.items.find((i) => i.id === itemId);
        const formClone = JSON.parse(JSON.stringify(props.slidercontentform));
        if (formClone && Array.isArray(formClone.fields)) {
          formClone.fields.forEach((field) => {
            switch (field.name) {
              case "name":
                field.value = item.name ?? field.value;
                break;
              case "sliderImage":
                field.value = item.sliderImage ?? field.value;
                break;
              case "sliderImageLink":
                field.value = item.sliderImageLink ?? field.value;
                break;
              case "content":
                field.value = item.content ?? field.value;
                break;
              case "copyright":
                field.value = item.copyright ?? field.value;
                break;
              case "show_content":
                field.value = item.show_content ?? field.value;
                break;
            }
          });
        }
        openSideModal(_sfc_main$2, {
          modalProps: {
            size: "large"
          },
          mode: "edit",
          itemId,
          form: formClone,
          item,
          onFormSuccess: (data) => {
            const updatedItems = props.data.items.map((i) => i.id === itemId ? data : i);
            emit("set", "sliderHomeContentListComponent", { items: updatedItems });
          }
        });
        emit("edit", itemId);
      }
      function handleDelete(itemId) {
        openDialog({
          name: "delete",
          title: t("common.delete"),
          message: t("common.confirmDelete"),
          actions: [
            {
              label: t("common.yes"),
              isWarnable: true,
              callback: (close) => {
                $.ajax({
                  url: props.data.apiUrl + "/" + itemId,
                  type: "DELETE",
                  headers: {
                    "X-Csrf-Token": pkp.currentUser.csrfToken,
                    "X-Http-Method-Override": "DELETE"
                  },
                  success: (r) => {
                    const filteredItems = props.data.items.filter((i) => i.id !== itemId);
                    emit("set", "sliderHomeContentListComponent", { items: filteredItems });
                    close();
                    emit("delete", itemId);
                  }
                });
              }
            },
            {
              label: t("common.no"),
              callback: (close) => close()
            }
          ],
          modalStyle: "negative"
        });
      }
      function toggleVisibility(itemId) {
        const item = props.data.items.find((i) => i.id === itemId);
        const newVisibility = !item.show_content;
        $.ajax({
          url: props.data.apiUrl + "/toggleVisibility/" + itemId,
          type: "POST",
          headers: {
            "X-Csrf-Token": pkp.currentUser.csrfToken
          },
          data: { show_content: newVisibility },
          success: (r) => {
            const updatedItems = props.data.items.map((i) => i.id === itemId ? { ...i, show_content: newVisibility } : i);
            emit("set", "sliderHomeContentListComponent", { items: updatedItems });
          }
        });
      }
      const {
        items: orderedItems,
        sortingEnabled,
        startSorting,
        saveSorting,
        moveUp,
        moveDown
      } = useOrdering({
        items: vue.computed(() => props.data.items),
        columns: vue.computed(() => props.data.columns),
        onSave: async (orderedItems2) => {
          await saveOrder(orderedItems2);
          emit("set", "sliderHomeContentListComponent", { items: orderedItems2 });
        }
      });
      function saveOrder(orderedItems2) {
        return new Promise((resolve, reject) => {
          $.ajax({
            url: props.data.apiUrl + "/saveOrder",
            type: "POST",
            headers: {
              "X-Csrf-Token": pkp.currentUser.csrfToken
            },
            data: { orderedIds: orderedItems2.map((item) => item.id) },
            success: (r) => {
              resolve(r);
            },
            error: (err) => {
              reject(err);
            }
          });
        });
      }
      const columns = vue.computed(() => props.data.columns);
      return (_ctx, _cache) => {
        const _component_PkpButton = vue.resolveComponent("PkpButton");
        const _component_PkpTableColumn = vue.resolveComponent("PkpTableColumn");
        const _component_PkpTableHeader = vue.resolveComponent("PkpTableHeader");
        const _component_PkpTableCell = vue.resolveComponent("PkpTableCell");
        const _component_PkpTableCellOrder = vue.resolveComponent("PkpTableCellOrder");
        const _component_PkpTableRow = vue.resolveComponent("PkpTableRow");
        const _component_PkpTableBody = vue.resolveComponent("PkpTableBody");
        const _component_PkpTable = vue.resolveComponent("PkpTable");
        const _directive_strip_unsafe_html = vue.resolveDirective("strip-unsafe-html");
        return vue.openBlock(), vue.createElementBlock("div", {
          class: vue.normalizeClass(["sliderHomeContentList pb-5", { "-isOrdering": _ctx.isOrdering }])
        }, [
          vue.createVNode(_component_PkpTable, null, vue.createSlots({
            "top-controls": vue.withCtx(() => [
              vue.createElementVNode("div", _hoisted_1, [
                !vue.unref(sortingEnabled) ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 0 }, [
                  vue.createVNode(_component_PkpButton, {
                    onClick: handleAdd,
                    class: "bg bg-default"
                  }, {
                    default: vue.withCtx(() => [
                      vue.createTextVNode(vue.toDisplayString(__props.data.ButtonLabelAdd), 1)
                    ]),
                    _: 1
                  }),
                  vue.createVNode(_component_PkpButton, {
                    onClick: addFromIssue,
                    class: "bg bg-default"
                  }, {
                    default: vue.withCtx(() => [
                      vue.createTextVNode(vue.toDisplayString(__props.data.ButtonLabeladdFromIssue), 1)
                    ]),
                    _: 1
                  })
                ], 64)) : vue.createCommentVNode("", true),
                vue.createVNode(_component_PkpButton, {
                  onClick: _cache[0] || (_cache[0] = ($event) => vue.unref(sortingEnabled) ? vue.unref(saveSorting)() : vue.unref(startSorting)()),
                  class: "bg bg-default"
                }, {
                  default: vue.withCtx(() => [
                    vue.createTextVNode(vue.toDisplayString(vue.unref(sortingEnabled) ? vue.unref(t)("common.save") : vue.unref(t)("common.order")), 1)
                  ]),
                  _: 1
                })
              ])
            ]),
            default: vue.withCtx(() => [
              vue.createVNode(_component_PkpTableHeader, null, {
                default: vue.withCtx(() => [
                  (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(columns.value, (column) => {
                    return vue.openBlock(), vue.createBlock(_component_PkpTableColumn, {
                      id: column.name,
                      key: column.name
                    }, {
                      default: vue.withCtx(() => [
                        vue.createTextVNode(vue.toDisplayString(column.label), 1)
                      ]),
                      _: 2
                    }, 1032, ["id"]);
                  }), 128))
                ]),
                _: 1
              }),
              vue.createVNode(_component_PkpTableBody, null, {
                default: vue.withCtx(() => [
                  (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(vue.unref(orderedItems), (item) => {
                    return vue.openBlock(), vue.createBlock(_component_PkpTableRow, {
                      key: item.id
                    }, {
                      default: vue.withCtx(() => [
                        vue.createVNode(_component_PkpTableCell, null, {
                          default: vue.withCtx(() => [
                            vue.createElementVNode("span", {
                              class: "fa fa-eye pkpIcon--inline",
                              style: vue.normalizeStyle(item.show_content ? "color: green;" : "color: gray;")
                            }, null, 4),
                            vue.createTextVNode(" " + vue.toDisplayString(item.name), 1)
                          ]),
                          _: 2
                        }, 1024),
                        vue.unref(sortingEnabled) ? (vue.openBlock(), vue.createBlock(_component_PkpTableCellOrder, {
                          key: 0,
                          onUp: ($event) => vue.unref(moveUp)(item.id),
                          onDown: ($event) => vue.unref(moveDown)(item.id)
                        }, null, 8, ["onUp", "onDown"])) : (vue.openBlock(), vue.createBlock(_component_PkpTableCell, { key: 1 }, {
                          default: vue.withCtx(() => [
                            vue.createElementVNode("div", _hoisted_2, [
                              vue.createVNode(_component_PkpButton, {
                                onClick: ($event) => handleEdit(item.id),
                                class: "bg bg-default"
                              }, {
                                default: vue.withCtx(() => [
                                  vue.createTextVNode(vue.toDisplayString(vue.unref(t)("common.edit")), 1)
                                ]),
                                _: 1
                              }, 8, ["onClick"]),
                              vue.createVNode(_component_PkpButton, {
                                onClick: ($event) => handleDelete(item.id),
                                class: "bg bg-danger"
                              }, {
                                default: vue.withCtx(() => [
                                  vue.createTextVNode(vue.toDisplayString(vue.unref(t)("common.delete")), 1)
                                ]),
                                _: 1
                              }, 8, ["onClick"]),
                              vue.createVNode(_component_PkpButton, {
                                onClick: ($event) => toggleVisibility(item.id),
                                class: "bg bg-default"
                              }, {
                                default: vue.withCtx(() => [
                                  vue.createTextVNode(vue.toDisplayString(item.show_content ? __props.data.ButtonLabelHide : __props.data.ButtonLabelShow), 1)
                                ]),
                                _: 2
                              }, 1032, ["onClick"])
                            ])
                          ]),
                          _: 2
                        }, 1024))
                      ]),
                      _: 2
                    }, 1024);
                  }), 128))
                ]),
                _: 1
              })
            ]),
            _: 2
          }, [
            __props.data.SliderGridTitle ? {
              name: "label",
              fn: vue.withCtx(() => [
                vue.withDirectives(vue.createElementVNode("span", null, null, 512), [
                  [_directive_strip_unsafe_html, __props.data.SliderGridTitle]
                ])
              ]),
              key: "0"
            } : void 0
          ]), 1024)
        ], 2);
      };
    }
  };
  pkp.registry.registerComponent("SliderHomeContentList", _sfc_main);
})(pkp.modules.vue);
