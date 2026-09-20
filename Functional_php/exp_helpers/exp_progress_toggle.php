<script>
function toggleProgress($exp_section) {
    // Implementation for toggling progress
    var progress = document.getElementById("progress");
    var xmlProgress = new XMLHttpRequest();
    if (progress.checked) {
        // Mark as done
        // Build AJAX request
            // Open POST request to exp_update_progress.php
        xmlProgress.open("POST", "exp_update_progress.php", true);
            // Set request header for form data
        xmlProgress.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            // Send exp_ID and progress=done to exp_update_progress.php
        xmlProgress.send("exp_ID=<?php echo urlencode($exp_ID); ?>&section=" + $exp_section + "&progress=done");
        // Log file?
        console.log("Experiment marked as done.");
    } else {
        // Mark as not done
        // Build AJAX request
            // Open POST request to exp_update_progress.php
        xmlProgress.open("POST", "exp_update_progress.php", true);
            // Set request header for form data
        xmlProgress.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            // Send exp_ID and progress=not_done to exp_update_progress.php
        xmlProgress.send("exp_ID=<?php echo urlencode($exp_ID); ?>&section=" + $exp_section + "&progress=not_done");
        // Log file?
        console.log("Experiment marked as not done.");
    }
}
</script>