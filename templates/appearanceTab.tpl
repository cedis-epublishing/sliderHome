 <tab id="sliderhome" label="{translate key="plugins.generic.sliderHome.tabname"}">
	
	{capture assign=sliderHomeGridUrl}{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.sliderHome.controllers.grid.SliderHomeGridHandler" op="fetchGrid" escape=false}{/capture}
	{load_url_in_div id="sliderHomeGridContainer" url=$sliderHomeGridUrl}

	<slider-home-list-panel
		v-bind="components.{$smarty.const.FORM_SLIDER_LIST_PANEL}"
		@set="set">
	</slider-home-list-panel>

	<pkp-form
		v-bind="components.{$smarty.const.FORM_SLIDER_SETTINGS}"
		@set="set"
	/>
</tab>