@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-4">Submit Wikipedia Page URL</h1>

        @if(session('success'))
            <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
                {{  session('success')  }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 text-red-800 p-3 rounded mb-4">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('page_requests.store') }}" method="POST" class="max-w-lg">
            @csrf
            <label for="url" class="block mb-2 font-semibold">Wikipedia Page URL:</label>
            <input type="url" id="url" name="url" placeholder="https://en.wikipedia.org/wiki/Women%27s_high_jump_world_record_progression"  class="w-full p-2 border border-gray-300 rounded mb-4" required>
            <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">Submit</button>
        </form>
    </div>
