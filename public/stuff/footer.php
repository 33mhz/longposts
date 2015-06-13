</div>

<div id="footer">
    Powered by the beautiful <a href="https://app.net" target="_blank"><i class="fa fa-adn"></i></a> <a href="https://developers.app.net/docs/" target="_blank">API</a>. <a href="https://github.com/33mhz/longposts" title="Source on Github"><i class="fa fa-github"></i></a>
</div>

<script>
$(document).ready(function() {
    $(".author-tstamp").each(function(i) {
        this.innerHTML = moment(this.innerHTML).fromNow();
    });
});
// expand author description
function toggle_description(id) {
    $("#post-"+id+" .author-description").toggleClass("author-open");
    
    if ($("#post-"+id+" .fa-chevron-circle-down").length) {
        $("#post-"+id+" .fa-chevron-circle-down").replaceWith('<i class="fa fa-chevron-circle-up"></i>');
    } else {
        $("#post-"+id+" .fa-chevron-circle-up").replaceWith('<i class="fa fa-chevron-circle-down"></i>');
    }
}
</script>


</body>
</html>