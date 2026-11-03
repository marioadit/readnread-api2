# Sinaumatika — Dokumentasi Sistem

## Tentang Sinaumatika

**Sinaumatika** adalah sistem backend berbasis REST API yang dirancang untuk mendukung pembelajaran dan pengukuran kemampuan siswa Sekolah Dasar (SD) dalam mata pelajaran **Matematika**. Sistem ini dibangun menggunakan **Laravel** dan menyediakan infrastruktur yang lengkap untuk manajemen kelas, pelajaran, kuis, dan penilaian otomatis berbasis kecerdasan buatan.

Nama "Sinaumatika" berasal dari gabungan kata **"sinau"** (belajar dalam Bahasa Jawa) dan **"matematika"**, mencerminkan tujuan utama sistem: menjadikan proses belajar matematika lebih terstruktur, terukur, dan mudah dianalisis.

---

## Tujuan Sistem

1. **Mengukur kemampuan siswa** dalam mata pelajaran Matematika secara objektif dan terstandarisasi.
2. **Memudahkan guru** dalam mengelola kelas, membuat soal, dan memantau perkembangan siswa.
3. **Memberikan pengalaman belajar interaktif** bagi siswa dengan berbagai tipe soal yang beragam.
4. **Merekap nilai otomatis** sehingga guru dan admin dapat dengan mudah memantau progres setiap siswa dari kelas 1 hingga kelas 6.
5. **Memanfaatkan AI** (Google Gemini) untuk penilaian jawaban essay yang lebih cerdas dan toleran terhadap variasi ejaan.

---

## Arsitektur Sistem

```
Frontend (Vue.js / Mobile App)
          ↕  REST API (JSON)
  Laravel Backend (Sinaumatika API)
          ↕
  MySQL Database
          ↕
  Google Gemini API (AI Grading)
```

Sistem menggunakan pola **Repository + Service** untuk memisahkan logika bisnis dari layer controller:

- **Controller** — Menerima HTTP request dan mengembalikan response
- **Service** — Orkestrator logika bisnis (transaksi, validasi tingkat tinggi)
- **Repository** — Interaksi langsung dengan database melalui Eloquent ORM
- **Model** — Representasi tabel database dan relasi antar tabel

---

## Peran Pengguna (Roles)

| Role | Deskripsi | Akses Utama |
|------|-----------|-------------|
| **Admin** | Administrator sistem | Akses penuh ke semua endpoint; manajemen pengguna, kelas, pelajaran, kuis, soal, nilai |
| **Guru** | Pengajar / Tenaga pendidik | Manajemen kelas yang diampu, pelajaran, kuis, soal; memantau nilai siswa |
| **Siswa** | Peserta didik | Mengikuti kuis, melihat pelajaran, melihat nilai sendiri |

---

## Fitur-Fitur Utama

### 1. Autentikasi & Manajemen Sesi
Sistem menggunakan **Laravel Sanctum** untuk autentikasi berbasis Bearer Token.

- **Login** — Masuk dengan email dan password; menghasilkan token yang berlaku 1 hari
- **Register** — Pendaftaran pengguna baru (siswa/guru/admin)
- **Logout** — Menghapus semua token aktif pengguna

📄 Dokumentasi: [Auth.md](Auth.md)

---

### 2. Manajemen Pengguna (User Management)
Admin dan guru dapat mengelola data pengguna secara lengkap.

- CRUD pengguna (siswa, guru, admin)
- Melihat daftar siswa aktif dan alumni (status `lulus`)
- Reset password pengguna
- Update profil dan password mandiri (oleh pengguna sendiri)
- Dashboard guru (ringkasan kelas dan siswa)
- Dashboard admin (statistik keseluruhan sistem)

📄 Dokumentasi: [User.md](User.md)

---

### 3. Manajemen Kelas (Class Management)
Setiap kelas merepresentasikan satu rombongan belajar dalam satu semester tertentu.

