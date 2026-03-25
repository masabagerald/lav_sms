<!DOCTYPE html>
<html>
<head>
    <title>License Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow-lg border-0">
        <div class="card-body">

            <h3 class="mb-4">🔐 License Management</h3>

            {{-- Alerts --}}
            @if(session('license_error'))
                <div class="alert alert-danger">{{ session('license_error') }}</div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @php
                $licensePath = storage_path('app/license/license.json');
                $keyPath = storage_path('app/license/public.pem');

                $licenseExists = file_exists($licensePath);
                $keyExists = file_exists($keyPath);

                $licenseData = null;

                if($licenseExists){
                    try {
                        $service = app(\App\Services\LicenseService::class)->validate();
                        if($service['valid']){
                            $licenseData = $service['data'];
                        }
                    } catch (\Exception $e) {}
                }
            @endphp

            {{-- CURRENT STATUS --}}
            <div class="mb-4">
                <h5>Current Status</h5>

                @if($licenseExists && $keyExists)
                    <span class="badge bg-success">Active</span>

                    @if($licenseData)
                        <div class="mt-2 text-muted small">
                            <div><strong>Client:</strong> {{ $licenseData['client'] }}</div>
                            <div><strong>Expires:</strong> {{ \Carbon\Carbon::parse($licenseData['expires_at'])->format('d M Y') }}</div>
                            <div><strong>Domain:</strong> {{ $licenseData['domain'] }}</div>
                        </div>
                    @endif

                @else
                    <span class="badge bg-danger">Not Configured</span>
                @endif
            </div>

            <hr>

            {{-- EXISTING FILES --}}
            <div class="mb-4">
                <h5>Existing Files</h5>

                <ul class="list-group">

                    {{-- License --}}
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        License File
                        @if($licenseExists)
                            <span>
                                <span class="badge bg-success me-2">Exists</span>
                                <form method="POST" action="{{ route('license.delete') }}" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="type" value="license">
                                    <button class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </span>
                        @else
                            <span class="badge bg-secondary">Missing</span>
                        @endif
                    </li>

                    {{-- Public Key --}}
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Public Key
                        @if($keyExists)
                            <span>
                                <span class="badge bg-success me-2">Exists</span>
                                <form method="POST" action="{{ route('license.delete') }}" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="type" value="key">
                                    <button class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </span>
                        @else
                            <span class="badge bg-secondary">Missing</span>
                        @endif
                    </li>

                </ul>
            </div>

            <hr>

            {{-- UPLOAD SECTION --}}
            <div class="row">

                {{-- Upload License --}}
                <div class="col-md-6">
                    <h5>Upload License</h5>
                    <form method="POST" action="{{ route('license.upload.post') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <input type="file" name="license" class="form-control" required>
                        </div>

                        <button class="btn btn-primary w-100">
                            Upload License
                        </button>
                    </form>
                </div>

                {{-- Upload Public Key --}}
                <div class="col-md-6">
                    <h5>Upload Public Key</h5>
                    <form method="POST" action="{{ route('license.key.upload') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <input type="file" name="key" class="form-control" required>
                        </div>

                        <button class="btn btn-dark w-100">
                            Upload Public Key
                        </button>
                    </form>
                </div>

            </div>

        </div>
    </div>

</div>

</body>
</html>