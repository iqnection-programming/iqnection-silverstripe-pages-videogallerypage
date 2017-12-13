<h5>$Title</h5>
<% if $YouTubeData %>
	<a href="javascript:;" data-type="iframe" data-src="$PopupEmbedURL" class="yt-video" style="background-image:url($YouTubeData.snippet.thumbnails.standard.url);">
		<img src="$YouTubeData.snippet.thumbnails.standard.url" alt="$Title" />
	</a>
	<div class="vid-description">
		$Description
	</div>
<% else %>
	$EmbedCodeSafe
<% end_if %>