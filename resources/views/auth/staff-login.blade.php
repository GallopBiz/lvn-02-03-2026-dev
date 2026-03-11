<!DOCTYPE html>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <meta http-equiv="X-UA-Compatible" content="ie=edge" />
  <title>Lokmanya Vidya Niketan Staff Login</title>
  <link href="https://fonts.googleapis.com/css?family=Nunito:300,400,400i,600,700,800,900" rel="stylesheet" />
  <link href="{{url('assets/backend/')}}/css/themes/lite-purple.min.css" rel="stylesheet" />
</head>
<div class="auth-layout-wrap" style="background-image: url({{url('assets/backend/')}}/images/lvn-bg.jpg)">
  <div class="auth-content">
    <div class="card o-hidden">
      <div class="row">
        <div class="col-md-6">
          <div class="p-4">
            <div class="auth-logo text-center mb-4" style="width:270px;height:65px;" >
              <img src="{{url('assets/backend/')}}/images/header-logo (1).png" alt="" style="width:100%; height: 65px;" />
            </div>
            <form method="POST" action="{{ route('staff.login.perform') }}">
              @csrf
              <div class="form-group">
                <label for="email">Email</label>
                <input id="email" type="email" class="form-control form-control-rounded @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
              </div>
              <div class="form-group">
                <label for="password">Password</label>
                <input id="password" type="password" class="form-control form-control-rounded @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
              </div>
              <button class="btn btn-rounded btn-primary w-100 mt-2">Sign In</button>
            </form>
          </div>
        </div>
        <div class="col-md-6 text-center" style="background-size:cover;background-image:url('{{url('assets/backend/')}}/images/login-side-img.jpg');">
        </div>
      </div>
    </div>
  </div>
</div>
