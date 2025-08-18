<?php
require_once 'db/config.php';

$username = $_GET['user'] ?? '';
$key = $_GET['key'] ?? '';

// Initialize state variables for PHP logic
$display_state = 'loading'; // Default: show loading screen initially
// Default messages for denied state, as per image. These will be passed to access-denied.php
$error_message = "The link you're using is invalid or has expired.\nPlease request a new sharing link from the student.";
$action_message = "Ask the student to generate a new sharing link from their dashboard and send it to you.";

$logbook_username = ''; // Will hold the actual username if access granted
$entries = []; // Will hold logbook entries
$entry_count = 0; // Initialize entry count for the cover page

// Perform validation and database lookup
if (empty($username) || empty($key)) {
    $display_state = 'denied';
} else {
    // Verify the secret key
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE username = ? AND secret_key = ?");
    $stmt->execute([$username, $key]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $display_state = 'denied';
    } else {
        $display_state = 'logbook';
        $logbook_username = $user['username']; // Set username for logbook display
        // Get user's entries
        $stmt = $pdo->prepare("SELECT * FROM logbook_entries WHERE user_id = ? ORDER BY entry_date ASC");
        $stmt->execute([$user['id']]);
        $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $entry_count = count($entries); // Get the count for the cover page
    }
}

// Set the title based on the determined state
$page_title = "Internship Logbook Access";
if ($display_state === 'logbook') {
    $page_title = htmlspecialchars($logbook_username) . "'s Internship Logbook";
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>

    <link href="css/output.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Text:ital,wght@0,400;0,600;1,400&display=swap"
        rel="stylesheet">
    <script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
    <script src="js/turn.min.js"></script>

    <style>
        html,
        body {
            overflow: hidden;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Initial hide for content that will be shown by JS */
        #logbook-container,
        #denied-container {
            display: none;
        }
    </style>
</head>

<body class="bg-background">
    <!-- Header -->
    <header id="logbook-header"
        class="h-20 flex-shrink-0 bg-card border-b border-border flex items-center justify-center"
        style="display: none;">
        <h1 class="text-2xl font-bold text-card-foreground">
            <?php echo htmlspecialchars($logbook_username); ?>'s Internship Logbook
        </h1>
    </header>

    <!-- Main Content Area - This will dynamically show one of the three states -->
    <!-- When display_state is loading or denied, this container fills the entire viewport. -->
    <div class="flex-grow w-full h-full flex items-center justify-center p-6 overflow-hidden relative">

        <!-- Loading Container (visible initially) -->
        <div id="loading-container" class="w-full h-full flex items-center justify-center absolute inset-0">
            <?php
            // Pass $loading_display true to show the spinner
            $loading_display = true;
            require 'templates/access-denied.php';
            ?>
        </div>

        <!-- Logbook Container (hidden initially, revealed by JS) -->
        <div id="logbook-container" class="w-full h-full flex items-center justify-center" style="display: none;">
            <?php
            // Pass the specific username to the template, as $username is used directly within it.
            // $entries and $entry_count are also available in this scope.
            $username = $logbook_username;
            require_once 'templates/logbook.php';
            ?>
        </div>

        <!-- Access Denied Container (hidden initially, revealed by JS) -->
        <div id="denied-container" class="w-full h-full flex items-center justify-center" style="display: none;">
            <?php
            // Pass $loading_display false to show the denied message
            $loading_display = false;
            // $error_message and $action_message are already set above in PHP logic
            require 'templates/access-denied.php';
            ?>
        </div>

    </div>

    <!-- Simple Footer -->
    <footer id="logbook-footer" class="h-12 flex-shrink-0 flex items-center justify-center" style="display: none;">
        <p class="text-sm text-muted-foreground">Powered by <span class="font-semibold text-primary">InternLog</span>
        </p>
    </footer>

    <script type="text/javascript">
        // PHP-driven state variable for JavaScript
        const displayState = "<?php echo $display_state; ?>";

        document.addEventListener('DOMContentLoaded', function () {
            const loadingContainer = document.getElementById('loading-container');
            const logbookContainer = document.getElementById('logbook-container');
            const deniedContainer = document.getElementById('denied-container');
            const logbookHeader = document.getElementById('logbook-header');
            const logbookFooter = document.getElementById('logbook-footer');

            // Set delay to ensure loading animation is visible for at least 1 second
            const displayDelay = 1000; // 1 second

            setTimeout(() => {
                loadingContainer.style.display = 'none'; // Always hide loading after delay

                if (displayState === 'logbook') {
                    logbookContainer.style.display = 'flex'; // Show logbook
                    if (logbookHeader) {
                        logbookHeader.style.display = 'flex';
                    }
                    if (logbookFooter) {
                        logbookFooter.style.display = 'flex';
                    }
                    initializeFlipbook(); // Initialize flipbook only when it's visible
                } else if (displayState === 'denied') {
                    deniedContainer.style.display = 'flex'; // Show access denied
                }
            }, displayDelay);
        });

        // Function to initialize the flipbook, this is critical for the Turn.js library.
        // It remains in guest.php because it needs to be called after the HTML structure
        // (which is loaded via templates/logbook.php) is fully in the DOM and visible.
        function initializeFlipbook() {
            var flipbook = $('#flipbook');
            var isTransitioning = false;

            function disableControls() {
                isTransitioning = true;
                $('#prev-page, #next-page').prop('disabled', true).css('opacity', '0.5');
            }

            function enableControls() {
                isTransitioning = false;
                $('#prev-page, #next-page').prop('disabled', false).css('opacity', '1');
            }

            function resizeFlipbook() {
                var viewport = $('#flipbook-viewport');
                flipbook.turn('size', 'auto', 'auto');

                var availableHeight = viewport.height();
                var availableWidth = viewport.width();
                var bookAspectRatio = flipbook.turn('display') === 'double' ? (461 * 2) / 600 : 461 / 600;

                var bookWidth = availableWidth;
                var bookHeight = bookWidth / bookAspectRatio;

                if (bookHeight > availableHeight) {
                    bookHeight = availableHeight;
                    bookWidth = bookHeight * bookAspectRatio;
                }
                flipbook.turn('size', bookWidth, bookHeight);
            }

            flipbook.turn({
                width: 922,
                height: 600,
                elevation: 50,
                acceleration: true,
                autoCenter: true,
                duration: 600,
                display: 'single',
                when: {
                    turning: function (e, page, view) {
                        var totalPages = $(this).turn('pages');
                        // Disable controls near the start/end to prevent display mode changes during quick turns
                        if (page === 1 || page === totalPages ||
                            (page === 2 && $(this).turn('display') === 'single') ||
                            (page === totalPages - 1 && $(this).turn('display') === 'single')) {
                            disableControls();
                        }
                    },
                    turned: function (e, page, view) {
                        var totalPages = $(this).turn('pages');
                        var newDisplay = (page === 1 || page === totalPages) ? 'single' : 'double';
                        if ($(this).turn('display') !== newDisplay) {
                            $(this).turn('display', newDisplay);
                            resizeFlipbook();
                            // Give a bit more time for the turn effect and resize to complete before re-enabling
                            setTimeout(enableControls, 1000);
                        } else {
                            enableControls();
                        }
                    }
                }
            });

            $(window).on('keydown', function (e) {
                if (isTransitioning) return; // Ignore keypresses during transition
                if (e.keyCode === 37) flipbook.turn('previous'); // Left arrow
                else if (e.keyCode === 39) flipbook.turn('next'); // Right arrow
            });

            var resizeTimer;
            $(window).on('resize', function () {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(resizeFlipbook, 150);
            });

            // Initial resize call to fit the flipbook properly
            resizeFlipbook();

            // Add button controls
            $('#prev-page').on('click', function () {
                if (!isTransitioning) flipbook.turn('previous');
            });

            $('#next-page').on('click', function () {
                if (!isTransitioning) flipbook.turn('next');
            });
        }
    </script>
</body>

</html>