# WhatsApp Business API - Production Mode Setup Guide

## 🚨 Current Limitation: Development Mode

### What You're Experiencing Now:
- ❌ Can only send messages to phone numbers added in Facebook Developer Console
- ❌ Maximum 5 test phone numbers allowed
- ❌ Cannot message real customers/users
- ✅ Free testing for 90 days

### Why This Happens:
Facebook/WhatsApp keeps apps in **Development Mode** by default to prevent spam and ensure quality. You must complete verification to unlock production access.

---

## 🎯 Solution: Move to Production Mode

### Overview of Steps:
1. ✅ Verify your Business
2. ✅ Complete Business Verification
3. ✅ Submit App for Review
4. ✅ Get Approved Templates
5. ✅ Enable Production Mode
6. ✅ Start Sending to ANY Number

---

## 📋 Step-by-Step Guide

### Step 1: Business Verification (Meta Business Suite)

#### 1.1 Go to Meta Business Settings
```
https://business.facebook.com/settings
```

#### 1.2 Complete Business Information
- **Business Name**: Your legal company name
- **Business Address**: Physical business address
- **Business Phone**: Valid business phone number
- **Business Email**: Business email address
- **Business Website**: Your official website
- **Tax ID/Business Registration**: Upload documents

#### 1.3 Required Documents (Choose based on your country):
- Business Registration Certificate
- Tax Registration Document
- Business License
- Articles of Incorporation
- Utility Bill (showing business address)

**Bangladesh Specific:**
- Trade License
- TIN Certificate
- Business Address Proof

#### 1.4 Verification Timeline:
- Usually 1-3 business days
- Can take up to 7 days
- Check email for updates

---

### Step 2: WhatsApp Business Account Verification

#### 2.1 Navigate to WhatsApp Manager
```
https://business.facebook.com/wa/manage/
```

#### 2.2 Display Name Verification
1. Click on your WhatsApp Business phone number
2. Go to "Settings" → "Profile"
3. Submit your **Display Name** for approval
   - Must represent your business accurately
   - Cannot be generic (e.g., "Customer Support")
   - Should match your business name

