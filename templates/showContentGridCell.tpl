<style>

	.isVisible {
		border-color: #ddd;
		color: #00B24E;
	}

	.pkpBadge--button:hover {
		background: none!important;
		border-color: #ddd!important;
		color: #00B24E;
		cursor: auto!important;
	}

</style>

{fbvFormSection  class="formButtons form_buttons"}
		{if $showContent == 1}
			<badge class="pkpBadge pkpBadge--button isVisible"
				:isSuccess="true"
				:isButton="true">
					<i class="fa fa-eye"></i>
			</badge>
		{else}
			<badge class="pkpBadge pkpBadge--button"
				:isSuccess="false"
				:isButton="true">
					<i class="fa fa-eye" style="color:gray;"></i>
			</badge>
		{/if}
{/fbvFormSection}