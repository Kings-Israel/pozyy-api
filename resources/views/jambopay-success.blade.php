<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pozyy</title>
  <style>
    .container {
      text-align: center;
      margin-top: 50px;
    }
    .success-text {
      color: #fff;
      margin: 0 20px 0 20px;
    }

    .success-text h3 {
      font-family: system-ui,
          -apple-system, /* Firefox supports this but not yet `system-ui` */
          'Segoe UI',
          Roboto,
          Helvetica,
          Arial,
          sans-serif,
          'Apple Color Emoji',
          'Segoe UI Emoji';
    }

    body{
      background-color: #1E272D;
    }

    .close-container{
      position: relative;
      margin: auto;
      width: 50px;
      height: 50px;
      margin-top: 100px;
      cursor: pointer;
    }

    .leftright{
      height: 4px;
      width: 25px;
      position: absolute;
      margin-top: 24px;
      margin-left: 10px;
      background-color: #F4A259;
      border-radius: 2px;
      transform: rotate(45deg);
      transition: all .3s ease-in;
    }

    .rightleft{
      height: 4px;
      width: 50px;
      position: absolute;
      margin-top: 15px;
      margin-left: 21px;
      background-color: #F4A259;
      border-radius: 2px;
      transform: rotate(-45deg);
      transition: all .3s ease-in;
    }

    label{
      color: white;
      font-family: Helvetica, Arial, sans-serif;
      font-size: .6em;
      text-transform: uppercase;
      letter-spacing: 2px;
      transition: all .3s ease-in;
      opacity: 0;
    }
    .close{
      margin: 60px 0 0 5px;
      position: absolute;
    }

    .close-container:hover .leftright{
      transform: rotate(-45deg);
      background-color: #F25C66;
    }
    .close-container:hover .rightleft{
      transform: rotate(45deg);
      background-color: #F25C66;
    }
    .close-container:hover label{
      opacity: 1;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="close-container">
      <div class="leftright"></div>
      <div class="rightleft"></div>
      <label class="close">close</label>
    </div>
    <div class="success-text">
      <h3>The transaction was successful.</h3>
    </div>
  </div>
</body>
</html>
