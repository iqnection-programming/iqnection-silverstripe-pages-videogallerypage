<?php

namespace IQnection\VideoGallery;

use SilverStripe\ORM;
use SilverStripe\Forms;
use SilverStripe\View;
use SilverStripe\SiteConfig\SiteConfig;

class Video extends ORM\DataObject
{
	private static $youtube_api_collection = [
		'snippet',
		'player'
	];
	
	private static $db = [
		"SortOrder" => "Int",
		"Title" => "Varchar(255)",
		'YouTubeLink' => 'Varchar(255)',
		"EmbedCode" => "Text",
		'Description' => 'HTMLText',
		'YouTubeData_JSON' => 'Text',
		'ImportFromYouTube' => 'Boolean'
	];
	
	private static $default_sort = "SortOrder";
	
	private static $summary_fields = [
		"Title" => "Title",
		'CMSThumbnail' => 'Thumbnail'
	];
	
	private static $has_one = [
		"Page" => \Page::class
	];
	
	private static $better_buttons_actions = [
		'importFromYouTube'
	];

	public function getCMSFields()
	{	
		$fields = parent::getCMSFields();
		$fields->push( Forms\HiddenField::create('SortOrder',null,$fields->dataFieldByName('SortOrder')->Value()) );
		$fields->dataFieldByName('Title')->setTitle('Video Title');
		$fields->insertBefore('Title', $fields->dataFieldByName('YouTubeLink')
			->setTitle('YouTube Share Link')
			->setDescription('YouTube ID: '.$this->YouTubeVideoID()) );
		$fields->insertBefore('YouTubeLink', Forms\LiteralField::create('yt-note','<p>Enter the share link from YouTube, or video embed code. 
			Imported YouTube data will overwrite existing values.</p>') );
//		if ( ( ($this->ID) && (!$this->isYouTube()) ) || (class_exists('UncleCheese\BetterButtons\Actions\BetterButtonCustomAction')) )
//		{
//			$fields->removeByName('ImportFromYouTube');
//		}
//		else
		{
			$fields->insertBefore('Title', $fields->dataFieldByName('ImportFromYouTube') );
		}
		if (class_exists('NathanCox\CodeEditorField\CodeEditorField'))
		{
			$fields->replaceField('EmbedCode', \NathanCox\CodeEditorField\CodeEditorField::create('EmbedCode','EmbedCode') );
		}
		$fields->dataFieldByName('EmbedCode')
			->setTitle('Video Embed Code')
			->addExtraClass('monotype');
		$fields->addFieldToTab('Root.Main', Forms\HTMLEditor\HTMLEditorField::create('Description','Description') );
		$fields->removeByName('YouTubeData_JSON');
		if ($ytData = json_decode($this->owner->YouTubeData_JSON,1))
		{
			$fields->addFieldToTab('Root.YouTubeData', Forms\LiteralField::create('ytData','<div><pre><xmp>'.print_r($ytData,1).'</xmp></pre></div>') );
		}
		$this->extend('updateCMSFields',$fields);
		return $fields;
	}
	
	public function getBetterButtonsActions() 
	{
        $fields = parent::getBetterButtonsActions();
		if ( ($this->isYouTube()) && (class_exists('UncleCheese\BetterButtons\Actions\BetterButtonCustomAction')) )
		{
//	        $fields->push($button = \UncleCheese\BetterButtons\Actions\BetterButtonCustomAction::create('importFromYouTube', 'Import From YouTube'));
//			$button->removeExtraClass('readonly');
		}
		$this->extend('updateBetterButtonsActions',$fields);
        return $fields;
    }
	
	public function canCreate($member = null,$context = array()) { return true; }
	public function canDelete($member = null,$context = array()) { return true; }
	public function canEdit($member = null,$context = array())   { return true; }
	public function canView($member = null,$context = array()) { return true; }
	
	public function forTemplate()
	{
		return $this->EmbedCodeSafe();
	}
			
	public function EmbedCodeSafe()
	{
		$code = $this->EmbedCode;
		$code = preg_replace("/<object([^>]+)>/i", "<object\\1><param name='wmode' value='opaque' />", $code);
		$code = preg_replace("/<embed/", "<embed wmode='opaque'", $code);
		$code = preg_replace("/<iframe(.+?)src=\"([^\"]+)\"/", "<iframe\\1src=\"\\2?wmode=opaque&amp;wmode=opaque\"", $code);
		$code = preg_replace("/<iframe(.+?)\/>/", "<iframe\\1></iframe>", $code);
		$this->extend('updateEmbedCodeSafe',$code);
		return $code;
	}
	
	public function EmbedURL()
	{
		if ($this->EmbedCode)
		{
			preg_match('/(src="([^"]+)")/',$this->EmbedCode,$src);
			return str_replace(array('"','src='),null,$src[0]);
		}
		if ($data = $this->YouTubeData())
		{
			preg_match('/(src="([^"]+)")/',$data->player->embedHtml,$src);
			return str_replace(array('"','src='),null,$src[0]);
		}
	}
	
	public function PopupEmbedURL()
	{
		$url = $this->EmbedURL();
		$url .= (preg_match('/\?/',$url)) ? '&' : '?';
		$url .= 'autoplay=1';
		$this->extend('updatePopupEmbedURL',$url);
		return $url;
	}
	
	public function VideoThumbnailURL()
	{
		if ( (!$this->isYouTube()) || (!$data = $this->YouTubeData()) || (!$url = $data->snippet->thumbnails->medium->url) )
		{
			$url = $this->VideoThumbnail(false,'default');
		}		
		$this->extend('updateVideoThumbnailURL',$url);
		return $url;
	}
	
	public function VideoThumbnail($img_tag=true, $size="small")
	{
		$html = "";
		$img_url = false;
		
		$match_embed = array();
		if (preg_match("/\/embed\/([^\"\?]+)/i", $this->EmbedCode, $match_embed))
		{
			$code = $match_embed[1];
			$img_url = "http://i1.ytimg.com/vi/".$code."/".($size == "small" ? "default" : "0").".jpg";
		}
		else if (preg_match("/youtube\.com\/v\/([^\?]+)/i", $this->EmbedCode, $match_embed))
		{
			$code = $match_embed[1];
			$img_url = "http://i1.ytimg.com/vi/".$code."/".($size == "small" ? "default" : "0").".jpg";
		}
		
		$match_embed = array();
		if (preg_match("/\/video\/([^\"\?]+)\"/i", $this->EmbedCode, $match_embed))
		{
			$code = $match_embed[1];
			$image = unserialize(file_get_contents("http://vimeo.com/api/v2/video/".$code.".php"));

			$img_url = $image[0]['thumbnail_medium'];
		}
		
		if ($img_url)
		{
			if ($img_tag)
				$html = "<img src='".$img_url."' style='width:120px; -webkit-box-shadow:2px 2px 5px 0px rgba(0, 0, 0, .4); box-shadow:2px 2px 5px 0px rgba(0, 0, 0, .4);' />";
			else
				$html = $img_url;
		}
		
		return $html;
	}
	
	public function VideoThumbnailUrlLarge()
	{
		return $this->VideoThumbnail(false, "large");
	}
	
	public function onBeforeWrite()
	{
		parent::onBeforeWrite();
		$this->updateYouTubeData();
		if ($this->ImportFromYouTube)
		{
			$this->updateFromYouTubeData();
			$this->ImportFromYouTube = false;
		}
	}
	
	public function isYouTube()
	{
		if ($this->YouTubeLink)
		{
			return preg_match('/youtu\.be|youtube/',$this->YouTubeLink);
		}
		elseif ($this->EmbedCode)
		{
			return preg_match('/youtu\.be|youtube|embed/',$this->EmbedCode);
		}
		
		return false;
	}
	
	public function YouTubeVideoID()
	{
		if ($this->isYouTube())
		{
			if ($this->YouTubeLink)
			{
				if (preg_match('/v\=([a-zA-Z0-9]+)/',$this->YouTubeLink,$matches))
				{
					return current($matches);
				}
				if (preg_match('/(?<=\/)([a-zA-Z0-9]+$)|(([a-zA-Z0-9]+)(?=\?))/',$this->YouTubeLink,$matches))
				{
					return current($matches);
				}
			}
			if ($this->EmbedCode)
			{
				if (preg_match("/\/embed\/([^\"\?]+)/i", $this->EmbedCode, $match_embed))
				{
					return $match_embed[1];
				}
				else if (preg_match("/youtube\.com\/v\/([^\?]+)/i", $this->EmbedCode, $match_embed))
				{
					return $match_embed[1];
				}
			}
		}
	}
	
	public function YouTubeData()
	{
		$data = false;
		if ( ($this->isYouTube()) && ($ytData = json_decode($this->YouTubeData_JSON,1)) )
		{
			$data = View\ArrayData::create($ytData);
		}
		$this->extend('updateYouTubeData',$data);
		return $data;
	}
	
	public function YouTubeThumbnailURL($resolution = 'standard')
	{
		$resolutions = [
			'default',
			'medium',
			'high',
			'standard',
			'maxres'
		];
		if (!$YouTubeData = $this->YouTubeData())
		{
			return false;
		}
		if ( (!isset($YouTubeData->snippet->thumbnails->{$resolution}->url)) || (!$url = $YouTubeData->snippet->thumbnails->{$resolution}->url) )
		{
			$key = array_search($resolution,$resolutions);
			return $this->YouTubeThumbnailURL($resolutions[$key-1]);
		}
		$this->extend('updateYouTubeThumbnailURL',$url);
		return $url;
	}
	
	public function CMSThumbnail()
	{
		$thumb = false;
		if ($data = $this->YouTubeData())
		{
			$thumb = ORM\FieldType\DBField::create_field('HTMLText','<img src="'.$this->YouTubeThumbnailURL('medium').'" />');
		}
		$this->extend('updateCMSThumbnail',$thumb);
		return $thumb;
	}
	
	public function importFromYouTube($action,$itemRequest,$request)
	{
		$this->updateYouTubeData();
		$this->importedYouTubeData = false;
		$this->updateFromYouTubeData();		
		$this->onAfterYouTubeImport();
		$this->write();
		return $this->importedYouTubeData ? 'YouTube data imported' : 'Could not retrieve YouTube data';
	}
	
	public function updateFromYouTubeData()
	{
		if ($data = $this->YouTubeData())
		{
			$this->Description = nl2br($data->snippet->description);
			$this->Title = $data->snippet->title;
			$this->EmbedCode = $data->player->embedHtml;
			$this->importedYouTubeData = true;
		}
		return $this;
	}
	
	public function onAfterYouTubeImport()
	{
		$this->extend('onAfterYouTubeImport');
		return $this;
	}
	
	public function updateYouTubeData()
	{
		if ( ($ytID = $this->YouTubeVideoID()) && ($ytKey = SiteConfig::current_site_config()->VideoGallery_YouTubeApiKey) )
		{
			$query = array(
				'id' => $ytID,
				'key' => $ytKey,
				'part' => implode(',',$this->Config()->get('youtube_api_collection'))
			);
			$ch = curl_init('https://www.googleapis.com/youtube/v3/videos?'.http_build_query($query));
			curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			$response = curl_getinfo($ch);
			curl_close($ch);
			if ( ($response['http_code'] == 200) && ($data = json_decode($result,1)) )
			{
				if ( (isset($data['items'])) && (count($data['items'])) )
				{
					$this->YouTubeData_JSON = json_encode(current($data['items']));
				}
			}
		}
		return $this;
	}
	
}



