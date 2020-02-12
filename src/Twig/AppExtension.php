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
            new TwigFilter('sortByField', array($this, 'sortByField')),
            new TwigFilter('session_display', array($this, 'sessionDisplay')),
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


    public function sortByField($content, $sort_by, $direction = 'desc'){
        if (is_a($content, 'Doctrine\ORM\PersistentCollection')) {
            $content = $content->toArray();
        }
        if (!is_array($content)) {
            throw new \InvalidArgumentException('Variable passed to the sortByField filter is not an array');
        } elseif (count($content) < 1) { return $content; } else { @usort($content, function ($a, $b) use ($sort_by, $direction) { $flip = ($direction === 'desc') ? -1 : 1; if (is_array($a)) $a_sort_value = $a[$sort_by]; else if (method_exists($a, 'get' . ucfirst($sort_by))) $a_sort_value = $a->{'get' . ucfirst($sort_by)}();
                else
                    $a_sort_value = $a->$sort_by;
                if (is_array($b))
                    $b_sort_value = $b[$sort_by];
                else if (method_exists($b, 'get' . ucfirst($sort_by)))
                    $b_sort_value = $b->{'get' . ucfirst($sort_by)}();
                else
                    $b_sort_value = $b->$sort_by;
                if ($a_sort_value == $b_sort_value) {
                    return 0;
                } else if ($a_sort_value > $b_sort_value) {
                    return (1 * $flip);
                } else {
                    return (-1 * $flip);
                }
            });
        }
        return $content;
    }

    public function sessionDisplay($session){
        return $result = intval($session)+1;
    }
    
}