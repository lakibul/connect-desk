# WhatsApp Business API Setup Guide

## Overview
This guide will help you get the required WhatsApp Business API credentials and configure them for localhost development.

## Prerequisites
- Facebook Developer Account
- WhatsApp Business Account
- Phone number for verification
- Valid business documentation (for production)

## Step 1: Create Facebook Developer Account

1. **Go to Facebook Developers**
   - Visit: https://developers.facebook.com/
   - Click "Get Started"
   - Log in with your Facebook account or create one

2. **Verify Your Account**
   - Add phone number and email verification
   - Complete the developer verification process

## Step 2: Create a Facebook App

1. **Create New App**
   - Go to https://developers.facebook.com/apps/
   - Click "Create App"
   - Select "Business" as app type
   - Fill in app details:
     - App Name: "ConnectDesk WhatsApp"
     - App Contact Email: your email
     - Business Account: Select or create one

2. **Add WhatsApp Product**
   - In your app dashboard, click "Add Product"
   - Find "WhatsApp" and click "Set Up"

## Step 3: Get WhatsApp Business API Credentials

### A. Get Access Token (WHATSAPP_ACCESS_TOKEN)

1. **Go to WhatsApp > Getting Started**
2. **Generate Access Token**
   - In the "Temporary access token" section
   - Copy the access token (valid for 24 hours for testing)
   - For permanent token, you'll need to generate a System User token

3. **For Permanent Token (Production)**
   - Go to Business Settings > System Users
   - Create a new system user
   - Add WhatsApp Business Management permissions
   - Generate access token

### B. Get Phone Number ID (WHATSAPP_PHONE_NUMBER_ID)

1. **In WhatsApp > Getting Started**
2. **Find "From" phone number section**
3. **Copy the Phone Number ID** (not the phone number itself)
   - It looks like: `123456789012345`

### C. Set Webhook (for receiving messages)

1. **Go to WhatsApp > Configuration**
2. **Webhook URL**: `https://yourdomain.com/api/whatsapp/webhook`
3. **Verify Token**: Create a random string (this becomes your WHATSAPP_WEBHOOK_SECRET)

## Step 4: Localhost Development Setup

### Option 1: Using ngrok (Recommended for Testing)

1. **Install ngrok**
   ```bash
   # Download from https://ngrok.com/
   # Or using chocolatey on Windows:
   choco install ngrok
   ```

2. **Expose Local Server**
   ```bash
   # Run this in a separate terminal
   ngrok http 80
   ```
   
3. **Use ngrok URL**
   - Copy the https URL (e.g., `https://abc123.ngrok.io`)
   - Update webhook URL: `https://abc123.ngrok.io/api/whatsapp/webhook`

### Option 2: Skip Webhook for Send-Only (Simplest)

For localhost development where you only send messages (no receiving), you can:

1. **Set dummy webhook values in .env:**
   ```env
   WHATSAPP_WEBHOOK_SECRET=dummy_secret_for_localhost
   WHATSAPP_WEBHOOK_URL=https://localhost/api/whatsapp/webhook
   ```

2. **This allows sending messages without webhook setup**

## Step 5: Configure Your .env File

Replace the placeholder values in your `.env` file:

```env
# WhatsApp Business API Configuration
WHATSAPP_ACCESS_TOKEN=EAAxxxxxxxxxxxxxxxxx  # Your actual access token
WHATSAPP_PHONE_NUMBER_ID=123456789012345   # Your phone number ID
WHATSAPP_WEBHOOK_SECRET=your_random_secret  # Your webhook verification token
WHATSAPP_WEBHOOK_URL=https://yourdomain.com/api/whatsapp/webhook  # Your webhook URL
```

## Step 6: Add Test Phone Number

1. **In WhatsApp > API Setup**
2. **Add Recipient Phone Number**
   - Click "Manage phone number list"
   - Add `+8801983427887` to the recipient list
   - Send verification code to that number

## Step 7: Test the Integration

1. **Test via ConnectDesk Frontend**
   - Register/login to your ConnectDesk
   - Click user avatar → "Test WhatsApp Integration"
   - Check if message is received on +8801983427887

2. **Test via API directly**
   ```bash
   curl -X POST http://connect-desk.test/api/test-whatsapp \
     -H "Content-Type: application/json" \
     -d "{}"
   ```

## Step 8: Troubleshooting

### Common Issues:

1. **"Access token invalid"**
   - Regenerate access token (temporary tokens expire in 24 hours)
   - For production, use System User tokens

2. **"Phone number not verified"**
   - Add +8801983427887 to your WhatsApp Business Manager
   - Verify the number with OTP

3. **"Webhook verification failed"**
   - Ensure webhook URL is accessible from internet
   - Use ngrok for localhost testing
   - Match the verify token exactly

4. **"Recipient not in allowed list"**
   - In development, add phone numbers to recipient list
   - In production with approved business, all numbers work

### Check Logs:

```bash
# View Laravel logs for debugging
tail -f storage/logs/laravel.log
```

## Production Considerations

1. **Business Verification**
   - Complete Meta Business verification
   - Submit WhatsApp Business API application
   - Get approved for production access

2. **Permanent Access Token**
   - Create System User in Business Manager
   - Generate long-lived access token
   - Store securely (not in version control)

3. **Webhook Security**
   - Implement proper webhook signature verification
   - Use HTTPS only
   - Validate incoming requests

4. **Rate Limits**
   - WhatsApp has messaging limits
   - Start with 250 messages/day for new numbers
   - Limits increase based on message quality

## Quick Start for Localhost (Send-Only)

If you just want to test sending messages on localhost:

1. Get temporary access token from Facebook Developer Console
2. Get phone number ID from WhatsApp setup
3. Add +8801983427887 to recipient list in Facebook console
4. Update .env with real access token and phone number ID
5. Set webhook values to dummy values
6. Test using the "Test WhatsApp Integration" button

## Example Working .env

```env
# Working example (replace with your actual values)
WHATSAPP_ACCESS_TOKEN=EAABsbCS1234abcd5678efgh...  # From Facebook Console
WHATSAPP_PHONE_NUMBER_ID=15550123456  # From WhatsApp setup
WHATSAPP_WEBHOOK_SECRET=my_random_secret_123  # Your random string
WHATSAPP_WEBHOOK_URL=https://abc123.ngrok.io/api/whatsapp/webhook  # ngrok URL
```

## Support Resources

- **Facebook Developers Documentation**: https://developers.facebook.com/docs/whatsapp
- **WhatsApp Business API Guide**: https://developers.facebook.com/docs/whatsapp/getting-started
- **Meta Business Help**: https://www.facebook.com/business/help
- **ngrok Documentation**: https://ngrok.com/docs

The most important credentials for basic testing are:
1. **WHATSAPP_ACCESS_TOKEN** - From Facebook Developer Console
2. **WHATSAPP_PHONE_NUMBER_ID** - From WhatsApp API Setup

Once you have these, you can test message sending immediately!