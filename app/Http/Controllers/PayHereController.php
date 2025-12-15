<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\PaymentSale;
use App\Models\PaymentWithCreditCard;
use App\Models\Account;
use Carbon\Carbon;

class PayHereController extends Controller
{
    /**
     * Handle PayHere payment notification
     */
  public function handleNotification(Request $request)
{
    \Log::info('PayHere Notification Received', $request->all());

    $merchant_id = $request->merchant_id;
    $order_id = $request->order_id;
    $payhere_amount = $request->payhere_amount;
    $payhere_currency = $request->payhere_currency;
    $status_code = $request->status_code;
    $md5sig = $request->md5sig;
    $payhere_payment_id = $request->payment_id;

    // ✅ Get sale_id from custom_1 instead of parsing order_id
    $sale_id = $request->custom_1;

    $merchant_secret = env('PAYHERE_MERCHANT_SECRET');

    // Verify MD5 signature
    $local_md5sig = strtoupper(
       md5(
           $merchant_id .
           $order_id .
           number_format((float)$payhere_amount, 2, '.', '') .
           $payhere_currency .
           $status_code .
           strtoupper(md5($merchant_secret))
       )
   );

    // Log for debugging
    \Log::info('Hash Validation', [
        'received_hash' => $md5sig,
        'calculated_hash' => $local_md5sig,
        'merchant_id' => $merchant_id,
        'order_id' => $order_id,
        'amount' => $payhere_amount,
        'formatted_amount' => number_format((float)$payhere_amount, 2, '.', ''),
        'currency' => $payhere_currency,
        'status_code' => $status_code
    ]);


    if ($local_md5sig !== $md5sig) {
        \Log::error('PayHere MD5 signature mismatch', [
            'received' => $md5sig,
            'calculated' => $local_md5sig,
            'request_data' => $request->all()
        ]);
        return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 400);
    }

    $sale = Sale::find($sale_id);

    if (!$sale) {
        \Log::error('Sale not found', ['sale_id' => $sale_id, 'order_id' => $order_id]);
        return response()->json(['status' => 'error', 'message' => 'Sale not found'], 404);
    }

    if ($status_code == 2) {
        // Payment successful
        \DB::transaction(function () use ($sale, $payhere_payment_id, $order_id, $request) {

            $pendingPayment = PaymentSale::where('sale_id', $sale->id)
                ->where('payment_method_id', 1)
                ->where('status', 'pending')
                ->first();

            if ($pendingPayment) {
                $pendingPayment->update(['status' => 'paid']);

                if ($pendingPayment->account_id) {
                    $account = Account::find($pendingPayment->account_id);
                    if ($account) {
                        $account->update(['balance' => $account->balance + $pendingPayment->montant]);
                    }
                }

                PaymentWithCreditCard::create([
                    'customer_id' => $sale->client_id,
                    'payment_id' => $pendingPayment->id,
                    'payhere_payment_id' => $payhere_payment_id,
                    'payhere_order_id' => $order_id,
                    'card_holder_name' => $request->card_holder_name ?? null,
                    'card_no' => $request->card_no ?? null,
                    'card_expiry' => $request->card_expiry ?? null,
                    'payment_method' => $request->method ?? 'CARD',
                ]);
            }

            $totalPaid = PaymentSale::where('sale_id', $sale->id)->where('status', 'paid')->sum('montant');
            $due = $sale->GrandTotal - $totalPaid;

            $sale->update([
                'paid_amount' => $totalPaid,
                'payment_statut' => $due <= 0 ? 'paid' : ($due < $sale->GrandTotal ? 'partial' : 'unpaid')
            ]);
        });

        \Log::info('Payment successful', ['sale_id' => $sale->id]);

    } elseif ($status_code == -1) {
        $pendingPayment = PaymentSale::where('sale_id', $sale->id)
            ->where('payment_method_id', 1)
            ->where('status', 'pending')
            ->first();

        if ($pendingPayment) {
            $pendingPayment->update(['status' => 'cancelled']);
        }
    } elseif ($status_code == -2) {
        $pendingPayment = PaymentSale::where('sale_id', $sale->id)
            ->where('payment_method_id', 1)
            ->where('status', 'pending')
            ->first();

        if ($pendingPayment) {
            $pendingPayment->update(['status' => 'failed']);
        }
    }

    return response()->json(['status' => 'success'], 200);
}
    /**
     * Handle return from PayHere (after payment attempt)
     */
    public function handleReturn(Request $request)
    {
        // Redirect to POS with success message
        // The actual payment verification happens via notify URL
        // return redirect('/app/pos?payment=processing&message=' . urlencode('Payment is being processed. Please wait...'));
        // Get order_id if present
        return redirect('/app/pos')->with([
            'payment_status' => 'processing',
            'message' => 'Payment is being processed. Please wait...'
        ]);
    }

    /**
     * Handle cancel from PayHere (user cancelled payment)
     */
    public function handleCancel(Request $request)
    {
        return redirect('/app/pos?payment=cancelled&message=' . urlencode('Payment was cancelled.'));
    }
}
