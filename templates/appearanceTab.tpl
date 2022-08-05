 <tab id="sliderhome" label="{translate key="plugins.generic.sliderHome.tabname"}">
	
	{capture assign=sliderHomeGridUrl}{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.sliderHome.controllers.grid.SliderHomeGridHandler" op="fetchGrid" escape=false}{/capture}
	{load_url_in_div id="sliderHomeGridContainer" url=$sliderHomeGridUrl}
	
	<fieldset>
		<list-panel
			v-bind="components.contentList"
			@set="set"
			title={translate key="plugins.generic.sliderHome.gridTitle"} >

			<template slot="header">
					<pkp-header>
						<h2>{translate key="plugins.generic.sliderHome.gridTitle"}</h2>
						<template slot="actions">
							<pkp-button @click="add_item">{translate key="plugins.generic.sliderHome.addSliderContent"}</pkp-button>
							<pkp-button @click="openModal">Reset Defaults</pkp-button>
							<template v-if="canOrder">
								<pkp-button
									class="listPanel--catalog__orderToggle"
									icon="sort"
									:isActive="isOrdering"
									@click="toggleOrdering"
								>
									ORDER{{ orderingLabel }}
								</pkp-button>
								<pkp-button
									v-if="isOrdering"
									class="listPanel--catalog__orderCancel"
									:isWarnable="true"
									@click="cancelOrdering"
								>
									{{ __('common.cancel') }}
								</pkp-button>
							</template>
						</template>
					</pkp-header>
					<div v-if="canSelectAll" class="listPanel__selectAllWrapper">
						<input
							type="checkbox"
							:id="id + '-selectAll'"
							:checked="isSelectAllOn"
							@click="toggleSelectAll"
						/>
						<label class="listPanel__selectAllLabel" :for="id + '-selectAll'">
							Select All
						</label>
					</div>
				</template>
				
				<template v-slot:item="{ldelim}item{rdelim}">
					<div class="listPanel__itemSummary">
						<label class="listPanel__selectWrapper">
							<div class="listPanel__selector">
								<input
									type="checkbox"
									name="submissions[]"
									:value="item.id"
									v-model="selected"
								/>
							</div>
							<div class="listPanel__itemIdentity">
								<div class="listPanel__itemTitle">
									{{ item['_data']['name'] }}
								</div>
								<div class="listPanel__itemSubTitle">
									{{ item['_data']['showContent'] }}
								</div>
							</div>
						</label>
					</div>
					<pkp-button @click="openModal(item.title)">{translate key="grid.action.edit"}</pkp-button>
					<pkp-button @click="openModal(item.title)" :isWarnable="true">{translate key="grid.action.delete"}</pkp-button>		
				</template>
			</list-panel>
	</fieldset>
	

{* <fieldset class="previewListPanelSelect">
			<legend class="-screenReader">List Panel with Select</legend>
			<list-panel :items="components.contentList.items">
				<template slot="header">
					<pkp-header>
						<h2>List Panel with Select</h2>
					</pkp-header>
					<div v-if="canSelectAll" class="listPanel__selectAllWrapper">
						<input
							type="checkbox"
							:id="id + '-selectAll'"
							:checked="isSelectAllOn"
							@click="toggleSelectAll"
						/>
						<label class="listPanel__selectAllLabel" :for="id + '-selectAll'">
							Select All
						</label>
					</div>
				</template>
				<template v-slot:item="{item}">
					<div class="listPanel__itemSummary">
						<label class="listPanel__selectWrapper">
							<div class="listPanel__selector">
								<input
									type="checkbox"
									name="submissions[]"
									:value="item.id"
									v-model="selected"
								/>
							</div>
							<div class="listPanel__itemIdentity">
								<div class="listPanel__itemTitle">
									{{ item.title }}
								</div>
								<div class="listPanel__itemSubTitle">
									{{ item.subtitle }}
								</div>
							</div>
						</label>
					</div>
				</template>
			</list-panel>
		</fieldset> *}

	<pkp-form
		v-bind="components.{$smarty.const.FORM_SLIDER_SETTINGS}"
		@set="set"
	/>
</tab>