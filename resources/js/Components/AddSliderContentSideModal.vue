<template>
  <PkpSideModalBody>
    <template #title>
	  <span v-if ="mode === 'add'">
	  	{{ form.ButtonLabelAdd }}
	  </span>
	  <span v-if ="mode === 'addFromIssue'">
	  	{{ form.ButtonLabeladdFromIssue }}
	  </span>
	  <span v-if ="mode === 'edit'">
		{{ form.ButtonLabelEdit }}: {{ item ? item.name : '' }}
	  </span>
    </template>
    <PkpSideModalLayoutBasic>
        <PkpForm v-bind="localForm" @set="handleSet" @success="handleSuccess" />
    </PkpSideModalLayoutBasic>
  </PkpSideModalBody>
</template>

<script setup>
// ==========================================
// Imports
// ==========================================
import { inject, watch, ref, reactive } from "vue";

// ==========================================
// Composables
// ==========================================
const { useLocalize } = pkp.modules.useLocalize;
const { t } = useLocalize();

const closeModal = inject("closeModal");
const emit = defineEmits(['set']);

// ==========================================
// Props
// ==========================================
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

// ==========================================
// Reactive State
// ==========================================

// Make form reactive locally
const localForm = reactive(props.form);

// ==========================================
// Helper Functions
// ==========================================

// Add hidden fields so the endpoint receives `mode` and `itemId` as parameters when the form posts
const ensureHiddenField = (name, value) => {
	if (!localForm || !Array.isArray(localForm.fields)) return;
	const existing = localForm.fields.find(f => f.name === name);
	if (existing) {
		existing.value = value;
	} else {
		localForm.fields.push({ name, type: 'hidden', value });
	}
};

// ==========================================
// Initialization
// ==========================================

// Initialize hidden fields
ensureHiddenField('mode', props.mode);
ensureHiddenField('itemId', props.itemId ?? '');

// ==========================================
// Watchers
// ==========================================

// Keep hidden fields in sync if props change
watch(() => props.mode, (v) => ensureHiddenField('mode', v));
watch(() => props.itemId, (v) => ensureHiddenField('itemId', v ?? ''));

// ==========================================
// Event Handlers
// ==========================================

function handleSet(formId, updates) {
	Object.assign(localForm, updates);
}

function handleSuccess(data) {
	if (props.onFormSuccess) {
		props.onFormSuccess(data);
	} 
	closeModal();
}

</script>
