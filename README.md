# 🎓 Online Learning Platform — Backend (HyPerf + Docker)

This project is a scalable, multi-tenant online learning platform built with [HyPerf](https://hyperf.io/) and powered by a containerized infrastructure using Docker. It includes support for i18n, async jobs, secure API access, content delivery, and user certification management.

---

## ⚙️ Tech Stack

| Layer     | Stack/Tools                                 |
|-----------|----------------------------------------------|
| Backend   | **HyPerf 3.1**, PHP **8.3**, Swoole          |
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
- `.env` file with the following (or use exported shell vars):

```env
UID=1000
GID=1000
```

### Commands

```bash
make go          # build + up + composer install
make sh          # open shell in backend container
make test        # run tests
make logs        # follow logs
make stop        # stop all containers
```

---

## 📚 Work Log

### 📅 18/06/2025

- ✅ Generated a new **HyPerf** project using the official Docker image: `hyperf/hyperf:8.3-alpine-swoole`
- ⚙️ PHP version: **8.3**
- 🔐 Configured **user permissions** in Docker using `USERID` and `GROUPID` as build arguments
- 👤 Set up a **non-root user** inside the HyPerf container for secure execution
- 🌐 Created and tested a **"Hello World" endpoint** served by the **Swoole HTTP server**

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
