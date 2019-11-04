</div>

<div id="footer">
    Powered by the beautiful <a href="https://pnut.io">Pnut</a> <a href="http://docs.pnut.io/">API</a>. <a href="https://github.com/33mhz/longposts" title="Source on Github">[source]</a>
</div>

<script>
$(document).ready(function() {
    $(".tstamp").each(function(i) {
        this.innerHTML = moment(this.innerHTML).fromNow();
    });
});
// expand author description
function toggle_description(id) {
    $("#post-"+id+" .author-description").toggleClass("author-open");
    $("#post-"+id+" .author-button").toggleClass("up down");
}
</script>

<script src="/static/js/moment.js"></script>
</body>
</html>