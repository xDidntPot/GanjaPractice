<?php

/*
 *
 * Developed by Warro#7777
 * Join Ganja: ganja.bet:19132
 * My Discord: https://discord.gg/vasar
 * Repository: https://github.com/Wqrro/Ganja
 *
 */

declare(strict_types=1);

namespace Warro\entities;

use pocketmine\entity\object\ItemEntity;

class VasarItemEntity extends ItemEntity{

	public function entityBaseTick(int $tickDiff = 1) : bool{
		if($this->ticksLived >= 20 * 30){
			$this->flagForDespawn();
		}
		return parent::entityBaseTick($tickDiff);
	}
}