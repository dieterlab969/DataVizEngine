<div class="visualization-container mt-4">
    @if ($pageRequest->status === 'pending')
        <div class="alert alert-info">
            <div class="d-flex align-items-center">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                <div>Your request is queued for processing. This may take a few moments...</div>
            </div>
        </div>
    @elseif ($pageRequest->status === 'processing')
        <div class="alert alert-warning">
            <div class="d-flex align-items-center">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                <div>Processing your Wikipedia page. Please wait...</div>
            </div>
        </div>
    @elseif ($pageRequest->status === 'failed')
        <div class="alert alert-danger">
            <strong>Processing Failed</strong>
            <p>We encountered an error while processing your request. Please try again or contact support if the issue persists.</p>
        </div>
    @elseif ($pageRequest->status === 'completed')
        @if ($pageRequest->visualizations->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Visualization Results</h3>
                </div>
                <div class="card-body">
                    @foreach ($pageRequest->visualizations as $visualization)
                        <div class="visualization-result mb-4">
                            <div class="text-center py-3">
                                <img src="{{ $visualization->image_url }}" alt="Visualization" class="img-fluid visualization-image">
                            </div>

                            <div class="visualization-details mt-3">
                                <div class="card">
                                    <div class="card-header">Data Summary</div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Source:</strong> <a href="{{ $pageRequest->url }}" target="_blank">Wikipedia Page</a></p>
                                                <p><strong>Table:</strong> {{ $visualization->table_title ?? 'Extracted Table' }}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Data Points:</strong> {{ $visualization->data_count ?? 'N/A' }}</p>
                                                <p><strong>Column Used:</strong> {{ $visualization->column_name ?? 'Numeric Column' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center mt-3">
                                <a href="{{ $visualization->image_url }}" download="visualization-{{ $visualization->id }}.png" class="btn btn-primary">
                                    <i class="fas fa-download"></i> Download Visualization
                                </a>
                            </div>

                            @if (!$loop->last)
                                <hr class="my-4">
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="alert alert-warning">
                <strong>No Visualizations Found</strong>
                <p>We processed your Wikipedia page but couldn't find any suitable tables with numeric data to visualize.</p>
            </div>
        @endif
    @endif
</div>

<style>
    .visualization-image {
        max-width: 100%;
        max-height: 600px;
        border: 1px solid #ddd;
        padding: 10px;
        background: #fff;
    }

    .visualization-details {
        text-align: left;
    }

    .visualization-details .card {
        box-shadow: none;
        border: 1px solid rgba(0, 0, 0, 0.125);
    }

    .visualization-details .card-header {
        background-color: #f1f1f1;
        font-weight: 600;
    }
</style>
