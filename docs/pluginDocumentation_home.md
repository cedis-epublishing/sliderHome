Key data
============

- name of the plugin: Slider Home Plugin
- author: Carola Fanselow, Ronald Steffen
- current version: 2.2.0.0
- tested on OJS/OMP 3.2
- community plugin: yes
- date: 2021/07/07

Description
============

This plugin creates a swiper slider on the OJS/OMP homepage. Images and image caputes can be added in the OJS backend (website settings). Data is stored in table "slider" and in the plugin settings.
 
Implementation
================

Hooks
-----
TemplateManager::display => to enable slider display in OMP frontend
Template::Settings::website::appearance => to enable display of plugin settings tab
LoadComponentHandler => to load (old style) grid handler for image uploadd form
Templates::Index::journal => to enable slider display in OJS frontend
APIHandler::endpoints => to setup endpoint for ComponentForm submission via REST API

New pages
------

Templates
---------
appearanceTab.tpl => to enable display of plugin settings tab
homeOMP.tpl => to enable slider display in OMP frontend
sliderContentForm.tpl => (old style) form for image upload

Database access, server access
-----------------------------
- table "slider"
- table "plugin_settings"
 
Classes, plugins, external software
-----------------------
- necessary plugins: 0
- optional plugins: 0 
- use of external software: yes

		https://swiperjs.com/

- file upload: yes (TinyMCE)
 
Metrics
--------
- number of files: 
- number of lines: 

Settings
--------
- settings: yes
- tab in website settings to enter slider data and settings

Plugin category
----------
- plugin category: generic

Other
=============
- does using the plugin require special (background)-knowledge?: no
- access restrictions: no
- adds css: yes