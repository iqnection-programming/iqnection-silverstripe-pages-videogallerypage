<?php


class VideoGalleryPageController extends PageController
{
	public function PageCSS()
	{
		return array_merge(
			array(
				'/fancybox3/jquery.fancybox.css'
			),
			parent::PageCSS()
		);
	}
	
	public function PageJS()
	{
		return array_merge(
			array(
				'/fancybox3/jquery.fancybox.js'
			),
			parent::PageJS()
		);
	}
}


