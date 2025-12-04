// Admin Dashboard JavaScript

let currentConversationId = null;
let currentPlatform = null;

/**
 * Filter conversations by platform
 */
function filterConversations(platform) {
    const buttons = document.querySelectorAll('.filter-btn');
    buttons.forEach(btn => {
        btn.classList.remove('active', 'btn-light');
        btn.classList.add('btn-outline-secondary');
    });

    event.target.classList.remove('btn-outline-secondary');
    event.target.classList.add('active', 'btn-light');

    const items = document.querySelectorAll('.conversation-item');
    items.forEach(item => {
        if (platform === 'all' || item.dataset.platform === platform) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

/**
 * Search conversations
 */
function searchConversations() {
    const search = document.getElementById('search-conversations').value.toLowerCase();
    const items = document.querySelectorAll('.conversation-item');
    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(search) ? 'block' : 'none';
    });
}

/**
 * Load conversation messages
 */
async function loadConversation(conversationId) {
    currentConversationId = conversationId;

    // Update UI to show selected conversation
    document.querySelectorAll('.conversation-item').forEach(item => {
        item.classList.remove('active');
    });

    const selectedItem = document.querySelector(`[data-conversation-id="${conversationId}"]`);
    if (selectedItem) {
        selectedItem.classList.add('active');
    }

    try {
        const response = await fetch(`/admin/api/conversations/${conversationId}/messages`);
        const data = await response.json();

        currentPlatform = data.conversation.platform;

        // Show chat container
        document.getElementById('no-conversation').classList.add('d-none');
        const chatContainer = document.getElementById('chat-container');
        chatContainer.classList.remove('d-none');
        chatContainer.classList.add('chat-active');
        chatContainer.style.display = 'flex';
        chatContainer.style.flexDirection = 'column';
        chatContainer.style.height = '100%';
        document.getElementById('conversation-id').value = conversationId;

        // Ensure input area is visible and properly styled
        const inputArea = document.querySelector('.message-input-area');
        const replyInput = document.getElementById('reply-input');
        const sendBtn = document.getElementById('send-btn');

        if (inputArea) {
            inputArea.style.display = 'flex';
            inputArea.style.visibility = 'visible';
            inputArea.style.opacity = '1';
            inputArea.style.position = 'relative';
            inputArea.style.zIndex = '1000';
            console.log('Message input area found and made visible');
        } else {
            console.error('Message input area not found!');
        }

        if (replyInput) {
            replyInput.disabled = false;
            replyInput.style.display = 'block';
            console.log('Reply input enabled');
        }

        if (sendBtn) {
            sendBtn.disabled = false;
            sendBtn.style.display = 'flex';
            console.log('Send button enabled');
        }

        // Force a layout reflow
        setTimeout(() => {
            if (inputArea) {
                inputArea.style.display = 'flex';
            }
        }, 100);

        // Update header
        const title = data.conversation.visitor_name || 'Anonymous User';
        const email = data.conversation.visitor_email || '';
        document.getElementById('chat-title').textContent = title;
        document.getElementById('chat-email').textContent = email;
        document.getElementById('chat-platform').textContent =
            data.conversation.platform === 'whatsapp' ? 'WhatsApp' : 'Facebook Messenger';

        // Update platform icon
        const platformIcon = document.getElementById('chat-platform-icon');
        if (data.conversation.platform === 'whatsapp') {
            platformIcon.innerHTML = '<i class="bi bi-whatsapp text-success" style="font-size: 1.3rem;"></i>';
        } else {
            platformIcon.innerHTML = '<i class="bi bi-messenger text-primary" style="font-size: 1.3rem;"></i>';
        }

        // Load messages
        const messagesWrapper = document.querySelector('.messages-wrapper');
        messagesWrapper.innerHTML = '';

        if (data.messages.length === 0) {
            messagesWrapper.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-chat-dots text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3">No messages yet. Start the conversation!</p>
                </div>
            `;
        } else {
            data.messages.forEach(message => {
                addMessageToUI(message);
            });
        }

        // Scroll to bottom
        const messagesContainer = document.getElementById('messages-container');
        messagesContainer.scrollTop = messagesContainer.scrollHeight;

        // Mark as read
        await fetch(`/admin/api/conversations/${conversationId}/mark-read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        loadConversations();
    } catch (error) {
        console.error('Error loading conversation:', error);
    }
}

/**
 * Add message to UI
 */
function addMessageToUI(message) {
    const messagesWrapper = document.querySelector('.messages-wrapper');
    const messageEl = document.createElement('div');
    messageEl.className = `row mb-3 message-enter ${message.sender_type === 'admin' ? 'justify-content-end' : 'justify-content-start'}`;

    const time = new Date(message.created_at);
    const timeString = time.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

    const bubbleClass = message.sender_type === 'admin'
        ? 'message-bubble-admin text-white'
        : 'message-bubble-customer';

    messageEl.innerHTML = `
        <div class="col-auto" style="max-width: 65%;">
            <div class="${bubbleClass} px-3 py-2">
                <p class="mb-1" style="white-space: pre-wrap; word-break: break-word; font-size: 0.9rem; line-height: 1.4;">${escapeHtml(message.message)}</p>
                <div class="d-flex align-items-center justify-content-end gap-1">
                    <small class="${message.sender_type === 'admin' ? 'text-white-50' : 'text-muted'}" style="font-size: 0.7rem;">${timeString}</small>
                    ${message.sender_type === 'admin' ? '<i class="bi bi-check2-all text-white-50 ms-1" style="font-size: 0.7rem;"></i>' : ''}
                </div>
            </div>
            <small class="${message.sender_type === 'admin' ? 'text-end' : 'text-start'} d-block mt-1 px-2" style="font-size: 0.7rem; color: #6b7280;">
                ${message.sender_type === 'admin' ? 'You' : 'Customer'}
            </small>
        </div>
    `;

    messagesWrapper.appendChild(messageEl);
}

/**
 * Send reply message
 */
async function sendReply(event) {
    event.preventDefault();
    console.log('Send reply function called');

    const conversationId = document.getElementById('conversation-id').value;
    const input = document.getElementById('reply-input');
    const message = input.value.trim();

    console.log('Conversation ID:', conversationId);
    console.log('Message:', message);

    if (!message) {
        console.log('No message to send');
        return;
    }

    if (!conversationId) {
        console.error('No conversation ID set');
        return;
    }

    try {
        const response = await fetch(`/admin/api/conversations/${conversationId}/messages`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ message })
        });

        const data = await response.json();

        if (data.success) {
            addMessageToUI(data.message);
            input.value = '';
            input.style.height = 'auto';

            const messagesContainer = document.getElementById('messages-container');
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    } catch (error) {
        console.error('Error sending reply:', error);
    }
}

/**
 * Load conversations list
 */
async function loadConversations() {
    try {
        const response = await fetch('/admin/api/conversations');
        const data = await response.json();
        document.getElementById('total-unread').textContent = data.totalUnread;
    } catch (error) {
        console.error('Error loading conversations:', error);
    }
}

/**
 * Escape HTML
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Auto resize textarea (not used with input type)
 */
function autoResize(textarea) {
    // No longer needed with input type="text"
    return;
}

/**
 * Handle textarea keydown
 */
function handleTextareaKeydown(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        document.getElementById('reply-form').dispatchEvent(new Event('submit'));
    }
}

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin Dashboard JavaScript loaded');

    // Ensure form submission works
    const replyForm = document.getElementById('reply-form');
    if (replyForm) {
        console.log('Reply form found');
        replyForm.addEventListener('submit', sendReply);
    } else {
        console.error('Reply form not found');
    }

    // Check if input area exists
    const inputArea = document.querySelector('.message-input-area');
    if (inputArea) {
        console.log('Input area found on page load');
    } else {
        console.log('Input area not found on page load');
    }

    // Load initial conversations
    loadConversations();
});

// Auto refresh conversations
setInterval(loadConversations, 5000);

// Auto refresh current conversation
setInterval(() => {
    if (currentConversationId) {
        const messagesContainer = document.getElementById('messages-container');
        const scrolledToBottom = messagesContainer.scrollHeight - messagesContainer.clientHeight <= messagesContainer.scrollTop + 100;

        loadConversation(currentConversationId).then(() => {
            if (scrolledToBottom) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        });
    }
}, 3000);
