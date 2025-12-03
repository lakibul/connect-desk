<div class="header-gradient py-2 border-bottom">
    <div class="container-fluid">
        <div class="row align-items-center g-2">
            <div class="col-12 col-md-6">
                <h5 class="fw-bold text-primary mb-0">
                    <i class="bi bi-chat-dots-fill me-2" style="font-size: 1.1rem;"></i>ConnectDesk
                </h5>
                <p class="text-muted mb-0" style="font-size: 0.75rem;">
                    <i class="bi bi-phone-fill me-1" style="font-size: 0.7rem;"></i>
                    Unified messaging platform
                </p>
            </div>
            <div class="col-12 col-md-6 text-md-end">
                <div class="d-inline-flex align-items-center gap-2">
                    <div class="badge bg-success-subtle text-success border border-success px-2 py-1" style="font-size: 0.7rem;">
                        <span class="spinner-grow me-1" style="width: 0.5rem; height: 0.5rem;"></span>
                        <span>Active</span>
                    </div>
                    <div class="position-relative">
                        <div class="bg-gradient bg-danger text-white px-2 py-1 rounded-3 shadow-sm">
                            <i class="bi bi-bell-fill me-1" style="font-size: 0.85rem;"></i>
                            <span class="fw-bold" style="font-size: 1rem;" id="total-unread">{{ $totalUnread }}</span>
                            <span class="ms-1" style="font-size: 0.7rem;">New</span>
                        </div>
                        @if($totalUnread > 0)
                        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-warning border border-light rounded-circle pulse-animation" style="width: 0.5rem; height: 0.5rem;"></span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-2 mt-1">
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm h-100 stats-card">
                    <div class="card-body bg-success bg-opacity-10 py-2 px-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-success fw-semibold text-uppercase mb-1" style="font-size: 0.65rem;">WhatsApp</p>
                                <h5 class="fw-bold text-success mb-0" id="whatsapp-count">{{ $conversations->where('platform', 'whatsapp')->count() }}</h5>
                            </div>
                            <div class="bg-success text-white rounded-3 p-2 shadow-sm">
                                <i class="bi bi-whatsapp" style="font-size: 1.1rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm h-100 stats-card">
                    <div class="card-body bg-primary bg-opacity-10 py-2 px-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-primary fw-semibold text-uppercase mb-1" style="font-size: 0.65rem;">Facebook</p>
                                <h5 class="fw-bold text-primary mb-0" id="facebook-count">{{ $conversations->where('platform', 'facebook')->count() }}</h5>
                            </div>
                            <div class="bg-primary text-white rounded-3 p-2 shadow-sm">
                                <i class="bi bi-messenger" style="font-size: 1.1rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm h-100 stats-card">
                    <div class="card-body bg-info bg-opacity-10 py-2 px-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-info fw-semibold text-uppercase mb-1" style="font-size: 0.65rem;">Total Chats</p>
                                <h5 class="fw-bold text-info mb-0" id="total-count">{{ $conversations->count() }}</h5>
                            </div>
                            <div class="bg-gradient bg-info text-white rounded-3 p-2 shadow-sm">
                                <i class="bi bi-chat-left-text-fill" style="font-size: 1.1rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
