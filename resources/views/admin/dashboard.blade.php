<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ConnectDesk - Admin Dashboard</title>

    <!-- External Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="{{ asset('css/admin-dashboard.css') }}" rel="stylesheet">
    <!-- App Config (injected from server) -->
    <script>
        const TWILIO_TARGET_PHONE = "{{ config('services.twilio.target_phone_number', '') }}";
    </script>
    <style>
        /* Alert banner takes its natural height, never stretches */
        .dashboard-container > .alert { flex: 0 0 auto; }

        /* Main row fills all remaining space below the navbar */
        .dashboard-container > .row.g-0.h-100 {
            flex: 1 1 auto;
            min-height: 0;
            height: 0 !important; /* Override Bootstrap h-100; flex handles the height */
        }

        /* Chat area: flex column, fills the Bootstrap column via stretch */
        .chat-main {
            display: flex;
            flex-direction: column;
            min-height: 0;
            overflow: hidden;
        }

        /* Chat interface: grows to fill chat-main, 3-section flex column layout */
        .chat-interface {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
            overflow: hidden;
        }

        /* Messages area: expands to fill the middle section, scrolls when full */
        .messages-container {
            flex: 1 1 0;
            min-height: 0;
            overflow-y: auto;
            overflow-x: hidden;
        }

        /* Input bar: always visible at the bottom, never shrinks or hides */
        .message-input-container {
            flex-shrink: 0;
            flex-grow: 0;
        }
    </style>
