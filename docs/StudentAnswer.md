# Dokumentasi API Jawaban Siswa (Student Answer)

API ini digunakan untuk mengelola jawaban siswa dalam sistem Sinaumatika. Ketika siswa submit kuis, jawaban dinilai secara otomatis dan riwayat kuis direkam sekaligus dalam satu transaksi.

## Request Headers

```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer {token}
```

---

## Alur Pengiriman Jawaban

```
Siswa submit kuis
       ↓
POST /student-answers (dengan semua jawaban + quiz_id)
       ↓
Sistem menilai otomatis tiap jawaban berdasarkan tipe soal:
  - essay / guess_image → Exact match → AI Gemini (fallback)
  - choice_image       → Cek is_correct pada opsi yang dipilih
  - match_image        → Proporsi pasangan benar
       ↓
Jawaban disimpan ke tabel student_answers
       ↓
Riwayat kuis direkam ke tabel answers_history (best score policy)
       ↓
Rekap nilai siswa diperbarui otomatis
```

---

## Endpoints

### 1. Mendapatkan Semua Jawaban Siswa

**Endpoint:**
```http
GET /api/admin/student-answers
```
```http
GET /api/users/student-answers
```

**Akses:** Admin, Guru (`/api/admin/`) | Siswa (`/api/users/`)

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "User answers retrieved successfully",
    "data": [
        {
            "id": 1,
            "student_id": 3,
            "question_id": 1,
            "answer_text": "15",
            "answer_option_id": null,
            "answer_image": null,
            "is_correct": true,
            "score": 10,
            "feedback": null,
            "submitted_at": "2025-05-20T08:30:00.000000Z",
            "student": {
                "id": 3,
                "name": "Andi Pratama"
            },
            "question": {
                "id": 1,
                "type": "essay",
                "question_text": "Berapa hasil dari 7 + 8?"
            }
        }
    ]
}
```

---

### 2. Mendapatkan Detail Jawaban Berdasarkan ID

**Endpoint:**
```http
GET /api/admin/student-answers/{id}
```
```http
GET /api/users/student-answers/{id}
```

**Akses:** Admin, Guru (`/api/admin/`) | Siswa (`/api/users/`)

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "User answer retrieved successfully",
    "data": {
        "id": 1,
        "student_id": 3,
        "question_id": 1,
        "answer_text": "15",
        "answer_option_id": null,
        "answer_image": null,
        "is_correct": true,
        "score": 10,
        "feedback": null,
        "submitted_at": "2025-05-20T08:30:00.000000Z"
    }
}
```

---

### 3. Mengirim Jawaban Kuis (Submit)

**Endpoint:**
```http
POST /api/admin/student-answers
```
```http
POST /api/users/student-answers
```

**Akses:** Admin, Guru (`/api/admin/`) | Siswa (`/api/users/`)

Mengirim semua jawaban siswa untuk satu kuis sekaligus. Penilaian dilakukan otomatis per soal.

**Request Body:**
```json
{
    "quiz_id": 1,
    "start_time": "2025-05-20T08:00:00",
    "answers": [
        {
            "student_id": 3,
            "question_id": 1,
            "answer_text": "15",
            "answer_option_id": null,
            "submitted_at": "2025-05-20T08:30:00"
        },
        {
            "student_id": 3,
            "question_id": 2,
            "answer_text": null,
            "answer_option_id": 5,
            "submitted_at": "2025-05-20T08:31:00"
        },
        {
            "student_id": 3,
            "question_id": 3,
            "answer_text": "[{\"left_id\": 10, \"right_id\": 10}, {\"left_id\": 11, \"right_id\": 11}]",
            "answer_option_id": null,
            "submitted_at": "2025-05-20T08:32:00"
        }
    ]
}
```

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|-----------|
| `quiz_id` | integer | Ya | ID kuis yang sedang dikerjakan |
| `start_time` | string | Tidak | Waktu mulai mengerjakan (`YYYY-MM-DD HH:MM:SS`) |
| `answers` | array | Ya | Array jawaban siswa |
| `answers[].student_id` | integer | Ya | ID siswa |
| `answers[].question_id` | integer | Ya | ID soal |
| `answers[].answer_text` | string | Kondisional | Jawaban teks (untuk `essay`, `guess_image`) atau JSON pasangan (untuk `match_image`) |
| `answers[].answer_option_id` | integer | Kondisional | ID opsi yang dipilih (untuk `choice_image`) |
| `answers[].submitted_at` | datetime | Tidak | Waktu jawaban dikirim |

#### Format `answer_text` per Tipe Soal

