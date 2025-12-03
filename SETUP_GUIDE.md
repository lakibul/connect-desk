# ConnectDesk - WhatsApp & Facebook Chat Integration

A Laravel-based chat integration system that allows users to send messages via WhatsApp and Facebook Messenger widgets on the frontend, with an admin panel to manage and respond to messages in real-time.

## Features

- ✅ User Authentication System
- ✅ Frontend Chat Widgets (WhatsApp & Facebook)
- ✅ Admin Dashboard for Message Management
- ✅ Real-time Message Updates (Polling-based)
- ✅ Conversation Management
- ✅ Message Counter & Unread Notifications
- ✅ Platform Filtering (WhatsApp/Facebook)
- ✅ Responsive Design

## Requirements

- PHP 8.2+
- MySQL/MariaDB
- Composer
- Node.js & NPM
- Laragon (or any local server environment)

## Installation & Setup

### Step 1: Database Configuration

1. Open `.env` file in the project root
2. Configure your database settings:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=connect_desk
DB_USERNAME=root
DB_PASSWORD=
```

3. Create the database in MySQL:
   - Open phpMyAdmin or MySQL CLI
   - Create a new database named `connect_desk`

### Step 2: Install Dependencies

Open terminal in project directory and run:

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Generate application key
php artisan key:generate
```

### Step 3: Run Migrations

The migrations have already been run, but if you need to reset:

```bash
# Fresh migration (warning: this will drop all tables)
php artisan migrate:fresh

# Or just run migrations
php artisan migrate
```

### Step 4: Create Admin User

The admin user has been created with these credentials:

```
Email: admin@connectdesk.com
Password: password
```

If you need to create another admin user:

```bash
php artisan db:seed --class=AdminUserSeeder
```

Or manually via tinker:

```bash
php artisan tinker

# Then run:
User::create([
    'name' => 'Your Name',
    'email' => 'your@email.com',
    'password' => Hash::make('your-password'),
    'role' => 'admin'
]);
```

### Step 5: Build Assets

```bash
# Build for development
npm run dev

# Or build for production
npm run build
```

### Step 6: Start the Development Server

```bash
php artisan serve
```

The application will be available at: `http://127.0.0.1:8000`

## Usage Guide

### For Visitors (Frontend)

1. Visit the homepage: `http://127.0.0.1:8000`
2. Click on the WhatsApp or Facebook chat button in the bottom-right corner
3. Type your message and click Send
4. Your messages will be stored and sent to the admin panel

### For Administrators

1. Navigate to: `http://127.0.0.1:8000/login`
2. Login with admin credentials:
   - Email: `admin@connectdesk.com`
   - Password: `password`
3. Click "Admin Dashboard" in the navigation
4. You'll see all conversations with unread counters
5. Click on any conversation to view messages
6. Reply directly from the admin panel
7. Messages auto-refresh every 3 seconds

## Features Breakdown

### 1. Authentication Module ✅

- Laravel Breeze for authentication
- Role-based access (admin/user)
- Admin middleware for protecting routes
- Login/Register functionality

### 2. Frontend Chat Widgets ✅

**Location:** Bottom-right corner of homepage

**WhatsApp Widget:**
- Green button with WhatsApp icon
- Click to open chat interface
- Send messages without WhatsApp app

**Facebook Messenger Widget:**
- Blue button with Messenger icon
- Click to open chat interface
- Send messages without Facebook app

**Features:**
- Anonymous user identification
- Message persistence via localStorage
- Auto-scroll to latest messages
- Real-time updates (3-second polling)

### 3. Admin Dashboard ✅

**URL:** `/admin/dashboard`

**Features:**
- View all conversations
- Filter by platform (All/WhatsApp/Facebook)
- Unread message counter
- Conversation list with latest message preview
- Click to view full conversation
- Reply to messages
- Auto-refresh conversations (5 seconds)
- Auto-refresh active conversation (3 seconds)
- Mark messages as read automatically

## Database Structure

### Users Table
- id
- name
- email
- password
- role (user/admin)
- timestamps

### Conversations Table
- id
- visitor_name (nullable)
- visitor_email (nullable)
- visitor_id (unique identifier)
- platform (whatsapp/facebook)
- unread_count
- last_message_at
- timestamps

### Messages Table
- id
- conversation_id (foreign key)
- message (text)
- sender_type (visitor/admin)
- is_read (boolean)
- timestamps

