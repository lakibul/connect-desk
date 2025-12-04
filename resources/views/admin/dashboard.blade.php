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
</head>
<body>
    <!-- Navigation Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <i class="bi bi-chat-dots-fill me-2"></i>
                <span class="fw-bold">ConnectDesk</span>
            </a>

            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                        <div class="avatar-circle me-2">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <span>{{ auth()->user()->name ?? 'Admin' }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Settings</a></li>
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
        <div class="row g-0 h-100">
            <!-- Sidebar -->
            <div class="col-md-3 sidebar-container">
                <div class="sidebar">
                    <!-- Platform Tabs -->
                    <div class="platform-tabs">
                        <button class="platform-tab active" data-platform="whatsapp">
                            <i class="bi bi-whatsapp"></i>
                            <span>WhatsApp</span>
                            <span class="badge">12</span>
                        </button>
                        <button class="platform-tab" data-platform="facebook">
                            <i class="bi bi-facebook"></i>
                            <span>Facebook</span>
                            <span class="badge">8</span>
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
                            <div class="chat-thread unread" data-conversation-id="1">
                                <div class="thread-avatar whatsapp-avatar">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <div class="thread-content">
                                    <div class="thread-header">
                                        <h6 class="thread-name">John Smith</h6>
                                        <span class="thread-time">2m ago</span>
                                    </div>
                                    <p class="thread-message">Hey, I need help with my order...</p>
                                    <div class="thread-indicators">
                                        <span class="status-badge unread">Unread</span>
                                        <i class="bi bi-whatsapp platform-icon"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="chat-thread" data-conversation-id="2">
                                <div class="thread-avatar whatsapp-avatar">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <div class="thread-content">
                                    <div class="thread-header">
                                        <h6 class="thread-name">Sarah Johnson</h6>
                                        <span class="thread-time">15m ago</span>
                                    </div>
                                    <p class="thread-message">Thank you for the quick response!</p>
                                    <div class="thread-indicators">
                                        <span class="status-badge active">Active</span>
                                        <i class="bi bi-whatsapp platform-icon"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="chat-thread" data-conversation-id="3">
                                <div class="thread-avatar whatsapp-avatar">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <div class="thread-content">
                                    <div class="thread-header">
                                        <h6 class="thread-name">Mike Wilson</h6>
                                        <span class="thread-time">1h ago</span>
                                    </div>
                                    <p class="thread-message">Can you send me the tracking info?</p>
                                    <div class="thread-indicators">
                                        <span class="status-badge resolved">Resolved</span>
                                        <i class="bi bi-whatsapp platform-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Facebook Threads -->
                        <div class="thread-container" id="facebook-threads">
                            <div class="chat-thread unread" data-conversation-id="4">
                                <div class="thread-avatar facebook-avatar">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <div class="thread-content">
                                    <div class="thread-header">
                                        <h6 class="thread-name">Emma Davis</h6>
                                        <span class="thread-time">5m ago</span>
                                    </div>
                                    <p class="thread-message">Is this product still available?</p>
                                    <div class="thread-indicators">
                                        <span class="status-badge unread">Unread</span>
                                        <i class="bi bi-facebook platform-icon"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="chat-thread" data-conversation-id="5">
                                <div class="thread-avatar facebook-avatar">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <div class="thread-content">
                                    <div class="thread-header">
                                        <h6 class="thread-name">Robert Brown</h6>
                                        <span class="thread-time">30m ago</span>
                                    </div>
                                    <p class="thread-message">Great service, thanks!</p>
                                    <div class="thread-indicators">
                                        <span class="status-badge active">Active</span>
                                        <i class="bi bi-facebook platform-icon"></i>
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
                                        WhatsApp
                                    </span>
                                    <span class="status-dot online"></span>
                                    <span class="status-text">Online</span>
                                </div>
                            </div>
                        </div>
                        <div class="chat-actions">
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
                        <div class="message-group">
                            <div class="message received">
                                <div class="message-content">
                                    <p>Hey, I need help with my order. It hasn't arrived yet and it's been 5 days.</p>
                                    <span class="message-time">2:30 PM</span>
                                </div>
                            </div>
                        </div>

                        <div class="message-group">
                            <div class="message sent">
                                <div class="message-content">
                                    <p>Hi! I'd be happy to help you with that. Can you please provide your order number?</p>
                                    <span class="message-time">2:32 PM</span>
                                </div>
                            </div>
                        </div>

                        <div class="message-group">
                            <div class="message received">
                                <div class="message-content">
                                    <p>Sure, it's #ORD-123456</p>
                                    <span class="message-time">2:33 PM</span>
                                </div>
                            </div>
                        </div>

                        <div class="message-group">
                            <div class="message sent">
                                <div class="message-content">
                                    <p>Thank you! Let me check the status for you. I can see your order is currently in transit and should arrive tomorrow. You'll receive a tracking notification shortly.</p>
                                    <span class="message-time">2:35 PM</span>
                                </div>
                            </div>
                        </div>

                        <div class="typing-indicator">
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

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        class ConnectDeskApp {
            constructor() {
                this.currentPlatform = 'whatsapp';
                this.currentConversation = null;
                this.initializeApp();
            }

            initializeApp() {
                this.setupEventListeners();
                this.autoResizeTextarea();
                this.loadInitialConversation();
            }

            setupEventListeners() {
                // Platform tab switching
                document.querySelectorAll('.platform-tab').forEach(tab => {
                    tab.addEventListener('click', (e) => this.switchPlatform(e.target.dataset.platform));
                });

                // Chat thread selection
                document.querySelectorAll('.chat-thread').forEach(thread => {
                    thread.addEventListener('click', (e) => this.selectConversation(e.currentTarget));
                });

                // Search functionality
                document.getElementById('searchInput').addEventListener('input', (e) => this.searchConversations(e.target.value));

                // Status filter
                document.getElementById('statusFilter').addEventListener('change', (e) => this.filterByStatus(e.target.value));

                // Message sending
                document.getElementById('sendBtn').addEventListener('click', () => this.sendMessage());
                document.getElementById('messageInput').addEventListener('keypress', (e) => {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        this.sendMessage();
                    }
                });

                // Quick replies
                document.querySelectorAll('.quick-reply-btn').forEach(btn => {
                    btn.addEventListener('click', (e) => this.insertQuickReply(e.target.textContent));
                });

                // Auto-scroll to bottom
                this.scrollToBottom();
            }

            switchPlatform(platform) {
                // Update active tab
                document.querySelectorAll('.platform-tab').forEach(tab => {
                    tab.classList.toggle('active', tab.dataset.platform === platform);
                });

                // Show/hide thread containers
                document.querySelectorAll('.thread-container').forEach(container => {
                    container.classList.toggle('active', container.id === `${platform}-threads`);
                });

                this.currentPlatform = platform;
                this.updatePlatformIndicator(platform);
            }

            selectConversation(threadElement) {
                // Remove active class from all threads
                document.querySelectorAll('.chat-thread').forEach(thread => {
                    thread.classList.remove('active');
                });

                // Add active class to selected thread
                threadElement.classList.add('active');
                threadElement.classList.remove('unread');

                // Update conversation data
                this.currentConversation = threadElement.dataset.conversationId;
                const userName = threadElement.querySelector('.thread-name').textContent;
                const platform = threadElement.querySelector('.platform-icon').classList.contains('bi-whatsapp') ? 'whatsapp' : 'facebook';

                // Update chat header
                document.querySelector('.user-name').textContent = userName;
                this.updatePlatformIndicator(platform);

                // Hide empty state, show chat
                document.getElementById('emptyChatState').style.display = 'none';
                document.querySelector('.chat-interface').style.display = 'flex';

                // Load conversation messages
                this.loadConversationMessages(this.currentConversation);
            }

            updatePlatformIndicator(platform) {
                const indicator = document.querySelector('.platform-indicator');
                indicator.className = `platform-indicator ${platform}`;

                const icon = indicator.querySelector('i');
                icon.className = platform === 'whatsapp' ? 'bi bi-whatsapp' : 'bi bi-facebook';

                indicator.querySelector('span').textContent = platform === 'whatsapp' ? 'WhatsApp' : 'Facebook';
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

            sendMessage() {
                const input = document.getElementById('messageInput');
                const message = input.value.trim();

                if (!message) return;

                // Create message element
                const messageGroup = document.createElement('div');
                messageGroup.className = 'message-group';
                messageGroup.innerHTML = `
                    <div class="message sent">
                        <div class="message-content">
                            <p>${message}</p>
                            <span class="message-time">${this.getCurrentTime()}</span>
                        </div>
                    </div>
                `;

                // Add to messages container
                const container = document.getElementById('messagesContainer');
                container.appendChild(messageGroup);

                // Clear input
                input.value = '';
                input.style.height = 'auto';

                // Scroll to bottom
                this.scrollToBottom();

                // Simulate typing indicator
                this.showTypingIndicator();
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

            scrollToBottom() {
                const container = document.getElementById('messagesContainer');
                container.scrollTop = container.scrollHeight;
            }

            getCurrentTime() {
                return new Date().toLocaleTimeString('en-US', {
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                });
            }

            showTypingIndicator() {
                const indicator = document.querySelector('.typing-indicator');
                indicator.style.display = 'flex';

                setTimeout(() => {
                    indicator.style.display = 'none';
                }, 3000);
            }

            loadInitialConversation() {
                // Select first conversation by default
                const firstThread = document.querySelector('.chat-thread');
                if (firstThread) {
                    this.selectConversation(firstThread);
                }
            }

            loadConversationMessages(conversationId) {
                // Here you would typically make an AJAX call to load messages
                // For now, we'll keep the existing sample messages
                console.log(`Loading messages for conversation: ${conversationId}`);
            }
        }

        // Initialize the application when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            window.connectDeskApp = new ConnectDeskApp();
        });
    </script>
</body>
</html>
