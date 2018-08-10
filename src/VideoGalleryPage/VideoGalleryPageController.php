<?php

namespace IQnection\VideoGallery;

class VideoGalleryPageController extends \PageController
{
	public function PageCSS()
	{
		return ['/fancybox3/jquery.fancybox.css'];
	}
	
	public function PageJS()
	{
		return ['/fancybox3/jquery.fancybox.js'];
	}
}


