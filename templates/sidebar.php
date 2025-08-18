<?php
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<aside class="w-64 flex-shrink-0 bg-card border-r border-border flex flex-col">
    <div class="h-20 flex items-center px-6 border-b border-border">
        <div class="flex items-center gap-3">
            <div class="bg-primary p-2 rounded-md">
                <svg class="w-6 h-6 text-primary-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                    </path>
                </svg>
            </div>
            <span class="text-lg font-semibold text-foreground">InternLog</span>
        </div>
    </div>
    <nav class="flex-1 p-4">
        <a href="dashboard.php"
            class="flex items-center gap-3 px-4 py-2 text-sm font-medium rounded-md <?php echo $current_page === 'dashboard' ? 'bg-accent text-primary' : 'text-muted-foreground hover:bg-accent hover:text-foreground'; ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                </path>
            </svg>
            Dashboard
        </a>
        <a href="logbook.php"
            class="mt-2 flex items-center gap-3 px-4 py-2 text-sm font-medium rounded-md <?php echo $current_page === 'logbook' ? 'bg-accent text-primary' : 'text-muted-foreground hover:bg-accent hover:text-foreground'; ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                </path>
            </svg>
            View as Flipbook
        </a>
    </nav>
    <div class="p-4 border-t border-border">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-foreground"><?php echo htmlspecialchars($username); ?></p>
                <p class="text-xs text-muted-foreground">Intern</p>
            </div>
            <form id="logout-form" action="logout.php" method="POST" class="inline">
                <button type="button" @click="showLogoutModal = true"
                    class="text-muted-foreground hover:text-foreground" title="Logout">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                        </path>
                    </svg>
                </button>
            </form>
        </div>
        <?php if (basename($_SERVER['PHP_SELF']) === 'dashboard.php'): ?>
            <button @click="showShareModal = true"
                class="mt-4 w-full flex items-center justify-center gap-2 bg-secondary text-secondary-foreground px-4 py-2 rounded-md hover:bg-accent transition text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8.684 13.342C8.886 12.938 9 12.482 9 12s-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.368a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z">
                    </path>
                </svg>Share Link
            </button>
        <?php endif; ?>
        <?php if (basename($_SERVER['PHP_SELF']) === 'logbook.php'): ?>
            <p class="text-xs text-muted-foreground mt-4">Click the navigation buttons or use the arrow keys to navigate the logbook.
            </p>
        <?php endif; ?>
    </div>
</aside>