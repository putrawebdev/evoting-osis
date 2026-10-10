# 📚 Dokumentasi Backend E-Voting OSIS

Dokumentasi ini dibuat untuk memandu rekan frontend dalam mengintegrasikan antarmuka (UI) dengan backend Laravel pada proyek **E-Voting OSIS SMK Metland Cibitung**.

---

## 🛠️ 1. Cara Setup Proyek Setelah di-Clone (Untuk Teman Tim)

Jika kamu baru saja melakukan *clone* repository ini dari GitHub, ikuti langkah-langkah berikut di terminal/command prompt komputermu agar aplikasi bisa berjalan:

1. **Install Dependencies PHP (Composer):**
   ```bash
   composer install
   ```

2. **Duplikat File Konfigurasi Environment:**
Salin file `.env.example` dan ubah namanya menjadi `.env`, atau jalankan perintah:
```bash
cp .env.example .env

```


*(Sesuaikan pengaturan database di dalam file `.env` kamu, contoh: `DB_DATABASE=nama_db_pemilu`)*
3. **Generate App Key:**
```bash
php artisan key:generate

```


4. **Jalankan Migrasi Database & Seeder:**
```bash
php artisan migrate

```


5. **Hubungkan Storage untuk Upload Foto & File:**
```bash
php artisan storage:link

```


6. **Jalankan Server Lokal:**
```bash
php artisan serve

```


Akses aplikasi melalui `http://localhost:8000` di browser.

---

## 🔑 2. Alur Autentikasi (Authentication Flow)

Sistem ini memisahkan hak akses antara **Siswa (Voter)** dan **Admin**.

* **Login Siswa**:
* **Endpoint / URL**: `POST /login`
* **Input yang dikirim**: `nis` (Nomor Induk Siswa).
* **Keterangan**: Sistem memvalidasi NIS dari tabel `pemilih`. Jika siswa sudah memilih (`sudah_memilih == true`), sistem akan menolak login dan menampilkan pesan error.


* **Login Admin**:
* **Endpoint / URL**: `POST /admin/login`
* **Input yang dikirim**: `username` dan `password`.
* **Guard**: Menggunakan *auth guard* khusus admin.



---

## 🗳️ 3. Daftar Endpoint & Routing Backend

Berikut adalah daftar *URL Routes* yang tersedia di *backend* untuk dihubungkan ke *frontend*:

### A. Sisi Pemilih (Siswa)

| Method | Endpoint / URL | Keterangan / Deskripsi |
| --- | --- | --- |
| `GET` | `/pilih` | Menampilkan halaman bilik suara utama (berisi daftar kartu kandidat). |
| `GET` | `/pilih/kandidat/{id}` | Menampilkan halaman detail Visi, Misi, dan Program Unggulan paslon tertentu. |
| `POST` | `/pilih/submit` | Mengirim pilihan suara siswa. **Wajib mengirim payload:** `kandidat_id`. Setelah sukses, sesi siswa langsung di-logout otomatis. |

### B. Sisi Administrator

| Method | Endpoint / URL | Keterangan / Deskripsi |
| --- | --- | --- |
| `GET` | `/admin/dashboard` | Mengambil data statistik (Total Pemilih, Suara Masuk, dan array data Chart.js). |
| `GET` | `/admin/pemilih` | Menampilkan halaman manajemen data siswa/pemilih (DPT) dengan pagination. |
| `POST` | `/admin/pemilih` | Menambah data pemilih baru (`nis`, `nama`, `kelas`). |
| `GET` | `/admin/pemilih/{id}/edit` | Mengambil data pemilih spesifik untuk form edit. |
| `PUT` | `/admin/pemilih/{id}` | Memperbarui data pemilih. |
| `DELETE` | `/admin/pemilih/{id}` | Menghapus data pemilih. |
| `POST` | `/admin/pemilih/import` | Mengimpor data pemilih secara massal lewat file CSV/Excel (`file_csv`). |
| `GET` | `/admin/kandidat` | Menampilkan halaman manajemen kandidat paslon. |
| `POST` | `/admin/kandidat` | Menambah kandidat baru (Wajib mengunggah file gambar `foto`, `no_paslon`, `nama_ketua`, `nama_wakil`, `visi`, `misi`, `proggram`). |
| `GET` | `/admin/kandidat/{id}/edit` | Mengambil data kandidat spesifik untuk diedit. |
| `PUT` | `/admin/kandidat/{id}` | Memperbarui data kandidat (Foto bersifat opsional saat edit). |
| `DELETE` | `/admin/kandidat/{id}` | Menghapus data kandidat (Ditolak jika paslon sudah memiliki suara). |
| `GET` | `/admin/export/excel` | Mengunduh laporan rekapitulasi data pemilih berformat Spreadsheet Excel (`.xls`) dengan kop resmi. |

---

## 📦 4. Struktur Data (Database Models Reference)

### 📌 Tabel `pemilih` (DPT Siswa)

* `id` (Primary Key)
* `nis` (String, Unique) — Digunakan untuk login.
* `nama` (String)
* `kelas` (String)
* `paslon_dipilih` (Integer, Nullable) — Berisi ID kandidat yang dipilih.
* `sudah_memilih` (Boolean) — `0` (Belum), `1` (Sudah).
* `waktu_memilih` (Timestamp, Nullable) — Waktu tepat saat siswa menekan tombol konfirmasi suara.

### 📌 Tabel `kandidat` (Paslon)

* `id` (Primary Key)
* `no_paslon` (Integer) — Nomor urut paslon.
* `nama_ketua` (String)
* `nama_wakil` (String)
* `visi` (Text)
* `misi` (Text)
* `proggram` (Text, Nullable) — Program unggulan paslon.
* `foto` (String) — Path penyimpanan file foto (diakses publik via `storage/app/public/...`).

---

## 🛡️ 5. Catatan Penting untuk Frontend

1. **CSRF Token**: Setiap form dengan metode `POST`, `PUT`, atau `DELETE` **wajib** menyertakan directive `@csrf` (jika menggunakan Blade Laravel).
2. **File Upload**: Form yang mengirimkan file foto kandidat atau file import CSV wajib menggunakan atribut `enctype="multipart/form-data"`.
3. **Pemberian Suara**: Backend sudah dilindungi dengan *database transaction* dan *pessimistic locking* untuk mencegah *double vote*.

```
