# WhatsApp Business API - Quick Reference

## 🚦 Current Status: Development Mode

### What This Means:
- ✅ You can test with up to 5 phone numbers
- ❌ Cannot send to any phone number
- ✅ Free unlimited testing for 90 days
- ❌ Must add each test number in Facebook Developer Console

---

## 📱 How to Add Test Phone Numbers

### Step 1: Go to Facebook Developer Console
```
https://developers.facebook.com/apps/YOUR_APP_ID/whatsapp-business/api-setup/
```

### Step 2: Scroll to "To" Section
- Click "Manage phone number list"
- Click "Add phone number"
- Enter phone number with country code (e.g., +880 1983-427887)
- Click "Verify" (OTP will be sent to that number)
- Enter the 6-digit code received
- Number is now added!

### Step 3: Send Messages
- You can now send messages to this number
- Use the template: `hello_world`
- Or any approved template

---

## 🚀 Go to Production (Send to ANY Number)

### Timeline: 2-4 weeks

### Requirements:
1. ✅ Business Verification (2-7 days)
2. ✅ Display Name Approval (1-24 hours)
3. ✅ Template Approval (1-2 hours)
4. ✅ App Review (optional, 3-7 days)
5. ✅ Switch to Production Mode (instant)

### Read Full Guide:
📖 [WHATSAPP_PRODUCTION_MODE_GUIDE.md](WHATSAPP_PRODUCTION_MODE_GUIDE.md)

---

## 🔑 Quick Actions

### For Testing Now:
1. Add test numbers in Developer Console
2. Use template: `hello_world`
3. Test your integration

### For Production:
1. Start business verification today
2. Submit display name for approval
3. Create templates
4. Submit for app review
5. Go live in 2-4 weeks

---

## 💡 Pro Tips

### Best Practices:
- Always use templates for new conversations
- Text messages only work within 24-hour window
- Keep quality score high (avoid spam)
- Only message opted-in users

### Template Messages:
- Required for business-initiated conversations
- Must be pre-approved by WhatsApp
- Can include variables {{1}}, {{2}}, etc.
- Categories: Marketing, Utility, Authentication

### 24-Hour Window:
- After customer replies, free messaging for 24 hours
- Can send any text during this window
- After 24 hours, must use template again

---

## 🆘 Common Issues

### "Message not delivered"
**Cause:** Not in test numbers OR not in production
**Fix:** Add number to test list OR go to production

### "Template not found"
**Cause:** Template not approved
**Fix:** Wait for template approval (1-2 hours)

### "Invalid phone number"
**Cause:** Wrong format
**Fix:** Use format: 8801XXXXXXXXX (with country code, no + or spaces)

### "Business not verified"
**Cause:** Business verification pending
**Fix:** Complete business verification in Meta Business Suite

---

## 📞 Support Links

- Facebook Developer Console: https://developers.facebook.com/apps
- WhatsApp Manager: https://business.facebook.com/wa/manage/
- Business Settings: https://business.facebook.com/settings
- Help Center: https://developers.facebook.com/docs/whatsapp

---

## ✅ Checklist

### Current Status:
- [ ] Business verified
- [ ] Display name approved
- [ ] Templates created and approved
- [ ] Test numbers added
- [ ] App in Development Mode
- [ ] Can send to test numbers only

### Production Ready:
- [ ] Business verified ✓
- [ ] Display name approved ✓
- [ ] At least 1 template approved ✓
- [ ] App reviewed (if needed) ✓
- [ ] Switched to Production Mode ✓
- [ ] Can send to ANY number ✓

---

Last Updated: December 22, 2025
