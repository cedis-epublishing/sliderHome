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
        <PkpForm v-bind="form" @success="handleSuccess" />
    </PkpSideModalLayoutBasic>
  </PkpSideModalBody>
</template>

<script setup>
import { inject, watch, ref } from "vue";
// import { toggleLocale } from 'pkp/modules/FormLocales';

const { useLocalize } = pkp.modules.useLocalize;
const { t } = useLocalize();

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

// add hidden fields so the endpoint receives `mode` and `itemId` as parameters when the form posts.
const ensureHiddenField = (name, value) => {
	if (!props.form || !Array.isArray(props.form.fields)) return;
	const existing = props.form.fields.find(f => f.name === name);
	if (existing) {
		existing.value = value;
	} else {
		props.form.fields.push({ name, type: 'hidden', value });
	}
};

// initialize hidden fields
ensureHiddenField('mode', props.mode);
ensureHiddenField('itemId', props.itemId ?? '');

// keep them in sync if props change
watch(() => props.mode, (v) => ensureHiddenField('mode', v));
watch(() => props.itemId, (v) => ensureHiddenField('itemId', v ?? ''));

function handleSuccess(data) {
	if (props.onFormSuccess) {
		props.onFormSuccess(data);
	} 
	closeModal();
}

</script>
