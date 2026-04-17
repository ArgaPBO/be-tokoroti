# 📡 API Specification

Base URL: `https://<your-domain>/api`

---

## Authentication

### Endpoint: User Login

### Request

**Method:** `POST`

**URL:** `/login`

**Headers:**

```json
{
  "Content-Type": "application/json"
}
```

**Body:**

```json
{
  "username": "user@example.com",
  "password": "SecurePassword123"
}
```

### Response - Success (200 OK)

```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "username": "user@example.com",
    "branch_id": null,
    "created_at": "2026-03-03T11:32:00.000000Z",
    "updated_at": "2026-03-03T11:32:00.000000Z"
  },
  "token": "1|XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX",
  "token_type": "Bearer",
  "admin": true
}
```

### Endpoint: User Logout

### Request

**Method:** `POST`

**URL:** `/logout`

**Headers:**

```json
{
  "Content-Type": "application/json",
  "Authorization": "Bearer {token}"
}
```

### Response - Success (200 OK)

```json
{
  "message": "Logged out successfully"
}
```

---

## Common Authentication Header

For protected endpoints, include:

```http
Authorization: Bearer {token}
```

---

## Admin Endpoints

> Protected by `auth:sanctum` and `role:admin`

### Endpoint: Get Admin Profile

**Method:** `GET`

**URL:** `/admin`

**Response:**

```json
{
  "user": {
    "id": 1,
    "name": "Admin Name",
    "username": "admin@example.com",
    "branch_id": null,
    "created_at": "2026-03-03T11:32:00.000000Z",
    "updated_at": "2026-03-03T11:32:00.000000Z"
  }
}
```

### Endpoint: Admin Dashboard

**Method:** `GET`

**URL:** `/admin/dashboard`

**Response:**

```json
{
  "products_count": 123,
  "branches_count": 10,
  "branch_product_history_quantity_sum": 456,
  "branch_expense_history_nominal_sum": 7890000
}
```

### Branch Management

#### List Branches

**Method:** `GET`

**URL:** `/branches`

**Query Parameters:**
- `search` (optional)

#### Get Branch by ID

**Method:** `GET`

**URL:** `/branches/{id}`

#### Create Branch

**Method:** `POST`

**URL:** `/branches`

**Body:**

```json
{
  "name": "Branch A"
}
```

#### Update Branch

**Method:** `PUT`

**URL:** `/branches/{id}`

**Body:**

```json
{
  "name": "Branch B"
}
```

#### Delete Branch

**Method:** `DELETE`

**URL:** `/branches/{id}`

---

### User Management

#### List Users

**Method:** `GET`

**URL:** `/users`

**Query Parameters:**
- `search` (optional)
- `role` (optional): `admin` or `branch`

#### List Branch Users

**Method:** `GET`

**URL:** `/users/branch`

#### List Users by Branch

**Method:** `GET`

**URL:** `/users/branch/{branch_id}`

#### Get User by ID

**Method:** `GET`

**URL:** `/users/{id}`

#### Create User

**Method:** `POST`

**URL:** `/users`

**Body:**

```json
{
  "name": "Jane Doe",
  "username": "jane.doe@example.com",
  "password": "SecurePassword123",
  "branch_id": 2
}
```

`branch_id` can be `null` for admin users.

#### Update User

**Method:** `PUT`

**URL:** `/users/{id}`

**Body:**

```json
{
  "name": "Jane Doe Updated",
  "username": "jane.doe@example.com",
  "password": "NewSecurePassword123"
}
```

#### Delete User

**Method:** `DELETE`

**URL:** `/users/{id}`

---

### Product Management

#### List Products

**Method:** `GET`

**URL:** `/products`

**Query Parameters:**
- `search` (optional)

#### Get Product by ID

**Method:** `GET`

**URL:** `/products/{id}`

#### Create Product

**Method:** `POST`

**URL:** `/products`

**Body:**

```json
{
  "name": "Product A",
  "price": 15000
}
```

#### Update Product

**Method:** `PUT`

**URL:** `/products/{id}`

**Body:**

```json
{
  "name": "Product A Updated",
  "price": 17500
}
```

#### Delete Product

**Method:** `DELETE`

**URL:** `/products/{id}`

---

### Expense Management

#### List Expenses

**Method:** `GET`

**URL:** `/expenses`

**Query Parameters:**
- `search` (optional)

#### Get Expense by ID

**Method:** `GET`

**URL:** `/expenses/{id}`

#### Create Expense

**Method:** `POST`

**URL:** `/expenses`

**Body:**

```json
{
  "name": "Electricity"
}
```

#### Update Expense

**Method:** `PUT`

**URL:** `/expenses/{id}`

**Body:**

```json
{
  "name": "Electricity Updated"
}
```

#### Delete Expense

**Method:** `DELETE`

**URL:** `/expenses/{id}`

---

### Branch Product Management

#### List Products in a Branch

**Method:** `GET`

**URL:** `/branches/{branchId}/products`

**Query Parameters:**
- `start_date` (optional)
- `end_date` (optional)
- `search` (optional)

