<?php
    include_once("../db.php");
    session_start();
    $sid=$_SESSION["id"];
    $ele=array("14CSE06","14CSE11","14CSO07","14ITO01");
    
    if(isset($_POST["tab"]))
    {
         $tab=strtolower($_POST["tab"]);
         $code=$_POST["code"];
         $sql="SELECT * FROM `tt` WHERE `class` LIKE '$tab'";
     
         $res=$con->query($sql);
         $day=array();
         $day_per=array();
         while($row=$res->fetch_assoc())
         { 
              $per=array();
              foreach($row as $in=>$v)
              {
                   if(strpos($v,$code)!==false)
                   {
                        array_push($per,$in);
                   } 
              }
              if(!empty($per))
              {
                    $day_per+=array($row["day"]=>$per);
              }   
         }
     
         $x=date("Y-m-d");
         $tdy=date_create($x);
         $date=date("2020-07-08");
         $diff=intval(date_diff($tdy,date_create($date))->format("%a"))+1;
         $dates=array();

        
         for($i=1;$i<=$diff;$i++)
         {    
              $s=date("l", strtotime($date));
               foreach($day_per as $d=>$pd)
               {
                    if($d==$s)
                    {
                         foreach($pd as $periods)
                         {
                              if(in_array($code,$ele))
                              {
                                   
                                   
                                   $sql="SELECT * FROM `$code` where date LIKE '$date' AND code LIKE '$sid' AND `period` LIKE '$periods'"; 
                              }
                              else
                              {
                                   $sql="SELECT * FROM `$tab` where date LIKE '$date' AND code LIKE '$code' AND `period` LIKE '$periods'"; 
                              }
                           
                              $r=$con->query($sql);
                              if($r->num_rows==0)
                              {    
                                   array_push($dates,$date);
                              }
                         }
                    }
               }
               $date=date_format(date_add(date_create($date),date_interval_create_from_date_string("1 days")),"Y-m-d");
          }
               
         
          echo json_encode(array($dates,$day_per));
          exit();

    }
    else if(isset($_POST["cls"]))
    {
         
         $tab=strtolower($_POST["cls"]);


         $code=$_POST["code"];
         $sql="SELECT name FROM `course_list` WHERE code LIKE '$code'";
         $name=($con->query($sql))->fetch_assoc()["name"]; 
         if(in_array($code,$ele))
         {
          $sql="SELECT * FROM `$code` WHERE code LIKE '$sid' ORDER BY `date` DESC,`period` ASC"; 
          $tab=$code;
          $code=$code;
         }
         else{
              $sql="SELECT * FROM `$tab` WHERE code LIKE '$code' ORDER BY `date` DESC,`period` ASC"; 
         }
         $res=$con->query($sql);
         $cnt=mysqli_num_fields($res)-3;
         while($row=$res->fetch_assoc())
         {
              $d=date("d-m-Y",strtotime($row["date"]));
              $h=$row["period"];
          //     $abs=array();
              $abs='<b><em>Course &nbsp: &nbsp'.$name.'<br><br>Date &nbsp: &nbsp '.$d.'<br><br>Absentees:<br> <ol class="ui  list">';
              foreach($row as $ind=>$val)
              {
                   if($val=="A")
                   {
                        $abs.='<li>'.$ind.'&nbsp; - &nbsp;'.($con->query("SELECT name from registration where regno like '$ind'"))->fetch_assoc()["name"].'</li>';
                   }
              }
              $abs.='</ol></b></em>';
              if(array_key_exists("P",array_count_values($row)))
              {
               $P=array_count_values($row)["P"];
              }
              else
              {
                    $P=0;
              }
              if(array_key_exists("A",array_count_values($row)))
              {
                    $A=array_count_values($row)["A"];
              }
              else
              {
                    $A=0;
              }
             
              if(array_key_exists("N/A",array_count_values($row)))
              {
                    $na=array_count_values($row)["N/A"];
              }
              else
              {
                    $na=0;
              }
              $cnt-=$na;
              echo '<div class="ui raised  segment" style="width:70%;margin:auto;margin-top:3%;">
                     
               <div class="ui black info right circular icon message">
             
               <div class="ui header">
                       
                              Date &nbsp;:&nbsp; '.$d.'  &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;   Period &nbsp;: &nbsp '.$h.'</span>
                         </div>
                         <div class="content">
                              <div class="ui inverted small statistics" style="margin-left:25%">
                                   <div class="statistic">
                                        <div class="value">
                                             '.$P.'
                                        </div>
                                        <div class="label">
                                             Present
                                        </div>
                                   </div>
                                   <div class="red statistic" id="'.$d.$h.'" >
                                        <div class="value">
                                             '.$A.'
                                        </div>
                                        <div class="label">
                                             Absent
                                        </div>
                                   </div>
                                   <div class="blue statistic">
                                        <div class="value">
                                             '.$cnt.'
                                        </div>
                                        <div class="label">
                                             Total
                                        </div>
                                   </div>
                                   <div class="statistic">
                                        <div class="value">
                                              <button class="ui right floated tertiary icon button" data-tooltip="Click to Edit Uploaded Attendance" id="'.$code."/".$d."/".$h."/".$tab.'" onclick="editor(this.id);" data-position="top left"><i class="edit large icon" style="color:cyan"></i></button>
                                        </div>
                                        <div class="label">
                                             Edit
                                        </div>
                                   </div>
                         </div></div>
                    </div></div><div class="ui popup" id="pop'.$d.$h.'" style="width:100%">'.$abs.'</div>
                    <script>
                    $(document).ready(function(){
                         $("#'.$d.$h.'")
                         .popup({
                         popup: "#pop'.$d.$h.'",
                         inline     : true,
                         hoverable  : true,
                         });
                    });
                    </script>'; 
         }
        exit();
    }
    else if(isset($_POST["consolidate"]))
    {
         
         $tab=$_POST["tname"];
          $code=$_POST["ccode"];
          $_SESSION["tname"]=$tab;
          $_SESSION["ccode"]=$code;
          $_SESSION["cname"]=$name=($con->query("SELECT name FROM `course_list` WHERE code LIKE '$code'"))->fetch_assoc()["name"]; 
         $tab=strtolower($tab);
         if(in_array($_POST["ccode"],$ele))
         {
          $num=($con->query("SELECT date from `$code` where code LIKE '$sid'"))->num_rows;
          if($num>=1)
          {
               echo "export_ready_for_Elec";
          }
         }
         else
         {
          $num=($con->query("SELECT date from `$tab` where code LIKE '$code'"))->num_rows;
          if($num>=1)
          {
               echo "export_ready";
          }
         }
         
          
         if($num==0)
          {
               echo "empty";
          }
         
         exit();
    }
    else if(isset($_POST["editor"]))
    {
          $_SESSION["code"]=$_POST["e_code"];
          $_SESSION["period"]=$_POST["e_period"];
          $_SESSION["date"]=$_POST["e_date"];
          $_SESSION["EditAttnd"]="go&edit";
         if(!in_array($_POST["e_code"],$ele))
         {
          $tab=strtoupper($_POST["edittab"]);
          $arr=explode('-',$tab,3);
          $_SESSION["sec"]=$arr[2];
          $_SESSION["batch"]=$arr[0];
          $_SESSION["dep"]=$arr[1];
          echo "go&edit";
          exit();
         }
         echo "go&editElec";
          exit();
    }
?>
