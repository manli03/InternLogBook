<?php
require_once 'db/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Get user's secret key and username
$stmt = $pdo->prepare("SELECT username, secret_key FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
$secret_key = $user['secret_key'];
$username = $user['username'];

$share_url = "http://" . $_SERVER['HTTP_HOST'] . preg_replace('/dashboard\.php$/', '', $_SERVER['PHP_SELF']) . "guest.php?user=" . urlencode($username) . "&key=" . $secret_key;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - InternLog</title>
    <script src="js/alpine.min.js" defer></script>
    <link href="css/output.css" rel="stylesheet">
    <style>
        .toast-enter-active,
        .toast-leave-active {
            transition: all 300ms ease-in-out;
        }

        .toast-enter,
        .toast-leave-to {
            opacity: 0;
            transform: translateY(20px);
        }
    </style>
</head>

<body class="bg-background text-foreground" x-data="dashboard()">
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

        <div class="flex-1 overflow-y-auto">
            <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="rounded-lg text-center mb-8 border border-border overflow-hidden"
                    style="background-image: linear-gradient(rgba(224, 239, 255, 0.9), rgba(224, 239, 255, 0.9)), url('assets/welcome-banner.jpg'); background-size: cover; background-position: center;">
                    <div class="p-8 md:p-12">
                        <h2 class="text-3xl md:text-4xl font-bold text-foreground mb-2">Welcome to Your Internship
                            Journey! 👋</h2>
                        <p class="text-muted-foreground max-w-2xl mx-auto mb-6">Track your daily activities, learnings,
                            and progress throughout your internship.</p><button @click="showAddModal = true"
                            class="bg-primary text-primary-foreground px-5 py-3 rounded-md hover:bg-primary-dark transition duration-150 shadow-sm font-semibold">+
                            Add New Entry</button>
                    </div>
                </div>

                <div class="bg-card rounded-lg shadow-md p-6 border border-border">
                    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <form @submit.prevent="applyFilters()" class="flex space-x-4">
                            <div class="flex-1"><input type="date" x-model="filters.date"
                                    class="w-full p-2 bg-input border-border rounded-md focus:outline-none focus:ring-2 focus:ring-ring">
                            </div>
                            <div class="flex-1"><input type="text" x-model="filters.title"
                                    placeholder="Search by title..."
                                    class="w-full p-2 bg-input border-border rounded-md focus:outline-none focus:ring-2 focus:ring-ring">
                            </div><button type="submit"
                                class="bg-secondary text-secondary-foreground px-4 py-2 rounded-md hover:bg-accent transition">Filter</button><a
                                href="#" @click.prevent="clearFilters()" x-show="filters.date || filters.title"
                                class="bg-secondary text-secondary-foreground px-4 py-2 rounded-md hover:bg-accent transition">Clear</a>
                        </form>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-border">
                            <thead>
                                <tr>
                                    <th
                                        class="px-6 py-3 bg-muted text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                        <a href="#" @click.prevent="applySort()"
                                            class="flex items-center space-x-1 group"><span>Date</span><svg
                                                x-show="sortDir === 'asc'" class="w-4 h-4" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 15l7-7 7 7" />
                                            </svg><svg x-show="sortDir === 'desc'" class="w-4 h-4" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg></a>
                                    </th>
                                    <th
                                        class="px-6 py-3 bg-muted text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                        Title</th>
                                    <th
                                        class="px-6 py-3 bg-muted text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-card divide-y divide-border">
                                <template x-if="loading">
                                    <td colspan="3" class="text-center py-10 text-muted-foreground">Loading...</td>
                                </template>
                                <template x-if="!loading && entries.length === 0">
                                    <td colspan="3" class="text-center py-10 text-muted-foreground">No entries found.
                                    </td>
                                </template>
                                <template x-for="entry in entries" :key="entry.id">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground"
                                            x-text="entry.entry_date"></td>
                                        <td class="px-6 py-4 text-sm font-medium text-foreground" x-text="entry.title">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex space-x-4"><button @click="openEditModal(entry)"
                                                    class="text-primary hover:text-primary-dark"><svg class="w-5 h-5"
                                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg></button><button type="button"
                                                    @click="openDeleteModal(entry.id)"
                                                    class="text-destructive hover:opacity-75"><svg class="w-5 h-5"
                                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg></button></div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <template x-if="!loading && pagination.totalPages > 1">
                        <div class="mt-6 flex justify-center items-center space-x-2"><a href="#"
                                @click.prevent="changePage(pagination.currentPage - 1)"
                                :class="pagination.currentPage <= 1 ? 'pointer-events-none text-muted-foreground opacity-50' : 'text-primary hover:text-primary-dark'"
                                class="px-3 py-1 rounded-md transition">« Previous</a><template
                                x-for="i in Array.from({length: pagination.totalPages}, (_, k) => k + 1)"><a href="#"
                                    @click.prevent="changePage(i)"
                                    :class="pagination.currentPage == i ? 'bg-primary text-primary-foreground' : 'bg-card text-foreground hover:bg-accent'"
                                    class="px-3 py-1 border border-border rounded-md transition"
                                    x-text="i"></a></template><a href="#"
                                @click.prevent="changePage(pagination.currentPage + 1)"
                                :class="pagination.currentPage >= pagination.totalPages ? 'pointer-events-none text-muted-foreground opacity-50' : 'text-primary hover:text-primary-dark'"
                                class="px-3 py-1 rounded-md transition">Next »</a></div>
                    </template>
                </div>
            </main>
        </div>
    </div>

    <!-- Modals -->
    <div x-show="showAddModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 backdrop-blur-sm"
        style="display: none;">
        <div class="bg-card rounded-lg p-8 max-w-md w-full shadow-xl" @click.away="showAddModal = false">
            <h3 class="text-lg font-semibold mb-4 text-card-foreground">Add New Entry</h3>
            <form @submit.prevent="submitAddEntry($event)" x-ref="addForm"><input type="hidden" name="action"
                    value="add">
                <div class="mb-4"><label class="block text-muted-foreground text-sm font-bold mb-2">Date</label><input
                        type="date" name="entry_date" required :value="addEntryDate"
                        class="bg-input shadow-sm border border-border rounded-md w-full py-2 px-3 text-foreground focus:outline-none focus:ring-2 focus:ring-ring">
                </div>
                <div class="mb-4"><label class="block text-muted-foreground text-sm font-bold mb-2">Title</label><input
                        type="text" name="title" required
                        class="bg-input shadow-sm border border-border rounded-md w-full py-2 px-3 text-foreground focus:outline-none focus:ring-2 focus:ring-ring">
                </div>
                <div class="mb-4"><label
                        class="block text-muted-foreground text-sm font-bold mb-2">Content</label><textarea
                        name="content" required rows="4"
                        class="bg-input shadow-sm border border-border rounded-md w-full py-2 px-3 text-foreground focus:outline-none focus:ring-2 focus:ring-ring"></textarea>
                </div>
                <div class="flex justify-end space-x-4"><button type="button" @click="showAddModal = false"
                        class="bg-secondary text-secondary-foreground px-4 py-2 rounded-md hover:bg-accent">Cancel</button><button
                        type="submit"
                        class="bg-primary text-primary-foreground px-4 py-2 rounded-md hover:bg-primary-dark">Save
                        Entry</button></div>
            </form>
        </div>
    </div>
    <div x-show="showEditModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 backdrop-blur-sm"
        style="display: none;">
        <div class="bg-card rounded-lg p-8 max-w-md w-full shadow-xl" @click.away="showEditModal = false">
            <h3 class="text-lg font-semibold mb-4 text-card-foreground">Edit Entry</h3>
            <form @submit.prevent="submitEditEntry($event)"><input type="hidden" name="action" value="edit"><input
                    type="hidden" name="entry_id" x-model="editEntry.id">
                <div class="mb-4"><label class="block text-muted-foreground text-sm font-bold mb-2">Date</label><input
                        type="date" name="entry_date" required x-model="editEntry.date"
                        class="bg-input shadow-sm border border-border rounded-md w-full py-2 px-3 text-foreground focus:outline-none focus:ring-2 focus:ring-ring">
                </div>
                <div class="mb-4"><label class="block text-muted-foreground text-sm font-bold mb-2">Title</label><input
                        type="text" name="title" required x-model="editEntry.title"
                        class="bg-input shadow-sm border border-border rounded-md w-full py-2 px-3 text-foreground focus:outline-none focus:ring-2 focus:ring-ring">
                </div>
                <div class="mb-4"><label
                        class="block text-muted-foreground text-sm font-bold mb-2">Content</label><textarea
                        name="content" required rows="4" x-model="editEntry.content"
                        class="bg-input shadow-sm border border-border rounded-md w-full py-2 px-3 text-foreground focus:outline-none focus:ring-2 focus:ring-ring"></textarea>
                </div>
                <div class="flex justify-end space-x-4"><button type="button" @click="showEditModal = false"
                        class="bg-secondary text-secondary-foreground px-4 py-2 rounded-md hover:bg-accent">Cancel</button><button
                        type="submit"
                        class="bg-primary text-primary-foreground px-4 py-2 rounded-md hover:bg-primary-dark">Save
                        Changes</button></div>
            </form>
        </div>
    </div>
    <div x-show="showShareModal"
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 backdrop-blur-sm" style="display: none;">
        <div class="bg-card rounded-lg p-8 max-w-lg w-full shadow-2xl" @click.away="showShareModal = false">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-card-foreground flex items-center gap-3"><svg
                        class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8.684 13.342C8.886 12.938 9 12.482 9 12s-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.368a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z">
                        </path>
                    </svg> Share Your Logbook</h3><button @click="showShareModal = false"
                    class="text-muted-foreground hover:text-foreground text-2xl">×</button>
            </div>
            <div class="mb-4"><label class="block text-muted-foreground text-sm font-medium mb-2">Shareable Link</label>
                <div class="flex items-center space-x-2"><input type="text" :value="shareUrl"
                        class="flex-1 p-3 border border-border bg-input rounded-md focus:ring-2 focus:ring-ring"
                        readonly><button @click="copyShareLink()"
                        :class="{ 'border-success': shareLinkCopied, 'border-border': !shareLinkCopied }"
                        class="p-3 border rounded-md hover:bg-accent transition-colors"><svg x-show="!shareLinkCopied"
                            class="w-5 h-5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z">
                            </path>
                        </svg><svg x-show="shareLinkCopied" class="w-5 h-5 text-success" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg></button></div>
            </div>
            <div class="bg-accent/50 border border-border p-4 rounded-lg">
                <h4 class="font-semibold text-foreground flex items-center gap-2 mb-2"><svg class="w-5 h-5" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg> How it works:</h4>
                <ul class="list-disc list-inside text-sm text-muted-foreground space-y-1">
                    <li>Supervisors can view your logbook without creating an account.</li>
                    <li>The link provides read-only access to all your entries.</li>
                    <li>You can regenerate the link anytime to disable the old one.</li>
                </ul>
            </div>
            <div class="mt-6 flex justify-end space-x-4"><button @click="regenerateLink()"
                    class="flex items-center gap-2 bg-card text-card-foreground px-4 py-2 rounded-md hover:bg-accent transition duration-150 border border-border"><svg
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg> Generate New Link</button><button @click="copyShareLink();"
                    class="flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2 rounded-md hover:bg-primary-dark transition duration-150 shadow-sm"><svg
                        class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z">
                        </path>
                    </svg> Copy Link</button></div>
        </div>
    </div>
    <div x-show="showDeleteModal"
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 backdrop-blur-sm" style="display: none;">
        <div class="bg-card rounded-lg p-8 max-w-md w-full shadow-xl" @click.away="showDeleteModal = false">
            <h3 class="text-lg font-semibold mb-2 text-card-foreground">Confirm Deletion</h3>
            <p class="text-muted-foreground mb-6">Are you sure you want to delete this entry? This action cannot be
                undone.</p>
            <div class="flex justify-end space-x-4"><button type="button" @click="showDeleteModal = false"
                    class="bg-secondary text-secondary-foreground px-4 py-2 rounded-md hover:bg-accent">Cancel</button><button
                    type="button" @click="submitDelete()"
                    class="bg-destructive text-destructive-foreground px-4 py-2 rounded-md hover:opacity-80">Delete</button>
            </div>
        </div>
    </div>

    <!-- Toast Notifications Container -->
    <div class="fixed bottom-0 right-0 p-8 space-y-4 w-full max-w-xs z-50">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="true" x-transition:enter="toast-enter" x-transition:enter-start="toast-enter"
                x-transition:enter-end="opacity-100 transform-none" x-transition:leave="toast-leave-active"
                x-transition:leave-start="opacity-100 transform-none" x-transition:leave-end="toast-leave-to"
                class="w-full rounded-lg shadow-lg pointer-events-auto"
                :class="{ 'bg-success text-success-foreground': toast.type === 'success', 'bg-destructive text-destructive-foreground': toast.type === 'delete', 'bg-info text-info-foreground': toast.type === 'info' }">
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0"><svg x-show="toast.type === 'success'" class="h-6 w-6" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg><svg x-show="toast.type === 'delete'" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg><svg x-show="toast.type === 'info'" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg></div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p class="text-sm font-medium" x-text="toast.message"></p>
                        </div>
                        <div class="ml-4 flex-shrink-0 flex"><button @click="removeToast(toast.id)"
                                class="inline-flex rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white opacity-70 hover:opacity-100 transition">×</button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <script>
        function dashboard() {
            return {
                loading: true, showAddModal: false, showEditModal: false, showShareModal: false,
                showDeleteModal: false, showLogoutModal: false,
                entries: [], editEntry: { id: null, date: '', title: '', content: '' }, deleteEntryId: null,
                sortDir: 'desc',
                filters: { date: '', title: '' },
                pagination: { currentPage: 1, totalPages: 1 },
                addEntryDate: '',
                shareUrl: '<?php echo html_entity_decode(htmlspecialchars($share_url)); ?>',
                shareLinkCopied: false,
                toasts: [],
                addToast(toast) { toast.id = Date.now(); this.toasts.push(toast); setTimeout(() => this.removeToast(toast.id), 4000); },
                removeToast(id) { this.toasts = this.toasts.filter(t => t.id !== id); },
                init() {
                    const urlParams = new URLSearchParams(window.location.search);
                    this.pagination.currentPage = parseInt(urlParams.get('page')) || 1;
                    this.sortDir = urlParams.get('sort_dir') || 'desc';
                    this.filters.date = urlParams.get('date_filter') || '';
                    this.filters.title = urlParams.get('title_filter') || '';
                    this.fetchEntries();
                },
                fetchEntries() {
                    this.loading = true;
                    const params = new URLSearchParams();
                    if (this.pagination.currentPage > 1) params.append('page', this.pagination.currentPage);
                    if (this.sortDir !== 'desc') params.append('sort_dir', this.sortDir);
                    if (this.filters.date) params.append('date_filter', this.filters.date);
                    if (this.filters.title) params.append('title_filter', this.filters.title);
                    const query = params.toString();
                    history.pushState({}, '', `${window.location.pathname}${query ? `?${query}` : ''}`);
                    fetch(`api.php${query ? `?${query}` : ''}`).then(res => res.json()).then(data => {
                        if (data.entries) {
                            this.entries = data.entries;
                            this.pagination = data.pagination;
                            this.calculateNextEntryDate(data.latestDate);
                        }
                        this.loading = false;
                    }).catch(() => { this.loading = false; this.addToast({ message: 'Error loading entries.', type: 'delete' }); });
                },
                calculateNextEntryDate(latestDate) {
                    const today = new Date();
                    let nextDate = today;
                    if (latestDate) {
                        const latest = new Date(latestDate);
                        latest.setUTCDate(latest.getUTCDate() + 1);
                        nextDate = latest;
                    }
                    this.addEntryDate = nextDate.toISOString().slice(0, 10);
                },
                changePage(page) { if (page < 1 || page > this.pagination.totalPages) return; this.pagination.currentPage = page; this.fetchEntries(); },
                applySort() { this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc'; this.pagination.currentPage = 1; this.fetchEntries(); },
                applyFilters() { this.pagination.currentPage = 1; this.fetchEntries(); },
                clearFilters() { this.filters.date = ''; this.filters.title = ''; this.pagination.currentPage = 1; this.fetchEntries(); },
                openEditModal(entry) {
                    // [FIX] Update properties individually to preserve reactivity.
                    this.editEntry.id = entry.id;
                    this.editEntry.date = entry.entry_date;
                    this.editEntry.title = entry.title;
                    this.editEntry.content = entry.content;
                    this.showEditModal = true;
                },
                openDeleteModal(id) { this.deleteEntryId = id; this.showDeleteModal = true; },
                async handleFormSubmit(formElement, successMessage, modalName) {
                    const formData = new FormData(formElement);
                    const response = await fetch('api.php', { method: 'POST', body: formData });
                    const data = await response.json();

                    if (response.ok && data.status === 'success') {
                        this.addToast({ message: successMessage, type: 'success' });
                        this[modalName] = false;
                        if (modalName === 'showAddModal') { formElement.reset(); }
                        this.fetchEntries();
                    } else {
                        this.addToast({ message: data.message || 'An error occurred.', type: 'delete' });
                    }
                },
                submitAddEntry(e) { this.handleFormSubmit(e.target, 'New entry added!', 'showAddModal'); },
                submitEditEntry(e) { this.handleFormSubmit(e.target, 'Entry updated!', 'showEditModal'); },
                submitDelete() {
                    const formData = new FormData();
                    formData.append('action', 'delete'); formData.append('entry_id', this.deleteEntryId);
                    fetch('api.php', { method: 'POST', body: formData }).then(res => res.json()).then(data => {
                        if (data.status === 'success') {
                            this.addToast({ message: 'Entry has been deleted.', type: 'delete' });
                            if (this.entries.length === 1 && this.pagination.currentPage > 1) this.pagination.currentPage--;
                            this.fetchEntries();
                            this.showDeleteModal = false;
                        } else {
                            this.addToast({ message: data.message || 'Could not delete entry.', type: 'delete' });
                        }
                    });
                },
                copyShareLink() {
                    navigator.clipboard.writeText(this.shareUrl);
                    this.addToast({ message: 'Link copied to clipboard!', type: 'info' });
                    this.shareLinkCopied = true;
                    setTimeout(() => this.shareLinkCopied = false, 2000);
                },
                regenerateLink() {
                    fetch('regenerate_key.php').then(res => res.json()).then(data => {
                        if (data.status === 'success' && data.newUrl) {
                            this.shareUrl = data.newUrl;
                            this.addToast({ message: 'New share link generated!', type: 'success' });
                        } else {
                            this.addToast({ message: 'Failed to generate new link.', type: 'delete' });
                        }
                    }).catch(() => this.addToast({ message: 'An error occurred.', type: 'delete' }));
                }
            }
        }
    </script>
</body>

</html>