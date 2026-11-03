# Dokumentasi API Soal (Question)

API ini digunakan untuk mengelola soal-soal dalam kuis pada sistem Sinaumatika. Soal mendukung **4 tipe** yang berbeda, masing-masing dengan struktur data dan mekanisme penilaian yang unik.

## Request Headers

```http
Accept: application/json
Content-Type: multipart/form-data  // Untuk soal yang mengandung gambar
Authorization: Bearer {token}
```

> **Penting:** Gunakan `multipart/form-data` saat mengirimkan soal yang mengandung gambar (`question_image`, `option_image`, `left_image`, `right_image`).

---

## Tipe Soal

| Tipe | Keterangan |
|------|-----------|
| `essay` | Siswa mengetik jawaban teks; dinilai dengan exact match atau AI (Gemini) |
| `choice_image` | Pilihan ganda; setiap opsi dapat berupa teks dan/atau gambar |
| `guess_image` | Tebak gambar; siswa mengetik jawaban dari gambar soal yang ditampilkan |
| `match_image` | Pasangkan gambar; siswa mencocokkan pasangan kiri-kanan |

---

## Endpoints

### 1. Mendapatkan Semua Soal

**Endpoint:**
```http
GET /api/admin/questions
```

**Akses:** Admin, Guru

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Questions retrieved successfully",
    "data": [
        {
            "id": 1,
            "quiz_id": 1,
            "type": "essay",
            "question_text": "Berapa hasil dari 7 + 8?",
            "question_image": null,
            "order": 1,
            "score": 10,
            "quiz": { "id": 1, "title": "Kuis Bilangan Bulat #1" },
            "options": [],
            "match_pairs": [],
            "essay_answers": [
                { "id": 1, "option_text": "15" }
            ]
        }
    ]
}
```

---

### 2. Mendapatkan Detail Soal

**Endpoint:**
```http
GET /api/admin/questions/{id}
```
```http
GET /api/users/questions/{id}
```

**Akses:** Admin, Guru (`/api/admin/`) | Siswa (`/api/users/`)

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Question retrieved successfully",
    "data": {
        "id": 1,
        "quiz_id": 1,
        "type": "choice_image",
        "question_text": "Manakah gambar yang menunjukkan angka 7?",
        "question_image": null,
        "order": 2,
        "score": 10,
        "options": [
            {
                "id": 1,
                "option_text": "Tujuh",
                "option_image": "storage/optionsImage/option_image_1234567890.png",
                "is_correct": true,
                "order": 1
            },
            {
                "id": 2,
                "option_text": "Delapan",
                "option_image": "storage/optionsImage/option_image_1234567891.png",
                "is_correct": false,
                "order": 2
            }
        ],
        "match_pairs": [],
        "essay_answers": []
    }
}
```

---

### 3. Mendapatkan Detail Lengkap Soal (termasuk Jawaban Siswa)

**Endpoint:**
```http
GET /api/admin/questions/{id}/detail
```

**Akses:** Admin, Guru

Mengembalikan detail soal beserta seluruh jawaban siswa yang telah dikumpulkan.

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Question details retrieved successfully",
    "data": {
        "id": 1,
        "quiz": { "id": 1, "title": "Kuis Bilangan Bulat #1" },
        "type": "essay",
        "question_text": "Berapa hasil dari 7 + 8?",
        "question_image": null,
        "order": 1,
        "score": 10,
        "student_answers": [
            {
                "id": 10,
                "student": { "id": 3, "name": "Andi Pratama" },
                "answer_text": "15",
                "is_correct": true,
                "score": 10,
                "submitted_at": "2025-05-20T08:30:00.000000Z"
            }
        ]
    }
}
```

---

### 4. Mendapatkan Jawaban Siswa per Soal

**Endpoint:**
```http
GET /api/admin/questions/{id}/student-answers
```

**Akses:** Admin, Guru

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Student answers retrieved successfully",
    "data": {
        "id": 1,
        "question_text": "Berapa hasil dari 7 + 8?",
        "student_answers": [
            {
                "id": 10,
                "student_id": 3,
                "answer_text": "15",
                "is_correct": true,
                "score": 10
            }
        ]
    }
}
```

---

### 5. Mendapatkan Semua Soal Beserta Jawaban Siswa

**Endpoint:**
```http
GET /api/admin/questions/all-student-answers
```

**Akses:** Admin, Guru

---

### 6. Membuat Soal Baru

**Endpoint:**
```http
POST /api/admin/questions
```

**Akses:** Admin, Guru

Mendukung pembuatan **satu soal** atau **beberapa soal sekaligus** dalam satu request.

#### 6a. Soal Tipe `essay`

```
quiz_id       = 1
type          = essay
question_text = Berapa hasil dari 7 + 8?
order         = 1
score         = 10
option_text   = 15
```

> **Catatan `option_text` untuk essay:** Dukung beberapa jawaban benar dengan memisahkan menggunakan `|`.
> Contoh: `option_text = 15|lima belas`

---

#### 6b. Soal Tipe `guess_image`

```
quiz_id        = 1
type           = guess_image
question_image = [file gambar: jpeg/png/jpg/gif/svg, maks 2MB]
order          = 2
score          = 10
option_text    = kucing|kuceng
```

> Siswa harus menebak nama/jawaban dari gambar yang ditampilkan.

---

#### 6c. Soal Tipe `choice_image`

