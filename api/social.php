<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/db.php';

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
    case 'reorder':
        handleReorder();
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
            $stmt = $pdo->prepare('SELECT id, name, icon, url, display_order, created_at, updated_at FROM social_links WHERE id = ? LIMIT 1');
            $stmt->execute([$id]);
            $item = $stmt->fetch();

            if (!$item) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Social link not found']);
                return;
            }

            echo json_encode(['success' => true, 'data' => $item]);
            return;
        }

        $stmt = $pdo->query('SELECT id, name, icon, url, display_order, created_at, updated_at FROM social_links ORDER BY display_order ASC, id ASC');
        $rows = $stmt->fetchAll();

        echo json_encode(['success' => true, 'data' => $rows]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to fetch social links']);
    }
}

function handleCreate(): void
{
    $name = trim((string) ($_POST['name'] ?? ''));
    $icon = trim((string) ($_POST['icon'] ?? ''));
    $url = trim((string) ($_POST['url'] ?? ''));

    if ($name === '' || $icon === '' || $url === '') {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Name, icon, and URL are required']);
        return;
    }

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Invalid URL']);
        return;
    }

    try {
        $pdo = getDB();
        $nextOrder = (int) $pdo->query('SELECT COALESCE(MAX(display_order), -1) + 1 FROM social_links')->fetchColumn();

        $stmt = $pdo->prepare('INSERT INTO social_links (name, icon, url, display_order) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $icon, $url, $nextOrder]);

        $id = (int) $pdo->lastInsertId();
        $created = fetchSocialById($pdo, $id);

        echo json_encode(['success' => true, 'data' => $created]);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Social link name must be unique']);
            return;
        }

        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create social link']);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create social link']);
    }
}

function handleUpdate(): void
{
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $name = trim((string) ($_POST['name'] ?? ''));
    $icon = trim((string) ($_POST['icon'] ?? ''));
    $url = trim((string) ($_POST['url'] ?? ''));

    if ($id <= 0 || $name === '' || $icon === '' || $url === '') {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'ID, name, icon, and URL are required']);
        return;
    }

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Invalid URL']);
        return;
    }

    try {
        $pdo = getDB();
        $current = fetchSocialById($pdo, $id);
        if (!$current) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Social link not found']);
            return;
        }

        $stmt = $pdo->prepare('UPDATE social_links SET name = ?, icon = ?, url = ? WHERE id = ?');
        $stmt->execute([$name, $icon, $url, $id]);

        $updated = fetchSocialById($pdo, $id);
        echo json_encode(['success' => true, 'data' => $updated]);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Social link name must be unique']);
            return;
        }

        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update social link']);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update social link']);
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
        echo json_encode(['success' => false, 'message' => 'No valid social link ID provided']);
        return;
    }

    try {
        $pdo = getDB();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $stmt = $pdo->prepare("DELETE FROM social_links WHERE id IN ($placeholders)");
        $stmt->execute($ids);

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Social link not found']);
            return;
        }

        echo json_encode(['success' => true, 'data' => ['deleted' => $stmt->rowCount()]]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to delete social link']);
    }
}

function handleReorder(): void
{
    $rawBody = file_get_contents('php://input');
    $payload = json_decode($rawBody, true);

    if (!is_array($payload) || !isset($payload['order']) || !is_array($payload['order'])) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Invalid reorder payload']);
        return;
    }

    $orderItems = [];
    foreach ($payload['order'] as $item) {
        if (!is_array($item)) {
            continue;
        }

        $id = isset($item['id']) ? (int) $item['id'] : 0;
        $displayOrder = isset($item['display_order']) ? (int) $item['display_order'] : null;

        if ($id > 0 && $displayOrder !== null) {
            $orderItems[] = ['id' => $id, 'display_order' => $displayOrder];
        }
    }

    if ($orderItems === []) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'No valid order items provided']);
        return;
    }

    try {
        $pdo = getDB();
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('UPDATE social_links SET display_order = ? WHERE id = ?');
        foreach ($orderItems as $item) {
            $stmt->execute([$item['display_order'], $item['id']]);
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'data' => ['updated' => count($orderItems)]]);
    } catch (Throwable $e) {
        if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to reorder social links']);
    }
}

function fetchSocialById(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT id, name, icon, url, display_order, created_at, updated_at FROM social_links WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}
