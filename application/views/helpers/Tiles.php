<?php
/* Icinga Web 2 Top Level View | (c) 2017 Icinga Development Team | GPLv2+ */

use Icinga\Module\Toplevelview\Tree\TLVTreeNode;

class Zend_View_Helper_Tiles extends Zend_View_Helper_Abstract
{
    /** @var \Icinga\Web\View */
    public $view;

    /**
     * tiles renders a TLVTreeNode into a HTML TLV tile
     */
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
        $statusClasses = [$statusClass, $status->getOverall()];

        $htm .= sprintf(
            '<div class="tlv-tile %s" title="%s" data-base-target="_next">' . "\n",
            join(' ', $classes + $statusClasses),
            $title
        );
        $badges = $this->view->badges($status);

        $htm .= $this->view->qlink(
            $title . $badges,
            'toplevelview/show/tree',
            [
                'name' => $node->getRoot()->getViewName(),
                'id'   => $node->getFullId()
            ],
            [
                'class' => 'tlv-tile-title'
            ],
            false
        );

        if ($levels > 1 && $node->hasChildren()) {
            $htm .= '<div class="tlv-tiles">';
            foreach ($node->getChildren() as $child) {
                $htm .= $this->tiles($child, $levels - 1, $classes);
            }
            $htm .= '</div>';
        }

        $htm .= "</div>\n";
        return $htm;
    }
}