- CRUD kelas (nama, jenjang, semester, tahun ajaran, guru pengampu)
- Menambahkan dan mengeluarkan siswa dari kelas
- Satu siswa hanya bisa terdaftar di **satu kelas aktif**
- Mendapatkan daftar pelajaran dalam kelas

**Atribut Kelas:**
- `level` — Jenjang: Kelas 1 s.d. Kelas 6
- `semester` — `gasal` atau `genap`
- `academic_year` — Tahun ajaran (format: `2025/2026`)

📄 Dokumentasi: [Class.md](Class.md)

---

### 4. Manajemen Pelajaran (Lesson Management)
Setiap pelajaran adalah unit materi belajar yang terhubung ke kelas tertentu.

- CRUD pelajaran (judul, deskripsi, konten, thumbnail)
- Upload thumbnail gambar pelajaran
- Melihat pelajaran berdasarkan kelas
- Melihat kuis-kuis yang terhubung ke pelajaran

📄 Dokumentasi: [Lesson.md](Lesson.md)

---

### 5. Manajemen Kuis (Quiz Management)
Kuis adalah instrumen evaluasi yang terdiri dari kumpulan soal dan terhubung ke pelajaran tertentu.

- CRUD kuis (judul, deskripsi, waktu mulai/selesai, status aktif)
- Aktivasi/nonaktivasi kuis (`is_active`)
- Melihat soal-soal dalam kuis
- Melihat hasil kuis per siswa
- Melihat semua kuis yang pernah dikerjakan siswa

📄 Dokumentasi: [Quiz.md](Quiz.md)

---

### 6. Manajemen Soal (Question Management)
Soal adalah unit pertanyaan dalam kuis. Sistem mendukung **4 tipe soal** yang berbeda:

| Tipe | Keterangan |
|------|-----------|
| **Essay** | Siswa mengetik jawaban teks; dinilai dengan exact match atau AI Gemini |
| **Choice Image** | Pilihan ganda dengan gambar; satu jawaban benar |
| **Guess Image** | Tebak gambar; siswa mengetik nama/deskripsi dari gambar yang ditampilkan |
| **Match Image** | Pasangkan gambar; siswa mencocokkan item kiri dengan item kanan |

Fitur:
- CRUD soal dengan dukungan upload gambar (soal, opsi, pasangan)
- Membuat banyak soal sekaligus (batch insert)
- Mendukung banyak kunci jawaban benar untuk tipe essay (pisahkan dengan `|`)
- Melihat jawaban siswa per soal

📄 Dokumentasi: [Question.md](Question.md)

---

### 7. Manajemen Opsi Jawaban (Option Management)
Opsi adalah pilihan jawaban untuk soal tipe `choice_image`.

- CRUD opsi jawaban dengan dukungan gambar
- CRUD kunci jawaban essay (`essay_answers`) untuk soal tipe `essay` dan `guess_image`

📄 Dokumentasi: [Option.md](Option.md)

---

### 8. Pengiriman & Penilaian Jawaban (Student Answer)
Siswa mengirim semua jawaban untuk satu kuis dalam satu request.

**Penilaian Otomatis per Tipe Soal:**
- `essay` / `guess_image` → Exact match → Jika gagal, lanjut ke **AI Gemini** (partial score)
- `choice_image` → Cek opsi yang dipilih apakah `is_correct = true`
- `match_image` → Skor proporsional berdasarkan jumlah pasangan benar

**AI Grading (Google Gemini):**
- Memberikan toleransi untuk ejaan mirip, sinonim, angka digit vs tulisan
- Menghasilkan skor 0–100 yang dikonversi ke partial score
- Fallback ke 0 jika API tidak tersedia

📄 Dokumentasi: [StudentAnswer.md](StudentAnswer.md)

---

### 9. Riwayat Jawaban (Answer History)
Setiap submit kuis oleh siswa dicatat sebagai riwayat.

**Best Score Policy:**
- Hanya satu riwayat per siswa per kuis
- Skor lama digantikan hanya jika skor baru lebih tinggi
- Semua data bersifat **snapshot** (tidak berubah meskipun master data diedit)

