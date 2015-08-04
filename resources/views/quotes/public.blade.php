<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quotebot Live Quotes</title>

    <link href="/css/app.css" rel="stylesheet">

    <!-- Fonts -->
    <link href='//fonts.googleapis.com/css?family=Roboto:400,300' rel='stylesheet' type='text/css'>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>



<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1>Live Quotes</h1>
            <div id="Quotes"></div>
        </div>
    </div>

    <div class="row" style="margin-top: 48px;">
        <div class="col-md-12">
            <p>Quotes are updated once every minute.</p>
            <p>Here is the <a href="/api/v1/quote/all?apitoken={{$apiToken}}">JSON Data feed</a>.</p>
        </div>
    </div>
</div>



    <!-- Scripts -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.2/js/bootstrap.min.js"></script>


    <!-- deps -->
    <script src="/bower_components/mithril/mithril.min.js"></script>
    <script src="/bower_components/moment/min/moment.min.js"></script>
    <script src="/bower_components/numeraljs/min/numeral.min.js"></script>

    <!-- pusher -->
    <script>window.PUSHER_URL = '{{$pusherUrl}}';</script>
    <script src="{{$pusherUrl}}/public/client.js"></script>

    <!-- app -->
    <script>window.quoteBotAPIToken = '{{$apiToken}}';</script>
    @foreach ($scripts as $script)
        <script src="/js/{{$script}}"></script>
    @endforeach
</body>
</html>
