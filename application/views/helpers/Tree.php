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
        $classes[] = 'tlv-status'; // TODO
        $classes[] = 'missing'; // TODO

        $title = $this->view->escape($node->getTitle());
        $type = $node->getType();

        $icon = 'right-dir';
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
            $url = Url::fromPath(
                'toplevelview/show/tree',
                array(
                    'name' => $node->getRoot()->getConfig()->getName(),
                    'id'   => $node->getFullId()
                )
            );
        }

        $cssClasses = join(' ', $classes);
        if ($type !== 'node') {
            $htm .= $this->view->qlink(
                $title,
                $url,
                null,
                array(
                    'icon'             => $icon,
                    'data-base-target' => '_next',
                    'class'            => "tlv-node-icinga tlv-node-$type $cssClasses" . $cssClasses,
                )
            );
        } else {
            $htm .= "<div class=\"tlv-tree-node $cssClasses\" title=\"$title\">";
            $htm .= $this->view->qlink(
                $title,
                $url,
                null,
                array(
                    'icon'  => $icon,
                    'class' => 'tlv-tree-title'
                )
            );
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
