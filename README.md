# 🎓 Online Learning Platform — Backend (HyPerf + Docker)

This project is a scalable, multi-tenant online learning platform built with [HyPerf](https://hyperf.io/) and powered by a containerized infrastructure using Docker. It includes support for i18n, async jobs, secure API access, content delivery, and user certification management.

---

## ⚙️ Tech Stack

| Layer     | Stack/Tools                                 |
|-----------|----------------------------------------------|
| Backend   | **HyPerf 3.1**, PHP **8.4**, Swoole          |
| Frontend  | Nuxt by Vue.js (with Firebase or Cloudflare) |
| Database  | MySQL 8 (via Docker)                         |
| Caching   | Redis (async-queue)                          |
| Media     | Google Cloud Storage, YouTube/Vimeo          |
| Hosting   | VPS (Hostinger), Firebase (Frontend)         |
| SSL       | Certbot + Cloudflare                         |
| Container | Docker + Docker Compose                      |

---

## 🚀 Project Features

- Multi-tenant architecture (`Tenant`, `Course`, `Topic`, `Content`)
- Student enrollment and progress tracking
- Video-based lessons and activity content
- Certification generation for completed courses
- Fully containerized backend + DB + cache
- Auto-configured non-root user in container
- Dark/Light theme toggle on frontend
- Firebase or Cloudflare hosting support
- Docker-ready for local + production environments

---

## 🧱 Domain Model

![Domain Model](./docs/images/domain-model.png)

---

## ☁️ Production Infrastructure

![Production Infrastructure](./docs/images/production-infrastructure.png)

---

## 📦 Developer Setup

### Requirements

- Docker + Docker Compose
- `make` (optional but recommended)
### Quick Start

1. **Copy the environment file:**
   ```bash
   cp src/.env.example src/.env
   ```

2. **Start the application:**
   ```bash
   make go
   ```

   This command will:
   - Stop any existing containers
   - Install Composer dependencies
   - Build and start containers
   - Run database migrations
   - Display the API URL

3. **Access the API:**
   ```
   http://localhost:9501
   ```

### Common Commands

```bash
make go          # Full setup (install + build + start + migrate)
make up          # Start containers
make down        # Stop containers
make restart     # Restart containers
make sh          # Open shell in backend container
make logs        # Follow container logs
make log         # Follow application logs
make test        # Run tests

# Database commands
make db-migrate          # Run migrations
make db-rollback         # Rollback last migration
make db-seed             # Run database seeders

# Cache & cleanup
make cache-clear         # Clear Hyperf cache
make autoload            # Regenerate autoload files
```


---

## 🧪 Testing

To run the test suite:

```bash
make test
```

Or generate a coverage report:

```bash
make test-report
```

---

## 🔐 SSL

All production traffic is routed via **Cloudflare**, with **Certbot** handling SSL provisioning using DNS challenge.

---

## 📦 Future Improvements

- WebSocket real-time updates
- Notification system
- Admin dashboard
- Payment integration (Stripe or MercadoPago)

---
