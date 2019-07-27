<?php
$path = './test.xls';
$file = fopen($path, 'r');
//标题行读取（第一行）
$row = fgets($file);

$row = explode("\t", $row);
$title = array();
foreach($row as $k => $v) {
  $title[$k] = str_replace("\n", '', $v);
}
//内容读取
$data = array();
$count = 0;
while(!feof($file)) {
  $row = fgets($file);
  $row = explode("\t", $row);
  if(!$row[0]) continue;//去除最后一行
  foreach($title as $k => $v) {
    $data[$count][$title[$k]] = $row[$k];
  }
  $count ++;
}
fclose($file);
echo '<pre>';
print_r($data);
