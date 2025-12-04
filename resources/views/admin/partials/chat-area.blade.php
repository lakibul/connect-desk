<div class="d-flex flex-column h-100 bg-white">
    <!-- Active Chat -->
    <div id="chat-container" class="d-none" style="min-height: 400px; height: 100%; display: flex !important; flex-direction: column !important;">
        <!-- Chat Header -->
        <div class="p-2 border-bottom bg-light flex-shrink-0">
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
        <div id="messages-container" class="flex-grow-1 overflow-auto p-3 custom-scrollbar bg-body-secondary" style="flex: 1 1 auto; height: calc(100% - 140px); min-height: 300px;">
            <div class="container-fluid">
                <div class="messages-wrapper">
                    <!-- Messages will be loaded here -->
                </div>
            </div>
        </div>

        <!-- Reply Input - Always Visible When Chat is Active -->
        <div class="p-3 border-top bg-white message-input-area flex-shrink-0" style="border-top: 2px solid #e9ecef !important; background: linear-gradient(to bottom, #f8f9fa, #ffffff) !important; min-height: 80px; position: relative; z-index: 1000;">
            <form id="reply-form" onsubmit="sendReply(event)">
                <input type="hidden" id="conversation-id" value="">
                <div class="d-flex gap-2 align-items-center">
                    <button type="button" class="btn btn-light rounded-circle shadow-sm" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-paperclip text-secondary" style="font-size: 0.9rem;"></i>
                    </button>
                    <div class="flex-grow-1">
                        <input type="text"
                               id="reply-input"
                               class="form-control rounded-pill border-2"
                               placeholder="Type your message here..."
                               style="font-size: 0.9rem; padding: 0.75rem 1.25rem; border-color: #dee2e6 !important; box-shadow: 0 1px 3px rgba(0,0,0,0.1);"
                               onkeydown="handleTextareaKeydown(event)"
                               required />
                    </div>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 shadow-sm" style="font-weight: 600;" id="send-btn">
                        <span class="me-1" style="font-size: 0.85rem;">Send</span>
                        <i class="bi bi-send-fill" style="font-size: 0.8rem;"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- No Conversation Selected -->
    <div id="no-conversation" class="d-flex flex-column h-100 bg-light">
        <div class="flex-grow-1 d-flex align-items-center justify-content-center">
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

        <!-- Test Input Area (Disabled) - for debugging -->
        <div class="p-3 border-top bg-white" style="border-top: 2px solid #e9ecef !important; background: linear-gradient(to bottom, #f8f9fa, #ffffff) !important; min-height: 80px;">
            <form>
                <div class="d-flex gap-2 align-items-center">
                    <button type="button" class="btn btn-light rounded-circle shadow-sm" disabled style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-paperclip text-secondary" style="font-size: 0.9rem;"></i>
                    </button>
                    <div class="flex-grow-1">
                        <input type="text"
                               class="form-control rounded-pill border-2"
                               placeholder="Select a conversation to start messaging..."
                               style="font-size: 0.9rem; padding: 0.75rem 1.25rem; border-color: #dee2e6 !important; box-shadow: 0 1px 3px rgba(0,0,0,0.1);"
                               disabled />
                    </div>
                    <button type="button" class="btn btn-secondary rounded-pill px-4 py-2 shadow-sm" disabled style="font-weight: 600;">
                        <span class="me-1" style="font-size: 0.85rem;">Send</span>
                        <i class="bi bi-send-fill" style="font-size: 0.8rem;"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
