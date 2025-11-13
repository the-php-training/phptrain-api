# Quick Start Guide - API Testing

Get started testing the PHPTrain API in 5 minutes!

---

## Step 1: Import Postman Collection

### Option A: Using Postman App

1. Open Postman Desktop App
2. Click **"Import"** button (top left)
3. Click **"Upload Files"**
4. Select `postman_collection.json` from the `docs/` folder
5. Click **"Import"**

### Option B: Using Postman Web

1. Go to https://www.postman.com/
2. Sign in to your account
3. Click **"Import"** in your workspace
4. Drag and drop `postman_collection.json`
5. Click **"Import"**

---

## Step 2: Verify Collection

After importing, you should see:

```
PHPTrain API - DDD Implementation
├── Tenant Management
│   ├── Create Tenant
│   ├── Get Tenant by ID
│   └── List Tenants
└── Student Enrollment
    └── Enroll Student in Course
```

---

## Step 3: Check Environment Variables

The collection includes these variables (already configured):

| Variable | Default Value | Description |
|----------|--------------|-------------|
| `base_url` | `http://localhost:9501` | API server URL |
| `tenant_id` | (empty) | Will be set after creating tenant |
| `course_id` | (empty) | Will be set manually |
| `student_id` | (empty) | Will be set manually |

To view/edit variables:
1. Click on the collection name
2. Go to **Variables** tab
3. Edit **Current Value** if needed

---

## Step 4: Start Testing!

### Test 1: Create a Tenant ✅

1. Open **"Tenant Management" → "Create Tenant"**
2. Click **"Send"**

**Expected Response (201 Created)**:
```json
{
  "success": true,
  "message": "Tenant created successfully",
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "name": "Acme University",
    "slug": "acme-university",
    "status": "pending"
  }
}
```

3. **Copy the `id` from response**
4. Go to collection **Variables** tab
5. Set `tenant_id` current value to the copied ID
6. Click **Save**

---

### Test 2: Get Tenant by ID ✅

1. Open **"Tenant Management" → "Get Tenant by ID"**
2. Notice the URL uses `{{tenant_id}}` variable
3. Click **"Send"**

**Expected Response (200 OK)**:
```json
{
  "success": true,
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "name": "Acme University",
    "slug": "acme-university",
    "status": "pending"
  }
}
```

---

### Test 3: List Tenants ✅

1. Open **"Tenant Management" → "List Tenants"**
2. Click **"Send"**

**Expected Response (200 OK)**:
```json
{
  "success": true,
  "data": [
    {
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "name": "Acme University",
      "slug": "acme-university"
    }
  ],
  "pagination": {
    "total": 1,
    "limit": 20,
    "offset": 0
  }
}
```

---

### Test 4: Enroll Student (Requires Setup) ✅

**Prerequisites**: You need existing course and student IDs.

**For testing purposes, you can try with sample UUIDs**:
1. Set `course_id` variable: `123e4567-e89b-12d3-a456-426614174000`
2. Set `student_id` variable: `987fcdeb-51a2-43d7-8f12-123456789abc`

**If the student/course don't exist, you'll get**:
```json
{
  "success": false,
  "message": "Student {id} not found"
}
```

**This is expected!** It means the validation is working.

**When you have real student/course data**:
1. Open **"Student Enrollment" → "Enroll Student in Course"**
2. Update the request body with valid UUIDs
3. Click **"Send"**

**Expected Response (201 Created)**:
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

---

## Common Issues & Solutions

### Issue 1: Connection Refused

**Error**: `Error: connect ECONNREFUSED 127.0.0.1:9501`

**Solution**:
1. Ensure the Hyperf server is running:
   ```bash
   cd src
   php bin/hyperf.php start
   ```
2. Verify server is listening on port 9501
3. Check if `base_url` variable is correct

---

### Issue 2: 404 Not Found

**Error**: `404 Not Found - Route not registered`

**Solution**:
1. Check the route exists in your Hyperf routes
2. Verify the URL path matches exactly
3. Ensure controllers are registered in DI container

---

### Issue 3: Validation Errors

**Error**:
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {...}
}
```

**Solution**:
1. Check the request body matches the required format
2. Verify all required fields are present
3. Check field types (UUIDs, emails, etc.)
4. Review the inline documentation in each request

---

### Issue 4: Business Rule Violations

**Error**:
```json
{
  "success": false,
  "message": "Student already has learning access to this course"
}
```

**Solution**:
- This is expected behavior! The domain is enforcing business rules
- Try with a different student or course
- This demonstrates DDD in action 🎯

---

## Understanding the Responses

### Success Indicators

✅ **HTTP 200/201**: Success
✅ `"success": true` in response body
✅ Data returned in `"data"` field

### Error Indicators

❌ **HTTP 400**: Bad request (business rule violation)
❌ **HTTP 404**: Resource not found
❌ **HTTP 422**: Validation failed
❌ **HTTP 500**: Server error
❌ `"success": false` in response body

---

## Next Steps

### 1. Explore Request Documentation

Each request in the collection has detailed documentation:
- Click on a request
- Look at the **description** on the right
- It explains what the endpoint does, parameters, and responses

### 2. Modify Request Bodies

Try changing values:
- Different tenant names
- Invalid emails (to see validation)
- Duplicate slugs (to see uniqueness check)

### 3. Test Error Scenarios

Intentionally trigger errors:
- Invalid UUID format
- Missing required fields
- Duplicate enrollments

### 4. Review the Architecture

Check out the documentation files in the root:
- `ENROLLMENT_FLOW.md` - Complete enrollment process
- `BOUNDED_CONTEXT_COMPARISON.md` - Context separation
- `LAYER_ORGANIZATION.md` - DDD layers explained

---

## Tips for Effective Testing

1. **Use Variables**: Don't hardcode IDs, use collection variables
2. **Save Responses**: Copy IDs from responses for later use
3. **Test Happy Path First**: Ensure basic flow works
4. **Then Test Errors**: Try edge cases and validations
5. **Read Descriptions**: Each request has helpful documentation

---

## Sample Testing Session

Here's a complete testing flow:

```
1. POST /api/tenants
   → Create "Acme University"
   → Save tenant ID to variable

2. GET /api/tenants/{id}
   → Verify tenant created
   → Check response structure

3. GET /api/tenants
   → List all tenants
   → Verify pagination

4. POST /api/tenants (again with same slug)
   → Should fail with "already exists"
   → Validates uniqueness

5. POST /api/enrollments
   → Try with fake IDs
   → Should fail with "not found"
   → Validates existence

6. Create real student and course (when implemented)
   → Then test enrollment successfully
```

---

## Support

If you encounter issues:
1. Check server logs: `tail -f runtime/logs/hyperf.log`
2. Review the API documentation in `docs/README.md`
3. Check architecture docs in root folder
4. Verify database tables exist and migrations ran

---

## Summary

You now have:
- ✅ Postman collection imported
- ✅ Environment variables configured
- ✅ Understanding of available endpoints
- ✅ Knowledge of how to test
- ✅ Troubleshooting guide

**Happy Testing! 🚀**

The collection demonstrates **Domain-Driven Design** in action with proper bounded contexts, domain events, and clean architecture.
