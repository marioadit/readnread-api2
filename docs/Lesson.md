# Dokumentasi API Pelajaran (Lesson)

API ini digunakan untuk mengelola pelajaran dalam sistem Sinaumatika. Setiap pelajaran terhubung ke sebuah kelas dan dapat memiliki satu atau beberapa kuis.

## Request Headers

```http
Accept: application/json
Content-Type: multipart/form-data  // Untuk upload thumbnail
Authorization: Bearer {token}
```

> **Catatan:** Gunakan `multipart/form-data` saat mengirim thumbnail. Gunakan `application/json` untuk endpoint yang tidak mengandung file.

---

## Endpoints

### 1. Mendapatkan Semua Pelajaran

**Endpoint:**
```http
GET /api/admin/lessons
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
            "class_id": 1,
            "title": "Pengenalan Bilangan Bulat",
            "description": "Belajar mengenal dan memahami bilangan bulat",
            "content": "Bilangan bulat adalah...",
            "thumbnail": "storage/thumbnails/thumbnail_1234567890.jpg",
            "created_at": "2025-05-17T10:00:00.000000Z",
            "updated_at": "2025-05-17T10:00:00.000000Z",
            "class": {
                "id": 1,
                "name": "Kelas 5 A",
                "level": "kelas 5",
                "teacher_id": 2,
                "semester": "genap",
                "academic_year": "2025/2026"
            }
        }
    ]
}
```

> **Catatan:** Data diurutkan berdasarkan `class_id` (asc) lalu `id` (asc).

---

### 2. Mendapatkan Detail Pelajaran

**Endpoint:**
```http
GET /api/admin/lessons/{id}
```
```http
GET /api/users/lessons/{id}
```

**Akses:** Admin, Guru (`/api/admin/lessons/{id}`) | Siswa (`/api/users/lessons/{id}`)

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Success",
    "data": {
        "id": 1,
        "class_id": 1,
        "title": "Pengenalan Bilangan Bulat",
        "description": "Belajar mengenal dan memahami bilangan bulat",
        "content": "Bilangan bulat adalah...",
        "thumbnail": "storage/thumbnails/thumbnail_1234567890.jpg",
        "created_at": "2025-05-17T10:00:00.000000Z",
        "updated_at": "2025-05-17T10:00:00.000000Z",
        "class": {
            "id": 1,
            "name": "Kelas 5 A",
            "level": "kelas 5",
            "teacher_id": 2,
            "semester": "genap",
            "academic_year": "2025/2026"
        },
        "quizzes": [
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
}
```

---

### 3. Membuat Pelajaran Baru

**Endpoint:**
```http
POST /api/admin/lessons
```

**Akses:** Admin, Guru

**Request Body** (`multipart/form-data`):
```
class_id    = 1
title       = Pengenalan Bilangan Bulat
description = Belajar mengenal dan memahami bilangan bulat
content     = Bilangan bulat adalah bilangan yang tidak memiliki pecahan...
thumbnail   = [file gambar: jpeg/png/jpg/gif/svg, maks 2MB]
```

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|-----------|
| `class_id` | integer | Ya | ID kelas, harus ada di tabel `classes` |
| `title` | string | Ya | Judul pelajaran, maks 255 karakter |
| `description` | string | Tidak | Deskripsi singkat pelajaran |
| `content` | string | Tidak | Konten lengkap pelajaran |
| `thumbnail` | file | Tidak | Gambar thumbnail (jpeg, png, jpg, gif, svg), maks 2MB |

**Response Sukses (201 Created):**
```json
{
    "status": true,
    "message": "Pelajaran berhasil dibuat",
    "data": {
        "id": 2,
        "class_id": 1,
        "title": "Pengenalan Bilangan Bulat",
        "description": "Belajar mengenal dan memahami bilangan bulat",
        "content": "Bilangan bulat adalah bilangan yang tidak memiliki pecahan...",
        "thumbnail": "storage/thumbnails/thumbnail_1747468800.jpg",
        "created_at": "2025-05-17T10:30:00.000000Z",
        "updated_at": "2025-05-17T10:30:00.000000Z"
    }
}
```

**Response Error:**
```json
// 400 - Validasi Gagal
{
    "status": false,
    "message": "Validasi gagal",
    "errors": {
        "class_id": ["The selected class id is invalid."],
        "title": ["The title field is required."]
    }
}
```

---

### 4. Mengupdate Pelajaran

**Endpoint:**
```http
PUT /api/admin/lessons/{id}
```

**Akses:** Admin, Guru

**Request Body** (`multipart/form-data`):
```
class_id    = 1
title       = Pengenalan Bilangan Bulat (Update)
description = Deskripsi baru...
content     = Konten baru...
thumbnail   = [file gambar baru — opsional]
```

> **Catatan:** Jika `thumbnail` baru dikirim, file lama akan dihapus secara otomatis dari storage.

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Pelajaran berhasil diperbarui",
    "data": {
        "id": 2,
        "class_id": 1,
        "title": "Pengenalan Bilangan Bulat (Update)",
        "description": "Deskripsi baru...",
        "content": "Konten baru...",
        "thumbnail": "storage/thumbnails/thumbnail_1747469000.jpg",
        "created_at": "2025-05-17T10:30:00.000000Z",
        "updated_at": "2025-05-17T10:35:00.000000Z"
    }
}
```

---

### 5. Menghapus Pelajaran

**Endpoint:**
```http
DELETE /api/admin/lessons/{id}
```

**Akses:** Admin, Guru

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Pelajaran berhasil dihapus",
    "data": null
}
```

---

### 6. Mendapatkan Pelajaran dalam Kelas

**Endpoint:**
```http
GET /api/admin/classes/{id}/lessons
```
```http
GET /api/users/classes/{id}/lessons
```

**Akses:** Admin, Guru (`/api/admin/classes/{id}/lessons`) | Siswa (`/api/users/classes/{id}/lessons`)

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Pelajaran ditemukan",
    "data": [
        {
            "id": 1,
            "class_id": 1,
            "title": "Pengenalan Bilangan Bulat",
            "description": "Belajar mengenal dan memahami bilangan bulat",
            "content": "Bilangan bulat adalah...",
            "thumbnail": "storage/thumbnails/thumbnail_1234567890.jpg",
            "created_at": "2025-05-17T10:00:00.000000Z",
            "updated_at": "2025-05-17T10:00:00.000000Z"
        }
    ]
}
```

