<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* CSS for receipt styling */
        body {
            /* font-family: Arial, sans-serif; */
            font-family: "Times New Roman", Times, serif;
            margin: 0;
            padding: 0;
        }

        .receipt {
            border: 2px solid #333;
            padding: 10px;
            width: 19cm;
            height: 26.7cm;
            margin: 0 auto;
            overflow: hidden;
            font-size: 12px;
        }

        .receipt-header {
            text-align: center;
            margin-bottom: 10px;
        }

        .receipt-header h2 {
            margin: 0;
        }

        .receipt-logo {
            text-align: center;
            margin-bottom: 10px;
        }

        .receipt-logo img {
            max-width: 80px;
        }

        .receipt-details {
            margin-bottom: 10px;
            font-size: 14px;
        }

        .receipt-details p {
            margin: 5px 0;
        }

        .receipt-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            text-transform: uppercase;
            font-size: 14px;
        }

        .receipt-table th,
        .receipt-table td {
            border: 1px solid #333;
            /* padding: 2px; */
            text-align: center;
        }

        .receipt-table th {
            background-color: #f2f2f2;
        }

        .receipt-total {
            text-align: right;
            font-size: 14px;
        }

        .receipt-total p {
            margin: 5px 0;
        }

        .bullet-points {
            margin-top: 5px;
            font-size: 14px;
        }

        .bullet-points p {
            margin-bottom: 5px;
        }

        .bullet-points i {
            color: red;
            font-size: 10px;
        }

        .bus-fees {
            margin-top: 10px;
            padding: 10px;
            border: 1px solid #333;
            width: 100%;
            box-sizing: border-box;
            font-size: 14px;
        }

        .bus-fees p {
            margin: 5px 0;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .receipt {
                width: 19cm;
                height: 26.7cm;
                overflow: hidden;
                page-break-after: always;
            }
        }

    </style>
</head>

<body>
    <div class="receipt">
        {{-- <div class="receipt-header">
            <h2>Lokmanya Vidhya Niketan</h2>
        </div>
        <div class="receipt-logo">
            <img src="http://localhost/lvn-school/public/assets/backend/images/header-logo.png" alt="Logo"> <!-- Placeholder logo -->
        </div> --}}

        <div class="d-flex justify-content-center align-items-center">
            <img height="60" src="https://gallopbiz.in/lvn-school/wp-content/uploads/2024/02/LVN-logo.png" alt="Logo">
            {{-- <h2>Lokmanya Vidhya Niketan</h2> --}}
        </div>

        <div class="receipt-details">
            <div class="d-flex justify-content-between" style="margin-top: 3rem;">
                <span><strong>Student Name:</strong> John Doe </span>
                <span><strong>Class:</strong> 10th </span>
                <span><strong>Payment Date:</strong> 09-08-2023 </span>
            </div>
        </div>
        {{-- <table class="receipt-table">
            <thead>
                <tr>
                    <th>S.no.</th>
                    <th>Account Type</th>
                    <th>Account Name</th>
                    <th>Amount (INR)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>Term 1</td>
                    <td>Tution Fee</td>
                    <td>500</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Term 1</td>
                    <td>Lab Fee</td>
                    <td>500</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>Term 2</td>
                    <td>Tution Fee</td>
                    <td>500</td>
                </tr>
                <tr>
                    <td>4</td>
                    <td>Term 2</td>
                    <td>Lab Fee</td>
                    <td>500</td>
                </tr>
                
                <tr>
                    <td>5</td>
                    <td>Term 3</td>
                    <td>Lab Fee</td>
                    <td>1000</td>
                </tr>
                <tr>
                    <td>6</td>
                    <td>Term 4</td>
                    <td>Tution Fee</td>
                    <td>1000</td>
                </tr>
                <tr>
                  <td>7</td>
                  <td>Bus</td>
                  <td>installment 1</td>
                  <td>1000</td>
                </tr>
                <tr>
                  <td>8</td>
                  <td>Bus</td>
                  <td>installment 2</td>
                  <td>1000</td>
                </tr>
                <tr>
                  <td>9</td>
                  <td>Bus</td>
                  <td>installment 3</td>
                  <td>1000</td>
                </tr>
                <tr>
                  <td>10</td>
                  <td>Term</td>
                  <td>Late Fee</td>
                  <td>500</td>
                </tr>
                <tr>
                  <td>11</td>
                  <td>-</td>
                  <td>Discount</td>
                  <td>0</td>
                </tr>

                
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>1500</td>
                </tr>
            </tbody>
        </table> --}}

        <table class="receipt-table">
          <thead>
              <tr>
                  <th>S.no.</th>
                  <th>Account Name</th>
                  <th>Term 1</th>
                  <th>Term 2</th>
                  <th>Term 3</th>
                  <th>Term 4</th>
                  <th>Total Amount</th>
              </tr>
          </thead>
          <tbody>
              <tr>
                  <td>1</td>
                  <td>Admission Fee</td>
                  <td>500</td>
                  <td>500</td>
                  <td>500</td>
                  <td>500</td>
                  <td>2000</td>
              </tr>
              <tr>
                  <td>2</td>
                  <td>Tution Fee</td>
                  <td>500</td>
                  <td>500</td>
                  <td>500</td>
                  <td>500</td>
                  <td>2000</td>
              </tr>
              <tr>
                  <td>3</td>
                  <td>Exam Fee</td>
                  <td>500</td>
                  <td>500</td>
                  <td>500</td>
                  <td>500</td>
                  <td>2000</td>
              </tr>
              <tr>
                  <td colspan="6" style="text-align: right">Total</td>
                  <td>9000</td>
              </tr>
              
              
             
          </tbody>
      </table>

      <table class="receipt-table">
        <thead>
            <tr>
                <th>S.no.</th>
                <th>Name</th>
                <th>installment 1</th>
                <th>installment 2</th>
                <th>installment 3</th>                
                <th>Total Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Bus Fee</td>
                <td>500</td>
                <td>500</td>
                <td>500</td>                
                <td>1500</td>
            </tr>                                            
        </tbody>
    </table>
        <div class="receipt-total">
            <p><strong>Total Amount (INR):</strong> 8000</p>
        </div>
        <div class="bullet-points">
            <ul>
              <li>The Fee is to be paid as per the schedule furnished in the fee card.</li>
              <li>Activity Fee/Computer Fee is to be paid with the I installment of the Fee.</li>
              <li>Fee once paid under head (other than Caution Money) <u>WILL NOT BE REFUNDABLE</u> in any circumstances.</li>
            </ul>           
        </div>
        {{-- <div class="bus-fees">
            <p><strong>Bus Fees:</strong> Distance: 5 K.M. - INR 2000 (Paid in 2 installments: INR 1000 each)</p>
            <p><strong>Total Bus Fees Paid (INR):</strong> 2000</p>
        </div> --}}
    </div>
</body>

</html>
