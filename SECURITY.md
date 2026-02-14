# SVJ Portal - Security Implementation Guide

## Overview
This document describes the complete security implementation added to SVJ Portal, including authentication, authorization, password management, and protected resources.

## Authentication System

### Features Implemented

✅ **User Registration**
- Email validation
- Password strength requirements (8+ characters)
- Duplicate email prevention
- User role assignment (resident by default)

✅ **User Login**
- Email and password verification
- Session management
- Last login tracking
- Account status checking (active/inactive)

✅ **Password Management**
- Secure password hashing using PHP's `password_hash()`
- Password reset with email verification token
- Password reset tokens with expiration (1 hour)
- Change password for logged-in users

✅ **Session Security**
- PHP session-based authentication
- Session timeout (1 hour default)
- Session validation on each request
- Automatic session renewal

✅ **Access Control**
- Role-based access control (RBAC)
- Three user roles: admin, resident, staff
- Permission checking per feature
- Protected API endpoints

## Database Schema Updates

### Users Table Enhancement
```sql
- reset_token VARCHAR(255) - Password reset token
- reset_token_expires DATETIME - Token expiration
- is_active BOOLEAN - Account status
- email_verified BOOLEAN - Email verification status
- last_login DATETIME - Last login timestamp
```

### Indexes Added
```sql
- idx_email - Fast email lookups
- idx_reset_token - Token validation
```

## File Structure

### Core Security Files
```
src/
├── helpers/
│   └── Auth.php              # Main authentication class
├── controllers/
│   └── AuthController.php    # Authentication endpoints
└── models/
    └── User.php              # Enhanced user model
```

### Pages & Routes
```
Public Pages (No Auth Required):
- /login.html                 # Login page
- /register.html              # Registration page
- /forgot-password.html       # Password reset request
- /reset-password.html        # Password reset form

Protected Pages (Auth Required):
- /index.php                  # Dashboard
- /surveys.html               # Surveys
- /quizzes.html               # Quizzes
- /profile.html               # User profile
- /maintenance.html           # Maintenance requests

API Endpoints:
- /api/auth/login             # POST - User login
- /api/auth/register          # POST - User registration
- /api/auth/logout            # POST - User logout
- /api/auth/forgot-password   # POST - Request password reset
- /api/auth/reset-password    # POST - Reset password with token
- /api/auth/change-password   # POST - Change password (auth required)
- /api/auth/me               # GET - Get current user (auth required)
- /api/auth/validate-token   # GET - Validate reset token
```

## Usage Examples

### Login
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123"
  }'

Response:
{
  "success": true,
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "role": "resident"
  }
}
```

### Register
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jane Smith",
    "email": "jane@example.com",
    "password": "securepass123",
    "flat_number": "B-205",
    "phone": "+1234567890"
  }'
```

### Request Password Reset
```bash
curl -X POST http://localhost:8000/api/auth/forgot-password \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com"}'

Response includes:
{
  "success": true,
  "token": "abc123def456..."  # Only for testing
}
```

### Reset Password
```bash
curl -X POST http://localhost:8000/api/auth/reset-password \
  -H "Content-Type: application/json" \
  -d '{
    "token": "abc123def456...",
    "password": "newpassword123"
  }'
```

### Get Current User
```bash
curl http://localhost:8000/api/auth/me

Response:
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "role": "resident"
  }
}
```

## Security Features

### 1. Password Security
- **Hashing**: Uses PHP's `password_hash()` with PASSWORD_DEFAULT algorithm
- **Verification**: Uses `password_verify()` for secure comparison
- **Strength**: Enforces 8+ character minimum
- **Reset Tokens**: Generated with `random_bytes(32)`, hex-encoded

### 2. Session Management
- **Session Start**: Automatic on first request to Auth class
- **Timeout**: 1 hour of inactivity (configurable)
- **Validation**: User data verified on each request
- **Destruction**: Complete session cleanup on logout

### 3. SQL Injection Prevention
- **Prepared Statements**: All database queries use parameterized statements
- **Type Binding**: Automatic type detection (int, string, float)
- **Escaping**: Database layer handles all escaping

