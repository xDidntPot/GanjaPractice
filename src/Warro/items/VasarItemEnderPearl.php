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

namespace Warro\items;

use pocketmine\entity\Location;
use pocketmine\entity\projectile\Throwable;
use pocketmine\item\EnderPearl;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use Warro\Base;
use Warro\entities\VasarPearl;

class VasarItemEnderPearl extends EnderPearl{

	public function getThrowForce() : float{
		return 2.35;
	}

	protected function createEntity(Location $location, Player $thrower) : Throwable{
		return new VasarPearl($location, $thrower);
	}

	public function onClickAir(Player $player, Vector3 $directionVector, array &$returnedItems) : ItemUseResult{
		if(Base::getInstance()->utils->isInPearlCooldown($player)){
			return ItemUseResult::FAIL();
		}else{
			Base::getInstance()->utils->setPearlCooldown($player, true, true);
		}
		return parent::onClickAir($player, $directionVector, $returnedItems);
	}
}