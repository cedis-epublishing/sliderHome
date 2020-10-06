{**
 * templates/frontend/pages/index.tpl
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @brief Display the front page of the site
 *
 * @uses $homepageImage array Details about the uploaded homepage image
 * @uses $spotlights array Selected spotlights to promote on the homepage
 * @uses $featuredMonographs array List of featured releases in this press
 * @uses $newReleases array List of new releases in this press
 * @uses $announcements array List of announcements
 * @uses $numAnnouncementsHomepage int Number of announcements to display on the
 *       homepage
 * @uses $additionalHomeContent string HTML blob of arbitrary content added by
 *  an editor/admin.
 *}
{include file="frontend/components/header.tpl"}


<link rel="stylesheet" href="http://ojs-test.cedis.fu-berlin.de/omp-cf-1/plugins/generic/sliderHome/css/sliderHome.css">
<link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.css">
<link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">

<script src="https://unpkg.com/swiper/swiper-bundle.js"></script>
<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>




<div class="page page_homepage">

{$sliderContent}
{**
  <div class="swiper-container" >
    <div class="swiper-wrapper">
      <div class="swiper-slide">
		<img src="http://ojs-test.cedis.fu-berlin.de/omp-cf-1/public/presses/1/landschaft.jpg">
		<div class="slider-text">
			<h3>Series all around the world</h3>
			<p>Language Science Press has 19 series with over 240 editorial board members from over 40 countries on all continents</p>               <a title="series" href="/series">Read more ...</a></p>
		</div>	  
	  </div>
      <div class="swiper-slide"><img src="http://ojs-test.cedis.fu-berlin.de/omp-cf-1/public/presses/1/bild1.png"></div>
      <div class="swiper-slide"><img src="http://ojs-test.cedis.fu-berlin.de/omp-cf-1/public/presses/1/bild2.png"></div>
      <div class="swiper-slide"><img src="http://ojs-test.cedis.fu-berlin.de/omp-cf-1/public/presses/1/bild3.png"></div>
      <div class="swiper-slide"><img src="http://ojs-test.cedis.fu-berlin.de/omp-cf-1/public/presses/1/bild4.png"></div>	  
    </div>
    <!-- Add Pagination -->
    <div class="swiper-pagination"></div>
  </div>
 **}
  
  {**				
<p><img src="/public/site/images/snordhoff/ebm2.png" alt=""></p>
<div>
<h3>Series all around the world</h3>
<p>Language Science Press has 19 series with over 240 editorial board members from over 40 countries on all contintents                 <a title="series" href="/series">Read more ...</a></p>
</div>				
  <div class="swiper-container" >
    <div class="swiper-wrapper">
      <div class="swiper-slide"><img src="http://ojs-test.cedis.fu-berlin.de/omp-cf-1/public/presses/1/bild1.png"></div>
      <div class="swiper-slide"><img src="http://ojs-test.cedis.fu-berlin.de/omp-cf-1/public/presses/1/bild2.png"></div>
      <div class="swiper-slide"><img src="http://ojs-test.cedis.fu-berlin.de/omp-cf-1/public/presses/1/bild3.png"></div>
      <div class="swiper-slide"><img src="http://ojs-test.cedis.fu-berlin.de/omp-cf-1/public/presses/1/bild4.png"></div>	  
    </div>
    <!-- Add Pagination -->
    <div class="swiper-pagination"></div>
  </div>
  **}
  
  

  <!-- Swiper JS -->
  <script src="../package/swiper-bundle.min.js"></script>

  <!-- Initialize Swiper -->
  <script>
    var swiper = new Swiper('.swiper-container', {
      pagination: {
        el: '.swiper-pagination',
        clickable: true,
        renderBullet: function (index, className) {
          return '<span class="' + className + '">' + '</span>';
        },
      },
	  speed: 2000,
	  autoplay: { delay: 200000,disableOnInteraction:true, stopOnLastSlide:true },
    });
  </script>






	{* Homepage Image *}
	{if !$activeTheme->getOption('useHomepageImageAsHeader') && $homepageImage}
		<img src="{$publicFilesDir}/{$homepageImage.uploadName|escape:"url"}" alt="{$homepageImageAltText|escape}">
	{/if}

	{* Spotlights *}
	{if !empty($spotlights)}
		<h2 id="homepageSpotlights" class="pkp_screen_reader">
			{translate key="spotlight.spotlights"}
		</h2>
		{include file="frontend/components/spotlights.tpl"}
	{/if}


	{* Featured *}
	{if !empty($featuredMonographs)}
		{include file="frontend/components/monographList.tpl" monographs=$featuredMonographs titleKey="catalog.featured"}
	{/if}

	{* New releases *}
	{if !empty($newReleases)}
		{include file="frontend/components/monographList.tpl" monographs=$newReleases titleKey="catalog.newReleases"}
	{/if}

	{* Announcements *}
	{if $numAnnouncementsHomepage && $announcements|@count}
		<div id="homepageAnnouncements" class="cmp_announcements highlight_first">
			<h2>
				{translate key="announcement.announcements"}
			</h2>
			{foreach name=announcements from=$announcements item=announcement}
				{if $smarty.foreach.announcements.iteration > $numAnnouncementsHomepage}
					{break}
				{/if}
				{if $smarty.foreach.announcements.iteration == 1}
					{include file="frontend/objects/announcement_summary.tpl" heading="h3"}
					<div class="more">
				{else}
					<article class="obj_announcement_summary">
						<h4>
							<a href="{url router=$smarty.const.ROUTE_PAGE page="announcement" op="view" path=$announcement->getId()}">
								{$announcement->getLocalizedTitle()|escape}
							</a>
						</h4>
						<div class="date">
							{$announcement->getDatePosted()}
						</div>
					</article>
				{/if}
			{/foreach}
			</div><!-- .more -->
		</div>
	{/if}

	{* Additional Homepage Content *}
	{if $additionalHomeContent}
		<div class="additional_content">
			{$additionalHomeContent}
		</div>
	{/if}

</div>



{include file="frontend/components/footer.tpl"}