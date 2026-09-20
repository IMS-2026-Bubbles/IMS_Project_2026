<?php
// Create progress badge

// Create a <span> element with checked or empty box and a label.
// Accepts a string $label and a boolean $flag. If $flag is true, the badge will show a checked box; 
// if false, it will show an empty box. The badge will have a class of 'badge-done' for done and 
// 'badge-open' for open.
// Returns a string containing the HTML for the badge.

function exp_progress_badge(string $label, bool $flag): string {
    $label = htmlspecialchars($label); // Sanitize label to prevent XSS
    $class = $flag ? "badge-done" : "badge-open";
    $symbol = $flag ? "&#9745;" : "&#9744;"; // Checked or empty box (unicode characters html entities)
    return "<span class='badge $class'>$symbol $label</span>";
}
?>