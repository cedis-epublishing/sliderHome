(function(vue) {
  "use strict";
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
      },
      onFormSuccess: { type: Function, default: null }
    },
    emits: ["set"],
    setup(__props, { emit: __emit }) {
      const { useLocalize } = pkp.modules.useLocalize;
      const closeModal = vue.inject("closeModal");
      const { t } = useLocalize();
      const props = __props;
      props.form.action = props.form.action + props.mode + (props.itemId ? `/${props.itemId}` : "");
      function handleSuccess(data) {
        if (props.onFormSuccess) {
          console.log("Add::calling onFormSuccess");
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
            vue.createTextVNode(vue.toDisplayString(__props.mode === "add" ? "Add Slider Content" : `Edit ${__props.item?.title || "Item"}`), 1)
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
  const _hoisted_1 = { class: "flex gap-x-2" };
  const _hoisted_2 = { class: "flex gap-x-2 items-end" };
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
        openSideModal(_sfc_main$1, {
          modalProps: {
            size: "large"
          },
          mode: "add",
          form: props.slidercontentform,
          onFormSuccess: (data) => {
            const updatedItems = props.data.items.concat(data || []);
            emit("set", "sliderHomeContentListComponent", { items: updatedItems });
            console.log("handleAdd - emitted set with data:", updatedItems);
          }
        });
        emit("add");
      }
      function handleEdit(itemId) {
        const item = props.data.items.find((i) => i.id === itemId);
        console.log("handleEdit - form:", props.slidercontentform, "item:", item);
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
        openSideModal(_sfc_main$1, {
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
                  url: props.slidercontentform.action + itemId,
                  type: "DELETE",
                  headers: {
                    "X-Csrf-Token": pkp.currentUser.csrfToken,
                    "X-Http-Method-Override": "DELETE"
                  },
                  // error: this.ajaxErrorCallback,
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
            url: props.slidercontentform.action + "saveOrder",
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
        const _component_icon = vue.resolveComponent("icon");
        const _component_badge = vue.resolveComponent("badge");
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
                    vue.createTextVNode(vue.toDisplayString(vue.unref(sortingEnabled) ? "Save Order" : vue.unref(t)("common.order")), 1)
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
                            vue.createTextVNode(vue.toDisplayString(item.name), 1)
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
                vue.createVNode(_component_icon, { icon: "Bug" }),
                vue.createVNode(_component_badge, { label: "32 submissions" }, {
                  default: vue.withCtx(() => [..._cache[1] || (_cache[1] = [
                    vue.createTextVNode("32", -1)
                  ])]),
                  _: 1
                }),
                _cache[2] || (_cache[2] = vue.createElementVNode("i", { class: "fa-solid fa-eye" }, null, -1)),
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
