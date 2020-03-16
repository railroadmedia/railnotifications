<?php

namespace Railroad\Railcontent\Contracts;

use Railroad\Doctrine\Contracts\UserEntityInterface;

interface UserInterface extends UserEntityInterface
{
	public function getEmail();
}
