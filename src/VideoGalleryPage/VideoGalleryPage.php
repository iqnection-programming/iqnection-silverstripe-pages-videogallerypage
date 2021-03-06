<?php

namespace IQnection\VideoGallery;

use SilverStripe\Forms;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;

class VideoGalleryPage extends \Page
{
	private static $table_name = 'VideoGalleryPage';
	
	private static $icon = "iqnection-pages/videogallerypage:images/icon-videogallerypage-file.gif";
	
	private static $has_many = [
		"Videos" => Model\Video::class
	];
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->addFieldToTab('Root.Videos', Forms\GridField\GridField::create(
			'Videos',
			'Videos',
			$this->Videos(),
			Forms\GridField\GridFieldConfig_RecordEditor::create()->addComponent(
				new GridFieldSortableRows('SortOrder')
			)
		));
		$this->extend('updateCMSFields',$fields);
		return $fields;
	}	
}