## API Endpoints

### Public Endpoints

```
POST /api/messages
- Send message from frontend
- Body: { message, platform, visitor_id }
```

### Admin Endpoints (Auth Required)

```
GET  /admin/dashboard
- Admin dashboard view

GET  /admin/api/conversations
- Get all conversations with unread count

GET  /admin/api/conversations/{id}/messages
- Get all messages for a conversation

POST /admin/api/conversations/{id}/messages
- Send reply as admin
- Body: { message }

POST /admin/api/conversations/{id}/mark-read
- Mark conversation messages as read
```

## File Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AdminDashboardController.php
│   │   ├── MessageController.php
│   │   └── ConversationController.php
│   └── Middleware/
│       └── AdminMiddleware.php
├── Models/
│   ├── User.php
│   ├── Conversation.php
│   └── Message.php

database/
├── migrations/
│   ├── 2025_12_03_101707_add_role_to_users_table.php
│   ├── 2025_12_03_101739_create_conversations_table.php
│   └── 2025_12_03_101759_create_messages_table.php
└── seeders/
    └── AdminUserSeeder.php

resources/
└── views/
    ├── frontend.blade.php
    └── admin/
        └── dashboard.blade.php

routes/
└── web.php
```

## Customization

### Change Admin Credentials

Edit `database/seeders/AdminUserSeeder.php` before running the seeder.

### Modify Polling Intervals

**Frontend (frontend.blade.php):**
```javascript
// Line ~180 - Change 3000 to desired milliseconds
setInterval(() => { ... }, 3000);
```

**Admin Dashboard (admin/dashboard.blade.php):**
```javascript
// Conversations refresh - Line ~200
setInterval(loadConversations, 5000);

// Messages refresh - Line ~203
setInterval(() => { ... }, 3000);
```

### Styling

- Frontend uses custom CSS with Tailwind classes
- Admin dashboard uses Tailwind CSS
- Chat widgets are fully customizable via CSS classes

## Troubleshooting

### Issue: "SQLSTATE[HY000] [1049] Unknown database"
**Solution:** Create the database in MySQL/phpMyAdmin

### Issue: "419 Page Expired" when sending messages
**Solution:** Clear browser cache or check CSRF token is included

### Issue: Messages not appearing in admin panel
**Solution:** 
- Check browser console for errors
- Verify database connection
- Check that migrations ran successfully

### Issue: Can't login as admin
**Solution:**
- Run seeder: `php artisan db:seed --class=AdminUserSeeder`
- Check user table has role='admin'

## Future Enhancements

For production deployment, consider:

1. **WebSocket Implementation** (Laravel Echo + Pusher/Socket.io)
   - Replace polling with real-time WebSocket connections
   - Instant message delivery
   
2. **WhatsApp Business API Integration**
   - Connect to actual WhatsApp Business API
   - Send messages to customer's WhatsApp
   
3. **Facebook Messenger API Integration**
   - Connect to Facebook Graph API
   - Real Facebook Messenger integration
   
4. **File Upload Support**
   - Allow sending images/files
   - Media storage and display
   
5. **Typing Indicators**
   - Show when admin/user is typing
   
6. **Message Search**
   - Search through conversations
   
7. **Admin Notifications**
   - Browser notifications for new messages
   - Sound alerts

## Testing

### Test the System

1. **Test Frontend Chat:**
   - Open homepage in incognito window
   - Click WhatsApp button
   - Send a test message
   - Switch to Facebook and send another message

2. **Test Admin Panel:**
   - Login as admin in normal window
   - Go to admin dashboard
   - Verify messages appear
   - Reply to messages
   - Check unread counter updates

3. **Test Multiple Conversations:**
   - Open multiple incognito windows
   - Send messages from different "users"
   - Verify all conversations appear in admin panel

## Security Notes

- Change default admin password immediately in production
- Use HTTPS in production
- Implement rate limiting for message endpoints
- Add CAPTCHA for spam prevention
- Sanitize all user inputs
- Use environment variables for sensitive data

## Support

For issues or questions:
1. Check the troubleshooting section
2. Review Laravel logs: `storage/logs/laravel.log`
3. Check browser console for JavaScript errors

## License

This project is open-source and available under the MIT License.

---

**Congratulations!** Your ConnectDesk chat integration system is now fully functional on your local server. 🎉
