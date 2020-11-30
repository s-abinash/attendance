<?php
    include_once("../db.php");
    session_start();
    $sid=$_SESSION["id"];
    $sql="SELECT `code` FROM `course_list` where `status` LIKE 'active' AND `category` LIKE 'elective'";
    $res=$con->query($sql);
    $ele=array();
    while($row=mysqli_fetch_array($res))
    {
    array_push($ele,$row['code']);
    }
    if(isset($_POST["s1drop"]))
    {
        $sql="SELECT code,name FROM `course_list` WHERE staffA LIKE '$sid' OR staffB LIKE '$sid' OR staffC LIKE '$sid' OR staffD LIKE '$sid' ORDER BY batch desc";
        $res=$con->query($sql);
        $drop=array();
        while($row=$res->fetch_assoc())
        {
           
            array_push($drop,$row);
        }
        echo json_encode($drop);
    }
    else if(isset($_POST["s1c1"]))
    {
         $code=$_POST["s1c1"];
         $row=$con->query("SELECT * from course_list where code LIKE '$code' AND ( staffA LIKE '$sid' OR staffB LIKE '$sid' OR staffC LIKE '$sid' OR staffD LIKE '$sid' )")->fetch_assoc();
         $b=intval($row['batch'])%2000;
         $c=strtolower($row['dept']);
         if($row["staffA"]==$sid)
        {
            $sec="a";
        }
        if($row["staffB"]==$sid)
        {
            $sec="b";
        }
        if($row["staffC"]==$sid)
        {
            $sec="c";
        }
        if($row["staffD"]==$sid) 
        {
            $sec="d";
        }

        $c1=$row['dept'];
        $b1=$row['batch'];
        $d1='staff'.strtoupper($sec);
        $ss2="SELECT code,name,`$d1` from course_list where dept LIKE '$c1' AND batch LIKE '$b1' AND `$d1` IS NOT NULL AND `$d1` NOT LIKE '$sid'";
     
        $ref=$con->query($ss2);
        $s2=array();
        $s3=array();
        while($rs=$ref->fetch_assoc())
        {
           
            $stfid=$rs[$d1];
            $stfname=$con->query("SELECT name from staff where staffid LIKE '$stfid'")->fetch_assoc()['name'];
            if(!array_key_exists($stfid,$s2))
            {
                $s2+=array($stfid=>array(array($stfname,$rs["code"],$rs["name"])));
            }
            else
            {
                $s2[$stfid][1]=array($stfname,$rs["code"],$rs["name"]);
            }
        }
        
        $tab=$b."-".$c."-".$sec;
        
        
        $sql="SELECT * FROM `ott` WHERE `class` LIKE '$tab'";

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
        $ott=$day_per;
        
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
        $tt=$day_per;
        
         $sql="SELECT * FROM `tt_8-10` WHERE `class` LIKE '$tab'";

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
        $tt_new=$day_per;
        $sql="SELECT * FROM `tt_30-11` WHERE `class` LIKE '$tab'";
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
       $tt_lab1=$day_per;

       $sql="SELECT * FROM `tt_07-12` WHERE `class` LIKE '$tab'";
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
      $tt_lab2=$day_per;    
         $x=date("Y-m-d");
         $tdy=date_create($x);
         $date=date("2020-07-08");
         $diff=intval(date_diff($tdy,date_create($date))->format("%a"))+1;
         $diff+=30;
         $dates=array();

        
         for($i=1;$i<=$diff;$i++)
         {    
              $s=date("l", strtotime($date));
              if(date($date)>date("2020-12-06"))
              {
                   $day_per=$tt_lab2;
              }
              else if(date($date)>date("2020-11-29"))
              {
                   $day_per=$tt_lab1;
              }
          else if(date($date)>date("2020-10-07"))
               {
                    $day_per=$tt_new;
               }
               else if(date($date)<date("2020-08-03"))
               {
                    $day_per=$ott;
               }
               else
               {
                    $day_per=$tt;
               }
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
//           if(in_array($code,array("14CSL71","14CSL72")))
//           {
//                $c=$dates;
//                $dates=array();
//                foreach($c as $dt)
//                {
//                     $x=date_diff(date_create(date("2020-07-08")),date_create(date($dt)))->format("%a");
//                     if(($code=="14CSL71")&&(($x/7)%2))
//                     {
//                          array_push($dates,$dt);
//                     }
//                     else if(($code=="14CSL72")&&((($x/7)%2)==0))
//                     {
//                          array_push($dates,$dt);
//                     }
                    
//                }
//           }
          $altsql="SELECT date,period FROM `alteration` WHERE `s1` LIKE '$sid' AND `c1` LIKE '$code' AND date<=CURRENT_DATE";
          $res=$con->query($altsql);
          $alt=array();
          while($row=$res->fetch_assoc())
          { 
               $alt+=array($row["date"]=>explode(",",$row["period"]));   
          }  
          if(empty($alt))
          {
               $alt="Empty";
          }  
          $alted="SELECT date,period FROM `alteration` WHERE `s2` LIKE '$sid' AND `c2` LIKE '$code' AND date<=CURRENT_DATE";
          $res=$con->query($alted);
          $alted=array();
         
          while($row=$res->fetch_assoc())
          { 
               $per=array();
               $dated= $row["date"];
               $bv=explode(",",$row["period"]);
               foreach($bv as $periods)
               {
                    if(in_array($code,$ele))
                    { 
                         $sql="SELECT * FROM `$code` where date LIKE '$dated' AND code LIKE '$sid' AND `period` LIKE '$periods'"; 
                    }
                    else
                    {
                         $sql="SELECT * FROM `$tab` where date LIKE '$dated' AND code LIKE '$code' AND `period` LIKE '$periods'"; 
                    }
                    
                                           
                    $r=$con->query($sql);
                    if($r->num_rows==0)
                    {    
                         array_push($per,$periods);
                    } 
               } 
               if(!empty($per))
              {
                    $alted+=array($row["date"]=>$per);
              }   

          }  
          if(empty($alted))
          {
               $alted="Empty";
          }  
         $re=($con->query("SELECT * FROM `course_list` WHERE `code` LIKE '$code'"))->fetch_assoc();
         $dept=$re["dept"];
         $bat=$re["batch"];
         if($dept=="MCSE")
         {
              $dept='CSE';
              $bat="2020";
         }
         $holid=array();
    
         $res2=$con->query("select * from `holiday` where `dept` LIKE '$dept' AND `year` like '$bat'");
         while($row=$res2->fetch_assoc())
         {
               array_push($holid,$row["date"]);
         }
          echo json_encode(array($dates,$tt,$alt,$alted,$s2,$holid,$ott,$tt_new,$tt_lab1,$tt_lab2));
          exit();
    }
    else if(isset($_POST["holidays"]))
    {
         $dept=$con->query("SELECT * from `staff` where `staffid` like '$sid'")->fetch_assoc()["dept"];
         $com=$_POST["comment"];
         $type=$_POST["type"];
         foreach($_POST["year"] as $y)
          {
          foreach($_POST["holidays"] as $hol)
          {
               
               $hol =date('Y-m-d', strtotime(str_replace('/', '-',$hol)));
               
              $sql="INSERT INTO `holiday`(`date`, `dept`, `year`, `type`, `comments`) VALUES ('$hol','$dept','$y','$type','$com')";
               if(!($con->query($sql)))
               {
                  
                    echo 'failed';
                    return;
               }
          }
          }
          echo 'success';
          exit();
    }
?>
