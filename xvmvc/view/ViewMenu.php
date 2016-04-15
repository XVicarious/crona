<?php
/**
 * Created by PhpStorm.
 * User: bmaurer
 * Date: 4/11/2016
 * Time: 12:04 PM
 */

namespace xvmvc\view;

class ViewMenu extends View
{
    public function output()
    {
        $output = '<ul id="nav-mobile" class="side-nav fixed" style="overflow: hidden">';
        foreach ($this->model->entries as $entry) {
            $output .= "<li class=\"bold\"><a href=\"$entry[2]\" class=\"$entry[1]\">$entry[0]</a></li>";
        }
        $output .= '</ul>';
        return $output;
    }
}