Data yang direkam: judul kuis, judul pelajaran, nama siswa, nama kelas, jenjang, semester, total soal, jawaban benar, total skor, persentase, waktu pengerjaan.

📄 Dokumentasi: [AnswerHistory.md](AnswerHistory.md)

---

### 10. Rekap Nilai Siswa (Student Score Recap)
Rekap nilai adalah agregasi nilai siswa dalam bentuk matriks kelas × semester.

- Nilai otomatis diperbarui setiap ada submit kuis (via model event)
- Format matriks: Kelas 1–6 × Semester 1–2 (total 12 sel per siswa)
- Filter berdasarkan kelas, nama, status, dan pagination
- Endpoint sinkronisasi manual untuk pemulihan data
- Siswa dapat melihat rekap nilai sendiri

📄 Dokumentasi: [StudentRecap.md](StudentRecap.md)

---

## Alur Penggunaan Sistem

### Alur Guru
```
1. Login
2. Melihat kelas yang diampu
3. Membuat pelajaran di kelas
4. Membuat kuis di pelajaran
5. Menambahkan soal ke kuis (essay / pilihan / tebak / pasangkan)
6. Mengaktifkan kuis (is_active = true)
7. Memantau riwayat jawaban siswa
8. Melihat rekap nilai siswa
```

### Alur Siswa
```
1. Login
2. Melihat kelas yang diikuti
3. Melihat pelajaran di kelas
4. Melihat kuis yang tersedia (is_active = true)
5. Mengerjakan kuis (mengambil soal via LessonQuestions)
6. Submit jawaban kuis
7. Melihat hasil kuis
8. Melihat riwayat dan rekap nilai sendiri
```

---

## Teknologi yang Digunakan

| Komponen | Teknologi |
|----------|-----------|
| Backend Framework | Laravel 10 |
| Autentikasi | Laravel Sanctum (Bearer Token) |
| Database | MySQL |
| AI Grading | Google Gemini API (`gemini-3.1-flash-lite`) |
| File Storage | Laravel Storage (`public` disk) |
| Pola Arsitektur | Repository + Service Pattern |

---

## Ringkasan Endpoint

| Prefix | Deskripsi | Akses |
|--------|-----------|-------|
| `POST /api/auth/login` | Login | Publik |
| `POST /api/auth/register` | Register | Publik |
| `DELETE /api/auth/logout` | Logout | Semua |
| `/api/admin/users` | Manajemen pengguna | Admin, Guru |
| `/api/admin/classes` | Manajemen kelas | Admin, Guru |
| `/api/admin/lessons` | Manajemen pelajaran | Admin, Guru |
| `/api/admin/quiz` | Manajemen kuis | Admin, Guru |
| `/api/admin/questions` | Manajemen soal | Admin, Guru |
| `/api/admin/options` | Manajemen opsi | Admin, Guru |
| `/api/admin/student-answers` | Jawaban siswa (admin) | Admin, Guru |
| `/api/admin/answer-history` | Riwayat kuis | Admin, Guru |
| `/api/admin/student-recaps` | Rekap nilai | Admin, Guru |
| `/api/guru/dashboard/teacher` | Dashboard guru | Guru |
| `/api/admin/dashboard/admin` | Dashboard admin | Admin |
| `/api/users/*` | Endpoint untuk siswa | Siswa |

---

## Dokumentasi API per Fitur

| Fitur | File Dokumentasi |
|-------|-----------------|
| Autentikasi | [Auth.md](Auth.md) |
| Pengguna | [User.md](User.md) |
| Kelas | [Class.md](Class.md) |
| Pelajaran | [Lesson.md](Lesson.md) |
| Kuis | [Quiz.md](Quiz.md) |
| Soal | [Question.md](Question.md) |
| Opsi & Kunci Jawaban | [Option.md](Option.md) |
| Jawaban Siswa | [StudentAnswer.md](StudentAnswer.md) |
| Riwayat Jawaban | [AnswerHistory.md](AnswerHistory.md) |
| Rekap Nilai | [StudentRecap.md](StudentRecap.md) |

