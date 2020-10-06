Key data
============

- name of the plugin: Home Plugin
- author: Carola Fanselow
- current version: 1.0
- tested on OMP version: 1.2.0
- github link: https://github.com/langsci/home.git
- community plugin: yes
- date: 2016/10/06

Description
============

This plugin replaces the template for the home page in OMP (frontend/pages/index.tpl). In index.tpl, it adds a slider that is populated with data from table 'langsci_slider_content'. The table can be created and managed with the Plugin "Slider Content Plugin".
 
Implementation
================

Hooks
-----
- used hooks: 1

		TemplateManager::display

New pages
------
- new pages: 0


Templates
---------
- templates that replace other templates: 1

		home.tpl replaces frontend/pages/index.tpl

- templates that are modified with template hooks: 0
- new/additional templates: 0

Database access, server access
-----------------------------
- reading access to OMP tables: 0
- writing access to OMP tables: 0
- new tables: 0
- nonrecurring server access: no
- recurring server access: no
 
Classes, plugins, external software
-----------------------
- OMP classes used (php): 2

		GenericPlugin
		TemplateManager

- OMP classes used (js, jqeury, ajax): 0
- necessary plugins: 0
- optional plugins: 1
 
		Slider Content Plugin (to enter slider content)

- use of external software: yes

		https://github.com/OwlFonk/OwlCarousel

- file upload: no
 
Metrics
--------
- number of files: 10 (without external software)
- number of lines: 428 (without external software)

Settings
--------
- settings: no

Plugin category
----------
- plugin category: generic

Other
=============
- does using the plugin require special (background)-knowledge?: yes, template for the home page has to be adapted
- access restrictions: no
- adds css: yes


