{* {capture assign=sliderHomeGridUrl}{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.sliderHome.controllers.grid.SliderHomeGridHandler" op="fetchGrid" escape=false}{/capture}
{load_url_in_div id="sliderHomeGridContainer" url=$sliderHomeGridUrl} *}

{* {capture assign="sliderHomeSettingsGridUrl"}{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.sliderHome.controllers.grid.SliderHomeGridHandler" op="showSettingsForm" escape=false}{/capture}
{load_url_in_div id="sliderHomeSettingsGridContainer" url=$sliderHomeSettingsGridUrl} *} *}
