<?php

namespace Alathea\Core\Install\Data;

use XF\Db\Schema\Create;

class MySQL
{
	public function getData(&$data = []): void
    {
        $data['xf_alathea_core_user'] = [
            'import' => true,
            'drop' => true,
            'create' => function(Create $table)
            {
                $table->addColumn('user_id', 'int')->primaryKey();
                $table->addColumn('gender', 'enum')->values(['none', 'male', 'female', "other"])->setDefault('none');
            }
        ];
	}
}