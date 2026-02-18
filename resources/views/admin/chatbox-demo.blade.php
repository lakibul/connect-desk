<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ConnectDesk - WhatsApp Chat Demo</title>

    <!-- External Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="{{ asset('css/admin-dashboard.css') }}" rel="stylesheet">
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
                        <span class="me-2 d-none d-md-inline fw-medium">Admin User</span>
                        <div class="avatar-circle">
                            <i class="bi bi-person-fill"></i>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="#">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Dashboard Container -->
    <div class="dashboard-container">
        <div class="row g-0 h-100">
            <!-- Sidebar -->
            <div class="col-md-3 sidebar-container">
                <div class="sidebar">
                    <!-- Platform Tabs -->
                    <div class="platform-tabs">
                        <button class="platform-tab active" data-platform="whatsapp">
                            <i class="bi bi-whatsapp"></i>
                            <span>WhatsApp</span>
                            <span class="badge">3</span>
                        </button>
                    </div>

                    <!-- New Conversation Button -->
                    <div class="p-3">
                        <button class="btn btn-success w-100 new-conversation-btn" id="newConversationBtn">
                            <i class="bi bi-plus-circle-fill me-2"></i>
                            Start New Conversation
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
                            <!-- Thread 1 -->
                            <div class="chat-thread active unread" data-conversation-id="1">
                                <div class="thread-avatar whatsapp-avatar">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <div class="thread-content">
                                    <div class="thread-header">
                                        <h6 class="thread-name">John Smith</h6>
                                        <span class="thread-time">2m ago</span>
                                    </div>
                                    <p class="thread-message">Yes, I would like to know more about your products.</p>
                                    <div class="thread-indicators">
                                        <span class="status-badge unread">Unread</span>
                                        <i class="bi bi-whatsapp platform-icon"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Thread 2 -->
                            <div class="chat-thread" data-conversation-id="2">
                                <div class="thread-avatar whatsapp-avatar">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <div class="thread-content">
                                    <div class="thread-header">
                                        <h6 class="thread-name">Sarah Johnson</h6>
                                        <span class="thread-time">15m ago</span>
                                    </div>
                                    <p class="thread-message">Thank you for your quick response!</p>
                                    <div class="thread-indicators">
                                        <span class="status-badge active">Active</span>
                                        <i class="bi bi-whatsapp platform-icon"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Thread 3 -->
                            <div class="chat-thread unread" data-conversation-id="3">
                                <div class="thread-avatar whatsapp-avatar">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <div class="thread-content">
                                    <div class="thread-header">
                                        <h6 class="thread-name">Mike Davis</h6>
                                        <span class="thread-time">1h ago</span>
                                    </div>
                                    <p class="thread-message">Is this still available?</p>
                                    <div class="thread-indicators">
                                        <span class="status-badge unread">Unread</span>
                                        <i class="bi bi-whatsapp platform-icon"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Thread 4 -->
                            <div class="chat-thread" data-conversation-id="4">
                                <div class="thread-avatar whatsapp-avatar">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <div class="thread-content">
                                    <div class="thread-header">
                                        <h6 class="thread-name">Emily Brown</h6>
                                        <span class="thread-time">3h ago</span>
                                    </div>
                                    <p class="thread-message">Perfect, see you tomorrow!</p>
                                    <div class="thread-indicators">
                                        <span class="status-badge active">Active</span>
                                        <i class="bi bi-whatsapp platform-icon"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Thread 5 -->
                            <div class="chat-thread" data-conversation-id="5">
                                <div class="thread-avatar whatsapp-avatar">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <div class="thread-content">
                                    <div class="thread-header">
                                        <h6 class="thread-name">David Wilson</h6>
                                        <span class="thread-time">5h ago</span>
                                    </div>
                                    <p class="thread-message">Thanks for the information!</p>
                                    <div class="thread-indicators">
                                        <span class="status-badge active">Active</span>
                                        <i class="bi bi-whatsapp platform-icon"></i>
                                    </div>
                                </div>
                            </div>
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
                                <h5 class="user-name">John Smith</h5>
                                <div class="user-status">
                                    <span class="platform-indicator whatsapp">
                                        <i class="bi bi-whatsapp"></i>
                                        <span class="platform-label">WhatsApp</span>
                                    </span>
                                    <span class="status-dot online"></span>
                                    <span class="status-text">+1 234 567 8900</span>
                                </div>
                            </div>
                        </div>
                        <div class="chat-actions">
                            <button class="btn btn-success btn-sm" title="Start new WhatsApp conversation">
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
                        <!-- Message Group 1 -->
                        <div class="message-group">
                            <div class="message received">
                                <div class="message-content">
                                    <p>Hi! I'm interested in your services. Can you tell me more?</p>
                                    <span class="message-time">10:30 AM</span>
                                </div>
                            </div>
                        </div>

                        <!-- Message Group 2 -->
                        <div class="message-group">
                            <div class="message sent">
                                <div class="message-content">
                                    <p>Hello! Thank you for reaching out. I'd be happy to help you with that.</p>
                                    <span class="message-time">10:32 AM</span>
                                </div>
                            </div>
                        </div>

                        <!-- Message Group 3 -->
                        <div class="message-group">
                            <div class="message sent">
                                <div class="message-content">
                                    <p>We offer a comprehensive range of services including WhatsApp Business integration, automated messaging, and customer support solutions.</p>
                                    <span class="message-time">10:33 AM</span>
                                </div>
                            </div>
                        </div>

                        <!-- Message Group 4 -->
                        <div class="message-group">
                            <div class="message received">
                                <div class="message-content">
                                    <p>That sounds great! What are your pricing plans?</p>
                                    <span class="message-time">10:35 AM</span>
                                </div>
                            </div>
                        </div>

                        <!-- Message Group 5 -->
                        <div class="message-group">
                            <div class="message sent">
                                <div class="message-content">
                                    <p>We have three main plans:</p>
                                    <span class="message-time">10:36 AM</span>
                                </div>
                            </div>
                        </div>

                        <!-- Message Group 6 -->
                        <div class="message-group">
                            <div class="message sent">
                                <div class="message-content">
                                    <p>1. Starter Plan - $29/month<br>2. Business Plan - $79/month<br>3. Enterprise Plan - Custom pricing</p>
                                    <span class="message-time">10:36 AM</span>
                                </div>
                            </div>
                        </div>

                        <!-- Message Group 7 -->
                        <div class="message-group">
                            <div class="message received">
                                <div class="message-content">
                                    <p>Perfect! Can you send me more details about the Business Plan?</p>
                                    <span class="message-time">10:38 AM</span>
                                </div>
                            </div>
                        </div>

                        <!-- Message Group 8 -->
                        <div class="message-group">
                            <div class="message sent">
                                <div class="message-content">
                                    <p>Absolutely! I'll send you a detailed brochure right away.</p>
                                    <span class="message-time">10:39 AM</span>
                                </div>
                            </div>
                        </div>

                        <!-- Message Group 9 -->
                        <div class="message-group">
                            <div class="message received">
                                <div class="message-content">
                                    <p>Yes, I would like to know more about your products.</p>
                                    <span class="message-time">10:42 AM</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Message Input -->
                    <div class="message-input-container">
                        <div class="message-options">
                            <select class="form-select form-select-sm" id="messageTypeSelect">
                                <option value="text" selected>Text message</option>
                                <option value="template">Template message</option>
                            </select>
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
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Demo functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-resize textarea
            const textarea = document.getElementById('messageInput');
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 120) + 'px';
            });

            // Thread switching
            const threads = document.querySelectorAll('.chat-thread');
            threads.forEach(thread => {
                thread.addEventListener('click', function() {
                    // Remove active class from all threads
                    threads.forEach(t => t.classList.remove('active'));
                    // Add active class to clicked thread
                    this.classList.add('active');
                    // Remove unread status
                    this.classList.remove('unread');

                    // Update header
                    const name = this.querySelector('.thread-name').textContent;
                    document.querySelector('.user-name').textContent = name;

                    // Update badge count
                    const unreadCount = document.querySelectorAll('.chat-thread.unread').length;
                    document.querySelector('.platform-tab .badge').textContent = unreadCount;
                });
            });

            // Send message
            function sendMessage() {
                const input = document.getElementById('messageInput');
                const message = input.value.trim();

                if (message) {
                    const messagesContainer = document.getElementById('messagesContainer');
                    const messageGroup = document.createElement('div');
                    messageGroup.className = 'message-group';

                    const now = new Date();
                    const time = now.toLocaleTimeString('en-US', {
                        hour: 'numeric',
                        minute: '2-digit',
                        hour12: true
                    });

                    messageGroup.innerHTML = `
                        <div class="message sent">
                            <div class="message-content">
                                <p>${escapeHtml(message)}</p>
                                <span class="message-time">${time}</span>
                            </div>
                        </div>
                    `;

                    messagesContainer.appendChild(messageGroup);
                    input.value = '';
                    input.style.height = 'auto';

                    // Scroll to bottom
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                }
            }

            // Send button click
            document.getElementById('sendBtn').addEventListener('click', sendMessage);

            // Enter key to send
            document.getElementById('messageInput').addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });

            // Quick reply buttons
            document.querySelectorAll('.quick-reply-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.getElementById('messageInput').value = this.textContent;
                    document.getElementById('messageInput').focus();
                });
            });

            // Search functionality
            document.getElementById('searchInput').addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                document.querySelectorAll('.chat-thread').forEach(thread => {
                    const name = thread.querySelector('.thread-name').textContent.toLowerCase();
                    const message = thread.querySelector('.thread-message').textContent.toLowerCase();
                    const matches = name.includes(searchTerm) || message.includes(searchTerm);
                    thread.style.display = matches ? 'flex' : 'none';
                });
            });

            // Filter functionality
            document.getElementById('statusFilter').addEventListener('change', function(e) {
                const filter = e.target.value;
                document.querySelectorAll('.chat-thread').forEach(thread => {
                    if (filter === 'all') {
                        thread.style.display = 'flex';
                    } else {
                        const badge = thread.querySelector('.status-badge');
                        const status = badge.textContent.toLowerCase();
                        thread.style.display = status === filter ? 'flex' : 'none';
                    }
                });
            });

            // Escape HTML
            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            // Auto scroll to bottom on load
            const messagesContainer = document.getElementById('messagesContainer');
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        });
    </script>
</body>
</html>
