<?php
/* Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

use Icinga\Module\Toplevelview\Tree\TLVTreeNode;
use Icinga\Web\Url;

class Zend_View_Helper_Tiles extends Zend_View_Helper_Abstract
{
    /** @var \Icinga\Web\View */
    public $view;

    public function tiles(TLVTreeNode $node, $levels = 2, $classes = array())
    {
        $htm = '';
        $classes[] = 'tlv-status'; // TODO
        $classes[] = 'missing'; // TODO

        $title = $this->view->escape($node->getTitle());

        $cssClasses = join(' ', $classes);
        $htm .= "<div class=\"tlv-tile $cssClasses\" title=\"$title\" data-base-target=\"_next\">\n";
        $htm .= $this->view->qlink(
            $title,
            'toplevelview/show/tree',
            array(
                'name' => $node->getRoot()->getConfig()->getName(),
                'id'   => $node->getFullId()
            ),
            array(
                'class' => 'tlv-tile-title'
            ),
            false
        );

        if ($levels > 1 && $node->hasChildren()) {
            foreach ($node->getChildren() as $child) {
                $htm .= $this->tiles($child, $levels - 1, $classes);
            }
        }

        $htm .= "</div>\n";
        return $htm;
    }
}
