# User API Documentation

## Request Headers
```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer {token}  // Required for all endpoints
```

## Admin Endpoints

### 1. Get All Users

#### Endpoint
```http
GET /api/admin/users
```

#### Success Response (200 OK)
```json
{
    "status": true,
    "message": "Success",
    "data": [
        {
            "id": 1,
            "first_name": "John",
            "last_name": "Doe",
            "email": "john@example.com",
            "birth_date": "1990-01-01",
            "gender": "male",
            "role": "user",
            "created_at": "2024-03-21 10:30:00",
            "updated_at": "2024-03-21 10:30:00"
        }
        // ... more users
    ]
}
```

### 2. Get User by ID

#### Endpoint
```http
GET /api/admin/users/{id}
```

#### Success Response (200 OK)
```json
{
    "status": true,
    "message": "Success",
    "data": {
        "id": 1,
        "first_name": "John",
        "last_name": "Doe",
        "email": "john@example.com",
        "birth_date": "1990-01-01",
        "gender": "male",
        "role": "user",
        "created_at": "2024-03-21 10:30:00",
        "updated_at": "2024-03-21 10:30:00"
    }
}
```

### 3. Create User

#### Endpoint
```http
POST /api/admin/users
```

#### Request Body
```json
{
    "email": "newuser@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "first_name": "Jane",
    "last_name": "Smith",
    "gender": "female",
    "birth_date": "1995-05-15"
}
```

#### Success Response (201 Created)
```json
{
    "status": true,
    "message": "User created successfully",
    "data": {
        "id": 2,
        "first_name": "Jane",
        "last_name": "Smith",
        "email": "newuser@example.com",
        "birth_date": "1995-05-15",
        "gender": "female",
        "role": "user",
        "created_at": "2024-03-21 10:30:00",
        "updated_at": "2024-03-21 10:30:00"
    }
}
```

### 4. Update User

#### Endpoint
```http
PUT /api/admin/users/{id}
```

#### Request Body
```json
{
    "first_name": "Jane",
    "last_name": "Smith",
    "email": "jane.smith@example.com",
    "gender": "female",
    "birth_date": "1995-05-15"
}
```

#### Success Response (200 OK)
```json
{
    "status": true,
    "message": "User updated successfully",
    "data": {
        "id": 2,
        "first_name": "Jane",
        "last_name": "Smith",
        "email": "jane.smith@example.com",
        "birth_date": "1995-05-15",
        "gender": "female",
        "role": "user",
        "created_at": "2024-03-21 10:30:00",
        "updated_at": "2024-03-21 10:35:00"
    }
}
```

### 5. Delete User

#### Endpoint
```http
DELETE /api/admin/users/{id}
```

#### Success Response (200 OK)
```json
{
    "status": true,
    "message": "User deleted successfully",
    "data": []
}
```

## User Endpoints

### 1. Get User Profile

#### Endpoint
```http
GET /api/users/profile
```

#### Success Response (200 OK)
```json
{
    "status": true,
    "message": "Success",
    "data": {
        "id": 1,
        "first_name": "John",
        "last_name": "Doe",
        "email": "john@example.com",
        "birth_date": "1990-01-01",
        "gender": "male",
        "role": "user",
        "created_at": "2024-03-21 10:30:00",
        "updated_at": "2024-03-21 10:30:00"
    }
}
```

### 2. Change Password

#### Endpoint
```http
PUT /api/users/password
```

#### Request Body
```json
{
    "current_password": "oldpassword123",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

#### Success Response (200 OK)
```json
{
    "status": true,
    "message": "Password updated successfully",
    "data": {
        "id": 1,
        "first_name": "John",
        "last_name": "Doe",
        "email": "john@example.com",
        "updated_at": "2024-03-21 10:35:00"
    }
}
```

## Error Responses

### Unauthorized (401 Unauthorized)
```json
{
    "status": false,
    "message": "Unauthorized. Please login first."
}
```

### Forbidden (403 Forbidden)
```json
{
    "status": false,
    "message": "Access Denied. You are not authorized."
}
```

### Not Found (404 Not Found)
```json
{
    "status": false,
    "message": "User not found"
}
```

### Validation Error (422 Unprocessable Entity)
```json
{
    "status": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password must be at least 8 characters."],
        // ... other validation errors
    }
}
```

## Important Notes

1. **Authentication**
   - All endpoints require authentication via Bearer token
   - Admin endpoints require 'admin' role
   - User endpoints require 'user' role

2. **Validation Rules**
   - Email must be unique and valid format
   - Password minimum length: 8 characters
   - Gender must be either 'male' or 'female'
   - Birth date must be a valid date format
   - All fields are required unless specified otherwise

3. **Response Format**
   - All responses follow the standard format:
     ```json
     {
         "status": boolean,
         "message": string,
         "data": object|array|null,
         "errors": object|null
     }
     ```

4. **Timezone**
   - All timestamps are in Asia/Jakarta timezone (WIB/GMT+7)

5. **HTTP Status Codes**
   - 200: Success
   - 201: Created
   - 400: Bad Request
   - 401: Unauthorized
   - 403: Forbidden
   - 404: Not Found
   - 422: Validation Error
   - 500: Server Error
