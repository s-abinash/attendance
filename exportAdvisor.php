<?php
session_start();

if(!isset($_SESSION['id']))
{
    header('Location: index.html');
}
include_once("./db.php");
include_once("./AJAX/header.php");
$sid=$_SESSION["id"];
$res=$con->query("SELECT * FROM staff where `staffid` LIKE '$sid'")->fetch_assoc();
if($res["designation"]!=="Advisor")
{
    header('Location: home.php');
}
else
{
    
    $b=$res["batch"];
    $s=$res["sec"];
    $dept=$res['dept'];
    $batch=intval($b)%2000;
    $Class=($b==2017?'IV':($b==2018?'III':'II')).'-'.$s;
    if($b==2020)
    {
        $dept='ME';
        $sec='-';
        $Class="ME";
    }
    $table=strval($batch).'-'.strtolower($res['dept']).'-'.strtolower($res['sec']);
}
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export</title>
    <script src="./assets/jquery.min.js"></script>
    <script src="./assets/Fomantic/dist/semantic.min.js"></script>
    <link rel="icon" type="image/png" href="./images/KEC.png">

    <?php include_once('./assets/notiflix.php'); ?>
    <style>
    .ui.table thead tr:first-child>th {
        position: sticky !important;
        top: 0;
        z-index: 2;
    }
 
    
    </style>

</head>

<body id="root">
    <?php
include_once('./navbar.php');
?>
    <style>
    body {
        background: url("./images/bgpic.jpg");
    }
    </style>


<?php
if(!isset($_POST["fetch"]))
{

        echo '<div class="card-1" style="margin-top:10%;">
                <div class="ui raised padded container segment" id="card" style="margin:auto;width:60%;">
                    <center>
                        <h1 class="header">
                            Export Attendance Report
                        </h1>
                    </center>
                    <br>
                    <form class="ui form" name="upload" action="exportAdvisor.php" method="post" autocomplete="off" >
                    <div class="two fields">
                        <div class="field">
                        <label>Start date</label>
                        <div class="ui calendar" id="rangestart">
                            <div class="ui input left icon">
                            <i class="calendar icon"></i>
                            <input type="text" name="start" placeholder="Start">
                            </div>
                        </div>
                        </div>
                        <div class="field">
                        <label>End date</label>
                        <div class="ui calendar" id="rangeend">
                            <div class="ui input left icon">
                            <i class="calendar icon"></i>
                            <input type="text"  name="end" placeholder="End">
                            </div>
                        </div>
                        </div>
                    </div>
                   
                    <br>
                    
                        <div class="field">
                            <center><button class="ui positive button" type="submit" name="fetch">Fetch</button></center>
                        </div>
                    </form>
                </div>
            </div>';

}



