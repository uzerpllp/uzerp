<?php

/**
 * Find stray smarty {debug} in commits
 * 
 * Assumes a list of file paths will be passed on the commandline.
 */

foreach (array_slice($argv, 1) as $file) {
    $fileStr = file_get_contents($file);
    // Test for false. The tag might be at the start of the file (0)!
    if (strpos($fileStr, '{debug}') !==false) {
        echo("Smarty debug tag found in file {$file}.\n");
        exit(1);
    }
}
