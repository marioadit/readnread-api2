# Dokumentasi API Opsi Jawaban (Option & Essay Answer)

API ini digunakan untuk mengelola opsi jawaban (pilihan) untuk soal tipe `choice_image`, serta kunci jawaban (essay answer) untuk soal tipe `essay` dan `guess_image`.

## Request Headers

```http
Accept: application/json
Content-Type: multipart/form-data  // Untuk upload gambar opsi
Authorization: Bearer {token}
```

---

## Bagian 1 — Opsi Jawaban (Options)

Digunakan untuk soal tipe **`choice_image`**. Setiap soal pilihan ganda memiliki beberapa opsi, di mana tepat satu opsi ditandai sebagai `is_correct = true`.

---

### 1. Mendapatkan Semua Opsi

**Endpoint:**
```http
GET /api/admin/options
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
            "question_id": 2,
            "option_text": "Lima belas",
            "option_image": null,
            "is_correct": true,
            "order": 1,
            "created_at": "2025-05-17T10:00:00.000000Z",
            "updated_at": "2025-05-17T10:00:00.000000Z"
        }
    ]
}
```

---

### 2. Mendapatkan Detail Opsi

**Endpoint:**
```http
GET /api/admin/options/{id}
```

**Akses:** Admin, Guru

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Success",
    "data": {
        "id": 1,
        "question_id": 2,
        "option_text": "Lima belas",
        "option_image": "storage/optionsImage/option_image_1234567890.png",
        "is_correct": true,
        "order": 1
    }
}
```

---

### 3. Membuat Opsi Baru

**Endpoint:**
```http
POST /api/admin/options
```

**Akses:** Admin, Guru

**Request Body** (`multipart/form-data`):
```
question_id  = 2
option_text  = Lima belas
option_image = [file gambar — opsional: jpeg/png/jpg/gif/svg, maks 2MB]
is_correct   = true
order        = 1
```

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|-----------|
| `question_id` | integer | Ya | ID soal tipe `choice_image` |
| `option_text` | string | Tidak | Teks opsi jawaban |
| `option_image` | file | Tidak | Gambar opsi (jpeg, png, jpg, gif, svg), maks 2MB |
| `is_correct` | boolean | Ya | `true` jika opsi ini adalah jawaban benar |
| `order` | integer | Tidak | Urutan tampil opsi |

**Response Sukses (201 Created):**
```json
{
    "status": true,
    "message": "Opsi berhasil dibuat",
    "data": {
        "id": 5,
        "question_id": 2,
        "option_text": "Lima belas",
        "option_image": null,
        "is_correct": true,
        "order": 1,
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
        "question_id": ["The selected question id is invalid."]
    }
}
```

---

### 4. Mengupdate Opsi

**Endpoint:**
```http
PUT /api/admin/options/{id}
```

**Akses:** Admin, Guru

**Request Body** (`multipart/form-data`):
```
option_text  = Lima belas (update)
option_image = [file gambar baru — opsional]
is_correct   = true
order        = 1
```

> Jika gambar baru dikirim, gambar lama akan otomatis dihapus dari storage.

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Opsi berhasil diperbarui",
    "data": {
        "id": 5,
        "option_text": "Lima belas (update)",
        "is_correct": true,
        "order": 1,
        "updated_at": "2025-05-17T11:00:00.000000Z"
    }
}
```

---

### 5. Menghapus Opsi

**Endpoint:**
```http
DELETE /api/admin/options/{id}
```

**Akses:** Admin, Guru

