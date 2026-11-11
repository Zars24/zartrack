# ZarTrack — Container Trucking & Logistics Management System

## 1. Project Overview

ZarTrack adalah aplikasi web terintegrasi untuk perusahaan container trucking dan logistik, mencakup company profile, manajemen operasional, pengiriman container, GPS tracking, dokumen, dan invoice.

**Tujuan:** membangun aplikasi profesional untuk portfolio developer sekaligus berpotensi digunakan dalam operasional bisnis nyata.

## 2. Technology Stack

- Backend: Laravel 10
- Language: PHP 8.1+
- Frontend: Blade, Bootstrap 5, JavaScript
- Database: MySQL / MariaDB
- Maps: Leaflet + OpenStreetMap
- GPS: Browser Geolocation API
- Build tool: Laravel Mix (jika kompatibel dengan setup)
- Version control: Git

Jangan mengganti stack tanpa alasan teknis yang kuat dan persetujuan.

## 3. Core Modules

### Public Website
- Home, About Us, Services, Contact
- Trucking, Freight Forwarding, Warehousing, Logistics
- Request a Quote tanpa login
- Public shipment tracking menggunakan tracking reference

### Authentication & Authorization
- Login, logout, profile, password management
- Role-based access control
- Validasi akses backend dan frontend

### Admin & Operations
- Dashboard operasional
- Customer management
- Driver management
- Fleet / truck management
- Order management
- Multi-container management
- Driver dan truck assignment
- Shipment status monitoring
- Shipment timeline
- Notifications

### Driver Portal
- Daftar tugas pengiriman
- Detail order dan container
- Update status perjalanan
- Live GPS location sharing
- Upload dokumen dan Proof of Delivery (POD)
- Laporan kendala operasional

### Customer Portal
- Dashboard pengiriman
- Daftar order dan container
- Detail status dan tracking
- Shipment history
- Invoice dan dokumen

### Documents & Finance
- Document management
- Delivery Order (DO)
- Surat jalan
- Proof of Delivery
- Invoice generation (PDF)
- Payment status
- Financial reports dasar

## 4. User Roles

1. `super_admin` — akses penuh dan pengaturan sistem
2. `ops_admin` — order, dispatch, armada, supir, tracking
3. `doc_admin` — dokumen dan administrasi pengiriman
4. `finance` — invoice, pembayaran, laporan keuangan
5. `driver` — tugas, GPS, status, dokumen, laporan kendala
6. `customer` — order milik sendiri, tracking, dokumen, invoice

Setiap role hanya boleh mengakses fitur dan data sesuai kewenangannya.

## 5. Business Rules

- Satu order dapat memiliki banyak container.
- Setiap container memiliki status, tracking, dan riwayat perjalanan individual.
- Setiap container dapat ditugaskan kepada driver dan truck sesuai kebutuhan operasional.
- Customer hanya dapat melihat data perusahaannya sendiri.
- Driver hanya dapat melihat tugas yang ditugaskan kepadanya.
- GPS hanya aktif selama perjalanan yang ditugaskan.
- Status pengiriman harus memiliki timestamp dan histori.
- Invoice terhubung ke order dan memiliki status pembayaran.
- Request a Quote tidak otomatis menjadi order.
- Akun customer dibuat atau diaktifkan oleh admin.
- Setiap tindakan penting harus tercatat dalam activity log.

## 6. Shipment Status Flow

Gunakan status terstruktur dan konsisten:

`pending → confirmed → assigned → pickup → in_transit → delivered → completed`

Status tambahan sesuai kebutuhan operasional:
- `cancelled`
- `on_hold`
- `returning_empty`
- `exception`

Perubahan status harus melalui validasi backend dan tercatat dalam timeline.

## 7. UI/UX Guidelines

Design direction: **Modern Industrial × Premium SaaS**

- Desain orisinal, bukan dashboard template pasaran.
- Light Mode dan Dark Mode.
- Default Light Mode dengan tampilan premium.
- Palet warna konsisten dan profesional.
- Typography modern dengan hierarki visual jelas.
- Dashboard memiliki komposisi layout yang khas.
- Animasi dan micro-interaction halus, tidak berlebihan.
- Responsive untuk desktop, tablet, dan mobile.
- Mobile-first untuk Driver Portal.
- Peta tracking interaktif.
- Gunakan reusable components dan design tokens.
- Prioritaskan usability, accessibility, dan performa.

Target kualitas desain: 10/10 secara estetika, konsistensi, dan pengalaman pengguna.

## 8. AI Agent Working Instructions

1. Baca README.md dan dokumen proyek yang relevan sebelum bekerja.
2. Audit struktur project, dependencies, database, dan fitur yang sudah tersedia.
3. Jangan mengulang planning, requirement, atau penjelasan yang sudah terdokumentasi.
4. Kerjakan hanya task yang diminta user.
5. Gunakan pendekatan bertahap (Stage/Phase) dengan scope yang jelas.
6. Jangan mengerjakan stage berikutnya tanpa instruksi.
7. Jangan menghapus, menimpa, atau mengubah fitur yang sudah berjalan tanpa alasan dan persetujuan.
8. Jangan menjalankan migration, reset database, atau destructive command tanpa izin.
9. Hindari overengineering, dependencies yang tidak diperlukan, dan duplikasi kode.
10. Implementasikan validasi, authorization, security, dan error handling sejak awal.
11. Setelah implementasi, jalankan test yang relevan dan periksa regresi.
12. Laporkan hasil secara ringkas: perubahan, file utama, test, dan kendala.

**Penting:** Jika task sudah jelas, langsung kerjakan. Bertanya hanya jika terdapat blocker atau keputusan yang berdampak signifikan.

## 9. Development Roadmap

Pengembangan dilakukan bertahap:

- Stage 1 — Project Audit & Foundation
- Stage 2 — Database Design & Core Models
- Stage 3 — Authentication & Authorization
- Stage 4 — Public Company Profile
- Stage 5 — Admin Dashboard & Management
- Stage 6 — Order & Multi-Container Management
- Stage 7 — Dispatch & Driver Portal
- Stage 8 — Live GPS Tracking
- Stage 9 — Customer Portal
- Stage 10 — Documents, POD & Invoice
- Stage 11 — Notifications & Reports
- Stage 12 — Testing, Security & Deployment

Setiap stage harus memiliki scope, implementasi, dan verifikasi sendiri.

## 10. Final Objective

Hasil akhir harus berupa aplikasi ZarTrack yang:

- Berfungsi end-to-end.
- Memiliki UI/UX premium dan responsif.
- Memiliki role dan authorization yang aman.
- Mendukung operasional container trucking.
- Memiliki live tracking berbasis GPS.
- Memiliki dokumentasi dan struktur kode yang maintainable.
- Siap dipresentasikan sebagai portfolio dan dikembangkan untuk penggunaan nyata.