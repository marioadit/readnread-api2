# Dokumentasi API Kuis (Quiz)

API ini digunakan untuk mengelola kuis dalam sistem Sinaumatika. Setiap kuis terhubung ke sebuah pelajaran dan berisi kumpulan soal yang harus dikerjakan siswa.

## Request Headers

```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer {token}
```

---

## Endpoints

### 1. Mendapatkan Semua Kuis

**Endpoint:**
```http
GET /api/admin/quiz
```

**Akses:** Admin, Guru

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Success",
    "data": [
        {
            "id": 1,
            "lesson_id": 1,
            "title": "Kuis Bilangan Bulat #1",
            "description": "Kuis untuk menguji pemahaman bilangan bulat",
            "start_time": "2025-05-20T08:00:00.000000Z",
            "end_time": "2025-05-20T09:00:00.000000Z",
            "is_active": true,
            "created_at": "2025-05-17T10:00:00.000000Z",
            "updated_at": "2025-05-17T10:00:00.000000Z"
        }
    ]
}
```

---

### 2. Mendapatkan Detail Kuis

**Endpoint:**
```http
GET /api/admin/quiz/{id}
```

**Akses:** Admin, Guru

Mengembalikan detail kuis beserta relasi `lesson` dan `questions`.

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Success",
    "data": {
        "id": 1,
        "lesson_id": 1,
        "title": "Kuis Bilangan Bulat #1",
        "description": "Kuis untuk menguji pemahaman bilangan bulat",
        "start_time": "2025-05-20T08:00:00.000000Z",
        "end_time": "2025-05-20T09:00:00.000000Z",
        "is_active": true,
        "lesson": {
            "id": 1,
            "class_id": 1,
            "title": "Pengenalan Bilangan Bulat"
        },
        "questions": [
            {
                "id": 1,
                "quiz_id": 1,
                "type": "essay",
                "question_text": "Berapa hasil dari 7 + 8?",
                "question_image": null,
                "order": 1,
                "score": 10
            }
        ]
    }
}
```

---

### 3. Mendapatkan Kuis beserta Pelajarannya

**Endpoint:**
```http
GET /api/admin/quiz/{id}/lessons
```

**Akses:** Admin, Guru

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Success",
    "data": {
        "id": 1,
        "lesson_id": 1,
        "title": "Kuis Bilangan Bulat #1",
        "is_active": true,
        "lesson": {
            "id": 1,
            "class_id": 1,
            "title": "Pengenalan Bilangan Bulat",
            "thumbnail": "storage/thumbnails/thumbnail_123.jpg"
        }
    }
}
```

---

### 4. Mendapatkan Kuis beserta Soal-soalnya

**Endpoint:**
```http
GET /api/admin/quiz/{id}/questions
```

**Akses:** Admin, Guru

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Success",
    "data": {
        "id": 1,
        "title": "Kuis Bilangan Bulat #1",
        "questions": [
            {
                "id": 1,
                "type": "essay",
                "question_text": "Berapa hasil dari 7 + 8?",
                "order": 1,
                "score": 10
            },
            {
                "id": 2,
                "type": "choice_image",
                "question_text": "Pilih jawaban yang benar!",
                "order": 2,
                "score": 10
            }
        ]
    }
}
```

---

### 5. Mendapatkan Kuis + Pelajaran + Soal Sekaligus

**Endpoint:**
```http
GET /api/admin/quiz/{id}/LessonQuestions
```
```http
GET /api/users/quiz/{id}/LessonQuestions
```

**Akses:** Admin, Guru (`/api/admin/quiz/{id}/LessonQuestions`) | Siswa (`/api/users/quiz/{id}/LessonQuestions`)

