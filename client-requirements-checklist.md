# Twilio WhatsApp Integration — Client Requirements Checklist

> **Project:** WhatsApp Messaging System via Twilio + Laravel  
> **Purpose:** Collect all necessary information, documents, and credentials from the client before starting development.

---

## 1. Business Information (For Meta Business Verification)

| # | Item | Details / Notes |
|---|------|-----------------|
| 1.1 | Legal Business Name | As it appears on official documents |
| 1.2 | Business Registration Number | Trade license / VAT |
| 1.3 | Registered Business Address | Full official address |
| 1.4 | Business Website URL | Must be live and accessible |
| 1.5 | Business Category / Industry | E.g., E-commerce, Healthcare, Education |
| 1.6 | Business Email | Preferably on own domain (e.g., info@company.com). Gmail/Yahoo may cause rejection |
| 1.7 | Official Verification Document | **Any ONE** of the following: |
|     | | — Business Registration Certificate (TIN) |
|     | | — Utility Bill in Business Name |
|     | | — Tax Registration Document |

---

## 2. Facebook / Meta Business Access

| # | Item | Details / Notes |
|---|------|-----------------|
| 2.1 | Facebook Business Manager Account | Client must have one at [business.facebook.com](https://business.facebook.com) |
| 2.2 | Admin Access to Business Manager | Grant developer admin role, OR share login |
| 2.3 | Business Manager ID | If an existing account is available |
| 2.4 | Client's Personal Facebook Account | May be needed for final verification approval by Meta |

> **Note:** If the client doesn't have a Business Manager, one will need to be created on their behalf.

---

## 3. Dedicated Phone Number for WhatsApp

| # | Item | Details / Notes |
|---|------|-----------------|
| 3.1 | Phone Number | Must be able to receive SMS or voice call (for OTP verification) |
| 3.2 | NOT Registered on WhatsApp | The number must **NOT** be currently active on WhatsApp Personal or Business app |
| 3.3 | If Currently on WhatsApp | Client must **delete the WhatsApp account** from that number first |
| 3.4 | Number Type | Can be: Mobile, Landline, or Twilio-purchased number |

> **Recommendation:** Ask the client to purchase a **new SIM card** dedicated to this purpose to avoid any conflicts.

---

## 4. WhatsApp Business Profile Details

These details appear when users view the business profile on WhatsApp.

| # | Item | Requirements |
|---|------|-------------|
| 4.1 | Business Display Name | Must match or closely relate to the verified business name (Meta will reject mismatches) |
| 4.2 | Profile Picture / Logo | Square format, minimum **640x640px**, PNG or JPG |
| 4.3 | Business Description | Short about text, **max 256 characters** |
| 4.4 | Business Category | E.g., E-commerce, Education, Healthcare, etc. |
| 4.5 | Website URL | Public-facing website |
| 4.6 | Contact Email | Displayed to WhatsApp users |
| 4.7 | Business Address | Displayed to WhatsApp users |

---

## 5. Message Templates

WhatsApp requires **pre-approved message templates** for business-initiated conversations. Collect the following for **each template**:

| # | Item | Example |
|---|------|---------|
| 5.1 | Template Purpose | Order confirmation, appointment reminder, payment receipt, promotional offer, OTP, etc. |
| 5.2 | Template Category | **Utility** (transactional) / **Marketing** (promotional) / **Authentication** (OTP) |
| 5.3 | Language(s) | English, Hindi, Arabic, etc. |
| 5.4 | Message Body | *"Hello {{1}}, your order {{2}} has been confirmed and will be delivered by {{3}}."* |
| 5.5 | Header (Optional) | Text, Image, Video, or Document |
| 5.6 | Footer (Optional) | Small text at bottom of message |
| 5.7 | Buttons (Optional) | URL button (e.g., "Track Order") or Quick Reply (e.g., "Confirm / Cancel") |

> **Important:** Get at least **2–3 templates drafted and approved** before starting development.

### Template Example Format:

```
Template Name   : order_confirmation
Category        : Utility
Language        : English
Header          : None
Body            : Hello {{1}}, your order #{{2}} has been confirmed. 
                  Expected delivery: {{3}}. Thank you for shopping with us!
Footer          : Reply STOP to unsubscribe
Buttons         : [Track Order → https://example.com/track/{{2}}]
```

---

## 6. Twilio Account & Credentials

| # | Item | Details / Notes |
|---|------|-----------------|
| 6.1 | Twilio Account | Client must create at [twilio.com](https://www.twilio.com) |
| 6.2 | Upgrade to Paid | Client must add credit card and load minimum **$15 balance** |
| 6.3 | Take Self image | For Account Verification |
| 6.4 | NID | For Account Upgrade |
| 6.5 | Account SID | Found on Twilio Console Dashboard |
| 6.6 | Auth Token | Found on Twilio Console Dashboard |
| 6.7 | Twilio Phone Number (if needed) | Purchase from Twilio if client doesn't have a dedicated number |

---

## 7. Quick Collection Card (Send to Client)

Copy-paste and send this directly to your client:

---

> ### What I Need From You to Start the WhatsApp Integration:
>
> 1. ✅ **Business registration document** (any one official document)
> 2. ✅ **Business details** — legal name, address, website, email
> 3. ✅ **Facebook Business Manager** account access (admin role)
> 4. ✅ **Phone number** NOT registered on WhatsApp (new SIM preferred)
> 5. ✅ **Business logo** — square, minimum 640x640px
> 6. ✅ **Short business description** — max 256 characters
> 7. ✅ **Message templates** — exact wording of messages you want to send customers (at least 2-3)
> 8. ✅ **Create a Twilio account** at [twilio.com](https://www.twilio.com), add a payment method, load $20, and share **Account SID** & **Auth Token**
> 9. ✅ **Server/hosting access** — domain, SSH/cPanel, database credentials
> 10. ✅ **Feature requirements** — how many admins, what features you need (media, scheduling, etc.)

---

## 8. Estimated Costs for Client

| Item | Approximate Cost |
|------|-----------------|
| Twilio WhatsApp message (Utility) | $0.005 – $0.05 per message (varies by country) |
| Twilio WhatsApp message (Marketing) | $0.02 – $0.08 per message |
| Twilio Phone Number (if needed) | ~$1 – $2 / month |
| Meta Business Verification | Free |
| WhatsApp Template Submission | Free |

> Refer to [Twilio WhatsApp Pricing](https://www.twilio.com/whatsapp/pricing) for country-specific rates.


---

*Document prepared for project planning and client onboarding.*
