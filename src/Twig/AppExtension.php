<?php

namespace App\Twig;

use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;
use Symfony\Component\Validator\Constraints\DateTime;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('date_minus', [$this, 'dateMinus']),
        ];
    }

    public function dateMinus(\DateTimeInterface $dateAmSrc, \DateTimeInterface $dateAmToMinus, \DateTimeInterface $datePmSrc, \DateTimeInterface $datePmToMinus)
    {
        $diffAm = $dateAmSrc->diff($dateAmToMinus);
        $diffAm =  explode(" ",$diffAm->format('%h %i'));
        $hours = intval($diffAm[0]);
        $minutes = $diffAm[1];
        

        $diffPm = $datePmSrc->diff($datePmToMinus);
        $diffPm =  explode(" ",$diffPm->format('%h %i'));
        $hours = $hours + intval($diffPm[0]);
        $minutes = $minutes + $diffPm[1];

        $result = "RESULT : ".$hours." ".$minutes;

        if ( $minutes >= 60 ) {
            $hours++;
            $minutes = $minutes - 60;
        }

        if ( $minutes != 0 ) {
            if ( $minutes < 10 ) {
                $result = $hours. "h0" .$minutes;
            } else {
                $result = $hours. "h" .$minutes;
            }
        } else {
            $result = $hours. "h";
        }
        
        return $result;
    }
}