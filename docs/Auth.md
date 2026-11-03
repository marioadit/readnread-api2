# Dokumentasi API Autentikasi (Auth)

API ini digunakan untuk autentikasi pengguna sistem Sinaumatika, meliputi login, registrasi, dan logout.

## Request Headers

```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer {token}  // Untuk route yang membutuhkan autentikasi
```

---

## Endpoints

### 1. Login

**Endpoint:**
```http
POST /api/auth/login
```

**Akses:** Publik (tidak perlu token)

**Request Body:**
```json
{
    "email": "guru@example.com",
    "password": "password123"
}
```

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Success",
    "data": {
        "user": {
            "id": 1,
            "name": "Budi Santoso",
            "email": "guru@example.com",
            "role": "guru",
            "status": "aktif",
            "created_at": "2025-05-17T10:00:00.000000Z",
            "updated_at": "2025-05-17T10:00:00.000000Z"
        },
        "token": "1|p6IghECGCVRMwAPCo1OptU7SQ0y0ykzd9pUWL4py"
    }
}
```

**Response Error:**

```json
// 422 - Kredensial Salah
{
    "status": false,
    "message": "Invalid credentials"
}
```

---

### 2. Register

**Endpoint:**
```http
POST /api/auth/register
```

**Akses:** Publik (tidak perlu token)

**Request Body:**
```json
{
    "name": "Andi Pratama",
    "email": "andi@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "siswa"
}
```

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|-----------|
| `name` | string | Ya | Nama lengkap pengguna |
| `email` | string | Ya | Email unik, format email valid |
| `password` | string | Ya | Minimal 8 karakter |
| `password_confirmation` | string | Ya | Harus sama dengan `password` |
| `role` | string | Tidak | Nilai: `siswa`, `guru`, `admin`. Default: `siswa` |

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "User registered successfully",
    "data": {
        "name": "Andi Pratama",
        "email": "andi@example.com",
        "role": "siswa"
    }
}
```

**Response Error:**

```json
// 422 - Validasi Gagal
{
    "status": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email has already been taken."],
        "password": ["The password must be at least 8 characters."]
    }
}
```

---

### 3. Logout

**Endpoint:**
```http
DELETE /api/auth/logout
```

**Akses:** Semua pengguna yang sudah login (membutuhkan token)

**Headers:**
```http
Authorization: Bearer {token}
```

**Response Sukses (200 OK):**
```json
{
    "status": true,
    "message": "Success",
    "data": []
}
```

**Response Error:**

```json
// 401 - Tidak Terautentikasi
{
    "status": false,
    "message": "Unauthenticated."
}
```

---

## Struktur Data Pengguna (User)

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| `id` | integer | ID unik pengguna |
| `name` | string | Nama lengkap pengguna |
| `email` | string | Alamat email (unik) |
| `role` | enum | Peran: `siswa`, `guru`, `admin` |
| `status` | enum | Status akun: `aktif`, `lulus` |
| `created_at` | datetime | Waktu akun dibuat |
| `updated_at` | datetime | Waktu akun terakhir diperbarui |

---

## Catatan Penting

### Token Autentikasi
- Token menggunakan **Laravel Sanctum** (Bearer Token)
- Token **expire dalam 1 hari** (timezone: Asia/Jakarta / WIB / GMT+7)
- Setiap login baru akan menghapus semua token lama milik pengguna tersebut
- Token akan otomatis tidak valid setelah logout atau kadaluarsa
- Middleware `check.token.expiration` memvalidasi token di setiap request

### Peran Pengguna (Role)
| Role | Keterangan |
|------|-----------|
| `siswa` | Siswa — dapat mengikuti kuis dan melihat nilai sendiri |
| `guru` | Guru — dapat mengelola kelas, pelajaran, soal, dan kuis |
| `admin` | Admin — akses penuh ke seluruh sistem |

### Format Response Standar
Semua response mengikuti format berikut:
```json
{
    "status": true | false,
    "message": "Pesan deskriptif",
    "data": { ... } | [ ... ] | null,
    "errors": { ... } | null
}
```

### HTTP Status Code
| Kode | Keterangan |
|------|-----------|
| 200 | Sukses |
| 201 | Berhasil dibuat |
| 400 | Bad Request |
| 401 | Tidak terautentikasi |
| 403 | Akses ditolak (role tidak sesuai) |
| 404 | Data tidak ditemukan |
| 422 | Validasi gagal |
| 500 | Server error |
