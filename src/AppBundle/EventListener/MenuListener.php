<?php
namespace AppBundle\EventListener;

// ...

use AppBundle\Entity\User;
use AppBundle\Entity\Groups;
use AppBundle\Entity\UserGroup;
use Avanzu\AdminThemeBundle\Model\MenuItemModel;
use Avanzu\AdminThemeBundle\Event\SidebarMenuEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

class MenuListener {

    protected $translator;
    protected $current_user;

    public function __construct(TranslatorInterface $translator, $user)
    {
        $this->translator = $translator;
        if($user instanceof User) {
            $this->current_user = $user;
        }
    }

    public function onSetupMenu(SidebarMenuEvent $event) {

        $request = $event->getRequest();

        foreach ($this->getMenu($request) as $item) {
            $event->addItem($item);
        }

    }

    protected function getMenu(Request $request) {
        // Build your menu here by constructing a MenuItemModel array
        $menuItems = [];
        $groups = new MenuItemModel('groupsid', $this->translator->trans('Password Groups'), 'groups', array(/* options */), 'iconclasses fa fa-object-group');
        $menuItems[] = $groups;


        /** @var Groups $group */
        /** @var UserGroup $usergroup */
        if($this->current_user) {
            $groups->addChild(
                new MenuItemModel('allgroups', 'All Groups', 'groups')
            );
            foreach ($this->current_user->getGroups() as $usergroup) {
                $group = $usergroup->getGroup();
                $groups->addChild(
                    new MenuItemModel(
                        'group_' . $group->getId(),
                        $group->getName(),
                        'logins',
                        ['groupid' => $group->getId()],
                        ''
                    )
                );
            }

            $groups->addChild(
                new MenuItemModel(
                    'new_group',
                    $this->translator->trans('New Group'),
                    'new_group',
                    [],
                    'fa fa-plus'
                )
            );
        }

        $menuItems[] = new MenuItemModel('export_all', $this->translator->trans('Export All Passwords'), 'export_logins');
/*
 *
        // Add some children

        // A child with an icon
        $groups->addChild(new MenuItemModel('ChildOneItemId', 'ChildOneDisplayName', 'child_1_route', array(), 'fa fa-rss-square'));

        // A child with default circle icon
        $groups->addChild(new MenuItemModel('ChildTwoItemId', 'ChildTwoDisplayName', 'child_2_route'));*/
        return $this->activateByRoute($request->get('_route'), $menuItems);
    }

    protected function activateByRoute($route, $items) {

        foreach($items as $item) {
            if($item->hasChildren()) {
                $this->activateByRoute($route, $item->getChildren());
            }
            else {
                if($item->getRoute() == $route) {
                    $item->setIsActive(true);
                }
            }
        }

        return $items;
    }

}