# POS System

Sistem Point of Sales (POS) untuk kasir dengan manajemen produk, transaksi, dan laporan keuangan.

🌐 **Live:** [pos.novasistem.cloud](https://pos.novasistem.cloud)

## Fitur

- **Kasir** — Transaksi cepat dengan antarmuka POS
- **Manajemen Produk** — Tambah, edit, hapus produk
- **Manajemen Stok** — Tracking stok barang
- **Laporan** — Laporan penjualan harian/bulanan
- **Cetak Struk** — Cetak receipt transaksi
- **Dashboard** — Grafik penjualan dan analitik
- **Multi User** — Admin, Kasir

## Teknologi

- Laravel
- MySQL
- Tailwind CSS
- Alpine.js
- Docker
- Nginx

## Instalasi

```bash
git clone https://github.com/MuhamadGilangFikriAzi/pos-system.git
cd pos-system

docker compose up -d

# Migrasi database
docker compose exec laravel php artisan migrate
docker compose exec laravel php artisan db:seed

# Akses: http://localhost:8080
```

## Persyaratan Sistem

- Docker & Docker Compose
- PHP 8.2+
- Composer
- MySQL 8.0+

## Struktur

```
pos-system/
├── laravel/
│   ├── app/Http/Controllers/Pos/
│   ├── resources/views/
│   └── routes/
├── docker-compose.yml
├── Dockerfile
└── nginx.conf
```
