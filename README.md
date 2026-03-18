# Slider Home Plugin
- Version: 2.5.0.0
- Date: 18.03.2026
- Author: Carola Fanselow, Ronald Steffen

## About

This plugin creates a swiper slider on the OJS/OMP homepage. Slides may contain linked images with HTML overlay or HTML only content. Additional supported features:

- Copyright notice below the slider image
- Multilingual slides and texts
- Configurable slider transitions and delays

This plugin is based on [Swiper](https://swiperjs.com) version 12.0.3 by Vladimir Kharlampidi (License MIT)

## License

Copyright (c) 2021 Universitätsbiblithek Freie Universität Berlin

This plugin is licensed under the GNU General Public License v3. 

## System Requirements

This plugin is compatible with...
 - OJS/OMP 3.5

## Installation

To install a release package of the plugin:
 - Upload the tar.gz file (Management > Website Settings > Plugins > Generic Plugins)

To install from source git clone or download the source to `plugins/generic/sliderHome`, go to your ojs root folder and execute:

```bash
php lib/pkp/tools/intsallPluginVersion.php plugins/generic/sliderHome/version.xml
cd plugins/generic/sliderHome
npm install
npm run build
```

## Usage

Go to "Website -> Appearance -> Slider Plugin" and create new slider entries. You can add a slider image and HTML content to overlay onto the slider image separately.

A config.inc.php setting is supported to enable TinyMCE plugins and add functions to the toolbars. E.g. to enable HTML source code editing add the following to config.inc.php. Only plugins that are enabled by OJS are available.

```php
[sliderHome]
tinymceplugins=',code'
tinymcetoolbar=' | code'
```

## Development

- Install `nvm` via `curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.3/install.sh | bash`
- Close and reopen terminal
- If the required node version is not yet installed run `nvm install`
- Run nvm use before each development session, this will select the correct node version as stated in `.nvmrc`
- Run `npm install`
- Use `npm run dev`


