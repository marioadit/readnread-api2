# Dokumentasi API Kelas (Class)

API ini digunakan untuk mengelola kelas dalam sistem LMS untuk anak SD.

## Endpoints

### 1. Mendapatkan Semua Kelas

**Endpoint:** `GET /api/admin/classes`

**Akses:** Admin

**Response:**
```json
{
  "status": true,
  "message": "Success",
  "data": [
    {
      "id": 1,
      "name": "Kelas 1A",
      "level": "kelas 1",
      "teacher_id": 2,
      "created_at": "2023-05-16T12:00:00.000000Z",
      "updated_at": "2023-05-16T12:00:00.000000Z",
      "teacher": {
        "id": 2,
        "name": "Guru SD",
        "email": "guru@example.com",
        "role": "guru"
      }
    }
  ]
}
```

### 2. Mendapatkan Detail Kelas

**Endpoint:** `GET /api/admin/classes/{id}`

**Akses:** Admin, Guru

**Response:**
```json
{
  "status": true,
  "message": "Success",
  "data": {
    "id": 1,
    "name": "Kelas 1A",
    "level": "kelas 1",
    "teacher_id": 2,
    "created_at": "2023-05-16T12:00:00.000000Z",
    "updated_at": "2023-05-16T12:00:00.000000Z",
    "teacher": {
      "id": 2,
      "name": "Guru SD",
      "email": "guru@example.com",
      "role": "guru"
    },
    "students": [
      {
        "id": 3,
        "name": "Siswa SD",
        "email": "siswa@example.com",
        "role": "siswa",
        "pivot": {
          "class_id": 1,
          "student_id": 3,
          "created_at": "2023-05-16T12:00:00.000000Z",
          "updated_at": "2023-05-16T12:00:00.000000Z"
        }
      }
    ]
  }
}
```

### 3. Membuat Kelas Baru

**Endpoint:** `POST /api/admin/classes`

**Akses:** Admin

**Request Body:**
```json
{
  "name": "Kelas 1B",
  "level": "kelas 1",
  "teacher_id": 2
}
```

**Response:**
```json
{
  "status": true,
  "message": "Kelas berhasil dibuat",
  "data": {
    "id": 2,
    "name": "Kelas 1B",
    "level": "kelas 1",
    "teacher_id": 2,
    "created_at": "2023-05-16T12:00:00.000000Z",
    "updated_at": "2023-05-16T12:00:00.000000Z"
  }
}
```

### 4. Mengupdate Kelas

**Endpoint:** `PUT /api/admin/classes/{id}`

**Akses:** Admin

**Request Body:**
```json
{
  "name": "Kelas 1B Update",
  "level": "kelas 1",
  "teacher_id": 2
}
```

**Response:**
```json
{
  "status": true,
  "message": "Kelas berhasil diperbarui",
  "data": {
    "id": 2,
    "name": "Kelas 1B Update",
    "level": "kelas 1",
    "teacher_id": 2,
    "created_at": "2023-05-16T12:00:00.000000Z",
    "updated_at": "2023-05-16T12:05:00.000000Z"
  }
}
```

### 5. Menghapus Kelas

**Endpoint:** `DELETE /api/admin/classes/{id}`

**Akses:** Admin

**Response:**
```json
{
  "status": true,
  "message": "Kelas berhasil dihapus"
}
```

### 6. Mendapatkan Kelas yang Diajarkan Guru

**Endpoint:** `GET /api/classes/my-classes`

**Akses:** Guru

**Response:**
```json
{
  "status": true,
  "message": "Success",
  "data": [
    {
      "id": 1,
      "name": "Kelas 1A",
      "level": "kelas 1",
      "teacher_id": 2,
      "created_at": "2023-05-16T12:00:00.000000Z",
      "updated_at": "2023-05-16T12:00:00.000000Z"
    }
  ]
}
```

### 7. Mendapatkan Siswa dalam Kelas

**Endpoint:** `GET /api/admin/classes/{id}/students`

**Akses:** Admin, Guru

**Response:**
```json
{
  "status": true,
  "message": "Success",
  "data": [
    {
      "id": 3,
      "name": "Siswa SD",
      "email": "siswa@example.com",
      "role": "siswa",
      "pivot": {
        "class_id": 1,
        "student_id": 3,
        "created_at": "2023-05-16T12:00:00.000000Z",
        "updated_at": "2023-05-16T12:00:00.000000Z"
      }
    }
  ]
}
```

### 8. Menambahkan Siswa ke Kelas

**Endpoint:** `POST /api/admin/classes/{id}/students`

**Akses:** Admin

**Request Body:**
```json
{
  "student_id": 4
}
```

**Response:**
```json
{
  "status": true,
  "message": "Siswa berhasil ditambahkan ke kelas"
}
```

### 9. Menghapus Siswa dari Kelas

**Endpoint:** `DELETE /api/admin/classes/{id}/students`

**Akses:** Admin

**Request Body:**
```json
{
  "student_id": 4
}
```

**Response:**
```json
{
  "status": true,
  "message": "Siswa berhasil dihapus dari kelas"
}
```

## Struktur Data Kelas

| Field       | Tipe    | Deskripsi                                       |
|-------------|---------|------------------------------------------------|
| id          | integer | ID unik kelas                                   |
| name        | string  | Nama kelas                                      |
| level       | enum    | Level kelas (kelas 1, kelas 2, dst)             |
| teacher_id  | integer | ID guru yang mengajar kelas ini                 |
| created_at  | datetime| Tanggal dan waktu kelas dibuat                  |
| updated_at  | datetime| Tanggal dan waktu kelas terakhir diperbarui     |

## Level Kelas

Berikut adalah nilai yang valid untuk field `level`:
- kelas 1
- kelas 2
- kelas 3
- kelas 4
- kelas 5
- kelas 6
