<h1>$MenuTitle</h1>
$Content
<ul id="videos">
    <% loop Videos %>
        <li>
            <h5>$Title</h5>
            $EmbedCodeSafe
        </li>
    <% end_loop %>
</ul><!--videos-->