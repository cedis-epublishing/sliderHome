<tab id="slider" label="{translate key="plugins.generic.sliderHome.tabname"}">
	{capture assign=sliderHomeGridUrl}{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.sliderHome.controllers.grid.SliderHomeGridHandler" op="fetchGrid" escape=false}{/capture}
	{load_url_in_div id="sliderHomeGridContainer" url=$sliderHomeGridUrl}							
</tab>