</head>
<body>
    <!-- Navigation Header -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm border-bottom">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <i class="bi bi-chat-dots-fill me-2 text-success"></i>
                <span class="fw-bold">ConnectDesk</span>
            </a>

            <div class="d-flex align-items-center ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center ps-3 pe-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="me-2 d-none d-md-inline fw-medium">{{ auth()->user()->name ?? 'Admin' }}</span>
                        <div class="avatar-circle">
                            <i class="bi bi-person-fill"></i>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.settings') }}"><i class="bi bi-gear me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('admin.faqs.index') }}"><i class="bi bi-question-circle me-2 text-success"></i>Manage FAQs</a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.templates.index') }}"><i class="bi bi-file-text me-2 text-primary"></i>Manage Templates</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Dashboard Container -->
    <div class="dashboard-container">
        <!-- WhatsApp Status Alert -->
        @if(empty(auth()->user()->twilio_account_sid) || empty(auth()->user()->twilio_auth_token))
        <div class="alert alert-warning alert-dismissible fade show m-3" role="alert" style="border-radius: 12px;">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Twilio WhatsApp Not Connected:</strong> Configure your Twilio credentials in
            <a href="{{ route('admin.settings') }}" class="alert-link">Settings</a> to send WhatsApp messages.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <div class="row g-0 h-100">
            <!-- Sidebar -->
            <div class="col-md-3 sidebar-container">
                <div class="sidebar">
                    <!-- Platform Tabs -->
                    <div class="platform-tabs">
                        <button class="platform-tab active" data-platform="whatsapp">
                            <i class="bi bi-whatsapp"></i>
                            <span>WhatsApp</span>
                            <span class="badge">0</span>
                        </button>
                    </div>

                    <!-- New Conversation Button -->
                    <div class="p-3 pb-1">
                        <button class="btn btn-success w-100 new-conversation-btn" id="newConversationBtn">
                            <i class="bi bi-plus-circle-fill me-2"></i>
                            Start New Conversation
                        </button>
                    </div>
                    <!-- Quick FAQ Button: sends FAQ to the currently open conversation -->
                    <div class="px-3 pb-3">
                        <button class="btn btn-outline-info w-100" id="quickFaqBtn"
                                title="Send FAQ menu to the currently open conversation" disabled>
                            <i class="bi bi-question-circle me-2"></i>
                            Send FAQ to Current Chat
                        </button>
                    </div>

                    <!-- Search and Filter -->
                    <div class="chat-controls">
                        <div class="search-container">
                            <i class="bi bi-search"></i>
                            <input type="text" class="form-control" placeholder="Search conversations..." id="searchInput">
                        </div>
                        <div class="filter-controls">
                            <select class="form-select form-select-sm" id="statusFilter">
                                <option value="all">All Status</option>
                                <option value="unread">Unread</option>
                                <option value="active">Active</option>
                                <option value="resolved">Resolved</option>
                            </select>
                        </div>
                    </div>

                    <!-- Chat Thread Lists -->
                    <div class="chat-threads">
                        <!-- WhatsApp Threads -->
                        <div class="thread-container active" id="whatsapp-threads">
                            <div class="thread-empty">No WhatsApp conversations yet.</div>
                        </div>

                        <!-- Facebook Threads -->
                        <div class="thread-container" id="facebook-threads">
                            <div class="thread-empty">No Facebook conversations yet.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Chat Area -->
            <div class="col-md-9 chat-main">
                <div class="chat-interface">
                    <!-- Chat Header -->
                    <div class="chat-header">
                        <div class="chat-user-info">
                            <div class="user-avatar">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <div class="user-details">
                                <h5 class="user-name">Select a conversation</h5>
                                <div class="user-status">
                                    <span class="platform-indicator whatsapp">
                                        <i class="bi bi-whatsapp"></i>
                                        <span class="platform-label">WhatsApp</span>
                                    </span>
                                    <span class="status-dot online"></span>
                                    <span class="status-text" id="userPhone">No chat selected</span>
                                </div>
                            </div>
                        </div>
                        <div class="chat-actions">
                            <button class="btn btn-success btn-sm" id="newWhatsAppBtn" title="Start new WhatsApp conversation">
                                <i class="bi bi-plus-circle me-1"></i>
                                <span class="d-none d-md-inline">New</span>
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" title="Search in conversation">
                                <i class="bi bi-search"></i>
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" title="Call">
                                <i class="bi bi-telephone"></i>
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" title="Video call">
                                <i class="bi bi-camera-video"></i>
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" title="More options">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Messages Container -->
                    <div class="messages-container" id="messagesContainer">
                        <div class="typing-indicator" style="display: none;">
                            <div class="typing-dots">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                            <span class="typing-text">John is typing...</span>
                        </div>
                    </div>

                    <!-- Message Input -->
                    <div class="message-input-container">
                        <div class="message-options d-flex gap-2 align-items-center">
                            <select class="form-select form-select-sm" id="messageTypeSelect" style="flex:1;">
                                <option value="text" selected>Text message</option>
                                <option value="template">📤 Template message</option>
                            </select>
                            <button class="btn btn-outline-info btn-sm" id="faqSendBtn"
                                    title="Send FAQ menu to this conversation" style="white-space:nowrap;">
                                <i class="bi bi-question-circle me-1"></i>FAQ
                            </button>
                            <div id="templateOptionsContainer" style="display: none; gap: 8px;" class="d-flex">
                                <select class="form-select form-select-sm" id="templateSelector" style="flex: 1;">
                                    <option value="">-- Select Template --</option>
                                    <option value="hello_world">👋 hello_world (Welcome)</option>
                                    <option value="thank_you">🙏 thank_you (Thanks)</option>
                                    <option value="welcome_message">🌟 welcome_message (Warm Welcome)</option>
                                    <option value="sample_purchase_feedback">🛍️ sample_purchase_feedback (Feedback)</option>
                                    <option value="sample_happy_hour_announcement">🎉 sample_happy_hour_announcement (Promo)</option>
                                    <option value="appointment_reminder">📅 appointment_reminder (Reminder)</option>
                                    <option value="custom">🔧 Custom Twilio Content SID...</option>
                                </select>
                                <input type="text" class="form-control form-control-sm" id="templateNameInput" placeholder="Enter Twilio Content SID (HXxxxxx)" style="display: none; flex: 1;">
                            </div>
                        </div>

                        <!-- FAQ Info banner (shown statically below the input row) -->
                        <div id="faqInfoBanner" class="faq-preview-container" style="display:none;">
                            <div class="faq-preview-header">
                                <i class="bi bi-question-circle-fill me-2 text-info"></i>
                                <strong>FAQ Menu</strong>
                                <span class="ms-2 text-muted" style="font-size:0.8rem;">Customer replies 1–5 to get instant answer — auto-replied by system</span>
                            </div>
                            <div class="faq-preview-items">
                                <div><span class="faq-num">1️⃣</span>  What are your business hours?</div>
                                <div><span class="faq-num">2️⃣</span>  How can I track my order?</div>
                                <div><span class="faq-num">3️⃣</span>  What is your return policy?</div>
                                <div><span class="faq-num">4️⃣</span>  How do I contact support?</div>
                                <div><span class="faq-num">5️⃣</span>  What payment methods do you accept?</div>
                            </div>
                            <small class="text-muted faq-sandbox-note">
                                <i class="bi bi-info-circle me-1"></i>
                                Works in Sandbox &amp; Production. Triggers: type <strong>FAQ</strong>, <strong>help</strong>, <strong>hi</strong>, or <strong>hello</strong>.
                            </small>
                        </div>

                        <div class="message-input">
                            <button class="attachment-btn" title="Attach file">
                                <i class="bi bi-paperclip"></i>
                            </button>
                            <div class="text-input-wrapper">
                                <textarea class="form-control" placeholder="Type a message..." id="messageInput" rows="1"></textarea>
                            </div>
                            <button class="emoji-btn" title="Emoji">
                                <i class="bi bi-emoji-smile"></i>
                            </button>
                            <button class="send-btn" id="sendBtn">
                                <i class="bi bi-send-fill"></i>
                            </button>
                        </div>

                        <!-- Quick Replies -->
                        <div class="quick-replies">
                            <button class="quick-reply-btn">Thank you for contacting us!</button>
                            <button class="quick-reply-btn">How can I help you today?</button>
                            <button class="quick-reply-btn">Let me check that for you</button>
                            <button class="quick-reply-btn">Is there anything else I can help with?</button>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div class="empty-chat-state" id="emptyChatState" style="display: none;">
                    <div class="empty-state-content">
                        <i class="bi bi-chat-dots"></i>
                        <h4>Select a conversation</h4>
                        <p>Choose a conversation from the sidebar to start messaging</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- New WhatsApp Message Modal -->
    <div class="modal fade" id="newWhatsAppModal" tabindex="-1" aria-labelledby="newWhatsAppModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newWhatsAppModalLabel">
                        <i class="bi bi-whatsapp text-success me-2"></i>
                        Start New WhatsApp Conversation
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger d-none" id="newWhatsAppError"></div>
                    <div class="alert alert-success d-none" id="newWhatsAppSuccess"></div>

                    <!-- Development Mode Notice -->
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Development Mode:</strong> You can only send to phone numbers added as test numbers in
                        <a href="https://developers.facebook.com/apps" target="_blank" class="alert-link">Facebook Developer Console</a>.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>

                    <form id="newWhatsAppForm">
                        <div class="recipient-mode-card">
                            <div class="recipient-mode-header">
                                <div>
                                    <label class="form-label fw-semibold mb-1">Recipients</label>
                                    <p class="text-muted small mb-0">Send to a single number or multiple recipients at once.</p>
                                </div>
                                <div class="recipient-mode-toggle btn-group" role="group" aria-label="Recipient mode">
                                    <input type="radio" class="btn-check" name="recipientMode" id="recipientModeSingle" autocomplete="off" checked>
                                    <label class="btn btn-outline-primary btn-sm" for="recipientModeSingle">Single</label>
                                    <input type="radio" class="btn-check" name="recipientMode" id="recipientModeBulk" autocomplete="off">
                                    <label class="btn btn-outline-primary btn-sm" for="recipientModeBulk">Multiple</label>
                                </div>
                            </div>
                        </div>

                        <div class="recipient-section mb-3" id="singleRecipientSection">
                            <label class="form-label fw-semibold">WhatsApp Number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="text" class="form-control" id="newWhatsAppNumber"
                                    placeholder="e.g., 8801XXXXXXXXX or 01XXXXXXXXX">
                            </div>
                            <small class="form-text text-muted">
                                Enter with country code (e.g., 8801XXXXXXXXX) or without (e.g., 01XXXXXXXXX)
                            </small>
                        </div>

                        <div class="recipient-section mb-3 d-none" id="bulkRecipientSection">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-semibold mb-0">WhatsApp Numbers</label>
                                <span class="bulk-count-badge" id="bulkCountBadge">0 numbers</span>
                            </div>
                            <textarea class="form-control bulk-input" id="bulkWhatsAppNumbers" rows="4"
                                placeholder="Paste numbers separated by comma, space, or new line."></textarea>
                            <div class="bulk-actions">
                                <div class="bulk-actions-left">
                                    <label class="btn btn-outline-secondary btn-sm mb-0">
                                        <i class="bi bi-upload me-1"></i>Upload CSV
                                        <input type="file" id="bulkUploadInput" accept=".csv,.txt" hidden>
                                    </label>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="bulkClearBtn">
                                        <i class="bi bi-x-circle me-1"></i>Clear
                                    </button>
                                </div>
                                <span class="text-muted small">Max 50 numbers per batch.</span>
                            </div>
                            <div class="bulk-preview" id="bulkPreview">
                                <div class="bulk-preview-title">Preview</div>
                                <div class="bulk-preview-list" id="bulkPreviewList">
                                    <span class="text-muted small">Add numbers to see a preview.</span>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                A separate conversation will be created for each recipient.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Message Type</label>
                            <select class="form-select" id="newMessageType">
                                <option value="template" selected>Template Message (Recommended for new chats)</option>
                                <option value="text">Text Message</option>
                            </select>
                            <small class="form-text text-muted">
                                <i class="bi bi-info-circle"></i> Use a Template to start new conversations. Once the user replies, switch to Text.
                                To send the FAQ menu, open the conversation and click the <strong>FAQ</strong> button.
                            </small>
                        </div>

                        <div class="mb-3" id="templateSection">
                            <label class="form-label fw-semibold">Template Selection</label>
                            <select class="form-select mb-2" id="newTemplateSelector">
                                <option value="">-- Select a Template --</option>
                                <optgroup label="Quick Start Templates (Text-based)">
                                    <option value="hello_world" selected>👋 hello_world - Welcome Message</option>
                                    <option value="thank_you">🙏 thank_you - Thank You Message</option>
                                    <option value="welcome_message">🌟 welcome_message - Warm Welcome</option>
                                    <option value="sample_purchase_feedback">🛍️ sample_purchase_feedback - Purchase Feedback</option>
                                    <option value="sample_happy_hour_announcement">🎉 sample_happy_hour_announcement - Promotion</option>
                                    <option value="sample_flight_confirmation">✈️ sample_flight_confirmation - Flight Booking</option>
                                    <option value="sample_movie_ticket_confirmation">🎬 sample_movie_ticket_confirmation - Movie Ticket</option>
                                    <option value="sample_issue_resolution">✅ sample_issue_resolution - Issue Resolution</option>
                                    <option value="sample_shipping_confirmation">📦 sample_shipping_confirmation - Shipping Update</option>
                                    <option value="appointment_reminder">📅 appointment_reminder - Appointment Reminder</option>
                                </optgroup>
                                <optgroup label="Twilio Content Templates">
                                    <option value="custom">🔧 Enter Twilio Content SID (HXxxxxx...)</option>
                                    <option value="random">🎲 Send Random Template</option>
                                </optgroup>
                            </select>
                            <input type="text" class="form-control" id="newTemplateName"
                                placeholder="Enter your Twilio Content Template SID (e.g., HXa1b2c3d4e5...)"
                                style="display: none;">
                            <small class="form-text text-muted" id="templateHelpText">
                                <i class="bi bi-info-circle"></i> Pre-built templates send instantly. For Twilio Content SIDs, select "Custom" option.
                            </small>
                        </div>

                        <div class="mb-3 d-none" id="textMessageSection">
                            <label class="form-label fw-semibold">Message</label>
                            <textarea class="form-control" id="newWhatsAppInitialMessage" rows="3"
                                placeholder="Type your message..."></textarea>
                            <small class="form-text text-muted">
                                Note: Text messages only work if customer messaged you in last 24 hours
                            </small>
                        </div>



                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success" id="startConversationBtn">
                                <i class="bi bi-check-circle me-2"></i>
                                <span id="btnText">Start Conversation</span>
                                <span class="spinner-border spinner-border-sm ms-2 d-none" id="btnSpinner" role="status" aria-hidden="true"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        class ConnectDeskApp {
            constructor() {
                this.currentPlatform = 'whatsapp';
                this.currentConversationId = null;
                this.conversations = [];
                this.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                this.modal = new bootstrap.Modal(document.getElementById('newWhatsAppModal'));
                this.bulkLimit = 50;
                this.refreshInterval = null;
                this.messageRefreshInterval = null;
                this.lastMessageCount = 0;
                this.initializeApp();
            }

            initializeApp() {
                this.setupEventListeners();
                this.autoResizeTextarea();
                this.toggleMessageType();
                this.toggleRecipientMode();
                this.updateBulkRecipientStats();
                this.loadConversations();
                this.startAutoRefresh();
            }

            setupEventListeners() {
                document.querySelectorAll('.platform-tab').forEach(tab => {
                    tab.addEventListener('click', (e) => this.switchPlatform(e.currentTarget.dataset.platform));
                });

                document.getElementById('searchInput').addEventListener('input', (e) => this.searchConversations(e.target.value));
                document.getElementById('statusFilter').addEventListener('change', (e) => this.filterByStatus(e.target.value));

                document.getElementById('sendBtn').addEventListener('click', () => this.sendMessage());
                document.getElementById('messageInput').addEventListener('keypress', (e) => {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        this.sendMessage();
                    }
                });
                document.getElementById('messageTypeSelect').addEventListener('change', () => this.toggleMessageType());
                document.getElementById('templateSelector').addEventListener('change', () => this.handleTemplateSelection());

                document.querySelectorAll('.quick-reply-btn').forEach(btn => {
                    btn.addEventListener('click', (e) => this.insertQuickReply(e.target.textContent));
                });

                document.getElementById('newWhatsAppBtn').addEventListener('click', () => {
                    this.clearNewWhatsAppForm();
                    this.modal.show();
                });
                document.getElementById('newConversationBtn').addEventListener('click', () => {
                    this.clearNewWhatsAppForm();
                    this.modal.show();
                });

                // Quick FAQ: send FAQ menu to the currently open conversation
                document.getElementById('quickFaqBtn').addEventListener('click', () => this.sendFaqToConversation());
                document.getElementById('faqSendBtn').addEventListener('click', () => this.sendFaqToConversation());
                document.getElementById('newMessageType').addEventListener('change', () => this.toggleNewMessageType());
                document.getElementById('newTemplateSelector').addEventListener('change', () => this.handleNewTemplateSelection());
                document.getElementById('newWhatsAppForm').addEventListener('submit', (e) => this.sendNewWhatsApp(e));
                document.getElementById('recipientModeSingle').addEventListener('change', () => this.toggleRecipientMode());
                document.getElementById('recipientModeBulk').addEventListener('change', () => this.toggleRecipientMode());
                document.getElementById('bulkWhatsAppNumbers').addEventListener('input', () => this.updateBulkRecipientStats());
                document.getElementById('bulkClearBtn').addEventListener('click', () => this.clearBulkRecipients());
                document.getElementById('bulkUploadInput').addEventListener('change', (e) => this.handleBulkUpload(e));

                this.setComposeEnabled(false);
            }

            startAutoRefresh() {
                // Refresh conversations list every 10 seconds
                this.refreshInterval = setInterval(() => {
                    this.loadConversations(this.currentConversationId, true);
                }, 10000);

                // Refresh current conversation messages every 5 seconds
                this.messageRefreshInterval = setInterval(() => {
                    if (this.currentConversationId) {
                        this.refreshCurrentConversationMessages();
                    }
                }, 5000);
            }

            stopAutoRefresh() {
                if (this.refreshInterval) {
                    clearInterval(this.refreshInterval);
                    this.refreshInterval = null;
                }
                if (this.messageRefreshInterval) {
                    clearInterval(this.messageRefreshInterval);
                    this.messageRefreshInterval = null;
                }
            }

            async refreshCurrentConversationMessages() {
                try {
                    const response = await fetch(`/admin/api/conversations/${this.currentConversationId}/messages`, {
                        headers: { 'Accept': 'application/json' }
                    });

                    if (!response.ok) {
                        return;
                    }

                    const data = await response.json();
                    const messages = data.messages || [];

                    // Only update if message count changed
                    if (messages.length !== this.lastMessageCount) {
                        this.lastMessageCount = messages.length;
                        this.renderMessages(messages);

                        // Also refresh conversation list to update preview
                        this.loadConversations(this.currentConversationId, true);
                    }
                } catch (error) {
                    console.error('Error refreshing messages:', error);
                }
            }

            async loadConversations(selectConversationId = null, silent = false) {
                try {
                    const response = await fetch('/admin/api/conversations', {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (!response.ok) {
                        throw new Error('Failed to load conversations');
                    }

                    const data = await response.json();
                    this.conversations = data.conversations || [];
                    this.renderConversations();
                    this.updatePlatformBadges();

                    const targetId = selectConversationId || this.currentConversationId;
                    if (targetId) {
                        await this.selectConversationById(targetId, silent);
                    } else if (this.conversations.length > 0 && !silent) {
                        await this.selectConversationById(this.conversations[0].id);
                    } else if (this.conversations.length === 0 && !silent) {
                        this.showEmptyState();
                    }
                } catch (error) {
                    if (!silent) {
                        console.error(error);
                        this.showEmptyState();
                    }
                }
            }

            renderConversations() {
                const whatsappContainer = document.getElementById('whatsapp-threads');
                const facebookContainer = document.getElementById('facebook-threads');
                whatsappContainer.innerHTML = '';
                facebookContainer.innerHTML = '';

                const platformBuckets = { whatsapp: [], facebook: [] };
                this.conversations.forEach(conversation => {
                    if (platformBuckets[conversation.platform]) {
                        platformBuckets[conversation.platform].push(conversation);
                    }
                });

                this.renderConversationList(whatsappContainer, platformBuckets.whatsapp, 'whatsapp');
                this.renderConversationList(facebookContainer, platformBuckets.facebook, 'facebook');
            }

            renderConversationList(container, conversations, platform) {
                if (!conversations.length) {
                    const empty = document.createElement('div');
                    empty.className = 'thread-empty';
                    empty.textContent = platform === 'whatsapp'
                        ? 'No WhatsApp conversations yet.'
                        : 'No Facebook conversations yet.';
                    container.appendChild(empty);
                    return;
                }

                conversations.forEach(conversation => {
                    const thread = document.createElement('div');
                    const isUnread = Number(conversation.unread_count || 0) > 0;
                    thread.className = `chat-thread ${isUnread ? 'unread' : ''}`;
                    thread.dataset.conversationId = conversation.id;

                    const name = conversation.visitor_name || conversation.visitor_phone || 'Unknown';
                    const latest = conversation.latest_message;
                    const preview = latest ? latest.message : 'No messages yet';
                    const time = conversation.last_message_at || (latest ? latest.created_at : null);

                    thread.innerHTML = `
                        <div class="thread-avatar ${platform}-avatar">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <div class="thread-content">
                            <div class="thread-header">
                                <h6 class="thread-name">${this.escapeHtml(name)}</h6>
                                <span class="thread-time">${this.formatTimeAgo(time)}</span>
                            </div>
                            <p class="thread-message">${this.escapeHtml(preview)}</p>
                            <div class="thread-indicators">
                                <span class="status-badge ${isUnread ? 'unread' : 'active'}">${isUnread ? 'Unread' : 'Active'}</span>
                                <i class="bi ${platform === 'whatsapp' ? 'bi-whatsapp' : 'bi-facebook'} platform-icon"></i>
                            </div>
                        </div>
                    `;

                    thread.addEventListener('click', () => this.selectConversationById(conversation.id));
                    container.appendChild(thread);
                });
            }

            async selectConversationById(conversationId, silent = false) {
                const conversation = this.conversations.find(item => Number(item.id) === Number(conversationId));
                if (!conversation) {
                    return;
                }

                this.currentConversationId = conversation.id;
                this.setActiveThread(conversation.id);

                document.querySelector('.user-name').textContent = conversation.visitor_name || conversation.visitor_phone || 'Unknown';
                document.getElementById('userPhone').textContent = conversation.visitor_phone || 'No phone';
                this.updatePlatformIndicator(conversation.platform);

                document.getElementById('emptyChatState').style.display = 'none';
                document.querySelector('.chat-interface').style.display = 'flex';
                this.setComposeEnabled(true);

                await this.loadConversationMessages(conversation.id);
                if (!silent) {
                    await this.markConversationRead(conversation.id);
                }
            }

            setActiveThread(conversationId) {
                document.querySelectorAll('.chat-thread').forEach(thread => {
                    thread.classList.toggle('active', Number(thread.dataset.conversationId) === Number(conversationId));
                });
            }

            updatePlatformIndicator(platform) {
                const indicator = document.querySelector('.platform-indicator');
                indicator.className = `platform-indicator ${platform}`;

                const icon = indicator.querySelector('i');
                icon.className = platform === 'whatsapp' ? 'bi bi-whatsapp' : 'bi bi-facebook';

                indicator.querySelector('.platform-label').textContent = platform === 'whatsapp' ? 'WhatsApp' : 'Facebook';
            }

            switchPlatform(platform) {
                document.querySelectorAll('.platform-tab').forEach(tab => {
                    tab.classList.toggle('active', tab.dataset.platform === platform);
                });

                document.querySelectorAll('.thread-container').forEach(container => {
                    container.classList.toggle('active', container.id === `${platform}-threads`);
                });

                this.currentPlatform = platform;
                this.updatePlatformIndicator(platform);
            }

            searchConversations(query) {
                const threads = document.querySelectorAll('.chat-thread');
                threads.forEach(thread => {
                    const name = thread.querySelector('.thread-name').textContent.toLowerCase();
                    const message = thread.querySelector('.thread-message').textContent.toLowerCase();
                    const matches = name.includes(query.toLowerCase()) || message.includes(query.toLowerCase());
                    thread.style.display = matches ? 'flex' : 'none';
                });
            }

            filterByStatus(status) {
                const threads = document.querySelectorAll('.chat-thread');
                threads.forEach(thread => {
                    const threadStatus = thread.querySelector('.status-badge').textContent.toLowerCase();
                    const matches = status === 'all' || threadStatus === status;
                    thread.style.display = matches ? 'flex' : 'none';
                });
            }

            async loadConversationMessages(conversationId) {
                try {
                    const response = await fetch(`/admin/api/conversations/${conversationId}/messages`, {
                        headers: { 'Accept': 'application/json' }
                    });

                    if (!response.ok) {
                        throw new Error('Failed to load messages');
                    }

                    const data = await response.json();
                    const messages = data.messages || [];
                    this.lastMessageCount = messages.length;
                    this.renderMessages(messages);
                } catch (error) {
                    console.error(error);
                }
            }

            renderMessages(messages) {
                const container = document.getElementById('messagesContainer');
                container.innerHTML = '';

                if (!messages.length) {
                    const empty = document.createElement('div');
                    empty.className = 'messages-empty';
                    empty.textContent = 'No messages yet.';
                    container.appendChild(empty);
                    return;
                }

                messages.forEach(message => {
                    const messageGroup = document.createElement('div');
                    const isAdmin = message.sender_type === 'admin';
                    const messageText = message.message || '';
                    messageGroup.className = 'message-group';
                    messageGroup.innerHTML = `
                        <div class="message ${isAdmin ? 'sent' : 'received'}">
                            <div class="message-content">
                                <p>${this.formatMessageText(messageText)}</p>
                                <span class="message-time">${this.formatMessageTime(message.created_at)}</span>
                            </div>
                        </div>
                    `;
                    container.appendChild(messageGroup);
                });

                this.scrollToBottom();
            }

            async sendMessage() {
                if (!this.currentConversationId) {
                    return;
                }

                const messageType = document.getElementById('messageTypeSelect').value;
                const input = document.getElementById('messageInput');

                // --- Template or Text ---
                const message = input.value.trim();
                const payload = { message_type: messageType };

                if (messageType === 'template') {
                    const templateName = this.getSelectedTemplate();
                    if (!templateName) {
                        alert('Please select or enter a template name.');
                        return;
                    }
                    payload.template_name = templateName;
                } else {
                    if (!message) {
                        return;
                    }
                    payload.message = message;
                }

                try {
                    const response = await fetch(`/admin/api/conversations/${this.currentConversationId}/messages`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await response.json();
                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Failed to send message.');
                    }

                    if (messageType === 'text') {
                        input.value = '';
                        input.style.height = 'auto';
                    } else {
                        document.getElementById('templateSelector').value = 'hello_world';
                        this.handleTemplateSelection();
                    }

                    await this.loadConversations(this.currentConversationId);
                } catch (error) {
                    alert(error.message);
                }
            }

            async markConversationRead(conversationId) {
                try {
                    await fetch(`/admin/api/conversations/${conversationId}/mark-read`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken
                        }
                    });
                } catch (error) {
                    console.error(error);
                }

                const conversation = this.conversations.find(item => Number(item.id) === Number(conversationId));
                if (conversation) {
                    conversation.unread_count = 0;
                    this.renderConversations();
                    this.updatePlatformBadges();
                    this.setActiveThread(conversationId);
                }
            }

            getRecipientMode() {
                return document.getElementById('recipientModeBulk').checked ? 'bulk' : 'single';
            }

            getSubmitButtonLabel() {
                return this.getRecipientMode() === 'bulk' ? 'Send to Multiple' : 'Start Conversation';
            }

            toggleRecipientMode() {
                const isBulk = this.getRecipientMode() === 'bulk';
                document.getElementById('singleRecipientSection').classList.toggle('d-none', isBulk);
                document.getElementById('bulkRecipientSection').classList.toggle('d-none', !isBulk);
                document.getElementById('btnText').textContent = this.getSubmitButtonLabel();
                this.updateBulkRecipientStats();
            }

            parseRecipientNumbers(value) {
                if (!value) {
                    return [];
                }

                const tokens = value.split(/[\s,;]+/);
                const uniqueNumbers = [];
                const seen = new Set();

                tokens.forEach(token => {
                    const trimmed = token.trim();
                    if (!trimmed) {
                        return;
                    }
                    const hasPlus = trimmed.startsWith('+');
                    const digits = trimmed.replace(/[^\d]/g, '');
                    if (!digits) {
                        return;
                    }
                    const normalized = hasPlus ? `+${digits}` : digits;
                    if (!seen.has(normalized)) {
                        seen.add(normalized);
                        uniqueNumbers.push(normalized);
                    }
                });

                return uniqueNumbers;
            }

            getBulkRecipientNumbers() {
                const input = document.getElementById('bulkWhatsAppNumbers');
                return this.parseRecipientNumbers(input.value);
            }

            updateBulkRecipientStats() {
                const numbers = this.getBulkRecipientNumbers();
                const badge = document.getElementById('bulkCountBadge');
                const previewList = document.getElementById('bulkPreviewList');

                badge.textContent = `${numbers.length} ${numbers.length === 1 ? 'number' : 'numbers'}`;
                previewList.innerHTML = '';

                if (!numbers.length) {
                    previewList.innerHTML = '<span class="text-muted small">Add numbers to see a preview.</span>';
                    return;
                }

                numbers.slice(0, 6).forEach(number => {
                    const chip = document.createElement('span');
                    chip.className = 'bulk-chip';
                    chip.textContent = number;
                    previewList.appendChild(chip);
                });

                if (numbers.length > 6) {
                    const more = document.createElement('span');
                    more.className = 'bulk-chip bulk-chip-muted';
                    more.textContent = `+${numbers.length - 6} more`;
                    previewList.appendChild(more);
                }
            }

            clearBulkRecipients() {
                document.getElementById('bulkWhatsAppNumbers').value = '';
                this.updateBulkRecipientStats();
            }

            handleBulkUpload(event) {
                const file = event.target.files[0];
                if (!file) {
                    return;
                }

                const reader = new FileReader();
                reader.onload = () => {
                    const input = document.getElementById('bulkWhatsAppNumbers');
                    const existing = input.value.trim();
                    const incoming = String(reader.result || '').trim();
                    input.value = existing ? `${existing}\n${incoming}` : incoming;
                    this.updateBulkRecipientStats();
                };
                reader.readAsText(file);
                event.target.value = '';
            }

            async validateWhatsAppNumber(phoneNumber) {
                const validateResponse = await fetch('/admin/api/whatsapp/validate', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken
                    },
                    body: JSON.stringify({ phone_number: phoneNumber })
                });

                const validateData = await validateResponse.json();

                if (!validateResponse.ok || !validateData.valid) {
                    throw new Error(validateData.message || 'Invalid WhatsApp number.');
                }

                return validateData;
            }

            async startWhatsAppConversation(payload) {
                const startResponse = await fetch('/admin/api/conversations/start', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken
                    },
                    body: JSON.stringify(payload)
                });

                const startData = await startResponse.json();

                if (!startResponse.ok || !startData.success) {
                    throw new Error(startData.message || 'Failed to start conversation.');
                }

                return startData;
            }

            async sendNewWhatsApp(event) {
                event.preventDefault();

                const errorBox = document.getElementById('newWhatsAppError');
                const successBox = document.getElementById('newWhatsAppSuccess');
                const btnText = document.getElementById('btnText');
                const btnSpinner = document.getElementById('btnSpinner');
                const submitBtn = document.getElementById('startConversationBtn');

                // Reset alerts
                errorBox.classList.add('d-none');
                successBox.classList.add('d-none');
                errorBox.textContent = '';
                successBox.textContent = '';

                const recipientMode = this.getRecipientMode();
                const phoneNumber = document.getElementById('newWhatsAppNumber').value.trim();
                const bulkNumbers = this.getBulkRecipientNumbers();
                const messageType = document.getElementById('newMessageType').value;
                const templateName = this.getNewSelectedTemplate();
                const textMessage = document.getElementById('newWhatsAppInitialMessage').value.trim();

                if (recipientMode === 'single' && !phoneNumber) {
                    return this.showNewWhatsAppError('Phone number is required.');
                }

                if (recipientMode === 'bulk') {
                    if (!bulkNumbers.length) {
                        return this.showNewWhatsAppError('At least one phone number is required.');
                    }
                    if (bulkNumbers.length > this.bulkLimit) {
                        return this.showNewWhatsAppError(`You can send to a maximum of ${this.bulkLimit} numbers at once.`);
                    }
                }

                if (messageType === 'template' && !templateName) {
                    return this.showNewWhatsAppError('Template name is required.');
                }

                if (messageType === 'text' && !textMessage) {
                    return this.showNewWhatsAppError('Message is required for text type.');
                }
                // FAQ type needs no additional input — the menu is predefined

                // Disable button and show spinner
                submitBtn.disabled = true;
                btnSpinner.classList.remove('d-none');
                btnText.textContent = recipientMode === 'bulk' ? 'Preparing...' : 'Validating...';

                try {
                    if (recipientMode === 'single') {
                        await this.validateWhatsAppNumber(phoneNumber);

                        successBox.textContent = 'Number validated! Starting conversation...';
                        successBox.classList.remove('d-none');
                        btnText.textContent = 'Starting...';

                        const payload = {
                            phone_number: phoneNumber,
                            message_type: messageType
                        };

                        if (messageType === 'template') {
                            payload.template_name = templateName;
                        } else if (messageType === 'text') {
                            payload.initial_message = textMessage;
                        }

                        const startData = await this.startWhatsAppConversation(payload);

                        const msgType = messageType === 'template' ? 'Template' : 'Message';
                        successBox.textContent = `${msgType} sent successfully! ${startData.conversation_existed ? 'Using existing conversation.' : 'Conversation started.'}`;
                        btnText.textContent = 'Success!';

                        setTimeout(() => {
                            this.modal.hide();
                            this.clearNewWhatsAppForm();
                            this.loadConversations(startData.conversation.id);
                        }, 1500);
                        return;
                    }

                    successBox.textContent = `Sending to ${bulkNumbers.length} recipients...`;
                    successBox.classList.remove('d-none');

                    let sentCount = 0;
                    let failedCount = 0;
                    const failureSamples = [];
                    let lastConversationId = null;

                    for (let index = 0; index < bulkNumbers.length; index++) {
                        const number = bulkNumbers[index];
                        btnText.textContent = `Sending ${index + 1}/${bulkNumbers.length}`;

                        try {
                            await this.validateWhatsAppNumber(number);
                            const payload = {
                                phone_number: number,
                                message_type: messageType
                            };

                            if (messageType === 'template') {
                                payload.template_name = templateName;
                            } else if (messageType === 'text') {
                                payload.initial_message = textMessage;
                            }

                            const startData = await this.startWhatsAppConversation(payload);
                            sentCount += 1;
                            if (startData.conversation && startData.conversation.id) {
                                lastConversationId = startData.conversation.id;
                            }
                        } catch (error) {
                            failedCount += 1;
                            if (failureSamples.length < 3) {
                                failureSamples.push(`${number}: ${error.message}`);
                            }
                        }
                    }

                    if (sentCount > 0) {
                        successBox.textContent = `Sent to ${sentCount} ${sentCount === 1 ? 'number' : 'numbers'}.`;
                        successBox.classList.remove('d-none');
                    } else {
                        successBox.classList.add('d-none');
                    }

                    if (failedCount > 0) {
                        let errorText = `Failed to send to ${failedCount} ${failedCount === 1 ? 'number' : 'numbers'}.`;
                        if (failureSamples.length) {
                            errorText += ` Examples: ${failureSamples.join(' | ')}`;
                        }
                        this.showNewWhatsAppError(errorText);
                    }

                    btnText.textContent = this.getSubmitButtonLabel();

                    if (sentCount > 0) {
                        if (failedCount === 0) {
                            setTimeout(() => {
                                this.modal.hide();
                                this.clearNewWhatsAppForm();
                                this.loadConversations(lastConversationId);
                            }, 1500);
                        } else {
                            this.loadConversations(lastConversationId);
                        }
                    }
                } catch (error) {
                    this.showNewWhatsAppError(error.message);
                    btnText.textContent = this.getSubmitButtonLabel();
                } finally {
                    // Re-enable button and hide spinner
                    submitBtn.disabled = false;
                    btnSpinner.classList.add('d-none');
                }
            }

            showNewWhatsAppError(message) {
                const errorBox = document.getElementById('newWhatsAppError');
                errorBox.textContent = message;
                errorBox.classList.remove('d-none');
            }

            clearNewWhatsAppForm() {
                document.getElementById('newWhatsAppForm').reset();
                document.getElementById('newWhatsAppError').classList.add('d-none');
                document.getElementById('newWhatsAppSuccess').classList.add('d-none');
                document.getElementById('recipientModeSingle').checked = true;
                document.getElementById('btnText').textContent = this.getSubmitButtonLabel();
                document.getElementById('btnSpinner').classList.add('d-none');
                document.getElementById('startConversationBtn').disabled = false;
                document.getElementById('newTemplateSelector').value = 'hello_world';
                document.getElementById('newTemplateName').value = '';
                this.toggleNewMessageType();
                this.clearBulkRecipients();
                document.getElementById('bulkUploadInput').value = '';
                this.toggleRecipientMode();
            }

            toggleNewMessageType() {
                const messageType     = document.getElementById('newMessageType').value;
                const templateSection = document.getElementById('templateSection');
                const textSection     = document.getElementById('textMessageSection');

                templateSection.classList.toggle('d-none', messageType !== 'template');
                textSection.classList.toggle('d-none',     messageType !== 'text');

                if (messageType === 'template') {
                    this.handleNewTemplateSelection();
                }
            }

            handleNewTemplateSelection() {
                const selector = document.getElementById('newTemplateSelector');
                const customInput = document.getElementById('newTemplateName');
                const helpText = document.getElementById('templateHelpText');
                const selectedValue = selector.value;

                if (selectedValue === 'custom') {
                    customInput.style.display = 'block';
                    customInput.value = '';
                    customInput.required = true;
                    customInput.focus();
                    helpText.innerHTML = '<i class="bi bi-info-circle"></i> Enter your Twilio Content Template SID (starts with HX...)';
                } else if (selectedValue === 'random') {
                    customInput.style.display = 'none';
                    customInput.required = false;
                    helpText.innerHTML = '<i class="bi bi-dice-5"></i> A random template will be selected from the available options';
                } else {
                    customInput.style.display = 'none';
                    customInput.required = false;
                    helpText.innerHTML = '<i class="bi bi-check-circle"></i> Using template: <strong>' + selectedValue + '</strong>';
                }
            }

            getNewSelectedTemplate() {
                const selector = document.getElementById('newTemplateSelector');
                const customInput = document.getElementById('newTemplateName');
                const selectedValue = selector.value;

                if (selectedValue === 'random') {
                    const templates = [
                        'hello_world',
                        'thank_you',
                        'welcome_message',
                        'sample_purchase_feedback',
                        'sample_happy_hour_announcement',
                        'sample_flight_confirmation',
                        'sample_movie_ticket_confirmation',
                        'sample_issue_resolution',
                        'sample_shipping_confirmation',
                        'appointment_reminder'
                    ];
                    return templates[Math.floor(Math.random() * templates.length)];
                }

                return selectedValue === 'custom' ? customInput.value.trim() : selectedValue;
            }

            toggleMessageType() {
                const messageType       = document.getElementById('messageTypeSelect').value;
                const templateContainer = document.getElementById('templateOptionsContainer');

                templateContainer.style.display = messageType === 'template' ? 'flex' : 'none';

                if (messageType === 'template') {
                    document.getElementById('templateSelector').value = 'hello_world';
                    this.handleTemplateSelection();
                }
            }

            async sendFaqToConversation() {
                if (!this.currentConversationId) {
                    alert('Please select a conversation first.');
                    return;
                }

                const faqBtn      = document.getElementById('faqSendBtn');
                const quickFaqBtn = document.getElementById('quickFaqBtn');
                const banner      = document.getElementById('faqInfoBanner');

                [faqBtn, quickFaqBtn].forEach(b => { if (b) b.disabled = true; });

                try {
                    const response = await fetch(`/admin/api/conversations/${this.currentConversationId}/send-faq`, {
                        method : 'POST',
                        headers: {
                            'Accept'       : 'application/json',
                            'Content-Type' : 'application/json',
                            'X-CSRF-TOKEN' : this.csrfToken,
                        },
                        body: JSON.stringify({}),
                    });
                    const data = await response.json();
                    if (!response.ok || !data.success) throw new Error(data.message || 'Failed to send FAQ.');

                    // Brief feedback: show the preview banner for 3 seconds
                    if (banner) {
                        banner.style.display = 'block';
                        setTimeout(() => { banner.style.display = 'none'; }, 3000);
                    }

                    await this.loadConversations(this.currentConversationId);
                } catch (error) {
                    alert(error.message);
                } finally {
                    [faqBtn, quickFaqBtn].forEach(b => { if (b) b.disabled = false; });
                }
            }

            handleTemplateSelection() {
                const selector = document.getElementById('templateSelector');
                const customInput = document.getElementById('templateNameInput');
                const selectedValue = selector.value;

                if (selectedValue === 'custom') {
                    customInput.style.display = 'block';
                    customInput.value = '';
                    customInput.focus();
                } else {
                    customInput.style.display = 'none';
                    customInput.value = selectedValue;
                }
            }

            getSelectedTemplate() {
                const selector = document.getElementById('templateSelector');
                const customInput = document.getElementById('templateNameInput');
                return selector.value === 'custom' ? customInput.value.trim() : selector.value;
            }

            insertQuickReply(text) {
                const input = document.getElementById('messageInput');
                input.value = text;
                input.focus();
            }

            autoResizeTextarea() {
                const textarea = document.getElementById('messageInput');
                textarea.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
                });
            }

            setComposeEnabled(enabled) {
                document.getElementById('messageInput').disabled       = !enabled;
                document.getElementById('sendBtn').disabled            = !enabled;
                document.getElementById('messageTypeSelect').disabled  = !enabled;
                document.getElementById('templateNameInput').disabled  = !enabled;
                const faqSendBtn  = document.getElementById('faqSendBtn');
                const quickFaqBtn = document.getElementById('quickFaqBtn');
                if (faqSendBtn)  faqSendBtn.disabled  = !enabled;
                if (quickFaqBtn) quickFaqBtn.disabled = !enabled;
                if (enabled) this.toggleMessageType();
                // Hide FAQ banner whenever compose state resets
                const banner = document.getElementById('faqInfoBanner');
                if (banner && !enabled) banner.style.display = 'none';
            }

            showEmptyState() {
                document.querySelector('.chat-interface').style.display = 'none';
                document.getElementById('emptyChatState').style.display = 'flex';
                this.setComposeEnabled(false);
            }

            scrollToBottom() {
                const container = document.getElementById('messagesContainer');
                container.scrollTop = container.scrollHeight;
            }

            updatePlatformBadges() {
                const whatsappCount = this.conversations
                    .filter(conv => conv.platform === 'whatsapp')
                    .reduce((sum, conv) => sum + Number(conv.unread_count || 0), 0);
                const facebookCount = this.conversations
                    .filter(conv => conv.platform === 'facebook')
                    .reduce((sum, conv) => sum + Number(conv.unread_count || 0), 0);

                const whatsappBadge = document.querySelector('.platform-tab[data-platform=\"whatsapp\"] .badge');
                const facebookBadge = document.querySelector('.platform-tab[data-platform=\"facebook\"] .badge');
                if (whatsappBadge) {
                    whatsappBadge.textContent = whatsappCount;
                }
                if (facebookBadge) {
                    facebookBadge.textContent = facebookCount;
                }
            }

            formatTimeAgo(timestamp) {
                if (!timestamp) {
                    return '';
                }

                const date = new Date(this.normalizeTimestamp(timestamp));
                const seconds = Math.floor((Date.now() - date.getTime()) / 1000);
                if (Number.isNaN(seconds) || seconds < 0) {
                    return '';
                }

                if (seconds < 60) {
                    return `${seconds}s ago`;
                }
                const minutes = Math.floor(seconds / 60);
                if (minutes < 60) {
                    return `${minutes}m ago`;
                }
                const hours = Math.floor(minutes / 60);
                if (hours < 24) {
                    return `${hours}h ago`;
                }
                const days = Math.floor(hours / 24);
                return `${days}d ago`;
            }

            formatMessageTime(timestamp) {
                if (!timestamp) {
                    return '';
                }
                const date = new Date(this.normalizeTimestamp(timestamp));
                return date.toLocaleTimeString('en-US', {
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                });
            }

            normalizeTimestamp(timestamp) {
                const value = String(timestamp);
                if (value.includes('T')) {
                    return value;
                }
                return value.replace(' ', 'T');
            }

            /**
             * Format message text for display in the chatbox.
             * - Escapes HTML (XSS safe)
             * - Converts \n to <br> (line breaks)
             * - Renders *bold* and _italic_ WhatsApp formatting
             * - Renders ━ separator lines
             */
            formatMessageText(text) {
                return this.escapeHtml(text)
                    .replace(/\n/g, '<br>')
                    .replace(/\*([^*<]+)\*/g, '<strong>$1</strong>')
                    .replace(/_([^_<]+)_/g, '<em>$1</em>');
            }

            escapeHtml(value) {
                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/\"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            window.connectDeskApp = new ConnectDeskApp();
        });
    </script>
</body>
</html>
