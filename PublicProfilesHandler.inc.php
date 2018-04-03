<?php

/**
 * @file plugins/generic/publicProfiles/PublicProfilesHandler.inc.php
 *
 * Copyright (c) 2016 Language Science Press
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicProfilesHandler
 *
 * Find the content and display the appropriate page
 *
 */

import('classes.handler.Handler');
import('plugins.generic.publicProfiles.PublicProfilesDAO');
import('classes.monograph.MonographDAO');
import('classes.monograph.PublishedMonographDAO');
import('classes.press.SeriesDAO');
import('classes.file.PublicFileManager');

class PublicProfilesHandler extends Handler {

	function viewPublicProfile($args, $request) {

		$templateMgr = $this->getTemplateManager($request);
		$press = $request->getPress();
		$plugin = PluginRegistry::getPlugin('generic', PUBLICPROFILES_PLUGIN_NAME);
		$context = $request->getContext();
		$contextId = $context->getId();
		$locale = AppLocale::getLocale();

		// get profile user id from url (last string in the url)
		$requestedUrl = $request->getCompleteUrl();
		if (substr($requestedUrl, -1)=="/") {
			$requestedUrl = substr($requestedUrl,0,strlen($requestedUrl)-1);
		}
		$userId = substr($requestedUrl,-strpos(strrev($requestedUrl),"/"));

		if (!ctype_digit ($userId)) {
			$request->redirect($press->getPath());			
		} 

		// get setting variables
		$press = $request -> getPress();

		$publicProfilesDAO = new PublicProfilesDAO;
		$existsLangsciWebsiteSettings = $publicProfilesDAO->existsTable('langsci_website_settings');

		$onlyPublishedSubmissions = $plugin->getSetting($contextId,'langsci_publicProfiles_onlyPublishedMonographs');
		$unifiedStyleSheetForLinguistics = $plugin->getSetting($contextId,'langsci_publicProfiles_unifiedStyleSheetForLinguistics');
		$userGroupsString = $plugin->getSetting($contextId,'langsci_publicProfiles_userGroups');

		$completePressPath = $this->getPressPath($request);

		if (!$existsLangsciWebsiteSettings) {
			$showProfile = true;
			$showEmail = false;
		} else {
			$showProfile = $publicProfilesDAO->getUserSetting($userId,'PublicProfile')=='true';
			$showEmail = $publicProfilesDAO->getUserSetting($userId,'Email')=='true';
		}

		// print books the user worked on
		$bookAchievements = "";

		$userGroupsArray = explode(",",$userGroupsString); 
		if ($userGroupsString=="") {
			$userGroupsArray = array();
		}

		for ($i=0; $i<sizeof($userGroupsArray); $i++) {

			$userGroupName = trim($userGroupsArray[$i]);

			$userGroupId = $publicProfilesDAO->getUserGroupIdByName($userGroupName,$contextId);

			if ($userGroupId) {
				$submissions = $publicProfilesDAO->getSubmissionsFromStageAssignments($userId, $userGroupId,
														$onlyPublishedSubmissions);
			} else {
				$submissions = array();
			}

			if ($locale=="de_DE") {
				$intro = "Arbeitete an den folgenden Büchern als ";
			} else {
				$intro = "Worked on the following books as ";
			}

			if (sizeof($submissions)>0) {
				$bookAchievements .= "<div><p><span class='header'>". $intro . $userGroupName . ":</span></p><ul>";
			}

			for ($ii=0; $ii<sizeof($submissions); $ii++) {
				$submissionString="";
							if ($unifiedStyleSheetForLinguistics) {
								$submissionString =
									$this->biblioHtmlListElement(		
											$this->getBiblioLinguistStyle($submissions[$ii],$completePressPath),
											$submissions[$ii],
											$completePressPath,
											true);
							} else {
								$submissionString = 
									$this->biblioHtmlListElement(
											$this->getNameOfSubmission($submissions[$ii],$completePressPath),
											$submissions[$ii],
											$completePressPath,
											true);								
							}
				$bookAchievements .= $submissionString;
			}

			if (sizeof($submissions)>0) {
				$bookAchievements .= "</ul></div>";
			}
			
		}
		
		// get profile image that was uploaded in the OMP public profile
		
		$userSetting = $publicProfilesDAO->getSettingsByAssoc($userId,ASSOC_TYPE_PRESS);	
		//$templateMgr->assign('profileImage',$userSetting['profileImage']);
			
		$publicFileManager = new PublicFileManager();
		$templateMgr->assign('publicSiteFilesPath',$publicFileManager->getSiteFilesPath());

		$templateMgr->assign('pageTitle', 'plugins.generic.publicProfiles.title');
		$templateMgr->assign('showProfile', $showProfile);

		if ($showProfile) {

			$userData =  $publicProfilesDAO->getUserData($userId,$locale);
			$templateMgr->assign('username',       $userData[0]);
			$templateMgr->assign('first_name',     $userData[1]);
			$templateMgr->assign('last_name',      $userData[2]);
			$templateMgr->assign('url',            $userData[3]);
			$templateMgr->assign('email',          $userData[4]);
			$templateMgr->assign('academic_title', $userData[5]);
			$templateMgr->assign('affiliation',    $userData[6]);
			//$templateMgr->assign('biostatement',   $userData[7]);
			$templateMgr->assign('showEmail',      $showEmail);
			$templateMgr->assign('bookAchievements', $bookAchievements);
		}

		$publicProfilesPlugin = PluginRegistry::getPlugin('generic', PUBLICPROFILES_PLUGIN_NAME);
		$templateMgr->display($publicProfilesPlugin->getTemplatePath().'publicProfile.tpl');
	}

