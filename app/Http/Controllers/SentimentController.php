<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Sentiment\Analyzer;
use Illuminate\Support\Facades\Response;

class SentimentController extends Controller
{
    public function index(Request $request)
    {
        $history = session('history', []);
        $filter = $request->get('filter', 'all');
        $filtered = $filter === 'all'
            ? $history
            : array_filter($history, fn($h) => $h['sentiment'] === $filter);

        // For trend chart
        $trend = [];
        foreach ($history as $item) {
            $date = substr($item['date'], 0, 10);
            $sent = $item['sentiment'];
            $trend[$date][$sent] = ($trend[$date][$sent] ?? 0) + 1;
        }

        // For word cloud
        $words = [];
        foreach ($filtered as $item) {
            foreach (explode(' ', strtolower(preg_replace('/[^\w\s]/', '', $item['text']))) as $word) {
                if (strlen($word) > 3) $words[$word] = ($words[$word] ?? 0) + 1;
            }
        }

        return view('welcome', [
            'history' => $history,
            'filtered' => $filtered,
            'trend' => $trend,
            'words' => $words,
            'filter' => $filter,
        ]);
    }

    public function analyze(Request $request)
    {
        $request->validate(['text' => 'required|string|max:500']);

        $analyzer = new Analyzer();
        $result = $analyzer->getSentiment($request->text);

        $sentiment = 'neutral';
        if ($result['pos'] > $result['neg'] && $result['pos'] > $result['neu']) {
            $sentiment = 'positive';
        } elseif ($result['neg'] > $result['pos'] && $result['neg'] > $result['neu']) {
            $sentiment = 'negative';
        }

        $history = session('history', []);
        $history[] = [
            'text' => $request->text,
            'sentiment' => $sentiment,
            'confidence' => round(max($result['pos'], $result['neg'], $result['neu']) * 100, 2),
            'date' => now()->format('Y-m-d H:i:s'),
        ];
        session(['history' => $history]);

        return redirect('/')->with([
            'last_text' => $request->text,
            'last_sentiment' => $sentiment,
            'last_confidence' => round(max($result['pos'], $result['neg'], $result['neu']) * 100, 2),
        ]);
    }

    public function clearHistory()
    {
        session()->forget('history');
        return redirect('/');
    }

    public function export()
    {
        $history = session('history', []);
        $csv = "Date,Text,Sentiment,Confidence\n";
        foreach ($history as $row) {
            $csv .= "\"{$row['date']}\",\"{$row['text']}\",{$row['sentiment']},{$row['confidence']}%\n";
        }
        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sentiment_history.csv"',
        ]);
    }
}
