# Forms Testing Guide

## Quick Test Checklist

### Contact Form
1. Go to `contacts.html`
2. Fill in Contact Form tab
3. Submit form
4. ✅ Should show success modal
5. ✅ Check Cross Admin → Contacts to see submission

### Prayer Request Form
1. Go to `contacts.html`
2. Click Prayer Request tab
3. Fill in prayer request (name/email optional)
4. Submit form
5. ✅ Should show success modal with heart icon
6. ✅ Check Cross Admin → Prayer Requests to see submission

### Feedback Form
1. Go to `contacts.html`
2. Scroll to Feedback section
3. Fill in feedback form
4. Select feedback type
5. Submit form
6. ✅ Should show success modal with star icon
7. ✅ Check Cross Admin → Feedback to see submission

### Newsletter Form
1. Go to `contacts.html`
2. Scroll to footer
3. Enter email in newsletter form
4. Submit
5. ✅ Should show success message inline
6. ✅ Check database `newsletter_subscriptions` table

## Database Verification

Run these SQL queries to verify:

```sql
-- Check contact inquiries
SELECT * FROM contact_inquiries ORDER BY created_at DESC LIMIT 5;

-- Check prayer requests
SELECT * FROM prayer_requests ORDER BY created_at DESC LIMIT 5;

-- Check feedback
SELECT * FROM feedback ORDER BY created_at DESC LIMIT 5;

-- Check newsletter subscriptions
SELECT * FROM newsletter_subscriptions ORDER BY subscribed_at DESC LIMIT 5;
```

## Email Testing

After configuring PHPMailer:

1. Submit each form
2. Check recipient email inbox
3. Verify email content is correct
4. Check PHP error log if emails don't arrive

## Common Issues

### Form not submitting
- Check browser console for JavaScript errors
- Verify form action URL is correct
- Check PHP error logs

### Success modal not showing
- Verify Bootstrap JS is loaded
- Check modal HTML exists
- Verify JavaScript is not blocked

### Database not saving
- Check database connection
- Verify tables exist
- Check PHP error logs

### Email not sending
- Verify PHPMailer is installed
- Check SMTP credentials
- Review PHP error logs
- Note: Forms still work without email

