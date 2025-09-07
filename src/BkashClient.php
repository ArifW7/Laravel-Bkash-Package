<?php

namespace ArifW7\Bkash;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BkashClient
{
    protected $config;
    protected $token;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->token = $this->grant();
    }

    private function curlWithBody($url, $header, $method, $body)
    {
        $curl = curl_init($this->config['base_url'] . $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public function authHeaders()
    {
        return [
            'Content-Type:application/json',
            'Authorization:'.$this->token,
            'X-APP-Key:'.$this->config['app_key']
        ];
    }

    public function grant()
    {
        // Basic token management using database table "bkash_token"
        if (!Schema::hasTable('bkash_token')) {
            Schema::create('bkash_token', function($table){
                $table->boolean('sandbox_mode');
                $table->bigInteger('id_expiry');
                $table->string('id_token',2048);
                $table->bigInteger('refresh_expiry');
                $table->string('refresh_token',2048);
            });
        }

        $sandbox = $this->config['sandbox'] ? 1 : 0;
        $tokenData = DB::table('bkash_token')->where('sandbox_mode',$sandbox)->first();

        if ($tokenData && $tokenData->id_expiry > time()) {
            return $tokenData->id_token;
        }

        // Grant new token
        $header = [
            'Content-Type:application/json',
            'username:'.$this->config['username'],
            'password:'.$this->config['password']
        ];

        $body = [
            'app_key' => $this->config['app_key'],
            'app_secret' => $this->config['app_secret']
        ];

        $response = $this->curlWithBody('/tokenized/checkout/token/grant',$header,'POST',json_encode($body));
        $res = json_decode($response, true);

        // Save to DB
        DB::table('bkash_token')->updateOrInsert(
            ['sandbox_mode' => $sandbox],
            [
                'id_expiry' => time() + 3600,
                'id_token' => $res['id_token'],
                'refresh_expiry' => time() + 864000,
                'refresh_token' => $res['refresh_token']
            ]
        );

        return $res['id_token'];
    }

    public function createPayment(array $data)
    {
        $body = [
            'mode' => '0011',
            'payerReference' => $data['payerReference'] ?? '01677444438',
            'callbackURL' => $data['callbackURL'],
            'amount' => $data['amount'],
            'currency' => 'BDT',
            'intent' => 'sale',
            'merchantInvoiceNumber' => $data['merchantInvoiceNumber'] ?? 'Inv_'.Str::random(6)
        ];

        $response = $this->curlWithBody('/tokenized/checkout/create', $this->authHeaders(), 'POST', json_encode($body));
        return json_decode($response, true);
    }

    public function executePayment($paymentID)
    {
        $body = ['paymentID' => $paymentID];
        $response = $this->curlWithBody('/tokenized/checkout/execute', $this->authHeaders(), 'POST', json_encode($body));
        return json_decode($response, true);
    }

    public function queryPayment($paymentID)
    {
        $body = ['paymentID' => $paymentID];
        $response = $this->curlWithBody('/tokenized/checkout/payment/status', $this->authHeaders(), 'POST', json_encode($body));
        return json_decode($response, true);
    }

    public function refundPayment(array $data)
    {
        $body = [
            'paymentID' => $data['paymentID'],
            'trxID' => $data['trxID'],
            'amount' => $data['amount'] ?? null,
            'sku' => 'sku',
            'reason' => 'Quality issue'
        ];

        $response = $this->curlWithBody('/tokenized/checkout/payment/refund',$this->authHeaders(),'POST',json_encode($body));
        return json_decode($response, true);
    }

    public function searchTransaction($trxID)
    {
        $body = ['trxID' => $trxID];
        $response = $this->curlWithBody('/tokenized/checkout/general/searchTransaction', $this->authHeaders(),'POST',json_encode($body));
        return json_decode($response, true);
    }
}
