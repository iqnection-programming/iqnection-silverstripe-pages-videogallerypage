<?php

use SilverStripe\ORM;
use SilverStripe\Forms;

class VideoGallerySiteConfigExtension extends ORM\DataExtension
{
	private static $db = array(
		'VideoGallery_YouTubeApiKey' => 'Varchar(50)'
	);
	
	public function updateCMSFields(Forms\FieldList $fields)
	{
		$tab = $fields->findOrMakeTab('Root.Developer.VideoGallery');
		$tab->push( Forms\TextField::create('VideoGallery_YouTubeApiKey','YouTube API Key') );
		return $fields;
	}
}
