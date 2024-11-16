<?php

namespace App\Filament\Widgets;

use App\Models\Listing;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    private function getPercentage($from, $to) {
        if ($from === 0) {
            return $to > 0 ? 100 : 0; // Avoid division by zero
        }
        return (($to - $from) / $from) * 100;
    }
    
    protected function getStats(): array
    {
        $newListings = Listing::whereMonth('created_at', Carbon::now()->month)
                              ->whereYear('created_at', Carbon::now()->year)
                              ->count();

        $transactions = Transaction::where('status', 'approved')
                                   ->whereMonth('created_at', Carbon::now()->month)
                                   ->whereYear('created_at', Carbon::now()->year);
        
        $prevTransactions = Transaction::where('status', 'approved')
                                       ->whereMonth('created_at', Carbon::now()->subMonth()->month)
                                       ->whereYear('created_at', Carbon::now()->subMonth()->year);

        $transactionCount = $transactions->count();
        $prevTransactionCount = $prevTransactions->count();

        $transactionPercentage = $this->getPercentage($prevTransactionCount, $transactionCount);

        $currentRevenue = $transactions->sum('total_price');
        $previousRevenue = $prevTransactions->sum('total_price');

        $revenuePercentage = $this->getPercentage($previousRevenue, $currentRevenue);

        return [
            Stat::make("New Listings", $newListings),
            Stat::make('This Month Transaction', $transactionCount)
                ->description($transactionPercentage > 0 ? "{$transactionPercentage}% increased" : "{$transactionPercentage}% decreased")
                ->descriptionIcon($transactionPercentage > 0 ? 'heroicon-m-arrow-trending-up' : "heroicon-m-arrow-trending-down")
                ->descriptionColor($transactionPercentage > 0 ? 'success' : 'danger'),
            Stat::make('Revenue of the Month', '$' . number_format($currentRevenue, 2))
                ->description($revenuePercentage > 0 ? "{$revenuePercentage}% increased" : "{$revenuePercentage}% decreased")
                ->descriptionIcon($revenuePercentage > 0 ? 'heroicon-m-arrow-trending-up' : "heroicon-m-arrow-trending-down")
                ->descriptionColor($revenuePercentage > 0 ? 'success' : 'danger'),
        ]; 
    }
}
