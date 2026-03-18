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
            $stmt = $pdo->prepare('SELECT id, title, issuer, thumbnail, credential_link, issue_date, display_order, created_at, updated_at FROM certificates WHERE id = ? LIMIT 1');
            $stmt->execute([$id]);
            $certificate = $stmt->fetch();

            if (!$certificate) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Certificate not found']);
                return;
            }

            echo json_encode(['success' => true, 'data' => $certificate]);
            return;
        }

        $stmt = $pdo->query('SELECT id, title, issuer, thumbnail, credential_link, issue_date, display_order, created_at, updated_at FROM certificates ORDER BY display_order ASC, issue_date DESC');
        $certificates = $stmt->fetchAll();

        echo json_encode(['success' => true, 'data' => $certificates]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to fetch certificates']);
    }
}

function handleCreate(): void
{
    $title = trim((string) ($_POST['title'] ?? ''));
    $issuer = trim((string) ($_POST['issuer'] ?? ''));
    $issueDate = trim((string) ($_POST['issue_date'] ?? ''));
    $credentialLink = trim((string) ($_POST['credential_link'] ?? ''));

    if ($title === '' || $issuer === '' || $issueDate === '') {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Title, issuer, and issue date are required']);
        return;
    }

    if (!validateDate($issueDate)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Invalid issue date format']);
        return;
    }

    if ($credentialLink !== '' && !filter_var($credentialLink, FILTER_VALIDATE_URL)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Invalid credential URL']);
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
        $nextOrder = (int) $pdo->query('SELECT COALESCE(MAX(display_order), -1) + 1 FROM certificates')->fetchColumn();

        $stmt = $pdo->prepare('INSERT INTO certificates (title, issuer, thumbnail, credential_link, issue_date, display_order) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $title,
            $issuer,
            $upload['path'],
            $credentialLink !== '' ? $credentialLink : null,
            $issueDate,
            $nextOrder,
        ]);

        $id = (int) $pdo->lastInsertId();
        $certificate = fetchCertificateById($pdo, $id);

        echo json_encode(['success' => true, 'data' => $certificate]);
    } catch (Throwable $e) {
        deleteCertificateFileIfLocal($upload['path']);
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create certificate']);
    }
}

function handleUpdate(): void
{
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $title = trim((string) ($_POST['title'] ?? ''));
    $issuer = trim((string) ($_POST['issuer'] ?? ''));
    $issueDate = trim((string) ($_POST['issue_date'] ?? ''));
    $credentialLink = trim((string) ($_POST['credential_link'] ?? ''));

    if ($id <= 0 || $title === '' || $issuer === '' || $issueDate === '') {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'ID, title, issuer, and issue date are required']);
        return;
    }

    if (!validateDate($issueDate)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Invalid issue date format']);
        return;
    }

    if ($credentialLink !== '' && !filter_var($credentialLink, FILTER_VALIDATE_URL)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Invalid credential URL']);
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
        $current = fetchCertificateById($pdo, $id);
        if (!$current) {
            if ($newThumbnailPath) {
                deleteCertificateFileIfLocal($newThumbnailPath);
            }
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Certificate not found']);
            return;
        }

        $thumbnail = $newThumbnailPath ?? $current['thumbnail'];

        $stmt = $pdo->prepare('UPDATE certificates SET title = ?, issuer = ?, thumbnail = ?, credential_link = ?, issue_date = ? WHERE id = ?');
        $stmt->execute([
            $title,
            $issuer,
            $thumbnail,
            $credentialLink !== '' ? $credentialLink : null,
            $issueDate,
            $id,
        ]);

        if ($newThumbnailPath && !empty($current['thumbnail']) && $current['thumbnail'] !== $newThumbnailPath) {
            deleteCertificateFileIfLocal((string) $current['thumbnail']);
        }

        $updated = fetchCertificateById($pdo, $id);
        echo json_encode(['success' => true, 'data' => $updated]);
    } catch (Throwable $e) {
        if ($newThumbnailPath) {
            deleteCertificateFileIfLocal($newThumbnailPath);
        }
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update certificate']);
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
        echo json_encode(['success' => false, 'message' => 'No valid certificate ID provided']);
        return;
    }

    try {
        $pdo = getDB();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $stmt = $pdo->prepare("SELECT id, thumbnail FROM certificates WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $rows = $stmt->fetchAll();

        if ($rows === []) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Certificate not found']);
            return;
        }

        $del = $pdo->prepare("DELETE FROM certificates WHERE id IN ($placeholders)");
        $del->execute($ids);

        foreach ($rows as $row) {
            if (!empty($row['thumbnail'])) {
                deleteCertificateFileIfLocal((string) $row['thumbnail']);
            }
        }

        echo json_encode(['success' => true, 'data' => ['deleted' => count($rows)]]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to delete certificate']);
    }
}

function fetchCertificateById(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT id, title, issuer, thumbnail, credential_link, issue_date, display_order, created_at, updated_at FROM certificates WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function validateDate(string $date): bool
{
    $dt = DateTime::createFromFormat('Y-m-d', $date);
    return $dt instanceof DateTime && $dt->format('Y-m-d') === $date;
}

function deleteCertificateFileIfLocal(string $relativePath): void
{
    $relativePath = trim($relativePath);
    if ($relativePath === '' || strpos($relativePath, 'assets/images/uploads/') !== 0) {
        return;
    }

    $fullPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);
    if (is_file($fullPath)) {
        unlink($fullPath);
    }
}
