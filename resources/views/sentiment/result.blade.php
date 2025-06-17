@extends('layout')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-6">Analysis Result</h1>
    
    <div class="mb-4">
        <p class="font-semibold">Original Text:</p>
        <p class="bg-gray-50 p-4 rounded">{{ $text }}</p>
    </div>
    
    <div class="mb-4">
        <p class="font-semibold">Sentiment:</p>
        <p class="p-4 rounded 
            @if($sentiment === 'positive') bg-green-100 text-green-800
            @elseif($sentiment === 'negative') bg-red-100 text-red-800
            @else bg-gray-100 @endif">
            {{ ucfirst($sentiment) }} ({{ $confidence }}% confidence)
        </p>
    </div>
    
    <a href="{{ route('home') }}" 
        class="text-blue-500 hover:text-blue-700">
        ← Analyze another text
    </a>
</div>
@endsection
