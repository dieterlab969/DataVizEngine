@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-4">Submitted Wikipedia Pages</h1>

        <a href="{{ route('page_requests.create') }}"
           class="inline-block mb-4 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
            New Submission
        </a>

        @if($requests->isEmpty())
            <p>No submissions yet.</p>
        @else
            <table class="w-full border-collapse border border-gray-200">
                <thead>
                <tr class="bg-gray-100">
                    <th class="border border-gray-300 px-4 py-2 text-left">ID</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">URL</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Status</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Submitted At</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($requests as $request)
                    <tr>
                        <td class="border border-gray-300 px-4 py-2">{{ $request->id }}</td>
                        <td class="border border-gray-300 px-4 py-2 break-words" style="max-width: 300px;">
                            <a href="{{ $request->url }}" target="_blank" class="text-blue-600 underline">
                                {{ $request->url }}
                            </a>
                        </td>
                        <td class="border border-gray-300 px-4 py-2">{{ ucfirst($request->status) }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $request->created_at->format('Y-m-d H:i') }}</td>
                        <td class="border border-gray-300 px-4 py-2">
                            <a href="{{ route('page_requests.show', $request->id) }}"
                               class="text-indigo-600 hover:underline">View</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
