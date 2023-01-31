<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Util;


use Symfony\Component\HttpFoundation\File\File;

class TmpFile extends File {

public function __construct(string $contents)
{
    $path = tempnam(sys_get_temp_dir(), '');
    file_put_contents($path, $contents);
    parent::__construct($path);
}

}