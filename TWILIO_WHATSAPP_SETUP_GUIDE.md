# Twilio WhatsApp Integration — Complete Setup Guide for ConnectDesk

> **Document Version:** 1.0  
> **Last Updated:** February 17, 2026  
> **Integration Type:** Twilio WhatsApp Messaging API

---

## Table of Contents

1. [Overview — Why Twilio for WhatsApp](#1-overview--why-twilio-for-whatsapp)
2. [Twilio vs Meta WhatsApp Cloud API](#2-twilio-vs-meta-whatsapp-cloud-api)
3. [Prerequisites](#3-prerequisites)
4. [Step 1 — Create Twilio Account](#4-step-1--create-twilio-account)
5. [Step 2 — Set Up WhatsApp Sandbox (Testing)](#5-step-2--set-up-whatsapp-sandbox-testing)
6. [Step 3 — Configure Environment Variables](#6-step-3--configure-environment-variables)
7. [Step 4 — Configure Webhooks](#7-step-4--configure-webhooks)
8. [Step 5 — Test Your Integration](#8-step-5--test-your-integration)
9. [Step 6 — Move to Production (WhatsApp Business Profile)](#9-step-6--move-to-production-whatsapp-business-profile)
10. [Step 7 — Configure Admin Credentials](#10-step-7--configure-admin-credentials)
11. [Sending Messages from Admin Panel](#11-sending-messages-from-admin-panel)
12. [Webhook Payload Reference](#12-webhook-payload-reference)
13. [Twilio WhatsApp Pricing](#13-twilio-whatsapp-pricing)
14. [Template Messages (Content API)](#14-template-messages-content-api)
15. [Troubleshooting](#15-troubleshooting)
16. [API Reference](#16-api-reference)
17. [Migration from Meta Cloud API](#17-migration-from-meta-cloud-api)

---

## 1. Overview — Why Twilio for WhatsApp

Twilio provides a simplified WhatsApp messaging API with several advantages:

✅ **Easier Setup** — No complex business verification required for sandbox  
✅ **Simple Authentication** — Account SID + Auth Token (no expiring access tokens)  
✅ **Better Documentation** — Clear API docs and SDKs  
✅ **Unified Platform** — Manage SMS, WhatsApp, Voice in one place  
✅ **Pay-as-you-go** — No monthly fees, pay only for messages sent  
✅ **Template Management** — Easier template creation and approval via Twilio Console  

### What ConnectDesk Can Do with Twilio WhatsApp

```
┌─────────────────────────────────────────────────────────────────┐
│              ConnectDesk + Twilio WhatsApp Flow                  │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  1. Visitor fills form on website                               │
│      └─> ConnectDesk sends WhatsApp to admin via Twilio         │
│      └─> Admin receives WhatsApp message                        │
│                                                                 │
│  2. Visitor sends WhatsApp to your Twilio number                │
│      └─> Twilio webhook → ConnectDesk stores message            │
│      └─> Admin sees message in dashboard                        │
│                                                                 │
│  3. Admin replies from dashboard                                │
│      └─> ConnectDesk → Twilio → Visitor's WhatsApp              │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 2. Twilio vs Meta WhatsApp Cloud API

| Feature | Meta Cloud API | Twilio API |
|---------|---------------|------------|
| **Setup Complexity** | High (business verification, app creation) | Low (account + enable WhatsApp) |
| **Access Token** | Expires every 24h (temp) or needs System User | Never expires (Account SID + Auth Token) |
| **Testing** | 5 test numbers max | Unlimited sandbox users |
| **Webhook Format** | JSON | Form data (URL-encoded) |
| **Template Approval** | Via Meta Business Manager | Via Twilio Console |
| **Phone Number** | Meta provides test number | Twilio provides sandbox number |
| **Pricing** | Conversation-based | Per-message pricing |
| **Production Requirements** | Business verification mandatory | Business profile approval needed |
| **SDK** | GuzzleHTTP (manual API calls) | Official Twilio PHP SDK |

---

## 3. Prerequisites

Before you start, ensure you have:

- [x] A Twilio account (free trial or paid)
- [x] A phone number with WhatsApp installed
- [x] Access to your Laravel server (local or production)
- [x] A domain with HTTPS (for production webhooks)
- [x] Basic understanding of Laravel and environment variables

---

## 4. Step 1 — Create Twilio Account

### 4.1 Sign Up

1. Go to **https://www.twilio.com/try-twilio**
2. Click **"Sign up and start building"**
3. Fill in:
   - **First Name**
   - **Last Name**
   - **Email**
   - **Password**
4. Verify your email address
5. Verify your phone number via SMS code

### 4.2 Get Your Account Credentials

After signing up and logging in:

1. Go to **Console Dashboard**: https://console.twilio.com
2. You'll see your credentials displayed:

```
┌───────────────────────────────────────────┐
│  Account Info                             │
├───────────────────────────────────────────┤
│  Account SID:  ACxxxxxxxxxxxxxxxxxxxx      │
│  Auth Token:   Click "View" to reveal     │
└───────────────────────────────────────────┘
```

3. Click **"Show"** on Auth Token to reveal it
4. **Copy both values** — you'll need them in `.env`

**Important:** Treat these like passwords. Never commit them to Git.

### 4.3 Free Trial Limitations

| Free Trial | Paid Account |
|-----------|-------------|
| $15.50 free credit | Pay-as-you-go |
| Can only message verified numbers | Can message any number |
| Messages include "Sent from a Twilio trial account" | No trial message |
| Limited to sandbox WhatsApp | Can get production WhatsApp number |

To upgrade, add a payment method: **Console → Billing → Add Payment Method**

---

## 5. Step 2 — Set Up WhatsApp Sandbox (Testing)

The WhatsApp Sandbox allows you to test WhatsApp messaging without a dedicated WhatsApp Business number.

### 5.1 Enable WhatsApp Sandbox

1. Go to **https://console.twilio.com/us1/develop/sms/try-it-out/whatsapp-learn**
2. Or: **Console → Messaging → Try it out → Send a WhatsApp message**
3. You'll see:

```
┌────────────────────────────────────────────────────────────┐
│  Twilio Sandbox for WhatsApp                               │
├────────────────────────────────────────────────────────────┤
│                                                            │
│  Your Sandbox Number:  whatsapp:+1 415 523 8886           │
│                                                            │
│  To activate this sandbox, send this code from WhatsApp:  │
│                                                            │
│      join <your-code>                                      │
│                                                            │
│  Example: join gravity-river                              │
│                                                            │
└────────────────────────────────────────────────────────────┘
```

### 5.2 Join the Sandbox

1. **Open WhatsApp** on your phone
2. **Add Twilio's sandbox number** to your contacts: `+1 415 523 8886`
3. **Send a message** to that number with your join code:
   ```
   join gravity-river
   ```
   (Replace `gravity-river` with YOUR unique code shown in the console)

4. You'll receive a confirmation message:
   ```
   Twilio Sandbox: ✅ You are all set! 
   Reply with the word "playground" to try sending this number a message.
   ```

### 5.3 Get Your Sandbox WhatsApp Number

Your sandbox WhatsApp number is:
```
whatsapp:+14155238886
```

This is the number you'll use in `.env` as `TWILIO_WHATSAPP_FROM`.

---

## 6. Step 3 — Configure Environment Variables

### 6.1 Add Twilio Credentials to `.env`

Open `c:\laragon\www\connect-desk\.env` and add:

```env
# ============================================
# TWILIO WHATSAPP CONFIGURATION
# ============================================

# Your Twilio Account SID (from Console Dashboard)
TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

# Your Twilio Auth Token (from Console Dashboard)
TWILIO_AUTH_TOKEN=your_auth_token_here

# Twilio WhatsApp Sandbox Number (or your production number)
# Format: +14155238886 (include + and country code, NO "whatsapp:" prefix)
TWILIO_WHATSAPP_FROM=+14155238886

# Admin phone number to receive visitor messages
# Format: +8801XXXXXXXXX (include + and country code)
TWILIO_TARGET_PHONE_NUMBER=+8801604509006
```

### 6.2 Where to Find Each Value

| Variable | Where to Find It |
|----------|-----------------|
| `TWILIO_ACCOUNT_SID` | Console Dashboard → Account Info |
| `TWILIO_AUTH_TOKEN` | Console Dashboard → Account Info → Click "Show" |
| `TWILIO_WHATSAPP_FROM` | Messaging → Try WhatsApp → Sandbox number |
| `TWILIO_TARGET_PHONE_NUMBER` | Your admin's WhatsApp phone number |

### 6.3 Clear Config Cache

After updating `.env`:

```bash
cd c:\laragon\www\connect-desk
php artisan config:clear
php artisan cache:clear
```

---

## 7. Step 4 — Configure Webhooks

Webhooks allow Twilio to send incoming WhatsApp messages to your ConnectDesk server.

### 7.1 For Local Development (ngrok or localhost tunneling)

Since Twilio needs a public HTTPS URL, you need to expose your local server:

#### Option A: Using ngrok (Recommended)

1. **Download ngrok**: https://ngrok.com/download
2. **Install and authenticate** (follow ngrok setup)
3. **Run ngrok**:
   ```bash
   ngrok http 8000
   ```
   (Adjust port if your Laravel runs on a different port)

4. **Copy the HTTPS URL** from ngrok output:
   ```
   Forwarding   https://abc123.ngrok.io -> http://localhost:8000
   ```

Your webhook URL will be:
```
https://abc123.ngrok.io/api/whatsapp/webhook
```

#### Option B: Using Laravel Expose

If you prefer Laravel's Expose tool:

```bash
php artisan serve
expose share http://127.0.0.1:8000
```

### 7.2 Configure Webhook in Twilio Console

1. Go to **https://console.twilio.com/us1/develop/sms/settings/whatsapp-sandbox**
2. Or: **Messaging → Try WhatsApp → Sandbox settings**
3. Under **"When a message comes in"**:
   - Enter your webhook URL: `https://your-domain.com/api/whatsapp/webhook`
   - Set HTTP method: **POST**
4. Click **"Save"**

### 7.3 For Production

For production, use your actual domain:

```
https://yourdomain.com/api/whatsapp/webhook
```

**Requirements:**
- ✅ Must be HTTPS (SSL certificate)
- ✅ Must be publicly accessible
- ✅ Must respond within 20 seconds
- ✅ Must return HTTP 200

---

## 8. Step 5 — Test Your Integration

### 8.1 Test Webhook Reception

1. **Send a WhatsApp message** from your phone to the Twilio sandbox number
2. **Check Laravel logs**:
   ```bash
   tail -f storage/logs/laravel.log
   ```

You should see:
```
[2026-02-17 07:45:12] local.INFO: Received Twilio WhatsApp webhook
[2026-02-17 07:45:12] local.INFO: Twilio WhatsApp message received
```

3. **Check Admin Dashboard** at `/admin/dashboard` — you should see the conversation

### 8.2 Test Sending Messages

#### Method 1: Use API Endpoint (Debug)

```bash
POST http://localhost:8000/api/test-whatsapp
```

Or using cURL:
```bash
curl -X POST http://localhost:8000/api/test-whatsapp
```

You should receive a WhatsApp message on your phone.

#### Method 2: Send from Admin Dashboard

1. Log in as admin: `/login`
2. Go to Admin Dashboard: `/admin/dashboard`
3. Select a conversation or start a new one
4. Type a message and send

### 8.3 Test WhatsApp Configuration

Check your configuration:

```bash
GET http://localhost:8000/api/whatsapp-debug
```

Response:
```json
{
  "service": "Twilio WhatsApp",
  "configuration": {
    "account_sid": "ACxxxxxxxx...",
    "auth_token": "your_auth_t...",
    "whatsapp_from": "+14155238886",
    "target_phone": "+8801604509006",
    "config_status": "configured",
    "config_message": "All Twilio credentials are configured"
  }
}
```

---

## 9. Step 6 — Move to Production (WhatsApp Business Profile)

Once you've tested the sandbox, you can apply for a production WhatsApp Business number.

### 9.1 Requirements

- ✅ Verified Twilio account (not trial)
- ✅ Business information (name, address, website)
- ✅ Facebook Business Manager account
- ✅ WhatsApp Business profile

### 9.2 Request WhatsApp Business Profile

1. Go to **https://console.twilio.com/us1/develop/sms/senders/whatsapp-senders**
2. Click **"Request a WhatsApp sender"**
3. Follow the guided setup:
   - **Link Facebook Business Manager**
   - **Create/Select WhatsApp Business Account**
   - **Submit business information**
   - **Complete display name approval**

### 9.3 Get Your Production WhatsApp Number

After approval:

1. Go to **WhatsApp Senders** in Twilio Console
2. Copy your **WhatsApp-enabled phone number**
3. Update `.env`:
   ```env
   TWILIO_WHATSAPP_FROM=+1234567890  # Your production number
   ```

### 9.4 Update Webhook URL

Update the webhook URL for your production number:

1. Go to **WhatsApp Senders** → Click your number
2. Under **Messaging Configuration** → **Webhook URL**
3. Enter: `https://yourdomain.com/api/whatsapp/webhook`
4. Save

---

## 10. Step 7 — Configure Admin Credentials

Each admin can have their own Twilio credentials for sending messages.

### 10.1 Via Admin Settings Page

1. Log in as admin
2. Go to **Settings** (`/admin/settings`)
3. Fill in:
   - **Twilio Account SID**: Your Account SID
   - **Twilio Auth Token**: Your Auth Token
   - **Twilio WhatsApp From**: Your WhatsApp sender number
   - **Phone Number**: Your personal WhatsApp number
4. Click **"Save Settings"**

### 10.2 Via Database (Manual)

Update the `users` table:

```sql
UPDATE users 
SET 
  twilio_account_sid = 'ACxxxxxxxxxxxxxxxx',
  twilio_auth_token = 'your_auth_token',
  twilio_whatsapp_from = '+14155238886',
  phone_number = '+8801604509006'
WHERE id = 1; -- Admin user ID
```

### 10.3 Credentials Priority

ConnectDesk uses credentials in this order:

1. **Admin user's credentials** (from `users` table)
2. **Global credentials** (from `.env` / `config/services.php`)

If an admin has their own credentials configured, those are used. Otherwise, global credentials from `.env` are used.

---

## 11. Sending Messages from Admin Panel

### 11.1 Send Text Message

From the admin dashboard:

1. Select a conversation
2. Type your message
3. Click **"Send"**

The message is sent via:
```
MessageController@sendAdminMessage
  → TwilioWhatsAppService@sendMessageForUser
    → Twilio API
```

### 11.2 Send Template Message

**Note:** Template messages in Twilio require the **Content API** and approved templates.

To send a template:

1. Select a conversation
2. Choose **"Template"** option
3. Enter **Template SID** (e.g., `HXxxxxxxxxxxxxxxxxxxxx`)
4. Click **"Send Template"**

### 11.3 Start New Conversation

1. Go to Admin Dashboard
2. Click **"Start New Conversation"** or similar button
3. Enter phone number (e.g., `+8801XXXXXXXXX`)
4. Choose to send:
   - **Text message**
   - **Template message**
5. Click **"Start"**

ConnectDesk will:
- Validate the phone number
- Create a conversation
- Send the initial message
- Display the conversation

---

## 12. Webhook Payload Reference

### 12.1 Incoming Message Webhook (Twilio → ConnectDesk)

When someone sends a WhatsApp message to your Twilio number, Twilio sends a POST request to your webhook URL.

**Request Format:** `application/x-www-form-urlencoded` (form data, not JSON)

**Sample Payload:**

```
MessageSid=SMxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
AccountSid=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
MessagingServiceSid=
From=whatsapp:+8801604509006
To=whatsapp:+14155238886
Body=Hello, I need help!
NumMedia=0
NumSegments=1
SmsStatus=received
ProfileName=John Doe
```

### 12.2 Media Message

If the message contains media (image, video, document):

```
MessageSid=SMxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
From=whatsapp:+8801604509006
To=whatsapp:+14155238886
Body=Check this out!
NumMedia=1
MediaUrl0=https://api.twilio.com/2010-04-01/Accounts/.../Media/MExxxx
MediaContentType0=image/jpeg
```

### 12.3 How ConnectDesk Handles It

The webhook controller (`WhatsAppWebhookController@handleWebhook`) does:

1. **Extracts** sender phone from `From` parameter
2. **Removes** `whatsapp:` prefix
3. **Creates/finds** a conversation
4. **Stores** the message in the database
5. **Updates** conversation timestamp and unread count
6. **Returns** an empty TwiML response (required by Twilio)

---

## 13. Twilio WhatsApp Pricing

### 13.1 Pricing Model

Twilio charges **per message** (not per conversation like Meta).

| Message Type | Price Range (USD) |
|-------------|------------------|
| **Inbound** (customer to business) | FREE |
| **Outbound** (business to customer) | $0.005 - $0.12 per message |
| **Template** (approved templates) | $0.005 - $0.12 per message |

> **Prices vary by country.** Check current pricing: https://www.twilio.com/en-us/whatsapp/pricing

### 13.2 Bangladesh Pricing Example

| Direction | Price (USD) |
|-----------|------------|
| Inbound (customer → your business) | FREE |
| Outbound (your business → customer) | ~$0.025 per message |

### 13.3 Monthly Cost Estimation

**Example usage:**

| Activity | Messages/month | Cost per message | Total |
|----------|---------------|-----------------|-------|
| Customer support (inbound) | 500 | $0.00 | $0.00 |
| Admin replies (outbound) | 500 | $0.025 | $12.50 |
| Follow-up messages | 200 | $0.025 | $5.00 |
| **Total** | **1,200** | | **$17.50** |

### 13.4 Free Trial Credit

Twilio provides **$15.50** free credit for trial accounts. This gives you approximately:

- 620 outbound messages to Bangladesh numbers
- Unlimited inbound messages (always free)

---

## 14. Template Messages (Content API)

### 14.1 Why Templates?

Just like Meta's WhatsApp Cloud API, Twilio also requires **pre-approved templates** for:

- Marketing messages
- Notifications
- Messages outside the 24-hour customer service window

### 14.2 Create a Template

1. Go to **https://console.twilio.com/us1/develop/sms/content-editor**
2. Click **"Create new Content"**
3. Choose **"WhatsApp"**
4. Fill in:
   - **Content Name**: `connectdesk_welcome`
   - **Template Type**: Select type (Marketing, Utility, etc.)
   - **Language**: English (en)
   - **Body**: Your message with variables
     ```
     Hello {{1}}! Welcome to ConnectDesk support. How can we help you today?
     ```
5. Add sample values for variables
6. Click **"Submit for Approval"**

### 14.3 Get Template SID

After approval:

1. Go to **Content Editor** → Your approved template
2. Copy the **Content SID**: `HXxxxxxxxxxxxxxxxxxxxx`
3. Use this SID when sending templates:

```php
// In admin panel or API
$templateSid = 'HXxxxxxxxxxxxxxxxxxxxx';
$variables = ['John']; // Variable values

$result = $twilioWhatsAppService->sendTemplateMessage(
    $templateSid,
    $variables,
    '+8801604509006'
);
```

### 14.4 Template Approval Timeline

| Status | Timeline |
|--------|----------|
| Pending | Usually 1-24 hours |
| Approved | Can use immediately |
| Rejected | Review rejection reason, fix, resubmit |

---

## 15. Troubleshooting

### 15.1 Common Errors

#### Error: "Twilio credentials not configured"

**Cause:** Missing or incorrect `.env` values

**Solution:**
1. Check `.env` has all Twilio variables
2. Run `php artisan config:clear`
3. Restart Laravel server

#### Error: "Invalid recipient phone number"

**Cause:** Phone number not in E.164 format

**Solution:** Ensure phone number includes `+` and country code:
- ✅ `+8801604509006`
- ❌ `01604509006`
- ❌ `8801604509006` (missing +)

#### Error: "The message From/To pair violates a blacklist rule"

**Cause:** Trying to send to a number not joined to sandbox (free trial)

**Solution:**
1. Have the recipient send `join <your-code>` to Twilio sandbox number
2. Or upgrade to paid account and use production number

#### Error: "Authenticate" (HTTP 401)

**Cause:** Incorrect Account SID or Auth Token

**Solution:**
1. Verify credentials in Twilio Console
2. Update `.env`
3. Clear config cache

#### Webhook Not Receiving Messages

**Checklist:**
- [ ] Webhook URL is publicly accessible (use ngrok for local)
- [ ] Webhook URL is HTTPS (not HTTP)
- [ ] Webhook URL is correctly configured in Twilio Console
- [ ] Route exists in `routes/api.php`
- [ ] CSRF protection is disabled for webhook route
- [ ] Check `storage/logs/laravel.log` for errors

### 15.2 Debug Webhook

Test your webhook manually using cURL:

```bash
curl -X POST https://your-domain.com/api/whatsapp/webhook \
  -d "MessageSid=SMxxxx" \
  -d "From=whatsapp:+8801234567890" \
  -d "To=whatsapp:+14155238886" \
  -d "Body=Test message" \
  -d "NumMedia=0" \
  -d "ProfileName=Test User"
```

Expected response: Empty XML (`<?xml version="1.0" encoding="UTF-8"?><Response></Response>`)

### 15.3 Check Configuration

Visit:
```
GET http://localhost:8000/api/whatsapp-debug
```

This will show you:
- Current Twilio configuration
- Whether credentials are set
- Setup instructions

---

## 16. API Reference

### 16.1 Send Text Message

```php
use App\Services\TwilioWhatsAppService;

$service = new TwilioWhatsAppService();

$result = $service->sendMessage(
    'Hello from ConnectDesk!',  // message
    '+8801604509006'             // recipient (E.164 format)
);

if ($result['success']) {
    echo "Message sent! SID: " . $result['message_id'];
} else {
    echo "Failed: " . $result['error'];
}
```

**Response Format:**
```php
[
    'success' => true,
    'message_id' => 'SMxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
    'error' => null,
    'status' => 'queued' // or 'sent', 'delivered', 'failed'
]
```

### 16.2 Send Template Message

```php
$result = $service->sendTemplateMessage(
    'HXxxxxxxxxxxxxxxxxxxxx',  // Template SID
    ['John', '123'],           // Variables
    '+8801604509006'           // Recipient
);
```

### 16.3 Send Message for Specific Admin

```php
$admin = User::find(1);

$result = $service->sendMessageForUser(
    $admin,
    'Hello from admin!',
    '+8801604509006'
);
```

This uses the admin's Twilio credentials if configured, otherwise falls back to global credentials.

### 16.4 Validate Phone Number

```php
$validation = $service->validateWhatsAppNumber('+8801604509006');

if ($validation['valid']) {
    $formatted = $validation['formatted_number'];
    // Proceed with sending
} else {
    echo $validation['message']; // Error message
}
```

---

## 17. Migration from Meta Cloud API

If you were previously using Meta's WhatsApp Cloud API, here's what changed:

### 17.1 Service Layer

**Before (Meta):**
```php
use App\Services\WhatsAppService;

$service = new WhatsAppService();
$sent = $service->sendMessage($message, $recipient); // Returns bool
```

**After (Twilio):**
```php
use App\Services\TwilioWhatsAppService;

$service = new TwilioWhatsAppService();
$result = $service->sendMessage($message, $recipient); // Returns array
$sent = $result['success'] ?? false;
```

### 17.2 Environment Variables

**Before (Meta):**
```env
WHATSAPP_ACCESS_TOKEN=EAAxxxxx...
WHATSAPP_PHONE_NUMBER_ID=123456789
WHATSAPP_TARGET_PHONE_NUMBER=8801604509006
```

**After (Twilio):**
```env
TWILIO_ACCOUNT_SID=ACxxxxx...
TWILIO_AUTH_TOKEN=your_token
TWILIO_WHATSAPP_FROM=+14155238886
TWILIO_TARGET_PHONE_NUMBER=+8801604509006
```

### 17.3 Webhook Format

**Before (Meta):** JSON payload
```json
{
  "object": "whatsapp_business_account",
  "entry": [...]
}
```

**After (Twilio):** Form data
```
MessageSid=SMxxxx&From=whatsapp:+xxx&Body=Hello
```

### 17.4 Phone Number Format

| Meta | Twilio |
|------|--------|
| `8801604509006` (digits only) | `+8801604509006` (E.164 with +) |
| No prefix | `whatsapp:+xxx` prefix in webhooks |

### 17.5 Template References

| Meta | Twilio |
|------|--------|
| Template Name: `hello_world` | Template SID: `HXxxxxxxxxxxxx` |
| Language code required | Language embedded in template |

---

## Appendix A: ConnectDesk Code Files Modified

| File | Changes Made |
|------|-------------|
| `app/Services/TwilioWhatsAppService.php` | **NEW** — Twilio integration service |
| `app/Http/Controllers/MessageController.php` | Updated to use `TwilioWhatsAppService` |
| `app/Http/Controllers/AdminDashboardController.php` | Updated to use `TwilioWhatsAppService` |
| `app/Http/Controllers/WhatsAppWebhookController.php` | Updated to handle Twilio webhook format |
| `app/Http/Controllers/Admin/AdminSettingsController.php` | Added Twilio credential fields |
| `app/Models/User.php` | Added `twilio_*` to fillable |
| `config/services.php` | Added `twilio` configuration array |
| `database/migrations/2026_02_17_074146_add_twilio_credentials_to_users_table.php` | **NEW** — Migration for Twilio fields |
| `composer.json` | Added `twilio/sdk` dependency |

---

## Appendix B: Quick Reference

### All Environment Variables

```env
# Twilio Account Credentials
TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=your_auth_token_here

# Twilio WhatsApp Sender Number
TWILIO_WHATSAPP_FROM=+14155238886

# Admin Phone (receives visitor messages)
TWILIO_TARGET_PHONE_NUMBER=+8801604509006
```

### Key Twilio URLs

| Purpose | URL |
|---------|-----|
| Console Dashboard | https://console.twilio.com |
| WhatsApp Sandbox | https://console.twilio.com/us1/develop/sms/try-it-out/whatsapp-learn |
| Content Templates | https://console.twilio.com/us1/develop/sms/content-editor |
| WhatsApp Senders | https://console.twilio.com/us1/develop/sms/senders/whatsapp-senders |
| Billing | https://console.twilio.com/us1/billing |

### Test API Endpoints

```bash
# Check configuration
GET http://localhost:8000/api/whatsapp-debug

# Send test message
POST http://localhost:8000/api/test-whatsapp

# Send text message (manual test)
POST http://localhost:8000/api/send-text
Body: { "to": "+8801XXXXXXXXX", "body": "Test message" }

# Send template (manual test)
POST http://localhost:8000/api/send-template
Body: { "to": "+8801XXXXXXXXX", "template_sid": "HXxxxx" }
```

---

## Support & Resources

- **Twilio Documentation:** https://www.twilio.com/docs/whatsapp
- **Twilio Support:** https://support.twilio.com
- **Twilio PHP SDK:** https://github.com/twilio/twilio-php
- **ConnectDesk Issues:** Check `storage/logs/laravel.log`

---

> **Congratulations!** 🎉 You've successfully integrated Twilio WhatsApp with ConnectDesk. You can now send and receive WhatsApp messages directly from your admin panel.
