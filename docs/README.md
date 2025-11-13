# API Documentation

This folder contains API documentation and testing resources for the PHPTrain API.

---

## Postman Collection

### Import Instructions

1. **Open Postman**

2. **Import the Collection**:
   - Click "Import" button in Postman
   - Select `postman_collection.json` from this folder
   - Click "Import"

3. **Environment Variables**:
   The collection includes these variables:
   - `base_url`: `http://localhost:9501` (default)
   - `tenant_id`: Empty (set after creating a tenant)
   - `course_id`: Empty (set after creating a course)
   - `student_id`: Empty (set after creating a student)

4. **Update Base URL** (if needed):
   - Go to collection variables
   - Update `base_url` to match your server

---

## Available Endpoints

### Tenant Management

#### 1. Create Tenant
```
POST /api/tenants
```

**Request**:
```json
{
  "name": "Acme University",
  "slug": "acme-university",
  "contact_email": "admin@acme.edu",
  "contact_phone": "+1-555-0100"
}
```

**Response (201)**:
```json
{
  "success": true,
  "message": "Tenant created successfully",
  "data": {
    "id": "uuid",
    "name": "Acme University",
    "slug": "acme-university",
    "status": "pending",
    "created_at": "2025-01-15T10:30:00Z"
  }
}
```

---

#### 2. Get Tenant by ID
```
GET /api/tenants/{id}
```

**Response (200)**:
```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "name": "Acme University",
    "slug": "acme-university",
    "status": "active"
  }
}
```

---

#### 3. List Tenants
```
GET /api/tenants?limit=20&offset=0
```

**Response (200)**:
```json
{
  "success": true,
  "data": [...],
  "pagination": {
    "total": 50,
    "limit": 20,
    "offset": 0
  }
}
```

---

### Student Enrollment

#### 1. Enroll Student in Course
```
POST /api/enrollments
```

**Request**:
```json
{
  "course_id": "123e4567-e89b-12d3-a456-426614174000",
  "student_id": "987fcdeb-51a2-43d7-8f12-123456789abc"
}
```

**Response (201)**:
```json
{
  "success": true,
  "message": "Student successfully enrolled in course",
  "data": {
    "course_id": "123e4567-e89b-12d3-a456-426614174000",
    "student_id": "987fcdeb-51a2-43d7-8f12-123456789abc",
    "status": "enrolled"
  }
}
```

**What This Does**:
1. ✅ Grants student learning access in **StudentLearning** context
2. ✅ Creates enrollment record in **CourseManagement** context
3. ✅ Both happen synchronously via domain events

---

## Error Responses

### Validation Error (422)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "course_id": ["The course_id field is required."]
  }
}
```

### Business Rule Violation (400)
```json
{
  "success": false,
  "message": "Student already has learning access to this course"
}
```

### Not Found (404)
```json
{
  "success": false,
  "message": "Tenant not found"
}
```

### Server Error (500)
```json
{
  "success": false,
  "message": "An error occurred while processing the request",
  "error": "Error details"
}
```

---

## Testing Flow

### Recommended Testing Sequence

1. **Create a Tenant**:
   ```
   POST /api/tenants
   ```
   Save the returned `id` to `tenant_id` variable

2. **List Tenants**:
   ```
   GET /api/tenants
   ```
   Verify your tenant appears

3. **Get Tenant Details**:
   ```
   GET /api/tenants/{tenant_id}
   ```

4. **Enroll a Student** (requires student and course to exist):
   ```
   POST /api/enrollments
   {
     "course_id": "uuid",
     "student_id": "uuid"
   }
   ```

---

## Using cURL

### Create Tenant
```bash
curl -X POST http://localhost:9501/api/tenants \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Acme University",
    "slug": "acme-university",
    "contact_email": "admin@acme.edu",
    "contact_phone": "+1-555-0100"
  }'
```

### Get Tenant
```bash
curl http://localhost:9501/api/tenants/{tenant_id}
```

### List Tenants
```bash
curl http://localhost:9501/api/tenants?limit=20&offset=0
```

### Enroll Student
```bash
curl -X POST http://localhost:9501/api/enrollments \
  -H "Content-Type: application/json" \
  -d '{
    "course_id": "123e4567-e89b-12d3-a456-426614174000",
    "student_id": "987fcdeb-51a2-43d7-8f12-123456789abc"
  }'
```

---

## Architecture Notes

This API follows **Domain-Driven Design (DDD)** principles:

### Bounded Contexts

1. **TenantManagement**: Manages organizations/institutions
2. **StudentLearning**: Manages student learning access
3. **CourseManagement**: Manages administrative enrollment tracking

### Event-Driven Communication

When a student enrolls:
1. `POST /api/enrollments` → **StudentLearning** context
2. `Course::grantLearningAccess()` → Domain logic
3. `StudentEnrolled` event emitted
4. **CourseManagement** context listens synchronously
5. `Course::recordEnrollment()` → Creates admin record

Both contexts update their databases in the same transaction.

---

## Development

### Prerequisites
- PHP 8.1+
- Hyperf Framework
- MySQL/PostgreSQL

### Running the Server
```bash
cd src
php bin/hyperf.php start
```

Server runs on: `http://localhost:9501`

---

## Support

For issues or questions:
- Check the architecture documentation in the root folder
- Review `ENROLLMENT_FLOW.md` for detailed enrollment process
- See `BOUNDED_CONTEXT_COMPARISON.md` for context separation

---

## Collection Features

✅ **Organized by Bounded Context**
✅ **Complete request examples**
✅ **Detailed response documentation**
✅ **Error handling examples**
✅ **Environment variables for flexibility**
✅ **Inline documentation in each request**

Import and start testing! 🚀
