# 🚀 Quick Start Guide - ConnectDesk

## Step-by-Step Setup for Local Server

### ✅ Prerequisites Check
- [x] Laragon installed and running
- [x] MySQL service started
- [x] PHP 8.2+ available
- [x] Composer installed
- [x] Node.js installed

---

## 📋 5-Minute Setup

### 1️⃣ Database Setup (1 minute)

```bash
# The database migrations have already been run!
# Admin user has been created with:
# Email: admin@connectdesk.com
# Password: password
```

✅ **Already Done:** Migrations executed, admin user seeded.

---

### 2️⃣ Start the Application (2 minutes)

**Option A: Using Terminal (Recommended)**

Open 2 terminal windows in your project directory:

**Terminal 1 - Laravel Server:**
```bash
php artisan serve
```
Output: `Server running on [http://127.0.0.1:8000]`

**Terminal 2 - Vite Dev Server:**
```bash
npm run dev
```
Output: Vite server will start on port 5173

**Option B: Using Composer Script**
```bash
composer dev
```
This runs both server, queue, and vite concurrently.

---

### 3️⃣ Access the Application (30 seconds)

Open your browser and visit:

🌐 **Frontend:** http://127.0.0.1:8000
- See the chat widgets in bottom-right corner
- Click WhatsApp or Facebook buttons to test

🔐 **Admin Login:** http://127.0.0.1:8000/login
- Email: `admin@connectdesk.com`
- Password: `password`

📊 **Admin Dashboard:** http://127.0.0.1:8000/admin/dashboard
- View all messages
- Reply to users
- See real-time updates

---

## 🧪 Testing the System (2 minutes)

### Test 1: Send a Message as Visitor

1. Open http://127.0.0.1:8000 in **incognito mode**
2. Click the **WhatsApp button** (green, bottom-right)
3. Type: "Hello, I need help!"
4. Click **Send**
5. ✅ Message should appear in the chat

### Test 2: View Message as Admin

1. Open http://127.0.0.1:8000/login in **normal browser**
2. Login with admin credentials
3. Click **Admin Dashboard** in navigation
4. ✅ You should see the conversation with unread counter
5. Click on the conversation
6. ✅ Message "Hello, I need help!" should be visible

### Test 3: Reply as Admin

1. In admin dashboard, type a reply: "Hi! How can I help you?"
2. Click **Send**
3. ✅ Reply should appear in blue on the right side

### Test 4: Facebook Messenger

1. Go back to incognito window (frontend)
2. Click the **Facebook button** (blue)
3. Send another test message
4. ✅ Check admin panel for new conversation

---

## 🎯 What You Get

### Frontend Features
- ✅ WhatsApp chat widget (green button)
- ✅ Facebook Messenger widget (blue button)
- ✅ No external apps needed
- ✅ Messages stored in database
- ✅ Auto-refresh every 3 seconds

### Admin Panel Features
- ✅ View all conversations
- ✅ Filter by platform (All/WhatsApp/Facebook)
- ✅ Unread message counter
- ✅ Reply to messages
- ✅ Auto-refresh conversations (5 sec)
- ✅ Auto-refresh messages (3 sec)
- ✅ Professional dashboard design

---

## 🔧 Common Commands

```bash
# Start development server
php artisan serve

# Start Vite dev server
npm run dev

# Run both (server + vite + queue)
composer dev

# Create new admin user
php artisan db:seed --class=AdminUserSeeder

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Check logs
tail -f storage/logs/laravel.log
```

---

## 📱 URLs Cheat Sheet

| Page | URL | Access |
|------|-----|--------|
| Homepage | http://127.0.0.1:8000 | Public |
| Login | http://127.0.0.1:8000/login | Public |
| Register | http://127.0.0.1:8000/register | Public |
| Admin Dashboard | http://127.0.0.1:8000/admin/dashboard | Admin Only |
| User Dashboard | http://127.0.0.1:8000/dashboard | Authenticated |

---

## 🎨 Demo Workflow

1. **Visitor sends message** → Chat widget on homepage
2. **Message stored** → Database (conversations & messages tables)
3. **Admin receives** → Appears in admin dashboard
4. **Admin replies** → Sent from dashboard
5. **Visitor receives** → Auto-refresh shows reply
6. **Real-time updates** → Both sides refresh automatically

---

## ⚡ Quick Troubleshooting

### Problem: Can't access admin dashboard
**Solution:** Make sure you're logged in as admin user

### Problem: Chat widgets not showing
**Solution:** Run `npm run dev` or `npm run build`

### Problem: Messages not saving
**Solution:** Check database connection in `.env`

### Problem: "419 Page Expired"
**Solution:** Refresh page (CSRF token expired)

---

## 🎉 You're All Set!

Your WhatsApp & Facebook chat integration is now **fully functional** on your local server!

**Next Steps:**
1. Test all features
2. Customize styling/colors
3. Add more admin users if needed
4. Review `SETUP_GUIDE.md` for advanced features

**Need Help?**
- Check `SETUP_GUIDE.md` for detailed documentation
- Review `storage/logs/laravel.log` for errors
- Check browser console for JavaScript errors

---

**Happy Chatting! 💬**
