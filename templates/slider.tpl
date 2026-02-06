{* Smarty template for slider markup. Variables assigned by PHP: *}
{* sliderItems, baseUrl, publicFilesDir, contextPath, contextId, maxHeight, speed, delay, stopOnLastSlide, slideEffect *}
{if $sliderItems|@count > 0}
    <div class="slider-home-mount">
        <div class="swiper slider-home-swiper" data-effect="{$slideEffect|escape:'html'}" data-speed="{$speed|default:0}"
            data-delay="{$delay|default:0}" data-stop-on-last="{$stopOnLastSlide|default:false}"
            data-prev-slide-message="{translate key='plugins.generic.sliderHome.prevSlide'}"
            data-next-slide-message="{translate key='plugins.generic.sliderHome.nextSlide'}">
            <div class="swiper-wrapper" style="max-height:{$maxHeight}vh;">
                {foreach from=$sliderItems item=item}
                    <div class="swiper-slide" style="max-height:{$maxHeight}vh; height: {$maxHeight}vh;">
                        <figure class="slider-figure" style="max-height:{$maxHeight}vh;{if $item.sliderImage} width: auto;{else} width: 100%;{/if}">
                            {if $item.sliderImage}
                                {assign var="imgSrc" value="`$baseUrl`/`$publicFilesDir``$contextPath``$contextId`/`$item.sliderImage`"}
                                {if $item.sliderImageLink}
                                    <a class="slider-link" href="{$item.sliderImageLink|escape}" style="max-height:{$maxHeight}vh;">
                                        <img style="max-height:{$maxHeight}vh;" src="{$imgSrc|escape}"
                                            alt="{$item.sliderImageAltText|escape}">
                                    </a>
                                {else}
                                    <img style="max-height:{$maxHeight}vh;" src="{$imgSrc|escape}"
                                        alt="{$item.sliderImageAltText|escape}">
                                {/if}

                                {if $item.content}
                                    <div id="slider-overlay" class="slider-text {if $item.noclick}noclick{/if}">
                                        {$item.content nofilter}
                                    </div>
                                {/if}
                            {else}
                                <div id="slide-no-image-container" class="slide-no-image-container" style="max-height:{$maxHeight}vh; height: {$maxHeight}vh;">
                                    {if $item.content}
                                        <div id="slider-overlay" class="slider-text-no-image slider-text">
                                            {if $item.sliderImageLink}
                                                <a class="slider-link" href="{$item.sliderImageLink|escape}">
                                                    {$item.content nofilter}
                                                </a>
                                            {else}
                                                {$item.content nofilter}
                                            {/if}
                                        </div>
                                    {else}
                                        {if $item.sliderImageLink}
                                            <a class="slider-link" href="{$item.sliderImageLink|escape}">
                                                <h2 class="slider-text-no-image slider-text">{$item.name|escape}</h2>
                                            </a>
                                        {else}
                                            <h2 class="slider-text-no-image slider-text">{$item.name|escape}</h2>
                                        {/if}
                                    {/if}
                                </div>
                            {/if}

                            {if $item.copyright}
                                <small class="slider-copyright">{$item.copyright|escape}</small>
                            {/if}

                        </figure>
                    </div>
                {/foreach}
            </div>

            <div class="swiper-pagination"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>
    </div>
{/if}