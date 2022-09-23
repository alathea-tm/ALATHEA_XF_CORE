<?php

namespace Alathea\Core\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * @package Entity
 *
 * Columns:
 * @property int user_id
 * @property string gender
 * 
 * Relations:
 * @property \XF\Entity\User User
 */
class User extends Entity
{
    const GENDER_MALE = 'male';
    const GENDER_FEMALE = 'female';
    const GENDER_OTHER = 'other';
    const GENDER_NONE = 'none';
    
    public static function getStructure(Structure $structure): Structure
    {
        $structure->primaryKey = 'user_id';
        $structure->shortName = 'Alathea\Core:User';
        $structure->table = 'xf_alathea_core_user';

        $structure->columns = [
            'user_id' => [
                'type' => self::UINT
            ],
            'gender' => [
                'type' => self::STR,
                'nullable' => false,
                'default' => self::GENDER_NONE,
                'allowedValues' => [
                    self::GENDER_MALE,
                    self::GENDER_FEMALE,
                    self::GENDER_NONE,
                    self::GENDER_OTHER
                ],
            ]
        ];

        $structure->getters = [];
        
        $structure->relations = [
            'User' => [
                'type' => self::TO_ONE,
                'entity' => 'XF:User',
                'conditions' => 'user_id'
            ]
        ];
        
        return $structure;
    }
}