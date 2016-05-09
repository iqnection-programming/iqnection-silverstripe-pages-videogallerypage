<?
	class Video extends DataObject
	{
		private static $db = array(
			"SortOrder" => "Int",
			"Title" => "Varchar(255)",
			"EmbedCode" => "Varchar(255)"
		);
		
		private static $default_sort = "SortOrder";
		
		private static $summary_fields = array(
			"Title" => "Title"
		);
		
		private static $has_one = array(
			"VideoGalleryPage" => "VideoGalleryPage"
		);

		public function getCMSFields()
		{	
			return new FieldList(
				new TextField("Title", "Video Title"),
				new TextAreaField("EmbedCode", "Video Embed Code")
			);
		}
				
		public function EmbedCodeSafe()
		{
			$code = $this->EmbedCode;
			$code = preg_replace("/<object([^>]+)>/i", "<object\\1><param name='wmode' value='opaque' />", $code);
			$code = preg_replace("/<embed/", "<embed wmode='opaque'", $code);
			$code = preg_replace("/<iframe(.+?)src=\"([^\"]+)\"/", "<iframe\\1src=\"\\2?wmode=opaque&amp;wmode=opaque\"", $code);
			$code = preg_replace("/<iframe(.+?)\/>/", "<iframe\\1></iframe>", $code);
			
			return $code;
		}
		
		public function EmbedURL()
		{
			preg_match('/(src="([^"]+)")/',$this->EmbedCode,$src);
			return str_replace(array('"','src='),null,$src[0]);
		}
		
		public function VideoThumbnailURL()
		{
			return $this->VideoThumbnail(false,'default');
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
		
		public function canCreate($member = null) { return true; }
		public function canDelete($member = null) { return true; }
		public function canEdit($member = null)   { return true; }
		public function canView($member = null) { return true; }
	}
	
	class VideoGalleryPage extends Page
	{
		static $icon = "iq-videogallerypage/images/icon-videogallerypage";
		
		private static $has_many = array(
			"Videos" => "Video"
		);
		
		public function getCMSFields()
		{
			$fields = parent::getCMSFields();
			$videos_config = GridFieldConfig::create()->addComponents(				
				new GridFieldSortableRows('SortOrder'),
				new GridFieldToolbarHeader(),
				new GridFieldAddNewButton('toolbar-header-right'),
				new GridFieldSortableHeader(),
				new GridFieldDataColumns(),
				new GridFieldPaginator(10),
				new GridFieldEditButton(),
				new GridFieldDeleteAction(),
				new GridFieldDetailForm()				
			);
			$fields->addFieldToTab('Root.Videos', new GridField('Videos','Videos',$this->Videos(),$videos_config));
			return $fields;
		}	
	}
	
	class VideoGalleryPage_Controller extends Page_Controller
	{
		public function init()
		{
			parent::init();
		}	
	}