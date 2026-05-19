# Edulink - Platform Pembelajaran Adaptif & Interaktif

![Laravel](https://img.shields.io/badge/laravel-%23FF2D20.svg?style=for-the-badge&logo=laravel&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/tailwindcss-%2338B2AC.svg?style=for-the-badge&logo=tailwind-css&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/postgresql-%23316192.svg?style=for-the-badge&logo=postgresql&logoColor=white)

**Edulink** adalah platform manajemen pembelajaran (LMS) modern yang dirancang untuk memberikan pengalaman belajar yang terpersonalisasi. Project ini dikembangkan untuk keperluan kompetisi, dengan fokus pada kemudahan akses materi dan integrasi teknologi AI untuk membantu proses belajar siswa.

---

## 🚀 Fitur Utama

* **Pelajaran Berbasis Kelas:** Materi disusun secara sistematis berdasarkan jenjang kelas untuk memudahkan navigasi siswa.
* **Kuis & Latihan Soal:** Fitur evaluasi interaktif untuk menguji pemahaman setelah mempelajari materi.
* **Tanya AI (Grok Integration):** Asisten belajar cerdas yang terintegrasi dengan API Grok untuk menjawab pertanyaan siswa secara real-time.
* **Manajemen Konten:** Dashboard admin untuk pengelolaan materi, soal kuis, dan data pengguna secara efisien.

---

## 🛠️ Tech Stack

* **Framework:** Laravel
* **Frontend:** Tailwind CSS / Blade Templating
* **Database:** PostgreSQL
* **AI Engine:** Grok API (xAI)

---

## 📋 Prasyarat

Sebelum menjalankan project ini, pastikan sistem kamu sudah terinstal:
* PHP >= 8.1
* Composer
* Node.js & NPM
* PostgreSQL Server

---

## ⚙️ Cara Instalasi

1.  **Clone Repository**
    ```bash
    git clone [https://github.com/Davalvauzi/edulink-codex.git](https://github.com/Davalvauzi/edulink-codex.git)
    cd edulink-codex
    ```

2.  **Install Dependencies**
    ```bash
    composer install
    npm install && npm run dev
    ```

3.  **Konfigurasi Environment**
    Salin file `.env.example` menjadi `.env`:
    ```bash
    cp .env.example .env
    ```
    Buka file `.env` dan sesuaikan bagian koneksi **PostgreSQL** & **AI**:

    **Database:**
    ```env
    DB_CONNECTION=pgsql
    DB_HOST=127.0.0.1
    DB_PORT=5432
    DB_DATABASE=nama_database_kamu
    DB_USERNAME=postgres
    DB_PASSWORD=password_kamu
    ```

    **Integrasi AI (Grok):**
    ```env
    GROK_API_KEY=your_grok_api_key_here
    ```

4.  **Generate App Key & Migrate**
    ```bash
    php artisan key:generate
    php artisan migrate --seed
    ```

5.  **Jalankan Aplikasi**
    ```bash
    php artisan serve
    ```
    Aplikasi dapat diakses melalui browser di `http://localhost:8000`.

---

## 🤝 Kontribusi

Jika ingin berkontribusi, silakan lakukan *fork* pada repository ini dan buat *pull request* dengan penjelasan mengenai perubahan yang dilakukan.

---
Developed by [Dafa Alvauzi](https://github.com/Davalvauzi)
