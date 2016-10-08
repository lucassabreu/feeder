<?php

namespace Core\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Helper for strip content for less data send
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
class StripContentHelper extends AbstractHelper {

    public function __invoke($content) {

        $return = "";
        $num = strpos($content, '<textarea');

        while ($num !== false) {
            $num = strpos($content, '>', $num);
            $before = substr($content, 0, ++$num);
            $before = str_replace(' &nbsp; ', "&nbsp;", str_replace('> <', '><', preg_replace('(\\s+)', ' ', $before)));

            $content = substr($content, $num);

            $num = strpos($content, '</textarea');
            $in = substr($content, 0, $num);
            $content = substr($content, $num);

            $return .= $before . $in;

            $num = '';
            $num = strpos($content, '<textarea');
            //echo ($num === false ? 'true' : 'false') . " <- watarel\n";
            //break;
        }

        $content = str_replace(' &nbsp; ', "&nbsp;", str_replace('> <', '><', preg_replace('(\\s+)', ' ', $content)));
        $return .= $content;

        return $return;
    }

}

?>
