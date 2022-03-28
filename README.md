- Home Plugin
- Version: 2.3.0.0
- Date: 8.12.2021
- Author: Carola Fanselow, Ronald Steffen

About
-----
This plugin creates a swiper slider on the OJS/OMP homepage. Images and image caputes can be added in the OJS backend (website settings). Data is stored in table "slider". 

License
-------
Copyright (c) 2021 Universitätsbiblithek Freie Universität Berlin

This plugin is licensed under the GNU General Public License v3. 

System Requirements
-------------------
This plugin is compatible with...
 - OJS/OMP 3.3

Installation
------------
To install the plugin:
 - Upload the tar.gz file (Management > Website Settings > Plugins > Generic Plugins)

Usage
------------

To insert a picture with copyright information into the slider use e.g.:

        <div>
            <figure>
                <img src="sample_picture.jpg" alt="" width="1200" height="1600" />
                <small style="display: block; text-align: right;">Copyright Information</small>
            </figure>
        </div>

If you want to add an overlay text/html use e.g.:

        <div>
            <figure>
                <img src="sample_picture.jpg" width="1200" height="1600"/>
                <small style="display: block; text-align: right;">Copyright Information</small>
                <div class="slider-text">
                    <h3>Title</h3>
                    <p>Text <a href="#">Read more ...</a></p>
                </div>
            </figure>
        </div>

Support
---------------
Additional information on this plugin can be found in docs/pluginDocumentation_home.md