Endpoint ini digunakan oleh siswa untuk mengambil seluruh data yang diperlukan saat mengerjakan kuis: informasi kuis, pelajaran terkait, dan daftar soal lengkap.

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Success",
    "data": {
        "id": 1,
        "lesson_id": 1,
        "title": "Kuis Bilangan Bulat #1",
        "description": "Kuis untuk menguji pemahaman bilangan bulat",
        "start_time": "2025-05-20T08:00:00.000000Z",
        "end_time": "2025-05-20T09:00:00.000000Z",
        "is_active": true,
        "lesson": {
            "id": 1,
            "class_id": 1,
            "title": "Pengenalan Bilangan Bulat"
        },
        "questions": [
            {
                "id": 1,
                "quiz_id": 1,
                "type": "essay",
                "question_text": "Berapa hasil dari 7 + 8?",
                "question_image": null,
                "order": 1,
                "score": 10,
                "options": [],
                "match_pairs": [],
                "essay_answers": []
            }
        ]
    }
}
```

---

### 6. Membuat Kuis Baru

**Endpoint:**
```http
POST /api/admin/quiz
```

**Akses:** Admin, Guru

**Request Body:**
```json
{
    "lesson_id": 1,
    "title": "Kuis Bilangan Bulat #1",
    "description": "Kuis untuk menguji pemahaman bilangan bulat",
    "start_time": "2025-05-20 08:00:00",
    "end_time": "2025-05-20 09:00:00",
    "is_active": true
}
```

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|-----------|
| `lesson_id` | integer | Ya | ID pelajaran, harus ada di tabel `lessons` |
| `title` | string | Ya | Judul kuis |
| `description` | string | Tidak | Deskripsi kuis |
| `start_time` | datetime | Tidak | Waktu mulai kuis (`YYYY-MM-DD HH:MM:SS`) |
| `end_time` | datetime | Tidak | Waktu selesai kuis (`YYYY-MM-DD HH:MM:SS`) |
| `is_active` | boolean | Ya | `true` = kuis aktif, `false` = kuis nonaktif |

**Response Sukses (201 Created):**
```json
{
    "status": true,
    "message": "Kuis berhasil dibuat",
    "data": {
        "id": 2,
        "lesson_id": 1,
        "title": "Kuis Bilangan Bulat #1",
        "description": "Kuis untuk menguji pemahaman bilangan bulat",
        "start_time": "2025-05-20T08:00:00.000000Z",
        "end_time": "2025-05-20T09:00:00.000000Z",
        "is_active": true,
        "created_at": "2025-05-17T10:30:00.000000Z",
        "updated_at": "2025-05-17T10:30:00.000000Z"
    }
}
```

**Response Error:**
```json
// 422 - Validasi Gagal
{
    "status": false,
    "message": "Validasi gagal",
    "errors": {
        "lesson_id": ["The selected lesson id is invalid."],
        "is_active": ["The is active field is required."]
    }
}
```

---

### 7. Mengupdate Kuis

**Endpoint:**
```http
PUT /api/admin/quiz/{id}
```

**Akses:** Admin, Guru

**Request Body:** sama seperti saat membuat kuis

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Kuis berhasil diperbarui",
    "data": {
        "id": 2,
        "lesson_id": 1,
        "title": "Kuis Bilangan Bulat #1 Update",
        "is_active": false,
        "updated_at": "2025-05-17T11:00:00.000000Z"
    }
}
```

---

### 8. Menghapus Kuis

**Endpoint:**
```http
DELETE /api/admin/quiz/{id}
```

**Akses:** Admin, Guru

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Kuis berhasil dihapus",
    "data": null
}
```

---

## Endpoints Hasil Kuis

### 9. Mendapatkan Hasil Kuis Siswa (per Kuis)

**Endpoint:**
```http
GET /api/admin/quizzes/{quizId}/results
```
```http
GET /api/users/quizzes/{quizId}/results
```

**Akses:** Admin, Guru (`/api/admin/`) | Siswa (`/api/users/`)

Mengembalikan hasil kuis untuk siswa yang sedang login (atau siswa tertentu bagi admin).

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Quiz results retrieved successfully",
    "data": {
        "quiz_id": 1,
        "quiz_title": "Kuis Bilangan Bulat #1",
        "total_questions": 5,
        "answered_questions": 5,
        "correct_answers": 3,
        "total_score": 30,
        "max_possible_score": 50,
        "percentage_score": 60.0,
        "completion_rate": 100.0,
        "questions": [
            {
                "question_id": 1,
                "question_text": "Berapa hasil dari 7 + 8?",
                "question_type": "essay",
                "max_score": 10,
                "student_answer": {
                    "answer_text": "15",
                    "answer_option_id": null,
                    "is_correct": true,
                    "score": 10,
                    "feedback": null,
                    "submitted_at": "2025-05-20T08:30:00.000000Z"
                }
            }
        ]
    }
}
```

---

### 10. Mendapatkan Semua Hasil Kuis Milik Siswa

**Endpoint:**
```http
GET /api/admin/quizzes/results/{userId}
```
```http
GET /api/users/quizzes/results
```

**Akses:** Admin, Guru (`/api/admin/quizzes/results/{userId}`) | Siswa (`/api/users/quizzes/results`)

Mengembalikan ringkasan semua kuis yang pernah dikerjakan oleh seorang siswa.

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "User quiz results retrieved successfully",
    "data": [
        {
            "quiz_id": 1,
            "quiz_title": "Kuis Bilangan Bulat #1",
            "lesson": {
                "id": 1,
                "title": "Pengenalan Bilangan Bulat"
            },
            "class": {
                "id": 1,
                "name": "Kelas 5 A"
            },
            "total_questions": 5,
            "answered_questions": 5,
            "correct_answers": 3,
            "total_score": 30,
            "max_possible_score": 50,
            "percentage_score": 60.0,
            "completion_rate": 100.0,
            "start_time": "2025-05-20T08:00:00.000000Z",
            "end_time": "2025-05-20T09:00:00.000000Z",
            "submitted_at": "2025-05-20T08:30:00.000000Z"
        }
    ]
}
```

---

## Struktur Data Kuis

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| `id` | integer | ID unik kuis |
| `lesson_id` | integer | ID pelajaran yang memiliki kuis ini |
| `title` | string | Judul kuis |
| `description` | text | Deskripsi kuis |
| `start_time` | datetime | Waktu mulai kuis |
| `end_time` | datetime | Waktu berakhir kuis |
| `is_active` | boolean | Status aktif kuis |
| `created_at` | datetime | Waktu kuis dibuat |
| `updated_at` | datetime | Waktu kuis terakhir diperbarui |

---

## Catatan Penting

- Kuis hanya bisa dikerjakan jika `is_active = true`.
- `start_time` dan `end_time` bersifat opsional, namun umumnya digunakan untuk membatasi waktu pengerjaan.
- Satu pelajaran dapat memiliki **lebih dari satu kuis**.

