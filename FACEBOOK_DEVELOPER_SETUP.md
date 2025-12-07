# Step-by-Step Guide: Adding WhatsApp Product & Getting API Keys

## Step 1: Create Facebook Developer Account

1. **Go to Facebook Developers**
   - Visit: https://developers.facebook.com/
   - Click **"Get Started"** button (top right)

2. **Login or Register**
   - Use your existing Facebook account
   - If no Facebook account, create one first

3. **Complete Developer Registration**
   - Accept Developer Terms
   - Verify your phone number
   - Verify your email address

## Step 2: Create a New Facebook App

1. **Navigate to Apps**
   - Go to https://developers.facebook.com/apps/
   - Click **"Create App"** button (green button)

2. **Select App Type**
   - Choose **"Business"** (this is important for WhatsApp)
   - Click **"Next"**

3. **Fill App Details**
   - **App Name**: `ConnectDesk WhatsApp` (or any name you prefer)
   - **App Contact Email**: Your email address
   - **Business Account**: Select existing or click "Create a new Business Account"
   - Click **"Create App"**

4. **Complete Security Check**
   - Complete the captcha/security verification
   - Wait for app creation to complete

## Step 3: Add WhatsApp Product to Your App

1. **In Your App Dashboard**
   - You'll see your new app dashboard
   - Look for **"Add a Product"** section

2. **Find WhatsApp Product**
   - Scroll down to find **"WhatsApp"** product
   - Click **"Set Up"** button under WhatsApp

3. **WhatsApp Setup Page**
   - You'll be redirected to WhatsApp configuration page
   - This page contains all the keys you need

## Step 4: Get Your Access Token (WHATSAPP_ACCESS_TOKEN)

1. **Navigate to Getting Started**
   - In left sidebar, click **"WhatsApp" → "Getting Started"**

2. **Find Access Token Section**
   - Look for **"Temporary access token"** section
   - You'll see a long token starting with `EAA...`

3. **Copy Access Token**
   - Click the **"Copy"** button next to the token
   - **This token expires in 24 hours** (good for testing)
   - Save it as your `WHATSAPP_ACCESS_TOKEN`

   ```env
   WHATSAPP_ACCESS_TOKEN=EAABsbCS1234abcd5678efgh...
   ```

## Step 5: Get Your Phone Number ID (WHATSAPP_PHONE_NUMBER_ID)

1. **On Same Page (Getting Started)**
   - Scroll down to find **"Send messages"** section

2. **Locate Phone Number**
   - You'll see **"From:"** field with a phone number
   - Below it, there's a **Phone Number ID**

3. **Copy Phone Number ID**
   - Copy the **Phone Number ID** (NOT the phone number itself)
   - It's a long number like: `123456789012345`
   - Save it as your `WHATSAPP_PHONE_NUMBER_ID`

   ```env
   WHATSAPP_PHONE_NUMBER_ID=123456789012345
   ```

## Step 6: Add Test Recipient Number (+8801983427887)

1. **Still on Getting Started Page**
   - Find **"To:"** field in the send messages section

2. **Add Recipient Number**
   - Click **"Manage phone number list"** or **"Edit"**
   - Click **"Add phone number"**
   - Enter: `+8801983427887`
   - Click **"Add phone number"**

3. **Verify the Number**
   - WhatsApp will send verification code to +8801983427887
   - Enter the 6-digit code when prompted
   - Click **"Verify"**

## Step 7: Test Message Sending (Optional)

1. **Send Test Message**
   - In the **"Send messages"** section
   - Type a test message like: `Hello from ConnectDesk!`
   - Click **"Send message"**

2. **Verify Message Received**
   - Check if +8801983427887 received the message
   - This confirms your setup is working

## Step 8: Configure Webhook (For Localhost - Simple Setup)

1. **Navigate to Configuration**
   - In left sidebar, click **"WhatsApp" → "Configuration"**

