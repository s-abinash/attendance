<?php
   echo '<script src="./assets/Notiflix/notiflix-2.5.0.min.js"></script>';
   echo '<link rel="stylesheet" href="./assets/Notiflix/notiflix-2.5.0.min.css">';
?>
<script type="text/javascript">
Notiflix.Notify.Init({
    distance: "20px",
    timeout: "5000",
    position: "right-bottom",
    fontSize: "20px",
    borderRadius: "10px",
    width: "300px",
});
Notiflix.Report.Init({
    width: "360px",
    titleFontSize: "25px",
    messageFontSize: "20px",
    buttonFontSize: "20px",
});
</script>