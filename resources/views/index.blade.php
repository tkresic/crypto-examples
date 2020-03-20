<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Cryptography OS</title>

    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
    <link rel="icon" href="../images/favicon.ico" type="image/x-icon" />

    <style>
        html, body {
            background-color: #fff;
            background-image: url("../images/bg.jpg");
            background-repeat:no-repeat;
            background-position: center center;
            background-size: cover;
            color: #636b6f;
            font-family: 'Nunito', sans-serif;
            font-weight: 200;
            height: 100vh;
            margin: 0;
        }

        .full-height {
            height: 100vh;
        }

        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }

        .position-ref {
            position: relative;
        }

        .content {
            text-align: center;
            word-wrap: break-word;
            width: 50%;
        }

        .title {
            font-size: 84px;
        }

        span, p, a, .title {
            color: #fff;
        }

        .links a {
            padding: 0 25px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: .1rem;
            text-decoration: none;
            text-transform: uppercase;
        }

        .m-b-md {
            margin-bottom: 30px;
        }
        .form-legend + p {
            margin-top: 1rem;
        }
        .form-element {
            position: relative;
            margin-top: 2.25rem;
            margin-bottom: 2.25rem;
        }
        .form-element-bar {
            position: relative;
            height: 1px;
            background: #999;
            display: block;
        }
        .form-element-bar::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: #4120ff;
            height: 2px;
            display: block;
            transform: rotateY(90deg);
            transition: transform 0.28s ease;
            will-change: transform;
        }
        .form-element-label {
            position: absolute;
            top: 35px;
            left: -1px;
            line-height: 1.5rem;
            pointer-events: none;
            padding-left: 0.125rem;
            z-index: 1;
            font-size: 1rem;
            font-weight: bold;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin: 0;
            color: #fff;
            transform: translateY(-50%);
            transform-origin: left center;
            transition: transform 0.28s ease, color 0.28s linear, opacity 0.28s linear;
            will-change: transform, color, opacity;
        }
        .form-element-field {
            outline: none;
            height: 3rem;
            display: block;
            background: none;
            padding: 0.125rem 0.125rem 0.0625rem;
            font-size: 1rem;
            border: 0 solid transparent;
            line-height: 1.5;
            width: 100%;
            color: #fff;
            box-shadow: none;
            opacity: 0.001;
            transition: opacity 0.28s ease;
            will-change: opacity;
            position: relative;
            top: 10px;
        }
        .form-element-field:-ms-input-placeholder {
            color: #ffffff;
            transform: scale(0.9);
            transform-origin: left top;
        }
        .form-element-field::placeholder {
            color: #ffffff;
            transform: scale(0.9);
            transform-origin: left top;
        }
        .form-element-field:focus ~ .form-element-bar::after {
            transform: rotateY(0deg);
        }
        .form-element-field:focus ~ .form-element-label {
            color: #fff;
            font-weight: bold;
        }
        .form-element-field.-hasvalue,
        .form-element-field:focus {
            opacity: 1;
        }
        .form-element-field.-hasvalue ~ .form-element-label,
        .form-element-field:focus ~ .form-element-label {
            transform: translateY(-100%) translateY(-0.5em) translateY(-2px) scale(0.9);
            cursor: pointer;
            pointer-events: auto;
        }
        input.form-element-field:not(:placeholder-shown) {
            opacity: 1;
        }
        input.form-element-field:not(:placeholder-shown) ~ .form-element-label{
            transform: translateY(-100%) translateY(-0.5em) translateY(-2px) scale(0.9);
            cursor: pointer;
            pointer-events: auto;
        }

    </style>
</head>
<body>
<div class="flex-center position-ref full-height">
    <div class="content">
        @if(app('request')->session()->get('message'))
            <p>
                {!! app('request')->session()->get('message') !!}
            </p>
        @endif
        <div class="title m-b-md">
            Crypto Examples
        </div>
        <div class="form-element form-input">
            <input class="form-control form-element-field" type="text" id="value" name="value" placeholder="Enter text here" value="The quick brown fox jumps over the lazy dog.">
            <div class="form-element-bar"></div>
            <label class="form-element-label" for="value">
                Text to encrypt
            </label>
        </div>
        <div class="links">
            <form name="ES" action="{{ url('encrypt-symmetric') }}" method="POST">
                <input id="encrypt-symmetric" hidden type="text" name="text">
                <a href="#" onclick="document.getElementById('encrypt-symmetric').value = document.getElementById('value').value; document.forms['ES'].submit(); return false;">Encrypt Symmetric</a>
            </form>
            <form name="DS" action="{{ url('decrypt-symmetric') }}" method="POST">
                <a href="#" onclick="document.forms['DS'].submit(); return false;">Decrypt Symmetric</a>
            </form>
            <form name="AT" action="{{ url('authenticate') }}" method="POST">
                <a href="#" onclick="document.forms['AT'].submit(); return false;">Authenticate</a>
            </form>
            <form name="VA" action="{{ url('verify-authentication') }}" method="POST">
                <input id="verify-authentication" hidden type="text" name="modified" value="0">
                <a href="#" onclick="document.forms['VA'].submit(); return false;">Verify Authentication</a>
                <span>|</span>
                <a href="#" onclick="document.getElementById('verify-authentication').value = 1; document.forms['VA'].submit(); return false;">Verify Modified Data</a>
            </form>
            <form name="EA" action="{{ url('encrypt-asymmetric') }}" method="POST">
                <input id="encrypt-asymmetric" hidden type="text" name="text">
                <a href="#" onclick="document.getElementById('encrypt-asymmetric').value = document.getElementById('value').value; document.forms['EA'].submit(); return false;">Encrypt Asymmetric</a>
            </form>
            <form name="DA" action="{{ url('decrypt-asymmetric') }}" method="POST">
                <a href="#" onclick="document.forms['DA'].submit(); return false;">Decrypt Asymmetric</a>
            </form>
            <form name="SI" action="{{ url('sign') }}" method="POST">
                <a href="#" onclick="document.forms['SI'].submit(); return false;">Sign</a>
            </form>
            <form name="VS" action="{{ url('verify-signature') }}" method="POST">
                <input id="verify-signature" hidden type="text" name="modified" value="0">
                <a href="#" onclick="document.forms['VS'].submit(); return false;">Verify Signature</a>
                <span>|</span>
                <a href="#" onclick="document.getElementById('verify-signature').value = 1; document.forms['VS'].submit(); return false;">Verify Modified Signature</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
