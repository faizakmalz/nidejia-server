<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class MonthlyTransaction extends ChartWidget
{
    protected static ?int $sort = 2;
    protected static ?string $heading = 'Monthly Transacntion';

    protected function getData(): array
    {
        $data = Trend::model(Transaction::class)
            ->between(start: now()->startOfMonth(), end: now()->endOfMonth())->perDay()->count(); 
        return [
            //
            'datasets' => [
                [
                    'label'=> 'Transaction Created',
                    'data'=> $data->map(fn(TrendValue $trendValue) => $trendValue->aggregate),
                ]
            ],
            'labels' => $data->map(fn(TrendValue $value) => $value->date )
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    public function getDescription():?string           
    {
        return 'The number Transaction created per month.';
    }
}
