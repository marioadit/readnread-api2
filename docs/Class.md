# Dokumentasi API Kelas (Class)

API ini digunakan untuk mengelola kelas dalam sistem Sinaumatika, termasuk manajemen siswa di dalam kelas.

## Request Headers

```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer {token}
```

---

## Endpoints

### 1. Mendapatkan Semua Kelas

**Endpoint:**
```http
GET /api/admin/classes
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
            "name": "Kelas 5 A",
            "level": "kelas 5",
            "teacher_id": 2,
            "semester": "genap",
            "academic_year": "2025/2026",
            "created_at": "2025-05-17T10:00:00.000000Z",
            "updated_at": "2025-05-17T10:00:00.000000Z",
            "teacher": {
                "id": 2,
                "name": "Budi Santoso",
                "email": "budi@example.com",
                "role": "guru"
            }
        }
    ]
}
```

> **Catatan:** Data diurutkan berdasarkan `level` (asc) lalu `name` (asc).

---

### 2. Mendapatkan Detail Kelas

**Endpoint:**
```http
GET /api/admin/classes/{id}
```

**Akses:** Admin, Guru, Siswa (`/api/users/classes/{id}`)

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Success",
    "data": {
        "id": 1,
        "name": "Kelas 5 A",
        "level": "kelas 5",
        "teacher_id": 2,
        "semester": "genap",
        "academic_year": "2025/2026",
        "created_at": "2025-05-17T10:00:00.000000Z",
        "updated_at": "2025-05-17T10:00:00.000000Z",
        "teacher": {
            "id": 2,
            "name": "Budi Santoso",
            "email": "budi@example.com",
            "role": "guru"
        },
        "students": [
            {
                "id": 3,
                "name": "Andi Pratama",
                "email": "andi@example.com",
                "role": "siswa"
            }
        ]
    }
}
```

---

### 3. Membuat Kelas Baru

**Endpoint:**
```http
POST /api/admin/classes
```

**Akses:** Admin, Guru

**Request Body:**
```json
{
    "name": "Kelas 5 B",
    "level": "kelas 5",
    "teacher_id": 2,
    "semester": "gasal",
    "academic_year": "2026/2027"
}
```

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|-----------|
| `name` | string | Ya | Nama kelas, maks 255 karakter |
| `level` | string | Ya | Level kelas (lihat nilai valid di bawah) |
| `teacher_id` | integer | Ya | ID guru yang mengajar, harus ada di tabel `users` |
| `semester` | string | Ya | `gasal` atau `genap` |
| `academic_year` | string | Ya | Format `YYYY/YYYY`, maks 9 karakter. Contoh: `2026/2027` |

**Response Sukses (201 Created):**
```json
{
    "status": true,
    "message": "Kelas berhasil dibuat",
    "data": {
        "id": 2,
        "name": "Kelas 5 B",
        "level": "kelas 5",
        "teacher_id": 2,
        "semester": "gasal",
        "academic_year": "2026/2027",
        "created_at": "2025-05-17T10:05:00.000000Z",
        "updated_at": "2025-05-17T10:05:00.000000Z"
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
        "level": ["The selected level is invalid."],
        "semester": ["The selected semester is invalid."]
    }
}
```

---

### 4. Mengupdate Kelas

**Endpoint:**
```http
PUT /api/admin/classes/{id}
```

**Akses:** Admin, Guru

**Request Body:** (semua field bersifat opsional / `sometimes`)
```json
{
    "name": "Kelas 5 B Update",
    "level": "kelas 5",
    "teacher_id": 3,
    "semester": "genap",
    "academic_year": "2026/2027"
}
```

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Kelas berhasil diperbarui",
    "data": {
        "id": 2,
        "name": "Kelas 5 B Update",
        "level": "kelas 5",
        "teacher_id": 3,
        "semester": "genap",
        "academic_year": "2026/2027",
        "created_at": "2025-05-17T10:05:00.000000Z",
        "updated_at": "2025-05-17T10:10:00.000000Z"
    }
}
```

---

### 5. Menghapus Kelas

**Endpoint:**
```http
DELETE /api/admin/classes/{id}
```

**Akses:** Admin, Guru

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Kelas berhasil dihapus",
    "data": null
}
```

---

### 6. Mendapatkan Daftar Siswa dalam Kelas

**Endpoint:**
```http
GET /api/admin/classes/{id}/students
```

**Akses:** Admin, Guru, Siswa (`/api/users/classes/{id}/students`)

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Success",
    "data": [
        {
            "id": 3,
            "name": "Andi Pratama",
            "email": "andi@example.com",
            "role": "siswa",
            "status": "aktif"
        }
    ]
}
```

---

### 7. Menambahkan Siswa ke Kelas

**Endpoint:**
```http
POST /api/admin/classes/{id}/students
```

**Akses:** Admin, Guru

**Request Body:**
```json
{
    "student_id": 5
}
```

> **Catatan:** Jika siswa sudah terdaftar di kelas lain, siswa akan **otomatis dipindahkan** ke kelas baru (detach dari kelas lama, attach ke kelas baru). Satu siswa hanya bisa terdaftar di satu kelas aktif.

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Siswa berhasil ditambahkan ke kelas",
    "data": null
}
```

**Response Error:**
```json
// 400 - Siswa sudah terdaftar
{
    "status": false,
    "message": "Siswa sudah terdaftar dalam kelas ini"
}
```

---

### 8. Menghapus Siswa dari Kelas

**Endpoint:**
```http
DELETE /api/admin/classes/{id}/students
```

**Akses:** Admin, Guru

**Request Body:**
```json
{
    "student_id": 5
}
```

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Siswa berhasil dihapus dari kelas",
    "data": null
}
```

---

### 9. Mendapatkan Pelajaran dalam Kelas

**Endpoint:**
```http
GET /api/admin/classes/{id}/lessons
```

**Akses:** Admin, Guru, Siswa (`/api/users/classes/{id}/lessons`)

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
            "content": "Konten pelajaran...",
            "thumbnail": "storage/thumbnails/thumbnail_1234567890.jpg",
            "created_at": "2025-05-17T10:00:00.000000Z",
            "updated_at": "2025-05-17T10:00:00.000000Z"
        }
    ]
}
```

---

## Struktur Data Kelas

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| `id` | integer | ID unik kelas |
| `name` | string | Nama kelas (contoh: `Kelas 5 A`) |
| `level` | string | Jenjang kelas (lihat nilai valid di bawah) |
| `teacher_id` | integer | ID guru pengampu |
| `semester` | string | Semester: `gasal` atau `genap` |
| `academic_year` | string | Tahun ajaran (format: `YYYY/YYYY`) |
| `created_at` | datetime | Waktu kelas dibuat |
| `updated_at` | datetime | Waktu kelas terakhir diperbarui |

---

## Nilai Valid

### Level Kelas (`level`)
| Nilai | Keterangan |
|-------|-----------|
| `kelas 1` | Kelas 1 SD |
| `kelas 2` | Kelas 2 SD |
| `kelas 3` | Kelas 3 SD |
| `kelas 4` | Kelas 4 SD |
| `kelas 5` | Kelas 5 SD |
| `kelas 6` | Kelas 6 SD |

### Semester (`semester`)
| Nilai | Keterangan |
|-------|-----------|
| `gasal` | Semester 1 / Gasal / Ganjil |
| `genap` | Semester 2 / Genap |
