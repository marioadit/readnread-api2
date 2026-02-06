# Authentication API Documentation

## Request Headers
```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer {token}  // For protected routes
```

## 1. Login

### Endpoint
```http
POST /api/auth/login
```

### Request Body
```json
{
    "email": "dosen@example.com",
    "password": "password"
}
```

### Success Response (200 OK)
```json
{
    "status": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "first_name": "Regular",
            "last_name": "Dosen",
            "email": "dosen@example.com",
            "birth_date": "2003-01-09",
            "gender": "Female",
            "role": "dosen",
            "created_at": "2024-03-21 10:30:00",
            "updated_at": "2024-03-21 10:30:00"
        },
        "token": "1|p6IghECGCVRMwAPCo1OptU7SQ0y0ykzd9pUWL4py",
        "token_type": "Bearer",
        "expires_at": "2024-03-28 10:30:00"
    }
}
```

### Error Responses

#### Invalid Credentials (401 Unauthorized)
```json
{
    "status": false,
    "message": "Email or password is incorrect"
}
```

#### Validation Error (422 Unprocessable Entity)
```json
{
    "status": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password field is required."]
    }
}
```

## 2. Register

### Endpoint
```http
POST /api/auth/register
```

### Request Body
```json
{
    "first_name": "Jhon",
    "last_name": "Doe",
    "email": "jhondoe@example.com",
    "birth_date": "2003-01-09",
    "gender": "male",
    "password": "password123",
    "password_confirmation": "password123"
}
```

### Success Response (200 OK)
```json
{
    "status": true,
    "data": {
        "first_name": "Jhon",
        "last_name": "Doe",
        "email": "jhondoe@example.com",
        "role": "user"
    }
}
```

### Error Responses

#### Email Already Exists (400 Bad Request)
```json
{
    "status": false,
    "message": "Email already exists"
}
```

#### Validation Error (422 Unprocessable Entity)
```json
{
    "status": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email field is required."],
        "first_name": ["The first name field is required."],
        "last_name": ["The last name field is required."],
        "birth_date": ["The birth date field is required."],
        "gender": ["The gender field must be either male or female."],
        "password": ["The password must be at least 8 characters."],
        "password_confirmation": ["The password confirmation does not match."]
    }
}
```

## 3. Logout

### Endpoint
```http
DELETE /api/auth/logout
```

### Headers
```http
Authorization: Bearer {token}
```

### Success Response (200 OK)
```json
{
    "status": true,
    "message": "Logout successful"
}
```

### Error Responses

#### Unauthorized (401 Unauthorized)
```json
{
    "status": false,
    "message": "Unauthorized. Please login first."
}
```

#### Invalid Token (401 Unauthorized)
```json
{
    "status": false,
    "message": "Invalid or expired token"
}
```

## Important Notes

1. **Token Expiration**
   - Access tokens expire after 7 days
   - Timezone used: Asia/Jakarta (WIB/GMT+7)
   - Expired tokens are automatically invalidated

2. **Authentication**
   - All protected routes require a valid Bearer token
   - Include the token in the Authorization header
   - Tokens are invalidated on logout

3. **Validation Rules**
   - Email must be unique and valid format
   - Password minimum length: 8 characters
   - Gender must be either 'male' or 'female'
   - Birth date must be a valid date format
   - All fields are required unless specified otherwise

4. **Response Format**
   - All responses follow the standard format:
     ```json
     {
         "status": boolean,
         "message": string,
         "data": object|null,
         "errors": object|null
     }
     ```

5. **HTTP Status Codes**
   - 200: Success
   - 400: Bad Request
   - 401: Unauthorized
   - 422: Validation Error
   - 500: Server Error
