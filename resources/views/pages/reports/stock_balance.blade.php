@extends('inc.frame')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3>Stock Balance Report</h3>
                <small>{{ \Carbon\Carbon::now()->toFormattedDateString() }}</small>
            </div>

            <div class="card-body">
                <ul class="nav nav-tabs" id="stockTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab == 'overall' ? 'active' : '' }}" 
                           href="{{ route('reports.stock-balance.overall') }}">
                            Overall
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab == 'location' ? 'active' : '' }}" 
                           href="{{ route('reports.stock-balance.location') }}">
                            By Location
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab == 'batch' ? 'active' : '' }}" 
                           href="{{ route('reports.stock-balance.batch') }}">
                            By Batch
                        </a>
                    </li>
                </ul>

                <div class="mt-3">
                    @include('pages.reports.partials.stock_filter', ['route' => 'reports.stock-balance.' . $activeTab])
                    
                    @if($activeTab == 'overall')
                        @include('pages.reports.partials.overall_table')
                    @elseif($activeTab == 'location')
                        @include('pages.reports.partials.location_table')
                    @elseif($activeTab == 'batch')
                        @include('pages.reports.partials.batch_table')
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
$(function() {
    $('.select2').select2();
});
</script>
@endpush