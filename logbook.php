<?php
require_once 'db/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Get user's entries from the database
$stmt = $pdo->prepare("SELECT * FROM logbook_entries WHERE user_id = ? ORDER BY entry_date ASC");
$stmt->execute([$_SESSION['user_id']]);
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$username = $stmt->fetchColumn();

// Calculate entry count for the cover page
$entry_count = count($entries);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Logbook - InternLog</title>

    <link href="css/output.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Text:ital,wght@0,400;0,600;1,400&display=swap"
        rel="stylesheet">
    <script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
    <script src="js/turn.min.js"></script>
    <script src="js/alpine.min.js" defer></script>

    <style>
        html,
        body {
            overflow: hidden;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* No specific #flipbook .page-content font-family needed here, handled by template */
    </style>
</head>

<body class="bg-background overflow-hidden font-sans" x-data="baseLayout()">
    <!-- Logout Confirmation Modal -->
    <div x-show="showLogoutModal"
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 backdrop-blur-sm" style="display: none;">
        <div class="bg-card rounded-lg p-8 max-w-md w-full shadow-xl" @click.away="showLogoutModal = false">
            <h3 class="text-lg font-semibold mb-2 text-card-foreground">Confirm Logout</h3>
            <p class="text-muted-foreground mb-6">Are you sure you want to log out?</p>
            <div class="flex justify-end space-x-4">
                <button type="button" @click="showLogoutModal = false"
                    class="bg-secondary text-secondary-foreground px-4 py-2 rounded-md hover:bg-accent">Cancel</button>
                <button type="button" @click="document.getElementById('logout-form').submit()"
                    class="bg-primary text-primary-foreground px-4 py-2 rounded-md hover:bg-primary-dark">Logout</button>
            </div>
        </div>
    </div>

    <div class="flex h-screen">
        <?php
        require_once 'templates/sidebar.php';
        ?>

        <div class="flex-1 flex flex-col items-center justify-center p-6 overflow-hidden relative">
            <?php
            // The variables $username, $entries, and $entry_count are all available
            // in this scope and will be passed to templates/logbook.php when it's included.
            require_once 'templates/logbook.php';
            ?>
        </div>
    </div>

    <!-- Alpine.js data definition for the layout -->
    <script>
        function baseLayout() {
            return {
                showLogoutModal: false // Initialize the modal state to false
                // Add any other top-level Alpine.js data properties here if needed for the overall layout
            }
        }
    </script>

    <script type="text/javascript">
        $(document).ready(function () {
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
                width: 922, height: 600,
                elevation: 50, acceleration: true, autoCenter: true,
                duration: 600,
                display: 'single',
                when: {
                    turning: function (e, page, view) {
                        var totalPages = $(this).turn('pages');
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
                            setTimeout(enableControls, 1000); // Enable controls after transition
                        } else {
                            enableControls();
                        }
                    }
                }
            });

            $(window).on('keydown', function (e) {
                if (isTransitioning) return; // Ignore keypresses during transition
                if (e.keyCode === 37) flipbook.turn('previous');
                else if (e.keyCode === 39) flipbook.turn('next');
            });

            var resizeTimer;
            $(window).on('resize', function () {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(resizeFlipbook, 150);
            });

            resizeFlipbook();

            // Add button controls
            $('#prev-page').on('click', function () {
                if (!isTransitioning) flipbook.turn('previous');
            });

            $('#next-page').on('click', function () {
                if (!isTransitioning) flipbook.turn('next');
            });
        });
    </script>
</body>

</html>