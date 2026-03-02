<!doctype html>
<html lang="en">
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.87.0">
    <title>Fixed top navbar example · Bootstrap v5.1</title>

    <!-- Bootstrap core CSS -->
    <link
      href="https://fonts.googleapis.com/css?family=Nunito:300,400,400i,600,700,800,900"
      rel="stylesheet"
    />
    <link
      href="{{url('assets/backend/')}}/css/themes/lite-purple.css"
      rel="stylesheet"
    />
    <link
      href="{{url('assets/backend/')}}/css/plugins/perfect-scrollbar.css"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="{{url('assets/backend/')}}/css/plugins/fontawesome-5.css"
    />
    <link
      href="{{url('assets/backend/')}}/css/plugins/metisMenu.min.css"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="{{url('assets/backend/')}}/css/plugins/datatables.min.css"
    />
    <!-- <link href="{!! url('assets/bootstrap/css/bootstrap.min.css') !!}" rel="stylesheet"> -->

    <style>
      .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        user-select: none;
      }

      @media (min-width: 768px) {
        .bd-placeholder-img-lg {
          font-size: 3.5rem;
        }
      }
    </style>

    
    <!-- Custom styles for this template -->
    <link href="{!! url('assets/css/app.css') !!}" rel="stylesheet">


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    










</head>
<body>
    
    @include('layouts.partials.navbar')

    <main class="container">
        @yield('content')
    </main>

    <script src="{!! url('assets/bootstrap/js/bootstrap.bundle.min.js') !!}"></script>
      
  </body>
</html>