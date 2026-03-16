# Product Requirement Document (PRD)

## Website Business Card – Faris AY | Faydev

---

# 1. Overview Produk

## 1.1 Nama Produk

**Faydev Personal Business Card Website**

## 1.2 Deskripsi Produk

Website ini merupakan **personal business card berbasis web** yang berfungsi sebagai pusat personal branding dan kanal utama untuk menarik calon client bisnis dan UMKM.

Website ini menampilkan informasi utama seperti:

* Profil singkat
* Skill dan layanan
* Galeri project
* Tautan media sosial
* Call-to-action untuk konsultasi melalui WhatsApp

Website dirancang **ringkas, cepat diakses, mobile-first**, serta dapat digunakan sebagai **landing page dari media sosial seperti Instagram, LinkedIn, atau platform lainnya**.

---

# 2. Tujuan Produk

Tujuan utama website:

1. Membangun **personal branding profesional**
2. Menjadi **landing page utama untuk calon client**
3. Menampilkan **portfolio project**
4. Mengarahkan pengunjung untuk melakukan **konsultasi melalui WhatsApp**

---

# 3. Target Pengguna

Target utama pengguna website:

### 3.1 Client Bisnis

Contoh:

* Pemilik UMKM
* Agency kecil
* Konsultan
* Startup kecil

Kebutuhan mereka:

* Membuat website
* Membuat landing page
* Automasi bisnis sederhana
* Chatbot

### 3.2 Pengunjung Umum

Contoh:

* Recruiter
* Developer lain
* Partner bisnis

---

# 4. Value Proposition

Website ini memberikan nilai berikut:

* Profil profesional yang **ringkas dan jelas**
* **Portfolio project nyata**
* Kemudahan untuk **menghubungi developer**
* Tampilan **modern dan mobile friendly**

---

# 5. Tujuan Bisnis

Website diharapkan dapat:

1. Meningkatkan **kepercayaan client**
2. Menjadi **alat pemasaran digital**
3. Menjadi **hub utama semua link profesional**

---

# 6. Identitas Brand

## 6.1 Nama

**Faris AY | Faydev**

## 6.2 Tagline

**Software & Web Developer untuk UMKM**

## 6.3 Profesi

* Software Engineer
* Fullstack Developer

## 6.4 Layanan Utama

1. Web Development
2. Landing Page Development
3. Automation System
4. Chatbot Development

---

# 7. Struktur Halaman Website

Website menggunakan **landing page structure (single page)** dengan beberapa section.

Struktur halaman:

1. Hero Section
2. About Me
3. Skills
4. Services
5. Project Gallery
6. Contact (WhatsApp CTA)
7. Social Media Links
8. Footer

---

# 8. Detail Fitur Produk

## 8.1 Hero Section

Fungsi:

* Memberikan impresi pertama
* Menjelaskan identitas developer

Komponen:

* Foto profile
* Nama
* Tagline
* CTA Button:

  * **Lihat Project**
  * **Konsultasi WhatsApp**

Sumber data:

Hardcoded di HTML.

---

## 8.2 About Me

Berisi deskripsi singkat tentang developer.

Contoh konten:

* Background singkat
* Fokus pekerjaan
* Target client

Sumber data:

Hardcoded di HTML.

---

## 8.3 Skills

Menampilkan kemampuan teknis developer.

Contoh kategori:

* Programming
* Backend
* Frontend
* Tools

Contoh skill:

* PHP
* JavaScript
* MySQL
* HTML
* CSS
* API Integration
* Automation

Sumber data:

Hardcoded.

---

## 8.4 Services

Menampilkan layanan yang ditawarkan.

Contoh layanan:

### Web Development

Pembuatan website custom untuk bisnis.

### Landing Page

Halaman penjualan atau promosi.

### Automation

Sistem otomatisasi untuk bisnis.

### Chatbot

Integrasi chatbot untuk WhatsApp atau website.

Sumber data:

Hardcoded.

---

## 8.5 Project Gallery

Fitur ini menampilkan **project terbaru** yang telah dibuat.

Tujuan:

* Menunjukkan portfolio
* Meningkatkan kredibilitas

### Komponen

* Thumbnail project
* Judul project
* Tanggal project
* Link demo

### Behavior

Menampilkan **latest project** dari database.

Setiap project memiliki:

* tombol **View Demo**

### Tombol tambahan

