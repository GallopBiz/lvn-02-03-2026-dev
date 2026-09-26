<!doctype html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow, noarchive">
<title>Download Transfer Certificate</title>
<style>
    *{box-sizing:border-box} body{margin:0;min-height:100vh;font-family:Arial,sans-serif;color:#1f2937;background:linear-gradient(135deg,#edf4ff,#f7f9fc)}
    .portal{max-width:760px;margin:0 auto;padding:52px 20px}.brand{text-align:center;margin-bottom:26px}.brand-mark{width:54px;height:54px;margin:auto auto 12px;border-radius:16px;background:#1e4f91;color:#fff;font-size:25px;font-weight:bold;display:grid;place-items:center}.brand h1{margin:0;font-size:27px;color:#173b6d}.brand p{margin:9px 0 0;color:#667085}
    .card{background:#fff;border-radius:14px;padding:30px;box-shadow:0 12px 35px rgba(26,65,115,.12)}label{font-size:14px;font-weight:bold;color:#344054;display:block;margin-bottom:7px}input{width:100%;padding:13px 14px;border:1px solid #d0d5dd;border-radius:8px;font-size:16px}input:focus{outline:0;border-color:#2d6cbe;box-shadow:0 0 0 3px #dceaff}.btn{border:0;border-radius:8px;padding:13px 18px;font-size:15px;font-weight:bold;cursor:pointer;text-decoration:none;text-align:center;display:inline-block}.btn-primary{background:#1e5ba8;color:white}.btn-secondary{background:#edf3fb;color:#174a89}.btn-row{display:flex;gap:12px;margin-top:23px}.btn-row .btn{flex:1}.notice{padding:13px 14px;border-radius:8px;margin-bottom:20px}.notice-error{background:#fff1f0;color:#b42318;border:1px solid #fecdca}.result-title{font-size:19px;font-weight:bold;color:#173b6d;margin:0 0 18px}.details{display:grid;grid-template-columns:1fr 1fr;gap:13px;background:#f7faff;padding:17px;border-radius:9px}.details div{font-size:14px}.details span{display:block;color:#667085;font-size:12px;margin-bottom:4px}.status{margin-top:18px;padding:11px 13px;border-left:4px solid #24965a;background:#edfff3;color:#146c3b;border-radius:4px;font-size:14px}.helper{font-size:12px;color:#667085;margin-top:8px}@media(max-width:520px){.portal{padding:28px 15px}.card{padding:22px}.details{grid-template-columns:1fr}.btn-row{flex-direction:column}}
</style></head><body>
<main class="portal">
    <div class="brand"><div class="brand-mark">TC</div><h1>Transfer Certificate Portal</h1><p>Secure student document download</p></div>
    <section class="card">
        @if (isset($file))
            <h2 class="result-title">Your Transfer Certificate is ready</h2>
            <div class="details">
                <div><span>Scholar Number</span>{{ $file->scholar_no }}</div>
                <div><span>Academic Session</span>{{ $file->session_name }}</div>
                <div><span>Student Name</span>{{ $studentDetails['name'] ?: '--' }}</div>
                <div><span>Class / Section</span>{{ trim(($studentDetails['class'] ?: '--') . ($studentDetails['section'] ? ' / ' . $studentDetails['section'] : '')) }}</div>
            </div>
            <div class="status">PDF available for download.</div>
            <div class="btn-row">
                <a class="btn btn-primary" href="{{ route('student-tc-download.file', $token) }}">Download PDF</a>
            </div>
        @else
            @if (session('error'))<div class="notice notice-error">{{ session('error') }}</div>@endif
            <h2 class="result-title">Find your Transfer Certificate</h2>
            <p style="color:#667085;margin-top:-8px">Enter your Scholar Number to check whether your certificate is available.</p>
            <form method="POST" action="{{ route('student-tc-download.search') }}">
                @csrf
                <label for="scholar_no">Scholar Number</label>
                <input id="scholar_no" name="scholar_no" value="{{ old('scholar_no') }}" maxlength="50" autocomplete="off" required autofocus>
                @error('scholar_no')<p class="notice notice-error">{{ $message }}</p>@enderror
                <div class="helper">For privacy, only your certificate's availability is returned.</div>
                <button class="btn btn-primary" style="width:100%;margin-top:22px">Check Availability</button>
            </form>
        @endif
    </section>
</main>
</body></html>
