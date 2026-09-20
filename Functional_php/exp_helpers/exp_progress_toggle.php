<script>
function toggleProgress($exp_section) {
    // Implementation for toggling progress
    var progress = document.getElementById("progress");
    var xmlProgress = new XMLHttpRequest();

    // Build AJAX request
        // Open POST request to exp_update_progress.php
    xmlProgress.open("POST", "exp_update_progress.php", true);
        // Set request header for form data
    xmlProgress.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xmlProgress.onload = function() {
        // Runs when response arrives
        if (xmlProgress.status === 200 && xmlProgress.responseText.includes('success')) {
            console.log("Progress updated successfully.");
        } else {
            console.error("Error updating progress: " + xmlProgress.responseText);
        }
    };
    xmlProgress.send("exp_ID=<?php echo urlencode($exp_ID); ?>&section=<?php echo urlencode($exp_section); ?>&progress=<?php echo urlencode(progress.checked ? 'done' : 'not_done'); ?>");
}
</script>