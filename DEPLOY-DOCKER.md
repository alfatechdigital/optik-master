# 🐳 PANDUAN DEPLOY DOCKER — Optik Store
## Ubuntu 22 + CasaOS + Cloudflare Tunnel

---

## STEP 1 — Install Docker di Ubuntu (jika belum ada)

```bash
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER
newgrp docker
```

---

## STEP 2 — Upload Project ke Server

### Opsi A — Via CasaOS File Manager
Zip project di Windows (tanpa vendor & node_modules):
```powershell
# Di PowerShell Windows
cd "D:\My Projek\Laravel"
tar -czf optik-master.tar.gz optik-master `
    --exclude=optik-master/vendor `
    --exclude=optik-master/node_modules `
    --exclude=optik-master/.git
```
Upload file `optik-master.tar.gz` via CasaOS Files ke folder `/home/casaos/`

### Opsi B — Via SCP
```bash
scp -r optik-master.tar.gz user@IP_SERVER:/home/casaos/
```

---

## STEP 3 — Extract & Setup di Server

```bash
# SSH ke server
ssh user@IP_SERVER

# Extract
cd /home/casaos
tar -xzf optik-master.tar.gz
cd optik-master

# Copy .env untuk Docker
cp .env.docker .env

# Edit .env — sesuaikan APP_URL dengan domain Anda
nano .env
# Ubah: APP_URL=https://optik.domain-anda.com
```

---

## STEP 4 — Build & Jalankan Docker

```bash
# Build image (pertama kali agak lama ~5-10 menit)
docker compose build

# Jalankan container
docker compose up -d

# Cek status
docker compose ps

# Lihat log
docker compose logs -f app
```

---

## STEP 5 — Verifikasi

```bash
# Cek container berjalan
docker ps

# Test akses lokal
curl http://localhost:8100
```

Buka browser: `http://IP_SERVER:8100/login`

---

## STEP 6 — Setup Cloudflare Tunnel

Di **Cloudflare Dashboard** → Zero Trust → Tunnels → Edit tunnel Anda:

Tambah **Public Hostname**:
```
Subdomain : optik
Domain    : domain-anda.com
Type      : HTTP
URL       : localhost:8100
```

Akses via: `https://optik.domain-anda.com/login`

---

## PERINTAH DOCKER BERGUNA

```bash
# Stop aplikasi
docker compose down

# Restart aplikasi
docker compose restart

# Update aplikasi (setelah ada perubahan file)
docker compose down
docker compose build --no-cache
docker compose up -d

# Masuk ke dalam container
docker exec -it optik_app bash

# Jalankan artisan dari luar container
docker exec optik_app php artisan migrate
docker exec optik_app php artisan cache:clear

# Lihat log error
docker compose logs app --tail=50

# Backup database
docker exec optik_db mysqldump -u optik_user -poptik_password_123 optik_store > backup.sql

# Restore database
docker exec -i optik_db mysql -u optik_user -poptik_password_123 optik_store < backup.sql
```

---

## PORT YANG DIGUNAKAN

| Aplikasi     | Port  |
|-------------|-------|
| Optik Store  | 8100  |
| (Aplikasi lain bisa pakai 8101, 8102, dst) |

---

## LOGIN DEFAULT

| Role        | Email                | Password |
|-------------|----------------------|----------|
| Super Admin | superadmin@optik.com | password |
| Admin       | admin@optik.com      | password |
| Kasir       | kasir@optik.com      | password |

**Ganti password setelah login pertama!**
