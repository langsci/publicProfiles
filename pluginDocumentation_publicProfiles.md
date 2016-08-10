Key data
============

- name of the plugin: Public Profiles Plugin
- author: Carola Fanselow
- current version: 1.0
- tested on OMP version: 1.2.0
- github link: https://github.com/langsci/publicProfiles.git
- community plugin: yes
- date: 2016/05/25

Description
============

This plugin creates a public profile for each registered user. The plugin displays
- salutation, first name, last name
- affiliation
- email
- bio statement
- public profile image
- achievements (work on books in different roles)

In the settings of this plugin you can specify:
- the path of the public profile pages
- what roles to include in the public profile
- style of the references displayed
- whether to use all or only published books
 
Implementation
================

Hooks
-----
- used hooks: 1

		LoadHandler

New pages
------
- new pages: 1

		path can be specified in the plugin settings

Templates
---------
- templates that replace other templates: 0
- templates that are modified with template hooks: 0
- new/additional templates: 2

		publicProfile.tpl
		settings.tpl

Database access, server access
-----------------------------
- reading access to OMP tables: 5

		plugin_settings
		user_settings
		user
		user_group_settings
		stage_assignments

- writing access to OMP tables: 1

		plugin_settings

- new tables: 0
- nonrecurring server access: no
- recurring server access: no
 
Classes, plugins, external software
-----------------------
- OMP classes used (php): 13
	
		GenericPlugin
		Handler
		DAO
		AjaxModal
		Form
		LinkAction
		JSONMessage
		TemplateManager
		FormValidatorPost
		MonographDAO
		PublishedMonographDAO
		SeriesDAO
		PublicFileManager

- OMP classes used (js, jqeury, ajax): 1

		AjaxFormHandler

- necessary plugins: 0
- optional plugins: 2

		User Website Settings (users can block their profile, users can specify whether to display their email address)

- use of external software: no
- file upload: no
 
Metrics
--------
- number of files: 13
- number of lines: 1185

Settings
--------
- settings: 4

		path to the public profile pages
		what user groups to user for the achievements display
		whether to use all or only published monographs (checkbox)
		style of the references (checkbox)

Plugin category
----------
- plugin category: generic

Other
=============
- does using the plugin require special (background)-knowledge?: yes, the relationship between this plugin and the User Website Settings Plugin and the Hall of Fame Plugin
- access restrictions: no
- adds css: yes




