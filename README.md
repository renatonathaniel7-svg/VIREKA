# VIREKA

**Visual Integrity for Revenue & Expenditure Knowledge with Awareness**

VIREKA adalah aplikasi manajemen keuangan pribadi berbasis web yang membantu pengguna mencatat pemasukan, pengeluaran, mengelola budget, serta melakukan verifikasi transaksi menggunakan AI.

## ✨ Fitur Utama

- Dashboard Keuangan Interaktif
- Pencatatan Pengeluaran (Expenses)
- Pencatatan Pemasukan (Income)
- Budget Management
- Financial Health Score
- AI Transaction Verification (Google Gemini)
- Upload Bukti Transaksi
- Shadow Balance & Verified Balance
- Analisis Kategori Pengeluaran

## 🛠 Tech Stack

- Laravel 11
- PHP 8.2
- MySQL
- Tailwind CSS
- Chart.js
- Google Gemini API

## 🚀 Instalasi

```bash
git clone https://github.com/renatonathaniel7-svg/VIREKA.git
cd VIREKA

composer install
npm install

cp .env.example .env

php artisan key:generate

php artisan migrate

npm run build

php artisan serve
```

## 🔑 Environment Variables

Tambahkan pada file `.env`:

```env
GEMINI_API_KEY=YOUR_API_KEY
GEMINI_MODEL=gemini-2.5-flash
```

## 📸 AI Verification

VIREKA menggunakan Google Gemini untuk membaca screenshot transaksi dan membandingkannya dengan data yang diinput pengguna untuk meningkatkan integritas data keuangan.

## 👨‍💻 Developer

Renato Nathaniel

Universitas Negeri Semarang
