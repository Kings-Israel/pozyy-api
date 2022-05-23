<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <link rel="icon" sizes="32x32" href="favicon-32x32.png" type="image/png">
    <link rel="icon" sizes="16x16" href="favicon-16x16.png" type="image/png">
    <link rel="manifest" href="site.webmanifest">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <title>Pozyy - Event Ticket Details</title>
    <style>
        #app {
            background: rgb(218, 218, 218);
        }
        .navbar {
            justify-content: center;
            background-color: rgb(36, 240, 237);
        }
        h1, h2, h3, h4, h5 {
            text-align: center;
        }
        .image {
            display: flex;
            justify-content: center;
        }
        .event-img {
            width: 150px;
            border-radius: 5px;
        }
    </style>
</head>
<body id="app">
    <nav class="navbar navbar-expand-md navbar-dark shadow-sm">
        <img src="{{ asset('images/pozzy.png') }}" width="250" alt="">
    </nav>
    <div class="container">
        <div class="details">
            <div class="user-details">
                <h2>{{ $ticket->user->fname }} {{ $ticket->user->lname }}</h2>
                <h3>{{ $ticket->user->email }}</h3>
                <h4>+{{ $ticket->user->phone_number }}</h4>
            </div>
            <hr>
            <div class="event-details">
                <div class="image">
                    <img src="{{ $ticket->event->poster }}" alt="" class="event-img">
                </div>
                <h2>{{ $ticket->event->title }}</h2>
                <h3>{{ $ticket->event->venue }}</h3>
                <h3>{{ Carbon\Carbon::parse($ticket->event->date)->format('D d M Y') }}</h3>
                <h5>Status: {{ $ticket->isPaid == true ? 'Paid' : 'Not Paid' }}</h5>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
</body>
</html>
