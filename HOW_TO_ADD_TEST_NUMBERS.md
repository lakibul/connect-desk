# How to Add Test Phone Numbers (Development Mode)

## 🎯 Quick Guide to Add Test Numbers

Since you're in **Development Mode**, you can only send messages to phone numbers that are registered as test numbers in your Facebook Developer Console.

---

## 📋 Step-by-Step Instructions

### Step 1: Access Your WhatsApp App Settings

1. Go to: **https://developers.facebook.com/apps**
2. Click on your WhatsApp Business App
3. In the left sidebar, click **WhatsApp** → **API Setup**

### Step 2: Find the "To" Field Section

Scroll down to find the section that looks like your screenshot:
```
To
[Phone Number Field]
+880 1983-427887  [Selected]
+880 1604-509006  [Option]
+880 1983-427887  [Option]

Manage phone number list
```

### Step 3: Add New Test Number

1. Click **"Manage phone number list"**
2. A modal will appear
3. Click **"Add phone number"** button
4. Enter the phone number you want to add:
   - Format: `+880 1234-567890`
   - Or: `+8801234567890`
   - Example: `+880 1983-427887`

### Step 4: Verify the Number

1. After entering the number, click **"Send code"** or **"Verify"**
2. An OTP (6-digit code) will be sent to that WhatsApp number
3. Enter the code in the verification field
4. Click **"Confirm"** or **"Verify"**
5. ✅ Number is now added as a test number!

### Step 5: Use the Number

1. The number now appears in your test numbers list
2. You can send messages to this number from your app
3. You can add up to **5 test phone numbers**

---

## 🖼️ Visual Guide

### Your Current Screen:

```
┌─────────────────────────────────────────┐
│ From                                     │
│ Test number: +1 555 177 8055  [▼]      │
│                                          │
│ Phone number ID: 87535457899065 [📋]   │
│                                          │
│ To                                       │
│ +880 1983-427887              [▼]      │
│   ⚪ +880 1604-509006                   │
│   🔵 +880 1983-427887                   │
│                                          │
│   Manage phone number list              │
└─────────────────────────────────────────┘
```

### Click "Manage phone number list":

```
┌─────────────────────────────────────────┐
│  Manage Phone Numbers                    │
│                                          │
│  Test phone numbers (2/5 used)           │
│                                          │
│  📱 +880 1983-427887    [Remove]        │
│  📱 +880 1604-509006    [Remove]        │
│                                          │
│  [+ Add phone number]                    │
│                                          │
│  [Close]                                 │
└─────────────────────────────────────────┘
```

### Add New Number:

```
┌─────────────────────────────────────────┐
│  Add Phone Number                        │
│                                          │
│  Phone number:                           │
│  [+880 1234567890____________]           │
│                                          │
│  [Send verification code]                │
│                                          │
│  Verification code:                      │
│  [______]                                │
│                                          │
│  [Cancel]  [Verify]                      │
└─────────────────────────────────────────┘
```

---

## 💡 Important Notes

### Phone Number Format:
- ✅ Include country code: `+880`
- ✅ Can use spaces or dashes: `+880 1234-567890`
- ✅ Or no spaces: `+8801234567890`
- ❌ Don't use just: `01234567890` (missing country code)

### Verification:
- OTP is sent via **WhatsApp** (not SMS)
- Make sure the number is registered on WhatsApp
- Code expires in 10 minutes
- Can request new code if expired

### Limits:
- **Maximum 5 test numbers** in Development Mode
- No limit in Production Mode
- Can remove and add different numbers anytime

### Who Can Be Added:
- Your own phone number ✅
- Team members' numbers ✅
- Client test numbers ✅
- Anyone's WhatsApp number (with their permission) ✅

---

## 🔧 Troubleshooting

### Problem: "Invalid phone number"
**Solution:**
- Check country code is correct (+880 for Bangladesh)
- Remove any special characters except + and spaces
- Try format: +8801234567890

### Problem: "Verification code not received"
**Solution:**
- Ensure number is registered on WhatsApp
- Check WhatsApp is working on that phone
- Wait 1-2 minutes and check again
- Request new code

### Problem: "Can't add more numbers"
**Solution:**
- You've reached the 5 number limit
- Remove an existing test number first
- Or go to Production Mode for unlimited numbers

### Problem: "Number already added"
**Solution:**
- This number is already in your test list
- No need to add again
- Just select it from the dropdown

---

## 🚀 After Adding Test Number

### 1. Select the Number
In the "To" field dropdown, select your newly added number

### 2. Send a Test Message
Click the "Send Message" button to test

### 3. Check WhatsApp
Open WhatsApp on the test phone and verify message received

### 4. Try from Your App
Now you can send messages to this number from your ConnectDesk admin dashboard!

---

## 📱 Test Numbers You Currently Have

Based on your screenshot, you have:
1. ✅ +880 1983-427887 (Active/Selected)
2. ✅ +880 1604-509006 (Added)

**You can add 3 more test numbers!**

---

## 🎓 Next Steps

### For Testing (Now):
1. ✅ Add your test numbers (max 5)
2. ✅ Test sending messages using templates
3. ✅ Test conversation flow
4. ✅ Test message delivery

### For Production (Later):
1. 📋 Complete business verification
2. 📋 Get display name approved
3. 📋 Create and approve templates
4. 📋 Switch to Production Mode
5. 🚀 Send to ANY number (no test list needed!)

---

## 💰 Cost

### Development Mode:
- ✅ **FREE** for 90 days
- ✅ Unlimited messages to test numbers
- ✅ Perfect for development and testing

### Production Mode:
- ✅ 1,000 conversations/month FREE
- 💵 After that: ~$0.042 per conversation (Bangladesh)
- ✅ All features unlocked

---

## 🆘 Need Help?

### Can't Add Number:
1. Check you have permission to access the Facebook App
2. Verify you're the app admin or developer
3. Ensure the number is a valid WhatsApp number
4. Contact Facebook Support if issue persists

### Ready for Production:
Read the full guide: [WHATSAPP_PRODUCTION_MODE_GUIDE.md](WHATSAPP_PRODUCTION_MODE_GUIDE.md)

### Technical Issues:
Check Laravel logs: `storage/logs/laravel.log`

---

## ✅ Quick Checklist

- [ ] Go to developers.facebook.com/apps
- [ ] Select your WhatsApp Business App
- [ ] Click WhatsApp → API Setup
- [ ] Scroll to "To" field section
- [ ] Click "Manage phone number list"
- [ ] Click "Add phone number"
- [ ] Enter phone with country code
- [ ] Verify with OTP received on WhatsApp
- [ ] Number added! (appears in dropdown)
- [ ] Select number and send test message
- [ ] ✅ Success! Message received on WhatsApp

---

**Remember:** This is only for Development Mode. Once you go to Production Mode, you can send to ANY WhatsApp number without adding them first!

Good luck! 🚀

---

Last Updated: December 22, 2025
For Production Access: See [WHATSAPP_PRODUCTION_MODE_GUIDE.md](WHATSAPP_PRODUCTION_MODE_GUIDE.md)
