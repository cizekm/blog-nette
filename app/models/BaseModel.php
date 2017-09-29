<?php

namespace App\Models;

use Doctrine\ORM\Mapping as ORM;
use Nette\SmartObject;

/**
 * @ORM\MappedSuperclass
 */
abstract class BaseModel
{
	use SmartObject;
}
