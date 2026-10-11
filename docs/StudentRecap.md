# Dokumentasi API Rekap Nilai Siswa (Student Score Recap)

API ini digunakan untuk menampilkan rekapitulasi nilai siswa per jenjang kelas (Kelas 1 s.d Kelas 6) dan semester (1 = Gasal/Ganjil, 2 = Genap). Nilai dihitung secara otomatis setiap kali ada transaksi kuis pada riwayat jawaban siswa (`answers_history`).

---

## Fitur Utama & Kinerja
- **Materialized Recap Table**: Menggunakan tabel agregasi `student_score_recaps` sehingga pembacaan data di Frontend sangat cepat (< 50ms) tanpa membebani database.
- **Auto-Update**: Nilai rerata siswa untuk jenjang kelas dan semester yang bersangkutan otomatis diperbarui seketika siswa men-submit kuis.
- **Struktur Matriks Siap Pakai**: Response API menyediakan format `grades` (nested) dan `flat_grades` (flat key) yang langsung dapat dipetakan ke tabel data Frontend.

---

## Endpoints

### 1. Mendapatkan Rekap Nilai Seluruh Siswa (Matriks)

**Endpoint:** `GET /api/admin/student-recaps`

**Akses:** Admin, Guru

**Query Parameters:**
| Parameter | Tipe | Deskripsi |
| :--- | :--- | :--- |
| `class_id` | integer | Filter siswa yang terdaftar di kelas tertentu |
| `search` | string | Cari berdasarkan nama atau email siswa |
| `status` | string | Filter status siswa (`aktif`, `lulus`, dll) |
| `per_page` | integer | Jumlah data per halaman (default: `15`) |
| `page` | integer | Nomor halaman |
| `all` | boolean | Set `true` untuk mengambil seluruh siswa tanpa pagination (misal untuk export Excel/PDF) |

**Urutan Data (Default Ordering):**
Data diurutkan secara hierarkis agar tampilan tabel di Frontend rapi:
1. **Jenjang Kelas Tertinggi Dahulu**: Kelas 6 $\rightarrow$ Kelas 5 $\rightarrow$ Kelas 4 $\rightarrow$ Kelas 3 $\rightarrow$ Kelas 2 $\rightarrow$ Kelas 1 (siswa dengan kelas didahulukan di atas yang tidak memiliki kelas).
2. **Nama Kelas (A $\rightarrow$ Z)**: e.g. Kelas 6 A lalu Kelas 6 B.
3. **Nama Siswa (A $\rightarrow$ Z)**: Urutan alfabet nama siswa.

**Response Sukses (200 OK):**
```json
{
  "status": 200,
  "message": "Data rekap nilai siswa berhasil diambil",
  "data": {
    "students": [
      {
        "student_id": 3,
        "student_name": "Budi galaksi",
        "student_email": "siswa@example.com",
        "student_status": "aktif",
        "current_class": {
          "id": 9,
          "name": "Kelas 5 A",
          "level": "kelas 5",
          "semester": "genap",
          "academic_year": "2026/2027"
        },
        "grades": {
          "kelas_1": { "semester_1": null, "semester_2": null },
          "kelas_2": { "semester_1": null, "semester_2": null },
          "kelas_3": { "semester_1": null, "semester_2": null },
          "kelas_4": { "semester_1": null, "semester_2": null },
          "kelas_5": { "semester_1": null, "semester_2": 77.78 },
          "kelas_6": { "semester_1": null, "semester_2": null }
        },
        "flat_grades": {
          "kelas_1_sem_1": null,
          "kelas_1_sem_2": null,
          "kelas_2_sem_1": null,
          "kelas_2_sem_2": null,
          "kelas_3_sem_1": null,
          "kelas_3_sem_2": null,
          "kelas_4_sem_1": null,
          "kelas_4_sem_2": null,
          "kelas_5_sem_1": null,
          "kelas_5_sem_2": 77.78,
          "kelas_6_sem_1": null,
          "kelas_6_sem_2": null
        }
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 15,
      "total": 1
    }
  }
}
```

---

### 2. Mendapatkan Rekap Nilai Satu Siswa

**Endpoint:** `GET /api/admin/student-recaps/{studentId}`

**Akses:** Admin, Guru

**Response Sukses (200 OK):**
```json
{
  "status": 200,
  "message": "Data rekap nilai siswa berhasil diambil",
  "data": {
    "student_id": 3,
    "student_name": "Budi galaksi",
    "student_email": "siswa@example.com",
    "student_status": "aktif",
    "current_class": {
      "id": 9,
      "name": "Kelas 5 A",
      "level": "kelas 5",
      "semester": "genap",
      "academic_year": "2026/2027"
    },
    "grades": {
      "kelas_1": { "semester_1": null, "semester_2": null },
      "kelas_2": { "semester_1": null, "semester_2": null },
      "kelas_3": { "semester_1": null, "semester_2": null },
      "kelas_4": { "semester_1": null, "semester_2": null },
      "kelas_5": { "semester_1": null, "semester_2": 77.78 },
      "kelas_6": { "semester_1": null, "semester_2": null }
    },
    "flat_grades": {
      "kelas_1_sem_1": null,
      "kelas_1_sem_2": null,
      "kelas_2_sem_1": null,
      "kelas_2_sem_2": null,
      "kelas_3_sem_1": null,
      "kelas_3_sem_2": null,
      "kelas_4_sem_1": null,
      "kelas_4_sem_2": null,
      "kelas_5_sem_1": null,
      "kelas_5_sem_2": 77.78,
      "kelas_6_sem_1": null,
      "kelas_6_sem_2": null
    }
  }
}
```

---

### 3. Mendapatkan Rekap Nilai Siswa Sendiri (Untuk Siswa Login)

**Endpoint:** `GET /api/users/student-recaps/my-recap`

**Akses:** Siswa

**Response:** Sama seperti endpoint detail siswa di atas (berdasarkan token siswa).

---

### 4. Sinkronisasi Ulang Data Rekap (Manual Trigger)

**Endpoint:** `POST /api/admin/student-recaps/sync`

**Akses:** Admin, Guru

**Response:**
```json
{
  "status": 200,
  "message": "Sinkronisasi rekap nilai berhasil",
  "data": {
    "processed_records": 1
  }
}
```

---

## Artisan CLI Command
Untuk melakukan sinkronisasi rekap nilai dari terminal:
```bash
php artisan recap:sync
```
