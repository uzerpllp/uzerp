<?php

/**
 *    PDF Tools
 *
 *    @author uzERP LLP and Steve Blamey <sblamey@uzerp.com>
 *    @license GPLv3 or later
 *    @copyright (c) 2018 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *    uzERP is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    any later version.
 */

class PDFTools
{

    /**
     * Append PDF files
     * 
     * Use qpdf to merge PDF files
     *
     * @param string $file, the file to append
     * @param string $output, the file to append to
     */
    public function append($file, $output)
    {

        if (!file_exists($file)) {
            return false;
        }

        $command = 'qpdf --empty --pages %s %s -- %s';
        $append = '';

        // if the output file exists, we want to start appending
        if (file_exists($output)) {
            $append = $output;
        }

        // use a different name to output the file, to prevent conflicting paths
        $output = $output . '.cat';

        $command = sprintf($command, $append, $file, $output);
        exec($command);

        if (file_exists($output)) {
            return rename($output, substr($output, 0, -4));
        } else {
            return false;
        }

    }
}
