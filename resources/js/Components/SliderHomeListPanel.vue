<template>
    <div>
        <pkp-list-panel :items="items" title="title">
            <pkp-header slot="header">
                <h2>{{ title }}</h2>
                <spinner v-if="isLoading"></spinner>
                <pkp-button ref="addSliderButton" @click="openAddModal" style="float: right;">
                    {{ addLabel }}
                </pkp-button>
            </pkp-header>{{ items.length }}
            <template v-slot:item-title="{item}">
                <pkp-badge v-if="item.show_content" :isSuccess="true">
                    <pkp-icon icon="fa-light fa-eye" :inline="true"></pkp-icon>
                    {{ item.name }}
                </pkp-badge>
                <pkp-badge v-if="!item.show_content">
                    {{ item.name }}
                </pkp-badge>
            </template>
            <template v-slot:item-actions="{item}">
                <pkp-button @click="toggleVisibility(item.id)">
                    Enable/Disable
                </pkp-button>
                <pkp-button @click="openEditModal(item.id)">
                    {{ __('common.edit') }}
                </pkp-button>
                <pkp-button :isWarnable="true" @click="openDeleteModal(item.id)">
                    {{ __('common.delete') }}
                </pkp-button>
            </template>
        </pkp-list-panel>
        <pkp-modal
            :closeLabel="__('common.close')"
            name="form"
            :title="gdsfdf"
            @closed="formModalClosed">
            <pkp-form
                    v-bind="activeForm"
                    @set="updateForm"
                    @success="formSuccess"
            />
        </pkp-modal>
    </div>
</template>

<style scoped>
.custom-styling {
    margin-top: 10px;
    height: 30px;
    background-color: blue;
}
</style>
<script>
import cloneDeep from 'clone-deep';

function arraymove(arr, fromIndex, toIndex) {
    var element = arr[fromIndex];
    arr.splice(fromIndex, 1);
    arr.splice(toIndex, 0, element);
}

export default {
    name: 'sliderHomeListPanelComponent',
	mixins: [pkp.vueMixins.dialog],
    props: {
        items: {
			type: Array,
			default() {
				return []; // sliderImages
			},
		},
        title: {
            type: String,
            required: true,
        },
        form: {
			type: Object,
			required: true,
		},
        addLabel: {
			type: String,
			required: true,
		},
		confirmDeleteMessage: {
			type: String,
			required: true,
		},
		apiUrl: {
			type: String,
			required: true
		}
    },
    data() {
        return {
            activeForm: null,
			activeFormTitle: '',
			isLoading: false,
			isOrdering: false,
			resetFocusTo: null,
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
			this.activeFormTitle = '';
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
			if (this.activeForm.method === 'POST') {
				this.offset = 0;
				this.get();
				pkp.eventBus.$emit('add:highlight', item);
			} else {
				this.setItems(
					this.items.map((i) => (i.id === item.id ? item : i)),
					this.itemsMax
				);
				pkp.eventBus.$emit('update:highlight', item);
			}
			this.$modal.hide('form');
		},

		/**
		 * Open the modal to add an item
		 */
		toggleVisibility(id) {
			const item = this.getItem(id);
			if (!item) return;
			item.show_content = !item.show_content;
			$.ajax({
				url: this.apiUrl + '/toggleShow/' + id,
				type: 'POST',
				headers: {
					'X-Csrf-Token': pkp.currentUser.csrfToken,
					'X-Http-Method-Override': 'PUT',
				},
				error: this.ajaxErrorCallback,
				success: (r) => {
					this.setItems(
						this.items.filter((i) => i.id !== id),
						this.itemsMax
					);
					this.$modal.hide('delete');
					this.setFocusIn(this.$el);
				},
			});
		},

		getItem(id) {
			const item = this.items.find((a) => a.id === id);
			if (typeof item === 'undefined') {
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
			let activeForm = cloneDeep(this.form);
			activeForm.action = this.apiUrl;
			activeForm.method = 'POST';
			this.activeForm = activeForm;
			this.activeFormTitle = this.i18nAdd;
			this.$modal.show('form');
		},

		/**
		 * Open delete modal
		 *
		 * @param {Number} id
		 */
		openDeleteModal(id) {
			const item = this.getItem(id);
			if (!item) return;

			this.openDialog({
				name: 'delete',
				title: this.__('common.delete'),
				message: this.replaceLocaleParams(this.confirmDeleteMessage, {
					title: item.name,
				}),
				actions: [
					{
						label: this.__('common.yes'),
						isPrimary: true,
						callback: () => {
							$.ajax({
								url: this.apiUrl + '/' + id,
								type: 'POST',
								headers: {
									'X-Csrf-Token': pkp.currentUser.csrfToken,
									'X-Http-Method-Override': 'DELETE',
								},
								error: this.ajaxErrorCallback,
								success: (r) => {
									this.setItems(
										this.items.filter((i) => i.id !== id),
										this.itemsMax
									);
									this.$modal.hide('delete');
									this.setFocusIn(this.$el);
								},
							});
						},
					},
					{
						label: this.__('common.no'),
						isWarnable: true,
						callback: () => this.$modal.hide('delete'),
					},
				],
			});
		},

		/**
		 * Open the modal to edit an item
		 *
		 * @param {Number} id
		 */
		openEditModal(id) {
			this.resetFocusTo = document.activeElement;

			const highlight = this.items.find((highlight) => highlight.id === id);
			if (!highlight) {
				this.ajaxErrorCallback({});
				return;
			}

			let activeForm = cloneDeep(this.form);
			activeForm.action = this.apiUrl + '/' + id;
			activeForm.method = 'PUT';
			activeForm.fields = activeForm.fields.map((field) => {
				if (Object.keys(highlight).includes(field.name)) {
					field.value = highlight[field.name];
				}
				return field;
			});
			this.activeForm = activeForm;
			this.activeFormTitle = this.i18nEdit;
			this.$modal.show('form');
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
				url: this.apiUrl + '/order',
				type: 'POST',
				context: this,
				data: {
					sequence: this.items.map(({id, sequence}) => ({id, sequence})),
				},
				headers: {
					'X-Csrf-Token': pkp.currentUser.csrfToken,
					'X-Http-Method-Override': 'PUT',
				},
				error: this.ajaxErrorCallback,
				success: (r) => {
					this.setItems(r.items, r.itemsMax);
				},
				complete: () => {
					this.isLoading = false;
					this.isOrdering = false;
				},
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
			this.$emit('set', this.id, {
				items,
				itemsMax,
			});
		},

		/**
		 * Update form values when they change
		 *
		 * @param {String} formId
		 * @param {Object} data
		 */
		updateForm(formId, data) {
			let activeForm = {...this.activeForm};
			Object.keys(data).forEach(function (key) {
				activeForm[key] = data[key];
			});
			this.activeForm = activeForm;
		},
    },
};
</script>