-- ============================================================
-- Tabel halaman (content / article) untuk Loka Boardgame Cafe
-- Cara: phpMyAdmin > pilih database loka_cafe > tab Import > pilih file ini > Go
-- Aman diulang: tidak menghapus data yang sudah ada.
-- ============================================================
USE loka_cafe;

CREATE TABLE IF NOT EXISTS pages (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  type        VARCHAR(10)  NOT NULL DEFAULT 'content',   -- 'content' atau 'article'
  title       VARCHAR(150) NOT NULL,                     -- Title_pages
  description VARCHAR(255) NOT NULL DEFAULT '',          -- Desc
  content     MEDIUMTEXT   NOT NULL,                     -- Content
  image_url   VARCHAR(255) NOT NULL DEFAULT '',          -- Images (path img/nama.jpg atau https://...)
  author_id   INT NULL,                                  -- Authors: id admin yang menulis (dirujuk ke users.id)
  is_active   TINYINT(1)   NOT NULL DEFAULT 1,           -- 1 tampil, 0 disembunyikan
  created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,                                  -- Created_at
  updated_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,      -- Updated_at
  KEY idx_pages_type (type, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dua contoh halaman (hanya dibuat kalau judulnya belum ada). Boleh dihapus lewat dashboard admin.
INSERT INTO pages (type, title, description, content)
SELECT 'content', 'Tentang Loka', 'Cerita singkat tentang Loka Boardgame Cafe.',
       'Loka Boardgame Cafe adalah tempat untuk makan, minum, dan bermain boardgame bersama.\n\nPelanggan bisa melihat katalog boardgame dan menu, memesan makanan dan minuman, serta booking meja dari satu akun.'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pages WHERE title = 'Tentang Loka');

INSERT INTO pages (type, title, description, content)
SELECT 'article', 'Tips Memilih Boardgame untuk Pemula', 'Beberapa hal yang bisa dipertimbangkan sebelum memilih game.',
       'Kalau baru pertama kali bermain, mulai dari game yang aturannya singkat dan durasinya pendek.\n\nPerhatikan jumlah pemain yang cocok dengan rombonganmu, lalu lihat tingkat kesulitannya. Di katalog Loka, setiap game menampilkan jumlah pemain, durasi, dan tingkat kesulitan.\n\nKalau ragu, tanyakan ke staf. Mereka bisa menjelaskan aturan dasarnya sebelum kalian mulai.'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pages WHERE title = 'Tips Memilih Boardgame untuk Pemula');
