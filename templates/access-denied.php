<?php
// Determine whether to display loading or denied state
// $loading_display is passed from guest.php; fallback to false if not set.
$loading_status = isset($loading_display) ? $loading_display : false;

// Messages for the access denied state (passed from guest.php)
// These should already be set correctly in guest.php
$error_message_display = $error_message ?? "The link you're using is invalid or has expired.\nPlease request a new sharing link from the student.";
$action_message_display = $action_message ?? "Ask the student to generate a new sharing link from their dashboard and send it to you.";
?>

<!-- This div will be the content inside #loading-container or #denied-container in guest.php -->
<div class="w-full h-full flex items-center justify-center p-6">
    <?php if ($loading_status): ?>
        <div class="text-center">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-primary border-t-transparent mb-4">
            </div>
            <h2 class="text-xl font-semibold text-foreground mb-2">Verifying access...</h2>
            <p class="text-muted-foreground">Please wait while we verify your access.</p>
        </div>
    <?php else: ?>
        <div class="bg-card rounded-lg p-8 max-w-md w-full shadow-xl text-center">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shield h-8 w-8 text-destructive" data-lov-id="src/pages/LogbookView.tsx:90:16" data-lov-name="Shield" data-component-path="src/pages/LogbookView.tsx" data-component-line="90" data-component-file="LogbookView.tsx" data-component-name="Shield" data-component-content="%7B%22className%22%3A%22h-8%20w-8%20text-destructive%22%7D"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path></svg>
            </div>
            <h2 class="text-2xl font-bold text-foreground mb-2">Access Denied</h2>
            <p class="text-muted-foreground mb-6" style="white-space: pre-line;"><?php echo htmlspecialchars($error_message_display); ?></p>
            <div class="bg-secondary/50 rounded-lg p-4">
                <h3 class="font-semibold text-foreground mb-2">Need access?</h3>
                <p class="text-sm text-muted-foreground"><?php echo htmlspecialchars($action_message_display); ?></p>
            </div>
        </div>
    <?php endif; ?>
</div>