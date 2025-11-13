# Tenant Management API - Usage Examples

## Using cURL

### 1. Create a New Tenant

```bash
curl -X POST http://localhost:9501/api/tenants \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Tech Academy",
    "slug": "tech-academy",
    "contact_email": "admin@techacademy.com",
    "contact_phone": "+1-555-0123"
  }'
```

**Success Response** (201):
```json
{
  "success": true,
  "message": "Tenant created successfully",
  "data": {
    "id": "9c8e7a5d-4b3a-2f1e-0d9c-8b7a6f5e4d3c",
    "name": "Tech Academy",
    "slug": "tech-academy",
    "contact_email": "admin@techacademy.com",
    "contact_phone": "+1-555-0123",
    "status": "pending",
    "created_at": "2024-10-01 14:30:00",
    "updated_at": "2024-10-01 14:30:00"
  }
}
```

**Error Response** (422 - Validation Failed):
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "slug": ["The slug format is invalid."],
    "contact_email": ["The contact email must be a valid email address."]
  }
}
```

**Error Response** (400 - Business Rule Violation):
```json
{
  "success": false,
  "message": "Tenant with slug 'tech-academy' already exists"
}
```

---

### 2. Get Tenant by ID

```bash
curl -X GET http://localhost:9501/api/tenants/9c8e7a5d-4b3a-2f1e-0d9c-8b7a6f5e4d3c
```

**Success Response** (200):
```json
{
  "success": true,
  "data": {
    "id": "9c8e7a5d-4b3a-2f1e-0d9c-8b7a6f5e4d3c",
    "name": "Tech Academy",
    "slug": "tech-academy",
    "contact_email": "admin@techacademy.com",
    "contact_phone": "+1-555-0123",
    "status": "active",
    "created_at": "2024-10-01 14:30:00",
    "updated_at": "2024-10-01 15:00:00"
  }
}
```

**Error Response** (404):
```json
{
  "success": false,
  "message": "Tenant with ID '9c8e7a5d-invalid-uuid' not found"
}
```

---

### 3. List All Tenants (Paginated)

```bash
curl -X GET "http://localhost:9501/api/tenants?limit=10&offset=0"
```

**Success Response** (200):
```json
{
  "success": true,
  "data": [
    {
      "id": "9c8e7a5d-4b3a-2f1e-0d9c-8b7a6f5e4d3c",
      "name": "Tech Academy",
      "slug": "tech-academy",
      "contact_email": "admin@techacademy.com",
      "contact_phone": "+1-555-0123",
      "status": "active",
      "created_at": "2024-10-01 14:30:00",
      "updated_at": "2024-10-01 15:00:00"
    },
    {
      "id": "8b7d6c5e-3a2b-1f0e-9d8c-7a6b5e4d3c2b",
      "name": "Business School",
      "slug": "business-school",
      "contact_email": "info@bizschool.edu",
      "contact_phone": null,
      "status": "pending",
      "created_at": "2024-10-01 13:00:00",
      "updated_at": "2024-10-01 13:00:00"
    }
  ],
  "pagination": {
    "total": 25,
    "limit": 10,
    "offset": 0
  }
}
```

---

## Using Postman

### Collection Setup

1. Create a new Collection: "Tenant Management API"
2. Set Base URL variable: `{{base_url}}` = `http://localhost:9501`

### Request Examples

#### 1. Create Tenant
- **Method**: POST
- **URL**: `{{base_url}}/api/tenants`
- **Headers**:
  - `Content-Type: application/json`
- **Body** (raw JSON):
```json
{
  "name": "Coding Bootcamp",
  "slug": "coding-bootcamp",
  "contact_email": "contact@codingbootcamp.io",
  "contact_phone": "+1-555-9999"
}
```

#### 2. Get Tenant
- **Method**: GET
- **URL**: `{{base_url}}/api/tenants/{{tenant_id}}`

#### 3. List Tenants
- **Method**: GET
- **URL**: `{{base_url}}/api/tenants`
- **Query Params**:
  - `limit`: 20
  - `offset`: 0

---

## Using HTTPie

### 1. Create Tenant

```bash
http POST localhost:9501/api/tenants \
  name="Data Science Institute" \
  slug="data-science-institute" \
  contact_email="hello@datasci.org" \
  contact_phone="+1-555-7777"
```

### 2. Get Tenant

```bash
http GET localhost:9501/api/tenants/9c8e7a5d-4b3a-2f1e-0d9c-8b7a6f5e4d3c
```

### 3. List Tenants

```bash
http GET localhost:9501/api/tenants limit==20 offset==0
```

---

## PHP Client Example

```php
use GuzzleHttp\Client;

$client = new Client(['base_uri' => 'http://localhost:9501']);

// Create tenant
$response = $client->post('/api/tenants', [
    'json' => [
        'name' => 'Design Academy',
        'slug' => 'design-academy',
        'contact_email' => 'info@designacademy.com',
        'contact_phone' => '+1-555-4444',
    ]
]);

$data = json_decode($response->getBody(), true);
$tenantId = $data['data']['id'];

// Get tenant
$response = $client->get("/api/tenants/{$tenantId}");
$tenant = json_decode($response->getBody(), true);

// List tenants
$response = $client->get('/api/tenants', [
    'query' => ['limit' => 10, 'offset' => 0]
]);
$tenants = json_decode($response->getBody(), true);
```

---

## Validation Rules

### Create Tenant Request

| Field | Type | Required | Validation Rules |
|-------|------|----------|------------------|
| `name` | string | Yes | Min: 3 chars, Max: 255 chars |
| `slug` | string | Yes | Min: 3 chars, Max: 50 chars, Pattern: `^[a-z0-9]+(?:-[a-z0-9]+)*$` |
| `contact_email` | string | Yes | Valid email format, Max: 255 chars |
| `contact_phone` | string | No | Max: 20 chars |

### Slug Pattern Examples

✅ Valid slugs:
- `tech-academy`
- `university-of-california`
- `acme123`
- `school-2024`

❌ Invalid slugs:
- `Tech-Academy` (uppercase not allowed)
- `tech_academy` (underscores not allowed)
- `tech academy` (spaces not allowed)
- `-tech-academy` (cannot start with hyphen)
- `tech-academy-` (cannot end with hyphen)
- `te` (too short, min 3 chars)

---

## Error Codes Reference

| Status Code | Description |
|-------------|-------------|
| 200 | Success (GET requests) |
| 201 | Created (POST successful) |
| 400 | Bad Request (Business rule violation) |
| 404 | Not Found (Tenant doesn't exist) |
| 422 | Unprocessable Entity (Validation failed) |
| 500 | Internal Server Error |

---

## Testing Workflow

### Complete Tenant Lifecycle

```bash
# 1. Create a new tenant
TENANT_JSON=$(curl -s -X POST http://localhost:9501/api/tenants \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test University",
    "slug": "test-university",
    "contact_email": "test@university.edu"
  }')

# 2. Extract tenant ID
TENANT_ID=$(echo $TENANT_JSON | jq -r '.data.id')

# 3. Get the created tenant
curl -X GET http://localhost:9501/api/tenants/$TENANT_ID

# 4. List all tenants
curl -X GET http://localhost:9501/api/tenants?limit=5
```

---

## Docker Environment

If running in Docker, use the appropriate host:

```bash
# If using docker-compose
curl -X POST http://localhost:9501/api/tenants ...

# If accessing from another container
curl -X POST http://hyperf:9501/api/tenants ...
```
