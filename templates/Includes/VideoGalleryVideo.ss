<h5>$Title</h5>
<% if $YouTubeData %>
	<a href="javascript:;" data-type="iframe" data-src="$PopupEmbedURL" class="yt-video" style="background-image:url($YouTubeThumbnailURL);">
		<img src="$YouTubeThumbnailURL" alt="$Title" />
	</a>
	<div class="vid-description">
		$Description
	</div>
<% else %>
	$EmbedCodeSafe.RAW
<% end_if %>