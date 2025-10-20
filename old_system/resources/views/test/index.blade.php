<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Tests</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <style>
        section {
            display: table;
        }

        a {
            display: table-row;
            color: firebrick;
            text-decoration: none;
            font-family: consolas;
            font-size: 14px;
        }

        span {
            display: table-cell;
            padding: 5px 20px;
        }

        a:hover {
            background-color: lightgray;
        }
    </style>

</head>
<body>

<section>
    @foreach($methods as $method)
        <a href="{{ $method['url'] }}">
            <span>{{ $method['url-signature'] }}</span>
            <span>{{ $method['description'] }}</span>
        </a>
    @endforeach
</section>

</body>
</html>
