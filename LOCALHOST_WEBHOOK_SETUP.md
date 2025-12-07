# Localhost Webhook Setup for WhatsApp

## Problem
Meta/Facebook cannot reach `localhost` or `127.0.0.1` URLs because they are not accessible from the internet.

## Solution: Use ngrok to Expose Localhost

### Step 1: Install ngrok

**Option A: Using Chocolatey (Recommended)**
```powershell
choco install ngrok
```

**Option B: Manual Download**
1. Go to https://ngrok.com/download
2. Download the Windows version
3. Extract to a folder (e.g., C:\ngrok)
4. Add to PATH or run from that folder

### Step 2: Create ngrok Account (Free)
1. Go to https://ngrok.com/
2. Sign up for a free account
3. Get your authtoken from https://dashboard.ngrok.com/get-started/your-authtoken
4. Run: `ngrok authtoken YOUR_AUTH_TOKEN`

### Step 3: Start Your Laravel Server
```powershell
# Make sure Laragon is running and your site is accessible at:
# http://connect-desk.test
```

### Step 4: Expose Localhost with ngrok
```powershell
# Open a new PowerShell/CMD window and run:
ngrok http connect-desk.test:80
```

### Step 5: Copy the HTTPS URL
After running ngrok, you'll see output like:
```
Forwarding   https://abc123def456.ngrok-free.app -> http://connect-desk.test:80
```

**Copy the HTTPS URL**: `https://abc123def456.ngrok-free.app`

### Step 6: Update Facebook Webhook Configuration

In the Meta Developer Console (the screenshot you shared):

1. **Callback URL**: 
   ```
   https://abc123def456.ngrok-free.app/api/whatsapp/webhook
   ```
   (Replace `abc123def456` with your actual ngrok URL)

2. **Verify Token**: 
   ```
   connectdesk_webhook_2025
   ```
   (This matches your WHATSAPP_WEBHOOK_SECRET in .env)

3. Click **"Verify and save"**

### Step 7: Subscribe to Webhook Events

After verification succeeds:
1. Click **"Manage"** next to Webhook fields
2. Subscribe to these events:
   - ✅ messages
   - ✅ message_status (optional)
3. Click **"Subscribe"**

## Important Notes

⚠️ **ngrok URL changes every time you restart it** (on free plan)
- You'll need to update the webhook URL in Facebook each time
- Consider ngrok paid plan for persistent URLs

⚠️ **Keep ngrok running**
- Don't close the ngrok terminal window
- Your webhook will stop working if ngrok stops

⚠️ **Test after setup**
- Send a WhatsApp message to your test number
- Check Laravel logs: `storage/logs/laravel.log`

## Alternative for Send-Only Testing

If you **only want to send messages** (not receive):
- You can skip the webhook setup entirely
- Leave webhook URL as dummy value
- Your send functionality will work fine
- You won't be able to receive messages from WhatsApp users

## Testing Your Setup

### Test Message Sending (Works without webhook):
```bash
# In your browser, login to ConnectDesk
# Click avatar → "Test WhatsApp Integration"
# Check if +8801983427887 receives the message
```

### Test Webhook (Needs ngrok):
```bash
# Send a WhatsApp message to your test number from +8801983427887
# Check Laravel logs to see if webhook received it
tail -f storage/logs/laravel.log
```

## Quick Summary

For **localhost development**:

1. **For SENDING messages only** (easiest):
   - Leave webhook URL as is
   - Just use your access token and phone number ID
   - You can send messages immediately

2. **For RECEIVING messages** (needs ngrok):
   - Install ngrok
   - Run: `ngrok http connect-desk.test:80`
   - Use ngrok HTTPS URL in Facebook webhook
   - Keep ngrok running

**Recommendation**: Start with send-only testing, then add webhook later if needed.
