<?php
function insertionSort($arr) {
    for ($i = 1; $i < count($arr); $i++) {
        $key = $arr[$i];
        $j = $i - 1;
        
        echo ("Key is {$key}:\n");
        while ($j >= 0 && $arr[$j] > $key) {
            $arr[$j + 1] = $arr[$j];
            $temp = $arr[$j];
            $arr[$j] = $arr[$j + 1];
            $arr[$j + 1] = $temp;
            $j = $j - 1;
            $arr[$j + 1] = $key;

            if ($key < $arr[$j + 2]){
                echo ("\n{$arr[$j + 2]} > {$key}, So \033[31mSwap:\033[0m ");
            } else if ($key > $arr[$j + 2]){
                echo ("\n{$arr[$j + 2]} < {$key}, So \033[31mSwap:\033[0m ");
            }
 
            for ($p = 0; $p < count($arr); $p++){
                if ($p == $j + 1){
                   echo ("\033[31m {$arr[$p]} \033[0m  ");
                } else {
                   echo ("{$arr[$p]}  ");
                }
            }
            echo("\n\n");
        }
            if($j >= 0){
                echo ("\n{$arr[$j]} < {$key}, So \033[33mNo Swap:\033[0m  ");
            } else{
                echo ("\n{$key} is at correct position: \033[33mNo Swap:\033[0m  ");
            }

            for ($p = 0; $p < count($arr); $p++){
                echo ("{$arr[$p]}  ");
            }
            echo ("\n\n");
    }
    return $arr;
 }
 

echo "\n\n\t\t-----I N S E R T I O N   S O R T-----\n\n";
$size = (int)readline("Enter the size of the list: ");
$arr = [];

echo "Enter $size elements of the list:\n";
for ($i = 0; $i < $size; $i++) {
    $element = (int)readline();
    $arr[] = $element;
}

$sortedArr = insertionSort($arr);

echo "\n\n\nSorted array is: \x1b[32m" . implode(" ", $sortedArr) . "\x1b[0m";
?>
