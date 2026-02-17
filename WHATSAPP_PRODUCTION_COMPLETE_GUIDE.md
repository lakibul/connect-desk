# WhatsApp Cloud API — Complete Production Integration Guide for ConnectDesk

> **Document Version:** 1.0  
> **Last Updated:** February 16, 2026  
> **Scope:** End-to-end guide covering Meta account setup, WhatsApp Business API purchase & pricing, production configuration, permanent access tokens, webhook setup, and integration into the ConnectDesk Laravel codebase.

---

## Table of Contents

1. [Architecture Overview — How It All Fits Together](#1-architecture-overview--how-it-all-fits-together)
2. [What You Have Now (Sandbox / Free Test Mode)](#2-what-you-have-now-sandbox--free-test-mode)
3. [What Changes in Production](#3-what-changes-in-production)
4. [Meta Accounts Hierarchy — Understand the Structure](#4-meta-accounts-hierarchy--understand-the-structure)
5. [Step 1 — Create & Configure Meta Developer Account](#5-step-1--create--configure-meta-developer-account)
6. [Step 2 — Create a Meta Business Portfolio (formerly Business Manager)](#6-step-2--create-a-meta-business-portfolio-formerly-business-manager)
7. [Step 3 — Complete Business Verification](#7-step-3--complete-business-verification)
8. [Step 4 — Create a Facebook App for WhatsApp](#8-step-4--create-a-facebook-app-for-whatsapp)
9. [Step 5 — Set Up WhatsApp Business Account (WABA)](#9-step-5--set-up-whatsapp-business-account-waba)
10. [Step 6 — Register Your Real Business Phone Number](#10-step-6--register-your-real-business-phone-number)
11. [Step 7 — Generate a Permanent Access Token (System User Token)](#11-step-7--generate-a-permanent-access-token-system-user-token)
12. [Step 8 — Create & Get Approved Message Templates](#12-step-8--create--get-approved-message-templates)
13. [Step 9 — Configure Webhooks for Incoming Messages](#13-step-9--configure-webhooks-for-incoming-messages)
14. [Step 10 — Switch App to Live Mode](#14-step-10--switch-app-to-live-mode)
15. [Step 11 — Update ConnectDesk .env & Config](#15-step-11--update-connectdesk-env--config)
16. [Step 12 — Code Changes for Production Readiness](#16-step-12--code-changes-for-production-readiness)
17. [WhatsApp Cloud API Pricing & Billing](#17-whatsapp-cloud-api-pricing--billing)
18. [Conversation-Based Pricing Model Explained](#18-conversation-based-pricing-model-explained)
19. [API Endpoints Reference](#19-api-endpoints-reference)
20. [Message Types & 24-Hour Window Rule](#20-message-types--24-hour-window-rule)
21. [Webhook Payload Reference](#21-webhook-payload-reference)
22. [Rate Limits & Messaging Tiers](#22-rate-limits--messaging-tiers)
23. [Production Security Checklist](#23-production-security-checklist)
24. [Troubleshooting Common Issues](#24-troubleshooting-common-issues)
25. [Quick Reference — All Credentials Needed](#25-quick-reference--all-credentials-needed)

---

## 1. Architecture Overview — How It All Fits Together

```
┌──────────────────────────────────────────────────────────────────────┐
│                        ConnectDesk System                            │
│                                                                      │
│  ┌──────────────┐    ┌──────────────────┐    ┌───────────────────┐   │
│  │  Frontend     │    │   Laravel App     │    │   Admin Panel     │   │
│  │  (Visitor)    │───▶│                  │◀───│   (Dashboard)     │   │
│  │              │    │  MessageController│    │                   │   │
│  │  Sends msg   │    │  WhatsAppService  │    │  View & reply     │   │
│  │  via form    │    │                  │    │  to conversations  │   │
│  └──────────────┘    └────────┬─────────┘    └───────────────────┘   │
│                               │                                      │
└───────────────────────────────┼──────────────────────────────────────┘
                                │
                    ┌───────────┴───────────┐
                    │  WhatsApp Cloud API    │
                    │  graph.facebook.com    │
                    │  /v18.0/{PhoneID}/     │
                    │  messages              │
                    └───────────┬───────────┘
                                │
              ┌─────────────────┼─────────────────┐
              │                 │                   │
              ▼                 ▼                   ▼
     ┌──────────────┐  ┌──────────────┐   ┌──────────────────┐
     │ Send Message  │  │  Receive Msg  │   │  Delivery Status │
     │ (Outgoing)    │  │  (Webhook)    │   │  (Webhook)       │
     │ POST /messages│  │ POST callback │   │  POST callback   │
     └──────────────┘  └──────────────┘   └──────────────────┘
```

### Current Flow in ConnectDesk

1. **Visitor sends message** → `POST /api/messages` → `MessageController@store` → `WhatsAppService@sendMessage` → WhatsApp Cloud API sends the message to the admin's WhatsApp business phone number.

2. **Admin receives message** → WhatsApp Cloud API → `POST /api/whatsapp/webhook` → `WhatsAppWebhookController@handleWebhook` → Stored in `conversations` & `messages` tables → Displayed in admin dashboard.

3. **Admin replies** → Admin dashboard → `POST /admin/api/conversations/{id}/messages` → `MessageController@sendAdminMessage` → `WhatsAppService@sendMessageForUser` → WhatsApp Cloud API sends reply to visitor's phone.

---

## 2. What You Have Now (Sandbox / Free Test Mode)

| Feature | Sandbox/Test Mode |
|---------|------------------|
| **Access Token** | Temporary (expires every 24 hours) |
| **Phone Number** | Meta-provided test number |
| **Recipients** | Only 5 pre-registered test numbers |
| **Message Templates** | Only `hello_world` template available |
| **Free-form Text** | Only within 24-hour customer-initiated window |
| **Cost** | Free (1,000 free service conversations/month) |
| **Business Verification** | Not required |
| **App Mode** | Development |

### Your Current `.env` Configuration

```env
WHATSAPP_ACCESS_TOKEN=EAAXXX...          # Temporary 24-hour token
WHATSAPP_PHONE_NUMBER_ID=123456789012345 # Meta test phone number ID
WHATSAPP_WEBHOOK_SECRET=your_verify_token
WHATSAPP_TARGET_PHONE_NUMBER=8801XXXXXXXXX
```

### Limitations You're Facing

- Access token expires every 24 hours — must manually regenerate
- Can only message numbers added in Meta Developer Console (max 5)
- Cannot message real customers
- Using Meta's test phone number, not your own business number

---

## 3. What Changes in Production

| Feature | Production Mode |
|---------|----------------|
| **Access Token** | Permanent System User Token (never expires) |
| **Phone Number** | Your own real business phone number |
| **Recipients** | Any WhatsApp user worldwide |
| **Message Templates** | Custom templates (need approval) |
| **Free-form Text** | Within 24-hour window after customer message |
| **Cost** | Pay-per-conversation (see pricing section) |
| **Business Verification** | Required |
| **App Mode** | Live |

---

## 4. Meta Accounts Hierarchy — Understand the Structure

Understanding the hierarchy is critical. Here's how Meta organizes everything:

```
Meta/Facebook Personal Account (your login)
  └── Meta Business Portfolio (formerly Business Manager)
        ├── Facebook App (created in developers.facebook.com)
        │     └── WhatsApp Product (added to the app)
        │
        ├── WhatsApp Business Account (WABA)
        │     ├── Phone Number 1 (your business number)
        │     │     └── Phone Number ID ← you use this in API calls
        │     ├── Phone Number 2 (optional additional numbers)
        │     └── Message Templates
        │
        ├── System Users
        │     └── System User Token ← permanent access token
        │
        └── Business Verification Status
```

### Key Terms

| Term | What It Is | Where to Find |
|------|-----------|---------------|
| **Meta Business Portfolio** | Your business entity in Meta's system | business.facebook.com |
| **Facebook App** | The application that connects to WhatsApp API | developers.facebook.com |
| **WABA (WhatsApp Business Account)** | Container for your WhatsApp business profile | business.facebook.com/wa/manage |
| **Phone Number ID** | Unique ID for your registered phone number | WhatsApp > API Setup |
| **WABA ID** | Unique ID for your WhatsApp Business Account | WhatsApp Manager |
| **System User Token** | Permanent access token for API calls | Business Settings > System Users |
| **App Secret** | Secret key for webhook signature verification | App Dashboard > Settings > Basic |

---

## 5. Step 1 — Create & Configure Meta Developer Account

### 5.1 Create Developer Account

1. Go to **https://developers.facebook.com**
2. Click **"Get Started"** (top right)
3. Log in with your Facebook/Meta personal account
4. Accept the Meta Platform Terms
5. Verify your email address
6. Add a phone number for 2FA (mandatory for production)

### 5.2 Enable Two-Factor Authentication

> **Required for Live Mode.** Without 2FA, you cannot switch your app to production.

1. Go to Facebook → Settings → Security and Login
2. Enable **Two-Factor Authentication**
3. Use an authenticator app (Google Authenticator, Authy) — SMS is less secure

---

## 6. Step 2 — Create a Meta Business Portfolio (formerly Business Manager)

### 6.1 Create Business Portfolio

1. Go to **https://business.facebook.com/overview**
2. Click **"Create Account"** or **"Create a Business Portfolio"**
3. Fill in:
   - **Business Portfolio Name**: Your company name (e.g., "ConnectDesk Ltd")
   - **Your Name**: Your legal name
   - **Business Email**: Official business email (e.g., admin@connectdesk.com)
4. Click **"Submit"**

### 6.2 Add Business Details

1. Go to **https://business.facebook.com/settings**
2. Navigate to **Business Info**
3. Fill in all fields:
   - **Legal Business Name**: As registered with your government
   - **Business Address**: Complete physical address
   - **Business Phone Number**: Landline or mobile
   - **Website URL**: Your business website (must be live and functioning)
   - **Business Industry/Category**: Select the appropriate category

### 6.3 Add Payment Method

1. In Business Settings → **Payment Methods**
2. Add a credit/debit card or set up payment
3. This is required for WhatsApp API billing

> **Note:** Meta bills you for conversations. You need a valid payment method before going live.

---

## 7. Step 3 — Complete Business Verification

> **This is the most critical step.** Without business verification, you cannot go to production.

### 7.1 Start Verification

1. Go to **https://business.facebook.com/settings/security**
2. Click **"Start Verification"**
3. Select your **country** and **legal entity type**

### 7.2 Submit Required Documents

Meta requires **two types of verification**:

#### A. Legal Business Documents (submit ONE):

| Document Type | Details |
|--------------|---------|
| Business Registration Certificate | Government-issued registration |
| Tax Registration (TIN/EIN/VAT) | Tax identification document |
| Articles of Incorporation | Company formation document |
| Business License | Operating license from local authority |

**For Bangladesh specifically:**
- Trade License (ট্রেড লাইসেন্স)
- TIN Certificate (টিন সার্টিফিকেট)
- NID of business owner
- Bank Statement showing business name

#### B. Business Address Verification (submit ONE):

| Document Type | Details |
|--------------|---------|
| Utility Bill | Electricity, water, gas, internet (last 3 months) |
| Bank Statement | Showing business name and address (last 3 months) |
| Tax Registration | If it shows the business address |

### 7.3 Phone/Email Verification

Meta will verify your business through one of these:
- **Phone call** to your listed business number
- **Email** to your business domain email
- **Domain verification** via DNS TXT record or HTML file

### 7.4 Domain Verification (Recommended)

1. Go to Business Settings → **Brand Safety** → **Domains**
2. Click **"Add Domain"**
3. Enter your domain (e.g., `connectdesk.com`)
4. Choose verification method:

   **Option A — DNS TXT Record:**
   ```
   Add TXT record to your domain's DNS:
   Name: @
   Value: meta-business-verification=XXXXXXXXXXXX
   ```

   **Option B — HTML File Upload:**
   ```
   Download the verification HTML file
   Upload to: https://yourdomain.com/.well-known/meta-verification.html
   ```

   **Option C — Meta Tag:**
   ```html
   Add to your website's <head>:
   <meta name="facebook-domain-verification" content="XXXXXXXXXXXX" />
   ```

### 7.5 Verification Timeline

| Status | Timeline |
|--------|----------|
| Under Review | 1-3 business days (typical) |
| Additional Info Requested | 3-7 days after resubmission |
| Rejected | Can re-apply with corrected documents |
| Maximum Wait | Up to 14 business days |

### 7.6 Check Verification Status

1. Go to https://business.facebook.com/settings/security
2. Look for **"Business Verification"** section
3. Status will show: Not Started / Pending / Verified / Rejected

---

## 8. Step 4 — Create a Facebook App for WhatsApp

### 8.1 Create New App

1. Go to **https://developers.facebook.com/apps/**
2. Click **"Create App"**
3. Select use case: **"Other"** → then **"Business"** type
4. Fill in:
   - **App Name**: `ConnectDesk` (or your preferred name)
   - **App Contact Email**: Your email
   - **Business Portfolio**: Select the portfolio you created in Step 2
5. Click **"Create App"**

### 8.2 Add WhatsApp Product

1. In your App Dashboard, scroll down to **"Add Products to Your App"**
2. Find **"WhatsApp"** and click **"Set Up"**
3. Select your existing Meta Business Portfolio when prompted
4. This creates a WhatsApp Business Account (WABA) automatically

### 8.3 Note Your App Credentials

From App Dashboard → **Settings** → **Basic**:

| Credential | Where to Find | Use In ConnectDesk |
|-----------|---------------|-------------------|
| **App ID** | Settings > Basic | Not directly used, but needed for reference |
| **App Secret** | Settings > Basic (click "Show") | Webhook signature verification |

> **Save the App Secret** — you'll need it for webhook security (see Step 9).

---

## 9. Step 5 — Set Up WhatsApp Business Account (WABA)

### 9.1 Access WhatsApp Manager

1. Go to **https://business.facebook.com/wa/manage/**
2. You'll see your WhatsApp Business Account created when you added WhatsApp to your app

### 9.2 Configure Business Profile

1. Click on your WABA → **Account Settings**
2. Fill in your business profile:
   - **Business Name**: Your display name (max 25 chars)
   - **Category**: Select your business category
   - **Business Description**: Brief description (max 256 chars)
   - **Business Website**: Your website URL
   - **Business Email**: Contact email
   - **Profile Picture**: 640x640 px recommended

### 9.3 Note Your WABA ID

- Found in WhatsApp Manager → Account Settings
- Format: `1234567890123456`
- You'll need this for some management API calls

---

## 10. Step 6 — Register Your Real Business Phone Number

### 10.1 Requirements for Your Business Phone Number

| Requirement | Details |
|------------|---------|
| **NOT currently on WhatsApp** | The number must NOT be registered on WhatsApp or WhatsApp Business app |
| **Can receive SMS or voice call** | For OTP verification |
| **Valid phone number** | Active SIM card or landline |
| **Country code included** | e.g., +880 for Bangladesh |

> **IMPORTANT:** If your number is currently used in WhatsApp personal or WhatsApp Business app, you MUST delete that WhatsApp account first. You can migrate it, but it's complex. Best to use a fresh number.

### 10.2 Add Your Phone Number

1. Go to your App Dashboard → **WhatsApp** → **API Setup** (or **Getting Started**)
2. Under **"Step 5: Add a phone number"** or **"From"** section
3. Click **"Add Phone Number"**
4. Enter:
   - **Phone Number**: Your business number with country code
   - **Display Name**: Your business name (subject to approval)
5. Choose verification method: **SMS** or **Voice Call**
6. Enter the **OTP code** you receive

### 10.3 Get Your Phone Number ID

After adding your number:

1. Go to WhatsApp → **API Setup**
2. In the **"From"** dropdown, select your newly added number
3. The **Phone Number ID** is displayed below the number
4. Copy this — it's your `WHATSAPP_PHONE_NUMBER_ID`

```
Example:
Phone Number: +880 1XXXXXXXXX
Phone Number ID: 234567890123456   ← This is what you need
```

### 10.4 Display Name Approval

Your display name must be approved by Meta. Rules:

- ✅ Must clearly represent your business
- ✅ Must match or relate to your business name
- ✅ Maximum 25 characters
- ❌ No URLs, phone numbers, or email addresses
- ❌ No generic words like "Customer Service" alone
- ❌ No misleading or deceptive names

**Approval timeline:** Usually 24-48 hours. Check status in WhatsApp Manager.

---

## 11. Step 7 — Generate a Permanent Access Token (System User Token)

> **This is the most important credential change from sandbox to production.** In sandbox, you used a 24-hour temporary token. In production, you need a permanent System User Token.

### 11.1 Create a System User

1. Go to **https://business.facebook.com/settings/system-users**
2. Click **"Add"** to create a new System User
3. Fill in:
   - **System User Name**: `connectdesk-api` (or any name)
   - **Role**: **Admin** (needed for full WhatsApp access)
4. Click **"Create System User"**

### 11.2 Assign Assets to System User

1. Click on the System User you created
2. Click **"Add Assets"**
3. In the popup:
   - Select **"Apps"** tab → Check your ConnectDesk app → Toggle **"Full Control"**
   - Select **"WhatsApp Accounts"** tab → Check your WABA → Toggle **"Full Control"**
4. Click **"Save Changes"**

### 11.3 Generate Permanent Token

1. Click on the System User → **"Generate New Token"**
2. Select your **App** (ConnectDesk)
3. Set **Token Expiration**: **"Never"** (permanent token)
4. Select the following **permissions** (scopes):

| Permission | Purpose |
|-----------|---------|
| `whatsapp_business_management` | Manage WhatsApp business settings |
| `whatsapp_business_messaging` | Send and receive messages |
| `business_management` | Access business settings |

5. Click **"Generate Token"**
6. **COPY THE TOKEN IMMEDIATELY** — it is shown only ONCE

```
Token format example:
EAAxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

> **⚠️ CRITICAL:** Save this token securely. You cannot view it again. If lost, you must generate a new one.

### 11.4 Token Comparison

| Feature | Temporary Token (Sandbox) | System User Token (Production) |
|---------|--------------------------|-------------------------------|
| **Lifespan** | 24 hours | Never expires |
| **Regeneration** | Manual, every day | Only when revoked |
| **Permissions** | Limited | Full (based on scopes) |
| **Security** | Low (shared casually) | High (treat as password) |
| **Rate Limits** | Lower | Higher (based on tier) |

---

## 12. Step 8 — Create & Get Approved Message Templates

### 12.1 Why Templates Are Required

WhatsApp enforces a **24-hour messaging window**:

```
Customer sends message to your business
          │
          ▼
    ┌─────────────┐
    │  24-Hour     │  ← You can send FREE-FORM text replies
    │  Window      │     (any text, no template needed)
    │  Opens       │
    └──────┬──────┘
           │
           ▼ (after 24 hours of no customer message)
    ┌─────────────┐
    │  Window      │  ← You can ONLY send PRE-APPROVED
    │  Closed      │     TEMPLATE messages
    └─────────────┘
```

**In ConnectDesk context:**
- When a visitor sends a WhatsApp message → webhook receives it → 24-hour window opens
- Admin can reply with any text within that window
- After 24 hours of no visitor message, admin must use a template to re-initiate

### 12.2 Create Templates

1. Go to **https://business.facebook.com/wa/manage/message-templates/**
2. Click **"Create Template"**
3. Choose template category:

| Category | Use Case | Cost |
|----------|----------|------|
| **Marketing** | Promotions, offers, announcements | Higher |
| **Utility** | Order updates, account alerts, confirmations | Medium |
| **Authentication** | OTP codes, login verification | Lowest |

### 12.3 Recommended Templates for ConnectDesk

#### Template 1: Welcome / Greeting

```
Name: connectdesk_welcome
Category: Utility
Language: English (en_US)

Header: None
Body: Hello {{1}}! 👋 Welcome to ConnectDesk support. How can we help you today?
Footer: Powered by ConnectDesk
Buttons: None

Variables:
{{1}} = Customer name
```

#### Template 2: Follow-Up

```
Name: connectdesk_followup
Category: Utility
Language: English (en_US)

Header: None
Body: Hi {{1}}, this is a follow-up from ConnectDesk support regarding your recent inquiry. 
      Please reply to continue the conversation, or let us know if your issue is resolved. Thank you!
Footer: ConnectDesk Support
Buttons: None

Variables:
{{1}} = Customer name
```

#### Template 3: Conversation Re-opener

```
Name: connectdesk_reopen
Category: Utility
Language: English (en_US)

Header: None
Body: Hi {{1}}, we'd like to follow up on your previous conversation. 
      Please reply to this message if you need further assistance. We're here to help!
Footer: ConnectDesk Support
Buttons: Quick Reply - "Yes, I need help" | Quick Reply - "Issue resolved"

Variables:
{{1}} = Customer name
```

### 12.4 Template Approval

- Submit your templates
- **Approval timeline:** Usually 1-24 hours
- **Status tracking:** WhatsApp Manager → Message Templates → Status column
- **Statuses:** Pending → Approved / Rejected

**If rejected:**
- Read the rejection reason
- Fix the issue (usually content policy violation)
- Resubmit

### 12.5 Template Restrictions

- ❌ No URL shorteners (bit.ly, etc.)
- ❌ No misleading content
- ❌ No variable-only messages (must have static text)
- ❌ No threatening or abusive language
- ✅ Must have clear opt-out option for marketing
- ✅ Variables must have sample values during submission

---

## 13. Step 9 — Configure Webhooks for Incoming Messages

### 13.1 Why Webhooks Are Needed

Webhooks allow Meta to send incoming WhatsApp messages to your ConnectDesk server. Without webhooks, you can send messages but cannot receive them.

Your ConnectDesk already has webhook endpoints:
- **Verification:** `GET /api/whatsapp/webhook` → `WhatsAppWebhookController@verify`
- **Messages:** `POST /api/whatsapp/webhook` → `WhatsAppWebhookController@handleWebhook`

### 13.2 Production Webhook Requirements

| Requirement | Details |
|------------|---------|
| **HTTPS** | Must be HTTPS (no HTTP). SSL certificate required |
| **Public URL** | Must be accessible from the internet |
| **Response Time** | Must respond within 20 seconds |
| **Response Code** | Must return HTTP 200 for successful receipt |
| **Verify Token** | A secret string you create for webhook verification |

### 13.3 Set Up Your Production Server

Your server must be publicly accessible with a valid SSL certificate.

**Options:**

| Option | Best For | Cost |
|--------|----------|------|
| **VPS (DigitalOcean, Linode, Vultr)** | Most control, good performance | $5-20/month |
| **Shared Hosting (cPanel with SSL)** | Budget option | $3-10/month |
| **Cloud (AWS, GCP, Azure)** | Scalability | Variable |
| **PaaS (Laravel Forge + DO/AWS)** | Easy Laravel deployment | $12/month + server |
| **Railway / Render** | Quick deployment | Free tier available |

Your webhook URL will be something like:
```
https://yourdomain.com/api/whatsapp/webhook
```

### 13.4 Configure Webhook in Meta Dashboard

1. Go to your App Dashboard → **WhatsApp** → **Configuration**
2. Under **"Webhook"** section:
   - Click **"Edit"**
   - Enter **Callback URL**: `https://yourdomain.com/api/whatsapp/webhook`
   - Enter **Verify Token**: The same value as your `WHATSAPP_WEBHOOK_SECRET` in `.env`
   - Click **"Verify and Save"**

3. **Subscribe to webhook fields** (check these boxes):
   - ✅ `messages` — Incoming messages
   - ✅ `message_deliveries` — Delivery confirmations (optional but recommended)
   - ✅ `message_reads` — Read receipts (optional)
   - ✅ `messaging_postbacks` — Button click callbacks
   - ✅ `message_template_status_updates` — Template approval notifications

### 13.5 Webhook Signature Verification (Security)

In production, you should verify that webhooks actually come from Meta using the **App Secret**.

Your `WhatsAppService` already has a `verifyWebhook()` method. Here's how to use it in production:

**Add to `WhatsAppWebhookController@handleWebhook`:**

```php
public function handleWebhook(Request $request)
{
    // Verify webhook signature in production
    $signature = $request->header('X-Hub-Signature-256');
    $payload = $request->getContent();
    
    if ($signature) {
        $appSecret = config('services.whatsapp.app_secret');
        $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $appSecret);
        
        if (!hash_equals($expectedSignature, $signature)) {
            Log::warning('Invalid webhook signature');
            return response('Invalid signature', 403);
        }
    }
    
    // ... rest of your existing handleWebhook code
}
```

**Add to `.env`:**
```env
WHATSAPP_APP_SECRET=your_app_secret_from_meta_dashboard
```

**Add to `config/services.php`:**
```php
'whatsapp' => [
    // ... existing keys
    'app_secret' => env('WHATSAPP_APP_SECRET'),
],
```

---

## 14. Step 10 — Switch App to Live Mode

### 14.1 Pre-Requisites Checklist

Before switching to Live mode, ensure ALL of these are complete:

- [ ] Business Verification is **Approved**
- [ ] Display Name is **Approved**
- [ ] Real phone number is **Registered and Verified**
- [ ] System User Token is **Generated**
- [ ] At least one Message Template is **Approved**
- [ ] Webhook URL is **Configured and Verified**
- [ ] Privacy Policy URL is set in App Settings
- [ ] Two-Factor Authentication is **Enabled** on your Facebook account
- [ ] Payment method is added to your Meta Business Portfolio

### 14.2 Set Privacy Policy URL

1. Go to App Dashboard → **Settings** → **Basic**
2. Add **Privacy Policy URL**: `https://yourdomain.com/privacy-policy`
3. Add **Terms of Service URL** (optional but recommended)
4. Click **"Save Changes"**

### 14.3 Switch to Live Mode

1. Go to your App Dashboard
2. At the top of the page, find the **"App Mode"** toggle
3. It currently shows **"Development"** — click to switch to **"Live"**
4. Confirm the switch

> **After switching to Live Mode:**
> - Your app can now message any WhatsApp user
> - Billing starts (per-conversation pricing)
> - Rate limits are based on your messaging tier
> - You must maintain compliance with WhatsApp policies

### 14.4 What Happens After Going Live

| Aspect | Before (Development) | After (Live) |
|--------|---------------------|--------------|
| Recipients | 5 test numbers only | Any WhatsApp number worldwide |
| Phone Number | Meta test number | Your real business number |
| Templates | hello_world only | Your custom approved templates |
| Token | 24-hour temporary | Permanent System User Token |
| Billing | Free | Pay-per-conversation |
| Display Name | Not shown | Your approved business name |
| Blue Tick | No | Can apply after consistency |

---

## 15. Step 11 — Update ConnectDesk `.env` & Config

### 15.1 Update `.env` File

Replace your sandbox values with production values:

```env
# ============================================
# WhatsApp Cloud API - PRODUCTION Configuration
# ============================================

# Permanent System User Token (from Step 7)
WHATSAPP_ACCESS_TOKEN=EAAxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

# Your real business phone number ID (from Step 6)
WHATSAPP_PHONE_NUMBER_ID=234567890123456

# Webhook verify token (any string you choose — must match Meta dashboard)
WHATSAPP_WEBHOOK_SECRET=your_secure_random_string_here

# Your production webhook URL
WHATSAPP_WEBHOOK_URL=https://yourdomain.com/api/whatsapp/webhook

# Default target phone number for testing (your admin's WhatsApp number)
WHATSAPP_TARGET_PHONE_NUMBER=8801XXXXXXXXX

# App Secret from Meta Dashboard (for webhook signature verification)
WHATSAPP_APP_SECRET=your_app_secret_here

# WhatsApp API Version (update periodically — check Meta docs for latest)
WHATSAPP_API_VERSION=v21.0
```

### 15.2 Update `config/services.php`

```php
'whatsapp' => [
    'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
    'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
    'webhook_secret' => env('WHATSAPP_WEBHOOK_SECRET'),
    'webhook_url' => env('WHATSAPP_WEBHOOK_URL'),
    'target_phone_number' => env('WHATSAPP_TARGET_PHONE_NUMBER'),
    'app_secret' => env('WHATSAPP_APP_SECRET'),
    'api_version' => env('WHATSAPP_API_VERSION', 'v21.0'),
],
```

### 15.3 Clear Config Cache

After updating `.env`, run:

```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

### 15.4 Update Admin User Credentials

Each admin user can also have their own WhatsApp credentials via the Admin Settings page:

1. Log in as admin
2. Go to **Settings** (`/admin/settings`)
3. Enter:
   - **WhatsApp Access Token**: System User Token
   - **WhatsApp Phone Number ID**: Your phone number ID
   - **Phone Number**: Your business phone number
4. Save

These values are stored in the `users` table (`whatsapp_access_token`, `whatsapp_phone_number_id` columns) and used by `WhatsAppService@resolveAccessToken` and `resolvePhoneNumberId`.

---

## 16. Step 12 — Code Changes for Production Readiness

### 16.1 Update API Version in WhatsAppService

In `app/Services/WhatsAppService.php`, update the API URL to use a configurable version:

```php
public function __construct()
{
    $this->client = new Client();
    $apiVersion = config('services.whatsapp.api_version', 'v21.0');
    $this->apiUrl = "https://graph.facebook.com/{$apiVersion}/";
    $this->accessToken = config('services.whatsapp.access_token');
    $this->businessPhoneNumberId = config('services.whatsapp.phone_number_id');
    $this->targetPhoneNumber = $this->sanitizePhoneNumber(
        config('services.whatsapp.target_phone_number')
    );
}
```

### 16.2 Add Webhook Signature Verification

Update `WhatsAppWebhookController@handleWebhook` to verify webhook signatures:

```php
public function handleWebhook(Request $request)
{
    try {
        // Verify webhook signature
        $signature = $request->header('X-Hub-Signature-256');
        $appSecret = config('services.whatsapp.app_secret');
        
        if ($appSecret && $signature) {
            $payload = $request->getContent();
            $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $appSecret);
            
            if (!hash_equals($expectedSignature, $signature)) {
                Log::warning('Invalid webhook signature', [
                    'received' => $signature,
                ]);
                return response('Invalid signature', 403);
            }
        }
        
        // ... rest of existing code
    }
}
```

### 16.3 Handle Message Status Updates

WhatsApp sends delivery status updates via webhooks. Add handling in `WhatsAppWebhookController`:

```php
// Inside the foreach ($entry['changes'] ?? [] as $change) loop
$value = $change['value'] ?? [];

// Handle message status updates (sent, delivered, read)
$statuses = $value['statuses'] ?? [];
foreach ($statuses as $status) {
    $messageId = $status['id'] ?? '';
    $recipientId = $status['recipient_id'] ?? '';
    $statusValue = $status['status'] ?? ''; // sent, delivered, read, failed
    $timestamp = $status['timestamp'] ?? '';
    
    Log::info('WhatsApp message status update', [
        'message_id' => $messageId,
        'recipient' => $recipientId,
        'status' => $statusValue,
        'timestamp' => $timestamp,
    ]);
    
    // Optionally update message status in your database
    if ($statusValue === 'read') {
        // Mark outgoing messages as read
        Message::where('whatsapp_message_id', $messageId)
            ->update(['is_read' => true]);
    }
}
```

### 16.4 Store WhatsApp Message ID

When sending messages, store the WhatsApp message ID for tracking delivery status:

In `WhatsAppService@sendMessageWithCredentials`, after a successful send:

```php
if ($statusCode === 200) {
    $whatsappMessageId = $responseBody['messages'][0]['id'] ?? null;
    Log::info('WhatsApp message sent successfully', [
        'recipient' => $recipient,
        'message_id' => $whatsappMessageId,
    ]);
    return [
        'success' => true,
        'message_id' => $whatsappMessageId,
    ];
}
```

### 16.5 Handle Media Messages

In production, users may send images, documents, audio, video, etc. Update the webhook handler:

```php
foreach ($messages as $message) {
    $from = $message['from'] ?? '';
    $messageId = $message['id'] ?? '';
    $type = $message['type'] ?? '';
    
    // Determine message content based on type
    $messageBody = '';
    switch ($type) {
        case 'text':
            $messageBody = $message['text']['body'] ?? '';
            break;
        case 'image':
            $messageBody = '[Image] ' . ($message['image']['caption'] ?? 'Image received');
            break;
        case 'document':
            $messageBody = '[Document] ' . ($message['document']['filename'] ?? 'Document received');
            break;
        case 'audio':
            $messageBody = '[Audio message received]';
            break;
        case 'video':
            $messageBody = '[Video] ' . ($message['video']['caption'] ?? 'Video received');
            break;
        case 'location':
            $lat = $message['location']['latitude'] ?? '';
            $lng = $message['location']['longitude'] ?? '';
            $messageBody = "[Location] Lat: {$lat}, Lng: {$lng}";
            break;
        case 'contacts':
            $messageBody = '[Contact shared]';
            break;
        case 'sticker':
            $messageBody = '[Sticker received]';
            break;
        case 'reaction':
            $emoji = $message['reaction']['emoji'] ?? '';
            $messageBody = "[Reaction: {$emoji}]";
            break;
        default:
            $messageBody = "[{$type}] message received";
    }
    
    // ... continue with your existing conversation/message creation
}
```

---

## 17. WhatsApp Cloud API Pricing & Billing

### 17.1 Is the API Free? How to "Purchase"?

**You do NOT "purchase" the WhatsApp Cloud API.** The API itself is free to use. Meta charges based on **conversations** (not individual messages).

| Item | Cost |
|------|------|
| **WhatsApp Cloud API Access** | FREE |
| **Meta Developer Account** | FREE |
| **Meta Business Portfolio** | FREE |
| **Business Verification** | FREE |
| **API Hosting (your server)** | You pay for your own server |
| **Conversations** | Pay-per-conversation (see below) |

### 17.2 How Billing Works

1. **Add payment method** in Meta Business Settings → Billing
2. Meta charges your card automatically based on usage
3. You receive invoices monthly
4. First 1,000 service conversations per month are **FREE**

### 17.3 Setting Up Billing

1. Go to **https://business.facebook.com/billing**
2. Or: Meta Business Suite → Settings → Billing & Payments
3. Click **"Add Payment Method"**
4. Add credit card, debit card, or other accepted method
5. Set a **spending limit** (recommended to avoid unexpected costs)

---

## 18. Conversation-Based Pricing Model Explained

### 18.1 Conversation Categories & Costs

WhatsApp charges per **conversation** (a 24-hour message session), NOT per message.

| Category | Who Initiates | Use Case | Approximate Cost (USD) |
|----------|--------------|----------|----------------------|
| **Service** | Customer initiates | Customer sends you a message first | FREE (first 1,000/month) |
| **Utility** | Business initiates | Order confirmations, shipping updates | $0.005 - $0.080 |
| **Authentication** | Business initiates | OTP codes, login verification | $0.003 - $0.060 |
| **Marketing** | Business initiates | Promotions, offers, newsletters | $0.010 - $0.150 |

> **Prices vary by country.** Bangladesh rates are on the lower end. Check the latest pricing at: https://developers.facebook.com/docs/whatsapp/pricing

### 18.2 How Conversations Work

```
Scenario 1: Customer Initiates (Service Conversation - FREE)
═══════════════════════════════════════════════════
Customer sends: "Hi, I need help"           ← Window opens
  └── You reply: "How can I help?"          ← FREE (within window)
  └── You reply: "Here's the solution..."    ← FREE (within window)
  └── Customer: "Thanks!"                   ← Window extends 24h
  └── You reply: "You're welcome!"          ← FREE (within window)
  
  ... 24 hours pass with no customer message ...
  
  Window closes. Total cost: $0.00 (service conversation)

Scenario 2: Business Initiates (Template Required)
═══════════════════════════════════════════════════
You send template: "Hi {{name}}, order update..." ← Opens new conversation
  └── Customer replies: "Thanks"                   ← Window now open
  └── You can send free-form text replies           ← Within 24h window
  
  Cost: Based on template category (utility/marketing/auth)

Scenario 3: ConnectDesk Flow
═══════════════════════════════════════════════════
Visitor fills form on your website
  └── ConnectDesk sends WhatsApp to admin           ← Business-initiated (utility)
  └── Admin sees message in dashboard
  └── Admin replies to visitor                      ← Needs template if >24h
  └── Visitor receives reply on WhatsApp
  └── Visitor replies back                          ← Service conversation opens
  └── Admin can now send free-form text for 24h
```

### 18.3 Free Tier

| What's Free | Limit |
|------------|-------|
| First 1,000 **service** conversations per month | Per WABA, per month |
| API access | Always free |
| Webhook delivery | Always free |
| Template submission | Always free |

### 18.4 Estimating Your Monthly Cost

**Example for ConnectDesk:**

| Activity | Conversations/month | Category | Cost per conv. | Total |
|----------|-------------------|----------|---------------|-------|
| Customer Support | 500 | Service (free) | $0.00 | $0.00 |
| Follow-up templates | 200 | Utility | $0.02 | $4.00 |
| Marketing campaigns | 100 | Marketing | $0.05 | $5.00 |
| **Total** | **800** | | | **$9.00** |

---

## 19. API Endpoints Reference

### 19.1 Base URL

```
https://graph.facebook.com/{API_VERSION}/{PHONE_NUMBER_ID}/messages
```

**Current recommended version:** `v21.0` (as of February 2026)

> **Always check for the latest version:** https://developers.facebook.com/docs/graph-api/changelog

### 19.2 Send Text Message

```bash
POST https://graph.facebook.com/v21.0/{PHONE_NUMBER_ID}/messages

Headers:
  Authorization: Bearer {ACCESS_TOKEN}
  Content-Type: application/json

Body:
{
  "messaging_product": "whatsapp",
  "to": "8801XXXXXXXXX",
  "type": "text",
  "text": {
    "preview_url": true,
    "body": "Hello! This is a message from ConnectDesk."
  }
}
```

### 19.3 Send Template Message

```bash
POST https://graph.facebook.com/v21.0/{PHONE_NUMBER_ID}/messages

Headers:
  Authorization: Bearer {ACCESS_TOKEN}
  Content-Type: application/json

Body:
{
  "messaging_product": "whatsapp",
  "to": "8801XXXXXXXXX",
  "type": "template",
  "template": {
    "name": "connectdesk_welcome",
    "language": {
      "code": "en_US"
    },
    "components": [
      {
        "type": "body",
        "parameters": [
          {
            "type": "text",
            "text": "John"
          }
        ]
      }
    ]
  }
}
```

### 19.4 Send Image Message

```bash
POST https://graph.facebook.com/v21.0/{PHONE_NUMBER_ID}/messages

Body:
{
  "messaging_product": "whatsapp",
  "to": "8801XXXXXXXXX",
  "type": "image",
  "image": {
    "link": "https://yourdomain.com/images/photo.jpg",
    "caption": "Check this out!"
  }
}
```

### 19.5 Send Document Message

```bash
POST https://graph.facebook.com/v21.0/{PHONE_NUMBER_ID}/messages

Body:
{
  "messaging_product": "whatsapp",
  "to": "8801XXXXXXXXX",
  "type": "document",
  "document": {
    "link": "https://yourdomain.com/files/invoice.pdf",
    "caption": "Your invoice",
    "filename": "invoice.pdf"
  }
}
```

### 19.6 Mark Message as Read

```bash
PUT https://graph.facebook.com/v21.0/{PHONE_NUMBER_ID}/messages

Body:
{
  "messaging_product": "whatsapp",
  "status": "read",
  "message_id": "wamid.XXXXXXXXXXXXXXXXXXXX"
}
```

### 19.7 Get Business Profile

```bash
GET https://graph.facebook.com/v21.0/{PHONE_NUMBER_ID}/whatsapp_business_profile

Headers:
  Authorization: Bearer {ACCESS_TOKEN}

Query Parameters:
  fields=about,address,description,email,profile_picture_url,websites,vertical
```

### 19.8 Success Response

```json
{
  "messaging_product": "whatsapp",
  "contacts": [
    {
      "input": "8801XXXXXXXXX",
      "wa_id": "8801XXXXXXXXX"
    }
  ],
  "messages": [
    {
      "id": "wamid.HBgMODgwMTYwNDUwOTAwNhUCABIYIDE2RjQ5..."
    }
  ]
}
```

### 19.9 Error Response

```json
{
  "error": {
    "message": "(#131030) Recipient phone number not in allowed list",
    "type": "OAuthException",
    "code": 131030,
    "error_subcode": 2655733,
    "fbtrace_id": "XXXXXXXXX"
  }
}
```

> **Error code 131030** is the sandbox limitation error — disappears in production.

---

## 20. Message Types & 24-Hour Window Rule

### 20.1 The 24-Hour Customer Service Window

```
┌──────────────────────────────────────────────────────┐
│           24-HOUR WINDOW RULE                         │
├──────────────────────────────────────────────────────┤
│                                                      │
│  Customer sends message ──────────┐                  │
│                                   ▼                  │
│                          ┌──────────────────┐        │
│                          │  WINDOW OPEN      │        │
│                          │  (24 hours)       │        │
│                          │                  │        │
│                          │  ✅ Free-form text │        │
│                          │  ✅ Images/media   │        │
│                          │  ✅ Documents      │        │
│                          │  ✅ Templates      │        │
│                          │  ✅ Interactive    │        │
│                          └────────┬─────────┘        │
│                                   │                  │
│                    24h expires     │                  │
│                                   ▼                  │
│                          ┌──────────────────┐        │
│                          │  WINDOW CLOSED    │        │
│                          │                  │        │
│                          │  ❌ Free-form text │        │
│                          │  ❌ Images/media   │        │
│                          │  ✅ Templates ONLY │        │
│                          └──────────────────┘        │
│                                                      │
└──────────────────────────────────────────────────────┘
```

### 20.2 How This Affects ConnectDesk

| Scenario | Window Status | What Admin Can Send |
|----------|--------------|-------------------|
| Visitor just sent a message | Open | Any text, media, documents |
| Visitor messaged 12 hours ago | Open | Any text, media, documents |
| Visitor messaged 25 hours ago | Closed | Template messages only |
| Admin initiating first contact | No window | Template messages only |
| After sending template, visitor replies | Reopened | Any text for next 24h |

### 20.3 Implementation in ConnectDesk

Your system already supports both text and template modes via `MessageController@sendAdminMessage`:

- `message_type: "text"` → calls `WhatsAppService@sendMessageForUser`
- `message_type: "template"` → calls `WhatsAppService@sendTemplateMessageForUser`

The admin dashboard should ideally track the last customer message timestamp and show a warning when the 24-hour window is about to close or has closed.

---

## 21. Webhook Payload Reference

### 21.1 Text Message Received

```json
{
  "object": "whatsapp_business_account",
  "entry": [
    {
      "id": "WABA_ID",
      "changes": [
        {
          "value": {
            "messaging_product": "whatsapp",
            "metadata": {
              "display_phone_number": "880XXXXXXXXXX",
              "phone_number_id": "PHONE_NUMBER_ID"
            },
            "contacts": [
              {
                "profile": {
                  "name": "Customer Name"
                },
                "wa_id": "8801XXXXXXXXX"
              }
            ],
            "messages": [
              {
                "from": "8801XXXXXXXXX",
                "id": "wamid.XXXXXXXXXXXX",
                "timestamp": "1234567890",
                "text": {
                  "body": "Hello, I need help!"
                },
                "type": "text"
              }
            ]
          },
          "field": "messages"
        }
      ]
    }
  ]
}
```

### 21.2 Image Message Received

```json
{
  "messages": [
    {
      "from": "8801XXXXXXXXX",
      "id": "wamid.XXXXXXXXXXXX",
      "timestamp": "1234567890",
      "type": "image",
      "image": {
        "caption": "Check this photo",
        "mime_type": "image/jpeg",
        "sha256": "XXXXXXXXXXX",
        "id": "MEDIA_ID"
      }
    }
  ]
}
```

### 21.3 Message Status Update

```json
{
  "object": "whatsapp_business_account",
  "entry": [
    {
      "changes": [
        {
          "value": {
            "messaging_product": "whatsapp",
            "metadata": {
              "display_phone_number": "880XXXXXXXXXX",
              "phone_number_id": "PHONE_NUMBER_ID"
            },
            "statuses": [
              {
                "id": "wamid.XXXXXXXXXXXX",
                "status": "delivered",
                "timestamp": "1234567890",
                "recipient_id": "8801XXXXXXXXX",
                "conversation": {
                  "id": "CONVERSATION_ID",
                  "expiration_timestamp": "1234567890",
                  "origin": {
                    "type": "service"
                  }
                },
                "pricing": {
                  "billable": true,
                  "pricing_model": "CBP",
                  "category": "service"
                }
              }
            ]
          },
          "field": "messages"
        }
      ]
    }
  ]
}
```

**Status values:** `sent` → `delivered` → `read` → `failed`

---

## 22. Rate Limits & Messaging Tiers

### 22.1 Messaging Tiers

After going live, your account starts at the lowest tier and can upgrade based on quality and volume:

| Tier | Messages per 24h | How to Reach |
|------|-----------------|-------------|
| **Unverified** | 250 unique users | Before business verification |
| **Tier 1** | 1,000 unique users | After business verification |
| **Tier 2** | 10,000 unique users | Send 2x current limit in 7 days, quality rating ≥ Medium |
| **Tier 3** | 100,000 unique users | Send 2x current limit in 7 days, quality rating ≥ Medium |
| **Tier 4** | Unlimited | Send 2x current limit in 7 days, quality rating ≥ Medium |

### 22.2 Quality Rating

Meta monitors your message quality. A poor rating can restrict your account:

| Rating | Meaning | Impact |
|--------|---------|--------|
| **Green (High)** | Users rarely block/report you | Can upgrade tiers |
| **Yellow (Medium)** | Some users blocking/reporting | Cannot upgrade, warning |
| **Red (Low)** | Many users blocking/reporting | May be downgraded, restricted |

### 22.3 How to Maintain High Quality

- ✅ Only message users who expect to hear from you
- ✅ Respond quickly to customer queries
- ✅ Provide opt-out options
- ✅ Use relevant, personalized templates
- ❌ Don't spam users
- ❌ Don't send excessive marketing to uninterested users
- ❌ Don't send misleading content

### 22.4 API Rate Limits

| Endpoint | Rate Limit |
|----------|-----------|
| Sending messages | 80 messages/second per phone number |
| Webhook delivery | Meta retries for up to 7 days if your server is down |
| Graph API calls (general) | 200 calls/hour per user |

---

## 23. Production Security Checklist

### 23.1 Must-Do Security Items

- [ ] **Never expose Access Token** in frontend code, API responses, or logs
- [ ] **Verify webhook signatures** using App Secret (see Section 13.5)
- [ ] **Use HTTPS only** for your webhook URL
- [ ] **Store tokens encrypted** in database (for per-admin tokens)
- [ ] **Set IP whitelist** if possible on your server
- [ ] **Enable 2FA** on your Meta accounts
- [ ] **Rotate tokens** periodically (generate new System User Token)
- [ ] **Remove debug endpoints** in production:
  - `GET /api/whatsapp-debug` — exposes configuration
  - `POST /api/test-whatsapp` — allows sending test messages without auth
  - `POST /api/send-text` — allows sending messages without auth
  - `POST /api/send-template` — allows sending templates without auth

### 23.2 Secure Your API Routes

Your current `routes/api.php` has several unprotected endpoints. For production, protect them:

```php
// REMOVE or protect these in production:
// Route::post('/test-whatsapp', ...);
// Route::get('/whatsapp-debug', ...);
// Route::post('/send-text', ...);
// Route::post('/send-template', ...);

// Keep webhook routes unprotected (Meta needs to access them):
Route::get('/whatsapp/webhook', [WhatsAppWebhookController::class, 'verify']);
Route::post('/whatsapp/webhook', [WhatsAppWebhookController::class, 'handleWebhook']);
```

### 23.3 Environment-Specific Security

```php
// In routes/api.php, only register debug routes in non-production:
if (app()->environment('local', 'staging')) {
    Route::post('/test-whatsapp', [MessageController::class, 'testWhatsApp']);
    Route::get('/whatsapp-debug', [MessageController::class, 'whatsappDebug']);
    Route::post('/send-text', [MessageController::class, 'sendTextMessage']);
    Route::post('/send-template', [MessageController::class, 'sendTemplateMessage']);
}
```

---

## 24. Troubleshooting Common Issues

### 24.1 Common Error Codes

| Error Code | Message | Cause | Solution |
|-----------|---------|-------|----------|
| 131030 | Recipient not in allowed list | Sandbox limitation | Switch to production |
| 131047 | Re-engagement message | 24-hour window closed | Use template message |
| 131051 | Unsupported message type | Invalid message format | Check API docs |
| 132000 | Template not found | Invalid template name | Verify template name and approval |
| 132001 | Template parameter mismatch | Wrong number of parameters | Match template variables |
| 133010 | Phone number not registered | Number not on WhatsApp | Verify recipient has WhatsApp |
| 130429 | Rate limit hit | Too many messages | Slow down, upgrade tier |
| 190 | Invalid OAuth token | Token expired or invalid | Generate new System User Token |
| 10 | Permission denied | Missing permissions | Check System User scopes |
| 100 | Invalid parameter | Malformed request | Check JSON payload format |

### 24.2 Webhook Not Receiving Messages

1. **Check webhook URL** is correct and accessible from the internet
2. **Check SSL certificate** is valid (not self-signed)
3. **Check your server responds within 20 seconds**
4. **Check webhook subscription** — make sure `messages` field is subscribed
5. **Check Laravel logs** at `storage/logs/laravel.log`
6. **Check CSRF middleware** — webhook routes must be excluded from CSRF verification

**Verify CSRF exclusion** — check `app/Http/Middleware/VerifyCsrfToken.php`:
```php
protected $except = [
    'api/*',  // or specifically 'api/whatsapp/webhook'
];
```

Or if using Laravel 11+, check `bootstrap/app.php`:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->validateCsrfTokens(except: [
        'api/*',
    ]);
})
```

### 24.3 Messages Sending But Not Appearing in Dashboard

1. Check `conversations` table for the correct `visitor_id` format (`whatsapp_` prefix)
2. Check `messages` table for correct `conversation_id`
3. Verify `platform` is set to `whatsapp`
4. Check that the admin's `user_id` matches the conversation's `user_id`

### 24.4 Template Message Rejected

- Review Meta's template guidelines: https://developers.facebook.com/docs/whatsapp/message-templates/guidelines
- Ensure template has proper sample values for variables
- Don't include URL shorteners
- Don't use overly promotional language in utility templates
- Resubmit after corrections

### 24.5 Access Token Issues

| Symptom | Likely Cause | Fix |
|---------|-------------|-----|
| Token expires daily | Using temporary token | Generate System User Token (Step 7) |
| "Invalid OAuth access token" | Token revoked or incorrect | Regenerate from System Users |
| "Insufficient permissions" | Missing scopes | Add `whatsapp_business_messaging` scope |
| Token works in Postman but not in code | Incorrect header format | Ensure `Bearer ` prefix (with space) |

---

## 25. Quick Reference — All Credentials Needed

### Complete `.env` Template for Production

```env
# ============================================================
# WHATSAPP CLOUD API - Production Credentials
# ============================================================

# 1. System User Token (permanent, from Meta Business Settings > System Users)
#    Permissions needed: whatsapp_business_management, whatsapp_business_messaging
WHATSAPP_ACCESS_TOKEN=EAAxxxxxxxx...

# 2. Phone Number ID (from WhatsApp > API Setup > "From" section)
#    This is NOT your phone number — it's the ID Meta assigns to your number
WHATSAPP_PHONE_NUMBER_ID=123456789012345

# 3. Webhook Verify Token (any string YOU choose — must match Meta dashboard)
WHATSAPP_WEBHOOK_SECRET=my_super_secret_verify_token_2026

# 4. Production Webhook URL (your public HTTPS server)
WHATSAPP_WEBHOOK_URL=https://yourdomain.com/api/whatsapp/webhook

# 5. Default target phone (admin's personal WhatsApp for receiving visitor messages)
WHATSAPP_TARGET_PHONE_NUMBER=8801XXXXXXXXX

# 6. App Secret (from App Dashboard > Settings > Basic)
#    Used for webhook signature verification
WHATSAPP_APP_SECRET=abcdef1234567890

# 7. API Version (check latest at developers.facebook.com)
WHATSAPP_API_VERSION=v21.0
```

### Where to Find Each Credential

| Credential | URL | Navigation |
|-----------|-----|------------|
| **Access Token** | https://business.facebook.com/settings/system-users | System Users → Your User → Generate Token |
| **Phone Number ID** | https://developers.facebook.com/apps/{APP_ID}/whatsapp-business/wa-dev-console | WhatsApp → API Setup → From section |
| **Webhook Secret** | You create this yourself | Any random string |
| **App Secret** | https://developers.facebook.com/apps/{APP_ID}/settings/basic/ | Settings → Basic → App Secret → Show |
| **WABA ID** | https://business.facebook.com/wa/manage/ | Account Settings |

### Production Deployment Sequence

```
1. ✅ Create Meta Developer Account
2. ✅ Create Meta Business Portfolio  
3. ✅ Submit Business Verification (wait for approval)
4. ✅ Create Facebook App + add WhatsApp product
5. ✅ Register your real business phone number
6. ✅ Create System User + generate permanent token
7. ✅ Create & get templates approved
8. ✅ Deploy ConnectDesk to production server (HTTPS)
9. ✅ Update .env with production credentials
10. ✅ Configure webhook URL in Meta dashboard
11. ✅ Remove debug/test routes
12. ✅ Add webhook signature verification
13. ✅ Switch App to Live mode
14. ✅ Test end-to-end flow
15. ✅ Monitor quality rating and billing
```

---

## Appendix A: Useful Links

| Resource | URL |
|----------|-----|
| Meta Developer Dashboard | https://developers.facebook.com/apps/ |
| Meta Business Suite | https://business.facebook.com/ |
| WhatsApp Manager | https://business.facebook.com/wa/manage/ |
| WhatsApp Cloud API Docs | https://developers.facebook.com/docs/whatsapp/cloud-api |
| Message Templates Docs | https://developers.facebook.com/docs/whatsapp/message-templates |
| Pricing Page | https://developers.facebook.com/docs/whatsapp/pricing |
| Webhook Reference | https://developers.facebook.com/docs/whatsapp/cloud-api/webhooks |
| Error Codes Reference | https://developers.facebook.com/docs/whatsapp/cloud-api/support/error-codes |
| API Changelog | https://developers.facebook.com/docs/graph-api/changelog |
| Quality Rating Dashboard | https://business.facebook.com/wa/manage/phone-numbers/ |
| Business Verification Status | https://business.facebook.com/settings/security |
| System Users | https://business.facebook.com/settings/system-users |

## Appendix B: ConnectDesk File Reference

| File | Purpose |
|------|---------|
| `config/services.php` | WhatsApp credentials configuration |
| `app/Services/WhatsAppService.php` | Core API service (send text, templates, validate) |
| `app/Http/Controllers/WhatsAppWebhookController.php` | Webhook verification & incoming messages |
| `app/Http/Controllers/MessageController.php` | Message sending (visitor & admin) |
| `app/Http/Controllers/AdminDashboardController.php` | Admin panel & conversation management |
| `app/Http/Controllers/Admin/AdminSettingsController.php` | Per-admin WhatsApp credential management |
| `app/Models/Conversation.php` | Conversation model (visitor_id, platform, phone) |
| `app/Models/Message.php` | Message model (content, sender_type, platform) |
| `app/Models/User.php` | User model (includes whatsapp_access_token, whatsapp_phone_number_id) |
| `routes/api.php` | Webhook routes & API endpoints |
| `routes/web.php` | Admin dashboard & settings routes |
| `.env` | Environment credentials (WHATSAPP_ACCESS_TOKEN, etc.) |

---

> **End of Guide.** Follow these steps sequentially for a smooth transition from sandbox to production. The most time-consuming part is business verification (Step 3) — start that first while preparing the rest.
