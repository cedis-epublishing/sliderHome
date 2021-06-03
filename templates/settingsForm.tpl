<form class="pkp_form" id="sliderSettingsForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.sliderHome.controllers.grid.SliderHomeGridHandler" op="submitSettingsForm" plugin="SliderHomePlugin" category="generic" verb="saveForm"}">
	{fbvFormArea id="sliderSettingsFormArea"}
		{fbvFormSection}
					{fbvElement type="text" id="maxHeight" value=$folderId label="plugins.generic.slider.settings.form.maxHeight" maxlength="5" size=$fbvStyles.size.SMALL}
					<span class="instruct">{translate key="plugins.generic.slider.settings.form.maxHeight.description"}</span><br/>
		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormButtons submitText="common.save"}
</form>