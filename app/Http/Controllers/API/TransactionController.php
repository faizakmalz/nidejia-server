<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Transaction;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Transaction\Store;

class TransactionController extends Controller
{
    //
    private function _fullyBookedChecker(Store $request){
        $listing = Listing::find( $request->listing_id );
        $runningTransaction = Transaction::whereListingId( $listing->id )
        ->whereNot("status", 'cancelled' )
        ->where(function($query) use ($request){
            $query->whereBetween('start_date', [
                $request->start_date, 
                $request->end_date,
            ])->orWhereBetween('end_date', [
                $request->start_date, 
                $request->end_date,
            ])->orWhere(function($subquery) use ($request){
                $subquery->where('start_date','<', $request->start_date)->where('end_date', '>', $request->end_date);
            });
        
        })->count();

        if($runningTransaction >= $listing->max_person){
            throw new HttpResponseException(response()->json([
                'message' => 'listing is fully book',
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
      
        }

        return true;
    }

    public function isAvailable(Store $request){
        $this->_fullyBookedChecker($request);
        return response()->json([
            'success' => true,
            'message' => 'Listing is ready'
        ]);
    }
}