	function getTemplateManager($request)	{
		$this->validate();
		$press = $request->getPress();
		$this->setupTemplate($request, $press);
		$templateMgr = TemplateManager::getManager($request);	
		return $templateMgr;
	}

	function getPressPath(&$request) {
		$press = $request -> getPress();
		$pressPath = $press -> getPath();
 		$completeUrl = $request->getCompleteUrl();
		return substr($completeUrl,0,strpos($completeUrl,$pressPath)) . $pressPath ;
	}

	function getNameOfSubmission($submission_id, $completePressPath) {

		$monographDAO = new MonographDAO;
		$monograph = $monographDAO -> getById($submission_id);

		if (!$monograph) {
			return "Invalid monograph id " . $submission_id;
		}

		$authors = $monograph -> getAuthorString();
			
		if ($authors=="") {
			$authors = "N.N. ";
		}
		$title   = $monograph -> getLocalizedFullTitle(); 
		$bookPath = $completePressPath . "/catalog/book/" . $submission_id;

		return $authors . ": " . $title;
	}

	function getBiblioLinguistStyle($submissionId) {

		$publicProfilesDAO = new PublicProfilesDAO;
		$contextId = $publicProfilesDAO->getContextBySubmissionId($submissionId);

		// get monograph and authors object
		$publishedMonographDAO = new PublishedMonographDAO;
 		$publishedMonograph = $publishedMonographDAO->getById($submissionId);
		$monographObject = $publishedMonograph;
		if (!$publishedMonograph) {
			$monographDAO = new MonographDAO;
 			$monograph = $monographDAO -> getById($submissionId);
			if (!$monograph) {
				return "Invalid  monograph id: " . $submissionId;
			}
			$monographObject = $monograph;
		}
		$authors = $monographObject->getAuthors();

		// get series information				
		$seriesId = $monographObject->getSeriesId();
		$seriesDAO = new SeriesDAO;
		$series = $seriesDAO->getById($seriesId,$contextId); 
		if (!$series) {
			$seriesTitle = "Series unknown";
			$seriesPosition="tba";
		} else {
			$seriesTitle = $series->getLocalizedFullTitle();
			$seriesPosition = $monographObject ->getSeriesPosition();
			if (empty($seriesPosition)) {
				$seriesPosition="tba";
			}
		}

		// is edited volume (if there is at least one volume editor)
		$editedVolume = false;
		for ($i=0; $i<sizeof($authors); $i++) {
			if ($authors[$i]->getUserGroupId()==$publicProfilesDAO->getUserGroupIdByName("Volume Editor",$contextId)) {
				$editedVolume=true;
			}
		}

		// get authors to be printed (all volume editors for edited volumes, all authors else)
		$numberOfAuthors = 0;
		$authorsInBiblio = array();
		for ($i=0; $i<sizeof($authors); $i++) {
			$userGroupId = $authors[$i]->getUserGroupId();
			if ($editedVolume && $userGroupId==$publicProfilesDAO->getUserGroupIdByName("Volume Editor",$contextId)) {
				$numberOfAuthors = $numberOfAuthors + 1;
				$authorsInBiblio[] = $authors[$i];
			} else if (!$editedVolume && $userGroupId==$publicProfilesDAO->getUserGroupIdByName("Author",$contextId))  {
				$numberOfAuthors = $numberOfAuthors + 1;
				$authorsInBiblio[] = $authors[$i];
			}
		}

		// get author string
		$authorString=""; 
		for ($i=0; $i<sizeof($authorsInBiblio); $i++) {

			// format for first author: last_name, first_name, for all others: first_name last_name
			if ($i==0) {
				$authorString = $authorString .
					$authorsInBiblio[$i]->getLastName() . ", " .  $authorsInBiblio[$i]->getFirstName();
			} else {	
				// separator between authors
				if ($i==$numberOfAuthors-1) {
					$authorString = $authorString . " & ";
				} else {
					$authorString = $authorString . ", ";												
				}
				$authorString = $authorString .
					$authorsInBiblio[$i]->getFirstName() . " " . $authorsInBiblio[$i]->getLastName();
			}
		}

		// get author string: for edited volumes: add (ed.)/(eds.)	
		if ($editedVolume && $numberOfAuthors==1) {
			$authorString = $authorString . " (ed.)";
		} else if ($editedVolume && $numberOfAuthors>1) {
			$authorString = $authorString . " (eds.)";
		}
		$authorString = $authorString . ". ";

		// get author string: if there are no authors: add N.N.		
		if ($authorString==". ") {
			$authorString = "N.N. ";
		}
	
		// get year of publication, only for published mongraphs
		$publicationDateString = $this->getPublicationDate($submissionId);
		if (!$publicationDateString) {
			$publicationDateString = "????";
		} else {
			$publicationDateString = substr($publicationDateString,0,4); 
		}

		// get title
		$title = $monographObject->getLocalizedFullTitle($submissionId);
		if (!$title) {
			$title = "Title unknown";
		}				

		// compose biblio string
		$biblioLinguisticStyle = $authorString . $publicationDateString .
				".<i> " . $title . "</i> (".$seriesTitle  . " " . $seriesPosition ."). Berlin: Language Science Press.";
		
		return $biblioLinguisticStyle;
	}

