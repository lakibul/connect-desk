<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Template Management – ConnectDesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f8f9fa; }
        .page-header { background: #fff; border-bottom: 1px solid #e5e7eb; padding: 1rem 1.5rem; }
        .tpl-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; transition: box-shadow .15s; }
        .tpl-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.08); }
        .badge-active   { background: #d1fae5; color: #065f46; }
        .badge-inactive { background: #fee2e2; color: #991b1b; }
        .content-preview {
            max-height: 56px; overflow: hidden;
            white-space: pre-wrap; font-size: .85rem; color: #6b7280;
            border-left: 3px solid #e5e7eb; padding-left: .75rem; margin-top: .4rem;
        }
        .tpl-name-badge { background: #eff6ff; color: #1d4ed8; font-family: monospace; font-size: .75rem; padding: 2px 8px; border-radius: 4px; }
        textarea { font-family: 'Inter', monospace, sans-serif; }
        .search-box { max-width: 320px; }
        .whatsapp-preview {
            background: #e8f5e9;
            border-radius: 12px 12px 12px 0;
            padding: .75rem 1rem;
            max-width: 380px;
            font-size: .875rem;
            white-space: pre-wrap;
            line-height: 1.5;
            border: 1px solid #c8e6c9;
        }
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
            <a href="{{ route('admin.faqs.index') }}" class="btn btn-sm btn-outline-success">
                <i class="bi bi-question-circle me-1"></i>FAQs
            </a>
        </div>
    </div>
</nav>

<!-- Page Header -->
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
        <h4 class="mb-0 fw-bold"><i class="bi bi-file-text text-primary me-2"></i>Template Management</h4>
        <small class="text-muted">Create and manage WhatsApp message templates. Active templates appear in the send-message dropdown.</small>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tplModal" onclick="openCreateModal()">
        <i class="bi bi-plus-circle me-1"></i>Add Template
    </button>
</div>

<div class="container-fluid px-4 py-4">

    <!-- Alert placeholder -->
    <div id="alertBox"></div>

    <!-- Toolbar -->
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <span class="text-muted small" id="tplCount">{{ $templates->count() }} template{{ $templates->count() !== 1 ? 's' : '' }}</span>
        <input type="text" class="form-control form-control-sm search-box" id="searchInput" placeholder="Search by name or label…">
    </div>

    <!-- Template Grid -->
    <div class="row g-3" id="templateGrid">
        @forelse($templates as $template)
        <div class="col-md-6 col-xl-4 tpl-row" data-search="{{ strtolower($template->name . ' ' . $template->label) }}">
            <div class="tpl-card h-100 p-3" data-tpl-id="{{ $template->id }}">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                    <div class="flex-grow-1 min-w-0">
                        <strong class="d-block text-truncate">{{ $template->label }}</strong>
                        <span class="tpl-name-badge mt-1 d-inline-block">{{ $template->name }}</span>
                    </div>
                    <span class="badge {{ $template->is_active ? 'badge-active' : 'badge-inactive' }} px-2 py-1 toggle-active-btn flex-shrink-0"
                          style="cursor:pointer;" data-tpl-id="{{ $template->id }}"
                          title="Click to toggle">
                        {{ $template->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="content-preview">{{ $template->content }}</div>
                <div class="d-flex gap-2 mt-3">
                    <button class="btn btn-sm btn-outline-primary flex-grow-1"
                            onclick="openEditModal({{ $template->id }})">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </button>
                    <button class="btn btn-sm btn-outline-secondary"
                            onclick="previewTemplate({{ $template->id }})"
                            title="Preview on WhatsApp">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger"
                            onclick="deleteTemplate({{ $template->id }})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="tpl-card p-4 empty-state">
                <i class="bi bi-file-text"></i>
                <p class="mb-2">No templates yet.</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tplModal" onclick="openCreateModal()">
                    Add your first template
                </button>
            </div>
        </div>
        @endforelse
    </div>
</div>

<!-- Create / Edit Modal -->
<div class="modal fade" id="tplModal" tabindex="-1" aria-labelledby="tplModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tplModalLabel"><i class="bi bi-file-text text-primary me-2"></i>Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-none" id="modalError"></div>
                <input type="hidden" id="editTplId" value="">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Template Key (name) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control font-monospace" id="tplName"
                               placeholder="hello_world" maxlength="100">
                        <small class="text-muted">Lowercase, numbers, underscores only. Used in code.</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Display Label <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="tplLabel"
                               placeholder="Hello World" maxlength="255">
                        <small class="text-muted">Shown in the template dropdown.</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Message Content <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="tplContent" rows="6"
                              placeholder="👋 Hello! Welcome to our service...&#10;&#10;Supports WhatsApp formatting: *bold*, _italic_, newlines."></textarea>
                    <small class="text-muted">Supports WhatsApp formatting: <code>*bold*</code>, <code>_italic_</code>, newlines.</small>
                </div>

                <!-- Live WhatsApp preview -->
                <div class="mb-3">
                    <label class="form-label fw-semibold text-muted small">WhatsApp Preview</label>
                    <div class="whatsapp-preview" id="livePreview" style="color:#333;">
                        <em class="text-muted">Start typing message content…</em>
                    </div>
                </div>

                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="tplIsActive" checked>
                    <label class="form-check-label" for="tplIsActive">Active (shown in dropdown)</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveTplBtn" onclick="saveTemplate()">
                    <span id="saveTplSpinner" class="spinner-border spinner-border-sm d-none me-1"></span>
                    <span id="saveTplText">Save Template</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel"><i class="bi bi-whatsapp text-success me-2"></i>WhatsApp Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="background:#e5ddd5; padding:1.5rem; border-radius:4px;">
                <div class="whatsapp-preview" id="previewContent" style="max-width:100%;"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const CSRF       = document.querySelector('meta[name="csrf-token"]').content;
    const tplModal   = new bootstrap.Modal(document.getElementById('tplModal'));
    const prevModal  = new bootstrap.Modal(document.getElementById('previewModal'));

    // ── In-memory template data ───────────────────────────────────────────────
    const tplData = {
        @foreach($templates as $tpl)
        {{ $tpl->id }}: {
            name     : @json($tpl->name),
            label    : @json($tpl->label),
            content  : @json($tpl->content),
            is_active: {{ $tpl->is_active ? 'true' : 'false' }},
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
        document.getElementById('saveTplBtn').disabled = loading;
        document.getElementById('saveTplSpinner').classList.toggle('d-none', !loading);
    }

    function formatPreview(text) {
        return text
            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
            .replace(/\n/g, '<br>')
            .replace(/\*([^*<]+)\*/g, '<strong>$1</strong>')
            .replace(/_([^_<]+)_/g, '<em>$1</em>');
    }

    // ── Live preview while typing ─────────────────────────────────────────────
    document.getElementById('tplContent').addEventListener('input', function() {
        const preview = document.getElementById('livePreview');
        preview.innerHTML = this.value.trim() ? formatPreview(this.value) : '<em class="text-muted">Start typing message content…</em>';
    });

    // ── Open create modal ─────────────────────────────────────────────────────
    function openCreateModal() {
        document.getElementById('tplModalLabel').innerHTML = '<i class="bi bi-plus-circle text-primary me-2"></i>Add Template';
        document.getElementById('editTplId').value  = '';
        document.getElementById('tplName').value    = '';
        document.getElementById('tplLabel').value   = '';
        document.getElementById('tplContent').value = '';
        document.getElementById('tplIsActive').checked = true;
        document.getElementById('livePreview').innerHTML = '<em class="text-muted">Start typing message content…</em>';
        document.getElementById('modalError').classList.add('d-none');
        document.getElementById('saveTplText').textContent = 'Save Template';
    }

    // ── Open edit modal ───────────────────────────────────────────────────────
    function openEditModal(id) {
        const tpl = tplData[id];
        if (!tpl) return;
        document.getElementById('tplModalLabel').innerHTML = '<i class="bi bi-pencil text-primary me-2"></i>Edit Template';
        document.getElementById('editTplId').value     = id;
        document.getElementById('tplName').value       = tpl.name;
        document.getElementById('tplLabel').value      = tpl.label;
        document.getElementById('tplContent').value    = tpl.content;
        document.getElementById('tplIsActive').checked = tpl.is_active;
        document.getElementById('livePreview').innerHTML = formatPreview(tpl.content);
        document.getElementById('modalError').classList.add('d-none');
        document.getElementById('saveTplText').textContent = 'Update Template';
        tplModal.show();
    }

    // ── Preview modal ─────────────────────────────────────────────────────────
    function previewTemplate(id) {
        const tpl = tplData[id];
        if (!tpl) return;
        document.getElementById('previewModalLabel').innerHTML = `<i class="bi bi-whatsapp text-success me-2"></i>${tpl.label}`;
        document.getElementById('previewContent').innerHTML = formatPreview(tpl.content);
        prevModal.show();
    }

    // ── Save (create or update) ───────────────────────────────────────────────
    async function saveTemplate() {
        const id       = document.getElementById('editTplId').value;
        const isEdit   = !!id;
        const name     = document.getElementById('tplName').value.trim();
        const label    = document.getElementById('tplLabel').value.trim();
        const content  = document.getElementById('tplContent').value.trim();
        const isActive = document.getElementById('tplIsActive').checked ? 1 : 0;
        const errorBox = document.getElementById('modalError');

        if (!name || !label || !content) {
            errorBox.textContent = 'Name, Label and Content are required.';
            errorBox.classList.remove('d-none');
            return;
        }
        if (!/^[a-z0-9_]+$/.test(name)) {
            errorBox.textContent = 'Name must contain only lowercase letters, numbers and underscores.';
            errorBox.classList.remove('d-none');
            return;
        }

        errorBox.classList.add('d-none');
        setModalLoading(true);

        try {
            const url    = isEdit ? `/admin/templates/${id}` : '/admin/templates';
            const method = isEdit ? 'PUT' : 'POST';

            const res  = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ name, label, content, is_active: isActive }),
            });
            const data = await res.json();

            if (!res.ok || !data.success) {
                const msgs = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Error saving template.');
                errorBox.textContent = msgs;
                errorBox.classList.remove('d-none');
                return;
            }

            tplModal.hide();
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
    async function deleteTemplate(id) {
        if (!confirm('Delete this template? This cannot be undone.')) return;
        try {
            const res  = await fetch(`/admin/templates/${id}`, {
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
    document.getElementById('templateGrid').addEventListener('click', async function(e) {
        const badge = e.target.closest('.toggle-active-btn');
        if (!badge) return;
        const id  = badge.dataset.tplId;
        const res = await fetch(`/admin/templates/${id}/toggle-active`, {
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

    // ── Search ────────────────────────────────────────────────────────────────
    document.getElementById('searchInput').addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.tpl-row').forEach(row => {
            row.style.display = row.dataset.search.includes(q) ? '' : 'none';
        });
    });

    // ── Auto-generate name from label ─────────────────────────────────────────
    document.getElementById('tplLabel').addEventListener('input', function() {
        if (document.getElementById('editTplId').value) return;
        const slug = this.value.toLowerCase().trim()
            .replace(/[^a-z0-9\s]/g, '')
            .replace(/\s+/g, '_')
            .substring(0, 60);
        document.getElementById('tplName').value = slug;
    });
</script>

</body>
</html>
