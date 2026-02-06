# Dokumentasi API Pelajaran (Lesson)

API ini digunakan untuk mengelola pelajaran dalam sistem LMS untuk anak SD.

## Endpoints

### 1. Mendapatkan Semua Pelajaran

**Endpoint:** `GET /api/admin/lessons`

**Akses:** Admin

**Response:**
```json
{
  "status": true,
  "message": "Success",
  "data": [
    {
      "id": 1,
      "class_id": 1,
      "title": "Pengenalan Matematika Dasar",
      "description": "Belajar angka dan operasi dasar matematika",
      "content": "Konten pelajaran matematika...",
      "thumbnail": "matematika.jpg",
      "created_at": "2023-05-16T12:00:00.000000Z",
      "updated_at": "2023-05-16T12:00:00.000000Z",
      "class": {
        "id": 1,
        "name": "Kelas 1A",
        "level": "kelas 1",
        "teacher_id": 2
      }
    }
  ]
}
```

### 2. Mendapatkan Detail Pelajaran

**Endpoint:** `GET /api/admin/lessons/{id}` atau `GET /api/lessons/{id}`

**Akses:** Admin, Guru, Siswa

**Response:**
```json
{
  "status": true,
  "message": "Success",
  "data": {
    "id": 1,
    "class_id": 1,
    "title": "Pengenalan Matematika Dasar",
    "description": "Belajar angka dan operasi dasar matematika",
    "content": "Konten pelajaran matematika...",
    "thumbnail": "matematika.jpg",
    "created_at": "2023-05-16T12:00:00.000000Z",
    "updated_at": "2023-05-16T12:00:00.000000Z",
    "class": {
      "id": 1,
      "name": "Kelas 1A",
      "level": "kelas 1",
      "teacher_id": 2
    },
    "quizzes": [
      {
        "id": 1,
        "lesson_id": 1,
        "title": "Quiz Matematika #1",
        "description": "Quiz untuk menguji pemahaman matematika dasar",
        "start_time": "2023-05-17T10:00:00.000000Z",
        "end_time": "2023-05-17T11:00:00.000000Z",
        "is_active": true,
        "created_at": "2023-05-16T12:00:00.000000Z",
        "updated_at": "2023-05-16T12:00:00.000000Z"
      }
    ]
  }
}
```

### 3. Membuat Pelajaran Baru

**Endpoint:** `POST /api/admin/lessons` atau `POST /api/lessons`

**Akses:** Admin, Guru

**Request Body:**
```json
{
  "class_id": 1,
  "title": "Belajar Bahasa Indonesia",
  "description": "Mengenal huruf dan kata dasar",
  "content": "Konten pelajaran bahasa indonesia...",
  "thumbnail": "bahasa.jpg"
}
```

**Response:**
```json
{
  "status": true,
  "message": "Pelajaran berhasil dibuat",
  "data": {
    "id": 2,
    "class_id": 1,
    "title": "Belajar Bahasa Indonesia",
    "description": "Mengenal huruf dan kata dasar",
    "content": "Konten pelajaran bahasa indonesia...",
    "thumbnail": "bahasa.jpg",
    "created_at": "2023-05-16T12:30:00.000000Z",
    "updated_at": "2023-05-16T12:30:00.000000Z"
  }
}
```

### 4. Mengupdate Pelajaran

**Endpoint:** `PUT /api/admin/lessons/{id}` atau `PUT /api/lessons/{id}`

**Akses:** Admin, Guru

**Request Body:**
```json
{
  "class_id": 1,
  "title": "Belajar Bahasa Indonesia Update",
  "description": "Mengenal huruf dan kata dasar dengan pendekatan baru",
  "content": "Konten pelajaran bahasa indonesia yang telah diperbarui...",
  "thumbnail": "bahasa_update.jpg"
}
```

**Response:**
```json
{
  "status": true,
  "message": "Pelajaran berhasil diperbarui",
  "data": {
    "id": 2,
    "class_id": 1,
    "title": "Belajar Bahasa Indonesia Update",
    "description": "Mengenal huruf dan kata dasar dengan pendekatan baru",
    "content": "Konten pelajaran bahasa indonesia yang telah diperbarui...",
    "thumbnail": "bahasa_update.jpg",
    "created_at": "2023-05-16T12:30:00.000000Z",
    "updated_at": "2023-05-16T12:35:00.000000Z"
  }
}
```

### 5. Menghapus Pelajaran

**Endpoint:** `DELETE /api/admin/lessons/{id}`

**Akses:** Admin

**Response:**
```json
{
  "status": true,
  "message": "Pelajaran berhasil dihapus"
}
```

### 6. Mendapatkan Pelajaran dalam Kelas

**Endpoint:** `GET /api/classes/{id}/lessons`

**Akses:** Admin, Guru, Siswa

**Response:**
```json
{
  "status": true,
  "message": "Success",
  "data": [
    {
      "id": 1,
      "class_id": 1,
      "title": "Pengenalan Matematika Dasar",
      "description": "Belajar angka dan operasi dasar matematika",
      "content": "Konten pelajaran matematika...",
      "thumbnail": "matematika.jpg",
      "created_at": "2023-05-16T12:00:00.000000Z",
      "updated_at": "2023-05-16T12:00:00.000000Z"
    },
    {
      "id": 2,
      "class_id": 1,
      "title": "Belajar Bahasa Indonesia Update",
      "description": "Mengenal huruf dan kata dasar dengan pendekatan baru",
      "content": "Konten pelajaran bahasa indonesia yang telah diperbarui...",
      "thumbnail": "bahasa_update.jpg",
      "created_at": "2023-05-16T12:30:00.000000Z",
      "updated_at": "2023-05-16T12:35:00.000000Z"
    }
  ]
}
```

## Struktur Data Pelajaran

| Field       | Tipe    | Deskripsi                                       |
|-------------|---------|------------------------------------------------|
| id          | integer | ID unik pelajaran                               |
| class_id    | integer | ID kelas yang terkait dengan pelajaran          |
| title       | string  | Judul pelajaran                                 |
| description | text    | Deskripsi singkat pelajaran                     |
| content     | text    | Konten lengkap pelajaran                        |
| thumbnail   | string  | Gambar thumbnail untuk pelajaran                |
| created_at  | datetime| Tanggal dan waktu pelajaran dibuat              |
| updated_at  | datetime| Tanggal dan waktu pelajaran terakhir diperbarui |

## Integrasi dengan Frontend

Untuk integrasi dengan Vue.js, Anda dapat menggunakan contoh kode berikut untuk mengakses API pelajaran:

```javascript
// src/services/LessonService.js
import apiClient from './api';

export default {
  // Mendapatkan semua pelajaran (admin)
  getLessons() {
    return apiClient.get('/admin/lessons');
  },
  
  // Mendapatkan detail pelajaran
  getLesson(id) {
    return apiClient.get(`/lessons/${id}`);
  },
  
  // Membuat pelajaran baru
  createLesson(lesson) {
    return apiClient.post('/lessons', lesson);
  },
  
  // Memperbarui pelajaran
  updateLesson(id, lesson) {
    return apiClient.put(`/lessons/${id}`, lesson);
  },
  
  // Mendapatkan pelajaran dalam kelas
  getLessonsByClass(classId) {
    return apiClient.get(`/classes/${classId}/lessons`);
  }
}
```