if(isset($_POST["fetch"]))
{
    $rolls=array();
    echo '<script>
    $(document).ready(function(){
        $(".card-1").css("display", "none");
    });
    $(document).ready(function(){
        $(".card-2").css("display", "");
    });
    </script>';

    echo '<div class="card-2"><div class="ui header" style="text-align:center;font-size:30px;margin-top:2%;color:#ADEFD1FF">Export Attendance
        </div>
        <div class="ui message" style="text-align:center;width:80%;margin: 0 auto;">
            <div class="ui header"> Class : '.$Class.
            '</div>
        </div><br />
        <div class="ui raised segment"
            style="height:90%;width:90%;overflow:auto;margin:0 auto;margin-bottom:3%;padding:2%;">
            <table class="ui violet selectable striped table" id="export">
                <thead>
                    <tr id="Dates" style="text-align:center">
                        <th>Register Number</th>
                        <th>Name</th>
                    </tr>
                
                </thead>
                <tbody style="text-align:center">
                    <tr id="Periods" style="text-align:center">
                        <td></td>
                        <td ><b>Periods</b></td> 
                    </tr>';
    
                $sql="SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N'$table'";
                $res=$con->query($sql);
                while($row=$res->fetch_assoc())
                {
                    if($row["COLUMN_NAME"]!='code' && $row["COLUMN_NAME"]!='date' && $row["COLUMN_NAME"]!='period'&& $row["COLUMN_NAME"]!='type')
                    {
                        $roll=$row["COLUMN_NAME"];
                        $sql="SELECT name FROM `registration` where regno LIKE '$roll';";
                        $reg=$con->query($sql)->fetch_assoc()["name"];
                        array_push($rolls,$roll);
                        echo "<tr class='' id=".$row["COLUMN_NAME"]."><td style='text-indent:15px'>".$row["COLUMN_NAME"]."</td><td style='text-align:left'>".$reg."</td></tr>";
                    }    
                }

                echo '</tbody>
                
                </table></div>';

                echo '<br />
                <div class="ui raised segment"style="height:90%;width:90%;overflow:auto;margin:0 auto;margin-bottom:3%;padding:2%; display: none;">
                    <table class="ui violet selectable striped table" id="abtable">
                        <thead>
                            <tr style="text-align:center">
                            
                                <th>Date</th>
                                <th>Period</th>
                                <th>Absentees</th>
                            </tr>
                        
                        </thead>
                        <tbody  id="ab_body">
                        <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        </tr>
                        </tbody>
                        </table></div>';
      
    $st=1;
    $e=5;
    $sql="SELECT `code` FROM `course_list` where `status` LIKE 'active' AND `category` LIKE 'elective' and `batch` like '$b'";
    $res=$con->query($sql);
    $elec=array();
    while($row=mysqli_fetch_array($res))
    {
        array_push($ele,$row['code']);
    }
    $dt= explode("/",$_POST["start"]);
    $start=$dt[2].'-'.$dt[1].'-'.$dt[0];
    $dt= explode("/",$_POST["end"]);
    $end=$dt[2].'-'.$dt[1].'-'.$dt[0];

    $x=date("Y-m-d",strtotime($end));
    $tdy=date_create($x);
    $date=date("Y-m-d",strtotime($start));
    $diff=intval(date_diff($tdy,date_create($date))->format("%a"))+1;
    for($i=1;$i<=$diff;$i++)
    { 
        $day=date("l",strtotime($date));
        if(!in_array($day,array("Sunday")))
        {

            if($con->query("select * from holiday where `date` LIKE '$date' AND `year` LIKE '$b' AND  `dept` LIKE '$dept'  and `type` like 'Holiday'")->num_rows!=0)
            {
                $date=date_format(date_add(date_create($date),date_interval_create_from_date_string("1 days")),"Y-m-d");
                continue;
            }
            
            for($p=$st;$p<=$e;$p++)   
            {
                echo "<script>$('#Dates').append('<th >".date("d/m",strtotime($date))."</th>')</script>";
                $bool=0;
                $sql="SELECT * FROM `$table` WHERE `date` LIKE '$date' AND `period` LIKE '$p'";
                $res=$con->query($sql);
                if($res->num_rows==1)
                {
                    $ab=array();
                    $row=$res->fetch_assoc();
                    foreach($row as $ind=>$val)
                    {
                        if(($ind=="date") || ($ind=="code") || ($ind=="period")|| ($ind=="type"))
                        {
            
                            if($ind=="period")
                            {
                                echo "<script>$('#Periods').append('<th>".$val."</th>')</script>";
                            }
                            continue;
                        }
                        else
                        {
                            echo "<script>$('#".$ind."').append('<td>".$val."</td>')</script>";
                            if($val=="A")
                            {
                               array_push($ab,$ind);
                            }
                        }  
                    }
                   $var= (count($ab)!=0)?implode(" , ",$ab):" NIL ";
                   
                   echo "<script>$('#ab_body').append('<tr><td>".date("d/m",strtotime($date))."</td><td>".$p."</td><td>".$var."</td></tr>');</script>";
                   if($p==$e)
                   {
                    echo "<script>$('#ab_body').append('<tr><td></td><td></td><td></td></tr>');</script>";
                   }
                }
                else
                {
                  
                    $results=array();
                    $ab=array();
                    foreach( $elec as $tab)
                    {
                        $stf="staff".$s;
                        $sl="SELECT `$stf` FROM `course_list` WHERE code LIKE '$tab'";
                    
                        $code=($con->query($sl)->fetch_assoc()["$stf"]);
                        
                       
                        $sql="SELECT * FROM `$tab` where code LIKE '$code' AND date LIKE '$date' AND period LIKE '$p'";
                        $res=$con->query($sql);
                        if($res->num_rows!=0)
                        {
                            while($row=$res->fetch_assoc())
                            {
                                $results+=$row;
                            }
                        }  
                    }
                    echo "<script>$('#Periods').append('<th>".$p."</th>')</script>";
                    $d=0;
                    foreach($rolls as $roll)
                    {
                        if(array_key_exists($roll,$results))
                        {
                            $d+=1;
                            echo "<script>$('#".$roll."').append('<td>".$results[$roll]."</td>')</script>";
                            if($results[$roll]=="A")
                            {
                                array_push($ab,$roll);
                            }
                        }
                        else 
                        {
                            echo "<script>$('#".$roll."').append('<td>".'NE'."</td>')</script>";
                        }
                    }
                    $var= (count($ab)!=0)?implode(" , ",$ab):" NIL ";
                    if($d==count($rolls))
                    {
                        
                        echo "<script>$('#ab_body').append('<tr><td>".date("d/m",strtotime($date))."</td><td>".$p."</td><td>".$var."</td></tr>');</script>";
                       
                    }
                    else{
                        echo "<script>$('#ab_body').append('<tr><td>".date("d/m",strtotime($date))."</td><td>".$p."</td><td><b> Waiting for Entry  </b>".$var."</td></tr>');</script>";
                    }
                    if($p==$e)
                    {
                     echo "<script>$('#ab_body').append('<tr><td></td><td></td><td></td></tr>');</script>";
                    }


                    
                } 
               
            }
        }
        $date=date_format(date_add(date_create($date),date_interval_create_from_date_string("1 days")),"Y-m-d");
    }
}

