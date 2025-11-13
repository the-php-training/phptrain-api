# Makefile Guide - PHPTrain API

Complete reference for all available `make` commands for the Hyperf-based PHPTrain API.

---

## Quick Start

```bash
# Show all available commands
make help

# Full setup (first time)
make go

# Start containers
make up

# Stop containers
make down
```

---

## Docker Management

### `make up`
Start containers in detached mode (background).
```bash
make up
```

### `make down` / `make stop`
Stop and remove containers.
```bash
make down
# or
make stop
```

### `make restart`
Restart all containers.
```bash
make restart
```

### `make sh`
Open a shell inside the app container.
```bash
make sh
```

### `make logs`
Follow container logs (last 50 lines).
```bash
make logs
```

---

## Application Commands

### `make install`
Install Composer dependencies.
```bash
make install
```

### `make autoload`
Regenerate Composer autoload files (after adding new classes/namespaces).
```bash
make autoload
```
**When to use**: After creating new bounded contexts or moving files.

### `make cache-clear`
Clear Hyperf runtime cache (DI container).
```bash
make cache-clear
```
**When to use**: After changing annotations, DI configurations, or when getting dependency injection errors.

---

## Database Commands

> **Note**: These commands require a migration tool to be installed (e.g., Phinx or Hyperf migrations package).
> All database commands use the `db-` prefix.

### `make db-migrate`
Run all pending database migrations.
```bash
make db-migrate
```

**Tries in order**:
1. Hyperf migrate command: `php bin/hyperf.php migrate`
2. Phinx: `vendor/bin/phinx migrate`

### `make db-migrate-create`
Create a new migration file.
```bash
make db-migrate-create
# Prompts for: Enter migration name: create_users_table
```

### `make db-migrate-rollback` / `make db-rollback`
Rollback the last migration batch.
```bash
make db-migrate-rollback
# or
make db-rollback
```

### `make db-migrate-fresh`
⚠️ **DANGER**: Drop all tables and re-run all migrations.
```bash
make db-migrate-fresh
# Prompts for confirmation: Are you sure? (yes/no)
```

### `make db-seed`
Run database seeders.
```bash
make db-seed
```

---

## Testing

### `make test`
Run tests using Composer test script or co-phpunit.
```bash
make test
```

### `make test-report`
Generate test coverage report (requires Pest).
```bash
make test-report
```

---

## Logs

### `make log`
Follow Hyperf application logs.
```bash
make log
```
Shows: `runtime/logs/hyperf.log` (last 50 lines)

### `make logs`
Follow Docker container logs.
```bash
make logs
```

---

## Development Workflow

### `make go`
Full setup for first-time or clean start:
1. Stop containers (`make down`)
2. Install dependencies (`make install`)
3. Build and start containers
4. Run database migrations (`make db-migrate`)
5. Show success message with URL

```bash
make go
```

Output:
```
✅ Containers started!
⏳ Running migrations...
[migration output]

📍 API running at: http://localhost:9501
📖 Health check: curl http://localhost:9501/
```

### `make rebuild`
Rebuild containers from scratch (no cache).
```bash
make rebuild
```

### `make clean`
Stop containers and remove volumes (full cleanup).
```bash
make clean
```

---

## Utility Commands

### `make show-vars`
Display current user and group IDs (for Docker permissions).
```bash
make show-vars
```

Output:
```
USERID: 1000
GROUPID: 1000
```

### `make help`
Show all available commands with descriptions.
```bash
make help
```

---

## Common Workflows

### First Time Setup
```bash
# 1. Full setup
make go

# 2. Wait for server to start

# 3. Test the API
curl http://localhost:9501/
```

### Daily Development
```bash
# Start working
make up

# Make code changes...

# Clear cache if needed
make cache-clear

# Restart if needed
make restart

# View logs
make logs

# Stop working
make down
```

### After Adding New Classes
```bash
# 1. Regenerate autoload
make autoload

# 2. Clear DI cache
make cache-clear

# 3. Restart
make restart
```

### Database Workflow
```bash
# 1. Create migration
make db-migrate-create
# Enter name: create_courses_table

# 2. Edit the migration file

# 3. Run migration
make db-migrate

# 4. If something went wrong
make db-rollback
```

### Debugging
```bash
# 1. Check container logs
make logs

# 2. Check application logs
make log

# 3. Open shell to investigate
make sh
```

---

## Environment Variables

The Makefile uses these variables:

| Variable | Default | Description |
|----------|---------|-------------|
| `USERID` | Current user ID | For file permissions |
| `GROUPID` | Current group ID | For file permissions |
| `DC` | docker compose command | Docker compose with env vars |
| `HYPERF` | `php bin/hyperf.php` | Hyperf CLI command |

---

## Troubleshooting

### Permission Errors
```bash
# Check IDs match
make show-vars

# Should match your system user
id -u  # Should match USERID
id -g  # Should match GROUPID
```

### Container Not Starting
```bash
# View logs
make logs

# Rebuild without cache
make rebuild

# Clean everything and start fresh
make clean
make go
```

### Migration Tool Not Found
```bash
make db-migrate
# ⚠️  No migration tool found. Install hyperf/database or phinx.
```

**Solution**: Install a migration package:
```bash
make sh
composer require --dev robmorgan/phinx
```

### Autoload Not Working
```bash
# After adding new namespaces to composer.json
make autoload
make cache-clear
make restart
```

---

## Tips & Best Practices

1. **Always use `make` commands** instead of direct docker commands
2. **Run `make autoload` after namespace changes** in composer.json
3. **Run `make cache-clear` after annotation changes**
4. **Use `make sh` to debug** inside the container
5. **Check `make logs` when something fails**
6. **Never use `make db-migrate-fresh` in production!**

---

## Quick Reference

```bash
# Setup
make go              # First time setup
make up              # Start
make down            # Stop
make restart         # Restart

# Development
make sh              # Shell
make logs            # View logs
make autoload        # Regenerate autoload
make cache-clear     # Clear cache

# Database (all with db- prefix)
make db-migrate          # Run migrations
make db-rollback         # Rollback migrations
make db-migrate-create   # Create migration
make db-migrate-fresh    # Drop all & re-run (dangerous!)
make db-seed             # Run seeders

# Testing
make test            # Run tests

# Cleanup
make rebuild         # Rebuild containers
make clean           # Remove everything
```

---

## Summary

The Makefile provides a consistent interface for managing the Hyperf application with proper Docker integration. All commands handle user permissions correctly and provide helpful feedback.

Use `make help` anytime to see available commands!
