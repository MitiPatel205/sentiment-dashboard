<?php

namespace App\Services;

use Phpml\Classification\NaiveBayes;
use Phpml\Dataset\CsvDataset;
use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Tokenization\WordTokenizer;
use Phpml\Pipeline;

class SentimentAnalyzer
{
    protected $pipeline;

    public function __construct()
    {
        // Load training data
        $dataset = new CsvDataset(resource_path('data/training_data.csv'), 1);
        
        // Create ML pipeline
        $this->pipeline = new Pipeline([
            new TokenCountVectorizer(new WordTokenizer()),
        ], new NaiveBayes());
        
        // Train model
        $this->pipeline->train($dataset->getSamples(), $dataset->getTargets());
    }

    public function analyze(string $text): array
    {
        $prediction = $this->pipeline->predict([$text]);
        
        return [
            'sentiment' => $prediction[0],
            'confidence' => number_format($this->pipeline->getEstimator()->getProbability(), 2)
        ];
    }
}