| Tipe Soal | Format `answer_text` | `answer_option_id` |
|-----------|---------------------|-------------------|
| `essay` | String teks biasa | `null` |
| `guess_image` | String teks biasa | `null` |
| `choice_image` | `null` | ID opsi yang dipilih |
| `match_image` | JSON array: `[{"left_id": X, "right_id": Y}]` | `null` |

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "User answer created successfully",
    "data": {
        "answers": [
            {
                "id": 10,
                "student_id": 3,
                "question_id": 1,
                "answer_text": "15",
                "answer_option_id": null,
                "is_correct": true,
                "score": 10,
                "submitted_at": "2025-05-20T08:30:00.000000Z"
            }
        ],
        "total_quiz_score": 30
    }
}
```

**Response Error:**
```json
// 422 - quiz_id tidak ada
{
    "status": false,
    "message": "quiz_id wajib disertakan dalam request."
}

// 422 - question_id tidak valid
{
    "status": false,
    "message": "question_id 99 bukan milik quiz_id 1."
}

// 422 - tipe soal tidak sesuai
{
    "status": false,
    "message": "Soal choice_image (question_id: 2) tidak boleh menerima answer_text. Gunakan answer_option_id."
}

// 422 - format JSON salah untuk match_image
{
    "status": false,
    "message": "answer_text untuk soal match_image (question_id: 3) harus berupa JSON array yang valid."
}
```

---

### 4. Mengupdate Jawaban Siswa

**Endpoint:**
```http
PUT /api/admin/student-answers
```
```http
PUT /api/users/student-answers
```

**Akses:** Admin, Guru (`/api/admin/`) | Siswa (`/api/users/`)

Mengupdate jawaban yang sudah ada. Sertakan `id` jawaban di setiap item `answers[]`.

> **Catatan:** Update jawaban **tidak** memperbarui riwayat kuis (`answers_history`). Riwayat bersifat immutable (hanya bisa ditambah saat submit pertama).

**Request Body:**
```json
{
    "quiz_id": 1,
    "answers": [
        {
            "id": 10,
            "student_id": 3,
            "question_id": 1,
            "answer_text": "16",
            "answer_option_id": null
        }
    ]
}
```

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "User answer updated successfully",
    "data": {
        "answers": [ ... ],
        "total_quiz_score": 0
    }
}
```

---

### 5. Menghapus Jawaban Siswa

**Endpoint:**
```http
DELETE /api/admin/student-answers
```
```http
DELETE /api/users/student-answers
```

**Akses:** Admin, Guru (`/api/admin/`) | Siswa (`/api/users/`)

Menghapus beberapa jawaban sekaligus berdasarkan array ID.

**Request Body:**
```json
{
    "ids": [10, 11, 12]
}
```

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "3 jawaban siswa berhasil dihapus",
    "data": {
        "deleted_count": 3
    }
}
```

**Response Error:**
```json
// 404 - Tidak ada ID diberikan
{
    "status": false,
    "message": "Tidak ada ID yang diberikan untuk penghapusan"
}
```

---

## Mekanisme Penilaian Otomatis (Auto-Grading)

### Tipe `essay` dan `guess_image`

Penilaian dilakukan dalam dua tahap:

1. **Exact Match** (Cepat, tanpa API):
   - Jawaban siswa dinormalisasi: trim, lowercase, hapus tanda baca
   - Dibandingkan dengan semua kunci jawaban yang tersedia
   - Jika cocok → skor penuh

2. **AI Grading via Google Gemini** (Fallback jika exact match gagal):
   - Jawaban dikirim ke Gemini API untuk penilaian semantik
   - Menghasilkan skor 0–100
   - `>= 100` → skor penuh (`is_correct = true`)
   - `> 0 dan < 100` → skor parsial (`is_correct = false`)
   - `= 0` → tidak dapat skor

   > AI memberikan toleransi untuk: ejaan mirip (`tuju` = `tujuh`), angka digit dan tulisan (`7` = `tujuh`), sinonim, huruf kapital.

### Tipe `choice_image`

- Cek field `is_correct` pada opsi yang dipilih siswa (`answer_option_id`)
- Skor penuh jika opsi benar, skor 0 jika salah
- Soal ini **tidak menerima** `answer_text`

### Tipe `match_image`

- Hitung jumlah pasangan yang benar dari total pasangan
- Skor proporsional: `(pasangan_benar / total_pasangan) × skor_maksimal`
- `answer_text` harus berupa JSON array: `[{"left_id": X, "right_id": Y}, ...]`
- Soal ini **tidak menerima** `answer_option_id`

---

## Struktur Data Jawaban Siswa

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| `id` | integer | ID unik jawaban |
| `student_id` | integer | ID siswa |
| `question_id` | integer | ID soal |
| `answer_text` | text | Jawaban teks / JSON pasangan |
| `answer_option_id` | integer | ID opsi yang dipilih (untuk `choice_image`) |
| `answer_image` | string | Path gambar (otomatis diisi dari opsi untuk `choice_image`) |
| `is_correct` | boolean | Apakah jawaban benar |
| `score` | float | Skor yang diperoleh |
| `feedback` | text | Feedback dari guru (opsional) |
| `submitted_at` | datetime | Waktu jawaban dikirim |

