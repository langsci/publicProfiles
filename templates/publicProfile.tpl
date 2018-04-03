{**
 * plugins/generic/publicProfiles/templates/publicProfile.tpl
 *
 * Copyright (c) 2016 Language Science Press
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * main template for the public profiles plugin
 *
 *}

{include file="frontend/components/header.tpl" pageTitleTranslated="$title"}

<link rel="stylesheet" href="{$baseUrl}/plugins/generic/publicProfiles/css/publicProfiles.css" type="text/css" />

<div id="publicProfiles">

{if $showProfile}

	<img src="{$baseUrl}/{$publicSiteFilesPath}/{$profileImage.uploadName}?{""|uniqid}" alt="" />

	<h2>{translate key="plugins.generic.publicProfiles.header"} {$academic_title|strip_unsafe_html} {$first_name|strip_unsafe_html} {$last_name|strip_unsafe_html}</h2>

	{if !$affiliation==""}
		<h3>{translate key="plugins.generic.publicProfiles.affiliation"}</h3>
		</p>{$affiliation|strip_unsafe_html}</p>
	{/if}

	{if $showEmail}
		<h3>{translate key="plugins.generic.publicProfiles.email"}</h3>
		<p><a href="mailto:{$email}">{$email|strip_unsafe_html}</a></p>

	{/if}

	{if !$url==""}
		<h3>{translate key="plugins.generic.publicProfiles.website"}</h3>
		<p><a href="{$url}">{$url|strip_unsafe_html}</a></p>
	{/if}


	{if !$biostatement==""}
		<h3>{translate key="plugins.generic.publicProfiles.biostate"}</h3>
		{$biostatement|strip_unsafe_html}
	{/if}

	{$bookAchievements}

{else}
	<p>{translate key="plugins.generic.publicProfiles.noProfileAvailable"}</p>
{/if}

</div>

{include file="frontend/components/footer.tpl"}


