# Dokumentasi API Riwayat Jawaban (Answer History)

API ini digunakan untuk melihat riwayat pengerjaan kuis oleh siswa dalam sistem Sinaumatika. Riwayat direkam otomatis setiap kali siswa berhasil submit kuis.

## Request Headers

```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer {token}
```

---

## Kebijakan Penyimpanan (Best Score Policy)

Sistem Sinaumatika menerapkan kebijakan **"best score"** pada riwayat jawaban:

- Setiap kombinasi `student_id` + `quiz_id` hanya menyimpan **satu** record history.
- Jika siswa mengerjakan kuis lagi:
  - **Skor baru lebih tinggi** → history lama dihapus, diganti history baru (termasuk jawaban lama)
  - **Skor baru lebih rendah/sama** → history lama dipertahankan, jawaban baru dihapus
- Hal ini memastikan nilai yang tersimpan selalu merupakan **pencapaian terbaik** siswa.

---

## Endpoints

### 1. Mendapatkan Semua Riwayat Kuis

**Endpoint:**
```http
GET /api/admin/answer-history
```

**Akses:** Admin, Guru

Mengembalikan seluruh riwayat kuis semua siswa, diurutkan dari yang terbaru.

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Answer histories retrieved successfully",
    "data": [
        {
            "id": 1,
            "student_id": 3,
            "class_id": 9,
            "quiz_id": 1,
            "lesson_id": 1,
            "quiz_title": "Kuis Bilangan Bulat #1",
            "lesson_title": "Pengenalan Bilangan Bulat",
            "student_name": "Andi Pratama",
            "class_name": "Kelas 5 A",
            "class_level": "kelas 5",
            "class_semester": "genap",
            "class_academic_year": "2025/2026",
            "question_ids": [1, 2, 3, 4, 5],
            "student_answers_ids": [10, 11, 12, 13, 14],
            "total_questions": 5,
            "answered_questions": 5,
            "correct_answers": 3,
            "total_score": 30,
            "max_possible_score": 50,
            "percentage_score": 60.0,
            "completion_rate": 100.0,
            "start_time": "2025-05-20T08:00:00.000000Z",
            "end_time": "2025-05-20T08:30:00.000000Z",
            "submitted_at": "2025-05-20T08:30:00.000000Z",
            "student": {
                "id": 3,
                "name": "Andi Pratama",
                "email": "andi@example.com"
            },
            "class": {
                "id": 9,
                "name": "Kelas 5 A"
            },
            "quiz": {
                "id": 1,
                "title": "Kuis Bilangan Bulat #1"
            },
            "lesson": {
                "id": 1,
                "title": "Pengenalan Bilangan Bulat"
            }
        }
    ]
}
```

---

### 2. Mendapatkan Riwayat Berdasarkan Siswa

**Endpoint:**
```http
GET /api/admin/answer-history/student/{studentId}
```

**Akses:** Admin, Guru

Mengembalikan semua riwayat kuis milik siswa dengan ID tertentu.

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Answer histories retrieved successfully",
    "data": [
        {
            "id": 1,
            "student_id": 3,
            "quiz_id": 1,
            "quiz_title": "Kuis Bilangan Bulat #1",
            "lesson_title": "Pengenalan Bilangan Bulat",
            "total_questions": 5,
            "correct_answers": 3,
            "percentage_score": 60.0,
            "submitted_at": "2025-05-20T08:30:00.000000Z"
        }
    ]
}
```

**Response Error:**
```json
// 404 - Tidak ada history
{
    "status": false,
    "message": "Answer histories not found for this student"
}
```

---

### 3. Mendapatkan Riwayat Berdasarkan Kuis

**Endpoint:**
```http
GET /api/admin/answer-history/quiz/{quizId}
```

**Akses:** Admin, Guru

Mengembalikan semua riwayat pengerjaan kuis tertentu oleh semua siswa.

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Answer histories retrieved successfully",
    "data": [
        {
            "id": 1,
            "student_id": 3,
            "quiz_id": 1,
            "student_name": "Andi Pratama",
            "class_name": "Kelas 5 A",
            "percentage_score": 60.0,
            "correct_answers": 3,
            "total_questions": 5,
            "submitted_at": "2025-05-20T08:30:00.000000Z",
            "student": {
                "id": 3,
                "name": "Andi Pratama"
            },
            "class": {
                "id": 9,
                "name": "Kelas 5 A"
            }
        }
    ]
}
```

---

### 4. Mendapatkan Detail Riwayat (per Siswa + per Kuis)

**Endpoint:**
```http
GET /api/admin/answer-history/detail/{studentId}/{quizId}
```

**Akses:** Admin, Guru

Mengembalikan satu record detail riwayat pengerjaan kuis spesifik oleh siswa tertentu.

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Answer history retrieved successfully",
    "data": {
        "id": 1,
        "student_id": 3,
        "class_id": 9,
        "quiz_id": 1,
        "lesson_id": 1,
        "quiz_title": "Kuis Bilangan Bulat #1",
        "lesson_title": "Pengenalan Bilangan Bulat",
        "student_name": "Andi Pratama",
        "class_name": "Kelas 5 A",
        "class_level": "kelas 5",
        "class_semester": "genap",
        "class_academic_year": "2025/2026",
        "question_ids": [1, 2, 3, 4, 5],
        "student_answers_ids": [10, 11, 12, 13, 14],
        "total_questions": 5,
        "answered_questions": 5,
        "correct_answers": 3,
        "total_score": 30,
        "max_possible_score": 50,
        "percentage_score": 60.0,
        "completion_rate": 100.0,
        "start_time": "2025-05-20T08:00:00.000000Z",
        "end_time": "2025-05-20T08:30:00.000000Z",
        "submitted_at": "2025-05-20T08:30:00.000000Z"
    }
}
```

