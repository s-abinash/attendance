
<?php 
    session_start();
    if(!isset($_SESSION['id']))
    {
        header('Location: index.html');
    }
    include_once("./db.php");
    $staffid=$_SESSION["id"];
    $res=$con->query("SELECT * FROM staff where `staffid` LIKE '$staffid'")->fetch_assoc();
    $dept=$res["dept"];
    include_once('navbar.php'); 
    include_once('./AJAX/header.php'); 
?>
<head>
<title>Pending Report</title>
</head>
<body>
<style>
    body {
        background: url("./images/bgpic.jpg");
    }
</style> 
    <div class="ui raised segment" style="width:96%;margin:2%;">

        <div class="ui header">Missing Attendance List
            <a href="home.php"><i class="close icon" style="float:right"></i></a>
        </div>
        <table class="ui celled table">
            <thead>
                <tr>
                    <th>Staff</th>
                    <th>Subject</th>
                    <th style="text-align:center">Class</th>
                    <th>Dates &emsp;&emsp;&emsp;&ensp;&nbsp;- &ensp;Periods</th>
                    <th>Inform</th>
                </tr>
            </thead>
            <tbody>
            <?php 
                    $sql="SELECT * FROM `staff` WHERE `dept` LIKE '$dept' ORDER BY `staffid` Asc";
                    $data=$con->query($sql);
                    $p=0;
                    $scnt=0;
                    $temp='';
                    while($row=mysqli_fetch_array($data))
                    {
                        $sid=$row['staffid'];
                        $sname=$row['name'];
                        $smail=$row['mail'];
                        $sql="SELECT * FROM `course_list` WHERE (`staffA`  LIKE '%$sid%' OR `staffB`  LIKE '%$sid%' OR `staffC`  LIKE '%$sid%' OR `staffD` LIKE '%$sid%' ) AND `status` LIKE 'active'";
                        $data1=$con->query($sql);  
                        $n=mysqli_num_rows($data1);
                        $bool=1;
                        $bol=0;
                        $cnt=0;
                        while($row1=mysqli_fetch_array($data1))
                        {
                              
                                $ssub=$row1['name'];
                                $code=$row1["code"];
                                $sec="";
                                if(substr_count($row1["staffA"],$sid))
                                {
                                    $sec="A";
                                }
                                else if(substr_count($row1["staffB"],$sid))
                                {
                                    $sec="B";
                                }
                                else if(substr_count($row1["staffC"],$sid))
                                {
                                    $sec="C";
                                }
                                else if(substr_count($row1["staffD"],$sid))
                                {
                                    $sec="D";
                                }

                                $year=$row1["batch"];
                                if($code=="18CEO02")
                                {
                                    $sec="A ".$sec;
                                }
                                $batch=$row1["batch"]%2000;
                                $cls=($batch==17?'IV':(($batch==18)?'III':'II')).' - '.$sec;
                                $dep=$dept;
                                if($row1['dept']=='ME')
                                {
                                    $dep='me';
                                    $year="2020";
                                    $cls='ME';
                                }
                                $tab=strtolower($batch.'-'.$dep.'-'.$sec);
                                $timetables=timetablesfn($con,$tab,$code,$project_array,$sid);
                                $x=date("Y-m-d");
                                $tdy=date_create($x);
                                if($year==2017)
                                    $date=date("2021-01-02");
                                else if(($year==2018))
                                    $date=date("2021-01-18");
                                else if(($year==2019))
                                    $date=date("2021-02-03");
                                else if ($year==2020) 
                                    $date=date("2021-01-04"); 
                                $diff=intval(date_diff($tdy,date_create($date))->format("%a"))+1;
                                
                                $dates=array();
                                for($i=1;$i<$diff;$i++)
                                {    
              
                                   
                                    if($con->query("select * from holiday where date LIKE '$date' AND `year` LIKE '$year' and `type` like 'Holiday'")->num_rows!=0)
                                    {
                                        $date=date_format(date_add(date_create($date),date_interval_create_from_date_string("1 days")),"Y-m-d");
                                        continue;
                                    }
                                   
                                    
                                    
                                    $alt=array();
                                    $result=$con->query("SELECT * FROM `alteration` where `date` LIKE '$date' AND `s2` LIKE '$sid' AND `c2` LIKE '$code'");
                                    if($result->num_rows!=0)
                                    {
                                        
                                        while($row=$result->fetch_assoc())
                                        {
                                            $prds=$row["period"];
                                            if(in_array($code,$ele))
                                                $sql="SELECT * FROM `$code` where date LIKE '$date' AND code LIKE '$sid' AND `period` LIKE '$prds'"; 
                                            else
                                                $sql="SELECT * FROM `$tab` where date LIKE '$date' AND code LIKE '$code' AND `period` LIKE '$prds'"; 
                                            $r=$con->query($sql);
                                            if($r->num_rows==0)
                                            {
                                                array_push($alt,$prds);
                                                $bol=1;
                                                $p+=1;
                                            }   
                                        }
                                    }
                                    $s=date("l",strtotime($date));
                                    $chan=$con->query("select * from `alter_tt_day` where `batch` like '%$year%' and `date` like '$date'");
                                    if($chan->num_rows!=0)
                                    {
                                            $s=$chan->fetch_assoc()["to_day"];
                                    }
                                    $day_pd=array();
                                    foreach ($timetables as $key => $value) {
                                        if((date($date)>=date($value["from"]))&&(date($date)<=date($value["to"])))
                                        {
                                             $day_per=$value["tt"];
                                             break;
                                        }
                                   }

                                    foreach($day_per as $d=>$pd)
                                    {
                                            if($d==$s)
                                            {
                                                
                                                foreach($pd as $periods)
                                                { 
                                                    if($con->query("select * from `holiday` where `date` LIKE '$date' AND `periods` like '%$periods%' AND  `dept` LIKE 'CSE' AND `year` like '$year' and `type` like 'Suspension'")->num_rows!=0)
                                                    {
                                                        $date=date_format(date_add(date_create($date),date_interval_create_from_date_string("1 days")),"Y-m-d");
                                                        continue;
                                                    } 
                                                    if(in_array($code,$ele))
                                                        $sql="SELECT * FROM `$code` where date LIKE '$date' AND code LIKE '$sid' AND `period` LIKE '$periods'"; 
                                                    else
                                                        $sql="SELECT * FROM `$tab` where date LIKE '$date' AND code LIKE '$code' AND `period` LIKE '$periods'"; 
                                                    $r=$con->query($sql);
                                                    if($r->num_rows==0)
                                                    {
                                                        if(($con->query("SELECT * FROM `alteration` WHERE `s1` LIKE '$sid' AND `c1` LIKE '$code' AND`period` LIKE '$periods' AND `date` like '$date' "))->num_rows==0)
                                                        {
                                                            $p+=1;
                                                            $bol=1;
                                                            array_push($day_pd,$periods);
                                                        }
                                                    }                               
                                                }
                                                
                                            }
                                    }
                                    if(!empty(array_merge($alt,$day_pd)))
                                    {
                                        $dates+=array($date=>array_merge($alt,$day_pd));
                                    }  
                                    $date=date_format(date_add(date_create($date),date_interval_create_from_date_string("1 days")),"Y-m-d");
                                }
                            
                            echo '<div class="bulleted list">';
                            $mailcontent="Dear ".$sname." Attendance entry is pending for '".$ssub. "' on the following dates:".'%0A%0A';
                            $datecell='';
                            foreach($dates as $i=>$pds)
                            {  
                                
                                $mailcontent.=date_format(date_create($i),"d-m-Y").'%20'.'-'.'%20'.implode(",",$pds).'%0A';
                                $datecell.=date_format(date_create($i),"d-m-Y").' &emsp;- &ensp;'.implode(",",$pds).'<br>';
                            }
                        
                            $mailcontent.="%0AKindly mark the attendance ASAP %0A %20 -"."HOD-CSE";
                            $mailcontent="mailto:".$smail."?subject=Attendance%20Pending%20report&body=".$mailcontent;
                            if(!empty($dates))
                            {
                                $cnt=1;    
                                echo '<tr>'.'<td><span style="color:red;">'.$sname.'</span></td>'.'<td><span style="color:blue">'.$ssub.'</span></td>'.'<td style="text-align:center"><span style="color:blue">'.$cls.'</span></td>'.'<td>'.$datecell.'</td>'.'<td><a href="'.$mailcontent.'" target="_blank">
                                <button class="ui violet button">
                                <i class="mail icon"></i> Send Mail
                                </button></a></td>'.'</tr>';
  
                            }
                   
                          
                        }
                        if($cnt==1)
                        {
                            $scnt+=1;
                        }
                     
                       
                    }
                    echo '</tbody>
                    <tfoot>
                        <tr>
                        <th><em>'.$scnt.' Staffs </em></th>
                        <th></th>
                        <th></th>
                        <th><em>'.$p.' Periods </em></th>
                        <th><em>Pending </em></th>
                    </tr></tfoot>';
                ?>
              
                </table>
        <div class="actions">
            <div class="ui bottom attached buttons">
            <div class="ui positive button" onclick="window.print()">Print</div>
            <div class="ui negative button" onclick="window.location.replace('home.php');">Close</div>
        </div>
</div>
</body>
</html>


