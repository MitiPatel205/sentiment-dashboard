<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sentiment Analysis Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="sa-container">
    <div class="sa-header">
        <h1>Sentiment Analysis</h1>
        <button id="toggle-dark" class="sa-dark-btn" title="Toggle Dark Mode">🌓</button>
    </div>
    <form action="{{ route('analyze') }}" method="POST" class="sa-form">
        @csrf
        <textarea name="text" rows="3" placeholder="Enter text to analyze..." required>{{ old('text') }}</textarea>
        <button type="submit">Analyze Sentiment</button>
    </form>
    <form action="{{ route('clear.history') }}" method="POST" class="sa-clear-form">
        @csrf
        <button type="submit">Clear History</button>
        <a href="{{ route('export') }}" class="sa-export-btn">Export CSV</a>
    </form>
    <div class="sa-main">
        <!-- Left: History, Filter, Word Cloud -->
        <div class="sa-history">
            <div class="sa-filter">
                <form method="GET" action="{{ route('home') }}">
                    <label for="filter">Filter:</label>
                    <select name="filter" id="filter" onchange="this.form.submit()">
                        <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>All</option>
                        <option value="positive" {{ $filter === 'positive' ? 'selected' : '' }}>Positive</option>
                        <option value="negative" {{ $filter === 'negative' ? 'selected' : '' }}>Negative</option>
                        <option value="neutral" {{ $filter === 'neutral' ? 'selected' : '' }}>Neutral</option>
                    </select>
                </form>
            </div>
            <h2>History</h2>
            @if(count($filtered))
                <ul>
                    @foreach(array_reverse($filtered) as $item)
                        <li class="sa-history-item">
                            <div class="sa-history-date">{{ $item['date'] }}</div>
                            <div class="sa-history-text">{{ $item['text'] }}</div>
                            <div class="sa-history-sentiment {{ 'sa-' . $item['sentiment'] }}">
                                {{ ucfirst($item['sentiment']) }} ({{ $item['confidence'] }}%)
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="sa-history-empty">No messages analyzed yet.</div>
            @endif
            <div class="sa-wordcloud">
                <h3>Word Cloud</h3>
                <div>
                    @foreach(array_slice(collect($words)->sortDesc()->toArray(),0,30) as $word => $count)
                        <span style="font-size: {{ 12 + $count*2 }}px; margin:2px; color: var(--blue-dark); display:inline-block;">
                            {{ $word }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
        <!-- Right: Charts -->
        <div class="sa-charts">
            <div>
                <h2>Sentiment Distribution</h2>
                <canvas id="sentimentChart" width="320" height="220"></canvas>
            </div>
            <div>
                <h2>Trends Over Time</h2>
                <canvas id="trendChart" width="320" height="220"></canvas>
            </div>
        </div>
    </div>
</div>
<script>
    // Dark mode toggle
    document.getElementById('toggle-dark').onclick = function() {
        document.body.classList.toggle('dark-mode');
    };

    // Pie chart
    (() => {
        const style = getComputedStyle(document.documentElement);
        const piePositive = style.getPropertyValue('--pie-positive').trim();
        const pieNegative = style.getPropertyValue('--pie-negative').trim();
        const pieNeutral  = style.getPropertyValue('--pie-neutral').trim();
        const ctx = document.getElementById('sentimentChart').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Positive', 'Negative', 'Neutral'],
                datasets: [{
                    data: [
                        {{ collect($filtered)->where('sentiment','positive')->count() }},
                        {{ collect($filtered)->where('sentiment','negative')->count() }},
                        {{ collect($filtered)->where('sentiment','neutral')->count() }}
                    ],
                    backgroundColor: [piePositive, pieNegative, pieNeutral],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    })();

    // Trend chart
    (() => {
        const ctx = document.getElementById('trendChart').getContext('2d');
        const trend = @json($trend);
        const dates = Object.keys(trend);
        const positives = dates.map(d => trend[d]?.positive ?? 0);
        const negatives = dates.map(d => trend[d]?.negative ?? 0);
        const neutrals  = dates.map(d => trend[d]?.neutral ?? 0);
        const style = getComputedStyle(document.documentElement);
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [
                    {
                        label: 'Positive',
                        data: positives,
                        borderColor: style.getPropertyValue('--pie-positive').trim(),
                        backgroundColor: style.getPropertyValue('--pie-positive').trim(),
                        fill: false
                    },
                    {
                        label: 'Negative',
                        data: negatives,
                        borderColor: style.getPropertyValue('--pie-negative').trim(),
                        backgroundColor: style.getPropertyValue('--pie-negative').trim(),
                        fill: false
                    },
                    {
                        label: 'Neutral',
                        data: neutrals,
                        borderColor: style.getPropertyValue('--pie-neutral').trim(),
                        backgroundColor: style.getPropertyValue('--pie-neutral').trim(),
                        fill: false
                    }
                ]
            },
            options: {
                responsive: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    })();
</script>
</body>
</html>
