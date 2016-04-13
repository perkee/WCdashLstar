#!/usr/bin/env php
<?php

define('VERBOSE', false);
/**
 * Given a file or directory, return the length in lines of all files therein
 * @param string $fileOrDir The file or directory on filesystems
 * @param  array $seen array of full paths already accounted for
 * @return array where [
 *   'total' => length of all files in directory
 *   'seen'  => array of seen filenames
 */
function listFileLengths($path, $seen = [])
{
    $total = 0;
    
    $seenLinks = [];
    while (is_link($fileOrDir) && !in_array($fileOrDir, $seenLinks)) {
        $fileOrDir = readlink($fileOrDir);
    }
    
    if (!in_array($path, $seen)) {
        $seen[] = $path;
        if (is_dir($path)) {//will not get hit if all links were circular
        
            $filesInDir = scandir($path);//assume this returns an array of file names.
            
        
            foreach ($filesInDir as $child) {
                if ('.' == $child || '..' == $child) {
                      //don't do anything.
                } else {
                    $childPath = realpath("${path}/${child}");
                    $total += listFileLengths($childPath, $seen)['total'];
                }
            }
        } elseif (is_file($path)) {
            $linesInFile = linesInFile($path);
            if (VERBOSE) {
                echo "\t{$linesInFile}\t{$path}" . PHP_EOL;
            }
            $total += $linesInFile;
        }
    }
    return [
        'total' => $total,
        'seen'  => $seen
    ];
}

/**
 * Given a filename, return the length of the file in lines
 *
 * @param string $fileName name of a file (not a directory)
 * @return int lenght of file in lines
 */
function linesInFile($fileName = '')
{
    $lines = 0;
    if (isTextFile($fileName)) {
        $file = fopen($fileName, 'r');
        while (!feof($file)) {
            $line = fgets($file, 1024);
            $lines = $lines + substr_count($line, PHP_EOL);
        }

        fclose($file);
    }
    return $lines;
}

/**
 * Given a filename, determine if that file contains text or not
 *
 * @param  string $fileName name of file to check
 * @return boolean whether this file's mime corresponds to text
 */
function isTextFile($fileName = '')
{
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($fileName);
    return 0 === strncmp($mime, 'text', 4);
}

call_user_func(function () {
    $paths = [
      '../perk/public/',
      '.'
    ];
    foreach ($paths as $path) {
        echo listFileLengths($path)['total'] . "\t$path" . PHP_EOL;
    }
});
