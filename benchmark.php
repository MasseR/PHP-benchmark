<?php

/*
 Copyright (C) 2011 by Mats Rauhala

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE.
 */

function sum($array)
{
    return array_reduce($array, function($acc, $x) { return $acc + $x; });
}

function tsv($array) {
    return implode("\n",
        array_map(function($x) { return implode("\t", $x); }, $array));
}

function mkFilename($name)
{
    $name = strtolower($name);
    $name = preg_replace("/\s+/", "-", $name);
    return "$name.tsv";
}

function benchmarkRunner($name, $call)
{
    printf("Benchmarking '$name'\n");
    $n = 0;
    $times = array();
    echo "Calculating estimate\n";
    $start = microtime(true);
    for($i = 0; $i < 10; $i++)
        call_user_func($call);
    $est = (microtime(true) - $start) / 10;
    $iterations = min(15000, max(floor(60 / $est), 8000));
    $time = floor($est * $iterations);
    echo "$iterations iterations will take $time seconds\n";
    $start = microtime(true);
    echo "Starting benchmarks\n";
    while($n < $iterations) {
        $t1 = microtime(true);
        call_user_func($call);
        $t2 = microtime(true);
        $times[] = $t2 - $t1;
        $n++;
        echo "\r$n";
    }
    echo "\n";
    $mean = sum($times) / $n;
    $stdDev = sqrt(sum(array_map(function($x) use($mean) { return pow($x - $mean, 2); }, $times)) / $n);
    echo "Mean: $mean\n";
    echo "Standard deviation: $stdDev\n";
    $filename = mkFilename($name);
    file_put_contents(mkFilename($name), tsv(array_map(function($x) use($n) { return array($x, 1/$n); }, $times)));
    echo "Wrote $filename\n\n";
}

function benchmark(array $tests)
{
    foreach($tests as $name => $bench)
        benchmarkRunner($name, $bench);
}
