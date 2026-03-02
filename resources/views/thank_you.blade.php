<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Thank You - Payment Receipt</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      background: #f4f4f9;
      color: #333;
      padding: 20px;
    }

    .container {
      max-width: 700px;
      margin: 0 auto;
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      text-align: center;
    }

    .buttons {
      margin-top: 20px;
    }

    button {
      padding: 12px 24px;
      font-size: 16px;
      border: none;
      border-radius: 6px;
      margin: 5px;
      cursor: pointer;
    }

    .download {
      background: #27ae60;
      color: white;
    }

    .goback {
      background: #e74c3c;
      color: white;
    }

    .table-wrapper {
      overflow-x: auto;
      margin-top: 20px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
      min-width: 600px;
    }

    th, td {
      padding: 10px;
      border: 1px solid #ccc;
      text-align: left;
    }

    @media print {
      .no-print {
        display: none;
      }
    }
  </style>
</head>
<body>

@php
    use Illuminate\Support\Facades\DB;
    use Carbon\Carbon;

    $paymentCreatedAt = DB::table('student_online_fee_payments')
        ->where('transaction_id', $transactionId)
        ->value('created_at');

    $paymentDateFormatted = $paymentCreatedAt 
        ? Carbon::parse($paymentCreatedAt)->format('d-m-Y') 
        : 'N/A';

    $scholarNo = $studentName = $classSection = 'N/A';

    $challanId = null;

    // Decode str_json and extract challan_id
    if (!empty($paymentDetails[0]->str_json)) {
        $decodedStrJson = json_decode($paymentDetails[0]->str_json, true);

        // Make sure it's properly decoded
        if (is_array($decodedStrJson)) {
            $challanId = $decodedStrJson['challan_id'] ?? null;
            $scholarNo = $decodedStrJson['name_scholarno'] ?? 'N/A';
            $studentName = $paymentDetails[0]->name_student ?? 'N/A';
            $classSection = $decodedStrJson['name_classsection'] ?? 'N/A';
        }
    }
@endphp




  <div class="container no-print">
    <h1>Thank You for Your Payment!</h1>
    <p>Your payment has been processed successfully.<br/>
       <strong>Transaction ID:</strong> {{ $transactionId }}</p>

    <div class="buttons">
      <button class="download" onclick="downloadPDF()">Download PDF Receipt</button>
      <button class="goback" onclick="goBack()">Go To Main Site</button>
    </div>
  </div>

  <!-- Hidden PDF content -->
  <div id="pdfContent" style="display: none; padding: 30px; font-size: 14px;">
    <div style="text-align: center; margin-bottom: 20px;">
      <img src="{{ asset('assets/backend/images/LVN-logo.png') }}" alt="LVN Logo" style="height: 80px;" />
    </div>

    <h2 style="text-align: center;">Payment Receipt</h2>

		<div style="display: flex; justify-content: space-between; margin-top: 10px; margin-bottom: 20px;">
		  <p><strong>Transaction ID:</strong> {{ $transactionId }}</p>
		  <p><strong>Payment Date:</strong> {{ $paymentDateFormatted }}</p>
		</div>

		<!--<div style="margin-bottom: 20px;">
		  <p><strong>Scholar Name:</strong> {{ $studentName }}</p>
		  <p><strong>Scholar No:</strong> {{ $scholarNo }}</p>
		  <p><strong>Class:</strong> {{ $classSection }}</p>
		</div>-->




    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>Account Name</th>
            <th>Term</th>
            <th>Fees Due</th>
            <th>Paid Amount</th>
            <th>Remaining Amount</th>
          </tr>
        </thead>
        <tbody>
          @foreach($paymentDetails as $payment)
          <tr>
            <td>{{ $payment->fee_account }}</td>
            <td>{{ $payment->fee_term }}</td>
            <td>{{ $payment->fees_due }}</td>
            <td>{{ $payment->allocated_amount }}</td>
            <td>{{ $payment->remaining_amount }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <p style="text-align:center; margin-top:30px;">Thank you for your payment.</p>
  </div>

  <!-- html2pdf.js CDN -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>

  <script>
    function downloadPDF() {
      const element = document.getElementById('pdfContent');
      element.style.display = 'block';

      const opt = {
        margin:       0.5,
        filename:     'payment_receipt_{{ $transactionId }}.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2 },
        jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
      };

      html2pdf().set(opt).from(element).save().then(() => {
        element.style.display = 'none';
      });
    }

    function goBack() {
      window.location.href = "https://lvnindore.org.in/";
    }
  </script>
</body>
</html>
