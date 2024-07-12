<?php
/* Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

use Icinga\Module\Toplevelview\Tree\TLVTreeNode;
use Icinga\Module\Toplevelview\Util\Str;

class Zend_View_Helper_Breadcrumb extends Zend_View_Helper_Abstract
{
    /** @var \Icinga\Web\View */
    public $view;

    /**
     * breadcrumb renders a list of TLVTreeNodes into a HTML breadcrumb list
     *
     * @param TLVTreeNode[] $breadcrumb
     *
     * @return string
     */
    public function breadcrumb($breadcrumb, $config_name)
    {
        $htm = '<ul class="breadcrumb">';
        foreach ($breadcrumb as $crumb) {
            $htm .= '<li>' . $this->view->qlink(
                Str::limit($crumb->getTitle()),
                'toplevelview/show/tree',
                array(
                        'name' => $config_name,
                        'id'   => $crumb->getFullId()
                    )
            ) . '</li>';
        }
        $htm .= '</ul>';
        return $htm;
    }
}
