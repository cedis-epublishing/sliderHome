<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#sliderContentForm').pkpHandler(
			'$.pkp.controllers.form.FileUploadFormHandler',
			{ldelim}
				$uploader: $('#sliderImageUploader'),
				$preview: $('#sliderImagePreview'),
				uploaderOptions: {ldelim}
					uploadUrl: {url|json_encode op="uploadFile" escape=false},
					baseUrl: {$baseUrl|json_encode},
					filters: {ldelim}
						mime_types : [
							{ldelim} title : "Image files", extensions : "jpg,jpeg,png,svg" {rdelim}
						]
					{rdelim}
				{rdelim}
			{rdelim}
		);
	{rdelim});
			

</script>

{capture assign=actionUrl}{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.sliderHome.controllers.grid.SliderHomeGridHandler" op="updateSliderContent" escape=false}{/capture}

<form class="pkp_form" id="sliderContentForm" method="post" action="{$actionUrl}">
	{csrf}
	{if $sliderContentId}
		<input type="hidden" name="sliderContentId" value="{$sliderContentId|escape}" />
	{/if}

	{fbvFormArea id="sliderImage" title="plugins.generic.sliderHome.sliderName"}
	
		{fbvFormSection}
			{fbvElement type="text" label="plugins.generic.sliderHome.name" id="name" required="true" value=$name maxlength="50" inline=true multilingual=false size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
		{fbvFormSection label="plugins.generic.sliderHome.sliderImage"}
			{include file="controllers/fileUploadContainer.tpl" id="sliderImageUploader"}
			<input type="hidden" name="temporaryFileId" id="temporaryFileId" value="" />
			{if !$sliderContentId}
				{translate key="plugins.generic.sliderHome.imageDetailsHint"}
			{/if}
		{/fbvFormSection}
		{fbvFormSection id="sliderImagePreview"}
			{if $sliderImage != ''}
				<div class="pkp_form_file_view pkp_form_image_view">
					<div class="img">
						<img src="{$publicFilesDir}/{$sliderImage|escape:"url"}{'?'|uniqid}" {if $sliderImageAlt !== ''} alt="{$sliderImageAlt|escape}"{/if}>
					</div>

					<div class="data">
						<span class="title">
							{translate key="common.altText"}
						</span>
						<span class="value">
							{fbvElement type="text" id="sliderImageAltText" label="common.altTextInstructions" multilingual=true value=$sliderImageAltText}
						</span>
						<span class="value">
							{fbvElement type="text" id="sliderImageLink" label="plugins.generic.sliderHome.sliderImageLink" value=$sliderImageLink}
						</span>

						<div id="{$deleteSliderImageLinkAction->getId()}" class="actions">
							{include file="linkAction/linkAction.tpl" action=$deleteSliderImageLinkAction contextId="sliderContentForm"}
						</div>
					</div>
				</div>
			{/if}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormArea id="sliderContentFormArea" class="border"}

		{fbvFormSection label="plugins.generic.sliderHome.sliderTextContentLabel"}
			{fbvElement type="textarea" rich=true label="plugins.generic.sliderHome.content" id="content" value=$content inline=true multilingual=true size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}

		{fbvFormSection}
			{fbvElement type="text" label="plugins.generic.sliderHome.copyright" id="copyright" value=$copyright maxlength="50" inline=true multilingual=true size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}

		{fbvFormSection list=true}
					{fbvElement type="checkbox" id="showContent" name="showContent" checked=$showContent label="plugins.generic.sliderHome.showSliderContent"}				
		{/fbvFormSection}
		
		{fbvFormSection class="formButtons"}
			{fbvElement type="submit" class="submitFormButton" id="submitFormButton" label="common.save"}
		{/fbvFormSection}

	{/fbvFormArea}

</form>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

