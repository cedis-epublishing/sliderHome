<template>
    <div class="sliderHomeContentList pb-5" :class="{'-isOrdering': isOrdering}">

		<PkpTable>
			<template v-if="data.SliderGridTitle" #label>
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
					<PkpTableCell>{{ item.title }}</PkpTableCell>
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
import BuiExampleSideModal from "./AddSliderContentSideModal.vue";
const { useModal } = pkp.modules.useModal;
const { openModal, openSideModal } = useModal();

	const props = defineProps({
	data: {type: Object, required: true},
	slidercontentform: {type: Object, required: true},
	});

	const emit = defineEmits(['add', 'edit', 'delete']);

	function handleAdd() {
		openSideModal(BuiExampleSideModal, {
			modalProps: {
				size: 'large',
			},
			mode: 'add',
			form: props.slidercontentform,
		});
		emit('add');
	}

	function handleEdit(itemId) {
		const item = props.data.items.find(i => i.id === itemId);
		openSideModal(BuiExampleSideModal, {
			modalProps: {
				size: 'large',
			},
			mode: 'edit',
			itemId,
			form: props.slidercontentform,
			item,
		});
		emit('edit', itemId);
	}

	function handleDelete(itemId) {
		openModal(BuiExampleSideModal, {
			modalProps: {
				size: 'small',
			},
			mode: 'delete',
			itemId,
		});
		emit('delete', itemId);
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
		await saveToBackend(orderedItems);
	}
});

	const columns = computed(() => props.data.columns);
</script>