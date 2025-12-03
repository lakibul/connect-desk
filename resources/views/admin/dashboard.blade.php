<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard - ConnectDesk</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="{{ asset('css/admin-dashboard.css') }}" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Header -->
    @include('admin.partials.header')

    <!-- Main Content -->
    <div class="py-3 min-vh-100">
        <div class="container-fluid">
            <!-- Main Chat Interface -->
            <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
                <div class="row g-0 chat-container-height">
                    <!-- Conversations Sidebar -->
                    <div class="col-12 col-lg-4 border-end">
                        @include('admin.partials.sidebar')
                    </div>

                    <!-- Chat Area -->
                    <div class="col-12 col-lg-8">
                        @include('admin.partials.chat-area')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script src="{{ asset('js/admin-dashboard.js') }}"></script>
</body>
</html>
