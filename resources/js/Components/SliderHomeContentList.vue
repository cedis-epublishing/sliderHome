<template>
    <div class="sliderHomeContentList pb-5" :class="{'-isOrdering': isOrdering}">
		<PkpTable>
			<template v-if="data.SliderGridTitle" #label>
				<icon icon="Bug"></icon>
				<badge label="32 submissions">32</badge>
				<i class="fa-solid fa-eye"></i>
				<span v-strip-unsafe-html="data.SliderGridTitle"></span>
			</template>

			<template #top-controls>
				<div class="flex gap-x-2">
					<template v-if="!sortingEnabled">
						<PkpButton @click="handleAdd" class="bg bg-default">
							{{ data.AddSliderContentButtonLabel }}
						</PkpButton>
					</template>
					<PkpButton @click="sortingEnabled ? saveSorting() : startSorting()" class="bg bg-default">
						{{ sortingEnabled ? 'Save Order' : t('common.order') }}
					</PkpButton>
				</div>
			</template>

			<PkpTableHeader>
				<PkpTableColumn v-for="column in columns"
					:id="column.name"
					:key="column.name"
				>
					{{ column.label }}
				</PkpTableColumn>
			</PkpTableHeader>

			<PkpTableBody>
				<PkpTableRow v-for="item in orderedItems" :key="item.id">
					<PkpTableCell>{{ item.name }}</PkpTableCell>
					<PkpTableCellOrder
						v-if="sortingEnabled"
						@up="moveUp(item.id)"
						@down="moveDown(item.id)"
					/>
					<PkpTableCell v-else>
						<!-- Normal mode actions -->
						 <div class="flex gap-x-2 items-end">
							
							<PkpButton @click="handleEdit(item.id)" class="bg bg-default">
								{{ t('common.edit') }}
							</PkpButton>
							<PkpButton @click="handleDelete(item.id)" class="bg bg-danger">
								{{ t('common.delete') }}
							</PkpButton>
						</div>
					</PkpTableCell>
				</PkpTableRow>
			</PkpTableBody>
		</PkpTable>
	</div>
</template>

<script setup>
const {useOrdering} = pkp.modules.useOrdering;
import {computed} from 'vue';
import SliderContentSideModal from "./AddSliderContentSideModal.vue";
const { useModal } = pkp.modules.useModal;
const { openDialog, openSideModal } = useModal();
const { useLocalize } = pkp.modules.useLocalize;
const { t } = useLocalize();

	const props = defineProps({
	data: {type: Object, required: true},
	slidercontentform: {type: Object, required: true},
	});

	const emit = defineEmits(['add', 'edit', 'delete', 'set']);

	function handleAdd() {
		openSideModal(SliderContentSideModal, {
			modalProps: {
				size: 'large',
			},
			mode: 'add',
			form: props.slidercontentform,
			onFormSuccess: (data) => {
				// data should contain whatever the modal returns (for consistency send { items: [...] }).
				// Update parent state via set so the global state/components are updated:
				const updatedItems = props.data.items.concat(data || []);
				emit('set', 'sliderHomeContentListComponent', {items: updatedItems});
				console.log('handleAdd - emitted set with data:', updatedItems);
			},
		});
		emit('add');
	}

	function handleEdit(itemId) {
		const item = props.data.items.find(i => i.id === itemId);
		console.log('handleEdit - form:', props.slidercontentform, 'item:', item);

		// Create a deep clone of the form config so we can populate it
		// with the selected item's values without mutating the shared config.
		const formClone = JSON.parse(JSON.stringify(props.slidercontentform));

		// Map item properties to form fields by matching field.name
		if (formClone && Array.isArray(formClone.fields)) {
			formClone.fields.forEach(field => {
				switch (field.name) {
					case 'name':
						field.value = item.name ?? field.value;
						break;
					case 'sliderImage':
						// sliderImage expects a locale-keyed object with uploadName/altText
						field.value = item.sliderImage ?? field.value;
						break;
					case 'sliderImageLink':
						field.value = item.sliderImageLink ?? field.value;
						break;
					case 'content':
						field.value = item.content ?? field.value;
						break;
					case 'copyright':
						field.value = item.copyright ?? field.value;
						break;
					case 'show_content':
						field.value = item.show_content ?? field.value;
						break;
					default:
						break;
				}
			});
		}

		openSideModal(SliderContentSideModal, {
			modalProps: {
				size: 'large',
			},
			mode: 'edit',
			itemId: itemId,
			form: formClone,
			item: item,
			onFormSuccess: (data) => {
				// update items with returned data
				const updatedItems = props.data.items.map(i => i.id === itemId ? data : i);
				// emit set event so parent updates canonical state
				emit('set', 'sliderHomeContentListComponent', {items: updatedItems});
			},
		});
		emit('edit', itemId);
	}

	function handleDelete(itemId) {

		openDialog({
			name: 'delete',
			title: t('common.delete'),
			message: t('common.confirmDelete'),
			actions: [
				{
					label: t('common.yes'),
					isWarnable: true,
					callback: (close) => {
						$.ajax({
							url: props.slidercontentform.action + itemId,
							type: 'DELETE',
							headers: {
								'X-Csrf-Token': pkp.currentUser.csrfToken,
								'X-Http-Method-Override': 'DELETE',
							},
							// error: this.ajaxErrorCallback,
						success: (r) => {
							const filteredItems = props.data.items.filter((i) => i.id !== itemId);
							// emit set event so parent updates canonical state
							emit('set', 'sliderHomeContentListComponent', {items: filteredItems});
							close();
							emit('delete', itemId);
							// this.setFocusIn(this.$el);
						},
						});
					},
				},
				{
					label: t('common.no'),
					callback: (close) => close(),
				},
			],
			modalStyle: 'negative',
		});
	}

const {
	items: orderedItems,
	sortingEnabled,
	startSorting,
	saveSorting,
	moveUp,
	moveDown,
} = useOrdering({
	items: computed(() => props.data.items),
	columns: computed(() => props.data.columns),
	onSave: async (orderedItems) => {
		await saveOrder(orderedItems);
		emit('set', 'sliderHomeContentListComponent', {items: orderedItems});
	}
});

function saveOrder(orderedItems) {
	// Send the new order to the server
	return new Promise((resolve, reject) => {
		$.ajax({
			url: props.slidercontentform.action + 'saveOrder',
			type: 'POST',
			headers: {
				'X-Csrf-Token': pkp.currentUser.csrfToken,
			},
			data: {orderedIds: orderedItems.map(item => item.id)},
			success: (r) => {
				resolve(r);
			},
			error: (err) => {
				reject(err);
			},
		});
	});
}

	const columns = computed(() => props.data.columns);
</script>