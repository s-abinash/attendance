<?php
    include_once("../db.php");
    session_start();
    $sid=$_SESSION["id"];
    $ele=array("14CSE06","14CSE11","14CSO07","14ITO01","18ITO02","18MEO01");
      
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
         $diff+=30;
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
          if(in_array($code,array("14CSL71","14CSL72")))
          {
               $c=$dates;
               $dates=array();
               foreach($c as $dt)
               {
                    $x=date_diff(date_create(date("2020-07-08")),date_create(date($dt)))->format("%a");
                    if(($code=="14CSL71")&&(($x/7)%2))
                    {
                         array_push($dates,$dt);
                    }
                    else if(($code=="14CSL72")&&((($x/7)%2)==0))
                    {
                         array_push($dates,$dt);
                    }
                    
               }
          }
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

          echo json_encode(array($dates,$day_per,$alt,$alted,$s2));
          exit();
    }
?>