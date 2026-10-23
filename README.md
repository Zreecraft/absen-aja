# Absen Aja

Aplikasi web untuk mencatat dan merekap kehadiran mahasiswa per kelas. Dibuat untuk menggantikan pencatatan kehadiran di kertas yang sering salah rekap dan lambat di akhir semester.

## Fitur

- Kelola data kelas (mata kuliah, nama kelas, dosen) dan mahasiswa (NIM, nama): tambah, lihat, ubah, hapus.
- Daftarkan dan keluarkan mahasiswa dari kelas.
- Buat pertemuan untuk sebuah kelas (pertemuan ke-berapa dan tanggal).
- Input kehadiran seluruh mahasiswa satu kelas dalam satu layar (hadir, izin, sakit, alpa).
- Rekap kehadiran per kelas: jumlah tiap status dan persentase kehadiran setiap mahasiswa.

## Teknologi

| Bagian | Pilihan |
|---|---|
| Backend | Laravel 13 (PHP 8.3+), REST API |
| Frontend | Vue 3 (JavaScript), Vue Router, Vite |
| Tampilan | Tailwind CSS 4, ikon lucide-vue-next, font Inter |
| Database | PostgreSQL |

Backend dan frontend berada di satu repo. Laravel menyajikan API di `/api` dan satu halaman SPA untuk semua alamat lainnya. Komponen antarmuka (modal, toast, tab) dibuat sendiri tanpa library komponen jadi.

## Prasyarat

- PHP 8.3 atau lebih baru, dengan ekstensi `pdo_pgsql` dan `pgsql` aktif
- Composer
- Node.js 20 atau lebih baru
- PostgreSQL (port 5432)

## Instalasi dan menjalankan

```bash
# 1. Pasang dependensi
composer install
npm install

# 2. Siapkan environment
cp .env.example .env
php artisan key:generate

# 3. Buat database di PostgreSQL
psql -U postgres -c "CREATE DATABASE absen_aja;"

# 4. Atur koneksi database di .env
#    DB_CONNECTION=pgsql
#    DB_HOST=127.0.0.1
#    DB_PORT=5432
#    DB_DATABASE=absen_aja
#    DB_USERNAME=postgres
#    DB_PASSWORD=<password kamu>

# 5. Buat tabel dan isi data contoh
php artisan migrate --seed

# 6. Jalankan aplikasi (server Laravel + Vite)
composer dev
```

Buka `http://localhost:8000`.

Catatan Windows (Laragon): jika `psql` tidak dikenali, jalankan dengan path lengkap, mis. `& "C:\Program Files\PostgreSQL\18\bin\psql.exe" -U postgres -c "CREATE DATABASE absen_aja;"`.

Untuk build produksi: `npm run build`.

Data contoh berisi 3 kelas, sekitar 15 mahasiswa, beberapa pertemuan per kelas, dan kehadiran yang bervariasi, sehingga rekap langsung terlihat berisi. Ada satu pertemuan yang sengaja belum diisi untuk memperlihatkan aturan penyebut persentase. Untuk mengulang dari awal: `php artisan migrate:fresh --seed`.

## Halaman

| Alamat | Fungsi |
|---|---|
| `/kelas` | Daftar, tambah, ubah, hapus kelas |
| `/kelas/:id` | Detail kelas dengan tab Pertemuan, Mahasiswa, dan Rekap |
| `/kelas/:id/pertemuan/:pid` | Input kehadiran seluruh mahasiswa untuk satu pertemuan |
| `/mahasiswa` | Daftar, tambah, ubah, hapus mahasiswa |

## Skema database

| Tabel | Kolom utama | Constraint penting |
|---|---|---|
| `kelas` | mata_kuliah, nama_kelas, dosen | |
| `mahasiswa` | nim, nama | `nim` unik |
| `kelas_mahasiswa` | kelas_id, mahasiswa_id | primary key gabungan |
| `pertemuan` | kelas_id, nomor, tanggal | unik (kelas_id, nomor); nomor > 0 |
| `kehadiran` | pertemuan_id, kelas_id, mahasiswa_id, status | unik (pertemuan_id, mahasiswa_id); CHECK status; dua foreign key gabungan |

Aturan bisnis dijaga sampai ke level database, tidak hanya di kode:

- Kolom `status` dibatasi CHECK ke empat nilai: `hadir`, `izin`, `sakit`, `alpa`.
- Foreign key gabungan `(kelas_id, mahasiswa_id)` ke `kelas_mahasiswa` membuat mahasiswa yang tidak terdaftar di sebuah kelas mustahil dicatat kehadirannya, bahkan bila ada bug di backend.
- Foreign key gabungan `(pertemuan_id, kelas_id)` ke `pertemuan` menjamin pertemuan dan kelas yang dicatat selalu cocok. Kolom `kelas_id` di `kehadiran` sengaja redundan demi dua constraint ini.

## Endpoint API

