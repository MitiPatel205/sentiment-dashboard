@extends('layout')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-6">Sentiment Analysis</h1>
    
    <form action="{{ route('analyze') }}" method="POST">
        @csrf
        <textarea name="text" rows="4" 
            class="w-full p-2 border rounded mb-4"
            placeholder="Enter text to analyze..."></textarea>
            
        <button type="submit" 
            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            Analyze Sentiment
        </button>
    </form>
</div>
@endsection
