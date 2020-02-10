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


    public function getHoursLength($dateAmSrc,$dateAmToMinus,$datePmSrc,$datePmToMinus)
    {
        $diffAm = $dateAmSrc->diff($dateAmToMinus);
        $diffAm =  explode(" ",$diffAm->format('%h %i'));
        $hours = intval($diffAm[0]);
        $minutes = $diffAm[1];
        

        $diffPm = $datePmSrc->diff($datePmToMinus);
        $diffPm =  explode(" ",$diffPm->format('%h %i'));
        $hours = $hours + intval($diffPm[0]);
        $minutes = $minutes + $diffPm[1];

        if ( $minutes >= 60 ) {
            $hours++;
            $minutes = $minutes - 60;
        }

        if ( $minutes != 0 ) {
            if ( $minutes < 10 ) {
                $result = $hours. " 0" .$minutes;
            } else {
                $result = $hours. " " .$minutes;
            }
        } else {
            $result = strval($hours);
        }
        
        return $result;
    }


    public function getHoursTotal($sessionLengthSrc, $sessionLengthToAdd)
    {
        $sessionLengthSrc =  explode(" ",$sessionLengthSrc);
        $hoursSrc = intval($sessionLengthSrc[0]);
        $minutesSrc = intval('00');

        if ( count($sessionLengthSrc) > 1 ) {
            $minutesSrc = intval($sessionLengthSrc[1]);
        }


        $sessionLengthToAdd =  explode(" ",$sessionLengthToAdd);
        $hoursToAdd = intval($sessionLengthToAdd[0]);
        $minutesToAdd = intval('00');

        if ( count($sessionLengthToAdd) > 1 ) {
            $minutesToAdd = intval($sessionLengthToAdd[1]);
        }

        $hours = $hoursSrc + $hoursToAdd;
        $minutes = $minutesSrc + $minutesToAdd;

        if ( $minutes >= 60 ) {
            $hours++;
            $minutes = $minutes - 60;
        }

        if ( $minutes != 0 ) {
            if ( $minutes < 10 ) {
                $result = $hours. " 0" .$minutes;
            } else {
                $result = $hours. " " .$minutes;
            }
        } else {
            $result = strval($hours);
        }   
        
        return $result;
    }


    public function formatHoursTotal($sessionLength)
    {
        $sessionLength =  explode(" ",$sessionLength);

        if ( count($sessionLength) > 1 ) {
            $result = $sessionLength[0]." heures et ".$sessionLength[1]." minutes";
        } else {
            $result = $sessionLength[0]." heures";
        }

        return $result;
    }
}