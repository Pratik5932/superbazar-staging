<?php
set_time_limit(0);
ini_set('memory_limit', '4096M');
         
$data = (file_get_contents('php://input'));
echo $data;
mail("er.bharatmali@gmail.com","Webhool",$data);