```
quiz_id                = 1
type                   = choice_image
question_text          = Pilih jawaban yang benar!
order                  = 3
score                  = 10
options[0][option_text]  = Pilihan A
options[0][option_image] = [file gambar — opsional]
options[0][is_correct]   = true
options[0][order]        = 1
options[1][option_text]  = Pilihan B
options[1][option_image] = [file gambar — opsional]
options[1][is_correct]   = false
options[1][order]        = 2
```

---

#### 6d. Soal Tipe `match_image`

```
quiz_id                      = 1
type                         = match_image
question_text                = Pasangkan dengan benar!
order                        = 4
score                        = 10
match_pairs[0][left_text]    = Satu
match_pairs[0][left_image]   = [file gambar — opsional]
match_pairs[0][right_text]   = 1
match_pairs[0][right_image]  = [file gambar — opsional]
match_pairs[0][pair_order]   = 1
match_pairs[1][left_text]    = Dua
match_pairs[1][right_text]   = 2
match_pairs[1][pair_order]   = 2
```

---

#### Membuat Beberapa Soal Sekaligus (Batch)

Kirimkan array soal sebagai JSON body (gunakan `Content-Type: application/json`):

```json
[
    {
        "quiz_id": 1,
        "type": "essay",
        "question_text": "Berapa hasil dari 7 + 8?",
        "order": 1,
        "score": 10,
        "option_text": "15"
    },
    {
        "quiz_id": 1,
        "type": "essay",
        "question_text": "Berapa hasil dari 10 - 3?",
        "order": 2,
        "score": 10,
        "option_text": "7|tujuh"
    }
]
```

**Response Sukses (201 Created):**
```json
{
    "status": true,
    "message": "Question created successfully with all relations",
    "data": {
        "id": 5,
        "quiz_id": 1,
        "type": "essay",
        "question_text": "Berapa hasil dari 7 + 8?",
        "question_image": null,
        "order": 1,
        "score": 10,
        "options": [],
        "match_pairs": [],
        "essay_answers": [
            { "id": 1, "option_text": "15" }
        ]
    }
}
```

---

### 7. Mengupdate Soal

**Endpoint:**
```http
PUT /api/admin/questions/{id}
```

**Akses:** Admin, Guru

Field yang dapat diupdate sama dengan saat membuat soal. Untuk opsi dan match pair:
- Sertakan `id` untuk **memperbarui** opsi/pair yang sudah ada
- Tanpa `id` untuk **membuat** opsi/pair baru
- Opsi/pair lama yang tidak disertakan akan **dihapus** secara otomatis

> Gambar lama akan dihapus dari storage jika gambar baru diunggah.

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Question updated successfully with all relations",
    "data": { ... }
}
```

---

### 8. Menghapus Soal

**Endpoint:**
```http
DELETE /api/admin/questions/{id}
```

**Akses:** Admin, Guru

Menghapus soal beserta seluruh relasinya (opsi, match pair, essay answer, gambar di storage).

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Question and its relations deleted successfully",
    "data": null
}
```

---

## Struktur Data Soal

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| `id` | integer | ID unik soal |
| `quiz_id` | integer | ID kuis yang memiliki soal ini |
| `type` | string | Tipe soal: `essay`, `choice_image`, `guess_image`, `match_image` |
| `question_text` | text | Teks pertanyaan (opsional jika ada gambar) |
| `question_image` | string | Path ke gambar pertanyaan (untuk `guess_image`) |
| `order` | integer | Urutan soal dalam kuis (mulai dari 0) |
| `score` | integer | Nilai maksimal untuk soal ini |
| `created_at` | datetime | Waktu soal dibuat |
| `updated_at` | datetime | Waktu soal terakhir diperbarui |

### Struktur Opsi (untuk `choice_image`)

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| `id` | integer | ID unik opsi |
| `question_id` | integer | ID soal |
| `option_text` | string | Teks opsi jawaban |
| `option_image` | string | Path ke gambar opsi |
| `is_correct` | boolean | `true` jika ini jawaban yang benar |
| `order` | integer | Urutan opsi |

### Struktur Match Pair (untuk `match_image`)

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| `id` | integer | ID unik pasangan |
| `question_id` | integer | ID soal |
| `left_text` | string | Teks sisi kiri |
| `left_image` | string | Path gambar sisi kiri |
| `right_text` | string | Teks sisi kanan (jawaban yang sesuai) |
| `right_image` | string | Path gambar sisi kanan |
| `pair_order` | integer | Urutan pasangan |

### Struktur Essay Answer (untuk `essay` dan `guess_image`)

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| `id` | integer | ID unik kunci jawaban |
| `question_id` | integer | ID soal |
| `option_text` | string | Kunci jawaban (disimpan dalam huruf kecil) |

---

## Mekanisme Penilaian per Tipe Soal

| Tipe | Cara Menilai |
|------|-------------|
| `essay` | Exact match (case-insensitive, toleran tanda baca) → jika tidak cocok, dilanjutkan ke **AI Gemini** (partial score 0–100%) |
| `guess_image` | Sama seperti `essay` — exact match lalu AI |
| `choice_image` | Cek field `is_correct` pada opsi yang dipilih siswa |
| `match_image` | Proporsi pasangan yang benar dari total pasangan (partial score) |

---

## Catatan Penting

- Gambar soal, opsi, dan match pair disimpan di `public/storage/` dan dapat diakses via URL.
- Untuk soal `essay` dan `guess_image`, kunci jawaban disimpan dalam **huruf kecil** agar perbandingan case-insensitive berfungsi dengan baik.
- Tipe soal **tidak dapat diubah** setelah dibuat (harus hapus dan buat ulang).