?>
    <script>
        $("#Dates").append(
            "<th>No.of.Hrs<br></br>Present</th><th>No.of.Hrs<br></br>Absent</th><th>Total Working Hrs</th><th>Not Updated</th><th>Attendance <br></br>Percentage</th>"
        );
        var P, A, T,N, per;

        $("#export > tbody  > tr").each(function(index, tr) {
            if(index==0)
            {
                $("#" + this.id).append("<td>" + 'N/A' + "</td><td>" + 'N/A' + "</td><td>" + 'N/A' + "</td><td>" + 'N/A' + "</td><td>" + 'N/A' + "</td>");
                return;
            }

            P = $('#' + this.id + ' td:contains("P")').length;
            A = $('#' + this.id + ' td:contains("A")').length;
            N = $('#' + this.id + ' td:contains("NE")').length;
            T = $('#' + this.id + ' td').length - 2-N;
            if (($('#' + this.id + ' td:nth-child(2):contains("P")').length) && P) {
                P -= 1;
            }
            if (($('#' + this.id + ' td:nth-child(2):contains("A")').length) && A) {
                A -= 1;
            }
            per = parseInt(((P / T) * 100), 10);
            if (per < 80) {
                $("#" + this.id).addClass("red");
            }
            $("#" + this.id).append("<td>" + P + "</td><td>" + A + "</td><td>" + T + "</td><td>" + N + "</td><td>" + per + "%</td>");
        });
    </script>
    <script>
    $(document).ready(function() {
            var tdy = new Date();
            var min ,ini;
            var bat="<?php echo $b ?>";
            if(bat==2017)
            {
                min=new Date('2021','00','02');
                ini=new Date('2021','00','02');
            }
            else if(bat==2018)
            {
                min=new Date('2021','00','18');
                ini=new Date('2021','00','18');
            }
            else if(bat==2019)
            {
                min=new Date('2021','02','03');
                ini=new Date('2021','02','03');
            }
            else if(bat==2017)
            {
                min=new Date('2021','00','04');
                ini=new Date('2021','00','04');
            }
            $('#rangestart').calendar({
                type: 'date',
                minDate: min,
                initialDate: ini,
                maxDate: tdy,
                disabledDaysOfWeek: [0],
                formatter: {
                    date: function(date, settings) {

                        if (!date) return '';
                        var day = date.getDate();
                        var month = date.getMonth() + 1;
                        var year = date.getFullYear();
                        return day + '/' + month + '/' + year;
                    }
                },
                endCalendar: $('#rangeend')
            });
            $('#rangeend').calendar({
                type: 'date',
                maxDate: tdy,
                initialDate: tdy,
                disabledDaysOfWeek: [0],
                formatter: {
                    date: function(date, settings) {

                        if (!date) return '';
                        var day = date.getDate();
                        var month = date.getMonth() + 1;
                        var year = date.getFullYear();
                        return day + '/' + month + '/' + year;
                    }
                },
                startCalendar: $('#rangestart')
            });
      
    });
    

    $(document).ready(function() {
       
       var abtab=$("#abtable").DataTable({
        "paging": false,
            "ordering": false,
            "info": false,
        buttons: [{
                    extend: 'excelHtml5',
                    title: 'KEC Student+ Export',
                    text: 'Absentees List',
                    attr: { id: 'abs' }
                }],
       });
        var table = $('#export').DataTable({
            "paging": false,
            "ordering": false,
            "info": false,
            lengthChange: false,
            buttons: ['copy',
                {
                    extend: 'excelHtml5',
                    title: 'KEC Student+ Export',
                }, 'print',
                
                {
                    extend: 'pdfHtml5',
                    download: 'open',
                    pageSize: 'A4',
                    orientation: 'landscape',
                    "paging": true,
                    "autowidth": true,
                    title: 'KEC Student+ Export',

                    customize: function(doc) {
                        // Splice the image in after the header, but before the table

                        var cols = [];
                        cols[0] = {
                            text: ' This document has been generated from KEC Student+ . \u00A9Kongu Engineering College.',
                            alignment: 'left',
                            margin: [20]
                        };
                        cols[1] = {
                            text: 'http://student.kongu.edu/',
                            alignment: 'right',
                            margin: [0, 0, 20]
                        };
                        var objFooter = {};
                        objFooter['columns'] = cols;
                        doc['footer'] = objFooter;

                        var now = new Date();
                        var jsDate = now.getDate() + '-' + (now.getMonth() + 1) + '-' + now
                            .getFullYear();
                        doc['header'] = (function(page, pages) {
                            return {
                                columns: [{
                                        alignment: 'left',
                                        text: ['Generated on: ', {
                                            text: jsDate.toString()
                                        }]
                                    },
                                    {
                                        alignment: 'right',
                                        text: ['page ', {
                                            text: page.toString()
                                        }, ' of ', {
                                            text: pages.toString()
                                        }]
                                    }
                                ],
                                margin: 20
                            }
                        });

                        doc.content.splice(0, 0, {
                            margin: [0, 0, 0, 12],
                            alignment: 'center',
                            image: 'data:image/png;base64,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',
                            fit: [70, 70],


                        });
                    }

                }, {
                    extend: 'colvis',
                    text: 'Visibility',
                },
                {
                text: 'Absentees List',
                action: function ( e, dt, node, config ) {
                    $("#abs").click();
                }
            }
                
            
                
            ]
        });
        
        table.buttons().container().appendTo($('div.eight.column:eq(0)', table.table().container()));
        abtab.buttons().container().appendTo($('div.eight.column:eq(0)', abtab.table().container()));
        $('body')
            .toast({
                position: 'bottom right',
                title: 'Info',
                class: 'warning',
                displayTime: 'auto',
                closeIcon: true,
                showIcon: true,
                message: 'This page may not work properly, since there was a change in attendance.',
                showProgress: 'top'
            });          
    });
    </script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/dataTables.semanticui.min.css">
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/dataTables.semanticui.min.js"></script>

    <!-- Export using Datatables -->
    <link rel="stylesheet" type="text/css"
        href="https://cdn.datatables.net/buttons/1.6.2/css/buttons.semanticui.min.css">
    <script src="https://cdn.datatables.net/buttons/1.6.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.2/js/buttons.semanticui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.2/js/buttons.colVis.min.js"></script>

</div>
</body>

</html>
