<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ConnectDesk - Professional Chat Integration Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/frontend.css') }}" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-content">
            <a href="#" class="logo">
                <i class="bi bi-chat-dots-fill"></i>
                ConnectDesk
            </a>
            <div class="nav-links">

                @if (Route::has('login'))
                    @auth
                        @if(auth()->user()->role === 'admin')
                            <a href="{{ route('admin.dashboard') }}" class="nav-link">Admin Dashboard</a>
                        @endif
                        <a href="{{ route('dashboard') }}" class="nav-link">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="nav-link">Laravel Auth</a>
                    @endauth
                @endif

                <!-- Guest Actions -->
                <div id="guest-auth-section" class="guest-auth-section">
                    <button onclick="showModal()" class="btn-primary">Login / Register</button>
                </div>

                <!-- User Authentication Status -->
                <div id="user-auth-section" class="user-auth-section" style="display: none;">
                    <div class="user-info">
                        <div class="user-avatar">
                            <i class="bi bi-person-circle"></i>
                        </div>
                        <div class="user-details">
                            <span id="user-name" class="user-name"></span>
                            <span id="user-email" class="user-email"></span>
                        </div>
                        <div class="user-actions">
                            <button onclick="toggleUserMenu()" class="user-menu-btn">
                                <i class="bi bi-chevron-down"></i>
                            </button>
                            <div id="user-menu" class="user-menu" style="display: none;">
                                <div class="user-menu-header">
                                    <div class="user-menu-name" id="menu-user-name"></div>
                                    <div class="user-menu-email" id="menu-user-email"></div>
                                    <div class="user-menu-phone" id="menu-user-phone"></div>
                                </div>
                                <div class="user-menu-divider"></div>
                                <button onclick="testWhatsApp()" class="user-menu-item">
                                    <i class="bi bi-whatsapp"></i>
                                    Test WhatsApp Integration
                                </button>
                                <button onclick="logoutUser()" class="user-menu-item logout-btn">
                                    <i class="bi bi-box-arrow-right"></i>
                                    Logout
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-gradient">
        <div class="hero-pattern"></div>
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">Connect Seamlessly with Your Customers</h1>
                <p class="hero-subtitle">
                    Professional chat integration platform that brings WhatsApp and Facebook Messenger
                    conversations directly to your business workflow
                </p>
            </div>

            <div class="hero-features">
                <div class="feature-card">
                    <div class="feature-icon whatsapp-icon">
                        <i class="bi bi-whatsapp"></i>
                    </div>
                    <h3 class="feature-title">WhatsApp Integration</h3>
                    <p class="feature-description">
                        Connect with customers through WhatsApp Business API. Manage conversations,
                        send multimedia messages, and provide instant customer support.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon facebook-icon">
                        <i class="bi bi-facebook"></i>
                    </div>
                    <h3 class="feature-title">Facebook Messenger</h3>
                    <p class="feature-description">
                        Integrate Facebook Messenger to reach customers on their preferred platform.
                        Seamless conversations with rich media support and automated responses.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon integration-icon">
                        <i class="bi bi-gear-fill"></i>
                    </div>
                    <h3 class="feature-title">Unified Dashboard</h3>
                    <p class="feature-description">
                        Manage all your conversations from one powerful dashboard. Track metrics,
                        assign agents, and deliver exceptional customer experiences.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Floating Chat Buttons -->
    <div class="chat-floating-buttons">
        <div class="chat-trigger whatsapp-trigger" onclick="toggleChat('whatsapp')">
            <i class="bi bi-whatsapp"></i>
            <div class="chat-tooltip">Chat via WhatsApp</div>
        </div>
        <div class="chat-trigger facebook-trigger" onclick="toggleChat('facebook')">
            <i class="bi bi-messenger"></i>
            <div class="chat-tooltip">Chat via Messenger</div>
        </div>
    </div>

    <!-- User Registration Modal -->
    <div id="registration-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Register to Send Message</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="registration-form" style="display: block;">
                    <p>Please register to send messages through our chat platform:</p>
                    <form id="registerForm">
                        <div class="form-group">
                            <label for="reg-name">Full Name *</label>
                            <input type="text" id="reg-name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="reg-email">Email Address *</label>
                            <input type="email" id="reg-email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="reg-phone">Phone Number *</label>
                            <input type="tel" id="reg-phone" name="phone_number" placeholder="+8801XXXXXXXXX" required>
                        </div>
                        <div class="form-group">
                            <label for="reg-password">Password *</label>
                            <input type="password" id="reg-password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="reg-password-confirm">Confirm Password *</label>
                            <input type="password" id="reg-password-confirm" name="password_confirmation" required>
                        </div>
                        <button type="submit" class="btn-primary" style="width: 100%;">Register</button>
                    </form>
                    <div class="form-footer">
                        <p>Already have an account? <a href="#" onclick="showLogin()">Login here</a></p>
                    </div>
                </div>

                <div id="login-form" style="display: none;">
                    <p>Login to continue chatting:</p>
                    <form id="loginForm">
                        <div class="form-group">
                            <label for="login-email">Email Address *</label>
                            <input type="email" id="login-email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="login-password">Password *</label>
                            <input type="password" id="login-password" name="password" required>
                        </div>
                        <button type="submit" class="btn-primary" style="width: 100%;">Login</button>
                    </form>
                    <div class="form-footer">
                        <p>Don't have an account? <a href="#" onclick="showRegistration()">Register here</a></p>
                    </div>
                </div>

                <div id="form-loading" style="display: none; text-align: center; padding: 2rem;">
                    <div class="loading-spinner"></div>
                    <p>Processing...</p>
                </div>

                <div id="form-error" class="error-message" style="display: none;"></div>
            </div>
        </div>
    </div>

    <!-- WhatsApp Chat Widget -->
    <div id="whatsapp-widget" class="chat-widget">
        <div class="chat-header whatsapp-header">
            <div class="chat-header-info">
                <div class="chat-avatar">
                    <i class="bi bi-whatsapp"></i>
                </div>
                <div class="chat-info">
                    <div class="chat-title">WhatsApp Support</div>
                    <div class="chat-status">Typically replies instantly</div>
                </div>
            </div>
            <button class="chat-close" onclick="closeChat('whatsapp')">
                <i class="bi bi-x"></i>
            </button>
        </div>
        <div id="whatsapp-messages" class="chat-messages">
            <div class="welcome-message">
                <div class="welcome-title">👋 Hi there!</div>
                <div class="welcome-text">Welcome to ConnectDesk. How can we help you today?</div>
            </div>
        </div>
        <div class="chat-input-container">
            <div class="chat-input-wrapper">
                <textarea
                    id="whatsapp-input"
                    class="chat-input"
                    placeholder="Type a message..."
                    rows="1"
                    onkeypress="handleKeyPress(event, 'whatsapp')"
                    oninput="autoResize(this); toggleSendButton('whatsapp')"></textarea>
                <button id="whatsapp-send" class="chat-send-btn" onclick="sendMessage('whatsapp')">
                    <i class="bi bi-send-fill"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Facebook Chat Widget -->
    <div id="facebook-widget" class="chat-widget">
        <div class="chat-header facebook-header">
            <div class="chat-header-info">
                <div class="chat-avatar">
                    <i class="bi bi-messenger"></i>
                </div>
                <div class="chat-info">
                    <div class="chat-title">Messenger Support</div>
                    <div class="chat-status">We're here to help</div>
                </div>
            </div>
            <button class="chat-close" onclick="closeChat('facebook')">
                <i class="bi bi-x"></i>
            </button>
        </div>
        <div id="facebook-messages" class="chat-messages">
            <div class="welcome-message">
                <div class="welcome-title">🚀 Hello!</div>
                <div class="welcome-text">Thanks for reaching out via Messenger. What can we assist you with?</div>
            </div>
        </div>
        <div class="chat-input-container">
            <div class="chat-input-wrapper">
                <textarea
                    id="facebook-input"
                    class="chat-input"
                    placeholder="Type a message..."
                    rows="1"
                    onkeypress="handleKeyPress(event, 'facebook')"
                    oninput="autoResize(this); toggleSendButton('facebook')"></textarea>
                <button id="facebook-send" class="chat-send-btn" onclick="sendMessage('facebook')">
                    <i class="bi bi-send-fill"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
        // Application State
        let currentUser = JSON.parse(localStorage.getItem('currentUser')) || null;
        let currentPlatform = null;
        let messagesCache = {};

        // Initialize user interface
        function initializeUserInterface() {
            if (currentUser) {
                showUserAuthenticated();
            } else {
                showGuestInterface();
            }
        }

        function showUserAuthenticated() {
            document.getElementById('user-auth-section').style.display = 'flex';
            document.getElementById('guest-auth-section').style.display = 'none';

            // Update user display
            document.getElementById('user-name').textContent = currentUser.name;
            document.getElementById('user-email').textContent = currentUser.email;
            document.getElementById('menu-user-name').textContent = currentUser.name;
            document.getElementById('menu-user-email').textContent = currentUser.email;
            document.getElementById('menu-user-phone').textContent = currentUser.phone_number || 'No phone';
        }

        function showGuestInterface() {
            document.getElementById('user-auth-section').style.display = 'none';
            document.getElementById('guest-auth-section').style.display = 'flex';
        }

        function toggleUserMenu() {
            const menu = document.getElementById('user-menu');
            const isVisible = menu.style.display === 'block';
            menu.style.display = isVisible ? 'none' : 'block';

            // Close menu when clicking outside
            if (!isVisible) {
                setTimeout(() => {
                    document.addEventListener('click', function closeMenu(e) {
                        if (!e.target.closest('.user-actions')) {
                            menu.style.display = 'none';
                            document.removeEventListener('click', closeMenu);
                        }
                    });
                }, 100);
            }
        }

        function logoutUser() {
            currentUser = null;
            localStorage.removeItem('currentUser');
            showGuestInterface();

            // Close any open chat widgets
            document.querySelectorAll('.chat-widget').forEach(widget => {
                widget.classList.remove('active');
            });
            currentPlatform = null;

            // Show success message
            showNotification('Logged out successfully!', 'success');
        }

        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.innerHTML = `
                <div class="notification-content">
                    <i class="bi bi-${type === 'success' ? 'check-circle' : (type === 'error' ? 'x-circle' : 'info-circle')}"></i>
                    <span>${message}</span>
                </div>
            `;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.classList.add('show');
            }, 100);

            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 5000);
        }

        async function testWhatsApp() {
            try {
                showNotification('Testing WhatsApp integration...', 'info');

                const response = await fetch('/api/test-whatsapp', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const result = await response.json();

                if (result.success) {
                    showNotification(`✅ ${result.message}`, 'success');
                } else {
                    showNotification(`❌ ${result.message}`, 'error');
                }
            } catch (error) {
                console.error('WhatsApp test error:', error);
                showNotification('❌ WhatsApp test failed - Network error', 'error');
            }

            // Close user menu
            document.getElementById('user-menu').style.display = 'none';
        }        // User Authentication
        function showModal() {
            document.getElementById('registration-modal').style.display = 'flex';
            showRegistration();
        }

        function closeModal() {
            document.getElementById('registration-modal').style.display = 'none';
            resetForms();
        }

        function showRegistration() {
            document.getElementById('registration-form').style.display = 'block';
            document.getElementById('login-form').style.display = 'none';
            document.getElementById('form-loading').style.display = 'none';
            document.getElementById('form-error').style.display = 'none';
        }

        function showLogin() {
            document.getElementById('registration-form').style.display = 'none';
            document.getElementById('login-form').style.display = 'block';
            document.getElementById('form-loading').style.display = 'none';
            document.getElementById('form-error').style.display = 'none';
        }

        function showLoading() {
            document.getElementById('registration-form').style.display = 'none';
            document.getElementById('login-form').style.display = 'none';
            document.getElementById('form-loading').style.display = 'block';
            document.getElementById('form-error').style.display = 'none';
        }

        function showError(message) {
            const errorDiv = document.getElementById('form-error');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
        }

        function resetForms() {
            document.getElementById('registerForm').reset();
            document.getElementById('loginForm').reset();
            document.getElementById('form-error').style.display = 'none';
        }

        // Chat Widget Management
        function toggleChat(platform) {
            // Check if user is authenticated
            if (!currentUser) {
                showModal();
                return;
            }

            const widget = document.getElementById(`${platform}-widget`);
            const otherPlatform = platform === 'whatsapp' ? 'facebook' : 'whatsapp';
            const otherWidget = document.getElementById(`${otherPlatform}-widget`);

            // Close other widget
            otherWidget.classList.remove('active');

            // Toggle current widget
            widget.classList.toggle('active');

            if (widget.classList.contains('active')) {
                currentPlatform = platform;
                loadMessages(platform);
                focusInput(platform);
            } else {
                currentPlatform = null;
            }
        }

        function closeChat(platform) {
            document.getElementById(`${platform}-widget`).classList.remove('active');
            currentPlatform = null;
        }

        function focusInput(platform) {
            setTimeout(() => {
                const input = document.getElementById(`${platform}-input`);
                input.focus();
            }, 100);
        }

        // Input Handling
        function handleKeyPress(event, platform) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                sendMessage(platform);
            }
        }

        function autoResize(textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = Math.min(textarea.scrollHeight, 80) + 'px';
        }

        function toggleSendButton(platform) {
            const input = document.getElementById(`${platform}-input`);
            const sendBtn = document.getElementById(`${platform}-send`);
            const hasContent = input.value.trim().length > 0;

            sendBtn.classList.toggle('active', hasContent);
        }

        // Message Management
        async function sendMessage(platform) {
            if (!currentUser) {
                showModal();
                return;
            }

            const input = document.getElementById(`${platform}-input`);
            const message = input.value.trim();

            if (!message) return;

            // Disable input temporarily
            input.disabled = true;
            const sendBtn = document.getElementById(`${platform}-send`);
            sendBtn.style.opacity = '0.5';

            try {
                const response = await fetch('/api/messages', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        message: message,
                        platform: platform,
                        user_id: currentUser.id
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Add message to UI
                    addMessageToUI(platform, message, 'sent');

                    // Clear input
                    input.value = '';
                    input.style.height = 'auto';
                    toggleSendButton(platform);

                    // Show WhatsApp sent status if applicable
                    if (platform === 'whatsapp' && data.whatsapp_sent) {
                        showSuccessMessage(platform, 'Message sent via WhatsApp!');
                    }

                    // Simulate admin response (for demo)
                    setTimeout(() => {
                        showTypingIndicator(platform);
                        setTimeout(() => {
                            hideTypingIndicator(platform);
                            addAdminResponse(platform);
                        }, 2000);
                    }, 1000);
                } else {
                    showErrorMessage(platform, data.message || 'Failed to send message');
                }
            } catch (error) {
                console.error('Error sending message:', error);
                showErrorMessage(platform, 'Failed to send message. Please try again.');
            } finally {
                // Re-enable input
                input.disabled = false;
                sendBtn.style.opacity = '1';
                input.focus();
            }
        }

        function addMessageToUI(platform, message, type) {
            const messagesDiv = document.getElementById(`${platform}-messages`);

            // Remove welcome message if exists
            const welcomeMessage = messagesDiv.querySelector('.welcome-message');
            if (welcomeMessage) {
                welcomeMessage.remove();
            }

            const messageEl = document.createElement('div');
            messageEl.className = `message-bubble message-${type} message-enter`;

            if (type === 'sent') {
                messageEl.classList.add(platform === 'whatsapp' ? 'whatsapp-sent' : 'facebook-sent');
            }

            const messageContent = document.createElement('div');
            messageContent.textContent = message;

            const messageTime = document.createElement('div');
            messageTime.className = 'message-time';
            messageTime.textContent = getCurrentTime();

            messageEl.appendChild(messageContent);
            messageEl.appendChild(messageTime);
            messagesDiv.appendChild(messageEl);

            scrollToBottom(messagesDiv);
        }

        function addAdminResponse(platform) {
            const responses = [
                "Thanks for reaching out! How can I assist you today?",
                "I've received your message. Let me help you with that.",
                "Hello! I'm here to help. What can I do for you?",
                "Thanks for contacting us. I'll be happy to assist you."
            ];

            const randomResponse = responses[Math.floor(Math.random() * responses.length)];
            addMessageToUI(platform, randomResponse, 'received');
        }

        function showTypingIndicator(platform) {
            const messagesDiv = document.getElementById(`${platform}-messages`);

            const typingEl = document.createElement('div');
            typingEl.className = 'typing-indicator';
            typingEl.innerHTML = `
                <div class="typing-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <span>Support is typing...</span>
            `;

            messagesDiv.appendChild(typingEl);
            scrollToBottom(messagesDiv);
        }

        function hideTypingIndicator(platform) {
            const messagesDiv = document.getElementById(`${platform}-messages`);
            const typingIndicator = messagesDiv.querySelector('.typing-indicator');
            if (typingIndicator) {
                typingIndicator.remove();
            }
        }

        function showErrorMessage(platform, errorText) {
            const messagesDiv = document.getElementById(`${platform}-messages`);

            const errorEl = document.createElement('div');
            errorEl.className = 'error-message';
            errorEl.style.cssText = `
                color: #dc2626;
                text-align: center;
                padding: 0.75rem;
                font-size: 0.8rem;
                background: rgba(220, 38, 38, 0.1);
                border-radius: 8px;
                margin: 0.5rem 0;
            `;
            errorEl.textContent = errorText;

            messagesDiv.appendChild(errorEl);
            scrollToBottom(messagesDiv);

            // Auto-remove error after 5 seconds
            setTimeout(() => {
                if (errorEl.parentNode) {
                    errorEl.remove();
                }
            }, 5000);
        }

        // Utility Functions
        function getCurrentTime() {
            return new Date().toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
        }

        function scrollToBottom(element) {
            element.scrollTop = element.scrollHeight;
        }

        async function loadMessages(platform) {
            // In a real implementation, this would load existing messages
            // For now, we'll just ensure the welcome message is visible if no messages exist
            const messagesDiv = document.getElementById(`${platform}-messages`);
            const hasMessages = messagesDiv.querySelector('.message-bubble');

            if (!hasMessages) {
                // Welcome message is already in HTML, no need to add it
            }
        }

        function showSuccessMessage(platform, message) {
            const messagesDiv = document.getElementById(`${platform}-messages`);

            const successEl = document.createElement('div');
            successEl.className = 'success-message';
            successEl.style.cssText = `
                color: #059669;
                text-align: center;
                padding: 0.75rem;
                font-size: 0.8rem;
                background: rgba(5, 150, 105, 0.1);
                border-radius: 8px;
                margin: 0.5rem 0;
            `;
            successEl.textContent = message;

            messagesDiv.appendChild(successEl);
            scrollToBottom(messagesDiv);

            // Auto-remove success message after 3 seconds
            setTimeout(() => {
                if (successEl.parentNode) {
                    successEl.remove();
                }
            }, 3000);
        }

        // Form Handlers
        document.getElementById('registerForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            showLoading();

            const formData = new FormData(this);
            const data = Object.fromEntries(formData);

            try {
                const response = await fetch('/api/users/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    currentUser = result.user;
                    localStorage.setItem('currentUser', JSON.stringify(currentUser));
                    showUserAuthenticated();
                    closeModal();
                    showNotification(`Welcome ${result.user.name}! Registration successful.`, 'success');
                    // Auto-open chat after registration
                    if (currentPlatform) {
                        toggleChat(currentPlatform);
                    }
                } else {
                    showRegistration();
                    if (result.errors) {
                        const firstError = Object.values(result.errors)[0][0];
                        showError(firstError);
                    } else {
                        showError(result.message || 'Registration failed');
                    }
                }
            } catch (error) {
                showRegistration();
                showError('Network error. Please try again.');
            }
        });

        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            showLoading();

            const formData = new FormData(this);
            const data = Object.fromEntries(formData);

            try {
                const response = await fetch('/api/users/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    currentUser = result.user;
                    localStorage.setItem('currentUser', JSON.stringify(currentUser));
                    showUserAuthenticated();
                    closeModal();
                    showNotification(`Welcome back ${result.user.name}!`, 'success');
                    // Auto-open chat after login
                    if (currentPlatform) {
                        toggleChat(currentPlatform);
                    }
                } else {
                    showLogin();
                    showError(result.message || 'Login failed');
                }
            } catch (error) {
                showLogin();
                showError('Network error. Please try again.');
            }
        });

        // Initialize Application
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize user interface based on current state
            initializeUserInterface();
            // Add smooth scrolling to hero section
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                });
            });

            // Add entrance animation to feature cards
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animation = 'slideUp 0.6s ease-out forwards';
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.feature-card').forEach(card => {
                observer.observe(card);
            });
        });

        // Real-time updates (enhanced version)
        setInterval(() => {
            if (currentPlatform && visitorId) {
                // This would poll for new messages from admin
                // Implementation depends on your real-time strategy (WebSockets, Server-Sent Events, etc.)
            }
        }, 3000);

        // Add CSS animations for entrance effects
        const style = document.createElement('style');
        style.textContent = `
            .typing-indicator {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.75rem;
                background: white;
                border-radius: 12px;
                margin: 0.5rem 0;
                max-width: 150px;
                border: 1px solid var(--gray-200);
                animation: slideUp 0.3s ease-out;
            }

            .typing-dots {
                display: flex;
                gap: 0.125rem;
            }

            .typing-dots span {
                width: 4px;
                height: 4px;
                background: var(--gray-400);
                border-radius: 50%;
                animation: typing 1.4s infinite;
            }

            .typing-dots span:nth-child(2) {
                animation-delay: 0.2s;
            }

            .typing-dots span:nth-child(3) {
                animation-delay: 0.4s;
            }

            @keyframes typing {
                0%, 60%, 100% {
                    transform: translateY(0);
                }
                30% {
                    transform: translateY(-6px);
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
