<?php
// This template assumes $username, $entries, and $entry_count are available in its scope
// It does NOT include db/config.php or session_start() as it's meant to be included by another file.

// Ensure variables are defined even if somehow not passed (for robustness)
$username = $username ?? 'Guest';
$entries = $entries ?? [];
$entry_count = $entry_count ?? count($entries); // Calculate if not provided
?>

<div id="flipbook-viewport" class="w-full h-full flex items-center justify-center">
    <div id="flipbook" class="shadow-2xl">
        <!-- LOGBOOK COVER PAGE -->
        <div class="hard">
            <div class="page-content flex flex-col items-center justify-center text-center">
                <!-- Book Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-16 h-16 text-primary mb-4">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <!-- Main Title -->
                <h1 class="text-3xl font-bold text-foreground mb-2">Internship Logbook</h1>
                <!-- Subtitle -->
                <p class="text-lg text-muted-foreground mb-1">Daily Learning Journey</p>
                <!-- Username -->
                <p class="text-xl opacity-90 font-semibold text-primary mb-2"><?php echo htmlspecialchars($username); ?>
                </p>
                <!-- Entry Count -->
                <p class="text-base text-muted-foreground"><?php echo $entry_count; ?> entries</p>
            </div>
        </div>
        <!-- END LOGBOOK COVER PAGE -->

        <div class="hard inside-cover"></div>
        <?php if (!empty($entries)): ?>
            <?php foreach ($entries as $entry): ?>
                <div class="page">
                    <div class="page-content prose">
                        <h2 class="font-bold"><?php echo htmlspecialchars($entry['title']); ?></h2>
                        <p class="text-sm text-muted-foreground"><?php echo htmlspecialchars($entry['entry_date']); ?></p>
                        <div class="mt-4"><?php echo nl2br(htmlspecialchars($entry['content'])); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="page">
                <div class="page-content flex items-center justify-center text-center">
                    <p class="text-muted-foreground">No entries yet.</p>
                </div>
            </div>
        <?php endif; ?>
        <div class="hard inside-cover"></div>
        <div class="hard">
            <div class="page-content flex items-center justify-center">
                <div class="text-center opacity-50">The End</div>
            </div>
        </div>
    </div>
</div>

<div class="book-controls">
    <button id="prev-page">← Previous Page</button>
    <button id="next-page">Next Page →</button>
</div>

<style>
    /* Styles for the flipbook when included by any page */
    .book-controls {
        position: absolute;
        bottom: 2rem;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 1rem;
        z-index: 1000;
        width: auto;
        justify-content: center;
        pointer-events: none;
        /* Allows clicks to pass through to flipbook area */
    }

    .book-controls button {
        pointer-events: auto;
        /* Re-enables clicks for the buttons */
        background-color: rgba(0, 0, 0, 0.6);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        cursor: pointer;
        transition: all 0.2s;
        backdrop-filter: blur(10px);
    }

    .book-controls button:hover {
        background-color: rgba(0, 0, 0, 0.8);
    }

    .book-controls button:disabled {
        /* NEW: Cursor for disabled buttons */
        cursor: not-allowed;
    }


    /* Inner Book Pages: Cream color with subtle texture (different from hard cover) */
    #flipbook .page {
        background-color: hsl(var(--background));
        /* Uses a light cream/off-white from theme */
        border: 1px solid hsl(var(--border));
        background-image:
            linear-gradient(90deg, rgba(0, 0, 0, 0.02) 0px, transparent 1px),
            linear-gradient(rgba(0, 0, 0, 0.02) 0px, transparent 1px);
        background-size: 20px 20px;
        /* Adds a subtle grid texture */
    }

    #flipbook .page-content {
        box-sizing: border-box;
        height: 100%;
        padding: 3rem;
        overflow-y: auto;
        line-height: 1.8;
        font-size: 1.1rem;
        color: hsl(var(--foreground));
        font-family: 'Crimson Text', serif;
        /* Apply font here for consistency */
    }

    /* Hard Covers: Dark blue color with pattern (different from inner pages) */
    #flipbook .hard {
        background-color: hsl(var(--primary-dark));
        /* Uses a dark blue from theme */
        color: white;
        /* Text color for hard covers */
        background-image: linear-gradient(45deg, hsl(var(--primary)) 25%, transparent 25%),
            linear-gradient(-45deg, hsl(var(--primary)) 25%, transparent 25%),
            linear-gradient(45deg, transparent 75%, hsl(var(--primary)) 75%),
            linear-gradient(-45deg, transparent 75%, hsl(var(--primary)) 75%);
        background-size: 20px 20px;
        /* Adds a pattern to the hard cover material */
        box-shadow: inset 0 0 20px rgba(0, 0, 0, 0.5);
    }

    #flipbook .inside-cover {
        background-color: hsl(var(--secondary));
        background-image:
            radial-gradient(hsl(var(--muted)) 2px, transparent 2px),
            radial-gradient(hsl(var(--muted)) 2px, transparent 2px);
        background-size: 40px 40px;
        background-position: 0 0, 20px 20px;
    }

    /* Specific style for the VERY FIRST hard cover page content (the front cover image) */
    /* This overrides the default .hard styles to make the visible front cover white and texture-less */
    #flipbook .hard:first-child .page-content {
        background-color: hsl(var(--card));
        /* Pure white background for the front cover image */
        background-image: none;
        /* No pattern/texture for the front cover image */
        color: hsl(var(--foreground));
        /* Ensure text color is foreground */
        box-shadow: none;
        /* Remove inner shadow from the cover content area */
    }

    .prose {
        color: hsl(var(--foreground));
    }

    .prose h2 {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
        color: hsl(var(--foreground));
    }

    .prose p {
        font-size: 1rem;
        line-height: 1.6;
        color: hsl(var(--foreground));
    }
</style>