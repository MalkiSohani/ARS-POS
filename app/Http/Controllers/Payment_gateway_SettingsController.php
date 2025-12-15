<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;

class Payment_gateway_SettingsController extends Controller
{
    //-------------- Get Payment Gateway ---------------\\
    public function Get_payment_gateway(Request $request)
{
    $this->authorizeForUser($request->user('api'), 'payment_gateway', Setting::class);
    Artisan::call('config:cache');
    Artisan::call('config:clear');

    $item['payhere_merchant_id'] = env('PAYHERE_MERCHANT_ID');
    $item['payhere_merchant_secret'] = ''; // Never send secret to frontend
    $item['payhere_mode'] = env('PAYHERE_MODE', 'sandbox');
    $item['deleted'] = false;

    return response()->json(['gateway' => $item], 200);
}

    //-------------- Update Payment Gateway ---------------\\
    public function Update_payment_gateway(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'payment_gateway', Setting::class);

        if($request['deleted'] == 'true'){
            $this->setEnvironmentValue([
                'PAYHERE_MERCHANT_ID' => '',
                'PAYHERE_MERCHANT_SECRET' => '',
                'PAYHERE_MODE' => 'sandbox',
            ]);
        } else {
        $this->setEnvironmentValue([
            'PAYHERE_MERCHANT_ID' => $request['payhere_merchant_id'] ?? env('PAYHERE_MERCHANT_ID'),
            'PAYHERE_MERCHANT_SECRET' => $request['payhere_merchant_secret'] ?? env('PAYHERE_MERCHANT_SECRET'),
            'PAYHERE_MODE' => $request['payhere_mode'] ?? 'sandbox',
        ]);
}

        Artisan::call('config:cache');
        Artisan::call('config:clear');

        return response()->json(['success' => true]);
    }


    //-------------- Set Environment Value ---------------\\

    public function setEnvironmentValue(array $values)
    {
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);
        $str .= "\r\n";
        if (count($values) > 0) {
            foreach ($values as $envKey => $envValue) {

                $keyPosition = strpos($str, "$envKey=");
                $endOfLinePosition = strpos($str, "\n", $keyPosition);
                $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);

                if (is_bool($keyPosition) && $keyPosition === false) {
                    // variable doesnot exist
                    $str .= "$envKey=$envValue";
                    $str .= "\r\n";
                } else {
                    // variable exist
                    $str = str_replace($oldLine, "$envKey=$envValue", $str);
                }
            }
        }

        $str = substr($str, 0, -1);
        if (!file_put_contents($envFile, $str)) {
            return false;
        }

        app()->loadEnvironmentFrom($envFile);

        return true;
    }

}
