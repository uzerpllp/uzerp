<?php

/**
 * Remove copyright comment from uzERP .php source files
 * 
 * Assumes a list of file paths will be passed on the commandline.
 */

foreach (array_slice($argv, 1) as $file) {
    $fileStr = file_get_contents($file);
    $newStr  = '';

    $commentTokens = array(T_DOC_COMMENT);
    $tokens = token_get_all($fileStr);

    foreach ($tokens as $token) {
        if (is_array($token)) {
            if (in_array($token[0], $commentTokens)) {
                if (str_contains($token[1], 'Released under GPLv3 license; see LICENSE')) {
                    $comment_removed = true;
                    continue;
                }
            }
            $token = $token[1];
        }

        $newStr .= $token;
    }

    file_put_contents($file, $newStr);
}
