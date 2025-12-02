 <tab id="sliderhome" label="{translate key="plugins.generic.sliderHome.tabname"}">

	<slider-home-content-list :data="{$smarty.const.SLIDER_CONTENT_LIST}" :slidercontentform="{$smarty.const.FORM_SLIDER_CONTENT}"></slider-home-content-list>

	<pre>print_r({$smarty.const.SLIDER_CONTENT_LIST})</pre>
	<pre>print_r({$smarty.const.FORM_SLIDER_CONTENT})</pre>
	{debug}

	<pkp-form
		v-bind="components.{$smarty.const.FORM_SLIDER_SETTINGS}"
		@set="set"
	/>
</tab>