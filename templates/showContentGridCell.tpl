{fbvFormSection  class="formButtons form_buttons"}
	{* {fbvElement type="checkbox" id="showContent" name="showContent" checked=$showContent} *}
    		<button class="pkp_button ">
			{translate key="lll"}
		</button>
		<badge
			v-if="showContent"
			:is-success="true"
			>
			{$showContent}
		</badge>
{/fbvFormSection}