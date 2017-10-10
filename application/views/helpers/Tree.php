<?php
/* Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

use Icinga\Module\Toplevelview\Tree\TLVTreeNode;
use Icinga\Web\Url;

class Zend_View_Helper_Tree extends Zend_View_Helper_Abstract
{
    /** @var \Icinga\Web\View */
    public $view;

    public function tree(TLVTreeNode $node, $classes = array())
    {
        $htm = '';
        $title = $node->getTitle();
        $type = $node->getType();

        if ($type === 'host') {
            $icon = 'host';
            $url = Url::fromPath(
                'monitoring/host/show',
                array(
                    'host' => $node->get('host')
                )
            );
        } elseif ($type === 'service') {
            $icon = 'service';
            $url = Url::fromPath(
                'monitoring/service/show',
                array(
                    'host'    => $node->get('host'),
                    'service' => $node->get('service')
                )
            );
        } elseif ($type === 'hostgroup') {
            $icon = 'cubes';
            $url = Url::fromPath(
                'monitoring/list/services',
                array(
                    'hostgroup' => $node->get('hostgroup'),
                    'sort'      => 'service_severity',
                    'dir'       => 'desc',
                )
            );
        } else {
            $icon = null;
            $url = Url::fromPath(
                'toplevelview/show/tree',
                array(
                    'name' => $node->getRoot()->getConfig()->getName(),
                    'id'   => $node->getFullId()
                )
            );
        }

        $status = $node->getStatus();
        $statusClass = $status->getOverall();

        $cssClasses = join(' ', $classes);
        if ($type !== 'node') {
            $htm .= $this->view->qlink(
                $title,
                $url,
                null,
                array(
                    'icon'             => $icon,
                    'data-base-target' => '_next',
                    'class'            => "tlv-node-icinga tlv-node-$type tlv-status-tile $statusClass $cssClasses",
                )
            );
        } else {
            $htm .= "<div class=\"tlv-tree-node tlv-status-section collapsible $statusClass $cssClasses\" title=\"$title\">";
            $htm .= '<div class="tlv-tree-title">';
            $htm .= '<i class="icon icon-bycss collapse-handle"></i>';
            $htm .= $this->view->qlink($title, $url);
            $htm .= '</div>';
            if ($node->hasChildren()) {
                foreach ($node->getChildren() as $child) {
                    $htm .= $this->tree($child, $classes);
                }
            }
            $htm .= '</div>';
        }

        return $htm;
    }
}