#### Get Branch Product by Product ID

**Method:** `GET`

**URL:** `/branches/{branchId}/products/{productId}`

#### Create Branch Product

**Method:** `POST`

**URL:** `/branches/{branchId}/products`

**Body:**

```json
{
  "product_id": 5,
  "branch_price": 18000
}
```

#### Update Branch Product

**Method:** `PUT`

**URL:** `/branches/{branchId}/products/{productId}`

**Body:**

```json
{
  "branch_price": 18500
}
```

#### Delete Branch Product

**Method:** `DELETE`

**URL:** `/branches/{branchId}/products/{productId}`

---

### Branch Expense Summary

#### Get Branch Expense Summary

**Method:** `GET`

**URL:** `/branches/{branchId}/expenses`

**Query Parameters:**
- `start_date` (optional)
- `end_date` (optional)
- `search` (optional)

---

### Laba Rugi (Profit and Loss)

#### Admin View

**Method:** `GET`

**URL:** `/branches/{branchId}/labarugi`

#### Branch View

**Method:** `GET`

**URL:** `/branch/labarugi`

---

## Branch User Endpoints

> Protected by `auth:sanctum` and `role:branch`

### Get Branch Profile

**Method:** `GET`

**URL:** `/branch`

### Branch Dashboard

**Method:** `GET`

**URL:** `/branch/dashboard`

### Branch Product Endpoints

#### List Branch Products

**Method:** `GET`

**URL:** `/branch/products`

#### Get Single Branch Product

**Method:** `GET`

**URL:** `/branch/products/{productId}`

#### Create Branch Product

**Method:** `POST`

**URL:** `/branch/products`

#### Update Branch Product

**Method:** `PUT`

**URL:** `/branch/products/{productId}`

#### Delete Branch Product

**Method:** `DELETE`

**URL:** `/branch/products/{productId}`

### Branch Expense Endpoints

#### List Branch Expenses

**Method:** `GET`

**URL:** `/branch/expenses`

### Branch Product History Endpoints

#### List Product History

**Method:** `GET`

**URL:** `/branch/histories/products`

**Query Parameters:**
- `search` (optional)
- `transaction_type` (optional): `pesanan`, `retail`
- `shift` (optional): `pagi`, `siang`
- `date_from` (optional)
- `date_to` (optional)

#### Create Product History

**Method:** `POST`

**URL:** `/branch/histories/products`

**Body Example (single item):**

```json
{
  "date": "2026-04-05",
  "product_name": "Product A",
  "quantity": 10,
  "transaction_type": "retail",
  "shift": "pagi",
  "discount_percent": 10,
  "discount_price": 1500
}
```

#### Delete Product History

**Method:** `DELETE`

**URL:** `/branch/histories/products/{id}`

### Branch Expense History Endpoints

#### List Expense History

**Method:** `GET`

**URL:** `/branch/histories/expenses`

**Query Parameters:**
- `search` (optional)
- `shift` (optional): `pagi`, `siang`
- `date_from` (optional)
- `date_to` (optional)

#### Create Expense History

**Method:** `POST`

**URL:** `/branch/histories/expenses`

**Body Example (single item):**

```json
{
  "date": "2026-04-05",
  "expense_name": "Electricity",
  "nominal": 200000,
  "description": "April electricity bill",
  "shift": "siang"
}
```

#### Delete Expense History

**Method:** `DELETE`

**URL:** `/branch/histories/expenses/{id}`

---

## ⚠️ Error Codes

| **HTTP Code** | **Error Code** | **Message** | **Description** |
| --- | --- | --- | --- |
| 400 | ERR_INVALID_INPUT | Invalid input parameters | Request body tidak sesuai format atau ada field yang missing |
| 401 | ERR_INVALID_CREDENTIALS | Invalid username or password | Credentials yang diberikan tidak valid |
| 401 | ERR_INVALID_TOKEN | Invalid or malformed token | Token tidak valid atau format salah |
| 403 | ERR_FORBIDDEN | Access denied | User tidak memiliki permission untuk resource ini |
| 404 | ERR_NOT_FOUND | Resource not found | Resource yang diminta tidak ditemukan |
| 409 | ERR_DUPLICATE | Resource already exists | Data sudah ada di database (duplicate) |
| 422 | ERR_UNPROCESSABLE_ENTITY | Validation failed | Data validasi tidak lolos |
| 429 | ERR_RATE_LIMIT | Too many requests | Request melebihi rate limit |
| 500 | ERR_INTERNAL_SERVER | Internal server error | Error di sisi server |
| 503 | ERR_SERVICE_UNAVAILABLE | Service temporarily unavailable | Service sedang maintenance atau down |

### Error Response Format

```json
{
  "status": "error",
  "error": {
    "code": "ERR_INVALID_CREDENTIALS",
    "message": "Invalid username or password",
    "details": {
      "field": "password",
      "reason": "Password does not match"
    }
  },
  "timestamp": "2026-04-05T11:32:00Z",
  "path": "/api/login"
}
```
