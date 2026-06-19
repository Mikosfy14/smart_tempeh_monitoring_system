# 🌱 Rizhomatix 
**IoT-Based Tempe Fermentation Monitoring & Mitigation System**

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-06B6D4?style=for-the-badge&logo=tailwind-css&logoColor=white)
![Node.js](https://img.shields.io/badge/Node.js-43853D?style=for-the-badge&logo=node.js&logoColor=white)
![ESP32](https://img.shields.io/badge/ESP32-000000?style=for-the-badge&logo=espressif&logoColor=white)
![Ubuntu](https://img.shields.io/badge/Ubuntu-E95420?style=for-the-badge&logo=ubuntu&logoColor=white)

## 💻 Programming Languages
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![C++](https://img.shields.io/badge/C%2B%2B-00599C?style=for-the-badge&logo=c%2B%2B&logoColor=white)

## 🌐 Tautan Penting (Project Links)

[![Website](https://img.shields.io/badge/Website_Dashboard-Rizhomatix-blue?style=for-the-badge&logo=google-chrome&logoColor=white)](https://rizhomatix.web.id/)
[![YouTube](https://img.shields.io/badge/Video_Demo_YouTube-Tonton-red?style=for-the-badge&logo=youtube&logoColor=white)](https://youtu.be/t_RRnhH6zRs)
[![Drive](https://img.shields.io/badge/Laporan_Project-Google_Drive-emerald?style=for-the-badge&logo=google-drive&logoColor=white)](https://drive.google.com/file/d/1jbaLcjBhZlkBGzMOGSHs8R7WlZsWTxQP/view?usp=sharing)

---

## 📖 Deskripsi Proyek
**Rizhomatix** hadir untuk menyelesaikan masalah kegagalan panen dan kerugian finansial UMKM pengrajin tempe akibat pembusukan protein kedelai yang dipicu oleh suhu ekstrem eksotermik di dalam ruang fermentasi tradisional. 

Sistem siber-fisik ini bekerja dengan memanfaatkan mikrokontroler ESP32 yang terhubung pada rangkaian sensor DS18B20, DHT22, dan MQ-135 untuk membaca parameter temperatur, kelembaban udara, serta lonjakan gas amonia ($NH_3$) secara otonom. Data sensor dikirim via HTTP POST JSON terenkripsi ke backend Laravel di VPS Ubuntu 24.04 LTS untuk divisualisasikan pada *dashboard web*. Jika terdeteksi anomali ambang batas, server akan langsung memicu modul relay untuk mengaktifkan kipas pendingin 12V DC sekaligus menembak API lokal WhatsApp Gateway berbasis Node.js untuk mengirimkan alarm darurat ke ponsel pengguna. Manfaat utama proyek ini adalah memodernisasi cara kerja tradisional melalui pengawasan otonom 24 jam penuh, menekan angka produk afkir, serta efisiensi biaya operasional.

## ✨ Fitur Utama
- **Real-time Telemetry:** Pemantauan suhu, kelembaban, dan gas amonia secara *real-time*.
- **Auto-Mitigation:** Otomatisasi pengaktifan kipas pembuang panas (*exhaust*) melalui relay saat suhu mencapai batas kritis (>33°C).
- **Self-Hosted WhatsApp Gateway:** Notifikasi alarm darurat secara instan melalui WhatsApp menggunakan *microservices* mandiri tanpa biaya langganan API pihak ketiga.
- **Smart Dashboard:** Antarmuka web responsif untuk manajemen perangkat (klaim Device ID), grafik histori, dan analisis kurva kematangan *batch* fermentasi.
- **Reporting:** Ekspor laporan hasil fermentasi dalam format PDF.

## 🛠️ Arsitektur Teknologi

### Perangkat Keras (Hardware)
- **ESP32 NodeMCU** (Mikrokontroler Utama)
- **DS18B20** (Sensor Suhu Biji Kedelai / Cairan)
- **DHT22** (Sensor Suhu & Kelembaban Udara Inkubator)
- **MQ-135** (Sensor Kualitas Udara / Deteksi Gas Amonia)
- **Modul Relay 1-Channel** (Pengontrol Aktuator)
- **Kipas DC 12V** (Aktuator Pendingin)

### Perangkat Lunak & Cloud (Software Stack)
- **Backend & Web:** Laravel 11 (PHP), MySQL/MariaDB
- **WhatsApp Gateway:** Node.js, Express, `whatsapp-web.js`, Puppeteer (Headless Browser X11)
- **Server Deployment:** VPS Linux Ubuntu 24.04 LTS
- **Process Manager:** PM2 (menjaga Node.js tetap berjalan di latar belakang)

## ⚙️ Alur Kerja Sistem (System Workflow)

1. **Pembacaan Sensor:** ESP32 membaca data dari sensor DS18B20, DHT22, dan MQ-135.
2. **Transmisi API:** ESP32 mengirim data sensor ke endpoint REST API Laravel `/api/telemetry` dalam format JSON yang dilengkapi dengan API key enkripsi.
3. **Pemrosesan Backend:** Backend Laravel memvalidasi request, menyimpan data ke database, mengecek *threshold*, memperbarui status kipas, dan menjalankan analisis kematangan *batch* fermentasi.
4. **Visualisasi Dashboard:** Dashboard web mengambil data terbaru secara dinamis melalui endpoint seperti `/api/dashboard/live` dan `/api/dashboard/chart`.
5. **Trigger Notifikasi:** Jika terdapat kondisi abnormal (misal: amonia tinggi / suhu panas), Laravel mengirim request HTTP POST internal ke WhatsApp Gateway lokal yang berjalan di port 3000 pada VPS.
6. **Eksekusi Gateway:** Aplikasi Node.js WhatsApp Gateway memproses *request* di bawah pengelolaan PM2.
7. **Virtual Rendering:** WhatsApp Gateway menjalankan *Headless Browser Puppeteer* dengan dependensi X11 untuk merender antarmuka WhatsApp Web secara virtual.
8. **Pengiriman Pesan:** Sistem secara otonom mengirimkan notifikasi teks terformat (*Alert Alarm*) langsung ke nomor WhatsApp pengguna yang ditarik secara dinamis dari database.
9. **Umpan Balik Aktuator:** ESP32 melakukan *polling* status kontrol kipas melalui endpoint `/api/device/status`, kemudian menyalakan atau mematikan kipas sesuai instruksi server untuk memitigasi suhu panas.

---

### 📷 Arsitektur Sistem

<img width="1481" height="687" alt="arsitektur-rizhomatix" src="https://github.com/user-attachments/assets/1ad63316-add0-4a2e-ba44-7b53952a6812" />

---

### 📷 Alur Data Sistem

<img width="1411" height="767" alt="alur-data-integrasi-rizhomatix" src="https://github.com/user-attachments/assets/538cd260-b054-4429-9dfd-f1aed614283f" />

---

## 👨‍💻 Pengembang
Proyek ini dikembangkan oleh **Muhammad Shafiq Dzakwan Ananda**

Program Studi Teknologi Informasi, Fakultas Vokasi, Universitas Brawijaya (2026).
