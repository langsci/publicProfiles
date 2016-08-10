<?php

/**
 * @file plugins/generic/publicProfiles/PublicProfilesSettingsForm.inc.php
 *
 * Copyright (c) 2016 Language Science Press
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicProfilesSettingsForm
 *
 */

import('lib.pkp.classes.form.Form');

class PublicProfilesSettingsForm extends Form {

	/** @var AddThisBlockPlugin The plugin being edited */
	var $_plugin;

	/** @var int Associated context ID */
	private $_contextId;

	/**
	 * Constructor.
	 * @param $plugin Plugin
	 * @param $press Press
	 */
	function PublicProfilesSettingsForm($plugin, $contextId) {

		$this->_contextId = $contextId;
		$this->_plugin = $plugin;

		parent::Form($plugin->getTemplatePath() . 'settings.tpl');
		$this->addCheck(new FormValidatorPost($this));
	}


	//
	// Overridden template methods
	//
	/**
	 * Initialize form data from the plugin.
	 */
	function initData() {

		$contextId = $this->_contextId;
		$plugin = $this->_plugin;

		$this->setData('langsci_publicProfiles_path', $plugin->getSetting($contextId, 'langsci_publicProfiles_path'));
		$this->setData('langsci_publicProfiles_userGroups', $plugin->getSetting($contextId, 'langsci_publicProfiles_userGroups'));
		$this->setData('langsci_publicProfiles_unifiedStyleSheetForLinguistics', $plugin->getSetting($contextId, 'langsci_publicProfiles_unifiedStyleSheetForLinguistics'));
		$this->setData('langsci_publicProfiles_onlyPublishedMonographs', $plugin->getSetting($contextId, 'langsci_publicProfiles_onlyPublishedMonographs'));
		
	}

	/**
	 * Fetch the form.
	 * @see Form::fetch()
	 * @param $request PKPRequest
	 */
	function fetch($request) {

		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('pluginName', $this->_plugin->getName());
		$templateMgr->assign('pluginBaseUrl', $request->getBaseUrl() . '/' . $this->_plugin->getPluginPath());

		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {

		$this->readUserVars(array(
			'langsci_publicProfiles_path',
			'langsci_publicProfiles_userGroups',
			'langsci_publicProfiles_onlyPublishedMonographs',
			'langsci_publicProfiles_unifiedStyleSheetForLinguistics'
		));
	}

	/**
	 * Save the plugin's data.
	 * @see Form::execute()
	 */
	function execute() {

		$plugin = $this->_plugin;
		$contextId = $this->_contextId;

		$plugin->updateSetting($contextId, 'langsci_publicProfiles_path', trim($this->getData('langsci_publicProfiles_path')));
		$plugin->updateSetting($contextId, 'langsci_publicProfiles_userGroups', trim($this->getData('langsci_publicProfiles_userGroups')));
		$plugin->updateSetting($contextId, 'langsci_publicProfiles_onlyPublishedMonographs', trim($this->getData('langsci_publicProfiles_onlyPublishedMonographs')));
		$plugin->updateSetting($contextId, 'langsci_publicProfiles_unifiedStyleSheetForLinguistics', trim($this->getData('langsci_publicProfiles_unifiedStyleSheetForLinguistics')));

	}
}
?>
