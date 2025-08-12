@extends('layouts.developer')

@section('title', 'System Logs')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">System Logs</h1>
                        <p class="text-sm text-gray-600">View and monitor system logs</p>
                    </div>
                    <a href="{{ route('developer.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        ← Back to Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Log Viewer -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Log File: {{ $logFile }}</h3>
                    <div class="flex space-x-2">
                        <select onchange="changeLogFile(this.value)" class="rounded-md border-gray-300 text-sm">
                            <option value="laravel.log" {{ $logFile == 'laravel.log' ? 'selected' : '' }}>Laravel Log</option>
                            <option value="daily.log" {{ $logFile == 'daily.log' ? 'selected' : '' }}>Daily Log</option>
                        </select>
                        <button onclick="refreshLogs()" class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                            <i class="fas fa-refresh mr-1"></i>
                            Refresh
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                @if(empty($logs))
                    <div class="text-center py-8">
                        <i class="fas fa-file-alt text-4xl text-gray-400 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No logs found</h3>
                        <p class="text-gray-500">The log file is empty or doesn't exist yet.</p>
                    </div>
                @else
                    <div class="bg-gray-900 rounded-lg p-4 max-h-96 overflow-y-auto">
                        <code class="text-green-400 text-sm">
                            @foreach($logs as $line)
                                <div class="mb-1 {{ strpos($line, 'ERROR') !== false ? 'text-red-400' : (strpos($line, 'WARNING') !== false ? 'text-yellow-400' : 'text-green-400') }}">
                                    {{ trim($line) }}
                                </div>
                            @endforeach
                        </code>
                    </div>
                    
                    <div class="mt-4 text-sm text-gray-500 text-center">
                        Showing last {{ count($logs) }} lines
                    </div>
                @endif
            </div>
        </div>

        <!-- Log Statistics -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100 text-red-600">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Errors</h3>
                        <p class="text-2xl font-bold text-red-600">
                            {{ count(array_filter($logs, function($line) { return strpos($line, 'ERROR') !== false; })) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Warnings</h3>
                        <p class="text-2xl font-bold text-yellow-600">
                            {{ count(array_filter($logs, function($line) { return strpos($line, 'WARNING') !== false; })) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Info</h3>
                        <p class="text-2xl font-bold text-blue-600">
                            {{ count(array_filter($logs, function($line) { return strpos($line, 'INFO') !== false; })) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function changeLogFile(fileName) {
    window.location.href = '{{ route("developer.logs") }}?file=' + fileName;
}

function refreshLogs() {
    location.reload();
}
</script>
@endsection