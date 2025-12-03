<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ConnectDesk - Chat Integration</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .chat-widget {
            position: fixed;
            bottom: 80px;
            right: 20px;
            width: 350px;
            height: 500px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0,0,0,0.15);
            display: none;
            flex-direction: column;
            z-index: 1000;
        }
        .chat-widget.active {
            display: flex;
        }
        .chat-buttons {
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
            z-index: 999;
        }
        .chat-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.2s;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        .chat-btn:hover {
            transform: scale(1.1);
        }
        .whatsapp-btn {
            background: #25D366;
        }
        .facebook-btn {
            background: #0084FF;
        }
        .chat-header {
            padding: 15px;
            border-radius: 12px 12px 0 0;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .whatsapp-header {
            background: #25D366;
        }
        .facebook-header {
            background: #0084FF;
        }
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
            background: #f5f5f5;
        }
        .message {
            margin-bottom: 10px;
            padding: 10px 15px;
            border-radius: 18px;
            max-width: 70%;
            word-wrap: break-word;
        }
        .message.visitor {
            background: white;
            margin-right: auto;
        }
        .message.admin {
            background: #dcf8c6;
            margin-left: auto;
        }
        .chat-input {
            padding: 15px;
            border-top: 1px solid #e0e0e0;
            display: flex;
            gap: 10px;
        }
        .chat-input input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 20px;
            outline: none;
        }
        .chat-input button {
            padding: 10px 20px;
            border: none;
            border-radius: 20px;
            background: #25D366;
            color: white;
            cursor: pointer;
        }
        .facebook-input button {
            background: #0084FF;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-gray-800">ConnectDesk</h1>
                </div>
                <div class="flex items-center gap-4">
                    @if (Route::has('login'))
                        @auth
                            @if(auth()->user()->role === 'admin')
                                <a href="{{ route('admin.dashboard') }}" class="text-gray-700 hover:text-gray-900">Admin Dashboard</a>
                            @endif
                            <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-gray-900">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-gray-700 hover:text-gray-900 font-medium">Login</a>
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Welcome to ConnectDesk</h2>
            <p class="text-xl text-gray-600 mb-8">Connect with us via WhatsApp or Facebook Messenger</p>
            <div class="bg-white rounded-lg shadow p-8 max-w-2xl mx-auto">
                <h3 class="text-2xl font-semibold mb-4">How can we help you?</h3>
                <p class="text-gray-600">Click on the chat buttons in the bottom right corner to start a conversation with our team.</p>
            </div>
        </div>
    </main>

    <!-- Chat Buttons -->
    <div class="chat-buttons">
        <div class="chat-btn whatsapp-btn" onclick="toggleChat('whatsapp')">
            <svg width="30" height="30" fill="white" viewBox="0 0 24 24">
                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824zm-3.423-14.416c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm.029 18.88c-1.161 0-2.305-.292-3.318-.844l-3.677.964.984-3.595c-.607-1.052-.927-2.246-.926-3.468.001-3.825 3.113-6.937 6.937-6.937 1.856.001 3.598.723 4.907 2.034 1.31 1.311 2.031 3.054 2.03 4.908-.001 3.825-3.113 6.938-6.937 6.938z"/>
            </svg>
        </div>
        <div class="chat-btn facebook-btn" onclick="toggleChat('facebook')">
            <svg width="30" height="30" fill="white" viewBox="0 0 24 24">
                <path d="M12 2c5.514 0 10 4.486 10 10s-4.486 10-10 10-10-4.486-10-10 4.486-10 10-10zm0-2c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm0 7.082c1.602 0 1.792.006 2.425.035 1.627.074 2.385.845 2.46 2.459.028.633.034.822.034 2.424s-.006 1.792-.034 2.424c-.075 1.613-.832 2.386-2.46 2.46-.633.028-.822.035-2.425.035-1.602 0-1.792-.006-2.424-.035-1.63-.075-2.385-.849-2.46-2.46-.028-.632-.035-.822-.035-2.424s.007-1.792.035-2.424c.074-1.615.832-2.386 2.46-2.46.632-.029.822-.034 2.424-.034zm0-1.082c-1.63 0-1.833.007-2.474.037-2.18.1-3.39 1.309-3.49 3.489-.029.641-.036.845-.036 2.474 0 1.63.007 1.834.036 2.474.1 2.179 1.31 3.39 3.49 3.49.641.029.844.036 2.474.036 1.63 0 1.834-.007 2.475-.036 2.176-.1 3.391-1.309 3.489-3.49.029-.64.036-.844.036-2.474 0-1.629-.007-1.833-.036-2.474-.098-2.177-1.309-3.39-3.489-3.489-.641-.03-.845-.037-2.475-.037zm0 2.919c-1.701 0-3.081 1.379-3.081 3.081s1.38 3.081 3.081 3.081 3.081-1.379 3.081-3.081c0-1.701-1.38-3.081-3.081-3.081zm0 5.081c-1.105 0-2-.895-2-2 0-1.104.895-2 2-2 1.104 0 2.001.895 2.001 2s-.897 2-2.001 2zm3.202-5.922c-.397 0-.72.322-.72.72 0 .397.322.72.72.72.398 0 .721-.322.721-.72 0-.398-.322-.72-.721-.72z"/>
            </svg>
        </div>
    </div>

    <!-- WhatsApp Chat Widget -->
    <div id="whatsapp-widget" class="chat-widget">
        <div class="chat-header whatsapp-header">
            <span class="font-semibold">WhatsApp Chat</span>
            <button onclick="closeChat('whatsapp')" class="text-white text-xl">&times;</button>
        </div>
        <div id="whatsapp-messages" class="chat-messages"></div>
        <div class="chat-input">
            <input type="text" id="whatsapp-input" placeholder="Type a message..." onkeypress="handleKeyPress(event, 'whatsapp')">
            <button onclick="sendMessage('whatsapp')">Send</button>
        </div>
    </div>

    <!-- Facebook Chat Widget -->
    <div id="facebook-widget" class="chat-widget">
        <div class="chat-header facebook-header">
            <span class="font-semibold">Messenger</span>
            <button onclick="closeChat('facebook')" class="text-white text-xl">&times;</button>
        </div>
        <div id="facebook-messages" class="chat-messages"></div>
        <div class="chat-input facebook-input">
            <input type="text" id="facebook-input" placeholder="Type a message..." onkeypress="handleKeyPress(event, 'facebook')">
            <button onclick="sendMessage('facebook')">Send</button>
        </div>
    </div>

    <script>
        let visitorId = localStorage.getItem('visitor_id') || null;
        let currentPlatform = null;

        function toggleChat(platform) {
            const widget = document.getElementById(`${platform}-widget`);
            const otherPlatform = platform === 'whatsapp' ? 'facebook' : 'whatsapp';
            const otherWidget = document.getElementById(`${otherPlatform}-widget`);

            otherWidget.classList.remove('active');
            widget.classList.toggle('active');

            if (widget.classList.contains('active')) {
                currentPlatform = platform;
                loadMessages(platform);
            } else {
                currentPlatform = null;
            }
        }

        function closeChat(platform) {
            document.getElementById(`${platform}-widget`).classList.remove('active');
            currentPlatform = null;
        }

        function handleKeyPress(event, platform) {
            if (event.key === 'Enter') {
                sendMessage(platform);
            }
        }

        async function sendMessage(platform) {
            const input = document.getElementById(`${platform}-input`);
            const message = input.value.trim();

            if (!message) return;

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
                        visitor_id: visitorId
                    })
                });

                const data = await response.json();

                if (data.success) {
                    visitorId = data.visitor_id;
                    localStorage.setItem('visitor_id', visitorId);

                    addMessageToUI(platform, message, 'visitor');
                    input.value = '';
                }
            } catch (error) {
                console.error('Error sending message:', error);
            }
        }

        function addMessageToUI(platform, message, sender) {
            const messagesDiv = document.getElementById(`${platform}-messages`);
            const messageEl = document.createElement('div');
            messageEl.className = `message ${sender}`;
            messageEl.textContent = message;
            messagesDiv.appendChild(messageEl);
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }

        async function loadMessages(platform) {
            // This will be implemented with real-time updates later
            const messagesDiv = document.getElementById(`${platform}-messages`);
            messagesDiv.innerHTML = '<div class="text-center text-gray-500 py-4">Start a conversation</div>';
        }

        // Poll for new messages every 3 seconds when chat is open
        setInterval(() => {
            if (currentPlatform && visitorId) {
                // This will be enhanced with real-time updates
            }
        }, 3000);
    </script>
</body>
</html>