| Method | Path | Keterangan |
|---|---|---|
| GET, POST | `/api/kelas` | Daftar dan tambah kelas |
| GET, PUT, DELETE | `/api/kelas/{kelas}` | Lihat, ubah, hapus kelas |
| GET, POST | `/api/mahasiswa` | Daftar dan tambah mahasiswa |
| GET, PUT, DELETE | `/api/mahasiswa/{mahasiswa}` | Lihat, ubah, hapus mahasiswa |
| GET, POST | `/api/kelas/{kelas}/mahasiswa` | Daftar mahasiswa kelas dan mendaftarkan |
| DELETE | `/api/kelas/{kelas}/mahasiswa/{mahasiswa}` | Keluarkan mahasiswa dari kelas |
| GET, POST | `/api/kelas/{kelas}/pertemuan` | Daftar dan buat pertemuan |
| PUT, DELETE | `/api/pertemuan/{pertemuan}` | Ubah dan hapus pertemuan |
| GET, PUT | `/api/pertemuan/{pertemuan}/kehadiran` | Lihat dan simpan kehadiran satu pertemuan |
| GET | `/api/kelas/{kelas}/rekap` | Rekap kehadiran per kelas |

Kesalahan validasi dan pelanggaran aturan bisnis mengembalikan HTTP 422 dengan pesan Bahasa Indonesia. Daftar lengkap rute: `php artisan route:list --path=api`.

## Aturan perhitungan persentase

```
persentase = hadir / pertemuan yang sudah dicatat x 100
```

- **Hanya status `hadir` yang dihitung hadir.** Izin dan sakit tidak dihitung hadir, tetapi tetap ditampilkan sebagai kolom tersendiri agar dosen bisa melihat alasan ketidakhadiran. Alasannya: persentase kehadiran biasanya dipakai sebagai syarat mengikuti ujian, sehingga definisi yang ketat lebih aman. Aturan ini hanya berada di satu tempat (class `RekapKehadiran`) sehingga mudah diubah.
- **Penyebut adalah pertemuan yang sudah punya catatan kehadiran.** Pertemuan yang sudah dibuat tetapi belum diisi tidak menurunkan persentase.
- Jika belum ada pertemuan yang dicatat, persentase 0.
- Hasil dibulatkan satu angka desimal.
- Perhitungan dilakukan dalam satu query PostgreSQL (`COUNT(*) FILTER (WHERE status = ...)`), bukan di PHP atau Vue.
- Di tampilan rekap, persentase di bawah 75 diberi warna peringatan. Ambang ini hanya penanda visual dan tidak memengaruhi data.

## Asumsi

1. Tidak ada login atau hak akses (di luar cakupan soal).
2. Satu mahasiswa boleh terdaftar di lebih dari satu kelas.
3. Mahasiswa yang **sudah punya data kehadiran** di sebuah kelas tidak bisa dikeluarkan dari kelas itu, supaya data absen tidak hilang diam-diam. Jika belum ada kehadiran, boleh.
4. Mahasiswa yang **masih terdaftar di suatu kelas** tidak bisa dihapus. Keluarkan dulu dari kelas.
5. Menghapus kelas ikut menghapus pertemuan, pendaftaran, dan kehadirannya.
6. Nomor pertemuan terisi otomatis (nomor terakhir + 1) bila dikosongkan, dan unik per kelas.
7. Pertemuan yang belum pernah dicatat diberi default "hadir" di layar input; dosen cukup mengubah yang tidak hadir. Data baru tersimpan setelah tombol Simpan ditekan.
8. Menyimpan kehadiran bersifat upsert dalam satu transaksi: dikirim ulang tidak membuat data ganda, dan bila ada satu data tidak valid, tidak ada yang tersimpan.

## Menjalankan test

Test memakai database PostgreSQL terpisah agar data asli tidak tersentuh:

```bash
psql -U postgres -c "CREATE DATABASE absen_aja_test;"
php artisan test
```

Test mencakup perhitungan rekap, penolakan mahasiswa yang tidak terdaftar, penolakan status tidak valid, upsert yang idempotent, aturan hapus dan keluarkan, nomor pertemuan, cascade penghapusan kelas, serta constraint database.

## Struktur singkat

```
app/Enums/StatusKehadiran.php        Enum empat status
app/Models/                          Kelas, Mahasiswa, Pertemuan, Kehadiran
app/Http/Controllers/                Controller tipis per resource
app/Http/Requests/                   Validasi input
app/Http/Resources/                  Bentuk response JSON
app/Services/RekapKehadiran.php      Query rekap
database/migrations/                 Skema dan constraint
database/seeders/                    Data contoh
resources/js/                        Aplikasi Vue (halaman, komponen, router, helper API)
resources/css/app.css                Tailwind dan tema warna
routes/api.php                       Rute API
tests/Feature/                       Feature test
```

## Catatan pengerjaan dengan AI

Proyek ini dikerjakan dengan bantuan AI coding assistant. Catatannya ada di [AI_NOTES.md](AI_NOTES.md) dan konteks untuk AI ada di [CLAUDE.md](CLAUDE.md).