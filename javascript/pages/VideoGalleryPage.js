$(document).ready(function(){
	$('#videos li').each(function(){
		// Fix stupid FF bug
		$(this).children("iframe").each(function(){
			$(this).attr("src", $(this).attr("src").replace(/\?wmode=transparent$/, ""));
		});
	});
	responsiveVideo();
});

$(window).resize(function(){
	responsiveVideo();
});

function responsiveVideo(){
	$('#videos li').each(function(){
		var new_width = $(this).innerWidth();
		var new_height = parseInt(new_width*.65);
		
		$(this).find("iframe,embed,object").height(new_height);
		$(this).find("iframe,embed,object").attr("height", new_height);
		$(this).find("iframe,embed,object").css("height", new_height+"px");
	});
}