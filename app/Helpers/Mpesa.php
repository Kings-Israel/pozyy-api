<?php

namespace App\Helpers;
use Carbon\Carbon;

class Mpesa
{
   public static function oxerus_mpesaSecurityCredentials()
   {
      try {
         $public_key = Storage::get('mpesa-cert.cer');
         $initiator_pw = config('services.mpesa.initiator_password');
         openssl_public_encrypt($initiator_pw, $encrypted, $public_key, OPENSSL_PKCS1_PADDING);
         return base64_encode($encrypted);
      } catch (\Exception $e) {
         return false;
      }
   }

   public static function oxerus_mpesaLipaNaMpesaPassword()
   {
      $passkey = config('services.mpesa.lipa_passkey');
      $business_shortcode = config('services.mpesa.business_shortcode');
      $timestamp = Carbon::now()->format('YmdHis');
      return base64_encode($business_shortcode . $passkey . $timestamp);
   }

   public static function oxerus_mpesaGetStkPushUrl()
   {
      if (config('services.mpesa.env') === 'SANDBOX') {
         return config('services.mpesa.stk_push_url_sandbox');
      } elseif (config('services.mpesa.env') === 'LIVE') {
         return config('services.mpesa.stk_push_url_live');
      } else {
         return config('services.mpesa.stk_push_url_sandbox');
      }
   }

   public static function oxerus_mpesaGetAccessTokenUrl()
   {
      if (config('services.mpesa.env') === 'SANDBOX') {
         return config('services.mpesa.access_token_url_sandbox');
      } elseif (config('services.mpesa.env') === 'LIVE') {
         return config('services.mpesa.access_token_url_live');
      } else {
         return config('services.mpesa.access_token_url_sandbox');
      }
   }

   public static function oxerus_mpesaGetB2CUrl()
   {
      if (config('services.mpesa.env') === 'SANDBOX') {
         return config('services.mpesa.b2c_url_sandbox');
      } elseif (config('services.mpesa.env') === 'LIVE') {
         return config('services.mpesa.b2c_url_live');
      } else {
         return config('services.mpesa.b2c_url_sandbox');
      }
   }

   public static function oxerus_mpesaGenerateAccessToken()
   {
      $consumer_key = config('services.mpesa.consumer_key');
      $consumer_secret = config('services.mpesa.consumer_secret');
      $credentials = base64_encode($consumer_key . ":" . $consumer_secret);
      $url = self::oxerus_mpesaGetAccessTokenUrl();
      try {
         $curl = curl_init();
         curl_setopt($curl, CURLOPT_URL, $url);
         curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Basic " . $credentials));
         curl_setopt($curl, CURLOPT_HEADER, false);
         curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
         curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
         $curl_response = curl_exec($curl);
         $access_token = json_decode($curl_response);
         return $access_token->access_token;
        } catch (\Exception $e) {
          info($e);
         return false;
      }
   }
}