**Response Error:**
```json
// 404 - History Tidak Ditemukan
{
    "status": false,
    "message": "Answer history not found for this student and quiz"
}
```

---

### 5. Mendapatkan Riwayat Sendiri (untuk Siswa Login)

**Endpoint:**
```http
GET /api/users/answer-history
```

**Akses:** Siswa

Mengembalikan seluruh riwayat kuis milik siswa yang sedang login.

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Answer histories retrieved successfully",
    "data": [
        {
            "id": 1,
            "quiz_id": 1,
            "quiz_title": "Kuis Bilangan Bulat #1",
            "lesson_title": "Pengenalan Bilangan Bulat",
            "percentage_score": 60.0,
            "correct_answers": 3,
            "total_questions": 5,
            "submitted_at": "2025-05-20T08:30:00.000000Z"
        }
    ]
}
```

---

### 6. Mendapatkan Detail Riwayat Kuis Sendiri

**Endpoint:**
```http
GET /api/users/answer-history/quiz/{quizId}
```

**Akses:** Siswa

Mengembalikan detail riwayat pengerjaan kuis tertentu oleh siswa yang sedang login.

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Answer history retrieved successfully",
    "data": {
        "id": 1,
        "quiz_id": 1,
        "quiz_title": "Kuis Bilangan Bulat #1",
        "lesson_title": "Pengenalan Bilangan Bulat",
        "class_name": "Kelas 5 A",
        "class_level": "kelas 5",
        "class_semester": "genap",
        "total_questions": 5,
        "answered_questions": 5,
        "correct_answers": 3,
        "total_score": 30,
        "max_possible_score": 50,
        "percentage_score": 60.0,
        "completion_rate": 100.0,
        "start_time": "2025-05-20T08:00:00.000000Z",
        "end_time": "2025-05-20T08:30:00.000000Z",
        "submitted_at": "2025-05-20T08:30:00.000000Z"
    }
}
```

---

## Struktur Data Riwayat Jawaban

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| `id` | integer | ID unik riwayat |
| `student_id` | integer | ID siswa |
| `class_id` | integer | ID kelas saat mengerjakan |
| `quiz_id` | integer | ID kuis |
| `lesson_id` | integer | ID pelajaran |
| `quiz_title` | string | **Snapshot** judul kuis saat dikerjakan |
| `lesson_title` | string | **Snapshot** judul pelajaran |
| `student_name` | string | **Snapshot** nama siswa |
| `class_name` | string | **Snapshot** nama kelas |
| `class_level` | string | **Snapshot** jenjang kelas |
| `class_semester` | string | **Snapshot** semester |
| `class_academic_year` | string | **Snapshot** tahun ajaran |
| `question_ids` | array | Array ID soal yang dikerjakan |
| `student_answers_ids` | array | Array ID jawaban siswa |
| `total_questions` | integer | Total soal dalam kuis |
| `answered_questions` | integer | Jumlah soal yang dijawab |
| `correct_answers` | integer | Jumlah jawaban benar |
| `total_score` | float | Total skor yang diperoleh |
| `max_possible_score` | float | Skor maksimal yang bisa diraih |
| `percentage_score` | float | Persentase skor (0–100) |
| `completion_rate` | float | Persentase penyelesaian kuis (0–100) |
| `start_time` | datetime | Waktu mulai mengerjakan |
| `end_time` | datetime | Waktu selesai mengerjakan |
| `submitted_at` | datetime | Waktu submit kuis |

---

## Catatan Penting

- Field `quiz_title`, `lesson_title`, `student_name`, `class_name`, dll. adalah **snapshot** yang diambil saat kuis dikerjakan. Nilai ini **tidak berubah** meskipun data master (kuis/pelajaran/kelas) diedit atau dihapus.
- Riwayat bersifat **immutable** — tidak dapat diubah setelah disimpan; hanya bisa digantikan jika siswa memperoleh skor lebih tinggi.
- Kolom `question_ids` dan `student_answers_ids` disimpan sebagai JSON array di database.
- Rekap nilai (`student_score_recaps`) diperbarui otomatis setiap ada perubahan pada tabel ini.

