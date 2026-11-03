# Dokumentasi API Pengguna (User)

API ini digunakan untuk mengelola data pengguna dalam sistem Sinaumatika, termasuk manajemen profil, password, dan akses berbasis peran.

## Request Headers

```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer {token}
```

---

## Endpoints — Admin & Guru

### 1. Mendapatkan Semua Pengguna

**Endpoint:**
```http
GET /api/admin/users
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
            "name": "Budi Santoso",
            "email": "budi@example.com",
            "role": "guru",
            "status": "aktif",
            "created_at": "2025-05-17T10:00:00.000000Z",
            "updated_at": "2025-05-17T10:00:00.000000Z"
        }
    ]
}
```

---

### 2. Mendapatkan Pengguna berdasarkan ID

**Endpoint:**
```http
GET /api/admin/users/{id}
```

**Akses:** Admin, Guru

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Success",
    "data": {
        "id": 3,
        "name": "Andi Pratama",
        "email": "andi@example.com",
        "role": "siswa",
        "status": "aktif",
        "created_at": "2025-05-17T10:00:00.000000Z",
        "updated_at": "2025-05-17T10:00:00.000000Z"
    }
}
```

---

### 3. Membuat Pengguna Baru

**Endpoint:**
```http
POST /api/admin/users
```

**Akses:** Admin, Guru

**Request Body:**
```json
{
    "name": "Siti Rahayu",
    "email": "siti@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "siswa"
}
```

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|-----------|
| `name` | string | Ya | Nama lengkap |
| `email` | string | Ya | Email unik |
| `password` | string | Ya | Minimal 8 karakter |
| `password_confirmation` | string | Ya | Harus sama dengan `password` |
| `role` | string | Ya | `siswa`, `guru`, atau `admin` |

**Response Sukses (201 Created):**
```json
{
    "status": true,
    "message": "User created successfully",
    "data": {
        "id": 4,
        "name": "Siti Rahayu",
        "email": "siti@example.com",
        "role": "siswa",
        "status": "aktif",
        "created_at": "2025-05-17T11:00:00.000000Z",
        "updated_at": "2025-05-17T11:00:00.000000Z"
    }
}
```

---

### 4. Mengupdate Pengguna

**Endpoint:**
```http
PUT /api/admin/users/{id}
```

**Akses:** Admin, Guru

**Request Body:**
```json
{
    "name": "Siti Rahayu Updated",
    "email": "siti.updated@example.com"
}
```

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "User updated successfully",
    "data": {
        "id": 4,
        "name": "Siti Rahayu Updated",
        "email": "siti.updated@example.com",
        "role": "siswa",
        "status": "aktif",
        "updated_at": "2025-05-17T11:05:00.000000Z"
    }
}
```

---

### 5. Menghapus Pengguna

**Endpoint:**
```http
DELETE /api/admin/users/{id}
```

**Akses:** Admin, Guru

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "User deleted successfully",
    "data": []
}
```

---

### 6. Mengupdate Profil Pengguna (oleh Admin/Guru)

**Endpoint:**
```http
PUT /api/admin/users/profile/{id}
```

**Akses:** Admin, Guru

**Request Body:**
```json
{
    "name": "Nama Baru",
    "email": "email.baru@example.com"
}
```

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "User updated successfully",
    "data": { ... }
}
```

---

### 7. Mengupdate Password Pengguna (oleh Admin/Guru)

**Endpoint:**
```http
PUT /api/admin/users/password/{id}
```

**Akses:** Admin, Guru

**Request Body:**
```json
{
    "current_password": "password_lama",
    "password": "password_baru123",
    "password_confirmation": "password_baru123"
}
```

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Password updated successfully",
    "data": { ... }
}
```

---

### 8. Reset Password Pengguna

**Endpoint:**
```http
POST /api/admin/users/reset-password/{id}
```

**Akses:** Admin, Guru

Mereset password pengguna ke nilai default.

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "User password reset successfully",
    "data": { ... }
}
```

---

### 9. Mendapatkan Semua Siswa

**Endpoint:**
```http
GET /api/admin/students
```

**Akses:** Admin, Guru

Mengembalikan semua pengguna dengan role `siswa`.

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

### 10. Mendapatkan Siswa Alumni/Lulus

**Endpoint:**
```http
GET /api/admin/alumni
```

**Akses:** Admin

