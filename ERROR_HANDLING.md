# Error Handling System

This document describes the comprehensive error handling system implemented in the Laravel application.

## Overview

The error handling system provides:
- Custom error pages for different HTTP status codes
- Comprehensive error logging
- Admin notifications for critical errors
- User-friendly error messages
- Debug mode support

## Error Pages

### Available Error Pages

1. **404.blade.php** - Page Not Found
   - Clean, modern design with gradient background
   - "Go Home" and "Go Back" buttons
   - User-friendly messaging

2. **403.blade.php** - Access Forbidden
   - Permission denied messaging
   - "Go Home" and "Login" buttons
   - Orange gradient theme

3. **419.blade.php** - Page Expired (CSRF Token)
   - Session expiration messaging
   - "Go Home" and "Refresh Page" buttons
   - Light blue gradient theme

4. **500.blade.php** - Internal Server Error
   - Server error messaging
   - "Go Home" and "Try Again" buttons
   - Red gradient theme

5. **503.blade.php** - Service Unavailable
   - Maintenance mode messaging
   - Estimated completion time
   - Pink gradient theme

6. **generic.blade.php** - Generic Error Page
   - Fallback for unhandled errors
   - Dynamic error code and message display
   - Blue gradient theme

### Error Page Features

- **Responsive Design**: All error pages are mobile-friendly
- **Consistent Styling**: Modern gradient backgrounds with clean typography
- **User Actions**: Relevant action buttons for each error type
- **Branding**: Consistent with application theme
- **Accessibility**: Proper semantic HTML and ARIA labels

## Error Handling Service

### ErrorHandlingService Class

Located at `app/Services/ErrorHandlingService.php`, this service provides:

#### Methods

1. **logError(Throwable $exception, Request $request = null)**
   - Logs comprehensive error details
   - Includes context like URL, method, IP, user agent
   - Records user ID if authenticated

2. **notifyAdmins(Throwable $exception, Request $request = null)**
   - Sends notifications for critical errors (500+)
   - Can be extended to send emails
   - Logs critical errors separately

3. **getUserFriendlyMessage(Throwable $exception)**
   - Returns user-friendly error messages
   - Maps status codes to appropriate messages
   - Handles unknown errors gracefully

4. **shouldReportError(Throwable $exception)**
   - Determines if error should be reported
   - Ignores common exceptions (404, MethodNotAllowed)
   - Configurable reporting rules

## Configuration

### Bootstrap Configuration

The error handling is configured in `bootstrap/app.php`:

```php
->withExceptions(function (Exceptions $exceptions): void {
    $exceptions->render(function (Throwable $e, Request $request) {
        // Error logging and notification
        // Custom error page rendering
        // Debug mode handling
    });
})
```

### Environment Configuration

- **APP_DEBUG**: Controls whether detailed error information is shown
- **LOG_LEVEL**: Controls error logging verbosity
- **MAIL_ADMIN_EMAIL**: Email for critical error notifications (optional)

## Testing Error Pages

### Test Routes (Debug Mode Only)

When `APP_DEBUG=true`, test routes are available:

- `/test-error/404` - Test 404 page
- `/test-error/403` - Test 403 page
- `/test-error/419` - Test 419 page
- `/test-error/500` - Test 500 page
- `/test-error/503` - Test 503 page

**Note**: Remove these routes in production by setting `APP_DEBUG=false`.

## Error Logging

### Log Structure

Errors are logged with the following structure:

```json
{
    "message": "Error message",
    "file": "File path",
    "line": "Line number",
    "trace": "Stack trace",
    "url": "Request URL",
    "method": "HTTP method",
    "ip": "Client IP",
    "user_agent": "User agent",
    "user_id": "Authenticated user ID"
}
```

### Log Levels

- **ERROR**: General application errors
- **CRITICAL**: Critical errors requiring immediate attention
- **DEBUG**: Detailed error information (debug mode only)

## Customization

### Adding New Error Pages

1. Create a new Blade template in `resources/views/errors/`
2. Add the status code handling in `bootstrap/app.php`
3. Update the ErrorHandlingService if needed

### Modifying Error Messages

Edit the `getUserFriendlyMessage()` method in `ErrorHandlingService.php` to customize error messages.

### Adding Email Notifications

1. Create a Mailable class for error notifications
2. Update the `notifyAdmins()` method in `ErrorHandlingService.php`
3. Configure SMTP settings in `.env`

## Security Considerations

- Error pages don't expose sensitive information
- Stack traces are only shown in debug mode
- User input is sanitized in error messages
- Admin notifications are rate-limited

## Performance

- Error pages are lightweight and fast-loading
- Minimal JavaScript dependencies
- Optimized CSS with inline styles
- Cached error page templates

## Maintenance

### Regular Tasks

1. Monitor error logs for patterns
2. Update error messages based on user feedback
3. Review and update ignored exceptions
4. Test error pages after application updates

### Monitoring

- Set up log monitoring for critical errors
- Monitor error page response times
- Track user experience metrics

## Troubleshooting

### Common Issues

1. **Error pages not showing**: Check `APP_DEBUG` setting and view cache
2. **Logs not appearing**: Verify log configuration and permissions
3. **Notifications not sending**: Check mail configuration and SMTP settings

### Debug Steps

1. Enable debug mode temporarily
2. Check Laravel logs in `storage/logs/`
3. Verify error page templates exist
4. Test with different error codes

## Future Enhancements

- Real-time error monitoring dashboard
- User feedback collection on error pages
- A/B testing for error page designs
- Integration with external monitoring services
- Automated error categorization and routing