**Response Error (Kelas Tidak Ditemukan):**
```json
{
    "status": false,
    "message": "Kelas tidak ditemukan",
    "data": null
}
```

---

### 7. Mendapatkan Pelajaran beserta Kuis-nya

**Endpoint:**
```http
GET /api/admin/lessons/{id}/quiz
```

**Akses:** Admin, Guru

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Success",
    "data": {
        "id": 1,
        "class_id": 1,
        "title": "Pengenalan Bilangan Bulat",
        "description": "Belajar mengenal dan memahami bilangan bulat",
        "content": "Bilangan bulat adalah...",
        "thumbnail": "storage/thumbnails/thumbnail_1234567890.jpg",
        "quizzes": [
            {
                "id": 1,
                "lesson_id": 1,
                "title": "Kuis Bilangan Bulat #1",
                "is_active": true,
                "start_time": "2025-05-20T08:00:00.000000Z",
                "end_time": "2025-05-20T09:00:00.000000Z"
            }
        ]
    }
}
```

---

### 8. Mendapatkan Pelajaran Milik Siswa (Login)

**Endpoint:**
```http
GET /api/users/lessons
```

**Akses:** Siswa

Mengembalikan semua pelajaran yang ada di kelas tempat siswa terdaftar.

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Success",
    "data": [
        {
            "id": 1,
            "class_id": 1,
            "title": "Pengenalan Bilangan Bulat",
            "description": "Belajar mengenal dan memahami bilangan bulat",
            "thumbnail": "storage/thumbnails/thumbnail_1234567890.jpg"
        }
    ]
}
```

---

## Struktur Data Pelajaran

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| `id` | integer | ID unik pelajaran |
| `class_id` | integer | ID kelas yang memiliki pelajaran ini |
| `title` | string | Judul pelajaran |
| `description` | text | Deskripsi singkat pelajaran |
| `content` | text | Konten lengkap pelajaran (bisa berisi HTML atau teks panjang) |
| `thumbnail` | string | Path relatif ke file gambar thumbnail di server |
| `created_at` | datetime | Waktu pelajaran dibuat |
| `updated_at` | datetime | Waktu pelajaran terakhir diperbarui |

---

## Catatan Penting

- **Thumbnail** disimpan di `storage/thumbnails/` dan dapat diakses melalui URL publik.
- Satu pelajaran dapat memiliki **banyak kuis** (`quizzes`).
- Siswa hanya dapat mengakses pelajaran yang ada di kelas tempat mereka **terdaftar**.
