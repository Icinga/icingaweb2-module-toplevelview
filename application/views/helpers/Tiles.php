<?php
/* Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

use Icinga\Module\Toplevelview\Tree\TLVTreeNode;

class Zend_View_Helper_Tiles extends Zend_View_Helper_Abstract
{
    /** @var \Icinga\Web\View */
    public $view;

    public function tiles(TLVTreeNode $node, $levels = 2, $classes = array())
    {
        $htm = '';
        $title = $this->view->escape($node->getTitle());

        $status = $node->getStatus();
        if ($levels > 1) {
            $statusClass = 'tlv-status-section';
        } else {
            $statusClass = 'tlv-status-tile';
        }
        $statusClasses = array($statusClass, $status->getOverall());

        $htm .= sprintf(
            '<div class="tlv-tile %s" title="%s" data-base-target="_next">' . "\n",
            join(' ', $classes + $statusClasses),
            $title
        );
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