	function biblioHtmlListElement($biblio,$submission_id,$completePressPath,$addLink) {
		
		$isPublished = false;
		$monographDAO = new PublishedMonographDAO;
 		$monograph = $monographDAO -> getById($submission_id);
		if ($monograph) {
			$isPublished = true;
		}

		if ($addLink && $isPublished) {
			$bookPath = $completePressPath . "/catalog/book/".$submission_id;
			$returner = "<li>".$biblio." <a href='".$bookPath."'>&rarr;</a></li>";
		} else {
			$returner = "<li>".$biblio."</li>";
		}
		return $returner;
	}	

	// get 'Publication date' for publication format 'PDF'
	function getPublicationDate($submissionId) {
		$publishedMonographDAO = new PublishedMonographDAO;
 		$publishedMonograph = $publishedMonographDAO->getById($submissionId);
		if ($publishedMonograph) {
			$pubformats = $publishedMonograph->getPublicationFormats();
			for ($i=0; $i<sizeof($pubformats); $i++) {
				$formatName = $pubformats[$i]->getName(AppLocale::getLocale());
				if ($formatName=="PDF") {
					$pubdates = $pubformats[$i]->getPublicationDates();
					$pubdatesArray = $pubdates->toArray();
					for ($ii=0;$ii<sizeof($pubdatesArray);$ii++) {
						// role id "01": publication date
						if ($pubdatesArray[$ii]->getRole()=="01") {
							return $pubdatesArray[$ii]->getDate();
						}
					}
				}
			}
		}
		return null;
	}

}

?>
