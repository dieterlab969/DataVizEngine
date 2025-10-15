@extends('layouts.app')

@section('content')
    <div class="container mx-auto">
        <div class="row justify-content-center">
            <div class="col-md-10">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card mb-4">
                    <div class="card-header">
                        <h2>Wikipedia Page Request</h2>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>URL:</strong>
                            <a href="{{ $pageRequest->url }}" target="_blank">{{ $pageRequest->url }}</a>
                        </div>

                        <div class="mb-3">
                            <strong>Submitted:</strong>
                            {{ $pageRequest->created_at->format('F j, Y, g:i a') }}
                        </div>

                        <div class="mb-3">
                            <strong>Status:</strong>
                            <span class="badge bg-{{ $pageRequest->status === 'completed' ? 'success' : ($pageRequest->status === 'failed' ? 'danger' : ($pageRequest->status === 'processing' ? 'warning' : 'info')) }}">
                            {{ ucfirst($pageRequest->status) }}
                        </span>
                        </div>

                        <div class="mt-4">
                            <a href="{{ route('page_requests.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to All Requests
                            </a>

                            @if ($pageRequest->status === 'failed' || $pageRequest->status === 'completed' && $pageRequest->visualizations->count() === 0)
                                <form action="{{ route('page_requests.retryVisualization', $pageRequest->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-redo"></i> Retry Processing
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Include the visualization display component -->
                @include('components.visualization-display', ['pageRequest' => $pageRequest])

                @if ($pageRequest->tableData->count() > 0)
                    <div class="card mt-4">
                        <div class="card-header">
                            <h3>Extracted Tables</h3>
                        </div>
                        <div class="card-body">
                            @foreach ($pageRequest->tableData as $index => $table)
                                <div class="table-responsive mb-4">
                                    <h4>Table {{ $index + 1 }}: {{ $table->title ?? 'Untitled Table' }}</h4>
                                    {!! $table->html_content !!}
                                </div>

                                @if (!$loop->last)
                                    <hr>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Optional: Add auto-refresh for pending/processing status
        document.addEventListener('DOMContentLoaded', function() {
            const status = "{{ $pageRequest->status }}";

            if (status === 'pending' || status === 'processing') {
                setTimeout(function() {
                    window.location.reload();
                }, 5000); // Refresh every 5 seconds
            }
        });
    </script>
@endpush
