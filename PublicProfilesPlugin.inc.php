<?php

/**
 * @file plugins/generic/publicProfiles/PublicProfilesPlugin.inc.php
 *
 * Copyright (c) 2016 Language Science Press
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicProfilesPlugin
 * Public Profiles plugin main class
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class PublicProfilesPlugin extends GenericPlugin {

	/**
	 * Get the plugin's display (human-readable) name.
	 * @return string
	 */
	function getDisplayName() {
		return __('plugins.generic.publicProfiles.displayName');
	}

	/**
	 * Get the plugin's display (human-readable) description.
	 * @return string
	 */
	function getDescription() {
		return __('plugins.generic.publicProfiles.description');
	}

	/**
	 * Register the plugin, attaching to hooks as necessary.
	 * @param $category string
	 * @param $path string
	 * @return boolean
	 */
	function register($category, $path) {

		if (parent::register($category, $path)) {
			if ($this->getEnabled()) {

				HookRegistry::register('LoadHandler', array($this, 'callbackHandleContent'));
			}
			return true;
		}
		return false;
	}

	/**
	 * Declare the handler function to process the actual page PATH
	 * @param $hookName string The name of the invoked hook
	 * @param $args array Hook parameters
	 * @return boolean Hook handling status
	 */
	function callbackHandleContent($hookName, $args) {

		$request = $this->getRequest();
		$press   = $request->getPress();		

		// get url path components to overwrite them 
		$pageUrl =& $args[0];
		$opUrl =& $args[1];

		$goToPublicProfiles = $this->checkUrl($pageUrl,$opUrl);

		if ($goToPublicProfiles) {

			$pageUrl = '';
			$opUrl = 'viewPublicProfile';

			define('HANDLER_CLASS', 'PublicProfilesHandler');
			define('PUBLICPROFILES_PLUGIN_NAME', $this->getName());

			$this->import('PublicProfilesHandler');

			return true;

		}
		return false;
	}

	/**
	 * @see Plugin::getActions()
	 */
	function getActions($request, $verb) {
		$router = $request->getRouter();
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		return array_merge(
			$this->getEnabled()?array(
				new LinkAction(
					'settings',
					new AjaxModal(
						$router->url($request, null, null, 'manage', null, array('verb' => 'settings', 'plugin' => $this->getName(), 'category' => 'generic')),
						$this->getDisplayName()
					),
					__('manager.plugins.settings'),
					null
				),
			):array(),
			parent::getActions($request, $verb)
		);
	}

 	/**
	 * @see Plugin::manage()
	 */
	function manage($args, $request) {
		switch ($request->getUserVar('verb')) {
			case 'settings':
				$context = $request->getContext();
				$this->import('PublicProfilesSettingsForm');
				$form = new PublicProfilesSettingsForm($this, $context->getId());
				if ($request->getUserVar('save')) {
					$form->readInputData();
					if ($form->validate()) {
						$form->execute();
						return new JSONMessage(true);
					}
				} else {
					$form->initData();
				}
				return new JSONMessage(true, $form->fetch($request));
		}
		return parent::manage($args, $request);
	}

	private function checkUrl($pageUrl,$opUrl) {

		$request = $this->getRequest();
		$context = $request->getContext();
	
		// get path components
		$urlArray = array();
		$urlArray[] = $pageUrl;
		$urlArray[] = $opUrl;
		$urlArray = array_merge($urlArray,$request->getRequestedArgs());
		$urlArrayLength = sizeof($urlArray);

		if ($urlArrayLength<2) {
			return false;
		}
		$urlUserId = $urlArray[$urlArrayLength-1];

		if (!ctype_digit($urlUserId)) {
			return false;
		}
 		unset($urlArray[$urlArrayLength-1]);

		// get path components specified in the plugin settings
		$settingPath = $this->getSetting($context->getId(),'langsci_publicProfiles_path');

		if (!ctype_alpha(substr($settingPath,0,1))&&!ctype_digit(substr($settingPath,0,1))) {
			return false;
		}
		$settingPathArray = explode("/",$settingPath);
		$settingPathArrayLength = sizeof($settingPathArray);

		// compare path and path settings
		$goToPublicProfiles = false;
		if ($settingPathArray==$urlArray){
			$goToPublicProfiles = true;
		}
		return $goToPublicProfiles;
	}


	/**
	 * @copydoc PKPPlugin::getTemplatePath
	 */
	function getTemplatePath() {
		return parent::getTemplatePath() . 'templates/';
	}


}

?>
