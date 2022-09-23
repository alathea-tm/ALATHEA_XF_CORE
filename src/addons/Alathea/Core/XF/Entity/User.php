<?php

namespace Alathea\Core\XF\Entity;

use XF\Mvc\Entity\Structure;

class User extends XFCP_User
{
    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->relations += [
            'AlatheaUser' => [
                'type' => self::TO_ONE,
                'entity' => 'Alathea\Core:User',
                'conditions' => 'user_id'
            ]
        ];

        return $structure;
    }
}