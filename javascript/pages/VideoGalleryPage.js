(function($){
	"use strict";
	$(document).ready(function(){
		$("a.yt-video").each(function(){
			$(this).fancybox({
				caption : $(this).siblings('.vid-description').text()
			});			
		});
	});
}(jQuery));