#### 2.3 Display Name Requirements:
- ✅ Clearly identifies your business
- ✅ No URLs or phone numbers
- ✅ No special characters (except &, -, ')
- ✅ Maximum 25 characters
- ❌ No generic terms
- ❌ No misleading names

---

### Step 3: Create Message Templates

#### 3.1 Why Templates Are Required:
- WhatsApp requires pre-approved templates for business-initiated conversations
- Templates ensure quality and prevent spam
- You can send free-form text only AFTER customer replies

#### 3.2 Go to Message Templates
```
https://business.facebook.com/wa/manage/message-templates/
```

#### 3.3 Create Templates:

**Example 1: Welcome Template**
```
Name: welcome_message
Category: ACCOUNT_UPDATE
Language: English

Message:
Hello! 👋 Welcome to [Your Business Name]. We're here to help you. How can we assist you today?
```

**Example 2: Order Confirmation**
```
Name: order_confirmation
Category: SHIPPING_UPDATE
Language: English

Message:
Your order #{{1}} has been confirmed! 📦 
Estimated delivery: {{2}}
Track your order: {{3}}
```

**Example 3: Appointment Reminder**
```
Name: appointment_reminder
Category: APPOINTMENT_UPDATE
Language: English

Message:
Hi {{1}}, this is a reminder about your appointment on {{2}} at {{3}}. 
Reply CONFIRM to confirm or RESCHEDULE to change the time.
```

#### 3.4 Template Approval:
- Usually approved within 1-2 hours
- Can take up to 24 hours
- You'll receive notification when approved

---

### Step 4: App Review & Permissions

#### 4.1 Go to App Dashboard
```
https://developers.facebook.com/apps/YOUR_APP_ID/
```

#### 4.2 Request Advanced Permissions:

**Required Permissions:**
1. **whatsapp_business_management**
   - Purpose: "Manage WhatsApp Business account and send messages to customers"
   
2. **whatsapp_business_messaging**
   - Purpose: "Send and receive messages on behalf of business"

#### 4.3 App Review Submission:

1. Click "App Review" in left sidebar
2. Click "Request Advanced Access"
3. Select permissions above
4. Fill out the form:

**Questions You'll Need to Answer:**
- How will you use WhatsApp API?
- What types of messages will you send?
- How did you obtain user phone numbers?
- Privacy policy URL
- Terms of service URL
- Demo video (optional but recommended)

**Example Answers:**
```
Use Case: Customer Support & Notifications
Message Types: Order updates, support responses, appointment reminders
Phone Number Collection: Customers provide numbers during signup/checkout
Privacy: We comply with GDPR and only message opted-in users
```

#### 4.4 Create Demo Video (Recommended):
- Show your website/app signup flow
- Show how users opt-in to WhatsApp messages
- Show your message sending interface
- Show sample messages being sent
- Duration: 2-5 minutes
- Upload to YouTube (unlisted)

---

### Step 5: Switch to Production Mode

#### 5.1 Prerequisites Checklist:
- ✅ Business verified
- ✅ Display name approved
- ✅ At least 1 template approved
- ✅ App review completed (for advanced permissions)
- ✅ Payment method added (if required)

#### 5.2 Enable Production Mode:

1. Go to WhatsApp → API Setup:
```
https://developers.facebook.com/apps/YOUR_APP_ID/whatsapp-business/api-setup/
```

2. Look for "App Mode" toggle
3. Switch from "Development" to "Production"
4. Confirm the switch

#### 5.3 Generate Production Access Token:
```bash
# Your production token will have broader access
# Update this in your Laravel .env file
```

---

### Step 6: Update Your Laravel Application

#### 6.1 Update Environment Variables:

```env
# .env file - Update with production credentials
WHATSAPP_ACCESS_TOKEN=your_production_token_here
WHATSAPP_PHONE_NUMBER_ID=875354578999065
WHATSAPP_VERIFY_TOKEN=your_secure_verify_token
```

#### 6.2 Update Admin Settings:
1. Login to admin dashboard
2. Go to Settings
3. Update WhatsApp credentials with production token
4. Save changes

---

## 🚀 Post-Production Checklist

### After Going Live:

1. **Test with Real Numbers:**
   - Send template to your own phone
   - Send to colleague's phone
   - Verify messages are delivered

2. **Monitor Usage:**
   - Check message counts in WhatsApp Manager
   - Monitor delivery rates
   - Track conversation quality score

3. **Quality Rating:**
   - WhatsApp monitors your quality score
   - Stay above "Medium" quality
   - Low quality = account restrictions

4. **Messaging Limits:**
   - Start with 250 messages per 24 hours
   - Increases automatically with good quality:
     - 1,000/day → 10,000/day → 100,000/day → Unlimited

---

## 💰 Pricing (After Free Trial)

### Free Tier:
- 1,000 business-initiated conversations per month (FREE)
- Customer-initiated conversations (always FREE)

### Paid Conversations:
- **Business-initiated**: ~$0.03 - $0.10 per conversation (varies by country)
- **24-hour window**: Once customer replies, free messaging for 24 hours
- **Bangladesh pricing**: ~$0.042 per conversation

### Payment Setup:
```
https://business.facebook.com/settings/payment-methods
```

---

## 🔧 Alternative: Use Cloud API (Recommended)

If you don't want to deal with app review, use **WhatsApp Cloud API** directly:

### Cloud API Benefits:
- ✅ No app review needed for basic use
- ✅ Faster setup
- ✅ Same features
- ✅ Better documentation
- ✅ Direct integration

### Cloud API Setup:

1. **Go to Cloud API:**
```
https://developers.facebook.com/docs/whatsapp/cloud-api/get-started
```

2. **Create WhatsApp Business Account:**
   - Skip traditional app creation
   - Use Cloud API directly

3. **Get Credentials:**
   - Phone Number ID (same as you have)
   - Access Token (permanent token)

4. **Still Need:**
   - Business verification
   - Display name approval
   - Template approval
   - But NO app review for basic messaging!

---

## 🆘 Troubleshooting

### Issue: Can't Switch to Production
**Solution:** Complete all prerequisites first
- Verify business is approved
- At least 1 template approved
- Display name approved

### Issue: App Review Rejected
**Common Reasons:**
- Unclear use case explanation
- Missing privacy policy
- No phone number collection consent shown
- Insufficient documentation

**Fix:** Resubmit with:
- Detailed use case
- Privacy policy link
- Terms of service link
- Demo video showing opt-in process

### Issue: Templates Keep Getting Rejected
**Common Reasons:**
- Too promotional
- Missing variable examples
- Unclear purpose
- Wrong category

**Fix:**
- Use transactional templates first
- Avoid marketing language
- Provide clear variable examples
- Choose correct category

### Issue: Low Quality Score
**Causes:**
- Spam reports from users
- Messages to invalid numbers
- Messages to users who didn't opt-in
- Too many blocked numbers

**Fix:**
- Only message opted-in users
- Validate phone numbers before sending
- Provide opt-out option
- Monitor feedback

---

## 📊 Quality Score Guidelines

### Green (High Quality):
- ✅ Continue normal operations
- ✅ Messaging limits increase
- ✅ All features available

### Yellow (Medium Quality):
- ⚠️ Warning state
- ⚠️ No immediate restrictions
- ⚠️ Improve quickly to avoid limits

### Red (Low Quality):
- ❌ Messaging limits reduced
- ❌ May lose production access
- ❌ Account under review

### How to Maintain Quality:
1. Only message users who opted-in
2. Send relevant, timely messages
3. Respect user preferences
4. Respond quickly to inquiries
5. Provide clear opt-out instructions

---

## 🎯 Quick Start for Production (Minimum Requirements)

### Fastest Path to Production:

1. **Business Verification** (1-3 days)
   - Upload business documents
   - Wait for approval

2. **Display Name** (1-24 hours)
   - Submit display name
   - Wait for approval

3. **One Template** (1-2 hours)
   - Create simple template (e.g., hello_world already approved)
   - Wait for approval

4. **Switch Mode** (Instant)
   - Toggle to Production
   - Update access token

**Total Time: 2-5 days** (with all approvals)

---

## 📝 Current Status Check

### Check Your Status:

1. **Business Verification:**
   - https://business.facebook.com/settings/info

2. **Display Name:**
   - https://business.facebook.com/wa/manage/ → Settings → Profile

3. **Templates:**
   - https://business.facebook.com/wa/manage/message-templates/

4. **App Mode:**
   - https://developers.facebook.com/apps/YOUR_APP_ID/whatsapp-business/api-setup/

5. **Permissions:**
   - https://developers.facebook.com/apps/YOUR_APP_ID/app-review/

---

## 🔐 Security Best Practices

### Protect Your Access Token:
```php
// Never commit token to git
// Use .env file
// Rotate tokens periodically
```

### Validate Phone Numbers:
```php
// Always validate before sending
// Use WhatsAppService->validateWhatsAppNumber()
```

### User Consent:
```php
// Only message users who opted-in
// Store opt-in timestamp
// Provide easy opt-out
```

---

## 📞 Support & Resources

### Official Documentation:
- WhatsApp Business API: https://developers.facebook.com/docs/whatsapp
- Cloud API: https://developers.facebook.com/docs/whatsapp/cloud-api
- Templates: https://developers.facebook.com/docs/whatsapp/message-templates
- Pricing: https://developers.facebook.com/docs/whatsapp/pricing

### Support Channels:
- Developer Community: https://developers.facebook.com/community/
- WhatsApp Business Support: https://business.whatsapp.com/support
- Meta Business Help: https://www.facebook.com/business/help

### Useful Tools:
- API Testing: https://developers.facebook.com/tools/explorer/
- Template Testing: Use the API explorer
- Webhook Testing: https://webhook.site/

---

## ✅ Summary

### Right Now (Development Mode):
- ❌ Can only send to 5 test numbers
- ✅ Free unlimited testing for 90 days
- ✅ Perfect for development and testing

### After Production Mode:
- ✅ Send to ANY WhatsApp number
- ✅ No test number limitations
- ✅ 1,000 free conversations/month
- ✅ Professional business messaging

### Action Items:
1. ☐ Verify your business
2. ☐ Approve display name
3. ☐ Create and approve templates
4. ☐ Submit app for review (if using App API)
5. ☐ Switch to production mode
6. ☐ Update access token
7. ☐ Start messaging customers!

---

## 🎓 Next Steps for You

Based on your screenshot, here's what you need to do:

1. **Immediate**: Keep using test numbers for development
2. **This Week**: Start business verification process
3. **Next Week**: Submit display name and templates
4. **Week 3-4**: Complete app review (if needed)
5. **Go Live**: Switch to production and update token

**Estimated Timeline**: 2-4 weeks for full production access

Good luck! 🚀
