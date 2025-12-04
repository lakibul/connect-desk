# ConnectDesk - WhatsApp & Facebook Integration Setup Guide

## Overview
ConnectDesk now requires user registration to send messages and includes WhatsApp Business API integration. All messages sent through WhatsApp will be forwarded to the specified phone number (+8801983427887).

## Features Implemented

### 1. User Registration System
- Users must register with name, email, and phone number before sending messages
- Login functionality for returning users
- All messages are associated with registered users

### 2. WhatsApp Business API Integration
- Messages sent through WhatsApp platform are forwarded to +8801983427887
- Real-time message delivery using WhatsApp Business API
- Comprehensive error handling and logging

### 3. Enhanced Frontend
- Modal-based registration and login system
- Professional UI with improved chat widgets
- Real-time chat functionality with typing indicators
- Responsive design for mobile and desktop

## Setup Instructions

### 1. Database Setup
Run the migrations to add required fields:
```bash
php artisan migrate
```

### 2. WhatsApp Business API Setup

#### Prerequisites
- Facebook Developer Account
- WhatsApp Business Account
- Verified Business Profile

#### Configuration Steps

1. **Create Facebook App**
   - Go to [Facebook Developers](https://developers.facebook.com/)
   - Create a new app with WhatsApp Business API
   - Note down your App ID

2. **Get WhatsApp Business API Credentials**
   - Add WhatsApp product to your app
   - Get your Access Token from the API Setup
   - Get your Phone Number ID from the WhatsApp Manager

3. **Configure Environment Variables**
   Copy `.env.example` to `.env` and update these values:
   ```env
   WHATSAPP_ACCESS_TOKEN=your_actual_access_token
   WHATSAPP_PHONE_NUMBER_ID=your_actual_phone_number_id
   WHATSAPP_WEBHOOK_SECRET=your_webhook_secret
   WHATSAPP_WEBHOOK_URL=https://yourdomain.com/api/whatsapp/webhook
   ```

4. **Verify Phone Number**
   - In Facebook Business Manager, verify +8801983427887 as a recipient
   - This number will receive all WhatsApp messages

### 3. API Endpoints

#### User Management
- `POST /api/users/register` - Register new user
- `POST /api/users/login` - User login  
- `POST /api/users/check` - Check if user exists

#### Message Sending
- `POST /api/messages` - Send message (requires user_id)

#### Request Format
```json
{
    "message": "Hello, I need help!",
    "platform": "whatsapp",
    "user_id": 1
}
```

### 4. Frontend Integration

The frontend now includes:
- User registration modal
- Login functionality
- Authenticated chat sessions
- WhatsApp integration status
- Professional UI design

### 5. Testing

1. **Test User Registration**
   - Open the frontend page
   - Click WhatsApp or Facebook chat button
   - Complete registration form
   - Verify user is stored in database

2. **Test Message Sending**
   - Send a message through WhatsApp chat
   - Check if message appears in admin dashboard
   - Verify WhatsApp message is sent to +8801983427887

3. **Test WhatsApp Integration**
   - Ensure access token is valid
   - Check application logs for WhatsApp API responses
   - Verify message delivery to target number

### 6. Troubleshooting

#### WhatsApp API Issues
- Check access token validity
- Verify phone number ID is correct
- Ensure target number (+8801983427887) is verified in Business Manager
- Check application logs for detailed error messages

#### Database Issues
- Run `php artisan migrate:fresh --seed` to reset database
- Ensure phone_number field is added to users table
- Check user_id and platform fields in messages table

#### Frontend Issues
- Clear browser localStorage if authentication issues occur
- Check browser console for JavaScript errors
- Verify CSRF token is properly configured

### 7. Security Considerations

- All API endpoints include proper validation
- CSRF protection is enabled for all forms
- User passwords are hashed using Laravel's built-in security
- WhatsApp webhook includes signature verification

### 8. Next Steps

To further enhance the system:
- Implement real-time notifications using WebSockets
- Add message history synchronization
- Create admin interface for managing users
- Add message templates for WhatsApp
- Implement message status tracking

## Support

For WhatsApp Business API setup issues:
- [WhatsApp Business API Documentation](https://developers.facebook.com/docs/whatsapp)
- [Facebook Developer Community](https://developers.facebook.com/community/)

For Laravel-specific issues:
- [Laravel Documentation](https://laravel.com/docs)
