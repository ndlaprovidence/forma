<?php

namespace App\Util;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;

class FormaHelper
{
    public function clearFolder($path)
    {
        // Clear documents folder before creating new one
        $finder = new Finder();
        $filesystem = new Filesystem();

        $finder->files()->in($path);
        
        foreach ($finder as $file) {
            $fileNameWithExtension = $file->getRelativePathname();
            $filesystem->remove([$path.'/'.$fileNameWithExtension]);
        }
    }


}