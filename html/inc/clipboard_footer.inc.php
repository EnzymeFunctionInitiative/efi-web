
<script>
var theButton = document.querySelector('#clipboard-citation-button');
if (theButton) {
    theButton.addEventListener('click' , ()=> {
        var theText = document.querySelector("#clipboard-citation").innerText;
        navigator.clipboard.writeText(theText).then(
            function() {
                /* clipboard successfully set */
            }, 
            function() {
                /* clipboard write failed */
            }
        );
    });
}
</script>

