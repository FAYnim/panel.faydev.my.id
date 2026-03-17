<?php
require_once '../includes/auth.php';
require_once '../includes/csrf.php';
require_once '../includes/db.php';
require_once '../includes/upload.php';

header('Content-Type: application/json; charset=utf-8');

requireAuth();
requireCsrf();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET') {
    handleList();
    exit;
}

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

switch ($action) {
    case 'create':
        handleCreate();
        break;
    case 'update':
        handleUpdate();
        break;
    case 'delete':
        handleDelete();
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function handleList(): void
{
    try {
        $pdo = getDB();

        if (isset($_GET['id']) && ctype_digit((string) $_GET['id'])) {
            $id = (int) $_GET['id'];
            $stmt = $pdo->prepare('SELECT id, title, thumbnail, demo_link, project_date, display_order, created_at, updated_at FROM projects WHERE id = ? LIMIT 1');
            $stmt->execute([$id]);
            $project = $stmt->fetch();

            if (!$project) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Project not found']);
                return;
            }

            echo json_encode(['success' => true, 'data' => $project]);
            return;
        }

        $stmt = $pdo->query('SELECT id, title, thumbnail, demo_link, project_date, display_order, created_at, updated_at FROM projects ORDER BY display_order ASC, project_date DESC');
        $projects = $stmt->fetchAll();

        echo json_encode(['success' => true, 'data' => $projects]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to fetch projects']);
    }
}

function handleCreate(): void
{
    $title = trim((string) ($_POST['title'] ?? ''));
    $demoLink = trim((string) ($_POST['demo_link'] ?? ''));
    $projectDate = trim((string) ($_POST['project_date'] ?? ''));

    if ($title === '' || $projectDate === '') {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Title and project date are required']);
        return;
    }

    if (!validateDate($projectDate)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Invalid project date']);
        return;
    }

    if ($demoLink !== '' && !filter_var($demoLink, FILTER_VALIDATE_URL)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Invalid demo URL']);
        return;
    }

    if (empty($_FILES['thumbnail'])) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Thumbnail image is required']);
        return;
    }

    $upload = handleImageUpload($_FILES['thumbnail']);
    if (!$upload['success']) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => $upload['message'] ?? 'Upload failed']);
        return;
    }

    try {
        $pdo = getDB();
        $nextOrder = (int) $pdo->query('SELECT COALESCE(MAX(display_order), -1) + 1 FROM projects')->fetchColumn();

        $stmt = $pdo->prepare('INSERT INTO projects (title, thumbnail, demo_link, project_date, display_order) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $title,
            $upload['path'],
            $demoLink !== '' ? $demoLink : null,
            $projectDate,
            $nextOrder,
        ]);

        $id = (int) $pdo->lastInsertId();
        $project = fetchProjectById($pdo, $id);

        echo json_encode(['success' => true, 'data' => $project]);
    } catch (Throwable $e) {
        deleteProjectFileIfLocal($upload['path']);
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create project']);
    }
}

function handleUpdate(): void
{
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $title = trim((string) ($_POST['title'] ?? ''));
    $demoLink = trim((string) ($_POST['demo_link'] ?? ''));
    $projectDate = trim((string) ($_POST['project_date'] ?? ''));

    if ($id <= 0 || $title === '' || $projectDate === '') {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'ID, title, and project date are required']);
        return;
    }

    if (!validateDate($projectDate)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Invalid project date']);
        return;
    }

    if ($demoLink !== '' && !filter_var($demoLink, FILTER_VALIDATE_URL)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Invalid demo URL']);
        return;
    }

    $newThumbnailPath = null;
    if (!empty($_FILES['thumbnail']) && (int) $_FILES['thumbnail']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload = handleImageUpload($_FILES['thumbnail']);
        if (!$upload['success']) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => $upload['message'] ?? 'Upload failed']);
            return;
        }
        $newThumbnailPath = $upload['path'];
    }

    try {
        $pdo = getDB();
        $current = fetchProjectById($pdo, $id);
        if (!$current) {
            if ($newThumbnailPath) {
                deleteProjectFileIfLocal($newThumbnailPath);
            }
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Project not found']);
            return;
        }

        $thumbnail = $newThumbnailPath ?? $current['thumbnail'];

        $stmt = $pdo->prepare('UPDATE projects SET title = ?, thumbnail = ?, demo_link = ?, project_date = ? WHERE id = ?');
        $stmt->execute([
            $title,
            $thumbnail,
            $demoLink !== '' ? $demoLink : null,
            $projectDate,
            $id,
        ]);

        if ($newThumbnailPath && !empty($current['thumbnail']) && $current['thumbnail'] !== $newThumbnailPath) {
            deleteProjectFileIfLocal((string) $current['thumbnail']);
        }

        $updated = fetchProjectById($pdo, $id);
        echo json_encode(['success' => true, 'data' => $updated]);
    } catch (Throwable $e) {
        if ($newThumbnailPath) {
            deleteProjectFileIfLocal($newThumbnailPath);
        }
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update project']);
    }
}

function handleDelete(): void
{
    $ids = [];

    if (!empty($_POST['id'])) {
        $id = (int) $_POST['id'];
        if ($id > 0) {
            $ids[] = $id;
        }
    }

    if (!empty($_POST['ids'])) {
        $raw = explode(',', (string) $_POST['ids']);
        foreach ($raw as $part) {
            $part = trim($part);
            if ($part !== '' && ctype_digit($part) && (int) $part > 0) {
                $ids[] = (int) $part;
            }
        }
    }

    $ids = array_values(array_unique($ids));
    if ($ids === []) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'No valid project ID provided']);
        return;
    }

    try {
        $pdo = getDB();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $stmt = $pdo->prepare("SELECT id, thumbnail FROM projects WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $rows = $stmt->fetchAll();

        if ($rows === []) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Project not found']);
            return;
        }

        $del = $pdo->prepare("DELETE FROM projects WHERE id IN ($placeholders)");
        $del->execute($ids);

        foreach ($rows as $row) {
            if (!empty($row['thumbnail'])) {
                deleteProjectFileIfLocal((string) $row['thumbnail']);
            }
        }

        echo json_encode(['success' => true, 'data' => ['deleted' => count($rows)]]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to delete project']);
    }
}

function fetchProjectById(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT id, title, thumbnail, demo_link, project_date, display_order, created_at, updated_at FROM projects WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function validateDate(string $date): bool
{
    $dt = DateTime::createFromFormat('Y-m-d', $date);
    return $dt instanceof DateTime && $dt->format('Y-m-d') === $date;
}

function deleteProjectFileIfLocal(string $relativePath): void
{
    $relativePath = trim($relativePath);
    if ($relativePath === '' || strpos($relativePath, 'src/images/uploads/') !== 0) {
        return;
    }

    $fullPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);
    if (is_file($fullPath)) {
        unlink($fullPath);
    }
}
