(function(vue) {
  "use strict";
  const _hoisted_1$1 = { key: 0 };
  const _hoisted_2$1 = { key: 1 };
  const _sfc_main$1 = {
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
      }
    },
    setup(__props) {
      const { useLocalize } = pkp.modules.useLocalize;
      const closeModal = vue.inject("closeModal");
      const { t } = useLocalize();
      const props = __props;
      console.log("AddSliderContentSideModal props:", props);
      function handleSuccess(data) {
        console.log("Form submitted:", data, "Mode:", props.mode, "ItemId:", props.itemId);
        closeModal();
      }
      function submit() {
        closeModal();
      }
      return (_ctx, _cache) => {
        const _component_PkpForm = vue.resolveComponent("PkpForm");
        const _component_PkpButton = vue.resolveComponent("PkpButton");
        const _component_PkpSideModalLayoutBasic = vue.resolveComponent("PkpSideModalLayoutBasic");
        const _component_PkpSideModalBody = vue.resolveComponent("PkpSideModalBody");
        return vue.openBlock(), vue.createBlock(_component_PkpSideModalBody, null, {
          title: vue.withCtx(() => [
            vue.createTextVNode(vue.toDisplayString(__props.mode === "add" ? "Add Slider Content" : `Edit ${__props.item?.title || "Item"}`), 1)
          ]),
          default: vue.withCtx(() => [
            vue.createVNode(_component_PkpSideModalLayoutBasic, null, {
              default: vue.withCtx(() => [
                __props.form && Object.keys(__props.form).length > 0 ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1$1, [
                  vue.createVNode(_component_PkpForm, vue.mergeProps(__props.form, { onSuccess: handleSuccess }), null, 16)
                ])) : (vue.openBlock(), vue.createElementBlock("div", _hoisted_2$1, [
                  vue.createElementVNode("p", null, vue.toDisplayString(vue.unref(t)("plugins.generic.backendUiExample.sideModalContent")), 1),
                  vue.createElementVNode("pre", null, "mode: " + vue.toDisplayString(__props.mode) + ", hasForm: " + vue.toDisplayString(!!__props.form) + ", itemId: " + vue.toDisplayString(__props.itemId), 1),
                  vue.createVNode(_component_PkpButton, { onClick: submit }, {
                    default: vue.withCtx(() => [
                      vue.createTextVNode(vue.toDisplayString(vue.unref(t)("plugins.generic.backendUiExample.sideModalSubmit")), 1)
                    ]),
                    _: 1
                  })
                ]))
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
  const _hoisted_2 = { class: "flex gap-x-2 items-end" };
  const _sfc_main = {
    __name: "SliderHomeContentList",
    props: {
      data: { type: Object, required: true },
      slidercontentform: { type: Object, required: true }
    },
    emits: ["add", "edit", "delete"],
    setup(__props, { emit: __emit }) {
      const { useOrdering } = pkp.modules.useOrdering;
      const { useModal } = pkp.modules.useModal;
      const { openModal, openSideModal } = useModal();
      const props = __props;
      const emit = __emit;
      function handleAdd() {
        openSideModal(_sfc_main$1, {
          modalProps: {
            size: "large"
          },
          mode: "add",
          form: props.slidercontentform
        });
        emit("add");
      }
      function handleEdit(itemId) {
        const item = props.data.items.find((i) => i.id === itemId);
        openSideModal(_sfc_main$1, {
          modalProps: {
            size: "large"
          },
          mode: "edit",
          itemId,
          form: props.slidercontentform,
          item
        });
        emit("edit", itemId);
      }
      function handleDelete(itemId) {
        openModal(_sfc_main$1, {
          modalProps: {
            size: "small"
          },
          mode: "delete",
          itemId
        });
        emit("delete", itemId);
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
          await saveToBackend(orderedItems2);
        }
      });
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
                !vue.unref(sortingEnabled) ? (vue.openBlock(), vue.createBlock(_component_PkpButton, {
                  key: 0,
                  onClick: handleAdd,
                  class: "bg bg-default"
                }, {
                  default: vue.withCtx(() => [
                    vue.createTextVNode(vue.toDisplayString(__props.data.AddSliderContentButtonLabel), 1)
                  ]),
                  _: 1
                })) : vue.createCommentVNode("", true),
                vue.createVNode(_component_PkpButton, {
                  onClick: _cache[0] || (_cache[0] = ($event) => vue.unref(sortingEnabled) ? vue.unref(saveSorting)() : vue.unref(startSorting)()),
                  class: "bg bg-default"
                }, {
                  default: vue.withCtx(() => [
                    vue.createTextVNode(vue.toDisplayString(vue.unref(sortingEnabled) ? "Save Order" : _ctx.t("common.order")), 1)
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
                            vue.createTextVNode(vue.toDisplayString(item.title), 1)
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
                                  vue.createTextVNode(vue.toDisplayString(_ctx.t("common.edit")), 1)
                                ]),
                                _: 1
                              }, 8, ["onClick"]),
                              vue.createVNode(_component_PkpButton, {
                                onClick: ($event) => handleDelete(item.id),
                                class: "bg bg-danger"
                              }, {
                                default: vue.withCtx(() => [
                                  vue.createTextVNode(vue.toDisplayString(_ctx.t("common.delete")), 1)
                                ]),
                                _: 1
                              }, 8, ["onClick"])
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
