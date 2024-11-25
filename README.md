- Slider Home Plugin
- Version: 2.5.0.0
- Date: 25.11.2024
- Author: Carola Fanselow, Ronald Steffen

About
-----
This plugin creates a swiper slider on the OJS/OMP homepage. Images and image caputes can be added in the OJS backend (website settings). Data is stored in table "slider". 

This plugin is based on [Swiper](https://swiperjs.com) version 6.3.2 by Vladimir Kharlampidi (License MIT)

License
-------
Copyright (c) 2021 Universitätsbiblithek Freie Universität Berlin

This plugin is licensed under the GNU General Public License v3. 

System Requirements
-------------------
This plugin is compatible with...
 - OJS/OMP 3.4

Installation
------------
To install a release package of the plugin:
 - Upload the tar.gz file (Management > Website Settings > Plugins > Generic Plugins)

To install from source git clone or download the source to `plugins/generic/sliderHome`, go to your ojs root folder and execute:

```bash
php lib/pkp/tools/intsallPluginVersion.php plugins/generic/sliderHome/version.xml
cd plugins/generic/sliderHome
npm install
npm run build
```

Usage
------------

Go to "Website -> Appearance -> Slider Plugin" and create new slider entries. You can add a slider image and HTML content to overlay onto the slider image separately.

Support
---------------
Additional information on this plugin can be found in docs/pluginDocumentation_home.md



