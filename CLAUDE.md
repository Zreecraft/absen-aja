# Project: Absen Aja

## Deskripsi singkat
Aplikasi web fullstack untuk mencatat dan merekap kehadiran mahasiswa per kelas. Fitur: kelola kelas dan mahasiswa, daftarkan mahasiswa ke kelas, buat pertemuan, input kehadiran satu layar per pertemuan, rekap per kelas. Di luar cakupan: login dan hak akses.

## Teknologi yang dipakai dan cara menjalankan
- Backend: Laravel 13 (PHP 8.3+) sebagai REST API di `/api`.
- Frontend: Vue 3 (JavaScript, bukan TypeScript) + Vue Router + Vite, Composition API dengan `<script setup>`. Tailwind CSS 4, ikon lucide-vue-next, font Inter dari paket npm. Satu SPA disajikan lewat route catch-all Laravel.
- Database: PostgreSQL (database `absen_aja`; test memakai `absen_aja_test`).
- Jalankan: `composer install`, `npm install`, salin `.env.example` ke `.env`, `php artisan key:generate`, `php artisan migrate --seed`, lalu `composer dev` (http://localhost:8000).
- Test: `php artisan test`. Build: `npm run build`.

## Aturan bisnis penting
- Hanya mahasiswa yang terdaftar di kelas yang boleh dicatat kehadirannya (validasi di backend dan foreign key gabungan di DB).
- Status hanya: `hadir`, `izin`, `sakit`, `alpa` (enum `StatusKehadiran` dan CHECK di DB).
- Persentase = hadir / pertemuan yang sudah dicatat x 100. Izin dan sakit TIDAK dihitung hadir. Pertemuan yang belum diisi tidak masuk penyebut. Dihitung dalam satu query PostgreSQL di `App\Services\RekapKehadiran`.
- Mahasiswa yang sudah punya kehadiran di sebuah kelas tidak boleh dikeluarkan dari kelas itu; mahasiswa yang masih terdaftar di kelas tidak boleh dihapus (HTTP 422, pesan Bahasa Indonesia).
- Hapus kelas menghapus pertemuan, pendaftaran, dan kehadirannya (cascade).
- PUT kehadiran adalah upsert dalam satu transaksi, idempotent, semua-atau-tidak-sama-sekali.

## Aturan penulisan kode
- Sederhana dan mudah dijelaskan baris per baris oleh pemilik project. Tanpa repository pattern dan abstraksi berlebihan.
- Controller tipis; validasi di FormRequest; response lewat API Resource; logika rekap hanya di `RekapKehadiran`.
- Tidak boleh ada N+1 query. Query mentah wajib memakai binding parameter.
- Pesan validasi, error, dan teks antarmuka dalam Bahasa Indonesia.
- Frontend: satu komponen satu tugas; panggilan API hanya lewat `resources/js/api.js`; warna dan label status hanya di `resources/js/status.js`.
- Komentar singkat hanya untuk bagian yang tidak obvious (foreign key gabungan, query rekap, upsert).
- Commit bertahap, format conventional commits (`feat:`, `fix:`, `test:`, `chore:`, `docs:`).

## Hal yang tidak boleh dilakukan AI
- Jangan memakai Livewire, Inertia, MySQL, atau SQLite.
- Jangan menambah package di luar yang sudah ada tanpa persetujuan pemilik.
- Jangan menambah fitur di luar kebutuhan (login, export, dan sejenisnya).
- Jangan mengubah migration yang sudah ada secara diam-diam; laporkan dulu bila menemukan bug.
- Jangan menjalankan `migrate:fresh` pada database `absen_aja` tanpa diminta, dan jangan menghapus data selain data uji.
- Jangan commit `.env`, password, atau kredensial apa pun.
- Jangan memakai `git add .` saat ada agent lain bekerja di repo yang sama; commit dengan path eksplisit.
- Jangan menulis kode yang tidak bisa dijelaskan pemilik; bila ragu, pilih solusi yang lebih sederhana.