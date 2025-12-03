<div class="d-flex flex-column h-100 bg-light">
    <!-- Sidebar Header -->
    <div class="p-3 border-bottom bg-white">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0">Messages</h6>
            <button class="btn btn-sm btn-light rounded-circle p-2">
                <i class="bi bi-three-dots-vertical"></i>
            </button>
        </div>

        <!-- Search Bar -->
        <div class="position-relative mb-3">
            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
            <input type="text" id="search-conversations"
                   class="form-control form-control-sm rounded-pill ps-5"
                   placeholder="Search conversations..."
                   onkeyup="searchConversations()">
        </div>

        <!-- Filter Tabs -->
        <div class="btn-group w-100 rounded-pill p-1 bg-body-secondary" role="group">
            <button type="button" class="filter-btn btn btn-sm btn-light active rounded-pill" onclick="filterConversations('all')">
                All
            </button>
            <button type="button" class="filter-btn btn btn-sm btn-outline-secondary rounded-pill" onclick="filterConversations('whatsapp')">
                <i class="bi bi-whatsapp me-1"></i>WhatsApp
            </button>
            <button type="button" class="filter-btn btn btn-sm btn-outline-secondary rounded-pill" onclick="filterConversations('facebook')">
                <i class="bi bi-messenger me-1"></i>Messenger
            </button>
        </div>
    </div>

    <!-- Conversations List -->
    <div id="conversations-list" class="flex-grow-1 overflow-auto custom-scrollbar">
        @forelse($conversations as $conversation)
            <div class="conversation-item p-3 border-bottom cursor-pointer hover-bg-white"
                 data-platform="{{ $conversation->platform }}"
                 data-conversation-id="{{ $conversation->id }}"
                 onclick="loadConversation({{ $conversation->id }})">
                <div class="d-flex align-items-start gap-2">
                    <!-- Platform Icon -->
                    <div class="position-relative flex-shrink-0">
                        @if($conversation->platform === 'whatsapp')
                            <div class="rounded-circle bg-success platform-icon">
                                <i class="bi bi-whatsapp text-white"></i>
                            </div>
                        @else
                            <div class="rounded-circle bg-primary platform-icon">
                                <i class="bi bi-messenger text-white"></i>
                            </div>
                        @endif
                        @if($conversation->unread_count > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                {{ min($conversation->unread_count, 9) }}
                            </span>
                        @endif
                    </div>

                    <!-- Conversation Info -->
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h6 class="fw-semibold mb-0 text-truncate small">
                                {{ $conversation->visitor_name ?: 'Anonymous User' }}
                            </h6>
                            <small class="text-muted ms-2 flex-shrink-0" style="font-size: 0.7rem;">
                                {{ $conversation->last_message_at?->format('H:i') ?? '--:--' }}
                            </small>
                        </div>
                        @if($conversation->visitor_email)
                            <p class="small text-muted mb-1 text-truncate" style="font-size: 0.7rem;">{{ $conversation->visitor_email }}</p>
                        @endif
                        @if($conversation->latestMessage)
                            <p class="small text-secondary mb-0 text-truncate {{ $conversation->unread_count > 0 ? 'fw-semibold' : '' }}" style="font-size: 0.75rem;">
                                {{ Str::limit($conversation->latestMessage->message, 40) }}
                            </p>
                        @else
                            <p class="small text-muted fst-italic mb-0" style="font-size: 0.75rem;">No messages yet</p>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="p-5 text-center">
                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-chat-dots text-muted icon-md"></i>
                </div>
                <h6 class="fw-semibold mb-2">No conversations yet</h6>
                <p class="small text-muted">New messages will appear here</p>
            </div>
        @endforelse
    </div>
</div>
