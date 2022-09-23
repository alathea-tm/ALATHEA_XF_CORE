<?php

namespace Alathea\Core\Admin\Controller;

use XF\Admin\Controller\AbstractController;
use XF\Mvc\Reply\AbstractReply;

class Plugin extends AbstractController
{
    public function actionIndex(): AbstractReply
    {
        return $this->message('В разработке.');
    }
}