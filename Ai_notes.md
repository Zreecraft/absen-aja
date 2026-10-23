# AI Notes

## Bagian yang saya kerjakan bersama AI

Saya memakai dua jenis AI dengan peran berbeda:

- **Claude (chat)** sebagai rekan berpikir: membahas soal, merancang skema database dan aturan bisnis, menjelaskan pilihan arsitektur, membantu membaca pesan error, dan menyusun prompt untuk agent coding.
- **Agent coding (Antigravity)** untuk menulis kode berdasarkan prompt yang saya tentukan lingkupnya per tahap.

Rinciannya:

- Perancangan skema, termasuk constraint di database (CHECK status, foreign key gabungan agar mahasiswa tak terdaftar mustahil dicatat) dan aturan hapus, dibahas dengan Claude, lalu saya putuskan.
- Migration, model, controller, FormRequest, dan route CRUD ditulis agent berdasarkan prompt per tahap.
- Pertemuan, kehadiran (upsert), dan rekap (satu query `COUNT(*) FILTER`) ditulis agent. Aturan hitungnya saya tentukan sendiri.
- Seeder dan feature test ditulis agent.
- Frontend Vue dengan Tailwind ditulis agent berdasarkan arah desain yang saya tentukan.
- Troubleshooting environment (driver PostgreSQL, port, error build Vite) dikerjakan dengan Claude.
- Draf README.md, CLAUDE.md, dan catatan ini disusun dengan bantuan Claude lalu saya sesuaikan.

**[REVIEW]** Sesuaikan daftar ini dengan apa yang benar-benar dikerjakan setiap agent dan bagian mana yang kamu ubah sendiri.

## Satu contoh saran AI yang saya tolak atau perbaiki, dan alasannya

**Tampilan "sederhana" yang terlalu polos.** Rencana awal dari AI adalah antarmuka dengan CSS tulisan tangan tanpa Tailwind, tanpa ikon, dan tanpa komponen interaktif, dengan alasan supaya mudah dijelaskan dan "tidak terlihat seperti template AI". Saya tolak karena soal hanya mensyaratkan tampilan yang dapat dipakai, sedangkan aplikasi absensi seharusnya nyaman dipakai dosen: ada notifikasi, dialog konfirmasi, dan status berwarna yang mudah dipindai. Saya minta Tailwind dengan arah desain yang jelas (sidebar, warna status yang konsisten dan selalu disertai teks, komponen modal dan toast buatan sendiri). Komponen tetap dibuat sendiri tanpa library UI jadi supaya masih bisa saya jelaskan.

Contoh lain yang saya perbaiki: agent pertama langsung menulis seluruh proyek dalam satu kali jalan. Saya hentikan dan pecah per tahap (migration, API, seeder dan test, frontend) supaya setiap bagian bisa saya baca dan pahami sebelum lanjut.

**[REVIEW]** Pilih satu contoh yang paling kamu ingat atau yang terjadi lagi saat mengerjakan, dan ceritakan dengan bahasamu: apa saran AI, kenapa kamu tidak setuju, apa yang kamu lakukan.

## Bagian yang saya kerjakan tanpa AI, dan alasannya

- Keputusan aturan bisnis: izin dan sakit tidak dihitung hadir, penyebut persentase hanya pertemuan yang sudah dicatat, serta aturan hapus dan keluarkan mahasiswa. Alasannya keputusan ini menyangkut kebijakan kampus, bukan teknis, dan saya yang harus bisa mempertanggungjawabkannya.
- Memperbaiki kesalahan konfigurasi `.env` (port MySQL 3306 terbawa padahal PostgreSQL memakai 5432, dan salah ketik nama user).
- Membaca kode hasil agent dan menguji alur lengkap secara manual di browser (buat kelas, buat mahasiswa, daftarkan, buat pertemuan, input kehadiran, lihat rekap) serta kasus penolakan.
- **[REVIEW]** Tambahkan bagian lain yang benar-benar kamu tulis atau perbaiki sendiri (misalnya mengubah teks, memperbaiki bug kecil, menyesuaikan seeder). Jangan menulis yang tidak kamu kerjakan.

## Kalau mengulang dari awal, apa yang akan saya ubah

- Menulis prompt dengan batas lingkup dan nama file yang eksplisit sejak awal. Agent memilih nama file test sendiri karena tidak saya tentukan, dan satu prompt terlalu besar membuat agent kena batas kuota di tengah jalan.
- Menyiapkan environment (PostgreSQL, ekstensi PHP, `.env`) lebih dulu dan memeriksanya sebelum menulis kode, karena beberapa error awal murni soal konfigurasi.
- Memutuskan arah tampilan (Tailwind dan komponen) di awal, bukan setelah ada versi polos yang harus diganti.
- Menulis test lebih awal bersama fitur, bukan sebagai tahap terpisah di akhir.
- **[REVIEW]** Tambahkan pelajaran yang benar-benar kamu rasakan.