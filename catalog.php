<?php
// Katalog publik: tidak perlu login.
require __DIR__ . '/bootstrap.php';

try {
    switch (action()) {
        case 'games':
            out(['games' => db()->query('SELECT id, title, genre, min_players, max_players, duration, difficulty, description, is_available
                FROM games WHERE is_active = 1 ORDER BY title')->fetchAll()]);

        case 'menu':
            out(['menu' => db()->query('SELECT id, name, price, category, description, image_url FROM menu WHERE is_active = 1 ORDER BY name')->fetchAll()]);

        case 'pages':
            $type = (string)($_GET['type'] ?? '');
            $sql = 'SELECT p.id, p.type, p.title, p.description, p.image_url, p.created_at, p.updated_at, u.name AS author_name
                FROM pages p LEFT JOIN users u ON u.id = p.author_id WHERE p.is_active = 1';
            $params = [];
            if (in_array($type, ['content', 'article'], true)) {
                $sql .= ' AND p.type = ?';
                $params[] = $type;
            }
            $sql .= ' ORDER BY p.created_at DESC, p.id DESC';
            $s = db()->prepare($sql);
            $s->execute($params);
            out(['pages' => $s->fetchAll()]);

        case 'page':
            $s = db()->prepare('SELECT p.id, p.type, p.title, p.description, p.content, p.image_url, p.created_at, p.updated_at, u.name AS author_name
                FROM pages p LEFT JOIN users u ON u.id = p.author_id WHERE p.id = ? AND p.is_active = 1');
            $s->execute([(int)($_GET['id'] ?? 0)]);
            $p = $s->fetch();
            if (!$p) fail('Halaman tidak ditemukan.', 404);
            out(['page' => $p]);

        default:
            fail('Aksi tidak dikenal.', 404);
    }
} catch (PDOException $e) {
    fail('Katalog belum siap. Impor ulang database.sql di phpMyAdmin, lalu buka migrate.php sekali (aman, data lama tidak terhapus). Untuk fitur halaman, impor juga pages.sql.', 500);
}