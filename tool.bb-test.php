<?php
 /**
 * WhispyForum tool - bb code tester
 * 
 * Use this to try out BB code formatted text (for example, in the forum)
 *
 * Even though this file is marked as a TOOL, DO NOT DELETE IT!
 * 
 * WhispyForum
 */

include('includes/safeload.php'); // We load the engine, but not the framework

if ( isset($_POST['text']) )
{
	// Parse text
	echo bbDecode($Cmysql->EscapeString($_POST['text']));
}

$Ctemplate->useTemplate("bbcodes", array(
	'TEXTAREA'	=>	@$_POST['text'], // Returning the BB code formatted text to the template
	/* Filling template with runtime live formations (some of them localized) */
	'BOLD_TEXT'	=>	bbDecode("[b]" .$wf_lang['{LANG_BBCODES_BOLD}']. "[/b]"),
	'ITALIC_TEXT'	=>	bbDecode("[i]" .$wf_lang['{LANG_BBCODES_ITALIC}']. "[/i]"),
	'UNDERLINED_TEXT'	=>	bbDecode("[u]" .$wf_lang['{LANG_BBCODES_UNDERLINED}']. "[/u]"),
	'STROKED_TEXT'	=>	bbDecode("[s]" .$wf_lang['{LANG_BBCODES_STROKED}']. "[/s]"),
	'IMAGE'	=>	bbDecode("[img]/themes/winky/Nuvola_apps_package_settings.png[/img]"),
	'IMAGE_SIZED'	=>	bbDecode("[img=24x24]/themes/winky/Nuvola_apps_package_settings.png[/img]"),
	'CODE'	=>	bbDecode("[code]using namespace System;[/code]"),
	'URL'	=>	bbDecode("[url]http://google.com[/url]"),
	'URL_WITH_TEXT'	=>	bbDecode("[url=http://google.com]" .$wf_lang['{LANG_BBCODES_GOOGLE_HOMEPAGE}']. "[/url]"),
	'QUOTE'	=>	bbDecode("[quote]" .$wf_lang['{LANG_BBCODES_PICASSO_QUOTE}']. "[/quote]"),
	'QUOTE_CITATED'	=>	bbDecode('[quote="Pablo Picasso"]' .$wf_lang['{LANG_BBCODES_PICASSO_QUOTE}']. '[/quote]'),
), FALSE);

DoFooter();
?>