Di bagian bawah terdapat tombol:

**"View All Projects"**

Tombol ini akan mengarah ke:

URL external aplikasi gallery project.

---

## 8.6 Detail Project Page

Setiap project memiliki halaman detail.

URL contoh:

```
/project/{slug}
```

Konten halaman:

* Thumbnail
* Judul project
* Tanggal project
* Link demo

---

## 8.7 Contact Section

Fokus utama:

**Konsultasi melalui WhatsApp**

Tombol CTA:

```
Konsultasi via WhatsApp
```

Link format:

```
https://wa.me/{nomor}
```

Nomor WhatsApp disimpan sebagai:

Hardcoded.

---

## 8.8 Social Media Links

Menampilkan ikon sosial media.

Contoh:

* Instagram
* LinkedIn
* GitHub
* Threads
* TikTok

Data sosial media diambil dari:

**Database**

Tujuan:

* Mudah diubah tanpa mengubah kode.

---

## 8.9 Dark / Light Mode

Website mendukung:

* **Light mode**
* **Dark mode**

Fitur:

* Toggle switch
* Preferensi tersimpan di **localStorage**

---

# 9. Teknologi yang Digunakan

## 9.1 Frontend

* HTML
* CSS
* JavaScript

Font:

**Poppins**

Design approach:

**Mobile-first**

---

## 9.2 Backend

Backend menggunakan:

**PHP**

Fungsi backend:

* Mengambil data project
* Mengambil data social media

---

## 9.3 Database

Database:

**MySQL**

Digunakan untuk:

* Project gallery
* Social media links

---

## 9.4 Hosting

Target deployment:

**Shared Hosting**

Karena:

* kompatibel dengan PHP
* biaya rendah
* mudah deploy

---

# 10. Database Design

## 10.1 Tabel Projects

Nama tabel:

```
projects
```

Struktur tabel:

| Field        | Type      | Keterangan      |
| ------------ | --------- | --------------- |
| id           | INT       | Primary Key     |
| title        | VARCHAR   | Judul project   |
| thumbnail    | VARCHAR   | Path gambar     |
| demo_link    | VARCHAR   | Link demo       |
| project_date | DATE      | Tanggal project |
| created_at   | TIMESTAMP | Waktu input     |

---

## 10.2 Tabel Social Media

Nama tabel:

```
social_links
```

Struktur tabel:

| Field      | Type      | Keterangan    |
| ---------- | --------- | ------------- |
| id         | INT       | Primary Key   |
| name       | VARCHAR   | Nama platform |
| icon       | VARCHAR   | Icon class    |
| url        | VARCHAR   | Link profile  |
| created_at | TIMESTAMP | Timestamp     |

---

# 11. Alur Pengguna (User Flow)

### Scenario 1 — Pengunjung Baru

1. Pengunjung membuka website
2. Melihat hero section
3. Scroll melihat services
4. Scroll melihat project
5. Klik **Project Gallery**
6. Melihat portfolio

---

### Scenario 2 — Client Ingin Konsultasi

1. Pengunjung membuka website
2. Melihat layanan
3. Tertarik
4. Klik **Konsultasi WhatsApp**
5. Terbuka chat WhatsApp

---

# 12. Non Functional Requirement

Website harus:

### Performance

* Load < 2 detik
* Optimized images

### Responsiveness

* Mobile first
* Responsive tablet dan desktop

### SEO Basic

* Meta title
* Meta description
* Open Graph

---

# 13. Future Development

Fitur yang direncanakan untuk masa depan:

### Admin Panel

Fungsi:

* Menambah project
* Mengedit project
* Menghapus project
* Mengedit social media link

Kemungkinan stack:

* PHP
* Session login sederhana

---

# 14. Design Guideline

### Style

Minimalist + Modern

### Warna utama

Hijau

Contoh:

```
Primary: #22C55E
```

### Font

Poppins

### Layout

Landing page dengan scroll vertical.

---

# 15. Deliverables

Produk akhir yang dihasilkan:

1. Website landing page
2. Sistem project gallery
3. Dark/light mode
4. Integrasi WhatsApp
5. Database MySQL
6. Struktur backend PHP

---

# 16. Estimasi Kompleksitas

Tingkat kompleksitas:

**Low – Medium**

Alasan:

* Tidak banyak fitur interaktif
* Tidak ada sistem login
* Database sederhana