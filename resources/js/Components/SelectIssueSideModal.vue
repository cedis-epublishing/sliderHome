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

				<div v-if="mode === 'addFromIssue'">
					<PkpTable :aria-label="t('plugins.generic.sliderHome.addFromIssue')">
						<template #top-controls>
							<PkpSearch 
									:searchPhrase="searchPhrase"
									@search-phrase-changed="setSearchPhrase"
							/>
							<div class="flex items-center">
								<div v-if="isSearching">{{ t('common.loading') }}</div>
							</div>
						</template>

						<PkpTableHeader>
							<PkpTableColumn id="identification">{{ t('editor.issues.identification') }}</PkpTableColumn>
							<PkpTableColumn id="title">{{ t('common.title') }}</PkpTableColumn>
							<PkpTableColumn id="actions">&nbsp;</PkpTableColumn>
						</PkpTableHeader>

						<PkpTableBody>
							<PkpTableRow v-for="issue in issueResults" :key="issue.id">
								<PkpTableCell>
									{{ issue.identification || '' }}
								</PkpTableCell>
								<PkpTableCell>
									{{ (issue.title && issue.title[Object.keys(issue.title || {})[0]]) || '' }}
								</PkpTableCell>
								<PkpTableCell>
									<PkpButton @click="selectIssue(issue)">{{ t('common.select') }}</PkpButton>
								</PkpTableCell>
							</PkpTableRow>
						</PkpTableBody>

					</PkpTable>
				</div>
    </PkpSideModalLayoutBasic>
  </PkpSideModalBody>
</template>

<script setup>
import { inject, watch, ref } from "vue";
import debounce from 'debounce';

const { useLocalize } = pkp.modules.useLocalize;
const { t } = useLocalize();

const closeModal = inject("closeModal");
const emit = defineEmits(['set']);
const publishedIssues = ref([]);

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

import AddSliderContentSideModal from "./AddSliderContentSideModal.vue";
const { useModal } = pkp.modules.useModal;
const { openSideModal } = useModal();

// If mode is 'addFromIssue' perform $ajax get request to fetch published issues
if (props.mode === 'addFromIssue') {
	$.ajax({
		url: props.form.searchApiUrl + '?status=3',
		method: 'GET',
		dataType: 'json',
	}).then((response) => {
		if (response) {
			issueResults.value = response.items;
			publishedIssues.value = response.items;
		}
	}).catch((error) => {
		console.error('Error fetching issue ID:', error);
	});
}

// Search state
const searchPhrase = ref('');
const issueResults = ref([]);
const isSearching = ref(false);
let searchRequestId = 0;

function setSearchPhrase(phrase) {
	// update reactive value and trigger debounced search
	searchPhrase.value = phrase;
	doIssueSearch(phrase);
}

const doIssueSearch = debounce(async (phrase) => {
	if (!phrase) {
		issueResults.value = publishedIssues.value;
		return;
	}
	isSearching.value = true;
	const reqId = ++searchRequestId;
	try {
		const url = props.form.searchApiUrl + '?status=3&searchPhrase=' + encodeURIComponent(phrase);
		const res = await fetch(url, { credentials: 'same-origin' });
		if (!res.ok) throw new Error('Network response was not ok');
		const json = await res.json();
		if (reqId !== searchRequestId) return; // stale
		issueResults.value = json.items || [];
	} catch (e) {
		console.error('Issue search error', e);
		issueResults.value = [];
	} finally {
		if (reqId === searchRequestId) isSearching.value = false;
	}
}, 300);

function selectIssue(issue) {
	if (!issue || !props.form) return;
	// populate form fields where appropriate
	props.form.fields.forEach(field => {
		if (field.name === 'name') {
			field.value = issue.identification || '';
		} else if (field.name === 'sliderImageLink') {
			field.value = issue.publishedUrl || '';
		} else if (field.name === 'sliderImage') {
			for (const localeKey in props.form.visibleLocales) {
				const locale = props.form.visibleLocales[localeKey];
				if (issue.coverImage && issue.coverImage[locale]) {
					if (!field.value[locale]) field.value[locale] = {};
					field.value[locale]['temporaryFileId'] = issue.coverImage[locale] || '';
					field.value[locale]['altText'] = issue.coverImageAltText && issue.coverImageAltText[locale] ? issue.coverImageAltText[locale] : '';
				}
			}
		} else if (field.name === 'content') {
			// map localized titles into form locales
			for (const localeKey in props.form.visibleLocales || {}) {
				const locale = props.form.visibleLocales[localeKey];
				if (!field.value) field.value = {};
				field.value[locale] = (issue.title && issue.title[locale]) || '';
			}
		}
	});
	closeModal();
	openSideModal(AddSliderContentSideModal, {
				modalProps: {
					size: 'large',
				},
				mode: 'addFromIssue',
				form: props.form,
				itemId: issue.id,
				item: issue,
				onFormSuccess: (data) => {
					if (props.onFormSuccess) {
						props.onFormSuccess(data);
					} 
					closeModal();
				},
			});
}

</script>
