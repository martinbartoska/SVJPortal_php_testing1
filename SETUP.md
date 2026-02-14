# Security Setup Guide

## Quick Start (5 minutes)

### 1. Update Database
```bash
mysql -u root -p < database/schema.sql
```

### 2. Configure Database Connection
Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
define('DB_NAME', 'svj_portal');
```

### 3. Start Server
```bash
php -S localhost:8000
```

### 4. Test the System

#### Register a New User
Visit: http://localhost:8000/register.html

Fill in:
- Full Name: John Doe
- Email: john@example.com
- Flat Number: A-101 (optional)
- Phone: (optional)
- Password: securepass123
- Confirm: securepass123

Click "Create Account" → Redirects to login

#### Login
Visit: http://localhost:8000/login.html

Login with:
- Email: john@example.com
- Password: securepass123

Redirects to: Dashboard (/index.php)

#### Test Forgot Password
Visit: http://localhost:8000/forgot-password.html

Enter email: john@example.com

Gets confirmation message (in testing, token shown)

#### Test Password Reset
Visit reset link with token: http://localhost:8000/reset-password.html?token=XXX

Enter new password and confirm

#### Change Password
From Profile page (/profile.html):
- Click "Profile" link in navbar
- Scroll to "Change Password" section
- Enter current and new passwords
- Click "Change Password"

#### Logout
Click "Logout" in navbar → Redirected to login page

## New Features

### ✅ User Authentication
- **Login Page** (`/login.html`) - Email and password login
- **Registration Page** (`/register.html`) - Create new account
- **Profile Page** (`/profile.html`) - View and edit account (requires login)

### ✅ Password Management
- **Forgot Password** (`/forgot-password.html`) - Request password reset
- **Reset Password** (`/reset-password.html`) - Reset with token
- **Change Password** - Available in profile page

### ✅ Protected Content
All these pages now require login:
- Dashboard (`/index.php`)
- Surveys (`/surveys.html`)
- Quizzes (`/quizzes.html`)
- Profile (`/profile.html`)
- Maintenance (`/maintenance.html`)

### ✅ Session Management
- Automatic logout after 1 hour of inactivity
- Session validation on every page load
- Secure session data storage

## API Endpoints

### Authentication Endpoints

**Register User**
```
POST /api/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "flat_number": "A-101",
  "phone": "555-1234"
}
```

**Login**
```
POST /api/auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

**Logout**
```
POST /api/auth/logout
```

**Get Current User (requires login)**
```
GET /api/auth/me
```

**Request Password Reset**
```
POST /api/auth/forgot-password
Content-Type: application/json

{
  "email": "john@example.com"
}
```

**Reset Password**
```
POST /api/auth/reset-password
Content-Type: application/json

{
  "token": "token_here",
  "password": "newpassword123"
}
```

**Change Password (requires login)**
```
POST /api/auth/change-password
Content-Type: application/json

{
  "current_password": "oldpass123",
  "new_password": "newpass123"
}
```

**Validate Reset Token**
```
GET /api/auth/validate-token?token=token_here
```

## User Roles & Permissions

### Resident
- Respond to surveys
- Take quizzes
- Request maintenance
- View own profile

### Staff
- Respond to surveys
- Take quizzes
- Manage maintenance requests
- View own profile

### Admin
- Create surveys & quizzes
- Manage users
- Create content
- View reports
- All resident features

## Security Best Practices Implemented

✅ **Password Security**
- Minimum 8 characters
- PHP password_hash() encryption
- Secure password reset tokens

✅ **Session Security**
- 1-hour session timeout
- Session validation on each request
- Automatic session renewal
- Secure session destruction on logout

✅ **Database Security**
- Prepared statements (prevents SQL injection)
- Type-safe parameter binding
- Indexed queries for performance

✅ **Data Protection**
- Input validation on all forms
- Email format validation
- Duplicate prevention
- Account status checking

## Common Tasks

### Create Test Admin Account

Use the API:
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Admin User",
    "email": "admin@example.com",
    "password": "admin123456"
  }'
```

Then manually update role in database:
```sql
UPDATE users SET role = 'admin' WHERE email = 'admin@example.com';
```

### Reset User Password

Email-based (for testing, shows token):
```bash
curl -X POST http://localhost:8000/api/auth/forgot-password \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com"}'
```

### Block/Unblock User Account

```sql
-- Block account
UPDATE users SET is_active = 0 WHERE id = 1;

-- Unblock account
UPDATE users SET is_active = 1 WHERE id = 1;
```

### View All Users

```sql
SELECT id, name, email, role, is_active, last_login FROM users;
```

## Troubleshooting

### "Invalid email or password"
- Check email exists in database
- Check account is active (is_active = 1)
- Verify password is correct

### "Session expired"
- Session timeout is 1 hour by default
- Log in again
- Increase timeout in `src/helpers/Auth.php` if needed

### "Reset token has expired"
- Reset tokens valid for 1 hour
- Request a new password reset

### Can't logout
- Try clearing browser cookies
- Check browser console for errors

### "Email already registered"
- Email must be unique
- Try different email
- Check if you can "Forgot Password" instead

## What's Next?

After security setup, consider:

1. **Email Integration**
   - Send actual password reset emails
   - Email verification for new accounts
   - Account notifications

2. **Production Security**
   - Enable HTTPS
   - Configure secure cookies
   - Add rate limiting
   - Implement account lockout
   - Enable activity logging

3. **Additional Features**
   - Two-factor authentication (2FA)
   - OAuth integration (Google, Facebook)
   - Social login
   - Account recovery codes

4. **Monitoring**
   - Activity logs
   - Failed login attempts
   - Account changes
   - Security alerts

## Files Modified/Created

### New Files
- `src/helpers/Auth.php` - Authentication system
- `src/controllers/AuthController.php` - Auth endpoints
- `login.html` - Login page
- `register.html` - Registration page
- `forgot-password.html` - Password reset request
- `reset-password.html` - Password reset form
- `profile.html` - User profile page
- `api.php` - API router
- `.htaccess` - URL rewriting
- `SECURITY.md` - Security documentation
- `SETUP.md` - This file

### Updated Files
- `database/schema.sql` - Added auth fields to users table
- `config/database.php` - Database connection
- `src/models/User.php` - Added auth methods
- `index.php` - Added authentication check
- `surveys.html` - Added auth check
- `quizzes.html` - Added auth check

## Support

For issues or questions:
1. Check SECURITY.md for technical details
2. Review API endpoints above
3. Check database for data integrity
4. Enable error logging in config
5. Review browser console for JavaScript errors

---
**Version**: 1.1 with Security  
**Date**: February 2025
