<div class="d-flex flex-column h-100 bg-white">
    <!-- Active Chat -->
    <div id="chat-container" class="d-none flex-column h-100">
        <!-- Chat Header -->
        <div class="p-2 border-bottom bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <div id="chat-platform-icon" class="rounded-circle bg-white shadow-sm" style="width: 38px; height: 38px; display: flex; align-items: center; justify-content: center;">
                        <!-- Icon will be updated via JS -->
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0" id="chat-title" style="font-size: 0.9rem;">User Name</h6>
                        <div class="d-flex align-items-center gap-1 mt-1">
                            <small class="text-muted" id="chat-email" style="font-size: 0.7rem;"></small>
                            <span class="bg-success rounded-circle" style="width: 6px; height: 6px;"></span>
                            <small class="text-muted" style="font-size: 0.65rem;">Active now</small>
                        </div>
                    </div>
                </div>
                <span class="badge bg-primary-subtle text-primary border border-primary px-2 py-1" style="font-size: 0.65rem;" id="chat-platform">
                    <!-- Platform will be shown here -->
                </span>
            </div>
        </div>

        <!-- Messages Container -->
        <div id="messages-container" class="flex-grow-1 overflow-auto p-3 custom-scrollbar bg-body-secondary">
            <div class="container-fluid">
                <div class="messages-wrapper">
                    <!-- Messages will be loaded here -->
                </div>
            </div>
        </div>

        <!-- Reply Input -->
        <div class="p-2 border-top bg-white message-input-area">
            <form id="reply-form" onsubmit="sendReply(event)">
                <input type="hidden" id="conversation-id" value="">
                <div class="d-flex gap-2 align-items-center">
                    <button type="button" class="btn btn-sm btn-light rounded-circle p-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-paperclip" style="font-size: 0.85rem;"></i>
                    </button>
                    <div class="flex-grow-1">
                        <input type="text"
                               id="reply-input"
                               class="form-control form-control-sm rounded-pill border-secondary"
                               placeholder="Type your message here..."
                               style="font-size: 0.85rem; padding: 0.5rem 1rem;"
                               onkeydown="handleTextareaKeydown(event)" />
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 py-2">
                        <span class="me-1" style="font-size: 0.8rem;">Send</span>
                        <i class="bi bi-send-fill" style="font-size: 0.75rem;"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- No Conversation Selected -->
    <div id="no-conversation" class="d-flex align-items-center justify-content-center h-100 bg-light">
        <div class="text-center p-4">
            <div class="mb-3">
                <i class="bi bi-chat-dots text-primary opacity-25" style="font-size: 5rem;"></i>
            </div>
            <h5 class="fw-bold mb-2">Welcome to ConnectDesk</h5>
            <p class="text-muted small" style="max-width: 400px;">
                Select a conversation from the sidebar to start managing customer messages.
            </p>
        </div>
    </div>
</div>