Mengembalikan semua siswa dengan status `lulus`.

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Success",
    "data": [
        {
            "id": 5,
            "name": "Dewi Lestari",
            "email": "dewi@example.com",
            "role": "siswa",
            "status": "lulus"
        }
    ]
}
```

---

### 11. Mendapatkan Semua Guru

**Endpoint:**
```http
GET /api/admin/teachers
```

**Akses:** Admin, Guru

Mengembalikan semua pengguna dengan role `guru`.

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Success",
    "data": [
        {
            "id": 2,
            "name": "Budi Santoso",
            "email": "budi@example.com",
            "role": "guru",
            "status": "aktif"
        }
    ]
}
```

---

### 12. Mendapatkan Jawaban Semua Siswa (untuk Admin)

**Endpoint:**
```http
GET /api/admin/users/all-student-answers/{idstudent}
```

**Akses:** Admin, Guru

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Success",
    "data": [ ... ]
}
```

---

### 13. Mendapatkan Detail Jawaban Siswa (untuk Admin)

**Endpoint:**
```http
GET /api/admin/users/student-answers/{idstudent}/{idanswer}
```

**Akses:** Admin, Guru

---

### 14. Dashboard Admin

**Endpoint:**
```http
GET /api/admin/dashboard/admin
```

**Akses:** Admin

Mengembalikan data statistik keseluruhan sistem.

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Success",
    "data": { ... }
}
```

---

### 15. Dashboard Guru

**Endpoint:**
```http
GET /api/guru/dashboard/teacher
```

**Akses:** Guru

Mengembalikan data ringkasan kelas dan siswa yang diampu oleh guru yang login.

---

---

## Endpoints — Siswa

### 1. Mendapatkan Profil Sendiri

**Endpoint:**
```http
GET /api/users/profile
```

**Akses:** Siswa, Guru, Admin

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Success",
    "data": {
        "id": 3,
        "name": "Andi Pratama",
        "email": "andi@example.com",
        "role": "siswa",
        "status": "aktif",
        "created_at": "2025-05-17T10:00:00.000000Z",
        "updated_at": "2025-05-17T10:00:00.000000Z"
    }
}
```

---

### 2. Mengupdate Profil Sendiri

**Endpoint:**
```http
PUT /api/users/profile/{id}
```

**Akses:** Siswa

**Request Body:**
```json
{
    "name": "Andi Pratama Baru",
    "email": "andi.baru@example.com"
}
```

---

### 3. Mengupdate Password Sendiri

**Endpoint:**
```http
PUT /api/users/password/{id}
```

**Akses:** Siswa

**Request Body:**
```json
{
    "current_password": "password_lama",
    "password": "password_baru123",
    "password_confirmation": "password_baru123"
}
```

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Password updated successfully",
    "data": { ... }
}
```

**Response Error:**
```json
// 400 - Password saat ini salah
{
    "status": false,
    "message": "Current password is incorrect"
}
```

---

### 4. Mendapatkan Kelas Siswa

**Endpoint:**
```http
GET /api/users/classes
```

**Akses:** Siswa

Mengembalikan kelas tempat siswa yang sedang login terdaftar.

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
            "semester": "genap",
            "academic_year": "2025/2026"
        }
    ]
}
```

---

### 5. Mendapatkan Jawaban Sendiri

**Endpoint:**
```http
GET /api/users/student-answers/{id}
```

**Akses:** Siswa

Mendapatkan jawaban siswa berdasarkan ID kuis atau soal tertentu.

---

### 6. Mendapatkan Semua Jawaban Sendiri

**Endpoint:**
```http
GET /api/users/all-student-answers
```

**Akses:** Siswa

Mendapatkan semua jawaban yang pernah dikirim oleh siswa yang login.

---

## Struktur Data Pengguna

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| `id` | integer | ID unik pengguna |
| `name` | string | Nama lengkap pengguna |
| `email` | string | Alamat email (unik) |
| `role` | string | Peran: `siswa`, `guru`, `admin` |
| `status` | string | Status akun: `aktif`, `lulus` |
| `created_at` | datetime | Waktu akun dibuat |
| `updated_at` | datetime | Waktu akun terakhir diperbarui |

---

## Response Error Umum

```json
// 401 - Tidak Terautentikasi
{
    "status": false,
    "message": "Unauthenticated."
}

// 403 - Akses Ditolak
{
    "status": false,
    "message": "Access Denied."
}

// 404 - Data Tidak Ditemukan
{
    "status": false,
    "message": "User not found"
}

// 422 - Validasi Gagal
{
    "status": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email has already been taken."]
    }
}
```
