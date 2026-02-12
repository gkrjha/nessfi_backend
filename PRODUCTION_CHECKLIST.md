# Production Readiness Checklist

## ✅ Fixed Issues

1. **User Model** - Added missing fillable fields and casts
2. **Routes** - Added all authentication endpoints
3. **Notification** - Implements ShouldQueue for async email sending
4. **Rate Limiting** - Added to login endpoint (5 attempts per minute)

## ⚠️ Required Before Production

### 1. Environment Configuration (.env)
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate new `APP_KEY` for production
- [ ] Update `APP_URL` to your production domain
- [ ] Configure proper mail credentials (Gmail or transactional service)
- [ ] Set `QUEUE_CONNECTION=database` (already set)
- [ ] Configure database credentials

### 2. Mail Configuration
**Current Issue:** Gmail is NOT recommended for production

**Recommended Solutions:**
- Use **SendGrid**, **Mailgun**, **Amazon SES**, or **Postmark**
- These services are designed for transactional emails
- Better deliverability and monitoring

**If using Gmail (not recommended):**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-16-char-app-password
MAIL_FROM_ADDRESS=your-email@gmail.com
```

### 3. Queue Worker Setup
Your emails are queued, so you MUST run:
```bash
php artisan queue:work --daemon
```

**Production Setup:**
- Use **Supervisor** to keep queue worker running
- Configure in `/etc/supervisor/conf.d/laravel-worker.conf`

### 4. Database
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Create jobs table: `php artisan queue:table && php artisan migrate`
- [ ] Backup strategy in place

### 5. Security
- [ ] Add CORS configuration
- [ ] Set up HTTPS/SSL certificate
- [ ] Configure Sanctum for your domain in `config/sanctum.php`
- [ ] Add rate limiting to registration endpoint
- [ ] Implement CSRF protection if needed
- [ ] Review and set proper file permissions

### 6. API Improvements Needed

**Add to routes/api.php:**
```php
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/user-register', [AuthController::class, 'register']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
});
```

**Add resend verification code endpoint:**
```php
Route::post('/resend-verification', [AuthController::class, 'resendVerification']);
```

### 7. Error Handling
- [ ] Set up error logging (Sentry, Bugsnag, etc.)
- [ ] Configure `LOG_CHANNEL` appropriately
- [ ] Add try-catch blocks in controllers

### 8. Performance
- [ ] Enable caching: `php artisan config:cache`
- [ ] Enable route caching: `php artisan route:cache`
- [ ] Enable view caching: `php artisan view:cache`
- [ ] Set up Redis for cache/sessions (optional but recommended)

### 9. Testing
- [ ] Test registration flow end-to-end
- [ ] Test email delivery
- [ ] Test verification code expiration
- [ ] Test rate limiting
- [ ] Test with invalid inputs

### 10. Monitoring
- [ ] Set up application monitoring
- [ ] Monitor queue jobs
- [ ] Monitor email delivery rates
- [ ] Set up alerts for failures

## Commands to Run

```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Run migrations
php artisan migrate --force

# Create jobs table for queue
php artisan queue:table
php artisan migrate

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start queue worker (use Supervisor in production)
php artisan queue:work --daemon --tries=3
```

## Missing Features to Consider

1. **Password Reset** - Not implemented yet
2. **Resend Verification Code** - Not implemented yet
3. **Email Change** - Not implemented yet
4. **Account Deletion** - Not implemented yet
5. **API Documentation** - Consider adding Swagger/OpenAPI
6. **Logging** - Add comprehensive logging for debugging

## Current Vulnerabilities

1. No rate limiting on registration (can be spammed)
2. No CAPTCHA on registration
3. Verification codes are predictable (6-digit numbers)
4. No IP-based blocking for abuse
5. No email validation beyond format check
