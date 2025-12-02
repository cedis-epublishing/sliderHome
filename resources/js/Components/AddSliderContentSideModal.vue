<template>
  <PkpSideModalBody>
    <template #title>
      {{ mode === 'add' ? 'Add Slider Content' : `Edit ${item?.title || 'Item'}` }}
    </template>
    <PkpSideModalLayoutBasic>
      <div v-if="form && Object.keys(form).length > 0">
        <PkpForm v-bind="form" @success="handleSuccess" />
      </div>
      <div v-else>
        <p>{{ t("plugins.generic.backendUiExample.sideModalContent") }}</p>
        <pre>mode: {{ mode }}, hasForm: {{ !!form }}, itemId: {{ itemId }}</pre>
        <PkpButton @click="submit">{{
          t("plugins.generic.backendUiExample.sideModalSubmit")
        }}</PkpButton>
      </div>
    </PkpSideModalLayoutBasic>
  </PkpSideModalBody>
</template>

<script setup>
import { inject } from "vue";

const { useLocalize } = pkp.modules.useLocalize;

const closeModal = inject("closeModal");

const { t } = useLocalize();

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
	}
});

console.log('AddSliderContentSideModal props:', props);

function handleSuccess(data) {
	console.log('Form submitted:', data, 'Mode:', props.mode, 'ItemId:', props.itemId);
	closeModal();
}

function submit() {
	closeModal();
}
</script>
