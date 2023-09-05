 <tab id="sliderhome" label="{translate key="plugins.generic.sliderHome.tabname"}">
	
	{capture assign=sliderHomeGridUrl}{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.sliderHome.controllers.grid.SliderHomeGridHandler" op="fetchGrid" escape=false}{/capture}
	{load_url_in_div id="sliderHomeGridContainer" url=$sliderHomeGridUrl}
	
	<list-panel :items="items" title="List Panel"> </list-panel>

	<submissions-list-panel
		v-bind="components.submissions"
		@set="set"
	>

		<template v-slot:item="{ldelim}item{rdelim}">
			<div class="listPanel__itemSummary">
				<label>
					<input
						type="checkbox"
						name="selectedSubmissions[]"
						:value="item.id"
						v-model="selectedSubmissions"
					/>
					<span 
						class="listPanel__itemSubTitle" 
						v-html="localize(
							item.publications.find(p => p.id == item.currentPublicationId).fullTitle,
							item.publications.find(p => p.id == item.currentPublicationId).locale
						)"
					>
					</span>
				</label>
				<pkp-button element="a" :href="item.urlWorkflow" style="margin-left: auto;">
					{{ __('common.view') }}
				</pkp-button>
			</div>
		</template>
	</submissions-list-panel>

	<doi-list-panel
		v-bind="components.submissionDoiListPanel"
		@set="set"
	/>

	<pkp-form
		v-bind="components.{$smarty.const.FORM_SLIDER_SETTINGS}"
		@set="set"
	/>
</tab>