<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.3/css/bootstrap.min.css"
         integrity="sha512-oc9+XSs1H243/FRN9Rw62Fn8EtxjEYWHXRvjS43YtueEewbS6ObfXcJNyohjHqVKFPoXXUxwc+q1K7Dee6vv9g=="
         crossorigin="anonymous" referrerpolicy="no-referrer" />
   <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"
         integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ=="
         crossorigin="anonymous" referrerpolicy="no-referrer"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.3/js/bootstrap.bundle.min.js"
         integrity="sha512-iceXjjbmB2rwoX93Ka6HAHP+B76IY1z0o3h+N1PeDtRSsyeetU3/0QKJqGyPJcX63zysNehggFwMC/bi7dvMig=="
         crossorigin="anonymous" referrerpolicy="no-referrer"></script>
   <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css"
         integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">

   <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.0.0/crypto-js.min.js"
         integrity="sha512-nOQuvD9nKirvxDdvQ9OMqe2dgapbPB7vYAMrzJihw5m+aNcf0dX53m6YxM4LgA9u8e9eg9QX+/+mPu8kCNpV2A=="
         crossorigin="anonymous" referrerpolicy="no-referrer"></script>
   <meta name="csrf-token" content="{{ csrf_token() }}" />
   <link rel="stylesheet" href="https://checkout.jambopay.com/jambopay-styles-checkout.min.css" />
    <!-- <link rel="stylesheet" href="http://196.50.21.51:16220/jambopay-styles-checkout.min.css" /> -->

</head>

<script type="text/javascript" defer src="https://checkout.jambopay.com/jambopay-js-checkout.min.js"></script>
<!-- <script type="text/javascript" defer src="http://196.50.21.51:16220/jambopay-js-checkout.min.js"></script>  -->


<style>
    .font-weight-bold {
        font-weight: 500 !important;
    }

    .picture-div {
        height: 200px;
    }

    .detail-div {
        height: 80px;
    }

    .pointable {
        cursor: pointer;
    }
</style>
<body onload="load_iframe()">

<div>
    <div class="p-4">

    </div>
</div>

</div>
<script type="text/javascript">
   $.ajaxSetup({
      headers: {
         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
   });

   function load_iframe() {
      var id = {!! json_decode($id) !!}
      var type = {!! json_encode($type) !!}
      var user_id = {!! json_decode($user_id) !!}
      var url = {!! json_encode($url) !!}

      var CustomerPhone = '0700123456';
      var user_email = 'user@jambopay.com';

      let _token   = $('meta[name="csrf-token"]').attr('content');
      $.ajax({
            url: url,
            type:"POST",
            data:{
               id: id,
               type: type,
               user_id: user_id,
               _token: _token
            },
            success:function(response){
               if(response.success){
                  const merchantDetails = {
                        OrderId: response.invoice_id,
                        CustomerEmail: user_email,
                        Currency: 'KES',
                        CustomerPhone: CustomerPhone,
                        OrderAmount: response.amount,
                        BusinessEmail: 'david@pozyy.com',
                        CancelledUrl: response.cancel_url,
                        CallBackUrl: response.callback_url,
                        Description: 'Jambopay Payment',
                        Checksum: 'sample checksum',
                        PhoneNumber: CustomerPhone,
                        StoreName: 'POZYY GROUP LIMITED',
                        ClientKey: response.client_key,
                  }
                  const demoClientKey = 'd3m07r183'
                  const checkSumVars = merchantDetails.CustomerEmail + merchantDetails.OrderId + merchantDetails.OrderAmount +
                        merchantDetails.Currency + merchantDetails.BusinessEmail + merchantDetails.CallBackUrl + merchantDetails.CancelledUrl + demoClientKey;
                  merchantDetails.Checksum = CryptoJS.SHA256(checkSumVars).toString();
                  const merchantCredetials = {
                        token: response.access_token,
                        secretKey: 'secretKey'
                  }
                  const themeDetails = {
                        // primaryColor: '#925223',
                        // secondaryColor: '#000',
                        // textColor: '#47bbe9',
                        // successButtonColor: '#4A160B',
                        // successButtonTextColor: '#FFF',
                        // cancelButtonTextColor: '#345',
                        // payBtnLoaderColor: "#3F8EFC",
                        // cancelBtnLoaderColor: "#3F8EFC",
                        // activeModeOfPaymentColor: "#fff",
                  }
                  jambopayCheckout(merchantDetails, merchantCredetials, themeDetails);
               }else{
                  alert("Error")
               }
            },
            error:function(error){
               console.log(error)
            }
      });
   }
   $("#jambopay-checkout-btn").click(function(e){
      e.preventDefault();
   });

</script>

</body>
</html>
