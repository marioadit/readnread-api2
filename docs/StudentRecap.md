# Dokumentasi API Rekap Nilai Siswa (Student Score Recap)

API ini digunakan untuk menampilkan rekapitulasi nilai siswa per jenjang kelas (Kelas 1 s.d Kelas 6) dan semester (`gasal`/`genap`). Nilai dihitung secara otomatis setiap kali ada transaksi kuis pada riwayat jawaban siswa (`answers_history`).

---

## Mekanisme Auto-Update

Rekap nilai diperbarui secara **otomatis** melalui dua jalur:

1. **Model Event** — Setiap kali record di tabel `answers_history` disimpan (`saved`) atau dihapus (`deleted`), model `AnswersHistory` otomatis memicu kalkulasi ulang rekap nilai untuk siswa dan kelas/semester yang bersangkutan.

2. **Manual Sync** — Tersedia endpoint API dan artisan command untuk sinkronisasi ulang secara manual jika diperlukan.

---

## Fitur Utama & Kinerja

- **Materialized Recap Table** — Menggunakan tabel agregasi `student_score_recaps` sehingga pembacaan data di frontend sangat cepat (< 50ms) tanpa membebani database.
- **Best Score Policy** — Nilai yang tersimpan adalah **nilai terbaik** yang pernah dicapai siswa untuk kombinasi kelas + semester tertentu.
- **Struktur Matriks Siap Pakai** — Response API menyediakan format `grades` (nested) dan `flat_grades` (flat key) yang langsung dapat dipetakan ke tabel di frontend.

---

## Endpoints

### 1. Mendapatkan Rekap Nilai Seluruh Siswa (Matriks)

**Endpoint:**
```http
GET /api/admin/student-recaps
```

**Akses:** Admin, Guru

**Query Parameters:**

| Parameter | Tipe | Deskripsi |
|-----------|------|-----------|
| `class_id` | integer | Filter siswa yang terdaftar di kelas tertentu |
| `search` | string | Cari berdasarkan nama atau email siswa |
| `status` | string | Filter status siswa (`aktif`, `lulus`) |
| `per_page` | integer | Jumlah data per halaman (default: `15`) |
| `page` | integer | Nomor halaman |
| `all` | boolean | Set `true` untuk mengambil seluruh data tanpa pagination (untuk export Excel/PDF) |

**Urutan Data (Default Ordering):**

Data diurutkan secara hierarkis:
1. **Jenjang Kelas Tertinggi Dahulu**: Kelas 6 → Kelas 5 → ... → Kelas 1 (siswa tanpa kelas di bawah)
2. **Nama Kelas (A → Z)**: contoh: Kelas 6 A lalu Kelas 6 B
3. **Nama Siswa (A → Z)**: urutan alfabet

**Response Sukses (200 OK):**
```json
{
    "status": 200,
    "message": "Data rekap nilai siswa berhasil diambil",
    "data": {
        "students": [
            {
                "student_id": 3,
                "student_name": "Andi Pratama",
                "student_email": "andi@example.com",
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
            "last_page": 3,
            "per_page": 15,
            "total": 42
        }
    }
}
```

> **Catatan:** Nilai `null` berarti belum ada kuis yang dikerjakan untuk kelas dan semester tersebut. Nilai berupa angka desimal (contoh: `77.78`) adalah rata-rata persentase skor dari semua kuis yang pernah dikerjakan.

---

### 2. Mendapatkan Rekap Nilai Satu Siswa

**Endpoint:**
```http
GET /api/admin/student-recaps/{studentId}
```

**Akses:** Admin, Guru

**Response Sukses (200 OK):**
```json
{
    "status": 200,
    "message": "Data rekap nilai siswa berhasil diambil",
    "data": {
        "student_id": 3,
        "student_name": "Andi Pratama",
        "student_email": "andi@example.com",
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

### 3. Mendapatkan Rekap Nilai Sendiri (untuk Siswa Login)

**Endpoint:**
```http
GET /api/users/student-recaps/my-recap
```

**Akses:** Siswa

Response sama seperti endpoint detail siswa di atas, namun data diambil berdasarkan token siswa yang sedang login.

---

### 4. Sinkronisasi Ulang Data Rekap (Manual Trigger)

**Endpoint:**
```http
POST /api/admin/student-recaps/sync
```

**Akses:** Admin, Guru

Memaksa kalkulasi ulang seluruh rekap nilai berdasarkan data `answers_history` yang ada.

**Response Sukses (200 OK):**
```json
{
    "status": 200,
    "message": "Sinkronisasi rekap nilai berhasil",
    "data": {
        "processed_records": 42
    }
}
```

---

## Artisan CLI Command

Untuk melakukan sinkronisasi rekap nilai melalui terminal:

```bash
php artisan recap:sync
```

---

## Struktur Data Rekap Nilai

### Tabel `student_score_recaps`

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| `id` | integer | ID unik rekap |
| `student_id` | integer | ID siswa |
| `class_level` | string | Level kelas (contoh: `kelas 5`) |
| `semester` | string | Semester: `gasal` atau `genap` |
| `average_score` | float | Nilai rata-rata persentase kuis (0–100) |
| `total_quizzes` | integer | Jumlah kuis yang sudah dikerjakan |
| `created_at` | datetime | Waktu record dibuat |
| `updated_at` | datetime | Waktu record terakhir diperbarui |

---

## Catatan Penting

- Nilai pada `grades` dan `flat_grades` merupakan **rata-rata `percentage_score`** dari semua entry di `answers_history` untuk kombinasi `student_id` + `class_level` + `semester` tertentu.
- Angka rekap diperbarui **secara real-time** setiap kali siswa submit kuis, tanpa perlu sinkronisasi manual.
- Endpoint sync manual berguna untuk **migrasi data lama** atau **pemulihan data** jika ada inkonsistensi.
