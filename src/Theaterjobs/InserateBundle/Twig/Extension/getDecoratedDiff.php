<?php

namespace Theaterjobs\InserateBundle\Twig\Extension;

/**
 * Class getDecoratedDiff
 * @author Redjan Ymeraj <ymerajr@yahoo.com>
 * @package Theaterjobs\InserateBundle\Twig\Extension
 */
class getDecoratedDiff extends \Twig_Extension
{
    /**
     * (non-PHPdoc)
     * @see Twig_Extension::getFunctions()
     *
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'getDecoratedDiff' => new \Twig_Function_Method( $this, 'getDecoratedDiff', array( 'is_safe' => array('html') ) )
        );
    }

    public function getDecoratedDiff( $o, $n )
    {
        $ol = str_replace('>', '> ', $o);
        $old = str_replace('<', ' <', $ol);

        $ne = str_replace('>', '> ', $n);
        $new = str_replace('<', ' <', $ne);

        // If you want to remove the HTML tags from the Activity Log Preview
        /*
            $old = strip_tags( $o );
            $new = strip_tags( $n );
        */
        $ret = array(
            'old'   => '',
            'new'   => ''
        );

        $diff = $this->stringDiff(preg_split("/[\s]+/", $old), preg_split("/[\s]+/", $new));
        foreach($diff as $k){
            if(is_array($k)){
                if( !empty($k['d']) ){
                    $ret['old'] .= "<del style='background-color: #ff000d'>".implode(' ',$k['d'])."</del> ";
                } else {
                    $ret['old'] .= '';
                }

                if( !empty($k['i']) ){
                    $ret['new'] .= "<ins style='background-color: #0091ff'>".implode(' ',$k['i'])."</ins> ";
                } else {
                    $ret['new'] .= '';
                }
            } else {
                $ret['old'] .= $k . ' ';
                $ret['new'] .= $k . ' ';
            }
        }
        return $ret;
    }

    public function stringDiff($old, $new){
        $matrix = array();
        $maxlen = 0;
        foreach($old as $oindex => $ovalue){
            $nkeys = array_keys($new, $ovalue);
            foreach($nkeys as $nindex){
                $matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) ?
                    $matrix[$oindex - 1][$nindex - 1] + 1 : 1;
                if($matrix[$oindex][$nindex] > $maxlen){
                    $maxlen = $matrix[$oindex][$nindex];
                    $omax = $oindex + 1 - $maxlen;
                    $nmax = $nindex + 1 - $maxlen;
                }
            }
        }
        if($maxlen == 0) return array(array('d'=>$old, 'i'=>$new));
        return array_merge(
            $this->stringDiff(
                array_slice($old, 0, $omax),
                array_slice($new, 0, $nmax)
            ),
            array_slice($new, $nmax, $maxlen),
            $this->stringDiff(
                array_slice($old, $omax + $maxlen),
                array_slice($new, $nmax + $maxlen)
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'decorated_diff';
    }
}
