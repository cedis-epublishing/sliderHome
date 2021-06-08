<script>
	$(function() {ldelim}
		// Attach the form handler.
		$('#sliderSettingsTabForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

{capture assign=sliderHomeGridUrl}{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.sliderHome.controllers.grid.SliderHomeGridHandler" op="fetchGrid" escape=false}{/capture}
{load_url_in_div id="sliderHomeGridContainer" url=$sliderHomeGridUrl}

<form class="pkp_form" id="sliderSettingsTabForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.sliderHome.controllers.tab.SliderHomeSettingsTabFormHandler" tab="sliderHome" plugin="SliderHomePlugin" category="generic" op="saveFormData"}">
	{csrf}

	{fbvFormArea id="sliderSettingsTabFormArea"}
		{fbvFormSection}
			<span class="instruct">{translate key="plugins.generic.slider.settings.form.maxHeight.description"}</span><br/>
			{fbvElement type="text" id="maxHeight" value=$maxHeight label="plugins.generic.slider.settings.form.maxHeight" maxlength="3" size=$fbvStyles.size.SMALL}
			<br>
			<span class="instruct">{translate key="plugins.generic.slider.settings.form.speed.description"}</span><br/>
			{fbvElement type="text" id="speed" value=$speed label="plugins.generic.slider.settings.form.speed" maxlength="8" size=$fbvStyles.size.SMALL}
			<br>
			<span class="instruct">{translate key="plugins.generic.slider.settings.form.delay.description"}</span><br/>
			{fbvElement type="text" id="delay" value=$delay label="plugins.generic.slider.settings.form.delay" maxlength="8" size=$fbvStyles.size.SMALL}
			<br>
			{fbvFormSection for="sendAnnouncementNotification" list="true"}
				{fbvElement type="checkbox" name="stopOnLastSlideCheckbox" id="stopOnLastSlide" checked=$stopOnLastSlide label="plugins.generic.slider.settings.form.stopOnLastSlide" inline=true}
			{/fbvFormSection}
		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormButtons submitText="common.save" hideCancel=true}
</form>
