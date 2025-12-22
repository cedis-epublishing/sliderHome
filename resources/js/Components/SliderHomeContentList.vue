<template>
	<div class="sliderHomeContentList pb-5" :class="{ '-isOrdering': isOrdering }">
		<PkpTable>
			<template v-if="data.SliderGridTitle" #label>
				<span v-strip-unsafe-html="data.SliderGridTitle"></span>
			</template>

			<template #top-controls >
				<div class="flex gap-x-2">
					<template v-if="!sortingEnabled">
						<PkpButton @click="handleAdd" class="bg bg-default">
							{{ data.ButtonLabelAdd }}
						</PkpButton>
						<!-- Add a button that allows the user to add a slider image by selecting a published publication -->
						 <PkpButton @click="addFromIssue" class="bg bg-default">
							{{ data.ButtonLabeladdFromIssue }}
						</PkpButton>
					</template>
					<PkpButton @click="sortingEnabled ? saveSorting() : startSorting()" class="bg bg-default">
						{{ sortingEnabled ? t('common.save') : t('common.order') }}
					</PkpButton>
				</div>
			</template>

			<PkpTableHeader>
				<PkpTableColumn v-for="column in columns" :id="column.name" :key="column.name">
					{{ column.label }}
				</PkpTableColumn>
			</PkpTableHeader>

			<PkpTableBody>
				<PkpTableRow v-for="item in orderedItems" :key="item.id">
					<PkpTableCell>
						<!-- if show_content -->
						<span class="fa fa-eye pkpIcon--inline"  :style="item.show_content ? 'color: green;' : 'color: gray;'">
						</span>
						{{ item.name }}
					</PkpTableCell>

					<PkpTableCellOrder v-if="sortingEnabled" @up="moveUp(item.id)" @down="moveDown(item.id)" />
					<PkpTableCell v-else>
						<!-- Normal mode actions -->
						<div class="flex gap-x-2 items-end justify-end">
							<PkpButton @click="handleEdit(item.id)" class="bg bg-default">
								{{ t('common.edit') }}
							</PkpButton>
							<PkpButton @click="handleDelete(item.id)" class="bg bg-danger">
								{{ t('common.delete') }}
							</PkpButton>
							<PkpButton @click="toggleVisibility(item.id)" class="bg bg-default">
								{{ item.show_content ? data.ButtonLabelHide : data.ButtonLabelShow }}
							</PkpButton>
						</div>
					</PkpTableCell>
				</PkpTableRow>
			</PkpTableBody>
		</PkpTable>
	</div>
</template>

<script setup>
const { useOrdering } = pkp.modules.useOrdering;
import { computed } from 'vue';
import AddSliderContentSideModal from "./AddSliderContentSideModal.vue";
import SelectIssueSideModal from "./SelectIssueSideModal.vue";
const { useModal } = pkp.modules.useModal;
const { openDialog, openSideModal } = useModal();
const { useLocalize } = pkp.modules.useLocalize;
const { t } = useLocalize();

const props = defineProps({
	data: { type: Object, required: true },
	slidercontentform: { type: Object, required: true },
});

const emit = defineEmits(['add', 'edit', 'delete', 'set']);

function handleAdd() {
	const formClone = JSON.parse(JSON.stringify(props.slidercontentform));
	openSideModal(AddSliderContentSideModal, {
		modalProps: {
			size: 'large',
		},
		mode: 'add',
		form: formClone,
		onFormSuccess: (data) => {
			// data should contain whatever the modal returns (for consistency send { items: [...] }).
			// Update parent state via set so the global state/components are updated:
			const updatedItems = props.data.items.concat(data || []);
			emit('set', 'sliderHomeContentListComponent', { items: updatedItems });
		},
	});
	emit('add');
}

function addFromIssue() {
	// Create a deep clone of the form config so we can populate it
	// with the selected item's values without mutating the shared config.
	const formClone = JSON.parse(JSON.stringify(props.slidercontentform));
	openSideModal(SelectIssueSideModal, {
		modalProps: {
			size: 'large',
		},
		mode: 'addFromIssue',
		form: formClone,
		onFormSuccess: (data) => {
			// data should contain whatever the modal returns (for consistency send { items: [...] }).
			// Update parent state via set so the global state/components are updated:
			const updatedItems = props.data.items.concat(data || []);
			emit('set', 'sliderHomeContentListComponent', { items: updatedItems });
		},
	});
	emit('add');
}
console.log('props : ', props);
function handleEdit(itemId) {
	const item = props.data.items.find(i => i.id === itemId);

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

	openSideModal(AddSliderContentSideModal, {
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
			emit('set', 'sliderHomeContentListComponent', { items: updatedItems });
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
						url: props.data.apiUrl + '/' + itemId,
						type: 'DELETE',
						headers: {
							'X-Csrf-Token': pkp.currentUser.csrfToken,
							'X-Http-Method-Override': 'DELETE',
						},
						success: (r) => {
							const filteredItems = props.data.items.filter((i) => i.id !== itemId);
							// emit set event so parent updates canonical state
							emit('set', 'sliderHomeContentListComponent', { items: filteredItems });
							close();
							emit('delete', itemId);
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

function toggleVisibility(itemId) {
	const item = props.data.items.find(i => i.id === itemId);
	const newVisibility = !item.show_content;

	$.ajax({
		url: props.data.apiUrl + '/toggleVisibility/' + itemId,
		type: 'POST',
		headers: {
			'X-Csrf-Token': pkp.currentUser.csrfToken,
		},
		data: { show_content: newVisibility },
		success: (r) => {
			const updatedItems = props.data.items.map(i => i.id === itemId ? { ...i, show_content: newVisibility } : i);
			emit('set', 'sliderHomeContentListComponent', { items: updatedItems });
		},
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
		emit('set', 'sliderHomeContentListComponent', { items: orderedItems });
	}
});

function saveOrder(orderedItems) {
	// Send the new order to the server
	return new Promise((resolve, reject) => {
		$.ajax({
			url: props.data.apiUrl + '/saveOrder',
			type: 'POST',
			headers: {
				'X-Csrf-Token': pkp.currentUser.csrfToken,
			},
			data: { orderedIds: orderedItems.map(item => item.id) },
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