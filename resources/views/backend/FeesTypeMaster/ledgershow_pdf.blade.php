
@php
$i = 0;
@endphp
<div class="main-content">
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card text-start">
            <div class="card-body">
                <div class="table-responsive">
                    <h3 class="ml-4">Lokmanya Vidhya Niketan</h3>
                    <h4 class="ml-4">Student ledge As On  {{ now()->format('d-M-Y') }}</h4>
                    <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                        {{-- <table class="display table table-striped table-bordered" id="zero_configuration_table" --}}
                        {{-- style="width: 100%ss">    --}}
                        
                      <thead>
                        <tr>
                            <th>S. NO.</th>
                            <th>Due Date</th>
                            <th>Entry Date</th>
                            <th>Ammount Dr</th>
                            <th>Ammount Cr</th>
                            <th>Remarks</th>
                            {{-- <th>Action</th> --}}
                        </tr>
                      </thead>
                      <tbody>
                        <?php $total_dueamount_ = 0; $by_cash_ = 0;?>
                        @if(!empty($challan_data))

                        @php $json_data = json_decode($challan_data); @endphp

                        <?php //$firstRow = true; //echo '<pre>'; 
                        // foreach($json_data as $obj){
                        //   if ($firstRow) {
                        //     $firstRow = false; // Set the flag to false after processing the first row
                        //     continue; // Skip the first row
                        //   }
                    
                        //   print_r($obj);
                        // }  
                        

                        $sno = 1;

                        // Loop through the objects and add rows
                        $firstRow = true;
                        foreach ($challan_data as $object) {
                          if ($firstRow) {
                            $firstRow = false; // Set the flag to false after processing the first row
                            continue; // Skip the first row
                          }
                          $data = json_decode($object->str_json, true);
                          // print_r($data);
                          // // Extract relevant data
                          $head_name = explode(',', $data['head_name']);
                          $head_due_amount = explode(',', $data['head_due_amount']);
                          $by_cash = $data['by_cash'];
                          $total_dueamount = $data['grand_total_due']; // Extract the "total_dueamount" value
                          $payment_by_select = $data['payment_by_select'];

                          // // Create an array to hold remarks for each fee item
                          $remarks = [];
                          for ($i = 0; $i < count($head_name); $i++) {
                              $remarks[] = $head_name[$i] . ' ( ' . $head_due_amount[$i] . ' )';
                          }

                          // // Add a row for 'by_cash' and 'total_dueamount' and display remarks and head_name
                          echo '<tr>';
                          echo "<td>$sno</td>";
                          echo '<td>' . date("d-m-Y", strtotime($object->due_upto)) . '</td>';
                          echo '<td>' . date("d-m-Y", strtotime($object->created_at)) . '</td>';
                          echo "<td>$by_cash</td>";
                          echo '<td>0</td>';
                          echo '<td>' . $payment_by_select . '</td>'; // Display payment_by_select as remarks
                          // echo '<td> 
                          // <form method="post" action="{{url(fees-types-master-delete)}}">
                          //   <!-- @csrf -->
                          //   <input type="hidden" name="table_name" value="fees_types_master">
                          //   <input type="hidden" name="delete_id" value="{{ $fees_type->id }}">
                          //   <button type="submit" class="btn btn-primary btn-sm removeItem">View</button>
                          //   </form>
                          // </td>';
                          echo '</tr>';

                          $by_cash_ += $by_cash;
                          $sno++;

                          echo '<tr>';
                          echo "<td>$sno</td>";
                          echo '<td>' . $object->due_upto . '</td>';
                          echo '<td>' . $object->created_at . '</td>';
                          echo '<td>0</td>';
                          echo "<td>$total_dueamount</td>"; // Display the "total_dueamount" value
                          echo '<td>' . implode(' / ', $remarks) . ' etc </td>'; // Display all head_name values as remarks
                          if(!empty($student_data)){
                            echo '<td> 
                            <form method="post" action="student-ledger-receipt-challan">
                              <input type = "hidden" name = "_token" value = "'.csrf_token().'">
                              <input type="hidden" name="student_id" value="'.$student_data->id.'">
                              <input type="hidden" name="class_name" value="'.$student_data->class_name.'">
                              <input type="hidden" name="due_upto" value="'.$object->due_upto.'">
                            
                              </form>
                            </td>';
                              // echo '<option selected value="'.$name->id.'" data-tokens="'.$name->student_name.'">'.$name->student_name.'</option>';
                          }
                          
                          echo '</tr>';

                          $total_dueamount_ += $total_dueamount;
                          $sno++;

                      }
                        ?>
                       
                       
                        @else
                        <tr><td colspan="9" class="text-center">No Data Found</td></tr>
                        @endif
                      </tbody>
                      <tfoot>
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th><?php echo $by_cash_; ?></th>
                            <th><?php echo $total_dueamount_; ?></th>
                            <th></th>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end of main-content -->
</div>


