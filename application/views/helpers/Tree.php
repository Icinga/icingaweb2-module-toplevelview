<?php
/* Icinga Web 2 Top Level View | (c) 2017 Icinga Development Team | GPLv2+ */

use Icinga\Module\Toplevelview\Tree\TLVTreeNode;
use Icinga\Web\Url;

class Zend_View_Helper_Tree extends Zend_View_Helper_Abstract
{
    /** @var \Icinga\Web\View */
    public $view;

    public function tree(TLVTreeNode $node, $classes = array(), $level = 0)
    {
        $htm = '';
        $htmExtra = '';
        $title = $node->getTitle();
        $type = $node->getType();

        $cssClasses = join(' ', $classes);

        $status = $node->getStatus();
        $statusClass = $status->getOverall();

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

            if (($h = $status->getMeta('hosts_unhandled')) > 0) {
                $hostTitle = '(<strong>'
                    . sprintf($this->view->translatePlural('%s unhandled host', '%s unhandled hosts', $h), $h)
                    . '</strong>)';
            } else {
                $h = $status->getMeta('hosts_total');
                $hostTitle = '(' . sprintf($this->view->translatePlural('%s host', '%s hosts', $h), $h) . ')';
            }

            $htmExtra .= ' ' . $this->view->qlink(
                $hostTitle,
                'monitoring/list/hosts',
                array(
                    'hostgroup' => $node->get('hostgroup'),
                    'sort'      => 'host_severity',
                    'dir'       => 'desc',
                ),
                null,
                false
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

        if ($type !== 'node') {
            $htm .= "<div class=\"tlv-node-icinga tlv-node-\$type tlv-status-tile action $statusClass $cssClasses\""
                . " data-base-target=\"_next\" href=\"$url\">";
            $htm .= $this->view->icon($icon) . ' ';
            $htm .= $this->view->qlink($title, $url);
            $htm .= $htmExtra;
            $htm .= ' ' . $this->view->badges($status, false);
            $htm .= '</div>';
        } else {
            $htm .= "<div class=\"tlv-tree-node tlv-status-section tlv-collapsible $statusClass $cssClasses\"";
            $htm .= " title=\"$title\">";
            $htm .= '<div class="tlv-tree-title">';
            $htm .= $this->view->badges($status, false, $level === 0 ? true : false);
            $htm .= '<i class="icon icon-bycss tlv-collapse-handle"></i> ';
            $htm .= $this->view->qlink($title, $url);
            $htm .= $htmExtra;
            $htm .= '</div>';
            if ($node->hasChildren()) {
                foreach ($node->getChildren() as $child) {
                    $htm .= $this->tree($child, $classes, $level + 1);
                }
            }
            $htm .= '</div>';
        }

        return $htm;
    }
}