2. **Set Webhook URL**
   - **Callback URL**: `https://yourdomain.com/api/whatsapp/webhook`
   - For localhost testing, use: `http://localhost/api/whatsapp/webhook`
   - **Verify Token**: Enter any random string (e.g., `localhost_secret_123`)

3. **Save Webhook Settings**
   - Click **"Verify and save"**
   - For localhost, this might fail (that's okay for send-only testing)

## Step 9: Update Your .env File

Replace the placeholder values in your `.env` file:

```env
# WhatsApp Business API Configuration
WHATSAPP_ACCESS_TOKEN=EAABsbCS1234abcd5678efgh...  # From Step 4
WHATSAPP_PHONE_NUMBER_ID=123456789012345  # From Step 5
WHATSAPP_WEBHOOK_SECRET=localhost_secret_123  # From Step 8
WHATSAPP_WEBHOOK_URL=http://localhost/api/whatsapp/webhook  # For localhost
```

## Step 10: Test in ConnectDesk

1. **Clear Laravel Config Cache**
   ```bash
   php artisan config:clear
   ```

2. **Test via Frontend**
   - Go to your ConnectDesk frontend
   - Register/Login as a user
   - Click your avatar → "Test WhatsApp Integration"
   - Check if +8801983427887 receives the message

3. **Check Logs if Issues**
   ```bash
   tail -f storage/logs/laravel.log
   ```

## Visual Guide - What to Look For:

### In Facebook Developer Console:
```
App Dashboard
├── Add a Product
│   └── WhatsApp [Set Up] ← Click this
│
└── WhatsApp (after setup)
    ├── Getting Started
    │   ├── Temporary access token: EAAxxxx... ← Copy this
    │   └── Phone Number ID: 123456... ← Copy this
    │
    └── Configuration
        ├── Webhook URL ← Set this
        └── Verify Token ← Set this
```

### Your .env Should Look Like:
```env
WHATSAPP_ACCESS_TOKEN=EAABsbCS1234...  ← Real token from Facebook
WHATSAPP_PHONE_NUMBER_ID=123456789012345  ← Real ID from Facebook
WHATSAPP_WEBHOOK_SECRET=any_random_string  ← Your choice
WHATSAPP_WEBHOOK_URL=http://localhost/api/whatsapp/webhook  ← For localhost
```

## Common Issues & Solutions:

### 1. "Access token invalid"
- **Solution**: Regenerate the temporary access token (expires every 24 hours)
- **Location**: WhatsApp → Getting Started → Temporary access token

### 2. "Phone number not found"
- **Solution**: Make sure you copied the Phone Number ID, not the phone number
- **Example**: Use `123456789012345`, not `+15551234567`

### 3. "Recipient not allowed"
- **Solution**: Add +8801983427887 to your recipient list and verify it
- **Location**: WhatsApp → Getting Started → Manage phone number list

### 4. "Webhook verification failed"
- **Solution**: For localhost testing, you can ignore this error
- **Alternative**: Use ngrok to expose localhost to internet

## For Production (Later):

### Generate Permanent Access Token:
1. **Go to Business Settings**
   - Visit: https://business.facebook.com/settings/
   - Navigate to **System Users**

2. **Create System User**
   - Click **"Add"** → **"System User"**
   - Give it a name like "ConnectDesk WhatsApp"
   - Select **"Admin"** role

3. **Generate Access Token**
   - Click on the system user
   - Click **"Generate New Token"**
   - Select your WhatsApp app
   - Choose **"whatsapp_business_management"** permission
   - Copy the permanent token

This permanent token won't expire and should be used in production.

## Summary - What You Need:

From Facebook Developer Console, you need these 2 main values:
1. **WHATSAPP_ACCESS_TOKEN** - From "Getting Started" page
2. **WHATSAPP_PHONE_NUMBER_ID** - From "Getting Started" page

The webhook values can be dummy for localhost testing:
3. **WHATSAPP_WEBHOOK_SECRET** - Any random string you choose
4. **WHATSAPP_WEBHOOK_URL** - Can be dummy for send-only testing

Once you have the access token and phone number ID, you can immediately test message sending!
