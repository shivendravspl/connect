<!-- resources/views/approvals/view-checklist.blade.php (for approved status, read-only) -->
@extends('layouts.app')

@section('content')
    <h1>Checklist for Approved Application: {{ $application->id }}</h1>

    <table class="table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($appChecklists as $checklist)
                <tr>
                    <td>{{ $checklist->item_name }} {{ $checklist->is_custom ? '(Custom)' : '' }}</td>
                    <td>{{ $checklist->is_checked ? 'Checked' : 'Not Checked' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection