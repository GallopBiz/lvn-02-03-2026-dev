<?php 
if (isset($_COOKIE['selectedYear'])) {
            $selectedYear = $_COOKIE['selectedYear'];
            // Now you can use $selectedYear in your PHP code
            $selectedYear;
        }
        
        if (!empty($request->session)){
            $db_name = $request->session; 
        } else if (!empty($request->session_name)){
            $db_name = $request->session_name;
        } else {
            if (isset($_COOKIE['selectedYear'])) {
                $db_name = $_COOKIE['selectedYear'];
            } else {
                $db_name = "2023_2024";
            }
            
        }
        // $db_name = "2023_2024";

        $dynamicConnectionName = 'dynamic';
        $dynamicConfig = Config::get("database.connections.{$dynamicConnectionName}");
        $dynamicConfig['database'] = $db_name;
        Config::set('database.connections.dynamic', $dynamicConfig);
        DB::reconnect('dynamic');
        $challan_data ="";
        $student_data = DB::connection('dynamic')->table('student_registration')->where('class_name', 01)->get();
        echo "<table>
                    <thead>
                      <tr>
                        <th>S. No</th>
                        <th>Scholar No</th>
                        <th>Student Name</th>
                        <th>Discount</th>
                        <th></th>
                        <th></th>
                      </tr>
                    </thead>"; 
        $j=1;            
        foreach($student_data as $student_data1){ 
          //echo $student_data1->id;
            $challan_data = DB::connection('dynamic')->table('feesreceiptchallan')->where('student_id', $student_data1->id)->get();
            //echo count($challan_data);
            

        $firstRow = true;  $sno = 1;
          echo "<tr><td>"; echo $j; echo "</td><td>" ;  echo $student_data1->scholar_no; echo "  "; echo "</td><td>" ; echo $student_data1->student_name; echo "</td>";
          
           $total_dueamount_ = 0; $by_cash_ = 0;
                        
                          $totalnextyear1 = 0;
                          $refundable = 0;
                          $totalnextyear = DB::connection('dynamic')->table('totalnextyear')->where('scholar_no',$student_data1->scholar_no)->get();
          $data_student_name = DB::connection('dynamic')->table('student_registration')->select('student_name','id')->get();
         $total_dueamount_ = 0; $by_cash_ = 0;
                        
                          $totalnextyear1 = 0;
                          $refundable = 0;
                         if(!empty($totalnextyear)) {
                          // print_r($totalnextyear);
                          $i = 1;
                          
                          $previousFeesDate = null; // Initialize variables to track previous values
                          $previousCreatedAt = null;
                          $previousTotalNextYear = null;
                          $mergedAccountNames = []; // Initialize an array to store merged account_names
                          
                          foreach ($totalnextyear as $next) {
                              
                              // print_r($next);
                              if($next->account_name == 'CUATION MONEY'){
                                $totalnextyear1 = $next->totalnextyear - $next->fees;
                                $refundable = $next->fees;
                              }
                              // Check if the current fees_date, created_at, and totalnextyear are different from the previous ones
                              if ($next->fees_date != $previousFeesDate || $next->created_at != $previousCreatedAt || $next->totalnextyear != $previousTotalNextYear) {
                                  
                                  $id_n = $i;
                                  $fees_date = $next->fees_date;
                                  $created_at = $next->created_at;
                                  
                                  
                                  // echo '<td>0</td>';
                                  $previousFeesDate = $next->fees_date; // Update previous values
                                  $previousCreatedAt = $next->created_at;
                                  $previousTotalNextYear = $next->totalnextyear;
                          
                                  // Display the merged account_names for the same group
                                  if (!empty($mergedAccountNames)) {
                                      // echo '<td>' . implode(', ', $mergedAccountNames) . '</td>';
                                  } else {
                                      // echo '<td></td>';
                                  }
                          
                                  // Clear the mergedAccountNames array for the next group
                                  $mergedAccountNames = [$next->account_name];
                              } else {
                                  // Append account_name to the mergedAccountNames for the same group
                                  $mergedAccountNames[] = $next->account_name;
                              }
                              $i++;
                          }
                          
                if(count($challan_data) == 0){ 
             echo "<td></td><td style='background-color: #e5ef36;'>";   echo $totalnextyear1; //echo $refundable; 
             "</td></tr>";}          
                          
                          // Display the last row outside the loop
                          
                          
                            
                        }                
        foreach ($challan_data as $object) {
            if ($firstRow) {
                            $firstRow = false; // Set the flag to false after processing the first row
                            continue; // Skip the first row
                            echo "next";
            }

            $data = json_decode($object->str_json, true);
            
        
         

        
      
         
            $term_str = explode(',', $data['term_str']);
            $head_name = explode(',', $data['head_name']);
                          $head_due_amount = explode(',', $data['head_due_amount']);
                          $head_rec_ammount1 = explode(',', $data['head_rec_ammount']);
                          $rec_ammount = array_sum($head_rec_ammount1);
                          $by_cash = $data['by_cash'];
                          $total_dueamount = $data['grand_total_due']; // Extract the "total_dueamount" value
                          $payment_by_select = $data['payment_by_select'];
                          $term_str = explode(',', $data['term_str']);

                          // // Create an array to hold remarks for each fee item
                          $remarks = [];
                          for ($i = 0; $i < count($head_name); $i++) {
                              $remarks[] = $head_name[$i] . ' ( ' . $head_due_amount[$i] . ' )';
                          }

                          

                          $by_cash_ += $by_cash;
                          $sno++;

                          
                          if(!empty($rec_ammount)){ 
                             $total_dueamount_ += $rec_ammount;
                           }else{
                            $total_dueamount_ += $total_dueamount;
                          }
                          $sno++;
                          $a1 = $total_dueamount_ + $totalnextyear1;
                          $b2 =$by_cash_ - $a1;

            echo "<td> ".$data['discount']['lumpsum_fees']."</td>";
            if(!empty($data['discount']['lumpsum_fees'])){
               echo "<td style='background-color: #bce7bc;'> Not Defaulter</td></tr>";
            }
            else{  
              //foreach ($term_str as $term_str1) { echo $term_str1; }
             // foreach ($term_str as $term_str1) { } 
              
              if(count($term_str) > 0 && empty($term_str)){ echo "<td style='background-color: #e5ef36;'> Defaulter "; echo count($term_str); echo "1111"; echo "</td></tr>"; }
              else{
              echo "<td style='background-color: #e5ef36;'>";  count($term_str);  if($b2 > 0){ echo "Defaulter";}  echo "--".$b2;  echo  "</td></tr>";
              //foreach ($term_str as $term_str1) { if( $term_str1 == "2nd"){ }else { echo "Defaulter"; break;} }
            }
            $a1=""; $b2="";
               //echo "<td style='background-color: #bce7bc;'> Not Defaulter</td></tr>";
           // }else{ 
            //  echo "<td  style='background-color: #e5ef36;'>"; foreach ($term_str as $term_str1) { echo $term_str1; }  echo "</td></tr>";
         // }
        }if($challan_data == ""){ echo "<table><tr><td style='background-color: #e5ef36;'>jjjjj</td></tr></table>";}
        }      
          
        
         
        
           





      $j++;                  
                        
       } 
       
       echo "</table>";
      
    ?>