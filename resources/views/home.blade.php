@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                <h4 class="mb-sm-0">Home</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Pages</a></li>
                        <li class="breadcrumb-item active">Home</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Welcome Section -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Welcome, {{ Auth::user()->name }}!</h5>
                </div>
                
                <div class="card-body">
                    <p>Your role: {{  Auth::user()->roles->pluck('name')->implode(', ') }}</p>
                    @if(Auth::user()->hasRole('Super Admin'))
                        <p>As an Super Admin, you can manage users, distributors, and other resources from the navigation menu.</p>
                    @elseif(Auth::user()->hasRole('Admin'))
                        <p>As a Distributor, you can view your sales data and collections by visiting the <a href="">Sales Dashboard</a>.</p>
                    @else
                        <p>You have access to basic features. Contact an administrator for additional permissions.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection