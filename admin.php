<?php
require __DIR__ . '/bootstrap.php';
$admin = require_admin();

const OSTAT = ['baru', 'diproses', 'selesai', 'dibatalkan'];
const BSTAT = ['menunggu', 'dikonfirmasi', 'selesai', 'dibatalkan'];

function one(string $sql, array $p = []) {
    $s = db()->prepare($sql);
    $s->execute($p);
    return $s->fetchColumn();
}

switch (action()) {
    case 'stats':
        out([
            'users' => (int)one("SELECT COUNT(*) FROM users WHERE role = 'user'"),
            'orders' => (int)one('SELECT COUNT(*) FROM orders'),
            'bookings' => (int)one('SELECT COUNT(*) FROM bookings'),
            'revenue' => (int)one("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status = 'selesai'"),
            'orders_new' => (int)one("SELECT COUNT(*) FROM orders WHERE status = 'baru'"),
            'bookings_pending' => (int)one("SELECT COUNT(*) FROM bookings WHERE status = 'menunggu'"),
            'logins_today' => (int)one("SELECT COUNT(*) FROM login_history WHERE status = 'berhasil' AND DATE(created_at) = CURDATE()"),
        ]);

    case 'orders':
        $r = db()->query('SELECT o.id, o.item_name, o.qty, o.total, o.status, o.created_at, u.name AS user_name, u.email AS user_email
            FROM orders o JOIN users u ON u.id = o.user_id ORDER BY o.created_at DESC, o.id DESC LIMIT 500')->fetchAll();
        out(['orders' => $r]);

    case 'order_status':
        $d = body();
        if (!in_array($d['status'] ?? '', OSTAT, true)) fail('Status tidak valid.');
        $s = db()->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $s->execute([$d['status'], (int)($d['id'] ?? 0)]);
        out(['ok' => true]);

    case 'bookings':
        $r = db()->query('SELECT b.id, b.booking_date, b.booking_time, b.people, b.status, u.name AS user_name, u.phone AS user_phone
            FROM bookings b JOIN users u ON u.id = b.user_id ORDER BY b.booking_date DESC, b.booking_time DESC, b.id DESC LIMIT 500')->fetchAll();
        out(['bookings' => $r]);

    case 'booking_status':
        $d = body();
        if (!in_array($d['status'] ?? '', BSTAT, true)) fail('Status tidak valid.');
        $s = db()->prepare('UPDATE bookings SET status = ? WHERE id = ?');
        $s->execute([$d['status'], (int)($d['id'] ?? 0)]);
        out(['ok' => true]);

    case 'menu':
        out(['menu' => db()->query('SELECT id, name, price, category, description, image_url, is_active FROM menu ORDER BY name')->fetchAll()]);

    case 'menu_save':
        $d = body();
        $name = trim((string)($d['name'] ?? ''));
        $price = (int)($d['price'] ?? 0);
        $cat = (string)($d['category'] ?? '');
        $desc = trim((string)($d['description'] ?? ''));
        $img = trim((string)($d['image_url'] ?? ''));
        $active = !empty($d['active']) ? 1 : 0;
        if ($name === '' || mb_strlen($name) > 100 || $price < 1) fail('Nama dan harga menu belum benar.');
        if (!in_array($cat, ['Makanan', 'Minuman', 'Cemilan'], true)) fail('Kategori tidak valid.');
        if (mb_strlen($desc) > 200) fail('Deskripsi maksimal 200 karakter.');
        if ($img !== '' && (strlen($img) > 255 || !preg_match('#^(https?://|img/)[^"\'<>\r\n]+$#', $img))) fail('Foto harus berupa alamat https://... atau path img/nama.jpg.');
        if (!empty($d['id'])) {
            db()->prepare('UPDATE menu SET name = ?, price = ?, category = ?, description = ?, image_url = ?, is_active = ? WHERE id = ?')
                ->execute([$name, $price, $cat, $desc, $img, $active, (int)$d['id']]);
        } else {
            db()->prepare('INSERT INTO menu (name, price, category, description, image_url, is_active) VALUES (?, ?, ?, ?, ?, ?)')
                ->execute([$name, $price, $cat, $desc, $img, $active]);
        }
        out(['ok' => true]);

    case 'menu_delete':
        $d = body();
        db()->prepare('DELETE FROM menu WHERE id = ?')->execute([(int)($d['id'] ?? 0)]);
        out(['ok' => true]);

    case 'games':
        out(['games' => db()->query('SELECT id, title, genre, min_players, max_players, duration, difficulty, description, is_available, is_active
            FROM games ORDER BY title')->fetchAll()]);

    case 'game_save':
        $d = body();
        $title = trim((string)($d['title'] ?? ''));
        $genre = trim((string)($d['genre'] ?? ''));
        $desc = trim((string)($d['description'] ?? ''));
        $min = (int)($d['min_players'] ?? 0);
        $max = (int)($d['max_players'] ?? 0);
        $dur = (int)($d['duration'] ?? 0);
        $diff = (string)($d['difficulty'] ?? '');
        $avail = !empty($d['available']) ? 1 : 0;
        $active = !empty($d['active']) ? 1 : 0;
        if ($title === '' || mb_strlen($title) > 100) fail('Judul game belum benar.');
        if ($genre === '' || mb_strlen($genre) > 30) fail('Genre belum benar (maks. 30 karakter).');
        if ($min < 1 || $max < $min || $max > 20) fail('Jumlah pemain belum benar.');
        if ($dur < 5 || $dur > 480) fail('Durasi harus 5-480 menit.');
        if (!in_array($diff, ['mudah', 'sedang', 'sulit'], true)) fail('Tingkat kesulitan tidak valid.');
        if (mb_strlen($desc) > 300) fail('Deskripsi maksimal 300 karakter.');
        if (!empty($d['id'])) {
            db()->prepare('UPDATE games SET title = ?, genre = ?, min_players = ?, max_players = ?, duration = ?, difficulty = ?, description = ?, is_available = ?, is_active = ? WHERE id = ?')
                ->execute([$title, $genre, $min, $max, $dur, $diff, $desc, $avail, $active, (int)$d['id']]);
        } else {
            db()->prepare('INSERT INTO games (title, genre, min_players, max_players, duration, difficulty, description, is_available, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)')
                ->execute([$title, $genre, $min, $max, $dur, $diff, $desc, $avail, $active]);
        }
        out(['ok' => true]);

    case 'game_delete':
        $d = body();
        db()->prepare('DELETE FROM games WHERE id = ?')->execute([(int)($d['id'] ?? 0)]);
        out(['ok' => true]);

    case 'pages':
        out(['pages' => db()->query('SELECT p.id, p.type, p.title, p.description, p.content, p.image_url, p.is_active, p.created_at, p.updated_at, u.name AS author_name
            FROM pages p LEFT JOIN users u ON u.id = p.author_id ORDER BY p.created_at DESC, p.id DESC')->fetchAll()]);

    case 'page_save':
        $d = body();
        $type = (string)($d['type'] ?? '');
        $title = trim((string)($d['title'] ?? ''));
        $desc = trim((string)($d['description'] ?? ''));
        $content = trim((string)($d['content'] ?? ''));
        $img = trim((string)($d['image_url'] ?? ''));
        $active = !empty($d['active']) ? 1 : 0;
        if (!in_array($type, ['content', 'article'], true)) fail('Jenis halaman tidak valid.');
        if ($title === '' || mb_strlen($title) > 150) fail('Judul halaman belum benar (maks. 150 karakter).');
        if (mb_strlen($desc) > 255) fail('Deskripsi maksimal 255 karakter.');
        if ($content === '' || mb_strlen($content) > 20000) fail('Isi halaman wajib diisi (maks. 20.000 karakter).');
        if ($img !== '' && (strlen($img) > 255 || !preg_match('#^(https?://|img/)[^"\'<>\r\n]+$#', $img))) fail('Gambar harus berupa alamat https://... atau path img/nama.jpg.');
        if (!empty($d['id'])) {
            db()->prepare('UPDATE pages SET type = ?, title = ?, description = ?, content = ?, image_url = ?, is_active = ? WHERE id = ?')
                ->execute([$type, $title, $desc, $content, $img, $active, (int)$d['id']]);
        } else {
            db()->prepare('INSERT INTO pages (type, title, description, content, image_url, author_id, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)')
                ->execute([$type, $title, $desc, $content, $img, (int)$admin['id'], $active]);
        }
        out(['ok' => true]);

    case 'page_delete':
        $d = body();
        db()->prepare('DELETE FROM pages WHERE id = ?')->execute([(int)($d['id'] ?? 0)]);
        out(['ok' => true]); 
        
    case 'users':
        $r = db()->query('SELECT u.id, u.name, u.email, u.phone, u.role,
                (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) AS orders,
                (SELECT COUNT(*) FROM bookings b WHERE b.user_id = u.id) AS bookings
            FROM users u ORDER BY u.created_at DESC, u.id DESC')->fetchAll();
        out(['users' => $r]);

    case 'user_delete':
        $d = body();
        $id = (int)($d['id'] ?? 0);
        if ($id === (int)$admin['id']) fail('Tidak bisa menghapus akun sendiri.');
        $s = db()->prepare("DELETE FROM users WHERE id = ? AND role = 'user'");
        $s->execute([$id]);
        if (!$s->rowCount()) fail('Pengguna tidak ditemukan atau bukan akun pelanggan.', 404);
        out(['ok' => true]);

    case 'logins':
        $r = db()->query('SELECT h.id, h.email, h.status, h.ip_address, h.user_agent, h.created_at, u.name AS user_name
            FROM login_history h LEFT JOIN users u ON u.id = h.user_id
            ORDER BY h.created_at DESC, h.id DESC LIMIT 300')->fetchAll();
        out(['logins' => $r]);

    // --- CATEGORIES ---
    case 'categories':
        out(['categories' => db()->query('SELECT id, name, slug FROM categories ORDER BY name')->fetchAll()]);

    case 'category_save':
        $d = body();
        $name = trim((string)($d['name'] ?? ''));
        $slug = strtolower(trim((string)($d['slug'] ?? '')));
        if ($name === '') fail('Nama kategori wajib diisi.');
        if ($slug === '') $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($name));
        if (!empty($d['id'])) {
            db()->prepare('UPDATE categories SET name = ?, slug = ? WHERE id = ?')->execute([$name, $slug, (int)$d['id']]);
        } else {
            db()->prepare('INSERT INTO categories (name, slug) VALUES (?, ?)')->execute([$name, $slug]);
        }
        out(['ok' => true]);

    case 'category_delete':
        $d = body();
        db()->prepare('DELETE FROM categories WHERE id = ?')->execute([(int)($d['id'] ?? 0)]);
        out(['ok' => true]);

    // --- HERO SECTIONS ---
    case 'hero_sections':
        out(['hero_sections' => db()->query('SELECT id, title, subtitle, image_url, button_text, button_url, is_active FROM hero_sections ORDER BY id DESC')->fetchAll()]);

    case 'hero_save':
        $d = body();
        $title = trim((string)($d['title'] ?? ''));
        $subtitle = trim((string)($d['subtitle'] ?? ''));
        $img = trim((string)($d['image_url'] ?? ''));
        $btnText = trim((string)($d['button_text'] ?? 'Jelajahi'));
        $btnUrl = trim((string)($d['button_url'] ?? '#'));
        $active = !empty($d['active']) ? 1 : 0;
        if ($title === '') fail('Judul hero wajib diisi.');
        if (!empty($d['id'])) {
            db()->prepare('UPDATE hero_sections SET title = ?, subtitle = ?, image_url = ?, button_text = ?, button_url = ?, is_active = ? WHERE id = ?')
                ->execute([$title, $subtitle, $img, $btnText, $btnUrl, $active, (int)$d['id']]);
        } else {
            db()->prepare('INSERT INTO hero_sections (title, subtitle, image_url, button_text, button_url, is_active) VALUES (?, ?, ?, ?, ?, ?)')
                ->execute([$title, $subtitle, $img, $btnText, $btnUrl, $active]);
        }
        out(['ok' => true]);

    case 'hero_delete':
        $d = body();
        db()->prepare('DELETE FROM hero_sections WHERE id = ?')->execute([(int)($d['id'] ?? 0)]);
        out(['ok' => true]);

    // --- WEB SETTINGS ---
    case 'web_settings':
        $r = db()->query('SELECT setting_key, setting_value FROM web_settings')->fetchAll(PDO::FETCH_KEY_PAIR);
        out(['settings' => $r ? $r : []]);

    case 'web_settings_save':
        $d = body();
        $settings = $d['settings'] ?? [];
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('INSERT INTO web_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?');
            foreach ($settings as $key => $val) {
                $stmt->execute([$key, trim((string)$val), trim((string)$val)]);
            }
            $pdo->commit();
            out(['ok' => true]);
        } catch (Throwable $e) {
            $pdo->rollBack();
            fail('Gagal menyimpan pengaturan.', 500);
        }

    // --- NETWORKINGS ---
    case 'networkings':
        out(['networkings' => db()->query('SELECT id, name, url, icon, is_active FROM networkings ORDER BY name')->fetchAll()]);

    case 'networking_save':
        $d = body();
        $name = trim((string)($d['name'] ?? ''));
        $url = trim((string)($d['url'] ?? ''));
        $icon = trim((string)($d['icon'] ?? ''));
        $active = !empty($d['active']) ? 1 : 0;
        if ($name === '' || $url === '') fail('Nama dan URL networking wajib diisi.');
        if (!empty($d['id'])) {
            db()->prepare('UPDATE networkings SET name = ?, url = ?, icon = ?, is_active = ? WHERE id = ?')
                ->execute([$name, $url, $icon, $active, (int)$d['id']]);
        } else {
            db()->prepare('INSERT INTO networkings (name, url, icon, is_active) VALUES (?, ?, ?, ?)')
                ->execute([$name, $url, $icon, $active]);
        }
        out(['ok' => true]);

    case 'networking_delete':
        $d = body();
        db()->prepare('DELETE FROM networkings WHERE id = ?')->execute([(int)($d['id'] ?? 0)]);
        out(['ok' => true]);

    default:
        fail('Aksi tidak dikenal.', 404);
}