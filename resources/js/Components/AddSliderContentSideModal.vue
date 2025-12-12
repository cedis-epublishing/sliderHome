<template>
  <PkpSideModalBody>
    <template #title>
      {{ mode === 'add' ? 'Add Slider Content' : `Edit ${item?.title || 'Item'}` }}
    </template>
    <PkpSideModalLayoutBasic>
        <PkpForm v-bind="form" @success="handleSuccess" />
    </PkpSideModalLayoutBasic>
  </PkpSideModalBody>
</template>

<script setup>
import { inject } from "vue";

const closeModal = inject("closeModal");
const emit = defineEmits(['set']);

// Accept params as component props
const props = defineProps({
	mode: {
		type: String,
		default: 'add'
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
});

props.form.action = props.form.action + props.mode + (props.itemId ? `/${props.itemId}` : '');

function handleSuccess(data) {
	if (props.onFormSuccess) {
		console.log('Add::calling onFormSuccess');
		props.onFormSuccess(data);
	} 
	closeModal();
}
</script>
