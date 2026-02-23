<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>FAQ Management – ConnectDesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f8f9fa; }
        .page-header { background: #fff; border-bottom: 1px solid #e5e7eb; padding: 1rem 1.5rem; }
        .faq-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; transition: box-shadow .15s; }
        .faq-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.08); }
        .faq-number { width: 36px; height: 36px; background: #e8f5e9; color: #2e7d32; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0; }
        .badge-active   { background: #d1fae5; color: #065f46; }
        .badge-inactive { background: #fee2e2; color: #991b1b; }
        .answer-preview { max-height: 60px; overflow: hidden; white-space: pre-wrap; font-size: .85rem; color: #6b7280; }
        .drag-handle { cursor: grab; color: #9ca3af; }
        .drag-handle:active { cursor: grabbing; }
        textarea { font-family: 'Inter', monospace, sans-serif; }
        .empty-state { text-align: center; padding: 4rem 1rem; color: #9ca3af; }
        .empty-state i { font-size: 4rem; display: block; margin-bottom: 1rem; }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm border-bottom">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center" href="{{ route('admin.dashboard') }}">
            <i class="bi bi-chat-dots-fill me-2 text-success"></i>
            <span class="fw-bold">ConnectDesk</span>
        </a>
        <div class="d-flex align-items-center ms-auto gap-2">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Dashboard
            </a>
            <a href="{{ route('admin.templates.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-file-text me-1"></i>Templates
            </a>
        </div>
    </div>
</nav>

<!-- Page Header -->
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-0 fw-bold"><i class="bi bi-question-circle text-success me-2"></i>FAQ Management</h4>
        <small class="text-muted">Manage auto-reply FAQ items sent to WhatsApp users. Changes take effect immediately.</small>
    </div>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#faqModal" onclick="openCreateModal()">
        <i class="bi bi-plus-circle me-1"></i>Add FAQ
    </button>
</div>

<div class="container-fluid px-4 py-4">

    <!-- Alert placeholder -->
    <div id="alertBox"></div>

    <!-- FAQ Count -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <span class="text-muted small" id="faqCount">{{ $faqs->count() }} FAQ{{ $faqs->count() !== 1 ? 's' : '' }} total</span>
        <span class="text-muted small"><i class="bi bi-info-circle me-1"></i>Drag rows to reorder. Reply numbers (1, 2…) auto-update based on order.</span>
    </div>

    <!-- FAQ List -->
    <div id="faqList">
        @forelse($faqs as $index => $faq)
        <div class="faq-card mb-3 p-3" data-faq-id="{{ $faq->id }}">
            <div class="d-flex gap-3 align-items-start">
                <div class="drag-handle pt-1" title="Drag to reorder">
                    <i class="bi bi-grip-vertical fs-5"></i>
                </div>
                <div class="faq-number">{{ $index + 1 }}</div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <strong class="d-block">{{ $faq->question }}</strong>
                            <code class="text-muted small">payload: {{ $faq->payload }}</code>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge {{ $faq->is_active ? 'badge-active' : 'badge-inactive' }} px-2 py-1 toggle-active-btn"
                                  style="cursor:pointer;"
                                  data-faq-id="{{ $faq->id }}"
                                  title="Click to toggle active/inactive">
                                {{ $faq->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            <button class="btn btn-sm btn-outline-primary" onclick="openEditModal({{ $faq->id }})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteFaq({{ $faq->id }})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="answer-preview mt-2">{{ $faq->answer }}</div>
                </div>
            </div>
        </div>
        @empty
        <div class="faq-card p-4 empty-state">
            <i class="bi bi-question-circle"></i>
            <p class="mb-2">No FAQs yet.</p>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#faqModal" onclick="openCreateModal()">
                Add your first FAQ
            </button>
        </div>
        @endforelse
    </div>
</div>

<!-- Create / Edit Modal -->
<div class="modal fade" id="faqModal" tabindex="-1" aria-labelledby="faqModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="faqModalLabel"><i class="bi bi-question-circle text-success me-2"></i>FAQ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-none" id="modalError"></div>

                <input type="hidden" id="editFaqId" value="">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Question <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="faqQuestion" placeholder="e.g. What are your business hours?" maxlength="255">
                    <small class="text-muted">Shown in the FAQ menu sent to users.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Answer <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="faqAnswer" rows="6"
                              placeholder="Type the full answer here.&#10;You can use *bold*, _italic_, and line breaks.&#10;&#10;e.g. 🕐 *Business Hours:*&#10;Monday–Friday: 9 AM – 6 PM"></textarea>
                    <small class="text-muted">Supports WhatsApp formatting: <code>*bold*</code>, <code>_italic_</code>, newlines.</small>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Payload Key <span class="text-danger">*</span></label>
                        <input type="text" class="form-control font-monospace" id="faqPayload"
                               placeholder="faq_business_hours" maxlength="100">
                        <small class="text-muted">Lowercase letters, numbers, underscores only. Used to match button replies.</small>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-semibold">Sort Order</label>
                        <input type="number" class="form-control" id="faqSortOrder" value="0" min="0">
                        <small class="text-muted">Lower = appears first.</small>
                    </div>
                    <div class="col-md-3 mb-3 d-flex flex-column">
                        <label class="form-label fw-semibold">Active</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" id="faqIsActive" checked>
                            <label class="form-check-label" for="faqIsActive">Enabled</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="saveFaqBtn" onclick="saveFaq()">
                    <span id="saveFaqSpinner" class="spinner-border spinner-border-sm d-none me-1"></span>
                    <span id="saveFaqText">Save FAQ</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

<script>
    const CSRF   = document.querySelector('meta[name="csrf-token"]').content;
    const faqModal  = new bootstrap.Modal(document.getElementById('faqModal'));

    // ── In-memory FAQ data (for edit pre-fill) ────────────────────────────────
    const faqData = {
        @foreach($faqs as $faq)
        {{ $faq->id }}: {
            question  : @json($faq->question),
            answer    : @json($faq->answer),
            payload   : @json($faq->payload),
            sort_order: {{ $faq->sort_order }},
            is_active : {{ $faq->is_active ? 'true' : 'false' }},
        },
        @endforeach
    };

    // ── Helpers ───────────────────────────────────────────────────────────────
    function showAlert(msg, type = 'success') {
        const box = document.getElementById('alertBox');
        box.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${msg} <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
        setTimeout(() => { box.innerHTML = ''; }, 4000);
    }

    function setModalLoading(loading) {
        document.getElementById('saveFaqBtn').disabled    = loading;
        document.getElementById('saveFaqSpinner').classList.toggle('d-none', !loading);
    }

    // ── Open create modal ─────────────────────────────────────────────────────
    function openCreateModal() {
        document.getElementById('faqModalLabel').innerHTML = '<i class="bi bi-plus-circle text-success me-2"></i>Add FAQ';
        document.getElementById('editFaqId').value   = '';
        document.getElementById('faqQuestion').value = '';
        document.getElementById('faqAnswer').value   = '';
        document.getElementById('faqPayload').value  = '';
        document.getElementById('faqSortOrder').value = '0';
        document.getElementById('faqIsActive').checked = true;
        document.getElementById('modalError').classList.add('d-none');
        document.getElementById('saveFaqText').textContent = 'Save FAQ';
    }

    // ── Open edit modal ───────────────────────────────────────────────────────
    function openEditModal(id) {
        const faq = faqData[id];
        if (!faq) return;
        document.getElementById('faqModalLabel').innerHTML = '<i class="bi bi-pencil text-primary me-2"></i>Edit FAQ';
        document.getElementById('editFaqId').value      = id;
        document.getElementById('faqQuestion').value    = faq.question;
        document.getElementById('faqAnswer').value      = faq.answer;
        document.getElementById('faqPayload').value     = faq.payload;
        document.getElementById('faqSortOrder').value   = faq.sort_order;
        document.getElementById('faqIsActive').checked  = faq.is_active;
        document.getElementById('modalError').classList.add('d-none');
        document.getElementById('saveFaqText').textContent = 'Update FAQ';
        faqModal.show();
    }

    // ── Save (create or update) ───────────────────────────────────────────────
    async function saveFaq() {
        const id       = document.getElementById('editFaqId').value;
        const isEdit   = !!id;
        const question = document.getElementById('faqQuestion').value.trim();
        const answer   = document.getElementById('faqAnswer').value.trim();
        const payload  = document.getElementById('faqPayload').value.trim();
        const sortOrder = parseInt(document.getElementById('faqSortOrder').value) || 0;
        const isActive = document.getElementById('faqIsActive').checked ? 1 : 0;
        const errorBox = document.getElementById('modalError');

        if (!question || !answer || !payload) {
            errorBox.textContent = 'Question, Answer and Payload are required.';
            errorBox.classList.remove('d-none');
            return;
        }
        if (!/^[a-z0-9_]+$/.test(payload)) {
            errorBox.textContent = 'Payload must contain only lowercase letters, numbers and underscores.';
            errorBox.classList.remove('d-none');
            return;
        }

        errorBox.classList.add('d-none');
        setModalLoading(true);

        try {
            const url    = isEdit ? `/admin/faqs/${id}` : '/admin/faqs';
            const method = isEdit ? 'PUT' : 'POST';

            const res  = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ question, answer, payload, sort_order: sortOrder, is_active: isActive }),
            });
            const data = await res.json();

            if (!res.ok || !data.success) {
                const msgs = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Error saving FAQ.');
                errorBox.textContent = msgs;
                errorBox.classList.remove('d-none');
                return;
            }

            faqModal.hide();
            showAlert(data.message);
            setTimeout(() => window.location.reload(), 800);
        } catch (e) {
            errorBox.textContent = 'Network error. Please try again.';
            errorBox.classList.remove('d-none');
        } finally {
            setModalLoading(false);
        }
    }

    // ── Delete ────────────────────────────────────────────────────────────────
    async function deleteFaq(id) {
        if (!confirm('Delete this FAQ? This cannot be undone.')) return;

        try {
            const res  = await fetch(`/admin/faqs/${id}`, {
                method : 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            });
            const data = await res.json();
            if (!res.ok || !data.success) throw new Error(data.message || 'Delete failed.');
            showAlert(data.message);
            setTimeout(() => window.location.reload(), 600);
        } catch (e) {
            showAlert(e.message, 'danger');
        }
    }

    // ── Toggle active ─────────────────────────────────────────────────────────
    document.getElementById('faqList').addEventListener('click', async function(e) {
        const badge = e.target.closest('.toggle-active-btn');
        if (!badge) return;

        const id  = badge.dataset.faqId;
        const res = await fetch(`/admin/faqs/${id}/toggle-active`, {
            method : 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
        });
        const data = await res.json();
        if (data.success) {
            badge.textContent = data.is_active ? 'Active' : 'Inactive';
            badge.classList.toggle('badge-active',   data.is_active);
            badge.classList.toggle('badge-inactive', !data.is_active);
        }
    });

    // ── Drag-to-reorder ───────────────────────────────────────────────────────
    const sortable = new Sortable(document.getElementById('faqList'), {
        handle    : '.drag-handle',
        animation : 150,
        onEnd     : async function() {
            const cards = document.querySelectorAll('#faqList .faq-card[data-faq-id]');
            const order = [];
            cards.forEach((card, i) => {
                order.push({ id: parseInt(card.dataset.faqId), sort_order: i });
                card.querySelector('.faq-number').textContent = i + 1;
            });

            await fetch('/admin/faqs/reorder', {
                method : 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body   : JSON.stringify({ order }),
            });
        },
    });

    // ── Auto-generate payload from question ───────────────────────────────────
    document.getElementById('faqQuestion').addEventListener('input', function() {
        if (document.getElementById('editFaqId').value) return; // don't overwrite on edit
        const slug = this.value.toLowerCase().trim()
            .replace(/[^a-z0-9\s]/g, '')
            .replace(/\s+/g, '_')
            .substring(0, 50);
        document.getElementById('faqPayload').value = slug ? 'faq_' + slug : '';
    });
</script>

</body>
</html>
