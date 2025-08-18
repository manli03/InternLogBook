<?php
require_once 'db/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
    exit();
}

header('Content-Type: application/json');
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // This POST handling logic is correct and remains unchanged.
    $data = $_POST;
    $action = $data['action'] ?? '';

    switch ($action) {
        case 'add':
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM logbook_entries WHERE user_id = ? AND entry_date = ?");
            $stmt->execute([$userId, $data['entry_date']]);
            if ($stmt->fetchColumn() > 0) {
                http_response_code(409);
                echo json_encode(['status' => 'error', 'message' => 'An entry for this date already exists.']);
                exit();
            }
            $stmt = $pdo->prepare("INSERT INTO logbook_entries (user_id, entry_date, title, content) VALUES (?, ?, ?, ?)");
            $stmt->execute([$userId, $data['entry_date'], $data['title'], $data['content']]);
            echo json_encode(['status' => 'success', 'message' => 'Entry added.']);
            break;

        case 'edit':
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM logbook_entries WHERE user_id = ? AND entry_date = ? AND id != ?");
            $stmt->execute([$userId, $data['entry_date'], $data['entry_id']]);
            if ($stmt->fetchColumn() > 0) {
                http_response_code(409);
                echo json_encode(['status' => 'error', 'message' => 'An entry for this date already exists.']);
                exit();
            }
            $stmt = $pdo->prepare("UPDATE logbook_entries SET entry_date = ?, title = ?, content = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$data['entry_date'], $data['title'], $data['content'], $data['entry_id'], $userId]);
            echo json_encode(['status' => 'success', 'message' => 'Entry updated.']);
            break;

        case 'delete':
            $stmt = $pdo->prepare("DELETE FROM logbook_entries WHERE id = ? AND user_id = ?");
            $stmt->execute([$data['entry_id'], $userId]);
            echo json_encode(['status' => 'success', 'message' => 'Entry deleted.']);
            break;

        default:
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
            break;
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $where_conditions = ["user_id = ?"];
        $params = [$userId];
        if (isset($_GET['date_filter']) && !empty($_GET['date_filter'])) {
            $where_conditions[] = "entry_date = ?";
            $params[] = $_GET['date_filter'];
        }
        if (isset($_GET['title_filter']) && !empty($_GET['title_filter'])) {
            $where_conditions[] = "title LIKE ?";
            $params[] = "%" . $_GET['title_filter'] . "%";
        }
        $where_clause = implode(" AND ", $where_conditions);
        $sort_dir = isset($_GET['sort_dir']) && $_GET['sort_dir'] === 'asc' ? 'ASC' : 'DESC';
        $limit = 7;
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
        if ($page < 1) {
            $page = 1;
        }
        $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM logbook_entries WHERE $where_clause");
        $count_stmt->execute($params);
        $total_entries = $count_stmt->fetchColumn();
        $total_pages = ceil($total_entries / $limit);
        if ($page > $total_pages && $total_pages > 0) {
            $page = $total_pages;
        }
        $offset = ($page - 1) * $limit;

        $stmt = $pdo->prepare("SELECT * FROM logbook_entries WHERE $where_clause ORDER BY entry_date $sort_dir LIMIT ? OFFSET ?");
        $i = 1;
        foreach ($params as $value) {
            $stmt->bindValue($i, $value);
            $i++;
        }
        $stmt->bindValue($i, $limit, PDO::PARAM_INT);
        $stmt->bindValue($i + 1, $offset, PDO::PARAM_INT);
        $stmt->execute();

        /*
        ================================================================================
        [FIX] Changed fetchAll() to fetchAll(PDO::FETCH_ASSOC).
        This ensures the data is a clean associative array (named keys only),
        which creates valid JSON for the JavaScript to consume. This is the fix.
        ================================================================================
        */
        $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $latest_entry_stmt = $pdo->prepare("SELECT entry_date FROM logbook_entries WHERE user_id = ? ORDER BY entry_date DESC LIMIT 1");
        $latest_entry_stmt->execute([$userId]);
        $latest_date = $latest_entry_stmt->fetchColumn();

        echo json_encode([
            'entries' => $entries,
            'pagination' => ['currentPage' => $page, 'totalPages' => $total_pages, 'totalEntries' => $total_entries],
            'latestDate' => $latest_date ?: null
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit();
}