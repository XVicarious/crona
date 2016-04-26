<?php

namespace xvmvc\view;

/**
 * Class ViewMenu
 * @package xvmvc\view
 */
class ViewMenu extends View
{
    /**
     * @return string
     */
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
