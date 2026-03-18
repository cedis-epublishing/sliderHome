 <tab id="sliderhome" label="{translate key="plugins.generic.sliderHome.tabname"}">

 	<slider-home-content-list
		:data="components.{$smarty.const.SLIDER_CONTENT_LIST}"
		:slidercontentform="components.{$smarty.const.FORM_SLIDER_CONTENT}"
		@set="set">
 	</slider-home-content-list>

 	<pkp-form v-bind="components.{$smarty.const.FORM_SLIDER_SETTINGS}" @set="set" />
</tab>