### 4. CSRF Protection
- **Session Tokens**: Session IDs used for request validation
- **SameSite Cookies**: PHP sessions have built-in protection

### 5. Email Verification
- **Reset Tokens**: One-time use tokens
- **Expiration**: Tokens expire after 1 hour
- **Validation**: Verified before allowing password reset

### 6. Access Control
- **Authentication Checks**: `Auth::requireLogin()` for protected pages
- **Role-based**: `Auth::requireRole()` for role-specific access
- **Permission System**: Methods to check specific permissions

### 7. Account Security
- **Status Checks**: Active/inactive account verification
- **Last Login Tracking**: Monitor account access
- **Email Uniqueness**: Prevent duplicate registrations

## Password Reset Flow

1. **User Requests Reset**
   - User submits email on `/forgot-password.html`
   - System generates unique reset token
   - Token expires in 1 hour
   - User receives reset link (in production would be email)

2. **User Resets Password**
   - User follows reset link to `/reset-password.html?token=xxx`
   - Frontend validates token via API
   - User enters new password
   - System updates password and clears token
   - User can login with new password

## Protected Resource Access

### All Protected Pages Check:
```javascript
// Executed on page load
async checkAuth() {
    const response = await fetch('/api/auth/me');
    if (!response.ok) {
        window.location.href = '/login.html';
    }
}
```

### PHP Protected Routes Check:
```php
// In PHP pages
Auth::requireLogin();

// In controllers
Auth::requireRole(['admin', 'staff']);
```

## Configuration

### Session Timeout
Edit `src/helpers/Auth.php` line 12:
```php
private $sessionTimeout = 3600; // seconds (1 hour)
```

### Database Connection
Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'svj_portal');
```

## Testing the System

### Setup Test User
```sql
-- Create admin account (password will be hashed automatically)
INSERT INTO users (name, email, password, role, is_active) 
VALUES (
  'Admin User',
  'admin@example.com',
  '$2y$10$...' /* hashed password */,
  'admin',
  1
);
```

### Test Endpoints
1. **Register**: `POST /api/auth/register`
2. **Login**: `POST /api/auth/login`
3. **View Profile**: `GET /api/auth/me`
4. **Change Password**: `POST /api/auth/change-password`
5. **Logout**: `POST /api/auth/logout`

## Production Checklist

- [ ] Remove token from `requestPasswordReset()` response
- [ ] Implement email sending for password resets
- [ ] Add email verification for new accounts
- [ ] Enable HTTPS/SSL
- [ ] Configure secure cookie settings
- [ ] Set strong session timeout
- [ ] Add rate limiting on login attempts
- [ ] Implement account lockout after failed attempts
- [ ] Add activity logging
- [ ] Configure CORS properly
- [ ] Review and update session configuration in php.ini
- [ ] Enable HTTPOnly and Secure flags on cookies

## Troubleshooting

### Login Not Working
```
Check:
1. Database is running
2. Credentials in config/database.php
3. User exists in database (is_active = 1)
4. Password is correct (case-sensitive)
```

### Session Expires Too Quickly
```
Edit src/helpers/Auth.php line 12:
private $sessionTimeout = 3600; // Increase this value
```

### Password Reset Link Invalid
```
Check:
1. Token hasn't expired (1 hour limit)
2. Database reset_token matches
3. Database reset_token_expires > NOW()
```

### Can't Access Protected Pages
```
Check:
1. User is logged in (check session)
2. Auth::requireLogin() is called
3. User role has required permissions
```

## Security Notes

1. **Never log passwords** - Log authentication events, not passwords
2. **Use HTTPS** - Always use HTTPS in production
3. **Rate limiting** - Add rate limiting on auth endpoints in production
4. **Email verification** - Implement email verification for production
5. **Account recovery** - Keep backup email addresses for account recovery
6. **Audit logging** - Log all authentication attempts
7. **Session storage** - Consider moving sessions to Redis in production
8. **API keys** - If using API keys, implement API key rotation

## Support & Questions

For implementation details, refer to:
- [PHP Sessions](https://www.php.net/manual/en/book.session.php)
- [Password Hashing](https://www.php.net/manual/en/function.password-hash.php)
- [OWASP Authentication](https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html)
