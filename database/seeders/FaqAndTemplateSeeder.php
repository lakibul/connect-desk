<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\MessageTemplate;
use Illuminate\Database\Seeder;

class FaqAndTemplateSeeder extends Seeder
{
    public function run(): void
    {
        // ── FAQs ──────────────────────────────────────────────────────────────
        $faqs = [
            [
                'sort_order' => 1,
                'payload'    => 'faq_business_hours',
                'question'   => 'What are your business hours?',
                'answer'     => "🕐 *Business Hours:*\n\nMonday – Friday: 9:00 AM – 6:00 PM\nSaturday: 10:00 AM – 4:00 PM\nSunday: Closed\n\n_Tap *FAQ* anytime to see all questions._",
            ],
            [
                'sort_order' => 2,
                'payload'    => 'faq_track_order',
                'question'   => 'How can I track my order?',
                'answer'     => "📦 *Order Tracking:*\n\nYou can track your order:\n• Check the confirmation email sent to you\n• Visit our website and enter your Order ID\n• Reply here with your Order ID for direct help\n\n_Tap *FAQ* anytime to see all questions._",
            ],
            [
                'sort_order' => 3,
                'payload'    => 'faq_return_policy',
                'question'   => 'What is your return policy?',
                'answer'     => "🔄 *Return Policy:*\n\n• Returns accepted within *30 days* of purchase\n• Item must be in original, unused condition\n• Contact us to initiate a return request\n• Refund processed within 5–7 business days\n\n_Tap *FAQ* anytime to see all questions._",
            ],
            [
                'sort_order' => 4,
                'payload'    => 'faq_contact_support',
                'question'   => 'How do I contact support?',
                'answer'     => "💬 *Contact Support:*\n\n• *Chat:* Reply directly to this message\n• *Email:* support@connectdesk.com\n• *Website:* Live chat available\n\nOur team responds within *2 hours* during business hours. 🌟\n\n_Tap *FAQ* anytime to see all questions._",
            ],
            [
                'sort_order' => 5,
                'payload'    => 'faq_payment_methods',
                'question'   => 'What payment methods do you accept?',
                'answer'     => "💳 *Payment Methods:*\n\nWe accept:\n• Credit/Debit Cards (Visa, Mastercard)\n• Mobile Banking (bKash, Nagad, Rocket)\n• Bank Transfer\n• Cash on Delivery (selected areas)\n\nAll transactions are *secure & encrypted* 🔒\n\n_Tap *FAQ* anytime to see all questions._",
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::updateOrCreate(['payload' => $faq['payload']], $faq);
        }

        // ── Message Templates ─────────────────────────────────────────────────
        $templates = [
            [
                'name'    => 'hello_world',
                'label'   => 'Hello World',
                'content' => "👋 Hello! Welcome to our service. We're here to help you. How can we assist you today?",
            ],
            [
                'name'    => 'thank_you',
                'label'   => 'Thank You',
                'content' => "🙏 Thank you for contacting us! We appreciate your message and will get back to you shortly.",
            ],
            [
                'name'    => 'welcome_message',
                'label'   => 'Welcome Message',
                'content' => "🌟 Welcome! Thank you for connecting with us. We're excited to serve you. Feel free to ask any questions!",
            ],
            [
                'name'    => 'welcome_bangla_message',
                'label'   => 'Welcome Message (Bangla)',
                'content' => "আমার মুরাদনগরে আপনাকে স্বাগতম। আপনার সকল সেবার প্রয়োজন পূরণে আমরা সর্বদা প্রস্তুত।\n\nযেকোনো প্রয়োজনে আমাদের সাথে যোগাযোগ করুন:\n📞 কল করুন: +8801234567890\n💬 অথবা হোয়াটসঅ্যাপে যোগাযোগ করুন।\n\nআমাদের সাপোর্ট টিম ২৪/৭ আপনার সেবায় নিয়োজিত।",
            ],
            [
                'name'    => 'appointment_reminder',
                'label'   => 'Appointment Reminder',
                'content' => "📅 Reminder: You have an appointment scheduled. Please confirm your attendance or reschedule if needed.",
            ],
            [
                'name'    => 'sample_purchase_feedback',
                'label'   => 'Purchase Feedback',
                'content' => "🛍️ Thank you for your recent purchase! We'd love to hear your feedback. How was your experience with us?",
            ],
            [
                'name'    => 'sample_happy_hour_announcement',
                'label'   => 'Happy Hour Announcement',
                'content' => "🎉 Special Offer! Join us for Happy Hour today! Enjoy exclusive deals and discounts. Don't miss out!",
            ],
            [
                'name'    => 'sample_flight_confirmation',
                'label'   => 'Flight Confirmation',
                'content' => "✈️ Flight Confirmation: Your booking has been confirmed. Check your email for details. Have a safe journey!",
            ],
            [
                'name'    => 'sample_movie_ticket_confirmation',
                'label'   => 'Movie Ticket Confirmation',
                'content' => "🎬 Movie Ticket Confirmed! Your booking is successful. Show this message at the counter. Enjoy the show!",
            ],
            [
                'name'    => 'sample_issue_resolution',
                'label'   => 'Issue Resolution',
                'content' => "✅ Issue Resolved: We've addressed your concern. Thank you for your patience. Is there anything else we can help with?",
            ],
            [
                'name'    => 'sample_shipping_confirmation',
                'label'   => 'Shipping Confirmation',
                'content' => "📦 Shipping Update: Your order has been dispatched and is on its way. Track your package using the link in your email.",
            ],
        ];

        foreach ($templates as $template) {
            MessageTemplate::updateOrCreate(['name' => $template['name']], $template);
        }
    }
}