Menghapus opsi beserta file gambarnya (jika ada).

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Opsi berhasil dihapus",
    "data": null
}
```

**Response Error:**
```json
// 404 - Opsi Tidak Ditemukan
{
    "status": false,
    "message": "Opsi tidak ditemukan"
}
```

---

---

## Bagian 2 — Kunci Jawaban Essay (Essay Answers)

Digunakan untuk mendefinisikan kunci jawaban untuk soal tipe **`essay`** dan **`guess_image`**. Setiap soal dapat memiliki lebih dari satu kunci jawaban yang dianggap benar.

> **Perhatian:** Manajemen essay answer melalui endpoint ini untuk operasi individual. Saat membuat/mengupdate soal melalui `/api/admin/questions`, field `option_text` (dengan pemisah `|`) lebih direkomendasikan untuk batch insert.

---

### 6. Mendapatkan Detail Kunci Jawaban Essay

**Endpoint:**
```http
GET /api/admin/options/essay-answers/{id}
```

**Akses:** Admin, Guru

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Success",
    "data": {
        "id": 1,
        "question_id": 1,
        "option_text": "15",
        "created_at": "2025-05-17T10:00:00.000000Z",
        "updated_at": "2025-05-17T10:00:00.000000Z"
    }
}
```

---

### 7. Membuat Kunci Jawaban Essay

**Endpoint:**
```http
POST /api/admin/options/essay-answers
```

**Akses:** Admin, Guru

**Request Body:**
```json
{
    "question_id": 1,
    "option_text": "lima belas"
}
```

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|-----------|
| `question_id` | integer | Ya | ID soal tipe `essay` atau `guess_image` |
| `option_text` | string | Ya | Kunci jawaban (disimpan dalam huruf kecil) |

**Response Sukses (201 Created):**
```json
{
    "status": true,
    "message": "Kunci jawaban essay berhasil dibuat",
    "data": {
        "id": 3,
        "question_id": 1,
        "option_text": "lima belas",
        "created_at": "2025-05-17T10:30:00.000000Z",
        "updated_at": "2025-05-17T10:30:00.000000Z"
    }
}
```

---

### 8. Mengupdate Kunci Jawaban Essay

**Endpoint:**
```http
PUT /api/admin/options/essay-answers/{id}
```

**Akses:** Admin, Guru

**Request Body:**
```json
{
    "option_text": "15"
}
```

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Kunci jawaban essay berhasil diperbarui",
    "data": {
        "id": 3,
        "question_id": 1,
        "option_text": "15",
        "updated_at": "2025-05-17T11:00:00.000000Z"
    }
}
```

---

### 9. Menghapus Kunci Jawaban Essay

**Endpoint:**
```http
DELETE /api/admin/options/essay-answers/{id}
```

**Akses:** Admin, Guru

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Jawaban essay berhasil dihapus",
    "data": null
}
```

**Response Error:**
```json
// 404 - Kunci Jawaban Tidak Ditemukan
{
    "status": false,
    "message": "Kunci jawaban tidak ditemukan"
}
```

---

## Struktur Data Opsi

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| `id` | integer | ID unik opsi |
| `question_id` | integer | ID soal yang memiliki opsi ini |
| `option_text` | string | Teks opsi jawaban |
| `option_image` | string | Path ke gambar opsi di storage |
| `is_correct` | boolean | `true` jika ini adalah jawaban benar |
| `order` | integer | Urutan tampil opsi dalam soal |
| `created_at` | datetime | Waktu opsi dibuat |
| `updated_at` | datetime | Waktu opsi terakhir diperbarui |

## Struktur Data Essay Answer (Kunci Jawaban)

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| `id` | integer | ID unik kunci jawaban |
| `question_id` | integer | ID soal yang memiliki kunci ini |
| `option_text` | string | Teks kunci jawaban (lowercase) |
| `created_at` | datetime | Waktu kunci jawaban dibuat |
| `updated_at` | datetime | Waktu kunci jawaban terakhir diperbarui |

---

## Catatan Penting

- Setiap soal `choice_image` harus memiliki **tepat satu** opsi dengan `is_correct = true`.
- Soal `essay` dan `guess_image` dapat memiliki **banyak kunci jawaban** yang semuanya dianggap benar.
- Kunci jawaban disimpan dalam **huruf kecil** (`strtolower`) untuk memudahkan perbandingan case-insensitive.
- Gambar opsi disimpan di `public/storage/optionsImage/`.

