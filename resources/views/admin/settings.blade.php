<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Settings - ConnectDesk Admin</title>

    <!-- External Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f7fa;
        }
        .settings-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 24px;
        }
        .card-header {
            background: white;
            border-bottom: 1px solid #e9ecef;
            padding: 20px 24px;
            font-weight: 600;
            font-size: 18px;
        }
        .card-body {
            padding: 24px;
        }
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        .status-connected {
            background-color: #d4edda;
            color: #155724;
        }
        .status-disconnected {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <!-- Navigation Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('admin.dashboard') }}">
                <i class="bi bi-chat-dots-fill me-2"></i>
                <span class="fw-bold">ConnectDesk</span>
            </a>

            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                        <div class="avatar-circle me-2" style="width:32px;height:32px;background:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-person-fill" style="color:#0d6efd;"></i>
                        </div>
                        <span>{{ auth()->user()->name ?? 'Admin' }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="bi bi-house me-2"></i>Dashboard</a></li>
                        <li><a class="dropdown-item active" href="{{ route('admin.settings') }}"><i class="bi bi-gear me-2"></i>Settings</a></li>
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

    <div class="settings-container">
        <h2 class="mb-4">Admin Settings</h2>

        <!-- WhatsApp Configuration Card -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-whatsapp me-2"></i>Twilio WhatsApp API</span>
                @if(!empty($admin->twilio_account_sid) && !empty($admin->twilio_auth_token))
                    <span class="status-badge status-connected">
                        <i class="bi bi-check-circle me-1"></i>Connected
                    </span>
                @else
                    <span class="status-badge status-disconnected">
                        <i class="bi bi-x-circle me-1"></i>Not Connected
                    </span>
                @endif
            </div>
            <div class="card-body">
                <!-- Twilio Sandbox Info -->
                <div class="alert alert-info border-info" style="border-left: 4px solid #0dcaf0;">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-info-circle-fill me-3" style="font-size: 24px;"></i>
                        <div>
                            <h6 class="alert-heading mb-2">Twilio WhatsApp Sandbox</h6>
                            <p class="mb-2">
                                <strong>For testing and development, use Twilio's WhatsApp Sandbox:</strong>
                            </p>
                            <ol class="mb-2">
                                <li>Join the sandbox by sending "join &lt;your-code&gt;" to the Twilio WhatsApp number</li>
                                <li>Use sandbox number: <strong>+1 415 523 8886</strong></li>
                                <li>For production, upgrade to an approved Twilio phone number</li>
                            </ol>
                            <p class="mb-2">
                                <strong>Production Mode:</strong> To send to any WhatsApp number, request a Twilio approved sender phone number.
                            </p>
                            <a href="{{ asset('TWILIO_WHATSAPP_SETUP_GUIDE.md') }}" class="btn btn-sm btn-info" target="_blank">
                                <i class="bi bi-book me-1"></i> Read Twilio Setup Guide
                            </a>
                            <a href="https://console.twilio.com/" class="btn btn-sm btn-outline-info" target="_blank">
                                <i class="bi bi-gear me-1"></i> Go to Twilio Console
                            </a>
                        </div>
                    </div>
                </div>

                <div class="alert alert-success">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Quick Setup:</strong> Configure your Twilio credentials to send WhatsApp messages from this admin account.
                </div>

                <form id="whatsappSettingsForm">
                    <div class="mb-3">
                        <label for="phone_number" class="form-label">Your Phone Number</label>
                        <input type="text" class="form-control" id="phone_number" name="phone_number"
                               value="{{ $admin->phone_number }}"
                               placeholder="e.g., +8801XXXXXXXXX">
                        <small class="form-text text-muted">Your WhatsApp phone number (E.164 format with +country code)</small>
                    </div>

                    <div class="mb-3">
                        <label for="twilio_account_sid" class="form-label">Twilio Account SID</label>
                        <input type="text" class="form-control" id="twilio_account_sid" name="twilio_account_sid"
                               value="{{ $admin->twilio_account_sid }}"
                               placeholder="e.g., ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" required>
                        <small class="form-text text-muted">From Twilio Console > Account Info</small>
                    </div>

                    <div class="mb-3">
                        <label for="twilio_auth_token" class="form-label">Twilio Auth Token</label>
                        <input type="password" class="form-control" id="twilio_auth_token" name="twilio_auth_token"
                               value="{{ $admin->twilio_auth_token }}"
                               placeholder="Paste your auth token here..." required>
                        <small class="form-text text-muted">From Twilio Console > Account Info (click to reveal)</small>
                    </div>

                    <div class="mb-3">
                        <label for="twilio_whatsapp_from" class="form-label">Twilio WhatsApp From Number</label>
                        <input type="text" class="form-control" id="twilio_whatsapp_from" name="twilio_whatsapp_from"
                               value="{{ $admin->twilio_whatsapp_from }}"
                               placeholder="+14155238886" required>
                        <small class="form-text text-muted">Sandbox: +14155238886 | Production: Your approved Twilio number</small>
                    </div>

                    <div class="alert alert-warning">
                        <strong>Setup Instructions:</strong>
                        <ol class="mb-0 mt-2">
                            <li>Go to <a href="https://console.twilio.com/" target="_blank">Twilio Console</a></li>
                            <li>Navigate to Account > Account Info</li>
                            <li>Copy your Account SID and Auth Token</li>
                            <li>For testing, use Twilio Sandbox number: <strong>+14155238886</strong></li>
                            <li>Join sandbox by sending "join &lt;your-code&gt;" from your WhatsApp</li>
                            <li>Configure webhook in Twilio Console > Messaging > WhatsApp Sandbox Settings</li>
                            <li>Paste all values above and save</li>
                        </ol>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Save Twilio Credentials
                    </button>
                </form>

                <div class="alert alert-success mt-3 d-none" id="successAlert">
                    <i class="bi bi-check-circle me-2"></i>
                    <span id="successMessage">Settings saved successfully!</span>
                </div>
                <div class="alert alert-danger mt-3 d-none" id="errorAlert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <span id="errorMessage">Failed to save settings.</span>
                </div>
            </div>
        </div>

        <!-- Account Information Card -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-person me-2"></i>Account Information
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Name:</strong>
                        <p class="mb-0">{{ $admin->name }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Email:</strong>
                        <p class="mb-0">{{ $admin->email }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Role:</strong>
                        <p class="mb-0"><span class="badge bg-primary">Admin</span></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Phone Number:</strong>
                        <p class="mb-0">{{ $admin->phone_number ?? 'Not set' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        document.getElementById('whatsappSettingsForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

            const formData = {
                twilio_account_sid: document.getElementById('twilio_account_sid').value.trim(),
                twilio_auth_token: document.getElementById('twilio_auth_token').value.trim(),
                twilio_whatsapp_from: document.getElementById('twilio_whatsapp_from').value.trim(),
                phone_number: document.getElementById('phone_number').value.trim(),
            };

            try {
                const response = await fetch('/admin/api/settings/whatsapp', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    showAlert('success', data.message || 'Settings saved successfully!');
                    // Reload page after 1 second to show updated status
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showAlert('error', data.message || 'Failed to save settings.');
                }
            } catch (error) {
                showAlert('error', 'An error occurred while saving settings.');
                console.error(error);
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });

        function showAlert(type, message) {
            const successAlert = document.getElementById('successAlert');
            const errorAlert = document.getElementById('errorAlert');

            if (type === 'success') {
                document.getElementById('successMessage').textContent = message;
                successAlert.classList.remove('d-none');
                errorAlert.classList.add('d-none');
                setTimeout(() => {
                    successAlert.classList.add('d-none');
                }, 5000);
            } else {
                document.getElementById('errorMessage').textContent = message;
                errorAlert.classList.remove('d-none');
                successAlert.classList.add('d-none');
                setTimeout(() => {
                    errorAlert.classList.add('d-none');
                }, 5000);
            }
        }
    </script>
</body>
</html>
